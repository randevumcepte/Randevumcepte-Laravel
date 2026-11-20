<?php

namespace App\Http\Controllers;

use App\Salonlar;
use App\Services\SantralPaket;
use App\Services\WaAsistan;
use App\Services\WaAsistanRapor;
use App\Services\WaAsistanVeri;
use App\Services\WaAvatar;
use App\Services\WaBedavaKatman;
use App\Services\WaDenetci;
use App\Services\WaGeriKazanim;
use App\Services\WaGorselDanisman;
use App\Services\WaTalep;
use App\Services\WaPaket;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Isletme paneli > WhatsApp Asistanı: Kazandirdiklari raporu, sohbetler (devral / asistana geri ver),
 * kacan & geri kazanilan musteriler, fotograf fiyat kurallari, ogrenilen cevaplar, ayarlar.
 * Yalniz rol 1, 2, 3 (kullanici_rolu / model_has_roles.role_id) — isletme istegi 2026-11-19. Web + mobil ayni kapi.
 */
class WaAsistanPanelController extends StoreAdminController
{
    /** WhatsApp Asistani sayfasini gorebilen roller (web + mobil). */
    const IZINLI_ROLLER = [1, 2, 3];

    protected function salon(Request $request): ?int
    {
        $sid = $this->asistanGorusmeSalon($request);
        if (!$sid) return null;
        $yetkili = $this->asistanGorusmeYetkili();
        if (!$yetkili || !in_array((int) self::kullaniciRolu($sid, $yetkili->id), self::IZINLI_ROLLER, true)) return null;
        return $sid;
    }

    protected function yok()
    {
        return response()->json(['ok' => 0, 'hata' => 'Bu sayfayı görüntüleme yetkiniz yok.'], 403);
    }

    public function sayfa(Request $request)
    {
        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->where('aktif', 1)->pluck('salon_id')->toArray();
        $isletme = Salonlar::where('id', self::mevcutsube($request))->first();
        if (!$isletme || !in_array(self::mevcutsube($request), $isletmeler)) return view('isletmeadmin.yetkisizerisim');
        if (str_contains(self::lisans_sure_kontrol($request), '-')) return view('isletmeadmin.lisanssurebitti', ['isletme' => $isletme]);
        if (count($isletmeler) > 1 && !isset($_GET['sube'])) return view('isletmeadmin.isletmesec', ['isletmeler' => $isletmeler, 'isletme' => $isletme]);
        $rol = self::kullaniciRolu($isletme->id, Auth::guard('isletmeyonetim')->user()->id);
        if (!in_array((int) $rol, self::IZINLI_ROLLER, true)) return view('isletmeadmin.yetkisizerisim');

        WaAsistanVeri::semaGaranti();
        WaGeriKazanim::semaGaranti();
        WaGorselDanisman::semaGaranti();
        $hizmetler = self::hizmetListesi((int) $isletme->id);
        $ayar = WaAsistanVeri::ayarlar($isletme->id);

        return view('isletmeadmin.wa-asistan', [
            'kullaniciRolu' => $rol, 'bildirimler' => self::bildirimgetir($request), 'paketler' => self::paket_liste_getir('', true, $request),
            'sayfa_baslik' => 'WhatsApp Asistanı', 'pageindex' => 652, 'isletme' => $isletme,
            'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request), 'urun_drop' => self::urundropliste($request),
            'hizmet_drop' => self::hizmetdropliste($request), 'personel_drop' => self::personeldropliste($request, []),
            'yetkiliolunanisletmeler' => $isletmeler,
            'waAcik' => WaAsistan::acikMi($isletme->id),
            'waAyar' => $ayar,
            'waPaket' => WaPaket::durum($isletme->id),
            'waPaketSatir' => WaPaket::paket($isletme->id),
            'waSektorler' => WaGorselDanisman::sektorler(),
            'waKurallar' => WaGorselDanisman::kurallar($isletme->id, (string) $ayar['gorsel_sektor']),
            'waHizmetler' => $hizmetler,
            // Kural girilmemis ana islemler hizmet listesinden otomatik eslesir (asistan da ayni eslesmeyi kullanir)
            'waOtoKurallar' => array_map(function ($s) use ($hizmetler) {
                return WaGorselDanisman::otomatikKurallar($hizmetler->map(function ($h) { return (array) $h; })->all(), $s);
            }, array_combine(array_keys(WaGorselDanisman::sektorler()), array_keys(WaGorselDanisman::sektorler()))),
        ]);
    }

    protected static function hizmetListesi(int $sid)
    {
        return DB::table('salon_sunulan_hizmetler as sh')->join('hizmetler as h', 'h.id', '=', 'sh.hizmet_id')
            ->where('sh.salon_id', $sid)->where('sh.aktif', 1)->orderBy('h.hizmet_adi')->get(['sh.hizmet_id as id', 'h.hizmet_adi as ad', \Illuminate\Support\Facades\DB::raw('NULLIF(sh.baslangic_fiyat, 0) as fiyat')]);
    }

    /** Genel bakis: secilen donem raporu + onceki doneme gore trend + gunluk seriler (mini grafikler) + paket + asistan durumu. */
    public function ozet(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $donem = (string) $request->input('donem', '30g');
        [$bas, $bit, $oBas, $oBit] = WaAsistanRapor::donemAraligi($donem);
        $v = WaAsistanRapor::veriYukle($sid, $bas, $bit);
        $paketSatir = WaPaket::paket($sid);
        $o = WaAsistanRapor::ozetHesapla($v, $paketSatir);
        $once = WaAsistanRapor::ozetHesapla(WaAsistanRapor::veriYukle($sid, $oBas, $oBit), $paketSatir);
        $t = function ($a, $b) { return WaAsistanRapor::trend($a, $b); };
        $trend = [
            'mesaj' => $t($o['sohbet']['gelen_mesaj'], $once['sohbet']['gelen_mesaj']),
            'randevu' => $t($o['randevu']['adet'] + $o['geri_kazanim']['randevu'], $once['randevu']['adet'] + $once['geri_kazanim']['randevu']),
            'yeni' => $t($o['randevu']['yeni_musteri'], $once['randevu']['yeni_musteri']),
            'memnuniyet' => ($o['sohbet']['memnuniyet_orani'] !== null && $once['sohbet']['memnuniyet_orani'] !== null)
                ? $o['sohbet']['memnuniyet_orani'] - $once['sohbet']['memnuniyet_orani'] : null, // puan farki
            'sure' => $t($o['cevap']['medyan_sure_sn'], $once['cevap']['medyan_sure_sn']),
            'kazanc' => $t($o['kazanc']['beklenen'], $once['kazanc']['beklenen']),
        ];
        $ayar = WaAsistanVeri::ayarlar($sid);
        $kuralVar = (bool) WaGorselDanisman::kurallar($sid, (string) $ayar['gorsel_sektor'])
            || (bool) WaGorselDanisman::otomatikKurallar(self::hizmetListesi($sid)->map(function ($h) { return (array) $h; })->all(), (string) $ayar['gorsel_sektor']);
        // "Sizden dönüş bekleyen" = acik talepler (WaTalep); asistan artik susmuyor, eski sessiz-aktar sayimi yerine
        $bekleyen = WaTalep::bekleyenler($sid);
        $o['aktar_bekleyen'] = array_map(function ($t) { return ['oturum_id' => $t['oturum_id'], 'ad' => $t['ad'], 'telefon' => $t['telefon'], 'tarih' => $t['tarih'], 'konu' => $t['konu_ad'], 'sure' => $t['sure']]; }, $bekleyen);
        $o['sohbet']['aktar_bekleyen'] = count($bekleyen);
        return response()->json(['ok' => 1, 'donem' => $donem, 'ozet' => $o, 'trend' => $trend,
            'seri' => WaAsistanRapor::seri($v, $bas, $bit, 'gun'),
            'paket' => WaPaket::hesapla($paketSatir, WaPaket::kullanilan($sid)),
            'asistan' => ['acik' => WaAsistan::acikMi($sid), 'fiyat' => (int) $ayar['fiyat_soyle'], 'gorsel' => (int) $ayar['gorsel_fiyat'] && $kuralVar,
                'geri_kazanim' => (int) $ayar['geri_kazanim'], 'memnuniyet' => (int) $ayar['memnuniyet_sor']]]);
    }

