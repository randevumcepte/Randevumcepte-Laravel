# CRM Aktarımları — Planla, Drklinik, Salonappy

Rakip SaaS sistemlerinden randevumcepte.com.tr'ye veri aktarımı için 3 artisan komutu:

| Kaynak | Komut | Dosya |
|---|---|---|
| Planla.co | `planla:import` | [app/Console/Commands/PlanlaImport.php](../app/Console/Commands/PlanlaImport.php) |
| Drklinik.net | `drklinik:import` | [app/Console/Commands/DrklinikImport.php](../app/Console/Commands/DrklinikImport.php) |
| Salonappy.com | `salonappy:import` | [app/Console/Commands/SalonappyImport.php](../app/Console/Commands/SalonappyImport.php) |

Tüm aktarımlar **idempotent marker** ile dedup eder (tekrar çalıştırılırsa duplicate üretmez), `--reset-*` komutu ile geri alınabilir, mevcut yapıyı koruyacak şekilde **canonical helper'ları** (`yeniPersonelKaydi`, `topluHizmetAktar`, `salonAppyAdisyonRandevuEkle`, vb.) kullanır.

---

## 1) Planla.co (`planla:import`)

**Kaynak**: Planla.co web uygulaması, email/şifre ile login + scraping.

**Sinyal**: HTTP form login + endpoint discovery. `PlanlaClient` servisi kullanır.

### Kullanım

```bash
# Tam aktarım
php artisan planla:import \
  --email=KULLANICI \
  --password=SIFRE \
  --salon=355

# Sadece belirli veri tipi
php artisan planla:import --email=X --password=Y --salon=355 --only=musteri,hizmet

# Endpoint keşfi (yazma yapmaz)
php artisan planla:import --email=X --password=Y --probe
php artisan planla:import --probe-api                # POST /connect-api varyantlari
php artisan planla:import --analyze                  # Login olmadan bundle.js taramasi

# Tanılama / tamir
php artisan planla:import --salon=355 --dupes        # Planla tarafindaki tel mukerrer/bos raporu
php artisan planla:import --salon=355 --diagnose     # Portfoye bagli olmayan kayitlari listele
php artisan planla:import --salon=355 --fix-olusturan # Gecersiz olusturan_personel_id'leri salonun default'una ayarla
```

### Parametreler

- `--email`, `--password`: Planla.co giriş bilgileri (zorunlu)
- `--salon`: Hedef randevumcepte salon_id (zorunlu)
- `--only=musteri,hizmet,randevu`: Sadece bu tipleri al
- `--probe`, `--probe-api`, `--analyze`: Salt-okunur keşif modları
- `--dupes`, `--diagnose`, `--fix-olusturan`: Tanı/tamir komutları

### Aktarılan veri

