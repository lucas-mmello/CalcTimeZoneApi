FROM php:8.2-apache

# Ativa mod_rewrite (útil se futuramente quiser rotas)
RUN a2enmod rewrite

# Copia o projeto para o Apache
COPY . /var/www/html

# Permissões
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
