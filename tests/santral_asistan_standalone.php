<?php
/**
 * SANTRAL SESLI ASISTAN — BAGIMSIZ BIRIM TESTLERI (phpunit / Laravel / DB / LLM GEREKMEZ)
 * ------------------------------------------------------------------------------------
 * RandevuAsistan'in KODLA verilen kararlarini sinar (LLM'e birakilmayan her sey):
 *   zaman cozumu, kesin aktarma, fiyat sorusu, iptal/degistirme girisi, bekletme temizligi,
 *   "Başka yardımcı..." garantisi, sahte basari yakalama, SSS eslesmesi, karsilama varyantlari,
 *   musaitlikte tam eslesme kisayolu, kampanya/paket teklifi.
 * Bugun SABIT: 2026-11-15 Pazar (tarih beklentileri buna gore).
 *
 * Calistir:  php tests/santral_asistan_standalone.php
 */
namespace Carbon {
    class Carbon extends \DateTime {
        const SUNDAY = 0, MONDAY = 1, TUESDAY = 2, WEDNESDAY = 3, THURSDAY = 4, FRIDAY = 5, SATURDAY = 6;
        static $bugun = '2026-11-15';
        static function today() { return new self(self::$bugun . ' 00:00:00'); }
        static function createFromDate($y, $m, $d) { return new self(sprintf('%04d-%02d-%02d', $y, $m, $d)); }
        static function parse($t) { return new self($t); }
        function copy() { return clone $this; }
        function startOfDay() { $this->setTime(0, 0); return $this; }
        function startOfWeek() { return $this->modify('monday this week'); }
        function addDay() { return $this->modify('+1 day'); }
        function addDays($n) { return $this->modify("+$n day"); }
        function addWeek() { return $this->modify('+7 day'); }
        function addWeeks($n) { return $this->modify('+' . (7 * $n) . ' day'); }
        function addYear() { return $this->modify('+1 year'); }
        function next($dow) { $n = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][$dow]; return $this->modify("next $n"); }
        function lt($o) { return $this < $o; }
        function toDateString() { return $this->format('Y-m-d'); }
        function toDateTimeString() { return $this->format('Y-m-d H:i:s'); }
        function diffInDays($o) { return (int) $this->diff($o)->days; }
        function __get($k) { if ($k === 'year') return (int) $this->format('Y'); if ($k === 'month') return (int) $this->format('n'); }
    }
}
namespace Illuminate\Support\Facades {
    class Log { static $satirlar = []; static function __callStatic($m, $a) { self::$satirlar[] = [$m, $a[0] ?? '']; } }
}
namespace Illuminate\Http { class Request { static function create() { return new self; } } }
namespace App\Http\Controllers\Api { class AiAsistanController {} }
namespace App\Http\Controllers { class ApiController {} }
namespace App\Services { class SantralPaket { static $kapali = []; static function asistanKapali(int $id): bool { return !empty(self::$kapali[$id]); } } }
namespace App {
    class SahteKoleksiyon { public $d; function __construct($d) { $this->d = $d; } function get($k = null) { return $k === null ? $this : ($this->d[$k] ?? null); } function keyBy($k) { $o = []; foreach ($this->d as $r) $o[$r->$k] = $r; return new self($o); } function isEmpty() { return !$this->d; } }
    class SalonCalismaSaatleri { static $satirlar = []; static function where() { return new SahteKoleksiyon(self::$satirlar); } }
    // Hatirlatma testleri icin sahte modeller (zincir: where/whereIn -> first/update)
    class SahteSorgu { public $sinif; function __construct($s) { $this->sinif = $s; } function where() { return $this; } function whereIn($k, $v) { $c = $this->sinif; $c::$sonIdler = $v; return $this; } function first() { $c = $this->sinif; return $c::$kayit; } function update($a) { $c = $this->sinif; $c::$guncelleme = $a; return count($c::$sonIdler ?? []); } }
    class Randevular { const BEKLENIYOR = 0, ONAYLANDI = 1, IPTAL_EDILDI = 2; static $kayit; static $sonIdler; static $guncelleme; public $durum = 1; public $randevuya_gelecek = null; public $kaydedildi = false;
        static function where() { return new SahteSorgu(self::class); } function save() { $this->kaydedildi = true; } }
    class Alacaklar { static $kayit; static $sonIdler; static $guncelleme; static function whereIn($k, $v) { return (new SahteSorgu(self::class))->whereIn($k, $v); } }
}
namespace {
    error_reporting(E_ALL & ~E_DEPRECATED);
    function now($tz = null) { return \Carbon\Carbon::today(); }
    $GLOBALS['_config'] = [];
    function config($k = null, $d = null) { return $GLOBALS['_config'][$k] ?? $d; }
    function env($k, $d = null) { return $d; }
    $GLOBALS['_app'] = [];
    function app($c = null) { return $GLOBALS['_app'][$c] ?? null; }

    // Ad -> cinsiyet istatistigi (adCinsiyeti) icin: Cache closure'i calistirir, DB sahte sayimlari dondurur
    class Cache { static function remember($k, $t, $f) { return $f(); } }
    class DB { static $sayim = []; static function selectOne($q, $b) { return (object) (self::$sayim[$b[1]] ?? ['e' => 0, 'k' => 0]); } }

    require __DIR__ . '/../app/Services/TurkceIsimCinsiyet.php';
    require __DIR__ . '/../app/Services/SesliRandevuCozService.php';
    require __DIR__ . '/../app/Services/RandevuAsistan.php';

    $servis = (new ReflectionClass(App\Services\SesliRandevuCozService::class))->newInstanceWithoutConstructor();
    $GLOBALS['_app'][App\Services\SesliRandevuCozService::class] = $servis;

    // ---- mini test cercevesi
    $gecen = 0; $kalan = 0; $hatalar = [];
    function bolum($ad) { echo "\n\033[1m== $ad\033[0m\n"; }
    function esit($ad, $beklenen, $gercek) {
        global $gecen, $kalan, $hatalar;
        if ($beklenen === $gercek) { $gecen++; echo "  \033[32m✓\033[0m $ad\n"; return; }
        $kalan++; $hatalar[] = $ad;
        echo "  \033[31m✗ $ad\033[0m\n     beklenen: " . var_export($beklenen, true) . "\n     gelen   : " . var_export($gercek, true) . "\n";
    }
    function icerir($ad, $parca, $metin) { esit($ad . "  [içerir: $parca]", true, mb_stripos((string) $metin, $parca) !== false); }
    function icermez($ad, $parca, $metin) { esit($ad . "  [içermez: $parca]", false, mb_stripos((string) $metin, $parca) !== false); }

    /** Constructor'siz asistan (DB yok); korunan alanlari dogrudan ayarla. */
    function asistan(array $alan = []) {
        $rc = new ReflectionClass(App\Services\RandevuAsistan::class);
        $a = $rc->newInstanceWithoutConstructor();
        $v = array_merge(['salonId' => 20, 'telefon' => '5321112233', 'salon' => ['ad' => 'Randevumcepte', 'karsilama' => "Randevumcepte'ye", 'adres' => 'Alsancak İzmir', 'hizmetler' => []],
            'musteriAd' => 'Anıl Orbey', 'musteriId' => 123244, '_personeller' => [21 => 'Gülşah', 34 => 'berkcan'], '_paketler' => [], '_kampanyalar' => [], '_sss' => [], '_randevularim' => ['ok' => true, 'randevular' => []]], $alan);
        foreach ($v as $k => $val) { $p = $rc->getProperty($k); $p->setAccessible(true); $p->setValue($a, $val); }
        return $a;
    }
    function cagir($nesne, $metot, ...$arg) { $m = new ReflectionMethod($nesne, $metot); $m->setAccessible(true); return $m->invoke($nesne, ...$arg); }
    $rv1 = ['id' => 901, 'durum' => 'talep (onay bekliyor) — iptal/güncelleme yapılabilir', 'tarih' => '2026-11-17', 'gun' => 'Salı, 17 Kasım', 'saat' => '14:00', 'hizmet' => 'Saç Kesimi', 'personel' => 'Gülşah', 'hizmet_id' => 5, 'personel_id' => 21];
    $rv2 = ['id' => 902, 'durum' => 'onaylı', 'tarih' => '2026-11-20', 'gun' => 'Cuma, 20 Kasım', 'saat' => '10:00', 'hizmet' => 'Lazer Epilasyon', 'personel' => 'berkcan', 'hizmet_id' => 9, 'personel_id' => 34];

