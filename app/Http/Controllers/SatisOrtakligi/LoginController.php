<?php

namespace App\Http\Controllers\SatisOrtakligi;

use App\Http\Controllers\Controller;
 use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\BildirimKimlikleri;
use App\IsletmeYetkilileri;
use App\Salonlar;
use App\Personeller;
use App\SMSIletimRaporlari;
use App\SatisOrtakligiModel\SatisOrtaklari;
use Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/satisortakligi';

    public function __construct()
    {
        $this->middleware('guest:satisortakligi')->except(['logout']);
    }

    // Login formunu gösteren metod
    public function showLoginForm()
    {
        if(!Auth::user()){

            return view('satisortakligi.login');
            exit;
        }
        else
        {
            return redirect('/satisortakligi');
            exit;
        }
    }

    // Giriş yapma işlemi
    public function login(Request $request)
    {
        // İstediğiniz alanları doğrulamak için kuralları belirleyin
        $this->validateLogin($request);

        // Kullanıcıyı doğrulama işlemi
        $credentials = $this->getCredentials($request);

        // satisortakligi guard ile giriş yapmaya çalışıyoruz
        if (Auth::guard('satisortakligi')->attempt($credentials, $request->filled('remember'))) {
              if(BildirimKimlikleri::where('satis_ortagi_id',Auth::guard('satisortakligi')->user()->id)->where('bildirim_id',$request->bildirimid)->where('cihaz',$request->header('User-Agent'))->count()>0)
                BildirimKimlikleri::where('satis_ortagi_id',Auth::guard('satisortakligi')->user()->id)->where('bildirim_id',$request->bildirimid)->where('cihaz',$request->header('User-Agent'))->delete();
             
            $bildirimkimligi = new BildirimKimlikleri();
            $bildirimkimligi->satis_ortagi_id = Auth::guard('satisortakligi')->user()->id;
            $bildirimkimligi->bildirim_id = $request->bildirimid;
            $bildirimkimligi->cihaz = $request->header('User-Agent');
            $bildirimkimligi->save();
             
            //Auth::guard('satisortakligi')->logoutOtherDevices($request->password);
            return redirect()->route('satisortakligi.dashboard');
        } 
        // Giriş başarısızsa hata mesajı döndür
        return back()->with('error', 'Yanlış kullanıcı adı veya şifre girdiniz!');
    }

    // Giriş bilgilerini alacak metod
    protected function getCredentials(Request $request)
    {
        if (is_numeric($request->email)) {
            // Eğer kullanıcı telefon numarası girmişse
            return [
                'telefon' => $request->email,
                'password' => $request->password,
            ];
        }

        // Normal email giriş işlemi
        return $request->only('email', 'password');
    }

     public function sifremiunuttum(Request $request)
    {
        return view('satisortakligi.sifremi-unuttum');
    }
    public function sifregonder(Request $request)
    {   
        $satisortagi = SatisOrtaklari::where('telefon',$request->telefon)->first();
        if($satisortagi)
        {
            $random = str_shuffle('1234567890');
            $kod = substr($random, 0, 4);
            $satisortagi->dogrulama_kodu = $kod;
            $satisortagi->save();
             
         
            $headers = array(
                 'Authorization: Key LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5',
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $mesaj = array(
                array("to"=>$satisortagi->telefon,"message"=>$kod.' Bu doğrulama kodunu kullanarak satış ortağı paneli şfirenizi sıfırlayabilirsiniz.'),

            );
            $postData = json_encode( array( "originator"=> "RANDVMCEPTE", "messages"=>$mesaj ,"encoding"=>"auto") );

            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
            $response = curl_exec($ch);
             
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
        $yetkili = SatisOrtaklari::where('dogrulama_kodu',$request->dogrulama_kodu)->first();
        if($yetkili)
        {
            $yetkili->password = Hash::make($request->sifre);
            $yetkili->save();
            return array(
                'status' => true,
                'redirect' => '/satisortakligi'
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