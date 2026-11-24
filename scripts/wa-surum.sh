#!/bin/bash
# WhatsApp koprusu: "tum oturumlar kopuk + QR gelmiyor" teshisi ve surum duzeltmesi. Sunucuda root ile:
#   bash scripts/wa-surum.sh             -> SADECE TESHIS (hicbir seyi degistirmez)
#   bash scripts/wa-surum.sh uygula      -> acilista guncel WhatsApp surumunu kullan (405 client outdated)
#   bash scripts/wa-surum.sh kutuphane   -> ustteki yetmezse: whatsmeow kutuphanesini de guncelle
# Derleme ayri klasorde yapilir; basarisizsa canliya dokunulmaz. data/ (oturumlar) ASLA degistirilmez.
B=${WA_DIR:-/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge}
P=${WA_PM2:-randevumcepte-whatsmeow}
MOD=$1
export PATH=$PATH:/usr/local/go/bin:/root/go/bin:/opt/go/bin
export GOTOOLCHAIN=auto CGO_ENABLED=1
cd "$B" || { echo "HATA: $B yok"; exit 1; }

son_log(){ pm2 logs "$P" --lines "$1" --nostream 2>&1; }

if [ -z "$MOD" ]; then
    echo "== pm2 =="; pm2 describe "$P" 2>&1 | grep -E 'status|restarts|uptime|script path|log path|not exist|doesn'
    pm2 ls 2>/dev/null | grep -iE 'whats|name'
    echo "== disk =="; df -h "$B" /tmp | tail -2; df -i "$B" | tail -1
    echo "== oturum dosyalari =="; ls data/salon_*.db 2>/dev/null | wc -l
    echo "bos (0 bayt): $(find data -name 'salon_*.db' -size 0 2>/dev/null | wc -l)"
    ls -ld data; ls -la whatsmeow-bridge
    PID=$(pm2 pid "$P" 2>/dev/null | tail -1)
    [ -n "$PID" ] && echo "silinmis-acik dosya: $(ls -l /proc/$PID/fd 2>/dev/null | grep -c deleted)"
    echo "== surum =="; go version 2>&1
    go version -m whatsmeow-bridge 2>/dev/null | grep -E 'go1|whatsmeow'
    grep -E '^go |whatsmeow|toolchain' go.mod
    echo "== WhatsApp erisimi =="
    for u in https://web.whatsapp.com/ https://web.whatsapp.com/ws/chat; do
        echo "$u -> $(curl -s -o /dev/null -m 10 -w '%{http_code} %{time_total}s' $u)"; done
    echo "== log ozeti (son 5000 satir) =="
    L=$(son_log 5000)
    for k in 'outdated' 'logged out' 'stream replaced' 'readonly' 'database is locked' 'disk' 'no such host' 'timeout' 'too many open files' 'QR ready' '] connected'; do
        printf '%-22s %s\n' "$k" "$(echo "$L" | grep -ciF "$k")"; done
    echo "== son hatalar =="
    echo "$L" | grep -iE 'outdated|error|failed|panic|logged out|err:|hatasi' | cut -c1-230 | tail -25
    exit 0
fi

[ "$MOD" = "uygula" ] || [ "$MOD" = "kutuphane" ] || { echo "Gecersiz mod: $MOD"; exit 1; }
command -v go >/dev/null || { echo "HATA: go bulunamadi"; exit 1; }

D=$(mktemp -d /root/wa-derleme-XXXXXX)
cp main.go go.mod go.sum "$D"/ || exit 1
cd "$D" || exit 1

if ! grep -q 'surumuTazele' main.go; then
    S='store.DeviceProps.RequireFullSync = proto.Bool(false)'
    [ "$(grep -cF "$S" main.go)" = 1 ] || { echo "HATA: main.go beklenen satiri icermiyor, degistirilmedi"; exit 1; }
    cat > ek.txt <<'GO'

	// WhatsApp eski istemci surumunu 405 (client outdated) ile reddeder: guncel
	// surumu WhatsApp'tan al (acilista + 6 saatte bir, yeni baglantilar icin).
	surumuTazele()
	go func() {
		for {
			time.Sleep(6 * time.Hour)
			surumuTazele()
		}
	}()
GO
    sed -i '/store.DeviceProps.RequireFullSync = proto.Bool(false)/r ek.txt' main.go
    cat >> main.go <<'GO'

func surumuTazele() {
	ctx, cancel := context.WithTimeout(context.Background(), 20*time.Second)
	defer cancel()
	v, err := whatsmeow.GetLatestVersion(ctx, &http.Client{Timeout: 20 * time.Second})
	if err != nil || v == nil {
		log.Printf("[surum] guncel WhatsApp surumu alinamadi: %v", err)
		return
	}
	store.SetWAVersion(*v)
	log.Printf("[surum] WhatsApp surumu: %v", *v)
}
GO
fi

if [ "$MOD" = "kutuphane" ]; then
    go get go.mau.fi/whatsmeow@latest && go mod tidy || { echo "HATA: kutuphane guncellenemedi, canliya dokunulmadi ($D)"; exit 1; }
fi

go build -o whatsmeow-bridge . || { echo "HATA: derlenemedi, canliya dokunulmadi ($D)"; exit 1; }
echo "1) derlendi: $(go version -m whatsmeow-bridge | grep -E 'whatsmeow.*v0' | head -1)"

Y=/root/wa-yedek-$(date +%F-%H%M%S)
mkdir -p "$Y" && cp -a "$B/main.go" "$B/go.mod" "$B/go.sum" "$B/whatsmeow-bridge" "$B/data" "$Y"/ \
    || { echo "HATA: yedek alinamadi, canliya dokunulmadi"; exit 1; }
echo "2) yedek: $Y"

# once yeni ad + mv: calisan binary'nin uzerine yazilamaz (text file busy)
cp whatsmeow-bridge "$B/whatsmeow-bridge.yeni" && chmod 755 "$B/whatsmeow-bridge.yeni" \
    && cp main.go go.mod go.sum "$B"/ && mv -f "$B/whatsmeow-bridge.yeni" "$B/whatsmeow-bridge" \
    || { echo "HATA: dosyalar yerlestirilemedi; yedek: $Y"; exit 1; }
son_log 20000 > "$Y/eski.log"   # teshis icin sakla; asagidaki sayim yalniz yeni acilisi gorsun
pm2 flush "$P" >/dev/null 2>&1
pm2 restart "$P" && sleep 45
echo "3) sonuc:"
L=$(son_log 3000)
echo "$L" | grep -E '\[surum\]|auto-restore' | tail -5
echo "   bagli: $(echo "$L" | grep -cF '] connected (')   405: $(echo "$L" | grep -ci outdated)   cikis: $(echo "$L" | grep -c 'logged out by')"
echo "Geri almak icin: cd $Y && cp main.go go.mod go.sum $B/ && cp whatsmeow-bridge $B/b.eski && mv -f $B/b.eski $B/whatsmeow-bridge && pm2 restart $P"
