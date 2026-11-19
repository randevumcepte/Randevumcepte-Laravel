<?php

namespace App\Services;

/**
 * Merkezi musteri ifade kutuphanesi motoru (database/ifade_kutuphanesi/*.json).
 * "ojemi kaldirtmak istiyorum" -> rc_kalici_oje_cikarma; salonun hizmet listesiyle kesisince tek hizmet ya da soru.
 * Saf PHP (DB yok): WhatsApp asistani, AI santral, sesli randevu ve Patron AI ayni motoru kullanir.
 *
 * Sira: normalize -> duzeltmeler (yazim/ses, yalniz kelime) -> esler (baglamli donusum) -> atilacak -> kavram eslesmesi
 *       (ardisik, ek toleransli, haric eler, uzun ifade kisayi ezer) -> belirsiz -> salon katalogu (secim()).
 */
class IfadeKutuphanesi
{
    protected static $veri = null;
    const YUMUSAMA = ['t' => 'd', 'k' => 'g', 'p' => 'b', 'c' => 'c'];
    /** Kisa koklerde izin verilen ekler (sadelestirilmis): cogul + iyelik + hal. "kaslarimi", "boyasini", "sacimin". */
    const EK = '/^(l[ae]r)?([iu]m|[iu]n|[iu]|s[iu]|[iu]m[iu]z|[iu]n[iu]z|m|n|m[iu]z|n[iu]z)?(n?[iuea]|y[iuea]|n?d[ae]|n?d[ae]n|n?l[ae]|n[iu]n)?$/';

    // ------------------------------------------------------------------ yukleme
    public static function yukle(?string $dizin = null): array
    {
        if (self::$veri !== null && $dizin === null) return self::$veri;
        $dizin = $dizin ?: dirname(__DIR__, 2) . '/database/ifade_kutuphanesi';
        $genel = json_decode((string) @file_get_contents($dizin . '/genel.json'), true) ?: [];
        $kavram = [];
        foreach (glob($dizin . '/*.json') as $f) {
            if (basename($f) === 'genel.json') continue;
            $j = json_decode((string) file_get_contents($f), true) ?: [];
            foreach ((array) ($j['kavramlar'] ?? []) as $k) {
                $k['sektor'] = $j['sektor'] ?? basename($f, '.json');
                $k['_ifade'] = array_values(array_filter(array_map([self::class, 'kelimeler'], (array) ($k['ifadeler'] ?? []))));
                $k['_haric'] = array_values(array_filter(array_map([self::class, 'kelimeler'], (array) ($k['haric'] ?? []))));
                $kavram[$k['id']] = $k;
            }
        }
        $korunan = [];
        foreach ($kavram as $k) foreach ($k['_ifade'] as $w) foreach ($w as $x) $korunan[$x] = 1;
        foreach ((array) ($genel['belirsiz'] ?? []) as $b) foreach (self::kelimeler($b['ifade']) as $x) $korunan[$x] = 1;
        $cocuk = [];
        foreach ($kavram as $id => $k) if (!empty($k['ust'])) $cocuk[$k['ust']][] = $id;
        $v = ['genel' => $genel, 'kavram' => $kavram, 'korunan' => $korunan, 'cocuk' => $cocuk,
            'atilacak' => array_fill_keys(array_map([self::class, 'sade'], (array) ($genel['atilacak'] ?? [])), 1)];
        return self::$veri = $v;
    }

    /** Kutuphane surumu + dosya degisiklik zamani (onbellek anahtari icin). */
    public static function surum(): string
    {
        $d = dirname(__DIR__, 2) . '/database/ifade_kutuphanesi';
        $t = 0;
        foreach (glob($d . '/*.json') as $f) $t = max($t, (int) @filemtime($f));
        return (self::yukle()['genel']['surum'] ?? '0') . '-' . $t;
    }

