#!/usr/bin/env bash
# Build shields.io endpoint JSON and a summary from server metric reports.
set -euo pipefail

usage() {
  cat <<'EOF'
Usage:
  build-server-badges.sh \
    --clover PATH \
    --infection-summary PATH \
    --phpmetrics PATH \
    --output-dir PATH \
    [--phpmetrics-summary PATH] \
    [--git-sha SHA] \
    [--generated-at ISO8601]
EOF
}

require_cmd() {
  command -v "$1" >/dev/null 2>&1 || {
    echo "error: required command not found: $1" >&2
    exit 1
  }
}

round1() {
  # awk keeps portable one-decimal rounding.
  awk -v n="$1" 'BEGIN { printf "%.1f", n + 0 }'
}

round2() {
  awk -v n="$1" 'BEGIN { printf "%.2f", n + 0 }'
}

color_for_pct() {
  local value="$1"
  awk -v n="$value" 'BEGIN {
    if (n < 50) print "red"
    else if (n < 70) print "orange"
    else if (n < 90) print "yellow"
    else print "brightgreen"
  }'
}

color_for_mi() {
  local value="$1"
  awk -v n="$value" 'BEGIN {
    if (n < 65) print "red"
    else if (n < 85) print "orange"
    else print "brightgreen"
  }'
}

color_for_ccn() {
  local value="$1"
  awk -v n="$value" 'BEGIN {
    if (n >= 10) print "red"
    else if (n >= 7) print "orange"
    else if (n >= 5) print "yellow"
    else print "brightgreen"
  }'
}

write_badge() {
  local path="$1"
  local label="$2"
  local message="$3"
  local color="$4"
  jq -n \
    --arg label "$label" \
    --arg message "$message" \
    --arg color "$color" \
    '{schemaVersion: 1, label: $label, message: $message, color: $color}' \
    >"$path"
}

CLOVER=""
INFECTION=""
PHPMETRICS=""
PHPMETRICS_SUMMARY=""
OUTPUT_DIR=""
GIT_SHA="${GITHUB_SHA:-unknown}"
GENERATED_AT="$(date -u +"%Y-%m-%dT%H:%M:%S+00:00")"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --clover)
      CLOVER="$2"
      shift 2
      ;;
    --infection-summary)
      INFECTION="$2"
      shift 2
      ;;
    --phpmetrics)
      PHPMETRICS="$2"
      shift 2
      ;;
    --phpmetrics-summary)
      PHPMETRICS_SUMMARY="$2"
      shift 2
      ;;
    --output-dir)
      OUTPUT_DIR="$2"
      shift 2
      ;;
    --git-sha)
      GIT_SHA="$2"
      shift 2
      ;;
    --generated-at)
      GENERATED_AT="$2"
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

if [[ -z "$CLOVER" || -z "$INFECTION" || -z "$PHPMETRICS" || -z "$OUTPUT_DIR" ]]; then
  echo "error: --clover, --infection-summary, --phpmetrics, and --output-dir are required" >&2
  usage >&2
  exit 1
fi

require_cmd jq
require_cmd awk
require_cmd grep

for path in "$CLOVER" "$INFECTION" "$PHPMETRICS"; do
  [[ -f "$path" ]] || {
    echo "error: file not found: $path" >&2
    exit 1
  }
done

# Prefer the first metrics node (project-level in PHPUnit Clover).
STATEMENTS="$(grep -oE 'statements="[0-9]+"' "$CLOVER" | head -n 1 | grep -oE '[0-9]+')"
COVERED="$(grep -oE 'coveredstatements="[0-9]+"' "$CLOVER" | head -n 1 | grep -oE '[0-9]+')"
if [[ -z "$STATEMENTS" || "$STATEMENTS" -le 0 ]]; then
  echo "error: clover report has zero statements: $CLOVER" >&2
  exit 1
fi
COVERAGE="$(round1 "$(awk -v c="$COVERED" -v s="$STATEMENTS" 'BEGIN { print 100.0 * c / s }')")"

