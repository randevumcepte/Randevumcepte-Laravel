# Randevumcepte WhatsApp Servisi

Laravel sisteminin randevu hatırlatmalarını WhatsApp üzerinden gönderen Node.js yan servisi.  
Baileys (whatsapp-web protokolü) ile çalışır. **Resmi olmayan bir entegrasyondur — ban riski azaltılmıştır ama tamamen ortadan kalkmaz.**

## Kurulum (Sunucuda)

Gereksinim: Node.js 20+ ve pm2.

```bash
cd /var/www/randevumcepte/whatsapp-service
npm install --production
cp .env.example .env
# .env dosyasını düzenle:
#  - SHARED_SECRET: Laravel tarafındaki WHATSAPP_SERVICE_TOKEN ile aynı olmalı
#  - LARAVEL_WEBHOOK_URL: https://<siteniz>/webhook/whatsapp
#  - LARAVEL_WEBHOOK_SECRET: Laravel WHATSAPP_WEBHOOK_SECRET ile aynı
nano .env

mkdir -p logs sessions
npm install -g pm2   # eğer kurulu değilse
pm2 start ecosystem.config.js
pm2 save
pm2 startup          # çıkan komutu çalıştır ki yeniden başlatmalarda otomatik açılsın
```

## Laravel .env

```
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001
WHATSAPP_SERVICE_TOKEN=<whatsapp-service/.env içindeki SHARED_SECRET ile aynı>
WHATSAPP_WEBHOOK_SECRET=<whatsapp-service/.env içindeki LARAVEL_WEBHOOK_SECRET ile aynı>
```

## Laravel Migration

```bash
php artisan migrate
```

Eklenen tablolar/kolonlar:
- `salonlar`: whatsapp_aktif, whatsapp_durum, whatsapp_numara, whatsapp_baglanti_tarihi, whatsapp_gunluk_limit, whatsapp_warmup_baslangic, whatsapp_son_hata
- `users`: whatsapp_onay (default 1)
- `salon_sms_ayarlari`: whatsapp_musteri, whatsapp_personel
- `whatsapp_gonderim_loglari`: yeni tablo (gönderim takibi + SMS fallback dahil)

## Ban Önleme Katmanları (Ultra-Güvenli Mod)

| Katman | Detay |
|---|---|
| **Sadece 1 gün önce hatırlatma** | `ayar_id=6` dışında WhatsApp'a hiçbir mesaj gitmez. Onay/iptal/doğum günü/kampanya hepsi SMS. |
| Her salon kendi numarasını tarar | Ban tek salona izole, sistem çökmez |
| Mesaj aralığı | **60-120 saniye** (1-2 dakika) gaussian jitter |
| Typing simülasyonu | Her mesaj öncesi 2-4s "yazıyor..." |
| Günlük tavan | Salon başına default 150 |
| Warm-up | 15 → 30 → 50 → 80 → 110 → 140 → 180 (7 gün) |
| Çalışma saatleri | 09:00-21:00 dışı reddedilir (SMS'e düşer) |
| Batch mola | 50 mesajda bir 10 dk durur |
| WhatsApp kontrolü | Gönderim öncesi numaranın WhatsApp'ta olduğu doğrulanır |
| **Ardışık fail-tracking** | 3 başarısızlıkta oto-durdur + `ban.warning` webhook |
| **Pencere fail-tracking** | 30 dakikada 5 başarısızlıkta oto-durdur |
| **Baileys ban kodları** | 401/403/406/410/411/500 → session siliniyor, ban.warning |
| **Rate-limit kodları** | 408/428/429/440 → session siliniyor, ban.warning |
| **Oto-durdur → Admin bildirimi** | SMS + OneSignal push + panel bildirimi |
| SMS fallback | WhatsApp hata verirse otomatik SMS gönderilir |
| Mesaj varyasyonu | 4 farklı selamlama × 4 farklı kapanış + müşteri adı |

## Sağlık Kontrolü

```bash
curl http://127.0.0.1:3001/health
pm2 logs randevumcepte-whatsapp --lines 100
```

## Güvenlik

- Servis sadece `127.0.0.1` üzerinde dinler (dış dünyaya açılmaz).
- Her istek `X-Service-Token` header'ı ile korunur.
- Webhook `X-Webhook-Secret` ile korunur.
- Session dosyaları `sessions/salon_{id}/` klasöründe; yedek alırken **dahil etmeyin** (kimlik verisi).

## Sık Karşılaşılan Durumlar

- **QR sürekli yenileniyor**: Telefon ile sunucu arasında zaman farkı ya da WhatsApp hesabı askıya alınmış olabilir.
- **`banned-or-loggedout` durumu**: Numara bantlandı veya kullanıcı telefondan cihazı kaldırdı. `sessions/salon_X` klasörü silinir, işletme yeni QR taratmalı.
- **Laravel `servis-kapali` gösteriyor**: `pm2 status` ile servisin açık olduğundan emin olun. Port 3001'in firewall tarafından localhost'a açık olduğunu kontrol edin.

## İşletme Kullanım Akışı

1. İşletme paneli → WhatsApp → **Bağla** butonuna tıklar.
2. Çıkan QR'ı telefonundaki WhatsApp > Bağlı Cihazlar menüsünden okutur.
3. Durum "Bağlı" olur, sonraki randevu hatırlatmaları WhatsApp'a düşer.
4. SMS Ayarları sayfasındaki "WhatsApp gönderimini aç" (`whatsapp_musteri`) seçeneğiyle kanal aktif edilir.

## İzleme (Prod)

Günlük kontrol:
- `whatsapp_gonderim_loglari` tablosu — durum dağılımı (1=gönderildi, 2=başarısız, 3=SMS'e düştü).
- `salonlar.whatsapp_son_hata` — banlanan/atılan salonlar.
- `pm2 logs` — bağlantı olayları, retry'lar.
