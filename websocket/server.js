/**
 * WebSocket Server for Exercise Session Tracking
 *
 * Tracks time spent on exercises even when students refresh the page.
 * When connection is lost, the elapsed time is saved to the database.
 *
 * Usage: node server.js
 *
 * Required packages: npm install ws better-sqlite3
 */

const WebSocket = require('ws');
const Database = require('better-sqlite3');
const path = require('path');
const crypto = require('crypto');

// Configuration
const PORT = process.env.WS_PORT || 8765;
const DB_PATH = path.join(__dirname, '..', 'database.db');
const HEARTBEAT_INTERVAL = 10000; // 10 seconds
const SESSION_TIMEOUT = 30000; // 30 seconds without heartbeat = abandoned

// Initialize database connection
const db = new Database(DB_PATH);
db.pragma('journal_mode = WAL');

// Active sessions map: sessionId -> { ws, email, name, exerciseId, startTime, lastHeartbeat }
const activeSessions = new Map();

// Create WebSocket server
const wss = new WebSocket.Server({ port: PORT });

console.log(`WebSocket server started on port ${PORT}`);

/**
 * Generate unique session ID
 */
function generateSessionId() {
    return crypto.randomBytes(16).toString('hex');
}

/**
 * Save session to database
 */
function saveSession(sessionId, email, name, exerciseId, startTime) {
    const stmt = db.prepare(`
        INSERT INTO exercise_sessions (session_id, email, name, exercise_id, started_at, last_heartbeat, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    `);
    const now = new Date().toISOString().replace('T', ' ').substr(0, 19);
    stmt.run(sessionId, email, name, exerciseId, startTime, now);
}

/**
 * Update session heartbeat
 */
function updateHeartbeat(sessionId) {
    const stmt = db.prepare(`
        UPDATE exercise_sessions
        SET last_heartbeat = ?
        WHERE session_id = ? AND status = 'active'
    `);
    const now = new Date().toISOString().replace('T', ' ').substr(0, 19);
    stmt.run(now, sessionId);
}

/**
 * End session and calculate duration
 */
function endSession(sessionId, status = 'abandoned', resultId = null) {
    const session = activeSessions.get(sessionId);
    if (!session) return;

    const now = new Date();
    const durationSeconds = (now - session.startTime) / 1000;
    const endedAt = now.toISOString().replace('T', ' ').substr(0, 19);

    const stmt = db.prepare(`
        UPDATE exercise_sessions
        SET ended_at = ?, duration_seconds = ?, status = ?, result_id = ?
        WHERE session_id = ?
    `);
    stmt.run(endedAt, durationSeconds, status, resultId, sessionId);

    activeSessions.delete(sessionId);
    console.log(`Session ${sessionId} ended: ${status}, duration: ${Math.round(durationSeconds)}s`);
}

/**
 * Handle incoming WebSocket connection
 */
wss.on('connection', (ws) => {
    let sessionId = null;

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);

            switch (data.type) {
                case 'start':
                    // Start new session
                    sessionId = generateSessionId();
                    const startTime = new Date();

                    activeSessions.set(sessionId, {
                        ws,
                        email: data.email,
                        name: data.name,
                        exerciseId: data.exerciseId,
                        startTime,
                        lastHeartbeat: startTime
                    });

                    // Save to database
                    const startTimeStr = startTime.toISOString().replace('T', ' ').substr(0, 19);
                    saveSession(sessionId, data.email, data.name, data.exerciseId, startTimeStr);

                    // Send session ID back to client
                    ws.send(JSON.stringify({
                        type: 'started',
                        sessionId
                    }));

                    console.log(`Session started: ${sessionId} - ${data.email} - Exercise ${data.exerciseId}`);
                    break;

                case 'heartbeat':
                    // Update heartbeat
                    if (sessionId && activeSessions.has(sessionId)) {
                        const session = activeSessions.get(sessionId);
                        session.lastHeartbeat = new Date();
                        updateHeartbeat(sessionId);
                    }
                    break;

                case 'complete':
                    // Exercise completed successfully
                    if (sessionId) {
                        endSession(sessionId, 'completed', data.resultId || null);
                        sessionId = null;
                    }
                    break;

                case 'abort':
                    // User explicitly abandoned (e.g., navigated away)
                    if (sessionId) {
                        endSession(sessionId, 'abandoned');
                        sessionId = null;
                    }
                    break;
            }
        } catch (err) {
            console.error('Error processing message:', err);
        }
    });

    ws.on('close', () => {
        // Connection closed - mark session as abandoned if still active
        if (sessionId && activeSessions.has(sessionId)) {
            endSession(sessionId, 'abandoned');
        }
    });

    ws.on('error', (err) => {
        console.error('WebSocket error:', err);
        if (sessionId && activeSessions.has(sessionId)) {
            endSession(sessionId, 'abandoned');
        }
    });
});

/**
 * Periodic cleanup of stale sessions
 */
setInterval(() => {
    const now = new Date();

    for (const [sessionId, session] of activeSessions) {
        const timeSinceHeartbeat = now - session.lastHeartbeat;

        if (timeSinceHeartbeat > SESSION_TIMEOUT) {
            console.log(`Session ${sessionId} timed out (no heartbeat for ${Math.round(timeSinceHeartbeat/1000)}s)`);
            endSession(sessionId, 'abandoned');
        }
    }
}, HEARTBEAT_INTERVAL);

/**
 * Graceful shutdown
 */
process.on('SIGINT', () => {
    console.log('Shutting down...');

    // End all active sessions
    for (const [sessionId] of activeSessions) {
        endSession(sessionId, 'abandoned');
    }

    wss.close(() => {
        db.close();
        console.log('Server stopped');
        process.exit(0);
    });
});

console.log('WebSocket server ready for connections');
