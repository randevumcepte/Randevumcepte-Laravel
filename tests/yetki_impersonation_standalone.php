<?php
/**
 * BAGIMSIZ YETKI/IMPERSONATION TEST CALISTIRICISI (phpunit gerekmez)
 * ------------------------------------------------------------------
 * Yerel PHP 8.5 + eski phpunit (~5.7) uyumsuz oldugu, Laravel 5.6 tam boot'u
 * da PHP 8.5'te patladigi icin; bu script SADECE Capsule(Eloquent)+sqlite :memory:
 * + facade'ler + sahte session ile PersonelYetkiServisi'ni izole test eder.
 *
 * Calistir:  php tests/yetki_impersonation_standalone.php
 *            /opt/php74/bin/php tests/yetki_impersonation_standalone.php   (sunucuda)
 *
 * Dogruladigi davranis (regresyon kalkani):
 *   - Rol-5 personel, ozel yetki kaydi YOK -> 'personel' varsayilan sablonu gecerli.
 *   - Impersonation (sysadmin "Hesabina Gir"):
 *       * yetkiliYetkiVar (AKSIYON/sayfa) -> bypass AKTIF (sysadmin 403 yemez)
 *       * menuYetki       (MENU gorunur) -> bypass YOK (menuler personelinki gibi kisitli)
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Events\Dispatcher;

$capsule = new Capsule;
$capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container = $capsule->getContainer();

class SahteSession {
    private $d = [];
    public function has($k) { return array_key_exists($k, $this->d); }
    public function put($k, $v) { $this->d[$k] = $v; }
    public function get($k, $def = null) { return $this->d[$k] ?? $def; }
    public function forget($k) { unset($this->d[$k]); }
}
class SahteLog { public function info($m) {} public function warning($m) {} public function error($m) {} }
$sess = new SahteSession();

$container->instance('db', $capsule->getDatabaseManager());
$container->instance('db.schema', $capsule->getConnection()->getSchemaBuilder());
$container->instance('session', $sess);
$container->instance('session.store', $sess);
$container->instance('log', new SahteLog());
Container::setInstance($container);
Facade::setFacadeApplication($container);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\PersonelYetkiServisi;

Schema::create('salon_personelleri', function ($t) {
    $t->bigIncrements('id'); $t->unsignedBigInteger('salon_id');
    $t->unsignedBigInteger('yetkili_id')->nullable(); $t->unsignedBigInteger('role_id')->nullable();
});
Schema::create('model_has_roles', function ($t) {
    $t->unsignedBigInteger('role_id'); $t->string('model_type')->nullable();
    $t->unsignedBigInteger('model_id'); $t->unsignedBigInteger('salon_id')->nullable();
});
Schema::create('personel_yetki_ayarlari', function ($t) {
    $t->bigIncrements('id'); $t->unsignedBigInteger('personel_id'); $t->unsignedBigInteger('salon_id');
    $t->string('sablon')->default('personel'); $t->longText('ayarlar')->nullable(); $t->timestamps();
});

// Rol-5 personel, OZEL YETKI KAYDI YOK. yetkili_id=777, salon=195, personel id=10
DB::table('salon_personelleri')->insert(['id' => 10, 'salon_id' => 195, 'yetkili_id' => 777, 'role_id' => 5]);
DB::table('model_has_roles')->insert(['role_id' => 5, 'model_type' => 'App\\IsletmeYetkilileri', 'model_id' => 777, 'salon_id' => 195]);

$pass = 0; $fail = 0;
function kontrol($ad, $beklenen, $gercek) {
    global $pass, $fail; $ok = ($beklenen === $gercek);
    echo ($ok ? "  PASS " : "  FAIL ") . $ad . "  (beklenen=" . var_export($beklenen, true) . ", gercek=" . var_export($gercek, true) . ")\n";
    $ok ? $pass++ : $fail++;
}

echo "--- IMPERSONATION YOK ---\n";
kontrol("yetkiliYetkiVar form.olustur = false", false, PersonelYetkiServisi::yetkiliYetkiVar(777, 195, 'form.olustur'));
kontrol("menuYetki form.olustur = false",       false, PersonelYetkiServisi::menuYetki(777, 195, 'form.olustur'));
kontrol("menuYetki randevu.takvim_gor = true",  true,  PersonelYetkiServisi::menuYetki(777, 195, 'randevu.takvim_gor'));
kontrol("menuYetki gorusme.liste_gor = true",   true,  PersonelYetkiServisi::menuYetki(777, 195, 'gorusme.liste_gor'));
kontrol("menuYetki musteri.tum_portfoy_gor = false", false, PersonelYetkiServisi::menuYetki(777, 195, 'musteri.tum_portfoy_gor'));

echo "--- IMPERSONATION AKTIF ---\n";
$sess->put('sysadmin_impersonation_id', 123);
kontrol("yetkiliYetkiVar form.olustur = true (bypass: sysadmin 403 yemez)", true,  PersonelYetkiServisi::yetkiliYetkiVar(777, 195, 'form.olustur'));
kontrol("menuYetki form.olustur = false (MENU bypass YOK = FIX)",           false, PersonelYetkiServisi::menuYetki(777, 195, 'form.olustur'));
kontrol("menuYetki randevu.takvim_gor = true",                             true,  PersonelYetkiServisi::menuYetki(777, 195, 'randevu.takvim_gor'));
kontrol("menuYetki musteri.tum_portfoy_gor = false (header arama gizli)",  false, PersonelYetkiServisi::menuYetki(777, 195, 'musteri.tum_portfoy_gor'));

echo "\nSONUC: $pass PASS, $fail FAIL\n";
exit($fail > 0 ? 1 : 0);
