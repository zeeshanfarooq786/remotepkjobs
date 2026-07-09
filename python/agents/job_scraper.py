#!/usr/bin/env python3
"""Fetch remote developer jobs from public APIs and ingest via DevRates internal API."""

from __future__ import annotations

import logging
import re
import sys
from collections import defaultdict
from dataclasses import dataclass, field
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
from dotenv import load_dotenv

# Allow imports from python/config.py when run as a script.
sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import config  # noqa: E402


@dataclass
class RunStats:
    fetched: int = 0
    filtered: int = 0
    posted: int = 0
    skipped_duplicates: int = 0
    dropped: int = 0
    errors: int = 0
    posted_by_stack: dict[str, list[int]] = field(default_factory=lambda: defaultdict(list))


def setup_logging() -> None:
    log_dir = Path(__file__).resolve().parent.parent / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"job_scraper_{datetime.now():%Y-%m-%d}.log"

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def normalize_text(value: Any) -> str:
    return re.sub(r"\s+", " ", str(value or "")).strip().lower()


def is_blocked_title(title: str) -> bool:
    title_lower = str(title or "").lower()
    return any(keyword in title_lower for keyword in config.BLOCKED_TITLE_KEYWORDS)


def is_blocked_location(country: str, remote_type: str) -> bool:
    country_lower = str(country or "").lower()
    is_fully_remote = "remote" in str(remote_type or "").lower()
    is_blocked_location_match = any(blocked in country_lower for blocked in config.BLOCKED_COUNTRIES)
    return is_blocked_location_match and not is_fully_remote


def passes_content_filters(job: dict[str, Any]) -> bool:
    if is_blocked_title(str(job.get("title", ""))):
        return False

    if is_blocked_location(str(job.get("country", "")), str(job.get("remote_type", ""))):
        return False

    return True


def match_stack(text_blobs: list[str]) -> str | None:
    haystack = normalize_text(" ".join(text_blobs))

    for keyword in config.TARGET_STACKS:
        if keyword == "node":
            if re.search(r"\bnode\.?js\b", haystack) or re.search(r"\bnode\b", haystack):
                return config.STACK_LABELS["node"]
            continue

        if re.search(rf"\b{re.escape(keyword)}\b", haystack):
            return config.STACK_LABELS.get(keyword, keyword.title())

    return None


def to_usd(amount: float | int | None, currency: str | None) -> int | None:
    if amount is None:
        return None

    try:
        value = float(amount)
    except (TypeError, ValueError):
        return None

    if value <= 0:
        return None

    code = (currency or "USD").upper()
    rate = config.EXCHANGE_RATES_TO_USD.get(code)
    if rate is None:
        logging.warning("Unknown currency %s — treating amount as USD", code)
        rate = 1.0

    return int(round(value * rate))


def monthly_usd_salary(
    salary_min: float | int | None,
    salary_max: float | int | None,
    currency: str | None,
    *,
    annual_hint: bool = False,
) -> tuple[int | None, int | None]:
    min_usd = to_usd(salary_min, currency)
    max_usd = to_usd(salary_max, currency)

    if annual_hint or (max(min_usd or 0, max_usd or 0) > 20000):
        if min_usd:
            min_usd = int(round(min_usd / 12))
        if max_usd:
            max_usd = int(round(max_usd / 12))

    if min_usd and max_usd and min_usd > max_usd:
        min_usd, max_usd = max_usd, min_usd

    return min_usd, max_usd


def mid_salary(salary_min: int | None, salary_max: int | None) -> int | None:
    if salary_min and salary_max:
        return int(round((salary_min + salary_max) / 2))
    return salary_min or salary_max


def parse_posted_at(value: Any) -> str | None:
    if value is None:
        return None

    if isinstance(value, (int, float)):
        try:
            return datetime.fromtimestamp(value, tz=timezone.utc).isoformat()
        except (OSError, OverflowError, ValueError):
            return None

    text = str(value).strip()
    if not text:
        return None

    try:
        if text.endswith("Z"):
            text = text[:-1] + "+00:00"
        return datetime.fromisoformat(text).astimezone(timezone.utc).isoformat()
    except ValueError:
        return None


def normalize_job(raw: dict[str, Any]) -> dict[str, Any] | None:
    stack = raw.get("stack")
    if not stack:
        return None

    salary_min, salary_max = monthly_usd_salary(
        raw.get("salary_min"),
        raw.get("salary_max"),
        raw.get("currency"),
        annual_hint=raw.get("annual_hint", False),
    )

    source_url = raw.get("source_url")
    title = raw.get("title")
    company = raw.get("company")

    if not source_url or not title or not company:
        return None

    return {
        "title": str(title)[:255],
        "company": str(company)[:255],
        "salary_min": salary_min,
        "salary_max": salary_max,
        "currency": "USD",
        "stack": stack,
        "country": raw.get("country") or "Remote",
        "remote_type": raw.get("remote_type") or "fully_remote",
        "source_url": str(source_url)[:500],
        "posted_at": parse_posted_at(raw.get("posted_at")),
    }


