#!/usr/bin/env bash
#
# ============================================================================
#  GÜVENLİK DUVARI — Root Watchdog  (Sistem Yönetim v2)
# ============================================================================
#  Her dakika cron ile çalışır. Şunları yapar:
#    1) HTTP flood tespit eder (ss ile IP başına eşzamanlı bağlantı sayısı) ve
#       eşiği aşan saldırgan IP'yi ipset+iptables ile ANINDA engeller.
#    2) SSH brute-force tespit eder (auth.log'da son 1 dk'daki Failed password)
#       ve eşiği aşan IP'yi engeller (fail2ban'a ek savunma).
#    3) Yüksek sistem yükünü (load average) izler, eşik üstünde uyarı verir.
#    4) Panelde tanımlı whitelist/blacklist kurallarını uygular.
#    5) Her olayı DB'ye (guvenlik_olaylari) yazar; YENİ engellenen IP olursa
#       'guvenlik:bildir' ile WhatsApp/SMS alarm gönderir.
#
#  Panel (www-root/PHP) iptables çalıştıramaz; bu script ROOT olarak enforce eder.
#  Panel deklaratiftir (kural yazar), watchdog uygular.
#
#  Kurulum: docs/GUVENLIK_DUVARI.md
# ============================================================================

set -uo pipefail

# ----------------------------- AYARLAR --------------------------------------
APP_DIR="/var/www/www-root/data/www/randevumcepte"
PHP_BIN="/opt/php74/bin/php"
ENV_FILE="$APP_DIR/.env"

IPSET_NAME="guvenlik_ban"           # otomatik/geçici engeller (timeout'lu)
IPSET_PERM="guvenlik_ban_kalici"    # blacklist (kalıcı)

CONN_THRESHOLD=60                   # tek IP'den 80/443'e bu kadar eşzamanlı bağlantı = flood
SSH_THRESHOLD=15                    # tek IP'den 1 dk içinde bu kadar başarısız SSH = brute
LOAD_FACTOR=4                       # load1 > çekirdek*LOAD_FACTOR = yük uyarısı
BAN_TIMEOUT=86400                   # otomatik ban süresi (sn) = 24 saat
LOAD_ALERT_COOLDOWN=900             # yük uyarısı en fazla 15 dk'da bir

CPU_THRESHOLD=90                    # anlık CPU kullanımı % >= bu → "CPU tavan" uyarısı
CPU_ALERT_COOLDOWN=900             # CPU uyarısı en fazla 15 dk'da bir

HTTP_CHECK=1                        # 1=site sağlık probu açık (502/503/504 yakala)
HTTP_CHECK_HOST="randevumcepte.com.tr"   # prob için Host başlığı (gerçek bir vhost)
HTTP_ALERT_COOLDOWN=600            # 502 uyarısı en fazla 10 dk'da bir

WEB_PORTS_REGEX=':(80|443)$'        # inbound flood sayılan yerel portlar

STATE_DIR="/var/lib/guvenlik-duvari"
AUTH_LOG="/var/log/auth.log"
# ----------------------------------------------------------------------------

mkdir -p "$STATE_DIR" 2>/dev/null
CORES="$(nproc 2>/dev/null || echo 2)"

log() { logger -t guvenlik-watchdog "$*" 2>/dev/null; }

# Gerekli araçlar
command -v ipset >/dev/null 2>&1    || { log "ipset yok — cikildi"; echo "ipset kurulu degil"; exit 1; }
command -v iptables >/dev/null 2>&1 || { log "iptables yok — cikildi"; exit 1; }
command -v ss >/dev/null 2>&1       || { log "ss yok — cikildi"; exit 1; }

# --- .env'den DB bilgisi (ilk eşleşen key — dosyada 2. bağlantı da olabilir) ---
env_get() { grep -m1 -E "^$1=" "$ENV_FILE" 2>/dev/null | cut -d= -f2- | sed 's/^"//;s/"$//;s/[[:space:]]*$//;s/\r$//'; }
DB_HOST="$(env_get DB_HOST)";     DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(env_get DB_PORT)";     DB_PORT="${DB_PORT:-3306}"
DB_NAME="$(env_get DB_DATABASE)"
DB_USER="$(env_get DB_USERNAME)"
DB_PASS="$(env_get DB_PASSWORD)"

HAVE_DB=1
[ -z "$DB_NAME" ] || [ -z "$DB_USER" ] && HAVE_DB=0
command -v mysql >/dev/null 2>&1 || HAVE_DB=0

