<?php

namespace App\SistemYonetim;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Audit
{
    /**
     * Aktiviteyi kaydet.
     *
     * @param string $action       login | logout | salon_login | salon_pasif | salon_aktif | not_ekle | ticket_yanit | kullanici_olustur | kullanici_guncelle | rol_degisikligi
     * @param string|null $targetType salon | isletme_yetkili | sistem_yoneticisi | ticket | not
     * @param int|null $targetId
     * @param string|null $targetLabel
     * @param string|null $aciklama
     * @param array $meta         eski/yeni veri vs.
     */
    public static function log($action, $targetType = null, $targetId = null, $targetLabel = null, $aciklama = null, array $meta = [])
    {
        try {
            $user = Auth::guard('sistemyonetim')->user();
            AuditLog::create([
                'user_id'      => $user ? $user->id : null,
                'user_name'    => $user ? $user->name : null,
                'user_rol'     => $user ? ($user->rol ?: ($user->admin == 1 ? 'super_admin' : 'destek')) : null,
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
            // log yazimi sessizce gecsin
        }
    }
}
