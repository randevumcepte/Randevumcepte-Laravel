<?php

namespace App\Services;

/**
 * Turkce metin -> dil (resources/dil/{dil}.json). Kurallar uygulama (lib/dil/ceviri.dart) ve web
 * (public/js/musteri-dil.js) ile AYNI: birebir, bosluk/buyuk harf farki, {} kaliplari, tarih adlari,
 * bas/son simge, parcalar (satir, • · , : ve cumle). Bulunamayan parca Turkce kalir.
 */
class DilSozluk
{
    const AY = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    const GUN = ['Cumartesi', 'Pazartesi', 'Çarşamba', 'Perşembe', 'Pazar', 'Salı', 'Cuma'];
    const AY_D = [
        'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'ru' => ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
        'de' => ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
    ];
    const HARF = 'A-Za-zÇĞİÖŞÜçğıöşü';

    /** @var DilSozluk[] */
    private static $ornek = [];

    private $dil;
    private $s = [];
    private $kucuk = [];
    private $kaliplar = [];
    /** Cevrilemeyen (Turkce kalan) parca var mi — AI yedegi buna bakar */
    private $eksik = false;

    public static function al($dil, array $ek = [])
    {
        $an = $dil . '|' . ($ek ? md5(serialize($ek)) : '');
        if (!isset(self::$ornek[$an])) self::$ornek[$an] = new self($dil, $ek);
        return self::$ornek[$an];
    }

    public static function dosya($dil)
    {
        return resource_path('dil/' . $dil . '.json');
    }

    public static function surum($dil)
    {
        static $s = [];
        if (!isset($s[$dil])) {
            $j = is_file(self::dosya($dil)) ? json_decode(file_get_contents(self::dosya($dil)), true) : null;
            $s[$dil] = (string) ($j['surum'] ?? '');
        }
        return $s[$dil];
    }

    private function __construct($dil, array $ek)
    {
        $this->dil = $dil;
        $j = is_file(self::dosya($dil)) ? json_decode(file_get_contents(self::dosya($dil)), true) : null;
        foreach ($ek + (array) ($j['sozluk'] ?? []) as $tr => $c) {
            $tr = (string) $tr;
            if (strpos($tr, '{}') !== false || strpos($tr, '{#}') !== false) {
                $this->kaliplar[] = $this->kalip($tr, $c);
                continue;
            }
            $this->s[$tr] = $c;
            $b = self::bosluk($tr);
            if (!isset($this->s[$b])) $this->s[$b] = $c;
            $k = self::kucult($b);
            if (!isset($this->kucuk[$k])) $this->kucuk[$k] = $c;
        }
        usort($this->kaliplar, function ($a, $b) { return strlen($b[0]) - strlen($a[0]); });
    }

    private function kalip($tr, $c)
    {
        $desen = '';
        $sabit = '';
        foreach (preg_split('/(\{#?\})/', $tr, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $p) {
            if ($p === '{}') { $desen .= '([^\n]+?)'; continue; }
            if ($p === '{#}') { $desen .= '([\d.,:%+\-\/]+)'; continue; }
            foreach (preg_split('/(\s+)/u', $p, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $x) {
                if (trim($x) === '') { $desen .= strpos($x, "\n") !== false ? '\s*\n\s*' : '[^\S\n]+'; continue; }
                $desen .= preg_quote($x, '/');
                if (strlen($x) > strlen($sabit)) $sabit = $x;
            }
        }
        return ['/^' . $desen . '$/u', $c, $sabit];
    }

    private static function bosluk($s) { return trim(preg_replace('/\s+/u', ' ', $s)); }

    private static function kucult($s) { return mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $s), 'UTF-8'); }

    private static function harf($s) { return (bool) preg_match('/[' . self::HARF . ']/u', $s); }

    /** @return string */
    public function cevir($metin)
    {
        $this->eksik = false;
        $metin = (string) $metin;
        if ($metin === '' || !self::harf($metin)) return $metin;
        if (!preg_match('/^(\s*)([\s\S]*?)(\s*)$/u', $metin, $m) || $m[2] === '') return $metin;
        $c = $this->bul($m[2], 0);
        if ($c === null) { $this->eksik = true; return $metin; }
        return $m[1] . $c . $m[3];
    }

    /** Son cevir() cagrisinda Turkce kalan cumle/satir oldu mu? */
    public function eksikMi() { return $this->eksik; }