- Müşteriler (telefon dedup'lu)
- Hizmetler + salon hizmet kategorileri
- Randevular (durum + geldi/gelmedi normalize)
- Personeller (`yeniPersonelKaydi` ile)

### Notlar

- Telefon mükerrerlikleri `--dupes` ile önceden taranır
- `--fix-olusturan` mevcut DB'deki yanlış `olusturan_personel_id` referanslarını düzeltir

---

## 2) Drklinik.net (`drklinik:import`)

**Kaynak**: uygulama.drklinik.net (ASP.NET WebForms). `__VIEWSTATE` + `__EVENTVALIDATION` ile sayfa scrape eder. CSRF token rotasyonlu.

**Sinyal**: HTML form post + table parse. Sayfalama, satır limiti gibi WebForms tuzakları için recursive split kullanır.

### Kullanım

```bash
# Tam aktarım (geçmiş + gelecek randevular dahil)
php artisan drklinik:import \
  --username=KULLANICI \
  --password=SIFRE \
  --salon=362 \
  --from=2018-01-01 \
  --to=2030-12-31

# Sadece belirli tip
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --only=tahsilat,gider

# Sadece son durumu raporla, yazma yapma
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --inspect-tahsilat

# Tahsilat reconciliation (4 CSV cikti uretir):
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --report-tahsilat-fark --from=2018-01-01 --to=2026-12-31
# Cikti:
#   /tmp/drk_tahsilat_ok_362.csv         (strict match)
#   /tmp/drk_tahsilat_isim_farki_362.csv (loose match, isim farki)
#   /tmp/drk_tahsilat_gercek_fazla_362.csv (DB'de var, drklinikte yok)
#   /tmp/drk_tahsilat_eksik_362.csv      (drklinikte var, DB'de yok)

# Reconciliation uygula:
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --apply-fazla-sil      # Fazla DB kayitlarini sil
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --apply-eksik-ekle     # Eksik drklinik tahsilatlari ekle

# Gider tamir (HTML'de 100TL'lik dup'lar icin):
php artisan drklinik:import --salon=362 --repair-gider-dedup
php artisan drklinik:import --salon=362 --repair-masraf-kategori

# Seans tamir
php artisan drklinik:import --salon=362 --repair-seans-sayisi
php artisan drklinik:import --salon=362 --cleanup-dummy-aps

# Reset (sadece [drklinik:...] markerli kayitlari):
php artisan drklinik:import --salon=362 --reset-drklinik-satis --dry-run
php artisan drklinik:import --salon=362 --reset-drklinik-satis
```

### Parametreler (önemliler)

- `--username`, `--password`, `--salon`: Zorunlu
- `--from`, `--to`: Tarih aralığı (varsayılan 2018-2026)
- `--only=musteri,hizmet,personel,urun,oda,randevu,tahsilat`
- `--analyze`, `--probe`: Salt-okunur keşif
- `--inspect-tahsilat`, `--inspect-kasa`, `--inspect-musid=X`: Tanı
- `--report-tahsilat-fark`: Reconciliation raporu (4 CSV)
- `--apply-fazla-sil`, `--apply-eksik-ekle`: Reconciliation uygula
- `--repair-*`: Hedef onarımlar
- `--reset-drklinik-satis`: `[drklinik:SatisNo]` markerlı kayıtları sil

### Aktarılan veri

- Müşteriler (musid bazlı, fallback: ad+tel)
- Hizmetler + kategoriler
- Personeller, odalar, cihazlar
- Randevular (geçmiş + gelecek; geldi/gelmedi durumu)
- **Adisyonlar + adisyon_hizmetler** (`seans_sayisi` paket için)
- **AdisyonPaketSeanslar** (kullanılan seanslar `geldi=1`)
- **Tahsilatlar** (ödeme yöntemi + tarih)
- **Masraflar / giderler** (kategori + ödeme + açıklama)

### Dedup marker'ları

- `[drklinik:SatisNo]` — adisyon notunda
- `drk:HASH` — gider için tarih+tutar+saat+aciklama hash'i (`:2`, `:3` suffix'i ile aynı hash tekrarları için)

### Reconciliation iş akışı

Tahsilat eşitleme için tipik akış:
1. `--report-tahsilat-fark` ile 4 CSV üret
2. `gercek_fazla.csv`'yi gözden geçir, `--apply-fazla-sil` ile sil
3. `eksik.csv`'yi gözden geçir, `--apply-eksik-ekle` ile ekle
4. Tekrar `--inspect-tahsilat` ile toplamları kontrol et

---

## 3) Salonappy.com (`salonappy:import`)

**Kaynak**: webapp.salonappy.com (Angular SPA) + REST API (web-api.salonappy.com).

**Sinyal**: Tarayıcıdan JSON dump (browser console scripti) → sunucu tarafında import. Sunucu doğrudan API çağırmaz (Cloudflare datacenter IP blok'u var).

### Akış: 2 aşamalı

#### Aşama 1: Tarayıcıdan dump çekme

[scripts/salonappy_dump_v7.js](../scripts/salonappy_dump_v7.js)'i salonappy açık tarayıcıda Console'a yapıştırın:

1. Salonappy'de giriş yap (TR locale ile)
2. F12 → Console
3. `scripts/salonappy_dump_v7.js` içeriğini yapıştır, Enter
4. Prompt'lar:
   - **Bearer token**: Network sekmesinden bir web-api isteğin Authorization header'ı (default doldurulu, değişirse güncelleyin)
   - **x-device**: Aynı request'in x-device header'ı
   - **Istek arasi gecikme**: 250ms (Cloudflare rate limit için)
   - **Resume DB adi**: Default `sa_v7_resume` (önceki çalışma yarım kalmışsa devam eder)
5. ~38 dakika bekle (4500+ visit × 250ms = 19 dk + master listeler)
6. Otomatik `salonappy_v7_<ts>.json` Downloads'a iner (20-25MB)

**Script özellikleri**:
- IndexedDB ile aralıklı kayıt (kesilirse devam eder)
- 429 (Cloudflare rate limit) algılarsa 30s+ exponential backoff
- Network err'de 5s+ retry, 6 kez dener
- TR locale: `x-language: tr` header'ı ile master listeleri ve booking detayları TR adlarla gelir

**Endpoint'ler** (script otomatik kullanır):
- `/api/service/salon` — hizmet master (TR adlar)
- `/api/staff/list` — personel master
- `/api/product/list` — ürün master
- `/api/client/list` — tüm müşteriler tek istekte
- `/api/visit/list` — tüm visit'ler tek istekte
- `/api/booking/detail?session={id}` — her visit detayı

**Auth header'ları (Network tab'den alınır)**:
- `Authorization: Bearer <token>`
- `x-device: <device-fingerprint>`
- `x-language: tr`
- `x-platform: web`
- `x-version: <surum>`

