FROM php:8.2-apache

# Enable common PHP extensions used in XAMPP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files
COPY src/ /var/www/html/

# Enable Apache rewrite (important for many PHP apps)
RUN a2enmod rewrite

EXPOSE 80
