# KARTEKS Energy Solution — Production Deployment Guide (AAPanel + Cloudflare Tunnel)

Target: home server, **AAPanel** web panel, **Cloudflare Tunnel** as public-facing proxy (no port forwarding needed, hides home IP, free DDoS protection).

---

## 1. Prerequisites

### 1.1 Server minimum
- 2 vCPU / 4 GB RAM / 40 GB SSD (AAPanel + Laravel + MySQL)
- Ubuntu 22.04 LTS or Debian 11+ recommended
- Public IP from ISP OR behind NAT (Cloudflare Tunnel handles both)

### 1.2 Domain
- Beli domain (Namecheap/Cloudflare Registrar)
- Nameservers pointing ke Cloudflare (free plan OK)
- Cloudflare DNS record: A atau CNAME → handled by Tunnel automatically

### 1.3 Software (AAPanel akan install sebagian besar)
- AAPanel (https://www.aapanel.com/new/download.html)
- Nginx 1.22+
- PHP 8.2 atau 8.3 (Wajib install ext: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd` atau `imagick`, `zip`, `xml`, `bcmath`, `intl`, `opcache`, `redis`)
- MySQL 5.7+ atau MariaDB 10.5+
- Redis 7+
- Composer 2.6+
- Node.js 20 LTS + npm 10
- Supervisor (queue worker)

---

## 2. AAPanel Setup

### 2.1 Install AAPanel
```bash
wget -O install.sh https://www.aapanel.com/script/install-ubuntu_6.0_en.sh
sudo bash install.sh aapanel
```

### 2.2 Install software stack via AAPanel
AAPanel → **App Store** → install:
- Nginx 1.22+ (free)
- PHP 8.2 (free) — set memory_limit ke 512M, max_execution_time 300, post_max_size 100M, upload_max_filesize 100M
- MySQL 5.7+ atau MariaDB 10.5
- Redis 7+
- Composer 2

**PHP extensions wajib** (via AAPanel → PHP → Extensions):
```
pdo_mysql, mbstring, openssl, curl, gd, zip, xml, bcmath, intl, opcache, redis, fileinfo, tokenizer
```
OPcache settings (PHP → Config → opcache.ini):
```
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2.3 Create database
AAPanel → Database → Add database:
- Name: `karteks_prod`
- User: `karteks_user`
- Password: generate strong random (save di password manager)

### 2.4 Create site + SSL
AAPanel → Website → Add site:
- Domain: `karteksenergy.com` (atau domain Anda)
- PHP version: 8.2
- Database: pilih yang di step 2.3
- SSL: AAPanel auto-issues Let's Encrypt (butuh real IP accessible untuk verify — kalau pakai Tunnel dari awal, skip dulu SSL di AAPanel)

---

## 3. Clone & Build Project

```bash
# Ganti path AAPanel default
cd /www/wwwroot

# Clone (ganti YOUR_REPO_URL dengan URL repo KARTEKS)
sudo git clone YOUR_REPO_URL karteks-energy-solution
cd karteks-energy-solution

# Permissions (AAPanel runs www user)
sudo chown -R www:www /www/wwwroot/karteks-energy-solution
sudo chmod -R 755 /www/wwwroot/karteks-energy-solution
sudo chmod -R 775 /www/wwwroot/karteks-energy-solution/storage
sudo chmod -R 775 /www/wwwroot/karteks-energy-solution/bootstrap/cache

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Copy & edit .env
cp .env.production.example .env
nano .env
```

### 3.1 Critical .env settings

```env
APP_NAME="KARTEKS Energy Solution"
APP_ENV=production
APP_KEY=                                # run php artisan key:generate
APP_DEBUG=false
APP_URL=https://karteksenergy.com

LOG_CHANNEL=stack
LOG_LEVEL=warning                       # production: avoid info/debug noise

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karteks_prod
DB_USERNAME=karteks_user
DB_PASSWORD=...

# CACHE — pakai Redis (1 jam rated, fast, persistent)
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# QUEUE — pakai Redis (faster than database, persistent)
QUEUE_CONNECTION=redis

# SESSION — pakai Redis untuk avoid MySQL row lock
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# FILESYSTEM — local untuk media (kalau single-server)
FILESYSTEM_DISK=public

# MAIL — pakai SMTP (Gmail, Mailgun, Resend, dll)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@karteksenergy.com"
MAIL_FROM_NAME="${APP_NAME}"

# MIDTRANS
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_NOTIFICATION_URL=https://karteksenergy.com/api/v1/payments/midtrans/notification

# CLOUDFLARE (untuk cache invalidation kalau perlu)
CF_ZONE_ID=...
CF_API_TOKEN=...

# SHIPPING
SHIPPING_PROVIDER=manual
# RAJAONGKIR_API_KEY=...
# BITESHIP_API_KEY=...
```

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder     # roles + admin user
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Verify
php artisan about
```

---

## 4. AAPanel Nginx Configuration

### 4.1 Replace default site config
AAPanel default ada di `/www/server/panel/vhost/nginx/yourdomain.conf`. Replace isinya dengan:

```bash
sudo cp /www/wwwroot/karteks-energy-solution/deploy/aapanel-nginx.conf \
        /www/server/panel/vhost/nginx/karteksenergy.com.conf

# Adjust PHP-FPM socket ke versi yang terinstall:
# ls /tmp/php-cgi-*.sock
# Edit fastcgi_pass line di config

sudo nginx -t && sudo nginx -s reload
```

Atau via AAPanel UI: Website → karteksenergy.com → Config → paste isi `deploy/aapanel-nginx.conf` → Save.

### 4.2 Test static asset cache
```bash
curl -I https://karteksenergy.com/storage/test.jpg   # should show Cache-Control: public, max-age=604800
```

---

## 5. Supervisor (Queue Worker)

AAPanel → App Store → install **Supervisor**.

```bash
sudo cp /www/wwwroot/karteks-energy-solution/deploy/supervisor.conf \
        /etc/supervisor/conf.d/karteks.conf

# Adjust path di file jika tidak di /var/www
sudo nano /etc/supervisor/conf.d/karteks.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start karteks-worker:*
sudo supervisorctl start karteks-scheduler

# Verify
sudo supervisorctl status
```

Schedule Laravel (cron):
```bash
crontab -e
# Add:
* * * * * cd /www/wwwroot/karteks-energy-solution && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Cloudflare Tunnel Setup

### 6.1 Install cloudflared di home server
```bash
# Download
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb

# Login (buka browser, authorize)
cloudflared tunnel login

# Create tunnel
cloudflared tunnel create karteks-prod

# Note Tunnel ID dari output, contoh: a1b2c3d4-e5f6-...
```

### 6.2 Configure tunnel
`~/.cloudflared/config.yml`:
```yaml
tunnel: a1b2c3d4-e5f6-...        # ID dari step 6.1
credentials-file: /home/YOUR_USER/.cloudflared/<TUNNEL_ID>.json

ingress:
  - hostname: karteksenergy.com
    service: http://localhost:80
    originRequest:
      connectTimeout: 10s
      noHappyEyeballs: true
  - hostname: www.karteksenergy.com
    service: http://localhost:80
  - service: http_status:404
```

### 6.3 DNS routing
```bash
cloudflared tunnel route dns karteks-prod karteksenergy.com
cloudflared tunnel route dns karteks-prod www.karteksenergy.com
```

### 6.4 Run as service
```bash
sudo cloudflared service install
sudo systemctl enable cloudflared
sudo systemctl start cloudflared
sudo systemctl status cloudflared

# Verify tunnel
cloudflared tunnel info karteks-prod
```

### 6.5 Cloudflare Dashboard settings
- **SSL/TLS** → Full (strict) — kalau AAPanel pakai Let's Encrypt
- **SSL/TLS** → Full (non-strict) — kalau AAPanel pakai self-signed
- **SSL/TLS → Edge Certificates** → enable Always Use HTTPS, Min TLS 1.2
- **Speed → Optimization** → enable Auto Minify (HTML, CSS, JS)
- **Caching → Configuration** → tambah Cache Rule untuk `/storage/*` dan `/build/*`
- **Security → Bots** → enable Bot Fight Mode (free)
- **Security → WAF** → enable Cloudflare Managed Rules (free)

### 6.6 Cloudflare Cache Rules (recommended)
Di Cloudflare Dashboard → Caching → Cache Rules → Create rule:

**Rule 1: Static assets (1 year)**
- Name: `karteks-static-1y`
- Match: `(http.request.uri.path matches "^/storage/.*\\.(jpg|jpeg|png|webp|avif|gif|svg|ico|woff2?|ttf|eot|css|js)$")`
- Action: Cache eligible, Edge TTL 1 year, Browser TTL 1 year

**Rule 2: HTML pages (bypass)**
- Name: `karteks-html-no-cache`
- Match: `(http.request.uri.path matches "\\.html$") or (not http.request.uri.path matches "\\.(jpg|jpeg|png|webp|avif|gif|svg|ico|woff2?|ttf|eot|css|js)$")`
- Action: Bypass cache

---

## 7. Backup Strategy

### 7.1 AAPanel backup (daily)
AAPanel → Cron → Add task:
```bash
#!/bin/bash
# Backup database + storage
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/www/backup/karteks/$TIMESTAMP
mkdir -p $BACKUP_DIR

# Database
mysqldump -u karteks_user -p'YOUR_PASSWORD' karteks_prod | gzip > $BACKUP_DIR/db.sql.gz

# Storage (media files)
tar -czf $BACKUP_DIR/storage.tar.gz /www/wwwroot/karteks-energy-solution/storage/app/public

# Keep last 7 days
find /www/backup/karteks -mtime +7 -type d -exec rm -rf {} \;
```

### 7.2 Offsite backup (rclone)
```bash
# Install rclone
curl https://rclone.org/install.sh | sudo bash

# Configure (pakai Google Drive / Dropbox / S3)
rclone config

# Daily cron (add di cron file yang sama)
rclone sync /www/backup/karteks remote:karteks-backup --log-file=/var/log/rclone-karteks.log
```

---

## 8. Monitoring

### 8.1 Laravel Telescope (development only)
Telescope is included in this project. Disable di production:
```php
// config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', false),
```

### 8.2 Laravel Pulse (optional, free)
```bash
composer require laravel/pulse
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate
```

### 8.3 Server monitoring
AAPanel → App Store → install **Server-status** atau pakai **UptimeRobot** (free) untuk monitor HTTP 200 di `/up` endpoint.

---

## 9. Post-Deploy Checklist

```bash
# 1. Verify Laravel can boot
cd /www/wwwroot/karteks-energy-solution && php artisan about

# 2. Verify queue workers running
sudo supervisorctl status

# 3. Verify tunnel running
sudo systemctl status cloudflared

# 4. Verify Nginx
sudo nginx -t

# 5. Verify all extensions
php -m | grep -E "redis|opcache|imagick|intl"

# 6. Verify route cache
php artisan route:list | head -5

# 7. Verify media accessible
curl -I https://karteksenergy.com/storage/   # should be 404 or 200, not 500

# 8. Verify Cloudflare cache working
curl -I -H "Cache-Tag: test" https://karteksenergy.com/storage/test.jpg
# Look for: cf-cache-status: HIT or MISS

# 9. Smoke test user flow
# - Register new customer
# - Browse catalog
# - Add to cart
# - Checkout
# - Pay via Midtrans sandbox (set MIDTRANS_IS_PRODUCTION=false temporarily)
# - Verify order created, email sent, admin notification received

# 10. Switch to production mode
# Set MIDTRANS_IS_PRODUCTION=true, clear config cache
php artisan config:clear && php artisan config:cache
```

---

## 10. Troubleshooting

### 10.1 502 Bad Gateway
- Cek PHP-FPM running: `sudo systemctl status php-fpm-82`
- Cek socket path benar di Nginx config
- Cek Laravel `storage/logs/laravel.log`

### 10.2 Images not loading
- `php artisan storage:link` (run if missing)
- Cek permissions: `ls -la /www/wwwroot/karteks-energy-solution/public/storage`
- Cek Nginx config untuk `/storage/*` location

### 10.3 Queue jobs stuck
```bash
php artisan queue:clear
php artisan queue:work --queue=default --tries=3 --timeout=300
```

### 10.4 Cloudflare showing 521 (Web server is down)
- Tunnel tidak running: `sudo systemctl restart cloudflared`
- Nginx down: `sudo systemctl restart nginx`
- AAPanel firewall block: AAPanel → Security → Firewall → allow 80 (Loopback only OK)

### 10.5 Slow queries
```bash
# Enable MySQL slow query log
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Add: slow_query_log = 1, long_query_time = 2
sudo systemctl restart mysql

# Check slow queries
sudo tail -f /var/log/mysql/mysql-slow.log

# Run our indexes migration
php artisan migrate
```

---

## 11. Security Hardening

AAPanel → Security:
- Enable **SSH Key only** login
- Disable **root login** over SSH
- Enable **Firewall** — allow only 80, 443 (loopback), SSH (your IP)
- Install **Fail2ban**

Laravel-level:
- App key generated (rotate every 90 days)
- APP_DEBUG=false (verify with `grep APP_DEBUG .env`)
- Rate limiters aktif (sudah built-in FASE 4.8)
- CloudflareProxyTrust middleware aktif (built-in FASE 6)
- CSRF middleware aktif
- Backup database setiap hari ke offsite storage

---

## 12. Performance Tuning

### 12.1 MySQL
AAPanel → Database → Performance tuning:
- Query cache: off (MySQL 8 deprecated)
- innodb_buffer_pool_size: 50-70% of RAM
- max_connections: 200
- innodb_log_file_size: 256M

### 12.2 PHP-FPM
AAPanel → PHP → Config → `www.conf`:
```
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### 12.3 Redis
AAPanel → Redis → Config:
```
maxmemory 512mb
maxmemory-policy allkeys-lru
```

---

DONE. Project siap untuk production di home server AAPanel + Cloudflare Tunnel.

Untuk update selanjutnya:
1. Backup database + storage sebelum deploy
2. `cd /www/wwwroot/karteks-energy-solution && git pull`
3. `composer install --no-dev --optimize-autoloader`
4. `npm run build`
5. `php artisan migrate --force`
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. `sudo supervisorctl restart karteks-worker:*`
