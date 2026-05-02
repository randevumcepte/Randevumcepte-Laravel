# Asterisk Kurulum — AI Sesli Asistan Faz 2

Bu belge, mevcut Asterisk PBX'i AI sesli asistan sidecar'ı ile entegre etmek için
yapılması gereken **tüm konfigürasyonu** içerir. Yazılımcı arkadaşa bu dosyayı
vermek yeterli.

## 0. Önkoşullar

```bash
# Asterisk versiyonu kontrolü (16+ olmalı, 18+ ideal)
asterisk -V
# Asterisk 18.x veya üstü olmalı.
# 16'dan eski ise external_media kanalı yok, upgrade gerek.

# Asterisk hangi sunucuda çalışıyor?
# Sidecar (Node.js) ile aynı sunucu MI yoksa farklı sunucu MU?
# - Aynı sunucu  -> en basit, latency düşük (önerilir)
# - Farklı sunucu -> sidecar'a IP/port erişimi açılmalı
```

## 1. ARI etkinleştir — `/etc/asterisk/ari.conf`

ARI (Asterisk REST Interface), sidecar'ın Asterisk'i WebSocket üzerinden
kontrol etmesini sağlar.

```ini
[general]
enabled = yes
pretty = yes
allowed_origins = *

[randevu_ai]
type = user
read_only = no
password = DEGISTIRILECEK_GUCLU_PAROLA_BURAYA
password_format = plain
```

> `randevu_ai` — sidecar'ın bağlanacağı kullanıcı adı. Parolayı `openssl rand -hex 24`
> ile üret, sidecar `.env`'sinde `ASTERISK_ARI_PASS` olarak kullan.

## 2. HTTP server etkinleştir — `/etc/asterisk/http.conf`

ARI HTTP üstünden çalışır.

```ini
[general]
enabled = yes
bindaddr = 127.0.0.1     ; aynı sunucuda sidecar varsa localhost yeterli
                         ; farklı sunucuysa: 0.0.0.0 + firewall ile sidecar IP'sine kısıtla
bindport = 8088
prefix = asterisk
```

> Güvenlik: 0.0.0.0 dinliyorsa **mutlaka** firewall'da port 8088'i sadece
> sidecar IP'sine açın. Aksi halde internete açılır.

## 3. Dialplan — `/etc/asterisk/extensions.conf`

Mevcut IVR dialplan'ında, AI menüsüne yönlendirilecek bir extension ekle:

```ini
[from-pstn]
; ... mevcut IVR satırların ...

; ── AI Sesli Asistan ──
; Mevcut IVR menüsünde bir tuşa (örn. 9) basıldığında veya doğrudan
; bir extension olarak çağrıyı AI Stasis app'ine ver:
exten => 9,1,NoOp(AI sesli asistan baslatiliyor)
 same => n,Answer()
 same => n,Stasis(randevu_ai,${CALLERID(num)},${EXTEN})
 same => n,Hangup()

; Veya direkt yeni gelen tüm çağrıları AI'ya yönlendirmek istersen:
; exten => _X.,1,Answer()
;  same => n,Stasis(randevu_ai,${CALLERID(num)},${EXTEN})
;  same => n,Hangup()

; Test için — sadece test SIP extension'ı 9999'a:
exten => 9999,1,Answer()
 same => n,Stasis(randevu_ai,${CALLERID(num)},${EXTEN})
 same => n,Hangup()
```

> `randevu_ai` — sidecar'ın `ari-client` ile başlatacağı Stasis app adı (kod tarafında
> sabit). Parametreler: caller ID + arandığı extension. Sidecar bunlardan salonu
> ve müşteriyi tespit eder.

## 4. Codec — `slin16` öner

Asterisk → sidecar arası ses formatı `slin16` (PCM 16-bit signed linear, 16 kHz mono)
olmalı çünkü Whisper STT bu formatı doğal olarak istiyor. Çevrim Asterisk
tarafında otomatik yapılır.

