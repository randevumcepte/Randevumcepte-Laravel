<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use App\KampanyaYonetimi;
use App\SalonEAsistanAyarlari;
use App\Jobs\HatirlatmaAramaJob;
use App\Jobs\SendCompletionNotification;
use App\BildirimKimlikleri;
use App\Personeller;

class KampanyaAramaYap extends Command
{
    protected $signature = 'kampanyaarama:yap';
    protected $description = 'Kampanya Aramalarını Gerçekleştirir (batch + queue)';
    
    public function handle()
    {
        Log::info('Kampanya arama kontrolü başlatıldı.');
        
        $kampanyalar = KampanyaYonetimi::where('asistan_tarih_saat', '<=', now())
            ->where('aktifmi', 1)
            ->where('arama_ile_gonderim', 1)
            ->with(['salon:id,santral_telaffuz_hatirlatma_aramasi'])
            ->get();
        
        Log::info('Kampanya sayısı: ' . $kampanyalar->count());
        
        foreach ($kampanyalar as $kampanya) {
            $this->processKampanya($kampanya);
        }
        
        Log::info('Kampanya arama kontrolü tamamlandı.');
    }
    
    protected function processKampanya($kampanya)
    {
        $ayarAcikMi = SalonEAsistanAyarlari::where('salon_id', $kampanya->salon_id)
            ->where('ayar_id', 8)
            ->value('acik_kapali');
        
        if (!$ayarAcikMi) {
            Log::info("Salon {$kampanya->salon_id} için arama ayarı kapalı.");
            return;
        }
        
        Log::info("Salon {$kampanya->salon_id} için kampanya araması başlıyor.");
        
        // Tüm arama listesini topla
        $tumAramaListeleri = [];
        
        $kampanya->kampanya_katilimcilari()
            ->with(['musteri:id,name,cinsiyet,cep_telefon'])
            ->select('id', 'user_id', 'kampanya_id', 'tekrar_arandi', 'tekrar_aranacak', 'tekrar_arama_tarih_saat')
            ->chunk(200, function ($katilimcilar) use ($kampanya, &$tumAramaListeleri) {
                
                $aramaListesi = $this->prepareAramaListesi($katilimcilar, $kampanya);
                
                if (count($aramaListesi) > 0) {
                    // 200'erli chunk'ları birleştir
                    $tumAramaListeleri = array_merge($tumAramaListeleri, $aramaListesi);
                }
            });
        
        if (empty($tumAramaListeleri)) {
            Log::info('Bu kampanya için arama yapılacak müşteri yok.');
            return;
        }
        
        Log::info("Toplam " . count($tumAramaListeleri) . " arama bulundu.");
        
        // Batch işlemi başlat
        $this->startBatchArama($tumAramaListeleri, $kampanya);
    }
    
    protected function prepareAramaListesi($katilimcilar, $kampanya)
    {
        $aramaListesi = [];
        
        foreach ($katilimcilar as $katilimci) {
            $aramaTarihSaatiIcinde = now()->format('d.m.Y H:i') >= date('d.m.Y H:i', strtotime($kampanya->asistan_tarih_saat));
            $tekrarArandi = $katilimci->tekrar_arandi;
            $tekrarAranacak = $katilimci->tekrar_aranacak;
            
            if ((($aramaTarihSaatiIcinde && is_null($tekrarArandi) && is_null($tekrarAranacak)) ||
                ($tekrarAranacak == 1 && date('d.m.Y H:i', strtotime($katilimci->tekrar_arama_tarih_saat)) == now()->format('d.m.Y H:i')))
                && !$kampanya->hatirlatma_gorevi_iptal) {
                
                $cinsiyetStr = $katilimci->musteri->cinsiyet === 0 ? 'hanım' : 'bey';
                $ilkAd = explode(' ', $katilimci->musteri->name)[0];
                $musteriAdStr = $ilkAd . ' ' . $cinsiyetStr;
                
                $aramaListesi[] = [
                    "alacakIdler" => "",
                    "randevuid" => "",
                    "kampanyaKatilimci" => $katilimci->id,
                    "katilimci" => $katilimci->id,
                    "mesaj" => "Merhaba " . $musteriAdStr . ". Sizi " .
                        $kampanya->salon->santral_telaffuz_hatirlatma_aramasi . " arıyorum. Umarım gününüz sağlıklı geçiyordur. " .
                        $kampanya->mesaj,
                    "tel" => $katilimci->musteri->cep_telefon,
                    "salonId" => $kampanya->salon_id,
                    "exten" => 3,
                ];
            }
        }
        
        return $aramaListesi;
    }
    
