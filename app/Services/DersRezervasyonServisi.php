<?php

namespace App\Services;

use App\DersKatilimci;
use App\DersOturumu;
use Illuminate\Support\Facades\DB;

/**
 * Grup dersine ONLINE rezervasyon (mobil API + mini-site ortak backend).
 *
 * "Sadece hakki olanlar" kurali: rezervasyon icin musterinin dersin hizmetine ait
 * kullanilabilir paket/seans hakki (DersSeansServisi::hakVarMi) olmali. Hak kontrolu
 * ONLINE'da sart; salon panelinden ekleme (StoreAdminController) hak sartsizdir.
 *
 * Paket DUSUMU burada YAPILMAZ — dusum "Geldi" isaretlenince (DersSeansServisi::dusumYap).
 * Online rezervasyon sadece kontenjan tutar (dolu ise bekleme).
 */
class DersRezervasyonServisi
{
    /**
     * @return array ['durum'=>'ok'|'hata', 'mesaj'=>..., 'katilimci_durum'=>'rezerve'|'bekleme'?]
     */
    public static function rezervasyonYap($salonId, $oturumId, $userId, bool $hakZorunlu = true): array
    {
        $oturum = DersOturumu::where('salon_id', $salonId)->where('aktif', true)->find($oturumId);
        if (!$oturum || $oturum->iptal) {
            return ['durum' => 'hata', 'mesaj' => 'Ders bulunamadı veya iptal edilmiş.'];
        }
        if (strtotime($oturum->tarih . ' ' . $oturum->saat) < time()) {
            return ['durum' => 'hata', 'mesaj' => 'Geçmiş bir derse rezervasyon yapılamaz.'];
        }

        $mevcut = DersKatilimci::where('oturum_id', $oturum->id)
            ->where('user_id', $userId)->whereNotIn('durum', ['iptal'])->first();
        if ($mevcut) {
            return ['durum' => 'hata', 'mesaj' => 'Bu derse zaten kayıtlısınız.'];
        }

        if ($hakZorunlu) {
            if (!$oturum->hizmet_id) {
                return ['durum' => 'hata', 'mesaj' => 'Bu ders online rezervasyona kapalı.'];
            }
            if (!DersSeansServisi::hakVarMi($salonId, $userId, $oturum->hizmet_id)) {
                return ['durum' => 'hata', 'mesaj' => 'Bu ders için kullanılabilir paket/seans hakkınız bulunmuyor. Lütfen salonla iletişime geçin.'];
            }
        }

        return DB::transaction(function () use ($oturum, $userId, $salonId) {
            $kilit = DersOturumu::where('id', $oturum->id)->lockForUpdate()->first();
            $aktif = DersKatilimci::where('oturum_id', $kilit->id)
                ->whereNotIn('durum', ['iptal', 'bekleme'])->count();
            $durum = ($aktif >= (int) $kilit->kapasite) ? 'bekleme' : 'rezerve';

            DersKatilimci::create([
                'oturum_id' => $kilit->id,
                'user_id'   => $userId,
                'salon_id'  => $salonId,
                'durum'     => $durum,
                'not'       => 'online',
            ]);

            return [
                'durum' => 'ok',
                'katilimci_durum' => $durum,
                'mesaj' => $durum === 'bekleme'
                    ? 'Kapasite dolu olduğu için BEKLEME listesine eklendiniz. Yer açılırsa bilgilendirileceksiniz.'
                    : 'Rezervasyonunuz alındı. Görüşmek üzere ✨',
            ];
        });
    }

    /**
     * Bir salonun online rezervasyona uygun (aktif, iptal degil, gelecekte, hizmet_id
     * bagli) ders oturumlari — kalan kontenjanla birlikte. Mobil + mini-site listesi.
     */
    public static function uygunDersler($salonId, int $gunSayisi = 14)
    {
        $bugun = date('Y-m-d');
        $son = date('Y-m-d', strtotime('+' . $gunSayisi . ' day'));

        $oturumlar = DersOturumu::with('personel')
            ->where('salon_id', $salonId)
            ->where('aktif', true)
            ->where(function ($q) { $q->whereNull('iptal')->orWhere('iptal', 0); })
            ->whereNotNull('hizmet_id')
            ->whereBetween('tarih', [$bugun, $son])
            ->orderBy('tarih', 'asc')->orderBy('saat', 'asc')
            ->get();

        return $oturumlar->map(function ($o) {
            $aktif = DersKatilimci::where('oturum_id', $o->id)->whereNotIn('durum', ['iptal', 'bekleme'])->count();
            $now = strtotime(date('Y-m-d H:i'));
            return [
                'id'        => $o->id,
                'ders_tipi' => $o->ders_tipi ?: 'Grup Dersi',
                'hizmet_id' => $o->hizmet_id,
                'tarih'     => $o->tarih,
                'saat'      => substr($o->saat, 0, 5),
                'saat_bitis'=> substr($o->saat_bitis, 0, 5),
                'egitmen'   => $o->personel ? $o->personel->personel_adi : '',
                'kapasite'  => (int) $o->kapasite,
                'doluluk'   => $aktif,
                'bos'       => max(0, (int) $o->kapasite - $aktif),
                'gecmis'    => strtotime($o->tarih . ' ' . $o->saat) < $now,
            ];
        })->filter(function ($r) { return !$r['gecmis']; })->values();
    }
}
