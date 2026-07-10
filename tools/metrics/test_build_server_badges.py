#!/usr/bin/env python3
"""Smoke checks for build-server-badges.py using fixtures."""

from __future__ import annotations

import json
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SCRIPT = Path(__file__).resolve().parent / "build-server-badges.py"
FIXTURES = Path(__file__).resolve().parent / "fixtures"


class BuildServerBadgesTest(unittest.TestCase):
    def test_fixture_run_writes_expected_badges(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            output_dir = Path(tmp)
            completed = subprocess.run(
                [
                    sys.executable,
                    str(SCRIPT),
                    "--clover",
                    str(FIXTURES / "clover.xml"),
                    "--infection-summary",
                    str(FIXTURES / "infection-summary.json"),
                    "--phpmetrics",
                    str(FIXTURES / "phpmetrics.json"),
                    "--phpmetrics-summary",
                    str(FIXTURES / "phpmetrics-summary.json"),
                    "--output-dir",
                    str(output_dir),
                    "--git-sha",
                    "fixture",
                    "--generated-at",
                    "2026-07-10T00:00:00+00:00",
                ],
                cwd=ROOT,
                check=True,
                capture_output=True,
                text=True,
            )
            summary = json.loads(completed.stdout)
            self.assertEqual(summary["coveragePercent"], 85.0)
            self.assertEqual(summary["infection"]["msi"], 83.0)
            self.assertEqual(summary["phpmetrics"]["avgMaintainabilityIndex"], 85.2)

            coverage_badge = json.loads((output_dir / "badges" / "coverage.json").read_text())
            self.assertEqual(coverage_badge["schemaVersion"], 1)
            self.assertEqual(coverage_badge["message"], "85.0%")
            self.assertEqual(coverage_badge["color"], "yellow")


if __name__ == "__main__":
    unittest.main()
