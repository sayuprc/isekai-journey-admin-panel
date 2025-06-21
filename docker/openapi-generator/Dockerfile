FROM openapitools/openapi-generator-cli:latest

ARG UID=1000
ARG GID=1000
ARG USERNAME=user
ARG GROUPNAME=user

RUN groupadd -g $GID $GROUPNAME \
  && useradd -m -s /bin/bash -u $UID -g $GID $USERNAME
