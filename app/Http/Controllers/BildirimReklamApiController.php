<?php

namespace App\Http\Controllers;

use App\BildirimReklamlari;
use App\CarkifelekOdulleri;
use App\Salonlar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Flutter uygulamasi icin Bildirim Reklamlari API'si.
 *  - liste     : uygulama-ici gosterilecek aktif reklamlar (in-app kart)
 *  - kuponKap  : musteri gorsele dokununca TEK DOKUNUS ile kupon tanimla
 *
 * Kupon, mevcut carkifelek_odulleri tablosuna yazilir -> "Kuponlarim" ekraninda
 * otomatik gorunur. kaynak_reklam_id ile reklama baglanir (kisi-basi limit icin).
 */
class BildirimReklamApiController extends Controller
{
    /**
     * Aktif in-app reklamlar. GET/POST: salon_id (ops.) veya appBundle (ops.)
     * En az biri onerilir; ikisi de yoksa bos doner.
     */
    public function liste(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $appBundle = $request->input('appBundle');

        $q = BildirimReklamlari::where('durum', 'aktif')
            ->where('kanal_inapp', true);

        if ($salonId > 0) {
            $q->where('salon_id', $salonId);
        } elseif (!empty($appBundle)) {
            $ids = Salonlar::where('app_bundle', $appBundle)->pluck('id')->toArray();
            $q->whereIn('salon_id', $ids);
        } else {
            return response()->json(['success' => true, 'data' => []]);
        }

        $now = Carbon::now();
        $reklamlar = $q->orderBy('id', 'desc')->get()
            ->filter(function ($r) use ($now) {
                if ($r->yayin_baslangic && $r->yayin_baslangic->isFuture()) return false;
                if ($r->yayin_bitis && $r->yayin_bitis->isPast()) return false;
                if ($r->kupon_toplam_adet !== null && $r->kupon_dagitilan >= $r->kupon_toplam_adet) return false;
                return true;
            })
            ->map(function ($r) {
                return [
                    'id'           => $r->id,
                    'salon_id'     => $r->salon_id,
                    'tur'          => $r->tur,
                    'baslik'       => $r->baslik,
                    'mesaj'        => $r->mesaj,
                    'gorsel'       => $r->gorsel ? url($r->gorsel) : null,
                    'aksiyon_tipi' => $r->aksiyon_tipi,
                    'aksiyon_hedef' => $r->aksiyon_hedef,
                    'kupon'        => $r->aksiyon_tipi === 'kupon' ? [
                        'indirim_tipi' => $r->kupon_indirim_tipi,
                        'deger'        => $r->kupon_deger !== null ? (float) $r->kupon_deger : null,
                        'hizmet_id'    => $r->kupon_hizmet_id,
                    ] : null,
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $reklamlar]);
    }

    /**
     * Gorsele dokununca kuponu kap. POST: user_id, reklam_id
     * TEK DOKUNUS = aninda kupon hesaba tanimlanir.
     */
    public function kuponKap(Request $request)
    {
        $userId = (int) $request->input('user_id');
        $reklamId = (int) $request->input('reklam_id');

        if ($userId <= 0) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.']);
        }

        $reklam = BildirimReklamlari::find($reklamId);
        if (!$reklam) {
            return response()->json(['success' => false, 'message' => 'Reklam bulunamadı.']);
        }
        if ($reklam->aksiyon_tipi !== 'kupon') {
            return response()->json(['success' => false, 'message' => 'Bu reklamda kupon yok.']);
        }
        if (!$reklam->yayindaMi()) {
            return response()->json(['success' => false, 'message' => 'Bu kampanya sona erdi.']);
        }

        // Kisi basi limit (varsayilan 1): ayni musteri kuponu tekrar kapamaz
        $mevcut = CarkifelekOdulleri::where('kaynak_reklam_id', $reklam->id)
            ->where('user_id', $userId)->count();
        if ($mevcut >= max(1, (int) $reklam->kupon_kisi_limit)) {
            return response()->json(['success' => true, 'zaten' => true, 'message' => 'Kuponun zaten cebinde 👍']);
        }

        // Toplam adet: yaris kosuluna karsi transaction + lock ile son kontrol
        $odul = DB::transaction(function () use ($reklam, $userId) {
            $taze = BildirimReklamlari::where('id', $reklam->id)->lockForUpdate()->first();
            if ($taze->kupon_toplam_adet !== null && $taze->kupon_dagitilan >= $taze->kupon_toplam_adet) {
                return null;
            }

            $baslik = $reklam->kupon_baslik;
            if (!$baslik) {
                $baslik = $reklam->kupon_indirim_tipi === 'tutar'
                    ? (rtrim(rtrim(number_format((float) $reklam->kupon_deger, 2, ',', '.'), '0'), ',') . ' ₺ İndirim')
                    : (intval($reklam->kupon_deger) . '% İndirim');
            }
            $gecerlilik = $reklam->kupon_gecerlilik_gun
                ? Carbon::now()->addDays((int) $reklam->kupon_gecerlilik_gun)->toDateString()
                : null;

            $odul = CarkifelekOdulleri::create([
                'salon_id'          => $reklam->salon_id,
                'user_id'           => $userId,
                'kaynak_reklam_id'  => $reklam->id,
                'kod'               => strtoupper(Str::random(8)),
                'tip'               => 'hizmet_indirimi',
                'deger'             => $reklam->kupon_deger,
                'baslik'            => $baslik,
                'gecerlilik_tarihi' => $gecerlilik,
            ]);

            $taze->kupon_dagitilan = (int) $taze->kupon_dagitilan + 1;
            $taze->save();

            return $odul;
        });

        if (!$odul) {
            return response()->json(['success' => false, 'message' => 'Bu kampanya sona erdi.']);
        }

        return response()->json([
            'success' => true,
            'message' => '🎉 Kuponun tanımlandı!',
            'kupon'   => [
                'id'                => $odul->id,
                'kod'               => $odul->kod,
                'baslik'            => $odul->baslik,
                'deger'             => (float) $odul->deger,
                'gecerlilik_tarihi' => $odul->gecerlilik_tarihi ? $odul->gecerlilik_tarihi->format('Y-m-d') : null,
            ],
        ]);
    }
}
