FROM php:8.1-fpm

COPY php.ini /usr/local/etc/php/

ARG UID
ARG GID

RUN apt update \
  && apt install -y git default-mysql-client zlib1g-dev libzip-dev unzip libpng-dev libjpeg-dev libfreetype6-dev \
  && docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

RUN groupadd -g ${GID} appgroup \
  && useradd -m -u ${UID} -g appgroup appuser

RUN mkdir -p /var/www && chown -R appuser:appgroup /var/www

USER appuser

WORKDIR /var/www