mysql_exec() {  # STDIN'den SQL çalıştırır (sessiz)
    [ "$HAVE_DB" -eq 1 ] || return 0
    MYSQL_PWD="$DB_PASS" mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" -N -B 2>/dev/null
}
mysql_query() { [ "$HAVE_DB" -eq 1 ] || return 0; MYSQL_PWD="$DB_PASS" mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" -N -B -e "$1" 2>/dev/null; }

sql_escape() { printf '%s' "$1" | sed "s/'/''/g"; }

db_olay() {  # tur ip deger esik aksiyon detay bildirildi
    [ "$HAVE_DB" -eq 1 ] || return 0
    local tur="$1" ip="$2" deger="$3" esik="$4" aksiyon="$5" detay="$6" bildirildi="$7"
    local ipsql="NULL"; [ -n "$ip" ] && ipsql="'$(sql_escape "$ip")'"
    local dgsql="NULL"; [ -n "$deger" ] && dgsql="$deger"
    local essql="NULL"; [ -n "$esik" ] && essql="$esik"
    printf "INSERT INTO guvenlik_olaylari (tur,ip,deger,esik,aksiyon,detay,bildirildi,created_at) VALUES ('%s',%s,%s,%s,'%s','%s',%s,NOW());" \
        "$tur" "$ipsql" "$dgsql" "$essql" "$aksiyon" "$(sql_escape "$detay")" "$bildirildi" | mysql_exec
}

# --- ipset/iptables altyapısı ---
ipset create "$IPSET_NAME" hash:ip timeout "$BAN_TIMEOUT" -exist 2>/dev/null
ipset create "$IPSET_PERM" hash:ip -exist 2>/dev/null
for SET in "$IPSET_NAME" "$IPSET_PERM"; do
    iptables -C INPUT -m set --match-set "$SET" src -j DROP 2>/dev/null || \
        iptables -I INPUT 1 -m set --match-set "$SET" src -j DROP 2>/dev/null
done

# --- Whitelist: statik (localhost + sunucunun kendi IP'leri + özel ağlar) + DB ---
declare -A WL
for ip in 127.0.0.1 ::1; do WL["$ip"]=1; done
for ip in $(hostname -I 2>/dev/null); do WL["$ip"]=1; done
is_private() { case "$1" in 10.*|127.*|192.168.*|172.1[6-9].*|172.2[0-9].*|172.3[0-1].*|169.254.*) return 0;; *) return 1;; esac; }

WL_FROM_DB="$(mysql_query "SELECT ip FROM guvenlik_ip_kurallari WHERE tip='whitelist';")"
while IFS= read -r ip; do [ -n "$ip" ] && WL["$ip"]=1; done <<< "$WL_FROM_DB"

# Whitelist'teki IP'leri ban setlerinden çıkar (panelden "engeli kaldır" => en geç 1 dk)
for ip in "${!WL[@]}"; do
    ipset del "$IPSET_NAME" "$ip" 2>/dev/null
    ipset del "$IPSET_PERM" "$ip" 2>/dev/null
done

# --- Blacklist (panelden kalıcı engel) uygula ---
BL_FROM_DB="$(mysql_query "SELECT ip FROM guvenlik_ip_kurallari WHERE tip='blacklist';")"
while IFS= read -r ip; do
    [ -z "$ip" ] && continue
    [ -n "${WL[$ip]:-}" ] && continue
    if ! ipset test "$IPSET_PERM" "$ip" 2>/dev/null; then
        ipset add "$IPSET_PERM" "$ip" -exist 2>/dev/null
        db_olay "flood" "$ip" "" "" "engellendi" "Panel blacklist — kalıcı engel" 1
        log "blacklist uygulandi: $ip"
    fi
done <<< "$BL_FROM_DB"

# ============================================================================
#  TESPİT
# ============================================================================
ALERT_LINES=()   # alarm mesajı için yeni engellenen IP satırları

engelle() {  # ip tur deger esik detay
    local ip="$1" tur="$2" deger="$3" esik="$4" detay="$5"
    [ -z "$ip" ] && return
    [ -n "${WL[$ip]:-}" ] && return
    is_private "$ip" && return
    # zaten engelli mi?
    if ipset test "$IPSET_NAME" "$ip" 2>/dev/null || ipset test "$IPSET_PERM" "$ip" 2>/dev/null; then
        return
    fi
    ipset add "$IPSET_NAME" "$ip" timeout "$BAN_TIMEOUT" -exist 2>/dev/null
    db_olay "$tur" "$ip" "$deger" "$esik" "engellendi" "$detay" 0
    log "ENGELLENDI $tur $ip ($detay)"
    case "$tur" in
        flood)     ALERT_LINES+=("• ${ip} → ${deger} bağlantı (flood)");;
        ssh_brute) ALERT_LINES+=("• ${ip} → ${deger} başarısız SSH");;
        *)         ALERT_LINES+=("• ${ip} → ${detay}");;
    esac
}

