# DevRates — Ubuntu 22.04 Deployment Guide

Deploy DevRates to a Linux production server with **Nginx**, **MySQL 8**, **Redis**, **PHP 8.3**, and **Python 3.11+**.

Local development is on **Windows** (`D:\something\devrates`). Production uses the Linux scripts in this repo.

---

## 1. Server prerequisites

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server redis-server \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-bcmath php8.3-redis php8.3-intl \
  git unzip curl python3 python3-venv python3-pip supervisor
```

Install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 2. Create database and user

```bash
sudo mysql
```

```sql
CREATE DATABASE devrates CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'devrates'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON devrates.* TO 'devrates'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Clone and install application

```bash
sudo mkdir -p /var/www
sudo chown $USER:$USER /var/www
cd /var/www
git clone YOUR_REPO_URL devrates
cd devrates
```

### PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `/var/www/devrates/.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://yourdomain.com`
- `APP_TIMEZONE=UTC`
- `DB_*` credentials from step 2
- `REDIS_HOST=127.0.0.1`
- `INTERNAL_API_TOKEN=` (generate: `php -r "echo bin2hex(random_bytes(32));"`)
- `GITHUB_TOKEN=` (GitHub personal access token)
- `EXCHANGE_RATE_API_URL=` (free or paid endpoint)

### Python virtualenv

```bash
cd /var/www/devrates/python
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
deactivate
chmod +x run_agent.sh
```

### Frontend assets

```bash
cd /var/www/devrates
npm ci
npm run build
```

### Laravel setup

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Set permissions:

```bash
sudo chown -R www-data:www-data /var/www/devrates/storage /var/www/devrates/bootstrap/cache /var/www/devrates/public
sudo chmod -R 775 /var/www/devrates/storage /var/www/devrates/bootstrap/cache
sudo chmod -R 775 /var/www/devrates/python/logs
```

---

## 4. Nginx configuration

Create `/etc/nginx/sites-available/devrates`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/devrates/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and reload:

```bash
sudo ln -s /etc/nginx/sites-available/devrates /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Add TLS with Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## 5. Laravel scheduler (cron)

Laravel 12 schedules are defined in `routes/console.php` (there is no `app/Console/Kernel.php`).

**Add this exact line** to the `www-data` user crontab (`sudo crontab -u www-data -e`):

```cron
* * * * * cd /var/www/devrates && php artisan schedule:run >> /var/www/devrates/storage/logs/scheduler.log 2>&1
```

### Scheduled tasks (all times UTC)

| When | Command |
|------|---------|
| Daily 02:00 | `agent:run job_scraper` |
| Sunday 03:00 | `agent:run rate_updater` |
| Sunday 04:00 | `agent:run github_updater` |
| Daily 06:00 | `jobs:clean-expired` |
| Daily (default) | `exchange-rates:fetch` (existing) |
| Daily 07:00 | `rates:check-freshness` |
| Daily 08:00 | `sitemap:generate` |

Verify:

```bash
cd /var/www/devrates
php artisan schedule:list
```

---

## 6. Manual agent testing (production)

Run each agent once before relying on cron:

```bash
cd /var/www/devrates/python
./run_agent.sh job_scraper
./run_agent.sh rate_updater
./run_agent.sh github_updater
```

Or via Artisan (preferred — same path the scheduler uses):

```bash
cd /var/www/devrates
php artisan agent:run job_scraper
php artisan agent:run rate_updater
php artisan agent:run github_updater
```

Check logs:

```bash
tail -f /var/www/devrates/python/logs/cron_$(date +%Y%m%d).log
tail -f /var/www/devrates/python/logs/job_scraper_$(date +%Y-%m-%d).log
tail -f /var/www/devrates/storage/logs/laravel.log
```

Verify data landed:

```bash
php artisan tinker --execute="echo App\Models\Job::count().' jobs';"
php artisan rates:check-freshness
php artisan sitemap:generate
curl -I https://yourdomain.com/sitemap.xml
```

---

## 7. Windows local testing

Use the `.bat` scripts — do **not** run `.sh` files on Windows.

### Run scheduler manually

```bat
D:\something\devrates\scripts\run-scheduler.bat
```

Or register Windows Task Scheduler to run that `.bat` every minute.

### Run a Python agent manually

```bat
D:\something\devrates\python\run_agent.bat job_scraper
D:\something\devrates\python\run_agent.bat rate_updater
D:\something\devrates\python\run_agent.bat github_updater
```

Or via Artisan:

```bat
cd D:\something\devrates
C:\xampp\php\php.exe artisan agent:run job_scraper
```

Ensure `.env` has `PYTHON_PATH` pointing to your local Python install.

---

## 8. Google Search Console — submit sitemap

1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add property: `https://yourdomain.com`
3. Verify ownership (DNS TXT record or HTML file)
4. **Sitemaps** → Submit: `https://yourdomain.com/sitemap.xml`
5. Confirm URLs are discovered after `php artisan sitemap:generate` runs

---

## 9. Google AdSense

Prerequisites (already in DevRates):

- `/about` — 300+ word about page
- `/contact` — contact page
- `/privacy` — privacy policy mentioning cookies and AdSense
- `<!-- ADSENSE_HEAD -->` placeholder in `resources/views/layouts/app.blade.php`
- `<!-- ADSENSE_FOOTER -->` placeholder in `resources/views/partials/footer.blade.php`

Steps:

1. Apply at [Google AdSense](https://www.google.com/adsense/)
2. Add your domain and verify site ownership
3. Wait for site review (can take days to weeks)
4. After approval, replace `<!-- ADSENSE_HEAD -->` with your AdSense `<script>` tag
5. Add ad unit snippets where appropriate in Blade templates
6. Re-deploy cached views: `php artisan view:cache`

---

## 10. Post-deploy checklist

- [ ] `APP_DEBUG=false` and `APP_ENV=production`
- [ ] `php artisan migrate --force` completed
- [ ] `php artisan db:seed --force` completed (or targeted seeders)
- [ ] Cron `* * * * * php artisan schedule:run` active for `www-data`
- [ ] `php artisan schedule:list` shows all 7 tasks
- [ ] All three Python agents run without errors
- [ ] `php artisan rates:check-freshness` returns success
- [ ] `public/sitemap.xml` exists and is reachable
- [ ] Sitemap submitted to Google Search Console
- [ ] HTTPS working via Certbot
- [ ] Redis running: `redis-cli ping` → `PONG`
- [ ] MySQL running: `systemctl status mysql`
- [ ] Nginx + PHP-FPM running
- [ ] AdSense placeholders ready for approval

---

## 11. Useful maintenance commands

```bash
# Clear caches after .env changes
php artisan config:clear && php artisan config:cache

# Rebuild sitemap immediately
php artisan sitemap:generate

# Clean old inactive jobs
php artisan jobs:clean-expired

# View scheduler log
tail -100 /var/www/devrates/storage/logs/scheduler.log
```
