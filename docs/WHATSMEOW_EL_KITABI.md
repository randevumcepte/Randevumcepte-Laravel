# WhatsMeow Bridge — El Kitabı

**Kime yönelik:** sunucu yönetimi ile ilgilenen kişi (Ferdi + destek ekibi)
**Ne zaman kullanılır:** bridge kopunca, sunucu restart olunca, salon bağlantı sorunu bildirince, panelde "disconnected" göründüğünde

---

## 0. Hızlı komutlar (kopyala-yapıştır cheat sheet)

```bash
# Yerine göre bu değişkenleri set et
BRIDGE=/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge
TOKEN="$(grep SHARED_SECRET $BRIDGE/.env | cut -d= -f2)"

# Bridge durumu
pm2 status | grep whatsmeow
curl -s http://127.0.0.1:3002/health

# Belirli bir salonun bridge durumu
SALON=397
curl -s -H "X-Service-Token: $TOKEN" http://127.0.0.1:3002/session/$SALON/status

# Belirli bir salonun log'u (son 100 satır)
pm2 logs randevumcepte-whatsmeow --lines 500 --nostream | grep "\[$SALON\]" | tail -30

# Bridge restart (sessions korunur, cache disk'te)
pm2 restart randevumcepte-whatsmeow

# Tüm salonları elle yeniden başlat (auto-restore çalışmıyorsa)
for f in $BRIDGE/data/salon_*.db; do
  S=$(basename "$f" .db | sed 's/salon_//')
  curl -s -X POST -H "X-Service-Token: $TOKEN" http://127.0.0.1:3002/session/$S/start > /dev/null
  sleep 0.3
done
```

---

## 1. Sunucu restart oldu / kapandı

**Belirti:** panelde tüm salonlar disconnected görünüyor, hiçbirinden mesaj gitmiyor

> **NORMAL beklenen davranış:** sunucu tekrar açıldıktan sonra pm2 kendini toparlar, whatsmeow bridge başlar ve bridge boot'ta `data/` klasöründeki tüm salon dosyalarını **otomatik yükler**. Salonlar QR taratmadan connected olur.
>
> Boot log satırı: `[boot] auto-restore tamamlandi: N oturum yuklendi`

Otomatik olmadıysa aşağıdaki adımları takip et.

### Adım 1a: pm2 ayakta mı?
```bash
pm2 list
```
`randevumcepte-whatsmeow` satırında **status=online** görmelisin.

- **Görüyorsan** → 1b'ye geç
- **Görmüyorsan** → pm2 daemon başlatılmamış:
  ```bash
  pm2 resurrect              # kayıtlı process listesini geri yükle
  # veya elle başlat:
  cd $BRIDGE && pm2 start ./start.sh --name randevumcepte-whatsmeow
  pm2 save
  ```

### Adım 1b: Bridge cevap veriyor mu?
```bash
curl -s http://127.0.0.1:3002/health
```
`{"sessions":N,"status":"ok"}` görmelisin.

- **N > 0** → session'lar yüklü, panel refresh'i beklemek yeterli. Adım 3'e (hâlâ sorun varsa).
- **N = 0** → session'lar yüklenmemiş. **Adım 1c'ye geç.**
- **Bağlantı reddedildi** → bridge çalışmıyor: `pm2 restart randevumcepte-whatsmeow` sonra tekrar dene.

### Adım 1c: Auto-restore çalıştı mı?
Boot log satırını ara:
```bash
pm2 logs randevumcepte-whatsmeow --lines 500 --nostream | grep -iE "boot|auto-restore" | head
```

- **`[boot] auto-restore tamamlandi: N oturum yuklendi` var + N>0** → sistem sağlam, salonların panel refresh'i beklemek yeterli. Sorun varsa Adım 2'ye geç
- **`[boot] auto-restore tamamlandi: 0 oturum yuklendi`** → data dizini boş veya path yanlış:
  ```bash
  ls -la $BRIDGE/data/ | head -20
  ```
  `salon_XXX.db` dosyaları görüyorsan path/permission sorunu → Adım 1d'ye
