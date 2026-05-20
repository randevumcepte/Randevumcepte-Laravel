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
        $this->resolveFirebaseProfile();

        $tokens = $this->findTokens();
        $sent = 0; $failed = 0;

        $deepLink = $this->buildDeepLink();
        $payloadExtra = array_merge([
            'type'      => $this->type,
            'deep_link' => $deepLink,
            'popup'     => $this->popup ? '1' : '0',
            'image'     => $this->imageUrl ?? '',
            'salon_id'  => (string)($this->salonId ?? ''),
            'randevu_id'=> (string)($this->randevuId ?? ''),
        ], array_map(function ($v) {
            return is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE);
        }, $this->extra));

        foreach ($tokens as $row) {
            try {
                $this->sendOne($row->bildirim_id, $payloadExtra, $row->platform);
                $sent++;
                BildirimKimlikleri::where('id', $row->id)->update([
                    'son_kullanim_tarihi' => now(),
                    'gonderim_hatalari'   => 0,
                ]);
            } catch (\Throwable $e) {
                $failed++;
                $msg = $e->getMessage();
                Log::warning('NotificationService send fail', [
                    'token_id' => $row->id,
                    'err'      => $msg,
                ]);
                // FCM "token gecersiz" hatalari: kademeli yerine HEMEN sil
                $invalidPatterns = [
                    'UNREGISTERED',
                    'NOT_FOUND',
                    'INVALID_ARGUMENT',
                    'registration-token-not-registered',
                    'invalid-registration-token',
                    'SENDER_ID_MISMATCH',
                ];
                $invalid = false;
                foreach ($invalidPatterns as $p) {
                    if (stripos($msg, $p) !== false) { $invalid = true; break; }
                }
                if ($invalid) {
                    BildirimKimlikleri::where('id', $row->id)->delete();
                } else {
                    $errCount = ($row->gonderim_hatalari ?? 0) + 1;
                    BildirimKimlikleri::where('id', $row->id)->update([
                        'gonderim_hatalari' => $errCount,
                        'aktif'             => $errCount < 5,
                    ]);
                }
            }
        }

        $this->logToDb($deepLink);

        return ['sent' => $sent, 'failed' => $failed, 'total' => count($tokens)];
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
            return BildirimKimlikleri::query()
                ->whereIn('bildirim_id', $this->rawTokens)
                ->where('aktif', true)
                ->whereNotNull('bildirim_id')
                ->where('bildirim_id', '!=', '')
                ->get();
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
            default:
                return $base . '/isletmeyonetim' . $sube;
        }
    }

    /**
     * Tek bir cihaza FCM v1 üzerinden gönderim.
     * Notification + data kombine: title/body bildirim olarak gözükür,
     * data alanında deep_link/type/image taşınır.
     */
    private function sendOne(string $deviceToken, array $data, ?string $platform = null): void
    {
        $accessToken = $this->getFcmAccessToken();
        $projectId = json_decode(file_get_contents(storage_path($this->firebaseJsonFile)), true)['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $highPriority = NotificationTypes::isHighPriority($this->type);

        $apns = [
            'headers' => [
                'apns-priority' => $highPriority ? '10' : '5',
            ],
            'payload' => [
                'aps' => array_filter([
                    // iOS: ios/Runner/ring.caf bundle'da. Uzantili yazilir.
                    'sound'             => 'ring.caf',
                    'mutable-content'   => $this->imageUrl ? 1 : null,
                    'content-available' => 1,
                ], function ($v) { return $v !== null; }),
            ],
        ];
        if ($this->imageUrl) {
            $apns['fcm_options'] = ['image' => $this->imageUrl];
        }

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
                        'image'      => $this->imageUrl,
                        // Android: res/raw/ring.mp3 -> uzantisiz 'ring'.
                        // Android 8+ kanal sesini kullanir; bu alan eski surumler icin.
                        'sound'      => 'ring',
                    ]),
                ],
                'apns' => $apns,
                'webpush' => $webpush,
            ],
        ];

        $client = new Client(['timeout' => 10]);
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ],
            'json' => $message,
            'http_errors' => true,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('FCM HTTP ' . $response->getStatusCode() . ' - ' . $response->getBody()->getContents());
        }
    }

    private function androidChannel(): string
    {
        // _v2: ring.mp3 custom sesi eklendiginde kanal ID'leri bump'landi.
        // Android'de bir kanalin sesi sonradan degistirilemedigi icin
        // uygulama tarafinda da yeni ID'lerle (rmc_*_v2) kanal olusturuluyor.
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
                $rows = BildirimKimlikleri::query()
                    ->whereIn('bildirim_id', $this->rawTokens)
                    ->where('aktif', true)
                    ->get(['user_id', 'isletme_yetkili_id']);
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

        $client = new Client(['timeout' => 10]);
        $response = $client->post($json['token_uri'], [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $cache[$key] = ['token' => $body['access_token'], 'exp' => $now + 3550];
        return $body['access_token'];
    }
}
