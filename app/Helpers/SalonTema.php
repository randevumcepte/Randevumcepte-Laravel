<?php

namespace App\Helpers;

/**
 * SALON TEMA KOPRUSU
 *
 * Flutter uygulamasindaki "Gorunum" ekraninda secilen tema rengini, mini web
 * sitesinin (HomeController@salonDetay_anasayfa -> salondetaylari.blade) renk
 * paletine cevirir.
 *
 * Flutter tarafi (lib/theme/app_theme.dart) bir Material-3 "seed" rengi uzerine
 * kuruludur: preset anahtari (16 hazir tema) + opsiyonel ozel renk (ARGB int).
 * Burada ayni preset->seed haritasini BIREBIR aynaliyoruz. Seed'den mini sitenin
 * ihtiyac duydugu 4 marka tonunu (brand / brand-2 / brand-3 / brand-dark) HSL
 * aciklik kaydirmasiyla turetiyoruz (Material HCT'yi birebir taklit etmeye gerek
 * yok; landing icin tutarli ve okunur sonuc yeterli).
 *
 * KAYNAK HATTI: app_theme.dart AppThemeCatalog.presets.seedColor (0xAARRGGBB).
 * Guncellenirse burasi da guncellenmeli.
 */
class SalonTema
{
    /** Flutter preset anahtari => seed hex (#RRGGBB). app_theme.dart ile ayni. */
    const PRESET_SEED = [
        'mor_luks'         => '#7C3AED',
        'pastel_lavanta'   => '#B794F4',
        'pastel_seftali'   => '#FB923C',
        'pastel_mint'      => '#5EEAD4',
        'pastel_bebe_mavi' => '#93C5FD',
        'pastel_pembe'     => '#F9A8D4',
        'pastel_sari'      => '#FCD34D',
        'okyanus'          => '#0EA5E9',
        'yesil_naturel'    => '#10B981',
        'gul_pembesi'      => '#EC4899',
        'altin_karamel'    => '#D97706',
        'mercan_corali'    => '#FB7185',
        'siyah_beyaz'      => '#1F2937',
        'gri'              => '#6B7280',
        'antrasit'         => '#334155',
        'klasik_koyu'      => '#6366F1',
    ];

    /** Mini sitenin tarihsel varsayilan markasi (salon-landing.css :root). */
    const VARSAYILAN_SEED = '#5C008E';

    /**
     * Flutter config dizisinden ({preset, customSeedArgb, brightnessMode})
     * tek bir seed hex'i cozer. Gecersiz/eksik ise varsayilan moru dondurur.
     */
    public static function seedCoz($config): string
    {
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        if (!is_array($config)) {
            return self::VARSAYILAN_SEED;
        }

        $preset = isset($config['preset']) ? strtolower(trim($config['preset'])) : 'mor_luks';

        if ($preset === 'ozel') {
            $hex = self::argbToHex($config['customSeedArgb'] ?? null);
            return $hex ?: self::VARSAYILAN_SEED;
        }

        return self::PRESET_SEED[$preset] ?? self::VARSAYILAN_SEED;
    }

    /** ARGB int (0xAARRGGBB) -> #RRGGBB. Gecersizse null. */
    public static function argbToHex($argb)
    {
        if ($argb === null || $argb === '' || !is_numeric($argb)) {
            return null;
        }
        $argb = (int) $argb;
        $r = ($argb >> 16) & 0xFF;
        $g = ($argb >> 8) & 0xFF;
        $b = $argb & 0xFF;
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /** #RRGGBB -> [r,g,b] (0-255). */
    public static function hexToRgb($hex): array
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return [92, 0, 142]; // varsayilan mor
        }
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function rgbToHex(array $rgb): string
    {
        return sprintf('#%02X%02X%02X',
            max(0, min(255, (int) round($rgb[0]))),
            max(0, min(255, (int) round($rgb[1]))),
            max(0, min(255, (int) round($rgb[2])))
        );
    }

    /** [r,g,b] (0-255) -> [h(0-360), s(0-1), l(0-1)] */
    private static function rgbToHsl(array $rgb): array
    {
        $r = $rgb[0] / 255; $g = $rgb[1] / 255; $b = $rgb[2] / 255;
        $max = max($r, $g, $b); $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;
        if ($d == 0) {
            return [0.0, 0.0, $l];
        }
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        if ($max == $r) {
            $h = (($g - $b) / $d) + ($g < $b ? 6 : 0);
        } elseif ($max == $g) {
            $h = (($b - $r) / $d) + 2;
        } else {
            $h = (($r - $g) / $d) + 4;
        }
        return [$h * 60, $s, $l];
    }