    // =====================================================================================
    bolum('Zaman çözümü (bugün 15 Kasım 2026 Pazar)');
    foreach ([
        ['Yarın saç kesimi için randevu almak istiyorum', '2026-11-16', null],
        ['Çarşamba 14', '2026-11-18', '14:00'],
        ['Perşembe 10', '2026-11-19', '10:00'],
        ["Çarşamba saat 14'te", '2026-11-18', '14:00'],
        ['Önümüzdeki hafta çarşamba 14', '2026-11-18', '14:00'],
        ['Önümüzdeki hafta', '2026-11-16', null],
        ['Haftaya salı 11', '2026-11-17', '11:00'],
        ['10 Aralık', '2026-12-10', null],
        ['5 Şubat saat 15', '2027-02-05', '15:00'],
        ['10 Aralık 14', '2026-12-10', '14:00'],
        ['Yarın 3', '2026-11-16', '15:00'],
        ['Çarşamba 2 buçukta', '2026-11-18', '14:30'],
        ['Perşembe sabah 10', '2026-11-19', '10:00'],
        ['Randevumu iptal etmek istiyorum', null, null],
    ] as [$t, $tar, $sa]) {
        $z = $servis->zamanCoz($t);
        esit("\"$t\" → tarih", $tar, $z['tarih']);
        esit("\"$t\" → saat", $sa, $z['saat']);
    }
    \Carbon\Carbon::$bugun = '2026-11-17'; // Salı
    $z = $servis->zamanCoz('Önümüzdeki hafta çarşamba 14');
    esit('Salı günü "önümüzdeki hafta çarşamba" → 25 Kasım', '2026-11-25', $z['tarih']);
    \Carbon\Carbon::$bugun = '2026-11-15';

    // =====================================================================================
    $a = asistan();
    bolum('Kesin aktarma (bağlanma isteği)');
    foreach (['Beni işletmeye bağlar mısın' => true, 'Beni genişletmeye bağlar mısın' => true, 'Operatörle görüşmek istiyorum' => true,
              'Yetkiliye bağlayın lütfen' => true, 'Biriyle görüşmek istiyorum' => true, 'Beni bağlayabilir misiniz' => true,
              'Yarın saç kesimi için randevu almak istiyorum' => false, 'Randevumu iptal etmek istiyorum' => false, 'Lazer paketim var mı' => false] as $t => $b) {
        esit("\"$t\"", $b, cagir($a, 'aktarmaIstegiMi', $t));
    }

    bolum('Fiyat sorusu → aktarma');
    foreach (['Saç kesimi ne kadar?' => true, 'Lazer epilasyonun fiyatı nedir' => true, 'Kaç lira' => true, 'Ücret ne kadar' => true, 'Taksit yapıyor musunuz' => true,
              'Saç kesimi ne kadar sürer' => false, 'Ne kadar erken gelmeliyim' => false, 'Kampanyanız var mı' => false, 'İndirim var mı' => false,
              'Saat kaçta açıksınız' => false, 'Randevumu iptal etmek istiyorum' => false] as $t => $b) {
        esit("\"$t\"", $b, cagir($a, 'fiyatSorusuMu', $t));
    }

    // =====================================================================================
    bolum('konus(): LLM\'den ÖNCE kodla cevaplananlar');
    $d = $a->konus('Saç kesimi ne kadar?', []);
    esit('fiyat → aksiyon aktar', 'aktar', $d['aksiyon']);
    icerir('fiyat → cümle', 'Fiyat bilgisi için', $d['cevap']);
    $d = $a->konus('Beni işletmeye bağlar mısın', []);
    esit('bağlanma → aksiyon aktar', 'aktar', $d['aksiyon']);
    icerir('bağlanma → cümle', 'yetkili bir arkadaşımıza', $d['cevap']);
    $d = $a->konus('', []);
    icerir('boş metin', 'dinliyorum', $d['cevap']);

    $a1 = asistan(['_randevularim' => ['ok' => true, 'randevular' => [$rv1]]]);
    $d = $a1->konus('İptal etmek istiyorum', []);
    esit('iptal (tek randevu) → aksiyon yok (onay bekler)', null, $d['aksiyon']);
    icerir('iptal (tek randevu) → randevuyu okur', 'Salı, 17 Kasım saat 14:00', $d['cevap']);
    icerir('iptal (tek randevu) → personel+hizmet', 'Gülşah ile Saç Kesimi', $d['cevap']);
    icerir('iptal (tek randevu) → onay sorar', 'iptal etmemi onaylıyor musunuz', $d['cevap']);
    icermez('iptal → bekletme yok', 'moment', $d['cevap']);
    $a2 = asistan(['_randevularim' => ['ok' => true, 'randevular' => [$rv1, $rv2]]]);
    $d = $a2->konus('Randevumu iptal etmek istiyorum', []);
    icerir('iptal (iki randevu) → sayı', '2 randevunuz var', $d['cevap']);
    icerir('iptal (iki randevu) → ikincisi', 'Cuma, 20 Kasım', $d['cevap']);
    icerir('iptal (iki randevu) → seçtirir', 'Hangisini iptal edelim', $d['cevap']);
    $d = $a2->konus('Randevumu ertelemek istiyorum', []);
    icerir('değiştirme (iki randevu)', 'Hangisini değiştirelim', $d['cevap']);
    $d = $a1->konus('Randevumun saatini değiştirmek istiyorum', []);
    icerir('değiştirme (tek) → yeni zaman sorar', 'Hangi gün ve saate', $d['cevap']);
    $a0 = asistan();
    $d = $a0->konus('Randevumu iptal etmek istiyorum', []);
    icerir('iptal (randevu yok)', 'randevu görünmüyor', $d['cevap']);
    icerir('iptal (randevu yok) → kapanış sorusu', 'Başka yardımcı olabileceğim', $d['cevap']);
    $gecmis = [['role' => 'assistant', 'content' => 'Salı, 17 Kasım saat 14:00\'te Gülşah ile Saç Kesimi randevunuz var. Bu randevuyu iptal etmemi onaylıyor musunuz?']];
    esit('liste zaten okunduysa tekrar etmez (modele bırakır)', null, cagir($a1, 'iptalGuncelleGiris', 'iptal etmek istiyorum', $gecmis));
    esit('tarih verilmiş iptal → modele bırakır', null, cagir($a1, 'iptalGuncelleGiris', 'çarşamba 14teki randevumu iptal et', []));
    esit('kayıtsız arayan → modele bırakır', null, cagir(asistan(['musteriId' => null, 'musteriAd' => null]), 'iptalGuncelleGiris', 'randevumu iptal et', []));

    bolum('SSS (işletmenin kendi cevapları)');
    $as = asistan(['_sss' => [['tet' => 'otopark, park yeri', 'cevap' => 'Binamızın önünde ücretsiz otopark var.'], ['tet' => 'kredi kartı, kart geçiyor', 'cevap' => 'Tüm kartlar geçerlidir.']]]);
    $d = $as->konus('Otoparkınız var mı?', []);
    icerir('tetikleyici "otopark" (ekli: otoparkınız)', 'ücretsiz otopark', $d['cevap']);
    icerir('SSS sonrası kapanış sorusu', 'Başka yardımcı olabileceğim', $d['cevap']);
    $d = $as->konus('Kredi kartı geçiyor mu', []);
    icerir('ikinci SSS', 'Tüm kartlar', $d['cevap']);
    esit('alakasız cümle SSS\'ye düşmez', null, cagir($as, 'sssCevap', 'Yarın saç kesimi istiyorum'));
    $d = $as->konus('Otopark ücreti ne kadar', []);
    esit('SSS kelimesi geçse de fiyat sorusu → aktar', 'aktar', $d['aksiyon']);

