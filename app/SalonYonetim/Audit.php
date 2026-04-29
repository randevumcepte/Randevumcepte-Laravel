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
}
