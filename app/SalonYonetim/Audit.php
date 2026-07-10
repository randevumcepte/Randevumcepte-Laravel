<?php

namespace App\SalonYonetim;

use App\SalonAktiviteLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Salon paneli aktivite logger.
 *
 * Kullanım:
 *   use App\SalonYonetim\Audit;
 *   Audit::log($salonId, 'randevu_sil', 'randevu', $rid, "Ahmet Yılmaz - 25.04.2026 15:00",
 *              "Personel randevuyu iptal etti", ['eski_durum' => 'onaylandi']);
 *
 * Try/catch ile sarılı; log yazımı asla iş akışını kırmaz.
 */
class Audit
{
    public static function log(
        $salonId,
        $action,
        $targetType = null,
        $targetId = null,
        $targetLabel = null,
        $aciklama = null,
        array $meta = []
    ) {
        try {
            $userId = null;
            $userType = null;
            $userName = null;
            $userRol = null;

            if (Auth::guard('isletmeyonetim')->check()) {
                $u = Auth::guard('isletmeyonetim')->user();
                $userId = $u->id;
                $userType = 'yetkili';
                $userName = $u->name;

                // Salona göre rolünü çek (1=Hesap Sahibi, 4=Yönetici, 5=Personel ...)
                $roleId = DB::table('model_has_roles')
                    ->where('model_id', $u->id)
                    ->where('salon_id', $salonId)
                    ->value('role_id');
                $rolMap = [
                    1 => 'Hesap Sahibi',
                    2 => 'Süpervizör',
                    3 => 'Yönetici',
                    4 => 'Yönetici',
                    5 => 'Personel',
                ];
                $userRol = isset($rolMap[$roleId]) ? $rolMap[$roleId] : ($roleId ? 'Rol#'.$roleId : 'Yetkili');

                if ($roleId == 5) $userType = 'personel';
            } elseif (Auth::guard('satisortakligi')->check()) {
                $u = Auth::guard('satisortakligi')->user();
                $userId = $u->id;
                $userType = 'satis_ortagi';
                $userName = isset($u->name) ? $u->name : 'Satış Ortağı';
                $userRol = 'Satış Ortağı';
            } else {
                $userType = 'sistem';
                $userName = 'Sistem';
                $userRol = 'Sistem';
            }

            SalonAktiviteLog::create([
                'salon_id'     => $salonId,
                'user_id'      => $userId,
                'user_type'    => $userType,
                'user_name'    => $userName,
                'user_rol'     => $userRol,
                'action'       => $action,
                'target_type'  => $targetType,
                'target_id'    => $targetId,
                'target_label' => $targetLabel ? mb_substr($targetLabel, 0, 200) : null,
                'aciklama'     => $aciklama,
                'meta'         => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                'ip'           => Request::ip(),
                'user_agent'   => mb_substr((string) Request::header('User-Agent'), 0, 255),
            ]);
        } catch (\Exception $e) {
            // log yazımı sessizce geçsin
        }
    }

