<?php
/**
 * BAĞIMSIZ ÇAKIŞMA/MÜSAİTLİK TEST ÇALIŞTIRICISI  (phpunit gerekmez)
 * ------------------------------------------------------------------
 * Yerel PHP 8.5 + eski phpunit (~5.7) uyumsuz olduğu için, bu script Laravel'i
 * elle boot edip randevual.dart backend mantığını bellek-içi sqlite üzerinde
 * gerçek verilerle sınar. Sunucuda (php74) tests/Feature/RandevuCakismaTest.php
 * phpunit ile aynı senaryoları koşar.
 *
 * Çalıştır:  php tests/cakisma_standalone.php
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

// ─────────────────────────────────────────────────────────────────────────────
//  BAĞLANTI: pdo_sqlite varsa bellek-içi sqlite (en temiz, izole).
//  Yoksa: AYRI bir MySQL test veritabanı oluştur (production tablolarına DOKUNMAZ),
//  sonunda tamamen SİL. Böylece sunucuda ek kurulum gerekmez.
// ─────────────────────────────────────────────────────────────────────────────
$USE_SQLITE = extension_loaded('pdo_sqlite');
$TEST_PREFIX = 'zzt_cakisma_';
// Testin oluşturduğu/sildiği tablolar (üretim tabloları DEĞİL — hepsi önekli)
$GLOBALS['_TABLOLAR'] = [
    'salonlar', 'salon_calisma_saatleri', 'personel_calisma_saatleri', 'personel_mola_saatleri',
    'salon_personelleri', 'hizmetler', 'salon_sunulan_hizmetler', 'randevular', 'randevu_hizmetler',
    'odalar', 'oda_sunulan_hizmetler', 'cihazlar', 'hizmet_kategorisi',
];
if ($USE_SQLITE) {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
        'database.connections.sqlite.foreign_key_constraints' => false,
    ]);
    DB::purge('sqlite');
    $CONN = 'sqlite';
    echo "Bağlantı: sqlite (bellek-içi)\n";
} else {
    // pdo_sqlite yok → AYNI MySQL veritabanında, '$TEST_PREFIX' önekli AYRI tablolar.
    // Laravel bağlantı 'prefix'i sayesinde controller/modeller otomatik bu önekli
    // tablolara gider; ÜRETİM TABLOLARINA (salonlar, randevular…) HİÇ DOKUNULMAZ.
    // Yalnız CREATE/DROP TABLE gerekir (kullanıcının kendi DB'sinde zaten var olan yetki).
    echo "pdo_sqlite yok → aynı DB'de '$TEST_PREFIX' önekli test tabloları kullanılacak (üretim tablolarına dokunulmaz)\n";
    $my = config('database.connections.mysql');
    if (!$my) { fwrite(STDERR, "HATA: mysql bağlantı ayarı bulunamadı.\n"); exit(2); }
    $my['prefix'] = $TEST_PREFIX;
    config(['database.connections.cakisma_test' => $my, 'database.default' => 'cakisma_test']);
    DB::purge('cakisma_test');
    $CONN = 'cakisma_test';
}

// Test bittiğinde önekli test tablolarını temizle (yalnız MySQL modunda)
register_shutdown_function(function () use ($USE_SQLITE, $CONN) {
    if (!$USE_SQLITE) {
        try {
            $s = Schema::connection($CONN);
            foreach (array_reverse($GLOBALS['_TABLOLAR']) as $t) { $s->dropIfExists($t); }
        } catch (\Throwable $e) { /* yok say */ }
    }
});

// ─────────────────────────────────────────────────────────────────────────────
//  Mini test çatısı
// ─────────────────────────────────────────────────────────────────────────────
$GLOBALS['_gecen'] = 0;
$GLOBALS['_kalan'] = 0;
$GLOBALS['_hatalar'] = [];

