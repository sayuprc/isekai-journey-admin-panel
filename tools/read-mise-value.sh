#!/usr/bin/env bash
# Read a top-level key = "value" from mise.toml without requiring mise.
# Usage: tools/read-mise-value.sh <key> [mise.toml]
set -euo pipefail

if [[ $# -lt 1 || $# -gt 2 ]]; then
  echo "usage: $0 <key> [mise.toml]" >&2
  exit 2
fi

key=$1
file=${2:-mise.toml}

if [[ ! "$key" =~ ^[A-Za-z0-9_]+$ ]]; then
  echo "invalid key: $key" >&2
  exit 1
fi

if [[ ! -f "$file" ]]; then
  echo "file not found: $file" >&2
  exit 1
fi

value=$(sed -n "s/^${key} = \"\\(.*\\)\"$/\\1/p" "$file" | head -n1)

if [[ -z "$value" ]]; then
  echo "key not found: $key (in $file)" >&2
  exit 1
fi

printf '%s\n' "$value"
