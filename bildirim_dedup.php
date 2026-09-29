<?php
/**
 * Bildirim token dedup analiz + temizlik (Laravel bootstrap, php74, proje kokunde).
 *
 * Kullanim:
 *   php bildirim_dedup.php                 -> ANALIZ (hicbir sey degistirmez)
 *   php bildirim_dedup.php 432             -> sadece salon 432 analiz
 *   php bildirim_dedup.php 432 temizle     -> salon 432 duplicate'leri PASIFLESTIR (aktif=0)
 *   php bildirim_dedup.php all temizle     -> TUM salonlar duplicate temizligi
 *
 * Duplicate tanimi: ayni (kullanici + platform) icin >1 AKTIF token.
 * Her grupta EN GUNCEL (en buyuk id) tutulur, digerleri aktif=0 yapilir.
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); die('CLI only'); }
error_reporting(E_ERROR | E_PARSE);

$arg1 = $argv[1] ?? null;                 // salon_id veya "all" (yoksa analiz-tum)
$apply = (($argv[2] ?? '') === 'temizle');

require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$q = DB::table('bildirim_kimlikleri')->where('aktif', 1);
if ($arg1 && $arg1 !== 'all') { $q->where('salon_id', (int)$arg1); }
$rows = $q->orderByDesc('id')->get([
    'id','bildirim_id','cihaz','platform','kullanici_tipi',
    'isletme_yetkili_id','user_id','salon_id','app_bundle','son_kullanim_tarihi'
]);
$rows = json_decode(json_encode($rows), true);

echo "==============================================\n";
echo "Kapsam: ".($arg1 ?: 'TUM salonlar')."   Mod: ".($apply ? 'TEMIZLE (yazacak)' : 'ANALIZ (salt-okunur)')."\n";
echo "Toplam aktif token: ".count($rows)."\n";
echo "==============================================\n\n";

// Grupla: sahip anahtari = kullanici_tipi + (user_id|isletme_yetkili_id) + platform + salon_id
function ownerKey($r){
    $who = $r['kullanici_tipi']==='musteri'
        ? 'u'.$r['user_id']
        : 'p'.$r['isletme_yetkili_id'];
    return $who.'|'.($r['platform'] ?: 'NULL').'|s'.$r['salon_id'];
}

$groups = [];
foreach ($rows as $r) { $groups[ownerKey($r)][] = $r; }

// WEB platformu dedup DISI: ayni kullanicinin birden cok tarayicisi mesrudur.
// Sadece mobil (ios/android) icin en guncel token tutulur.
$dupGroups = 0; $toDeactivate = [];
foreach ($groups as $key => $list) {
    if (count($list) < 2) continue;          // duplicate yok
    $platform = $list[0]['platform'] ?: 'NULL';
    $isWeb = ($platform === 'web');
    $dupGroups++;
    // en buyuk id = en guncel (get zaten orderByDesc id) -> ilk eleman tutulur
    $keep = $list[0];
    echo "── GRUP: $key  (".count($list)." aktif token)".($isWeb ? "  [WEB — DOKUNULMAYACAK]" : "")."\n";
    foreach ($list as $r) {
        if ($isWeb) { $flag = 'WEB '; }
        else        { $flag = ($r['id']==$keep['id']) ? 'TUT ' : 'SIL '; }
        $tok = substr($r['bildirim_id'],0,18);
        echo "   [$flag] id={$r['id']} cihaz=".($r['cihaz']?:'(bos)')." son_kullanim=".($r['son_kullanim_tarihi']?:'(bos)')." tok=$tok...\n";
        if (!$isWeb && $r['id']!=$keep['id']) $toDeactivate[] = $r['id'];
    }
    echo "\n";
}

echo "----------------------------------------------\n";
echo "Duplicate grup sayisi: $dupGroups\n";
echo "Pasiflestirilecek token sayisi: ".count($toDeactivate)."\n";

if (!$apply) {
    echo "\n(ANALIZ modu — hicbir sey degismedi. Uygulamak icin: ... temizle)\n";
    exit(0);
}

if ($toDeactivate) {
    $n = DB::table('bildirim_kimlikleri')->whereIn('id', $toDeactivate)->update(['aktif' => 0]);
    echo "\n✅ $n token pasiflestirildi (aktif=0).\n";
} else {
    echo "\nTemizlenecek duplicate yok.\n";
}
