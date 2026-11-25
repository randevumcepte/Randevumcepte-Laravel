<?php

namespace App\Http\Controllers;

use App\Salonlar;
use App\Services\DilSozluk;
use App\Services\MusteriDili;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Musteri dili uclari (mobil + web): /api/v1/dil/* (acik), /api/v1/musteri-profil/dil (Bearer, yalniz kendi dili) */
class MusteriDilController extends Controller
{
    /** Salon sitesi (host) icin sozluk */
    public function webSozluk(Request $request, $dil)
    {
        $request->attributes->set('md_salon', \App\Http\Middleware\MusteriDilWeb::salon($request->getHost()));
        return $this->sozluk($request, $dil);
    }

    /** Markanin pilotta olan salonu (yoksa 0). */
    private function salon(Request $request)
    {
        if ($id = (int) $request->attributes->get('md_salon')) return MusteriDili::acikMi($id) ? $id : 0;
        $bundle = trim((string) $request->input('appBundle'));
        if ($bundle === '' || $bundle === \App\Services\MusteriProfil::ORTAK_BUNDLE) return 0;
        foreach (Salonlar::where('app_bundle', $bundle)->pluck('id') as $id) {
            if (MusteriDili::acikMi($id)) return (int) $id;
        }
        return 0;
    }

    public function ayar(Request $request)
    {
        $salonId = $this->salon($request);
        if (!$salonId) return response()->json(['diller' => [], 'surum' => new \stdClass]);
        $surum = [];
        foreach (MusteriDili::diller() as $d) {
            if ($d['kod'] !== 'tr') $surum[$d['kod']] = MusteriDili::salonSurum($salonId, $d['kod']);
        }
        return response()->json(['diller' => MusteriDili::diller(), 'surum' => $surum]);
    }

    public function sozluk(Request $request, $dil)
    {
        $salonId = $this->salon($request);
        if (!$salonId || $dil === 'tr' || !MusteriDili::gecerli($dil) || !is_file(DilSozluk::dosya($dil))) {
            return response()->json(['hata' => 'Dil bulunamadı.'], 404);
        }
        $j = json_decode(file_get_contents(DilSozluk::dosya($dil)), true);
        return response()->json([
            'dil' => $dil,
            'surum' => MusteriDili::salonSurum($salonId, $dil),
            'sozluk' => MusteriDili::salonSozluk($salonId, $dil) + (array) ($j['sozluk'] ?? []),
        ], 200, [], JSON_UNESCAPED_UNICODE)->header('Cache-Control', 'public, max-age=300');
    }

    /** Giris yapan musteri (personel token'i degil) */
    private function musteri()
    {
        $u = Auth::guard('api')->user();
        return $u instanceof User ? $u : null;
    }

    public function dilim()
    {
        if (!$u = $this->musteri()) return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
        return response()->json(['ok' => 1, 'dil' => MusteriDili::dil($u->id)]);
    }

    public function dilKaydet(Request $request)
    {
        if (!$u = $this->musteri()) return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
        $ok = MusteriDili::kaydet($u->id, (string) $request->input('dil'), 'musteri');
        return response()->json(['ok' => $ok ? 1 : 0, 'dil' => MusteriDili::dil($u->id)]);
    }

    /** Web (online randevu sitesi): giris yapmis musteri dil dugmesinden sectiginde. Cerez zaten tarayicida. */
    public function webKaydet(Request $request)
    {
        $u = Auth::guard('web')->user();
        if ($u instanceof User) MusteriDili::kaydet($u->id, (string) $request->input('dil'), 'musteri');
        return response()->json(['ok' => 1]);
    }
}
