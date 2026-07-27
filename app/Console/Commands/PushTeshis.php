<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Salonlar;

/**
 * Push (FCM) teşhis komutu — neden gitmediğini ekrana yazar.
 * mysql creds gerektirmez; Laravel'in kendi bağlantısını kullanır.
 *
 * Kullanım:
 *   php artisan push:teshis                 -> genel token/app_bundle sağlık raporu
 *   php artisan push:teshis {salonId}       -> o salon için token bul + GERÇEK test push at, FCM hatasını göster
 *
 * Örnek:
 *   /opt/php74/bin/php artisan push:teshis
 *   /opt/php74/bin/php artisan push:teshis 331
 */
class PushTeshis extends Command
{
    protected $signature = 'push:teshis {salonId?}';
    protected $description = 'FCM push teşhisi: token sayıları, app_bundle eşleşmesi ve gerçek gönderim testi';

    public function handle()
    {
        $this->info('=== PUSH (FCM) TEŞHİS ===');
        $this->line('');

        // 1) Global token sağlığı
        $toplam = DB::table('bildirim_kimlikleri')->count();
        $aktif  = DB::table('bildirim_kimlikleri')->where('aktif', 1)->count();
        $tokenli = DB::table('bildirim_kimlikleri')
            ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')->count();
        $kullanilabilir = DB::table('bildirim_kimlikleri')
            ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')->count();

        $this->info('--- bildirim_kimlikleri ---');
        $this->line("toplam kayit        : {$toplam}");
        $this->line("aktif=1             : {$aktif}");
        $this->line("bildirim_id dolu    : {$tokenli}");
        $this->line("aktif + tokenli     : {$kullanilabilir}   <- gerçekte gönderilebilir olanlar");
        $this->line('');

        // 2) app_bundle kırılımı (token tarafı)
        $this->info('--- Token app_bundle dağılımı (aktif+tokenli) ---');
        $tokBundles = DB::table('bildirim_kimlikleri')
            ->select('app_bundle', DB::raw('COUNT(*) as adet'))
            ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')
            ->groupBy('app_bundle')->orderByDesc('adet')->get();
        foreach ($tokBundles as $b) {
            $ab = $b->app_bundle === null ? 'NULL' : ($b->app_bundle === '' ? "'' (bos)" : $b->app_bundle);
            $this->line(sprintf('  %-45s %d', $ab, $b->adet));
        }
        $this->line('');

        // 3) Salonlar tarafındaki app_bundle değerleri
        $this->info('--- Salonlar.app_bundle dağılımı ---');
        $salBundles = DB::table('salonlar')
            ->select('app_bundle', DB::raw('COUNT(*) as adet'))
            ->groupBy('app_bundle')->orderByDesc('adet')->get();
        $salSet = [];
        foreach ($salBundles as $b) {
            $ab = $b->app_bundle === null ? 'NULL' : ($b->app_bundle === '' ? "'' (bos)" : $b->app_bundle);
            if (!empty($b->app_bundle)) $salSet[$b->app_bundle] = true;
            $this->line(sprintf('  %-45s %d salon', $ab, $b->adet));
        }
        $this->line('');

        // 4) EŞLEŞME KONTROLÜ: token app_bundle'ı hiçbir salonun app_bundle'ına uymuyorsa
        //    o cihazlara ASLA push gitmez (brand izolasyon filtresi eler).
        $this->info('--- app_bundle EŞLEŞME KONTROLÜ ---');
        $uyumsuz = 0;
        foreach ($tokBundles as $b) {
            if (empty($b->app_bundle)) continue;
            if (!isset($salSet[$b->app_bundle])) {
                $this->error("  [UYUMSUZ] token app_bundle='{$b->app_bundle}' ({$b->adet} cihaz) hiçbir salonda yok -> bu cihazlara push GİTMEZ");
                $uyumsuz += $b->adet;
            }
        }
        if ($uyumsuz === 0) {
            $this->info('  [OK] Tüm token app_bundle değerleri en az bir salonla eşleşiyor.');
        } else {
            $this->warn("  Toplam {$uyumsuz} cihaz brand izolasyonu yüzünden eleniyor olabilir.");
        }
        $this->line('');

        // 5) Firebase JSON dosyaları mevcut mu?
        $this->info('--- Firebase service account JSON kontrolü ---');
        foreach ((array) config('firebase_projects') as $profile => $rel) {
            $abs = storage_path($rel);
            $ok = is_file($abs);
            $this->line(sprintf('  %-16s %s %s', $profile, $ok ? '[OK]' : '[YOK]', $rel));
        }
        $this->line('');

        $salonId = $this->argument('salonId');
        if (!$salonId) {
            $this->comment('Bir salona gerçek test push atmak için: php artisan push:teshis {salonId}');
            return 0;
        }

        // 6) Belirli salon için gerçek gönderim testi
        $salonId = (int) $salonId;
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("Salon bulunamadı: {$salonId}");
            return 1;
        }

        $this->info("=== SALON {$salonId} GERÇEK TEST PUSH ===");
        $this->line("salon_adi        : " . ($salon->salon_adi ?? '-'));
        $this->line("firebase_profile : " . ($salon->firebase_profile ?? 'NULL'));
        $this->line("app_bundle       : " . ($salon->app_bundle ?? 'NULL'));

        // Profil config'te var mı?
        $prof = $salon->firebase_profile;
        if (!empty($prof)) {
            $path = config("firebase_projects.{$prof}");
            if (empty($path)) {
                $this->error("  [X] firebase_profile='{$prof}' config/firebase_projects.php'de YOK -> default'a düşer, yanlış proje!");
            } else {
                $this->info("  [OK] firebase_profile -> {$path}");
            }
        }
        $this->line('');

        // Bu salonun brand'ine ait, aktif+tokenli cihaz sayısı
        $q = DB::table('bildirim_kimlikleri')
            ->where('aktif', 1)->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '');
        if (!empty($salon->app_bundle)) {
            $q->where('app_bundle', $salon->app_bundle);
        }
        $hedefAdet = $q->count();
        $this->line("Bu salonun app_bundle'ına ait gönderilebilir cihaz: {$hedefAdet}");
        if ($hedefAdet === 0) {
            $this->error('  [X] Hedef cihaz YOK. Push gitmez. (Uygulama token kaydetmiyor ya da app_bundle uyuşmuyor.)');
            return 0;
        }
        $ornek = $q->select('bildirim_id', 'platform', 'app_bundle')->first();
        $this->line("  Örnek token app_bundle: " . ($ornek->app_bundle ?? 'NULL') . " / platform: " . ($ornek->platform ?? 'NULL'));
        $this->line('');

        // Gerçek gönderim — yetkili (salon sahibi) hedefli değil, raw token ile tek cihaza test
        $this->info("--- Tek cihaza gerçek FCM gönderimi deneniyor ---");
        try {
            $svc = NotificationService::forTokens([$ornek->bildirim_id], $salonId)
                ->type('test')
                ->title('Push Test')
                ->body('Bu bir teshis test bildirimidir.');
            $sonuc = $svc->send();
            $this->info("  SONUÇ: sent={$sonuc['sent']} failed={$sonuc['failed']} total={$sonuc['total']}");
            if ($sonuc['sent'] > 0) {
                $this->info('  [OK] FCM kabul etti. Cihaz bildirimi almalı.');
            } else {
                $this->error('  [X] Gönderilemedi. Üstteki "send fail" loguna / laravel.log\'a bak.');
            }
        } catch (\Throwable $e) {
            $this->error('  [X] İSTİSNA: ' . $e->getMessage());
        }

        return 0;
    }
}
