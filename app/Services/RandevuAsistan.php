<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\MusteriPortfoy;
use App\Personeller;
use App\PersonelHizmetler;
use App\Randevular;
use App\RandevuHizmetler;
use App\SalonHizmetler;
use App\Services\SesliRandevuCozService;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\AiAsistanController;

/**
 * RandevuAsistan — sesli randevu beyni (restaurant-laravel SantralAsistan muadili).
 *
 * Node kopru (randevu-santral-ai) STT metnini /api/santral/konus'a yollar; bu sinif
 * Anthropic (claude-haiku) + TOOL dongusu ile cevap+aksiyon uretir. Araclar, mevcut
 * App\Http\Controllers\Api\AiAsistanController metotlarini YENIDEN KULLANIR (DB mantigi
 * kopyalanmaz: randevuOlustur/musaitSaatler/hizmetEslestir vb.).
 *
 * Kontrat (restaurant ile birebir):
 *   konus($metin,$gecmis): ['cevap','aksiyon'(null|randevu|iptal|guncelle|aktar|veda),'veri','bitir']
 *   bitir=true yalniz aksiyon==='veda'.
 */
class RandevuAsistan
{
    public ?string $teshis = null;
    /** Bu nesnenin yaptigi Claude cagrilarinin toplam token kullanimi (paket/maliyet takibi; kopru controller'i oturuma yazar). */
    public array $kullanim = ['girdi' => 0, 'cikti' => 0, 'cache_okuma' => 0, 'cache_yazma' => 0, 'cagri' => 0];
    /** Bu turdaki her Claude cagrisinin suresi (ms) -> [SANTRAL-SURE] logu (gecikme teshisi). */
    public array $llmMs = [];
    protected string $_onBaglam = '';

    protected int $salonId;
    protected ?string $telefon;
    protected array $salon = [];        // ad, karsilama, hizmetler[]
    protected ?string $musteriAd = null;
    protected ?int $musteriId = null;    // bu salonun portfoyunde AKTIF musteri (karsilama ile ayni kural)
    protected ?int $musteriCinsiyet = null; // users.cinsiyet: 0=Kadın, 1=Erkek, null=belirtilmemis (hitap icin)
    protected bool $_isimOgrenildi = false; // sozlukte olmayan ad bu nesnede panele bir kez yazilsin
    /** Arayan numarasi gizli/gecersizdi ve kayitta telefon alindiysa: oturuma yazilsin diye kopru controller'i okur. */
    public ?string $yeniTelefon = null;
    protected ?array $_personeller = null;
    protected array $_personelCins = []; // personel_id => kayittaki cinsiyet (0 Bayan / 1 Bay / null)
    protected ?array $_paketler = null;
    protected ?array $_randevularim = null; // tur icinde onbellek (prompt her model cagrisinda yeniden kurulur)
    protected AiAsistanController $ctrl;

    public function __construct(int $salonId, ?string $telefon = null)
    {
        $this->salonId = $salonId;
        $this->telefon = $telefon ? preg_replace('/\D/', '', $telefon) : null;
        $this->ctrl = new AiAsistanController();
        $this->salonYukle();
        $this->musteriYukle();
    }

