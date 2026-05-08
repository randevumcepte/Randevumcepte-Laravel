<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AnketSablon;
use App\AnketGonderim;
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
use App\PersonelHizmetler;
use App\SalonHizmetler;
use App\Randevular;
use App\RandevuHizmetler;
use App\User;
use App\Subeler;
use App\Etkinlikler;
use App\EtkinlikKatilimcilari;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\SessionGuard; 
use App\Arsiv;
use App\FormTaslaklari;

use Exception;
use App\IsletmeYetkilileri;
use Aws\Ses\SesClient;
use GuzzleHttp\Client;
use App\Bildirimler;
use App\Adisyonlar;
use App\AdisyonHizmetler;
use App\AdisyonUrunler;
use App\AdisyonPaketler;
use App\AdisyonPaketSeanslar;
use Aws\Exception\AwsException;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException;
use App\SalonKampanyalar;
use App\SatinAlinanKampanyalar;
 use App\AramaTerimleri;
 use App\AramaTerimleriKampanya;
 use App\IsletmeZiyaretciler;
 use Illuminate\Support\Facades\DB;
 use App\param\GetInstallmentPlanForUser;
 use App\param\Sale3d;
 use Illuminate\Support\Facades\Mail;
 use Hash;
 use App\BildirimKimlikleri;
 use App\KampanyaYonetimi;
 use App\KampanyaKatilimcilari;
 use App\SalonSMSAyarlari;
 use App\SatisOrtakligiModel\SatisOrtaklari;
