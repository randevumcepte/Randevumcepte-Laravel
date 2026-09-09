<?php

namespace App\Services;

use App\AdisyonPaketSeanslar;
use App\DersKatilimci;
use App\DersOturumu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Grup dersi (Pilates/kurs) paket/seans dusumu.
 *
 * Mevcut randevu seans dusum mantiginin (StoreAdminController FIFO aday secimi +
 * adisyon_paket_seanslar) grup dersine uyarlanmasidir. Kalanin sistemde her yerde
 * TUTARLI dusmesi icin ayni adisyon_paket_seanslar tablosuna yazilir.
 *
 * Randevu mantigi: dusum "Geldi" isaretlenince olur (APS satiri geldi=true),
 * "gelmedi"/geri-alinca satir silinir (hak iade). Paketler hizmet bazli oldugu
 * icin oturumun hizmet_id'si sart; yoksa dusum yapilmaz (sadece katilim takibi).
 */
class DersSeansServisi
{
    /**
     * Musterinin bu hizmet icin kullanilabilir aktif paket/hizmet hakki var mi?
     * (Online rezervasyonda "sadece hakki olanlar" kontrolu icin.)
     */
    public static function hakVarMi($salonId, $userId, $hizmetId): bool
    {
        if (!$salonId || !$userId || !$hizmetId) return false;
        return self::adaySec($salonId, $userId, $hizmetId) !== null;
    }

    /**
     * "Geldi" aninda cagrilir: FIFO aday paket/hizmet secip APS satiri (geldi=true)
     * olusturur, id'yi ders_katilimcilar.aps_id'ye yazar. Zaten dusulduyse tekrar
     * dusmez. Donus: olusan APS id veya null (hak yok / hizmet_id yok).
     */
    public static function dusumYap(DersOturumu $oturum, DersKatilimci $katilimci): ?int
    {
        if ($katilimci->hak_dusuldu && $katilimci->aps_id) return $katilimci->aps_id;
        $hizmetId = (int) $oturum->hizmet_id;
        if ($hizmetId <= 0) return null; // hizmet bagli degil -> dusum yok

        $aday = self::adaySec($oturum->salon_id, $katilimci->user_id, $hizmetId);
        if (!$aday) return null;

        try {
            $aps = new AdisyonPaketSeanslar();
            $aps->seans_tarih = $oturum->tarih;
            $aps->seans_saat  = $oturum->saat;
            $aps->personel_id = $oturum->personel_id;
            $aps->randevu_id  = null;                 // grup dersi — randevuya bagli degil
            $aps->hizmet_id   = $hizmetId;
            $aps->seans_no    = ((int) ($aday->kullanilan_seans ?? 0)) + ((int) ($aday->kullanilmayan_seans ?? 0)) + 1;
            if ($aday->tur === 'hizmet') {
                $aps->adisyon_hizmet_id = $aday->id;
            } else {
                $aps->adisyon_paket_id = $aday->id;
            }
            $aps->geldi = true;
            $aps->dusulen_miktar = 1;
            $aps->save();

            $katilimci->aps_id = $aps->id;
            $katilimci->hak_dusuldu = true;
            $katilimci->save();
            return $aps->id;
        } catch (\Throwable $e) {
            Log::warning('[DERS-SEANS] dusum hata: ' . $e->getMessage(), [
                'oturum_id' => $oturum->id, 'user_id' => $katilimci->user_id, 'hizmet_id' => $hizmetId,
            ]);
            return null;
        }
    }

    /**
     * Dusumu geri al (gelmedi/rezerve'ye donunce): olusturulan APS satirini sil,
     * bayraklari temizle. Hak sisteme geri doner (COUNT/SUM duser).
     */
    public static function dusumGeriAl(DersKatilimci $katilimci): void
    {
        try {
            if ($katilimci->aps_id) {
                AdisyonPaketSeanslar::where('id', $katilimci->aps_id)->delete();
            }
        } catch (\Throwable $e) {
            Log::warning('[DERS-SEANS] geri-al hata: ' . $e->getMessage(), ['katilimci_id' => $katilimci->id]);
        }
        $katilimci->aps_id = null;
        $katilimci->hak_dusuldu = false;
        $katilimci->save();
    }

