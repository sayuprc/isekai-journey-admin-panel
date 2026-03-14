#!/usr/bin/env bash
set -euo pipefail

# PostToolUse hook: ファイル編集後に自動リント・フォーマットを実行し、
# 残った違反を additionalContext としてエージェントにフィードバックする

input="$(cat)"
file="$(jq -r '.tool_input.file_path // .tool_input.path // empty' <<< "$input")"

[ -z "$file" ] && exit 0

# 自動生成ファイルはスキップ
case "$file" in
  */Generated/*|*/generated/*|*/vendor/*|*/node_modules/*|*/.astro/*|*/dist/*)
    exit 0
    ;;
esac

case "$file" in
  */src/server/*.php)
    repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
    cd "$repo_root"

    # Docker コンテナが起動していなければエラーフィードバック
    if ! docker compose exec -T php true 2>/dev/null; then
      jq -n '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: "ERROR: PHP コンテナが起動していません。\nFIX: mise run up を実行してコンテナを起動してください。"
        }
      }'
      exit 0
    fi

    # コンテナ内パスに変換
    container_path="${file#*src/server/}"

    # 自動修正
    mise run ecs:fix -- "$container_path" >/dev/null 2>&1 || true

    # 残った違反をチェック
    diag="$(mise run ecs -- "$container_path" 2>&1 | head -30)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|found'; then
      jq -n --arg msg "$diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: ("ECS violations in " + "'"$container_path"'" + ":\n" + $msg)
        }
      }'
    fi
    ;;

  */src/client/*.ts|*/src/client/*.tsx|*/src/client/*.js|*/src/client/*.jsx|*/src/client/*.mjs)
    repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
    cd "$repo_root/src/client"

    # Biome フォーマット
    bunx biome format --write "$file" >/dev/null 2>&1 || true

    # Oxlint 自動修正
    bunx oxlint --fix "$file" >/dev/null 2>&1 || true

    # 残った違反をチェック
    diag="$(bunx oxlint "$file" 2>&1 | head -20)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|warning'; then
      jq -n --arg msg "$diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: $msg
        }
      }'
    fi
    ;;

  */src/client/*.astro)
    repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
    cd "$repo_root/src/client"

    # Biome フォーマット（フロントマターの script 部分）
    bunx biome format --write "$file" >/dev/null 2>&1 || true
    ;;

  */src/client/*.css)
    # CSS は Stylelint が担当（Tailwind v4 構文は Biome 非対応）
    ;;
esac
