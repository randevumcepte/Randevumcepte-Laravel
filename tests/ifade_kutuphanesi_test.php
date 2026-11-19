<?php
/**
 * Ifade kutuphanesi kontrolu + motor testleri (Laravel'siz):  php tests/ifade_kutuphanesi_test.php
 * 1) Veri kontrolu: tekil id, ust/aday kodlari var mi, iki kavramda ayni ifade (belirsiz listesinde degilse hata),
 *    atilacak kelime hizmet ifadesinde mi (motor korur; bilgi), kendi kendini eleyen ifade (haric ile cakisan).
 * 2) Gercek musteri cumleleri: kavram + salon katalogu karari.
 */
require_once __DIR__ . '/../app/Services/IfadeKutuphanesi.php';
use App\Services\IfadeKutuphanesi as K;

$gec = 0; $kal = 0;
function esit($ad, $beklenen, $gercek) {
    global $gec, $kal;
    if ($beklenen === $gercek) { $gec++; echo "  \033[32m✓\033[0m $ad\n"; return; }
    $kal++; echo "  \033[31m✗ $ad\033[0m\n      beklenen: " . json_encode($beklenen, JSON_UNESCAPED_UNICODE) . "\n      gelen:    " . json_encode($gercek, JSON_UNESCAPED_UNICODE) . "\n";
}

$v = K::yukle();
$kv = $v['kavram']; $g = $v['genel'];

echo "\n\033[1mVERİ KONTROLÜ\033[0m\n";
$sektor = []; $ifadeSay = 0;
foreach ($kv as $k) { $sektor[$k['sektor']] = ($sektor[$k['sektor']] ?? 0) + 1; $ifadeSay += count($k['_ifade']); }
foreach ($sektor as $s => $n) echo "  $s: $n kavram\n";
echo '  TOPLAM: ' . count($kv) . " kavram, $ifadeSay ifade, " . count($g['duzeltmeler']) . ' + ' . count($g['birlesik_duzeltmeler']) . ' düzeltme, '
    . count($g['esler']) . ' eş kuralı, ' . count($g['belirsiz']) . " belirsiz ifade\n";

$hata = [];
foreach ($kv as $id => $k) {
    if (!empty($k['ust']) && !isset($kv[$k['ust']])) $hata[] = "$id: üst kavram yok ({$k['ust']})";
    foreach ($k['_ifade'] as $iw) foreach ($k['_haric'] as $h) if (K::bul($iw, $h)) $hata[] = "$id: '" . implode(' ', $iw) . "' kendi hariç listesine takılıyor ('" . implode(' ', $h) . "')";
}
$belirsizIfade = [];
foreach ($g['belirsiz'] as $b) {
    $belirsizIfade[implode(' ', K::kelimeler($b['ifade']))] = 1;
    foreach ($b['adaylar'] as $a) if (!isset($kv[$a])) $hata[] = "belirsiz '{$b['ifade']}': aday kodu yok ($a)";
    if (!empty($b['en_olasi']) && !in_array($b['en_olasi'], $b['adaylar'], true)) $hata[] = "belirsiz '{$b['ifade']}': en_olasi adaylarda yok";
}
$sahip = [];
foreach ($kv as $id => $k) foreach ($k['_ifade'] as $iw) $sahip[implode(' ', $iw)][$id] = 1;
foreach ($sahip as $ifade => $ids) if (count($ids) > 1 && !isset($belirsizIfade[$ifade])) $hata[] = "'$ifade' iki kavramda (" . implode(', ', array_keys($ids)) . ") ama belirsiz listesinde yok";
$korunanAtilacak = array_keys(array_intersect_key($v['atilacak'], $v['korunan']));
echo $hata ? "  \033[31m" . count($hata) . " HATA:\033[0m\n    - " . implode("\n    - ", $hata) . "\n" : "  \033[32m✓ veri hatasız\033[0m\n";
if ($korunanAtilacak) echo "  bilgi: hizmet ifadesinde geçtiği için atılmayan kelimeler: " . implode(', ', $korunanAtilacak) . "\n";
if ($hata) $kal += count($hata);

