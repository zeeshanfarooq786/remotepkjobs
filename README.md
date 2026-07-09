# RemotePKJobs

A full-stack web platform for remote developers — especially in Pakistan and South Asia — to browse remote jobs, estimate real take-home pay after platform fees, and discover self-hosted alternatives to expensive SaaS tools.

**Portfolio project** — built as a production-style Laravel application with Python automation agents, SEO tooling, and an admin panel.

---

## What it does

| Feature | Description |
|--------|-------------|
| **Job board** | Aggregates remote dev roles from RemoteOK, Himalayas, and Arbeitnow via a Python scraper agent |
| **Fee calculator** | Live take-home estimates for Upwork, Fiverr, and Toptal with Payoneer, Wise, or local bank payouts |
| **Salary snapshots** | Average monthly pay by stack and country over time |
| **Tools directory** | Self-hosted alternatives to Pusher, Mailgun, Heroku, Datadog, and more — with GitHub stats |
| **Blog** | SEO-focused articles with automated hero image generation |
| **Admin panel** | Password-protected blog CMS at `/admin` |

---

## Tech stack

**Backend**
- Laravel 12 (PHP 8.2+)
- MySQL / SQLite
- Redis (queues & cache)
- Laravel Telescope & Debugbar (dev)

**Frontend**
- Blade templates
- Tailwind CSS 4
- Alpine.js
- Chart.js
- Vite 7

**Automation**
- Python 3.11+ agents for job scraping, exchange rates, platform fees, GitHub stats, and blog generation
- Internal REST API secured with `X-Internal-Token`

---

## Project structure

```
app/           Laravel controllers, models, services
python/        Automation agents (job_scraper, rate_updater, blog_generator, etc.)
resources/     Blade views, CSS, JS
database/      Migrations and seeders (demo data included)
routes/        Web, API, and admin routes
scripts/       Local scheduler helpers
```

---

## Local setup

### Requirements

- PHP 8.2+, Composer
- Node.js 18+, npm
- MySQL or SQLite
- Python 3.11+ (optional — for running agents locally)

### Quick start

```bash
# Clone the repo
git clone https://github.com/YOUR_USERNAME/remotepkjobs.git
cd remotepkjobs

# PHP dependencies
composer install
cp .env.example .env
php artisan key:generate

# Database (SQLite works for local demo)
touch database/database.sqlite
# Set DB_CONNECTION=sqlite in .env, then:
php artisan migrate --seed

# Frontend
npm install
npm run build

# Run the app
php artisan serve
```

Visit `http://127.0.0.1:8000`.

### Full dev environment (with Vite hot reload)

```bash
composer run dev
```

This starts the Laravel server, queue worker, log tail, and Vite dev server together.

### Python agents (optional)

```bash
cd python
python -m venv venv
# Windows: venv\Scripts\activate
# Linux/Mac: source venv/bin/activate
pip install -r requirements.txt

# Set INTERNAL_API_TOKEN in .env, then from project root:
php artisan agent:run job_scraper
php artisan agent:run update_exchange_rates
```

---

## Environment variables

Copy `.env.example` to `.env`. Key values:

| Variable | Purpose |
|----------|---------|
| `INTERNAL_API_TOKEN` | Shared secret for Python agents → Laravel API |
| `ADMIN_PASSWORD` | Admin panel login password |
| `GITHUB_TOKEN` | GitHub API token for alternatives stats updater |
| `EXCHANGE_RATE_API_URL` | Live FX rates endpoint |

Never commit `.env` or real API keys.

---

## Screenshots

_Add 2–3 screenshots here before publishing (home page, calculator, jobs list) — recruiters love visuals._

---

## Deployment

See [DEPLOY.md](DEPLOY.md) for Ubuntu 22.04 production setup with Nginx, MySQL, Redis, Supervisor, and cron.

---

## License

MIT — portfolio use. Job listings are aggregated from public APIs; respect each source's terms of use if you deploy publicly.
