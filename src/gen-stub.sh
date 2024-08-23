#!/bin/sh

protoc --proto_path=proto/journey_log \
  --php_out=app \
  --grpc_out=app \
  --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
  ./proto/journey_log/journey_log.proto

composer dump-autoload
