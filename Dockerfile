FROM php:8.2-apache

# Enable required Apache modules
RUN a2enmod rewrite headers

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Update Apache configs
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Configure DirectoryIndex + permissions
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
    DirectoryIndex index.php index.html\n\
</Directory>' >> /etc/apache2/apache2.conf

# Copy project files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
