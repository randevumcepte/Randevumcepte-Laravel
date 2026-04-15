<?php
 
namespace App\Http\Controllers;
use App\Salonlar;
use Illuminate\Support\Facades\Log;

 
class SMSController extends Controller
{
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