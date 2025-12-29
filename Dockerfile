FROM php:8.5-fpm

# Arguments for user ID and group ID
ARG USER_ID=1000
ARG GROUP_ID=1000

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    libexif-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    supervisor \
    librdkafka-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions - install in groups to avoid conflicts
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring

# Configure and install GD with dependencies
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

# Install remaining extensions - one by one to identify any issues
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl

# OPcache is now built-in to PHP 8.5 as a required extension - no installation needed

# Install Redis extension (use latest stable version for PHP 8.5 compatibility)
RUN pecl install redis && docker-php-ext-enable redis

# Install rdkafka extension for Kafka support
RUN pecl install rdkafka && docker-php-ext-enable rdkafka

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm (for building assets)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Create user and group with matching host UID/GID
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m -s /bin/bash appuser

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod 755 /usr/local/bin/entrypoint.sh

# Change ownership of working directory
RUN chown -R appuser:appuser /var/www/html

# Switch to non-root user
USER appuser

# Expose port 9000 for PHP-FPM
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
