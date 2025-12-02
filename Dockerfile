FROM php:8.5-fpm

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

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm (for building assets)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies and build assets
RUN npm install && npm run build

# Set proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
