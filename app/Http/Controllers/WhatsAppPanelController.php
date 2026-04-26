<?php

namespace App\Http\Controllers;

use App\Salonlar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppPanelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    public function index()
    {
        return view('superadmin.whatsapp_panel', [
            'title' => 'WhatsApp Yönetim Paneli | randevumcepte.com.tr',
            'pageindex' => 99,
        ]);
    }

    /**
     * Dashboard üst kart verileri: bugün/hafta/ay + başarı oranı + aktif oturum
     */
    public function dashboardData(Request $request)
    {
        $today = Carbon::today();
        $weekStart = Carbon::today()->subDays(6);
        $monthStart = Carbon::today()->subDays(29);

        $aktifSalon = Salonlar::where('whatsapp_aktif', 1)
            ->where('whatsapp_durum', 'connected')->count();
        $banRiskSalon = Salonlar::whereIn('whatsapp_durum', ['banned-or-loggedout', 'auto-paused-ban-risk', 'rate-limited'])->count();

        $bugunToplam = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', $today)->count();
        $bugunBasari = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', $today)->where('durum', 1)->count();
        $bugunFail = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', $today)->where('durum', 2)->count();
        $bugunFallback = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', $today)->where('durum', 3)->count();

        $haftaToplam = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', '>=', $weekStart)->count();
        $haftaBasari = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', '>=', $weekStart)->where('durum', 1)->count();

        $ayToplam = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', '>=', $monthStart)->count();
        $ayBasari = DB::table('whatsapp_gonderim_loglari')->whereDate('created_at', '>=', $monthStart)->where('durum', 1)->count();

        $basariOrani = $haftaToplam > 0 ? round(($haftaBasari / $haftaToplam) * 100, 1) : 0;

        return response()->json([
            'aktifSalon' => $aktifSalon,
            'banRiskSalon' => $banRiskSalon,
            'bugun' => [
                'toplam' => $bugunToplam,
                'basari' => $bugunBasari,
                'fail' => $bugunFail,
                'fallback' => $bugunFallback,
            ],
            'hafta' => [
                'toplam' => $haftaToplam,
                'basari' => $haftaBasari,
            ],
            'ay' => [
                'toplam' => $ayToplam,
                'basari' => $ayBasari,
            ],
            'basariOrani' => $basariOrani,
        ]);
    }

    /**
     * Salon bazlı detay tablosu — bağlantı durumu, günlük/toplam mesaj, son hata
     */
    public function salonlarData(Request $request)
    {
        $bugun = Carbon::today();
        $hafta = Carbon::today()->subDays(6);

        // Tüm WhatsApp aktif olmuş salonları ve son 30 gün log'u olanları getir
        $salonlar = Salonlar::query()
            ->select('id', 'salon_adi', 'whatsapp_aktif', 'whatsapp_durum', 'whatsapp_numara',
                'whatsapp_baglanti_tarihi', 'whatsapp_warmup_baslangic', 'whatsapp_son_hata',
                'whatsapp_gunluk_limit')
            ->where(function ($q) {
                $q->where('whatsapp_aktif', 1)
                  ->orWhereNotNull('whatsapp_durum')
                  ->orWhereExists(function ($sub) {
                      $sub->select(DB::raw(1))
                          ->from('whatsapp_gonderim_loglari')
                          ->whereRaw('whatsapp_gonderim_loglari.salon_id = salonlar.id')
                          ->whereDate('whatsapp_gonderim_loglari.created_at', '>=', Carbon::today()->subDays(30));
                  });
            })
            ->orderBy('whatsapp_aktif', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $salonIds = $salonlar->pluck('id')->toArray();

        // Bugünkü ve haftalık istatistikler — tek sorguda topla
        $bugunStats = DB::table('whatsapp_gonderim_loglari')
            ->select('salon_id', 'durum', DB::raw('COUNT(*) as adet'))
            ->whereIn('salon_id', $salonIds)
            ->whereDate('created_at', $bugun)
            ->groupBy('salon_id', 'durum')->get()
            ->groupBy('salon_id');

        $haftaStats = DB::table('whatsapp_gonderim_loglari')
            ->select('salon_id', DB::raw('COUNT(*) as adet'))
            ->whereIn('salon_id', $salonIds)
            ->whereDate('created_at', '>=', $hafta)
            ->groupBy('salon_id')->get()
            ->keyBy('salon_id');

        $sonGonderim = DB::table('whatsapp_gonderim_loglari')
            ->select('salon_id', DB::raw('MAX(created_at) as son_tarih'))
            ->whereIn('salon_id', $salonIds)
            ->groupBy('salon_id')->get()
            ->keyBy('salon_id');

        $rows = [];
        foreach ($salonlar as $s) {
            $bugunSalon = $bugunStats->get($s->id, collect());
            $bugunBasari = (int) ($bugunSalon->firstWhere('durum', 1)->adet ?? 0);
            $bugunFail = (int) ($bugunSalon->firstWhere('durum', 2)->adet ?? 0);
            $bugunFallback = (int) ($bugunSalon->firstWhere('durum', 3)->adet ?? 0);
            $bugunToplam = $bugunBasari + $bugunFail + $bugunFallback;

            $rows[] = [
                'id' => $s->id,
                'salon_adi' => $s->salon_adi,
                'aktif' => (int) $s->whatsapp_aktif,
                'durum' => $s->whatsapp_durum,
                'numara' => $s->whatsapp_numara,
                'baglanti_tarihi' => optional($s->whatsapp_baglanti_tarihi)->format('Y-m-d H:i'),
                'warmup_baslangic' => optional($s->whatsapp_warmup_baslangic)->format('Y-m-d'),
                'son_hata' => $s->whatsapp_son_hata,
                'gunluk_limit' => (int) ($s->whatsapp_gunluk_limit ?: 150),
                'bugun_basari' => $bugunBasari,
                'bugun_fail' => $bugunFail,
                'bugun_fallback' => $bugunFallback,
                'bugun_toplam' => $bugunToplam,
                'hafta_toplam' => (int) optional($haftaStats->get($s->id))->adet ?? 0,
                'son_gonderim' => optional($sonGonderim->get($s->id))->son_tarih,
            ];
        }

        return response()->json(['rows' => $rows]);
    }

    /**
     * Mesaj log tablosu — filtre + sayfalama
     */
    public function loglarData(Request $request)
    {
        $q = DB::table('whatsapp_gonderim_loglari as wl')
            ->leftJoin('salonlar as s', 's.id', '=', 'wl.salon_id')
            ->leftJoin('users as u', 'u.id', '=', 'wl.user_id')
            ->select(
                'wl.id', 'wl.salon_id', 'wl.user_id', 'wl.randevu_id',
                'wl.telefon', 'wl.mesaj', 'wl.durum', 'wl.hata',
                'wl.mesaj_id', 'wl.gonderim_tarihi', 'wl.created_at',
                's.salon_adi', 'u.name as musteri_adi'
            );

        if ($salonId = $request->input('salon_id')) {
            $q->where('wl.salon_id', $salonId);
        }
        if ($durum = $request->input('durum')) {
            $q->where('wl.durum', $durum);
        }
        if ($telefon = $request->input('telefon')) {
            $q->where('wl.telefon', 'like', '%' . $telefon . '%');
        }
        if ($baslangic = $request->input('baslangic')) {
            $q->whereDate('wl.created_at', '>=', $baslangic);
        }
        if ($bitis = $request->input('bitis')) {
            $q->whereDate('wl.created_at', '<=', $bitis);
        }
        if ($arama = $request->input('arama')) {
            $q->where('wl.mesaj', 'like', '%' . $arama . '%');
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $page = max((int) $request->input('page', 1), 1);

        $toplam = (clone $q)->count();
        $rows = $q->orderByDesc('wl.id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'rows' => $rows,
            'toplam' => $toplam,
            'page' => $page,
            'per_page' => $perPage,
            'son_sayfa' => (int) ceil($toplam / $perPage),
        ]);
    }

    /**
     * Grafik için 30 günlük trend (gün bazında)
     */
    public function grafikData(Request $request)
    {
        $start = Carbon::today()->subDays(29);
        $rows = DB::table('whatsapp_gonderim_loglari')
            ->select(
                DB::raw('DATE(created_at) as tarih'),
                'durum',
                DB::raw('COUNT(*) as adet')
            )
            ->whereDate('created_at', '>=', $start)
            ->groupBy('tarih', 'durum')
            ->orderBy('tarih', 'asc')
            ->get();

        // Günleri doldur, eksik günleri 0 ile geçir
        $gunler = [];
        for ($i = 0; $i < 30; $i++) {
            $g = Carbon::today()->subDays(29 - $i)->format('Y-m-d');
            $gunler[$g] = ['gun' => $g, 'basari' => 0, 'fail' => 0, 'fallback' => 0];
        }
        foreach ($rows as $r) {
            $key = $r->tarih;
            if (!isset($gunler[$key])) continue;
            if ((int) $r->durum === 1) $gunler[$key]['basari'] = (int) $r->adet;
            if ((int) $r->durum === 2) $gunler[$key]['fail'] = (int) $r->adet;
            if ((int) $r->durum === 3) $gunler[$key]['fallback'] = (int) $r->adet;
        }

        return response()->json(['gunler' => array_values($gunler)]);
    }

    /**
     * Tek mesaj detayı — tam metin + zaman çizelgesi
     */
    public function mesajDetay(Request $request, $id)
    {
        $log = DB::table('whatsapp_gonderim_loglari as wl')
            ->leftJoin('salonlar as s', 's.id', '=', 'wl.salon_id')
            ->leftJoin('users as u', 'u.id', '=', 'wl.user_id')
            ->select('wl.*', 's.salon_adi', 'u.name as musteri_adi')
            ->where('wl.id', $id)
            ->first();
        if (!$log) return response()->json(['error' => 'bulunamadi'], 404);
        return response()->json(['log' => $log]);
    }
}
