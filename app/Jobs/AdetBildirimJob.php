<?php

namespace App\Jobs;

use App\AdetDuzeni;
use App\BildirimKimlikleri;
use App\Http\Controllers\BildirimController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdetBildirimJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adetDuzeni;
   
    protected $mesaj;

    public function __construct(string $mesaj,AdetDuzeni $adetDuzeni)
    {
        $this->adetDuzeni = $adetDuzeni;
         
        $this->mesaj = $mesaj;

    }

    public function handle()
    {
        Log::info('adet dönemi Job detayı '.json_encode($this->adetDuzeni->fresh()));
        try {
            $now = \Carbon\Carbon::now();
            Log::info('⏱ Adet hatırlatması çalışıyor: ' . $now->format('Y-m-d H:i'));

            $adetDuzeni = $this->adetDuzeni->fresh();
            $mesaj = $this->mesaj;

            // Sadece bildirim_id null olmayan ve boş olmayanları al
            $bildirimkimlikleri = BildirimKimlikleri::where('user_id', $adetDuzeni->user_id)
                ->whereNotNull('bildirim_id')
                ->where('bildirim_id', '<>', '')
                ->pluck('bildirim_id')
                ->filter() // null/empty string'leri temizle
                ->values()
                ->toArray();

            if (count($bildirimkimlikleri) > 0) {
                foreach ($bildirimkimlikleri as $bildirimKimligi) {
                    $baslik = "Regl Dönemi";
                   

                    Log::info('⏱ FCM token: ' . $bildirimKimligi);

                    // Controller'ı container üzerinden al
                    $controller = app(\App\Http\Controllers\BildirimController::class);
                    Log::info('SALON DATA ---> ', [Salonlar::where('id',$adetDuzeni->salon_id)->first()]);
                    // bildirimGonder (firebase json path, token, title, body)
                    $data = [
                        'category'=>'',
                        'buttons' => json_encode([]),
                        'userInfo'=>$adetDuzeni->user_id,
                        'salonId'=>$adetDuzeni->salon_id,
                        'bildirimlereGit'=>"0",

                        
                       


                    ];
                    $controller->bildirimGonder('app/firebase/randevumcepte-uygulamalar-0d38a7fc2d78.json', $bildirimKimligi, $baslik, $mesaj,$data,$adetDuzeni->salon_id,$adetDuzeni->user_id,'/public/yeni_panel/vendors/images/eczane24-icon.jpg','adet',null,null,$adetDuzeni->id,null);
                }
            } else {
                Log::info('⏱ Kullanıcı için fcm token bulunamadı. user_id: ' . $adetDuzeni->user_id);
            }
        } catch (\Throwable $e) {
            Log::error('Adet dönemi bildirimi hata: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            Log::error($e->getTraceAsString());
        }
        finally {
            // Job bitince bağlantıyı tamamen temizle
            DB::purge('mysql');
        }
    }
}