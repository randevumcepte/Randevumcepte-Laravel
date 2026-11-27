<?php
/**
 * WHATSAPP ASISTANI EKLENTILERI — BAGIMSIZ BIRIM TESTLERI (Laravel / gercek DB / LLM GEREKMEZ)
 * -------------------------------------------------------------------------------------------
 * Bedava katmanlar (fiyat, kalip, ogrenen onbellek, sohbet), paket sayaci, mesai disi, geri kazanma,
 * gorsel danisman (sema, dogrulama, fiyat hesabi, mime) ve "Kazandirdiklari" rapor hesabi.
 * Facade'lar (Cache/DB/Log/Schema) bellek ici sahtelerle; RandevuAsistan sahte (hizmet listesi + SSS).
 *
 * Calistir:  php tests/wa_asistan_standalone.php
 */
namespace Illuminate\Support\Facades {
    class Cache {
        public static $d = [];
        static function get($k, $v = null) { return self::$d[$k] ?? $v; }
        static function remember($k, $t, $f) { if (!array_key_exists($k, self::$d)) self::$d[$k] = $f(); return self::$d[$k]; }
        static function forever($k, $v) { self::$d[$k] = $v; }
        static function forget($k) { unset(self::$d[$k]); }
        static function add($k, $v, $t) { if (array_key_exists($k, self::$d)) return false; self::$d[$k] = $v; return true; }
    }
    class Log { public static $l = []; static function __callStatic($n, $a) { self::$l[] = [$n, $a[0] ?? '']; } }
    class Schema { static function hasTable($t) { return true; } static function hasColumn($t, $c) { return true; } }
    class DB {
        public static $t = [];
        static function table($t) { return new \FakeSorgu($t); }
        static function raw($s) { return $s; }
    }
}

namespace {
    // ---- bellek ici sorgu (yalniz testlerin kullandigi kadar)
    class FakeSorgu {
        public $t; public $w = [];
        function __construct($t) { $this->t = $t; if (!isset(Illuminate\Support\Facades\DB::$t[$t])) Illuminate\Support\Facades\DB::$t[$t] = []; }
        function where($c, $o = null, $v = null) { if (func_num_args() === 2) { $v = $o; $o = '='; } $this->w[] = [$c, $o, $v]; return $this; }
        function whereIn($c, $v) { $this->w[] = [$c, 'in', $v]; return $this; }
        function whereNull($c) { $this->w[] = [$c, 'null', null]; return $this; }
        function whereNotNull($c) { $this->w[] = [$c, "notnull", null]; return $this; }
        function orderBy() { return $this; } function orderByDesc() { return $this; } function limit() { return $this; }
        protected function uy($r) {
            foreach ($this->w as [$c, $o, $v]) {
                $x = $r[$c] ?? null;
                if ($o === '=' && $x != $v) return false;
                if ($o === 'in' && !in_array($x, $v)) return false;
                if ($o === 'null' && $x !== null) return false;
                if ($o === 'notnull' && $x === null) return false;
                if ($o === '<' && is_numeric($x) && is_numeric($v)) { if (!($x < $v)) return false; continue; }
                if ($o === '>=' && !($x >= (string) $v)) return false;
                if ($o === '<' && !($x < (string) $v)) return false;
            }
            return true;
        }
        function satirlar() { return array_filter(Illuminate\Support\Facades\DB::$t[$this->t], function ($r) { return $this->uy($r); }); }
        function first() { $s = $this->satirlar(); return $s ? (object) reset($s) : null; }
        function exists() { return (bool) $this->satirlar(); }
        function count() { return count($this->satirlar()); }
        function get() { return new FakeKoleksiyon(array_map(function ($r) { return (object) $r; }, array_values($this->satirlar()))); }
        function insert($r) { $r = array_map(function ($x) { return is_object($x) ? (string) $x : $x; }, $r); $r['id'] = count(Illuminate\Support\Facades\DB::$t[$this->t]) + 1; Illuminate\Support\Facades\DB::$t[$this->t][$r['id']] = $r; return true; }
        function insertGetId($r) { $this->insert($r); return count(Illuminate\Support\Facades\DB::$t[$this->t]); }
        function update($v) { $v = array_map(function ($x) { return is_object($x) ? (string) $x : $x; }, $v); $n = 0; foreach ($this->satirlar() as $id => $r) { Illuminate\Support\Facades\DB::$t[$this->t][$id] = array_merge($r, $v); $n++; } return $n; }
        function increment($c) { foreach ($this->satirlar() as $id => $r) Illuminate\Support\Facades\DB::$t[$this->t][$id][$c] = ($r[$c] ?? 0) + 1; }
        function delete() { foreach (array_keys($this->satirlar()) as $id) unset(Illuminate\Support\Facades\DB::$t[$this->t][$id]); }
    }
    class FakeKoleksiyon implements IteratorAggregate {
        public $a; function __construct($a) { $this->a = $a; }
        function map($f) { return new FakeKoleksiyon(array_map($f, $this->a)); }
        function all() { return $this->a; }
        function getIterator(): Iterator { return new ArrayIterator($this->a); }
    }
    function now() { return new class { function subDays($n) { return date('Y-m-d H:i:s', time() - $n * 86400); } function __toString() { return date('Y-m-d H:i:s'); } }; }
    $GLOBALS['TEST_CONFIG'] = [];
    function config($k, $v = null) { return array_key_exists($k, $GLOBALS['TEST_CONFIG']) ? $GLOBALS['TEST_CONFIG'][$k] : $v; }
    function app($c) { return new $c(); }
    function storage_path($p = '') { return sys_get_temp_dir() . '/wa_test_' . getmypid() . '/' . $p; }
}
namespace App\Services {
    class AiKullanimLog { public static $l = []; static function yaz(...$a) { self::$l[] = $a; } }
    class RandevuAsistan {
        public $h; public $sss;
        function __construct($h = [], $sss = []) { $this->h = $h; $this->sss = $sss; }
        function hizmetler(): array { return $this->h; }
        // Gercek kalip santral testinde sinanir; burada WaAsistan::yarimMi icin ayni kalip
        static function sozVeripBirakti(string $c): bool {
            $c = trim($c);
            if ($c === '' || mb_strpos($c, '?') !== false) return false;
            return (bool) preg_match('/\b(bir )?bak(ay[ıi]m|ar[ıi]m|[ıi]yorum|al[ıi]m)\b|kontrol (edeyim|ediyorum|ederim|edelim|edip)|deneyebilirim|deneyeyim|not (ald[ıi]m|ettim)/iu', $c);
        }
        function sssCevabi(string $m): ?string {
            foreach ($this->sss as $tet => $c) if (strpos(WaBedavaKatman::sade($m), $tet) !== false) return $c;
            return null;
        }
    }
}

namespace {
    $kok = __DIR__ . '/../app/';
    foreach (['Services/WaBedavaKatman.php', 'Services/WaPaket.php', 'Services/WaAsistanVeri.php', 'Services/WaGeriKazanim.php',
              'Services/WaGorselDanisman.php', 'Services/WaAsistanRapor.php', 'Services/Gorsel/GorselSaglayici.php',
              'Services/Gorsel/AnthropicGorselSaglayici.php', 'Support/HizmetBilgiIcerik.php'] as $f) require_once $kok . $f;
    // Sahte gorsel saglayici (arayuz yuklendikten SONRA tanimlanir): sabit analiz doner, cagri sayisini tutar
    if (!class_exists('SahteGorselSaglayici', false)) {
        class SahteGorselSaglayici implements App\Services\Gorsel\GorselSaglayici {
            public static $cagri = 0; public static $json = null;
            public function analiz(string $b64, string $mime, string $talimat, array $sema, string $metin = ''): array {
                self::$cagri++;
                return ['json' => self::$json, 'kullanim' => ['girdi' => 1700, 'cikti' => 120], 'maliyet_usd' => 0.0046, 'hata' => null];
            }
        }
    }

    use App\Services\WaBedavaKatman as K;
    use App\Services\WaPaket;
    use App\Services\WaAsistanVeri;
    use App\Services\WaGeriKazanim as GK;
    use App\Services\WaGorselDanisman as G;
    use App\Services\WaAsistanRapor;
    use App\Services\Gorsel\AnthropicGorselSaglayici;

    $gecti = 0; $kaldi = 0;
    function esit($ad, $beklenen, $gercek) {
        global $gecti, $kaldi;
        if ($beklenen === $gercek) { $gecti++; echo "  \033[32m✓\033[0m $ad\n"; return; }
        $kaldi++; echo "  \033[31m✗ $ad\033[0m\n      beklenen: " . var_export($beklenen, true) . "\n      gelen   : " . var_export($gercek, true) . "\n";
    }
    function icerir($ad, $parca, $metin) {
        global $gecti, $kaldi;
        if (is_string($metin) && mb_strpos($metin, $parca) !== false) { $gecti++; echo "  \033[32m✓\033[0m $ad\n"; return; }
        $kaldi++; echo "  \033[31m✗ $ad\033[0m\n      aranan: $parca\n      metin : " . var_export($metin, true) . "\n";
    }
    function baslik($s) { echo "\n\033[1m$s\033[0m\n"; }

    $H = [
        ['id' => 1, 'ad' => 'Protez Tırnak', 'fiyat' => 800, 'sure_dk' => 90],
        ['id' => 2, 'ad' => 'Protez Tırnak Bakım', 'fiyat' => 500, 'sure_dk' => 60],
        ['id' => 3, 'ad' => 'Kalıcı Oje', 'fiyat' => 400, 'sure_dk' => 45],
        ['id' => 4, 'ad' => 'Manikür', 'fiyat' => 250, 'sure_dk' => 30],
        ['id' => 5, 'ad' => 'Lazer Epilasyon Tüm Vücut', 'fiyat' => 2000, 'sure_dk' => 60],
        ['id' => 6, 'ad' => 'Kaş Alımı', 'fiyat' => 100, 'sure_dk' => 15],
        ['id' => 7, 'ad' => 'Saç Kesimi', 'fiyat' => 300, 'sure_dk' => 30],
        ['id' => 8, 'ad' => 'Saç Boyama', 'fiyat' => 900, 'sure_dk' => 120],
        ['id' => 9, 'ad' => 'Nail Art', 'fiyat' => 0, 'sure_dk' => 30],
    ];

