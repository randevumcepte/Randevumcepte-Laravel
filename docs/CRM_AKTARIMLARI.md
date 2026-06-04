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
  --email=ceviz1235@gmail.com \
  --password=123456. \
  --salon=355

  /opt/php74/bin/php artisan planla:import --email=suveydascaylak@gmail.com  --password=svyd6717 --salon=370

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
/opt/php74/bin/php artisan salonappy:import   --dump-file=/tmp/salonappy_v7_1780435641843.json --salon=368
```

### Parametreler

- `--dump-file`: v7 dump dosyası (zorunlu)
- `--salon`: Hedef salon_id (zorunlu)
- `--services-master`: ESKİ v5 dump'lar için ayrı services master JSON (v6+ dump'larda zorunlu değil, içeride)
- `--reset-salonappy`: salon_id bazlı TÜM transactional veriyi sil — randevu/adisyon/tahsilat/masraf **ve** taksitli_tahsilatlar/taksit_vadeleri/alacaklar. Müşteri + kurulum (hizmet/personel/ürün) korunur.
- `--only-package-sales`: Sadece paket satışlarını işle (modüler akış için, bkz. aşağıda)
- `--only-package-payments`: Paket satışlarına ait tahsilatları işle (modüler akış için)
- `--dry-run`: Reset öncesi sadece sayım
- `--username`, `--password`, `--token`, `--proxy`: Server-side API çağırmak için (CF block sebebiyle pratik değil)
- `--analyze`, `--probe`: Endpoint keşfi
- `--from-file=<dir>`: Eski dizin-bazlı mod (deprecated)

### Modüler aktarım (parça parça)

Tek seferde full dump v7 + import yerine her veri tipini ayrı bir dump + ayrı bir komutla işleme akışı. Hata izolasyonu ve aşamalı onay için tercih edilir.

#### Paket satışları (Aşama A + B)

**Dump**: [scripts/salonappy_dump_package_sales.js](../scripts/salonappy_dump_package_sales.js) — sadece `/api/client/list` + `/api/package_sale/list_v2` + `/api/payment/list` çeker. IndexedDB resume YOK (her çalıştırma fresh). ~2 dk.

**Sıra**:

```bash
# 1) Reset (taksitli_tahsilatlar + taksit_vadeleri + alacaklar dahil tüm transactional veri)
/opt/php74/bin/php artisan salonappy:import --reset-salonappy --salon=368

# 2) Tarayıcıda dump_package_sales.js çalıştır → salonappy_package_sales_<ts>.json indir
#    scp ile sunucuya yükle:
scp salonappy_package_sales_*.json root@<server>:/tmp/

# 3) PASS1 — paket satışları + alacaklar (tahsilat YOK)
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_package_sales_<ts>.json \
    --salon=368 --only-package-sales

# 4) Onaylayınca PASS2 — tahsilatları paket adisyonlarına bağla
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_package_sales_<ts>.json \
    --salon=368 --only-package-payments
