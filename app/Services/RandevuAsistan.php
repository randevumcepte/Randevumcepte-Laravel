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

    protected int $salonId;
    protected ?string $telefon;
    protected array $salon = [];        // ad, karsilama, hizmetler[]
    protected ?string $musteriAd = null;
    protected ?int $musteriId = null;    // bu salonun portfoyunde AKTIF musteri (karsilama ile ayni kural)
    /** Arayan numarasi gizli/gecersizdi ve kayitta telefon alindiysa: oturuma yazilsin diye kopru controller'i okur. */
    public ?string $yeniTelefon = null;
    protected ?array $_personeller = null;
    protected ?array $_paketler = null;
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
     * Kayitli musteri: "Sayın Ad Soyad, <isletme> hoş geldiniz. Ben yapay zeka asistanınızım, ..." (isim SORULMAZ).
     * Kayitsiz: karsilamada ad soyad (numara gizliyse telefon da) istenir -> musteri_kaydet.
     */
    public function karsilama(): string
    {
        // Restoran santrali ile ayni kalip: "<isletme> hoş geldiniz, ben yapay zeka asistanınızım. ..."
        $hosgeldin = $this->hosgeldinMetni();
        if ($this->musteriAd) {
            $paketCumle = $this->paketTeklifCumlesi();
            if ($paketCumle !== '') {
                return "Sayın {$this->musteriAd}, {$hosgeldin}. Ben yapay zeka asistanınızım. {$paketCumle} Ya da size nasıl yardımcı olabilirim?";
            }
            return "Sayın {$this->musteriAd}, {$hosgeldin}. Ben yapay zeka asistanınızım. Size nasıl yardımcı olabilirim?";
        }
        $ilk = mb_strtoupper(mb_substr($hosgeldin, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($hosgeldin, 1, null, 'UTF-8');
        if ($this->telNorm($this->telefon)) {
            return "{$ilk}, ben yapay zeka asistanınızım. Size yardımcı olabilmem için adınızı ve soyadınızı öğrenebilir miyim?";
        }
        return "{$ilk}, ben yapay zeka asistanınızım. Size yardımcı olabilmem için adınızı, soyadınızı ve telefon numaranızı öğrenebilir miyim?";
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

    public function hizmetSayisi(): int { return count($this->salon['hizmetler'] ?? []); }

    public function salonAdi(): string { return $this->salon['ad'] ?? ('Salon ' . $this->salonId); }

    /**
     * Ana beyin. $gecmis: [['role'=>'user'|'assistant','content'=>'...'], ...]
     * @return array{cevap:string,aksiyon:?string,veri:array,bitir:bool}
     */
    public function konus(string $metin, array $gecmis = []): array
    {
        $this->teshis = 'ok';
        $metin = trim($metin);
        if ($metin === '') {
            return ['cevap' => 'Buyurun, sizi dinliyorum.', 'aksiyon' => null, 'veri' => [], 'bitir' => false];
        }
        // KESIN AKTARMA: "beni işletmeye/yetkiliye bağlar mısın", "operatör", "biriyle görüşmek istiyorum" ->
        // LLM'e sormadan aktar (STT "işletmeye"yi "genişletme" duysa bile "bağlar mısın" yakalanir).
        if ($this->aktarmaIstegiMi($metin)) {
            Log::info('[SANTRAL-TUR] kesin aktarma', ['salon' => $this->salonId, 'metin' => $metin]);
            $this->teshis = 'aktar';
            return ['cevap' => 'Sizi hemen yetkili bir arkadaşımıza bağlıyorum, lütfen hatta kalın.', 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
        }

        $key = config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY');
        if (!$key) {
            $this->teshis = 'anahtar_yok';
            return ['cevap' => 'Sizi bir yetkiliye aktarıyorum, lütfen hatta kalın.', 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
        }

        $messages = $this->gecmisMesajlari($gecmis);
        $messages[] = ['role' => 'user', 'content' => $metin . $this->zamanNotu($metin)];

        $aksiyon = null; $bitir = false; $randevuId = null; $cevap = '';
        $isTamamlandi = false; // randevu/iptal/guncelleme/yol tarifi tamamlandi -> "Başka yardımcı olabileceğim..." sorulmali
        $yolTarifiGitti = false; // yol tarifi mesaji basariyla gonderildi -> sabit cumle
        $randevuHatasi = null;   // bu turda randevu_olustur/guncelle ok=false dondu (sebep)

        for ($deneme = 0; $deneme < 2; $deneme++) {
        for ($tur = 0; $tur < 6; $tur++) {
            $resp = $this->cagir($messages, $key);
            if (!$resp) { if ($this->teshis === 'ok') $this->teshis = 'cevap_yok'; break; }

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
            $messages[] = ['role' => 'assistant', 'content' => $content];

            $sonuclar = [];
            foreach ($toolUse as $tu) {
                [$sonuc, $aks, $rid, $bit] = $this->toolCalistir($tu['name'] ?? '', $tu['input'] ?? []);
                // Teshis: her arac cagrisi + sonucu (randevu neden olusmadi vb. sorulari loglardan cevaplamak icin)
                Log::info('[SANTRAL-ARAC] ' . ($tu['name'] ?? '?'), [
                    'salon' => $this->salonId, 'musteri' => $this->musteriId, 'tel' => $this->telefon,
                    'girdi' => $tu['input'] ?? [], 'sonuc' => mb_substr(json_encode($sonuc, JSON_UNESCAPED_UNICODE), 0, 1500),
                ]);
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
                if (in_array($tu['name'] ?? '', ['randevu_olustur', 'randevu_guncelle'], true) && empty($sonuc['ok'])) {
                    $randevuHatasi = (string) ($sonuc['hata'] ?? 'bilinmeyen hata');
                }
            }
            $messages[] = ['role' => 'user', 'content' => $sonuclar];

            // Terminal aksiyon: aktar/veda -> kapanis cumlesi icin bir tur daha don, sonra bitir.
            if ($aksiyon === 'aktar' || $aksiyon === 'veda') {
                $resp2 = $this->cagir($messages, $key);
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
                && preg_match('/randevu(nuz|nuzu)?\s+(talebiniz\s+)?(başarıyla\s+)?(oluşturul|güncellen|kaydedil|alın)/iu', $cevap);
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
            && preg_match('/randevu(nuz|nuzu)?\s+(talebiniz\s+)?(başarıyla\s+)?(oluşturul|güncellen|kaydedil|alın)/iu', $cevap)) {
            $cevap = 'Üzgünüm, randevunuzu şu an kaydedemedim. Başka bir saat deneyelim mi, yoksa sizi bir yetkiliye aktarayım mı?';
        }

        if ($cevap === '') $cevap = $this->garantiCevap($aksiyon);
        Log::info('[SANTRAL-TUR]', ['salon' => $this->salonId, 'musteri' => $this->musteriId, 'metin' => $metin, 'cevap' => $cevap, 'aksiyon' => $aksiyon]);
        // Yol tarifi gonderildiyse (ayni turda baska bir is yoksa) sabit, net cumle:
        if ($yolTarifiGitti && $aksiyon === null && !$bitir) {
            $cevap = 'Yol tarifini size mesaj olarak ilettim. Başka yardımcı olabileceğim bir konu var mı?';
        }
        if ($isTamamlandi && !$bitir && $aksiyon !== 'aktar') $cevap = $this->baskaYardimEkle($cevap);
        return [
            'cevap' => $this->ttsTemizle($cevap),
            'aksiyon' => $aksiyon,
            'veri' => ['randevu_id' => $randevuId],
            'bitir' => (bool) $bitir,
        ];
    }

    /**
     * Bir is tamamlandiginda kapanis sorusu GARANTI: "Size nasıl yardımcı olabilirim?" (karsilama sorusu)
     * tekrar edilmez, yerine "Başka yardımcı olabileceğim bir konu var mı?" sorulur.
     */
    protected function baskaYardimEkle(string $cevap): string
    {
        $c = preg_replace('/\s*(Peki,?\s*|Ya da\s*)?(size|sizlere)\s+(başka\s+)?(nasıl|ne konuda)\s+(yardımcı olabilirim|yardımcı olayım)\s*\??/iu', ' ', $cevap);
        $c = trim(preg_replace('/\s{2,}/u', ' ', $c));
        if (!preg_match('/başka\s+(bir\s+)?(konu|yardımcı|isteğiniz|bir şey)/iu', $c)) {
            $c = rtrim($c, " ,") . ($c !== '' && !preg_match('/[.!?]$/u', $c) ? '.' : '') . ' Başka yardımcı olabileceğim bir konu var mı?';
        }
        return trim($c);
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
                return [$this->ctrlCagir('hizmetEslestir', [
                    'salon_id' => $this->salonId, 'metin' => (string) ($in['metin'] ?? ''),
                ]), null, null, false];

            case 'musait_saatler':
                return [$this->musaitSaatlerPersonelli($in), null, null, false];

            case 'mevcut_randevular':
                return [$this->randevularim(), null, null, false];

            case 'musteri_kaydet':
                return [$this->musteriKaydet((string) ($in['ad_soyad'] ?? ''), (string) ($in['telefon'] ?? '')), null, null, false];

            case 'randevu_olustur':
                $d = $this->randevuOlusturPersonelli($in);
                $ok = !empty($d['ok']);
                return [$d, $ok ? 'randevu' : null, $ok ? ($d['randevu_id'] ?? null) : null, false];

            case 'randevu_iptal':
                $rid = $this->intId($in['randevu_id'] ?? null);
                // SAHIPLIK: yalniz ARAYANIN kendi randevusu (model yanlis/uydurma id verirse baskasininki iptal olmasin)
                if (!$this->randevuSahibiMi($rid)) return [$this->sahipDegilHata(), null, null, false];
                $d = $this->ctrlCagir('randevuIptal', ['salon_id' => $this->salonId, 'randevu_id' => $rid]);
                return [$d, !empty($d['ok']) ? 'iptal' : null, null, false];

            case 'randevu_guncelle':
                $d = $this->randevuGuncellePersonelli($in);
                return [$d, !empty($d['ok']) ? 'guncelle' : null, null, false];

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
                    'notlar' => ['type' => 'string'],
                ], 'required' => ['tarih_saat', 'hizmet_id', 'personel_id']],
            ],
            [
                'name' => 'randevu_iptal',
                'description' => 'Randevu iptal eder. Önce mevcut_randevular ile id öğren ve müşteriye onaylat.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'randevu_id' => ['type' => 'integer'],
                ], 'required' => ['randevu_id']],
            ],
            [
                'name' => 'randevu_guncelle',
                'description' => 'Randevu tarih/saatini değiştirir (personel ve hizmet aynı kalır). Önce mevcut_randevular ile randevuyu bul, sonra musait_saatler’i o randevunun hizmet_id ve personel_id’si ile çağırıp yeni saati müşteriye onaylat.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'randevu_id' => ['type' => 'integer'],
                    'yeni_tarih_saat' => ['type' => 'string', 'description' => 'ISO 8601'],
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
                'name' => 'veda',
                'description' => 'Müşteri görüşmeyi bitirmek istediğinde (teşekkür/hoşça kal) çağır; çağrı kapanır.',
                'input_schema' => ['type' => 'object', 'properties' => $bos],
            ],
        ];
    }

    // ---------------------------------------------------------------- LLM
    protected function cagir(array $messages, string $key): ?array
    {
        $body = [
            'model' => config('services.anthropic.model', 'claude-haiku-4-5'),
            'max_tokens' => 1000,
            'system' => [[
                'type' => 'text',
                'text' => $this->sistemPromptu(),
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'tools' => $this->toolTanimlari(),
            'messages' => $messages,
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
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
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $code !== 200) {
            $this->teshis = 'http_' . $code . ':' . substr((string) ($raw ?: $err), 0, 160);
            return null;
        }
        $j = json_decode($raw, true);
        return is_array($j) ? $j : null;
    }

    protected function sistemPromptu(): string
    {
        $ad = $this->salonAdi();
        $bugun = now('Europe/Istanbul')->format('Y-m-d');
        $harita = $this->gunHaritasi();

        $hizmetSatir = '';
        foreach (array_slice($this->salon['hizmetler'] ?? [], 0, 40) as $h) {
            $f = isset($h['fiyat']) && $h['fiyat'] ? ' (' . $h['fiyat'] . ' TL)' : '';
            $hizmetSatir .= '- ' . ($h['ad'] ?? '') . $f . "\n";
        }
        if ($hizmetSatir === '') $hizmetSatir = "(hizmet listesi boş — müşteriyi operatöre aktar)\n";

        $telBilinen = (bool) $this->telNorm($this->telefon);
        if ($this->musteriAd) {
            $musteriSatir = "Arayan bu işletmenin KAYITLI müşterisi: {$this->musteriAd}. Adını/soyadını/telefonunu SORMA; uygun yerde ismiyle hitap et.";
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
                . "- musteri_kaydet ok=false dönerse hatadaki eksik bilgiyi iste. Kayıt tamamlanmadan randevu_olustur ÇAĞIRMA.";
        }
        $telSatir = $telBilinen ? $this->telefon : 'bilinmiyor (gizli numara)';

        $personelSatir = '';
        foreach ($this->personelListesi() as $pid => $pad) $personelSatir .= "- {$pad} (personel_id {$pid})\n";
        if ($personelSatir === '') $personelSatir = "(randevu takvimi açık personel yok — randevu verilemez, müşteriyi operatöre aktar)\n";
        $adresSatir = trim((string) ($this->salon['adres'] ?? '')) ?: 'kayıtlı değil';
        $paketSatir = $this->paketSatirlari();

        return <<<PROMPT
Sen {$ad} işletmesinin SESLİ RANDEVU asistanısın. Telefonda, kısa ve doğal Türkçe konuşuyorsun. Çıktın düz metindir (TTS ile okunur): emoji, madde işareti, markdown KULLANMA. Her yanıt 1-2 cümle.

# AKIŞ (adımları atlama)
1. SELAMLAMA zaten TTS ile çalındı — TEKRAR ETME. İşletme adına "-da/-de/-a/-e/-ye" eki EKLEME. Arayan KAYITLI DEĞİLSE önce BAĞLAM'daki KAYIT ADIMI'nı tamamla; kayıtlıysa doğrudan niyete geç.
2. NİYET: yeni randevu mu, iptal mi, güncelleme mi, başka bir şey mi? (Müşterinin paketi varsa karşılamada paket teklif edildi: aşağıdaki PAKET AKIŞI'na bak.)
3. HİZMET: müşteri hizmet söyleyince hemen "hizmet_eslestir" aracını çağır (dediğini AYNEN ver). Hizmeti KENDİN tahmin etme. eslesme=tek ise onaylatmadan devam et; coklu ise seçenekleri kısaca oku ve seçtir; yok ise "o hizmetimiz yok" de ve dönen önerileri sun.
4. TARİH/SAAT: Müşteri mesajının sonunda "[SİSTEM zaman çözümü ...]" notu varsa tarih ve saati AYNEN oradan al (kendin hesaplama; "çarşamba 14", "önümüzdeki hafta perşembe 10", "10 aralık", "5 şubat" hepsi orada çözülmüş gelir). Not yoksa TARİH HARİTASI'nı kullan. Müşteri gün ve saati söylediyse saati TEKRAR SORMA, sabah/öğleden sonra diye SORMA.
5. PERSONEL: müşteri bir personel adı söylediyse (ör. "Ayşe Hanım'a", "Mehmet Bey olsun") PERSONELLER listesinden eşleştir ve personel_id'yi kullan; listede yoksa "o isimde personelimiz yok" de ve listeden say. Söylemediyse personel SORMA (uygun personel otomatik seçilir) — İSTİSNA: paketten randevuda uygun personeli say ve tercih sor (PAKET AKIŞI).
6. MÜSAİT SAAT: "musait_saatler" aracını hizmet_id + tarih ile çağır (müşteri saat söylediyse saat'i MUTLAKA ver; gün dilimi söylendiyse zaman_dilimi; personel söylendiyse personel_id).
   - tam_eslesme=true dönerse: istenen saat uygun → ALTERNATİF SUNMA, seçenek okuma; doğrudan 8. adımdaki onay cümlesine geç (gün için seçeneğin "gun" alanını kullan).
   - Aksi halde: müşteri saat söylediyse önce "O saat dolu" de, sonra dönen seçenekleri (en fazla 2) "gun" alanı ve personel adıyla öner. istenen_gun_dolu=true ise o günün dolu olduğunu söyle. Seçenek yoksa başka gün/dilim öner.
7. SAAT SEÇİMİ (yalnız seçenek sunulduysa).
8. ONAY (yalnız burada, tek sefer): "[gün] [saat]'te [personel] ile [hizmet] için randevu talebi oluşturayım mı?" ([gün] = seçeneğin "gun" alanı, ör. "Çarşamba, 18 Kasım")
9. Onay gelince "randevu_olustur" (seçilen seçeneğin tarih_saat ve personel_id'si + hizmet_id). ok=false ve saat dolu derse yeniden musait_saatler çağır ve yeni saat öner.
10. SONUÇ: Randevu TALEP olarak oluşturulur (işletme onaylar): "[gün] saat [saat] için [personel] ile randevu talebiniz oluşturuldu, işletmemiz onayladığında bilgilendirileceksiniz." randevu_olustur sonucunda bilgilendirme_gonderildi=true ise ardından "Size bilgilendirme mesajı ilettim." ekle; false ise mesajdan HİÇ bahsetme. En sonda MUTLAKA "Başka yardımcı olabileceğim bir konu var mı?" diye sor. Güncelleme ve iptal sonrasında da aynı şekilde (güncellemede bilgilendirme_gonderildi'ye göre) bu soruyu sor.
11. KAPANIŞ: Müşteri "yok, teşekkürler" derse "veda" çağır; yeni bir isteği varsa 2. adımdan devam et.

PAKET AKIŞI (BAĞLAM'da PAKETLER varsa): Karşılamada "paketiniz bulunmaktadır, randevu oluşturmak ister misiniz? Ya da size nasıl yardımcı olabilirim?" soruldu; müşteri pakete evet diyebilir ya da doğrudan başka bir istek söyleyebilir.
- Evet / olur / tabii / oluşturalım vb. derse paketten randevu: hizmet_eslestir ÇAĞIRMA, hizmet_id'yi PAKETLER'den al. Pakette birden fazla hizmet ya da birden fazla paket varsa hangisi için olduğunu sor.
- Hizmet belli olunca o hizmete UYGUN PERSONELİ PAKETLER'deki listeden adlarıyla say ve tercihini sor (ör. "Bu hizmet için Ayşe Hanım ve Zeynep Hanım uygun, tercih ettiğiniz biri var mı?"). Tek kişiyse onu söyle. "Fark etmez" derse personel_id verme. Listede olmayan personeli önerme.
- Sonra normal akış: tarih → musait_saatler (paket hizmet_id + seçilen personel_id) → saat → onay → randevu_olustur. Seans pakete otomatik bağlanır; sonuçta paket_seansi=true ise "paketinizden" de.
- Hayır derse ya da başka bir şey isterse paketi bırak, normal akışa geç.

İPTAL/GÜNCELLEME: önce "mevcut_randevular" ile randevuyu bul, müşteriye hangisini kastettiğini onaylat. İptalde "randevu_iptal". Güncellemede yeni gün/saat için "musait_saatler"i o randevunun hizmet_id ve personel_id'si ile çağır, onay al, sonra "randevu_guncelle". Yalnız mevcut_randevular'ın döndüğü id'leri kullan.

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
Müşteri açıkça operatör/insan/yetkili/işletmeye bağlanmak isterse ("beni bağlar mısınız", "işletmeye bağlayın", "biriyle görüşmek istiyorum") HİÇ SORU SORMADAN hemen "aktar" çağır — bunu hizmet sanma, hizmet_eslestir ÇAĞIRMA. YA DA randevu/iptal/güncelleme dışı konu (şikayet, fatura, ödeme) olursa. Hizmet bulunamadı diye AKTARMA — sormaya devam et.

# KIRMIZI ÇİZGİLER
Burası restoran DEĞİL, randevu hattı: kişi sayısı, masa, rezervasyon, sipariş SORMA. Her randevu arayan kişi içindir. Anlamsız/alakasız bir ifade duyarsan (ses tanıma hatası olabilir) tahmin yürütme, ne istediğini kısaca tekrar sor.
randevu_olustur ok=true DÖNMEDEN asla "randevunuz oluşturuldu" deme; ok=false ise hatayı müşteriye dürüstçe söyle. Hizmeti kendin tahmin etme (daima hizmet_eslestir). coklu'da kendin seçme. 8. adım onayı almadan randevu_olustur çağırma. Saat ve personeli kendin uydurma (yalnız musait_saatler sonucundan). Tüm saatleri sıralama (2 tane öner). Bilgi uydurma.

# BAĞLAM
İşletme: {$ad}
Adres: {$adresSatir}
Müşteri telefonu: {$telSatir}
{$musteriSatir}
Hizmetler (yalnızca bağlam; eşleştirmeyi SEN yapma, hizmet_eslestir kullan):
{$hizmetSatir}
PERSONELLER (randevu takvimi açık olanlar):
{$personelSatir}
PAKETLER (kullanılabilir): {$paketSatir}
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
        $d = $this->ctrlCagir('salonBilgi', ['salon_id' => $this->salonId]);
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
        }
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
                    foreach ($veren as $pid) if (isset($acik[$pid])) $personel[] = ['personel_id' => $pid, 'ad' => $acik[$pid]];
                    $hizmetler[] = ['hizmet_id' => $hid, 'ad' => trim((string) $h->hizmet_adi), 'kalan_seans' => $kalan, 'personeller' => $personel];
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

    /** "X paketiniz bulunmaktadır, bu paket için randevu oluşturmak ister misiniz?" (paket yoksa ''). */
    protected function paketTeklifCumlesi(): string
    {
        $p = $this->paketler();
        if (!$p) return '';
        $adlar = [];
        foreach ($p as $x) $adlar[$x['paket_adi']] = $this->paketAdiSoz($x['paket_adi']);
        $adlar = array_values($adlar);
        if (count($adlar) === 1) {
            return "{$adlar[0]} bulunmaktadır, bu paket için randevu oluşturmak ister misiniz?";
        }
        $son = array_pop($adlar);
        return implode(', ', $adlar) . " ve {$son} bulunmaktadır, bu paketlerden biri için randevu oluşturmak ister misiniz?";
    }

    /** "Lazer" -> "Lazer paketiniz"; adinda zaten "paket" geciyorsa ("Lazer Paketi") -> "Lazer Paketi". */
    protected function paketAdiSoz(string $ad): string
    {
        return preg_match('/paket/iu', $ad) ? $ad : "{$ad} paketiniz";
    }

    /** Prompt BAGLAM satirlari. */
    protected function paketSatirlari(): string
    {
        $p = $this->paketler();
        if (!$p) return 'yok';
        $out = '';
        foreach ($p as $x) {
            $out .= "\n- Paket: {$x['paket_adi']}";
            foreach ($x['hizmetler'] as $h) {
                $kalan = $h['kalan_seans'] === null ? 'süreli/sınırsız' : "{$h['kalan_seans']} seans kaldı";
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
                ->where('takvimde_gorunsun', 1)->orderBy('id')->get(['id', 'personel_adi']);
            foreach ($q as $p) {
                $ad = trim((string) $p->personel_adi);
                if ($ad !== '') $out[(int) $p->id] = $ad;
            }
        } catch (\Throwable $e) {
            Log::warning('RandevuAsistan personelListesi: ' . $e->getMessage());
        }
        return $this->_personeller = $out;
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
            if (!isset($plist[$personelId])) return ['ok' => false, 'hata' => 'Bu personelin randevu takvimi kapalı ya da bulunamadı. Müşteriye PERSONELLER listesinden başka biri olabileceğini söyle.'];
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
            if ($m) $secenek[] = ['tarih' => $m['tarih'], 'saat' => $m['saat'], 'personel_id' => $pid, 'personel' => $plist[$pid] ?? '', '_m' => $m, '_tam' => !empty($m['tam_istek'])];
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
        return [
            'ok' => true,
            'istenen_tarih' => $tarih,
            'istenen_gun_dolu' => $sec[0]['tarih'] !== $tarih,
            'istenen_saat_dolu' => (bool) $saat,
            'secenekler' => $sec,
        ];
    }

    /** Arayanin kendi gelecek randevulari (bekleyen + onayli), personel/hizmet bilgisiyle. */
    protected function randevularim(): array
    {
        if (!$this->musteriId) return ['ok' => true, 'randevular' => [], 'not' => 'Arayan bu işletmede kayıtlı değil; randevusu yok.'];
        $liste = [];
        $rs = Randevular::where('user_id', $this->musteriId)->where('salon_id', $this->salonId)
            ->whereDate('tarih', '>=', now('Europe/Istanbul')->toDateString())
            ->whereIn('durum', [Randevular::BEKLENIYOR, Randevular::ONAYLANDI])
            ->orderBy('tarih')->orderBy('saat')->limit(5)->get(['id', 'tarih', 'saat']);
        foreach ($rs as $r) {
            $rh = RandevuHizmetler::where('randevu_id', $r->id)->with(['hizmetler', 'personeller'])->get();
            $ilk = $rh->first();
            $liste[] = [
                'id' => (int) $r->id,
                'tarih' => substr((string) $r->tarih, 0, 10),
                'saat' => substr((string) $r->saat, 0, 5),
                'hizmet' => $rh->map(function ($x) { return optional($x->hizmetler)->hizmet_adi; })->filter()->implode(', '),
                'personel' => $ilk ? optional($ilk->personeller)->personel_adi : null,
                'hizmet_id' => $ilk ? (int) $ilk->hizmet_id : null,
                'personel_id' => $ilk && $ilk->personel_id ? (int) $ilk->personel_id : null,
            ];
        }
        return ['ok' => true, 'randevular' => $liste];
    }

    /** Randevu bu salonda ve ARAYANIN mi? (iptal/guncelle oncesi zorunlu) */
    protected function randevuSahibiMi(?int $randevuId): bool
    {
        if (!$randevuId || !$this->musteriId) return false;
        return Randevular::where('id', $randevuId)->where('salon_id', $this->salonId)
            ->where('user_id', $this->musteriId)->exists();
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
        return ['ok' => true, 'randevu_id' => (int) $rid, 'tarih' => $tarih, 'saat' => $saat, 'personel' => $this->personelListesi()[$personelId] ?? null, 'bilgilendirme_gonderildi' => $this->bilgilendirmeAcik(),
            // randevuekleguncelle paket seansini kendisi baglar; baglandiysa asistan "paketinizden" diyebilsin
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
        if (!$this->slotHalaBos((int) $ilk->personel_id, (int) $ilk->hizmet_id, $tarih, $saat)) {
            return ['ok' => false, 'hata' => 'Bu saat bu personel için dolu. musait_saatler ile (randevunun hizmet_id ve personel_id’si) yeni saat bulup öner.'];
        }
        try {
            $r = $this->randevuEkleGuncelleCagir([
                'salonid' => $this->salonId, 'randevu_id' => $rid, 'user_id' => $this->musteriId,
                'randevu_tarihi' => $tarih, 'randevu_saati' => $saat,
                'hizmetler' => $satirlar->map(function ($x) {
                    return ['hizmet_id' => $x->hizmet_id, 'personel_id' => $x->personel_id, 'oda_id' => $x->oda_id ?: '', 'cihaz_id' => $x->cihaz_id ?: '',
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
        return ['ok' => true, 'randevu_id' => $rid, 'tarih' => $tarih, 'saat' => $saat, 'bilgilendirme_gonderildi' => $this->bilgilendirmeAcik()];
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
            case 'randevu': return 'Randevu talebiniz oluşturuldu, işletmemiz onayladığında bilgilendirileceksiniz.';
            case 'iptal': return 'Randevunuz iptal edildi. Başka bir arzunuz var mı?';
            case 'guncelle': return 'Randevunuz güncellendi. Başka bir arzunuz var mı?';
            case 'aktar': return 'Sizi bir yetkiliye aktarıyorum, lütfen hatta kalın.';
            case 'veda': return 'Teşekkür ederiz, iyi günler dileriz.';
            default: return 'Tabii, size nasıl yardımcı olabilirim?';
        }
    }

    protected function ttsTemizle(string $m): string
    {
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
            $out[] = ['role' => $role, 'content' => $c];
        }
        // Anthropic: ilk mesaj user olmali. Karsilamayi (ad soyad sorusu dahil) ATMA: model ilk cevabin
        // isim oldugunu bilsin diye basina yapay bir "arama baglandi" turu ekle.
        if (!empty($out) && $out[0]['role'] !== 'user') array_unshift($out, ['role' => 'user', 'content' => '(arama bağlandı)']);
        return $out;
    }
}
