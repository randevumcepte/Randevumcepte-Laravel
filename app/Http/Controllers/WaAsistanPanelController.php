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
 * Yalniz salon yoneticileri (rol < 5) — StoreAdminController::asistanGorusmeSalon ile ayni kapi.
 */
class WaAsistanPanelController extends StoreAdminController
{
    protected function salon(Request $request): ?int
    {
        return $this->asistanGorusmeSalon($request);
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
        if ($rol >= 5) return view('isletmeadmin.yetkisizerisim');

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
            ->where('sh.salon_id', $sid)->orderBy('h.hizmet_adi')->get(['sh.hizmet_id as id', 'h.hizmet_adi as ad', 'sh.son_fiyat as fiyat']);
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
        $q = DB::table('wa_asistan_oturumlari')->where('isletme_id', $sid)
            ->whereBetween('son_mesaj_at', [$bas . ' 00:00:00', $bit . ' 23:59:59'])->orderByDesc('son_mesaj_at')->limit(300);
        $ara = trim((string) $request->input('q'));
        if ($ara !== '') $q->where(function ($w) use ($ara) { $w->where('ad', 'like', "%$ara%")->orWhere('telefon', 'like', "%$ara%")->orWhere('musteri_tel', 'like', "%$ara%"); });
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
                'hatirlatma' => !empty($m['hatirlatma']), 'at' => $saatler[$i] ?? null];
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
        $alanlar = ['fiyat_soyle', 'geri_kazanim', 'gk_bekle_saat', 'gk_kampanya', 'memnuniyet_sor', 'gorsel_fiyat', 'gorsel_sektor', 'gorsel_not', 'denetci', 'denetci_oto'];
        $veri = [];
        foreach ($alanlar as $a) if ($request->has($a)) $veri[$a] = $request->input($a);
        WaAsistanVeri::ayarKaydet($sid, $veri, optional(Auth::guard('isletmeyonetim')->user())->name);
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
        WaAsistanVeri::ayarKaydet($sid, ['gorsel_sektor' => $sektor], optional(Auth::guard('isletmeyonetim')->user())->name);
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
        return response()->json(['ok' => 1] + WaDenetci::panel($sid));
    }

    /** Davranis dersi: aktif <-> kapali (geri al) ya da bekleyen oneriyi onayla. */
    public function dersDurum(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $durum = $request->input('durum') === 'aktif' ? 'aktif' : 'kapali';
        $q = DB::table('wa_asistan_dersler')->where('id', (int) $id)->where('isletme_id', $sid)->where('tur', 'davranis');
        if ($durum === 'aktif' && DB::table('wa_asistan_dersler')->where('isletme_id', $sid)->where('tur', 'davranis')->where('durum', 'aktif')->count() >= WaDenetci::MAX_AKTIF_DERS) {
            return response()->json(['ok' => 0, 'hata' => 'En fazla ' . WaDenetci::MAX_AKTIF_DERS . ' ders aynı anda açık olabilir. Önce birini kapatın.']);
        }
        $n = $q->update(['durum' => $durum, 'updated_at' => now()]);
        \Illuminate\Support\Facades\Cache::forget("wa_dersler_{$sid}");
        return response()->json(['ok' => $n ? 1 : 0]);
    }

    /** Bilgi eksigi: isletmenin cevabi "Öğrenilen Cevaplar"a yazilir (asistan sonra bedava cevaplar); bos = yoksay. */
    public function bilgiCevapla(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        $d = DB::table('wa_asistan_dersler')->where('id', (int) $id)->where('isletme_id', $sid)->whereIn('tur', ['bilgi', 'kod'])->first();
        if (!$d) return response()->json(['ok' => 0]);
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
        if (!\Illuminate\Support\Facades\Cache::add("wa_denetim_elle_{$sid}", 1, 360)) {
            return response()->json(['ok' => 0, 'hata' => 'İnceleme kısa süre önce başlatıldı. Sonuçlar genellikle 1 saat içinde gelir.']);
        }
        $r = WaDenetci::gonder($sid);
        return response()->json(['ok' => $r['ok'] ? 1 : 0, 'adet' => $r['adet'] ?? 0, 'hata' => $r['ok'] ? null : 'İnceleme başlatılamadı.']);
    }

    protected static function devralindiMi($o): bool
    {
        return ($o->sessiz_neden === 'devral' && $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time())
            || ($o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - 10 * 60);
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

    /** "Test bildirimi gönder": talep bildirimiyle ayni yoldan (zil + yoneticilerin telefonu) deneme gonderir, kime ulastigini dondurur. */
    public function testBildirim(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        if (!\Illuminate\Support\Facades\Cache::add('wa_test_bildirim_' . $sid, 1, 60)) {
            return response()->json(['ok' => 0, 'hata' => 'Az önce test bildirimi gönderildi; 1 dakika sonra tekrar deneyin.']);
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
        $n = WaTalep::cevaplandi((int) $id, (optional(Auth::guard('isletmeyonetim')->user())->name ?: 'Panel') . ' (panelden işaretlendi)');
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
