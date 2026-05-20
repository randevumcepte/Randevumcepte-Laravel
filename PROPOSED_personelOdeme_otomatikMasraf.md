# PROPOSED: Personel Ödemesi → Otomatik Masraf Kaydı

Salon sahibi personel sayfasından maaş/prim/avans/bonus ödediğinde sistem
**otomatik olarak Masraflar tablosuna da bir kayıt** atsın. Aynı işin iki kere
girilmemesi için. Silindiğinde de bağlı Masraflar kaydı otomatik silinsin.

**Henüz canlıya inmedi — kullanıcı onayı sonrası entegre edilecek.**

## 1. Migration: masraflar tablosuna kaynak iz kolonu

`database/migrations/2026_05_10_000001_add_personel_maas_odemesi_id_to_masraflar.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonelMaasOdemesiIdToMasraflar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('masraflar') && !Schema::hasColumn('masraflar', 'personel_maas_odemesi_id')) {
            Schema::table('masraflar', function (Blueprint $table) {
                $table->unsignedInteger('personel_maas_odemesi_id')->nullable()->after('salon_id');
                $table->index('personel_maas_odemesi_id', 'masraflar_pmo_idx');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('masraflar') && Schema::hasColumn('masraflar', 'personel_maas_odemesi_id')) {
            Schema::table('masraflar', function (Blueprint $table) {
                $table->dropIndex('masraflar_pmo_idx');
                $table->dropColumn('personel_maas_odemesi_id');
            });
        }
    }
}
```

> Bu kolon, Masraflar kaydının hangi PersonelMaasOdemesi'nden geldiğini takip eder.
> NULL = manuel girilmiş masraf (eski kayıt veya kira/elektrik/sarf vb).
> Dolu = otomatik personel ödemesinden geldi.

## 2. StoreAdminController.php — `primOde()` metodunu güncelle

Şu an `primOde()` (~satır 24878-24920) sadece PersonelMaasOdemesi yaratıyor.
Aşağıdaki şekilde **otomatik Masraflar kaydı** ekle:

### Mevcut kod (24904-24914):

```php
PersonelMaasOdemesi::create([
    'personel_id'        => $personel->id,
    'salon_id'           => $salonId,
    'donem'              => $donem,
    'tutar'              => $tutar,
    'odeme_tipi'         => $odemeTipi,
    'odeme_tarihi'       => $odemeTarihi,
    'odeme_yontemi'      => mb_substr((string)$request->odeme_yontemi, 0, 60),
    'aciklama'           => mb_substr((string)$request->aciklama, 0, 300),
    'ekleyen_yetkili_id' => Auth::guard('isletmeyonetim')->user()->id ?? null,
]);
```

### Yeni kod:

```php
$pmo = PersonelMaasOdemesi::create([
    'personel_id'        => $personel->id,
    'salon_id'           => $salonId,
    'donem'              => $donem,
    'tutar'              => $tutar,
    'odeme_tipi'         => $odemeTipi,
    'odeme_tarihi'       => $odemeTarihi,
    'odeme_yontemi'      => mb_substr((string)$request->odeme_yontemi, 0, 60),
    'aciklama'           => mb_substr((string)$request->aciklama, 0, 300),
    'ekleyen_yetkili_id' => Auth::guard('isletmeyonetim')->user()->id ?? null,
]);

// === OTOMATIK KASA/MASRAF KAYDI ===
// Personel ödemesi yapılınca kasaya gider olarak da yazılır.
// Salon sahibi iki yere ayrı ayrı girmesin.
$kategoriAdi = 'Personel Ödemeleri'; // standart kategori
$kategori = MasrafKategorisi::firstOrCreate(
    ['salon_id' => $salonId, 'kategori_adi' => $kategoriAdi],
    ['kategori_adi' => $kategoriAdi, 'salon_id' => $salonId]
);

$masraf = new Masraflar();
$masraf->personel_maas_odemesi_id = $pmo->id;
$masraf->salon_id        = $salonId;
$masraf->harcayan_id     = $personel->id;
$masraf->masraf_kategori_id = $kategori->id;
$masraf->tarih           = $odemeTarihi;
$masraf->tutar           = $tutar;
$masraf->odeme_yontemi_id = (int)($request->odeme_yontemi_id ?? 1);
$masraf->notlar          = "Personel: {$personel->personel_adi} — "
    . ucfirst($odemeTipi) . " ($donem) "
    . ($request->aciklama ? "• " . mb_substr($request->aciklama, 0, 100) : '');
$masraf->save();
```

> ℹ️ `MasrafKategorisi::firstOrCreate(...)` kategori salon'da yoksa ilk seferinde
> otomatik oluşturur. Sonraki ödemelerde aynı kategoriyi kullanır.

