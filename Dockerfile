FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath pdo_mysql mysqli gd mbstring zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

RUN printf 'display_errors=On\nerror_reporting=E_ALL\ndate.timezone=UTC\n' \
        > /usr/local/etc/php/conf.d/zz-yamilrh.ini

WORKDIR /var/www/html
