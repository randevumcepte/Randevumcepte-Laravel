<?php

namespace App\Services;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\StoreAdminController;
use App\IsletmeYetkilileri;
use App\OnGorusmeler;
use App\Personeller;
use App\Randevular;
use App\SalonSMSAyarlari;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Ön görüşme randevusu bildirimleri — v2 outbox ('ongorusme_kayit', v2:bildirim-isle) TEK KAYNAĞI.
 * Metinler eski web ongorusmeekleduzenle / mobil v1 ongorusmeekleguncelle ile aynı; SMS ayarı ayar_id=12:
 *   musteri  → "... ön görüşme randevunuz düzenlenmiştir ..." (WA-first + SMS, kara liste süzgeci)
 *   personel → görüşmeyi yapan personele SMS + panel bildirimi + FCM push (deep link: appointment_detail)
 * Eski kanallardan farkı: web personel metnindeki hizmet adı hatası (hizmet->paket_adi) ve
 * "randevusu randevusu" tekrarı düzeltildi; müşteri seçiliyken telefon boş kalmıyor.
 */
class OnGorusmeBildirimServisi
{
    public static function kayitBildirimi(OnGorusmeler $og, Randevular $randevu, $yetkiliId)
    {
        $ayar = SalonSMSAyarlari::where('ayar_id', 12)->where('salon_id', $og->salon_id)->first(['musteri', 'personel']);
        if (!$ayar) {
            return;
        }
        $salon = $og->salon;
        $zaman = date('d.m.Y', strtotime($randevu->tarih)) . ' - ' . date('H:i', strtotime($randevu->saat));
        $istek = new Request();

        $tel = $og->cep_telefon ?: optional($og->musteri)->cep_telefon;
        if ($ayar->musteri == 1 && $tel && $salon) {
            app(StoreAdminController::class)->sms_gonder_bildirimli($istek, [[
                'to' => $tel,
                'message' => $salon->salon_adi . ' tarafından ' . $zaman . ' olarak ön görüşme randevunuz düzenlenmiştir. '
                    . 'Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0' . $salon->telefon_1,
            ]], false, 1, false, $og->salon_id);
        }

        if ($ayar->personel != 1 || !$og->personel_id) {
            return;
        }
        $yetkili = $yetkiliId ? IsletmeYetkilileri::find($yetkiliId) : null;
        $mesaj = $og->ad_soyad . ' isimli müşterinin ' . $zaman . ' ' . self::nedenAdi($og) . ' için ön görüşme randevusu '
            . (optional($yetkili)->name ?: 'işletme') . ' tarafından düzenlenmiştir.';

        $personelGsm = IsletmeYetkilileri::where('id', Personeller::where('id', $og->personel_id)->value('yetkili_id'))->value('gsm1');
        if ($personelGsm) {
            app(StoreAdminController::class)->sms_gonder_bildirimli($istek, [['to' => $personelGsm, 'message' => $mesaj]], false, 1, false, $og->salon_id);
        }
        app(ApiController::class)->bildirimekle($istek, $og->salon_id, $mesaj, '#', $og->personel_id, null, optional($yetkili)->profil_resim, $randevu->id);

        try {
            NotificationService::toStaff((int) $og->personel_id, (int) $og->salon_id)
                ->type(NotificationTypes::APPOINTMENT_CREATED)
                ->title('Yeni Ön Görüşme Randevusu')
                ->body($mesaj)
                ->randevu((int) $randevu->id)
                ->deepLink('appointment_detail', ['randevu_id' => $randevu->id, 'on_gorusme' => 1])
                ->extra(['ongorusme' => '1'])
                ->send();
        } catch (\Throwable $e) {
            Log::warning('on gorusme push: ' . $e->getMessage());
        }
    }

    private static function nedenAdi(OnGorusmeler $og)
    {
        if ($og->paket_id) {
            return optional($og->paket)->paket_adi ?: 'Paket';
        }
        if ($og->urun_id) {
            return optional($og->urun)->urun_adi ?: 'Ürün';
        }
        if ($og->hizmet_id) {
            return optional($og->hizmet)->hizmet_adi ?: 'Hizmet';
        }

        return 'Ön Görüşme';
    }
}
