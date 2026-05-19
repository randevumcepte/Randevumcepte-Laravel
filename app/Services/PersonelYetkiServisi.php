<?php

namespace App\Services;

use App\PersonelYetkiAyari;
use App\PersonelYetkiSabitleri;
use App\Personeller;
use App\IsletmeYetkilileri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Personel yetki kontrol merkezi.
 *
 * Kullanim:
 *   if (!PersonelYetkiServisi::yetkiVar($personelId, $salonId, 'musteri.telefon_gor')) {
 *       // numara maskele
 *   }
 *
 * Kural:
 *   1. Personel rolu olmayan (Hesap Sahibi / Yonetici / Supervisor / Sekreter
 *      / Sanat Yonetmeni / Sosyal Medya Uzmani) → her zaman TRUE doner.
 *   2. Personel rolundeyse → personel_yetki_ayarlari tablosuna bakar.
 *   3. Tablo bos / kayit yoksa → 'personel_sade' sablonu uygulanir.
 */
class PersonelYetkiServisi
{
    /**
     * Tablo varligi kontrolu. Migration sunucuda calismadiysa runtime'da yarat.
     * Bu defansif onlem; normal akista migration yeterli olur. Ilk yetki
     * kayit/oku denemesinde tetiklenir.
     */
    private static $_tabloHazir = false;
    public static function tabloyuHazirla(): void
    {
        if (self::$_tabloHazir) return;
        // Raw SQL kullaniyoruz cunku JSON column tipi eski MySQL'de problem yapabilir;
        // TEXT + Eloquent cast 'array' ile ayni isi gorur.
        try {
            $exists = false;
            try {
                $exists = Schema::hasTable('personel_yetki_ayarlari');
            } catch (\Exception $e) {
                // Schema::hasTable bazi sunuculurda hata atabilir; raw kontrol yapalim
                $rows = DB::select("SHOW TABLES LIKE 'personel_yetki_ayarlari'");
                $exists = !empty($rows);
            }
            if (!$exists) {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS personel_yetki_ayarlari (
                        id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        personel_id BIGINT UNSIGNED NOT NULL,
                        salon_id BIGINT UNSIGNED NOT NULL,
                        sablon VARCHAR(32) NOT NULL DEFAULT 'personel_sade',
                        ayarlar LONGTEXT NULL,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        UNIQUE KEY pya_unique_per_sub (personel_id, salon_id),
                        KEY pya_salon_idx (salon_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                \Log::info('PersonelYetkiServisi: personel_yetki_ayarlari tablosu runtime olusturuldu.');
            }
            self::$_tabloHazir = true;
        } catch (\Exception $e) {
            \Log::warning('PersonelYetkiServisi tabloyuHazirla HATA: ' . $e->getMessage());
            // Exception'i bastirma — yukari fırlat ki controller catch'inde mesaj gozuksun
            throw $e;
        }
    }

    /**
     * Yetki kontrolu.
     *
     * @param int|string $personelId  salon_personelleri.id
     * @param int|string $salonId
     * @param string     $key         Ornek: 'musteri.telefon_gor'
     */
    public static function yetkiVar($personelId, $salonId, string $key): bool
    {
        self::tabloyuHazirla();
        if (!$personelId || !$salonId) return true; // bilinmiyorsa engelleme

        // 1. Personel rolu degilse → tam yetki
        if (!self::personelRolundeMi($personelId, $salonId)) {
            return true;
        }

        // 2. Yetki ayar kaydi
        $ayar = PersonelYetkiAyari::where('personel_id', $personelId)
            ->where('salon_id', $salonId)
            ->first();

        $ayarlar = [];
        if ($ayar && is_array($ayar->ayarlar)) {
            $ayarlar = $ayar->ayarlar;
        } else {
            // Default: personel (sade)
            $ayarlar = PersonelYetkiSabitleri::sablonAyarlari('personel');
        }

        // Anahtar tabloda yoksa default sade'den al
        if (!array_key_exists($key, $ayarlar)) {
            $sadeAyarlar = PersonelYetkiSabitleri::sablonAyarlari('personel');
            return (bool)($sadeAyarlar[$key] ?? false);
        }

        return (bool)$ayarlar[$key];
    }

    /**
     * IsletmeYetkilileri.id (giris yapan kullanici) icin yetki kontrolu.
     * Yetkili'nin personel_id'sini bulup ana yetkiVar()'a yonlendirir.
     */
    public static function yetkiliYetkiVar($yetkiliId, $salonId, string $key): bool
    {
        if (!$yetkiliId || !$salonId) return true;

        // Yetkili'nin baglandigi personel_id (Personeller.yetkili_id uzerinden,
        // belirli salon icin). Tersine arama: IsletmeYetkilileri tablosunda
        // personel_id alani yok — bag Personeller tarafindadir.
        $personelId = \App\Personeller::where('yetkili_id', $yetkiliId)
            ->where('salon_id', $salonId)
            ->value('id');
        if (!$personelId) {
            // Personel kaydi yoksa salon sahibidir veya direk yetkilidir → tam yetki
            return true;
        }
        return self::yetkiVar($personelId, $salonId, $key);
    }

    /**
     * Giris yapan kullanici "Personel rolunde" mi VE bu yetki KAPALI mi?
     * Yani: bu kontrol "personeli kisitla" anlamina gelir.
     *
     * Kullanim (asagidaki gibi mevcut personelmi() cagrilari yerine):
     *   eski: if (StoreAdminController::personelmi($request)) { kendi randevulari }
     *   yeni: if (PersonelYetkiServisi::kisitla($request, 'randevu.tum_personel_gor')) { kendi randevulari }
     *
     * Cevap:
     *   - Salon sahibi / Personel rolu degil → false (kisitlama yok)
     *   - Personel rolu + yetki acik → false (yetki gore izinli, kisitlama yok)
     *   - Personel rolu + yetki kapali → true (kisitla, eski personelmi() davranisi)
     */
    public static function kisitla($request, string $bypassYetki): bool
    {
        $userSatis = \Auth::guard('satisortakligi')->user();
        $userIsletme = \Auth::guard('isletmeyonetim')->user();
        $user = $userIsletme ?: $userSatis;
        if (!$user) return false;

        // Salon ID'yi request'ten al
        $salonId = $request->sube ?? $request->salon_id ?? null;
        if (!$salonId) {
            // mevcutsube() helper'ini kullanmak istemiyoruz cunku circular dependency
            // olabilir. Mevcut akista request->sube veya salon_id zaten geliyor.
            return false;
        }

        // 1. Personel rolunde mi (role_id 5)?
        $personelRolunde = DB::table('model_has_roles')
            ->where('role_id', 5)
            ->where('model_id', $user->id)
            ->where('salon_id', $salonId)
            ->exists();

        if (!$personelRolunde) {
            return false; // Personel rolu degil → kisitlama yok
        }

        // 2. Yetki kontrolu — yetki varsa kisitlama yok, yoksa kisitla
        return !self::yetkiliYetkiVar($user->id, $salonId, $bypassYetki);
    }

    /**
     * API tarafi (mobile) icin: isletmeyonetim-api guard'inda yetki kontrolu.
     * Mobile request'lerinde Auth bu guard'a duser.
     */
    public static function kisitlaApi($request, string $bypassYetki): bool
    {
        $user = \Auth::guard('isletmeyonetim-api')->user();
        if (!$user) return false;
        $salonId = $request->sube ?? $request->salon_id ?? null;
        if (!$salonId) return false;
        $personelRolunde = DB::table('model_has_roles')
            ->where('role_id', 5)
            ->where('model_id', $user->id)
            ->where('salon_id', $salonId)
            ->exists();
        if (!$personelRolunde) return false;
        return !self::yetkiliYetkiVar($user->id, $salonId, $bypassYetki);
    }

    /**
     * Mobile/API icin "bu kullaniciyi filtrele" personel_id'sini dondur.
     *
     * - null donerse: kullanici Personel rolu degil VEYA bypass yetkisi var
     *   → kisitlama yok, tum kayitlari gosterebilir
     * - int donerse: yetkisi yok → sadece bu personel_id'ye ait kayitlar
     *
     * Ornek kullanim:
     *   $kisitla = PersonelYetkiServisi::apiKisitlamaPersonelId(
     *       $request, $salonid, 'musteri.tum_portfoy_gor'
     *   );
     *   $query->when($kisitla, fn($q) => $q->where('personel_id', $kisitla));
     */
    public static function apiKisitlamaPersonelId(
        $request,
        $salonId,
        string $bypassYetki,
        string $guard = 'isletmeyonetim-api'
    ) {
        $user = \Auth::guard($guard)->user();
        if (!$user || !$salonId) return null;
        $personelRolunde = DB::table('model_has_roles')
            ->where('role_id', 5)
            ->where('model_id', $user->id)
            ->where('salon_id', $salonId)
            ->exists();
        if (!$personelRolunde) return null;
        if (self::yetkiliYetkiVar($user->id, $salonId, $bypassYetki)) return null;
        return Personeller::where('yetkili_id', $user->id)
            ->where('salon_id', $salonId)
            ->value('id');
    }

    /**
     * Kullanici belirli salonda "Hesap Sahibi" rolune mi sahip?
     * Roles tablosunda name='Hesap Sahibi' ile model_has_roles join.
     */
    public static function isHesapSahibi($yetkiliId, $salonId): bool
    {
        if (!$yetkiliId || !$salonId) return false;
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Hesap Sahibi')
            ->where('model_has_roles.model_id', $yetkiliId)
            ->where('model_has_roles.salon_id', $salonId)
            ->exists();
    }

    /**
     * Auth user'in belirli salondaki personel_id'si (varsa).
     * Musteri portfoy olusturulurken "olusturan_personel_id" alanini
     * doldurmak icin kullanilir. Salon sahibi/yonetici icin null doner.
     */
    public static function authPersonelId($salonId, $guard = 'isletmeyonetim'): ?int
    {
        $user = \Auth::guard($guard)->user();
        if (!$user) $user = \Auth::guard('isletmeyonetim-api')->user();
        if (!$user || !$salonId) return null;
        $pid = Personeller::where('yetkili_id', $user->id)
            ->where('salon_id', $salonId)
            ->value('id');
        return $pid ? (int)$pid : null;
    }

    /**
     * Personelin "kendi portfoyu"ndaki user_id listesi.
     *
     * Tanim: a) musteri_portfoy.olusturan_personel_id = $personelId
     *        b) Bu salondaki adisyon_hizmetler/urunler/paketler tablolarinda
     *           personel_id = $personelId olan tum musteri user_id'leri
     *
     * Yetki sistemi: 'musteri.tum_portfoy_gor' yetkisi yoksa kullanici sadece
     * bu listeyi gormeli. Cagriyi sadece personel rolu + yetki yoksa yap.
     *
     * Donus: distinct user_id array (int[])
     */
    public static function kendiPortfoyUserIds($salonId, $personelId): array
    {
        if (!$salonId || !$personelId) return [];

        // a) Personelin olusturdugu portfoyler
        $olusturdugu = \App\MusteriPortfoy::where('salon_id', $salonId)
            ->where('olusturan_personel_id', $personelId)
            ->pluck('user_id')
            ->all();

        // b) Hizmet/urun/paket satislarinda yer aldiklari adisyonlarin musterileri
        $satistakiAdisyonIds = DB::table('adisyon_hizmetler')
            ->where('personel_id', $personelId)
            ->pluck('adisyon_id');
        $satistakiAdisyonIds = $satistakiAdisyonIds->merge(
            DB::table('adisyon_urunler')->where('personel_id', $personelId)->pluck('adisyon_id')
        );
        $satistakiAdisyonIds = $satistakiAdisyonIds->merge(
            DB::table('adisyon_paketler')->where('personel_id', $personelId)->pluck('adisyon_id')
        );
        $satistakiAdisyonIds = $satistakiAdisyonIds->unique()->values()->all();

        $satistaki = [];
        if (!empty($satistakiAdisyonIds)) {
            $satistaki = DB::table('adisyonlar')
                ->where('salon_id', $salonId)
                ->whereIn('id', $satistakiAdisyonIds)
                ->pluck('user_id')
                ->all();
        }

        $birlesik = array_unique(array_merge($olusturdugu, $satistaki));
        return array_values(array_filter($birlesik, fn($v) => $v !== null && $v !== ''));
    }

    /**
     * Yetkili kullanici icin: 'musteri.tum_portfoy_gor' yetkisi yoksa
     * kendi portfoyundeki user_id listesi, varsa NULL (kisitlama yok).
     *
     * Kullanim:
     *   $kisitla = PersonelYetkiServisi::musteriPortfoyKisitlamasi($request, $salonid);
     *   $q->when($kisitla, fn($q) => $q->whereIn('user_id', $kisitla));
     */
    public static function musteriPortfoyKisitlamasi($request, $salonId, $guard = 'isletmeyonetim-api'): ?array
    {
        $user = \Auth::guard($guard)->user();
        if (!$user) $user = \Auth::guard('isletmeyonetim')->user();
        if (!$user || !$salonId) return null;
        $personelRolunde = DB::table('model_has_roles')
            ->where('role_id', 5)
            ->where('model_id', $user->id)
            ->where('salon_id', $salonId)
            ->exists();
        if (!$personelRolunde) return null;
        if (self::yetkiliYetkiVar($user->id, $salonId, 'musteri.tum_portfoy_gor')) return null;
        $personelId = Personeller::where('yetkili_id', $user->id)
            ->where('salon_id', $salonId)
            ->value('id');
        if (!$personelId) return null;
        return self::kendiPortfoyUserIds($salonId, $personelId);
    }

    /**
     * Personel rolunde mi? (model_has_roles.role_id == 5)
     */
    public static function personelRolundeMi($personelId, $salonId): bool
    {
        $yetkiliId = Personeller::where('id', $personelId)
            ->where('salon_id', $salonId)
            ->value('yetkili_id');
        if (!$yetkiliId) return false;

        return DB::table('model_has_roles')
            ->where('role_id', 5) // Personel rolu
            ->where('model_id', $yetkiliId)
            ->where('salon_id', $salonId)
            ->exists();
    }

    /**
     * Personelin tum yetki ayarlarini dondur (yoksa default).
     * Frontend bunu cekip UI'da gosterir.
     */
    public static function ayarlariGetir($personelId, $salonId): array
    {
        self::tabloyuHazirla();
        $ayar = PersonelYetkiAyari::where('personel_id', $personelId)
            ->where('salon_id', $salonId)
            ->first();

        if ($ayar) {
            $kayitliAyarlar = is_array($ayar->ayarlar) ? $ayar->ayarlar : [];
            // Backward compat: eski sablon key'lerini yeniye map et
            $sablon = $ayar->sablon ?: 'ozel';
            $eskidenYeniye = [
                'personel_sade' => 'personel',
                'personel_tam'  => 'yonetici',
                'demo'          => 'personel',
            ];
            if (isset($eskidenYeniye[$sablon])) {
                $sablon = $eskidenYeniye[$sablon];
            }
            // Eksik anahtarlari SECILI sablondan tamamla (sade'den DEGIL).
            // Sema'ya yeni yetki eklendiginde, personel hangi sablonda
            // kaydedildiyse o sablonun default'unu alir. Bilinmeyen sablon
            // (ornegin 'ozel') varsa 'personel' default'una duser.
            $sablonDefault = PersonelYetkiSabitleri::sablonAyarlari($sablon);
            return [
                'sablon' => $sablon,
                'ayarlar' => array_merge($sablonDefault, $kayitliAyarlar),
            ];
        }
        // Hic kayit yoksa → personel default
        return [
            'sablon' => 'personel',
            'ayarlar' => PersonelYetkiSabitleri::sablonAyarlari('personel'),
        ];
    }

    /**
     * Ayarlari kaydet. $sablon 'ozel' ise UI'dan gelen ayarlar kullanilir.
     * Onceden tanimli sablon ('sekreter' vb) gelirse o sablonun ayarlari + UI override.
     */
    public static function ayarlariKaydet($personelId, $salonId, string $sablon, array $ayarlar): PersonelYetkiAyari
    {
        self::tabloyuHazirla();
        // Bilinmeyen sablon → ozel olarak isaretle
        $bilinen = array_keys(PersonelYetkiSabitleri::sablonlar());
        if (!in_array($sablon, $bilinen) && $sablon !== 'ozel') {
            $sablon = 'ozel';
        }

        // Sadece tanimli anahtarlari kabul et (XSS / kotu key engelleme)
        $izinli = PersonelYetkiSabitleri::tumAnahtarlar();
        $temizAyarlar = [];
        foreach ($izinli as $key) {
            if (array_key_exists($key, $ayarlar)) {
                $temizAyarlar[$key] = (bool)$ayarlar[$key];
            }
        }

        return PersonelYetkiAyari::updateOrCreate(
            ['personel_id' => $personelId, 'salon_id' => $salonId],
            ['sablon' => $sablon, 'ayarlar' => $temizAyarlar]
        );
    }
}
