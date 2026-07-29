<?php

namespace App\Http\Controllers;

use App\BildirimKimlikleri;
use App\Bildirimler;
use App\Services\NotificationService;
use App\Services\NotificationTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Yeni nesil bildirim API katmanı. Mevcut ApiController bildirim
 * endpoint'lerini bozmaz; üzerine ek olarak çalışır.
 */
class NotificationApiController extends Controller
{
    /**
     * POST /api/v1/bildirim/cihaz-kaydet
     *
     * Body:
     *  - token           : FCM cihaz token'ı
     *  - platform        : android | ios
     *  - kullanici_tipi  : musteri | personel | yetkili
     *  - user_id         : musteri ise users.id
     *  - personel_id     : personel ise salon_personelleri.id
     *  - yetkili_id      : yetkili ise user.id (salon sahibi)
     *  - salon_id        : opsiyonel, bağlı olduğu salon
     *  - cihaz           : opsiyonel cihaz fingerprint
     *  - app_bundle      : opsiyonel
     */
    public function cihazKaydet(Request $request)
    {
        $request->validate([
            'token'          => 'required|string|min:20',
            'kullanici_tipi' => 'required|in:musteri,personel,yetkili',
            'platform'       => 'nullable|in:android,ios,web',
        ]);

        $token = $request->input('token');
        $tip   = $request->input('kullanici_tipi');
        $cihaz = $request->input('cihaz');

        // Kullanici + cihaz kombinasyonu icin onceki tokenlar gecersizdir; sil
        if ($cihaz) {
            $owner = BildirimKimlikleri::query();
            if ($tip === 'musteri') {
                $owner->where('user_id', $request->input('user_id'));
            } elseif ($tip === 'personel') {
                $owner->where('isletme_yetkili_id', $request->input('personel_id'));
            } elseif ($tip === 'yetkili') {
                // yetkili_id'yi sonradan Personeller.id'ye cevirdigimiz icin burada her ikisini de tara
                $yId = $request->input('yetkili_id');
                $sId = $request->input('salon_id');
                $pId = ($yId && $sId)
                    ? \App\Personeller::where('yetkili_id', $yId)->where('salon_id', $sId)->value('id')
                    : null;
                $owner->where(function($q) use($pId, $yId){
                    $q->where('isletme_yetkili_id', $pId ?: -1)
                      ->orWhere('isletme_yetkili_id', $yId ?: -1);
                });
            }
            $owner->where('cihaz', $cihaz)
                  ->where('bildirim_id', '!=', $token)
                  ->delete();
        }

        // Bu token icin halihazirda bir kayit var mi? Varsa onu kullan, digerlerini sil
        $row = BildirimKimlikleri::where('bildirim_id', $token)->first();
        if ($row) {
            BildirimKimlikleri::where('bildirim_id', $token)
                ->where('id', '!=', $row->id)->delete();
        } else {
            $row = new BildirimKimlikleri();
        }

        $row->bildirim_id = $token;
        $row->cihaz       = $cihaz;
        $row->app_bundle  = $request->input('app_bundle');

        if (Schema::hasColumn('bildirim_kimlikleri', 'platform'))       $row->platform = $request->input('platform');
        if (Schema::hasColumn('bildirim_kimlikleri', 'token_tipi'))     $row->token_tipi = 'fcm';
        if (Schema::hasColumn('bildirim_kimlikleri', 'kullanici_tipi')) $row->kullanici_tipi = $tip;
        if (Schema::hasColumn('bildirim_kimlikleri', 'salon_id'))       $row->salon_id = $request->input('salon_id');
        if (Schema::hasColumn('bildirim_kimlikleri', 'aktif'))          $row->aktif = true;
        if (Schema::hasColumn('bildirim_kimlikleri', 'gonderim_hatalari')) $row->gonderim_hatalari = 0;

        // Eski şema alanları (geri uyum). Her iki alani da önce null'la, sonra
        // tip'e göre set et. Aksi halde ayni cihaz farklı rolle login olunca
        // eski rol değeri (user_id veya isletme_yetkili_id) satırda kalıyordu.
        $row->user_id = null;
        $row->isletme_yetkili_id = null;
        if ($tip === 'musteri') {
            $row->user_id = $request->input('user_id');
        } elseif ($tip === 'personel') {
            $row->isletme_yetkili_id = $request->input('personel_id');
        } elseif ($tip === 'yetkili') {
            // Yetkili kayıtlarında da isletme_yetkili_id alanı aslında
            // salon_personelleri.id'yi tutmalı (randevu push lookup'ları bu id'yi arıyor).
            // Gelen yetkili_id (= isletme_yetkilileri.id) + salon_id ile personel kaydını bul.
            $yetkiliId = $request->input('yetkili_id');
            $salonId   = $request->input('salon_id');
            $personelId = null;
            if ($yetkiliId && $salonId) {
                $personelId = \App\Personeller::where('yetkili_id', $yetkiliId)
                    ->where('salon_id', $salonId)
                    ->value('id');
            }
            $row->isletme_yetkili_id = $personelId ?: $yetkiliId;
        }

        $row->save();

        return response()->json(['success' => true, 'id' => $row->id]);
    }

