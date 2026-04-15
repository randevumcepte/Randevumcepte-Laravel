<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Olcumler;
use App\Jobs\OlcumHatirlatmaJob;
use Carbon\Carbon;

class AdetDonemiBildirimleri extends Command
{
    protected $signature = 'adetdonemi:bildirimleri';
    protected $description = 'Adet dönemlerini kontrol eder ve gerekli Job’ları tetikler ve belirli periyotlarla bildirimler gönderir.';

    public function handle()
    {
        $now = Carbon::now()->setTime(
            Carbon::now()->hour,
            Carbon::now()->minute,
            0
        );
        $this->info('⏱ Hatırlatma kontrolü: ' . $now->format('Y-m-d H:i'));
        Log::info('⏱ Hatırlatma kontrolü: ' . $now->format('Y-m-d H:i'));

        Olcumler::chunk(500, function ($hatirlatmalar) use ($now) { 
                foreach ($hatirlatmalar as $hatirlatma) {
                    if ($this->tetiklenmeliMi($hatirlatma, $now)) {
                         Log::info('⏱ tetiklemek üzere eklendi: ' . $now->format('Y-m-d H:i'));
                        OlcumHatirlatmaJob::dispatch($hatirlatma)->onQueue('hatirlatmalar');
                    }
                }
            });

        $this->info('✅ Kontrol tamamlandı.');
    }

    private function tetiklenmeliMi($hatirlatma, Carbon $now)
    {
        // Şu anki saat, kaydedilen saatlerden biri mi?
        $simdikiSaat = $now->format('H:i');
        $saatler = array_map('trim', explode(',', $hatirlatma->olcum_saatleri));
        Log::info( $simdikiSaat. ' - '.json_encode($saatler));
        if (!in_array($simdikiSaat, $saatler)) {
            return false;
        }

        // created_at başlangıç kabul edilir
        $baslangic = Carbon::parse($hatirlatma->created_at);
        $tetikle = false;

        switch ($hatirlatma->olcum_sikligi) {
            case 'her_gun':
            Log::info('⏱ her gün');
                $tetikle = true;
                break;

            case 'her_x_gun':
                Log::info('⏱ her x gün');
                if ($hatirlatma->her_x_gun > 0) {
                    $fark = $baslangic->diffInDays($now);
                    if ($fark % intval($hatirlatma->her_x_gun) === 0) {
                        $tetikle = true;
                    }
                }
                break;

            case 'haftanin_gunleri':
            Log::info('⏱ haftanın günleri');

                if ($hatirlatma->haftanin_gunleri) {
                    $gunler = json_decode($hatirlatma->haftanin_gunleri, true) ?? [];
                    $bugunStr = $now->translatedFormat('l'); // Örn: Pazartesi
                    // Türkçe çeviri desteği
                    $bugunStr = $this->turkceGun($bugunStr);
                    if (in_array($bugunStr, $gunler)) {
                        $tetikle = true;
                    }
                }
                break;
        }

        return $tetikle;
    }

    private function turkceGun($englishDay)
    {
        $map = [
            'Monday' => 'Pazartesi',
            'Tuesday' => 'Salı',
            'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe',
            'Friday' => 'Cuma',
            'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar',
        ];
        return $map[$englishDay] ?? $englishDay;
    }
}
