#!/usr/bin/env bash
# Shared helpers for agent hook scripts (Claude Code / Codex / Cursor).

hook_repo_root() {
  cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd
}

# Extract a target file path from Claude / Codex / Cursor hook payloads.
# Supports:
# - tool_input.file_path / tool_input.path (Claude Write/Edit, Cursor Write)
# - file_path (Cursor afterFileEdit)
# - apply_patch payloads with *** Update/Add File: lines (Codex)
hook_extract_file_path() {
  local input="$1"
  local file

  file="$(jq -r '
    .tool_input.file_path
    // .tool_input.path
    // .file_path
    // empty
  ' <<< "$input")"

  if [ -n "$file" ] && [ "$file" != "null" ]; then
    printf '%s\n' "$file"
    return 0
  fi

  # Codex apply_patch embeds paths inside tool_input.command.
  local command
  command="$(jq -r '.tool_input.command // empty' <<< "$input")"
  if [ -z "$command" ]; then
    return 1
  fi

  file="$(printf '%s\n' "$command" | sed -nE 's/^(\*\*\* (Update|Add|Delete) File:|diff --git a\/[^ ]+ b\/)[[:space:]]*(.+)$/\3/p' | head -1)"
  if [ -n "$file" ]; then
    printf '%s\n' "$file"
    return 0
  fi

  return 1
}

# Emit PostToolUse / afterFileEdit feedback compatible with Claude nested
# output and Cursor flat additional_context.
hook_emit_additional_context() {
  local event_name="$1"
  local message="$2"

  jq -n \
    --arg event "$event_name" \
    --arg msg "$message" \
    '{
      additional_context: $msg,
      hookSpecificOutput: {
        hookEventName: $event,
        additionalContext: $msg
      }
    }'
}

# Emit Stop follow-up feedback compatible with Claude / Codex / Cursor.
hook_emit_stop_block() {
  local reason="$1"

  jq -n --arg reason "$reason" '{
    decision: "block",
    reason: $reason,
    followup_message: $reason,
    hookSpecificOutput: {
      decision: "block",
      reason: $reason
    }
  }'
}
