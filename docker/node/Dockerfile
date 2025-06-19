FROM node:22-bookworm

COPY certs/rootCA.pem /usr/share/ca-certificates/

RUN echo 'rootCA.pem' >> /etc/ca-certificates.conf \
  && update-ca-certificates

USER node

WORKDIR /app/src

CMD ["npm", "run", "dev", "--", "--host"]