    baslik('Bedava katman: siniflandirma');
    esit('sade: Türkçe', 'protez tirnagi kac tl', K::sade('Protez Tırnağı KAÇ TL?'));
    foreach (['protez tırnak ne kadar', 'Fiyatı nedir?', 'kaç TL', 'kaça yapıyorsunuz', 'ücret ne'] as $x) esit("fiyat sorusu: $x", true, K::fiyatSorusuMu($x));
    foreach (['kalıcı oje ne kadar dayanır', 'işlem ne kadar sürer', 'paketimde ne kadar seans kaldı', 'merhaba'] as $x) esit("fiyat sorusu DEĞİL: $x", false, K::fiyatSorusuMu($x));
    foreach (["yarın 3'te yer var mı", 'randevu almak istiyorum', 'saat 14:30 uygun mu', 'cumartesi müsait misiniz', 'randevumu iptal etmek istiyorum', 'perşembe akşam gelebilir miyim'] as $x) esit("randevu niyeti: $x", true, K::randevuNiyetiMi($x));
    foreach (['protez tırnak ne kadar', 'kalıcı oje kaç gün dayanır', 'fiyat bilgisi alabilir miyim', 'otopark var mı'] as $x) esit("randevu niyeti DEĞİL: $x", false, K::randevuNiyetiMi($x));
    foreach (['protez tırnak zarar verir mi', 'manikür nedir', 'kalıcı oje kaç gün gider', 'lazer acıtır mı', 'Hamileyken yapılır mı?'] as $x) esit("bilgi sorusu: $x", true, K::bilgiSorusuMu($x));
    foreach (['tamam', 'Ayşe Yılmaz', 'protez tırnak'] as $x) esit("bilgi sorusu DEĞİL: $x", false, K::bilgiSorusuMu($x));

    baslik('Bedava katman: hizmet bulma');
    $ad = function ($l) { return array_column($l, 'ad'); };
    esit('protez tırnak → tek (bakım elenir)', ['Protez Tırnak'], $ad(K::hizmetBul('protez tırnak ne kadar', $H)));
    esit('ekli: protez tırnağı', ['Protez Tırnak'], $ad(K::hizmetBul('protez tırnağı kaç para', $H)));
    esit('protez tırnak bakımı → bakım', ['Protez Tırnak Bakım'], $ad(K::hizmetBul('protez tırnak bakımı fiyatı', $H)));
    esit('kalıcı oje', ['Kalıcı Oje'], $ad(K::hizmetBul('kalıcı oje fiyatı', $H)));
    esit('lazer (kısmi, uzun kelime)', ['Lazer Epilasyon Tüm Vücut'], $ad(K::hizmetBul('lazer ne kadar', $H)));
    esit('kaş (kısmi, ilk kelime)', ['Kaş Alımı'], $ad(K::hizmetBul('kaş fiyatı', $H)));
    esit('saç → iki seçenek', ['Saç Kesimi', 'Saç Boyama'], $ad(K::hizmetBul('saç ne kadar', $H)));
    esit('manikür', ['Manikür'], $ad(K::hizmetBul('manikür kaç tl', $H)));
    esit('alakasız → boş', [], K::hizmetBul('otopark var mı', $H));

    baslik('Bedava katman: fiyat cevabı');
    esit('tl: tam', '1.450 TL', K::tl(1450));
    esit('tl: kuruşlu', '99,50 TL', K::tl(99.5));
    $f = K::fiyatCevabi('protez tırnak ne kadar', $H);
    icerir('tek hizmet fiyatı', 'Protez Tırnak fiyatımız 800 TL.', $f['cevap']);
    esit('yalnız fiyat sorusunda süre eklenmez', false, mb_strpos($f['cevap'], 'dakika') !== false);
    $f2 = K::fiyatCevabi('protez tırnak fiyatı ve süresi ne', $H);
    icerir('fiyat + süre sorusu: süre', 'yaklaşık 90 dakika', $f2['cevap']);
    icerir('tek hizmet: randevu teklifi', 'randevu oluşturmamı', $f['cevap']);
    esit('tek hizmet: işaret', ['fiyat' => 1], $f['isaret']);
    esit('tek hizmet: hizmet', ['id' => 1, 'ad' => 'Protez Tırnak'], $f['hizmet']);
    $f = K::fiyatCevabi('saç fiyatları', $H);
    icerir('çoklu: liste', "• Saç Kesimi – 300 TL\n• Saç Boyama – 900 TL", $f['cevap']);
    $f = K::fiyatCevabi('fiyatlarınız nedir', $H);
    icerir('hizmet yok → hangi hizmet', 'Hangi hizmetimizin fiyatını', $f['cevap']);
    esit('hizmet yok → işaret yok', [], $f['isaret']);
    $f = K::fiyatCevabi('fiyatı ne kadar', $H, 3);
    icerir('önceki konuşmadaki hizmet', 'Kalıcı Oje fiyatımız 400 TL', $f['cevap']);
    esit('fiyatı tanımsız hizmet → null (AI/aktar)', null, K::fiyatCevabi('nail art ne kadar', $H));

    baslik('Bedava katman: sohbet / memnuniyet');
    $g = [['role' => 'user', 'content' => 'x'], ['role' => 'assistant', 'content' => 'Randevunuz oluşturuldu.']];
    esit('teşekkür → rica', 'sohbet', K::sohbetCevabi('teşekkürler', $g)['kaynak'] ?? null);
    esit('tamam teşekkürler → rica', 'sohbet', K::sohbetCevabi('tamam teşekkür ederim', $g)['kaynak'] ?? null);
    esit('çok teşekkürler iyi günler → rica', 'sohbet', K::sohbetCevabi('çok teşekkürler iyi günler', $g)['kaynak'] ?? null);
    esit('tamam → sessiz', 'sessiz', K::sohbetCevabi('tamam', $g)['kaynak'] ?? null);
    esit('👍 → sessiz', 'sessiz', K::sohbetCevabi('👍', $g)['kaynak'] ?? null);
    $gs = [['role' => 'user', 'content' => 'x'], ['role' => 'assistant', 'content' => 'Perşembe 14:00 için oluşturayım mı?']];
    esit('soru bekleniyor + tamam → AI (onay)', null, K::sohbetCevabi('tamam', $gs));
    esit('ilk mesaj → null (karşılama akışı)', null, K::sohbetCevabi('teşekkürler', []));
    esit('uzun mesaj → null', null, K::sohbetCevabi('teşekkürler ama yarın için başka bir saat var mı acaba', $g));
    esit('memnuniyet 👍', 1, K::memnuniyetCevabi('👍'));
    esit('memnuniyet 👎', 0, K::memnuniyetCevabi('👎'));
    esit('memnuniyet yazı', 1, K::memnuniyetCevabi('memnun kaldım'));
    esit('memnuniyet olumsuz yazı', 0, K::memnuniyetCevabi('memnun kalmadım'));
    esit('memnuniyet değil', null, K::memnuniyetCevabi('yarın gelebilir miyim'));

    baslik('Bedava katman: kalıp kütüphanesi');
    $kal = [['id' => 1, 'tetikleyiciler' => 'kalici oje, kalıcı oje dayanır', 'cevap' => "A cevap\n---\nB cevap", 'kategori' => 'kalici_oje'],
            ['id' => 2, 'tetikleyiciler' => 'protez tirnak', 'cevap' => 'P', 'kategori' => 'protez']];
    esit('kalıp eşleşme (ekli)', 1, K::kalipEsle('kalıcı ojem kaç gün dayanır', $kal)['id'] ?? null);
    esit('kalıp eşleşme protez', 2, K::kalipEsle('protez tırnaklar zarar verir mi', $kal)['id'] ?? null);
    esit('kalıp yok', null, K::kalipEsle('otopark var mı', $kal));
    esit('cevap havuzu (tohum 0)', 'A cevap', K::cevapSec("A cevap\n---\nB cevap", 0));
    esit('cevap havuzu (tohum 1)', 'B cevap', K::cevapSec("A cevap\n---\nB cevap", 1));

    baslik('Bedava katman: öğrenen önbellek');
    foreach (['otopark var mı', 'kalıcı oje tırnağa zarar verir mi', 'kredi kartı geçiyor mu'] as $x) esit("öğrenilebilir: $x", true, K::ogrenilebilirSoru($x));
    foreach (['yarın açık mısınız', 'benim randevum ne zaman', 'protez tırnak ne kadar', 'bu model yapılır mı', 'saat 3 uygun mu', 'tamam'] as $x) esit("öğrenilemez: $x", false, K::ogrenilebilirSoru($x));
    $d = ['cevap' => 'Evet, işletmemizin önünde ücretsiz otopark bulunuyor.', 'aksiyon' => null, 'baglam' => null];
    esit('AI cevabı öğrenilir', true, K::cevapOgrenilebilir('otopark var mı', $d, ['cagri' => 1], null));
    esit('araçlı (2 çağrı) öğrenilmez', false, K::cevapOgrenilebilir('otopark var mı', $d, ['cagri' => 2], null));
    esit('müşteri adı geçen öğrenilmez', false, K::cevapOgrenilebilir('otopark var mı', ['cevap' => 'Ayşe Hanım, otopark var.'] + $d, ['cagri' => 1], 'Ayşe Yılmaz'));
    esit('aktarma cevabı öğrenilmez', false, K::cevapOgrenilebilir('otopark var mı', ['cevap' => 'Talebinizi işletmemize ilettim.'] + $d, ['cagri' => 1], null));
    esit('anahtar: noktalama/büyük harf farkı', K::ogrenilenAnahtar('Otopark var mı?'), K::ogrenilenAnahtar('otopark var mi'));
    Illuminate\Support\Facades\Cache::$d[WaAsistanVeri::SEMA] = 1;
    Illuminate\Support\Facades\Cache::$d['wa_asistan_sema_v1'] = 1;
    K::ogren(236, 'otopark var mı', 'Evet, ücretsiz otoparkımız var.');
    esit('öğrenilen getir (aynı işletme)', 'Evet, ücretsiz otoparkımız var.', K::ogrenilenGetir(236, 'Otopark var mı?'));
    esit('öğrenilen başka işletmede yok', null, K::ogrenilenGetir(20, 'otopark var mı'));
    esit('kullanım sayacı arttı', 1, (int) (Illuminate\Support\Facades\DB::table('wa_asistan_ogrenilen')->where('salon_id', 236)->first()->adet));
    Illuminate\Support\Facades\DB::table('wa_asistan_ogrenilen')->where('salon_id', 236)->update(['aktif' => 0]);
    K::ogren(236, 'otopark var mı', 'Yeni cevap');
    esit('işletmenin sildiği yeniden öğrenilmez', null, K::ogrenilenGetir(236, 'otopark var mı'));

