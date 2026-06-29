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

# 4) Laravel view + uygulama + route + config cache'ini temizle + pending migration'lari calistir
if [ -x "$PHP" ]; then
    VIEW_OUT=$($PHP artisan view:clear 2>&1)
    log "view:clear: $VIEW_OUT"
    CACHE_OUT=$($PHP artisan cache:clear 2>&1)
    log "cache:clear: $CACHE_OUT"
    ROUTE_OUT=$($PHP artisan route:clear 2>&1)
    log "route:clear: $ROUTE_OUT"
    CONFIG_OUT=$($PHP artisan config:clear 2>&1)
    log "config:clear: $CONFIG_OUT"
    MIGRATE_OUT=$($PHP artisan migrate --force 2>&1)
    log "migrate --force: $MIGRATE_OUT"
    # Hatirlatma arama worker'i (queue:work) bellekte ESKI job kodunu calistirir;
    # her deploy'da nazikce yeniden baslat ki guncel HatirlatmaAramaJob yuklensin.
    QRESTART_OUT=$($PHP artisan queue:restart 2>&1)
    log "queue:restart: $QRESTART_OUT"
fi

# 4b) OPcache sifirla (php-fpm SAPI). CLI artisan FPM opcache'ini sifirlamaz;
# bu yuzden .php degisiklikleri (controller vb.) reload olmadan canli olmaz.
# Localhost + token korumali endpoint'i curl ile tetikle.
OPCACHE_TOKEN=$(grep '^GITHUB_WEBHOOK_SECRET=' .env 2>/dev/null | cut -d= -f2- | tr -d '\r')
if [ -n "$OPCACHE_TOKEN" ]; then
    OPC_OUT=$(curl -s -k -m 10 -H "Host: apptest.randevumcepte.com.tr" \
        "https://127.0.0.1/public/opcache-reset.php?token=$OPCACHE_TOKEN" 2>&1)
    log "opcache-reset: $OPC_OUT"
else
    log "opcache-reset: GITHUB_WEBHOOK_SECRET bulunamadi, atlandi"
fi

# 5) Son commit'i kaydet (bir sonraki cron tetiklenmesin)
echo "$REMOTE" > "$HASH_FILE"

# 6) Logla ve flag'i temizle
LAST_COMMIT=$(git log -1 --oneline)
log "HEAD: $LAST_COMMIT"
rm -f "$FLAG_FILE"

log "=== Deploy tamamlandi ==="
