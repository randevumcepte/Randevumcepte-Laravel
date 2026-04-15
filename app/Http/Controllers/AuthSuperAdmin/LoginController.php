<?php

namespace App\Http\Controllers\AuthSuperAdmin;

use App\Http\Controllers\Controller;
 use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
        if(Auth::guard('sistemyonetim')->attempt($credential,$request->member)){
            return redirect()->route('superadmin.isletmeler');
        } 
        return redirect()->back()->withInput($request->only('email','remember'));
    }
}
