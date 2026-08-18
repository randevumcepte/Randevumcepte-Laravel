<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\SalonHizmetler;
use App\Personeller;

/**
 * Sesli / yazili komuttan randevu bilgisi cozumleyen KURAL MOTORU.
 *
 * LLM YOKTUR — tamamen PHP kurallariyla calisir (sifir maliyet).
 * Amac: "yarin 2'de Mehmet Bey'e sac kesimi" gibi bir Turkce cumleyi
 *   { intent, musteri, hizmetler, personel, tarih, saat, eksik_alanlar, guven }
 * yapisina cevirmek. Randevuyu OLUSTURMAZ — sadece cozumler; olusturma
 * onay sonrasi mevcut ApiController@randevuekleguncelle ile yapilir.
 *
 * v1: sadece "randevu_olustur" intent'i uygulanir; diger intent'ler tespit
 *     edilip "desteklenmiyor" bayragiyla doner (iskelet hazir).
 */
class SesliRandevuCozService
{
    /** @var int */
    protected $salonId;

    /** @var int|null Giris yapan personel (randevu onun adina; hizmetler onunkiyle sinirli) */
    protected $personelId;

    /** Gecici teshis bilgisi */
    protected $dbg = [];

    /** Turkce gun adlari => Carbon dayOfWeek (0=pazar ... 6=cumartesi) */
    protected $gunler = [
        'pazartesi' => Carbon::MONDAY,
        'sali'      => Carbon::TUESDAY,
        'carsamba'  => Carbon::WEDNESDAY,
        'persembe'  => Carbon::THURSDAY,
        'cuma'      => Carbon::FRIDAY,
        'cumartesi' => Carbon::SATURDAY,
        'pazar'     => Carbon::SUNDAY,
    ];

    /** Turkce ay adlari => ay no */
    protected $aylar = [
        'ocak' => 1, 'subat' => 2, 'mart' => 3, 'nisan' => 4, 'mayis' => 5,
        'haziran' => 6, 'temmuz' => 7, 'agustos' => 8, 'eylul' => 9,
        'ekim' => 10, 'kasim' => 11, 'aralik' => 12,
    ];

    /** Musteri adini bulurken atilacak dolgu kelimeleri */
    protected $stopKelimeler = [
        'randevu', 'randevusu', 'randevuyu', 'ver', 'versene', 'yaz', 'ekle', 'olustur',
        'olusturur', 'musun', 'lutfen', 'icin', 'ile', 've', 'bir',
        // tarih ekli/yalin
        'bugun', 'bugune', 'yarin', 'yarina', 'obur', 'ertesi', 'haftaya',
        'gun', 'gune', 'gunu', 'gunku',
        // saat ekli/yalin
        'saat', 'saatte', 'saatinde', 'sabah', 'sabaha', 'ogleden', 'once', 'sonra',
        'aksam', 'aksama', 'gece', 'geceye', 'ogle', 'ogleye', 'bucuk', 'bucukta',
        'bucugu', 'ceyrek', 'ceyrekte',
        // lokatif/ekler tek basina kalinca
        'te', 'de', 'da', 'ta',
        // hitap
        'bey', 'beye', 'beyi', 'hanim', 'hanima', 'hanimi', 'hn', 'by',
        'kardes', 'abi', 'abla', 'usta',
    ];

