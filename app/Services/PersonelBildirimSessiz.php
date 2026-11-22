<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Bildirimlerim (2026-11-23): personel kendi uygulama bildirimlerini TUR bazinda sessize alir.
 * Sessiz bildirim yine gelir ve zil listesine yazilir; yalniz ses/titresim yapmaz. Hangi bildirimin
 * gidecegine yine isletme sahibi karar verir (BildirimTercih); personel kapatamaz, sadece susturur.
 * Ayrim bildirim tipine (NotificationTypes) gore yapilir: NotificationService::send tek noktadan bakar,
 * gonderim yerlerine dokunulmaz. Guvenlik bildirimleri (sifre/yetki/pasif) asla susturulmaz.
 */
class PersonelBildirimSessiz
{
    const TABLO = 'personel_bildirim_sessiz';
    const SEMA = 'personel_bildirim_sessiz_sema_v1';

    private static $onbellek = [];

    /** kod => ad, aciklama, tipler (NotificationTypes), kodlar (BildirimTercih katalogu; kilit/aciklama icin) */
    const KATEGORILER = [
        'yeni_randevu' => ['ad' => 'Yeni randevular', 'ikon' => 'event_available',
            'aciklama' => 'Size atanan yeni randevu, online randevu talebi, müşteri geldi.',
            'tipler' => ['appointment_created', 'staff_assigned'],
            'kodlar' => ['randevu_olusturuldu', 'online_randevu_talebi', 'musteri_geldi', 'telefon_asistani']],
        'randevu_degisiklik' => ['ad' => 'Randevu değişiklikleri', 'ikon' => 'edit_calendar',
            'aciklama' => 'Tarih, saat veya personel değişikliği ve onaylanan talepler.',
            'tipler' => ['appointment_time_changed', 'appointment_approved'],
            'kodlar' => ['randevu_guncellendi', 'randevu_onaylandi']],
        'randevu_iptal' => ['ad' => 'İptal ve reddedilenler', 'ikon' => 'event_busy',
            'aciklama' => 'İptal edilen ya da reddedilen randevular.',
            'tipler' => ['appointment_cancelled'],
            'kodlar' => ['randevu_iptal', 'randevu_reddedildi']],
        'hatirlatma' => ['ad' => 'Hatırlatmalar', 'ikon' => 'alarm',
            'aciklama' => 'Yaklaşan randevu, seans, grup dersi, ödeme ve tekrar arama hatırlatmaları.',
            'tipler' => ['appointment_reminder', 'appointment_reminder_hour', 'session_reminder', 'class_reminder',
                'call_appointment_reminder', 'membership_expiring'],
            'kodlar' => ['hatirlatma_yaklasan', 'hatirlatma_bir_gun_once', 'seans_hatirlatma', 'ders_hatirlatma',
                'alacak_hatirlatma', 'senet_vade', 'tekrar_arama', 'ders_katilim_sor']],
        'musteri' => ['ad' => 'Müşteri hareketleri', 'ikon' => 'people',
            'aciklama' => 'Fotoğraf, değerlendirme, seans kullanımı, ödeme ve doğum günü bildirimleri.',
            'tipler' => ['musteri_resim_yuklendi', 'survey', 'new_message', 'payment_received', 'session_used',
                'birthday', 'stamp_reward'],
            'kodlar' => ['musteri_eklendi', 'dogum_gunu', 'kotu_degerlendirme', 'degerlendirme_anketi', 'musteri_foto',
                'seans_kullanim', 'para_islemleri']],
        'wa_asistan' => ['ad' => 'WhatsApp asistanı soruyor', 'ikon' => 'chat',
            'aciklama' => 'Asistan bir müşteri için size danıştığında.',
            'tipler' => ['wa_asistan_talep'],
            'kodlar' => ['wa_asistan_talep']],
        'genel' => ['ad' => 'Genel duyurular', 'ikon' => 'campaign',
            'aciklama' => 'Sistem duyuruları ve diğer bildirimler.',
            'tipler' => [],
            'kodlar' => []],
    ];

    /** Bu tipler hicbir zaman susturulmaz (hesap guvenligi). */
    const SUSTURULAMAZ = ['yetki_degisti', 'sifre_degisti', 'personel_pasif', 'personel_silindi'];

