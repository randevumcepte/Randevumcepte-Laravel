<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      ini_set('memory_limit', env('PHP_MEMORY_LIMIT', '1024M'));
      ini_set('max_execution_time', env('PHP_MAX_EXECUTION_TIME', 300)); // 60 saniye varsayılan

      // ==== Yetki Blade directive'leri ====
      //
      // Kullanim:
      //   @yetki('musteri.ekle_duzenle')
      //     <button>Yeni Musteri</button>
      //   @endyetki
      //
      //   @yetkisiz('musteri.sil')
      //     <span>Silme yetkiniz yok</span>
      //   @endyetkisiz
      //
      // Iceride giris yapan yetkili (isletmeyonetim guard) + secili sube'ye
      // gore PersonelYetkiServisi::yetkiliYetkiVar cagrilir.
      Blade::if('yetki', function ($key) {
          $user = \Auth::guard('isletmeyonetim')->user()
              ?? \Auth::guard('isletmeyonetim-api')->user();
          if (!$user) return true; // auth yoksa varsayilan: gozuksun
          // Salon id kaynaklari (StoreAdminController::mevcutsube ile uyumlu):
          //   1) request->sube / salon_id
          //   2) session('salon_id')
          //   3) auth user'in ilk yetkili oldugu sube
          $salonId = request()->get('sube')
              ?? request()->get('salon_id')
              ?? session('salon_id');
          if (!$salonId) {
              try {
                  $salonId = $user->yetkili_olunan_isletmeler->pluck('salon_id')->first();
              } catch (\Throwable $e) {
                  $salonId = null;
              }
          }
          if (!$salonId) return true;
          try {
              return \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(
                  $user->id, $salonId, $key
              );
          } catch (\Throwable $e) {
              return true;
          }
      });

      // Tutar gosterimi icin Blade directive — ornek:
      //   {!! tutar(personel.maas_tutar_gor, $tutar) !!}
      // helper'i daha temiz oldugundan directive yerine global function tercih
      // edilebilir; ileride ihtiyac olursa eklenir.
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
