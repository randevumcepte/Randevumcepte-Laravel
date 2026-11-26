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
namespace App\Services { class IfadeOgrenme { static $sayac = []; static function say($s, $k, $kat) { self::$sayac[] = $kat; } static function __callStatic($m, $a) {} }
    class SantralPaket { static $kapali = []; static function asistanKapali(int $id): bool { return !empty(self::$kapali[$id]); } } }
namespace App {
    class SahteKoleksiyon { public $d; function __construct($d) { $this->d = $d; } function get($k = null) { return $k === null ? $this : ($this->d[$k] ?? null); } function keyBy($k) { $o = []; foreach ($this->d as $r) $o[$r->$k] = $r; return new self($o); } function isEmpty() { return !$this->d; } }
    class SalonCalismaSaatleri { static $satirlar = []; static function where() { return new SahteKoleksiyon(self::$satirlar); } }
    // Hizmet-personel eslestirmesi: test verisi yok -> isletme eslestirme kullanmiyor (herkes verir)
    class PersonelHizmetler { static function __callStatic($m, $a) { return new self; } function __call($m, $a) { return $m === "exists" ? false : ($m === "pluck" ? new PhKoleksiyon : $this); } }
    class PhKoleksiyon { function __call($m, $a) { return $m === "all" ? [] : $this; } }
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
    require_once __DIR__ . '/../app/Services/WaBedavaKatman.php';

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
        // kayit #78: STT saati sozcukle yazinca ("öğleden sonra iki")
        ['Perşembe öğleden sonra iki', '2026-11-19', '14:00'],
        ['Perşembe öğleden sonra ikide', '2026-11-19', '14:00'],
        ['Yarın saat üçte', '2026-11-16', '15:00'],
        ['Cuma on dört buçukta', '2026-11-20', '14:30'],
        ['Bir randevu almak istiyorum', null, null],
        ['Onda var mı', null, null],
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
    esit('fiyat → telefonda aktarılmaz (kayıt 239)', null, $d['aksiyon']);
    icerir('fiyat → işletme iletecek + randevuya devam', 'Fiyat bilgisini işletmemiz size ayrıca iletecek', $d['cevap']);
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
    icerir('iptal (iki randevu) → "Birinci randevunuz"', 'Birinci randevunuz Salı', $d['cevap']);
    icerir('iptal (iki randevu) → "İkinci randevunuz"', 'İkinci randevunuz Cuma', $d['cevap']);
    icermez('iptal → "1." numara okunmaz', '1. ', $d['cevap']);
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
    esit('SSS kelimesi geçse de fiyat sorusu → fiyat yolu', 'fiyat', $as->teshis);

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
    $ah->konus('Saç kesimi ne kadar', []); esit('fiyat sorusu süreye düşmez → fiyat yolu', 'fiyat', $ah->teshis);

    bolum('Bekletme cümlesi temizliği');
    foreach ([
        'Bir moment, sizi biraz bekleteceğim.' => '',
        'Bir moment lütfen, randevularınızı kontrol ediyorum.' => '',
        'Hemen bakıyorum. Pazartesi 14:00\'te randevunuz var, iptal edeyim mi?' => 'Pazartesi 14:00\'te randevunuz var, iptal edeyim mi?',
        'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?' => 'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?',
    ] as $g => $b) esit("\"$g\"", $b, cagir($a, 'bekletmeTemizle', $g));

    bolum('Modelin iç sesi okunmaz (canlı 2026-11-17)');
    foreach ([
        'Müşteri 13.40 dedi; seçeneklerde 13:40 var, tarih ise önceki konuşmadaki Çarşamba, 25 Kasım olarak kalmalı. Onay soruyorum. Çarşamba, 25 Kasım saat 13:40\'ta Gülşah Hanım ile randevu talebi oluşturayım mı?'
            => 'Çarşamba, 25 Kasım saat 13:40\'ta Gülşah Hanım ile randevu talebi oluşturayım mı?',
        'Müşterinin iki paketi olduğunu söylemiştim. Hangi paketinizden randevu oluşturalım?' => 'Hangi paketinizden randevu oluşturalım?',
        'musait_saatler sonucuna göre 14:00 uygun. Saat 14:00 olur mu?' => 'Saat 14:00 olur mu?',
        'Müşterimiz olduğunuz için teşekkürler. Hangi gün uygun?' => 'Müşterimiz olduğunuz için teşekkürler. Hangi gün uygun?',
        'Saat 13.40 uygun, oluşturayım mı?' => 'Saat 13.40 uygun, oluşturayım mı?',
    ] as $g => $b) esit("\"" . mb_substr($g, 0, 50) . "…\"", $b, cagir($a, 'icSesTemizle', $g));

    bolum('Cümle başı "Peki." / hitap atılır (canlı 2026-11-18 kayıt 166)');
    $ab = asistan(['musteriAd' => 'Anıl Orbey', 'musteriCinsiyet' => 1]);
    foreach ([
        'Anıl Bey, hangi hizmet için randevu almak istiyorsunuz?' => 'Hangi hizmet için randevu almak istiyorsunuz?',
        'Peki. Protez tırnak için hangi gün uygun?' => 'Protez tırnak için hangi gün uygun?',
        'Peki, Anıl Bey, iptal ediyorum.' => 'İptal ediyorum.',
        'Rica ederiz Anıl Bey. İyi günler dileriz.' => 'Rica ederiz Anıl Bey. İyi günler dileriz.',
        'Anıl Bey, bizi aradığınız için teşekkür ederiz.' => 'Anıl Bey, bizi aradığınız için teşekkür ederiz.',
        'Pekişmiş bir randevunuz var.' => 'Pekişmiş bir randevunuz var.',
    ] as $g => $b) esit("\"$g\"", $b, cagir($ab, 'dogalBaslangic', $g));

    bolum('Karşılama "Ben Ayşe" + doğal kapanış sorusu (kayıt 172)');
    $ay = asistan(['musteriAd' => 'Anıl Orbey', 'musteriCinsiyet' => 1, '_asistanAdi' => 'Ayşe', 'salon' => ['ad' => 'Randevumcepte', 'karsilama' => "Randevumcepte'ye", 'hizmetler' => []], '_kampanyalar' => []]);
    esit('karşılama tamamen sabit (kayıt 242)', "Merhaba, Randevumcepte'ye hoş geldiniz. Ben Ayşe, size nasıl yardımcı olabilirim?", $ay->karsilama());
    esit('karşılama sabit girişle başlar (köprü önceden çalar)', 0, strpos($ay->karsilama(), $ay->karsilamaGiris()));
    icerir('kayıtsız → aynı sabit karşılama', 'hoş geldiniz. Ben Ayşe, size nasıl yardımcı olabilirim?', asistan(['musteriAd' => null, 'musteriId' => null, '_asistanAdi' => 'Ayşe', '_kampanyalar' => []])->karsilama());
    icerir('ad tanımsız (erkek ses) → eski kalıp', 'Ben yapay zeka asistanınızım.', asistan(['_asistanAdi' => '', '_kampanyalar' => []])->karsilama());
    icerir('prompt: adın Ayşe', 'Adın Ayşe', cagir($ay, 'sistemPromptu'));
    $kal = 'Randevunuz iptal edildi. Başka yardımcı olabileceğim bir konu var mı?';
    esit('kapanış 1. kez', 'Randevunuz iptal edildi. Sizin için yapabileceğim başka bir şey var mı?', $ay->kapanisDogallastir($kal, []));
    $g1 = [['role' => 'assistant', 'content' => 'Sizin için yapabileceğim başka bir şey var mı?']];
    esit('kapanış 2. kez (hitaplı)', 'Randevunuz iptal edildi. Başka bir isteğiniz var mı, Anıl Bey?', $ay->kapanisDogallastir($kal, $g1));
    $g2 = array_merge($g1, [['role' => 'assistant', 'content' => 'Başka bir isteğiniz var mı, Anıl Bey?']]);
    esit('kapanış 3. kez', 'Randevunuz iptal edildi. Başka merak ettiğiniz bir şey var mı?', $ay->kapanisDogallastir($kal, $g2));
    foreach (['Sizin için yapabileceğim başka bir şey var mı?', 'Başka bir isteğiniz var mı, Anıl Bey?', 'Başka merak ettiğiniz bir şey var mı?'] as $v) {
        esit("hızlı veda yine çalışır: \"$v\"", true, cagir($ay, 'baskaYardimSoruldu', [['role' => 'assistant', 'content' => $v]]));
    }

    bolum('Yanlış duyulan hizmet kelimesi + uzman listesi + STT ipucu (canlı 2026-11-18 22:59)');
    $hz = ['hizmetler' => [['id' => 1, 'ad' => 'El Protez Tırnak'], ['id' => 2, 'ad' => 'Ayak Protez Tırnak'], ['id' => 3, 'ad' => 'Kirpik Lifting'], ['id' => 4, 'ad' => 'Kaş Alma']]];
    $pz = asistan(['salon' => ['ad' => 'X', 'karsilama' => "X'e"] + $hz]);
    foreach (['protest' => 'Protez tırnak', 'prot' => 'Protez tırnak', 'el Protestanlık' => 'Protez tırnak', 'protest tırnak' => 'Protez tırnak', 'kirpik liftink' => 'Kirpik lifting'] as $g => $b) {
        $kd = cagir($pz, 'kelimeDuzeltme', $g);
        esit("kelime düzeltme \"$g\"", $b, $kd['ortak'] ?? null);
    }
    esit('"protest" → iki aday', 2, count(cagir($pz, 'kelimeDuzeltme', 'protest')['adaylar'] ?? []));
    esit('"Olmak istiyorum" → düzeltme yok', null, cagir($pz, 'kelimeDuzeltme', 'Olmak istiyorum'));
    esit('"kirpk" → Kirpik lifting', 'Kirpik lifting', cagir($pz, 'kelimeDuzeltme', 'kirpk')['ortak'] ?? null);
    esit('soru eki: tırnak → mı', 'mı', cagir($pz, 'soruEki', 'Protez tırnak'));
    esit('soru eki: lifting → mi', 'mi', cagir($pz, 'soruEki', 'Kirpik lifting'));
    esit('soru eki: boyun → mu', 'mu', cagir($pz, 'soruEki', 'Boyun'));
    $ip = cagir($pz, 'sttIpuclari');
    icerir('STT ipucu: tam ad', 'El Protez Tırnak', implode('|', $ip));
    esit('STT ipucu: tek kelime "protez"', true, in_array('protez', $ip, true));
    esit('STT ipucu: kısa kelime ("el", "kaş") yok', false, in_array('el', $ip, true) || in_array('kaş', $ip, true));
    $uz = 'Tabii, El protez tırnak için uygun uzmanlarımız arasında Sümeyra Hanım, Melis Hanım, Fatma Hanım ve Berfin Hanım var. Tercih ettiğiniz bir uzmanımız var mı, yoksa fark etmez mi?';
    esit('uzman listesi kesilir', 'Tercih ettiğiniz bir uzmanımız var mı, yoksa fark etmez mi?', cagir($pz, 'uzmanListesiKes', $uz, 'el protez'));
    esit('müşteri "kimler var" dediyse liste kalır', $uz, cagir($pz, 'uzmanListesiKes', $uz, 'kimler var'));
    $tek = 'Saat 14:00 uzmanımız Sümeyra Hanım ile uygun. Bu saat olur mu?';
    esit('tercih sorusu yoksa dokunulmaz', $tek, cagir($pz, 'uzmanListesiKes', $tek, 'yarın'));
    icerir('prompt: anlamadığında kısa soru', 'Kusura bakmayın, tam duyamadım. Hangi hizmet için arıyorsunuz?', cagir($pz, 'sistemPromptu'));

    bolum('Kayıt 176: önce söylenen tarih/saat unutulmaz, doğru söylenen hizmete teyit sorulmaz');
    $g176 = [
        ['role' => 'assistant', 'content' => 'Merhaba, size nasıl yardımcı olabilirim?'],
        ['role' => 'user', 'content' => 'Yarın saat 18.30 Melis Hanım boş mu?'],
        ['role' => 'assistant', 'content' => 'Hangi hizmet için randevu almak istiyorsunuz?'],
    ];
    $zn = cagir($pz, 'oncekiZamanNotu', 'Jel güçlendirme', $g176);
    icerir('önceki saat hatırlatılır', 'saat=18:30', $zn);
    icerir('tarih tekrar sorulmaz notu', 'TEKRAR SORMA', $zn);
    esit('bu cümlede zaman varsa not yok', '', cagir($pz, 'oncekiZamanNotu', 'perşembe 14', $g176));
    $g176b = array_merge($g176, [['role' => 'user', 'content' => 'Lazer'], ['role' => 'assistant', 'content' => 'Tamamdır, randevu talebinizi oluşturdum.']]);
    esit('randevu oluştuysa eski saat hatırlatılmaz', '', cagir($pz, 'oncekiZamanNotu', 'bir randevu daha', $g176b));
    $kj = cagir(asistan(['salon' => ['ad' => 'X', 'hizmetler' => [['id' => 7, 'ad' => 'Jel Güçlendirmesi']]]]), 'kelimeDuzeltme', 'Jel güçlendirme');
    esit('kelime düzeltme id\'li seçenek verir', [['id' => 7, 'ad' => 'Jel Güçlendirmesi']], $kj['secenekler'] ?? null);

