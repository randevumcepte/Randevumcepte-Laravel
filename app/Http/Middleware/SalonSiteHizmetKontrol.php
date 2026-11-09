<?php

namespace App\Http\Middleware;

use App\Salonlar;
use Closure;

/**
 * Isletme mini sitesi: kullanim suresi (uyelik_bitis_tarihi) bitmis salonun domain'inde
 * sayfa yerine "web sitemiz hizmet disidir" ekrani doner; online randevu da kapanir.
 * Bitis gunu dahil site acik kalir (panel kilidi lisans_sure_kontrol ile ayni sinir).
 * Tarihi bos salon "suresi bitmis" sayilmaz. Domain'i bir salona ait olmayan host
 * (panel, app.*, test) oldugu gibi gecer.
 */
class SalonSiteHizmetKontrol
{
    public function handle($request, Closure $next)
    {
        $domain = str_replace('www.', '', $request->getHost());
        $salon  = Salonlar::where('domain', $domain)->select('salon_adi', 'uyelik_bitis_tarihi')->first();

        if ($salon && self::suresiBitti($salon->uyelik_bitis_tarihi)) {
            return response()
                ->view('site_hizmet_disi', ['salon' => $salon], 503)
                ->header('Retry-After', '86400')
                ->header('X-Rc-Kasitli', '1'); // hata izleme bu 503'u sunucu hatasi saymaz
        }

        return $next($request);
    }

    public static function suresiBitti($bitisTarihi)
    {
        if (empty($bitisTarihi)) {
            return false;
        }

        $bitis = strtotime(substr((string) $bitisTarihi, 0, 10) . ' 23:59:59');

        return $bitis !== false && $bitis < time();
    }
}
