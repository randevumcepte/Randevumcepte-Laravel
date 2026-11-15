<?php
/**
 * SANTRAL SESLI ASISTAN — UCTAN UCA SENARYO TESTI (canli API: /api/v1/santral/baslat + konus + bitir)
 * ------------------------------------------------------------------------------------------------
 * Koprunun (randevu-santral-ai) yaptigini taklit eder: oturum acar, musteri cumlelerini gonderir,
 * asistanin cevabini/aksiyonunu dogrular. Gercek Claude + gercek DB calisir.
 *
 *  GUVENLI mod (varsayilan): numara YOK -> kayit/randevu/mesaj YOK. Yalniz santral_oturumlari'na satir yazar.
 *  TAM mod (--tel=5xxxxxxxxx): o numara salonda KAYITLI musteri olmali. Randevu TALEBI olusturur ve
 *     ayni testte IPTAL eder (temizlik); yol tarifi o numaraya WA/SMS gider. Baskasinin numarasini KULLANMA.
 *
 * Calistir:
 *   php tests/santral_e2e.php                                  # guvenli, salon 20, canli
 *   php tests/santral_e2e.php --tel=5321112233                 # tam akis (kendi numaran)
 *   php tests/santral_e2e.php --salon=20 --url=https://app.randevumcepte.com.tr --secret=XXXX
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$opt = getopt('', ['tel::', 'salon::', 'url::', 'secret::', 'sadece::']);
$URL = rtrim($opt['url'] ?? 'https://app.randevumcepte.com.tr', '/') . '/api/v1/santral';
$SALON = (int) ($opt['salon'] ?? 20);
$TEL = isset($opt['tel']) ? preg_replace('/\D/', '', (string) $opt['tel']) : '';
$SECRET = (string) ($opt['secret'] ?? getenv('SANTRAL_SECRET') ?: '');
$SADECE = $opt['sadece'] ?? null;

function api($yol, $veri) {
    global $URL, $SECRET;
    $ch = curl_init("$URL/$yol");
    $h = ['Content-Type: application/json', 'Accept: application/json'];
    if ($SECRET !== '') $h[] = 'X-Santral-Secret: ' . $SECRET;
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $h, CURLOPT_POSTFIELDS => json_encode($veri, JSON_UNESCAPED_UNICODE)]);
    $t0 = microtime(true);
    $raw = curl_exec($ch); $kod = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $j = json_decode((string) $raw, true);
    if (!is_array($j)) $j = ['ok' => 0, 'hata' => "HTTP $kod: " . substr((string) $raw, 0, 200)];
    $j['_ms'] = (int) ((microtime(true) - $t0) * 1000);
    return $j;
}

$gecen = 0; $kalan = 0; $hatalar = [];
function kontrol($ad, $kosul, $detay = '') {
    global $gecen, $kalan, $hatalar, $SENARYO;
    if ($kosul) { $gecen++; echo "    \033[32m✓\033[0m $ad\n"; return true; }
    $kalan++; $hatalar[] = "[$SENARYO] $ad";
    echo "    \033[31m✗ $ad\033[0m" . ($detay !== '' ? "  ($detay)" : '') . "\n";
    return false;
}

/** Bir oturum: karsilama + sira ile cumleler. Her adim: [metin, function($cevap,$aksiyon,$tur):void] */
class Oturum {
    public $id; public $karsilama = ''; public $gecmis = [];
    function __construct($tel) {
        global $SALON;
        $d = api('baslat', ['sube_id' => $SALON, 'telefon' => $tel]);
        $this->id = $d['oturum_id'] ?? null;
        $this->karsilama = (string) ($d['karsilama'] ?? ($d['hata'] ?? ''));
        echo "    \033[2mAI: {$this->karsilama}\033[0m\n";
    }
    function soyle($metin) {
        echo "    \033[36mMüşteri:\033[0m $metin\n";
        $d = api('konus', ['oturum_id' => $this->id, 'metin' => $metin]);
        $c = (string) ($d['cevap'] ?? ($d['hata'] ?? ''));
        echo "    \033[2mAI: $c" . (!empty($d['aksiyon']) ? "  [aksiyon: {$d['aksiyon']}]" : '') . "  ({$d['_ms']} ms, teşhis: " . ($d['teshis'] ?? '-') . ")\033[0m\n";
        return ['cevap' => $c, 'aksiyon' => $d['aksiyon'] ?? null, 'teshis' => $d['teshis'] ?? null, 'ms' => $d['_ms']];
    }
    function bitir() { if ($this->id) api('bitir', ['oturum_id' => $this->id, 'ozet' => 'e2e-test']); }
}
function var_mi($c, $p) { return mb_stripos($c, $p) !== false; }
function bekletme_yok($r) { return !preg_match('/\b(bir|1)\s*(moment|saniye|dakika)|beklet|kontrol ediyorum|bakıyorum/iu', $r['cevap']); }

