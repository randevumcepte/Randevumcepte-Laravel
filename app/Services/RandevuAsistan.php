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

        $key = config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY');
        if (!$key) {
            $this->teshis = 'anahtar_yok';
            return ['cevap' => 'Sizi bir yetkiliye aktarıyorum, lütfen hatta kalın.', 'aksiyon' => 'aktar', 'veri' => [], 'bitir' => false];
        }

        $messages = $this->gecmisMesajlari($gecmis);
        $messages[] = ['role' => 'user', 'content' => $metin];

        $aksiyon = null; $bitir = false; $randevuId = null; $cevap = '';

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
                $sonuclar[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $tu['id'] ?? '',
                    'content' => json_encode($sonuc, JSON_UNESCAPED_UNICODE),
                ];
                if ($aks) $aksiyon = $aks;
                if ($rid) $randevuId = $rid;
                if ($bit) $bitir = true;
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

        if ($cevap === '') $cevap = $this->garantiCevap($aksiyon);
        return [
            'cevap' => $this->ttsTemizle($cevap),
            'aksiyon' => $aksiyon,
            'veri' => ['randevu_id' => $randevuId],
            'bitir' => (bool) $bitir,
        ];
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
                'description' => 'Müşteri adres, konum ya da yol tarifi sorduğunda çağır: işletmenin yol tarifini müşterinin telefonuna mesaj (WhatsApp/SMS) olarak gönderir ve adresi döner. Adresi kısaca söyle, tarifi mesajla ilettiğini belirt. Bunun için AKTARMA.',
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

        return <<<PROMPT
Sen {$ad} işletmesinin SESLİ RANDEVU asistanısın. Telefonda, kısa ve doğal Türkçe konuşuyorsun. Çıktın düz metindir (TTS ile okunur): emoji, madde işareti, markdown KULLANMA. Her yanıt 1-2 cümle.

