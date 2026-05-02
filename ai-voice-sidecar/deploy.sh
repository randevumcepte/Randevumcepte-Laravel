#!/bin/bash
# AI sidecar deploy yardimcisi.
# Kullanim:  ./deploy.sh
#
# Yapar:
#   1) git pull
#   2) package.json/package-lock.json degistiyse npm install
#   3) PM2 ile servisi reload (yoksa baslat)
#
# Cron ile dakikada bir cagirilabilir; degisiklik yoksa hicbir sey yapmaz.

set -e
cd "$(dirname "$0")"

LOG=/var/log/ai-sidecar-deploy.log
APP=ai-sidecar
ENTRY=test/test-ari.js   # ileride src/server.js'ye gecince guncellenecek

log() { echo "[$(date '+%F %T')] $*" | tee -a "$LOG"; }

# 1) git pull (cron zaten cekiyor olabilir, idempotent)
BEFORE=$(git rev-parse HEAD)
git pull --quiet || { log "git pull HATA"; exit 1; }
AFTER=$(git rev-parse HEAD)

if [ "$BEFORE" = "$AFTER" ]; then
  exit 0   # degisiklik yok, sessiz cik
fi

log "Yeni commit: $BEFORE -> $AFTER"

# 2) Bagimliliklar degisti mi? (package.json veya package-lock.json)
if git diff --name-only "$BEFORE" "$AFTER" | grep -qE '^(package\.json|package-lock\.json)$'; then
  log "package.json/lock degisti -> npm install"
  npm install --silent || { log "npm install HATA"; exit 1; }
fi

# 3) PM2 ile reload (yoksa baslat)
if command -v pm2 >/dev/null 2>&1; then
  if pm2 describe "$APP" >/dev/null 2>&1; then
    log "PM2 reload"
    pm2 reload "$APP" --update-env >/dev/null
  else
    log "PM2 ilk kez baslatiliyor"
    pm2 start "$ENTRY" --name "$APP"
    pm2 save >/dev/null
  fi
else
  log "PM2 kurulu degil. Once: npm install -g pm2"
  exit 1
fi

log "Deploy OK"
