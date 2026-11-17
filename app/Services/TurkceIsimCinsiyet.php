<?php

namespace App\Services;

/**
 * Turkce addan cinsiyet (santral hitabi: "Ferdi Bey" / "Ayşe Hanım"). Sorgusuz, aninda.
 * 1) veri/turkce_isim_cinsiyet.php: 7.413 ad, 49,6 milyon kisiden (trnames, MIT); %85 esigi altindaki adlar
 *    IKI CINSIYETLI (Deniz, Emir, Görkem, Seçkin...) -> tahmin edilmez (yanlis hitap musteriyi kirar).
 * 2) Veride olmayan (cogunlukla 1991 sonrasi yeni) adlar icin asagidaki elle listeler.
 * Hicbirinde yoksa null (cagiran kendi musteri istatistigine bakar ya da hitapsiz konusur).
 * Bilesik adlarda ilk ad kullanilir ("Ayşe Nur" -> ayşe, "Ömer Faruk" -> ömer).
 */
class TurkceIsimCinsiyet
{
    const ERKEK = 1;
    const KADIN = 0;
    const IKI_CINSIYET = 2;

    protected static ?array $veri = null;

    /**
     * ISLETME DUZELTMESI (2026-11-17, Ferdi): veride iki cinsiyetli cikan yaygin adlar icin karar. HER SEYIN ONUNDE
     * (sozluk yeniden uretilse de kaybolmaz). Anahtar Turkce karaktersiz. Kamer, İmran, Sunay, Evren, Mutlu
     * veriyle celisse de isletme karariyla ERKEK.
     */
    const DUZELTME = [
        'yuksel' => 1, 'dursun' => 1, 'hikmet' => 1, 'servet' => 1, 'serif' => 1, 'hidayet' => 1, 'gunay' => 1,
        'ilkay' => 1, 'sefa' => 1, 'olcay' => 1, 'gungor' => 1, 'emir' => 1, 'senel' => 1, 'gursel' => 1,
        'omur' => 1, 'rusen' => 1, 'garip' => 1, 'gorkem' => 1, 'armagan' => 1, 'berat' => 1, 'zulfu' => 1,
        'ozay' => 1, 'bilgin' => 1, 'eser' => 1, 'askin' => 1, 'gurcan' => 1, 'bahtiyar' => 1, 'seckin' => 1,
        'safak' => 1, 'saffet' => 1, 'ergul' => 1, 'kamer' => 1, 'imran' => 1, 'sunay' => 1, 'evren' => 1, 'mutlu' => 1,
        'deniz' => 2, 'seyhan' => 0, 'sirin' => 0, 'kudret' => 0, 'durdu' => 0, 'sureyya' => 0, 'guner' => 0,
        'elvan' => 0, 'bircan' => 0, 'ozden' => 0, 'kamuran' => 0, 'sezer' => 0, 'burcin' => 0, 'muhterem' => 0,
        'aycan' => 0, 'ferhan' => 0, 'masallah' => 0, 'ceyhan' => 0, 'ikram' => 0, 'tuna' => 0, 'devrim' => 0,
        'evrim' => 0, 'gokcen' => 0, 'nedret' => 0,
    ];

    /**
     * ELLE LISTELER yalniz buyuk veride OLMAYAN adlarda kullanilir (veri her zaman once gelir; cakismada veri hakli,
     * Ayhan, Yaşar, Ufuk kadinlarda da kullaniliyor). Iki cinsiyette yaygin: asla tahmin etme.
     */
    const BELIRSIZ = [
        'deniz', 'ümit', 'umut', 'özgür', 'evren', 'ege', 'bilge', 'doğa', 'yağmur', 'çağrı', 'aytaç', 'ömür',
        'sezgin', 'kader', 'nurhan', 'özden', 'elvan', 'derya', 'hikmet', 'nimet', 'yücel', 'yüksel', 'gökçe',
        'irmak', 'şafak', 'tuna', 'ilkay', 'cihan', 'sevgin', 'günay', 'ilkin', 'aras', 'aykan', 'işık',
    ];