    /** [h(0-360), s(0-1), l(0-1)] -> [r,g,b] (0-255) */
    private static function hslToRgb(array $hsl): array
    {
        list($h, $s, $l) = $hsl;
        $h = fmod(fmod($h, 360) + 360, 360) / 360;
        if ($s == 0) {
            $v = $l * 255;
            return [$v, $v, $v];
        }
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $hue = function ($t) use ($p, $q) {
            if ($t < 0) $t += 1;
            if ($t > 1) $t -= 1;
            if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
            if ($t < 1 / 2) return $q;
            if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
            return $p;
        };
        return [$hue($h + 1 / 3) * 255, $hue($h) * 255, $hue($h - 1 / 3) * 255];
    }

    /**
     * Seed hex'ten mini site paletini turetir.
     * Donus: brand, brand2, brand3, brand_dark, brand_darker, rgb ("r, g, b").
     * Tonlar, tarihsel mor rampasindaki (#5C008E -> -2/-3/-dark) aciklik
     * farklarini taklit eder.
     */
    public static function palet($seedHex): array
    {
        $rgb = self::hexToRgb($seedHex);
        $hsl = self::rgbToHsl($rgb);

        // Marka tonunu okunur bir banda cek: mini sitede marka rengi uzerine
        // BEYAZ yazi/buton var. Flutter seed'leri (pastel sari, mint, bebe mavi...)
        // cok acik olabilir; o yuzden marka aydinligini en fazla 0.46'ya indiriyoruz
        // (hue+doygunluk korunur, sadece koyulastirir; zaten koyu olani oldugu gibi birakir).
        $brandHsl = $hsl;
        $brandHsl[2] = min($hsl[2], 0.46);
        $brandL = $brandHsl[2];

        $brand = self::rgbToHex(self::hslToRgb($brandHsl));

        $tonla = function ($deltaL) use ($brandHsl) {
            $h = $brandHsl;
            $h[2] = max(0.06, min(0.96, $brandHsl[2] + $deltaL));
            return self::rgbToHex(self::hslToRgb($h));
        };
        $brandRgb = self::hexToRgb($brand);

        return [
            'brand'        => $brand,
            'brand2'       => $tonla(+0.14),
            'brand3'       => $tonla(+0.28),
            'brand_dark'   => $tonla(-0.12),
            'brand_darker' => $tonla(-0.20),
            'rgb'          => $brandRgb[0] . ', ' . $brandRgb[1] . ', ' . $brandRgb[2],
        ];
    }

    /**
     * Salon icin mini siteye enjekte edilecek :root override <style> blogunu uretir.
     * Salonun ozel temasi yoksa (tema_seed bos veya varsayilan mor) BOS string
     * doner -> salon-landing.css varsayilani gecerli kalir (geriye donuk uyum).
     */
    public static function cssOverride($salon): string
    {
        $seed = is_object($salon) ? ($salon->tema_seed ?? null) : null;
        if (empty($seed)) {
            return '';
        }
        $seed = strtoupper(trim($seed));
        if ($seed === strtoupper(self::VARSAYILAN_SEED)) {
            return ''; // varsayilanla ayni -> override gereksiz
        }

        $p = self::palet($seed);

        return "\n<style id=\"salon-tema-override\">:root{"
            . "--slp-brand:{$p['brand']};"
            . "--slp-brand-2:{$p['brand2']};"
            . "--slp-brand-3:{$p['brand3']};"
            . "--slp-brand-dark:{$p['brand_dark']};"
            . "--slp-brand-darker:{$p['brand_darker']};"
            . "--slp-brand-rgb:{$p['rgb']};"
            . "--slp-pink:{$p['brand2']};"
            . "--slp-g-main:linear-gradient(100deg,{$p['brand']} 0%,{$p['brand2']} 55%,{$p['brand3']} 100%);"
            . "--slp-g-dark:linear-gradient(135deg,{$p['brand_darker']} 0%,{$p['brand_dark']} 45%,{$p['brand']} 100%);"
            . "--slp-glow:0 12px 34px rgba({$p['rgb']},0.28);"
            . "}</style>\n";
    }
}
