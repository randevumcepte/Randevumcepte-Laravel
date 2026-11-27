<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Personeller;

/**
 * Reklam (kampanya) aramalari kuyruga eklendikten sonra salon yoneticilerine
 * "tamamlandi" push bildirimi gonderir. Asenkron (database connection) calisir.
 */
class SendCompletionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Mevcut worker yalnizca `hatirlatmalar` kuyrugunu izledigi icin bu bildirim
    // de ayni kuyruga konur (default connection — bkz. HatirlatmaAramaJob notu).
    // Kuyruk adi constructor'da onQueue() ile. DIKKAT: `public $queue = '...'` YAZMA;
    // Queueable trait'i `public $queue;` tanimladigi icin PHP 7.4'te FATAL verir
    // ("define the same property ($queue) ... incompatible") ve sinif yuklenemez.
    public $tries = 2;

    protected $toplamArama;
    protected $salonId;
    protected $kampanyaId;

    public function __construct($toplamArama, $salonId = null, $kampanyaId = null)
    {
        $this->onQueue('hatirlatmalar');
        $this->toplamArama = $toplamArama;
        $this->salonId = $salonId;
        $this->kampanyaId = $kampanyaId;
    }

    public function handle()
    {
        if (!$this->salonId) {
            return;
        }

        // NotificationService: salonun KENDI Firebase projesiyle (firebase_profile) ve gorunur bildirim
        // olarak gider. Eskiden sabit 'uygulamalar' projesiyle yalniz-veri gonderiliyordu: baska projedeki
        // markalarda FCM SENDER_ID_MISMATCH donup personelin cihaz kaydi siliniyordu, arka planda da
        // bildirim ekranda hic gorunmuyordu.
        $personelIdler = Personeller::where('salon_id', $this->salonId)->pluck('id')->toArray();
        $gonderilen = 0;
        foreach ($personelIdler as $pid) {
            try {
                $r = \App\Services\NotificationService::toStaff((int) $pid, (int) $this->salonId)
                    ->type(\App\Services\NotificationTypes::REKLAM_KAMPANYA)
                    ->title('Reklam Kampanyası Aramaları Tamamlandı')
                    ->body($this->toplamArama . ' kişiye kampanya araması kuyruğa eklendi.')
                    ->extra(['kampanya_id' => (string) ($this->kampanyaId ?? '')])
                    ->send();
                $gonderilen += (int) ($r['sent'] ?? 0);
            } catch (\Throwable $e) {
                Log::warning('[REKLAM-BILDIRIM] tamamlandi push fail: ' . $e->getMessage());
            }
        }
        Log::info('[REKLAM-BILDIRIM] tamamlandi bildirimi gonderildi', [
            'salon_id' => $this->salonId,
            'gonderilen_cihaz' => $gonderilen,
        ]);
    }
}
