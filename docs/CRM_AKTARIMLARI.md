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

**Sinyal**: HTML form post + table parse. Sayfalama, satır limiti gibi WebForms tuzakları için pagination (RP_Sayfalar_*) + form state preservation kullanır.

### Genel akış

Drklinik UI **musid-bazlı atomik** modelle çalışır. Her müsteri için `musteri.aspx?musid=X` sayfasında 5 tablo var:
1. **Satışlar** → adisyon + adisyon_hizmetler + tahsilatlar (satis.aspx?id=X&tip=d detay'dan)
2. **Hizmet/Ürün** alımları (yalnız goruntu, biz Satislar tab'ından yazıyoruz)
3. **Randevular** + Seans Düşümü işareti → randevular + randevu_hizmetler + APS tüketimi
4. **Kalan Seanslar** → drklinik aggregate sayım (sadece DOĞRULAMA için; biz override etmiyoruz)
5. **Tahsilatlar** → tahsilatlar (alternatif kaynak)

Tüm aktarım **idempotent**: marker dedup ile tekrar çalıştırılabilir, eski kayıtlar update edilir.

### Kullanım

```bash
# === EN SIK KULLANILAN: full reimport + per-musteri verify ===
php artisan drklinik:import \
  --username=KULLANICI --password=SIFRE \
  --salon=362 \
  --only=satis-tahsilat \
  --verify

# Cikti: /tmp/drk_verify_<salon>.csv
# Her musteri+hizmet icin OK / FARK / EKSIK_DB durumu yazilir
# Sonda ozet: "Verify ozet: OK=X FARK=Y EKSIK_DB=Z"

# === Spesifik tip ===
php artisan drklinik:import --salon=362 --only=hizmet,personel,urun,oda  # kurulum
php artisan drklinik:import --salon=362 --only=randevu                    # gunlukrandevulistesi.aspx (eski yol)
php artisan drklinik:import --salon=362 --only=satis-tahsilat             # musteri.aspx atomik (yeni)
php artisan drklinik:import --salon=362 --only=gider                      # masraflar

# === Tek-musteri repair (tanılama) ===
php artisan drklinik:import --salon=362 --username=X --password=Y \
  --repair-musid=2295052

# === Tanılama ===
php artisan drklinik:import --username=X --password=Y --salon=362 --inspect-tahsilat
php artisan drklinik:import --username=X --password=Y --salon=362 --inspect-kasa
php artisan drklinik:import --username=X --password=Y --salon=362 --inspect-musid=2295052
php artisan drklinik:import --salon=362 --debug-seans-musid=2295052

# === Reconciliation (drklinik kasa vs DB tahsilat) ===
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --report-tahsilat-fark --from=2024-01-01 --to=2026-12-31
# Cikti:
#   /tmp/drk_tahsilat_isim_farki_<salon>.csv   (loose match)
#   /tmp/drk_tahsilat_gercek_fazla_<salon>.csv (DB'de var, drklinikte yok)
#   /tmp/drk_tahsilat_eksik_<salon>.csv        (drklinikte var, DB'de yok)

php artisan drklinik:import --salon=362 --apply-fazla-sil
php artisan drklinik:import --salon=362 --apply-eksik-ekle
php artisan drklinik:import --username=X --password=Y --salon=362 --add-eksik-musteriler

# === Seans dogrulama ===
php artisan drklinik:import --username=X --password=Y --salon=362 --report-seans-fark
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --reprocess-seans-fark-musteriler

# === Onarim / Temizlik komutlari ===
php artisan drklinik:import --salon=362 --cleanup-0000-randevu          # saat=00:00 placeholder randevular
php artisan drklinik:import --salon=362 --cleanup-hatali-hizmetler      # parens/N seans formati bozuk hizmet
php artisan drklinik:import --salon=362 --cleanup-duplicate-hizmetler   # trKey ayni hizmetleri merge
php artisan drklinik:import --salon=362 --cleanup-aps-overflow          # APS > seans_sayisi (kapasite)
php artisan drklinik:import --salon=362 --dedupe-internal-tahsilat      # ic-duplicate tahsilat
php artisan drklinik:import --salon=362 --merge-tahsilat-duplicates     # kasa-NULL + satis-detay merge
php artisan drklinik:import --username=X --password=Y --salon=362 \
  --add-missing-hizmetler                                                # EKSIK_DB hizmetlerini pasif olarak ekle

# === Reset ===
php artisan drklinik:import --salon=362 --reset-drklinik-satis          # [drklinik:..] markerli adisyonlar
php artisan drklinik:import --salon=362 --nuke-salon-data               # tum hareket verisi (musteri korunur)
php artisan drklinik:import --salon=362 --wipe-salon-tahsilatlar        # sadece tahsilatlar
php artisan drklinik:import --salon=362 --wipe-salon-masraflar          # sadece masraflar
```

### Aktarılan veri

| Yer | Açıklama |
|---|---|
| `users` + `musteri_portfoy` | musid bazlı; telefon/ad fallback ile dedup |
| `hizmetler` + `salon_sunulan_hizmetler` | Drklinik kategori; bulunamayan hizmet adları için **pasif kayıt** (`aktif=0`) otomatik açılır |
| `personeller`, `odalar` | calisanmodulu.aspx + cihazlar |
| `adisyonlar` + `adisyon_hizmetler` | seans_sayısı paket bilgisi; idempotent update |
| `adisyon_paket_seanslar` (APS) | **randevu-bazlı** tüketim (her "Seanstan Düş İşaretlenmiş" randevu → APS) |
| `randevular` + `randevu_hizmetler` | `dusum_miktari` kolonu ile multi-hizmet "(X x N)" desteği |
| `tahsilatlar` | satis.aspx?tip=d detay sayfasından kalemleri ile |
| `masraflar` | kasa_islemleri.aspx gider sekmesi |

### Dedup marker'ları

| Marker | Yer | Amaç |
|---|---|---|
| `[drklinik:SatisNo]` | `adisyonlar.notlar` | Satış idempotent dedup |
| `[drk-tah:SatisNo:idx]` | `tahsilatlar.notlar` | Satis-detay'dan gelen tahsilat dedup |
| `drk:HASH` | `masraflar.notlar` | Gider için tarih+tutar+saat+aciklama hash (`:N` suffix ile dup) |

### Seans (APS) yazma kuralları — KRITIK

Drklinik **per-hizmet aggregate** sayıyor (Kalan Seanslar tablosu: alındı/harcanan/kalan). Biz **per-randevu** kayıt tutuyoruz (APS). İki sistem arası uyumsuzluk hep bug üretmişti — **kalıcı çözüm**:

1. `processKalanSeanslar` DEVRE DIŞI — randevu-bazlı APS tek source of truth
2. `processMusteriRandevular` başında o müşterinin TÜM APS'leri silinir → randevulardan **CLEAN REBUILD**
3. Multi-hizmet randevu `(Hizmet1 x 1),(Hizmet2 x 10)` her kalem için **ayrı `seanslariTuket`** çağrısı
4. `seanslariTuket` SADECE eşleşen `hizmet_id` AH'larını tüketir (cross-hizmet fallback YOK)
5. Türkçe `İ` normalize: `mb_strtolower("İşaretlenmiş")` → `i̇şaretlenmiş` combining mark → strpos eşleşmez → normalize sart
6. "Seanstan Düş İşaretlenmiş" + NOT "Düşülmeyecek" + (durum şart değil — admin işaretlediyse drk düşmüş)
7. AH dedup'ta `seans_sayisi` GUNCELLENIR (drklinik admin satışı edit etmis olabilir); kapasite azalırsa APS overflow temizlenir
8. **ensureSalonHizmet `forceHizmet` kuralı**: seans bağlamı (randevu/seans tablosu) %100 hizmet; satış kaleminde `seans>1` %100 hizmet; aksi durumda urun-skip aktif

Detay: [`memory/project_drklinik_seans_dusumu.md`](../.claude/projects/-Users-ferdi-Desktop-randevumcepte-yeni/memory/project_drklinik_seans_dusumu.md)

### Tipik reconciliation iş akışı

```bash
# 1) Cleanup (eski hatali data)
php artisan drklinik:import --salon=362 --cleanup-duplicate-hizmetler
php artisan drklinik:import --salon=362 --cleanup-0000-randevu
php artisan drklinik:import --salon=362 --cleanup-aps-overflow

# 2) Full reimport + verify
nohup php artisan drklinik:import --salon=362 --username=X --password=Y \
    --only=satis-tahsilat --verify > /tmp/drk362_reimport.log 2>&1 &
tail -f /tmp/drk362_reimport.log
# Bittiginde: "Verify ozet: OK=X FARK=Y EKSIK_DB=Z" satirina bak

# 3) EKSIK_DB hizmetler (drklinikte var bizde yok)
php artisan drklinik:import --salon=362 --username=X --password=Y \
    --add-missing-hizmetler                  # forceHizmet ile urun-also olanlar dahil

# 4) Tahsilat reconciliation
php artisan drklinik:import --salon=362 --username=X --password=Y \
    --report-tahsilat-fark --from=2024-06-01 --to=2026-12-31
# Eksik tahsilatlari musteri ismi uzerinden bagla:
php artisan drklinik:import --salon=362 --apply-eksik-ekle
# Bulunamayan musterileri drklinik aramasiyla ekle:
php artisan drklinik:import --salon=362 --username=X --password=Y \
    --add-eksik-musteriler

# 5) Final verify
nohup php artisan drklinik:import --salon=362 --username=X --password=Y \
    --only=satis-tahsilat --verify > /tmp/drk362_final.log 2>&1 &
```

### Web tabanli API endpoint'leri (alternatif)

CLI yerine HTTP cagri ile (admin auth altinda):
- `GET  /isletmeyonetim/api/drklinik/scan/{musid}?salon=362` — DB vs drklinik karsilastir
- `POST /isletmeyonetim/api/drklinik/repair/{musid}?salon=362` — tek musteri repair
- `GET  /isletmeyonetim/api/drklinik/satis-mismatch?salon=362` — verify CSV'den satis farklarini liste
- `GET  /isletmeyonetim/api/drklinik/verify-ozet?salon=362` — verify CSV ozet
- `POST /isletmeyonetim/api/drklinik/full-reimport?salon=362` — background reimport baslat

Controller: [`app/Http/Controllers/DrklinikApiController.php`](../app/Http/Controllers/DrklinikApiController.php)

### Bilinen yapısal farklar

- **Compound paketler**: "Heykel+Ems+G5+Lenf Drenaj 3 seans" tek isimli hizmet ama drklinik Kalan Seanslar'da 4 hizmet × 3 seans = 12 olarak aggregate edebiliyor. Bizim parser literal "X Seans = Y TRY" alır; admin manuel "1×12" yazarsa düzgün okunur. Otomatik decompose yok.
- **Drklinik admin manuel düzeltmeleri**: Kalan Seanslar tablosunda admin manuel harcanan ayarlayabilir; bizim randevu sayımı ile birebir tutmayabilir (örnek: aynı randevuda "X seans ems x 1" iki kez geçen satır bizde 2 düşülür, drklinik elle 1'e indirmiş olabilir).
- **Kasa-only entries**: Drklinik kasa listesinde gözüken ama satis-detay'da olmayan tahsilatlar mevcut. `--apply-eksik-ekle` bunları musteri ismi LIKE match ile bağlar.

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

## 4) Salonrandevu (`salonrandevu:import`)

**Kaynak**: app.salonrandevu.com (web app) + REST API. `SalonrandevuClient` servisi.

**Sinyal**: Login (email/şifre veya telefon) + Bearer token + `/company/*` endpoint'leri JSON döner.

### Kullanım

```bash
# Tam aktarım
php artisan salonrandevu:import \
  --email=KULLANICI \
  --password=SIFRE \
  --salon=195

# Sadece belirli tip
php artisan salonrandevu:import --email=X --password=Y --salon=195 \
  --only=musteri,hizmet,randevu

# Endpoint keşfi (yazma yapmaz)
php artisan salonrandevu:import --analyze            # Anasayfa + bundle.js
php artisan salonrandevu:import --email=X --password=Y --probe   # Login + endpoint kesfi
php artisan salonrandevu:import --email=X --password=Y --inspect # Her endpoint ilk kayit yapisi

# Reset
php artisan salonrandevu:import --salon=195 --reset-salonrandevu  # [salonrandevu:RefId] markerli sil
php artisan salonrandevu:import --salon=195 --reset-all           # Tum islem verisi (salon sr'ya aitse)

# Proxy ile (rate limit/IP blok varsa)
php artisan salonrandevu:import --email=X --password=Y --salon=195 \
  --proxy=http://user:pass@host:port
```

### Parametreler

- `--email`, `--password`: Salonrandevu giriş (telefon da kabul edilir)
- `--salon`: Hedef randevumcepte salon_id
- `--only=musteri,hizmet,personel,randevu,tahsilat,paket,urun,gider`
- `--from`, `--to`: Tarih aralığı (varsayılan 2020-2030)
- `--analyze`, `--probe`, `--inspect`: Salt-okunur keşif
- `--reset-salonrandevu`, `--reset-all`: Geri alma

### Aktarılan veri

- **Müşteriler** (`/company/customers?extra=1&page=N` — pagination'lı)
- **Hizmetler** + kategoriler
- **Personeller**
- **Ürünler**
- **Randevular** (sayfa-sayfa, resilient retry ile)
- **Tahsilatlar / receipt'ler** (her receipt detay'i)
- **Giderler**

### Dedup marker

- `[salonrandevu:RefId]` — adisyon/randevu notunda

### Notlar

- `SalonrandevuClient.get` bağlantı koparsa **6x backoff retry** yapar
- Müşteri listesi pagination'lı; tüm sayfalar çekilir
- Receipt'ler tek tek detay endpoint'inden okunur (kalemler dahil)
- Drklinik gibi atomik **musid-bazlı** akış değil — REST API üzerinden tip-tip iterasyon

---

## Repo dosyaları

```
app/Console/Commands/PlanlaImport.php
app/Console/Commands/DrklinikImport.php
app/Console/Commands/SalonappyImport.php
app/Console/Commands/SalonrandevuImport.php
app/Services/PlanlaClient.php
app/Services/DrklinikClient.php
app/Services/SalonappyClient.php
app/Services/SalonrandevuClient.php
app/Imports/PlanlaImporter.php
app/Imports/DrklinikImporter.php
app/Imports/SalonrandevuImporter.php
app/Http/Controllers/DrklinikApiController.php   # REST API endpoint'leri
scripts/salonappy_dump_v7.js
scripts/salonappy_dump_v6.js           # eski versiyon
scripts/salonappy_scraper_resilient.py # Python Selenium fallback (deprecated)
scripts/drklinik.py                    # Python Selenium scraper (referans implementasyon)
```
