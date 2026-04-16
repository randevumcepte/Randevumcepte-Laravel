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
use App\RandevuHizmetler;

use App\AramaTerimleri;
use App\IsletmeYetkilileri;
use App\MusteriPortfoy;
use App\Subeler;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
 use App\Favoriler;
 use Illuminate\Support\Facades\DB;
  use Hash;
  use Mail;
use App\User;
use App\Bildirimler;
use App\BildirimKimlikleri;
class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

     
 
       
         $this->middleware('auth');
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
    }

    public function yorumyap(Request $request){
       $user = User::where('id',Auth::user()->id)->first();
       $yorumyeni = new SalonYorumlar();
       $yorumyeni->salon_id = $request->yorum_isletmeid;
       $yorumyeni->user_id = $user->id;
       $yorumyeni->yorum = $request->yorumtext_yorum;
       $yorumyeni->save();
       $puanyeni = new SalonPuanlar();
       $puanyeni->salon_id = $request->yorum_isletmeid;
       $puanyeni->puan = $request->puanlama;
       $puanyeni->user_id = $user->id;
       $puanyeni->save();
       $isletme = Salonlar::where('id',$request->yorum_isletmeid)->first();

      
       return redirect('/##yorumtext_yorum');
    }
    public function randevuonayla($salonno,$hizmetler,$personeller,$randevutarihi,$randevusaati){
        $secilenhizmetler = explode('_',$hizmetler);
        $secilenpersoneller = explode('_',$personeller);
        $tumhizmetler = Hizmetler::all();
        $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
        $salon = Salonlar::where('id',$salonno)->first();
        $secilenhizmetler = Hizmetler::whereIn('id',$secilenhizmetler)->get();
        $secilenpersoneller = Personeller::whereIn('id',$secilenpersoneller)->get();
        $salonturleri = SalonTuru::all();
        $salonpuanlar = SalonPuanlar::where('salon_id',$salonno)->get();
 $salonyorumlar = SalonYorumlar::where('salon_id',$salonno)->orderBy('updated_at','desc')->get();
        $aramaterimleri = AramaTerimleri::where('salon_id',$salonno)->get();
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

        return view('randevuonayla',['secilenhizmetler'=>$secilenhizmetler,'secilenpersoneller'=>$secilenpersoneller ,'salon'=>$salon,'hizmetler'=>$tumhizmetler,'hizmetkategorileri' => $hizmetkategorileri,'randevutarihi'=> date('d.m.Y',strtotime($randevutarihi)),'randevusaati' => str_replace('_', ':', $randevusaati),'personelparametre' => $personeller,'salonturleri' => $salonturleri, 'aramaterimimeta' => $aramaterimimeta, 'aramaterimlerihepsi' => $aramaterimleritaglar, 'aramaterimisayfa' =>$aramaterimianasayfa ,'aramaterimleriid' =>$aramaterimleriid,'salonpuanlar' => $salonpuanlar,'salonyorumlar' => $salonyorumlar]);
    }
    public function randevuekle(Request $request){
        try{
            $randevu = new Randevular();
            $salon = Salonlar::where('id',$request->salonno)->first();
            $randevu->user_id = Auth::user()->id;
            $randevu->salon_id = $request->salonno;
            $randevu->tarih = $request->randevutarihi;
           
            $randevu->saat = $request->randevusaati;
            $randevu->notlar = $request->randevunotu;
            $randevu->web = true;

            $randevu->durum = 0;
            $randevu->saat_bitis = date('H:i:s',strtotime('+'.$salon->randevu_saat_araligi.' minutes',strtotime($request->randevusaati)));
            $randevu->save();
            $musteriporfoyunde = MusteriPortfoy::where('user_id',Auth::user()->id)->where('salon_id',$request->salonno)->first();
            if(!$musteriporfoyunde){
                $portfoyyeni = new MusteriPortfoy();
                $portfoyyeni->user_id = Auth::user()->id;
                $portfoyyeni->salon_id = $request->salonno;
                $portfoyyeni->tur = 1;
                $portfoyyeni->save();
            }
            $personelid = 0;
            $hizmetid = 0;
            $baslangicSaati = $randevu->saat;
            $personeller = array();
            foreach ($request->hizmetler as $key => $value) {
            
                $randevuhizmetler = new RandevuHizmetler();
                $randevuhizmetler->randevu_id = $randevu->id;
                $randevuhizmetler->hizmet_id = $value;
                $randevuhizmetler->saat = $baslangicSaati;
                $sure = SalonHizmetler::where('hizmet_id',$value)->where('salon_id',$randevu->salon_id)->value('sure_dk');
                if($sure === null && $sure ==='')
                    $sure=60;
                $randevuhizmetler->saat_bitis = date('H:i:s',strtotime('+'.$sure.' minutes',strtotime($baslangicSaati)));
                $randevuhizmetler->sure_dk = $sure;
                $randevuhizmetler->personel_id = $request->personeller[$key];
                $randevuhizmetler->save();
                $baslangicSaati = $randevuhizmetler->saat_bitis;
                array_push($personeller,$request->personeller[$key]);


            }  
            /*Mail Bildirim Bloğu */

            $isletme = Salonlar::where('id',$request->salonno)->first();
            $mesaj = $isletme->salon_adi." için oluşturduğunuz ".date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($randevu->saat)) ." tarihli randevu talebiniz alınmıştır. Talebiniz kısa sürede sonuçlanacaktır. İlginiz için teşekkür ederiz.";
            $mesaj_isletme = Auth::user()->name .' isimli müşteri '.date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($randevu->saat)) ." için randevu oluşturmuş olup onayınızı bekliyor.";
            
            $mesaj_isletme_bildirim = Auth::user()->name .' '.date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($randevu->saat)) ." için randevu oluşturmuştur";
            if($isletme->yeni_sms)
            {
                $smsController = app()->make(SMSController::class);
                $smsController->tekilSMSGonderVoiceTelekom(Auth::user()->cep_telefon,$mesaj,$randevu->salon_id,'Randevu talebi bilgilendirme');
            }
            else
            {
                $postUrl = "https://api.efetech.net.tr/v2/sms/basic";
                $apiKey = $isletme->sms_apikey; // should match with Server key
                $headers = array(
                     'Authorization: Key '.$apiKey,
                     'Content-Type: application/json',
                     'Accept: application/json'
                );
                 $postData = json_encode( array( "originator"=> $isletme->sms_baslik, "message"=>$mesaj, "to"=>[Auth::user()->cep_telefon],"encoding"=>"auto") );
                 $postData2 = json_encode( array( "originator"=> $isletme->sms_baslik, "message"=>$mesaj_isletme, "to"=>['05316237563'],"encoding"=>"auto") );
              
                 
                $ch=curl_init();
                curl_setopt($ch,CURLOPT_URL,$postUrl);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch,CURLOPT_TIMEOUT,5);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
                $response=curl_exec($ch);
                curl_close($ch);
            }
            

           

            $isletmeyetkilileri = Personeller::where('salon_id',$request->salonno)->where(function($q) use($personeller){
                $q->whereIn('id',$personeller);
                $q->orWhere('role_id','<',5);
            })->get();
            
            
            foreach($isletmeyetkilileri as $yetkili)
            {

                self::bildirimekle($request,$request->salonno,$mesaj_isletme,"#",$yetkili->id,null, Auth::user()->profil_resim,$randevu->id);

            }
            $bildirimkimlikleri = BildirimKimlikleri::whereIn('isletme_yetkili_id',$isletmeyetkilileri->pluck('yetkili_id')->toArray())->where('bildirim_id','!=',null)->pluck('bildirim_id')->toArray(); 
            
            self::bildirimgonder($bildirimkimlikleri,$mesaj_isletme,"Yeni Randevu");
            


            /* $mesaj_isletme = str_replace(' ','%20',$mesaj_isletme);
            $postUrl2="http://panel.1sms.com.tr:8080/api/smsget/v1?username=avantajbu&password=d42249a8aec4b8b2503105909bcbc329&header=AVANTAJBU&gsm=90".$isletmegsm."&message=".$mesaj_isletme;

            $ch2=curl_init();
            curl_setopt($ch2,CURLOPT_URL,$postUrl2);
            curl_setopt($ch2,CURLOPT_TIMEOUT,5);
            curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);

            $response2=curl_exec($ch2);
            curl_close($ch2);*/

 
            echo  "<span style='background-color:1px solid #FF4E00; font-size:17px; font-weight:bold; border:radius:60px;padding:10px'>Randevunuz başarı ile oluşturuldu. Yönlendiriliyorsunuz...</span>";
        }
        catch(Exception $e){
            echo "Bir hata oluştu : ".$e->getMessage(); 
        } 
    }
    public function bildirimekle(Request $request,$salonid,$mesaj,$url,$personelid,$musteriid,$imgurl,$randevuid)
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
        $bildirim->save();
    }
    public function bildirimgonder($bildirimkimlikleri,$mesaj,$baslik){
        $post_url_push_notification = "https://api.onesignal.com/notifications?c=push";

         $headers_push_notification = array(
                                        'Accept: application/json',
                                        'Authorization: Key os_v2_app_lzipqtrm3bctfj3f6lfyfirp7ghx6w4i7t6e6iufqzlj6ginpkucdwamtgxy5bclne737yh7y62zxlfmep2c4ijioiimrps4jcq5ysi',
                                        'Content-Type: application/json',
        );

         
        $post_data_push_notification = 
            json_encode( 
            
                array( 
                    "app_id"=> "5e50f84e-2cd8-4532-a765-f2cb82a22ff9",
                 
                    "include_player_ids" =>  $bildirimkimlikleri,
                    "android_channel_id" => '12d6537e-7a7d-4d1d-a838-e3fc947eaf44',
                    "contents" => array("en"=>  $mesaj),
                    "headings" =>  array("en"=> $baslik),
                    "sound" => "default",
                     
                ) 
            );
        $ch_push_notification=curl_init();
        curl_setopt($ch_push_notification,CURLOPT_URL,$post_url_push_notification);
        curl_setopt($ch_push_notification,CURLOPT_POSTFIELDS,$post_data_push_notification);
        curl_setopt($ch_push_notification,CURLOPT_POST,1);
        curl_setopt($ch_push_notification,CURLOPT_TIMEOUT,5);
        curl_setopt($ch_push_notification,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch_push_notification,CURLOPT_HTTPHEADER,$headers_push_notification);
        $response_push_notifications=curl_exec($ch_push_notification);
        curl_close($ch_push_notification);
    }
       
  
    public function randevularim(){
       
        $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
            $hizmetler = Hizmetler::all();
            $salonturleri = SalonTuru::all();
            $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
		 $randevular = Randevular::where('user_id',Auth::user()->id)->where('salon_id',$salon->id)->orderBy('id','desc')->get();
        return view('user.randevular',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler' => $hizmetler,'salonturleri' => $salonturleri,'randevular' => $randevular,'salon'=>$salon]);

    }
    public function ayarlar(){
          $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
            $hizmetler = Hizmetler::all();
            $salonturleri = SalonTuru::all();
            $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();
        return view('user.ayarlar',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler' => $hizmetler,'salonturleri' => $salonturleri,'salon'=>$salon]);
    }
    public function favoriler(){
            $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
            $hizmetler = Hizmetler::all();
            $salonturleri = SalonTuru::all();
            $favoriler = Favoriler::where('user_id',Auth::user()->id)->get();

            return view('user.favoriler',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler' => $hizmetler,'salonturleri' => $salonturleri, 'favoriler' => $favoriler]);
    }
    public function firsatlar(){
         $hizmetkategorileri = Hizmet_Kategorisi::limit(8)->get();
            $hizmetler = Hizmetler::all();
            $salonturleri = SalonTuru::all();
          

            return view('user.firsatlar',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler' => $hizmetler,'salonturleri' => $salonturleri]);
    }
    public function randevuiptalet(Request $request){
        $randevu = Randevular::where('id',$request->randevuno)->first();
        $randevu->durum = 3;
        $randevu->save();


        echo Salonlar::where('id',$randevu->salon_id)->value('salon_adi'). ' için randevunuz iptal edilmiştir';
    }
    public function randevuyorumlapuanla(Request $request){
        $yorumvar = SalonYorumlar::where('salon_id',$request->salonno)->where('user_id',Auth::user()->id)->first();
        $puanvar = SalonPuanlar::where('salon_id',$request->salonno)->where('user_id',Auth::user()->id)->first();
        if($request->salonyorum != null || $request->salonyorum != ''){
            if($yorumvar){
                $yorumvar->yorum = $request->salonyorum;
                $yorumvar->save();
            }
            else{
                 $yorum = new SalonYorumlar();
                 $yorum->user_id = Auth::user()->id;
                $yorum->salon_id = $request->salonno;
                $yorum->yorum = $request->salonyorum;
                $yorum->save();
            }
           
        }
        if($puanvar){
            $puanvar->puan = $request->puan;
            $puanvar->save();

        }
        else{
            $salonpuan = new SalonPuanlar();
            $salonpuan->user_id = Auth::user()->id;
            $salonpuan->salon_id = $request->salonno;
            $salonpuan->puan = $request->puan;
            $salonpuan->save();
        }
        if($puanvar || $yorumvar) 
            echo 'Geri bildiriminiz güncellenmiştir. Teşekkürler';
         
        else
            echo 'Geri bildiriminiz alınmıştır. Teşekkürler';
    }
    public function puanyorumgetir(Request $request){
        $puan = SalonPuanlar::where('salon_id',$request->salonno)->where('user_id',Auth::user()->id)->value('puan');
        $yorum = SalonYorumlar::where('salon_id',$request->salonno)->where('user_id',Auth::user()->id)->value('yorum');
        return ['puan' => $puan,'yorum' => $yorum];
    }
    public function kampanyafirsatbildirimler(Request $request){
        $user = Auth::user()->first();
        $user->kampanya_firsat_bildirim = $request->data;
        
        $user->save();
    }
    public function sifredegistir(Request $request){
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return array( 
                'type' =>'warning',
                'title' => 'Uyarı',
                'text' => "Eski şifrenizi yanlış girdiniz!",
            );
            exit;
        }
 
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            //Current password and new password are same
             return array( 
                'type' =>'warning',
                'title' => 'Uyarı',
                'text' => "Girdiğiniz şifreler uyuşmamaktadır!",
            );
            exit;
        }
 
        $validatedData = $this->validate($request,[
            'current-password' => 'required',
            'new-password' => 'required|string|min:3|confirmed',
        ]);
 
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new-password'));
        $user->save();
          return array( 
                'type' =>'success',
                'title' => 'Başarılı',
                'text' => "Şifreniz başarıyla değiştirildi.",
            );
        
    }
    public function favorilereekle(Request $request){
        try{
               $user = Auth::user();
        $salon = Salonlar::where('id',$request->salonno)->first();
        $favori = new Favoriler();
        $favori->user_id = $user->id;
        $favori->salon_id = $salon->id;
        $favori->save();
        echo $salon->salon_adi." favori listenize başarı ile eklendi";
        }
        catch(Exception $e){
            echo "Bir hata oluştu : ".$e->getMessage();
        }
     
    }
    
        
}