    // ------------------------------------------------------------------ metin
    /** kucuk harf + Turkce karakter sadelestirme + noktalama -> bosluk */
    public static function sade(string $s): string
    {
        $s = str_replace(['İ', 'I'], ['i', 'ı'], $s);
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u', 'â' => 'a', 'î' => 'i', 'û' => 'u', "\u{0307}" => '']);
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    public static function kelimeler(string $s): array
    {
        $s = self::sade($s);
        return $s === '' ? [] : explode(' ', $s);
    }

    /** Metin kelimesi (tw) ifade kelimesine (iw) uyuyor mu: ekli hal, eksik ek, yumusama (tirnag~tirnak). */
    public static function uyar(string $tw, string $iw): bool
    {
        if ($tw === $iw) return true;
        $lt = strlen($tw); $li = strlen($iw);
        // yumusama: cilt -> cildi, tirnak -> tirnagi, kitap -> kitabi
        $kok = [$iw];
        if ($li >= 3 && isset(self::YUMUSAMA[$iw[$li - 1]])) $kok[] = substr($iw, 0, -1) . self::YUMUSAMA[$iw[$li - 1]];
        foreach ($kok as $k) {
            if ($li >= 3 && strncmp($tw, $k, $li) === 0) {
                $ek = substr($tw, $li);
                if ($k !== $iw && $ek === '') continue; // yumusak kok tek basina ("cild") kelime degil
                // kisa kokte (kas, sac, dip...) yalniz bilinen ekler; uzun kokte 7 harfe kadar her ek
                if ($li >= 5 ? strlen($ek) <= 7 : (bool) preg_match(self::EK, $ek)) return true;
            }
        }
        if ($lt >= 4 && strncmp($iw, $tw, $lt) === 0 && $li - $lt <= 2) return true;
        if ($li >= 5 && $lt >= 5) {
            $n = 0; $m = min($lt, $li);
            while ($n < $m && $tw[$n] === $iw[$n]) $n++;
            if ($n >= $li - 1 && $n >= 4) return true; // tirnag|imi ~ tirnak
        }
        return false;
    }

    /** Desen: "kaldir*" onek, digerleri uyar(). */
    protected static function desenUyar(string $tw, string $desen): bool
    {
        $d = self::sade(rtrim($desen, '*'));
        if (substr($desen, -1) === '*') return $d !== '' && strncmp($tw, $d, strlen($d)) === 0;
        return self::uyar($tw, $d);
    }

    /** Ifade (kelime dizisi) metinde ardisik geciyor mu: [baslangic, bitis] ya da null. */
    public static function bul(array $tws, array $iws): ?array
    {
        $n = count($iws);
        if (!$n) return null;
        for ($i = 0; $i + $n <= count($tws); $i++) {
            $ok = true;
            for ($j = 0; $j < $n; $j++) if (!self::uyar($tws[$i + $j], $iws[$j])) { $ok = false; break; }
            if ($ok) return [$i, $i + $n - 1];
        }
        return null;
    }

    protected static function baglamVar(array $tws, array $desenler): bool
    {
        foreach ($desenler as $d) {
            if (strpos(trim($d), ' ') !== false) { if (self::bul($tws, self::kelimeler($d))) return true; continue; }
            foreach ($tws as $tw) if (self::desenUyar($tw, $d)) return true;
        }
        return false;
    }

    /** Duzeltme + es donusum + atma: islenmis kelime dizisi. */
    public static function isle(string $metin): array
    {
        $v = self::yukle(); $g = $v['genel'];
        // Turkce karakterle yazan musteri "kas" yazdiysa kas/kasim/kasa demektir, kas degil (sadelestirince carpisiyor)
        if (preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $metin)) $metin = preg_replace('/(?<![\p{L}])([kK])as/u', '$1azs', $metin);
        $s = ' ' . self::sade($metin) . ' ';
        foreach ((array) ($g['birlesik_duzeltmeler'] ?? []) as $b) $s = str_replace(' ' . self::sade($b['yanlis']) . ' ', ' ' . self::sade($b['dogru']) . ' ', $s);
        $tws = self::kelimeler($s);
        foreach ((array) ($g['duzeltmeler'] ?? []) as $d) {
            $yanlis = array_map([self::class, 'sade'], (array) $d['yanlis']);
            foreach ($tws as $i => $tw) {
                if (!in_array($tw, $yanlis, true)) continue;
                $diger = $tws; unset($diger[$i]); $diger = array_values($diger);
                if (!empty($d['baglam']) && !self::baglamVar($diger, $d['baglam'])) continue;
                if (!empty($d['haric']) && self::baglamVar($diger, $d['haric'])) continue;
                $tws[$i] = self::sade($d['dogru']);
            }
            $tws = self::kelimeler(implode(' ', $tws));
        }
        foreach ((array) ($g['esler'] ?? []) as $e) {
            $yeni = $tws;
            foreach ($tws as $i => $tw) {
                $esle = false;
                foreach ((array) $e['kelimeler'] as $k) if (self::desenUyar($tw, $k)) { $esle = true; break; }
                if (!$esle) continue;
                $diger = $tws; unset($diger[$i]); $diger = array_values($diger);
                if (!empty($e['baglam']) && !self::baglamVar($diger, $e['baglam'])) continue;
                if (!empty($e['haric']) && self::baglamVar($diger, $e['haric'])) continue;
                $yeni[$i] = self::sade($e['standart']);
            }
            $tws = self::kelimeler(implode(' ', $yeni));
        }
        return array_values(array_filter($tws, function ($w) use ($v) { return isset($v['korunan'][$w]) || !isset($v['atilacak'][$w]); }));
    }

