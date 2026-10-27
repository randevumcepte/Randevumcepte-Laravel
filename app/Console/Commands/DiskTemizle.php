<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Disk sismesini onleyen periyodik temizlik komutu.
 *
 * Bilinen kok nedenler (bkz. hafiza: "Disk sisme kaynaklari"):
 *   - DB yedekleri (storage/dbyedekler) rotasyonsuz birikiyordu
 *   - Oturum dosyalari SESSION_LIFETIME=525600 (1 yil) yuzunden hic silinmiyordu
 *   - Loglar (laravel.log/performance.log/deploy.log) 'single' channel, sinirsiz buyur
 *   - pm2.log + olu whatsapp-service err.log GB'larca sisiyordu
 *   - systemd journal (root yetkisi gerek; PHP erisemezse rapor edilir)
 *
 * Yazabildigi her seyi kirpar/siler; yetkisi olmayan dev dosyalar icin
 * tam root komutunu ekrana basar. withoutOverlapping ile saat basi cron'da.
 */
class DiskTemizle extends Command
{
    protected $signature = 'disk:temizle
        {--yedek-gun=7 : Kac gunluk DB yedegi tutulsun}
        {--oturum-gun=7 : Bu gunden eski oturum dosyalari silinir}
        {--log-mb=200 : Bu boyutu (MB) asan loglar son kuyrugu korunarak kirpilir}
        {--kuyruk-mb=2 : Kirpilan logda korunacak son kisim (MB)}
        {--kuru : Sadece raporla, hicbir sey silme/kirpma}';

    protected $description = 'Disk sismesini onler: DB yedek rotasyonu, eski oturumlar, sisen loglar (pm2/whatsapp dahil)';

    /** Root gerektirip dokunulamayan dev dosyalar */
    protected $yetkiyok = [];

    public function handle()
    {
        $kuru = (bool) $this->option('kuru');
        if ($kuru) {
            $this->warn('[KURU CALISMA] Hicbir dosyaya dokunulmayacak, sadece raporlanacak.');
        }

        $kazanim = 0;
        $kazanim += $this->dbYedekRotasyon((int) $this->option('yedek-gun'), $kuru);
        $kazanim += $this->oturumTemizle((int) $this->option('oturum-gun'), $kuru);
        $kazanim += $this->logKirp((int) $this->option('log-mb'), (int) $this->option('kuyruk-mb'), $kuru);

        $this->line('');
        $this->info(($kuru ? '[KURU] Kazanilabilecek' : 'Kazanilan') . ' toplam: ' . $this->boyut($kazanim));

        if (!empty($this->yetkiyok)) {
            $this->line('');
            $this->warn('Asagidaki dev dosyalar PHP kullanicisinin yetkisinde DEGIL (muhtemelen root/pm2/systemd).');
            $this->warn('Root ile tek seferlik su komutu calistirin:');
            foreach ($this->yetkiyok as $f) {
                $this->line('   truncate -s 0 ' . $f . '   # ' . $this->boyut($this->guvenliBoyut($f)));
            }
            $this->line('   journalctl --vacuum-size=200M   # systemd journal icin');
        }

        \Log::info('[disk:temizle] ' . ($kuru ? 'kuru ' : '') . 'kazanim=' . $this->boyut($kazanim)
            . ($this->yetkiyok ? ' | root-gerekli=' . count($this->yetkiyok) : ''));

        return 0;
    }

    /** 1) DB yedek rotasyonu: en yeni N gun disindaki *.sql dosyalarini sil */
    protected function dbYedekRotasyon($gun, $kuru)
    {
        $dir = storage_path('dbyedekler');
        if (!is_dir($dir)) {
            return 0;
        }
        $kazanim = 0;
        $esik = time() - ($gun * 86400);
        foreach (glob($dir . '/*.sql') ?: [] as $f) {
            $mt = @filemtime($f);
            if ($mt !== false && $mt < $esik) {
                $boyut = $this->guvenliBoyut($f);
                if (!$kuru && @unlink($f)) {
                    $kazanim += $boyut;
                } elseif ($kuru) {
                    $kazanim += $boyut;
                }
            }
        }
        $this->satir('DB yedek rotasyonu (>' . $gun . ' gun)', $kazanim);
        return $kazanim;
    }

    /** 2) Eski oturum dosyalari: mtime N gunden eski ise sil (aktif oturum mtime tazelenir) */
    protected function oturumTemizle($gun, $kuru)
    {
        $dir = storage_path('framework/sessions');
        if (!is_dir($dir)) {
            return 0;
        }
        $kazanim = 0;
        $sayac = 0;
        $esik = time() - ($gun * 86400);
        foreach (glob($dir . '/*') ?: [] as $f) {
            if (!is_file($f)) {
                continue;
            }
            $base = basename($f);
            if ($base === '.gitignore') {
                continue;
            }
            $mt = @filemtime($f);
            if ($mt !== false && $mt < $esik) {
                $boyut = $this->guvenliBoyut($f);
                if ($kuru) {
                    $kazanim += $boyut;
                    $sayac++;
                } elseif (@unlink($f)) {
                    $kazanim += $boyut;
                    $sayac++;
                }
            }
        }
        $this->satir('Eski oturum dosyalari (>' . $gun . ' gun, ' . $sayac . ' adet)', $kazanim);
        return $kazanim;
    }

