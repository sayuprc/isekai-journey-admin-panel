#!/bin/sh

protoc --proto_path=../contracts/generated/protobuf \
  --php_out=. \
  --grpc_out=. \
  --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
  ../contracts/generated/protobuf/isekai_journey/shared.proto \
  ../contracts/generated/protobuf/isekai_journey/journey_log.proto \
  ../contracts/generated/protobuf/isekai_journey/journey_log_link_type.proto \
  ../contracts/generated/protobuf/isekai_journey/song.proto

composer dump-autoload