```

**PASS1 (`--only-package-sales`)** ne yapar:

- Her paket satışı için `adisyonlar` insert (marker: `[salonappy-pkgsale:<group_id>]`)
- `is_group=true` ise her `group_items[]` ayrı `adisyon_hizmetler` (Ayşe Gürbüz örneği: Heykeltraş 4 seans + G5 10 seans + Bölgesel Yağ Yakma 4 seans = 12.300 TL)
- **Seans paketleri için APS placeholder YAZILMAZ.** Drklinik mantığı: visit/usage geldikçe `adisyon_paket_seanslar` INSERT (geldi=1, seans_tarih=usage.date). `AH.seans_sayisi` paket toplam seansını tutar; "kalan seans" = `seans_sayisi - mevcut APS sayısı`. "Gelmedi" gösterimi yok.
- **Alacak filtresi**: `receivable_amount > 0` VEYA (`remaining_payment > 0` AND `paid_amount > 0`). Hiç ödenmemiş paketler (paid=0, rec=0) alacak DEĞİL — Salonappy de göstermiyor (Hayal Kıran vb 18 örnek).
- **Vade tarihi**: `created_at`'in gün kısmı (Salonappy UI ile %100 uyum). Önder Yılmaz: date=2025-03-20 + payment_date=2025-04-21 olsa da UI 25.03.2025 gösteriyor — bu `created_at=2025-03-25` değerinden geliyor.
- Filtreyi geçen her paket için `taksitli_tahsilatlar` + `taksit_vadeleri` + `alacaklar` üçlüsü atomik yazılır (vade_sayisi=1). `adisyon_hizmetler.taksitli_tahsilat_id` set edilir. Hem "Alacaklar" sayfası hem "Satış Takibi" sayfası bunlara JOIN yaptığı için üçü beraber zorunlu.
- **UPSERT modu**: mevcut `[salonappy-pkgsale:gid]` markerlı adisyon varsa önce SİLİNİR (AH, APS, tahsilat, TH/TU, taksit, vade, alacak), sonra yeniden yazılır. Tekrar çalıştırmak idempotent.

**PASS2 (`--only-package-payments`)** ne yapar:

- DB'den `[salonappy-pkgsale:%]` markerlı paket adisyonlarını okur
- `pkgIndex` re-build **dump otoriter**: `client_name` ve `service_text` dump'taki `packageSales[]`'den çekilir (DB users.name ve hizmetler.hizmet_adi'nde Türkçe karakter / format farklılığı oluyordu — Sinem Soybek, Yasemin Şahin vb 16 ödeme eşleşmiyordu). `adId` + `userId` DB'den.
- Mevcut paket adisyonlarına bağlı tahsilatları siler (UPSERT)
- Dump `payments[]` listesinde `source_text="Paket satışı"` olanları paket adisyonlarına eşleştirir:
  - **Eşleşme kriteri** (öncelik sırası): (1) EXACT services match (payment.svc paket.svcNorms içinde tam eşleşme), (2) SUBSTRING match (birinin diğerini içermesi), (3) FALLBACK (müşterinin tüm paketleri). Dilzerin örneği: payment "Kaş Alma" → exact "Kaş Alma" paketi substring "Ölçülü Kaş Alma"dan önce seçilir.
  - **Aday içinde paket seçimi**: her sınıfta FIT olan (`paket.kalan = paket.total - paid_so_far >= payment.amount` — UI'da eksi göstermesin) adaylar önce; FIT içinden `payment.date`'e mutlak farkı en yakın paket seçilir. Bir sınıfta FIT yoksa alt sınıfa düşülür. Hiçbir sınıfta FIT yoksa **payment atlanır** (Gülcan Bikini paid=2000 tot=2000, ikinci 1000 TL payment'ı atlanır — Salonappy taraflı overpay).
  - Cennet örneği: payment 1750 TL date=2025-05-10, adaylar 2025-04-16 G5 (total=0 FIT değil) + 2025-05-10 G5+Heykeltraş (total=1750 FIT) → 10.05.2025 paketine atanır, eski 16.04 paketinde eksi gözükmez.
  - **`kalan > 0` filtresi YOK** — tamamen ödenmiş paketler (Gülcan Bikini paid=2000 tot=2000) de geçmiş tahsilat alabilir
  - **`payment.date >= paket.date` filtresi YOK** — Salonappy'de kaparo veya geriye dönük kayıt nedeniyle ödeme paketten önce de gözükebilir (Yıldız Acar 2025-05-01 payment, paket 2025-05-02)
  - **Services overlap fail fallback**: müşterinin DB'deki en eski paket adisyonuna bağlan (Nesrin Özmen payment svc=G5+Heykeltraş, paketleri Pedikür+Manikür — tutarsız Salonappy verisi için)
  - Çakışmada **en eski paket** önce seçilir
- Eşleşen pakete `tahsilatlar` insert + `tahsilat_hizmetler` (AH fiyat orantılı dağıtım — UI'da "adisyona bağlı" görünmesi için şart). **AH fiyat toplamı 0 ise** (Salonappy'de 87 fiyat=0 paket) **eşit pay** ile dağıtılır; aksi halde UI "Satış Takibi" `odenenTutar=SUM(tahsilat_hizmetler.tutar)` hesabı eksik gösterir.
- **Ödeme yöntemi** `payment_method_text` üzerinden: Nakit=1, Kart=2, Havale=3, Diğer=4
- Marker: `[salonappy-pay:<payment_id>]`
- `source_text="Adisyon"` olan tahsilatlar atlanır (visit pipeline'ı işleyecek)
- **UPSERT**: çalıştırışta önce paket adisyonlara bağlı tüm tahsilatları siler, sonra yeniden yazar — idempotent
- **Sonuç (dump 1780483152143 örneği)**: 379 paket tahsilatından 378'i yazıldı (1 atlanan = paketi dump'ta olmayan tek müşteri). Toplam ~933.325 TL.

#### Ürün satışları (Aşama A + B)

Paket akışıyla simetrik. Dump: [scripts/salonappy_dump_product_sales.js](../scripts/salonappy_dump_product_sales.js) — `/api/client/list` + `/api/product_sale/list_v2` + `/api/payment/list`.

```bash
# 1) Dump indir
# 2) PASS1 — ürün satışları + alacaklar (tahsilat YOK)
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_product_sales_<ts>.json \
    --salon=368 --only-product-sales

# 3) PASS2 — tahsilatları ürün adisyonlarına bağla
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_product_sales_<ts>.json \
    --salon=368 --only-product-payments
