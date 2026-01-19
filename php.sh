#!/bin/bash

set -e

IMAGE_NAME="riidaja-app"
CONTAINER_NAME="riidaja-server"
PORT=8001

function composer() {
  docker run --rm \
    -v $(pwd):/app \
    -v ${COMPOSER_HOME:-$HOME/.composer}:/tmp/composer \
    -e COMPOSER_CACHE_DIR=/tmp/composer/cache \
    -w /app \
    --entrypoint sh \
    composer:latest -c "git config --global --add safe.directory /app && composer $*"
}

function start() {
  # Run composer install to ensure dependencies are up to date
  echo "Running optimized composer install..."
  composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

  # Build and run in one command
  echo "Building and starting PHP server..."
  docker build -t "$IMAGE_NAME" .
  docker rm -f "$CONTAINER_NAME" 2>/dev/null || true
  docker run -d \
    --name "$CONTAINER_NAME" \
    -p "$PORT:8000" \
    -v $(pwd):/app \
    --restart unless-stopped \
    "$IMAGE_NAME"
  echo "Server running at http://localhost:$PORT"
}

function stop() {
  docker rm -f "$CONTAINER_NAME" 2>/dev/null || true
}

# Show usage if no arguments
[ -z "$1" ] && { 
  echo "Usage: $0 {start|stop|restart|composer <args>}"
  echo "Examples:"
  echo "  $0 start      # Start the server"
  echo "  $0 stop       # Stop the server"
  echo "  $0 restart    # Restart the server"
  echo "  $0 composer   # Run composer commands"
  exit 1
}

case "$1" in
  start) start ;;
  stop) stop ;;
  restart) stop; start ;;
  composer) shift; composer "$@" ;;
  *) echo "Unknown command: $1"; exit 1 ;;
esac
