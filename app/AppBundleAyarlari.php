<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * app_bundle (beyaz etiket marka) bazlı ayarlar. Şu an: bundle_baslik (marka başlığı).
 */
class AppBundleAyarlari extends Model
{
    protected $table = 'app_bundle_ayarlari';

    protected $fillable = [
        'app_bundle',
        'bundle_baslik',
        // Genel (ortak) app sürüm kontrolü — bundle bazlı (per-salon değil)
        'android_son_versiyon',
        'ios_son_versiyon',
        'huawei_son_versiyon',
        'android_uygulama',
        'ios_uygulama',
        'huawei_uygulama',
    ];
}
