<?php

namespace App\Services;

use App\Http\Controllers\StoreAdminController;
use App\MusteriPortfoy;
use App\SalonSMSAyarlari;
use App\Salonlar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Musteri bildirimleri — TEK KAYNAK. Panel (StoreAdminController) ve v2 outbox
 * (v2:bildirim-isle: musteri_kayit / kara_liste_bildirim) ayni metni ve ayni
 * gonderim yolunu (WA-first + SMS fallback, kara liste suzgeci) kullanir.
 */
class MusteriBildirimServisi
{
    /**
     * Yeni musteri kayit mesaji — SMS ayari ayar_id=4 (musteri) aciksa.
     * $sifre verilirse kullanici adi + sifre mesaja eklenir (yeni hesap).
     */
    public static function kayitBildirimi($salonId, User $musteri, $sifre = null, ?Request $request = null)
    {
        if (!SalonSMSAyarlari::where('salon_id', $salonId)->where('ayar_id', 4)->value('musteri')) {
            return;
        }
        $salon = Salonlar::where('id', $salonId)->first(['salon_adi', 'uygulamalar_kisa_link']);
        if (!$salon || !$musteri->cep_telefon) {
            return;
        }

        $mesaj = 'Sayın ' . $musteri->name . '. ' . $salon->salon_adi . ' tarafından müşteri kaydınız oluşturulmuştur.';
        if ($sifre) {
            $mesaj .= '. Sistem kullanıcı adınız:' . $musteri->cep_telefon . ' Şifreniz:' . $sifre;
        }
        if ($salon->uygulamalar_kisa_link) {
            $mesaj .= ' Uygulamamızı indirmek için linke tıklayın. ' . $salon->uygulamalar_kisa_link;
        }

        app(StoreAdminController::class)->sms_gonder_bildirimli($request ?: new Request(),
            [['to' => $musteri->cep_telefon, 'message' => $mesaj]], false, 1, false, $salonId);
    }

    /**
     * "Kara listeye alindiniz" bilgilendirmesi — ayar_id=15 (musteri) aciksa WA/SMS + push.
     * 'kara_liste_bildirim' tipi KaraListeServisi::MUAF_TIPLER'de: yeni kara listeye
     * alinan musteriye bu mesaj yine de gider.
     */
    public static function karaListeBildirimi($salonId, $userId, ?Request $request = null)
    {
        if (SalonSMSAyarlari::where('ayar_id', 15)->where('salon_id', $salonId)->value('musteri') != 1) {
            return;
        }
        $portfoy = MusteriPortfoy::where('user_id', $userId)->where('salon_id', $salonId)->first();
        if (!$portfoy || !$portfoy->users) {
            return;
        }

        $salonTel = Salonlar::where('id', $salonId)->value('telefon_1');
        $metin = 'Sayın ' . $portfoy->users->name . ', isteğiniz doğrultusunda telefon numaranız kara listeye alınmıştır. '
            . 'Kampanya ve reklam mesajları gönderilmeyecektir. Detaylı bilgi için bize ulaşınız. 0' . $salonTel;

        app(StoreAdminController::class)->sms_gonder_bildirimli($request ?: new Request(), [[
            'to' => $portfoy->users->cep_telefon,
            'message' => $metin,
            'user_id' => $portfoy->user_id,
            'gonderim_tipi' => 'kara_liste_bildirim',
        ]], false, 1, false, $salonId, 'kara_liste_bildirim');

        try {
            NotificationService::toCustomer((int) $portfoy->user_id, (int) $salonId)
                ->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                ->title('Kara Listeye Alındınız')
                ->body($metin)
                ->karaListeMuaf(true)
                ->send();
        } catch (\Throwable $e) {
            Log::warning('karaliste bildirim push hata: ' . $e->getMessage());
        }
    }
}
