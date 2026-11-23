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
                'salonTur'  => [], 'gunlukOrtUsd' => 0, 'kalanGun' => null,
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

        // Prompt-cache kolonlari yoksa 0 doner; eski (kolonlar oncesi) kayitlar NULL -> 'eski' sayilir.
        $detay = AiKullanimLog::semaGaranti();
        $turler = $q()->select('tur',
                DB::raw('COUNT(*) as cagri'),
                DB::raw('SUM(maliyet_usd) as maliyet'),
                DB::raw('SUM(girdi_token) as girdi'),
                DB::raw('SUM(cikti_token) as cikti'),
                DB::raw('SUM(cache) as cache'),
                DB::raw($detay ? 'SUM(COALESCE(cache_okuma_token,0)) as okuma' : '0 as okuma'),
                DB::raw($detay ? 'SUM(COALESCE(cache_yazma_token,0)) as yazma' : '0 as yazma'),
                DB::raw($detay ? 'SUM(CASE WHEN cache_okuma_token IS NULL THEN 1 ELSE 0 END) as eski' : 'COUNT(*) as eski'),
                DB::raw('MAX(model) as model'))
            ->groupBy('tur')->orderByDesc('maliyet')->get();

        // Salon x kanal: her salonun parasi hangi kanala gidiyor
        $salonTur = [];
        foreach ($q()->select('salon_id', 'tur', DB::raw('SUM(maliyet_usd) as maliyet'), DB::raw('COUNT(*) as cagri'))
                     ->groupBy('salon_id', 'tur')->get() as $r) {
            $salonTur[(int) $r->salon_id][] = $r;
        }
        foreach ($salonTur as &$liste) {
            usort($liste, function ($a, $b) { return $b->maliyet <=> $a->maliyet; });
        }
        unset($liste);

        // Bu hizla kredi kac gun yeter: son 7 gunun (veri daha yeniyse ilk kayittan beri) gunluk ortalamasi
        $ilk = DB::table('ai_kullanim')->min('created_at');
        $ortGunSayisi = $ilk ? min(7, max(1, (int) floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($ilk)))) / 86400) + 1)) : 7;
        $son7Usd = (float) DB::table('ai_kullanim')
            ->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-' . ($ortGunSayisi - 1) . ' days')))->sum('maliyet_usd');
        $gunlukOrtUsd = $son7Usd / $ortGunSayisi;
        $kalanGun = ((float) $ayar['yuklenen_usd'] > 0 && $gunlukOrtUsd > 0) ? (int) floor(max(0, $kalanUsd) / $gunlukOrtUsd) : null;

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
            'salonTur' => $salonTur,
            'gunluk'   => $gunluk,
            'gunlukOrtUsd' => $gunlukOrtUsd,
            'kalanGun' => $kalanGun,
            'tumHarcamaUsd' => $tumHarcamaUsd,
            'kalanUsd' => $kalanUsd,
        ]));
    }

    /**
     * Isletme detayi: kanal dagilimi, gun gun (kanal kirilimli), en pahali WhatsApp sohbetleri ve telefon
     * gorusmeleri, son AI islemleri. ai_kullanim.salon_id = WA'da isletme_id, santralde sube_id.
     */
    public function isletme(Request $request, $id)
    {
        $id  = (int) $id;
        $gun = (int) $request->input('gun', 30);
        $bas = $gun > 0 ? date('Y-m-d 00:00:00', strtotime('-' . ($gun - 1) . ' days')) : null;
        $ayar = AiKullanimLog::ayar();
        $kur  = ($ayar['kur'] > 0) ? (float) $ayar['kur'] : 40.0;

        $salon = DB::table('salonlar')->where('id', $id)->first();
        if (!$salon && !DB::table('ai_kullanim')->where('salon_id', $id)->exists()) abort(404);

        $q = function () use ($bas, $id) {
            $x = DB::table('ai_kullanim')->where('salon_id', $id);
            if ($bas) $x->where('created_at', '>=', $bas);
            return $x;
        };
        $detay = AiKullanimLog::semaGaranti();

        $maliyetUsd = (float) $q()->sum('maliyet_usd');
        $tumDonemUsd = (float) ($bas ? DB::table('ai_kullanim')->where('created_at', '>=', $bas)->sum('maliyet_usd') : DB::table('ai_kullanim')->sum('maliyet_usd'));
        $ozet = [
            'cagri'       => (int) $q()->count(),
            'gercek'      => (int) $q()->where('cache', 0)->count(),
            'maliyet_usd' => $maliyetUsd,
            'bugun_usd'   => (float) DB::table('ai_kullanim')->where('salon_id', $id)->where('created_at', '>=', date('Y-m-d 00:00:00'))->sum('maliyet_usd'),
            'pay'         => $tumDonemUsd > 0 ? $maliyetUsd / $tumDonemUsd * 100 : 0,
            'son'         => DB::table('ai_kullanim')->where('salon_id', $id)->max('created_at'),
        ];

        $turler = $q()->select('tur',
                DB::raw('COUNT(*) as cagri'),
                DB::raw('SUM(maliyet_usd) as maliyet'),
                DB::raw('SUM(girdi_token) as girdi'),
                DB::raw('SUM(cikti_token) as cikti'),
                DB::raw('SUM(cache) as cache'),
                DB::raw($detay ? 'SUM(COALESCE(cache_okuma_token,0)) as okuma' : '0 as okuma'),
                DB::raw($detay ? 'SUM(COALESCE(cache_yazma_token,0)) as yazma' : '0 as yazma'),
                DB::raw('MAX(model) as model'))
            ->groupBy('tur')->orderByDesc('maliyet')->get();

        // Gun gun, kanal kirilimli (son 30 gun)
        $gunluk = [];
        foreach ($q()->select(DB::raw('DATE(created_at) as gun'), 'tur', DB::raw('COUNT(*) as cagri'), DB::raw('SUM(maliyet_usd) as maliyet'))
                     ->groupBy(DB::raw('DATE(created_at)'), 'tur')->orderBy('gun', 'desc')->get() as $r) {
            if (!isset($gunluk[$r->gun])) {
                if (count($gunluk) >= 30) break;
                $gunluk[$r->gun] = ['cagri' => 0, 'maliyet' => 0.0, 'tur' => []];
            }
            $gunluk[$r->gun]['cagri'] += (int) $r->cagri;
            $gunluk[$r->gun]['maliyet'] += (float) $r->maliyet;
            $gunluk[$r->gun]['tur'][$r->tur] = (float) $r->maliyet;
        }

        // En pahali WhatsApp sohbetleri (oturum toplam maliyeti; donemde mesaji olanlar)
        $waSohbetler = collect(); $waAdet = 0;
        try {
            if (\Schema::hasTable('wa_asistan_oturumlari')) {
                $w = DB::table('wa_asistan_oturumlari')->where('isletme_id', $id)->where('maliyet_usd', '>', 0);
                if ($bas) $w->where('son_mesaj_at', '>=', $bas);
                $waAdet = (int) (clone $w)->count();
                $waSohbetler = $w->orderByDesc('maliyet_usd')->limit(15)
                    ->get(['id', 'ad', 'telefon', 'musteri_tel', 'mesaj_sayisi', 'cevap_sayisi', 'sonuc', 'maliyet_usd', 'son_mesaj_at', 'created_at']);
            }
        } catch (\Throwable $e) {}

        // En pahali telefon gorusmeleri
        $santralGorusmeler = collect(); $santralAdet = 0;
        try {
            if (\Schema::hasTable('santral_oturumlari') && \Schema::hasColumn('santral_oturumlari', 'maliyet_usd')) {
                $s = DB::table('santral_oturumlari')->where('sube_id', $id)->where('maliyet_usd', '>', 0);
                if ($bas) $s->where('created_at', '>=', $bas);
                $santralAdet = (int) (clone $s)->count();
                $santralGorusmeler = $s->orderByDesc('maliyet_usd')->limit(15)
                    ->get(['id', 'telefon', 'sure_sn', 'sonuc', 'yon', 'maliyet_usd', 'created_at']);
            }
        } catch (\Throwable $e) {}

        // Son AI islemleri (tek tek)
        $sonIslemler = $q()->orderByDesc('id')->limit(60)->get(array_merge(
            ['created_at', 'tur', 'model', 'girdi_token', 'cikti_token', 'maliyet_usd', 'cache'],
            $detay ? ['cache_okuma_token', 'cache_yazma_token'] : []));

        return view('sistemyonetim.v2.ai-kredi-isletme', [
            'title'     => 'AI Kullanım · ' . ($salon->salon_adi ?? ('#' . $id)),
            'aktifMenu' => 'aikredi',
            'gun'       => $gun,
            'kur'       => $kur,
            'salonId'   => $id,
            'salonAdi'  => $salon->salon_adi ?? ('İşletme #' . $id),
            'ozet'      => $ozet,
            'turler'    => $turler,
            'donemUsd'  => $maliyetUsd,
            'gunluk'    => $gunluk,
            'waSohbetler'       => $waSohbetler,
            'santralGorusmeler' => $santralGorusmeler,
            'sonIslemler'       => $sonIslemler,
            'waAdet'            => $waAdet,
            'santralAdet'       => $santralAdet,
        ]);
    }

    /** Toplam yuklenen kredi + kur + esik guncelle (mutlak deger). */
    public function krediYukle(Request $request)
    {
        $this->validate($request, [
            'yuklenen' => 'required|numeric|min:0',
            'kur'      => 'nullable|numeric|min:1',
            'esik'     => 'nullable|numeric|min:0',
        ]);
        $ayar = AiKullanimLog::ayar();
        AiKullanimLog::ayarKaydet(
            $request->input('yuklenen'),
            $request->input('kur', $ayar['kur']),
            $request->input('esik', $ayar['esik_usd'])
        );
        return redirect('/sistemyonetim/v2/ai-kredi')->with('ok', 'Kredi ayarı güncellendi.');
    }

    /** Mevcut yuklenen krediye EKLE (yeni yukleme yapinca toplamla ugrasma). */
    public function krediEkle(Request $request)
    {
        $this->validate($request, ['eklenen' => 'required|numeric|min:0.01']);
        $ayar = AiKullanimLog::ayar();
        $yeniToplam = (float) $ayar['yuklenen_usd'] + (float) $request->input('eklenen');
        AiKullanimLog::ayarKaydet($yeniToplam, $ayar['kur'], $ayar['esik_usd']);
        return redirect('/sistemyonetim/v2/ai-kredi')
            ->with('ok', '$' . number_format((float) $request->input('eklenen'), 2, ',', '.') . ' eklendi. Yeni toplam: $' . number_format($yeniToplam, 2, ',', '.'));
    }

    /** Test: dusuk kredi alarmini WhatsApp'tan HEMEN gonder (numara/oturum dogrulama). */
    public function testAlarm()
    {
        $r = AiKullanimLog::alarmGonder(AiKullanimLog::kalan(), (float) AiKullanimLog::ayar()['esik_usd'], AiKullanimLog::ayar()['kur']);
        $ok = !empty($r['ok']);
        return redirect('/sistemyonetim/v2/ai-kredi')->with('ok',
            $ok ? 'Test alarmı gönderildi (WhatsApp + SMS). Gelmezse Sistem WhatsApp numarası/aktifliğini kontrol edin.'
                : 'Alarm gönderilemedi — Sistem WhatsApp ayarını (numara + aktif) kontrol edin.');
    }
}
