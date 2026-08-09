#!/usr/bin/env bash

set -euo pipefail

worktree_repo_root() {
  cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd
}

worktree_git_common_dir() {
  git -C "$(worktree_repo_root)" rev-parse --path-format=absolute --git-common-dir
}

worktree_state_dir() {
  printf '%s/.worktree\n' "$(worktree_repo_root)"
}

worktree_mise_local_toml_file() {
  printf '%s/mise.local.toml\n' "$(worktree_repo_root)"
}

worktree_shared_state_dir() {
  local common_dir
  local common_hash

  common_dir="$(worktree_git_common_dir)"
  common_hash="$(printf '%s' "$common_dir" | sha256sum | cut -c1-12)"

  printf '/tmp/isekai-observatory-worktree/%s\n' "$common_hash"
}

worktree_shared_env_file() {
  printf '%s/%s.env\n' "$(worktree_shared_state_dir)" "$(worktree_hash)"
}

worktree_hash() {
  printf '%s' "$(worktree_repo_root)" | sha256sum | cut -c1-8
}

worktree_slug() {
  basename "$(worktree_repo_root)" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//; s/-{2,}/-/g'
}

compose_project_name() {
  local slug
  slug="$(worktree_slug)"
  slug="${slug:-worktree}"
  printf 'isekai-observatory-%s-%s\n' "${slug:0:24}" "$(worktree_hash)"
}

worktree_prefers_port() {
  local start="$1"
  local end="$2"
  local span
  span=$((end - start + 1))

  printf '%d\n' "$((start + (16#$(worktree_hash) % span)))"
}

port_is_listening() {
  local port="$1"
  (echo >"/dev/tcp/127.0.0.1/${port}") >/dev/null 2>&1
}

write_shell_value() {
  local key="$1"
  local value="$2"
  printf '%s=%q\n' "$key" "$value"
}

write_toml_string() {
  local key="$1"
  local value="$2"

  value="${value//\\/\\\\}"
  value="${value//\"/\\\"}"

  printf '%s = "%s"\n' "$key" "$value"
}
