FROM php:apache

RUN apt-get update && \
    apt-get install -y unzip && \
    docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY . .

CMD ["bash", "-c", "composer install --no-interaction --prefer-dist && apache2-foreground"]