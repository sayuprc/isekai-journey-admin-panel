#!/bin/bash

SCRIPT_DIR=$(cd $(dirname $0); pwd)

env_files=("${SCRIPT_DIR}/../../.env" "${SCRIPT_DIR}/../../.env.testing")
env_names=("local" "testing")

for i in "${!env_files[@]}"; do
  env_file="${env_files[$i]}"
  env_name="${env_names[$i]}"

  if [ -f "${env_file}" ]; then
    . "${env_file}"

    db_name=$(basename "${DB_DATABASE}")

    echo "${db_name}(${env_name}) に適用します"

    atlas schema apply --env "${env_name}" --var db_name="${db_name}"
  fi
done