    /**
     * Metindeki hizmet kavramlari (salon bagimsiz).
     * @return array ['adaylar' => [id...], 'soru' => ?string, 'en_olasi' => ?string, 'islenmis' => string, 'eslesme' => [[id, ifade]]]
     */
    public static function cozumle(string $metin, bool $belirsizKullan = true): array
    {
        $v = self::yukle();
        $tws = self::isle($metin);
        $es = [];
        foreach ($v['kavram'] as $id => $k) {
            $haric = false;
            foreach ($k['_haric'] as $h) if (self::bul($tws, $h)) { $haric = true; break; }
            if ($haric) continue;
            $en = null;
            foreach ($k['_ifade'] as $iw) {
                $sp = self::bul($tws, $iw);
                if ($sp && (!$en || count($iw) > $en['n'] || (count($iw) === $en['n'] && strlen(implode(' ', $iw)) > strlen($en['ifade'])))) $en = ['id' => $id, 'sp' => $sp, 'n' => count($iw), 'ifade' => implode(' ', $iw)];
            }
            if ($en) $es[] = $en;
        }
        $bel = [];
        if ($belirsizKullan) foreach ((array) ($v['genel']['belirsiz'] ?? []) as $b) {
            $iw = self::kelimeler($b['ifade']);
            foreach ((array) ($b['haric'] ?? []) as $h) if (self::bul($tws, self::kelimeler($h))) continue 2;
            if ($sp = self::bul($tws, $iw)) $bel[] = ['b' => $b, 'sp' => $sp, 'n' => count($iw)];
        }
        // uzun ifade kisayi ezer: tamamen baska bir eslesmenin icinde kalan ve daha az kelimeli eslesme elenir
        $ici = function ($a, $tum) { foreach ($tum as $o) if ($o['n'] > $a['n'] && $o['sp'][0] <= $a['sp'][0] && $o['sp'][1] >= $a['sp'][1]) return true; return false; };
        $es2 = array_values(array_filter($es, function ($a) use ($es, $ici) { return !$ici($a, $es); }));
        $bel2 = array_values(array_filter($bel, function ($a) use ($es, $bel, $ici) { return !$ici($a, array_merge($es, $bel)); }));
        $ad = array_map(function ($a) { return $a['id']; }, $es2);
        $soru = null; $olasi = null;
        foreach ($bel2 as $b) {
            // belirsiz ifade, kavram eslesmesiyle ayni ya da daha uzunsa aday listesini genisletir
            $kapsiyor = true;
            foreach ($es2 as $a) if ($a['n'] > $b['n']) { $kapsiyor = false; break; }
            if (!$kapsiyor) continue;
            $ad = array_merge($ad, (array) $b['b']['adaylar']);
            $soru = $soru ?: ($b['b']['soru'] ?? null); $olasi = $olasi ?: ($b['b']['en_olasi'] ?? null);
        }
        return ['adaylar' => array_values(array_unique($ad)), 'soru' => $soru, 'en_olasi' => $olasi, 'islenmis' => implode(' ', $tws),
            'eslesme' => array_map(function ($a) { return [$a['id'], $a['ifade']]; }, $es2)];
    }

    // ------------------------------------------------------------------ salon katalogu
    /** Salon hizmet adlari -> kavram: [kavram_id => [hizmet_id...]] (hizmet adi ayni motordan gecer, belirsiz kullanilmaz). */
    public static function katalog(array $hizmetler): array
    {
        $map = [];
        foreach ($hizmetler as $hid => $ad) foreach (self::cozumle((string) $ad, false)['adaylar'] as $kid) $map[$kid][] = (int) $hid;
        return $map;
    }

    /**
     * Musteri ifadesi + salon hizmetleri -> karar.
     * @param array $hizmetler [hizmet_id => ad]
     * @return array ['durum' => 'tek'|'coklu'|'yok', 'hizmetler' => [hizmet_id...], 'soru' => ?string, 'kavramlar' => [...]]
     */
    public static function secim(string $metin, array $hizmetler, ?array $katalog = null): array
    {
        $v = self::yukle();
        $katalog = $katalog ?? self::katalog($hizmetler);
        $c = self::cozumle($metin);
        $bul = function ($kid) use ($katalog, $v) {
            $h = $katalog[$kid] ?? [];
            foreach ($v['cocuk'][$kid] ?? [] as $alt) $h = array_merge($h, $katalog[$alt] ?? []);
            if (!$h && !empty($v['kavram'][$kid]['ust'])) $h = $katalog[$v['kavram'][$kid]['ust']] ?? [];
            return $h;
        };
        $sonuc = [];
        foreach ($c['adaylar'] as $kid) foreach ($bul($kid) as $hid) $sonuc[$hid] = 1;
        $ids = array_keys($sonuc);
        $durum = count($ids) === 1 ? 'tek' : (count($ids) ? 'coklu' : 'yok');
        return ['durum' => $durum, 'hizmetler' => $ids, 'soru' => $durum === 'coklu' ? $c['soru'] : null, 'kavramlar' => $c['adaylar'], 'islenmis' => $c['islenmis']];
    }
}