function ok($kosul, $ad)
{
    if ($kosul) {
        $GLOBALS['_gecen']++;
        echo "  \033[32m✓\033[0m $ad\n";
    } else {
        $GLOBALS['_kalan']++;
        $GLOBALS['_hatalar'][] = $ad;
        echo "  \033[31m✗ $ad\033[0m\n";
    }
}

$TARIH = '2026-09-10';
$GUN = (int) date('N', strtotime($TARIH));
$SALON = 1;

function semaKur()
{
    global $CONN;
    $s = Schema::connection($CONN);
    foreach (array_reverse($GLOBALS['_TABLOLAR']) as $tbl) {
        $s->dropIfExists($tbl);
    }

    $s->create('salonlar', function (Blueprint $t) {
        $t->increments('id'); $t->string('app_bundle')->nullable(); $t->string('name')->nullable();
        $t->integer('randevu_saat_araligi')->nullable(); $t->integer('cakisma_uyarisi_aktif')->nullable();
        $t->integer('online_saat_kisitlama_aktif')->nullable(); $t->integer('online_gunluk_slot_limiti')->nullable();
    });
    $s->create('salon_calisma_saatleri', function (Blueprint $t) {
        $t->increments('id'); $t->integer('salon_id'); $t->integer('haftanin_gunu');
        $t->integer('calisiyor')->default(1); $t->string('baslangic_saati'); $t->string('bitis_saati');
    });
    $s->create('personel_calisma_saatleri', function (Blueprint $t) {
        $t->increments('id'); $t->integer('personel_id'); $t->integer('haftanin_gunu');
        $t->integer('calisiyor')->default(1); $t->string('baslangic_saati'); $t->string('bitis_saati');
    });
    $s->create('personel_mola_saatleri', function (Blueprint $t) {
        $t->increments('id'); $t->integer('personel_id'); $t->integer('haftanin_gunu');
        $t->integer('mola_var')->default(1); $t->string('baslangic_saati'); $t->string('bitis_saati');
    });
    $s->create('salon_personelleri', function (Blueprint $t) {
        $t->increments('id'); $t->integer('salon_id'); $t->string('personel_adi')->nullable(); $t->integer('aktif')->default(1);
    });
    $s->create('hizmet_kategorisi', function (Blueprint $t) {
        $t->increments('id'); $t->string('kategori_adi')->nullable();
    });
    $s->create('hizmetler', function (Blueprint $t) {
        $t->increments('id'); $t->string('hizmet_adi')->nullable();
        $t->integer('hizmet_kategori_id')->nullable(); $t->integer('personeller_id')->nullable();
    });
    $s->create('salon_sunulan_hizmetler', function (Blueprint $t) {
        $t->increments('id'); $t->integer('salon_id'); $t->integer('hizmet_id');
        $t->integer('sure_dk')->nullable(); $t->integer('hizmet_kategori_id')->nullable();
    });
    $s->create('randevular', function (Blueprint $t) {
        $t->increments('id'); $t->integer('user_id')->nullable(); $t->integer('salon_id'); $t->string('tarih');
        $t->string('saat')->nullable(); $t->string('saat_bitis')->nullable(); $t->integer('durum')->default(0);
        $t->integer('uygulama')->nullable(); $t->integer('salon')->nullable(); $t->integer('web')->nullable();
        $t->integer('olusturan_personel_id')->nullable(); $t->integer('olusturan_user_id')->nullable();
        $t->integer('on_gorusme_id')->nullable(); $t->integer('easistan')->nullable(); $t->text('personel_notu')->nullable();
    });
    $s->create('randevu_hizmetler', function (Blueprint $t) {
        $t->increments('id'); $t->integer('randevu_id'); $t->integer('hizmet_id')->nullable();
        $t->integer('personel_id')->nullable(); $t->integer('cihaz_id')->nullable(); $t->integer('oda_id')->nullable();
        $t->integer('sure_dk')->nullable(); $t->string('fiyat')->nullable(); $t->string('saat')->nullable();
        $t->string('saat_bitis')->nullable(); $t->integer('yardimci_personel')->nullable(); $t->integer('dusum_miktari')->nullable();
    });
    $s->create('odalar', function (Blueprint $t) {
        $t->increments('id'); $t->integer('salon_id'); $t->string('oda_adi')->nullable();
        $t->integer('personel_id')->nullable(); $t->integer('aktifmi')->nullable(); $t->integer('takvim_sirasi')->nullable();
    });
    $s->create('oda_sunulan_hizmetler', function (Blueprint $t) {
        $t->increments('id'); $t->integer('salon_id'); $t->integer('oda_id'); $t->integer('hizmet_id');
    });
    $s->create('cihazlar', function (Blueprint $t) {
        $t->increments('id'); $t->integer('salon_id'); $t->string('cihaz_adi')->nullable(); $t->integer('aktifmi')->nullable();
    });
}

