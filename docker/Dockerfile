FROM php:7.2-apache

RUN docker-php-ext-install pdo_mysql
COPY src/ /var/www/html/

CMD ["touch", "/tmp/settings.txt"]