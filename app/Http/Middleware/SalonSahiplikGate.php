<?php

namespace App\Http\Middleware;

use Closure;

/**
 * SALON SAHIPLIK GATE (Guvenlik G1 — merkezi kiracı izolasyonu)
 *
 * Mobil /api/v1 uclarinda "salonu token'dan dogrula" gate'i. Istekteki
 * salonid/salon_id/sube parametresinin, TOKEN sahibinin gercekten ait oldugu
 * bir salon olup olmadigini kontrol eder. Degilse 403.
 *
 * MODLAR (route'ta parametre olarak verilir: ->middleware('salon.sahiplik:enforce')):
 *   - 'enforce' (VARSAYILAN, Faz A/B): GECISLI. Token VARSA sahiplik dogrulanir;
 *     uymazsa 403. Token YOKSA (sahadaki eski app surumleri) istek AYNEN gecer —
 *     boylece yuklu uygulamalar KIRILMAZ. Yeni app token gonderdiginde otomatik korunur.
 *   - 'deny' (Faz C): Token ZORUNLU. Token yoksa 401. Eski app trafigi yok olunca
 *     bu moda cevrilir; o ana kadar 'enforce' kalir.
 *
 * NOT: Bu middleware yalnizca salon-kapsamli (salonid/sube iceren) uclarda anlamlidir.
 * Istekte salon parametresi yoksa dogrulama yapmadan gecer (id-tabanli uclar — musteri
 * detay vb. — ayrica ele alinir; madde 1'deki gibi metot icinde sahiplik baglanir).
 */
class SalonSahiplikGate
{
    /** Istekte salon kimligi tasiyabilecek olasi anahtarlar (route param + body). */
    private const SALON_ANAHTARLARI = ['salonid', 'salonId', 'salon_id', 'sube', 'subeId', 'subeid'];

    public function handle($request, Closure $next, $mod = 'enforce')
    {
        // Kimligi token'dan coz: once isletme (isletmeyonetim-api), sonra musteri (api).
        $user = \Auth::guard('isletmeyonetim-api')->user();
        $tip  = $user ? 'isletme' : null;
        if (!$user) {
            $user = \Auth::guard('api')->user();
            $tip  = $user ? 'musteri' : null;
        }

        if (!$user) {
            // TOKEN YOK. Faz C ('deny') -> reddet; Faz A/B ('enforce') -> gecisli, aynen gec.
            if ($mod === 'deny') {
                return response()->json(['basarili' => false, 'mesaj' => 'Oturum gerekli.'], 401, [], JSON_UNESCAPED_UNICODE);
            }
            return $next($request);
        }

        // Istenen salon id'sini route param + body'den bul.
        $salonId = $this->salonIdBul($request);
        if ($salonId === null) {
            // Salon-kapsamli degil (veya id-tabanli uc) -> bu gate dogrulamaz, gec.
            return $next($request);
        }

        if (!$this->salonaAitMi($user, $tip, (int) $salonId)) {
            return response()->json([
                'basarili' => false,
                'mesaj'    => 'Bu salon için yetkiniz yok.',
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        return $next($request);
    }

    /** Route parametreleri ONCE, sonra request body — ilk dolu salon anahtarini dondur. */
    private function salonIdBul($request)
    {
        foreach (self::SALON_ANAHTARLARI as $k) {
            $v = $request->route($k);
            if ($v !== null && $v !== '') return $v;
        }
        foreach (self::SALON_ANAHTARLARI as $k) {
            $v = $request->input($k);
            if ($v !== null && $v !== '') return $v;
        }
        return null;
    }

    /** Token sahibi bu salona gercekten ait mi? (login giris gate'i ile ayni kaynak) */
    private function salonaAitMi($user, $tip, int $salonId): bool
    {
        if ($tip === 'isletme') {
            // Isletme yetkilisi: aktif Personeller satiri olan salonlar (yetkili_olunan_isletmeler).
            return \App\Personeller::where('yetkili_id', $user->id)
                ->where('salon_id', $salonId)
                ->where('aktif', 1)
                ->exists();
        }

        // Musteri: portfoyunde (musteri_portfoy) bu salon varsa.
        return \App\MusteriPortfoy::where('user_id', $user->id)
            ->where('salon_id', $salonId)
            ->exists();
    }
}