#### Aşama 2: Sunucuda import

```bash
# Dump'i kopyala:
scp salonappy_v7_*.json root@<server>:/tmp/

# Eski yanlislari temizle:
php artisan salonappy:import --reset-salonappy --salon=368
# Onceki failed import'lardan kalan ozel hizmetleri sil:
php artisan tinker --execute="
\$hids=DB::table('hizmetler')->where('ozel_hizmet',1)->where('salon_id',368)->pluck('id');
DB::table('salon_sunulan_hizmetler')->whereIn('hizmet_id',\$hids)->delete();
DB::table('hizmetler')->whereIn('id',\$hids)->delete();
"

# Import:
php artisan salonappy:import \
  --dump-file=/tmp/salonappy_v7_<ts>.json \
  --salon=368
```

### Parametreler

- `--dump-file`: v7 dump dosyası (zorunlu)
- `--salon`: Hedef salon_id (zorunlu)
- `--services-master`: ESKİ v5 dump'lar için ayrı services master JSON (v6+ dump'larda zorunlu değil, içeride)
- `--reset-salonappy`: `[salonappy:session]` markerlı kayıtları sil
- `--dry-run`: Reset öncesi sadece sayım
- `--username`, `--password`, `--token`, `--proxy`: Server-side API çağırmak için (CF block sebebiyle pratik değil)
- `--analyze`, `--probe`: Endpoint keşfi
- `--from-file=<dir>`: Eski dizin-bazlı mod (deprecated)

### Aktarılan veri

- **Müşteriler** (`aktarimMusteriKontrol` controller ile)
- **Randevular** (status normalize: Approved→Onaylandı, vb.; showup normalize: Showed up→Geldi)
- **Adisyon + adisyon_hizmetler** (`salonAppyAdisyonRandevuEkle` controller)
- **AdisyonHizmetler.seans_sayisi** (paket satış için, post-controller update)
- **AdisyonPaketSeanslar** (paket kullanım için, `salonappySeansiTuket` ile)
- **AdisyonUrunler** (63 ürün, staff_id+staff[] lookup ile personel resolve)
- **Tahsilatlar** (`payments[]` her birini ayrı tahsilat)

### Dedup marker'ları

- `randevular.personel_notu`: `[salonappy:<session>]`
- `adisyonlar.aciklama` (veya benzeri kolon): aynı marker
- `tahsilatlar.notlar`: aynı marker

### İdempotent ve resume özellikleri

- Visit listesi marker ile dedup (`personel_notu LIKE '%[salonappy:session]%'`)
- Paket satış `id` ile dedup (aynı paket farklı visit detaylarında tekrar gelir)
- Tahsilat tarih+tutar dedup
- Tarayıcı scripti IndexedDB'de aralıklı kayıt, 429'da otomatik bekler

### Özel mantıklar

- **TR locale şart**: Script `x-language: tr` ile çeker. EN locale'de bazı hizmetlerin `service_text` boş gelir (silinmiş hizmet referansları)
- **Visit'ler ASC sıralı işlenir**: Paket satışları, paket kullanımlarından önce işlensin diye (seans takibi için kritik)
- **Hizmet matching önceliği**:
  1. Salon-specific (`salon_sunulan_hizmetler` join) trKey match
  2. Global exact match
  3. Global trKey match (case+diacritic insensitive)
  4. Yoksa `ozel_hizmet=true` + salon kategorisi ile yeni hizmet, `salon_sunulan_hizmetler.aktif=0`
- **Paket satış dedup**: Aynı paket `id`'si farklı visit'lerin `package_sales` dizisinde tekrar görünür (salonappy aktif paketleri her visit detayında listeler). Sadece ilk gördüğümüze ekleriz.

---

## Ortak Mimari

### Helper fonksiyonlar (`ApiController`)

Tüm aktarımlar bu canonical helper'ları kullanır:

