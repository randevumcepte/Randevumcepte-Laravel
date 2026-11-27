<?php

namespace App\Services;

use App\Personeller;
use App\Randevular;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Bildirimlerin "akilli" kismi (2026-11-27):
 *  - Randevu cevabi: musteri hatirlatmada "Geliyorum" / "Gecikecegim" der; personele bildirim gider.
 *  - Acilma kaydi: musteri bildirime dokundu (uygulama bildirir).
 *  - Once bildirim, acilmazsa SMS: salon acarsa, yeni uygulamasi olan musterinin hatirlatma SMS'i
 *    bekletilir; bu surede bildirimi acmaz / cevap vermezse SMS gider.
 *  - Kampanya izni: musteri kampanya/firsat bildirimlerini kapatabilir (randevu bildirimleri etkilenmez).
 *  - Sessiz saat: kampanya bildirimleri 21:00-09:00 arasi gonderilmez, 09:00'da gider.
 *  - Gelmeme olcumu: salonun gecmis randevularinda gelmeme orani ve hatirlatma cevabinin etkisi.
 * Tablolar calisma aninda olusturulur (BildirimTercih::semaGaranti ile ayni yontem).
 */
class AkilliBildirim
{
    const SEMA = 'akilli_bildirim_sema_v1';
    const YANITLAR = ['geliyorum', 'gecikecegim'];
    const SMS_BEKLETME_DK = 30;
    const SESSIZ_BAS = 21; // 21:00
    const SESSIZ_BIT = 9;  // 09:00

