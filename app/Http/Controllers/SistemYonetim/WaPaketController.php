<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Salonlar;
use App\Services\SantralPaket;
use App\Services\WaAsistanVeri;
use App\Services\WaPaket;
use App\SistemYonetim\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sistem Yonetimi > WhatsApp Asistan Paketleri (santral dakika paketinin WhatsApp karsiligi).
 * Salon bazli aylik AI mesaj hakki; yalniz yapay zeka cevaplari sayilir. Paket tanimlanan hatta asistan acilir.
 */
class WaPaketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    private function rol()
    {
        $u = Auth::guard('sistemyonetim')->user();
        if (!$u) return null;
        if (!empty($u->rol)) return $u->rol;
        return $u->admin == 1 ? 'super_admin' : 'destek';
    }

    /** Tum salonlarda WhatsApp asistaninin isletmeye sordugu konular: kim bekletiyor, ne kadar, sonuc ne oldu. */
    public function talepler(Request $request)
    {
        $liste = \App\Services\WaTalep::sistemListesi(max(1, min(60, (int) $request->get('gun', 7))));
        if ($this->rol() === 'destek') {
            $izinli = Salonlar::where('musteri_yetkili_id', Auth::guard('sistemyonetim')->user()->id)->pluck('id')->map(function ($x) { return (int) $x; })->all();
            $liste = array_values(array_filter($liste, function ($t) use ($izinli) { return in_array($t['isletme_id'], $izinli, true); }));
        }
        $say = ['bekliyor' => 0, 'cevaplandi' => 0, 'cevapsiz' => 0];
        foreach ($liste as $t) $say[$t['durum']] = ($say[$t['durum']] ?? 0) + 1;
        return view('sistemyonetim.v2.wa-talepler', ['title' => 'WhatsApp Asistan Talepleri', 'aktifMenu' => 'watalep', 'liste' => $liste, 'say' => $say, 'gun' => (int) $request->get('gun', 7)]);
    }

    public function index(Request $request)
    {
        WaPaket::semaGaranti();
        WaAsistanVeri::semaGaranti();
        [$ay, $bas, $bit] = SantralPaket::donem($request->get('ay'));
        $q = trim((string) $request->get('q', ''));
        $kur = SantralPaket::kur();

        $paketler = DB::table(WaPaket::TABLO)->get()->keyBy('salon_id');
        $kullanim = DB::table('wa_asistan_cevaplar')->where('created_at', '>=', $bas)->where('created_at', '<', $bit)
            ->groupBy('salon_id')->select('salon_id', DB::raw('COUNT(*) as toplam'), DB::raw('SUM(ai) as ai'), DB::raw('SUM(maliyet_usd) as usd'))
            ->get()->keyBy('salon_id');
        $env = array_filter(array_map('intval', preg_split('/[\s,;]+/', (string) config('services.wa_asistan.salonlar', ''))));

        $ids = array_unique(array_merge(array_map('intval', $paketler->keys()->all()), array_map('intval', $kullanim->keys()->all()), $env));
        $salonQ = Salonlar::whereIn('id', $ids ?: [0]);
        if ($this->rol() === 'destek') $salonQ->where('musteri_yetkili_id', Auth::guard('sistemyonetim')->user()->id);
        if ($q !== '') $salonQ->where('salon_adi', 'like', "%$q%");

        $satirlar = [];
        $top = ['toplam' => 0, 'ai' => 0, 'maliyet_tl' => 0.0, 'fatura_tl' => 0.0, 'kar_tl' => 0.0];
        foreach ($salonQ->get(['id', 'salon_adi']) as $s) {
            $p = $paketler[$s->id] ?? null;
            $k = $kullanim[$s->id] ?? null;
            $ai = (int) ($k->ai ?? 0);
            $h = WaPaket::hesapla($p, $ai);
            $maliyet = (float) ($k->usd ?? 0) * $kur;
            $fatura = $p ? (float) $p->aylik_ucret : 0.0;
            $satir = ['salon_id' => $s->id, 'salon_adi' => $s->salon_adi, 'paket' => $p, 'env' => in_array((int) $s->id, $env, true),
                'toplam' => (int) ($k->toplam ?? 0), 'ai' => $ai, 'maliyet_tl' => $maliyet, 'fatura_tl' => $fatura, 'kar_tl' => $fatura - $maliyet] + $h;
            foreach ($top as $kk => $_) $top[$kk] += $satir[$kk];
            $satirlar[] = $satir;
        }
        usort($satirlar, function ($a, $b) { return [$b['paket'] ? 1 : 0, $b['yuzde'], $b['ai']] <=> [$a['paket'] ? 1 : 0, $a['yuzde'], $a['ai']]; });

        $aylar = [];
        $tr = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        for ($i = 0; $i < 12; $i++) {
            $ts = strtotime(date('Y-m-01') . " -$i month");
            $aylar[date('Y-m', $ts)] = $tr[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        }

        return view('sistemyonetim.v2.wa-paket', [
            'title' => 'WhatsApp Asistan Paketleri', 'aktifMenu' => 'wapaket', 'satirlar' => $satirlar, 'top' => $top,
            'ay' => $ay, 'aylar' => $aylar, 'buAy' => $ay === date('Y-m'), 'q' => $q, 'kur' => $kur, 'hazir' => WaPaket::HAZIR,
            'yetkili' => in_array($this->rol(), ['super_admin', 'yonetici'], true),
        ]);
    }

    public function kaydet(Request $request)
    {
        if (!in_array($this->rol(), ['super_admin', 'yonetici'], true)) abort(403, 'Bu işlem için yetkiniz yok.');
        WaPaket::semaGaranti();
        $salon = Salonlar::find((int) $request->input('salon_id'));
        if (!$salon) return redirect()->back()->with('hata', 'Salon #' . (int) $request->input('salon_id') . ' bulunamadı. Salon numarasını kontrol edin.');
        $veri = [
            'paket_adi' => mb_substr(trim((string) $request->input('paket_adi')), 0, 60) ?: null,
            'dahil_mesaj' => max(0, (int) $request->input('dahil_mesaj')),
            'aylik_ucret' => max(0, (float) str_replace(',', '.', (string) $request->input('aylik_ucret'))),
            'asim_davranis' => $request->input('asim_davranis') === 'devam' ? 'devam' : 'durdur',
            'notlar' => trim((string) $request->input('notlar')) ?: null,
            'guncelleyen' => optional(Auth::guard('sistemyonetim')->user())->name,
            'updated_at' => now(),
        ];
        $eski = DB::table(WaPaket::TABLO)->where('salon_id', $salon->id)->first();
        if ($eski) DB::table(WaPaket::TABLO)->where('salon_id', $salon->id)->update($veri);
        else DB::table(WaPaket::TABLO)->insert($veri + ['salon_id' => $salon->id, 'created_at' => now()]);
        WaPaket::onbellekSil($salon->id);
        Audit::log('wa_paket_kaydet', 'salon', $salon->id, $salon->salon_adi,
            ($veri['paket_adi'] ?: 'Paket') . ': ' . $veri['dahil_mesaj'] . ' mesaj, ' . $veri['aylik_ucret'] . ' TL, bitince ' . $veri['asim_davranis'],
            ['eski' => $eski ? (array) $eski : null, 'yeni' => $veri]);
        return redirect()->back()->with('basari', $salon->salon_adi . ' için WhatsApp asistan paketi kaydedildi.');
    }

    public function sil(Request $request, $salonId)
    {
        if (!in_array($this->rol(), ['super_admin', 'yonetici'], true)) abort(403, 'Bu işlem için yetkiniz yok.');
        $salon = Salonlar::findOrFail((int) $salonId);
        DB::table(WaPaket::TABLO)->where('salon_id', $salon->id)->delete();
        WaPaket::onbellekSil($salon->id);
        Audit::log('wa_paket_sil', 'salon', $salon->id, $salon->salon_adi, 'WhatsApp asistan paketi kaldırıldı');
        return redirect()->back()->with('basari', $salon->salon_adi . ' için WhatsApp asistan paketi kaldırıldı.');
    }
}
