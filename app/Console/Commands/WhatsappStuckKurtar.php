<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Salonlar;

/**
 * Kuyrukta takılı kalan WhatsApp mesajlarını kurtarır:
 *   - durum=0 (kuyrukta) AND 10 dakikadır gönderilmediyse
 *   - Salonun WhatsApp bağlantısı kopmuş VEYA Node service mesajı unutmuşsa
 *   - Mesajı durum=3 (SMS'e düşürüldü) yapıp SMS olarak gönderir
 *
 * Sebep: Node service RAM-only queue tutuyor; service restart/crash olunca
 * queue siliniyor, DB'de durum=0 olan mesajlar sonsuza kadar takılı kalıyordu.
 */
class WhatsappStuckKurtar extends Command
{
    protected $signature = 'whatsapp:stuck-kurtar {--dakika=10 : Bu kadar dakikadır kuyrukta olanlar kurtarılır}';
    protected $description = 'WhatsApp gönderim kuyruğunda takılı kalan mesajları SMS\'e düşürür';

    public function handle()
    {
        if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
            $this->warn('Tablo yok, atlanıyor.');
            return 0;
        }

        $dakika = (int) $this->option('dakika');
        if ($dakika < 2) $dakika = 2;
        $esik = Carbon::now()->subMinutes($dakika);

        $stuck = DB::table('whatsapp_gonderim_loglari')
            ->where('durum', 0)
            ->where('created_at', '<', $esik)
            ->orderBy('id')
            ->limit(500)
            ->get();

        if ($stuck->isEmpty()) {
            return 0;
        }

        $kurtarildi = 0;
        $atlandi = 0;
        $controller = app()->make(Controller::class);

        foreach ($stuck as $log) {
            try {
                // Telefon ve mesaj yoksa anlamsız — failed olarak işaretle
                if (empty($log->telefon) || empty($log->mesaj)) {
                    DB::table('whatsapp_gonderim_loglari')
                        ->where('id', $log->id)
                        ->update([
                            'durum' => 2,
                            'hata' => 'stuck-kurtar: telefon/mesaj eksik',
                            'updated_at' => now(),
                        ]);
                    $atlandi++;
                    continue;
                }

                // SMS fallback config kapalıysa, sadece failed olarak işaretle
                if (!config('whatsapp.fallback_to_sms', true)) {
                    DB::table('whatsapp_gonderim_loglari')
                        ->where('id', $log->id)
                        ->update([
                            'durum' => 2,
                            'hata' => 'stuck-kurtar: ' . $dakika . 'dk kuyrukta kaldı, fallback kapalı',
                            'updated_at' => now(),
                        ]);
                    $atlandi++;
                    continue;
                }

                // SMS olarak gönder
                $controller->sms_gonder($log->salon_id, [[
                    'to' => $log->telefon,
                    'message' => $log->mesaj,
                ]]);

                DB::table('whatsapp_gonderim_loglari')
                    ->where('id', $log->id)
                    ->update([
                        'durum' => 3,
                        'hata' => 'stuck-kurtar: ' . $dakika . 'dk kuyrukta kaldı, SMS\'e düşürüldü',
                        'gonderim_tarihi' => now(),
                        'updated_at' => now(),
                    ]);

                $kurtarildi++;
            } catch (\Throwable $e) {
                Log::warning('[WA-STUCK] kurtarma hatası', [
                    'log_id' => $log->id,
                    'err' => $e->getMessage(),
                ]);
                // Bu log için bir sonraki tick'te tekrar denenecek (durum=0 hala)
                $atlandi++;
            }
        }

        if ($kurtarildi > 0 || $atlandi > 0) {
            Log::info('[WA-STUCK] tarama tamamlandi', [
                'esik_dk' => $dakika,
                'kurtarildi' => $kurtarildi,
                'atlandi' => $atlandi,
                'toplam' => $stuck->count(),
            ]);
            $this->info("Kurtarıldı: {$kurtarildi}, atlandı: {$atlandi}, toplam: " . $stuck->count());
        }

        return 0;
    }
}