- **`[boot] data dizini okunamadi` VEYA hiç boot satırı yok** → auto-restore hiç çalışmamış → Adım 1d'ye

### Adım 1d: Salonları manuel restore et
Auto-restore çalışmadıysa veya bekleyemiyorsan hızlı fix:

```bash
BRIDGE=/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge
TOKEN="$(grep SHARED_SECRET $BRIDGE/.env | cut -d= -f2)"

for f in $BRIDGE/data/salon_*.db; do
  S=$(basename "$f" .db | sed 's/salon_//')
  echo "starting $S..."
  curl -s -X POST -H "X-Service-Token: $TOKEN" http://127.0.0.1:3002/session/$S/start > /dev/null
  sleep 0.3
done
```

30-60 saniye içinde her salon reconnect eder — **QR tarama gerekmez**, cached credential'lar SQLite dosyasında.

### Adım 1e: Doğrula
```bash
sleep 30
pm2 logs randevumcepte-whatsmeow --lines 200 --nostream | grep "connected (phone=" | tail -20
mysql randevumcepteweb -e "SELECT COUNT(*) as bagli FROM salonlar WHERE whatsapp_bridge_tipi='whatsmeow' AND whatsapp_durum='connected';"
```

### Adım 1f: Boot sonrası otomatik başlaması için — pm2 startup kontrolü

Bir sonraki reboot'ta manuel adım gerekmesin diye pm2'nin systemd üzerinde kayıtlı olduğunu doğrula:
```bash
systemctl status pm2-root
```
**active (running)** göremiyorsan:
```bash
pm2 startup
# çıkan `sudo env PATH=...` satırını kopyala ve çalıştır
pm2 save
```

Bu adım bir kere yapılırsa reboot sonrası pm2 kendini toparlar, bridge otomatik başlar, auto-restore devreye girer.

---

## 2. Tek bir salon disconnected görünüyor (bridge sağlam)

**Belirti:** panelde salon 397 "disconnected", diğerleri "connected"

### Adım 2a: Bridge'in gördüğü durum
```bash
SALON=397
curl -s -H "X-Service-Token: $TOKEN" http://127.0.0.1:3002/session/$SALON/status
```

- **`{"connected":true,"phone":"90..."}`** → bridge bağlı, sorun Laravel tarafında.
  ```bash
  mysql randevumcepteweb -e "UPDATE salonlar SET whatsapp_durum='connected' WHERE id=$SALON;"
  ```
- **`{"connected":false,"status":"no-session"}`** → session yok. Adım 2b'ye.
- **`{"status":"qr-pending"}`** → yeniden pair'e ihtiyaç var. Adım 2c'ye.
- **`{"status":"disconnected"}`** → bridge oturumu kopmuş. Adım 2b'ye.

### Adım 2b: Restart session
```bash
curl -X POST -H "X-Service-Token: $TOKEN" http://127.0.0.1:3002/session/$SALON/start
```
Cached credentials'la reconnect eder — QR gerekmez.

### Adım 2c: Log'da neden koptu bak
```bash
pm2 logs randevumcepte-whatsmeow --lines 500 --nostream | grep "\[$SALON\]" | tail -30
```

Aradığın satırlar:
- `logged out by user/server` → salon telefondan cihazı sildi, YA WA server'ı otomatik kesti
- `disconnected` sonrası tekrar `QR ready` → session tamamen bitti, yeniden pair gerek
- `Bad MAC Error` → Signal state bozuk, aşağıya git

---

## 3. Salon tekrar QR taratması gerekiyor (pair olmuyor / sürekli kopuyor)

**Belirti:** salon panelde "Bağlan"a bastığında QR çıkıyor, salon telefondan tarıyor, ama connected olmuyor VEYA 1-2 dakika sonra düşüyor.

