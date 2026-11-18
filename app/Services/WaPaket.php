<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistani AYLIK MESAJ PAKETI (santral dakika paketinin WhatsApp karsiligi; SantralPaket deseni).
 *
 * Sayilan: yalniz YAPAY ZEKA (Claude) cevaplari = wa_asistan_cevaplar.ai=1. Bedava katman cevaplari (fiyat, bilgi,
 * selam, ogrenilen, ayni fotografin tekrari) ve geri kazanma mesajlari paketten DUSMEZ.
 * Donem = takvim ayi; devretmez. Paket bitince (asim_davranis=durdur) asistan o hatta cevap vermez; %80 ve %100'de
 * isletmeye bildirim (ayda bir kez).
 * Paket TANIMSIZ ama .env WA_ASISTAN_SALONLAR'da acik salon: sinirsiz (eski davranis, geriye uyum).
 */
class WaPaket
{
    const TABLO = 'wa_asistan_paketleri';
    const SEMA = 'wa_paket_sema_v1';

    const HAZIR = [
        ['ad' => 'Başlangıç',   'mesaj' => 500,  'ucret' => 990],
        ['ad' => 'Standart',    'mesaj' => 1500, 'ucret' => 1990],
        ['ad' => 'Profesyonel', 'mesaj' => 5000, 'ucret' => 4990],
    ];

    public static function semaGaranti(): void
    {
        if (Cache::get(self::SEMA)) return;
        try {
            if (!Schema::hasTable(self::TABLO)) {
                Schema::create(self::TABLO, function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id')->unique();       // WhatsApp hattinin salonu
                    $t->string('paket_adi', 60)->nullable();
                    $t->unsignedInteger('dahil_mesaj')->default(0);     // aylik AI cevabi hakki
                    $t->decimal('aylik_ucret', 10, 2)->default(0);      // TL
                    $t->string('asim_davranis', 10)->default('durdur'); // durdur | devam
                    $t->text('notlar')->nullable();
                    $t->string('guncelleyen', 120)->nullable();
                    $t->timestamps();
                });
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[WA-PAKET] sema hatasi: ' . $e->getMessage());
        }
    }

    public static function paket(int $salonId)
    {
        self::semaGaranti();
        try {
            $p = Cache::remember("wa_paket_{$salonId}", 1, function () use ($salonId) {
                return DB::table(self::TABLO)->where('salon_id', $salonId)->first() ?: '-';
            });
            return is_object($p) ? $p : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Bu ay kullanilan AI cevabi (hat salonu bazinda). */
    public static function kullanilan(int $salonId, ?string $ay = null): int
    {
        try {
            if (!WaAsistanVeri::semaGaranti()) return 0;
            [, $bas, $bit] = SantralPaket::donem($ay);
            return (int) DB::table('wa_asistan_cevaplar')->where('salon_id', $salonId)->where('ai', 1)
                ->where('created_at', '>=', $bas)->where('created_at', '<', $bit)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Saf hesap: paket + kullanim -> sayac. kapali = durdur paketinde hak bitti.
     * @return array ['var','dahil','kullanilan','kalan','yuzde','kapali','seviye'(null|80|100),'asim']
     */
    public static function hesapla($paket, int $kullanilan): array
    {
        if (!$paket) {
            return ['var' => false, 'dahil' => 0, 'kullanilan' => $kullanilan, 'kalan' => null, 'yuzde' => 0, 'kapali' => false, 'seviye' => null, 'asim' => 0];
        }
        $dahil = (int) $paket->dahil_mesaj;
        $yuzde = $dahil > 0 ? (int) floor($kullanilan / $dahil * 100) : 100;
        $kapali = ($paket->asim_davranis ?? 'durdur') === 'durdur' && $kullanilan >= $dahil;
        return [
            'var' => true,
            'dahil' => $dahil,
            'kullanilan' => $kullanilan,
            'kalan' => max(0, $dahil - $kullanilan),
            'yuzde' => min(100, $yuzde),
            'kapali' => $kapali,
            'seviye' => $yuzde >= 100 ? 100 : ($yuzde >= 80 ? 80 : null),
            'asim' => max(0, $kullanilan - $dahil),
        ];
    }

    public static function durum(int $salonId): array
    {
        return self::hesapla(self::paket($salonId), self::kullanilan($salonId));
    }

    /** Paket bitti, asistan bu hatta cevap vermesin mi? (60 sn onbellek; hata -> false: asistan kapanmasin) */
    public static function kapali(int $salonId): bool
    {
        try {
            return (bool) Cache::remember("wa_paket_kapali_{$salonId}", 1, function () use ($salonId) {
                return self::durum($salonId)['kapali'] ? 1 : 0;
            });
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Her AI cevabindan sonra: %80 / %100 esigi ilk kez gecildiyse isletmeye (ayda bir kez) bildir. */
    public static function esikKontrol(int $salonId): void
    {
        try {
            $d = self::durum($salonId);
            if (!$d['var'] || !$d['seviye']) return;
            if ($d['seviye'] >= 100) Cache::forget("wa_paket_kapali_{$salonId}");
            if (!Cache::add("wa_paket_esik_{$salonId}_" . date('Ym') . "_{$d['seviye']}", 1, 60 * 24 * 32)) return;
            $mesaj = $d['seviye'] >= 100
                ? "WhatsApp asistanı paketinizdeki {$d['dahil']} mesaj hakkı bu ay doldu. Asistan ay sonuna kadar müşterilerinize cevap vermeyecek; ek paket için bizimle iletişime geçin."
                : "WhatsApp asistanı paketinizin %{$d['yuzde']}'i kullanıldı ({$d['kullanilan']}/{$d['dahil']}). Paket dolunca asistan cevap vermeyi durduracak.";
            WaAsistanVeri::bildir($salonId, $mesaj);
        } catch (\Throwable $e) {
            Log::warning('[WA-PAKET] esik kontrolu: ' . $e->getMessage());
        }
    }

    public static function onbellekSil(int $salonId): void
    {
        Cache::forget("wa_paket_{$salonId}");
        Cache::forget("wa_paket_kapali_{$salonId}");
    }
}