    bolum('Kayıt 180: randevu oluştuktan sonra gelen boş onay modele sorulmaz');
    $g180 = [['role' => 'user', 'content' => 'Evet'], ['role' => 'assistant', 'content' => 'Tamamdır, randevu talebinizi oluşturdum. Sizin için yapabileceğim başka bir şey var mı?']];
    foreach (['Evet oluşturdum.' => true, 'Evet, oluşturalım' => true, 'Evet' => true, 'Tamam lütfen' => true, 'Evet bir de kaş randevusu' => false, 'Yarın da olur mu' => false] as $m => $b) {
        esit("boş onay? \"$m\"", $b, cagir($pz, 'isBittiOnayiMi', $m, $g180));
    }
    esit('randevu yeni oluşmadıysa boş onay sayılmaz', false, cagir($pz, 'isBittiOnayiMi', 'Evet', [['role' => 'assistant', 'content' => 'Saat 14 olur mu?']]));
    $d = $pz->konus('Evet oluşturdum.', $g180);
    esit('onay tekrarı → modelsiz kısa cevap', 'Randevunuz oluşturuldu, merak etmeyin. Sizin için yapabileceğim başka bir şey var mı?', $pz->kapanisDogallastir($d['cevap'], []));
    esit('yalın "evet" → dinliyorum', 'Tabii, sizi dinliyorum.', $pz->konus('Evet', $g180)['cevap']);

    bolum('Kayıt 178: üst üste iki kez anlaşılamazsa uzatmadan aktar');
    $anla = 'Kusura bakmayın, tam duyamadım. Hangi hizmet için arıyorsunuz?';
    esit('ilk anlamama aktarılmaz', false, $pz->ikinciAnlamama($anla, [['role' => 'assistant', 'content' => 'Merhaba, size nasıl yardımcı olabilirim?'], ['role' => 'user', 'content' => 'şey']]));
    esit('ikinci anlamama aktarılır', true, $pz->ikinciAnlamama($anla, [['role' => 'assistant', 'content' => $anla], ['role' => 'user', 'content' => 'hmm']]));
    // canli 2026-11-23: teyit sorusu anlamama SAYILMAZ (musteri teyide cevap veremeden aktariliyordu)
    esit('anlamama + teyit → aktarılmaz', false, $pz->ikinciAnlamama('Kadıköy mü demek istediniz efendim? Tam anlayamadım.', [['role' => 'assistant', 'content' => 'Sizi tam anlayamadım. Tekrar söyler misiniz?'], ['role' => 'user', 'content' => 'kadıköy']]));
    // canli 2026-11-23: anlasilamayan musteri AKTARILMAZ; 2. kez kapali soru, 3. kez WhatsApp/geri arama (controller)
    $anlG = [['role' => 'assistant', 'content' => 'Merhaba, Ola Nail\'e hoş geldiniz. Ben Ayşe, size nasıl yardımcı olabilirim?'], ['role' => 'user', 'content' => 'şey'],
             ['role' => 'assistant', 'content' => 'Kusura bakmayın, tam duyamadım. Hangi hizmet için arıyorsunuz?'], ['role' => 'user', 'content' => 'hmm']];
    esit('anlama serisi: anlamama değil → 0', 0, $pz->anlamamaSerisi('Hangi gün uygun?', $anlG));
    esit('anlama serisi: ikinci', 2, $pz->anlamamaSerisi('Tam anlayamadım, tekrar söyler misiniz?', $anlG));
    $k2 = $pz->anlamamaKurtarmaCumlesi($anlG);
    icerir('2. anlamama: hizmetler sayılır', 'Size birkaç hizmetimizi sayayım:', $k2);
    esit('2. anlamama cümlesi de anlamama sayılır (3. seri)', 3, $pz->anlamamaSerisi('Kusura bakmayın, tam duyamadım.', array_merge($anlG, [['role' => 'assistant', 'content' => $k2], ['role' => 'user', 'content' => 'x']])));
    $anlZ = [['role' => 'assistant', 'content' => 'Pedikür hizmetimiz için hangi gün ve saat uygun?'], ['role' => 'user', 'content' => 'şey'],
             ['role' => 'assistant', 'content' => 'Kusura bakmayın, tam duyamadım.'], ['role' => 'user', 'content' => 'hmm']];
    icerir('2. anlamama: gün sorusunda örnekli tekrar', 'yarın saat üç', $pz->anlamamaKurtarmaCumlesi($anlZ));
    esit('teyit + anlamama → aktarılmaz', false, $pz->ikinciAnlamama('Kusura bakmayın, tam duyamadım. Hangi hizmet için arıyorsunuz?', [['role' => 'assistant', 'content' => 'Pedikür mü demek istediniz efendim?'], ['role' => 'user', 'content' => 'hmm']]));

    bolum('Anlaşılamama merdiveni: 1 tekrar → 2 kapalı soru → 3 evet/hayır → 4 salona aktar (WhatsApp yok)');
    $anlamaC = 'Kusura bakmayın, tam duyamadım. Tekrar söyler misiniz?';
    $sv = function (array $seviyeler) {
        $g = [];
        foreach ($seviyeler as $s) { $g[] = ['role' => 'assistant', 'content' => 'x', 'seviye' => $s]; $g[] = ['role' => 'user', 'content' => 'hmm']; }
        return $g;
    };
    esit('ilk anlamama → 1', 1, $pz->cevapSeviyesi($anlamaC, $sv([0])));
    esit('ikinci → 2', 2, $pz->cevapSeviyesi($anlamaC, $sv([0, 1])));
    esit('üçüncü → 3', 3, $pz->cevapSeviyesi($anlamaC, $sv([1, 2])));
    esit('teklif sonrası anlamama → 4', 4, $pz->cevapSeviyesi($anlamaC, $sv([2, 3])));
    esit('zorlukta evet/hayır teklifi → 3', 3, $pz->cevapSeviyesi('Pedikür için yarın 14:00 uygun mu? Evet ya da hayır demeniz yeterli.', $sv([1, 2])));
    esit('"hayır" sonrası yeni teklif 3\'te kalır', 3, $pz->cevapSeviyesi('Peki, yarın 15:00 uygun mu? Evet ya da hayır demeniz yeterli.', $sv([2, 3])));
    esit('anlaşılan cevap sıfırlar', 0, $pz->cevapSeviyesi('Hangi gün sizin için uygun?', $sv([1, 2])));
    esit('teyit anlamama sayılmaz', 0, $pz->cevapSeviyesi('Kadıköy mü demek istediniz efendim? Tam anlayamadım.', $sv([1])));
    esit('eski kayıt (seviye yok): anlamama = 1', 2, $pz->cevapSeviyesi($anlamaC, [['role' => 'assistant', 'content' => $anlamaC], ['role' => 'user', 'content' => 'x']]));
    esit('zorluk notu: seviye 1\'de yok', '', $pz->zorlukNotu($sv([1])));
    icerir('zorluk notu: seviye 2\'de evet/hayır ister', 'Evet ya da hayır demeniz yeterli', $pz->zorlukNotu($sv([1, 2])));
    $tk = $pz->anlamamaTeklifCumlesi($sv([1, 2]));
    icerir('kod teklifi: evet/hayır', 'Evet ya da hayır demeniz yeterli.', $tk);
    esit('kod teklifi anlamama sayılmaz', false, $pz->anlamadiCevabi($tk));
    icerir('hizmet belliyse en yakın saat teklifi', 'en yakın boş saati ayarlayayım mı',
        $pz->anlamamaTeklifCumlesi([['role' => 'assistant', 'content' => 'x', 'baglam' => 'musait_saatler hizmet_id=4 seçenekler: ...', 'seviye' => 2]]));
    bolum('İşin içinden çıkamama: sinir / döngü');
    foreach (['Anlamıyorsun beni' => true, 'Robot musunuz siz?' => true, 'Of yeter ya' => true, 'Makineyle konuşmak istemiyorum' => true,
              'Sizi anlamadım, tekrar söyler misiniz?' => false, 'Pedikür istiyorum' => false] as $m => $b) {
        esit("sinirli? \"$m\"", $b, $pz->sinirliMi($m));
    }
    esit('sinirli müşteri → aktar', 'aktar', $pz->konus('Anlamıyorsun beni ya', [['role' => 'assistant', 'content' => 'Hangi hizmet için arıyorsunuz?']])['aksiyon']);
    $soru = 'Protez tırnak için hangi gün gelmek istersiniz?';
    $dg = [['role' => 'assistant', 'content' => $soru], ['role' => 'user', 'content' => 'a'], ['role' => 'assistant', 'content' => 'Protez tırnak için hangi gün gelmek istersiniz efendim?'], ['role' => 'user', 'content' => 'b']];
    esit('aynı soru 3. kez → döngü', true, $pz->donguMu($soru, $dg));
    esit('aynı soru 2. kez → döngü değil', false, $pz->donguMu($soru, array_slice($dg, 0, 2)));
    esit('farklı soru → döngü değil', false, $pz->donguMu('Saat kaçta uygun olursunuz?', $dg));
    $st = 'Yarın 14:00 Zehra Hanım ya da 15:00 Ayşe Hanım uygun, hangisini istersiniz?';
    esit('saat teklifleri döngü sayılmaz', false, $pz->donguMu($st, [['role' => 'assistant', 'content' => str_replace('14', '16', $st)], ['role' => 'user', 'content' => 'a'], ['role' => 'assistant', 'content' => str_replace('14', '17', $st)], ['role' => 'user', 'content' => 'b']]));
    $ola = asistan(['salon' => ['ad' => 'Ola Nail', 'hizmetler' => [
        ['id' => 1, 'ad' => 'Kalıcı Oje', 'kategori' => 'Tırnak'], ['id' => 2, 'ad' => 'Jel Tırnak', 'kategori' => 'Tırnak'],
        ['id' => 3, 'ad' => 'İpek Kirpik', 'kategori' => 'Kirpik'], ['id' => 4, 'ad' => 'El Protez Tırnak', 'kategori' => 'Tırnak'],
        ['id' => 5, 'ad' => 'Fullset', 'kategori' => 'Tırnak']]]]);
    $u = $ola->uydurmaHizmetKes('Kusura bakmayın, tam eşleştiremedim. Kalıcı oje, jel tırnak, ipek kirpik ya da cilt bakımı gibi hizmetlerimizden hangisini istersiniz?');
    esit('uydurma "cilt bakımı" çıkar', false, mb_stripos($u, 'cilt') !== false);
    icerir('gerçek örneklerle değişir + soru korunur', 'Hangisiyle ilgileniyorsunuz?', $u);
    icerir('ilk cümle korunur', 'Kusura bakmayın, tam eşleştiremedim.', $u);
    $u2 = $ola->uydurmaHizmetKes('Hangi hizmetle ilgilendiğinizi öğrenirsem bakabilirim. Örneğin kalıcı oje, jel tırnak ya da cist bakımı gibi hizmetlerimiz var. Hangisi için randevu almak istersiniz?');
    esit('"Örneğin ..." listesinde de uydurma çıkar', false, mb_stripos($u2, 'cist') !== false);
    $dogru = 'Tırnak için jel tırnak, fullset ya da kalıcı oje gibi bir hizmet mi istersiniz?';
    esit('listede olan hizmetler dokunulmaz', $dogru, $ola->uydurmaHizmetKes($dogru));
    bolum('Kayıt 242: "protest tırnak" sessizce "protez tırnak" olur (teyit döngüsü yok)');
    $p242 = asistan(['salon' => ['ad' => 'Ola Nail', 'hizmetler' => [['id' => 1, 'ad' => 'Protez Tırnak'], ['id' => 2, 'ad' => 'Ayak Protez Tırnak'], ['id' => 3, 'ad' => 'Kalıcı Oje']]]]);
    esit('"Protest tırnak." → "Protez tırnak."', 'Protez tırnak.', $p242->sttDuzelt('Protest tırnak.'));
    esit('"protestırnak" bitişik dokunulmaz (bağlam yok)', 'protestırnak', $p242->sttDuzelt('protestırnak'));
    esit('bağlam yoksa düzeltilmez', 'Dün protestoya gittim', $p242->sttDuzelt('Dün protestoya gittim'));
    esit('doğru kelime aynen kalır', 'protez tırnak istiyorum', $p242->sttDuzelt('protez tırnak istiyorum'));
    esit('"ayak protest tırnak" → "ayak protez tırnak"', 'ayak protez tırnak', $p242->sttDuzelt('ayak protest tırnak'));