- `yeniPersonelKaydi($ad, $salonId)` → personel + işletme yetkilisi
- `aktarimMusteriKontrol(Request)` → müşteri ekle / duplicate kontrol
- `salonAppyAdisyonRandevuEkle(Request)` → randevu + adisyon + hizmetler + ürünler tek seferde
- `salonAppyTahsilatEkle(Request)` → tahsilat + tahsilat_hizmetler/urunler/paketler dağıtımı
- `yeni_adisyon_olustur($userId, $salonId, $not, $tarih, $yetkili)` → adisyon
- `adisyon_hizmet_ekle(...)` → adisyon_hizmet kaydı (StokController::receteyiUygula tetikler)

### Stok reçete entegrasyonu

`adisyon_hizmet_ekle` → `StokController::receteyiUygula` → `hizmet_sarf_receteleri` tablosu sorgular.

Bu tablo migration `database/migrations/2026_05_13_100000_create_stok_yonetimi_v2.php` ile gelir. Migration çalıştırılmamış ortamlarda `StokController::receteyiUygula` başında `Schema::hasTable` guard'ı varsa sessizce çıkar (mevcut [StokController.php:830](../app/Http/Controllers/StokController.php#L830)).

### Türkçe karakter normalize (`trKey`)

Tüm importer'larda case + diacritic insensitive matching:
```php
// 'Lazer Epilasyon (Komple Vücut)' === 'lazer epilasyon - komple vucut'
'lazer epilasyon komple vucut'
```

Türkçe karakterler (ı, İ, ş, Ş, ğ, Ğ, ü, Ü, ö, Ö, ç, Ç) ASCII karşılığına çevrilir, kombine diacritic'ler silinir, non-alfanumerik tek boşluğa düşer, trim'lenir.

---

## Sorun giderme

### Salonappy: tüm visit'ler fail (`hata=4567`)

`StokController::receteyiUygula` `hizmet_sarf_receteleri` tablosu yokken exception fırlatıyordu. [StokController.php:830](../app/Http/Controllers/StokController.php#L830)'da `Schema::hasTable` guard var; yine de fail olursa migration çalıştır:

```bash
php artisan migrate --path=database/migrations/2026_05_13_100000_create_stok_yonetimi_v2.php
```

### Salonappy: hizmetler `Salonappy` kategorisinde özel hizmet olarak yaratıldı

Dump EN locale çekilmiştir. TR locale ile yeniden çek:
1. Salonappy → ayarlar → dil: Türkçe
2. Sayfayı yenile (hard refresh)
3. [scripts/salonappy_dump_v7.js](../scripts/salonappy_dump_v7.js) ile yeniden dump

Ardından eski özel hizmetleri temizle:
```bash
php artisan tinker --execute="
\$hids=DB::table('hizmetler')->where('ozel_hizmet',1)->where('salon_id',368)->pluck('id');
DB::table('salon_sunulan_hizmetler')->whereIn('hizmet_id',\$hids)->delete();
DB::table('hizmetler')->whereIn('id',\$hids)->delete();
"
```

### Drklinik: tahsilat tutarı DB ≠ kasayla uyuşmuyor

Reconciliation iş akışı (yukarıda detaylı):
1. `--report-tahsilat-fark` 4 CSV üret
2. CSV'leri incele
3. `--apply-fazla-sil` + `--apply-eksik-ekle` uygula

### Drklinik: aynı 100 TL gider 2x görünüyor

HTML'de `BTN_GiderHepsi` ile çekerken aynı satır tekrar dönebilir. Hash marker'a occurrence counter eklenir (`:2`, `:3`). Eski import'lar için:
```bash
php artisan drklinik:import --salon=362 --repair-gider-dedup
```

### Salonappy: 429 (Cloudflare rate limit)

Tarayıcı scripti throttle (250ms default) ile 4 req/s gönderir. 429 görürse 30s+ exponential backoff yapar. IndexedDB resume sayesinde kesilirse devam eder. Manuel müdahale gerekmez.

### Gelecek randevular eksik

`/api/visit/list` default'ta sadece geçmiş + bugünü döner. Gelecek randevular için ayrı bir filter parametre keşfi gerekebilir (`?from_date=...&to_date=...` veya `?upcoming=1`). Henüz çözülmedi — Network sekmesinden salonappy'nin "Yaklaşan randevular" sayfası açıldığında ne çağırdığına bakılmalı.

---

## Repo dosyaları

```
app/Console/Commands/PlanlaImport.php
app/Console/Commands/DrklinikImport.php
app/Console/Commands/SalonappyImport.php
app/Services/PlanlaClient.php
app/Services/DrklinikClient.php
app/Services/SalonappyClient.php
app/Imports/PlanlaImporter.php
scripts/salonappy_dump_v7.js
scripts/salonappy_dump_v6.js           # eski versiyon
scripts/salonappy_scraper_resilient.py # Python Selenium fallback (deprecated)
```