    baslik('Bedava katman: onCevap sırası');
    Illuminate\Support\Facades\Cache::$d['wa_asistan_kaliplar_v1'] = $kal;
    $as = new App\Services\RandevuAsistan($H, ['otopark' => 'Otoparkımız var (işletme cevabı).']);
    $ay = ['fiyat_soyle' => 1];
    esit('fiyat → fiyat katmanı', 'fiyat', K::onCevap(236, $as, 'protez tırnak ne kadar', $g, null, $ay)['kaynak'] ?? null);
    esit('fiyat kapalı → fiyat katmanı yok', null, K::onCevap(236, $as, 'protez tırnak ne kadar', $g, null, ['fiyat_soyle' => 0]));
    esit('randevu niyeti → AI', null, K::onCevap(236, $as, "yarın 3'te protez tırnak için yer var mı", $g, null, $ay));
    esit('işletme SSS ortak kalıptan önce', 'sss', K::onCevap(236, $as, 'otopark var mı', $g, null, $ay)['kaynak'] ?? null);
    $r = K::onCevap(236, $as, 'kalıcı oje kaç gün dayanır', $g, null, $ay);
    esit('bilgi sorusu → kalıp', 'kalip', $r['kaynak'] ?? null);
    icerir('kalıp cevabına randevu teklifi eklenir', 'randevu da oluşturabilirim', $r['cevap'] ?? '');
    // Canli 2026-11-27 Ola Nail: fotograf teklifinden sonra "Toplam fiyatı ne kadar" -> "Hangi hizmetimizin fiyatını..." deniyordu
    $gT = [['role' => 'user', 'content' => '[fotoğraf] Bu model için fiyat alabilir miyim'],
        ['role' => 'assistant', 'content' => "Modelinizi inceledim 😊\nKısa kare tırnakta bambi nail art.\n\nBu model için fiyatımız: 1.640 TL\n(Kalıcı Oje 990 TL + Ombre 400 TL + Nail art × 5 250 TL)\nProtez tırnak çıkarma 300 tl\n\nSize uygun bir gün için randevu oluşturalım mı?",
            'baglam' => 'görsel analiz: bambi; söylenen fiyat 1.640 TL']];
    esit('teklif okunur', '1.640 TL', K::gorselTeklif($gT)['fiyat'] ?? null);
    $r = K::onCevap(236, $as, 'Toplam fiyatı ne kadar', $gT, null, $ay);
    icerir('fotoğraf sonrası "toplam ne kadar" → model toplamı', 'model için toplam fiyatımız 1.640 TL', $r['cevap'] ?? '');
    icerir('kalemler de yazılır', '(Kalıcı Oje 990 TL + Ombre 400 TL', $r['cevap'] ?? '');
    icerir('attığım modelin toplam fiyatı → toplam', '1.640 TL', K::onCevap(236, $as, 'Size attığım modelin toplam fiyatını öğrenmek istiyorum', $gT, null, $ay)['cevap'] ?? '');
    icerir('teklifteki hizmet adı geçse de → toplam', '1.640 TL', K::onCevap(236, $as, 'kalıcı oje ile toplam ne kadar', $gT, null, $ay)['cevap'] ?? '');
    esit('ek işlem (çıkarma) → AI teklifle hesaplar', null, K::onCevap(236, $as, 'Jel çıkartma olacak üstüne bambi deseni toplam ücret ne kadar', $gT, null, $ay));
    esit('teklifte olmayan hizmet → AI', null, K::onCevap(236, $as, 'protez tırnak ile ne kadar olur', $gT, null, $ay));
    esit('fotoğraf yoksa eski akış', 'Hangi hizmetimizin', mb_substr(K::onCevap(236, $as, 'Toplam fiyatı ne kadar', $g, null, $ay)['cevap'] ?? '', 0, 18));
    $gT2 = $gT; $gT2[1]['content'] = 'Modelinizi inceledim: x' . "\n\nHangi uygulamayı düşündüğünüzü yazar mısınız?";
    esit('fiyatsız fotoğraf cevabı → teklif yok', null, K::gorselTeklif($gT2));

    $o = (object) ['memnuniyet_soruldu' => 1, 'memnuniyet' => null, 'son_hizmet_id' => null];
    esit('memnuniyet cevabı yakalanır', 1, K::onCevap(236, $as, '👍', $g, $o, $ay)['memnuniyet'] ?? null);
    esit('tanınmayan soru → AI', null, K::onCevap(236, $as, 'Ayşe Hanım bugün çalışıyor mu', $g, null, $ay));

    baslik('Paket sayacı');
    esit('paket yok → sınırsız', false, WaPaket::hesapla(null, 9999)['kapali']);
    $p = (object) ['dahil_mesaj' => 500, 'asim_davranis' => 'durdur', 'aylik_ucret' => 990];
    esit('%79 → uyarı yok', null, WaPaket::hesapla($p, 399)['seviye']);
    esit('%80 → uyarı', 80, WaPaket::hesapla($p, 400)['seviye']);
    esit('kalan', 100, WaPaket::hesapla($p, 400)['kalan']);
    esit('dolunca kapalı', true, WaPaket::hesapla($p, 500)['kapali']);
    esit('dolunca seviye 100', 100, WaPaket::hesapla($p, 500)['seviye']);
    esit('devam paketinde kapanmaz', false, WaPaket::hesapla((object) ['dahil_mesaj' => 500, 'asim_davranis' => 'devam'], 700)['kapali']);
    esit('devam paketinde aşım', 200, WaPaket::hesapla((object) ['dahil_mesaj' => 500, 'asim_davranis' => 'devam'], 700)['asim']);

    baslik('Mesai dışı');
    $s = [1 => ['acik' => true, 'bas' => '09:00', 'bit' => '19:00'], 7 => ['acik' => false, 'bas' => '', 'bit' => '']];
    $pzt = strtotime('2026-11-16 10:00'); // Pazartesi
    esit('Pzt 10:00 mesai içi', false, WaAsistanVeri::mesaiDisiHesapla($s, $pzt));
    esit('Pzt 20:00 mesai dışı', true, WaAsistanVeri::mesaiDisiHesapla($s, strtotime('2026-11-16 20:00')));
    esit('Pzt 08:59 mesai dışı', true, WaAsistanVeri::mesaiDisiHesapla($s, strtotime('2026-11-16 08:59')));
    esit('Pazar kapalı', true, WaAsistanVeri::mesaiDisiHesapla($s, strtotime('2026-11-22 12:00')));
    esit('tanımsız gün (Sal) varsayılan saat', false, WaAsistanVeri::mesaiDisiHesapla($s, strtotime('2026-11-17 12:00')));
    esit('hiç tanım yok: Pazar dışı', true, WaAsistanVeri::mesaiDisiHesapla([], strtotime('2026-11-22 12:00')));

    baslik('Geri kazanma');
    $now = strtotime('2026-11-19 12:00');
    $ot = function ($ek = []) use ($now) { return (object) array_merge(['randevu_id' => null, 'ret' => 0, 'geri_kazanim_id' => null, 'insan_devraldi' => 0,
        'sonuc' => null, 'fiyat_verildi' => 1, 'saat_onerildi' => 0, 'gorsel_sayisi' => 0, 'bilgi_verildi' => 0, 'jid' => '905321112233@s.whatsapp.net',
        'telefon' => '905321112233', 'son_mesaj_at' => date('Y-m-d H:i:s', $now - 26 * 3600)], $ek); };
    esit('fiyat aldı, 26 saat sessiz → aday', true, GK::adayMi($ot(), 24, $now));
    esit('henüz 10 saat → aday değil', false, GK::adayMi($ot(['son_mesaj_at' => date('Y-m-d H:i:s', $now - 10 * 3600)]), 24, $now));
    esit('çok eski (80 saat) → yazılmaz', false, GK::adayMi($ot(['son_mesaj_at' => date('Y-m-d H:i:s', $now - 80 * 3600)]), 24, $now));
    esit('randevu almış → değil', false, GK::adayMi($ot(['randevu_id' => 5]), 24, $now));
    esit('vazgeçti → değil', false, GK::adayMi($ot(['ret' => 1]), 24, $now));
    esit('aktarılmış → değil (işletme dönecek)', false, GK::adayMi($ot(['sonuc' => 'aktar']), 24, $now));
    esit('hiç bilgi almamış → değil', false, GK::adayMi($ot(['fiyat_verildi' => 0]), 24, $now));
    esit('fotoğrafa fiyat almış → aday', true, GK::adayMi($ot(['fiyat_verildi' => 0, 'gorsel_sayisi' => 1]), 24, $now));
    esit('neden: pahalı', 'pahali', GK::neden($ot(), [['role' => 'user', 'content' => 'biraz pahalı geldi']]));
    esit('neden: düşünme', 'dusunme', GK::neden($ot(), [['role' => 'user', 'content' => 'düşünüp haber veririm']]));
    esit('neden: fotoğraf', 'gorsel', GK::neden($ot(['gorsel_sayisi' => 1]), []));
    esit('neden: fiyat', 'fiyat', GK::neden($ot(), []));
    foreach (['dur', 'DUR', 'istemiyorum', 'hayır teşekkürler', 'lütfen yazmayın'] as $x) esit("ret: $x", true, GK::retMi($x));
    foreach (['yarın gelebilirim', 'olur perşembe uygun', 'durumu nedir'] as $x) esit("ret değil: $x", false, GK::retMi($x));
    $sec = [['tarih' => '2026-11-20', 'gun' => '20 Kasım Perşembe', 'saat' => '14:00', 'personel_id' => 3, 'personel' => 'Ayşe Hanım'],
            ['tarih' => '2026-11-21', 'gun' => '21 Kasım Cuma', 'saat' => '11:30', 'personel_id' => 3, 'personel' => 'Ayşe Hanım']];
    $m = GK::mesajMetni('Merhaba Ayşe Hanım.', 'Protez Tırnak', 'fiyat', $sec, null, 26);
    icerir('mesaj: hitap', 'Merhaba Ayşe Hanım 😊', $m);
    icerir('mesaj: dün + hizmet', 'Dün Protez Tırnak için fiyat bilgisi almıştınız.', $m);
    icerir('mesaj: gerçek saat 1', '• 20 Kasım Perşembe 14:00 (Ayşe Hanım)', $m);
    icerir('mesaj: gerçek saat 2', '• 21 Kasım Cuma 11:30', $m);
    icerir('mesaj: çıkış seçeneği', '"dur" yazmanız yeterli', $m);
    $m = GK::mesajMetni('Merhaba.', null, 'pahali', [], 'Bu haftaya özel %10 indirimimiz var.', 40);
    icerir('mesaj: kampanya yalnız işletmenin cümlesi', 'Bu haftaya özel %10 indirimimiz var.', $m);
    icerir('mesaj: saat yoksa gün sor', 'gün ve saat yazarsanız', $m);
    icerir('mesaj: 40 saat → geçen gün', 'Geçen gün', $m);
    $m = GK::mesajMetni('Merhaba.', 'Kalıcı Oje', 'bilgi', [], 'İNDİRİM', 26);
    esit('mesaj: bilgi nedeninde kampanya eklenmez', false, mb_strpos($m, 'İNDİRİM') !== false);
    icerir('bağlam notu: seçenek id', '2026-11-20T14:00 personel_id=3', GK::baglamNotu(1, $sec));

