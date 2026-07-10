#!/usr/bin/env python3
"""Publish generated badge JSON to the orphan metrics branch."""

from __future__ import annotations

import argparse
import os
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path


def run(cmd: list[str], **kwargs) -> subprocess.CompletedProcess[str]:
    return subprocess.run(cmd, check=True, text=True, **kwargs)


def main(argv: list[str]) -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--source-dir", type=Path, required=True)
    parser.add_argument("--branch", default=os.environ.get("METRICS_BRANCH", "metrics"))
    parser.add_argument("--git-sha", default=os.environ.get("GITHUB_SHA", "unknown"))
    args = parser.parse_args(argv)

    source_dir = args.source_dir
    badges_src = source_dir / "badges"
    summary_src = source_dir / "summary.json"
    if not summary_src.exists():
        raise SystemExit(f"missing summary: {summary_src}")
    if not badges_src.is_dir():
        raise SystemExit(f"missing badges dir: {badges_src}")

    run(["git", "config", "user.name", "github-actions[bot]"])
    run(["git", "config", "user.email", "github-actions[bot]@users.noreply.github.com"])

    worktree = Path(tempfile.mkdtemp(prefix="metrics-branch-"))
    short_sha = args.git_sha[:7]

    try:
        remote = subprocess.run(
            ["git", "ls-remote", "--exit-code", "--heads", "origin", args.branch],
            check=False,
            capture_output=True,
            text=True,
        )
        if remote.returncode == 0:
            run(["git", "fetch", "origin", args.branch])
            run(["git", "worktree", "add", "-B", args.branch, str(worktree), f"origin/{args.branch}"])
        else:
            run(["git", "worktree", "add", "--detach", str(worktree)])
            run(["git", "checkout", "--orphan", args.branch], cwd=worktree)
            subprocess.run(["git", "rm", "-rf", "."], cwd=worktree, check=False, capture_output=True)

        badges_dir = worktree / "docs" / "metrics" / "badges"
        badges_dir.mkdir(parents=True, exist_ok=True)
        shutil.copy(summary_src, worktree / "docs" / "metrics" / "summary.json")
        for path in sorted(badges_src.glob("*.json")):
            shutil.copy(path, badges_dir / path.name)

        (worktree / "README.md").write_text(
            "# Server metrics\n\n"
            "このブランチは `Server Metrics` workflow が生成するバッジ JSON / summary 専用です。\n"
            "ソースコードの変更は含めません。\n",
            encoding="utf-8",
        )

        run(["git", "add", "README.md", "docs/metrics"], cwd=worktree)
        cached = subprocess.run(
            ["git", "diff", "--cached", "--quiet"],
            cwd=worktree,
            check=False,
        )
        if cached.returncode == 0:
            print("metrics branch is already up to date")
            return 0

        run(
            ["git", "commit", "-m", f"chore: update server metrics badges ({short_sha})"],
            cwd=worktree,
        )
        run(["git", "push", "origin", f"HEAD:{args.branch}"], cwd=worktree)
        return 0
    finally:
        remove = subprocess.run(
            ["git", "worktree", "remove", "--force", str(worktree)],
            check=False,
            capture_output=True,
            text=True,
        )
        if remove.returncode != 0:
            shutil.rmtree(worktree, ignore_errors=True)


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