    const ERKEK_ADLAR = [
        'ahmet', 'mehmet', 'mustafa', 'ali', 'hüseyin', 'hasan', 'ibrahim', 'ismail', 'osman', 'yusuf', 'murat',
        'ömer', 'ramazan', 'halil', 'süleyman', 'abdullah', 'mahmut', 'recep', 'salih', 'fatih', 'kadir', 'emre',
        'hakan', 'adem', 'kemal', 'yaşar', 'bayram', 'burak', 'serkan', 'orhan', 'metin', 'erkan', 'ferdi', 'anıl',
        'onur', 'oğuz', 'oğuzhan', 'tolga', 'tuncay', 'sinan', 'selim', 'serdar', 'cem', 'cengiz', 'levent', 'volkan',
        'gökhan', 'tamer', 'tarık', 'uğur', 'ufuk', 'yunus', 'yasin', 'enes', 'furkan', 'emir', 'efe', 'kerem', 'arda',
        'berk', 'kaan', 'mert', 'alper', 'alperen', 'batuhan', 'doğan', 'engin', 'erdem', 'erdal', 'erhan', 'ersin',
        'ertuğrul', 'fikret', 'gürkan', 'hamza', 'harun', 'ilker', 'ilhan', 'kenan', 'korkut', 'kürşat', 'mesut',
        'muhammed', 'muhammet', 'muharrem', 'necati', 'nihat', 'okan', 'özcan', 'rıza', 'sedat', 'sefa', 'selçuk',
        'semih', 'sercan', 'şahin', 'şükrü', 'taner', 'tayfun', 'turgay', 'turan', 'vedat', 'veli', 'yavuz', 'yiğit',
        'yılmaz', 'zafer', 'zeki', 'ziya', 'abdulkadir', 'abdurrahman', 'alican', 'aykut', 'bekir', 'bülent', 'cemal',
        'cevdet', 'coşkun', 'dursun', 'erol', 'ferhat', 'ferit', 'gökay', 'hayati', 'hilmi', 'ilyas', 'irfan', 'kazım',
        'lütfi', 'muammer', 'nail', 'necdet', 'nevzat', 'nurettin', 'önder', 'polat', 'rafet', 'rasim', 'reşat',
        'sabri', 'sadık', 'seyit', 'sami', 'savaş', 'sertaç', 'şenol', 'şeref', 'talha', 'tahir', 'timur', 'tuğrul',
        'uğurcan', 'ünal', 'yahya', 'yakup', 'yener', 'zeynel', 'berkay', 'berke', 'berkcan', 'emirhan', 'eymen',
        'miraç', 'poyraz', 'çınar', 'atakan', 'egemen', 'kutay', 'onurcan', 'sarp', 'taylan', 'tunahan', 'cenk',
        'ertan', 'arif', 'asım', 'azat', 'baran', 'behçet', 'bilal', 'cahit', 'cumhur', 'davut', 'ekrem', 'enver',
        'eyüp', 'faruk', 'fuat', 'güray', 'haydar', 'hulusi', 'iskender', 'kamil', 'kubilay', 'mithat', 'musa',
        'nazım', 'nuri', 'oktay', 'orkun', 'rüştü', 'sabahattin', 'selahattin', 'seyfi', 'soner', 'şakir', 'tekin',
        'tevfik', 'turgut', 'uygar', 'veysel', 'zekeriya', 'ayhan', 'erdoğan', 'cemil', 'celal', 'ercüment', 'orçun',
        'serhat', 'serhan', 'koray', 'ilkan', 'görkem', 'çağatay', 'bora', 'tuğhan', 'utku', 'umutcan', 'yalçın',
        'yasir', 'mücahit', 'kıvanç', 'kutlu', 'gökmen', 'hüsnü', 'ihsan', 'kasım', 'mazlum', 'nedim', 'necip',
        'raşit', 'remzi', 'saffet', 'sait', 'sefer', 'sezai', 'şaban', 'şevket', 'tuncer', 'zübeyir', 'abdülkadir',
        'erkut', 'erman', 'ertunç', 'gürol', 'kerim', 'mertcan', 'muzaffer', 'oral', 'rahmi', 'seçkin', 'tufan',
        'ercan', 'ozan', 'mevlüt', 'erkin',
    ];