echo "\n\033[1mİŞLEME (düzeltme + eş dönüşüm)\033[0m\n";
$isl = function ($m) { return implode(' ', K::isle($m)); };
esit('ojemi kaldırtmak istiyorum', 'ojemi cikarma', $isl('Ojemi kaldırtmak istiyorum'));
esit('kirpik kaldırma = lifting', 'kirpik lifting', $isl('kirpik kaldırma'));
esit('ipek kirpiklerimi söktürmek', 'ipek kirpiklerimi cikarma', $isl('ipek kirpiklerimi söktürmek'));
esit('kaş aldırmak', 'kas alma', $isl('kaş aldırmak istiyorum'));
esit('saçımı kestirmek', 'sacimi kesim', $isl('saçımı kestirmek'));
esit('derin doku mesajı -> masajı', 'derin doku masaji', $isl('derin doku mesajı'));
esit('mesaj attım (masaja çevrilmez)', 'mesaj attim', $isl('mesaj attım'));
esit('ses: et plakson', 'lazer epilasyon', $isl('lazer et plakson'));
esit('ses: protest tırnak', 'protez tirnak', $isl('protest tırnak'));
esit('kaç alma -> kaş alma', 'kas alma', $isl('kaç alma'));
esit('kaç para (kaş yapılmaz)', '', $isl('kaç para'));
esit('bir numara korunur', 'bir numara', $isl('bir numara'));

echo "\n\033[1mKAVRAM (salon bağımsız)\033[0m\n";
$kav = function ($m) { $c = K::cozumle($m); sort($c['adaylar']); return $c['adaylar']; };
esit('protez takma', ['rc_protez_sac' ?? null] === [] ? [] : array_values(array_filter($kav('protez takma'), function ($x) { return $x === 'rc_protez_tirnak_uygulamasi'; })), ['rc_protez_tirnak_uygulamasi']);
esit('kalıcı oje çıkarma (tek)', ['rc_kalici_oje_cikarma'], $kav('kalıcı oje çıkarma istiyorum'));
esit('kalıcımı sildirmek', ['rc_kalici_oje_cikarma'], $kav('kalıcılarımı sildirmek istiyorum'));
esit('kirpik kaldırma', ['rc_kirpik_laminasyonu', 'rc_kirpik_lifting'], $kav('kirpik kaldırma'));
esit('kaş kaldırma (laminasyon)', ['rc_kas_laminasyonu'], $kav('kaş kaldırma'));
esit('kaş aldırmak', ['rc_kas_agda', 'rc_kas_alma'], $kav('kaşlarımı aldırmak istiyorum'));
esit('lazer lipoliz lazere gitmez', ['rc_lazer_lipoliz'], $kav('lazer lipoliz'));
esit('koltuk altı lazer', ['rc_koltuk_alti_lazer_epilasyon'], $kav('koltuk altı lazer yaptırmak istiyorum'));
esit('yüzümdeki tüyleri ipe aldırmak', ['rc_yuz_alimi'], $kav('yüzümü ipe aldırmak istiyorum'));
esit('saç sakal', ['rc_sac_sakal'], $kav('saç sakal'));
esit('yarın saç kesimi', ['rc_sac_kesimi'], $kav('yarın saç kesimi için randevu'));
esit('tırnaklarımı kestirmek', ['rc_tirnak_kesimi'], $kav('tırnaklarımı kestirmek istiyorum'));
esit('dişlerimi temizletmek', ['rc_dis_tasi_temizligi'], $kav('dişlerimi temizletmek istiyorum'));
esit('cildimi temizletmek', ['rc_cilt_temizligi'], $kav('cildimi temizletmek istiyorum'));
esit('göbeğimi eritmek', ['rc_karin_bolgesi_incelme'], $kav('göbeğimi eritmek istiyorum'));
esit('reformer', ['rc_reformer_pilates'], $kav('reformer dersi'));
esit('tay masajı', ['rc_thai_masaji'], $kav('tay masajı'));
esit('saç boyasını sildirmek', ['rc_renk_silme'], $kav('saç boyasını sildirmek istiyorum'));
esit('dip boya', ['rc_dip_boya'], $kav('diplerimi boyatmak istiyorum'));

