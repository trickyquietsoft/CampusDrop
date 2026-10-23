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

# Create the uploads directory and set permissions
# Doing this after COPY ensures the directory exists and is writable regardless of source folder structure
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

# Set correct file ownership for the entire web root
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80
