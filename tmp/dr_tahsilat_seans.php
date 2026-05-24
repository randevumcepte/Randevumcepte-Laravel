<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

function inspect($c, $path, $btn, $tarihAlanlari, $extra = []) {
    echo "\n========= $path =========\n";

    // Once GET ile form yapisini gor (varsayilan dropdownlar dolu mu)
    $get = $c->getHtml('/' . $path);
    echo "GET boyut: " . strlen($get) . "\n";
    if (preg_match_all('~<select[^>]+(?:id|name)="([^"]+)"[^>]*>(.*?)</select>~is', $get, $sm, PREG_SET_ORDER)) {
        foreach ($sm as $s) {
            preg_match_all('~<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>~is', $s[2], $om, PREG_SET_ORDER);
            if (count($om) > 1) {
                $cur = '';
                if (preg_match('~<option[^>]+selected[^>]*value="([^"]*)"[^>]*>(.*?)</option>~is', $s[2], $sel)) {
                    $cur = "selected={$sel[1]}({$sel[2]})";
                }
                echo "  <select id={$s[1]} (" . count($om) . " opt) $cur\n";
            }
        }
    }

    $body = array_merge($tarihAlanlari, $extra);
    $h = $c->postBack('/' . $path, $btn, '', $body);
    if (!$h) { echo "POSTBACK NULL\n"; return; }
    echo "POSTBACK boyut: " . strlen($h) . "\n";

    // TH'ler
    if (preg_match_all('~<th[^>]*>(.*?)</th>~is', $h, $tm)) {
        $clean = array_filter(array_map(function($t){return trim(html_entity_decode(strip_tags($t), ENT_QUOTES|ENT_HTML5, 'UTF-8'));}, $tm[1]));
        if ($clean) echo "TH (" . count($clean) . "): " . implode(' | ', array_slice(array_unique($clean), 0, 20)) . "\n";
    }

    // En genis tablo
    preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tlist);
    $best = ''; $maxRows = 0;
    foreach ($tlist[1] as $t) {
        if (preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $t, $r) && count($r[1]) > $maxRows) { $maxRows = count($r[1]); $best = $t; }
    }
    echo "En genis tablo: $maxRows tr\n";

    // Toplam metni
    if (preg_match('~Toplam\s*[:=]?\s*([\d.,]+)~iu', strip_tags($h), $tm)) echo "Toplam metni: " . trim($tm[0]) . "\n";

    // Ilk 2 satir
    preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $best, $rows);
    $shown = 0;
    foreach ($rows[1] as $tr) {
        if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
        preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
        if (empty($tds[1])) continue;
        if (++$shown > 2) break;
        echo "Satir $shown (" . count($tds[1]) . " td):\n";
        foreach ($tds[1] as $i => $td) {
            $clean = trim(preg_replace('~\s+~', ' ', strip_tags($td)));
            $clean = trim(html_entity_decode($clean, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
            if (strlen($clean) > 80) $clean = substr($clean, 0, 80) . '...';
            printf("  td[%2d] %s\n", $i, $clean);
        }
    }
}

// 1) TAHSILAT - kasa_islemleri.aspx
inspect($c, 'kasa_islemleri.aspx', 'BTN_Ara', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
]);

// 2) HAKEDIS - randevu_hakedis_raporu.aspx
inspect($c, 'randevu_hakedis_raporu.aspx', 'BTN_Ara', [
    'TB_Tarih1' => '01.06.2024', 'TB_Tarih2' => '30.06.2024',
]);

// 3) SATIS - satis_raporlari.aspx
inspect($c, 'satis_raporlari.aspx', 'BTN_Ara', [
    'TB_TarihSec1' => '01.06.2024', 'TB_TarihSec2' => '30.06.2024',
]);
