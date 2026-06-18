<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Paketler;
use App\Salonlar;
use App\Personeller;
use App\MusteriPortfoy;
use App\BildirimKimlikleri;
class AuthController extends Controller
{
public $successStatus = 200;

/**
* Kullanıcı Oluşturma
*
* @param [string] name
* @param [string] email
* @param [string] password
* @return [string] message
*/

    public function isLoggedIn(Request $request){
        
    }
     /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'cep_telefon' => 'required|regex:/(5)[0-9]{9}/|numeric|digits:10|unique:users',
            'password' => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
          return response()->json([
            'success' => false,
            'message' => $validator->errors(),
          ], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('appToken')->accessToken;
        return response()->json([
          'success' => true,
          'token' => $success,
          'user' => $user
      ]);
    }

    /**
    * Kullanıcı Girişi ve token oluşturma
    *
    * @param [string] email
    * @param [string] password
    * @return [string] token
    * @return [string] token_type
    * @return [string] expires_at
    * @return [string] success
    */
    public function telefon_no_format_duzenle($telefon)
    { 
        $phone = preg_replace('/^(\+?90|0)/', '', $telefon);
        $phone = str_replace(["(",")"," ","-"], "", $phone);
        return $phone;
         
    }
    public function login(Request $request){
          $yetkili_salonlar = array();
          $phone = $this->telefon_no_format_duzenle($request->cep_telefon);
          $salonlar = array();
          if($request->appBundle != 'com.randevumcepte.randevumcepte')
            $salonlar = Salonlar::where('app_bundle',$request->appBundle)->pluck('id')->toArray();
          /*$this->validate($request,[
            
                     
            'password' => 'required|min:3',
          ]);*/
          $credential = ['cep_telefon' => $this->telefon_no_format_duzenle($request->cep_telefon), 'password' =>$request->password];
          $credential2 = ['gsm1'=> $this->telefon_no_format_duzenle($request->cep_telefon),'password'=>$request->password];
          $is_attempt = false;
          $user="";
          $usertype= '';
          if(Auth::attempt($credential)){
           
            $is_attempt=true;
            $user = Auth::user();
            $usertype = '0';
          

            
          }
          if(Auth::guard('isletmeyonetim')->attempt($credential2)){
              
            $is_attempt=true;
            $user = Auth::guard('isletmeyonetim')->user()->load('yetkili_olunan_isletmeler');
            Log::info($user->yetkili_olunan_isletmeler->toArray());
            $usertype = '1';
          }

          if($is_attempt)
          { 
                if($usertype == '0')
                {
                   
                    if($request->appBundle != 'com.randevumcepte.randevumcepte')
                    {
                        $portfoydeVar = MusteriPortfoy::where('user_id',$user->id)->whereIn('salon_id',$salonlar);
                     
                        if(!$portfoydeVar)
                        { 
                            Auth::logout();
                            $message['message'] = 'Bu işletme için kaydınız bulunmamaktadır. Lütfen müşteri/danışan ol bölümünden kayıt olunuz.';
                            $message['success'] = false;  
                            return response()->json(['error'=>'Unauthorised','message'=> $message], 401);
                        }
                        foreach($salonlar as $salon)
                        {
                            $portfoyVar = MusteriPortfoy::where('user_id',$user->id)->where('salon_id',$salon)->first();
                            if(!$portfoyVar)
                            {
                                $portfoy = new MusteriPortfoy();
                                $portfoy->user_id = $user->id;
                                $portfoy->salon_id = $salon;
                                $portfoy->aktif = 1;
                                $portfoy->save();
                            }
                        }
                    }
                    
                   
                }
                if($usertype == '1')
                {
                    $yetkiler = $user->yetkili_olunan_isletmeler->where('aktif',1)->pluck('salon_id')->flatten()->unique()->toArray();
                    if($request->appBundle != 'com.randevumcepte.randevumcepte')
                    {
                        foreach($salonlar as $salon)
                        {
                            if (!in_array($salon, $yetkiler)) {
                          
                                $message['message'] = 'Bu işletme için yetkiniz bulunmamaktadır.';
                                $message['success'] = false;  
                                return response()->json(['error'=>'Unauthorised','message'=> $message], 401);
                            } 
                        }
                    
                    }
                    
                }
             
            
            
                // KALDIRILDI: Eski OneSignal donemi /login akisinda bildirim_kimlikleri
                // tablosuna foreach ile her yetkili_olunan_isletme icin satir aciyordu.
                // - request->bildirimId artik onesignal_player_id (FCM gecisinde null)
                // - foreach 27 yetkili olunan salon icin 27 satir uretiyordu
                // - app_bundle/salon_id/kullanici_tipi set edilmiyordu, brand izolasyonu
                //   ve push hedeflemesi bozuluyordu
                // Yeni akis: Flutter login sonrasi (subesecimi.dart veya tek-sube login)
                // NotificationService.registerForUser cagriyor -> /api/v1/bildirim/cihaz-kaydet
                // (NotificationApiController@cihazKaydet) endpointinden cihaz basina TEK
                // dogru kayit (bildirim_id+cihaz+app_bundle+salon_id+kullanici_tipi) aciliyor.
                

                    
                $message['token'] = $user->createToken('appToken')->accessToken;
                $message['token_type'] = 'Bearer';
                $message['experies_at'] = Carbon::parse(Carbon::now()->addYears(1))->toDateTimeString();
                $message['success'] = true;
                $message['user'] = $usertype == '1' ? $user->load([
                    'yetkili_olunan_isletmeler' => function ($query) use ($salonlar,$request) {
                        if($request->appBundle  != 'com.randevumcepte.randevumcepte')
                            $query->whereIn('salon_id', $salonlar);
                    },
                    'yetkili_olunan_isletmeler.salonlar',
                ]) : '';
                $message['musteri'] = $usertype == '0' ? $user->load('salonlar.salonlar') : '';
                $message['user_type'] = $usertype;
                      
                return response()->json(['message' => $message], $this->successStatus);
            
        }
        else{
            $message['message'] = 'Yanlış kullanıcı adı veya şifre! Lütfen yeniden deneyiniz.';
            $message['success'] = false;
            return response()->json(['error'=>'Unauthorised','message'=> $message], 401);
        }
    }
    public function logout(Request $request)
    {
       $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

}