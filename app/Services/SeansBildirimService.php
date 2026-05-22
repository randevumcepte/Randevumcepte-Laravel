<?php

namespace App\Services;

use App\AdisyonHizmetler;
use App\AdisyonPaketler;
use App\AdisyonPaketSeanslar;
use App\Randevular;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Bir randevu "geldi" olarak isaretlenip seanslar geldi=true yapildiktan
 * sonra musteriye paket/hizmet bazinda seans kullanim ozeti push'u yollar.
 *
 * Hem ApiController (mobil API) hem StoreAdminController (web admin) aynisini
 * cagirir; mantik tek bir yerde merkezi tutulur — boylece bir taraf
 * degisirse digeri otomatik senkron kalir.
 *
 * Kullanim:
 *   app(SeansBildirimService::class)->bilgilendir($randevu);
 */
class SeansBildirimService
{
    /**
     * Randevu kapsamindaki seanslari ozetleyip musteriye push gonderir.
     * Push fail olsa bile cagiran taraftaki ana akis etkilenmez — yine de
     * cagiran taraf da try/catch ile sarmalamali (savunma katmanli).
     */
    public function bilgilendir(Randevular $randevu): void
    {
        $musteri = $randevu->users ?? User::find($randevu->user_id);
        if (!$musteri || !$musteri->id) {
            Log::info('[SEANS-KULLANIM] musteri bulunamadi, atlandi', [
                'randevu_id' => $randevu->id,
            ]);
            return;
        }
        $musteriIsmi = $musteri->name ?? 'Müşterimiz';

        // Bu randevu kapsamindaki seans satirlarini cek
        $seanslar = AdisyonPaketSeanslar::where('randevu_id', $randevu->id)->get();
        if ($seanslar->isEmpty()) {
            Log::info('[SEANS-KULLANIM] randevuya bagli seans yok, atlandi', [
                'randevu_id' => $randevu->id,
            ]);
            return;
        }

        // Gruplama: paket (adisyon_paket_id) ve hizmet (adisyon_hizmet_id)
        $paketIdleri = $seanslar->pluck('adisyon_paket_id')->filter()->unique();
        $hizmetIdleri = $seanslar->pluck('adisyon_hizmet_id')->filter()->unique();

        $bilgilendirmeler = [];
        foreach ($paketIdleri as $apId) {
            $bilgi = $this->paketSeansOzeti((int) $apId);
            if ($bilgi) $bilgilendirmeler[] = $bilgi;
        }
        foreach ($hizmetIdleri as $ahId) {
            $bilgi = $this->hizmetSeansOzeti((int) $ahId);
            if ($bilgi) $bilgilendirmeler[] = $bilgi;
        }

        if (empty($bilgilendirmeler)) {
            Log::info('[SEANS-KULLANIM] ozet uretemedi, atlandi', [
                'randevu_id' => $randevu->id,
            ]);
            return;
        }

        // Tek paket/hizmet => sade tek cumle
        // Birden fazla => maddeli liste
        if (count($bilgilendirmeler) === 1) {
            $b = $bilgilendirmeler[0];
            $body = "Sayın {$musteriIsmi}, almış olduğunuz {$b['toplam']} seanslık {$b['ad']} {$b['tipEk']} bugün {$b['kullanilan']}. seansını kullandınız, geri kalan {$b['kalan']} seansınız bulunmaktadır.";
        } else {
            $body = "Sayın {$musteriIsmi}, bugünkü randevunuzda kullanılan seanslar:\n";
            foreach ($bilgilendirmeler as $b) {
                $body .= "• {$b['ad']} {$b['tipEk']} {$b['toplam']} seanslık, {$b['kullanilan']}. seansı kullanıldı, kalan {$b['kalan']}.\n";
            }
            $body = rtrim($body);
        }

        try {
            $sonuc = NotificationService::toCustomer(
                (int) $musteri->id,
                (int) $randevu->salon_id
            )
                ->type(NotificationTypes::SESSION_USED)
                ->title('Seans Kullanımı')
                ->body($body)
                ->randevu((int) $randevu->id)
                ->deepLink('sessions')
                ->send();

            Log::info('[SEANS-KULLANIM] push gonderildi', [
                'randevu_id' => $randevu->id,
                'user_id'    => $musteri->id,
                'salon_id'   => $randevu->salon_id,
                'paket_adet' => count($bilgilendirmeler),
                'sonuc'      => $sonuc,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[SEANS-KULLANIM] push fail', [
                'randevu_id' => $randevu->id,
                'err'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * adisyon_paket_id icin seans ozeti: paket adi, toplam, kullanilan, kalan.
     */
    private function paketSeansOzeti(int $adisyonPaketId): ?array
    {
        $paket = AdisyonPaketler::with('paket')->find($adisyonPaketId);
        if (!$paket || !$paket->paket) return null;

        $seanslar = AdisyonPaketSeanslar::where('adisyon_paket_id', $adisyonPaketId)->get();
        $toplam = (int) ($paket->seans_sayisi ?? $seanslar->count());
        // dusulen_miktar destegi: bir satir N seans/dakika dusebilir
        $kullanilan = (int) $seanslar->where('geldi', 1)->sum('dusulen_miktar');
        $kalan = $seanslar->filter(function ($s) {
            return $s->geldi === null && !$s->iptal;
        })->count();

        return [
            'ad'         => $paket->paket->paket_adi,
            'tipEk'      => 'paketinizin',
            'toplam'     => $toplam,
            'kullanilan' => $kullanilan,
            'kalan'      => $kalan,
        ];
    }

    /**
     * Standalone adisyon_hizmet seans ozeti (paket disi, seans_sayisi atanmis hizmet).
     */
    private function hizmetSeansOzeti(int $adisyonHizmetId): ?array
    {
        $hizmet = AdisyonHizmetler::with('hizmet')->find($adisyonHizmetId);
        if (!$hizmet || !$hizmet->hizmet) return null;

        $seanslar = AdisyonPaketSeanslar::where('adisyon_hizmet_id', $adisyonHizmetId)->get();
        $toplam = (int) ($hizmet->seans_sayisi ?? $seanslar->count());
        $kullanilan = (int) $seanslar->where('geldi', 1)->sum('dusulen_miktar');
        $kalan = $seanslar->filter(function ($s) {
            return $s->geldi === null && !$s->iptal;
        })->count();

        return [
            'ad'         => $hizmet->hizmet->hizmet_adi,
            'tipEk'      => 'hizmetinizin',
            'toplam'     => $toplam,
            'kullanilan' => $kullanilan,
            'kalan'      => $kalan,
        ];
    }
}
