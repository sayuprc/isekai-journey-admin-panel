ARG TAG=24
FROM node:${TAG}-bookworm

COPY certs/rootCA.pem /usr/share/ca-certificates/

RUN echo 'rootCA.pem' >> /etc/ca-certificates.conf \
  && update-ca-certificates

USER node

WORKDIR /app/src

CMD ["npm", "run", "dev", "--", "--host"]
