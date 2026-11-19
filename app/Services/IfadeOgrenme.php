<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Ifade kutuphanesinin ogrenme dongusu + katman sayaci.
 *
 * Ogrenme (ek AI cagrisi YOK): kutuphane + kelime eslestirme bir ifadeyi bulamazsa asistan modeli salonun listesinden
 * anlamca secer. Musteri o hizmetle randevuyu GERCEKTEN olusturursa secim dogrulanmis sayilir:
 *   - o salon icin hemen wa_hizmet_esanlam'a yazilir (bir dahaki sefere bedava),
 *   - ifade_ogrenme'ye kavramiyla kaydedilir; ayni ifade 3 farkli salonda ayni kavrama gittiyse TUM salonlarda
 *     kutuphaneye eklenir (IfadeKutuphanesi::ekIfadeler). Kutuphanenin zaten bildigi ifade eklenmez.
 * Sayac: ifade_kayit (salon, kanal, katman, gun) -> hangi katman ne kadar cozuyor (sistem yoneticisi panelinde).
 */
class IfadeOgrenme
{
    const SEMA = 'ifade_ogrenme_sema_v1';
    const ORTAK_ESIK = 3;

    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        try {
            if (!Schema::hasTable('ifade_ogrenme')) {
                Schema::create('ifade_ogrenme', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('ifade', 150);                  // IfadeKutuphanesi::isle() sonrasi
                    $t->string('kavram_id', 80)->nullable();
                    $t->unsignedBigInteger('hizmet_id');
                    $t->string('kaynak', 12)->default('model');
                    $t->unsignedInteger('adet')->default(1);
                    $t->timestamps();
                    $t->unique(['salon_id', 'ifade'], 'ifade_ogr_salon_ifade_uq');
                    $t->index(['ifade', 'kavram_id'], 'ifade_ogr_ifade_kavram_idx');
                });
            }
            if (!Schema::hasTable('ifade_kayit')) {
                Schema::create('ifade_kayit', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('kanal', 12);                   // whatsapp | santral | sesli | patron
                    $t->string('katman', 16);                  // salon | tam_ad | kutuphane | kelime | model | yok
                    $t->date('tarih');
                    $t->unsignedInteger('adet')->default(0);
                    $t->unique(['salon_id', 'kanal', 'katman', 'tarih'], 'ifade_kayit_uq');
                });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::warning('[IFADE] sema olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ sayac
    public static function say(int $salonId, string $kanal, string $katman): void
    {
        try {
            if (!self::semaGaranti()) return;
            $k = ['salon_id' => $salonId, 'kanal' => mb_substr($kanal, 0, 12), 'katman' => mb_substr($katman, 0, 16), 'tarih' => date('Y-m-d')];
            if (!DB::table('ifade_kayit')->where($k)->increment('adet')) DB::table('ifade_kayit')->insert($k + ['adet' => 1]);
        } catch (\Throwable $e) {}
    }

    /** Son $gun gunun katman dagilimi: ['toplam' => n, 'katman' => [katman => n], 'kanal' => [kanal => n]]; $salonId null = tum salonlar. */
    public static function ozet(?int $salonId = null, int $gun = 30): array
    {
        $bos = ['toplam' => 0, 'katman' => [], 'kanal' => [], 'ogrenilen' => 0, 'ortak' => 0];
        try {
            if (!self::semaGaranti()) return $bos;
            $q = DB::table('ifade_kayit')->where('tarih', '>=', date('Y-m-d', strtotime("-{$gun} days")));
            if ($salonId) $q->where('salon_id', $salonId);
            $rows = $q->get(['kanal', 'katman', 'adet']);
            $o = $bos;
            foreach ($rows as $r) {
                $o['toplam'] += (int) $r->adet;
                $o['katman'][$r->katman] = ($o['katman'][$r->katman] ?? 0) + (int) $r->adet;
                $o['kanal'][$r->kanal] = ($o['kanal'][$r->kanal] ?? 0) + (int) $r->adet;
            }
            $og = DB::table('ifade_ogrenme');
            if ($salonId) $og->where('salon_id', $salonId);
            $o['ogrenilen'] = $og->count();
            $o['ortak'] = array_sum(array_map('count', self::ortakIfadeler()));
            return $o;
        } catch (\Throwable $e) {
            return $bos;
        }
    }

    // ------------------------------------------------------------------ ogrenme
    protected static function anahtar(int $salonId, ?string $tel): ?string
    {
        $tel = $tel ? preg_replace('/\D/', '', $tel) : '';
        return $tel === '' ? null : "ifade_yok_{$salonId}_{$tel}";
    }

    /** Hizmet bulunamadi: musterinin ifadesini 2 saat hatirla (randevu olusursa ogrenilir). */
    public static function bulunamadi(int $salonId, ?string $tel, string $metin): void
    {
        if (($k = self::anahtar($salonId, $tel)) && trim($metin) !== '') Cache::put($k, mb_substr($metin, 0, 150), 120);
    }

    /** Randevu olustu: az once bulunamayan ifade varsa secilen hizmetle ogren. */
    public static function randevuOlustu(int $salonId, ?string $tel, ?int $hizmetId): void
    {
        if (!$hizmetId || !($k = self::anahtar($salonId, $tel))) return;
        $m = Cache::pull($k);
        if ($m) self::ogren($salonId, (string) $m, $hizmetId, 'model');
    }

    public static function ogren(int $salonId, string $metin, int $hizmetId, string $kaynak = 'model'): void
    {
        try {
            if (!self::semaGaranti()) return;
            $isl = IfadeKutuphanesi::isle($metin);
            if (!$isl || count($isl) > 5) return; // yalniz kisa hizmet ifadeleri (uzun cumle ogrenilmez)
            $ifade = mb_substr(implode(' ', $isl), 0, 150);
            $ad = DB::table('hizmetler')->where('id', $hizmetId)->value('hizmet_adi');
            if (!$ad) return;
            $kav = IfadeKutuphanesi::cozumle((string) $ad, false)['adaylar'];
            $kavram = count($kav) === 1 ? $kav[0] : null;
            // 1) bu salon icin hemen (hizmet_eslestir once buna bakar)
            WaDenetci::semaGaranti();
            $es = \App\Http\Controllers\Api\AiAsistanController::esanlamAnahtar($metin);
            if ($es !== '' && Schema::hasTable('wa_hizmet_esanlam')) {
                DB::table('wa_hizmet_esanlam')->updateOrInsert(['salon_id' => $salonId, 'ifade' => mb_substr($es, 0, 120)], ['hizmet_id' => $hizmetId, 'created_at' => now(), 'updated_at' => now()]);
            }
            // 2) ortak havuz adayi
            $var = DB::table('ifade_ogrenme')->where('salon_id', $salonId)->where('ifade', $ifade)->first();
            if ($var) DB::table('ifade_ogrenme')->where('id', $var->id)->update(['hizmet_id' => $hizmetId, 'kavram_id' => $kavram, 'adet' => $var->adet + 1, 'updated_at' => now()]);
            else DB::table('ifade_ogrenme')->insert(['salon_id' => $salonId, 'ifade' => $ifade, 'kavram_id' => $kavram, 'hizmet_id' => $hizmetId, 'kaynak' => $kaynak, 'adet' => 1, 'created_at' => now(), 'updated_at' => now()]);
            Cache::forget('ifade_ortak_ogrenilen');
            Log::info('[IFADE] ogrenildi', ['salon' => $salonId, 'ifade' => $ifade, 'hizmet' => $ad, 'kavram' => $kavram]);
        } catch (\Throwable $e) {
            Log::warning('[IFADE] ogrenilemedi: ' . $e->getMessage());
        }
    }

    /** En az ORTAK_ESIK farkli salonda ayni kavrama giden ve kutuphanenin bilmedigi ifadeler: [kavram_id => [ifade...]]. */
    public static function ortakIfadeler(): array
    {
        try {
            if (!self::semaGaranti()) return [];
            return Cache::remember('ifade_ortak_ogrenilen', 10, function () {
                IfadeKutuphanesi::ekIfadeler([]); // kutuphanenin KENDI bildigine gore suz
                $rows = DB::table('ifade_ogrenme')->whereNotNull('kavram_id')->groupBy('ifade', 'kavram_id')
                    ->havingRaw('COUNT(DISTINCT salon_id) >= ' . self::ORTAK_ESIK)->get(['ifade', 'kavram_id']);
                $out = [];
                foreach ($rows as $r) {
                    if (IfadeKutuphanesi::cozumle($r->ifade, false)['adaylar']) continue; // kutuphane zaten biliyor / baska kavrama gidiyor
                    $out[$r->kavram_id][] = $r->ifade;
                }
                return $out;
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Ortak ogrenilenleri motora ekler (her istek basinda; 10 dk onbellek). */
    public static function hazirla(): void
    {
        try { IfadeKutuphanesi::ekIfadeler(self::ortakIfadeler()); } catch (\Throwable $e) {}
    }
}
