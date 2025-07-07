FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    unzip zip curl git libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set proper Apache document root
# If you have a `public` folder, change to: DocumentRoot /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html|' /etc/apache2/sites-available/000-default.conf

# Set .htaccess support
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
</Directory>' >> /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Run composer install
RUN composer install --no-dev --optimize-autoloader || true

# Expose default web port
EXPOSE 8080

# Start Apache in the foreground
CMD ["apache2-foreground"]