    const KADIN_ADLAR = [
        'ayşe', 'fatma', 'emine', 'hatice', 'zeynep', 'elif', 'meryem', 'şerife', 'zehra', 'sultan', 'hanife',
        'merve', 'esra', 'yasemin', 'büşra', 'hülya', 'gülşah', 'gülşen', 'gül', 'gülay', 'gülcan', 'gülsüm', 'aysel',
        'ayten', 'ayla', 'aynur', 'arzu', 'aslı', 'aslıhan', 'ayşegül', 'ayşenur', 'aylin', 'ayça', 'aybüke', 'başak',
        'belgin', 'berna', 'betül', 'beyza', 'burcu', 'buse', 'cansu', 'ceren', 'ceyda', 'cemile', 'damla', 'dilek',
        'dilara', 'didem', 'duygu', 'ebru', 'eda', 'ece', 'ecem', 'ela', 'elçin', 'emel', 'esin', 'eylül', 'fadime',
        'feride', 'filiz', 'funda', 'gamze', 'gizem', 'gonca', 'gülden', 'gülizar', 'gülten', 'hacer', 'hale',
        'hande', 'havva', 'hilal', 'ilknur', 'ipek', 'irem', 'kübra', 'leyla', 'melek', 'melike', 'meltem', 'melis',
        'mine', 'nalan', 'nazlı', 'nermin', 'neslihan', 'nesrin', 'nihal', 'nil', 'nilay', 'nilgün', 'nuray',
        'nurcan', 'oya', 'öykü', 'özge', 'pelin', 'pınar', 'rabia', 'reyhan', 'rukiye', 'sabriye', 'saadet', 'seda',
        'sedef', 'selin', 'selma', 'serap', 'serpil', 'sevda', 'sevgi', 'sevil', 'sevim', 'sevinç', 'seher', 'sibel',
        'sinem', 'songül', 'su', 'şebnem', 'şeyma', 'şule', 'şükran', 'tuba', 'tuğba', 'tülay', 'tülin', 'ümran',
        'yeliz', 'yeşim', 'yıldız', 'zerrin', 'zübeyde', 'nazan', 'asya', 'azra', 'defne', 'elanur', 'ecrin', 'esila',
        'hiranur', 'miray', 'nehir', 'zümra', 'lale', 'işıl', 'ilayda', 'nisa', 'sena', 'simge', 'tuğçe', 'dilan',
        'bahar', 'çiğdem', 'aysun', 'gülbahar', 'kevser', 'kezban', 'lütfiye', 'makbule', 'medine', 'münevver',
        'naciye', 'necla', 'nefise', 'nezahat', 'nuran', 'perihan', 'saliha', 'semra', 'sevgül', 'şengül', 'şenay',
        'türkan', 'ülkü', 'ümmühan', 'yurdagül', 'zeliha', 'zekiye', 'zuhal', 'canan', 'figen', 'gülnur', 'handan',
        'birsen', 'berrin', 'aydan', 'meral', 'mehtap', 'müge', 'nagehan', 'nevra', 'nergis', 'özlemnur', 'rana',
        'seval', 'sevcan', 'süheyla', 'şirin', 'tijen', 'yonca', 'zeyneb', 'aleyna', 'sude', 'ezgi', 'beren',
        'begüm', 'buket', 'cemre', 'derin', 'esma', 'gülistan', 'hayriye', 'hümeyra', 'jale', 'mukaddes', 'nazife',
        'neriman', 'nurten', 'nurgül', 'safiye', 'sakine', 'semiha', 'sevtap', 'sinem', 'şaziye', 'tülin', 'gülfem',
        'ayfer', 'aysu', 'bengü', 'bengisu', 'berfin', 'cansel', 'dicle', 'ebrar', 'elçin', 'eslem', 'fulya',
        'gülbin', 'güler', 'gülsen', 'havin', 'hazal', 'ilkim', 'kıymet', 'mihriban', 'nurşen', 'senem', 'tansu',
        'özlem', 'nevin', 'çağla', 'mercan', 'eylem',
    ];

