<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp asistani kampanyalarinin (Reklamlarim) kazanci ve katilimcilari (2026-11-23).
 *
 * Kampanya gonderilen her musteri (wa_reklam_gosterim) icin:
 *  - Dogrudan: asistanin kampanya sonrasi actigi randevu (gosterim.randevu_id).
 *  - Salonda yakalanan (dolayli): kampanyayi alan musteri, kampanya BITIS TARIHINE kadar kampanya
 *    hizmetlerinden (hizmet secilmediyse herhangi) randevu aldiysa — personel acmis olsa bile.
 * Kazanc = o randevunun adisyon kalemlerine yapilan tahsilat (tahsilat_hizmetler) — randevu degil, giren para.
 * Indirim = asistanin randevu fiyatina isledigi kampanya indirimi + adisyonda uygulanan indirim.
 * Iptal (2) / reddedilen (3) randevular sayilmaz; ayni randevu iki kampanyaya yazilmaz.
 */
class WaReklamKazanc
{
    const DURUM_AD = [
        'gonderildi' => 'Gönderildi', 'acti' => 'Açtı', 'randevu' => 'Randevu aldı', 'geldi' => 'Geldi', 'odedi' => 'Ödedi',
    ];

    /**
     * @param int      $isletmeId
     * @param int|null $reklamId  yalniz bu kampanya
     * @param int      $gun       son N gunde gonderilenler (0 = tumu)
     */
    public static function hesapla(int $isletmeId, ?int $reklamId = null, int $gun = 30): array
    {
        $bos = ['ozet' => self::bosOzet(), 'reklamlar' => [], 'katilimcilar' => []];
        try {
            if (!WaReklam::semaGaranti()) return $bos;

            $q = DB::table('wa_reklam_gosterim')->where('salon_id', $isletmeId);
            if ($reklamId) $q->where('reklam_id', $reklamId);
            if ($gun > 0) $q->where('created_at', '>=', now()->subDays($gun)->startOfDay());
            $gosterimler = $q->orderBy('id')->limit(3000)
                ->get(['id', 'reklam_id', 'oturum_id', 'anahtar', 'tiklama_at', 'randevu_id', 'indirim_tutari', 'created_at']);
            if ($gosterimler->isEmpty()) return $bos;

            $reklamlar = DB::table('wa_reklamlar')->where('salon_id', $isletmeId)
                ->whereIn('id', $gosterimler->pluck('reklam_id')->unique()->all())->get()->keyBy('id');

            // ── Musteri: oturumun telefonu -> salonun musteri kaydi ──
            $oturumlar = DB::table('wa_asistan_oturumlari')->whereIn('id', $gosterimler->pluck('oturum_id')->filter()->unique()->all())
                ->get(['id', 'telefon', 'musteri_tel', 'anahtar'])->keyBy('id');
            $tel = [];
            foreach ($gosterimler as $g) {
                $o = $oturumlar[$g->oturum_id] ?? null;
                $ham = $o ? ($o->musteri_tel ?: ($o->telefon ?: $o->anahtar)) : $g->anahtar;
                $t = substr(preg_replace('/\D/', '', (string) $ham), -10);
                $tel[$g->id] = strlen($t) === 10 ? $t : null;
            }
            $kullanicilar = [];
            $telListe = array_values(array_unique(array_filter($tel)));
            if ($telListe) {
                $rows = DB::table('musteri_portfoy')->join('users', 'users.id', '=', 'musteri_portfoy.user_id')
                    ->where('musteri_portfoy.salon_id', $isletmeId)
                    ->whereIn(DB::raw('RIGHT(users.cep_telefon, 10)'), $telListe)
                    ->get(['users.id', 'users.name', 'users.cep_telefon']);
                foreach ($rows as $u) $kullanicilar[substr(preg_replace('/\D/', '', (string) $u->cep_telefon), -10)] = $u;
            }

            // ── Randevular: dogrudan bagli + dolayli aday (kampanyayi alan musterilerin randevulari) ──
            $userIdler = [];
            foreach ($tel as $t) if ($t && isset($kullanicilar[$t])) $userIdler[] = (int) $kullanicilar[$t]->id;
            $userIdler = array_values(array_unique($userIdler));
            $dogrudanIdler = $gosterimler->pluck('randevu_id')->filter()->unique()->values()->all();
            $enEski = (string) $gosterimler->min('created_at');
            $randevular = DB::table('randevular')->where('salon_id', $isletmeId)
                ->where(function ($w) use ($userIdler, $dogrudanIdler, $enEski) {
                    $w->whereIn('id', $dogrudanIdler ?: [0]);
                    if ($userIdler) $w->orWhere(function ($w2) use ($userIdler, $enEski) {
                        $w2->whereIn('user_id', $userIdler)->where('created_at', '>=', $enEski);
                    });
                })
                ->whereNotIn('durum', [2, 3])
                ->orderBy('created_at')->get(['id', 'user_id', 'tarih', 'saat', 'created_at'])->keyBy('id');
            $randevuHizmet = [];
            if ($randevular->count()) {
                foreach (DB::table('randevu_hizmetler')->whereIn('randevu_id', $randevular->keys()->all())->get(['randevu_id', 'hizmet_id']) as $rh) {
                    $randevuHizmet[(int) $rh->randevu_id][] = (int) $rh->hizmet_id;
                }
            }

            // Ayni musteri ayni kampanyayi birden cok oturumda aldiysa ilk gonderim sayilir
            $gorulen = []; $kullanilanRandevu = [];
            foreach ($gosterimler as $g) if ($g->randevu_id) $kullanilanRandevu[(int) $g->randevu_id] = true;
            $satirlar = [];
            foreach ($gosterimler as $g) {
                $t = $tel[$g->id];
                $u = $t ? ($kullanicilar[$t] ?? null) : null;
                $kimlik = $g->reklam_id . '|' . ($u ? 'u' . $u->id : ($t ?: 'a' . $g->anahtar));
                if (isset($gorulen[$kimlik])) continue;
                $gorulen[$kimlik] = true;
                $rek = $reklamlar[$g->reklam_id] ?? null;

                $randevuId = $g->randevu_id ? (int) $g->randevu_id : null;
                $kaynak = $randevuId ? 'asistan' : null;
                if (!$randevuId && $u && $rek) {
                    // Dolayli: kampanya bitis tarihine kadar, kampanya hizmetlerinden randevu
                    $son = $rek->bitis ? date('Y-m-d 23:59:59', strtotime($rek->bitis)) : date('Y-m-d H:i:s');
                    $hiz = WaReklam::idler($rek->hizmetler);
                    foreach ($randevular as $r) {
                        if ((int) $r->user_id !== (int) $u->id || isset($kullanilanRandevu[(int) $r->id])) continue;
                        if ((string) $r->created_at < (string) $g->created_at || (string) $r->created_at > $son) continue;
                        if ($hiz && !array_intersect($hiz, $randevuHizmet[(int) $r->id] ?? [])) continue;
                        $randevuId = (int) $r->id; $kaynak = 'salon'; $kullanilanRandevu[$randevuId] = true;
                        break;
                    }
                }
                if ($randevuId && !isset($randevular[$randevuId])) { $randevuId = null; $kaynak = null; } // iptal/baska salon
                $satirlar[] = ['g' => $g, 'u' => $u, 'tel' => $t, 'rek' => $rek, 'randevu_id' => $randevuId, 'kaynak' => $kaynak];
            }

            // ── Adisyon + tahsilat ──
            $rIdler = array_values(array_filter(array_column($satirlar, 'randevu_id')));
            $ah = $rIdler ? DB::table('adisyon_hizmetler')->whereIn('randevu_id', $rIdler)->get(['id', 'randevu_id', 'geldi', 'indirim_tutari']) : collect();
            $odenenAh = [];
            if ($ah->count()) {
                foreach (DB::table('tahsilat_hizmetler')->whereIn('adisyon_hizmet_id', $ah->pluck('id')->all())
                    ->groupBy('adisyon_hizmet_id')->select('adisyon_hizmet_id', DB::raw('SUM(tutar) t'))->get() as $th) {
                    $odenenAh[(int) $th->adisyon_hizmet_id] = (float) $th->t;
                }
            }
            $rBilgi = [];
            foreach ($ah as $a) {
                $rid = (int) $a->randevu_id;
                if (!isset($rBilgi[$rid])) $rBilgi[$rid] = ['odenen' => 0.0, 'geldi' => false, 'indirim' => 0.0];
                $rBilgi[$rid]['odenen'] += $odenenAh[(int) $a->id] ?? 0;
                $rBilgi[$rid]['indirim'] += (float) ($a->indirim_tutari ?? 0);
                if ((int) $a->geldi === 1) $rBilgi[$rid]['geldi'] = true;
            }

            // ── Satirlar + ozet ──
            $ozet = self::bosOzet(); $rekOzet = []; $liste = [];
            foreach ($satirlar as $s) {
                $g = $s['g']; $rid = $s['randevu_id']; $b = $rid ? ($rBilgi[$rid] ?? ['odenen' => 0.0, 'geldi' => false, 'indirim' => 0.0]) : null;
                $odenen = $b ? round($b['odenen'], 2) : 0.0;
                $indirim = round((float) ($g->indirim_tutari ?? 0) + ($b ? $b['indirim'] : 0), 2);
                $durum = $odenen > 0 ? 'odedi' : (($b && $b['geldi']) ? 'geldi' : ($rid ? 'randevu' : ($g->tiklama_at ? 'acti' : 'gonderildi')));
                $rk = (int) $g->reklam_id;
                if (!isset($rekOzet[$rk])) $rekOzet[$rk] = self::bosOzet();
                foreach ([&$ozet, &$rekOzet[$rk]] as &$o) {
                    $o['ulasilan']++;
                    if ($g->tiklama_at) $o['acan']++;
                    if ($rid) $o['randevu']++;
                    if (in_array($durum, ['geldi', 'odedi'], true)) $o['gelen']++;
                    $o['kazanc'] += $odenen; $o['indirim'] += $indirim;
                    if ($s['kaynak'] === 'salon') $o['kazanc_salon'] += $odenen; else $o['kazanc_asistan'] += $odenen;
                }
                unset($o);
                $r = $rid ? $randevular[$rid] : null;
                $liste[] = [
                    'gosterim_id' => (int) $g->id, 'reklam_id' => $rk, 'reklam' => $s['rek']->baslik ?? '',
                    'user_id' => $s['u'] ? (int) $s['u']->id : null,
                    'ad' => $s['u'] ? $s['u']->name : null,
                    'telefon' => $s['u'] ? $s['u']->cep_telefon : ($s['tel'] ? '0' . $s['tel'] : null),
                    'gonderim' => (string) $g->created_at,
                    'durum' => $durum, 'durum_ad' => self::DURUM_AD[$durum],
                    'kaynak' => $s['kaynak'], // asistan | salon | null
                    'randevu_id' => $rid, 'randevu_tarih' => $r ? trim($r->tarih . ' ' . substr((string) $r->saat, 0, 5)) : null,
                    'odenen' => $odenen, 'indirim' => $indirim,
                ];
            }
            foreach ([&$ozet] as &$o) self::yuvarla($o);
            unset($o);
            foreach ($rekOzet as &$o) self::yuvarla($o);
            unset($o);
            usort($liste, function ($a, $b) { return strcmp($b['gonderim'], $a['gonderim']); });
            return ['ozet' => $ozet, 'reklamlar' => $rekOzet, 'katilimcilar' => array_slice($liste, 0, 300)];
        } catch (\Throwable $e) {
            Log::warning('[WA-REKLAM] kazanc hesaplanamadi', ['salon' => $isletmeId, 'err' => $e->getMessage()]);
            return $bos;
        }
    }

    private static function bosOzet(): array
    {
        return ['ulasilan' => 0, 'acan' => 0, 'randevu' => 0, 'gelen' => 0,
            'kazanc' => 0.0, 'kazanc_asistan' => 0.0, 'kazanc_salon' => 0.0, 'indirim' => 0.0];
    }

    private static function yuvarla(array &$o): void
    {
        foreach (['kazanc', 'kazanc_asistan', 'kazanc_salon', 'indirim'] as $k) $o[$k] = round((float) $o[$k], 2);
    }
}
