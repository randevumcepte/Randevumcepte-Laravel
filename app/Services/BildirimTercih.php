<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Bildirim tercihleri (2026-11-22): her otomatik bildirim x alici (musteri/personel) x kanal
 * (SMS / WhatsApp / uygulama bildirimi) isletme sahibi tarafindan AYRI AYRI acilip kapatilir.
 * Ayni bildirim istenirse uc kanaldan birden gider; hicbiri acik degilse hic gitmez.
 *
 * - Tablo: salon_bildirim_tercihleri (salon_id, kod, alici, sms, whatsapp, push).
 * - Salon geneli: salonlar.bildirim_wa_yedek_sms (varsayilan 1) = WhatsApp gidemezse SMS'e dus.
 *   SMS paketi/bakiyesi yoksa yedek SMS de gitmez.
 * - Ilk okumada eski salon_sms_ayarlari'ndan TOHUMLANIR, bugunku davranis aynen korunur:
 *   WA bagli salonda bayrak acik olan -> whatsapp=1 (+yedek SMS), bagli degilse -> sms=1.
 *   Push bugun ayarsiz gidiyorsa 1, eski ayara bagliysa ayar, hic yoksa 0.
 * - Kayit sirasinda eski salon_sms_ayarlari.musteri/personel = "herhangi bir kanal acik" olarak
 *   yazilir (henuz cevrilmemis okuyucular ve eski mobil surumler icin).
 *
 * Cagri yeri kullanimi:
 *   BildirimTercih::acikMi($salonId, 'randevu_iptal', 'musteri')    // mesaj hazirlansin mi
 *   BildirimTercih::izin($salonId, 'randevu_iptal', 'musteri', 'push') // kendi push'unu atan yer
 *   $mesajlar[] = ['to'=>..,'message'=>..,'bk'=>'randevu_iptal','alici'=>'musteri','user_id'=>..];
 *   -> sms_gonder_bildirimli / dagit() satir bazinda kanallari uygular.
 */
class BildirimTercih
{
    const SEMA = 'bildirim_tercih_sema_v1';
    const TABLO = 'salon_bildirim_tercihleri';
    const KANALLAR = ['sms', 'whatsapp', 'push'];
    const ALICILAR = ['musteri', 'personel', 'yonetici'];

    const GRUPLAR = [
        'randevu'    => 'Randevu',
        'hatirlatma' => 'Hatırlatmalar',
        'musteri'    => 'Müşteri',
        'seans'      => 'Seans & Paket',
        'para'       => 'Para & Kasa',
        'guvenlik'   => 'Onay ve Doğrulama Kodları',
        'kampanya'   => 'Kampanya',
        'asistan'    => 'Asistanlar',
    ];

