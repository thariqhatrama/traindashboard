FROM php:8.2-apache

# Copy file dari folder public ke web server Apache
COPY public/ /var/www/html/

# Install ekstensi PostgreSQL
RUN docker-php-ext-install pgsql pdo_pgsql
