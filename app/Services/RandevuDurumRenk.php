<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Randevu DURUM renkleri (Geldi, Gelmedi, ...): isletmeye ozel ayar, yoksa sistemin sabit rengi.
 * Takvimin durum/renk kuralinin TEK yeri (web takvimi, mobil takvim API, On Gorusme Takvimi).
 *
 * - Tabloda yalniz isletmenin DEGISTIRDIGI durumlar durur; varsayilan renk yazilmaz.
 * - Kayit yok / bozuk / tablo yok => varsayilan renkler (hicbir isletme etkilenmez).
 * - Yayilim: SALONLAR listesi. Herkese acmak icin SALONLAR = null.
 */
class RandevuDurumRenk
{
    const SALONLAR = [195]; // null => tum isletmeler
    const TABLO = 'randevu_durum_renkleri';
    const SEMA = 'randevu_durum_renkleri_sema_v1';

    /** Bugune kadarki sabit takvim renkleri. 'islemde' takvimde renksizdir; yalniz ayarlanirsa renk alir. */
    const VARSAYILAN = [
        'geldi'    => '#008000',
        'gelmedi'  => '#ff0000',
        'bekliyor' => '#ffc107',
        'gelecek'  => '#EF7400',
        'iptal'    => '#000000',
    ];

    const ADLAR = [
        'geldi'    => 'Geldi',
        'gelmedi'  => 'Gelmedi',
        'bekliyor' => 'Onay Bekliyor',
        'gelecek'  => 'Gelecek',
        'islemde'  => 'İşlemde',
        'iptal'    => 'İptal',
    ];

    private static $harita = [];

    public static function acik($salonId): bool
    {
        return self::SALONLAR === null || in_array((int) $salonId, self::SALONLAR, true);
    }

    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        try {
            if (!Schema::hasTable(self::TABLO)) {
                DB::statement('CREATE TABLE IF NOT EXISTS ' . self::TABLO . ' (
                    salon_id INT UNSIGNED NOT NULL PRIMARY KEY,
                    renkler TEXT NOT NULL,
                    updated_at TIMESTAMP NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            }
            Cache::put(self::SEMA, 1, 1440); // dakika
            return true;
        } catch (\Throwable $e) {
            \Log::warning('RandevuDurumRenk sema: ' . $e->getMessage());
            return false;
        }
    }

    /** Isletmenin ayarladigi durumlar: [durum => ['bg'=>'#RRGGBB','text'=>'#RRGGBB']]; ayar yoksa []. */
    public static function harita($salonId): array
    {
        $salonId = (int) $salonId;
        if (!self::acik($salonId)) return [];
        if (isset(self::$harita[$salonId])) return self::$harita[$salonId];
        $sonuc = [];
        if (self::semaGaranti()) {
            try {
                $sonuc = self::temizle(json_decode((string) DB::table(self::TABLO)->where('salon_id', $salonId)->value('renkler'), true));
            } catch (\Throwable $e) {
                Cache::forget(self::SEMA); // tablo yoksa sonraki istekte olusur; takvim dusmesin
                \Log::warning('RandevuDurumRenk oku: ' . $e->getMessage());
            }
        }
        return self::$harita[$salonId] = $sonuc;
    }

    /** Gecerli zemin renkleri: varsayilanlar + isletme ayari. 'islemde' yalniz ayarliysa bulunur. */
    public static function zeminler($salonId): array
    {
        return array_merge(self::VARSAYILAN, array_map(function ($r) { return $r['bg']; }, self::harita($salonId)));
    }

    /** Ayarli durumlarin yazi renkleri; ayarsiz durumda anahtar yoktur (mevcut yazi kurali surer). */
    public static function yazilar($salonId): array
    {
        return array_map(function ($r) { return $r['text']; }, self::harita($salonId));
    }

    /**
     * Randevu kaleminin takvimdeki durumu; null => durum rengi yok (kaynak rengi kullanilir).
     * @param array $zeminler zeminler() sonucu ('islemde' anahtari varsa o durum da ayrisir)
     */
    public static function durum($rh, array $zeminler)
    {
        $r = $rh->randevu;
        if (($r && $r->randevuya_geldi === 1) || $rh->seansa_geldi === 1) return 'geldi';
        if (($r && $r->randevuya_geldi === 0) || $rh->seansa_geldi === 0) return 'gelmedi';
        if ($r && $r->durum === 0) return 'bekliyor';
        if ($r && isset($zeminler['islemde']) && (int) $r->durum === 1 && (int) $r->randevuya_islemde === 1) return 'islemde';
        if ($r && $r->randevuya_gelecek === 1 && $r->randevuya_geldi === null) return 'gelecek';
        return $rh->iptal ? 'iptal' : null;
    }

    /** @param mixed $veri [durum => ['bg','text']] — ekrandan gelen ham deger */
    public static function kaydet($salonId, $veri): bool
    {
        $salonId = (int) $salonId;
        if (!self::acik($salonId) || !self::semaGaranti()) return false;
        $renkler = self::temizle($veri);
        try {
            if ($renkler) {
                DB::table(self::TABLO)->updateOrInsert(['salon_id' => $salonId],
                    ['renkler' => json_encode($renkler), 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                DB::table(self::TABLO)->where('salon_id', $salonId)->delete(); // varsayilana donus
            }
        } catch (\Throwable $e) {
            Cache::forget(self::SEMA);
            \Log::warning('RandevuDurumRenk kaydet: ' . $e->getMessage());
            return false;
        }
        self::$harita[$salonId] = $renkler;
        return true;
    }

    /** Takvim degisiklik imzasina eklenir: renk degisince acik takvimler tazelenir. Ayar yoksa ''. */
    public static function imza($salonId): string
    {
        $h = self::harita($salonId);
        return $h ? '|dr' . substr(md5(json_encode($h)), 0, 8) : '';
    }

    /** Zemine gore okunur yazi rengi (takvim renk ayarindaki kuralla ayni). */
    public static function otoYazi(string $bg): string
    {
        $l = (0.299 * hexdec(substr($bg, 1, 2)) + 0.587 * hexdec(substr($bg, 3, 2)) + 0.114 * hexdec(substr($bg, 5, 2))) / 255;
        return $l > 0.6 ? '#000000' : '#FFFFFF';
    }

    private static function temizle($veri): array
    {
        $hex = function ($v) { return is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v) === 1; };
        $sonuc = [];
        foreach (array_keys(self::ADLAR) as $d) {
            $r = is_array($veri) ? ($veri[$d] ?? null) : null;
            if (!is_array($r) || !$hex($r['bg'] ?? null)) continue;
            $bg = strtoupper($r['bg']);
            $text = $hex($r['text'] ?? null) ? strtoupper($r['text']) : self::otoYazi($bg);
            // Varsayilan zemin + beyaz yazi = ayarsiz ile ayni: yazma
            if (isset(self::VARSAYILAN[$d]) && $bg === strtoupper(self::VARSAYILAN[$d]) && $text === '#FFFFFF') continue;
            $sonuc[$d] = ['bg' => $bg, 'text' => $text];
        }
        return $sonuc;
    }
}
