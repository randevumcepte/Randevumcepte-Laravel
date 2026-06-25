<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

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

      // ==== Yetkisiz erisim view composer ====
      //
      // 'isletmeadmin.yetkisizerisim' blade'i layout_isletmeadmin extend ediyor.
      // Layout, sidebar/header icin $isletme, $pageindex, $bildirimler vb.
      // degiskenler bekler. Controller'in 100+ yerinden parametresiz
      // 'view('isletmeadmin.yetkisizerisim')' cagrisi yapildigi icin, gereken
      // minimal degiskenleri otomatik enjekte ediyoruz — yeniden yazmamak icin.
      View::composer('isletmeadmin.yetkisizerisim', function ($view) {
          try {
              $user = \Auth::guard('isletmeyonetim')->user();
              $isletmeler = [];
              $isletme = null;
              if ($user) {
                  $rel = $user->yetkili_olunan_isletmeler;
                  // Sube switcher icin aktif isletmeler; $isletme fallback'i icin ise
                  // PASIF dahil tum isletmeler (kullanicinin tek isletmesi pasif olup
                  // 403 aliyorsa, layout yine de o isletme ile render olabilsin diye).
                  $isletmeler = $rel->where('aktif', 1)->pluck('salon_id')->map(function ($v) { return (int) $v; })->toArray();
                  $tumIds     = $rel->pluck('salon_id')->map(function ($v) { return (int) $v; })->toArray();
                  $fallbackIds = !empty($isletmeler) ? $isletmeler : $tumIds;
                  $secili = (int) request()->get('sube');
                  if ($secili && in_array($secili, $tumIds)) {
                      $isletme = \App\Salonlar::where('id', $secili)->first();
                  } else if (!empty($fallbackIds)) {
                      $isletme = \App\Salonlar::where('id', $fallbackIds[0])->first();
                  }
                  // Son care: kullanicinin hic isletme bagi yoksa bile layout 500
                  // vermesin diye istenen sube'yi dogrudan yukle (varsa).
                  if (!$isletme && $secili) {
                      $isletme = \App\Salonlar::where('id', $secili)->first();
                  }
              }
              // Gercek kalan uyelik suresi (gun). yetkisizerisim view'a deger
              // gecilmedigi icin eskiden sabit 999 gosteriliyordu.
              $kalanGun = 999;
              if ($isletme && !empty($isletme->uyelik_bitis_tarihi)) {
                  $kalanGun = (int) round((strtotime($isletme->uyelik_bitis_tarihi.' 23:59:59') - time()) / 86400);
              }
              $view->with([
                  'isletme'                => $isletme,
                  'bildirimler'            => collect(),
                  'pageindex'              => 0,
                  'kalan_uyelik_suresi'    => $kalanGun,
                  'yetkiliolunanisletmeler' => $isletmeler,
                  'sayfa_baslik'           => 'Yetkisiz İşlem',
              ]);
          } catch (\Throwable $e) {
              // Defansif: composer hata vermesin
              $view->with([
                  'isletme' => null, 'bildirimler' => collect(),
                  'pageindex' => 0, 'kalan_uyelik_suresi' => 999,
                  'yetkiliolunanisletmeler' => [], 'sayfa_baslik' => 'Yetkisiz İşlem',
              ]);
          }
      });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // WhatsAppService cagrildiginda salon basina dogru bridge'e yonlendiren
        // alt sinifi don. Mevcut WhatsAppService.php koduna HIC dokunulmuyor;
        // sadece container resolution noktasinda subclass donduruluyor.
        // Salonun whatsapp_bridge_tipi 'whatsmeow' ise istek port 3002'ye,
        // diger durumda 3001'e (Baileys) gider.
        $this->app->bind(
            \App\Services\WhatsAppService::class,
            \App\Services\WhatsAppRouterService::class
        );
    }
}
