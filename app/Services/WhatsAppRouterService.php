<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * WhatsAppService'in saglik-koruyucu bir alt sinifi.
 *
 * Mevcut WhatsAppService.php'ye HIC dokunulmadan, salon basina pilot
 * whatsmeow bridge'ine yonlendirme yapmak icin yazildi. AppServiceProvider'da
 *
 *   $this->app->bind(WhatsAppService::class, WhatsAppRouterService::class);
 *
 * ile binding yapilir. Cagri yeri (controller, cron vb.) hala app(WhatsAppService::class)
 * cagiriyor — container WhatsAppRouterService donduruyor (tip uyumlu, subclass).
 *
 * Algoritma cok basit: her halka acik metoddan once salonun whatsapp_bridge_tipi
 * alanina bak; 'whatsmeow' ise $this->baseUrl'i gecici olarak whatsmeow URL'siyle
 * degistir, parent metodu cagir, sonra eski URL'yi geri yukle.
 *
 * Boylece tum mesaj yollari (sendReminder, sendUrgent, sendViaBaileys, request)
 * parent'taki ORIGINAL kodu kullanir; sadece base URL salon basina farkli olur.
 *
 * Salon flag'i yoksa (whatsapp_bridge_tipi=NULL ya da 'baileys') sifir davranis
 * degisikligi: parent metodlari mevcut Baileys URL'siyle calismaya devam eder.
 */
class WhatsAppRouterService extends WhatsAppService
{
    protected $whatsmeowUrl;

    public function __construct()
    {
        parent::__construct();
        $this->whatsmeowUrl = rtrim(
            config('whatsapp.whatsmeow_service_url', 'http://127.0.0.1:3002'),
            '/'
        );
    }

    /**
     * Salonun bridge tipini doner: 'whatsmeow' veya 'baileys' (default).
     * Kolon yoksa (migration kosmamis) ya da hata olursa 'baileys'.
     */
    protected function tipFor($salonId)
    {
        // CUTOVER (whatsmeow-only): artik VARSAYILAN whatsmeow. Sadece acikca 'baileys'
        // olarak isaretli salonlar Baileys'e gider; NULL / bos / 'whatsmeow' hepsi whatsmeow (3002).
        if (!$salonId) return 'whatsmeow';
        try {
            $t = DB::table('salonlar')->where('id', $salonId)->value('whatsapp_bridge_tipi');
            return $t === 'baileys' ? 'baileys' : 'whatsmeow';
        } catch (\Throwable $e) {
            return 'whatsmeow';
        }
    }

    /**
     * Salon whatsmeow ise gecici olarak $this->baseUrl'i swap edip $callback'i cagirir,
     * sonra eski URL'yi restore eder. Parent metodlari icindeki $this->baseUrl
     * kullanimi otomatik dogru bridge'e gider.
     */
    protected function withRouting($salonId, callable $callback)
    {
        if ($this->tipFor($salonId) !== 'whatsmeow') {
            return $callback();
        }
        $original = $this->baseUrl;
        $this->baseUrl = $this->whatsmeowUrl;
        try {
            return $callback();
        } finally {
            $this->baseUrl = $original;
        }
    }

    // ==== Session yonetim metodlari ====
    public function startSession($salonId)
    {
        return $this->withRouting($salonId, function () use ($salonId) {
            return parent::startSession($salonId);
        });
    }

    public function status($salonId)
    {
        return $this->withRouting($salonId, function () use ($salonId) {
            $res = parent::status($salonId);
            // whatsmeow bridge farkli sekilde cevap veriyor; Baileys-uyumlu hale getir
            // ki whatsapp.blade UI ve whatsappDurum() DB guncellemesi dogru calissin.
            if ($this->tipFor($salonId) === 'whatsmeow') {
                $res['body'] = $this->normalizeWhatsmeowStatus($res['body'] ?? []);
            }
            return $res;
        });
    }

    /**
     * whatsmeow bridge status yanitini Baileys-uyumlu sekle cevirir.
     * whatsmeow: {connected:true,phone} | {status:'qr-pending'} | {connected:false,status:'no-session'} | {status:'disconnected'}
     * Baileys UI bekliyor: {status:'connected'|'qr-pending'|'disconnected', phone, hasQr}
     */
    protected function normalizeWhatsmeowStatus($body)
    {
        if (!is_array($body)) return ['status' => 'disconnected'];
        $out = $body;
        if (!empty($body['connected'])) {
            $out['status'] = 'connected';
        } elseif (empty($body['status']) || $body['status'] === 'no-session') {
            $out['status'] = 'disconnected';
        }
        if (($out['status'] ?? '') === 'qr-pending') {
            $out['hasQr'] = true;
        }
        return $out;
    }

    public function qr($salonId)
    {
        return $this->withRouting($salonId, function () use ($salonId) {
            return parent::qr($salonId);
        });
    }

    public function logout($salonId)
    {
        return $this->withRouting($salonId, function () use ($salonId) {
            return parent::logout($salonId);
        });
    }

    // ==== Mesaj gonderim metodlari ====
    // sendReminder parent'taki orijinal akisi calistirir; sadece base URL salon basina degisir.
    public function sendReminder(\App\Salonlar $salon, $to, $message, $randevuId = null, $userId = null, $templateCtx = null, $urgent = false, $gonderimTipi = null)
    {
        return $this->withRouting($salon->id, function () use ($salon, $to, $message, $randevuId, $userId, $templateCtx, $urgent, $gonderimTipi) {
            return parent::sendReminder($salon, $to, $message, $randevuId, $userId, $templateCtx, $urgent, $gonderimTipi);
        });
    }

    public function sendUrgent(\App\Salonlar $salon, $to, $message, $userId = null, $gonderimTipi = 'sifre_kodu')
    {
        return $this->withRouting($salon->id, function () use ($salon, $to, $message, $userId, $gonderimTipi) {
            return parent::sendUrgent($salon, $to, $message, $userId, $gonderimTipi);
        });
    }
}