    /**
     * Hizmete ait, kalani olan en eski (FIFO) adisyon_hizmet veya adisyon_paket adayi.
     * Mevcut StoreAdminController seciminin (6904-6965) birebir uyarlamasi.
     * Donus: {tur:'hizmet'|'paket', id, kullanilan_seans, kullanilmayan_seans, adisyon_tarih} | null
     */
    private static function adaySec($salonId, $userId, $hizmetId)
    {
        // 1) Tek hizmet satisi (adisyon_hizmetler)
        $hizmetAdaylar = DB::table('adisyon_hizmetler')
            ->join('adisyonlar', 'adisyon_hizmetler.adisyon_id', '=', 'adisyonlar.id')
            ->where('adisyon_hizmetler.hizmet_id', $hizmetId)
            ->where('adisyonlar.user_id', $userId)
            ->where('adisyonlar.salon_id', $salonId)
            ->where('adisyon_hizmetler.seans_sayisi', '>', 0)
            ->where(function ($q) {
                $q->whereNull('adisyon_hizmetler.otomatik_randevu_olusturuldu')
                  ->orWhere('adisyon_hizmetler.otomatik_randevu_olusturuldu', '!=', 1);
            })
            ->orderBy('adisyonlar.tarih', 'asc')
            ->select('adisyon_hizmetler.id', 'adisyon_hizmetler.seans_sayisi',
                     'adisyon_hizmetler.kullanilan_seans', 'adisyon_hizmetler.kullanilmayan_seans',
                     'adisyonlar.tarih as adisyon_tarih')
            ->get();
        $hizmetSecili = null;
        foreach ($hizmetAdaylar as $h) {
            $kul = (int) DB::table('adisyon_paket_seanslar')->where('adisyon_hizmet_id', $h->id)->count();
            if ($kul < (int) $h->seans_sayisi) { $hizmetSecili = $h; break; }
        }

        // 2) Paket (adisyon_paketler + paket_hizmetler)
        $paketAdaylar = DB::table('adisyon_paketler')
            ->join('adisyonlar', 'adisyon_paketler.adisyon_id', '=', 'adisyonlar.id')
            ->join('paket_hizmetler', 'paket_hizmetler.paket_id', '=', 'adisyon_paketler.paket_id')
            ->where('paket_hizmetler.hizmet_id', $hizmetId)
            ->where('adisyonlar.user_id', $userId)
            ->where('adisyonlar.salon_id', $salonId)
            ->where('adisyon_paketler.seans_sayisi', '>', 0)
            ->where(function ($q) {
                $q->whereNull('adisyon_paketler.otomatik_randevu_olusturuldu')
                  ->orWhere('adisyon_paketler.otomatik_randevu_olusturuldu', '!=', 1);
            })
            ->orderBy('adisyonlar.tarih', 'asc')
            ->select('adisyon_paketler.id', 'adisyon_paketler.seans_sayisi',
                     'adisyon_paketler.kullanilan_seans', 'adisyon_paketler.kullanilmayan_seans',
                     'adisyonlar.tarih as adisyon_tarih')
            ->distinct()
            ->get();
        $paketSecili = null;
        foreach ($paketAdaylar as $p) {
            $kul = (int) DB::table('adisyon_paket_seanslar')
                ->where('adisyon_paket_id', $p->id)->where('hizmet_id', $hizmetId)->count();
            if ($kul < (int) $p->seans_sayisi) { $paketSecili = $p; break; }
        }

        // 3) Ikisi de varsa eski tarihli adisyon kazanir (FIFO)
        if ($hizmetSecili && $paketSecili) {
            if (date('Y-m-d', strtotime($hizmetSecili->adisyon_tarih)) < date('Y-m-d', strtotime($paketSecili->adisyon_tarih))) {
                return (object) ['tur' => 'hizmet', 'id' => $hizmetSecili->id, 'kullanilan_seans' => $hizmetSecili->kullanilan_seans, 'kullanilmayan_seans' => $hizmetSecili->kullanilmayan_seans, 'adisyon_tarih' => $hizmetSecili->adisyon_tarih];
            }
            return (object) ['tur' => 'paket', 'id' => $paketSecili->id, 'kullanilan_seans' => $paketSecili->kullanilan_seans, 'kullanilmayan_seans' => $paketSecili->kullanilmayan_seans, 'adisyon_tarih' => $paketSecili->adisyon_tarih];
        }
        if ($hizmetSecili) return (object) ['tur' => 'hizmet', 'id' => $hizmetSecili->id, 'kullanilan_seans' => $hizmetSecili->kullanilan_seans, 'kullanilmayan_seans' => $hizmetSecili->kullanilmayan_seans, 'adisyon_tarih' => $hizmetSecili->adisyon_tarih];
        if ($paketSecili) return (object) ['tur' => 'paket', 'id' => $paketSecili->id, 'kullanilan_seans' => $paketSecili->kullanilan_seans, 'kullanilmayan_seans' => $paketSecili->kullanilmayan_seans, 'adisyon_tarih' => $paketSecili->adisyon_tarih];
        return null;
    }
}