$SENARYO = '';
function senaryo($ad, callable $f) {
    global $SENARYO, $SADECE;
    if ($SADECE && mb_stripos($ad, $SADECE) === false) return;
    $SENARYO = $ad;
    echo "\n\033[1m▶ $ad\033[0m\n";
    try { $f(); } catch (\Throwable $e) { kontrol('istisna yok', false, $e->getMessage()); }
}

echo "Hedef: $URL  salon=$SALON  mod=" . ($TEL ? "TAM (tel $TEL)" : 'GÜVENLİ (numarasız)') . "\n";

// ======================================================================== GUVENLI (numarasiz)
senaryo('G1 Kayıtsız/gizli numara karşılaması', function () {
    $o = new Oturum('');
    kontrol('oturum açıldı', (bool) $o->id, $o->karsilama);
    kontrol('yapay zeka asistanı tanıtımı', var_mi($o->karsilama, 'yapay zeka asistanınızım'));
    kontrol('ad, soyad ve telefon ister', var_mi($o->karsilama, 'telefon numaranızı'));
    $o->bitir();
});
senaryo('G2 Fiyat sorusu → işletmeye aktarma', function () {
    $o = new Oturum('');
    $r = $o->soyle('Saç kesimi ne kadar?');
    kontrol('aksiyon = aktar', $r['aksiyon'] === 'aktar', (string) $r['aksiyon']);
    kontrol('"Fiyat bilgisi için" der', var_mi($r['cevap'], 'Fiyat bilgisi için'));
    kontrol('fiyat söylemez (TL/lira yok)', !preg_match('/\d+\s*(tl|lira)/iu', $r['cevap']));
    $o->bitir();
});
senaryo('G3 "Beni işletmeye bağlar mısın" → aktarma', function () {
    $o = new Oturum('');
    $r = $o->soyle('Beni işletmeye bağlar mısın');
    kontrol('aksiyon = aktar', $r['aksiyon'] === 'aktar', (string) $r['aksiyon']);
    $r = (new Oturum(''))->soyle('Beni genişletmeye bağlar mısın'); // STT hatasi varyanti
    kontrol('STT varyantı ("genişletmeye") da aktarılır', $r['aksiyon'] === 'aktar', (string) $r['aksiyon']);
    $o->bitir();
});
senaryo('G4 Kesin aktarma kod tarafından, hızlı (LLM\'siz)', function () {
    $o = new Oturum('');
    $r = $o->soyle('Operatörle görüşmek istiyorum');
    kontrol('aksiyon = aktar', $r['aksiyon'] === 'aktar');
    kontrol('2 sn altında cevap', $r['ms'] < 2000, $r['ms'] . ' ms');
    $o->bitir();
});

