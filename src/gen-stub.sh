#!/bin/sh

protoc --proto_path=proto \
  --php_out=app \
  --grpc_out=app \
  --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
  ./proto/shared/shared.proto \
  ./proto/journey_log/journey_log.proto \
  ./proto/journey_log_link_type/journey_log_link_type.proto \
  ./proto/song/song.proto

composer dump-autoload
