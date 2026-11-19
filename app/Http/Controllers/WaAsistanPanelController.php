<?php

namespace App\Http\Controllers;

use App\Salonlar;
use App\Services\SantralPaket;
use App\Services\WaAsistan;
use App\Services\WaAsistanRapor;
use App\Services\WaAsistanVeri;
use App\Services\WaAvatar;
use App\Services\WaGeriKazanim;
use App\Services\WaGorselDanisman;
use App\Services\WaPaket;
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
        $hizmetler = DB::table('salon_sunulan_hizmetler as sh')->join('hizmetler as h', 'h.id', '=', 'sh.hizmet_id')
            ->where('sh.salon_id', $isletme->id)->orderBy('h.hizmet_adi')->get(['sh.hizmet_id as id', 'h.hizmet_adi as ad', 'sh.son_fiyat as fiyat']);
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
        ]);
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
        $kuralVar = (bool) WaGorselDanisman::kurallar($sid, (string) $ayar['gorsel_sektor']);
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
        foreach ($rows as $o) {
            $t10 = substr(preg_replace('/\D/', '', (string) ($o->musteri_tel ?: $o->telefon)), -10);
            $g = json_decode($o->gecmis ?: '[]', true) ?: [];
            $sonMus = '';
            for ($i = count($g) - 1; $i >= 0; $i--) if (($g[$i]['role'] ?? '') === 'user') { $sonMus = (string) $g[$i]['content']; break; }
            $liste[] = [
                'id' => (int) $o->id, 'ad' => $o->ad, 'telefon' => $o->musteri_tel ?: $o->telefon, 'son_mesaj' => mb_substr($sonMus, 0, 120),
                'tarih' => $o->son_mesaj_at, 'mesaj' => (int) $o->mesaj_sayisi, 'cevap' => (int) $o->cevap_sayisi,
                'sonuc' => $o->sonuc, 'randevu_id' => $o->randevu_id, 'durum' => self::durumEtiket($o),
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
        foreach (json_decode($o->gecmis ?: '[]', true) ?: [] as $m) {
            $rol = ($m['role'] ?? '') === 'user' ? 'musteri' : (!empty($m['personel']) ? 'personel' : 'asistan');
            $satirlar[] = ['rol' => $rol, 'metin' => (string) ($m['content'] ?? ''), 'geri_kazanim' => !empty($m['geri_kazanim']),
                'hatirlatma' => !empty($m['hatirlatma'])];
        }
        $gorseller = [];
        if (WaGorselDanisman::semaGaranti()) {
            foreach (DB::table('wa_gorsel_analizler')->where('oturum_id', $o->id)->where('isletme_id', $sid)->orderBy('id')->get() as $g) {
                $an = json_decode($g->analiz ?: 'null', true);
                $gorseller[] = ['id' => (int) $g->id, 'dosya' => (bool) $g->dosya, 'durum' => $g->durum, 'toplam' => $g->toplam !== null ? (float) $g->toplam : null,
                    'tarif' => $an['aciklama'] ?? null, 'onbellek' => (int) $g->onbellek];
            }
        }
        return response()->json(['ok' => 1, 'sohbet' => [
            'id' => (int) $o->id, 'ad' => $o->ad, 'telefon' => $o->musteri_tel ?: $o->telefon, 'durum' => self::durumEtiket($o),
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
        $alanlar = ['fiyat_soyle', 'geri_kazanim', 'gk_bekle_saat', 'gk_kampanya', 'memnuniyet_sor', 'gorsel_fiyat', 'gorsel_sektor', 'gorsel_not'];
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

    protected static function devralindiMi($o): bool
    {
        return ($o->sessiz_neden === 'devral' && $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time())
            || ($o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - 10 * 60);
    }

    protected static function durumEtiket($o): string
    {
        if (self::devralindiMi($o)) return 'Personelde';
        if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time()) {
            return ['aktar' => 'İşletmeye aktarıldı', 'hiz_siniri' => 'Geçici durduruldu', 'dongu' => 'Geçici durduruldu', 'otomatik_yanit' => 'Bot algılandı'][$o->sessiz_neden] ?? 'Asistan beklemede';
        }
        $s = ['randevu' => 'Randevu alındı', 'iptal' => 'Randevu iptal', 'guncelle' => 'Randevu değişti', 'aktar' => 'İşletmeye aktarıldı', 'teyit' => 'Randevuyu teyit etti', 'odeme' => 'Ödeme sözü'];
        if ($o->sonuc && isset($s[$o->sonuc])) return $s[$o->sonuc];
        if ($o->fiyat_verildi) return 'Fiyat aldı';
        return $o->durum === 'acik' ? 'Devam ediyor' : 'Tamamlandı';
    }
}
