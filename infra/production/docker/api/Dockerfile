FROM dunglas/frankenphp:php8.5

ENV TZ=Asia/Tokyo

WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y \
    unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && install-php-extensions \
    opcache \
    pdo \
    pdo_mysql \
    redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY infra/production/docker/api/php.ini /usr/local/etc/php/php.ini

COPY src/server/composer.json src/server/composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY src/contracts/generated/oas ../contracts/generated/oas
COPY src/server ./

RUN composer dump-autoload --optimize --no-dev --no-interaction \
	&& setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
	# /config/caddy および /data/caddy への書き込み権限を付与
	&& chown -R www-data:www-data /config/caddy /data/caddy \
	# 起動時に services.php の再生成や storage への書き込みが走るため、実行ユーザーに権限を付与
	&& chown -R www-data:www-data bootstrap/cache storage

EXPOSE 8080

USER www-data
