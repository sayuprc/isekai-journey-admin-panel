#!/usr/bin/env bash
# Publish generated badge JSON to the orphan metrics branch.
set -euo pipefail

usage() {
  cat <<'EOF'
Usage:
  publish-metrics-branch.sh \
    --source-dir PATH \
    [--branch NAME] \
    [--git-sha SHA]
EOF
}

SOURCE_DIR=""
BRANCH="${METRICS_BRANCH:-metrics}"
GIT_SHA="${GITHUB_SHA:-unknown}"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --source-dir)
      SOURCE_DIR="$2"
      shift 2
      ;;
    --branch)
      BRANCH="$2"
      shift 2
      ;;
    --git-sha)
      GIT_SHA="$2"
      shift 2
      ;;
    -h | --help)
      usage
      exit 0
      ;;
    *)
      echo "error: unknown argument: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

if [[ -z "$SOURCE_DIR" ]]; then
  echo "error: --source-dir is required" >&2
  usage >&2
  exit 1
fi

SUMMARY_SRC="${SOURCE_DIR}/summary.json"
BADGES_SRC="${SOURCE_DIR}/badges"

[[ -f "$SUMMARY_SRC" ]] || {
  echo "error: missing summary: $SUMMARY_SRC" >&2
  exit 1
}
[[ -d "$BADGES_SRC" ]] || {
  echo "error: missing badges dir: $BADGES_SRC" >&2
  exit 1
}

git config user.name "github-actions[bot]"
git config user.email "github-actions[bot]@users.noreply.github.com"

WORKTREE="$(mktemp -d "${TMPDIR:-/tmp}/metrics-branch.XXXXXX")"
cleanup() {
  if git worktree remove --force "$WORKTREE" >/dev/null 2>&1; then
    return
  fi
  rm -rf "$WORKTREE"
}
trap cleanup EXIT

SHORT_SHA="${GIT_SHA:0:7}"

if git ls-remote --exit-code --heads origin "$BRANCH" >/dev/null 2>&1; then
  git fetch origin "$BRANCH"
  git worktree add -B "$BRANCH" "$WORKTREE" "origin/${BRANCH}"
else
  git worktree add --detach "$WORKTREE"
  git -C "$WORKTREE" checkout --orphan "$BRANCH"
  git -C "$WORKTREE" rm -rf . >/dev/null 2>&1 || true
fi

mkdir -p "${WORKTREE}/docs/metrics/badges"
cp "$SUMMARY_SRC" "${WORKTREE}/docs/metrics/summary.json"
cp "${BADGES_SRC}"/*.json "${WORKTREE}/docs/metrics/badges/"

cat >"${WORKTREE}/README.md" <<'EOF'
# Server metrics

このブランチは `Server Metrics` workflow が生成するバッジ JSON / summary 専用です。
ソースコードの変更は含めません。
EOF

git -C "$WORKTREE" add README.md docs/metrics
if git -C "$WORKTREE" diff --cached --quiet; then
  echo "metrics branch is already up to date"
  exit 0
fi

git -C "$WORKTREE" commit -m "chore: update server metrics badges (${SHORT_SHA})"
git -C "$WORKTREE" push origin "HEAD:${BRANCH}"
