"""Shared configuration for DevRates Python agents."""

from __future__ import annotations

import os
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parent.parent

LARAVEL_API_URL = os.environ.get("APP_URL", "http://127.0.0.1:8000").rstrip("/")
INTERNAL_API_TOKEN = os.environ.get("INTERNAL_API_TOKEN", "")
GITHUB_TOKEN = os.environ.get("GITHUB_TOKEN", "")
BLOG_IMAGE_WIDTH = int(os.environ.get("BLOG_IMAGE_WIDTH", "1640"))
BLOG_IMAGE_HEIGHT = int(os.environ.get("BLOG_IMAGE_HEIGHT", "720"))

TARGET_STACKS = [
    "laravel",
    "php",
    "python",
    "django",
    "react",
    "vue",
    "node",
]

STACK_LABELS = {
    "laravel": "Laravel",
    "php": "Laravel",
    "python": "Python",
    "django": "Python",
    "react": "React",
    "vue": "Vue",
    "node": "Node",
    "node.js": "Node",
}

# Multiply foreign currency amounts by this rate to get USD.
EXCHANGE_RATES_TO_USD = {
    "USD": 1.0,
    "EUR": 1.08,
    "GBP": 1.27,
    "CAD": 0.74,
    "AUD": 0.66,
    "CHF": 1.12,
    "PKR": 0.0036,
    "INR": 0.012,
    "BDT": 0.0082,
    "AED": 0.27,
    "SAR": 0.27,
    "NGN": 0.00065,
}

SOURCES = [
    {
        "name": "remoteok",
        "url": "https://remoteok.com/api",
        "type": "remoteok",
    },
    {
        "name": "himalayas",
        "url": "https://himalayas.app/jobs/api",
        "type": "himalayas",
    },
    {
        "name": "arbeitnow",
        "url": "https://www.arbeitnow.com/api/job-board-api",
        "type": "arbeitnow",
    },
]

HIMALAYAS_PAGE_LIMIT = 50
HIMALAYAS_MAX_PAGES = 3

BLOCKED_TITLE_KEYWORDS = [
    "nanny",
    "nurse",
    "driver",
    "chef",
    "cook",
    "cleaner",
    "accountant",
    "sales",
    "marketing",
    "hr ",
    "recruiter",
    "manager",
    "director",
    "consulting",
    "datacenter",
    "network-engineer",
    "verifikation",
    "dokumentation",
    "access und",
    "managing director",
]

EXCHANGE_RATE_API_URL = "https://open.exchangerate-api.com/v6/latest/USD"

EXCHANGE_CURRENCIES = [
    "PKR",
    "GBP",
    "EUR",
    "CAD",
    "AUD",
]

PLATFORM_FEES = {
    "upwork_tiers": [
        {"fee_type": "flat_percent", "fee_value": 10.0},
    ],
    "fiverr_percent": 20,
    "payoneer_withdrawal_percent": 2,
    "wise_transfer_percent_fallback": 0.5,
}

WISE_COMPARISON_API_URL = "https://api.wise.com/v3/comparisons"

BLOCKED_COUNTRIES = [
    "berlin",
    "munich",
    "hamburg",
    "dresden",
    "germany",
    "zurich",
    "switzerland",
    "offenbach",
    "austria",
    "netherlands",
]
