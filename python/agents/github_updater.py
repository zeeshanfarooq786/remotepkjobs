#!/usr/bin/env python3
"""Fetch GitHub stats for self-hosted alternatives and sync to DevRates via internal API."""

from __future__ import annotations

import json
import logging
import os
import sys
import time
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
from dotenv import load_dotenv

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import config  # noqa: E402

ALTERNATIVES_CONFIG = Path(__file__).resolve().parent.parent / "config" / "alternatives.json"
REQUEST_DELAY_SECONDS = 1


@dataclass
class RunStats:
    total: int = 0
    updated: int = 0
    skipped: int = 0
    errors: int = 0
    rate_limited: bool = False


def setup_logging() -> None:
    log_dir = Path(__file__).resolve().parent.parent / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"github_updater_{datetime.now():%Y-%m-%d}.log"

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def load_alternatives() -> list[dict[str, Any]]:
    with ALTERNATIVES_CONFIG.open(encoding="utf-8") as handle:
        payload = json.load(handle)

    if not isinstance(payload, list):
        raise RuntimeError("alternatives.json must be a JSON array")

    return payload


def github_headers() -> dict[str, str]:
    headers = {
        "Accept": "application/vnd.github+json",
        "X-GitHub-Api-Version": "2022-11-28",
        "User-Agent": "DevRates-GitHub-Updater/1.0",
    }

    token = config.GITHUB_TOKEN
    if token:
        headers["Authorization"] = f"Bearer {token}"

    return headers


def api_headers() -> dict[str, str]:
    token = config.INTERNAL_API_TOKEN
    if not token:
        raise RuntimeError("INTERNAL_API_TOKEN is not set in .env")

    return {
        "X-Internal-Token": token,
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


def is_rate_limited(response: requests.Response) -> bool:
    if response.status_code == 403:
        remaining = response.headers.get("X-RateLimit-Remaining", "")
        if remaining == "0":
            return True
        if "rate limit" in response.text.lower():
            return True

    return response.status_code == 429


def fetch_repo_stats(session: requests.Session, repo: str) -> dict[str, Any] | None:
    url = f"https://api.github.com/repos/{repo}"
    logging.info("Fetching GitHub stats for %s", repo)

    response = session.get(url, headers=github_headers(), timeout=30)

    if is_rate_limited(response):
        reset_at = response.headers.get("X-RateLimit-Reset", "unknown")
        logging.error(
            "GitHub rate limit hit for %s (reset epoch: %s). Stopping run.",
            repo,
            reset_at,
        )
        return {"rate_limited": True}

    if response.status_code == 404:
        logging.warning("GitHub repo not found: %s", repo)
        return None

    if not response.ok:
        logging.error("GitHub API error %s for %s: %s", response.status_code, repo, response.text)
        return None

    payload = response.json()
    pushed_at = payload.get("pushed_at")

    return {
        "github_stars": int(payload.get("stargazers_count") or 0),
        "github_forks": int(payload.get("forks_count") or 0),
        "last_commit": pushed_at,
        "open_issues": int(payload.get("open_issues_count") or 0),
        "language": payload.get("language"),
    }


def post_update(session: requests.Session, paid_tool: str, stats: dict[str, Any]) -> bool:
    endpoint = f"{config.LARAVEL_API_URL}/api/internal/alternatives/update"
    body = {
        "paid_tool": paid_tool,
        "github_stars": stats["github_stars"],
        "github_forks": stats["github_forks"],
        "last_commit": stats.get("last_commit"),
        "open_issues": stats.get("open_issues", 0),
        "language": stats.get("language"),
    }

    try:
        response = session.post(endpoint, headers=api_headers(), json=body, timeout=30)
    except requests.RequestException as exc:
        logging.error("Network error posting %s: %s", paid_tool, exc)
        return False

    if response.status_code == 200:
        logging.info(
            "Updated %s — stars=%s forks=%s issues=%s lang=%s",
            paid_tool,
            body["github_stars"],
            body["github_forks"],
            body["open_issues"],
            body["language"],
        )
        return True

    if response.status_code == 404:
        logging.warning("Alternative not found in database: %s", paid_tool)
        return False

    logging.error(
        "Laravel API error %s for %s: %s",
        response.status_code,
        paid_tool,
        response.text,
    )
    return False


def run() -> int:
    project_root = Path(__file__).resolve().parent.parent.parent
    load_dotenv(project_root / ".env")

    config.LARAVEL_API_URL = os.environ.get("APP_URL", config.LARAVEL_API_URL).rstrip("/")
    config.INTERNAL_API_TOKEN = os.environ.get("INTERNAL_API_TOKEN", config.INTERNAL_API_TOKEN)
    config.GITHUB_TOKEN = os.environ.get("GITHUB_TOKEN", getattr(config, "GITHUB_TOKEN", ""))

    setup_logging()
    stats = RunStats()

    if config.GITHUB_TOKEN:
        logging.info("Using authenticated GitHub API (5000 req/hour)")
    else:
        logging.warning("GITHUB_TOKEN not set — limited to 60 GitHub requests/hour")

    alternatives = load_alternatives()
    stats.total = len(alternatives)
    logging.info("Loaded %d alternatives from %s", stats.total, ALTERNATIVES_CONFIG)

    with requests.Session() as session:
        for index, entry in enumerate(alternatives):
            paid_tool = entry.get("paid_tool")
            repo = entry.get("repo")

            if not paid_tool or not repo:
                stats.skipped += 1
                logging.warning("Skipping invalid config entry: %s", entry)
                continue

            repo_stats = fetch_repo_stats(session, repo)

            if repo_stats is None:
                stats.skipped += 1
            elif repo_stats.get("rate_limited"):
                stats.rate_limited = True
                break
            elif post_update(session, paid_tool, repo_stats):
                stats.updated += 1
            else:
                stats.errors += 1

            if index < len(alternatives) - 1 and not stats.rate_limited:
                time.sleep(REQUEST_DELAY_SECONDS)

    logging.info(
        "Run complete — total=%d updated=%d skipped=%d errors=%d rate_limited=%s",
        stats.total,
        stats.updated,
        stats.skipped,
        stats.errors,
        stats.rate_limited,
    )

    return 0 if stats.errors == 0 and not stats.rate_limited else 1


def main() -> int:
    try:
        return run()
    except Exception as exc:
        logging.exception("GitHub updater failed: %s", exc)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