    baslik('Görsel danışman');
    $sema = G::jsonSemasi('tirnak');
    esit('şema: zorunlu alanlar', ['uygun', 'ana', 'ozellikler', 'aciklama', 'guven', 'soru'], $sema['required']);
    esit('şema: ana enum belirsiz içerir', true, in_array('belirsiz', $sema['properties']['ana']['enum'], true));
    esit('şema: özellik additionalProperties false', false, $sema['properties']['ozellikler']['additionalProperties']);
    esit('şema: tas tam sayı', 'integer', $sema['properties']['ozellikler']['properties']['tas']['type']);
    icerir('talimat: fiyat söyleme', 'FİYAT SÖYLEME', G::talimat('tirnak'));
    $ham = ['uygun' => true, 'ana' => 'protez', 'ozellikler' => ['uzunluk' => 'uzun', 'sekil' => 'badem', 'french' => true, 'tas' => 2, 'nail_art' => 0, 'krom' => 'evet?', 'bilinmeyen' => 5],
            'aciklama' => 'Uzun badem protez tırnak, french ve iki parmakta taş.', 'guven' => 0.9, 'soru' => ''];
    $a = G::analizDogrula($ham, 'tirnak');
    esit('doğrula: ana', 'protez', $a['ana']);
    esit('doğrula: bilinmeyen özellik atılır', false, isset($a['ozellikler']['bilinmeyen']));
    esit('doğrula: bool zorlanır', true, $a['ozellikler']['krom']);
    esit('doğrula: geçersiz seçim null', null, G::analizDogrula(['ana' => 'protez', 'ozellikler' => ['uzunluk' => 'devasa']], 'tirnak')['ozellikler']['uzunluk']);
    esit('doğrula: geçersiz ana null', null, G::analizDogrula(['ana' => 'roket'], 'tirnak')['ana']);
    esit('doğrula: adet sınırı', 20, G::analizDogrula(['ana' => 'protez', 'ozellikler' => ['tas' => 99]], 'tirnak')['ozellikler']['tas']);
    esit('doğrula: boş → null', null, G::analizDogrula(null, 'tirnak'));
    $kur = ['ana:protez' => ['hizmet_id' => 1, 'fiyat' => null], 'uzunluk:uzun' => ['hizmet_id' => null, 'fiyat' => 150.0],
            'french' => ['hizmet_id' => null, 'fiyat' => 200.0], 'tas' => ['hizmet_id' => null, 'fiyat' => 125.0], 'ana:kalici_oje' => ['hizmet_id' => 9, 'fiyat' => 350.0]];
    $f = G::fiyatHesapla($a, $kur, $H, 'tirnak');
    esit('fiyat: toplam 800+150+200+2×125', 1400.0, $f['toplam']);
    esit('fiyat: durum', 'fiyat', $f['durum']);
    esit('fiyat: kalem sayısı', 4, count($f['kalemler']));
    esit('fiyat: hizmet (randevu için)', ['id' => 1, 'ad' => 'Protez Tırnak'], $f['hizmet']);
    esit('fiyat: deterministik', $f, G::fiyatHesapla($a, $kur, $H, 'tirnak'));
    esit('fiyat: hizmet fiyatı 0 → sabit fiyat', 350.0, G::fiyatHesapla(['ana' => 'kalici_oje', 'ozellikler' => [], 'uygun' => true], $kur, $H, 'tirnak')['toplam']);
    esit('fiyat: kural yok', 'kural_yok', G::fiyatHesapla(['ana' => 'jel_guclendirme', 'ozellikler' => [], 'uygun' => true], $kur, $H, 'tirnak')['durum']);
    esit('fiyat: ana belirsiz', 'belirsiz', G::fiyatHesapla(['ana' => null, 'ozellikler' => [], 'uygun' => true], $kur, $H, 'tirnak')['durum']);
    esit('fiyat: uygun değil', 'uygun_degil', G::fiyatHesapla(['ana' => 'protez', 'uygun' => false], $kur, $H, 'tirnak')['durum']);
    $c = G::cevapMetni($a, $f, 'Mevcut protez varsa söküm 150 TL eklenir.');
    icerir('cevap: tarif', 'Uzun badem protez tırnak', $c);
    icerir('cevap: toplam', 'Bu model için fiyatımız: 1.400 TL', $c);
    icerir('cevap: döküm', 'Protez tırnak 800 TL + Uzunluk (Uzun) 150 TL + French 200 TL + Taş süsleme (taşlı parmak) × 2 250 TL', $c);
    icerir('cevap: işletme notu', 'söküm 150 TL', $c);
    icerir('cevap: randevu teklifi', 'randevu oluşturalım mı', $c);
    icerir('cevap: düşük güven ifadesi', 'Fotoğraftan anladığım kadarıyla', G::cevapMetni(['guven' => 0.5] + $a, $f, null));
    icerir('cevap: belirsiz → soru', 'Protez mi kalıcı oje mi?', G::cevapMetni(['soru' => 'Protez mi kalıcı oje mi?', 'aciklama' => ''] + $a, ['durum' => 'belirsiz'], null));
    icerir('cevap: uygun değil', 'modeli göremedim', G::cevapMetni($a, ['durum' => 'uygun_degil'], null));
    icerir('bağlam: hizmet_id', 'hizmet_id=1', G::baglamNotu($a, $f));
    esit('mime: jpeg', 'image/jpeg', G::mimeTespit("\xFF\xD8\xFF\xE0abc"));
    esit('mime: png', 'image/png', G::mimeTespit("\x89PNG\r\n\x1A\nabc"));
    esit('mime: webp', 'image/webp', G::mimeTespit('RIFF1234WEBPVP8 '));
    esit('mime: pdf reddedilir', null, G::mimeTespit('%PDF-1.4'));
    esit('kural anahtarı: ana', true, G::kuralAnahtariGecerli('ana:protez', 'tirnak'));
    esit('kural anahtarı: seçim', true, G::kuralAnahtariGecerli('uzunluk:uzun', 'tirnak'));
    esit('kural anahtarı: bilgi alanı fiyatlanmaz', false, G::kuralAnahtariGecerli('sekil:badem', 'tirnak'));
    esit('kural anahtarı: adet', true, G::kuralAnahtariGecerli('tas', 'tirnak'));
    esit('kural anahtarı: geçersiz', false, G::kuralAnahtariGecerli('roket', 'tirnak'));
    esit('sektör: saç şeması', true, isset(G::jsonSemasi('sac')['properties']['ana']));
    esit('json ayıkla: kod bloğu', ['a' => 1], AnthropicGorselSaglayici::jsonAyikla("```json\n{\"a\":1}\n```"));
    esit('json ayıkla: düz', ['a' => 1], AnthropicGorselSaglayici::jsonAyikla('{"a":1}'));
    esit('json ayıkla: bozuk', null, AnthropicGorselSaglayici::jsonAyikla('yok'));
    Illuminate\Support\Facades\Cache::$d[G::SEMA] = 1;
    G::kurallariKaydet(236, 'tirnak', ['ana:protez' => ['hizmet_id' => 1], 'french' => ['fiyat' => '200,50'], 'roket' => ['fiyat' => 5], 'tas' => ['fiyat' => '']]);
    $k = G::kurallar(236, 'tirnak');
    esit('kural kaydet: geçerliler', ['ana:protez', 'french'], array_keys($k));
    esit('kural kaydet: virgüllü fiyat', 200.5, $k['french']['fiyat']);

