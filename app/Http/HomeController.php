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
 use App\PersonelHizmetler;
use App\SalonHizmetler;
use App\Randevular;
use App\RandevuHizmetler;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 


 use App\AramaTerimleri;
 use Illuminate\Support\Facades\DB;
 use Mail;
 use Hash;

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
        $hizmetkategorileri = Hizmet_Kategorisi::all();
        return view('welcome');
    }
    public function profilim(){

         if(!Auth::check()) return redirect('/login');
        $user = Auth::user()->get();
        $hizmetkategorileri = Hizmet_Kategorisi::all();
    $hizmetler = Hizmetler::all();
    $salonturleri = SalonTuru::all();
        return view('user.profil',['userinfo' => $user,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri]);
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
                                $hedef   =  "public/profil_resimleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
                        $user->profil_resim = $hedef;
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
            $hizmetkategorileri = Hizmet_Kategorisi::all();
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
    public function salonDetay_anasayfa(Request $request,$isletme_turu,$il,$ilce,$isletme_id,$isletme_adi){
        $hizmetkategorileri = Hizmet_Kategorisi::all();
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
        $aramaterimleri = AramaTerimleri::where('salon_id',$isletme_id)->get();
        $aramaterimleritaglar = array();
       $aramaterimleriid = array();
        $aramaterimianasayfa = $aramaterimleri[0]->arama_terimi;
        $aramaterimimeta = "";
        $i = 1;
        foreach($aramaterimleri as $key => $value){
             $aramaterimimeta .= $value->arama_terimi;
             $aramaterimleritaglar[] = $value->arama_terimi;
             $aramaterimleriid[] = $value->id;
             if($i !== $aramaterimleri->count())
                  $aramaterimimeta .=','; 
            $i++;
        }

        $instagrampaylasimlar = []; 
       


        $client = new \GuzzleHttp\Client;
        $token = '4574389641.M2E4MWE5Zg==.ZDk5MzlhY2NlZTYw.NGU5OGE4MjNkZjY5ZGVjZDg1MDM=';
        $response = $client->get('https://api.instagram.com/v1/users/self/', [
          'query' => [
            'access_token' => $token
          ]
        ]);
         $instagrampaylasimlar = json_decode((string) $response->getBody(), true)['instagrampaylasimlar'];


      
      
         return view('salondetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa ,'aramaterimleriid' =>$aramaterimleriid,'salongorselikapak' => $salongorselikapak,'instagrampaylasimlar'=>$instagrampaylasimlar]);
     }
     public function salonDetay_altsayfa(Request $request,$isletme_turu,$il,$ilce,$isletme_id,$isletme_adi,$arama_terimi,$arama_terim_id){
        $hizmetkategorileri = Hizmet_Kategorisi::all();
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
        $aramaterimleri = AramaTerimleri::where('salon_id',$isletme_id)->get();
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

         return view('salondetaylari',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salon' => $salon, 'salongorselleri' => $salongorselleri,'personeller' => $personeller,'saloncalismasaatleri' => $saloncalismasaatleri,'salonyorumlar' => $salonyorumlar,'salonpuanlar' => $salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'salonsunulanhizmetler' => $salonsunulanhizmetler, 'hizmetbolumleri' => $hizmetbolumleri,'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa,'aramaterimleriid' =>$aramaterimleriid ,'salongorselikapak' => $salongorselikapak]);
     }



     public function tumSalonlariListele($id){
        if($id==1)
            return redirect('/');
        
     }
     public function tarihsaatadiminagec(Request $request,$id){
            $personelidsira = "";
            foreach ($request->personeller as $key => $value) {
                 if($key+1 != sizeof($request->personeller))
                        $personelidsira .= $value.',';
                else
                    $personelidsira .= $value;
            }
  
          $personeller = Personeller::whereIn('id',$request->personeller)->orderByRaw('field(id,'.$personelidsira.')')->get();
          $htmlall = array();
          $html_tarih_saat_bolumu =  "";

          $html = "<ul style='list-style:none'>";
          foreach ($personeller as $personel) {
              $html .="<li> <input type='hidden' value='".$personel->id."' name='secilenpersoneller[]'><div class='author small' style='position: relative;'>
                                                         <div class='author-image' style='float: none'>
                                                            ";
               if($personel->profil_resmi == null || $personel->profil_resmi == ''){
                   if($personel->cinsiyet==0)
                        $html .= '<div class="background-image" style="background-image: url(/public/img/author0.jpg);"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/img/author0.jpg" alt="Profil resmi">';
                    else
                        $html .= '<div class="background-image" style="background-image: url(/public/img/author1.jpg);"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/img/author1.jpg" alt="Profil resmi">';
                                                                  
                                                               
               }
               else

                    $html .= '<div class="background-image" style="background-image: url("http://'.$_SERVER['SERVER_NAME'].'/'.$personel->profil_resmi.'");"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$personel->profil_resmi.'" alt="Profil resmi">';
              $html .='</div></div></div>'.$personel->personel_adi.'</li>';
              
          }
          $html .= "</ul>";
          $htmlall['personelbilgi'] = $html;
          
          
          $tarih = date('Y-m-d');
           
          $day = 0; 
          if(date('D', strtotime($tarih))=='Mon') $day=1;
          else if(date('D', strtotime($tarih))=='Tue') $day=2;
          else if(date('D', strtotime($tarih))=='Wed') $day=3;
          else if(date('D', strtotime($tarih))=='Thu') $day=4;
          else if(date('D', strtotime($tarih))=='Fri') $day=5;
          else if(date('D', strtotime($tarih))=='Sat') $day=6;
          else if(date('D', strtotime($tarih))=='Sun') $day=7;
          $nowtime = date('H:i');
           
          $randevusaataraligi = Salonlar::where('id',$id)->value('randevu_saat_araligi');
          $mesaibaslangic =  SalonCalismaSaatleri::where('salon_id',$id)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('baslangic_saati');
          $mesaibitis = SalonCalismaSaatleri::where('salon_id',$id)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('bitis_saati');
          
          $dolusaatler = array();
          $musaitolmayansaatler = Randevular::join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')->where('randevular.tarih',date('Y-m-d'))->where(function ($q) use($request)
                        { 
                            $q->whereIn('randevu_hizmetler.personel_id',$request->personeller);
                              $q->orWhereIn('randevu_hizmetler.hizmet_id',$request->secilenhizmetler);
                                                        
                        }
                    )->groupBy('randevular.saat')->select('randevular.*')->get();
          foreach ($musaitolmayansaatler as $musaitolmayansaat) {
               $dolusaatler[] = date('H:i' ,strtotime($musaitolmayansaat->saat));
          }
          $html_tarih_saat_bolumu .= ' <div id="saatsecimtablosu" class="saatler">';
          $saatindex = 0;
          for($j = strtotime(date('H:i', strtotime($mesaibaslangic) )) ; $j < strtotime(date('H:i', strtotime($mesaibitis))); $j+=($randevusaataraligi * 60)){
                 if(date('H:i',$j) > $nowtime){
                    if(!in_array(date('H:i',$j), $dolusaatler))
                      $html_tarih_saat_bolumu .= '<div class="input-radio"><input class="saatsecimleri" id="time'.$j.'"  type="radio" name="randevusaati" value="'.date('H:i',$j).'"> <label name="randevusaati" for="time'.$j.'">'. date('H:i',$j).'</label></div>';
                    else
                        $html_tarih_saat_bolumu .= '<div class="input-radio"><input class="saatsecimleri" id="time'.$j.'"  type="radio" name="randevusaati" value="'.date('H:i',$j).'" disabled> <label for="time'.$j.'" title="Bu saatte hizmet verebilecek uygun personel bulunmamaktadır">'. date('H:i',$j).'</label></div>';
                 }
                    
                $saatindex ++;             

          }
          if($saatindex == 0){
               $html_tarih_saat_bolumu .= "<p>Uygun randevu bulunamadı. Lütfen başka bir tarih seçiniz.</p>";
          }
          $html_tarih_saat_bolumu .= '</div>';
          $htmlall['tarihsaatbolumu'] =  $html_tarih_saat_bolumu;

          return $htmlall;
     }
    public function personeladiminagec(Request $request,$id){
         $salon = Salonlar::where('id',$id)->first();
      
          
           $personelhizmetleri = PersonelHizmetler::whereIn('hizmet_id',$request->randevuhizmet)->get();
          $secilenhizmetler = Hizmetler::whereIn('id',$request->randevuhizmet)->get();

          $salonhizmetleri = SalonHizmetler::where('salon_id',$id)->get();
          $personeller = Personeller::where('salon_id',$id)->get();
          
          $html = "<ul>"; 
          $html_personel_bolumu = "<button id='hizmetseckisminageridon' style='width:100%;border-radius:60px' class='btn btn-primary'><< GERİ DÖN</button><p style='font-size:20px; font-weight:bold'>Personel Seçimi</<p>";
          $allhtml = array();
         foreach ($secilenhizmetler as $secilenhizmet) 
         {
              $html .= "<li><input type='hidden' name='secilenhizmetler[]' value='".$secilenhizmet->id."'>".$secilenhizmet->hizmet_adi."</li>";
 
              $html_personel_bolumu .= "<p>".$secilenhizmet->hizmet_adi." için personel seçiniz</p>".
                                "<form id='personellisteparametreler' method='get'><div class='form-group'>
                                        <select name='personeller[]'>
                                            <option value='0'>Farketmez</option>";
                                    foreach($personelhizmetleri as $personelhizmet){
                                        if($personelhizmet->hizmet_id == $secilenhizmet->id)
                                              $html_personel_bolumu .="<option value='".$personelhizmet->personeller->id."'>".$personelhizmet->personeller->personel_adi."</option>";
                                    }
                                           
                                     $html_personel_bolumu .="</select> </div>" ;          
                                        
         }
             $html_personel_bolumu .="<button id='tarihsaatsecimadiminagec' type='submit' class='btn btn-primary width-100' style='width:100%; margin-top: 10px; margin-bottom: 10px'>DEVAM ET <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i></button></form>";

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
          $personeller = Personeller::where('salon_id',$id)->get();
          
         
         
          $tumhizmetler = Hizmetler::all();
          $hizmetkategorileri = Hizmet_Kategorisi::all();
        
          $salonpuanlar = SalonPuanlar::where('salon_id',$id)->get();
        $salonsunulanhizmetler_kategori = SalonHizmetler::where('salon_id' ,$id)->groupBy('hizmet_kategori_id','bolum')->limit(10)->offset(0)->get();
        
         $randevular = Randevular::where('salon_id',$id)->get();
        $hizmetbolumleri = SalonHizmetler::groupBy('bolum')->orderBy('bolum','asc')->limit(2)->offset(0)->get();

        return view('randevual',['salon'=>$salon, 'salonhizmetleri' => $salonhizmetleri, 'hizmetler'=>$tumhizmetler,'hizmetkategorileri' => $hizmetkategorileri, 'secilenhizmetler'=>$secilenhizmetler, 'personeller' => $personeller, 'personelhizmetleri' => $personelhizmetleri ,'salonpuanlar'=>$salonpuanlar, 'salonsunulanhizmetler_kategori' => $salonsunulanhizmetler_kategori, 'hizmetbolumleri' => $hizmetbolumleri,'secilenhizmetlerid' => explode('_',$hizmet),'randevular' =>$randevular]);

     }
     public function saatgetir(Request $request){
        
        $tarih = $request->randevutarihi;

          $day = 0; 
          if(date('D', strtotime($tarih))=='Mon') $day=1;
          else if(date('D', strtotime($tarih))=='Tue') $day=2;
          else if(date('D', strtotime($tarih))=='Wed') $day=3;
          else if(date('D', strtotime($tarih))=='Thu') $day=4;
          else if(date('D', strtotime($tarih))=='Fri') $day=5;
          else if(date('D', strtotime($tarih))=='Sat') $day=6;
          else if(date('D', strtotime($tarih))=='Sun') $day=7;
          $nowtime = date('H:i');
          $html = "";  
          $randevusaataraligi = Salonlar::where('id',$request->isletmeno)->value('randevu_saat_araligi');
          $mesaibaslangic =  SalonCalismaSaatleri::where('salon_id',$request->isletmeno)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('baslangic_saati');
          $mesaibitis = SalonCalismaSaatleri::where('salon_id',$request->isletmeno)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('bitis_saati');
           
          $dolusaatler = array();
          $musaitolmayansaatler = Randevular::join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')->where('randevular.tarih',$request->randevutarihi)->where(function ($q) use($request)
                        { 
                            $q->whereIn('randevu_hizmetler.personel_id',$request->secilenpersoneller);
                              $q->orWhereIn('randevu_hizmetler.hizmet_id',$request->secilenhizmetler);
                                                        
                        }
                    )->groupBy('randevular.saat')->select('randevular.*')->get();
          foreach ($musaitolmayansaatler as $musaitolmayansaat) {
               $dolusaatler[] = date('H:i' ,strtotime($musaitolmayansaat->saat));
          }
          $saatindex = 0;
          for($j = strtotime(date('H:i', strtotime($mesaibaslangic) )) ; $j < strtotime(date('H:i', strtotime($mesaibitis))); $j+=($randevusaataraligi * 60)){
                 if( (date('H:i',$j) > $nowtime && date('Y-m-d') == date('Y-m-d', strtotime($request->randevutarihi)))  || date('Y-m-d') < date('Y-m-d', strtotime($request->randevutarihi)) )
                    if(!in_array(date('H:i',$j), $dolusaatler))
                      $html .= '<div class="input-radio"><input class="saatsecimleri" id="time'.$j.'"  type="radio" name="randevusaati" value="'.date('H:i',$j).'"> <label name="randevusaati" for="time'.$j.'">'. date('H:i',$j).'</label></div>';
                    else
                        $html .= '<div class="input-radio"><input class="saatsecimleri" id="time'.$j.'"  type="radio" name="randevusaati" value="'.date('H:i',$j).'" disabled> <label for="time'.$j.'" title="Bu saatte hizmet verebilecek uygun personel bulunmamaktadır">'. date('H:i',$j).'</label></div>';
                                
             $saatindex ++;
          }
          if($saatindex == 0)
             $html.="Uygun randevu bulunamadı. Lütfen başka bir tarih seçiniz.";
        
        echo $html;

     }
     public function sifregonder(Request $request){
         $kullanici = User::where('email',$request->eposta)->first();
          
         if($kullanici){
             $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
             $olusturulansifre = substr($random, 0, 5);
             $kullanici->password = Hash::make($olusturulansifre);
             $kullanici->save();
             $data = array('sifre'=>$olusturulansifre);
            Mail::send('mail', $data, function($message) use($request){
                $message->to($request->eposta, '')->subject('randevumcepte.com.tr giriş şifreniz');
                $message->from('anil@webfirmam.net','randevumcepte.com.tr');
            });

             echo " <div id='hosgeldinizbildirim'>randevumcepte.com.tr'a tekrar hoşgeldiniz! Randevunuzu onaylamak için lütfen ".$request->eposta." adresinize gönderdiğimiz şifrenizi aşağıdaki alana giriniz. Şifreniz birkaç dakika içerisinde ulaşmazsa tekrar gönderilmesi için lütfen <button type='button' id='sifregonder4' class='btn btn-primary small'>buraya tıklayınız</button></div> <div id='sifrealani'>
                                        <div class='form-group'>
                                            <input type='password' id='sifre' name='sifre' placeholder='Mevcut şifreniz'><br />
                                            <button type='button' id='randevuonayla' class='btn btn-primary'>GÖNDER <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i> </button> 
                                        </div>
                                    </div>
                                    ";


         }
         else
         {
            echo "<div id='hosgeldinizbildirim'>
            randevumcepte.com.tr'a hoşgeldiniz! Görünüşe göre sistemimizi ilk defa kullanıyorsunuz. Sisteme kayıt olmak ve randevu almak için lütfen adınızı soyadınızı giriniz.</div>
            <div id='adsoyadalani'>
              <div class='form-group'>
            <input type='text' name='adsoyad' id='adsoyad' placeholder='Adınız Soyadınız'></div>
            <button type='button' id='sifregonder2' class='btn btn-primary small'>Gönder</button></div>
            ";
         }
           
         
     }
     public function sifregonder2(Request $request){
        $kullanici = new User();
        $kullanici->name = $request->adsoyad;
        $kullanici->email = $request->eposta;

         $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
         $olusturulansifre = substr($random, 0, 5);
          $kullanici->password = Hash::make($olusturulansifre);
          $kullanici->save();
           $data = array('sifre'=>$olusturulansifre);
            Mail::send('mail', $data, function($message) use($request){
                $message->to($request->eposta, '')->subject('randevumcepte.com.tr giriş şifreniz');
                $message->from('anil@webfirmam.net','randevumcepte.com.tr');
            });
            echo "<div id='hosgeldinizbildirim'>Randevunuzu onaylamak için lütfen ".$request->eposta." adresinize gönderdiğimiz şifrenizi aşağıdaki alana giriniz. Şifreniz birkaç dakika içerisinde ulaşmazsa tekrar gönderilmesi için lütfen <button type='button' id='sifregonder3' class='btn btn-primary small'>buraya tıklayınız</button></div><div id='sifrealani'>
                                        <div class='form-group'>
                                            <input type='password' id='sifre' name='sifre' placeholder='Gönderilen şifreyi giriniz'><br />
                                            <button type='button' id='randevuonayla'  class='btn btn-primary'>GÖNDER <i class='fa fa-chevron-right'></i><i class='fa fa-chevron-right'></i></button> 
                                        </div>
                                    </div>";




     }
     public function randevuonayla1(Request $request){
          $credential = ['email' => $request->eposta, 'password' =>$request->sifre];

          if(Auth::attempt($credential,$request->member)){
            $hizmetler_html = "";
            $personeller_html = "";

             $hizmetliste = Hizmetler::whereIn('id',$request->secilenhizmetler)->get();
             $personelliste = Personeller::whereIn('id',$request->secilenpersoneller)->get();
             foreach ($hizmetliste as $key => $value) {
                 $hizmetler_html .= "<input type='hidden' name='hizmetler[]' value='".$value->id."'>".$value->hizmet_adi."&nbsp;";

             }
             foreach ($personelliste as $key => $value) {
                 $personeller_html .= "<input type='hidden' name='personeller[]' value='".$value->id."'>
                  <div class='col-md-3' style='float:left; margin:10px;font-size:14px'><div class='author small' style='position: relative;'>
                    <div class='author-image' style='float: none'>";
                 if($value->profil_resmi == null || $value->profil_resmi == ''){
                   if($value->cinsiyet==0)
                        $personeller_html .= '<div class="background-image" style="background-image: url(/public/img/author0.jpg);"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/img/author0.jpg" alt="Profil resmi">';
                    else
                        $personeller_html .= '<div class="background-image" style="background-image: url(/public/img/author1.jpg);"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/img/author1.jpg" alt="Profil resmi">';
                                                                  
                                                               
               }
               else

                    $personeller_html .= '<div class="background-image" style="background-image: url("http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'");"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'" alt="Profil resmi">';

                $personeller_html .= "</div></div></div>".$value->personel_adi."</div>&nbsp;";
             }
            $randevusaati = '<input type="hidden" name="randevusaati" value="'.date('H:i:s', strtotime($request->randevutarihivesaati)).'">'.date('H:i', strtotime($request->randevutarihivesaati));
            $randevutarihi =  '<input type="hidden" name="randevutarihi" value="'.date('Y-m-d', strtotime($request->randevutarihivesaati)).'">'.date('d.m.Y', strtotime($request->randevutarihivesaati));
             $randevudokumu = array();
             $randevudokumu['hizmetler'] = $hizmetler_html;
             $randevudokumu['personeller'] = $personeller_html;
             $randevudokumu['randevutarihi'] = $randevutarihi;
             $randevudokumu['randevusaati'] = $randevusaati;
            return  $randevudokumu;
          } 
          else{
            echo 'Giriş bilgileriniz hatalıdır. Lütfen yeniden deneyiniz';
          }

     }
        
}