    /**
     * Mobil uygulama (API) aktivite logger.
     *
     * Web panelinden farkli olarak API isteklerinde session guard (isletmeyonetim)
     * bos olur. Bu metod kullaniciyi sirasiyla:
     *   1) Passport API token'i (isletmeyonetim-api guard)
     *   2) Istek govdesindeki olusturan / yetkili_id / olusturan_id alani
     * uzerinden cozer. Hicbiri yoksa 'Mobil' olarak isaretler.
     *
     * Kullanım (ApiController icinde):
     *   Audit::logApi($salonId, $request, 'randevu_iptal', 'randevu', $rid, "Ahmet Yılmaz");
     *
     * Try/catch ile sarili; log yazimi asla API akisini kirmaz.
     *
     * @param mixed                          $salonId
     * @param \Illuminate\Http\Request|null  $req
     */
    public static function logApi(
        $salonId,
        $req = null,
        $action = null,
        $targetType = null,
        $targetId = null,
        $targetLabel = null,
        $aciklama = null,
        array $meta = []
    ) {
        try {
            $user = null;
            // Sadece Authorization: Bearer varsa passport guard'ini yokla;
            // token cozumu \Error firlatabilir, \Throwable ile yakala ki
            // fallback (olusturan) ile log YINE de yazilsin.
            try {
                if ($req && $req->bearerToken() && Auth::guard('isletmeyonetim-api')->check()) {
                    $user = Auth::guard('isletmeyonetim-api')->user();
                }
            } catch (\Throwable $e) {
                // token cozulemezse sessizce gec, fallback'e dus
            }

            $userId = $user ? $user->id : null;
            if (!$userId && $req) {
                // Islemi yapan giris kullanicisi: mobil uygulama login'den sonra
                // kullanici id'sini tutar ve mutasyon isteklerinde asagidaki
                // alanlardan biriyle gonderir. Once "olusturan/yetkili/user"
                // (giris yapan aktor), en son care olarak "personel_id".
                foreach ([
                    'olusturan',
                    'olusturan_user_id',
                    'olusturan_id',
                    'yetkili_id',
                    'user_id',
                    'olusturan_personel_id',
                    'personel_id',
                ] as $alan) {
                    $deger = $req->input($alan);
                    // "user[id]" gibi ic ice gonderilmis olabilir
                    if (is_array($deger)) {
                        $deger = $deger['id'] ?? null;
                    }
                    if ($deger !== null && $deger !== '' && is_numeric($deger)) {
                        $userId = $deger;
                        break;
                    }
                }
                // Nesne olarak gonderilmis "user":{"id":..} ihtimali
                if (!$userId) {
                    $u = $req->input('user');
                    if (is_array($u) && isset($u['id']) && is_numeric($u['id'])) {
                        $userId = $u['id'];
                    }
                }
            }
            // sayisal degilse (ornegin personel adi gondermisse) yok say
            if ($userId !== null && !is_numeric($userId)) {
                $userId = null;
            }

            $userName = $user ? $user->name : null;
            $userType = 'yetkili';
            $userRol  = null;

            if ($userId) {
                if (!$userName) {
                    $userName = \App\IsletmeYetkilileri::where('id', $userId)->value('name');
                }
                $roleId = DB::table('model_has_roles')
                    ->where('model_id', $userId)
                    ->where('salon_id', $salonId)
                    ->value('role_id');
                $rolMap = [
                    1 => 'Hesap Sahibi',
                    2 => 'Süpervizör',
                    3 => 'Yönetici',
                    4 => 'Yönetici',
                    5 => 'Personel',
                ];
                $userRol = isset($rolMap[$roleId]) ? $rolMap[$roleId] : ($roleId ? 'Rol#'.$roleId : 'Yetkili');
                if ($roleId == 5) $userType = 'personel';
            } else {
                $userType = 'sistem';
                $userName = $userName ?: 'Mobil';
                $userRol  = 'Mobil';
            }

            // Kaynagi mobil olarak isaretle (web log'larindan ayirt etmek icin)
            if (!isset($meta['kaynak'])) {
                $meta['kaynak'] = 'mobil';
            }

            $ip = $req ? $req->ip() : Request::ip();
            $ua = $req ? (string) $req->header('User-Agent') : (string) Request::header('User-Agent');

            SalonAktiviteLog::create([
                'salon_id'     => $salonId,
                'user_id'      => $userId,
                'user_type'    => $userType,
                'user_name'    => $userName,
                'user_rol'     => $userRol,
                'action'       => $action,
                'target_type'  => $targetType,
                'target_id'    => $targetId,
                'target_label' => $targetLabel ? mb_substr($targetLabel, 0, 200) : null,
                'aciklama'     => $aciklama,
                'meta'         => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                'ip'           => $ip,
                'user_agent'   => mb_substr($ua, 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Log yazimi ana akisi kirmaz; ama sessizce kaybolmasin diye
            // sebebi laravel.log'a dusur (teshis icin).
            try {
                \Log::warning('Audit::logApi basarisiz', [
                    'action' => $action,
                    'salon_id' => $salonId,
                    'hata' => $e->getMessage(),
                ]);
            } catch (\Throwable $e2) {}
        }
    }
}