    // =====================================================================================
    bolum('Hizmet süresi sorusu (kodla, gerçek süre)');
    $ah = asistan(['salon' => ['ad' => 'Randevumcepte', 'karsilama' => '', 'adres' => '', 'hizmetler' => [
        ['id' => 5, 'ad' => 'Saç Kesimi', 'fiyat' => 300, 'sure_dk' => 30], ['id' => 9, 'ad' => 'Lazer Epilasyon', 'fiyat' => 900, 'sure_dk' => 0],
        ['id' => 11, 'ad' => 'Saç Kesimi Erkek', 'fiyat' => 200, 'sure_dk' => 20]]]]);
    $d = $ah->konus('Saç kesimi ne kadar sürer?', []);
    esit('aktarmaz', null, $d['aksiyon']);
    icerir('gerçek süre', 'Saç Kesimi yaklaşık 30 dakika', $d['cevap']);
    icerir('kapanış sorusu', 'Başka yardımcı olabileceğim', $d['cevap']);
    icerir('en uzun ad eşleşir', 'Saç Kesimi Erkek yaklaşık 20 dakika', cagir($ah, 'sureCevabi', 'Erkek saç kesimi değil, saç kesimi erkek kaç dakika'));
    icerir('süre girilmemiş → uydurmaz', 'kesin süre bilgisi bende kayıtlı değil', cagir($ah, 'sureCevabi', 'Lazer epilasyon ne kadar sürer'));
    esit('hizmet adı yoksa modele bırakır', null, cagir($ah, 'sureCevabi', 'Ne kadar sürer'));
    esit('süre sorusu değilse null', null, cagir($ah, 'sureCevabi', 'Saç kesimi randevusu istiyorum'));
    esit('fiyat sorusu yine aktar', 'aktar', $ah->konus('Saç kesimi ne kadar', [])['aksiyon']);

    bolum('Bekletme cümlesi temizliği');
    foreach ([
        'Bir moment, sizi biraz bekleteceğim.' => '',
        'Bir moment lütfen, randevularınızı kontrol ediyorum.' => '',
        'Hemen bakıyorum. Pazartesi 14:00\'te randevunuz var, iptal edeyim mi?' => 'Pazartesi 14:00\'te randevunuz var, iptal edeyim mi?',
        'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?' => 'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?',
    ] as $g => $b) esit("\"$g\"", $b, cagir($a, 'bekletmeTemizle', $g));

    bolum('"Başka yardımcı olabileceğim..." garantisi');
    foreach ([
        'Yol tarifini gönderdim. Size nasıl yardımcı olabilirim?' => 'Yol tarifini gönderdim. Başka yardımcı olabileceğim bir konu var mı?',
        'Yol tarifini gönderdim, size başka nasıl yardımcı olabilirim?' => 'Yol tarifini gönderdim. Başka yardımcı olabileceğim bir konu var mı?',
        'Randevu talebiniz oluşturuldu' => 'Randevu talebiniz oluşturuldu. Başka yardımcı olabileceğim bir konu var mı?',
        'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?' => 'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?',
    ] as $g => $b) esit("\"$g\"", $b, cagir($a, 'baskaYardimEkle', $g));

    bolum('Sahte başarı yakalama (araç başarısı olmadan "oluşturuldu")');
    foreach (['Randevunuz oluşturuldu, bekliyoruz.' => true, 'Randevu talebiniz oluşturuldu.' => true, 'Randevunuz başarıyla güncellendi.' => true,
              'Saç Kesimi randevusu talebiniz oluşturuldu, işletmemiz onayladığında...' => true, 'Randevunuz talep olarak oluşturuldu.' => true,
              'Randevunuz kaydedildi.' => true,
              'Randevunuz iptal edildi.' => false, 'Pazartesi 12de randevunuz var.' => false, 'Randevu talebi oluşturayım mı?' => false,
              'Saç Kesimi randevusu talebi oluşturayım mı?' => false] as $t => $b) {
        esit("\"$t\"", $b, cagir($a, 'sahteBasariMetni', $t));
    }

    // =====================================================================================
    bolum('Karşılama varyantları');
    icerir('kayıtlı', 'Sayın Anıl Orbey', $a->karsilama());
    icerir('kayıtlı → yardım sorusu', 'Size nasıl yardımcı olabilirim?', $a->karsilama());
    $kp = asistan(['_paketler' => [['adisyon_paket_id' => 1, 'paket_adi' => 'Lazer', 'hizmetler' => [['hizmet_id' => 9, 'ad' => 'Lazer Epilasyon', 'kalan_seans' => 4, 'personeller' => []]]]]]);
    // 2026-11-17: paket karsilamada SOYLENMEZ; musteri isteyince asistan getirir (PAKET AKIŞI)
    icermez('paket → karşılamada söylenmez', 'paket', $kp->karsilama());
    icerir('paket → kısa karşılama', 'Ben yapay zeka asistanınızım. Size nasıl yardımcı olabilirim?', $kp->karsilama());
    $kk = asistan(['_kampanyalar' => [['kampanya_id' => 5, 'ad' => 'Yaz Bakım', 'bitis' => '2026-12-01', 'durum' => 'davetli_bekliyor', 'katilimci_id' => 77, 'randevu_gerekli' => false]]]);
    icerir('davetli kampanya → teklif', 'Yaz Bakım kampanyamıza davetlisiniz', $kk->karsilama());
    $kk2 = asistan(['_kampanyalar' => [['kampanya_id' => 5, 'ad' => 'Yaz Bakım', 'bitis' => null, 'durum' => 'katildi', 'katilimci_id' => 77, 'randevu_gerekli' => false]]]);
    icermez('zaten katılmış kampanya teklif edilmez', 'davetlisiniz', $kk2->karsilama());
    $kpk = asistan(['_paketler' => $kp->karsilama() ? cagir($kp, 'paketler') : [], '_kampanyalar' => $kk->karsilama() ? cagir($kk, 'kampanyalar') : []]);
    icerir('paket + kampanya → yalnız kampanya daveti', 'davetlisiniz', $kpk->karsilama());
    icermez('paket + kampanya → paket söylenmez', 'paketiniz', $kpk->karsilama());
    $ky = asistan(['musteriAd' => null, 'musteriId' => null]);
    icerir('kayıtsız → ad soyad ister', 'adınızı ve soyadınızı', $ky->karsilama());
    $kg = asistan(['musteriAd' => null, 'musteriId' => null, 'telefon' => null]);
    icerir('gizli numara → telefon da ister', 'telefon numaranızı', $kg->karsilama());

    bolum('Kampanya katılımı (DB\'siz korumalar)');
    $kx = asistan(['_kampanyalar' => [['kampanya_id' => 5, 'ad' => 'Yaz Bakım', 'bitis' => null, 'durum' => 'katildi', 'katilimci_id' => 77, 'randevu_gerekli' => false]]]);
    esit('zaten katılmış → zaten_katildi', true, cagir($kx, 'kampanyaKatil', 5)['zaten_katildi'] ?? null);
    esit('listede olmayan kampanya id → hata', false, cagir($kx, 'kampanyaKatil', 999)['ok']);
    esit('kayıtsız müşteri → hata', false, cagir(asistan(['musteriId' => null]), 'kampanyaKatil', 5)['ok']);

