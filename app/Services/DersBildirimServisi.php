<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Grup dersi bildirimleri icin tek gonderim yardimcisi.
 * WhatsApp ONCELIKLI, basarisiz/kapali ise SMS'e duser (randevu hatirlatma ile
 * ayni kanal mantigi: WhatsAppService::sendReminder + Controller::sms_gonder).
 * Musteri whatsapp_onay + kanal-acik + paylasilan-oturum kontrolleri dahil.
 */
class DersBildirimServisi
{
    /**
     * @param Salonlar $salon    Dersin salonu
     * @param object   $musteri  users kaydi (name, cep_telefon, whatsapp_onay, id)
     * @param string   $mesaj    Gonderilecek metin
     * @param string   $gonderimTipi Kontor/loglama etiketi
     */
    public static function musteriyeGonder($salon, $musteri, string $mesaj, string $gonderimTipi = 'ders_bildirim')
    {
        if (!$salon || !$musteri || empty($musteri->cep_telefon)) {
            return;
        }

        $waSalon = WhatsAppService::resolveWaSalon($salon);
        $saglayici = $waSalon->whatsapp_saglayici ?? 'baileys';
        if ($saglayici === 'cloud_api') {
            $kanalAcik = !empty($waSalon->cloud_api_token) && !empty($waSalon->cloud_api_phone_number_id);
        } else {
            $kanalAcik = !empty($waSalon->whatsapp_aktif) && $waSalon->whatsapp_durum === 'connected';
        }
        $onayli = !Schema::hasColumn('users', 'whatsapp_onay') || (int) ($musteri->whatsapp_onay ?? 1) === 1;

        $waOk = false;
        if ($kanalAcik && $onayli) {
            try {
                $wa = app(WhatsAppService::class);
                $metin = WhatsAppMesajFormat::uygulamaDavetiEk($mesaj, $salon, $musteri->id ?? null);
                $sonuc = $wa->sendReminder($salon, $musteri->cep_telefon, $metin, null, $musteri->id ?? null, null, false, $gonderimTipi);
                $waOk = $sonuc['ok'] ?? false;
                if (!$waOk) {
                    Log::warning('[DERS-BILD] WA basarisiz -> SMS fallback', [
                        'salon_id' => $salon->id, 'error' => $sonuc['error'] ?? 'unknown',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('[DERS-BILD] WA exception -> SMS fallback', ['salon_id' => $salon->id, 'err' => $e->getMessage()]);
            }
        }

        if (!$waOk) {
            // SMS'te gonderici kimligi gorunmez; salon adini basa ekle (randevu hatirlatma ile ayni).
            $smsBase = !empty($salon->salon_adi) ? $salon->salon_adi . ' - ' . $mesaj : $mesaj;
            try {
                app(\App\Http\Controllers\Controller::class)->sms_gonder($salon->id, [[
                    'to' => $musteri->cep_telefon,
                    'message' => $smsBase,
                ]]);
            } catch (\Throwable $e) {
                Log::warning('[DERS-BILD] SMS fail', ['salon_id' => $salon->id, 'err' => $e->getMessage()]);
            }
        }
    }
}