    baslik('Kazandırdıkları raporu');
    $v = [
        'cevaplar' => [
            ['oturum_id' => 1, 'kaynak' => 'fiyat', 'ai' => 0, 'sure_sn' => 3, 'mesai_disi' => 0, 'aksiyon' => null, 'randevu_id' => null, 'maliyet_usd' => 0, 'created_at' => '2026-11-10 14:00:00'],
            ['oturum_id' => 1, 'kaynak' => 'ai', 'ai' => 1, 'sure_sn' => 7, 'mesai_disi' => 0, 'aksiyon' => 'randevu', 'randevu_id' => 100, 'maliyet_usd' => 0.004, 'created_at' => '2026-11-10 14:05:00'],
            ['oturum_id' => 2, 'kaynak' => 'ai', 'ai' => 1, 'sure_sn' => 5, 'mesai_disi' => 1, 'aksiyon' => 'randevu', 'randevu_id' => 101, 'maliyet_usd' => 0.003, 'created_at' => '2026-11-10 23:10:00'],
            ['oturum_id' => 3, 'kaynak' => 'kalip', 'ai' => 0, 'sure_sn' => 2, 'mesai_disi' => 0, 'aksiyon' => null, 'randevu_id' => null, 'maliyet_usd' => 0, 'created_at' => '2026-11-11 10:00:00'],
            ['oturum_id' => 5, 'kaynak' => 'geri_kazanim', 'ai' => 0, 'sure_sn' => null, 'mesai_disi' => 0, 'aksiyon' => null, 'randevu_id' => null, 'maliyet_usd' => 0, 'created_at' => '2026-11-12 10:00:00'],
            ['oturum_id' => 5, 'kaynak' => 'ai', 'ai' => 1, 'sure_sn' => 6, 'mesai_disi' => 0, 'aksiyon' => 'randevu', 'randevu_id' => 102, 'maliyet_usd' => 0.004, 'created_at' => '2026-11-12 11:00:00'],
        ],
        'oturumlar' => [
            ['id' => 1, 'anahtar' => 'a', 'ad' => 'A', 'telefon' => '1', 'musteri_tel' => null, 'sonuc' => 'randevu', 'randevu_id' => 100, 'fiyat_verildi' => 1, 'bilgi_verildi' => 0, 'saat_onerildi' => 1, 'gorsel_sayisi' => 1, 'son_hizmet_ad' => 'Protez Tırnak', 'memnuniyet' => 1, 'ret' => 0, 'sessiz_bitis' => null, 'sessiz_neden' => null, 'geri_kazanim_id' => null, 'son_mesaj_at' => '2026-11-10 14:05:00', 'created_at' => '2026-11-10 14:00:00'],
            ['id' => 2, 'anahtar' => 'b', 'ad' => 'B', 'telefon' => '2', 'musteri_tel' => null, 'sonuc' => 'randevu', 'randevu_id' => 101, 'fiyat_verildi' => 0, 'bilgi_verildi' => 0, 'saat_onerildi' => 1, 'gorsel_sayisi' => 0, 'son_hizmet_ad' => 'Kalıcı Oje', 'memnuniyet' => 0, 'ret' => 0, 'sessiz_bitis' => null, 'sessiz_neden' => null, 'geri_kazanim_id' => null, 'son_mesaj_at' => '2026-11-10 23:10:00', 'created_at' => '2026-11-10 23:00:00'],
            ['id' => 3, 'anahtar' => 'c', 'ad' => 'C', 'telefon' => '3', 'musteri_tel' => null, 'sonuc' => null, 'randevu_id' => null, 'fiyat_verildi' => 1, 'bilgi_verildi' => 1, 'saat_onerildi' => 0, 'gorsel_sayisi' => 0, 'son_hizmet_ad' => 'Protez Tırnak', 'memnuniyet' => null, 'ret' => 0, 'sessiz_bitis' => null, 'sessiz_neden' => null, 'geri_kazanim_id' => null, 'son_mesaj_at' => '2026-11-11 10:00:00', 'created_at' => '2026-11-11 10:00:00'],
            ['id' => 4, 'anahtar' => 'd', 'ad' => 'D', 'telefon' => '4', 'musteri_tel' => null, 'sonuc' => 'aktar', 'randevu_id' => null, 'fiyat_verildi' => 0, 'bilgi_verildi' => 0, 'saat_onerildi' => 0, 'gorsel_sayisi' => 0, 'son_hizmet_ad' => null, 'memnuniyet' => null, 'ret' => 0, 'sessiz_bitis' => date('Y-m-d H:i:s', time() + 3600), 'sessiz_neden' => 'aktar', 'geri_kazanim_id' => null, 'son_mesaj_at' => '2026-11-11 11:00:00', 'created_at' => '2026-11-11 11:00:00'],
            ['id' => 5, 'anahtar' => 'c', 'ad' => 'C', 'telefon' => '3', 'musteri_tel' => null, 'sonuc' => 'randevu', 'randevu_id' => 102, 'fiyat_verildi' => 0, 'bilgi_verildi' => 0, 'saat_onerildi' => 1, 'gorsel_sayisi' => 0, 'son_hizmet_ad' => 'Protez Tırnak', 'memnuniyet' => null, 'ret' => 0, 'sessiz_bitis' => null, 'sessiz_neden' => null, 'geri_kazanim_id' => 1, 'son_mesaj_at' => '2026-11-12 11:00:00', 'created_at' => '2026-11-12 10:00:00'],
        ],
        'randevular' => [100 => ['tutar' => 800.0, 'geldi' => true, 'iptal' => false, 'yeni' => true], 101 => ['tutar' => 400.0, 'geldi' => false, 'iptal' => false, 'yeni' => false],
                         102 => ['tutar' => 800.0, 'geldi' => true, 'iptal' => false, 'yeni' => false]],
        'geri_kazanim' => [['id' => 1, 'durum' => 'geldi', 'cevap_at' => '2026-11-12 10:30:00', 'randevu_id' => 102, 'geldi' => 1, 'tutar' => 800.0, 'ad' => 'C', 'telefon' => '3', 'hizmet_ad' => 'Protez Tırnak', 'neden' => 'fiyat', 'gonderildi_at' => '2026-11-12 10:00:00'],
                           ['id' => 2, 'durum' => 'ret', 'cevap_at' => '2026-11-12 12:00:00', 'randevu_id' => null, 'geldi' => null, 'tutar' => null, 'ad' => 'E', 'telefon' => '5', 'hizmet_ad' => null, 'neden' => 'bilgi', 'gonderildi_at' => '2026-11-12 11:00:00']],
        'gorseller' => [['id' => 1, 'oturum_id' => 1, 'durum' => 'fiyat', 'toplam' => 1400, 'onbellek' => 0], ['id' => 2, 'oturum_id' => 3, 'durum' => 'fiyat', 'toplam' => 1400, 'onbellek' => 1]],
    ];
    $o = WaAsistanRapor::ozetHesapla($v, (object) ['aylik_ucret' => 1000]);
    esit('cevap: toplam', 6, $o['cevap']['toplam']);
    esit('cevap: ücretsiz', 3, $o['cevap']['ucretsiz']);
    esit('cevap: AI (paketten)', 3, $o['cevap']['ai']);
    esit('cevap: ort süre (geri kazanma hariç)', 5, $o['cevap']['ort_sure_sn']);
    esit('cevap: mesai dışı', 1, $o['cevap']['mesai_disi']);
    esit('randevu: asistan (geri kazanma ayrı)', 2, $o['randevu']['adet']);
    esit('randevu: tutar', 1200.0, $o['randevu']['tutar']);
    esit('randevu: geldi', 1, $o['randevu']['geldi']);
    esit('randevu: yeni müşteri', 1, $o['randevu']['yeni_musteri']);
    esit('randevu: mesai dışı', 1, $o['randevu']['mesai_disi']);
    esit('randevu: mesai dışı tutar', 400.0, $o['randevu']['mesai_disi_tutar']);
    esit('geri kazanma: yazıldı', 2, $o['geri_kazanim']['yazildi']);
    esit('geri kazanma: geldi + tutar', [1, 800.0], [$o['geri_kazanim']['geldi'], $o['geri_kazanim']['tutar']]);
    esit('geri kazanma: ret', 1, $o['geri_kazanim']['ret']);
    esit('kazanç: gerçekleşen (800 + 800)', 1600.0, $o['kazanc']['gerceklesen']);
    esit('kazanç: paketin katı', 1.6, $o['kazanc']['kat']);
    esit('sohbet: memnuniyet oranı', 50, $o['sohbet']['memnuniyet_orani']);
    esit('sohbet: çözülen oranı (1 aktar / 5)', 80, $o['sohbet']['cozulen_orani']);
    esit('sohbet: dönüş bekleyen aktarma', 1, $o['sohbet']['aktar_bekleyen']);
    esit('kaçan: yalnız 3 numaralı sohbet', [3], array_column($o['kacan'], 'oturum_id'));
    esit('kaçan: neden', 'Fiyat aldı', $o['kacan'][0]['neden']);
    esit('görsel: fiyat verilen / randevu / önbellek', [2, 1, 1], [$o['gorsel']['fiyat_verildi'], $o['gorsel']['randevu'], $o['gorsel']['onbellek']]);
    esit('huni', ['yazdi' => 5, 'bilgi' => 4, 'randevu' => 3, 'geldi' => 2], $o['huni']);
    esit('popüler hizmet', ['Protez Tırnak' => 3, 'Kalıcı Oje' => 1], $o['populer_hizmet']);
    esit('kart: fiyat verilen sohbetler (TL + mesaj)', ['tutar' => 800.0, 'mesaj' => 3], $o['kart']['fiyat']);
    esit('kart: kendi bitirdiği (aktar hariç)', ['tutar' => 2000.0, 'mesaj' => 6], $o['kart']['cozulen']);
    esit('kart: fotoğraflı sohbetler', ['tutar' => 800.0, 'mesaj' => 3], $o['kart']['gorsel']);
    esit('kart: geri kazanma sohbetleri', ['tutar' => 800.0, 'mesaj' => 2], $o['kart']['geri_kazanim']);
    esit('saat dağılımı: 24 saat, 14:00 = 2 mesaj', [24, 2, 1], [count($o['saat_dagilim']), $o['saat_dagilim'][14], $o['saat_dagilim'][23]]);
    esit('boş veri çökmez', 0,WaAsistanRapor::ozetHesapla(['cevaplar' => [], 'oturumlar' => [], 'randevular' => [], 'geri_kazanim' => []])['cevap']['toplam']);

    baslik('Genel bakış: dönem, trend, seri');
    $n = strtotime('2026-11-19 14:00');
    esit('son 30 gün (bugün dahil)', ['2026-10-21 00:00:00', '2026-11-20 00:00:00', '2026-09-21 00:00:00', '2026-10-21 00:00:00'], WaAsistanRapor::donemAraligi('30g', $n));
    esit('son 7 gün', ['2026-11-13 00:00:00', '2026-11-20 00:00:00', '2026-11-06 00:00:00', '2026-11-13 00:00:00'], WaAsistanRapor::donemAraligi('7g', $n));
    esit('bu ay (önceki = geçen ayın aynı uzunluğu)', ['2026-11-01 00:00:00', '2026-11-20 00:00:00', '2026-10-01 00:00:00', '2026-10-20 00:00:00'], WaAsistanRapor::donemAraligi('bu_ay', $n));
    esit('geçen ay', ['2026-10-01 00:00:00', '2026-11-01 00:00:00', '2026-09-01 00:00:00', '2026-10-01 00:00:00'], WaAsistanRapor::donemAraligi('gecen_ay', $n));
    esit('bilinmeyen dönem → 30 gün', WaAsistanRapor::donemAraligi('30g', $n), WaAsistanRapor::donemAraligi('xyz', $n));
    esit('trend artış', 24, WaAsistanRapor::trend(124, 100));
    esit('trend düşüş', -50, WaAsistanRapor::trend(5, 10));
    esit('trend önceki 0 → null', null, WaAsistanRapor::trend(5, 0));
    esit('trend veri yok → null', null, WaAsistanRapor::trend(null, 3));
    $v['oturumlar'][0]['mesaj_sayisi'] = 6; $v['oturumlar'][1]['mesaj_sayisi'] = 4; $v['oturumlar'][2]['mesaj_sayisi'] = 2;
    esit('gelen mesaj toplamı', 12, WaAsistanRapor::ozetHesapla($v)['sohbet']['gelen_mesaj']);
    $s = WaAsistanRapor::seri($v, '2026-11-10 00:00:00', '2026-11-13 00:00:00', 'gun');
    esit('seri: gün etiketleri', ['2026-11-10', '2026-11-11', '2026-11-12'], $s['etiket']);
    esit('seri: mesaj (sohbet başlangıç günü)', [10, 2, 0], $s['mesaj']);
    esit('seri: randevu (iptal hariç, tekil)', [2, 0, 1], $s['randevu']);
    esit('seri: yeni müşteri', [1, 0, 0], $s['yeni']);
    esit('seri: memnuniyet', [[1, 0, 0], [1, 0, 0]], [$s['memnun'], $s['memnun_degil']]);
    esit('seri: medyan cevap süresi', [5, 2, 6], $s['sure']);
    $s = WaAsistanRapor::seri($v, '2026-09-01 00:00:00', '2026-12-01 00:00:00', 'ay');
    esit('seri: ay kovaları', ['2026-09', '2026-10', '2026-11'], $s['etiket']);
    esit('seri: ay toplam randevu', [0, 0, 3], $s['randevu']);
    $s = WaAsistanRapor::seri($v, '2026-11-10 00:00:00', '2026-11-13 00:00:00', 'gun');
    esit('kart serisi: randevu tutarı', [1200.0, 0.0, 800.0], $s['tutar']);
    esit('kart serisi: mesai dışı tutar', [400.0, 0.0, 0.0], $s['mesai_tutar']);
    esit('kart serisi: cevap (geri kazanma hariç)', [3, 1, 1], $s['cevap']);
    esit('kart serisi: mesai dışı yazışma', [1, 0, 0], $s['mesai_cevap']);
    esit('kart serisi: tek başına çözülen %', [100, 50, 100], $s['cozulen']);
    esit('kart serisi: geri kazanılan', [0, 0, 1], $s['geri_kazanim']);
    esit('kart serisi: fotoğrafa fiyat (tarihsiz kayıt sayılmaz)', [0, 0, 0], $s['gorsel']);
    // Olcum kaydi olmayan (eski) randevu: sohbetin randevu_id'sinden, son mesaj gunune
    $v2 = $v; $v2['cevaplar'] = []; $v2['oturumlar'] = [['randevu_id' => 100, 'son_mesaj_at' => '2026-11-11 15:00:00'] + $v['oturumlar'][0]];
    $s2 = WaAsistanRapor::seri($v2, '2026-11-10 00:00:00', '2026-11-13 00:00:00', 'gun');
    esit('cevap kaydı olmayan randevu da grafikte', [0.0, 800.0, 0.0], $s2['tutar']);
    esit('cevap kaydı olmayan randevu: randevu sayısı', [0, 1, 0], $s2['randevu']);
    esit('kart ile grafik aynı toplam', WaAsistanRapor::ozetHesapla($v2)['randevu']['tutar'], array_sum($s2['tutar']));