    // =====================================================================================
    bolum('Müsaitlik: istenen saat boşsa alternatif YOK, doluysa 2 seçenek');
    $sahteServis = new class {
        public $bos = true;
        function musaitlikBul($salon, $pid, $hizmet, $tarih, $saat, $vakit, $ufuk = 14) {
            if ($saat && $this->bos) return ['bulundu' => true, 'tarih' => $tarih, 'saat' => $saat, 'sure_dk' => 30, 'tam_istek' => true];
            if ($saat) return ['bulundu' => true, 'tarih' => $tarih, 'saat' => $pid == 21 ? '15:00' : '16:00', 'sure_dk' => 30, 'tam_istek' => false];
            return ['bulundu' => true, 'tarih' => $tarih, 'saat' => '10:00', 'sure_dk' => 30, 'tam_istek' => false];
        }
    };
    $GLOBALS['_app'][App\Services\SesliRandevuCozService::class] = $sahteServis;
    $am = asistan();
    $r = cagir($am, 'musaitSaatlerPersonelli', ['hizmet_id' => 5, 'tarih' => '2026-11-18', 'saat' => '14:00', 'personel_id' => 21]);
    esit('boş saat → tam_eslesme', true, $r['tam_eslesme'] ?? false);
    esit('boş saat → tek seçenek', 1, count($r['secenekler']));
    esit('boş saat → gün metni', 'Çarşamba, 18 Kasım', $r['secenekler'][0]['gun']);
    $sahteServis->bos = false;
    $r = cagir($am, 'musaitSaatlerPersonelli', ['hizmet_id' => 5, 'tarih' => '2026-11-18', 'saat' => '14:00', 'personel_id' => 21]);
    esit('dolu saat → tam_eslesme yok', false, $r['tam_eslesme'] ?? false);
    esit('dolu saat → istenen_saat_dolu', true, $r['istenen_saat_dolu'] ?? null);
    esit('dolu saat → seçenekte gün metni', 'Çarşamba, 18 Kasım', $r['secenekler'][0]['gun'] ?? null);
    $r = cagir($am, 'musaitSaatlerPersonelli', ['hizmet_id' => 5, 'tarih' => '2026-11-18', 'personel_id' => 99]);
    esit('takvimde görünmeyen personel → hata', false, $r['ok']);
    $r = cagir($am, 'musaitSaatlerPersonelli', ['hizmet_id' => 5, 'tarih' => '18 Kasım']);
    esit('ISO olmayan tarih → hata', false, $r['ok']);
    $GLOBALS['_app'][App\Services\SesliRandevuCozService::class] = $servis;

    bolum('Zaman notu (modele giden [SİSTEM zaman çözümü])');
    icerir('"çarşamba 14"', 'tarih=2026-11-18 (Çarşamba, 18 Kasım), saat=14:00', cagir($a, 'zamanNotu', 'çarşamba 14'));
    icerir('"5 şubat" (yıl eklenir)', 'Cuma, 5 Şubat 2027', cagir($a, 'zamanNotu', '5 şubat'));
    esit('zaman yoksa not yok', '', cagir($a, 'zamanNotu', 'randevumu iptal etmek istiyorum'));

    bolum('Çalışma saatleri metni');
    $o = function ($g, $c, $b, $e) { return (object) ['haftanin_gunu' => $g, 'calisiyor' => $c, 'baslangic_saati' => $b, 'bitis_saati' => $e]; };
    App\SalonCalismaSaatleri::$satirlar = [$o(1, 1, '09:00:00', '19:00:00'), $o(2, 1, '09:00:00', '19:00:00'), $o(3, 1, '09:00:00', '19:00:00'),
        $o(4, 1, '09:00:00', '19:00:00'), $o(5, 1, '09:00:00', '19:00:00'), $o(6, 1, '10:00:00', '17:00:00'), $o(7, 0, '00:00:00', '00:00:00')];
    $cs = cagir($a, 'calismaSaatleriMetni');
    icerir('hafta içi saat', 'Pazartesi 09:00-19:00', $cs);
    icerir('cumartesi farklı saat', 'Cumartesi 10:00-17:00', $cs);
    icerir('pazar kapalı', 'Pazar kapalı', $cs);
    App\SalonCalismaSaatleri::$satirlar = [];
    esit('saat girilmemişse', 'tanımlı değil', cagir($a, 'calismaSaatleriMetni'));

    bolum('Kampanya ve paket bağlamı (modele giden satırlar)');
    $kb = asistan(['_kampanyalar' => [
        ['kampanya_id' => 5, 'ad' => 'Yaz Bakım', 'bitis' => '2026-12-01', 'durum' => 'davetli_bekliyor', 'katilimci_id' => 77, 'randevu_gerekli' => true],
        ['kampanya_id' => 6, 'ad' => 'Kış Lazer', 'bitis' => null, 'durum' => 'listede_degil', 'katilimci_id' => null, 'randevu_gerekli' => false],
        ['kampanya_id' => 7, 'ad' => 'Bayram', 'bitis' => null, 'durum' => 'katilmadi', 'katilimci_id' => 78, 'randevu_gerekli' => false]]]);
    $km = cagir($kb, 'kampanyaMetni');
    icerir('davetli etiketi', 'kampanya_id 5 | Yaz Bakım | son gün Salı, 1 Aralık | müşteri DAVETLİ', $km);
    icerir('listede değil etiketi', 'kampanya_id 6 | Kış Lazer | müşteri listede değil', $km);
    icerir('katılmadı etiketi', 'katılmayacağını söyledi', $km);
    icermez('kampanya bağlamında fiyat/oran yok', '%', $km);
    esit('kampanya yoksa', ' yok', cagir(asistan(), 'kampanyaMetni'));
    $kaynak2 = file_get_contents(__DIR__ . '/../app/Services/RandevuAsistan.php');
    icerir('pasif kampanya filtresi (aktifmi=1)', "->where('aktifmi', 1)", $kaynak2);
    icermez('hizmet listesinde fiyat verilmiyor', "\$h['fiyat'] . ' TL'", $kaynak2);
    $pb = asistan(['_paketler' => [['adisyon_paket_id' => 1, 'paket_adi' => 'Lazer', 'hizmetler' => [
        ['hizmet_id' => 9, 'ad' => 'Lazer Epilasyon', 'kalan_seans' => 4, 'personeller' => [['personel_id' => 21, 'ad' => 'Gülşah']]],
        ['hizmet_id' => 10, 'ad' => 'Cilt Bakımı', 'kalan_seans' => null, 'personeller' => []]]]]]);
    $ps = cagir($pb, 'paketSatirlari');
    icerir('paket: kalan seans', 'Lazer Epilasyon (hizmet_id 9, 4 seans kaldı)', $ps);
    icerir('paket: uygun personel', 'Gülşah (personel_id 21)', $ps);
    icerir('paket: süreli/sınırsız', 'süreli/sınırsız', $ps);
    icerir('paket: personel tanımsız', 'takvimi açık tüm personel uygun', $ps);
    // Paketi asistan getirir: toplam/kullanilan/kalan seans
    $pt = asistan(['_paketler' => [['adisyon_paket_id' => 1, 'paket_adi' => 'Lazer', 'hizmetler' => [
        ['hizmet_id' => 9, 'ad' => 'Lazer Epilasyon', 'kalan_seans' => 4, 'toplam_seans' => 10, 'kullanilan_seans' => 6, 'personeller' => []],
        ['hizmet_id' => 10, 'ad' => 'Cilt Bakımı', 'kalan_seans' => null, 'toplam_seans' => null, 'kullanilan_seans' => null, 'personeller' => []]]]]]);
    icerir('paket: toplam/kullanılan bağlamda', 'toplam 10 seans, kullanılan 6, 4 seans kaldı', cagir($pt, 'paketSatirlari'));
    esit('paketBilgisi: sayılı paket', ['paket_adi' => 'Lazer', 'hizmet' => 'Lazer Epilasyon', 'sinirsiz' => false, 'toplam_seans' => 10, 'kullanilan_seans' => 6, 'kalan_seans' => 4], cagir($pt, 'paketBilgisi', 9));
    esit('paketBilgisi: sınırsız pakette sayı yok', ['paket_adi' => 'Lazer', 'hizmet' => 'Cilt Bakımı', 'sinirsiz' => true], cagir($pt, 'paketBilgisi', 10));
    esit('paketBilgisi: pakette olmayan hizmet → null', null, cagir($pt, 'paketBilgisi', 99));
    esit('"Paketimde ne kadar seans kaldı" fiyat sorusu DEĞİL', false, cagir($pt, 'fiyatSorusuMu', 'Paketimde ne kadar seans kaldı?'));
    esit('"Ne kadar hakkım kaldı" fiyat sorusu DEĞİL', false, cagir($pt, 'fiyatSorusuMu', 'Ne kadar hakkım kaldı'));
    esit('"Seans ücreti ne kadar" hâlâ fiyat sorusu', true, cagir($pt, 'fiyatSorusuMu', 'Seans ücreti ne kadar'));
    esit('"Lazer ne kadar" hâlâ fiyat sorusu', true, cagir($pt, 'fiyatSorusuMu', 'Lazer ne kadar'));

