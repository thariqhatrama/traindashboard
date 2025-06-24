FROM php:8.2-apache

# Install dependensi PostgreSQL terlebih dahulu
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pgsql pdo_pgsql

# Copy aplikasi Anda ke direktori publik Apache
COPY public/ /var/www/html/
