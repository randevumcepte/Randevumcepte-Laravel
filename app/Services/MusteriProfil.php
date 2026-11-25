<?php

namespace App\Services;

use App\CarkifelekOdulleri;
use App\MusteriPortfoy;
use App\SalonYonetim\Audit;
use App\Salonlar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Musteri profil sayfasi (mobil, 2026-11-25): sayilar + siradaki randevu + cihazlar. Giris yapan musteri
 * yalniz kendi kayitlarini gorur; kapsam, markanin (app_bundle) musteriye kayitli subeleridir.
 */
class MusteriProfil
{
    const ORTAK_BUNDLE = 'com.randevumcepte.randevumcepte';

    /** Musterinin portfoyunde oldugu marka subeleri; secili salon portfoyunde degilse []. */
    public static function salonlar($userId, $salonId)
    {
        $portfoy = array_map('intval', MusteriPortfoy::where('user_id', $userId)->pluck('salon_id')->toArray());
        if (!in_array((int) $salonId, $portfoy, true)) return [];
        $bundle = trim((string) Salonlar::where('id', $salonId)->value('app_bundle'));
        if ($bundle === '' || $bundle === self::ORTAK_BUNDLE) return [(int) $salonId];
        return array_map('intval', Salonlar::whereIn('id', $portfoy)->where('app_bundle', $bundle)->pluck('id')->toArray());
    }

    public static function ozet($userId, array $salonlar, $salonId)
    {
        $s = ['ziyaret' => null, 'yaklasan' => null, 'kupon' => 0, 'cihaz' => 0, 'siradaki' => null];
        $bugun = date('Y-m-d');
        $simdi = date('H:i:s');
        $gelecek = function ($q) use ($bugun, $simdi) {
            $q->where('r.tarih', '>', $bugun)
              ->orWhere(function ($x) use ($bugun, $simdi) { $x->where('r.tarih', $bugun)->where('r.saat', '>=', $simdi); });
        };
        $taban = function () use ($userId, $salonlar) {
            return DB::table('randevular as r')->where('r.user_id', $userId)->whereIn('r.salon_id', $salonlar);
        };
        try {
            $s['ziyaret'] = $taban()->where('r.randevuya_geldi', 1)->where('r.tarih', '<=', $bugun)->count();
            $s['yaklasan'] = $taban()->whereIn('r.durum', [0, 1])->where($gelecek)->count();
            $n = $taban()->whereIn('r.durum', [0, 1])->where($gelecek)->orderBy('r.tarih')->orderBy('r.saat')
                ->first(['r.id', 'r.tarih', 'r.saat', 'r.durum', 'r.salon_id']);
            if ($n) {
                $hizmetler = DB::table('randevu_hizmetler as rh')->join('hizmetler as h', 'h.id', '=', 'rh.hizmet_id')
                    ->where('rh.randevu_id', $n->id)->pluck('h.hizmet_adi')->unique()->implode(', ');
                $s['siradaki'] = [
                    'randevu_id' => (int) $n->id,
                    'tarih' => $n->tarih,
                    'saat' => substr((string) $n->saat, 0, 5),
                    'hizmet' => $hizmetler,
                    'sube' => count($salonlar) > 1 ? (string) Salonlar::where('id', $n->salon_id)->value('salon_adi') : '',
                    'onayli' => (int) $n->durum === 1,
                    'dakika' => max(0, (int) round((strtotime($n->tarih . ' ' . $n->saat) - time()) / 60)),
                    'bugun' => $n->tarih === $bugun,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[MusteriProfil] randevu', ['err' => $e->getMessage()]);
        }
        try {
            $s['kupon'] = CarkifelekOdulleri::where('user_id', $userId)->whereIn('salon_id', $salonlar)->where('kullanildi', 0)
                ->where(function ($q) use ($bugun) { $q->whereNull('gecerlilik_tarihi')->orWhere('gecerlilik_tarihi', '>=', $bugun); })->count();
        } catch (\Throwable $e) {
            Log::warning('[MusteriProfil] kupon', ['err' => $e->getMessage()]);
        }
        try {
            $s['cihaz'] = count(self::cihazlar($userId, $salonId));
        } catch (\Throwable $e) {
            Log::warning('[MusteriProfil] cihaz', ['err' => $e->getMessage()]);
        }
        return $s;
    }

    public static function cihazlar($userId, $salonId)
    {
        $q = DB::table('bildirim_kimlikleri')->where('user_id', $userId)->where('aktif', 1)
            ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '');
        $bundle = Salonlar::where('id', $salonId)->value('app_bundle');
        if ($bundle) $q->where('app_bundle', $bundle);
        return $q->orderByDesc('id')->get(['id', 'bildirim_id', 'cihaz', 'platform', 'created_at', 'updated_at'])->all();
    }

    /** Cihazdan cikis: o cihaza force_logout push'u gonderilir ve cihaz kaydi silinir. */
    public static function cihazKapat($user, $salonId, $id)
    {
        $c = DB::table('bildirim_kimlikleri')->where('id', $id)->where('user_id', $user->id)->first();
        if (!$c) return 'Cihaz bulunamadı.';
        try {
            NotificationService::forTokens([$c->bildirim_id], $salonId)->type(NotificationTypes::SIFRE_DEGISTI)
                ->title('Oturum kapatıldı')->body('Bu cihazdaki oturumunuz başka bir cihazdan kapatıldı.')
                ->extra(['force_logout' => '1'])->send();
        } catch (\Throwable $e) {
            Log::warning('[MusteriProfil] cihaz push', ['err' => $e->getMessage()]);
        }
        DB::table('bildirim_kimlikleri')->where('id', $c->id)->delete();
        // Istek verilmez: musteri numarasi, ayni numarali isletme yetkilisi gibi loglanmasin
        Audit::logApi($salonId, null, 'musteri_cihaz_cikis', 'musteri', $user->id, $user->name,
            $user->name . ' kendi hesabında bir cihazdaki oturumu kapattı (' . ($c->cihaz ?: ($c->platform ?: 'cihaz')) . ')');
        return true;
    }
}