function temelVeri()
{
    global $SALON, $GUN;
    DB::table('salonlar')->insert([
        'id' => $SALON, 'app_bundle' => 'test.bundle', 'name' => 'Test',
        'randevu_saat_araligi' => 15, 'cakisma_uyarisi_aktif' => 0,
        'online_saat_kisitlama_aktif' => 0, 'online_gunluk_slot_limiti' => 0,
    ]);
    DB::table('salon_calisma_saatleri')->insert([
        'salon_id' => $SALON, 'haftanin_gunu' => $GUN, 'calisiyor' => 1,
        'baslangic_saati' => '09:00', 'bitis_saati' => '20:00',
    ]);
    foreach ([10, 20] as $pid) {
        DB::table('salon_personelleri')->insert(['id' => $pid, 'salon_id' => $SALON, 'personel_adi' => 'P' . $pid, 'aktif' => 1]);
        DB::table('personel_calisma_saatleri')->insert([
            'personel_id' => $pid, 'haftanin_gunu' => $GUN, 'calisiyor' => 1,
            'baslangic_saati' => '09:00', 'bitis_saati' => '20:00',
        ]);
    }
    DB::table('hizmetler')->insert(['id' => 100, 'hizmet_adi' => 'Hizmet']);
    DB::table('salon_sunulan_hizmetler')->insert(['salon_id' => $SALON, 'hizmet_id' => 100, 'sure_dk' => 50]);
}

function sifirla() { semaKur(); temelVeri(); }

function randevuEkle($personelId, $saat, $saatBitis, $durum, $odaId = null)
{
    global $SALON, $TARIH;
    $rid = DB::table('randevular')->insertGetId([
        'user_id' => 999, 'salon_id' => $SALON, 'tarih' => $TARIH,
        'saat' => $saat, 'saat_bitis' => $saatBitis, 'durum' => $durum,
    ]);
    DB::table('randevu_hizmetler')->insert([
        'randevu_id' => $rid, 'hizmet_id' => 100, 'personel_id' => $personelId, 'oda_id' => $odaId,
        'sure_dk' => (strtotime($saatBitis) - strtotime($saat)) / 60,
        'saat' => $saat, 'saat_bitis' => $saatBitis, 'fiyat' => '0',
    ]);
    return $rid;
}

function musaitSaatler(array $personeller, array $hizmetler): array
{
    global $SALON, $TARIH;
    $req = Request::create('/x', 'POST', [
        'sube' => (string) $SALON, 'personeller' => $personeller, 'secilenhizmetler' => $hizmetler,
        'randevutarihi' => $TARIH, 'appBundle' => 'test.bundle',
    ]);
    $res = (new ApiController())->randevuTarihSaatAdimi($req);
    if (!isset($res['saatler']) || !is_array($res['saatler'])) return [];
    $bos = array_filter($res['saatler'], fn($x) => $x['dolu'] === '0');
    return array_values(array_map(fn($x) => $x['saat'], $bos));
}

