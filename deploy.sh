#!/bin/bash
# =============================================================
# Auto Deploy Script - GitHub Webhook ile tetiklenir
# =============================================================

# Proje dizini (sunucunuzdaki Laravel dizini)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
LOG_FILE="$PROJECT_DIR/storage/logs/deploy.log"

echo "========================================" >> "$LOG_FILE"
echo "Deploy başladı: $(date '+%Y-%m-%d %H:%M:%S')" >> "$LOG_FILE"

cd "$PROJECT_DIR" || exit 1

# Git pull
echo "Git pull yapılıyor..." >> "$LOG_FILE"
git fetch origin 2>&1 >> "$LOG_FILE"
git pull origin $(git rev-parse --abbrev-ref HEAD) 2>&1 >> "$LOG_FILE"

# Composer install (yeni paket eklendiyse)
if [ -f "composer.json" ]; then
    echo "Composer install..." >> "$LOG_FILE"
    composer install --no-interaction --no-dev --prefer-dist 2>&1 >> "$LOG_FILE"
fi

# Laravel cache temizle
echo "Cache temizleniyor..." >> "$LOG_FILE"
php artisan config:cache 2>&1 >> "$LOG_FILE"
php artisan route:cache 2>&1 >> "$LOG_FILE"
php artisan view:cache 2>&1 >> "$LOG_FILE"

# Dosya izinleri
chmod -R 775 storage bootstrap/cache 2>&1 >> "$LOG_FILE"

echo "Deploy tamamlandı: $(date '+%Y-%m-%d %H:%M:%S')" >> "$LOG_FILE"
echo "========================================" >> "$LOG_FILE"
