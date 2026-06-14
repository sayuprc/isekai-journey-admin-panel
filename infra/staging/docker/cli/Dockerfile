FROM jdxcode/mise:2026.6.6 AS atlas

WORKDIR /workspace

COPY mise.toml ./

RUN mise trust \
  && mise install atlas \
  && install -Dm755 "$(mise which atlas)" /out/atlas

FROM php:8.5.7-cli

ENV TZ=Asia/Tokyo

WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y \
    unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && pecl channel-update pecl.php.net \
  && pecl install \
    redis \
  && docker-php-ext-enable \
    redis \
  && docker-php-ext-install \
    pdo_mysql

COPY --from=composer:2.10.1 /usr/bin/composer /usr/bin/composer
COPY src/server/composer.json src/server/composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY --from=atlas /out/atlas /usr/local/bin/atlas
COPY infra/staging/docker/cli/php.ini /usr/local/etc/php/php.ini
COPY src/server ./

RUN composer dump-autoload --optimize --no-dev --no-interaction

USER www-data
