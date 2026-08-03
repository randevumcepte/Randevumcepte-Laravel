<?php

namespace App\Services;

use App\AdisyonPaketSeanslar;
use App\Randevular;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bir randevu "geldi" olarak isaretlenip seanslar geldi=true yapildiktan sonra
 * musteriye ISLEM (hizmet) bazinda -detayli- seans kullanim ozeti push'u yollar.
 *
 * ONEMLI - DOGRULUK: Buradaki tum sayilar salon panelindeki "Seans Takibi"
 * ekraniyla BIREBIR ayni mantikla hesaplanir (StoreAdminController::getPaketDetaylari
 * + public/js/seansTakibi.js). Boylece musteriye giden mesaj, salonun gordugu
 * panelle ASLA celismez:
 *   - toplam (hizmet basi) = adisyon_paketler.seans_sayisi
 *   - tamamlanan           = o hizmetin geldi=1 satir sayisi
 *   - gelmedi              = o hizmetin geldi=0 satir sayisi
 *   - kalan                = toplam - tamamlanan - gelmedi
 *   - bu randevuda         = bu randevuya ait, o hizmetin geldi=1 satir sayisi
 *
 * Sadece UYGULAMA BILDIRIMI (push) gonderir. SMS/WhatsApp GONDERMEZ.
 *
 * Hem ApiController (mobil) hem StoreAdminController (web) ayni servisi cagirir.
 *
 * Kullanim:  app(SeansBildirimService::class)->bilgilendir($randevu);
 */