    /** 1 Erkek, 0 Kadın, null = iki cinsiyetli ya da hicbir listede yok. */
    public static function bul(string $ad): ?int
    {
        $v = self::deger($ad);
        return $v === self::IKI_CINSIYET ? null : $v;
    }

    /** Ad iki cinsiyette de kullaniliyor mu? (true ise musteri istatistigine de bakilmaz) */
    public static function belirsizMi(string $ad): bool
    {
        return self::deger($ad) === self::IKI_CINSIYET;
    }

    /**
     * 1 / 0 / 2 (iki cinsiyet) / null (bilinmiyor). Sira: panel karari (Sistem Yonetimi > İsim Sözlüğü) ->
     * kod duzeltmesi -> buyuk veri -> elle listeler.
     */
    protected static function deger(string $ad): ?int
    {
        $anahtar = self::anahtar($ad);
        if ($anahtar === '') return null;
        $panel = self::panelKararlari();
        if (isset($panel[$anahtar])) return $panel[$anahtar];
        return self::sozlukDegeri($anahtar, self::ilkAd($ad));
    }

    /** Panel haric (kod + veri + elle liste). Panel ekrani "sözlükte ne diyor" gostermek icin de kullanir. */
    public static function sozlukDegeri(string $anahtar, ?string $ilk = null): ?int
    {
        if (isset(self::DUZELTME[$anahtar])) return self::DUZELTME[$anahtar];
        if (self::$veri === null) self::$veri = require __DIR__ . '/veri/turkce_isim_cinsiyet.php';
        if (isset(self::$veri[$anahtar])) return self::$veri[$anahtar];
        if ($ilk === null) return null;
        if (in_array($ilk, self::BELIRSIZ, true)) return self::IKI_CINSIYET;
        if (in_array($ilk, self::ERKEK_ADLAR, true)) return self::ERKEK;
        if (in_array($ilk, self::KADIN_ADLAR, true)) return self::KADIN;
        return null;
    }

    /** "Ayşe Nur" -> "ayse" (ilk ad, Turkce karaktersiz kucuk harf). */
    public static function anahtar(string $ad): string
    {
        return strtr(self::ilkAd($ad), ['ı' => 'i', 'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c', 'â' => 'a', 'î' => 'i', 'û' => 'u']);
    }

    protected static function ilkAd(string $ad): string
    {
        $ilk = explode(' ', trim($ad))[0] ?? '';
        return mb_strtolower(strtr($ilk, ['I' => 'ı', 'İ' => 'i']), 'UTF-8');
    }

    // ------------------------------------------------------------------ OGRENEN SOZLUK (panel)
    // Sozlukte olmayan bir ad aramada gecince "karar bekliyor" olarak kaydedilir (gorulme sayilir);
    // Sistem Yonetimi > İsim Sözlüğü'nde Erkek / Kadın / Hitapsız secilince bir sonraki aramada kullanilir.

    const TABLO = 'santral_isim_sozluk';
    const ONBELLEK = 'santral_isim_sozluk_kararlar';
    protected static ?array $panel = null;

