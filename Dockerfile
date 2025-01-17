# Use an official PHP image with Apache
FROM php:8.2-apache

# Enable MySQLi extension
RUN docker-php-ext-install mysqli

# Copy application files to the container
COPY ./app /var/www/html

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apply specific permissions for the uploads directory
RUN chmod -R 777 /var/www/html/uploads \
&& chown -R www-data:www-data /var/www/html/uploads

# Expose port 80 for web traffic
EXPOSE 80
