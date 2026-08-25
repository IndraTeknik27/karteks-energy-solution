# Deploy KARTEKS ke `shop.karteksofficial.com` via AAPanel

Panduan step-by-step spesifik untuk domain kamu. Asumsi: AAPanel sudah terinstall di home server, dan kamu pakai Cloudflare Tunnel (untuk hide home IP + free DDoS protection).

> Untuk full reference, lihat juga [DEPLOY_AAPANEL_CLOUDFLARE.md](DEPLOY_AAPANEL_CLOUDFLARE.md).

---

## STEP 0 — Persiapan (one-time)

### 0.1 Cek tools di server
Login SSH ke home server kamu:
```bash
php --version          # KARTEKS butuh 8.2+ (kamu punya 8.3 ✓)
mysql --version        # KARTEKS butuh 5.7+ / MariaDB 10.5+ (kamu punya 8.0 ✓)
redis-cli --version    # Kalau command not found, install: apt install -y redis-tools
composer --version     # HARUS 2.x (kamu punya 2.9.4 ✓)
node --version         # KARTEKS butuh 20+ (kamu punya 26 ✓)
git --version
```

**Output server kamu** (sudah diverifikasi):
```
PHP 8.3.32
MySQL 8.0.45
Composer 2.9.4
Node v26.7.0
Git 2.43.0
```

### 0.2 Composer root warning
Composer keluarkan warning "Do not run Composer as root". Untuk server, ada 2 opsi:
- **Rekomendasi**: jalankan sebagai `www` user: `sudo -u www composer install ...`
- **Quick fix**: set env var: `export COMPOSER_ALLOW_SUPERUSER=1` di `/etc/environment` (atau tambahkan di awal setiap command: `COMPOSER_ALLOW_SUPERUSER=1 composer install ...`)

### 0.3 Install redis-tools (untuk testing cache)
```bash
sudo apt install -y redis-tools
redis-cli ping   # harus return PONG (kalau Redis sudah running)
```

### 0.4 Siapkan domain di Cloudflare
- Login ke https://dash.cloudflare.com
- Pilih domain `karteksofficial.com` (atau tambahkan kalau belum ada)
- **DNS** → pastikan `shop.karteksofficial.com` ada record CNAME (placeholder dulu, akan di-point ke tunnel)

---

## STEP 1 — Install Software Stack di AAPanel

Login ke **AAPanel** (biasanya `http://[server-ip]:7800`).

### 1.1 App Store → install semua ini (free versions):
- ✅ **Nginx** 1.22+
- ✅ **PHP 8.3** (kamu sudah punya di `/www/server/php/83/bin/php`)
- ✅ **MySQL 8.0** (sudah terinstall di luar AAPanel — pastikan `systemctl status mysql` running)
- ✅ **Redis 7+** — kalau belum install: `sudo apt install redis-server && sudo systemctl enable --now redis`
- ✅ **Composer 2** (sudah ada ✓)
- ✅ **Supervisor** — kalau belum: `sudo apt install -y supervisor && sudo systemctl enable --now supervisor`

### 1.2 PHP 8.3 → Extensions → install (via AAPanel UI)
```
pdo_mysql, mbstring, openssl, curl, gd, zip, xml, bcmath,
intl, opcache, redis, fileinfo, tokenizer, mysqli, sodium
```
**WAJIB ada**: `redis` (untuk CACHE_STORE=redis dan QUEUE_CONNECTION=redis).

### 1.3 PHP 8.3 → Config → `php.ini` (via AAPanel UI):
```
memory_limit = 512M
max_execution_time = 300
post_max_size = 100M
upload_max_filesize = 100M
date.timezone = Asia/Makassar
max_input_vars = 3000
```

