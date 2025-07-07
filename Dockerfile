FROM php:8.2-apache

# Let apt run non-interactively
ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev unzip zip curl git \
    && docker-php-ext-install pdo pdo_mysql zip mbstring

# Enable Apache Rewrite
RUN a2enmod rewrite

# Enable .htaccess override
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
</Directory>' >> /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy your app
COPY . .

# Optional: install composer dependencies
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer install --no-dev || true

EXPOSE 80
CMD ["apache2-foreground"]
