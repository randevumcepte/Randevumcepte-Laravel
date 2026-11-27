<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistani BEDAVA cevap katmanlari (Claude'a gitmeden cevap). Patron asistanindaki
 * "kural -> kalip kutuphanesi -> ogrenen onbellek -> Haiku" zincirinin musteri tarafi.
 *
 * Sira (onCevap):
 *   1. memnuniyet cevabi (👍/👎)            -> kaydet + tesekkur
 *   2. sohbet kapanisi (tesekkur/tamam)      -> kisa cevap ya da sessiz (bekleyen soru yoksa)
 *   -- randevu niyeti varsa burada durur: randevu islemleri AI'da (canli musaitlik) --
 *   3. fiyat sorusu                          -> isletmenin kayitli hizmet fiyati (ayar: fiyat_soyle)
 *   4. isletmenin kendi SSS'i                -> aynen
 *   5. hizmet bilgi kutuphanesi              -> asistan_kalip (yalniz hizmet bilgi kategorileri; patron sohbeti degil)
 *   6. ogrenilen cevap                       -> bu isletmede AI'nin daha once verdigi baglamsiz bilgi cevabi
 * Hicbiri tutmazsa null -> RandevuAsistan (AI).
 *
 * Karar mantigi SAF fonksiyonlarda (tests/wa_asistan_standalone.php); DB yalniz ince sarmalayicilarda.
 */
class WaBedavaKatman
{
    // ================================================================== giris
    /**
     * @param object|null $oturum wa_asistan_oturumlari satiri (son_hizmet_id, memnuniyet_soruldu, memnuniyet)
     * @return array|null ['cevap','kaynak','hizmet'=>?['id','ad'],'isaret'=>[...], 'memnuniyet'=>?int]
     */
    public static function onCevap(int $salonId, RandevuAsistan $as, string $metin, array $gecmis, $oturum, array $ayar): ?array
    {
        // 1) memnuniyet
        if ($oturum && !empty($oturum->memnuniyet_soruldu) && $oturum->memnuniyet === null) {
            $m = self::memnuniyetCevabi($metin);
            if ($m !== null) {
                return ['cevap' => $m === 1 ? 'Teşekkür ederiz, sizi ağırlamayı dört gözle bekliyoruz 😊'
                    : 'Geri bildiriminiz için teşekkür ederiz; işletmemize ileteceğiz. Bir sorununuz varsa buradan yazabilirsiniz.',
                    'kaynak' => 'memnuniyet', 'memnuniyet' => $m];
            }
        }
        // 2) sohbet kapanisi
        if (($s = self::sohbetCevabi($metin, $gecmis)) !== null) return $s;

        if (self::randevuNiyetiMi($metin)) return null;
        $hizmetler = $as->hizmetler();

        // 3) fiyat
        if (!empty($ayar['fiyat_soyle']) && self::fiyatSorusuMu($metin)) {
            // Fotograftan fiyat verildiyse "toplam ne kadar?" O MODELI soruyor (canli 2026-11-27 Ola Nail: 1.640 TL'lik
            // fotograf teklifinden sonra "Hangi hizmetimizin fiyatını..." dendi, musteri koptu)
            if (($gt = self::gorselTeklif($gecmis)) !== null) {
                $gc = self::gorselFiyatCevabi($metin, $gt, $hizmetler);
                if ($gc === null) return null; // ek islem (cikarma vb.) / teklifte olmayan hizmet: AI teklifle birlikte hesaplar
                return $gc;
            }
            $sonHizmet = ($oturum && !empty($oturum->son_hizmet_id)) ? (int) $oturum->son_hizmet_id : null;
            $f = self::fiyatCevabi($metin, $hizmetler, $sonHizmet);
            if ($f !== null) return $f;
        }
        // 4) isletmenin SSS'i (ortak kutuphaneden ONCE: isletmenin kendi bilgisi kazanir)
        if (($sss = $as->sssCevabi($metin)) !== null) {
            return ['cevap' => trim($sss), 'kaynak' => 'sss', 'isaret' => ['bilgi' => 1]];
        }
        // 5) hizmet bilgi kutuphanesi
        if (self::bilgiSorusuMu($metin)) {
            $k = self::kalipEsle($metin, self::kaliplar());
            if ($k) {
                try { DB::table('asistan_kalip')->where('id', $k['id'])->increment('kullanim_sayisi'); } catch (\Throwable $e) {}
                $h = self::hizmetBul($metin, $hizmetler);
                return ['cevap' => self::cevapSec($k['cevap']) . "\n\nDilerseniz size uygun bir gün için randevu da oluşturabilirim 😊",
                    'kaynak' => 'kalip', 'hizmet' => $h ? ['id' => (int) $h[0]['id'], 'ad' => $h[0]['ad']] : null, 'isaret' => ['bilgi' => 1]];
            }
        }
        // 6) ogrenilen
        if (self::ogrenilebilirSoru($metin)) {
            $o = self::ogrenilenGetir($salonId, $metin);
            // Eskiden ogrenilmis fiyat/sure cevaplari kullanilmaz (hizmet/fiyat panelde degismis olabilir; Ola Nail 236)
            if ($o !== null && !self::fiyatSureIcerir($o)) return ['cevap' => $o, 'kaynak' => 'ogrenilen', 'isaret' => ['bilgi' => 1]];
        }
        return null;
    }

    // ================================================================== saf: metin
    public static function sade(string $s): string
    {
        $s = strtr($s, ['İ' => 'i', 'I' => 'i', 'ı' => 'i', 'Ş' => 's', 'ş' => 's', 'Ğ' => 'g', 'ğ' => 'g', 'Ü' => 'u', 'ü' => 'u', 'Ö' => 'o', 'ö' => 'o', 'Ç' => 'c', 'ç' => 'c', 'Â' => 'a', 'â' => 'a']);
        $s = mb_strtolower($s, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^a-z0-9\s]/u', ' ', $s)));
    }

    /** RandevuAsistan::fiyatSorusuMu ile ayni kural (ikisi ayni mesaji ayni sekilde siniflandirsin). */
    public static function fiyatSorusuMu(string $m): bool
    {
        $f = mb_strtolower(strtr($m, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        if (preg_match('/ne kadar (sür|zaman|vakit|bekle|erken|önce|dayan|kalıcı|gider)/u', $f)) return false;
        if (preg_match('/seans|ne kadar (hak|kald)/u', $f) && !preg_match('/fiyat|ücret|lira|tl\b|para/u', $f)) return false;
        return (bool) preg_match('/fiyat|ücret|kaç (para|lira|tl|tele)|ne kadar(\s|$|\?)|kaça (yapıyorsunuz|oluyor|olur|yapılıyor)|taksit|kaç tl/u', $f);
    }

    /** Randevu almak/degistirmek/iptal ya da tarih-saat iceren mesaj -> AI (canli musaitlik gerekir). */
    public static function randevuNiyetiMi(string $m): bool
    {
        $s = ' ' . self::sade($m) . ' ';
        if (preg_match('/ (randevu\w*|rezervasyon\w*|musait\w*|bos (yer|saat|gun)\w*|yer var|yeriniz|ayir\w*|iptal\w*|ertele\w*|degistir\w*|gelebilir miyim|gelecegim|gelmek ist\w*|yazdir\w*|olustur\w*) /u', $s)) return true;
        if (preg_match('/ (bugun|yarin|obur gun|haftaya|hafta sonu|pazartesi|sali|carsamba|persembe|cuma|cumartesi|pazar gunu|aksam|sabah|ogleden sonra|ogle) /u', $s)) return true;
        if (preg_match('/\b\d{1,2}[:.]\d{2}\b|\bsaat \d|\b\d{1,2} ?(te|ta|de|da|gibi|civari)\b/u', $s)) return true;
        return false;
    }

    /** Bilgi sorusu mu ("nedir", "nasil yapilir", "kac gun dayanir", "zarar verir mi", "?") */
    public static function bilgiSorusuMu(string $m): bool
    {
        $s = ' ' . self::sade($m) . ' ';
        if (preg_match('/ (nedir|ne demek|nasil|neden|nicin|ne kadar (surer|dayanir|kalici|gider|kalir)|kac (gun|hafta|ay|seans|saat)|zarar|acit\w*|aci\w* (var|mi|verir)|agri\w*|fark\w*|kimler\w*|uygun mu|faydas\w*|sonras\w*|oncesi\w*|dayan\w*|kalicilik|dikkat|hamile|emzir|alerji\w*|tehlike\w*|guvenli|sakinca\w*|ne ise yarar|yapilir mi|olur mu|var mi) /u', $s)) return true;
        return mb_substr(trim($m), -1) === '?';
    }

    /** Memnuniyet cevabi: 1 memnun, 0 degil, null baska bir sey. */
    public static function memnuniyetCevabi(string $m): ?int
    {
        $t = trim($m);
        if ($t === '') return null;
        if (preg_match('/👎|😡|😠|😞|☹/u', $t)) return 0;
        if (preg_match('/👍|😊|🙂|😍|🥰|❤|♥|👏|🙏/u', $t) && mb_strlen($t) <= 12) return 1;
        $s = self::sade($t);
        if (count(explode(' ', $s)) > 6) return null;
        if (preg_match('/^(memnun (kalmadim|degilim)|begenmedim|kotu|berbat|hic iyi degil)/u', $s)) return 0;
        if (preg_match('/^(memnun kaldim|memnunum|cok memnun\w*|cok iyi|harika|super|mukemmel|tesekkurler cok iyi|evet memnun\w*|guzel)/u', $s)) return 1;
        return null;
    }

    /**
     * Kapanis/tesekkur: bekleyen soru (son asistan mesaji "?" ile bitiyor) yoksa kisa cevap; salt "tamam/ok" ise
     * SESSIZ (cevap gerekmez, bot-bot dongusu de olmaz). Gecmis bossa (ilk mesaj) null: karsilama akisi isler.
     */
    public static function sohbetCevabi(string $m, array $gecmis): ?array
    {
        if (!$gecmis) return null;
        $s = self::sade($m);
        if ($s === '' && !preg_match('/👍|🙏|😊|🙂/u', $m)) return null;
        if (count(explode(' ', $s)) > 6) return null;
        $son = '';
        for ($i = count($gecmis) - 1; $i >= 0; $i--) {
            if (($gecmis[$i]['role'] ?? '') === 'assistant') { $son = trim((string) ($gecmis[$i]['content'] ?? '')); break; }
        }
        if ($son !== '' && (mb_substr($son, -1) === '?' || preg_match('/\?\s*(😊|🙂|🙏)?$/u', $son))) return null; // soru bekliyor: "tamam" = onay -> AI
        $tesekkur = '(tesekkur\w*( ederim| ederiz)?|tsk\w*|sagol\w*|sag ol\w*|eyvallah|cok sag ol\w*|cok tesekkur\w*|tesekkurler)';
        $veda = '(iyi gunler|iyi aksamlar|iyi geceler|gorusuruz|hoscakal\w*|kolay gelsin|bye)';
        if (preg_match('/^((tamam|tamamdir|ok|okey|peki|anladim|anlasildi|super|harika|tmm|olur) ?)*(' . $tesekkur . '|' . $veda . ')( ' . $tesekkur . '| ' . $veda . '| size de| sizlere de)*$/u', $s)) {
            return ['cevap' => 'Rica ederiz 😊 Başka bir konuda yardımcı olabilirsem buradayım. İyi günler dileriz!', 'kaynak' => 'sohbet'];
        }
        if (preg_match('/^(tamam|tamamdir|ok|okey|peki|anladim|anlasildi|tmm|olur|super|harika|👍)( (tamam|tesekkur\w*|anladim))?$/u', $s)
            || ($s === '' && preg_match('/^(👍|🙏|😊|🙂)+$/u', trim($m)))) {
            return ['cevap' => '', 'kaynak' => 'sessiz'];
        }
        return null;
    }

    // ================================================================== saf: hizmet / fiyat
    /** Hizmet adinda eslesmeye katilmayan genel kelimeler. */
    const GENEL_KELIME = ['ve', 'ile', 'tum', 'tek', 'bolge', 'bolgesi', 'seans', 'seansi', 'paket', 'paketi', 'islem', 'islemi', 'uygulama', 'uygulamasi', 'hizmet', 'hizmeti', 'bayan', 'erkek', 'kadin', 'bay', 'ozel', 'standart', 'adet'];

    /**
     * Mesajda gecen hizmet(ler): adinin anlamli kelimelerinin TAMAMI mesajda (ek toleransli) gecenler, en ozel (cok kelimeli)
     * once. Tam eslesme yoksa en az bir uzun (>=5 harf) kelimesi gecenler. [['id','ad','fiyat',...], ...] en fazla 6.
     */
    public static function hizmetBul(string $metin, array $hizmetler): array
    {
        $kel = array_values(array_filter(explode(' ', self::sade($metin)), 'strlen'));
        if (!$kel || !$hizmetler) return [];
        $var = function (string $w) use ($kel) {
            foreach ($kel as $k) {
                if ($k === $w) return true;
                if (strlen($w) >= 3 && strpos($k, $w) === 0) return true;                       // ek: tirnak -> tirnagi(x) tirnaklar(ok)
                if (strlen($k) >= 4 && strlen($w) >= 5 && strpos($w, $k) === 0 && strlen($w) - strlen($k) <= 2) return true; // kisaltma: "protez" ~ "protezi"
                if (strlen($w) >= 6 && strlen($k) >= 6 && substr($w, 0, -1) === substr($k, 0, strlen($w) - 1)) return true; // tirnak/tirnag
            }
            return false;
        };
        $tam = []; $kismi = [];
        foreach ($hizmetler as $h) {
            $ad = trim((string) ($h['ad'] ?? ''));
            if ($ad === '') continue;
            $hk = array_values(array_filter(explode(' ', self::sade($ad)), function ($w) {
                return strlen($w) >= 2 && !in_array($w, self::GENEL_KELIME, true);
            }));
            if (!$hk) continue;
            // Kismi eslesme icin ayirt edici kelime: uzun (>=5 harf) ya da hizmet adinin ILK kelimesi ("kaş", "saç")
            $n = 0; $uzun = false;
            foreach ($hk as $i => $w) if ($var($w)) { $n++; if (strlen($w) >= 5 || $i === 0) $uzun = true; }
            if ($n === count($hk)) $tam[] = $h + ['_n' => count($hk)];
            elseif ($n > 0 && $uzun) $kismi[] = $h + ['_n' => $n, '_o' => $n / count($hk)];
        }
        if ($tam) {
            usort($tam, function ($a, $b) { return [$b['_n'], mb_strlen($a['ad'])] <=> [$a['_n'], mb_strlen($b['ad'])]; });
            // En ozel eslesme (en cok kelime) ile ayni kelime sayisindakiler; daha genel olanlar elenir
            $enCok = $tam[0]['_n'];
            $tam = array_values(array_filter($tam, function ($h) use ($enCok) { return $h['_n'] === $enCok; }));
            return array_map(function ($h) { unset($h['_n']); return $h; }, array_slice($tam, 0, 6));
        }
        usort($kismi, function ($a, $b) { return [$b['_o'], $b['_n']] <=> [$a['_o'], $a['_n']]; });
        return array_map(function ($h) { unset($h['_n'], $h['_o']); return $h; }, array_slice($kismi, 0, 6));
    }

    public static function tl($f): string
    {
        $f = (float) $f;
        return number_format($f, abs($f - round($f)) < 0.005 ? 0 : 2, ',', '.') . ' TL';
    }

    /**
     * Fiyat cevabi (saf). Eslesen hizmet(ler)in kayitli fiyati; eslesme yoksa onceki konusmadaki hizmet; o da yoksa
     * "hangi hizmet?" sorusu (ornek 3 hizmet). Eslesen hizmetin fiyati tanimsizsa (0) null -> AI/aktarma.
     */
    public static function fiyatCevabi(string $metin, array $hizmetler, ?int $sonHizmetId = null): ?array
    {
        $bulunan = self::hizmetBul($metin, $hizmetler);
        if (!$bulunan && $sonHizmetId) {
            foreach ($hizmetler as $h) if ((int) ($h['id'] ?? 0) === $sonHizmetId) { $bulunan = [$h]; break; }
        }
        if (!$bulunan) {
            $ornek = array_slice(array_values(array_filter($hizmetler, function ($h) { return (float) ($h['fiyat'] ?? 0) > 0; })), 0, 3);
            if (!$ornek) return null;
            return ['cevap' => 'Hangi hizmetimizin fiyatını öğrenmek istersiniz? Örneğin: ' . implode(', ', array_column($ornek, 'ad')) . '.',
                'kaynak' => 'fiyat', 'isaret' => []];
        }
        $fiyatli = array_values(array_filter($bulunan, function ($h) { return (float) ($h['fiyat'] ?? 0) > 0; }));
        if (!$fiyatli) return null;
        $ilk = $fiyatli[0];
        if (count($fiyatli) === 1) {
            $cevap = "{$ilk['ad']} fiyatımız " . self::tl($ilk['fiyat']) . '.';
            // Soruya gore: sure yalniz sorulduysa eklenir ("fiyatı ve süresi ne?"); yalniz fiyat sorusunda eklenmez
            $sure = (int) ($ilk['sure_dk'] ?? 0);
            if ($sure > 0 && preg_match('/sür|dakika|saat|zaman|vakit/iu', $metin)) $cevap .= " İşlem yaklaşık {$sure} dakika sürüyor.";
            $cevap .= "\n\nSize uygun bir gün için randevu oluşturmamı ister misiniz? 😊";
        } else {
            $satir = array_map(function ($h) { return '• ' . $h['ad'] . ' – ' . self::tl($h['fiyat']); }, array_slice($fiyatli, 0, 5));
            $cevap = "Fiyatlarımız:\n" . implode("\n", $satir) . "\n\nHangisiyle ilgileniyorsunuz? Dilerseniz hemen randevu da oluşturabilirim 😊";
        }
        return ['cevap' => $cevap, 'kaynak' => 'fiyat', 'hizmet' => ['id' => (int) ($ilk['id'] ?? 0), 'ad' => (string) $ilk['ad']], 'isaret' => ['fiyat' => 1]];
    }

    /**
     * Yakin gecmiste fotograftan verilen fiyat teklifi (WaGorselDanisman): ['fiyat' => "1.640 TL", 'kalemler' => "(...)" | '',
     * 'metin' => tum teklif mesaji]. Teklif son 12 mesajda yoksa null.
     */
    public static function gorselTeklif(array $gecmis): ?array
    {
        for ($i = count($gecmis) - 1, $n = 0; $i >= 0 && $n < 12; $i--, $n++) {
            $m = $gecmis[$i];
            if (($m['role'] ?? '') !== 'assistant' || mb_strpos((string) ($m['baglam'] ?? ''), 'görsel analiz:') === false) continue;
            $c = (string) ($m['content'] ?? '');
            if (!preg_match('/Bu model için fiyatımız: ([^\n]+)(?:\n(\([^\n]+\)))?/u', $c, $x)) return null; // son fotografin fiyati yok
            return ['fiyat' => trim($x[1]), 'kalemler' => trim($x[2] ?? ''), 'metin' => $c];
        }
        return null;
    }

    /**
     * Fotograf teklifinden sonra fiyat sorusu (saf): teklifte olmayan hizmet / ek islem (cikarma, sokum) sorulduysa null (AI
     * teklifi de gorerek hesaplar); yoksa teklifin toplami tekrar soylenir.
     */
    public static function gorselFiyatCevabi(string $metin, array $teklif, array $hizmetler): ?array
    {
        if (preg_match('/\b(cikar\w*|sok\w*|temizle\w*)\b/u', self::sade($metin))) return null;
        $teklifS = self::sade($teklif['fiyat'] . ' ' . $teklif['kalemler']); // isletme notu ("Protez çıkarma 300 tl") teklif sayilmaz
        foreach (self::hizmetBul($metin, $hizmetler) as $h) {
            if (strpos($teklifS, self::sade((string) $h['ad'])) === false) return null;
        }
        $cevap = 'Fotoğrafını gönderdiğiniz model için toplam fiyatımız ' . $teklif['fiyat'] . ' 😊'
            . ($teklif['kalemler'] !== '' ? "\n" . $teklif['kalemler'] : '')
            . "\n\nSize uygun bir gün için randevu oluşturalım mı?";
        return ['cevap' => $cevap, 'kaynak' => 'fiyat', 'isaret' => ['fiyat' => 1]];
    }

    // ================================================================== saf: kalip kutuphanesi
    /** Patron asistanindaki kalipCevabi ile ayni eslesme: kelime basindan tetik + Turkce ek; en uzun tetik kazanir. */
    public static function kalipEsle(string $metin, array $kaliplar): ?array
    {
        $n = ' ' . self::sade($metin) . ' ';
        $enIyi = null; $enSkor = 0;
        foreach ($kaliplar as $k) {
            foreach (preg_split('/[\r\n,;]+/', (string) ($k['tetikleyiciler'] ?? '')) as $t) {
                $t = self::sade($t);
                if (mb_strlen($t) < 3) continue;
                if (preg_match('/(?:^| )' . preg_quote($t, '/') . '[a-z]*(?= |$)/u', $n)) {
                    $skor = mb_strlen($t);
                    if ($skor > $enSkor) { $enSkor = $skor; $enIyi = $k; }
                }
            }
        }
        return $enIyi;
    }

    /** "---" ile ayrilmis cevap havuzundan rastgele biri. */
    public static function cevapSec(string $cevap, ?int $tohum = null): string
    {
        $parca = array_values(array_filter(array_map('trim', preg_split('/^\s*-{3,}\s*$/m', $cevap)), 'strlen'));
        if (!$parca) return trim($cevap);
        return $parca[$tohum !== null ? $tohum % count($parca) : mt_rand(0, count($parca) - 1)];
    }

    // ================================================================== saf: ogrenme
    /** Ogrenilebilir (ve ogrenilmisten getirilebilir) soru: baglamsiz, kisiye/zamana bagli olmayan bilgi sorusu. */
    public static function ogrenilebilirSoru(string $m): bool
    {
        $s = ' ' . self::sade($m) . ' ';
        if (mb_strlen($s) < 8 || mb_strlen($s) > 200) return false;
        if (self::randevuNiyetiMi($m)) return false;
        if (preg_match('/\d/', $s)) return false;
        if (preg_match('/ (ben|benim|bana|beni|randevum\w*|paketim\w*|seansim\w*|borcum\w*|odeme\w*|hesabim\w*|bu|su|o|bunu|bunun|sunu|onu|onun|bunlar\w*|demin|az once|dediginiz|dediniz|soyledigin\w*|yukaridaki|fotograf\w*|resim\w*|model\w*) /u', $s)) return false;
        if (self::fiyatSorusuMu($m)) return false; // fiyat ogrenilmez: fiyat katmani her seferinde guncel listeden verir
        return self::bilgiSorusuMu($m) || (bool) preg_match('/ (var mi|yapiyor musunuz|yapilir mi|kabul ed\w*|nerede\w*|adres\w*|otopark\w*|kredi kart\w*) /u', $s);
    }

    /**
     * AI cevabi ogrenilsin mi? Tek Claude cagrisi + arac yok + aksiyon yok + cevapta musteri adi yok + makul uzunluk.
     * @param array $d RandevuAsistan::konus sonucu; $kullanim ->kullanim
     */
    public static function cevapOgrenilebilir(string $soru, array $d, array $kullanim, ?string $musteriAd): bool
    {
        if (!self::ogrenilebilirSoru($soru)) return false;
        if (!empty($d['aksiyon']) || !empty($d['baglam']) || !empty($d['bitir'])) return false;
        if ((int) ($kullanim['cagri'] ?? 0) !== 1) return false;
        $c = trim((string) ($d['cevap'] ?? ''));
        if (mb_strlen($c) < 15 || mb_strlen($c) > 700) return false;
        if (preg_match('/\b\d{1,2}[:.]\d{2}\b|bugün|yarın|randevunuz/u', $c)) return false; // zamana/kisiye bagli
        if ($musteriAd) {
            foreach (explode(' ', $musteriAd) as $p) if (mb_strlen($p) >= 3 && mb_stripos($c, $p) !== false) return false;
        }
        if (preg_match('/aktar|ilettim|dönüş yapacak/u', $c)) return false;
        if (self::fiyatSureIcerir($c)) return false; // fiyat/sure degisir: kalici ogrenilirse eski bilgi okunur
        return true;
    }

    /** Cevapta fiyat ya da sure var mi ("400 TL", "₺350", "150 dakika")? Bunlar ogrenilmez / ogrenilmisse kullanilmaz. */
    public static function fiyatSureIcerir(string $c): bool
    {
        return (bool) preg_match('/\d[\d.,]*\s*(TL|₺|lira|dakika|dk\b|saat sürer)|₺\s*\d|fiyat/iu', $c);
    }

    public static function ogrenilenAnahtar(string $metin): string
    {
        $s = self::sade($metin);
        $s = preg_replace('/ (mi|mu|mı|mü|acaba|lutfen|rica etsem|merhaba|selam|iyi gunler) /u', ' ', ' ' . $s . ' ');
        return md5(trim(preg_replace('/\s+/', ' ', $s)));
    }

    // ================================================================== DB
    /** Hizmet bilgi kaliplari (asistan_kalip; yalniz hizmet bilgi kategorileri), 10 dk onbellek. */
    public static function kaliplar(): array
    {
        try {
            return Cache::remember('wa_asistan_kaliplar_v1', 10, function () {
                $kat = self::hizmetBilgiKategorileri();
                if (!$kat || !Schema::hasTable('asistan_kalip')) return [];
                return DB::table('asistan_kalip')->where('aktif', 1)->whereIn('kategori', $kat)
                    ->get(['id', 'tetikleyiciler', 'cevap', 'kategori'])->map(function ($r) { return (array) $r; })->all();
            });
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] kaliplar okunamadi: ' . $e->getMessage());
            return [];
        }
    }

    /** Hizmet BILGI kalibi kategorileri (patron sohbet/egitim kaliplari musteriye gitmesin). */
    public static function hizmetBilgiKategorileri(): array
    {
        $kat = [];
        try { $kat = array_keys(\App\Support\HizmetBilgiIcerik::veri()); } catch (\Throwable $e) {}
        try {
            $r = new \ReflectionMethod(\App\Console\Commands\HizmetBilgiKaliplari::class, 'kutuphane');
            $r->setAccessible(true);
            $kat = array_merge($kat, array_keys((array) $r->invoke(new \App\Console\Commands\HizmetBilgiKaliplari())));
        } catch (\Throwable $e) {}
        return array_values(array_unique(array_filter($kat, 'is_string')));
    }

    public static function ogrenilenGetir(int $salonId, string $metin): ?string
    {
        try {
            if (!WaAsistanVeri::semaGaranti()) return null;
            $r = DB::table('wa_asistan_ogrenilen')->where('salon_id', $salonId)->where('anahtar', self::ogrenilenAnahtar($metin))
                ->where('aktif', 1)->where('updated_at', '>=', now()->subDays(30))->first();
            if (!$r) return null;
            DB::table('wa_asistan_ogrenilen')->where('id', $r->id)->increment('adet');
            return (string) $r->cevap;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function ogren(int $salonId, string $soru, string $cevap): void
    {
        try {
            if (!WaAsistanVeri::semaGaranti()) return;
            $a = self::ogrenilenAnahtar($soru);
            $var = DB::table('wa_asistan_ogrenilen')->where('salon_id', $salonId)->where('anahtar', $a)->first();
            if ($var) {
                if ((int) $var->aktif === 1) {
                    DB::table('wa_asistan_ogrenilen')->where('id', $var->id)->update(['cevap' => $cevap, 'soru' => mb_substr($soru, 0, 500), 'updated_at' => now()]);
                }
                return; // isletme sildiyse (aktif=0) yeniden ogrenme
            }
            DB::table('wa_asistan_ogrenilen')->insert(['salon_id' => $salonId, 'anahtar' => $a, 'soru' => mb_substr($soru, 0, 500),
                'cevap' => $cevap, 'adet' => 0, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] ogrenme yazilamadi: ' . $e->getMessage());
        }
    }
}
