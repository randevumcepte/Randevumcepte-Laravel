<?php
$base = '/var/www/www-root/data/www/randevumceptetest';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Services\DrklinikClient('ezgitakmaz', 'Coco88');
$c->login();

$pages = [
    'uruntanimlamalari.aspx',
    'kasa_islemleri.aspx',
    'satis_raporlari.aspx',
    'gunlukrandevulistesi.aspx',
    'randevu_hakedis_raporu.aspx',
];

foreach ($pages as $p) {
    $h = $c->getHtml('/' . $p, 'inspect5_' . preg_replace('/[^a-z0-9]+/i','_',$p));
    echo "\n========= $p (" . strlen($h) . " byte) =========\n";

    if (preg_match_all('~<input[^>]+name="(TB_[^"]*[Tt]arih[^"]*|[^"]*[Dd]ate[^"]*)"[^>]*?(?:value="([^"]*)")?~i', $h, $m, PREG_SET_ORDER)) {
        echo "  --- Tarih input'lari ---\n";
        foreach ($m as $row) printf("    %-30s = [%s]\n", $row[1], $row[2] ?? '');
    }

    if (preg_match_all('~__doPostBack\(&#39;([^&]+)&#39~', $h, $m)) {
        $targets = array_unique($m[1]);
        $relevant = array_filter($targets, function($t){
            return preg_match('~(Liste|Listele|Goster|Goruntule|Filtrele|Rapor|Ara)~i', $t);
        });
        if ($relevant) {
            echo "  --- __doPostBack hedefleri (Liste/Goster/Rapor) ---\n";
            foreach ($relevant as $t) echo "    " . $t . "\n";
        }
    }

    if (preg_match_all('~id="(DGRV_[A-Za-z0-9_]+|GV_[A-Za-z0-9_]+|GridView[A-Za-z0-9_]*)"~', $h, $m)) {
        $g = array_unique($m[1]);
        if ($g) echo "  --- GridView ID'leri: " . implode(', ', $g) . "\n";
    }

    if (preg_match_all('~<input[^>]+type="(?:submit|button)"[^>]+name="(LB_[A-Za-z0-9_]+|BTN_[A-Za-z0-9_]+|Btn_[A-Za-z0-9_]+|Button[0-9]+)"[^>]*?(?:value="([^"]*)")?~i', $h, $m, PREG_SET_ORDER)) {
        echo "  --- Liste/Submit butonlari ---\n";
        foreach ($m as $row) printf("    %-30s value=[%s]\n", $row[1], $row[2] ?? '');
    }
}