# AKIŞ (adımları atlama)
1. SELAMLAMA zaten TTS ile çalındı — TEKRAR ETME. İşletme adına "-da/-de/-a/-e/-ye" eki EKLEME. Arayan KAYITLI DEĞİLSE önce BAĞLAM'daki KAYIT ADIMI'nı tamamla; kayıtlıysa doğrudan niyete geç.
2. NİYET: yeni randevu mu, iptal mi, güncelleme mi, başka bir şey mi?
3. HİZMET: müşteri hizmet söyleyince hemen "hizmet_eslestir" aracını çağır (dediğini AYNEN ver). Hizmeti KENDİN tahmin etme. eslesme=tek ise onaylatmadan devam et; coklu ise seçenekleri kısaca oku ve seçtir; yok ise "o hizmetimiz yok" de ve dönen önerileri sun.
4. TARİH: "TARİH HARİTASI" tablosundan kesin ISO tarihi AL, kendin hesaplama. Belirsizse tek cümleyle teyit et.
5. PERSONEL: müşteri bir personel adı söylediyse (ör. "Ayşe Hanım'a", "Mehmet Bey olsun") PERSONELLER listesinden eşleştir ve personel_id'yi kullan; listede yoksa "o isimde personelimiz yok" de ve listeden say. Söylemediyse personel SORMA (uygun personel otomatik seçilir).
6. MÜSAİT SAAT: "musait_saatler" aracını hizmet_id + tarih ile çağır (saat/gün dilimi söylendiyse ver; personel söylendiyse personel_id ver). Dönen seçenekleri (en fazla 2) personel adıyla öner: "[saat]'te [personel] ile ya da [saat]'te ...". istenen_gun_dolu=true ise önce o günün dolu olduğunu söyle. Seçenek yoksa başka gün/dilim öner.
7. SAAT SEÇİMİ.
8. ONAY (yalnız burada, tek sefer): "[gün] [saat]'te [personel] ile [hizmet] için randevu, onaylıyor musunuz?"
9. Onay gelince "randevu_olustur" (seçilen seçeneğin tarih_saat ve personel_id'si + hizmet_id). ok=false ve saat dolu derse yeniden musait_saatler çağır ve yeni saat öner.
10. SONUÇ: "Randevunuz oluşturuldu, [gün] saat [saat]'te bekliyoruz."

İPTAL/GÜNCELLEME: önce "mevcut_randevular" ile randevuyu bul, müşteriye hangisini kastettiğini onaylat. İptalde "randevu_iptal". Güncellemede yeni gün/saat için "musait_saatler"i o randevunun hizmet_id ve personel_id'si ile çağır, onay al, sonra "randevu_guncelle". Yalnız mevcut_randevular'ın döndüğü id'leri kullan.

ADRES / YOL TARİFİ: müşteri adres, konum, "nasıl gelirim" sorarsa "yol_tarifi_gonder" çağır; adresi kısaca söyle ve yol tarifini mesajla gönderdiğini belirt. Bunun için aktarma yapma.

# ONAY KELİMELERİ
evet, tabii, olur, olsun, tamam, ok, okey, kabul, onaylıyorum, uygundur, peki, he, aynen, oldu = ONAY.
hayır, yok, istemiyorum, olmaz, vazgeçtim, başka = RET.

# ZAMAN
Bugün: {$bugun}. Tarihleri müşteriye Türkçe söyle (ör. "Perşembe, 7 Mayıs") ama araçlara ISO 8601 ver.
Saat kestirimi: "on dörtte"=14:00, "on dört buçukta"=14:30, "ikide/saat ikide"=14:00 (öğleden sonra varsayılan), "iki buçukta"=14:30, "sabah dokuz"=09:00, "akşam yedide"=19:00, "öğleden sonra üçte"=15:00. Belirsizse "sabah mı öğleden sonra mı?" diye sor.

# TARİH HARİTASI (HESAP YAPMA, TABLOYU KULLAN)
{$harita}

# AKTARMA (sadece bunlarda "aktar")
Müşteri açıkça operatör/insan/yetkili isterse YA DA randevu/iptal/güncelleme dışı konu (şikayet, fatura, ödeme) olursa. Hizmet bulunamadı diye AKTARMA — sormaya devam et.

# KIRMIZI ÇİZGİLER
Hizmeti kendin tahmin etme (daima hizmet_eslestir). coklu'da kendin seçme. 8. adım onayı almadan randevu_olustur çağırma. Saat ve personeli kendin uydurma (yalnız musait_saatler sonucundan). Tüm saatleri sıralama (2 tane öner). Bilgi uydurma.

# BAĞLAM
İşletme: {$ad}
Adres: {$adresSatir}
Müşteri telefonu: {$telSatir}
{$musteriSatir}
Hizmetler (yalnızca bağlam; eşleştirmeyi SEN yapma, hizmet_eslestir kullan):
{$hizmetSatir}
PERSONELLER (randevu takvimi açık olanlar):
{$personelSatir}
PROMPT;
    }

    /** Bugün + 14 gün: "GünAdı DD Ay = YYYY-MM-DD" (İstanbul, deterministik). */
    protected function gunHaritasi(): string
    {
        $gunAd = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $ayAd = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $out = '';
        $t = now('Europe/Istanbul')->startOfDay();
        for ($i = 0; $i < 15; $i++) {
            $g = (clone $t)->addDays($i);
            $etiket = $i === 0 ? ' (BUGÜN)' : ($i === 1 ? ' (YARIN)' : ($i >= 7 ? ' (haftaya)' : ''));
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
        $sec = array_map(function ($s) { unset($s['_m'], $s['_tam']); return $s; }, $sec);
        if (!$sec) return ['ok' => true, 'secenekler' => [], 'mesaj' => 'Önümüzdeki 14 günde uygun saat bulunamadı. Başka gün/dilim ya da başka personel öner.'];
        return [
            'ok' => true,
            'istenen_tarih' => $tarih,
            'istenen_gun_dolu' => $sec[0]['tarih'] !== $tarih,
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
                'randevuKaynak' => 'salon', 'easistan' => '1', 'durum' => (string) Randevular::ONAYLANDI,
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
        return ['ok' => true, 'randevu_id' => (int) $rid, 'tarih' => $tarih, 'saat' => $saat, 'personel' => $this->personelListesi()[$personelId] ?? null];
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
        return ['ok' => true, 'randevu_id' => $rid, 'tarih' => $tarih, 'saat' => $saat];
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
            case 'randevu': return 'Randevunuz oluşturuldu, sizi bekliyoruz. İyi günler dilerim.';
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