    baslik('Entegrasyon: ayarlar');
    $T = &Illuminate\Support\Facades\DB::$t;
    esit('ayar: varsayılanlar', 1, WaAsistanVeri::ayarlar(236)['fiyat_soyle']);
    WaAsistanVeri::ayarKaydet(236, ['fiyat_soyle' => 0, 'gk_bekle_saat' => 999, 'gk_kampanya' => '  %10 indirim  ', 'gorsel_sektor' => 'yok_boyle'], 'Test');
    $ay = WaAsistanVeri::ayarlar(236);
    esit('ayar: kapatıldı', 0, $ay['fiyat_soyle']);
    esit('ayar: bekleme üst sınır 168', 168, $ay['gk_bekle_saat']);
    esit('ayar: kampanya kırpıldı', '%10 indirim', $ay['gk_kampanya']);
    esit('ayar: geçersiz sektör yok sayılır', 'tirnak', $ay['gorsel_sektor']);
    esit('ayar: başka işletme etkilenmez', 1, WaAsistanVeri::ayarlar(20)['fiyat_soyle']);

    baslik('Entegrasyon: görsel danışman (sahte sağlayıcı)');
    $GLOBALS['TEST_CONFIG']['services.wa_asistan.gorsel_saglayici'] = SahteGorselSaglayici::class;
    SahteGorselSaglayici::$json = $ham;
    G::kurallariKaydet(300, 'tirnak', ['ana:protez' => ['hizmet_id' => 1], 'uzunluk:uzun' => ['fiyat' => 150], 'french' => ['fiyat' => 200], 'tas' => ['fiyat' => 125]]);
    $jpeg = "\xFF\xD8\xFF\xE0" . str_repeat('x', 200);
    $gid = G::fotografKaydet(300, '905321112233', base64_encode($jpeg));
    esit('fotoğraf kaydı', true, $gid > 0);
    esit('fotoğraf diske yazıldı', true, is_file(storage_path('app/' . $T['wa_gorsel_analizler'][$gid]['dosya'])));
    $r = G::isle($gid, 300, 77, $H, 'bunu yapabilir misiniz');
    esit('analiz: sağlayıcı çağrıldı', 1, SahteGorselSaglayici::$cagri);
    esit('analiz: AI sayılır', true, $r['ai']);
    esit('analiz: fiyat durumu', 'fiyat', $r['durum']);
    icerir('analiz: müşteri cevabı fiyatlı', '1.400 TL', $r['cevap']);
    esit('analiz: randevu hizmeti', ['id' => 1, 'ad' => 'Protez Tırnak'], $r['hizmet']);
    esit('analiz: kayıt toplamı', 1400.0, (float) $T['wa_gorsel_analizler'][$gid]['toplam']);
    esit('analiz: AI kullanım kaydı', 1, count(App\Services\AiKullanimLog::$l));
    $gid2 = G::fotografKaydet(300, '905321112233', base64_encode($jpeg));
    $r2 = G::isle($gid2, 300, 78, $H);
    esit('aynı fotoğraf: sağlayıcı tekrar çağrılmaz', 1, SahteGorselSaglayici::$cagri);
    esit('aynı fotoğraf: bedava', false, $r2['ai']);
    esit('aynı fotoğraf: aynı fiyat', $r['cevap'], $r2['cevap']);
    esit('aynı fotoğraf: önbellek işaretli', 1, (int) $T['wa_gorsel_analizler'][$gid2]['onbellek']);
    SahteGorselSaglayici::$json = ['uygun' => true, 'ana' => 'jel_guclendirme', 'ozellikler' => [], 'aciklama' => 'Jel', 'guven' => 0.9, 'soru' => ''];
    $gid3 = G::fotografKaydet(300, 'x', base64_encode("\x89PNG\r\n\x1A\n" . 'farkli'));
    $rk = G::isle($gid3, 300, 79, $H);
    esit('kural yok → işletmeye sorulur (talep), asistan susmaz', ['fotograf', null, 'kural_yok'], [$rk['talep'], $rk['aksiyon'], $rk['durum']]);
    icerir('kural yok → müşteriye cevap + randevuya devam', 'Bu arada randevunuzu ayarlayalım mı', $rk['cevap']);
    SahteGorselSaglayici::$json = null;
    $gid4 = G::fotografKaydet(300, 'x', base64_encode("\x89PNG\r\n\x1A\n" . 'baska'));
    $r4 = G::isle($gid4, 300, 80, $H);
    esit('analiz başarısız → hata', 'hata', $r4['durum']);
    icerir('analiz başarısız → müşteriye yazması istenir', 'kısaca yazar mısınız', $r4['cevap']);
    esit('geçersiz dosya (pdf) reddedilir', null, G::fotografKaydet(300, 'x', base64_encode('%PDF-1.4 xx')));
    esit('bozuk base64 reddedilir', null, G::fotografKaydet(300, 'x', '***'));
    WaAsistanVeri::ayarKaydet(301, ['gorsel_fiyat' => 0]);
    $gid5 = G::fotografKaydet(301, 'x', base64_encode($jpeg . 'y'));
    $once = SahteGorselSaglayici::$cagri;
    $rk5 = G::isle($gid5, 301, 81, $H);
    esit('fotoğraf fiyatı kapalı → talep, cevap var, AI çağrılmaz', ['fotograf', true, $once], [$rk5['talep'], $rk5['cevap'] !== '', SahteGorselSaglayici::$cagri]);

    baslik('Entegrasyon: geri kazanma cevabı');
    Illuminate\Support\Facades\Cache::$d[GK::SEMA] = 1;
    $T['wa_geri_kazanim'][1] = ['id' => 1, 'durum' => 'gonderildi', 'cevap_at' => null, 'salon_id' => 236];
    $T['wa_asistan_oturumlari'][9] = ['id' => 9, 'geri_kazanim_id' => 1, 'ret' => 0];
    esit('olumlu cevap → normal akış (null)', null, GK::cevapGeldi(1, 'olur perşembe uygun'));
    esit('olumlu cevap → durum cevap', 'cevap', $T['wa_geri_kazanim'][1]['durum']);
    $T['wa_geri_kazanim'][2] = ['id' => 2, 'durum' => 'gonderildi', 'cevap_at' => null, 'salon_id' => 236];
    $T['wa_asistan_oturumlari'][10] = ['id' => 10, 'geri_kazanim_id' => 2, 'ret' => 0];
    icerir('dur → nazik veda', 'tekrar yazmayacağız', GK::cevapGeldi(2, 'dur'));
    esit('dur → ret', 'ret', $T['wa_geri_kazanim'][2]['durum']);
    esit('dur → sohbet ret işaretli', 1, $T['wa_asistan_oturumlari'][10]['ret']);

    baslik('Entegrasyon: paket sayacı');
    Illuminate\Support\Facades\Cache::$d[WaPaket::SEMA] = 1;
    require_once $kok . 'Services/SantralPaket.php';
    $ayBas = date('Y-m-01 10:00:00');
    for ($i = 0; $i < 3; $i++) WaAsistanVeri::cevapKaydet(['salon_id' => 236, 'isletme_id' => 236, 'kaynak' => 'ai', 'ai' => 1]);
    WaAsistanVeri::cevapKaydet(['salon_id' => 236, 'isletme_id' => 236, 'kaynak' => 'fiyat', 'ai' => 0]);
    WaAsistanVeri::cevapKaydet(['salon_id' => 20, 'isletme_id' => 20, 'kaynak' => 'ai', 'ai' => 1]);
    esit('yalnız AI cevapları sayılır (bu salon)', 3, WaPaket::kullanilan(236));
    esit('başka salonun kullanımı karışmaz', 1, WaPaket::kullanilan(20));

    baslik('Fotoğraf: hizmet listesinden otomatik eşleştirme + eksik ek ücret');
    $oto = G::otomatikKurallar($H, 'tirnak');
    esit('oto: protez → "Protez Tırnak" (bakım değil)', 1, $oto['ana:protez']['hizmet_id']);
    esit('oto: kalıcı oje / manikür eşleşti', [3, 4], [$oto['ana:kalici_oje']['hizmet_id'], $oto['ana:klasik_oje']['hizmet_id']]);
    esit('oto: listede jel yok → eşleşme yok', false, isset($oto['ana:jel_guclendirme']));
    $otoS = G::otomatikKurallar($H, 'sac');
    esit('oto saç: boya / kesim', [8, 7], [$otoS['ana:komple_boya']['hizmet_id'], $otoS['ana:kesim']['hizmet_id']]);
    esit('oto: fiyatı 0 olan hizmet seçilmez', [], G::otomatikKurallar([['id' => 9, 'ad' => 'Kalıcı Oje', 'fiyat' => 0]], 'tirnak'));
    $fe = G::fiyatHesapla(['uygun' => true, 'ana' => 'kalici_oje', 'ozellikler' => ['french' => true, 'krom' => true, 'tas' => 2]], ['ana:kalici_oje' => ['hizmet_id' => 3], 'french' => ['fiyat' => 100]], $H, 'tirnak');
    esit('eksik ek: krom + taş ücreti tanımsız', [500.0, ['Krom / ayna efekti', 'Taş süsleme']], [$fe['toplam'], $fe['eksik']]);
    $ce = G::cevapMetni(['aciklama' => 'Krom efektli kalıcı oje', 'guven' => 0.9], $fe, null);
    icerir('eksik ek: fiyat "-den başlıyor"', "500 TL'den başlıyor", $ce);
    icerir('eksik ek: işletmeye soruldu notu', 'Krom / ayna efekti ve taş süsleme için ek ücret olabilir; işletmemize sordum', $ce);
    esit('liste metni', 'French, krom ve taş', G::listeMetni(['French', 'Krom', 'Taş']));
    SahteGorselSaglayici::$json = ['uygun' => true, 'ana' => 'kalici_oje', 'ozellikler' => [], 'aciklama' => 'Kalıcı oje', 'guven' => 0.95, 'soru' => ''];
    $gid6 = G::fotografKaydet(302, 'x', base64_encode("\x89PNG\r\n\x1A\n" . 'oto-esles'));
    $r6 = G::isle($gid6, 302, 82, $H);
    esit('kural girilmemiş işletme: listeden otomatik fiyat, talep yok', ['fiyat', 400.0, null], [$r6['durum'], (float) $T['wa_gorsel_analizler'][$gid6]['toplam'], $r6['talep']]);
    SahteGorselSaglayici::$json = null;

