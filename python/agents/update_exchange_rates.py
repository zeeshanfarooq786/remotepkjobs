#!/usr/bin/env python3
"""Fetch live USD exchange rates and bulk-update DevRates via internal API."""

from __future__ import annotations

import logging
import os
import sys
from datetime import datetime, timezone
from pathlib import Path

import requests
from dotenv import load_dotenv

CURRENCIES = [
    "PKR",
    "EUR",
    "GBP",
    "CAD",
    "AUD",
    "AED",
    "SAR",
    "BDT",
    "INR",
    "NGN",
]

API_URL = os.environ.get("EXCHANGE_RATE_API_URL", "https://open.er-api.com/v6/latest/USD")


def setup_logging() -> None:
    log_dir = Path(__file__).resolve().parent.parent / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"update_exchange_rates_{datetime.now():%Y-%m-%d}.log"

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def fetch_rates() -> dict[str, float]:
    logging.info("Fetching live rates from %s", API_URL)
    response = requests.get(API_URL, timeout=30)
    response.raise_for_status()
    payload = response.json()

    if payload.get("result") != "success":
        raise RuntimeError(f"Exchange API error: {payload}")

    api_rates = payload.get("rates", {})
    selected: dict[str, float] = {}

    for currency in CURRENCIES:
        if currency not in api_rates:
            logging.warning("Currency %s missing from API response", currency)
            continue
        selected[currency] = float(api_rates[currency])

    if not selected:
        raise RuntimeError("No supported currencies returned from API")

    return selected


def post_rates(rates: dict[str, float]) -> dict:
    app_url = os.environ.get("APP_URL", "http://localhost:8000").rstrip("/")
    token = os.environ.get("INTERNAL_API_TOKEN")

    if not token:
        raise RuntimeError("INTERNAL_API_TOKEN is not set in .env")

    body = {
        "rates": [
            {
                "from_currency": "USD",
                "to_currency": currency,
                "rate": rate,
            }
            for currency, rate in rates.items()
        ],
        "recorded_at": datetime.now(timezone.utc).isoformat(),
    }

    endpoint = f"{app_url}/api/internal/exchange-rates"
    logging.info("Posting %d rates to %s", len(body["rates"]), endpoint)

    response = requests.post(
        endpoint,
        headers={
            "X-Internal-Token": token,
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
        json=body,
        timeout=30,
    )
    response.raise_for_status()
    return response.json()


def main() -> int:
    project_root = Path(__file__).resolve().parent.parent.parent
    load_dotenv(project_root / ".env")
    setup_logging()

    try:
        rates = fetch_rates()
        result = post_rates(rates)
        logging.info("Success: %s", result)
        return 0
    except Exception as exc:
        logging.exception("Exchange rate update failed: %s", exc)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
