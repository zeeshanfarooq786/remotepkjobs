#!/usr/bin/env python3
"""Discover trending dev topics and publish one humanized blog post per run."""

from __future__ import annotations

import hashlib
import logging
import os
import re
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
from dotenv import load_dotenv

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import config  # noqa: E402
from blog_image import generate_hero_image  # noqa: E402

RELEVANCE_KEYWORDS = [
    "remote",
    "developer",
    "dev",
    "freelanc",
    "upwork",
    "fiverr",
    "salary",
    "laravel",
    "python",
    "react",
    "node",
    "javascript",
    "typescript",
    "php",
    "software",
    "engineer",
    "contract",
    "saas",
    "open source",
    "self-host",
    "startup",
    "ai",
    "llm",
    "coding",
    "programming",
    "hire",
    "job",
    "career",
]

NEGATIVE_SIGNALS = [
    "never",
    "avoid",
    "stop using",
    "quit",
    "hate",
    "problem with",
    "why x is bad",
    "is dead",
    "is dying",
    "don't use",
    "do not use",
]

BANNED_PHRASES = [
    "in today's",
    "it is worth noting",
    "furthermore",
    "in conclusion",
    "navigating",
    "delve into",
    "ever-evolving",
    "fast-paced",
    "let's explore",
    "in this article",
    "takeaways",
    "silicon valley",
    "not just news",
    "day-to-day work",
]

WRITING_RULES = """
Write like a Pakistani freelance developer explaining money to a friend.
Rules:
- Vary sentence length. Short punch. Then a longer one with a real number.
- Use local context: Payoneer, Wise, HBL, PKR, FBR, JazzCash when relevant.
- Never write negative titles about Upwork, Fiverr, or Payoneer.
- Frame from opportunity and practical money advice.
- No AI filler phrases. No parallel bullet lists with identical grammar.
- Maximum two bold phrases in the entire post.
- Include one real stat from site_context when available.
- First sentence must be a specific money situation, not a global trend statement.
"""


def setup_logging() -> None:
    log_dir = Path(__file__).resolve().parent.parent / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"blog_generator_{datetime.now():%Y-%m-%d}.log"

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def normalize_title(title: str) -> str:
    cleaned = re.sub(r"[^\w\s]", " ", title.lower())
    return re.sub(r"\s+", " ", cleaned).strip()


def topic_key(title: str) -> str:
    return hashlib.sha256(normalize_title(title).encode("utf-8")).hexdigest()


def published_topic_key(reframed_title: str) -> str:
    """Dedup key based on what readers see — blocks same title twice."""
    return topic_key(reframed_title)


def is_relevant(title: str, tags: list[str] | None = None) -> bool:
    haystack = normalize_title(f"{title} {' '.join(tags or [])}")
    return any(keyword in haystack for keyword in RELEVANCE_KEYWORDS)


def is_negative_title(title: str) -> bool:
    lower = title.lower()
    return any(signal in lower for signal in NEGATIVE_SIGNALS)


