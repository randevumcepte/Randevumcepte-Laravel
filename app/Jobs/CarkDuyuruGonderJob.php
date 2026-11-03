<?php

namespace App\Jobs;

use App\Services\CarkDuyuruServisi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * v2 Çarkıfelek "Müşterilere Duyur" (outbox 'cark_duyuru') — gönderimi CarkDuyuruServisi yapar.
 * BildirimReklamGonderJob ile aynı kurallar: kuyruk 'hatirlatmalar' (prod'da onu dinleyen tek
 * worker), $tries=1 (retry = MÜKERRER push), hatalar handle() içinde yutulur. Çok alıcılı döngü
 * outbox'u kilitlemesin diye job'a verilir.
 */
class CarkDuyuruGonderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // `public $queue` YAZMA (Queueable ile çakışır, PHP 7.4 FATAL) — kuyruk constructor'da.
    public $timeout = 1700;
    public $tries = 1;

    protected $salonId;
    protected $baslik;
    protected $mesaj;

    public function __construct($salonId, $baslik, $mesaj)
    {
        $this->salonId = (int) $salonId;
        $this->baslik = (string) $baslik;
        $this->mesaj = (string) $mesaj;
        $this->onQueue('hatirlatmalar');
    }

    public function handle()
    {
        @set_time_limit(0);
        try {
            $s = CarkDuyuruServisi::gonder($this->salonId, $this->baslik, $this->mesaj);
            Log::info('[CARK-DUYURU] v2', ['salon_id' => $this->salonId] + $s);
        } catch (\Throwable $e) {
            Log::error('[CARK-DUYURU] hata', ['salon_id' => $this->salonId, 'hata' => $e->getMessage()]);
        }
    }
}
