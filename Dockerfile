FROM php:7.4-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod ssl \
    && a2ensite default-ssl

COPY certs/fullchain.pem /etc/ssl/certs/
COPY certs/privkey.pem /etc/ssl/private/

COPY . /var/www/html/


EXPOSE 443

#RUN sed -i 's/443/443/' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
