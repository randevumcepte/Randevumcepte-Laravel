<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\MusteriPortfoy;
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
     * Kayitli musteri: "Merhaba Ad Soyad, <isletme> hoş geldiniz. ..." (isim SORULMAZ).
     * Kayitsiz: karsilamada ad soyad (numara gizliyse telefon da) istenir -> musteri_kaydet.
     */
    public function karsilama(): string
    {
        $hosgeldin = $this->hosgeldinMetni();
        if ($this->musteriAd) {
            return "Merhaba {$this->musteriAd}, {$hosgeldin}. Size nasıl yardımcı olabilirim?";
        }
        if ($this->telNorm($this->telefon)) {
            return "Merhaba, {$hosgeldin}. Size yardımcı olabilmem için adınızı ve soyadınızı öğrenebilir miyim?";
        }
        return "Merhaba, {$hosgeldin}. Size yardımcı olabilmem için adınızı, soyadınızı ve telefon numaranızı öğrenebilir miyim?";
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
                return [$this->ctrlCagir('musaitSaatler', [
                    'salon_id' => $this->salonId,
                    'tarih' => (string) ($in['tarih'] ?? ''),
                    'zaman_dilimi' => $in['zaman_dilimi'] ?? null,
                    'hizmet_id' => $this->intId($in['hizmet_id'] ?? null),
                ]), null, null, false];

            case 'mevcut_randevular':
                return [$this->ctrlCagir('mevcutRandevular', [
                    'salon_id' => $this->salonId, 'telefon' => $this->telefon,
                ]), null, null, false];

            case 'musteri_kaydet':
                return [$this->musteriKaydet((string) ($in['ad_soyad'] ?? ''), (string) ($in['telefon'] ?? '')), null, null, false];

            case 'randevu_olustur':
                // Kayitsiz musteriye randevu YOK: once normal musteri kaydi (ad soyad + telefon).
                // (Aksi halde randevuOlustur "Telefon Müşterisi" adli kayit acar.)
                if (!$this->musteriId) {
                    return [['ok' => false, 'hata' => 'Müşteri kayıtlı değil. Önce adını ve soyadını alıp musteri_kaydet aracını çağır, sonra randevuyu oluştur.'], null, null, false];
                }
                $d = $this->ctrlCagir('randevuOlustur', [
                    'salon_id' => $this->salonId,
                    'telefon' => $this->telefon,
                    'tarih_saat' => (string) ($in['tarih_saat'] ?? ''),
                    'ad_soyad' => $this->musteriAd,
                    'hizmet_id' => $this->intId($in['hizmet_id'] ?? null),
                    'notlar' => $in['notlar'] ?? null,
                ]);
                $ok = !empty($d['ok']);
                return [$d, $ok ? 'randevu' : null, $ok ? ($d['randevu_id'] ?? null) : null, false];

            case 'randevu_iptal':
                $d = $this->ctrlCagir('randevuIptal', [
                    'salon_id' => $this->salonId, 'randevu_id' => $this->intId($in['randevu_id'] ?? null),
                ]);
                return [$d, !empty($d['ok']) ? 'iptal' : null, null, false];

            case 'randevu_guncelle':
                $d = $this->ctrlCagir('randevuGuncelle', [
                    'salon_id' => $this->salonId,
                    'randevu_id' => $this->intId($in['randevu_id'] ?? null),
                    'yeni_tarih_saat' => (string) ($in['yeni_tarih_saat'] ?? ''),
                ]);
                return [$d, !empty($d['ok']) ? 'guncelle' : null, null, false];

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
                'description' => 'Verilen tarih için boş randevu saatlerini getirir. Dönen saatlerden müşteriye yalnızca 2 tanesini öner, hepsini sıralama.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'tarih' => ['type' => 'string', 'description' => 'YYYY-MM-DD biçiminde tarih (TARİH HARİTASI tablosundan al)'],
                    'zaman_dilimi' => ['type' => 'string', 'enum' => ['sabah', 'ogle', 'ogleden_sonra', 'aksam'], 'description' => 'opsiyonel gün dilimi'],
                    'hizmet_id' => ['type' => 'integer', 'description' => 'opsiyonel hizmet id'],
                ], 'required' => ['tarih']],
            ],
            [
                'name' => 'mevcut_randevular',
                'description' => 'Arayan müşterinin gelecekteki randevularını listeler (iptal/güncelleme için önce bunu çağır).',
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
                'description' => 'Yeni randevu oluşturur. SADECE müşteri açıkça onayladıktan sonra çağır (onay adımını atlama). Müşteri kayıtlı değilse önce musteri_kaydet.',
                'input_schema' => ['type' => 'object', 'properties' => [
                    'tarih_saat' => ['type' => 'string', 'description' => 'ISO 8601, örn 2026-05-07T14:30:00'],
                    'hizmet_id' => ['type' => 'integer', 'description' => 'hizmet_eslestir’den gelen id'],
                    'notlar' => ['type' => 'string'],
                ], 'required' => ['tarih_saat']],
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
                'description' => 'Randevu tarih/saatini değiştirir. Önce mevcut_randevular ile id öğren ve müsait saati teyit et.',
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

        return <<<PROMPT
Sen {$ad} işletmesinin SESLİ RANDEVU asistanısın. Telefonda, kısa ve doğal Türkçe konuşuyorsun. Çıktın düz metindir (TTS ile okunur): emoji, madde işareti, markdown KULLANMA. Her yanıt 1-2 cümle.

# AKIŞ (adımları atlama)
1. SELAMLAMA zaten TTS ile çalındı — TEKRAR ETME. İşletme adına "-da/-de/-a/-e/-ye" eki EKLEME. Arayan KAYITLI DEĞİLSE önce BAĞLAM'daki KAYIT ADIMI'nı tamamla; kayıtlıysa doğrudan niyete geç.
2. NİYET: yeni randevu mu, iptal mi, güncelleme mi, başka bir şey mi?
3. HİZMET: müşteri hizmet söyleyince hemen "hizmet_eslestir" aracını çağır (dediğini AYNEN ver). Hizmeti KENDİN tahmin etme. eslesme=tek ise onaylatmadan devam et; coklu ise seçenekleri kısaca oku ve seçtir; yok ise "o hizmetimiz yok" de ve dönen önerileri sun.
4. TARİH: "TARİH HARİTASI" tablosundan kesin ISO tarihi AL, kendin hesaplama. Belirsizse tek cümleyle teyit et.
5. MÜSAİT SAAT: "musait_saatler" aracını çağır (müşteri gün dilimi dediyse zaman_dilimi ver). Dönen saatlerden müşteriye SADECE 2 tanesini öner; yoksa başka gün/dilim öner.
6. SAAT SEÇİMİ.
7. ONAY (yalnız burada, tek sefer): "[gün] [saat]'te [hizmet] için randevu, onaylıyor musunuz?"
8. Onay gelince "randevu_olustur" (hizmet_eslestir'den gelen hizmet_id ile).
9. SONUÇ: "Randevunuz oluşturuldu, [gün] saat [saat]'te bekliyoruz."

İPTAL/GÜNCELLEME: önce "mevcut_randevular" ile randevuyu bul, müşteriye hangisini kastettiğini onaylat, sonra "randevu_iptal" / "randevu_guncelle".

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
Hizmeti kendin tahmin etme (daima hizmet_eslestir). coklu'da kendin seçme. 7. adım onayı almadan randevu_olustur çağırma. Tüm saatleri sıralama (2 tane öner). Bilgi uydurma.

# BAĞLAM
İşletme: {$ad}
Müşteri telefonu: {$telSatir}
{$musteriSatir}
Hizmetler (yalnızca bağlam; eşleştirmeyi SEN yapma, hizmet_eslestir kullan):
{$hizmetSatir}
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