    bolum('API uyumu: parametresiz aracın girdisi JSON nesnesi gider');
    $icerik = json_decode('{"content":[{"type":"text","text":"Bakıyorum"},{"type":"tool_use","id":"t1","name":"mevcut_randevular","input":{}},{"type":"tool_use","id":"t2","name":"musait_saatler","input":{"hizmet_id":5}}]}', true)['content'];
    esit('json_decode sonrası (hata kaynağı) dizi olur', '[]', json_encode($icerik[1]['input']));
    $duz = cagir($a, 'aracGirdisiNesne', $icerik);
    esit('boş girdi {} olarak gider', '{}', json_encode($duz[1]['input']));
    esit('dolu girdi korunur', '{"hizmet_id":5}', json_encode($duz[2]['input']));
    esit('metin bloğu dokunulmaz', 'Bakıyorum', $duz[0]['text']);

    bolum('Randevu TALEP ifadesi');
    esit('cümle başı büyük harf korunur', 'Randevu talebiniz oluşturuldu. Bilgilendirme mesajı ilettim.', cagir($a, 'talepIfadesi', 'Randevunuz kaydedildi. Bilgilendirme mesajı ilettim.'));
    esit('cümle içi küçük harf', "Perşembe 11:00'te randevu talebiniz oluşturuldu.", cagir($a, 'talepIfadesi', "Perşembe 11:00'te randevunuz başarıyla oluşturuldu."));
    esit('"talebiniz" zaten varsa dokunmaz', 'Randevu talebiniz oluşturuldu.', cagir($a, 'talepIfadesi', 'Randevu talebiniz oluşturuldu.'));

    bolum('Onay turu: zorla randevu güvenlik ağı');
    $onaySoru = [['role' => 'assistant', 'content' => "Perşembe, 19 Kasım saat 11:00'te Gülşah ile Saç Kesimi için randevu talebi oluşturayım mı?"]];
    esit('onay sorusu algılanır', true, cagir($a, 'onayBekleniyorMu', $onaySoru));
    esit('başka soru onay değil', false, cagir($a, 'onayBekleniyorMu', [['role' => 'assistant', 'content' => 'Hangi saat uygun olur?']]));
    esit('ekli varyant ("randevusu talebi oluşturayım mı") algılanır', true, cagir($a, 'onayBekleniyorMu', [['role' => 'assistant', 'content' => "Perşembe 11:00'te Gülşah ile Saç Kesimi randevusu talebi oluşturayım mı?"]]));
    esit('iptal onayı randevu onayı sayılmaz', false, cagir($a, 'onayBekleniyorMu', [['role' => 'assistant', 'content' => 'Bu randevuyu iptal etmemi onaylıyor musunuz?']]));
    foreach (['Evet' => true, 'Evet, olsun' => true, 'Tamam onaylıyorum' => true, 'Olur' => true, 'Hayır' => false, 'Evet ama başka saat olsun' => false, 'Yok vazgeçtim' => false] as $t => $b) {
        esit("onay cevabı \"$t\"", $b, cagir($a, 'onayMi', $t));
    }
    $zr = new class extends App\Services\RandevuAsistan {
        public $sahteYanit; public $sahteSonuc;
        public function __construct() {}
        protected function cagir(array $m, string $k, int $t = 0, array $e = []): ?array { $this->sonEk = $e; return $this->sahteYanit; }
        protected function randevuOlusturPersonelli(array $in): array { $this->sonGirdi = $in; return $this->sahteSonuc; }
    };
    foreach (['salonId' => 20, 'musteriId' => 123244, 'musteriAd' => 'Anıl Orbey', '_personeller' => [21 => 'Gülşah'], 'salon' => ['hizmetler' => []]] as $k => $v) { $p = new ReflectionProperty(App\Services\RandevuAsistan::class, $k); $p->setAccessible(true); $p->setValue($zr, $v); }
    $zr->sahteYanit = ['content' => [['type' => 'tool_use', 'id' => 'x', 'name' => 'randevu_olustur', 'input' => ['tarih_saat' => '2026-11-19T11:00', 'hizmet_id' => 5, 'personel_id' => 21]]]];
    $zr->sahteSonuc = ['ok' => true, 'randevu_id' => 555, 'tarih' => '2026-11-19', 'saat' => '11:00', 'personel' => 'Gülşah', 'bilgilendirme_gonderildi' => true];
    $z = cagir($zr, 'zorlaRandevu', [['role' => 'user', 'content' => 'Evet']], 'k');
    esit('tool_choice ile randevu_olustur zorlanır', ['type' => 'tool', 'name' => 'randevu_olustur'], $zr->sonEk['tool_choice'] ?? null);
    esit('modelin verdiği id\'ler kullanılır', 21, $zr->sonGirdi['personel_id'] ?? null);
    esit('aksiyon randevu', 'randevu', $z[1]);
    esit('randevu id', 555, $z[2]);
    icerir('deterministik talep cümlesi', "Perşembe, 19 Kasım saat 11:00'te Gülşah ile randevu talebiniz oluşturuldu", $z[0]);
    icerir('bilgilendirme ayarı açık', 'bilgilendirme mesajı ilettim', $z[0]);
    $zr->sahteSonuc = ['ok' => false, 'hata' => 'Bu saat çakışıyor.'];
    $z = cagir($zr, 'zorlaRandevu', [['role' => 'user', 'content' => 'Evet']], 'k');
    esit('kayıt başarısız → aksiyon yok', null, $z[1]);
    icerir('kayıt başarısız → dürüst cümle', 'kaydedemedim', $z[0]);
    icerir('çakışma → yeni saat önerir', 'az önce doldu', $z[0]);
    esit('son mesaj user değilse zorlamaz', null, cagir($zr, 'zorlaRandevu', [['role' => 'assistant', 'content' => 'x']], 'k'));

    bolum('Randevu onay cümlesi koddan (model atlasa da)');
    $oc = cagir($zr, 'randevuOnayCumlesi', ['ok' => true, 'tarih' => '2026-11-26', 'saat' => '11:00:00', 'personel' => 'Gülşah', 'bilgilendirme_gonderildi' => true]);
    esit('tam cümle', "Perşembe, 26 Kasım saat 11:00'te Gülşah ile randevu talebiniz oluşturuldu, işletmemiz onayladığında bilgilendirileceksiniz. Size bilgilendirme mesajı ilettim.", $oc);
    $oc = cagir($zr, 'randevuOnayCumlesi', ['ok' => true, 'tarih' => '2026-11-26', 'saat' => '11:00', 'personel' => 'Gülşah', 'paket_seansi' => true, 'bilgilendirme_gonderildi' => false]);
    icerir('paket seansı belirtilir', 'Gülşah ile paketinizden randevu talebiniz', $oc);
    esit('ayar kapalı → bilgilendirme denmez', false, mb_stripos($oc, 'bilgilendirme mesajı') !== false);

