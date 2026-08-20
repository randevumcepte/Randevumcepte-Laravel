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
            'kasa', 'ciro', 'hasilat', 'kazanc', 'kazandik', 'kazandim', 'kazanmis',
            'ne kadar kazan', 'kac para', 'para girdi', 'ne kadar para', 'ne kadar oldu',
            'tahsilat', 'nakit', 'gelir', 'topladik', 'kac lira', 'lira topla',
        ],
        'personel' => [
            'personel', 'eleman', 'calisan', 'kim satti', 'en cok kim', 'en iyi personel',
            'hangi personel', 'performans', 'hakedis', 'kim ne kadar', 'kim ne satti',
            'kim calisti', 'basarili',
        ],
        'hizmet' => [
            'hizmet', 'islem', 'hangi hizmet', 'populer hizmet', 'en cok yapilan',
            'cok yapilan', 'hizmet raporu', 'hangi islem',
        ],
        'urun' => [
            'urun', 'hangi urun', 'satan urun', 'urun satis', 'mamul', 'kozmetik',
        ],
        'musteri' => [
            'musteri', 'kac kisi', 'kac musteri', 'yeni musteri', 'kim geldi',
            'kisi geldi', 'sadik musteri', 'en iyi musteri', 'kadin erkek', 'musteri sayisi',
        ],
        'ozet' => [
            'ozet', 'genel durum', 'gun sonu', 'nasil gidiyor', 'nasil gecti',
            'isler nasil', 'toplam durum', 'bugun nasil', 'yolunda', 'bereket',
            'ne alemde', 'gunumuz nasil', 'nasil bir gun',
        ],
        'bugun' => [
            'randevu', 'takvim', 'gundem', 'kimler var', 'kimler gelecek', 'kim gelecek',
            'kac randevu', 'randevu var', 'gunluk program',
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
        $skor = array_fill_keys(array_keys($this->niyetAnahtarlari), 0);
        foreach ($this->niyetAnahtarlari as $niyet => $kelimeler) {
            foreach ($kelimeler as $k) {
                if (strpos($norm, $this->normalize($k)) !== false) {
                    $skor[$niyet]++;
                }
            }
        }
        // En yuksek skorlu niyet; esitlikte oncelik sirasina gore (deterministik).
        $oncelik = ['kasa', 'personel', 'hizmet', 'urun', 'musteri', 'bugun', 'ozet'];
        $enIyi = 'bilinmiyor'; $enYuksek = 0;
        foreach ($oncelik as $n) {
            if (($skor[$n] ?? 0) > $enYuksek) {
                $enYuksek = $skor[$n];
                $enIyi = $n;
            }
        }
        $intent = $enYuksek > 0 ? $enIyi : 'bilinmiyor';

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
            return ['anahtar' => 'gun', 'ad' => 'bugün'];
        }
        return ['anahtar' => 'gun', 'ad' => 'bugün'];
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
            $cevap = ucfirst($donemAdi) . " için henüz bir tahsilat görünmüyor.";
        } else {
            $cevap = ucfirst($donemAdi) . " toplam " . $this->tl($toplam) . " tahsilat gerçekleşmiş. "
                   . "Nakit olarak " . $this->tl($nakit) . ", kart ile " . $this->tl($kart) . " alınmış";
            if ($havale > 0) {
                $cevap .= ", ayrıca " . $this->tl($havale) . " havale ile ödenmiş";
            }
            $cevap .= ".";
        }

        // Kart KALDIRILDI: rakamlar zaten cevap metninde (okunuyor) — kart ayni veriyi
        // tekrarlayip "iki kere" gostermesin diye tek kaynak: metin.
        return [
            'basarili' => true,
            'intent'   => 'kasa',
            'cevap'    => $cevap,
            'seslendir'=> true,
            'kart'     => null,
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
                $cevap = $bulunan['personel_adi'] . ", " . $donemAdi . " toplam "
                       . $this->tl((float) $bulunan['ciro']) . " ciro yapmış ve "
                       . (int) $bulunan['hizmet_say'] . " işlem gerçekleştirmiş. "
                       . "Genel sıralamada " . $sira . ". sırada yer alıyor.";
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
                'cevap' => ucfirst($donemAdi) . " için henüz bir personel satışı görünmüyor.",
                'kart' => null, 'niyet' => $niyet,
            ];
        }

        $ilk3 = array_slice($liste, 0, 3);
        $siralar = ['ilk sırada', 'ikinci sırada', 'üçüncü sırada'];
        $parcalar = [];
        foreach ($ilk3 as $i => $p) {
            $parcalar[] = $siralar[$i] . " " . $p['personel_adi'] . ", " . $this->tl((float) $p['ciro']);
        }
        $cevap = ucfirst($donemAdi) . " en çok ciro yapan personeller; " . implode(", ", $parcalar) . ".";

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
            $cevap = "Bugün için planlanmış bir randevu görünmüyor.";
        } else {
            $ilk = $liste[0];
            $cevap = "Bugün toplam " . $adet . " randevu bulunuyor. Sıradaki randevu saat "
                   . ($ilk['saat'] ?? '') . ", " . ($ilk['musteri'] ?? '')
                   . (!empty($ilk['hizmet']) ? ", " . $ilk['hizmet'] . " için" : "") . ".";
        }

        return [
            'basarili' => true, 'intent' => 'bugun', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip'    => 'bugun',
                'baslik' => 'Bugün · ' . $adet . ' randevu',
                'liste'  => $liste,
            ],
            'niyet' => $niyet,
        ];
    }

    /**
     * Belirli DONEM icin randevu SAYISI cevabi ("bu hafta/bu ay kaç randevu").
     * $veri = ozet JSON (toplam_randevu vb.). "bugun" disindaki donemlerde kullanilir.
     */
    public function cevapRandevuDonem(array $veri, array $niyet)
    {
        $donemAdi = $niyet['donemAdi'];
        $adet = (int) ($veri['toplam_randevu'] ?? 0);
        $cevap = $adet > 0
            ? ucfirst($donemAdi) . " toplam " . $adet . " randevu bulunuyor."
            : ucfirst($donemAdi) . " için planlanmış bir randevu görünmüyor.";

        return [
            'basarili' => true, 'intent' => 'bugun', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip' => 'ozet',
                'baslik' => 'Randevular · ' . ucfirst($donemAdi),
                'toplam_randevu' => $adet,
                'toplam_adisyon' => (int) ($veri['toplam_adisyon'] ?? 0),
                'toplam_gelir' => (float) ($veri['toplam_gelir'] ?? 0),
                'nakit' => (float) ($veri['nakit'] ?? 0),
                'kart' => (float) ($veri['kart'] ?? 0),
            ],
            'niyet' => $niyet,
        ];
    }

    /** Hizmet karlilik cevabi. $veri = isletmeRaporlariHizmet JSON. */
    public function cevapHizmet(array $veri, array $niyet)
    {
        $liste = $veri['hizmetler'] ?? [];
        $donemAdi = $niyet['donemAdi'];
        if (empty($liste)) {
            return $this->basitCevap('hizmet', ucfirst($donemAdi) . " için henüz bir hizmet kaydı görünmüyor.", $niyet);
        }
        $ilk3 = array_slice($liste, 0, 3);
        $siralar = ['ilk sırada', 'ikinci sırada', 'üçüncü sırada'];
        $parcalar = [];
        foreach ($ilk3 as $i => $h) {
            $ad = is_array($h) ? ($h['hizmet_adi'] ?? '') : ($h->hizmet_adi ?? '');
            $ciro = is_array($h) ? ($h['ciro'] ?? 0) : ($h->ciro ?? 0);
            $parcalar[] = $siralar[$i] . " " . $ad . ", " . $this->tl((float) $ciro);
        }
        $cevap = ucfirst($donemAdi) . " en çok kazandıran hizmetler; " . implode(", ", $parcalar) . ".";
        return [
            'basarili' => true, 'intent' => 'hizmet', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => ['tip' => 'hizmet', 'baslik' => 'Hizmet · ' . ucfirst($donemAdi), 'satirlar' => $liste],
            'niyet' => $niyet,
        ];
    }

    /** Urun karlilik cevabi. $veri = isletmeRaporlariUrun JSON. */
    public function cevapUrun(array $veri, array $niyet)
    {
        $liste = $veri['urunler'] ?? [];
        $donemAdi = $niyet['donemAdi'];
        if (empty($liste)) {
            return $this->basitCevap('urun', ucfirst($donemAdi) . " için henüz bir ürün satışı görünmüyor.", $niyet);
        }
        $ilk3 = array_slice($liste, 0, 3);
        $siralar = ['ilk sırada', 'ikinci sırada', 'üçüncü sırada'];
        $parcalar = [];
        foreach ($ilk3 as $i => $u) {
            $ad = is_array($u) ? ($u['urun_adi'] ?? '') : ($u->urun_adi ?? '');
            $ciro = is_array($u) ? ($u['ciro'] ?? 0) : ($u->ciro ?? 0);
            $parcalar[] = $siralar[$i] . " " . $ad . ", " . $this->tl((float) $ciro);
        }
        $cevap = ucfirst($donemAdi) . " en çok satılan ürünler; " . implode(", ", $parcalar) . ".";
        return [
            'basarili' => true, 'intent' => 'urun', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => ['tip' => 'urun', 'baslik' => 'Ürün · ' . ucfirst($donemAdi), 'satirlar' => $liste],
            'niyet' => $niyet,
        ];
    }

    /** Musteri ozeti cevabi. $veri = isletmeRaporlariMusteri JSON. */
    public function cevapMusteri(array $veri, array $niyet)
    {
        $donemAdi = $niyet['donemAdi'];
        $aktif = (int) ($veri['toplam_aktif'] ?? 0);
        $yeni  = (int) ($veri['yeni_musteri'] ?? 0);
        $tekrar = (int) ($veri['tekrar_gelen'] ?? 0);
        if ($aktif === 0) {
            return $this->basitCevap('musteri', ucfirst($donemAdi) . " için henüz bir müşteri hareketi görünmüyor.", $niyet);
        }
        $cevap = ucfirst($donemAdi) . " toplam " . $aktif . " müşteri gelmiş. Bunların "
               . $yeni . " tanesi yeni, " . $tekrar . " tanesi tekrar gelen müşteri.";
        return [
            'basarili' => true, 'intent' => 'musteri', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip' => 'musteri', 'baslik' => 'Müşteri · ' . ucfirst($donemAdi),
                'toplam_aktif' => $aktif, 'yeni' => $yeni, 'tekrar' => $tekrar,
                'cinsiyet' => $veri['cinsiyet'] ?? null,
            ],
            'niyet' => $niyet,
        ];
    }

    /** Genel ozet / gun sonu cevabi. $veri = isletmeRaporlariOzet JSON. */
    public function cevapOzet(array $veri, array $niyet)
    {
        $donemAdi = $niyet['donemAdi'];
        $gelir = (float) ($veri['toplam_gelir'] ?? 0);
        $randevu = (int) ($veri['toplam_randevu'] ?? 0);
        $adisyon = (int) ($veri['toplam_adisyon'] ?? 0);
        $cevap = ucfirst($donemAdi) . " özeti: toplam tahsilat " . $this->tl($gelir)
               . ", " . $randevu . " randevu, " . $adisyon . " adisyon.";
        return [
            'basarili' => true, 'intent' => 'ozet', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip' => 'ozet', 'baslik' => 'Genel Özet · ' . ucfirst($donemAdi),
                'toplam_gelir' => $gelir, 'toplam_randevu' => $randevu, 'toplam_adisyon' => $adisyon,
                'satilan_urun' => (int) ($veri['satilan_urun'] ?? 0),
                'uygulanan_hizmet' => (int) ($veri['uygulanan_hizmet'] ?? 0),
                'nakit' => (float) ($veri['nakit'] ?? 0),
                'kart' => (float) ($veri['kart'] ?? 0),
            ],
            'niyet' => $niyet,
        ];
    }

    /**
     * ZENGIN GUN OZETI: kasa + en cok satilan hizmet + (varsa) urun + en cok islem
     * yapan personel + samimi yorum. Hepsi tek cevapta. $veri controller'da toplanir:
     *   ['kasa'=>[toplam,nakit,kart], 'enHizmet'=>[ad,adet,ciro]|null,
     *    'enUrun'=>[ad,ciro]|null, 'enPersonel'=>[ad,islem]|null]
     */
    public function cevapGunOzeti(array $veri, array $niyet)
    {
        $donemAdi = $niyet['donemAdi'];
        $kasa = $veri['kasa'] ?? [];
        $toplam = (float) ($kasa['toplam'] ?? 0);

        // Duzgun, tam kurulmus cumleler (her metrik ayri cumle -> TTS dogal/vurgulu okur).
        $cumleler = [];
        if ($toplam > 0) {
            $cumleler[] = ucfirst($donemAdi) . " kasada toplam " . $this->tl($toplam) . " bulunuyor";
        } else {
            $cumleler[] = ucfirst($donemAdi) . " kasada henüz bir hareket görünmüyor";
        }
        if (!empty($veri['enHizmet']['ad'])) {
            $cumleler[] = "En çok uygulanan hizmet " . $veri['enHizmet']['ad']
                        . ", toplam " . (int) $veri['enHizmet']['adet'] . " kez gerçekleştirilmiş";
        }
        // Urun SADECE varsa soylenir (yoksa hic bahsedilmez).
        if (!empty($veri['enUrun']['ad'])) {
            $cumleler[] = "En çok satılan ürün " . $veri['enUrun']['ad']
                        . ", " . $this->tl($veri['enUrun']['ciro']) . " gelir getirmiş";
        }
        if (!empty($veri['enPersonel']['ad'])) {
            $cumleler[] = "En çok işlem yapan personel " . $veri['enPersonel']['ad']
                        . ", toplam " . (int) $veri['enPersonel']['islem'] . " işlem gerçekleştirmiş";
        }

        $cevap = implode(". ", $cumleler) . ". " . $this->gunYorum($toplam, $veri);

        return [
            'basarili' => true, 'intent' => 'ozet', 'cevap' => $cevap, 'seslendir' => true,
            'kart' => [
                'tip'        => 'gun_ozeti',
                'baslik'     => 'Günün Özeti · ' . ucfirst($donemAdi),
                'kasa'       => $toplam,
                'nakit'      => (float) ($kasa['nakit'] ?? 0),
                'kart'       => (float) ($kasa['kart'] ?? 0),
                'enHizmet'   => $veri['enHizmet'] ?? null,
                'enUrun'     => $veri['enUrun'] ?? null,
                'enPersonel' => $veri['enPersonel'] ?? null,
            ],
            'niyet' => $niyet,
        ];
    }

    /**
     * Samimi yorum — HER SEFERINDE FARKLI olsun diye rastgele secilir (ezbere durmaz).
     * Bedava (AI'a gerek yok). Sesli asistan icin DUZ yazim: unlem yok, az noktalama.
     */
    protected function gunYorum($toplam, array $veri)
    {
        if ($toplam <= 0) {
            $sifir = [
                'Gün daha yeni bol bereketli olsun',
                'Henüz erken güzel bir gün diliyorum',
                'Gün yeni başlıyor bereketli geçsin',
                'Bugün için henüz erken umarım güzel geçer',
            ];
            return $sifir[array_rand($sifir)] . '.';
        }
        $genel = [
            'Gün güzel gidiyor eline sağlık',
            'Bugün bereketli görünüyor aynen devam',
            'İşler yolunda tebrik ederim',
            'Güzel bir gün geçiriyorsun böyle devam',
            'Bugün gayet iyi görünüyor',
            'Fena değil istikrarı koru',
            'Güzel bir tempo yakalamışsın',
            'İyi bir gün emeğine sağlık',
            'Bugün için memnun olabilirsin',
            'Akışında güzel bir gün',
        ];
        $yorum = $genel[array_rand($genel)];
        if (!empty($veri['enPersonel']['ad'])) {
            $ad = $veri['enPersonel']['ad'];
            $ovgu = [
                ' Bugün en çok emeği geçen ' . $ad . ' olmuş',
                ' ' . $ad . ' bugün gerçekten yoğun çalışmış',
                ' ' . $ad . ' bugünkü performansıyla öne çıkıyor',
                ' Özellikle ' . $ad . ' bugün çok iş çıkarmış',
                '', // bazen ovgu olmasin -> cesitlilik
                '', // ovgusuz ihtimali biraz artir
            ];
            $yorum .= $ovgu[array_rand($ovgu)];
        }
        return $yorum . '.';
    }

    /** Kisa/veri-yok cevaplari icin ortak sarmalayici. */
    protected function basitCevap($intent, $metin, array $niyet)
    {
        return [
            'basarili' => true, 'intent' => $intent, 'cevap' => $metin,
            'seslendir' => true, 'kart' => null, 'niyet' => $niyet,
        ];
    }

    // ------------------------------------------------------------------
    // OGRENEN ONBELLEK: AI ile bir kez cozuleni sakla -> sonraki sefer bedava
    // ------------------------------------------------------------------

    /**
     * Daha once (AI ile) cozulmus bir sorunun niyetini dondurur; yoksa null.
     * Ayni/normalize-esit soru tekrar gelince AI'ya GITMEDEN bedava cevaplanir.
     * Niyet salon-bagimsiz (sadece cumle -> intent/donem) oldugu icin tum salonlar paylasir.
     */
    public function ogrenilenNiyet($metin)
    {
        try {
            $v = \Cache::get($this->ogrenAnahtar($metin));
            if (is_array($v) && !empty($v['intent'])) {
                $v['ham'] = trim((string) $metin);
                return $v;
            }
        } catch (\Throwable $e) {}
        return null;
    }

    /** AI bir soruyu basariyla cozunce eslesmeyi kalici sakla (sonraki sefer bedava). */
    public function ogren($metin, array $niyet)
    {
        if (($niyet['intent'] ?? 'bilinmiyor') === 'bilinmiyor') return;
        try {
            \Cache::forever($this->ogrenAnahtar($metin), [
                'intent'        => $niyet['intent'],
                'donem'         => $niyet['donem'] ?? 'gun',
                'donemAdi'      => $niyet['donemAdi'] ?? 'bugün',
                'personelIpucu' => $niyet['personelIpucu'] ?? null,
            ]);
        } catch (\Throwable $e) {}
    }

    protected function ogrenAnahtar($metin)
    {
        return 'patron_asistan_ogr:' . md5($this->normalize($metin));
    }

    // ------------------------------------------------------------------
    // OPSIYONEL: Haiku ile niyet cozumu (TAM SERBEST DIYALOG)
    // ------------------------------------------------------------------

    /**
     * ANTHROPIC_API_KEY tanimliysa, serbest metni Haiku'ya function-calling ile cozdurur.
     * Haiku SADECE niyeti yapilandirir (hangi rapor + donem); RAKAM URETMEZ.
     * Key yoksa / hata olursa null doner -> cagiran taraf kural motoruna duser.
     *
     * @return array|null niyetCoz() ile ayni yapida (intent/donem/donemAdi/personelIpucu/ham) veya null
     */
    /** Son AI cagrisinin teshisi (debug): anahtar_yok | http_XXX | curl_HATA | yanit_bos | tool_yok | ok */
    public $aiTeshis = null;

    public function niyetCozAI($metin)
    {
        // config once (config:cache'liyse de calisir), yoksa env fallback.
        $apiKey = config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY');
        if (!$apiKey) {
            $this->aiTeshis = 'anahtar_yok';
            return null; // Anahtar yok -> sessizce kural motoruna dus
        }

        $ham   = trim((string) $metin);
        $araclar = [[
            'name'        => 'rapor_sec',
            'description' => 'Patronun sorusuna gore hangi isletme raporunun hangi donem icin getirilecegini secer.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'intent' => [
                        'type' => 'string',
                        'enum' => ['kasa','personel','hizmet','urun','musteri','ozet','bugun','bilinmiyor'],
                        'description' => 'kasa=ciro/tahsilat, personel=kim ne sattı, hizmet=hizmet karlilik, urun=urun satis, musteri=musteri ozeti, ozet=genel/gun sonu, bugun=bugunku randevular, bilinmiyor=anlasilamadi',
                    ],
                    'donem' => [
                        'type' => 'string',
                        'enum' => ['gun','hafta','ay'],
                        'description' => 'Zaman araligi: gun=bugun, hafta=bu hafta, ay=bu ay',
                    ],
                    'personel_adi' => [
                        'type' => 'string',
                        'description' => 'Soru belirli bir personel hakkindaysa o kisinin adi, degilse bos birak',
                    ],
                ],
                'required' => ['intent','donem'],
            ],
        ]];

        $govde = [
            'model'      => config('services.anthropic.model') ?: 'claude-haiku-4-5',
            'max_tokens' => 256,
            'tool_choice'=> ['type' => 'tool', 'name' => 'rapor_sec'],
            'tools'      => $araclar,
            'system'     => 'Sen bir salon isletme yonetim panelinin asistanisin. Patronun Turkce (argo/yarim cumle olabilir) sorusunu oku ve rapor_sec aracini cagirarak hangi raporu hangi donem icin istedigini belirt. Rakam URETME, yorum yapma; sadece araci cagir.',
            'messages'   => [['role' => 'user', 'content' => $ham]],
        ];

        try {
            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_HTTPHEADER     => [
                    'content-type: application/json',
                    'x-api-key: ' . $apiKey,
                    'anthropic-version: 2023-06-01',
                ],
                CURLOPT_POSTFIELDS     => json_encode($govde, JSON_UNESCAPED_UNICODE),
            ]);
            $yanit = curl_exec($ch);
            $kod   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlHata = curl_error($ch);
            curl_close($ch);

            if ($yanit === false || $kod !== 200) {
                $this->aiTeshis = ($yanit === false)
                    ? ('curl_HATA: ' . $curlHata)
                    : ('http_' . $kod . ': ' . mb_substr((string) $yanit, 0, 300));
                return null;
            }
            $data = json_decode($yanit, true);
            if (!is_array($data) || empty($data['content'])) {
                $this->aiTeshis = 'yanit_bos';
                return null;
            }
            // tool_use blogunu bul
            $tool = null;
            foreach ($data['content'] as $blok) {
                if (($blok['type'] ?? '') === 'tool_use' && ($blok['name'] ?? '') === 'rapor_sec') {
                    $tool = $blok['input'] ?? null;
                    break;
                }
            }
            if (!is_array($tool) || empty($tool['intent'])) {
                $this->aiTeshis = 'tool_yok';
                return null;
            }

            $donem = in_array($tool['donem'] ?? '', ['gun','hafta','ay'], true) ? $tool['donem'] : 'gun';
            $donemAdi = ['gun' => 'bugün', 'hafta' => 'bu hafta', 'ay' => 'bu ay'][$donem];
            $personel = trim((string) ($tool['personel_adi'] ?? '')) ?: null;
            $this->aiTeshis = 'ok';

            return [
                'intent'        => $tool['intent'],
                'donem'         => $donem,
                'donemAdi'      => $donemAdi,
                'personelIpucu' => $personel,
                'ham'           => $ham,
                '_kaynak'       => 'ai',
            ];
        } catch (\Throwable $e) {
            $this->aiTeshis = 'exception: ' . $e->getMessage();
            return null; // Her turlu hatada kural motoruna dus
        }
    }

    /**
     * Terbiyesiz / kufur / hakaret iceriyor mu? Kelime siniriyla aranir (yanlis
     * pozitif azaltmak icin; is terimleriyle cakisan kelimeler listede YOK).
     */
    public function kufurMu($metin)
    {
        $norm = ' ' . $this->normalize($metin) . ' ';
        $kelimeler = [
            'amk', 'aq', 'amina', 'amina koyayim', 'amcik', 'orospu', 'oc', 'pic',
            'siktir', 'sikeyim', 'sikerim', 'sikik', 'sicayim', 'yarrak', 'yarak',
            'gavat', 'kahpe', 'ibne', 'serefsiz', 'pezevenk', 'gerizekali', 'salak',
            'aptal', 'embesil', 'denyo', 'yavsak', 'surtuk', 'godos',
        ];
        foreach ($kelimeler as $k) {
            if (strpos($norm, ' ' . $this->normalize($k) . ' ') !== false) {
                return true;
            }
        }
        return false;
    }

    /** Terbiyesiz konusmaya NAZIK uyari. */
    public function kufurCevabi()
    {
        return [
            'basarili'  => true,
            'intent'    => 'uyari',
            'seslendir' => true,
            'cevap'     => 'Efendim sizi saygıya davet ediyorum. Eğer böyle konuşmaya devam ederseniz görüşmeyi kapatmak zorunda kalacağım.',
            'kart'      => null,
        ];
    }

    /**
     * Niyet anlasilamadi / konu disi soru (ör. mac skoru, hava durumu).
     * Siri tarzi nazik geri cevirme + salon konularina yonlendirme.
     */
    public function yardimCevabi(array $niyet = [])
    {
        return [
            'basarili' => true,
            'intent'   => 'bilinmiyor',
            'seslendir'=> true,
            'cevap'    => "Efendim bu konu hakkında bilgim yok, ama isterseniz salonunuz "
                        . "hakkında bilgi verebilirim. Örneğin bugünkü kasa, en çok satılan "
                        . "hizmet ya da personel performansı gibi konularda yardımcı olabilirim.",
            'ornekler' => [
                'Bugün kasa ne durumda?',
                'Bu ay ciro ne kadar?',
                'Bu hafta en çok kim sattı?',
                'Bugün kaç randevu var?',
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
        // Sonuna "TL" (₺ sembolunu TTS okuyamiyordu). Binlik ayraci nokta kalir.
        return number_format((float) $tutar, 0, ',', '.') . ' TL';
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
