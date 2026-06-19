<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Salonlar;
use App\Services\WhatsAppService;

/**
 * Bir salonun WhatsApp gonderim profilini tek komutta cikartir.
 * "Bu salonun mesajlari gitmiyor" sikayetlerinde teshis hizini artirmak icin.
 *
 * Kullanim:
 *   php artisan whatsapp:salon-teshis 278
 *   php artisan whatsapp:salon-teshis 278 --gun=14
 */
class WhatsappSalonTeshis extends Command
{
    protected $signature = 'whatsapp:salon-teshis {salonId : Teshis edilecek salon ID} {--gun=7 : Son kac gunluk log incelenecek}';
    protected $description = 'Bir salonun WhatsApp gonderim durumunu, son hatalarini ve bridge oturumunu raporlar';

    public function handle()
    {
        $salonId = (int) $this->argument('salonId');
        $gun = max(1, (int) $this->option('gun'));

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            $this->error("Salon bulunamadi: id={$salonId}");
            return 1;
        }

        $this->info("=== Salon Teshis: #{$salon->id} {$salon->salon_adi} ===");
        $this->line('');

        // 1) DB'deki salon durumu
        $this->info('DB DURUMU:');
        $this->line('  whatsapp_aktif        : ' . (int) ($salon->whatsapp_aktif ?? 0));
        $this->line('  whatsapp_durum        : ' . ($salon->whatsapp_durum ?? '-'));
        $this->line('  whatsapp_numara       : ' . ($salon->whatsapp_numara ?? '-'));
        $this->line('  whatsapp_saglayici    : ' . ($salon->whatsapp_saglayici ?? 'baileys'));
        $this->line('  baglanti_tarihi       : ' . ($salon->whatsapp_baglanti_tarihi ?? '-'));
        $this->line('  son_hata              : ' . ($salon->whatsapp_son_hata ?? '-'));
        $this->line('  gunluk_limit          : ' . (int) ($salon->whatsapp_gunluk_limit ?? 0));
        $this->line('');

