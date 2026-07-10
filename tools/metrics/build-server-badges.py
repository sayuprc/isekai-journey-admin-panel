#!/usr/bin/env python3
"""Build shields.io endpoint JSON and a summary from server metric reports."""

from __future__ import annotations

import argparse
import json
import statistics
import sys
import xml.etree.ElementTree as ET
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


def round_pct(value: float) -> float:
    return round(value, 1)


def color_for_pct(value: float) -> str:
    if value < 50:
        return "red"
    if value < 70:
        return "orange"
    if value < 90:
        return "yellow"
    return "brightgreen"


def color_for_mi(value: float) -> str:
    # PhpMetrics MI is roughly 0–100; higher is better.
    if value < 65:
        return "red"
    if value < 85:
        return "orange"
    return "brightgreen"


def color_for_ccn(value: float) -> str:
    # Lower average cyclomatic complexity is better.
    if value >= 10:
        return "red"
    if value >= 7:
        return "orange"
    if value >= 5:
        return "yellow"
    return "brightgreen"


def badge(label: str, message: str, color: str) -> dict[str, Any]:
    return {
        "schemaVersion": 1,
        "label": label,
        "message": message,
        "color": color,
    }


def parse_clover_coverage(path: Path) -> float:
    root = ET.parse(path).getroot()
    metrics_nodes = root.findall(".//metrics")
    if not metrics_nodes:
        raise ValueError(f"no <metrics> in clover report: {path}")

    statements = 0
    covered = 0
    for node in metrics_nodes:
        statements += int(node.attrib.get("statements", 0))
        covered += int(node.attrib.get("coveredstatements", 0))

    # Prefer the project-level metrics node when present.
    project_metrics = root.find("./project/metrics")
    if project_metrics is not None:
        statements = int(project_metrics.attrib.get("statements", 0))
        covered = int(project_metrics.attrib.get("coveredstatements", 0))

    if statements <= 0:
        raise ValueError(f"clover report has zero statements: {path}")

    return round_pct(100.0 * covered / statements)


def parse_infection_summary(path: Path) -> dict[str, float | int]:
    data = json.loads(path.read_text(encoding="utf-8"))
    stats = data.get("stats", data)
    required = ("msi", "coveredCodeMsi", "mutationCodeCoverage", "totalMutantsCount")
    missing = [key for key in required if key not in stats]
    if missing:
        raise ValueError(f"infection summary missing keys {missing}: {path}")

    return {
        "msi": float(stats["msi"]),
        "covered_code_msi": float(stats["coveredCodeMsi"]),
        "mutation_code_coverage": float(stats["mutationCodeCoverage"]),
        "total_mutants": int(stats["totalMutantsCount"]),
        "killed": int(stats.get("killedCount", 0)),
        "escaped": int(stats.get("escapedCount", 0)),
        "not_covered": int(stats.get("notCoveredCount", 0)),
    }


def parse_phpmetrics(path: Path) -> dict[str, float | int]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        raise ValueError(f"phpmetrics JSON must be an object: {path}")

    mis: list[float] = []
    ccns: list[float] = []
    class_count = 0
    for metric in data.values():
        if not isinstance(metric, dict):
            continue
        metric_type = str(metric.get("_type", ""))
        if not metric_type.endswith("ClassMetric"):
            continue
        class_count += 1
        if metric.get("mi") is not None:
            mis.append(float(metric["mi"]))
        if metric.get("ccn") is not None:
            ccns.append(float(metric["ccn"]))

    if not mis:
        raise ValueError(f"no ClassMetric.mi values in phpmetrics JSON: {path}")

    return {
        "avg_mi": round(statistics.fmean(mis), 1),
        "avg_ccn": round(statistics.fmean(ccns), 2) if ccns else 0.0,
        "classes": class_count,
    }


