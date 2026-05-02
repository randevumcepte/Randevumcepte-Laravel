<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Randevular;
use App\RandevuHizmetler;
use App\SalonCalismaSaatleri;
use App\Salonlar;
use App\SalonHizmetler;
use App\User;
use App\MusteriPortfoy;
use App\Helpers\CinsiyetTahmin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AI sesli asistan sidecar'ı için API uçları.
 * Tüm uçlar AiSidecarAuth middleware'i ile korunur.
 */
class AiAsistanController extends Controller
{
    /* ─────────────────────────────────────────────────────────────
     * 1. Müsait saatleri getir
     * ───────────────────────────────────────────────────────────── */
    public function musaitSaatler(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $tarih = $request->input('tarih'); // YYYY-MM-DD

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['ok' => false, 'mesaj' => 'Salon bulunamadı'], 404);
        }
        try {
            $tarihObj = Carbon::createFromFormat('Y-m-d', $tarih);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mesaj' => 'Tarih formatı YYYY-MM-DD olmalı'], 422);
        }

        $haftaninGunu = self::haftaninGunu($tarih);
        $calisma = SalonCalismaSaatleri::where('salon_id', $salonId)
            ->where('haftanin_gunu', $haftaninGunu)
            ->where('calisiyor', 1)
            ->first();

        if (!$calisma) {
            return response()->json([
                'ok' => true,
                'tarih' => $tarih,
                'saatler' => [],
                'mesaj' => 'Salon bu gün kapalı'
            ]);
        }

        $aralikDk = max(15, (int) ($salon->randevu_saat_araligi ?? 30));

        // O gün dolu olan saat dilimleri
        $doluSaatler = RandevuHizmetler::join('randevular', 'randevular.id', '=', 'randevu_hizmetler.randevu_id')
            ->where('randevular.salon_id', $salonId)
            ->where('randevular.tarih', $tarih)
            ->where('randevular.durum', Randevular::ONAYLANDI)
            ->select('randevu_hizmetler.saat', 'randevu_hizmetler.saat_bitis')
            ->get();

        $doluSet = [];
        foreach ($doluSaatler as $r) {
            $start = strtotime($r->saat);
            $end = strtotime($r->saat_bitis ?: $r->saat);
            for ($t = $start; $t < $end; $t += 60) {
                $doluSet[date('H:i', $t)] = true;
            }
        }

        $bugun = Carbon::today()->format('Y-m-d');
        $simdi = Carbon::now()->format('H:i');

        $musait = [];
        $start = strtotime($calisma->baslangic_saati);
        $end = strtotime($calisma->bitis_saati);
        for ($t = $start; $t < $end; $t += $aralikDk * 60) {
            $hhmm = date('H:i', $t);
            // Geçmiş saatleri atla (sadece bugün için)
            if ($tarih === $bugun && $hhmm <= $simdi) continue;
            // Dolu mu?
            if (isset($doluSet[$hhmm])) continue;
            $musait[] = $hhmm;
        }

        return response()->json([
            'ok' => true,
            'tarih' => $tarih,
            'saatler' => array_slice($musait, 0, 12), // sesli için ilk 12 yeter
            'aralik_dk' => $aralikDk,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 2. Randevu oluştur
     * ───────────────────────────────────────────────────────────── */
    public function randevuOlustur(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $telefon = self::telefonNormalize($request->input('telefon'));
        $adSoyad = trim((string) $request->input('ad_soyad', ''));
        $tarihSaat = $request->input('tarih_saat'); // ISO 8601: 2026-05-15T14:00:00
        $hizmetId = $request->input('hizmet_id');
        $notlar = $request->input('notlar');

        if (!$salonId || !$telefon || !$tarihSaat) {
            return response()->json(['ok' => false, 'mesaj' => 'salon_id, telefon, tarih_saat zorunlu'], 422);
        }
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['ok' => false, 'mesaj' => 'Salon bulunamadı'], 404);
        }
        try {
            $dt = Carbon::parse($tarihSaat);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mesaj' => 'tarih_saat ISO 8601 olmalı (örn: 2026-05-15T14:00:00)'], 422);
        }
        $tarih = $dt->format('Y-m-d');
        $saat = $dt->format('H:i:s');

        // Müşteri: telefonla bul, yoksa oluştur
        $user = User::where('cep_telefon', $telefon)->first();
        if (!$user) {
            $user = new User();
            $user->cep_telefon = $telefon;
            $user->name = $adSoyad ?: 'Telefon Müşterisi';
            $user->password = bcrypt(str_random_safe(10));
            // Ad-soyaddan otomatik cinsiyet tahmin
            if ($adSoyad) {
                $tahmin = CinsiyetTahmin::tahmin($adSoyad);
                if ($tahmin !== null) $user->cinsiyet = $tahmin;
            }
            $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $user->save();
        } elseif ($adSoyad && empty($user->name)) {
            $user->name = $adSoyad;
            if ($user->cinsiyet === null) {
                $tahmin = CinsiyetTahmin::tahmin($adSoyad);
                if ($tahmin !== null) $user->cinsiyet = $tahmin;
            }
            $user->save();
        }

        // MusteriPortfoy garantile
        $portfoy = MusteriPortfoy::where('user_id', $user->id)
            ->where('salon_id', $salonId)
            ->first();
        if (!$portfoy) {
            $portfoy = new MusteriPortfoy();
            $portfoy->user_id = $user->id;
            $portfoy->salon_id = $salonId;
            $portfoy->aktif = true;
            $portfoy->ozel_notlar = 'AI sesli asistan üzerinden eklendi';
            $portfoy->save();
        }

        // Çakışma: aynı saat ve salonda aktif randevu var mı?
        $cakisma = Randevular::where('salon_id', $salonId)
            ->where('tarih', $tarih)
            ->where('saat', $saat)
            ->where('durum', Randevular::ONAYLANDI)
            ->exists();
        if ($cakisma) {
            return response()->json([
                'ok' => false,
                'mesaj' => 'Bu saat dolu, başka bir saat seçiniz'
            ], 409);
        }

        // Hizmet süresi (varsa)
        $sureDk = 30;
        if ($hizmetId) {
            $sure = SalonHizmetler::where('salon_id', $salonId)
                ->where('hizmet_id', $hizmetId)
                ->value('sure_dk');
            if ($sure) $sureDk = (int) $sure;
        }
        $saatBitis = Carbon::parse($saat)->addMinutes($sureDk)->format('H:i:s');

        DB::beginTransaction();
        try {
            $randevu = new Randevular();
            $randevu->user_id = $user->id;
            $randevu->salon_id = $salonId;
            $randevu->tarih = $tarih;
            $randevu->saat = $saat;
            $randevu->durum = Randevular::ONAYLANDI;
            $randevu->web = 1; // AI üzerinden gelmiş işareti olarak; istersen yeni bir kolon ekle
            $randevu->personel_notu = $notlar;
            $randevu->save();

            if ($hizmetId) {
                $rh = new RandevuHizmetler();
                $rh->randevu_id = $randevu->id;
                $rh->hizmet_id = $hizmetId;
                $rh->saat = $saat;
                $rh->saat_bitis = $saatBitis;
                $rh->sure_dk = $sureDk;
                $rh->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'mesaj' => 'Randevu kaydedilemedi: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'randevu_id' => $randevu->id,
            'tarih_saat' => $tarih . 'T' . substr($saat, 0, 5) . ':00',
            'mesaj' => "Randevunuz {$tarih} saat " . substr($saat, 0, 5) . " için oluşturuldu"
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 3. Mevcut randevularım
     * ───────────────────────────────────────────────────────────── */
    public function mevcutRandevular(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $telefon = self::telefonNormalize($request->input('telefon'));

        $user = User::where('cep_telefon', $telefon)->first();
        if (!$user) {
            return response()->json(['ok' => true, 'randevular' => []]);
        }

        $bugun = Carbon::today()->format('Y-m-d');
        $randevular = Randevular::where('user_id', $user->id)
            ->where('salon_id', $salonId)
            ->whereDate('tarih', '>=', $bugun)
            ->where('durum', Randevular::ONAYLANDI)
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get(['id', 'tarih', 'saat']);

        $list = $randevular->map(function ($r) {
            return [
                'id' => $r->id,
                'tarih_saat' => $r->tarih . 'T' . $r->saat,
                'tarih' => $r->tarih,
                'saat' => substr($r->saat, 0, 5),
            ];
        });

        return response()->json(['ok' => true, 'randevular' => $list]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 4. Randevu iptal
     * ───────────────────────────────────────────────────────────── */
    public function randevuIptal(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $randevuId = (int) $request->input('randevu_id');

        $randevu = Randevular::where('id', $randevuId)
            ->where('salon_id', $salonId)
            ->first();
        if (!$randevu) {
            return response()->json(['ok' => false, 'mesaj' => 'Randevu bulunamadı'], 404);
        }
        $randevu->durum = Randevular::IPTAL_EDILDI;
        $randevu->save();

        return response()->json([
            'ok' => true,
            'mesaj' => 'Randevu iptal edildi',
            'randevu_id' => $randevu->id
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 5. Randevu güncelle
     * ───────────────────────────────────────────────────────────── */
    public function randevuGuncelle(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $randevuId = (int) $request->input('randevu_id');
        $yeniTs = $request->input('yeni_tarih_saat');

        $randevu = Randevular::where('id', $randevuId)
            ->where('salon_id', $salonId)
            ->first();
        if (!$randevu) {
            return response()->json(['ok' => false, 'mesaj' => 'Randevu bulunamadı'], 404);
        }
        try {
            $dt = Carbon::parse($yeniTs);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mesaj' => 'yeni_tarih_saat ISO 8601 olmalı'], 422);
        }
        $yeniTarih = $dt->format('Y-m-d');
        $yeniSaat = $dt->format('H:i:s');

        // Çakışma kontrolü (kendisi hariç)
        $cakisma = Randevular::where('salon_id', $salonId)
            ->where('tarih', $yeniTarih)
            ->where('saat', $yeniSaat)
            ->where('durum', Randevular::ONAYLANDI)
            ->where('id', '!=', $randevu->id)
            ->exists();
        if ($cakisma) {
            return response()->json(['ok' => false, 'mesaj' => 'Yeni saat dolu'], 409);
        }

        DB::beginTransaction();
        try {
            $eskiSaat = $randevu->saat;
            $randevu->tarih = $yeniTarih;
            $randevu->saat = $yeniSaat;
            $randevu->save();

            // RandevuHizmetler de güncellensin
            $rhList = RandevuHizmetler::where('randevu_id', $randevu->id)->get();
            foreach ($rhList as $rh) {
                $sure = (int) ($rh->sure_dk ?: 30);
                $rh->saat = $yeniSaat;
                $rh->saat_bitis = Carbon::parse($yeniSaat)->addMinutes($sure)->format('H:i:s');
                $rh->save();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'mesaj' => 'Güncelleme başarısız: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'ok' => true,
            'mesaj' => "Randevu {$yeniTarih} saat " . substr($yeniSaat, 0, 5) . " olarak güncellendi",
            'randevu_id' => $randevu->id,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * Helpers
     * ───────────────────────────────────────────────────────────── */

    private static function haftaninGunu($tarih)
    {
        $g = date('D', strtotime($tarih));
        $map = ['Mon'=>1,'Tue'=>2,'Wed'=>3,'Thu'=>4,'Fri'=>5,'Sat'=>6,'Sun'=>7];
        return $map[$g] ?? 0;
    }

    /**
     * Telefonu normalize et: rakam dışını sil, +90/0 baş varyantlarını standardize et.
     */
    private static function telefonNormalize($t)
    {
        $t = preg_replace('/[^0-9]/', '', (string) $t);
        if (strlen($t) === 12 && substr($t, 0, 2) === '90') {
            $t = substr($t, 2);
        }
        if (strlen($t) === 11 && substr($t, 0, 1) === '0') {
            $t = substr($t, 1);
        }
        return $t; // 5xxxxxxxxx
    }
}

if (!function_exists('str_random_safe')) {
    function str_random_safe($len = 10)
    {
        return substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, $len);
    }
}
