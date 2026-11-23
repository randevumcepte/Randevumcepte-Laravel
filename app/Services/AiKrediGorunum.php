<?php

namespace App\Services;

/**
 * Sistem Yonetimi > AI Kredi ekranlari (genel + isletme detay) ortak gorunum yardimcilari.
 * View'da: @php extract(\App\Services\AiKrediGorunum::yardimcilar($kur)); @endphp
 * -> $usd, $tl, $birim, $sayi, $tk, $kanal, $rgba, $modelAd, $tarih, $telMaske
 */
class AiKrediGorunum
{
    /** Kanal = ai_kullanim.tur; ekranda teknik kod degil ne is yaptigi gorunur */
    const KANALLAR = [
        'wa_asistan' => ['ad' => 'WhatsApp Asistanı',       'ikon' => 'mdi-whatsapp',     'renk' => '#22c55e', 'koyu' => '#15803d', 'birim' => 'cevap',          'ne' => 'Müşterilerin WhatsApp mesajlarına AI\'nin yazdığı cevaplar'],
        'santral'    => ['ad' => 'Telefon Asistanı',        'ikon' => 'mdi-phone',        'renk' => '#3b82f6', 'koyu' => '#1d4ed8', 'birim' => 'konuşma sırası', 'ne' => 'Santralde telefonu AI açar; müşterinin her konuşma sırası 1 işlem'],
        'wa_gorsel'  => ['ad' => 'Fotoğraftan Fiyat',       'ikon' => 'mdi-camera',       'renk' => '#f59e0b', 'koyu' => '#b45309', 'birim' => 'fotoğraf',       'ne' => 'Müşterinin WhatsApp\'tan attığı fotoğrafı inceleyip fiyat çıkarma'],
        'niyet'      => ['ad' => 'Patron AI · Rapor sorusu', 'ikon' => 'mdi-chart-bar',   'renk' => '#38bdf8', 'koyu' => '#0369a1', 'birim' => 'soru',           'ne' => 'İşletme sahibinin "bu ay ciro ne?" gibi soruları'],
        'sohbet'     => ['ad' => 'Patron AI · Sohbet',      'ikon' => 'mdi-robot',        'renk' => '#a78bfa', 'koyu' => '#6d28d9', 'birim' => 'mesaj',          'ne' => 'İşletme sahibinin asistanla serbest sohbeti'],
        'karne'      => ['ad' => 'Personel Karnesi',        'ikon' => 'mdi-card-account-details', 'renk' => '#f472b6', 'koyu' => '#be185d', 'birim' => 'karne', 'ne' => 'Personel performans karnesi yorumu'],
        'yorum'      => ['ad' => 'Personel Yorumu',         'ikon' => 'mdi-comment-text', 'renk' => '#fbbf24', 'koyu' => '#b45309', 'birim' => 'yorum',          'ne' => 'Personel hakkında AI yorumu'],
        'kampanya'   => ['ad' => 'Kampanya Metni',          'ikon' => 'mdi-bullhorn',     'renk' => '#34d399', 'koyu' => '#047857', 'birim' => 'metin',          'ne' => 'AI ile yazılan kampanya / duyuru metni'],
        'pdf_egitim' => ['ad' => 'PDF Eğitim',              'ikon' => 'mdi-file-pdf-box', 'renk' => '#ef4444', 'koyu' => '#b91c1c', 'birim' => 'belge',          'ne' => 'Yüklenen PDF\'ten asistana bilgi öğretme'],
    ];

    /** Isletme avatar renkleri (id'ye gore sabit) */
    const AVATAR = ['#7B2FB8', '#db2777', '#2563eb', '#059669', '#ea580c', '#0891b2', '#9333ea', '#ca8a04'];

    public static function kanal($tur)
    {
        return self::KANALLAR[$tur] ?? ['ad' => ucfirst(str_replace('_', ' ', (string) $tur)), 'ikon' => 'mdi-auto-fix', 'renk' => '#94a3b8', 'koyu' => '#475569', 'birim' => 'işlem', 'ne' => 'Diğer AI işlemi'];
    }

    public static function avatarRenk($id)
    {
        return self::AVATAR[((int) $id) % count(self::AVATAR)];
    }

    public static function yardimcilar($kur)
    {
        $kur = $kur ?: 40;
        return [
            'usd' => function ($v) { return '$' . number_format((float) $v, ((float) $v < 1 ? 4 : 2), ',', '.'); },
            'tl'  => function ($v) use ($kur) { return number_format((float) $v * $kur, 2, ',', '.') . ' ₺'; },
            // Tek islem maliyeti: 1 ₺ altindaysa kurus olarak (daha okunur)
            'birim' => function ($v) use ($kur) {
                $t = (float) $v * $kur;
                if ($t <= 0) return '—';
                return $t < 1 ? number_format($t * 100, ($t * 100 < 1 ? 2 : 1), ',', '.') . ' kuruş' : number_format($t, 2, ',', '.') . ' ₺';
            },
            'sayi' => function ($v) { return number_format((float) $v, 0, ',', '.'); },
            // Token: milyon/bin kisaltmasi
            'tk' => function ($v) {
                $v = (float) $v;
                if ($v >= 1000000) return number_format($v / 1000000, 1, ',', '.') . ' milyon';
                if ($v >= 1000) return number_format($v / 1000, 1, ',', '.') . ' bin';
                return number_format($v, 0, ',', '.');
            },
            'kanal' => function ($tur) { return self::kanal($tur); },
            'rgba' => function ($hex, $a) {
                $h = ltrim($hex, '#');
                return 'rgba(' . hexdec(substr($h, 0, 2)) . ',' . hexdec(substr($h, 2, 2)) . ',' . hexdec(substr($h, 4, 2)) . ',' . $a . ')';
            },
            'modelAd' => function ($m) {
                $m = strtolower((string) $m);
                if (strpos($m, 'haiku') !== false) return 'Ekonomik model (Haiku)';
                if (strpos($m, 'sonnet') !== false) return 'Güçlü model (Sonnet)';
                if (strpos($m, 'opus') !== false) return 'En güçlü model (Opus)';
                return $m !== '' ? $m : '—';
            },
            'tarih' => function ($v, $bicim = 'd.m H:i') { return $v ? date($bicim, strtotime($v)) : '—'; },
            // KVKK: sistem panelinde numara maskeli (0532 *** ** 12)
            'telMaske' => function ($t) {
                $d = preg_replace('/\D/', '', (string) $t);
                if (strlen($d) < 7) return $t ? 'Gizli numara' : '—';
                $d = substr($d, -10);
                return '0' . substr($d, 0, 3) . ' *** ** ' . substr($d, -2);
            },
        ];
    }
}
