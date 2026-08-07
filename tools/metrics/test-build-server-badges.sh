#!/usr/bin/env bash
# Smoke checks for build-server-badges.sh using fixtures.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
SCRIPT="${ROOT}/tools/metrics/build-server-badges.sh"
FIXTURES="${ROOT}/tools/metrics/fixtures"
OUTPUT_DIR="$(mktemp -d "${TMPDIR:-/tmp}/server-metrics-fixture.XXXXXX")"
trap 'rm -rf "$OUTPUT_DIR"' EXIT

"$SCRIPT" \
  --clover "${FIXTURES}/clover.xml" \
  --infection-summary "${FIXTURES}/infection-summary.json" \
  --phpmetrics "${FIXTURES}/phpmetrics.json" \
  --phpmetrics-summary "${FIXTURES}/phpmetrics-summary.json" \
  --output-dir "$OUTPUT_DIR" \
  --git-sha fixture \
  --generated-at "2026-07-10T00:00:00+00:00" \
  >/dev/null

assert_eq() {
  local actual="$1"
  local expected="$2"
  local label="$3"
  if [[ "$actual" != "$expected" ]]; then
    echo "FAIL: ${label}: expected=${expected} actual=${actual}" >&2
    exit 1
  fi
}

assert_eq "$(jq -r '.coveragePercent' "${OUTPUT_DIR}/summary.json")" "85.0" "coveragePercent"
assert_eq "$(jq -r '.infection.msi' "${OUTPUT_DIR}/summary.json")" "83.0" "msi"
assert_eq "$(jq -r '.phpmetrics.avgMaintainabilityIndex' "${OUTPUT_DIR}/summary.json")" "85.2" "avg MI"
assert_eq "$(jq -r '.message' "${OUTPUT_DIR}/badges/coverage.json")" "85.0%" "coverage badge message"
assert_eq "$(jq -r '.color' "${OUTPUT_DIR}/badges/coverage.json")" "yellow" "coverage badge color"
assert_eq "$(jq -r '.schemaVersion' "${OUTPUT_DIR}/badges/coverage.json")" "1" "badge schemaVersion"

echo "OK: build-server-badges.sh fixture checks passed"