```

**PASS1 (`--only-product-sales`)**: her satış için `adisyonlar` + `adisyon_urunler` insert (marker `[salonappy-prodsale:<id>]`). Alacak filtresi paket akışıyla aynı (`receivable_amount > 0` VEYA kısmi ödenmiş). Vade = `created_at` gün kısmı. UPSERT.

**PASS2 (`--only-product-payments`)**: dump `payments[]`'tan `source_text="Ürün satışı"` olanları ürün adisyonlarına eşleştirir; aynı algoritma (exact > substring > fallback, her sınıfta FIT öncelik + en yakın tarih, FIT yoksa atla). Tahsilat insert + `tahsilat_urunler` (TU) dağıtımı (`fiyat*adet` orantılı; tFiyat=0 ise eşit). Marker `[salonappy-prod-pay:<payment_id>]`.

#### Visit / randevu (tarih aralıklı, peyderpey)

Visit aktarımı tüm sistemi sıfırlamadan tarih aralığında çalışır. Marker: `[salonappy-visit:<session>]` — sadece kendi aralığını UPSERT eder.

**Dump**: [scripts/salonappy_dump_visits.js](../scripts/salonappy_dump_visits.js) — `/client/list` + `/visit/list` (full) + her visit için `/booking/detail` + `/payment/list`. IndexedDB resume; binlerce visit'te yarım kalsa devam eder.

```bash
# 1) Tarayıcıda full dump indir (uzun süre — her visit ayrı detail)
# 2) Tarih aralıklı import (örn. tek bir gün)
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_visits_<ts>.json \
    --salon=368 --only-visits --from=2026-05-16 --to=2026-05-16

# Sadece ürün satışı içeren visit'leri test etmek için:
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_visits_<ts>.json \
    --salon=368 --only-visits --from=2026-05-01 --to=2026-05-31 --with-products

# Tarih aralığını sıfırla (hata olursa kolay temizlik):
/opt/php74/bin/php artisan salonappy:import \
    --salon=368 --reset-visits --from=2026-05-16 --to=2026-05-16
```

**Visit başına yapılanlar** (her session UPSERT):

> **Upcoming visit** (`details.is_past === false`) → **sadece randevu** + randevu_hizmetler yazılır, adisyon ve sonraki adımlar atlanır. Gelecek randevular için adisyon/tahsilat oluşmaz.

Geçmiş visit (`is_past=true`):
1. **Adisyon** insert (marker, salon+user+tarih)
2. **Adisyon hizmetleri** (services[]) — fiyat, personel, geldi=1 (showup="Geldi" ise)
3. **Randevu** + randevu_hizmetler (showup map: Geldi=1, Gelmedi=2, İptal=3)
4. **Ürün taşıma**: aynı tarih + aynı müşteri ile mevcut `[salonappy-prodsale:%]` adisyon(lar)ı varsa **tüm AU + tahsilat + alacak visit adisyonuna taşınır**, eski adisyon silinir (item-level kontrol yok — Salonappy aynı gün aynı müşteriye yapılmış ürün satışlarını visit'le ilişkili kabul eder). Ek olarak `bd.product_sales[]` içindeki ürünler visit adisyonuna yoksa eklenir (urun_id+adet+fiyat dedup).
5. **Paket kullanımı**: `package_usages[]` her usage için müşterinin `[salonappy-pkgsale:%]` adisyonlarındaki aynı hizmetin AH'lerini bul → kalan kapasiteye (`seans_sayisi - mevcut APS sayısı`) göre yeni APS **INSERT** (geldi=1, seans_tarih=usage.date). Placeholder yok; APS'lerin tümü gerçekleşen kullanımdır.
6. **Tahsilat**: `payments[]` her ödeme tahsilatlar + TH dağıt. Marker: `[salonappy-visit-pay:<pid>]`.
7. **Alacak**: `unpaid_amount > 0` → TaksitliTahsilatlar + TaksitVadeleri (vade=visit tarihi) + Alacaklar.

`--reset-visits --from --to`: tarih aralığındaki `[salonappy-visit:%]` markerlı tüm randevu+adisyon+tahsilat+taksit+alacak siler. Katalog dokunulmaz.

#### Gider / Masraf

**Dump**: [scripts/salonappy_dump_expenses.js](../scripts/salonappy_dump_expenses.js) — `/api/expense/list` (paginated). Cıktı: `salonappy_expenses_<ts>.json` (~50 KB).

```bash
/opt/php74/bin/php artisan salonappy:import \
    --dump-file=/tmp/salonappy_expenses_<ts>.json \
    --salon=368 --only-expenses
