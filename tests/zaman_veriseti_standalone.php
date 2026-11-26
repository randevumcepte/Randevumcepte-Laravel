<?php
/**
 * GUNLUK TURKCE ZAMAN IFADELERI — VERI SETI TESTI (Laravel / DB / LLM gerekmez)
 * -------------------------------------------------------------------------
 * tests/veri/zaman_ifadeleri.tsv : ifade -> beklenen tarih / saat / vakit
 * tests/veri/tuzak_cumleler.tsv  : randevu ZAMANI OLMAYAN cumleler (saat/tarih cikarilmamali)
 * SesliRandevuCozService::zamanCoz ile karsilastirir. Bugun SABIT: 2026-11-27 Cuma.
 *
 * Calistir:  php tests/zaman_veriseti_standalone.php          (ozet + hatalar)
 *            php tests/zaman_veriseti_standalone.php -v       (tum satirlar)
 */
namespace Carbon {
    class Carbon extends \DateTime {
        const SUNDAY = 0, MONDAY = 1, TUESDAY = 2, WEDNESDAY = 3, THURSDAY = 4, FRIDAY = 5, SATURDAY = 6;
        static $bugun = '2026-11-27';
        static function today() { return new self(self::$bugun . ' 00:00:00'); }
        static function now() { return new self(self::$bugun . ' 14:00:00'); }
        static function createFromDate($y, $m, $d) { return new self(sprintf('%04d-%02d-%02d', $y, $m, $d)); }
        static function parse($t) { return new self($t); }
        function copy() { return clone $this; }
        function startOfDay() { $this->setTime(0, 0); return $this; }
        function startOfWeek() { return $this->modify('monday this week'); }
        function addDay() { return $this->modify('+1 day'); }
        function addDays($n) { return $this->modify("+$n day"); }
        function addWeek() { return $this->modify('+7 day'); }
        function addWeeks($n) { return $this->modify('+' . (7 * $n) . ' day'); }
        function addMonth() { return $this->modify('+1 month'); }
        function addYear() { return $this->modify('+1 year'); }
        function next($dow) { $n = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][$dow]; return $this->modify("next $n"); }
        function lt($o) { return $this < $o; }
        function gt($o) { return $this > $o; }
        function toDateString() { return $this->format('Y-m-d'); }
        function diffInDays($o) { return (int) $this->diff($o)->days; }
        function __get($k) { if ($k === 'year') return (int) $this->format('Y'); if ($k === 'month') return (int) $this->format('n'); if ($k === 'day') return (int) $this->format('j'); if ($k === 'dayOfWeek') return (int) $this->format('w'); }
    }
}
namespace Illuminate\Support\Facades { class Log { static function __callStatic($m, $a) {} } }
namespace {
    error_reporting(E_ALL & ~E_DEPRECATED);
    function now($tz = null) { return \Carbon\Carbon::now(); }
    require __DIR__ . '/../app/Services/SesliRandevuCozService.php';
    $s = (new ReflectionClass(App\Services\SesliRandevuCozService::class))->newInstanceWithoutConstructor();
    $ayrinti = in_array('-v', $argv, true);

    function tsv($dosya) {
        $satirlar = array_map(function ($l) { return explode("\t", rtrim($l, "\r\n")); }, file(__DIR__ . '/veri/' . $dosya));
        array_shift($satirlar);
        return array_filter($satirlar, function ($r) { return trim($r[0] ?? '') !== ''; });
    }

    // ---- 1) zaman ifadeleri: beklenen dolu alanlar birebir tutmali; belirsiz ("evet") satirlarda bos beklenen = kontrol yok
    $gecen = 0; $kalan = []; $toplam = 0;
    foreach (tsv('zaman_ifadeleri.tsv') as $r) {
        [$ifade, $kat, $vakit, $saat, $tarih, $belirsiz] = array_pad($r, 6, '');
        $z = $s->zamanCoz($ifade);
        $fark = [];
        if ($tarih !== '' && $belirsiz !== 'evet' && $z['tarih'] !== $tarih) $fark[] = "tarih {$tarih}≠" . ($z['tarih'] ?? '-');
        if ($saat !== '' && $belirsiz !== 'evet' && $z['saat'] !== $saat) $fark[] = "saat {$saat}≠" . ($z['saat'] ?? '-');
        // vakit: saat cikarildiysa vakit gereksiz (saat daha kesin)
        if ($vakit !== '' && $belirsiz !== 'evet' && $saat === '' && $z['vakit'] !== $vakit && !$z['saat']) $fark[] = "vakit {$vakit}≠" . ($z['vakit'] ?? '-');
        $toplam++;
        $cikti = sprintf('tarih=%s saat=%s vakit=%s', $z['tarih'] ?? '-', $z['saat'] ?? '-', $z['vakit'] ?? '-');
        if ($fark) $kalan[] = sprintf("  ✗ %-34s %s   [%s]", $ifade, implode(', ', $fark), $cikti);
        else { $gecen++; if ($ayrinti) printf("  ✓ %-34s %s\n", $ifade, $cikti); }
    }
    echo "\n== ZAMAN IFADELERI: {$gecen}/{$toplam} dogru\n" . implode("\n", $kalan) . "\n";

    // ---- 2) tuzaklar: randevu saati/tarihi icermeyen cumleden saat cikarilirsa raporlanir (bilgi amacli)
    $tuz = 0; $tuzSay = 0; $tuzRapor = [];
    foreach (tsv('tuzak_cumleler.tsv') as $r) {
        $z = $s->zamanCoz($r[0]);
        $tuzSay++;
        if ($z['saat'] && preg_match('/\d{1,2}:\d{2}|randevu talebi|kesin/u', $r[1] ?? '')) {
            $tuz++; $tuzRapor[] = sprintf("  ! %-40s saat=%s  (doğru yorum: %s)", $r[0], $z['saat'], $r[2] ?? '');
        }
    }
    echo "\n== TUZAKLAR: {$tuzSay} cumlenin {$tuz} tanesinde saat cikarildi (baglam kontrolu gerekir)\n" . implode("\n", $tuzRapor) . "\n";
    exit($kalan ? 1 : 0);
}
