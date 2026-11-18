<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Salonlar;
use App\Services\SantralPaket;
use App\SistemYonetim\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sistem Yonetimi > Santral Asistan Paketleri.
 * Salon bazli aylik dakika paketi; gelen/giden AI gorusme dakikasi, Claude token,
 * maliyet (Claude + STT + TTS tahmini), fatura (paket + asim) ve asim davranisi.
 */
class SantralPaketController extends Controller
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

    public function index(Request $request)
    {
        SantralPaket::semaGaranti();
        [$ay, $bas, $bit] = SantralPaket::donem($request->get('ay'));
        $q = trim((string) $request->get('q', ''));
        $kur = SantralPaket::kur();

        $paketler = DB::table(SantralPaket::TABLO)->get()->keyBy('salon_id');
        $kullanimlar = SantralPaket::kullanimlar($bas, $bit);

        $salonIdler = array_unique(array_merge(array_map('intval', $paketler->keys()->all()), array_keys($kullanimlar)));
        $salonQ = Salonlar::whereIn('id', $salonIdler ?: [0]);
        if ($this->rol() === 'destek') $salonQ->where('musteri_yetkili_id', Auth::guard('sistemyonetim')->user()->id);
        if ($q !== '') $salonQ->where('salon_adi', 'like', "%$q%");
        $salonlar = $salonQ->get(['id', 'salon_adi']);

        $satirlar = [];
        $top = ['gelen_dk' => 0, 'giden_dk' => 0, 'toplam_dk' => 0, 'girdi_token' => 0, 'cikti_token' => 0,
                'maliyet_tl' => 0, 'fatura_tl' => 0, 'kar_tl' => 0, 'gelen_adet' => 0, 'giden_adet' => 0, 'olculen_adet' => 0, 'onbellek_tl' => 0, 'toplam_kar_tl' => 0];
        foreach ($salonlar as $s) {
            $p = $paketler[$s->id] ?? null;
            $h = SantralPaket::hesapla($p, $kullanimlar[$s->id] ?? null, $kur);
            foreach ($top as $k => $v) $top[$k] += $h[$k];
            $satirlar[] = ['salon_id' => $s->id, 'salon_adi' => $s->salon_adi, 'paket' => $p] + $h;
        }
        // Paketi bitmek uzere / asanlar uste
        usort($satirlar, function ($a, $b) {
            return [$b['paket'] ? 1 : 0, $b['yuzde'], $b['toplam_dk']] <=> [$a['paket'] ? 1 : 0, $a['yuzde'], $a['toplam_dk']];
        });

        // Ay secici: son 12 ay
        $aylar = [];
        $tr = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        for ($i = 0; $i < 12; $i++) {
            $ts = strtotime(date('Y-m-01') . " -$i month");
            $aylar[date('Y-m', $ts)] = $tr[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        }

        return view('sistemyonetim.v2.santral-paket', [
            'title'     => 'Santral Asistan Paketleri',
            'aktifMenu' => 'santralpaket',
            'satirlar'  => $satirlar,
            'top'       => $top,
            'ay'        => $ay,
            'aylar'     => $aylar,
            'buAy'      => $ay === date('Y-m'),
            'q'         => $q,
            'kur'       => $kur,
            'kurBilgi'  => \App\Services\DovizKuru::usdBilgi(),
            'hazir'     => SantralPaket::HAZIR,
            'sesler'    => SantralPaket::sesler(),
            'varsayilanSes' => SantralPaket::varsayilanSes(),
            'yetkili'   => in_array($this->rol(), ['super_admin', 'yonetici'], true),
        ]);
    }

    public function kaydet(Request $request)
    {
        if (!in_array($this->rol(), ['super_admin', 'yonetici'], true)) abort(403, 'Bu işlem için yetkiniz yok.');
        SantralPaket::semaGaranti();

        $salon = Salonlar::findOrFail((int) $request->input('salon_id'));
        $veri = [
            'paket_adi'     => mb_substr(trim((string) $request->input('paket_adi')), 0, 60) ?: null,
            'dahil_dakika'  => max(0, (int) $request->input('dahil_dakika')),
            'aylik_ucret'   => max(0, (float) str_replace(',', '.', (string) $request->input('aylik_ucret'))),
            'asim_dk_ucret' => max(0, (float) str_replace(',', '.', (string) $request->input('asim_dk_ucret'))),
            'asim_davranis' => $request->input('asim_davranis') === 'durdur' ? 'durdur' : 'devam',
            'tts_ses'       => SantralPaket::sesGecerli($request->input('tts_ses')) ?: SantralPaket::varsayilanSes(),
            'notlar'        => trim((string) $request->input('notlar')) ?: null,
            'guncelleyen'   => optional(Auth::guard('sistemyonetim')->user())->name,
            'updated_at'    => now(),
        ];
        $eski = DB::table(SantralPaket::TABLO)->where('salon_id', $salon->id)->first();
        if ($eski) {
            DB::table(SantralPaket::TABLO)->where('salon_id', $salon->id)->update($veri);
        } else {
            DB::table(SantralPaket::TABLO)->insert($veri + ['salon_id' => $salon->id, 'created_at' => now()]);
        }
        SantralPaket::onbellekSil($salon->id);

        Audit::log('santral_paket_kaydet', 'salon', $salon->id, $salon->salon_adi,
            ($veri['paket_adi'] ?: 'Paket') . ': ' . $veri['dahil_dakika'] . ' dk, ' . $veri['aylik_ucret'] . ' TL, aşımda ' . $veri['asim_davranis'] . ', ses ' . $veri['tts_ses'],
            ['eski' => $eski ? (array) $eski : null, 'yeni' => $veri]);

        return redirect()->back()->with('basari', $salon->salon_adi . ' için asistan paketi kaydedildi.');
    }

    public function sil(Request $request, $salonId)
    {
        if (!in_array($this->rol(), ['super_admin', 'yonetici'], true)) abort(403, 'Bu işlem için yetkiniz yok.');
        $salon = Salonlar::findOrFail((int) $salonId);
        DB::table(SantralPaket::TABLO)->where('salon_id', $salon->id)->delete();
        SantralPaket::onbellekSil($salon->id);
        Audit::log('santral_paket_sil', 'salon', $salon->id, $salon->salon_adi, 'Asistan paketi kaldırıldı (asistan kapalı)');
        return redirect()->back()->with('basari', $salon->salon_adi . ' için asistan paketi kaldırıldı.');
    }
}
