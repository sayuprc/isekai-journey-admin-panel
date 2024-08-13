FROM php:8.3-fpm

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update \
  && apt-get install -y git \
    libpq-dev \
    unzip \
    nodejs \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && docker-php-ext-install pgsql pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000
ARG USERNAME=user
ARG GROUPNAME=user

RUN groupadd -g $GID $GROUPNAME \
  && useradd -m -s /bin/bash -u $UID -g $GID $USERNAME

USER $USERNAME

WORKDIR /var/www/html
