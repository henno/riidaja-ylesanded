#!/bin/bash
# Start both WebSocket servers for torva.ee and vkok.ee

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

echo "Starting WebSocket servers..."

# Start torva.ee WebSocket server (port 8765, database.db)
echo "Starting torva.ee WebSocket server (port 8765)..."
nohup node "$SCRIPT_DIR/server.js" > "$PROJECT_DIR/logs/websocket-8765.log" 2>&1 &
TORVA_PID=$!
echo "✓ torva.ee WebSocket server started (PID: $TORVA_PID)"

# Start vkok.ee WebSocket server (port 8766, database_vkok.db)
echo "Starting vkok.ee WebSocket server (port 8766)..."
nohup env WS_PORT=8766 DB_PATH="$PROJECT_DIR/database_vkok.db" node "$SCRIPT_DIR/server.js" > "$PROJECT_DIR/logs/websocket-8766.log" 2>&1 &
VKOK_PID=$!
echo "✓ vkok.ee WebSocket server started (PID: $VKOK_PID)"

echo ""
echo "WebSocket servers running:"
echo "  torva.ee (port 8765): $TORVA_PID"
echo "  vkok.ee  (port 8766): $VKOK_PID"
echo ""
echo "To stop servers:"
echo "  kill $TORVA_PID"
echo "  kill $VKOK_PID"

# Save PIDs to a file for later reference
echo "$TORVA_PID" > "$SCRIPT_DIR/.ws-pids-torva"
echo "$VKOK_PID" > "$SCRIPT_DIR/.ws-pids-vkok"