use PDF;
use App\Uyelik;
use App\PersonelCalismaSaatleri;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
 
         $this->middleware('guest', [ 'except' => 'logout' ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
       echo 'Merhaba';
    }
    public function profilim(){
        if(!Auth::check()) return redirect('/login');
        // View sadece Auth::user() ve $salon kullaniyor; Hizmetler::all() / SalonTuru::all() / Hizmet_Kategorisi::limit(8)
        // gereksiz agir sorgulardi (binlerce kayit) ve Auth::user()->get() yanlis kullanimdi (tum User tablosu).
        $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
        return view('user.profil',['salon' => $salon]);
    }
    public function musteri_profil_guncelleme(Request $request){
         $user = User::where('id',Auth::user()->id)->first();
         $user->name = $request->get('name');
         $user->email = $request->get('email');
         $user->cep_telefon = $request->get('cep_telofon');
         $user->ev_telefon = $request->get('ev_telofon');
         $user->dogum_tarihi = $request->get('dogum_tarihi');
         $user->cinsiyet = $request->get('cinsiyet');
        if (isset($_FILES["profil_resim"]["name"])) {
                        $dosya  = $request->profil_resim;
                        $kaynak = $_FILES["profil_resim"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["profil_resim"]["name"]);
                        $uzanti = explode(".", $_FILES["profil_resim"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "/public/profil_resimleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        } 
                        if($hedef != './')
                            $user->profil_resim = $hedef;
                         else
                           $user->profil_resim = null;
                    }
         $user->save();
         return redirect('/profilim');


    }
    public function musteri_profil_resmi_kaldirma(){
        $user = User::where('id',Auth::user()->id)->first();
        $user->profil_resim = '';
        $user->save();
        return redirect('/profilim');

    }
    public function salonara(Request $request){
            $il = "";
            $ilce = "";
            $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
            $hizmetler = Hizmetler::all();   
            $salongorselleri = SalonGorselleri::all();
            $salonyorumlar = SalonYorumlar::all();
            $iller = Iller::where('aktif',1)->get();
            $ilceler = Ilceler::where('aktif',1)->get();
            $kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_adi','like','%'.$request->get('salon_adi').'%')->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
            $salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_adi','like','%'.$request->get('salon_adi').'%')->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->select('salonlar.*')->get();
            $salonturu = SalonTuru::where('id',$isletme_turu->id)->first();
            $salonturleri = SalonTuru::all();
            return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$iller, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar, 'salonturleri' => $salonturleri]);
    }
    public function avantajlikampanyalar_anasayfa(Request $request,$il,$ilce,$isletme_id,$isletme_adi,$kampanya_id){
         $hizmetkategorileri = Hizmet_Kategorisi::where('avantaj_kosesi',1)->get();
        $hizmetler = Hizmetler::all();
        $salonturleri = SalonTuru::limit(6)->inRandomOrder()->get();
        $salon = Salonlar::where('id',$isletme_id)->first();
        $salongorselleri = SalonGorselleri::all();
        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id', $isletme_id)->orderBy('haftanin_gunu','asc')->get();
        $personeller = Personeller::where('salon_id',$isletme_id)->where('takvimde_gorunsun',true)->get();
        $salonyorumlar = SalonYorumlar::where('salon_id',$isletme_id)->orderBy('updated_at','desc')->get();
        $salonpuanlar = SalonPuanlar::where('salon_id',$isletme_id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$isletme_id)->groupBy('hizmet_kategori_id','bolum')->limit(10)->offset(0)->get();
        $salonsunulanhizmetler = SalonHizmetler::where('salon_id' ,$isletme_id)->get();
        $salongorselikapak = SalonGorselleri::where('salon_id',$isletme_id)->where('kapak_fotografi',1)->value('salon_gorseli');
     
        $kampanya = SalonKampanyalar::where('salon_id',$isletme_id)->where('id',$kampanya_id)->first();
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->orderBy('bolum','asc')->limit(2)->offset(0)->get();
        $aramaterimleri = AramaTerimleriKampanya::where('salon_id',$isletme_id)->where('kampanya_id',$kampanya->id)->orderBy('id','asc')->get();
        $aramaterimimeta = "";
        $aramaterimleritaglar = "";
        $aramaterimianasayfa = "";
        $aramaterimleriid = "";
        if($aramaterimleri->count() > 0){
            $aramaterimleritaglar = array();
            $aramaterimleriid = array();
            $aramaterimianasayfa = $aramaterimleri[0]->arama_terimi;
        
            $i = 1;
            foreach($aramaterimleri as $key => $value){
             $aramaterimimeta .= $value->arama_terimi;
             $aramaterimleritaglar[] = $value->arama_terimi;
             $aramaterimleriid[] = $value->id;
             if($i !== $aramaterimleri->count())
                  $aramaterimimeta .=','; 
            $i++;
        }
        }
      
     /* if($salon->instagram_sayfa){
          
       


       $client = new \GuzzleHttp\Client;
        $token = $salon->instagram_sayfa;
        $response = $client->get('https://api.instagram.com/v1/users/self/media/recent/', [
          'query' => [
            'access_token' => $token
          ]
        ]);
         $instagrampaylasimlar = json_decode($response->getBody(),true);

      }
      else{*/
        $instagrampaylasimlar = "";
      //}
     


      
      
         return view('kampanyadetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa ,'aramaterimleriid' =>$aramaterimleriid,'salongorselikapak' => $salongorselikapak,'instagrampaylasimlar' => $instagrampaylasimlar,'aramaterimleri' => $aramaterimleri,'kampanya'=>$kampanya]);
    }
    /*avantajlı kampanya alt anahtar kelimeler */
    public function avantajlikampanyalar_altsayfa(Request $request,$il,$ilce,$isletme_id,$isletme_adi,$kampanya_id,$arama_terimi,$arama_terim_id){
         $hizmetkategorileri = Hizmet_Kategorisi::where('avantaj_kosesi',1)->get();
        $hizmetler = Hizmetler::all();
        $salonturleri = SalonTuru::limit(6)->inRandomOrder()->get();
        $salon = Salonlar::where('id',$isletme_id)->first();
        $salongorselleri = SalonGorselleri::all();
        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id', $isletme_id)->orderBy('haftanin_gunu','asc')->get();
        $personeller = Personeller::where('salon_id',$isletme_id)->where('takvimde_gorunsun',true)->get();
        $salonyorumlar = SalonYorumlar::where('salon_id',$isletme_id)->orderBy('updated_at','desc')->get();
        $salonpuanlar = SalonPuanlar::where('salon_id',$isletme_id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$isletme_id)->groupBy('hizmet_kategori_id','bolum')->limit(10)->offset(0)->get();
        $salonsunulanhizmetler = SalonHizmetler::where('salon_id' ,$isletme_id)->get();
        $salongorselikapak = SalonGorselleri::where('salon_id',$isletme_id)->where('kapak_fotografi',1)->value('salon_gorseli');
     
        $kampanya = SalonKampanyalar::where('salon_id',$isletme_id)->where('id',$kampanya_id)->first();
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->orderBy('bolum','asc')->limit(2)->offset(0)->get();
        $aramaterimleri = AramaTerimleriKampanya::where('salon_id',$isletme_id)->orderBy('id','asc')->get();
          $i = 1;
           $j = 1;
           $aramaterimleritaglar = array();
           $aramaterimleriid = array();
          $aramaterimimeta = "";
       
        $aramaterimianasayfa = "";
         
        foreach ($aramaterimleri as $key => $value) {
            if($value->id == $arama_terim_id){
                $aramaterimianasayfa = $value->arama_terimi;
                $aramaterimimeta .= $value->arama_terimi;
                $aramaterimleritaglar[] = $value->arama_terimi;
                $aramaterimleriid[] = $value->id;

                if($i !== $aramaterimleri->count())
                        $aramaterimimeta .=','; 
            }
           
        } 
       
        foreach ($aramaterimleri as $key => $value) { 
             if($value->id != $arama_terim_id){
                 $aramaterimimeta .= $value->arama_terimi;
                 $aramaterimleritaglar [] =$value->arama_terimi;
                  $aramaterimleriid[] = $value->id;

                     if($j !== $aramaterimleri->count())
                        $aramaterimimeta .=',';  
             } 
             $j++; 
        } 
     /* if($salon->instagram_sayfa){
          
       


       $client = new \GuzzleHttp\Client;
        $token = $salon->instagram_sayfa;
        $response = $client->get('https://api.instagram.com/v1/users/self/media/recent/', [
          'query' => [
            'access_token' => $token
          ]
        ]);
         $instagrampaylasimlar = json_decode($response->getBody(),true);

      }
      else{*/
        $instagrampaylasimlar = "";
      //}
     


      
      
         return view('kampanyadetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa ,'aramaterimleriid' =>$aramaterimleriid,'salongorselikapak' => $salongorselikapak,'instagrampaylasimlar' => $instagrampaylasimlar,'aramaterimleri' => $aramaterimleri,'kampanya'=>$kampanya]);
    }
    public function salonDetay(Request $request){
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
$salon = Salonlar::where('domain', $domain)->first();
        
         
        $isletme_id = $salon->id;
         $subeler = Subeler::where('salon_id',$isletme_id)->where('aktif',1)->get();
    $ipaddress = '';
     if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'Tanılmanamadı';
       $ziyaretci = new IsletmeZiyaretciler();
      $ziyaretci->ipadres = $ipaddress;
      $ziyaretci->salon_id = $isletme_id;
      $ziyaretci->browser =  $_SERVER['HTTP_USER_AGENT'];
      if(AramaTerimleri::where('salon_id',$isletme_id)->first()!=null){
            $aramaterimi = AramaTerimleri::where('salon_id',$isletme_id)->first();
            $ziyaretci->arama_terimi_id = $aramaterimi->id;
      }
      $ziyaretci->save();


        $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();

        $hizmetler = Hizmetler::all();
        $salonturleri = SalonTuru::all();
       
        $salongorselleri = SalonGorselleri::all();
        
        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id',$isletme_id )->orderBy('haftanin_gunu','asc')->get();
        $personeller = Personeller::where('salon_id',$isletme_id)->where('takvimde_gorunsun',true)->get();
        $salonyorumlar = SalonYorumlar::where('salon_id',$isletme_id)->orderBy('updated_at','desc')->get();
        $salonpuanlar = SalonPuanlar::where('salon_id',$isletme_id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$isletme_id)->where('aktif',1)->groupBy('hizmet_kategori_id')->limit(10)->offset(0)->get();

        $salonsunulanhizmetler = SalonHizmetler::where('salon_id' ,$isletme_id)->where('aktif',1)->get();
        $salongorselikapak = SalonGorselleri::where('salon_id',$isletme_id)->where('kapak_fotografi',1)->value('salon_gorseli');
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->where('aktif',1)->orderBy('bolum','asc')->get();
        $aramaterimleri = AramaTerimleri::where('salon_id',$isletme_id)->orderBy('id','asc')->get();
        $aramaterimimeta = "";
        $aramaterimleritaglar = "";
        $aramaterimianasayfa = "";
        $aramaterimleriid = "";
        if($aramaterimleri->count() > 0){
            $aramaterimleritaglar = array();
            $aramaterimleriid = array();
            $aramaterimianasayfa = $aramaterimleri[0]->arama_terimi;
        
            $i = 1;
            foreach($aramaterimleri as $key => $value){
             $aramaterimimeta .= $value->arama_terimi;
             $aramaterimleritaglar[] = $value->arama_terimi;
             $aramaterimleriid[] = $value->id;
             if($i !== $aramaterimleri->count())
                  $aramaterimimeta .=','; 
            $i++;
        }
        }
      
     /* if($salon->instagram_sayfa){
          
       


       $client = new \GuzzleHttp\Client;
        $token = $salon->instagram_sayfa;
        $response = $client->get('https://api.instagram.com/v1/users/self/media/recent/', [
          'query' => [
            'access_token' => $token
          ]
        ]);
         $instagrampaylasimlar = json_decode($response->getBody(),true);

      }
      else{*/
        $instagrampaylasimlar = "";
      //}
     


      
      
         return view('salondetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa ,'aramaterimleriid' =>$aramaterimleriid,'salongorselikapak' => $salongorselikapak,'instagrampaylasimlar' => $instagrampaylasimlar,'aramaterimleri' => $aramaterimleri,'subeler'=>$subeler]);
     }
     public function gizlilik(Request $request)
     {
         
         $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
        return view('gizlilik',['salon'=> $salon,'titlepage'=>$salon->salon_adi .' Gizlilik ve Kişisel Verileri Koruma Politikası']);
     }
    public function salonDetay_anasayfa(Request $request,$isletme_adi,$isletme_id){
		 $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->where('id',$isletme_id)->first();
		
		 
		$isletme_id = $salon->id;
		 $subeler = Subeler::where('salon_id',$isletme_id)->where('aktif',1)->get();
    $ipaddress = '';
     if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'Tanılmanamadı';
       $ziyaretci = new IsletmeZiyaretciler();
      $ziyaretci->ipadres = $ipaddress;      
      $ziyaretci->salon_id = $isletme_id;
      $ziyaretci->browser =  $_SERVER['HTTP_USER_AGENT'];
      if(AramaTerimleri::where('salon_id',$isletme_id)->first()!=null){
            $aramaterimi = AramaTerimleri::where('salon_id',$isletme_id)->first();
            $ziyaretci->arama_terimi_id = $aramaterimi->id;
      }
      $ziyaretci->save();


        $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();

        $hizmetler = Hizmetler::all();
        $salonturleri = SalonTuru::all();
       
        $salongorselleri = SalonGorselleri::all();
		
        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id',$isletme_id )->orderBy('haftanin_gunu','asc')->get();
        $personeller = Personeller::where('salon_id',$isletme_id)->where('takvimde_gorunsun',true)->get();
        $salonyorumlar = SalonYorumlar::where('salon_id',$isletme_id)->orderBy('updated_at','desc')->get();
        $salonpuanlar = SalonPuanlar::where('salon_id',$isletme_id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$isletme_id)->groupBy('hizmet_kategori_id','bolum')->limit(10)->offset(0)->get();
        $salonsunulanhizmetler = SalonHizmetler::where('salon_id' ,$isletme_id)->get();
        $salongorselikapak = SalonGorselleri::where('salon_id',$isletme_id)->where('kapak_fotografi',1)->value('salon_gorseli');
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->orderBy('bolum','asc')->limit(2)->offset(0)->get();
        $aramaterimleri = AramaTerimleri::where('salon_id',$isletme_id)->orderBy('id','asc')->get();
        $aramaterimimeta = "";
        $aramaterimleritaglar = "";
        $aramaterimianasayfa = "";
        $aramaterimleriid = "";
        if($aramaterimleri->count() > 0){
            $aramaterimleritaglar = array();
            $aramaterimleriid = array();
            $aramaterimianasayfa = $aramaterimleri[0]->arama_terimi;
        
            $i = 1;
            foreach($aramaterimleri as $key => $value){
             $aramaterimimeta .= $value->arama_terimi;
             $aramaterimleritaglar[] = $value->arama_terimi;
             $aramaterimleriid[] = $value->id;
             if($i !== $aramaterimleri->count())
                  $aramaterimimeta .=','; 
            $i++;
        }
        }
      
     /* if($salon->instagram_sayfa){
          
       


       $client = new \GuzzleHttp\Client;
        $token = $salon->instagram_sayfa;
        $response = $client->get('https://api.instagram.com/v1/users/self/media/recent/', [
          'query' => [
            'access_token' => $token
          ]
        ]);
         $instagrampaylasimlar = json_decode($response->getBody(),true);

      }
      else{*/
        $instagrampaylasimlar = "";
      //}
     


      
      
         return view('salondetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa ,'aramaterimleriid' =>$aramaterimleriid,'salongorselikapak' => $salongorselikapak,'instagrampaylasimlar' => $instagrampaylasimlar,'aramaterimleri' => $aramaterimleri,'subeler'=>$subeler]);
     }

     public function salonDetay_altsayfa(Request $request,$isletme_turu,$il,$ilce,$isletme_id,$isletme_adi,$arama_terimi,$arama_terim_id){
          $ipaddress = '';
     if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
          $ipaddress = 'Tanılmanamadı';
       $ziyaretci = new IsletmeZiyaretciler();
      $ziyaretci->ipadres = $ipaddress;
      $ziyaretci->salon_id = $isletme_id;
      $ziyaretci->browser =  $_SERVER['HTTP_USER_AGENT'];
      if(AramaTerimleri::where('id',$arama_terim_id)->first()!=null)
            $ziyaretci->arama_terimi_id = AramaTerimleri::where('id',$arama_terim_id)->value('id');
      $ziyaretci->save();

      $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
        $hizmetler = Hizmetler::all();
        $salonturleri = SalonTuru::all();
        $salon = Salonlar::where('id',$isletme_id)->first();
        $salongorselleri = SalonGorselleri::all();
        $saloncalismasaatleri = SalonCalismaSaatleri::all();
        $personeller = Personeller::all();
        $salonyorumlar = SalonYorumlar::where('salon_id',$isletme_id)->orderBy('updated_at','desc')->get();
        $salonpuanlar = SalonPuanlar::where('salon_id',$isletme_id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$isletme_id)->groupBy('hizmet_kategori_id','bolum')->limit(10)->offset(0)->get();
        $salonsunulanhizmetler = SalonHizmetler::where('salon_id' ,$isletme_id)->get();
        $salongorselikapak = SalonGorselleri::where('salon_id',$isletme_id)->where('kapak_fotografi',1)->value('salon_gorseli');
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->orderBy('bolum','asc')->limit(2)->offset(0)->get();
         $aramaterimimeta = "";
        $aramaterimleri = AramaTerimleri::where('salon_id',$isletme_id)->orderBy('id','asc')->get();
          $i = 1;
           $j = 1;
           $aramaterimleritaglar = array();
           $aramaterimleriid = array();
        foreach ($aramaterimleri as $key => $value) {
            if($value->id == $arama_terim_id){
                $aramaterimianasayfa = $value->arama_terimi;
                $aramaterimimeta .= $value->arama_terimi;
                $aramaterimleritaglar[] = $value->arama_terimi;
                $aramaterimleriid[] = $value->id;

                if($i !== $aramaterimleri->count())
                        $aramaterimimeta .=','; 
            }
           
        } 
       
        foreach ($aramaterimleri as $key => $value) { 
             if($value->id != $arama_terim_id){
                 $aramaterimimeta .= $value->arama_terimi;
                 $aramaterimleritaglar [] =$value->arama_terimi;
                  $aramaterimleriid[] = $value->id;

                     if($j !== $aramaterimleri->count())
                        $aramaterimimeta .=',';  
             } 
             $j++; 
        } 

         return view('salondetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa,'aramaterimleriid' =>$aramaterimleriid ,'salongorselikapak' => $salongorselikapak,'aramaterimleri'=>$aramaterimleri]);
     }



     public function tumSalonlariListele($id){
        if($id==1)
            return redirect('/');
        
     }
     public function tarihsaatadiminagec(Request $request, $id)
        {
    $tarih = $request->randevutarihi ?? date('Y-m-d');
    $day = date('N', strtotime($tarih));

    // Salon çalışma saatlerini kontrol et
    $salonCalisma = SalonCalismaSaatleri::where('salon_id', $id)
        ->where('calisiyor', 1)
        ->where('haftanin_gunu', $day)
        ->first();

    if (!$salonCalisma) {
        return response()->json([
            'tarihsaatbolumu' => "<p>Salon seçtiğiniz tarihte kapalı.</p>"
        ]);
    }
    $personelBilgi = "";
    $personeller = Personeller::whereIn('id',$request->personeller)->get();
    foreach($personeller as $key => $personel){
        $personelBilgi .= "<input type='hidden' value='".$personel->id."' name='secilenpersoneller[]'>".$personel->personel_adi;
        if($key+1 != $personeller->count())
            $personelBilgi .= ", ";
        
    }
    // Personel çalışma saatlerini belirle
    $personelCalismalari = PersonelCalismaSaatleri::whereIn('personel_id', $request->personeller)
        ->where('calisiyor', 1)
        ->where('haftanin_gunu', $day)
        ->get();

    $ortakBaslangic = $salonCalisma->baslangic_saati;
    $ortakBitis = $salonCalisma->bitis_saati;

    foreach ($personelCalismalari as $calisma) {
        if (strtotime($calisma->baslangic_saati) > strtotime($ortakBaslangic)) {
            $ortakBaslangic = $calisma->baslangic_saati;
        }
        if (strtotime($calisma->bitis_saati) < strtotime($ortakBitis)) {
            $ortakBitis = $calisma->bitis_saati;
        }
    }

    $simdikiZaman = (date('Y-m-d') == $tarih) ? date('H:i') : '00:00';
    $randevusaataraligi = Salonlar::find($id)->randevu_saat_araligi;
    Log::info("Tarih ".$tarih);
    // Randevu ve hizmet bilgilerini al
    $randevular = Randevular::where('tarih', $tarih)
        ->whereHas('hizmetler', function($query) use ($request) {
            $query->whereIn('personel_id', $request->personeller)
                  ->orWhereIn('hizmet_id', $request->secilenhizmetler);
        })
        ->with(['hizmetler' => function($query) {
            $query->select('randevu_id', 'sure_dk', 'saat','saat_bitis','personel_id');
        }])
        ->get();

    $dolusaatler = [];

    foreach ($randevular as $randevu) {
        Log::info("randevu : ".$randevu->id);
        foreach($randevu->hizmetler as $rH)
        {
            Log::info("randevu hizmet : ".$rH);
            Log::info("hizmet başlangıç : ".$rH->saat);
             Log::info("hizmet bitiş : ".$rH->saat_bitis);
            $baslangic = strtotime($rH->saat);
            $bitis = strtotime($rH->saat_bitis);
            
            // Tüm zaman aralığını blokla
            for ($t = $baslangic; $t < $bitis; $t += ($randevusaataraligi * 60)) {
                $dolusaatler[] = date('H:i', $t);

            }
        }
       
       
    }
    Log::info("dolu saatler : ",$dolusaatler);
    $dolusaatler = array_unique($dolusaatler);
    $html = '<div class="saatler">';
    $saatindex = 0;

    for ($j = strtotime($ortakBaslangic); $j < strtotime($ortakBitis); $j += ($randevusaataraligi * 60)) {
        $saat = date('H:i', $j);

        if ($saat >= $simdikiZaman && !in_array($saat, $dolusaatler)) {
            $html .= '<div class="input-radio">
                <input class="saatsecimleri" id="time'.$j.'" type="radio" name="randevusaati" value="'.$saat.'">
                <label for="time'.$j.'">'.$saat.'</label>
            </div>';
            $saatindex++;
        } else {
            $html .= '<div class="input-radio">
                <input class="saatsecimleri" id="time'.$j.'" type="radio" name="randevusaati" value="'.$saat.'" disabled>
                <label for="time'.$j.'" title="Bu saat dolu">'.$saat.'</label>
            </div>';
        }
    }

    if ($saatindex == 0) {
        $html .= "<p>Seçtiğiniz tarih için uygun randevu bulunamadı.</p>";
    }

    $html .= '</div>';

    return response()->json([
        'tarihsaatbolumu' => $html,
        'personelbilgi' => $personelBilgi,
    ]);
}

 public function personeladiminagec(Request $request, $id) {
    $salon = Salonlar::where('id', $id)->first();
    
    $personelhizmetleri = PersonelHizmetler::whereIn('hizmet_id', $request->randevuhizmet)->get();
    $secilenhizmetler = Hizmetler::whereIn('id', $request->randevuhizmet)->get();
    $salonhizmetleri = SalonHizmetler::where('salon_id', $id)->get();
    
    $html = "<ul>"; 
    $html_personel_bolumu = "<button id='hizmetseckisminageridon' style='width:200px;border-radius:60px' class='btn btn-primary'><< GERİ DÖN</button><p style='font-size:20px; font-weight:bold'>Personel Seçimi</<p>";
    $allhtml = array();
    
    foreach ($secilenhizmetler as $secilenhizmet) {
        $html .= "<li><input type='hidden' name='secilenhizmetler[]' value='".$secilenhizmet->id."'>".$secilenhizmet->hizmet_adi."</li>";
        
        // Bu hizmeti verebilen personelleri al
        $hizmetPersonelleri = PersonelHizmetler::where('hizmet_id', $secilenhizmet->id)
            ->pluck('personel_id')
            ->toArray();
            
        $personeller = Personeller::where('salon_id', $id)
            ->where('aktif', 1)
            ->where('takvimde_gorunsun', true)
            ->whereIn('id', $hizmetPersonelleri)
            ->get();
        
        $html_personel_bolumu .= "<p>".$secilenhizmet->hizmet_adi." için personel seçiniz</p>".
                            "<form id='personellisteparametreler' method='get'><div class='form-group'>
                                    <select name='personeller[]' style='border-radius:60px'>";
        
        if($personeller->count() > 0) {
            foreach($personeller as $personel) {
                $html_personel_bolumu .= "<option value='".$personel->id."'>".$personel->personel_adi."</option>";
            }
        } else {
            $html_personel_bolumu .= "<option value=''>Bu hizmet için uygun personel bulunamadı</option>";
        }
        
        $html_personel_bolumu .= "</select></div>";          
    }
    
    $html_personel_bolumu .= "<button id='tarihsaatsecimadiminagec' type='submit' class='btn btn-primary width-100 btn-rounded' style='width:100%; margin-top: 10px; margin-bottom: 10px'>DEVAM ET <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i></button></form>";
    $html .= "</ul>";
    
    $allhtml['hizmetliste'] = $html;
    $allhtml['personelbolumu'] = $html_personel_bolumu;
    return $allhtml;
}
    public function personelgetir(Request $request,$id,$subeid)
    {
         $salon = Salonlar::where('id',$id)->first();
      
        $subeler = Subeler::where('salon_id',$id)->where('aktif',1)->get();





         $personelhizmetleri = PersonelHizmetler::whereIn('hizmet_id',$request->randevuhizmet)->get();
          $secilenhizmetler = Hizmetler::whereIn('id',$request->randevuhizmet)->get();

          $salonhizmetleri = SalonHizmetler::where('salon_id',$id)->get();
          $personeller = Personeller::where('sube_id',$subeid)->get();
          
          $html = "<ul>"; 
          $html_personel_bolumu = "<button id='hizmetseckisminageridon' style='width:200px;border-radius:60px' class='btn btn-primary'><< GERİ DÖN</button>";
          $allhtml = array();
         $html_personel_bolumu .= "<p style='font-size:20px; font-weight:bold'>Şube Seçimi</p>";
         $html_personel_bolumu .= "<form id='personellisteparametreler' method='get'><div class='form-group'>";
            
         $html_personel_bolumu .= "<div class='form-group'><select name='sube' style='border-radius:60px'>";
          foreach($subeler as $sube){
              if($sube->id == $subeid || $subeler->count() == 1)
                $html_personel_bolumu .= "<option value='".$sube->id."' selected>".$sube->sube."</option>";
              else
                $html_personel_bolumu .= "<option value='".$sube->id."'>".$sube->sube."</option>";


          }
          $html_personel_bolumu .= "</select></div><p style='font-size:20px; font-weight:bold'>Personel Seçimi</<p>";

         foreach ($secilenhizmetler as $secilenhizmet) 
         {
              $html .= "<li><input type='hidden' name='secilenhizmetler[]' value='".$secilenhizmet->id."'>".$secilenhizmet->hizmet_adi."</li>";
 
              $html_personel_bolumu .= "<p>".$secilenhizmet->hizmet_adi." için personel seçiniz</p>".
                                "
                                        <select name='personeller[]' style='border-radius:60px'>
                                             ";
                                    foreach($personeller as $personelhizmet){
                                        

                                              $html_personel_bolumu .="<option value='".$personelhizmet->id."'>".$personelhizmet->personel_adi."</option>";
                                    }
                                           
                                     $html_personel_bolumu .="</select> </div>" ;          
                                        
         }
         $html_personel_bolumu .="<button id='tarihsaatsecimadiminagec' type='submit' class='btn btn-primary width-100 btn-rounded' style='width:100%; margin-top: 10px; margin-bottom: 10px'>DEVAM ET <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i></button></form>";

         $html .= "</ul>";
         $allhtml['hizmetliste'] = $html;
         $allhtml['personelbolumu'] = $html_personel_bolumu;
         return $allhtml;
    }   
   

     public function randevual($hizmet,$id){
        $salon = Salonlar::where('id',$id)->first();
      
          $secilenhizmetler = explode('_',$hizmet);
           $personelhizmetleri = PersonelHizmetler::whereIn('hizmet_id',$secilenhizmetler)->get();
          $secilenhizmetler = Hizmetler::whereIn('id',$secilenhizmetler)->get();

          $salonhizmetleri = SalonHizmetler::where('salon_id',$id)->get();
          $personeller = Personeller::where('salon_id',$id)->where('takvimde_gorunsun',true)->get();
          
         
         
          $tumhizmetler = Hizmetler::all();
          $hizmetkategorileri = Hizmet_Kategorisi::all();
        
          $salonpuanlar = SalonPuanlar::where('salon_id',$id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$id)->groupBy('hizmet_kategori_id','bolum')->limit(10)->offset(0)->get();
        
         $randevular = Randevular::where('salon_id',$id)->get();
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->orderBy('bolum','asc')->limit(2)->offset(0)->get();

        return view('randevual',['salon'=>$salon, 'salonhizmetleri' => $salonhizmetleri, 'hizmetler'=>$tumhizmetler,'hizmetkategorileri' => $hizmetkategorileri, 'secilenhizmetler'=>$secilenhizmetler, 'personeller' => $personeller, 'personelhizmetleri' => $personelhizmetleri ,'salonpuanlar'=>$salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'hizmetbolumleri' => $hizmetbolumleri,'secilenhizmetlerid' => explode('_',$hizmet),'randevular' =>$randevular]);

     }
     public function saatgetir(Request $request) {
    
       $tarih = $request->randevutarihi ?? date('Y-m-d');
    $day = date('N', strtotime($tarih));
    $id = Salonlar::where('id', $request->isletmeno)->value('id');

    // Salon çalışma saatlerini kontrol et
    $salonCalisma = SalonCalismaSaatleri::where('salon_id', $id)
        ->where('calisiyor', 1)
        ->where('haftanin_gunu', $day)
        ->first();
    
    if (!$salonCalisma) 
        return "<p>Salon seçtiğiniz tarihte kapalı.</p>";     
       

    // Personel çalışma saatlerini belirle
    $personelCalismalari = PersonelCalismaSaatleri::whereIn('personel_id', $request->secilenpersoneller)
        ->where('calisiyor', 1)
                ->where('haftanin_gunu', $day)
        ->get();

    $ortakBaslangic = $salonCalisma->baslangic_saati;
    $ortakBitis = $salonCalisma->bitis_saati;

    foreach ($personelCalismalari as $calisma) {
        if (strtotime($calisma->baslangic_saati) > strtotime($ortakBaslangic)) {
            $ortakBaslangic = $calisma->baslangic_saati;
        }
        if (strtotime($calisma->bitis_saati) < strtotime($ortakBitis)) {
            $ortakBitis = $calisma->bitis_saati;
        }
    }

    $simdikiZaman = (date('Y-m-d') == $tarih) ? date('H:i') : '00:00';
    $randevusaataraligi = Salonlar::find($id)->randevu_saat_araligi;
    Log::info("Tarih ".$tarih);
    // Randevu ve hizmet bilgilerini al
    $randevular = Randevular::where('tarih', $tarih)
        ->whereHas('hizmetler', function($query) use ($request) {
            $query->whereIn('personel_id', $request->secilenpersoneller)
                  ->orWhereIn('hizmet_id', $request->secilenhizmetler);
        })
        ->with(['hizmetler' => function($query) {
            $query->select('randevu_id', 'sure_dk', 'saat','saat_bitis','personel_id');
        }])
        ->get();

    $dolusaatler = [];

    foreach ($randevular as $randevu) {
        Log::info("randevu : ".$randevu->id);
        foreach($randevu->hizmetler as $rH)
        {
            Log::info("randevu hizmet : ".$rH);
            Log::info("hizmet başlangıç : ".$rH->saat);
             Log::info("hizmet bitiş : ".$rH->saat_bitis);
            $baslangic = strtotime($rH->saat);
            $bitis = strtotime($rH->saat_bitis);
            
            // Tüm zaman aralığını blokla
            for ($t = $baslangic; $t < $bitis; $t += ($randevusaataraligi * 60)) {
                $dolusaatler[] = date('H:i', $t);

            }
        }
       
       
    }
    Log::info("dolu saatler : ",$dolusaatler);
    $dolusaatler = array_unique($dolusaatler);
    $html = '<div class="saatler">';
    $saatindex = 0;

    for ($j = strtotime($ortakBaslangic); $j < strtotime($ortakBitis); $j += ($randevusaataraligi * 60)) {
        $saat = date('H:i', $j);

        if ($saat >= $simdikiZaman && !in_array($saat, $dolusaatler)) {
            $html .= '<div class="input-radio">
                <input class="saatsecimleri" id="time'.$j.'" type="radio" name="randevusaati" value="'.$saat.'">
                <label for="time'.$j.'">'.$saat.'</label>
            </div>';
            $saatindex++;
        } else {
            $html .= '<div class="input-radio">
                <input class="saatsecimleri" id="time'.$j.'" type="radio" name="randevusaati" value="'.$saat.'" disabled>
                <label for="time'.$j.'" title="Bu saat dolu">'.$saat.'</label>
            </div>';
        }
    }

    if ($saatindex == 0) {
        $html .= "<p>Seçtiğiniz tarih için uygun randevu bulunamadı.</p>";
    }

    $html .= '</div>';
 
    /*$day = date('N', strtotime($tarih)); // 1 (Pazartesi) ile 7 (Pazar) arasında değer
    $nowtime = date('H:i');
    $html = "";  
    
    $randevusaataraligi = Salonlar::where('id', $request->isletmeno)->value('randevu_saat_araligi');
    
    // Salonun genel çalışma saatleri
    $salonCalisma = SalonCalismaSaatleri::where('salon_id', $request->isletmeno)
        ->where('calisiyor', 1)
        ->where('haftanin_gunu', $day)
        ->first();
    
    if (!$salonCalisma) {
        echo "Salon seçtiğiniz tarihte kapalı.";
        return;
    }
    
    // Personellerin çalışma saatlerini al
    $personelCalismalari = PersonelCalismaSaatleri::whereIn('personel_id', $request->secilenpersoneller)
        ->where('calisiyor', 1)
        ->where('haftanin_gunu', $day)
        ->get();
    
    // Tüm personellerin ortak çalışma saatlerini bul
    $ortakBaslangic = $salonCalisma->baslangic_saati;
    $ortakBitis = $salonCalisma->bitis_saati;
    
    foreach ($personelCalismalari as $calisma) {
        if (strtotime($calisma->baslangic_saati) > strtotime($ortakBaslangic)) {
            $ortakBaslangic = $calisma->baslangic_saati;
        }
        if (strtotime($calisma->bitis_saati) < strtotime($ortakBitis)) {
            $ortakBitis = $calisma->bitis_saati;
        }
    }
    
    // Dolu saatleri belirle
    $dolusaatler = Randevular::join('randevu_hizmetler', 'randevu_hizmetler.randevu_id', '=', 'randevular.id')
        ->where('randevular.tarih', $tarih)
        ->where('durum', 1)
        ->where(function ($q) use($request) { 
            $q->whereIn('randevu_hizmetler.personel_id', $request->secilenpersoneller)
              ->orWhereIn('randevu_hizmetler.hizmet_id', $request->secilenhizmetler);
        })
        ->groupBy('randevular.saat')
        ->pluck('randevular.saat')
        ->map(function($saat) {
            return date('H:i', strtotime($saat));
        })
        ->toArray();
    
    $saatindex = 0;
    for ($j = strtotime($ortakBaslangic); $j < strtotime($ortakBitis); $j += ($randevusaataraligi * 60)) {
        $saat = date('H:i', $j);
        
        // Bugün için şu anki saatten sonrasını, diğer günler için tüm uygun saatleri göster
        if ((date('Y-m-d') == $tarih && $saat > $nowtime) || (date('Y-m-d') != $tarih)) {
            if (!in_array($saat, $dolusaatler)) {
                $html .= '<div class="input-radio">
                    <input class="saatsecimleri" id="time'.$j.'" type="radio" name="randevusaati" value="'.$saat.'">
                    <label name="randevusaati" for="time'.$j.'">'.$saat.'</label>
                </div>';
                $saatindex++;
            } else {
                $html .= '<div class="input-radio">
                    <input class="saatsecimleri" id="time'.$j.'" type="radio" name="randevusaati" value="'.$saat.'" disabled>
                    <label for="time'.$j.'" title="Bu saatte hizmet verebilecek uygun personel bulunmamaktadır">'.$saat.'</label>
                </div>';
            }
        }
    }
    
    if ($saatindex == 0) {
        $html .= "Uygun randevu bulunamadı. Lütfen başka bir tarih seçiniz.";
    }*/
    
    echo $html;
}
     // StoreAdminController::sms_gonder_bildirimli ile ayni desen, ama auth gerektirmez (salon parametre olarak alinir).
     // Mini-website (musteri tarafi) sifre/dogrulama SMSlerini hem gonderir hem SMSIletimRaporlari'na yazar.
     // $mesajlar: [['to'=>'05XXXXXXXXX','message'=>'...']]; $tur: 'sifregonder' vb.
     // Donus: ['success'=>bool, 'error'=>string|null]
     private function sms_gonder_bildirimli_salon($salon, array $mesajlar, $tur)
     {
        if(!$salon){
            return ['success'=>false, 'error'=>'Salon bulunamadi'];
        }
        if(empty($salon->sms_baslik)){
            return ['success'=>false, 'error'=>'sms_baslik bos (salon konfigi eksik)'];
        }
        $aciklama = '';
        foreach($mesajlar as $m) $aciklama .= $m['message']."\n";

        if($salon->yeni_sms == 1)
        {
            try {
                require_once app_path('VoiceTelekom/Sms/SmsApi.php');
                require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
                require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
                $smsApi = new \SmsApi("smsvt.voicetelekom.com", $salon->sms_user_name, $salon->sms_secret);
                $sonHata = null;
                $sonPkgId = null;
                $basariliAdet = 0;
                foreach($mesajlar as $mesaj){
                    $req = new \SendMultiSms();
                    $req->customID = "sms_" . date('Ymd_His') . "_" . substr(md5(microtime()), 0, 8);
                    $req->content = $mesaj['message'];
                    $req->title = 'Online randevu '.$tur;
                    $req->numbers = [$mesaj['to']];
                    $req->encoding = 0;
                    $req->sender = $salon->sms_baslik;
                    $req->skipAhsQuery = true;
                    $resp = $smsApi->sendMultiSms($req);
                    if($resp->err == null){
                        $basariliAdet++;
                        $sonPkgId = $resp->pkgID ?? null;
                    } else {
                        $sonHata = 'VoiceTelekom: '.($resp->err->code ?? '?').' - '.($resp->err->message ?? '');
                    }
                }
                Log::info('[sms_gonder_bildirimli_salon VT] tamamlandi', [
                    'salon_id' => $salon->id, 'tur' => $tur, 'gonderim' => count($mesajlar), 'basarili' => $basariliAdet, 'son_hata' => $sonHata,
                ]);
                if($basariliAdet > 0){
                    try {
                        $rapor = new \App\SMSIletimRaporlari();
                        $rapor->salon_id = $salon->id;
                        $rapor->tur = $tur;
                        $rapor->aciklama = $aciklama;
                        $rapor->rapor_id = $sonPkgId ?: ('vt_'.date('YmdHis').'_'.rand(1000,9999));
                        $rapor->adet = $basariliAdet;
                        $rapor->kredi = 0;
                        $rapor->durum = 0;
                        $rapor->save();
                    } catch(\Throwable $e2){ Log::warning('SMS rapor kaydedilemedi (VT): '.$e2->getMessage()); }
                    return ['success'=>true, 'error'=>null];
                }
                return ['success'=>false, 'error'=>$sonHata ?: 'VoiceTelekom: bilinmeyen hata'];
            } catch(\Throwable $e){
                Log::error('[sms_gonder_bildirimli_salon VT] exception: '.$e->getMessage());
                return ['success'=>false, 'error'=>'VT exception: '.$e->getMessage()];
            }
        }

        // Efetech (yeni_sms == 0) — StoreAdminController ile ayni endpoint
        if(empty($salon->sms_apikey)){
            return ['success'=>false, 'error'=>'sms_apikey bos (salon konfigi eksik)'];
        }
        $headers = [
            'Authorization: Key '.$salon->sms_apikey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        $postData = json_encode([
            'originator' => $salon->sms_baslik,
            'messages'   => $mesajlar,
            'encoding'   => 'auto',
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.efetech.net.tr/v2/sms/multi');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $efeResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $decoded = json_decode($efeResponse, true);
        Log::info('[sms_gonder_bildirimli_salon Efetech] yanit', [
            'salon_id' => $salon->id, 'tur' => $tur, 'http_code' => $httpCode, 'response' => $efeResponse, 'curl_err' => $curlErr,
        ]);
        if($curlErr){
            return ['success'=>false, 'error'=>'Efetech baglanti hatasi: '.$curlErr];
        }
        if($httpCode >= 400){
            return ['success'=>false, 'error'=>'Efetech HTTP '.$httpCode.' - '.substr((string)$efeResponse, 0, 200)];
        }
        if(is_array($decoded) && isset($decoded['response']['message']['id'])){
            try {
                $rapor = new \App\SMSIletimRaporlari();
                $rapor->salon_id = $salon->id;
                $rapor->tur = $tur;
                $rapor->aciklama = $aciklama;
                $rapor->rapor_id = $decoded['response']['message']['id'];
                $rapor->adet = $decoded['response']['message']['count'] ?? count($mesajlar);
                $rapor->kredi = $decoded['response']['message']['total_price'] ?? 0;
                $rapor->durum = 0;
                $rapor->save();
            } catch(\Throwable $e2){ Log::warning('SMS rapor kaydedilemedi (Efetech): '.$e2->getMessage()); }
            return ['success'=>true, 'error'=>null];
        }
        return ['success'=>false, 'error'=>'Efetech beklenmeyen yanit: '.substr((string)$efeResponse, 0, 200)];
     }

     public function kullanicikontrolet(Request $request)
     {
         $kullanici = User::where('cep_telefon',$request->cep_telefon)->first();
         $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
         if($kullanici){
            echo " <div id='hosgeldinizbildirim'>".$salon->salon_adi."'a tekrar hoşgeldiniz! Randevunuzu onaylamak için lütfen mevcut sistem şifrenizi aşağıdaki alana giriniz. Eğer şifrenizi unuttuysanız tekrar gönderilmesi için lütfen <button type='button' id='sifregonder4' class='btn btn-primary small btn-rounded'>buraya tıklayınız</button></div> <div id='sifrealani'>
                                        <div class='form-group'>
                                            <input type='password' id='sifre' name='sifre' placeholder='Mevcut şifreniz'><br />
                                            <button type='button' style='width:100%' id='randevuonayla' class='btn btn-primary btn-rounded'>GÖNDER <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i> </button> 
                                        </div>
                                    </div>
                                    ";
         }
         else
         {
             echo "<div id='hosgeldinizbildirim'>
           ".$salon->salon_adi."'a hoşgeldiniz! Görünüşe göre randevu sistemimizi ilk defa kullanıyorsunuz. Sisteme kayıt olmak ve randevu almak için lütfen adınızı soyadınızı ve cep telefon numaranızı giriniz.</div>
            <div id='adsoyadalani'>
              <div class='form-group'>
            <input type='text' name='adsoyad' id='adsoyad' required placeholder='Adınız Soyadınız'></div> <div class='form-group'>
             
            <button type='button' id='sifregonder2' class='btn btn-primary small btn-rounded'>->> Gönder</button></div></div>
            ";
         }
         
     }
     public function sifregonder(Request $request){
         $kullanici = User::where('cep_telefon',$request->cep_telefon)->first();
         $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();

         // Test/dev domainlerinde sifreyi de ekrana goster (SMS gelmediginde devam edilebilsin)
         $isTestDomain = isset($_SERVER['HTTP_HOST']) && (
            str_contains($_SERVER['HTTP_HOST'], 'apptest.') ||
            str_contains($_SERVER['HTTP_HOST'], 'localhost') ||
            str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')
         );

         if($kullanici){
             $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
             $olusturulansifre = substr($random, 0, 5);
             $kullanici->password = Hash::make($olusturulansifre);
             $kullanici->save();

             $smsMetin = $salon->salon_adi."'a hoşgeldiniz. Randevunuzu oluşturmak için sistem giriş şifreniz : ".$olusturulansifre.". Lütfen giriş yaptıktan sonra şifrenizi hatırlayabileceğiniz güvenli bir şifre ile değiştirmeyi unutmayınız.";
             $mesajlar = [['to' => $request->cep_telefon, 'message' => $smsMetin]];
             $sonuc = $this->sms_gonder_bildirimli_salon($salon, $mesajlar, 'sifregonder');
             $smsBasarili = $sonuc['success'];
             $smsHataMesaji = $sonuc['error'] ?? '';

             if($smsBasarili) {
                $devSifre = $isTestDomain ? "<div style='background:#fef3c7;color:#92400e;padding:8px 12px;border-radius:8px;margin:8px 0;font-size:13px;'>🔧 <b>Test domaini:</b> Şifreniz: <code style='background:#fff;padding:2px 8px;border-radius:4px;font-weight:bold;'>".$olusturulansifre."</code></div>" : '';
                echo " <div id='hosgeldinizbildirim'>".$salon->salon_adi."'a tekrar hoşgeldiniz! Randevunuzu onaylamak için lütfen ".$request->cep_telefon." telefon numaranıza gönderdiğimiz şifrenizi aşağıdaki alana giriniz. Şifreniz birkaç dakika içerisinde ulaşmazsa tekrar gönderilmesi için lütfen <button type='button' id='sifregonder4' class='btn btn-primary small btn-rounded'>buraya tıklayınız</button></div>".$devSifre." <div id='sifrealani'>
                                        <div class='form-group'>
                                            <input type='password' id='sifre' name='sifre' placeholder='Mevcut şifreniz'><br />
                                            <button type='button' style='width:100%' id='randevuonayla' class='btn btn-primary btn-rounded'>GÖNDER <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i> </button>
                                        </div>
                                    </div>
                                    ";
             } else {
                 // SMS basarisiz - test domainde sifreyi goster, yine de devam edilebilsin
                $devSifre = $isTestDomain ? "<div style='background:#fef3c7;color:#92400e;padding:10px 14px;border-radius:8px;margin:8px 0;font-size:13px;'>🔧 <b>Test domaini:</b> SMS gönderilemedi ama şifreniz: <code style='background:#fff;padding:2px 8px;border-radius:4px;font-weight:bold;'>".$olusturulansifre."</code></div>" : '';
                echo "<div id='hosgeldinizbildirim' style='background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:8px;'>SMS gönderilemedi: ".e($smsHataMesaji)."</div>".$devSifre." <div id='sifrealani'>
                        <div class='form-group'>
                            <input type='password' id='sifre' name='sifre' placeholder='Mevcut şifreniz'><br />
                            <button type='button' style='width:100%' id='randevuonayla' class='btn btn-primary btn-rounded'>GÖNDER <i class='fa fa-chevron-right'></i></button>
                        </div>
                    </div>";
             }
         }
         else
         {
            echo "<div id='hosgeldinizbildirim'>
           ".$salon->salon_adi."'a hoşgeldiniz! Görünüşe göre randevu sistemimizi ilk defa kullanıyorsunuz. Sisteme kayıt olmak ve randevu almak için lütfen adınızı soyadınızı ve cep telefon numaranızı giriniz.</div>
            <div id='adsoyadalani'>
              <div class='form-group'>
            <input type='text' name='adsoyad' id='adsoyad' required placeholder='Adınız Soyadınız'></div> <div class='form-group'>
             
            <button type='button' id='sifregonder2' class='btn btn-primary small btn-rounded'>->> Gönder</button></div></div>
            ";
         }
           
         
     }
     public function sifregonder2(Request $request){
        $kullanici = new User();
        $kullanici->name = $request->adsoyad;
        $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
        $kullanici->cep_telefon = $request->ceptelefon;
        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
        $olusturulansifre = substr($random, 0, 5);
        $kullanici->password = Hash::make($olusturulansifre);
        $kullanici->save();

        $isTestDomain = isset($_SERVER['HTTP_HOST']) && (
            str_contains($_SERVER['HTTP_HOST'], 'apptest.') ||
            str_contains($_SERVER['HTTP_HOST'], 'localhost') ||
            str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')
        );

        $smsMetin = $salon->salon_adi."'a hoşgeldiniz. Randevunuzu oluşturmak için sistem giriş şifreniz : ".$olusturulansifre.". Lütfen giriş yaptıktan sonra şifrenizi hatırlayabileceğiniz güvenli bir şifre ile değiştirmeyi unutmayınız.";
        $mesajlar = [['to' => $request->ceptelefon, 'message' => $smsMetin]];
        $sonuc = $this->sms_gonder_bildirimli_salon($salon, $mesajlar, 'sifregonder');

        if($sonuc['success']){
            $devSifre = $isTestDomain ? "<div style='background:#fef3c7;color:#92400e;padding:8px 12px;border-radius:8px;margin:8px 0;font-size:13px;'>🔧 <b>Test domaini:</b> Şifreniz: <code style='background:#fff;padding:2px 8px;border-radius:4px;font-weight:bold;'>".$olusturulansifre."</code></div>" : '';
            echo "<div id='hosgeldinizbildirim'>Randevunuzu onaylamak için lütfen ".$request->ceptelefon." telefon numaranıza gönderdiğimiz şifrenizi aşağıdaki alana giriniz. Şifreniz birkaç dakika içerisinde ulaşmazsa tekrar gönderilmesi için lütfen <button type='button' id='sifregonder3' class='btn btn-primary small btn-rounded'>buraya tıklayınız</button></div>".$devSifre."<div id='sifrealani'>
                                <div class='form-group'>
                                    <input type='password' id='sifre' name='sifre' placeholder='Gönderilen şifreyi giriniz'><br />
                                    <button type='button' style='width:100%' id='randevuonayla' class='btn btn-primary btn-rounded'>GÖNDER <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i></button>
                                </div>
                            </div>";
        } else {
            $devSifre = $isTestDomain ? "<div style='background:#fef3c7;color:#92400e;padding:10px 14px;border-radius:8px;margin:8px 0;font-size:13px;'>🔧 <b>Test domaini:</b> SMS gönderilemedi ama şifreniz: <code style='background:#fff;padding:2px 8px;border-radius:4px;font-weight:bold;'>".$olusturulansifre."</code></div>" : '';
            echo "<div id='hosgeldinizbildirim' style='background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:8px;'>SMS gönderilemedi: ".e($sonuc['error'])."</div>".$devSifre."<div id='sifrealani'>
                                <div class='form-group'>
                                    <input type='password' id='sifre' name='sifre' placeholder='Gönderilen şifreyi giriniz'><br />
                                    <button type='button' style='width:100%' id='randevuonayla' class='btn btn-primary btn-rounded'>GÖNDER <i class='fa fa-chevron-right'></i></button>
                                </div>
                            </div>";
        }
			
 

     }
      
          public function randevuonaylaauth(Request $request){
         $hizmetler_html = "";
            $personeller_html = "";

             $secilenHizmetIds = $request->secilenhizmetler ?? [];
             $secilenPersonelIds = $request->secilenpersoneller ?? [];
             $hizmetliste = Hizmetler::whereIn('id', $secilenHizmetIds)->get();

             foreach ($hizmetliste as $key => $value) {
                 $hizmetler_html .= "<input type='hidden' name='hizmetler[]' value='".$value->id."'>".$value->hizmet_adi."&nbsp;";
                  $personelId = $secilenPersonelIds[$key] ?? 0;
                  $personelliste = Personeller::where('id', $personelId)->first();
                  if (!$personelliste) {
                      $personeller_html .= "<input type='hidden' name='personeller[]' value='0'>
                        <div class='col-md-3' style='float:left; margin:10px;font-size:14px'>Farketmez</div>&nbsp;";
                      continue;
                  }
                   $personeller_html .= "<input type='hidden' name='personeller[]' value='".$personelliste->id."'>
                  <div class='col-md-3' style='float:left; margin:10px;font-size:14px'><div class='author small' style='position: relative;'>
                    <div class='author-image' style='float: none'>";
                 if($personelliste->profil_resmi == null || $personelliste->profil_resmi == ''){
                   if($personelliste->cinsiyet==0)
                        $personeller_html .= '<div class="background-image" style="background-image: url(/public/img/author0.jpg);"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/img/author0.jpg" alt="Profil resmi">';
                    else
                        $personeller_html .= '<div class="background-image" style="background-image: url(/public/img/author1.jpg);"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/img/author1.jpg" alt="Profil resmi">';


               }
               else

                    $personeller_html .= '<div class="background-image" style="background-image: url(/'.$personelliste->profil_resmi.');"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$personelliste->profil_resmi.'" alt="Profil resmi">';

                $personeller_html .= "</div></div></div>".$personelliste->personel_adi."</div>&nbsp;";

             }

            $tarihSaatRaw = $request->randevutarihivesaati;
            if (empty($tarihSaatRaw) || !strtotime($tarihSaatRaw)) {
                return response()->json(['error' => 'Lütfen geçerli bir tarih ve saat seçiniz.'], 422);
            }
            $tarihSaatTs = strtotime($tarihSaatRaw);
            $randevusaati = '<input type="hidden" name="randevusaati" value="'.date('H:i:s', $tarihSaatTs).'">'.date('H:i', $tarihSaatTs);
            $randevutarihi = '<input type="hidden" name="randevutarihi" value="'.date('Y-m-d', $tarihSaatTs).'">'.date('d.m.Y', $tarihSaatTs);
             $randevudokumu = array();
             $randevudokumu['hizmetler'] = $hizmetler_html;
             $randevudokumu['personeller'] = $personeller_html;
             $randevudokumu['randevutarihi'] = $randevutarihi;
             $randevudokumu['randevusaati'] = $randevusaati;
            return  $randevudokumu;

     }
     /**
      * Yeni randevu onay akisi - SMS/sifre gerektirmez.
      * Bot/spam korumalari:
      *  - Honeypot field (_hp): bot'lar gizli alani doldurur, biz reddederiz
      *  - Form start time (_t): bot'lar aninda gonderir, min 3sn olmali
      *  - IP rate limit: saatte max 5 randevu denemesi
      *  - Telefon rate limit: gunde max 3 yeni musteri kaydi
      *  - Strict format validation
      */
     public function randevuonaylaDirekt(\Illuminate\Http\Request $request)
     {
         // 1) Honeypot kontrolu
         if (!empty($request->input('_hp'))) {
             \Log::warning('[randevu-direkt] honeypot dolu', ['ip' => $request->ip(), 'tel' => $request->ceptelefon]);
             return response()->json(['error' => 'Geçersiz istek.'], 422);
         }

         // 2) Form acilis suresinde bot kontrolu (min 3 saniye)
         $formStartedAt = (int) $request->input('_t', 0);
         if ($formStartedAt > 0) {
             $elapsedMs = (int) (microtime(true) * 1000) - $formStartedAt;
             if ($elapsedMs < 3000) {
                 \Log::warning('[randevu-direkt] cok hizli gonderim', ['ip' => $request->ip(), 'elapsedMs' => $elapsedMs]);
                 return response()->json(['error' => 'Lütfen biraz daha yavaş ilerleyin.'], 429);
             }
         }

         // 3) IP bazli rate limit
         $ipKey = 'rdv-rate:ip:'.md5($request->ip());
         $ipCount = (int) \Cache::get($ipKey, 0);
         if ($ipCount >= 5) {
             return response()->json(['error' => 'Çok fazla deneme. Lütfen 1 saat sonra tekrar deneyin.'], 429);
         }

         // 4) KVKK onay
         if (empty($request->input('kvkk'))) {
             return response()->json(['error' => 'Kullanım ve gizlilik koşullarını onaylamanız gerekir.'], 422);
         }

         // 5) Telefon ve ad validation
         $tel = preg_replace('/[^0-9]/', '', (string) $request->input('cep_telefon', ''));
         $ad = trim((string) $request->input('adsoyad', ''));
         if (!preg_match('/^05[0-9]{9}$/', $tel)) {
             return response()->json(['error' => 'Geçerli bir cep telefonu girin (05XXXXXXXXX).'], 422);
         }
         if (mb_strlen($ad) < 2 || mb_strlen($ad) > 100) {
             return response()->json(['error' => 'Adınız ve soyadınız 2-100 karakter olmalı.'], 422);
         }

         // 6) Telefon bazli rate limit (yeni kayit icin)
         $telKey = 'rdv-rate:tel:'.$tel;
         $telCount = (int) \Cache::get($telKey, 0);
         if ($telCount >= 3) {
             return response()->json(['error' => 'Bu numara için bugün çok fazla işlem yapıldı. Yarın tekrar deneyin.'], 429);
         }

         // Mevcut user'i bul ya da yeni olustur (sifre auto-generate, kullanici hic gormeyecek)
         $kullanici = User::where('cep_telefon', $tel)->first();
         if (!$kullanici) {
             $kullanici = new User();
             $kullanici->name = $ad;
             $kullanici->cep_telefon = $tel;
             // Random sifre uretip hash'le (kullanici hic kullanmayacak; sadece hesabin geçerliliği için)
             $randomSifre = bin2hex(random_bytes(16));
             $kullanici->password = \Hash::make($randomSifre);
             $kullanici->save();
         } else if (!empty($ad) && $kullanici->name !== $ad) {
             // Ad guncel degilse guncelle
             $kullanici->name = $ad;
             $kullanici->save();
         }

         // Otomatik login
         \Auth::login($kullanici);

         // Rate limit sayaclarini artir (basarili kayit/login sonrasi)
         \Cache::put($ipKey, $ipCount + 1, now()->addHour());
         \Cache::put($telKey, $telCount + 1, now()->addDay());

         // Randevu ozetini olustur (mevcut randevuonayla1 ile ayni formatta)
         $hizmetler_html = "";
         $personeller_html = "";

         $secilenHizmetIds = $request->secilenhizmetler ?? [];
         $secilenPersonelIds = $request->secilenpersoneller ?? [];
         $hizmetliste = Hizmetler::whereIn('id', $secilenHizmetIds)->get();

         foreach ($hizmetliste as $key => $value) {
             $hizmetler_html .= "<input type='hidden' name='hizmetler[]' value='".$value->id."'>".$value->hizmet_adi."&nbsp;";
             $personelId = $secilenPersonelIds[$key] ?? 0;
             $personelliste = Personeller::where('id', $personelId)->first();
             if (!$personelliste) {
                 $personeller_html .= "<input type='hidden' name='personeller[]' value='0'><div class='col-md-3' style='float:left; margin:10px;font-size:14px'>Farketmez</div>&nbsp;";
                 continue;
             }
             $personeller_html .= "<input type='hidden' name='personeller[]' value='".$personelliste->id."'>";
             $personeller_html .= "<div class='col-md-3' style='float:left; margin:10px;font-size:14px'><div class='author small' style='position: relative;'><div class='author-image' style='float: none'>";
             if ($personelliste->profil_resmi == null || $personelliste->profil_resmi == '') {
                 $img = $personelliste->cinsiyet == 0 ? '/public/img/author0.jpg' : '/public/img/author1.jpg';
                 $personeller_html .= '<div class="background-image" style="background-image: url('.$img.');"><img src="//'.$_SERVER['HTTP_HOST'].$img.'" alt="Profil resmi">';
             } else {
                 $personeller_html .= '<div class="background-image" style="background-image: url(/'.$personelliste->profil_resmi.');"><img src="//'.$_SERVER['HTTP_HOST'].'/'.$personelliste->profil_resmi.'" alt="Profil resmi">';
             }
             $personeller_html .= "</div></div></div>".$personelliste->personel_adi."</div>&nbsp;";
         }

         $tarihSaatRaw = $request->randevutarihivesaati;
         if (empty($tarihSaatRaw) || !strtotime($tarihSaatRaw)) {
             return response()->json(['error' => 'Lütfen geçerli bir tarih ve saat seçiniz.'], 422);
         }
         $tarihSaatTs = strtotime($tarihSaatRaw);
         $randevusaati = '<input type="hidden" name="randevusaati" value="'.date('H:i:s', $tarihSaatTs).'">'.date('H:i', $tarihSaatTs);
         $randevutarihi = '<input type="hidden" name="randevutarihi" value="'.date('Y-m-d', $tarihSaatTs).'">'.date('d.m.Y', $tarihSaatTs);

         return response()->json([
             'hizmetler' => $hizmetler_html,
             'personeller' => $personeller_html,
             'randevutarihi' => $randevutarihi,
             'randevusaati' => $randevusaati,
         ]);
     }

     public function randevuonayla1(Request $request){
        $credential = ['cep_telefon' => $request->ceptelefon, 'password' =>$request->sifre];

          if(Auth::attempt($credential,$request->member)){

            $user = Auth::user();

            $mevcutbildirimkimligi = BildirimKimlikleri::where('user_id',Auth::user()->id)->where('bildirim_id',$request->bildirimid)->first();
            if(!$mevcutbildirimkimligi)
            {
                $bildirimkimligi = new BildirimKimlikleri();
                $bildirimkimligi->user_id = Auth::user()->id;
                $bildirimkimligi->bildirim_id = $request->bildirimid;
                $bildirimkimligi->save();
            }
            $hizmetler_html = "";
            $personeller_html = "";

             $secilenHizmetIds = $request->secilenhizmetler ?? [];
             $secilenPersonelIds = $request->secilenpersoneller ?? [];
             $hizmetliste = Hizmetler::whereIn('id', $secilenHizmetIds)->get();

             foreach ($hizmetliste as $key => $value) {
                 $hizmetler_html .= "<input type='hidden' name='hizmetler[]' value='".$value->id."'>".$value->hizmet_adi."&nbsp;";
                 $personelId = $secilenPersonelIds[$key] ?? 0;
                 $personelliste = Personeller::where('id', $personelId)->first();
                 if (!$personelliste) {
                     $personeller_html .= "<input type='hidden' name='personeller[]' value='0'>
                        <div class='col-md-3' style='float:left; margin:10px;font-size:14px'>Farketmez</div>&nbsp;";
                     continue;
                 }
                  $personeller_html .= "<input type='hidden' name='personeller[]' value='".$personelliste->id."'>
                  <div class='col-md-3' style='float:left; margin:10px;font-size:14px'><div class='author small' style='position: relative;'>
                    <div class='author-image' style='float: none'>";
                 if($personelliste->profil_resmi == null || $personelliste->profil_resmi == ''){
                   if($personelliste->cinsiyet==0)
                        $personeller_html .= '<div class="background-image" style="background-image: url(/public/img/author0.jpg);"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/img/author0.jpg" alt="Profil resmi">';
                    else
                        $personeller_html .= '<div class="background-image" style="background-image: url(/public/img/author1.jpg);"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/img/author1.jpg" alt="Profil resmi">';


               }
               else

                    $personeller_html .= '<div class="background-image" style="background-image: url(/'.$personelliste->profil_resmi.');"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$personelliste->profil_resmi.'" alt="Profil resmi">';

                $personeller_html .= "</div></div></div>".$personelliste->personel_adi."</div>&nbsp;";

             }

            $tarihSaatRaw = $request->randevutarihivesaati;
            if (empty($tarihSaatRaw) || !strtotime($tarihSaatRaw)) {
                return response()->json(['error' => 'Lütfen geçerli bir tarih ve saat seçiniz.'], 422);
            }
            $tarihSaatTs = strtotime($tarihSaatRaw);
            $randevusaati = '<input type="hidden" name="randevusaati" value="'.date('H:i:s', $tarihSaatTs).'">'.date('H:i', $tarihSaatTs);
            $randevutarihi = '<input type="hidden" name="randevutarihi" value="'.date('Y-m-d', $tarihSaatTs).'">'.date('d.m.Y', $tarihSaatTs);
             $randevudokumu = array();
             $randevudokumu['hizmetler'] = $hizmetler_html;
             $randevudokumu['personeller'] = $personeller_html;
             $randevudokumu['randevutarihi'] = $randevutarihi;
             $randevudokumu['randevusaati'] = $randevusaati;
            return  $randevudokumu;
          } 
          else{
            echo '<div style="position:relative;float:left;width:100%;border-radius:10px; color:white; background-color:#dc3545;padding:5px;">
            <div style="width:10%;float:left">
            <img src="https://'.$_SERVER['HTTP_HOST'].'/public/img/error.png" width="20" heigth="20" alt="Hata">
            </div>
            <div style="float:left; width:90%">
            Giriş bilgileriniz hatalıdır. Lütfen yeniden deneyiniz
            </div>
            </div>';
          }

     }
     public function smskampanyabildirimiptal(Request $request){
          $kullanici = User::where('cep_telefon',$request->telefon)->first();
          if($kullanici){
             $kullanici->sms_kampanya_karaliste = 1;
            if($request->neden == 3)
                $kullanici->sms_kampanya_karaliste_nedeni = $request->digerneden;
            if($request->neden == 2)
                $kullanici->sms_kampanya_karaliste_nedeni = 'Gönderilerle ilgilenmiyor';
             if($request->neden == 1)
                $kullanici->sms_kampanya_karaliste_nedeni = 'Çok fazla gönderim yapıldığını bildirdi';
            $kullanici->save();
             echo 'SMS kampanya gönderim listesinden başarı ile çıkarıldınız. Artık kampanya SMS leri almayacaksınız. Anasayfaya yönlendiriliyorsunuz!';
          }

          else{
            echo 'İstek gönderdiğiniz numaraya ait hesap bulunamamıştır. Lütfen yeniden deneyiniz';
          }
         

     }
      public function mailkampanyabildirimiptal(Request $request){
          $kullanici = User::where('email',$request->eposta)->first();
          if($kullanici){
             $kullanici->mail_kampanya_karaliste = 1;
            if($request->neden == 3)
                $kullanici->mail_kampanya_karaliste_nedeni = $request->digerneden;
            if($request->neden == 2)
                $kullanici->mail_kampanya_karaliste_nedeni = 'Gönderilerle ilgilenmiyor';
             if($request->neden == 1)
                $kullanici->mail_kampanya_karaliste_nedeni = 'Çok fazla gönderim yapıldığını bildirdi';
            $kullanici->save();
             echo 'E-posta kampanya gönderim listesinden başarı ile çıkarıldınız. Artık kampanya e-postaları almayacaksınız. Anasayfaya yönlendiriliyorsunuz!';
          }

          else{
            echo 'İstek gönderdiğiniz e-postaya ait hesap bulunamamıştır. Lütfen yeniden deneyiniz';
          }
         

     }
     public function avantajfiyathesapla(Request $request){
         $avantaj = SalonKampanyalar::where('id',$request->avantajid)->first();
         $avantajfiyat = $avantaj->kampanya_fiyat * $request->avantajadedi;
         echo $avantajfiyat .' <span class="simge-tl">&#8378;</span>';
     }
     public function avantajsatinal($kampanyaid){
        $avantaj = SalonKampanyalar::where('id',$kampanyaid)->first();
        $kapakgorsel = SalonGorselleri::where('salon_id',$avantaj->salon_id)->where('kampanya_id',$kampanyaid)->where('kampanya_gorsel_kapak',1)->first();
        $hizmetler = Hizmetler::all();
        $salonturleri = SalonTuru::limit(7)->get();
        $hizmetkategorileri = Hizmet_Kategorisi::where('avantaj_kosesi',1)->get();
       

        $iller = Iller::where('aktif',1)->get();
        $ilceler = Ilceler::where('aktif',1)->get();
         
        return view('avantajsatinal',['avantaj'=>$avantaj,'kapakgorsel' => $kapakgorsel,'hizmetler'=>$hizmetler,'salonturleri'=>$salonturleri,'iller'=>$iller,'ilceler' => $ilceler,'hizmetkategorileri' => $hizmetkategorileri]);



     }
     public function avantajkartodemeadimi(Request $request){
         $kullanicivar = User::where('cep_telefon',$request->ceptelefon)->first();
         $musteriid = "";
         if(!$kullanicivar){
                 $yenimusteri = new User();
             $yenimusteri->name = $request->adsoyad;
             $yenimusteri->cep_telefon = $request->ceptelefon;
             $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
             $olusturulansifre = substr($random, 0, 5); 
             $yenimusteri->password =  Hash::make($olusturulansifre);
             $musteriid = $yenimusteri->id;    
         }
         else
            $musteriid = $kullanicivar->id;

        
         $avantaj = SalonKampanyalar::where('id',$request->avantajid)->first();
         $kupon = new SatinAlinanKampanyalar();
        $kupon->user_id = $musteriid;
        $kupon->kampanya_id = $avantaj->id;
        $kupon->salon_id = $avantaj->salon_id;
        $kupon->adet = $request->avantajadedi;
       
        $kuponkodlari = SatinAlinanKampanyalar::all();       
        
        $kupon->kupon_kodu = $this->kuponkodolustur();
        $kupon->save();
         $avantajdetay = array();
         $avantajdetay['siparis_kod'] = $kupon->id;
         $avantajdetay['avantaj'] = '<strong>'.$avantaj->kampanya_baslik.'</strong><br />'.$avantaj->kampanya_aciklama;
        $avantajdetay['birimfiyat'] = $avantaj->kampanya_fiyat .' <span class="simge-tl">&#8378;</span>';
         $avantajdetay['adet'] = $request->avantajadedi;
          $toplam = $avantaj->kampanya_fiyat * $request->avantajadedi;
         $avantajdetay['toplam'] = '';

         $komisyonlar = new GetInstallmentPlanForUser('16577', 'TP10033903', '69E21AD2FFCE19C9','CCAD72EE-F1D2-4C89-B497-4B9A34F75ECF', 'PROD');
         $komisyonlar->send();
          $komisyonlarsonuc =  $komisyonlar->parse();
         
         $odemesecenekleri[] = array();
         
           $i = 0;
          foreach($komisyonlarsonuc as  $komisyonsonuc){
                $odemeseceneklerihtml = "";
               
               foreach ($komisyonsonuc as $key => $value) {
                  

                   if($value['MO_01']>=0)
                   {
                        $taksittutari = $toplam * ($value['MO_01']/100)+$toplam;
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."'  type='radio' value='1' required>
                                          <span class='checkmark'></span></td><td>Tek Çekim</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_1' value='".round($taksittutari,2)."'>
                                          </td></tr>";
                                           
                   }
                   if($value['MO_02']>=0){
                        $taksittutari = ($toplam * ($value['MO_02']/100)+$toplam)/2;
                        $toplamtutar = ($toplam * ($value['MO_02']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='2' required>
                                          <span class='checkmark'></span></label></td><td>2 Taksit</td>
                                        <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                         <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_2' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                    if($value['MO_03']>=0){
                        $taksittutari = ($toplam * ($value['MO_03']/100)+$toplam)/3;
                        $toplamtutar = ($toplam * ($value['MO_03']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='3' required>
                                          <span class='checkmark'></span></label></td><td>3 Taksit</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                        <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_3' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                    if($value['MO_04']>=0){
                        $taksittutari = ($toplam * ($value['MO_04']/100)+$toplam)/4;
                        $toplamtutar = ($toplam * ($value['MO_04']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='4' required>
                                          <span class='checkmark'></span></label></td><td>4 Taksit</td>
                                       <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                        <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_4' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                     if($value['MO_05']>=0){
                        $taksittutari = ($toplam * ($value['MO_05']/100)+$toplam)/5;
                        $toplamtutar = ($toplam * ($value['MO_05']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='5' required>
                                          <span class='checkmark'></span></label></td><td>5 Taksit</td>
                                          <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_5' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                     if($value['MO_06']>=0){
                        $taksittutari = ($toplam * ($value['MO_06']/100)+$toplam)/6;
                        $toplamtutar = ($toplam * ($value['MO_06']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='6' required>
                                          <span class='checkmark'></span></label></td><td>6 Taksit</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_6' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                     if($value['MO_07']>=0){
                        $taksittutari = ($toplam * ($value['MO_07']/100)+$toplam)/7;
                        $toplamtutar = ($toplam * ($value['MO_07']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='7' required>
                                          <span class='checkmark'></span></label></td><td>7 Taksit</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_7' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                     if($value['MO_08']>=0){
                        $taksittutari = ($toplam * ($value['MO_08']/100)+$toplam)/8;
                        $toplamtutar = ($toplam * ($value['MO_08']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='8' required>
                                          <span class='checkmark'></span></label></td><td>8 Taksit</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2). "<span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_8' value='".round($toplamtutar,2)."'>
                                          </td>";
                   }
                     if($value['MO_09']>=0){
                        $taksittutari = ($toplam * ($value['MO_09']/100)+$toplam)/9;
                        $toplamtutar = ($toplam * ($value['MO_09']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='9' required>
                                          <span class='checkmark'></span></label></td><td>9 Taksit</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_9' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                      if($value['MO_10']>=0){
                        $taksittutari = ($toplam * ($value['MO_10']/100)+$toplam)/10;
                        $toplamtutar = ($toplam * ($value['MO_10']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='10' required>
                                          <span class='checkmark'></span></label></td><td>10 Taksit</td>
                                         <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_10' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                      if($value['MO_11']>=0){
                        $taksittutari = ($toplam * ($value['MO_11']/100)+$toplam)/11;
                        $toplamtutar = ($toplam * ($value['MO_11']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='11' required>
                                          <span class='checkmark'></span></label></td><td>11 Taksit</td>
                                          <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_11' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                      if($value['MO_12']>=0){
                        $taksittutari = ($toplam * ($value['MO_12']/100)+$toplam)/12;
                        $toplamtutar = ($toplam * ($value['MO_12']/100)+$toplam);
                        $odemeseceneklerihtml .= "<tr><td> <label class='checkboxcontainer'>
                                          <input name='taksittekcekimsecenek' data-value='".$value['SanalPOS_ID']."' type='radio' value='12' required>
                                          <span class='checkmark'></span></label></td><td>12 Taksit</td>
                                          <td>".round($taksittutari,2)." <span class='simge-tl'>&#8378;</span></td>
                                          
                                          <td>".round($toplamtutar,2)." <span class='simge-tl'>&#8378;</span>
                                          <input type='hidden' id='toplam_tutar_".$value['SanalPOS_ID']."_12' value='".round($toplamtutar,2)."'>
                                          </td></tr>";
                   }
                   
               }
               $odemesecenekleri[$i]['id']=  $value['SanalPOS_ID'];
               $odemesecenekleri[$i]['tablo'] = $odemeseceneklerihtml;
               $i++;

          }
            
         return response()->json(array(
                'avantajdetay' => $avantajdetay,
                'odemesecenekleri' => $odemesecenekleri,
    
        ));
         

     }
     public static function kuponkodolustur(){
         $random = str_shuffle('1234567890');
          $random2 = str_shuffle('1234567890');
           $random3 = str_shuffle('1234567890');
        $kuponkod1 = substr($random, 0,3);
        $kuponkod2 = substr($random2, 0,3);
        $kuponkod3 = substr($random3, 0,3);
        $kuponkod = $kuponkod1 .'-'.$kuponkod2.'-'.$kuponkod3;
         $kuponkodlari = SatinAlinanKampanyalar::all();
         $kuponkodlariliste = array();
        foreach($kuponkodlari as $kuponkodu){
            array_push($kuponkodlariliste, $kuponkodu->kupon_kodu);
        }
        if(!in_array($kuponkod, $kuponkodlariliste))
            return $kuponkod;
        else
            $this->kuponkodolustur();
        


     }
     public function odemeisleminibaslat(Request $request)
     {
         return view('odeme',['request'=>$request]);

     }
     public function ozelOdeme(Request $request)
     {
          return view('ozelOdeme',['request'=>$request]);
     }
     /*public function odemeisleminibaslat(Request $request){
          $saleObj = new Sale3d('16577', 'TP10033903', '69E21AD2FFCE19C9','CCAD72EE-F1D2-4C89-B497-4B9A34F75ECF', 'PROD');
          $avantajfiyat =  SalonKampanyalar::where('id',$request->avantajid)->value('kampanya_fiyat');
          $avantajadet = $request->avantajadedi;
          $islemtutar = number_format($avantajfiyat * $avantajadet,2, ',', '.');
         $musteriid = SatinAlinanKampanyalar::where('id',$request->kuponkodu)->value('user_id');
 
         $saleObj->send(
        $request->pos_id,
         $request->kartadsoyad,
         $request->kartno,
         $request->kartay,
         $request->kartyil,
         $request->kartcvc,
         '',
        'https://randevumcepte.com.tr/odemebasarisiz',
        'https://randevumcepte.com.tr/odemebasarili/'.$request->kuponkodu.'/'.$musteriid,
        $request->kuponkodu,
        $request->odemeaciklama,
        $request->taksit_sayisi,
        $islemtutar,
        number_format($request->toplam_tutar,2,',','.'),
        null,
        '31.170.123.8',
        'https://randevumcepte.com.tr/avantajsatinal/'.$request->avantajid,
        null,
        null,
        null,
        null,
        null
        );
         $sonuc = $saleObj->parse();
         $index =0; 
         $odemeurl = "";
         $sonuclarstring = "";
         $index = 0;
       
         foreach($sonuc as $sonucliste){ 
            if($index ==1)
                $odemeurl = '<iframe src='.$sonucliste.' style="width: 100%; height: 500px;overflow:  auto"></iframe>';
             $index++;
         }
         echo $odemeurl;
     }
     public function odemebasarili($kuponid,$musteriid){
          $kupon = SatinAlinanKampanyalar::where('id',$kuponid)->first();
          $avantaj = SalonKampanyalar::where('id',$kupon->kampanya_id)->first();
          $musteri = User::where('id',$musteriid)->first();
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
             $olusturulansifre = substr($random, 0, 5);
             $musteri->password = Hash::make($olusturulansifre);
             $musteri->save();
        
          $kupon->odeme_yapildi = 1;
          $kupon->save();
          $mesaj = $avantaj->kampanya_baslik .':'.$avantaj->kampanya_aciklama. ' kampanyası için kupon kodunuz:'.$kupon->kupon_kodu.' ve randevumcepte.com.tr giriş şifreniz:'.$olusturulansifre.'. Detay:https://randevumcepte.com.tr/avantajlarim';

            

            $postUrl="http://panel.1sms.com.tr:8080/api/smspost/v1";
            
             $postData="".
              "<sms>".
              "<username>avantajbu</username>".
              "<password>d42249a8aec4b8b2503105909bcbc329</password>".
              "<header>AVANTAJBU</header>".
              "<validity>2880</validity>".
              "<message>".
              "<gsm>".
              "<no>90".$musteri->cep_telefon."</no>".
              
              "</gsm>".
              "<msg><![CDATA[".$mesaj."]]></msg>".
              "</message>".
              "</sms>"; 
             
              $ch=curl_init();
              curl_setopt($ch,CURLOPT_URL,$postUrl);
              curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
              
              curl_setopt($ch,CURLOPT_POST,1);
              curl_setopt($ch,CURLOPT_TIMEOUT,5);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
              curl_setopt($ch,CURLOPT_HTTPHEADER,Array("Content-Type: text/xml; charset=UTF-8"));

              $response=curl_exec($ch);
              curl_close($ch);

             return redirect('/basariliislem'); 


     }
     public function odemebasarisiz(){
        return redirect('/basarisizislem');
     }
     public function avantajsatinalma(Request $request){

     }
     public function paketsatinalyukseltmeornek(){

          return view ('isletmeadmin.paketorneksayfa',['pageindex'=>1001, 'title' => 'SMS Paketleri | randevumcepte.com.tr İşletme Yönetim Paneli']);
     }
     public function yenikampanyaekle(){
        return view('isletmeadmin.kampanyayayinla',['pageindex'=>1002, 'title' => 'Yeni Kampanya Ekle | randevumcepte.com.tr İşletme Yönetim Paneli']);
     }
      public function seobasvuru(){
        return view('isletmeadmin.seobasvuru',['pageindex'=>1003, 'title' => 'Google Seo Paketleri | randevumcepte.com.tr İşletme Yönetim Paneli']);
     }
     public function smspaketsatinal($paketno){
         $paket = 0;
          if($paketno==1)
             $paket=1;
          else if($paketno==2)
             $paket=2;
          else if($paketno==3)
             $paket=3;
          else if($paketno==4)
             $paket=4;
          return view ('isletmeadmin.smspaketisatinal',['pageindex'=>1001, 'title' => 'SMS Paketi Satın Alma İşlem Onayı | randevumcepte.com.tr İşletme Yönetim Paneli','paket'=> $paket]);
     }*/
    public function odemebasarisiz(){
        return view('isletmeadmin.odeme_basarisiz');
    }
    public function etkinlikkatilimanketi(Request $request,$id,$userid)
    {
        $etkinlik = Etkinlikler::where('id',$id)->first();
        $isletme = Salonlar::where('id',$etkinlik->salon_id)->first();
        $user = User::where('id',$userid)->first();
        $dahaoncecevapladi = false;
        if(EtkinlikKatilimcilari::where('user_id',$userid)->where('etkinlik_id',$id)->value('durum')!=null)
            $dahaoncecevapladi = true;
        return view('etkinlikkatilimanket',[ 'title' => $etkinlik->etkinlik_adi.' Etkinliği Katılım Anketi | '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'dahaoncecevapladi'=>$dahaoncecevapladi,'etkinlik'=>$etkinlik]);
    }
    public function kampanyakatilimanketi(Request $request,$id,$userid)
    {
        $kampanya = KampanyaYonetimi::where('id',$id)->first();
        $isletme = Salonlar::where('id',$kampanya->salon_id)->first();
        $user = User::where('id',$userid)->first();
        $dahaoncecevapladi = false;
        if(KampanyaKatilimcilari::where('user_id',$userid)->where('kampanya_id',$id)->value('durum')!=null)
            $dahaoncecevapladi = true;
        return view('kampanyakatilimanket',[ 'title' => $kampanya->paket_isim.' Kampanyası Katılım Anketi | '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'dahaoncecevapladi'=>$dahaoncecevapladi,'kampanya'=>$kampanya]);
    }
    public function etkinlikkatilimanketicevapla(Request $request)
     {
        $katilim = EtkinlikKatilimcilari::where('user_id',$request->userid)->where('etkinlik_id',$request->etkinlikid)->first();
        $katilim->durum = $request->durum;
        $katilim->save();
        return $request->durum;
     }
     public function kampanyakatilimanketicevapla(Request $request)
     {
        $katilim = KampanyaKatilimcilari::where('user_id',$request->userid)->where('kampanya_id',$request->kampanyaid)->first();
        $katilim->durum = $request->durum;
        $katilim->save();
        return $request->durum;
     }
    /*public function sms_gonder(Request $request,$mesajlar)
    {
        $isletme = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
        $headers = array(
             'Authorization: Key '.$isletme->sms_apikey,
             'Content-Type: application/json',
             'Accept: application/json'
        );
        $postData = json_encode( array( "originator"=> $isletme->sms_baslik, "messages"=> $mesajlar,"encoding"=>"auto") );

        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                
        $response=curl_exec($ch);

                
        
        echo $response;
       
    }*/
    public function arsivmusteriform(Request $request,$id,$user_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = User::where('id',$user_id)->first();
        return view('isletmeadmin.musteriformugonder',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
    public function arsivpersonelform(Request $request,$id,$personel_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = Personeller::where('id',$personel_id)->first();
        return view('isletmeadmin.personelformugonder',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'personel'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
      public function arsivpersonelform2(Request $request,$id,$personel_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = Personeller::where('id',$personel_id)->first();
        return view('isletmeadmin.personelformugonder2',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'personel'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
        public function arsivmusteriform2(Request $request,$id,$user_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = User::where('id',$user_id)->first();
        return view('isletmeadmin.musteriformugonder2',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
    public function arsivmusteriform3(Request $request,$id,$user_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = User::where('id',$user_id)->first();
        return view('isletmeadmin.musteriformugonder3',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
     public function arsivmusteriform4(Request $request,$id,$user_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = User::where('id',$user_id)->first();
        return view('isletmeadmin.musteriformugonder4',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
     public function arsivmusteriform5(Request $request,$id,$user_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = User::where('id',$user_id)->first();
        return view('isletmeadmin.musteriformugonder5',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
       public function arsivmusteriform6(Request $request,$id,$user_id){
        $form=Arsiv::where('id',$id)->first();
        $isletme = Salonlar::where('id',$form->salon_id)->first();
        $user = User::where('id',$user_id)->first();
        return view('isletmeadmin.musteriformugonder6',['title'=>FormTaslaklari::where('id',$form->form_id)->value('form_adi').' Soruları '.$isletme->salon_adi,'user'=>$user,'isletme'=>$isletme,'arsiv'=>$form]);

    }
    public function personelonamformugonderme2(Request $request){

        $form=Arsiv::where('id',$request->arsiv_id)->first();
           $baslik=FormTaslaklari::where('id',$form->form_id)->value('form_adi');
        $taslak = FormTaslaklari::where('id',$form->form_id)->value('taslak');
        $isletme=Salonlar::where('id',$form->salon_id)->first();

        $form->personel_imza = $request->personel_imza;
        $form->toplam_ucret=$request->toplam_ucret;
        $form->kapora=$request->kapora;
        $form->cevapladi2=true;
         $pdfDirectory = 'public/formlar/';
        $pdfPath = $pdfDirectory . str_replace(' ','',$baslik) .'-'. date('Ymdhis',strtotime($form->created_at)).'-'.$form->id. '.pdf';

// Ensure the directory exists before saving the file
        Storage::makeDirectory($pdfDirectory, 0755, true);
            $pdf = PDF::loadView($taslak, [
                'title' => date('Y-m-d-H-i-s'),
                'arsiv'=>$form,
                'isletme'=>$isletme
            ])->setOptions(['defaultFont' => 'sans-serif',

            ]); 
         $pdf->save($pdfPath);

       
        $form->uzanti=$pdfPath;
        $form->save();
        return $form;
    }
     public function musterionamformugonderme3(Request $request){

        $form=Arsiv::where('id',$request->arsiv_id)->first();
        $returnText="";

        $baslik=FormTaslaklari::where('id',$form->form_id)->value('form_adi');
        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$form->salon_id)->where('ayar_id',16)->value('musteri');
        $taslak = FormTaslaklari::where('id',$form->form_id)->value('taslak');
        $isletme=Salonlar::where('id',$form->salon_id)->first();
        
      
        $form->musteri_imza = $request->musteri_imza;
        $form->cevapladi=true;

        if($form->dogrulama_kodu==$request->formdogrulama)
                $returnText="Cevaplarınız tarafımıza ulaşmıştır. Teşekkür ederiz.";
                
        else
            return $returnText="Doğrulama kodu yanlış lütfen tekrar deneyiniz.";
        $pdfDirectory = 'public/formlar/';
        $pdfPath = $pdfDirectory . str_replace(' ','',$baslik) .'-'. date('Ymdhis',strtotime($form->created_at)).'-'.$form->id. '.pdf';

// Ensure the directory exists before saving the file
        Storage::makeDirectory($pdfDirectory, 0755, true);
            $pdf = PDF::loadView($taslak, [
                'title' => date('Y-m-d-H-i-s'),
                'arsiv'=>$form,
                'isletme'=>$isletme
            ])->setOptions(['defaultFont' => 'sans-serif',

            ]); 
         $pdf->save($pdfPath);

       
        $form->uzanti=$pdfPath;
        $form->save();


        $mesaj=$form->musteri->name." isimli müşteri ".FormTaslaklari::where('id',$form->form_id)->value('form_adi')." doldurmuştur";

        self::bildirimekle(
            $request,
            $form->salon_id,
            $mesaj,
            '#form-'.$form->id,
            $form->form_olusturan,
            null,
            IsletmeYetkilileri::where('id',$form->personel->yetkili_id)->value('profil_resim'),
            null,
            $form->id);
        $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$form->personel_id)->pluck('bildirim_id')->toArray(); 
         return array(
       
            'returnText'=>$returnText


        );


    }
     public function musterionamformugonderme2(Request $request){

        $form=Arsiv::where('id',$request->arsiv_id)->first();
        $returnText="";

        $baslik=FormTaslaklari::where('id',$form->form_id)->value('form_adi');
        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$form->salon_id)->where('ayar_id',16)->value('musteri');
        $taslak = FormTaslaklari::where('id',$form->form_id)->value('taslak');
        $isletme=Salonlar::where('id',$form->salon_id)->first();
        
      
        $form->musteri_imza = $request->musteri_imza;
        $form->cevapladi=true;

        if($form->dogrulama_kodu==$request->formdogrulama)
                $returnText="Cevaplarınız tarafımıza ulaşmıştır. Teşekkür ederiz.";
                
        else
            return $returnText="Doğrulama kodu yanlış lütfen tekrar deneyiniz.";
        $pdfDirectory = 'public/formlar/';
        $pdfPath = $pdfDirectory . str_replace(' ','',$baslik) .'-'. date('Ymdhis',strtotime($form->created_at)).'-'.$form->id. '.pdf';

// Ensure the directory exists before saving the file
        Storage::makeDirectory($pdfDirectory, 0755, true);
            $pdf = PDF::loadView($taslak, [
                'title' => date('Y-m-d-H-i-s'),
                'arsiv'=>$form,
                'isletme'=>$isletme
            ])->setOptions(['defaultFont' => 'sans-serif',

            ]); 
         $pdf->save($pdfPath);

       
        $form->uzanti=$pdfPath;
        $form->save();


        $mesaj=$form->musteri->name." isimli müşteri ".FormTaslaklari::where('id',$form->form_id)->value('form_adi')." doldurmuştur";

        self::bildirimekle(
            $request,
            $form->salon_id,
            $mesaj,
            '#form-'.$form->id,
            $form->form_olusturan,
            null,
            IsletmeYetkilileri::where('id',$form->personel->yetkili_id)->value('profil_resim'),
            null,
            $form->id);
        $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$form->personel_id)->pluck('bildirim_id')->toArray(); 
         return array(
       
            'returnText'=>$returnText


        );


    }
    public function musterionamformugonderme(Request $request){

        $form=Arsiv::where('id',$request->arsiv_id)->first();
        $returnText="";

        $baslik=FormTaslaklari::where('id',$form->form_id)->value('form_adi');
        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$form->salon_id)->where('ayar_id',16)->value('musteri');
        $taslak = FormTaslaklari::where('id',$form->form_id)->value('taslak');
        $isletme=Salonlar::where('id',$form->salon_id)->first();
        
        if(isset($request->enfeksiyon))
            $form->enfeksiyon=true;
        else
            $form->enfeksiyon=false;
        if(isset($request->seker))
            $form->seker=true;
        else
            $form->seker=false;
        if(isset($request->alerji))
            $form->alerji_bagisiklik_romatizma=true;
        else
            $form->alerji_bagisiklik_romatizma=false;
        if(isset($request->operasyon))
            $form->operasyon=true;
        else
            $form->operasyon=false;
        if(isset($request->deri_hastaligi))
            $form->deri_hastaligi=true;
        else
            $form->deri_hastaligi=false;
        if(isset($request->kanama))
            $form->kanama=true;
        else
            $form->kanama=false;
        if(isset($request->hepatit))
            $form->hepatit_aids=true;
        else
            $form->hepatit_aids=false;
        if(isset($request->gebelik))
            $form->gebelik=true;
        else
            $form->gebelik=false;
        if(isset($request->son_bir_hafta))
            $form->son_bir_hafta=true;
        else
            $form->son_bir_hafta=false;
        if(isset($request->son_uc_gun))
            $form->son_uc_gun=true;
        else
            $form->son_uc_gun=false;
        if(isset($request->son_bir_ay))
            $form->son_bir_ay=true;
        else
            $form->son_bir_ay=false;
        if(isset($request->son_birkac))
            $form->son_birkac_hafta=true;
        else
            $form->son_birkac_hafta=false;
        if(isset($request->dahaonceislem))
            $form->daha_once_islem=true;
        else
            $form->daha_once_islem=false;
        if(isset($request->kronik))
            $form->kronik = true;
        else
            $form->kronik = false;
        if(isset($request->receteli_ilaclar_var))
            $form->receteli_ilaclar_var = true;
        else
            $form->receteli_ilaclar_var = false;

        
        $form->musteri_imza = $request->musteri_imza;
        $form->cevapladi=true;

        if($form->dogrulama_kodu==$request->formdogrulama)
                $returnText="Cevaplarınız tarafımıza ulaşmıştır. Teşekkür ederiz.";
                
        else
            return $returnText="Doğrulama kodu yanlış lütfen tekrar deneyiniz.";
        $pdfDirectory = 'public/formlar/';
        $pdfPath = $pdfDirectory . str_replace(' ','',$baslik) .'-'. date('Ymdhis',strtotime($form->created_at)).'-'.$form->id. '.pdf';

// Ensure the directory exists before saving the file
        Storage::makeDirectory($pdfDirectory, 0755, true);
            $pdf = PDF::loadView($taslak, [
                'title' => date('Y-m-d-H-i-s'),
                'arsiv'=>$form,
                'isletme'=>$isletme
            ])->setOptions(['defaultFont' => 'sans-serif',

            ]); 
         $pdf->save($pdfPath);

       
        $form->uzanti=$pdfPath;
        $form->save();


        $mesaj=$form->musteri->name." isimli müşteri ".FormTaslaklari::where('id',$form->form_id)->value('form_adi')." doldurmuştur";

        self::bildirimekle(
            $request,
            $form->salon_id,
            $mesaj,
            '#form-'.$form->id,
            $form->form_olusturan,
            null,
            IsletmeYetkilileri::where('id',$form->personel->yetkili_id)->value('profil_resim'),
            null,
            $form->id);
        $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$form->personel_id)->pluck('bildirim_id')->toArray(); 
         return array(
            'enfeksiyon'=>$form->enfeksiyon,
            'seker'=> $form->seker,
            'alerji'=>$form->alerji_bagisiklik_romatizma,
            'operasyon'=> $form->operasyon,
            'deri'=> $form->deri_hastaligi,
            'kanama'=>$form->kanama,
            'hepatit'=> $form->hepatit_aids,
            'gebelik'=> $form->gebelik,
            'birhafta'=> $form->son_bir_hafta,
            'ucgun'=>$form->son_uc_gun,
            'biray'=> $form->son_bir_ay,
            'birkachafta'=>$form->son_birkac_hafta,
            'dahaonceislem'=> $form->daha_once_islem,
            'returnText'=>$returnText


        );


    }
    public function personelonamformugonderme(Request $request){

        $form=Arsiv::where('id',$request->arsiv_id)->first();
           $baslik=FormTaslaklari::where('id',$form->form_id)->value('form_adi');
        $taslak = FormTaslaklari::where('id',$form->form_id)->value('taslak');
        $isletme=Salonlar::where('id',$form->salon_id)->first();

        $form->personel_imza = $request->personel_imza;
        $form->cevapladi2=true;
         $pdfDirectory = 'public/formlar/';
        $pdfPath = $pdfDirectory . str_replace(' ','',$baslik) .'-'. date('Ymdhis',strtotime($form->created_at)).'-'.$form->id. '.pdf';

// Ensure the directory exists before saving the file
        Storage::makeDirectory($pdfDirectory, 0755, true);
            $pdf = PDF::loadView($taslak, [
                'title' => date('Y-m-d-H-i-s'),
                'arsiv'=>$form,
                'isletme'=>$isletme
            ])->setOptions(['defaultFont' => 'sans-serif',

            ]); 
         $pdf->save($pdfPath);

       
        $form->uzanti=$pdfPath;
        $form->save();
        return $form;
    }
    public function bildirimekle(Request $request,$salonid,$mesaj,$url,$personelid,$musteriid,$imgurl,$randevuid,$arsivid)
    {
        $bildirim = new Bildirimler();
        $bildirim->aciklama = $mesaj;
        $bildirim->salon_id = $salonid;
        $bildirim->personel_id = $personelid;
        $bildirim->url = $url;
        $bildirim->tarih_saat = date('Y-m-d H:i:s');
        $bildirim->okundu = false;
        $bildirim->user_id = $musteriid;
        $bildirim->img_src = $imgurl;
        $bildirim->randevu_id = $randevuid;
        $bildirim->arsiv_id=$arsivid;
        $bildirim->save();
    }
    public function odeme_basarili(Request $request)
    {
       
        return view('isletmeadmin.odeme_basarili');
    }
    public function ilcelerigetir(Request $request)
    {
        return Ilceler::where('il_id',$request->il_id)->get();
    }

    public function satisortagikayitol(Request $request)
    {
         return view('satisortakligi.kayit-ol');
    }
    public function satis_ortakligi_kayit(Request $request)
    {
         
         if(SatisOrtaklari::where('telefon',$request->phone)->count() > 0)
            {
                return array('dogrulamakodu'=>'2','mesaj'=>'Belirtilen telefon numarasına ait bir satış ortağı hesabı bulunmaktadır.','status'=>'warning');
                exit();
            } 
        
        else{
            $satisortagi = new SatisOrtaklari();
           
            $satisortagi->ad_soyad = $request->name ." ".$request->surname;
            $satisortagi->email = $request->email;
            $satisortagi->telefon = $request->phone;
            $satisortagi->profil_resmi = '/public/satisortakligipanel/assets/img/auth.png';
            $satisortagi->pasif_ortak = 0;
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 5);
            $satisortagi->password = Hash::make($olusturulansifre);
            $satisortagi ->save();
            DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values (1, "App\\\SatisOrtakligiModel\\\SatisOrtaklari",'.$satisortagi->id.',15)');
             $mesaj = [
                [
                   "to"=>$satisortagi->telefon,"message"=>"RandevumCepte ailesine hoşgeldiniz. Satış ortaklığı panel giriş bilgileriniz.\n\nPanel : https://app.randevumcepte.com.tr/satisortakligi\n Kullanıcı adınız : ".$satisortagi->telefon."\n şifreniz : ".$olusturulansifre
                ]
            ];
            Mail::send(['html' =>"satisortakligi.mail-bildirim"],["hesap_silinsin"=>"","bildirim"=>$satisortagi->ad_soyad ." satış ortaklığı hesabı oluşturdu. Panel : https://app.randevumcepte.com.tr/satisortakligi Kullanıcı adı : ".$satisortagi->telefon." şifre : ".$olusturulansifre, "satis_ortagi"=>$satisortagi], function ($message)  {
            $message->from("info@randevumcepte.com.tr", "RandevumCepte Satış Ortaklığı Yönetim Paneli");
            $message->to("elif@randevumcepte.com.tr", "RandevumCepte Satış Ortaklığı Yönetim Paneli")->subject('RandevumCepte Satış Ortaklığı Yönetim Paneline yeni ortak geldi');
        });

             self::sms_gonder($request,$mesaj);


            return array('dogrulamakodu'=>'0','mesaj'=>'Satış ortaklığı hesabınız başarıyla oluşturulmuştur. Yönlendiriliyorsunuz');
            exit();           
        }
       
        
    }
     
    public function materyalleri_indir(Request $request)
    {
        return view('satisortakligi.materyalleri-indir');
    }
    public function ornekarama(Request $request)
    {
        $date= "16:40";
       /* $mesaj = "Randevumcepteye hoşgeldiniz. Görüşmelerimiz sizlere daha iyi hizmet verebilmek adına altı bin altı bin altı yüz doksan sekiz sayılı kişisel verileri koruma kanunu kapsamında kayıt altına alınacaktır. Dahili numarayı biliyorsanız lütfen tuşlayınız. Bilmiyorsanız operatöre bağlanmak için lütfen bekleyiniz.";
        self::hatirlatmaaramasiyap(114,"05316237563",$mesaj,"",array(471),"",2);*/
         if(date('H:i',strtotime($date)) < date('H:i',strtotime('19:30')) || date('H:i',strtotime($date)) < date('H:i',strtotime('10:00'))){
            echo "içinde";
        }
        else{ 
            echo "dışında";
        }
       
    }
    public function veri_aktarimi(Request $request)
    {
        $adisyonlar =  Adisyonlar::on('mysql_source')->where('salon_id',118)->get();
        foreach($adisyonlar as $adisyon)
        {
             DB::connection('mysql_target')->transaction(function () use ($adisyon) {
                $new = new Adisyonlar();
                $new->setTargetConnection(); // Hedef database bağlantısını değiştir
                $new->fill($adisyon->toArray());
                $new->save();
                foreach($adisyon->hizmetler as $hizmet)
                {
                    $newHizmet = new AdisyonHizmetler();
                    $newHizmet->setTargetConnection(); // Hedef database bağlantısını değiştir
                    $newHizmet->fill($hizmet->toArray());
                    $newHizmet->adisyon_id = $new->id;
                    $newHizmet->save();
                }
             });
        }

    }
    public function drklinik(Request $request)
    {
        $baseUrl = "https://uygulama.drklinik.net"; // CRM’inizin ana URL’sini buraya yazın
        $loginPage = $baseUrl . "/giris.aspx?tip=1"; // Giriş URL’sini tamamladık

        $cookieFile = storage_path('cookies.txt');

        // 1. Giriş sayfasını çek ve action, ViewState & EventValidation değerlerini al
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $loginPage);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        $response = curl_exec($ch);
        curl_close($ch);

        // 2. Formun gerçek action URL'sini tespit et
        preg_match('/<form .*? action="([^"]+)"/', $response, $formAction);
        $relativeAction = isset($formAction[1]) ? $formAction[1] : "giris.aspx?tip=1"; 

        // Eğer form action tam URL değilse, ana URL ile birleştir
        if (!preg_match('/^https?:\/\//', $relativeAction)) {
            $loginUrl = rtrim($baseUrl, '/') . '/' . ltrim($relativeAction, './');
        } else {
            $loginUrl = $relativeAction;
        }

        // 3. ViewState ve EventValidation değerlerini çek
        preg_match('/id="__VIEWSTATE" value="(.*?)"/', $response, $viewState);
        preg_match('/id="__EVENTVALIDATION" value="(.*?)"/', $response, $eventValidation);

        $viewState = $viewState[1] ?? '';
        $eventValidation = $eventValidation[1] ?? '';

        // 4. Giriş isteğini hazırla ve gönder
        $loginData = [
            '__VIEWSTATE' => $viewState,
            '__EVENTVALIDATION' => $eventValidation,
            'txtUsername' => 'Carpediem',  // Kullanıcı adınızı girin
            'txtPassword' => '147258',         // Şifrenizi girin
            'btnLogin' => 'Giriş Yap'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9",
    "Accept-Language: en-US,en;q=0.5",
    "Connection: keep-alive"
]);

        $response = curl_exec($ch);
       

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "HTTP Kod: " . $httpCode . PHP_EOL;
        echo "Response Body: " . substr($response, 0, 500) . PHP_EOL;

        echo file_get_contents($cookieFile);

        curl_close($ch);
    }
    public function sitemap(Request $request)
    {
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $salon = Salonlar::where('domain', $domain)->first();
        if (!$salon) {
            abort(404);
        }

        $aramaterimleri = AramaTerimleri::where('salon_id', $salon->id)->get();
        $host = 'https://' . $_SERVER['HTTP_HOST'];
        $urls = [
            ['loc' => $host . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
        ];

        if ($salon->il_id && $salon->ilce_id && $salon->salon_turu_id) {
            $turu    = self::turkishSlug(optional(SalonTuru::find($salon->salon_turu_id))->salon_turu_adi ?? 'isletme');
            $il      = self::turkishSlug(optional(Iller::find($salon->il_id))->il_adi ?? '');
            $ilce    = self::turkishSlug(optional(Ilceler::find($salon->ilce_id))->ilce_adi ?? '');
            $salonAdi = self::turkishSlug($salon->salon_adi);

            foreach ($aramaterimleri as $keyword) {
                $arama = self::turkishSlug($keyword->arama_terimi);
                $urls[] = [
                    'loc'        => "{$host}/{$turu}/{$il}/{$ilce}/{$salon->id}/{$salonAdi}/{$arama}/{$keyword->id}",
                    'priority'   => '0.8',
                    'changefreq' => 'weekly',
                ];
            }
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(Request $request)
    {
        $host = 'https://' . $_SERVER['HTTP_HOST'];
        return response()
            ->view('robots', ['host' => $host])
            ->header('Content-Type', 'text/plain');
    }

    private static function turkishSlug($text)
    {
        return str_replace(' ', '-', str_replace(
            ['Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', 'ç', 'ğ', 'ı', 'ö', 'ş', 'ü'],
            ['C', 'G', 'I', 'O', 'S', 'U', 'c', 'g', 'i', 'o', 's', 'u'],
            mb_strtolower($text)
        ));
    }

    // ── Dinamik Onam Formu (müşteri tarafı) ─────────────────────────────────

    public function onamFormSayfasi(Request $request, $arsiv_id, $user_id)
    {
        $arsiv = Arsiv::where('id', $arsiv_id)->first();
        if (!$arsiv) abort(404);

        $form = FormTaslaklari::where('id', $arsiv->form_id)->first();
        if (!$form || !$form->is_dinamik) abort(404);

        $isletme = Salonlar::where('id', $arsiv->salon_id)->first();
        $musteri = User::where('id', $user_id)->first();
        $sorular = $form->sorular_json ? json_decode($form->sorular_json, true) : [];

        $hizmet_adi = null; $paket_adi = null;
        if ($arsiv->hizmet_id) {
            try {
                $sh = \DB::table('salon_sunulan_hizmetler')->leftJoin('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')->where('salon_sunulan_hizmetler.id',$arsiv->hizmet_id)->select('hizmetler.hizmet_adi')->first();
                $hizmet_adi = $sh ? $sh->hizmet_adi : null;
            } catch(\Exception $e) {}
        }
        if ($arsiv->paket_id) {
            try { $paket_adi = \DB::table('paketler')->where('id',$arsiv->paket_id)->value('paket_adi'); } catch(\Exception $e) {}
        }

        return view('onamform.musteri_form', [
            'arsiv'         => $arsiv,
            'isletme'       => $isletme,
            'musteri'       => $musteri,
            'form_baslik'   => $form->form_adi,
            'aciklama'      => $form->aciklama ?? '',
            'sorular'       => $sorular,
            'hizmet_adi'    => $hizmet_adi,
            'paket_adi'     => $paket_adi,
            'zaten_dolduruldu' => (bool) $arsiv->cevapladi,
        ]);
    }

    public function onamFormKaydet(Request $request)
    {
        $arsiv = Arsiv::where('id', $request->arsiv_id)->first();
        if (!$arsiv) {
            return response()->json(['basarili' => false, 'mesaj' => 'Form bulunamadı.']);
        }

        if ($arsiv->cevapladi) {
            return response()->json(['basarili' => false, 'mesaj' => 'Bu form zaten doldurulmuş.']);
        }

        if(!$request->musteri_imza || strpos($request->musteri_imza, 'data:') !== 0 || strlen($request->musteri_imza) < 500){
            return response()->json(['basarili'=>false,'mesaj'=>'İmza zorunludur. Lütfen imza alanına net bir imza atın.']);
        }

        if (!$request->dogrulama_kodu || trim($arsiv->dogrulama_kodu) !== trim($request->dogrulama_kodu)) {
            return response()->json(['basarili' => false, 'mesaj' => 'Onay kodu hatalı. Lütfen SMS ile gelen 4 haneli kodu girin.']);
        }

        if(!$request->kvkk_onay){
            return response()->json(['basarili'=>false,'mesaj'=>'KVKK aydınlatma metni onayı zorunludur.']);
        }
        $arsiv->cevaplar_json = $request->cevaplar_json;
        $arsiv->musteri_imza  = $request->musteri_imza;
        $arsiv->cevapladi     = true;
        $arsiv->kvkk_onay     = 1;
        $arsiv->imza_ip       = $request->ip();
        $arsiv->imza_cihaz    = substr($request->header('User-Agent') ?? '', 0, 250);
        $arsiv->imza_zaman    = now();
        $arsiv->save();

        return response()->json(['basarili' => true]);
    }

    public function personelDetayPublic(Request $request, $isletme_adi, $isletme_id, $personel_id)
    {
        $salon = Salonlar::where('domain', $_SERVER['HTTP_HOST'])->where('id', $isletme_id)->first();
        if (!$salon) abort(404);

        $personel = Personeller::where('id', $personel_id)->where('salon_id', $salon->id)->where('aktif', 1)->where('takvimde_gorunsun', true)->first();
        if (!$personel) abort(404);

        $yetkili = \App\IsletmeYetkilileri::where('personel_id', $personel->id)->first();
        $profilResim = $yetkili ? $yetkili->profil_resim : null;
        if (empty($profilResim)) {
            $profilResim = $personel->cinsiyet == 0 ? 'public/img/author0.jpg' : 'public/img/author1.jpg';
        }
        $adSoyad = $yetkili && $yetkili->name ? $yetkili->name : $personel->personel_adi;

        $digerPersoneller = Personeller::where('salon_id', $salon->id)
            ->where('id', '!=', $personel->id)
            ->where('aktif', 1)
            ->where('takvimde_gorunsun', true)
            ->limit(8)
            ->get();

        $sunulanHizmetler = PersonelHizmetler::where('personel_id', $personel->id)
            ->get()
            ->pluck('hizmetler')
            ->filter()
            ->unique('id')
            ->values();

        return view('personeldetay_public', [
            'salon'            => $salon,
            'personel'         => $personel,
            'profilResim'      => $profilResim,
            'adSoyad'          => $adSoyad,
            'digerPersoneller' => $digerPersoneller,
            'sunulanHizmetler' => $sunulanHizmetler,
            'aramaterimisayfa' => $adSoyad,
        ]);
    }

    public function sozlesmeSayfasi(Request $request, $arsiv_id, $user_id)
    {
        $arsiv = Arsiv::where('id', $arsiv_id)->first();
        if (!$arsiv || !$arsiv->is_sozlesme) abort(404);
        $isletme = \App\Salonlar::where('id', $arsiv->salon_id)->first();
        $musteri = User::where('id', $user_id)->first();
        $hizmet_adi = null; $paket_adi = null;
        if ($arsiv->hizmet_id) {
            try {
                $sh = \DB::table('salon_sunulan_hizmetler')
                    ->leftJoin('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')
                    ->where('salon_sunulan_hizmetler.id', $arsiv->hizmet_id)
                    ->select('hizmetler.hizmet_adi')->first();
                $hizmet_adi = $sh ? $sh->hizmet_adi : null;
            } catch(\Exception $e) {}
        }
        if ($arsiv->paket_id) {
            try {
                $paket_adi = \DB::table('paketler')->where('id', $arsiv->paket_id)->value('paket_adi');
            } catch(\Exception $e) {}
        }
        return view('onamform.musteri_sozlesme', [
            'arsiv'           => $arsiv,
            'isletme'         => $isletme,
            'musteri'         => $musteri,
            'hizmet_adi'      => $hizmet_adi,
            'paket_adi'       => $paket_adi,
            'zaten_imzalandi' => (bool) $arsiv->cevapladi,
        ]);
    }

    public function sozlesmeKaydet(Request $request)
    {
        $arsiv = Arsiv::where('id', $request->arsiv_id)->first();
        if (!$arsiv) return response()->json(['basarili'=>false,'mesaj'=>'Sözleşme bulunamadı.']);
        if ($arsiv->cevapladi) return response()->json(['basarili'=>false,'mesaj'=>'Bu sözleşme zaten imzalanmış.']);
        if(!$request->musteri_imza || strpos($request->musteri_imza, 'data:') !== 0 || strlen($request->musteri_imza) < 500){
            return response()->json(['basarili'=>false,'mesaj'=>'İmza zorunludur. Lütfen imza alanına net bir imza atın.']);
        }
        if (!$request->dogrulama_kodu || trim($arsiv->dogrulama_kodu) !== trim($request->dogrulama_kodu)) {
            return response()->json(['basarili'=>false,'mesaj'=>'Onay kodu hatalı. Lütfen SMS ile gelen 4 haneli kodu girin.']);
        }
        if(!$request->kvkk_onay){
            return response()->json(['basarili'=>false,'mesaj'=>'KVKK aydınlatma metni onayı zorunludur.']);
        }
        $arsiv->musteri_imza = $request->musteri_imza;
        $arsiv->cevapladi    = true;
        $arsiv->kvkk_onay    = 1;
        $arsiv->imza_ip      = $request->ip();
        $arsiv->imza_cihaz   = substr($request->header('User-Agent') ?? '', 0, 250);
        $arsiv->imza_zaman   = now();
        $arsiv->save();
        return response()->json(['basarili'=>true]);
    }

    /* ============================================================
     * MUSTERI MEMNUNIYET ANKETI - PUBLIC
     * ============================================================ */

    public function anketSayfasi(Request $request, $token)
    {
        $gonderim = AnketGonderim::where('token', $token)->first();
        if (!$gonderim) abort(404);

        $sablon = AnketSablon::where('id', $gonderim->sablon_id)->first();
        if (!$sablon) abort(404);

        $isletme = Salonlar::where('id', $gonderim->salon_id)->first();
        $sorular = $sablon->sorular_json ? json_decode($sablon->sorular_json, true) : [];

        $suresiBitti = false;
        if ($gonderim->son_gecerlilik && now()->gt($gonderim->son_gecerlilik)) {
            $suresiBitti = true;
        }

        return view('anket.musteri_anket', [
            'gonderim'        => $gonderim,
            'sablon'          => $sablon,
            'isletme'         => $isletme,
            'sorular'         => $sorular,
            'zaten_dolduruldu'=> (bool) $gonderim->cevaplandi,
            'suresi_bitti'    => $suresiBitti,
        ]);
    }

    public function anketKaydet(Request $request)
    {
        $gonderim = AnketGonderim::where('token', $request->token)->first();
        if (!$gonderim) {
            return response()->json(['basarili' => false, 'mesaj' => 'Anket bulunamadı.']);
        }

        if ($gonderim->cevaplandi) {
            return response()->json(['basarili' => false, 'mesaj' => 'Bu anket zaten dolduruldu.']);
        }

        if ($gonderim->son_gecerlilik && now()->gt($gonderim->son_gecerlilik)) {
            return response()->json(['basarili' => false, 'mesaj' => 'Anket linkinin süresi dolmuş.']);
        }

        $sablon = AnketSablon::where('id', $gonderim->sablon_id)->first();
        $sorular = $sablon && $sablon->sorular_json ? json_decode($sablon->sorular_json, true) : [];
        $cevaplar = json_decode($request->cevaplar_json ?? '[]', true) ?: [];

        // Zorunlu soruları doğrula
        $cevapMap = [];
        foreach ($cevaplar as $c) {
            if (isset($c['indeks'])) $cevapMap[$c['indeks']] = $c['cevap'] ?? '';
        }
        foreach ($sorular as $idx => $soru) {
            $tip = $soru['tip'] ?? '';
            // sadece cevap bekleyen tipler
            if (in_array($tip, ['bolum_basligi','bilgi_metni','metin_blogu'])) continue;
            if (!empty($soru['zorunlu'])) {
                $deger = $cevapMap[$idx] ?? '';
                if ($deger === '' || $deger === null || (is_array($deger) && count($deger) === 0)) {
                    return response()->json(['basarili'=>false,'mesaj'=>'Lütfen tüm zorunlu soruları cevaplayın.']);
                }
            }
        }

        // NPS / CSAT skorlarını cache'le
        $npsSkoru = null; $csatToplam = 0; $csatSayi = 0; $genelYorum = null;
        foreach ($sorular as $idx => $soru) {
            $tip = $soru['tip'] ?? '';
            $deger = $cevapMap[$idx] ?? null;
            if ($deger === null || $deger === '') continue;
            if ($tip === 'nps') {
                $n = (int) $deger;
                if ($n >= 0 && $n <= 10 && $npsSkoru === null) $npsSkoru = $n;
            } elseif ($tip === 'csat_yildiz') {
                $n = (int) $deger;
                if ($n >= 1 && $n <= 5) { $csatToplam += $n; $csatSayi++; }
            } elseif ($tip === 'uzun_metin' && !$genelYorum) {
                $genelYorum = mb_substr((string)$deger, 0, 2000);
            }
        }
        $csatSkoru = $csatSayi > 0 ? round($csatToplam / $csatSayi, 2) : null;

        $gonderim->cevaplar_json = $request->cevaplar_json;
        $gonderim->cevaplandi    = true;
        $gonderim->cevap_zamani  = now();
        $gonderim->nps_skoru     = $npsSkoru;
        $gonderim->csat_skoru    = $csatSkoru;
        $gonderim->genel_yorum   = $genelYorum;
        $gonderim->ip            = $request->ip();
        $gonderim->user_agent    = substr($request->header('User-Agent') ?? '', 0, 250);
        $gonderim->kvkk_onay     = $request->kvkk_onay ? 1 : 0;
        $gonderim->save();

        // Premium "Reputation Booster" mantığı
        $salon = Salonlar::where('id', $gonderim->salon_id)->first();
        $googleUrl = null;
        if ($salon && $salon->reputation_premium_aktif) {
            // Yüksek puan → Google Review yönlendirmesi
            if ($salon->google_review_url) {
                $yuksekNps  = $npsSkoru !== null && $npsSkoru >= ($salon->google_review_esik_nps ?? 9);
                $yuksekCsat = $csatSkoru !== null && $csatSkoru >= ($salon->google_review_esik_csat ?? 4.5);
                if ($yuksekNps || $yuksekCsat) {
                    $googleUrl = $salon->google_review_url;
                }
            }

            // Düşük puan → admin/yetkili SMS uyarısı
            $dusukNps  = $npsSkoru !== null && $npsSkoru <= ($salon->kotu_puan_uyari_esik_nps ?? 6);
            $dusukCsat = $csatSkoru !== null && $csatSkoru <= ($salon->kotu_puan_uyari_esik_csat ?? 2.5);
            if (($dusukNps || $dusukCsat) && !$gonderim->kotu_puan_uyari_gonderildi) {
                $uyariTel = $salon->kotu_puan_uyari_telefon;
                if ($uyariTel) {
                    $musteriAd = $gonderim->ad_soyad ?: 'Müşteri';
                    $skor = $npsSkoru !== null ? ('NPS '.$npsSkoru.'/10') : ('Puan '.$csatSkoru.'/5');
                    $yorumOzet = $genelYorum ? (' Yorum: ' . mb_substr($genelYorum, 0, 80)) : '';
                    $mesaj = '⚠️ DÜŞÜK PUAN UYARISI — '.$skor.' | '.$musteriAd.' ('.$gonderim->telefon.') anket doldurdu.'.$yorumOzet.' Hemen iletişime geçin.';
                    try {
                        $ctrl = app()->make(\App\Http\Controllers\Controller::class);
                        $ctrl->sms_gonder($salon->id, [['to' => $uyariTel, 'message' => $mesaj]]);
                        $gonderim->kotu_puan_uyari_gonderildi = true;
                        $gonderim->save();
                    } catch(\Exception $e) {
                        \Log::error('Kötü puan SMS uyarısı hata: '.$e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            'basarili' => true,
            'google_review_url' => $googleUrl,  // null veya URL — frontend buton göstermeyi karar verir
        ]);
    }

    public function anketGoogleTiklandi(Request $request)
    {
        // Public endpoint — anket teşekkür sayfasından "Google'da Yorum Yaz" tıklanınca tracking
        try {
            $token = $request->token;
            $g = AnketGonderim::where('token', $token)->first();
            if ($g && !$g->google_yonlendirildi) {
                $g->google_yonlendirildi = true;
                $g->save();
            }
            return response()->json(['basarili' => true]);
        } catch (\Exception $e) {
            return response()->json(['basarili' => false]);
        }
    }
}
