<?php

namespace App\Http\Controllers\AuthSuperAdmin;

use App\Http\Controllers\Controller;
 use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\SistemYonetim\LoginLog;
use App\SistemYonetim\Audit;
use App\SistemYoneticileri;

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
    public function __construct()
    {
        $this->middleware('guest:sistemyonetim')->except(['logout']);
    }
   
    public function showSuperAdminLoginForm(){
        return view('superadmin.login');
    }
    public function login(Request $request){
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required|min:3',
        ]);
        $credential = ['email' => $request->email, 'password' =>$request->password];

        // Aktif olmayan kullanici girisini engelle
        $kullanici = SistemYoneticileri::where('email', $request->email)->first();
        if ($kullanici && isset($kullanici->aktif) && $kullanici->aktif == 0) {
            try {
                LoginLog::create([
                    'user_id' => $kullanici->id,
                    'email_attempt' => $request->email,
                    'basarili' => 0,
                    'hata' => 'Hesap pasif',
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr((string)$request->header('User-Agent'), 0, 255),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {}
            return redirect()->back()->withErrors(['email' => 'Hesabınız pasif edilmiş. Yöneticinize başvurun.']);
        }

        if(Auth::guard('sistemyonetim')->attempt($credential,$request->member)){
            $u = Auth::guard('sistemyonetim')->user();
            try {
                $u->son_giris_tarihi = date('Y-m-d H:i:s');
                $u->son_giris_ip = $request->ip();
                $u->save();
            } catch (\Exception $e) {}
            try {
                LoginLog::create([
                    'user_id' => $u->id,
                    'email_attempt' => $request->email,
                    'basarili' => 1,
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr((string)$request->header('User-Agent'), 0, 255),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {}
            Audit::log('login', 'sistem_yoneticisi', $u->id, $u->name);
            return redirect('/sistemyonetim/v2/dashboard');
        }

        // Basarisiz giris log
        try {
            LoginLog::create([
                'user_id' => $kullanici ? $kullanici->id : null,
                'email_attempt' => $request->email,
                'basarili' => 0,
                'hata' => 'Hatalı kimlik bilgisi',
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string)$request->header('User-Agent'), 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {}

        return redirect()->back()->withInput($request->only('email','remember'));
    }
}
