<?php

namespace App\Services;

use App\PersonelYetkiAyari;
use App\PersonelYetkiSabitleri;
use App\Personeller;
use App\IsletmeYetkilileri;
use Illuminate\Support\Facades\DB;

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
     * Yetki kontrolu.
     *
     * @param int|string $personelId  salon_personelleri.id
     * @param int|string $salonId
     * @param string     $key         Ornek: 'musteri.telefon_gor'
     */
    public static function yetkiVar($personelId, $salonId, string $key): bool
    {
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
            // Default: personel_sade
            $ayarlar = PersonelYetkiSabitleri::sablonAyarlari('personel_sade');
        }

        // Anahtar tabloda yoksa default sade'den al
        if (!array_key_exists($key, $ayarlar)) {
            $sadeAyarlar = PersonelYetkiSabitleri::sablonAyarlari('personel_sade');
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

        // Yetkili'nin baglandigi personel_id (varsa)
        $personelId = IsletmeYetkilileri::where('id', $yetkiliId)->value('personel_id');
        if (!$personelId) {
            // Personel kaydi yoksa salon sahibidir veya direk yetkilidir → tam yetki
            return true;
        }
        return self::yetkiVar($personelId, $salonId, $key);
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
        $ayar = PersonelYetkiAyari::where('personel_id', $personelId)
            ->where('salon_id', $salonId)
            ->first();

        if ($ayar) {
            $kayitliAyarlar = is_array($ayar->ayarlar) ? $ayar->ayarlar : [];
            // Eksik anahtarlari sade sablondan tamamla
            $sade = PersonelYetkiSabitleri::sablonAyarlari('personel_sade');
            return [
                'sablon' => $ayar->sablon ?: 'ozel',
                'ayarlar' => array_merge($sade, $kayitliAyarlar),
            ];
        }
        // Hic kayit yoksa → personel_sade default
        return [
            'sablon' => 'personel_sade',
            'ayarlar' => PersonelYetkiSabitleri::sablonAyarlari('personel_sade'),
        ];
    }

    /**
     * Ayarlari kaydet. $sablon 'ozel' ise UI'dan gelen ayarlar kullanilir.
     * Onceden tanimli sablon ('sekreter' vb) gelirse o sablonun ayarlari + UI override.
     */
    public static function ayarlariKaydet($personelId, $salonId, string $sablon, array $ayarlar): PersonelYetkiAyari
    {
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
