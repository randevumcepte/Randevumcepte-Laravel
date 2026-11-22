<?php

namespace App\Http\Controllers;

use App\Services\BildirimTercih;
use Illuminate\Http\Request;

/**
 * Ayarlar > Bildirim Ayarlari (2026-11-22): SMS / WhatsApp / Uygulama bildirimi ayri ayri,
 * her bildirim x alici icin isletme sahibi secer. Web paneli ve mobil uygulama ayni uclari kullanir
 * (web: oturum, mobil: Bearer + sube parametresi). Her degisiklik Log Hareketleri'ne yazilir.
 * Yalniz Hesap Sahibi (rol 1) — eski SMS Ayarlari sekmesiyle ayni kapi.
 */
class BildirimAyarController extends StoreAdminController
{
    /**
     * SAC kurucusundaki web-oturum kapisi (oturum yoksa 401 session_expired) mobil Bearer istegini
     * reddederdi. Burada kimlik + rol salon() icinde dogrulanir (web: isletmeyonetim, mobil:
     * isletmeyonetim-api); oturumsuz istek 403 alir.
     */
    public function __construct()
    {
    }

    /** Ayari degistirebilen: Hesap Sahibi (1); rol atamasi olmayan yetkili de hesap sahibi sayilir. */
    protected function salon(Request $request): ?int
    {
        $sid = $this->asistanGorusmeSalon($request);
        if (!$sid) return null;
        $yetkili = $this->asistanGorusmeYetkili();
        $rol = $yetkili ? self::kullaniciRolu($sid, $yetkili->id) : 5;
        if ($rol !== null && (int) $rol !== 1) return null;
        return $sid;
    }

    protected function kaynak()
    {
        return \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->check() ? 'panel' : 'mobil';
    }

    public function veri(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return response()->json(['ok' => 0, 'hata' => 'Bildirim ayarlarını yalnızca işletme sahibi değiştirebilir.'], 403);
        return response()->json(['ok' => 1] + BildirimTercih::ekranVerisi($sid));
    }

    /**
     * Govde: {kod, alici, kanal, deger}            tek anahtar
     *     | {toplu:1, kanal, alici?, deger}         bir kanaldaki hepsini ac/kapat
     *     | {wa_yedek_sms: 0/1}                     WhatsApp gidemezse SMS
     */
    public function kaydet(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return response()->json(['ok' => 0, 'hata' => 'Bildirim ayarlarını yalnızca işletme sahibi değiştirebilir.'], 403);
        $kaynak = $this->kaynak();

        if ($request->has('wa_yedek_sms')) {
            BildirimTercih::waYedekKaydet($sid, (int) $request->input('wa_yedek_sms') === 1, $kaynak, $request);
            return response()->json(['ok' => 1, 'wa_yedek_sms' => BildirimTercih::waYedekSms($sid)]);
        }

        $kanal = (string) $request->input('kanal');
        if (!in_array($kanal, BildirimTercih::KANALLAR, true)) return response()->json(['ok' => 0, 'hata' => 'Geçersiz kanal.'], 422);
        $deger = (int) $request->input('deger') === 1;

        if ((int) $request->input('toplu') === 1) {
            $alici = $request->input('alici');
            if ($alici !== null && $alici !== '' && !in_array($alici, BildirimTercih::ALICILAR, true)) {
                return response()->json(['ok' => 0, 'hata' => 'Geçersiz alıcı.'], 422);
            }
            $adet = BildirimTercih::topluKaydet($sid, $kanal, $alici ?: null, $deger, $kaynak, $request);
            return response()->json(['ok' => 1, 'degisen' => $adet] + BildirimTercih::ekranVerisi($sid));
        }

        $sonuc = BildirimTercih::kaydet($sid, (string) $request->input('kod'), (string) $request->input('alici'), $kanal, $deger, $kaynak, $request);
        if ($sonuc === null) return response()->json(['ok' => 0, 'hata' => 'Geçersiz bildirim.'], 422);
        return response()->json(['ok' => 1, 'kanallar' => BildirimTercih::kanallar($sid, $request->input('kod'), $request->input('alici'))]);
    }
}
