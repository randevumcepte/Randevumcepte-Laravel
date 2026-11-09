<?php

namespace App\Console\Commands;

use App\Services\SistemBildirim;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Performans nobetcisi (dakikada bir). Veritabani / bellek / yavas istek esikleri asilinca o anin
 * goruntusunu performans_anliklari'na yazar ve sistem bildirim numaralarina (WhatsApp, yoksa SMS)
 * alarm gonderir. Guvenlik watchdog'unun load uyarisi CPU'ya bakar; buradaki sinyaller DB tarafli
 * takilmalari yakalar (load dusukken de panel donabiliyor).
 *
 * Host'tan mysql CLI baglanamadigi icin (docker grant) DB'ye uygulama baglantisiyla gidilir.
 */
class PerformansNobetci extends Command
{
    protected $signature = 'performans:nobetci {--zorla : esik asilmasa da goruntu al (deneme)}';

    protected $description = 'DB/bellek/yavas istek esiklerini izler; asilinca goruntu alir ve alarm gonderir';

    const UZUN_SORGU_SN      = 15;   // bu sureden uzun calisan sorgu
    const BOS_BELLEK_MB      = 400;  // MemAvailable bunun altina inerse
    const YAVAS_ISTEK_MS     = 5000; // yavas istek esigi (sayim icin)
    const YAVAS_ISTEK_ADET   = 8;    // son 5 dk'da bu kadar yavas istek
    const ZAMAN_ASIMI_ADET   = 3;    // son 5 dk'da bu kadar tarayici zaman asimi
    const LOAD_CARPANI       = 3;    // load1 > cekirdek * carpan
    const ALARM_ARALIGI_DK   = 30;   // ayni kisa ariza icin tekrar alarm araligi
    const SAKLAMA_GUN        = 14;

    public function handle()
    {
        if (!Schema::hasTable('performans_anliklari')) {
            return 0;
        }

        $bellek = $this->bellek();
        $load1 = $this->load1();
        $cekirdek = $this->cekirdekSayisi();
        $sorgular = $this->calisanSorgular();
        $uzunlar = array_values(array_filter($sorgular, function ($s) { return $s['sn'] >= self::UZUN_SORGU_SN; }));
        list($yavasAdet, $zamanAsimiAdet, $yavaslar) = $this->sonYavasIstekler();

        $nedenler = [];
        if ($uzunlar) {
            $nedenler[] = count($uzunlar).' sorgu '.self::UZUN_SORGU_SN.' sn+ calisiyor';
        }
        if ($bellek['bos_mb'] !== null && $bellek['bos_mb'] < self::BOS_BELLEK_MB) {
            $nedenler[] = 'bos bellek '.$bellek['bos_mb'].' MB';
        }
        if ($yavasAdet >= self::YAVAS_ISTEK_ADET) {
            $nedenler[] = '5 dk icinde '.$yavasAdet.' yavas istek';
        }
        if ($zamanAsimiAdet >= self::ZAMAN_ASIMI_ADET) {
            $nedenler[] = '5 dk icinde '.$zamanAsimiAdet.' tarayici zaman asimi';
        }
        if ($load1 !== null && $load1 > $cekirdek * self::LOAD_CARPANI) {
            $nedenler[] = 'load '.$load1;
        }

        $this->eskileriTemizle();

        if (!$nedenler && !$this->option('zorla')) {
            return 0;
        }
        if (!$nedenler) {
            $nedenler[] = 'elle deneme';
        }

        $enUzunSn = $sorgular ? max(array_column($sorgular, 'sn')) : 0;
        $id = DB::table('performans_anliklari')->insertGetId([
            'created_at'         => date('Y-m-d H:i:s'),
            'nedenler'           => mb_substr(implode('; ', $nedenler), 0, 500),
            'load1'              => $load1,
            'bos_bellek_mb'      => $bellek['bos_mb'],
            'swap_kullanilan_mb' => $bellek['swap_mb'],
            'uzun_sorgu_sayisi'  => count($uzunlar),
            'en_uzun_sorgu_sn'   => $enUzunSn,
            'yavas_istek_sayisi' => $yavasAdet,
            'zaman_asimi_sayisi' => $zamanAsimiAdet,
            'detay'              => json_encode([
                'sorgular'  => $sorgular,
                'yavaslar'  => $yavaslar,
                'surecler'  => $this->surecler(),
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        ]);

        // Elle deneme alarm gondermez; gercek arizada 30 dk'da bir alarm.
        $sonAlarmVar = DB::table('performans_anliklari')->where('alarm_gonderildi', 1)
            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - self::ALARM_ARALIGI_DK * 60))->exists();
        if (!$this->option('zorla') && !$sonAlarmVar) {
            $mesaj = $this->alarmMetni($nedenler, $uzunlar, $yavasAdet, $zamanAsimiAdet, $bellek, $load1, $yavaslar);
            try {
                $r = SistemBildirim::gonder($mesaj);
                if (!empty($r['ok'])) {
                    DB::table('performans_anliklari')->where('id', $id)->update(['alarm_gonderildi' => 1]);
                }
            } catch (\Throwable $e) {
                Log::warning('[performans:nobetci] alarm gonderilemedi', ['hata' => $e->getMessage()]);
            }
        }

        $this->info('Goruntu #'.$id.': '.implode('; ', $nedenler));
        return 0;
    }

