<?php
$h = file_get_contents($argv[1]);
echo "--- pager linkleri (anchor href + onclick) ---\n";
preg_match_all('#<a[^>]+(?:href|onclick)="([^"]+)"[^>]*>([^<]{0,40})</a>#i', $h, $m, PREG_SET_ORDER);
foreach ($m as $row) {
    $h2 = $row[1]; $t = trim($row[2]);
    if (preg_match('/(__doPostBack|Page\$|page=|sayfa)/i', $h2)) {
        echo "  text=[{$t}] href={$h2}\n";
    }
}
echo "\n--- form bilgisi (action, viewstate boyutu) ---\n";
if (preg_match('#<form[^>]*action="([^"]+)"#i', $h, $m)) echo "form action: " . $m[1] . "\n";
if (preg_match('#name="__VIEWSTATE"\s+(?:id="[^"]+"\s+)?value="([^"]+)"#i', $h, $m)) echo "VIEWSTATE bytes: " . strlen($m[1]) . "\n";
if (preg_match('#name="__EVENTVALIDATION"\s+(?:id="[^"]+"\s+)?value="([^"]+)"#i', $h, $m)) echo "EVENTVALIDATION bytes: " . strlen($m[1]) . "\n";

echo "\n--- toplam musteri / sayfa sayisi metni varsa ---\n";
if (preg_match_all('#(toplam[^<]{0,40}|sayfa[^<]{0,40}|kayit[^<]{0,40}|page \d+[^<]{0,20})#i', strip_tags($h), $m)) {
    foreach (array_unique($m[1]) as $t) echo "  " . trim($t) . "\n";
}