### Adım 3a: Session'ı temizle ve baştan başla
```bash
SALON=397
BRIDGE=/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge
TOKEN="$(grep SHARED_SECRET $BRIDGE/.env | cut -d= -f2)"

# Bridge'ten logout
curl -X POST -H "X-Service-Token: $TOKEN" http://127.0.0.1:3002/session/$SALON/logout

# SQLite dosyasını sil — clean slate
rm -f $BRIDGE/data/salon_$SALON.db

# DB'de eski durumu temizle
mysql randevumcepteweb -e "UPDATE salonlar SET whatsapp_aktif=0, whatsapp_durum=NULL, whatsapp_numara=NULL, whatsapp_baglanti_tarihi=NULL, whatsapp_warmup_baslangic=NULL WHERE id=$SALON;"
```

Şimdi salon paneline gir: WhatsApp Yeni Test → Bağlan / QR Üret → yeni QR ile tarat.

### Adım 3b: Salon telefonundan kontrol
Bağlanır bağlanmaz salon telefonundan çıkarılıyorsa sebep telefon tarafında olabilir:
- **WA Business → Bağlı Cihazlar** listesine bak
- "Chrome (Windows)" görünüyor mu?
- Salon farkında olmadan silmiş olabilir → "yeni cihaz bağlandı" bildirimini görmezden gelmelerini söyle

### Adım 3c: Aynı numara başka yerde bağlı mı?
Salon numarası **hem Baileys hem whatsmeow bridge'inde** bağlıysa çakışıyor olabilir:

```bash
# Baileys'te aynı salon aktif mi? (port 3001)
curl -s -H "X-Service-Token: $TOKEN" http://127.0.0.1:3001/session/$SALON/status 2>&1 | head

# Aktifse Baileys'ten logout
curl -X POST -H "X-Service-Token: $TOKEN" http://127.0.0.1:3001/session/$SALON/logout
```

Aynı zamanda salonlar tablosunda `whatsapp_bridge_tipi='whatsmeow'` olmalı ki mesajlar 3002'ye gitsin:
```bash
mysql randevumcepteweb -e "SELECT id, whatsapp_bridge_tipi FROM salonlar WHERE id=$SALON;"
```

---

## 4. Mesajlar "SMS'e düştü" olarak damgalanıyor (aslında WA'dan gitti)

**Belirti:** panelde WA mesajları `stuck-kurtar: 10dk kuyrukta kaldı, SMS'e düşürüldü` etiketiyle görünüyor ama bridge log'unda mesaj başarıyla gönderilmiş

### Adım 4a: Webhook URL doğru mu?
```bash
grep WEBHOOK_URL $BRIDGE/.env
```
Sonuç: `WEBHOOK_URL=https://app.randevumcepte.com.tr/webhook/whatsapp`

Eğer `/api/wa-webhook` yazıyorsa yanlış → düzelt, sonra `pm2 restart randevumcepte-whatsmeow`

### Adım 4b: Webhook Laravel'e ulaşıyor mu?
```bash
pm2 logs randevumcepte-whatsmeow --lines 200 --nostream | grep -i webhook | tail -10
```
Beklediğin: `[webhook] message.sent status=200`

- **status=404** → URL yanlış
- **status=401** → secret uyuşmuyor (`WHATSAPP_WEBHOOK_SECRET` vs `WEBHOOK_SECRET`)
- **hiç satır yok** → bridge webhook firing yapmıyor, kod problemi

### Adım 4c: stuck-kurtar cron zamanlaması
```bash
crontab -l | grep stuck-kurtar
```
Her 3 dakikada çalışıyor. Bridge webhook'u 5 saniye içinde firing yapıyorsa sorun yok. Firing gecikiyorsa stuck-kurtar önce yakalıyor.

---

## 5. WhatsApp'tan mesaj göndermesine rağmen alıcıya varmıyor

**Belirti:** salon "Gönderildi" görüyor ama alıcıda ⏱ (saat ikonu, "bekleniyor") kalıyor

Bu WA server veya alıcı tarafı sorunu, bridge tarafı değil. Klasik nedenler:

- Salon'un WA Business hesabı **soft-ban** almış (463 hatası)
- Alıcı numara WA'da kayıtlı değil
- Alıcı telefon offline / arka planda kapalı

### Kontrol:
```bash
# Salonun 463 geçmişi var mı?
mysql randevumcepteweb -e "SELECT COUNT(*) FROM whatsapp_gonderim_loglari WHERE salon_id=$SALON AND (hata LIKE '%463%' OR hata LIKE '%error in ack%');"
```