    bolum('Turlar arası araç bağlamı (gizli not)');
    $bn = cagir($a, 'baglamNotu', 'musait_saatler', ['hizmet_id' => 5], ['ok' => true, 'secenekler' => [['tarih' => '2026-11-19', 'saat' => '11:00', 'personel_id' => 21, 'personel' => 'Gülşah']]]);
    esit('müsaitlik notu id\'leri taşır', 'musait_saatler hizmet_id=5 seçenekler: 2026-11-19T11:00 personel_id=21 (Gülşah)', $bn);
    esit('bilgi aracı olmayan → not yok', '', cagir($a, 'baglamNotu', 'yol_tarifi_gonder', [], ['ok' => true]));
    $gm = cagir($a, 'gecmisMesajlari', [['role' => 'assistant', 'content' => 'Oluşturayım mı?', 'baglam' => $bn], ['role' => 'user', 'content' => 'Evet']]);
    esit('ilk mesaj user', 'user', $gm[0]['role']);
    icerir('gizli not modele gider', 'personel_id=21', $gm[1]['content']);
    esit('gizli not seslendirilmez', 'Tamam.', cagir($a, 'ttsTemizle', 'Tamam. [SİSTEM — bu turdaki araç sonuçları, müşteri duymadı: x=1]'));

    bolum('TTS temizliği');
    icerir('uzun numara rakam rakam', '5 3 2', cagir($a, 'ttsTemizle', 'Numaranız 5321112233 doğru mu?'));

    // =====================================================================================
    bolum('Hatırlatma araması (randevu 1 gün önce / alacak 2 gün önce)');
    $GLOBALS['_config']['services.santral_ai.hatirlatma_salonlar'] = '20, 35';
    esit('salon listede → yeni asistan', true, App\Services\RandevuAsistan::aiHatirlatmaAcik(35));
    esit('salon listede değil → eski IVR', false, App\Services\RandevuAsistan::aiHatirlatmaAcik(7));
    $GLOBALS['_config']['services.santral_ai.hatirlatma_salonlar'] = '*';
    esit('* → tüm salonlar', true, App\Services\RandevuAsistan::aiHatirlatmaAcik(7));
    App\Services\SantralPaket::$kapali[7] = true;
    esit('paket bitti + durdur → klasik IVR', false, App\Services\RandevuAsistan::aiHatirlatmaAcik(7));
    App\Services\SantralPaket::$kapali = [];
    $GLOBALS['_config']['services.santral_ai.hatirlatma_salonlar'] = '';
    esit('boş → kapalı', false, App\Services\RandevuAsistan::aiHatirlatmaAcik(20));

    $hm = ['tur' => 'randevu', 'randevu_id' => 901, 'soz' => "yarın, Pazartesi, 16 Kasım saat 14:00'te Gülşah ile Saç Kesimi"];
    $hk = ['role' => 'assistant', 'content' => "Sayın Anıl Orbey, ben Randevumcepte yapay zeka asistanıyım. {$hm['soz']} randevunuzu hatırlatmak için arıyorum. Randevunuza gelebilecek misiniz?", 'hatirlatma' => $hm];
    $h = asistan();
    esit('ilk soru → cevap bekleniyor', 'soru', cagir($h, 'hatirlatmaBekleyen', [$hk])['adim'] ?? null);
    esit('iş bittikten sonra → bekleme yok', null, cagir($h, 'hatirlatmaBekleyen', [$hk, ['role' => 'user', 'content' => 'evet'], ['role' => 'assistant', 'content' => 'Teşekkür ederiz. Başka yardımcı olabileceğim bir konu var mı?']]));
    esit('normal (gelen) arama → bekleme yok', null, cagir($h, 'hatirlatmaBekleyen', [['role' => 'assistant', 'content' => 'Randevunuza gelebilecek misiniz?']]));

    $yeniRandevu = function ($durum = 1) { $r = new App\Randevular(); $r->durum = $durum; App\Randevular::$kayit = $r; return $r; };
    $soru = ['adim' => 'soru'] + $hm;
    $r = $yeniRandevu();
    $c = cagir($h, 'hatirlatmaCevap', 'Evet geleceğim', $soru);
    esit('"Evet geleceğim" → teyit', 'teyit', $c['aksiyon'] ?? null);
    esit('randevuya_gelecek işaretlenir', [1, true], [$r->randevuya_gelecek, $r->kaydedildi]);
    icerir('teyit cümlesi', 'sizi bekliyoruz', $c['cevap']);
    icerir('teyit sonrası kapanış sorusu', 'Başka yardımcı olabileceğim', $c['cevap']);
    $c = cagir($h, 'hatirlatmaCevap', 'Tabii ki gelirim', $soru);
    esit('"Tabii ki gelirim" → teyit', 'teyit', $c['aksiyon'] ?? null);
    $r = $yeniRandevu();
    $c = cagir($h, 'hatirlatmaCevap', 'Hayır gelemeyeceğim', $soru);
    icerir('"gelemeyeceğim" → ertele mi iptal mi', 'ertelemek mi istersiniz, yoksa iptal etmemi mi istersiniz?', $c['cevap'] ?? '');
    esit('olumsuzda gelecek işaretlenmez', null, $r->randevuya_gelecek);
    esit('ertele/iptal sorusu → sonraki adım', 'ertele_iptal', cagir($h, 'hatirlatmaBekleyen', [$hk, ['role' => 'user', 'content' => 'hayır'], ['role' => 'assistant', 'content' => $c['cevap']]])['adim'] ?? null);
    $c = cagir($h, 'hatirlatmaCevap', 'İptal edin', $soru);
    esit('"İptal edin" → randevuyu adıyla onaylatır', $hm['soz'] . ' randevunuzu iptal etmemi onaylıyor musunuz?', $c['cevap'] ?? null);
    icerir('iptal için randevu id modele taşınır', 'randevu_id=901', $c['baglam'] ?? '');
    $c = cagir($h, 'hatirlatmaCevap', 'Ertelemek istiyorum', $soru);
    esit('"Ertelemek istiyorum" → hangi gün', 'Tabii, randevunuzu hangi gün ve saate almak istersiniz?', $c['cevap'] ?? null);
    esit('"perşembe 14e erteleyelim" → model (müsaitlik + güncelle)', null, cagir($h, 'hatirlatmaCevap', "Perşembe 14'e erteleyelim", $soru));
    $c = cagir($h, 'hatirlatmaCevap', 'ertele', ['adim' => 'ertele_iptal'] + $hm);
    esit('2. adım "ertele" → hangi gün', 'Tabii, randevunuzu hangi gün ve saate almak istersiniz?', $c['cevap'] ?? null);
    esit('anlaşılmayan cevap → model', null, cagir($h, 'hatirlatmaCevap', 'Kim arıyor', $soru));
    $yeniRandevu(2);
    esit('randevu bu arada iptal edilmişse → model', null, cagir($h, 'hatirlatmaCevap', 'Evet', $soru));

    $r = $yeniRandevu();
    $k = $h->konus('Evet, geleceğim', [$hk]);
    esit('konus: hatırlatma cevabı LLM\'siz', ['teyit', 'hatirlatma'], [$k['aksiyon'], $h->teshis]);
    $k = $h->konus('Beni işletmeye bağlar mısın', [$hk]);
    esit('konus: aktarma isteği hatırlatmada da çalışır', 'aktar', $k['aksiyon']);