    /** Tablo yoksa olusturur (migrate kosmamis ortam icin idempotent). */
    public static function semaGaranti()
    {
        try {
            if (Cache::get(self::SEMA)) return;
            if (!Schema::hasTable(self::TABLO)) {
                Schema::create(self::TABLO, function ($t) {
                    $t->increments('id');
                    $t->unsignedInteger('personel_id');
                    $t->string('kategori', 40);
                    $t->timestamps();
                    $t->unique(['personel_id', 'kategori']);
                });
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[PersonelBildirimSessiz] sema hatasi', ['err' => $e->getMessage()]);
        }
    }

    /** Bildirim tipinin kategorisi; susturulamaz tipler icin null. */
    public static function kategori($tip)
    {
        if (in_array($tip, self::SUSTURULAMAZ, true)) return null;
        foreach (self::KATEGORILER as $kod => $k) {
            if (in_array($tip, $k['tipler'], true)) return $kod;
        }
        return 'genel';
    }

    /** Personelin sessize aldigi kategoriler. Kisa sureli (30 sn) surec ici onbellek: uzun calisan isci de degisikligi gorur. */
    public static function sessizler($personelId)
    {
        $personelId = (int) $personelId;
        if ($personelId <= 0) return [];
        if (isset(self::$onbellek[$personelId]) && self::$onbellek[$personelId]['zaman'] > time() - 30) return self::$onbellek[$personelId]['liste'];
        $liste = [];
        try {
            if (Schema::hasTable(self::TABLO)) {
                $liste = DB::table(self::TABLO)->where('personel_id', $personelId)->pluck('kategori')->all();
            }
        } catch (\Throwable $e) {}
        self::$onbellek[$personelId] = ['zaman' => time(), 'liste' => $liste];
        return $liste;
    }

    /** NotificationService: bu personele giden bu tipteki bildirim sessiz mi. Hata olursa sesli (eski davranis). */
    public static function sessizMi($personelId, $tip)
    {
        try {
            $kat = self::kategori((string) $tip);
            return $kat !== null && in_array($kat, self::sessizler($personelId), true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Ekran verisi: kategoriler + sessiz mi + isletme kapattigi icin hic gelmiyor mu (kilitli). */
    public static function ekranVerisi($salonId, $personelId, $alici)
    {
        $sessiz = self::sessizler($personelId);
        $liste = [];
        foreach (self::KATEGORILER as $kod => $k) {
            $acik = [];
            foreach ($k['kodlar'] as $bk) {
                if (!isset(BildirimTercih::KATALOG[$bk])) continue;
                try {
                    if (BildirimTercih::izin($salonId, $bk, $alici, 'push')) $acik[] = BildirimTercih::KATALOG[$bk]['ad'];
                } catch (\Throwable $e) {}
            }
            $liste[] = [
                'kod' => $kod,
                'ad' => $k['ad'],
                'aciklama' => $k['aciklama'],
                'ikon' => $k['ikon'],
                'sessiz' => in_array($kod, $sessiz, true),
                // Isletme bu turdeki tum uygulama bildirimlerini kapattiysa personel icin anlamsiz (gri, kilitli)
                'kilitli' => !empty($k['kodlar']) && empty($acik),
                'acik_olanlar' => $acik,
            ];
        }
        return ['kategoriler' => $liste, 'alici' => $alici];
    }

    /** Sesli/sessiz degistirir ve Log Hareketleri'ne yazar. Degisiklik yoksa false. */
    public static function kaydet($salonId, $personelId, $kategori, $sessiz, $req = null, $kisiAdi = null)
    {
        if (!isset(self::KATEGORILER[$kategori])) return null;
        self::semaGaranti();
        $personelId = (int) $personelId;
        $simdi = date('Y-m-d H:i:s');
        $var = DB::table(self::TABLO)->where('personel_id', $personelId)->where('kategori', $kategori)->exists();
        if ($sessiz === $var) return false;
        if ($sessiz) {
            DB::table(self::TABLO)->insert(['personel_id' => $personelId, 'kategori' => $kategori, 'created_at' => $simdi, 'updated_at' => $simdi]);
        } else {
            DB::table(self::TABLO)->where('personel_id', $personelId)->where('kategori', $kategori)->delete();
        }
        unset(self::$onbellek[$personelId]);
        $ad = self::KATEGORILER[$kategori]['ad'];
        $aciklama = ($kisiAdi ? $kisiAdi . ' · ' : '') . 'Bildirimlerim: "' . $ad . '" ' . ($sessiz ? 'sessize alındı' : 'sesli yapıldı');
        $meta = ['personel_id' => $personelId, 'kategori' => $kategori, 'sessiz' => $sessiz ? 1 : 0];
        try {
            $web = false;
            try { $web = \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->check(); } catch (\Throwable $e) {}
            if (!$web && $req) {
                \App\SalonYonetim\Audit::logApi($salonId, $req, 'personel_bildirim_sessiz', 'personel', $personelId, 'Bildirimlerim', $aciklama, $meta);
            } else {
                \App\SalonYonetim\Audit::log($salonId, 'personel_bildirim_sessiz', 'personel', $personelId, 'Bildirimlerim', $aciklama, $meta);
            }
        } catch (\Throwable $e) {}
        return true;
    }
}
