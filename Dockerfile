FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Enable error reporting (kwa debugging)
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/errors.ini && \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/errors.ini

# Copy all files to Apache document root
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Configure Apache to use port 10000 (Render default)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf && \
    sed -i 's/:80/:10000/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD ["apache2-foreground"]