    bolum('Kayıt 239: randevu onayı ortasında fiyat sorusu aktarılmaz, soru tekrar sorulur');
    $f239 = asistan(['salon' => ['ad' => 'Ola Nail', 'hizmetler' => [
        ['id' => 1, 'ad' => 'Kalıcı Oje', 'fiyat' => 400, 'kategori' => 'Tırnak'], ['id' => 2, 'ad' => 'Jel Tırnak', 'fiyat' => 0, 'kategori' => 'Tırnak']]]]);
    $onay = 'Salı 24 Kasım saat 17\'de uzmanımız Sümeyra Hanım ile kalıcı oje için randevu talebi oluşturayım mı?';
    $g239 = [['role' => 'user', 'content' => 'Salı saat 5'], ['role' => 'assistant', 'content' => $onay]];
    $f239->santralFiyatSoyle = true;
    $d = $f239->konus('Fiyat ne kadar?', $g239);
    esit('aktarma yok', null, $d['aksiyon']);
    icerir('kayıtlı fiyat söylenir', 'Kalıcı Oje fiyatımız 400 lira.', $d['cevap']);
    icerir('onay sorusu aynen tekrar', $onay, $d['cevap']);
    $f239->santralFiyatSoyle = false;
    $d = $f239->konus('Fiyat ne kadar?', $g239);
    esit('ayar kapalı: yine aktarma yok', null, $d['aksiyon']);
    icerir('ayar kapalı: işletme iletecek', 'Fiyat bilgisini işletmemiz size ayrıca iletecek', $d['cevap']);
    esit('ayar kapalı: işletmeye fiyat notu', 'Fiyat sordu: Kalıcı Oje', $d['fiyat_notu'] ?? null);
    $f239->santralFiyatSoyle = true;
    $d = $f239->konus('Jel tırnak kaç lira', [['role' => 'assistant', 'content' => 'Size nasıl yardımcı olabilirim?']]);
    esit('fiyatı tanımsız hizmet: not', 'Fiyat sordu: Jel Tırnak', $d['fiyat_notu'] ?? null);
    icerir('randevu teklifiyle devam', 'randevunuzu hemen oluşturayım', $d['cevap']);
    esit('açık aktarma isteği hâlâ aktarılır', 'aktar', $f239->konus('Beni yetkiliye bağlar mısınız', $g239)['aksiyon']);
    $p = cagir($f239, 'sistemPromptu'); icerir('fiyat açık: söylenen fiyat doğru, düzeltme yok', 'DOĞRUDUR', $p); icerir('fiyat açık: liste fiyatı promptta', '400 TL', $p);
    $f239->santralFiyatSoyle = false; $p = cagir($f239, 'sistemPromptu'); esit('fiyat kapalı: promptta fiyat yok', false, strpos($p, '400 TL') !== false);

    esit('anladıysa aktarılmaz', false, $pz->ikinciAnlamama('Hangi gün sizin için uygun?', [['role' => 'assistant', 'content' => $anla], ['role' => 'user', 'content' => 'manikür']]));

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
    // 2026-11-17: "Sayın Anıl Orbey" yerine "Merhaba Anıl Bey" (cinsiyet biliniyorsa); belirsizse "Sayın Ad Soyad"
    icermez('kayıtlı → karşılamada hitap yok (sabit)', 'Anıl Bey', asistan(['musteriCinsiyet' => 1])->karsilama());
    
    
    icermez('kayıtlı → soyadı söylenmez', 'Orbey', asistan(['musteriCinsiyet' => 1])->karsilama());
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
    icermez('kayıtsız → karşılamada ad sorulmaz (istekten sonra)', 'adınızı', $ky->karsilama());
    icerir('kayıtsız → prompt ad soyadı istekten sonra ister', 'Karşılamada ad SORULMADI', cagir($ky, 'sistemPromptu'));
    $kg = asistan(['musteriAd' => null, 'musteriId' => null, 'telefon' => null]);
    icerir('gizli numara → prompt telefonu da ister', 've cep telefonunu iste', cagir($kg, 'sistemPromptu'));

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
    esit('paketlerHizmet: sayılı paket', [['adisyon_paket_id' => 1, 'paket_adi' => 'Lazer', 'hizmet' => 'Lazer Epilasyon', 'hizmet_id' => 9, 'sinirsiz' => false, 'toplam_seans' => 10, 'kullanilan_seans' => 6, 'kalan_seans' => 4]], cagir($pt, 'paketlerHizmet', 9));
    esit('paketlerHizmet: sınırsız pakette sayı yok', [['adisyon_paket_id' => 1, 'paket_adi' => 'Lazer', 'hizmet' => 'Cilt Bakımı', 'hizmet_id' => 10, 'sinirsiz' => true]], cagir($pt, 'paketlerHizmet', 10));
    esit('paketlerHizmet: pakette olmayan hizmet → boş', [], cagir($pt, 'paketlerHizmet', 99));
    icerir('bağlamda adisyon_paket_id', 'Paket: Lazer (adisyon_paket_id 1)', cagir($pt, 'paketSatirlari'));

    bolum('Paket tespiti (müşteri paketten bahsetmese de)');
    $pc = asistan(['_paketler' => [
        ['adisyon_paket_id' => 11, 'paket_adi' => 'Lazer 10', 'hizmetler' => [['hizmet_id' => 9, 'ad' => 'Lazer Epilasyon', 'kalan_seans' => 4, 'toplam_seans' => 10, 'kullanilan_seans' => 6, 'personeller' => []]]],
        ['adisyon_paket_id' => 12, 'paket_adi' => 'Yaz Kampanyası', 'hizmetler' => [['hizmet_id' => 9, 'ad' => 'Lazer Epilasyon', 'kalan_seans' => 2, 'toplam_seans' => 5, 'kullanilan_seans' => 3, 'personeller' => []]]],
        ['adisyon_paket_id' => 13, 'paket_adi' => 'Cilt', 'hizmetler' => [['hizmet_id' => 10, 'ad' => 'Cilt Bakımı', 'kalan_seans' => 3, 'toplam_seans' => 3, 'kullanilan_seans' => 0, 'personeller' => []]]]]]);
    $n = cagir($pc, 'paketNotu', 'Yarın lazer epilasyon randevusu almak istiyorum', []);
    icerir('çoklu paket: iki paket sayılır', "'Lazer 10' (adisyon_paket_id 11, toplam 10, kullanılan 6, kalan 4 seans); 'Yaz Kampanyası' (adisyon_paket_id 12", $n);
    icerir('çoklu paket: hangi paket sorusu', 'Hangi paketinizden randevu oluşturalım?', $n);
    $n1 = cagir($pc, 'paketNotu', 'cilt bakımı için randevu', []);
    icerir('tek paket: oluşturalım mı sorusu', 'Randevuyu bu paketinizden oluşturmak ister misiniz?', $n1);
    icermez('tek pakette diğer paketler yok', 'Lazer 10', $n1);
    esit('paketsiz hizmet → not yok', '', cagir($pc, 'paketNotu', 'saç kesimi istiyorum', []));
    esit('daha önce sorulduysa tekrar yok', '', cagir($pc, 'paketNotu', 'cilt bakımı evet', [['role' => 'assistant', 'content' => 'Cilt paketinizde 3 seans kalmış, bu paketten oluşturalım mı?']]));
    esit('paketi olmayan müşteri → not yok', '', cagir(asistan(['_paketler' => []]), 'paketNotu', 'lazer epilasyon', []));
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
    $z = cagir($zr, 'zorlaRandevu', [['role' => 'assistant', 'content' => "Perşembe 11:00 Gülşah ile Saç Kesimi oluşturayım mı?\n[SİSTEM — bu turdaki araç sonuçları, müşteri duymadı: musait_saatler hizmet_id=5 seçenekler: 2026-11-19T11:00 personel_id=21 (Gülşah)]"], ['role' => 'user', 'content' => 'Evet']], 'k');
    esit('tool_choice ile randevu_olustur zorlanır', ['type' => 'tool', 'name' => 'randevu_olustur'], $zr->sonEk['tool_choice'] ?? null);
    esit('modelin verdiği id\'ler kullanılır', 21, $zr->sonGirdi['personel_id'] ?? null);
    esit('aksiyon randevu', 'randevu', $z[1]);
    esit('randevu id', 555, $z[2]);
    icerir('kısa talep cümlesi (gün/saat/uzman tekrar edilmez, kayıt 172)', 'Tamamdır, randevu talebinizi oluşturdum', $z[0]);
    icerir('bilgilendirme ayarı açık', '; bilgilendirme mesajını ve yol tarifini telefonunuza gönderdim. Onaylandığında size haber vereceğiz.', $z[0]);
    $zr->sahteSonuc = ['ok' => false, 'hata' => 'Bu saat çakışıyor.'];
    $z = cagir($zr, 'zorlaRandevu', [['role' => 'assistant', 'content' => "Perşembe 11:00 Gülşah ile Saç Kesimi oluşturayım mı?\n[SİSTEM — bu turdaki araç sonuçları, müşteri duymadı: musait_saatler hizmet_id=5 seçenekler: 2026-11-19T11:00 personel_id=21 (Gülşah)]"], ['role' => 'user', 'content' => 'Evet']], 'k');
    esit('kayıt başarısız → aksiyon yok', null, $z[1]);
    icerir('kayıt başarısız → dürüst cümle', 'kaydedemedim', $z[0]);
    icerir('çakışma → yeni saat önerir', 'az önce doldu', $z[0]);
    // 2026-11-22 canli: hatirlatma hizmet secilmeden "10:00 oluşturayım mı" dedi -> model hizmeti tahmin etti
    $zr->sonGirdi = null;
    $z = cagir($zr, 'zorlaRandevu', [['role' => 'assistant', 'content' => "Yarın 10:00'da randevunuzu oluşturayım mı?"], ['role' => 'user', 'content' => 'Evet']], 'k');
    esit('yazışmada geçmeyen hizmet → randevu açılmaz', null, $zr->sonGirdi);
    icerir('yazışmada geçmeyen hizmet → hizmet sorulur', 'Hangi hizmet', $z[0]);
    $zr->sahteYanit = ['content' => [['type' => 'tool_use', 'id' => 'x', 'name' => 'randevu_olustur', 'input' => ['tarih_saat' => '2026-11-19T11:00', 'personel_id' => 21]]]];
    $zr->sahteSonuc = ['ok' => false, 'hata' => 'tarih_saat, hizmet_id ve personel_id gerekli (musait_saatler seçeneğinden al).'];
    $z = cagir($zr, 'zorlaRandevu', [['role' => 'user', 'content' => 'Evet']], 'k');
    icerir('hizmet eksik → "kaydedemedim" değil hizmet sorulur', 'Hangi hizmet', $z[0]);
    esit('son mesaj user değilse zorlamaz', null, cagir($zr, 'zorlaRandevu', [['role' => 'assistant', 'content' => 'x']], 'k'));

    bolum('Randevu onay cümlesi koddan (model atlasa da)');
    $oc = cagir($zr, 'randevuOnayCumlesi', ['ok' => true, 'tarih' => '2026-11-26', 'saat' => '11:00:00', 'personel' => 'Gülşah', 'bilgilendirme_gonderildi' => true]);
    esit('tam cümle', 'Tamamdır, randevu talebinizi oluşturdum; bilgilendirme mesajını ve yol tarifini telefonunuza gönderdim. Onaylandığında size haber vereceğiz.', $oc);
    $oc = cagir($zr, 'randevuOnayCumlesi', ['ok' => true, 'tarih' => '2026-11-26', 'saat' => '11:00', 'personel' => 'Gülşah', 'paket_seansi' => true, 'bilgilendirme_gonderildi' => false]);
    icerir('paket seansı belirtilir', 'paketinizden randevu talebinizi', $oc);
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
    $hl = asistan(['salon' => ['ad' => 'X', 'karsilama' => '', 'adres' => '', 'hizmetler' => [
        ['id' => 31, 'ad' => 'Lazer Epilasyon Tüm Vücut'], ['id' => 32, 'ad' => 'Lazer Epilasyon Bacak'], ['id' => 33, 'ad' => 'Lazer Epilasyonu Yüz']]]]);
    esit('"lazer epilasyon" (alt hizmet belirsiz) → yanlış-duyma notu yok (kayıt #70)', '', $hl->hizmetDuzeltmeNotu('Lazer epilasyon olabilir.'));
    esit('ön eşleştirme: birden çok/eksik ad → yok', '', $hl->onEslestirmeNotu('Lazer epilasyon olabilir.'));

