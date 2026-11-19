<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RandevuAsistan;
use App\Services\SantralPaket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Randevu AI Santral kopru uclari (restaurant-laravel /api/santral/* muadili).
 * Node kopru (randevu-santral-ai) bu uclari cagirir: STT metni gonderir, cevap+aksiyon alir.
 * Ses YOK; sadece metin. sube_id = salon_id.
 */
class SantralKopruController extends Controller
{
    // ---------------------------------------------------------- tablolar
    protected function tabloGaranti(): void
    {
        // GECIKME: her turda 2 Schema::hasTable (information_schema sorgusu) yapiliyordu. Tablolar bir kez
        // dogrulaninca bayrak onbellekte tutulur (paket semasi zaten boyle).
        if (\Cache::get('santral_tablo_v1') && SantralPaket::semaHazir()) return;
        if (!Schema::hasTable('santral_oturumlari')) {
            Schema::create('santral_oturumlari', function ($t) {
                $t->bigIncrements('id'); // Laravel 5.6: id() yok (L7+)
                $t->unsignedBigInteger('sube_id');              // = salon_id
                $t->string('telefon', 30)->nullable();
                $t->unsignedBigInteger('musteri_id')->nullable();
                $t->longText('gecmis')->nullable();             // JSON [{role,content}]
                $t->string('durum', 15)->default('acik');       // acik | kapandi
                $t->string('sonuc', 20)->nullable();            // randevu | iptal | guncelle | aktar | bilgi
                $t->unsignedBigInteger('randevu_id')->nullable();
                $t->timestamp('created_at')->useCurrent();
                $t->timestamp('updated_at')->nullable();
                $t->index(['sube_id', 'durum']);
            });
        }
        if (!Schema::hasTable('santral_ses_kayit')) {
            Schema::create('santral_ses_kayit', function ($t) {
                $t->bigIncrements('id'); // Laravel 5.6: id() yok (L7+)
                $t->unsignedBigInteger('oturum_id')->nullable();
                $t->unsignedBigInteger('sube_id')->nullable();
                $t->string('tur', 12)->default('ai');           // ai | aktarma
                $t->string('dosya', 255);
                $t->unsignedInteger('boyut')->default(0);
                $t->timestamp('created_at')->useCurrent();
                $t->index('oturum_id');
            });
        }
        SantralPaket::semaGaranti(); // paket tablosu + oturum kullanim kolonlari (yon/sure/token/maliyet)
        \Cache::forever('santral_tablo_v1', 1);
    }

    /** Kullanim kolonlari hazir mi (sema garantisi basarisizsa insert/update bozulmasin). */
    protected function kullanimKolonlari(): bool
    {
        return (bool) SantralPaket::semaHazir();
    }

    protected function yetkiVar(Request $r): bool
    {
        $secret = (string) env('SANTRAL_SECRET', '');
        if ($secret === '') return true; // sir tanimli degilse acik
        $gelen = $r->header('X-Santral-Secret') ?: $r->input('santral_secret');
        return is_string($gelen) && hash_equals($secret, $gelen);
    }

    protected function j($data, int $code = 200)
    {
        return response()->json($data, $code, [], JSON_UNESCAPED_UNICODE);
    }

    // ---------------------------------------------------------- /baslat
    public function baslat(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $salonId = (int) ($r->input('sube_id') ?: (DB::table('salonlar')->min('id') ?: 1));
        $telefon = trim((string) $r->input('telefon'));

        // GIDEN HATIRLATMA ARAMASI (kopru: Stasis hatirlatma,<hedef>): salon + musteri telefonu KAYITTAN alinir
        // (giden cagride kanal caller-id'si isletmenin numarasidir, musteri degil).
        $hedef = trim((string) $r->input('hatirlatma'));
        $hMeta = null;
        if ($hedef !== '' && ($hh = RandevuAsistan::hatirlatmaHedefi($hedef))) {
            $salonId = $hh['salon_id'];
            if (!empty($hh['telefon'])) $telefon = (string) $hh['telefon'];
        }

        // Asistan paketi YOK ya da bitti (+ "asimda durdur"): GELEN cagriyi AI karsilamaz. Hata kodu ->
        // kopru cagriyi salonun operator_kanali'na aktarir (paket_yok/paket_bitti'de anons yapmadan, zil sesiyle).
        if ($hedef === '' && ($neden = SantralPaket::kapaliNedeni($salonId))) {
            \Log::info('[SANTRAL-PAKET] asistan kapali, gelen cagri operatore', ['salon' => $salonId, 'neden' => $neden]);
            return $this->j(['ok' => 0, 'hata' => $neden], 402);
        }

        $as = new RandevuAsistan($salonId, $telefon ?: null);
        $ttsSes = SantralPaket::salonSesi($salonId); // isletmenin paketinde secilen ses (kopru bununla okur)
        $ilkMesaj = null;
        if ($hedef !== '' && ($hk = $as->hatirlatmaKarsilama($hedef))) {
            [$karsilama, $hMeta] = $hk;
            $ilkMesaj = ['role' => 'assistant', 'content' => $karsilama, 'hatirlatma' => $hMeta];
            \Log::info('[SANTRAL-HATIRLATMA] arama acildi', ['salon' => $salonId, 'hedef' => $hedef]);
        } else {
            if ($hedef !== '') \Log::warning('[SANTRAL-HATIRLATMA] hedef gecersiz/iptal, normal karsilama', ['hedef' => $hedef]);
            $karsilama = $as->karsilama();
            $ilkMesaj = ['role' => 'assistant', 'content' => $karsilama];
        }

        $oid = DB::table('santral_oturumlari')->insertGetId([
            'sube_id' => $salonId,
            'telefon' => $telefon ?: null,
            'gecmis' => json_encode([$ilkMesaj], JSON_UNESCAPED_UNICODE),
            'durum' => 'acik',
            'created_at' => now(),
        ] + ($this->kullanimKolonlari() ? ['yon' => $hedef !== '' ? 'giden' : 'gelen', 'tts_karakter' => mb_strlen($karsilama), 'tts_ses' => $ttsSes] : [])
          + ($hMeta && !empty($hMeta['randevu_id']) ? ['randevu_id' => (int) $hMeta['randevu_id']] : []));

        return $this->j([
            'ok' => 1,
            'oturum_id' => $oid,
            'karsilama' => $karsilama,
            'sube_id' => $salonId,
            'menu_adet' => $as->hizmetSayisi(),   // randevuda: aktif hizmet sayisi
            'geri_arama' => 0,
            'musteri' => $as->taninanMusteri(),
            'son_siparis' => '',
            'stt_ipuclari' => $as->sttIpuclari(), // kopru: Google STT speechContexts (hizmet adlari dogru duyulsun)
            'tts_ses' => $ttsSes,
        ]);
    }

    // ---------------------------------------------------------- /konus
    public function konus(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        // GECIKME DOKUMU (Laravel ici): acilis (PHP+Laravel boot) / hazirlik / asistan kurulum / konus / yazma
        $sGir = microtime(true);
        $sIstek = (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? $sGir);
        $this->tabloGaranti();

        $oid = (int) $r->input('oturum_id');
        $metin = (string) $r->input('metin');
        $o = DB::table('santral_oturumlari')->where('id', $oid)->first();
        if (!$o) return $this->j(['ok' => 0, 'hata' => 'oturum_yok'], 404);

        // tur: kopru tur kimligi. paralel_onceki: musteri cumlesine devam etti, kopru eski turun bitmesini BEKLEMEDEN
        // bu turu acti (eski tur = paralel_onceki; is yapmadiysa cevabi calinmaz, gecmisten dusurulur).
        $tur = (int) $r->input('tur');
        $paralelOnceki = (int) $r->input('paralel_onceki');
        $gecmisHazirla = function ($json) use ($r, $oid, $paralelOnceki) {
            $g = $this->iptalTurlariDus($oid, json_decode($json ?: '[]', true) ?: [], $paralelOnceki);
            // Kopru bir onceki cevabi CALMADI (musteri o sirada yeni bir sey soyledi, cevap is yapmamisti):
            // musterinin duymadigi o asistan cevabini gecmisten dusur (ilk karsilama/hatirlatma mesaji asla dusmez).
            if ($r->input('onceki_duyulmadi') && count($g) > 1) {
                $son = end($g);
                if (($son['role'] ?? '') === 'assistant' && empty($son['hatirlatma'])) array_pop($g);
            }
            return $g;
        };
        $gecmis = $gecmisHazirla($o->gecmis);

        // Paralel turda eski tur bir ONAYI isliyor olabilir ("Evet" -> randevu olusturma): ayni anda ikinci bir
        // randevu olusmasin diye bu durumda eski turun bitmesi beklenir; eski tur is yaptiysa bu tur hic calismaz.
        if ($paralelOnceki > 0 && $this->onayBekleyenSoru($gecmis)) {
            $bitti = null;
            for ($i = 0; $i < 120 && $bitti === null; $i++) {
                $bitti = Cache::get("santral_tur_bitti_{$oid}_{$paralelOnceki}");
                if ($bitti === null) usleep(100000);
            }
            if ($bitti !== null && $bitti !== '-') {
                return $this->j(['ok' => 1, 'cevap' => '', 'aksiyon' => null, 'bitir' => false, 'iptal_edildi' => 1]);
            }
            $gecmis = $gecmisHazirla(DB::table('santral_oturumlari')->where('id', $oid)->value('gecmis'));
        }

        $sHazir = microtime(true);
        $as = new RandevuAsistan((int) $o->sube_id, $o->telefon);
        $sKur = microtime(true);
        // Yetkiliye ulasilamadi / mesai disi -> asistan "konuyu söyleyin, notuma ekleyeyim" dediyse bu cumle NOTTUR
        $d = $this->geriAraNotTuru($oid, $metin, $gecmis) ?? $as->konus($metin, $gecmis);
        // Ust uste IKINCI kez anlasilamadi: musteriyi uzatma, isletmeye aktar (2026-11-19 isletme istegi, kayit 178)
        if (empty($d['aksiyon']) && empty($d['bitir']) && $as->ikinciAnlamama((string) ($d['cevap'] ?? ''), $gecmis)) {
            \Log::info('[SANTRAL-TUR] iki kez anlasilamadi -> aktar', ['oturum' => $oid, 'metin' => $metin, 'cevap' => $d['cevap'] ?? '']);
            $as->teshis = 'anlasilamadi_aktar';
            $d = ['cevap' => 'Kusura bakmayın, size daha iyi yardımcı olabilmesi için sizi hemen yetkili arkadaşıma bağlıyorum, lütfen hatta kalın.',
                  'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
        }
        // Aktarma yerine not: mesai disi ya da bu aramada yetkiliye zaten ulasilamadi (tekrar caldirmak bosuna)
        if (($d['aksiyon'] ?? null) === 'aktar' && ($gNeden = $this->aktarmaYerineNot($o))) {
            $this->geriAraKaydet($o, $gNeden);
            $d = ['cevap' => $this->geriAraCumlesi($gNeden, $o->telefon), 'aksiyon' => null, 'veri' => [], 'bitir' => false, 'geri_ara_soru' => 1];
        }
        $sKonus = microtime(true);

        // PARALEL TUR: bu tur dusunurken diger tur gecmise yazmis olabilir -> gecmisi TAZE oku, ustune ekle
        // (eski kopyanin ustune yazilirsa o tur silinirdi). Kopru bu turu iptal ettiyse gecmise yazilmaz.
        if ($tur > 0) $gecmis = $gecmisHazirla(DB::table('santral_oturumlari')->where('id', $oid)->value('gecmis'));
        // Kalip "Başka yardımcı olabileceğim bir konu var mı?" -> sirayla dogal soru (kayit 172)
        if (!empty($d['cevap'])) $d['cevap'] = $as->kapanisDogallastir((string) $d['cevap'], $gecmis);

        // gecmisi guncelle (son 40)
        $isaret = $tur > 0 ? ['tur' => $tur] : [];
        $gecmis[] = ['role' => 'user', 'content' => $metin] + $isaret;
        $asistanMsj = ['role' => 'assistant', 'content' => $d['cevap']] + $isaret;
        if (!empty($d['baglam'])) $asistanMsj['baglam'] = $d['baglam']; // gizli arac ozeti (id'ler) -> sonraki tur
        if (!empty($d['geri_ara_soru'])) $asistanMsj['geri_ara_soru'] = 1; // sonraki musteri cumlesi = geri arama notu
        $gecmis[] = $asistanMsj;
        $gecmis = array_slice($gecmis, -40);
        if ($tur > 0) $gecmis = $this->iptalTurlariDus($oid, $gecmis);

        $guncelle = ['gecmis' => json_encode($gecmis, JSON_UNESCAPED_UNICODE), 'updated_at' => now()];
        // Gizli numarayla arayan kayitta telefonunu soyledi: sonraki turlar (tanima/randevu) bu numarayla yurusun
        if ($as->yeniTelefon) $guncelle['telefon'] = $as->yeniTelefon;
        $aksiyon = $d['aksiyon'] ?? null;
        if (in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'aktar', 'teyit', 'odeme'], true)) $guncelle['sonuc'] = $aksiyon;
        if (!empty($d['veri']['randevu_id'])) $guncelle['randevu_id'] = (int) $d['veri']['randevu_id'];
        if (!empty($d['bitir'])) $guncelle['durum'] = 'kapandi';
        DB::table('santral_oturumlari')->where('id', $oid)->update($guncelle);
        // Paralel tur bu turun bittigini (ve is yapip yapmadigini) buradan gorur; gecmis yazildiktan SONRA
        if ($tur > 0) Cache::put("santral_tur_bitti_{$oid}_{$tur}", $aksiyon ?: (!empty($d['bitir']) ? 'bitir' : '-'), 10); // L5.6: dakika
        if ($this->kullanimKolonlari()) SantralPaket::turKaydet($oid, (int) $o->sube_id, $as->kullanim, mb_strlen((string) $d['cevap']));
        $ms = function ($a, $b) { return (int) round(($b - $a) * 1000); };
        $sSon = microtime(true);
        \Log::info('[SANTRAL-LARAVEL-SURE]', ['oturum' => $oid, 'acilis_ms' => $ms($sIstek, $sGir), 'hazirlik_ms' => $ms($sGir, $sHazir),
            'asistan_kur_ms' => $ms($sHazir, $sKur), 'konus_ms' => $ms($sKur, $sKonus), 'yazma_ms' => $ms($sKonus, $sSon), 'toplam_ms' => $ms($sIstek, $sSon)]);

        return $this->j([
            'ok' => 1,
            'cevap' => $d['cevap'],
            'aksiyon' => $aksiyon,
            'bitir' => (bool) ($d['bitir'] ?? false),
            'teshis' => $as->teshis,
            'randevu_id' => $d['veri']['randevu_id'] ?? null,   // teshis/e2e (kopru kullanmaz)
        ]);
    }

    // ---------------------------------------------------------- /tur-iptal
    // Kopru bir turun cevabini CALMADI (paralel turda yenisi kullanildi): o turun mesajlari gecmisten dusulur;
    // tur henuz bitmediyse bittiginde gecmise yazilmaz (Cache isareti).
    public function turIptal(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $oid = (int) $r->input('oturum_id');
        $tur = (int) $r->input('tur');
        if (!$oid || !$tur) return $this->j(['ok' => 0]);
        Cache::put("santral_tur_iptal_{$oid}_{$tur}", 1, 10); // L5.6: dakika
        $json = DB::table('santral_oturumlari')->where('id', $oid)->value('gecmis');
        $g = json_decode($json ?: '[]', true) ?: [];
        $yeni = $this->iptalTurlariDus($oid, $g);
        if (count($yeni) !== count($g)) {
            DB::table('santral_oturumlari')->where('id', $oid)->update(['gecmis' => json_encode($yeni, JSON_UNESCAPED_UNICODE)]);
        }
        return $this->j(['ok' => 1]);
    }

    /**
     * Iptal edilmis turlarin mesajlarini dusur. $paralelOnceki: bu turun yerine gectigi eski tur; is yapmadan
     * bittiyse (santral_tur_bitti = '-') onun mesajlari da dusulur (musteri o cevabi hic duymadi).
     */
    protected function iptalTurlariDus(int $oid, array $gecmis, int $paralelOnceki = 0): array
    {
        $durum = [];
        $out = [];
        foreach ($gecmis as $m) {
            $t = (int) ($m['tur'] ?? 0);
            if ($t > 0) {
                if (!isset($durum[$t])) {
                    $durum[$t] = Cache::has("santral_tur_iptal_{$oid}_{$t}")
                        || ($t === $paralelOnceki && Cache::get("santral_tur_bitti_{$oid}_{$t}") === '-');
                }
                if ($durum[$t]) continue;
            }
            $out[] = $m;
        }
        return $out;
    }

    /** Musterinin duydugu son asistan cumlesi bir islem onayi/bilgisi mi soruyordu (cevap randevu/kayit yapabilir)? */
    protected function onayBekleyenSoru(array $gecmis): bool
    {
        for ($i = count($gecmis) - 1; $i >= 0; $i--) {
            if (($gecmis[$i]['role'] ?? '') !== 'assistant') continue;
            $c = mb_strtolower((string) ($gecmis[$i]['content'] ?? ''), 'UTF-8');
            return (bool) (preg_match('/(?<!\p{L})(mı|mi|mu|mü|musunuz|müsünüz|mısınız|misiniz|mıyım|miyim|muyum|müyüm)(?!\p{L})/u', $c)
                && preg_match('/randevu|iptal|kaydet|kayd|taşı|değiştir|güncelle|oluştur|onay|ad(ınız|ı ve)|soyad/u', $c));
        }
        return false;
    }

    // ---------------------------------------------------------- /bitir
    public function bitir(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $oid = (int) $r->input('oturum_id');
        $sonuc = (string) ($r->input('ozet') ?: $r->input('sonuc') ?: 'bilgi');
        if ($oid) {
            $o = DB::table('santral_oturumlari')->where('id', $oid)->first();
            $upd = ['durum' => 'kapandi', 'updated_at' => now()];
            if ($o && !$o->sonuc) $upd['sonuc'] = $sonuc;
            // AI'nin hatta kaldigi sure (aktarmada kopru bitir'i aktarmadan ONCE cagirir -> operator suresi dahil degil)
            if ($o && $this->kullanimKolonlari() && empty($o->sure_sn)) {
                $upd['bitis_at'] = now();
                $upd['sure_sn'] = max(0, min(3600, time() - strtotime($o->created_at)));
            }   // onceki aksiyonu (randevu/aktar) ezme
            // KOPRU OLCUMU (net maliyet): Google'a giden karakter + STT'ye akan saniye + kullanilan ses
            if ($o && $this->kullanimKolonlari()) {
                if (is_numeric($r->input('tts_ucretli_karakter'))) $upd['tts_ucretli_karakter'] = max(0, min(4000000, (int) $r->input('tts_ucretli_karakter')));
                if (is_numeric($r->input('stt_sn'))) $upd['stt_sn'] = round(max(0, min(36000, (float) $r->input('stt_sn'))), 1);
                if ($ses = SantralPaket::sesGecerli($r->input('tts_ses'))) $upd['tts_ses'] = $ses;
                foreach (['tts_onbellek_karakter', 'tts_soylenen_karakter'] as $alan) {
                    if (is_numeric($r->input($alan))) $upd[$alan] = max(0, min(4000000, (int) $r->input($alan)));
                }
                // Islem islem seslendirme isareti (y=yeni uretim / o=onbellek, calindi mi) — denetim icin
                $detay = json_decode((string) $r->input('tts_detay'), true);
                if (is_array($detay)) {
                    $temiz = [];
                    foreach (array_slice($detay, 0, 400) as $d) {
                        if (!is_array($d)) continue;
                        $temiz[] = ['m' => mb_substr((string) ($d['m'] ?? ''), 0, 160), 'k' => (int) ($d['k'] ?? 0),
                            'src' => ($d['src'] ?? '') === 'y' ? 'y' : 'o', 'c' => !empty($d['c']) ? 1 : 0, 'sn' => (float) ($d['sn'] ?? 0)];
                    }
                    $upd['tts_detay'] = json_encode($temiz, JSON_UNESCAPED_UNICODE);
                }
                // Gorusmenin kuru (TCMB doviz satis) -> TL maliyeti o gunun kuruyla sabitlenir.
                // Ag istegi YOK (aktarma bitir'i bekliyor); onbellek cevap gittikten sonra tazelenir.
                $upd['kur'] = \App\Services\DovizKuru::usdHizli();
                app()->terminating(function () { try { \App\Services\DovizKuru::usd(); } catch (\Throwable $e) {} });
            }
            DB::table('santral_oturumlari')->where('id', $oid)->update($upd);
        }
        return $this->j(['ok' => 1]);
    }

    // ---------------------------------------------------------- geri aranacak (yetkiliye ulasilamadi)
    // Kopru aktarmada ~25 sn kimse acmazsa /aktarma-cevapsiz cagirir: asistan geri doner, "not aldım, sizi arayacağız"
    // der, konuyu sorar; musterinin cevabi nota yazilir. Panel > Asistan görüşmeleri'nde "Geri aranacak" olarak durur.

    /** santral_oturumlari geri arama kolonlari (runtime; sunucuda migrate gerekmez). */
    protected function geriAraSemasi(): bool
    {
        if (Cache::get('santral_geri_ara_v1')) return true;
        try {
            if (!Schema::hasTable('santral_oturumlari')) return false;
            $eksik = [];
            foreach (['geri_ara', 'geri_ara_neden', 'geri_ara_not', 'geri_arandi_at', 'geri_arayan'] as $k) {
                if (!Schema::hasColumn('santral_oturumlari', $k)) $eksik[] = $k;
            }
            if ($eksik) {
                Schema::table('santral_oturumlari', function ($t) use ($eksik) {
                    if (in_array('geri_ara', $eksik, true)) $t->tinyInteger('geri_ara')->default(0);          // 0 yok | 1 bekliyor | 2 arandi
                    if (in_array('geri_ara_neden', $eksik, true)) $t->string('geri_ara_neden', 20)->nullable(); // cevapsiz | hedef_yok | mesai_disi
                    if (in_array('geri_ara_not', $eksik, true)) $t->text('geri_ara_not')->nullable();
                    if (in_array('geri_arandi_at', $eksik, true)) $t->timestamp('geri_arandi_at')->nullable();
                    if (in_array('geri_arayan', $eksik, true)) $t->string('geri_arayan', 100)->nullable();
                });
            }
            Cache::forever('santral_geri_ara_v1', 1);
            return true;
        } catch (\Throwable $e) {
            \Log::warning('[SANTRAL-GERI-ARA] sema hatasi: ' . $e->getMessage());
            return false;
        }
    }

    public function aktarmaCevapsiz(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();
        $oid = (int) $r->input('oturum_id');
        $o = DB::table('santral_oturumlari')->where('id', $oid)->first();
        if (!$o) return $this->j(['ok' => 0, 'hata' => 'oturum_yok'], 404);
        $neden = in_array($r->input('neden'), ['cevapsiz', 'hedef_yok'], true) ? $r->input('neden') : 'cevapsiz';
        $this->geriAraKaydet($o, $neden);
        $cevap = $this->geriAraCumlesi($neden, $o->telefon);

        // Gecmis: "sizi bağlıyorum" cumlesinin ardina eklenir (art arda iki asistan mesaji olmasin)
        $g = json_decode($o->gecmis ?: '[]', true) ?: [];
        $n = count($g);
        if ($n && ($g[$n - 1]['role'] ?? '') === 'assistant') {
            $g[$n - 1]['content'] = trim(($g[$n - 1]['content'] ?? '') . ' ' . $cevap);
            $g[$n - 1]['geri_ara_soru'] = 1;
        } else {
            $g[] = ['role' => 'assistant', 'content' => $cevap, 'geri_ara_soru' => 1];
        }
        DB::table('santral_oturumlari')->where('id', $oid)->update(['gecmis' => json_encode($g, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
        \Log::info('[SANTRAL-GERI-ARA] yetkiliye ulasilamadi, asistan geri dondu', ['oturum' => $oid, 'salon' => $o->sube_id, 'neden' => $neden]);
        return $this->j(['ok' => 1, 'cevap' => $cevap]);
    }

    /** Aktarma yerine not alinacak mi: 'mesai_disi' | 'cevapsiz' (bu aramada zaten ulasilamadi) | null */
    protected function aktarmaYerineNot($o): ?string
    {
        if ((int) ($o->geri_ara ?? 0) === 1) return 'cevapsiz';
        return $this->mesaiDisi((int) $o->sube_id) ? 'mesai_disi' : null;
    }

    /** Salon calisma saatleri tanimliysa ve su an kapaliysa true (tanimsizsa aktarma denenir). */
    protected function mesaiDisi(int $salonId): bool
    {
        try {
            $simdi = now('Europe/Istanbul');
            $r = \App\SalonCalismaSaatleri::where('salon_id', $salonId)->where('haftanin_gunu', (int) $simdi->format('N'))->first();
            if (!$r) return false;
            if ((int) $r->calisiyor !== 1) return true;
            $b = substr((string) $r->baslangic_saati, 0, 5);
            $e = substr((string) $r->bitis_saati, 0, 5);
            if (!$b || !$e || $b === $e) return false;
            $s = $simdi->format('H:i');
            return $s < $b || $s >= $e;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function geriAraCumlesi(string $neden, $telefon): string
    {
        $bas = $neden === 'mesai_disi'
            ? 'Şu an mesai saatlerimiz dışındayız, yetkili arkadaşlarımıza ulaşamıyorum. Aradığınızı not aldım, mesai başladığında sizi '
            : 'Efendim, yetkili arkadaşlarımız şu an diğer misafirlerimizle ilgileniyor. Aradığınızı not aldım, en kısa sürede sizi ';
        return trim((string) $telefon) !== ''
            ? $bas . 'bu numaradan geri arayacaklar. İsterseniz konuyu kısaca söyleyin, notuma ekleyeyim.'
            : $bas . 'geri arayacaklar. Telefon numaranızı ve konuyu kısaca söyler misiniz?';
    }

    /** Oturumu "geri aranacak" isaretle; ilk kez isaretleniyorsa isletmeye panel bildirimi + push. */
    protected function geriAraKaydet($o, string $neden): void
    {
        if (!$this->geriAraSemasi()) return;
        $ilk = (int) ($o->geri_ara ?? 0) !== 1;
        DB::table('santral_oturumlari')->where('id', $o->id)->update(['sonuc' => 'geri_ara', 'geri_ara' => 1, 'geri_ara_neden' => $neden, 'updated_at' => now()]);
        $o->geri_ara = 1;
        if (!$ilk) return;
        $salonId = (int) $o->sube_id;
        $kim = $this->musteriAdi($salonId, $o->telefon) ?: ($o->telefon ? '0' . substr(preg_replace('/\D/', '', $o->telefon), -10) : 'Gizli numaralı bir müşteri');
        $mesaj = "📞 Geri aranacak: {$kim} aradı, " . ($neden === 'mesai_disi' ? 'mesai dışıydı' : 'yetkiliye ulaşılamadı')
            . '. Asistan not aldı; Asistan Görüşmeleri\'nden bakabilirsiniz.';
        try {
            $b = new \App\Bildirimler();
            $b->aciklama = $mesaj;
            $b->salon_id = $salonId;
            $b->url = '/isletmeyonetim/santral?sube=' . $salonId . '#asistan-gorusmeleri';
            $b->tarih_saat = date('Y-m-d H:i:s');
            $b->okundu = false;
            $b->save();
        } catch (\Throwable $e) {
            \Log::warning('[SANTRAL-GERI-ARA] panel bildirimi yazilamadi: ' . $e->getMessage());
        }
        try {
            $yoneticiler = \App\Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $salonId)->where('model_has_roles.role_id', '<', 5)
                ->pluck('salon_personelleri.id')->toArray();
            foreach ($yoneticiler as $pid) {
                \App\Services\NotificationService::toStaff((int) $pid, $salonId)->type(\App\Services\NotificationTypes::SYSTEM_ANNOUNCEMENT)
                    ->title('Geri aranacak müşteri')->body($mesaj)
                    // Mobil: tiklaninca Asistanim > Asistan Gorusmeleri (eski uygulama surumu yok sayar -> Bildirimler)
                    ->deepLink('assistant_calls', ['salon_id' => $salonId, 'oturum_id' => (int) $o->id])->send();
            }
        } catch (\Throwable $e) {
            \Log::warning('[SANTRAL-GERI-ARA] push gonderilemedi: ' . $e->getMessage());
        }
    }

    protected function musteriAdi(int $salonId, $telefon): ?string
    {
        $d = preg_replace('/\D/', '', (string) $telefon);
        if (strlen($d) < 10) return null;
        try {
            return DB::table('users')->join('musteri_portfoy', 'musteri_portfoy.user_id', '=', 'users.id')
                ->where('musteri_portfoy.salon_id', $salonId)->where('users.cep_telefon', substr($d, -10))->value('users.name') ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Asistan "konuyu söyleyin, notuma ekleyeyim" dediyse musterinin cevabi: not -> kaydet + veda.
     * "Yok, teşekkürler" -> notsuz veda; "Evet" -> "Buyurun, dinliyorum"; randevu/iptal konusu -> normal akis (null).
     */
    protected function geriAraNotTuru(int $oid, string $metin, array $gecmis): ?array
    {
        $son = null;
        for ($i = count($gecmis) - 1; $i >= 0; $i--) {
            if (($gecmis[$i]['role'] ?? '') === 'assistant') { $son = $gecmis[$i]; break; }
        }
        if (!$son || empty($son['geri_ara_soru'])) return null;
        $m = trim($metin);
        $kucuk = mb_strtolower($m, 'UTF-8');
        if ($m === '' || preg_match('/randevu|iptal|değiştir|ertele|kampanya/u', $kucuk)) return null;
        $kelimeler = preg_split('/[^\p{L}]+/u', $kucuk, -1, PREG_SPLIT_NO_EMPTY);
        $ret = ['hayır', 'yok', 'gerek', 'istemiyorum', 'teşekkürler', 'teşekkür', 'ederim', 'sağ', 'sağol', 'olun', 'tamam', 'tamamdır',
            'peki', 'iyi', 'günler', 'akşamlar', 'görüşürüz', 'anladım', 'kolay', 'gelsin', 'hoşça', 'kalın'];
        $evet = ['evet', 'tabii', 'tabi', 'olur', 'söyleyeyim', 'şöyle', 'şey'];
        if ($kelimeler && !array_diff($kelimeler, $evet)) {
            return ['cevap' => 'Buyurun, sizi dinliyorum.', 'aksiyon' => null, 'veri' => [], 'bitir' => false, 'geri_ara_soru' => 1];
        }
        $not = ($kelimeler && !array_diff($kelimeler, $ret)) ? '' : $m;
        if ($not !== '' && $this->geriAraSemasi()) {
            $eski = (string) DB::table('santral_oturumlari')->where('id', $oid)->value('geri_ara_not');
            DB::table('santral_oturumlari')->where('id', $oid)->update(['geri_ara_not' => mb_substr(trim($eski . ' ' . $not), 0, 2000)]);
        }
        \Log::info('[SANTRAL-GERI-ARA] not', ['oturum' => $oid, 'not' => $not]);
        $cevap = $not !== ''
            ? 'Notunuzu ilettim, arkadaşlarımız en kısa sürede sizi arayacak. İyi günler dilerim.'
            : 'Tabii, arkadaşlarımız en kısa sürede sizi arayacak. İyi günler dilerim.';
        return ['cevap' => $cevap, 'aksiyon' => 'veda', 'veri' => [], 'bitir' => true];
    }

    // ---------------------------------------------------------- /aktarma-hedef
    // Operatore aktarma hedefi. Basit surum: env ile tek hedef (SANTRAL_AKTARMA_DIAL).
    // Ileride salon bazli panele cekilebilir.
    public function aktarmaHedef(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        // Hedef = ILGILI SALONUN operator_kanali kuyrugu (dialplan [operator-bagla] ile ayni:
        // Dial(Local/<operator_kanali>@from-queue/n)). Salonda tanimli degilse env yedegi.
        $salonId = (int) $r->input('sube_id');
        $kanal = $salonId ? preg_replace('/[^0-9]/', '', (string) DB::table('salonlar')->where('id', $salonId)->value('operator_kanali')) : '';
        $dial = $kanal !== '' ? "Local/{$kanal}@from-queue/n" : (string) env('SANTRAL_AKTARMA_DIAL', '');
        $aktif = $dial !== '' ? 1 : 0;
        return $this->j([
            'ok' => 1,
            'aktif' => $aktif,
            'strateji' => 'hepsi',
            'hedefler' => $aktif ? [$dial] : [],
            'dial' => $dial,
            'zil' => (int) env('SANTRAL_AKTARMA_ZIL', 30),
        ]);
    }

    // ---------------------------------------------------------- /kayit-yukle
    public function kayitYukle(Request $r)
    {
        if (!$this->yetkiVar($r)) return $this->j(['ok' => 0, 'hata' => 'Yetkisiz santral isteği'], 401);
        $this->tabloGaranti();

        $oturum = (int) $r->input('oturum_id');
        $tur = in_array($r->input('tur'), ['ai', 'aktarma'], true) ? $r->input('tur') : 'ai';
        $raw = file_get_contents('php://input');
        if (!$raw || strlen($raw) < 200) return $this->j(['ok' => 0, 'hata' => 'bos ses']);

        $dir = storage_path('app/santral_ses');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $ad = 'santral_' . ($oturum ?: 'x') . '_' . $tur . '_' . date('YmdHis') . '_' . substr(md5((string) mt_rand()), 0, 6) . '.wav';
        @file_put_contents($dir . '/' . $ad, $raw);

        $sube = (int) $r->input('sube_id') ?: (DB::table('santral_oturumlari')->where('id', $oturum)->value('sube_id') ?: null);
        DB::table('santral_ses_kayit')->insert([
            'oturum_id' => $oturum ?: null, 'sube_id' => $sube, 'tur' => $tur,
            'dosya' => $ad, 'boyut' => strlen($raw), 'created_at' => now(),
        ]);
        return $this->j(['ok' => 1, 'dosya' => $ad]);
    }

    // ---------------------------------------------------------- geri-arama (stub)
    // Otomatik geri arama su an KAPALI (Node GERI_ARAMA=0). Kopru hata almasin diye bos liste.
    public function geriAramaBekleyen(Request $r)
    {
        return response()->json(['ok' => 1, 'liste' => []], 200,
            ['Cache-Control' => 'no-store'], JSON_UNESCAPED_UNICODE);
    }

    public function geriAramaDurum(Request $r)
    {
        return $this->j(['ok' => 1]);
    }
}
