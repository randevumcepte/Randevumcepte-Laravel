<?php

namespace App\Services;

use App\Http\Controllers\StoreAdminController;
use App\Personeller;
use App\Salonlar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * On Gorusme Takvimi renkleri = randevu takviminin renkleri.
 * Kural sirasi StoreAdminController::randevuyukle ile ayni: durum rengi > tek renk > renk sirasi > kaynak rengi.
 */
class OnGorusmeTakvimRenk
{
    const VARSAYILAN = '#3a87ad'; // renksiz kaynak: FullCalendar varsayilani

    /** @return \Closure ($rh: RandevuHizmetler) => ['kat'=>, 'renk'=>, 'yazi'=>] */
    public static function cozucu($salonId): \Closure
    {
        $turu = (int) (Salonlar::where('id', $salonId)->value('randevu_takvim_turu') ?? 1);
        list($kat, $cihaz, $oda) = StoreAdminController::takvimRenkMaplariKur($salonId, $turu);
        list($tekRenk, $sira, $yazi) = self::ayarlar($salonId);
        $siraMap = $sira ? self::siraHaritasi($salonId, $turu, $sira, $yazi) : [];

        $durumZemin = RandevuDurumRenk::zeminler($salonId);
        $durumYazi = RandevuDurumRenk::yazilar($salonId);

        return function ($rh) use ($turu, $kat, $cihaz, $oda, $tekRenk, $sira, $yazi, $siraMap, $durumZemin, $durumYazi) {
            $durum = self::kategori($rh);
            $durumAd = RandevuDurumRenk::durum($rh, $durumZemin);
            $renk = StoreAdminController::takvimRandevuRengi($rh, $turu, $kat, $cihaz, $oda);
            if ($durumAd !== null) {
                // Durum renkli: yazi beyaz (takvimde varsayilan da beyaz), isletme yazi sectiyse o
                return ['kat' => $durum, 'renk' => $renk ?: self::VARSAYILAN, 'yazi' => $durumYazi[$durumAd] ?? '#ffffff'];
            }
            if ($tekRenk !== null) $renk = $tekRenk;
            $yaziRenk = $yazi !== null ? $yazi : '#ffffff';
            if ($sira) {
                $kaynak = $turu === 1 ? $rh->personel_id : ($turu === 2 ? $rh->cihaz_id : ($turu === 3 ? $rh->oda_id
                    : ($rh->hizmetler ? $rh->hizmetler->hizmet_kategori_id : 0)));
                if (isset($siraMap[(string) $kaynak])) {
                    $renk = $siraMap[(string) $kaynak]['bg'];
                    $yaziRenk = $siraMap[(string) $kaynak]['text'];
                } else {
                    $yaziRenk = '#ffffff';
                }
            }
            return ['kat' => $durum, 'renk' => $renk ?: self::VARSAYILAN, 'yazi' => $yaziRenk];
        };
    }

    /**
     * Personel sutun basliklari = randevu takvimi kolon basligi: tek renk > renk sirasi > personel rengi;
     * yazi: cift rengi > takvim yazi rengi > beyaz.
     * @return array [personel_id => ['bg'=>, 'yazi'=>]]
     */
    public static function basliklar($salonId, array $idler): array
    {
        $turu = (int) (Salonlar::where('id', $salonId)->value('randevu_takvim_turu') ?? 1);
        list($tekRenk, $sira, $yazi) = self::ayarlar($salonId);
        $siraMap = $sira && $turu === 1 ? self::siraHaritasi($salonId, $turu, $sira, $yazi) : [];
        $kendi = DB::table('salon_personelleri')->join('renk_duzenleri', 'salon_personelleri.renk', '=', 'renk_duzenleri.id')
            ->whereIn('salon_personelleri.id', $idler)->pluck('renk_duzenleri.renk', 'salon_personelleri.id')->all();

        $sonuc = [];
        foreach (array_values($idler) as $i => $id) {
            $cift = ['bg' => ($kendi[$id] ?? null) ?: self::VARSAYILAN, 'text' => null];
            if ($tekRenk !== null) $cift['bg'] = $tekRenk;
            elseif (isset($siraMap[(string) $id])) $cift = $siraMap[(string) $id];
            elseif ($sira && $turu !== 1) $cift = $sira[$i % count($sira)]; // takvim kolonu personel degil: gorunum sirasi
            elseif ($sira) $cift['text'] = '#ffffff'; // takvimde kolonu olmayan personel: kendi rengi, beyaz yazi
            $sonuc[$id] = ['bg' => $cift['bg'], 'yazi' => $cift['text'] ?? ($yazi ?? '#ffffff')];
        }
        return $sonuc;
    }

