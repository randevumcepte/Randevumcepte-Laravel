<?php

namespace App\Http\Controllers;

use App\CarkifelekSistemi;
use App\CarkifelekDilimleri;
use App\CarkifelekCevirmeLoglari;
use App\CarkifelekOdulleri;
use App\Randevular;
use App\Salonlar;
use App\SalonPuanlar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CarkifelekMusteriController extends Controller
{
    /**
     * Müşterinin bu salondaki çevirme hakkı:
     *   Onaylanmış (durum=1) randevu sayısı – bu randevulardan log'a yazılmış olanlar
     */
    private function kalanHak($salonId, $userId)
    {
        $onaylanmisRandevuIds = Randevular::where('salon_id', $salonId)
            ->where('user_id', $userId)
            ->where('durum', Randevular::ONAYLANDI)
            ->pluck('id');

        if ($onaylanmisRandevuIds->isEmpty()) return [];

        $kullanilmis = CarkifelekCevirmeLoglari::whereIn('randevu_id', $onaylanmisRandevuIds)
            ->where('tip', '!=', 'tekrar_dene')
            ->pluck('randevu_id')
            ->toArray();

        return $onaylanmisRandevuIds->diff($kullanilmis)->values()->toArray();
    }

    /**
     * Çark sayfasını gösterir.
     */
    public function goster($salonId)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Çarkı çevirmek için giriş yapmalısınız.');
        }

        $salon = Salonlar::find($salonId);
        if (!$salon) abort(404, 'Salon bulunamadı.');

        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return view('carkifelek.pasif', compact('salon'));
        }

        $dilimler = CarkifelekDilimleri::where('cark_id', $cark->id)
            ->orderBy('sira')
            ->get();

        if ($dilimler->count() < 2) {
            return view('carkifelek.pasif', compact('salon'));
        }

        $kullanilabilir = $this->kalanHak($salonId, Auth::id());

        return view('carkifelek.cevir', [
            'salon'           => $salon,
            'cark'            => $cark,
            'dilimler'        => $dilimler,
            'kalanHak'        => count($kullanilabilir),
            'randevuIdleri'   => $kullanilabilir,
        ]);
    }

    /**
     * AJAX: Çarkı çevirir, ödülü işler, sonucu döner.
     */
    public function cevir(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.'], 401);
        }

        $userId  = Auth::id();
        $salonId = (int) $request->input('salon_id');

        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return response()->json(['success' => false, 'message' => 'Çarkıfelek şu an aktif değil.']);
        }

        $kullanilabilir = $this->kalanHak($salonId, $userId);
        if (empty($kullanilabilir)) {
            return response()->json(['success' => false, 'message' => 'Çevirme hakkınız bulunmuyor. Onaylanmış randevunuz olmalı.']);
        }

        $randevuId = $kullanilabilir[0];

        $dilimler = CarkifelekDilimleri::where('cark_id', $cark->id)->orderBy('sira')->get();
        if ($dilimler->count() < 2) {
            return response()->json(['success' => false, 'message' => 'Çark henüz hazırlanmamış.']);
        }

        $secilen = $this->olasilikIleSec($dilimler);
        if (!$secilen) {
            return response()->json(['success' => false, 'message' => 'Dilim seçilemedi.']);
        }

        $secilenIndex = $dilimler->search(function ($d) use ($secilen) {
            return $d->id === $secilen->id;
        });

        // İşlemi atomik yapalım
        $sonuc = DB::transaction(function () use ($cark, $secilen, $salonId, $userId, $randevuId) {
            $log = CarkifelekCevirmeLoglari::create([
                'cark_id'     => $cark->id,
                'salon_id'    => $salonId,
                'user_id'     => $userId,
                'randevu_id'  => $randevuId,
                'dilim_id'    => $secilen->id,
                'tip'         => $secilen->tip,
                'deger'       => $secilen->deger,
                'dilim_ismi'  => $secilen->dilim_ismi,
            ]);

            $odul = null;

            if ($secilen->tip === 'puan' && $secilen->deger) {
                $puanKaydi = SalonPuanlar::firstOrNew([
                    'salon_id' => $salonId,
                    'user_id'  => $userId,
                ]);
                $puanKaydi->puan = ((float) $puanKaydi->puan) + (float) $secilen->deger;
                $puanKaydi->save();

            } elseif (in_array($secilen->tip, ['hizmet_indirimi', 'urun_indirimi']) && $secilen->deger) {
                $kod  = strtoupper(Str::random(8));
                $odul = CarkifelekOdulleri::create([
                    'log_id'            => $log->id,
                    'salon_id'          => $salonId,
                    'user_id'           => $userId,
                    'kod'               => $kod,
                    'tip'               => $secilen->tip,
                    'deger'             => $secilen->deger,
                    'baslik'            => $this->baslikUret($secilen),
                    'gecerlilik_tarihi' => Carbon::now()->addDays(30)->toDateString(),
                ]);
            }

            return compact('log', 'odul');
        });

        return response()->json([
            'success'     => true,
            'dilimIndex'  => (int) $secilenIndex,
            'dilim'       => [
                'id'     => $secilen->id,
                'ismi'   => $secilen->dilim_ismi,
                'tip'    => $secilen->tip,
                'deger'  => $secilen->deger,
                'baslik' => $this->baslikUret($secilen),
            ],
            'odulKodu'    => $sonuc['odul']->kod ?? null,
            'kalanHak'    => max(0, count($kullanilabilir) - 1),
        ]);
    }

    /**
     * Müşterinin kazandığı (kullanılmamış) ödüller.
     */
    public function odullerim()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $odullerim = CarkifelekOdulleri::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('carkifelek.odullerim', compact('odullerim'));
    }

    /* ───────── yardımcılar ───────── */

    private function olasilikIleSec($dilimler)
    {
        $toplam = $dilimler->sum('dilim_olasilik');
        if ($toplam <= 0) return null;

        $rand     = mt_rand(1, $toplam);
        $birikim  = 0;
        foreach ($dilimler as $d) {
            $birikim += (int) $d->dilim_olasilik;
            if ($rand <= $birikim) return $d;
        }
        return $dilimler->last();
    }

    private function baslikUret($d)
    {
        switch ($d->tip) {
            case 'puan':            return $d->deger ? ((int) $d->deger) . ' Puan' : 'Puan';
            case 'hizmet_indirimi': return $d->deger ? '%' . ((int) $d->deger) . ' Hizmet İndirimi' : 'Hizmet İndirimi';
            case 'urun_indirimi':   return $d->deger ? '%' . ((int) $d->deger) . ' Ürün İndirimi'   : 'Ürün İndirimi';
            case 'tekrar_dene':     return 'Tekrar Dene';
            case 'bos':             return 'Boş';
            default:                return $d->dilim_ismi ?: 'Ödül';
        }
    }
}