def parse_phpmetrics_summary(path: Path | None) -> dict[str, Any]:
    if path is None or not path.exists():
        return {}

    data = json.loads(path.read_text(encoding="utf-8"))
    complexity = data.get("Complexity", {})
    violations = data.get("Violations", {})
    result: dict[str, Any] = {}
    if "avgCyclomaticComplexityByClass" in complexity:
        result["avg_ccn"] = round(float(complexity["avgCyclomaticComplexityByClass"]), 2)
    if violations:
        result["violations"] = {
            "critical": int(violations.get("critical", 0)),
            "error": int(violations.get("error", 0)),
            "warning": int(violations.get("warning", 0)),
            "information": int(violations.get("information", 0)),
        }
    return result


def write_json(path: Path, payload: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def build(
    *,
    clover: Path,
    infection: Path,
    phpmetrics: Path,
    phpmetrics_summary: Path | None,
    output_dir: Path,
    git_sha: str,
    generated_at: str,
) -> dict[str, Any]:
    coverage = parse_clover_coverage(clover)
    infection_stats = parse_infection_summary(infection)
    metrics_stats = parse_phpmetrics(phpmetrics)
    summary_extra = parse_phpmetrics_summary(phpmetrics_summary)
    if "avg_ccn" in summary_extra:
        metrics_stats["avg_ccn"] = float(summary_extra["avg_ccn"])

    msi = round_pct(float(infection_stats["msi"]))
    covered_msi = round_pct(float(infection_stats["covered_code_msi"]))
    avg_mi = float(metrics_stats["avg_mi"])
    avg_ccn = float(metrics_stats["avg_ccn"])

    badges = {
        "coverage.json": badge("coverage", f"{coverage}%", color_for_pct(coverage)),
        "msi.json": badge("mutation score", f"{msi}%", color_for_pct(msi)),
        "covered-msi.json": badge("covered MSI", f"{covered_msi}%", color_for_pct(covered_msi)),
        "maintainability.json": badge("maintainability", f"{avg_mi}", color_for_mi(avg_mi)),
        "complexity.json": badge("avg CCN", f"{avg_ccn}", color_for_ccn(avg_ccn)),
    }

    badges_dir = output_dir / "badges"
    for name, payload in badges.items():
        write_json(badges_dir / name, payload)

    summary = {
        "generatedAt": generated_at,
        "gitSha": git_sha,
        "coveragePercent": coverage,
        "infection": {
            "msi": msi,
            "coveredCodeMsi": covered_msi,
            "mutationCodeCoverage": round_pct(float(infection_stats["mutation_code_coverage"])),
            "totalMutants": infection_stats["total_mutants"],
            "killed": infection_stats["killed"],
            "escaped": infection_stats["escaped"],
            "notCovered": infection_stats["not_covered"],
        },
        "phpmetrics": {
            "avgMaintainabilityIndex": avg_mi,
            "avgCyclomaticComplexity": avg_ccn,
            "classes": metrics_stats["classes"],
            "violations": summary_extra.get("violations"),
        },
        "badges": {name: payload for name, payload in badges.items()},
    }
    write_json(output_dir / "summary.json", summary)
    return summary


def main(argv: list[str]) -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--clover", type=Path, required=True)
    parser.add_argument("--infection-summary", type=Path, required=True)
    parser.add_argument("--phpmetrics", type=Path, required=True)
    parser.add_argument("--phpmetrics-summary", type=Path, default=None)
    parser.add_argument("--output-dir", type=Path, required=True)
    parser.add_argument("--git-sha", default="unknown")
    parser.add_argument(
        "--generated-at",
        default=datetime.now(timezone.utc).replace(microsecond=0).isoformat(),
    )
    args = parser.parse_args(argv)

    summary = build(
        clover=args.clover,
        infection=args.infection_summary,
        phpmetrics=args.phpmetrics,
        phpmetrics_summary=args.phpmetrics_summary,
        output_dir=args.output_dir,
        git_sha=args.git_sha,
        generated_at=args.generated_at,
    )
    print(json.dumps(summary, indent=2, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
