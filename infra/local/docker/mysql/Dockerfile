ARG TAG=8.4
FROM mysql:${TAG}

COPY my.cnf /etc/mysql/conf.d/my.cnf
COPY init /docker-entrypoint-initdb.d