    /**
     * POST /api/v1/bildirim/cihaz-sil
     * Body: token
     * Logout sırasında çağrılır.
     */
    public function cihazSil(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        BildirimKimlikleri::where('bildirim_id', $request->input('token'))
            ->update(['aktif' => false]);
        return response()->json(['success' => true]);
    }

    /**
     * POST /api/v1/bildirim/test
     *
     * Yetkili panelinden tek bir kişiye/kendisine test bildirimi göndermek için.
     * Body: kullanici_tipi, user_id|personel_id|yetkili_id, baslik, mesaj,
     *       tip (NotificationTypes), image (opsiyonel), popup (bool), deep_link (string)
     */
    public function test(Request $request)
    {
        $request->validate([
            'kullanici_tipi' => 'required|in:musteri,personel,yetkili',
            'baslik'         => 'required|string',
            'mesaj'          => 'required|string',
        ]);

        $tip = $request->input('kullanici_tipi');
        $salonId = $request->input('salon_id');

        switch ($tip) {
            case 'musteri':
                $svc = NotificationService::toCustomer((int)$request->input('user_id'), $salonId);
                break;
            case 'personel':
                $svc = NotificationService::toStaff((int)$request->input('personel_id'), $salonId);
                break;
            case 'yetkili':
                $svc = NotificationService::toOwner((int)$request->input('yetkili_id'), $salonId);
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Geçersiz kullanici_tipi'], 400);
        }

        $svc->type($request->input('tip', NotificationTypes::SYSTEM_ANNOUNCEMENT))
            ->title($request->input('baslik'))
            ->body($request->input('mesaj'))
            ->image($request->input('image'))
            ->popup((bool)$request->input('popup', false));

        if ($dl = $request->input('deep_link')) {
            $svc->deepLink($dl, (array)$request->input('deep_link_params', []));
        }

        return response()->json($svc->send());
    }