> ℹ️ `MasrafKategorisi` modelinin `salon_id` ve `kategori_adi` alanları olduğunu
> varsayar. Eğer kolon ismi farklıysa (örn. `masraf_kategori_adi`) ayarla.

> ⚠️ `use App\MasrafKategorisi;` import'u dosyanın üstünde olmalı (zaten var olabilir).

## 3. StoreAdminController.php — `primOdemeSil()` metodunu güncelle

Mevcut (satır 25126-25139):

```php
public function primOdemeSil(Request $request)
{
    try{
        $salonId = self::mevcutsube($request);
        $kayit = PersonelMaasOdemesi::where('id',$request->id)->where('salon_id',$salonId)->first();
        if(!$kayit){
            return response()->json(['basarili'=>false,'mesaj'=>'Ödeme kaydı bulunamadı.']);
        }
        $kayit->delete();
        return response()->json(['basarili'=>true]);
    } catch(\Exception $e){
        return response()->json(['basarili'=>false,'mesaj'=>$e->getMessage()]);
    }
}
```

### Yeni hali (silinmeden önce bağlı Masraflar kaydını da sil):

```php
public function primOdemeSil(Request $request)
{
    try{
        $salonId = self::mevcutsube($request);
        $kayit = PersonelMaasOdemesi::where('id',$request->id)->where('salon_id',$salonId)->first();
        if(!$kayit){
            return response()->json(['basarili'=>false,'mesaj'=>'Ödeme kaydı bulunamadı.']);
        }

        // Bağlı kasa/masraf kaydını da sil (varsa)
        Masraflar::where('personel_maas_odemesi_id', $kayit->id)
            ->where('salon_id', $salonId)
            ->delete();

        $kayit->delete();
        return response()->json(['basarili'=>true]);
    } catch(\Exception $e){
        return response()->json(['basarili'=>false,'mesaj'=>$e->getMessage()]);
    }
}
```

## 4. (Opsiyonel) Geçmiş veri temizleme komutu

Eski salonların `Masraflar` tablosunda manuel girilmiş "Personel" kategorili
kayıtları olabilir. İstersen ayrı bir Artisan komutu yazabilirim:

```
php artisan dashboard:cleanup-personel-masraflar --salon=ID --donem=2026-05
```

Bu komut, belirli salonun belirli dönem'inde:
- `personel_maas_odemeleri` ve `masraflar` arasındaki ÖNCEKİ manuel kayıtları
- `notlar` veya `aciklama` alanında eşleştirir
- Eşleşmeyenleri rapor eder, eşleşenleri otomatik bağlar

Şimdilik **gerekmiyor** — yeni ödemeler otomatik bağlanır, eski kayıtlar
manual olarak duruyor (zaten kasada görünüyor, sayım doğru).

## 5. Sonuç

✅ Salon sahibi sadece **Personel Yönetimi** sayfasından ödeme yapar
✅ Sistem otomatik:
   - `personel_maas_odemeleri` → personel raporu için
   - `masraflar` → kasa/maliyet raporu için
✅ Silme işleminde her ikisi de senkron silinir
✅ Mevcut tüm kasa/dashboard raporları çalışmaya devam eder (Masraflar SUM yeterli)
✅ Çift sayım yok, çift giriş yok

## 6. Deploy adımları

```bash
cd Randevumcepte-Laravel

# 1. Migration dosyasını oluştur (yukarıdaki içerikle)
# Dosya: database/migrations/2026_05_10_000001_add_personel_maas_odemesi_id_to_masraflar.php

# 2. StoreAdminController.php iki metodunu güncelle
#    - primOde() → otomatik Masraflar oluştur
#    - primOdemeSil() → bağlı Masraflar'ı da sil

# 3. Test (lokal)
php artisan migrate

# 4. Deploy
git add -A
git commit -m "Personel ödemesi yapıldığında otomatik kasa/masraf kaydı"
git push
./deploy.sh
```

## 7. Test senaryosu

1. **Yeni ödeme**: Personel sayfasından 1.000₺ maaş öde
   - `personel_maas_odemeleri` tablosunda 1 kayıt → ✓
   - `masraflar` tablosunda 1 kayıt (`personel_maas_odemesi_id` dolu, kategori "Personel Ödemeleri") → ✓
   - Kasa raporunda görünür → ✓
   - Personel maaş raporunda görünür → ✓
2. **Silme**: Aynı ödemeyi sil
   - Her iki kayıt da silinir → ✓
3. **Eski kayıt** (manuel girilmiş Masraflar):
   - `personel_maas_odemesi_id = NULL`
   - Etkilenmez, dururumda kalır → ✓