// ======================================================================== TAM (kayitli musteri numarasi)
if ($TEL) {
    senaryo('T1 Kayıtlı müşteri karşılaması', function () use ($TEL) {
        $o = new Oturum($TEL);
        kontrol('"Sayın" ile ismen karşılar', var_mi($o->karsilama, 'Sayın'), $o->karsilama);
        kontrol('ad soyad SORMAZ', !var_mi($o->karsilama, 'adınızı'));
        kontrol('yardım sorusu var', var_mi($o->karsilama, 'nasıl yardımcı olabilirim'));
        $o->bitir();
    });
    senaryo('T2 İşletme bilgisi: çalışma saatleri', function () use ($TEL) {
        $o = new Oturum($TEL);
        $r = $o->soyle('Kaçta kapanıyorsunuz?');
        kontrol('aktarmaz', $r['aksiyon'] !== 'aktar', (string) $r['aksiyon']);
        kontrol('saat söyler (rakam var)', (bool) preg_match('/\d{1,2}[:.]\d{2}|\b\d{1,2}\b/u', $r['cevap']));
        kontrol('bekletme cümlesi yok', bekletme_yok($r));
        $o->bitir();
    });
    senaryo('T3 Yol tarifi', function () use ($TEL) {
        $o = new Oturum($TEL);
        $r = $o->soyle('Yol tarifi almak istiyorum, konum gönderebilir misiniz');
        kontrol('ilk istekte gönderir', var_mi($r['cevap'], 'mesaj olarak ilettim'), $r['cevap']);
        kontrol('kapanış sorusu', var_mi($r['cevap'], 'Başka yardımcı olabileceğim'));
        kontrol('"Size nasıl yardımcı olabilirim" tekrar etmez', !var_mi($r['cevap'], 'Size nasıl yardımcı'));
        $o->bitir();
    });
    senaryo('T4 Gün+saat söylenince saat tekrar sorulmaz', function () use ($TEL) {
        $o = new Oturum($TEL);
        $r = $o->soyle('Çarşamba 14 için saç kesimi randevusu almak istiyorum');
        kontrol('"sabah mı öğleden sonra mı" sormaz', !var_mi($r['cevap'], 'sabah mı'));
        kontrol('saati tekrar sormaz', !preg_match('/saat(i)? kaçta|hangi saat/iu', $r['cevap']), $r['cevap']);
        kontrol('bekletme cümlesi yok', bekletme_yok($r));
        $o->bitir(); // onaylamadan cik: kayit OLUSMAZ
    });
    senaryo('T5 Randevu talebi oluştur → sonra iptal et (temizlik)', function () use ($TEL) {
        $o = new Oturum($TEL);
        $r = $o->soyle('Önümüzdeki hafta perşembe 11 için saç kesimi randevusu almak istiyorum');
        for ($i = 0; $i < 4 && $r['aksiyon'] !== 'randevu'; $i++) {
            if (preg_match('/oluşturayım mı|onaylıyor musunuz/iu', $r['cevap'])) $r = $o->soyle('Evet');
            elseif (preg_match('/hangisini|ya da/iu', $r['cevap'])) $r = $o->soyle('İlkini');
            elseif (preg_match('/hangi hizmet|saç kesimi/iu', $r['cevap']) && $i === 0) $r = $o->soyle('Saç kesimi');
            else break;
        }
        kontrol('randevu aksiyonu', $r['aksiyon'] === 'randevu', (string) $r['aksiyon']);
        kontrol('TALEP olarak bildirir', var_mi($r['cevap'], 'talebiniz oluşturuldu'), $r['cevap']);
        kontrol('kapanış sorusu', var_mi($r['cevap'], 'Başka yardımcı olabileceğim'));
        $o->bitir();
        // ---- temizlik: ayni test iptal akisini da sinar
        $o2 = new Oturum($TEL);
        $r = $o2->soyle('Randevumu iptal etmek istiyorum');
        kontrol('iptal: randevuyu hemen okur (bekletmeden)', bekletme_yok($r) && preg_match('/randevunuz var/iu', $r['cevap']), $r['cevap']);
        if (preg_match('/hangisini/iu', $r['cevap'])) $r = $o2->soyle('Perşembe 11 olanı');
        if ($r['aksiyon'] !== 'iptal') $r = $o2->soyle('Evet, iptal edin');
        kontrol('iptal aksiyonu', $r['aksiyon'] === 'iptal', (string) $r['aksiyon']);
        kontrol('"iptal edildi" der', var_mi($r['cevap'], 'iptal edildi'), $r['cevap']);
        kontrol('"Orada mısınız"a düşmedi / kapanış sorusu', var_mi($r['cevap'], 'Başka yardımcı olabileceğim'));
        $r = $o2->soyle('Hayır teşekkürler, iyi günler');
        kontrol('veda ile kapanır', $r['aksiyon'] === 'veda', (string) $r['aksiyon']);
        $o2->bitir();
    });
}

echo "\n\033[1mSONUÇ: $gecen geçti, $kalan kaldı\033[0m\n";
if ($kalan) { echo "Kalanlar:\n - " . implode("\n - ", $hatalar) . "\n"; exit(1); }
