FROM php:8.4.7-cli-alpine

# Install SQLite and PDO SQLite extension
RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    && docker-php-ext-install pdo_sqlite \
    && apk del sqlite-dev

# Create app directory
WORKDIR /app

# Set entrypoint to PHP's built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]
