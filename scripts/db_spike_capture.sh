#!/usr/bin/env bash
# MariaDB CPU spike yakalama — Threads_running eşiği aşılınca processlist + innodb status kaydeder.
# Kurulum (sunucuda):
#   nohup bash scripts/db_spike_capture.sh > /dev/null 2>&1 &
# Loglar: storage/logs/db_spike/ altına timestamp'li dosyalar.
#
# DB bağlantısı için .env'den okumaya çalışır; gerekirse aşağıdaki değişkenleri elle doldur.

cd "$(dirname "$0")/.." || exit 1
ENVF=".env"
# cut -f2- (= sonrasi her sey), \r temizligi, surrounding tirnak temizligi
envval() { grep -m1 "^$1=" "$ENVF" | cut -d= -f2- | tr -d '\r' | sed 's/^"//; s/"$//; s/^'\''//; s/'\''$//'; }
DBH=$(envval DB_HOST)
DBP=$(envval DB_PORT)
DBU=$(envval DB_USERNAME)
DBPW=$(envval DB_PASSWORD)
DBN=$(envval DB_DATABASE)
# Sifreyi komut satirina yazma; MYSQL_PWD ile gecir (ozel karakter sorunu olmaz)
export MYSQL_PWD="$DBPW"

THRESHOLD="${1:-15}"     # Threads_running eşiği (varsayılan 15 — tırmanmayı erken yakala)
SLEEP="${2:-2}"          # örnekleme aralığı (sn)
OUTDIR="storage/logs/db_spike"
mkdir -p "$OUTDIR"

# DOCKER modu: DBSPIKE_DOCKER=<container_adi> verilirse mysql container icinde
# root ile calisir (host yetki/sifre cikmazi olmaz). Aksi halde host'tan baglanir.
if [ -n "$DBSPIKE_DOCKER" ]; then
  RPW=$(docker exec "$DBSPIKE_DOCKER" printenv MYSQL_ROOT_PASSWORD 2>/dev/null)
  # container icinde root ile, secili DB'ye baglan
  runsql() { docker exec -e MYSQL_PWD="$RPW" "$DBSPIKE_DOCKER" mysql -uroot "$DBN" -N -B -e "$1" 2>&1; }
else
  runsql() { MYSQL_PWD="$DBPW" mysql --connect-timeout=2 -h"$DBH" -P"$DBP" -u"$DBU" "$DBN" -N -B -e "$1" 2>&1; }
fi
# Başlangıçta baseline Threads_running'i göster ki eşiği doğru seçebilelim
ERR=$(runsql "SHOW GLOBAL STATUS LIKE 'Threads_running';")
BASE=$(echo "$ERR" | awk '/Threads_running/{print $2}')
MODE=$([ -n "$DBSPIKE_DOCKER" ] && echo "docker:$DBSPIKE_DOCKER" || echo "host:$DBH:$DBP")
if [ -z "$BASE" ]; then
  echo "[$(date)] BAGLANTI HATASI ($MODE) | mysql cikti: $(echo "$ERR" | head -1)" | tee -a "$OUTDIR/capture.log"
else
  echo "[$(date)] spike capture başladı ($MODE, eşik=${THRESHOLD}, aralık=${SLEEP}s, mevcut Threads_running=${BASE})" | tee -a "$OUTDIR/capture.log"
fi

while true; do
  TR=$(runsql "SHOW GLOBAL STATUS LIKE 'Threads_running';" | awk '/Threads_running/{print $2}')
  if [ -n "$TR" ] && [ "$TR" -ge "$THRESHOLD" ] 2>/dev/null; then
    TS=$(date +%Y%m%d_%H%M%S)
    F="$OUTDIR/spike_${TS}.log"
    {
      echo "==== $(date) | Threads_running=$TR ===="
      echo "---- En uzun süren 25 sorgu (info dolu) ----"
      runsql "SELECT ID,USER,HOST,DB,COMMAND,TIME,STATE,LEFT(INFO,400) AS Q FROM information_schema.PROCESSLIST WHERE INFO IS NOT NULL ORDER BY TIME DESC LIMIT 25;"
      echo "---- INNODB STATUS (kilitler/transaction) ----"
      runsql "SHOW ENGINE INNODB STATUS\G"
    } >> "$F" 2>&1
    echo "[$(date)] SPIKE yakalandı: Threads_running=$TR -> $F" >> "$OUTDIR/capture.log"
    sleep 5
  fi
  sleep "$SLEEP"
done
