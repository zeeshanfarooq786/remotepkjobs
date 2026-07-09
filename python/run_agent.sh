#!/bin/bash
# Usage: ./run_agent.sh job_scraper
set -euo pipefail

AGENT="${1:-}"

if [ -z "$AGENT" ]; then
    echo "Usage: ./run_agent.sh job_scraper"
    exit 1
fi

case "$AGENT" in
    job_scraper|rate_updater|github_updater|update_exchange_rates|blog_generator) ;;
    *)
        echo "Unknown agent: $AGENT"
        echo "Allowed: job_scraper, rate_updater, github_updater, update_exchange_rates, blog_generator"
        exit 1
        ;;
esac

cd /var/www/devrates/python
source venv/bin/activate
python "agents/${AGENT}.py" >> "logs/cron_$(date +%Y%m%d).log" 2>&1
