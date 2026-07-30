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
                        // Coklu sube: personel markanin (app_bundle) HERHANGI BIR subesinde
                        // yetkiliyse girise izin ver. (Eskiden foreach ile TUM marka subelerinde
                        // yetki sart kosuluyordu; cok subeli markada yalnizca bazi subelerde
                        // yetkili personel hatali sekilde reddediliyordu.) yetkili_olunan_isletmeler
                        // yaniti zaten yalnizca bu markanin subelerini icerir (asagida whereIn filtresi).
                        if (empty(array_intersect($salonlar, $yetkiler))) {
                            $message['message'] = 'Bu işletme için yetkiniz bulunmamaktadır.';
                            $message['success'] = false;
                            return response()->json(['error'=>'Unauthorised','message'=> $message], 401);
                        }
                    }
                    else
                    {
                        // Master uygulama (com.randevumcepte.randevumcepte): yetkili olunan
                        // salonlardan en az birinin uyelik_turu 3 DISINDA olmasi gerekir.
                        // Tum salonlar uyelik_turu = 3 ise girise izin verme.
                        $uyelik3DisiVar = Salonlar::whereIn('id', $yetkiler)->where('uyelik_turu', '!=', 3)->exists();
                        if(!$uyelik3DisiVar)
                        {
                            $message['message'] = 'Yetkiniz bulunmamaktadır.';
                            $message['success'] = false;
                            return response()->json(['error'=>'Unauthorised','message'=> $message], 401);
                        }
                    }
                    
                }
             
            
            
                // bildirim_kimlikleri kayitlari:
                // - Brand build (app_bundle != master) -> SADECE brand'in salonlari icin kayit
                // - Master app (com.randevumcepte.randevumcepte) -> eski davranis (tum yetkili
                //   olunan salonlar) korunur cunku master yetkilisi sube degisiminde tum
                //   salonlarinin pushlarini almali.
                // app_bundle alani satira yazilir -> NotificationService brand filtresi devrede.
                // bildirimId bos gelirse hicbir kayit acmaz (eski OneSignal'de null kayitlari
                // engelle).
                if (!empty($request->bildirimId)) {
                    $isMaster = ($request->appBundle == 'com.randevumcepte.randevumcepte');
                    $bildirimKimlikleri = BildirimKimlikleri::where(function($q) use($usertype,$user,$isMaster,$salonlar){
                                if($usertype=='0')
                                    $q->where('user_id',$user->id);
                                if($usertype=='1') {
                                    $hedefler = $isMaster
                                        ? $user->yetkili_olunan_isletmeler
                                        : $user->yetkili_olunan_isletmeler->whereIn('salon_id', $salonlar);
                                    $q->whereIn('isletme_yetkili_id', $hedefler->pluck('id')->toArray());
                                }
                    })->where('bildirim_id',$request->bildirimId)->first();
                    if(!$bildirimKimlikleri)
                    {
                                if($usertype == '1')
                                {
                                    $hedefler = $isMaster
                                        ? $user->yetkili_olunan_isletmeler
                                        : $user->yetkili_olunan_isletmeler->whereIn('salon_id', $salonlar);
                                    foreach($hedefler as $yetkili)
                                    {
                                        $bildirimKimligi  = new BildirimKimlikleri();
                                        $bildirimKimligi->bildirim_id = $request->bildirimId;
                                        $bildirimKimligi->cihaz = $request->cihazBilgi;
                                        $bildirimKimligi->isletme_yetkili_id = $yetkili->id;
                                        $bildirimKimligi->app_bundle = $request->appBundle;
                                        $bildirimKimligi->save();
                                    }
                                }
                                else
                                {
                                    $bildirimKimligi  = new BildirimKimlikleri();
                                    $bildirimKimligi->user_id = $user->id;
                                    $bildirimKimligi->bildirim_id = $request->bildirimId;
                                    $bildirimKimligi->cihaz = $request->cihazBilgi;
                                    $bildirimKimligi->app_bundle = $request->appBundle;
                                    $bildirimKimligi->save();
                                }
                    }
                }
                

                    
                $message['token'] = $user->createToken('appToken')->accessToken;
                $message['token_type'] = 'Bearer';
                $message['experies_at'] = Carbon::parse(Carbon::now()->addYears(1))->toDateTimeString();
                $message['success'] = true;
                $message['user'] = $usertype == '1' ? $user->load([
                    'yetkili_olunan_isletmeler' => function ($query) use ($salonlar,$request) {
                        if($request->appBundle  != 'com.randevumcepte.randevumcepte')
                            $query->whereIn('salon_id', $salonlar);
                        else
                            // Master uygulamada sadece uyelik_turu 3 DISI salonlar gosterilir;
                            // birden fazla ise app icinde sube olarak listelenir.
                            $query->whereHas('salonlar', function($q){
                                $q->where('uyelik_turu', '!=', 3);
                            });
                    },
                    'yetkili_olunan_isletmeler.salonlar',
                ]) : '';
                if ($usertype == '0') {
                    $appBundleReq = $request->appBundle;
                    if (!empty($appBundleReq) && $appBundleReq != 'com.randevumcepte.randevumcepte') {
                        // Beyaz etiket: müşteri YALNIZCA bu markanın (app_bundle) şubelerini
                        // görsün; farklı işletmelerdeki portföy kayıtları (ör. başka salonlar)
                        // musteri_olunan_salonlar'a KARIŞMASIN.
                        $user->load(['salonlar' => function ($q) use ($appBundleReq) {
                            $q->whereHas('salonlar', function ($q2) use ($appBundleReq) {
                                $q2->where('app_bundle', $appBundleReq);
                            })->with('salonlar');
                        }]);
                    } else {
                        $user->load('salonlar.salonlar');
                    }
                    $message['musteri'] = $user;
                } else {
                    $message['musteri'] = '';
                }
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