def api_headers() -> dict[str, str]:
    token = config.INTERNAL_API_TOKEN
    if not token:
        raise RuntimeError("INTERNAL_API_TOKEN is not set in .env")

    return {
        "X-Internal-Token": token,
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


def fetch_existing_keys(session: requests.Session) -> set[str]:
    url = f"{config.LARAVEL_API_URL}/api/internal/blog-posts/topic-keys"
    response = session.get(url, headers=api_headers(), timeout=30)
    response.raise_for_status()
    keys = response.json().get("topic_keys", [])
    logging.info("Loaded %s existing topic keys", len(keys))
    return set(keys)


def fetch_existing_titles(session: requests.Session) -> set[str]:
    url = f"{config.LARAVEL_API_URL}/api/internal/blog-posts"
    response = session.get(url, headers=api_headers(), timeout=30)
    response.raise_for_status()
    titles = {
        normalize_title(str(post.get("title", "")))
        for post in response.json().get("posts", [])
        if post.get("title")
    }
    logging.info("Loaded %s existing published titles", len(titles))
    return titles


def fetch_site_context(session: requests.Session) -> dict[str, Any]:
    url = f"{config.LARAVEL_API_URL}/api/internal/blog-posts/context"
    try:
        response = session.get(url, headers=api_headers(), timeout=20)
        response.raise_for_status()
        return response.json()
    except requests.RequestException as exc:
        logging.warning("Could not load site context: %s", exc)
        return {"active_jobs": 0, "salary_snapshots": []}


def fetch_hn_topics(session: requests.Session) -> list[dict[str, Any]]:
    topics: list[dict[str, Any]] = []
    queries = ["remote developer", "freelance developer", "laravel", "upwork", "software salary"]

    for query in queries:
        url = "https://hn.algolia.com/api/v1/search"
        params = {"query": query, "tags": "story", "hitsPerPage": 15}
        try:
            response = session.get(url, params=params, timeout=20)
            response.raise_for_status()
            for hit in response.json().get("hits", []):
                title = str(hit.get("title") or "").strip()
                if not title or not is_relevant(title) or is_negative_title(title):
                    continue
                topics.append(
                    {
                        "title": title,
                        "url": hit.get("url") or f"https://news.ycombinator.com/item?id={hit.get('objectID')}",
                        "source_name": "hackernews",
                        "tags": ["remote-work", query.replace(" ", "-")],
                        "points": int(hit.get("points") or 0),
                    }
                )
        except requests.RequestException as exc:
            logging.warning("HN fetch failed for %r: %s", query, exc)

    return topics


def fetch_devto_topics(session: requests.Session) -> list[dict[str, Any]]:
    topics: list[dict[str, Any]] = []
    tags = ["remote", "freelance", "career", "webdev", "programming", "devops"]

    for tag in tags:
        url = "https://dev.to/api/articles"
        params = {"tag": tag, "per_page": 10}
        try:
            response = session.get(url, params=params, timeout=20)
            response.raise_for_status()
            for article in response.json():
                title = str(article.get("title") or "").strip()
                if not title or not is_relevant(title, [tag]) or is_negative_title(title):
                    continue
                topics.append(
                    {
                        "title": title,
                        "url": article.get("url"),
                        "source_name": "devto",
                        "tags": list(set([tag] + (article.get("tag_list") or [])[:5])),
                        "points": int(article.get("public_reactions_count") or 0),
                    }
                )
        except requests.RequestException as exc:
            logging.warning("dev.to fetch failed for %r: %s", tag, exc)

    return topics


def reframe_title(raw_title: str) -> str:
    """Always produce a helpful, positive freelancer angle — never clickbait negative."""
    lower = raw_title.lower()
    year = datetime.now().year
    variant = int(topic_key(raw_title)[:8], 16)

    if "upwork" in lower:
        frames = [
            f"How Pakistani Laravel Developers Are Earning More on Upwork in {year}",
            f"Upwork Fees in Pakistan: What Freelancers Actually Keep in {year}",
            f"Upwork Contracts for Pakistani Developers: A Practical Money Guide",
            f"Pakistani Freelancers on Upwork: Fees, Withdrawals, and Real Take-Home Pay",
        ]
        return frames[variant % len(frames)]

    if "fiverr" in lower:
        frames = [
            f"What Fiverr Sellers in Pakistan Should Price Their Gigs at in {year}",
            f"Fiverr Fees in Pakistan: How Much You Really Earn Per Gig",
            f"Pakistani Fiverr Sellers: Pricing and Withdrawal Guide for {year}",
        ]
        return frames[variant % len(frames)]

    if "payoneer" in lower or "wise" in lower:
        frames = [
            f"Payoneer vs Wise for Pakistani Freelancers: Real Withdrawal Costs in {year}",
            f"Wise or Payoneer for PKR Withdrawals: What Pakistani Freelancers Pay",
            f"Withdrawal Fees in Pakistan: Payoneer vs Wise Compared for {year}",
        ]
        return frames[variant % len(frames)]

    if "laravel" in lower:
        frames = [
            f"Laravel Remote Contracts in {year}: What Pakistani Developers Are Actually Paid",
            f"Laravel Freelance Rates in Pakistan: What the Market Pays in {year}",
            f"Remote Laravel Jobs for Pakistani Developers: Salary Reality Check",
        ]
        return frames[variant % len(frames)]

    if "salary" in lower or "paid" in lower:
        return f"Remote Developer Pay in {year}: Numbers Pakistani Freelancers Should Know"

    if "ai" in lower or "llm" in lower:
        return f"How Freelance Developers in Pakistan Are Using AI Without Cutting Rates in {year}"

    frames = [
        f"Remote Developer Money Moves That Work in Pakistan ({year})",
        f"What Pakistani Freelancers Should Watch in Dev Hiring This Week",
        f"Freelance Developer Guide: Turning Industry News Into Better Rates",
    ]
    return frames[variant % len(frames)]


def format_usd(amount: int) -> str:
    """Format salary for markdown-safe display (avoid $4 being stripped by downstream tools)."""
    return f"US${amount:,}"


def format_country(country: str) -> str:
    if not country or country.lower() in {"remote", "global", "worldwide"}:
        return "remote listings"
    return country


def site_stat_line(context: dict[str, Any]) -> str:
    snapshots = context.get("salary_snapshots") or []
    jobs = int(context.get("active_jobs") or 0)

    preferred = sorted(
        snapshots,
        key=lambda row: (
            0 if str(row.get("stack", "")).lower() == "laravel" else 1,
            0 if "pakistan" in str(row.get("country", "")).lower() else 1,
        ),
    )

    for row in preferred:
        salary = int(row.get("avg_salary") or 0)
        if salary <= 0:
            continue
        stack = row.get("stack") or "developer"
        country = format_country(str(row.get("country") or ""))
        return (
            f"On remotepkjobs this week, {stack} roles in {country} "
            f"are averaging about {format_usd(salary)}/month across {jobs} active listings."
        )

    if jobs > 0:
        return f"We currently track {jobs} active remote developer listings on remotepkjobs."

    return "Check live listings on remotepkjobs before you quote your next rate."


def hook_sentence(topic: dict[str, Any], context: dict[str, Any]) -> str:
    title_lower = topic["title"].lower()
    if "upwork" in title_lower:
        return (
            "A $1,200 Upwork contract in Pakistan usually lands closer to $1,020 after the "
            "10% platform fee — and that is before Payoneer or Wise takes another cut on withdrawal."
        )
    if "fiverr" in title_lower:
        return (
            "Sell a $500 Fiverr gig and Fiverr keeps $100 right away. What hits your Payoneer "
            "balance is already down to $400 before bank charges."
        )
    if "laravel" in title_lower or "php" in title_lower:
        return (
            "Laravel clients still pay in USD, but your rent is in PKR. The gap between "
            "contract value and what you can spend locally is where most freelancers miscalculate."
        )
    return (
        "A remote contract looks generous on paper until you stack platform fees, withdrawal "
        "costs, and the PKR rate you actually get on payout day."
    )


def humanize_body(topic: dict[str, Any], context: dict[str, Any]) -> str:
    seed = topic["title"]
    stat = site_stat_line(context)
    hook = hook_sentence(topic, context)

    body = f"""{hook}

{stat}

## The money problem most freelancers skip

Developers in Karachi, Lahore, and Islamabad often negotiate the headline rate and forget the fee stack. Upwork charges a flat 10%. Fiverr takes 20%. Payoneer commonly adds around 2% on withdrawal. Wise is often closer to 0.5%, which sounds tiny until you run it on a $2,500 month.

But the headline rate is not what lands in your bank. A client paying $28/hour through Upwork can net less than a direct client paying $24/hour if your withdrawal path is expensive.

## What to do with this week's news

If the conversation around "{seed}" made you rethink your stack or platform, good — that reaction is useful. Do not rewrite your entire career plan because of one viral thread. Do check whether your current setup still makes sense.

Run your actual numbers in our [freelancer fee calculator](/calculator). Plug in the contract size you are negotiating today, pick Upwork or Fiverr, then compare Payoneer against Wise. The difference between those two paths alone can be $30–$80 on a typical $1,500 month.

Then open [remote developer jobs](/jobs) and filter by your stack. If listings in your niche are showing stronger salary ranges than what you charge, that is a signal to raise your quote on the next proposal — not next year.

## One move you can make today

Honestly, Payoneer's 2% feels small until you do the math on a $3,000 month. That is $60 just to move your own money. If you are still renewing SaaS tools out of habit, scan our [self-hosted alternatives](/tools) before the next invoice hits.

Pick one live contract you are negotiating this week. Calculate net take-home in PKR. Compare withdrawal options. Send the proposal with a number you can defend after fees — not before them.
"""
    return sanitize_body(body)


def sanitize_body(body: str) -> str:
    cleaned = body
    for phrase in BANNED_PHRASES:
        cleaned = re.sub(re.escape(phrase), "", cleaned, flags=re.IGNORECASE)
    cleaned = re.sub(r"\n{3,}", "\n\n", cleaned)
    return cleaned.strip()


def build_post_payload(topic: dict[str, Any], context: dict[str, Any]) -> dict[str, Any]:
    title = reframe_title(topic["title"])
    body = humanize_body(topic, context)
    excerpt = (
        f"Practical freelance money advice for Pakistani developers: fees, withdrawal costs, "
        f"and how to respond to this week's {topic.get('tags', ['remote work'])[0]} conversation."
    )[:500]

    key = published_topic_key(title)

    return {
        "title": title,
        "topic_key": key,
        "excerpt": excerpt,
        "body": body,
        "hero_image_url": generate_hero_image(title, key, topic.get("tags"), excerpt, body),
        "meta_description": excerpt[:300],
        "source_url": topic.get("url"),
        "source_name": topic.get("source_name"),
        "tags": (topic.get("tags") or [])[:6],
        "published_at": datetime.now(timezone.utc).isoformat(),
    }


def publish_post(session: requests.Session, payload: dict[str, Any]) -> bool:
    url = f"{config.LARAVEL_API_URL}/api/internal/blog-posts"
    response = session.post(url, headers=api_headers(), json=payload, timeout=30)

    if response.status_code == 409:
        logging.info("Duplicate topic skipped: %s", payload["title"])
        return False

    response.raise_for_status()
    logging.info("Published blog post: %s", payload["title"])
    return True


def main() -> int:
    project_root = Path(__file__).resolve().parent.parent.parent
    load_dotenv(project_root / ".env")
    config.INTERNAL_API_TOKEN = os.environ.get("INTERNAL_API_TOKEN", config.INTERNAL_API_TOKEN)

    setup_logging()
    logging.info("Starting blog generator")

    session = requests.Session()
    existing_keys = fetch_existing_keys(session)
    existing_titles = fetch_existing_titles(session)
    context = fetch_site_context(session)

    candidates = fetch_hn_topics(session) + fetch_devto_topics(session)
    logging.info("Found %s candidate topics (negative titles filtered)", len(candidates))

    if not candidates:
        logging.warning("No suitable candidate topics found")
        return 1

    ranked = sorted(candidates, key=lambda item: item.get("points", 0), reverse=True)
    attempts = 0

    for topic in ranked:
        title = reframe_title(topic["title"])
        key = published_topic_key(title)

        if key in existing_keys or normalize_title(title) in existing_titles:
            logging.info("Skipping duplicate title: %s", title)
            continue

        attempts += 1
        payload = build_post_payload(topic, context)
        if publish_post(session, payload):
            logging.info("Done — 1 post published after %s attempt(s)", attempts)
            return 0

        existing_keys.add(key)
        existing_titles.add(normalize_title(title))
        if attempts >= 15:
            break

    logging.warning("Could not publish a new unique post")
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
