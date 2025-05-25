# Use official PHP Apache image
FROM php:8.2-apache

# Enable common PHP extensions if needed
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy app code into web server root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80

