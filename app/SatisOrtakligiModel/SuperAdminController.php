<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonTuru;
use App\Iller;
use App\Ilceler;
use App\Ulkeler;
use App\Salonlar;
use App\SalonGorselleri;
use App\Personeller;
use App\SalonCalismaSaatleri;
use App\SalonPuanlar;
use App\SalonYorumlar;
use App\PersonelPuanlar;
use App\PersonelYorumlar;
use App\SalonHizmetler;
use App\Randevular;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
 
 use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
   
    public function __construct()
    {

         $this->middleware('auth:sistemyonetim');
       
    }

    
    public function index()
    {
         
          return view('superadmin.dashboard',['title' =>  'Sistem Yönetim Paneli | randevumcepte.com.tr','pageindex' => 0]); 
    }
    
        
}
