<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

/**
 * Bir grup hatirlatma aramasini (reklam / randevu / alacak) FreePBX santraline
 * iletir. Controller::hatirlatmaaramasiyap() AMI Originate gonderir.
 *
 * Tasarim notlari:
 * - $connection = 'database': global QUEUE_DRIVER=sync olsa bile bu job
 *   asenkron kuyruga gider. Ilac/adet/olcum joblari sync kalir (etkilenmez).
 *   Isleyici:  php artisan queue:work database --queue=hatirlatmalar,notifications
 * - $tries = 1 ve handle() ICINDE hata yutulur (rethrow YOK): bir job 50 kisiyi
 *   arar; retry edilirse o 50 numara TEKRAR aranir. Tekrar arama istemiyoruz;
 *   tek tek basari/hata isaretlemesi zaten Controller icinde yapiliyor.
 */
class HatirlatmaAramaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Connection ZORLANMAZ: mevcut prod worker'i (supervisor: randevumcepte-worker,
    // `queue:work --queue=hatirlatmalar`) default connection'i (prod .env
    // QUEUE_DRIVER=database) kullanir. Ilac/adet joblari gibi default'a birakiyoruz
    // ki ayni worker bu joblari da islesin. (Lokalde QUEUE_DRIVER=sync → inline.)
    // Kuyruk adi constructor'da onQueue() ile veriliyor.
    // DIKKAT: `public $queue = '...'` YAZMA. Queueable trait'i zaten `public $queue;`
    // (varsayilan null) tanimliyor; sinifta farkli bir varsayilanla yeniden tanimlamak
    // PHP 7.4'te FATAL verir ("define the same property ($queue) ... incompatible")
    // ve sinif hic yuklenemez.

    // $tries=1 KRITIK: worker CLI'da --tries=3 olsa da job-level deger onu ezer.
    // Bir job 50 kisiyi arar; retry = mukerrer arama. Asla retry etme.
    public $timeout = 1700;
    public $tries = 1;

    protected $aramaListesi;
    protected $salonId;
    protected $kaynakId;

    public function __construct(array $aramaListesi, $salonId = null, $kaynakId = null)
    {
        $this->onQueue('hatirlatmalar');
        $this->aramaListesi = $aramaListesi;
        $this->salonId = $salonId;
        $this->kaynakId = $kaynakId;
    }

    public function handle()
    {
        Log::info('[ARAMA-JOB] basladi', [
            'salon_id' => $this->salonId,
            'kaynak_id' => $this->kaynakId,
            'kayit' => count($this->aramaListesi),
        ]);

        $durum = 'basarili';
        try {
            $controller = app()->make(Controller::class);
            $controller->hatirlatmaaramasiyap($this->aramaListesi);
            Log::info('[ARAMA-JOB] tamamlandi', ['salon_id' => $this->salonId]);
        } catch (\Throwable $e) {
            // Bilerek rethrow YOK: retry = mukerrer arama. Sadece logla.
            $durum = 'hata';
            Log::error('[ARAMA-JOB] hata (retry yok): ' . $e->getMessage(), [
                'salon_id' => $this->salonId,
                'kaynak_id' => $this->kaynakId,
            ]);
        }

        $this->istatistikYaz($durum);
    }

    /** Best-effort istatistik kaydi; basarisizlik aramayi etkilemez. */
    protected function istatistikYaz($durum)
    {
        try {
            \DB::table('arama_istatistikleri')->insert([
                'salon_id' => $this->salonId,
                'kampanya_id' => $this->kaynakId,
                'toplam_arama' => count($this->aramaListesi),
                'durum' => $durum,
                'tamamlanma_tarihi' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[ARAMA-JOB] istatistik yazilamadi: ' . $e->getMessage());
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('[ARAMA-JOB] failed(): ' . $exception->getMessage());
    }
}