    bolum('Kayıt #72: STT düzeltme, uzman hitabı, paket kararı');
    esit('"et plakson" → epilasyon', 'Bir lazer epilasyon randevusu almak istedim.', $hl->sttDuzelt('Bir lazer et plakson randevusu almak istedim.'));
    esit('"epilasyonu" eki korunur', 'lazer epilasyonu', $hl->sttDuzelt('lazer et plaksonu'));
    esit('doğru kelimeye dokunmaz', 'lazer epilasyon', $hl->sttDuzelt('lazer epilasyon'));
    $hb = asistan(['salon' => ['ad' => 'X', 'karsilama' => '', 'adres' => '', 'hizmetler' => [['id' => 5, 'ad' => 'Lazer Epilasyon']]]]);
    $ad72 = cagir($hb, 'hizmetAdaylari', 'lazer et plakson');
    esit('bitişik benzerlik: "lazer et plakson" → Lazer Epilasyon', 5, $ad72[0]['id'] ?? null);
    $hp = asistan(['_personeller' => [21 => 'Gülşah', 22 => 'Ferdi', 23 => 'Elif Hanım', 24 => 'Zxq'], '_personelCins' => [21 => 0, 22 => 1, 23 => 0, 24 => null]]);
    esit('uzman: kadın → Hanım', 'Gülşah Hanım', cagir($hp, 'personelHitap', 21));
    esit('uzman: erkek → Bey', 'Ferdi Bey', cagir($hp, 'personelHitap', 22));
    esit('uzman: zaten Hanım → değişmez', 'Elif Hanım', cagir($hp, 'personelHitap', 23));
    esit('uzman: cinsiyet bilinmiyor → yalnız ad', 'Zxq', cagir($hp, 'personelHitap', 24));
    // GECIKME: arac sonrasi cumle koddan (son Claude cagrisi atlanir)
    $iki = ['ok' => true, 'istenen_tarih' => '2026-11-20', 'istenen_gun_dolu' => false, 'secenekler' => [
        ['tarih' => '2026-11-20', 'saat' => '10:00', 'personel_id' => 32, 'personel' => 'Anıl Bey', 'gun' => 'Cuma, 20 Kasım'],
        ['tarih' => '2026-11-20', 'saat' => '11:40', 'personel_id' => 32, 'personel' => 'Anıl Bey', 'gun' => 'Cuma, 20 Kasım']]];
    esit('kod cevap: iki seçenek aynı gün/uzman', 'Cuma, 20 Kasım için uzmanımız Anıl Bey ile saat 10:00 ya da 11:40 uygun. Hangisini tercih edersiniz?',
        cagir($hp, 'aracSonrasiKodCevabi', [['musait_saatler', $iki]]));
    $farkli = $iki; $farkli['secenekler'][1] = ['tarih' => '2026-11-21', 'saat' => '12:00', 'personel_id' => 21, 'personel' => 'Gülşah Hanım', 'gun' => 'Cumartesi, 21 Kasım'];
    esit('kod cevap: farklı gün/uzman', 'Size Cuma, 20 Kasım saat 10:00 uzmanımız Anıl Bey ile ya da Cumartesi, 21 Kasım saat 12:00 uzmanımız Gülşah Hanım ile önerebilirim. Hangisini tercih edersiniz?',
        cagir($hp, 'aracSonrasiKodCevabi', [['musait_saatler', $farkli]]));
    $gunDolu = $iki; $gunDolu['istenen_tarih'] = '2026-11-19'; $gunDolu['istenen_gun_dolu'] = true;
    icerir('kod cevap: istenen gün dolu', 'Perşembe, 19 Kasım için uygun saat kalmadı.', cagir($hp, 'aracSonrasiKodCevabi', [['musait_saatler', $gunDolu]]));
    esit('kod cevap: dolu + öneri cümlesi aynen', 'X', cagir($hp, 'aracSonrasiKodCevabi', [['musait_saatler', ['ok' => true, 'onerilen_cumle' => 'X', 'secenekler' => []]]]));
    esit('kod cevap YOK: tam eşleşme (bağlama göre model)', null, cagir($hp, 'aracSonrasiKodCevabi', [['musait_saatler', ['ok' => true, 'tam_eslesme' => true, 'secenekler' => [$iki['secenekler'][0]]]]]));
    esit('kod cevap YOK: boş sonuç', null, cagir($hp, 'aracSonrasiKodCevabi', [['musait_saatler', ['ok' => true, 'secenekler' => [], 'mesaj' => 'yok']]]));
    esit('kod cevap YOK: hata', null, cagir($hp, 'aracSonrasiKodCevabi', [['randevu_guncelle', ['ok' => false, 'hata' => 'dolu']]]));

    // SALON REKLAMI: reklam yoksa / ozellik kapaliysa asistan BIREBIR ayni (arac + prompt bolumu yok)
    require_once __DIR__ . '/../app/Services/WaReklam.php';
    $rkA = asistan(['kanal' => 'whatsapp']);
    $araclarOnce = array_column(cagir($rkA, 'toolTanimlari'), 'name');
    esit('reklam yok: reklam_gonder aracı yok', false, in_array('reklam_gonder', $araclarOnce, true));
    esit('reklam yok: prompt bölümü boş', '', cagir($rkA, 'waReklamBolumu'));
    $rkR = [['id' => 7, 'baslik' => 'Protez kampanyası', 'metin' => '950 TL', 'hizmetler' => [11], 'hizmet_adlari' => ['Protez Tırnak'], 'token' => 'abc']];
    $rkA->waReklamlarAyarla($rkR, true);
    esit('reklam var: araç eklendi, diğerleri aynı', array_merge($araclarOnce, ['reklam_gonder']), array_column(cagir($rkA, 'toolTanimlari'), 'name'));
    icerir('reklam var: prompt kampanyayı listeler', 'reklam_id 7 | Protez kampanyası | ilgili hizmetler: Protez Tırnak', cagir($rkA, 'waReklamBolumu'));
    esit('reklam_gonder: geçersiz id reddedilir', false, cagir($rkA, 'toolCalistir', 'reklam_gonder', ['reklam_id' => 99])[0]['ok']);
    esit('reklam_gonder: geçerli id seçilir', [true, 7], [cagir($rkA, 'toolCalistir', 'reklam_gonder', ['reklam_id' => 7])[0]['ok'], $rkA->waReklamId]);
    esit('reklam_gonder: aynı turda ikinci kez yok', false, cagir($rkA, 'toolCalistir', 'reklam_gonder', ['reklam_id' => 7])[0]['ok']);
    $rkS = asistan(['kanal' => 'santral']);
    $rkS->waReklamlarAyarla($rkR, true);
    esit('santralde reklam yok (yalnız WhatsApp)', [false, ''], [in_array('reklam_gonder', array_column(cagir($rkS, 'toolTanimlari'), 'name'), true), cagir($rkS, 'waReklamBolumu')]);
    // WhatsApp sablonu: randevu onayi kart + "Başka..." alt satirda (satir sonlari ezilmez); santral eskisi gibi tek cumle
    $waOnay = cagir($rkA, 'baskaYardimEkle', cagir($rkA, 'randevuOnayCumlesi', ['tarih' => '2026-11-21', 'saat' => '10:00:00', 'hizmet' => 'Jel Güçlendirme', 'personel' => 'Berfin Hanım', 'bilgilendirme_gonderildi' => true]));
    icerir('WA randevu onayı: kart başlığı', '✅ *Randevu talebiniz oluşturuldu*', $waOnay);
    icerir('WA randevu onayı: satır satır', "💅 Jel Güçlendirme\n📅 Cumartesi, 21 Kasım\n🕒 10:00\n👤 Berfin Hanım", $waOnay);
    icerir('WA randevu onayı: soru ayrı satırda', "haber vereceğiz.\n\nBaşka yardımcı olabileceğim bir konu var mı?", $waOnay);
    $snOnay = cagir(asistan(), 'baskaYardimEkle', cagir(asistan(), 'randevuOnayCumlesi', ['tarih' => '2026-11-21', 'saat' => '10:00', 'bilgilendirme_gonderildi' => true]));
    esit('santral onayı değişmedi (tek cümle)', 'Tamamdır, randevu talebinizi oluşturdum; bilgilendirme mesajını ve yol tarifini telefonunuza gönderdim. Onaylandığında size haber vereceğiz. Başka yardımcı olabileceğim bir konu var mı?', $snOnay);
    icerir('kampanyayı isteyen müşteri kuralı', 'SEVE SEVE', cagir($rkA, 'waReklamBolumu'));
    $rkG = asistan(['kanal' => 'whatsapp']);
    $rkG->waReklamlarAyarla($rkR, false);
    esit('bu sohbette gönderildi: araç yok ama kampanya bilinir', [false, true],
        [in_array('reklam_gonder', array_column(cagir($rkG, 'toolTanimlari'), 'name'), true), mb_strpos(cagir($rkG, 'waReklamBolumu'), 'tekrar gönderme') !== false]);
    esit('kod cevap YOK: başka araç', null, cagir($hp, 'aracSonrasiKodCevabi', [['hizmet_eslestir', ['ok' => true]]]));
    esit('kod cevap: iptal', 'Randevunuz iptal edildi. Size bilgilendirme mesajı ilettim.',
        cagir($hp, 'aracSonrasiKodCevabi', [['randevu_iptal', ['ok' => true, 'mesaj' => 'Randevu iptal edildi', 'bilgilendirme_gonderildi' => true]]]));
    icerir('kod cevap: randevu oluştu', 'randevu talebinizi oluşturdum', cagir($hp, 'aracSonrasiKodCevabi', [['randevu_olustur', ['ok' => true, 'tarih' => '2026-11-20', 'saat' => '10:00', 'personel' => 'Anıl Bey']]]));

    // kayit #120: guncellemede uzman degisebilir; onay cumlesi koddan (yeni uzman adiyla)
    esit('güncelleme onayı (yeni uzman)', "Perşembe, 19 Kasım saat 14:00'te uzmanımız Gülşen Hanım ile randevunuzu güncelledim ve size bilgilendirme mesajı gönderdim.",
        cagir($hp, 'guncelleOnayCumlesi', ['tarih' => '2026-11-19', 'saat' => '14:00', 'personel' => 'Gülşen Hanım', 'bilgilendirme_gonderildi' => true]));
    esit('güncelleme onayı (mesaj yok)', "Perşembe, 19 Kasım saat 14:00'te uzmanımız Ferdi Bey ile randevunuzu güncelledim.",
        cagir($hp, 'guncelleOnayCumlesi', ['tarih' => '2026-11-19', 'saat' => '14:00', 'personel' => 'Ferdi Bey', 'bilgilendirme_gonderildi' => false]));
    icerir('onay cümlesi: paketinizden', 'Tamamdır, paketinizden randevu talebinizi oluşturdum', cagir($hp, 'randevuOnayCumlesi', ['tarih' => '2026-11-20', 'saat' => '10:00', 'personel' => 'Gülşah Hanım', 'paket_seansi' => true]));
    $hk = asistan(['_paketler' => [['adisyon_paket_id' => 77, 'paket_adi' => '5 Seans Lazer', 'hizmetler' => [['hizmet_id' => 5, 'ad' => 'Lazer Epilasyon', 'kalan_seans' => 3, 'personeller' => []]]]]]);
    $g72 = [['role' => 'assistant', 'content' => 'Anıl Bey, 5 seans lazer paketiniz var. 3 seansınız kalmış görünüyor. Randevuyu bu paketinizden oluşturmak ister misiniz?']];
    icerir('"Tabi ki" (bu tur) → onaylandı + id', 'adisyon_paket_id 77 ver', cagir($hk, 'paketKarariNotu', $g72, 'Tabi ki.'));
    $g72[] = ['role' => 'user', 'content' => 'Tabi ki.'];
    icerir('sonraki turlarda da hatırlatılır', 'TEKRAR SORMA', cagir($hk, 'paketKarariNotu', $g72, 'Fark etmez.'));
    icerir('"hayır paketten olmasın" → paketsiz', 'paketsiz=true', cagir($hk, 'paketKarariNotu', [$g72[0]], 'Hayır paketten olmasın'));
    esit('soru sorulmadıysa → not yok', '', cagir($hk, 'paketKarariNotu', [], 'evet'));