`/etc/asterisk/asterisk.conf` veya kanal config'inde codec listesinde
`slin16` olduğundan emin ol. Çoğu kurulumda zaten var.

## 5. Reload + doğrulama

```bash
# Asterisk CLI'a bağlan
asterisk -rvvv

# Içerideyken:
http reload
ari reload
dialplan reload

# Doğrulamalar:
http show status              ; HTTP sunucusu calisir gorunuyor mu?
ari show status               ; ARI etkin mi?
ari show users                ; randevu_ai kullanicisi listede mi?
dialplan show 9@from-pstn     ; AI extension'i tanindi mi?
```

Beklenen çıktılar:

```
HTTP Server Status:
Server Enabled and Bound to 127.0.0.1:8088
ARI Status:
ARI is enabled and ready to accept connections.
```

## 6. Firewall / port

```bash
# Eğer sidecar farklı sunucudaysa:
ufw allow from <SIDECAR_IP> to any port 8088   # ARI HTTP
ufw allow from <SIDECAR_IP> to any port 10000:20000/udp  # RTP audio (external_media)
```

## 7. Hızlı test — ARI bağlantısı çalışıyor mu?

Sidecar'a geçmeden, curl ile ARI'ye ulaşılabildiğini doğrula:

```bash
curl -u randevu_ai:DEGISTIRILECEK_GUCLU_PAROLA_BURAYA \
  http://localhost:8088/asterisk/ari/asterisk/info | head -50
```

200 OK + JSON cevap geldiyse ARI hazır demek.

## 8. Test arama (sidecar bağlandıktan sonra)

```bash
# Asterisk CLI'da, manuel test çağrısı originate et:
asterisk -rx 'channel originate Local/9999@from-pstn application Stasis randevu_ai'

# Veya gerçek SIP telefondan 9999'u ara.
```

Sidecar log'unda Stasis app'e gelen çağrı görünecek, AI selamlama sesi başlayacak.

---

## Sidecar tarafına geçmek için yazılımcı arkadaştan istenenler

1. **Asterisk versiyonu** — `asterisk -V` çıktısı
2. **Asterisk hangi sunucuda?** Sidecar (Node.js) ile aynı sunucu mu?
3. **`/etc/asterisk/ari.conf` aktarılabilir mi?** (parola hariç)
4. **`/etc/asterisk/http.conf`** mevcut hali
5. **Mevcut `extensions.conf` IVR akışı** — AI menüsünü nereye eklemek istediğin
6. **Test için bir SIP extension** veya ucuz bir gelen DID

Bu bilgiler geldiğinde sidecar'a ARI client + RTP audio relay kodu yazılır
(Faz 2'nin sidecar tarafı). Mevcut sidecar pipeline (STT/LLM/TTS) hazır,
sadece ses I/O kısmı eklenecek.

## Sorun giderme

| Sorun | Çözüm |
|---|---|
| `ARI is not enabled` | `ari.conf`'ta `enabled = yes` ve `asterisk -rx 'ari reload'` |
| `401 Unauthorized` | `ari.conf`'taki kullanıcı adı/parola sidecar `.env`'siyle eşleşmiyor |
| Stasis app'e çağrı düşmüyor | `dialplan show <extension>` ile kontrol et, `Stasis()` satırı doğru mu |
| `slin16` codec hatası | Asterisk modüllerinde `codec_resample.so` yüklü mü? `module show like resample` |
| WebSocket bağlanmıyor | Firewall portu 8088 sidecar'a kapalı |
| Ses kesik kesik geliyor | RTP paket kaybı — `slin16` mu yoksa `ulaw` mı kullanılıyor kontrol et |

## Sonraki adım

Yukarıdakiler tamam olduğunda haber ver — sidecar'a ARI bağlantısı + audio relay
kodunu yazıp gerçek telefon arama testine geçeriz.
