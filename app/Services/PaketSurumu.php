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

        return DB::transaction(function () use ($paketId, $eski) {
            $kopyaId = self::kopyaAc($paketId, $eski);

            // Toplu guncelleme: model olayi calismaz, satisin kendi sure/limit bilgisi korunur.
            DB::table('adisyon_paketler')->where('paket_id', $paketId)->update(['paket_id' => $kopyaId]);

            return $kopyaId;
        });
    }

    /**
     * Satista secilen hizmetlere gore satilacak paket: liste katalogla ayniysa katalog paketi,
     * farkliysa o satisa ozel pasif kopya. Katalog paketi degismez.
     *
     * @param  array|null  $secilenHizmetler  bos/null ise katalog listesi
     * @return int  adisyon_paketler.paket_id olarak yazilacak id
     */
    public static function satisPaketi($paketId, $secilenHizmetler, $salonId)
    {
        $secilen = array_values(array_unique(array_filter(array_map('intval', (array) $secilenHizmetler))));
        if (!$secilen) return (int) $paketId;

        $katalog = DB::table('paket_hizmetler')->where('paket_id', $paketId)->pluck('hizmet_id')->toArray();
        if (self::ayniHizmetler($katalog, $secilen)) return (int) $paketId;

        // Yeni eklenen hizmet bu salonun olmali.
        $ekler = array_diff($secilen, array_map('intval', $katalog));
        if ($ekler) {
            $gecerli = DB::table('salon_sunulan_hizmetler')->where('salon_id', $salonId)
                ->whereIn('hizmet_id', $ekler)->distinct()->count('hizmet_id');
            if ($gecerli !== count($ekler)) abort(422, 'Seçilen hizmet bu işletmeye ait değil.');
        }

        return DB::transaction(function () use ($paketId, $secilen) {
            return self::kopyaAc($paketId, $secilen);
        });
    }

    /** Pasif paket kopyasi: katalogdaki hizmetin seans/fiyati korunur, yeni hizmet bos eklenir. Prim oranlari da kopyalanir. */
    private static function kopyaAc($paketId, array $hizmetIdler)
    {
        $paket = (array) DB::table('paketler')->where('id', $paketId)->first();
        unset($paket['id']);
        $paket['aktif'] = 0;
        $kopyaId = DB::table('paketler')->insertGetId($paket);

        $mevcut = DB::table('paket_hizmetler')->where('paket_id', $paketId)->get()->keyBy('hizmet_id');
        foreach (array_unique(array_map('intval', $hizmetIdler)) as $hizmetId) {
            $eski = $mevcut->get($hizmetId);
            $satir = new \App\PaketHizmetler();
            $satir->paket_id = $kopyaId;
            $satir->hizmet_id = $hizmetId;
            $satir->seans = $eski->seans ?? null;
            $satir->fiyat = $eski->fiyat ?? null;
            $satir->save();
        }

        if (Schema::hasTable('personel_prim_oranlari')) {
            foreach (DB::table('personel_prim_oranlari')->where('tur', 'paket')->where('kalem_id', $paketId)->get() as $o) {
                $satir = (array) $o;
                unset($satir['id']);
                $satir['kalem_id'] = $kopyaId;
                DB::table('personel_prim_oranlari')->insert($satir);
            }
        }

        return $kopyaId;
    }

    /** Personel prim formu pasif paketleri gostermez; kaydedince oranlari silinmesin. */
    public static function pasifPaketIdler($salonId): array
    {
        return DB::table('paketler')->where('salon_id', $salonId)->where('aktif', 0)->pluck('id')->toArray();
    }
}
