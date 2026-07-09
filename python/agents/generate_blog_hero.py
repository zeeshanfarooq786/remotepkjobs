#!/usr/bin/env python3
"""Generate or refresh Gemini hero images for existing blog posts."""

from __future__ import annotations

import logging
import os
import sys
from pathlib import Path

import requests
from dotenv import load_dotenv

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import config  # noqa: E402
from blog_image import generate_hero_image  # noqa: E402


def setup_logging() -> None:
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[logging.StreamHandler(sys.stdout)],
    )


def api_headers() -> dict[str, str]:
    token = config.INTERNAL_API_TOKEN
    if not token:
        raise RuntimeError("INTERNAL_API_TOKEN is not set in .env")

    return {
        "X-Internal-Token": token,
        "Accept": "application/json",
        "Content-Type": "application/json",
    }


def fetch_posts(session: requests.Session) -> list[dict]:
    url = f"{config.LARAVEL_API_URL}/api/internal/blog-posts"
    response = session.get(url, headers=api_headers(), timeout=30)
    response.raise_for_status()
    return response.json().get("posts", [])


def patch_hero_url(session: requests.Session, post_id: int, hero_image_url: str) -> None:
    url = f"{config.LARAVEL_API_URL}/api/internal/blog-posts/{post_id}/hero-image"
    response = session.patch(
        url,
        headers=api_headers(),
        json={"hero_image_url": hero_image_url},
        timeout=30,
    )
    response.raise_for_status()


def needs_new_image(hero_image_url: str | None) -> bool:
    if not hero_image_url:
        return True

    lowered = hero_image_url.lower()
    return "unsplash.com" in lowered or "source.unsplash" in lowered


def main() -> int:
    project_root = Path(__file__).resolve().parent.parent.parent
    load_dotenv(project_root / ".env")
    config.INTERNAL_API_TOKEN = os.environ.get("INTERNAL_API_TOKEN", config.INTERNAL_API_TOKEN)

    setup_logging()

    session = requests.Session()
    posts = fetch_posts(session)
    updated = 0
    force = "--force" in sys.argv

    for post in posts:
        if not force and not needs_new_image(post.get("hero_image_url")):
            logging.info("Skipping post %s — already has local hero image", post.get("slug"))
            continue

        hero_url = generate_hero_image(
            post["title"],
            post["topic_key"],
            post.get("tags") or [],
            post.get("excerpt") or "",
            post.get("body") or "",
            force=force,
        )
        if not hero_url:
            logging.error("Could not generate image for post %s", post.get("slug"))
            continue

        patch_hero_url(session, post["id"], hero_url)
        logging.info("Updated hero image for: %s", post.get("title"))
        updated += 1

    logging.info("Done — %s post(s) updated", updated)
    return 0 if updated > 0 or not posts else 1


if __name__ == "__main__":
    raise SystemExit(main())
