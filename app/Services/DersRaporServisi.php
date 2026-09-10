<?php

namespace App\Services;

use App\Personeller;
use Illuminate\Support\Facades\DB;

/**
 * Grup dersi raporlama — ozet + egitmen bazli + ders bazli metrikler.
 * Web (StoreAdminController) ve mobil API (ApiController) ORTAK kaynagi.
 *
 * Tanimlar:
 *  - "katilim" (doluluk) = iptal ve bekleme DISI katilimci (rezerve+geldi+gelmedi).
 *  - doluluk% = katilim / toplam_kapasite.
 *  - noshow% = gelmedi / (gelen+gelmedi).
 */
class DersRaporServisi
{
    public static function ozet($salonId, $tarih1, $tarih2): array
    {
        $oturumlar = DB::table('ders_oturumlari')
            ->where('salon_id', $salonId)
            ->where('aktif', true)
            ->where(function ($q) { $q->whereNull('iptal')->orWhere('iptal', 0); })
            ->whereBetween('tarih', [$tarih1, $tarih2])
            ->get(['id', 'kapasite', 'personel_id', 'ders_tipi']);

        $oturumIds = $oturumlar->pluck('id')->all();
        $katByOturum = collect();
        if (!empty($oturumIds)) {
            $katByOturum = DB::table('ders_katilimcilar')
                ->whereIn('oturum_id', $oturumIds)
                ->where('durum', '!=', 'iptal')
                ->select('oturum_id', 'durum')
                ->get()->groupBy('oturum_id');
        }

        $personelAd = Personeller::where('salon_id', $salonId)->pluck('personel_adi', 'id');

        $ozet = ['oturum' => 0, 'kapasite' => 0, 'katilim' => 0, 'gelen' => 0, 'gelmedi' => 0, 'bekleme' => 0];
        $egitmen = []; // personel_id => metrik
        $ders = [];    // ders_tipi => metrik

        foreach ($oturumlar as $o) {
            $kats = $katByOturum->get($o->id, collect());
            $aktif = $kats->whereNotIn('durum', ['bekleme'])->count(); // iptal zaten disarida
            $gelen = $kats->where('durum', 'geldi')->count();
            $gelmedi = $kats->where('durum', 'gelmedi')->count();
            $bekleme = $kats->where('durum', 'bekleme')->count();
            $kap = (int) $o->kapasite;

            $ozet['oturum']++;
            $ozet['kapasite'] += $kap;
            $ozet['katilim'] += $aktif;
            $ozet['gelen'] += $gelen;
            $ozet['gelmedi'] += $gelmedi;
            $ozet['bekleme'] += $bekleme;

            $pid = $o->personel_id ?: 0;
            if (!isset($egitmen[$pid])) {
                $egitmen[$pid] = ['personel' => $personelAd->get($pid) ?? '—', 'oturum' => 0, 'kapasite' => 0, 'katilim' => 0, 'gelen' => 0, 'gelmedi' => 0];
            }
            $egitmen[$pid]['oturum']++;
            $egitmen[$pid]['kapasite'] += $kap;
            $egitmen[$pid]['katilim'] += $aktif;
            $egitmen[$pid]['gelen'] += $gelen;
            $egitmen[$pid]['gelmedi'] += $gelmedi;

            $dt = $o->ders_tipi ?: 'Grup Dersi';
            if (!isset($ders[$dt])) {
                $ders[$dt] = ['ders_tipi' => $dt, 'oturum' => 0, 'kapasite' => 0, 'katilim' => 0, 'gelen' => 0];
            }
            $ders[$dt]['oturum']++;
            $ders[$dt]['kapasite'] += $kap;
            $ders[$dt]['katilim'] += $aktif;
            $ders[$dt]['gelen'] += $gelen;
        }

        $yuzde = function ($pay, $payda) {
            return $payda > 0 ? round($pay / $payda * 100) : 0;
        };
        $ozet['doluluk'] = $yuzde($ozet['katilim'], $ozet['kapasite']);
        $ozet['noshow'] = $yuzde($ozet['gelmedi'], $ozet['gelen'] + $ozet['gelmedi']);

        // Egitmen/ders dizilerine doluluk% ekle + sirala (katilim desc)
        $egitmenListe = array_map(function ($e) use ($yuzde) {
            $e['doluluk'] = $yuzde($e['katilim'], $e['kapasite']);
            return $e;
        }, array_values($egitmen));
        usort($egitmenListe, function ($a, $b) { return $b['katilim'] <=> $a['katilim']; });

        $dersListe = array_map(function ($d) use ($yuzde) {
            $d['doluluk'] = $yuzde($d['katilim'], $d['kapasite']);
            return $d;
        }, array_values($ders));
        usort($dersListe, function ($a, $b) { return $b['katilim'] <=> $a['katilim']; });

        return ['ozet' => $ozet, 'egitmen' => $egitmenListe, 'ders' => $dersListe];
    }
}
