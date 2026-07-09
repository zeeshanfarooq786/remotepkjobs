#!/usr/bin/env python3
"""Fetch live exchange rates and platform fees, then sync to DevRates via internal API."""

from __future__ import annotations

import logging
import sys
from datetime import date, datetime, timezone
from pathlib import Path
from typing import Any

import requests
from dotenv import load_dotenv

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import config  # noqa: E402


def setup_logging() -> None:
    log_dir = Path(__file__).resolve().parent.parent / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / f"rate_updater_{datetime.now():%Y-%m-%d}.log"

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def api_headers() -> dict[str, str]:
    token = config.INTERNAL_API_TOKEN
    if not token:
        raise RuntimeError("INTERNAL_API_TOKEN is not set in .env")

    return {
        "X-Internal-Token": token,
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


def fetch_exchange_rates(session: requests.Session) -> list[dict[str, Any]]:
    logging.info("Fetching exchange rates from %s", config.EXCHANGE_RATE_API_URL)
    response = session.get(config.EXCHANGE_RATE_API_URL, timeout=30)
    response.raise_for_status()
    payload = response.json()

    if payload.get("result") != "success":
        raise RuntimeError(f"Exchange API error: {payload}")

    api_rates = payload.get("rates", {})
    rows: list[dict[str, Any]] = []

    for currency in config.EXCHANGE_CURRENCIES:
        if currency not in api_rates:
            logging.warning("Currency %s missing from exchange API response", currency)
            continue

        rows.append(
            {
                "from_currency": "USD",
                "to_currency": currency,
                "rate": float(api_rates[currency]),
            }
        )

    if not rows:
        raise RuntimeError("No supported exchange currencies returned from API")

    logging.info("Prepared %d exchange rate rows", len(rows))
    return rows


def fetch_wise_transfer_percent(session: requests.Session) -> float:
    fallback = float(config.PLATFORM_FEES["wise_transfer_percent_fallback"])
    send_amount = 1000

    try:
        response = session.get(
            config.WISE_COMPARISON_API_URL,
            params={
                "sourceCurrency": "USD",
                "targetCurrency": "PKR",
                "sendAmount": send_amount,
            },
            timeout=20,
        )

        if not response.ok:
            logging.warning("Wise comparison API returned HTTP %s — using %.2f%% fallback", response.status_code, fallback)
            return fallback

        payload = response.json()
        providers = payload.get("providers", [])

        for provider in providers:
            if str(provider.get("alias", "")).lower() != "wise":
                continue

            quotes = provider.get("quotes") or []
            if not quotes:
                continue

            fee_amount = quotes[0].get("fee")
            if fee_amount is None:
                continue

            percent = round((float(fee_amount) / send_amount) * 100, 4)
            logging.info("Wise transfer fee from API: %.4f%%", percent)
            return percent

        logging.warning("Wise provider not found in comparison API — using %.2f%% fallback", fallback)
    except requests.RequestException as exc:
        logging.warning("Wise fee fetch failed: %s — using %.2f%% fallback", exc, fallback)

    return fallback


def build_platform_rates(session: requests.Session) -> list[dict[str, Any]]:
    effective_date = date.today().isoformat()
    rates: list[dict[str, Any]] = []

    for tier in config.PLATFORM_FEES["upwork_tiers"]:
        rates.append(
            {
                "platform": "Upwork",
                "fee_type": tier["fee_type"],
                "fee_value": tier["fee_value"],
                "currency": "USD",
                "effective_date": effective_date,
            }
        )

    rates.append(
        {
            "platform": "Fiverr",
            "fee_type": "flat_percent",
            "fee_value": config.PLATFORM_FEES["fiverr_percent"],
            "currency": "USD",
            "effective_date": effective_date,
        }
    )

    rates.append(
        {
            "platform": "Payoneer",
            "fee_type": "withdrawal_percent",
            "fee_value": config.PLATFORM_FEES["payoneer_withdrawal_percent"],
            "currency": "USD",
            "effective_date": effective_date,
        }
    )

    wise_percent = fetch_wise_transfer_percent(session)
    rates.append(
        {
            "platform": "Wise",
            "fee_type": "transfer_percent",
            "fee_value": wise_percent,
            "currency": "USD",
            "effective_date": effective_date,
        }
    )

    logging.info("Prepared %d platform rate rows", len(rates))
    return rates


def post_exchange_rates(session: requests.Session, rates: list[dict[str, Any]]) -> dict[str, Any]:
    endpoint = f"{config.LARAVEL_API_URL}/api/internal/exchange-rates"
    body = {
        "rates": rates,
        "recorded_at": datetime.now(timezone.utc).isoformat(),
    }

    logging.info("Posting %d exchange rates to %s", len(rates), endpoint)
    response = session.post(endpoint, headers=api_headers(), json=body, timeout=30)

    if not response.ok:
        logging.error("Exchange rates API error %s: %s", response.status_code, response.text)
        response.raise_for_status()

    return response.json()


def post_platform_rates(session: requests.Session, rates: list[dict[str, Any]]) -> dict[str, Any]:
    endpoint = f"{config.LARAVEL_API_URL}/api/internal/platform-rates"
    body = {
        "rates": rates,
        "effective_date": date.today().isoformat(),
    }

    logging.info("Posting %d platform rates to %s", len(rates), endpoint)
    response = session.post(endpoint, headers=api_headers(), json=body, timeout=30)

    if not response.ok:
        logging.error("Platform rates API error %s: %s", response.status_code, response.text)
        response.raise_for_status()

    return response.json()


def run() -> int:
    project_root = Path(__file__).resolve().parent.parent.parent
    load_dotenv(project_root / ".env")

    import os

    config.LARAVEL_API_URL = os.environ.get("APP_URL", config.LARAVEL_API_URL).rstrip("/")
    config.INTERNAL_API_TOKEN = os.environ.get("INTERNAL_API_TOKEN", config.INTERNAL_API_TOKEN)

    setup_logging()
    logging.info("Starting rate updater run")

    errors = 0

    with requests.Session() as session:
        try:
            exchange_rows = fetch_exchange_rates(session)
            result = post_exchange_rates(session, exchange_rows)
            logging.info("Exchange rates updated: %s", result)
        except Exception as exc:
            errors += 1
            logging.exception("Exchange rate update failed: %s", exc)

        try:
            platform_rows = build_platform_rates(session)
            result = post_platform_rates(session, platform_rows)
            logging.info("Platform rates updated: %s", result)
        except Exception as exc:
            errors += 1
            logging.exception("Platform rate update failed: %s", exc)

    if errors:
        logging.error("Rate updater finished with %d error(s)", errors)
        return 1

    logging.info("Rate updater finished successfully")
    return 0


def main() -> int:
    try:
        return run()
    except Exception as exc:
        logging.exception("Rate updater failed: %s", exc)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
