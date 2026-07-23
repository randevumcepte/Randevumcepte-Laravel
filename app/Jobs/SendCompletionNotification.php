<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Personeller;
use App\BildirimKimlikleri;
use App\Http\Controllers\BildirimController;

/**
 * Reklam (kampanya) aramalari kuyruga eklendikten sonra salon yoneticilerine
 * "tamamlandi" push bildirimi gonderir. Asenkron (database connection) calisir.
 */
class SendCompletionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Mevcut worker yalnizca `hatirlatmalar` kuyrugunu izledigi icin bu bildirim
    // de ayni kuyruga konur (default connection — bkz. HatirlatmaAramaJob notu).
    public $queue = 'hatirlatmalar';
    public $tries = 2;

    protected $toplamArama;
    protected $salonId;
    protected $kampanyaId;

    public function __construct($toplamArama, $salonId = null, $kampanyaId = null)
    {
        $this->toplamArama = $toplamArama;
        $this->salonId = $salonId;
        $this->kampanyaId = $kampanyaId;
    }

    public function handle()
    {
        if (!$this->salonId) {
            return;
        }

        $personelIdler = Personeller::where('salon_id', $this->salonId)->pluck('id')->toArray();
        // Brand izolasyonu: aynı personel farklı brand build'inde de kayıtlıysa
        // sadece $this->salonId'in app_bundle'ini yuklu cihazlarina gonder.
        $tokenlar = BildirimKimlikleri::whereIn('isletme_yetkili_id', $personelIdler)
            ->forBrand($this->salonId)
            ->whereNotNull('bildirim_id')
            ->get();

        $bildirim = app(BildirimController::class);

        foreach ($tokenlar as $token) {
            $data = [
                'category' => 'reklam',
                'buttons' => json_encode([]),
                'userInfo' => '',
                'salonId' => $this->salonId,
                'bildirimlereGitYonetici' => '1',
                'kullaniciRolu' => Personeller::where('id', $token->isletme_yetkili_id)->value('role_id'),
            ];

            try {
                $bildirim->bildirimGonder(
                    'app/firebase/randevumcepte-uygulamalar-0d38a7fc2d78.json',
                    $token->bildirim_id,
                    'Reklam Kampanyasi Aramalari Tamamlandi',
                    $this->toplamArama . ' kisiye kampanya aramasi kuyruga eklendi.',
                    $data,
                    $this->salonId,
                    null,
                    '/public/yeni_panel/vendors/images/icon.png',
                    'kampanya',
                    null,
                    null,
                    null,
                    null
                );
            } catch (\Throwable $e) {
                Log::warning('[REKLAM-BILDIRIM] tamamlandi push fail: ' . $e->getMessage());
            }
        }

        Log::info('[REKLAM-BILDIRIM] tamamlandi bildirimi gonderildi', [
            'salon_id' => $this->salonId,
            'token' => count($tokenlar),
        ]);
    }
}
