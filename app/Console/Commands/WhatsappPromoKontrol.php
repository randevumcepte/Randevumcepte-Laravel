<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp "2 Ay Ücretsiz" tanıtım (promo) yöneticisi.
 *
 *  1) BAŞLATMA: Ücretli (pro/premium) olmayan ve henüz promo başlatılmamış
 *     salonlara 2 aylık ücretsiz WhatsApp tanıtımını tanımlar.
 *  2) SÜRE DOLUMU: Ücretsiz süresi biten salonlarda WhatsApp'ı otomatik
 *     devre dışı bırakır (whatsapp_aktif = 0) ve promo'yu kapatır.
 *
 * Hesabım > Aldığım Hizmetler ekranındaki WhatsApp kartı ve panelde son 5 gün
 * uyarısı bu kolonlardan beslenir (bkz. Salonlar::whatsappPromoBilgisi()).
 *
 * Schedule: dailyAt('00:30') — günde tek tetik, idempotent.
 */
class WhatsappPromoKontrol extends Command
{
    protected $signature = 'whatsapp:promo-kontrol';
    protected $description = 'WhatsApp 2 ay ucretsiz promosunu baslatir ve suresi dolanlari devre disi birakir';

    public function handle()
    {
        // Prod'da deploy sadece "git pull" yaptigindan migration otomatik
        // calismayabilir. Kolonlari idempotent sekilde kendimiz garanti ediyoruz.
        $this->kolonlariGarantiEt();

        if (!Schema::hasColumn('salonlar', 'whatsapp_promo_baslangic')) {
            $this->warn('whatsapp_promo_* kolonlari olusturulamadi.');
            return 0;
        }

        $bugun = date('Y-m-d');

        // 1) BAŞLATMA — ücretli olmayan ve promo başlatılmamış salonlar
        $baslatilan = DB::table('salonlar')
            ->whereNull('whatsapp_promo_baslangic')
            ->where(function ($q) {
                $q->whereNull('whatsapp_paket')
                  ->orWhereNotIn('whatsapp_paket', ['pro', 'premium']);
            })
            ->update([
                'whatsapp_promo_baslangic' => $bugun,
                'whatsapp_promo_bitis'     => date('Y-m-d', strtotime('+2 months')),
                'whatsapp_promo_aktif'     => 1,
                'whatsapp_promo_kapatildi' => 0,
            ]);

        // 2) SÜRE DOLUMU — promosu aktif ama bitiş tarihi geçmiş salonlar
        $sonlananlar = DB::table('salonlar')
            ->where('whatsapp_promo_aktif', 1)
            ->whereNotNull('whatsapp_promo_bitis')
            ->whereDate('whatsapp_promo_bitis', '<', $bugun)
            // Ücretli pakete geçenler etkilenmesin
            ->where(function ($q) {
                $q->whereNull('whatsapp_paket')
                  ->orWhereNotIn('whatsapp_paket', ['pro', 'premium']);
            })
            ->pluck('id');

        $kapatilan = 0;
        if ($sonlananlar->count() > 0) {
            $kapatilan = DB::table('salonlar')
                ->whereIn('id', $sonlananlar)
                ->update([
                    'whatsapp_promo_aktif'     => 0,
                    'whatsapp_promo_kapatildi' => 1,
                    'whatsapp_aktif'           => 0, // ödeme yoksa WhatsApp otomatik kapanır
                ]);
        }

        Log::info('[WA-PROMO] kontrol', [
            'baslatilan' => $baslatilan,
            'kapatilan'  => $kapatilan,
            'salon_ids'  => $sonlananlar->toArray(),
        ]);

        $this->info("Promo baslatilan: {$baslatilan} | suresi dolup kapatilan: {$kapatilan}");
        return 0;
    }

    /**
     * whatsapp_promo_* kolonlari yoksa olusturur (idempotent, hatasiz gecer).
     */
    private function kolonlariGarantiEt()
    {
        try {
            if (Schema::hasColumn('salonlar', 'whatsapp_promo_baslangic')) {
                return;
            }
            Schema::table('salonlar', function (Blueprint $table) {
                if (!Schema::hasColumn('salonlar', 'whatsapp_promo_baslangic')) {
                    $table->date('whatsapp_promo_baslangic')->nullable();
                }
                if (!Schema::hasColumn('salonlar', 'whatsapp_promo_bitis')) {
                    $table->date('whatsapp_promo_bitis')->nullable();
                }
                if (!Schema::hasColumn('salonlar', 'whatsapp_promo_aktif')) {
                    $table->boolean('whatsapp_promo_aktif')->default(1);
                }
                if (!Schema::hasColumn('salonlar', 'whatsapp_promo_kapatildi')) {
                    $table->boolean('whatsapp_promo_kapatildi')->default(0);
                }
            });
            Log::info('[WA-PROMO] kolonlar olusturuldu');
        } catch (\Exception $e) {
            Log::warning('[WA-PROMO] kolon olusturma hatasi: ' . $e->getMessage());
        }
    }
}
