<?php

namespace App\Http\Controllers;
use App\Salonlar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


class SMSController extends Controller
{
     const VT_TUR_BASLIKLARI = [
         1 => 'Bildirim',
         2 => 'Grup SMS',
         3 => 'Filtreli SMS',
         4 => 'Toplu SMS',
         5 => 'Kampanya',
         6 => 'Etkinlik',
     ];

     public function voiceTelekomRaporlariGetir($salonId, $gunSayisi = 60)
     {
          $bosSonuc = [
               'toplu' => collect([]),
               'bildirim' => collect([]),
               'grup' => collect([]),
               'filtre' => collect([]),
               'kampanya' => collect([]),
               'etkinlik' => collect([]),
          ];

          $isletme = Salonlar::where('id', $salonId)->first();
          if (!$isletme || empty($isletme->sms_user_name) || empty($isletme->sms_secret)) {
               return $bosSonuc;
          }

          $cacheKey = 'vt_sms_rapor_' . $salonId . '_' . $gunSayisi;
          $cached = Cache::get($cacheKey);
          if ($cached !== null) {
               return $cached;
          }

          require_once app_path('VoiceTelekom/Sms/SmsApi.php');
          require_once app_path('VoiceTelekom/Sms/GetSmsReports.php');

          try {
               $smsApi = new \SmsApi('smsvt.voicetelekom.com', $isletme->sms_user_name, $isletme->sms_secret, '9588');
               $request = new \GetSmsReports();
               $request->startDate = date('Y-m-d H:i', strtotime('-' . intval($gunSayisi) . ' days'));
               $request->finishDate = date('Y-m-d H:i');
               $request->pageIndex = 0;
               $request->pageSize = 1000;

               $response = $smsApi->getSmsReports($request);
               if ($response->err !== null) {
                    Log::warning('VoiceTelekom rapor alinamadi salon=' . $salonId . ' hata=' . $response->err->message);
                    return $bosSonuc;
               }
          } catch (\Exception $e) {
               Log::warning('VoiceTelekom rapor exception salon=' . $salonId . ' hata=' . $e->getMessage());
               return $bosSonuc;
          }

          $sonuc = [
               'toplu' => [],
               'bildirim' => [],
               'grup' => [],
               'filtre' => [],
               'kampanya' => [],
               'etkinlik' => [],
          ];

          foreach ($response->list as $item) {
               $tur = self::vtBaslikTanimla($item->title);
               $tarihHam = $item->processingDate ?: $item->sendingDate;
               if (!$tarihHam && isset($item->createDate)) {
                    $tarihHam = $item->createDate;
               }
               $tarihGosterim = self::vtTarihFormatla($tarihHam);

               $adet = isset($item->statistics->total) ? intval($item->statistics->total) : 0;
               $toplamKredi = isset($item->statistics->credit) ? floatval($item->statistics->credit) : 0;
               $tekilKredi = ($adet > 0) ? round($toplamKredi / $adet, 4) : $toplamKredi;

               $sonuc[$tur][] = [
                    'date' => $tarihGosterim,
                    'count' => $adet,
                    'price' => $tekilKredi,
                    'msgdetails' => $item->content,
                    'status' => self::vtDurumKodla($item->state, $item->statistics ?? null),
               ];
          }

          foreach ($sonuc as $anahtar => $liste) {
               $sonuc[$anahtar] = collect($liste);
          }

          Cache::put($cacheKey, $sonuc, 180);
          return $sonuc;
     }

     private static function vtBaslikTanimla($title)
     {
          $harita = [
               'Bildirim' => 'bildirim',
               'Grup SMS' => 'grup',
               'Grup Sms' => 'grup',
               'Filtreli SMS' => 'filtre',
               'Filtreli Sms' => 'filtre',
               'Toplu SMS' => 'toplu',
               'Toplu Sms' => 'toplu',
               'Kampanya' => 'kampanya',
               'Etkinlik' => 'etkinlik',
          ];
          return $harita[$title] ?? 'bildirim';
     }

     private static function vtTarihFormatla($tarihHam)
     {
          if (!$tarihHam) {
               return '<span style="display:none">00000000000000</span>-';
          }
          $ts = strtotime($tarihHam);
          if (!$ts) {
               return '<span style="display:none">00000000000000</span>' . $tarihHam;
          }
          return '<span style="display:none">' . date('YmdHis', $ts) . '</span>' . date('d.m.Y H:i:s', $ts);
     }

     private static function vtDurumKodla($state, $istatistik)
     {
          // VoiceTelekom paket durumlari -> UI kodlari
          // 2+ => Gonderildi (99), 0/1 => Bekliyor (0), negatif => Iptal (95)
          $state = intval($state);
          if ($state < 0) {
               return 95;
          }
          if ($state >= 2) {
               return 99;
          }
          if ($istatistik && isset($istatistik->delivered) && intval($istatistik->delivered) > 0) {
               return 99;
          }
          return 0;
     }

