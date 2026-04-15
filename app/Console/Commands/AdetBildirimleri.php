<?php

namespace App\Console\Commands;

use App\AdetDuzeni;
use App\Jobs\AdetBildirimJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AdetBildirimleri extends Command
{
    protected $signature = 'adet:bildirim-calistir';
    protected $description = 'Adet döngüsüne göre bildirim gönderir';

    public function handle()
    {
        Log::info("Adet bildirim cron çalıştı - " . now());

        AdetDuzeni::with('user')
            ->orderBy('id')
            ->chunkById(500, function ($adetler) {

                $bugun = Carbon::today();
                $suanSaat = Carbon::now()->format('H:i');

                foreach ($adetler as $adet) {

                    $baslangic = Carbon::parse($adet->baslangic_tarihi);
                    $bitis = Carbon::parse($adet->bitis_tarihi);

                    // Yaklaşıyor → Başlangıçtan 3 gün önce (14:00'te)
                    if ($bugun->equalTo($baslangic->copy()->subDays(3))) {
                        Log::info('3 gün önce adet dönemi hatırlatma');
                        dispatch(new AdetBildirimJob("Takvimin söylüyor: birkaç gün içinde regl başlıyor. Gerekli ürünleri hazır etmeyi unutmayın 🩸", $adet))->onQueue('hatirlatmalar');
                    }

                    // Yaklaşıyor → Başlangıçtan 2 gün önce (14:00'te)
                    if ($bugun->equalTo($baslangic->copy()->subDays(2))) {
                         Log::info('2 gün önce adet dönemi hatırlatma');
                        dispatch(new AdetBildirimJob("Regl dönemi yaklaşıyor. Ruh haliniz biraz değişebilir, kendinize minik molalar vermeyi unutmayın 💆‍♀", $adet))->onQueue('hatirlatmalar');
                    }

                    // PMS → Başlangıçtan 1 gün önce (14:00'te)
                    if ($bugun->equalTo($baslangic->copy()->subDay())) {
                         Log::info('1 gün önce adet dönemi hatırlatma');
                        dispatch(new AdetBildirimJob("Döngünüz yaklaşıyor 🌸 Küçük hatırlatma: bir ısıtma yastığı ve çikolata köşesi hazırda bulunsun 💕", $adet))->onQueue('hatirlatmalar');
                    }

                    // Başladı (14:00'te)
                    if ($bugun->equalTo($baslangic)) {
                        dispatch(new AdetBildirimJob("Adet döneminiz başladı 🌹 Takvim otomatik olarak güncellendi. Dinlenmeye vakit ayırmayı unutmayın 💗", $adet))->onQueue('hatirlatmalar');
                    }

                    // Bitiş (14:00'te)
                    if ($bugun->equalTo($bitis)) {
                        dispatch(new AdetBildirimJob("Döngünüzün bu dönemi sona eriyor 🌸 Enerjinizin artacağı günler yaklaşıyor!", $adet))->onQueue('hatirlatmalar');
                    }

                    // Ovulasyon → 14. gün (11:00'de)
                    if ($bugun->equalTo($baslangic->copy()->addDays(13))) {
                        dispatch(new AdetBildirimJob("Ovülasyon döneminiz başladı 💫 Bu günlerde enerji ve yaratıcılığınız yükselebilir!", $adet))->onQueue('hatirlatmalar');
                    }

                    // Genel motivasyon → her gün 1 defa (isteğe bağlı)
                    // dispatch(new AdetBildirimJob($adet->user, 'genel'));
                }
            });
    }
}
