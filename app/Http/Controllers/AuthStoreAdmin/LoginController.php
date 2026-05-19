<?php

namespace App\Http\Controllers\AuthStoreAdmin;

use App\Http\Controllers\Controller;
 use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\BildirimKimlikleri;
use App\IsletmeYetkilileri;
use App\Salonlar;
use App\Personeller;
use App\SMSIletimRaporlari;
use Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    //use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
     use AuthenticatesUsers;
     protected $redirectTo = '/isletmeyonetim';
    public function __construct()
    {
        $this->middleware('guest:isletmeyonetim')->except(['logout']);
    }
    public function satisortagiornekhesapgirisi(Request $request)
{
    $user = IsletmeYetkilileri::find(1); // ID'si 1 olan kullanıcı

    if (!$user) {
        return redirect('/')->with('error', 'Kullanıcı bulunamadı!');
    }

    try {
        // Kullanıcıyı özel guard ile giriş yaptır
        Auth::guard('satisortakligi')->logout();
        Auth::guard('isletmeyonetim')->login($user);
        
        // Oturum açıldıktan sonra, oturum bilgisini manuel olarak kontrol edebiliriz
        if (Auth::guard('isletmeyonetim')->check()) {
            // Oturum başarılı bir şekilde açıldıysa, session'ı manuel olarak kontrol et ve güncelle
            Session::put('isletmeyonetim_user', $user); // Kullanıcıyı manuel olarak session'a ekle
            return redirect($this->_yetkiliIlkSayfa());
        } else {
            // Oturum açma işlemi başarısız olursa hata mesajı göster
            echo 'Oturum açılırken bir hata oluştu!';
            exit();
        }
        
    } catch (\Exception $e) {
        // Hata mesajı
         echo 'Oturum açılırken bir hata oluştu!';
            exit();
        
    }
}
    public function showStoreAdminLoginForm(){
        if(!Auth::user()){

            return view('isletmeadmin.login');
            exit;
        }
        else
        {
            return redirect($this->_yetkiliIlkSayfa());
            exit;
        }
    }
    public function login(Request $request){
        $credential = array();
        $credential2 = array();
        if(is_numeric($request->email)){
             $this->validate($request,[
            'gsm1' => 'email',
            'gsm2' => 'email',           
            'password' => 'required|min:3',
          ]);
             $credential = ['gsm1' => $request->email, 'password' =>$request->password];
             $credential2 =['gsm2' => $request->email,'password' =>$request->password];
        }
        else{
            $this->validate($request,[
            'email' => 'email',
           
            'password' => 'required|min:3',
             ]);
            $credential =['email' => $request->email, 'password' =>$request->password];
        }
       
        //  Auth::guard('isletmeyonetim')->user()->id)
        if((Auth::guard('isletmeyonetim')->attempt($credential,$request->member) ) || Auth::guard('isletmeyonetim')->attempt($credential2, $request->member)){

             if(BildirimKimlikleri::where('isletme_yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('bildirim_id',$request->bildirimid)->where('cihaz',$request->header('User-Agent'))->count()>0)
                BildirimKimlikleri::where('isletme_yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('bildirim_id',$request->bildirimid)->where('cihaz',$request->header('User-Agent'))->delete();

            $bildirimkimligi = new BildirimKimlikleri();
            $bildirimkimligi->isletme_yetkili_id = Auth::guard('isletmeyonetim')->user()->id;
            $bildirimkimligi->bildirim_id = $request->bildirimid;
            $bildirimkimligi->cihaz = $request->header('User-Agent');
            $bildirimkimligi->save();

            // Audit — login (yetkilinin baglandigi her aktif salon icin tek kayit)
            try {
                $userId = Auth::guard('isletmeyonetim')->user()->id;
                $userName = Auth::guard('isletmeyonetim')->user()->name;
                $salonIds = \App\Personeller::where('yetkili_id', $userId)
                    ->whereNotNull('salon_id')
                    ->pluck('salon_id')->unique();
                foreach ($salonIds as $sid) {
                    \App\SalonYonetim\Audit::log($sid, 'login', 'kullanici', $userId, $userName, 'Panele giriş yapıldı');
                }
            } catch (\Exception $e) {}

           // Auth::guard('isletmeyonetim')->logoutOtherDevices($request->password);
            return redirect($this->_yetkiliIlkSayfa());
            /*if(Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))
            {

                
                exit;
             }
             else{
                return redirect()->route('isletmeadmin.dashboard');
                exit;
             }*/
            
        } 
        return back()->with('error', 'Yanlış kullanıcı adı veya şifre girdiniz!');
    }
    /**
     * Auth user'in yetkilerine gore login sonrasi yonlendirilecek ilk sayfa.
     * Onclik: randevu takvimi (varsayilan) → musteriler → satis → raporlar
     * → kasa → personel → SMS → fallback profil.
     */
    private function _yetkiliIlkSayfa(): string
    {
        $user = \Auth::guard('isletmeyonetim')->user();
        if (!$user) return '/isletmeyonetim/randevular';
        $isletmeler = $user->yetkili_olunan_isletmeler
            ->where('aktif', 1)->pluck('salon_id')->toArray();
        if (empty($isletmeler)) return '/isletmeyonetim/randevular';
        $salonId = $isletmeler[0];
        $has = function ($key) use ($user, $salonId) {
            return \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(
                $user->id, $salonId, $key
            );
        };
        // Oncelikli kontroller (sidebar siralamasi ile ayni)
        if ($has('randevu.takvim_gor'))      return '/isletmeyonetim/randevular';
        if ($has('musteri.liste_gor'))       return '/isletmeyonetim/musteriler';
        if ($has('satis.adisyon_olustur') || $has('satis.tahsilat_al') || $has('satis.tum_satis_gor'))
                                              return '/isletmeyonetim/adisyonlar';
        if ($has('rapor.satis'))             return '/isletmeyonetim/raporlar';
        if ($has('paket.seans_takip'))       return '/isletmeyonetim/seanstakip';
        if ($has('personel.liste_gor'))      return '/isletmeyonetim/personel-yonetimi';
        if ($has('rapor.kasa') || $has('finans.kasa_giris_cikis') || $has('finans.masraf_gor'))
                                              return '/isletmeyonetim/kasadefteri';
        if ($has('pazarlama.kampanya_yonet'))return '/isletmeyonetim/kampanya_yonetimi';
        if ($has('pazarlama.sms_gonder') || $has('pazarlama.toplu_sms'))
                                              return '/isletmeyonetim/toplusms';
        if ($has('ayar.salon_bilgi') || $has('ayar.cihaz_oda_yonet') || $has('randevu.online_ayar'))
                                              return '/isletmeyonetim/ayarlar?p=temelbilgiler';
        // Personel rolu + hicbir buyuk yetki yoksa: kendi rapor sayfasina yonlendir
        $personelId = \App\Personeller::where('yetkili_id', $user->id)
            ->where('salon_id', $salonId)
            ->value('id');
        if ($personelId) {
            return '/isletmeyonetim/personeldetay/' . $personelId;
        }
        return '/isletmeyonetim/profilbilgileri';
    }

    public function sifremiunuttum(Request $request)
    {
        return view('isletmeadmin.sifremiunuttum');
    }
    public function sifregonder(Request $request)
    {   
        $yetkili = IsletmeYetkilileri::where('gsm1',$request->telefon)->whereHas('yetkili_olunan_isletmeler',function($q){
            $q->where('aktif',true);
        })->first();
        if($yetkili)
        {
            $random = str_shuffle('1234567890');
            $kod = substr($random, 0, 4);
            $yetkili->dogrulama_kodu = $kod;
            $yetkili->save();
            $isletme = Salonlar::where('id',$yetkili->salon_id)->first();
         
            $headers = array(
                 'Authorization: Key LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5',
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $mesaj = array(
                array("to"=>$yetkili->gsm1,"message"=>$kod.' Bu doğrulama kodunu kullanarak şfirenizi sıfırlayabilirsiniz.'),

            );
            require_once app_path('VoiceTelekom/Sms/SmsApi.php');
            require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
            require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
            //$smsApi = new \SmsApi("smsvt.voicetelekom.com","webfirmam","nBJeB5xb*4");
            $smsApi = new \SmsApi("smsvt.voicetelekom.com","webfirmam","nBJeB5xb*4");
            $request = new \SendMultiSms(); // başına "\" koyman lazım
            
           
            $request->content = $mesaj[0]['message'];
            $request->title = 'Bildirim';
            $toList = array_column($mesaj, 'to'); 
            $request->numbers = $toList;
            $request->encoding = 0;
            $request->sender = 'RANDVMCEPTE';
 
            $request->skipAhsQuery = true;

           

            $response = $smsApi->sendMultiSms($request);

            if($response->err == null){
                Log::info("MessageId: ".$response->pkgID."\n");
                 
            }else{
                Log::info( "SMS Status: ".$response->err->status."\n");
                Log::info("Code: ".$response->err->code."\n");
                Log::info("Message: ".$response->err->message."\n");
                
            }
             
            return array(
                'status'=>true,
                'mesaj'=>''
            );
            exit;

        }
        else
        {
            return array(
                'status'=>false,
                'mesaj'=>'Sistemde girdiğiniz telefon numarasına ait kullanıcı bulunamadı'
            );
            exit;
        }



    }
    public function sifredegistir(Request $request)
    {
        $yetkili = IsletmeYetkilileri::where('dogrulama_kodu',$request->dogrulama_kodu)->first();
        if($yetkili)
        {
            $yetkili->password = Hash::make($request->sifre);
            $yetkili->save();
            return array(
                'status' => true,
                'redirect' => '/isletmeyonetim/girisyap'
            );
            exit;
        }
        else
        {
            return array(
                'status' => false,
                'redirect' => ''
            );
        }

    }

}
