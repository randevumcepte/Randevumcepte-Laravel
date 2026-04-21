<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class HatirlatmaAramaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $aramaListesi;
    protected $salonId;
    protected $kaynakId;

    public $timeout = 1800;
    public $tries = 3;

    public function __construct(array $aramaListesi, $salonId = null, $kaynakId = null)
    {
        $this->aramaListesi = $aramaListesi;
        $this->salonId = $salonId;
        $this->kaynakId = $kaynakId;
    }

    public function handle()
    {
        Log::info('Hatırlatma arama job çalışıyor. Salon: ' . $this->salonId .
            ', Kaynak: ' . $this->kaynakId .
            ', Kayıt: ' . count($this->aramaListesi));

        $controller = app()->make(Controller::class);

        try {
            $controller->hatirlatmaaramasiyap($this->aramaListesi);
            Log::info('Arama job başarıyla tamamlandı.');
        } catch (\Throwable $e) {
            Log::error('HatirlatmaAramaJob hata: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('HatirlatmaAramaJob tamamen başarısız oldu: ' . $exception->getMessage());
    }
}
