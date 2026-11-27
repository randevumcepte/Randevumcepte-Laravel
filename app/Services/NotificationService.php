<?php

namespace App\Services;

use App\BildirimKimlikleri;
use App\Bildirimler;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Tek noktadan FCM gönderimi + log + token bakımı.
 *
 * Kullanım:
 *   NotificationService::toCustomer($userId, $salonId)
 *       ->type(NotificationTypes::APPOINTMENT_APPROVED)
 *       ->title('Randevu onaylandı')
 *       ->body('Randevunuz kuaförümüz tarafından onaylandı.')
 *       ->deepLink('appointment_detail', ['randevu_id' => $randevuId])
 *       ->send();
 *
 *   NotificationService::toCustomer($userId, $salonId)
 *       ->type(NotificationTypes::DISCOUNT)
 *       ->title('Size özel %20 indirim!')
 *       ->body('Bugün gelene özel kampanya.')
 *       ->image('https://cdn.../kampanya.png')
 *       ->popup()
 *       ->deepLink('campaign_detail', ['kampanya_id' => 17])
 *       ->send();
 */
class NotificationService
{
    /** @var string */ private $kullaniciTipi;   // musteri | personel | yetkili | raw
    /** @var int|null */ private $userId = null;
    /** @var int|null */ private $personelId = null;
    /** @var int|null */ private $yetkiliId = null;
    /** @var int|null */ private $salonId = null;
    /** @var int|null */ private $randevuId = null;
    /** @var array|null */ private $rawTokens = null;

    /** @var string */ private $type = NotificationTypes::SYSTEM_ANNOUNCEMENT;
    /** @var string */ private $title = '';
    /** @var string */ private $body = '';
    /** @var string|null */ private $imageUrl = null;
    /** @var string|null */ private $deepLinkRoute = null;
    /** @var array */ private $deepLinkParams = [];
    /** @var array */ private $extra = [];
    /** @var bool */ private $popup = false;
    /** @var bool */ private $karaListeMuaf = false;
    /** @var bool */ private $cevaptanSonra = false;
    /** @var bool */ private $sessiz = false;      // personel bu turu Bildirimlerim'de sessize aldi
    /** @var string|null */ private $firebaseJsonFile = 'app/firebase/randevumcepte-uygulamala-5ff4d-8a85c43832c1.json';

    /** @return static */
    public static function toCustomer(int $userId, ?int $salonId = null): self
    {
        $i = new self();
        $i->kullaniciTipi = 'musteri';
        $i->userId = $userId;
        $i->salonId = $salonId;
        return $i;
    }

    /** @return static */
    public static function toStaff(int $personelId, ?int $salonId = null): self
    {
        $i = new self();
        $i->kullaniciTipi = 'personel';
        $i->personelId = $personelId;
        $i->salonId = $salonId;
        return $i;
    }

    /** @return static */
    public static function toOwner(int $yetkiliId, ?int $salonId = null): self
    {
        $i = new self();
        $i->kullaniciTipi = 'yetkili';
        $i->yetkiliId = $yetkiliId;
        $i->salonId = $salonId;
        return $i;
    }

    /**
     * Eski bildirimgonder() kopruleri icin: dogrudan token listesine gonderir.
     * Kullanici lookup yapmaz, sadece verilen bildirim_id'lere FCM atar.
     * Pasif/bos tokenlar ve FCM'in reddettigi (OneSignal kalintilari) otomatik elenir.
     *
     * @return static
     */
    public static function forTokens(array $tokens, ?int $salonId = null): self
    {
        $i = new self();
        $i->kullaniciTipi = 'raw';
        $i->rawTokens = array_values(array_unique(array_filter(
            $tokens,
            function ($t) { return is_string($t) && trim($t) !== ''; }
        )));
        $i->salonId = $salonId;
        return $i;
    }

    public function type(string $type): self        { $this->type = $type; return $this; }
    public function title(string $title): self      { $this->title = $title; return $this; }
    public function body(string $body): self        { $this->body = $body; return $this; }
    public function image(?string $url): self       { $this->imageUrl = $url; return $this; }
    public function popup(bool $on = true): self    { $this->popup = $on; return $this; }
    public function randevu(?int $id): self         { $this->randevuId = $id; return $this; }
    public function firebaseFile(string $f): self   { $this->firebaseJsonFile = $f; return $this; }
    /** Kara liste suzgecinden muaf: "kara listeye alindiniz" / sifre teslim / OTP gibi
     *  musterinin kendi aksiyonuyla iliskili bildirim push'lari icin. */
    public function karaListeMuaf(bool $on = true): self { $this->karaListeMuaf = $on; return $this; }

