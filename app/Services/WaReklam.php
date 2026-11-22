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
    const SEMA = 'wa_reklam_sema_v2';

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
            // v2: kampanya indirimi (yuzde | tutar) -> asistanin olusturdugu randevunun fiyatina islenir
            if (!Schema::hasColumn('wa_reklamlar', 'indirim_tur')) {
                Schema::table('wa_reklamlar', function ($t) { $t->string('indirim_tur', 10)->nullable(); $t->decimal('indirim_deger', 10, 2)->nullable(); });
            }
            if (!Schema::hasColumn('wa_reklam_gosterim', 'indirim_tutari')) {
                Schema::table('wa_reklam_gosterim', function ($t) { $t->decimal('indirim_tutari', 10, 2)->nullable(); });
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
                        'token' => (string) $r->token, 'bitis' => $r->bitis, 'indirim_tur' => $r->indirim_tur ?? null, 'indirim_deger' => $r->indirim_deger ?? null];
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
        // Sablon (kullanici onayi 2026-11-20): baslik kalin, metin satir satir ✨, son gun, hizmetler, link, net cagri
        $m = '🎁 *' . trim($r['baslik']) . "*\n";
        $satirlar = array_values(array_filter(array_map('trim', preg_split('/\R/u', trim((string) ($r['metin'] ?? '')))), 'strlen'));
        $ek = [];
        foreach ($satirlar as $s) $ek[] = '✨ ' . $s;
        if ($ie = self::indirimEtiket($r)) $ek[] = '🏷️ Kampanya indirimi: *' . $ie . '*';
        if (!empty($r['hizmet_adlari'])) $ek[] = '💆 ' . implode(', ', array_slice($r['hizmet_adlari'], 0, 4));
        if (!empty($r['bitis']) && ($ts = strtotime((string) $r['bitis']))) {
            $ay = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
            $ek[] = '📅 Son gün: *' . (int) date('j', $ts) . ' ' . $ay[(int) date('n', $ts)] . '*';
        }
        if ($ek) $m .= "\n" . implode("\n", $ek) . "\n";
        if ($link !== '') $m .= "\n👉 Detaylar ve afiş:\n" . $link . "\n";
        return $m . "\nKampanyadan yararlanmak için *\"evet\"* yazmanız yeterli, randevunuzu hemen oluşturalım 🙂"
            . "\n\n_Kampanya mesajı istemezseniz \"kampanya istemiyorum\" yazın._";
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
                ->where('created_at', '>=', now()->subDays(7))->orderByDesc('id')->first(['id', 'reklam_id']);
            if (!$g) return;
            DB::table('wa_reklam_gosterim')->where('id', $g->id)->update(['randevu_id' => $randevuId]);
            $rek = DB::table('wa_reklamlar')->where('id', $g->reklam_id)->where('salon_id', $isletmeId)->first();
            $baslik = (string) ($rek->baslik ?? '');
            if ($baslik !== '') {
                $ind = self::indirimUygula($isletmeId, $rek, $randevuId);
                if ($ind > 0) DB::table('wa_reklam_gosterim')->where('id', $g->id)->update(['indirim_tutari' => $ind]);
                // Tahsilati alan personel gorsun: indirim islendiyse ne kadar, islenmediyse elle uygulasin
                $not = '🎁 WhatsApp kampanyası: ' . $baslik . ($ind > 0
                    ? ' (' . self::indirimEtiket($rek) . ' indirim randevu fiyatına işlendi, toplam -' . self::tl($ind) . ')'
                    : (self::indirimEtiket($rek) !== '' ? ' (kampanya hizmeti yok / fiyat 0: indirim işlenmedi)' : ' (indirimi tahsilatta uygulayın)'));
                $eski = (string) DB::table('randevular')->where('id', $randevuId)->where('salon_id', $isletmeId)->value('personel_notu');
                if (mb_strpos($eski, $not) === false) {
                    DB::table('randevular')->where('id', $randevuId)->where('salon_id', $isletmeId)
                        ->update(['personel_notu' => trim($not . "\n" . $eski)]); // kampanya notu basta: kolon kisaysa bile gorunur
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Kampanya indirimini randevunun kampanya hizmetlerine isler (randevu_hizmetler.fiyat). Tahsilat (web + mobil)
     * adisyonu bu fiyattan actigi icin indirim tahsilata da yansir. Yalniz: kampanya bugun yayinda, indirim tanimli,
     * satir kampanya hizmeti, paket seansi degil, henuz adisyona gecmemis. Yuzde: her satira; tutar: toplamda bir kez.
     * @return float uygulanan toplam indirim (TL)
     */
    public static function indirimUygula(int $isletmeId, $rek, int $randevuId): float
    {
        $tur = (string) ($rek->indirim_tur ?? ''); $deger = (float) ($rek->indirim_deger ?? 0);
        if (!in_array($tur, ['yuzde', 'tutar'], true) || $deger <= 0 || !(int) $rek->aktif) return 0.0;
        $bugun = date('Y-m-d');
        if (($rek->baslangic && $rek->baslangic > $bugun) || ($rek->bitis && $rek->bitis < $bugun)) return 0.0;
        if (!DB::table('randevular')->where('id', $randevuId)->where('salon_id', $isletmeId)->exists()) return 0.0;
        $hiz = self::idler($rek->hizmetler);
        $paketli = DB::table('adisyon_paket_seanslar')->where('randevu_id', $randevuId)->pluck('hizmet_id')->map(function ($x) { return (int) $x; })->all();
        $adisyonda = DB::table('adisyon_hizmetler')->where('randevu_id', $randevuId)->pluck('hizmet_id')->map(function ($x) { return (int) $x; })->all();
        $kalan = $tur === 'tutar' ? $deger : 0; $toplam = 0.0;
        foreach (DB::table('randevu_hizmetler')->where('randevu_id', $randevuId)->orderBy('id')->get(['id', 'hizmet_id', 'fiyat', 'yardimci_personel']) as $s) {
            $h = (int) $s->hizmet_id; $f = (float) $s->fiyat;
            if ($f <= 0 || !empty($s->yardimci_personel) || ($hiz && !in_array($h, $hiz, true)) || in_array($h, $paketli, true) || in_array($h, $adisyonda, true)) continue;
            if ($tur === 'yuzde') $d = round($f * min($deger, 100) / 100, 2);
            else { if ($kalan <= 0) break; $d = min($kalan, $f); $kalan -= $d; }
            if ($d <= 0) continue;
            DB::table('randevu_hizmetler')->where('id', $s->id)->update(['fiyat' => round($f - $d, 2)]);
            $toplam += $d;
        }
        if ($toplam > 0) Log::info('[WA-REKLAM] indirim islendi', ['salon' => $isletmeId, 'randevu' => $randevuId, 'reklam' => $rek->id, 'tutar' => $toplam]);
        return round($toplam, 2);
    }

    /** "%15" | "250 TL" | "" */
    public static function indirimEtiket($r): string
    {
        $r = (array) $r; $d = (float) ($r['indirim_deger'] ?? 0);
        if ($d <= 0) return '';
        if (($r['indirim_tur'] ?? '') === 'yuzde') return '%' . rtrim(rtrim(number_format($d, 2, ',', ''), '0'), ',');
        if (($r['indirim_tur'] ?? '') === 'tutar') return self::tl($d);
        return '';
    }

    public static function tl(float $x): string
    {
        return number_format($x, fmod($x, 1) ? 2 : 0, ',', '.') . ' TL';
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
                'indirim_tur' => $r->indirim_tur ?? null, 'indirim_deger' => isset($r->indirim_deger) ? (float) $r->indirim_deger : null,
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