> 0 dönerse numara Meta anti-abuse'e takılmış. 2-4 hafta dinlendirmeli, aksi halde salonun yeni numara kullanması gerekir.

---

## 6. Yeni salon nasıl eklenir?

1. Salon paneli: **WhatsApp Yeni Test** menüsü (uyelik_turu=3 salonlarda görünür)
2. **Bağlan / QR Üret** butonuna bas
3. Ekrandaki QR salonun telefonundaki **WA Business → Bağlı Cihazlar → Cihaz Bağla**'dan tarat
4. Bridge log'unda `[SALON_ID] connected (phone=90...)` görürsen tamam
5. DB otomatik: `whatsapp_aktif=1`, `whatsapp_durum=connected`, `whatsapp_bridge_tipi=whatsmeow` set edilir

Sorun olursa: **Adım 3'e git** (session temizle + baştan başla).

---

## 7. Sistem sağlık kontrolü (haftalık rutin)

```bash
# Bridge memory usage
pm2 status | grep whatsmeow

# Kaç aktif salon
mysql randevumcepteweb -e "SELECT whatsapp_bridge_tipi, COUNT(*) FROM salonlar WHERE whatsapp_aktif=1 AND whatsapp_durum='connected' GROUP BY whatsapp_bridge_tipi;"

# Son 24 saatte gönderilen mesaj sayısı ve durum dağılımı
mysql randevumcepteweb -e "
SELECT
  CASE durum
    WHEN 0 THEN 'kuyrukta' WHEN 1 THEN 'gönderildi'
    WHEN 2 THEN 'başarısız' WHEN 3 THEN 'sms_fallback'
    ELSE 'diğer' END as durum_adi,
  COUNT(*) adet
FROM whatsapp_gonderim_loglari
WHERE created_at > NOW() - INTERVAL 1 DAY
GROUP BY durum;"

# En çok hata alan 5 salon
mysql randevumcepteweb -e "
SELECT salon_id, COUNT(*) fail_adet, MAX(created_at) son_hata
FROM whatsapp_gonderim_loglari
WHERE durum=2 AND created_at > NOW() - INTERVAL 7 DAY
GROUP BY salon_id
ORDER BY fail_adet DESC LIMIT 5;"
```

Anormal artış varsa Adım 5'e git.

---

## 8. Acil durum — bridge tamamen bozuldu, hiçbir salon çalışmıyor

**Son çare** — tüm salonları Baileys'e geri döndür:

```bash
mysql randevumcepteweb -e "UPDATE salonlar SET whatsapp_bridge_tipi='baileys' WHERE whatsapp_bridge_tipi='whatsmeow';"
pm2 stop randevumcepte-whatsmeow
```

Salonlar Baileys'ten mesaj göndermeye devam eder (varsayarak Baileys sağlam). Whatsmeow'u tamir et, sonra:

```bash
mysql randevumcepteweb -e "UPDATE salonlar SET whatsapp_bridge_tipi='whatsmeow' WHERE id IN (...);"
pm2 start randevumcepte-whatsmeow
```

---

## Ek: Dosya/klasör konumları

| Ne | Nerede |
|---|---|
| Bridge binary | `/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge/whatsmeow-bridge` |
| Bridge kaynak | `/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge/main.go` |
| Bridge .env | `/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge/.env` |
| Salon SQLite dosyaları | `/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge/data/salon_*.db` |
| Bridge start script | `/var/www/www-root/data/www/randevumcepte/whatsmeow-bridge/start.sh` |
| pm2 process adı | `randevumcepte-whatsmeow` (id=2) |
| Bridge log (out) | `/root/.pm2/logs/randevumcepte-whatsmeow-out.log` |
| Bridge log (err) | `/root/.pm2/logs/randevumcepte-whatsmeow-error.log` |
| Laravel log | `/var/www/www-root/data/www/randevumcepte/storage/logs/laravel.log` |
| Baileys bridge | port 3001 (aynı klasör mantığı, `whatsapp-service/`) |
| whatsmeow bridge | port 3002 |