    /**
     * Web isteginde gonderimi cevap kullaniciya gittikten sonraya birakir (terminate). Kullanici push'u
     * beklemez. send() sonucu {ertelendi:true, sent:0} doner: push ulasti mi'ye gore SMS'e dusen
     * cagiranlar bunu KULLANMAMALI. Konsolda etkisizdir (hemen gonderir).
     */
    public function cevaptanSonra(bool $on = true): self { $this->cevaptanSonra = $on; return $this; }

    public function deepLink(string $route, array $params = []): self
    {
        $this->deepLinkRoute = $route;
        $this->deepLinkParams = $params;
        return $this;
    }

    public function extra(array $data): self
    {
        $this->extra = array_merge($this->extra, $data);
        return $this;
    }

    /**
     * Asıl gönderim. Tüm uygun cihaz token'larını çeker, FCM'e yollar,
     * bildirimler tablosuna log düşer, başarısız token'ları pasifleştirir.
     *
     * @return array{sent:int, failed:int, total:int}
     */
    public function send(): array
    {
        if ($this->cevaptanSonra && !app()->runningInConsole()) {
            $kopya = clone $this;
            $kopya->cevaptanSonra = false;
            app()->terminating(function () use ($kopya) {
                try {
                    $kopya->send();
                } catch (\Throwable $e) {
                    Log::warning('[PUSH] cevaptan sonra gonderim hatasi', ['err' => $e->getMessage()]);
                }
            });

            return ['sent' => 0, 'failed' => 0, 'total' => 0, 'ertelendi' => true];
        }

        $this->resolveFirebaseProfile();

        // KARA LISTE kapisi — sadece MUSTERI hedefli push etkilenir, salon_id bilinir olmali.
        // karaListeMuaf() ile iliskili push'lar bypass eder (kara liste bildirim, OTP vb.).
        if (!$this->karaListeMuaf && $this->kullaniciTipi === 'musteri' && $this->userId && $this->salonId) {
            if (\App\Services\KaraListeServisi::engelliMi($this->salonId, null, $this->userId, null)) {
                Log::info('[PUSH] kara liste — gonderim atlandi', [
                    'user_id' => $this->userId, 'salon_id' => $this->salonId, 'type' => $this->type,
                ]);
                return ['sent' => 0, 'failed' => 0, 'total' => 0];
            }
        }

        // Kampanya / firsat bildirimleri: musteri kapattiysa gitmez; 21:00-09:00 arasi ertelenir (09:00'da gider).
        // Randevu, odeme, seans gibi bilgilendirmeler etkilenmez.
        if (AkilliBildirim::kampanyaTuruMu($this->type) && in_array($this->kullaniciTipi, ['musteri', 'raw'], true)) {
            if ($this->kullaniciTipi === 'musteri' && $this->userId && !AkilliBildirim::kampanyaIzni((int) $this->userId)) {
                return ['sent' => 0, 'failed' => 0, 'total' => 0, 'kampanya_izni_yok' => true];
            }
            if ($sonra = AkilliBildirim::sessizSaatBitisi()) {
                try {
                    AkilliBildirim::ertele($this->salonId, $this->durum(), $sonra);
                    return ['sent' => 0, 'failed' => 0, 'total' => 0, 'ertelendi' => true];
                } catch (\Throwable $e) {
                    Log::warning('[PUSH] sessiz saat ertelemesi yapilamadi, hemen gonderiliyor: ' . $e->getMessage());
                }
            }
        }

        // Bildirimlerim: personel bu turu sessize aldiysa bildirim yine gider, ses/titresim olmaz
        if ($this->kullaniciTipi === 'personel' || $this->kullaniciTipi === 'yetkili') {
            $this->sessiz = PersonelBildirimSessiz::sessizMi($this->kullaniciTipi === 'personel' ? $this->personelId : $this->yetkiliId, $this->type);
        }

        $tokens = $this->findTokens();
        $sent = 0; $failed = 0; $hatalar = [];

        // Mobil (iOS/Android) tokenlar salonun firebase_profile projesinde uretilir;
        // WEB tokenlari ise DAIMA tek web projesinde (config/firebase_web.php -> .env,
        // = firebase_projects.default = 5ff4d) uretilir. Salonun profili baska bir
        // projeye isaret etse bile web tokenini o projeden gondermek SENDER_ID_MISMATCH
        // verir ve asagidaki dongu tokeni SILER. Bu yuzden platforma gore proje sec.
        $mobileFile = $this->firebaseJsonFile;
        $webFile    = config('firebase_projects.default', $mobileFile);
        // "Genel" gibi tek app_bundle'i cok isletme paylasan ORTAK uygulamalarda
        // Firebase projesi salon-bazli (firebase_profile) degil, token'in KENDI
        // app_bundle'ina gore secilir. Haritada olmayan bundle'lar salon-bazli
        // mobileFile'a duser (beyaz-etiket marka appler — davranis degismez).
        $bundleMap = config('firebase_bundle_projects', []);

        $deepLink = $this->buildDeepLink();
        $payloadExtra = array_merge([
            'type'      => $this->type,
            'deep_link' => $deepLink,
            'popup'     => $this->popup ? '1' : '0',
            'image'     => $this->imageUrl ?? '',
            'salon_id'  => (string)($this->salonId ?? ''),
            'randevu_id'=> (string)($this->randevuId ?? ''),
            'sessiz'    => $this->sessiz ? '1' : '0',
            'aksiyonlar'=> $this->randevuYanitliMi() ? 'randevu_yanit' : '',
        ], array_map(function ($v) {
            return is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE);
        }, $this->extra));

