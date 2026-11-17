<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Santral yapay zeka asistani — salon bazli AYLIK dakika paketi + kullanim/maliyet hesabi.
 *
 * Kullanim kaynagi: santral_oturumlari (her AI gorusmesi bir satir).
 *   yon      : gelen | giden (giden = hatirlatma aramasi)
 *   sure_sn  : AI'nin hatta oldugu sure (baslat -> bitir; aktarmada operator suresi DAHIL DEGIL)
 *   *_token  : o gorusmedeki Claude token kullanimi, maliyet_usd = Claude maliyeti
 *   tts_karakter : seslendirilen karakter (Wavenet maliyet tahmini icin)
 *
 * Donem = takvim ayi. Paket TANIMSIZ salonda asistan CALISMAZ (gelen cagri dogrudan operator_kanali'na,
 * hatirlatmalar klasik IVR). asim_davranis:
 *   devam  -> paket bitse de asistan calisir, asan dakika asim ucretiyle faturalanir
 *   durdur -> paket bitince gelen cagri operatore aktarilir, hatirlatmalar klasik tuslamali IVR ile yapilir
 */
class SantralPaket
{
    const TABLO = 'santral_asistan_paketleri';

    /** Hazir paket onerileri (modal'da tek tikla doldurma). */
    const HAZIR = [
        // Chirp3-HD seslendirme ile (maliyet ~1 TL/dk, STT AI konusurken kapali): 2026-11-17 isletme karari
        ['ad' => 'Başlangıç',   'dk' => 500,  'ucret' => 1590, 'asim' => 3.5],
        ['ad' => 'Standart',    'dk' => 1000, 'ucret' => 2790, 'asim' => 3.5],
        ['ad' => 'Profesyonel', 'dk' => 2500, 'ucret' => 5990, 'asim' => 3.5],
    ];

