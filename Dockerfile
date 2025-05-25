# Use official PHP image with Apache
FROM php:8.2-apache

# Copy your PHP app into the web root
COPY . /var/www/html/

# Enable required Apache modules
RUN docker-php-ext-install mysqli pdo pdo_mysql
