# KARTEKS Energy Solution

> Solusi energi terbarukan, kendaraan listrik, custom battery, dan konsultasi profesional.

**Stack**: Laravel 13 + Filament 5 + Sanctum 4 + Spatie Media 11 + Midtrans + MySQL

---

## Table of Contents

- [Quick Start](#quick-start)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [API Reference](#api-reference)
- [Frontend](#frontend)
- [Admin Panel](#admin-panel-filament)
- [Email Templates](#email-templates)
- [Notification System](#notification-system)
- [Payment Flow](#payment-flow-midtrans)
- [Shipping](#shipping)
- [Security](#security)
- [Deployment](#deployment)
- [Testing](#testing)
- [Useful Commands](#useful-commands)

---

## Quick Start

```bash
# Clone & install
git clone https://github.com/karteksofficial/karteks-energy-solution.git
cd karteks-energy-solution
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database (MySQL must be running)
php artisan migrate

# Seed initial data
php artisan db:seed

# Start dev server
php artisan serve
```

Open `http://127.0.0.1:8000` — you should see the homepage.

**Default admin credentials:**
- Email: `admin@karteks-energy-solution.com`
- Password: `password`

Admin panel: `http://127.0.0.1:8000/admin`

---

## Requirements

| Dependency | Version | Notes |
|---|---|---|
| PHP | 8.2+ | |
| MySQL | 8.0+ | |
| Composer | 2.x | |
| Node.js | 18+ | For Vite asset compilation |
| Midtrans Account | Sandbox/Production | [midtrans.com](https://midtrans.com) |
| RajaOngkir/BiteShip (optional) | API Key | For real-time shipping rates |

---

## Installation

### 1. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials and API keys.

### 2. Database

```bash
# Create MySQL database
mysql -u root -p
CREATE DATABASE karteks_energy_solution CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

php artisan migrate
php artisan db:seed
```

### 3. Asset Build (optional — for production CSS/JS)

```bash
npm install
npm run build
```

For development with hot reload:
```bash
npm run dev
```

### 4. Start Server

```bash
php artisan serve --port=8000
```

---

## Configuration

### Environment Variables

**Critical variables to set in `.env`:**

```env
APP_URL=https://your-domain.com

# Database
DB_HOST=127.0.0.1
DB_DATABASE=karteks_energy_solution
DB_USERNAME=root
DB_PASSWORD=your_password

# Midtrans (required for payments)
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_ENV=sandbox   # or 'production'

# WhatsApp (optional)
WHATSAPP_BUSINESS_NUMBER=+6281234567890
WHATSAPP_API_TOKEN=your_fonnte_or_wablas_token

# FCM for Flutter (optional)
FCM_SERVER_KEY=your_fcm_key
FCM_PROJECT_ID=your_project_id

# Shipping (optional — defaults to manual pricing)
SHIPPING_PROVIDER=manual  # or 'rajaongkir' or 'biteship'
RAJAONGKIR_API_KEY=your_rajaongkir_key
```

### Full list of all env vars — see `.env.example`

---

## Architecture

### API Design

- **Version**: v1 — all routes prefixed `/api/v1/`
- **Auth**: Laravel Sanctum (Bearer token)
- **Format**: JSON — all responses follow `{success, data, meta, message}` pattern
- **Guest support**: Cart endpoints work with or without auth (uses `X-Session-Id` header)
- **Rate limiting**: Named limiters per action (see `RouteServiceProvider`)

### Response Format

**Success:**
```json
{
    "success": true,
    "message": "Operation successful.",
    "data": { ... },
    "meta": { "current_page": 1, "per_page": 15, "total": 42 }
}
```

**Error:**
```json
{
    "success": false,
    "message": "Validation failed.",
    "error": "validation_error",
    "errors": { "field": ["Error message."] }
}
```

### Services Architecture

Business logic lives in Service classes in `app/Services/`:

| Service | Responsibility |
|---|---|
| `CartService` | Guest + auth cart, coupon, merge |
| `OrderService` | Order creation, status transitions |
| `MidtransService` | Snap token, webhook, refund |
| `ShippingService` | Provider abstraction, rate calculation |
| `NotificationService` | Multi-channel notification dispatch |
| `SeoService` | Meta tag generation, JSON-LD schemas |
| `HomepageService` | CMS-driven homepage sections |

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/    # API controllers (modular per resource)
│   │   ├── Auth/
│   │   ├── Cart/
│   │   ├── Checkout/
│   │   ├── Order/
│   │   ├── CustomBattery/
│   │   ├── Quotation/
│   │   ├── Booking/
│   │   ├── Payment/
│   │   ├── Notification/
│   │   ├── Customer/
│   │   ├── Review/
│   │   └── Public/            # Public catalog controllers
│   ├── Middleware/
│   │   ├── SecurityHeaders.php       # CSP, HSTS, anti-clickjacking
│   │   ├── ValidateFileUpload.php    # MIME + magic bytes + extension check
│   │   ├── VerifyCsrfHeader.php     # CSRF double-submit cookie
│   │   ├── ForceJsonResponse.php    # Force JSON for /api/*
│   │   ├── ApiResponseHeaders.php   # CORS + API versioning headers
│   │   ├── RequestId.php            # X-Request-Id for tracing
│   │   └── AuditLog.php            # Action logging
│   └── Requests/Api/V1/           # Form Request validation
├── Models/                        # Eloquent models + relationships
├── Services/                       # Business logic layer
│   ├── CartService.php
│   ├── OrderService.php
│   ├── MidtransService.php
│   ├── ShippingService.php
│   ├── NotificationService.php
│   ├── SeoService.php
│   └── HomepageService.php
├── Mail/                           # Branded email mailable classes
│   ├── Order/                      # 4 order emails
│   │   ├── Placed.php, Paid.php, Shipped.php, Delivered.php
│   │   └── Placed.blade.php, Paid.blade.php, ...
│   ├── CustomBattery/             # 3 custom battery emails
│   ├── Quotation/                 # 3 quotation emails
│   ├── Booking/                    # 4 booking emails
│   ├── Contact/                   # 2 contact emails
│   ├── Newsletter/                # 1 newsletter email
│   └── Notification/              # 1 generic notification email
├── Notifications/                  # Laravel notification classes
│   ├── DatabaseNotification.php    # DB channel
│   ├── EmailNotification.php      # Queued email channel
│   ├── WhatsAppNotification.php    # Fonnte/Wablas channel
│   └── FcmNotification.php        # Firebase Cloud Messaging
├── Support/Traits/
│   └── InputSanitizer.php          # XSS sanitization trait
├── Enums/                          # PHP 8.1+ backed enums
│   ├── OrderStatus.php
│   ├── PaymentStatus.php
│   ├── QuotationStatus.php
│   ├── CustomBatteryStatus.php
│   └── BookingStatus.php
└── Providers/
    ├── RouteServiceProvider.php    # 10 named rate limiters
    └── AppServiceProvider.php

config/
├── karteks.php                    # Business config (company, shipping, numbering)
└── midtrans.php                   # Midtrans config

database/
├── migrations/                     # 40+ tables
└── seeders/

resources/views/
├── components/                    # Reusable Blade components
│   ├── card.blade.php
│   ├── button.blade.php
│   ├── badge.blade.php
│   ├── alert.blade.php
│   ├── section.blade.php
│   ├── price.blade.php
│   ├── rating.blade.php
│   ├── empty-state.blade.php
│   ├── input.blade.php
│   └── select.blade.php
├── layouts/app.blade.php          # Main layout
├── partials/
│   ├── navbar.blade.php           # Sticky nav + mobile menu
│   ├── footer.blade.php           # Multi-column footer
│   ├── product-card.blade.php     # Shared product card
│   └── home-sections/             # 10 CMS-driven section partials
├── pages/                         # Customer-facing pages
│   ├── home.blade.php            # CMS-driven homepage
│   ├── catalog/                   # Product listing + detail
│   ├── cart/
│   ├── checkout/
│   ├── blog/
│   ├── services/
│   └── payment/                  # Midtrans redirect pages
├── dashboard/                     # Customer dashboard pages
│   ├── orders/
│   ├── wishlist/
│   ├── reviews/
│   ├── custom-battery/
│   ├── quotation/
│   └── booking/
└── emails/                       # Branded HTML email templates

routes/api/v1/                    # API v1 routes
├── auth.php                       # Auth endpoints
├── cart.php                       # Cart endpoints
├── checkout.php                   # Checkout + place order
├── order.php                      # Order management
├── wishlist.php
├── review.php
├── profile.php                    # Profile + addresses
├── custom-battery.php
├── quotation.php
├── booking.php
├── payment.php                    # Payment initiation + status
├── notification.php               # Notifications + FCM tokens
├── contact.php                    # Public contact + newsletter
└── public.php                     # Public catalog + content

docs/
├── api/
│   ├── v1.md                      # Full API documentation
│   └── karteks-api-v1.postman-collection.json
```

---

## API Reference

See [docs/api/v1.md](docs/api/v1.md) for the complete API reference with all endpoints, request/response examples, and error codes.

**Import Postman collection**: `docs/api/karteks-api-v1.postman-collection.json`

### Quick Reference

| Module | Endpoints | Auth |
|---|---|---|
| Auth | Register, Login, Logout, Profile, Password | Guest / Auth |
| Products | List, Detail, Related, Reviews | Public |
| Cart | CRUD, Coupon, Shipping | Guest / Auth |
| Checkout | Preview, Place Order | Auth |
| Orders | List, Detail, Cancel, Tracking, Invoice | Auth |
| Wishlist | Toggle, Move to Cart | Auth |
| Reviews | Create, Update, Delete | Auth |
| Profile | CRUD, Avatar | Auth |
| Addresses | CRUD, Set Primary | Auth |
| Custom Battery | Submit, Track, Revisions | Auth |
| Quotations | List, Accept, Reject, PDF | Auth |
| Bookings | List, Create, Cancel, Reschedule | Auth |
| Payments | Initiate, Status, Refresh | Auth |
| Notifications | List, Mark Read, FCM Token | Auth |
| Contact | Submit Form | Public |
| Newsletter | Subscribe, Unsubscribe | Public |

---

## Frontend

### Stack

- **Framework**: Laravel Blade + Vite
- **Styling**: Tailwind CSS v4 with custom design tokens
- **JavaScript**: Alpine.js (no build step)
- **Icons**: Heroicons (inline SVG)
- **Images**: Spatie MediaLibrary for upload + conversion

### Design System

Available Blade components in `resources/views/components/`:

```blade
{{-- Card --}}
<x-card variant="shadow" hover>
    Content
</x-card>

{{-- Button --}}
<x-button variant="primary" size="lg" loading href="/link">
    Lihat Produk
</x-button>

{{-- Badge --}}
<x-badge variant="success">Terverifikasi</x-badge>
<x-badge variant="discount">-20%</x-badge>

{{-- Alert --}}
<x-alert type="success" title="Berhasil!" dismissible>
    Pesanan berhasil dibuat.
</x-alert>

{{-- Section Wrapper --}}
<x-section title="Featured Products" subtitle="Best sellers" eyebrow="Produk">
    Content
</x-section>

{{-- Price Display --}}
<x-price :price="250000" :sale-price="199000" size="lg" show-discount />

{{-- Star Rating --}}
<x-rating :value="4.5" :count="128" />

{{-- Empty State --}}
<x-empty-state icon="cart" title="Keranjang kosong" description="Yuk mulai belanja!">
    <x-button href="/catalog">Lihat Katalog</x-button>
</x-empty-state>

{{-- Form Input --}}
<x-input name="email" label="Email" type="email" :error="$errors->first('email')" />

{{-- Form Select --}}
<x-select name="courier" label="Kurir" :options="['jne' => 'JNE', 'pos' => 'POS']" />
```

### Brand Tokens (Tailwind v4 CSS variables)

```css
--color-brand-50  through --color-brand-950   /* Emerald primary */
--color-accent-400, --color-accent-500, --color-accent-600  /* Amber accent */
```

### Homepage Sections (CMS-driven)

The homepage is driven by `HomepageSection` database records. Admin can reorder and configure sections via Filament.

10 section types:
- Hero Banner, Featured Categories, Featured Products
- Category Showcase (EV Car, EV Bike)
- Custom Battery Promo, Services Grid
- Testimonials, Blog Highlights
- Brand Logos, Custom HTML

---

## Admin Panel (Filament)

**URL**: `/admin`

### Filament Resources (18 total)

| Resource | Model | Description |
|---|---|---|
| UserResource | User | Admin + customer user management |
| RoleResource | Role | Spatie roles (Super Admin, Admin, dll) |
| PermissionResource | Permission | Granular permissions |
| CategoryResource | Category | Product categories |
| BrandResource | Brand | Brands |
| ProductResource | Product | Products with media gallery |
| ServiceResource | Service | Service types |
| BlogResource | Blog | Blog posts |
| PageResource | Page | Static pages |
| FaqResource | Faq | FAQ items |
| TestimonialResource | Testimonial | Customer testimonials |
| MenuResource | Menu | Navigation menus |
| BannerResource | Banner | Homepage banners |
| HomepageSectionResource | HomepageSection | CMS sections |
| CouponResource | Coupon | Discount codes |
| ReviewResource | Review | Product reviews |
| NewsletterSubscriberResource | NewsletterSubscriber | Email subscribers |
| ContactMessageResource | ContactMessage | Contact form submissions |
| SiteSettingResource | SiteSetting | Key-value settings |
| AuditLogResource | AuditLog | Action audit trail |
| CustomBatteryRequestResource | CustomBatteryRequest | CBR submissions |
| QuotationResource | Quotation | Quotations |
| ServiceBookingResource | ServiceBooking | Service bookings |
| NotificationCenter | — | Notification management page |

### Dashboard Widgets

9 widgets on the Filament dashboard:
- Stats overview (orders, revenue, pending)
- Revenue chart (30-day bar chart)
- Orders chart (daily line chart)
- Low stock alert
- Top selling products
- Latest orders
- Pending custom battery requests
- Pending service bookings

---

## Email Templates

17 branded HTML email templates. All use `resources/views/emails/layout.blade.php` as the base layout.

| Mailable | Trigger |
|---|---|
| `Order/Placed` | Customer places order |
| `Order/Paid` | Midtrans confirms payment |
| `Order/Shipped` | Admin marks as shipped |
| `Order/Delivered` | Customer confirms delivery |
| `CustomBattery/Submitted` | Customer submits CBR |
| `CustomBattery/StatusChanged` | Admin changes CBR status |
| `CustomBattery/RevisionRequested` | Admin requests revision |
| `Quotation/Sent` | Admin sends quotation |
| `Quotation/Accepted` | Customer accepts quotation |
| `Quotation/Rejected` | Customer rejects quotation |
| `Booking/Created` | Customer books service |
| `Booking/Confirmed` | Admin confirms booking |
| `Booking/Rescheduled` | Booking rescheduled |
| `Booking/Cancelled` | Booking cancelled |
| `Newsletter/Welcome` | New subscriber welcome |
| `Contact/AdminNotification` | Admin receives contact form |
| `Contact/AutoReply` | Auto-reply to contact form submitter |

All emails are queued (`ShouldQueue`) for performance.

---

## Notification System

5 notification channels — customers can configure preferences per type:

| Channel | Implementation | Use Case |
|---|---|---|
| Database | `DatabaseNotification` | In-app notification bell |
| Email | `EmailNotification` (queued) | Order updates, quotations |
| WhatsApp | `WhatsAppNotification` | Real-time alerts |
| FCM | `FcmNotification` | Flutter push notifications |
| Broadcast | Pusher/log | Real-time events (future) |

Notification types: `order_status`, `payment_status`, `custom_battery`, `quotation`, `booking`, `newsletter`, `promo`

---

## Payment Flow (Midtrans)

```
1. Customer places order → Order created (status: PENDING_PAYMENT)
2. POST /payments/orders/{orderNumber}/initiate
   → MidtransService creates Snap transaction
   → Returns redirect_url
3. Customer redirected to Midtrans Snap
4. Customer completes payment on Midtrans
5. Midtrans sends webhook to /api/v1/payments/midtrans/notification
   → Signature validated
   → Order status updated (PAID/PROCESSING/etc.)
   → Notification sent to customer
6. Customer redirected to /payment/finish or /payment/unfinish
```

**Refund flow**: Admin can trigger refund from Filament → Midtrans refund API → Payment status updated.

---

## Shipping

Current implementation: **Manual rates** (no third-party API required).

Provider: `manual` (default), `rajaongkir` (optional), `biteship` (optional)

To enable RajaOngkir:
```env
SHIPPING_PROVIDER=rajaongkir
RAJAONGKIR_API_KEY=your_key
RAJAONGKIR_PACKAGE=starter
```

**Free shipping**: Set `FREE_SHIPPING_THRESHOLD` in `.env` (e.g., `500000` for free shipping on orders above Rp 500,000).

---

## Security

### Implemented Protections

| Protection | Implementation |
|---|---|
| Security Headers | `SecurityHeaders` middleware (CSP, HSTS, X-Frame-Options, dll) |
| Rate Limiting | 10 named limiters in `RouteServiceProvider` |
| Brute Force | `LoginAttempt` model — 5 attempts/15min per email |
| XSS Sanitization | `InputSanitizer` trait on Register + Login requests |
| File Upload | `ValidateFileUpload` middleware (MIME, magic bytes, extension) |
| CSRF | `VerifyCsrfHeader` middleware for custom forms |
| CSRF (Filament) | Livewire handles automatically |
| API Auth | Laravel Sanctum Bearer tokens |
| Mass Assignment | Form Requests + `$fillable` whitelist |
| SQL Injection | Eloquent ORM (parameterized queries) |

### CSP Notes

Content Security Policy allows:
- self
- Inline scripts (`'unsafe-inline'`) — required for Blade + Livewire
- `https://*.midtrans.com` — Midtrans Snap
- Google Fonts

For strict CSP (required for some compliance), implement nonce-based inline scripts.

---

## Deployment

### Server Requirements

- Ubuntu 22.04 LTS (recommended) or similar
- Nginx or Apache
- PHP 8.2+ with extensions: `pdo_mysql`, `bcmath`, `fileinfo`, `mbstring`, `openssl`, `zip`, `curl`
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (for asset compilation)
- Supervisor (for queue workers)
- SSL certificate (Let's Encrypt)

### Deployment Checklist

```bash
# 1. Clone & install
git clone https://github.com/karteksofficial/karteks-energy-solution.git
cd karteks-energy-solution
composer install --optimize-autoloader --no-dev

# 2. Environment
cp .env.example .env
php artisan key:generate
# Edit .env with production values (APP_ENV=production, APP_DEBUG=false)

# 3. Database
php artisan migrate --force

# 4. Seed admin user
php artisan db:seed --class=AdminUserSeeder --force

# 5. Cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Queue worker (see supervisor config below)

# 7. Scheduler (crontab)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Nginx Config

```nginx
server {
    listen 80;
    server_name karteksenergy.com www.karteksenergy.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name karteksenergy.com www.karteksenergy.com;

    root /path-to-project/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/karteksenergy.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/karteksenergy.com/privkey.pem;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }

    # Static assets — long cache
    location ~* \.(css|js|woff2?|ttf|eot|svg|jpg|jpeg|png|webp|ico|avif)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Queue Worker (Supervisor)

```ini
# /etc/supervisor/conf.d/karteks-worker.conf
[program:karteks-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/karteks-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start karteks-worker:*
```

### Production .env Additions

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://karteksenergy.com

# Queue — use Redis for production
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache
CACHE_STORE=redis

# Log
LOG_CHANNEL=daily
LOG_LEVEL=error

# Midtrans (production keys)
MIDTRANS_ENV=production
MIDTRANS_SERVER_KEY=your_production_server_key
MIDTRANS_CLIENT_KEY=your_production_client_key

# HSTS (enable in SecurityHeaders middleware — already configured)
# Strict-Transport-Security: max-age=31536000; includeSubDomains
```

### Pre-deploy Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] HTTPS enforced (301 redirect in Nginx)
- [ ] `HSTS` header enabled (in `SecurityHeaders.php`)
- [ ] Strong `APP_KEY` (regenerate: `php artisan key:generate`)
- [ ] MySQL credentials not in version control
- [ ] Midtrans production keys set
- [ ] `QUEUE_CONNECTION=redis` or `database` (not `sync`)
- [ ] Queue workers running via Supervisor
- [ ] Scheduler cron entry added
- [ ] `LOG_LEVEL=error` (not `debug`)
- [ ] SecurityHeaders middleware active (CSP blocks XSS)

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Test with coverage
php artisan test --coverage

# Browser tests (Dusk)
php artisan dusk

# Check routes
php artisan route:list --path=api/v1

# Check routes with middleware
php artisan route:list --path=api/v1/auth
```

---

## Useful Commands

```bash
# Development
php artisan serve --port=8000
npm run dev              # Hot reload
php artisan tinker       # Interactive REPL

# Database
php artisan migrate:fresh --seed
php artisan db:seed

# Cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Notifications
php artisan notification:table   # Create notifications table

# Media
php artisan media-library:regenerate
php artisan media-library:remove   # Clean orphan media

# Queue
php artisan queue:work
php artisan queue:flush          # Clear failed jobs
php artisan queue:retry all       # Retry failed jobs

# Scheduler (add to crontab)
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

# Security
php artisan sanitize:dry-run '<script>alert("xss")</script>hello'

# Audit
php artisan audit:clean          # Clean logs older than retention_days
```

---

## Flutter Integration Notes

The API is designed to be consumed by a Flutter mobile app:

1. **Store token**: Use `flutter_secure_storage` to persist Sanctum token
2. **Guest cart**: Generate UUID, store locally, send as `X-Session-Id` header
3. **On login**: Cart items from guest session auto-merge with user cart
4. **Push notifications**: Register FCM token via `POST /notifications/fcm-token`
5. **On logout**: Unregister FCM token via `DELETE /notifications/fcm-token`
6. **Images**: All URLs are absolute — use `cached_network_image` package

See `docs/api/v1.md` for complete API reference.

---

## Contributing

1. Branch from `main`: `git checkout -b feature/your-feature`
2. Follow existing code style (PSR-12, Laravel conventions)
3. Write tests for new features
4. Submit PR with description

---

## License

Proprietary — KARTEKS Energy Solution. All rights reserved.
