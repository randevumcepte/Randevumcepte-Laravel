<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp kontör (kredi) yönetimi. 1 mesaj = 1 kontör.
 *
 * - Salon-basi 60 gun UCRETSIZ deneme: whatsapp_deneme_bitis kolonuna kadar
 *   kontor dusmez, engel yok. Elle uzatilabilir (ornek: mevcut salonlar icin 2026-08-31)
 * - Deneme bitisinden sonra: her WhatsApp mesaji 1 kontor duser; bakiye yoksa gonderilmez
 *   (arayan taraf SMS'e dusurur).
 * - Deneme tarihi bilinmiyorsa (kolon yok / degeri null) fallback global BASLANGIC.
 *
 * Bakiye salonlar.whatsapp_kontor'da; her hareket whatsapp_kontor_hareketleri'nde loglanır.
 */
class KontorServisi
{
    /** Fallback: hicbir salon-ozel deneme bitisi yoksa kullanilan global tarih. */
    const BASLANGIC = '2026-09-01';

    /**
     * Kontorlu donem basladi mi? Salon verilirse salon-ozel deneme_bitis'e bakilir,
     * verilmezse (veya kolon null'sa) global BASLANGIC fallback'i kullanilir.
     * ORNEK: salonun whatsapp_deneme_bitis=2026-11-30 ise 1 Aralik'a kadar
     * kontor dusmez, engel yok. Salon parametresi id (int) veya Eloquent obj olabilir.
     */
    public static function kontorlusDonemMi($salon = null)
    {
        if ($salon) {
            $bitis = null;
            if (is_object($salon)) {
                $bitis = $salon->whatsapp_deneme_bitis ?? null;
            }
            // Obje olsa bile null gelirse (attribute yuklenmemis) veya id gecildiyse DB'den cek
            if (empty($bitis)) {
                try {
                    $id = is_object($salon) ? ($salon->id ?? null) : (int) $salon;
                    if ($id) {
                        $bitis = DB::table('salonlar')->where('id', (int) $id)->value('whatsapp_deneme_bitis');
                    }
                } catch (\Throwable $e) {}
            }
            if (!empty($bitis)) {
                // '0000-00-00' gibi bozuk degerleri es gec, fallback'e dus
                $s = substr((string) $bitis, 0, 10);
                if ($s !== '' && $s !== '0000-00-00') {
                    return date('Y-m-d') > $s;
                }
            }
        }
        return date('Y-m-d') >= self::BASLANGIC;
    }

    protected static function kolonVar()
    {
        try { return Schema::hasColumn('salonlar', 'whatsapp_kontor'); }
        catch (\Throwable $e) { return false; }
    }

    protected static function selfHeal()
    {
        try {
            if (!Schema::hasColumn('salonlar', 'whatsapp_kontor')) {
                DB::statement('ALTER TABLE salonlar ADD COLUMN whatsapp_kontor INT NOT NULL DEFAULT 0');
            }
            if (!Schema::hasTable('whatsapp_kontor_hareketleri')) {
                DB::statement('CREATE TABLE whatsapp_kontor_hareketleri (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    salon_id BIGINT UNSIGNED,
                    tip VARCHAR(20),
                    adet INT,
                    bakiye_sonrasi INT NULL,
                    aciklama VARCHAR(160) NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX (salon_id, created_at)
                )');
            }
        } catch (\Throwable $e) {}
    }

    /** Salonun güncel kontör bakiyesi. */
    public static function bakiye($salon)
    {
        $id = is_object($salon) ? ($salon->id ?? null) : $salon;
        if (!$id) return 0;
        try {
            return (int) DB::table('salonlar')->where('id', $id)->value('whatsapp_kontor');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Gönderim için kontör yeterli mi? Ücretsiz dönemde HER ZAMAN true (engel yok).
     * Kolon yoksa da true (sistem yeni kurulmuş, engelleme).
     */
    public static function yeterliMi($salon, $adet = 1)
    {
        // Salon-ozel deneme bitisine bakilir; deneme surerken engel yok.
        if (!self::kontorlusDonemMi($salon)) return true;
        if (!self::kolonVar()) return true;
        return self::bakiye($salon) >= $adet;
    }

    /**
     * Kontör düşer (gönderim başarılı olunca). Ücretsiz dönemde hiçbir şey yapmaz.
     * Atomik: negatife düşürmez.
     */
    public static function dus($salon, $adet = 1, $aciklama = 'whatsapp-mesaj')
    {
        // Salon-ozel deneme bitisine bakilir; deneme surerken kontor dusmez.
        if (!self::kontorlusDonemMi($salon)) return true;
        $id = is_object($salon) ? ($salon->id ?? null) : $salon;
        if (!$id || $adet < 1) return false;
        self::selfHeal();
        try {
            // Atomik dus — sadece yeterli bakiye varsa
            $etkilenen = DB::table('salonlar')
                ->where('id', $id)
                ->where('whatsapp_kontor', '>=', $adet)
                ->update(['whatsapp_kontor' => DB::raw('whatsapp_kontor - ' . (int) $adet)]);
            if (!$etkilenen) return false;
            $kalan = self::bakiye($id);
            self::hareket($id, 'harcama', -abs((int) $adet), $kalan, $aciklama);
            return true;
        } catch (\Throwable $e) {
            \Log::warning('[KONTOR] dus hata: ' . $e->getMessage());
            return false;
        }
    }

    /** Kontör yükler (manuel). Bakiyeyi artırır + hareket loglar. */
    public static function yukle($salonId, $adet, $aciklama = 'manuel-yukleme')
    {
        $adet = (int) $adet;
        if (!$salonId || $adet == 0) return ['ok' => false, 'mesaj' => 'Geçersiz miktar.'];
        self::selfHeal();
        try {
            DB::table('salonlar')->where('id', $salonId)
                ->update(['whatsapp_kontor' => DB::raw('whatsapp_kontor + ' . $adet)]);
            $kalan = self::bakiye($salonId);
            self::hareket($salonId, $adet > 0 ? 'yukleme' : 'harcama', $adet, $kalan, $aciklama);
            return ['ok' => true, 'bakiye' => $kalan];
        } catch (\Throwable $e) {
            \Log::error('[KONTOR] yukle hata: ' . $e->getMessage());
            return ['ok' => false, 'mesaj' => $e->getMessage()];
        }
    }

    protected static function hareket($salonId, $tip, $adet, $bakiyeSonrasi, $aciklama)
    {
        try {
            DB::table('whatsapp_kontor_hareketleri')->insert([
                'salon_id' => $salonId,
                'tip' => $tip,
                'adet' => (int) $adet,
                'bakiye_sonrasi' => (int) $bakiyeSonrasi,
                'aciklama' => mb_substr((string) $aciklama, 0, 160),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}
    }
}
