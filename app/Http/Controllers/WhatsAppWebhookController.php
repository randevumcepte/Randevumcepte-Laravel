<?php

namespace App\Http\Controllers;

use App\Salonlar;
use App\Bildirimler;
use App\Personeller;
use App\BildirimKimlikleri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('whatsapp.webhook_secret');
        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['error' => 'unauthorized'], 401);
        }
        $event = $request->input('event');
        $salonId = $request->input('salonId');
        if (!$salonId) return response()->json(['error' => 'salon-id-eksik'], 400);

        $salon = Salonlar::find($salonId);
        if (!$salon) return response()->json(['error' => 'salon-bulunamadi'], 404);

        switch ($event) {
            case 'connected':
                $this->onConnected($salon, $request);
                break;
            case 'disconnected':
                $this->onDisconnected($salon, $request);
                break;
            case 'qr.ready':
                $salon->whatsapp_durum = 'qr-pending';
                $salon->save();
                break;
            case 'ban.warning':
                $this->onBanWarning($salon, $request);
                break;
            case 'message.sent':
                $this->onMessageSent($request);
                break;
            case 'message.failed':
                $this->onMessageFailed($salon, $request);
                break;
        }

        return response()->json(['ok' => true]);
    }

    protected function onMessageSent(Request $request)
    {
        $logId = $request->input('logId');
        if (!$logId) return;
        DB::table('whatsapp_gonderim_loglari')
            ->where('id', $logId)
            ->update([
                'durum' => 1,
                'mesaj_id' => $request->input('messageId'),
                'gonderim_tarihi' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function onMessageFailed(Salonlar $salon, Request $request)
    {
        $logId = $request->input('logId');
        $err = substr((string) $request->input('error'), 0, 150);
        $log = $logId ? DB::table('whatsapp_gonderim_loglari')->where('id', $logId)->first() : null;
        if (!$log) return;

        // Log'u başarısız olarak işaretle
        DB::table('whatsapp_gonderim_loglari')
            ->where('id', $logId)
            ->update(['durum' => 2, 'hata' => $err, 'updated_at' => now()]);

        // SMS'e düşür — aynı randevu için
        if (!config('whatsapp.fallback_to_sms', true)) return;

        try {
            $controller = app()->make(Controller::class);
            $controller->sms_gonder($log->salon_id, [[
                'to' => $log->telefon,
                'message' => $log->mesaj,
            ]]);
            DB::table('whatsapp_gonderim_loglari')
                ->where('id', $logId)
                ->update(['durum' => 3, 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp failed → SMS fallback hatası', [
                'log_id' => $logId,
                'err' => $e->getMessage(),
            ]);
        }
    }

    protected function onConnected(Salonlar $salon, Request $request)
    {
        $salon->whatsapp_durum = 'connected';
        $salon->whatsapp_numara = $request->input('phone');
        if (!$salon->whatsapp_baglanti_tarihi) {
            $salon->whatsapp_baglanti_tarihi = now();
            $salon->whatsapp_warmup_baslangic = now();
        }
        $salon->whatsapp_son_hata = null;
        $salon->save();
    }

    protected function onDisconnected(Salonlar $salon, Request $request)
    {
        $salon->whatsapp_durum = $request->input('banLikely') ? 'banned-or-loggedout' : 'disconnected';
        $salon->whatsapp_son_hata = substr((string) $request->input('reason'), 0, 120);
        if ($request->input('banLikely')) {
            $salon->whatsapp_aktif = 0;
            Log::warning('WhatsApp ban sinyali', [
                'salon_id' => $salon->id,
                'reason' => $request->input('reason'),
                'statusCode' => $request->input('statusCode'),
            ]);
        }
        $salon->save();
    }

    protected function onBanWarning(Salonlar $salon, Request $request)
    {
        $reason = (string) $request->input('reason', 'bilinmiyor');
        $lastError = (string) $request->input('lastError', '');

        // Idempotent: zaten kapatılmışsa tekrar bildirim gönderme
        if (!$salon->whatsapp_aktif) return;

        $salon->whatsapp_aktif = 0;
        $salon->whatsapp_durum = 'auto-paused-ban-risk';
        $salon->whatsapp_son_hata = substr($reason, 0, 120);
        $salon->save();

        Log::warning('WhatsApp oturumu ban riski nedeniyle otomatik kapatıldı', [
            'salon_id' => $salon->id,
            'reason' => $reason,
            'lastError' => $lastError,
            'phone' => $request->input('phone'),
        ]);

        $mesaj = $salon->salon_adi . ' için WhatsApp oturumu ban riski sebebiyle otomatik kapatıldı. '
            . 'Hatırlatmalar SMS\'e düşürüldü. Detay: ' . $reason . '. '
            . 'İşletme panelinden yeniden QR tarayarak aktif edebilirsiniz.';

        $this->smsBildir($salon, $mesaj);
        $this->pushBildir($salon, $mesaj);
        $this->panelBildirimiEkle($salon, $mesaj);
    }

    protected function smsBildir(Salonlar $salon, $mesaj)
    {
        if (empty($salon->yetkili_telefon)) return;
        try {
            $controller = app()->make(Controller::class);
            $controller->sms_gonder($salon->id, [[
                'to' => $salon->yetkili_telefon,
                'message' => 'WhatsApp Uyarısı: ' . $mesaj,
            ]]);
        } catch (\Throwable $e) {
            Log::error('Ban uyarı SMS gönderilemedi', ['salon_id' => $salon->id, 'err' => $e->getMessage()]);
        }
    }

    protected function pushBildir(Salonlar $salon, $mesaj)
    {
        try {
            $yonetici = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $salon->id)
                ->where('model_has_roles.role_id', '<', 5)
                ->pluck('salon_personelleri.id')->toArray();

            foreach ($yonetici as $pid) {
                \App\Services\NotificationService::toStaff((int) $pid, (int) $salon->id)
                    ->type(\App\Services\NotificationTypes::SYSTEM_ANNOUNCEMENT)
                    ->title('WhatsApp Uyarısı')
                    ->body($mesaj)
                    ->send();
            }
        } catch (\Throwable $e) {
            Log::error('Ban uyarı push gönderilemedi', ['salon_id' => $salon->id, 'err' => $e->getMessage()]);
        }
    }

    protected function panelBildirimiEkle(Salonlar $salon, $mesaj)
    {
        try {
            $bildirim = new Bildirimler();
            $bildirim->aciklama = $mesaj;
            $bildirim->salon_id = $salon->id;
            $bildirim->url = '/isletmeyonetim/whatsapp?sube=' . $salon->id;
            $bildirim->tarih_saat = date('Y-m-d H:i:s');
            $bildirim->okundu = false;
            $bildirim->save();
        } catch (\Throwable $e) {
            Log::error('Ban uyarı panel bildirimi eklenemedi', ['salon_id' => $salon->id, 'err' => $e->getMessage()]);
        }
    }
}
