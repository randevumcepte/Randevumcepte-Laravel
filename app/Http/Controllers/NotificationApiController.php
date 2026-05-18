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

        // Bu token icin halihazirda bir kayit var mi?
        $row = BildirimKimlikleri::where('bildirim_id', $token)->first();
        // Ayni token tutan diger TUM kayitlari sil (cihaz devri + dublikasyon onleme)
        if ($row) {
            BildirimKimlikleri::where('bildirim_id', $token)
                ->where('id', '!=', $row->id)->delete();
        } else {
            BildirimKimlikleri::where('bildirim_id', $token)->delete();
            $row = new BildirimKimlikleri();
        }

        $row->bildirim_id = $token;
        $row->cihaz       = $request->input('cihaz');
        $row->app_bundle  = $request->input('app_bundle');

        if (Schema::hasColumn('bildirim_kimlikleri', 'platform'))       $row->platform = $request->input('platform');
        if (Schema::hasColumn('bildirim_kimlikleri', 'token_tipi'))     $row->token_tipi = 'fcm';
        if (Schema::hasColumn('bildirim_kimlikleri', 'kullanici_tipi')) $row->kullanici_tipi = $tip;
        if (Schema::hasColumn('bildirim_kimlikleri', 'salon_id'))       $row->salon_id = $request->input('salon_id');
        if (Schema::hasColumn('bildirim_kimlikleri', 'aktif'))          $row->aktif = true;
        if (Schema::hasColumn('bildirim_kimlikleri', 'gonderim_hatalari')) $row->gonderim_hatalari = 0;

        // Eski şema alanları (geri uyum)
        $row->user_id = $tip === 'musteri' ? $request->input('user_id') : null;
        if ($tip === 'personel') {
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
     * GET /api/v1/bildirim/liste
     * Query: kullanici_tipi, user_id|personel_id|yetkili_id, salon_id (opsiyonel)
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

        if ($salonId = $request->input('salon_id')) {
            $q->where(function ($w) use ($salonId) {
                $w->where('salon_id', $salonId)->orWhereNull('salon_id');
            });
        }

        return response()->json($q->limit(200)->get());
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

        return response()->json(['sayi' => $q->count()]);
    }
}
