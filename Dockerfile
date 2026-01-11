# PHP avec FPM
FROM php:8.2-fpm

# Installer extensions courantes
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Dossier de travail
WORKDIR /var/www/html