    bolum('Çakışma: istenen saat dolu → uygun değil + 2 öneri');
    esit('saat eki 13:00', "13:00'te", cagir($a, 'saatEkli', '13:00'));
    esit('saat eki 10:00', "10:00'da", cagir($a, 'saatEkli', '10:00'));
    esit('saat eki 12:00', "12:00'de", cagir($a, 'saatEkli', '12:00'));
    esit('saat eki 12:30', "12:30'da", cagir($a, 'saatEkli', '12:30'));
    esit('saat eki 14:40', "14:40'ta", cagir($a, 'saatEkli', '14:40'));
    $sec2 = [['tarih' => '2026-11-20', 'saat' => '13:00', 'personel' => 'Gülşah Hanım'], ['tarih' => '2026-11-20', 'saat' => '14:30', 'personel' => 'Ferdi Bey']];
    $dc = cagir($a, 'doluOneriCumlesi', '2026-11-20', '12:00', $sec2);
    icerir('dolu: önce uygun değil', "Cuma, 20 Kasım saat 12:00 uygun değil.", $dc);
    icerir('dolu: iki öneri', "aynı gün saat 13:00'te uzmanımız Gülşah Hanım ile ya da saat 14:30'da uzmanımız Ferdi Bey ile önerebilirim, hangisi uygun olur?", $dc);
    $dc2 = cagir($a, 'doluOneriCumlesi', '2026-11-20', '12:00', [['tarih' => '2026-11-20', 'saat' => '10:00', 'personel' => 'Gülşah Hanım'], ['tarih' => '2026-11-21', 'saat' => '12:00', 'personel' => 'Gülşah Hanım']]);
    icerir('dolu: ertesi gün önerisi gün adıyla', "Cumartesi, 21 Kasım saat 12:00'de", $dc2);
    $ds = ['secenekler' => $sec2];
    esit('model "dolu" demeden saat önerdi → eksik', false, cagir($a, 'doluCevabiTamMi', "Cuma saat 13:00'te Gülşah Hanım ile oluşturayım mı?", $ds));
    esit('model tek öneri söyledi → eksik', false, cagir($a, 'doluCevabiTamMi', "12 dolu, 13:00 uygun mu?", $ds));
    esit('model iki öneri + dolu → tamam', true, cagir($a, 'doluCevabiTamMi', "Saat 12 dolu. 13'te Gülşah Hanım ya da 14.30'da Ferdi Bey uygun.", $ds));
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
    foreach (['Aksel Kaya', 'Candan Ak', 'Baran Er', 'Deniz Kaya'] as $iki) esit("iki cinsiyetli: $iki → hitap yok", null, cagir(asistan(['musteriAd' => $iki]), 'hitap'));
    foreach (['Emir Ak' => 'Emir Bey', 'Görkem Can' => 'Görkem Bey', 'Yüksel Er' => 'Yüksel Bey', 'Tuna Su' => 'Tuna Hanım', 'Kamer Ak' => 'Kamer Bey', 'İmran Ak' => 'İmran Bey', 'Sunay Ak' => 'Sunay Bey', 'Evren Ak' => 'Evren Bey', 'Mutlu Ak' => 'Mutlu Bey'] as $ad => $bek) esit("işletme düzeltmesi: $ad → $bek", $bek, cagir(asistan(['musteriAd' => $ad]), 'hitap'));
    foreach (['Ümit Ak' => 'Ümit Bey', 'Özgür Can' => 'Özgür Bey', 'Anıl Orbey' => 'Anıl Bey', 'Ayhan Er' => 'Ayhan Bey'] as $ad => $bek) esit("çoğunlukla erkek (%85+): $ad → $bek", $bek, cagir(asistan(['musteriAd' => $ad]), 'hitap'));
    esit('sözlükte yok, elle listede: Ecrin → Ecrin Hanım', 'Ecrin Hanım', cagir(asistan(['musteriAd' => 'Ecrin Su']), 'hitap'));
    DB::$sayim = ['Orbeyhan' => ['e' => 40, 'k' => 0], 'Zirvan' => ['e' => 3, 'k' => 0]];
    esit('hiçbir listede yok, müşteri verisi erkek → Bey', 'Orbeyhan Bey', cagir(asistan(['musteriAd' => 'Orbeyhan Ak']), 'hitap'));
    esit('hiçbir listede yok, az veri → hitap yok', null, cagir(asistan(['musteriAd' => 'Zirvan Ak']), 'hitap'));
    DB::$sayim = ['Aksel' => ['e' => 100, 'k' => 0]];
    esit('iki cinsiyetli ad müşteri verisine bakmaz', null, cagir(asistan(['musteriAd' => 'Aksel Kaya']), 'hitap'));
    esit('kayıt her şeyi ezer (Ferdi ama kayıtta kadın)', 'Ferdi Hanım', cagir(asistan(['musteriAd' => 'Ferdi Korkmaz', 'musteriCinsiyet' => 0]), 'hitap'));
    $pk = new ReflectionProperty(App\Services\TurkceIsimCinsiyet::class, 'panel'); $pk->setAccessible(true);
    $pk->setValue(null, ['ferdi' => 0, 'orbeyhan' => 2]);
    esit('panel kararı sözlüğü ezer (Ferdi → Kadın)', 'Ferdi Hanım', cagir(asistan(['musteriAd' => 'Ferdi Korkmaz']), 'hitap'));
    esit('panel Hitapsız → müşteri verisine bakılmaz', null, cagir(asistan(['musteriAd' => 'Orbeyhan Ak']), 'hitap'));
    $pk->setValue(null, null);
    esit('anahtar: Türkçe karaktersiz ilk ad', 'gulsah', App\Services\TurkceIsimCinsiyet::anahtar('GÜLŞAH İnce'));
    DB::$sayim = [];
    esit('erkek (1) → Anıl Bey', 'Anıl Bey', cagir(asistan(['musteriCinsiyet' => 1]), 'hitap'));
    esit('kadın (0) → Anıl Hanım', 'Anıl Hanım', cagir(asistan(['musteriCinsiyet' => 0]), 'hitap'));
    esit('belirtilmemiş + iki cinsiyetli ad → hitap yok', null, cagir(asistan(['musteriAd' => 'Aksel Kaya']), 'hitap'));
    esit('kayıtsız müşteri → hitap yok', null, cagir(asistan(['musteriAd' => null, 'musteriId' => null, 'musteriCinsiyet' => 1]), 'hitap'));
    icerir('hızlı veda hitaplı', 'Rica ederiz Anıl Bey,', asistan(['musteriCinsiyet' => 1])->konus('Yok teşekkürler', $sor)['cevap']);
    icerir('hızlı veda cinsiyet yoksa hitapsız', 'Rica ederiz, bizi', asistan(['musteriAd' => 'Aksel Kaya'])->konus('Yok teşekkürler', $sor)['cevap']);
    icerir('prompt: cinsiyet yoksa tahmin yasağı', 'isimden cinsiyet TAHMİN ETME', cagir(asistan(['musteriAd' => 'Aksel Kaya']), 'sistemPromptu'));
    icerir('prompt: cinsiyet varsa hitap', '"Anıl Bey" hitabını YALNIZ', cagir(asistan(['musteriCinsiyet' => 1]), 'sistemPromptu'));

    // ---- WhatsApp kanali (WaAsistan): ayni beyin, yazili cikis
    $wa = asistan(['kanal' => 'whatsapp', 'musteriCinsiyet' => 1]);
    esit('WA: numara rakam rakam AYRILMAZ', 'Numaranız 5321112233 doğru mu?', cagir($wa, 'ttsTemizle', 'Numaranız 5321112233 doğru mu?'));
    esit('WA: satır sonları korunur', "Seçenekler:\n1) 14:00\n2) 15:30", cagir($wa, 'ttsTemizle', "Seçenekler:\n1) 14:00\n2) 15:30"));
    esit('WA: gizli not gönderilmez', 'Tamam.', cagir($wa, 'ttsTemizle', 'Tamam. [SİSTEM — x=1]'));
    icerir('WA: prompt kanal bölümü', 'KANAL: WHATSAPP YAZIŞMASI', cagir($wa, 'sistemPromptu'));
    esit('santral: prompt kanal bölümü YOK', false, strpos(cagir(asistan(), 'sistemPromptu'), 'KANAL: WHATSAPP') !== false);
    esit('WA: ilk yapay tur', "(müşteri WhatsApp'tan yazdı)", cagir($wa, 'gecmisMesajlari', [['role' => 'assistant', 'content' => 'Merhaba']])[0]['content']);
    esit('santral: ilk yapay tur', '(arama bağlandı)', cagir(asistan(), 'gecmisMesajlari', [['role' => 'assistant', 'content' => 'Merhaba']])[0]['content']);
    icerir('WA: personel mesajı modele işaretli', 'işletme personeli elle yazdı', cagir($wa, 'gecmisMesajlari', [['role' => 'user', 'content' => 'x'], ['role' => 'assistant', 'content' => 'Ben bakarım', 'personel' => 1]])[1]['content']);
    esit('WA karşılama: kayıtlı müşteri hitap (işletme metni)', 'Merhabalar Anıl Bey, hoş geldiniz! 🌸 Nasıl yardımcı olabilirim size?', $wa->waKarsilama());
    $waK = asistan(['kanal' => 'whatsapp', 'musteriAd' => null, 'musteriId' => null]);
    esit('WA karşılama: kayıtsız → işletme metni (ad/telefon sormaz)', 'Merhabalar, hoş geldiniz! 🌸 Nasıl yardımcı olabilirim size?', $waK->waKarsilama());
    esit('WA karşılama: kayıtsız → ad sorusu yok', false, mb_strpos($waK->waKarsilama(), 'adınızı') !== false);
    esit('WA profil adı: gerçek ad soyad', 'Feyza Musabeyoğlu', App\Services\RandevuAsistan::profilAdSoyad('feyza  MUSABEYOĞLU'));
    esit('WA profil adı: tek kelime → yok', null, App\Services\RandevuAsistan::profilAdSoyad('Zeynep'));
    esit('WA profil adı: işaretli takma ad → yok', null, App\Services\RandevuAsistan::profilAdSoyad('Htc~K'));
    esit('WA profil adı: emoji → yok', null, App\Services\RandevuAsistan::profilAdSoyad('Ayşe Kaya 💅'));
    esit('WA profil adı: işletme adı → yok', null, App\Services\RandevuAsistan::profilAdSoyad('Nail Studio Ece'));
    esit('WA profil adı: rakam → yok', null, App\Services\RandevuAsistan::profilAdSoyad('Ayşe 2024'));
    $waK->waProfilAdiAyarla('Feyza Musabeyoğlu');
    $pK = cagir($waK, 'sistemPromptu');
    icerir('WA kayıtsız prompt: başta kayıt isteme', 'sohbetin başında ad, soyad ya da telefon İSTEME', $pK);
    icerir('WA kayıtsız prompt: profil adıyla onay', 'Feyza Musabeyoğlu adına oluşturuyorum', $pK);
    icerir('WA kayıtsız prompt: numara biliniyor → sorma', 'telefon SORMA', $pK);
    icerir('WA prompt: hız kuralı', 'randevu en fazla 2-3 mesajda', $pK);
    $pG = cagir(asistan(['kanal' => 'whatsapp', 'musteriAd' => null, 'musteriId' => null, 'telefon' => null]), 'sistemPromptu');
    icerir('WA kayıtsız + gizli numara: onayda ad soyad + telefon', 'cep telefonunu da iste', $pG);
    icerir('WA kayıtsız profil adı yok: onayda ad soyad iste', 'adınızı ve soyadınızı yazar mısınız', $pG);
    $pS = cagir(asistan(['musteriAd' => null, 'musteriId' => null]), 'sistemPromptu');
    icerir('Santral değişmedi: kayıt adımı başta', 'KAYIT ADIMI (randevudan ÖNCE, atlanamaz)', $pS);
    $waD = asistan(['kanal' => 'whatsapp']);
    $waD->waDersleriAyarla(['İlk mesajdaki saati hemen kontrol et.', '  ']);
    icerir('WA dersleri: talimata eklenir', 'SOHBETLERİNDEN ÖĞRENİLENLER (test edildi; uy)' . "\n" . '- İlk mesajdaki saati hemen kontrol et.', cagir($waD, 'sistemPromptu'));
    esit('WA dersleri: santralde yok', false, strpos(cagir(asistan(), 'sistemPromptu'), 'ÖĞRENİLENLER') !== false);
    $dn = $waD->waDenemeIstegi([['role' => 'user', 'content' => 'yarın 3te protez']]);
    esit('deneme isteği: canlı model + araçlar + müşteri mesajı', [true, 'user'], [count($dn['tools']) > 3, $dn['messages'][0]['role']]);
    $RA = App\Services\RandevuAsistan::class;
    esit('söz verip bıraktı: "bir bakayım."', true, $RA::sozVeripBirakti('Tabii, Fatma Hanım\'ı deneyebilirim. Perşembe 19 Kasım saat 17:30\'da uzmanımız Fatma Hanım ile uygun mu bir bakayım.'));
    esit('söz verip bıraktı: "not aldım."', true, $RA::sozVeripBirakti('Harika, Berfin Hanım\'ı not aldım.'));
    esit('soru soruyor → bırakmadı', false, $RA::sozVeripBirakti('Kontrol ettim: 17:30 Fatma Hanım uygun. Oluşturayım mı?'));
    esit('net bilgi → bırakmadı', false, $RA::sozVeripBirakti('Randevunuz Perşembe 17:30 Fatma Hanım ile oluşturuldu.'));
    icerir('WA prompt: söz verip bırakma kuralı', 'SÖZ VERİP BIRAKMA', cagir($waD, 'sistemPromptu'));
    esit('uzman sorusu: "tercih ettiğiniz bir uzmanımız var mı"', true, $RA::uzmanSorusuMu('Merhaba. Kalıcı Oje için Pazar, 22 Kasım uygun saatlerimizi kontrol edebilmem için tercih ettiğiniz bir uzmanımız var mı, yoksa fark etmez mi?'));
    esit('uzman sorusu: "hangi uzmanımızı tercih edersiniz" (saat olsa da)', true, $RA::uzmanSorusuMu('Tabii, 17:30 için bakabiliriz. Hangi uzmanımızı tercih edersiniz: Melis Hanım ya da Fatma Hanım?'));
    esit('uzman sorusu değil: saat sunuyor', false, $RA::uzmanSorusuMu('Pazar 22 Kasım 11:00 Kübra Hanım ile uygun. Oluşturayım mı?'));
    icerir('WA prompt: personel sorma kuralı', 'PERSONEL SORMA', cagir($waD, 'sistemPromptu'));
    $pSan = cagir(asistan(), 'sistemPromptu');
    icerir('Santral prompt: uzman tercihi sorulmaz', 'uzman tercihi SORMA', $pSan);
    esit('Santral prompt: eski uzman sorusu kalmadı', false, mb_strpos($pSan, 'Söylemediyse BİR KEZ') !== false);
    esit('WA selam: hitaplı', 'Merhaba Anıl Bey.', $wa->waSelam());
    esit('WA selam: cinsiyet yok', 'Merhaba, Sayın Aksel Kaya.', asistan(['kanal' => 'whatsapp', 'musteriAd' => 'Aksel Kaya'])->waSelam());
    $waH = ['role' => 'assistant', 'content' => 'Sayın Anıl, yarın 14:00 randevunuzu hatırlatırız.', 'hatirlatma' => ['tur' => 'randevu', 'randevu_id' => 5, 'soz' => 'yarın 14:00', 'wa' => 1]];
    esit('WA hatırlatma mesajı → cevap bekleniyor', 'soru', cagir($wa, 'hatirlatmaBekleyen', [$waH])['adim'] ?? null);
    esit('WA hatırlatma, sonraki tur → bekleme yok', null, cagir($wa, 'hatirlatmaBekleyen', [$waH, ['role' => 'user', 'content' => 'evet'], ['role' => 'assistant', 'content' => 'Teşekkürler, sizi bekliyoruz.']]));

