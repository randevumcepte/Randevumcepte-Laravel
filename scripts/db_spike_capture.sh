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

# --connect-timeout=2: doygunlukta hızlı başarısız ol, takılma yapma (sifre MYSQL_PWD'de)
MYSQL="mysql --connect-timeout=2 -h${DBH} -P${DBP} -u${DBU} ${DBN} -N -B"

# Başlangıçta baseline Threads_running'i göster ki eşiği doğru seçebilelim
ERR=$($MYSQL -e "SHOW GLOBAL STATUS LIKE 'Threads_running';" 2>&1)
BASE=$(echo "$ERR" | awk '/Threads_running/{print $2}')
if [ -z "$BASE" ]; then
  echo "[$(date)] BAGLANTI HATASI -> host=$DBH port=$DBP user=$DBU db=$DBN | mysql cikti: $(echo "$ERR" | head -1)" | tee -a "$OUTDIR/capture.log"
else
  echo "[$(date)] spike capture başladı (eşik=${THRESHOLD}, aralık=${SLEEP}s, mevcut Threads_running=${BASE})" | tee -a "$OUTDIR/capture.log"
fi

while true; do
  TR=$($MYSQL -e "SHOW GLOBAL STATUS LIKE 'Threads_running';" 2>/dev/null | awk '{print $2}')
  if [ -n "$TR" ] && [ "$TR" -ge "$THRESHOLD" ] 2>/dev/null; then
    TS=$(date +%Y%m%d_%H%M%S)
    F="$OUTDIR/spike_${TS}.log"
    {
      echo "==== $(date) | Threads_running=$TR ===="
      echo "---- SHOW FULL PROCESSLIST ----"
      $MYSQL -e "SHOW FULL PROCESSLIST;"
      echo "---- En uzun süren 20 sorgu (info dolu) ----"
      $MYSQL -e "SELECT ID,USER,HOST,DB,COMMAND,TIME,STATE,LEFT(INFO,300) AS Q FROM information_schema.PROCESSLIST WHERE INFO IS NOT NULL ORDER BY TIME DESC LIMIT 20;"
      echo "---- INNODB STATUS (kilitler/transaction) ----"
      $MYSQL -e "SHOW ENGINE INNODB STATUS\G"
    } >> "$F" 2>&1
    echo "[$(date)] SPIKE yakalandı: Threads_running=$TR -> $F" >> "$OUTDIR/capture.log"
    sleep 5
  fi
  sleep "$SLEEP"
done
