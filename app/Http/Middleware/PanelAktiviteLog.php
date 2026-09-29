<?php

namespace App\Http\Middleware;

use App\SalonYonetim\Audit;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * PANEL AKTİVİTE LOG — işletme yönetim panelindeki HER değiştiren (POST/PUT/PATCH/DELETE)
 * isteği otomatik loglar. terminate() YANIT GÖNDERİLDİKTEN SONRA çalışır → isteği asla
 * bozmaz/yavaşlatmaz (tamamı try/catch). Böylece "tüm hareketler" tek tek elle
 * Audit::log yazmadan salon_aktivite_log'a düşer.
 *
 * Gürültü (ajax/arama/bildirim/heartbeat) ve zaten elle loglanan (login/logout) yollar ATLANIR.
 */
class PanelAktiviteLog
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        try {
            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) return;

            $path = strtolower(ltrim($request->path(), '/'));

            // Gürültü + zaten elle loglanan yollar -> atla (çift kayıt / anlamsız kayıt olmasın)
            $atlaParcalar = [
                'login', 'logout', 'oturum', 'csrf', 'heartbeat', 'ping', 'online',
                'ajax', 'arama', 'autocomplete', 'canli-arama', 'live-search', 'suggest',
                'bildirim', 'notification', 'okundu', 'read', 'sayac', 'badge',
                'fcm', 'token', 'sms-bakiye', 'bakiye-sorgu', 'kontrol-et', 'check',
                'yukle-ajax', 'liste-ajax', 'getir-ajax', 'sound', 'ses', 'upload-temp',
            ];
            foreach ($atlaParcalar as $s) {
                if (strpos($path, $s) !== false) return;
            }

            // Sadece giriş yapmış panel kullanıcısı (yetkili/satış ortağı) — misafir/sistem POST'larını atla
            if (!Auth::guard('isletmeyonetim')->check() && !Auth::guard('satisortakligi')->check()) return;

            // Salon id: önce ?sube, sonra route param, sonra kullanıcının ilk aktif işletmesi
            $salonId = $request->input('sube') ?: $request->route('sube');
            if (!$salonId && Auth::guard('isletmeyonetim')->check()) {
                $salonId = optional(
                    optional(Auth::guard('isletmeyonetim')->user())->yetkili_olunan_isletmeler
                )->where('aktif', 1)->pluck('salon_id')->first();
            } elseif (!$salonId && Auth::guard('satisortakligi')->check()) {
                $salonId = 15;
            }
            $salonId = (int) $salonId;
            if ($salonId <= 0) return;

            [$action, $hedefTip] = $this->etiket($request, $path);
            Audit::log($salonId, $action, $hedefTip, null, $this->hedefEtiket($request), $this->aciklama($request), ['kaynak' => 'panel_oto']);
        } catch (\Throwable $e) { /* log yazımı asla iş akışını bozmaz */ }
    }

    /** Yol / route adından okunur bir action üret. */
    private function etiket($request, string $path): array
    {
        $rota = $request->route();
        $ham = ($rota && method_exists($rota, 'getName') && $rota->getName()) ? $rota->getName() : $path;
        // isletmeadmin.randevu.kaydet -> randevu_kaydet ; isletmeyonetim/randevu-sil -> randevu_sil
        $ham = str_replace(['isletmeadmin.', 'isletmeyonetim.', 'isletmesatisortagi.', 'isletmeyonetim/', 'store.', 'admin.'], '', $ham);
        $action = preg_replace('/[^a-z0-9]+/', '_', strtolower($ham));
        $action = trim($action, '_');
        if ($action === '') $action = 'islem';
        // Hedef tipini action ilk parçasından tahmin et (randevu/musteri/personel/urun/...)
        $ilk = explode('_', $action)[0];
        $bilinen = ['randevu', 'musteri', 'danisan', 'personel', 'urun', 'hizmet', 'paket', 'kampanya', 'sms', 'kupon', 'cark', 'form', 'odeme', 'kasa', 'stok', 'ayar', 'salon', 'seans', 'anket', 'duyuru', 'reklam'];
        $hedefTip = in_array($ilk, $bilinen) ? $ilk : null;
        return [mb_substr($action, 0, 70), $hedefTip];
    }

    /** İstekteki isim/başlık türü alanlardan kısa bir hedef etiketi. */
    private function hedefEtiket($request): ?string
    {
        foreach (['ad_soyad', 'adsoyad', 'ad', 'isim', 'adi', 'baslik', 'name', 'title', 'unvan'] as $k) {
            $v = $request->input($k);
            if (is_string($v) && trim($v) !== '') return mb_substr(trim($v), 0, 120);
        }
        return null;
    }

    /** Öne çıkan parametreleri özetle. */
    private function aciklama($request): ?string
    {
        $anahtar = ['ad', 'adsoyad', 'ad_soyad', 'isim', 'baslik', 'tip', 'durum', 'tutar', 'fiyat', 'tarih', 'saat', 'miktar', 'oran', 'sebep', 'telefon'];
        $parca = [];
        foreach ($anahtar as $k) {
            $v = $request->input($k);
            if ($v !== null && $v !== '' && !is_array($v)) {
                $parca[] = $k . ': ' . mb_substr((string) $v, 0, 40);
            }
            if (count($parca) >= 4) break;
        }
        return $parca ? implode(' · ', $parca) : null;
    }
}