        // 2) Son N gunun gonderim istatistigi
        if (!Schema::hasTable('whatsapp_gonderim_loglari')) {
            $this->warn('whatsapp_gonderim_loglari tablosu yok — log analizi atlandi.');
        } else {
            $this->info("SON {$gun} GUN GONDERIM PROFILI:");
            $simdi = Carbon::now();
            $esik = $simdi->copy()->subDays($gun);

            $stats = DB::table('whatsapp_gonderim_loglari')
                ->where('salon_id', $salonId)
                ->where('created_at', '>=', $esik)
                ->selectRaw('durum, COUNT(*) as adet, MIN(created_at) as ilk, MAX(created_at) as son')
                ->groupBy('durum')
                ->orderBy('durum')
                ->get();

            if ($stats->isEmpty()) {
                $this->line("  Son {$gun} gunde hic gonderim kaydi yok.");
            } else {
                $etiket = [
                    0 => 'KUYRUKTA (durum=0)',
                    1 => 'GONDERILDI (durum=1)',
                    2 => 'BASARISIZ (durum=2)',
                    3 => 'SMS FALLBACK (durum=3)',
                ];
                foreach ($stats as $s) {
                    $ad = $etiket[(int) $s->durum] ?? ('durum=' . $s->durum);
                    $this->line(sprintf('  %-25s %5d adet   %s -> %s', $ad, $s->adet, $s->ilk, $s->son));
                }
            }
            $this->line('');

            // 2b) Teslim oranı (yeni kolon varsa)
            if (Schema::hasColumn('whatsapp_gonderim_loglari', 'teslim_tarihi')) {
                $gonderildi = DB::table('whatsapp_gonderim_loglari')
                    ->where('salon_id', $salonId)
                    ->where('created_at', '>=', $esik)
                    ->where('durum', 1)
                    ->count();
                $teslim = DB::table('whatsapp_gonderim_loglari')
                    ->where('salon_id', $salonId)
                    ->where('created_at', '>=', $esik)
                    ->where('durum', 1)
                    ->whereNotNull('teslim_tarihi')
                    ->count();
                $oran = $gonderildi > 0 ? round(($teslim / $gonderildi) * 100, 1) : null;
                $this->info('TESLIMAT (bridge message.delivered eventi gerekir):');
                $this->line("  Gonderildi (durum=1) : {$gonderildi}");
                $this->line("  Teslim oldu          : {$teslim}");
                $this->line('  Teslim orani         : ' . ($oran === null ? '-' : ($oran . '%')));
                $this->line('');
            }

            // 3) Son hatalar (basarisizlarin sebepleri)
            $sonHatalar = DB::table('whatsapp_gonderim_loglari')
                ->where('salon_id', $salonId)
                ->where('created_at', '>=', $esik)
                ->where('durum', 2)
                ->selectRaw('hata, COUNT(*) as adet, MAX(created_at) as son')
                ->groupBy('hata')
                ->orderByDesc('adet')
                ->limit(10)
                ->get();
            if ($sonHatalar->isNotEmpty()) {
                $this->info('EN COK GORULEN HATALAR:');
                foreach ($sonHatalar as $h) {
                    $hataStr = $h->hata ?: '(bos)';
                    $this->line(sprintf('  %5d x %s   (son: %s)', $h->adet, $hataStr, $h->son));
                }
                $this->line('');
            }

            // 4) Son 5 basarili + 5 basarisiz ornek satir
            $this->info('SON 5 KAYIT:');
            $sonKayitlar = DB::table('whatsapp_gonderim_loglari')
                ->where('salon_id', $salonId)
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'telefon', 'durum', 'hata', 'gonderim_tarihi', 'created_at']);
            foreach ($sonKayitlar as $k) {
                $durEtiket = ['0' => 'KUY', '1' => 'OK ', '2' => 'FAIL', '3' => 'SMS'][(string) $k->durum] ?? '?  ';
                $this->line(sprintf('  #%d %s tel=%s %s', $k->id, $durEtiket, $k->telefon, $k->hata ? ('hata: ' . $k->hata) : ''));
                $this->line('       created=' . $k->created_at . '  gonderim=' . ($k->gonderim_tarihi ?? '-'));
            }
            $this->line('');
        }

        // 5) Bridge oturum durumunu sor (Baileys icin)
        $saglayici = $salon->whatsapp_saglayici ?? 'baileys';
        if ($saglayici === 'baileys') {
            $this->info('BRIDGE OTURUM DURUMU:');
            try {
                $wa = app(WhatsAppService::class);
                $resp = $wa->status($salonId);
                $this->line('  HTTP status: ' . ($resp['status'] ?? '?'));
                $this->line('  Body       : ' . json_encode($resp['body'] ?? $resp['error'] ?? null, JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                $this->warn('  Bridge sorgulanamadi: ' . $e->getMessage());
            }
            $this->line('');
        }

        // 6) Onerilen aksiyon
        $this->info('YORUM:');
        $sonHataMetni = strtolower((string) ($sonHatalar[0]->hata ?? ''));
        if (strpos($sonHataMetni, '463') !== false || strpos($sonHataMetni, 'error in ack') !== false) {
            $this->warn('  Meta anti-abuse (463) tespit edildi — numara SOFT-BAN.');
            $this->line('  - Bu numarayla otomatik gonderimi en az 7-14 gun durdur.');
            $this->line('  - Salona SMS fallback aktif/yeterli mi diye bak.');
            $this->line('  - Kalici cozum: numara degisikligi veya Cloud API gecisi.');
        } elseif (strpos($sonHataMetni, 'unauthorized') !== false || strpos($sonHataMetni, 'loggedout') !== false) {
            $this->warn('  Oturum yetkisiz — salon WA panelinden QR tekrar taratilmali.');
        } elseif (($salon->whatsapp_durum ?? '') === 'connected' && isset($gonderildi) && $gonderildi > 0 && isset($oran) && $oran !== null && $oran < 50) {
            $this->warn('  Connected gorunuyor ama teslim orani dusuk — shadow-throttle olabilir.');
            $this->line('  - 24-48 saat hacmi yari dusur, sonra tekrar olcum al.');
        } else {
            $this->line('  Acik bir patolojik patern saptanmadi.');
        }

        return 0;
    }
}