        // Cihazlara PARALEL gonderim (en fazla 10 es zamanli). Eskiden sirayla, her token icin yeni
        // HTTPS baglantisiyla gonderiliyordu: randevu kaydi/iptali PHP iscisini 20+ sn kilitleyip
        // yogun saatte tum isletmelerde zaman asimina yol aciyordu (10 Kas 2026).
        $satirlar = [];
        foreach ($tokens as $row) {
            $satirlar[] = $row;
        }
        $sonuclar = [];
        $istekler = function () use ($satirlar, $webFile, $bundleMap, $mobileFile, $payloadExtra) {
            foreach ($satirlar as $i => $row) {
                yield $i => function () use ($row, $webFile, $bundleMap, $mobileFile, $payloadExtra) {
                    // Web tokenini web projesinden gonder. Mobil tokenlari once KENDI
                    // app_bundle'ina gore coz (ortak/genel app); haritada yoksa salon
                    // projesi (mobileFile).
                    if ($row->platform === 'web') {
                        $this->firebaseJsonFile = $webFile;
                    } else {
                        $tokenBundle = $row->app_bundle ?? '';
                        $this->firebaseJsonFile =
                            (!empty($tokenBundle) && !empty($bundleMap[$tokenBundle]))
                                ? $bundleMap[$tokenBundle]
                                : $mobileFile;
                    }
                    // Musteri dili: bu cihazin musterisi yabanci dil sectiyse baslik/metin o dilde
                    $asil = [$this->title, $this->body];
                    try {
                        if (!empty($row->user_id) && $this->kullaniciTipi !== 'personel' && $this->kullaniciTipi !== 'yetkili' && MusteriDili::acikMi($this->salonId)) {
                            $this->title = (string) MusteriDili::mesaj($this->salonId, $row->user_id, null, $asil[0], 'push');
                            $this->body = (string) MusteriDili::mesaj($this->salonId, $row->user_id, null, $asil[1], 'push');
                        } elseif (!empty($row->isletme_yetkili_id) && MusteriDili::acikMi($this->salonId)) {
                            // Personel kendi dilini sectiyse
                            $this->title = (string) MusteriDili::personelMetni($this->salonId, $row->isletme_yetkili_id, $asil[0]);
                            $this->body = (string) MusteriDili::personelMetni($this->salonId, $row->isletme_yetkili_id, $asil[1]);
                        }
                        // Yeni Android uygulamasi (aksiyon_v1) hatirlatmayi kendisi cizer: bildirimde
                        // "Geliyorum / Gecikecegim" dugmeleri. Eski surumler ve iPhone normal bildirim alir.
                        // Acil durum vanasi: .env PUSH_DUGMELI=0 -> herkes normal bildirim alir (uygulama guncellemesiz)
                        $dugmeli = (string) env('PUSH_DUGMELI', '1') !== '0' && $this->randevuYanitliMi() && ($row->platform ?? '') === 'android'
                            && stripos((string) ($row->yetenek ?? ''), 'aksiyon_v1') !== false;
                        return $this->sendOneAsync($row->bildirim_id, $payloadExtra, $row->platform, $dugmeli);
                    } catch (\Throwable $e) {
                        return \GuzzleHttp\Promise\rejection_for($e);
                    } finally {
                        [$this->title, $this->body] = $asil;
                    }
                };
            }
        };
        (new \GuzzleHttp\Pool(self::httpClient(), $istekler(), [
            'concurrency' => 10,
            'fulfilled'   => function ($cevap, $i) use (&$sonuclar) { $sonuclar[$i] = null; },
            'rejected'    => function ($neden, $i) use (&$sonuclar) {
                $sonuclar[$i] = $neden instanceof \Throwable ? $neden : new \Exception((string) $neden);
            },
        ]))->promise()->wait();

