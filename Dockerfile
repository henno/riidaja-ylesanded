FROM php:8.4.7-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    libzip-dev \
    zlib-dev

# Install PHP extensions for performance and functionality
RUN docker-php-ext-install -j$(nproc) \
    pdo_sqlite \
    zip \
    opcache

# Clean up build dependencies to reduce image size
RUN apk del sqlite-dev libzip-dev zlib-dev

# Configure OPcache for development (optimized for performance)
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Create app directory
WORKDIR /app

# Set entrypoint to PHP's built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]
