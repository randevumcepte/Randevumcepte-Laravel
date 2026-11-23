<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Adil randevu dagitimi (2026-11-23). Musteri uzman belirtmediginde ayni saatte bos olan personeller arasindan
 * TAKVIM SIRASINA DEGIL yuke gore secilir (2026-11-23: gunluk yuk = dolu dk / o gun calisacagi dk):
 *   1) o gun calisma suresine oranla en az dolu olan  2) bu hafta en az dakika  3) en uzun suredir randevu almamis  4) rastgele
 * Kullanim: WhatsApp + santral asistani (RandevuAsistan), online randevuda "fark etmez" (CustomerController).
 * Musteri bir uzman sectiyse dokunulmaz. "Otomatik atamaya dahil etme" isaretli personel aday olmaz.
 */
class AdilAtama
{
    const TABLO = 'personel_atama_ayar';
    const SEMA = 'personel_atama_ayar_sema_v1';

    public static function semaGaranti()
    {
        try {
            if (Cache::get(self::SEMA)) return;
            if (!Schema::hasTable(self::TABLO)) {
                Schema::create(self::TABLO, function ($t) {
                    $t->unsignedInteger('personel_id')->primary();
                    $t->unsignedInteger('salon_id')->index();
                    $t->boolean('oto_haric')->default(false);
                    $t->timestamps();
                });
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[AdilAtama] sema hatasi', ['err' => $e->getMessage()]);
        }
    }

    /** Otomatik atamadan haric tutulan personel id'leri. */
    public static function haricler($salonId)
    {
        try {
            if (!Schema::hasTable(self::TABLO)) return [];
            return DB::table(self::TABLO)->where('salon_id', $salonId)->where('oto_haric', 1)
                ->pluck('personel_id')->map(function ($v) { return (int) $v; })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function haricKaydet($salonId, $yapan, $personelId, $haric, $req)
    {
        self::semaGaranti();
        $p = \App\Personeller::where('id', $personelId)->where('salon_id', $salonId)->first(['id', 'personel_adi']);
        if (!$p) return 'Personel bulunamadı.';
        $simdi = date('Y-m-d H:i:s');
        DB::table(self::TABLO)->updateOrInsert(['personel_id' => $p->id],
            ['salon_id' => $salonId, 'oto_haric' => $haric ? 1 : 0, 'updated_at' => $simdi, 'created_at' => $simdi]);
        $aciklama = $yapan->personel_adi . ', ' . $p->personel_adi . ' için otomatik randevu atamasını ' . ($haric ? 'kapattı' : 'açtı');
        try {
            $web = false;
            try { $web = \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->check(); } catch (\Throwable $e) {}
            if (!$web && $req) \App\SalonYonetim\Audit::logApi($salonId, $req, 'oto_atama_ayar', 'personel', $p->id, $p->personel_adi, $aciklama);
            else \App\SalonYonetim\Audit::log($salonId, 'oto_atama_ayar', 'personel', $p->id, $p->personel_adi, $aciklama);
        } catch (\Throwable $e) {}
        return true;
    }

    /** Dakika ifadesi: sure_dk yoksa saat araligindan, o da yoksa 30. */
    private static function dakikaSql()
    {
        return 'COALESCE(NULLIF(rh.sure_dk, 0), (TIME_TO_SEC(rh.saat_bitis) - TIME_TO_SEC(rh.saat)) / 60, 30)';
    }

    /** Gercek randevular (saat kapama ve iptal/red haric). */
    private static function randevuSorgu($salonId, array $pids)
    {
        return DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', $salonId)->whereIn('rh.personel_id', $pids)
            ->where('r.user_id', '!=', PersonelProfil::KAPAMA_USER)->where('rh.hizmet_id', '!=', PersonelProfil::KAPAMA_HIZMET)
            ->where(function ($w) { $w->whereNull('r.durum')->orWhereNotIn('r.durum', [2, 3]); });
    }

    /** Adaylari adil siraya dizer (ilk = randevuyu almasi gereken). Hata olursa gelen sirayi karistirarak doner. */
    public static function sirala($salonId, array $pids, $tarih)
    {
        $pids = array_values(array_unique(array_map('intval', array_filter($pids))));
        if (count($pids) <= 1) return $pids;
        try {
            $t = strtotime($tarih) ?: time();
            $haftaBas = date('Y-m-d', strtotime('monday this week', $t));
            $haftaBit = date('Y-m-d', strtotime('sunday this week', $t));
            $dk = self::dakikaSql();
            $gun = self::randevuSorgu($salonId, $pids)->where('r.tarih', date('Y-m-d', $t))
                ->groupBy('rh.personel_id')->selectRaw("rh.personel_id p, SUM($dk) d")->pluck('d', 'p');
            $hafta = self::randevuSorgu($salonId, $pids)->whereBetween('r.tarih', [$haftaBas, $haftaBit])
                ->groupBy('rh.personel_id')->selectRaw("rh.personel_id p, SUM($dk) d")->pluck('d', 'p');
            $son = self::randevuSorgu($salonId, $pids)->groupBy('rh.personel_id')
                ->selectRaw('rh.personel_id p, MAX(r.created_at) s')->pluck('s', 'p');
            // Gunluk yuk = dolu dakika / o gun CALISACAGI dakika (yarim gun calisan tam gunle ayni sirada is almasin)
            $calisma = self::gunlukCalismaDakika($salonId, $pids, date('Y-m-d', $t));
            $anahtar = [];
            foreach ($pids as $p) {
                $dolu = (float) ($gun[$p] ?? 0);
                $oran = ($calisma[$p] ?? 0) > 0 ? $dolu / $calisma[$p] : ($dolu > 0 ? 1.0 : 0.0);
                $anahtar[$p] = [round($oran, 4), (float) ($hafta[$p] ?? 0), (string) ($son[$p] ?? ''), mt_rand()];
            }
            usort($pids, function ($a, $b) use ($anahtar) { return $anahtar[$a] <=> $anahtar[$b]; });
            return $pids;
        } catch (\Throwable $e) {
            Log::warning('[AdilAtama] sirala', ['err' => $e->getMessage()]);
            shuffle($pids);
            return $pids;
        }
    }

    /**
     * Personelin o gun calisacagi dakika: kendi calisma saati (gun acik ama saat girilmemisse / satir yoksa salonun
     * saati) eksi o gunku saat kapama / mola / izin bloklari. pid => dakika (calismiyorsa 0).
     */
    public static function gunlukCalismaDakika($salonId, array $pids, $tarih)
    {
        $gunNo = (int) date('N', strtotime($tarih));
        $dk = function ($s) {
            if (!$s || !preg_match('/^(\d{1,2}):(\d{2})/', (string) $s, $m)) return null;
            return (int) $m[1] * 60 + (int) $m[2];
        };
        $salon = DB::table('salon_calisma_saatleri')->where('salon_id', $salonId)->where('haftanin_gunu', $gunNo)->first();
        $salonDk = ($salon && (int) $salon->calisiyor === 1 && $dk($salon->bitis_saati) > $dk($salon->baslangic_saati))
            ? $dk($salon->bitis_saati) - $dk($salon->baslangic_saati) : 0;
        $kendi = DB::table('personel_calisma_saatleri')->whereIn('personel_id', $pids)->where('haftanin_gunu', $gunNo)->get()->keyBy('personel_id');
        $kapama = DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', $salonId)->where('r.tarih', $tarih)->whereIn('rh.personel_id', $pids)
            ->where('r.user_id', PersonelProfil::KAPAMA_USER)
            ->where(function ($w) { $w->whereNull('r.durum')->orWhere('r.durum', '<', 2); })
            ->groupBy('rh.personel_id')->selectRaw('rh.personel_id p, SUM((TIME_TO_SEC(rh.saat_bitis) - TIME_TO_SEC(rh.saat)) / 60) d')->pluck('d', 'p');
        $sonuc = [];
        foreach ($pids as $p) {
            $r = $kendi->get($p);
            if ($r) {
                $bas = $dk($r->baslangic_saati); $bit = $dk($r->bitis_saati);
                $top = (int) $r->calisiyor !== 1 ? 0 : (($bas !== null && $bit !== null && $bit > $bas) ? $bit - $bas : $salonDk);
            } else {
                $top = $salonDk;
            }
            $sonuc[$p] = max(0, $top - (int) round((float) ($kapama[$p] ?? 0)));
        }
        return $sonuc;
    }

    /** Verilen aralikta personelin baska randevusu (kapama dahil) var mi. */
    public static function bosMu($salonId, $pid, $tarih, $bas, $bit)
    {
        return !DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', $salonId)->where('rh.personel_id', $pid)->where('r.tarih', $tarih)
            ->where(function ($w) { $w->whereNull('r.durum')->orWhere('r.durum', '<', 2); })
            ->where('rh.saat', '<', $bit)->where('rh.saat_bitis', '>', $bas)->exists();
    }

    /**
     * Online "fark etmez": hizmeti veren, aktif, takvimde gorunen, haric olmayan ve o aralikta bos personeller
     * arasindan adil secim. Kimse bos degilse null (cagiran eski davranisa doner).
     */
    public static function bosPersonelSec($salonId, $hizmetId, $tarih, $bas, $bit)
    {
        try {
            $veren = \App\PersonelHizmetler::where('hizmet_id', $hizmetId)->pluck('personel_id')->map(function ($v) { return (int) $v; })->all();
            $q = \App\Personeller::where('salon_id', $salonId)->where('aktif', 1)->where('takvimde_gorunsun', 1)
                ->where(function ($w) { $w->whereNull('arsivli')->orWhere('arsivli', '!=', 1); });
            if ($veren) $q->whereIn('id', $veren);
            $haric = self::haricler($salonId);
            $bos = [];
            foreach ($q->pluck('id') as $pid) {
                $pid = (int) $pid;
                if (in_array($pid, $haric, true)) continue;
                if (self::bosMu($salonId, $pid, $tarih, $bas, $bit)) $bos[] = $pid;
            }
            if (!$bos) return null;
            return self::sirala($salonId, $bos, $tarih)[0];
        } catch (\Throwable $e) {
            Log::warning('[AdilAtama] bosPersonelSec', ['err' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Musterinin son geldigi uzman: once ayni hizmette "geldi" isaretli son randevu, yoksa herhangi "geldi" son randevu.
     * Personel aktif ve haric degilse id, yoksa null.
     */
    public static function sonUzman($salonId, $userId, $hizmetId)
    {
        if (!$userId) return null;
        try {
            $taban = function () use ($salonId, $userId) {
                return DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
                    ->join('salon_personelleri as sp', 'sp.id', '=', 'rh.personel_id')
                    ->where('r.salon_id', $salonId)->where('r.user_id', $userId)->where('r.randevuya_geldi', 1)
                    ->where('rh.hizmet_id', '!=', PersonelProfil::KAPAMA_HIZMET)
                    ->where('sp.aktif', 1)->where(function ($w) { $w->whereNull('sp.arsivli')->orWhere('sp.arsivli', '!=', 1); })
                    ->orderByDesc('r.tarih')->orderByDesc('rh.saat');
            };
            $pid = $hizmetId ? $taban()->where('rh.hizmet_id', $hizmetId)->value('rh.personel_id') : null;
            if (!$pid) $pid = $taban()->value('rh.personel_id');
            $pid = (int) $pid;
            if (!$pid || in_array($pid, self::haricler($salonId), true)) return null;
            return $pid;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Rapor: donemde asistanin (WhatsApp/santral) actigi randevular ve tum randevular, personel basina. */
    public static function rapor($salonId, $bas, $bit)
    {
        self::semaGaranti();
        $dk = self::dakikaSql();
        $tumu = DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', $salonId)->whereBetween('r.tarih', [$bas, $bit])
            ->where('r.user_id', '!=', PersonelProfil::KAPAMA_USER)->where('rh.hizmet_id', '!=', PersonelProfil::KAPAMA_HIZMET)
            ->where(function ($w) { $w->whereNull('r.durum')->orWhereNotIn('r.durum', [2, 3]); })
            ->whereNotNull('rh.personel_id')->groupBy('rh.personel_id')
            ->selectRaw("rh.personel_id p, COUNT(DISTINCT r.id) adet, SUM($dk) dakika, COUNT(DISTINCT CASE WHEN r.easistan = 1 THEN r.id END) asistan_adet, SUM(CASE WHEN r.easistan = 1 THEN $dk ELSE 0 END) asistan_dakika")
            ->get()->keyBy('p');
        $haric = self::haricler($salonId);
        return \App\Personeller::where('salon_id', $salonId)->where('aktif', 1)
            ->where(function ($w) { $w->whereNull('arsivli')->orWhere('arsivli', '!=', 1); })
            ->orderBy('takvim_sirasi')->orderBy('id')->get(['id', 'personel_adi', 'takvimde_gorunsun'])
            ->map(function ($p) use ($tumu, $haric) {
                $r = $tumu->get($p->id);
                return [
                    'id' => (int) $p->id, 'ad' => (string) $p->personel_adi, 'takvimde' => (bool) $p->takvimde_gorunsun,
                    'oto_haric' => in_array((int) $p->id, $haric, true),
                    'adet' => $r ? (int) $r->adet : 0, 'dakika' => $r ? (int) round($r->dakika) : 0,
                    'asistan_adet' => $r ? (int) $r->asistan_adet : 0, 'asistan_dakika' => $r ? (int) round($r->asistan_dakika) : 0,
                ];
            })->all();
    }
}
