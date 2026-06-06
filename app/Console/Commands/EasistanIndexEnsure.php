<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

/**
 * E-Asistan ve admin sayfalarinin temel sorgu performansi icin gereken
 * index'leri GARANTILER. Migrations tablosundan BAGIMSIZ calisir; cunku
 * 2026_06_03 perf-index migration'i prod'da:
 *   1) hic calismamis olabilir (deploy sadece `git pull` yapiyor), VEYA
 *   2) "calisti" diye isaretlenmis ama icindeki try/catch yuzunden index'ler
 *      sessizce olusmamis olabilir (bu durumda `migrate` bir daha denemez).
 *
 * Bu komut idempotent'tir: var olan index'e dokunmaz, eksik olani olusturur,
 * ne yaptigini raporlar. Scheduler ile periyodik calisinca git-pull-only
 * deploy'da bile index'ler kendiliginden uygulanir; tum index'ler mevcutken
 * sadece ucuz birkac SHOW INDEX sorgusu atar (no-op).
 *
 * Tek seferlik manuel calistirma:
 *   /opt/php74/bin/php artisan easistan:index-ensure
 */
class EasistanIndexEnsure extends Command
{
    protected $signature = 'easistan:index-ensure {--quiet-noop : Tum index mevcutsa log yazma}';
    protected $description = 'E-Asistan sorgu index\'lerini garanti eder (idempotent).';

    /** @var array<array{table:string, cols:string[], name:string}> */
    private $indexes = [
        ['table' => 'randevular',        'cols' => ['salon_id', 'tarih'],                         'name' => 'rand_salon_tarih_idx'],
        ['table' => 'randevular',        'cols' => ['salon_id', 'onceki_tarih'],                  'name' => 'rand_salon_onceki_idx'],
        ['table' => 'randevular',        'cols' => ['salon_id', 'durum'],                         'name' => 'rand_salon_durum_idx'],
        ['table' => 'alacaklar',         'cols' => ['salon_id', 'planlanan_odeme_tarihi'],        'name' => 'alacak_salon_tarih_idx'],
        ['table' => 'alacaklar',         'cols' => ['salon_id', 'onceki_planlanan_odeme_tarihi'], 'name' => 'alacak_salon_onceki_idx'],
        ['table' => 'randevu_hizmetler', 'cols' => ['randevu_id'],                                'name' => 'rh_randevu_idx'],
        ['table' => 'bildirimler',       'cols' => ['salon_id', 'personel_id', 'id'],             'name' => 'bild_salon_personel_idx'],
        // bildirimkontrolet polling COUNT'lari (30sn): okunmamis + toplam sayim.
        // okundu kolonunu da kapsar -> index-only count, full scan biter (CPU spike fix).
        ['table' => 'bildirimler',       'cols' => ['salon_id', 'personel_id', 'okundu'],         'name' => 'bild_salon_personel_okundu_idx'],
        // MOBIL MUSTERI uygulamasi (musteriozet/bildirimgetirmusteri): user_id uzerinden
        // okunmamis sayim + liste. user_id'de HIC index yoktu -> her cagri full table scan,
        // CPU spike'in asil kaynagi buydu. (user_id, salon_id, okundu) -> index-only count.
        ['table' => 'bildirimler',       'cols' => ['user_id', 'salon_id', 'okundu'],             'name' => 'bild_user_salon_okundu_idx'],
        ['table' => 'bildirimler',       'cols' => ['user_id', 'salon_id', 'tarih_saat'],         'name' => 'bild_user_salon_tarih_idx'],
        ['table' => 'kampanya_yonetimi', 'cols' => ['salon_id', 'asistan_tarih_saat'],            'name' => 'kampanya_salon_tarih_idx'],
    ];

    public function handle()
    {
        $created = [];
        $skipped = [];
        $missingCols = [];

        foreach ($this->indexes as $ix) {
            $table = $ix['table'];
            $cols  = $ix['cols'];
            $name  = $ix['name'];

            if (!Schema::hasTable($table)) {
                $missingCols[] = "{$name} (tablo yok: {$table})";
                continue;
            }
            foreach ($cols as $c) {
                if (!Schema::hasColumn($table, $c)) {
                    $missingCols[] = "{$name} (kolon yok: {$table}.{$c})";
                    continue 2;
                }
            }

            $exists = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]))->isNotEmpty();
            if ($exists) {
                $skipped[] = $name;
                continue;
            }

            try {
                $colList = implode('`,`', $cols);
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` (`{$colList}`)");
                $created[] = $name;
            } catch (\Exception $e) {
                Log::warning('easistan:index-ensure — index olusturulamadi: ' . $name . ' on ' . $table . ' — ' . $e->getMessage());
                $missingCols[] = "{$name} (HATA: " . $e->getMessage() . ")";
            }
        }

        if (count($created) === 0 && count($missingCols) === 0) {
            if (!$this->option('quiet-noop')) {
                $this->info('E-Asistan index\'leri zaten mevcut (' . count($skipped) . ' index). Yapilacak is yok.');
            }
            return 0;
        }

        if (count($created) > 0) {
            $msg = 'easistan:index-ensure — OLUSTURULAN index\'ler: ' . implode(', ', $created);
            $this->info($msg);
            Log::info($msg);
        }
        if (count($skipped) > 0) {
            $this->line('Zaten var: ' . implode(', ', $skipped));
        }
        if (count($missingCols) > 0) {
            $this->warn('Atlanan/Hatali: ' . implode(' | ', $missingCols));
        }

        return 0;
    }
}
