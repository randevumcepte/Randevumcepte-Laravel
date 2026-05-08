#!/bin/bash
# Canli sunucu icin SECMELI deploy.
# Sadece app/Http/Controllers/ApiController.php ve routes/api.php
# dosyalarini origin/main'den ceker. Diger dosyalara DOKUNMAZ
# (onlari kullanici manuel yukluyor).
#
# Kurulum (canli sunucuda):
#   chmod +x deploy-api-files.sh
#   crontab -e:
#     * * * * * /CANLI/PROJECT/PATH/deploy-api-files.sh >/dev/null 2>&1

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"

FILES=(
    "app/Http/Controllers/ApiController.php"
    "routes/api.php"
)

HASH_FILE="$PROJECT_DIR/storage/.deploy-api-last-hash"
LOG_DIR="$PROJECT_DIR/storage/logs"
LOG_FILE="$LOG_DIR/deploy-api.log"
PHP="/opt/php74/bin/php"

mkdir -p "$LOG_DIR"

log(){
    echo "$(date '+%Y-%m-%d %H:%M:%S') $1" >> "$LOG_FILE"
}

cd "$PROJECT_DIR" || { log "HATA: Proje dizinine gidilemedi: $PROJECT_DIR"; exit 1; }

# 1) Uzak main'i cek (working tree'ye dokunmaz)
git fetch origin main >/dev/null 2>&1 || { log "HATA: git fetch basarisiz"; exit 1; }

# 2) Hedef dosyalarin origin/main'deki blob hash'lerini topla
CURRENT_SIG=""
for f in "${FILES[@]}"; do
    BLOB=$(git rev-parse "origin/main:$f" 2>/dev/null)
    if [ -z "$BLOB" ]; then
        log "UYARI: origin/main icinde bulunamadi: $f"
        BLOB="MISSING"
    fi
    CURRENT_SIG="${CURRENT_SIG}${f}=${BLOB};"
done

LAST_SIG=$(cat "$HASH_FILE" 2>/dev/null)

if [ "$CURRENT_SIG" = "$LAST_SIG" ]; then
    # Degisiklik yok
    exit 0
fi

log "=== API deploy basladi ==="
log "Eski sig: $LAST_SIG"
log "Yeni sig: $CURRENT_SIG"

# 3) Sadece bu iki dosyayi origin/main'den checkout et.
#    Bu komut hem index'i hem working tree'yi gunceller, DIGER dosyalara dokunmaz.
CHANGED=0
for f in "${FILES[@]}"; do
    if git rev-parse "origin/main:$f" >/dev/null 2>&1; then
        OUT=$(git checkout origin/main -- "$f" 2>&1)
        if [ $? -eq 0 ]; then
            log "OK: $f guncellendi. $OUT"
            CHANGED=1
        else
            log "HATA: $f checkout basarisiz: $OUT"
        fi
    else
        log "ATLA: $f origin/main'de yok"
    fi
done

# 4) Laravel cache'leri temizle (sadece degisiklik olduysa)
if [ "$CHANGED" -eq 1 ] && [ -x "$PHP" ]; then
    VIEW_OUT=$($PHP artisan view:clear 2>&1)
    log "view:clear: $VIEW_OUT"
    CACHE_OUT=$($PHP artisan cache:clear 2>&1)
    log "cache:clear: $CACHE_OUT"
    ROUTE_OUT=$($PHP artisan route:clear 2>&1)
    log "route:clear: $ROUTE_OUT"
fi

# 5) Imzayi kaydet
echo "$CURRENT_SIG" > "$HASH_FILE"

log "=== API deploy tamamlandi ==="