### 1.4 PHP 8.3 → Config → `opcache.ini`:
```
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

**Setelah semua diubah**: klik **Save** → restart PHP service. Verifikasi:
```bash
/www/server/php/83/bin/php -m | grep -E "^(redis|opcache|pdo_mysql|gd|intl|bcmath)$"
# Harus menampilkan 6 baris (semua loaded)
```

---

## STEP 2 — Create Database

AAPanel → **Database** → **Add database**:
- **Database name**: `karteks_shop`
- **Username**: `karteks_user`
- **Password**: klik **Generate** (copy ke password manager kamu!)
- **Access**: Local Server
- **Encoding**: utf8mb4
- Klik **Submit**

**Simpan info ini**:
```
DB_DATABASE=karteks_shop
DB_USERNAME=karteks_user
DB_PASSWORD=<yang tadi kamu generate>
```

---

## STEP 3 — Create Website di AAPanel

AAPanel → **Website** → **Add site**:
- **Domain**: `shop.karteksofficial.com`
- **Description**: KARTEKS Production Shop
- **Root directory**: `/www/wwwroot/shop.karteksofficial.com`
- **FTP**: skip
- **Database**: pilih yang baru dibuat (`karteks_shop`)
- **PHP version**: **8.3**
- **SSL**: ❌ **JANGAN enable dulu** (karena kita pakai Cloudflare Tunnel — SSL di-handle Cloudflare, AAPanel tidak perlu issue Let's Encrypt)
- Klik **Submit**

AAPanel akan create site skeleton kosong di `/www/wwwroot/shop.karteksofficial.com`.

### Note: kalau PHP 8.3 belum terlihat di dropdown AAPanel

AAPanel install PHP 8.3 via App Store akan create symlink di `/www/server/php/83/`. Kalau App Store tidak punya PHP 8.3, install manual:
```bash
# Skip kalau AAPanel sudah punya
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-gd php8.3-intl php8.3-bcmath php8.3-zip php8.3-mbstring php8.3-xml php8.3-curl
```

Socket check:
```bash
ls -la /run/php/php8.3-fpm.sock   # Debian/Ubuntu native
ls -la /tmp/php-cgi-83.sock       # AAPanel convention
```

Lihat STEP 8.3 untuk cara adjust Nginx config ke socket yang benar.

---

## STEP 4 — Push Project dari Lokal ke Server

### 4.1 Di lokal kamu (Windows), buka Git Bash / Terminal:
```bash
cd C:\laragon\www\karteks-energy-solution

# Kalau belum punya Git remote, init + add
git init
git add .
git commit -m "Initial commit"
```

### 4.2 Setup Git di server AAPanel:
Login SSH ke server:
```bash
# Install git kalau belum
sudo apt update && sudo apt install -y git

# Setup git identity (untuk commit otomatis)
git config --global user.email "deploy@karteksofficial.com"
git config --global user.name "KARTEKS Deploy Bot"
```

### 4.3 Copy project dari lokal ke server (pilih salah satu):

**Cara A: GitHub (recommended)**
```bash
# Di lokal: push ke GitHub
git remote add origin git@github.com:karteksofficial/karteks-energy-solution.git
git push -u origin main

# Di server: clone
cd /www/wwwroot
sudo rm -rf shop.karteksofficial.com   # remove skeleton AAPanel
sudo git clone git@github.com:karteksofficial/karteks-energy-solution.git shop.karteksofficial.com
```

**Cara B: SCP (kalau tidak mau pakai GitHub)**
```bash
# Di lokal (PowerShell/Git Bash)
scp -r C:\laragon\www\karteks-energy-solution\* root@SERVER_IP:/tmp/karteks-source/

