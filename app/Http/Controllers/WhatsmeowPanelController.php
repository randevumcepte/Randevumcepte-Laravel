<?php

namespace App\Http\Controllers;

use App\Salonlar;
use App\Services\WhatsmeowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * whatsmeow pilot panel controller'i.
 *
 * Mevcut StoreAdminController@whatsapp ve onun AJAX endpoint'lerine HIC
 * dokunmaz. Production Baileys panel'i degismeden calismaya devam eder.
 *
 * Bu controller sadece pilot test ortami:
 *   /isletmeyonetim/whatsmeow            -> panel (QR + status)
 *   /isletmeyonetim/whatsmeow/baslat     -> start session POST
 *   /isletmeyonetim/whatsmeow/durum      -> status GET
 *   /isletmeyonetim/whatsmeow/qr         -> QR text GET
 *   /isletmeyonetim/whatsmeow/cikis      -> logout POST
 *   /isletmeyonetim/whatsmeow/test-mesaj -> manuel test mesaji POST
 */
class WhatsmeowPanelController extends Controller
{
    /**
     * StoreAdminController instance method'larini buradan kolay cagirmak icin.
     */
    protected function admin()
    {
        return app(StoreAdminController::class);
    }

    public function index(Request $request)
    {
        // Birleştirildi: "WhatsApp Yeni Test" ayrı sayfası kaldırıldı; tek "WhatsApp Yönetimi"
        // sayfası (/isletmeyonetim/whatsapp) artık bağlantıyı whatsmeow üzerinden yapıyor.
        $sube = $request->has('sube') ? ('?sube=' . $request->query('sube')) : '';
        return redirect('/isletmeyonetim/whatsapp' . $sube);
    }

    public function baslat(Request $request)
    {
        $salonId = $this->yetkiliSalon($request);
        if (!$salonId) return response()->json(['error' => 'yetkisiz'], 403);

        $svc = app(WhatsmeowService::class);
        $res = $svc->startSession($salonId);

        // Production form/randevu yollari $isletme->whatsapp_aktif + durum='connected'
        // sartiyla WA'ya yonleniyor. whatsmeow bridge sayfasi acilinca bu alanlari
        // ayrica whatsapp_bridge_tipi='whatsmeow' set et ki WhatsAppRouterService
        // butun gonderim cagrilarini otomatik yeni bridge'e (port 3002) yonlendirsin.
        if ($res['ok'] ?? false) {
            Salonlar::where('id', $salonId)->update([
                'whatsapp_aktif' => 1,
                'whatsapp_durum' => $res['body']['status'] ?? 'connecting',
                'whatsapp_bridge_tipi' => 'whatsmeow',
            ]);
        }

        return response()->json($res['body'] ?? ['error' => 'servis-erisilemiyor'], $res['status'] ?: 502);
    }

    public function durum(Request $request)
    {
        $salonId = $this->yetkiliSalon($request);
        if (!$salonId) return response()->json(['error' => 'yetkisiz'], 403);

        $svc = app(WhatsmeowService::class);
        $res = $svc->status($salonId);
        return response()->json($res['body'] ?? ['status' => 'servis-kapali'], $res['status'] ?: 502);
    }

    public function qr(Request $request)
    {
        $salonId = $this->yetkiliSalon($request);
        if (!$salonId) return response()->json(['error' => 'yetkisiz'], 403);

        $svc = app(WhatsmeowService::class);
        $res = $svc->qr($salonId);
        return response()->json($res['body'] ?? ['error' => 'qr-yok'], $res['status'] ?: 404);
    }

    public function cikis(Request $request)
    {
        $salonId = $this->yetkiliSalon($request);
        if (!$salonId) return response()->json(['error' => 'yetkisiz'], 403);

        $svc = app(WhatsmeowService::class);
        $res = $svc->logout($salonId);

        // Cikista DB temizligi: aktif=0. Kopru whatsmeow kalir (whatsmeow-only cutover;
        // Baileys'e geri pinleme yok, tekrar baglaninca yine whatsmeow'dan baglanir).
        Salonlar::where('id', $salonId)->update([
            'whatsapp_aktif' => 0,
            'whatsapp_durum' => 'cikis-yapildi',
            'whatsapp_numara' => null,
            'whatsapp_baglanti_tarihi' => null,
            'whatsapp_warmup_baslangic' => null,
            'whatsapp_bridge_tipi' => 'whatsmeow',
        ]);

        return response()->json($res['body'] ?? ['ok' => false], $res['status'] ?: 502);
    }

    public function testMesaj(Request $request)
    {
        $salonId = $this->yetkiliSalon($request);
        if (!$salonId) return response()->json(['error' => 'yetkisiz'], 403);

        $to = (string) $request->input('to', '');
        $msg = (string) $request->input('message', '');
        if ($to === '' || $msg === '') {
            return response()->json(['error' => 'to/message gerekli'], 422);
        }

        $svc = app(WhatsmeowService::class);
        $res = $svc->sendTest($salonId, $to, $msg);
        return response()->json($res['body'] ?? ['error' => 'gonderim-hatasi'], $res['status'] ?: 502);
    }

    public function health(Request $request)
    {
        $svc = app(WhatsmeowService::class);
        $res = $svc->health();
        return response()->json($res['body'] ?? ['status' => 'down'], $res['status'] ?: 502);
    }

    /**
     * Kullanicinin secili subesi sahibi mi?
     */
    protected function yetkiliSalon(Request $request)
    {
        $salonId = (int) $this->admin()->mevcutsube($request);
        if (!$salonId) return null;

        if (Auth::guard('satisortakligi')->check()) {
            return $salonId === 15 ? 15 : null;
        }

        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return null;

        $yetkili = $u->yetkili_olunan_isletmeler
            ->where('aktif', 1)
            ->pluck('salon_id')->toArray();
        return in_array($salonId, $yetkili) ? $salonId : null;
    }
}