    public static function semaGaranti()
    {
        if (Cache::get(self::SEMA)) return;
        try {
            DB::statement('CREATE TABLE IF NOT EXISTS randevu_yanitlari (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                randevu_id INT UNSIGNED NOT NULL,
                salon_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                yanit VARCHAR(15) NOT NULL,
                gecikme_dk SMALLINT UNSIGNED NULL,
                kaynak VARCHAR(15) NOT NULL DEFAULT \'uygulama\',
                created_at TIMESTAMP NULL DEFAULT NULL,
                KEY ry_randevu (randevu_id),
                KEY ry_salon_tarih (salon_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::statement('CREATE TABLE IF NOT EXISTS bildirim_acilmalari (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                randevu_id INT UNSIGNED NULL,
                salon_id INT UNSIGNED NULL,
                tip VARCHAR(40) NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                KEY ba_user_randevu (user_id, randevu_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::statement('CREATE TABLE IF NOT EXISTS push_sms_yedek (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                salon_id INT UNSIGNED NOT NULL,
                randevu_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                telefon VARCHAR(30) NOT NULL,
                mesaj TEXT NOT NULL,
                gonderim_zamani DATETIME NOT NULL,
                durum VARCHAR(12) NOT NULL DEFAULT \'bekliyor\',
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                KEY psy_durum_zaman (durum, gonderim_zamani)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::statement('CREATE TABLE IF NOT EXISTS musteri_kampanya_izni (
                user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                kampanya TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::statement('CREATE TABLE IF NOT EXISTS bekleyen_bildirimler (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                salon_id INT UNSIGNED NULL,
                veri MEDIUMTEXT NOT NULL,
                gonderim_zamani DATETIME NOT NULL,
                durum VARCHAR(12) NOT NULL DEFAULT \'bekliyor\',
                created_at TIMESTAMP NULL DEFAULT NULL,
                KEY bb_durum_zaman (durum, gonderim_zamani)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            if (!Schema::hasColumn('salonlar', 'bildirim_push_once_sms')) {
                DB::statement('ALTER TABLE salonlar ADD COLUMN bildirim_push_once_sms TINYINT(1) NOT NULL DEFAULT 0');
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'yetenek')) {
                // Uygulamanin destekledigi ozellikler (aksiyon_v1: bildirimde dugme, acildi_v1: acilma bildirir)
                DB::statement('ALTER TABLE bildirim_kimlikleri ADD COLUMN yetenek VARCHAR(100) NULL');
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[AkilliBildirim] sema hatasi: ' . $e->getMessage());
        }
    }

    // ───────────────────────────────────────────── randevu cevabi

    /**
     * Musterinin randevu cevabi. Donus: ['ok'=>1,'mesaj'=>..] ya da ['ok'=>0,'hata'=>..].
     * Ayni cevap tekrar gelirse personele ikinci kez bildirim gitmez.
     */
    public static function randevuYanit(int $userId, int $randevuId, string $yanit, ?int $dakika = null, string $kaynak = 'uygulama'): array
    {
        if (!in_array($yanit, self::YANITLAR, true)) return ['ok' => 0, 'hata' => 'Geçersiz cevap.'];
        $r = Randevular::with('hizmetler', 'users')->where('id', $randevuId)->first();
        if (!$r || (int) $r->user_id !== $userId) return ['ok' => 0, 'hata' => 'Randevu bulunamadı.'];
        if (!in_array((int) $r->durum, [0, 1], true)) return ['ok' => 0, 'hata' => 'Bu randevu iptal edilmiş.'];
        $bas = strtotime(date('Y-m-d', strtotime($r->tarih)) . ' ' . date('H:i:s', strtotime($r->saat)));
        if ($bas < time() - 3 * 3600) return ['ok' => 0, 'hata' => 'Bu randevunun saati geçmiş.'];
        $dakika = $yanit === 'gecikecegim' ? max(5, min(120, (int) ($dakika ?: 15))) : null;

        self::semaGaranti();
        $son = DB::table('randevu_yanitlari')->where('randevu_id', $randevuId)->orderByDesc('id')->first();
        $ayni = $son && $son->yanit === $yanit && (int) $son->gecikme_dk === (int) $dakika;
        if (!$ayni) {
            DB::table('randevu_yanitlari')->insert([
                'randevu_id' => $randevuId, 'salon_id' => (int) $r->salon_id, 'user_id' => $userId, 'yanit' => $yanit,
                'gecikme_dk' => $dakika, 'kaynak' => mb_substr($kaynak, 0, 15), 'created_at' => date('Y-m-d H:i:s'),
            ]);
            self::personeleBildir($r, $yanit, $dakika);
        }
        self::acildi($userId, $randevuId, (int) $r->salon_id, 'randevu_yanit');

        $saat = date('H:i', $bas);
        return ['ok' => 1, 'mesaj' => $yanit === 'geliyorum'
            ? "Teşekkürler! Saat {$saat} randevunuza geleceğinizi salona ilettik."
            : "Salona yaklaşık {$dakika} dakika gecikeceğinizi ilettik."];
    }

    private static function personeleBildir($r, string $yanit, ?int $dakika): void
    {
        try {
            $salonId = (int) $r->salon_id;
            $ad = optional($r->users)->name ?: 'Müşteri';
            $zaman = date('d.m.Y', strtotime($r->tarih)) . ' ' . date('H:i', strtotime($r->saat));
            $baslik = $yanit === 'geliyorum' ? 'Müşteri geleceğini onayladı' : 'Müşteri gecikecek';
            $metin = $yanit === 'geliyorum'
                ? "{$ad}, {$zaman} randevusuna geleceğini onayladı."
                : "{$ad}, {$zaman} randevusuna yaklaşık {$dakika} dakika gecikeceğini bildirdi.";
            $idler = [];
            foreach ($r->hizmetler ?? [] as $h) if ($h->personel_id) $idler[] = (int) $h->personel_id;
            foreach (BildirimTercih::yoneticiIdleri($salonId) as $y) $idler[] = (int) $y;
            foreach (array_values(array_unique(array_filter($idler))) as $pid) {
                NotificationService::toStaff($pid, $salonId)
                    ->type(NotificationTypes::APPOINTMENT_REPLY)
                    ->title($baslik)->body($metin)
                    ->randevu((int) $r->id)
                    ->deepLink('appointment_detail', ['randevu_id' => (int) $r->id])
                    ->cevaptanSonra()
                    ->send();
            }
        } catch (\Throwable $e) {
            Log::warning('[AkilliBildirim] randevu cevabi personele bildirilemedi: ' . $e->getMessage());
        }
    }

    /** Randevunun son musteri cevabi (yoksa null): ['yanit','gecikme_dk','zaman'] */
    public static function sonYanit(int $randevuId): ?array
    {
        try {
            self::semaGaranti();
            $s = DB::table('randevu_yanitlari')->where('randevu_id', $randevuId)->orderByDesc('id')->first();
            return $s ? ['yanit' => $s->yanit, 'gecikme_dk' => $s->gecikme_dk, 'zaman' => $s->created_at] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ───────────────────────────────────────────── acilma

    public static function acildi(int $userId, ?int $randevuId, ?int $salonId, ?string $tip): void
    {
        try {
            self::semaGaranti();
            DB::table('bildirim_acilmalari')->insert([
                'user_id' => $userId, 'randevu_id' => $randevuId ?: null, 'salon_id' => $salonId ?: null,
                'tip' => $tip ? mb_substr($tip, 0, 40) : null, 'created_at' => date('Y-m-d H:i:s'),
            ]);
            if ($randevuId) {
                // Bekleyen yedek SMS'i iptal et: musteri hatirlatmayi gordu
                DB::table('push_sms_yedek')->where('user_id', $userId)->where('randevu_id', $randevuId)
                    ->where('durum', 'bekliyor')->update(['durum' => 'acildi', 'updated_at' => date('Y-m-d H:i:s')]);
            }
        } catch (\Throwable $e) {
            Log::warning('[AkilliBildirim] acilma kaydi: ' . $e->getMessage());
        }
    }

    // ───────────────────────────────────────────── once bildirim, acilmazsa SMS

    public static function pushOnceAcikMi(int $salonId): bool
    {
        try {
            self::semaGaranti();
            return (int) DB::table('salonlar')->where('id', $salonId)->value('bildirim_push_once_sms') === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function pushOnceKaydet(int $salonId, bool $deger, string $kaynak, $request = null): bool
    {
        self::semaGaranti();
        $eski = self::pushOnceAcikMi($salonId);
        if ($eski === $deger) return false;
        DB::table('salonlar')->where('id', $salonId)->update(['bildirim_push_once_sms' => $deger ? 1 : 0]);
        try {
            \App\SalonYonetim\Audit::logApi($salonId, $request, 'bildirim_ayar', 'salon', $salonId, null,
                'Önce bildirim, açılmazsa SMS ' . ($deger ? 'açıldı' : 'kapatıldı') . ' (' . $kaynak . ')');
        } catch (\Throwable $e) {
        }
        return true;
    }

    /**
     * Musteri hatirlatma SMS'i: salon "once bildirim" actiysa ve musterinin bu salonun uygulamasinin
     * acilma bildiren (yeni) surumu kuruluysa SMS bekletilir. true = bekletildi (cagiran SMS gondermez).
     */
    public static function smsBeklet($salon, $randevu, string $telefon, string $mesaj, string $bkKod): bool
    {
        try {
            $salonId = (int) $salon->id;
            $userId = (int) $randevu->user_id;
            if (!$userId || !self::pushOnceAcikMi($salonId)) return false;
            if (!BildirimTercih::izin($salonId, $bkKod, 'musteri', 'push')) return false;
            $bas = strtotime(date('Y-m-d', strtotime($randevu->tarih)) . ' ' . date('H:i:s', strtotime($randevu->saat)));
            if ($bas - time() < 50 * 60) return false; // randevuya az kaldi: hemen SMS
            if (!self::acilmaBildirenCihazVar($userId, $salonId)) return false;
            $zaman = min(time() + self::SMS_BEKLETME_DK * 60, $bas - 45 * 60);
            DB::table('push_sms_yedek')->insert([
                'salon_id' => $salonId, 'randevu_id' => (int) $randevu->id, 'user_id' => $userId,
                'telefon' => $telefon, 'mesaj' => $mesaj, 'gonderim_zamani' => date('Y-m-d H:i:s', $zaman),
                'durum' => 'bekliyor', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            Log::info('[AkilliBildirim] hatirlatma SMS bekletildi (once bildirim)', ['salon_id' => $salonId, 'randevu_id' => $randevu->id]);
            return true;
        } catch (\Throwable $e) {
            Log::warning('[AkilliBildirim] SMS bekletilemedi, hemen gonderilecek: ' . $e->getMessage());
            return false;
        }
    }

    private static function acilmaBildirenCihazVar(int $userId, int $salonId): bool
    {
        $q = DB::table('bildirim_kimlikleri')->where('user_id', $userId)->where('aktif', 1)
            ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '')->where('yetenek', 'like', '%acildi_v1%');
        $bundle = DB::table('salonlar')->where('id', $salonId)->value('app_bundle');
        if (!empty($bundle)) $q->where('app_bundle', $bundle);
        return $q->exists();
    }

    /** Zamani gelen bekletilmis SMS'ler: musteri acmadiysa / cevap vermediyse SMS gider. Donus: [gonderilen, iptal]. */
    public static function yedekleriIsle(): array
    {
        self::semaGaranti();
        $gonderilen = $iptal = 0;
        $satirlar = DB::table('push_sms_yedek')->where('durum', 'bekliyor')
            ->where('gonderim_zamani', '<=', date('Y-m-d H:i:s'))->orderBy('id')->limit(200)->get();
        foreach ($satirlar as $y) {
            // Ayni satiri iki surec islemesin
            $kilit = DB::table('push_sms_yedek')->where('id', $y->id)->where('durum', 'bekliyor')
                ->update(['durum' => 'isleniyor', 'updated_at' => date('Y-m-d H:i:s')]);
            if (!$kilit) continue;
            $sinir = date('Y-m-d H:i:s', strtotime($y->created_at) - 26 * 3600);
            $goruldu = DB::table('bildirim_acilmalari')->where('user_id', $y->user_id)->where('randevu_id', $y->randevu_id)
                    ->where('created_at', '>=', $sinir)->exists()
                || DB::table('randevu_yanitlari')->where('randevu_id', $y->randevu_id)->where('created_at', '>=', $sinir)->exists();
            $r = Randevular::where('id', $y->randevu_id)->first(['id', 'durum']);
            if ($goruldu || !$r || !in_array((int) $r->durum, [0, 1], true)) {
                DB::table('push_sms_yedek')->where('id', $y->id)->update(['durum' => $goruldu ? 'acildi' : 'iptal', 'updated_at' => date('Y-m-d H:i:s')]);
                $iptal++;
                continue;
            }
            try {
                app()->make(\App\Http\Controllers\Controller::class)->sms_gonder((int) $y->salon_id, [['to' => $y->telefon, 'message' => $y->mesaj]]);
                DB::table('push_sms_yedek')->where('id', $y->id)->update(['durum' => 'sms_gitti', 'updated_at' => date('Y-m-d H:i:s')]);
                $gonderilen++;
            } catch (\Throwable $e) {
                DB::table('push_sms_yedek')->where('id', $y->id)->update(['durum' => 'hata', 'updated_at' => date('Y-m-d H:i:s')]);
                Log::warning('[AkilliBildirim] yedek SMS gonderilemedi: ' . $e->getMessage(), ['id' => $y->id]);
            }
        }
        return [$gonderilen, $iptal];
    }

    // ───────────────────────────────────────────── kampanya izni + sessiz saat

    public static function kampanyaIzni(int $userId): bool
    {
        try {
            self::semaGaranti();
            $v = DB::table('musteri_kampanya_izni')->where('user_id', $userId)->value('kampanya');
            return $v === null ? true : (int) $v === 1;
        } catch (\Throwable $e) {
            return true;
        }
    }

    public static function kampanyaIzniKaydet(int $userId, bool $deger): void
    {
        self::semaGaranti();
        DB::table('musteri_kampanya_izni')->updateOrInsert(['user_id' => $userId],
            ['kampanya' => $deger ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /** Kampanya/firsat turu mu (randevu, odeme, seans vb. bilgilendirme degil) */
    public static function kampanyaTuruMu(string $tip): bool
    {
        return in_array($tip, [NotificationTypes::CAMPAIGN, NotificationTypes::DISCOUNT, NotificationTypes::WHEEL_CHANCE,
            NotificationTypes::BIRTHDAY], true);
    }

    /** Su an sessiz saat mi; oyleyse kampanyanin gidecegi zaman (unix), degilse null */
    public static function sessizSaatBitisi(?int $simdi = null): ?int
    {
        $simdi = $simdi ?: time();
        $saat = (int) date('G', $simdi);
        if ($saat >= self::SESSIZ_BIT && $saat < self::SESSIZ_BAS) return null;
        $gun = $saat >= self::SESSIZ_BAS ? date('Y-m-d', strtotime('+1 day', $simdi)) : date('Y-m-d', $simdi);
        return strtotime($gun . ' ' . sprintf('%02d:00:00', self::SESSIZ_BIT));
    }

    public static function ertele(?int $salonId, array $veri, int $zaman): void
    {
        self::semaGaranti();
        DB::table('bekleyen_bildirimler')->insert([
            'salon_id' => $salonId, 'veri' => json_encode($veri, JSON_UNESCAPED_UNICODE),
            'gonderim_zamani' => date('Y-m-d H:i:s', $zaman), 'durum' => 'bekliyor', 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Sessiz saatte ertelenen kampanya bildirimleri (zamani gelenler). Donus: gonderilen satir sayisi. */
    public static function ertelenenleriGonder(): int
    {
        self::semaGaranti();
        $n = 0;
        foreach (DB::table('bekleyen_bildirimler')->where('durum', 'bekliyor')->where('gonderim_zamani', '<=', date('Y-m-d H:i:s'))
                     ->orderBy('id')->limit(300)->get() as $b) {
            if (!DB::table('bekleyen_bildirimler')->where('id', $b->id)->where('durum', 'bekliyor')->update(['durum' => 'gonderildi'])) continue;
            try {
                NotificationService::durumdan((array) json_decode($b->veri, true))->send();
                $n++;
            } catch (\Throwable $e) {
                DB::table('bekleyen_bildirimler')->where('id', $b->id)->update(['durum' => 'hata']);
                Log::warning('[AkilliBildirim] ertelenen bildirim gonderilemedi: ' . $e->getMessage(), ['id' => $b->id]);
            }
        }
        return $n;
    }

    // ───────────────────────────────────────────── gelmeme olcumu

    /**
     * Son $gun gunun gecmis, onayli randevulari: geldi / gelmedi / isaretlenmemis, gelmeme orani
     * (isaretli olanlar icinde) ve hatirlatmaya "Geliyorum" diyenlerde gelmeme orani.
     */
    public static function gelmemeOlcumu(int $salonId, int $gun = 90): array
    {
        $bos = ['gun' => $gun, 'toplam' => 0, 'geldi' => 0, 'gelmedi' => 0, 'isaretsiz' => 0, 'oran' => null,
                'yanitli' => 0, 'yanitli_gelmedi' => 0, 'yanitli_oran' => null, 'aylik' => []];
        try {
            self::semaGaranti();
            $bas = date('Y-m-d', strtotime('-' . $gun . ' days'));
            $dun = date('Y-m-d', strtotime('-1 day'));
            $q = DB::table('randevular')->where('salon_id', $salonId)->where('durum', 1)->where('user_id', '!=', 2012)
                ->where('tarih', '>=', $bas)->where('tarih', '<=', $dun);
            $sat = (clone $q)->selectRaw("SUM(CASE WHEN randevuya_geldi = 1 THEN 1 ELSE 0 END) geldi,
                    SUM(CASE WHEN randevuya_geldi = 0 THEN 1 ELSE 0 END) gelmedi,
                    SUM(CASE WHEN randevuya_geldi IS NULL THEN 1 ELSE 0 END) isaretsiz, COUNT(*) toplam")->first();
            $geldi = (int) $sat->geldi; $gelmedi = (int) $sat->gelmedi;
            $yanitli = (clone $q)->whereExists(function ($s) {
                $s->select(DB::raw(1))->from('randevu_yanitlari as ry')->whereColumn('ry.randevu_id', 'randevular.id')->where('ry.yanit', 'geliyorum');
            })->selectRaw("SUM(CASE WHEN randevuya_geldi = 1 THEN 1 ELSE 0 END) geldi, SUM(CASE WHEN randevuya_geldi = 0 THEN 1 ELSE 0 END) gelmedi")->first();
            $yGeldi = (int) $yanitli->geldi; $yGelmedi = (int) $yanitli->gelmedi;
            $aylik = (clone $q)->selectRaw("DATE_FORMAT(tarih, '%Y-%m') ay, SUM(CASE WHEN randevuya_geldi = 1 THEN 1 ELSE 0 END) geldi,
                    SUM(CASE WHEN randevuya_geldi = 0 THEN 1 ELSE 0 END) gelmedi")->groupBy('ay')->orderBy('ay')->get()
                ->map(function ($a) {
                    $t = (int) $a->geldi + (int) $a->gelmedi;
                    return ['ay' => $a->ay, 'geldi' => (int) $a->geldi, 'gelmedi' => (int) $a->gelmedi,
                            'oran' => $t ? round($a->gelmedi / $t * 100, 1) : null];
                })->values()->all();
            return ['gun' => $gun, 'toplam' => (int) $sat->toplam, 'geldi' => $geldi, 'gelmedi' => $gelmedi,
                'isaretsiz' => (int) $sat->isaretsiz, 'oran' => ($geldi + $gelmedi) ? round($gelmedi / ($geldi + $gelmedi) * 100, 1) : null,
                'yanitli' => $yGeldi + $yGelmedi, 'yanitli_gelmedi' => $yGelmedi,
                'yanitli_oran' => ($yGeldi + $yGelmedi) ? round($yGelmedi / ($yGeldi + $yGelmedi) * 100, 1) : null, 'aylik' => $aylik];
        } catch (\Throwable $e) {
            Log::warning('[AkilliBildirim] gelmeme olcumu: ' . $e->getMessage());
            return $bos;
        }
    }
}