function appRandevuEkle($personelId, $saat, $cakisanEkle = ''): array
{
    global $SALON, $TARIH;
    $req = Request::create('/x', 'POST', [
        'salonid' => (string) $SALON, 'user_id' => 500, 'randevu_id' => '',
        'randevu_tarihi' => $TARIH, 'randevu_saati' => $saat,
        'hizmetler' => [['hizmet_id' => 100, 'personel_id' => (string) $personelId, 'oda_id' => '', 'cihaz_id' => '', 'sure_dk' => 50, 'fiyat' => '0']],
        'notlar' => '', 'randevuKaynak' => 'uygulama', 'durum' => '0',
        'cakisanrandevuekle' => $cakisanEkle, 'appBundle' => 'test.bundle',
    ]);
    return (array) (new ApiController())->randevuekleguncelle($req);
}

function ayariAc() { global $SALON; DB::table('salonlar')->where('id', $SALON)->update(['cakisma_uyarisi_aktif' => 1]); }

// ─────────────────────────────────────────────────────────────────────────────
//  SENARYOLAR
// ─────────────────────────────────────────────────────────────────────────────

echo "\nA) MÜSAİTLİK (randevuTarihSaatAdimi)\n";

sifirla();
randevuEkle(20, '10:00', '10:50', 1);
$bos = musaitSaatler([10], [100]);
ok(in_array('10:00', $bos), 'Başka personelin randevusu seçili personeli bloklamaz (10:00 boş)');

sifirla();
randevuEkle(10, '10:00', '10:50', 1);
$bos = musaitSaatler([10], [100]);
ok(!in_array('09:30', $bos), 'Overlap eden başlangıç bloklanır (09:30→10:20)');
ok(!in_array('10:00', $bos) && !in_array('10:15', $bos), 'Dolu aralık bloklanır (10:00/10:15)');
ok(!in_array('10:45', $bos), 'Süre penceresi hâlâ çakışıyorsa bloklanır (10:45→11:35)');
ok(in_array('11:00', $bos), 'Çakışmayan başlangıç boş (11:00→11:50)');

sifirla();
DB::table('personel_mola_saatleri')->insert(['personel_id' => 10, 'haftanin_gunu' => $GUN, 'mola_var' => 1, 'baslangic_saati' => '16:30', 'bitis_saati' => '20:00']);
$bos = musaitSaatler([10], [100]);
ok(count(array_filter($bos, fn($s) => $s >= '16:30')) === 0, 'Mola içi slotlar müsait değil (>=16:30 yok)');
ok(!in_array('16:00', $bos), 'Molaya taşan başlangıç bloklanır (16:00→16:50)');

sifirla();
randevuEkle(10, '11:00', '11:50', 1);
$bos = musaitSaatler([10, 10], [100, 100]); // toplam 100 dk
ok(!in_array('10:15', $bos), 'Çoklu hizmet toplam süresi overlap bloklar (10:15→11:55)');
ok(in_array('09:00', $bos), 'Çoklu hizmet: çakışmayan başlangıç boş (09:00→10:40)');

echo "\nB) KAYIT ÇAKIŞMA ENGELİ (app / randevuekleguncelle)\n";

sifirla();
randevuEkle(10, '10:00', '10:50', 1);
$res = appRandevuEkle(10, '10:30');
ok(($res['cakismavar'] ?? null) === '1', 'ANA VAKA: app onaylı randevunun üstüne oluşturamaz (ayar KAPALI)');

sifirla();
ayariAc();
randevuEkle(10, '10:00', '10:50', 1);
$res = appRandevuEkle(10, '10:30');
ok(($res['cakismavar'] ?? null) === '1', 'Ayar AÇIK iken de engellenir');

sifirla();
randevuEkle(10, '10:00', '10:50', 0); // bekleyen
$res = appRandevuEkle(10, '10:15');
ok(($res['cakismavar'] ?? null) === '1', 'Bekleyen (durum=0) randevu da çakışma sayılır');

