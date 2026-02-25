FROM php:8.2-apache

# Instalar extensión de PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Copiar proyecto al servidor
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80