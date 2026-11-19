<?php

namespace App\Services;

use App\Services\Gorsel\AnthropicGorselSaglayici;
use App\Services\Gorsel\GorselSaglayici;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * AI Gorsel Danisman: musterinin WhatsApp'tan gonderdigi model fotografi -> hizmet + FIYAT -> randevu.
 *
 * Ilke: AI fiyat SOYLEMEZ/HESAPLAMAZ. AI yalniz fotograftaki ozellikleri (sektor semasina gore) cikarir; fiyati
 * isletmenin kurallari (wa_gorsel_kurallar: ana hizmet -> hizmet fiyati/sabit fiyat, ekler -> sabit / adet basi)
 * KODLA hesaplar. Ayni girdi her zaman ayni fiyat. Kural yoksa / fotograf belirsizse fiyat uydurulmaz
 * (soru sorulur ya da isletmeye aktarilir).
 *
 * Sektorden bagimsiz: sektorler() semasi (tirnak, sac ...) yeni sektor eklenerek genisler.
 * Ayni fotograf (sha256) ayni isletmede ikinci kez analiz edilmez (bedava, paketten dusmez).
 * Fotograflar storage/app/wa_gorsel altinda 30 gun tutulur (wa:geri-kazanim komutu siler); model egitiminde kullanilmaz.
 */
class WaGorselDanisman
{
    const SEMA = 'wa_gorsel_sema_v1';
    const SAKLAMA_GUN = 30;

    // ================================================================== sektor semalari
    /**
     * ana: fiyatin dayandigi ana hizmet secenekleri. ozellikler: tip secim (secenekli, secenek basina ek fiyat),
     * bool (varsa ek fiyat), adet (adet x fiyat), bilgi (fiyata etki etmez, aciklamada gecer).
     */
    public static function sektorler(): array
    {
        return [
            'tirnak' => [
                'ad' => 'Tırnak / Nail Art',
                'nesne' => 'tırnak',
                'ana' => ['protez' => 'Protez tırnak', 'kalici_oje' => 'Kalıcı oje', 'jel_guclendirme' => 'Jel güçlendirme', 'klasik_oje' => 'Klasik oje / manikür'],
                'ozellikler' => [
                    'uzunluk' => ['tip' => 'secim', 'ad' => 'Uzunluk', 'secenek' => ['kisa' => 'Kısa', 'orta' => 'Orta', 'uzun' => 'Uzun', 'cok_uzun' => 'Çok uzun']],
                    'sekil' => ['tip' => 'bilgi', 'ad' => 'Şekil', 'secenek' => ['kare' => 'Kare', 'oval' => 'Oval', 'badem' => 'Badem', 'balerin' => 'Balerin', 'stiletto' => 'Stiletto', 'yuvarlak' => 'Yuvarlak']],
                    'french' => ['tip' => 'bool', 'ad' => 'French'],
                    'ombre' => ['tip' => 'bool', 'ad' => 'Ombre / babyboomer'],
                    'krom' => ['tip' => 'bool', 'ad' => 'Krom / ayna efekti'],
                    'kedi_gozu' => ['tip' => 'bool', 'ad' => 'Kedi gözü'],
                    'simli' => ['tip' => 'bool', 'ad' => 'Simli / glitter'],
                    'nail_art' => ['tip' => 'adet', 'ad' => 'Nail art (desenli parmak)', 'birim' => 'parmak'],
                    'tas' => ['tip' => 'adet', 'ad' => 'Taş süsleme (taşlı parmak)', 'birim' => 'parmak'],
                    'susleme_3d' => ['tip' => 'adet', 'ad' => '3D süsleme (parmak)', 'birim' => 'parmak'],
                ],
            ],
            'sac' => [
                'ad' => 'Saç (renk / kesim)',
                'nesne' => 'saç',
                'ana' => ['komple_boya' => 'Komple boya', 'dip_boya' => 'Dip boya', 'balyaj' => 'Balyaj', 'ombre' => 'Ombre', 'rofle' => 'Röfle', 'kesim' => 'Kesim', 'fon' => 'Fön / şekillendirme'],
                'ozellikler' => [
                    'uzunluk' => ['tip' => 'secim', 'ad' => 'Saç uzunluğu', 'secenek' => ['kisa' => 'Kısa', 'orta' => 'Orta', 'uzun' => 'Uzun', 'cok_uzun' => 'Çok uzun']],
                    'acma' => ['tip' => 'bool', 'ad' => 'Açma işlemi gerekir'],
                    'bakim' => ['tip' => 'bool', 'ad' => 'Bakım / keratin'],
                ],
            ],
        ];
    }

