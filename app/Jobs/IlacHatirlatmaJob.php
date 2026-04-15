<?php
namespace App\Jobs;

use App\Ilac;
use App\BildirimKimlikleri;
use App\Http\Controllers\BildirimController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Salonlar;

class IlacHatirlatmaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ilac;

    public function __construct(Ilac $ilac)
    {
        $this->ilac = $ilac;
    }

    public function handle()
    {
        try {
            $ilac = $this->ilac->fresh();

            Log::info("💊 İlaç Hatırlatma Çalıştı → {$ilac->adi}");

            // Kullanıcı tokenlarını al
            $tokens = BildirimKimlikleri::where('user_id', $ilac->user_id)
                ->whereNotNull('bildirim_id')
                ->pluck('bildirim_id')
                ->filter()
                ->toArray();

            if (!count($tokens)) {
                Log::info("💊 Kullanıcı için token yok → user_id: {$ilac->user_id}");
                return;
            }

            // --- 📌 NORMAL İLAÇ BİLDİRİMİ ---
            foreach ($tokens as $token) {
                    $controller = app(\App\Http\Controllers\BildirimController::class);

                $data = [
                    'category' => 'ilac',
                    'buttons' => json_encode([
                        ['title' => 'Onayla', 'action' => 'ilac_onayla'],
                        ['title' => '30dk Sonra', 'action' => 'ilac_30_dk_ertele'],
                       
                    ]),
                    'userInfo' => json_encode($ilac),
                ];

                $controller->bildirimGonder(
                    'app/firebase/randevumcepte-uygulamalar-0d38a7fc2d78.json',
                    $token,
                    "💊 {$ilac->adi} İlacı",
                    "{$ilac->adi} ilacını alma zamanı! Kalan: {$ilac->kalan_adet} adet",
                    $data,
                    null,
                    $ilac->user_id,
                    '/public/yeni_panel/vendors/images/eczane24-icon.jpg',
                    'ilaclar',
                    null,
                    $ilac->id,
                    null,
                    null
                );
            }

            Log::info("💊 İlaç bildirimi gönderildi → {$ilac->adi}");

            // --- 📌 BİTMEK ÜZERE UYARISI (Sadece bilgilendirme) ---
            if ($ilac->kalan_adet <= 3 && $ilac->kalan_adet > 0) {
                foreach ($tokens as $token) {
                    $data = [
                        'category' => 'ilac_bitmek_uzere',
                        'buttons' => json_encode([
                            ['title' => 'Tamam', 'action' => 'tamam']
                        ]),
                        'userInfo'=>$ilac->user_id,
                        'salonId'=>$ilac->salon_id,
                        'bildirimlereGit'=>"1",                    ];
 
                    app(\App\Http\Controllers\BildirimController::class)->bildirimGonder(
                        'app/firebase/randevumcepte-uygulamalar-0d38a7fc2d78.json',
                        $token,
                        "⚠️ İlacınız Bitmek Üzere",
                        "{$ilac->adi} ilacınızın sadece {$ilac->kalan_adet} adet kaldı! Lütfen yeni ilaç temin edin.",
                        $data,
                        null,
                        $ilac->user_id,
                        '/public/yeni_panel/vendors/images/eczane24-icon.jpg',
                        'ilac_bitmek_uzere',
                        $ilac->id,
                        null,
                        null,
                        null
                    );
                }

                Log::info("⚠️ Bitmek üzere bildirimi gönderildi → {$ilac->adi}");
            }

        } catch (\Throwable $e) {
            Log::error("💊 İlaç JOB HATASI: ".$e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}