# --- 1) HTTP FLOOD ---
FLOOD="$(ss -tnH state established 2>/dev/null | awk -v re="$WEB_PORTS_REGEX" '
    $3 ~ re {
        peer=$4;
        sub(/:[0-9]+$/,"",peer);
        gsub(/[][]/,"",peer);
        sub(/^::ffff:/,"",peer);
        if (peer != "") print peer;
    }' | sort | uniq -c | sort -rn)"

while IFS= read -r line; do
    [ -z "$line" ] && continue
    cnt="$(awk '{print $1}' <<< "$line")"
    ip="$(awk '{print $2}' <<< "$line")"
    [ -z "$ip" ] && continue
    if [ "$cnt" -ge "$CONN_THRESHOLD" ]; then
        engelle "$ip" "flood" "$cnt" "$CONN_THRESHOLD" "HTTP flood: $cnt eşzamanlı bağlantı (80/443)"
    fi
done <<< "$FLOOD"

# --- 2) SSH BRUTE-FORCE (auth.log'da son turdan bu yana yeni satırlar) ---
if [ -r "$AUTH_LOG" ]; then
    OFFSET_FILE="$STATE_DIR/auth.offset"
    cursize="$(stat -c%s "$AUTH_LOG" 2>/dev/null || echo 0)"
    prevoff="$(cat "$OFFSET_FILE" 2>/dev/null || echo 0)"
    [ "$cursize" -lt "$prevoff" ] && prevoff=0    # logrotate olduysa baştan
    echo "$cursize" > "$OFFSET_FILE"
    NEW="$(tail -c +$((prevoff + 1)) "$AUTH_LOG" 2>/dev/null | grep -a 'Failed password' | grep -aoE 'from [0-9]+\.[0-9]+\.[0-9]+\.[0-9]+' | awk '{print $2}' | sort | uniq -c | sort -rn)"
    while IFS= read -r line; do
        [ -z "$line" ] && continue
        cnt="$(awk '{print $1}' <<< "$line")"
        ip="$(awk '{print $2}' <<< "$line")"
        [ -z "$ip" ] && continue
        if [ "$cnt" -ge "$SSH_THRESHOLD" ]; then
            engelle "$ip" "ssh_brute" "$cnt" "$SSH_THRESHOLD" "SSH brute: 1 dk içinde $cnt başarısız giriş"
        fi
    done <<< "$NEW"
fi

# --- 3) YÜKSEK YÜK ---
LOAD1="$(awk '{print $1}' /proc/loadavg 2>/dev/null)"
LOAD_LIMIT=$((CORES * LOAD_FACTOR))
if [ -n "$LOAD1" ] && awk -v l="$LOAD1" -v m="$LOAD_LIMIT" 'BEGIN{exit !(l>m)}'; then
    LAST_LOAD_FILE="$STATE_DIR/load.last"
    now="$(date +%s)"
    last="$(cat "$LAST_LOAD_FILE" 2>/dev/null || echo 0)"
    if [ $((now - last)) -ge "$LOAD_ALERT_COOLDOWN" ]; then
        echo "$now" > "$LAST_LOAD_FILE"
        deger="$(awk -v l="$LOAD1" 'BEGIN{printf "%d", l*100}')"
        db_olay "load_yuksek" "" "$deger" "$((LOAD_LIMIT * 100))" "uyari" "load1=$LOAD1 (çekirdek=$CORES limit=$LOAD_LIMIT)" 0
        ALERT_LINES+=("⚠️ Yüksek yük: load ${LOAD1} (limit ${LOAD_LIMIT})")
        log "YUK UYARISI load1=$LOAD1"
    fi
fi