    baslik('Talep (işletmeye sorulan konu) — saf');
    require_once $kok . 'Services/WaTalep.php';
    $TL = App\Services\WaTalep::class;
    esit('konu: fiyat', 'fiyat', $TL::konuBul('protez tırnak ne kadar'));
    esit('konu: yetkili', 'yetkili', $TL::konuBul('yetkili biriyle görüşmek istiyorum'));
    esit('konu: şikâyet', 'sikayet', $TL::konuBul('dün yaptırdım rezalet oldu'));
    esit('konu: diğer', 'diger', $TL::konuBul('otopark var mı'));
    icerir('iletildi: susmaz, devam eder', 'Bu arada randevu ya da başka bir konuda yardımcı olabilirim', $TL::iletildiMetni('fiyat', false));
    icerir('iletildi: tekrar talepte yeni bildirim yok', 'iletmiştim', $TL::iletildiMetni('yetkili', true));
    icerir('takip: randevu yoksa randevu teklifi', 'randevunuzu oluşturabilirim', $TL::takipMetni('fotograf', false));
    esit('takip: randevu varsa teklif yok', false, mb_strpos($TL::takipMetni('fotograf', true), 'oluşturabilirim') !== false);
    esit('süre metni', ['7 dk', '2 sa', '1 gün'], [$TL::sureMetni(420), $TL::sureMetni(7300), $TL::sureMetni(90000)]);

    baslik('Yanlış numara / kapanış mesajı (saf)');
    require_once $kok . 'Services/WaAsistan.php';
    $WA = App\Services\WaAsistan::class;
    esit('yanlış numara: "Ben Leyla Ahmet değilim"', true, $WA::yanlisNumaraMi('Ben Leyla Ahmet değilim yalnis numara'));
    esit('yanlış numara: "numaramı nerden buldunuz"', true, $WA::yanlisNumaraMi('İstemiyorum benim numaramı nerden buldunuz'));
    esit('yanlış numara: "atmayın bana mesaj"', true, $WA::yanlisNumaraMi('Sizi şikayet edicem atmayın bana mesaj'));
    esit('yanlış numara: kayıtlı ad "ben leyla değilim"', true, $WA::yanlisNumaraMi('ben leyla değilim', 'Leyla Ahmet'));
    esit('yanlış numara değil: "Ben müsait değilim"', false, $WA::yanlisNumaraMi('Ben müsait değilim yarın olur mu'));
    esit('yanlış numara değil: "Ben Ayşe, yarın 3te"', false, $WA::yanlisNumaraMi('Ben Ayşe, yarın 3te gelebilir miyim'));
    esit('kapanış: tamam / teşekkürler / 👍', [true, true, true], [$WA::kapanisMi('tamam'), $WA::kapanisMi('Teşekkür ederim 🙏'), $WA::kapanisMi('👍')]);
    esit('kapanış değil: soru', false, $WA::kapanisMi('Fatma hanım olabilir mi'));
    $yt = 'Yol tarifini size mesaj olarak ilettim. Başka yardımcı olabileceğim bir konu var mı?';
    $dongu = [['role' => 'user', 'content' => 'Konum'], ['role' => 'assistant', 'content' => $yt], ['role' => 'user', 'content' => 'Boş mesaj bu'], ['role' => 'assistant', 'content' => $yt],
              ['role' => 'user', 'content' => '[fotoğraf]'], ['role' => 'assistant', 'content' => 'Fotoğrafta modeli göremedim. Tekrar gönderir misiniz?']];
    esit('yarım: aynı cevap tekrarlandı → tekrar', 'tekrar', $WA::yarimMi($dongu));
    esit('yarım: bir kez gözden geçirildi → tekrar tetiklenmez', null, $WA::yarimMi(array_merge($dongu, [['role' => 'assistant', 'content' => 'Adres...', 'gozden' => 1]])));
    esit('yarım: müşteri "gelmedi hâlâ" → şikayet', 'sikayet', $WA::yarimMi([['role' => 'user', 'content' => 'mesaj gelmedi hala'], ['role' => 'assistant', 'content' => 'Hangi gün uygun?']]));
    esit('yarım değil: sonuçlu cevap', null, $WA::yarimMi([['role' => 'user', 'content' => 'yarın 3'], ['role' => 'assistant', 'content' => 'Yarın 15:00 uygun, oluşturayım mı?']]));
    esit('yarım: cevapsız müşteri mesajı', 'cevapsiz', $WA::yarimMi([['role' => 'user', 'content' => 'Fatma hanım olabilir mi']]));
    // ---- SALON REKLAMI (WaReklam, saf)
    require_once $kok . 'Services/WaReklam.php';
    $RK = App\Services\WaReklam::class;
    $rkl = [['id' => 7, 'baslik' => 'Protez kampanyası', 'metin' => '1.200 yerine 950 TL', 'hizmetler' => [11, 12], 'token' => 'abc'],
            ['id' => 8, 'baslik' => 'Saç bakım', 'metin' => '', 'hizmetler' => [30], 'token' => 'def']];
    esit('reklam: ilgili hizmette seçilir', 7, ($RK::sec($rkl, 12) ?? [])['id'] ?? null);
    esit('reklam: ilgisiz hizmette seçilmez', null, $RK::sec($rkl, 99));
    esit('reklam: hizmet yoksa seçilmez', null, $RK::sec($rkl, null));
    esit('reklam: id ile bul', 'Saç bakım', ($RK::bul($rkl, 8) ?? [])['baslik'] ?? null);
    esit('reklam ret: "kampanya istemiyorum"', true, $RK::retMi('Kampanya istemiyorum'));
    esit('reklam ret: "reklam mesajı göndermeyin"', true, $RK::retMi('reklam mesajı göndermeyin lütfen'));
    esit('reklam ret: "İndirim mesajları gelmesin"', true, $RK::retMi('İndirim mesajları gelmesin'));
    esit('reklam ret değil: "randevu istemiyorum"', false, $RK::retMi('randevu istemiyorum artık'));
    esit('reklam ret değil: "kampanya var mı"', false, $RK::retMi('kampanya var mı'));
    esit('fiyat sorusu: "Lazer epilasyon ne kadar" (canlı)', true, $RK::fiyatSorusuMu('Lazer epilasyon ne kadar'));
    // (tek basina "kaça" fiyat sayilmaz: "saat kaça kadar açıksınız" — ortak kural WaBedavaKatman::fiyatSorusuMu)
    esit('fiyat sorusu: "protez kaça yapıyorsunuz", "ücreti nedir", "indirim var mı"', [true, true, true],
        [$RK::fiyatSorusuMu('protez kaça yapıyorsunuz'), $RK::fiyatSorusuMu('Ücreti nedir'), $RK::fiyatSorusuMu('indirim var mı')]);
    esit('reklam tetiklemez: "saat kaça kadar açıksınız"', false, $RK::fiyatSorusuMu('saat kaça kadar açıksınız'));
    esit('fiyat sorusu değil: "yarın 3te olur mu"', false, $RK::fiyatSorusuMu('yarın 3te olur mu'));
    esit('reklam tetiklemez: "işlem ne kadar sürer", "paketimde ne kadar seans kaldı"', [false, false],
        [$RK::fiyatSorusuMu('işlem ne kadar sürer'), $RK::fiyatSorusuMu('paketimde ne kadar seans kaldı')]);
    esit('kampanya sorusu: "Kampanyanız var mı"', true, $RK::kampanyaSorusuMu('Kampanyanız var mı'));
    esit('secListe: eşleşen hizmetlerden ilk kampanya', 8, ($RK::secListe($rkl, [99, 30, 12]) ?? [])['id'] ?? null);
    esit('secListe: hiçbiri uymuyor', null, $RK::secListe($rkl, [99]));
    $rm = $RK::mesaj($rkl[0], 'https://x/k/abc?g=5');
    esit('reklam mesajı: başlık + metin + link + ret bilgisi', [true, true, true, true],
        [strpos($rm, 'Protez kampanyası') !== false, strpos($rm, '950 TL') !== false, strpos($rm, 'https://x/k/abc?g=5') !== false, strpos($rm, 'kampanya istemiyorum') !== false]);
    $soruReklam = [['role' => 'user', 'content' => 'protez kaç para'], ['role' => 'assistant', 'content' => 'Protez Tırnak 1.200 TL. Randevu oluşturmamı ister misiniz?'],
        ['role' => 'assistant', 'content' => $rm, 'reklam' => 7]];
    esit('reklam sonrası: önceki soru hâlâ cevap bekliyor (5 dk hatırlatma çalışır)', true, $WA::cevapBekliyorMu($soruReklam));
    esit('reklam sonrası: yarım sayılmaz (reklam mesajı akış değil)', null, $WA::yarimMi($soruReklam));
    esit('cevap bekliyor: saat seçimi sorusu (canlı Selin Aktaş)', true, $WA::cevapBekliyorMu([['role' => 'user', 'content' => 'Sadece bu saatler mi bos'],
        ['role' => 'assistant', 'content' => 'Şu an yarın için bu iki saat boş görünüyor: 10:00 ve 11:30. Hangisi size uygun olur?']]));
    esit('selam: model selamladıysa tekrar eklenmez', 'Merhaba Rabia Hanım, pazar 12:00-22:00 açığız.', $WA::selamEkle('Merhaba Rabia Hanım.', 'Merhaba Rabia Hanım, pazar 12:00-22:00 açığız.'));
    esit('selam: emoji + "merhabalar" da selam sayılır', '🌸 Merhabalar! Buyurun.', $WA::selamEkle('Merhaba.', '🌸 Merhabalar! Buyurun.'));
    esit('selam: model selamlamadıysa eklenir', 'Merhaba Rabia Hanım. Pazar 12:00-22:00 açığız.', $WA::selamEkle('Merhaba Rabia Hanım.', 'Pazar 12:00-22:00 açığız.'));
    esit('selam: "İyi günler" (büyük İ) selam sayılır', 'İyi günler, yardımcı olayım.', $WA::selamEkle('Merhaba.', 'İyi günler, yardımcı olayım.'));
    esit('yarım değil: "tamam"', null, $WA::yarimMi([['role' => 'assistant', 'content' => 'Randevunuz oluşturuldu.'], ['role' => 'user', 'content' => 'tamam']]));