```

UPSERT: önce salondaki `[salonappy-expense:%]` markerlı tüm masraflar silinir, sonra dump'tan yeniden yazılır. `masraf_kategorisi` auto-create (`category_text` → `masraf_kategorisi_adi`). `harcayan_id` salon_personelleri.personel_adi LIKE match. İdempotent.

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

**Kaynak**: app.salonrandevu.com (web app) + REST API (api.salonrandevu.com). `SalonrandevuClient` servisi.

**Sinyal**: Login (email/şifre veya telefon) + Bearer token + `/company/*` endpoint'leri JSON döner. Cloudflare datacenter blok'u **YOK** — sunucu doğrudan API çağırır.

### Aktif modüler akış (önerilen)

Salonappy'deki gibi parça-parça aşamalı ilerleme. Tek seferlik full aktarım değil; her veri tipi ayrı flag, ayrı çalıştırma, ayrı rapor.

| Aşama | Flag | İşlev |
|---|---|---|
| 1a | `--only-package-sales` | Paket satışları (Paketler+PaketHizmetler+AdisyonPaketler+APS). Tahsilat YOK. |
| 1b | `--report-package-sales` | SR `/receipts/packets` vs DB AdisyonPaketler karşılaştırması |
| 2a | `--only-package-payments` | Paket fişi tahsilatları (Tahsilatlar+TahsilatPaketler). Adım 1 çalıştırılmış olmalı. |
| 2b | `--report-package-payments` | SR `paid` toplam vs DB Tahsilatlar toplam |
| 3a | `--only-other-receipts` | Paket-dışı receipt'ler (hizmet+ürün satışları + tahsilatları + dağılım). Tek flag, kompozit akış. |
| 3b | `--report-other-receipts` | Paket-dışı SR vs DB karşılaştırması (sayım+toplam+eksik liste) |
| 3+ | `--start-page=N`, `--max-page=M` | `--only-other-receipts` için partition/resume (büyük dataset, ~7000 receipt) |
| 4a | `--only-expenses` | Masraflar (`Masraflar` + `masraf_kategorileri` firstOrCreate). Kategori ad map `/expense/categories` JSON-decode. |
| 4b | `--report-expenses` | SR `total_expense` vs DB masraf toplamı |

### Endpoint haritası

```
GET /company/receipts/packets?page=N&order=0          — paket fişleri (paginated, next_page var)
GET /company/receipts/opened?page=N&ispaid=2&order=0  — paket-dışı receipt'ler (paginated, next_page var)
GET /company/receipt/{id}                             — receipt detayı (her aşamada)
GET /company/accounting/expenses?page=N&isbetween=true&start=Y-m-d&end=Y-m-d
                                                      — masraflar (paginated, next_page YOK; records<10 ise dur)
GET /company/expense/categories                       — kategori ad map (data.name JSON-encoded string)
```

### Receipt detay yapısı (kritik)

```json
{
  "receipt": {
    "id": 1554595,
    "customer": {...},
    "is_package": false,         // paket fişi mi
    "receipt_transactions": [    // hizmet satışları (paket içiyse receipt_package_id != 0)
      {"id", "service_id", "Service", "amount", "process_date",
       "receipt_package_id", "staffID", "is_paid"}
    ],
    "receipt_sales": [           // ürün satışları
      {"id", "stock_item_id", "stock_item{name}", "quantity",
       "amount", "staff_id", "sold_date"}
    ],
    "receipt_packages": [        // paket satışı master kayıtları
      {"id", "packet_id", "package_name", "amount",
       "paid_amount", "staff_id"}
    ],
    "receipt_payments": [        // ÖDEMELERİN HEPSİ BURADA
      {"id", "amount", "payment_type",  // 1=Nakit, 2=KK, 3=Havale, 4=Diğer, 5=Senet
       "payment_date", "s_id", "s_type", "description"}
    ]
  }
}
```

**`s_type` taksonomisi (tahsilat ne'ye bağlı)** — pro-rata gerekmez:

| s_type | Anlam | s_id referansı |
|---|---|---|
| **1** | Hizmet | `receipt_transactions[].id` |
| **2** | Paket | `receipt_packages[].id` |
| **3** | Ürün | `receipt_sales[].id` |

### Sistem tarafı tablo eşlemeleri

| SR alanı | DB tablo |
|---|---|
| `receipt_packages[]` | `Paketler` master + `PaketHizmetler` + `AdisyonPaketler` (paket satışı) |
| `receipt_transactions[]` (paket içi, `receipt_package_id != 0`) | `AdisyonPaketSeanslar` (1 tx = 1 APS, `geldi=NULL`=Bekleniyor) |
| `receipt_transactions[]` (paket dışı) | `AdisyonHizmetler` (`geldi=1`, `islem_tarihi=process_date`) |
| `receipt_sales[]` | `AdisyonUrunler` (`adet=quantity`, `fiyat=amount`) |
| `receipt_payments[]` | `Tahsilatlar` + `TahsilatPaketler` (s_type=2) / `TahsilatHizmetler` (s_type=1) / `TahsilatUrunler` (s_type=3) |

### Marker'lar

| Marker | Yer | Amaç |
|---|---|---|
| `[salonrandevu:RID]` | `adisyonlar.aciklama` (veya `notlar`/`adisyon_notu`) | Receipt → adisyon dedup |
| `[salonrandevu-paket:RID]` | `adisyonlar.aciklama` | Paket fişi etiketi (UI için) |
| `[SR-sale:saleId]` | `paketler.paket_adi` suffix | Paket master = receipt_packages[].id; tahsilat eşleme için |
| `[SR-payment:payId]` | `tahsilatlar.notlar` | Tahsilat UPSERT marker |
| `[salonrandevu-gider:id]` | `masraflar.notlar` | Masraf UPSERT marker (Adım 4) |

### Adım 1 — Paket satışları

```bash
# Önce reset (gerekirse)
/opt/php74/bin/php artisan salonrandevu:import --reset-salonrandevu --salon=195

# Paket satışlarını yaz
/opt/php74/bin/php artisan salonrandevu:import \
    --email=KULLANICI --password=SIFRE --salon=195 --only-package-sales

# Karşılaştırma raporu
/opt/php74/bin/php artisan salonrandevu:import \
    --email=KULLANICI --password=SIFRE --salon=195 --report-package-sales
```

**Akış (`importOneReceipt` `package_only=true`)**:

1. Markerlı eski adisyon ve tüm bağlı kayıtları sil (UPSERT)
2. `/receipt/{id}` detayı çek
3. `resolveUser` — inline müşteri yarat (`ApiController::aktarimMusteriKontrol`)
4. Adisyon insert + marker
5. Her `receipt_packages[]` için:
   - `Paketler` master oluştur (paket_adi suffix `[SR-sale:saleId]`)
   - `receipt_transactions` (recPkgId match'leyenler) → `PaketHizmetler` (her svc için seans+fiyat)
   - `AdisyonPaketler` insert (adisyon_id, paket_id, fiyat, `baslangic_tarihi=tarih`, `seans_araligi=7`, `seans_sayisi=total`, `personel_id`)
   - Her tx → 1 `AdisyonPaketSeanslar` (`geldi=NULL`=Bekleniyor, `seans_tarih=process_date`, `personel_id=tx.staffID`)

**Fallback'ler (3 adet)**:

- **Eski 2023 paket fişleri** (`receipt_packages=[]` boş ama paginated rec `is_package=true`): Paginated rec'in `info` ilk satırı paket adı, `all_amount` fiyat. Sentetik paket master + tx'leri pakete bağla. ~8 receipt için.
- **`receipt_package_id=0` tx + tek paketli receipt**: tx'leri o tek pakete bağla (SR "açık paket" mantığı). ~2 receipt için.
- **`pkg.amount=0` + tek paketli**: paginated rec.`all_amount` kullan. ~1-2 receipt için.

**Test sonucu (salon 195)**: SR=76 paket, DB=73 (eksik 3 = SR'de bedava bekleyen boş paket, anlamsız).

### Adım 2 — Paket tahsilatları

```bash
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --only-package-payments

/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-package-payments
```

**Akış (`importPaymentsForOneReceipt`)**:

1. `[salonrandevu:RID]` markerlı adisyon bul (yoksa SKIP+warn — Adım 1 çalıştırılmamış)
2. Eski `[SR-payment:%]` markerlı `Tahsilatlar` + TP/TH/TU sil (UPSERT)
3. `/receipt/{id}` detay çek
4. Adisyon altındaki `AdisyonPaketler` + `Paketler.paket_adi` JOIN → `[SR-sale:saleId]` markeri parse → `saleId → ap_id` map
5. Her `receipt_payments[]`:
   - `Tahsilatlar` insert (tutar, `odeme_yontemi_id=payment_type`, `odeme_tarihi=payment_date`, marker `[SR-payment:payId]`, `aciklama=description`)
   - Dağılım:
     - `s_id` saleIdToApId map'te varsa → direkt o `AdisyonPaketler.id`
     - yoksa + tek AP varsa → o AP
     - yoksa + çoklu AP → pro-rata fiyat oranıyla böl
   - `TahsilatPaketler` insert (tahsilat_id, adisyon_paket_id, tutar)

**Test sonucu (salon 195)**: SR ödenen 536152 TL, DB 536152 TL, fark **0 TL**, 117 tahsilat.

### Adım 3 — Paket-dışı receipt'ler (hizmet+ürün+tahsilat)

Tek flag (`--only-other-receipts`) ile kompozit akış. Salonappy gibi paket/ürün/visit ayrımına gerek yok — SR'de tek bir receipt'te hizmet ve ürün karışık olabilir.

```bash
# Büyük dataset — arka planda çalıştır (~7000 receipt × ~1sn = ~2 saat)
nohup /opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --only-other-receipts \
    > /var/www/.../tmp/sr_other.log 2>&1 &

# Yarıda kesildi → belirli sayfadan devam
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --only-other-receipts \
    --start-page=350

# Sayfa aralığı (batch'leme)
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --only-other-receipts \
    --start-page=1 --max-page=100

# Rapor
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-other-receipts
```

**Akış (`importOtherReceiptOne`)**:

1. UPSERT sil (markerlı adisyon + AH + AU + T + TP/TH/TU)
2. `/receipt/{id}` detay; `is_package=true` ise atla (Adım 1+2 işi)
3. `resolveUser` → adisyon + marker
4. `receipt_transactions[]` → `AdisyonHizmetler` (geldi=1, fiyat, personel `ensurePersonel`). `srTxId → ah.id` map.
5. `receipt_sales[]` → `AdisyonUrunler` (`adet=quantity`, ürün `ensureUrun`). `srSaleId → au.id` map.
6. `receipt_payments[]` → `Tahsilatlar` + dağılım:
   - `s_type=1`, `s_id` map'te → `TahsilatHizmetler`
   - `s_type=3`, `s_id` map'te → `TahsilatUrunler`
   - eşleşme yoksa → **pro-rata** AH+AU fiyat oranıyla (info log)

**Resume notu**: Her sayfa sonunda log'a `[Salonrandevu other] sayfa OK page=N toplam=M` yazılır. Yarım kalırsa son işlenmiş sayfayı + 1 ile `--start-page=N+1` çalıştır.

### Adım 4 — Masraflar (giderler)

```bash
# Tarih aralığı zorunlu (varsayılan 2020-01-01..bugün)
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --only-expenses \
    --from=2020-01-01 --to=2026-06-05

# Rapor
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-expenses \
    --from=2020-01-01 --to=2026-06-05
```

**Akış (`importExpensesOnly`)**:

1. **Kategori map yükle** — `/company/expense/categories` çek; `data.name` JSON-encoded string'i decode et (`custom_expense_N → ad`). Örnek: `custom_expense_2 → "Maaş"`, `custom_expense_9 → "mutfak"`, `custom_expense_19 → "siğorta pirimi"`. `null` değerler tanımsız (atlanır, kod string'i fallback).
2. **UPSERT sil** — salonun tüm `[salonrandevu-gider:%]` markerlı `Masraflar` satırlarını sil.
3. **Paginated fetch** — `/accounting/expenses?isbetween=true&start&end&page=N`. **`next_page` yok** — `count(records) < 10` olunca dur.
4. Her record için `Masraflar` insert:
   - `masraf_kategori_id` ← `ensureMasrafKategorisi($kategoriAdi)` (`masraf_kategorileri` GLOBAL tablo, kolon `kategori`, `firstOrCreate`)
   - `harcayan_id` ← `ensurePersonel(spender)`, boşsa salon default
   - `odeme_yontemi_id` ← SR `payment_type` map: `0 → 1` (Nakit), `1 → 2` (KK), `2 → 3` (Havale), diğer → `4` (Diğer)
   - `tarih` ← `transaction_date` gün kısmı
   - `aciklama` ← `description`
   - `tutar` ← `amount`
   - `notlar` ← marker `[salonrandevu-gider:id]`

**Field eşlemeleri**:

| SR | Sistem |
|---|---|
| `amount` | `Masraflar.tutar` |
| `transaction_date` | `Masraflar.tarih` |
| `description` | `Masraflar.aciklama` |
| `json_id` (`custom_expense_2`) | `masraf_kategorileri.kategori` (lookup `data.name`'den) |
| `payment_type` (0/1/2) | `odeme_yontemi_id` (1=Nakit, 2=KK, 3=Havale) |
| `spender` (string) | `harcayan_id` (`ensurePersonel` → Personel ID) |

**Test sonucu (salon 195, 2020-01-01..2026-06-05)**: SR 153 masraf 1083303 TL, DB 153 masraf 1083303 TL, **fark=0 TL**. API `total_expense` = `records` toplamı = DB toplamı.

### Sağlama / doğrulama komutları (tek panoda)

Her aşamadan sonra çalıştırılacak rapor komutları. Hepsi **sadece okur**, DB'ye dokunmaz.

```bash
# Adim 1 sonrasi — paket satislari
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-package-sales
# Cikti yapisi:
#   SR paket fis sayisi: 76
#   SR toplam tutar: 605616 TL (odenen: 536152 TL)
#   Bizdeki paket adisyon sayisi (AdisyonPaketler dolu): 73
#   Bizdeki toplam tutar: 601116 TL
#   DB'de EKSIK: 3 (toplam 0 TL) + eksik liste (ilk 30)
#   DB'de FAZLA: 0
# Yorum: eksik liste 0 TL ise bedava bekleyen bos paketler (anlamli).
# Eksik > 0 TL ise: Adim 1 yarim kaldi veya bir fallback eksik.

# Adim 2 sonrasi — paket tahsilatlari
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-package-payments
# Cikti yapisi:
#   SR paket fis odenen toplam: 536152 TL
#   Bizdeki SR paket tahsilat sayisi: 117
#   Bizdeki SR paket tahsilat toplam: 536152 TL
#   Fark (SR-DB): 0 TL
# Yorum: Fark=0 ise birebir tutuyor. Fark>0 ise: Adim 2 yarim kaldi,
# adisyon eksik (Adim 1 calistir), veya pkg.amount=0 fallback gerekiyor.

# Adim 4 sonrasi — masraflar
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-expenses \
    --from=2020-01-01 --to=2026-06-05
# Cikti yapisi:
#   SR masraf sayisi: 153
#   SR toplam (records): 1083303 TL
#   SR total_expense (API): 1083303 TL
#   Bizdeki SR masraf sayisi: 153
#   Bizdeki toplam tutar: 1083303 TL
#   Fark (SR-DB): 0 TL
# Yorum: total_expense (API toplami) records toplamiyla eslesmeli;
# DB toplam = SR toplam = fark 0.

# Adim 3 sonrasi — paket-disi receipt'ler (hizmet + urun + tahsilat)
/opt/php74/bin/php artisan salonrandevu:import \
    --email=X --password=Y --salon=195 --report-other-receipts
# Cikti yapisi:
#   SR paket-disi receipt sayisi: 6932
#   SR toplam tutar: 42298503 TL (odenen: 39621176 TL)
#   Bizdeki paket-disi receipt sayisi: 5800
#   Bizdeki hizmet+urun toplam: ... TL
#   Bizdeki tahsilat toplam: ... TL
#   EKSIK: N (toplam X TL) + eksik liste (ilk 30)
#   Ozet: SR=6932 DB=5800 eksik=1132 fark=...
# Yorum: 'eksik' tum SR'de var ama bizde yok. EKSIK'in cogu 0 TL ise
# bos receipt'lerdir. >0 TL ise: yarim kaldi (--start-page=N+1 devam et).
# Listenin tarihine bak — eski sayfalar islenmemis olabilir.
```

**Hizli sayim sorulari (artisan tinker)**:

```bash
/opt/php74/bin/php artisan tinker --execute="
echo 'Salonrandevu markerli adisyon: ' .
  DB::table('adisyonlar')->where('salon_id',195)
    ->where('aciklama','LIKE','%[salonrandevu:%')->count() . PHP_EOL;
echo 'Paket AdisyonPaketler: ' .
  DB::table('adisyon_paketler')->whereIn('adisyon_id',
    DB::table('adisyonlar')->where('salon_id',195)
      ->where('aciklama','LIKE','%[salonrandevu:%')->pluck('id'))->count() . PHP_EOL;
echo 'SR-payment markerli Tahsilatlar: ' .
  DB::table('tahsilatlar')->whereIn('adisyon_id',
    DB::table('adisyonlar')->where('salon_id',195)
      ->where('aciklama','LIKE','%[salonrandevu:%')->pluck('id'))
    ->where('notlar','LIKE','%[SR-payment:%')->count() . PHP_EOL;
"
```

**UI doğrulama (örnek)**: SR'de bir paketli müşteri (örn. Onur Hasan Acun) — yan panelde:
- Paket: `[SR-sale:5382378]` marker'lı, 16 seans Lazer (tüm vücut), 12000 TL
- Seanslar: 16 APS, `geldi=NULL` (Bekleniyor)
- Tahsilatlar: 1 × 12000 TL Kredi Kartı, paket'e bağlı (`TahsilatPaketler.adisyon_paket_id` set)
- "Satış Takibi"nde: paket fiyat=12000, ödenen=12000, kalan=0

### Salt-okunur / tanı modları

```bash
# Endpoint keşfi
/opt/php74/bin/php artisan salonrandevu:import --analyze            # Anasayfa+bundle.js
/opt/php74/bin/php artisan salonrandevu:import --email=X --password=Y --probe
/opt/php74/bin/php artisan salonrandevu:import --email=X --password=Y --inspect

# Reset
/opt/php74/bin/php artisan salonrandevu:import --salon=195 --reset-salonrandevu
/opt/php74/bin/php artisan salonrandevu:import --salon=195 --reset-all

# Proxy
/opt/php74/bin/php artisan salonrandevu:import --email=X --password=Y --salon=195 \
  --proxy=http://user:pass@host:port
```

### Eski/legacy aktarım (deprecated — modüler akışı tercih edin)

`--only=musteri,hizmet,personel,randevu,tahsilat,paket,urun,gider` ile tek-seferlik full akış mevcut (`SalonrandevuImporter.importPersonel()`, `importHizmetler()`, vs.) ama modüler akış (Adım 1+2+3) daha güvenli; her aşamada rapor ile doğrulama yapılır.

### Sunucu dump (debug için)

`SalonrandevuClient` her HTTP GET'i `storage/app/salonrandevu/<timestamp>/get_<safe>.body` olarak diske yazar. Sorunlu bir receipt'in tam JSON'unu görmek için:

```bash
LATEST=$(ls -t storage/app/salonrandevu/ | head -1)
cat storage/app/salonrandevu/$LATEST/get_company_receipt_1509321.body
```

### Bilinen yapısal farklar

- **Eski paket fişleri (2023 öncesi)** SR detay API'sinde `receipt_packages=[]` boş döner — paginated `/receipts/packets` listesinde paket olarak görünür. Fallback: paginated rec'ten sentetik paket master üret (`importOneReceipt` package_only modu).
- **"Açık paket"** durumu: SR'de paket master oluşturulmuş, içine seans atanmamış (tx'ler `receipt_package_id=0`). Tek paketli receipt'lerde tx'leri o pakete bağlamak fallback'i mevcut.
- **Bedava bekleyen boş paketler** (`tx_count=0, amount=0`): `bos hizmetler` warning'i ile atlanır (DB'de yer almaması doğru — anlamsız kayıt).
- **`payment_type` sistem `odeme_yontemleri.id` ile birebir uyumlu**: 1=Nakit, 2=KK, 3=Havale, 4=Diğer, 5=Senet. Mapping gereksiz.
- **`SalonrandevuClient.get` 6x backoff retry** yapar bağlantı koparsa.

### Sorun giderme

#### Adım 3 yarıda kesildi (SSH timeout, oturum düştü)

`--only-other-receipts` ~7000 receipt için ~2 saat sürer. SSH oturumu kapanırsa kesilir.

**Çözüm**: `nohup ... &` ile arka plana al, log'dan son işlenen sayfayı bul, `--start-page=N+1` ile devam. Marker UPSERT olduğu için aynı sayfadan başlatmak da güvenli (idempotent).

#### Paket fişi raporda eksik gözüküyor ama log'da `paket SAVE OK`

Rapor `AdisyonPaketler.fiyat > 0` filtresi uyguluyor. SR'de `pkg.amount=0` ise fallback ile `paginated rec.all_amount` kullanılır; o da 0 ise raporda eksik gözükür. UI'da gerçek paket var, sadece fiyat 0.

#### `[Salonrandevu] paket bos hizmetler` warning

Receipt detayında paket master var ama içine bağlı transaction yok (`tx_count=0`). SR'de bedava/iptal paket — DB'de yer almaması doğru, sessiz atlanır.

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
app/Imports/SalonappyImporter.php
app/Imports/SalonrandevuImporter.php
app/Http/Controllers/DrklinikApiController.php   # REST API endpoint'leri
scripts/salonappy_dump_v7.js                     # full visit-bazli dump
scripts/salonappy_dump_v6.js                     # eski versiyon
scripts/salonappy_dump_package_sales.js          # modular: sadece paket satışları
scripts/salonappy_dump_product_sales.js          # modular: sadece ürün satışları
scripts/salonappy_dump_visits.js                 # modular: visit detayları + ödemeler
scripts/salonappy_dump_expenses.js               # modular: giderler
scripts/salonappy_scraper_resilient.py           # Python Selenium fallback (deprecated)
scripts/drklinik.py                              # Python Selenium scraper (referans implementasyon)
```

---

## Salonappy + Salonrandevu modular akış karşılaştırması

| Konsept | Salonappy | Salonrandevu |
|---|---|---|
| Veri kaynağı | Tarayıcıdan JSON dump (Cloudflare blok) | Sunucu doğrudan API çağırır |
| Modüler giriş | `--dump-file=...` + flag (`--only-package-sales`, `--only-visits`, vb.) | Login + flag (`--only-package-sales`, `--only-other-receipts`, vb.) |
| Paket satış akışı | Dump'tan `package_sale/list_v2` | `/receipts/packets` paginated |
| Tahsilat-paket bağı | Dump `source_text="Paket satışı"` + exact/substring service match (heuristic) | SR `s_type=2 + s_id` (deterministik, pro-rata gerekmez) |
| Tahsilat-hizmet bağı | Visit içi (`bd.payments[]`) | SR `s_type=1 + s_id` |
| Tahsilat-ürün bağı | Visit içi / product sale payment | SR `s_type=3 + s_id` |
| Paket sevkiyat (APS) | `package_usages[]` visit içinde insert | `receipt_transactions` paket içi = APS (`geldi=NULL`) |
| Resume | IndexedDB (dump) | `--start-page=N` (import) |
| Rapor | (manuel sayım/grep) | `--report-package-sales/payments/other-receipts` |