class SeansBildirimService
{
    public function bilgilendir(Randevular $randevu): void
    {
        $musteri = $randevu->users ?? User::find($randevu->user_id);
        if (!$musteri || !$musteri->id) {
            Log::info('[SEANS-KULLANIM] musteri bulunamadi, atlandi', ['randevu_id' => $randevu->id]);
            return;
        }
        $musteriIsmi = trim($musteri->name ?? '') ?: 'Müşterimiz';

        // Bu randevu kapsamindaki seans satirlari (isaretlenmeyenler zaten silinmis;
        // kalanlar bu randevuda kullanilan/gelinen seanslar)
        $seanslar = AdisyonPaketSeanslar::where('randevu_id', $randevu->id)->get();
        if ($seanslar->isEmpty()) {
            Log::info('[SEANS-KULLANIM] randevuya bagli seans yok, atlandi', ['randevu_id' => $randevu->id]);
            return;
        }

        $paketIdleri  = $seanslar->pluck('adisyon_paket_id')->filter()->unique()->values();
        $hizmetIdleri = $seanslar->pluck('adisyon_hizmet_id')->filter()->unique()->values();

        // Her paket/hizmet icin blok uret
        $bloklar = [];
        foreach ($paketIdleri as $apId) {
            $b = $this->paketBloku((int) $apId, (int) $randevu->id);
            if ($b !== null) $bloklar[] = $b;
        }
        foreach ($hizmetIdleri as $ahId) {
            $b = $this->hizmetBloku((int) $ahId, (int) $randevu->id);
            if ($b !== null) $bloklar[] = $b;
        }

        if (empty($bloklar)) {
            Log::info('[SEANS-KULLANIM] ozet uretilemedi, atlandi', ['randevu_id' => $randevu->id]);
            return;
        }

        $body = $this->mesajOlustur($musteriIsmi, $bloklar);

        try {
            $sonuc = NotificationService::toCustomer((int) $musteri->id, (int) $randevu->salon_id)
                ->type(NotificationTypes::SESSION_USED)
                ->title('Seans Bilgilendirmesi ✨')
                ->body($body)
                ->randevu((int) $randevu->id)
                ->deepLink('sessions')
                ->send();

            Log::info('[SEANS-KULLANIM] push gonderildi', [
                'randevu_id' => $randevu->id,
                'user_id'    => $musteri->id,
                'salon_id'   => $randevu->salon_id,
                'blok_adet'  => count($bloklar),
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
     * Bir paket (adisyon_paket_id) icin hizmet-bazinda detay blok.
     * Donen yapi: ['ad'=>paketAdi, 'seansSayisi'=>int, 'kullanilan'=>[...], 'kullanilmayan'=>[...]]
     */
    private function paketBloku(int $adisyonPaketId, int $randevuId): ?array
    {
        $ap = DB::table('adisyon_paketler')->where('id', $adisyonPaketId)->first();
        if (!$ap) return null;

        $paketAdi = DB::table('paketler')->where('id', $ap->paket_id)->value('paket_adi') ?: 'Paketiniz';
        $seansSayisi = (int) ($ap->seans_sayisi ?? 0);

        // Paketin hizmetleri (panel "Seans Takibi" ile ayni kaynak: paket_hizmetler)
        $hizmetler = DB::table('paket_hizmetler')
            ->join('hizmetler', 'paket_hizmetler.hizmet_id', '=', 'hizmetler.id')
            ->where('paket_hizmetler.paket_id', $ap->paket_id)
            ->select('hizmetler.id as hid', 'hizmetler.hizmet_adi as ad')
            ->get();

        // Paket tanimindan hizmet gelmezse (veri farki), seans satirlarindaki
        // distinct hizmet_id'lerden turet — yine de dogru calissin.
        if ($hizmetler->isEmpty()) {
            $hizmetler = DB::table('adisyon_paket_seanslar')
                ->join('hizmetler', 'adisyon_paket_seanslar.hizmet_id', '=', 'hizmetler.id')
                ->where('adisyon_paket_seanslar.adisyon_paket_id', $adisyonPaketId)
                ->select('hizmetler.id as hid', 'hizmetler.hizmet_adi as ad')
                ->distinct()->get();
        }

        $kullanilan = [];
        $kullanilmayan = [];
        foreach ($hizmetler as $h) {
            $tamamlanan = (int) DB::table('adisyon_paket_seanslar')
                ->where('adisyon_paket_id', $adisyonPaketId)->where('hizmet_id', $h->hid)
                ->where('geldi', 1)->count();
            $gelmedi = (int) DB::table('adisyon_paket_seanslar')
                ->where('adisyon_paket_id', $adisyonPaketId)->where('hizmet_id', $h->hid)
                ->where('geldi', 0)->count();
            $pending = (int) DB::table('adisyon_paket_seanslar')
                ->where('adisyon_paket_id', $adisyonPaketId)->where('hizmet_id', $h->hid)
                ->whereNull('geldi')->count();

            // toplam: seans_sayisi (panelle ayni). Yoksa satirlardan turet (savunma).
            $toplam = $seansSayisi > 0 ? $seansSayisi : ($tamamlanan + $gelmedi + $pending);
            $kalan  = max(0, $toplam - $tamamlanan - $gelmedi);

            // Bu randevuda (bugun) o hizmetten kullanilan
            $bugun = (int) DB::table('adisyon_paket_seanslar')
                ->where('adisyon_paket_id', $adisyonPaketId)->where('hizmet_id', $h->hid)
                ->where('randevu_id', $randevuId)->where('geldi', 1)->count();

            $satir = [
                'ad' => $h->ad, 'toplam' => $toplam, 'tamamlanan' => $tamamlanan,
                'kalan' => $kalan, 'bugun' => $bugun,
            ];
            if ($bugun > 0) $kullanilan[] = $satir;
            else            $kullanilmayan[] = $satir;
        }

        if (empty($kullanilan) && empty($kullanilmayan)) return null;

        return [
            'tur' => 'paket', 'ad' => $paketAdi, 'seansSayisi' => $seansSayisi,
            'kullanilan' => $kullanilan, 'kullanilmayan' => $kullanilmayan,
        ];
    }

    /**
     * Paket disi tekil hizmet seansi (adisyon_hizmet_id) icin blok.
     */
    private function hizmetBloku(int $adisyonHizmetId, int $randevuId): ?array
    {
        $ah = DB::table('adisyon_hizmetler')->where('id', $adisyonHizmetId)->first();
        if (!$ah) return null;

        $hizmetAdi = DB::table('hizmetler')->where('id', $ah->hizmet_id)->value('hizmet_adi') ?: 'Hizmetiniz';
        $seansSayisi = (int) ($ah->seans_sayisi ?? 0);

        $tamamlanan = (int) DB::table('adisyon_paket_seanslar')
            ->where('adisyon_hizmet_id', $adisyonHizmetId)->where('geldi', 1)->count();
        $gelmedi = (int) DB::table('adisyon_paket_seanslar')
            ->where('adisyon_hizmet_id', $adisyonHizmetId)->where('geldi', 0)->count();
        $pending = (int) DB::table('adisyon_paket_seanslar')
            ->where('adisyon_hizmet_id', $adisyonHizmetId)->whereNull('geldi')->count();

        $toplam = $seansSayisi > 0 ? $seansSayisi : ($tamamlanan + $gelmedi + $pending);
        $kalan  = max(0, $toplam - $tamamlanan - $gelmedi);

        $bugun = (int) DB::table('adisyon_paket_seanslar')
            ->where('adisyon_hizmet_id', $adisyonHizmetId)
            ->where('randevu_id', $randevuId)->where('geldi', 1)->count();

        if ($bugun < 1 && $tamamlanan < 1) return null;

        return [
            'tur' => 'hizmet', 'ad' => $hizmetAdi, 'seansSayisi' => $toplam,
            'kullanilan' => [[
                'ad' => $hizmetAdi, 'toplam' => $toplam, 'tamamlanan' => $tamamlanan,
                'kalan' => $kalan, 'bugun' => max(1, $bugun),
            ]],
            'kullanilmayan' => [],
        ];
    }

    /**
     * Kullanicinin verdigi sablona BIREBIR uygun mesaj metni.
     */
    private function mesajOlustur(string $musteriIsmi, array $bloklar): string
    {
        $s  = "✨ **SEANS TAKİP BİLGİLENDİRMESİ** ✨\n\n";
        $s .= "Merhaba **Sayın {$musteriIsmi}**, 🌸\n\n";
        $s .= "Bugünkü randevunuz başarıyla tamamlanmıştır. Aşağıda paketinizin güncel seans durumunu inceleyebilirsiniz.\n";

        foreach ($bloklar as $blok) {
            $etiket = $blok['tur'] === 'hizmet' ? 'Hizmetiniz' : 'Paketiniz';
            $seansEk = $blok['seansSayisi'] > 0 ? " (**{$blok['seansSayisi']} Seans**)" : '';
            $s .= "\n📦 **{$etiket}:** {$blok['ad']}{$seansEk}\n";

            if (!empty($blok['kullanilan'])) {
                $baslik = count($blok['kullanilan']) > 1 ? 'Bugün Kullanılan İşlemler' : 'Bugün Kullanılan İşlem';
                $s .= "\n✅ **{$baslik}**\n";
                foreach ($blok['kullanilan'] as $k) {
                    $s .= "**{$k['ad']}**\n";
                    $s .= "• Bu randevuda kullanılan: **{$k['bugun']} Seans**\n";
                    $s .= "• Tamamlanan: **{$k['tamamlanan']} / {$k['toplam']} Seans**\n";
                    $s .= "• Kalan: **{$k['kalan']} Seans**\n";
                }
            }

            if (!empty($blok['kullanilmayan'])) {
                $s .= "\n⏳ **Bugün Kullanılmayan İşlemler**\n";
                foreach ($blok['kullanilmayan'] as $k) {
                    $s .= "• **{$k['ad']}:** Kalan **{$k['kalan']} Seans**\n";
                }
            }
        }

        $s .= "\n📌 Seans bilgileriniz sistemimizde güncellenmiştir. Bir sonraki randevunuzda kalan haklarınızı dilediğiniz gibi kullanabilirsiniz.\n\n";
        $s .= "💚 Bizi tercih ettiğiniz için teşekkür eder, sağlıklı ve güzel günlerde yeniden görüşmeyi dileriz.";

        return $s;
    }
}
