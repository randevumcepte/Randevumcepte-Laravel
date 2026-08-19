<?php

namespace App\Services;

/**
 * PATRON ASISTANI — niyet cozumleme + dogal dil cevap yazimi (KURAL MOTORU).
 *
 * Mimari (restoran copilot'undan tasindi):
 *   1) Patron sesli/yazili SERBEST konusur ("bugun kasa ne durumda", "en cok kim
 *      sattı", "ahmet ne yaptı bu ay"). Sesli ise cihazda STT ile metne cevrilir.
 *   2) Bu servis niyeti cozer  ->  { intent, donem }  (LLM YOK, sifir maliyet).
 *   3) StoreAdminController MEVCUT rapor fonksiyonunu cagirir (yetki + rakam ORADAN;
 *      rakami ASLA bu servis uretmez — halusinasyon olmasin diye).
 *   4) Donen gercek veriyi bu servis dogal Turkce cevaba cevirir (+ opsiyonel kart).
 *
 * Kural: AI/kural KARAR verir (hangi rapor), mevcut kod HESAPLAR (rakam),
 *        bu servis SUNAR (cevap metni). Ileride niyet cozumu Haiku'ya alinabilir;
 *        cevap + veri katmani aynen kalir.
 */
class PatronAsistanServisi
{
    /** Desteklenen niyetler icin anahtar kelimeler (ASCII-normalize edilmis metinde aranir) */
    protected $niyetAnahtarlari = [
        'kasa' => [
            'kasa', 'ciro', 'hasilat', 'kazanc', 'kazandik', 'para', 'gelir',
            'ne kadar', 'tahsilat', 'nakit', 'kart', 'satis',
        ],
        'personel' => [
            'personel', 'eleman', 'calisan', 'kim sattı', 'kim satti', 'en cok kim',
            'performans', 'hakedis', 'kim ne', 'basarili',
        ],
        'bugun' => [
            'bugun', 'gundem', 'randevu', 'takvim', 'kimler var', 'kimler gelecek',
            'durum ne', 'ne var ne yok', 'gunluk',
        ],
    ];

    /**
     * Serbest metinden niyeti + donemi cozer.
     * @return array{intent:string,donem:string,donemAdi:string,personelIpucu:?string,ham:string}
     */
    public function niyetCoz($metin)
    {
        $ham  = trim((string) $metin);
        $norm = $this->normalize($ham);

        $donem = $this->donemCoz($norm);

        // Niyet: anahtar kelime eslesme sayisina gore en yuksek skorlu niyet secilir.
        $skor = ['kasa' => 0, 'personel' => 0, 'bugun' => 0];
        foreach ($this->niyetAnahtarlari as $niyet => $kelimeler) {
            foreach ($kelimeler as $k) {
                if (strpos($norm, $this->normalize($k)) !== false) {
                    $skor[$niyet]++;
                }
            }
        }
        arsort($skor);
        $enIyi   = array_key_first($skor);
        $intent  = $skor[$enIyi] > 0 ? $enIyi : 'bilinmiyor';

        // "kim ne sattı" + isim gecerse personel niyeti one cikar
        $personelIpucu = $this->personelIpucuCoz($norm, $ham);
        if ($personelIpucu && $skor['personel'] === 0 && $skor['kasa'] === 0) {
            $intent = 'personel';
        }

        return [
            'intent'        => $intent,
            'donem'         => $donem['anahtar'],
            'donemAdi'      => $donem['ad'],
            'personelIpucu' => $personelIpucu,
            'ham'           => $ham,
        ];
    }

    /** Metinden zaman araligini cozer (varsayilan: bugun). */
    protected function donemCoz($norm)
    {
        if (preg_match('/\b(bu ?ay|aylik|son ?30|gecen ?ay|ay ?boyunca)\b/', $norm)) {
            return ['anahtar' => 'ay', 'ad' => 'bu ay'];
        }
        if (preg_match('/\b(bu ?hafta|haftalik|son ?7|hafta ?boyunca)\b/', $norm)) {
            return ['anahtar' => 'hafta', 'ad' => 'bu hafta'];
        }
        if (preg_match('/\b(dun)\b/', $norm)) {
            // Kasa/personel raporlari "dun"u ayri tutmuyor; en yakin bugun'e duser.
            return ['anahtar' => 'gun', 'ad' => 'bugun'];
        }
        return ['anahtar' => 'gun', 'ad' => 'bugun'];
    }