    /** Tablo yoksa olusturur (migrate gerekmez; SantralPaket ile ayni kalip). */
    public static function semaGaranti(): void
    {
        if (\Cache::get('santral_isim_sozluk_sema_v1')) return;
        if (!\Schema::hasTable(self::TABLO)) {
            \Schema::create(self::TABLO, function ($t) {
                $t->increments('id');
                $t->string('anahtar', 60)->unique();          // Turkce karaktersiz ilk ad
                $t->string('ad', 60);                          // ilk goruldugu yazilis ("Orbey")
                $t->tinyInteger('cinsiyet')->nullable();       // 1 Erkek, 0 Kadın, 2 Hitapsız, null = karar bekliyor
                $t->unsignedInteger('gorulme')->default(0);    // kac aramada gecti
                $t->timestamp('son_gorulme')->nullable();
                $t->string('karar_veren', 100)->nullable();
                $t->timestamps();
                $t->index('cinsiyet');
            });
        }
        \Cache::forever('santral_isim_sozluk_sema_v1', 1);
    }

    /** Panelde karar verilmis adlar [anahtar => 1/0/2]. 10 dk onbellek; hata/tablo yoksa bos. */
    public static function panelKararlari(): array
    {
        if (self::$panel !== null) return self::$panel;
        try {
            self::$panel = \Cache::remember(self::ONBELLEK, 10, function () {
                if (!\Schema::hasTable(self::TABLO)) return [];
                return \DB::table(self::TABLO)->whereNotNull('cinsiyet')->pluck('cinsiyet', 'anahtar')
                    ->map(function ($v) { return (int) $v; })->all();
            });
        } catch (\Throwable $e) {
            self::$panel = [];
        }
        return self::$panel;
    }

    /**
     * Ayni adli ve cinsiyeti girilmis musteri sayilari (tum sistem): ['e' => erkek, 'k' => kadin]; hata/zaman asimi null.
     * Ad basina 1 gun onbellek, sorgu 400 ms siniri (MySQL).
     */
    public static function istatistik(string $ilkAd): ?array
    {
        $ilkAd = trim($ilkAd);
        if (mb_strlen($ilkAd) < 2) return null;
        try {
            return \Cache::remember('santral_ad_istatistik_' . md5(self::anahtar($ilkAd)), 60 * 24, function () use ($ilkAd) {
                $s = \DB::selectOne('SELECT /*+ MAX_EXECUTION_TIME(400) */ SUM(cinsiyet = 1) AS e, SUM(cinsiyet = 0) AS k
                    FROM users WHERE cinsiyet IN (0, 1) AND (name LIKE ? OR name = ?)', [$ilkAd . ' %', $ilkAd]);
                return ['e' => (int) ($s->e ?? 0), 'k' => (int) ($s->k ?? 0)];
            });
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function onbellekSil(): void
    {
        self::$panel = null;
        try { \Cache::forget(self::ONBELLEK); } catch (\Throwable $e) {}
    }

    /**
     * Ad sozlukte (panel/kod/veri/elle liste) YOKSA "karar bekliyor" listesine yaz, gorulmeyi artir.
     * Telefonu bekletmesin diye tek kucuk sorgu; hata olursa sessiz gecer.
     */
    public static function ogren(string $ad): void
    {
        $anahtar = self::anahtar($ad);
        if (mb_strlen($anahtar) < 2 || mb_strlen($anahtar) > 60 || !preg_match('/^[a-z]+$/', $anahtar)) return;
        if (self::deger($ad) !== null) return;
        try {
            self::semaGaranti();
            $ilk = self::ilkAd($ad);
            $gorunen = mb_strtoupper(strtr(mb_substr($ilk, 0, 1, 'UTF-8'), ['i' => 'İ', 'ı' => 'I']), 'UTF-8') . mb_substr($ilk, 1, null, 'UTF-8');
            $n = \DB::table(self::TABLO)->where('anahtar', $anahtar)->increment('gorulme', 1, ['son_gorulme' => now(), 'updated_at' => now()]);
            if (!$n) {
                \DB::table(self::TABLO)->insert(['anahtar' => $anahtar, 'ad' => $gorunen, 'gorulme' => 1,
                    'son_gorulme' => now(), 'created_at' => now(), 'updated_at' => now()]);
            }
        } catch (\Throwable $e) {
            // ayni anda iki arama ayni adi eklerse unique hatasi -> onemsiz
        }
    }
}