def fetch_remoteok(session: requests.Session) -> tuple[int, list[dict[str, Any]]]:
    source = next(s for s in config.SOURCES if s["type"] == "remoteok")
    response = session.get(
        source["url"],
        headers={"User-Agent": "DevRates Job Scraper/1.0"},
        timeout=45,
    )
    response.raise_for_status()
    payload = response.json()

    jobs: list[dict[str, Any]] = []
    fetched = 0

    for item in payload:
        if not isinstance(item, dict) or not item.get("position"):
            continue

        fetched += 1
        tags = [str(tag) for tag in (item.get("tags") or [])]
        stack = match_stack([item.get("position", ""), item.get("description", ""), *tags])
        if not stack:
            continue

        candidate = {
            "title": item.get("position"),
            "company": item.get("company"),
            "salary_min": item.get("salary_min"),
            "salary_max": item.get("salary_max"),
            "currency": "USD",
            "stack": stack,
            "country": item.get("location") or "Remote",
            "remote_type": "fully_remote",
            "source_url": item.get("url"),
            "posted_at": item.get("date"),
        }

        if not passes_content_filters(candidate):
            continue

        jobs.append(candidate)

    return fetched, jobs


def fetch_himalayas(session: requests.Session) -> tuple[int, list[dict[str, Any]]]:
    source = next(s for s in config.SOURCES if s["type"] == "himalayas")
    jobs: list[dict[str, Any]] = []
    fetched = 0

    for page in range(config.HIMALAYAS_MAX_PAGES):
        offset = page * config.HIMALAYAS_PAGE_LIMIT
        response = session.get(
            source["url"],
            params={"limit": config.HIMALAYAS_PAGE_LIMIT, "offset": offset},
            timeout=45,
        )
        response.raise_for_status()
        payload = response.json()
        batch = payload.get("jobs") or []

        if not batch:
            break

        for item in batch:
            fetched += 1
            categories = [str(c) for c in (item.get("categories") or [])]
            parent_categories = [str(c) for c in (item.get("parentCategories") or [])]
            stack = match_stack(
                [
                    item.get("title", ""),
                    item.get("excerpt", ""),
                    item.get("description", ""),
                    *categories,
                    *parent_categories,
                ]
            )
            if not stack:
                continue

            candidate = {
                "title": item.get("title"),
                "company": item.get("companyName"),
                "salary_min": item.get("minSalary"),
                "salary_max": item.get("maxSalary"),
                "currency": item.get("currency") or "USD",
                "stack": stack,
                "country": "Remote",
                "remote_type": "fully_remote",
                "source_url": item.get("applicationLink") or item.get("guid"),
                "posted_at": item.get("pubDate"),
                "annual_hint": True,
            }

            if not passes_content_filters(candidate):
                continue

            jobs.append(candidate)

        if len(batch) < config.HIMALAYAS_PAGE_LIMIT:
            break

    return fetched, jobs


def fetch_arbeitnow(session: requests.Session) -> tuple[int, list[dict[str, Any]]]:
    source = next(s for s in config.SOURCES if s["type"] == "arbeitnow")
    response = session.get(source["url"], timeout=45)
    response.raise_for_status()
    payload = response.json()

    jobs: list[dict[str, Any]] = []
    batch = payload.get("data") or []
    fetched = len(batch)

    for item in batch:
        tags = [str(tag) for tag in (item.get("tags") or [])]
        stack = match_stack(
            [
                item.get("title", ""),
                item.get("description", ""),
                *tags,
            ]
        )
        if not stack:
            continue

        remote_type = "fully_remote" if item.get("remote") else "hybrid_remote"
        country = "Remote" if item.get("remote") else (item.get("location") or "Remote")

        candidate = {
            "title": item.get("title"),
            "company": item.get("company_name"),
            "salary_min": None,
            "salary_max": None,
            "currency": "USD",
            "stack": stack,
            "country": country,
            "remote_type": remote_type,
            "source_url": item.get("url"),
            "posted_at": item.get("created_at"),
        }

        if not passes_content_filters(candidate):
            continue

        jobs.append(candidate)

    return fetched, jobs


