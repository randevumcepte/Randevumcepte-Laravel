<?php

namespace App\Services;

use App\BildirimKimlikleri;
use App\CarkifelekSistemi;
use App\Randevular;
use App\Salonlar;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Çarkıfelek DUYURU push'u — uygulaması yüklü müşterilere "çarkı çevir" bildirimi. TEK KAYNAK:
 *   - mobil v1  POST /api/v1/carkAdmin/bildirim-gonder/{salon} (ApiController::carkBildirimGonder, senkron)
 *   - v2 outbox 'cark_duyuru' → CarkDuyuruGonderJob (v2 Çarkıfelek "Müşterilere Duyur")
 *
 * Hedef kitle: çarkın şube grubundaki (gecerli_salonlar; yoksa yalnız bu şube) randevulu
 * müşterilerden aktif FCM token'ı olanlar. {ad} ve {salon_adi} kişiselleştirilir.
 * (Mantık ApiController::carkBildirimGonder'den taşındı; davranış değişmedi.)
 */
class CarkDuyuruServisi
{
    const VARSAYILAN_MESAJ = "Çark-ı Felek'i çevir, sürpriz ödülü kap! Bugün şansını dene 🎁";

    /**
     * @return array{basarili:bool, gonderildi:int, hata:int, hedef:int, mesaj:?string, kod:int}
     *   basarili=false → kod 404 (salon yok) / 422 (çark aktif değil)
     */
    public static function gonder($salonId, $baslik = '', $mesaj = '')
    {
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return self::sonuc(false, 0, 0, 0, 'Salon bulunamadi', 404);
        }
        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return self::sonuc(false, 0, 0, 0, 'Cark sistemi aktif degil', 422);
        }
        // Bildirim tercihleri: cark_hatirlatma / musteri / push kapaliysa duyuru gitmez
        if (!BildirimTercih::izin($salon->id, 'cark_hatirlatma', 'musteri', 'push')) {
            return self::sonuc(true, 0, 0, 0, 'Bildirim Ayarlarinda carkifelek uygulama bildirimi kapali');
        }

        $baslik = trim((string) $baslik);
        $mesaj  = trim((string) $mesaj);
        if ($baslik === '') {
            $baslik = '🎡 ' . $salon->salon_adi;
        } elseif (mb_substr($baslik, 0, 1) !== '🎡') {
            $baslik = '🎡 ' . $baslik;   // kullanıcının başlığına ikon ekle
        }
        if ($mesaj === '') $mesaj = self::VARSAYILAN_MESAJ;

        // Çoklu şube: çark bir grup için tanımlıysa duyuru gruptaki TÜM şubelerin randevulu
        // müşterilerine gider (tekilleştirilmiş). Grup yoksa yalnız bu şube.
        $grup = \App\Personeller::salonIdListesiCoz($cark->gecerli_salonlar ?? null);
        if (empty($grup)) $grup = [(int) $salonId];

        $musteriIdleri = Randevular::whereIn('salon_id', $grup)->whereNotNull('user_id')
            ->pluck('user_id')->unique()->filter()->values();
        if ($musteriIdleri->isEmpty()) {
            return self::sonuc(true, 0, 0, 0, 'Bu salonda randevulu musteri bulunmuyor');
        }

        $alicilar = BildirimKimlikleri::whereIn('user_id', $musteriIdleri)->where('aktif', true)
            ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')
            ->pluck('user_id')->unique()->values();
        if ($alicilar->isEmpty()) {
            return self::sonuc(true, 0, 0, 0, 'Uygulamasi yuklu musteri yok');
        }

        $sent = 0;
        $failed = 0;
        $ulasanMusteri = 0;
        foreach ($alicilar as $userId) {
            $user = User::find($userId);
            if (!$user) continue;
            $yer = ['{ad}' => explode(' ', (string) $user->name)[0] ?? '', '{salon_adi}' => (string) $salon->salon_adi];
            try {
                $s = NotificationService::toCustomer((int) $userId, (int) $salon->id)
                    ->type(NotificationTypes::WHEEL_CHANCE)
                    ->title(strtr($baslik, $yer))
                    ->body(strtr($mesaj, $yer))
                    ->popup(true)
                    ->deepLink('wheel', ['salon_id' => $salon->id])
                    ->extra(['kaynak' => 'admin_duyuru'])
                    ->send();
                $sent   += (int) ($s['sent'] ?? 0);
                $failed += (int) ($s['failed'] ?? 0);
                if ((int) ($s['sent'] ?? 0) > 0) $ulasanMusteri++;
            } catch (\Throwable $e) {
                Log::warning("Cark duyuru push hatasi user={$userId}: " . $e->getMessage());
                $failed++;
            }
        }

        // Ek alicilar (personel/yonetici bilgi kopyasi) — duyuru basina TEK kez (musteri basina degil).
        if ($ulasanMusteri > 0) {
            $ekB = ['ozet' => 'Çarkıfelek duyurusu ' . $ulasanMusteri . ' müşteriye gönderildi.'];
            $gonderen = self::oturumPersonelId($salon->id);
            if ($gonderen) $ekB['personel_ids'] = [$gonderen];
            BildirimTercih::ekGonder($salon, 'cark_hatirlatma', $ekB);
        }

        return self::sonuc(true, $sent, $failed, $alicilar->count(), null);
    }

    /** Duyuruyu gonderen oturumdaki isletme kullanicisinin bu salondaki personel kaydi (job'da null). */
    private static function oturumPersonelId($salonId)
    {
        try {
            $yetkili = null;
            foreach (['isletmeyonetim', 'isletmeyonetim-api'] as $g) {
                try { $yetkili = \Illuminate\Support\Facades\Auth::guard($g)->user(); } catch (\Throwable $e) { $yetkili = null; }
                if ($yetkili) break;
            }
            if (!$yetkili) return null;
            $id = \Illuminate\Support\Facades\DB::table('salon_personelleri')->where('salon_id', (int) $salonId)
                ->where('yetkili_id', (int) $yetkili->id)->value('id');
            return $id ? (int) $id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function sonuc($ok, $gonderildi, $hata, $hedef, $mesaj, $kod = 200)
    {
        return ['basarili' => $ok, 'gonderildi' => $gonderildi, 'hata' => $hata, 'hedef' => $hedef, 'mesaj' => $mesaj, 'kod' => $kod];
    }
}