    require_once __DIR__ . '/../app/Services/WaAsistan.php';
    $wc = function ($m, ...$a) { $r = new ReflectionMethod(App\Services\WaAsistan::class, $m); $r->setAccessible(true); return $r->invoke(null, ...$a); };
    foreach (['Merhaba', 'merhabalar', 'Selam', 'iyi günler', 'Günaydın', 'slm', 'Merhaba iyi günler'] as $x) esit("WA selam mı: $x", true, $wc('selamMi', $x));
    foreach (['Merhaba yarın saç kesimi için randevu alabilir miyim', 'yarın 3te', 'iptal etmek istiyorum'] as $x) esit("WA selam değil: $x", false, $wc('selamMi', $x));
    foreach (['Mesajınız alınmıştır, en kısa sürede dönüş yapılacaktır.', 'Bu bir otomatik yanıttır.', 'Mesai saatlerimiz dışında yazdınız', 'Auto-reply: I am away'] as $x) esit("WA otomatik yanıt: $x", true, $wc('otomatikYanitMi', $x));
    foreach (['Yarın randevum var mı?', 'Saat 3 uygun mu', 'tamam teşekkürler'] as $x) esit("WA otomatik yanıt değil: $x", false, $wc('otomatikYanitMi', $x));
    esit('WA telefon ifadesi: hatta kalın', 'Sizi işletmemize yönlendiriyorum.', $wc('telefonIfadeleri', 'Sizi işletmemize yönlendiriyorum, lütfen hatta kalın.'));
    icerir('WA telefon ifadesi: aradığınız → yazdığınız', 'bize yazdığınız için teşekkür', $wc('telefonIfadeleri', 'Rica ederiz, bizi aradığınız için teşekkür ederiz.'));
    esit('WA döngü sadeleştirme', 'merhaba nasılsınız', $wc('sade', '  Merhaba,   nasılsınız?? '));

    // Kayit 2026-11-23 12.52: "Tabii, anladım." her cevapta tekrarlanmaz, nezaketten once atilir
    $gT = [['role' => 'assistant', 'content' => 'Tabii, anladım. Pedikür için hangi gün uygun?'], ['role' => 'user', 'content' => 'yarın']];
    esit('Tekrar onay: son cevapta da vardı → atılır', 'Yarın 14:00 uygun mu?', cagir($ab, 'tekrarOnayKes', 'Tabii, anladım. Yarın 14:00 uygun mu?', $gT));
    esit('Tekrar onay: rica öncesi → atılır', 'Rica ederiz Miray Hanım.', cagir($ab, 'tekrarOnayKes', 'Tabii, anladım. Rica ederiz Miray Hanım.', []));
    esit('Tekrar onay: ilk kez → kalır', 'Tabii, anladım. Hangi gün?', cagir($ab, 'tekrarOnayKes', 'Tabii, anladım. Hangi gün?', [['role' => 'assistant', 'content' => 'Merhaba.']]));
    esit('Tekrar onay: farklı kalıp → kalır', 'Harika. Hangi gün?', cagir($ab, 'tekrarOnayKes', 'Harika. Hangi gün?', $gT));
    esit('Tekrar onay: yalnız onay → dokunulmaz', 'Tabii, anladım.', cagir($ab, 'tekrarOnayKes', 'Tabii, anladım.', $gT));

    bolum('Kayıt 258: müşteri uzman adı söylüyor (Leyla Hanım)');
    $l258 = asistan(['_personeller' => [1 => 'Sümeyra', 2 => 'Melis', 3 => 'Berfin', 4 => 'Feyza', 5 => 'Leyla Kaya', 6 => 'İpek'],
        'salon' => ['ad' => 'Ola Nail', 'hizmetler' => [['id' => 7, 'ad' => 'Jel Güçlendirme'], ['id' => 8, 'ad' => 'İpek Kirpik']]]]);
    foreach (["Cumartesi saat 3'te Leyla Hanım'a randevu oluşturmak istiyorum" => [5], "Leyla'yı istiyorum" => [5], 'Leyla dedim' => [5], 'leylaya olsun' => [5],
              'İpek kirpik istiyorum' => [], 'İpek Hanım olsun' => [6], 'Cumartesi saat 3' => [], "Melis Hanım'dan alayım" => [2]] as $m => $b) {
        esit("anılan uzman: $m", $b, array_keys(cagir($l258, 'anilanPersoneller', $m)));
    }
    $g258 = [['role' => 'user', 'content' => 'Evet'], ['role' => 'assistant', 'content' => 'Jel güçlendirme için hangi gün ve saat sizin için uygun?']];
    esit('konuşulan hizmet geçmişten', 7, cagir($l258, 'konusulanHizmetId', "Cumartesi 3'te Leyla Hanım'a", $g258));
    esit('konuşulan hizmet araç notundan', 8, cagir($l258, 'konusulanHizmetId', 'Leyla', [['role' => 'assistant', 'content' => 'x', 'baglam' => 'hizmet_eslestir("ipek") → {"ok":true,"hizmet":{"id":8,"ad":"İpek Kirpik"}}']]));
    $n258 = cagir($l258, 'personelNotu', "Cumartesi saat 3'te Leyla Hanım'a randevu oluşturmak istiyorum", $g258);
    icerir('not: personel_id', 'Leyla Hanım = personel_id 5', $n258);
    icerir('not: hizmeti veriyor', 'hizmet_id 7, VERİYOR', $n258);
    icerir('not: yok deme', 'DEME', $n258);
    esit('uzman adı yoksa not yok', '', cagir($l258, 'personelNotu', 'Cumartesi saat 3 olsun', $g258));
    esit('müşteri kendi adını söylüyorsa not yok', '', cagir($l258, 'personelNotu', 'Leyla Yılmaz', [['role' => 'assistant', 'content' => 'Önce adınızı ve soyadınızı alabilir miyim?']]));
    esit('"Benim adım Leyla" → not yok', '', cagir($l258, 'personelNotu', 'Benim adım Leyla', $g258));
    icerir('yanlış duyulan ad → teyit', 'Leyla Hanım mı demek istediniz', cagir($l258, 'personelNotu', "Leyda Hanım'a randevu", $g258));
    esit('alakasız ad → not yok', '', cagir($l258, 'personelNotu', 'Gülbahar Hanım var mı', $g258));
    esit('"Leyla Hanım hangi hizmeti veriyor" → salon listesi okunmaz', null, $l258->hizmetListesiCevabi('Leyla Hanım hangi hizmeti veriyor'));
    $ip258 = $l258->sttIpuclari();
    esit('STT ipucu: uzman adı', true, in_array('Leyla', $ip258, true) && in_array('Leyla Hanım', $ip258, true));
    icerir('prompt: kısaltılmış listeye bakıp yok deme', 'KISALTILMIŞ', cagir($pz, 'sistemPromptu'));

    bolum('Kayıt 281: fiyat sesli okunuş');
    foreach ([
        "Protez tırnak hizmetimizin fiyatı 1.200 TL'dir." => 'Protez tırnak hizmetimizin fiyatı bin iki yüz liradır.',
        'Kalıcı Oje fiyatımız 790 lira.' => 'Kalıcı Oje fiyatımız yedi yüz doksan lira.',
        '1.250,50 TL' => 'bin iki yüz elli lira elli kuruş',
        '12.500 TL\'ye kadar' => 'on iki bin beş yüz liraya kadar',
        '₺450' => 'dört yüz elli lira',
        '300 tl olur' => 'üç yüz lira olur',
        'Saat 15.30 uygun mu?' => 'Saat 15.30 uygun mu?',
        '25.11.2026 tarihinde' => '25.11.2026 tarihinde',
        'Sizi bağlıyorum, lütfen hatta kalın.' => 'Sizi bağlıyorum, lütfen kapatmayın.',
        'HTML ve TLC' => 'HTML ve TLC',
    ] as $m => $b) esit("sesli: $m", $b, $pz->sesliOkunus($m));
    foreach ([1 => 'bir', 10 => 'on', 100 => 'yüz', 101 => 'yüz bir', 1000 => 'bin', 1200 => 'bin iki yüz', 2350 => 'iki bin üç yüz elli', 100000 => 'yüz bin', 1500000 => 'bir milyon beş yüz bin'] as $n => $b)
        esit("sayı $n", $b, $pz->sayiYazi($n));
    $g283 = [['role' => 'assistant', 'content' => 'Üzgünüm, kaynak adında bir hizmetimiz bulunmuyor. Bunun yerine tırnak, kaş, kirpik veya pedikür gibi hizmetlerimiz var. Hangisiyle ilgilenirsiniz?'], ['role' => 'user', 'content' => 'Tırnak']];
    esit('aynı uzun cevap tekrarı → yalnız soru', 'Hangisiyle ilgilenirsiniz?', $pz->tekrarCevapKisalt($g283[0]['content'], $g283));
    esit('farklı cevap dokunulmaz', 'Tırnak için hangi gün uygun?', $pz->tekrarCevapKisalt('Tırnak için hangi gün uygun?', $g283));