    /** Performans grafigi: hafta (7 gun) | ay (30 gun) | yil (12 ay) -> randevu + mesaj serileri */
    public function grafik(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $g = (string) $request->input('g', 'ay');
        if ($g === 'yil') {
            $bas = date('Y-m-01 00:00:00', strtotime(date('Y-m-01') . ' -11 months'));
            $bit = date('Y-m-01 00:00:00', strtotime(date('Y-m-01') . ' +1 month'));
            $birim = 'ay';
        } else {
            [$bas, $bit] = WaAsistanRapor::donemAraligi($g === 'hafta' ? '7g' : '30g');
            $birim = 'gun';
        }
        $s = WaAsistanRapor::seri(WaAsistanRapor::veriYukle($sid, $bas, $bit), $bas, $bit, $birim);
        return response()->json(['ok' => 1, 'birim' => $birim, 'etiket' => $s['etiket'], 'randevu' => $s['randevu'], 'mesaj' => $s['mesaj']]);
    }

    public function sohbetler(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        WaAsistanVeri::semaGaranti();
        $bas = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('baslangic')) ? $request->input('baslangic') : date('Y-m-d', strtotime('-6 days'));
        $bit = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('bitis')) ? $request->input('bitis') : date('Y-m-d');
        $q = DB::table('wa_asistan_oturumlari')->where('isletme_id', $sid)->orderByDesc('son_mesaj_at')->limit(300);
        $ara = trim((string) $request->input('q'));
        if ($ara !== '') {
            // Ad/telefon aramasi TUM tarihlerde (eski sohbet "gorunmuyor" sanilmasin); telefon 0/90 ile de bulunur
            $rak = preg_replace('/\D/', '', $ara);
            $rak = strlen($rak) >= 7 ? substr($rak, -10) : '';
            $q->where(function ($w) use ($ara, $rak) {
                $w->where('ad', 'like', "%$ara%")->orWhere('telefon', 'like', "%$ara%")->orWhere('musteri_tel', 'like', "%$ara%");
                if ($rak !== '') $w->orWhere('telefon', 'like', "%$rak%")->orWhere('musteri_tel', 'like', "%$rak%")->orWhere('anahtar', 'like', "%$rak%");
            });
        } else {
            $q->whereBetween('son_mesaj_at', [$bas . ' 00:00:00', $bit . ' 23:59:59']);
        }
        $liste = [];
        $rows = $q->get();
        // Profil fotografi: sistemde yuklu musteri fotografi; yoksa panel WhatsApp fotografini /avatar/{id}'den ister
        $fotolar = WaAvatar::sistemFotolari($sid, $rows->map(function ($o) { return $o->musteri_tel ?: $o->telefon; })->all());
        $talepler = WaTalep::oturumlarIcin($rows->pluck('id')->map(function ($x) { return (int) $x; })->all());
        foreach ($rows as $o) {
            $t10 = substr(preg_replace('/\D/', '', (string) ($o->musteri_tel ?: $o->telefon)), -10);
            $g = json_decode($o->gecmis ?: '[]', true) ?: [];
            $sonMus = '';
            for ($i = count($g) - 1; $i >= 0; $i--) if (($g[$i]['role'] ?? '') === 'user') { $sonMus = (string) $g[$i]['content']; break; }
            $liste[] = [
                'id' => (int) $o->id, 'ad' => $o->ad, 'telefon' => $o->musteri_tel ?: $o->telefon, 'son_mesaj' => mb_substr($sonMus, 0, 120),
                'tarih' => $o->son_mesaj_at, 'mesaj' => (int) $o->mesaj_sayisi, 'cevap' => (int) $o->cevap_sayisi,
                'sonuc' => $o->sonuc, 'randevu_id' => $o->randevu_id, 'durum' => self::durumEtiket($o, $talepler[(int) $o->id] ?? null),
                'fiyat' => (int) $o->fiyat_verildi, 'gorsel' => (int) $o->gorsel_sayisi, 'memnuniyet' => $o->memnuniyet === null ? null : (int) $o->memnuniyet,
                'geri_kazanim' => $o->geri_kazanim_id ? 1 : 0, 'devralindi' => self::devralindiMi($o),
                'foto' => $fotolar[$t10] ?? null,
                // mobil "Bekleyen" sekmesi: isletmeye sorulan konu bekliyor ya da musteri mesaji cevapsiz
                'bekleyen' => (($talepler[(int) $o->id]['durum'] ?? '') === 'bekliyor' || WaAsistan::yarimMi($g) === 'cevapsiz') ? 1 : 0,
                'talep' => isset($talepler[(int) $o->id]) && $talepler[(int) $o->id]['durum'] === 'bekliyor' ? ($talepler[(int) $o->id]['ozet'] ?: $talepler[(int) $o->id]['konu_ad']) : null,
            ];
        }
        return response()->json(['ok' => 1, 'liste' => $liste]);
    }

    /** Musterinin WhatsApp profil fotografi (yalniz bu isletmenin sohbeti; yoksa 404 -> panel bas harf gosterir). */
    public function avatar(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) abort(403);
        $o = DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)->first(['salon_id', 'jid', 'telefon']);
        if (!$o) abort(404);
        $yol = WaAvatar::dosya((int) $o->salon_id, $o->jid, $o->telefon);
        if (!$yol) abort(404);
        return response()->file($yol, ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'private, max-age=86400']);
    }

    public function sohbet(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $o = DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)->first();
        if (!$o) return response()->json(['ok' => 0, 'hata' => 'Sohbet bulunamadı.'], 404);
        $satirlar = [];
        $gecmis = json_decode($o->gecmis ?: '[]', true) ?: [];
        $saatler = self::mesajSaatleri($o, $gecmis);
        foreach ($gecmis as $i => $m) {
            $rol = ($m['role'] ?? '') === 'user' ? 'musteri' : (!empty($m['personel']) ? 'personel' : 'asistan');
            $satirlar[] = ['rol' => $rol, 'metin' => (string) ($m['content'] ?? ''), 'geri_kazanim' => !empty($m['geri_kazanim']),
                'hatirlatma' => !empty($m['hatirlatma']), 'at' => $saatler[$i] ?? null, 'yazan' => $rol === 'personel' ? WaAsistan::yazanKisa($m['yazan'] ?? null) : null];
        }
        $gorseller = [];
        if (WaGorselDanisman::semaGaranti()) {
            foreach (DB::table('wa_gorsel_analizler')->where('oturum_id', $o->id)->where('isletme_id', $sid)->orderBy('id')->get() as $g) {
                $an = json_decode($g->analiz ?: 'null', true);
                $gorseller[] = ['id' => (int) $g->id, 'dosya' => (bool) $g->dosya, 'durum' => $g->durum, 'toplam' => $g->toplam !== null ? (float) $g->toplam : null,
                    'tarif' => $an['aciklama'] ?? null, 'onbellek' => (int) $g->onbellek];
            }
        }
        $talepler = WaTalep::oturumTalepleri((int) $o->id);
        [$giden, $onceki] = self::numaraGecmisi($o, $sid);
        return response()->json(['ok' => 1, 'sohbet' => [
            'talepler' => $talepler, 'giden' => $giden, 'onceki' => $onceki,
            'yarim' => WaAsistan::yarimMi(json_decode($o->gecmis ?: '[]', true) ?: []),
            'id' => (int) $o->id, 'ad' => $o->ad, 'telefon' => $o->musteri_tel ?: $o->telefon, 'durum' => self::durumEtiket($o, $talepler ? end($talepler) : null),
            'devralindi' => self::devralindiMi($o), 'randevu_id' => $o->randevu_id, 'satirlar' => $satirlar, 'gorseller' => $gorseller,
            'asistan_durum' => self::asistanDurum($o, $gecmis),
            'maliyet_usd' => (float) $o->maliyet_usd,
        ]]);
    }

    /** Panelden devral: asistan bu sohbette (asistana geri verilene kadar, en fazla 24 saat) susar. */
    public function devral(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $n = DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)
            ->update(['sessiz_bitis' => now()->addHours(24), 'sessiz_neden' => 'devral', 'updated_at' => now()]);
        return response()->json(['ok' => $n ? 1 : 0]);
    }

    public function geriVer(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $n = DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)
            ->update(['sessiz_bitis' => null, 'sessiz_neden' => null, 'insan_devraldi' => 0, 'updated_at' => now()]);
        return response()->json(['ok' => $n ? 1 : 0]);
    }

    public function geriKazanimListe(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!WaGeriKazanim::semaGaranti()) return response()->json(['ok' => 1, 'liste' => []]);
        [$bas, $bit] = WaAsistanRapor::donemAraligi((string) $request->input('donem', '30g'));
        $liste = DB::table(WaGeriKazanim::TABLO)->where('isletme_id', $sid)->where('gonderildi_at', '>=', $bas)->where('gonderildi_at', '<', $bit)
            ->orderByDesc('id')->limit(300)->get(['id', 'yeni_oturum_id', 'ad', 'telefon', 'hizmet_ad', 'neden', 'durum', 'gonderildi_at', 'cevap_at', 'randevu_tarih', 'tutar', 'geldi', 'mesaj']);
        return response()->json(['ok' => 1, 'liste' => $liste]);
    }

    public function ayarKaydet(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $alanlar = ['fiyat_soyle', 'geri_kazanim', 'gk_bekle_saat', 'gk_kampanya', 'memnuniyet_sor', 'gorsel_fiyat', 'gorsel_sektor', 'gorsel_not', 'denetci', 'denetci_oto', 'reklam'];
        $veri = [];
        foreach ($alanlar as $a) if ($request->has($a)) $veri[$a] = $request->input($a);
        if (!self::sistemYoneticisiMi()) unset($veri['denetci'], $veri['denetci_oto'], $veri['reklam']); // denetci + reklam ozelligi bizim ayarimiz
        WaAsistanVeri::ayarKaydet($sid, $veri, optional($this->asistanGorusmeYetkili())->name);
        return response()->json(['ok' => 1, 'ayar' => WaAsistanVeri::ayarlar($sid)]);
    }

    public function kuralKaydet(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $sektor = (string) $request->input('sektor', 'tirnak');
        if (!isset(WaGorselDanisman::sektorler()[$sektor])) return response()->json(['ok' => 0, 'hata' => 'Geçersiz sektör.'], 422);
        $gecerli = DB::table('salon_sunulan_hizmetler')->where('salon_id', $sid)->pluck('hizmet_id')->map(function ($x) { return (int) $x; })->all();
        $kurallar = [];
        foreach ((array) $request->input('kurallar', []) as $anahtar => $r) {
            $hid = !empty($r['hizmet_id']) ? (int) $r['hizmet_id'] : null;
            if ($hid && !in_array($hid, $gecerli, true)) $hid = null; // baska salonun hizmeti olamaz
            $kurallar[$anahtar] = ['hizmet_id' => $hid, 'fiyat' => $r['fiyat'] ?? null];
        }
        WaGorselDanisman::kurallariKaydet($sid, $sektor, $kurallar);
        WaAsistanVeri::ayarKaydet($sid, ['gorsel_sektor' => $sektor], optional($this->asistanGorusmeYetkili())->name);
        return response()->json(['ok' => 1, 'kurallar' => WaGorselDanisman::kurallar($sid, $sektor)]);
    }

    /** Fotograf (yalniz bu isletmenin sohbetindeki). */
    public function gorsel(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) abort(403);
        $g = DB::table('wa_gorsel_analizler')->where('id', (int) $id)->where('isletme_id', $sid)->first();
        if (!$g || !$g->dosya) abort(404);
        $yol = storage_path('app/' . $g->dosya);
        if (!is_file($yol)) abort(404);
        return response()->file($yol, ['Content-Type' => $g->mime ?: 'image/jpeg', 'Cache-Control' => 'private, max-age=3600']);
    }

    public function ogrenilenler(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        WaAsistanVeri::semaGaranti();
        $liste = DB::table('wa_asistan_ogrenilen')->where('salon_id', $sid)->where('aktif', 1)->orderByDesc('adet')->limit(300)
            ->get(['id', 'soru', 'cevap', 'adet', 'updated_at']);
        return response()->json(['ok' => 1, 'liste' => $liste]);
    }

    public function ogrenilenSil(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $n = DB::table('wa_asistan_ogrenilen')->where('id', (int) $id)->where('salon_id', $sid)->update(['aktif' => 0, 'updated_at' => now()]);
        return response()->json(['ok' => $n ? 1 : 0]);
    }

    // ------------------------------------------------------------------ Asistan Gelisimi (Gece Denetcisi)
    public function gelisim(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!WaDenetci::semaGaranti()) return response()->json(['ok' => 0, 'hata' => 'Veri tabloları hazırlanamadı.']);
        $biz = self::sistemYoneticisiMi();
        $p = WaDenetci::panel($sid, $biz);
        if (!$biz) {
            // salon yalniz kendi bilgi sorularini gorur; egitim (ders/puan/inceleme) sistem yoneticisinin
            $p = ['biz' => 0, 'hizmetler' => $p['hizmetler'], 'dersler' => array_values(array_filter($p['dersler'], function ($d) { return $d->tur === 'bilgi' && $d->durum === 'oneri'; }))];
        }
        // ifade kutuphanesi sayaci (hangi katman cozuyor) yalniz sistem yoneticisine
        if ($biz) $p['ifade'] = ['salon' => \App\Services\IfadeOgrenme::ozet($sid), 'tum' => \App\Services\IfadeOgrenme::ozet(null)];
        return response()->json(['ok' => 1, 'biz' => $biz ? 1 : 0] + $p);
    }

    /** Asistan egitimi (ortak beyin) yalniz sistem yoneticisine: panelden "salona gir" ile gelen oturum. */
    protected static function sistemYoneticisiMi(): bool
    {
        return session()->has('sysadmin_impersonation_id') || \Illuminate\Support\Facades\Auth::guard('sistemyonetim')->check();
    }

    /**
     * Salonun GERCEK WhatsApp asistani AI maliyeti (ay): Paketler sayfasindaki rakam yalniz gonderilen cevaplardir;
     * burada gonderilemeyen cevaplar, Gece Denetcisi incelemesi, ders testleri (ortak beyin payi) ve fotograf analizi de var.
     * Yalniz sistem yoneticisi. ?sube=236&ay=2026-11
     */
    public function maliyetRaporu(Request $request)
    {
        if (!self::sistemYoneticisiMi()) return $this->yok();
        $sid = (int) $request->get('sube');
        if ($sid <= 0) return response()->json(['ok' => 0, 'hata' => 'sube gerekli']);
        [$ay, $bas, $bit] = \App\Services\SantralPaket::donem($request->get('ay'));
        // ?bas=2026-11-19 (&bit=2026-11-20): gun araligi (bit dahil); verilmezse ay
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->get('bas'))) {
            $bas = $request->get('bas') . ' 00:00:00';
            $bit = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->get('bit')) ? date('Y-m-d 00:00:00', strtotime($request->get('bit') . ' +1 day')) : date('Y-m-d H:i:s', time() + 60);
            $ay = substr($bas, 0, 10) . ' → ' . date('Y-m-d H:i', strtotime($bit) - 60);
        }
        $kur = \App\Services\SantralPaket::kur();
        $ara = function ($q) use ($bas, $bit) { return $q->where('created_at', '>=', $bas)->where('created_at', '<', $bit); };
        $guvenli = function (callable $f, $vars = null) { try { return $f(); } catch (\Throwable $e) { return $vars; } };
        $tl = function ($usd) use ($kur) { return round((float) $usd * $kur, 2); };

        // A) Gonderilen cevaplar (Paketler sayfasindaki rakam)
        $c = $guvenli(function () use ($sid, $ara) {
            return $ara(DB::table('wa_asistan_cevaplar')->where('salon_id', $sid))
                ->selectRaw('COUNT(*) n, SUM(ai) ai, SUM(maliyet_usd) usd')->first();
        });
        // B) Sohbetlerdeki toplam Claude harcamasi (gonderilemeyen cevaplar dahil)
        $o = $guvenli(function () use ($sid, $ara) {
            return $ara(DB::table('wa_asistan_oturumlari')->where('salon_id', $sid))
                ->selectRaw('COUNT(*) n, SUM(maliyet_usd) usd, SUM(girdi_token) gi, SUM(cikti_token) ci, SUM(cache_okuma_token) co, SUM(cache_yazma_token) cy')->first();
        });
        // C) Fotograf analizi (A'nin icinde; bilgi icin ayri)
        $g = $guvenli(function () use ($sid, $ara) {
            return $ara(DB::table('wa_gorsel_analizler')->where('salon_id', $sid))->selectRaw('COUNT(*) n, SUM(onbellek) onb, SUM(maliyet_usd) usd')->first();
        });
        // D) Gece Denetcisi: bu salonun sohbet incelemeleri (Sonnet, batch)
        $d = $guvenli(function () use ($sid, $ara) {
            return $ara(DB::table('wa_denetim')->where('isletme_id', $sid))->selectRaw('COUNT(*) n, SUM(maliyet_usd) usd, COUNT(DISTINCT DATE(created_at)) gun')->first();
        });
        // E) Ortak beyin ders testleri: toplam maliyet, salonun denetim payina gore bolunur
        $testUsd = 0.0;
        foreach ($guvenli(function () use ($ara) { return $ara(DB::table('wa_asistan_dersler')->where('isletme_id', \App\Services\WaDenetci::ORTAK))->pluck('test'); }, []) as $t) {
            $testUsd += (float) ((json_decode((string) $t, true) ?: [])['maliyet_usd'] ?? 0);
        }
        $tumDenetim = (int) $guvenli(function () use ($ara) { return $ara(DB::table('wa_denetim'))->count(); }, 0);
        $pay = $tumDenetim > 0 ? (int) ($d->n ?? 0) / $tumDenetim : 0;
        // F) Ders uretimi (salon raporu + ortak ders): maliyeti KAYDEDILMIYORDU -> tahmin (gun basina 1 Sonnet cagrisi + ortak payi)
        $dersTahminUsd = (int) ($d->gun ?? 0) * 0.05 * (1 + $pay);

        $cevapUsd = (float) ($c->usd ?? 0);
        $sohbetUsd = max($cevapUsd, (float) ($o->usd ?? 0));
        $denetimUsd = (float) ($d->usd ?? 0);
        $testPayUsd = $testUsd * $pay;
        $toplamUsd = $sohbetUsd + $denetimUsd + $testPayUsd + $dersTahminUsd;
        $ai = (int) ($c->ai ?? 0);

        return response()->json([
            'ok' => 1, 'salon' => $sid, 'ay' => $ay, 'kur' => round($kur, 4),
            'kalemler' => [
                ['kalem' => 'Gönderilen cevaplar (Paketler sayfasındaki rakam)', 'adet' => (int) ($c->n ?? 0), 'ai_cevap' => $ai, 'usd' => round($cevapUsd, 4), 'tl' => $tl($cevapUsd)],
                ['kalem' => 'Sohbetlerin tamamı (gönderilemeyenler dahil)', 'sohbet' => (int) ($o->n ?? 0), 'usd' => round($sohbetUsd, 4), 'tl' => $tl($sohbetUsd),
                    'token' => ['girdi' => (int) ($o->gi ?? 0), 'cikti' => (int) ($o->ci ?? 0), 'onbellek_okuma' => (int) ($o->co ?? 0), 'onbellek_yazma' => (int) ($o->cy ?? 0)]],
                ['kalem' => '  içinde: fotoğraf analizi', 'adet' => (int) ($g->n ?? 0), 'onbellekten' => (int) ($g->onb ?? 0), 'usd' => round((float) ($g->usd ?? 0), 4), 'tl' => $tl($g->usd ?? 0)],
                ['kalem' => 'Gece Denetçisi sohbet incelemesi', 'adet' => (int) ($d->n ?? 0), 'gun' => (int) ($d->gun ?? 0), 'usd' => round($denetimUsd, 4), 'tl' => $tl($denetimUsd)],
                ['kalem' => 'Ortak beyin ders testleri (salon payı %' . round($pay * 100) . ')', 'usd' => round($testPayUsd, 4), 'tl' => $tl($testPayUsd)],
                ['kalem' => 'Ders üretimi (KAYDEDİLMİYORDU — tahmin)', 'usd' => round($dersTahminUsd, 4), 'tl' => $tl($dersTahminUsd)],
            ],
            'toplam_usd' => round($toplamUsd, 4), 'toplam_tl' => $tl($toplamUsd),
            'ai_cevap_basina_tl' => $ai > 0 ? round($toplamUsd * $kur / $ai, 3) : null,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ------------------------------------------------------------------ SALON REKLAMLARI (WaReklam)
    public function reklamlar(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!\App\Services\WaReklam::semaGaranti()) return response()->json(['ok' => 0, 'hata' => 'Reklam tabloları hazırlanamadı.']);
        return response()->json(['ok' => 1, 'acik' => \App\Services\WaReklam::acikMi($sid), 'liste' => \App\Services\WaReklam::liste($sid)]);
    }

    public function reklamKaydet(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!\App\Services\WaReklam::semaGaranti()) return response()->json(['ok' => 0, 'hata' => 'Reklam tabloları hazırlanamadı.']);
        $baslik = mb_substr(trim((string) $request->input('baslik')), 0, 120);
        if ($baslik === '') return response()->json(['ok' => 0, 'hata' => 'Kampanya başlığını yazın.'], 422);
        $gecerli = DB::table('salon_sunulan_hizmetler')->where('salon_id', $sid)->pluck('hizmet_id')->map(function ($x) { return (int) $x; })->all();
        $hiz = array_values(array_intersect(array_map('intval', (array) $request->input('hizmetler', [])), $gecerli));
        if (!$hiz) return response()->json(['ok' => 0, 'hata' => 'Kampanyanın ilgili olduğu en az bir hizmet seçin (asistan reklamı yalnız bu hizmetler sorulunca gönderir).'], 422);
        $tarih = function ($x) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $x) ? (string) $x : null; };
        $veri = ['baslik' => $baslik, 'metin' => mb_substr(trim((string) $request->input('metin')), 0, 700) ?: null,
            'hizmetler' => json_encode($hiz), 'baslangic' => $tarih($request->input('baslangic')), 'bitis' => $tarih($request->input('bitis')),
            'aktif' => $request->input('aktif', 1) ? 1 : 0, 'updated_at' => now()];
        $id = (int) $request->input('id');
        $eski = $id ? DB::table('wa_reklamlar')->where('id', $id)->where('salon_id', $sid)->first() : null;
        if ($id && !$eski) return response()->json(['ok' => 0, 'hata' => 'Reklam bulunamadı.'], 404);
        $token = $eski->token ?? substr(bin2hex(random_bytes(8)), 0, 12);
        $f = $request->file('gorsel');
        if ($f) {
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$f->getMimeType()] ?? null;
            if (!$ext) return response()->json(['ok' => 0, 'hata' => 'Görsel JPG, PNG ya da WEBP olmalı.'], 422);
            if ($f->getSize() > 4 * 1024 * 1024) return response()->json(['ok' => 0, 'hata' => 'Görsel en fazla 4 MB olabilir.'], 422);
            $klasor = "wa_reklam/{$sid}";
            @mkdir(storage_path('app/' . $klasor), 0775, true);
            $ad = $token . '_' . time() . '.' . $ext;
            $f->move(storage_path('app/' . $klasor), $ad);
            if ($eski && $eski->gorsel) @unlink(storage_path('app/' . $eski->gorsel));
            $veri['gorsel'] = $klasor . '/' . $ad;
        }
        if ($eski) DB::table('wa_reklamlar')->where('id', $eski->id)->update($veri);
        else DB::table('wa_reklamlar')->insert($veri + ['salon_id' => $sid, 'token' => $token, 'created_at' => now()]);
        \App\Services\WaReklam::onbellekSil($sid);
        return response()->json(['ok' => 1, 'liste' => \App\Services\WaReklam::liste($sid)]);
    }

    public function reklamSil(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $r = DB::table('wa_reklamlar')->where('id', (int) $id)->where('salon_id', $sid)->first();
        if ($r) {
            if ($r->gorsel) @unlink(storage_path('app/' . $r->gorsel));
            DB::table('wa_reklamlar')->where('id', $r->id)->delete();
            \App\Services\WaReklam::onbellekSil($sid);
        }
        return response()->json(['ok' => 1, 'liste' => \App\Services\WaReklam::liste($sid)]);
    }

    /** Asistanin son isleme hatalari (mesaj geldi, cevap uretilemedi). Yalniz sistem yoneticisi. */
    public function sonHata(Request $request)
    {
        if (!self::sistemYoneticisiMi()) return $this->yok();
        return response()->json(['ok' => 1, 'hatalar' => \Illuminate\Support\Facades\Cache::get('wa_asistan_son_hatalar', []),
            'reklam_kararlari' => \Illuminate\Support\Facades\Cache::get('wa_reklam_kararlar', [])],
            200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /** ORTAK BEYIN davranis dersi: aktif <-> kapali (geri al / yine de uygula). Yalniz sistem yoneticisi; tum salonlari etkiler. */
    public function dersDurum(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!self::sistemYoneticisiMi()) return response()->json(['ok' => 0, 'hata' => 'Bu işlem yalnız sistem yöneticisine açık.'], 403);
        $durum = $request->input('durum') === 'aktif' ? 'aktif' : 'kapali';
        $q = DB::table('wa_asistan_dersler')->where('id', (int) $id)->where('isletme_id', WaDenetci::ORTAK)->where('tur', 'davranis');
        if ($durum === 'aktif' && DB::table('wa_asistan_dersler')->where('isletme_id', WaDenetci::ORTAK)->where('tur', 'davranis')->where('durum', 'aktif')->count() >= WaDenetci::MAX_AKTIF_DERS) {
            return response()->json(['ok' => 0, 'hata' => 'En fazla ' . WaDenetci::MAX_AKTIF_DERS . ' ders aynı anda açık olabilir. Önce birini kapatın.']);
        }
        $n = $q->update(['durum' => $durum, 'updated_at' => now()]);
        \Illuminate\Support\Facades\Cache::forget('wa_dersler_' . WaDenetci::ORTAK);
        return response()->json(['ok' => $n ? 1 : 0]);
    }

    /**
     * Bilgi eksigi cevabi. tip=fiyat: fiyatlar [{hizmet_id, fiyat}] dogrudan hizmet listesine (baslangic_fiyat) yazilir;
     * tip=hizmet_sec: musterinin ifadesi o hizmete baglanir (wa_hizmet_esanlam); digerleri "Öğrenilen Cevaplar"a. bos = yoksay.
     */
    public function bilgiCevapla(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $d = DB::table('wa_asistan_dersler')->where('id', (int) $id)->where('isletme_id', $sid)->whereIn('tur', ['bilgi', 'kod'])->first();
        if (!$d) return response()->json(['ok' => 0]);
        if ($d->tur === 'bilgi' && $request->input('tip') === 'fiyat') {
            $n = 0;
            foreach ((array) $request->input('fiyatlar', []) as $x) {
                $hid = (int) ($x['hizmet_id'] ?? 0); $fiyat = (float) str_replace(',', '.', (string) ($x['fiyat'] ?? ''));
                if ($hid < 1 || $fiyat <= 0 || $fiyat >= 1000000) continue;
                $n += DB::table('salon_sunulan_hizmetler')->where('salon_id', $sid)->where('hizmet_id', $hid)->where('aktif', 1)->update(['baslangic_fiyat' => $fiyat]);
            }
            if (!$n) return response()->json(['ok' => 0, 'hata' => 'En az bir fiyat yazın.']);
            \Illuminate\Support\Facades\Cache::forget('santral_salon_bilgi:' . $sid);
            DB::table('wa_asistan_dersler')->where('id', $d->id)->update(['durum' => 'cevaplandi', 'updated_at' => now()]);
            return response()->json(['ok' => 1, 'mesaj' => 'Fiyatlar hizmet listenize kaydedildi. Asistan artık bu fiyatları söyler.']);
        }
        if ($d->tur === 'bilgi' && $request->input('tip') === 'hizmet_sec') {
            $hid = (int) $request->input('hizmet_id');
            $ifade = \App\Http\Controllers\Api\AiAsistanController::esanlamAnahtar((string) (json_decode($d->form ?: '{}', true)['ifade'] ?? ''));
            if ($ifade === '' || !DB::table('salon_sunulan_hizmetler')->where('salon_id', $sid)->where('hizmet_id', $hid)->exists()) return response()->json(['ok' => 0, 'hata' => 'Bir hizmet seçin.']);
            DB::table('wa_hizmet_esanlam')->updateOrInsert(['salon_id' => $sid, 'ifade' => mb_substr($ifade, 0, 120)], ['hizmet_id' => $hid, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('wa_asistan_dersler')->where('id', $d->id)->update(['durum' => 'cevaplandi', 'updated_at' => now()]);
            return response()->json(['ok' => 1, 'mesaj' => 'Öğrendim. Müşteri bunu yazınca bu hizmetten randevu veririm.']);
        }
        $cevap = trim(mb_substr((string) $request->input('cevap', ''), 0, 1000));
        if ($d->tur === 'bilgi' && $cevap !== '') WaBedavaKatman::ogren($sid, (string) $d->metin, $cevap);
        DB::table('wa_asistan_dersler')->where('id', $d->id)->update(['durum' => $cevap !== '' ? 'cevaplandi' : 'kapali', 'updated_at' => now()]);
        return response()->json(['ok' => 1]);
    }

    /** "Şimdi incele": bu isletmenin bitmis sohbetlerini hemen denetime yollar (6 saatte bir). */
    public function denetimBaslat(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!self::sistemYoneticisiMi()) return response()->json(['ok' => 0, 'hata' => 'Bu işlem yalnız sistem yöneticisine açık.'], 403);
        if (!\Illuminate\Support\Facades\Cache::add("wa_denetim_elle_{$sid}", 1, 360)) {
            return response()->json(['ok' => 0, 'hata' => 'İnceleme kısa süre önce başlatıldı. Sonuçlar genellikle 1 saat içinde gelir.']);
        }
        $r = WaDenetci::gonder($sid);
        return response()->json(['ok' => $r['ok'] ? 1 : 0, 'adet' => $r['adet'] ?? 0, 'hata' => $r['ok'] ? null : 'İnceleme başlatılamadı.']);
    }

    protected static function devralindiMi($o): bool
    {
        return ($o->sessiz_neden === 'devral' && $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time())
            || ($o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - WaAsistan::insanEsikDk() * 60);
    }

    /**
     * Sohbetin altindaki durum satiri (mobil): kim yazdi, asistan ne kadar bekliyor.
     * @return array ['kod' => asistan|personel|devral|sessiz|kapali, 'metin' => string, 'kalan_dk' => ?int]
     */
    protected static function asistanDurum($o, array $gecmis): array
    {
        if ($o->sessiz_neden === 'devral' && $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time()) {
            return ['kod' => 'devral', 'metin' => 'Uzun süre devralındı · asistana geri verene kadar susuyor', 'kalan_dk' => null];
        }
        $esik = WaAsistan::insanEsikDk();
        if ($o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - $esik * 60) {
            $yazan = 'Personel';
            for ($i = count($gecmis) - 1; $i >= 0; $i--) if (!empty($gecmis[$i]['personel'])) { $yazan = WaAsistan::yazanKisa($gecmis[$i]['yazan'] ?? null) ?: 'Personel'; break; }
            $kalan = max(1, (int) ceil((strtotime($o->son_insan_at) + $esik * 60 - time()) / 60));
            return ['kod' => 'personel', 'metin' => "$yazan yazdı · cevap vermezseniz asistan $kalan dk sonra devam eder", 'kalan_dk' => $kalan];
        }
        if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time() && $o->sessiz_neden !== 'aktar') {
            return ['kod' => 'sessiz', 'metin' => self::durumEtiket($o), 'kalan_dk' => null];
        }
        try { if (!WaAsistan::acikMi((int) $o->salon_id)) return ['kod' => 'kapali', 'metin' => 'Asistan kapalı', 'kalan_dk' => null]; } catch (\Throwable $e) {}
        return ['kod' => 'asistan', 'metin' => 'Asistan cevaplıyor', 'kalan_dk' => null];
    }

    const GIDEN_TIP = [
        'wa_asistan' => 'Asistan cevabı', 'randevu_hatirlatma' => 'Randevu hatırlatması', 'yeni_randevu_bildirim' => 'Yeni randevu bildirimi',
        'guncelleme_bildirim' => 'Randevu değişiklik bildirimi', 'iptal_bildirim' => 'Randevu iptal bildirimi', 'dogum_gunu' => 'Doğum günü mesajı',
        'kampanya' => 'Kampanya mesajı', 'sifre_kodu' => 'Uygulama şifresi', 'sifre_sifirlama' => 'Şifre sıfırlama', 'manuel_musteri' => 'Panelden elle gönderilen mesaj',
        'seans_bildirim' => 'Seans bildirimi', 'yol_tarifi' => 'Yol tarifi', 'randevu_bilgi' => 'Randevu bilgisi', 'personel_hatirlatma' => 'Personel hatırlatması',
        'kara_liste_bildirim' => 'Kara liste bildirimi', 'mesaj' => 'Sistem mesajı',
    ];

    /**
     * "Bu mesaji kim atti?": bu numaraya son 14 gunde sistemden giden WhatsApp mesajlari (turuyle) + ayni numaranin onceki asistan sohbetleri.
     * @return array [giden[], onceki[]]
     */
    protected static function numaraGecmisi($o, int $sid): array
    {
        $giden = []; $onceki = [];
        $tel = preg_replace('/\D/', '', (string) ($o->musteri_tel ?: $o->telefon));
        try {
            $salonlar = DB::table('salonlar')->where('whatsapp_paylasilan_salon_id', $o->salon_id)->pluck('id')->push($o->salon_id)->push($sid)->unique()->values()->all();
            if (strlen($tel) >= 10) {
                $tipVar = \Illuminate\Support\Facades\Schema::hasColumn('whatsapp_gonderim_loglari', 'gonderim_tipi');
                $rows = DB::table('whatsapp_gonderim_loglari')->whereIn('salon_id', $salonlar)->where('telefon', 'like', '%' . substr($tel, -10))
                    ->where('created_at', '>=', now()->subDays(14))->orderByDesc('id')->limit(20)->get();
                foreach ($rows as $l) {
                    $tip = $tipVar ? (string) ($l->gonderim_tipi ?? '') : '';
                    $giden[] = ['tarih' => $l->created_at, 'tip' => self::GIDEN_TIP[$tip] ?? ($tip !== '' ? ucfirst(str_replace('_', ' ', $tip)) : 'Sistem mesajı'),
                        'asistan' => $tip === WhatsAppService::ASISTAN_TIPI, 'mesaj' => mb_substr((string) $l->mesaj, 0, 400), 'durum' => (int) $l->durum];
                }
            }
            $q = DB::table('wa_asistan_oturumlari')->where('isletme_id', $sid)->where('id', '<>', $o->id)
                ->where(function ($w) use ($o, $tel) {
                    $w->where('anahtar', $o->anahtar);
                    if (strlen($tel) >= 10) $w->orWhere('telefon', 'like', '%' . substr($tel, -10))->orWhere('musteri_tel', 'like', '%' . substr($tel, -10));
                });
            foreach ($q->orderByDesc('id')->limit(5)->get(['id', 'created_at', 'son_mesaj_at', 'gecmis']) as $x) {
                $g = json_decode($x->gecmis ?: '[]', true) ?: [];
                $ilk = $g ? $g[0] : null;
                $onceki[] = ['id' => (int) $x->id, 'tarih' => $x->created_at, 'son' => $x->son_mesaj_at, 'mesaj' => count($g),
                    'ilk' => $ilk ? ((($ilk['role'] ?? '') === 'user' ? 'Müşteri: ' : 'Asistan: ') . mb_substr((string) ($ilk['content'] ?? ''), 0, 140)) : ''];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[WA-PANEL] numara gecmisi: ' . $e->getMessage());
        }
        return [$giden, $onceki];
    }

    /**
     * Her mesajin saati: yeni kayitlarda gecmis 'at'; eski kayitlarda gelen mesaj (wa_asistan_gelen, 7 gun) ve
     * asistan gonderim kaydi (whatsapp_gonderim_loglari) metinle sirayla eslenir. Bulunamayan null (saat gosterilmez).
     */
    protected static function mesajSaatleri($o, array $gecmis): array
    {
        $out = [];
        foreach ($gecmis as $i => $m) if (!empty($m['at'])) $out[$i] = $m['at'];
        if (count($out) === count($gecmis)) return $out;
        $bas = date('Y-m-d H:i:s', strtotime((string) $o->created_at) - 300);
        $norm = function ($s) { return mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $s)), 0, 120); };
        try {
            $gelen = DB::table('wa_asistan_gelen')->where('salon_id', $o->salon_id)->where('anahtar', $o->anahtar)
                ->where('created_at', '>=', $bas)->orderBy('id')->get(['metin', 'created_at'])->all();
            $tel = substr(preg_replace('/\D/', '', (string) ($o->telefon ?: $o->musteri_tel)), -10);
            $giden = strlen($tel) === 10 ? DB::table('whatsapp_gonderim_loglari')->where('telefon', 'like', '%' . $tel)
                ->where('created_at', '>=', $bas)->orderBy('id')->limit(300)->get(['mesaj', 'created_at'])->all() : [];
        } catch (\Throwable $e) {
            return $out;
        }
        $gi = 0; $ci = 0;
        foreach ($gecmis as $i => $m) {
            if (isset($out[$i])) continue;
            $c = $norm($m['content'] ?? '');
            if (($m['role'] ?? '') === 'user') {
                // birlestirilmis mesaj: gecmisteki metin, gelen satirlarin ilkiyle baslar
                for ($k = $gi; $k < count($gelen); $k++) {
                    $g = $norm($gelen[$k]->metin);
                    if ($g !== '' && mb_strpos($c, $g) === 0) { $out[$i] = $gelen[$k]->created_at; $gi = $k + 1; break; }
                }
            } else {
                for ($k = $ci; $k < count($giden); $k++) {
                    if ($norm($giden[$k]->mesaj) === $c) { $out[$i] = $giden[$k]->created_at; $ci = $k + 1; break; }
                }
            }
        }
        return $out;
    }

    /**
     * "Bu numara yazdı ama cevap yok / görünmüyor": numaranin asistandaki izini cikarir.
     * Hariç mi (personel/yetkili), son 7 gunde gelen mesaj var mi (islendi mi), sohbeti var mi, giden mesajlar.
     */
    public function numaraKontrol(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $rak = preg_replace('/\D/', '', (string) $request->input('tel'));
        if (strlen($rak) < 10) return response()->json(['ok' => 0, 'hata' => 'Telefon numarasını tam yazın (ör. 0546 691 73 93).']);
        $son10 = substr($rak, -10);
        $salonlar = DB::table('salonlar')->where('whatsapp_paylasilan_salon_id', $sid)->pluck('id')->push($sid)->unique()->values()->all();
        $haric = null;
        foreach (WaAsistan::haricListesi($sid) as $n => $neden) if (substr($n, -10) === $son10) { $haric = $neden; break; }
        $gelen = DB::table('wa_asistan_gelen')->whereIn('salon_id', $salonlar)->where('telefon', 'like', '%' . $son10)
            ->orderByDesc('id')->limit(15)->get(['metin', 'islem', 'created_at', 'anahtar']);
        $oturumlar = DB::table('wa_asistan_oturumlari')->where('isletme_id', $sid)
            ->where(function ($w) use ($son10) { $w->where('telefon', 'like', '%' . $son10)->orWhere('musteri_tel', 'like', '%' . $son10)->orWhere('anahtar', 'like', '%' . $son10 . '%'); })
            ->orderByDesc('id')->limit(5)->get(['id', 'son_mesaj_at', 'insan_devraldi', 'sessiz_bitis', 'sessiz_neden', 'durum']);
        $giden = DB::table('whatsapp_gonderim_loglari')->whereIn('salon_id', $salonlar)->where('telefon', 'like', '%' . $son10)
            ->where('created_at', '>=', now()->subDays(14))->count();

        $neden = [];
        if ($haric) $neden[] = "Bu numara asistanın cevap vermediği listede: {$haric}. Asistan personel/yetkili numaralarına cevap vermez ve bu yazışmalar panelde sohbet olarak görünmez.";
        if ($oturumlar->count()) {
            $o = $oturumlar->first();
            if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time()) $neden[] = 'Sohbet var ama asistan susturulmuş (' . ($o->sessiz_neden ?: '—') . ', ' . $o->sessiz_bitis . '\'e kadar).';
            elseif ($o->insan_devraldi) $neden[] = 'Sohbet var; personel WhatsApp\'tan yazdığı için asistan o sohbette susmuştu.';
            else $neden[] = 'Sohbet panelde var (son mesaj ' . $o->son_mesaj_at . '). Sohbetler\'de arayınca açılır.';
        }
        $bekleyen = $gelen->filter(function ($g) { return $g->islem === null && strtotime($g->created_at) < time() - 120; })->count();
        if ($bekleyen) $neden[] = "{$bekleyen} mesaj geldi ama işlenemedi (sunucu hatası). Bize iletin.";
        if (!$haric && !$oturumlar->count() && !$gelen->count()) {
            $neden[] = 'Son 7 günde bu numaradan sisteme HİÇ mesaj ulaşmadı. Olası nedenler: müşteri sesli mesaj / video / belge gönderdi (köprü güncellenene kadar bu mesajlar iletilmiyor), '
                . 'başka bir WhatsApp numaranıza yazdı ya da numara gizli kimlikle (LID) geldi ve adıyla kayıtlı olabilir — Sohbetler\'de adıyla arayın.';
        }
        return response()->json(['ok' => 1, 'neden' => $neden, 'giden_14g' => $giden,
            'gelen' => $gelen->map(function ($g) { return ['tarih' => $g->created_at, 'metin' => mb_substr((string) $g->metin, 0, 140), 'durum' => $g->islem === 'haric' ? 'hariç (cevap yok)' : ($g->islem ? 'işlendi' : 'bekliyor')]; })->values(),
            'oturumlar' => $oturumlar->pluck('id')]);
    }

    /** "Test bildirimi gönder": talep bildirimiyle ayni yoldan (zil + yoneticilerin telefonu) deneme gonderir, kime ulastigini dondurur. */
    public function testBildirim(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!\Illuminate\Support\Facades\Cache::add('wa_test_bildirim_' . $sid, 1, 1)) {
            return response()->json(['ok' => 0, 'hata' => 'Az önce test bildirimi gönderildi; 1 dakika sonra tekrar deneyin.']);
        }
        // Test bilerek yapiliyor: bu isletmenin yoneticilerinin hata yuzunden PASIFE dusmus
        // telefon kayitlarini yeniden dene (orn. APNs anahtari sonradan yuklendi). Gecersiz
        // token ise gonderim zaten siler; hata devam ederse sayac yeniden birikir.
        try {
            $pidler = DB::table('salon_personelleri as p')->join('model_has_roles as m', 'p.yetkili_id', '=', 'm.model_id')
                ->where('p.salon_id', $sid)->where('m.role_id', '<', 5)->pluck('p.id');
            if ($pidler->count()) {
                DB::table('bildirim_kimlikleri')->whereIn('isletme_yetkili_id', $pidler)->where('aktif', 0)
                    ->update(['aktif' => 1, 'gonderim_hatalari' => 0]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[WA-TEST-BILDIRIM] pasif yeniden: ' . $e->getMessage());
        }
        // Gondermeden ONCE teshis (gonderim gecersiz tokenlari siler)
        $teshis = $this->bildirimTeshis($sid);
        $liste = WaAsistan::bildir($sid, '🔔 Test bildirimi: WhatsApp Asistanı talep bildirimleri bu telefona böyle gelir.');
        foreach ($liste as &$x) {
            if (empty($x['ulasti'])) $x['neden'] = trim(($teshis[$x['pid'] ?? 0] ?? '') . (!empty($x['hata']) ? ' Firebase cevabı: ' . $x['hata'] : ''));
            unset($x['pid'], $x['hata']);
        }
        unset($x);
        return response()->json(['ok' => 1, 'liste' => $liste]);
    }

    /**
     * Push neden ulasmadi: personelin bu isletmeye bagli cihaz kaydi var mi, varsa hangi uygulamada,
     * yoksa ayni kisinin telefonu baska isletme / baska uygulama icin mi kayitli. [pid => metin]
     */
    private function bildirimTeshis(int $sid): array
    {
        $out = [];
        try {
            $bundle = (string) Salonlar::where('id', $sid)->value('app_bundle');
            $kisiler = DB::table('salon_personelleri as p')->join('model_has_roles as m', 'p.yetkili_id', '=', 'm.model_id')
                ->where('p.salon_id', $sid)->where('m.role_id', '<', 5)->get(['p.id', 'p.yetkili_id']);
            foreach ($kisiler as $k) {
                $tum = DB::table('bildirim_kimlikleri')->where('isletme_yetkili_id', $k->id)
                    ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')->get();
                $uygun = $tum->filter(function ($t) use ($bundle) { return $bundle === '' || $t->app_bundle === $bundle; });
                if ($uygun->where('aktif', 1)->count()) { $out[$k->id] = 'Telefon kaydı var ama gönderim hata verdi (Firebase).'; continue; }
                if ($uygun->count()) { $out[$k->id] = 'Telefon kaydı çok hata verdiği için pasif; uygulamadan çıkış yapıp tekrar giriş yapmalı.'; continue; }
                if ($tum->count()) { $out[$k->id] = 'Telefonu başka bir uygulamada kayıtlı (' . $tum->pluck('app_bundle')->unique()->implode(', ') . '); bu işletmenin uygulaması: ' . ($bundle ?: '—') . '.'; continue; }
                // Ayni kisi (yetkili) baska isletmenin personeli olarak mi kayitli?
                $baska = $k->yetkili_id ? DB::table('bildirim_kimlikleri as b')->join('salon_personelleri as p2', 'p2.id', '=', 'b.isletme_yetkili_id')
                    ->join('salonlar as s', 's.id', '=', 'p2.salon_id')->where('p2.yetkili_id', $k->yetkili_id)->where('p2.salon_id', '!=', $sid)
                    ->distinct()->pluck('s.salon_adi')->filter()->values() : collect();
                $out[$k->id] = $baska->count()
                    ? 'Telefonu başka işletme için kayıtlı (' . $baska->take(3)->implode(', ') . '). Uygulamada bu işletmeyi seçip çıkış yapıp tekrar giriş yapmalı.'
                    : 'Bu kişiye kayıtlı telefon yok (uygulamaya hiç giriş yapılmamış ya da bildirim izni verilmemiş).';
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[WA-TEST-BILDIRIM] teshis: ' . $e->getMessage());
        }
        return $out;
    }

    /** "Yol tarifi gönder": salonun adresi + Google Haritalar linki bu sohbete yazilir (personel istegi; asistan susmus olsa da). */
    public function yolTarifi(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)->exists()) return response()->json(['ok' => 0], 404);
        $s = Salonlar::find($sid);
        $link = \App\Services\WhatsAppMesajFormat::yolTarifiLinki($s);
        $adres = trim((string) ($s->adres ?? ''));
        if ($link === '' && $adres === '') return response()->json(['ok' => 0, 'hata' => 'İşletmenin adresi kayıtlı değil. Önce işletme ayarlarından adresi girin.']);
        $metin = '📍 ' . $s->salon_adi . ($adres !== '' ? "\n" . $adres : '') . ($link !== '' ? "\n\nYol tarifi (harita): " . $link : '');
        $ok = WaAsistan::oturumaYaz((int) $id, $metin, 'yol_tarifi', true);
        return response()->json(['ok' => $ok ? 1 : 0, 'hata' => $ok ? null : 'Mesaj gönderilemedi (WhatsApp bağlantısını kontrol edin).']);
    }

    /** "Asistan tamamlasın": yarim kalan sohbeti asistan simdi surdurur (cevapsiz mesaj / sozde kalan cevap). */
    public function devamEttir(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)->exists()) return response()->json(['ok' => 0], 404);
        try {
            return response()->json(WaAsistan::devamEttir((int) $id));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[WA-DEVAM] ' . $e->getMessage(), ['oturum' => $id]);
            return response()->json(['ok' => 0, 'hata' => 'Asistan şu an devam edemedi; birazdan tekrar deneyin.']);
        }
    }

    /** "Cevapladım": isletme musteriye dondu (panelden isaret) -> bekleyen talepler kapanir, takip durur. */
    public function cevaplandi(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)->exists()) return response()->json(['ok' => 0], 404);
        $n = WaTalep::cevaplandi((int) $id, (optional($this->asistanGorusmeYetkili())->name ?: 'Panel') . ' (panelden işaretlendi)');
        return response()->json(['ok' => $n ? 1 : 0]);
    }

    protected static function durumEtiket($o, ?array $talep = null): string
    {
        if (self::devralindiMi($o)) return 'Personelde';
        // Isletmeye sorulan konu: sonucu gorunur (bekliyor ne kadar / cevaplandi / cevapsiz)
        if ($talep && $talep['durum'] === 'bekliyor') return 'Dönüş bekliyor · ' . $talep['sure'];
        if ($talep && $talep['durum'] === 'cevapsiz') return 'İşletme dönmedi';
        $randevu = in_array($o->sonuc, ['randevu', 'guncelle', 'teyit'], true);
        if ($talep && $talep['durum'] === 'cevaplandi' && !$randevu) return 'İşletme cevapladı';
        if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time() && $o->sessiz_neden !== 'aktar') {
            return ['aktar' => 'İşletmeye aktarıldı', 'yanlis_numara' => 'Yanlış numara · yazılmıyor', 'hiz_siniri' => 'Geçici durduruldu', 'dongu' => 'Geçici durduruldu', 'otomatik_yanit' => 'Bot algılandı'][$o->sessiz_neden] ?? 'Asistan beklemede';
        }
        $s = ['randevu' => 'Randevu alındı', 'iptal' => 'Randevu iptal', 'guncelle' => 'Randevu değişti', 'aktar' => 'İşletmeye aktarıldı', 'teyit' => 'Randevuyu teyit etti', 'odeme' => 'Ödeme sözü'];
        if ($o->sonuc && isset($s[$o->sonuc])) return $s[$o->sonuc];
        if ($o->fiyat_verildi) return 'Fiyat aldı';
        return $o->durum === 'acik' ? 'Devam ediyor' : 'Tamamlandı';
    }
}