        foreach ($satirlar as $i => $row) {
            $e = array_key_exists($i, $sonuclar) ? $sonuclar[$i] : new \Exception('FCM sonuc yok');
            if ($e === null) {
                $sent++;
                BildirimKimlikleri::where('id', $row->id)->update([
                    'son_kullanim_tarihi' => now(),
                    'gonderim_hatalari'   => 0,
                ]);
            } else {
                $failed++;
                $msg = $e->getMessage();
                // Teshis icin FCM'in tam cevabi (Guzzle mesaji govdeyi kirpar)
                $govde = ($e instanceof \GuzzleHttp\Exception\RequestException && $e->getResponse()) ? (string) $e->getResponse()->getBody() : '';
                $hatalar[] = mb_substr($govde !== '' ? $govde : $msg, 0, 400) . ' [' . ($row->platform ?? '?') . ' ' . ($row->app_bundle ?? '') . ']';
                Log::warning('NotificationService send fail', [
                    'token_id' => $row->id,
                    'err'      => $msg,
                ]);
                switch (self::hataTuru($msg . ' ' . $govde)) {
                    case 'token':
                        // Google "bu cihaz kaydi artik gecersiz" dedi (uygulama silindi / token yenilendi)
                        BildirimKimlikleri::where('id', $row->id)->delete();
                        break;
                    case 'ayar':
                        // Yanlis proje / APNs anahtari / yetki: token SAGLAM, sorun bizde. Asla silme;
                        // teshis icin kaydet (php artisan push:teshis gosterir).
                        self::ayarHatasiKaydet($this->salonId, $this->firebaseJsonFile, $govde !== '' ? $govde : $msg, $row);
                        break;
                    case 'gecici':
                    case 'icerik':
                        // Google gecici hata / bildirim icerigi hatasi: cihazla ilgisi yok, dokunma
                        break;
                    default:
                        $errCount = ($row->gonderim_hatalari ?? 0) + 1;
                        BildirimKimlikleri::where('id', $row->id)->update([
                            'gonderim_hatalari' => $errCount,
                            'aktif'             => $errCount < 5,
                        ]);
                }
            }
        }

        $this->logToDb($deepLink);

