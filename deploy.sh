#!/bin/bash
# Deploy script - Cron job ile her dakika calisir
# Flag dosyasi varsa repo'yu remote main ile zorla senkronize eder

PROJECT_DIR="/var/www/www-root/data/www/randevumceptetest"
FLAG_FILE="$PROJECT_DIR/storage/.deploy-flag"
LOG_DIR="$PROJECT_DIR/storage/logs"
LOG_FILE="$LOG_DIR/deploy.log"
PHP="/opt/php74/bin/php"

# Log dizini yoksa olustur
mkdir -p "$LOG_DIR"

log(){
    echo "$(date '+%Y-%m-%d %H:%M:%S') $1" >> "$LOG_FILE"
}

if [ ! -f "$FLAG_FILE" ]; then
    exit 0
fi

log "=== Deploy basladi ==="

cd "$PROJECT_DIR" || { log "HATA: Proje dizinine gidilemedi"; rm -f "$FLAG_FILE"; exit 1; }

# 1) Uzak main'i cek (conflict olsa bile fetch basarili olur)
FETCH_OUT=$(git fetch origin main 2>&1)
log "git fetch: $FETCH_OUT"

# 2) Zorla remote main'e esitle (conflict'leri bypass eder)
RESET_OUT=$(git reset --hard origin/main 2>&1)
log "git reset --hard origin/main: $RESET_OUT"

# 3) Laravel view cache'i temizle (blade degisiklikleri yansisin)
if [ -x "$PHP" ]; then
    VIEW_OUT=$($PHP artisan view:clear 2>&1)
    log "view:clear: $VIEW_OUT"
fi

# 4) Son commit'i logla
LAST_COMMIT=$(git log -1 --oneline)
log "HEAD: $LAST_COMMIT"

# 5) Flag'i sil
rm -f "$FLAG_FILE"

log "=== Deploy tamamlandi ==="