    /** Uyuyan baglantilar ve nobetcinin kendi sorgusu haric, calisan sorgular (uzundan kisaya). */
    private function calisanSorgular()
    {
        try {
            $satirlar = DB::select('SHOW FULL PROCESSLIST');
        } catch (\Throwable $e) {
            return [];
        }
        $liste = [];
        foreach ($satirlar as $s) {
            $s = array_change_key_case((array) $s, CASE_LOWER);
            $komut = (string) ($s['command'] ?? '');
            $sql = (string) ($s['info'] ?? '');
            if (in_array($komut, ['Sleep', 'Daemon', 'Binlog Dump'], true) || $sql === '' || stripos($sql, 'SHOW FULL PROCESSLIST') !== false) {
                continue;
            }
            $liste[] = [
                'sn'    => (int) ($s['time'] ?? 0),
                'durum' => mb_substr((string) ($s['state'] ?? ''), 0, 64),
                'db'    => (string) ($s['db'] ?? ''),
                'sql'   => self::sqlMaskele($sql),
            ];
        }
        usort($liste, function ($a, $b) { return $b['sn'] <=> $a['sn']; });

        return array_slice($liste, 0, 30);
    }

    /** Processlist SQL'i degerleriyle gelir: tirnakli metinler (isim/telefon) maskelenir. */
    private static function sqlMaskele($sql)
    {
        $sql = preg_replace("/'(?:[^'\\\\]|\\\\.)*'/s", "'?'", $sql);
        $sql = preg_replace('/\b\d{7,}\b/', '?', $sql); // uzun sayilar (telefon)
        $sql = preg_replace('/\s+/', ' ', $sql);

        return mb_substr($sql, 0, 2000);
    }

    /** Son 5 dk: yavas istek / zaman asimi sayilari + en yavas uclar. */
    private function sonYavasIstekler()
    {
        if (!Schema::hasTable('yavas_istekler')) {
            return [0, 0, []];
        }
        $sinir = date('Y-m-d H:i:s', time() - 300);
        try {
            $yavas = (int) DB::table('yavas_istekler')->where('created_at', '>=', $sinir)
                ->where('method', '<>', 'TIMEOUT')->where('sure_ms', '>=', self::YAVAS_ISTEK_MS)->count();
            $zamanAsimi = (int) DB::table('yavas_istekler')->where('created_at', '>=', $sinir)
                ->where('method', 'TIMEOUT')->count();
            $uclar = DB::table('yavas_istekler')->where('created_at', '>=', $sinir)
                ->selectRaw("COALESCE(aksiyon, uri) as uc, method, COUNT(*) as adet, MAX(sure_ms) as en_uzun_ms, GROUP_CONCAT(DISTINCT salon_id) as salonlar")
                ->groupBy('uc', 'method')->orderBy('en_uzun_ms', 'desc')->limit(10)->get()
                ->map(function ($r) { return (array) $r; })->all();
        } catch (\Throwable $e) {
            return [0, 0, []];
        }

        return [$yavas, $zamanAsimi, $uclar];
    }

    private function bellek()
    {
        $m = [];
        foreach (@file('/proc/meminfo') ?: [] as $satir) {
            if (preg_match('/^(\w+):\s+(\d+)/', $satir, $e)) {
                $m[$e[1]] = (int) $e[2];
            }
        }

        return [
            'bos_mb'  => isset($m['MemAvailable']) ? intdiv($m['MemAvailable'], 1024) : null,
            'swap_mb' => isset($m['SwapTotal'], $m['SwapFree']) ? intdiv($m['SwapTotal'] - $m['SwapFree'], 1024) : null,
        ];
    }

    private function load1()
    {
        $l = function_exists('sys_getloadavg') ? sys_getloadavg() : false;

        return $l ? round((float) $l[0], 2) : null;
    }

    private function cekirdekSayisi()
    {
        $n = preg_match_all('/^processor\s*:/m', (string) @file_get_contents('/proc/cpuinfo'));

        return $n > 0 ? $n : 1;
    }

    /** Bellegi en cok kullanan surecler (ps). shell_exec kapaliysa bos. */
    private function surecler()
    {
        if (!function_exists('shell_exec')) {
            return null;
        }
        $cikti = @shell_exec('ps -eo pid,etime,pcpu,rss,comm --sort=-rss 2>/dev/null | head -15');

        return $cikti ? mb_substr($cikti, 0, 4000) : null;
    }

    private function alarmMetni(array $nedenler, array $uzunlar, $yavasAdet, $zamanAsimiAdet, array $bellek, $load1, array $yavaslar)
    {
        $m = "⚠️ Performans uyarısı\n".implode("\n", array_map(function ($n) { return '• '.$n; }, $nedenler))."\n";
        if ($uzunlar) {
            $m .= 'En uzun sorgu: '.$uzunlar[0]['sn'].' sn — '.mb_substr($uzunlar[0]['sql'], 0, 120)."\n";
        }
        if ($yavaslar) {
            $m .= 'En yavaş uç: '.$yavaslar[0]['uc'].' ('.round($yavaslar[0]['en_uzun_ms'] / 1000, 1).' sn, salon '.($yavaslar[0]['salonlar'] ?: '-').")\n";
        }
        $m .= 'Boş RAM: '.($bellek['bos_mb'] ?? '?').' MB · Swap: '.($bellek['swap_mb'] ?? '?').' MB · Load: '.($load1 ?? '?')."\n";
        $m .= date('d.m.Y H:i').' · Ayrıntı: Sistem Yönetimi > Sistem Performansı';

        return $m;
    }

    /** Saatte bir: eski goruntuler ve yavas istek kayitlari. */
    private function eskileriTemizle()
    {
        if ((int) date('i') !== 7) {
            return;
        }
        try {
            DB::table('performans_anliklari')->where('created_at', '<', date('Y-m-d H:i:s', time() - self::SAKLAMA_GUN * 86400))->delete();
        } catch (\Throwable $e) {
        }
    }
}