    /** 3) Sisen loglar: esigi asanlari son kuyrugu koruyarak kirp (yetki yoksa listeye al) */
    protected function logKirp($logMb, $kuyrukMb, $kuru)
    {
        $esikByte = $logMb * 1024 * 1024;
        $kuyrukByte = $kuyrukMb * 1024 * 1024;
        $kazanim = 0;

        foreach ($this->logAdaylari() as $f) {
            if (!is_file($f)) {
                continue;
            }
            $boyut = $this->guvenliBoyut($f);
            if ($boyut <= $esikByte) {
                continue;
            }
            if (!is_writable($f)) {
                $this->yetkiyok[$f] = $f;
                continue;
            }
            $kazanilan = max(0, $boyut - $kuyrukByte);
            if ($kuru) {
                $kazanim += $kazanilan;
                $this->line('   kirpilacak: ' . $f . ' (' . $this->boyut($boyut) . ')');
                continue;
            }
            if ($this->kuyrukKoruyarakKirp($f, $kuyrukByte)) {
                $kazanim += $kazanilan;
                $this->line('   kirpildi:   ' . $f . ' -> son ' . $kuyrukMb . 'MB korundu');
            }
        }
        $this->satir('Sisen log kirpma (>' . $logMb . 'MB)', $kazanim);
        return $kazanim;
    }

    /** Log dosyasi adaylari: proje loglari + yaygin pm2/node/whatsapp konumlari */
    protected function logAdaylari()
    {
        $adaylar = [];

        // Laravel/proje loglari
        foreach (glob(storage_path('logs') . '/*.log') ?: [] as $f) {
            $adaylar[] = $f;
        }

        // pm2 + node servis loglari (whatsmeow bridge / olu whatsapp-service dahil)
        $kokler = array_filter([
            getenv('HOME') ?: null,
            '/var/www/www-root',
            '/var/www/www-root/data',
            '/root',
            '/home/www-root',
        ]);
        foreach ($kokler as $kok) {
            foreach (['/.pm2/logs', '/.pm2', '/logs'] as $alt) {
                $dizin = $kok . $alt;
                if (!is_dir($dizin)) {
                    continue;
                }
                foreach (glob($dizin . '/*.log') ?: [] as $f) {
                    $adaylar[$f] = $f; // dedup
                }
                foreach (glob($dizin . '/*-{error,out,err}*.log', GLOB_BRACE) ?: [] as $f) {
                    $adaylar[$f] = $f;
                }
            }
        }

        return array_values(array_unique($adaylar));
    }

    /** Dosyanin son $kuyrukByte kismini koruyup gerisini at (ftruncate ile yerinde) */
    protected function kuyrukKoruyarakKirp($f, $kuyrukByte)
    {
        $boyut = $this->guvenliBoyut($f);
        $fp = @fopen($f, 'c+b');
        if (!$fp) {
            return false;
        }
        if (!@flock($fp, LOCK_EX)) {
            @fclose($fp);
            return false;
        }
        $ok = false;
        try {
            if ($kuyrukByte <= 0 || $boyut <= $kuyrukByte) {
                $ok = @ftruncate($fp, 0);
            } else {
                // Son kuyrugu oku, basa yaz, dosyayi kuyruk boyuna kirp
                @fseek($fp, $boyut - $kuyrukByte, SEEK_SET);
                $kuyruk = @stream_get_contents($fp);
                @rewind($fp);
                $yazilan = @fwrite($fp, $kuyruk);
                @fflush($fp);
                $ok = @ftruncate($fp, $yazilan !== false ? $yazilan : 0);
            }
        } catch (\Throwable $e) {
            $ok = false;
        }
        @flock($fp, LOCK_UN);
        @fclose($fp);
        return (bool) $ok;
    }

    protected function guvenliBoyut($f)
    {
        $s = @filesize($f);
        return $s === false ? 0 : $s;
    }

    protected function satir($etiket, $kazanim)
    {
        $this->line('  ' . str_pad($etiket, 48) . $this->boyut($kazanim));
    }

    protected function boyut($byte)
    {
        if ($byte >= 1073741824) {
            return round($byte / 1073741824, 2) . ' GB';
        }
        if ($byte >= 1048576) {
            return round($byte / 1048576, 1) . ' MB';
        }
        if ($byte >= 1024) {
            return round($byte / 1024, 1) . ' KB';
        }
        return $byte . ' B';
    }
}