    /**
     * Katalog. alicilar[x]:
     *   eski     => [ayar_id, 'musteri'|'personel'] eski salon_sms_ayarlari bayragi (tohum), null = yok
     *   mesaj    => eski bayrak yoksa SMS/WA tohumu (0/1)
     *   sadeceSms=> tohumda WA yerine SMS (bugun yalnizca SMS giden)
     *   push     => 1 bugun ayarsiz gidiyor | 0 bugun yok | 'ayar' eski bayraga bagli
     */
    const KATALOG = [
        // ── Randevu ──
        'randevu_olusturuldu' => ['grup' => 'randevu', 'ad' => 'Randevu oluşturulduğunda',
            'aciklama' => 'Randevu eklendiğinde müşteriye ve randevudaki personele bilgi gider.', 'push_tip' => 'appointment_created',
            'alicilar' => ['musteri' => ['eski' => [12, 'musteri'], 'push' => 1], 'personel' => ['eski' => [12, 'personel'], 'push' => 1], 'yonetici' => ['eski' => [12, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'randevu_guncellendi' => ['grup' => 'randevu', 'ad' => 'Randevu güncellendiğinde',
            'aciklama' => 'Tarih, saat, personel veya hizmet değişince (sürükle-bırak dahil).', 'push_tip' => 'appointment_time_changed',
            'alicilar' => ['musteri' => ['eski' => [14, 'musteri'], 'push' => 1], 'personel' => ['eski' => [14, 'personel'], 'push' => 1], 'yonetici' => ['eski' => [14, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'randevu_onaylandi' => ['grup' => 'randevu', 'ad' => 'Randevu talebi onaylandığında',
            'aciklama' => 'Online gelen randevu talebini onayladığınızda.', 'push_tip' => 'appointment_approved',
            // personel push bugun 2.personel bayragina bagli (randevuonayla) -> 'ayar'
            'alicilar' => ['musteri' => ['eski' => [2, 'musteri'], 'push' => 1], 'personel' => ['eski' => [2, 'personel'], 'push' => 'ayar'], 'yonetici' => ['eski' => [2, 'personel'], 'push' => 'ayar', 'kopya' => 'personel']]],
        'randevu_reddedildi' => ['grup' => 'randevu', 'ad' => 'Randevu talebi reddedildiğinde',
            'aciklama' => 'Online gelen randevu talebini reddettiğinizde.', 'push_tip' => 'appointment_cancelled',
            'alicilar' => ['musteri' => ['eski' => [3, 'musteri'], 'push' => 1], 'personel' => ['eski' => [3, 'personel'], 'push' => 1], 'yonetici' => ['eski' => [3, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'randevu_iptal' => ['grup' => 'randevu', 'ad' => 'Randevu iptal edildiğinde',
            'aciklama' => 'Aktif bir randevu iptal edildiğinde.', 'push_tip' => 'appointment_cancelled',
            'alicilar' => ['musteri' => ['eski' => [3, 'musteri'], 'push' => 1], 'personel' => ['eski' => [3, 'personel'], 'push' => 1], 'yonetici' => ['eski' => [3, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'online_randevu_talebi' => ['grup' => 'randevu', 'ad' => 'Online randevu talebi alındığında',
            'aciklama' => 'Müşteri web sitesinden, uygulamadan veya telefon asistanından randevu talebi bıraktığında.', 'push_tip' => 'appointment_created',
            'alicilar' => ['musteri' => ['eski' => [11, 'musteri'], 'push' => 0], 'personel' => ['eski' => [11, 'personel'], 'push' => 1], 'yonetici' => ['eski' => [11, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'musteri_geldi' => ['grup' => 'randevu', 'ad' => 'Müşteri geldi',
            'aciklama' => 'Randevu "geldi" işaretlenince ilgili personele haber verilir.', 'push_tip' => 'appointment_created',
            'alicilar' => ['personel' => ['eski' => [21, 'personel'], 'push' => 1]]],

        // ── Hatirlatmalar ──
        'hatirlatma_yaklasan' => ['grup' => 'hatirlatma', 'ad' => 'Yaklaşan randevu hatırlatma',
            'aciklama' => 'Randevudan birkaç saat önce (süreyi Randevu Ayarları\'ndan seçersiniz).', 'push_tip' => 'appointment_reminder_hour',
            'alicilar' => ['musteri' => ['eski' => [1, 'musteri'], 'push' => 1], 'personel' => ['eski' => [1, 'personel'], 'push' => 1], 'yonetici' => ['eski' => [1, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'hatirlatma_bir_gun_once' => ['grup' => 'hatirlatma', 'ad' => 'Bir gün önce randevu hatırlatma',
            'aciklama' => 'Yarınki randevular için bugün öğleden sonra.', 'push_tip' => 'appointment_reminder',
            'alicilar' => ['musteri' => ['eski' => [6, 'musteri'], 'push' => 1], 'personel' => ['eski' => null, 'mesaj' => 0, 'push' => 1], 'yonetici' => ['eski' => null, 'mesaj' => 0, 'push' => 1, 'kopya' => 'personel']]],
        'seans_hatirlatma' => ['grup' => 'hatirlatma', 'ad' => 'Kalan seans hatırlatma',
            'aciklama' => 'Paketinde kullanılmamış seansı olan müşteriye randevu almayı hatırlatır.', 'push_tip' => 'session_reminder',
            'alicilar' => ['musteri' => ['eski' => null, 'mesaj' => 0, 'push' => 1]]],
        'ders_hatirlatma' => ['grup' => 'hatirlatma', 'ad' => 'Grup dersi hatırlatma',
            'aciklama' => 'Grup dersine (pilates, kurs vb.) kayıtlı müşteriye ders öncesi.', 'push_tip' => 'class_reminder',
            'alicilar' => ['musteri' => ['eski' => [1, 'musteri'], 'push' => 'ayar']]],
        'alacak_hatirlatma' => ['grup' => 'hatirlatma', 'ad' => 'Ödeme (alacak) hatırlatma',
            'aciklama' => 'Taksit/borç günü gelen müşteriye; yöneticilere de alacak özeti.', 'push_tip' => 'system_announcement',
            'alicilar' => ['musteri' => ['eski' => [1, 'musteri'], 'push' => 0], 'yonetici' => ['eski' => [1, 'personel'], 'push' => 1, 'kopya' => 'personel']]],
        'senet_vade' => ['grup' => 'hatirlatma', 'ad' => 'Senet vadesi geçti',
            'aciklama' => 'Vadesi geçmiş senedi olan müşteriye her gün saat 10:00\'da.', 'push_tip' => 'system_announcement',
            'alicilar' => ['musteri' => ['eski' => null, 'mesaj' => 1, 'sadeceSms' => true, 'push' => 0]]],
        'tekrar_arama' => ['grup' => 'hatirlatma', 'ad' => 'Müşteriyi tekrar arama zamanı',
            'aciklama' => 'Çağrı merkezinde "sonra ara" denen müşterinin arama saati geldiğinde personele.', 'push_tip' => 'call_appointment_reminder',
            'alicilar' => ['personel' => ['eski' => null, 'mesaj' => 0, 'push' => 1]]],
        'ders_katilim_sor' => ['grup' => 'hatirlatma', 'ad' => 'Grup dersi katılım sorusu',
            'aciklama' => 'Grup dersine kayıtlı müşteriye "derse geliyor musunuz?" sorusu.', 'push_tip' => 'class_reminder',
            'alicilar' => ['musteri' => ['eski' => null, 'mesaj' => 0, 'push' => 1]]],

        // ── Musteri ──
        'musteri_eklendi' => ['grup' => 'musteri', 'ad' => 'Müşteri eklendiğinde',
            'aciklama' => 'Yeni müşteri kaydı açılınca hoş geldin mesajı.', 'push_tip' => 'system_announcement',
            'alicilar' => ['musteri' => ['eski' => [4, 'musteri'], 'push' => 0]]],
        'dogum_gunu' => ['grup' => 'musteri', 'ad' => 'Doğum günü kutlaması',
            'aciklama' => 'Doğum günü olan müşteriye kutlama mesajı.', 'push_tip' => 'birthday',
            'alicilar' => ['musteri' => ['eski' => [8, 'musteri'], 'push' => 'ayar']]],
        'kara_liste' => ['grup' => 'musteri', 'ad' => 'Kara listeye alındığında',
            'aciklama' => 'Müşteri kara listeye alındığında bilgilendirme.', 'push_tip' => 'system_announcement',
            'alicilar' => ['musteri' => ['eski' => [15, 'musteri'], 'push' => 'ayar']]],
        // Bugun kanal sirasi salonlar.anket_gonderim_kanali'ndan: 'sms' ise tohumda SMS secilir
        'degerlendirme_anketi' => ['grup' => 'musteri', 'ad' => 'Randevu sonrası değerlendirme',
            'aciklama' => 'Randevudan sonra memnuniyet anketi bağlantısı.', 'push_tip' => 'survey',
            'alicilar' => ['musteri' => ['eski' => [13, 'musteri'], 'push' => 0, 'kanalSutunu' => 'anket_gonderim_kanali']]],
        'kotu_degerlendirme' => ['grup' => 'musteri', 'ad' => 'Düşük puanlı değerlendirme uyarısı',
            'aciklama' => 'Müşteri ankette düşük puan verince yöneticilere haber verilir.', 'push_tip' => 'survey',
            'alicilar' => ['yonetici' => ['eski' => null, 'mesaj' => 0, 'push' => 1, 'kopya' => 'personel']]],
        'musteri_foto' => ['grup' => 'musteri', 'ad' => 'Fotoğraf paylaşıldığında',
            'aciklama' => 'Müşteri uygulamadan fotoğraf yüklediğinde personele, işletme yüklediğinde müşteriye.', 'push_tip' => 'musteri_resim_yuklendi',
            'alicilar' => ['musteri' => ['eski' => null, 'mesaj' => 0, 'push' => 1], 'personel' => ['eski' => null, 'mesaj' => 0, 'push' => 1]]],
        'damga_karti' => ['grup' => 'musteri', 'ad' => 'Damga kartı ödülü kazanıldı',
            'aciklama' => 'Damga kartı dolan müşteriye ödül bildirimi.', 'push_tip' => 'stamp_reward',
            'alicilar' => ['musteri' => ['eski' => null, 'mesaj' => 0, 'push' => 1]]],
        'cark_hatirlatma' => ['grup' => 'musteri', 'ad' => 'Çarkıfelek çevirme hakkı',
            'aciklama' => 'Çarkıfelek hakkı olan müşteriye hatırlatma ve duyuru.', 'push_tip' => 'wheel_chance',
            'alicilar' => ['musteri' => ['eski' => null, 'mesaj' => 0, 'push' => 1]]],

        // ── Asistanlar ──
        'wa_asistan_talep' => ['grup' => 'asistan', 'ad' => 'WhatsApp asistanı size soruyor',
            'aciklama' => 'Asistan cevaplayamadığı bir konuda işletmeden yardım istediğinde.', 'push_tip' => 'wa_asistan_talep',
            'alicilar' => ['yonetici' => ['eski' => null, 'mesaj' => 0, 'push' => 1, 'kopya' => 'personel']]],
        'telefon_asistani' => ['grup' => 'asistan', 'ad' => 'Telefon asistanı işlem yaptığında',
            'aciklama' => 'Yapay zeka telefon asistanı randevu aldığında veya iptal ettiğinde personele.', 'push_tip' => 'appointment_created',
            'alicilar' => ['yonetici' => ['eski' => null, 'mesaj' => 0, 'push' => 1, 'kopya' => 'personel']]],

        // ── Seans & paket ──
        // Bugun: once push (+WA), ikisi de ulasmazsa SMS -> tohumda SMS kapali, yedek SMS bunu karsilar
        // ('yedek' => 'tum': secili kanallarin HICBIRI ulasmazsa SMS; digerlerinde yalniz WA ulasmazsa).
        'seans_kullanim' => ['grup' => 'seans', 'ad' => 'Seans kullanıldığında',
            'aciklama' => 'Paketten seans düşünce kalan seans bilgisi.', 'push_tip' => 'session_used', 'yedek' => 'tum',
            'alicilar' => ['musteri' => ['eski' => [20, 'musteri'], 'push' => 'ayar', 'smsYedekle' => true]]],

        // ── Para & kasa ──
        'para_islemleri' => ['grup' => 'para', 'ad' => 'Kasa para giriş/çıkışı',
            'aciklama' => 'Kasadan para alındığında veya kasaya para eklendiğinde yöneticilere.', 'push_tip' => 'system_announcement',
            'alicilar' => ['yonetici' => ['eski' => [19, 'personel'], 'push' => 0, 'kopya' => 'personel']]],

        // ── Onay ve dogrulama kodlari ──
        'dogrulama_kodu' => ['grup' => 'guvenlik', 'ad' => 'Seans/ödeme doğrulama kodu',
            'aciklama' => 'Paket seansı kullanılırken veya ödemede müşteriye onay kodu gider. Kapalıysa kod istenmez.', 'push_tip' => 'system_announcement',
            'alicilar' => ['musteri' => ['eski' => [16, 'musteri'], 'push' => 0]]],
        'kvkk_onay' => ['grup' => 'guvenlik', 'ad' => 'KVKK onay kodu',
            'aciklama' => 'Yeni müşteriden KVKK onayı kodla alınır. Kapalıysa onay istenmez.', 'push_tip' => 'system_announcement',
            'alicilar' => ['musteri' => ['eski' => [22, 'musteri'], 'push' => 0]]],
        'silme_onay_kodu' => ['grup' => 'guvenlik', 'ad' => 'Satış/tahsilat silme onay kodu',
            'aciklama' => 'Satış veya tahsilat silinirken işletme sahibine onay kodu gider. Kapalıysa kod istenmez.', 'push_tip' => 'system_announcement',
            'alicilar' => ['yonetici' => ['eski' => [23, 'personel'], 'push' => 0, 'kopya' => 'personel']]],

        // ── Kampanya ──
        'kampanya_link' => ['grup' => 'kampanya', 'ad' => 'Etkinlik & kampanya katılım linki',
            'aciklama' => 'Etkinlik/kampanya duyurusunda katılım bağlantısı.', 'push_tip' => 'campaign',
            'alicilar' => ['musteri' => ['eski' => [10, 'musteri'], 'push' => 0]]],
    ];

    /** salon_sms_ayarlari satiri yoksa kayit varsayilanlari (ApiController kayit blogu ile ayni). */
    const ESKI_VARSAYILAN = [
        1 => [1, 1], 2 => [1, 1], 3 => [1, 1], 4 => [1, 0], 5 => [1, 0], 6 => [1, 1], 7 => [1, 1],
        8 => [1, 0], 9 => [1, 0], 10 => [1, 0], 11 => [1, 1], 12 => [1, 1], 13 => [1, 0], 14 => [1, 1],
        15 => [1, 0], 16 => [1, 0], 17 => [0, 0], 18 => [1, 0], 19 => [1, 0], 20 => [0, 0], 21 => [0, 0],
        22 => [0, 0], 23 => [0, 0],
    ];

    /** Uzun yasayan surecler (queue/cron) icin kisa omurlu bellek onbellegi. */
    private static $onbellek = [];
    const ONBELLEK_SN = 60;

    // ─────────────────────────────────────────────────────────── sema

    public static function semaGaranti()
    {
        if (Cache::get(self::SEMA)) return;
        try {
            if (!Schema::hasTable(self::TABLO)) {
                DB::statement('CREATE TABLE IF NOT EXISTS ' . self::TABLO . ' (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    salon_id INT UNSIGNED NOT NULL,
                    kod VARCHAR(50) NOT NULL,
                    alici VARCHAR(10) NOT NULL,
                    sms TINYINT(1) NOT NULL DEFAULT 0,
                    whatsapp TINYINT(1) NOT NULL DEFAULT 0,
                    push TINYINT(1) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP NULL DEFAULT NULL,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY sbt_salon_kod_alici (salon_id, kod, alici)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            }
            if (!Schema::hasColumn('salonlar', 'bildirim_wa_yedek_sms')) {
                DB::statement('ALTER TABLE salonlar ADD COLUMN bildirim_wa_yedek_sms TINYINT(1) NOT NULL DEFAULT 1');
            }
            if (Schema::hasTable('whatsapp_gonderim_loglari') && !Schema::hasColumn('whatsapp_gonderim_loglari', 'sms_yedeksiz')) {
                // 1 = bu mesaj icin SMS ayrica gonderildi veya yedek istenmedi: WA basarisiz olursa SMS'e DUSME
                DB::statement('ALTER TABLE whatsapp_gonderim_loglari ADD COLUMN sms_yedeksiz TINYINT(1) NOT NULL DEFAULT 0');
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[BildirimTercih] sema hatasi: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────── okuma

    /** Salonun tum tercihleri: [kod][alici] => ['sms'=>0/1,'whatsapp'=>0/1,'push'=>0/1]. Eksikler tohumlanir. */
    public static function hepsi($salonId)
    {
        $salonId = (int) $salonId;
        if ($salonId <= 0) return [];
        $o = self::$onbellek[$salonId] ?? null;
        if ($o && (time() - $o['t']) < self::ONBELLEK_SN) return $o['v'];

        self::semaGaranti();
        $v = [];
        try {
            foreach (DB::table(self::TABLO)->where('salon_id', $salonId)->get() as $r) {
                $v[$r->kod][$r->alici] = ['sms' => (int) $r->sms, 'whatsapp' => (int) $r->whatsapp, 'push' => (int) $r->push];
            }
            $eksik = [];
            foreach (self::KATALOG as $kod => $k) {
                foreach ($k['alicilar'] as $alici => $spec) {
                    if (!isset($v[$kod][$alici])) $eksik[] = [$kod, $alici, $spec];
                }
            }
            // Yalniz gercek salon tohumlanir (kimliksiz uclardan rastgele id ile tablo sisirilemesin)
            if ($eksik && DB::table('salonlar')->where('id', $salonId)->exists()) {
                $v = self::tohumla($salonId, $eksik, $v);
            }
        } catch (\Throwable $e) {
            Log::warning('[BildirimTercih] okuma hatasi', ['salon_id' => $salonId, 'err' => $e->getMessage()]);
            // Tablo okunamazsa bugunku davranisa en yakin: eski bayraklardan hesapla, yazma
            $v = self::tohumDegerleri($salonId, self::tumAlicilar());
        }
        self::$onbellek[$salonId] = ['t' => time(), 'v' => $v];
        return $v;
    }

    /** ['sms'=>bool,'whatsapp'=>bool,'push'=>bool] — katalogda yoksa hepsi false. */
    public static function kanallar($salonId, $kod, $alici = 'musteri')
    {
        $h = self::hepsi($salonId);
        $r = $h[$kod][$alici] ?? null;
        return [
            'sms'      => $r ? $r['sms'] === 1 : false,
            'whatsapp' => $r ? $r['whatsapp'] === 1 : false,
            'push'     => $r ? $r['push'] === 1 : false,
        ];
    }

    /** Herhangi bir kanal acik mi (mesaj hazirlanmali mi / kod istenmeli mi). */
    public static function acikMi($salonId, $kod, $alici = 'musteri')
    {
        $k = self::kanallar($salonId, $kod, $alici);
        return $k['sms'] || $k['whatsapp'] || $k['push'];
    }

    /** SMS veya WhatsApp acik mi (telefon mesaji hazirlanmali mi). */
    public static function mesajAcikMi($salonId, $kod, $alici = 'musteri')
    {
        $k = self::kanallar($salonId, $kod, $alici);
        return $k['sms'] || $k['whatsapp'];
    }

    public static function izin($salonId, $kod, $alici, $kanal)
    {
        return !empty(self::kanallar($salonId, $kod, $alici)[$kanal]);
    }

    /** WhatsApp gidemezse SMS'e dusulsun mu (salon geneli, varsayilan acik). */
    public static function waYedekSms($salonId)
    {
        $salonId = (int) $salonId;
        $o = self::$yedekOnbellek[$salonId] ?? null;
        if ($o && (time() - $o['t']) < self::ONBELLEK_SN) return $o['v'];
        self::semaGaranti();
        try {
            $d = DB::table('salonlar')->where('id', $salonId)->value('bildirim_wa_yedek_sms');
            $v = $d === null ? true : (int) $d === 1;
        } catch (\Throwable $e) {
            $v = true;
        }
        self::$yedekOnbellek[$salonId] = ['t' => time(), 'v' => $v];
        return $v;
    }
    private static $yedekOnbellek = [];

    // ─────────────────────────────────────────────────────────── yazma

    const KANAL_AD = ['sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'push' => 'Uygulama bildirimi'];
    const ALICI_AD = ['musteri' => 'Müşteriye', 'personel' => 'Personele', 'yonetici' => 'Yöneticiye'];

    /**
     * Tek bir anahtari kaydeder. Deger degistiyse Log Hareketleri'ne (salon_aktivite_log) yazar.
     * $kaynak: 'panel' | 'mobil' | 'eski_sms_ekrani' | 'eski_mobil' ; $req: mobil istekte kullaniciyi cozmek icin.
     * Donus: degisti mi (true/false), gecersiz kod/kanal -> null.
     */
    public static function kaydet($salonId, $kod, $alici, $kanal, $deger, $kaynak = 'panel', $req = null, $logla = true)
    {
        $salonId = (int) $salonId;
        if (!isset(self::KATALOG[$kod]['alicilar'][$alici]) || !in_array($kanal, self::KANALLAR, true)) return null;
        $deger = $deger ? 1 : 0;
        unset(self::$onbellek[$salonId]);
        $h = self::hepsi($salonId); // satir yoksa once tohumla
        $eski = (int) ($h[$kod][$alici][$kanal] ?? 0);
        if ($eski === $deger) return false;
        DB::table(self::TABLO)->where('salon_id', $salonId)->where('kod', $kod)->where('alici', $alici)
            ->update([$kanal => $deger, 'updated_at' => date('Y-m-d H:i:s')]);
        unset(self::$onbellek[$salonId]);
        self::eskiyeYansit($salonId, $kod, $alici);
        if ($logla) {
            self::logYaz($salonId, $req, self::etiket($kod, $alici, $kanal), ($deger ? 'Açıldı' : 'Kapatıldı') . self::kaynakEk($kaynak),
                ['kod' => $kod, 'alici' => $alici, 'kanal' => $kanal, 'eski' => $eski, 'yeni' => $deger, 'kaynak' => $kaynak]);
        }
        return true;
    }

    /** Bir kanal sutununu (opsiyonel alici) toplu ac/kapat; degisenler TEK log kaydinda listelenir. */
    public static function topluKaydet($salonId, $kanal, $alici, $deger, $kaynak = 'panel', $req = null)
    {
        $degisen = [];
        foreach (self::KATALOG as $kod => $k) {
            foreach ($k['alicilar'] as $a => $spec) {
                if ($alici && $a !== $alici) continue;
                if (self::kaydet($salonId, $kod, $a, $kanal, $deger, $kaynak, $req, false)) {
                    $degisen[] = $k['ad'] . ' · ' . self::ALICI_AD[$a];
                }
            }
        }
        if ($degisen) {
            $etiket = 'Toplu: ' . (self::KANAL_AD[$kanal] ?? $kanal) . ($alici ? ' · ' . self::ALICI_AD[$alici] : '');
            self::logYaz($salonId, $req, $etiket,
                ($deger ? 'Hepsi açıldı' : 'Hepsi kapatıldı') . self::kaynakEk($kaynak) . ' — ' . count($degisen) . ' bildirim: ' . implode(', ', $degisen),
                ['kanal' => $kanal, 'alici' => $alici, 'yeni' => $deger ? 1 : 0, 'kaynak' => $kaynak, 'degisen' => $degisen]);
        }
        return count($degisen);
    }

    public static function waYedekKaydet($salonId, $deger, $kaynak = 'panel', $req = null)
    {
        $eski = self::waYedekSms($salonId);
        $deger = (bool) $deger;
        if ($eski === $deger) return false;
        DB::table('salonlar')->where('id', (int) $salonId)->update(['bildirim_wa_yedek_sms' => $deger ? 1 : 0]);
        unset(self::$yedekOnbellek[(int) $salonId]);
        self::logYaz($salonId, $req, 'WhatsApp gidemezse SMS gönder', ($deger ? 'Açıldı' : 'Kapatıldı') . self::kaynakEk($kaynak),
            ['ayar' => 'wa_yedek_sms', 'eski' => $eski ? 1 : 0, 'yeni' => $deger ? 1 : 0, 'kaynak' => $kaynak]);
        return true;
    }

    /**
     * Eski tek bayrakli ekranlardan (eski SMS Ayarlari / eski mobil surum) gelen ac-kapa.
     * Kapat = o alicinin SMS+WhatsApp'i kapanir (push'a dokunulmaz). Ac = hicbir mesaj kanali
     * acik degilse tohum kuraliyla acilir (WA bagliysa WhatsApp, degilse SMS). Her degisiklik loglanir.
     */
    public static function eskiBayrakUygula($salonId, $ayarId, $alan, $acik, $kaynak = 'eski_sms_ekrani', $req = null)
    {
        $salonId = (int) $salonId;
        $waBagli = null;
        foreach (self::KATALOG as $kod => $k) {
            foreach ($k['alicilar'] as $alici => $spec) {
                if (empty($spec['eski']) || (int) $spec['eski'][0] !== (int) $ayarId || $spec['eski'][1] !== $alan) continue;
                $m = self::kanallar($salonId, $kod, $alici);
                // Push'u da eski bayraga bagli olanlar (dogum gunu, kara liste, seans...) push'u da birlikte acar/kapatir
                $pushBagli = ($spec['push'] ?? null) === 'ayar';
                if (!$acik) {
                    self::kaydet($salonId, $kod, $alici, 'sms', 0, $kaynak, $req);
                    self::kaydet($salonId, $kod, $alici, 'whatsapp', 0, $kaynak, $req);
                    if ($pushBagli) self::kaydet($salonId, $kod, $alici, 'push', 0, $kaynak, $req);
                } elseif (!empty($spec['smsYedekle'])) {
                    if (!$m['push'] && !$m['whatsapp'] && !$m['sms']) {
                        if ($waBagli === null) $waBagli = self::waKurulu($salonId);
                        self::kaydet($salonId, $kod, $alici, 'push', 1, $kaynak, $req);
                        if ($waBagli) self::kaydet($salonId, $kod, $alici, 'whatsapp', 1, $kaynak, $req);
                    }
                } else {
                    if (!$m['sms'] && !$m['whatsapp']) {
                        if ($waBagli === null) $waBagli = self::waKurulu($salonId);
                        $kanal = (!empty($spec['sadeceSms']) || !$waBagli) ? 'sms' : 'whatsapp';
                        self::kaydet($salonId, $kod, $alici, $kanal, 1, $kaynak, $req);
                    }
                    if ($pushBagli && !$m['push']) self::kaydet($salonId, $kod, $alici, 'push', 1, $kaynak, $req);
                }
            }
        }
    }

    /**
     * Salon WhatsApp'i ILK KEZ bagladi (WhatsAppWebhookController::onConnected, baglanti_tarihi bos iken).
     * Bugun WA baglaninca mesajlar kendiliginden WhatsApp'a gecer (once WA, ulasmazsa SMS). Ayni davranis:
     * yalniz SMS secili olan (WA secili olmayan) mesaj kanallari WhatsApp'a tasinir, yedek SMS korur.
     * Yalniz SMS ile gidenler (senet) ve push'a dokunulmaz. Her degisiklik loglanir.
     */
    public static function waIlkBaglanti($salonId)
    {
        $salonId = (int) $salonId;
        if ($salonId <= 0) return 0;
        self::hepsi($salonId); // tohum (WA kurulu ise zaten whatsapp secili gelir)
        // Salon basina TEK SEFER: isaret satiri varsa (daha once tasindi / isletme sonra SMS'e aldiysa) dokunma
        // INSERT IGNORE etkilenen satir 0 ise isaret zaten vardi
        $eklendi = DB::affectingStatement('INSERT IGNORE INTO ' . self::TABLO . " (salon_id, kod, alici, sms, whatsapp, push, created_at, updated_at) VALUES (?, '_wa_ilk_tasima', 'genel', 0, 1, 0, ?, ?)",
            [$salonId, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
        if ($eklendi < 1) return 0;
        $degisen = [];
        foreach (self::KATALOG as $kod => $k) {
            foreach ($k['alicilar'] as $alici => $spec) {
                if (!empty($spec['sadeceSms']) || !empty($spec['smsYedekle'])) continue;
                $m = self::kanallar($salonId, $kod, $alici);
                if (!$m['sms'] || $m['whatsapp']) continue;
                self::kaydet($salonId, $kod, $alici, 'whatsapp', 1, 'wa_ilk_baglanti', null, false);
                self::kaydet($salonId, $kod, $alici, 'sms', 0, 'wa_ilk_baglanti', null, false);
                $degisen[] = $k['ad'] . ' · ' . self::ALICI_AD[$alici];
            }
        }
        if ($degisen) {
            self::logYaz($salonId, null, 'WhatsApp ilk kez bağlandı',
                count($degisen) . ' bildirim SMS yerine WhatsApp\'tan gidecek (WhatsApp ulaşmazsa SMS yedeği): ' . implode(', ', $degisen),
                ['kaynak' => 'wa_ilk_baglanti', 'degisen' => $degisen]);
        }
        return count($degisen);
    }

    /** Eski salon_sms_ayarlari durumunun anlik goruntusu: [ayar_id] => ['musteri'=>0/1,'personel'=>0/1]. */
    public static function eskiGoruntu($salonId)
    {
        $g = [];
        try {
            foreach (DB::table('salon_sms_ayarlari')->where('salon_id', (int) $salonId)->get(['ayar_id', 'musteri', 'personel']) as $r) {
                $g[(int) $r->ayar_id] = ['musteri' => (int) $r->musteri, 'personel' => (int) $r->personel];
            }
        } catch (\Throwable $e) {}
        return $g;
    }

    /**
     * Eski ekran kaydindan SONRA cagrilir: once/sonra goruntusunu karsilastirip yalnizca DEGISEN
     * bayraklari yeni sisteme uygular (degismeyenlere dokunmaz -> yeni ekranda yapilan kanal secimi korunur).
     */
    public static function eskiDegisiklikleriUygula($salonId, array $once, array $sonra, $kaynak = 'eski_sms_ekrani', $req = null)
    {
        foreach ($sonra as $ayarId => $alanlar) {
            foreach (['musteri', 'personel'] as $alan) {
                $o = (int) ($once[$ayarId][$alan] ?? -1);
                $s = (int) $alanlar[$alan];
                if ($o === $s) continue;
                self::eskiBayrakUygula($salonId, $ayarId, $alan, $s === 1, $kaynak, $req);
            }
        }
    }

    private static function etiket($kod, $alici, $kanal)
    {
        return (self::KATALOG[$kod]['ad'] ?? $kod) . ' · ' . (self::ALICI_AD[$alici] ?? $alici) . ' · ' . (self::KANAL_AD[$kanal] ?? $kanal);
    }

    private static function kaynakEk($kaynak)
    {
        $m = [
            'panel' => ' (Bildirim Ayarları, web panel)',
            'mobil' => ' (Bildirim Ayarları, mobil uygulama)',
            'eski_sms_ekrani' => ' (SMS Ayarları ekranı, web panel)',
            'eski_mobil' => ' (SMS Ayarları ekranı, mobil uygulama)',
        ];
        return $m[$kaynak] ?? '';
    }

    /** Log Hareketleri'ne yazar: web oturumu varsa Audit::log, yoksa (mobil) Audit::logApi. Asla hata firlatmaz. */
    private static function logYaz($salonId, $req, $etiket, $aciklama, array $meta)
    {
        try {
            $web = false;
            try { $web = \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->check(); } catch (\Throwable $e) {}
            if (!$web && $req) {
                \App\SalonYonetim\Audit::logApi($salonId, $req, 'bildirim_ayar_degisti', 'bildirim_ayar', null, $etiket, $aciklama, $meta);
            } else {
                \App\SalonYonetim\Audit::log($salonId, 'bildirim_ayar_degisti', 'bildirim_ayar', null, $etiket, $aciklama, $meta);
            }
        } catch (\Throwable $e) {}
    }

    /** Eski salon_sms_ayarlari bayragini "herhangi bir kanal acik" olarak gunceller. */
    private static function eskiyeYansit($salonId, $kod, $alici)
    {
        $spec = self::KATALOG[$kod]['alicilar'][$alici] ?? null;
        if (!$spec || empty($spec['eski'])) return;
        list($ayarId, $alan) = $spec['eski'];
        // Ayni eski ayara bagli tum kod/alicilardan biri aciksa bayrak acik. Eski ekran SMS/WhatsApp
        // ekrani oldugu icin mesaj kanali sayilir; kod isteyen guvenlik ayarlarinda (16/22/23) ise
        // "kod istenir mi" anlami korunur -> herhangi bir kanal (push dahil).
        $acik = false;
        foreach (self::KATALOG as $k2 => $kk) {
            foreach ($kk['alicilar'] as $a2 => $s2) {
                if (empty($s2['eski']) || (int) $s2['eski'][0] !== (int) $ayarId || $s2['eski'][1] !== $alan) continue;
                $tumKanal = $kk['grup'] === 'guvenlik' || !empty($s2['smsYedekle']) || ($s2['push'] ?? null) === 'ayar';
                $var = $tumKanal ? self::acikMi($salonId, $k2, $a2) : self::mesajAcikMi($salonId, $k2, $a2);
                if ($var) $acik = true;
            }
        }
        try {
            DB::table('salon_sms_ayarlari')->where('salon_id', $salonId)->where('ayar_id', $ayarId)
                ->update([$alan => $acik ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
            Log::warning('[BildirimTercih] eski ayar yansitilamadi', ['salon_id' => $salonId, 'ayar_id' => $ayarId, 'err' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────── tohum

    private static function tumAlicilar()
    {
        $l = [];
        foreach (self::KATALOG as $kod => $k) foreach ($k['alicilar'] as $a => $s) $l[] = [$kod, $a, $s];
        return $l;
    }

    private static function tohumDegerleri($salonId, array $liste, array $mevcut = [])
    {
        $eski = [];
        try {
            foreach (DB::table('salon_sms_ayarlari')->where('salon_id', $salonId)->get(['ayar_id', 'musteri', 'personel']) as $r) {
                $eski[(int) $r->ayar_id] = ['musteri' => (int) $r->musteri, 'personel' => (int) $r->personel];
            }
        } catch (\Throwable $e) {}
        $waBagli = self::waKurulu($salonId);
        $salonSatir = null;
        $v = [];
        foreach ($liste as $x) {
            list($kod, $alici, $spec) = $x;
            // Yonetici ilk kez gelir: ayni bildirimin KAYITLI personel ayari aynen kopyalanir (bugun tek ayar ikisini birlikte yonetiyordu)
            if (!empty($spec['kopya']) && isset($mevcut[$kod][$spec['kopya']])) { $v[$kod][$alici] = $mevcut[$kod][$spec['kopya']]; continue; }
            if (!empty($spec['eski'])) {
                list($ayarId, $alan) = $spec['eski'];
                // Eski satir yoksa bugun o bildirim hic gitmiyor (value()==1 / if(!$ayar) return) -> kapali
                $bayrak = isset($eski[$ayarId]) ? $eski[$ayarId][$alan] : 0;
            } else {
                $bayrak = (int) ($spec['mesaj'] ?? 0);
            }
            $smsMi = !empty($spec['sadeceSms']) || !$waBagli;
            if (!empty($spec['kanalSutunu'])) {
                // Salonun bu bildirim icin ayri kanal secimi varsa ('sms'/'whatsapp') o korunur
                if ($salonSatir === null) {
                    try { $salonSatir = DB::table('salonlar')->where('id', $salonId)->first() ?: false; } catch (\Throwable $e) { $salonSatir = false; }
                }
                $sk = $salonSatir ? ($salonSatir->{$spec['kanalSutunu']} ?? null) : null;
                if ($sk === 'sms') $smsMi = true;
            }
            $push = $spec['push'] === 'ayar' ? $bayrak : (int) $spec['push'];
            if (!empty($spec['smsYedekle'])) {
                // SMS dogrudan secilmez; push/WA ulasmazsa yedek SMS gider (bugunku davranis)
                $v[$kod][$alici] = ['sms' => 0, 'whatsapp' => ($bayrak && $waBagli) ? 1 : 0, 'push' => $push ? 1 : 0];
                continue;
            }
            $v[$kod][$alici] = [
                'sms'      => ($bayrak && $smsMi) ? 1 : 0,
                'whatsapp' => ($bayrak && !$smsMi) ? 1 : 0,
                'push'     => $push ? 1 : 0,
            ];
        }
        return $v;
    }

    private static function tohumla($salonId, array $eksik, array $v)
    {
        $yeni = self::tohumDegerleri($salonId, $eksik, $v);
        $simdi = date('Y-m-d H:i:s');
        foreach ($yeni as $kod => $alicilar) {
            foreach ($alicilar as $alici => $d) {
                // INSERT IGNORE: ayni anda iki istek tohumlarsa unique anahtar ikincisini yutar
                DB::statement('INSERT IGNORE INTO ' . self::TABLO . ' (salon_id, kod, alici, sms, whatsapp, push, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)',
                    [$salonId, $kod, $alici, $d['sms'], $d['whatsapp'], $d['push'], $simdi, $simdi]);
                $v[$kod][$alici] = $d;
            }
        }
        // WA zaten kurulu salonda tohum whatsapp'i secti: "ilk baglanti tasimasi" yapilmis sayilir; boylece
        // isletmenin sonradan SMS'e aldigi bildirim, oturum yeniden baglaninca WA'ya geri tasinmaz.
        if (self::waKurulu($salonId)) {
            DB::statement('INSERT IGNORE INTO ' . self::TABLO . " (salon_id, kod, alici, sms, whatsapp, push, created_at, updated_at) VALUES (?, '_wa_ilk_tasima', 'genel', 0, 1, 0, ?, ?)",
                [$salonId, $simdi, $simdi]);
        }
        Log::info('[BildirimTercih] tohumlandi', ['salon_id' => $salonId, 'adet' => count($eksik)]);
        return $v;
    }

    // ─────────────────────────────────────────────────────────── kanal durumlari

    /**
     * Salon WhatsApp'i KURMUS mu (whatsapp_aktif=1, oturum su an kopuk olsa bile). Tohum bunu kullanir:
     * bugun WA kurulu salonda mesaj once WA'ya gider, oturum koparsa SMS'e duser -> whatsapp=1 + yedek SMS
     * ayni davranisi verir. Anlik bagliliga bakilsaydi gecici kopuklukta salon kalici SMS'e gecerdi.
     */
    public static function waKurulu($salon)
    {
        try {
            $s = is_object($salon) ? $salon : \App\Salonlar::where('id', (int) $salon)->first();
            if (!$s) return false;
            $wa = WhatsAppService::resolveWaSalon($s);
            if (!$wa) return false;
            if (!empty($wa->whatsapp_aktif)) return true;
            // Resmi Cloud API salonu: hatirlatmalar whatsapp_aktif'e bakmadan token+numara ile WA dener
            return ($wa->whatsapp_saglayici ?? null) === 'cloud_api' && !empty($wa->cloud_api_token) && !empty($wa->cloud_api_phone_number_id);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function waBagli($salon)
    {
        try {
            $s = is_object($salon) ? $salon : \App\Salonlar::where('id', (int) $salon)->first();
            if (!$s) return false;
            $wa = WhatsAppService::resolveWaSalon($s);
            return $wa && !empty($wa->whatsapp_aktif) && ($wa->whatsapp_durum ?? null) === 'connected';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Salonun SMS hesabi tanimli mi (saglayici bilgisi var mi). */
    public static function smsHesabiVar($salon)
    {
        $s = is_object($salon) ? $salon : \App\Salonlar::where('id', (int) $salon)->first();
        if (!$s) return false;
        if ((int) ($s->yeni_sms ?? 0) === 1) return !empty($s->sms_user_name) && !empty($s->sms_secret);
        return !empty($s->sms_apikey);
    }

    /**
     * Kalan SMS (saglayicidan, 10 dk onbellekli). Sorgu basarisizsa null (bilinmiyor).
     */
    public static function smsBakiye($salon)
    {
        $s = is_object($salon) ? $salon : \App\Salonlar::where('id', (int) $salon)->first();
        if (!$s || !self::smsHesabiVar($s)) return 0;
        $anahtar = 'sms_bakiye_' . $s->id;
        $o = Cache::get($anahtar);
        if ($o !== null) return $o === 'bilinmiyor' ? null : (float) $o;
        $kalan = null;
        try {
            if ((int) $s->yeni_sms === 1) {
                require_once app_path('VoiceTelekom/Sms/SmsApi.php');
                $api = new \SmsApi('smsvt.voicetelekom.com', $s->sms_user_name, $s->sms_secret);
                $r = $api->getCredit();
                if ($r && $r->err === null) $kalan = (float) $r->credit;
            } else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.efetech.net.tr/v2/get/balance');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 4);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Key ' . $s->sms_apikey, 'Content-Type: application/json', 'Accept: application/json']);
                $d = json_decode((string) curl_exec($ch), true);
                curl_close($ch);
                if (is_array($d) && isset($d['response']['balance'])) $kalan = (float) $d['response']['balance'];
            }
        } catch (\Throwable $e) {
            Log::warning('[BildirimTercih] SMS bakiye sorgulanamadi', ['salon_id' => $s->id, 'err' => $e->getMessage()]);
        }
        Cache::put($anahtar, $kalan === null ? 'bilinmiyor' : $kalan, 10);
        return $kalan;
    }

    /**
     * SMS gonderilebilir mi: hesap var ve bakiye 0 degil. Bakiye sorgusu basarisizsa (null)
     * ENGELLENMEZ — saglayici hatasi yuzunden kritik mesaj kaybolmasin.
     */
    public static function smsKullanilabilir($salon)
    {
        if (!self::smsHesabiVar($salon)) return false;
        $b = self::smsBakiye($salon);
        return $b === null || $b > 0;
    }

    // ─────────────────────────────────────────────────────────── dagitim

    /**
     * Mesaj satirlarini kanallara dagitir: WhatsApp'a gidenleri gonderir, genel push'u atar,
     * SMS ile gitmesi gerekenleri DONDURUR (cagiran kendi SMS saglayicisiyla gonderir).
     *
     * Satir: ['to','message', opsiyonel 'wa_message','urgent','gonderim_tipi','randevu_id',
     *         'user_id' (musteri push), 'personel_id' / 'yetkili_id' (personel push),
     *         'bk' => katalog kodu, 'alici' => musteri|personel,
     *         'push_ayri' => true (cagiran push'u kendisi atiyor), 'push_baslik', 'push_mesaj']
     *
     * 'bk' olmayan satir (katalog disi, elle gonderim): eski davranis — WA bagliysa once WA,
     * gidemezse salonun yedek ayari aciksa SMS; WA bagli degilse SMS.
     */
    public static function dagit($salon, array $mesajlar, $gonderimTipi = null, $tur = null)
    {
        $s = is_object($salon) ? $salon : \App\Salonlar::where('id', (int) $salon)->first();
        if (!$s || !$mesajlar) return [];

        $mesajlar = KaraListeServisi::topluSuz($s->id, $mesajlar, $gonderimTipi);
        if (!$mesajlar) return [];

        $waBagli = self::waBagli($s);
        $yedek = self::waYedekSms($s->id);
        $wa = null;
        $smsListesi = [];
        $smsYok = false;

        foreach ($mesajlar as $m) {
            $to = $m['to'] ?? null;
            $metin = $m['message'] ?? null;
            $bk = $m['bk'] ?? null;
            $alici = $m['alici'] ?? 'musteri';

            if ($bk) {
                $k = self::kanallar($s->id, $bk, $alici);
                $waIste = $k['whatsapp'];
                $smsIste = $k['sms'];
                if ($k['push'] && empty($m['push_ayri'])) self::genelPush($s, $bk, $m);
            } else {
                $waIste = $waBagli;
                $smsIste = !$waBagli;
            }
            if (!$to || !$metin || (!$waIste && !$smsIste)) continue;

            $waOk = false;
            if ($waIste && $waBagli) {
                try {
                    if (!$wa) $wa = app(WhatsAppService::class);
                    $sonuc = $wa->sendReminder($s, $to, $m['wa_message'] ?? $metin, $m['randevu_id'] ?? null, $m['user_id'] ?? null,
                        null, !empty($m['urgent']), $m['gonderim_tipi'] ?? $gonderimTipi);
                    $waOk = !empty($sonuc['ok']);
                    // SMS de ayrica gidiyorsa veya yedek kapaliysa: WA sonradan duserse SMS'e dusme
                    if ($waOk && !empty($sonuc['logId']) && ($smsIste || !$yedek)) {
                        try {
                            DB::table('whatsapp_gonderim_loglari')->where('id', $sonuc['logId'])->update(['sms_yedeksiz' => 1]);
                        } catch (\Throwable $e) {}
                    }
                    if (!$waOk) Log::warning('[BildirimTercih] WA gonderilemedi', ['salon_id' => $s->id, 'to' => $to, 'bk' => $bk, 'err' => $sonuc['error'] ?? null]);
                } catch (\Throwable $e) {
                    Log::warning('[BildirimTercih] WA istisna', ['salon_id' => $s->id, 'err' => $e->getMessage()]);
                }
            }
            $yedekSms = false;
            if (!$smsIste && $waIste && !$waOk && $yedek) { $smsIste = true; $yedekSms = true; } // WhatsApp gidemedi -> yedek SMS

            if ($smsIste) {
                // Yedek SMS'te SMS hesabi + bakiye bakilir: paket yoksa/bittiyse gitmez.
                // Dogrudan secilen SMS'te kontrolu saglayici yapar (bugunku davranis aynen korunur).
                if ($yedekSms && !self::smsKullanilabilir($s)) {
                    Log::info('[BildirimTercih] SMS hesabi/paketi yok, SMS gonderilmedi', ['salon_id' => $s->id, 'to' => $to, 'bk' => $bk, 'yedek' => $yedekSms]);
                    continue;
                }
                unset($m['bk'], $m['alici'], $m['push_ayri'], $m['push_baslik'], $m['push_mesaj'], $m['personel_id'], $m['yetkili_id']);
                $smsListesi[] = $m;
            }
        }
        return $smsListesi;
    }

    /**
     * Kuyruga girmis bir WhatsApp mesaji sonradan dustu (webhook fail / takili kaldi): SMS'e
     * dusulsun mu? Hayir -> ayni mesaj SMS ile zaten gonderildi (sms_yedeksiz), salon yedegi
     * kapatti ya da SMS paketi/bakiyesi yok. Donus: [bool, sebep].
     */
    public static function waLogSmsYedegi($log)
    {
        if (!empty($log->sms_yedeksiz)) return [false, 'sms ayrica gonderildi veya yedek istenmedi'];
        if (!self::waYedekSms($log->salon_id)) return [false, 'isletme WhatsApp->SMS yedegini kapatti'];
        if (!self::smsKullanilabilir($log->salon_id)) return [false, 'SMS paketi/bakiyesi yok'];
        return [true, null];
    }

    /** Katalog bildirimi icin uygulama bildirimi (kendi push'u olmayan cagri yerleri). */
    public static function genelPush($salon, $kod, array $m)
    {
        try {
            $s = is_object($salon) ? $salon : \App\Salonlar::where('id', (int) $salon)->first();
            $salonId = $s ? (int) $s->id : null;
            $ns = null;
            if (!empty($m['user_id']) && ($m['alici'] ?? 'musteri') === 'musteri') {
                $ns = NotificationService::toCustomer((int) $m['user_id'], $salonId);
            } elseif (!empty($m['personel_id'])) {
                $ns = NotificationService::toStaff((int) $m['personel_id'], $salonId);
            } elseif (!empty($m['yetkili_id'])) {
                $ns = NotificationService::toOwner((int) $m['yetkili_id'], $salonId);
            }
            if (!$ns) return;
            $baslik = $m['push_baslik'] ?? (self::KATALOG[$kod]['ad'] ?? ($s->salon_adi ?? 'Bildirim'));
            $govde = $m['push_mesaj'] ?? ($m['wa_message'] ?? ($m['message'] ?? ''));
            $ns->type(self::KATALOG[$kod]['push_tip'] ?? 'system_announcement')
                ->title(mb_substr((string) $baslik, 0, 120))
                ->body(mb_substr((string) $govde, 0, 900))
                ->randevu(!empty($m['randevu_id']) ? (int) $m['randevu_id'] : null)
                ->send();
        } catch (\Throwable $e) {
            Log::warning('[BildirimTercih] genel push hatasi', ['kod' => $kod, 'err' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────── ekran verisi

    /** Panel/mobil ekran icin: gruplu katalog + degerler + kanal durumlari. */
    public static function ekranVerisi($salonId)
    {
        $h = self::hepsi($salonId);
        $gruplar = [];
        foreach (self::GRUPLAR as $g => $gAd) $gruplar[$g] = ['kod' => $g, 'ad' => $gAd, 'bildirimler' => []];
        foreach (self::KATALOG as $kod => $k) {
            $al = [];
            foreach ($k['alicilar'] as $a => $spec) $al[$a] = $h[$kod][$a] ?? ['sms' => 0, 'whatsapp' => 0, 'push' => 0];
            $gruplar[$k['grup']]['bildirimler'][] = ['kod' => $kod, 'ad' => $k['ad'], 'aciklama' => $k['aciklama'], 'alicilar' => $al];
        }
        $s = \App\Salonlar::where('id', (int) $salonId)->first();
        $uygulamaMusteri = 0;
        try {
            $uygulamaMusteri = (int) DB::table('bildirim_kimlikleri')->where('aktif', 1)
                ->whereIn('user_id', function ($q) use ($salonId) {
                    $q->select('user_id')->from('musteri_portfoy')->where('salon_id', (int) $salonId);
                })->distinct()->count('user_id');
        } catch (\Throwable $e) {}
        $personelSayisi = 0;
        try { $personelSayisi = (int) \App\Personeller::where('salon_id', (int) $salonId)->where('aktif', 1)->count(); } catch (\Throwable $e) {}
        return [
            'gruplar' => array_values(array_filter($gruplar, function ($g) { return count($g['bildirimler']) > 0; })),
            'wa_yedek_sms' => self::waYedekSms($salonId),
            // "Yaklasan randevu hatirlatma" kac saat once (salonlar.randevu_sms_hatirlatma; eski SMS ekraniyla ayni alan)
            'hatirlatma_saat' => $s ? (int) ($s->randevu_sms_hatirlatma ?: 0) : 0,
            'durum' => [
                'sms_hesabi' => self::smsHesabiVar($s),
                'sms_bakiye' => self::smsBakiye($s),
                'wa_bagli' => self::waBagli($s),
                'uygulama_musteri' => $uygulamaMusteri,
                'personel_sayisi' => $personelSayisi,
            ],
        ];
    }

    /** Hatirlatma suresini (saat) kaydeder; degistiyse loglar. Gecerli: 1-23. */
    public static function hatirlatmaSaatKaydet($salonId, $saat, $kaynak = 'panel', $req = null)
    {
        $saat = (int) $saat;
        if ($saat < 1 || $saat > 23) return null;
        $eski = (int) DB::table('salonlar')->where('id', (int) $salonId)->value('randevu_sms_hatirlatma');
        if ($eski === $saat) return false;
        DB::table('salonlar')->where('id', (int) $salonId)->update(['randevu_sms_hatirlatma' => $saat]);
        self::logYaz($salonId, $req, 'Yaklaşan randevu hatırlatma süresi', $eski . ' saat önce → ' . $saat . ' saat önce' . self::kaynakEk($kaynak),
            ['ayar' => 'randevu_sms_hatirlatma', 'eski' => $eski, 'yeni' => $saat, 'kaynak' => $kaynak]);
        return true;
    }
}
