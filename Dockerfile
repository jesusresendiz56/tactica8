FROM php:8.2-apache

# Instalar dependencias necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copiar proyecto completo al directorio raíz de Apache
COPY . /var/www/html/

EXPOSE 80