"""Generate blog hero images with Pollinations AI — free, no API key."""

from __future__ import annotations

import io
import logging
from pathlib import Path
from urllib.parse import quote

import requests
from PIL import Image

import config

BLOG_IMAGES_DIR = config.PROJECT_ROOT / "public" / "images" / "blog"
IMAGE_WIDTH = int(getattr(config, "BLOG_IMAGE_WIDTH", 1640))
IMAGE_HEIGHT = int(getattr(config, "BLOG_IMAGE_HEIGHT", 720))


def build_image_prompt(title: str, tags: list[str] | None = None, excerpt: str = "") -> str:
    """Build a specific image prompt based on blog content."""
    title_lower = title.lower()

    if any(w in title_lower for w in ("upwork", "fiverr", "freelance", "fee")):
        visual = "money flowing through digital funnel, currency symbols, payment icons"
    elif any(w in title_lower for w in ("laravel", "php", "python", "developer")):
        visual = "code editor screen, programming syntax, tech workspace"
    elif any(w in title_lower for w in ("salary", "rate", "earn", "income")):
        visual = "upward trending graph, currency symbols, financial growth"
    elif any(w in title_lower for w in ("remote", "work", "job")):
        visual = "laptop on desk, globe, wifi connection, home office"
    elif any(w in title_lower for w in ("payoneer", "wise", "withdrawal", "pkr")):
        visual = "bank transfer arrows, USD to PKR currency exchange, digital wallet"
    elif any(w in title_lower for w in ("tool", "saas", "server", "host")):
        visual = "server rack, cloud computing icons, self-hosted infrastructure"
    else:
        visual = "remote developer workspace, laptop, code, global connection"

    tag_hint = ", ".join((tags or [])[:3])
    summary = (excerpt or "")[:120]

    return (
        f"Professional blog hero illustration, {visual}, "
        f"topic: {title}, context: {summary}, tags: {tag_hint}, "
        "flat design style, navy blue and white color scheme, "
        "green accents, minimal clean composition, "
        "no text, no faces, wide landscape format, "
        "modern tech startup aesthetic, unique creative layout"
    )


def public_image_url(topic_key: str) -> str:
    return f"{config.LARAVEL_API_URL}/images/blog/{topic_key}.jpg"


def generate_hero_image(
    title: str,
    topic_key: str,
    tags: list[str] | None = None,
    excerpt: str = "",
    body: str = "",
    *,
    force: bool = False,
) -> str | None:
    """Generate hero image using Pollinations AI and save to public/images/blog/."""
    del body  # reserved for future body-aware prompts

    BLOG_IMAGES_DIR.mkdir(parents=True, exist_ok=True)
    output_path = BLOG_IMAGES_DIR / f"{topic_key}.jpg"
    unique_seed = abs(hash(title + topic_key)) % 999_999

    if not force and output_path.exists() and output_path.stat().st_size > 0:
        logging.info("Reusing existing hero image: %s", output_path.name)
        return public_image_url(topic_key)

    if force and output_path.exists():
        output_path.unlink()
        png_legacy = BLOG_IMAGES_DIR / f"{topic_key}.png"
        if png_legacy.exists():
            png_legacy.unlink()

    prompt = build_image_prompt(title, tags, excerpt)
    encoded = quote(prompt[:900], safe="")
    url = (
        f"https://image.pollinations.ai/prompt/{encoded}"
        f"?width={IMAGE_WIDTH}&height={IMAGE_HEIGHT}&seed={unique_seed}&nologo=true"
    )

    logging.info("Generating image from Pollinations (%sx%s)", IMAGE_WIDTH, IMAGE_HEIGHT)

    try:
        response = requests.get(url, timeout=90)
        response.raise_for_status()

        img = Image.open(io.BytesIO(response.content))
        img = img.convert("RGB")
        img.save(output_path, "JPEG", quality=88)

        logging.info("Image saved: %s", output_path)
        return public_image_url(topic_key)
    except Exception as exc:
        logging.warning("Pollinations image generation failed: %s", exc)
        return None
