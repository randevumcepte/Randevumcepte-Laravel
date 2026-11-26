<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Services\BildirimDurumSemasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sistem Yonetimi > Bildirim Sorunlari.
 * Mobil uygulama, cihazda bildirim calismiyorsa (izin kapali / FCM token alinamiyor) bunu kullaniciya
 * gostermeden POST /api/v1/bildirim/durum ile musteri_portfoy / salon_personelleri satirina yazar.
 * Bu sayfa o satirlari listeler; sorun cozulunce (uygulama 'ok' yazinca) listeden kendiliginden duser.
 */
class BildirimSorunController extends Controller
{
    const ETIKET = [
        'izin_kapali' => 'İzin kapalı',
        'token_yok'   => 'Token alınamıyor',
        'ok'          => 'Çalışıyor',
    ];

    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    public function index(Request $request)
    {
        $semaHata = null;
        try {
            BildirimDurumSemasi::ensure();
        } catch (\Throwable $e) {
            $semaHata = $e->getMessage();
            Log::warning('bildirim sorunlari sema: ' . $semaHata);
        }

        $tip   = in_array($request->get('tip'), ['musteri', 'personel', 'hepsi'], true) ? $request->get('tip') : 'hepsi';
        $durum = in_array($request->get('durum'), ['sorunlu', 'izin_kapali', 'token_yok', 'ok'], true) ? $request->get('durum') : 'sorunlu';
        $q     = trim((string) $request->get('q', ''));
        $salonId = (int) $request->get('salon_id') ?: null;

        $musteriler = collect();
        $personeller = collect();
        $ozet = ['musteri' => [], 'personel' => []];
        $salonOzet = collect();

        if ($semaHata === null) {
            $durumFiltre = function ($sorgu, $tablo) use ($durum) {
                if ($durum === 'sorunlu') $sorgu->whereIn("$tablo.bildirim_durum", ['izin_kapali', 'token_yok']);
                else $sorgu->where("$tablo.bildirim_durum", $durum);
            };

            if ($tip !== 'personel') {
                $s = DB::table('musteri_portfoy as mp')
                    ->join('users as u', 'u.id', '=', 'mp.user_id')
                    ->join('salonlar as s', 's.id', '=', 'mp.salon_id')
                    ->select('mp.id', 'mp.salon_id', 'u.name as ad', 'u.cep_telefon as telefon', 's.salon_adi',
                        'mp.bildirim_durum', 'mp.bildirim_durum_tarihi', 'mp.bildirim_sorun_baslangic',
                        'mp.bildirim_hata', 'mp.bildirim_cihaz');
                $durumFiltre($s, 'mp');
                if ($salonId) $s->where('mp.salon_id', $salonId);
                if ($q !== '') $s->where(function ($w) use ($q) {
                    $w->where('u.name', 'like', "%$q%")->orWhere('s.salon_adi', 'like', "%$q%")->orWhere('u.cep_telefon', 'like', "%$q%");
                });
                $musteriler = $s->orderBy('mp.bildirim_durum_tarihi', 'desc')->limit(300)->get();
            }

            if ($tip !== 'musteri') {
                $s = DB::table('salon_personelleri as sp')
                    ->join('salonlar as s', 's.id', '=', 'sp.salon_id')
                    ->select('sp.id', 'sp.salon_id', 'sp.personel_adi as ad', 'sp.cep_telefon as telefon', 's.salon_adi',
                        'sp.bildirim_durum', 'sp.bildirim_durum_tarihi', 'sp.bildirim_sorun_baslangic',
                        'sp.bildirim_hata', 'sp.bildirim_cihaz');
                $durumFiltre($s, 'sp');
                if ($salonId) $s->where('sp.salon_id', $salonId);
                if ($q !== '') $s->where(function ($w) use ($q) {
                    $w->where('sp.personel_adi', 'like', "%$q%")->orWhere('s.salon_adi', 'like', "%$q%")->orWhere('sp.cep_telefon', 'like', "%$q%");
                });
                $personeller = $s->orderBy('sp.bildirim_durum_tarihi', 'desc')->limit(300)->get();
            }

            foreach (['musteri' => 'musteri_portfoy', 'personel' => 'salon_personelleri'] as $k => $tablo) {
                $ozet[$k] = DB::table($tablo)->whereNotNull('bildirim_durum')
                    ->groupBy('bildirim_durum')->select('bildirim_durum', DB::raw('COUNT(*) as adet'))
                    ->pluck('adet', 'bildirim_durum')->map(function ($v) { return (int) $v; })->all();
            }

            // En cok sorunlu subeler (iki tablo birlikte)
            $birlesik = DB::table('musteri_portfoy')->select('salon_id')->whereIn('bildirim_durum', ['izin_kapali', 'token_yok'])
                ->unionAll(DB::table('salon_personelleri')->select('salon_id')->whereIn('bildirim_durum', ['izin_kapali', 'token_yok']));
            $salonOzet = DB::table(DB::raw('(' . $birlesik->toSql() . ') as x'))
                ->mergeBindings($birlesik)
                ->join('salonlar as s', 's.id', '=', 'x.salon_id')
                ->groupBy('x.salon_id', 's.salon_adi')
                ->select('x.salon_id', 's.salon_adi', DB::raw('COUNT(*) as adet'))
                ->orderBy('adet', 'desc')->limit(15)->get();
        }

        return view('sistemyonetim.v2.bildirim-sorunlari', [
            'title'       => 'Bildirim Sorunları',
            'aktifMenu'   => 'bildirimsorun',
            'musteriler'  => $musteriler,
            'personeller' => $personeller,
            'ozet'        => $ozet,
            'salonOzet'   => $salonOzet,
            'tip'         => $tip,
            'durum'       => $durum,
            'q'           => $q,
            'salonId'     => $salonId,
            'etiket'      => self::ETIKET,
            'semaHata'    => $semaHata,
        ]);
    }
}
