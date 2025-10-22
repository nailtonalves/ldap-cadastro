FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y libldap2-dev && \
    docker-php-ext-install ldap

COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./html /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