sifirla();
randevuEkle(10, '10:00', '10:50', 1);
$res = appRandevuEkle(10, '10:30', '1'); // yine de oluştur
ok(($res['cakismavar'] ?? null) === '1', 'App çakışmayı override EDEMEZ (sert engel)');

sifirla();
ayariAc();
randevuEkle(20, '10:00', '10:50', 1); // personel 20 dolu
$res = appRandevuEkle(10, '10:00');   // personel 10 boş
ok(($res['cakismavar'] ?? '0') !== '1', 'Farklı personel aynı saat çakışma DEĞİL');

echo "\nC) ODA SERT ENGELİ (odaMusaitlikCakismasi)\n";

sifirla();
DB::table('odalar')->insert(['id' => 1, 'salon_id' => $SALON, 'oda_adi' => 'Oda1', 'personel_id' => null, 'aktifmi' => 1, 'takvim_sirasi' => 1]);
DB::table('oda_sunulan_hizmetler')->insert(['salon_id' => $SALON, 'oda_id' => 1, 'hizmet_id' => 100]);
randevuEkle(20, '10:00', '10:50', 1, 1); // oda 1 dolu
$res = appRandevuEkle(10, '10:30');
ok(($res['cakismavar'] ?? null) === '1', 'Tek uygun oda doluyken app engellenir (ayardan bağımsız)');

sifirla();
foreach ([1, 2] as $oid) {
    DB::table('odalar')->insert(['id' => $oid, 'salon_id' => $SALON, 'oda_adi' => 'Oda' . $oid, 'personel_id' => null, 'aktifmi' => 1, 'takvim_sirasi' => $oid]);
    DB::table('oda_sunulan_hizmetler')->insert(['salon_id' => $SALON, 'oda_id' => $oid, 'hizmet_id' => 100]);
}
randevuEkle(20, '10:00', '10:50', 1, 1); // yalnız oda 1 dolu
$res = appRandevuEkle(10, '10:30');
ok(($res['cakismavar'] ?? '0') !== '1', 'İki odadan biri boşken engel YOK (boş odaya atanır)');

echo "\nD) SALON/WEB davranışı korunmuş mu (ayar bağımlı)\n";

$refl = new ReflectionMethod(ApiController::class, 'kaynak_cakisma_kontrol_api');
$refl->setAccessible(true);

sifirla();
randevuEkle(10, '10:00', '10:50', 1);
$req = Request::create('/x', 'POST', ['randevu_saati' => '10:30', 'randevu_id' => '', 'hizmetler' => [['hizmet_id' => 100, 'personel_id' => '10', 'oda_id' => '', 'cihaz_id' => '', 'sure_dk' => 50]]]);
$msg = $refl->invoke(new ApiController(), $req, [$TARIH], $SALON, true, true);
ok($msg === '', 'Salon/web: ayar KAPALI iken kaynak kontrolü boş döner');

sifirla();
ayariAc();
randevuEkle(10, '10:00', '10:50', 1);
$req = Request::create('/x', 'POST', ['randevu_saati' => '10:30', 'randevu_id' => '', 'hizmetler' => [['hizmet_id' => 100, 'personel_id' => '10', 'oda_id' => '', 'cihaz_id' => '', 'sure_dk' => 50]]]);
$msg = $refl->invoke(new ApiController(), $req, [$TARIH], $SALON, true, true);
ok($msg !== '', 'Salon/web: ayar AÇIK + onaylı çakışma bulunur');

// ─────────────────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 60) . "\n";
printf("GEÇEN: %d   KALAN: %d\n", $GLOBALS['_gecen'], $GLOBALS['_kalan']);
if ($GLOBALS['_kalan'] > 0) {
    echo "\033[31mBAŞARISIZ:\033[0m\n";
    foreach ($GLOBALS['_hatalar'] as $h) echo "  - $h\n";
    exit(1);
}
echo "\033[32mTÜM TESTLER GEÇTİ\033[0m\n";
exit(0);
