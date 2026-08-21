<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Services\AiKullanimLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Sistem Yonetimi > AI Kredi & Kullanim paneli.
 * Loglardan (ai_kullanim) salon bazinda harcama, tur dagilimi, gunluk akis, cache
 * tasarrufu; kalan kredi = elle girilen yuklenen kredi - tum zaman harcama.
 */
class AiKrediController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    public function index(Request $request)
    {
        $gun = (int) $request->input('gun', 30); // 0 = tumu
        $bas = $gun > 0 ? date('Y-m-d 00:00:00', strtotime('-' . ($gun - 1) . ' days')) : null;

        $ayar = AiKullanimLog::ayar();
        $kur  = ($ayar['kur'] > 0) ? (float) $ayar['kur'] : 40.0;

        $ortak = [
            'title'     => 'AI Kredi & Kullanım',
            'aktifMenu' => 'aikredi',
            'gun'       => $gun,
            'ayar'      => $ayar,
            'kur'       => $kur,
        ];

        if (!\Schema::hasTable('ai_kullanim')) {
            return view('sistemyonetim.v2.ai-kredi', array_merge($ortak, [
                'tabloYok'  => true,
                'ozet'      => ['cagri' => 0, 'gercek' => 0, 'cache' => 0, 'maliyet_usd' => 0, 'bugun_usd' => 0,
                                'girdi' => 0, 'cikti' => 0, 'ort_usd' => 0, 'cache_tasarruf_usd' => 0],
                'turler'    => collect(), 'salonlar' => collect(), 'gunluk' => collect(),
                'tumHarcamaUsd' => 0, 'kalanUsd' => (float) $ayar['yuklenen_usd'],
            ]));
        }

        // Donem filtreli taze sorgu (join'lerde belirsizlik olmasin diye kolon nitelikli).
        $q = function () use ($bas) {
            $x = DB::table('ai_kullanim');
            if ($bas) $x->where('ai_kullanim.created_at', '>=', $bas);
            return $x;
        };

        $toplamCagri = (int) $q()->count();
        $gercekCagri = (int) $q()->where('cache', 0)->count();
        $cacheCagri  = (int) $q()->where('cache', 1)->count();
        $maliyetUsd  = (float) $q()->sum('maliyet_usd');
        $girdiTk     = (int) $q()->sum('girdi_token');
        $ciktiTk     = (int) $q()->sum('cikti_token');

        $bugunUsd = (float) DB::table('ai_kullanim')
            ->where('ai_kullanim.created_at', '>=', date('Y-m-d 00:00:00'))->sum('maliyet_usd');

        // Kalan kredi: TUM ZAMAN harcamaya gore (yuklenen birikimli).
        $tumHarcamaUsd = (float) DB::table('ai_kullanim')->sum('maliyet_usd');
        $kalanUsd = (float) $ayar['yuklenen_usd'] - $tumHarcamaUsd;

        // Cache tasarrufu tahmini: gercek cagri ort. maliyeti x cache adedi.
        $ortUsd = $gercekCagri > 0 ? $maliyetUsd / $gercekCagri : 0;
        $cacheTasarruf = $ortUsd * $cacheCagri;

        $turler = $q()->select('tur',
                DB::raw('COUNT(*) as cagri'),
                DB::raw('SUM(maliyet_usd) as maliyet'),
                DB::raw('SUM(girdi_token) as girdi'),
                DB::raw('SUM(cikti_token) as cikti'),
                DB::raw('SUM(cache) as cache'))
            ->groupBy('tur')->orderByDesc('maliyet')->get();

        $salonlar = $q()
            ->leftJoin('salonlar', 'salonlar.id', '=', 'ai_kullanim.salon_id')
            ->select('ai_kullanim.salon_id',
                DB::raw("COALESCE(salonlar.salon_adi,'— (sistem)') as salon_adi"),
                DB::raw('COUNT(*) as cagri'),
                DB::raw('SUM(ai_kullanim.maliyet_usd) as maliyet'),
                DB::raw('SUM(ai_kullanim.girdi_token + ai_kullanim.cikti_token) as token'),
                DB::raw('SUM(ai_kullanim.cache) as cache'),
                DB::raw('MAX(ai_kullanim.created_at) as son'))
            ->groupBy('ai_kullanim.salon_id', 'salonlar.salon_adi')
            ->orderByDesc('maliyet')->limit(300)->get();

        $gunluk = $q()->select(
                DB::raw('DATE(ai_kullanim.created_at) as gun'),
                DB::raw('COUNT(*) as cagri'),
                DB::raw('SUM(maliyet_usd) as maliyet'))
            ->groupBy(DB::raw('DATE(ai_kullanim.created_at)'))
            ->orderBy('gun', 'desc')->limit(30)->get();

        return view('sistemyonetim.v2.ai-kredi', array_merge($ortak, [
            'tabloYok' => false,
            'ozet' => [
                'cagri'   => $toplamCagri,
                'gercek'  => $gercekCagri,
                'cache'   => $cacheCagri,
                'maliyet_usd' => $maliyetUsd,
                'bugun_usd'   => $bugunUsd,
                'girdi'   => $girdiTk,
                'cikti'   => $ciktiTk,
                'ort_usd' => $ortUsd,
                'cache_tasarruf_usd' => $cacheTasarruf,
            ],
            'turler'   => $turler,
            'salonlar' => $salonlar,
            'gunluk'   => $gunluk,
            'tumHarcamaUsd' => $tumHarcamaUsd,
            'kalanUsd' => $kalanUsd,
        ]));
    }

    /** Yuklenen kredi + kur guncelle. */
    public function krediYukle(Request $request)
    {
        $this->validate($request, [
            'yuklenen' => 'required|numeric|min:0',
            'kur'      => 'nullable|numeric|min:1',
        ]);
        $ayar = AiKullanimLog::ayar();
        AiKullanimLog::ayarKaydet(
            $request->input('yuklenen'),
            $request->input('kur', $ayar['kur'])
        );
        return redirect('/sistemyonetim/v2/ai-kredi')->with('ok', 'Kredi ayarı güncellendi.');
    }
}