# Di server
cd /www/wwwroot/shop.karteksofficial.com
# (kosongkan dulu)
sudo rm -rf public/* .[!.]*
# Copy
sudo cp -r /tmp/karteks-source/* /tmp/karteks-source/.[!.]* .
```

### 4.4 Set permissions (AAPanel pakai `www` user):
```bash
cd /www/wwwroot/shop.karteksofficial.com
sudo chown -R www:www .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

**Verify**: pastikan `www` user bisa read project:
```bash
sudo -u www ls -la /www/wwwroot/shop.karteksofficial.com/artisan
# Harus berhasil tanpa permission error
```

---

## STEP 5 — Install Dependencies & Build

Di server (pakai `www` user untuk avoid composer root warning):
```bash
cd /www/wwwroot/shop.karteksofficial.com

# Composer (PHP dependencies) — pakai www user untuk bypass root warning
sudo -u www composer install --no-dev --optimize-autoloader

# Atau kalau tetap sebagai root dengan COMPOSER_ALLOW_SUPERUSER:
# export COMPOSER_ALLOW_SUPERUSER=1
# composer install --no-dev --optimize-autoloader

# NPM (CSS/JS build)
sudo -u www npm install --production
sudo -u www npm run build

# Copy production env template
sudo -u www cp .env.production.example .env
sudo chown www:www .env
```

---

## STEP 6 — Configure `.env`

Edit `.env`:
```bash
nano .env
```

Isi dengan nilai ini (sesuaikan dengan database dan domain kamu):

```env
APP_NAME="KARTEKS Energy Solution"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shop.karteksofficial.com

LOG_CHANNEL=daily
LOG_LEVEL=warning

# Database (sesuai STEP 2)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karteks_shop
DB_USERNAME=karteks_user
DB_PASSWORD=<password yang di-generate tadi>

# Cache & Session → Redis (production)
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3

# Queue → Redis (production)
QUEUE_CONNECTION=redis

# Filesystem (local, single-server)
FILESYSTEM_DISK=public

# Mail (WAJIB, untuk notifikasi order)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=karteksenergy27@gmail.com
MAIL_PASSWORD=<app password Gmail — bukan password biasa>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@shop.karteksofficial.com"
MAIL_FROM_NAME="${APP_NAME}"

# Midtrans payment
MIDTRANS_ENV=production
MIDTRANS_SERVER_KEY=<dari dashboard.midtrans.com>
MIDTRANS_CLIENT_KEY=<dari dashboard.midtrans.com>
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_NOTIFICATION_URL="${APP_URL}/api/v1/payments/midtrans/notification"

# Shipping
SHIPPING_PROVIDER=manual
FREE_SHIPPING_THRESHOLD=
SHIPPING_ORIGIN_CITY=Gowa

# Company info (muncul di invoice)
COMPANY_NAME="KARTEKS ENERGY SOLUTION"
COMPANY_EMAIL="karteksenergy27@gmail.com"
COMPANY_PHONE="+6281545326426"
COMPANY_WHATSAPP="+6281545326426"
COMPANY_WEBSITE="https://shop.karteksofficial.com"
COMPANY_ADDRESS="Jln. Bonto Marannu, Perumahan Tanjung Kencana Residence No. 8, Kabupaten Gowa, Sulawesi Selatan, Indonesia"

# WhatsApp
WHATSAPP_BUSINESS_NUMBER="+6281545326426"
WHATSAPP_PROVIDER=fonnte
WHATSAPP_API_TOKEN=<token dari fonnte.com>
WHATSAPP_ENABLED=true

# FCM (untuk Flutter app — bisa kosong dulu)
FCM_SERVER_KEY=
FCM_PROJECT_ID=

CART_TAX_RATE=0
VITE_APP_NAME="${APP_NAME}"
```

**Save**: `Ctrl+O` → `Enter` → `Ctrl+X`

---

## STEP 7 — Laravel Production Setup

```bash
cd /www/wwwroot/shop.karteksofficial.com

# Pakai PHP 8.3 binary eksplisit (sesuai STEP 0)
/www/server/php/83/bin/php artisan --version

# Generate encryption key
sudo -u www /www/server/php/83/bin/php artisan key:generate

# Storage symlink (untuk media library)
/www/server/php/83/bin/php artisan storage:link

# Run migrations (termasuk FASE 6 composite indexes)
sudo -u www /www/server/php/83/bin/php artisan migrate --force

# Seed initial data (roles + admin user)
sudo -u www /www/server/php/83/bin/php artisan db:seed --class=ProductionSeeder

# Optimize for production
sudo -u www /www/server/php/83/bin/php artisan config:cache
sudo -u www /www/server/php/83/bin/php artisan route:cache
sudo -u www /www/server/php/83/bin/php artisan view:cache
sudo -u www /www/server/php/83/bin/php artisan event:cache

# Verify (cek env, cache driver, db connection)
/www/server/php/83/bin/php artisan about
```

**Catatan**: bisa juga `php artisan` asalkan `php` di PATH mengarah ke PHP 8.3 (cek dengan `which php`). AAPanel default biasanya set ini di `.bashrc`. Kalau tidak, pakai full path di atas.

---

## STEP 8 — Configure Nginx di AAPanel

### 8.1 Backup default config dulu
```bash
sudo cp /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf \
        /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf.bak
```

### 8.2 Replace dengan config tuned untuk AAPanel
```bash
sudo cp /www/wwwroot/shop.karteksofficial.com/deploy/aapanel-nginx.conf \
        /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf
```

### 8.3 Edit PHP-FPM socket path untuk PHP 8.3

Cek dulu socket yang ada:
```bash
# Cari semua PHP-FPM socket di server
find / -name "*php*cgi*.sock" 2>/dev/null
find / -name "*php*fpm*.sock" 2>/dev/null
ls /tmp/php-cgi-*.sock 2>/dev/null
ls /run/php/*.sock 2>/dev/null
```

**Paling umum untuk server kamu**:

| Setup | Socket path |
|---|---|
| AAPanel (PHP 8.3 via App Store) | `/tmp/php-cgi-83.sock` |
| AAPanel (default PHP) | `/tmp/php-cgi-83.sock` |
| Native Ubuntu/Debian (apt install) | `/run/php/php8.3-fpm.sock` |

Sesuaikan Nginx config ke socket yang ketemu:
```bash
# Edit aapanel-nginx.conf dan ganti fastcgi_pass ke socket yang ada:
# Contoh untuk native Debian socket:
sudo sed -i 's|fastcgi_pass unix:/tmp/php-cgi-82.sock;|fastcgi_pass unix:/run/php/php8.3-fpm.sock;|' \
    /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf

# Atau untuk AAPanel PHP 8.3:
sudo sed -i 's|fastcgi_pass unix:/tmp/php-cgi-82.sock;|fastcgi_pass unix:/tmp/php-cgi-83.sock;|' \
    /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf
```

### 8.4 Test & reload Nginx
```bash
sudo nginx -t
sudo nginx -s reload
```

Kalau ada error `connect() failed (111: Connection refused)` ke PHP-FPM socket, berarti socket path masih salah atau PHP-FPM belum running:
```bash
# Cek PHP-FPM running
sudo systemctl status php8.3-fpm       # Debian/Ubuntu native
sudo systemctl status php-fpm-83       # AAPanel convention (check /etc/init.d/)
sudo /etc/init.d/php-fpm-83 status 2>/dev/null   # older AAPanel

# Start kalau belum
sudo systemctl start php8.3-fpm
```

Kalau via AAPanel UI: **Website** → `shop.karteksofficial.com` → **Reload**.

---

## STEP 9 — Setup Cloudflare Tunnel

### 9.1 Install cloudflared di server
```bash
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb
cloudflared --version
```

### 9.2 Login ke Cloudflare
```bash
cloudflared tunnel login
```
→ Browser terbuka → pilih domain `karteksofficial.com` → Authorize.

### 9.3 Create tunnel
```bash
cloudflared tunnel create karteks-shop
```
→ Output `Tunnel credentials written to /home/YOUR_USER/.cloudflared/<UUID>.json`. **Copy UUID ini** (misal: `a1b2c3d4-e5f6-...`).

### 9.4 Create config file
```bash
mkdir -p ~/.cloudflared
nano ~/.cloudflared/config.yml
```

Isi dengan:
```yaml
tunnel: a1b2c3d4-e5f6-...        # UUID dari step 9.3
credentials-file: /home/YOUR_USER/.cloudflared/a1b2c3d4-e5f6-....json

ingress:
  - hostname: shop.karteksofficial.com
    service: http://localhost:80
    originRequest:
      connectTimeout: 10s
      noHappyEyeballs: true
  - hostname: www.shop.karteksofficial.com
    service: http://localhost:80
  - service: http_status:404
```

Save & exit (`Ctrl+O`, `Enter`, `Ctrl+X`).

### 9.5 DNS routing
```bash
cloudflared tunnel route dns karteks-shop shop.karteksofficial.com
cloudflared tunnel route dns karteks-shop www.shop.karteksofficial.com
```
→ Cek di Cloudflare DNS dashboard: record CNAME baru untuk `shop` dan `www.shop` ke `<UUID>.cfargotunnel.com`.

### 9.6 Run as system service
```bash
sudo cloudflared service install
sudo systemctl enable cloudflared
sudo systemctl start cloudflared
sudo systemctl status cloudflared    # harus active (running)
```

### 9.7 Verify tunnel
```bash
cloudflared tunnel info karteks-shop
curl -I https://shop.karteksofficial.com
# Harus return 200 OK dari server AAPanel kamu
```

---

## STEP 10 — Cloudflare Dashboard Settings

Login ke https://dash.cloudflare.com → pilih domain `karteksofficial.com`.

### 10.1 SSL/TLS
- **SSL/TLS** → Overview → Mode: **Full** (strict kalau AAPanel punya SSL, Full kalau tidak)
- **SSL/TLS** → Edge Certificates → Always Use HTTPS: **ON**
- **SSL/TLS** → Edge Certificates → Minimum TLS Version: **1.2**
- **SSL/TLS** → Edge Certificates → Automatic HTTPS Rewrites: **ON**

### 10.2 Caching Rules
**Caching** → **Cache Rules** → Create rule:

**Rule 1**: Cache static assets 1 year
- Name: `karteks-static-1y`
- Match: `(http.request.uri.path matches "^/storage/.*\\.(jpg|jpeg|png|webp|avif|gif|svg|ico|woff2?|ttf|eot|css|js)$")`
- Action: Cache eligible, Edge TTL: 1 year, Browser TTL: 1 year

**Rule 2**: Bypass HTML/Blade
- Name: `karteks-html-no-cache`
- Match: `(not http.request.uri.path matches "\\.(jpg|jpeg|png|webp|avif|gif|svg|ico|woff2?|ttf|eot|css|js)$")`
- Action: Bypass cache

### 10.3 Security
- **Security** → **Bots** → Bot Fight Mode: **ON**
- **Security** → **WAF** → Managed Rules → semua rule free tier: **ON**
- **Security** → **Settings** → Security Level: **Medium**

---

## STEP 11 — Setup Supervisor (Queue Worker)

```bash
sudo cp /www/wwwroot/shop.karteksofficial.com/deploy/supervisor.conf \
        /etc/supervisor/conf.d/karteks-shop.conf

# Edit path + PHP binary path
sudo nano /etc/supervisor/conf.d/karteks-shop.conf
```

Di dalam file, ubah:
- `/var/www/karteks-energy-solution` → `/www/wwwroot/shop.karteksofficial.com` (2 baris: command dan directory)
- `php artisan` → `/www/server/php/83/bin/php artisan` (pakai full PHP path agar supervisor pakai PHP 8.3)

Save, lalu:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start karteks-shop-worker:*
sudo supervisorctl start karteks-shop-scheduler

# Verify (semua harus RUNNING)
sudo supervisorctl status
```

### Setup scheduler cron (alternatif untuk scheduler process):
```bash
sudo crontab -e
```
Tambah line (kalau scheduler process di supervisor tidak jalan):
```
* * * * * cd /www/wwwroot/shop.karteksofficial.com && /www/server/php/83/bin/php artisan schedule:run >> /dev/null 2>&1
```

> Pakai **cron** atau **supervisor scheduler** — jangan dua-duanya, pilih salah satu. Cron lebih reliable karena persist setelah reboot.

---

## STEP 12 — Post-Deploy Verification

```bash
# 1. Cek Laravel boot
cd /www/wwwroot/shop.karteksofficial.com && php artisan about

# 2. Cek queue worker jalan
sudo supervisorctl status    # karteks-shop-worker harus RUNNING

# 3. Cek tunnel
sudo systemctl status cloudflared    # active (running)

# 4. Cek Nginx
sudo nginx -t

# 5. Test HTTPS
curl -I https://shop.karteksofficial.com
# Harus return 200/302 dari Cloudflare

# 6. Test static asset cache
curl -I https://shop.karteksofficial.com/storage/test.jpg
# Lihat header cf-cache-status: HIT atau MISS

# 7. Test admin panel
# Buka https://shop.karteksofficial.com/admin
# Login dengan super admin (yang di-seed tadi)

# 8. Test user flow
# - Register customer baru
# - Browse catalog
# - Tambah ke cart
# - Checkout
# - Pay via Midtrans SANDBOX (set MIDTRANS_IS_PRODUCTION=false dulu, test, lalu balikin ke true)
# - Verify order created, email terkirim, admin notif muncul

# 9. Finalize: switch ke Midtrans production
nano .env
# Set MIDTRANS_IS_PRODUCTION=true, MIDTRANS_SERVER_KEY=<production key>
sudo -u www /www/server/php/83/bin/php artisan config:clear && sudo -u www /www/server/php/83/bin/php artisan config:cache
```

---

## Update Code (git pull)

Setiap kali ada update code:
```bash
cd /www/wwwroot/shop.karteksofficial.com

# Backup dulu
mysqldump -u karteks_user -p'PASSWORD' karteks_shop | gzip > /www/backup/db-$(date +%Y%m%d).sql.gz

# Pull + install
sudo -u www git pull origin main
sudo -u www composer install --no-dev --optimize-autoloader
sudo -u www npm run build

# Migrate (kalau ada migration baru)
sudo -u www /www/server/php/83/bin/php artisan migrate --force

# Clear + rebuild cache
sudo -u www /www/server/php/83/bin/php artisan optimize:clear
sudo -u www /www/server/php/83/bin/php artisan config:cache
sudo -u www /www/server/php/83/bin/php artisan route:cache
sudo -u www /www/server/php/83/bin/php artisan view:cache

# Restart workers
sudo supervisorctl restart karteks-shop-worker:*
```

---

## Troubleshooting

### ❌ 502 Bad Gateway (PHP-FPM socket issue)
- Cek socket file ada di path yang Nginx config pakai:
  ```bash
  find / -name "*php*fpm*.sock" 2>/dev/null
  find / -name "*php*cgi*.sock" 2>/dev/null
  ```
- Cek PHP-FPM service running:
  ```bash
  sudo systemctl status php8.3-fpm
  ```
- Cek Nginx error log: `sudo tail -50 /www/wwwlogs/shop.karteksofficial.com.error.log`
- Cek Laravel log: `sudo tail -50 /www/wwwroot/shop.karteksofficial.com/storage/logs/laravel.log`

### ❌ 521 (Web server down) di Cloudflare
- Tunnel down: `sudo systemctl restart cloudflared`
- Nginx down: `sudo systemctl restart nginx` atau restart via AAPanel UI

### ❌ Composer warning root
- Selalu pakai `sudo -u www composer ...` atau set `export COMPOSER_ALLOW_SUPERUSER=1`

### ❌ Redis not connecting
```bash
# Cek Redis running
sudo systemctl status redis
sudo systemctl start redis    # kalau down

# Test
redis-cli ping    # harus return PONG

# Cek PHP redis extension
php -m | grep redis    # harus ada
```
Kalau extension belum ada, install via AAPanel → PHP 8.3 → Extensions.

### ❌ Database connection error
- Cek credential `.env` benar (DB_HOST, DB_USERNAME, DB_PASSWORD)
- Test manual: `mysql -u karteks_user -p karteks_shop`
- Cek MySQL running: `sudo systemctl status mysql`

### ❌ Images 404
```bash
cd /www/wwwroot/shop.karteksofficial.com
sudo -u www /www/server/php/83/bin/php artisan storage:link
ls -la public/storage    # harus symlink → ../storage/app/public
```

### ❌ Email tidak terkirim
- Cek Gmail App Password (bukan password biasa — generate di https://myaccount.google.com/apppasswords)
- Cek SPF/DKIM record di Cloudflare DNS untuk `karteksofficial.com`
- Test: `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('you@gmail.com')->subject('test'));`

### ❌ Midtrans webhook tidak sampai
- Set MIDTRANS_NOTIFICATION_URL = `https://shop.karteksofficial.com/api/v1/payments/midtrans/notification`
- Test webhook manual: `curl -X POST https://shop.karteksofficial.com/api/v1/payments/midtrans/notification -H "Content-Type: application/json" -d '{}'`
- Cek Laravel log untuk signature validation error

---

## Ringkasan Command (copy-paste untuk fresh install)

```bash
# === AAPanel Server ===
cd /www/wwwroot
sudo rm -rf shop.karteksofficial.com
sudo git clone git@github.com:karteksofficial/karteks-energy-solution.git shop.karteksofficial.com
cd shop.karteksofficial.com

sudo chown -R www:www .
sudo chmod -R 775 storage bootstrap/cache

# Composer & NPM pakai www user (avoid root warning)
sudo -u www composer install --no-dev --optimize-autoloader
sudo -u www npm install --production
sudo -u www npm run build

sudo -u www cp .env.production.example .env
sudo nano .env   # edit sesuai STEP 6

# Laravel artisan dengan full PHP 8.3 path
sudo -u www /www/server/php/83/bin/php artisan key:generate
sudo -u www /www/server/php/83/bin/php artisan storage:link
sudo -u www /www/server/php/83/bin/php artisan migrate --force
sudo -u www /www/server/php/83/bin/php artisan db:seed --class=ProductionSeeder
sudo -u www /www/server/php/83/bin/php artisan optimize

# Nginx (adjust PHP-FPM socket path dulu, lihat STEP 8.3)
sudo cp deploy/aapanel-nginx.conf /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf
sudo sed -i 's|karteks-energy-solution|shop.karteksofficial.com|g' /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf
sudo sed -i 's|fastcgi_pass unix:/tmp/php-cgi-82.sock;|fastcgi_pass unix:/tmp/php-cgi-83.sock;|' /www/server/panel/vhost/nginx/shop.karteksofficial.com.conf
sudo nginx -t && sudo nginx -s reload

# Cloudflare Tunnel (kalau belum)
cloudflared tunnel login
cloudflared tunnel create karteks-shop
# ... config + route dns ...

# Supervisor (edit file dulu untuk PHP path + project path)
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/karteks-shop.conf
sudo sed -i 's|/var/www/karteks-energy-solution|/www/wwwroot/shop.karteksofficial.com|g' /etc/supervisor/conf.d/karteks-shop.conf
sudo sed -i 's|php artisan|/www/server/php/83/bin/php artisan|g' /etc/supervisor/conf.d/karteks-shop.conf
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start karteks-shop-worker:* karteks-shop-scheduler

# Verify
curl -I https://shop.karteksofficial.com
```

---

## Catatan Spesifik Server Kamu

Output `php --version` dan tool check di server kamu:
- ✅ **PHP 8.3.32** di `/www/server/php/83/bin/php` (AAPanel convention)
- ✅ **MySQL 8.0.45** (lebih baru dari requirement 5.7+)
- ✅ **Composer 2.9.4**
- ✅ **Node v26.7.0**
- ✅ **Git 2.43.0**
- ⚠️ **redis-cli not found** — install via `apt install redis-tools` (STEP 0.3)
- ⚠️ **Redis server not verified** — kalau belum running, `apt install redis-server && systemctl enable --now redis`
