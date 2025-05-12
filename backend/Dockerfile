FROM php:apache

ADD . /var/www/html

WORKDIR /var/www/html

RUN apt-get update && \
    apt-get install -y unzip && \
    docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer

RUN composer install --no-interaction --prefer-dist

CMD ["apache2-foreground"]