    /**
     * Ana giris noktasi.
     *
     * @param  string   $metin       Kullanicinin soyledigi / yazdigi cumle
     * @param  int      $salonId     Salon id
     * @param  int|null $personelId  Varsa: randevu bu personel adina; hizmetler onunkiyle sinirli
     * @return array
     */
    public function coz($metin, $salonId, $personelId = null)
    {
        $this->salonId = (int) $salonId;
        $this->personelId = $personelId ? (int) $personelId : null;

        $ham = trim((string) $metin);
        // Girdi UTF-8 degilse (latin5/Windows-1254 tek baytlar) once cevir; yoksa Turkce
        // harfler foldlanamaz ve silinir ("yarin"->"yar n", "sac"->"sa").
        if (!mb_check_encoding($ham, 'UTF-8')) {
            $ham = mb_convert_encoding($ham, 'UTF-8', 'Windows-1254');
        }
        $fold   = $this->fold($ham);           // karsilastirma icin sadelestirilmis metin
        $intent = $this->intentBul($fold);

        $tarih = $this->tarihCoz($fold);
        $saat  = $this->saatCoz($fold);

        // Datif ekli isim (Ferdi Korkmaz'A) MUSTERI'dir — bu ipucu, ayni adli bir
        // personelin musteri adini kapmasini onler ("Ferdi" personel degil, musteri).
        $musteriIpucu = $this->datifMusteriBul($fold);

        // Hizmet: personel verildiyse SADECE o personelin sundugu hizmetlerle sinirli
        list($hizmetler, $hizmetMetinleri) = $this->hizmetEslestir($fold);

        // Personel: giris yapan personel SABIT (cumleden cozumlenmez). Yoksa cumleden.
        if ($this->personelId) {
            $p = Personeller::find($this->personelId);
            $personel = $p ? ['personel_id' => (int) $p->id, 'personel_adi' => $p->personel_adi, 'sabit' => true] : null;
            $personelMetni = null;
        } else {
            list($personel, $personelMetni) = $this->personelEslestir($fold, $hizmetler, $musteriIpucu);
        }

        // Musteri: datif ipucu varsa onu kullan, yoksa kalan kelimelerden ad tahmini
        $musteri = $this->musteriEslestir($ham, $fold, $hizmetMetinleri, $personelMetni, $musteriIpucu);

        $eksik = $this->eksikAlanlar($musteri, $hizmetler, $tarih, $saat);
        $guven = $this->guvenHesapla($intent, $musteri, $hizmetler, $tarih, $saat);

        return [
            'basarili'      => $intent === 'randevu_olustur',
            'intent'        => $intent,
            'desteklenmiyor' => $intent !== 'randevu_olustur',
            'ham_metin'     => $ham,
            'musteri'       => $musteri,
            'hizmetler'     => $hizmetler,
            'personel'      => $personel,
            'tarih'         => $tarih,      // Y-m-d | null
            'saat'          => $saat,       // H:i   | null
            'eksik_alanlar' => $eksik,      // ['musteri','hizmet','tarih','saat','personel'] alt kumesi
            'guven'         => $guven,      // yuksek | orta | dusuk
            '_ver'          => 'dbg-6',     // GECICI: dagitim/opcache teshisi
            '_debug'        => array_merge(['fold' => $fold], $this->dbg),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* INTENT                                                             */
    /* ------------------------------------------------------------------ */

    protected function intentBul($fold)
    {
        // Saat kapatma: "yarin 2'den sonra kapat", "bugun kapali"
        if (preg_match('/\bkapat\b|\bkapali\b|\bkapama\b|\bmesai.*kapat/u', $fold)) {
            return 'saat_kapat';
        }
        // Personel izni: "Merve bugun ogleden sonra izinli"
        if (preg_match('/\bizinli\b|\bizin\b|\brapor(lu)?\b/u', $fold)) {
            return 'personel_izin';
        }
        return 'randevu_olustur';
    }

    /**
     * Datif ekli ismi (musteri) bulur: "Ferdi Korkmaz'a", "Ayse'ye".
     * Apostrof + a/e/ya/ye eki guclu musteri sinyalidir. Ekten onceki 1-3 kelimeyi
     * (dolgu/tarih/saat kelimelerini eleyerek) musteri adi olarak doner.
     */
    protected function datifMusteriBul($fold)
    {
        if (!preg_match('/([a-z]+(?:\s+[a-z]+){0,2})[\x{2019}\x{27}](?:a|e|ya|ye|na|ne)\b/u', $fold, $m)) {
            return null;
        }
        $kelimeler = array_values(array_filter(preg_split('/\s+/u', trim($m[1])), function ($w) {
            return mb_strlen($w) >= 2 && !in_array($w, $this->stopKelimeler, true)
                && !array_key_exists($w, $this->gunler)
                && !array_key_exists($w, $this->aylar);
        }));
        if (empty($kelimeler)) {
            return null;
        }
        return implode(' ', array_slice($kelimeler, -3)); // en fazla ad + soyad (+1)
    }

    /* ------------------------------------------------------------------ */
    /* TARIH                                                              */
    /* ------------------------------------------------------------------ */

    protected function tarihCoz($fold)
    {
        $bugun = Carbon::today();

        // 1) Kesin format: 2026-07-28 / 28.07.2026 / 28/07
        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/u', $fold, $m)) {
            return $this->guvenliTarih($m[1], $m[2], $m[3]);
        }
        // dd.mm(.yyyy) — ay 1-12 ve gun 1-31 degilse tarih SAYMA ("14.30" saat olabilir)
        if (preg_match('/\b(\d{1,2})[.\/](\d{1,2})(?:[.\/](\d{2,4}))?\b/u', $fold, $m)
            && (int) $m[2] >= 1 && (int) $m[2] <= 12 && (int) $m[1] >= 1 && (int) $m[1] <= 31) {
            $yil = isset($m[3]) && $m[3] !== '' ? $this->yilNormalize($m[3]) : $bugun->year;
            return $this->guvenliTarih($yil, $m[2], $m[1]);
        }

        // 2) Goreli: bugun / yarin / obur gun / ertesi gun
        if (preg_match('/\bbugun\b/u', $fold))                  return $bugun->toDateString();
        if (preg_match('/\byarin\b/u', $fold))                  return $bugun->copy()->addDay()->toDateString();
        if (preg_match('/\bobur gun\b|\bertesi gun\b/u', $fold)) return $bugun->copy()->addDays(2)->toDateString();

        // 3) "15 agustos" / "3 nisan"
        if (preg_match('/\b(\d{1,2})\s+(' . implode('|', array_keys($this->aylar)) . ')\b/u', $fold, $m)) {
            $ay  = $this->aylar[$m[2]];
            $gun = (int) $m[1];
            if (!checkdate($ay, $gun, $bugun->year)) {
                return null;
            }
            $t = Carbon::createFromDate($bugun->year, $ay, $gun);
            if ($t->lt($bugun)) {
                $t->addYear(); // gecmisse gelecek yila al
            }
            return $t->toDateString();
        }

        // 4) Gun adi: "cumartesi", "haftaya sali", "bu cuma"
        foreach ($this->gunler as $ad => $dow) {
            if (preg_match('/\b' . $ad . '\b/u', $fold)) {
                $hedef = $bugun->copy()->next($dow);            // her zaman ILERI tarih
                if (preg_match('/\bhaftaya\b/u', $fold)) {
                    $hedef->addWeek();
                }
                return $hedef->toDateString();
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------ */
    /* SAAT                                                               */
    /* ------------------------------------------------------------------ */

    protected function saatCoz($fold)
    {
        $ogledenSonra = (bool) preg_match('/\bogleden sonra\b|\baksam\b|\bgece\b/u', $fold);
        $sabah        = (bool) preg_match('/\bsabah\b|\bogleden once\b/u', $fold);

        // 1) 14:30 / 9.45
        if (preg_match('/\b(\d{1,2})[:.](\d{2})\b/u', $fold, $m)) {
            return $this->saatFormat((int) $m[1], (int) $m[2], $ogledenSonra, $sabah, true);
        }

        // 2) "2 bucuk" / "3 bucukta" (ekli hali de) -> :30
        if (preg_match('/\b(\d{1,2})\s*bucuk/u', $fold, $m)) {
            return $this->saatFormat((int) $m[1], 30, $ogledenSonra, $sabah, false);
        }

        // 3) "saat 14" / "2'de" / "saat 2" / "ogleden sonra 3"
        if (preg_match('/\bsaat\s+(\d{1,2})\b/u', $fold, $m)
            || preg_match('/\b(\d{1,2})[\'’]?(?:de|da|te|ta)\b/u', $fold, $m)
            || preg_match('/\b(?:ogleden sonra|aksam|sabah)\s+(\d{1,2})\b/u', $fold, $m)) {
            return $this->saatFormat((int) $m[1], 0, $ogledenSonra, $sabah, false);
        }

        return null;
    }

    /**
     * Saati normalize eder ve H:i doner.
     *
     * @param bool $kesin  Kullanici saati acikca yazdi mi (14:30 gibi)? Ise AM/PM tahmini yapilmaz.
     */
    protected function saatFormat($saat, $dakika, $ogledenSonra, $sabah, $kesin)
    {
        if ($saat < 0 || $saat > 23 || $dakika < 0 || $dakika > 59) {
            return null;
        }

        if (!$kesin) {
            if ($ogledenSonra && $saat < 12) {
                $saat += 12;
            } elseif ($sabah && $saat == 12) {
                $saat = 0;
            } elseif (!$ogledenSonra && !$sabah && $saat >= 1 && $saat <= 7) {
                // Salon calisma saati mantigi: yalin kucuk saat ogleden sonra kabul edilir
                // ("yarin saat 2" -> 14:00). Rakip uygulama da boyle davraniyor.
                $saat += 12;
            }
        }

        return sprintf('%02d:%02d', $saat, $dakika);
    }

    /* ------------------------------------------------------------------ */
    /* HIZMET                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * @return array [0 => eslesen hizmetler[], 1 => metinde eslesen fold parcalari[]]
     */
    protected function hizmetEslestir($fold)
    {
        $query = SalonHizmetler::where('salon_id', $this->salonId);

        // Personel verildiyse SADECE onun sundugu hizmetler (personel_sunulan_hizmetler)
        if ($this->personelId) {
            $hizmetIdleri = \App\PersonelHizmetler::where('personel_id', $this->personelId)
                ->pluck('hizmet_id')->all();
            $query->whereIn('hizmet_id', $hizmetIdleri ?: [0]); // bos ise hicbir hizmet eslesmez
        }

        $liste = $query->get();

        // Metnin kelimeleri (kelime-bazli eslestirme icin)
        $metinKelimeleri = array_values(array_filter(
            preg_split('/\s+/u', $fold),
            function ($w) { return mb_strlen($w) >= 2; }
        ));

        $eslesen = [];
        $metinler = [];
        foreach ($liste as $sh) {
            $ad = optional($sh->hizmetler)->hizmet_adi;
            if (!$ad) {
                continue;
            }
            $adFold = $this->fold($ad);
            if ($adFold === '') {
                continue;
            }

            // KELIME-BAZLI eslestirme: hizmetin TUM kelimeleri (ayirt edici olan
            // dahil, or. "cilt"/"sac") metinde gecmeli. Boylece "cilt bakimi" yalnizca
            // "Cilt Bakimi" ile eslesir; ortak "bakimi" yuzunden "Sac Bakimi"ye kaymaz.
            $skor = 0.0;
            if (mb_strpos($fold, $adFold) !== false) {
                $skor = 1.0; // tam hizmet adi cumlede geciyor
            } else {
                $adKelimeleri = array_values(array_filter(
                    preg_split('/\s+/u', $adFold),
                    function ($w) { return mb_strlen($w) >= 2; }
                ));
                if (!empty($adKelimeleri)) {
                    $eslesenKelime = 0;
                    foreach ($adKelimeleri as $ak) {
                        foreach ($metinKelimeleri as $mk) {
                            if ($ak === $mk || $this->benzerlik($ak, $mk) >= 0.85) {
                                $eslesenKelime++;
                                break;
                            }
                        }
                    }
                    if ($eslesenKelime === count($adKelimeleri)) {
                        $skor = 0.9; // hizmetin tum kelimeleri metinde var
                    }
                }
            }

            if ($skor >= 0.9) {
                $eslesen[] = [
                    'hizmet_id'      => $sh->hizmet_id,
                    'salon_hizmet_id'=> $sh->id,
                    'hizmet_adi'     => $ad,
                    'sure_dk'        => 30, // hizmetler tablosunda sure yok; onay ekraninda ayarlanabilir
                    'fiyat'          => $sh->son_fiyat ?: $sh->baslangic_fiyat,
                    'skor'           => round($skor, 2),
                ];
                $metinler[] = $adFold;
            }
        }

        // En yuksek skora gore sirala
        usort($eslesen, function ($a, $b) {
            return $b['skor'] <=> $a['skor'];
        });

        // Ayni adli hizmet birden fazla kayitliysa tekille (en yuksek skorlusu kalir)
        $gorulen = [];
        $tekil = [];
        foreach ($eslesen as $h) {
            $anahtar = $this->fold($h['hizmet_adi']);
            if (isset($gorulen[$anahtar])) {
                continue;
            }
            $gorulen[$anahtar] = true;
            $tekil[] = $h;
        }

        return [$tekil, $metinler];
    }

    /* ------------------------------------------------------------------ */
    /* PERSONEL                                                           */
    /* ------------------------------------------------------------------ */

    protected function personelEslestir($fold, $hizmetler, $musteriIpucu = null)
    {
        // Datif ipucundaki kelimeler MUSTERI adidir; personel eslestirmesinden haric tut
        $haric = $musteriIpucu ? preg_split('/\s+/u', $musteriIpucu) : [];

        $liste = Personeller::where('salon_id', $this->salonId)
            ->where(function ($q) {
                $q->whereNull('arsivli')->orWhere('arsivli', '!=', 1);
            })
            ->get();

        $enIyi = null;
        $enIyiSkor = 0.0;
        $metin = null;
        foreach ($liste as $p) {
            $ad = $p->personel_adi;
            if (!$ad) {
                continue;
            }
            $adFold = $this->fold($ad);
            $ilk = explode(' ', $adFold)[0];
            // Personel adi/ilk kelimesi musteri ipucundaysa personel sayma
            if (in_array($adFold, $haric, true) || in_array($ilk, $haric, true)) {
                continue;
            }
            // Ad ya da adin ilk kelimesi cumlede geciyor mu?
            $gecti = mb_strpos($fold, $adFold) !== false
                || (mb_strlen($ilk) >= 3 && preg_match('/\b' . preg_quote($ilk, '/') . '\b/u', $fold));
            if ($gecti && mb_strlen($adFold) > $enIyiSkor) {
                $enIyi = $p;
                $enIyiSkor = mb_strlen($adFold);
                $metin = $adFold;
            }
        }

        if (!$enIyi) {
            return [null, null]; // belirtilmedi -> onay ekraninda secilir / "fark etmez"
        }

        return [[
            'personel_id'  => $enIyi->id,
            'personel_adi' => $enIyi->personel_adi,
        ], $metin];
    }

    /* ------------------------------------------------------------------ */
    /* MUSTERI                                                            */
    /* ------------------------------------------------------------------ */

    protected function musteriEslestir($ham, $fold, $hizmetMetinleri, $personelMetni, $musteriIpucu = null)
    {
        if ($musteriIpucu) {
            // Datif ipucu (Ferdi Korkmaz'a) -> dogrudan musteri adi; kalan kelime tahminine gerek yok
            $temiz = $musteriIpucu;
            $tokenlar = array_values(array_filter(preg_split('/\s+/u', $musteriIpucu), function ($t) {
                return mb_strlen(trim($t)) >= 2;
            }));
        } else {
            // Cumleden tarih/saat/hizmet/personel ve dolgu kelimeleri cikar -> geriye ad kalir
            $temiz = $fold;
            foreach (array_merge($hizmetMetinleri, [$personelMetni]) as $cikar) {
                if ($cikar) {
                    $temiz = str_replace($cikar, ' ', $temiz);
                }
            }
            // saat/tarih iz birakan rakam ve kaliplari sil
            $temiz = preg_replace('/\b\d{1,2}[:.]\d{2}\b/u', ' ', $temiz);
            $temiz = preg_replace('/\b\d{1,4}[.\/]\d{1,2}([.\/]\d{2,4})?\b/u', ' ', $temiz);
            $temiz = preg_replace('/\b\d+\b/u', ' ', $temiz);
            $temiz = preg_replace('/[\'’]\S*/u', ' ', $temiz);

            $tokenlar = array_filter(preg_split('/\s+/u', $temiz), function ($t) {
                $t = trim($t);
                return mb_strlen($t) >= 2 && !in_array($t, $this->stopKelimeler, true)
                    && !array_key_exists($t, $this->gunler)
                    && !array_key_exists($t, $this->aylar);
            });
            $tokenlar = array_values($tokenlar);
        }
        $this->dbg = ['temiz' => $temiz, 'tokenlar' => $tokenlar]; // GECICI teshis

        if (empty($tokenlar)) {
            return [
                'ad_tahmini' => null,
                'user_id'    => null,
                'adaylar'    => [],
            ];
        }

        // Portfoyde bulanik arama
        $sorgu = DB::table('musteri_portfoy as mp')
            ->join('users as u', 'mp.user_id', '=', 'u.id')
            ->where('mp.salon_id', $this->salonId)
            ->where('mp.aktif', 1)
            ->where(function ($q) use ($tokenlar) {
                foreach ($tokenlar as $t) {
                    $q->orWhere('u.name', 'like', '%' . $t . '%');
                }
            })
            ->select('u.id', 'u.name', 'u.cep_telefon')
            ->limit(25)
            ->get();

        $adTahmini = implode(' ', $tokenlar);
        $adaylar = [];
        foreach ($sorgu as $u) {
            $nameFold = $this->fold($u->name);
            $skor = $this->benzerlik($nameFold, $this->fold($adTahmini));
            // Bir token, adin icinde TAM KELIME olarak geciyorsa skoru yukselt
            foreach ($tokenlar as $t) {
                if (mb_strlen($t) >= 3 && preg_match('/\b' . preg_quote($t, '/') . '\b/u', $nameFold)) {
                    $skor = max($skor, 0.85);
                }
            }
            if ($skor < 0.5) {
                continue; // zayif eslesme = gurultu, aday sayma
            }
            $adaylar[] = [
                'user_id'       => $u->id,
                'name'          => $u->name,
                'telefon_maske' => $this->telMaskele($u->cep_telefon),
                'skor'          => round($skor, 2),
            ];
        }
        usort($adaylar, function ($a, $b) {
            return $b['skor'] <=> $a['skor'];
        });
        $adaylar = array_slice($adaylar, 0, 5);

        // Yeterince net aday varsa otomatik sec (aksi halde onay ekraninda secilir)
        $userId = null;
        $en     = isset($adaylar[0]) ? $adaylar[0] : null;
        $ikinci = isset($adaylar[1]) ? $adaylar[1] : null;
        if ($en) {
            if (!$ikinci && $en['skor'] >= 0.6) {
                $userId = $en['user_id'];                                   // tek aday
            } elseif ($en['skor'] >= 0.95 && (!$ikinci || $en['skor'] - $ikinci['skor'] >= 0.1)) {
                $userId = $en['user_id'];                                   // net/tam eslesme
            } elseif ($en['skor'] >= 0.85 && $ikinci && $en['skor'] - $ikinci['skor'] >= 0.25) {
                $userId = $en['user_id'];                                   // digerlerinden acik ara onde
            }
        }

        return [
            'ad_tahmini' => $adTahmini,
            'user_id'    => $userId,
            'adaylar'    => $adaylar,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* DEGERLENDIRME                                                      */
    /* ------------------------------------------------------------------ */

    protected function eksikAlanlar($musteri, $hizmetler, $tarih, $saat)
    {
        $eksik = [];
        if (empty($musteri['user_id']))  $eksik[] = 'musteri';
        if (empty($hizmetler))           $eksik[] = 'hizmet';
        if (empty($tarih))               $eksik[] = 'tarih';
        if (empty($saat))                $eksik[] = 'saat';
        return $eksik;
    }

    protected function guvenHesapla($intent, $musteri, $hizmetler, $tarih, $saat)
    {
        if ($intent !== 'randevu_olustur') {
            return 'dusuk';
        }
        $puan = 0;
        if (!empty($musteri['user_id'])) $puan++;
        elseif (!empty($musteri['adaylar'])) $puan += 0.5;
        if (!empty($hizmetler)) $puan++;
        if (!empty($tarih))     $puan++;
        if (!empty($saat))      $puan++;

        if ($puan >= 3.5) return 'yuksek';
        if ($puan >= 2)   return 'orta';
        return 'dusuk';
    }

    /* ------------------------------------------------------------------ */
    /* YARDIMCILAR                                                        */
    /* ------------------------------------------------------------------ */

    /**
     * Karsilastirma icin sadelestir: UTF-8'e cevir + Turkce harfleri (buyuk/kucuk)
     * ASCII'ye indir + kucuk harf + ASCII disi temizle.
     * Turkce eslemesi \u{} kacislariyla yapilir -> kaynak dosya encoding'inden bagimsiz.
     */
    protected function fold($s)
    {
        $s = (string) $s;
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = mb_convert_encoding($s, 'UTF-8', 'Windows-1254');
        }
        $s = strtr($s, [
            "\u{00C7}" => 'c', "\u{00E7}" => 'c', // Ç ç
            "\u{011E}" => 'g', "\u{011F}" => 'g', // Ğ ğ
            "\u{0130}" => 'i', "\u{0131}" => 'i', // İ ı
            "\u{00D6}" => 'o', "\u{00F6}" => 'o', // Ö ö
            "\u{015E}" => 's', "\u{015F}" => 's', // Ş ş
            "\u{00DC}" => 'u', "\u{00FC}" => 'u', // Ü ü
        ]);
        $s = mb_strtolower($s, 'UTF-8');                              // kalan ASCII buyuk harfler
        $s = preg_replace('/[^a-z0-9:.\/\x{2019}\x{27}\s]/u', ' ', $s); // artik sadece ASCII bekliyoruz
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    /** 0..1 arasi benzerlik (similar_text yuzdesi) */
    protected function benzerlik($a, $b)
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }
        similar_text($a, $b, $yuzde);
        return $yuzde / 100;
    }

    protected function telMaskele($tel)
    {
        $tel = preg_replace('/\D/', '', (string) $tel);
        if (mb_strlen($tel) < 4) {
            return null;
        }
        return str_repeat('*', mb_strlen($tel) - 4) . mb_substr($tel, -4);
    }

    protected function guvenliTarih($yil, $ay, $gun)
    {
        $yil = (int) $yil;
        $ay  = (int) $ay;
        $gun = (int) $gun;
        if (!checkdate($ay, $gun, $yil)) {
            return null;
        }
        return Carbon::createFromDate($yil, $ay, $gun)->toDateString();
    }

    protected function yilNormalize($y)
    {
        $y = (int) $y;
        return $y < 100 ? 2000 + $y : $y;
    }
}