    // ------------------------------------------------------------------ sema
    /** Paket tablosu + santral_oturumlari ek kolonlari (runtime; sunucuda migrate gerekmez). */
    public static function semaGaranti(): void
    {
        if (Cache::get('santral_paket_sema_v1')) return;
        try {
            if (!Schema::hasTable(self::TABLO)) {
                Schema::create(self::TABLO, function ($t) {
                    $t->bigIncrements('id'); // Laravel 5.6
                    $t->unsignedBigInteger('salon_id')->unique();
                    $t->string('paket_adi', 60)->nullable();
                    $t->unsignedInteger('dahil_dakika')->default(0);
                    $t->decimal('aylik_ucret', 10, 2)->default(0);     // TL
                    $t->decimal('asim_dk_ucret', 8, 2)->default(0);    // TL / dk
                    $t->string('asim_davranis', 10)->default('devam'); // devam | durdur
                    $t->text('notlar')->nullable();
                    $t->string('guncelleyen', 120)->nullable();
                    $t->timestamps();
                });
            }
            if (!Schema::hasTable('santral_oturumlari')) return; // kopru ilk cagride olusturur; flag set ETME
            $eksik = [];
            foreach (['yon', 'sure_sn', 'bitis_at', 'girdi_token', 'cikti_token', 'cache_okuma_token', 'cache_yazma_token', 'tts_karakter', 'maliyet_usd'] as $k) {
                if (!Schema::hasColumn('santral_oturumlari', $k)) $eksik[] = $k;
            }
            if ($eksik) {
                Schema::table('santral_oturumlari', function ($t) use ($eksik) {
                    if (in_array('yon', $eksik)) $t->string('yon', 6)->nullable();
                    if (in_array('sure_sn', $eksik)) $t->unsignedInteger('sure_sn')->nullable();
                    if (in_array('bitis_at', $eksik)) $t->timestamp('bitis_at')->nullable();
                    foreach (['girdi_token', 'cikti_token', 'cache_okuma_token', 'cache_yazma_token', 'tts_karakter'] as $k) {
                        if (in_array($k, $eksik)) $t->unsignedInteger($k)->default(0);
                    }
                    if (in_array('maliyet_usd', $eksik)) $t->decimal('maliyet_usd', 12, 6)->default(0);
                });
                // Eski kayitlar: yon'u gecmisteki hatirlatma isaretinden cikar
                if (in_array('yon', $eksik)) {
                    DB::table('santral_oturumlari')->whereNull('yon')->where('gecmis', 'like', '%"hatirlatma"%')->update(['yon' => 'giden']);
                    DB::table('santral_oturumlari')->whereNull('yon')->update(['yon' => 'gelen']);
                }
                try {
                    Schema::table('santral_oturumlari', function ($t) {
                        $t->index(['sube_id', 'created_at'], 'santral_ot_sube_tarih_idx');
                    });
                } catch (\Throwable $e) { /* indeks zaten var */ }
            }
            Cache::forever('santral_paket_sema_v1', 1);
        } catch (\Throwable $e) {
            Log::warning('[SANTRAL-PAKET] sema hatasi: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ maliyet
    public static function claudeMaliyetUsd(array $k): float
    {
        $gi = (float) config('services.santral_ai.girdi_usd_m', 0.10);
        $ci = (float) config('services.santral_ai.cikti_usd_m', 0.50);
        return ((int) ($k['girdi'] ?? 0) * $gi
              + (int) ($k['cache_okuma'] ?? 0) * $gi * 0.1
              + (int) ($k['cache_yazma'] ?? 0) * $gi * 1.25
              + (int) ($k['cikti'] ?? 0) * $ci) / 1000000;
    }

    /** Bir konusma turunun kullanimini oturuma ekle (+ AI Kredi paneli icin ai_kullanim'a logla). Hata yutar. */
    public static function turKaydet(int $oturumId, int $salonId, array $kullanim, int $ttsKarakter): void
    {
        try {
            self::semaGaranti();
            $usd = self::claudeMaliyetUsd($kullanim);
            DB::table('santral_oturumlari')->where('id', $oturumId)->update([
                'girdi_token'       => DB::raw('girdi_token + ' . (int) $kullanim['girdi']),
                'cikti_token'       => DB::raw('cikti_token + ' . (int) $kullanim['cikti']),
                'cache_okuma_token' => DB::raw('cache_okuma_token + ' . (int) $kullanim['cache_okuma']),
                'cache_yazma_token' => DB::raw('cache_yazma_token + ' . (int) $kullanim['cache_yazma']),
                'tts_karakter'      => DB::raw('tts_karakter + ' . max(0, $ttsKarakter)),
                'maliyet_usd'       => DB::raw('maliyet_usd + ' . sprintf('%.6F', $usd)),
            ]);
            if (($kullanim['cagri'] ?? 0) > 0) {
                AiKullanimLog::yaz($salonId, 'santral',
                    (int) $kullanim['girdi'] + (int) $kullanim['cache_okuma'] + (int) $kullanim['cache_yazma'],
                    (int) $kullanim['cikti'], config('services.anthropic.santral_model'), false, true, $usd);
            }
        } catch (\Throwable $e) {
            Log::warning('[SANTRAL-PAKET] tur kaydi hatasi: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ paket / kullanim
    public static function paket(int $salonId)
    {
        self::semaGaranti();
        try { return DB::table(self::TABLO)->where('salon_id', $salonId)->first(); }
        catch (\Throwable $e) { return null; }
    }

    /** 'Y-m' -> [bas, bit) takvim ayi. */
    public static function donem(?string $ay = null): array
    {
        $ay = ($ay && preg_match('/^\d{4}-\d{2}$/', $ay)) ? $ay : date('Y-m');
        $bas = $ay . '-01 00:00:00';
        $bit = date('Y-m-d 00:00:00', strtotime($bas . ' +1 month'));
        return [$ay, $bas, $bit];
    }

    /** Salon(lar) icin donem kullanimi. $salonIdler null -> donemde kaydi olan tum salonlar. [salon_id => satir] */
    public static function kullanimlar(string $bas, string $bit, ?array $salonIdler = null): array
    {
        self::semaGaranti();
        if (!Schema::hasTable('santral_oturumlari')) return [];
        // Kapanmamis (kopru coktu) oturumda sure: son guncellemeye kadar, en fazla 30 dk
        $sure = 'COALESCE(sure_sn, LEAST(TIMESTAMPDIFF(SECOND, created_at, COALESCE(updated_at, created_at)), 1800))';
        $q = DB::table('santral_oturumlari')
            ->where('created_at', '>=', $bas)->where('created_at', '<', $bit)
            ->select('sube_id',
                DB::raw("SUM(CASE WHEN COALESCE(yon,'gelen')='gelen' THEN 1 ELSE 0 END) as gelen_adet"),
                DB::raw("SUM(CASE WHEN yon='giden' THEN 1 ELSE 0 END) as giden_adet"),
                DB::raw("SUM(CASE WHEN COALESCE(yon,'gelen')='gelen' THEN $sure ELSE 0 END) as gelen_sn"),
                DB::raw("SUM(CASE WHEN yon='giden' THEN $sure ELSE 0 END) as giden_sn"),
                DB::raw('SUM(girdi_token) as girdi'), DB::raw('SUM(cikti_token) as cikti'),
                DB::raw('SUM(cache_okuma_token) as cache_okuma'), DB::raw('SUM(cache_yazma_token) as cache_yazma'),
                DB::raw('SUM(tts_karakter) as tts'), DB::raw('SUM(maliyet_usd) as claude_usd'))
            ->groupBy('sube_id');
        if ($salonIdler !== null) $q->whereIn('sube_id', $salonIdler ?: [0]);
        $out = [];
        foreach ($q->get() as $r) $out[(int) $r->sube_id] = $r;
        return $out;
    }

    /** Paket + kullanim -> panelde gosterilecek hesap (dakika, maliyet, fatura). */
    public static function hesapla($paket, $k, float $kur): array
    {
        $gelenSn = (int) ($k->gelen_sn ?? 0);
        $gidenSn = (int) ($k->giden_sn ?? 0);
        $toplamDk = ($gelenSn + $gidenSn) / 60;
        $dahil = $paket ? (int) $paket->dahil_dakika : 0;
        $kalan = $dahil - $toplamDk;
        $asimDk = $paket ? max(0, $toplamDk - $dahil) : 0;
        $asimFaturaDk = (int) ceil($asimDk - 1e-9); // asim tam dakikaya yuvarlanir

        $claudeUsd = (float) ($k->claude_usd ?? 0);
        $sttUsd = $toplamDk * (float) config('services.santral_ai.stt_usd_dk', 0.016) * (float) config('services.santral_ai.stt_oran', 0.52);
        $ttsUsd = (int) ($k->tts ?? 0) / 1000000 * (float) config('services.santral_ai.tts_usd_m', 30.0);
        $maliyetTl = ($claudeUsd + $sttUsd + $ttsUsd) * $kur;

        $fatura = $paket ? (float) $paket->aylik_ucret + $asimFaturaDk * (float) $paket->asim_dk_ucret : 0.0;

        return [
            'gelen_adet' => (int) ($k->gelen_adet ?? 0),
            'giden_adet' => (int) ($k->giden_adet ?? 0),
            'gelen_dk'   => round($gelenSn / 60, 1),
            'giden_dk'   => round($gidenSn / 60, 1),
            'toplam_dk'  => round($toplamDk, 1),
            'dahil_dk'   => $dahil,
            'kalan_dk'   => round($kalan, 1),
            'asim_dk'    => $asimFaturaDk,
            'yuzde'      => $dahil > 0 ? (int) min(100, round($toplamDk / $dahil * 100)) : ($toplamDk > 0 && $paket ? 100 : 0),
            'girdi_token'=> (int) ($k->girdi ?? 0) + (int) ($k->cache_okuma ?? 0) + (int) ($k->cache_yazma ?? 0),
            'cikti_token'=> (int) ($k->cikti ?? 0),
            'tts_karakter' => (int) ($k->tts ?? 0),
            'claude_tl'  => round($claudeUsd * $kur, 2),
            'stt_tl'     => round($sttUsd * $kur, 2),
            'tts_tl'     => round($ttsUsd * $kur, 2),
            'maliyet_tl' => round($maliyetTl, 2),
            'fatura_tl'  => round($fatura, 2),
            'kar_tl'     => round($fatura - $maliyetTl, 2),
            'kapali'     => $paket && $paket->asim_davranis === 'durdur' && $kalan <= 0,
        ];
    }

    public static function kur(): float
    {
        $a = AiKullanimLog::ayar();
        return ((float) ($a['kur'] ?? 0) > 0) ? (float) $a['kur'] : 40.0;
    }

    /**
     * Asistan bu salonda neden calismiyor? null = calisir | 'paket_yok' | 'paket_bitti' (asimda durdur).
     * (gelen: operatore aktar, giden: klasik IVR). Her cagride calisir -> 60 sn onbellek; panel kaydinda silinir.
     */
    public static function kapaliNedeni(int $salonId): ?string
    {
        try {
            $n = Cache::remember('santral_paket_kapali:' . $salonId, 1, function () use ($salonId) {
                $p = self::paket($salonId);
                if (!$p) return 'paket_yok';
                if ($p->asim_davranis !== 'durdur') return '-';
                [, $bas, $bit] = self::donem();
                $k = self::kullanimlar($bas, $bit, [$salonId])[$salonId] ?? null;
                return self::hesapla($p, $k, self::kur())['kapali'] ? 'paket_bitti' : '-';
            });
            return ($n && $n !== '-') ? $n : null;
        } catch (\Throwable $e) {
            Log::warning('[SANTRAL-PAKET] durum hesaplanamadi: ' . $e->getMessage());
            return null; // hesap/DB hatasinda asistani KAPATMA (tanimli paketli salonlar magdur olmasin)
        }
    }

    public static function asistanKapali(int $salonId): bool
    {
        return self::kapaliNedeni($salonId) !== null;
    }

    public static function onbellekSil(int $salonId): void
    {
        Cache::forget('santral_paket_kapali:' . $salonId);
    }
}
