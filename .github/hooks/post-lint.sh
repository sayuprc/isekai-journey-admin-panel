#!/usr/bin/env bash
set -euo pipefail

# PostToolUse hook: ファイル編集後に自動リント・フォーマットを実行し、
# 残った違反を additionalContext としてエージェントにフィードバックする

input="$(cat)"
file="$(jq -r '.tool_input.file_path // .tool_input.path // empty' <<< "$input")"

[ -z "$file" ] && exit 0

repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
hook_state_dir="$repo_root/.git/copilot-hooks"
contracts_stop_marker="$hook_state_dir/contracts-stop-verify"

# 自動生成ファイルはスキップ
case "$file" in
  */Generated/*|*/generated/*|*/vendor/*|*/node_modules/*|*/.astro/*|*/dist/*)
    exit 0
    ;;
esac

case "$file" in
  src/contracts/*.tsp|*/src/contracts/*.tsp|src/contracts/scripts/*.ts|*/src/contracts/scripts/*.ts|src/contracts/scripts/*.js|*/src/contracts/scripts/*.js|src/contracts/package.json|*/src/contracts/package.json|src/contracts/bun.lock|*/src/contracts/bun.lock|src/contracts/tspconfig.yaml|*/src/contracts/tspconfig.yaml)
    mkdir -p "$hook_state_dir"
    : > "$contracts_stop_marker"
    ;;
esac

case "$file" in
  */src/server/*.php)
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
    mise run api:ecs:fix -- "$container_path" >/dev/null 2>&1 || true

    # 残った違反をチェック
    diag="$(mise run api:ecs -- "$container_path" 2>&1 | head -30)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|found'; then
      jq -n --arg msg "$diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: ("ECS violations in " + "'"$container_path"'" + ":\n" + $msg)
        }
      }'
    fi

    # mago lint（ホスト上で高速実行）
    mago_diag="$(mago lint "$file" 2>&1 | head -30)" || true

    if [ -n "$mago_diag" ] && echo "$mago_diag" | grep -qiE 'warning|error|help'; then
      jq -n --arg msg "$mago_diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: ("mago lint:\n" + $msg)
        }
      }'
    fi
    ;;

  */src/admin/*.ts|*/src/admin/*.tsx|*/src/admin/*.js|*/src/admin/*.jsx|*/src/admin/*.mjs|*/src/viewer/*.ts|*/src/viewer/*.tsx|*/src/viewer/*.js|*/src/viewer/*.jsx|*/src/viewer/*.mjs)
    case "$file" in
      */src/admin/*)
        cd "$repo_root/src/admin"
        ;;
      *)
        cd "$repo_root/src/viewer"
        ;;
    esac

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

  */src/admin/*.astro|*/src/viewer/*.astro)
    case "$file" in
      */src/admin/*)
        cd "$repo_root/src/admin"
        ;;
      *)
        cd "$repo_root/src/viewer"
        ;;
    esac

    # ESLint
    bunx eslint --fix "$file" >/dev/null 2>&1 || true
    diag_eslint="$(bunx eslint "$file" 2>&1 | head -10)" || true

    # Stylelint
    bunx stylelint --fix "$file" >/dev/null 2>&1 || true
    diag_style="$(bunx stylelint "$file" 2>&1 | head -10)" || true

    diag=""
    if [ -n "$diag_eslint" ] && echo "$diag_eslint" | grep -qiE 'error|warning'; then
      diag="$diag_eslint"
    fi
    if [ -n "$diag_style" ] && echo "$diag_style" | grep -qiE 'error|warning'; then
      diag="${diag:+$diag\n}$diag_style"
    fi

    if [ -n "$diag" ]; then
      jq -n --arg msg "$diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: $msg
        }
      }'
    fi
    ;;

  */src/admin/*.css|*/src/viewer/*.css)
    case "$file" in
      */src/admin/*)
        cd "$repo_root/src/admin"
        ;;
      *)
        cd "$repo_root/src/viewer"
        ;;
    esac

    # Stylelint 自動修正
    bunx stylelint --fix "$file" >/dev/null 2>&1 || true

    # 残った違反
    diag="$(bunx stylelint "$file" 2>&1 | head -20)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|warning'; then
      jq -n --arg msg "$diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: $msg
        }
      }'
    fi
    ;;

  src/contracts/*.tsp|*/src/contracts/*.tsp)
    cd "$repo_root"

    mise run contract:format >/dev/null 2>&1 || true

    if ! diag="$(mise run contract:format:check 2>&1)"; then
      diag="$(printf '%s\n' "$diag" | head -20)"

      jq -n --arg msg "$diag" '{
        hookSpecificOutput: {
          hookEventName: "PostToolUse",
          additionalContext: ("TypeSpec format violations:\n" + $msg)
        }
      }'
    fi
    ;;
esac