    $am = ['adim' => 'soru', 'tur' => 'alacak', 'alacak_idler' => [7, 8], 'tutar' => '1.250 lira', 'tarih' => 'Salı, 17 Kasım'];
    App\Alacaklar::$guncelleme = null;
    $c = cagir($h, 'hatirlatmaCevap', 'Evet öderim', $am);
    esit('alacak "Evet öderim" → odeme', 'odeme', $c['aksiyon'] ?? null);
    esit('alacaklar odenecek=1', [['odenecek' => 1], [7, 8]], [App\Alacaklar::$guncelleme, App\Alacaklar::$sonIdler]);
    esit('alacak "vadesini uzatabilir miyiz" → aktar', 'aktar', cagir($h, 'hatirlatmaCevap', 'Vadesini uzatabilir miyiz', $am)['aksiyon'] ?? null);
    esit('alacak "hayır ödeyemem" → aktar', 'aktar', cagir($h, 'hatirlatmaCevap', 'Hayır ödeyemem', $am)['aksiyon'] ?? null);
    esit('alacak anlaşılmayan → model', null, cagir($h, 'hatirlatmaCevap', 'Hangi borç', $am));
    icerir('alacak gizli notu: vade konusu → aktar', 'aktar', cagir($h, 'hatirlatmaNotu', $am));
    $gm = cagir($h, 'gecmisMesajlari', [$hk]);
    icerir('hatırlatma notu modele gider', 'randevu_id=901', $gm[1]['content']);
    esit('hatırlatma notu seslendirilmez', 'Tamam.', cagir($h, 'ttsTemizle', 'Tamam. [SİSTEM — Bu arama İŞLETMENİN ...]'));

    // --- STT hizmet adi duzeltme (2026-11-16: "tay masajı" -> "dayı"/"tay makajı" duyuldu, model "yok" dedi)
    $hd = asistan(['salon' => ['ad' => 'Randevumcepte', 'karsilama' => '', 'adres' => '', 'hizmetler' => [
        ['id' => 1, 'ad' => 'Tay Masajı'], ['id' => 2, 'ad' => 'Aromaterapi Masajı'], ['id' => 3, 'ad' => 'Botoks'],
        ['id' => 4, 'ad' => 'Kaş'], ['id' => 5, 'ad' => 'Lazer Epilasyon'], ['id' => 6, 'ad' => 'Saç Kesimi'], ['id' => 7, 'ad' => 'Antistres Masajı'],
    ]]]);
    icerir('"tay makajı" → Tay Masajı notu', "'Tay Masajı'", $hd->hizmetDuzeltmeNotu('O zaman ben bir tay makajı randevu almak istiyorum.'));
    icerir('"dayı masajı" → Tay Masajı notu', "'Tay Masajı'", $hd->hizmetDuzeltmeNotu('Dayı masajı istiyorum'));
    icerir('"thai masajı" → Tay Masajı notu', "'Tay Masajı'", $hd->hizmetDuzeltmeNotu('Thai masajı için randevu'));
    esit('doğru duyulan "tay masajı" → not yok', '', $hd->hizmetDuzeltmeNotu('Tay masajı randevusu almak istiyorum'));
    esit('ekli hali "tay masajına" → not yok', '', $hd->hizmetDuzeltmeNotu('Tay masajına randevu'));
    esit('"botoksu" (ek) → not yok', '', $hd->hizmetDuzeltmeNotu('Botoksu yaptırmak istiyorum'));
    esit('alakasız cümle → not yok', '', $hd->hizmetDuzeltmeNotu('Yarın saat üçe randevu almak istiyorum'));
    esit('"kaç" kısa ad "Kaş" ile eşleşmez', '', $hd->hizmetDuzeltmeNotu('Kaç para tutuyor'));
    esit('"bir masaj" tek başına → not yok', '', $hd->hizmetDuzeltmeNotu('Bir masaj yaptırmak istiyorum'));
    icerir('"saç kesim" → Saç Kesimi değil (zaten doğru) / "sac kesimi" eki', '', $hd->hizmetDuzeltmeNotu('Saç kesimi'));
    $ady = cagir($hd, 'hizmetAdaylari', 'tay makajı');
    esit('hizmetAdaylari: "tay makajı" → ilk aday Tay Masajı (id 1)', [1, 'Tay Masajı'], [$ady[0]['id'] ?? null, $ady[0]['ad'] ?? null]);
    esit('hizmetAdaylari: doğru ad → boş', [], cagir($hd, 'hizmetAdaylari', 'aromaterapi masajı'));
    // --- Hizmet listesi: 5 ornek + kalan kategoriler
    $hl = asistan(['salon' => ['ad' => 'X', 'karsilama' => '', 'adres' => '', 'hizmetler' => [
        ['id' => 1, 'ad' => 'Tay Masajı', 'kategori' => 'Masaj'], ['id' => 2, 'ad' => 'Aromaterapi Masajı', 'kategori' => 'Masaj'],
        ['id' => 3, 'ad' => 'Antistres Masajı', 'kategori' => 'Masaj'], ['id' => 4, 'ad' => 'Manikür', 'kategori' => 'El Ayak Bakımı'],
        ['id' => 5, 'ad' => 'Pedikür', 'kategori' => 'El Ayak Bakımı'], ['id' => 6, 'ad' => 'Botoks', 'kategori' => 'Medikal Estetik'],
        ['id' => 7, 'ad' => 'Lazer Epilasyon', 'kategori' => 'Epilasyon'], ['id' => 8, 'ad' => 'Ağda', 'kategori' => 'Epilasyon'],
        ['id' => 9, 'ad' => 'Cilt Bakımı', 'kategori' => 'Cilt Bakımı'], ['id' => 10, 'ad' => 'Hydrafacial', 'kategori' => 'Cilt Bakımı'],
        ['id' => 11, 'ad' => 'Saç Kesimi', 'kategori' => 'Saç'], ['id' => 12, 'ad' => 'Fön', 'kategori' => 'Saç'],
    ]]]);
    $c = $hl->hizmetListesiCevabi('Hangi hizmetleri veriyorsunuz?');
    echo "     → $c\n";
    icerir('liste: örneklenmeyen kategori sayılır', 'medikal estetik alanında başka seçeneklerimiz de var', $c);
    icermez('liste: örneği okunan kategori tekrar edilmez (masaj)', 'masaj alan', $c);
    icermez('liste: örneği okunan kategori tekrar edilmez (cilt)', 'cilt bakımı alan', $c);
    icerir('liste: kapanış sorusu', 'Hangisiyle ilgileniyorsunuz?', $c);
    esit('liste: en fazla 5 örnek (ilk cümlede 4 virgül/ve)', 5, count(preg_split('/, | ve /u', explode(' gibi', $c)[0])));
    icerir('"Hangi işlemleri yapıyorsunuz" → liste', 'gibi hizmetlerimiz var', (string) $hl->hizmetListesiCevabi('Hangi işlemleri yapıyorsunuz?'));
    icerir('"Hangi işlemlerimiz var" → liste', 'gibi hizmetlerimiz var', (string) $hl->hizmetListesiCevabi('Hangi işlemlerimiz var?'));
    $mc = (string) $hl->hizmetListesiCevabi('Hangi masajlarınız var?');
    echo "     → $mc\n";
    icerir('"Hangi masajlarınız var" → masaj kategorisi', 'masaj hizmetlerimiz var', $mc);
    esit('"Randevu almak istiyorum" → liste değil', null, $hl->hizmetListesiCevabi('Randevu almak istiyorum'));
    esit('"Manikür hizmetiniz var mı" → liste değil', null, $hl->hizmetListesiCevabi('Manikür hizmetiniz var mı'));
    esit('kategorisiz salon da çalışır', true, strpos((string) $hd->hizmetListesiCevabi('Hangi hizmetleriniz var'), 'gibi hizmetlerimiz var') !== false);
    esit('STT ipuçları hizmet adlarını verir', ['Tay Masajı', 'Aromaterapi Masajı'], array_slice($hd->sttIpuclari(), 0, 2));

