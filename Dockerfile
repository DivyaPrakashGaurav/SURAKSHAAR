FROM php:8.2-apache

# Enable Apache mod_rewrite for .htaccess
RUN a2enmod rewrite

# Install required PHP extensions (if any). cURL is typically included.
# Update system and install basic utilities
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip pdo pdo_mysql mysqli

# Copy application files to the Apache document root
COPY . /var/www/html/

# Set the proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Update Apache configuration to allow override for .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Expose port 80 (Render forwards to the port you expose)
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
