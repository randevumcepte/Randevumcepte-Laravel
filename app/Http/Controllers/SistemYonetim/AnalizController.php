<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Salonlar;
use App\SistemYoneticileri;
use App\SistemYonetim\Audit;
use App\SistemYonetim\AuditLog;
use App\SistemYonetim\DestekTalebi;
use App\SistemYonetim\DestekMesaji;
use App\SistemYonetim\HazirCevap;
use App\SistemYonetim\SaglikSkoru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    private function user() { return Auth::guard('sistemyonetim')->user(); }
    private function rol()
    {
        $u = $this->user();
        if (!empty($u->rol)) return $u->rol;
        return $u->admin == 1 ? 'super_admin' : 'destek';
    }
    private function gerektir($izinler) { if (!in_array($this->rol(), $izinler, true)) abort(403); }

    /* ============================================================
     * RISK ALTINDAKI SALONLAR
     * ============================================================ */
    public function riskliSalonlar()
    {
        $list = SaglikSkoru::riskAltindakiler(80);

        // MT id -> isim eslesme
        $mtIds = array_filter(array_column($list, 'mt_id'));
        $mtMap = SistemYoneticileri::whereIn('id', $mtIds)->pluck('name', 'id');

        return view('sistemyonetim.v2.riskli-salonlar', [
            'title' => 'Risk Altındaki Salonlar',
            'aktifMenu' => 'risk',
            'list' => $list,
            'mtMap' => $mtMap,
        ]);
    }

    /* ============================================================
     * EKIP PERFORMANSI (SLA)
     * ============================================================ */
    public function ekipPerformansi(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);

        $gunler = (int) $request->get('gun', 30);
        if ($gunler < 1 || $gunler > 365) $gunler = 30;
        $sinir = date('Y-m-d', strtotime("-{$gunler} days"));

        $ekip = SistemYoneticileri::where('aktif', 1)->orderBy('name')->get();

        $perf = [];
        foreach ($ekip as $u) {
            // Ticket istatistikleri
            $row = DB::table('sistemyonetim_destek_talepleri')
                ->where('atanan_user_id', $u->id)
                ->where('created_at', '>=', $sinir)
                ->selectRaw("
                    COUNT(*) as toplam,
                    SUM(CASE WHEN durum IN ('cozumlendi','kapali') THEN 1 ELSE 0 END) as cozulen,
                    SUM(CASE WHEN durum NOT IN ('cozumlendi','kapali') THEN 1 ELSE 0 END) as acik,
                    AVG(CASE WHEN ilk_yanit_tarihi IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, ilk_yanit_tarihi) END) as ort_yanit_dk,
                    AVG(CASE WHEN cozumlenme_tarihi IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, cozumlenme_tarihi) END) as ort_cozum_saat
                ")
                ->first();

            // Yanit sayisi (mesaj olarak attigi)
            $mesajSayisi = DB::table('sistemyonetim_destek_mesajlari')
                ->where('user_id', $u->id)
                ->where('created_at', '>=', $sinir)
                ->count();

            // Aktivite sayisi
            $aktiviteSayisi = DB::table('sistemyonetim_audit_log')
                ->where('user_id', $u->id)
                ->where('created_at', '>=', $sinir)
                ->count();

            // Salon hesabina giris
            $impCount = DB::table('sistemyonetim_impersonation_loglari')
                ->where('user_id', $u->id)
                ->where('baslangic_tarihi', '>=', $sinir)
                ->count();

            $perf[] = [
                'user'     => $u,
                'rol'      => $u->rol ?: ($u->admin == 1 ? 'super_admin' : 'destek'),
                'toplam'   => (int) ($row->toplam ?? 0),
                'cozulen'  => (int) ($row->cozulen ?? 0),
                'acik'     => (int) ($row->acik ?? 0),
                'ort_yanit_dk'   => $row->ort_yanit_dk ? round($row->ort_yanit_dk, 1) : null,
                'ort_cozum_saat' => $row->ort_cozum_saat ? round($row->ort_cozum_saat, 1) : null,
                'mesaj_sayisi'   => $mesajSayisi,
                'aktivite_sayisi'=> $aktiviteSayisi,
                'imp_count'      => $impCount,
            ];
        }

        // Genel SLA metrikleri
        $genel = DB::table('sistemyonetim_destek_talepleri')
            ->where('created_at', '>=', $sinir)
            ->selectRaw("
                COUNT(*) as toplam,
                SUM(CASE WHEN durum IN ('cozumlendi','kapali') THEN 1 ELSE 0 END) as cozulen,
                AVG(CASE WHEN ilk_yanit_tarihi IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, ilk_yanit_tarihi) END) as ort_yanit_dk,
                AVG(CASE WHEN cozumlenme_tarihi IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, cozumlenme_tarihi) END) as ort_cozum_saat
            ")
            ->first();

        return view('sistemyonetim.v2.ekip-performans', [
            'title' => 'Ekip Performansı',
            'aktifMenu' => 'performans',
            'gunler' => $gunler,
            'perf' => $perf,
            'genel' => $genel,
        ]);
    }

    /* ============================================================
     * HAZIR CEVAPLAR (snippet)
     * ============================================================ */
    public function hazirCevaplar()
    {
        $list = HazirCevap::orderByDesc('kullanim_sayisi')->orderBy('id', 'desc')->paginate(40);
        return view('sistemyonetim.v2.hazir-cevaplar', [
            'title' => 'Hazır Cevaplar',
            'aktifMenu' => 'hazircevap',
            'list' => $list,
        ]);
    }

    public function hazirCevapYeni()
    {
        return view('sistemyonetim.v2.hazir-cevap-form', [
            'title' => 'Yeni Hazır Cevap',
            'aktifMenu' => 'hazircevap',
            'item' => null,
        ]);
    }

    public function hazirCevapKaydet(Request $request)
    {
        $this->validate($request, [
            'baslik'   => 'required|min:2|max:200',
            'icerik'   => 'required|min:2',
            'kategori' => 'required|in:genel,teknik,odeme,egitim,iade,kapanis',
            'kisayol'  => 'nullable|max:30',
        ]);

        $i = HazirCevap::create([
            'baslik'  => $request->baslik,
            'icerik'  => $request->icerik,
            'kategori'=> $request->kategori,
            'kisayol' => $request->kisayol,
            'aktif'   => $request->aktif ? 1 : 1,
            'olusturan_user_id'   => $this->user()->id,
            'olusturan_user_name' => $this->user()->name,
        ]);

        Audit::log('hazir_cevap_olustur', 'hazir_cevap', $i->id, $i->baslik);
        return redirect('/sistemyonetim/v2/hazir-cevap')->with('basari', 'Hazır cevap eklendi.');
    }

    public function hazirCevapDuzenle($id)
    {
        $i = HazirCevap::findOrFail($id);
        return view('sistemyonetim.v2.hazir-cevap-form', [
            'title' => $i->baslik,
            'aktifMenu' => 'hazircevap',
            'item' => $i,
        ]);
    }

    public function hazirCevapGuncelle(Request $request, $id)
    {
        $i = HazirCevap::findOrFail($id);
        $this->validate($request, [
            'baslik'   => 'required|min:2|max:200',
            'icerik'   => 'required|min:2',
            'kategori' => 'required|in:genel,teknik,odeme,egitim,iade,kapanis',
        ]);
        $i->fill([
            'baslik'  => $request->baslik,
            'icerik'  => $request->icerik,
            'kategori'=> $request->kategori,
            'kisayol' => $request->kisayol,
            'aktif'   => $request->aktif ? 1 : 0,
        ])->save();
        Audit::log('hazir_cevap_guncelle', 'hazir_cevap', $i->id, $i->baslik);
        return redirect('/sistemyonetim/v2/hazir-cevap')->with('basari', 'Güncellendi.');
    }

    public function hazirCevapSil($id)
    {
        $i = HazirCevap::findOrFail($id);
        Audit::log('hazir_cevap_sil', 'hazir_cevap', $i->id, $i->baslik);
        $i->delete();
        return redirect()->back()->with('basari', 'Silindi.');
    }

    /**
     * Ticket cevap formu icin AJAX ile aktif hazir cevaplari listele.
     */
    public function hazirCevapJson(Request $request)
    {
        $q = trim($request->get('q', ''));
        $kategori = $request->get('kategori');
        $query = HazirCevap::where('aktif', 1)->orderByDesc('kullanim_sayisi');
        if ($kategori) $query->where('kategori', $kategori);
        if ($q !== '') $query->where(function ($w) use ($q) {
            $w->where('baslik', 'like', "%$q%")
              ->orWhere('kisayol', 'like', "%$q%")
              ->orWhere('icerik', 'like', "%$q%");
        });
        return response()->json($query->limit(30)->get(['id','baslik','icerik','kategori','kisayol']));
    }

    public function hazirCevapKullan($id)
    {
        try {
            HazirCevap::where('id', $id)->increment('kullanim_sayisi');
        } catch (\Exception $e) {}
        return response()->json(['ok' => 1]);
    }

    /* ============================================================
     * DASHBOARD CHART JSON
     * ============================================================ */
    public function dashboardChartData(Request $request)
    {
        $gun = (int) $request->get('gun', 30);
        if ($gun < 7 || $gun > 90) $gun = 30;

        $tarihler = [];
        $salonSeri = [];
        $randevuSeri = [];
        $ticketSeri = [];

        for ($i = $gun - 1; $i >= 0; $i--) {
            $g = date('Y-m-d', strtotime("-$i days"));
            $tarihler[] = date('d.m', strtotime($g));

            $salonSeri[] = (int) DB::table('salonlar')->whereDate('created_at', $g)->count();

            try {
                $randevuSeri[] = (int) DB::table('randevular')->whereDate('created_at', $g)->count();
            } catch (\Exception $e) { $randevuSeri[] = 0; }

            try {
                $ticketSeri[] = (int) DB::table('sistemyonetim_destek_talepleri')->whereDate('created_at', $g)->count();
            } catch (\Exception $e) { $ticketSeri[] = 0; }
        }

        // Ticket kategori dagilimi (son 30 gun)
        $kategoriDagilim = DB::table('sistemyonetim_destek_talepleri')
            ->where('created_at', '>=', date('Y-m-d', strtotime("-{$gun} days")))
            ->selectRaw('kategori, COUNT(*) as adet')
            ->groupBy('kategori')
            ->pluck('adet', 'kategori');

        // Durum dagilimi (mevcut)
        $durumDagilim = DB::table('sistemyonetim_destek_talepleri')
            ->selectRaw('durum, COUNT(*) as adet')
            ->groupBy('durum')
            ->pluck('adet', 'durum');

        return response()->json([
            'tarihler'       => $tarihler,
            'salon'          => $salonSeri,
            'randevu'        => $randevuSeri,
            'ticket'         => $ticketSeri,
            'kategori_dagilim' => $kategoriDagilim,
            'durum_dagilim'  => $durumDagilim,
        ]);
    }
}
