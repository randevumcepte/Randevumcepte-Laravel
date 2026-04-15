<?php
namespace App\Jobs;

use App\Olcumler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\BildirimKimlikleri;
use Illuminate\Support\Facades\Log;
use App\Salonlar;

class OlcumHatirlatmaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $hatirlatma;

    public function __construct(Olcumler $hatirlatma)
    {
        $this->hatirlatma = $hatirlatma;
    }

    public function handle()
    {
        Log::info('Job detayı '.json_encode($this->hatirlatma->fresh()));
        try {
            $now = \Carbon\Carbon::now();
            Log::info('⏱ Hatırlatma çalışıyor: ' . $now->format('Y-m-d H:i'));

            $hatirlatma = $this->hatirlatma->fresh();

            // Sadece bildirim_id null olmayan ve boş olmayanları al
            $bildirimkimlikleri = BildirimKimlikleri::where('user_id', $hatirlatma->user_id)
                ->whereNotNull('bildirim_id')
                ->where('bildirim_id', '<>', '')
                ->pluck('bildirim_id')
                ->filter() // null/empty string'leri temizle
                ->values()
                ->toArray();

            if (count($bildirimkimlikleri) > 0) {
                foreach ($bildirimkimlikleri as $bildirimKimligi) {
                    $baslik = $hatirlatma->olcumTuru->olcum_turu . ' Ölçüm Hatırlatması';
                    $mesaj = 'Lütfen ölçümünüzü yapmayı unutmayın.';

                    Log::info('⏱ FCM token: ' . $bildirimKimligi);

                    // Controller'ı container üzerinden al
                    $controller = app(\App\Http\Controllers\BildirimController::class);
                    Log::info('SALON DATA ---> ', [Salonlar::where('id',$hatirlatma->salon_id)->first()]);
                    // bildirimGonder (firebase json path, token, title, body)
                    $data = [
                        'category'=>'   ',
                        'buttons' => json_encode([
                            ['title' => 'Ölçüm Yap', 'action' => 'olcum_yap'],
                            ['title' => '30dk Sonra', 'action' => 'olcum_30_dk_ertele'],
                            
                        ]),
                        'userId'=>$hatirlatma->user_id,
                        'salonId'=>$hatirlatma->salon_id,
                        'olcum'=>"1",

                        
                       


                    ];
                    $controller->bildirimGonder('app/firebase/randevumcepte-uygulamalar-0d38a7fc2d78.json', $bildirimKimligi, $baslik, $mesaj,$data,$hatirlatma->salon_id,$hatirlatma->user_id,'/public/yeni_panel/vendors/images/eczane24-icon.jpg','olcumler',$hatirlatma->id,null,null,null);
                }
            } else {
                Log::info('⏱ Kullanıcı için fcm token bulunamadı. user_id: ' . $hatirlatma->user_id);
            }
        } catch (\Throwable $e) {
            Log::error('OlcumHatirlatmaJob hata: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            Log::error($e->getTraceAsString());
        }
    }
}
