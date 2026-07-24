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
                // Bos slot (randevu) kampanyasinin bitis tarihi gectiyse gizle
                if ($r->aksiyon_tipi === 'randevu') {
                    $sonTarih = $r->randevu_tarih_bit ?: $r->randevu_tarih;
                    if ($sonTarih && $sonTarih < date('Y-m-d')) return false;
                }
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
                    // Bos slot randevu penceresi (aksiyon randevu ise): tarih ARALIGI + saat araligi
                    'randevu'      => $r->aksiyon_tipi === 'randevu' ? [
                        'tarih_bas' => $r->randevu_tarih,        // baslangic "yyyy-MM-dd" veya null
                        'tarih_bit' => $r->randevu_tarih_bit,    // bitis "yyyy-MM-dd" veya null
                        'saat_bas'  => $r->randevu_saat_bas,     // "10:00" veya null
                        'saat_bit'  => $r->randevu_saat_bit,     // "12:00" veya null
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

    // =====================================================================
    // ADMIN (Flutter yonetici uygulamasi) — reklam YONETIM API'si.
    // Web panel (StoreAdminController) ile AYNI mantik; ayni tablo -> es zamanli.
    // Tum endpoint'ler POST + body'de user_id (yetkili) ile korunur.
    // =====================================================================

    /** Giris yapan yetkili bu salonu yonetiyor mu? (kanonik: personeller.yetkili_id) */
    private function adminYetkiVar(Request $request, $salonId)
    {
        $userId = (int) $request->input('user_id');
        if ($userId <= 0) return false;
        return \App\Personeller::where('yetkili_id', $userId)->where('salon_id', $salonId)->exists();
    }

    /** Tek reklami Flutter icin duz diziye cevirir. */
    private function adminReklamArray(BildirimReklamlari $r)
    {
        return [
            'id'                   => $r->id,
            'salon_id'             => $r->salon_id,
            'baslik'               => $r->baslik,
            'mesaj'                => $r->mesaj,
            'gorsel'               => $r->gorsel,
            'gorsel_url'           => $r->gorsel ? url($r->gorsel) : null,
            'tur'                  => $r->tur,
            'kanal_push'           => (bool) $r->kanal_push,
            'kanal_inapp'          => (bool) $r->kanal_inapp,
            'tam_ekran'            => (bool) $r->tam_ekran,
            'aksiyon_tipi'         => $r->aksiyon_tipi,
            'kupon_indirim_tipi'   => $r->kupon_indirim_tipi,
            'kupon_deger'          => $r->kupon_deger !== null ? (float) $r->kupon_deger : null,
            'kupon_hizmet_id'      => $r->kupon_hizmet_id,
            'kupon_gecerlilik_gun' => $r->kupon_gecerlilik_gun,
            'kupon_kisi_limit'     => $r->kupon_kisi_limit,
            'kupon_toplam_adet'    => $r->kupon_toplam_adet,
            'kupon_dagitilan'      => (int) $r->kupon_dagitilan,
            'randevu_tarih'        => $r->randevu_tarih,
            'randevu_tarih_bit'    => $r->randevu_tarih_bit,
            'randevu_saat_bas'     => $r->randevu_saat_bas,
            'randevu_saat_bit'     => $r->randevu_saat_bit,
            'hedef_kitle'          => $r->hedef_kitle,
            'hedef_kosul'          => $r->hedef_kosul,
            'durum'                => $r->durum,
            'push_gonderildi'      => (bool) $r->push_gonderildi,
        ];
    }

    public function adminListe(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $reklamlar = BildirimReklamlari::where('salon_id', $salonId)
            ->orderBy('id', 'desc')->get()
            ->map(function ($r) { return $this->adminReklamArray($r); });
        return response()->json(['success' => true, 'data' => $reklamlar]);
    }

    public function adminKaydet(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);

        if (!empty($request->id))
            $reklam = BildirimReklamlari::where('salon_id', $salonId)->where('id', $request->id)->first();
        else
            $reklam = new BildirimReklamlari();
        if (!$reklam)
            return response()->json(['success' => false, 'message' => 'Reklam bulunamadı.'], 404);
        if (trim((string) $request->baslik) === '')
            return response()->json(['success' => false, 'message' => 'Başlık zorunlu.'], 422);

        $reklam->salon_id     = $salonId;
        $reklam->baslik       = trim($request->baslik);
        $reklam->mesaj        = $request->mesaj;
        $reklam->tur          = $request->tur ?: 'kampanya';
        $reklam->kanal_push   = filter_var($request->input('kanal_push'), FILTER_VALIDATE_BOOLEAN);
        $reklam->kanal_inapp  = filter_var($request->input('kanal_inapp'), FILTER_VALIDATE_BOOLEAN);
        $reklam->tam_ekran    = filter_var($request->input('tam_ekran'), FILTER_VALIDATE_BOOLEAN);
        $reklam->aksiyon_tipi = $request->aksiyon_tipi ?: 'kupon';
        $reklam->aksiyon_hedef = $request->aksiyon_hedef;

        if (!$reklam->kanal_push && !$reklam->kanal_inapp && !$reklam->tam_ekran)
            return response()->json(['success' => false, 'message' => 'En az bir kanal seçili olmalı.'], 422);

        if (!empty($request->gorsel))
            $reklam->gorsel = $request->gorsel;

        if ($reklam->aksiyon_tipi === 'kupon' || $reklam->aksiyon_tipi === 'randevu') {
            $reklam->kupon_indirim_tipi   = $request->kupon_indirim_tipi ?: 'yuzde';
            $reklam->kupon_deger          = $request->kupon_deger ?: 0;
            $reklam->kupon_hizmet_id      = $request->kupon_hizmet_id ?: null;
            $reklam->kupon_baslik         = $request->kupon_baslik ?: null;
            $reklam->kupon_gecerlilik_gun = ($request->kupon_gecerlilik_gun !== null && $request->kupon_gecerlilik_gun !== '') ? (int) $request->kupon_gecerlilik_gun : null;
            $reklam->kupon_kisi_limit     = $request->kupon_kisi_limit ?: 1;
            $reklam->kupon_toplam_adet    = ($request->kupon_toplam_adet !== null && $request->kupon_toplam_adet !== '') ? (int) $request->kupon_toplam_adet : null;
        }

        if ($reklam->aksiyon_tipi === 'randevu') {
            $reklam->randevu_tarih     = !empty($request->randevu_tarih) ? $request->randevu_tarih : null;
            $reklam->randevu_tarih_bit = !empty($request->randevu_tarih_bit) ? $request->randevu_tarih_bit : null;
            $reklam->randevu_saat_bas  = !empty($request->randevu_saat_bas) ? $request->randevu_saat_bas : null;
            $reklam->randevu_saat_bit  = !empty($request->randevu_saat_bit) ? $request->randevu_saat_bit : null;
        } else {
            $reklam->randevu_tarih = null; $reklam->randevu_tarih_bit = null;
            $reklam->randevu_saat_bas = null; $reklam->randevu_saat_bit = null;
        }

        if ($request->hedef_kitle === 'segment') {
            $reklam->hedef_kitle = 'segment';
            $segTip = in_array($request->segment_tip, ['gelmeyen', 'dogum_gunu', 'hizmet', 'cinsiyet', 'kisi']) ? $request->segment_tip : 'gelmeyen';
            $kosul = ['tip' => $segTip];
            if ($segTip === 'gelmeyen') $kosul['gun'] = (int) ($request->segment_gun ?: 60);
            if ($segTip === 'hizmet')   $kosul['hizmet_id'] = (int) ($request->segment_hizmet_id ?: 0);
            if ($segTip === 'cinsiyet') $kosul['cinsiyet'] = ($request->segment_cinsiyet === '1' ? '1' : '0');
            if ($segTip === 'kisi')     $kosul['user_id'] = (int) ($request->segment_user_id ?: 0);
            $reklam->hedef_kosul = json_encode($kosul);
        } else {
            $reklam->hedef_kitle = 'tumu';
            $reklam->hedef_kosul = null;
        }

        $reklam->durum = $request->durum ?: 'taslak';
        $reklam->save();

        return response()->json(['success' => true, 'id' => $reklam->id, 'reklam' => $this->adminReklamArray($reklam)]);
    }

    public function adminDurum(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $reklam = BildirimReklamlari::where('salon_id', $salonId)->where('id', $request->id)->first();
        if (!$reklam) return response()->json(['success' => false, 'message' => 'Bulunamadı.'], 404);
        $reklam->durum = in_array($request->durum, ['aktif', 'pasif', 'taslak', 'bitti']) ? $request->durum : 'pasif';
        $reklam->save();
        return response()->json(['success' => true, 'durum' => $reklam->durum]);
    }

    public function adminSil(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $reklam = BildirimReklamlari::where('salon_id', $salonId)->where('id', $request->id)->first();
        if (!$reklam) return response()->json(['success' => false, 'message' => 'Bulunamadı.'], 404);
        $reklam->delete();
        return response()->json(['success' => true]);
    }

    public function adminGorsel(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $data = $request->input('gorsel');
        if (!$data) return response()->json(['success' => false, 'message' => 'Görsel yok.'], 422);
        $folderPath = public_path('reklam_gorselleri') . '/';
        if (!is_dir($folderPath)) @mkdir($folderPath, 0755, true);
        $ext = 'jpg';
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $m)) {
            $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
            if (!in_array($ext, ['jpg', 'png', 'webp'])) $ext = 'jpg';
            $data = substr($data, strpos($data, ',') + 1);
        }
        $binary = base64_decode($data);
        if ($binary === false) return response()->json(['success' => false, 'message' => 'Görsel çözülemedi.'], 422);
        $filename = 'reklam_' . $salonId . '_' . uniqid() . '.' . $ext;
        file_put_contents($folderPath . $filename, $binary);
        $yol = '/public/reklam_gorselleri/' . $filename;
        return response()->json(['success' => true, 'yol' => $yol, 'url' => url($yol)]);
    }

    public function adminGonder(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $reklam = BildirimReklamlari::where('salon_id', $salonId)->where('id', $request->id)->first();
        if (!$reklam) return response()->json(['success' => false, 'message' => 'Bulunamadı.'], 404);
        if (!$reklam->kanal_push) return response()->json(['success' => false, 'message' => 'Bu reklamda Push kapalı.'], 422);
        if ($reklam->durum !== 'aktif') $reklam->durum = 'aktif';
        $reklam->push_gonderildi = true;
        $reklam->save();
        $gonderilen = (int) \App\Jobs\BildirimReklamGonderJob::dispatchNow($reklam->id);
        return response()->json([
            'success'    => true,
            'gonderilen' => $gonderilen,
            'message'    => $gonderilen > 0 ? ($gonderilen . ' kişiye gönderildi.') : 'Gönderildi ama alıcı bulunamadı (kişinin uygulamaya girip bildirime izin vermiş olması gerekir).',
        ]);
    }

    public function adminMusteriler(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $musteriler = DB::table('musteri_portfoy')
            ->join('users', 'users.id', '=', 'musteri_portfoy.user_id')
            ->where('musteri_portfoy.salon_id', $salonId)
            ->where('musteri_portfoy.aktif', 1)
            ->select('users.id', 'users.name', 'users.cep_telefon')
            ->orderBy('users.name')->get();
        return response()->json(['success' => true, 'data' => $musteriler]);
    }

    public function adminHizmetler(Request $request, $salonId)
    {
        if (!$this->adminYetkiVar($request, $salonId))
            return response()->json(['success' => false, 'message' => 'Yetkiniz yok.'], 403);
        $hizmetler = \App\SalonHizmetler::where('salon_id', $salonId)->where('aktif', true)->get()
            ->map(function ($h) { return ['id' => $h->hizmet_id, 'ad' => optional($h->hizmetler)->hizmet_adi]; })
            ->filter(function ($x) { return !empty($x['ad']); })->values();
        return response()->json(['success' => true, 'data' => $hizmetler]);
    }
}
