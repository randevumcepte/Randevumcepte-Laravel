<?php

namespace App\Http\Controllers;

use App\Services\PersonelProfil;
use Illuminate\Http\Request;

/**
 * Profil sayfasi uclari (2026-11-23). Giris yapan kisi (her rol) kendi verisini gorur/degistirir;
 * ekip, hedef ve izin karari yalniz yonetici (rol < 5). Web: oturum, mobil: Bearer + sube.
 */
class ProfilController extends StoreAdminController
{
    /** SAC kurucusundaki web-oturum kapisi mobil Bearer istegini reddederdi; kimlik kisi() icinde dogrulanir. */
    public function __construct()
    {
    }

    /** [salon_id, personel satiri, yonetici mi] ya da null. */
    protected function kisi(Request $request)
    {
        $yetkili = $this->asistanGorusmeYetkili();
        if (!$yetkili) return null;
        $salonId = (int) ($request->has('sube') ? $request->input('sube') : self::mevcutsube($request));
        $salonlar = array_map('intval', $yetkili->yetkili_olunan_isletmeler->where('aktif', 1)->pluck('salon_id')->toArray());
        if (!$salonId || !in_array($salonId, $salonlar, true)) return null;
        $p = \App\Personeller::where('salon_id', $salonId)->where('yetkili_id', $yetkili->id)->first(['id', 'personel_adi', 'unvan', 'salon_id']);
        if (!$p) return null;
        $rol = self::kullaniciRolu($salonId, $yetkili->id);
        PersonelProfil::semaGaranti();
        return [$salonId, $p, $rol === null || (int) $rol < 5];
    }

    private function yok()
    {
        return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
    }

    private function sonuc($r, array $ek = [])
    {
        if (is_string($r)) return response()->json(['ok' => 0, 'hata' => $r], 422);
        return response()->json(['ok' => 1] + $ek);
    }

    public function ozet(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return response()->json(['ok' => 1, 'yonetici' => $k[2], 'personel_id' => (int) $k[1]->id,
            'durumlar' => PersonelProfil::DURUMLAR] + PersonelProfil::ozet($k[0], $k[1], $k[2], !$request->input('hizli')));
    }

    /**
     * Durum degisikligi. Govde: {durum: musait|molada|izinli, dakika?, bitis_saat?, bitis_tarih?, personel_id?}
     * Personel: Molada = talep (yonetici onayi), Musait = onaysiz donus, Izinli secilemez (Izinlerim).
     * Yonetici (rol < 5): kendisi ve baska personel icin onaysiz uygular.
     */
    public function durum(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        list($sid, $ben, $yonetici) = $k;
        $durum = (string) $request->input('durum');
        $hedefId = (int) $request->input('personel_id');
        $hedef = $ben;
        if ($hedefId && $hedefId !== (int) $ben->id) {
            if (!$yonetici) return response()->json(['ok' => 0, 'hata' => 'Başkasının durumunu yalnızca yönetici değiştirebilir.'], 403);
            $hedef = \App\Personeller::where('id', $hedefId)->where('salon_id', $sid)->first(['id', 'personel_adi']);
            if (!$hedef) return $this->sonuc('Personel bulunamadı.');
        }
        $v = $request->only(['dakika', 'bitis_saat', 'bitis_tarih']);
        if ($yonetici) {
            $r = \App\Services\PersonelMola::dogrudan($sid, $ben, $hedef, $durum, $v, $request);
        } elseif ($durum === 'molada') {
            $r = \App\Services\PersonelMola::talep($sid, $ben, $v['dakika'] ?? 0, $v['bitis_saat'] ?? '', $request);
        } elseif ($durum === 'musait') {
            $r = \App\Services\PersonelMola::musaitYap($sid, $ben, $ben, false, $request);
        } else {
            $r = 'İzin için Profil > İzinlerim\'den talep gönderin.';
        }
        if (is_string($r)) return $this->sonuc($r);
        return $this->sonuc(true, ['durum_detay' => \App\Services\PersonelMola::durumDetay($hedef->id),
            'talep' => !$yonetici && $durum === 'molada']);
    }

    /** Yonetici: randevu dagilim raporu (asistanin actiklari + tumu, personel basina). ?donem=bugun|hafta|ay */
    public function atamaRaporu(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Bu rapor yalnızca yöneticiye açık.'], 403);
        $donem = (string) $request->input('donem', 'ay');
        if ($donem === 'bugun') { $bas = $bit = date('Y-m-d'); }
        elseif ($donem === 'hafta') { $bas = date('Y-m-d', strtotime('monday this week')); $bit = date('Y-m-d', strtotime('sunday this week')); }
        else { $donem = 'ay'; $bas = date('Y-m-01'); $bit = date('Y-m-t'); }
        return $this->sonuc(true, ['donem' => $donem, 'baslangic' => $bas, 'bitis' => $bit,
            'personeller' => \App\Services\AdilAtama::rapor($k[0], $bas, $bit)]);
    }