    // --- 2026-11-17 kayit: "hangi uzmanlarınız var" hizmet listesi okutuyordu
    bolum('Uzman sorusu / ad okunuşu / hızlı veda (2026-11-17 kayıt)');
    esit('"Hangi uzmanlarınız var" → liste değil', null, $hl->hizmetListesiCevabi('Hangi uzmanlarınız var?'));
    esit('"Protez tırnakta hangi uzmanlarınız var" → liste değil', null, $hl->hizmetListesiCevabi('Protez tırnakta hangi uzmanlarınız var?'));
    esit('"Hangi personelleriniz var" → liste değil', null, $hl->hizmetListesiCevabi('Hangi personelleriniz var'));
    icerir('"Hangi masajlarınız var" hâlâ kategori', 'masaj hizmetlerimiz var', (string) $hl->hizmetListesiCevabi('Hangi masajlarınız var?'));
    esit('adSesli: ORBEY → Orbey', 'Orbey', cagir($hl, 'adSesli', 'ORBEY'));
    esit('adSesli: GÜLŞAH İNCE → Gülşah İnce', 'Gülşah İnce', cagir($hl, 'adSesli', 'GÜLŞAH İNCE'));
    esit('adSesli: IŞIL → Işıl', 'Işıl', cagir($hl, 'adSesli', 'IŞIL'));
    esit('adSesli: karışık harf dokunulmaz', 'Elif YILMAZ', cagir($hl, 'adSesli', 'Elif YILMAZ'));
    $sor = [['role' => 'assistant', 'content' => 'Randevu talebiniz oluşturuldu. Başka yardımcı olabileceğim bir konu var mı?']];
    esit('baskaYardimSoruldu: evet', true, cagir($hl, 'baskaYardimSoruldu', $sor));
    esit('baskaYardimSoruldu: onay sorusunda hayır', false, cagir($hl, 'baskaYardimSoruldu', [['role' => 'assistant', 'content' => 'Randevu talebi oluşturayım mı?']]));
    esit('vedaMi: "Yok, teşekkür ederim. Sağ olun."', true, cagir($hl, 'vedaMi', 'Yok, teşekkür ederim. Sağ olun.'));
    esit('vedaMi: "Teşekkürler, bu kadar"', true, cagir($hl, 'vedaMi', 'Teşekkürler, bu kadar'));
    esit('vedaMi: "Yok ama randevumu iptal etmek istiyorum" → hayır', false, cagir($hl, 'vedaMi', 'Yok ama randevumu iptal etmek istiyorum'));
    esit('vedaMi: "Bir de saat kaçta açıksınız" → hayır', false, cagir($hl, 'vedaMi', 'Bir de saat kaçta açıksınız'));
    $v = $hl->konus('Yok, teşekkür ederim. Sağ olun.', $sor);
    esit('konus: hızlı veda aksiyon/bitir', ['veda', true], [$v['aksiyon'], $v['bitir']]);

    bolum('Hitap: kayıt → isim sözlüğü (7.413 ad) → elle liste → müşteri istatistiği');
    esit('sözlük: Ferdi → Ferdi Bey', 'Ferdi Bey', cagir(asistan(['musteriAd' => 'Ferdi Korkmaz']), 'hitap'));
    esit('sözlük: Ayşe Nur → Ayşe Hanım', 'Ayşe Hanım', cagir(asistan(['musteriAd' => 'Ayşe Nur Yılmaz']), 'hitap'));
    esit('sözlük: İSMAİL (büyük harf) → İsmail Bey', 'İsmail Bey', cagir(asistan(['musteriAd' => 'İsmail Kaya']), 'hitap'));
    foreach (['Aksel Kaya', 'Candan Ak', 'Baran Er'] as $iki) esit("iki cinsiyetli: $iki → hitap yok", null, cagir(asistan(['musteriAd' => $iki]), 'hitap'));
    foreach (['Emir Ak' => 'Emir Bey', 'Görkem Can' => 'Görkem Bey', 'Yüksel Er' => 'Yüksel Bey', 'Deniz Kaya' => 'Deniz Hanım', 'Tuna Su' => 'Tuna Hanım', 'Kamer Ak' => 'Kamer Bey', 'İmran Ak' => 'İmran Bey', 'Sunay Ak' => 'Sunay Bey', 'Evren Ak' => 'Evren Bey', 'Mutlu Ak' => 'Mutlu Bey'] as $ad => $bek) esit("işletme düzeltmesi: $ad → $bek", $bek, cagir(asistan(['musteriAd' => $ad]), 'hitap'));
    foreach (['Ümit Ak' => 'Ümit Bey', 'Özgür Can' => 'Özgür Bey', 'Anıl Orbey' => 'Anıl Bey', 'Ayhan Er' => 'Ayhan Bey'] as $ad => $bek) esit("çoğunlukla erkek (%85+): $ad → $bek", $bek, cagir(asistan(['musteriAd' => $ad]), 'hitap'));
    esit('sözlükte yok, elle listede: Ecrin → Ecrin Hanım', 'Ecrin Hanım', cagir(asistan(['musteriAd' => 'Ecrin Su']), 'hitap'));
    DB::$sayim = ['Orbeyhan' => ['e' => 40, 'k' => 0], 'Zirvan' => ['e' => 3, 'k' => 0]];
    esit('hiçbir listede yok, müşteri verisi erkek → Bey', 'Orbeyhan Bey', cagir(asistan(['musteriAd' => 'Orbeyhan Ak']), 'hitap'));
    esit('hiçbir listede yok, az veri → hitap yok', null, cagir(asistan(['musteriAd' => 'Zirvan Ak']), 'hitap'));
    DB::$sayim = ['Aksel' => ['e' => 100, 'k' => 0]];
    esit('iki cinsiyetli ad müşteri verisine bakmaz', null, cagir(asistan(['musteriAd' => 'Aksel Kaya']), 'hitap'));
    esit('kayıt her şeyi ezer (Ferdi ama kayıtta kadın)', 'Ferdi Hanım', cagir(asistan(['musteriAd' => 'Ferdi Korkmaz', 'musteriCinsiyet' => 0]), 'hitap'));
    DB::$sayim = [];
    esit('erkek (1) → Anıl Bey', 'Anıl Bey', cagir(asistan(['musteriCinsiyet' => 1]), 'hitap'));
    esit('kadın (0) → Anıl Hanım', 'Anıl Hanım', cagir(asistan(['musteriCinsiyet' => 0]), 'hitap'));
    esit('belirtilmemiş + iki cinsiyetli ad → hitap yok', null, cagir(asistan(['musteriAd' => 'Aksel Kaya']), 'hitap'));
    esit('kayıtsız müşteri → hitap yok', null, cagir(asistan(['musteriAd' => null, 'musteriId' => null, 'musteriCinsiyet' => 1]), 'hitap'));
    icerir('hızlı veda hitaplı', 'Rica ederiz Anıl Bey,', asistan(['musteriCinsiyet' => 1])->konus('Yok teşekkürler', $sor)['cevap']);
    icerir('hızlı veda cinsiyet yoksa hitapsız', 'Rica ederiz, bizi', asistan(['musteriAd' => 'Aksel Kaya'])->konus('Yok teşekkürler', $sor)['cevap']);
    icerir('prompt: cinsiyet yoksa tahmin yasağı', 'isimden cinsiyet TAHMİN ETME', cagir(asistan(['musteriAd' => 'Aksel Kaya']), 'sistemPromptu'));
    icerir('prompt: cinsiyet varsa hitap', '"Anıl Bey" diye hitap et', cagir(asistan(['musteriCinsiyet' => 1]), 'sistemPromptu'));

    echo "\n\033[1mSONUÇ: $gecen geçti, $kalan kaldı\033[0m\n";
    if ($kalan) { echo "Kalanlar:\n - " . implode("\n - ", $hatalar) . "\n"; exit(1); }
}
