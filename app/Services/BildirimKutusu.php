<?php

namespace App\Services;

use App\Bildirimler;
use App\Personeller;
use App\SalonYonetim\Audit;
use App\Salonlar;

/**
 * Mobil "Bildirimler" ekranindaki silme. Herkes yalniz kendi kutusunu siler: musteri kutusu
 * Bildirimler::musteriKutusu, personel kutusu o salondaki kendi personel kayitlaridir.
 */
class BildirimKutusu
{
    /** Musterinin gordugu liste (bildirimgetirmusteri ile ayni kapsam) */
    public static function musteri($userId, $salonId)
    {
        $q = Bildirimler::musteriKutusu($userId);
        $bundle = trim((string) Salonlar::where('id', $salonId)->value('app_bundle'));
        if ($bundle === '' || $bundle === MusteriProfil::ORTAK_BUNDLE) return $q->where('salon_id', $salonId);
        $salonIds = Salonlar::where('app_bundle', $bundle)->pluck('id')->toArray();
        return $q->where(function ($w) use ($salonIds) {
            $w->whereIn('salon_id', $salonIds)->orWhereNull('salon_id');
        });
    }

    public static function personel($yetkiliId, $salonId)
    {
        return Bildirimler::where('salon_id', $salonId)
            ->whereIn('personel_id', Personeller::where('salon_id', $salonId)->where('yetkili_id', $yetkiliId)->pluck('id')->toArray());
    }

    /** kapsam: 'secili' (idler), 'okunmus', 'tumu'. $sonId: ekran acikken gelen yeni bildirim toplu silmede kalir. */
    public static function sil($q, $kapsam, array $idler = [], $sonId = 0)
    {
        if ($kapsam === 'secili') {
            $idler = array_values(array_filter(array_map('intval', $idler)));
            if (!$idler) return null;
            $q->whereIn('id', array_slice($idler, 0, 500));
        } elseif ($kapsam === 'okunmus' || $kapsam === 'tumu') {
            if ($kapsam === 'okunmus') $q->where('okundu', 1);
            if ($sonId > 0) $q->where('id', '<=', $sonId);
        } else {
            return null;
        }
        return $q->delete();
    }

    public static function logla($salonId, $request, $tur, $kisiId, $kisiAd, $kapsam, $adet)
    {
        if (!$adet) return;
        $ne = ['secili' => $adet . ' bildirimini', 'okunmus' => 'okunmuş ' . $adet . ' bildirimini', 'tumu' => 'tüm bildirimlerini (' . $adet . ')'][$kapsam];
        Audit::logApi($salonId, $request, 'bildirim_sil', $tur, $kisiId, $kisiAd, $kisiAd . ' kendi ' . $ne . ' sildi');
    }
}
