<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * YETKİ TEŞHİS — bir yetkilinin (isletmeyetkilileri.id) hangi salonlarda kayitli
 * oldugunu, her satirin aktif durumunu ve mevcutsube()'nin secmis olacagi salonu
 * gosterir. "Giris yapiyor ama tum sayfalar yetkisiz" durumunu teshis icin.
 *
 * Kullanim:
 *   /opt/php74/bin/php artisan yetki:teshis 680
 */
class YetkiTeshis extends Command
{
    protected $signature = 'yetki:teshis {yetkiliId : isletmeyetkilileri.id}';
    protected $description = 'Bir yetkilinin salon baglantilarini, aktif durumlarini ve secilecek subeyi gosterir';

    public function handle()
    {
        $yid = (int) $this->argument('yetkiliId');
        $this->info("=== YETKİ TEŞHİS — yetkili_id={$yid} ===");

        // isletmeyetkilileri kaydi (statik salon_id kolonu dahil)
        $yetkili = DB::table('isletmeyetkilileri')->where('id', $yid)->first();
        if (!$yetkili) {
            $this->error('isletmeyetkilileri kaydi bulunamadi!');
            return 0;
        }
        $this->line('isletmeyetkilileri.salon_id (statik kolon) = ' . ($yetkili->salon_id ?? 'NULL'));
        $this->line('');

        // salon_personelleri: yetkili_olunan_isletmeler relation'in kaynagi
        $rows = DB::table('salon_personelleri')
            ->where('yetkili_id', $yid)
            ->orderBy('id')
            ->get(['id', 'salon_id', 'aktif']);

        $this->line('salon_personelleri satirlari (yetkili_olunan_isletmeler kaynagi):');
        $tumu = [];
        $aktifler = [];
        foreach ($rows as $r) {
            $salonAd = DB::table('Salonlar')->where('id', $r->salon_id)->value('salon_adi');
            $this->line(sprintf('  personel_id=%-6s salon_id=%-6s aktif=%-3s  (%s)',
                $r->id, $r->salon_id, $r->aktif, $salonAd ?: '?'));
            $tumu[] = $r->salon_id;
            if ((int) $r->aktif === 1) $aktifler[] = $r->salon_id;
        }
        $this->line('');
        $this->line('TUM salonlar (filtresiz)  = [' . implode(', ', $tumu) . ']');
        $this->line('AKTIF salonlar (aktif=1)  = [' . implode(', ', $aktifler) . ']');
        $this->line('');

        // ESKI mevcutsube() (filtresiz) api-host dalinda sececegi: tumu[0]
        $eski = $tumu[0] ?? '(bos)';
        // YENI mevcutsube() (aktif=1) secilecek: aktifler[0]
        $yeni = $aktifler[0] ?? '(bos)';
        $this->warn("ESKI mevcutsube secimi (filtresiz, tumu[0]) = {$eski}");
        $this->info("YENI mevcutsube secimi (aktif=1, aktifler[0]) = {$yeni}");
        $this->line('');

        if ($eski !== $yeni) {
            $this->warn('>> Uyusmazlik var: eski secim aktif olmayan/yanlis salona dusuyordu — 403 nedeni budur.');
        } else {
            $this->line('>> Eski ve yeni secim ayni. 403 nedeni mevcutsube DEGIL; baska yerde ara.');
        }

        return 0;
    }
}
