<?php

namespace App\Services;

use App\IsletmeYetkilileri;
use App\Personeller;
use Illuminate\Support\Facades\Log;

/**
 * Guvenlik: bir hesabin oturum(lar)ini tek noktadan sonlandirma.
 *
 * "Gercek" yaptirim Passport token revoke'tur: oauth_access_tokens.revoked=1
 * yapilir, boylece o token'la gelen bir sonraki auth:*-api istegi 401 alir.
 * Ayrica force_logout push'u ile (uygulama acikken) anlik + mesajli logout
 * saglanir. Push ulasmazsa bile token iptali kaldigi icin oturum guvenli
 * sekilde sonlanir.
 *
 * NOT: Kimlik/token modeli login'de gsm1/cep_telefon ile eslesen kayittir:
 *   - Musteri  -> App\User            (users.password)
 *   - Personel -> App\IsletmeYetkilileri (isletmeyetkilileri.password)
 * Personelin salon bazli satiri App\Personeller (salon_personelleri) olup
 * bir isletmeyetkilileri kaydina yetkili_id ile baglidir; push hedefi ise
 * bildirim_kimlikleri.isletme_yetkili_id = salon_personelleri.id'dir.
 */
class OturumServisi
{
    /**
     * HasApiTokens kullanan bir modelin (User / IsletmeYetkilileri) tum aktif
     * Passport token'larini revoke eder. $haricTokenId verilirse o token
     * (islemi yapan kullanicinin kendi oturumu) korunur.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null $model
     * @param  string|null $haricTokenId  korunacak token id (opsiyonel)
     * @return int iptal edilen token sayisi
     */
    public static function tokenlariIptalEt($model, ?string $haricTokenId = null): int
    {
        if (!$model || !method_exists($model, 'tokens')) return 0;
        try {
            $q = $model->tokens()->where('revoked', false);
            if ($haricTokenId) {
                $q->where('id', '!=', $haricTokenId);
            }
            return (int) $q->update(['revoked' => true]);
        } catch (\Throwable $e) {
            Log::warning('OturumServisi tokenlariIptalEt hata: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * force_logout push'u tek noktadan gonderir (best-effort; hata yutulur).
     * Hedef = salon_personelleri.id (bildirim_kimlikleri.isletme_yetkili_id).
     */
    public static function forceLogoutPush(int $personelId, ?int $salonId, string $tip, string $baslik, string $govde): void
    {
        try {
            NotificationService::toStaff($personelId, $salonId)
                ->type($tip)
                ->title($baslik)
                ->body($govde)
                ->extra(['force_logout' => '1'])
                ->send();
        } catch (\Throwable $e) {
            Log::warning('OturumServisi forceLogoutPush hata: ' . $e->getMessage());
        }
    }

    /**
     * Musteriye (users) force_logout push'u gonderir (best-effort).
     * Hedef = users.id (bildirim_kimlikleri.user_id).
     */
    public static function forceLogoutPushMusteri(int $userId, ?int $salonId, string $tip, string $baslik, string $govde): void
    {
        try {
            NotificationService::toCustomer($userId, $salonId)
                ->type($tip)
                ->title($baslik)
                ->body($govde)
                ->extra(['force_logout' => '1'])
                ->send();
        } catch (\Throwable $e) {
            Log::warning('OturumServisi forceLogoutPushMusteri hata: ' . $e->getMessage());
        }
    }

    /**
     * Bir isletmeyetkilileri hesabinin (login kimligi) TUM salon personel
     * satirlarina force_logout push'u gonderir. Panel self-service sifre
     * degisimi gibi, token revoke'un yaninda mobil cihazlari anlik logout icin.
     * Token iptali ayrica yapilir; bu yalnizca push'tur.
     */
    public static function yetkiliTumCihazlaraForceLogout(int $yetkiliId, string $tip, string $baslik, string $govde): void
    {
        try {
            $rows = Personeller::where('yetkili_id', $yetkiliId)->get(['id', 'salon_id']);
            foreach ($rows as $r) {
                self::forceLogoutPush(
                    (int) $r->id,
                    $r->salon_id !== null ? (int) $r->salon_id : null,
                    $tip, $baslik, $govde
                );
            }
        } catch (\Throwable $e) {
            Log::warning('OturumServisi yetkiliTumCihazlaraForceLogout hata: ' . $e->getMessage());
        }
    }

    /**
     * Bir salon_personelleri (Personeller) satiri pasife alindiktan / silindikten
     * SONRA cagrilir. Bagli login kimliginin (isletmeyetkilileri, yetkili_id)
     * BASKA aktif salon satiri KALMADIYSA:
     *   - o kimligin tum Passport token'larini revoke eder,
     *   - force_logout push gonderir.
     * Baska aktif salon varsa hicbir sey yapmaz (login'in bugunku davranisiyla
     * birebir: kismi pasif/silmede giris hala gecerli -> oturuma dokunma).
     *
     * @param int      $personelId salon_personelleri.id (push hedefi)
     * @param int|null $yetkiliId  salon_personelleri.yetkili_id = isletmeyetkilileri.id
     * @param int|null $salonId    islem yapilan salon (push brand izolasyonu icin)
     * @return bool oturum sonlandirildi mi
     */
    public static function personelErisimiKalktiysaSonlandir(
        int $personelId,
        ?int $yetkiliId,
        ?int $salonId,
        string $tip,
        string $baslik,
        string $govde
    ): bool {
        // Sistem/super hesaplar (ornegin yetkili_id=3) asla sonlandirilmaz.
        if (!$yetkiliId || in_array((int) $yetkiliId, Personeller::SISTEM_YETKILI_IDLER, true)) {
            return false;
        }

        // MARKA (app_bundle) BAZLI kontrol: islem yapilan salonun markasindaki
        // (ayni app_bundle'a ait) isletmelerde personelin hala AKTIF bir satiri
        // var mi? Varsa bu markanin oturumu gecerli -> dokunma. Yoksa sonlandir.
        // BASKA markalardaki aktiflik bu markanin oturumunu ILGILENDIRMEZ; login de
        // zaten app_bundle'a gore filtreler. (Salon cozulemezse global kontrole
        // duser -> fail-safe.)
        $markaSalonIdler = null;
        if ($salonId) {
            $appBundle = \App\Salonlar::where('id', $salonId)->value('app_bundle');
            if (!empty($appBundle)) {
                $markaSalonIdler = \App\Salonlar::where('app_bundle', $appBundle)
                    ->pluck('id')->all();
            }
        }

        // (pasifyap oncesinde aktif=0 kaydeder / personelsil satiri siler,
        // yani bu sorgu islem SONRASI dogru sonucu verir.)
        $q = Personeller::where('yetkili_id', $yetkiliId)->where('aktif', 1);
        if (!empty($markaSalonIdler)) {
            $q->whereIn('salon_id', $markaSalonIdler);
        }
        $kalanAktif = $q->exists();
        if ($kalanAktif) {
            return false; // marka icinde hala aktif -> oturuma dokunma (non-breaking)
        }

        $yetkili = IsletmeYetkilileri::find($yetkiliId);
        if ($yetkili) {
            self::tokenlariIptalEt($yetkili);
        }
        self::forceLogoutPush($personelId, $salonId, $tip, $baslik, $govde);
        return true;
    }
}
