<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\SeslendirmeServisi;

/**
 * Asistan bilgilendirme cevaplarini (asistan_kalip tablosu) ONCEDEN sese cevirip
 * sunucuda onbellege alir. Boylece ilk musteride Google beklemeden HAZIR MP3 calar.
 *
 * Cevaplar sabit oldugu icin tek seferlik calisir; ayni cumle tekrar uretilmez.
 * Sunucuda:  /opt/php74/bin/php artisan asistan:ses-onuret
 */
class SeslendirmeOnUret extends Command
{
    protected $signature = 'asistan:ses-onuret {--limit=0 : En fazla kac cevap uret (0=tumu)} {--ses= : Ses adi (bos=config varsayilani)}';
    protected $description = 'Bilgilendirme cevaplarini Google TTS ile onceden uretip onbellege alir.';

    public function handle(SeslendirmeServisi $servis)
    {
        if ((string) config('services.google_tts.key', '') === '') {
            $this->error('GOOGLE_TTS_API_KEY tanimli degil (.env). Once anahtari ekleyin.');
            return 1;
        }
        if (!Schema::hasTable('asistan_kalip')) {
            $this->error('asistan_kalip tablosu yok.');
            return 1;
        }

        $ses = $this->option('ses') ?: null;
        $limit = (int) $this->option('limit');

        $satirlar = DB::table('asistan_kalip')->where('aktif', 1)->pluck('cevap');
        $parcalar = [];
        foreach ($satirlar as $c) {
            foreach (preg_split('/^\s*-{3,}\s*$/m', (string) $c) as $p) {
                $p = trim($p);
                if ($p !== '') $parcalar[] = $p;
            }
        }
        $parcalar = array_values(array_unique($parcalar));
        $toplam = count($parcalar);
        if ($limit > 0) $parcalar = array_slice($parcalar, 0, $limit);

        $this->info('Toplam ' . $toplam . ' benzersiz cevap. Uretiliyor: ' . count($parcalar));
        $uretilen = 0; $hata = 0;
        foreach ($parcalar as $i => $metin) {
            $ad = $servis->uret($metin, $ses);
            if ($ad) { $uretilen++; } else { $hata++; }
            if (($i + 1) % 25 === 0) {
                $this->line(($i + 1) . '/' . count($parcalar) . ' ... ok=' . $uretilen . ' hata=' . $hata);
            }
        }

        $this->info("Bitti. Uretilen/onbellek: {$uretilen}, hata: {$hata}.");
        return 0;
    }
}
