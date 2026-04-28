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
use App\PersonelHizmetler;
use App\SalonCalismaSaatleri;
use App\PersonelCalismaSaatleri;
use App\SalonPuanlar;
use App\SalonYorumlar;
use App\PersonelPuanlar;
use App\PersonelYorumlar;
use App\SalonHizmetler;
use App\IsletmeYetkilileri;
use App\SistemYoneticileri;
use App\AramaTerimleri;
use App\AramaTerimleriKampanya;
use App\SalonKampanyalar;
use App\SatinAlinanKampanyalar;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
 
 use Illuminate\Support\Facades\DB;
 use Hash;

class AdminController extends Controller
{
   
    public function __construct()
    {

         $this->middleware('auth:sistemyonetim');
       
    }

    
    public function index()
    {
       return view('superadmin.dashboard',['title' =>  'Sistem Yönetim Paneli | randevumcepte.com.tr','pageindex' => 0]); 
    }
    public function isletmeler(){
        if(Auth::user()->admin==1)
    	   $isletmeler = Salonlar::all();
        else
            $isletmeler = Salonlar::where('musteri_yetkili_id',Auth::user()->id)->get();
    	return view('superadmin.isletmeler',['title' =>  'İşletmeler | randevumcepte.com.tr','pageindex' => 1,'isletmeler' => $isletmeler]);


    }
    public function avantajlar(){
         $avantajlar = SalonKampanyalar::all();
        $isletmeler = Salonlar::all();
        $avantajhtml = "";
        foreach ($avantajlar as $key => $value) {
            $avantajhtml .= "<tr><td>".$value->kampanya_aciklama."</td>
                                    <td>".Salonlar::where('id',$value->salon_id)->value('salon_adi')."</td>
                                   <td>".date('d.m.Y',strtotime($value->kampanya_baslangic_tarihi))."</td>";
                                   if($value->kampanya_bitis_tarihi != null || $value->kampanya_bitis_tarihi != '')
                                        $avantajhtml .="
                                   <td>".date('d.m.Y',strtotime($value->kampanya_bitis_tarihi))."</td>";
                                   else
                                        $avantajhtml .="<td>Süresiz</td>";
                                   $avantajhtml .="<td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->count()."</td>
                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',1)->count()."</td>
                                     <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',0)->count()."</td>";

                                   if($value->onayli == 1)

                                        $avantajhtml .= "<td style='color:green;font-weight:bold'>Aktif</td>";
                                   else if($value->onayli == 0)
                                     $avantajhtml .= "<td style='color:red;font-weight:bold'>Pasif</td>";

                                   $avantajhtml .= "<td><a class='btn btn-space btn-primary btn-xs' href='/sistemyonetim/avantajdetay/".$value->id."' style='width:100%'><span class='icon mdi mdi-settings'></span> Detaylar & Düzenle</a>";
                                   if($value->kampanya_bitis_tarihi != null && $value->kampanya_bitis_tarihi != '')
                                       $avantajhtml .= "<button name='sureuzat' class='btn btn-space btn-success btn-xs' data-value='{{$value->id}}' style='width:100%'><span class='icon mdi mdi-plus'></span> Süre Uzat</button>";
                                   if($value->onayli == 1)
                                       $avantajhtml .= "<button class='btn btn-space btn-danger btn-xs' data-value='".$value->id."' name='pasifdurumaal' style='width:100%'><span class='icon mdi mdi-delete'></span> Pasif Et</button>";

                                   /*$avantajhtml .= "<a class='btn btn-space btn-primary btn-xs' href='tel:0'".IsletmeYetkilileri::where('salon_id',$value->salon_id)->where('is_admin',1)->value('gsm1')."><span class='icon mdi mdi-phone'></span> Yetkiliyi Ara</a></td></tr>";*/
                                   $avantajhtml .= "</td></tr>";
                               }
        return view('superadmin.avantajlar',['title' =>  'Avantajlar | randevumcepte.com.tr','pageindex' => 9,'isletmeler' => $isletmeler,'avantajhtml'=>$avantajhtml]);


    }
    public function avantajdetayi($id){

        $avantaj = SalonKampanyalar::where('id',$id)->first();

        $salongorselleri = SalonGorselleri::where('salon_id',$avantaj->salon_id)->where('kampanya_gorsel_kapak','!=',1)->where('kampanya_gorsel',1)->get();
        $salongorselkapak = SalonGorselleri::where('salon_id',$avantaj->salon_id)->where('kampanya_gorsel_kapak',1)->first();
         $gorseller_html = "";
        foreach($salongorselleri as $key => $value){
            $gorselindex = $key+1;
            $gorseller_html .= " <div class='item'>
              <div class='photo'>
                <div class='img'><img id='gorsel".$gorselindex."' src='/".$value->salon_gorseli."' alt='Salon Görseli'>
                  <div class='over'>
                    <div class='info-wrapper'>
                      <div class='info'>
                         
                        <div class='func'><a id='gorsellink".$gorselindex."' href='/".$value->salon_gorseli."' class='image-zoom'><i class='icon mdi mdi-search'></i></a> <a style='cursor:pointer' name='gorsel' title='Kaldır' data-value='".$value->id."'><i class='icon mdi mdi-delete'></i></a></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>";
        }
        if($salongorselleri->count()<12){
            for($i=$salongorselleri->count()+1;$i<=12;$i++){
                $gorseller_html .="<div class='item'>
                                    <div class='photo'>
                                        <div class='img'><img id='gorsel".$i."' src='/public/img/image-01.jpg' alt='Salon Görseli'>
                                            <div class='over'>
                                                <div class='info-wrapper'>
                                                    <div class='info'>
                         
                                                        <div class='func'><a id='gorsellink".$i."' href='public/img/image-01.jpg' class='image-zoom'><i class='icon mdi mdi-search'></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
            }
        }

        $etiketler = AramaTerimleriKampanya::where('salon_id',$avantaj->salon_id)->where('kampanya_id',$id)->orderBy('id','asc')->get();
          return view('superadmin.avantajdetaylari',['title' => 'Avantaj Detayları | randevumcepte.com.tr','pageindex' => 9, 'avantaj'=>$avantaj,'etiketler'=>$etiketler,'salongorselleri'=>$salongorselleri,'salongorselkapak'=>$salongorselkapak,'gorseller_html'=>$gorseller_html]);
 
    }
    public function avantajpasifdurumaal(Request $request){
         $avantaj = SalonKampanyalar::where('id',$request->kampanyaid)->first();
         $avantaj->onayli = 0;
         $avantaj->save();
           $avantajlar = SalonKampanyalar::all();
      
        $avantajhtml = "";
        foreach ($avantajlar as $key => $value) {
            $avantajhtml .= "<tr><td>".$value->kampanya_aciklama."</td>
                                    <td>".Salonlar::where('id',$value->salon_id)->value('salon_adi')."</td>
                                   <td>".date('d.m.Y',strtotime($value->kampanya_baslangic_tarihi))."</td>";
                                   if($value->kampanya_bitis_tarihi != null || $value->kampanya_bitis_tarihi != '')
                                        $avantajhtml .="
                                   <td>".date('d.m.Y',strtotime($value->kampanya_bitis_tarihi))."</td>";
                                   else
                                        $avantajhtml .="<td>Süresiz</td>";
                                   $avantajhtml .="<td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->count()."</td>
                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',1)->count()."</td>
                                     <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',0)->count()."</td>";

                                   if($value->onayli == 1)

                                        $avantajhtml .= "<td style='color:green'>Aktif</td>";
                                   else if($value->onayli == 0)
                                     $avantajhtml .= "<td style='color:red'>Pasif</td>";
                                   $avantajhtml .= "<td><a class='btn btn-space btn-success btn-xs' href='/sistemyonetim/avantajdetay/".$value->id."'><span class='icon mdi mdi-settings'></span> Detaylar & Düzenle</a>";
                                   if($value->onayli == 1)
                                       $avantajhtml .= "<a class='btn btn-space btn-danger btn-xs' data-value='".$value->id."' name='pasifdurumaal'><span class='icon mdi mdi-delete'></span> Pasif Et</a>";

                                   /*$avantajhtml .= "<a class='btn btn-space btn-primary btn-xs' href='tel:0'".IsletmeYetkilileri::where('salon_id',$value->salon_id)->where('is_admin',1)->value('gsm1')."><span class='icon mdi mdi-phone'></span> Yetkiliyi Ara</a></td></tr>";*/
                                   $avantajhtml .= "</td></tr>";
                               }
         echo $avantajhtml;
    }
    public function isletmedetay($id){
    	$isletme = Salonlar::where('id',$id)->first();
    	$salonhizmetler = SalonHizmetler::where('salon_id',$id)->get();
    	$personeller = Personeller::where('salon_id',$id)->get();
    	$saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id',$id)->orderBy('haftanin_gunu','asc')->get();
    	$salongorselleri = SalonGorselleri::where('salon_id',$id)->where('kapak_fotografi','!=',1)->get();
        $salongorselkapak = SalonGorselleri::where('salon_id',$id)->where('kapak_fotografi',1)->first();
        $etiketler = AramaTerimleri::where('salon_id',$id)->orderBy('id','asc')->get();
        $isletmeturu = SalonTuru::all();  
        $hizmetler = Hizmetler::all();
        $isletmeturu_html = "";

        

        $hizmetlistesi_html = "";
         foreach ($hizmetler as $key => $value) {
             $hizmetlistesi_html.="<option value='".$value->id."'>".$value->hizmet_adi."</option>";
        }
        

        foreach ($isletmeturu as $key => $value) {
            if($value->id ==$isletme->salon_turu_id)
                $isletmeturu_html .= "<option selected value='".$value->id."'>".$value->salon_turu_adi."</option>";
            else
                $isletmeturu_html .= "<option value='".$value->id."'>".$value->salon_turu_adi."</option>";
        }
        $gorseller_html = "";
        foreach($salongorselleri as $key => $value){
            $gorselindex = $key+1;
            $gorseller_html .= " <div class='item'>
              <div class='photo'>
                <div class='img'><img id='gorsel".$gorselindex."' src='/".$value->salon_gorseli."' alt='Salon Görseli'>
                  <div class='over'>
                    <div class='info-wrapper'>
                      <div class='info'>
                         
                        <div class='func'><a id='gorsellink".$gorselindex."' href='/".$value->salon_gorseli."' class='image-zoom'><i class='icon mdi mdi-search'></i></a> <a style='cursor:pointer' name='gorsel' title='Kaldır' data-value='".$value->id."'><i class='icon mdi mdi-delete'></i></a></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>";
        }
        if($salongorselleri->count()<12){
            for($i=$salongorselleri->count()+1;$i<=12;$i++){
                $gorseller_html .="<div class='item'>
                                    <div class='photo'>
                                        <div class='img'><img id='gorsel".$i."' src='/public/img/image-01.jpg' alt='Salon Görseli'>
                                            <div class='over'>
                                                <div class='info-wrapper'>
                                                    <div class='info'>
                         
                                                        <div class='func'><a id='gorsellink".$i."' href='public/img/image-01.jpg' class='image-zoom'><i class='icon mdi mdi-search'></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
            }
        }

           
             

      
       


    	return view('superadmin.isletmedetay',['salongorselleri'=> $salongorselleri,'saloncalismasaatleri'=>$saloncalismasaatleri,'personeller' => $personeller, 'salonhizmetler' => $salonhizmetler,'isletme'=> $isletme,'title' => $isletme->salon_adi.' | Detaylar & Düzenle | randevumcepte.com.tr', 'pageindex' => 2,'etiketler' => $etiketler,'isletmeturulistesi' => $isletmeturu_html,'gorseller_html' => $gorseller_html,'hizmetlistesi'=>$hizmetlistesi_html,'salongorselkapak'=>$salongorselkapak]);
    }
     public function personeldetay($id){
        $personel = Personeller::where('id',$id)->orderBy('id','desc')->first();
         $personelhizmetler = PersonelHizmetler::where('personel_id',$id)->orderBy('id','desc')->get();
         $personelcalismasaatleri = PersonelCalismaSaatleri::where('personel_id',$id)->orderBy('haftanin_gunu','asc')->get();
         $personelhizmetleri_html = "";
          foreach($personelhizmetler as $personelhizmet){
                $personelhizmetleri_html .="<tr><td>".$personelhizmet->hizmetler->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel_superadmin' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
          }
         $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        return view('superadmin.personeldetay',['personelsunulanhizmetler'=>$personelhizmetleri_html,'personelcalismasaatleri'=>$personelcalismasaatleri,'personel' => $personel , 'title' => $personel->personel_adi.' | Detaylar & Düzenle | randevumcepte.com.tr', 'pageindex' => 105,'isletme' => $isletme]);
    }
    public function hizmetlistesi(){
    	$hizmetler = Hizmetler::orderBy('id','desc')->get();
    	$hizmetkategorileri = Hizmet_Kategorisi::orderBy('id','desc')->get();
    	return view('superadmin.hizmetler',['title' =>  ' Hizmetler & Hizmet Kategorileri | randevumcepte.com.tr', 'pageindex' => 4,'hizmetler' => $hizmetler ,'hizmetkategorileri' => $hizmetkategorileri]);
    }
    public function isletmeyetkilileri(){
    	$yetkililer = IsletmeYetkilileri::orderBy('id','desc')->get();
    	return view('superadmin.yetkililer',['yetkililer'=> $yetkililer,'title'=> 'İşletme Yetkilileri | randevumcepte.com.tr','pageindex'=>6]);
    }
    public function yetkilidetay($id){
    	$yetkili = IsletmeYetkilileri::where('id',$id)->first();
    	$salonlar = Salonlar::orderBy('salon_adi','asc')->get();
    	return view('superadmin.yetkilidetay',['salonlar'=>$salonlar,'yetkili'=> $yetkili, 'title' => $yetkili->name. ' Detayları & Düzenle | randevumcepte.com.tr','pageindex' => 7]);
    }
    public function yenihizmetekleme(Request $request){
    	$hizmetler = new Hizmetler();
    	$hizmetler->hizmet_adi = $request->hizmetadi;
    	$hizmetler->hizmet_kategori_id = $request->hizmetkategorisi;
    	$hizmetler->fiyat = 0;
    	$hizmetler->save();
    	echo $request->hizmetadi . ' adlı hizmet sisteme başarı ile eklendi';
    }
    public function yenihizmetkategoriekleme(Request $request){
    	$hizmetkategorisi = new Hizmet_Kategorisi();
    	$hizmetkategorisi->hizmet_kategorisi_adi = $request->hizmetkategorisiadi;
    	$hizmetkategorisi->save();
    	echo $request->hizmetkategorisiadi .' adlı hizmet kategorisi sisteme başarı ile eklendi';
    }
    public function hizmetkategorisisil(Request $request){
    	$hizmetkategorisi = Hizmet_Kategorisi::where('id',$request->kategoriid)->first();
    	$kategoriadi = $hizmetkategorisi->hizmet_kategorisi_adi;
    	$hizmetkategorisi->delete();
    	echo $kategoriadi . ' sistemden başarı ile silindi';
    }
    public function hizmetsil(Request $request){
    	$hizmet = Hizmetler::where('id',$request->hizmetid)->first();
    	$hizmetadi = $hizmet->hizmet_adi;
    	$hizmet->delete();
    	echo $hizmetadi .' adlı hizmet sistemden başarı ile silindi';
    }
    public function yenisalonhizmetiekle(Request $request){
    	try{
    		$hizmet = new SalonHizmetler();
    	$hizmet->salon_id = $request->isletmeid;
    	$hizmet->hizmet_id = $request->yenisalonhizmeti;
    	$hizmet->hizmet_kategori_id =  Hizmetler::where('id',$request->yenisalonhizmeti)->value('hizmet_kategori_id');
    	$hizmet->baslangic_fiyat =$request->baslangicfiyat;
    	$hizmet->son_fiyat = $request->sonfiyat;
    	$hizmet->bolum = $request->bolum;
    	$hizmet->save();
    	echo 'Salon hizmeti sisteme başarı ile eklendi';
    	}
    	catch (Exception $e){
    		echo $e->getMessage();
    	}
    	
    }
    public function isletmeaciklamaekle(Request $request){
    	try{
    		$isletme = Salonlar::where('id',$request->isletmeid)->first();
    		$isletme->aciklama = $request->isletmeaciklama;
    		$isletme->save();
    		echo $isletme->salon_adi .' için açıklama başarı ile sisteme eklendi';

    	}
    	catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
    public function aciklamaguncelle(Request $request){
    	try{
    		$isletme = Salonlar::where('id',$request->isletmeid)->first();
    		$isletme->aciklama = $request->isletmeaciklama;
    		$isletme->save();


    	}
    	catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
    public function calismasaatiguncelle(Request $request){
    	try{

    	   $index =0; 
    	   foreach($request->calismasaatiid as $calismasaatiid){
    	   		$calismasaati = SalonCalismaSaatleri::where('id',$calismasaatiid)->first();
    	   		if(array_key_exists($index, $request->calisiyor)){
    	   			$calismasaati->baslangic_saati = $request->calismasaatibaslangic[$index];
    	   			$calismasaati->bitis_saati = $request->calismasaatibitis[$index];
    	   			$calismasaati->calisiyor = $request->calisiyor[$index];
    	   		}
    	   		else
    	   			$calismasaati->calisiyor = 0;

    	   		
    	   		$calismasaati->save();
    	   		$index++;
    	   }
    	   echo 'Çalışma saatleri başarı ile güncellendi';

    	}
    	catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
    public function yeniavantaj(){
        $isletmeler = Salonlar::where('uyelik_turu',2)->orWhere('uyelik_turu',3)->get();
        return view('superadmin.yeniavantajekle',['title'=>'Yeni Avantaj Ekle | randevumcepte.com.tr','pageindex'=>10,'isletmeler'=>$isletmeler]);
    }
    public function yeniisletme(){
    	$yetkililer = IsletmeYetkilileri::all();
    	$hizmetler = Hizmetler::all();
    	$personeller = Personeller::all();
        $isletmeturu = SalonTuru::all();
        $salonyetkilileri = IsletmeYetkilileri::where('salon_id',null)->orWhere('salon_id','')->get();

        $hizmetlistesi_html = "";
        
        $personeller_html = "";
        $isletmeturu_html = "";
        $salonyetkilileri_html = "";
        foreach ($salonyetkilileri as $key => $value) {
             $salonyetkilileri_html .= "<option value='".$value->id."'>".$value->name."</option>";
        }
        foreach ($isletmeturu as $key => $value) {
            $isletmeturu_html .= "<option value='".$value->id."'>".$value->salon_turu_adi."</option>";
        }
        foreach ($personeller as $key => $value) {
           
            if($value->id!=15 && ($value->salon_id == null || $value->salon_id == ''))
                 $personeller_html .= "<option value='".$value->id."'>".$value->personel_adi."</option>";
                                  
        }
        foreach ($hizmetler as $key => $value) {
             $hizmetlistesi_html.="<option value='".$value->id."'>".$value->hizmet_adi."</option>";
        }
    	 return view('superadmin.yeniisletmeekle',['personeller'=>$personeller,'hizmetler'=>$hizmetler,'yetkililer'=>$yetkililer,'pageindex'=>3 ,'title' => 'Yeni İşletme Ekle | randevumcepte.com.tr', 'hizmetlistesi' => $hizmetlistesi_html ,'isletmeturulistesi' => $isletmeturu_html,'personelliste' => $personeller_html, 'salonyetkilileri' => $salonyetkilileri_html]);
    }
    public function yeniavantajyayinla(Request $request){
        $kampanya = new SalonKampanyalar();
        $kampanya->salon_id= $request->isletme;

        $kampanya->kampanya_baslik = $request->kampanya_baslik;
        $kampanya->kampanya_aciklama = $request->kampanya_aciklama;
        $kampanya->kampanya_baslangic_tarihi = date('Y-m-d H:i:s');
        $kampanya->kampanya_bitis_tarihi = $request->avantajbitistarih.' 23:59:59';
        $kampanya->kampanya_fiyat = $request->kampanya_fiyat;
        $kampanya->hizmet_normal_fiyat = $request->hizmet_normal_fiyat;
        $kampanya->kampanya_detay = $request->kampanya_detay;
        $kampanya->onayli = 1;
        $kampanya->save();
        if(isset($_FILES["isletmekapakfoto"]["name"])){
          
              $dosya  = $request->isletmekapakfoto;
               $kaynak = $_FILES["isletmekapakfoto"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["isletmekapakfoto"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["isletmekapakfoto"]["name"]);
                        $uzanti = explode(".", $_FILES["isletmekapakfoto"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
              $salongorselkapak = new SalonGorselleri();
              $salongorselkapak->salon_id= $request->isletme;
              $salongorselkapak->kapak_fotografi = 0;
              $salongorselkapak->kampanya_gorsel = 1;
              $salongorselkapak->kampanya_gorsel_kapak =1;

              $salongorselkapak->salon_gorseli = $hedef;
              $salongorselkapak->save();
               
        }
        if(isset($_FILES["isletmegorselleri"]["name"])){
          
             for($i=0;$i<count($_FILES["isletmegorselleri"]["name"]);$i++){
                 $salongorselleri = new SalonGorselleri();
                 $dosya = $request->isletmegorselleri[$i];
                 $kaynak = $_FILES["isletmegorselleri"]["tmp_name"][$i];
                 $dosya  = str_replace(" ", "_", $_FILES["isletmegorselleri"]["name"][$i]);
                  $dosya  = str_replace(" ", "-", $_FILES["isletmegorselleri"]["name"][$i]);
                 $uzanti = explode(".", $_FILES["isletmegorselleri"]["name"][$i]);
                  $hedef  = "./" . $dosya;
                  if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                }  
                $salongorselleri->salon_gorseli = $hedef;
                $salongorselleri->salon_id = $request->isletme;
                $salongorselleri->kapak_fotografi = 0;
                $salongorselleri->kampanya_gorsel = 1;
                $salongorselleri->kampanya_gorsel_kapak = 0;
                $salongorselleri->save();
             }
               
        }
         if(Auth::user()->admin==1){
             if($request->etiket1 != null || $request->etiket1!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletme;
             $aramaterimleri->arama_terimi = $request->etiket1;
             $aramaterimleri->kampanya_id = $kampanya->id;
             $aramaterimleri->save();
        }
          if($request->etiket2 != null || $request->etiket2!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id =  $request->isletme;
             $aramaterimleri->arama_terimi = $request->etiket2;
              $aramaterimleri->kampanya_id = $kampanya->id;
             $aramaterimleri->save();
        }
          if($request->etiket3 != null || $request->etiket3!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id =  $request->isletme;
             $aramaterimleri->arama_terimi = $request->etiket3;
              $aramaterimleri->kampanya_id = $kampanya->id;
             $aramaterimleri->save();
        }
          if($request->etiket4 != null || $request->etiket4!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id =  $request->isletme;
             $aramaterimleri->arama_terimi = $request->etiket4;
              $aramaterimleri->kampanya_id = $kampanya->id;
             $aramaterimleri->save();
        }
          if($request->etiket5 != null || $request->etiket5!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletme;
             $aramaterimleri->arama_terimi = $request->etiket5;
              $aramaterimleri->kampanya_id = $kampanya->id;
             $aramaterimleri->save();
        }
          if($request->etiket6 != null || $request->etiket6!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletme;
             $aramaterimleri->arama_terimi = $request->etiket6;
              $aramaterimleri->kampanya_id = $kampanya->id;
             $aramaterimleri->save();
        }
        }
       
        echo 'Avantaj başarı ile yayınlandı';

    }

    public function yeniisletmeekle(Request $request){
        $isletme = new Salonlar();
        $isletme->salon_adi = $request->isletmeadi;
        $isletme->adres = $request->adres;
        $isletme->il_id = $request->il;
        $isletme->ilce_id = $request->ilce;
        $isletme->salon_turu_id = $request->isletmeturu;
        $isletme->aciklama = $request->aciklama;
        $isletme->facebook_sayfa = $request->facebookadres;
        $isletme->instagram_sayfa = $request->instagramaccesstoken;
        $isletme->maps_iframe = $request->googlemapskaydi;
        $isletme->randevu_saat_araligi = 30;
        $isletme->uyelik_turu = $request->uyelikturu;
         

        $isletme->musteri_yetkili_id = Auth::user()->id;
        if(isset($_FILES["isletmelogo"]["name"])){
          
              $dosya  = $request->isletmelogo;
               $kaynak = $_FILES["isletmelogo"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["isletmelogo"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["isletmelogo"]["name"]);
                        $uzanti = explode(".", $_FILES["isletmelogo"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
              $isletme->logo = $hedef;
               
        }
        $isletme->save();
        if(Auth::user()->admin==1){
             if($request->etiket1 != null || $request->etiket1!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket1;
             $aramaterimleri->save();
        }
          if($request->etiket2 != null || $request->etiket2!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket2;
             $aramaterimleri->save();
        }
          if($request->etiket3 != null || $request->etiket3!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket3;
             $aramaterimleri->save();
        }
          if($request->etiket4 != null || $request->etiket4!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket4;
             $aramaterimleri->save();
        }
          if($request->etiket5 != null || $request->etiket5!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket5;
             $aramaterimleri->save();
        }
          if($request->etiket6 != null || $request->etiket6!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket6;
             $aramaterimleri->save();
        }
        }
       
       
  
        if(isset($_FILES["isletmekapakfoto"]["name"])){
          
              $dosya  = $request->isletmekapakfoto;
               $kaynak = $_FILES["isletmekapakfoto"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["isletmekapakfoto"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["isletmekapakfoto"]["name"]);
                        $uzanti = explode(".", $_FILES["isletmekapakfoto"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
              $salongorselkapak = new SalonGorselleri();
              $salongorselkapak->salon_id= $isletme->id;
              $salongorselkapak->kapak_fotografi = 1;
              $salongorselkapak->salon_gorseli = $hedef;
              $salongorselkapak->save();
               
        }
        $yetkili = IsletmeYetkilileri::where('id',$request->isletmeyetkilileri)->first();
        $yetkili->salon_id = $isletme->id;
        $yetkili->is_admin=1;
        $yetkili->save();
       if($request->has('personeller')){
        foreach ($request->personeller as $key => $value) {
            $personel = Personeller::where('id',$value)->first();
            $personel->salon_id = $isletme->id;
            $personel->save();

        }
        }
        if($request->has('hizmetler_bayan')){
            foreach ($request->hizmetler_bayan as $key => $value) {
             $hizmetler = new SalonHizmetler();
             $hizmetler->salon_id = $isletme->id;
             $hizmetler->hizmet_id = $value;
             $hizmetler->hizmet_kategori_id = Hizmetler::where('id',$value)->value('hizmet_kategori_id');
             $hizmetler->baslangic_fiyat = $request->salonsunulanhizmetbayanbaslangicfiyat[$key];
             $hizmetler->son_fiyat = $request->salonsunulanhizmetbayansonfiyat[$key];

             $hizmetler->bolum = 0;
             $hizmetler->save();
            }
        }
        if($request->has('hizmetler_bay')){
             foreach ($request->hizmetler_bay as $key => $value) {
             $hizmetler = new SalonHizmetler();
             $hizmetler->salon_id = $isletme->id;
             $hizmetler->hizmet_id = $value;
             $hizmetler->hizmet_kategori_id = Hizmetler::where('id',$value)->value('hizmet_kategori_id');
             $hizmetler->baslangic_fiyat = $request->salonsunulanhizmetbaybaslangicfiyat[$key];
             $hizmetler->son_fiyat = $request->salonsunulanhizmetbaysonfiyat[$key];
             $hizmetler->bolum = 1;
             $hizmetler->save();
            }
        }
         

        for($i=1;$i<=7;$i++){
            
            $saloncalismasaatleri = new SalonCalismaSaatleri();
            $saloncalismasaatleri->haftanin_gunu = $i;
            $saloncalismasaatleri->salon_id = $isletme->id;
            if(isset($_POST['calisiyor'.$i])){

                $saloncalismasaatleri->calisiyor = 1;
               
               
            }
            else{
                $saloncalismasaatleri->calisiyor = 0;
            }
                
             if($i==1){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati1;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati1;
                }
                 if($i==2){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati2;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati2;
                }
                 if($i==3){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati3;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati3;
                }
                 if($i==4){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati4;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati4;
                }
                 if($i==5){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati5;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati5;
                }
                 if($i==6){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati6;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati6;
                }
                 if($i==7){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati7;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati7;
                }
                $saloncalismasaatleri->save();
            
        }

        if(isset($_FILES["isletmegorselleri"]["name"])){
          
             for($i=0;$i<count($_FILES["isletmegorselleri"]["name"]);$i++){
                 $salongorselleri = new SalonGorselleri();
                 $dosya = $request->isletmegorselleri[$i];
                 $kaynak = $_FILES["isletmegorselleri"]["tmp_name"][$i];
                 $dosya  = str_replace(" ", "_", $_FILES["isletmegorselleri"]["name"][$i]);
                  $dosya  = str_replace(" ", "-", $_FILES["isletmegorselleri"]["name"][$i]);
                 $uzanti = explode(".", $_FILES["isletmegorselleri"]["name"][$i]);
                  $hedef  = "./" . $dosya;
                  if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                }  
                $salongorselleri->salon_gorseli = $hedef;
                $salongorselleri->salon_id = $isletme->id;
                $salongorselleri->kapak_fotografi = 0;
                $salongorselleri->save();
             }
               
        }
        echo 'Salon bilgileri başarı ile eklendi';
    }

    public function yetkilidetayguncelle(Request $request){
    	$yetkili = IsletmeYetkilileri::where('id',$request->yetkiliid)->first();
    	$yetkili->name = $request->yetkiliadi;
    	$yetkili->email = $request->eposta;
    	if($request->sifre != null ||$request->sifre != ""){
            $yetkili->password = Hash::make($request->sifre);
    	}
    	 
    	$yetkili->telefon = $request->telefon;
    	$yetkili->gsm1 = $request->gsm1;
    	$yetkili->gsm2 = $request->gsm2;  
    	if($request->hasFile('yetkiliprofil')){
			$profilresim = $request->file('yetkiliprofil');
			$dosyaadi = $profilresim->getClientOriginalName().'.'.$profilresim->getClientOriginalExtension();
    		$file->move(public_path().'/salon_gorselleri/'.$dosyaadi);
    		$yetkili = IsletmeYetkilileri::where('id',$request->yetkiliid)->first();
    		$yetkili->profil_resim = public_path().'/salon_gorselleri/'.$dosyaadi;

    	}
    	$yetkili->save();
    	echo 'Yetkili bilgileri başarı ile güncellendi'; 
    }
    public function yetkiliresimguncelle(Request $request){
    	 
    	echo 'Profil resmi başarı ile yüklendi';
    	 
    }
    public function musteritemsilcileri(){
        $sistemyoneticileri = SistemYoneticileri::orderBy('id','desc')->get();
        return view('superadmin.musteritemsilcileri',['sistemyoneticileri' => $sistemyoneticileri,'pageindex' => 8,'title'=> 'Müşteri Temsilcileri | randevumcepte.com.tr']);
    }
    public function yenimusteritemsilcisiekle(){
        return view('superadmin.yenimusteritemsilcisiekle',['pageindex'=>9,'title'=>'Yeni Müşteri Temsilcisi Ekle | randevumcepte.com.tr']);
    }
    public function yenimusteritemsilcisi(Request $request){
        $yenitemsilci = new SistemYoneticileri();
        $yenitemsilci->name = $request->name;
        $yenitemsilci->email = $request->email;
        $yenitemsilci->password = Hash::make($request->password);
        $yenitemsilci->telefon = $request->phone;
        $yenitemsilci->admin = $request->isadmin;
        $yenitemsilci->save();


    }
    public function sistemeyenihizmetekle(Request $request){
        $hizmetkategoriid = 0;
        $hizmetkategori = Hizmet_Kategorisi::where('id',$request->hizmetkategori)->first();

        if(!$hizmetkategori){
             $yenihizmetkategori = new Hizmet_Kategorisi();
             $yenihizmetkategori->hizmet_kategorisi_adi = $request->hizmetkategori;
             $yenihizmetkategori->save();
             $hizmetkategoriid = $yenihizmetkategori->id;
        }
        else
            $hizmetkategoriid = $request->hizmetkategori;
        $yenihizmet = new Hizmetler();
        $yenihizmet->hizmet_kategori_id = $hizmetkategoriid;
        $yenihizmet->hizmet_adi = $request->hizmetadi;
        $yenihizmet->fiyat = 0;
        $yenihizmet->save();
        $hizmetler = Hizmetler::all();
        $hizmetlistesi_html = "";

        foreach ($hizmetler as $key => $value) {
             $hizmetlistesi_html.="<option value='".$value->id."'>".$value->hizmet_adi."</option>";
        }
        echo $hizmetlistesi_html;


    }
    public function ilcelistele(Request $request){
        $ilceler = Ilceler::where('il_id',$request->il)->get();
        $ilceler_html = "";
        foreach ($ilceler as $key => $value) {
            $ilceler_html .= "<option value='".$value->id."'>".$value->ilce_adi."</option>";
        }

        echo $ilceler_html;
    }
    public function yeniyetkilibilgisiekle(Request $request){
        $yetkili = new IsletmeYetkilileri();
        $yetkili->name = $request->adsoyad;
        $yetkili->email = $request->eposta;
        $yetkili->gsm1 = $request->ceptelefon;
        $yetkili->password = Hash::make($request->sifre);
        $yetkili->save();
        $yetkililer = IsletmeYetkilileri::where('salon_id',null)->orWhere('salon_id','')->get();
        $yetkililer_html = "";
        foreach ($yetkililer as $key => $value) {
            $yetkililer_html .= "<option value='".$value->id."'>".$value->name."</option>";
        }
        echo $yetkililer_html;
    }
    public function yeniisletmeturuekle(Request $request){
        $isletmeturu = new SalonTuru();
        $isletmeturu->salon_turu_adi = $request->isletmeturu;
        $isletmeturu->save();
        $isletmeturleri = SalonTuru::all();
        $isletmeturleri_html = "";
        foreach ($isletmeturleri as $key => $value) {
            $isletmeturleri_html .= "<option value='".$value->id."'>".$value->salon_turu_adi."</option>";
        }
        echo $isletmeturleri_html;
    }
    public function yenipersonelgir(Request $request){
        $personel = new Personeller();
        $personel->personel_adi = $request->personeladi_yeni;
        $personel->cinsiyet = $request->personelcinsiyet_yeni;
        $personel->unvan = $request->personelunvan_yeni;
        $personel->save();
        if($request->has('personelsunulanhizmetlerbayan_yeni')){
            foreach ($request->personelsunulanhizmetlerbayan_yeni as $key => $value) {
            $personelhizmetler = new PersonelHizmetler();
            $personelhizmetler->personel_id = $personel->id;
            $personelhizmetler->hizmet_id = $value;
            $personelhizmetler->bolum = 0;
            $personelhizmetler->save();
        }
        }
        if($request->has('personelsunulanhizmetlerbay_yeni')){
             foreach ($request->personelsunulanhizmetlerbay_yeni as $key => $value) {
            $personelhizmetler = new PersonelHizmetler();
            $personelhizmetler->personel_id = $personel->id;
            $personelhizmetler->hizmet_id = $value;
            $personelhizmetler->bolum = 1;
            $personelhizmetler->save();
        }
        }
        
       

        $personeller = Personeller::all();
        $personeller_html = "";
        foreach ($personeller as $key => $value) {
              if($value->id!=15 && ($value->salon_id == null || $value->salon_id == ''))
                 $personeller_html .= "<option value='".$value->id."'>".$value->personel_adi."</option>";
        }
        echo $personeller_html;
    }
    public function gorselsil(Request $request){
        $gorsel = SalonGorselleri::where('id',$request->gorselid)->first();
        $isletmeid = $gorsel->salon_id;
        unlink($gorsel->salon_gorseli);
        $gorsel->delete();
        $gorseller_html = "";
        $salongorselleri = SalonGorselleri::where('salon_id',$isletmeid)->where('kapak_fotografi','!=',1)->get();
         foreach($salongorselleri as $key => $value){
            $gorselindex = $key+1;
            $gorseller_html .= " <div class='item' style='float:left'>
              <div class='photo'>
                <div class='img'><img id='gorsel".$gorselindex."' src='/".$value->salon_gorseli."' alt='Salon Görseli'>
                  <div class='over'>
                    <div class='info-wrapper'>
                      <div class='info'>
                         
                        <div class='func'><a id='gorsellink".$gorselindex."' href='/".$value->salon_gorseli."' class='image-zoom'><i class='icon mdi mdi-search'></i></a> <a style='cursor:pointer' name='gorsel' title='Kaldır' data-value='".$value->id."'><i class='icon mdi mdi-delete'></i></a></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>";
        }
        if($salongorselleri->count()<12){
            for($i=$salongorselleri->count()+1;$i<=12;$i++){
                $gorseller_html .="<div class='item'  style='float:left'>
                                    <div class='photo'>
                                        <div class='img'><img id='gorsel".$i."' src='/public/img/image-01.jpg' alt='Salon Görseli'>
                                            <div class='over'>
                                                <div class='info-wrapper'>
                                                    <div class='info'>
                         
                                                        <div class='func'><a id='gorsellink".$i."' href='public/img/image-01.jpg' class='image-zoom'><i class='icon mdi mdi-search'></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
            }
        }
        echo $gorseller_html;



    }
      public function cikisyap(){
        $u = auth('sistemyonetim')->user();
        if ($u) {
            \App\SistemYonetim\Audit::log('logout', 'sistem_yoneticisi', $u->id, $u->name);
        }

        // Aktif impersonation varsa kapat
        $impId = session('sysadmin_impersonation_id');
        if ($impId) {
            $imp = \App\SistemYonetim\ImpersonationLog::find($impId);
            if ($imp && !$imp->bitis_tarihi) {
                $imp->bitis_tarihi = date('Y-m-d H:i:s');
                $imp->save();
            }
            session()->forget(['sysadmin_impersonation_id', 'sysadmin_impersonation_uid']);
        }
        auth('sistemyonetim')->logout();
        return redirect('/sistemyonetim' );
    }
    public function kayitlisalongorselisayisi(Request $request){
        $gorselsayisi = SalonGorselleri::where('salon_id',$request->isletmeid)->where('kapak_fotografi','!=',1)->count();
        echo $gorselsayisi;
    }
    public function mevcutisletmeduzenleme(Request $request){
        $isletme = Salonlar::where('id',$request->isletmeid)->first();
        $isletme->salon_adi = $request->isletmeadi;
        $isletme->adres = $request->adres;
        $isletme->il_id = $request->il;
        $isletme->ilce_id = $request->ilce;
        $isletme->salon_turu_id = $request->isletmeturu;
        $isletme->aciklama = $request->aciklama;
        $isletme->facebook_sayfa = $request->facebookadres;
        $isletme->instagram_sayfa = $request->instagramaccesstoken;
        $isletme->maps_iframe = $request->googlemapskaydi; 
        if(isset($_FILES["isletmelogo"]["name"])){
          
              $dosya  = $request->isletmelogo;
               $kaynak = $_FILES["isletmelogo"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["isletmelogo"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["isletmelogo"]["name"]);
                        $uzanti = explode(".", $_FILES["isletmelogo"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
              $isletme->logo = $hedef;
               
        }
        $isletme->save();



        if(Auth::user()->admin==1){

        $mevcutaramaterimleri = AramaTerimleri::where('salon_id',$isletme->id)->get();
        if($mevcutaramaterimleri->count()!=0){
            foreach ($mevcutaramaterimleri as $key => $value) {
                if($key==0 && ($request->etiket1 != null || $request->etiket1!='') ){
                    $aramaterimleri = AramaTerimleri::where('id',$value->id)->first();
                    $aramaterimleri->salon_id = $isletme->id;
                    $aramaterimleri->arama_terimi = $request->etiket1;
                    $aramaterimleri->save();
                }
                   if($key==1 &&($request->etiket2 != null || $request->etiket2!='')){
             $aramaterimleri = AramaTerimleri::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket2;
             $aramaterimleri->save();
        }
          if($key==2 &&($request->etiket3 != null || $request->etiket3!='')){
             $aramaterimleri = AramaTerimleri::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket3;
             $aramaterimleri->save();
        }
          if($key==3 &&($request->etiket4 != null || $request->etiket4!='')){
             $aramaterimleri = AramaTerimleri::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket4;
             $aramaterimleri->save();
        }
          if($key==4 &&($request->etiket5 != null || $request->etiket5!='')){
             $aramaterimleri = AramaTerimleri::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket5;
             $aramaterimleri->save();
        }
          if($key==5 &&($request->etiket6 != null || $request->etiket6!='')){
             $aramaterimleri = AramaTerimleri::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket6;
             $aramaterimleri->save();
        }
            }
        }
            else{

           if($request->etiket1 != null || $request->etiket1!=''){

             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket1;
             $aramaterimleri->save();
        }
          if($request->etiket2 != null || $request->etiket2!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket2;
             $aramaterimleri->save();
        }
          if($request->etiket3 != null || $request->etiket3!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket3;
             $aramaterimleri->save();
        }
          if($request->etiket4 != null || $request->etiket4!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket4;
             $aramaterimleri->save();
        }
          if($request->etiket5 != null || $request->etiket5!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket5;
             $aramaterimleri->save();
        }
          if($request->etiket6 != null || $request->etiket6!=''){
             $aramaterimleri = new AramaTerimleri();

             $aramaterimleri->salon_id = $isletme->id;
             $aramaterimleri->arama_terimi = $request->etiket6;
             $aramaterimleri->save();
        }
        }
            
       
        }

       
       
  
        if(isset($_FILES["isletmekapakfoto"]["name"])){
          
              $dosya  = $request->isletmekapakfoto;
               $kaynak = $_FILES["isletmekapakfoto"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["isletmekapakfoto"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["isletmekapakfoto"]["name"]);
                        $uzanti = explode(".", $_FILES["isletmekapakfoto"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
              $salongorselkapakeski = SalonGorselleri::where('kapak_fotografi',1)->where('salon_id',$isletme->id)->first();
              if($salongorselkapakeski){
                  $salongorselkapakeski->delete();
              }
              
              $salongorselkapak = new SalonGorselleri();
              $salongorselkapak->salon_id= $isletme->id;
              $salongorselkapak->kapak_fotografi = 1;
              $salongorselkapak->salon_gorseli = $hedef;
              $salongorselkapak->save();
               
        }
        /* $yetkili = IsletmeYetkilileri::where('id',$request->isletmeyetkilileri)->first();
        $yetkili->salon_id = $isletme->id;
        $yetkili->save(); */
       if($request->has('personeller')){

        foreach ($request->personeller as $key => $value) {
            $personel = Personeller::where('id',$value)->first();
            $personel->salon_id = $isletme->id;
            $personel->save();

        }
        }
        


        if($request->has('hizmetler_bayan')){
              
           
            foreach ($request->hizmetler_bayan as $key => $value) {
             $hizmetler = new SalonHizmetler();
             $hizmetler->salon_id = $isletme->id;
             $hizmetler->hizmet_id = $value;
             $hizmetler->hizmet_kategori_id = Hizmetler::where('id',$value)->value('hizmet_kategori_id');
             $hizmetler->baslangic_fiyat = $request->salonsunulanhizmetbayanbaslangicfiyat[$key];
             $hizmetler->son_fiyat = $request->salonsunulanhizmetbayansonfiyat[$key];

             $hizmetler->bolum = 0;
             $hizmetler->save();
            }
        }
        if($request->has('hizmetler_bay')){
           
           
             foreach ($request->hizmetler_bay as $key => $value) {
             $hizmetler = new SalonHizmetler();
             $hizmetler->salon_id = $isletme->id;
             $hizmetler->hizmet_id = $value;
             $hizmetler->hizmet_kategori_id = Hizmetler::where('id',$value)->value('hizmet_kategori_id');
             $hizmetler->baslangic_fiyat = $request->salonsunulanhizmetbaybaslangicfiyat[$key];
             $hizmetler->son_fiyat = $request->salonsunulanhizmetbaysonfiyat[$key];
             $hizmetler->bolum = 1;
             $hizmetler->save();
            }
        }
        if($request->has('salonsunulanhizmetbayanid')){
            foreach ($request->salonsunulanhizmetbayanid as $key => $value) {
                $hizmetler = SalonHizmetler::where('hizmet_id',$value)->where('bolum',0)->where('salon_id',$isletme->id)->first();
                $hizmetler->hizmet_kategori_id = Hizmetler::where('id',$value)->value('hizmet_kategori_id');
                 $hizmetler->baslangic_fiyat = $request->salonsunulanhizmetbayanbaslangicfiyat[$key];
                 $hizmetler->son_fiyat = $request->salonsunulanhizmetbayansonfiyat[$key];
                 $hizmetler->save();
            }
        }
        if($request->has('salonsunulanhizmetbayid')){
            foreach ($request->salonsunulanhizmetbayid as $key => $value) {
                $hizmetler = SalonHizmetler::where('hizmet_id',$value)->where('bolum',1)->where('salon_id',$isletme->id)->first();
                $hizmetler->hizmet_kategori_id = Hizmetler::where('id',$value)->value('hizmet_kategori_id');
                 $hizmetler->baslangic_fiyat = $request->salonsunulanhizmetbaybaslangicfiyat[$key];
                 $hizmetler->son_fiyat = $request->salonsunulanhizmetbaysonfiyat[$key];
                 $hizmetler->save();
            }
        }
       

        $saloncalismasaatlerieski = SalonCalismaSaatleri::where('salon_id',$isletme->id)->get();
        if($saloncalismasaatlerieski){
            foreach ($saloncalismasaatlerieski as $key => $value) {
                $silineceksaatler = SalonCalismaSaatleri::where('id',$value->id)->first();
                $silineceksaatler->delete();
            }
        }

        for($i=1;$i<=7;$i++){
            
            $saloncalismasaatleri = new SalonCalismaSaatleri();
            $saloncalismasaatleri->haftanin_gunu = $i;
            $saloncalismasaatleri->salon_id = $isletme->id;
            if(isset($_POST['calisiyor'.$i])){

                $saloncalismasaatleri->calisiyor = 1;
               
               
            }
            else{
                $saloncalismasaatleri->calisiyor = 0;
            }
                
             if($i==1){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati1;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati1;
                }
                 if($i==2){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati2;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati2;
                }
                 if($i==3){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati3;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati3;
                }
                 if($i==4){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati4;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati4;
                }
                 if($i==5){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati5;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati5;
                }
                 if($i==6){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati6;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati6;
                }
                 if($i==7){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati7;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati7;
                }
                $saloncalismasaatleri->save();
            
        }

        if(isset($_FILES["isletmegorselleri"]["name"])){
          
             for($i=0;$i<count($_FILES["isletmegorselleri"]["name"]);$i++){
                 $salongorselleri = new SalonGorselleri();
                 $dosya = $request->isletmegorselleri[$i];
                 $kaynak = $_FILES["isletmegorselleri"]["tmp_name"][$i];
                 $dosya  = str_replace(" ", "_", $_FILES["isletmegorselleri"]["name"][$i]);
                  $dosya  = str_replace(" ", "-", $_FILES["isletmegorselleri"]["name"][$i]);
                 $uzanti = explode(".", $_FILES["isletmegorselleri"]["name"][$i]);
                  $hedef  = "./" . $dosya;
                  if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                }  
                $salongorselleri->salon_gorseli = $hedef;
                $salongorselleri->salon_id = $isletme->id;
                $salongorselleri->kapak_fotografi = 0;
                $salongorselleri->save();
             }
               
        }
        echo 'Salon bilgileri başarı ile güncellendi';

    }
    public function mevcutavantajduzenleme(Request $request){
         $avantaj = SalonKampanyalar::where('id',$request->avantajid)->first();
         $avantaj->kampanya_baslik = $request->kampanya_baslik;
         $avantaj->kampanya_aciklama = $request->kampanya_aciklama;
         if($request->avantajbitistarih != null && $request->avantajbitistarih != '')
            $avantaj->kampanya_bitis_tarihi = $request->avantajbitistarih.' 23:59:59';
        else
            $avantaj->kampanya_bitis_tarihi = null;

        $avantaj->kampanya_fiyat = $request->kampanya_fiyat;
        $avantaj->hizmet_normal_fiyat = $request->hizmet_normal_fiyat;
        $avantaj->kampanya_detay = $request->kampanya_detay;
         $avantaj->save();

         if(isset($_FILES["isletmegorselleri"]["name"])){
          
             for($i=0;$i<count($_FILES["isletmegorselleri"]["name"]);$i++){
                 $salongorselleri = new SalonGorselleri();
                 $dosya = $request->isletmegorselleri[$i];
                 $kaynak = $_FILES["isletmegorselleri"]["tmp_name"][$i];
                 $dosya  = str_replace(" ", "_", $_FILES["isletmegorselleri"]["name"][$i]);
                  $dosya  = str_replace(" ", "-", $_FILES["isletmegorselleri"]["name"][$i]);
                 $uzanti = explode(".", $_FILES["isletmegorselleri"]["name"][$i]);
                  $hedef  = "./" . $dosya;
                  if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                }  
                $salongorselleri->salon_gorseli = $hedef;
                $salongorselleri->salon_id = $request->isletmeid;
                $salongorselleri->kapak_fotografi = 0;
                $salongorselleri->kampanya_gorsel = 1;
                $salongorselleri->kampanya_gorsel_kapak =0;
                $salongorselleri->kampanya_id = $request->avantajid;
                $salongorselleri->save();
             }
               
        }
        if(isset($_FILES["isletmekapakfoto"]["name"])){
          
              $dosya  = $request->isletmekapakfoto;
               $kaynak = $_FILES["isletmekapakfoto"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["isletmekapakfoto"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["isletmekapakfoto"]["name"]);
                        $uzanti = explode(".", $_FILES["isletmekapakfoto"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/salon_gorselleri/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
              $salongorselkapakeski = SalonGorselleri::where('kapak_fotografi',1)->where('salon_id',$isletme->id)->first();
              if($salongorselkapakeski){
                  $salongorselkapakeski->delete();
              }
              
              $salongorselkapak = new SalonGorselleri();
              $salongorselkapak->salon_id= $request->isletmeid;
              $salongorselkapak->kapak_fotografi = 0;
              $salongorselkapak->kampanya_gorsel = 1;
              $salongorselkapak->kampanya_gorsel_kapak = 1;
              $salongorselkapak->salon_gorseli = $hedef;
               $salongorselleri->kampanya_id = $request->avantajid;
              $salongorselkapak->save();
               
        }
        if(Auth::user()->admin==1){

        $mevcutaramaterimleri = AramaTerimleriKampanya::where('salon_id',$request->isletmeid)->where('kampanya_id',$request->avantajid)->get();
        if($mevcutaramaterimleri->count()!=0){
            foreach ($mevcutaramaterimleri as $key => $value) {
                if($key==0 && ($request->etiket1 != null || $request->etiket1!='') ){
                    $aramaterimleri = AramaTerimleriKampanya::where('id',$value->id)->first();
                    $aramaterimleri->salon_id =$request->isletmeid;
                    $aramaterimleri->arama_terimi = $request->etiket1;
                    $aramaterimleri->kampanya_id = $request->avantajid;
                    $aramaterimleri->save();
                }
                   if($key==1 &&($request->etiket2 != null || $request->etiket2!='')){
             $aramaterimleri = AramaTerimleriKampanya::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket2;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($key==2 &&($request->etiket3 != null || $request->etiket3!='')){
             $aramaterimleri = AramaTerimleriKampanya::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket3;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($key==3 &&($request->etiket4 != null || $request->etiket4!='')){
             $aramaterimleri = AramaTerimleriKampanya::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket4;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($key==4 &&($request->etiket5 != null || $request->etiket5!='')){
             $aramaterimleri = AramaTerimleriKampanya::where('id',$value->id)->first();

             $aramaterimleri->salon_id =$request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket5;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($key==5 &&($request->etiket6 != null || $request->etiket6!='')){
             $aramaterimleri = AramaTerimleriKampanya::where('id',$value->id)->first();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket6;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
            }
        }
            else{

           if($request->etiket1 != null || $request->etiket1!=''){

             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket1;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($request->etiket2 != null || $request->etiket2!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket2;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($request->etiket3 != null || $request->etiket3!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket3;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($request->etiket4 != null || $request->etiket4!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket4;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($request->etiket5 != null || $request->etiket5!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket5;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
          if($request->etiket6 != null || $request->etiket6!=''){
             $aramaterimleri = new AramaTerimleriKampanya();

             $aramaterimleri->salon_id = $request->isletmeid;
             $aramaterimleri->arama_terimi = $request->etiket6;
             $aramaterimleri->kampanya_id = $request->avantajid;
             $aramaterimleri->save();
        }
        }
            
       
        }

    }
     public function personelhizmetekle(Request $request,$id){
        foreach($request->hizmetler as $hizmet){
            $personelhizmet = new PersonelHizmetler();

            $personelhizmet->personel_id = $id;
            $personelhizmet->hizmet_id = $hizmet;
            $personelhizmet->save();

        }
         $personelhizmetler = PersonelHizmetler::where('personel_id',$id)->orderBy('id','desc')->get();
         $personelhizmetleri_html = "";
          foreach($personelhizmetler as $personelhizmet){
                $personelhizmetleri_html .="<tr><td>".$personelhizmet->hizmetler->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel_superadmin' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
          }
          echo $personelhizmetleri_html;
        
    }
     public function personelhizmetsil(Request $request,$id){
        $hizmet = PersonelHizmetler::where('id',$request->hizmet)->first();
        $hizmet->delete();

        $personelhizmetler = PersonelHizmetler::where('personel_id',$id)->orderBy('id','desc')->get();
         $personelhizmetleri_html = "";
          foreach($personelhizmetler as $personelhizmet){
                $personelhizmetleri_html .="<tr><td>".$personelhizmet->hizmetler->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
          }
          echo $personelhizmetleri_html;



    }
    public function personelprofilresmiyukle(Request $request,$id){
        $personel = Personeller::where('id',$id)->first();
       
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
                        $personel->profil_resmi = $hedef;
                    }
         $personel->save(); 
         echo $hedef;   
         
   
    }
    public function personelbilgiguncelle(Request $request,$id){
        $personel = Personeller::where('id',$id)->first();
        $personel->personel_adi = $request->personeladi;
        $personel->unvan = $request->unvan;
        $personel->cinsiyet = $request->personelcinsiyet;
        $personel->save();
        $personelcalismasaatlerieski = PersonelCalismaSaatleri::where('personel_id',$id)->get();
        if($personelcalismasaatlerieski){
            foreach ($personelcalismasaatlerieski as $key => $value) {
                $silineceksaatler = PersonelCalismaSaatleri::where('id',$value->id)->first();
                $silineceksaatler->delete();
            }
        }
        for($i=1;$i<=7;$i++){
            
            $personelcalismasaatleri = new PersonelCalismaSaatleri();
            $personelcalismasaatleri->haftanin_gunu = $i;
            $personelcalismasaatleri->personel_id = $id;
            if(isset($_GET['calisiyor'.$i])){

                $personelcalismasaatleri->calisiyor = 1;
               
               
            }
            else{
                $personelcalismasaatleri->calisiyor = 0;
            }
                
             if($i==1){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati1;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati1;
                }
                 if($i==2){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati2;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati2;
                }
                 if($i==3){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati3;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati3;
                }
                 if($i==4){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati4;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati4;
                }
                 if($i==5){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati5;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati5;
                }
                 if($i==6){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati6;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati6;
                }
                 if($i==7){
                     $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati7;
                     $personelcalismasaatleri->bitis_saati = $request->bitissaati7;
                }
                $personelcalismasaatleri->save();
            
        }
        


    }
        
}
