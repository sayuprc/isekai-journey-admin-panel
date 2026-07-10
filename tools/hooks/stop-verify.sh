#!/usr/bin/env bash
set -euo pipefail

# shellcheck source=tools/hooks/common.sh
source "$(cd "$(dirname "$0")" && pwd)/common.sh"

repo_root="$(hook_repo_root)"
hook_state_dir="$repo_root/.git/agent-hooks"
contracts_stop_marker="$hook_state_dir/contracts-stop-verify"

[ -f "$contracts_stop_marker" ] || exit 0
rm -f "$contracts_stop_marker"

cd "$repo_root"

result=""

if ! format_output="$(mise run contract:format:check 2>&1)"; then
  result="フォーマット違反あり:
$(printf '%s\n' "$format_output" | head -40)"
fi

if ! test_output="$(mise run contract:test 2>&1)"; then
  result="${result:+$result

}テスト失敗:
$(printf '%s\n' "$test_output" | head -40)"
fi

if [ -n "$result" ]; then
  hook_emit_stop_block "$result"
  # Cursor also honors exit 2 for blocking; Claude/Codex stop blockers prefer JSON.
  exit 2
fi
