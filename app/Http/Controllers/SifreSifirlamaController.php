<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\User;
use App\IsletmeYetkilileri;

class SifreSifirlamaController extends Controller
{
    // Rate limiting için cache key
    const SMS_RATE_LIMIT_KEY = 'sms_rate_';
    const SMS_DAILY_LIMIT = 3;
    const SMS_HOURLY_LIMIT = 1;
    
    /**
     * YENİ ve GÜVENLİ şifre sıfırlama endpoint'i
     */
    public function sifreSifirla(Request $request)
    {
        Log::info($request->cep_telefon);
        try {
            $controller = app()->make(ApiController::class);
            // 1. Validation - Laravel 5.6 style
            
            $this->validate($request, [
                'cep_telefon' => 'required|string|min:10|max:14',
                'appBundle' => 'required|string',
            ]);
            
            $telefon = $controller->telefon_no_format_duzenle($request->cep_telefon);
            
            // 2. Rate limiting kontrolü (ÖNCE BUNU KONTROL ET!)
            if (!$this->checkRateLimits($telefon, $request->ip())) {
                Log::warning('Rate limit exceeded', [
                    'phone' => $telefon,
                    'ip' => $request->ip()
                ]);
                
                /*return response()->json([
                    'success' => true, // Hile: Spamcılar anlamasın
                    'message' => 'İşleminiz alındı. Telefonunuza SMS gönderildi.'
                ]);*/
            }
            
            // 3. Telefon format kontrolü
            if (strlen($telefon) < 10) {
                return $this->genericResponse();
            }
            
            // 4. Kullanıcı arama - MEVCUT KODUNUZA UYGUN
            $kullanici = null;
            $kullanicivar = false;
            
            // User tablosunda ara
            if (User::where("cep_telefon", $telefon)->count() >= 1) {
                Log::info('kullanıcı var user');
                $kullanicivar = true;
                $kullanici = User::where("cep_telefon", $telefon)->first();
            }
            // IsletmeYetkilileri tablosunda ara
            elseif (IsletmeYetkilileri::where("gsm1", $telefon)->count() >= 1) {
                Log::info('kullanıcı var işletme');
                $kullanicivar = true;
                $kullanici = IsletmeYetkilileri::where("gsm1", $telefon)->first();
            }
            
            if (!$kullanicivar) {
                Log::info('kullanıcı yok');
                // Kullanıcı yoksa bile generic response dön
                usleep(rand(200000, 400000)); // 200-400ms random delay
                $this->incrementRateLimit($telefon, $request->ip()); // Rate limit say
                return "error";
            }
            
            // 5. Yeni şifre oluştur - MEVCUT KODUNUZA BENZER AMA DAHA GÜVENLİ
            $random = str_shuffle("abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789");
            $olusturulansifre = substr($random, 0, 5);
            
            // 6. Şifreyi güncelle
            $kullanici->password = Hash::make($olusturulansifre);
            $kullanici->save();
            
            // 7. Log kaydı
            Log::info('Password reset', [
                'user_id' => $kullanici->id,
                'phone' => $telefon,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            // 8. SMS gönder - SİZİN MEVCUT sms_gonder_2 FONKSİYONUNUZU KULLANIN
            $smsMesaj = [[
                "to" => $telefon,
                "message" => ($request->isletmeadi) . 
                            " uygulama şifreniz: " . $olusturulansifre
            ]];
           
            // SİZİN MEVCUT SMS FONKSİYONUNUZ - AYNI PARAMETRELERLE
            $smsSonuc = $controller->sms_gonder_2(
                $request,           // Request object
                $smsMesaj,          // Mesaj array
                false,              // isOtp (mevcut kodunuzdaki gibi)
                '1',                // tr (mevcut kodunuzdaki gibi)
                false,              // coklu (mevcut kodunuzdaki gibi)
                $request->salonidler ,true
            );
            
            // 9. Rate limit sayacını artır
            $this->incrementRateLimit($telefon, $request->ip());
            
            // 10. SMS log kaydı
            Log::info('SMS gönderildi', [
                'phone' => $telefon,
                'result' => $smsSonuc
            ]);
            
            return 'success';
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('Geçersiz veri formatı');
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri formatı'
            ], 400);
            
        } catch (\Exception $e) {

            Log::error('Şifre sıfırlama hatası', [
                'error' => $e->getMessage(),
                'phone' => $request->cep_telefon ?? 'N/A',
                'ip' => $request->ip()
            ]);
            
            return 'error';
        }
    }
    
    /**
     * Rate limiting kontrolü - SADECE BU FONKSİYONU EKLEYİN
     */
    private function checkRateLimits($telefon, $ip)
    {
        // 1. AYNI TELEFON - GÜNLÜK 3 KEZ
        $phoneDailyKey = self::SMS_RATE_LIMIT_KEY . 'phone_daily_' . $telefon;
        $phoneDailyCount = Cache::get($phoneDailyKey, 0);
        
        if ($phoneDailyCount >= self::SMS_DAILY_LIMIT) {
            return false;
        }
        
        // 2. AYNI TELEFON - SAATTE 1 KEZ
        $phoneHourlyKey = self::SMS_RATE_LIMIT_KEY . 'phone_hourly_' . $telefon;
        $phoneHourlyCount = Cache::get($phoneHourlyKey, 0);
        
        if ($phoneHourlyCount >= self::SMS_HOURLY_LIMIT) {
            return false;
        }
        
        // 3. AYNI IP - SAATTE 10 KEZ
        $ipKey = self::SMS_RATE_LIMIT_KEY . 'ip_' . $ip;
        $ipCount = Cache::get($ipKey, 0);
        
        if ($ipCount >= 10) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Rate limit sayacını artır
     */
    private function incrementRateLimit($telefon, $ip)
    {
        // Telefon - günlük (24 saat)
        $phoneDailyKey = self::SMS_RATE_LIMIT_KEY . 'phone_daily_' . $telefon;
        $currentDaily = Cache::get($phoneDailyKey, 0);
        Cache::put($phoneDailyKey, $currentDaily + 1, 1440); // 1440 dakika = 24 saat
        
        // Telefon - saatlik (60 dakika)
        $phoneHourlyKey = self::SMS_RATE_LIMIT_KEY . 'phone_hourly_' . $telefon;
        $currentHourly = Cache::get($phoneHourlyKey, 0);
        Cache::put($phoneHourlyKey, $currentHourly + 1, 60); // 60 dakika
        
        // IP bazlı (60 dakika)
        $ipKey = self::SMS_RATE_LIMIT_KEY . 'ip_' . $ip;
        $currentIp = Cache::get($ipKey, 0);
        Cache::put($ipKey, $currentIp + 1, 60); // 60 dakika
    }
    
    /**
     * SİZİN MEVCUT sms_gonder_2 FONKSİYONUNUZ
     * Bu kısmı mevcut kodunuzdan kopyalayın
     */
    
    
    /**
     * Generic response - HER ZAMAN AYNI MESAJ
     */
    private function genericResponse()
    {
        return response()->json([
            'success' => true,
            'message' => 'Eğer bu telefon numarası sistemimizde kayıtlıysa, şifreniz SMS olarak gönderilecektir.'
        ]);
    }
    
  
    
   
  
}