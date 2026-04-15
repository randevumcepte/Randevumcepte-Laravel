<?php

namespace App\Http\Controllers\Auth;


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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
     public function login(Request $request){
        $credential = array();
       
        
        $this->validate($request,[
            'cep_telefon' => 'required|regex:/(5)[0-9]{9}/|numeric|digits:10',
                     
            'password' => 'required|min:3',
          ]);
         $credential = ['cep_telefon' => $request->cep_telefon, 'password' =>$request->password];
          
      
        
        if(Auth::attempt($credential,$request->member)){
            return redirect('/');
        } 
        return redirect()->back()->withInput($request->only('cep_telefon','remember'));
    }
    
}
