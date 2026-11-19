<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp asistani paneli: musterinin WhatsApp profil fotografi.
 * Kopru (GET /session/{id}/avatar?jid=) kisa omurlu CDN URL'i verir; burada indirilip storage/app/wa_avatar altinda
 * 7 gun saklanir (panel /whatsapp-asistan/avatar/{oturum} ile servis eder). Foto yok / gizli / kopruda uc yoksa
 * 12 saat tekrar sorulmaz -> panel bas harfleri gosterir.
 */
class WaAvatar
{
    const SAKLAMA_GUN = 7;
    const MAX_BAYT = 1048576;

    /** Oturumun fotograf dosyasi (tam yol) ya da null. */
    public static function dosya(int $salonId, ?string $jid, ?string $telefon): ?string
    {
        $hedef = $jid ?: ($telefon ? preg_replace('/\D/', '', $telefon) . '@s.whatsapp.net' : null);
        if (!$hedef) return null;
        $anahtar = md5($hedef);
        $yol = storage_path("app/wa_avatar/{$salonId}/{$anahtar}.jpg");
        if (is_file($yol) && filemtime($yol) > time() - self::SAKLAMA_GUN * 86400) return $yol;
        $yok = "wa_avatar_yok_{$salonId}_{$anahtar}";
        if (Cache::get($yok)) return is_file($yol) ? $yol : null;   // eski kopya varsa onu kullan

        $salon = Salonlar::find($salonId);
        $url = null;
        try { $url = $salon ? app(WhatsAppService::class)->profilFotoUrl($salon, $hedef) : null; }
        catch (\Throwable $e) { Log::info('[WA-AVATAR] kopru: ' . $e->getMessage()); }
        $bayt = $url ? self::indir($url) : null;
        if (!$bayt) {
            Cache::put($yok, 1, 720); // 12 saat
            return is_file($yol) ? $yol : null;
        }
        if (!is_dir(dirname($yol))) @mkdir(dirname($yol), 0750, true);
        @file_put_contents($yol, $bayt);
        return is_file($yol) ? $yol : null;
    }

    protected static function indir(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 2]);
        $b = curl_exec($ch);
        $kod = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($b === false || $kod !== 200 || strlen($b) > self::MAX_BAYT) return null;
        return WaGorselDanisman::mimeTespit($b) ? $b : null; // yalniz gercek JPEG/PNG/WebP
    }

    /** Sistemde yuklu (varsayilan olmayan) musteri fotografi: [tel10 => url]. */
    public static function sistemFotolari(int $salonId, array $teller): array
    {
        $t10 = array_values(array_unique(array_filter(array_map(function ($t) {
            $d = preg_replace('/\D/', '', (string) $t); return strlen($d) >= 10 ? substr($d, -10) : null;
        }, $teller))));
        if (!$t10) return [];
        try {
            $l = \DB::table('users')->join('musteri_portfoy', 'musteri_portfoy.user_id', '=', 'users.id')
                ->where('musteri_portfoy.salon_id', $salonId)->whereIn('users.cep_telefon', $t10)
                ->whereNotNull('users.profil_resim')->where('users.profil_resim', 'not like', '%avatar.png')
                ->pluck('users.profil_resim', 'users.cep_telefon')->all();
        } catch (\Throwable $e) {
            return [];
        }
        return array_filter($l, function ($u) { return is_string($u) && $u !== ''; });
    }
}
