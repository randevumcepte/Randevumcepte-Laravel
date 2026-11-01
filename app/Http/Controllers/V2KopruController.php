<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Eski web paneli (session) → yeni v2 paneli (Bearer token) KOPRUSU.
 *
 * Oturumdaki isletme yetkilisi icin Passport token uretir (Flutter login'i ile ayni
 * yol: createToken) ve v2 sayfasina yonlendirir. Token URL FRAGMENT'inda tasinir
 * (#token=...) → sunucu loglarina ve Referer basligina dusmez; v2 kabugu saklayip
 * adres cubugundan siler.
 *
 * Guvenlik:
 *   - Yalnizca beyaz listedeki hedefler (acik yonlendirme yok).
 *   - Kullanici o subeye bagli olmali (personel kaydi veya rol atamasi).
 *   - Ayni kullanicinin 12 saatten eski 'v2-web' tokenlari iptal edilir (birikmesin).
 *     v2 her istekte kapsam + yetkiyi ayrica denetler.
 */
class V2KopruController extends Controller
{
    private const HEDEFLER = [
        'takvim' => '/v2/takvim',
    ];

    public function gecis(Request $request)
    {
        $hedef = self::HEDEFLER[$request->input('hedef')] ?? null;
        $salonId = (int) $request->input('sube');
        $user = Auth::guard('isletmeyonetim')->user();
        if (!$hedef || !$salonId || !$user) {
            abort(404);
        }

        $bagli = DB::table('salon_personelleri')->where('yetkili_id', $user->id)->where('salon_id', $salonId)->exists()
            || DB::table('model_has_roles')->where('model_id', $user->id)->where('salon_id', $salonId)->exists();
        if (!$bagli) {
            abort(403, 'Bu işletmeye erişim yetkiniz yok.');
        }

        DB::table('oauth_access_tokens')
            ->where('user_id', $user->id)->where('name', 'v2-web')->where('revoked', 0)
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-12 hours')))
            ->update(['revoked' => 1]);

        $token = $user->createToken('v2-web')->accessToken;

        return redirect()->away(config('v2kopru.panel_url') . $hedef . '?sube=' . $salonId . '#token=' . urlencode($token));
    }

    /** Bu salonda "Randevu Takvimi" menusu v2'yi mi acsin? (V2_TAKVIM_SALONLARI) */
    public static function takvimAcik($salonId)
    {
        $liste = trim((string) config('v2kopru.takvim_salonlari'));
        if ($liste === '') {
            return false;
        }
        if ($liste === '*') {
            return true;
        }

        return in_array((int) $salonId, array_map('intval', explode(',', $liste)), true);
    }
}