    /** Yonetici: personeli otomatik randevu atamasina dahil et / haric tut. Govde: {personel_id, haric: 0/1} */
    public function otoAtama(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Bu ayarı yalnızca yönetici değiştirebilir.'], 403);
        return $this->sonuc(\App\Services\AdilAtama::haricKaydet($k[0], $k[1], (int) $request->input('personel_id'), (int) $request->input('haric') === 1, $request));
    }

    /** Yonetici: mola talebine karar (ilk karar veren gecerli). */
    public function molaKarar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Mola kararını yalnızca yönetici verebilir.'], 403);
        return $this->sonuc(\App\Services\PersonelMola::karar($k[0], $k[1], (int) $request->input('id'), (int) $request->input('onay') === 1, $request));
    }

    public function yorumlar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['yorumlar' => PersonelProfil::yorumlar($k[0], (int) $k[1]->id, $k[2] && $request->input('tum') == 1)]);
    }

    public function calismaSaatleri(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, PersonelProfil::calismaSaatleri((int) $k[1]->id));
    }

    public function hizmetler(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['hizmetler' => PersonelProfil::hizmetler($k[0], (int) $k[1]->id)]);
    }

    public function izinler(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $tum = $k[2] && $request->input('tum') == 1;
        $liste = PersonelProfil::izinler($k[0], (int) $k[1]->id, $tum);
        if ($tum) {
            // Onay ekraninda uyari: bekleyen izin suresinde zaten alinmis randevular
            foreach ($liste as $z) {
                if ($z->durum === 'bekliyor') $z->cakisan = \App\Services\PersonelMola::cakisanlar($k[0], $z->personel_id, $z->baslangic, $z->bitis);
            }
        }
        return $this->sonuc(true, ['izinler' => $liste, 'yonetici' => $k[2]]);
    }

    public function izinTalep(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::izinTalep($k[0], $k[1], $request->only(['baslangic', 'bitis', 'tur', 'aciklama']), $request));
    }

    public function izinIptal(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::izinIptal($k[0], $k[1], (int) $request->input('id'), $request));
    }

    public function izinKarar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'İzin kararını yalnızca yönetici verebilir.'], 403);
        return $this->sonuc(PersonelProfil::izinKarar($k[0], $k[1], (int) $request->input('id'),
            (int) $request->input('onay') === 1, (string) $request->input('not'), $request));
    }

    public function ekip(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Bu ekran yalnızca yöneticiye açık.'], 403);
        return $this->sonuc(true, ['ekip' => PersonelProfil::ekip($k[0]), 'donem' => date('Y-m'), 'durumlar' => PersonelProfil::DURUMLAR,
            'mola_bekleyen' => \App\Services\PersonelMola::bekleyenler($k[0])]);
    }

    public function hedef(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Hedefi yalnızca yönetici belirleyebilir.'], 403);
        return $this->sonuc(PersonelProfil::hedefKaydet($k[0], $k[1], (int) $request->input('personel_id'), $request->input('tutar'), $request));
    }

    public function calismalar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['calismalar' => PersonelProfil::calismalar((int) $k[1]->id)]);
    }

    public function calismaEkle(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$request->hasFile('resim')) return $this->sonuc('Fotoğraf seçin.');
        return $this->sonuc(PersonelProfil::calismaEkle($k[0], $k[1], $request->file('resim'), $request->input('aciklama'), $request));
    }

    public function calismaSil(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::calismaSil($k[0], $k[1], (int) $request->input('id'), $request));
    }

    public function yetkiler(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $y = $this->asistanGorusmeYetkili();
        return $this->sonuc(true, ['gruplar' => PersonelProfil::yetkiler($k[0], $y->id), 'yonetici' => $k[2]]);
    }

    /** Uygulama Hakkinda: isletme bilgisi + uygulama ici yasal metinler (her isletmeye otomatik). */
    public function hakkinda(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['isletme' => \App\Services\IsletmeYasalMetin::isletme($k[0]), 'metinler' => \App\Services\IsletmeYasalMetin::metinler($k[0])]);
    }

    public function cihazlar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $bu = (string) $request->input('token');
        $liste = array_map(function ($c) use ($bu) {
            $c->bu_cihaz = $bu !== '' && hash_equals((string) $c->bildirim_id, $bu);
            unset($c->bildirim_id);
            return $c;
        }, PersonelProfil::cihazlar($k[0], (int) $k[1]->id));
        return $this->sonuc(true, ['cihazlar' => $liste]);
    }

    public function cihazKapat(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::cihazKapat($k[0], $k[1], (int) $request->input('id'), $request));
    }
}