    private static function kategori($rh): string
    {
        $r = $rh->randevu;
        $geldi = $r && $r->randevuya_geldi !== null ? (int) $r->randevuya_geldi : ($rh->seansa_geldi !== null ? (int) $rh->seansa_geldi : null);
        if ($geldi === 1) return 'geldi';
        if ($geldi === 0) return 'gelmedi';
        return $r && (int) $r->durum === 1 && (int) $r->randevuya_gelecek === 1 ? 'gelecek' : 'bekliyor';
    }

    /** [tekRenk|null, renkSirasi|null, yaziRengi|null] */
    private static function ayarlar($salonId): array
    {
        $hex = function ($v) { return is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v); };
        $kolonlar = array_values(array_filter(
            ['takvim_tek_renk_aktif', 'takvim_tek_renk', 'takvim_renk_sirasi', 'takvim_yazi_rengi_aktif', 'takvim_yazi_rengi'],
            function ($k) { return Schema::hasColumn('salonlar', $k); }
        ));
        $s = $kolonlar ? DB::table('salonlar')->where('id', $salonId)->first($kolonlar) : null;
        if (!$s) return [null, null, null];

        $tek = !empty($s->takvim_tek_renk_aktif) && !empty($s->takvim_tek_renk) ? $s->takvim_tek_renk : null;
        $yazi = !empty($s->takvim_yazi_rengi_aktif) && $hex($s->takvim_yazi_rengi ?? null) ? $s->takvim_yazi_rengi : null;
        $sira = [];
        if ($tek === null && !empty($s->takvim_renk_sirasi)) {
            foreach ((array) json_decode($s->takvim_renk_sirasi, true) as $e) {
                if (is_array($e) && $hex($e['bg'] ?? null)) $sira[] = ['bg' => $e['bg'], 'text' => $hex($e['text'] ?? null) ? $e['text'] : null];
                elseif ($hex($e)) $sira[] = ['bg' => $e, 'text' => null];
            }
        }
        return [$tek, $sira ?: null, $yazi];
    }

    /** Takvim kolonlari gorunum sirasina gore renk listesinden doner. */
    private static function siraHaritasi($salonId, $turu, array $sira, $yazi): array
    {
        if ($turu === 2) {
            $idler = DB::table('cihazlar')->join('salon_cihaz_renkleri', 'salon_cihaz_renkleri.cihaz_id', '=', 'cihazlar.id')
                ->join('renk_duzenleri', 'salon_cihaz_renkleri.renk_id', '=', 'renk_duzenleri.id')
                ->where('cihazlar.salon_id', $salonId)->where('cihazlar.aktifmi', true)->orderBy('takvim_sirasi', 'asc')->pluck('cihazlar.id');
        } elseif ($turu === 3) {
            $idler = DB::table('odalar')->join('salon_oda_renkleri', 'salon_oda_renkleri.oda_id', '=', 'odalar.id')
                ->join('renk_duzenleri', 'salon_oda_renkleri.renk_id', '=', 'renk_duzenleri.id')
                ->where('odalar.salon_id', $salonId)->where('odalar.aktifmi', true)->orderBy('takvim_sirasi', 'asc')->pluck('odalar.id');
        } elseif ($turu === 1) {
            $idler = Personeller::join('renk_duzenleri', 'salon_personelleri.renk', '=', 'renk_duzenleri.id')
                ->where('salon_personelleri.salon_id', $salonId)->where('salon_personelleri.aktif', true)
                ->where('salon_personelleri.takvimde_gorunsun', true)->orderBy('salon_personelleri.takvim_sirasi', 'asc')
                ->pluck('salon_personelleri.id');
        } else {
            $idler = collect(); // kategori modu: on gorusmenin kategorisi yok
        }
        $harita = [];
        foreach ($idler->values() as $i => $id) {
            $cift = $sira[$i % count($sira)];
            $harita[(string) $id] = ['bg' => $cift['bg'], 'text' => $cift['text'] ?? ($yazi ?? '#ffffff')];
        }
        return $harita;
    }
}
