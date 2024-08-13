#!/bin/bash

sed -i -e "s/^#logging_collector = off/logging_collector = on/" /var/lib/postgresql/data/postgresql.conf

sed -i -e "s/^#log_directory = 'log'/log_directory = '\/var\/lib\/postgresql\/data\/logs'/" /var/lib/postgresql/data/postgresql.conf

sed -i -e "s/^#log_statement = 'none'/log_statement = 'all'/" /var/lib/postgresql/data/postgresql.conf

sed -i -e "s/^log_timezone = 'Etc\/UTC'/log_timezone = 'Japan'/" /var/lib/postgresql/data/postgresql.conf

sed -i -e "s/^timezone = 'Etc\/UTC'/timezone = 'Japan'/" /var/lib/postgresql/data/postgresql.conf