    // ---------------------------------------------------------------- public
    /**
     * Kayitli musteri: "Merhaba Anıl Bey, <isletme> hoş geldiniz. Ben yapay zeka asistanınızım, ..." (isim SORULMAZ).
     * Kayitsiz: karsilamada ad soyad (numara gizliyse telefon da) istenir -> musteri_kaydet.
     */
    public function karsilama(): string
    {
        // Restoran santrali ile ayni kalip: "<isletme> hoş geldiniz, ben yapay zeka asistanınızım. ..."
        $hosgeldin = $this->hosgeldinMetni();
        if ($this->musteriAd) {
            // Paket karsilamada SOYLENMEZ (uzun + dogal degil): musteri ne istedigini soyler ("lazer paketim vardı",
            // "lazer randevusu alacaktım"), asistan paketi o an bulup getirir (PAKET AKIŞI). Kampanya daveti kalir.
            $kampanyaCumle = $this->kampanyaTeklifCumlesi();
            if ($kampanyaCumle !== '') {
                return "{$this->acilisHitap($this->musteriAd)}, {$hosgeldin}. {$this->benKimim()}. {$kampanyaCumle} Ya da size nasıl yardımcı olabilirim?";
            }
            return "{$this->acilisHitap($this->musteriAd)}, {$hosgeldin}. " . ($this->asistanAdi() ? "Ben {$this->asistanAdi()}, size nasıl yardımcı olabilirim?" : 'Ben yapay zeka asistanınızım. Size nasıl yardımcı olabilirim?');
        }
        $ilk = mb_strtoupper(mb_substr($hosgeldin, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($hosgeldin, 1, null, 'UTF-8');
        if ($this->telNorm($this->telefon)) {
            return "{$ilk}, " . $this->benKimim(false) . ". Size yardımcı olabilmem için adınızı ve soyadınızı öğrenebilir miyim?";
        }
        return "{$ilk}, " . $this->benKimim(false) . ". Size yardımcı olabilmem için adınızı, soyadınızı ve telefon numaranızı öğrenebilir miyim?";
    }

    /** Salonun karsilama telaffuzu (eski santralla ayni: "<telaffuz> hoşgeldiniz"), yoksa salon adi. */
    protected function hosgeldinMetni(): string
    {
        $t = trim((string) ($this->salon['karsilama'] ?? ''), " \t\n\r.");
        if ($t !== '') {
            return preg_match('/ho[şs]\s*geldiniz/iu', $t) ? $t : "{$t} hoş geldiniz";
        }
        return ($this->salon['ad'] ?? 'randevu hattımız') . ' randevu hattına hoş geldiniz';
    }

    public function taninanMusteri(): ?string { return $this->musteriAd; }

    // ============================================================ GIDEN HATIRLATMA ARAMASI
    /**
     * Kopru "hatirlatma,<hedef>" ile baslatir. Hedef: "randevu:<id>" | "alacak:<id>-<id>".
     * Kaydin salonu + musterinin telefonu (oturum bu bilgilerle kurulur).
     * @return array{salon_id:int,telefon:?string}|null
     */
    public static function hatirlatmaHedefi(string $hedef): ?array
    {
        if (preg_match('/^randevu:(\d+)$/', $hedef, $m)) {
            $r = Randevular::with('users')->find((int) $m[1]);
            return $r ? ['salon_id' => (int) $r->salon_id, 'telefon' => optional($r->users)->cep_telefon] : null;
        }
        if (preg_match('/^alacak:([\d\-]+)$/', $hedef, $m)) {
            $ids = array_values(array_filter(array_map('intval', explode('-', $m[1]))));
            $a = $ids ? \App\Alacaklar::with('musteri')->find($ids[0]) : null;
            return $a ? ['salon_id' => (int) $a->salon_id, 'telefon' => optional($a->musteri)->cep_telefon] : null;
        }
        return null;
    }

    /** Bu salonda hatirlatma aramalari yeni asistanla mi yapilir? (.env SANTRAL_AI_HATIRLATMA_SALONLAR="20,35" | "*") */
    public static function aiHatirlatmaAcik($salonId): bool
    {
        $l = trim((string) config('services.santral_ai.hatirlatma_salonlar', ''));
        if ($l === '') return false;
        if ($l !== '*' && !in_array((int) $salonId, array_map('intval', preg_split('/[\s,;]+/', $l)), true)) return false;
        // Asistan paketi bitti + "asimda durdur" ayari: AI ile arama yapma (klasik tuslamali IVR'a duser, AI maliyeti yok)
        return !SantralPaket::asistanKapali((int) $salonId);
    }

    /**
     * Hatirlatma karsilamasi + gecmise yazilacak meta. Kayit artik gecerli degilse (iptal/odendi) null.
     * Ayrica "ulasildi" isaretler (eski ulasildi.php / asistanUlasti ile ayni alanlar).
     * @return array{0:string,1:array}|null
     */
    public function hatirlatmaKarsilama(string $hedef): ?array
    {
        $ad = $this->musteriAd;
        $isletme = $this->hatirlatmaIsletmeAdi();
        if (preg_match('/^randevu:(\d+)$/', $hedef, $m)) {
            $r = Randevular::with('users')->where('id', (int) $m[1])->where('salon_id', $this->salonId)->first();
            if (!$r || !in_array((int) $r->durum, [Randevular::BEKLENIYOR, Randevular::ONAYLANDI], true)) return null;
            try { $r->hatirlatma_ulasilamadi = false; $r->tekrar_aranacak = 0; $r->tekrar_arandi = 0; $r->save(); } catch (\Throwable $e) {}
            $ad = $ad ?: optional($r->users)->name;
            $satir = $this->randevuSatiri($r);
            $gun = $satir['tarih'] === now('Europe/Istanbul')->addDay()->toDateString() ? 'yarın, ' . $satir['gun'] : $satir['gun'];
            $soz = $this->randevuSoz(['gun' => $gun] + $satir);
            $k = $this->acilisHitap($ad) . ", ben " . ($this->asistanAdi() ? "{$isletme} asistanı {$this->asistanAdi()}" : "{$isletme} yapay zeka asistanıyım") . ". "
                . "{$soz} randevunuzu hatırlatmak için arıyorum. Randevunuza gelebilecek misiniz?";
            return [$k, ['tur' => 'randevu', 'randevu_id' => (int) $r->id, 'soz' => $soz]];
        }
        if (preg_match('/^alacak:([\d\-]+)$/', $hedef, $m)) {
            $ids = array_values(array_filter(array_map('intval', explode('-', $m[1]))));
            $as = \App\Alacaklar::with('musteri')->whereIn('id', $ids)->where('salon_id', $this->salonId)->get();
            if ($as->isEmpty()) return null;
            $ad = $ad ?: optional($as->first()->musteri)->name;
            $toplam = (float) $as->sum('tutar');
            $tarihler = $as->pluck('planlanan_odeme_tarihi')->filter()->map(function ($t) { return substr((string) $t, 0, 10); })->unique()->sort()->values();
            foreach ($as as $a) { try { $a->hatirlatma_ulasilamadi = false; $a->tekrar_aranacak = 0; $a->tekrar_arandi = 0; $a->save(); } catch (\Throwable $e) {} }
            $tarihSoz = $tarihler->map(function ($t) { return $this->tarihSoz($t); })->implode(' ve ');
            $tutar = number_format($toplam, floor($toplam) == $toplam ? 0 : 2, ',', '.') . ' lira';
            $k = $this->acilisHitap($ad) . ", ben " . ($this->asistanAdi() ? "{$isletme} asistanı {$this->asistanAdi()}" : "{$isletme} yapay zeka asistanıyım") . ". "
                . ($tarihSoz !== '' ? "{$tarihSoz} tarihinde ödemeniz gereken" : 'Ödemeniz gereken')
                . " toplam {$tutar} borcunuz bulunmaktadır. Ödemenizi bu tarihte yapabilecek misiniz?";
            return [$k, ['tur' => 'alacak', 'alacak_idler' => $as->pluck('id')->map(function ($v) { return (int) $v; })->all(), 'tutar' => $tutar, 'tarih' => $tarihSoz]];
        }
        return null;
    }

    /** "X için ..." kalibi: eski hatirlatma ile ayni (santral_telaffuz_2), yoksa salon adi. */
    protected function hatirlatmaIsletmeAdi(): string
    {
        $t = '';
        try { $t = trim((string) \App\Salonlar::where('id', $this->salonId)->value('santral_telaffuz_2')); } catch (\Throwable $e) {}
        return $t !== '' ? $t : $this->salonAdi();
    }

    /** LLM'e giden gizli not (musteri duymaz). */
    protected function hatirlatmaNotu(array $h): string
    {
        if (($h['tur'] ?? '') === 'randevu') {
            return "Bu arama İŞLETMENİN müşteriyi aradığı randevu HATIRLATMA aramasıdır. Hatırlatılan randevu: randevu_id={$h['randevu_id']} ({$h['soz']}). "
                . 'Müşteri gelemeyecekse başka gün/saate ertelemeyi (musait_saatler + randevu_guncelle, bu randevu_id ile) ya da iptali (randevu_iptal) öner.';
        }
        return "Bu arama İŞLETMENİN müşteriyi aradığı ÖDEME HATIRLATMA aramasıdır ({$h['tarih']} tarihli toplam {$h['tutar']}). "
            . 'Borç tutarı, taksit, vade değişikliği gibi konuları sen konuşma: müşteriyi işletmeye aktar.';
    }

    /** Asistanin son sorusu hatirlatma sorusuysa (cevap bekleniyor) o aramanin meta'si; degilse null. */
    protected function hatirlatmaBekleyen(array $gecmis): ?array
    {
        $meta = null; $son = '';
        foreach ($gecmis as $g) {
            if (($g['role'] ?? '') !== 'assistant') continue;
            if (!empty($g['hatirlatma']) && $meta === null) $meta = $g['hatirlatma'];
            $son = (string) ($g['content'] ?? '');
        }
        if (!$meta) return null;
        $adim = null;
        if (preg_match('/gelebilecek misiniz\?$/u', $son)) $adim = 'soru';
        elseif (preg_match('/yapabilecek misiniz\?$/u', $son)) $adim = 'soru';
        elseif (preg_match('/ertelemek mi istersiniz, yoksa iptal etmemi mi istersiniz\?$/u', $son)) $adim = 'ertele_iptal';
        return $adim ? ['adim' => $adim] + $meta : null;
    }

    /**
     * Hatirlatma sorusuna cevap. Anlasilmayan / tarihli ("perşembe 14'e alalım") cevapta null -> normal akis (LLM, gizli notla).
     * @return array{cevap:string,aksiyon:?string,baglam?:string}|null
     */
    protected function hatirlatmaCevap(string $metin, array $h): ?array
    {
        $f = mb_strtolower(strtr($metin, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        $iptal = (bool) preg_match('/iptal|vazgeç|sil(in|ebilir)/u', $f);
        $ertele = (bool) preg_match('/ertele|değiştir|kaydır|başka (bir )?(gün|saat|zaman|tarih)|ileri (bir )?(tarih|gün)|güncelle/u', $f);
        $olumsuz = $iptal || $ertele || (bool) preg_match('/\bhayır|gelemem|gelemeyece|gelemiyorum|gelmeyece|yetişeme|müsait değil|uygun değil|işim çıktı|yapamam|yapamayaca|ödeyemem|ödeyemeyece|ödeyemiyorum|öde(ye)?mi?yeceğim|vade|taksit|sonra öde/u', $f);
        $olumlu = !$olumsuz && ($this->onayMi($metin) || preg_match('/geleceğim|gelirim|geliyorum|gelicem|oradayım|orada olacağım|ödeyeceğim|öderim|ödüyorum|ödeyebilirim|yapacağım|yaparım|yapabilirim|ödedim|ödeme yaptım/u', $f));
        $tarihVar = function () use ($metin) {
            $z = app(SesliRandevuCozService::class)->zamanCoz($metin);
            return !empty($z['tarih']) || !empty($z['saat']);
        };

        if (($h['tur'] ?? '') === 'alacak') {
            if ($olumlu) {
                $ids = array_map('intval', $h['alacak_idler'] ?? []);
                $n = \App\Alacaklar::whereIn('id', $ids)->where('salon_id', $this->salonId)->update(['odenecek' => 1]);
                Log::info('[SANTRAL-HATIRLATMA] alacak odenecek', ['salon' => $this->salonId, 'alacaklar' => $ids, 'guncellenen' => $n]);
                return ['cevap' => 'Teşekkür ederiz, ödemenizi yapacağınızı işletmemize ilettim. Başka yardımcı olabileceğim bir konu var mı?', 'aksiyon' => 'odeme'];
            }
            if ($olumsuz) {
                return ['cevap' => 'Ödemenizle ilgili görüşmek için sizi hemen işletmemize bağlıyorum, lütfen hatta kalın.', 'aksiyon' => 'aktar'];
            }
            return null; // anlasilmadi -> LLM (gizli not: vade/tutar konusu -> aktar)
        }

        // ---- randevu hatirlatma
        $rid = (int) ($h['randevu_id'] ?? 0);
        $r = $rid ? Randevular::where('id', $rid)->where('salon_id', $this->salonId)->first() : null;
        if (!$r || !in_array((int) $r->durum, [Randevular::BEKLENIYOR, Randevular::ONAYLANDI], true)) return null;
        $soz = (string) ($h['soz'] ?? '');
        $baglam = "hatırlatılan randevu_id={$rid}";

        if ($h['adim'] === 'soru' && $olumlu) {
            $r->randevuya_gelecek = 1;
            $r->save();
            Log::info('[SANTRAL-HATIRLATMA] randevuya gelecek', ['salon' => $this->salonId, 'randevu' => $rid]);
            return ['cevap' => 'Teşekkür ederiz, sizi bekliyoruz. Randevunuza on beş dakika önce gelmenizi rica ederiz. Başka yardımcı olabileceğim bir konu var mı?', 'aksiyon' => 'teyit', 'baglam' => $baglam];
        }
        if ($iptal) {
            return ['cevap' => "{$soz} randevunuzu iptal etmemi onaylıyor musunuz?", 'aksiyon' => null, 'baglam' => $baglam];
        }
        if ($ertele || ($h['adim'] === 'ertele_iptal' && preg_match('/^(ertele|ertelemek|ertelensin|erteleyelim|başka gün)/u', $f))) {
            if ($tarihVar()) return null; // "perşembe 14'e alalım" -> model musaitlik + randevu_guncelle
            return ['cevap' => 'Tabii, randevunuzu hangi gün ve saate almak istersiniz?', 'aksiyon' => null, 'baglam' => $baglam];
        }
        if ($h['adim'] === 'soru' && $olumsuz) {
            return ['cevap' => 'Anlıyorum. Randevunuzu başka bir gün ve saate ertelemek mi istersiniz, yoksa iptal etmemi mi istersiniz?', 'aksiyon' => null, 'baglam' => $baglam];
        }
        return null;
    }

    public function hizmetSayisi(): int { return count($this->salon['hizmetler'] ?? []); }

    public function salonAdi(): string { return $this->salon['ad'] ?? ('Salon ' . $this->salonId); }

    /**
     * Ana beyin. $gecmis: [['role'=>'user'|'assistant','content'=>'...'], ...]
     * @return array{cevap:string,aksiyon:?string,veri:array,bitir:bool}
     */
    public function konus(string $metin, array $gecmis = []): array
    {
        $this->teshis = 'ok';
        $metin = $this->sttDuzelt(trim($metin));
        if ($metin === '') {
            return ['cevap' => 'Buyurun, sizi dinliyorum.', 'aksiyon' => null, 'veri' => [], 'bitir' => false];
        }
        // KESIN AKTARMA: "beni işletmeye/yetkiliye bağlar mısın", "operatör", "biriyle görüşmek istiyorum" ->
        // LLM'e sormadan aktar (STT "işletmeye"yi "genişletme" duysa bile "bağlar mısın" yakalanir).
        $fiyatSorusu = !$this->aktarmaIstegiMi($metin) && $this->fiyatSorusuMu($metin);
        if ($fiyatSorusu || $this->aktarmaIstegiMi($metin)) {
            Log::info('[SANTRAL-TUR] kesin aktarma', ['salon' => $this->salonId, 'metin' => $metin, 'fiyat' => $fiyatSorusu]);
            $this->teshis = 'aktar';
            $cevap = $fiyatSorusu
                ? 'Fiyat bilgisi için sizi hemen işletmemize bağlıyorum, lütfen hatta kalın.'
                : 'Sizi hemen yetkili bir arkadaşımıza bağlıyorum, lütfen hatta kalın.';
            return ['cevap' => $cevap, 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
        }
        // HIZLI VEDA: "Başka yardımcı olabileceğim bir konu var mı?" -> "yok, teşekkürler" ise KODLA kapat.
        // LLM'li veda (arac + kapanis cumlesi = 2 cagri) 5,5 sn suruyordu (2026-11-17 kayit).
        if ($this->baskaYardimSoruldu($gecmis) && $this->vedaMi($metin)) {
            Log::info('[SANTRAL-TUR] hizli veda', ['salon' => $this->salonId, 'metin' => $metin]);
            $this->teshis = 'veda';
            $h = $this->hitap();
            return ['cevap' => 'Rica ederiz' . ($h ? " {$h}" : '') . ', bizi aradığınız için teşekkür ederiz. İyi günler dileriz.', 'aksiyon' => 'veda', 'veri' => [], 'bitir' => true];
        }
        // GIDEN HATIRLATMA ARAMASI (randevu 1 gun once / alacak 2 gun once): asistanin hatirlatma sorusuna
        // verilen cevap KODLA islenir (gelecek/odeyecek isaretle, ertele/iptal sor, vade -> isletmeye aktar).
        if (($h = $this->hatirlatmaBekleyen($gecmis)) !== null && ($hc = $this->hatirlatmaCevap($metin, $h)) !== null) {
            Log::info('[SANTRAL-HATIRLATMA] cevap', ['salon' => $this->salonId, 'tur' => $h['tur'], 'metin' => $metin, 'aksiyon' => $hc['aksiyon']]);
            $this->teshis = 'hatirlatma';
            $hc['cevap'] = $this->ttsTemizle($hc['cevap']);
            return $hc + ['veri' => [], 'bitir' => false];
        }
        // IPTAL / DEGISTIRME NIYETI -> randevulari KOD soyler (restoran "mevcut rezervasyon" kalibi; LLM/arac turu yok,
        // "sunalım..." deyip susma olmaz). Liste zaten okunduysa (son asistan mesajinda) tekrar etme, modele birak.
        $direkt = $this->iptalGuncelleGiris($metin, $gecmis);
        // HIZMET SURESI ("saç kesimi ne kadar sürer") -> gercek sure_dk ile KOD cevaplar (model sure uydurmasin/aktarmasin)
        if ($direkt === null && ($sureCevap = $this->sureCevabi($metin)) !== null) {
            Log::info('[SANTRAL-TUR] sure sorusu', ['salon' => $this->salonId, 'metin' => $metin, 'cevap' => $sureCevap]);
            $this->teshis = 'sure';
            return ['cevap' => $this->baskaYardimEkle($sureCevap), 'aksiyon' => null, 'veri' => [], 'bitir' => false];
        }
        if ($direkt === null && ($sss = $this->sssCevap($metin)) !== null) {
            // ISLETMENIN KENDI CEVABI (panel > Santral > Sık Sorulan Sorular): LLM'siz, aynen okunur.
            Log::info('[SANTRAL-TUR] sss', ['salon' => $this->salonId, 'metin' => $metin]);
            $this->teshis = 'sss';
            return ['cevap' => $this->baskaYardimEkle($this->ttsTemizle($sss)), 'aksiyon' => null, 'veri' => [], 'bitir' => false];
        }
        // HIZMET LISTESI ("hangi hizmetleriniz var") -> KOD: 5 ornek hizmet + kalanlarin kategorileri (model 13 sn liste okuyordu)
        if ($direkt === null && ($hl = $this->hizmetListesiCevabi($metin)) !== null) {
            Log::info('[SANTRAL-TUR] hizmet listesi', ['salon' => $this->salonId, 'metin' => $metin, 'cevap' => $hl]);
            $this->teshis = 'hizmet_liste';
            return ['cevap' => $this->ttsTemizle($hl), 'aksiyon' => null, 'veri' => [], 'bitir' => false];
        }
        if ($direkt !== null) {
            Log::info('[SANTRAL-TUR] iptal/guncelle girisi (kod)', ['salon' => $this->salonId, 'metin' => $metin, 'cevap' => $direkt]);
            return ['cevap' => $this->ttsTemizle($direkt), 'aksiyon' => null, 'veri' => [], 'bitir' => false];
        }

        $key = config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY');
        if (!$key) {
            $this->teshis = 'anahtar_yok';
            return ['cevap' => 'Sizi bir yetkiliye aktarıyorum, lütfen hatta kalın.', 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
        }

        $messages = $this->gecmisMesajlari($gecmis);
        $turBas = microtime(true);
        $onNot = $this->onEslestirmeNotu($metin, $onPaketli);
        $messages[] = ['role' => 'user', 'content' => $metin . $this->zamanNotu($metin)
            . ($onNot !== '' ? $onNot : $this->hizmetDuzeltmeNotu($metin))
            . ($onPaketli ? '' : $this->paketNotu($metin, $gecmis))
            . $this->paketKarariNotu($gecmis, $metin)];

        $aksiyon = null; $bitir = false; $randevuId = null; $cevap = '';
        $isTamamlandi = false; // randevu/iptal/guncelleme/yol tarifi tamamlandi -> "Başka yardımcı olabileceğim..." sorulmali
        $yolTarifiGitti = false; // yol tarifi mesaji basariyla gonderildi -> sabit cumle
        $randevuHatasi = null;   // bu turda randevu_olustur/guncelle ok=false dondu (sebep)
        $randevuDenendi = false; // bu turda randevu_olustur cagrildi mi
        $randevuSonuc = null;    // bu turda randevu_olustur ok=true sonucu (onay cumlesi koddan kurulur)
        $guncelleSonuc = null;   // bu turda randevu_guncelle ok=true sonucu (onay cumlesi koddan kurulur)
        $doluSonuc = null;       // bu turda musait_saatler "istenen saat dolu + oneriler" dondu (cumle koddan garanti)
        $baglam = [];            // bu turun arac sonuc ozeti -> gecmise gizli not (sonraki tur id'leri bilsin)
        if ($onNot !== '' && $this->_onBaglam !== '') $baglam[] = $this->_onBaglam;

        for ($deneme = 0; $deneme < 2; $deneme++) {
        for ($tur = 0; $tur < 6; $tur++) {
            $resp = $this->cagir($messages, $key);
            if (!$resp) {
                if ($this->teshis === 'ok') $this->teshis = 'cevap_yok';
                // Arac sonrasi cevap alinamadiysa elde kalan on-cumle ("sunalım...") anlamsiz: guvenli cumle
                if ($tur > 0 && !$aksiyon) $cevap = 'Kusura bakmayın, sizi tam anlayamadım. Tekrar söyler misiniz?';
                break;
            }

            if (($resp['stop_reason'] ?? '') === 'refusal') {
                return ['cevap' => 'Bu konuda size en doğru bilgiyi işletmemiz verecektir, sizi hemen bağlıyorum, lütfen hatta kalın.', 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
            }
            $content = $resp['content'] ?? [];
            $metinler = []; $toolUse = [];
            foreach ($content as $b) {
                $t = $b['type'] ?? '';
                if ($t === 'text') $metinler[] = $b['text'] ?? '';
                elseif ($t === 'tool_use') $toolUse[] = $b;
            }
            $yeni = trim(implode(' ', array_filter($metinler)));
            if ($yeni !== '') $cevap = $yeni;

            if (empty($toolUse)) break; // duz dialog cevabi -> tur bitti

            // assistant turunu (tool_use bloklariyla) ekle
            // KRITIK: json_decode(assoc) parametresiz aracin input'unu {} -> [] yapar; API geri gonderilen
            // tool_use.input'un NESNE olmasini ister (yoksa 400 -> tur yarim kalir, "sunalım..." deyip susar).
            $messages[] = ['role' => 'assistant', 'content' => $this->aracGirdisiNesne($content)];

            $sonuclar = [];
            $turAraclari = []; // bu turdaki [arac adi, sonuc] -> araç sonrasi cumle koddan kurulabilir mi
            foreach ($toolUse as $tu) {
                [$sonuc, $aks, $rid, $bit] = $this->toolCalistir($tu['name'] ?? '', $tu['input'] ?? []);
                // Teshis: her arac cagrisi + sonucu (randevu neden olusmadi vb. sorulari loglardan cevaplamak icin)
                Log::info('[SANTRAL-ARAC] ' . ($tu['name'] ?? '?'), [
                    'salon' => $this->salonId, 'musteri' => $this->musteriId, 'tel' => $this->telefon,
                    'girdi' => $tu['input'] ?? [], 'sonuc' => mb_substr(json_encode($sonuc, JSON_UNESCAPED_UNICODE), 0, 1500),
                ]);
                $turAraclari[] = [$tu['name'] ?? '', $sonuc];
                $sonuclar[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $tu['id'] ?? '',
                    'content' => json_encode($sonuc, JSON_UNESCAPED_UNICODE),
                ];
                if ($aks) $aksiyon = $aks;
                if ($rid) $randevuId = $rid;
                if ($bit) $bitir = true;
                if (in_array($aks, ['randevu', 'iptal', 'guncelle'], true) || ($tu['name'] ?? '') === 'yol_tarifi_gonder') $isTamamlandi = true;
                if (($tu['name'] ?? '') === 'yol_tarifi_gonder' && !empty($sonuc['ok'])) $yolTarifiGitti = true;
                $not = $this->baglamNotu($tu['name'] ?? '', $tu['input'] ?? [], $sonuc);
                if ($not !== '') $baglam[] = $not;
                if (($tu['name'] ?? '') === 'randevu_olustur') $randevuDenendi = true;
                if (($tu['name'] ?? '') === 'musait_saatler') $doluSonuc = !empty($sonuc['onerilen_cumle']) ? $sonuc : null;
                if (($tu['name'] ?? '') === 'randevu_olustur' && !empty($sonuc['ok'])) $randevuSonuc = $sonuc;
                if (($tu['name'] ?? '') === 'randevu_guncelle' && !empty($sonuc['ok'])) $guncelleSonuc = $sonuc;
                if (in_array($tu['name'] ?? '', ['randevu_olustur', 'randevu_guncelle'], true) && empty($sonuc['ok'])) {
                    $randevuHatasi = (string) ($sonuc['hata'] ?? 'bilinmeyen hata');
                }
            }
            $messages[] = ['role' => 'user', 'content' => $sonuclar];

            // GECIKME: arac sonucu belirliyse (randevu oldu / guncellendi / iptal / iki secenek / dolu+oneri) cumleyi
            // KOD kurar, son Claude cagrisi yapilmaz (~0,7-0,9 sn). Baglama gore degisen durumlar modele kalir.
            if ($aksiyon !== 'aktar' && $aksiyon !== 'veda' && ($kc = $this->aracSonrasiKodCevabi($turAraclari)) !== null) {
                Log::info('[SANTRAL-KOD-CEVAP]', ['salon' => $this->salonId, 'araclar' => array_column($turAraclari, 0), 'cevap' => $kc]);
                $cevap = $kc;
                break;
            }
            // Terminal aksiyon: aktar/veda -> kapanis cumlesi icin bir tur daha don, sonra bitir.
            if ($aksiyon === 'aktar' || $aksiyon === 'veda') {
                // Model ayni cevapta bir cumle soylediyse onu kullan; yoksa SABIT kapanis cumlesi (garantiCevap).
                // Eskiden yalniz kapanis cumlesi icin bir LLM turu daha yapiliyordu (~1-1,5 sn ek bekleme).
                $resp2 = null;
                if ($resp2) {
                    $t2 = [];
                    foreach (($resp2['content'] ?? []) as $b) {
                        if (($b['type'] ?? '') === 'text') $t2[] = $b['text'] ?? '';
                    }
                    $c2 = trim(implode(' ', array_filter($t2)));
                    if ($c2 !== '') $cevap = $c2;
                }
                $this->teshis = $aksiyon;
                break;
            }
            // query/commit araclari -> dongu devam (LLM sonucu gorup kapanis cumlesi/soru uretsin)
        }
            // SAHTE BASARI KORUMASI: model randevu_olustur/guncelle ok=true OLMADAN "oluşturuldu/güncellendi"
            // derse (araci hic cagirmadi ya da hata aldi) musteriye yanlis bilgi gitmesin: bir duzeltme turu.
            $sahteBasari = !in_array($aksiyon, ['randevu', 'guncelle'], true)
                && $this->sahteBasariMetni($cevap);
            // BEKLETME CUMLESI: "bir moment / bekleteceğim / kontrol ediyorum" -> cumleyi at; geriye bir sey
            // kalmadiysa (model araci cagirmadan bekletip turu bitirdi) duzeltme turu: araci simdi cagirsin.
            $cevap = $this->dogalBaslangic($this->bekletmeTemizle($this->icSesTemizle($cevap)));
            $bekletti = ($cevap === '' && !$bitir && !in_array($aksiyon, ['aktar', 'veda'], true));
            if ($bekletti && $deneme === 0) {
                Log::warning('[SANTRAL-BEKLETME] model bekletme cumlesiyle turu bitirdi', ['salon' => $this->salonId, 'metin' => $metin]);
                $messages[] = ['role' => 'assistant', 'content' => 'Bir moment.'];
                $messages[] = ['role' => 'user', 'content' => '[SİSTEM — müşteri bunu duymadı] Müşteriyi bekletme. Gereken aracı (ör. mevcut_randevular, musait_saatler) ŞİMDİ çağır ve sonucu doğrudan söyle.'];
                continue;
            }
            if (!$sahteBasari || $deneme > 0 || $bitir) break;
            Log::warning('[SANTRAL-SAHTE-BASARI] model arac basarisi olmadan olusturuldu dedi', [
                'salon' => $this->salonId, 'musteri' => $this->musteriId, 'cevap' => $cevap, 'hata' => $randevuHatasi,
            ]);
            $messages[] = ['role' => 'assistant', 'content' => $cevap];
            $messages[] = ['role' => 'user', 'content' => '[SİSTEM — müşteri bunu duymadı] Randevu KAYDEDİLMEDİ: '
                . ($randevuHatasi ? "randevu_olustur hata döndü: {$randevuHatasi}" : 'randevu_olustur aracı çağrılmadı.')
                . ' Müşteri onay verdiyse şimdi randevu_olustur çağır (seçilen seçeneğin tarih_saat, personel_id, hizmet_id). '
                . 'Hata sürerse müşteriye randevunun oluşturulamadığını dürüstçe söyle ve yeni saat ya da yetkiliye aktarma öner. '
                . 'ok=true dönmeden "oluşturuldu" DEME.'];
            $cevap = '';
        }
        // Ikinci denemede de arac basarisi yoksa ve hala "olusturuldu" diyorsa: kesin dogru cumle
        if (!in_array($aksiyon, ['randevu', 'guncelle'], true)
            && $this->sahteBasariMetni($cevap)) {
            $cevap = 'Üzgünüm, randevunuzu şu an kaydedemedim. Başka bir saat deneyelim mi, yoksa sizi bir yetkiliye aktarayım mı?';
        }

        // ZORLA RANDEVU (restoran "zorla aksiyon" guvenlik agi): asistan onay sordu, musteri EVET dedi ama model
        // randevu_olustur'u hic cagirmadi (ör. "sistem kapasitesi nedeniyle..." uydurdu) -> tool_choice ile zorla.
        if ($aksiyon === null && !$randevuDenendi && !$bitir && $this->onayBekleniyorMu($gecmis) && $this->onayMi($metin)) {
            $zorla = $this->zorlaRandevu($messages, $key);
            if ($zorla !== null) {
                [$cevap, $aksiyon, $randevuId, $zNot] = $zorla;
                if ($zNot !== '') $baglam[] = $zNot;
                if ($aksiyon === 'randevu') $isTamamlandi = true;
                $this->teshis = 'zorla_randevu';
            }
        }

        // ISTENEN SAAT DOLU (talep/onayli randevuyla cakisma): model "uygun değil" demeden ya da iki oneriyi
        // eksik okuyarak gecerse (kayit #72: "Cuma 12" -> aciklamasiz 10) cumleyi KOD kurar.
        if ($doluSonuc && $aksiyon === null && !$bitir && !$this->doluCevabiTamMi($cevap, $doluSonuc)) {
            Log::info('[SANTRAL-DOLU-ONERI] cevap kodla kuruldu', ['salon' => $this->salonId, 'model' => $cevap, 'kod' => $doluSonuc['onerilen_cumle']]);
            $cevap = $doluSonuc['onerilen_cumle'];
        }
        if ($cevap === '') $cevap = $this->garantiCevap($aksiyon);
        Log::info('[SANTRAL-TUR]', ['salon' => $this->salonId, 'musteri' => $this->musteriId, 'metin' => $metin, 'cevap' => $cevap, 'aksiyon' => $aksiyon, 'teshis' => $this->teshis]);
        // Gecikme teshisi: tur toplami + her LLM cagrisi (ms). Arac (DB) suresi = toplam - LLM toplami.
        Log::info('[SANTRAL-SURE]', ['salon' => $this->salonId, 'toplam_ms' => (int) round((microtime(true) - $turBas) * 1000),
            'llm_ms' => $this->llmMs, 'llm_toplam_ms' => array_sum($this->llmMs), 'on_eslestirme' => $onNot !== '']);
        // Yol tarifi gonderildiyse (ayni turda baska bir is yoksa) sabit, net cumle:
        if ($yolTarifiGitti && $aksiyon === null && !$bitir) {
            $cevap = 'Yol tarifini size mesaj olarak ilettim. Başka yardımcı olabileceğim bir konu var mı?';
        }
        // Randevu olustuysa onay cumlesi KODDAN (restoran kalibi): model cumleyi atlasa / eksik kursa da
        // musteri talep ifadesini mutlaka duyar (gun/saat/uzman onay sorusunda okundu, tekrar edilmez).
        if ($aksiyon === 'randevu' && $randevuSonuc) {
            $cevap = $this->randevuOnayCumlesi($randevuSonuc);
        } elseif ($aksiyon === 'randevu') {
            $cevap = $this->talepIfadesi($cevap);
        }
        if ($aksiyon === 'guncelle' && $guncelleSonuc) $cevap = $this->guncelleOnayCumlesi($guncelleSonuc);
        if ($isTamamlandi && !$bitir && $aksiyon !== 'aktar') $cevap = $this->baskaYardimEkle($cevap);
        return [
            'cevap' => $this->ttsTemizle($cevap),
            'aksiyon' => $aksiyon,
            'veri' => ['randevu_id' => $randevuId],
            'bitir' => (bool) $bitir,
            // Gecmise GIZLI not (musteri duymaz): sonraki turda model hizmet/personel/saat id'lerini bilsin
            'baglam' => $baglam ? mb_substr(implode(' | ', $baglam), 0, 1200) : null,
        ];
    }

    /** Arac sonucunun sonraki tura tasinacak kisa ozeti (id'ler). */
    protected function baglamNotu(string $ad, array $in, array $sonuc): string
    {
        if ($ad === 'musait_saatler' && !empty($sonuc['secenekler'])) {
            $sec = array_map(function ($x) {
                return ($x['tarih'] ?? '') . 'T' . ($x['saat'] ?? '') . ' personel_id=' . ($x['personel_id'] ?? '') . ' (' . ($x['personel'] ?? '') . ')';
            }, $sonuc['secenekler']);
            return 'musait_saatler hizmet_id=' . ($in['hizmet_id'] ?? '?') . ' seçenekler: ' . implode('; ', $sec);
        }
        if ($ad === 'hizmet_eslestir') {
            return 'hizmet_eslestir("' . ($in['metin'] ?? '') . '") → ' . mb_substr(json_encode($sonuc, JSON_UNESCAPED_UNICODE), 0, 300);
        }
        if ($ad === 'randevu_olustur' && !empty($sonuc['ok'])) {
            return 'randevu_olustur OK randevu_id=' . ($sonuc['randevu_id'] ?? '') . ' ' . ($sonuc['tarih'] ?? '') . ' ' . ($sonuc['saat'] ?? '');
        }
        return '';
    }

    /**
     * Cevap randevunun KAYDEDILDIGINI iddia ediyor mu? (randevu/randevunuz/randevusu + talebi/talebiniz + oluşturuldu/kaydedildi...)
     * "oluşturayım mı" gibi SORULAR iddia sayilmaz.
     */
    protected function sahteBasariMetni(string $c): bool
    {
        return (bool) preg_match('/randevu\w*\s+(talebi\w*\s+)?(talep olarak\s+)?(başarıyla\s+)?(oluşturuldu|oluşturulmuştur|güncellendi|kaydedildi|alındı|alınmıştır)/iu', $c);
    }

    /** Son asistan mesaji randevu onay sorusu muydu? ("... randevu talebi oluşturayım mı?") */
    protected function onayBekleniyorMu(array $gecmis): bool
    {
        for ($i = count($gecmis) - 1; $i >= 0; $i--) {
            if (($gecmis[$i]['role'] ?? '') === 'assistant') {
                $c = (string) ($gecmis[$i]['content'] ?? '');
                return (bool) (preg_match('/randevu/iu', $c) && preg_match('/oluşturayım mı|oluşturalım mı|kaydedeyim mi|onaylıyor musunuz/iu', $c)
                    && !preg_match('/iptal/iu', $c));
            }
        }
        return false;
    }

    /** Son asistan mesaji "Başka yardımcı olabileceğim bir konu var mı?" (ya da "başka bir arzunuz var mı") muydu? */
    protected function baskaYardimSoruldu(array $gecmis): bool
    {
        for ($i = count($gecmis) - 1; $i >= 0; $i--) {
            if (($gecmis[$i]['role'] ?? '') === 'assistant') {
                return (bool) preg_match('/başka\s+(bir\s+)?(konu|yardımcı|isteğiniz|arzunuz|bir şey|merak)[^.?!]*var mı/iu', (string) ($gecmis[$i]['content'] ?? ''));
            }
        }
        return false;
    }

    /** Musteri kapanis mi soyluyor? ("yok teşekkürler", "sağ olun", "bu kadar") — yeni bir istek iceriyorsa hayir. */
    protected function vedaMi(string $m): bool
    {
        $f = ' ' . $this->sade($m) . ' ';
        if (preg_match('/ (ama|fakat|bir de|var mi) | (randevu|iptal|degis|ertele|saat|soru|ogren|istiyor|bir sey daha)/u', $f)) return false;
        return (bool) preg_match('/ (yok|hayir|tesekkur|sag ?ol|eyvallah|iyi gunler|iyi aksamlar|bu kadar|o kadar|gerek yok)/u', $f);
    }

    /** Musteri cevabi onay mi? (ret/kosul icermeyen evet turevleri) */
    protected function onayMi(string $m): bool
    {
        $f = mb_strtolower(strtr($m, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        if (preg_match('/hayır|istemiyorum|olmaz|vazgeç|başka|değil|ama |fakat|iptal/u', $f)) return false;
        return (bool) preg_match('/\b(evet|tamam|olur|olsun|tabii|tabi|onaylıyorum|onayla|uygun|peki|aynen|oluştur|kabul)\b/u', $f);
    }

    /**
     * tool_choice ile randevu_olustur'u ZORLA cagir; sonucu deterministik cumleye cevir.
     * @return array{0:string,1:?string,2:?int,3:string}|null [cevap, aksiyon, randevuId, baglamNotu]
     */
    protected function zorlaRandevu(array $messages, string $key): ?array
    {
        $son = end($messages);
        if (($son['role'] ?? '') !== 'user') return null; // tool_choice icin son mesaj user olmali
        $resp = $this->cagir($messages, $key, 0, ['tool_choice' => ['type' => 'tool', 'name' => 'randevu_olustur']]);
        $tu = null;
        foreach (($resp['content'] ?? []) as $b) if (($b['type'] ?? '') === 'tool_use') { $tu = $b; break; }
        if (!$tu) return null;
        $in = is_array($tu['input'] ?? null) ? $tu['input'] : [];
        [$d] = $this->toolCalistir('randevu_olustur', $in);
        Log::warning('[SANTRAL-ZORLA-RANDEVU]', ['salon' => $this->salonId, 'girdi' => $in, 'sonuc' => mb_substr(json_encode($d, JSON_UNESCAPED_UNICODE), 0, 600)]);
        if (empty($d['ok'])) {
            return ['Üzgünüm, randevu talebinizi şu an kaydedemedim. ' . (preg_match('/çakış|dolu/iu', (string) ($d['hata'] ?? '')) ? 'O saat az önce doldu. Başka bir saat önermemi ister misiniz?' : 'Başka bir saat deneyelim mi, yoksa sizi bir yetkiliye aktarayım mı?'), null, null, ''];
        }
        $c = $this->randevuOnayCumlesi($d);
        return [$c, 'randevu', (int) ($d['randevu_id'] ?? 0) ?: null, $this->baglamNotu('randevu_olustur', $in, $d)];
    }

    /**
     * Arac sonrasi cumle KODDAN (son LLM cagrisi atlanir). Yalniz sonucun cumlesi belli olan durumlar:
     * randevu_olustur/guncelle/iptal ok=true, musait_saatler dolu+oneri ya da 1-2 secenek. Tam eslesme (onay sorusu
     * baglama gore: olustur/guncelle), bos sonuc, hata ve baska araclar -> null (model kurar).
     */
    protected function aracSonrasiKodCevabi(array $araclar): ?string
    {
        if (!$araclar || !config('services.santral_ai.kod_cevap', true)) return null;
        $izinli = ['musait_saatler', 'randevu_olustur', 'randevu_guncelle', 'randevu_iptal'];
        foreach ($araclar as [$ad, $sonuc]) {
            if (!in_array($ad, $izinli, true) || !is_array($sonuc) || empty($sonuc['ok'])) return null;
        }
        foreach ($araclar as [$ad, $sonuc]) {
            if ($ad === 'randevu_olustur') return $this->randevuOnayCumlesi($sonuc);
            if ($ad === 'randevu_guncelle') return $this->guncelleOnayCumlesi($sonuc);
            if ($ad === 'randevu_iptal') {
                if (($sonuc['mesaj'] ?? '') !== 'Randevu iptal edildi') return null;
                return 'Randevunuz iptal edildi.' . (!empty($sonuc['bilgilendirme_gonderildi']) ? ' Size bilgilendirme mesajı ilettim.' : '');
            }
        }
        if (count($araclar) !== 1) return null; // birden fazla musait sorgusu: model birlestirsin
        $m = $araclar[0][1];
        if (!empty($m['onerilen_cumle'])) return (string) $m['onerilen_cumle'];
        if (!empty($m['tam_eslesme']) || empty($m['secenekler'])) return null;
        return $this->secenekCumlesi($m);
    }

    /** musait_saatler 1-2 secenek -> "Cuma, 20 Kasım için uzmanımız Anıl Bey ile saat 10:00 ya da 11:40 uygun. Hangisini tercih edersiniz?" */
    protected function secenekCumlesi(array $m): string
    {
        $sec = array_values(array_slice($m['secenekler'], 0, 2));
        $gun = function ($s) { return $s['gun'] ?? $this->tarihSoz((string) $s['tarih']); };
        $ile = function ($s) { return !empty($s['personel']) ? " uzmanımız {$s['personel']} ile" : ''; };
        $on = '';
        if (!empty($m['istenen_gun_dolu']) && !empty($m['istenen_tarih']) && ($sec[0]['tarih'] ?? '') !== $m['istenen_tarih']) {
            $on = $this->tarihSoz((string) $m['istenen_tarih']) . ' için uygun saat kalmadı. ';
        }
        if (count($sec) === 1) {
            return $on . $gun($sec[0]) . ' saat ' . $sec[0]['saat'] . $ile($sec[0]) . ' uygun. Bu saat olur mu?';
        }
        $ayniGun = $sec[0]['tarih'] === $sec[1]['tarih'];
        $ayniKisi = ($sec[0]['personel_id'] ?? null) === ($sec[1]['personel_id'] ?? null);
        if ($ayniGun && $ayniKisi) {
            return $on . $gun($sec[0]) . ' için' . $ile($sec[0]) . ' saat ' . $sec[0]['saat'] . ' ya da ' . $sec[1]['saat'] . ' uygun. Hangisini tercih edersiniz?';
        }
        $p = [];
        foreach ($sec as $i => $s) {
            $p[] = (($i === 0 || !$ayniGun) ? $gun($s) . ' ' : '') . 'saat ' . $s['saat'] . $ile($s);
        }
        return $on . 'Size ' . implode(' ya da ', $p) . ' önerebilirim. Hangisini tercih edersiniz?';
    }

    /** randevu_guncelle ok=true sonucundan onay cumlesi: "Perşembe, 19 Kasım saat 14:00'te uzmanımız Gülşen Hanım ile randevunuzu güncelledim..." */
    protected function guncelleOnayCumlesi(array $d): string
    {
        $gun = $this->tarihSoz((string) ($d['tarih'] ?? ''));
        $saat = substr((string) ($d['saat'] ?? ''), 0, 5);
        return trim("{$gun} saat " . $this->saatEkli($saat) . (!empty($d['personel']) ? " uzmanımız {$d['personel']} ile" : ''))
            . ' randevunuzu güncelledim' . (!empty($d['bilgilendirme_gonderildi']) ? ' ve size bilgilendirme mesajı gönderdim.' : '.');
    }

    /** randevu_olustur ok=true sonucundan deterministik onay cumlesi. */
    protected function randevuOnayCumlesi(array $d): string
    {
        // Isletme istegi: "... randevu talebinizi oluşturdum ve size bilgilendirme mesajı gönderdim. Randevunuz
        // onaylandığında size bilgi verilecektir." (once yapilan is, SONRA onay bilgisi)
        // Gun/saat/uzman TEKRAR EDILMEZ (kayit 172: onay sorusunda zaten okunmustu, "evet"ten sonra hepsi yeniden
        // sayiliyordu: 9 sn'lik cumle + uzun seslendirme beklemesi). Yol tarifi randevu bilgilendirme mesajinin
        // icinde gider (isletme bilgisi 2026-11-18).
        return 'Tamamdır, ' . (!empty($d['paket_seansi']) ? 'paketinizden ' : '') . 'randevu talebinizi oluşturdum'
            . (!empty($d['bilgilendirme_gonderildi']) ? '; bilgilendirme mesajını ve yol tarifini telefonunuza gönderdim.' : '.')
            . ' Onaylandığında size haber vereceğiz.';
    }

    /**
     * Bir is tamamlandiginda kapanis sorusu GARANTI: "Size nasıl yardımcı olabilirim?" (karsilama sorusu)
     * tekrar edilmez, yerine "Başka yardımcı olabileceğim bir konu var mı?" sorulur.
     */
    protected function baskaYardimEkle(string $cevap): string
    {
        $c = preg_replace('/\s*(Peki,?\s*|Ya da\s*)?(size|sizlere)\s+(başka\s+)?(nasıl|ne konuda)\s+(yardımcı olabilirim|yardımcı olayım)\s*\??/iu', ' ', $cevap);
        $c = trim(preg_replace('/\s{2,}/u', ' ', $c));
        if (!preg_match('/başka\s+(bir\s+)?(konu|yardımcı|isteğiniz|bir şey|merak)/iu', $c)) {
            $c = rtrim($c, " ,") . ($c !== '' && !preg_match('/[.!?]$/u', $c) ? '.' : '') . ' Başka yardımcı olabileceğim bir konu var mı?';
        }
        return trim($c);
    }

    /**
     * Kalip kapanis sorusu ("Başka yardımcı olabileceğim bir konu var mı?") robotik (kayit 172): musteriye giden
     * cumlede sirayla dogal bir soruyla degistirilir (ayni gorusmede ust uste ayni soru gelmez). Kod ve prompt
     * kalibi kullanmaya devam eder; degisim yalniz en sonda (kopru controller'inda) yapilir.
     */
    public function kapanisDogallastir(string $cevap, array $gecmis): string
    {
        $kalip = 'Başka yardımcı olabileceğim bir konu var mı?';
        if (mb_strpos($cevap, $kalip) === false) return $cevap;
        $n = 0;
        foreach ($gecmis as $g) {
            if (($g['role'] ?? '') === 'assistant' && preg_match('/başka\s+(bir\s+)?(konu|yardımcı|isteğiniz|arzunuz|bir şey|merak)[^.?!]*var mı/iu', (string) ($g['content'] ?? ''))) $n++;
        }
        $h = $this->hitap();
        $secenek = [
            'Sizin için yapabileceğim başka bir şey var mı?',
            'Başka bir isteğiniz var mı' . ($h ? ", {$h}" : '') . '?',
            'Başka merak ettiğiniz bir şey var mı?',
        ];
        return str_replace($kalip, $secenek[$n % count($secenek)], $cevap);
    }

    /**
     * Musterinin cumlesindeki tarih/saat ifadesini KODLA coz ("çarşamba 14", "önümüzdeki hafta perşembe 10",
     * "10 aralık", "5 şubat saat 3") ve modele not olarak ver: model tarih hesaplamaz, bu ISO degerleri kullanir.
     */
    protected function zamanNotu(string $metin): string
    {
        try {
            $z = app(SesliRandevuCozService::class)->zamanCoz($metin);
        } catch (\Throwable $e) {
            return '';
        }
        $parca = [];
        if (!empty($z['tarih'])) $parca[] = "tarih={$z['tarih']} ({$this->tarihSoz($z['tarih'])})";
        if (!empty($z['saat'])) $parca[] = "saat={$z['saat']}";
        if (!empty($z['vakit']) && empty($z['saat'])) $parca[] = "zaman_dilimi={$z['vakit']}";
        if (!$parca) return '';
        return "\n[SİSTEM zaman çözümü — müşteri duymaz: " . implode(', ', $parca) . ']';
    }

    /** 2026-11-18 -> "Çarşamba, 18 Kasım" (yil farkliysa yil da eklenir). */
    protected function tarihSoz(string $iso): string
    {
        $ts = strtotime($iso);
        if (!$ts) return $iso;
        $gunAd = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $ayAd = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $yil = date('Y', $ts) !== now('Europe/Istanbul')->format('Y') ? ' ' . date('Y', $ts) : '';
        return $gunAd[(int) date('w', $ts)] . ', ' . (int) date('j', $ts) . ' ' . $ayAd[(int) date('n', $ts)] . $yil;
    }

    // ------------------------------------------------ isletme bilgisi (calisma saati / kampanya / SSS)
    /** "Pazartesi 09:00-19:00, ..., Pazar kapalı" (salon_calisma_saatleri; haftanin_gunu 1=Pzt..7=Paz) */
    protected function calismaSaatleriMetni(): string
    {
        try {
            $ad = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
            $rows = \App\SalonCalismaSaatleri::where('salon_id', $this->salonId)->get()->keyBy('haftanin_gunu');
            if ($rows->isEmpty()) return 'tanımlı değil';
            $out = [];
            foreach ($ad as $g => $gAd) {
                $r = $rows->get($g);
                if (!$r || (int) $r->calisiyor !== 1) { $out[] = "{$gAd} kapalı"; continue; }
                $b = substr((string) $r->baslangic_saati, 0, 5); $e = substr((string) $r->bitis_saati, 0, 5);
                $out[] = ($b && $e && $b !== $e) ? "{$gAd} {$b}-{$e}" : "{$gAd} açık (saat tanımsız)";
            }
            return implode(', ', $out);
        } catch (\Throwable $e) {
            return 'tanımlı değil';
        }
    }

    protected ?array $_kampanyalar = null;
    /**
     * Aktif kampanyalar (kampanya_yonetimi; bugun baslangic..bitis arasinda) + ARAYANIN durumu:
     * davetli_bekliyor (listede, durum_asistan NULL) | katildi (1) | katilmadi (0) | listede_degil.
     * Fiyat/oran verilmez (fiyat sorusu isletmeye aktarilir).
     */
    protected function kampanyalar(): array
    {
        if ($this->_kampanyalar !== null) return $this->_kampanyalar;
        $out = [];
        try {
            $bugun = now('Europe/Istanbul')->toDateString();
            $q = \App\KampanyaYonetimi::without(['kampanya_katilimcilari', 'salon'])->where('salon_id', $this->salonId)
                ->where(function ($w) use ($bugun) { $w->whereNull('baslangic_tarihi')->orWhereDate('baslangic_tarihi', '<=', $bugun); })
                ->where(function ($w) use ($bugun) { $w->whereNull('bitis_tarihi')->orWhereDate('bitis_tarihi', '>=', $bugun); });
            // Panelde kapatilan (aktifmi=0) kampanya asistanda GORUNMEZ (giden arama komutuyla ayni kural).
            if (\Schema::hasColumn('kampanya_yonetimi', 'aktifmi')) $q->where('aktifmi', 1);
            foreach ($q->orderByDesc('id')->limit(6)->get() as $k) {
                $ad = trim((string) ($k->paket_isim ?: ($k->hizmet_adi ?? '') ?: 'Kampanya'));
                $durum = 'listede_degil'; $katId = null;
                if ($this->musteriId) {
                    $kat = \App\KampanyaKatilimcilari::without('musteri')->where('kampanya_id', $k->id)->where('user_id', $this->musteriId)->first(['id', 'durum_asistan']);
                    if ($kat) {
                        $katId = (int) $kat->id;
                        $durum = $kat->durum_asistan === null ? 'davetli_bekliyor' : ((int) $kat->durum_asistan === 1 ? 'katildi' : 'katilmadi');
                    }
                }
                $out[] = ['kampanya_id' => (int) $k->id, 'ad' => $ad, 'bitis' => $k->bitis_tarihi ? substr((string) $k->bitis_tarihi, 0, 10) : null,
                          'durum' => $durum, 'katilimci_id' => $katId, 'randevu_gerekli' => !empty($k->randevu_olustur)];
            }
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan kampanyalar: ' . $e->getMessage());
        }
        return $this->_kampanyalar = $out;
    }

    protected function kampanyaMetni(): string
    {
        $etiket = ['davetli_bekliyor' => 'müşteri DAVETLİ, henüz cevap vermedi', 'katildi' => 'müşteri katıldı', 'katilmadi' => 'müşteri daha önce katılmayacağını söyledi', 'listede_degil' => 'müşteri listede değil (isterse katılabilir)'];
        $out = '';
        foreach ($this->kampanyalar() as $k) {
            $out .= "\n- kampanya_id {$k['kampanya_id']} | {$k['ad']}" . ($k['bitis'] ? ' | son gün ' . $this->tarihSoz($k['bitis']) : '') . ' | ' . $etiket[$k['durum']];
        }
        return $out ?: ' yok';
    }

    /** Davetli ama cevap vermemis kampanya teklifi (karsilama icin) */
    protected function kampanyaTeklifCumlesi(): string
    {
        foreach ($this->kampanyalar() as $k) {
            if ($k['durum'] === 'davetli_bekliyor') {
                return "{$k['ad']} kampanyamıza davetlisiniz, katılmak ister misiniz?";
            }
        }
        return '';
    }

    /**
     * Kampanyaya katilim talebi: listedeyse isaretle, degilse listeye ekle; sonra mevcut
     * ApiController@kampanyaKatilinacak ile katildi=1 + indirim kodu (WA-first, SMS yedek) gonderilir.
     */
    protected function kampanyaKatil(?int $kampanyaId): array
    {
        if (!$this->musteriId) return ['ok' => false, 'hata' => 'Önce müşteri kaydı gerekli (musteri_kaydet).'];
        $k = null;
        foreach ($this->kampanyalar() as $x) if ($x['kampanya_id'] === $kampanyaId) $k = $x;
        if (!$k) return ['ok' => false, 'hata' => 'Bu kampanya aktif değil ya da bulunamadı. Yalnız BAĞLAM’daki kampanya_id’leri kullan.'];
        if ($k['durum'] === 'katildi') return ['ok' => true, 'zaten_katildi' => true, 'mesaj' => 'Müşteri bu kampanyaya zaten katılmış.'];
        try {
            $katId = $k['katilimci_id'];
            if (!$katId) {
                $kodu = \DB::table('kampanya_yonetimi')->where('id', $k['kampanya_id'])->value('indirim_kodu')
                    ?: \App\KampanyaKatilimcilari::without('musteri')->where('kampanya_id', $k['kampanya_id'])->value('indirim_kodu')
                    ?: substr(str_shuffle('1234567890'), 0, 4);
                $yeni = new \App\KampanyaKatilimcilari();
                $yeni->kampanya_id = $k['kampanya_id'];
                $yeni->user_id = $this->musteriId;
                $yeni->indirim_kodu = $kodu;
                $yeni->save();
                $katId = (int) $yeni->id;
            }
            app(ApiController::class)->kampanyaKatilinacak(Request::create('/api/kampanyaKatilinacak', 'POST', ['katilimci_id' => $katId, 'katilacak' => 1]));
        } catch (\Throwable $e) {
            Log::error('RandevuAsistan kampanyaKatil: ' . $e->getMessage());
            return ['ok' => false, 'hata' => 'Katılım şu an kaydedilemedi. Müşteriye özür dile, yetkiliye aktarmayı teklif et.'];
        }
        $this->_kampanyalar = null;
        Log::info('[SANTRAL-KAMPANYA] katilim', ['salon' => $this->salonId, 'musteri' => $this->musteriId, 'kampanya' => $k['kampanya_id'], 'katilimci' => $katId]);
        return ['ok' => true, 'mesaj' => 'Katılım kaydedildi; indirim kodu ve yol tarifi müşteriye mesajla gönderildi.', 'randevu_gerekli' => $k['randevu_gerekli']];
    }

    /** Isletmenin santral SSS tablosu (panel > Santral > Sık Sorulan Sorular). Yoksa olusturur. */
    public static function sssTablo(): void
    {
        if (!\Schema::hasTable('santral_sss')) {
            \Schema::create('santral_sss', function ($t) {
                $t->increments('id');
                $t->unsignedBigInteger('salon_id');
                $t->boolean('aktif')->default(1);
                $t->text('tetikleyiciler');   // virgulle ayrilmis kelime/ifadeler
                $t->text('cevap');
                $t->timestamp('created_at')->useCurrent();
                $t->index(['salon_id', 'aktif']);
            });
        }
    }

    protected ?array $_sss = null;
    protected function sssKayitlari(): array
    {
        if ($this->_sss !== null) return $this->_sss;
        try {
            self::sssTablo();
            return $this->_sss = \DB::table('santral_sss')->where('salon_id', $this->salonId)->where('aktif', 1)
                ->orderBy('id')->limit(40)->get(['tetikleyiciler', 'cevap'])
                ->map(function ($r) { return ['tet' => (string) $r->tetikleyiciler, 'cevap' => (string) $r->cevap]; })->all();
        } catch (\Throwable $e) {
            return $this->_sss = [];
        }
    }

    /**
     * Hizmet suresi sorusu mu? Evetse hizmeti salon listesinden bulup gercek sureyi soyleyen cumle.
     * Hizmet adi gecmiyorsa ya da soru sure sorusu degilse null (model devam eder).
     */
    protected function sureCevabi(string $metin): ?string
    {
        $m = ' ' . $this->sade($metin) . ' ';
        if (!preg_match('/ne kadar sur|kac dakika|kac saat sur|suresi ne|ne kadar zaman al|ne kadar vakit al/u', $m)) return null;
        $bul = null; $uzun = 0;
        foreach (($this->salon['hizmetler'] ?? []) as $h) {
            $ad = $this->sade((string) ($h['ad'] ?? ''));
            if (mb_strlen($ad) >= 3 && strpos($m, ' ' . $ad) !== false && mb_strlen($ad) > $uzun) { $bul = $h; $uzun = mb_strlen($ad); }
        }
        if (!$bul) return null;
        $sure = (int) ($bul['sure_dk'] ?? 0);
        $ad = trim((string) $bul['ad']);
        return $sure > 0
            ? "{$ad} yaklaşık {$sure} dakika sürüyor."
            : "{$ad} için kesin süre bilgisi bende kayıtlı değil, işletmemiz size net bilgi verebilir.";
    }

    /**
     * Ses tanimanin SALON alaninda sik yanlis duydugu kelimeleri duzelt (kayit #72: "lazer epilasyon" ->
     * "lazer et plakson"; model bu adi musteriye aynen geri okuyordu ve hizmeti bulamayip tekrar sordu).
     */
    public function sttDuzelt(string $m): string
    {
        static $harita = [
            '/\b(e[tp]{1,2} ?p?la[ksyz]{1,3}[iıo]?on|epi ?la[sz]yon|ebi ?lasyon|e ?pilasyon)(\p{L}*)/iu' => 'epilasyon$2',
        ];
        return preg_replace(array_keys($harita), array_values($harita), $m) ?? $m;
    }

    /** Turkce harfleri sadelestirip kucult; noktalama at (STT varyasyonlarina toleransli eslesme). */
    protected function sade(string $s): string
    {
        $s = strtr($s, ['İ' => 'i', 'I' => 'i', 'ı' => 'i', 'Ş' => 's', 'ş' => 's', 'Ğ' => 'g', 'ğ' => 'g', 'Ü' => 'u', 'ü' => 'u', 'Ö' => 'o', 'ö' => 'o', 'Ç' => 'c', 'ç' => 'c']);
        $s = mb_strtolower($s, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^a-z0-9\s]/u', ' ', $s)));
    }

    /**
     * Ses tanima (STT) hizmet adini yanlis duyduysa ("tay makajı", "dayı masajı" -> Tay Masajı) KODLA yakala ve
     * modele gizli not ver: aksi halde model "boyle bir hizmetimiz yok" diyor (2026-11-16 canli cagri).
     * Cumlede bir hizmet adi zaten (ekli haliyle) geciyorsa not uretmez.
     */
    public function hizmetDuzeltmeNotu(string $metin): string
    {
        $adaylar = $this->hizmetAdaylari($metin);
        if (!$adaylar) return '';
        $adlar = array_column(array_slice($adaylar, 0, 2), 'ad');
        Log::info('[SANTRAL-HIZMET-DUZELTME]', ['salon' => $this->salonId, 'metin' => $metin, 'adaylar' => $adaylar]);
        $liste = implode("' veya '", $adlar);
        return "\n[SİSTEM hizmet eşleştirme — müşteri duymaz: Ses tanıma hizmet adını yanlış duymuş olabilir; müşteri büyük ihtimalle '{$liste}' hizmetini kastediyor. Bu hizmet listede VAR: 'yok' deme. Tek aday varsa o hizmetle devam et; emin değilsen '{$adlar[0]} mı demek istediniz?' diye kısaca teyit et.]";
    }

    /**
     * Cumlede yanlis duyulmus olabilecek hizmet adlari: [['id','ad','oran'], ...] (yuksekten dusuge).
     * Bir hizmet adi cumlede zaten (ekli haliyle) geciyorsa ya da benzer yoksa bos dizi.
     */
    protected function hizmetAdaylari(string $metin): array
    {
        $hizmetler = $this->salon['hizmetler'] ?? [];
        $m = $this->sade($metin);
        if (!$hizmetler || $m === '') return [];
        $kelimeler = explode(' ', $m);
        $ara = ' ' . $m . ' ';
        $adaylar = [];
        foreach ($hizmetler as $h) {
            $ad = trim((string) ($h['ad'] ?? ''));
            $sa = $this->sade($ad);
            if ($sa === '') continue;
            if (strpos($ara, ' ' . $sa) !== false) return []; // hizmet adi dogru duyulmus (ek toleransli)
            $hk = explode(' ', $sa);
            // Hizmet adinin ILK en az 2 kelimesi cumlede aynen geciyorsa ("lazer epilasyon" -> "Lazer Epilasyon Tüm Vücut")
            // dogru duyulmustur, sadece hangi alt hizmet oldugu belirsiz: yanlis-duyma notu URETME (kayit #70'te model
            // "Lazer epilasyon hizmetini mi kastettiniz?" diye bosuna teyit soruyordu). Secimi hizmet_eslestir yapar.
            if (count($hk) >= 2 && strpos($ara, ' ' . $hk[0] . ' ' . $hk[1]) !== false) return [];
            $n = count($hk);
            if ($n === 1 && strlen($sa) < 5) continue;   // "kaş", "fön", "masaj" gibi kisa tek kelime -> yanlis pozitif riski
            // Kelime SAYISI tutmasa da (STT bir kelimeyi ikiye bolmus: "epilasyon" -> "et plakson") bosluksuz karsilastir
            $saBitisik = str_replace(' ', '', $sa);
            if (strlen($saBitisik) >= 12) {
                foreach ([$n, $n + 1] as $pn) for ($i = 0; $i + $pn <= count($kelimeler); $i++) {
                    $w = implode('', array_slice($kelimeler, $i, $pn));
                    $oran = 1 - levenshtein($w, $saBitisik) / strlen($saBitisik);
                    if ($oran >= 0.7 && $w !== $saBitisik) {
                        $k = (int) ($h['id'] ?? 0) . '|' . $ad;
                        if (!isset($adaylar[$k]) || $adaylar[$k]['oran'] < $oran) $adaylar[$k] = ['id' => (int) ($h['id'] ?? 0), 'ad' => $ad, 'oran' => round($oran, 3)];
                    }
                }
            }
            $esik = $n === 1 ? 0.85 : 0.75;
            for ($i = 0; $i + $n <= count($kelimeler); $i++) {
                // Kelime kelime karsilastir; Turkce ek toleransi icin uzun kelimeyi hedef uzunluga da kirpip
                // iyi olani al ("masajini"~"masaji", "dayi"~"tay").
                $mesafe = 0;
                foreach ($hk as $j => $hw) {
                    $w = $kelimeler[$i + $j];
                    $d = levenshtein($w, $hw);
                    if (strlen($w) > strlen($hw)) $d = min($d, levenshtein(substr($w, 0, strlen($hw)), $hw));
                    $mesafe += $d;
                }
                $oran = 1 - $mesafe / max(1, strlen(str_replace(' ', '', $sa)));
                if ($oran >= $esik && $mesafe > 0) {
                    $k = (int) ($h['id'] ?? 0) . '|' . $ad;
                    if (!isset($adaylar[$k]) || $adaylar[$k]['oran'] < $oran) $adaylar[$k] = ['id' => (int) ($h['id'] ?? 0), 'ad' => $ad, 'oran' => round($oran, 3)];
                }
            }
        }
        $adaylar = array_values($adaylar);
        usort($adaylar, function ($a, $b) { return $b['oran'] <=> $a['oran']; });
        return $adaylar;
    }

    /**
     * "Hangi hizmetleriniz var / hangi işlemleri yapıyorsunuz / neler yapıyorsunuz" sorusuna KOD cevabi:
     * farkli kategorilerden en fazla 5 ornek hizmet, sonra geri kalan hizmetlerin BENZERSIZ kategorileri.
     * "Hangi masajlarınız var" gibi bir kategori soruluyorsa o kategoriden 5 ornek + toplam sayi.
     * Soru degilse ya da cumlede belirli bir hizmet adi geciyorsa null (normal akis).
     */
    public function hizmetListesiCevabi(string $metin): ?string
    {
        $hizmetler = array_values(array_filter($this->salon['hizmetler'] ?? [], function ($h) { return trim((string) ($h['ad'] ?? '')) !== ''; }));
        if (!$hizmetler) return null;
        $m = ' ' . $this->sade($metin) . ' ';
        $soru = preg_match('/ (hangi|ne gibi|neler|nelerdir|ne tur|ne cesit) .*(hizmet|islem)/u', $m)
            || preg_match('/ (hizmet|islem)\w* .*(neler|nelerdir|ne var|ne yapiyor)/u', $m)
            || preg_match('/ neler yapiyorsunuz | ne yapiyorsunuz /u', $m)
            || preg_match('/ hangi \w+(lar|ler)\w* (var|mevcut)/u', $m);   // "hangi masajlariniz var"
        if (!$soru) return null;
        // KISI sorusu hizmet listesi DEGIL: "hangi uzmanlarınız var" son kaliba ("hangi X-lar var") uyup hizmet
        // listesi okutuyordu (2026-11-17 kayit: "protez tırnakta hangi uzmanlarınız var" -> "cilt bakımı, makyaj...").
        if (preg_match('/ (uzman|personel|calisan|eleman|usta|kuafor|estetisyen|doktor|hoca|kimler )/u', $m)) return null;
        // Belirli bir hizmet adi geciyorsa ("kalıcı oje hizmetiniz var mı") liste degil -> normal akis
        foreach ($hizmetler as $h) {
            $sa = $this->sade((string) $h['ad']);
            if ($sa !== '' && mb_strlen($sa) >= 4 && strpos($m, ' ' . $sa) !== false) return null;
        }
        if ($this->hizmetAdaylari($metin)) return null;

        $kucuk = function ($s) { return mb_strtolower(strtr(trim((string) $s), ['I' => 'ı', 'İ' => 'i']), 'UTF-8'); };
        $listele = function (array $adlar) {
            $adlar = array_values($adlar);
            if (count($adlar) <= 1) return implode('', $adlar);
            $son = array_pop($adlar);
            return implode(', ', $adlar) . ' ve ' . $son;
        };
        // Kategori gruplari (hizmet sayisina gore buyukten kucuge); kategorisi olmayanlar '' grubunda
        $gruplar = [];
        foreach ($hizmetler as $h) $gruplar[trim((string) ($h['kategori'] ?? ''))][] = trim((string) $h['ad']);
        // Siralama: en cok randevu alinan hizmet once (son 180 gun); veri yoksa kisa (genel) ad once.
        $pop = $this->hizmetPopulerlik();
        foreach ($gruplar as &$g) usort($g, function ($a, $b) use ($pop) { return ($pop[$b] ?? 0) <=> ($pop[$a] ?? 0) ?: (mb_strlen($a) <=> mb_strlen($b)) ?: strcmp($a, $b); });
        unset($g);
        $katPop = function ($adlar) use ($pop) { $t = 0; foreach ($adlar as $a) $t += $pop[$a] ?? 0; return $t; };
        uksort($gruplar, function ($a, $b) use ($gruplar, $katPop) { return $katPop($gruplar[$b]) <=> $katPop($gruplar[$a]) ?: count($gruplar[$b]) <=> count($gruplar[$a]) ?: strcmp($a, $b); });

        // Kategori soruldu mu? ("masajlarınız" -> "Masaj"/"Masaj Hizmetleri")
        foreach ($gruplar as $kat => $adlar) {
            if ($kat === '') continue;
            $kk = explode(' ', $this->sade($kat))[0] ?? '';
            if (strlen($kk) >= 4 && strpos($m, ' ' . $kk) !== false) {
                $ornek = array_slice($adlar, 0, 5);
                $c = $listele($ornek) . (count($adlar) > 1 ? ' gibi' : '') . ' ' . $kucuk($kat) . ' hizmetlerimiz var.';
                if (count($adlar) > 5) $c .= ' Bu alanda toplam ' . count($adlar) . ' çeşit hizmetimiz bulunuyor.';
                return $c . ' Hangisiyle ilgileniyorsunuz?';
            }
        }

        // 5 ornek: once her kategoriden bir tane (cesitlilik), eksik kalirsa siradaki hizmetler
        $ornek = [];
        foreach ($gruplar as $adlar) { if (count($ornek) >= 5) break; $ornek[] = $adlar[0]; }
        if (count($ornek) < 5) {
            foreach ($gruplar as $adlar) foreach ($adlar as $a) { if (count($ornek) >= 5) break 2; if (!in_array($a, $ornek, true)) $ornek[] = $a; }
        }
        // Orneklerde HIC gecmeyen kategoriler (en fazla 4). Ornegi okunan kategori tekrar sayilmaz: eskiden
        // "lazer epilasyon, saç kesimi, cilt bakımı gibi... Ayrıca lazer epilasyon, saç, cilt bakımı... alanlarında"
        // diye 16 sn tekrar okunuyordu (2026-11-17 kayit).
        $kalanKat = [];
        foreach ($gruplar as $kat => $adlar) {
            if ($kat === '' || count($kalanKat) >= 4) continue;
            if (!array_intersect($adlar, $ornek)) $kalanKat[] = $kucuk($kat);
        }
        $c = $listele($ornek) . ' gibi hizmetlerimiz var.';
        if ($kalanKat) $c .= ' Ayrıca ' . $listele($kalanKat) . (count($kalanKat) > 1 ? ' alanlarında' : ' alanında') . ' başka seçeneklerimiz de var.';
        elseif (count($hizmetler) > count($ornek)) $c .= ' Bunların dışında da birçok hizmetimiz var.';
        return $c . ' Hangisiyle ilgileniyorsunuz?';
    }

    /** Hizmet adi => son 180 gundeki randevu sayisi (salon basina 12 saat onbellek). Hata/veri yoksa bos. */
    protected function hizmetPopulerlik(): array
    {
        try {
            $salonId = (int) $this->salonId;
            return \Cache::remember('santral_hizmet_pop_' . $salonId, 720, function () use ($salonId) {
                return \DB::table('randevu_hizmetler as rh')
                    ->join('randevular as r', 'r.id', '=', 'rh.randevu_id')
                    ->join('hizmetler as h', 'h.id', '=', 'rh.hizmet_id')
                    ->where('r.salon_id', $salonId)
                    ->where('r.tarih', '>=', date('Y-m-d', strtotime('-180 days')))
                    ->groupBy('h.hizmet_adi')
                    ->pluck(\DB::raw('COUNT(*) as n'), 'h.hizmet_adi')
                    ->map(function ($v) { return (int) $v; })->all();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** STT ipucu: salonun hizmet adlari (Google speechContexts; kopru baslatta alir). */
    public function sttIpuclari(): array
    {
        $out = [];
        foreach (($this->salon['hizmetler'] ?? []) as $h) {
            $ad = trim((string) ($h['ad'] ?? ''));
            if ($ad !== '' && mb_strlen($ad) <= 100) $out[$ad] = true;
            if (count($out) >= 400) break;
        }
        return array_keys($out);
    }

    /** Musterinin cumlesinde bir SSS tetikleyicisi geciyorsa isletmenin cevabi; yoksa null. */
    protected function sssCevap(string $metin): ?string
    {
        $m = ' ' . $this->sade($metin) . ' ';
        foreach ($this->sssKayitlari() as $r) {
            foreach (explode(',', $r['tet']) as $t) {
                $t = $this->sade($t);
                if (mb_strlen($t) >= 3 && strpos($m, ' ' . $t) !== false) return $r['cevap']; // kelime basindan eslesme (ek toleransli)
            }
        }
        return null;
    }

    /** Randevunun konusmada okunacak hali: "Pazartesi, 16 Kasım saat 14:00'te Gülşah ile Saç Kesimi" */
    protected function randevuSoz(array $r): string
    {
        $s = "{$r['gun']} saat " . $this->saatEkli((string) $r['saat']);
        if (!empty($r['personel'])) $s .= " {$r['personel']} ile";
        if (!empty($r['hizmet'])) $s .= " {$r['hizmet']}";
        return $s;
    }

    /** Musteri iptal/degistirme istiyorsa randevularini dogrudan soyleyen cumle; uygun degilse null (LLM devam eder). */
    protected function iptalGuncelleGiris(string $metin, array $gecmis): ?string
    {
        if (!$this->musteriId) return null;
        $f = mb_strtolower(strtr($metin, ['İ' => 'i', 'I' => 'ı']), 'UTF-8'); // Turkce I/İ (mb "İ"->"i̇" yapar)
        $iptal = (bool) preg_match('/iptal|vazgeç|gelemeyece|gelemiyorum|sil(in|ebilir|er misiniz)/u', $f);
        $degis = !$iptal && preg_match('/değiştir|ertele|güncelle|kaydır|başka (bir )?(güne|saate|zamana)|saatini değiş|gününü değiş/u', $f)
            && preg_match('/randevu/u', $f);
        if (!$iptal && !$degis) return null;
        // Liste bu konusmada zaten okunduysa (asistan randevuyu sordu) model devam etsin
        $son = '';
        for ($i = count($gecmis) - 1; $i >= 0; $i--) {
            if (($gecmis[$i]['role'] ?? '') === 'assistant') { $son = (string) ($gecmis[$i]['content'] ?? ''); break; }
        }
        if (preg_match('/randevunuz var|randevunuz bulunuyor|hangisini/iu', $son)) return null;
        // Mesajda tarih/saat de verildiyse ("çarşamba 14'teki randevumu iptal et") modelin eslestirmesine birak
        $z = app(SesliRandevuCozService::class)->zamanCoz($metin);
        if (!empty($z['tarih']) || !empty($z['saat'])) return null;

        $liste = $this->randevularim()['randevular'] ?? [];
        if (!$liste) {
            return 'Adınıza kayıtlı ileri tarihli bir randevu görünmüyor. Başka yardımcı olabileceğim bir konu var mı?';
        }
        if (count($liste) === 1) {
            $r = $liste[0];
            return $iptal
                ? $this->randevuSoz($r) . ' randevunuz var. Bu randevuyu iptal etmemi onaylıyor musunuz?'
                : $this->randevuSoz($r) . ' randevunuz var. Hangi gün ve saate almak istersiniz?';
        }
        $parca = [];
        // "1. Çarşamba..., 2. Perşembe..." yerine "Birinci randevunuz Çarşamba..." (canli 2026-11-18 isletme istegi:
        // sira numarasi tek basina okununca anlasilmiyordu). Her biri ayri cumle: seslendirme ilk cumleyle hemen baslar.
        $sira = ['Birinci', 'İkinci', 'Üçüncü', 'Dördüncü'];
        foreach (array_slice($liste, 0, 4) as $i => $r) $parca[] = $sira[$i] . ' randevunuz ' . $this->randevuSoz($r) . '.';
        return count($liste) . ' randevunuz var. ' . implode(' ', $parca) . ' Hangisini ' . ($iptal ? 'iptal edelim?' : 'değiştirelim?');
    }

    /** "Randevunuz kaydedildi/oluşturuldu" -> "Randevu talebiniz oluşturuldu" (buyuk/kucuk harf korunur). */
    protected function talepIfadesi(string $c): string
    {
        return preg_replace_callback('/\b(r|R)andevunuz\s+(başarıyla\s+)?(kaydedildi|oluşturuldu|alındı)/u', function ($m) {
            return ($m[1] === 'R' ? 'R' : 'r') . 'andevu talebiniz oluşturuldu';
        }, $c);
    }

    /** tool_use bloklarinda bos/dizi input'u JSON nesnesine cevir ({} olarak gitsin). */
    protected function aracGirdisiNesne(array $content): array
    {
        foreach ($content as $i => $b) {
            if (($b['type'] ?? '') === 'tool_use') {
                $in = $b['input'] ?? [];
                $content[$i]['input'] = (is_array($in) && $in !== []) ? $in : new \stdClass();
            }
        }
        return $content;
    }

    /** "Bir moment", "biraz bekleteceğim", "kontrol ediyorum" gibi kisa bekletme cumlelerini cevaptan atar. */
    protected function bekletmeTemizle(string $c): string
    {
        $re = '/(\b(bir|1)\s*(moment|dakika|saniye|dk)\b|beklet|lütfen bekle|biraz bekle|hatta kalın.*kontrol|kontrol ediyorum|kontrol edeyim|hemen bakıyorum|bakıyorum|bakayım|sorguluyorum)/iu';
        $parcalar = preg_split('/(?<=[.!?…])\s+/u', trim($c)) ?: [];
        $kalan = [];
        foreach ($parcalar as $p) {
            if (mb_strlen($p) <= 70 && preg_match($re, $p)) continue; // kisa bekletme cumlesi
            $kalan[] = $p;
        }
        return trim(implode(' ', $kalan));
    }

    /**
     * Modelin IC SESI musteriye okunmasin (canli 2026-11-17: "Müşteri 13.40 dedi; seçeneklerde 13:40 var, tarih ise
     * önceki konuşmadaki Çarşamba... olarak kalmalı. Onay soruyorum. Çarşamba..." telefonda okundu). Musteriden
     * ucuncu sahis diye bahseden / kendi adimini anlatan / arac-sistem adi gecen cumleler atilir.
     */
    protected function icSesTemizle(string $c): string
    {
        $re = '/^müşteri(nin|ye|yi|den)?\s|(onay|teyit)\s+(soruyorum|istiyorum)|söylemiştim|önceki konuşma|olarak kalmal|seçeneklerde\s|araç sonu|\[SİSTEM|BAĞLAM|\b[a-z]+_[a-z_]+\b/iu';
        $parcalar = preg_split('/(?<=[.!?…])\s+/u', trim($c)) ?: [];
        $kalan = [];
        foreach ($parcalar as $p) {
            if (preg_match($re, $p)) continue;
            $kalan[] = $p;
        }
        $temiz = trim(implode(' ', $kalan));
        if ($temiz !== trim($c)) Log::warning('[SANTRAL-IC-SES] modelin ic sesi atildi', ['salon' => $this->salonId, 'ham' => $c, 'temiz' => $temiz]);
        return $temiz;
    }

    /**
     * Cumle basindaki "Peki." ve "Ferdi Bey," atilir (canli 2026-11-18 kayit 166): her tur "Ferdi Bey, hangi..." diye
     * baslamak robotik, tek basina "Peki." havada kaliyordu. Hitap tesekkur/veda cumlesinde kalir ("Rica ederiz Ferdi Bey").
     */
    protected function dogalBaslangic(string $c): string
    {
        $c = trim($c);
        $once = $c;
        $c = preg_replace('/^peki\s*[,.!]?\s+(?=\S)/iu', '', $c);
        $h = $this->hitap();
        if ($h && !preg_match('/rica|teşekkür|iyi günler|iyi akşamlar|hoşça|görüşmek üzere/iu', $c)) {
            $c = preg_replace('/^' . preg_quote($h, '/') . '\s*[,.!]?\s+(?=\S)/u', '', $c);
        }
        if ($c === $once) return $c;
        $ilk = mb_substr($c, 0, 1, 'UTF-8');
        return ($ilk === 'i' ? 'İ' : mb_strtoupper($ilk, 'UTF-8')) . mb_substr($c, 1, null, 'UTF-8');
    }

    /** Fiyat/ucret sorusu mu? ("saç kesimi ne kadar", "fiyatı nedir", "kaç lira") -> isletmeye aktarilir. */
    protected function fiyatSorusuMu(string $m): bool
    {
        $f = mb_strtolower(strtr($m, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        if (preg_match('/ne kadar (sür|zaman|vakit|bekle|erken|önce)/u', $f)) return false; // sure sorusu, fiyat degil
        if (preg_match('/seans|ne kadar (hak|kald)/u', $f) && !preg_match('/fiyat|ücret|lira|tl\b|para/u', $f)) return false; // "paketimde ne kadar seans kaldı"
        return (bool) preg_match('/fiyat|ücret|kaç (para|lira|tl|tele)|ne kadar(\s|$|\?)|kaça (yapıyorsunuz|oluyor|olur|yapılıyor)|taksit/u', $f);
    }

    /** Musteri acikca bir insana/isletmeye baglanmak istiyor mu? */
    protected function aktarmaIstegiMi(string $m): bool
    {
        return (bool) preg_match('/(bağla(r|rmısın|yın|yınız|yabilir|yar mısın|sana)|bağlanmak|bağlanabilir|aktar(ır|ırmısın|ın|ınız|abilir)\s*(mı|mi|mısın|mısınız)?\s*$|operatör|yetkili(ye|yle|lerle)?\s+(bağla|görüş|konuş|ver)|canlı destek|müşteri temsilci|biriyle\s+(görüş|konuş)|insanla\s+(görüş|konuş)|gerçek\s+(biri|bir kişi|insan))/iu', $m);
    }

    // ---------------------------------------------------------------- tools
    /** @return array{0:array,1:?string,2:?int,3:bool} [sonuc, aksiyon, randevuId, bitir] */
    protected function toolCalistir(string $ad, array $in): array
    {
        switch ($ad) {
            case 'hizmet_eslestir':
                $d = $this->ctrlCagir('hizmetEslestir', [
                    'salon_id' => $this->salonId, 'metin' => (string) ($in['metin'] ?? ''),
                ]);
                // Kelime eslesmesi bulamadiysa ses tanima hatasi olabilir ("tay makajı" -> Tay Masajı): benzerlikle dene.
                if (($d['eslesme'] ?? '') === 'yok' && ($ad = $this->hizmetAdaylari((string) ($in['metin'] ?? '')))) {
                    Log::info('[SANTRAL-HIZMET-DUZELTME] hizmet_eslestir', ['salon' => $this->salonId, 'metin' => $in['metin'] ?? '', 'adaylar' => $ad]);
                    $d = (count($ad) === 1 || $ad[0]['oran'] - $ad[1]['oran'] >= 0.05)
                        ? ['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => $ad[0]['id'], 'ad' => $ad[0]['ad']],
                           'not' => 'Ses tanıma kelimeyi yanlış duymuş olabilir; en yakın hizmet bu. Müşteriye TEYİT SORMA ("... için mi?" DEME): hizmeti doğru adıyla kullanarak doğrudan sonraki adıma geç.']
                        : ['ok' => true, 'eslesme' => 'coklu', 'ortak' => (string) ($in['metin'] ?? ''),
                           'secenekler' => array_map(function ($x) { return ['id' => $x['id'], 'ad' => $x['ad']]; }, array_slice($ad, 0, 2))];
                }
                // Ayni ADLI birden fazla hizmet kaydi (salonda mukerrer "Saç Kesimi"): musteriye secenek gibi sunma,
                // tek hizmet say (Haiku 5.5 "iki secenek var ama ikisi de ayni" deyip randevuyu kuramiyordu).
                if (($d['eslesme'] ?? '') === 'coklu' && !empty($d['secenekler'])) {
                    $tekil = [];
                    foreach ($d['secenekler'] as $sc) { $k = $this->sade((string) ($sc['ad'] ?? '')); if (!isset($tekil[$k])) $tekil[$k] = $sc; }
                    if (count($tekil) === 1) $d = ['ok' => true, 'eslesme' => 'tek', 'hizmet' => array_values($tekil)[0]];
                    else $d['secenekler'] = array_values($tekil);
                }
                // Personel tercihi sorusu icin: bu hizmeti veren (takvimi acik) personelin adlari
                if (($d['eslesme'] ?? '') === 'tek' && !empty($d['hizmet']['id'])) {
                    $plist = $this->personelListesi();
                    // personel_id ADIN YANINDA (canli 2026-11-17 kayit 106: "Gülşah Hanım olsun" -> model yanlis id verdi,
                    // "Gülşah Hanım bu hizmet için uygun görünmüyor" dedi; addan id'yi PERSONELLER listesinden kendisi buluyordu)
                    $adlar = array_values(array_filter(array_map(function ($pid) use ($plist) {
                        return isset($plist[$pid]) ? ['ad' => $this->personelHitap((int) $pid), 'personel_id' => (int) $pid] : null;
                    }, $this->hizmetPersonelleri((int) $d['hizmet']['id']))));
                    // Telefonda en fazla 4 isim (2026-11-17 kayit: 12 isim + "G, F, F, F, F" gibi test kaydi 10 sn okundu).
                    // Okunamayan (sesli harfsiz/tek harf) adlar sayilmaz; musteri listede olmayan birini sorarsa PERSONELLER'den eslesir.
                    $okunur = array_values(array_filter($adlar, function ($a) { return mb_strlen($a['ad']) >= 2 && preg_match('/[aeıioöuüAEIİOÖUÜ]/u', $a['ad']); }));
                    $d['personeller'] = array_slice($okunur, 0, 4);
                    if (count($okunur) > 4) $d['personel_sayisi'] = count($okunur);
                    // Bu hizmet musterinin paket(ler)inde -> asistan paketleri + kalan seanslari sayip SORAR (PAKET AKIŞI)
                    if ($pl = $this->paketlerHizmet((int) $d['hizmet']['id'])) {
                        $d['paketler'] = $pl;
                        $d['paket_talimat'] = count($pl) === 1
                            ? 'Bu hizmet müşterinin 1 paketinde var: paketi ve kalan seansını söyle, "bu paketten randevu oluşturmak ister misiniz?" diye SOR.'
                            : 'Bu hizmet müşterinin ' . count($pl) . ' paketinde var: her paketi kalan seansıyla say, hangi paketten randevu oluşturmak istediğini SOR.';
                    }
                }
                return [$d, null, null, false];

            case 'musait_saatler':
                return [$this->musaitSaatlerPersonelli($in), null, null, false];

            case 'mevcut_randevular':
                return [$this->randevularim(), null, null, false];

            case 'musteri_kaydet':
                return [$this->musteriKaydet((string) ($in['ad_soyad'] ?? ''), (string) ($in['telefon'] ?? '')), null, null, false];

            case 'randevu_olustur':
                $d = $this->randevuOlusturPersonelli($in);
                $this->_randevularim = null;
                $ok = !empty($d['ok']);
                return [$d, $ok ? 'randevu' : null, $ok ? ($d['randevu_id'] ?? null) : null, false];

            case 'randevu_iptal':
                $rid = $this->intId($in['randevu_id'] ?? null);
                // SAHIPLIK: yalniz ARAYANIN kendi randevusu (model yanlis/uydurma id verirse baskasininki iptal olmasin)
                if (!$this->randevuSahibiMi($rid)) return [$this->sahipDegilHata(), null, null, false];
                $d = $this->randevuIptalEt($rid); $this->_randevularim = null;
                return [$d, !empty($d['ok']) ? 'iptal' : null, null, false];

            case 'randevu_guncelle':
                $d = $this->randevuGuncellePersonelli($in); $this->_randevularim = null;
                return [$d, !empty($d['ok']) ? 'guncelle' : null, null, false];

            case 'kampanya_katil':
                return [$this->kampanyaKatil($this->intId($in['kampanya_id'] ?? null)), null, null, false];

            case 'yol_tarifi_gonder':
                return [$this->yolTarifiGonder(), null, null, false];

            case 'aktar':
                return [['ok' => true, 'mesaj' => 'operatöre aktarılıyor'], 'aktar', null, false];

            case 'veda':
                return [['ok' => true], 'veda', null, true];

            default:
                return [['ok' => false, 'hata' => 'bilinmeyen arac: ' . $ad], null, null, false];
        }
    }

    protected function toolTanimlari(): array
    {
        $bos = new \stdClass();
        return [
            [
                'name' => 'hizmet_eslestir',
                'description' => 'Müşterinin söylediği hizmet ifadesini sistemde eşleştirir. Hizmeti ASLA kendin tahmin etme; müşteri bir hizmet söyler söylemez bunu çağır. Dönüş eslesme=tek (netleşti), coklu (seçenek sun), yok (öneri sun).',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'metin' => ['type' => 'string', 'description' => 'Müşterinin hizmet için söylediği ifade (aynen)'],
                ], 'required' => ['metin']],
            ],
            [
                'name' => 'musait_saatler',
                'description' => 'Personelin çalışma saati ve dolu randevularına göre boş saat bulur (en fazla 2 seçenek döner; her seçenekte tarih, saat, personel_id, personel). Müşteri bir personel söylediyse personel_id MUTLAKA ver; söylemediyse verme (hizmeti veren uygun personel otomatik seçilir). İstenen gün doluysa en yakın uygun gün döner (istenen_gun_dolu=true).',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'hizmet_id' => ['type' => 'integer', 'description' => 'hizmet_eslestir’den gelen id (ZORUNLU)'],
                    'tarih' => ['type' => 'string', 'description' => 'YYYY-MM-DD (TARİH HARİTASI tablosundan al)'],
                    'saat' => ['type' => 'string', 'description' => 'opsiyonel: müşterinin istediği saat HH:MM (bu saatten itibaren aranır)'],
                    'zaman_dilimi' => ['type' => 'string', 'enum' => ['sabah', 'ogle', 'ogleden_sonra', 'aksam'], 'description' => 'opsiyonel gün dilimi'],
                    'personel_id' => ['type' => 'integer', 'description' => 'opsiyonel: PERSONELLER listesindeki id (sadece müşteri personel söylediyse)'],
                ], 'required' => ['hizmet_id', 'tarih']],
            ],
            [
                'name' => 'mevcut_randevular',
                'description' => 'Arayan müşterinin gelecekteki randevularını listeler (id, tarih, saat, hizmet, personel, hizmet_id, personel_id). İptal/güncelleme için önce bunu çağır.',
                'input_schema' => ['type' => 'object', 'properties' => $bos],
            ],
            [
                'name' => 'yol_tarifi_gonder',
                'description' => 'Müşteri adres, konum ya da yol tarifi sorduğunda HEMEN çağır: işletmenin yol tarifini müşterinin telefonuna mesaj (WhatsApp/SMS) olarak gönderir. ok=true ise "Yol tarifini size mesaj olarak ilettim. Başka yardımcı olabileceğim bir konu var mı?" de; ok=false ise adresi söyle. Bunun için AKTARMA.',
                'input_schema' => ['type' => 'object', 'properties' => $bos],
            ],
            [
                'name' => 'musteri_kaydet',
                'description' => 'Kayıtlı OLMAYAN arayanı işletmenin normal müşteri kaydıyla kaydeder (müşteri kartı + uygulama şifresi mesajı). Müşteri adını ve soyadını söyler söylemez çağır. Kayıtlı müşteride ÇAĞIRMA.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'ad_soyad' => ['type' => 'string', 'description' => 'Yalnız ad ve soyad, söylendiği gibi ("benim adım" gibi ekler olmadan). Örn: "Ayşe Yılmaz"'],
                    'telefon' => ['type' => 'string', 'description' => 'SADECE arayan numarası bilinmiyorsa: müşterinin söylediği cep numarası (rakamlarla, örn 05321234567)'],
                ], 'required' => ['ad_soyad']],
            ],
            [
                'name' => 'randevu_olustur',
                'description' => 'Yeni randevu oluşturur. SADECE müşteri açıkça onayladıktan sonra çağır (onay adımını atlama). Müşteri kayıtlı değilse önce musteri_kaydet. tarih_saat ve personel_id, musait_saatler’in döndüğü seçenekten AYNEN alınır.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'tarih_saat' => ['type' => 'string', 'description' => 'ISO 8601, örn 2026-05-07T14:30:00'],
                    'hizmet_id' => ['type' => 'integer', 'description' => 'hizmet_eslestir’den gelen id'],
                    'personel_id' => ['type' => 'integer', 'description' => 'seçilen seçeneğin personel_id’si'],
                    'adisyon_paket_id' => ['type' => 'integer', 'description' => 'Müşteri randevunun bir PAKETİNDEN olmasını kabul ettiyse o paketin adisyon_paket_id’si (PAKETLER / hizmet_eslestir.paketler).'],
                    'paketsiz' => ['type' => 'boolean', 'description' => 'Müşteri paketinden KULLANMAK İSTEMEDİYSE true (seans paketten düşmez).'],
                    'notlar' => ['type' => 'string'],
                ], 'required' => ['tarih_saat', 'hizmet_id', 'personel_id']],
            ],
            [
                'name' => 'randevu_iptal',
                'description' => 'Randevu iptal eder (talep/onay bekleyen ya da onaylı). Önce mevcut_randevular ile id öğren ve müşteriye onaylat.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'randevu_id' => ['type' => 'integer'],
                ], 'required' => ['randevu_id']],
            ],
            [
                'name' => 'randevu_guncelle',
                'description' => 'Randevu tarih/saatini (ve istenirse uzmanını) değiştirir; hizmet aynı kalır. Önce mevcut_randevular ile randevuyu bul, sonra musait_saatler’i o randevunun hizmet_id’si ile çağırıp yeni saati müşteriye onaylat. Müşteri BAŞKA UZMAN istediyse ve musait_saatler o uzman için uygun dediyse yeni_personel_id MUTLAKA ver (yoksa eski uzmanın takvimine bakılır, "dolu" çıkar).',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'randevu_id' => ['type' => 'integer'],
                    'yeni_tarih_saat' => ['type' => 'string', 'description' => 'ISO 8601'],
                    'yeni_personel_id' => ['type' => 'integer', 'description' => 'Uzman değişiyorsa musait_saatler’in döndürdüğü personel_id. Aynı uzmansa verme.'],
                ], 'required' => ['randevu_id', 'yeni_tarih_saat']],
            ],
            [
                'name' => 'aktar',
                'description' => 'Çağrıyı canlı operatöre aktarır. Sadece: müşteri açıkça operatör/insan/yetkili isterse YA DA randevu dışı konu (şikayet, fatura, ödeme) olursa. Hizmet bulunamadı diye AKTARMA.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'sebep' => ['type' => 'string'],
                ], 'required' => ['sebep']],
            ],
            [
                'name' => 'kampanya_katil',
                'description' => 'Müşteri bir kampanyaya katılmak istediğini söyleyince (davetliyse ya da listede değilse) çağır: katılımı kaydeder ve indirim kodunu mesajla gönderir. kampanya_id BAĞLAM’daki AKTİF KAMPANYALAR listesinden.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'kampanya_id' => ['type' => 'integer'],
                ], 'required' => ['kampanya_id']],
            ],
            [
                'name' => 'veda',
                'description' => 'Müşteri görüşmeyi bitirmek istediğinde (teşekkür/hoşça kal) çağır; çağrı kapanır.',
                'input_schema' => ['type' => 'object', 'properties' => $bos],
            ],
        ];
    }

    // ---------------------------------------------------------------- LLM
    protected function cagir(array $messages, string $key, int $tekrar = 0, array $ek = []): ?array
    {
        $model = (string) (config('services.anthropic.santral_model') ?: 'claude-haiku-5-5');
        $body = [
            'model' => $model,
            'max_tokens' => 1000,
            'system' => [[
                'type' => 'text',
                'text' => $this->sistemPromptu(),
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'tools' => $this->toolTanimlari(),
            'messages' => $messages,
        ] + $ek;
        // Haiku 5.5 (ve yeni nesil): dusunme varsayilan ACIK gelir -> telefonda gecikme. Kapat ve effort'u ayarla.
        // (disabled yalniz low/medium/high effort ile gecerli.) Haiku 4.5'te bu alanlar yok -> gonderme.
        if (strpos($model, 'claude-haiku-4') !== 0) {
            $effort = (string) (config('services.anthropic.santral_effort') ?: 'medium');
            if (!in_array($effort, ['low', 'medium', 'high'], true)) $effort = 'medium';
            $body['thinking'] = ['type' => 'disabled'];
            $body['output_config'] = ['effort' => $effort];
        }

        // GECIKME: ayni istekteki 2.+ cagri ayni TLS baglantisini kullansin (her cagrida yeni el sikisma ~200-400 ms).
        static $ch = null;
        if ($ch === null) $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                'x-api-key: ' . $key,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        ]);
        $t0 = microtime(true);
        $raw = curl_exec($ch);
        $this->llmMs[] = (int) round((microtime(true) - $t0) * 1000);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($raw === false || $code !== 200) {
            $this->teshis = 'http_' . $code . ':' . substr((string) ($raw ?: $err), 0, 160);
            Log::warning('[SANTRAL-LLM-HATA] ' . $this->teshis, ['salon' => $this->salonId, 'deneme' => $tekrar]);
            // 429/5xx/zaman asimi: bir kez daha dene (yoksa turda yalniz on-cumle kalip musteri sessizlikte kaliyor)
            if ($tekrar === 0 && ($raw === false || $code === 0 || $code === 429 || $code >= 500)) {
                usleep(400000);
                return $this->cagir($messages, $key, 1, $ek);
            }
            return null;
        }
        $j = json_decode($raw, true);
        if (is_array($j) && isset($j['usage']) && is_array($j['usage'])) {
            $u = $j['usage'];
            $this->kullanim['girdi'] += (int) ($u['input_tokens'] ?? 0);
            $this->kullanim['cikti'] += (int) ($u['output_tokens'] ?? 0);
            $this->kullanim['cache_okuma'] += (int) ($u['cache_read_input_tokens'] ?? 0);
            $this->kullanim['cache_yazma'] += (int) ($u['cache_creation_input_tokens'] ?? 0);
            $this->kullanim['cagri']++;
        }
        if (is_array($j) && ($j['stop_reason'] ?? '') === 'refusal') {
            // Guvenlik filtresi istegi reddetti (Haiku 5.5'te yeni; sunucu tarafi yedek model yok) -> tur isletmeye aktarilir
            $this->teshis = 'refusal';
            Log::warning('[SANTRAL-LLM-RED] model istegi reddetti', ['salon' => $this->salonId, 'model' => $model ?? '', 'kategori' => $j['stop_details']['category'] ?? null]);
        }
        return is_array($j) ? $j : null;
    }

    protected function sistemPromptu(): string
    {
        $ad = $this->salonAdi();
        $bugun = now('Europe/Istanbul')->format('Y-m-d');
        $harita = $this->gunHaritasi();

        $hizmetSatir = '';
        foreach (array_slice($this->salon['hizmetler'] ?? [], 0, 40) as $h) {
            // Fiyat BILEREK verilmez: fiyat sorulari isletmeye aktarilir (isletme talebi). Sure verilir.
            $sure = (int) ($h['sure_dk'] ?? 0);
            $hizmetSatir .= '- ' . ($h['ad'] ?? '') . ($sure > 0 ? " (yaklaşık {$sure} dk)" : '') . "\n";
        }
        if ($hizmetSatir === '') $hizmetSatir = "(hizmet listesi boş — müşteriyi operatöre aktar)\n";

        $telBilinen = (bool) $this->telNorm($this->telefon);
        if ($this->musteriAd) {
            $musteriSatir = "Arayan bu işletmenin KAYITLI müşterisi: {$this->musteriAd}. Adını/soyadını/telefonunu SORMA; "
                . (($h = $this->hitap())
                    ? "\"{$h}\" hitabını YALNIZ teşekkür/vedada kullan (görüşme ortasında en fazla bir kez, cümle SONUNDA: \"... uygun mu {$h}?\"); cümleye ASLA hitapla BAŞLAMA."
                    : "bu ad için cinsiyet belirsiz: \"Bey\"/\"Hanım\" KULLANMA, isimden cinsiyet TAHMİN ETME; gerekirse \"Sayın {$this->musteriAd}\" de ya da hitapsız konuş.");
        } else {
            $musteriSatir = "Arayan bu işletmede KAYITLI DEĞİL. Karşılamada adı ve soyadı "
                . ($telBilinen ? '' : 've telefon numarası ')
                . "soruldu. KAYIT ADIMI (randevudan ÖNCE, atlanamaz):\n"
                . "- Müşteri ad ve soyadını söyleyince HEMEN musteri_kaydet çağır.\n"
                . "- Sadece ad söylerse soyadını da iste; ad soyad yerine doğrudan isteğini söylerse kibarca önce adını ve soyadını iste.\n"
                . ($telBilinen
                    ? "- Arayan numarası biliniyor, telefon SORMA.\n"
                    : "- Arayan numarası GİZLİ: cep telefonunu da iste, musteri_kaydet'e telefon olarak ver. Numarayı rakam rakam teyit et.\n")
                . "- Kayıt olunca \"Teşekkürler [ad soyad], kaydınızı oluşturdum.\" de; müşteri isteğini zaten söylediyse ona devam et, söylemediyse nasıl yardımcı olabileceğini sor.\n"
                . "- musteri_kaydet ok=false dönerse hatadaki eksik bilgiyi iste. Kayıt tamamlanmadan randevu_olustur ÇAĞIRMA.\n"
                . "- Cinsiyet bilinmiyor: \"Bey\"/\"Hanım\" KULLANMA, isimden cinsiyet TAHMİN ETME; adıyla ya da hitapsız konuş.";
        }
        $telSatir = $telBilinen ? $this->telefon : 'bilinmiyor (gizli numara)';

        $personelSatir = '';
        foreach ($this->personelListesi() as $pid => $pad) {
            $ph = $this->personelHitap((int) $pid);
            $personelSatir .= "- {$ph} (personel_id {$pid}" . ($ph !== $pad ? ", kayıtlı adı {$pad}" : '') . ")\n";
        }
        if ($personelSatir === '') $personelSatir = "(randevu takvimi açık personel yok — randevu verilemez, müşteriyi operatöre aktar)\n";
        $adresSatir = trim((string) ($this->salon['adres'] ?? '')) ?: 'kayıtlı değil';
        $paketSatir = $this->paketSatirlari();
        $rvSatir = '';
        foreach (($this->randevularim()['randevular'] ?? []) as $r) {
            $rvSatir .= "\n- randevu_id {$r['id']} | {$r['gun']} {$r['saat']} | " . ($r['hizmet'] ?: '-') . ' | ' . ($r['personel'] ?: '-')
                . " | hizmet_id {$r['hizmet_id']} | personel_id " . ($r['personel_id'] ?: '-') . " | {$r['durum']}";
        }
        $rvSatir = $rvSatir ?: ' yok';
        $calismaSatir = $this->calismaSaatleriMetni();
        $kampanyaSatir = $this->kampanyaMetni();
        $sssSatir = '';
        foreach ($this->sssKayitlari() as $r) $sssSatir .= "\n- Soru ({$r['tet']}): {$r['cevap']}";
        $sssSatir = $sssSatir ?: ' yok';

        $kimlik = $this->asistanAdi() !== ''
            ? " Adın {$this->asistanAdi()}; kendini bu adla tanıt. \"Robot musun / yapay zeka mısın\" diye sorulursa dürüstçe yapay zeka asistanı olduğunu söyle."
            : '';
        return <<<PROMPT
Sen {$ad} işletmesinin SESLİ RANDEVU asistanısın.{$kimlik} Telefonda, kısa ve doğal Türkçe konuşuyorsun. Çıktın düz metindir (TTS ile okunur): emoji, madde işareti, markdown KULLANMA. Her yanıt 1-2 cümle. Çıktının TAMAMI müşteriye okunur: düşünceni, akıl yürütmeni, ne yapacağını ("Müşteri ... dedi", "Onay soruyorum") ASLA yazma; yalnız müşteriye söyleyeceğin cümleyi yaz.

# KONUŞMA ÜSLUBU (sıcak, insan gibi)
- Güler yüzlü, sıcak ve samimi bir resepsiyonist gibi konuş; resmi anons ya da robot gibi değil. Cümleleri kısa ve akıcı kur.
- Müşterinin cevabını doğal bir onayla karşıla ve HER SEFERİNDE farklı söyle: "Tabii, anladım.", "Çok güzel.", "Harika.", "Anladım.", "Olur, hemen ayarlayalım." Tek başına "Peki." DEME; cümleye "Peki" ile başlama.
- EMPATİ: müşteri bir sorun, ağrı, rahatsızlık, yoğunluk, gecikme, memnuniyetsizlik ya da üzücü bir durum anlatırsa ÖNCE kısaca duygusunu karşıla ("Çok geçmiş olsun.", "Sizi çok iyi anlıyorum.", "Bunu duyduğuma üzüldüm, hemen yardımcı olayım."), SONRA işe geç. Sevindirici bir şey söylerse ("düğünüm var", "doğum günüm") içtenlikle sevin ("Ne güzel, şimdiden tebrikler!"). İptalde "Tabii, hiç sorun değil." gibi rahatlat.
- Aynı bilgiyi iki kez sorma; uzman adını her cümlede tekrarlama.
- Birden fazla randevu sayarken numara okuma ("1. Çarşamba" DEME): "Birinci randevunuz Çarşamba ..., ikinci randevunuz Perşembe ..." diye her birini ayrı cümleyle say.

# AKIŞ (adımları atlama)
1. SELAMLAMA zaten TTS ile çalındı — TEKRAR ETME. İşletme adına "-da/-de/-a/-e/-ye" eki EKLEME. Arayan KAYITLI DEĞİLSE önce BAĞLAM'daki KAYIT ADIMI'nı tamamla; kayıtlıysa doğrudan niyete geç.
2. NİYET: yeni randevu mu, iptal mi, güncelleme mi, başka bir şey mi? (Müşterinin paketi varsa aşağıdaki PAKET AKIŞI'na bak.)
3. HİZMET: müşteri hizmet söyleyince hemen "hizmet_eslestir" aracını çağır (dediğini AYNEN ver). Hizmeti KENDİN tahmin etme. eslesme=tek ise TEYİT SORMADAN ("bunu onaylıyor musunuz" DEME) doğrudan sonraki adıma geç; eşleştirmenin iç işleyişinden (kayıt, seçenek sayısı, "eşleştirebildim") müşteriye ASLA bahsetme; coklu ise seçenekleri kısaca oku ve seçtir; yok ise "o hizmetimiz yok" de ve dönen önerileri sun.
4. TARİH/SAAT: Müşteri mesajının sonunda "[SİSTEM zaman çözümü ...]" notu varsa tarih ve saati AYNEN oradan al (kendin hesaplama; "çarşamba 14", "önümüzdeki hafta perşembe 10", "10 aralık", "5 şubat" hepsi orada çözülmüş gelir). Not yoksa TARİH HARİTASI'nı kullan. Müşteri gün ve saati söylediyse saati TEKRAR SORMA, sabah/öğleden sonra diye SORMA. Tarih/saat ÖNCEKİ bir mesajda söylendiyse (ör. hizmeti teyit ettirdikten sonra) o mesajdaki zaman çözümü geçerlidir: yeniden sorma, kendin hesaplama.
UZMAN ADLARI: Uzmanı her zaman araç sonucundaki / PERSONELLER'deki gibi "Hanım"/"Bey" ile söyle (ör. "uzmanımız Gülşah Hanım", "Ferdi Bey ve Gülşah Hanım uygun"); çıplak ad ("Gülşah ile") KULLANMA.
5. PERSONEL TERCİHİ (hizmet netleşir netleşmez, tarih/saat sormadan ÖNCE): müşteri bir personel adı söylediyse (ör. "Ayşe Hanım'a", "Mehmet Bey olsun") personel_id'yi hizmet_eslestir sonucundaki "personeller" listesinden (ad + personel_id), yoksa PERSONELLER listesinden AYNEN al (id uydurma); listede yoksa "o isimde personelimiz yok" de ve hizmeti verenleri say. Söylemediyse BİR KEZ, uzman adlarını SAYMADAN yalnız şunu sor: "Tercih ettiğiniz bir uzmanımız var mı, yoksa fark etmez mi?" (kayıt 172: kendiliğinden 4 uzmanı saymak 13 sn sürdü). Müşteri "kimler var"/"hangi uzmanlarınız var" derse hizmet_eslestir sonucundaki "personeller" adlarını say (en fazla 4; "personel_sayisi" varsa sonuna "ve diğer uzmanlarımız" ekle) — hizmet listesi OKUMA. "Fark etmez" derse personel_id vermeden devam et ve yalnız "Hangi gün sizin için uygun?" diye sor ("uygun ilk uzmanımızı ayarlayalım" gibi açıklama yapma). hizmet_eslestir sonucunda personeller tek kişiyse sormadan o kişiyle devam et. Müşteri tarih/saati zaten söylediyse personel cevabından sonra TARİHİ YENİDEN SORMA, doğrudan 6. adıma geç.
6. MÜSAİT SAAT: "musait_saatler" aracını hizmet_id + tarih ile çağır (müşteri saat söylediyse saat'i MUTLAKA ver; gün dilimi söylendiyse zaman_dilimi; personel söylendiyse personel_id).
   - tam_eslesme=true dönerse: istenen saat uygun → ALTERNATİF SUNMA, seçenek okuma; doğrudan 8. adımdaki onay cümlesine geç (gün için seçeneğin "gun" alanını kullan).
   - Aksi halde: müşteri saat söylediyse önce "O saat dolu" de, sonra dönen seçenekleri (en fazla 2) "gun" alanı ve personel adıyla öner. istenen_gun_dolu=true ise o günün dolu olduğunu söyle. Seçenek yoksa başka gün/dilim öner.
7. SAAT SEÇİMİ (yalnız seçenek sunulduysa).
8. ONAY (yalnız burada, tek sefer): "[gün] [saat]'te uzmanımız [personel] ile [hizmet] için randevu talebi oluşturayım mı?" ([gün] = seçeneğin "gun" alanı, ör. "Çarşamba, 18 Kasım")
9. Onay gelince "randevu_olustur" (seçilen seçeneğin tarih_saat ve personel_id'si + hizmet_id). ok=false ve saat dolu derse yeniden musait_saatler çağır ve yeni saat öner.
10. SONUÇ: Randevu TALEP olarak oluşturulur (işletme onaylar). Gün/saat/uzmanı TEKRAR SAYMA (onay sorusunda söylendi): "Tamamdır, randevu talebinizi oluşturdum; bilgilendirme mesajını ve yol tarifini telefonunuza gönderdim. Onaylandığında size haber vereceğiz." (bilgilendirme_gonderildi=false ise "; bilgilendirme mesajını ve yol tarifini telefonunuza gönderdim" kısmını SÖYLEME.) En sonda MUTLAKA "Başka yardımcı olabileceğim bir konu var mı?" diye sor. Güncelleme ve iptal sonrasında da aynı şekilde bu soruyu sor; iptalde "Randevunuz iptal edildi." de, randevu_iptal sonucunda bilgilendirme_gonderildi=true ise "Size bilgilendirme mesajı ilettim." ekle (güncellemede de aynı alan).
11. KAPANIŞ: Müşteri "yok, teşekkürler" derse "veda" çağır; yeni bir isteği varsa 2. adımdan devam et.

PAKET AKIŞI (BAĞLAM'da PAKETLER varsa): Karşılamada paketten BAHSEDİLMEDİ. Paketi müşteriye kendiliğinden hatırlatma; müşteri ne istediğini söyler, paketi o an sen bul ve getir.
- Müşteri paketinden bahsederse ("lazer paketim vardı", "paketimden randevu alacağım", "paketimi kullanmak istiyorum") paketi PAKETLER'den bul: hizmet_eslestir ÇAĞIRMA, hizmet_id'yi PAKETLER'den al. Birden fazla paket ya da hizmet uyuyorsa hangisi olduğunu sor.
- Müşteri paketten bahsetmeden bir hizmet söylerse ("lazer randevusu alacaktım") ve hizmet_eslestir sonucunda "paketler" gelirse (ya da [SİSTEM paket] notu varsa) bu hizmet müşterinin paket(ler)indedir: paketi SEN getir ve SOR.
- TEK paket: "[paket adı] paketinizde toplam [toplam] seansın [kullanılan] seansı kullanılmış, [kalan] seansınız kalmış görünüyor. Randevuyu bu paketinizden oluşturmak ister misiniz?" Yalnız kalan varsa yalnız kalanı söyle. Süreli/sınırsız pakette seans sayısı SÖYLEME: "[paket adı] paketiniz var, randevuyu bu paketinizden oluşturmak ister misiniz?"
- BİRDEN FAZLA paket: "Bu hizmet [n] paketinizde var: [paket A]'da [x] seans, [paket B]'de [y] seans kalmış. Hangi paketinizden randevu oluşturalım?" Müşteri seçince o paketin adisyon_paket_id'sini hatırla.
- Evet derse / paket seçerse randevu_olustur'a adisyon_paket_id ver. Paketten istemezse ("hayır, ücretli olsun", "paketimden düşmesin") paketsiz=true ver. Sayıları YALNIZ BAĞLAM'dan/araç sonucundan al, ASLA tahmin etme. Paket sorusunu bir kez sor; cevap geldikten sonra tekrar sorma.
- ANLAŞMAZLIK: Müşteri seans sayısına itiraz ederse ("ben 3 seans kullandım", "yanlış", "daha fazla seansım olmalı", "bu paketim yoktu") ya da paketi bulamazsan veya emin değilsen TARTIŞMA, sayıyı düzeltmeye/değiştirmeye çalışma. Kibarca: "Kayıtlarımızda bu şekilde görünüyor; seans bilginizi en doğru işletmemiz netleştirecektir. Sizi işletmemize bağlayayım mı?" Evet derse "aktar" çağır. Müşteri önce randevusunu almak isterse randevu akışına devam et.
- Hizmet belli olunca o hizmete UYGUN PERSONELİ PAKETLER'deki listeden adlarıyla say ve tercihini sor (ör. "Bu hizmet için Ayşe Hanım ve Zeynep Hanım uygun, tercih ettiğiniz biri var mı?"). Tek kişiyse onu söyle. "Fark etmez" derse personel_id verme. Listede olmayan personeli önerme.
- Sonra normal akış: tarih → musait_saatler (paket hizmet_id + seçilen personel_id) → saat → onay → randevu_olustur. Seans pakete otomatik bağlanır; sonuçta paket_seansi=true ise "paketinizden" de.
- Hayır derse randevuyu paketsiz=true ile normal akışta oluştur; başka bir şey isterse paketi bırak, normal akışa geç.

HİZMET LİSTESİ SORULURSA ("hangi hizmetleriniz var"): listenin tamamını OKUMA. En fazla 5 örnek say (farklı türlerden), "ve daha birçok hizmetimiz var" de, sonra "Hangisiyle ilgileniyorsunuz?" diye sor. Telefonda uzun liste dinlenmez.

BEKLETME YOK: "Bir moment", "bir saniye", "biraz bekleteceğim", "kontrol ediyorum", "bakıyorum" gibi bekletme cümleleri ASLA kurma. Bilgi gerekiyorsa aracı HEMEN çağır ve sonucu doğrudan söyle (ör. "Pazartesi 16 Kasım saat 14:00'te Saç Kesimi randevunuz var, bunu iptal edeyim mi?").

İPTAL/GÜNCELLEME: Talep (onay bekleyen) randevular da onaylılar gibi iptal edilebilir ve güncellenebilir. Randevular BAĞLAM'daki MÜŞTERİNİN MEVCUT RANDEVULARI listesinde: müşteri onaylayınca (evet/olur/onaylıyorum) HEMEN randevu_iptal çağır, başka soru sorma. Liste boşsa ya da emin değilsen "mevcut_randevular" ile randevuyu bul, müşteriye hangisini kastettiğini onaylat. İptalde "randevu_iptal". Güncellemede yeni gün/saat için "musait_saatler"i o randevunun hizmet_id ve personel_id'si ile çağır, onay al, sonra "randevu_guncelle". Müşteri BAŞKA UZMAN isterse musait_saatler'i o uzmanın personel_id'si ile çağır ve randevu_guncelle'ye yeni_personel_id olarak ver; "dolu" dönerse aynı saati tekrar "uygun" diye SÖYLEME. Yalnız mevcut_randevular'ın döndüğü id'leri kullan.

KAMPANYA KATILIMI: Karşılamada kampanya teklif edildiyse ya da müşteri bir kampanyaya katılmak isterse ("katılmak istiyorum", "evet katılırım", "o kampanyadan yararlanmak istiyorum") → onay sorma, HEMEN "kampanya_katil" çağır. ok=true ise "Kampanyaya katılımınızı aldım, indirim kodunuzu mesaj olarak ilettim." de; randevu_gerekli=true ise randevu oluşturmak isteyip istemediğini sor, değilse "Başka yardımcı olabileceğim bir konu var mı?". Müşteri "hayır/istemiyorum" derse ısrar etme, normal akışa geç. Birden fazla kampanya varsa hangisi olduğunu sor.

İŞLETME BİLGİSİ: Çalışma saati ("kaçta açıksınız", "pazar açık mısınız"), kampanya ("kampanyanız var mı") ve işletmenin sık sorulan sorularına BAĞLAM'daki bilgilerle kısa cevap ver, sonra "Başka yardımcı olabileceğim bir konu var mı?" diye sor. BAĞLAM'da olmayan bir bilgi sorulursa UYDURMA: "Bu konuda sizi işletmemize bağlayayım mı?" diye sor; evet derse "aktar".

ADRES / YOL TARİFİ: müşteri yol tarifi, adres, konum, "konum gönderir misiniz", "nasıl gelirim", "neredesiniz" derse İLK SEFERDE, teyit sormadan HEMEN "yol_tarifi_gonder" çağır. Gönderilince yalnızca "Yol tarifini size mesaj olarak ilettim. Başka yardımcı olabileceğim bir konu var mı?" de (adresi okuma). Gönderilemezse (ok=false) adresi kısaca söyle ve aynı soruyu sor. Bunun için aktarma yapma.

İŞ TAMAMLANINCA: Bir istek bittiğinde (randevu oluşturuldu, iptal edildi, güncellendi, yol tarifi gönderildi, sorusu cevaplandı) daima "Başka yardımcı olabileceğim bir konu var mı?" diye sor. Karşılamadan sonra "Size nasıl yardımcı olabilirim?" cümlesini bir daha KULLANMA.

# ONAY KELİMELERİ
evet, tabii, olur, olsun, tamam, ok, okey, kabul, onaylıyorum, uygundur, peki, he, aynen, oldu = ONAY.
hayır, yok, istemiyorum, olmaz, vazgeçtim, başka = RET.

# ZAMAN
Bugün: {$bugun}. Tarihleri müşteriye Türkçe söyle (ör. "Perşembe, 7 Mayıs") ama araçlara ISO 8601 ver.
Saat kestirimi: "on dörtte"=14:00, "on dört buçukta"=14:30, "ikide/saat ikide"=14:00 (öğleden sonra varsayılan), "iki buçukta"=14:30, "sabah dokuz"=09:00, "akşam yedide"=19:00, "öğleden sonra üçte"=15:00. Belirsizse "sabah mı öğleden sonra mı?" diye sor.

# TARİH HARİTASI (HESAP YAPMA, TABLOYU KULLAN)
{$harita}

# AKTARMA (sadece bunlarda "aktar")
FİYAT/ÜCRET/İNDİRİM/KAMPANYA soruları ("ne kadar", "fiyatı nedir", "kaç lira") → fiyat SÖYLEME, "Fiyat bilgisi için sizi işletmemize bağlıyorum" deyip "aktar" çağır. Müşteri açıkça operatör/insan/yetkili/işletmeye bağlanmak isterse ("beni bağlar mısınız", "işletmeye bağlayın", "biriyle görüşmek istiyorum") HİÇ SORU SORMADAN hemen "aktar" çağır — bunu hizmet sanma, hizmet_eslestir ÇAĞIRMA. YA DA randevu/iptal/güncelleme dışı konu (şikayet, fatura, ödeme) olursa. Hizmet bulunamadı diye AKTARMA — sormaya devam et.

# KIRMIZI ÇİZGİLER
Burası restoran DEĞİL, randevu hattı: kişi sayısı, masa, rezervasyon, sipariş SORMA. Her randevu arayan kişi içindir. Anlamsız/alakasız bir ifade duyarsan (ses tanıma hatası olabilir) tahmin yürütme, ne istediğini kısaca tekrar sor.
randevu_olustur ok=true DÖNMEDEN asla "randevunuz oluşturuldu" deme; ok=false ise hatayı müşteriye dürüstçe söyle. Hizmeti kendin tahmin etme (daima hizmet_eslestir). coklu'da kendin seçme. 8. adım onayı almadan randevu_olustur çağırma. Saat ve personeli kendin uydurma (yalnız musait_saatler sonucundan). Tüm saatleri sıralama (2 tane öner). Bilgi uydurma.

# BAĞLAM
İşletme: {$ad}
Adres: {$adresSatir}
Müşteri telefonu: {$telSatir}
{$musteriSatir}
Hizmetler (yalnızca bağlam; eşleştirmeyi SEN yapma, hizmet_eslestir kullan; "ne kadar sürer" sorusuna buradaki süreyle cevap ver, süre yazmıyorsa "kesin süreyi bilmiyorum" de — ASLA tahmini süre uydurma, bunun için aktarma yapma):
{$hizmetSatir}
PERSONELLER (randevu takvimi açık olanlar):
{$personelSatir}
PAKETLER (kullanılabilir): {$paketSatir}
MÜŞTERİNİN MEVCUT RANDEVULARI (iptal/güncellemede bunları kullan; mevcut_randevular çağırmana GEREK YOK):{$rvSatir}
ÇALIŞMA SAATLERİ: {$calismaSatir}
AKTİF KAMPANYALAR (yalnız adını ve son gününü söyle; indirim oranı/fiyat sorulursa aktar):{$kampanyaSatir}
İŞLETMENİN SIK SORULAN SORU CEVAPLARI (benzer sorularda bu cevabı kullan, değiştirme):{$sssSatir}
PROMPT;
    }

    /** Bugün + 14 gün: "GünAdı DD Ay = YYYY-MM-DD" (İstanbul, deterministik). */
    protected function gunHaritasi(): string
    {
        $gunAd = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $ayAd = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $out = '';
        $t = now('Europe/Istanbul')->startOfDay();
        $buPzt = (clone $t)->startOfWeek(); // Pazartesi
        for ($i = 0; $i < 22; $i++) {
            $g = (clone $t)->addDays($i);
            $hafta = intdiv($buPzt->diffInDays($g), 7);
            $hEt = $hafta === 0 ? 'bu hafta' : ($hafta === 1 ? 'önümüzdeki hafta' : "{$hafta} hafta sonra");
            $etiket = ' (' . ($i === 0 ? 'BUGÜN, ' : ($i === 1 ? 'YARIN, ' : '')) . $hEt . ')';
            $out .= $gunAd[(int) $g->format('w')] . ' ' . (int) $g->format('d') . ' ' . $ayAd[(int) $g->format('n')]
                . ' = ' . $g->format('Y-m-d') . $etiket . "\n";
        }
        return $out;
    }

    // ---------------------------------------------------------------- helpers
    protected function salonYukle(): void
    {
        // GECIKME: nesne her konusma TURUNDA yeniden kurulur; salon/hizmet bilgisi 2 dk onbellekte (her turda DB yok)
        $sid = $this->salonId;
        try {
            $d = \Cache::remember('santral_salon_bilgi:' . $sid, 2, function () use ($sid) {
                return $this->ctrlCagir('salonBilgi', ['salon_id' => $sid]);
            });
        } catch (\Throwable $e) {
            $d = $this->ctrlCagir('salonBilgi', ['salon_id' => $sid]);
        }
        $this->salon = [
            'ad' => $d['ad'] ?? ('Salon ' . $this->salonId),
            'karsilama' => $d['karsilama_telaffuz'] ?? '',
            'adres' => $d['adres'] ?? '',
            'hizmetler' => is_array($d['hizmetler'] ?? null) ? $d['hizmetler'] : [],
        ];
    }

    /**
     * Arayan bu salonun AKTIF portfoy musterisi mi? (santralkarsilamametni / eski santral ile AYNI kural.)
     * Yalniz users tablosunda olup bu salonda portfoyu olmayan kisi = kayitsiz -> musteri_kaydet.
     */
    protected function musteriYukle(): void
    {
        $this->musteriId = null; $this->musteriAd = null;
        $tel = $this->telNorm($this->telefon);
        if (!$tel) return;
        try {
            $mp = MusteriPortfoy::where('aktif', 1)->where('salon_id', $this->salonId)
                ->whereHas('users', function ($q) use ($tel) { $q->where('cep_telefon', $tel); })
                ->with('users')->first();
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan musteriYukle: ' . $e->getMessage());
            return;
        }
        if ($mp && $mp->users) {
            $this->musteriId = (int) $mp->users->id;
            $ad = trim((string) $mp->users->name);
            $this->musteriAd = $ad !== '' ? $this->adDuzelt($ad) : null;
            $c = $mp->users->cinsiyet;
            $this->musteriCinsiyet = ($c === 0 || $c === 1 || $c === '0' || $c === '1') ? (int) $c : null;
        }
    }

    /**
     * "Ferdi Bey" / "Ayşe Hanım". Once kayittaki cinsiyet (users.cinsiyet); bos ise (eski kayitlarin cogu)
     * AYNI ADLI ve cinsiyeti girilmis musterilerin istatistigi (adCinsiyeti). Emin degilse null -> hitapsiz
     * (Deniz, Ümit, Derya, Özgür gibi iki cinsiyette kullanilan adlarda yanlis hitap musteriyi kirar).
     */
    /**
     * Acilis hitabi: "Merhaba Anıl Bey" (cinsiyet biliniyorsa). Eskiden "Sayın Anıl Orbey" deniyordu: resmi/anons gibi
     * (2026-11-17 isletme istegi). Cinsiyet belirsizse yanlis Bey/Hanım riski yerine "Sayın Ad Soyad" kalir; ad yoksa "Merhaba".
     */
    /** @var string|null Asistanin adi (null = henuz okunmadi). Testler dogrudan atar. */
    protected $_asistanAdi = null;

    /**
     * Asistanin adi: isletmenin sectigi sese gore (config services.santral_ai.tts_sesler.*.asistan_adi; kadin
     * Chirp3 -> "Ayşe"). Erkek seste ad tanimli degilse '' -> eski "yapay zeka asistanınızım" kalibi.
     */
    public function asistanAdi(): string
    {
        if ($this->_asistanAdi === null) {
            try {
                $ses = SantralPaket::salonSesi((int) $this->salonId);
                $this->_asistanAdi = trim((string) config("services.santral_ai.tts_sesler.{$ses}.asistan_adi", ''));
            } catch (\Throwable $e) {
                $this->_asistanAdi = '';
            }
        }
        return $this->_asistanAdi;
    }

    /** Karsilamadaki tanitim: "Ben Ayşe" (2026-11-18 isletme istegi) ya da "Ben yapay zeka asistanınızım". */
    protected function benKimim(bool $buyuk = true): string
    {
        $c = $this->asistanAdi() !== '' ? 'ben ' . $this->asistanAdi() : 'ben yapay zeka asistanınızım';
        return $buyuk ? 'B' . substr($c, 1) : $c;
    }

    protected function acilisHitap(?string $ad): string
    {
        $ad = trim((string) $ad);
        if ($ad === '') return 'Merhaba';
        $h = ($ad === trim((string) $this->musteriAd)) ? $this->hitap() : null;
        return $h ? "Merhaba {$h}" : "Sayın {$ad}";
    }

    protected function hitap(): ?string
    {
        if (!$this->musteriAd) return null;
        $ilkAd = explode(' ', trim($this->musteriAd))[0];
        if ($ilkAd === '') return null;
        // Sira: kayit -> iki cinsiyetli ad ise DUR -> hazir ad listesi (sorgusuz) -> kendi verimizin istatistigi
        $c = $this->musteriCinsiyet;
        if ($c === null && !TurkceIsimCinsiyet::belirsizMi($ilkAd)) {
            $c = TurkceIsimCinsiyet::bul($ilkAd);
            if ($c === null) {
                // Sozlukte yok: panelde karar icin "bekliyor" listesine + bu arada istatistik. Nesne her TURDA yeniden
                // kurulur -> ayni arayan+ad 2 saatte bir kez sayilsin (gorulme = arama sayisi, tur sayisi degil).
                if (!$this->_isimOgrenildi) {
                    $this->_isimOgrenildi = true;
                    try {
                        if (\Cache::add('santral_isim_ogren_' . md5($this->telefon . '|' . $ilkAd), 1, 120)) TurkceIsimCinsiyet::ogren($ilkAd);
                    } catch (\Throwable $e) {}
                }
                $c = $this->adCinsiyeti($ilkAd);
            }
        }
        return $c === null ? null : $ilkAd . ($c === 1 ? ' Bey' : ' Hanım');
    }

    /**
     * Ad -> cinsiyet (1 Erkek / 0 Kadın) TUM sistemdeki cinsiyeti girilmis kayitlardan: en az 5 kayit ve %95 ayni
     * cinsiyet sartiyla; aksi halde null. Sayim TurkceIsimCinsiyet::istatistik (1 gun onbellek, 400 ms sinir;
     * panel ekrani da ayni sayimi oneri olarak gosterir). Tahmin kayda YAZILMAZ.
     */
    protected function adCinsiyeti(string $ilkAd): ?int
    {
        $s = TurkceIsimCinsiyet::istatistik($ilkAd);
        if (!$s) return null;
        $t = $s['e'] + $s['k'];
        if ($t < 5) return null;
        if ($s['e'] / $t >= 0.95) return 1;
        if ($s['k'] / $t >= 0.95) return 0;
        return null;
    }

    /**
     * Normal musteri kaydi: ApiController@yenimusteridanisankaydi (uygulama/panel ve eski santral ile
     * AYNI yol: users + bu salonun portfoyu + isletmeye ozel sifre WA/SMS + audit). santraldenkayit=1.
     */
    protected function musteriKaydet(string $adSoyad, string $telefonSozlu): array
    {
        if ($this->musteriId) return ['ok' => true, 'mesaj' => 'Müşteri zaten kayıtlı', 'ad_soyad' => $this->musteriAd];

        $adSoyad = $this->adDuzelt(preg_replace('/[^\p{L}\s\'-]/u', ' ', $adSoyad));
        if (count(array_filter(explode(' ', $adSoyad), function ($w) { return mb_strlen($w, 'UTF-8') >= 2; })) < 2) {
            return ['ok' => false, 'hata' => 'Ad ve soyad birlikte gerekli. Müşteriden soyadını da iste.'];
        }

        $tel = $this->telNorm($this->telefon);
        if (!$tel) {
            $tel = $this->telNorm($telefonSozlu);
            if (!$tel) return ['ok' => false, 'hata' => 'Geçerli bir cep telefonu gerekli (05 ile başlayan 11 hane). Müşteriden numarasını iste.'];
        }

        try {
            $req = Request::create('/api/yenimusteridanisankaydi', 'POST', [
                'salonidler' => (string) $this->salonId,
                'name' => $adSoyad,
                'cep_telefon' => $tel,
                'isletmeadi' => $this->salonAdi(),
                'santraldenkayit' => '1',
            ]);
            $resp = app(ApiController::class)->yenimusteridanisankaydi($req);
            $raw = is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp;
        } catch (\Throwable $e) {
            Log::error('RandevuAsistan musteriKaydet: ' . $e->getMessage());
            return ['ok' => false, 'hata' => 'Kayıt şu an oluşturulamadı. Müşteriye özür dileyip yetkiliye aktarmayı teklif et.'];
        }

        // Numara gizliydi ve sozlu alindiysa: bu oturumun telefonu artik o (sonraki turlar + randevu bununla).
        if (!$this->telNorm($this->telefon)) { $this->telefon = '0' . $tel; $this->yeniTelefon = $this->telefon; }

        $this->musteriYukle(); // "exists" (zaten portfoyde) dahil: yeniden tani
        if (!$this->musteriId) {
            Log::warning('RandevuAsistan musteriKaydet: kayit sonrasi musteri bulunamadi', ['salon' => $this->salonId, 'yanit' => mb_substr($raw, 0, 300)]);
            return ['ok' => false, 'hata' => 'Kayıt şu an oluşturulamadı. Müşteriye özür dileyip yetkiliye aktarmayı teklif et.'];
        }
        return ['ok' => true, 'mesaj' => 'Müşteri kaydı oluşturuldu', 'ad_soyad' => $this->musteriAd];
    }

    // ------------------------------------------------ personel / musaitlik / randevu
    // Eski santral (randevu_ai) ve uygulama ile AYNI kaynaklar: SesliRandevuCozService::musaitlikBul
    // (personelin calisma saati + dolu araliklari) ve ApiController@randevuekleguncelle (panel/app yolu).

    // ------------------------------------------------ paket
    /**
     * Musterinin KULLANILABILIR paketleri. Secim mantigi ApiController@randevuekleguncelle'deki
     * otomatik seans baglama ile AYNI (adisyon_paketler.seans_sayisi > 0, otomatik_randevu_olusturuldu != 1,
     * hizmet bazinda acilmis seans < seans_sayisi, gecerlilik suresi dolmamis; sinirsiz paket = sure/limit uygunsa).
     * Boylece paket hizmetiyle randevu_olustur cagrilinca seans pakete KENDILIGINDEN baglanir.
     * @return array<int, array{adisyon_paket_id:int, paket_adi:string, hizmetler:array}>
     */
    protected function paketler(): array
    {
        if ($this->_paketler !== null) return $this->_paketler;
        $out = [];
        if (!$this->musteriId) return $this->_paketler = [];
        try {
            $sinirsizKolon = PaketGecerlilik::kolonVar();
            $sec = ['adisyon_paketler.id', 'adisyon_paketler.paket_id', 'adisyon_paketler.seans_sayisi', 'paketler.paket_adi'];
            if ($sinirsizKolon) $sec[] = 'adisyon_paketler.sinirsiz';
            $satirlar = \DB::table('adisyon_paketler')
                ->join('adisyonlar', 'adisyon_paketler.adisyon_id', '=', 'adisyonlar.id')
                ->join('paketler', 'paketler.id', '=', 'adisyon_paketler.paket_id')
                ->where('adisyonlar.user_id', $this->musteriId)
                ->where('adisyonlar.salon_id', $this->salonId)
                ->where(function ($q) use ($sinirsizKolon) {
                    $q->where('adisyon_paketler.seans_sayisi', '>', 0);
                    if ($sinirsizKolon) $q->orWhere('adisyon_paketler.sinirsiz', 1);
                })
                ->where(function ($q) {
                    $q->whereNull('adisyon_paketler.otomatik_randevu_olusturuldu')
                      ->orWhere('adisyon_paketler.otomatik_randevu_olusturuldu', '!=', 1);
                })
                ->orderBy('adisyonlar.tarih', 'asc')->select($sec)->get();

            $bugun = now('Europe/Istanbul')->format('Y-m-d');
            $acik = $this->personelListesi();
            foreach ($satirlar as $ap) {
                if (PaketGecerlilik::suresiDoldu($ap->id, $bugun)) continue;
                $sinirsiz = !empty($ap->sinirsiz);
                $hizmetler = [];
                $ph = \DB::table('paket_hizmetler')->join('hizmetler', 'hizmetler.id', '=', 'paket_hizmetler.hizmet_id')
                    ->where('paket_hizmetler.paket_id', $ap->paket_id)->get(['paket_hizmetler.hizmet_id', 'hizmetler.hizmet_adi']);
                foreach ($ph as $h) {
                    $hid = (int) $h->hizmet_id;
                    $kalan = null;
                    if (!$sinirsiz) {
                        $acilan = (int) \DB::table('adisyon_paket_seanslar')->where('adisyon_paket_id', $ap->id)->where('hizmet_id', $hid)->count();
                        $kalan = (int) $ap->seans_sayisi - $acilan;
                        if ($kalan <= 0) continue;
                    } elseif (!PaketGecerlilik::seansUygun($ap->id, $hid, $bugun)) {
                        continue;
                    }
                    // Paket hizmetini VEREN ve takvimi acik personel (yalniz eslesenler; liste bossa "tum personel")
                    $veren = PersonelHizmetler::where('hizmet_id', $hid)->whereIn('personel_id', array_keys($acik) ?: [0])
                        ->pluck('personel_id')->map(function ($v) { return (int) $v; })->unique()->values()->all();
                    $personel = [];
                    foreach ($veren as $pid) if (isset($acik[$pid])) $personel[] = ['personel_id' => $pid, 'ad' => $this->personelHitap((int) $pid)];
                    $hizmetler[] = ['hizmet_id' => $hid, 'ad' => trim((string) $h->hizmet_adi), 'kalan_seans' => $kalan,
                        'toplam_seans' => $sinirsiz ? null : (int) $ap->seans_sayisi, 'kullanilan_seans' => $sinirsiz ? null : $acilan,
                        'personeller' => $personel];
                }
                if ($hizmetler) {
                    $out[] = ['adisyon_paket_id' => (int) $ap->id, 'paket_adi' => trim((string) $ap->paket_adi), 'hizmetler' => $hizmetler];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan paketler: ' . $e->getMessage());
            $out = [];
        }
        return $this->_paketler = $out;
    }

    /**
     * Hizmet musterinin kullanilabilir bir paketinde mi? hizmet_eslestir sonucuna eklenir: musteri "lazer randevusu
     * alacaktım" deyince asistan paketi (toplam/kullanilan/kalan seans) kendisi getirir. Yoksa null.
     */
    protected function paketlerHizmet(int $hizmetId): array
    {
        $out = [];
        foreach ($this->paketler() as $p) {
            foreach ($p['hizmetler'] as $h) {
                if ((int) $h['hizmet_id'] !== $hizmetId) continue;
                $sinirsiz = $h['kalan_seans'] === null;
                $out[] = ['adisyon_paket_id' => $p['adisyon_paket_id'], 'paket_adi' => $p['paket_adi'], 'hizmet' => $h['ad'], 'hizmet_id' => $hizmetId, 'sinirsiz' => $sinirsiz]
                    + ($sinirsiz ? [] : array_filter(['toplam_seans' => $h['toplam_seans'] ?? null, 'kullanilan_seans' => $h['kullanilan_seans'] ?? null], 'is_int')
                        + ['kalan_seans' => (int) $h['kalan_seans']]);
                break;
            }
        }
        return $out;
    }

    /**
     * GECIKME: Musteri cumlesinde salonun TEK bir hizmet adi (ekli haliyle) aciktan geciyorsa hizmet_eslestir'i KOD
     * onceden calistirir ve sonucu gizli notla verir -> model eslestirme icin ayri bir tur (~1-1,5 sn) harcamaz.
     * Birden fazla hizmet / belirsiz / yanlis duyulmus ad -> bos (model normal akista hizmet_eslestir cagirir).
     * $paketli: sonuc paket bilgisi iceriyorsa true (paketNotu ayrica eklenmesin).
     */
    public function onEslestirmeNotu(string $metin, &$paketli = false): string
    {
        $paketli = false;
        $m = ' ' . $this->sade($metin) . ' ';
        $bulunan = [];
        foreach ($this->salon['hizmetler'] ?? [] as $h) {
            $ad = trim((string) ($h['ad'] ?? ''));
            $sa = $this->sade($ad);
            if ($sa === '' || strlen($sa) < 3) continue;
            if (strpos($m, ' ' . $sa) !== false) $bulunan[$sa] = $ad;
        }
        // "Lazer" ve "Lazer Epilasyon" ayni anda: uzun olan kazanir (kisa olan uzunun parcasi)
        foreach (array_keys($bulunan) as $k) foreach (array_keys($bulunan) as $k2) {
            if ($k !== $k2 && isset($bulunan[$k]) && strpos(' ' . $k2 . ' ', ' ' . $k . ' ') !== false) unset($bulunan[$k]);
        }
        if (count($bulunan) !== 1) return '';
        try {
            [$d] = $this->toolCalistir('hizmet_eslestir', ['metin' => array_values($bulunan)[0]]);
        } catch (\Throwable $e) { return ''; }
        if (!is_array($d) || ($d['eslesme'] ?? '') !== 'tek') return '';
        $paketli = !empty($d['paketler']);
        $this->_onBaglam = $this->baglamNotu('hizmet_eslestir', ['metin' => array_values($bulunan)[0]], $d); // sonraki tur id'yi bilsin
        Log::info('[SANTRAL-ON-ESLESTIRME]', ['salon' => $this->salonId, 'metin' => $metin, 'hizmet' => $d['hizmet'] ?? null, 'paketli' => $paketli]);
        return "\n[SİSTEM — müşteri duymaz: hizmet_eslestir bu cümle için ZATEN çalıştırıldı, TEKRAR ÇAĞIRMA. Sonuç: "
            . json_encode($d, JSON_UNESCAPED_UNICODE) . ']';
    }

    /**
     * Musteri bir hizmet soyledi (paketten bahsetmese bile) ve bu hizmet musterinin paket(ler)inde: KODLA tespit edip
     * modele gizli not verir -> asistan paketleri + kalan seanslari sayar, tek pakette "bu paketten olusturalim mi",
     * coklu pakette "hangi paketten" diye sorar. Ayni paket(ler) daha once konusulduysa (asistan mesajinda paket adi
     * gectiyse) tekrar not uretmez.
     */
    public function paketNotu(string $metin, array $gecmis = []): string
    {
        $paketler = $this->paketler();
        if (!$paketler) return '';
        $m = ' ' . $this->sade($metin) . ' ';
        $adaylar = array_column($this->hizmetAdaylari($metin), 'id');
        $bulunan = null;
        foreach ($paketler as $p) {
            foreach ($p['hizmetler'] as $h) {
                $sa = $this->sade((string) $h['ad']);
                if ($sa !== '' && (strpos($m, ' ' . $sa) !== false || in_array((int) $h['hizmet_id'], $adaylar, true))) { $bulunan = (int) $h['hizmet_id']; break 2; }
            }
        }
        if (!$bulunan) return '';
        $pl = $this->paketlerHizmet($bulunan);
        if (!$pl) return '';
        // Daha once konusulduysa tekrar sorma
        $onceki = '';
        foreach ($gecmis as $g) if (($g['role'] ?? '') === 'assistant') $onceki .= ' ' . $this->sade((string) ($g['content'] ?? ''));
        $yeni = array_filter($pl, function ($p) use ($onceki) { $a = $this->sade($p['paket_adi']); return $a === '' || strpos($onceki, $a) === false; });
        if (!$yeni) return '';

        $satir = [];
        foreach ($pl as $p) {
            $sayi = $p['sinirsiz'] ? 'süreli paket (seans sayısı söyleme)'
                : (isset($p['toplam_seans'], $p['kullanilan_seans']) ? "toplam {$p['toplam_seans']}, kullanılan {$p['kullanilan_seans']}, " : '') . "kalan {$p['kalan_seans']} seans";
            $satir[] = "'{$p['paket_adi']}' (adisyon_paket_id {$p['adisyon_paket_id']}, {$sayi})";
        }
        Log::info('[SANTRAL-PAKET-TESPIT]', ['salon' => $this->salonId, 'metin' => $metin, 'hizmet_id' => $bulunan, 'paket_adet' => count($pl)]);
        $hizmet = $pl[0]['hizmet'];
        $soru = count($pl) === 1
            ? "Paketi ve kalan seansını TEK cümlede söyle, sonra \"Randevuyu bu paketinizden oluşturmak ister misiniz?\" diye SOR."
            : "Bu hizmetin " . count($pl) . " paketinde olduğunu söyle, her paketi adıyla ve kalan seansıyla say, sonra \"Hangi paketinizden randevu oluşturalım?\" diye SOR.";
        return "\n[SİSTEM paket — müşteri duymaz: '{$hizmet}' (hizmet_id {$bulunan}) müşterinin paketinde: " . implode('; ', $satir) . ". {$soru}"
            . " Evet/paket seçerse randevu_olustur'a o adisyon_paket_id'yi ver; paketten istemezse paketsiz=true ver. Sayıları AYNEN kullan.]";
    }

    /**
     * Asistan "bu paketinizden oluşturmak ister misiniz / hangi paketinizden" diye sordu ve musteri cevap verdiyse
     * KARARI her sonraki turda modele hatirlat (kayit #72: "Tabi ki" dedikten sonra onay cumlesinde paketi yeniden
     * sordu). Musteri bu turda cevapliyorsa ($metin) o da sayilir. Karar yoksa bos.
     */
    public function paketKarariNotu(array $gecmis, string $metin = ''): string
    {
        $paketler = $this->paketler();
        if (!$paketler) return '';
        $dizi = array_values($gecmis);
        if ($metin !== '') $dizi[] = ['role' => 'user', 'content' => $metin];
        $karar = null; $soru = null;
        foreach ($dizi as $i => $g) {
            if (($g['role'] ?? '') !== 'assistant') continue;
            $c = (string) ($g['content'] ?? '');
            if (!preg_match('/paketiniz(den)?\b[^?]*\?/u', $c) || !preg_match('/ister misiniz|oluşturalım|kullanmak|kullanalım/u', $c)) continue;
            $cevap = null;
            for ($j = $i + 1; $j < count($dizi); $j++) if (($dizi[$j]['role'] ?? '') === 'user') { $cevap = (string) ($dizi[$j]['content'] ?? ''); break; }
            if ($cevap === null) continue;
            $soru = $c;
            $f = mb_strtolower(strtr($cevap, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
            $secilen = null;
            foreach ($paketler as $p) { $a = $this->sade($p['paket_adi']); if ($a !== '' && strpos(' ' . $this->sade($cevap) . ' ', ' ' . $a) !== false) $secilen = $p; }
            if (preg_match('/hayır|istemiyorum|olmasın|kullanma|düşme|ücretli|gerek yok/u', $f)) $karar = ['paketsiz'];
            elseif ($secilen) $karar = ['paket', $secilen];
            elseif ($this->onayMi($cevap)) {
                $anilan = array_values(array_filter($paketler, function ($p) use ($c) { $a = $this->sade($p['paket_adi']); return $a !== '' && strpos($this->sade($c), $a) !== false; }));
                $karar = ['paket', count($anilan) === 1 ? $anilan[0] : (count($paketler) === 1 ? $paketler[0] : null)];
            }
        }
        if (!$karar) return '';
        if ($karar[0] === 'paketsiz') {
            return "\n[SİSTEM paket kararı — müşteri duymaz: Müşteri randevunun paketinden KULLANILMAMASINI istedi. Paketi TEKRAR SORMA; randevu_olustur'da paketsiz=true ver.]";
        }
        $p = $karar[1];
        return "\n[SİSTEM paket kararı — müşteri duymaz: Müşteri randevunun " . ($p ? "'{$p['paket_adi']}' paketinden (adisyon_paket_id {$p['adisyon_paket_id']})" : 'paketinden')
            . " oluşturulmasını ONAYLADI. Paketi TEKRAR SORMA; onay cümlesinde \"paketinizden\" de" . ($p ? ", randevu_olustur'da adisyon_paket_id {$p['adisyon_paket_id']} ver" : '') . '.]';
    }

    /**
     * randevuekleguncelle seansi otomatik olarak EN ESKI uygun pakete baglar. Musteri baska bir paket sectiyse seansi
     * o pakete tasir; paketten istemediyse paket seansini kaldirir (seans pakete geri doner). Hata yutar.
     */
    protected function paketSeciminiUygula(int $rid, int $hizmetId, array $in): void
    {
        try {
            $istenen = $this->intId($in['adisyon_paket_id'] ?? null);
            $paketsiz = !empty($in['paketsiz']) && $in['paketsiz'] !== 'false';
            if (!$istenen && !$paketsiz) return;
            $aps = \App\AdisyonPaketSeanslar::where('randevu_id', $rid)->where('hizmet_id', $hizmetId)->whereNotNull('adisyon_paket_id')->first();
            if ($paketsiz) {
                if ($aps) { $aps->delete(); Log::info('[SANTRAL-PAKET-SECIM] paketsiz', ['randevu' => $rid, 'aps' => $aps->id]); }
                return;
            }
            // Istenen paket bu musterinin bu hizmet icin KULLANILABILIR paketi mi? (uydurma id'ye baglama)
            $gecerli = false;
            foreach ($this->paketlerHizmet($hizmetId) as $p) if ((int) $p['adisyon_paket_id'] === $istenen) $gecerli = true;
            if (!$gecerli || !$aps || (int) $aps->adisyon_paket_id === $istenen) return;
            $ap = \DB::table('adisyon_paketler')->where('id', $istenen)->first(['kullanilan_seans', 'kullanilmayan_seans']);
            $eski = $aps->adisyon_paket_id;
            $aps->adisyon_paket_id = $istenen;
            $aps->seans_no = ((int) ($ap->kullanilan_seans ?? 0)) + ((int) ($ap->kullanilmayan_seans ?? 0)) + 1;
            $aps->save();
            Log::info('[SANTRAL-PAKET-SECIM] paket degisti', ['randevu' => $rid, 'eski' => $eski, 'yeni' => $istenen]);
        } catch (\Throwable $e) {
            Log::warning('[SANTRAL-PAKET-SECIM] ' . $e->getMessage());
        }
    }

    /** Prompt BAGLAM satirlari. */
    protected function paketSatirlari(): string
    {
        $p = $this->paketler();
        if (!$p) return 'yok';
        $out = '';
        foreach ($p as $x) {
            $out .= "\n- Paket: {$x['paket_adi']} (adisyon_paket_id {$x['adisyon_paket_id']})";
            foreach ($x['hizmetler'] as $h) {
                $kalan = $h['kalan_seans'] === null ? 'süreli/sınırsız'
                    : (isset($h['toplam_seans'], $h['kullanilan_seans']) ? "toplam {$h['toplam_seans']} seans, kullanılan {$h['kullanilan_seans']}, " : '') . "{$h['kalan_seans']} seans kaldı";
                $pers = $h['personeller']
                    ? implode(', ', array_map(function ($q) { return "{$q['ad']} (personel_id {$q['personel_id']})"; }, $h['personeller']))
                    : 'hizmete özel personel tanımlı değil (takvimi açık tüm personel uygun)';
                $out .= "\n  * {$h['ad']} (hizmet_id {$h['hizmet_id']}, {$kalan}) — uygun personel: {$pers}";
            }
        }
        return $out;
    }

    /** Randevu takvimi ACIK personeller: [id => ad] (takvimde_gorunsun=1, arsivli degil). */
    protected function personelListesi(): array
    {
        if ($this->_personeller !== null) return $this->_personeller;
        $out = [];
        try {
            $q = Personeller::where('salon_id', $this->salonId)
                ->where(function ($w) { $w->whereNull('arsivli')->orWhere('arsivli', '!=', 1); })
                ->where('takvimde_gorunsun', 1)->orderBy('id')->get(['id', 'personel_adi', 'cinsiyet']);
            foreach ($q as $p) {
                $ad = $this->adSesli(trim((string) $p->personel_adi));
                if ($ad !== '') { $out[(int) $p->id] = $ad; $this->_personelCins[(int) $p->id] = $p->cinsiyet === null ? null : (int) $p->cinsiyet; }
            }
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan personelListesi: ' . $e->getMessage());
        }
        return $this->_personeller = $out;
    }

    /**
     * TAMAMI BUYUK HARF ad ("ORBEY", "GÜLŞAH İNCE") TTS'te harf harf okunuyordu ("O, R, B, E, Y") -> Turkce
     * bas harf buyuk ("Orbey", "Gülşah İnce"). Kucuk harf iceren adlara dokunulmaz.
     */
    protected function adSesli(string $ad): string
    {
        if ($ad === '' || !preg_match('/\p{Lu}{2}/u', $ad) || preg_match('/\p{Ll}/u', $ad)) return $ad;
        $kucuk = mb_strtolower(strtr($ad, ['I' => 'ı', 'İ' => 'i']), 'UTF-8');
        return preg_replace_callback('/(^|[\s\-])(\p{L})/u', function ($x) {
            return $x[1] . mb_strtoupper(strtr($x[2], ['i' => 'İ', 'ı' => 'I']), 'UTF-8');
        }, $kucuk);
    }

    /**
     * Uzmanin musteriye soylenecek adi: "Gülşah Hanım", "Ferdi Bey" (isletme istegi 2026-11-17: uzman adi ön ekle).
     * Cinsiyet: once isim sozlugu (net adlar), sonra personel kaydi (kayitta ayarlanmamis cinsiyet 0=Bayan
     * gorunebildigi icin sozluk once), ikisi de yoksa yalniz ad. Ad zaten Hanım/Bey ile bitiyorsa dokunulmaz.
     */
    protected function personelHitap(int $pid): string
    {
        $ad = $this->personelListesi()[$pid] ?? '';
        if ($ad === '' || preg_match('/\s(Hanım|Bey)$/u', $ad)) return $ad;
        $ilk = explode(' ', $ad)[0];
        $c = null;
        try {
            if (!TurkceIsimCinsiyet::belirsizMi($ilk)) $c = TurkceIsimCinsiyet::bul($ilk);
        } catch (\Throwable $e) {}
        if ($c === null) $c = $this->_personelCins[$pid] ?? null;
        if ($c === null) return $ad;
        return $ilk . ((int) $c === 1 ? ' Bey' : ' Hanım');
    }

    /** Hizmeti veren (personel_sunulan_hizmetler) ve takvimi acik personeller; yoksa takvimi acik HERKES (online randevu mantigi). */
    protected function hizmetPersonelleri(int $hizmetId): array
    {
        $acik = $this->personelListesi();
        $veren = PersonelHizmetler::where('hizmet_id', $hizmetId)->whereIn('personel_id', array_keys($acik) ?: [0])
            ->pluck('personel_id')->map(function ($v) { return (int) $v; })->unique()->values()->all();
        return array_slice($veren ?: array_keys($acik), 0, 8);
    }

    protected function musaitSaatlerPersonelli(array $in): array
    {
        $hizmetId = $this->intId($in['hizmet_id'] ?? null);
        if (!$hizmetId) return ['ok' => false, 'hata' => 'Önce hizmet_eslestir ile hizmeti netleştir ve hizmet_id ver.'];
        $tarih = (string) ($in['tarih'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) return ['ok' => false, 'hata' => 'tarih YYYY-MM-DD olmalı (TARİH HARİTASI).'];
        $saat = preg_match('/^(\d{1,2}):(\d{2})/', (string) ($in['saat'] ?? ''), $sm) ? sprintf('%02d:%02d', $sm[1], $sm[2]) : null;
        $vakitMap = ['sabah' => 'sabah', 'ogle' => 'ogleden_sonra', 'ogleden_sonra' => 'ogleden_sonra', 'aksam' => 'aksam'];
        $vakit = $saat ? null : ($vakitMap[$in['zaman_dilimi'] ?? ''] ?? null);

        $plist = $this->personelListesi();
        $personelId = $this->intId($in['personel_id'] ?? null);
        if ($personelId) {
            if (!isset($plist[$personelId])) {
                // Cogu zaman model yanlis id verir (musteri listedeki birini secmisken): dogru id'leri ver, kendisi duzeltsin
                $dogru = [];
                try { $veren = $this->hizmetPersonelleri($hizmetId); } catch (\Throwable $e) { $veren = array_keys($plist); }
                foreach ($veren as $pid) if (isset($plist[$pid])) $dogru[] =$this->personelHitap((int) $pid) . " = personel_id {$pid}";
                return ['ok' => false, 'hata' => "personel_id {$personelId} geçersiz. Bu hizmetin personelleri: " . implode(', ', $dogru)
                    . '. Müşterinin seçtiği kişinin personel_id\'si ile musait_saatler\'i HEMEN yeniden çağır; müşteriye "uygun değil" DEME. Seçtiği kişi listede yoksa o isimde personel olmadığını söyle.'];
            }
            $adaylar = [$personelId];
        } else {
            $adaylar = $this->hizmetPersonelleri($hizmetId);
        }
        if (!$adaylar) return ['ok' => false, 'hata' => 'Randevu takvimi açık personel yok. Müşteriyi operatöre aktar.'];

        $servis = app(SesliRandevuCozService::class);
        $bul = function ($pid, $t, $s, $v, $ufuk = 14) use ($servis, $hizmetId) {
            try { $m = $servis->musaitlikBul($this->salonId, $pid, $hizmetId, $t, $s, $v, $ufuk); }
            catch (\Throwable $e) { Log::warning('RandevuAsistan musaitlikBul: ' . $e->getMessage()); return null; }
            return !empty($m['bulundu']) ? $m : null;
        };
        $sonraki = function ($m) { // ayni personelin ayni gundeki bir sonraki slotu icin baslangic saati
            [$h, $d] = array_map('intval', explode(':', $m['saat']));
            $dk = $h * 60 + $d + max(15, (int) ($m['sure_dk'] ?? 30));
            return sprintf('%02d:%02d', intdiv($dk, 60), $dk % 60);
        };

        $secenek = [];
        foreach ($adaylar as $pid) {
            $m = $bul($pid, $tarih, $saat, $vakit);
            if ($m) $secenek[] = ['tarih' => $m['tarih'], 'saat' => $m['saat'], 'personel_id' => $pid, 'personel' => isset($plist[$pid]) ? $this->personelHitap((int) $pid) : '', '_m' => $m, '_tam' => !empty($m['tam_istek'])];
        }
        // tam istenen saat > en erken; ayni saatte tek secenek kalsin (farkli personel ayni saat = ayni oneri)
        usort($secenek, function ($a, $b) { return [$a['_tam'] ? 0 : 1, $a['tarih'], $a['saat']] <=> [$b['_tam'] ? 0 : 1, $b['tarih'], $b['saat']]; });
        $sec = []; $gorulen = [];
        foreach ($secenek as $s) {
            $k = $s['tarih'] . ' ' . $s['saat'];
            if (isset($gorulen[$k])) continue;
            $gorulen[$k] = 1; $sec[] = $s;
            if (count($sec) === 2) break;
        }
        // Tek secenek varsa: ayni personelin ayni gunku sonraki bos saatini ikinci secenek yap
        if (count($sec) === 1) {
            $m2 = $bul($sec[0]['personel_id'], $sec[0]['tarih'], $sonraki($sec[0]['_m']), null, 1);
            if ($m2 && $m2['tarih'] === $sec[0]['tarih'] && $m2['saat'] > $sec[0]['saat']) {
                $sec[] = ['tarih' => $m2['tarih'], 'saat' => $m2['saat'], 'personel_id' => $sec[0]['personel_id'], 'personel' => $sec[0]['personel']];
            }
        }
        // Hala tek secenek: ayni gunun EN ERKEN bos saati, o da yoksa sonraki gunlerden ilk bos saat (2 oneri garanti)
        if (count($sec) === 1 && $saat) {
            $p0 = $sec[0]['personel_id'];
            foreach ([[$tarih, null], [date('Y-m-d', strtotime($sec[0]['tarih'] . ' +1 day')), null]] as [$t2, $s2]) {
                $m3 = $bul($p0, $t2, $s2, null, $t2 === $tarih ? 1 : 14);
                if ($m3 && ($m3['tarih'] . ' ' . $m3['saat']) !== ($sec[0]['tarih'] . ' ' . $sec[0]['saat'])) {
                    $sec[] = ['tarih' => $m3['tarih'], 'saat' => $m3['saat'], 'personel_id' => $p0, 'personel' => $sec[0]['personel']];
                    break;
                }
            }
            usort($sec, function ($a, $b) { return [$a['tarih'], $a['saat']] <=> [$b['tarih'], $b['saat']]; });
        }
        // Musteri GUN+SAAT verdi ve o saat (o gun) bos: alternatif YOK, dogrudan onay.
        if ($saat && $sec && $sec[0]['_tam'] && $sec[0]['tarih'] === $tarih) {
            $t = $sec[0];
            return [
                'ok' => true,
                'tam_eslesme' => true,
                'talimat' => 'İstenen gün ve saat UYGUN. Alternatif SUNMA, saat SORMA: doğrudan onay cümlesini söyle.',
                'secenekler' => [['tarih' => $t['tarih'], 'gun' => $this->tarihSoz($t['tarih']), 'saat' => $t['saat'], 'personel_id' => $t['personel_id'], 'personel' => $t['personel']]],
            ];
        }
        $sec = array_map(function ($s) { unset($s['_m'], $s['_tam']); $s['gun'] = $this->tarihSoz($s['tarih']); return $s; }, $sec);
        if (!$sec) return ['ok' => true, 'secenekler' => [], 'mesaj' => 'Önümüzdeki 14 günde uygun saat bulunamadı. Başka gün/dilim ya da başka personel öner.'];
        $sonuc = [
            'ok' => true,
            'istenen_tarih' => $tarih,
            'istenen_gun_dolu' => $sec[0]['tarih'] !== $tarih,
            'istenen_saat_dolu' => (bool) $saat,
            'secenekler' => $sec,
        ];
        if ($saat) {
            // Istenen saat dolu (onayli ya da talep randevusuyla cakisiyor): once "uygun değil", sonra 2 oneri
            $sonuc['onerilen_cumle'] = $this->doluOneriCumlesi($tarih, $saat, $sec);
            $sonuc['talimat'] = 'İstenen saat DOLU. Müşteriye AYNEN onerilen_cumle\'yi söyle (önce uygun olmadığını, sonra iki seçeneği).';
        }
        return $sonuc;
    }

    /** "13:00" -> "13:00'te", "10:00" -> "10:00'da", "12:30" -> "12:30'da" (okunan son kelimeye gore bulunma eki). */
    protected function saatEkli(string $saat): string
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})/', $saat, $m)) return $saat . "'te";
        $n = (int) $m[2] !== 0 ? (int) $m[2] : (int) $m[1];
        $birler = ['', 'de', 'de', 'te', 'te', 'te', 'da', 'de', 'de', 'da'];
        $onlar = ['da', 'da', 'de', 'da', 'ta', 'de'];   // sıfır, on, yirmi, otuz, kırk, elli
        $ek = $n % 10 !== 0 ? $birler[$n % 10] : $onlar[intdiv($n, 10)] ?? 'de';
        return $m[1] . ':' . $m[2] . "'" . $ek;
    }

    /** "Cuma, 20 Kasım saat 12:00 uygun değil. Aynı gün saat 13:00'te uzmanımız Gülşah Hanım ile ya da ... önerebilirim, hangisi uygun olur?" */
    protected function doluOneriCumlesi(string $tarih, string $saat, array $sec): string
    {
        $c = $this->tarihSoz($tarih) . ' saat ' . $saat . ' uygun değil.';
        if (!$sec) return $c . ' Başka bir gün ya da saat söyler misiniz?';
        $parca = [];
        foreach (array_slice($sec, 0, 2) as $i => $s) {
            $gun = $s['tarih'] === $tarih ? ($i === 0 || $sec[0]['tarih'] !== $tarih ? 'aynı gün' : '') : $this->tarihSoz($s['tarih']);
            $parca[] = trim($gun . ' saat ' . $this->saatEkli($s['saat']) . (!empty($s['personel']) ? ' uzmanımız ' . $s['personel'] . ' ile' : ''));
        }
        $c .= ' Size ' . implode(' ya da ', $parca) . (count($parca) > 1 ? ' önerebilirim, hangisi uygun olur?' : ' önerebilirim, uygun olur mu?');
        return $c;
    }

    /** Model cevabi "dolu/uygun değil" diyor VE tum onerilen saatleri iceriyor mu? */
    protected function doluCevabiTamMi(string $cevap, array $sonuc): bool
    {
        $f = mb_strtolower($cevap, 'UTF-8');
        if (!preg_match('/dolu|uygun değil|müsait değil|boş değil|uygun olmadığı/u', $f)) return false;
        foreach (array_slice($sonuc['secenekler'] ?? [], 0, 2) as $s) {
            [$h, $d] = array_map('intval', explode(':', $s['saat']));
            $kalip = $d === 0 ? '/\b0?' . $h . '(\b|[:.\']|\s)/u' : '/\b0?' . $h . '[:.]' . sprintf('%02d', $d) . '/u';
            if (!preg_match($kalip, $cevap)) return false;
        }
        return true;
    }

    /** Arayanin kendi gelecek randevulari (bekleyen + onayli), personel/hizmet bilgisiyle. */
    protected function randevularim(): array
    {
        if (!$this->musteriId) return ['ok' => true, 'randevular' => [], 'not' => 'Arayan bu işletmede kayıtlı değil; randevusu yok.'];
        if ($this->_randevularim !== null) return $this->_randevularim;
        $liste = [];
        $rs = Randevular::where('user_id', $this->musteriId)->where('salon_id', $this->salonId)
            ->whereDate('tarih', '>=', now('Europe/Istanbul')->toDateString())
            ->whereIn('durum', [Randevular::BEKLENIYOR, Randevular::ONAYLANDI])
            ->orderBy('tarih')->orderBy('saat')->limit(5)->get(['id', 'tarih', 'saat', 'durum']);
        foreach ($rs as $r) $liste[] = $this->randevuSatiri($r);
        return $this->_randevularim = ['ok' => true, 'randevular' => $liste];
    }

    /** Tek randevunun konusma/arac icin ozeti (randevularim + hatirlatma ortak). */
    protected function randevuSatiri($r): array
    {
        $rh = RandevuHizmetler::where('randevu_id', $r->id)->with(['hizmetler', 'personeller'])->get();
        $ilk = $rh->first();
        return [
            'id' => (int) $r->id,
            'durum' => (int) $r->durum === Randevular::BEKLENIYOR ? 'talep (onay bekliyor) — iptal/güncelleme yapılabilir' : 'onaylı',
            'tarih' => substr((string) $r->tarih, 0, 10),
            'gun' => $this->tarihSoz(substr((string) $r->tarih, 0, 10)),
            'saat' => substr((string) $r->saat, 0, 5),
            'hizmet' => $rh->map(function ($x) { return optional($x->hizmetler)->hizmet_adi; })->filter()->implode(', '),
            // "Ferdi Bey" / "Gülşah Hanım" (kayit #120: liste "Ferdi ile", "Anıl ORBEY ile" okuyordu)
            'personel' => $ilk && $ilk->personel_id ? ($this->personelHitap((int) $ilk->personel_id) ?: optional($ilk->personeller)->personel_adi) : null,
            'hizmet_id' => $ilk ? (int) $ilk->hizmet_id : null,
            'personel_id' => $ilk && $ilk->personel_id ? (int) $ilk->personel_id : null,
        ];
    }

    /** Randevu bu salonda ve ARAYANIN mi? (iptal/guncelle oncesi zorunlu) */
    protected function randevuSahibiMi(?int $randevuId): bool
    {
        if (!$randevuId || !$this->musteriId) return false;
        return Randevular::where('id', $randevuId)->where('salon_id', $this->salonId)
            ->where('user_id', $this->musteriId)->exists();
    }

    /**
     * Musteri kendi randevusunu iptal eder (TALEP/bekleyen ya da onayli farketmez).
     * Panel/uygulama iptaliyle ayni etkiler: paket seansi silinir (seans pakete geri doner), durum=IPTAL,
     * iptal bildirimi (ayar 3: "randevu iptal") WA-first/SMS ile. Uygulamanin randevuiptalet'i bekleyen
     * randevuyu "isletme reddetti" sayip "talebiniz reddedildi" yazdigi icin dogrudan kullanilmadi.
     */
    protected function randevuIptalEt(int $rid): array
    {
        $r = Randevular::where('id', $rid)->where('salon_id', $this->salonId)->first();
        if (!$r) return ['ok' => false, 'hata' => 'Randevu bulunamadı.'];
        if ((int) $r->durum === Randevular::IPTAL_EDILDI) return ['ok' => true, 'mesaj' => 'Randevu zaten iptal edilmiş.'];
        try {
            \App\AdisyonPaketSeanslar::where('randevu_id', $r->id)->delete();
            $r->durum = Randevular::IPTAL_EDILDI;
            $r->save();
        } catch (\Throwable $e) {
            Log::error('RandevuAsistan randevuIptalEt: ' . $e->getMessage());
            return ['ok' => false, 'hata' => 'İptal şu an yapılamadı. Müşteriye özür dile, yetkiliye aktarmayı teklif et.'];
        }
        try {
            app(ApiController::class)->randevuIptalBildirimleri(
                Request::create('/api/randevuiptalet', 'POST', ['salonid' => $this->salonId, 'sube' => $this->salonId, 'randevuid' => $r->id, 'durum' => Randevular::IPTAL_EDILDI, 'easistan' => '1']),
                $r->fresh(), false
            );
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan iptal bildirimi: ' . $e->getMessage()); // iptal yapildi; bildirim hatasi akisi bozmasin
        }
        // Panel > SMS ayarlari > "Aktif Randevu İptalinde" (ayar 3) musteri acik mi? Kapaliysa musteriye WA/SMS GITMEZ.
        $ayarAcik = (int) \App\SalonSMSAyarlari::where('ayar_id', 3)->where('salon_id', $this->salonId)->value('musteri') === 1;
        Log::info('[SANTRAL-IPTAL]', ['salon' => $this->salonId, 'randevu' => $r->id, 'musteri_tel' => optional($r->users)->cep_telefon, 'iptal_sms_ayari_acik' => $ayarAcik]);
        return ['ok' => true, 'mesaj' => 'Randevu iptal edildi', 'randevu_id' => (int) $r->id, 'bilgilendirme_gonderildi' => $ayarAcik];
    }

    protected function sahipDegilHata(): array
    {
        return ['ok' => false, 'hata' => 'Bu randevu arayan müşteriye ait değil ya da bulunamadı. Yalnız mevcut_randevular’ın döndüğü id’leri kullan.'];
    }

    /** ISO tarih_saat -> [Y-m-d, H:i] ya da null */
    protected function tarihSaatAyir(string $iso): ?array
    {
        if (!preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{1,2}):(\d{2})/', trim($iso), $m)) return null;
        return [$m[1], sprintf('%02d:%02d', $m[2], $m[3])];
    }

    /** Slot hala bos mu? (personelin o anki dolu araliklarina gore, tam o saat) */
    protected function slotHalaBos(int $personelId, int $hizmetId, string $tarih, string $saat): bool
    {
        try {
            $m = app(SesliRandevuCozService::class)->musaitlikBul($this->salonId, $personelId, $hizmetId, $tarih, $saat, null, 1);
        } catch (\Throwable $e) { return false; }
        return !empty($m['bulundu']) && $m['tarih'] === $tarih && $m['saat'] === $saat;
    }

    /** ApiController@randevuekleguncelle (panel/uygulama/eski santral ile ayni randevu yolu) */
    protected function randevuEkleGuncelleCagir(array $payload)
    {
        $r = app(ApiController::class)->randevuekleguncelle(Request::create('/api/randevuekleguncelle', 'POST', $payload));
        if (is_object($r) && method_exists($r, 'getContent')) $r = json_decode($r->getContent(), true);
        return is_array($r) ? $r : [];
    }

    protected function randevuOlusturPersonelli(array $in): array
    {
        // Kayitsiz musteriye randevu YOK: once normal musteri kaydi (ad soyad + telefon).
        if (!$this->musteriId) return ['ok' => false, 'hata' => 'Müşteri kayıtlı değil. Önce adını ve soyadını alıp musteri_kaydet aracını çağır, sonra randevuyu oluştur.'];
        $ts = $this->tarihSaatAyir((string) ($in['tarih_saat'] ?? ''));
        $hizmetId = $this->intId($in['hizmet_id'] ?? null);
        $personelId = $this->intId($in['personel_id'] ?? null);
        if (!$ts || !$hizmetId || !$personelId) return ['ok' => false, 'hata' => 'tarih_saat, hizmet_id ve personel_id gerekli (musait_saatler seçeneğinden al).'];
        [$tarih, $saat] = $ts;
        if (!isset($this->personelListesi()[$personelId])) return ['ok' => false, 'hata' => 'Bu personelin takvimi kapalı. musait_saatler ile yeniden ara.'];
        if (!$this->slotHalaBos($personelId, $hizmetId, $tarih, $saat)) {
            return ['ok' => false, 'hata' => 'Bu saat bu personel için artık dolu. musait_saatler ile yeni saat bulup müşteriye öner.'];
        }

        $sh = SalonHizmetler::where('salon_id', $this->salonId)->where('hizmet_id', $hizmetId)->first();
        $sure = ($sh && (int) $sh->sure_dk > 0) ? (int) $sh->sure_dk : 30;
        $fiyat = $sh ? ($sh->baslangic_fiyat ?? $sh->son_fiyat ?? 0) : 0;
        try {
            $r = $this->randevuEkleGuncelleCagir([
                'salonid' => $this->salonId, 'randevu_id' => '', 'user_id' => $this->musteriId,
                'randevu_tarihi' => $tarih, 'randevu_saati' => $saat,
                'hizmetler' => [['hizmet_id' => $hizmetId, 'personel_id' => $personelId, 'oda_id' => '', 'cihaz_id' => '', 'yardimci_personel' => '', 'sure_dk' => $sure, 'fiyat' => $fiyat, 'birlestir' => '']],
                'yardimcipersoneller' => [],
                'notlar' => trim('Sesli asistan (telefon) ' . (string) ($in['notlar'] ?? '')),
                'cakisma_varmi' => '', 'cakisanrandevuekle' => '', 'olusturan' => null, 'olusturanMusteri' => null,
                // TALEP olarak (BEKLENIYOR): isletme panelden onaylar. Bildirim de "randevu talebiniz alınmıştır" tonunda gider.
                'randevuKaynak' => 'salon', 'easistan' => '1', 'durum' => (string) Randevular::BEKLENIYOR,
            ]);
        } catch (\Throwable $e) {
            Log::error('RandevuAsistan randevuOlustur: ' . $e->getMessage());
            return ['ok' => false, 'hata' => 'Randevu şu an oluşturulamadı. Müşteriye özür dile, yetkiliye aktarmayı teklif et.'];
        }
        if ((string) ($r['cakismavar'] ?? '') === '1') {
            return ['ok' => false, 'hata' => 'Bu saat çakışıyor. musait_saatler ile yeni saat bulup müşteriye öner.'];
        }
        $rid = Randevular::where('user_id', $this->musteriId)->where('salon_id', $this->salonId)
            ->whereDate('tarih', $tarih)->where('saat', 'like', $saat . '%')->orderByDesc('id')->value('id');
        if (!$rid) {
            Log::warning('RandevuAsistan randevuOlustur: kayit bulunamadi', ['yanit' => $r]);
            return ['ok' => false, 'hata' => 'Randevu şu an oluşturulamadı. Müşteriye özür dile, yetkiliye aktarmayı teklif et.'];
        }
        // randevuekleguncelle seansi EN ESKI uygun pakete kendisi baglar; musterinin secimi (baska paket / paketsiz) uygulanir
        $this->paketSeciminiUygula((int) $rid, $hizmetId, $in);
        return ['ok' => true, 'randevu_id' => (int) $rid, 'tarih' => $tarih, 'saat' => $saat, 'personel' => isset($this->personelListesi()[$personelId]) ? $this->personelHitap($personelId) : null, 'bilgilendirme_gonderildi' => $this->bilgilendirmeAcik(),
            // baglandiysa asistan "paketinizden" diyebilsin
            'paket_seansi' => \App\AdisyonPaketSeanslar::where('randevu_id', $rid)->whereNotNull('adisyon_paket_id')->exists(),
            'durum' => 'talep (işletme onayı bekliyor)'];
    }

    /** Panel > SMS ayarlari > "Randevu Oluşturulduğunda" (ayar 12) musteri acik mi? Aciksa randevuekleguncelle WA-first/SMS gonderir. */
    protected function bilgilendirmeAcik(): bool
    {
        return (int) \App\SalonSMSAyarlari::where('ayar_id', 12)->where('salon_id', $this->salonId)->value('musteri') === 1;
    }

    protected function randevuGuncellePersonelli(array $in): array
    {
        $rid = $this->intId($in['randevu_id'] ?? null);
        if (!$this->randevuSahibiMi($rid)) return $this->sahipDegilHata();
        $ts = $this->tarihSaatAyir((string) ($in['yeni_tarih_saat'] ?? ''));
        if (!$ts) return ['ok' => false, 'hata' => 'yeni_tarih_saat ISO 8601 olmalı.'];
        [$tarih, $saat] = $ts;

        $rv = Randevular::find($rid);
        $satirlar = RandevuHizmetler::where('randevu_id', $rid)->orderBy('id')->get();
        $ilk = $satirlar->first();
        if (!$ilk || !$ilk->personel_id) {
            // Personelsiz eski kayit: eski yol (salon geneli)
            return $this->ctrlCagir('randevuGuncelle', ['salon_id' => $this->salonId, 'randevu_id' => $rid, 'yeni_tarih_saat' => "{$tarih}T{$saat}:00"]);
        }
        // UZMAN DEGISIMI (kayit #120): musteri "Gülşen Hanım uygunsa ona alalım" dedi; musait_saatler Gülşen icin
        // "uygun" dedi ama guncelleme ESKI uzmanin (Ferdi) takvimine bakip "dolu" diyordu -> 3 tur dongu. Yeni uzman
        // verilirse kontrol ve kayit onunla yapilir (eski uzmanin satirlari yeni uzmana tasinir).
        $eskiPid = (int) $ilk->personel_id;
        $yeniPid = $this->intId($in['yeni_personel_id'] ?? null) ?: 0;
        if ($yeniPid && $yeniPid !== $eskiPid && !isset($this->personelListesi()[$yeniPid])) {
            return ['ok' => false, 'hata' => 'yeni_personel_id bu işletmenin uzmanı değil. musait_saatler’in döndürdüğü personel_id’yi ver.'];
        }
        $pid = ($yeniPid && $yeniPid !== $eskiPid) ? $yeniPid : $eskiPid;
        if (!$this->slotHalaBos($pid, (int) $ilk->hizmet_id, $tarih, $saat)) {
            return ['ok' => false, 'hata' => 'Bu saat ' . ($this->personelHitap($pid) ?: 'bu personel') . ' için dolu. musait_saatler ile yeni saat bulup öner'
                . ($pid === $eskiPid ? ' (müşteri başka uzman istediyse randevu_guncelle’ye yeni_personel_id ver).' : '.')];
        }
        try {
            $r = $this->randevuEkleGuncelleCagir([
                'salonid' => $this->salonId, 'randevu_id' => $rid, 'user_id' => $this->musteriId,
                'randevu_tarihi' => $tarih, 'randevu_saati' => $saat,
                'hizmetler' => $satirlar->map(function ($x) use ($eskiPid, $pid) {
                    return ['hizmet_id' => $x->hizmet_id, 'personel_id' => ((int) $x->personel_id === $eskiPid) ? $pid : $x->personel_id, 'oda_id' => $x->oda_id ?: '', 'cihaz_id' => $x->cihaz_id ?: '',
                        'yardimci_personel' => '', 'sure_dk' => $x->sure_dk ?: 30, 'fiyat' => $x->fiyat ?: 0, 'birlestir' => ''];
                })->all(),
                'yardimcipersoneller' => [],
                'notlar' => $rv->personel_notu,
                'cakisma_varmi' => '', 'cakisanrandevuekle' => '', 'olusturan' => null, 'olusturanMusteri' => null,
                'randevuKaynak' => 'salon', 'easistan' => '1', 'durum' => (string) $rv->durum,
            ]);
        } catch (\Throwable $e) {
            Log::error('RandevuAsistan randevuGuncelle: ' . $e->getMessage());
            return ['ok' => false, 'hata' => 'Randevu şu an güncellenemedi. Müşteriye özür dile, yetkiliye aktarmayı teklif et.'];
        }
        if ((string) ($r['cakismavar'] ?? '') === '1') return ['ok' => false, 'hata' => 'Bu saat çakışıyor. Yeni saat öner.'];
        return ['ok' => true, 'randevu_id' => $rid, 'tarih' => $tarih, 'saat' => $saat, 'personel' => $this->personelHitap($pid),
            'personel_degisti' => $pid !== $eskiPid, 'bilgilendirme_gonderildi' => $this->bilgilendirmeAcik()];
    }

    /** ApiController@yolTarifiGonder (eski santral ile ayni: WhatsApp once, olmazsa SMS) + adres. */
    protected function yolTarifiGonder(): array
    {
        $tel = $this->telNorm($this->telefon);
        if (!$tel) return ['ok' => false, 'adres' => $this->salon['adres'] ?? '', 'hata' => 'Arayan numarası yok; mesaj gönderilemedi. Adresi söyle.'];
        try {
            $r = app(ApiController::class)->yolTarifiGonder(Request::create('/api/yolTarifiGonder', 'POST', [
                'salonid' => $this->salonId, 'userid' => $this->musteriId ?: '', 'cep_telefon' => $tel,
            ]));
            $d = is_object($r) && method_exists($r, 'getContent') ? json_decode($r->getContent(), true) : null;
            $ok = !empty($d['success']);
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan yolTarifi: ' . $e->getMessage());
            $ok = false;
        }
        return ['ok' => $ok, 'adres' => $this->salon['adres'] ?? '', 'mesaj' => $ok ? 'Yol tarifi mesajla gönderildi' : 'Mesaj gönderilemedi; adresi söyle'];
    }

    /** Cep no -> DB formati 5xxxxxxxxx (users.cep_telefon). Gecersizse null. */
    protected function telNorm(?string $t): ?string
    {
        $d = preg_replace('/\D/', '', (string) $t);
        if (strlen($d) === 14 && substr($d, 0, 4) === '0090') $d = substr($d, 4);
        if (strlen($d) === 12 && substr($d, 0, 2) === '90') $d = substr($d, 2);
        if (strlen($d) === 11 && $d[0] === '0') $d = substr($d, 1);
        return (strlen($d) === 10 && $d[0] === '5') ? $d : null;
    }

    /** "AYŞE YILMAZ" / "ayşe yılmaz" -> "Ayşe Yılmaz" (Turkce I/İ dogru). */
    protected function adDuzelt(string $s): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $s));
        if ($s === '') return '';
        $out = [];
        foreach (explode(' ', $s) as $k) {
            $low = mb_strtolower(strtr($k, ['I' => 'ı', 'İ' => 'i']), 'UTF-8');
            $ilk = mb_substr($low, 0, 1, 'UTF-8');
            $ilk = $ilk === 'i' ? 'İ' : ($ilk === 'ı' ? 'I' : mb_strtoupper($ilk, 'UTF-8'));
            $out[] = $ilk . mb_substr($low, 1, null, 'UTF-8');
        }
        return implode(' ', $out);
    }

    /** AiAsistanController metodunu dahili cagir, JSON govdesini dizi dondur. */
    protected function ctrlCagir(string $method, array $fields): array
    {
        try {
            $req = Request::create('/', 'POST', $fields);
            $resp = $this->ctrl->$method($req);
            $raw = method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp;
            $data = json_decode($raw, true);
            return is_array($data) ? $data : ['ok' => false];
        } catch (\Throwable $e) {
            return ['ok' => false, 'hata' => $e->getMessage()];
        }
    }

    protected function intId($v): ?int
    {
        if ($v === null || $v === '') return null;
        $n = (int) preg_replace('/\D/', '', (string) $v);
        return $n > 0 ? $n : null;
    }

    protected function garantiCevap(?string $aksiyon): string
    {
        switch ($aksiyon) {
            case 'randevu': return 'Randevu talebinizi oluşturdum. Randevunuz onaylandığında size bilgi verilecektir.';
            case 'iptal': return 'Randevunuz iptal edildi. Başka bir arzunuz var mı?';
            case 'guncelle': return 'Randevunuz güncellendi. Başka bir arzunuz var mı?';
            case 'aktar': return 'Sizi bir yetkiliye aktarıyorum, lütfen hatta kalın.';
            case 'veda': return 'Teşekkür ederiz, iyi günler dileriz.';
            default: return 'Tabii, size nasıl yardımcı olabilirim?';
        }
    }

    protected function ttsTemizle(string $m): string
    {
        $m = preg_replace('/\[SİSTEM[^\]]*\]/u', '', $m);   // gizli notu model taklit ederse SESLENDIRME
        $m = preg_replace('/[*_`#>\-]+/u', ' ', $m);          // markdown
        $m = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}]/u', '', $m); // emoji
        // TELEFON NUMARASI (restaurant SantralAsistan::ttsTemizle ile ayni): 10+ haneli numarayi
        // rakam rakam okut ("beş yüz otuz iki milyar..." olmasin). Tarih/saat/fiyat etkilenmez.
        $m = preg_replace_callback('/\d[\d \-\.\(\)]{6,}\d/u', function ($x) {
            $d = preg_replace('/\D/', '', $x[0]);
            return strlen($d) < 10 ? $x[0] : implode(' ', str_split($d));
        }, $m);
        $m = preg_replace('/\s+/u', ' ', $m);
        return trim($m);
    }

    /** gecmis -> Anthropic messages (role user/assistant, bos at, ilk mesaj user olmali). */
    protected function gecmisMesajlari(array $gecmis): array
    {
        $out = [];
        foreach ($gecmis as $m) {
            $role = ($m['role'] ?? '') === 'assistant' ? 'assistant' : (($m['role'] ?? '') === 'user' ? 'user' : null);
            $c = trim((string) ($m['content'] ?? ''));
            if (!$role || $c === '') continue;
            if ($role === 'assistant' && !empty($m['hatirlatma'])) {
                $c .= "\n[SİSTEM — " . $this->hatirlatmaNotu($m['hatirlatma']) . ']';
            }
            if ($role === 'assistant' && !empty($m['baglam'])) {
                $c .= "\n[SİSTEM — bu turdaki araç sonuçları, müşteri duymadı: " . $m['baglam'] . ']';
            }
            // Onceki musteri cumlelerindeki tarih/saat de KODLA cozulmus gitsin: aksi halde hizmet teyidi gibi
            // ara bir turdan sonra model gunu kendisi hesaplayip yaniliyordu ("cumartesi saat 2" -> "Cuma 20 Kasım",
            // saati de yeniden soruyordu; 2026-11-16 kayit #55).
            if ($role === 'user') $c = $this->sttDuzelt($c);
            if ($role === 'user' && strpos($c, '[SİSTEM') === false) $c .= $this->zamanNotu($c);
            $out[] = ['role' => $role, 'content' => $c];
        }
        // Anthropic: ilk mesaj user olmali. Karsilamayi (ad soyad sorusu dahil) ATMA: model ilk cevabin
        // isim oldugunu bilsin diye basina yapay bir "arama baglandi" turu ekle.
        if (!empty($out) && $out[0]['role'] !== 'user') array_unshift($out, ['role' => 'user', 'content' => '(arama bağlandı)']);
        return $out;
    }
}