    bolum('Hizmet anlaşılmadı → yine randevu (2026-11-26)');
    esit('tahmini hizmet: benzeyen ifade', 1, cagir($pz, 'tahminiHizmetId', 'el protez yaptırmak istiyorum'));
    esit('tahmini hizmet: anlamsız ifade → yine bir hizmet', true, is_int(cagir($pz, 'tahminiHizmetId', 'kaynak yaptırmak istiyorum')));
    $ro = null; foreach (cagir($pz, 'toolTanimlari') as $a) if ($a['name'] === 'randevu_olustur') $ro = $a;
    esit('randevu_olustur: hizmet_anlasilmadi parametresi', true, isset($ro['input_schema']['properties']['hizmet_anlasilmadi']));
    esit('randevu_olustur: zorunlu yalnız tarih_saat', ['tarih_saat'], $ro['input_schema']['required']);
    icerir('prompt: iki denemede anlaşılmazsa randevu aç', 'hizmet_anlasilmadi=true', cagir($pz, 'sistemPromptu'));
    $hz0 = asistan(['musteriId' => null, 'salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Kalıcı Oje']]]]);
    esit('kayıtsız müşteri → önce kayıt', false, cagir($hz0, 'randevuHizmetsiz', ['tarih_saat' => '2026-12-01T15:00:00', 'musteri_ifadesi' => 'kaynak'])['ok']);

    bolum('Hizmet anlaşılmadı: açıklama yok, gün-saat → randevu; fiyat → salona bağla');
    $hzE = ['i' => 'kaynak', 't' => '2026-12-05', 's' => null, 'a' => [['2026-12-05', '14:30'], ['2026-12-05', '15:30']]];
    esit('etiket → geçmişten geri okunur', $hzE, cagir($pz, 'hizmetsizBekleyen', [['role' => 'user', 'content' => 'x'], ['role' => 'assistant', 'content' => 'y', 'baglam' => cagir($pz, 'hizmetsizEtiket', $hzE)]]));
    esit('etiket yalnız SON asistan mesajında geçerli', null, cagir($pz, 'hizmetsizBekleyen', [['role' => 'assistant', 'content' => 'y', 'baglam' => cagir($pz, 'hizmetsizEtiket', $hzE)], ['role' => 'user', 'content' => 'x'], ['role' => 'assistant', 'content' => 'z']]));
    icerir('dolu cümlesi iki saat', '14:30 ya da saat 15:30 uygun, hangisini istersiniz?', cagir($pz, 'hizmetsizDoluCumlesi', '2026-12-05', '15:00', $hzE['a']));
    $kz = asistan(['musteriId' => null, 'salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Kalıcı Oje']]]]);
    $b = cagir($kz, 'hizmetsizBaslat', 'Kaynak yaptırmak istiyorum', 'kaynak');
    esit('kayıtsız: ad + gün-saat tek soruda', 'Randevunuzu oluşturmam için adınızı, soyadınızı ve size uygun gün ile saati alabilir miyim?', $b['cevap']);
    icerir('başlangıç: mod etiketi geçmişe', '[[HZ:', $b['baglam']);
    esit('başlangıç: "hizmetimiz yok" denmez', false, mb_stripos($b['cevap'], 'bulunmuyor') !== false || mb_stripos($b['cevap'], 'yok') !== false);
    esit('kayıtsız müşteride kod yolu yok (model ad alır)', null, cagir($kz, 'hizmetsizTur', 'yarın saat 3', $hzE));
    $kk = asistan(['musteriId' => 77, 'salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Kalıcı Oje']]]]);
    $t1 = cagir($kk, 'hizmetsizTur', 'yarın olsun', ['i' => 'kaynak', 't' => null, 's' => null, 'a' => []]);
    icerir('yalnız gün → saat sorulur', 'saat kaçta uygun?', $t1['cevap']);
    $t2 = cagir($kk, 'hizmetsizTur', 'saat 3 olur', ['i' => 'kaynak', 't' => null, 's' => null, 'a' => []]);
    icerir('yalnız saat → gün sorulur', 'hangi gün uygun?', $t2['cevap']);
    esit('zaman yok → model', null, cagir($kk, 'hizmetsizTur', 'aslında emin değilim', ['i' => 'kaynak', 't' => null, 's' => null, 'a' => []]));
    esit('müşteri mesajında ⚠ hizmet adı yazılmaz', '', App\Services\RandevuAsistan::musteriHizmetAdi('⚠ HİZMET ANLAŞILMADI'));
    esit('müşteri mesajında normal hizmet adı aynen', 'Kalıcı Oje', App\Services\RandevuAsistan::musteriHizmetAdi('Kalıcı Oje'));
    esit('fiyat konusu: "eğitim ücreti"', 'egitim', $pz->fiyatKonusu('Ben eğitim almak istiyordum da, ücretini öğrenmek istiyordum.'));
    esit('fiyat konusu: yalnız "fiyatı almak istiyorum"', '', $pz->fiyatKonusu('Fiyatı almak istiyorum.'));
    $fz = asistan(['salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Protez Tırnak', 'fiyat' => 1200]]]]); $fz->santralFiyatSoyle = true;
    $fa = $fz->santralFiyatCevabi('Ben eğitim almak istiyordum, ücretini öğrenmek istiyordum', []);
    esit('bilinmeyen hizmet fiyatı → salona bağla', 'aktar', $fa['aksiyon']);
    icerir('bilinmeyen hizmet fiyatı → not', 'Fiyat sordu: egitim', $fa['fiyat_aktar'] ?? '');
    esit('konusuz fiyat → hizmet sorulur', 'Hangi hizmetimizin fiyatını öğrenmek istersiniz?', $fz->santralFiyatCevabi('Fiyatı almak istiyorum', [])['cevap']);
    $gF = [['role' => 'assistant', 'content' => 'Hangi hizmetimizin fiyatını öğrenmek istersiniz?']];
    esit('fiyat sorusuna "Eğitim" → salona bağla', 'aktar', $fz->konus('Eğitim', $gF)['aksiyon']);
    icerir('fiyat sorusuna bilinen hizmet → gerçek fiyat', 'bin iki yüz', $fz->sesliOkunus($fz->konus('Protez tırnak', $gF)['cevap']));

    // Model hizmet_eslestir cagirdi, sonuc "yok" -> aciklama yapilmadan gun-saat sorulur (model ikinci kez cagrilmaz)
    $hy = new class extends App\Services\RandevuAsistan {
        public $cagri = 0;
        public function __construct() {}
        protected function cagir(array $m, string $k, int $t = 0, array $e = []): ?array {
            $this->cagri++;
            return ['content' => [['type' => 'tool_use', 'id' => 'h1', 'name' => 'hizmet_eslestir', 'input' => ['metin' => 'kaynak']]]];
        }
        protected function ctrlCagir(string $m, array $p): array { return ['ok' => true, 'eslesme' => 'yok', 'oneriler' => []]; }
    };
    foreach (['salonId' => 20, 'musteriId' => 77, 'telefon' => '5321112233', 'kanal' => 'santral', 'salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Kalıcı Oje']]]] as $k => $v) { $p = new ReflectionProperty(App\Services\RandevuAsistan::class, $k); $p->setAccessible(true); $p->setValue($hy, $v); }
    $GLOBALS['_config']['services.anthropic.key'] = 'test';
    $r = $hy->konus('Kaynak yaptırmak istiyorum', [['role' => 'assistant', 'content' => 'Merhaba, size nasıl yardımcı olabilirim?']]);
    esit('hizmet yok → yalnız gün-saat sorulur', 'Hangi gün ve saat size uygun?', $r['cevap']);
    esit('hizmet yok → model tek kez çağrıldı', 1, $hy->cagri);
    icerir('hizmet yok → mod etiketi', '[[HZ:', (string) ($r['baglam'] ?? ''));

    bolum('Kayıt 315/321: "öğleden sonra" → hemen randevu (açılamazsa iki saat), "saat kaçta?" tekrarlanmaz');
    $hv = new class extends App\Services\RandevuAsistan {
        public $vakitler = [];
        public $acilamaz = true;
        public function __construct() {}
        protected function hizmetsizRandevuAc(string $tarih, string $saat, string $ifade): array {
            return $this->acilamaz ? ['ok' => false, 'alternatif' => []] : ['ok' => true, 'randevu_id' => 5, 'tarih' => $tarih, 'saat' => $saat];
        }
        protected function waOtomatikKayit(): void {}
        protected function hizmetsizGunSecenekleri(string $tarih, ?string $vakit, string $ifade): array {
            $this->vakitler[] = $vakit;
            return $vakit === 'ogleden_sonra' ? [[$tarih, '13:00'], [$tarih, '14:00']] : [[$tarih, '10:00'], [$tarih, '11:00']];
        }
    };
    foreach (['salonId' => 20, 'musteriId' => 77, 'kanal' => 'santral', 'salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Kalıcı Oje']]]] as $k => $v) { $p = new ReflectionProperty(App\Services\RandevuAsistan::class, $k); $p->setAccessible(true); $p->setValue($hv, $v); }
    $yarin = date('Y-m-d', strtotime('+1 day'));
    $hv->acilamaz = false;
    $v0 = cagir($hv, 'hizmetsizTur', 'Yarın akşamüstü', ['i' => 'kaynak', 't' => null, 's' => null, 'a' => []]);
    esit('"yarın akşamüstü" → randevu HEMEN açılmaz', null, $v0['aksiyon']);
    icerir('önce önerilir, onay istenir', "akşam saat 10:00 uygun, randevunuzu oluşturayım mı?", $v0['cevap']); // sahte secenekler: akşam -> 10/11
    esit('vakit akşam olarak arandı', 'aksam', end($hv->vakitler));
    $hzO = cagir($hv, 'hizmetsizBekleyen', [['role' => 'assistant', 'content' => 'x', 'baglam' => $v0['baglam']]]);
    $v0b = cagir($hv, 'hizmetsizTur', 'Evet, oluşturun.', $hzO);
    esit('öneriye "evet" → randevu açılır', 'randevu', $v0b['aksiyon']);
    icerir('önerilen saate', "saat 10:00'da randevunuzu oluşturdum", $v0b['cevap']);
    $v0c = cagir($hv, 'hizmetsizTur', 'Hayır, olmasın.', $hzO);
    esit('öneriye "hayır" → hangi saat', 'Tabii, hangi saat olsun?', $v0c['cevap']);
    $v0d = cagir($hv, 'hizmetsizTur', '18 olsun', $hzO);
    icerir('öneri yerine "18 olsun" → 18:00 açılır', "saat 18:00'de randevunuzu oluşturdum", $v0d['cevap'] ?? '');
    $hv->acilamaz = true;
    $v1 = cagir($hv, 'hizmetsizTur', 'Yarın hangi saat mi sayısı bana öğleden sonra randevu verebilirsiniz.', ['i' => 'kaynak', 't' => null, 's' => null, 'a' => []]);
    icerir('yarın öğleden sonra → önce ilk boş saat önerilir', 'öğleden sonra saat 13:00 uygun, randevunuzu oluşturayım mı?', $v1['cevap']);
    esit('öneriler etikete yazıldı', ['13:00'], array_column(cagir($hv, 'hizmetsizBekleyen', [['role' => 'assistant', 'content' => 'x', 'baglam' => $v1['baglam']]])['a'], 1));
    $v2 = cagir($hv, 'hizmetsizTur', 'Öğleden sonra.', ['i' => 'kaynak', 't' => $yarin, 's' => null, 'a' => [], 'q' => 1]);
    icerir('"saat kaçta?" sonrası "Öğleden sonra" → ilk boş saat önerilir', 'saat 13:00 uygun, randevunuzu oluşturayım mı?', $v2['cevap']);
    $hzA = ['i' => 'kaynak', 't' => $yarin, 's' => null, 'a' => [[$yarin, '13:00'], [$yarin, '14:00']]];
    esit('kayıt 316: "14 olsun" → 14:00 seçilir', [$yarin, '14:00'], cagir($hv, 'hizmetsizSaatSec', '14 olsun', $hzA['a']));
    esit('"iki olsun" → 14:00', [$yarin, '14:00'], cagir($hv, 'hizmetsizSaatSec', 'iki olsun', $hzA['a']));
    esit('"saat 1" → 13:00', [$yarin, '13:00'], cagir($hv, 'hizmetsizSaatSec', 'saat 1', $hzA['a']));
    esit('"bir buçuk" → eşleşme yok', null, cagir($hv, 'hizmetsizSaatSec', 'bir bucuk', $hzA['a']));
    $v5 = cagir($hv, 'hizmetsizTur', 'Asıl o diyoruz.', $hzA);
    esit('seçim anlaşılmadı → aynı iki saat kısaca', 'Saat 13:00 mi, saat 14:00 mi?', $v5['cevap']);
    esit('seçim anlaşılmadı → başka saat önerilmedi', ['13:00', '14:00'], array_column(cagir($hv, 'hizmetsizBekleyen', [['role' => 'assistant', 'content' => 'x', 'baglam' => $v5['baglam']]])['a'], 1));
    esit('STT "randevu aldattım" → alacaktım', 'Merhaba, randevu alacaktım ben ama.', $hv->sttDuzelt('Merhaba, randevu aldattım ben ama.'));
    $v3 = cagir($hv, 'hizmetsizTur', 'yarın olsun', ['i' => 'kaynak', 't' => null, 's' => null, 'a' => []]);
    icerir('yalnız gün → ilk kez saat sorulur', 'saat kaçta uygun?', $v3['cevap']);
    $v4 = cagir($hv, 'hizmetsizTur', 'bilmiyorum ki yarın işte', cagir($hv, 'hizmetsizBekleyen', [['role' => 'assistant', 'content' => 'x', 'baglam' => $v3['baglam']]]));
    icerir('saat ikinci kez SORULMAZ → iki saat önerilir', 'saat 10:00 ya da saat 11:00 uygun, hangisini istersiniz?', $v4['cevap']);

    bolum('Kayıt 317: hizmet sorgulanmaz, kapanış isimle ve yumuşak');
    // Model "Kaynak ile ne kastettiğinizi anlayamadım" dedi -> soru yerine gun-saat
    $hq = new class extends App\Services\RandevuAsistan {
        public function __construct() {}
        protected function waOtomatikKayit(): void {}
        protected function cagir(array $m, string $k, int $t = 0, array $e = []): ?array {
            return ['content' => [['type' => 'text', 'text' => 'Kusura bakmayın. Kaynak ile ne kastettiğinizi tam anlayamadım. Hangi hizmet için arıyorsunuz?']]];
        }
    };
    foreach (['salonId' => 20, 'musteriId' => 77, 'kanal' => 'santral', 'salon' => ['ad' => 'X', 'hizmetler' => [['id' => 1, 'ad' => 'Kalıcı Oje']]]] as $k => $v) { $p = new ReflectionProperty(App\Services\RandevuAsistan::class, $k); $p->setAccessible(true); $p->setValue($hq, $v); }
    putenv('ANTHROPIC_API_KEY=test');
    $q1 = $hq->konus('Ben kaynak yapacaktım ama', [['role' => 'assistant', 'content' => 'Tabii. Hangi hizmet için randevu almak istiyorsunuz?']]);
    esit('"ne kastettiniz" sorulmaz → gün-saat', 'Hangi gün ve saat size uygun?', $q1['cevap']);
    icerir('hizmet sorgulanınca → hizmetsiz mod', '[[HZ:', (string) ($q1['baglam'] ?? ''));
    esit('sorgulama tespiti', true, cagir($hq, 'hizmetSorgulamaMi', 'Kaynak ile ne kastettiğinizi tam anlayamadım.'));
    esit('normal soru sorgulama sayılmaz', false, cagir($hq, 'hizmetSorgulamaMi', 'Hangi gün ve saat size uygun?'));
    $p = new ReflectionProperty(App\Services\RandevuAsistan::class, 'musteriAd'); $p->setAccessible(true); $p->setValue($hq, 'Ferdi Yılmaz');
    $p = new ReflectionProperty(App\Services\RandevuAsistan::class, 'musteriCinsiyet'); $p->setAccessible(true); $p->setValue($hq, 1);
    $on = cagir($hq, 'hizmetsizOnayCumlesi', ['tarih' => '2026-11-27', 'saat' => '14:00:00']);
    icerir('kapanış isimle başlar', 'Ferdi Bey, ', $on);
    icerir('kapanış "başka isteğiniz" ile biter', 'Salonumuz sizi arayıp detayları netleştirecek. Başka bir isteğiniz var mı?', $on);
    esit('ardından "yok teşekkürler" → hızlı veda', 'veda', $hq->konus('Yok teşekkürler', [['role' => 'assistant', 'content' => $on]])['aksiyon']);

    bolum('Kayıt 321: akşamüstü, tekrar eden saat');
    $zc = app(App\Services\SesliRandevuCozService::class);
    esit('"yarın akşamüstü" → akşam', 'aksam', $zc->zamanCoz('Yarın akşamüstü')['vakit']);
    esit('"akşam üzeri" → akşam', 'aksam', $zc->zamanCoz('yarın akşam üzeri olsun')['vakit']);
    esit('"öğlen" → öğle', 'ogle', $zc->zamanCoz('yarın öğlen')['vakit']);
    esit('"öğleden sonra" bozulmadı', 'ogleden_sonra', $zc->zamanCoz('yarın öğleden sonra')['vakit']);
    esit('"akşamüstü 6" → 18:00', '18:00', $zc->zamanCoz('akşamüstü 6 gibi')['saat']);
    $onc = 'Ferdi Bey, Cumartesi, 28 Kasım saat 18:00\'de randevunuzu oluşturdum. Salonumuz sizi arayıp detayları netleştirecek. Başka bir isteğiniz var mı?';
    $t1 = cagir($hv, 'hizmetsizTekrarCevabi', '18 dedim ya.', [['role' => 'assistant', 'content' => $onc]]);
    esit('"18 dedim ya" → yeni randevu yok, teyit', 'Evet, randevunuz saat 18:00 için oluşturuldu, merak etmeyin. Başka bir isteğiniz var mı?', $t1['cevap'] ?? null);
    esit('teyitte aksiyon yok', true, array_key_exists('aksiyon', $t1) && $t1['aksiyon'] === null);
    esit('"Oluştur" → teyit', 'bos_onay', cagir($hv, 'hizmetsizTekrarCevabi', 'Oluştur.', [['role' => 'assistant', 'content' => $onc]]) ? 'bos_onay' : null);
    esit('"yok teşekkürler" → teyit değil (veda)', null, cagir($hv, 'hizmetsizTekrarCevabi', 'Yok teşekkürler', [['role' => 'assistant', 'content' => $onc]]));
    esit('"19 olsun" → teyit değil', null, cagir($hv, 'hizmetsizTekrarCevabi', '19 olsun', [['role' => 'assistant', 'content' => $onc]]));

    bolum('Veri seti: telefon STT zaman yanlış duymaları (tests/veri)');
    $st = asistan();
    foreach ([
        ['ölden sonra', 'öğleden sonra'], ['öğleden sonda', 'öğleden sonra'], ['öğleden sora', 'öğleden sonra'], ['öğlenden sonra', 'öğleden sonra'],
        ['akşama doru', 'akşama doğru'], ['öğlene doru', 'öğlene doğru'], ['akşamüst', 'akşamüstü'], ['akşama üstü', 'akşamüstü'],
        ['akşam üzre', 'akşam üzeri'], ['öğür gün', 'öbür gün'], ['yarınna', 'yarına'], ['haftayağ', 'haftaya'], ['cumartesii', 'cumartesi'],
        ['pazartesii', 'pazartesi'], ['beş buçu', 'beş buçuk'], ['yeddi', 'yedi'], ['sekis', 'sekiz'], ['doküs', 'dokuz'],
        ['akşamüstü', 'akşamüstü'], ['öğleden sonra', 'öğleden sonra'], ['randevu aldattım', 'randevu alacaktım'],
    ] as [$duy, $dogru]) esit("STT \"$duy\" → \"$dogru\"", $dogru, $st->sttDuzelt($duy));
    esit('"yarınna öğleden sora" → tarih + vakit', 'ogleden_sonra', $zc->zamanCoz($st->sttDuzelt('yarınna öğleden sora'))['vakit']);
    esit('"beşi çeyrek geçe" akşam/gece sayılmaz → 17:15', '17:15', $zc->zamanCoz('beşi çeyrek geçe')['saat']);
    esit('"on biri çeyrek geçe" → 11:15 (23:15 değil)', '11:15', $zc->zamanCoz('on biri çeyrek geçe')['saat']);
    esit('"yarın değil öbür gün" → öbür gün', '2026-11-17', $zc->zamanCoz('yarın değil öbür gün')['tarih']);
    esit('"akşam değil sabah" → sabah', 'sabah', $zc->zamanCoz('Hayır, akşam değil sabah')['vakit']);
    esit('"beşe beş var" → 16:55', '16:55', $zc->zamanCoz('beşe beş var')['saat']);

    // Niyet veri seti (tests/veri/niyet_cumleleri.tsv): modelsiz KOD kisayollari (aktar / fiyat / veda) icin rapor.
    // Kacirma zararsiz (model yakalar); YANLIS tetikleme zararli -> yalniz yanlis pozitifler raporlanir. NIYET_RAPOR=1 ile ayrinti.
    $nv = asistan();
    $nyRapor = ['kacan' => [], 'yanlis' => []];
    foreach (array_slice(file(__DIR__ . '/veri/niyet_cumleleri.tsv', FILE_IGNORE_NEW_LINES), 1) as $sat) {
        [$cm, $ny] = array_pad(explode("\t", $sat), 2, '');
        if ($cm === '') continue;
        $kod = [
            'yetkiliye_baglan' => cagir($nv, 'aktarmaIstegiMi', $cm),
            'fiyat_sor' => cagir($nv, 'fiyatSorusuMu', $cm),
            'veda' => cagir($nv, 'vedaMi', $cm),
        ];
        foreach ($kod as $k => $v) {
            if ($v && $ny !== $k) $nyRapor['yanlis'][] = "$k ← \"$cm\" ($ny)";
            if (!$v && $ny === $k) $nyRapor['kacan'][] = "$k: \"$cm\"";
        }
    }
    if (getenv('NIYET_RAPOR')) { echo "  kod kaçırdı (model yakalar):\n    " . implode("\n    ", $nyRapor['kacan']) . "\n  yanlış tetik:\n    " . implode("\n    ", $nyRapor['yanlis']) . "\n"; }

    bolum('Kayıt 322: hizmetsiz randevudan sonra saat değişikliği');
    $hv->acilamaz = false;
    $yr = '2026-11-16'; // test bugunu 2026-11-15 -> yarin
    $okE = ['r' => 4, 't' => $yr, 's' => '17:00', 'i' => 'kaynak'];
    $okG = [['role' => 'assistant', 'content' => 'x', 'baglam' => cagir($hv, 'hizmetsizAcilanEtiket', ['randevu_id' => 4, 'tarih' => $yr, 'saat' => '17:00:00', 'ifade' => 'kaynak'])]];
    esit('açılan randevu etiketi geçmişten okunur', $okE, cagir($hv, 'hizmetsizAcilan', $okG));
    $d1 = cagir($hv, 'hizmetsizSonrasi', '17 olmasın.', $okE);
    esit('"17 olmasın" → teyit değil, yeni saat sorulur', 'Tabii, hangi saat olsun?', $d1['cevap'] ?? null);
    $hz1 = cagir($hv, 'hizmetsizBekleyen', [['role' => 'assistant', 'content' => 'x', 'baglam' => $d1['baglam']]]);
    esit('eski randevu taşınacak (r)', 4, $hz1['r'] ?? null);
    $d2 = cagir($hv, 'hizmetsizTur', '18-19 saatlerinde var.', $hz1);
    esit('"18-19 saatlerinde" → randevu taşındı', 'randevu', $d2['aksiyon'] ?? null);
    icerir('taşıma cümlesi', 'randevunuzu', $d2['cevap'] ?? '');
    icerir('taşıma → 18:00 güncellendi', 'saat 18:00 olarak güncelledim', $d2['cevap'] ?? '');
    $d3 = cagir($hv, 'hizmetsizSonrasi', '18 olsun', $okE);
    icerir('randevudan sonra "18 olsun" → doğrudan taşınır', 'saat 18:00 olarak güncelledim', $d3['cevap'] ?? '');
    $d4 = cagir($hv, 'hizmetsizSonrasi', 'Yarın akşam üstü olsun.', $okE);
    icerir('17:00 akşamüstü zaten → teyit (yeni randevu yok)', 'Evet, randevunuz', $d4['cevap'] ?? '');
    esit('teyitte aksiyon yok', true, is_array($d4) && $d4['aksiyon'] === null);
    esit('"Yok teşekkürler" → değişiklik değil (veda yoluna)', null, cagir($hv, 'hizmetsizSonrasi', 'Yok teşekkürler', $okE));
    esit('"17 olmasın" eski teyit kontrolüne de takılmaz', null, cagir($hv, 'hizmetsizTekrarCevabi', '17 olmasın', [['role' => 'assistant', 'content' => "Ferdi Bey, Cumartesi saat 17:00'de randevunuzu oluşturdum. Salonumuz sizi arayıp detayları netleştirecek. Başka bir isteğiniz var mı?"]]));
    esit('yalın saat "18-19 saatlerinde"', '18:00', cagir($hv, 'yalinSaat', '18-19 saatlerinde var'));
    esit('yalın saat "27 Kasım" saat sayılmaz', null, cagir($hv, 'yalinSaat', '27 kasım'));

    bolum('Kayıt 322b: randevudan sonra aynı cümlenin devamı sessiz');
    $okG2 = [['role' => 'user', 'content' => 'Saat 19 gibi bir'], ['role' => 'assistant', 'content' => "Ferdi Bey, Cumartesi saat 19:00'da randevunuzu oluşturdum. Salonumuz sizi arayıp detayları netleştirecek. Başka bir isteğiniz var mı?",
        'baglam' => cagir($hv, 'hizmetsizAcilanEtiket', ['randevu_id' => 4, 'tarih' => $yr, 'saat' => '19:00:00', 'ifade' => 'kaynak'])]];
    $s1 = $hv->konus('Saat 19 gibi bir müsaitlik var mı?', $okG2);
    esit('devam cümlesi → sessiz (konuşma yok)', true, !empty($s1['sessiz']) && $s1['cevap'] === '');
    $s2 = $hv->konus('Saat 20 gibi bir olsun', $okG2);
    icerir('devam ama yeni saat → taşınır', 'saat 20:00 olarak güncelledim', $s2['cevap'] ?? '');
    esit('devam değil "Saat 19 müsait mi?" → teyit konuşulur', true, empty(cagir($hv, 'oncekiCumleDevami', 'Saat 19 müsait mi', $okG2)));

    bolum('Kayıt 277: karşılama tekrarlanmaz');
    $g277 = [['role' => 'assistant', 'content' => "Merhaba, Ola Nail'e hoş geldiniz. Ben Ayşe, size nasıl yardımcı olabilirim?"]];
    foreach (['Merhaba' => true, 'Alo, iyi günler.' => true, 'Merhabalar kolay gelsin' => true, 'Merhaba randevu almak istiyorum' => false, 'Evet' => false] as $m => $b) {
        esit("yalnız selam? \"$m\"", $b, cagir($pz, 'yalnizSelamMi', $m, $g277));
    }
    esit('ortada "alo" selam sayılmaz', false, cagir($pz, 'yalnizSelamMi', 'Alo', array_merge($g277, [['role' => 'user', 'content' => 'x'], ['role' => 'assistant', 'content' => 'y']])));
    esit('hatırlatma aramasında selam kısayolu yok', false, cagir($pz, 'yalnizSelamMi', 'Alo', [['role' => 'assistant', 'content' => 'y', 'hatirlatma' => ['tur' => 'randevu']]]));
    esit('"Merhaba" → kısa cevap', 'Buyurun, sizi dinliyorum.', $pz->konus('Merhaba', $g277)['cevap']);
    foreach ([
        'Merhaba İbrahim Bey, hoş geldiniz. Randevu için aradığınızı anladım; önce adınızı ve soyadınızı alabilir miyim?' => 'Randevu için aradığınızı anladım; önce adınızı ve soyadınızı alabilir miyim?',
        'Merhaba, hoş geldiniz. Size nasıl yardımcı olabilirim?' => 'Buyurun, sizi dinliyorum.',
        'Hoş geldiniz. Hangi gün uygun?' => 'Hangi gün uygun?',
        'Merhaba, ipek kirpik için hangi gün uygun?' => 'İpek kirpik için hangi gün uygun?',
        'Hangi hizmet için arıyorsunuz?' => 'Hangi hizmet için arıyorsunuz?',
    ] as $m => $b) esit("selam kes: $m", $b, cagir($pz, 'tekrarSelamKes', $m, $g277));
    esit('karşılama yapılmadıysa selam kalır', 'Merhaba, hoş geldiniz.', cagir($pz, 'tekrarSelamKes', 'Merhaba, hoş geldiniz.', []));
    esit('"İbrahim de bu için" → "bir randevu için"', 'Merhaba bir randevu için arıyorum.', $pz->sttDuzelt('Merhaba İbrahim de bu için arıyorum.'));
    esit('gerçek İbrahim dokunulmaz', 'Adım İbrahim Demir', $pz->sttDuzelt('Adım İbrahim Demir'));
    esit('"İbrahim de bu saatte" dokunulmaz', 'İbrahim de bu saatte gelsin', $pz->sttDuzelt('İbrahim de bu saatte gelsin'));

    // Kayit 2026-11-23 12.52: "pedikür" -> "Kadıköy" duyuldu -> teyit ("... mı demek istediniz efendim?")
    icerir('prompt: yanlış duyma teyidi', 'Kadıköy mü demek istediniz efendim? Tam anlayamadım.', cagir($pz, 'sistemPromptu'));
    $ab->sttGuven = 0.42; icerir('düşük STT güveni → teyit notu', 'EMİN DEĞİL (güven %42)', cagir($ab, 'sttGuvenNotu'));
    $ab->sttGuven = 0.91; esit('yüksek STT güveni → not yok', '', cagir($ab, 'sttGuvenNotu'));
    $ab->sttGuven = null; esit('STT güveni yok → not yok', '', cagir($ab, 'sttGuvenNotu'));

    echo "\n\033[1mSONUÇ: $gecen geçti, $kalan kaldı\033[0m\n";
    if ($kalan) { echo "Kalanlar:\n - " . implode("\n - ", $hatalar) . "\n"; exit(1); }
}
