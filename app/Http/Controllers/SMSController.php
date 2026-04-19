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
                    'id' => $item->id,
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

     public function voiceTelekomRaporDetayGetir($salonId, $pkgID)
     {
          $isletme = Salonlar::where('id', $salonId)->first();
          if (!$isletme || empty($isletme->sms_user_name) || empty($isletme->sms_secret) || empty($pkgID)) {
               return ['basarili' => false, 'mesaj' => 'Kimlik veya paket bilgisi eksik', 'kayitlar' => []];
          }

          require_once app_path('VoiceTelekom/Sms/SmsApi.php');
          require_once app_path('VoiceTelekom/Sms/GetSmsReportDetails.php');

          try {
               $smsApi = new \SmsApi('smsvt.voicetelekom.com', $isletme->sms_user_name, $isletme->sms_secret, '9588');
               $request = new \GetSmsReportDetails();
               $request->pkgID = $pkgID;
               $request->pageIndex = 0;
               $request->pageSize = 1000;

               $response = $smsApi->getSmsReportDetails($request);
               if ($response->err !== null) {
                    return ['basarili' => false, 'mesaj' => $response->err->message, 'kayitlar' => []];
               }
          } catch (\Exception $e) {
               return ['basarili' => false, 'mesaj' => $e->getMessage(), 'kayitlar' => []];
          }

          $numaralar = array_unique(array_map(function($item){ return preg_replace('/\D/', '', $item->target); }, $response->list));
          $numaralar = array_values(array_filter($numaralar));
          $adHaritasi = self::adHaritasiniHazirla($salonId, $numaralar);

          $kayitlar = [];
          foreach ($response->list as $detay) {
               $temiz = preg_replace('/\D/', '', $detay->target);
               $ad = isset($adHaritasi[$temiz]) ? $adHaritasi[$temiz] : '';
               $kayitlar[] = [
                    'telefon' => $detay->target,
                    'ad' => $ad,
                    'operator' => $detay->operator ?? '',
                    'durum' => self::vtDetayDurumEtiket(intval($detay->state)),
                    'iletim_tarihi' => self::vtTarihOkunur($detay->deliveryDate ?? $detay->processingDate ?? $detay->sendingDate ?? ''),
               ];
          }

          return ['basarili' => true, 'kayitlar' => $kayitlar];
     }

     private static function adHaritasiniHazirla($salonId, array $numaralar)
     {
          if (empty($numaralar)) {
               return [];
          }

          $varyasyonlar = [];
          foreach ($numaralar as $num) {
               $varyasyonlar[] = $num;
               if (strlen($num) === 12 && substr($num, 0, 2) === '90') {
                    $varyasyonlar[] = substr($num, 2);
               }
               if (strlen($num) === 10) {
                    $varyasyonlar[] = '90' . $num;
               }
               if (strlen($num) === 11 && substr($num, 0, 1) === '0') {
                    $varyasyonlar[] = substr($num, 1);
                    $varyasyonlar[] = '90' . substr($num, 1);
               }
          }
          $varyasyonlar = array_values(array_unique($varyasyonlar));

          $musteriIdleri = \DB::table('musteri_portfoy')->where('salon_id', $salonId)->pluck('user_id')->toArray();
          $kullanicilar = \DB::table('users')
               ->whereIn('id', $musteriIdleri)
               ->whereIn('cep_telefon', $varyasyonlar)
               ->select('cep_telefon', 'name')
               ->get();

          $harita = [];
          foreach ($kullanicilar as $k) {
               $t = preg_replace('/\D/', '', $k->cep_telefon);
               $harita[$t] = $k->name;
               if (strlen($t) === 10) {
                    $harita['90' . $t] = $k->name;
               }
               if (strlen($t) === 11 && substr($t, 0, 1) === '0') {
                    $harita['90' . substr($t, 1)] = $k->name;
                    $harita[substr($t, 1)] = $k->name;
               }
               if (strlen($t) === 12 && substr($t, 0, 2) === '90') {
                    $harita[substr($t, 2)] = $k->name;
               }
          }
          return $harita;
     }

     private static function vtDetayDurumEtiket($state)
     {
          $harita = [
               -2 => 'İptal Edildi',
               -1 => 'Operatör Reddetti',
                0 => 'Bekliyor',
                1 => 'Gönderiliyor',
                2 => 'Gönderildi',
                3 => 'Ulaştı',
                4 => 'Ulaşmadı',
                5 => 'Zaman Aşımı',
          ];
          return isset($harita[$state]) ? $harita[$state] : '—';
     }

     private static function vtTarihOkunur($tarihHam)
     {
          if (!$tarihHam) return '';
          $ts = strtotime($tarihHam);
          return $ts ? date('d.m.Y H:i:s', $ts) : $tarihHam;
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