    /**
     * Personel adi ipucu: anahtar kelimeler cikarildiktan sonra kalan, buyuk harfle
     * baslayan olasi isim. Kesin eslestirme controller'da gercek personel listesiyle
     * yapilir; burada sadece aday dondururuz.
     */
    protected function personelIpucuCoz($norm, $ham)
    {
        $stop = ['bugun','bu','ay','hafta','ne','kadar','sattı','satti','yaptı','yapti',
                 'kim','en','cok','satis','kasa','ciro','personel','eleman','performans',
                 'gosterir','misin','bana','ver','getir','durum','nasil','gidiyor','ki',
                 'da','de','icin','the','a','an'];
        $kelimeler = preg_split('/\s+/', trim($ham));
        foreach ($kelimeler as $kel) {
            $temiz = preg_replace('/[^\p{L}]/u', '', $kel);
            if ($temiz === '') continue;
            $normKel = $this->normalize($temiz);
            if (in_array($normKel, $stop, true)) continue;
            if (mb_strlen($temiz, 'UTF-8') < 3) continue;
            // Ilk harfi buyukse ya da metinde tek basina bir ozel ad gibi duruyorsa aday.
            $ilk = mb_substr($temiz, 0, 1, 'UTF-8');
            if (mb_strtoupper($ilk, 'UTF-8') === $ilk) {
                return $temiz;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------
    // CEVAP YAZIMI — gelen GERCEK veriyi dogal Turkce metne cevirir
    // ------------------------------------------------------------------

    /** Kasa/ciro cevabi. $veri = dashboardKasa JSON (nakit,kart,havale,diger,toplam). */
    public function cevapKasa(array $veri, array $niyet)
    {
        $toplam = (float) ($veri['toplam'] ?? 0);
        $nakit  = (float) ($veri['nakit'] ?? 0);
        $kart   = (float) ($veri['kart'] ?? 0);
        $havale = (float) ($veri['havale'] ?? 0);
        $donemAdi = $niyet['donemAdi'];

        if ($toplam <= 0) {
            $cevap = ucfirst($donemAdi) . " icin henuz tahsilat gorunmuyor.";
        } else {
            $cevap = ucfirst($donemAdi) . " toplam tahsilat " . $this->tl($toplam) . ". "
                   . "Nakit " . $this->tl($nakit) . ", kart " . $this->tl($kart);
            if ($havale > 0) {
                $cevap .= ", havale " . $this->tl($havale);
            }
            $cevap .= ".";
        }

        return [
            'basarili' => true,
            'intent'   => 'kasa',
            'cevap'    => $cevap,
            'seslendir'=> true,
            'kart'     => [
                'tip'     => 'kasa',
                'baslik'  => 'Kasa · ' . ucfirst($donemAdi),
                'toplam'  => $toplam,
                'satirlar'=> [
                    ['etiket' => 'Nakit',  'tutar' => $nakit],
                    ['etiket' => 'Kart',   'tutar' => $kart],
                    ['etiket' => 'Havale', 'tutar' => $havale],
                ],
            ],
            'niyet'    => $niyet,
        ];
    }

    /** Personel performans cevabi. $veri = isletmeRaporlariPersonel JSON (personeller[]). */
    public function cevapPersonel(array $veri, array $niyet)
    {
        $liste = $veri['personeller'] ?? [];
        $donemAdi = $niyet['donemAdi'];

        // Belirli personel soruldu mu?
        if (!empty($niyet['personelIpucu'])) {
            $aday = $this->normalize($niyet['personelIpucu']);
            $bulunan = null; $sira = 0;
            foreach ($liste as $i => $p) {
                if (strpos($this->normalize($p['personel_adi'] ?? ''), $aday) !== false) {
                    $bulunan = $p; $sira = $i + 1; break;
                }
            }
            if ($bulunan) {
                $cevap = $bulunan['personel_adi'] . " " . $donemAdi . " "
                       . $this->tl((float) $bulunan['ciro']) . " ciro yaptı, "
                       . (int) $bulunan['hizmet_say'] . " islem. "
                       . "Siralamada " . $sira . ". sirada.";
                return [
                    'basarili' => true, 'intent' => 'personel', 'cevap' => $cevap,
                    'seslendir' => true,
                    'kart' => [
                        'tip' => 'personel_tek',
                        'baslik' => $bulunan['personel_adi'] . ' · ' . ucfirst($donemAdi),
                        'ciro' => (float) $bulunan['ciro'],
                        'islem' => (int) $bulunan['hizmet_say'],
                        'sira' => $sira,
                    ],
                    'niyet' => $niyet,
                ];
            }
        }

        // Genel: ilk 3 personel
        if (empty($liste)) {
            return [
                'basarili' => true, 'intent' => 'personel', 'seslendir' => true,
                'cevap' => ucfirst($donemAdi) . " icin personel satis verisi bulunamadı.",
                'kart' => null, 'niyet' => $niyet,
            ];
        }

        $ilk3 = array_slice($liste, 0, 3);
        $parcalar = [];
        foreach ($ilk3 as $i => $p) {
            $parcalar[] = ($i + 1) . ") " . $p['personel_adi'] . " " . $this->tl((float) $p['ciro']);
        }
        $cevap = ucfirst($donemAdi) . " en cok satan: " . implode(", ", $parcalar) . ".";

        return [
            'basarili' => true, 'intent' => 'personel', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip'    => 'personel_sirali',
                'baslik' => 'Personel · ' . ucfirst($donemAdi),
                'satirlar' => array_map(function ($p) {
                    return [
                        'ad'    => $p['personel_adi'],
                        'ciro'  => (float) $p['ciro'],
                        'islem' => (int) ($p['hizmet_say'] ?? 0),
                    ];
                }, $liste),
            ],
            'niyet' => $niyet,
        ];
    }

    /** Gunun ozeti cevabi. $veri = dashboardBugun JSON (liste[]). */
    public function cevapBugun(array $veri, array $niyet)
    {
        $liste = $veri['liste'] ?? [];
        $adet = count($liste);

        if ($adet === 0) {
            $cevap = "Bugun icin planlanmis randevu gorunmuyor.";
        } else {
            $ilk = $liste[0];
            $cevap = "Bugun " . $adet . " randevu var. Siradaki: "
                   . ($ilk['saat'] ?? '') . " " . ($ilk['musteri'] ?? '')
                   . (isset($ilk['hizmet']) ? " (" . $ilk['hizmet'] . ")" : "") . ".";
        }

        return [
            'basarili' => true, 'intent' => 'bugun', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip'    => 'bugun',
                'baslik' => 'Bugun · ' . $adet . ' randevu',
                'liste'  => $liste,
            ],
            'niyet' => $niyet,
        ];
    }

    /** Niyet anlasilamadiginda yardim/ornek cevabi. */
    public function yardimCevabi(array $niyet = [])
    {
        return [
            'basarili' => true,
            'intent'   => 'bilinmiyor',
            'seslendir'=> true,
            'cevap'    => "Tam anlayamadım. Sunlari sorabilirsin: "
                        . "\"bugun kasa ne durumda\", \"bu ay ciro ne kadar\", "
                        . "\"en cok kim sattı\", \"bugun kac randevu var\".",
            'ornekler' => [
                'Bugun kasa ne durumda?',
                'Bu ay ciro ne kadar?',
                'Bu hafta en cok kim sattı?',
                'Bugun kac randevu var?',
            ],
            'kart'  => null,
            'niyet' => $niyet,
        ];
    }

    // ------------------------------------------------------------------
    // Yardimcilar
    // ------------------------------------------------------------------

    /** Kanonik donemi ilgili rapor fonksiyonunun bekledigi period degerine cevirir. */
    public function periodDashboard($donem)
    {
        // dashboardKasa/dashboardBugun -> dashPeriodDates: daily | 7d | 30d
        switch ($donem) {
            case 'ay':    return '30d';
            case 'hafta': return '7d';
            default:      return 'daily';
        }
    }

    public function periodRapor($donem)
    {
        // isletmeRaporlariPersonel -> raporPeriodDates: bugun | hafta | ay
        switch ($donem) {
            case 'ay':    return 'ay';
            case 'hafta': return 'hafta';
            default:      return 'bugun';
        }
    }

    /** Turkce para bicimi: 12.500 ₺ */
    protected function tl($tutar)
    {
        return number_format((float) $tutar, 0, ',', '.') . ' ₺';
    }

    /** Turkce metni kucuk harfe + ASCII-benzeri hale getirir (aksan/karakter toleransi). */
    protected function normalize($metin)
    {
        $metin = mb_strtolower(trim((string) $metin), 'UTF-8');
        $ceviri = [
            'ç'=>'c','ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','ş'=>'s','ü'=>'u',
            'â'=>'a','î'=>'i','û'=>'u',
        ];
        $metin = strtr($metin, $ceviri);
        // Kalan buyuk I gibi durumlar
        $metin = str_replace(['i̇'], ['i'], $metin);
        return $metin;
    }
}
