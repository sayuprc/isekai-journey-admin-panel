FROM dunglas/frankenphp:php8.4

COPY certs/rootCA.pem /usr/share/ca-certificates/

RUN echo 'rootCA.pem' >> /etc/ca-certificates.conf \
  && update-ca-certificates \
  && apt-get update \
  && apt-get install -y \
    git \
    unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && curl -sSf https://atlasgo.sh | sh \
  && install-php-extensions \
    intl \
    xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000
ARG USERNAME=user
ARG GROUPNAME=user

RUN groupadd -g $GID $GROUPNAME \
  && useradd -m -s /bin/bash -u $UID -g $GID $USERNAME \
  && setcap -r /usr/local/bin/frankenphp \
  && chown -R ${USERNAME}:${GROUPNAME} /data/caddy \
  && chown -R ${USERNAME}:${GROUPNAME} /config/caddy

USER $USERNAME

ENV SERVER_NAME=:80

WORKDIR /var/www/html