    protected function startBatchArama($aramaListesi, $kampanya)
    {
        // Başlangıç bildirimi gönder
        $this->sendStartNotification($kampanya);
        
        // Aramaları 50'şerli gruplara böl (channel limiti için)
        $chunks = array_chunk($aramaListesi, 50);
        $jobs = [];
        
        foreach ($chunks as $chunkIndex => $chunk) {
            $jobs[] = new HatirlatmaAramaJob($chunk, $kampanya->salon_id, $kampanya->id);
            
            // Her chunk arasında 30 saniye boşluk bırak
            if ($chunkIndex < count($chunks) - 1) {
                $jobs[] = function () {
                    sleep(30); // Kanalların boşalması için bekle
                };
            }
        }
        
        // Batch oluştur
        $batch = Bus::batch($jobs)
            ->then(function (Illuminate\Bus\Batch $batch) use ($kampanya, $aramaListesi) {
                // Başarılı tamamlanma
                Log::info('Batch işlemi başarıyla tamamlandı: ' . $batch->id);
                
                // Tamamlama bildirimi gönder
                SendCompletionNotification::dispatch(
                    count($aramaListesi), 
                    $kampanya->salon_id, 
                    $kampanya->id
                )->onQueue('notifications');
                
            })->catch(function (Illuminate\Bus\Batch $batch, \Throwable $e) use ($kampanya) {
                // Hata durumu
                Log::error('Batch işlemi hatası: ' . $e->getMessage());
                
                // Hata bildirimi gönder
                $this->sendErrorNotification($kampanya, $e->getMessage());
                
            })->finally(function (Illuminate\Bus\Batch $batch) use ($kampanya) {
                // Her durumda çalışacak kod
                Log::info('Batch işlemi sonlandı: ' . $batch->id . ' - Salon: ' . $kampanya->salon_id);
                
            })->name('Kampanya Aramaları - Salon: ' . $kampanya->salon_id)
              ->onQueue('hatirlatmalar')
              ->dispatch();
        
        Log::info('Batch işlemi başlatıldı. ID: ' . $batch->id . ', Chunk sayısı: ' . count($chunks));
        
        return $batch->id;
    }
    
    protected function sendStartNotification($kampanya)
    {
        $personeller = Personeller::where('salon_id', $kampanya->salon_id)->pluck('id')->toArray();
        $bildirimKimlikleri = BildirimKimlikleri::whereIn('isletme_yetkili_id', $personeller)
            ->whereNotNull('bildirim_id')
            ->get();
        
        foreach ($bildirimKimlikleri as $token) {
            $data = [
                'category' => 'reklam',
                'buttons' => json_encode([]),
                'userInfo' => '',
                'salonId' => $kampanya->salon_id,
                'bildirimlereGitYonetici' => "1",
                'kullaniciRolu' => Personeller::where('id', $token->isletme_yetkili_id)->value('role_id')
            ];
            
            app(\App\Http\Controllers\BildirimController::class)->bildirimGonder(
                'app/firebase/randevumcepte-uygulamalar-0d38a7fc2d78.json',
                $token->bildirim_id,
                'Reklam Kampanyası Araması Başlatıldı',
                $kampanya->paket_isim . ' için hastaların aranması başlatılmıştır.',
                $data,
                $kampanya->salon_id,
                null,
                '/public/yeni_panel/vendors/images/eczane24-icon.jpg',
                'kampanya',
                null,
                null,
                null,
                null,
                $token->isletme_yetkili_id,
                $kampanya->id
            );
        }
    }
    
    protected function sendErrorNotification($kampanya, $errorMessage)
    {
        // Hata bildirimi gönderme kodu
        // ...
    }
}