     public function cokluSMSGonderVoiceTelekom($numaralar,$mesaj,$salonId,$baslik)
     {
          require_once app_path('VoiceTelekom/Sms/SmsApi.php');
          require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
          require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
          $isletme = Salonlar::where('id',$salonId)->first();
          $smsApi = new \SmsApi("smsvt.voicetelekom.com", $isletme->sms_user_name, $isletme->sms_secret);
            //Kendi sistemindeki id ‘ler ile eşleştirme yapabilmek için kullanılan parametre

            $request = new \SendMultiSms();
            $request->title = $baslik;
            $request->content = $mesaj;
            $request->numbers = $numaralar;
            $request->encoding = 0;
            
            $request->customID = "sms_" . date('Ymd_His') . "_" . substr(md5(microtime()), 0, 8);
            $request->sender = $isletme->sms_baslik;
            $request->skipAhsQuery = true;
            $response = $smsApi->sendMultiSms($request);
          //Ticari gönderimlerde true değeri girilmelidir.
          //$request->commercial = true;

          //Mesajların AHS sorgusuna sokulması istenmiyorsa true değeri girilmelidir.
          //$request->skipAhsQuery = true;

          //İleri tarihli gönderim için
          //$request->sendingDate = "2021-01-10 13:00";

          //Gönderen başlığına tanımlı ağ geçidi
          //$request->gateway = "1b09b8c5-ae80-42af-8779-21a61afd5da1";

          //Paket periyodik olarak gönderilecekse
          //$request->periodicSettings = new PeriodicSettings();
          //$request->periodicSettings->interval = 1; 
          //$request->periodicSettings->amount = 1000;

          //Rapor push olarak alınmak isteniyorsa ilgili url girilir
          //$request->pushUrl = "https://webhook.site/8d7ed0f7"

          

          if($response->err == null){
            Log::info($isletme->salon_adi. " için Toplu Mesaj Gönderme Başarlı : ".$response->pkgID);
            return 'başarılı';
          }else{
            Log::info($isletme->salon_adi. " Toplu Mesaj Hatası Durum: ".$response->err->status);
             Log::info("Hata kodu : ".$response->err->code);
             Log::info("Hata: ".$response->err->message);
             return $response->err->message;
          }   
     }
     public function tekilSMSGonderVoiceTelekom($numara,$mesaj,$salonId)
     {
          require_once app_path('VoiceTelekom/Sms/SmsApi.php');
          require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
          require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
          $isletme = Salonlar::where('id',$salonId)->first();
          $smsApi = new \SmsApi("smsvt.voicetelekom.com", $isletme->sms_user_name, $isletme->sms_secret);
            //Kendi sistemindeki id ‘ler ile eşleştirme yapabilmek için kullanılan parametre

            $request = new \SendSingleSms();
            $request->title = "Bilgilendirme";
            $request->content = $mesaj;
            $request->number = $numara;
            $request->encoding = 0;
            
            $request->customID = "sms_" . date('Ymd_His') . "_" . substr(md5(microtime()), 0, 8);
            $request->sender = $isletme->sms_baslik;
            $request->skipAhsQuery = true;
            $response = $smsApi->sendSingleSms($request);
          //Ticari gönderimlerde true değeri girilmelidir.
          //$request->commercial = true;

          //Mesajların AHS sorgusuna sokulması istenmiyorsa true değeri girilmelidir.
          //$request->skipAhsQuery = true;

          //İleri tarihli gönderim için
          //$request->sendingDate = "2021-01-10 13:00";

          //Gönderen başlığına tanımlı ağ geçidi
          //$request->gateway = "1b09b8c5-ae80-42af-8779-21a61afd5da1";

          //Paket periyodik olarak gönderilecekse
          //$request->periodicSettings = new PeriodicSettings();
          //$request->periodicSettings->interval = 1; 
          //$request->periodicSettings->amount = 1000;

          //Rapor push olarak alınmak isteniyorsa ilgili url girilir
          //$request->pushUrl = "https://webhook.site/8d7ed0f7"

          

          if($response->err == null){
            Log::info($isletme->salon_adi. " için Tek SMS Gönderme Başarlı : ".$response->pkgID);
            return 'başarılı';
          }else{
            Log::info($isletme->salon_adi. " Tek SMS Hatası Durum: ".$response->err->status);
             Log::info("Hata kodu : ".$response->err->code);
             Log::info("Hata: ".$response->err->message);
             return $response->err->message;
          }   
     }

}