        return ['sent' => $sent, 'failed' => $failed, 'total' => count($tokens), 'hatalar' => $hatalar];
    }

    /**
     * FCM hata cevabini siniflandirir (Firebase'in onerisi):
     *   token  : UNREGISTERED / gecersiz kayit metni -> cihaz kaydi silinir
     *   ayar   : SENDER_ID_MISMATCH (yanlis proje), APNs anahtari, yetki -> token SILINMEZ
     *   gecici : 429 / 5xx / zaman asimi -> dokunulmaz
     *   icerik : INVALID_ARGUMENT ama token'dan bahsetmiyor (bildirim govdesi hatali) -> dokunulmaz
     *   diger  : hata sayaci artar (5 ardisik hatada pasif)
     * Eskiden SENDER_ID_MISMATCH ve tum INVALID_ARGUMENT'ler cihazi siliyordu: yanlis projeyle
     * gonderen tek bir is, o markanin personelinin cihaz kayitlarini sessizce siliyordu.
     */
    public static function hataTuru(string $metin): string
    {
        foreach (['SENDER_ID_MISMATCH', 'THIRD_PARTY_AUTH_ERROR', 'THIRD_PARTY_PERMISSION_DENIED', 'InvalidApnsCredential',
                  'Invalid APNs credential', 'APNS_AUTH_ERROR', 'PERMISSION_DENIED', 'UNAUTHENTICATED'] as $p) {
            if (stripos($metin, $p) !== false) return 'ayar';
        }
        foreach (['UNREGISTERED', 'registration-token-not-registered', 'invalid-registration-token',
                  'not a valid FCM registration token', 'not a valid registration token'] as $p) {
            if (stripos($metin, $p) !== false) return 'token';
        }
        foreach (['QUOTA_EXCEEDED', 'UNAVAILABLE', 'INTERNAL', 'RESOURCE_EXHAUSTED', 'cURL error', 'timed out',
                  'Connection refused', 'Could not resolve', 'FCM HTTP 5', ' 500 ', ' 502 ', ' 503 ', ' 504 ', ' 429 '] as $p) {
            if (stripos($metin, $p) !== false) return 'gecici';
        }
        if (stripos($metin, 'INVALID_ARGUMENT') !== false) return 'icerik';
        return 'diger';
    }

    /** Ayar hatasini loglar ve teshis icin saklar (salon basina son hata, 7 gun). */
    private static function ayarHatasiKaydet($salonId, $dosya, string $metin, $row): void
    {
        Log::error('[PUSH] FIREBASE AYAR HATASI (cihaz kaydi korundu)', [
            'salon_id' => $salonId, 'proje_dosyasi' => $dosya, 'token_id' => $row->id ?? null,
            'app_bundle' => $row->app_bundle ?? null, 'platform' => $row->platform ?? null, 'hata' => mb_substr($metin, 0, 300),
        ]);
        try {
            \Illuminate\Support\Facades\Cache::put('push_ayar_hatasi:' . ((int) $salonId), [
                'zaman' => date('Y-m-d H:i:s'), 'proje_dosyasi' => $dosya, 'app_bundle' => $row->app_bundle ?? null,
                'platform' => $row->platform ?? null, 'hata' => mb_substr($metin, 0, 300),
            ], 60 * 24 * 7); // L5.6: dakika
        } catch (\Throwable $e) {
        }
    }

    /**
     * Salon'a ozel firebase_profile varsa ilgili JSON dosyasini sec.
     * Profile bos, tanimsiz veya config'de yoksa default'a duser.
     * firebaseFile() ile manuel set edilmisse ona dokunulmaz.
     */
    private function resolveFirebaseProfile(): void
    {
        if (!$this->salonId) return;
        try {
            $profile = BildirimKimlikleri::query() // hafif: salonlar yerine direkt sorgu
                ->getConnection()
                ->table('salonlar')
                ->where('id', $this->salonId)
                ->value('firebase_profile');
            if (empty($profile)) return;
            $path = config("firebase_projects.{$profile}");
            if (empty($path)) {
                Log::warning('NotificationService: bilinmeyen firebase_profile', [
                    'salon_id' => $this->salonId,
                    'profile'  => $profile,
                ]);
                return;
            }
            $this->firebaseJsonFile = $path;
        } catch (\Throwable $e) {
            Log::warning('NotificationService: firebase_profile resolve hata', [
                'salon_id' => $this->salonId,
                'err'      => $e->getMessage(),
            ]);
        }
    }

    private function findTokens()
    {
        // Raw token modu: forTokens() ile gelen bildirim_id'leri DB'den bul, kullanici lookup yapma.
        if ($this->kullaniciTipi === 'raw') {
            if (empty($this->rawTokens)) return collect();
            $raw = BildirimKimlikleri::query()
                ->whereIn('bildirim_id', $this->rawTokens)
                ->where('aktif', true)
                ->whereNotNull('bildirim_id')
                ->where('bildirim_id', '!=', '');

            // Brand izolasyonu raw mode'da da gecerli: salon X'in app_bundle'ina
            // ait olmayan cihazlara gonderme. Tokens listesi disardan gelse bile
            // alici salonla baglantili olmali (yetkili/personelin baska brand'deki
            // kayitlari elenir).
            if ($this->salonId) {
                $brandBundle = \App\Salonlar::where('id', $this->salonId)->value('app_bundle');
                if (!empty($brandBundle)) {
                    $raw->where('app_bundle', $brandBundle);
                }
            }
            return $raw->get();
        }

        $q = BildirimKimlikleri::query()->where('aktif', true);

        if ($this->kullaniciTipi === 'musteri') {
            $q->where('user_id', $this->userId);
        } elseif ($this->kullaniciTipi === 'personel') {
            $q->where('isletme_yetkili_id', $this->personelId);
        } elseif ($this->kullaniciTipi === 'yetkili') {
            // Yetkili (salon sahibi) personeller tablosunda yetkili_id ile temsil edilir.
            // Mevcut isletme_yetkili_id alanı personeller.id (yani salon kullanıcısı) -- aynı şema.
            $q->where('isletme_yetkili_id', $this->yetkiliId);
        }

        // White-label brand izolasyonu: salon X icin push, sadece o salonun
        // app_bundle'ini yuklu olan cihazlara gitsin. Ayni kullanici/personel
        // baska bir brand build'inde de login oldu ise oraya pushu sizdirma.
        // salon_id yerine app_bundle ile filtreliyoruz cunku musteri kaydinda
        // salon_id NULL gelebilirken, app_bundle her cihaz kaydinda dolu.
        if ($this->salonId) {
            $brandBundle = \App\Salonlar::where('id', $this->salonId)->value('app_bundle');
            if (!empty($brandBundle)) {
                $q->where('app_bundle', $brandBundle);
            }
        }

        // Eski OneSignal-only kayıtlarda token_tipi olmayabilir; sadece bildirim_id dolu olanları al.
        $q->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '');

        return $q->get();
    }

    private function buildDeepLink(): ?string
    {
        if (!$this->deepLinkRoute) return null;
        $params = http_build_query($this->deepLinkParams);
        return $params ? "{$this->deepLinkRoute}?{$params}" : $this->deepLinkRoute;
    }

    /**
     * Mobile deep_link route adini web URL'sine cevirir (FCM webpush click).
     * Bilinmeyen route'lar /isletmeyonetim'e dusur.
     */
    private function buildClickActionUrl(string $deepLink): string
    {
        $base = rtrim(config('app.url', 'https://app.randevumcepte.com.tr'), '/');
        $route = $this->deepLinkRoute;
        $p = $this->deepLinkParams;
        $sube = isset($p['salon_id']) ? '?sube=' . $p['salon_id'] : '';

        switch ($route) {
            case 'appointment_detail':
                return $base . '/isletmeyonetim/randevular' . $sube;
            case 'wheel':
                return $base . '/isletmeyonetim/cark' . $sube;
            case 'campaign_detail':
                return $base . '/isletmeyonetim/kampanyalar' . $sube;
            case 'payment_received':
                return $base . '/isletmeyonetim/tahsilatlar' . $sube;
            case 'survey':
                return $base . '/isletmeyonetim/anketler' . $sube;
            case 'assistant_calls':
                return $base . '/isletmeyonetim/santral' . $sube . '#asistan-gorusmeleri';
            case 'wa_assistant':
                return $base . '/isletmeyonetim/whatsapp-asistan' . $sube . (!empty($p['oturum_id']) ? ($sube ? '&' : '?') . 'sohbet=' . (int) $p['oturum_id'] : '');
            default:
                return $base . '/isletmeyonetim' . $sube;
        }
    }

    /**
     * Tek bir cihaza FCM v1 üzerinden gönderim.
     * Notification + data kombine: title/body bildirim olarak gözükür,
     * data alanında deep_link/type/image taşınır.
     */
    /** Paylasilan HTTP istemcisi: baglanti yeniden kullanilir; kisa sureler isciyi uzun kilitlemesin. */
    private static function httpClient(): Client
    {
        static $client = null;
        if ($client === null) {
            $client = new Client(['connect_timeout' => 3, 'timeout' => 6]);
        }

        return $client;
    }

    /** @return \GuzzleHttp\Promise\PromiseInterface */
    private function sendOneAsync(string $deviceToken, array $data, ?string $platform = null, bool $uygulamaCizer = false)
    {
        $accessToken = $this->getFcmAccessToken();
        static $projeler = [];
        if (!isset($projeler[$this->firebaseJsonFile])) {
            $projeler[$this->firebaseJsonFile] = json_decode(file_get_contents(storage_path($this->firebaseJsonFile)), true)['project_id'];
        }
        $projectId = $projeler[$this->firebaseJsonFile];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $highPriority = NotificationTypes::isHighPriority($this->type);

        $apns = [
            'headers' => [
                'apns-priority' => $highPriority ? '10' : '5',
            ],
            'payload' => [
                'aps' => array_filter([
                    // iOS: ios/Runner/ring.caf bundle'da. Uzantili yazilir.
                    'sound'             => $this->sessiz ? null : 'ring.caf',
                    'mutable-content'   => $this->imageUrl ? 1 : null,
                    'content-available' => 1,
                ], function ($v) { return $v !== null; }),
            ],
        ];
        if ($this->imageUrl) {
            $apns['fcm_options'] = ['image' => $this->imageUrl];
        }
        // Ayni randevunun bildirimleri telefonda birikmesin: yenisi eskisinin yerine gecer
        // (olusturuldu -> saati degisti -> hatirlatma). Android 'tag', iOS 'apns-collapse-id'.
        $birlestir = $this->randevuId ? 'randevu_' . (int) $this->randevuId : null;
        if ($birlestir) $apns['headers']['apns-collapse-id'] = $birlestir;

        // Web Push (FCM Web SDK) bloku.
        // Tarayici tokenlari icin webpush.notification kullanilir; aksi halde
        // sadece data mesaji olarak gelir, banner gostermez.
        $webpush = [
            'headers' => [
                'Urgency' => $highPriority ? 'high' : 'normal',
            ],
            'notification' => array_filter([
                'title' => $this->title,
                'body'  => $this->body,
                'icon'  => $this->imageUrl ?: '/public/img/logo.png',
                'image' => $this->imageUrl,
                // Aynı tag ile yenisi geldiginde eskisini yerine yazar (spam onleme)
                'tag'   => $this->type,
                'silent' => $this->sessiz ? true : null,
            ]),
        ];
        // Tiklayinca derin link
        $deepLinkUrl = $this->buildDeepLink();
        if ($deepLinkUrl) {
            $webpush['fcm_options'] = [
                'link' => $this->buildClickActionUrl($deepLinkUrl),
            ];
        }

        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => array_filter([
                    'title' => $this->title,
                    'body'  => $this->body,
                    'image' => $this->imageUrl,
                ]),
                'data' => $data,
                'android' => [
                    'priority' => $highPriority ? 'HIGH' : 'NORMAL',
                    'notification' => array_filter([
                        'channel_id' => $this->androidChannel(),
                        'tag'        => $birlestir,
                        'image'      => $this->imageUrl,
                        // Android: res/raw/ring.mp3 -> uzantisiz 'ring'.
                        // Android 8+ kanal sesini kullanir; bu alan eski surumler icin.
                        'sound'      => $this->sessiz ? null : 'ring',
                    ]),
                ],
                'apns' => $apns,
                'webpush' => $webpush,
            ],
        ];

        if ($uygulamaCizer) {
            // Yalniz veri: uygulamanin arka plan isleyicisi dugmeli bildirimi kendisi gosterir
            unset($message['message']['notification'], $message['message']['webpush']);
            $message['message']['android'] = ['priority' => 'HIGH'];
            $message['message']['data'] = array_merge($data, ['title' => (string) $this->title, 'body' => (string) $this->body, 'goster' => '1',
                'kanal' => $this->androidChannel()]);
        }

        return self::httpClient()->postAsync($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ],
            'json' => $message,
            'http_errors' => true,
        ])->then(function ($response) {
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('FCM HTTP ' . $response->getStatusCode() . ' - ' . $response->getBody()->getContents());
            }

            return $response;
        });
    }

    /** Musteriye giden randevu hatirlatmasi: "Geliyorum / Gecikecegim" cevabi alinabilir */
    private function randevuYanitliMi(): bool
    {
        return $this->kullaniciTipi === 'musteri' && $this->randevuId
            && in_array($this->type, [NotificationTypes::APPOINTMENT_REMINDER, NotificationTypes::APPOINTMENT_REMINDER_HOUR], true);
    }

    /** Ertelenecek bildirimin tum alanlari (bekleyen_bildirimler.veri) */
    public function durum(): array
    {
        return ['kullaniciTipi' => $this->kullaniciTipi, 'userId' => $this->userId, 'personelId' => $this->personelId,
            'yetkiliId' => $this->yetkiliId, 'salonId' => $this->salonId, 'randevuId' => $this->randevuId, 'rawTokens' => $this->rawTokens,
            'type' => $this->type, 'title' => $this->title, 'body' => $this->body, 'imageUrl' => $this->imageUrl,
            'deepLinkRoute' => $this->deepLinkRoute, 'deepLinkParams' => $this->deepLinkParams, 'extra' => $this->extra,
            'popup' => $this->popup, 'karaListeMuaf' => $this->karaListeMuaf];
    }

    /** durum()'dan geri kurar (ertelenen kampanya sabah gonderilirken) */
    public static function durumdan(array $d): self
    {
        $i = new self();
        foreach (['kullaniciTipi', 'userId', 'personelId', 'yetkiliId', 'salonId', 'randevuId', 'rawTokens', 'type', 'title', 'body',
                  'imageUrl', 'deepLinkRoute', 'deepLinkParams', 'extra', 'popup', 'karaListeMuaf'] as $k) {
            if (array_key_exists($k, $d) && $d[$k] !== null) $i->$k = $d[$k];
        }
        return $i;
    }

    private function androidChannel(): string
    {
        // _v2: ring.mp3 custom sesi eklendiginde kanal ID'leri bump'landi.
        // Android'de bir kanalin sesi sonradan degistirilemedigi icin
        // uygulama tarafinda da yeni ID'lerle (rmc_*_v2) kanal olusturuluyor.
        // Bildirimlerim'de sessize alinan tur: sessiz kanal (uygulamanin yeni surumunde var)
        if ($this->sessiz)                                   return 'rmc_sessiz_v1';
        if (NotificationTypes::isPopup($this->type))         return 'rmc_promo_v2';
        if (NotificationTypes::isHighPriority($this->type))  return 'rmc_important_v2';
        return 'rmc_default_v2';
    }

    private function logToDb(?string $deepLink): void
    {
        // Raw token modu: token listesinden unique alici cikar, her birine ayri satir at.
        // Boylece Bildirimler tablosu hedef bazinda dolu kalir (orphan kayit olmaz).
        if ($this->kullaniciTipi === 'raw') {
            if (empty($this->rawTokens)) return;
            try {
                $rowsQ = BildirimKimlikleri::query()
                    ->whereIn('bildirim_id', $this->rawTokens)
                    ->where('aktif', true);

                // Brand izolasyonu inbox yazimi icin de gerekli: yanlis brand'deki
                // cihaz satirindan user_id/isletme_yetkili_id turetip inbox'a yazma.
                if ($this->salonId) {
                    $brandBundle = \App\Salonlar::where('id', $this->salonId)->value('app_bundle');
                    if (!empty($brandBundle)) {
                        $rowsQ->where('app_bundle', $brandBundle);
                    }
                }
                $rows = $rowsQ->get(['user_id', 'isletme_yetkili_id']);
                $users = [];
                $personeller = [];
                foreach ($rows as $r) {
                    if (!empty($r->user_id)) {
                        $users[(int) $r->user_id] = true;
                    } elseif (!empty($r->isletme_yetkili_id)) {
                        $personeller[(int) $r->isletme_yetkili_id] = true;
                    }
                }
                if (empty($users) && empty($personeller)) {
                    // Hicbir aktif token bulunamadi -> hedefsiz tek satir at (eski davranis)
                    $this->writeBildirimlerRow($deepLink, null, null);
                    return;
                }
                foreach (array_keys($users) as $uid) {
                    $this->writeBildirimlerRow($deepLink, $uid, null);
                }
                foreach (array_keys($personeller) as $pid) {
                    $this->writeBildirimlerRow($deepLink, null, $pid);
                }
            } catch (\Throwable $e) {
                Log::warning('Bildirim log (raw) yazilamadi: ' . $e->getMessage());
            }
            return;
        }

        $this->writeBildirimlerRow($deepLink, $this->userId, $this->personelId);
    }

    private function writeBildirimlerRow(?string $deepLink, ?int $userId, ?int $personelId): void
    {
        try {
            $b = new Bildirimler();
            // Tum field assignment'lari Schema::hasColumn ile sar; farkli
            // ortamlarda eski/yeni sema oldugunda her hangi bir eksik kolon
            // butun save'i fail ettirmesin (butonlar, baslik gibi alanlar
            // bazi installation'larda yok).
            if (\Schema::hasColumn('bildirimler', 'salon_id'))    $b->salon_id    = $this->salonId;
            if (\Schema::hasColumn('bildirimler', 'user_id'))     $b->user_id     = $userId;
            if (\Schema::hasColumn('bildirimler', 'personel_id')) $b->personel_id = $personelId;
            if (\Schema::hasColumn('bildirimler', 'aciklama'))    $b->aciklama    = $this->body;
            if (\Schema::hasColumn('bildirimler', 'url'))         $b->url         = $this->imageUrl;
            if (\Schema::hasColumn('bildirimler', 'img_src'))     $b->img_src     = $this->imageUrl;
            if (\Schema::hasColumn('bildirimler', 'tarih_saat'))  $b->tarih_saat  = date('Y-m-d H:i:s');
            if (\Schema::hasColumn('bildirimler', 'okundu'))      $b->okundu      = false;
            if (\Schema::hasColumn('bildirimler', 'butonlar'))    $b->butonlar    = json_encode([], JSON_UNESCAPED_UNICODE);
            if (\Schema::hasColumn('bildirimler', 'randevu_id'))  $b->randevu_id  = $this->randevuId;
            if (\Schema::hasColumn('bildirimler', 'baslik'))      $b->baslik      = $this->title;

            // Yeni kolonlar — migration sonrası dolacak. Mevcut değilse PDO hata vermesin diye try/catch.
            if (\Schema::hasColumn('bildirimler', 'tip'))        $b->tip = $this->type;
            if (\Schema::hasColumn('bildirimler', 'deep_link'))  $b->deep_link = $deepLink;
            if (\Schema::hasColumn('bildirimler', 'image_url'))  $b->image_url = $this->imageUrl;
            if (\Schema::hasColumn('bildirimler', 'popup'))      $b->popup = $this->popup;
            if (\Schema::hasColumn('bildirimler', 'extra_data')) $b->extra_data = json_encode($this->extra, JSON_UNESCAPED_UNICODE);

            $b->save();
        } catch (\Throwable $e) {
            Log::warning('Bildirim log yazılamadı: ' . $e->getMessage());
        }
    }

    /** FCM access token (cache'le). */
    private function getFcmAccessToken(): string
    {
        static $cache = [];
        $key = $this->firebaseJsonFile;
        if (isset($cache[$key]) && $cache[$key]['exp'] > time() + 60) {
            return $cache[$key]['token'];
        }
        // Istekler arasi onbellek: php-fpm'de static her istekte sifirlanir; her push'ta Google'dan
        // yeni token aliniyordu (istek basina fazladan bir HTTPS cagrisi).
        $onbellekAnahtari = 'fcm_erisim_token:' . md5($key);
        try {
            $kayitli = \Illuminate\Support\Facades\Cache::get($onbellekAnahtari);
            if (is_array($kayitli) && ($kayitli['exp'] ?? 0) > time() + 60) {
                $cache[$key] = $kayitli;
                return $kayitli['token'];
            }
        } catch (\Throwable $e) {
        }

        $jsonPath = storage_path($this->firebaseJsonFile);
        $json = json_decode(file_get_contents($jsonPath), true);

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now = time();
        $claim = [
            'iss'   => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $json['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];
        $b64 = function ($d) {
            return rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
        };
        $data = $b64(json_encode($header)) . '.' . $b64(json_encode($claim));
        openssl_sign($data, $signature, $json['private_key'], OPENSSL_ALGO_SHA256);
        $jwt = $data . '.' . $b64($signature);

        $response = self::httpClient()->post($json['token_uri'], [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $cache[$key] = ['token' => $body['access_token'], 'exp' => $now + 3550];
        try {
            \Illuminate\Support\Facades\Cache::put($onbellekAnahtari, $cache[$key], 55); // L5.6: dakika
        } catch (\Throwable $e) {
        }
        return $body['access_token'];
    }
}
