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
   database kuyruğu (queue: hatirlatmalar)
             │
             ▼
   queue:work (MEVCUT supervisor: randevumcepte-worker)
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

## Kuyruk mimarisi

- Arama job'ları (`HatirlatmaAramaJob`, `SendCompletionNotification`) **default
  connection** ile `hatirlatmalar` kuyruğuna gider — ilaç/adet hatırlatma
  job'larıyla **birebir aynı** desen. Connection sınıfta zorlanmaz.
- **Prod'da `QUEUE_DRIVER=database`** olmalı (zaten öyle; mevcut worker bunun
  kanıtı). Lokalde `sync` ise job'lar inline çalışır.
- `$tries = 1` (job-level) — worker CLI'da `--tries=3` olsa bile bunu ezer; bir
  arama job'u asla retry edilmez → **mükerrer arama olmaz**.

`config/queue.php` → `connections.database.retry_after = 1800` (job `timeout`
1700'den büyük; aksi halde uzun süren iş kuyruğa geri düşüp **aynı numaralar
mükerrer aranır**).

## Worker (prod — MEVCUT, yeni kurulum gerekmez)

Worker zaten çalışıyor: `/etc/supervisor/conf.d/randevumcepte-worker.conf`

```
[program:randevumcepte-worker]
command=/opt/php74/bin/php .../artisan queue:work --queue=hatirlatmalar --sleep=3 --timeout=1800 --tries=3
```

Bu worker `hatirlatmalar` kuyruğunu işlediği için santral arama job'ları da
otomatik işlenir — **ekstra program/komut gerekmez**. `deploy.sh` her deploy'da
`php artisan queue:restart` çağırır → worker güncel job koduyla devam eder.

Tek kontrol: prod `.env` içinde `QUEUE_DRIVER=database` olduğundan emin ol
(`grep QUEUE_DRIVER .env`). Worker'ın canlı olduğunu görmek için:
`supervisorctl status randevumcepte-worker`.

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
