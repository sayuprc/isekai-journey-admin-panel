#!/usr/bin/env bash
set -euo pipefail

# PreToolUse hook: リンター・フォーマッター設定ファイルへの編集をブロックする
# エージェントがリンターエラーを設定変更で回避することを防止する

input="$(cat)"
file="$(jq -r '.tool_input.file_path // .tool_input.path // empty' <<< "$input")"

[ -z "$file" ] && exit 0

basename_file="$(basename "$file")"

PROTECTED_FILES=(
  "ecs.php"
  "phpstan.neon"
  "phparkitect.php"
  "eslint.config.mjs"
  "stylelint.config.mjs"
  ".editorconfig"
  "lefthook.yml"
  "tsconfig.json"
  "infection.json5"
  "phpunit.xml"
  "baseline.php"
)

for p in "${PROTECTED_FILES[@]}"; do
  if [ "$basename_file" = "$p" ]; then
    echo "BLOCKED: $file is a protected config file. Fix the code, not the linter/formatter config." >&2
    exit 2
  fi
done
