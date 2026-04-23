<?php

namespace App\Http\Controllers;

use App\CarkifelekSistemi;
use App\CarkifelekDilimleri;
use App\CarkifelekCevirmeLoglari;
use App\CarkifelekOdulleri;
use App\Randevular;
use App\Salonlar;
use App\SalonPuanlar;
use App\SalonPuanOdulleri;
use App\Hizmet_Kategorisi;
use App\Hizmetler;
use App\SalonTuru;
use App\Iller;
use App\Ilceler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CarkifelekMusteriController extends Controller
{
    /**
     * Layout (layout/layout.blade.php) için zorunlu olan ortak değişkenleri döner.
     */
    private function layoutData()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        return [
            'hizmetkategorileri' => Hizmet_Kategorisi::limit(8)->get(),
            'hizmetler'          => Hizmetler::all(),
            'salonturleri'       => SalonTuru::all(),
            'salonlar'           => Salonlar::limit(20)->get(),
            'iller'              => Iller::all(),
            'ilceler'            => Ilceler::all(),
            'salon'              => Salonlar::where('domain', $host)->first(),
        ];
    }

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
            return view('carkifelek.pasif', array_merge($this->layoutData(), ['salon' => $salon]));
        }

        $dilimler = CarkifelekDilimleri::where('cark_id', $cark->id)
            ->orderBy('sira')
            ->get();

        if ($dilimler->count() < 2) {
            return view('carkifelek.pasif', array_merge($this->layoutData(), ['salon' => $salon]));
        }

        $kullanilabilir = $this->kalanHak($salonId, Auth::id());

        $dilimlerJson = $dilimler->map(function ($d) {
            return [
                'id'    => $d->id,
                'ismi'  => $d->dilim_ismi,
                'renk'  => $d->renk_kodu,
                'tip'   => isset($d->tip) ? $d->tip : 'bos',
                'deger' => $d->deger !== null ? (float) $d->deger : null,
            ];
        })->values()->toArray();

        return view('carkifelek.cevir', array_merge($this->layoutData(), [
            'salon'           => $salon,
            'cark'            => $cark,
            'dilimler'        => $dilimler,
            'dilimlerJson'    => $dilimlerJson,
            'kalanHak'        => count($kullanilabilir),
            'randevuIdleri'   => $kullanilabilir,
        ]));
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
     * Müşterinin salon bazlı puan merdiveni sayfası.
     */
    public function puanOdullerim(Request $request, $salonId = null)
    {
        if (!Auth::check()) return redirect('/login');

        $userId = Auth::id();

        // Müşterinin puanı olan salonları getir — biri istenmişse onu seç
        $puanKayitlari = SalonPuanlar::where('user_id', $userId)
            ->where('puan', '>', 0)
            ->get();

        if ($puanKayitlari->isEmpty() && !$salonId) {
            return view('carkifelek.puan_odullerim_bos', $this->layoutData());
        }

        $salonId = $salonId ? (int) $salonId : (int) $puanKayitlari->first()->salon_id;
        $salon   = Salonlar::find($salonId);
        if (!$salon) abort(404);

        $puanBakiyesi = (float) (SalonPuanlar::where('user_id', $userId)->where('salon_id', $salonId)->value('puan') ?: 0);

        $odulSeviyeleri = SalonPuanOdulleri::where('salon_id', $salonId)
            ->where('aktif', 1)
            ->orderBy('puan_esigi')
            ->get();

        $tumSalonlar = Salonlar::whereIn('id', $puanKayitlari->pluck('salon_id'))->get()->keyBy('id');

        return view('carkifelek.puan_odullerim', array_merge($this->layoutData(), [
            'salon'          => $salon,
            'salonId'        => $salonId,
            'puanBakiyesi'   => $puanBakiyesi,
            'odulSeviyeleri' => $odulSeviyeleri,
            'puanKayitlari'  => $puanKayitlari,
            'tumSalonlar'    => $tumSalonlar,
        ]));
    }

    /**
     * AJAX: Müşteri bir puan ödülü talep ediyor.
     * Puanı düşer, kupon oluşur.
     */
    public function puanOdulTalep(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.'], 401);
        }

        $userId  = Auth::id();
        $salonId = (int) $request->input('salon_id');
        $odulId  = (int) $request->input('odul_id');

        $odul = SalonPuanOdulleri::where('id', $odulId)
            ->where('salon_id', $salonId)
            ->where('aktif', 1)
            ->first();
        if (!$odul) {
            return response()->json(['success' => false, 'message' => 'Ödül bulunamadı veya pasif.']);
        }

        $puanKaydi = SalonPuanlar::where('salon_id', $salonId)->where('user_id', $userId)->first();
        $mevcutPuan = $puanKaydi ? (float) $puanKaydi->puan : 0;

        if ($mevcutPuan < $odul->puan_esigi) {
            return response()->json([
                'success' => false,
                'message' => 'Yetersiz puan. Gerekli: ' . $odul->puan_esigi . ', mevcut: ' . ((int) $mevcutPuan),
            ]);
        }

        $sonuc = \DB::transaction(function () use ($puanKaydi, $odul, $salonId, $userId) {
            // Puanı düş
            $puanKaydi->puan = ((float) $puanKaydi->puan) - (float) $odul->puan_esigi;
            $puanKaydi->save();

            // Kupon tipi: hizmet/ürün indirimi veya "hediye" (de hizmet_indirimi gibi davranır ama başlık farklı)
            $kuponTip = in_array($odul->tip, ['hizmet_indirimi', 'urun_indirimi']) ? $odul->tip : 'hizmet_indirimi';

            $kupon = CarkifelekOdulleri::create([
                'log_id'            => null,
                'salon_id'          => $salonId,
                'user_id'           => $userId,
                'kod'               => strtoupper(\Illuminate\Support\Str::random(8)),
                'tip'               => $kuponTip,
                'deger'             => $odul->deger ?: 0,
                'baslik'            => $odul->baslik,
                'gecerlilik_tarihi' => \Carbon\Carbon::now()->addDays(60)->toDateString(),
            ]);

            return $kupon;
        });

        return response()->json([
            'success'      => true,
            'kod'          => $sonuc->kod,
            'baslik'       => $sonuc->baslik,
            'kalanPuan'    => (int) ($puanKaydi->puan),
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

        return view('carkifelek.odullerim', array_merge($this->layoutData(), ['odullerim' => $odullerim]));
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