echo "\n\033[1mTUZAK (hizmet çıkmamalı / yanlış hizmete gitmemeli)\033[0m\n";
foreach (['Merhaba yarın saat 3te müsait misiniz', 'mesaj attım cevap yok', 'kaslarımı geliştirmek istiyorum', 'kaslarimi gelistirmek istiyorum',
    'kasım ayında gelirim', 'bu ay kaç para', 'adresiniz nerede', 'Ayşe hanıma randevu'] as $m) esit("boş: $m", [], $kav($m));
esit('kas alma (Türkçe karaktersiz) = kaş alma', ['rc_kas_agda', 'rc_kas_alma'], $kav('kas alma'));
esit('oje sürdürmek', ['rc_normal_oje_uygulamasi'], $kav('oje sürdürmek'));
esit('kalıcı ojemi kaldırtmak', ['rc_kalici_oje_cikarma'], $kav('kalıcı ojemi kaldırtmak'));
esit('saçımın uçlarından aldırmak', ['rc_sac_uclarindan_aldirma'], $kav('saçımın uçlarından aldırmak'));
esit('ense masajı ense almaya gitmez', ['rc_boyun_omuz_masaji'], $kav('ense masajı'));

echo "\n\033[1mSALON KATALOĞU (Ola Nail benzeri)\033[0m\n";
$ola = [10 => 'Kalıcı Oje Çıkarma', 11 => 'El Kalıcı Oje', 12 => 'Ayak Kalıcı Oje', 13 => 'Jel Güçlendirme', 14 => 'El Protez Tırnak',
    15 => 'Protez Tırnak Çıkarma', 16 => 'Manikür', 17 => 'Medikal Pedikür', 18 => 'Kaş Alma', 19 => 'Protez Tırnak Dolgu', 20 => 'Nail Art'];
$kat = K::katalog($ola);
$sec = function ($m) use ($ola, $kat) { $s = K::secim($m, $ola, $kat); sort($s['hizmetler']); return [$s['durum'], $s['hizmetler']]; };
esit('oje kaldırma -> Kalıcı Oje Çıkarma (sorulmaz)', ['tek', [10]], $sec('oje kaldırma'));
esit('ojemi sildirmek', ['tek', [10]], $sec('ojemi sildirmek istiyorum'));
esit('protez takma -> El Protez Tırnak', ['tek', [14]], $sec('protez takma'));
esit('protezlerimi söktürmek', ['tek', [15]], $sec('protezlerimi söktürmek'));
esit('protez dolgu', ['tek', [19]], $sec('protez dolgu'));
esit('kalıcı oje -> el/ayak sor', ['coklu', [11, 12]], $sec('kalıcı oje'));
esit('ellerime kalıcı', ['tek', [11]], $sec('ellerime kalıcı oje'));
esit('pedikür -> Medikal Pedikür (alt hizmet)', ['tek', [17]], $sec('pedikür istiyorum'));
esit('jel güçlendirme', ['tek', [13]], $sec('jel güçlendirme'));
esit('kendi tırnağıma jel', ['tek', [13]], $sec('kendi tırnağıma jel'));
esit('kaşlarımı aldırmak', ['tek', [18]], $sec('kaşlarımı aldırmak'));
esit('medikal manikür -> Manikür (üst hizmet)', ['tek', [16]], $sec('medikal manikür'));
esit('saç kesimi -> salonda yok', ['yok', []], $sec('saç kesimi'));

echo "\n\033[1mSONUÇ: $gec geçti, $kal kaldı\033[0m\n";
exit($kal ? 1 : 0);