def fetch_all_jobs(session: requests.Session) -> tuple[int, list[dict[str, Any]]]:
    fetchers = {
        "remoteok": fetch_remoteok,
        "himalayas": fetch_himalayas,
        "arbeitnow": fetch_arbeitnow,
    }

    all_jobs: list[dict[str, Any]] = []
    total_fetched = 0

    for source in config.SOURCES:
        name = source["name"]
        fetcher = fetchers.get(source["type"])
        if fetcher is None:
            logging.warning("No fetcher registered for source type %s", source["type"])
            continue

        try:
            fetched, jobs = fetcher(session)
            total_fetched += fetched
            logging.info("Fetched %d jobs (%d matched stacks) from %s", fetched, len(jobs), name)
            all_jobs.extend(jobs)
        except requests.RequestException as exc:
            logging.error("Failed to fetch from %s: %s", name, exc)
        except Exception as exc:
            logging.exception("Unexpected error fetching from %s: %s", name, exc)

    return total_fetched, all_jobs


def api_headers() -> dict[str, str]:
    token = config.INTERNAL_API_TOKEN
    if not token:
        raise RuntimeError("INTERNAL_API_TOKEN is not set in .env")

    return {
        "X-Internal-Token": token,
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


def post_job(session: requests.Session, job: dict[str, Any], stats: RunStats) -> bool:
    endpoint = f"{config.LARAVEL_API_URL}/api/internal/jobs"

    try:
        response = session.post(endpoint, headers=api_headers(), json=job, timeout=30)
    except requests.RequestException as exc:
        stats.errors += 1
        logging.error("Network error posting %s: %s", job.get("source_url"), exc)
        return False

    if response.status_code == 201:
        stats.posted += 1
        mid = mid_salary(job.get("salary_min"), job.get("salary_max"))
        if mid:
            stats.posted_by_stack[job["stack"]].append(mid)
        logging.info("Posted: %s at %s", job.get("title"), job.get("company"))
        return True

    if response.status_code == 409:
        stats.skipped_duplicates += 1
        logging.info("Skipped duplicate: %s", job.get("source_url"))
        return False

    stats.errors += 1
    logging.error(
        "API error %s for %s — response: %s",
        response.status_code,
        job.get("source_url"),
        response.text,
    )
    return False


def post_salary_snapshots(session: requests.Session, stats: RunStats) -> None:
    endpoint = f"{config.LARAVEL_API_URL}/api/internal/salary-snapshot"

    for stack, salaries in stats.posted_by_stack.items():
        if not salaries:
            continue

        body = {
            "stack": stack.lower(),
            "country": "remote",
            "avg_salary": int(round(sum(salaries) / len(salaries))),
            "sample_size": len(salaries),
            "recorded_at": datetime.now(timezone.utc).isoformat(),
        }

        try:
            response = session.post(endpoint, headers=api_headers(), json=body, timeout=30)
        except requests.RequestException as exc:
            stats.errors += 1
            logging.error("Network error posting salary snapshot for %s: %s", stack, exc)
            continue

        if response.status_code == 201:
            logging.info(
                "Salary snapshot posted for %s: avg=$%s sample=%d",
                stack,
                body["avg_salary"],
                body["sample_size"],
            )
            continue

        stats.errors += 1
        logging.error(
            "Salary snapshot API error %s for %s — response: %s",
            response.status_code,
            stack,
            response.text,
        )


def run() -> int:
    project_root = Path(__file__).resolve().parent.parent.parent
    load_dotenv(project_root / ".env")

    # Refresh config values after .env load.
    config.LARAVEL_API_URL = __import__("os").environ.get("APP_URL", config.LARAVEL_API_URL).rstrip("/")
    config.INTERNAL_API_TOKEN = __import__("os").environ.get("INTERNAL_API_TOKEN", config.INTERNAL_API_TOKEN)

    setup_logging()
    stats = RunStats()
    seen_urls: set[str] = set()

    logging.info("Starting job scraper run")

    with requests.Session() as session:
        stats.fetched, raw_jobs = fetch_all_jobs(session)
        stats.filtered = len(raw_jobs)

        for raw in raw_jobs:
            normalized = normalize_job(raw)
            if normalized is None:
                stats.dropped += 1
                continue

            source_url = normalized["source_url"]
            if source_url in seen_urls:
                stats.skipped_duplicates += 1
                logging.info("Skipped in-batch duplicate: %s", source_url)
                continue

            seen_urls.add(source_url)
            post_job(session, normalized, stats)

        if stats.posted > 0:
            post_salary_snapshots(session, stats)

    logging.info(
        "Run complete — fetched=%d filtered=%d posted=%d skipped_duplicates=%d dropped=%d errors=%d",
        stats.fetched,
        stats.filtered,
        stats.posted,
        stats.skipped_duplicates,
        stats.dropped,
        stats.errors,
    )

    return 0 if stats.errors == 0 else 1


def main() -> int:
    try:
        return run()
    except Exception as exc:
        logging.exception("Job scraper failed: %s", exc)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
