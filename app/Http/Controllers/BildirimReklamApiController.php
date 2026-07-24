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
        $userId = (int) $request->input('user_id'); // 0 ise kisi-bazli gizleme yapilmaz

        $q = BildirimReklamlari::where('durum', 'aktif')
            ->where(function ($w) {
                // Uygulama-ici kart VEYA acilis tam ekran popup olanlar
                $w->where('kanal_inapp', true)->orWhere('tam_ekran', true);
            });

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
            ->filter(function ($r) use ($now, $userId) {
                if ($r->yayin_baslangic && $r->yayin_baslangic->isFuture()) return false;
                if ($r->yayin_bitis && $r->yayin_bitis->isPast()) return false;
                if ($r->kupon_toplam_adet !== null && $r->kupon_dagitilan >= $r->kupon_toplam_adet) return false;
                // Bos slot (randevu) kampanyasinin tarihi gectiyse gizle
                if ($r->aksiyon_tipi === 'randevu' && $r->randevu_tarih && $r->randevu_tarih < date('Y-m-d')) return false;
                // Kullanici bu SALT-kupon reklamini zaten kaptiysa (kisi limiti doldu) ona artik gosterme.
                // ('randevu' reklami kupon verse de kampanya tarihine kadar gorunsun; kupon 1 kez kapilir.)
                if ($userId > 0 && $r->aksiyon_tipi === 'kupon') {
                    $limit = max(1, (int) $r->kupon_kisi_limit);
                    $kaptigi = CarkifelekOdulleri::where('kaynak_reklam_id', $r->id)
                        ->where('user_id', $userId)->count();
                    if ($kaptigi >= $limit) return false;
                }
                return true;
            })
            ->map(function ($r) {
                $kuponVar = ($r->aksiyon_tipi === 'kupon' || $r->aksiyon_tipi === 'randevu')
                    && $r->kupon_deger !== null && (float) $r->kupon_deger > 0;
                return [
                    'id'           => $r->id,
                    'salon_id'     => $r->salon_id,
                    'tur'          => $r->tur,
                    'baslik'       => $r->baslik,
                    'mesaj'        => $r->mesaj,
                    'gorsel'       => $r->gorsel ? url($r->gorsel) : null,
                    'tam_ekran'    => (bool) $r->tam_ekran,
                    'aksiyon_tipi' => $r->aksiyon_tipi,
                    'aksiyon_hedef' => $r->aksiyon_hedef,
                    'kupon'        => $kuponVar ? [
                        'indirim_tipi' => $r->kupon_indirim_tipi,
                        'deger'        => (float) $r->kupon_deger,
                        'hizmet_id'    => $r->kupon_hizmet_id,
                    ] : null,
                    // Bos slot randevu penceresi (aksiyon randevu ise)
                    'randevu'      => $r->aksiyon_tipi === 'randevu' ? [
                        'tarih'    => $r->randevu_tarih,        // "yyyy-MM-dd" veya null
                        'saat_bas' => $r->randevu_saat_bas,     // "10:00" veya null
                        'saat_bit' => $r->randevu_saat_bit,     // "12:00" veya null
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
        // aksiyon 'kupon' veya 'randevu' (bos slot indirimli) + gecerli kupon degeri olmali
        $kuponVerir = in_array($reklam->aksiyon_tipi, ['kupon', 'randevu'])
            && $reklam->kupon_deger !== null && (float) $reklam->kupon_deger > 0;
        if (!$kuponVerir) {
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
                'indirim_tipi'      => $reklam->kupon_indirim_tipi === 'tutar' ? 'tutar' : 'yuzde',
                'hizmet_id'         => $reklam->kupon_hizmet_id, // null = tum hizmetler
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