    /**
     * POST /api/v1/bildirim/test-cihaz
     *
     * Teshis endpointi: bir kullaniciya + app_bundle esitiligine gore
     * bildirim_kimlikleri kayitlarini listeler VE test push'u atar.
     * "Bildirimler calismiyor" seklindeki sikayetlerde ilk basvurulacak uc.
     *
     * Body:
     *  - user_id    : (int) hedef kullanici (musteri user_id ya da yetkili yetkili_id)
     *  - app_bundle : (string) hangi brand build (ornek: com.randevumcepte.salooncadde)
     *  - baslik     : (ops) push basligi (default: "Test Bildirimi")
     *  - mesaj      : (ops) push body (default: "Bu bir test bildirimidir")
     *
     * Response ornegi:
     *  {
     *    "success": true,
     *    "arama": { "user_id": 46120, "app_bundle": "com.randevumcepte.salooncadde", ... },
     *    "cihaz_sayisi": 2,
     *    "cihazlar": [ { id, kullanici_tipi, salon_id, app_bundle, platform, aktif,
     *                    son_kullanim_tarihi, gonderim_hatalari, token_kisa,
     *                    created_at, updated_at } ],
     *    "salon_esleme": { "app_bundle": "...", "salon_id_listesi": [391,392] },
     *    "push_sonuc": { "sent": 1, "failed": 1, "total": 2 },
     *    "not": "cihaz yoksa Flutter cihaz-kaydet cagrilmamis demektir"
     *  }
     */
    public function testCihaz(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer',
            'app_bundle' => 'required|string',
        ]);

        $userId    = (int) $request->input('user_id');
        $appBundle = trim((string) $request->input('app_bundle'));
        $baslik    = (string) $request->input('baslik', 'Test Bildirimi');
        $mesaj     = (string) $request->input('mesaj', 'Bu bir test bildirimidir');

        // 1) app_bundle'a hangi salonlar bagli?
        $salonIdListesi = \App\Salonlar::where('app_bundle', $appBundle)->pluck('id')->toArray();
        $salonIdIcinPush = !empty($salonIdListesi) ? (int) $salonIdListesi[0] : null;

        // 2) Cihaz kayitlarini cek — hem musteri (user_id) hem yetkili (isletme_yetkili_id)
        //    Ayni kisi hem musteri hem personel olabilir; ayni user_id degeri iki farkli
        //    role'de kayit olabilir. Ikisini de listeleyelim ki eksiksiz teshis olsun.
        $q = BildirimKimlikleri::query()
            ->where('app_bundle', $appBundle)
            ->where(function ($w) use ($userId) {
                $w->where('user_id', $userId)
                  ->orWhere('isletme_yetkili_id', $userId);
            })
            ->orderBy('id', 'desc');

        $cihazlar = $q->get()->map(function ($r) {
            return [
                'id'                => $r->id,
                'kullanici_tipi'    => $r->kullanici_tipi,
                'user_id'           => $r->user_id,
                'isletme_yetkili_id'=> $r->isletme_yetkili_id,
                'salon_id'          => $r->salon_id,
                'app_bundle'        => $r->app_bundle,
                'platform'          => $r->platform,
                'aktif'             => (bool) $r->aktif,
                'son_kullanim_tarihi' => $r->son_kullanim_tarihi,
                'gonderim_hatalari' => $r->gonderim_hatalari,
                'token_kisa'        => $r->bildirim_id ? substr($r->bildirim_id, 0, 24) . '...' : null,
                'created_at'        => (string) $r->created_at,
                'updated_at'        => (string) $r->updated_at,
            ];
        })->values();

        // 3) Test push at (varsa)
        $aktifTokenlar = $q->where('aktif', true)
            ->whereNotNull('bildirim_id')
            ->where('bildirim_id', '!=', '')
            ->pluck('bildirim_id')->toArray();

        $pushSonuc = null;
        if (!empty($aktifTokenlar)) {
            try {
                $pushSonuc = NotificationService::forTokens($aktifTokenlar, $salonIdIcinPush)
                    ->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                    ->title($baslik)
                    ->body($mesaj)
                    ->send();
            } catch (\Throwable $e) {
                $pushSonuc = ['hata' => $e->getMessage()];
            }
        }

        // 4) Teshis notu
        $not = null;
        if ($cihazlar->isEmpty()) {
            $not = 'Bu user_id + app_bundle ile hicbir cihaz kaydi yok. '
                 . 'Muhtemel sebep: mobil app henuz /api/v1/bildirim/cihaz-kaydet cagirmadi '
                 . '(izin verilmedi, FCM token gelmedi, veya login sonrasi registerForUser tetiklenmedi).';
        } elseif (empty($aktifTokenlar)) {
            $not = 'Cihaz kaydi var ama hicbiri aktif degil (5+ hata sonrasi pasiflesmis olabilir) '
                 . 'veya bildirim_id bos. Token yenilenmesi icin uygulamada yeniden login gerekebilir.';
        } elseif (empty($salonIdListesi)) {
            $not = 'app_bundle salonlar tablosunda hicbir salon ile eslesmiyor. '
                 . 'Salon.app_bundle kolonu bos veya farkli yazilmis olabilir. '
                 . 'Brand izolasyon filtresi bu durumda uygulanmaz, push cikar; ama salon_id bagi kurulamaz.';
        } elseif ($pushSonuc && isset($pushSonuc['sent']) && (int) $pushSonuc['sent'] === 0) {
            $not = 'Cihaz bulundu, push atildi ama hicbiri basarili degil. FCM token gecersiz olabilir '
                 . '(uygulama silinip yeniden yuklendi mi?). BildirimController::bildirimGonder ve '
                 . 'NotificationService::send invalid-token temizligi calisir; sonraki denemede yeniden '
                 . 'kayit gerekebilir.';
        } else {
            $not = 'Test basarili. Push cihaza ulasmadiysa: (a) sistem bildirim izni kapali, '
                 . '(b) iOS ise arka planda restart gerekebilir, (c) foreground da suppress edilmis olabilir.';
        }

        return response()->json([
            'success'      => true,
            'arama'        => [
                'user_id'    => $userId,
                'app_bundle' => $appBundle,
                'baslik'     => $baslik,
                'mesaj'      => $mesaj,
            ],
            'salon_esleme' => [
                'app_bundle'      => $appBundle,
                'salon_id_listesi'=> $salonIdListesi,
                'push_icin_kullanilan_salon_id' => $salonIdIcinPush,
            ],
            'cihaz_sayisi' => $cihazlar->count(),
            'cihazlar'     => $cihazlar,
            'aktif_token_sayisi' => count($aktifTokenlar),
            'push_sonuc'   => $pushSonuc,
            'not'          => $not,
        ]);
    }

    /**
     * GET /api/v1/bildirim/liste
     * Query: kullanici_tipi, user_id|personel_id|yetkili_id, salon_id (ops), appBundle (ops)
     *
     * Brand izolasyonu: musteri user'i birden fazla salonun musterisi olabilir.
     * Ceren Ceviz app'inde Vionna'nin inbox mesajlari gorunmesin diye
     * salon_id verilmezse appBundle uzerinden salonlar sinirlanir.
     * Master app (com.randevumcepte.randevumcepte) icin filtre atlanir.
     */
    public function liste(Request $request)
    {
        $tip = $request->input('kullanici_tipi');
        $q = Bildirimler::query()->orderBy('tarih_saat', 'desc');

        if ($tip === 'musteri') {
            $q->where('user_id', $request->input('user_id'));
        } elseif ($tip === 'personel') {
            $q->where('personel_id', $request->input('personel_id'));
        } elseif ($tip === 'yetkili') {
            $q->where('salon_id', $request->input('salon_id'));
        }

        $this->applyBrandInboxFilter($q, $request);

        return response()->json($q->limit(200)->get());
    }

    /**
     * salon_id > appBundle > filtre yok (master) sirasiyla inbox brand filtresi.
     * orWhereNull('salon_id') tutulur: eski (salon_id NULL) legacy kayitlar
     * gecis surecinde silinmeden kutuya dusmeye devam etsin.
     */
    private function applyBrandInboxFilter($q, Request $request): void
    {
        if ($salonId = $request->input('salon_id')) {
            $q->where(function ($w) use ($salonId) {
                $w->where('salon_id', $salonId)->orWhereNull('salon_id');
            });
            return;
        }
        $appBundle = $request->input('appBundle');
        if (!empty($appBundle) && $appBundle !== 'com.randevumcepte.randevumcepte') {
            $salonIds = \App\Salonlar::where('app_bundle', $appBundle)->pluck('id')->toArray();
            if (!empty($salonIds)) {
                $q->where(function ($w) use ($salonIds) {
                    $w->whereIn('salon_id', $salonIds)->orWhereNull('salon_id');
                });
            }
        }
    }

    /**
     * POST /api/v1/bildirim/okundu
     * Body: bildirim_id (id) veya bildirim_ids (array)
     */
    public function okundu(Request $request)
    {
        $ids = (array)($request->input('bildirim_ids') ?? [$request->input('bildirim_id')]);
        $ids = array_filter($ids);
        if (empty($ids)) return response()->json(['success' => false, 'message' => 'id yok'], 400);

        Bildirimler::whereIn('id', $ids)->update(['okundu' => true]);
        return response()->json(['success' => true, 'guncellenen' => count($ids)]);
    }

    /**
     * GET /api/v1/bildirim/okunmamis-sayi
     * Query: kullanici_tipi, user_id|personel_id|yetkili_id, salon_id (ops), appBundle (ops)
     * Brand izolasyonu liste() ile ayni mantik (applyBrandInboxFilter).
     */
    public function okunmamisSayi(Request $request)
    {
        $tip = $request->input('kullanici_tipi');
        $q = Bildirimler::query()->where('okundu', false);

        if ($tip === 'musteri') {
            $q->where('user_id', $request->input('user_id'));
        } elseif ($tip === 'personel') {
            $q->where('personel_id', $request->input('personel_id'));
        } elseif ($tip === 'yetkili') {
            $q->where('salon_id', $request->input('salon_id'));
        }

        $this->applyBrandInboxFilter($q, $request);

        return response()->json(['sayi' => $q->count()]);
    }
}