# --- 4) CPU TAVAN (gerçek CPU% — /proc/stat, 1 sn örnekleme) ---
cpu_kullanim() {
    local a u1 n1 s1 i1 w1 x1 y1 z1
    read -r a u1 n1 s1 i1 w1 x1 y1 z1 _ < /proc/stat
    local idle1=$((i1 + w1)) tot1=$((u1 + n1 + s1 + i1 + w1 + x1 + y1 + z1))
    sleep 1
    read -r a u2 n2 s2 i2 w2 x2 y2 z2 _ < /proc/stat
    local idle2=$((i2 + w2)) tot2=$((u2 + n2 + s2 + i2 + w2 + x2 + y2 + z2))
    local dt=$((tot2 - tot1)) di=$((idle2 - idle1))
    [ "$dt" -le 0 ] && { echo 0; return; }
    echo $(( (100 * (dt - di)) / dt ))
}
CPU="$(cpu_kullanim)"
if [ "${CPU:-0}" -ge "$CPU_THRESHOLD" ]; then
    F="$STATE_DIR/cpu.last"; now="$(date +%s)"; last="$(cat "$F" 2>/dev/null || echo 0)"
    if [ $((now - last)) -ge "$CPU_ALERT_COOLDOWN" ]; then
        echo "$now" > "$F"
        # en çok CPU yiyen 3 process — teşhis için
        TOPCPU="$(ps -eo pcpu,comm --sort=-pcpu 2>/dev/null | awk 'NR>1 && NR<=4{printf "%s(%s%%) ", $2, $1}')"
        db_olay "cpu_yuksek" "" "$CPU" "$CPU_THRESHOLD" "uyari" "CPU %${CPU} · en çok: ${TOPCPU}" 0
        ALERT_LINES+=("🔥 CPU tavan: %${CPU} (limit %${CPU_THRESHOLD}) · ${TOPCPU}")
        log "CPU UYARISI %${CPU} top=${TOPCPU}"
    fi
fi

# --- 5) 502/503/504 BAD GATEWAY (siteyi canlı prob'la) ---
if [ "$HTTP_CHECK" -eq 1 ] && command -v curl >/dev/null 2>&1; then
    http_prob() { curl -o /dev/null -sk -w '%{http_code}' --max-time 8 -H "Host: $HTTP_CHECK_HOST" "https://127.0.0.1/" 2>/dev/null; }
    CODE="$(http_prob)"
    case "$CODE" in
        502|503|504)
            sleep 2; CODE2="$(http_prob)"    # geçici pik değil, sürekli mi? teyit et
            case "$CODE2" in
                502|503|504)
                    F="$STATE_DIR/http502.last"; now="$(date +%s)"; last="$(cat "$F" 2>/dev/null || echo 0)"
                    if [ $((now - last)) -ge "$HTTP_ALERT_COOLDOWN" ]; then
                        echo "$now" > "$F"
                        db_olay "http_502" "" "$CODE2" "" "uyari" "Site HTTP $CODE2 döndürüyor (php-fpm/upstream sorunu olabilir)" 0
                        ALERT_LINES+=("🚨 Site HATASI: HTTP $CODE2 (Bad Gateway) — ${HTTP_CHECK_HOST}")
                        log "HTTP $CODE2 UYARISI"
                    fi
                    ;;
            esac
            ;;
    esac
fi

# ============================================================================
#  ALARM (yeni engel / yük uyarısı varsa) — önce engelledik, sonra haber ver
# ============================================================================
if [ "${#ALERT_LINES[@]}" -gt 0 ]; then
    engelSayisi=0
    for l in "${ALERT_LINES[@]}"; do [[ "$l" == "•"* ]] && engelSayisi=$((engelSayisi + 1)); done
    BASLIK="🛡️ GÜVENLİK ALARMI"
    [ "$engelSayisi" -gt 0 ] && BASLIK="$BASLIK — ⛔ ${engelSayisi} IP engellendi"
    MSG="$BASLIK"$'\n'
    for l in "${ALERT_LINES[@]}"; do MSG="$MSG$l"$'\n'; done
    MSG="${MSG}Load: ${LOAD1:-?} · $(date '+%d.%m.%Y %H:%M')"$'\n'
    MSG="${MSG}Panel: /sistemyonetim/v2/guvenlik-duvari"

    if [ -x "$PHP_BIN" ] && [ -f "$APP_DIR/artisan" ]; then
        "$PHP_BIN" "$APP_DIR/artisan" guvenlik:bildir "$MSG" >/dev/null 2>&1 \
            && mysql_query "UPDATE guvenlik_olaylari SET bildirildi=1 WHERE bildirildi=0 AND created_at >= NOW() - INTERVAL 2 MINUTE;" >/dev/null 2>&1
    fi
    log "ALARM gonderildi ($engelSayisi engel)"
fi

exit 0