INFECTION_JSON="$(jq '
  .stats // .
  | {
      msi: (.msi | tonumber),
      coveredCodeMsi: (.coveredCodeMsi | tonumber),
      mutationCodeCoverage: (.mutationCodeCoverage | tonumber),
      totalMutants: (.totalMutantsCount | tonumber),
      killed: ((.killedCount // 0) | tonumber),
      escaped: ((.escapedCount // 0) | tonumber),
      notCovered: ((.notCoveredCount // 0) | tonumber)
    }
' "$INFECTION")"

MSI="$(round1 "$(jq -r '.msi' <<<"$INFECTION_JSON")")"
COVERED_MSI="$(round1 "$(jq -r '.coveredCodeMsi' <<<"$INFECTION_JSON")")"
MUTATION_COVERAGE="$(round1 "$(jq -r '.mutationCodeCoverage' <<<"$INFECTION_JSON")")"

PHPMETRICS_JSON="$(jq '
  [
    .[]
    | select((._type // "") | endswith("ClassMetric"))
  ] as $classes
  | if ($classes | length) == 0 then
      error("no ClassMetric entries")
    else
      {
        classes: ($classes | length),
        avgMi: (([$classes[] | .mi | tonumber] | add) / ($classes | length)),
        avgCcn: (
          if ([$classes[] | select(.ccn != null)] | length) == 0 then 0
          else (([$classes[] | select(.ccn != null) | .ccn | tonumber] | add)
            / ([$classes[] | select(.ccn != null)] | length))
          end
        )
      }
    end
' "$PHPMETRICS")"

AVG_MI="$(round1 "$(jq -r '.avgMi' <<<"$PHPMETRICS_JSON")")"
AVG_CCN="$(round2 "$(jq -r '.avgCcn' <<<"$PHPMETRICS_JSON")")"
CLASSES="$(jq -r '.classes' <<<"$PHPMETRICS_JSON")"
VIOLATIONS_JSON="null"

if [[ -n "$PHPMETRICS_SUMMARY" ]]; then
  [[ -f "$PHPMETRICS_SUMMARY" ]] || {
    echo "error: file not found: $PHPMETRICS_SUMMARY" >&2
    exit 1
  }
  if jq -e '.Complexity.avgCyclomaticComplexityByClass != null' "$PHPMETRICS_SUMMARY" >/dev/null; then
    AVG_CCN="$(round2 "$(jq -r '.Complexity.avgCyclomaticComplexityByClass' "$PHPMETRICS_SUMMARY")")"
  fi
  if jq -e '.Violations != null' "$PHPMETRICS_SUMMARY" >/dev/null; then
    VIOLATIONS_JSON="$(jq '.Violations | {
      critical: (.critical // 0 | tonumber),
      error: (.error // 0 | tonumber),
      warning: (.warning // 0 | tonumber),
      information: (.information // 0 | tonumber)
    }' "$PHPMETRICS_SUMMARY")"
  fi
fi

BADGES_DIR="${OUTPUT_DIR}/badges"
mkdir -p "$BADGES_DIR"

write_badge "${BADGES_DIR}/coverage.json" "coverage" "${COVERAGE}%" "$(color_for_pct "$COVERAGE")"
write_badge "${BADGES_DIR}/msi.json" "mutation score" "${MSI}%" "$(color_for_pct "$MSI")"
write_badge "${BADGES_DIR}/covered-msi.json" "covered MSI" "${COVERED_MSI}%" "$(color_for_pct "$COVERED_MSI")"
write_badge "${BADGES_DIR}/maintainability.json" "maintainability" "${AVG_MI}" "$(color_for_mi "$AVG_MI")"
write_badge "${BADGES_DIR}/complexity.json" "avg CCN" "${AVG_CCN}" "$(color_for_ccn "$AVG_CCN")"

SUMMARY="$(jq -n \
  --arg generatedAt "$GENERATED_AT" \
  --arg gitSha "$GIT_SHA" \
  --argjson coveragePercent "$COVERAGE" \
  --argjson msi "$MSI" \
  --argjson coveredCodeMsi "$COVERED_MSI" \
  --argjson mutationCodeCoverage "$MUTATION_COVERAGE" \
  --argjson totalMutants "$(jq -r '.totalMutants' <<<"$INFECTION_JSON")" \
  --argjson killed "$(jq -r '.killed' <<<"$INFECTION_JSON")" \
  --argjson escaped "$(jq -r '.escaped' <<<"$INFECTION_JSON")" \
  --argjson notCovered "$(jq -r '.notCovered' <<<"$INFECTION_JSON")" \
  --argjson avgMi "$AVG_MI" \
  --argjson avgCcn "$AVG_CCN" \
  --argjson classes "$CLASSES" \
  --argjson violations "$VIOLATIONS_JSON" \
  --slurpfile coverageBadge "${BADGES_DIR}/coverage.json" \
  --slurpfile msiBadge "${BADGES_DIR}/msi.json" \
  --slurpfile coveredMsiBadge "${BADGES_DIR}/covered-msi.json" \
  --slurpfile maintainabilityBadge "${BADGES_DIR}/maintainability.json" \
  --slurpfile complexityBadge "${BADGES_DIR}/complexity.json" \
  '{
    generatedAt: $generatedAt,
    gitSha: $gitSha,
    coveragePercent: $coveragePercent,
    infection: {
      msi: $msi,
      coveredCodeMsi: $coveredCodeMsi,
      mutationCodeCoverage: $mutationCodeCoverage,
      totalMutants: $totalMutants,
      killed: $killed,
      escaped: $escaped,
      notCovered: $notCovered
    },
    phpmetrics: {
      avgMaintainabilityIndex: $avgMi,
      avgCyclomaticComplexity: $avgCcn,
      classes: $classes,
      violations: $violations
    },
    badges: {
      "coverage.json": $coverageBadge[0],
      "msi.json": $msiBadge[0],
      "covered-msi.json": $coveredMsiBadge[0],
      "maintainability.json": $maintainabilityBadge[0],
      "complexity.json": $complexityBadge[0]
    }
  }')"

printf '%s\n' "$SUMMARY" | tee "${OUTPUT_DIR}/summary.json"
