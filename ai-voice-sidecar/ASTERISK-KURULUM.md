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

**Yaklaşım:** Ayrı bir `[sesli-asistan]` context'i kuruyoruz. Mevcut IVR/SIP
context'inden, belirli bir aranan numara veya tuş bu yeni context'e
yönlendirilir. Bu sayede mevcut IVR akışı bozulmaz.

### 3.1. AI context'i — yeni bir bölüm olarak ekle

```ini
; ============================================================
;  AI SESLI ASISTAN CONTEXT
;  Sidecar Stasis app: randevu_ai
;  Parametreler: CALLERID + arandigi numara
; ============================================================
[sesli-asistan]
exten => s,1,NoOp(=== AI Sesli Asistan baslatildi ===)
 same => n,Answer()
 same => n,Wait(0.3)                                                ; SIP setup tamamlansin
 same => n,Set(CHANNEL(language)=tr)
 same => n,Stasis(randevu_ai,${CALLERID(num)},${FROM_DID})           ; sidecar'a devret
 same => n,Hangup()

; Stasis baglanti kuramazsa (sidecar dustu / ARI hatasi) → fallback
exten => failed,1,NoOp(AI baglanti hatasi, eski IVR'a donuluyor)
 same => n,Playback(silence/1)
 same => n,Goto(from-pstn,s,1)                                      ; mevcut IVR context'in adi neyse
 same => n,Hangup()

exten => h,1,NoOp(AI cagrisi sonlandi - DURATION=${CDR(duration)}s)
```

### 3.2. Mevcut context'ten bu context'e yönlendirme

Aranan numaraya göre 3 seçeneğin var, sana uygun olanı seç:

#### A) Belirli bir DID (gelen numara) AI'ya yönlensin

```ini
[from-pstn]                                  ; mevcut gelen kanal context'in
; ... mevcut satirlarin ...

; Bu numarayi arayan direkt AI'ya gider, IVR'i atlar
exten => 02121234567,1,Set(FROM_DID=${EXTEN})
 same => n,Goto(sesli-asistan,s,1)
```

#### B) Mevcut IVR menüsünde bir tuş (örn. 9) AI'ya geçirsin

```ini
[ana-ivr]                                    ; mevcut IVR menun ne adlaysa
; ... 1, 2, 3 ... mevcut secenekler ...

exten => 9,1,NoOp(Musteri AI asistani sectik)
 same => n,Set(FROM_DID=${EXTEN})
 same => n,Goto(sesli-asistan,s,1)
```

#### C) Tüm gelen çağrılar AI ile karşılanır (riskli, fallback yoksa kullanma)

```ini
[from-pstn]
exten => _X.,1,Set(FROM_DID=${EXTEN})
 same => n,Goto(sesli-asistan,s,1)
```

### 3.3. Test için (SIP extension'dan)

```ini
[from-internal]                              ; veya dahili context'in
; Test: 9999'u ara, AI baglansin
exten => 9999,1,Set(FROM_DID=test)
 same => n,Goto(sesli-asistan,s,1)
```

### 3.4. Sidecar'a giden parametreler

`Stasis(randevu_ai, ${CALLERID(num)}, ${FROM_DID})` üç parametre yollar:

| Parametre | Değer | Sidecar nasıl kullanır |
|---|---|---|
| App adı | `randevu_ai` | Sidecar bu app'i dinler |
| Arg 1 | `${CALLERID(num)}` (arayan kişinin telefon no.) | Müşteri kaydı bul/oluştur |
| Arg 2 | `${FROM_DID}` (arandı numara) | Hangi salonu arıyor → salon_id tespit |

> `FROM_DID` — sidecar tarafında bir DID→salon_id eşleme tablosu olacak. Hangi
> salon hangi numarayı kullanıyor bilgisi sidecar `.env`'sinde veya Laravel'de
> bir tabloda tutulacak. Şimdilik tek salon için sabit `TEST_SALON_ID` kullanır.

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
module reload res_http_websocket.so
http reload
ari reload
dialplan reload

# Doğrulamalar:
http show status                          ; HTTP sunucusu calisir gorunuyor mu?
ari show status                           ; ARI etkin mi?
ari show users                            ; randevu_ai kullanicisi listede mi?
dialplan show sesli-asistan               ; AI context'i yuklendi mi?
dialplan show s@sesli-asistan             ; Stasis satiri var mi?
dialplan show 9999@from-internal          ; test extension yonlendiriyor mu?
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
asterisk -rx 'channel originate Local/9999@from-internal application Stasis randevu_ai'

# Veya gerçek SIP telefondan 9999'u ara → AI selamlamasi gelmeli.

# Sesli-asistan context'inin trace'ini izle:
asterisk -rvvv
# Içerideyken:
core set verbose 5
dialplan show sesli-asistan
# Sonra arama yap, ekranda Stasis'e dustugunu gor.
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
