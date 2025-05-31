#!/bin/sh

protoc --proto_path=../proto/generated/protobuf \
  --php_out=. \
  --grpc_out=. \
  --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
  ../proto/generated/protobuf/isekai_journey/shared.proto \
  ../proto/generated/protobuf/isekai_journey/journey_log.proto \
  ../proto/generated/protobuf/isekai_journey/journey_log_link_type.proto \
  ../proto/generated/protobuf/isekai_journey/song.proto

composer dump-autoload
