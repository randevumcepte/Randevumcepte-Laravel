<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satilmis paketin hizmetleri, katalog duzenlenince DEGISMEMELI.
 * v2 karsiligi: New-Laravel App\Services\Paket\PaketSurumu (iki repoda ayni kural).
 *
 * Satilan paket yalniz paket_id tutar, hizmetleri paket_hizmetler'den okunur. Hizmet listesi
 * degisince eski satislar pasif (aktif=0) bir kopyaya tasinir. Katalog paketinin id'si AYNI kalir:
 * kampanya, on gorusme ve acik ekranlar etkilenmez, satislarin okuma kodu degismez.
 */
class PaketSurumu
{
    public static function ayniHizmetler(array $a, array $b): bool
    {
        $duzenle = function (array $x) {
            $x = array_values(array_unique(array_map('intval', $x)));
            sort($x);
            return $x;
        };
        return $duzenle($a) === $duzenle($b);
    }

    /**
     * Katalog paketi guncellenmeden ONCE cagrilir (eski fiyat/seans/hizmetler kopyalansin).
     * Hizmet listesi ayniysa ya da paket hic satilmadiysa hicbir sey yapmaz.
     *
     * @return int|null olusan pasif kopyanin id'si
     */
    public static function satilmislariDondur($paketId, array $yeniHizmetIdler)
    {
        $eski = DB::table('paket_hizmetler')->where('paket_id', $paketId)->pluck('hizmet_id')->toArray();
        if (self::ayniHizmetler($eski, $yeniHizmetIdler)) return null;
        if (!DB::table('adisyon_paketler')->where('paket_id', $paketId)->exists()) return null;

        return DB::transaction(function () use ($paketId) {
            $paket = (array) DB::table('paketler')->where('id', $paketId)->first();
            unset($paket['id']);
            $paket['aktif'] = 0;
            $kopyaId = DB::table('paketler')->insertGetId($paket);

            foreach (DB::table('paket_hizmetler')->where('paket_id', $paketId)->get() as $h) {
                $satir = (array) $h;
                unset($satir['id']);
                $satir['paket_id'] = $kopyaId;
                DB::table('paket_hizmetler')->insert($satir);
            }

            // Eski satislarin detayli primi degismesin.
            if (Schema::hasTable('personel_prim_oranlari')) {
                foreach (DB::table('personel_prim_oranlari')->where('tur', 'paket')->where('kalem_id', $paketId)->get() as $o) {
                    $satir = (array) $o;
                    unset($satir['id']);
                    $satir['kalem_id'] = $kopyaId;
                    DB::table('personel_prim_oranlari')->insert($satir);
                }
            }

            // Toplu guncelleme: model olayi calismaz, satisin kendi sure/limit bilgisi korunur.
            DB::table('adisyon_paketler')->where('paket_id', $paketId)->update(['paket_id' => $kopyaId]);

            return $kopyaId;
        });
    }

    /** Personel prim formu pasif paketleri gostermez; kaydedince oranlari silinmesin. */
    public static function pasifPaketIdler($salonId): array
    {
        return DB::table('paketler')->where('salon_id', $salonId)->where('aktif', 0)->pluck('id')->toArray();
    }
}