    public static function sektor(string $s): array
    {
        $l = self::sektorler();
        return $l[$s] ?? $l['tirnak'];
    }

    /** Claude'dan beklenen JSON semasi (structured outputs uyumlu: tum alanlar required, null yerine "belirsiz"/0/""). */
    public static function jsonSemasi(string $sektor): array
    {
        $s = self::sektor($sektor);
        $oz = []; $req = [];
        foreach ($s['ozellikler'] as $k => $o) {
            if ($o['tip'] === 'secim' || $o['tip'] === 'bilgi') $oz[$k] = ['type' => 'string', 'enum' => array_merge(array_keys($o['secenek']), ['belirsiz'])];
            elseif ($o['tip'] === 'bool') $oz[$k] = ['type' => 'boolean'];
            else $oz[$k] = ['type' => 'integer'];
            $req[] = $k;
        }
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['uygun', 'ana', 'ozellikler', 'aciklama', 'guven', 'soru'],
            'properties' => [
                'uygun' => ['type' => 'boolean'],
                'ana' => ['type' => 'string', 'enum' => array_merge(array_keys($s['ana']), ['belirsiz'])],
                'ozellikler' => ['type' => 'object', 'additionalProperties' => false, 'required' => $req, 'properties' => $oz],
                'aciklama' => ['type' => 'string'],
                'guven' => ['type' => 'number'],
                'soru' => ['type' => 'string'],
            ],
        ];
    }

    public static function talimat(string $sektor): string
    {
        $s = self::sektor($sektor);
        $ana = implode(', ', array_map(function ($k, $v) { return "{$k} ({$v})"; }, array_keys($s['ana']), $s['ana']));
        $oz = [];
        foreach ($s['ozellikler'] as $k => $o) {
            if (isset($o['secenek'])) $oz[] = "- {$k}: {$o['ad']} → " . implode(' | ', array_keys($o['secenek'])) . ' | belirsiz';
            elseif ($o['tip'] === 'bool') $oz[] = "- {$k}: {$o['ad']} → true/false";
            else $oz[] = "- {$k}: {$o['ad']} → kaç {$o['birim']} (yoksa 0)";
        }
        $oz = implode("\n", $oz);
        return <<<T
Bir güzellik salonunun müşteri asistanı için {$s['nesne']} modeli fotoğrafını analiz ediyorsun. Müşteri bu modeli yaptırmak istiyor.
GÖREV: Fotoğrafta GÖRDÜĞÜN özellikleri şemaya göre çıkar. FİYAT SÖYLEME, fiyat tahmini yapma.
- uygun: fotoğrafta yaptırılabilecek bir {$s['nesne']} modeli/uygulaması var mı (alakasız fotoğraf, belge, ekran görüntüsünde model yoksa false).
- ana: ana işlem → {$ana}. Emin değilsen "belirsiz".
- ozellikler:
{$oz}
- Görmediğin bir uygulamayı VAR sayma; seçimde emin değilsen "belirsiz", sayıda emin değilsen görebildiğin kadarını say. Sadece görünen elin/parmakların sayısını değil, modelin tamamında (iki el, 10 parmak) uygulanacak sayıyı tahmin etme: yalnız fotoğrafta görüneni say.
- aciklama: modelin müşteriye yazılacak KISA Türkçe tarifi (en fazla 1 cümle, ör. "Uzun badem protez tırnak, french ve iki parmakta taş süsleme."). Fiyat yazma.
- guven: 0 ile 1 arası; fotoğraf net ve model açıkça görünüyorsa yüksek.
- soru: fiyatı belirlemek için müşteriye sorulması gereken TEK kısa soru (ör. "Protez tırnak mı, kalıcı oje mi düşünüyorsunuz?"); gerek yoksa boş metin.
Müşterinin yazdığı mesaj varsa dikkate al (ör. "kısa olsun", "doğal tırnağıma"); fotoğraftan çok müşterinin açık isteği geçerlidir.
T;
    }

    /** Model ciktisini dogrula/normallestir (bilinmeyen anahtar atilir, tipler duzeltilir). */
    public static function analizDogrula(?array $j, string $sektor): ?array
    {
        if (!$j) return null;
        $s = self::sektor($sektor);
        $ana = (string) ($j['ana'] ?? 'belirsiz');
        $out = [
            'uygun' => !empty($j['uygun']),
            'ana' => isset($s['ana'][$ana]) ? $ana : null,
            'ozellikler' => [],
            'aciklama' => mb_substr(trim((string) ($j['aciklama'] ?? '')), 0, 300),
            'guven' => max(0.0, min(1.0, (float) ($j['guven'] ?? 0))),
            'soru' => mb_substr(trim((string) ($j['soru'] ?? '')), 0, 200),
        ];
        $oz = (array) ($j['ozellikler'] ?? []);
        foreach ($s['ozellikler'] as $k => $o) {
            $v = $oz[$k] ?? null;
            if ($o['tip'] === 'secim' || $o['tip'] === 'bilgi') $out['ozellikler'][$k] = (is_string($v) && isset($o['secenek'][$v])) ? $v : null;
            elseif ($o['tip'] === 'bool') $out['ozellikler'][$k] = (bool) $v;
            else $out['ozellikler'][$k] = max(0, min(20, (int) $v));
        }
        return $out;
    }

    /**
     * FIYAT (saf, deterministik). $kurallar: [anahtar => ['hizmet_id'=>?, 'fiyat'=>?]] anahtar: "ana:protez",
     * "uzunluk:uzun", "french", "tas" ... ; $hizmetler: [['id','ad','fiyat']].
     * @return array ['durum' => fiyat|kural_yok|belirsiz|uygun_degil, 'toplam', 'kalemler' => [[etiket, tutar]], 'hizmet' => ?['id','ad']]
     */
    public static function fiyatHesapla(array $analiz, array $kurallar, array $hizmetler, string $sektor): array
    {
        $s = self::sektor($sektor);
        if (empty($analiz['uygun'])) return ['durum' => 'uygun_degil', 'toplam' => null, 'kalemler' => [], 'hizmet' => null];
        if (empty($analiz['ana'])) return ['durum' => 'belirsiz', 'toplam' => null, 'kalemler' => [], 'hizmet' => null];
        $hFiyat = [];
        foreach ($hizmetler as $h) $hFiyat[(int) ($h['id'] ?? 0)] = ['ad' => (string) ($h['ad'] ?? ''), 'fiyat' => (float) ($h['fiyat'] ?? 0)];
        $tutar = function ($k) use ($kurallar, $hFiyat) {
            if (!isset($kurallar[$k])) return null;
            $r = $kurallar[$k];
            if (!empty($r['hizmet_id']) && isset($hFiyat[(int) $r['hizmet_id']]) && $hFiyat[(int) $r['hizmet_id']]['fiyat'] > 0) return $hFiyat[(int) $r['hizmet_id']]['fiyat'];
            return (isset($r['fiyat']) && $r['fiyat'] !== null && $r['fiyat'] !== '') ? (float) $r['fiyat'] : null;
        };
        $anaK = 'ana:' . $analiz['ana'];
        $anaT = $tutar($anaK);
        if ($anaT === null) return ['durum' => 'kural_yok', 'toplam' => null, 'kalemler' => [], 'hizmet' => null];
        $hizmet = null;
        if (!empty($kurallar[$anaK]['hizmet_id']) && isset($hFiyat[(int) $kurallar[$anaK]['hizmet_id']])) {
            $hizmet = ['id' => (int) $kurallar[$anaK]['hizmet_id'], 'ad' => $hFiyat[(int) $kurallar[$anaK]['hizmet_id']]['ad']];
        }
        $kalem = [[$s['ana'][$analiz['ana']], $anaT]];
        $toplam = $anaT;
        $eksik = []; // fotografta var ama ek ucreti tanimli degil (fiyat "-den baslar", isletmeye sorulur)
        foreach ($s['ozellikler'] as $k => $o) {
            $v = $analiz['ozellikler'][$k] ?? null;
            if ($o['tip'] === 'secim' && $v) {
                $t = $tutar("{$k}:{$v}");
                if ($t) { $kalem[] = [$o['ad'] . ' (' . $o['secenek'][$v] . ')', $t]; $toplam += $t; }
            } elseif ($o['tip'] === 'bool' && $v) {
                $t = $tutar($k);
                if ($t) { $kalem[] = [$o['ad'], $t]; $toplam += $t; } else $eksik[] = $o['ad'];
            } elseif ($o['tip'] === 'adet' && $v > 0) {
                $t = $tutar($k);
                if ($t) { $kalem[] = [$o['ad'] . " × {$v}", $t * $v]; $toplam += $t * $v; } else $eksik[] = preg_replace('/\s*\([^)]*\)/u', '', $o['ad']);
            }
        }
        return ['durum' => 'fiyat', 'toplam' => round($toplam, 2), 'kalemler' => $kalem, 'hizmet' => $hizmet, 'eksik' => $eksik];
    }

    /** ["French","Krom"] -> "French ve krom" */
    public static function listeMetni(array $l): string
    {
        $l = array_values($l);
        foreach ($l as $i => $x) if ($i > 0) $l[$i] = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $x), 'UTF-8');
        if (count($l) <= 1) return (string) ($l[0] ?? '');
        $son = array_pop($l);
        return implode(', ', $l) . ' ve ' . $son;
    }

    /** Ana islem -> hizmet listesinde aranacak kelimeler / dislanacaklar (otomatik eslestirme). */
    const OTO_ESLES = [
        'tirnak' => [
            'protez' => [['protez'], ['dolum', 'bakım', 'bakim', 'söküm', 'sokum', 'çıkar', 'cikar', 'tamir', 'onarım', 'onarim', 'ayak', 'pedikür', 'pedikur', 'tek tırnak', 'tek tirnak']],
            'kalici_oje' => [['kalıcı oje', 'kalici oje', 'kalıcı', 'kalici', 'gel polish', 'semi'], ['ayak', 'pedikür', 'pedikur', 'söküm', 'sokum', 'çıkar', 'cikar']],
            'jel_guclendirme' => [['jel güçlendirme', 'jel guclendirme', 'güçlendirme', 'guclendirme', 'jel'], ['söküm', 'sokum', 'ayak', 'pedikür', 'pedikur']],
            'klasik_oje' => [['manikür', 'manikur', 'klasik oje', 'oje'], ['kalıcı', 'kalici', 'pedikür', 'pedikur', 'ayak', 'protez', 'jel']],
        ],
        'sac' => [
            'komple_boya' => [['komple boya', 'saç boya', 'sac boya', 'boya'], ['dip']],
            'dip_boya' => [['dip boya'], []],
            'balyaj' => [['balyaj'], []],
            'ombre' => [['ombre'], []],
            'rofle' => [['röfle', 'rofle'], []],
            'kesim' => [['kesim'], ['erkek', 'çocuk', 'cocuk', 'sakal']],
            'fon' => [['fön', 'fon'], []],
        ],
    ];

    /**
     * Isletme kural girmediyse ana islem hizmet listesinden OTOMATIK eslesir (fiyati sistemde olan hizmet):
     * kelimeyi iceren, dislanan kelimeyi icermeyen, fiyati > 0 olan; birden coksa en kisa adli (en genel) hizmet.
     * @return array ['ana:kalici_oje' => ['hizmet_id' => 12, 'oto' => 1], ...]
     */
    public static function otomatikKurallar(array $hizmetler, string $sektor): array
    {
        $kucuk = function ($s) { return mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], (string) $s), 'UTF-8'); };
        $out = [];
        foreach (self::OTO_ESLES[$sektor] ?? [] as $ana => $kural) {
            [$ara, $dis] = $kural;
            $aday = null;
            foreach ($hizmetler as $h) {
                $ad = $kucuk($h['ad'] ?? '');
                if ((float) ($h['fiyat'] ?? 0) <= 0 || $ad === '') continue;
                $var = false; foreach ($ara as $k) if (mb_strpos($ad, $k) !== false) { $var = true; break; }
                if (!$var) continue;
                foreach ($dis as $k) if (mb_strpos($ad, $k) !== false) continue 2;
                if (!$aday || mb_strlen($ad) < mb_strlen($kucuk($aday['ad']))) $aday = $h;
            }
            if ($aday) $out['ana:' . $ana] = ['hizmet_id' => (int) $aday['id'], 'oto' => 1];
        }
        return $out;
    }

    /** Kayitli kurallar + eksik ana islemler icin otomatik eslesme (kayitli kural her zaman onceliklidir). */
    public static function etkinKurallar(int $isletmeId, string $sektor, array $hizmetler): array
    {
        $k = self::kurallar($isletmeId, $sektor);
        foreach (self::otomatikKurallar($hizmetler, $sektor) as $a => $v) if (!isset($k[$a])) $k[$a] = $v;
        return $k;
    }

    /** Musteriye gidecek cevap (saf). */
    public static function cevapMetni(array $analiz, array $fiyat, ?string $not): string
    {
        $tarif = trim((string) ($analiz['aciklama'] ?? ''));
        switch ($fiyat['durum']) {
            case 'uygun_degil':
                return 'Gönderdiğiniz fotoğrafta yaptırmak istediğiniz modeli göremedim. Modelin fotoğrafını tekrar gönderebilir ya da ne istediğinizi yazabilir misiniz? 😊';
            case 'belirsiz':
                return ($tarif !== '' ? "Modelinizi inceledim: {$tarif}\n\n" : '')
                    . ($analiz['soru'] ?: 'Fiyatı doğru hesaplayabilmem için hangi uygulamayı düşündüğünüzü yazar mısınız?');
            case 'kural_yok':
                // Fiyat bilinmiyor: isletmeye sorulur (WaTalep) ama musteri bekletilmez, randevuya devam edilir
                return 'Modelinizi inceledim 😊' . ($tarif !== '' ? "\n{$tarif}" : '')
                    . "\n\nBu modelin fiyatını işletmemize sordum, kısa sürede buradan bildirecekler. Bu arada randevunuzu ayarlayalım mı? Hangi gün ve saat size uygun?";
        }
        $eksik = (array) ($fiyat['eksik'] ?? []);
        $belirsizlik = ($analiz['guven'] ?? 1) < 0.75 ? 'Fotoğraftan anladığım kadarıyla' : 'Modelinizi inceledim';
        $out = "{$belirsizlik} 😊" . ($tarif !== '' ? "\n{$tarif}" : '');
        $out .= "\n\nBu model için fiyatımız: " . WaBedavaKatman::tl($fiyat['toplam']) . ($eksik ? "'den başlıyor" : '');
        if (count($fiyat['kalemler']) > 1) {
            $out .= "\n(" . implode(' + ', array_map(function ($k) { return $k[0] . ' ' . WaBedavaKatman::tl($k[1]); }, $fiyat['kalemler'])) . ')';
        }
        if ($eksik) $out .= "\n" . self::listeMetni($eksik) . ' için ek ücret olabilir; işletmemize sordum, netleşince buradan bildireceğiz.';
        if (trim((string) $not) !== '') $out .= "\n" . trim($not);
        if (!empty($analiz['soru']) && ($analiz['guven'] ?? 1) < 0.75) $out .= "\n\n" . $analiz['soru'];
        return $out . "\n\nSize uygun bir gün için randevu oluşturalım mı?";
    }

    /** Asistan gecmisine gizli baglam (model randevuya bu hizmetle devam etsin). */
    public static function baglamNotu(array $analiz, array $fiyat): string
    {
        $b = 'görsel analiz: ' . ($analiz['aciklama'] ?? '');
        if (!empty($fiyat['hizmet']['id'])) $b .= '; hizmet_id=' . $fiyat['hizmet']['id'] . ' (' . $fiyat['hizmet']['ad'] . ')';
        if ($fiyat['durum'] === 'fiyat') $b .= '; söylenen fiyat ' . WaBedavaKatman::tl($fiyat['toplam']);
        return $b;
    }

    /** Dosya turunu ICERIKTEN dogrula (uzantiya guvenme). */
    public static function mimeTespit(string $bytes): ?string
    {
        if (strncmp($bytes, "\xFF\xD8\xFF", 3) === 0) return 'image/jpeg';
        if (strncmp($bytes, "\x89PNG\r\n\x1A\n", 8) === 0) return 'image/png';
        if (strncmp($bytes, 'RIFF', 4) === 0 && substr($bytes, 8, 4) === 'WEBP') return 'image/webp';
        return null;
    }

    // ================================================================== DB / akis
    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        try {
            if (!Schema::hasTable('wa_gorsel_analizler')) {
                Schema::create('wa_gorsel_analizler', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->unsignedBigInteger('isletme_id')->nullable();
                    $t->unsignedBigInteger('oturum_id')->nullable();
                    $t->string('anahtar', 100)->nullable();
                    $t->char('hash', 64);
                    $t->string('dosya', 255)->nullable();
                    $t->string('mime', 30)->nullable();
                    $t->string('sektor', 30)->nullable();
                    $t->text('analiz')->nullable();          // normallestirilmis JSON
                    $t->text('fiyat')->nullable();           // hesap JSON
                    $t->decimal('toplam', 12, 2)->nullable();
                    $t->string('durum', 20)->default('bekliyor'); // bekliyor | fiyat | belirsiz | kural_yok | uygun_degil | hata
                    $t->tinyInteger('onbellek')->default(0);     // ayni fotograf onceki analizden (bedava)
                    $t->decimal('maliyet_usd', 12, 6)->default(0);
                    $t->unsignedBigInteger('randevu_id')->nullable();
                    $t->timestamps();
                    $t->index(['salon_id', 'hash'], 'wa_gorsel_salon_hash_idx');
                    $t->index(['isletme_id', 'created_at'], 'wa_gorsel_isletme_tarih_idx');
                });
            }
            if (!Schema::hasTable('wa_gorsel_kurallar')) {
                Schema::create('wa_gorsel_kurallar', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('sektor', 30);
                    $t->string('anahtar', 60);               // ana:protez | uzunluk:uzun | french | tas ...
                    $t->unsignedBigInteger('hizmet_id')->nullable();
                    $t->decimal('fiyat', 10, 2)->nullable();
                    $t->timestamps();
                    $t->unique(['salon_id', 'sektor', 'anahtar'], 'wa_gorsel_kural_uq');
                });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-GORSEL] sema: ' . $e->getMessage());
            return false;
        }
    }

    public static function saglayici(): GorselSaglayici
    {
        $c = config('services.wa_asistan.gorsel_saglayici');
        if ($c && class_exists($c) && is_subclass_of($c, GorselSaglayici::class)) return app($c);
        return new AnthropicGorselSaglayici();
    }

    public static function kurallar(int $salonId, string $sektor): array
    {
        if (!self::semaGaranti()) return [];
        $out = [];
        foreach (DB::table('wa_gorsel_kurallar')->where('salon_id', $salonId)->where('sektor', $sektor)->get() as $r) {
            $out[$r->anahtar] = ['hizmet_id' => $r->hizmet_id ? (int) $r->hizmet_id : null, 'fiyat' => $r->fiyat !== null ? (float) $r->fiyat : null];
        }
        return $out;
    }

    public static function kurallariKaydet(int $salonId, string $sektor, array $kurallar): void
    {
        self::semaGaranti();
        DB::table('wa_gorsel_kurallar')->where('salon_id', $salonId)->where('sektor', $sektor)->delete();
        foreach ($kurallar as $anahtar => $r) {
            $anahtar = (string) $anahtar;
            if (!self::kuralAnahtariGecerli($anahtar, $sektor)) continue;
            $hid = !empty($r['hizmet_id']) ? (int) $r['hizmet_id'] : null;
            $f = (isset($r['fiyat']) && $r['fiyat'] !== '' && $r['fiyat'] !== null) ? max(0, (float) str_replace(',', '.', (string) $r['fiyat'])) : null;
            if (!$hid && $f === null) continue;
            DB::table('wa_gorsel_kurallar')->insert(['salon_id' => $salonId, 'sektor' => $sektor, 'anahtar' => $anahtar,
                'hizmet_id' => $hid, 'fiyat' => $f, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public static function kuralAnahtariGecerli(string $a, string $sektor): bool
    {
        $s = self::sektor($sektor);
        if (strpos($a, 'ana:') === 0) return isset($s['ana'][substr($a, 4)]);
        if (strpos($a, ':') !== false) {
            [$k, $v] = explode(':', $a, 2);
            return isset($s['ozellikler'][$k]) && $s['ozellikler'][$k]['tip'] === 'secim' && isset($s['ozellikler'][$k]['secenek'][$v]);
        }
        return isset($s['ozellikler'][$a]) && in_array($s['ozellikler'][$a]['tip'], ['bool', 'adet'], true);
    }

    /**
     * Webhook: gelen fotografi kaydet (dogrula + sakla). Bayt yoksa/gecersizse null.
     * @return int|null wa_gorsel_analizler.id
     */
    public static function fotografKaydet(int $salonId, string $anahtar, string $b64): ?int
    {
        if (!self::semaGaranti()) return null;
        $bytes = base64_decode($b64, true);
        if ($bytes === false || $bytes === '') return null;
        $maxMb = (float) config('services.wa_asistan.gorsel_max_mb', 5);
        if (strlen($bytes) > $maxMb * 1024 * 1024) { Log::info('[WA-GORSEL] fotograf cok buyuk', ['salon' => $salonId, 'bayt' => strlen($bytes)]); return null; }
        $mime = self::mimeTespit($bytes);
        if (!$mime) { Log::info('[WA-GORSEL] desteklenmeyen dosya', ['salon' => $salonId]); return null; }
        $hash = hash('sha256', $bytes);
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $yol = "wa_gorsel/{$salonId}/{$hash}.{$ext}";
        try {
            $tam = storage_path('app/' . $yol);
            if (!is_dir(dirname($tam))) @mkdir(dirname($tam), 0750, true);
            if (!is_file($tam)) file_put_contents($tam, $bytes);
        } catch (\Throwable $e) {
            Log::warning('[WA-GORSEL] dosya yazilamadi: ' . $e->getMessage());
            $yol = null;
        }
        return (int) DB::table('wa_gorsel_analizler')->insertGetId([
            'salon_id' => $salonId, 'anahtar' => mb_substr($anahtar, 0, 100), 'hash' => $hash, 'dosya' => $yol, 'mime' => $mime,
            'durum' => 'bekliyor', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /**
     * Fotografli mesaji cevapla. $metin: musterinin fotografla yazdigi (caption) metin.
     * @return array ['cevap','ai'(bool),'maliyet_usd','aksiyon'(null|aktar),'hizmet'=>?['id','ad'],'baglam','durum']
     */
    public static function isle(int $gorselId, int $isletmeId, ?int $oturumId, array $hizmetler, string $metin = ''): array
    {
        $ayar = WaAsistanVeri::ayarlar($isletmeId);
        $sektor = (string) ($ayar['gorsel_sektor'] ?: 'tirnak');
        $g = DB::table('wa_gorsel_analizler')->where('id', $gorselId)->first();
        $bos = ['cevap' => 'Fotoğrafınızı şu an inceleyemedim. Yaptırmak istediğiniz modeli kısaca yazar mısınız? (ör. uzun protez tırnak, french)',
            'ai' => false, 'maliyet_usd' => 0.0, 'aksiyon' => null, 'hizmet' => null, 'baglam' => '', 'durum' => 'hata'];
        if (!$g) return $bos;
        if (empty($ayar['gorsel_fiyat'])) {
            // Isletme fotograftan fiyat vermeyi kapatti: fiyat isletmeye sorulur, musteri bekletilmez
            DB::table('wa_gorsel_analizler')->where('id', $gorselId)->update(['durum' => 'kural_yok', 'isletme_id' => $isletmeId, 'oturum_id' => $oturumId, 'updated_at' => now()]);
            return ['cevap' => self::cevapMetni([], ['durum' => 'kural_yok'], null), 'aksiyon' => null, 'durum' => 'kural_yok', 'talep' => 'fotograf'] + $bos;
        }

        // Ayni fotograf bu isletmede daha once analiz edildiyse: BEDAVA
        $onceki = DB::table('wa_gorsel_analizler')->where('salon_id', $g->salon_id)->where('hash', $g->hash)->where('id', '<', $g->id)
            ->where('sektor', $sektor)->whereNotNull('analiz')->orderByDesc('id')->first();
        $ai = false; $usd = 0.0;
        if ($onceki) {
            $analiz = json_decode($onceki->analiz, true);
        } else {
            $b64 = null;
            if ($g->dosya && is_file(storage_path('app/' . $g->dosya))) $b64 = base64_encode(file_get_contents(storage_path('app/' . $g->dosya)));
            if (!$b64) return $bos;
            $r = self::saglayici()->analiz($b64, (string) $g->mime, self::talimat($sektor), self::jsonSemasi($sektor), $metin);
            $ai = ($r['kullanim']['girdi'] ?? 0) > 0;
            $usd = (float) ($r['maliyet_usd'] ?? 0);
            if ($ai) {
                AiKullanimLog::yaz($isletmeId, 'wa_gorsel', (int) $r['kullanim']['girdi'], (int) $r['kullanim']['cikti'],
                    (string) (config('services.wa_asistan.gorsel_model') ?: 'claude-sonnet-5-5'), false, true, $usd);
            }
            $analiz = self::analizDogrula($r['json'] ?? null, $sektor);
            if (!$analiz) {
                DB::table('wa_gorsel_analizler')->where('id', $gorselId)->update(['durum' => 'hata', 'sektor' => $sektor, 'isletme_id' => $isletmeId,
                    'oturum_id' => $oturumId, 'maliyet_usd' => $usd, 'updated_at' => now()]);
                Log::warning('[WA-GORSEL] analiz alinamadi', ['gorsel' => $gorselId, 'hata' => $r['hata'] ?? null]);
                return ['ai' => $ai, 'maliyet_usd' => $usd] + $bos;
            }
        }
        $fiyat = self::fiyatHesapla($analiz, self::etkinKurallar($isletmeId, $sektor, $hizmetler), $hizmetler, $sektor);
        DB::table('wa_gorsel_analizler')->where('id', $gorselId)->update([
            'isletme_id' => $isletmeId, 'oturum_id' => $oturumId, 'sektor' => $sektor, 'onbellek' => $onceki ? 1 : 0,
            'analiz' => json_encode($analiz, JSON_UNESCAPED_UNICODE), 'fiyat' => json_encode($fiyat, JSON_UNESCAPED_UNICODE),
            'toplam' => $fiyat['toplam'], 'durum' => $fiyat['durum'], 'maliyet_usd' => $usd, 'updated_at' => now(),
        ]);
        return [
            'cevap' => self::cevapMetni($analiz, $fiyat, $ayar['gorsel_not'] ?? null),
            'ai' => $ai, 'maliyet_usd' => $usd,
            'aksiyon' => null,
            // Isletmeye sorulacak konu (WaTalep): fiyat hic yok / ek ucret tanimsiz. Asistan susmaz.
            'talep' => $fiyat['durum'] === 'kural_yok' ? 'fotograf' : (!empty($fiyat['eksik']) ? 'fotograf_ek' : null),
            'hizmet' => $fiyat['hizmet'], 'baglam' => self::baglamNotu($analiz, $fiyat), 'durum' => $fiyat['durum'],
            'tarif' => $analiz['aciklama'] ?? '',
        ];
    }

    /** 30 gunden eski fotograf dosyalarini sil (KVKK); analiz kaydi (fotografsiz) rapor icin kalir. */
    public static function eskiDosyalariSil(): int
    {
        if (!self::semaGaranti()) return 0;
        $n = 0;
        $eski = DB::table('wa_gorsel_analizler')->whereNotNull('dosya')->where('created_at', '<', now()->subDays(self::SAKLAMA_GUN))->limit(2000)->get(['id', 'dosya', 'hash', 'salon_id']);
        foreach ($eski as $r) {
            // Ayni dosya (hash) daha yeni bir kayitta kullaniliyorsa silme
            $yeni = DB::table('wa_gorsel_analizler')->where('salon_id', $r->salon_id)->where('hash', $r->hash)
                ->where('created_at', '>=', now()->subDays(self::SAKLAMA_GUN))->exists();
            if (!$yeni) { @unlink(storage_path('app/' . $r->dosya)); $n++; }
            DB::table('wa_gorsel_analizler')->where('id', $r->id)->update(['dosya' => null]);
        }
        return $n;
    }
}
