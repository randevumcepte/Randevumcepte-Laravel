<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use App\Salonlar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp test gönderim komutu — gerçek akıştaki tüm kontrolleri çalıştırır
 * ve hangi adımda engellendiğini ekrana yazar.
 *
 * Kullanım:
 *   php artisan whatsapp:test {salonId} {telefon} {--mesaj=Test mesaji}
 *
 * Örnek:
 *   php artisan whatsapp:test 1 05551234567
 *   php artisan whatsapp:test 1 905551234567 --mesaj="Selam bu bir test"
 */
class WhatsappTestGonder extends Command
{
    protected $signature = 'whatsapp:test {salonId} {telefon} {--mesaj=Bu bir WhatsApp test mesajidir.}';
    protected $description = 'WhatsApp gönderim akışını teşhis amaçlı tetikler — neden gitmediğini ekrana yazar';

    public function handle()
    {
        $salonId = (int) $this->argument('salonId');
        $telefon = $this->argument('telefon');
        $mesaj = (string) $this->option('mesaj');

        $this->info("=== WhatsApp Test Gönderim ===");
        $this->line("Salon ID: {$salonId}");
        $this->line("Telefon: {$telefon}");
        $this->line("Mesaj: {$mesaj}");
        $this->line('');

        // 1. Salon var mı?
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("[X] Salon bulunamadı (id={$salonId})");
            return 1;
        }
        $this->info("[OK] Salon: {$salon->salon_adi}");

        // 2. Salon ayarlarını göster
        $this->line('');
        $this->info("--- Salon WhatsApp Durumu ---");
        $this->line("whatsapp_aktif       : " . ($salon->whatsapp_aktif ?? 'NULL'));
        $this->line("whatsapp_durum       : " . ($salon->whatsapp_durum ?? 'NULL'));
        $this->line("whatsapp_numara      : " . ($salon->whatsapp_numara ?? 'NULL'));
        $this->line("whatsapp_saglayici   : " . ($salon->whatsapp_saglayici ?? 'baileys (default)'));
        $this->line("whatsapp_gunluk_limit: " . ($salon->whatsapp_gunluk_limit ?? 'NULL'));
        $this->line("whatsapp_warmup_bsl  : " . ($salon->whatsapp_warmup_baslangic ?? 'NULL'));
        $this->line("whatsapp_baglanti    : " . ($salon->whatsapp_baglanti_tarihi ?? 'NULL'));
        $this->line("whatsapp_son_hata    : " . ($salon->whatsapp_son_hata ?? 'YOK'));

        // 3. Config bilgisi
        $this->line('');
        $this->info("--- Config ---");
        $this->line("service_url          : " . config('whatsapp.service_url'));
        $this->line("fallback_to_sms      : " . (config('whatsapp.fallback_to_sms') ? 'true' : 'false'));
        $this->line("business_hours.enforce: " . (config('whatsapp.business_hours.enforce') ? 'true (saat KISITI VAR)' : 'false (24/7)'));

        // 4. WhatsApp servisini çağır
        $this->line('');
        $this->info("--- sendReminder() Çağırılıyor ---");
        $wa = app(WhatsAppService::class);

        $normalized = $wa->normalizePhone($telefon);
        $this->line("Normalize telefon: " . ($normalized ?? 'GEÇERSİZ'));

        $this->line("canSendToday(): " . ($wa->canSendToday($salon) ? 'true' : 'false (kanal kapalı veya limit doldu)'));
        $this->line("withinBusinessHours(): " . ($wa->withinBusinessHours() ? 'true' : 'false (saat kısıtlaması engelliyor)'));
        $this->line("warmupCap(): " . $wa->warmupCap($salon));

        $this->line('');
        $sonuc = $wa->sendReminder($salon, $telefon, $mesaj, null, null);

        $this->line('');
        $this->info("--- SONUÇ ---");
        if (isset($sonuc['ok']) && $sonuc['ok']) {
            $this->info("[OK] Gönderildi/kuyruğa alındı");
            $this->line("Provider: " . ($sonuc['provider'] ?? '-'));
            $this->line("Queued: " . (($sonuc['queued'] ?? false) ? 'true (Node service kuyruğunda)' : 'false (anında gitti)'));
            $this->line("Log ID: " . ($sonuc['logId'] ?? '-'));
            if (isset($sonuc['logId'])) {
                $this->line('');
                $this->info("DB Log Kontrolü:");
                if (Schema::hasTable('whatsapp_gonderim_loglari')) {
                    $log = DB::table('whatsapp_gonderim_loglari')->where('id', $sonuc['logId'])->first();
                    if ($log) {
                        $durumLabels = [0 => 'Kuyrukta', 1 => 'Gönderildi', 2 => 'Başarısız', 3 => "SMS'e düşürüldü"];
                        $this->line("  durum: {$log->durum} ({$durumLabels[$log->durum]} )");
                        $this->line("  telefon: {$log->telefon}");
                        $this->line("  hata: " . ($log->hata ?? 'YOK'));
                        $this->line("  created_at: {$log->created_at}");
                    }
                }
            }
        } else {
            $this->error("[X] Gönderim BAŞARISIZ");
            $this->line("Hata: " . ($sonuc['error'] ?? 'unknown'));
            $this->line("Status: " . ($sonuc['status'] ?? '-'));
            $this->line("Provider: " . ($sonuc['provider'] ?? '-'));
            $this->line('');
            $this->warn("Hata sebebine göre ne yapılmalı:");
            $hata = $sonuc['error'] ?? '';
            switch ($hata) {
                case 'invalid-phone':
                    $this->line("→ Telefon formatı geçersiz. Ülke kodu (90) ile birlikte 11+ hane gerekli.");
                    break;
                case 'daily-cap-reached':
                    $this->line("→ Günlük limit doldu. Yarın yeniden deneyin veya whatsapp_gunluk_limit'i artırın.");
                    break;
                case 'outside-business-hours':
                    $this->line("→ Saat kısıtlaması engellemiş. WHATSAPP_BUSINESS_HOURS_ENFORCE=false yapın.");
                    break;
                case 'service-unreachable':
                    $this->line("→ Node service'e bağlanılamadı. pm2 status randevumcepte-whatsapp ile kontrol edin.");
                    break;
                case 'session-not-connected':
                case 'session-disconnected':
                case 'session-reconnecting':
                case 'session-not-found':
                    $this->line("→ WhatsApp bağlantısı yok. Panelden QR taratın.");
                    break;
                case 'queue-paused-health':
                    $this->line("→ Çok fail oldu, queue duraklatıldı. WhatsApp panelinden yeniden bağlayın.");
                    break;
                case 'cloud-template-not-configured':
                    $this->line("→ Cloud API kullanıyorsunuz ama template adı boş. Salon ayarlarına bakın.");
                    break;
                default:
                    $this->line("→ Detay log'da: storage/logs/laravel.log'a bakın.");
            }
        }

        return 0;
    }
}
