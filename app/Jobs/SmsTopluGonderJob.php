<?php

namespace App\Jobs;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SMSController;
use App\Salonlar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * v2 SMS Yonetimi toplu/filtreli gonderim (outbox 'sms_toplu'). Alicilar v2'de cozulur (portfoy, kapsam,
 * kara liste); burada yalniz MEVCUT gonderim kodu calisir — v1 sms-yonetim/toplu-gonder ile ayni yol:
 *   yeni_sms=1 → SMSController::cokluSMSGonderVoiceTelekom (tek paket, baslik raporda turu belirler)
 *   aksi halde → ApiController::sms_gonder_bildirimli (WA-first + Efetech, iletim raporu $tur ile)
 *
 * BildirimReklamGonderJob ile ayni kurallar: kuyruk 'hatirlatmalar', $tries=1 (retry = MUKERRER ucretli SMS),
 * hatalar handle() icinde yutulur. WA-first dongusu binlerce alicida outbox'u kilitlemesin diye job'a verilir.
 */
class SmsTopluGonderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // `public $queue` YAZMA (Queueable ile cakisir, PHP 7.4 FATAL) — kuyruk constructor'da.
    public $timeout = 1700;
    public $tries = 1;

    protected $salonId;
    protected $telefonlar;
    protected $mesaj;
    protected $tur;
    protected $baslik;

    public function __construct($salonId, array $telefonlar, $mesaj, $tur, $baslik)
    {
        $this->salonId = (int) $salonId;
        $this->telefonlar = array_values($telefonlar);
        $this->mesaj = (string) $mesaj;
        $this->tur = (int) $tur;
        $this->baslik = (string) $baslik;
        $this->onQueue('hatirlatmalar');
    }

    public function handle()
    {
        @set_time_limit(0);
        $log = ['salon_id' => $this->salonId, 'tur' => $this->tur, 'alici' => count($this->telefonlar)];
        try {
            $salon = Salonlar::where('id', $this->salonId)->first();
            if (!$salon) {
                Log::warning('[SMS-TOPLU] salon yok', $log);
                return;
            }
            if ((int) $salon->yeni_sms === 1) {
                $sonuc = app()->make(SMSController::class)
                    ->cokluSMSGonderVoiceTelekom($this->telefonlar, $this->mesaj, $this->salonId, $this->baslik);
            } else {
                $mesaj = $this->mesaj;
                $mesajlar = array_map(function ($to) use ($mesaj) {
                    return ['to' => $to, 'message' => $mesaj];
                }, $this->telefonlar);
                $sonuc = app(ApiController::class)->sms_gonder_bildirimli(new Request(), $mesajlar, true, $this->tur, false, $this->salonId);
            }
            Log::info('[SMS-TOPLU] v2', $log + ['sonuc' => $sonuc]);
        } catch (\Throwable $e) {
            Log::error('[SMS-TOPLU] hata', $log + ['hata' => $e->getMessage()]);
        }
    }
}
