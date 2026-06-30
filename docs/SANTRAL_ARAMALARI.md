# Santral (FreePBX) Hatırlatma Aramaları

Reklam (kampanya), randevu teyit ve alacak (borç) hatırlatma aramalarının
cron + kuyruk (job) mimarisi. Eczane24 projesindeki sistemin RandevuMcepte
uyarlamasıdır.

## Akış

```
cron (her dakika)
  └─ artisan schedule:run
       ├─ kampanyaarama:yap          (Reklam — App\Console\Commands\KampanyaAramaYap)
       ├─ randevuarama:yap           (Randevu — RandevuHatirlatmaAramasiYap)
       └─ alacakhatirlatma:aramayap  (Alacak  — AlacakHatirlatmaAramasiYap)
             │
             │  aranacakları toplar, 50'şerli chunk'lara böler, her chunk'ı
             │  35 sn arayla HatirlatmaAramaJob ile kuyruğa atar (delay)
             ▼
   database kuyruğu (queue: hatirlatmalar / notifications)
             │
             ▼
   queue:work (supervisor: randevumcepte-arama-worker)
             │
             ▼
   Controller::hatirlatmaaramasiyap()
             │  Asterisk AMI'ye bağlan (34.45.69.65:5038, cxpanel)
             ▼
   Action: Originate  → PJSIP/0<tel>@<sabitno>  → Exten <n> @ from-internal-custom
```

`exten` değeri santral dialplan'ında karşılanır:
- `1` → randevu / alacak teyit menüsü (1: onayla, 2: ertele/operatör)
- `3` → reklam (kampanya) anonsu

## Kuyruk mimarisi (neden ayrı connection)

- Global `QUEUE_DRIVER` **`sync`** kalır (ilaç/adet/ölçüm hatırlatma job'ları
  inline çalışmaya devam eder; davranışları değişmez).
- Sadece arama job'ları (`HatirlatmaAramaJob`, `SendCompletionNotification`)
  sınıf üzerinde `public $connection = 'database'` ile **asenkron** kuyruğa gider.
- Bu yüzden arama job'larını işlemek için **çalışan bir worker süreci şarttır**.

`config/queue.php` → `connections.database.retry_after = 1800`. Bir arama job'u
50 aramaya kadar sürebildiği için bu değer job `timeout` (1700) değerinden büyük
olmalı; aksi halde iş biterken kuyruğa geri düşer ve **aynı numaralar mükerrer
aranır**.

## Worker kurulumu (prod — supervisor)

Eczane24'teki worker ile aynı yöntem. Repodaki conf'u supervisor'a kopyala:

```bash
sudo cp resources/randevumcepte-arama-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update          # program'ı ekler ve başlatır
sudo supervisorctl status randevumcepte-arama-worker
```

Çalıştırdığı komut:
`php artisan queue:work database --queue=hatirlatmalar,notifications --sleep=3 --tries=1 --timeout=1700`

`deploy.sh` her deploy'da `php artisan queue:restart` çağırır → supervisor worker'ı
nazikçe yeniden başlatır, güncel job koduyla devam eder. `stopwaitsecs=1800`
olduğu için restart sırasında süren bir arama partisi yarıda kesilmez.

Log: `storage/logs/arama-worker.log`.

## Tablolar (migration ile, idempotent)

- `jobs`, `failed_jobs` — database kuyruğu (Laravel 5.6 şeması).
- `arama_istatistikleri` — arama başarı/hata kaydı.

Deploy `php artisan migrate --force` çalıştırır; tablolar yoksa oluşur, varsa no-op.

## E-Asistan ayarları (salon_easistan_ayarlari.ayar_id)

| ayar_id | Anlam |
|---------|-------|
| 1 | Alacak hatırlatma araması açık/kapalı |
| 4 | Randevu hatırlatma araması açık/kapalı |
| 8 | Reklam (kampanya) araması açık/kapalı |

İlgili salon için `acik_kapali = 0` ise o tür arama yapılmaz.

## Mükerrer arama / güvenlik notları

- Arama job'ları `tries = 1` ve `handle()` içinde hata yutar (rethrow yok) →
  bir job asla retry edilmez, kimse iki kez aranmaz.
- Her numara için başarı/başarısızlık `Controller` içinde tek tek işaretlenir
  (`*HatirlatmaAramasiYapildiIsaretle`).
- `Controller::hatirlatmaSaatiIcinde()` ile arama saatleri (10:00–19:30)
  dışındaki kayıtlar aranmaz, ertesi güne ertelenir.
- Kara listedeki müşteriler (`musteri_portfoy.kara_liste`) atlanır.
```
