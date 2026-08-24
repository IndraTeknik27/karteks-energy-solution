#!/bin/bash
# =============================================================================
# KARTEKS Energy Solution — Deploy Script
# =============================================================================
# Usage: ./deploy/deploy.sh
#
# Prerequisites:
#   - Root/domain pointing to server
#   - PHP 8.2+, Composer, MySQL configured
#   - .env.production file on server (or passed as env vars)
#   - Supervisord installed
#
# This script handles the full production deployment.
# Run as the web server user (e.g., www-data) or with sudo.
# =============================================================================

set -euo pipefail

# --- Config ---
PROJECT_DIR="${PROJECT_DIR:-/var/www/karteks-energy-solution}"
WEB_USER="${WEB_USER:-www-data}"
BRANCH="${BRANCH:-main}"
NOW="$(date '+%Y-%m-%d %H:%M:%S')"

echo "============================================"
echo "KARTEKS Deploy — $NOW"
echo "Branch: $BRANCH"
echo "============================================"

cd "$PROJECT_DIR"

# --- 1. Maintenance mode ---
echo "[1/10] Enabling maintenance mode..."
sudo -u "$WEB_USER" php artisan down --render="errors::503"
echo "✓ Maintenance mode ON"

# --- 2. Pull latest code ---
echo "[2/10] Pulling latest code ($BRANCH)..."
sudo -u "$WEB_USER" git fetch origin
sudo -u "$WEB_USER" git reset --hard "origin/$BRANCH"
echo "✓ Code updated"

# --- 3. Install dependencies ---
echo "[3/10] Installing Composer dependencies..."
sudo -u "$WEB_USER" composer install --no-dev --optimize-autoloader --no-interaction
echo "✓ Dependencies installed"

# --- 4. Install frontend assets ---
echo "[4/10] Installing npm dependencies..."
if [ -f "package.json" ]; then
    npm ci --legacy-peer-deps 2>/dev/null || npm install --legacy-peer-deps
    npm run build
    echo "✓ Assets compiled"
else
    echo "⚠ No package.json found — skipping asset build"
fi

# --- 5. Environment ---
echo "[5/10] Checking environment file..."
if [ ! -f ".env" ]; then
    if [ -f ".env.production" ]; then
        cp .env.production .env
        echo "✓ Copied .env.production → .env"
    else
        echo "⚠ WARNING: .env not found and no .env.production available!"
        echo "  Copy .env.example to .env and configure manually."
    fi
fi

# --- 6. Key generation ---
echo "[6/10] Ensuring APP_KEY is set..."
sudo -u "$WEB_USER" php artisan key:generate --force
echo "✓ Key generated"

# --- 7. Database migrations ---
echo "[7/10] Running database migrations..."
sudo -u "$WEB_USER" php artisan migrate --force --no-interaction
echo "✓ Migrations complete"

# --- 8. Cache clearing + config cache ---
echo "[8/10] Clearing and rebuilding caches..."
sudo -u "$WEB_USER" php artisan optimize:clear
sudo -u "$WEB_USER" php artisan config:cache
sudo -u "$WEB_USER" php artisan route:cache
sudo -u "$WEB_USER" php artisan view:cache
sudo -u "$WEB_USER" php artisan event:cache
echo "✓ Caches rebuilt"

# --- 9. Permissions ---
echo "[9/10] Fixing file permissions..."
find "$PROJECT_DIR/storage" -type d -exec chmod 775 {} \;
find "$PROJECT_DIR/storage" -type f -exec chmod 664 {} \;
find "$PROJECT_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;
chmod -f 775 "$PROJECT_DIR/storage/app/public" 2>/dev/null || true
echo "✓ Permissions set"

# --- 10. Restart queue workers ---
echo "[10/10] Restarting queue workers..."
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart karteks-worker:*
echo "✓ Queue workers restarted"

# --- Done ---
echo "[DONE] Taking site out of maintenance mode..."
sudo -u "$WEB_USER" php artisan up
echo ""
echo "============================================"
echo "✓ Deploy complete — $NOW"
echo "============================================"
echo ""
echo "Queue workers: sudo supervisorctl status karteks-worker"
echo "Logs:          tail -f /var/log/karteks-worker.log"
echo "Maintenance:   php artisan down / up"
echo "Queue:         php artisan queue:failed"
