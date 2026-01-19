/**
 * Exercise Session Tracker
 *
 * Tracks time spent on exercises via WebSocket connection.
 * Automatically saves elapsed time when connection is lost (page refresh, close, etc.)
 *
 * Usage:
 *   const tracker = new SessionTracker(userEmail, userName, exerciseId);
 *   tracker.start();  // Call when exercise/timer starts
 *   tracker.complete(resultId);  // Call when exercise is completed successfully
 */

class SessionTracker {
    constructor(email, name, exerciseId) {
        this.email = email;
        this.name = name;
        this.exerciseId = exerciseId;
        this.ws = null;
        this.sessionId = null;
        this.heartbeatInterval = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3;

        // WebSocket server URL - adjust port if needed
        this.wsUrl = this.getWebSocketUrl();

        // Bind methods
        this.handleBeforeUnload = this.handleBeforeUnload.bind(this);
    }

    /**
     * Get WebSocket URL based on current location
     */
    getWebSocketUrl() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.hostname;
        const port = 8765; // Default WebSocket port
        return `${protocol}//${host}:${port}`;
    }

    /**
     * Start tracking session
     */
    start() {
        if (this.ws) {
            console.warn('Session tracker already started');
            return;
        }

        this.connect();

        // Add beforeunload handler to try to send abort on page leave
        window.addEventListener('beforeunload', this.handleBeforeUnload);
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        try {
            this.ws = new WebSocket(this.wsUrl);

            this.ws.onopen = () => {
                console.log('Session tracker connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;

                // Send start message
                this.ws.send(JSON.stringify({
                    type: 'start',
                    email: this.email,
                    name: this.name,
                    exerciseId: this.exerciseId
                }));

                // Start heartbeat
                this.startHeartbeat();
            };

            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    if (data.type === 'started') {
                        this.sessionId = data.sessionId;
                        console.log('Session started:', this.sessionId);
                    }
                } catch (err) {
                    console.error('Error parsing message:', err);
                }
            };

            this.ws.onclose = () => {
                console.log('Session tracker disconnected');
                this.isConnected = false;
                this.stopHeartbeat();

                // Try to reconnect if not intentionally closed
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                    console.log(`Reconnecting (attempt ${this.reconnectAttempts})...`);
                    setTimeout(() => this.connect(), 2000);
                }
            };

            this.ws.onerror = (err) => {
                console.error('Session tracker error:', err);
            };

        } catch (err) {
            console.error('Failed to connect to session tracker:', err);
        }
    }

    /**
     * Start sending heartbeats
     */
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify({ type: 'heartbeat' }));
            }
        }, 5000); // Every 5 seconds
    }

    /**
     * Stop sending heartbeats
     */
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }

    /**
     * Handle page unload - try to send abort signal
     */
    handleBeforeUnload(event) {
        // Try to send abort message
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({ type: 'abort' }));
        }

        // Also try using sendBeacon as fallback
        if (navigator.sendBeacon) {
            const data = JSON.stringify({
                sessionId: this.sessionId,
                type: 'abort'
            });
            navigator.sendBeacon('session_abort.php', data);
        }
    }

    /**
     * Mark session as completed
     */
    complete(resultId = null) {
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
        this.stopHeartbeat();

        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                type: 'complete',
                resultId: resultId
            }));

            // Close connection
            setTimeout(() => {
                this.ws.close();
                this.ws = null;
            }, 100);
        }
    }

    /**
     * Explicitly abort session
     */
    abort() {
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
        this.stopHeartbeat();

        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({ type: 'abort' }));

            setTimeout(() => {
                this.ws.close();
                this.ws = null;
            }, 100);
        }
    }
}

// Export for use in exercises
window.SessionTracker = SessionTracker;
