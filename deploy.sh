#!/bin/bash
# Deploy script - Cron job ile her dakika calisir
# Yeni commit varsa repo'yu remote main ile zorla senkronize eder ve
# Laravel view cache'ini temizler. Eski "flag dosyasi" sartl(SI ELLE
# build) artik secimlidir; flag yoksa da yeni commit varsa calisir.

PROJECT_DIR="/var/www/www-root/data/www/randevumceptetest"
FLAG_FILE="$PROJECT_DIR/storage/.deploy-flag"
HASH_FILE="$PROJECT_DIR/storage/.deploy-last-hash"
LOG_DIR="$PROJECT_DIR/storage/logs"
LOG_FILE="$LOG_DIR/deploy.log"
PHP="/opt/php74/bin/php"

# Log dizini yoksa olustur
mkdir -p "$LOG_DIR"

log(){
    echo "$(date '+%Y-%m-%d %H:%M:%S') $1" >> "$LOG_FILE"
}

cd "$PROJECT_DIR" || { log "HATA: Proje dizinine gidilemedi"; exit 1; }

# 1) Uzak main'i cek
git fetch origin main >/dev/null 2>&1

# 2) Yerel ve uzak hash karsilastir
LOCAL=$(git rev-parse HEAD 2>/dev/null)
REMOTE=$(git rev-parse origin/main 2>/dev/null)
LAST=$(cat "$HASH_FILE" 2>/dev/null)

NEEDS_DEPLOY=0
if [ -f "$FLAG_FILE" ]; then NEEDS_DEPLOY=1; fi
if [ "$LOCAL" != "$REMOTE" ]; then NEEDS_DEPLOY=1; fi
if [ "$REMOTE" != "$LAST" ]; then NEEDS_DEPLOY=1; fi

if [ "$NEEDS_DEPLOY" -eq 0 ]; then
    exit 0
fi

log "=== Deploy basladi (LOCAL=$LOCAL REMOTE=$REMOTE LAST=$LAST) ==="

# 3) Zorla remote main'e esitle
RESET_OUT=$(git reset --hard origin/main 2>&1)
log "git reset --hard origin/main: $RESET_OUT"

# 4) Laravel view + uygulama cache'ini temizle
if [ -x "$PHP" ]; then
    VIEW_OUT=$($PHP artisan view:clear 2>&1)
    log "view:clear: $VIEW_OUT"
    CACHE_OUT=$($PHP artisan cache:clear 2>&1)
    log "cache:clear: $CACHE_OUT"
fi

# 5) Son commit'i kaydet (bir sonraki cron tetiklenmesin)
echo "$REMOTE" > "$HASH_FILE"

# 6) Logla ve flag'i temizle
LAST_COMMIT=$(git log -1 --oneline)
log "HEAD: $LAST_COMMIT"
rm -f "$FLAG_FILE"

log "=== Deploy tamamlandi ==="