    baslik('Gece Denetçisi (saf)');
    require_once $kok . 'Services/WaDenetci.php';
    $D = App\Services\WaDenetci::class;
    $g = [
        ['role' => 'user', 'content' => 'Merhaba damla hanımdan randevu almak istiyorum'],
        ['role' => 'assistant', 'content' => 'Merhaba, hoş geldiniz. Adınızı ve soyadınızı öğrenebilir miyim?'],
        ['role' => 'user', 'content' => 'Cumartesi günü saat 12 uygun mu'],
        ['role' => 'assistant', 'content' => 'Adınızı ve soyadınızı öğrenebilir miyim?', 'baglam' => 'musait_saatler: 12:00 boş'],
        ['role' => 'user', 'content' => 'Feyza Musabeyoğlu, demiştim zaten'],
        ['role' => 'assistant', 'content' => 'Ben bakarım', 'personel' => 1],
        ['role' => 'assistant', 'content' => 'Randevunuzu oluşturdum: Cumartesi 12:00.'],
        ['role' => 'user', 'content' => ''],
    ];
    $tr = $D::transkript($g);
    esit('transkript: boş mesaj atlanır, numaralı', [7, 'PERSONEL', 6], [count($tr), $tr[5]['rol'], $tr[5]['no']]);
    icerir('transkript metni: araç sonucu notu', 'asistanın araç sonucu', $D::transkriptMetni($tr));
    $mt = $D::metrikler($g, 'randevu');
    esit('metrik: müşteri/asistan/personel', [3, 3, 1], [$mt['musteri_mesaj'], $mt['asistan_mesaj'], $mt['personel_mesaj']]);
    esit('metrik: tekrar soru yakalandı', 1, $mt['tekrar_soru']);
    esit('metrik: "demiştim/zaten" → anlamadı sinyali', 1, $mt['anlamadi']);
    esit('metrik: randevuya kadar 3 müşteri mesajı', [true, 3], [$mt['randevu'], $mt['randevuya_kadar']]);
    esit('kesit: 4 numaralı asistan cevabından önce (müşteriyle biter)', 3, count($D::kesit($g, 4)));
    esit('kesit: son eleman müşteri', 'user', $D::kesit($g, 4)[2]['role']);
    esit('kesit: müşteri mesaj no → o mesaj dahil', 3, count($D::kesit($g, 3)));
    esit('kesit: geçersiz → boş', [], $D::kesit([], 2));

    // Asistan size soruyor: kolay cevap formu
    $hz = [['id' => 10, 'ad' => 'Kalıcı Oje Çıkarma', 'fiyat' => 0], ['id' => 11, 'ad' => 'Medikal Pedikür', 'fiyat' => 990], ['id' => 12, 'ad' => 'Manikür', 'fiyat' => 500]];
    $ft = $D::formTemizle(['tip' => 'fiyat', 'kalemler' => [['ad' => 'Kalıcı oje çıkarma', 'hizmet_id' => 10, 'oneri_fiyat' => 400], ['ad' => 'Kedi gözü', 'hizmet_id' => 0, 'oneri_fiyat' => -5], ['ad' => '', 'hizmet_id' => 3, 'oneri_fiyat' => 1]]]);
    esit('form: fiyat kalemleri temizlenir (boş ad atılır, negatif fiyat 0)', [2, 400, 0], [count($ft['kalemler']), $ft['kalemler'][0]['oneri_fiyat'], $ft['kalemler'][1]['oneri_fiyat']]);
    esit('form: evet/hayır cümlesi eksikse metin', 'metin', $D::formTemizle(['tip' => 'evet_hayir', 'evet_cevap' => 'Evet var.', 'hayir_cevap' => ''])['tip']);
    esit('form: hizmet_sec ifadesiz → metin', 'metin', $D::formTemizle(['tip' => 'hizmet_sec', 'ifade' => ' '])['tip']);
    esit('form: geçersiz tip → metin', 'metin', $D::formTemizle(['tip' => 'xyz'])['tip']);
    $sf = $D::soruFormu('x', json_encode($ft), $hz);
    esit('soruFormu: salondaki ad + güncel fiyat, salonda olmayan id sıfırlanır', ['Kalıcı Oje Çıkarma', 0, 0], [$sf['kalemler'][0]['ad'], $sf['kalemler'][0]['mevcut'], $sf['kalemler'][1]['hizmet_id']]);
    $eski = $D::soruFormu('Medikal pedikür fiyatı nedir?', null, $hz);
    esit('soruFormu: formsuz eski fiyat sorusu → fiyat formu (mevcut fiyatla)', ['fiyat', 11, 990], [$eski['tip'], $eski['kalemler'][0]['hizmet_id'], $eski['kalemler'][0]['mevcut']]);
    esit('soruFormu: formsuz bilgi sorusu → metin', 'metin', $D::soruFormu('Otopark var mı?', null, $hz)['tip']);
    esit('ders güvenli: davranış', true, $D::dersGuvenliMi('Müşteri ilk mesajda gün ve saat yazdıysa önce o saate bak, karşılama yazma.'));
    esit('ders güvensiz: fiyat', false, $D::dersGuvenliMi('Protez tırnak için 750 TL söyle ve indirim teklif et.'));
    esit('ders güvensiz: kampanya', false, $D::dersGuvenliMi('Her müşteriye kampanyadan bahset ve kapora iste.'));
    esit('ders güvensiz: çok kısa', false, $D::dersGuvenliMi('Kısa yaz.'));
    $k = function ($g, $kz) { return ['grup' => $g, 'kazanan' => $kz]; };
    esit('test: yeni sorunlu anlarda kazandı, iyiler bozulmadı → geçti', true, $D::testKarari([$k('kanit', 'yeni'), $k('kanit', 'yeni'), $k('kanit', 'esit'), $k('iyi', 'esit'), $k('iyi', 'yeni'), $k('iyi', 'eski')])['gecti']);
    esit('test: yarıdan azında kazandı → kaldı', false, $D::testKarari([$k('kanit', 'yeni'), $k('kanit', 'esit'), $k('kanit', 'esit'), $k('kanit', 'eski')])['gecti']);
    esit('test: iyi sohbetlerin çoğu bozuldu → kaldı', false, $D::testKarari([$k('kanit', 'yeni'), $k('kanit', 'yeni'), $k('iyi', 'eski'), $k('iyi', 'eski'), $k('iyi', 'esit')])['gecti']);
    esit('test: örnek yok → kaldı', false, $D::testKarari([])['gecti']);
    $ss = $D::sonucSatiri(['puan' => 140, 'ozet' => 'İstek atlandı', 'sorunlar' => [['tur' => 'istek_atlandi', 'mesaj_no' => 2, 'aciklama' => 'x', 'daha_iyi_cevap' => 'y'], ['tur' => 'uydurma', 'mesaj_no' => 1]], 'ders_onerileri' => [], 'bilgi_eksikleri' => []]);
    esit('sonuç: puan 0-100 arası', 100, $ss['puan']);
    esit('sonuç: bilinmeyen sorun türü atılır', 1, count(json_decode($ss['sorunlar'], true)));
    $jl = $D::jsonlCoz("{\"custom_id\":\"oturum-5\",\"result\":{\"type\":\"succeeded\"}}\n\nbozuk\n{\"custom_id\":\"oturum-7\",\"result\":{\"type\":\"errored\"}}\n");
    esit('batch sonuç JSONL', ['oturum-5' => 'succeeded', 'oturum-7' => 'errored'], array_map(function ($r) { return $r['type']; }, $jl));
    $sm = $D::denetimSemasi();
    esit('şema: tüm nesneler kapalı (structured outputs)', [false, false], [$sm['additionalProperties'], $sm['properties']['sorunlar']['items']['additionalProperties']]);
    esit('şema: tüm alanlar zorunlu', ['puan', 'ozet', 'sorunlar', 'ders_onerileri', 'bilgi_eksikleri'], $sm['required']);
    $pr = $D::denetimParametreleri($g, $mt, 'Salon: Ola Nail');
    esit('batch isteği: denetçi modeli + şema', ['claude-sonnet-5-5', 'json_schema'], [$pr['model'], $pr['output_config']['format']['type']]);
    icerir('batch isteği: işletme bağlamı + yazışma', 'Salon: Ola Nail', $pr['messages'][0]['content']);

    baslik('Hatırlatma saat uyduramaz (2026-11-22 canlı)');
    $W = App\Services\WaAsistan::class;
    $gS = [['role' => 'user', 'content' => 'Yarın Damla hanım işe dönüyor mu'], ['role' => 'assistant', 'content' => 'Hangi hizmetle ilgileniyorsunuz ve saat tercihiniz var mı?']];
    esit('önceki mesajda saat yok → uydurulan 10:00 reddedilir', false, $W::hatirlatmaSaatleriGecerli("Yarın *10:00*'da randevunuzu oluşturayım mı?", $gS));
    esit('saatsiz hatırlatma geçerli', true, $W::hatirlatmaSaatleriGecerli('Hangi hizmetle ilgilendiğinizi yazarsanız hemen bakayım 🙂', $gS));
    $gS2 = [['role' => 'assistant', 'content' => 'Yarın 10:00 ya da 11:30 uygun, hangisini istersiniz?']];
    esit('önerilen saatlerden biri → geçerli', true, $W::hatirlatmaSaatleriGecerli("Yarın *10:00* için randevunuzu oluşturayım mı?\n🔄 11:30'u tercih ederseniz yazın", $gS2));
    esit('önerilmeyen saat → geçersiz', false, $W::hatirlatmaSaatleriGecerli('Yarın 14:00 oluşturayım mı?', $gS2));

    @array_map('unlink', glob(storage_path('app/wa_gorsel/*/*')) ?: []);

    echo "\n\033[1mSONUÇ: $gecti geçti, $kaldi kaldı\033[0m\n";
    exit($kaldi ? 1 : 0);
}
