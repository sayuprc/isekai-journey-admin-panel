#!/bin/sh

protoc --proto_path=proto \
  --php_out=app \
  --grpc_out=app \
  --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
  ./proto/journey_log/shared.proto \
  ./proto/journey_log/journey_log.proto \
  ./proto/journey_log/journey_log_link_type.proto

composer dump-autoload
