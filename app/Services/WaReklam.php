<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistani SALON REKLAMI: salon kendi kampanyasini (afis + metin + ilgili hizmetler) panelden girer,
 * asistan musteriye uygun anda (ilgili hizmetin fiyati soruldu / "kampanya var mi" dendi) sohbetin icinde gonderir.
 * Yol tarifi gibi: ayni sohbet, salonun kendi numarasi (toplu mesaj degil).
 *
 * Kurallar: salon ayari 'reklam' (yalniz sistem yoneticisi acar, varsayilan KAPALI) + aktif reklam yoksa asistan hic
 * degismez; sohbet basina en fazla 1 reklam; yalniz ilgili hizmette; "kampanya istemiyorum" diyen numaraya bir daha yok.
 * Olcum: wa_reklam_gosterim (gosterim -> tiklama (/k/{token}?g=) -> randevu).
 */
class WaReklam
{
    const SEMA = 'wa_reklam_sema_v1';

    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        if (!WaAsistanVeri::semaGaranti()) return false;
        try {
            if (!Schema::hasTable('wa_reklamlar')) {
                Schema::create('wa_reklamlar', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('baslik', 120);
                    $t->text('metin')->nullable();
                    $t->text('hizmetler')->nullable();              // json [hizmet_id]
                    $t->string('gorsel', 255)->nullable();          // storage/app/... (afis)
                    $t->date('baslangic')->nullable();
                    $t->date('bitis')->nullable();
                    $t->tinyInteger('aktif')->default(1);
                    $t->string('token', 24)->unique();              // herkese acik sayfa /k/{token}
                    $t->timestamps();
                    $t->index(['salon_id', 'aktif'], 'wa_reklam_salon_aktif_idx');
                });
            }
            if (!Schema::hasTable('wa_reklam_gosterim')) {
                Schema::create('wa_reklam_gosterim', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('reklam_id');
                    $t->unsignedBigInteger('salon_id');
                    $t->unsignedBigInteger('oturum_id')->nullable();
                    $t->string('anahtar', 100)->nullable();
                    $t->string('tetik', 20)->nullable();            // fiyat | ai
                    $t->timestamp('tiklama_at')->nullable();
                    $t->unsignedBigInteger('randevu_id')->nullable();
                    $t->timestamp('created_at')->nullable();
                    $t->index(['salon_id', 'created_at'], 'wa_rg_salon_tarih_idx');
                    $t->index('oturum_id', 'wa_rg_oturum_idx');
                    $t->index(['salon_id', 'anahtar'], 'wa_rg_salon_anahtar_idx');
                });
            }
            if (!Schema::hasTable('wa_reklam_ret')) {
                Schema::create('wa_reklam_ret', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('anahtar', 100);
                    $t->timestamp('created_at')->nullable();
                    $t->unique(['salon_id', 'anahtar'], 'wa_reklam_ret_uq');
                });
            }
            if (!Schema::hasColumn('wa_asistan_ayarlar', 'reklam')) {
                Schema::table('wa_asistan_ayarlar', function ($t) { $t->tinyInteger('reklam')->default(0); });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-REKLAM] sema olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    /** Salonda reklam ozelligi acik mi (Ayarlar; yalniz sistem yoneticisi acar). */
    public static function acikMi(int $isletmeId): bool
    {
        return (int) (WaAsistanVeri::ayarlar($isletmeId)['reklam'] ?? 0) === 1;
    }

    /** Bugun yayindaki reklamlar [['id','baslik','metin','hizmetler'=>[id],'hizmet_adlari'=>[],'token']]. */
    public static function aktifler(int $isletmeId): array
    {
        if (!self::semaGaranti()) return [];
        try {
            return Cache::remember("wa_reklam_aktif_{$isletmeId}_" . date('Ymd'), 2, function () use ($isletmeId) {
                $bugun = date('Y-m-d');
                $satirlar = DB::table('wa_reklamlar')->where('salon_id', $isletmeId)->where('aktif', 1)
                    ->where(function ($w) use ($bugun) { $w->whereNull('baslangic')->orWhere('baslangic', '<=', $bugun); })
                    ->where(function ($w) use ($bugun) { $w->whereNull('bitis')->orWhere('bitis', '>=', $bugun); })
                    ->orderByDesc('id')->limit(10)->get();
                $tumId = [];
                foreach ($satirlar as $r) foreach (self::idler($r->hizmetler) as $h) $tumId[] = $h;
                $adlar = $tumId ? DB::table('hizmetler')->whereIn('id', array_unique($tumId))->pluck('hizmet_adi', 'id')->all() : [];
                $out = [];
                foreach ($satirlar as $r) {
                    $ids = self::idler($r->hizmetler);
                    $out[] = ['id' => (int) $r->id, 'baslik' => (string) $r->baslik, 'metin' => (string) ($r->metin ?? ''), 'hizmetler' => $ids,
                        'hizmet_adlari' => array_values(array_filter(array_map(function ($i) use ($adlar) { return $adlar[$i] ?? null; }, $ids))),
                        'token' => (string) $r->token, 'bitis' => $r->bitis];
                }
                return $out;
            });
        } catch (\Throwable $e) {
            Log::warning('[WA-REKLAM] aktifler: ' . $e->getMessage());
            return [];
        }
    }

    /** Bu sohbette reklam gonderilebilir mi: ozellik acik, numara ret etmemis, bu sohbette daha once gonderilmemis. */
    public static function gonderilebilirMi(int $isletmeId, $o): bool
    {
        if (!self::semaGaranti() || !self::acikMi($isletmeId)) return false;
        try {
            if (DB::table('wa_reklam_ret')->where('salon_id', $isletmeId)->where('anahtar', (string) $o->anahtar)->exists()) return false;
            return !DB::table('wa_reklam_gosterim')->where('oturum_id', (int) $o->id)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Musteri "kampanya / reklam istemiyorum" dedi mi (saf). */
    public static function retMi(string $metin): bool
    {
        $s = mb_strtolower(strtr($metin, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        return (bool) preg_match('/(reklam|kampanya|indirim|tan[ıi]t[ıi]m)\w*\s*(mesaj\w*\s*)?(istemiyorum|istemem|g[öo]ndermeyin|atmay[ıi]n|yollamay[ıi]n|gelmesin|olmas[ıi]n)/u', $s)
            || (bool) preg_match('/(istemiyorum|g[öo]ndermeyin|atmay[ıi]n)\s+(reklam|kampanya)/u', $s);
    }

    public static function retEt(int $isletmeId, string $anahtar): void
    {
        if (!self::semaGaranti() || $anahtar === '') return;
        try {
            if (!DB::table('wa_reklam_ret')->where('salon_id', $isletmeId)->where('anahtar', $anahtar)->exists()) {
                DB::table('wa_reklam_ret')->insert(['salon_id' => $isletmeId, 'anahtar' => $anahtar, 'created_at' => now()]);
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Musteri fiyat / kampanya soruyor mu (saf) — reklamin dogal yeri. Fiyat tanima bedava katmaninkiyle AYNI
     * ("ne kadar sürer / ne kadar seans kaldı" fiyat degil).
     */
    public static function fiyatSorusuMu(string $metin): bool
    {
        return WaBedavaKatman::fiyatSorusuMu($metin) || self::kampanyaSorusuMu($metin);
    }

    public static function kampanyaSorusuMu(string $metin): bool
    {
        $s = mb_strtolower(strtr($metin, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        return (bool) preg_match('/kampanya|indirim|f[ıi]rsat|promosyon/u', $s);
    }

    /** Hizmet id listesinden ilk uyan reklam (saf). */
    public static function secListe(array $reklamlar, array $hizmetIdler): ?array
    {
        foreach (array_unique(array_map('intval', $hizmetIdler)) as $h) if ($r = self::sec($reklamlar, $h)) return $r;
        return null;
    }

    /** Teshis: son 30 reklam karari (neden gitti / gitmedi), 1 gun (panel son-hata). */
    public static function kararYaz(int $salonId, int $oturumId, string $karar, array $ek = []): void
    {
        try {
            $l = Cache::get('wa_reklam_kararlar', []);
            array_unshift($l, ['at' => date('Y-m-d H:i:s'), 'salon' => $salonId, 'oturum' => $oturumId, 'karar' => $karar] + $ek);
            Cache::put('wa_reklam_kararlar', array_slice($l, 0, 30), 1440);
        } catch (\Throwable $e) {}
    }

    /** Hizmete uyan ilk reklam (saf). $hizmetId yoksa null. */
    public static function sec(array $reklamlar, ?int $hizmetId): ?array
    {
        if (!$hizmetId) return null;
        foreach ($reklamlar as $r) if (in_array($hizmetId, $r['hizmetler'] ?? [], true)) return $r;
        return null;
    }

    public static function bul(array $reklamlar, ?int $id): ?array
    {
        foreach ($reklamlar as $r) if ((int) $r['id'] === (int) $id) return $r;
        return null;
    }

    /** Musteriye giden reklam mesaji (saf). */
    public static function mesaj(array $r, string $link): string
    {
        $m = '🎁 ' . trim($r['baslik']);
        if (trim((string) $r['metin']) !== '') $m .= "\n" . trim($r['metin']);
        if ($link !== '') $m .= "\n\nDetaylar ve görsel: " . $link;
        return $m . "\n\n(Kampanya mesajı istemezseniz \"kampanya istemiyorum\" yazmanız yeterli.)";
    }

    /**
     * Gosterimi kaydeder ve gonderilecek metni doner. Gonderilemezse geriAl($id) cagrilir.
     * @return array [metin, gosterim_id]
     */
    public static function hazirla(array $r, $o, string $tetik): array
    {
        $gid = (int) DB::table('wa_reklam_gosterim')->insertGetId([
            'reklam_id' => (int) $r['id'], 'salon_id' => (int) $o->isletme_id, 'oturum_id' => (int) $o->id,
            'anahtar' => (string) $o->anahtar, 'tetik' => $tetik, 'created_at' => now(),
        ]);
        return [self::mesaj($r, self::link($r['token'], $gid)), $gid];
    }

    public static function geriAl(int $gosterimId): void
    {
        try { DB::table('wa_reklam_gosterim')->where('id', $gosterimId)->delete(); } catch (\Throwable $e) {}
    }

    /**
     * Musteriye giden link: sabit https alan adi (APP_URL'e guvenilmez: http / farkli alan adi olabiliyor ->
     * panelde afis yuklenmiyordu, 2026-11-20). Gerekirse .env WA_REKLAM_KOK.
     */
    public static function link(string $token, ?int $gosterimId = null): string
    {
        $kok = rtrim((string) (env('WA_REKLAM_KOK') ?: 'https://app.randevumcepte.com.tr'), '/');
        return $kok . '/k/' . $token . ($gosterimId ? '?g=' . $gosterimId : '');
    }

    /** Afis adresi, sayfanin kendi alan adina gore (goreli): panel + kampanya sayfasi. */
    public static function afisYolu(string $token, $guncelleme = null): string
    {
        return '/k/' . $token . '/afis' . ($guncelleme ? '?v=' . strtotime((string) $guncelleme) : '');
    }

    /** Randevu olustu: bu musteriye son 7 gunde gosterilen reklam randevuya donustu say (ilk eslesen). */
    public static function randevuOldu(int $isletmeId, string $anahtar, int $randevuId): void
    {
        if ($randevuId <= 0 || $anahtar === '' || !self::semaGaranti()) return;
        try {
            $g = DB::table('wa_reklam_gosterim')->where('salon_id', $isletmeId)->where('anahtar', $anahtar)->whereNull('randevu_id')
                ->where('created_at', '>=', now()->subDays(7))->orderByDesc('id')->first(['id']);
            if ($g) DB::table('wa_reklam_gosterim')->where('id', $g->id)->update(['randevu_id' => $randevuId]);
        } catch (\Throwable $e) {}
    }

    /** Panel: salonun reklamlari + son 30 gun sonuclari. */
    public static function liste(int $isletmeId): array
    {
        if (!self::semaGaranti()) return [];
        $say = DB::table('wa_reklam_gosterim')->where('salon_id', $isletmeId)->where('created_at', '>=', now()->subDays(30))
            ->groupBy('reklam_id')->select('reklam_id', DB::raw('COUNT(*) gosterim'), DB::raw('SUM(CASE WHEN tiklama_at IS NULL THEN 0 ELSE 1 END) tiklama'),
                DB::raw('SUM(CASE WHEN randevu_id IS NULL THEN 0 ELSE 1 END) randevu'))->get()->keyBy('reklam_id');
        $out = [];
        foreach (DB::table('wa_reklamlar')->where('salon_id', $isletmeId)->orderByDesc('id')->get() as $r) {
            $s = $say[$r->id] ?? null;
            $out[] = ['id' => (int) $r->id, 'baslik' => $r->baslik, 'metin' => $r->metin, 'hizmetler' => self::idler($r->hizmetler),
                'baslangic' => $r->baslangic, 'bitis' => $r->bitis, 'aktif' => (int) $r->aktif, 'token' => $r->token,
                'link' => '/k/' . $r->token, 'gorsel' => $r->gorsel ? self::afisYolu($r->token, $r->updated_at) : null,
                'gosterim' => (int) ($s->gosterim ?? 0), 'tiklama' => (int) ($s->tiklama ?? 0), 'randevu' => (int) ($s->randevu ?? 0)];
        }
        return $out;
    }

    public static function onbellekSil(int $isletmeId): void
    {
        Cache::forget("wa_reklam_aktif_{$isletmeId}_" . date('Ymd'));
    }

    public static function idler($json): array
    {
        return array_values(array_unique(array_filter(array_map('intval', (array) (json_decode((string) $json, true) ?: [])))));
    }
}
