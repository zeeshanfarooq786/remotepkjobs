<p align="center">
  <img width="180" height="42" alt="Image" src="https://github.com/user-attachments/assets/4761bbe5-cf0d-40a8-bc57-5a65ad167a53" />
</p>
<h1 align="center">RemotePKJobs</h1>
<p align="center"><em>by Zef Technology</em></p>


A full-stack web platform for remote developers in Pakistan and South Asia to browse remote jobs, estimate actual take-home pay after platform fees, and find self-hosted alternatives to expensive SaaS tools.

This is a **portfolio project** built as a production-ready Laravel application, backed by Python automation agents, built-in SEO tooling, and a custom admin panel.

---

## Features

* **Job Board:** Pulls remote dev roles from RemoteOK, Himalayas, and Arbeitnow using a custom Python scraper.
* **Fee Calculator:** Live take-home estimates for Upwork, Fiverr, and Toptal, factoring in Payoneer, Wise, or local bank payout rates.
* **Salary Snapshots:** Tracks and displays average monthly pay trends by tech stack and country over time.
* **SaaS Alternatives Directory:** Curated self-hosted alternatives to tools like Pusher, Mailgun, Heroku, and Datadog, complete with live GitHub stats.
* **Blog:** SEO-optimized articles featuring automated hero image generation.
* **Admin Panel:** A protected CMS for managing blog posts at `/admin`.

---

## Tech Stack

* **Backend:** Laravel 12 (PHP 8.2+), MySQL / SQLite, Redis (for queues & caching), Laravel Telescope & Debugbar (dev mode)
* **Frontend:** Blade templates, Tailwind CSS 4, Alpine.js, Chart.js, Vite 7
* **Automation & Scripts:** Python 3.11+ agents handling job scraping, exchange rates, fee calculations, GitHub stats, and automated blog content.
* **Security:** Internal REST API endpoints secured with a custom `X-Internal-Token`.

---

## Project Structure
app/          # Laravel controllers, models, and services
python/       # Automation agents (job_scraper, rate_updater, blog_generator, etc.)
resources/    # Blade views, CSS, and JS components
database/     # Migrations and seeders (includes demo data)
routes/       # Web, API, and admin routing
scripts/      # Local scheduler and helper scripts

---
## Screenshots
<img width="1366" height="1492" alt="Image" src="https://github.com/user-attachments/assets/fc7f6ca6-5923-48c1-a662-1c482cbb6029" />

<img width="1366" height="1816" alt="Image" src="https://github.com/user-attachments/assets/3aee504c-e70c-4193-9de4-e8ba8d7a9059" />

<img width="1366" height="2224" alt="Image" src="https://github.com/user-attachments/assets/635c6cd3-359b-4702-86f9-e701ad6d1f37" />

<img width="1366" height="1072" alt="Image" src="https://github.com/user-attachments/assets/589024b2-2cb7-4e64-b2bd-438adae5d101" />

<img width="1366" height="2042" alt="Image" src="https://github.com/user-attachments/assets/242d6c06-fea7-402e-bde7-33be1e4d521c" />
---
## Local Setup

### Prerequisites
* PHP 8.2+ & Composer
* Node.js 18+ & npm
* MySQL or SQLite
* Python 3.11+ *(optional, only needed to run agents locally)*

### Quick Start


# Clone the repo
git clone [https://github.com/YOUR_USERNAME/remotepkjobs.git](https://github.com/YOUR_USERNAME/remotepkjobs.git)
cd remotepkjobs

# Install PHP dependencies & set up environment
composer install
cp .env.example .env
php artisan key:generate

# Set up database (SQLite works best for a quick local demo)
touch database/database.sqlite
# Note: Set DB_CONNECTION=sqlite in your .env before running migrations
php artisan migrate --seed

# Install frontend assets
npm install
npm run build

# Start the local server
php artisan serve
Open http://127.0.0.1:8000 in your browser.

Full Dev Environment (with Vite Hot Reload)
composer run dev
This runs the Laravel server, queue worker, log tail, and Vite dev server simultaneously.

Running Python Agents Locally (Optional)
cd python
python -m venv venv
# On Windows: venv\Scripts\activate
# On Linux/Mac: source venv/bin/activate
pip install -r requirements.txt

# Make sure INTERNAL_API_TOKEN is set in your .env, then run from the project root:
php artisan agent:run job_scraper
php artisan agent:run update_exchange_rates
Environment Variables
Copy .env.example to .env and configure the following key variables:

INTERNAL_API_TOKEN: Shared secret key used by Python agents to authenticate with the Laravel API.

ADMIN_PASSWORD: Password for accessing the admin panel.

GITHUB_TOKEN: Personal access token to fetch stats for the SaaS alternatives directory.

EXCHANGE_RATE_API_URL: Endpoint used for fetching live FX rates.

Note: Never commit your actual .env file or production API keys to GitHub.

Screenshots
Deployment
For a production setup on Ubuntu 22.04 using Nginx, MySQL, Redis, Supervisor, and Cron, check out DEPLOY.md.

License
MIT. Feel free to use this for your portfolio. Job listings are aggregated from public APIs; please respect each source's terms of use if you host this publicly.
