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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CarkifelekApiController extends Controller
{
    /**
     * Müşterinin bu salonda kalan çevirme hakkı:
     * Onaylanmış (durum=1) randevu sayısı – bu randevulardan log'a yazılmış olanlar.
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
     * Müşteri bugün (yerel tarih) bu salonda çarkı çevirdi mi?
     */
    private function bugunCevirdi($salonId, $userId)
    {
        return CarkifelekCevirmeLoglari::where('salon_id', $salonId)
            ->where('user_id', $userId)
            ->where('tip', '!=', 'tekrar_dene')
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * Çark durumu — dilimler, kalanHak, bugunCevirdi.
     * GET/POST: salon_id, user_id
     */
    public function durum(Request $request)
    {
        $salonId = (int) ($request->input('salon_id') ?? $request->route('salonId'));
        $userId  = (int) ($request->input('user_id')  ?? $request->route('userId'));

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['success' => false, 'message' => 'Salon bulunamadı.']);
        }

        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return response()->json([
                'success' => true,
                'aktif'   => false,
                'message' => 'Bu salonda çarkıfelek aktif değil.',
                'salon'   => ['id' => $salon->id, 'salon_adi' => $salon->salon_adi],
            ]);
        }

        $dilimler = CarkifelekDilimleri::where('cark_id', $cark->id)->orderBy('sira')->get();
        if ($dilimler->count() < 2) {
            return response()->json([
                'success' => true,
                'aktif'   => false,
                'message' => 'Çark henüz hazırlanmamış.',
                'salon'   => ['id' => $salon->id, 'salon_adi' => $salon->salon_adi],
            ]);
        }

        $kullanilabilir = $userId > 0 ? $this->kalanHak($salonId, $userId) : [];
        $bugunCevirdi   = $userId > 0 ? $this->bugunCevirdi($salonId, $userId) : false;

        $dilimlerJson = $dilimler->map(function ($d) {
            return [
                'id'    => $d->id,
                'ismi'  => $d->dilim_ismi,
                'renk'  => $d->renk_kodu,
                'tip'   => $d->tip ?? 'bos',
                'deger' => $d->deger !== null ? (float) $d->deger : null,
                'sira'  => (int) $d->sira,
            ];
        })->values()->toArray();

        return response()->json([
            'success'      => true,
            'aktif'        => true,
            'salon'        => ['id' => $salon->id, 'salon_adi' => $salon->salon_adi],
            'cark_id'      => $cark->id,
            'dilimler'     => $dilimlerJson,
            'kalanHak'     => count($kullanilabilir),
            'randevuIdleri'=> $kullanilabilir,
            'bugunCevirdi' => $bugunCevirdi,
            'yarinSaat'    => Carbon::tomorrow()->format('d.m.Y H:i'),
        ]);
    }

    /**
     * Çarkı çevir. Üye giriş yapmış olmalı (user_id body'de).
     */
    public function cevir(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $userId  = (int) $request->input('user_id');

        if ($userId <= 0) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.']);
        }

        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return response()->json(['success' => false, 'message' => 'Çarkıfelek şu an aktif değil.']);
        }

        $kullanilabilir = $this->kalanHak($salonId, $userId);
        if (empty($kullanilabilir)) {
            return response()->json([
                'success' => false,
                'message' => 'Çevirme hakkınız bulunmuyor. Onaylanmış randevunuz olmalı.',
            ]);
        }

        if ($this->bugunCevirdi($salonId, $userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Bugün çarkı çevirdiniz. Yarın tekrar deneyebilirsiniz.',
            ]);
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

        $sonuc = DB::transaction(function () use ($cark, $secilen, $salonId, $userId, $randevuId) {
            $log = CarkifelekCevirmeLoglari::create([
                'cark_id'    => $cark->id,
                'salon_id'   => $salonId,
                'user_id'    => $userId,
                'randevu_id' => $randevuId,
                'dilim_id'   => $secilen->id,
                'tip'        => $secilen->tip,
                'deger'      => $secilen->deger,
                'dilim_ismi' => $secilen->dilim_ismi,
            ]);

            $odul = null;
            if ($secilen->tip === 'puan' && $secilen->deger) {
                $puanKaydi = SalonPuanlar::firstOrNew(['salon_id' => $salonId, 'user_id' => $userId]);
                $puanKaydi->puan = ((float) $puanKaydi->puan) + (float) $secilen->deger;
                $puanKaydi->save();
            } elseif (in_array($secilen->tip, ['hizmet_indirimi', 'urun_indirimi']) && $secilen->deger) {
                $odul = CarkifelekOdulleri::create([
                    'log_id'            => $log->id,
                    'salon_id'          => $salonId,
                    'user_id'           => $userId,
                    'kod'               => strtoupper(Str::random(8)),
                    'tip'               => $secilen->tip,
                    'deger'             => $secilen->deger,
                    'baslik'            => $this->baslikUret($secilen),
                    'gecerlilik_tarihi' => Carbon::now()->addDays(30)->toDateString(),
                ]);
            }

            return compact('log', 'odul');
        });

        return response()->json([
            'success'    => true,
            'dilimIndex' => (int) $secilenIndex,
            'dilim'      => [
                'id'     => $secilen->id,
                'ismi'   => $secilen->dilim_ismi,
                'tip'    => $secilen->tip,
                'deger'  => $secilen->deger !== null ? (float) $secilen->deger : null,
                'baslik' => $this->baslikUret($secilen),
            ],
            'odulKodu'   => $sonuc['odul']->kod ?? null,
            'kalanHak'   => max(0, count($kullanilabilir) - 1),
        ]);
    }

    /**
     * Müşterinin tüm kazandığı kuponlar (çark + puan ödülü).
     * GET/POST: user_id
     */
    public function odullerim(Request $request)
    {
        $userId = (int) ($request->input('user_id') ?? $request->route('userId'));
        if ($userId <= 0) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.']);
        }

        $odullerim = CarkifelekOdulleri::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($o) {
                $salon = Salonlar::find($o->salon_id);
                return [
                    'id'                => $o->id,
                    'salon_id'          => $o->salon_id,
                    'salon_adi'         => $salon ? $salon->salon_adi : 'Salon',
                    'kod'               => $o->kod,
                    'tip'               => $o->tip,
                    'deger'             => $o->deger !== null ? (float) $o->deger : null,
                    'baslik'            => $o->baslik,
                    'kullanildi'        => (int) $o->kullanildi,
                    'kullanim_tarihi'   => $o->kullanim_tarihi ? $o->kullanim_tarihi->format('Y-m-d H:i:s') : null,
                    'gecerlilik_tarihi' => $o->gecerlilik_tarihi ? $o->gecerlilik_tarihi->format('Y-m-d') : null,
                    'created_at'        => $o->created_at ? $o->created_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $odullerim,
        ]);
    }

    /**
     * Müşterinin salon bazlı puan merdiveni — kazanılabilecek puan ödülleri ve mevcut bakiye.
     * GET/POST: user_id, salon_id (opsiyonel; verilmezse puanı olan ilk salon)
     */
    public function puanOdullerim(Request $request)
    {
        $userId  = (int) ($request->input('user_id')  ?? $request->route('userId'));
        $salonId = (int) ($request->input('salon_id') ?? $request->route('salonId') ?? 0);

        if ($userId <= 0) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.']);
        }

        $puanKayitlari = SalonPuanlar::where('user_id', $userId)
            ->where('puan', '>', 0)
            ->get();

        if ($puanKayitlari->isEmpty() && $salonId === 0) {
            return response()->json([
                'success'        => true,
                'bos'            => true,
                'puanKayitlari'  => [],
                'odulSeviyeleri' => [],
            ]);
        }

        if ($salonId === 0) {
            $salonId = (int) $puanKayitlari->first()->salon_id;
        }

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['success' => false, 'message' => 'Salon bulunamadı.']);
        }

        $puanBakiyesi = (float) (
            SalonPuanlar::where('user_id', $userId)
                ->where('salon_id', $salonId)
                ->value('puan') ?: 0
        );

        $odulSeviyeleri = SalonPuanOdulleri::where('salon_id', $salonId)
            ->where('aktif', 1)
            ->orderBy('puan_esigi')
            ->get()
            ->map(function ($o) {
                return [
                    'id'         => $o->id,
                    'salon_id'   => $o->salon_id,
                    'puan_esigi' => (int) $o->puan_esigi,
                    'baslik'     => $o->baslik,
                    'aciklama'   => $o->aciklama,
                    'tip'        => $o->tip,
                    'deger'      => $o->deger !== null ? (float) $o->deger : null,
                    'sira'       => (int) $o->sira,
                ];
            });

        $salonlar = Salonlar::whereIn('id', $puanKayitlari->pluck('salon_id'))->get()->keyBy('id');
        $puanKayitlariJson = $puanKayitlari->map(function ($pk) use ($salonlar) {
            $s = $salonlar->get($pk->salon_id);
            return [
                'salon_id'  => (int) $pk->salon_id,
                'salon_adi' => $s ? $s->salon_adi : 'Salon',
                'puan'      => (float) $pk->puan,
            ];
        });

        return response()->json([
            'success'        => true,
            'bos'            => false,
            'salon'          => ['id' => $salon->id, 'salon_adi' => $salon->salon_adi],
            'puanBakiyesi'   => $puanBakiyesi,
            'odulSeviyeleri' => $odulSeviyeleri,
            'puanKayitlari'  => $puanKayitlariJson,
        ]);
    }

    /**
     * Müşteri bir puan ödülünü talep ediyor.
     */
    public function puanOdulTalep(Request $request)
    {
        $userId  = (int) $request->input('user_id');
        $salonId = (int) $request->input('salon_id');
        $odulId  = (int) $request->input('odul_id');

        if ($userId <= 0) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.']);
        }

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

        $kupon = DB::transaction(function () use ($puanKaydi, $odul, $salonId, $userId) {
            $puanKaydi->puan = ((float) $puanKaydi->puan) - (float) $odul->puan_esigi;
            $puanKaydi->save();

            $kuponTip = in_array($odul->tip, ['hizmet_indirimi', 'urun_indirimi'])
                ? $odul->tip
                : 'hizmet_indirimi';

            return CarkifelekOdulleri::create([
                'log_id'            => null,
                'salon_id'          => $salonId,
                'user_id'           => $userId,
                'kod'               => strtoupper(Str::random(8)),
                'tip'               => $kuponTip,
                'deger'             => $odul->deger ?: 0,
                'baslik'            => $odul->baslik,
                'gecerlilik_tarihi' => Carbon::now()->addDays(60)->toDateString(),
            ]);
        });

        return response()->json([
            'success'   => true,
            'kod'       => $kupon->kod,
            'baslik'    => $kupon->baslik,
            'kalanPuan' => (int) ($puanKaydi->puan),
        ]);
    }

    /* ───────── yardımcılar ───────── */

    private function olasilikIleSec($dilimler)
    {
        $toplam = $dilimler->sum('dilim_olasilik');
        if ($toplam <= 0) return null;

        $rand    = mt_rand(1, $toplam);
        $birikim = 0;
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
