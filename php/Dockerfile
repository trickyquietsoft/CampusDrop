FROM php:8.2-apache

# Install system dependencies and PHP extensions for MySQL and GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    zlib1g-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
    mysqli \
    gd \
    pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application source code into the image
COPY ./www /var/www/html

# Set correct file ownership for Apache
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80
