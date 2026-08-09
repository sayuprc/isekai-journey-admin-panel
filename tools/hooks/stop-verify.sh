#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib.sh
source "$script_dir/lib.sh"

hook_read_input

repo_root="$(cd "$script_dir/../.." && pwd)"
hook_state_dir="$repo_root/.git/agent-hooks"
contracts_stop_marker="$hook_state_dir/contracts-stop-verify"

[ -f "$contracts_stop_marker" ] || exit 0
rm -f "$contracts_stop_marker"

cd "$repo_root"

result=""

if ! format_output="$(mise run contract:format:check 2>&1)"; then
  result="フォーマット違反あり:\n$(printf '%s\n' "$format_output" | head -40)"
fi

if ! test_output="$(mise run contract:test 2>&1)"; then
  result="${result:+$result\n\n}テスト失敗:\n$(printf '%s\n' "$test_output" | head -40)"
fi

if [ -n "$result" ]; then
  hook_emit_stop_failure "$result"
fi
