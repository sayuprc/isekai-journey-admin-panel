#!/bin/bash

SCRIPT_DIR=$(cd $(dirname $0); pwd)

. $SCRIPT_DIR/../../.env

atlas schema apply --env local \
  --var db_user=$DB_USERNAME \
  --var db_password=$DB_PASSWORD

atlas schema apply --env testing \
  --var db_user=$DB_USERNAME \
  --var db_password=$DB_PASSWORD
