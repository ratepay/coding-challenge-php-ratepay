FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/ratepay ratepay
RUN mkdir -p /home/ratepay/.composer && \
    chown -R ratepay:ratepay /home/ratepay

# Copy existing application directory contents
COPY ./backend /var/www

# Copy existing application directory permissions
COPY --chown=ratepay:ratepay ./backend /var/www

# Change current user to ratepay
USER ratepay

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
