<?php

namespace App\Services;

use App\Randevular;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * On gorusmenin TARIHI degisince eski gunu kaydeder; On Gorusme Takvimi eski gunde "Ertelendi" gosterir.
 * Kaynak: Randevular model olaylari (web formu, mobil, surukle-birak hepsi ayni yerden gecer):
 *   - tarih yerinde guncellenir (updating)
 *   - eski randevu silinip yenisi olusturulur (deleting -> created)
 *   - yenisi olusturulup eskisi sonra silinir (created; eskisi hala duruyor)
 * Saat/personel degisikligi erteleme SAYILMAZ. Hata asil kaydi engellemez.
 */
class OnGorusmeErteleme
{
    const TABLO = 'ongorusme_ertelemeleri';
    const SEMA = 'ongorusme_ertelemeleri_sema_v1';

    /** @var array on_gorusme_id => bu istekte silinen randevunun bilgisi */
    private static $silinen = [];

    /** Tablo yoksa olusturur (idempotent). DDL oldugu icin model olaylarindan CAGRILMAZ (acik transaction'i bitirir). */
    public static function semaGaranti(): bool
    {
        try {
            if (self::hazir()) return true;
            DB::statement('CREATE TABLE IF NOT EXISTS '.self::TABLO.' (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                salon_id INT UNSIGNED NOT NULL,
                on_gorusme_id INT UNSIGNED NOT NULL,
                personel_id INT UNSIGNED NULL,
                eski_tarih DATE NOT NULL,
                eski_saat TIME NULL,
                sure_dk INT NOT NULL DEFAULT 60,
                yeni_tarih DATE NOT NULL,
                yeni_saat TIME NULL,
                yapan_yetkili_id INT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                KEY ong_ert_salon_tarih (salon_id, eski_tarih),
                KEY ong_ert_gorusme (on_gorusme_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            return self::hazir();
        } catch (\Throwable $e) {
            \Log::warning('OnGorusmeErteleme sema: '.$e->getMessage());
            return false;
        }
    }

    private static function hazir(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        if (!Schema::hasTable(self::TABLO)) return false;
        Cache::put(self::SEMA, 1, 1440); // dakika
        return true;
    }

    public static function randevuSiliniyor(Randevular $r)
    {
        if (!$r->on_gorusme_id) return;
        self::guvenli(function () use ($r) {
            self::$silinen[(int) $r->on_gorusme_id] = self::bilgi($r, $r->tarih, $r->saat);
        });
    }

    public static function randevuOlustu(Randevular $r)
    {
        if (!$r->on_gorusme_id) return;
        self::guvenli(function () use ($r) {
            $id = (int) $r->on_gorusme_id;
            $eski = self::$silinen[$id] ?? null;
            unset(self::$silinen[$id]);
            if (!$eski) {
                $onceki = Randevular::where('on_gorusme_id', $id)->where('id', '!=', $r->id)->where('durum', '<', 2)->orderBy('id', 'desc')->first();
                if ($onceki) $eski = self::bilgi($onceki, $onceki->tarih, $onceki->saat);
            }
            if ($eski) self::kaydet($r, $eski);
        });
    }

    public static function randevuGuncelleniyor(Randevular $r)
    {
        if (!$r->on_gorusme_id || !$r->isDirty('tarih')) return;
        self::guvenli(function () use ($r) {
            if ($r->getOriginal('tarih')) self::kaydet($r, self::bilgi($r, $r->getOriginal('tarih'), $r->getOriginal('saat')));
        });
    }

    /** [bas, bit] arasindaki eski gunler: gorusme + eski gun basina SON erteleme. */
    public static function aralik($salonId, $bas, $bit)
    {
        try {
            if (!self::hazir()) return collect();
            return DB::table(self::TABLO)->where('salon_id', (int) $salonId)->whereBetween('eski_tarih', [$bas, $bit])
                ->orderBy('id', 'asc')->get()
                ->keyBy(function ($e) { return $e->on_gorusme_id.'|'.$e->eski_tarih; })->values();
        } catch (\Throwable $e) {
            Cache::forget(self::SEMA);
            \Log::warning('OnGorusmeErteleme oku: '.$e->getMessage());
            return collect();
        }
    }

    private static function bilgi(Randevular $r, $tarih, $saat): array
    {
        // Kalemler randevudan ONCE silinmis olabilir; yuklu iliski varsa onu kullanir.
        $kalem = $r->hizmetler->sortByDesc('sure_dk')->first();
        return [
            'tarih'       => date('Y-m-d', strtotime($tarih)),
            'saat'        => $saat ?: null,
            'sure_dk'     => $kalem && (int) $kalem->sure_dk > 0 ? (int) $kalem->sure_dk : 60,
            'personel_id' => $kalem && $kalem->personel_id ? (int) $kalem->personel_id
                : DB::table('on_gorusmeler')->where('id', $r->on_gorusme_id)->value('personel_id'),
        ];
    }

    private static function kaydet(Randevular $r, array $eski)
    {
        $yeniTarih = date('Y-m-d', strtotime($r->tarih));
        if ($eski['tarih'] === $yeniTarih || !self::hazir()) return;
        DB::table(self::TABLO)->insert([
            'salon_id'         => (int) $r->salon_id,
            'on_gorusme_id'    => (int) $r->on_gorusme_id,
            'personel_id'      => $eski['personel_id'] ?: null,
            'eski_tarih'       => $eski['tarih'],
            'eski_saat'        => $eski['saat'],
            'sure_dk'          => $eski['sure_dk'],
            'yeni_tarih'       => $yeniTarih,
            'yeni_saat'        => $r->saat ?: null,
            'yapan_yetkili_id' => Auth::guard('isletmeyonetim')->check() ? Auth::guard('isletmeyonetim')->id() : null,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    private static function guvenli(callable $is)
    {
        try {
            $is();
        } catch (\Throwable $e) {
            \Log::warning('OnGorusmeErteleme: '.$e->getMessage());
        }
    }
}
