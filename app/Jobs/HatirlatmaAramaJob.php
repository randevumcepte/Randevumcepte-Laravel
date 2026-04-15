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

    public function __construct(array $aramaListesi)
    {
        $this->aramaListesi = $aramaListesi;
    }

    public function handle()
    {
        $controller = app()->make(Controller::class);

        Log::info('Hatırlatma arama job çalışıyor. (' . count($this->aramaListesi) . ' kayıt)');
        try {
            $controller->hatirlatmaaramasiyap($this->aramaListesi);
        } catch (\Throwable $e) {
            Log::error('HatirlatmaAramaJob hata: ' . $e->getMessage());
        }
    }
}