    private function tam($s)
    {
        if (isset($this->s[$s])) return $this->s[$s];
        $b = self::bosluk($s);
        if (isset($this->s[$b])) return $this->s[$b];
        if (mb_strlen($b) < 3) return null; // "AD" bas harfleri "Ad" sanilmasin
        $k = self::kucult($b);
        if (!isset($this->kucuk[$k])) return null;
        $buyuk = mb_strlen($s) > 2 && $s === mb_strtoupper($s, 'UTF-8') && $s !== mb_strtolower($s, 'UTF-8');
        return $buyuk ? mb_strtoupper($this->kucuk[$k], 'UTF-8') : $this->kucuk[$k];
    }

    private function bul($s, $derin)
    {
        $t = $this->tam($s);
        if ($t !== null) return $t;
        if ($derin > 3) return null;

        foreach ($this->kaliplar as $k) {
            if ($k[2] !== '' && strpos($s, $k[2]) === false) continue;
            if (!preg_match($k[0], $s, $m)) continue;
            $sira = 0;
            return preg_replace_callback('/\{(\d?)#?\}/', function ($x) use ($m, &$sira, $derin) {
                $no = $x[1] === '' ? ++$sira : (int) $x[1];
                $parca = $m[$no] ?? '';
                if (!self::harf($parca) || in_array(trim($parca), self::AY, true)) return $parca; // "Sayın Eylül" kisi adi
                $eksik = $this->eksik;
                $c = $this->bul(trim($parca), $derin + 1);
                $this->eksik = $eksik; // ad / link / adres gibi parcalar cevrilmeyebilir
                return $c === null ? $parca : $c;
            }, $k[1]);
        }

        // Cok satirli metin satir satir (bas/son simge ayiklamadan ONCE: satirlar bozulmasin)
        if (strpos($s, "\n") !== false) return $this->parcala(explode("\n", $s), "\n", $derin, false);

        if (preg_match('/^(\d{1,2}[:.]\d{2}) +(.+)$/u', $s, $m)) { // "14:00 Manikür"
            $c = $this->bul($m[2], $derin + 1);
            if ($c !== null) return $m[1] . ' ' . $c;
        }

        $tarihli = $this->tarih($s);
        if ($tarihli !== null && !self::harf(preg_replace('/' . implode('|', array_merge(self::AY, self::GUN)) . '/u', '', $s))) return $tarihli;

        if (preg_match('/^([^' . self::HARF . '0-9%]*)([\s\S]*?)([^' . self::HARF . '0-9%)]*)$/u', $s, $m)
            && ($m[1] !== '' || $m[3] !== '') && $m[2] !== '') {
            $c = $this->bul($m[2], $derin + 1);
            if ($c !== null) return $m[1] . $c . $m[3];
        }

        $cumle = preg_split('/(?<=[.!?])\s+/u', $s);
        if (count($cumle) > 1) {
            $p = $this->parcala($cumle, ' ', $derin, false);
            if ($p !== null) return $p;
        }

        foreach ([' • ', ' · ', '  ·  ', ' | ', ' — ', ' – ', ' / ', ' + ', ', ', ': '] as $ayrac) {
            if (strpos($s, $ayrac) === false) continue;
            $p = $this->parcala(explode($ayrac, $s), $ayrac, $derin, $ayrac === ', ');
            if ($p !== null) return $p;
        }
        return $tarihli;
    }

    private function parcala(array $parcalar, $ayrac, $derin, $hepsiSart)
    {
        $degisti = false;
        $hepsi = true;
        $eksikDurum = $this->eksik;
        $c = array_map(function ($x) use ($derin, &$degisti, &$hepsi) {
            if (trim($x) === '' || !self::harf($x) || preg_match('#^\s*https?://\S+\s*$#', $x)) return $x;
            $y = $this->bul(trim($x), $derin + 1);
            if ($y === null) { $hepsi = false; return $x; }
            $degisti = true;
            return $y;
        }, $parcalar);
        if (!$degisti || ($hepsiSart && !$hepsi)) { $this->eksik = $eksikDurum; return null; }
        if (!$hepsi) $this->eksik = true;
        return implode($ayrac, $c);
    }

    private function tarih($s)
    {
        if (!isset(self::AY_D[$this->dil])) return null;
        $r = preg_replace_callback('/(\d{1,2}) (' . implode('|', self::AY) . ')(?![' . self::HARF . '])/u', function ($m) {
            $ay = self::AY_D[$this->dil][array_search($m[2], self::AY, true)];
            return $this->dil === 'de' ? $m[1] . '. ' . $ay : $m[1] . ' ' . $ay;
        }, $s);
        $r = preg_replace_callback('/(?<![' . self::HARF . '])(' . implode('|', self::GUN) . ')(?![' . self::HARF . '])/u', function ($m) {
            return $this->s[$m[1]] ?? $m[1];
        }, $r);
        return $r === $s ? null : $r;
    }
}
