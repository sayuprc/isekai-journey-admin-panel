FROM php:8.4-fpm as grpc-build

ARG PROTOC_VERSION="27.3"
ARG GRPC_VERSION="v1.65.5"

RUN apt-get update \
  && apt-get install -y \
    cmake \
    git \
    libpq-dev \
    unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && curl -L https://github.com/protocolbuffers/protobuf/releases/download/v${PROTOC_VERSION}/protoc-${PROTOC_VERSION}-linux-x86_64.zip -o protoc.zip \
  && unzip -q protoc.zip bin/protoc -d /usr/local/ \
  && cd /var/local \
  && git clone -b ${GRPC_VERSION} https://github.com/grpc/grpc --depth=1 \
  && cd /var/local/grpc \
  && git submodule update --init \
  && mkdir -p cmake/build \
  && cd /var/local/grpc/cmake/build \
  && cmake ../.. \
  && make protoc grpc_php_plugin \
  && cp -p /var/local/grpc/cmake/build/grpc_php_plugin /usr/local/bin/ \
  && cd /var/local \
  && rm -rf grpc

FROM php:8.4-fpm

COPY certs/rootCA.pem /usr/share/ca-certificates/

RUN echo 'rootCA.pem' >> /etc/ca-certificates.conf \
  && update-ca-certificates \
  && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update \
  && apt-get install -y \
    git \
    libpq-dev \
    unzip \
    nodejs \
    zlib1g-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && pecl install xdebug protobuf grpc \
  && docker-php-ext-enable xdebug protobuf grpc \
  && docker-php-ext-install pgsql pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=grpc-build /usr/local/bin/protoc /usr/local/bin/protoc
COPY --from=grpc-build /usr/local/bin/grpc_php_plugin /usr/local/bin/grpc_php_plugin

ARG UID=1000
ARG GID=1000
ARG USERNAME=user
ARG GROUPNAME=user

RUN groupadd -g $GID $GROUPNAME \
  && useradd -m -s /bin/bash -u $UID -g $GID $USERNAME

USER $USERNAME

WORKDIR /var/www/html
