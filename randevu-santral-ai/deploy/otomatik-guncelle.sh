#!/usr/bin/env bash
# Randevu AI Santral kopru — GIT'TEN OTOMATIK GUNCELLEME (cron ile)
# Repo'nun YALNIZ randevu-santral-ai klasorunu ceker (sparse checkout), degisiklik varsa
# /opt/randevu-santral-ai'ye kopyalar ve servisi yeniden baslatir.
#
# Guvenlik:
#  - .env, node_modules ve sunucudaki diger dosyalara DOKUNMAZ (silme yapmaz, yalniz gunceller).
#  - Kopyalamadan once tum .js dosyalari `node --check` ile denetlenir; hata varsa kurulmaz.
#  - AKTIF ASISTAN GORUSMESI VARSA yeniden baslatmaz (gorusme kopmasin); bir sonraki calismada tekrar dener.
#  - Ayni anda iki kez calismaz (flock).
#
# Kurulum ve cron: deploy/OTOMATIK-GUNCELLEME.md

set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/randevumcepte/Randevumcepte-Laravel.git}"
BRANCH="${BRANCH:-main}"
KAYNAK="${KAYNAK:-/opt/randevu-santral-ai-git}"     # sparse git kopyasi
HEDEF="${HEDEF:-/opt/randevu-santral-ai}"           # calisan kopru
SERVIS="${SERVIS:-randevu-santral-ai}"
ARI_APP="${ARI_APP:-randevu-santral-ai}"
DURUM="$HEDEF/.son-kurulan-commit"
KILIT="/var/lock/randevu-santral-guncelle.lock"

log() { echo "$(date '+%F %T') $*"; }

exec 9>"$KILIT"
flock -n 9 || { log "baska bir guncelleme calisiyor, cikiliyor"; exit 0; }

# 1) Sparse kopya yoksa olustur (yalniz randevu-santral-ai klasoru, sig gecmis)
if [ ! -d "$KAYNAK/.git" ]; then
  log "ilk kurulum: $KAYNAK"
  git clone --depth 1 --filter=blob:none --no-checkout --branch "$BRANCH" "$REPO_URL" "$KAYNAK"
  git -C "$KAYNAK" sparse-checkout init --cone
  git -C "$KAYNAK" sparse-checkout set randevu-santral-ai
  git -C "$KAYNAK" checkout "$BRANCH"
fi

# 2) Guncel hale getir
git -C "$KAYNAK" fetch --depth 1 -q origin "$BRANCH"
git -C "$KAYNAK" reset -q --hard "origin/$BRANCH"

YENI=$(git -C "$KAYNAK" log -1 --format=%H -- randevu-santral-ai)
ESKI=$(cat "$DURUM" 2>/dev/null || echo "")
if [ "$YENI" = "$ESKI" ]; then
  exit 0   # degisiklik yok (sessiz cik, log sismesin)
fi
log "yeni surum: ${ESKI:0:8} -> ${YENI:0:8} ($(git -C "$KAYNAK" log -1 --format=%s -- randevu-santral-ai | cut -c1-90))"

# 3) Sozdizimi denetimi (bozuk dosya kurulmasin)
for f in "$KAYNAK"/randevu-santral-ai/src/*.js; do
  node --check "$f" || { log "HATA: sozdizimi bozuk: $f -> kurulum YAPILMADI"; exit 1; }
done

# 4) Aktif asistan gorusmesi varsa bekle (cron bir sonraki calismada tekrar dener)
if command -v asterisk >/dev/null 2>&1; then
  AKTIF=$(asterisk -rx "core show channels concise" 2>/dev/null | grep -c "Stasis!${ARI_APP}" || true)
  if [ "${AKTIF:-0}" -gt 0 ]; then
    log "aktif gorusme var ($AKTIF kanal) -> yeniden baslatma ertelendi"
    exit 0
  fi
fi

# 5) Kopyala (.env / node_modules / kayit dosyalarina dokunmadan; --delete YOK)
PAKET_ONCE=$(md5sum "$HEDEF/package.json" 2>/dev/null | cut -d' ' -f1 || echo "")
rsync -a \
  --exclude '.env' --exclude 'node_modules/' --exclude '.son-kurulan-commit' --exclude '*.log' \
  "$KAYNAK/randevu-santral-ai/" "$HEDEF/"
PAKET_SONRA=$(md5sum "$HEDEF/package.json" | cut -d' ' -f1)

# 6) package.json degistiyse bagimliliklari kur
if [ "$PAKET_ONCE" != "$PAKET_SONRA" ]; then
  log "package.json degisti -> npm install"
  (cd "$HEDEF" && npm install --omit=dev --no-audit --no-fund)
fi

# 7) Yeniden baslat + saglik kontrolu
systemctl restart "$SERVIS"
sleep 4
if systemctl is-active --quiet "$SERVIS"; then
  echo "$YENI" > "$DURUM"
  log "kuruldu ve servis calisiyor (${YENI:0:8})"
else
  log "HATA: servis yeniden baslatildiktan sonra calismiyor! journalctl -u $SERVIS -n 50"
  exit 1
fi
