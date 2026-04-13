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
use App\SalonMolaSaatleri;
use App\SalonPuanlar;
use App\SalonYorumlar;
use App\PersonelPuanlar;
use App\PersonelYorumlar;
use App\SalonHizmetler;
use App\Randevular;
use App\KasaDefteri;
use App\PersonelHizmetler;
use App\PersonelCalismaSaatleri;
use App\RandevuHizmetler;
use App\AramaTerimleri;
use App\SalonKampanyalar;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
use App\IsletmeYetkilileri;
use App\IsletmeAnaTur;
 use App\User;
 use App\SMSListeleri;
 use App\SMSListeBilgiler;
 use App\SMSPaketleri;
 use App\SMSBilgiler;
 use App\MusteriPortfoy;
 use App\SMSTaslaklari;
 use App\SMSIletimRaporlari;
 use App\SatinAlinanKampanyalar;
 use App\KampanyaYapilanOdemeler;
 use App\Subeler;
 use Illuminate\Support\Facades\DB;
 use Illuminate\Support\Facades\Schema;
 use App\Islemler;
 use App\Urunler;
 use App\UrunSatislari;
 use App\Tahsilatlar;
 use App\Paketler;
 use App\PaketSatislari;
 use App\RandevuHareketleri;
 use App\OnGorusmeler;
 use App\Bildirimler;
 use App\BildirimKimlikleri;
 use App\Masraflar;
 use App\MasrafKategorisi;
 use App\Alacaklar;
 use Hash;
 use Mail;
 use Excel;

use Illuminate\Support\Facades\App;
class StoreAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

         $this->middleware('auth:isletmeyonetim');
          
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         $gonderilensmssayisi = 0;
         $bekleyensmssayisi = 0;
         $hatalismssayisi = 0;
         $basarilismssayisi = 0;
         $randevular = "";
         $randevusayisi = 0;
         $musterisayisi = 0;
         if(Auth::user()->is_admin){
            $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->orderBy('id','desc')->limit(5)->get();
            $randevusayisi = Randevular::where('salon_id',Auth::user()->salon_id)->count();
            
            
         }
         else{
            $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->orderBy('id','desc')->limit(5)->get();
             $randevusayisi = Randevular::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->count();
               
         }
        $musterisayisi = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->groupBy('user_id')->get();
            
        $personelsayisi = Personeller::where('salon_id',Auth::user()->salon_id)->count();

         
         $musterisayisirakam = 0;
         foreach ($musterisayisi as $musterisayi) {
              $musterisayisirakam ++;
         }
         $kampanyasayisi = SalonKampanyalar::where('salon_id',Auth::user()->salon_id)->count();

         $salonpuan = SalonPuanlar::where('salon_id',Auth::user()->salon_id)->sum('puan');
         $salonpuanlamasayi = SalonPuanlar::where('salon_id',Auth::user()->salon_id)->count();
         if($salonpuanlamasayi != 0)
            $salonpuan = $salonpuan / $salonpuanlamasayi;
        else 
            $salonpuan=0;
         $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
         $salonyorumlar = SalonYorumlar::where('salon_id',Auth::user()->salon_id)->orderBy('updated_at','desc')->limit(5)->get();
         $kasatoplam = KasaDefteri::where('gelir_gider',1)->where('salon_id',Auth::user()->salon_id)->sum('miktar')-KasaDefteri::where('gelir_gider',0)->where('salon_id',Auth::user()->salon_id)->sum('miktar');
         $salonyorumsayisi = SalonYorumlar::where('salon_id',Auth::user()->salon_id)->count();
          $tumkampanyalar = SalonKampanyalar::where('salon_id',Auth::user()->salon_id)->get();

          $raporlar = SMSIletimRaporlari::where('salon_id',Auth::user()->salon_id)->get();
          $smsbilgiler = SMSBilgiler::where('salon_id',Auth::user()->salon_id)->first();
            
          $raporhtml = "";
       /* foreach ($raporlar as $key => $value) {
              $postUrl="http://panel.1sms.com.tr:8080/api/dlr/v1?username=".$smsbilgiler->kullanici_adi."&password=".$smsbilgiler->sifre."&id=".$value->rapor_id;

            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,$postUrl);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

            $response=curl_exec($ch);
            curl_close($ch);
            $responsemesaj = explode('|', $response);
           
            foreach ($responsemesaj as $key2 => $value2) {
                 
                if($key2==0 && $value2=='99')
                    $raporhtml .='<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 99 : Dokümante edilmemiş bilinmeyen hata. Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                else if($key2==0 && $value2=='95')
                    $raporhtml .= '<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 95 : USE_GET_METHOD Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                 else if($key2==0 && $value2=='93')
                    $raporhtml .= '<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 93 : MISSING_GET_PARAMS Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                 else if($key2==0 && $value2=='87')
                    $raporhtml .= '<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 87 : WRONG_USER_OR_PASSWORD Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                else if($key2==0 && $value2=='79')
                    $raporhtml .= '<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 79 : DLR_ID_NOT_FOUND Rapor bulunamadı. Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                 else if($key2==0 && $value2=='29')
                    $raporhtml .= '<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>Mesaj yollanmak üzere. Lütfen bekleyiniz</td><td></td><td></td>';
                 else if($key2==0 && $value2=='27')
                    $raporhtml .= '<tr><td></td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>Mesaj yollanırken raporlamada beklenmeyen hata oluştu</td><td></td><td></td>';
                 else if($key2>0){
                    $numara_durum = explode(' ', $value2);
                    $musteriprofilresim = User::where('cep_telefon', substr($numara_durum[0], 2))->value('profil_resim');
                        $profillink = "";
                    if($musteriprofilresim == null ||$musteriprofilresim =='')
                            $profillink = 'http://'.$_SERVER['SERVER_NAME'].'/public/isletmeyonetim_assets/img/avatar.png';
                    else
                        $profillink = 'http://'.$_SERVER['SERVER_NAME'].'/'.$musteriprofilresim;
                    
                    $raporhtml.= '<tr><td class="user-avatar"><img src="'.$profillink.'" alt="Avatar"/></td><td>'.User::where('cep_telefon',substr($numara_durum[0], 2))->value('name').'<br />'.$numara_durum[0].'</td>';
                    $mesajdurum = '';
                    if($numara_durum[1] == '0'){
                        $mesajdurum = '<td style="color:orange">Beklemede</td>';
                        $bekleyensmssayisi++;
                    }
                            
                    else if($numara_durum[1]=='5'){
                        $mesajdurum = '<td style="color:orange">SMS Gönderildi, İletim Raporu Bekleniyor</td>';
                        $basarilismssayisi++;
                    }
                    else if($numara_durum[1]=='6'){
                        $mesajdurum = '<td style="color:red">Başarısız</td>';
                        $hatalismssayisi++;
                    }
                    else if($numara_durum[1]=='9'){
                        $mesajdurum = '<td style="color:green">Başarılı</td>';
                        $basarilismssayisi++;
                    }
                    $raporhtml .= '<td>'.date('d.m.Y H:i',strtotime($value->created_at)).'<td>'.$value->aciklama.'</td>'.$mesajdurum;
                 }
                 $raporhtml .='</tr>'; 


            }}*/
            $kalankullanim=0;
           /* if($smsbilgiler){
                  $kredisorgulama="http://panel.1sms.com.tr:8080/api/credit/v1?username=".$smsbilgiler->kullanici_adi."&password=".$smsbilgiler->sifre;

                $ch2=curl_init();
                curl_setopt($ch2,CURLOPT_URL,$kredisorgulama);
                curl_setopt($ch2,CURLOPT_TIMEOUT,5);
                curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);

                $response2=curl_exec($ch2);
                curl_close($ch2);
                $kalankredi = explode(' ',$response2);
                $kalankullanim = 0;
            }*/
            $acik_adisyonlar = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->join('users','randevular.user_id','=','users.id')->leftJoin('urun_satislari','urun_satislari.randevu_id','=','randevular.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->leftJoin('urunler','urun_satislari.urun_id','=','urunler.id')->select(
                    'users.name as musteri',
                    DB::raw("CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),'<br> (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') as hizmetler"),
                    DB::raw('GROUP_CONCAT(urunler.urun_adi) as urunler'),
                    DB::raw('CONCAT(SUM(randevu_hizmetler.fiyat) + COALESCE(SUM(urun_satislari.fiyat), 0) ," ₺") as toplam'),
                    'randevular.tarih as tarih',
                    DB::raw('CONCAT("<a title=\"Detaylı Bilgi\" href=\"/isletmeyonetim/randevudetay/",randevular.id,"\" type=\"button\" class=\"btn btn-primary\"><i class=\"dw dw-eye\"></i></a>") as islemler'),
                )->where('randevular.salon_id',Auth::user()->salon_id)->where('randevular.acik',true)->whereMonth('randevular.tarih','=',date('m'))->groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();
           $yaklasan_dogumgunleri = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->select('users.name as ad_soyad','users.cep_telefon as telefon', 'users.dogum_tarihi as dogum_tarihi')->whereDay('dogum_tarihi', '>=',date('d'))->whereDay('dogum_tarihi','<=',date('d',strtotime('+5 days',strtotime(date('Y-m-d')))))->where('musteri_portfoy.salon_id',Auth::user()->salon_id)->get();
           $alacak_hatirlatmalari = DB::table('alacaklar')->join('randevular','alacaklar.randevu_id','=','randevular.id')->join('users','alacaklar.user_id','=','users.id')->select('users.name as adsoyad','alacaklar.planlanan_odeme_tarihi as odemetarihi','alacaklar.tutar as tutar')->where('alacaklar.salon_id',Auth::user()->salon_id)->whereDay('planlanan_odeme_tarihi','<=',date('d', strtotime('+5 days', strtotime(date('Y-m-d')))))->whereDay('planlanan_odeme_tarihi','>=',date('d'))->get();
           $paketler = self::paket_liste_getir('',true);
          return view('isletmeadmin.dashboard',['paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'sayfa_baslik'=>'Özet','randevular'=>$randevular,'musterisayisi'=>$musterisayisirakam,'randevusayisi'=>$randevusayisi,'salonyorumlar' => $salonyorumlar,'salonpuan' =>$salonpuan,'salonyorumsayisi'=>$salonyorumsayisi,'title' => Salonlar::where('id',Auth::user()->salon_id)->value('salon_adi'). ' Yönetim Paneli','pageindex' => 1,'isletme'=>$isletme,'kasatoplam' => $kasatoplam,'smsrapor'=>$raporhtml,'bekleyensms'=>$bekleyensmssayisi,'hatalisms'=>$hatalismssayisi,'basarilisms'=>$basarilismssayisi,'kalansmskullanim' => $kalankullanim,'kampanyasayisi' => $kampanyasayisi,'tumkampanyalar' => $tumkampanyalar,'personelsayisi'=>$personelsayisi,'acik_adisyonlar'=>$acik_adisyonlar,'yaklasan_dogumgunleri'=>$yaklasan_dogumgunleri,'alacak_hatirlatmalari'=>$alacak_hatirlatmalari]); 
    }
    public function acikadisyonlar(Request $request){
          $acik_adisyonlar = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->join('users','randevular.user_id','=','users.id')->leftJoin('urun_satislari','urun_satislari.randevu_id','=','randevular.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->leftJoin('urunler','urun_satislari.urun_id','=','urunler.id')->select(
                    'users.name as musteri',
                    DB::raw("CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') as hizmetler"),
                    DB::raw('GROUP_CONCAT(urunler.urun_adi) as urunler'),
                    DB::raw('SUM(randevu_hizmetler.fiyat) + COALESCE(SUM(urun_satislari.fiyat), 0) as toplam'),
                    'randevular.tarih as tarih',
                    DB::raw('CONCAT("<a title=\"Detaylı Bilgi\" href=\"/isletmeyonetim/musteridetay/",randevular.id," type=\"button\" class=\"btn btn-info\"><i class=\"dw dw-eye\"></i></a>") as islemler'),
                )->where('randevular.salon_id',Auth::user()->salon_id)->where('randevular.acik',true)->whereMonth('randevular.tarih','=',date('m'))->groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();
         return $acik_adisyonlar;
    }
    public function randevular(Request $request){


        
        $personeller = "";
        $randevular = self::randevuyukle($request);
        $hizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
        $subeler = Subeler::where('salon_id',Auth::user()->salon_id)->where('aktif',1)->get();
        $mevcutmusteriler = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get();
         $paketler = self::paket_liste_getir('',true);
        if(Auth::user()->is_admin)
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        else
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->get();
        return view('isletmeadmin.randevular',['bildirimler'=>self::bildirimgetir($request), 'paketler'=>$paketler,'sayfa_baslik'=>'Randevu Takvimi','pageindex' => 2,'randevular'=>$randevular,'sunulanhizmetler'=>$hizmetler,'subeler'=>$subeler,'mevcutmusteriler'=>$mevcutmusteriler,'personeller'=>$personeller ]);
    }
    public function randevularfiltre(Request $request){
        $randevular = "";
        if(Auth::user()->is_admin)
            $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->where('tarih','like','%'.$request->tarih.'%')->where('sube_id','like','%'.$request->sube.'%')->orderBy('id','desc')->get();
        else
            $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->where('tarih','like','%'.$request->tarih.'%')->where('sube_id',Auth::user()->salon_personelleri->sube_id)->orderBy('id','desc')->get();

        $randevuliste = array();
        foreach($randevular as $randevu){
            $randevuliste1['musteri'] = '<span style="display:none">'.strtotime($randevu->tarih).'</span>'.$randevu->users->name;
            $randevuliste1['tarihsaat'] = date('d.m.Y',strtotime($randevu->tarih)). ' '.date('H:i', strtotime($randevu->saat));
            $randevuliste1['sube'] = $randevu->sube->sube;
            $hizmethtml = "";
            $durumhtml = "";
            $islemlerhtml = "";
            foreach(RandevuHizmetler::where('randevu_id',$randevu->id)->get() as $hizmet) 
                $hizmethtml .= $hizmet->hizmetler->hizmet_adi. "<br>" ;
                $islemlerhtml .= '  <button class="btn btn-primary randevudetayigetir" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-edit"></span> Düzenle
                            </button>';
            if($randevu->durum == 0){
                $durumhtml .= "<button class='btn btn-warning'>Beklemede</button>";
                $islemlerhtml .= ' <button class="btn btn-success randevuonayla" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-check-circle"></span> Onayla 
                            </button>';
                $islemlerhtml .=   '<button class="btn btn-danger randevuiptalet" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-minus-circle"></span> İptal Et 
                            </button>';
            }
            elseif($randevu->durum == 1){
                  $durumhtml .= "<button class='btn btn-success'>Onaylı</button>";
                $islemlerhtml .=   '<button class="btn btn-danger randevuiptalet" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-minus-circle"></span> İptal Et 
                            </button>';
            }
              
            else
                $durumhtml .= "<button class='btn btn-danger'>İptal</button>";
            $islemlerhtml .=   ' <button class="btn btn-default randevusil" style="background-color: #0080FF;color:#fff"  data-value="'.$randevu->id.'">
                               
                               <span class="mdi mdi-delete"></span> Sil
                             </button>
                              '; 
         
           

                            
                           
            $randevuliste1['durum'] = $durumhtml;
            $randevuliste1['hizmetler'] = $hizmethtml;
            $randevuliste1['islemler'] = $islemlerhtml;
            array_push($randevuliste, $randevuliste1);


        }
        return $randevuliste;


    }
    public function randevugetir(Request $request){
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $subeler = Subeler::where('salon_id',Auth::user()->salon_id)->get();
        $sube_html = "";
        $info['musteri'] = $randevu->users->name;
        $info['tarih'] = $randevu->tarih;
        $info['saat'] = $randevu->saat;

        $info['saatbitis'] = $randevu->saat_bitis;
        $info['telefon'] =  $randevu->users->ev_telefon;
        $info['gsm'] = $randevu->users->cep_telefon;
        $info['eposta'] = $randevu->users->email;
        $info['randevuid'] = $randevu->id;
        $info['durum'] = $randevu->durum;
        foreach($subeler as $sube){
            if($sube->id == $randevu->sube_id)
                $sube_html .= "<option selected value='".$sube->id."'>".$sube->sube."</option>";
            else
                $sube_html .= "<option value='".$sube->id."'>".$sube->sube."</option>";
        }
        $info['subeler'] = $sube_html;
        $randevuhizmetler = RandevuHizmetler::where('randevu_id',$randevu->id)->get();
        $hizmetler = SalonHizmetler::where('salon_id',$randevu->salon_id)->get();
        $hizmet_html = "";
        foreach($hizmetler as $hizmet){
            foreach($randevuhizmetler as $randevuhizmet){
                if($hizmet->hizmetler->id == $randevuhizmet->hizmet_id)
                    $hizmet_html .= "<option selected value='".$hizmet->id."'>".$hizmet->hizmetler->hizmet_adi."</option>";
                else
                    $hizmet_html .= "<option value='".$hizmet->id."'>".$hizmet->hizmetler->hizmet_adi."</option>";

            }
            if($randevuhizmetler->count() == 0)
                 $hizmet_html .= "<option value='".$hizmet->id."'>".$hizmet->hizmetler->hizmet_adi."</option>";

        }
         

        $info['hizmetler'] = $hizmet_html;
        return $info;
     }

     public function islemsonuraporugir(Request $request)
     {
        
        $randevu = "";
        $rapor ="";
        $yenirapor = false;
        if(!empty($request->islem_id)){
            $rapor = Islemler::where('id',$request->islem_id)->first();

        }
        else{
            $rapor = new Islemler();
            $yenirapor = true;
        }
  
        
        
        $musteri = User::where('id',$request->musteri_id)->first();
        $musteri->kil_yapisi = $request->kil_yapisi;
        $musteri->ten_rengi = $request->ten_rengi;
        $musteri->baslangic_kg = $request->baslangic_kilo;
        $musteri->baslangic_gogus = $request->baslangic_gogus;
        $musteri->baslangic_gobek = $request->baslangic_gobek;
        $musteri->baslangic_kalca = $request->baslangic_kalca;
        $musteri->baslangic_basen = $request->baslangic_basen;
        $musteri->baslangic_bel = $request->baslangic_bel;
        $musteri->baslangic_sirt = $request->baslangic_sirt;
        $musteri->save();
        $islem_text = "";
        $raportext = "";
        if($request->alinan_odeme > 0 && $yenirapor){
            $raportext ="Yeni girdi";
            $kasa = new KasaDefteri();
            $kasa->salon_id = Auth::user()->salon_id;
            $kasa->gelir_gider = 1;
           
                $islemler = SalonHizmetler::whereIn('hizmet_id',$request->yapilan_islemler)->get();
                $islemsayisi = SalonHizmetler::whereIn('hizmet_id',$request->yapilan_islemler)->count();
                
                $islemindex = 0;

                foreach($islemler as $islem){

                    $islem_text .= $islem->hizmetler->hizmet_adi;
                    $islemindex++;
                    if($islemindex != $islemsayisi)
                        $islem_text .= "<br>";
                }
                $kasa->aciklama = $musteri->name ." ". date('d.m.Y H:i')." tarihli ".$islem_text." için alınan işlem ücreti";
                $kasa->tarih = date('Y-m-d');
           

                
            
            $kasa->miktar = $request->alinan_odeme;
            $kasa->save();
        }
        else{
            $raportext ="girdi güncelleme";
             
            $islemler = SalonHizmetler::whereIn('hizmet_id',$request->yapilan_islemler)->get();
            $islemsayisi = SalonHizmetler::whereIn('hizmet_id',$request->yapilan_islemler)->count();
                 
            $islemindex = 0;
            foreach($islemler as $islem){

                    $islem_text .= $islem->hizmetler->hizmet_adi;
                    $islemindex++;
                    if($islemindex != $islemsayisi)
                        $islem_text .= "<br>";
                
            }

        }

        $rapor->personel_id = $request->uygulayan;

        $rapor->user_id = $musteri->id;
        $rapor->alinan_odeme = $request->alinan_ucret;
        
        $rapor->aciklama = $request->aciklama;
        $rapor->yapilan_islemler = $islem_text;
        $rapor->tarih = $request->tarih. " 00:00:00";
        $rapor->salon_id = Auth::user()->salon_id;
        $rapor->hizmet_kategori_id = $request->hizmet_kategori_id;
        

        $rapor->sube_id = Subeler::where('id',Personeller::where('id',$request->islemiyapanpersonel)->value('sube_id'))->value('id');

        $rapor->seans_no = $request->seans_no;
        if(isset($request->koltuk_alti))
            $rapor->koltuk_alti = true;
        if(isset($request->bacak))
            $rapor->back = true;
        if(isset($request->kol))
            $rapor->kol = true;
        if(isset($request->bikini))
            $rapor->bikini = true;
        if(isset($request->yuz))
            $rapor->yuz = true;
        if(isset($request->gogus))
            $rapor->gogus = true;
        if(isset($request->gobek))
            $rapor->gobek= true;
        if(isset($request->sirt))
            $rapor->sirt = true;
        if(isset($request->biyik))
            $rapor->biyik = true;
        if(isset($request->favori))
            $rapor->favori = true;
        if(isset($request->ense))
            $rapor->ense = true;

        $rapor->save();
        $rapor_cevap = "";
        if($yenirapor)
            $rapor_cevap =  "İşlem formu başarıyla oluşturuldu";
        else

            $rapor_cevap =  "İşlem formu başarıyla güncellendi";
        echo $rapor_cevap;


 
        


     }

     public function islemraporlari(Request $request){
        $tarih = "";
        if(isset($request->tarih))
            $tarih = $request->tarih;
        else
            $tarih = date('Y-m-d');

        
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $sunulanhizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->groupBy('hizmet_id')->get();
         
        $personeller = "";
        $hizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
        $subeler = Subeler::where('salon_id',$isletme->id)->get();
        
        $islemler = "";
        $gelen_musteri = 0;
        $alinan_odeme = 0;
        $kalan_odeme = 0;
        if(Auth::user()->is_admin){
            $islemler = Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like','%'.$tarih.'%')->orderBy('id','desc')->get();

            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        }
        else{
            $islemler = Islemler::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->where('tarih','like','%'.$tarih.'%')->orderBy('id','desc')->get();
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->get();
        }
        foreach($islemler as $islem){
            $alinan_odeme = $alinan_odeme + $islem->alinan_odeme;
            $kalan_odeme = $kalan_odeme + $islem->kalan_odeme;

        }
        $gelen_musteri = Islemler::where('tarih','like','%'.$tarih.'%')->where('salon_id',Auth::user()->salon_id)->distinct()->pluck('user_id')->count();

        


        $mevcutmusteriler = Randevular::join('users','randevular.user_id','=','users.id')->where('randevular.salon_id',Auth::user()->salon_id)->groupBy('randevular.user_id')->get();
        return view('isletmeadmin.yapilanislemler',[ 'title' =>  'Günlük Genel Raporlar & Yapılan İşlemler | Aleyna Pelit Beauty Studio','pageindex' => 200,'sunulanhizmetler'=>$sunulanhizmetler,'isletme'=>$isletme,'personeller'=> $personeller,  'hizmetler'=> $hizmetler,'mevcutmusteriler'=>$mevcutmusteriler,'subeler'=>$subeler,'gelen_musteri'=>$gelen_musteri,'alinan_odeme'=>$alinan_odeme,'kalan_odeme'=>$kalan_odeme,'islemler'=>$islemler,'tarih'=>$tarih]);


     }
     public function islemraporlari_filtre(Request $request){
        $islemlar = "";
        $gelen_musteri = 0;
        $alinan_odeme = 0;
        $kalan_odeme = 0;
        $personeller = "";
        if(Auth::user()->is_admin){


            if($request->sube != 0){
                $islemlar = Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',$request->sube)->orderBy('id','desc')->get();
                $gelen_musteri =  Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',$request->sube)->distinct()->pluck('user_id')->count();
                $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->where('sube_id',date('Y-m-d', strtotime($request->tarih)))->get();

            }

              
            else{
                $islemlar = Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->orderBy('id','desc')->get();
                $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
                $gelen_musteri =  Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->distinct()->pluck('user_id')->count();
            }
            
        }
        else{
            $islemlar = Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',Auth::user()->salon_personelleri->sube_id)->orderBy('id','desc')->get();
             $gelen_musteri =  Islemler::where('salon_id',Auth::user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',Auth::user()->salon_personelleri->sube_id)->distinct()->pluck('user_id')->count();
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->get();
        }
       
        $islemliste["islemler"] = array();
        $islemliste["total"] =  array();
        foreach($islemlar as $islem){
            $islemliste1['musteri'] = $islem->user->name;
            $islemliste1['tarih'] = date('d.m.Y H:i',strtotime($islem->tarih));
            $islemhtml = '  <button class="btn btn-primary islemdetayigetir" data-value="'.$islem->id.'">    
                               <span class="mdi mdi-edit"></span> Düzenle 
                            </button>';
            $islemliste1['subepersonel'] =  $islem->personeller->sube->sube."şubesi<br>".$islem->personeller->personel_adi;
            $islemliste1['hizmetler'] = $islem->yapilan_islemler;
            $islemliste1['alinanodeme']  = $islem->alinan_odeme;
            $islemliste1['kalanodeme']  = $islem->kalan_odeme;
          
            if($islem->kalan_odeme>0)
                $islemhtml .= '<button class="btn btn-success kalanodemealindi" data-value="'.$islem->id.'">    
                               <span class="mdi mdi-money-box"></span> Kalan Ödeme Alındı 
                            </button>';
            $islemliste1['islemler'] = $islemhtml;   
          
            $alinan_odeme = $alinan_odeme + $islem->alinan_odeme;
            $kalan_odeme = $kalan_odeme + $islem->kalan_odeme;
            array_push($islemliste["islemler"], $islemliste1);



        }
        $islemliste2['gelen_musteri'] =$gelen_musteri;
        $islemliste2['alinan_odeme'] = $alinan_odeme;
        $islemliste2['kalan_odeme'] = $kalan_odeme;
        array_push($islemliste["total"], $islemliste2);
       
        return json_encode($islemliste);


     }
     public function islemdeneme(){
         echo is_numeric("anil");
     }

    public function islemgetir(Request $request){
        $islem = Islemler::where('id',$request->islemid)->first();
        $subeler = Subeler::where('salon_id',Auth::user()->salon_id)->get();
        $musteriler = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get();
        $personeller = "";
        $hizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
        $sube_html = "";
        $musteri_html = "";
        $personel_html = "";
        $hizmet_html = "";

        $islemhizmetler = explode('<br>',$islem->yapilan_islemler);
        if(Auth::user()->is_admin)
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        else
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->get();

        
        foreach($musteriler as $musteri){
            if($musteri->users->id == $islem->user->id)
                $musteri_html .= "<option value='".$musteri->users->id."' selected>".$musteri->users->name."</option>";
            else
                $musteri_html .= "<option value='".$musteri->users->id."'>".$musteri->users->name."</option>";
        }
        foreach($personeller as $personel){
            if($personel->id == $islem->personel_id)
                $personel_html .= "<option value='".$personel->id."' selected>".$personel->personel_adi."</option>";
            else
                $personel_html .= "<option value='".$personel->id."'>".$personel->personel_adi."</option>";

        }
        foreach($subeler as $sube){
            if($sube->id == $islem->sube_id)
                $sube_html .= "<option selected value='".$sube->id."'>".$sube->sube."</option>";
            else
                $sube_html .= "<option value='".$sube->id."'>".$sube->sube."</option>";
        }
        foreach($hizmetler as $hizmet){
            if(in_array($hizmet->hizmetler->hizmet_adi,$islemhizmetler))
                $hizmet_html .= "<option selected value='".$hizmet->hizmet_id."'>".$hizmet->hizmetler->hizmet_adi."</option>";
            else
                $hizmet_html .= "<option value='".$hizmet->hizmet_id."'>".$hizmet->hizmetler->hizmet_adi."</option>";
                    
           

           
        }
        $info['islem_id'] = $islem->id;
        $info['hizmet_kategori_id'] = $islem->hizmet_kategori_id;

        $info['tarih'] = date('Y-m-d',strtotime($islem->tarih));
        $info['seans_no'] = $islem->seans_no;

        //$info['musteri'] = $musteri_html;
        //$info['gsm'] = $islem->user->cep_telefon;



        $info['personel_id'] = $islem->personel_id;
        $info['yapilanislemler'] =$hizmet_html;

        $info['alinanodeme'] = $islem->alinan_odeme;
        //$info['kalanodeme'] = $islem->kalan_odeme;
        $info['aciklama'] = $islem->aciklama; 
        
      
        $info['subeler'] = $sube_html;

        $info['baslangic_kilo'] = $islem->baslangic_kg;
        $info['baslangic_gogus'] = $islem->baslangic_gogus;
        $info['baslangic_gobek'] = $islem->baslangic_gobek;
        $info['baslangic_kalca'] = $islem->baslangic_kalca;
        $info['baslangic_basen'] = $islem->baslangic_basen;
        $info['baslangic_bel'] = $islem->baslangic_bel;
        $info['baslangic_sirt'] = $islem->baslangic_sirt;
        $info['koltuk_alti'] = $islem->koltuk_alti;
        $info['bacak'] = $islem->bacak;
        $info['kol'] = $islem->kol;
        $info['bikini'] = $islem->bikini;
        $info['yuz'] = $islem->yuz;
        $info['gogus'] = $islem->gogus;
        $info['gobek'] = $islem->gobek;
        $info['sirt'] = $islem->sirt;
        $info['biyik'] = $islem->biyik;
        $info['favori'] = $islem->favori;
        $info['ense'] = $islem->ense;
        $info['ten_rengi'] = $islem->ten_rengi;
        $info['kil_yapisi'] = $islem->kil_yapisi;

        

        
        return $info;
     }


     public function islemkalanodemealindi(Request $request){
          $islem = Islemler::where('id',$request->islemid)->first();
          $islem->alinan_odeme += $islem->kalan_odeme;
          $kalan_odeme = $islem->kalan_odeme;
          $islem->kalan_odeme = 0;
          $islem->save();
          $musteri = User::where('id',$islem->user_id)->first();
          $musteri->odenen += $kalan_odeme;
          $musteri->alacak -= $kalan_odeme;
          $musteri->save();
          echo date('Y-m-d',strtotime($islem->tarih));
     }


    public function avantajraporlar(){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $avantajlar = SalonKampanyalar::where('salon_id',$isletme->id)->get();

        return view ('isletmeadmin.kampanyaraporlar',['title' => 'Avantaj Raporları |randevumcepte.com.tr','pageindex' => 1052,'isletme' => $isletme,'avantajlar' => $avantajlar]);
    }
    public function toplusmsgonder(Request $request){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $portfoy = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->groupBy('user_id')->get();
        $taslaklar = SMSTaslaklari::where('salon_id',Auth::user()->salon_id)->orWhere('salon_id',null)->get();
        $paketler = self::paket_liste_getir("",false);
        return view('isletmeadmin.toplusmsgonder',['paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'title' => 'Toplu SMS Gönder | randevumcepte.com.tr','pageindex' => 106,'isletme'=>$isletme,'portfoy' => $portfoy,'taslaklar'=>$taslaklar]);
    }
    public function smslistesi(){
        $listeler = SMSListeleri::where('user_id',Auth::user()->id)->get();
         $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        return view('isletmeadmin.smslistesi',['title' => 'Toplu SMS Listelerim | randevumcepte.com.tr','pageindex' => 107,'isletme'=>$isletme,'listeler'=> $listeler]);
    }
    public function toplumailgonder(){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        return view('isletmeadmin.toplumailgonder',['title' => 'Toplu Mail Gönder | randevumcepte.com.tr','pageindex' => 110,'isletme'=>$isletme]);
    }
    public function ayarlar(Request $request){
         $id = Auth::user()->salon_id;
        $isletme = Salonlar::where('id',$id)->first();
        $subeler = Subeler::where('salon_id',$id)->get();
        $salonhizmetler = SalonHizmetler::where('salon_id',$id)->get();
        $personeller = Personeller::where('salon_id',$id)->get();
        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id',$id)->orderBy('haftanin_gunu','asc')->get();
        $salonmolasaatleri = SalonMolaSaatleri::where('salon_id',$id)->orderBy('haftanin_gunu','asc')->get();
        $salongorselleri = SalonGorselleri::where('salon_id',$id)->where('kapak_fotografi','!=',1)->get();
        $salongorselkapak = SalonGorselleri::where('salon_id',$id)->where('kapak_fotografi',1)->first();
        $etiketler = AramaTerimleri::where('salon_id',$id)->orderBy('id','asc')->get();
        $isletmeturu = SalonTuru::all();  
        $hizmetler = Hizmetler::all();
        $isletmeturu_html = "";

        $gorseller_html = "";

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
 
         $urunler = self::urun_liste_getir("");
         $paketler = self::paket_liste_getir("",false);
        return view('isletmeadmin.ayarlar',['bildirimler'=>self::bildirimgetir($request),'sayfa_baslik' => 'Hesap Ayarları','pageindex' => 9,'salongorselleri'=> $salongorselleri,'saloncalismasaatleri'=>$saloncalismasaatleri,'personeller' => $personeller, 'salonhizmetler' => $salonhizmetler,'isletme'=> $isletme,'sayfa_baslik' => $isletme->salon_adi.' | Detayları & Düzenle', 'etiketler' => $etiketler,'isletmeturulistesi' => $isletmeturu_html,'gorseller_html' => $gorseller_html,'hizmetlistesi'=>$hizmetlistesi_html,'salongorselkapak'=>$salongorselkapak,'subeler'=>$subeler,'salonmolasaatleri'=>$salonmolasaatleri,'urunler'=> $urunler,'paketler'=>$paketler]);
    }
    public function randevuyukle(Request $request){
        
        $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->pluck('id');
        $salon = Salonlar::where('id',Auth::user()->salon_id)->first();
        $randevu_hizmetler = "";
        $resources = "";
        if($salon->randevu_takvim_turu == 1){
            $randevu_hizmetler = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->join('users','randevular.user_id','=','users.id')->select('randevular.user_id as userid','randevu_hizmetler.id as id',DB::raw('CASE when randevular.user_id = 0 THEN "Kapalı Saat" ELSE users.name END as title'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" ELSE salon_personelleri.renk END as color'),'randevu_hizmetler.personel_id as resourceId')->where('randevular.salon_id',Auth::user()->salon_id)->get();
            $resources = Personeller::where('salon_id',Auth::user()->salon_id)->orWhere('id',183)->get(['id as id','personel_adi as title','renk as bgcolor']);
        }
        else{
            $randevu_hizmetler = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('users','randevular.user_id','=','users.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->select('randevular.user_id as userid','randevu_hizmetler.id as id',DB::raw('CASE when randevular.user_id = 0 THEN "Kapalı Saat" ELSE users.name END as title'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" ELSE salon_personelleri.renk END as color'),'hizmetler.hizmet_kategori_id as resourceId')->where('randevular.salon_id',Auth::user()->salon_id)->get();
            $resources = DB::table('salon_sunulan_hizmetler')->join('hizmet_kategorisi','salon_sunulan_hizmetler.hizmet_kategori_id','=','hizmet_kategorisi.id')->select(['salon_sunulan_hizmetler.hizmet_kategori_id as id','hizmet_kategorisi.hizmet_kategorisi_adi as title','hizmet_kategorisi.renk as bgcolor'])->where('salon_sunulan_hizmetler.salon_id',Auth::user()->salon_id)->groupBy('salon_sunulan_hizmetler.hizmet_kategori_id')->get();
        }

        return array(
                'randevu' => $randevu_hizmetler,
                'resource' => $resources,
                
    
        );
       
        
    }
    public function kasadefteri(Request $request){
            $tarih = date('Y-m-d');
            if($request->kasatarih !== null)
                $tarih = date('Y-m-d',strtotime($request->kasatarih));
            $kasadefterigider = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',0)->where('tarih',$tarih)->get();
                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',1)->where('tarih',$tarih)->get(); 
            $toplamgelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$tarih)->where('gelir_gider',1)->sum('miktar');
            $toplamgider =  KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$tarih)->where('gelir_gider',0)->sum('miktar');
            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$tarih)->where('gelir_gider',1)->sum('miktar');
            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$tarih)->where('gelir_gider',0)->sum('miktar');
            $kasaacilis = $toplamgelir_oncekigunler - $toplamgider_oncekigunler;
            $kasatoplam = $kasaacilis + $toplamgelir- $toplamgider;

            $kasadefterigelirtablosuhtml = "";
            $kasadefterigidertablosuhtml = "";
            foreach($kasadefterigider as $kasadefterigirdi){
                 
                   
                $kasadefterigidertablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

                 
            }
            foreach($kasadefterigelir as $kasadefterigirdi){
                  
                     $kasadefterigelirtablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

            }
            if($kasadefterigider->count() == 0)
                $kasadefterigidertablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>";
            if($kasadefterigelir->count()==0)
                 $kasadefterigelirtablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>";
             
               
             $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();


            
             if($request->kasatarih ===null)
                return view('isletmeadmin.kasadefteri',['title'=> 'Kasa Defteri | '.$isletme->salon_adi.' İşletme Yönetim Paneli',   'pageindex' => 103, 'kasadefterigelir' => $kasadefterigelirtablosuhtml, 'kasadefterigider' => $kasadefterigidertablosuhtml, 'toplamgelir' => $toplamgelir, 'toplamgider' => $toplamgider,'isletme' => $isletme,'kasaacilis' => $kasaacilis, 'kasatoplam'=>$kasatoplam]);
            else
            {
                $result['gider'] = array();
                $result['gelir'] = array();
                $result['toplamgider'] = array();
                $result['toplamgelir'] = array();
                $result['kasaacilis'] = array();
                $result['kasatoplam'] = array();
                array_push($result['gider'], $kasadefterigidertablosuhtml);
                array_push($result['gelir'], $kasadefterigelirtablosuhtml);
                array_push($result['toplamgider'], $toplamgider);
                array_push($result['toplamgelir'], $toplamgelir);
                array_push($result['kasatoplam'], $kasatoplam);
                array_push($result['kasaacilis'], $kasaacilis);
                return $result;

            }
    }


    public function isletme(){
        $id = Auth::user()->salon_id;
        $isletme = Salonlar::where('id',$id)->first();
        $subeler = Subeler::where('salon_id',$id)->get();
        $salonhizmetler = SalonHizmetler::where('salon_id',$id)->get();
        $personeller = Personeller::where('salon_id',$id)->get();
        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id',$id)->orderBy('haftanin_gunu','asc')->get();
        $salongorselleri = SalonGorselleri::where('salon_id',$id)->where('kapak_fotografi','!=',1)->get();
        $salongorselkapak = SalonGorselleri::where('salon_id',$id)->where('kapak_fotografi',1)->first();
        $etiketler = AramaTerimleri::where('salon_id',$id)->orderBy('id','asc')->get();
        $isletmeturu = SalonTuru::all();  
        $hizmetler = Hizmetler::all();
        $isletmeturu_html = "";

        $gorseller_html = "";

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
        $paketler = self::paket_liste_getir('',true);
        return view('isletmeadmin.isletme',['paketler'=>$paketler,'salongorselleri'=> $salongorselleri,'saloncalismasaatleri'=>$saloncalismasaatleri,'personeller' => $personeller, 'salonhizmetler' => $salonhizmetler,'isletme'=> $isletme,'sayfa_baslik' => $isletme->salon_adi.' | Detayları & Düzenle', 'pageindex' => 6,'etiketler' => $etiketler,'isletmeturulistesi' => $isletmeturu_html,'gorseller_html' => $gorseller_html,'hizmetlistesi'=>$hizmetlistesi_html,'salongorselkapak'=>$salongorselkapak,'subeler'=>$subeler]);
    }
    public function giderekle(Request $request){
         $giderkayit = new KasaDefteri();
         $giderkayit->gelir_gider = 0;
         $giderkayit->tarih = $request->gider_tarih;
         $giderkayit->aciklama = $request->gider_aciklama;
         $giderkayit->miktar = $request->gider_miktar;
         $giderkayit->salon_id = Auth::user()->salon_id;
         $giderkayit->save();
         $kasadefterigider = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',0)->where('tarih',$request->gider_tarih)->get();
                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',1)->where('tarih',$request->gider_tarih)->get(); 
            $toplamgelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$request->gider_tarih)->where('gelir_gider',1)->sum('miktar');
            $toplamgider =  KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$request->gider_tarih)->where('gelir_gider',0)->sum('miktar');
            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$request->gider_tarih)->where('gelir_gider',1)->sum('miktar');
            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$request->gider_tarih)->where('gelir_gider',0)->sum('miktar');
            $kasaacilis = $toplamgelir_oncekigunler - $toplamgider_oncekigunler;
            $kasatoplam = $kasaacilis + $toplamgelir- $toplamgider;

            $kasadefterigelirtablosuhtml = "";
            $kasadefterigidertablosuhtml = "";
            foreach($kasadefterigider as $kasadefterigirdi){
                 
                   
                $kasadefterigidertablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

                 
            }
            foreach($kasadefterigelir as $kasadefterigirdi){
                  
                     $kasadefterigelirtablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

            }
            if($kasadefterigider->count() == 0)
                $kasadefterigidertablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>";
            if($kasadefterigelir->count()==0)
                 $kasadefterigelirtablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>"; 
            
             $result['gider'] = array();
              $result['gelir'] = array();
                $result['toplamgider'] = array();
                $result['toplamgelir'] = array();
                $result['kasaacilis'] = array();
                $result['kasatoplam'] = array();
                array_push($result['gider'], $kasadefterigidertablosuhtml);
                array_push($result['gelir'], $kasadefterigelirtablosuhtml);
                array_push($result['toplamgider'], $toplamgider);
                array_push($result['toplamgelir'], $toplamgelir);
                array_push($result['kasatoplam'], $kasatoplam);
                array_push($result['kasaacilis'], $kasaacilis);
                return $result;

           
          
 


    }
    public function gelirekle(Request $request){
         $giderkayit = new KasaDefteri();
         $giderkayit->gelir_gider = 1;
         $giderkayit->tarih = $request->gelir_tarih;
         $giderkayit->aciklama = $request->gelir_aciklama;
         $giderkayit->miktar = $request->gelir_miktar;
         $giderkayit->salon_id = Auth::user()->salon_id;
         $giderkayit->save();

          $kasadefterigider = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',0)->where('tarih',$request->gelir_tarih)->get();
                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',1)->where('tarih',$request->gelir_tarih)->get(); 
            $toplamgelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$request->gelir_tarih)->where('gelir_gider',1)->sum('miktar');
            $toplamgider =  KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$request->gelir_tarih)->where('gelir_gider',0)->sum('miktar');
            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$request->gelir_tarih)->where('gelir_gider',1)->sum('miktar');
            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$request->gelir_tarih)->where('gelir_gider',0)->sum('miktar');
            $kasaacilis = $toplamgelir_oncekigunler - $toplamgider_oncekigunler;
            $kasatoplam = $kasaacilis + $toplamgelir- $toplamgider;

            $kasadefterigelirtablosuhtml = "";
            $kasadefterigidertablosuhtml = "";
            foreach($kasadefterigider as $kasadefterigirdi){
                 
                   
                $kasadefterigidertablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

                 
            }
            foreach($kasadefterigelir as $kasadefterigirdi){
                  
                     $kasadefterigelirtablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

            }
            if($kasadefterigider->count() == 0)
                $kasadefterigidertablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>";
            if($kasadefterigelir->count()==0)
                 $kasadefterigelirtablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>"; 
            
             $result['gider'] = array();
              $result['gelir'] = array();
                $result['toplamgider'] = array();
                $result['toplamgelir'] = array();
                $result['kasaacilis'] = array();
                $result['kasatoplam'] = array();
                array_push($result['gider'], $kasadefterigidertablosuhtml);
                array_push($result['gelir'], $kasadefterigelirtablosuhtml);
                array_push($result['toplamgider'], $toplamgider);
                array_push($result['toplamgelir'], $toplamgelir);
                array_push($result['kasatoplam'], $kasatoplam);
                array_push($result['kasaacilis'], $kasaacilis);
                return $result;


    }
    public function kasadefterigirdisil(Request $request){
        $kasadefteri = KasaDefteri::where('id',$request->girdi_id)->first();
        $girditarihi = $kasadefteri->tarih;
        $kasadefteri->delete();

          $kasadefterigider = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',0)->where('tarih',$girditarihi)->get();
                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',1)->where('tarih',$girditarihi)->get(); 
            $toplamgelir = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$girditarihi)->where('gelir_gider',1)->sum('miktar');
            $toplamgider =  KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih',$girditarihi)->where('gelir_gider',0)->sum('miktar');
            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$girditarihi)->where('gelir_gider',1)->sum('miktar');
            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('tarih','<',$girditarihi)->where('gelir_gider',0)->sum('miktar');
            $kasaacilis = $toplamgelir_oncekigunler - $toplamgider_oncekigunler;
            $kasatoplam = $kasaacilis + $toplamgelir- $toplamgider;

            $kasadefterigelirtablosuhtml = "";
            $kasadefterigidertablosuhtml = "";
            foreach($kasadefterigider as $kasadefterigirdi){
                 
                   
                $kasadefterigidertablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

                 
            }
            foreach($kasadefterigelir as $kasadefterigirdi){
                  
                     $kasadefterigelirtablosuhtml .= "<tr><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

            }
            if($kasadefterigider->count() == 0)
                $kasadefterigidertablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>";
            if($kasadefterigelir->count()==0)
                 $kasadefterigelirtablosuhtml .= "<tr><td colspan='4' style='color:red;font-weight:bold;text-align:center'>Kayıt bulunamadı</td></tr>"; 
            
             $result['gider'] = array();
              $result['gelir'] = array();
                $result['toplamgider'] = array();
                $result['toplamgelir'] = array();
                $result['kasaacilis'] = array();
                $result['kasatoplam'] = array();
                array_push($result['gider'], $kasadefterigidertablosuhtml);
                array_push($result['gelir'], $kasadefterigelirtablosuhtml);
                array_push($result['toplamgider'], $toplamgider);
                array_push($result['toplamgelir'], $toplamgelir);
                array_push($result['kasatoplam'], $kasatoplam);
                array_push($result['kasaacilis'], $kasaacilis);
                return $result;




    }
    public function kasadefterifiltre(Request $request){
         
         
        $kasadefteri = KasaDefteri::where('salon_id',Auth::user()->salon_id)->where('gelir_gider',$request->gelir_gider)->where('tarih', $request->girdi_tarih)->get();
        $kasadefterihtml = "";
        foreach($kasadefteri as $kasadefterigirdi) {
            $kasadefterihtml .= "<tr><td>";
              if($kasadefterigirdi->tarih == date('Y-m-d'))
                 $kasadefterihtml .= "Bugün";
              else 
                  $kasadefterihtml .= date('d.m.Y',strtotime($kasadefterigirdi->tarih));
              $kasadefterihtml .= "</td><td>".$kasadefterigirdi->miktar."</td><td>".$kasadefterigirdi->aciklama."</td><td class='actions'><a href='#' name='kasadefterigirdisil' data-value='".$kasadefterigirdi->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
             
        }
         if($kasadefteri->count() == 0)
                $kasadefterihtml .= "<tr><td colspan='4' style='color:red'>Kayıt bulunamadı</td></tr>";
          
        echo $kasadefterihtml;
         
    }
    public function calismasaatiguncelle(Request $request){
        try{

           
          
                $calismasaati = SalonCalismaSaatleri::where('salon_id',Auth::user()->salon_id)->get();
                 
                foreach ($calismasaati as $key => $value) {
                    if($key == 0){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor1;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic1;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis1;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 1){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor2;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic2;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis2;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 2){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor3;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic3;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis3;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 3){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor4;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic4;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis4;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 4){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor5;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic5;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis5;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 5){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor6;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic6;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis6;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 6){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor7;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic7;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis7;
                          $calismasaatiherbiri->save();
                    }

                     
                } 
          
                echo 'Çalışma saatleri başarı ile güncellendi';

        }
        catch(Exception $e){
            echo $e->getMessage();
        }
    }
    public function personeller(){
        $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $subeler  =Subeler::where('salon_id',Auth::user()->salon_id)->get();
        $personeltablohtml = "";
        /*foreach ($personeller as $key => $value) {
          if(Auth::user()->personel_id != $value->id){
                 $personeltablohtml .= '<tr>';
            if($value->profil_resmi == null || $value->profil_resmi =='')
                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';
            else
                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'"></td>';
           $personeltablohtml .= '<td><input type="hidden" id="personeladi'.$value->id.'" value="'.$value->personel_adi.'">'.$value->personel_adi.'</td>';
            if($value->cinsiyet == 0)
                $personeltablohtml .= '<td>Bayan</td>';
            else if($value->cinsiyet==1)
                $personeltablohtml .= '<td>Bay</td>';
            else
                $personeltablohtml .= '<td></td>';
            $personeltablohtml .= '<td>'.$value->unvan.'</td>';
            $personeltablohtml .= '<td>'.RandevuHizmetler::where('personel_id',$value->id)->groupBy('randevu_id')->count().'</td><td>';
            if(\App\IsletmeYetkilileri::where('personel_id',$value->id)->count()==0)
                   $personeltablohtml .= '<button name="kullaniciolustur" data-value='.$value->id.' class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-face"></span> Yetki Oluştur</button>';
            else
                $personeltablohtml .= '<button name="kullaniciyetkikaldir" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Yetki Kaldır</button><button name="personelyetkiduzenle" data-value="'.$value->id.'" class="btn btn-space btn-warning active btn-xs"><span class="icon mdi mdi-settings"></span> Yetki Düzenle</button>';

          
             $personeltablohtml .= '
                <a href="/isletmeyonetim/personeldetay/'.$value->id.'" class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-settings"></span>  Detaylar</button>
            </td></tr>';
          }
          

        }*/
        $paketler = self::paket_liste_getir('',true);
        return view ('isletmeadmin.personeller',['paketler'=>$paketler,'pageindex' =>5 , 'sayfa_baslik' => 'Personeller','isletme'=>$isletme,'personeller'=>$personeller,'subeler'=>$subeler]);
    }

    public function personelyetkiolustur(Request $request){
        $sonuc = "";
        $result['sonuc'] = array();
          $result['liste'] = array();
        $yetkilendirilecekpersonel = Personeller::where('id',$request->yetkili_personelid)->first();

        if($request->eposta_yeni!=''|| $request->ceptelefon_yeni!='' || $request->sifre_yeni!=''){
             if($request->ceptelefon_yeni != ''){
                 if(!is_numeric($request->ceptelefon_yeni)||strlen($request->ceptelefon_yeni) != 10 || substr($request->ceptelefon_yeni, 0, 1) == '0'){
                    $sonuc = 'Yetkilendirme yapılamadı : Lütfen yetkilendirme için geçerli bir cep telefon numarası giriniz!';
                    
                    $eklenebilir = 0;
                }
                else{
                    $yetkilieski = IsletmeYetkilileri::where('gsm1',$request->ceptelefon_yeni)->orwhere('gsm2',$request->ceptelefon_yeni)->first();
                    if($yetkilieski){
                          $sonuc = "Yetkilendirme yapılamadı : Girilen telefon numarası sistemde mevcuttur. Yetkilendirme için lütfen başka bir telefon numarası veya e-posta giriniz!";
                        $eklenebilir = 0;
                    }
                      
                     else
                        $eklenebilir = 1;
                }
                 
             }
             else{
                 
                 $yetkilieski = IsletmeYetkilileri::where('email',$request->eposta_yeni)->first();
                 if($yetkilieski){
                    $sonuc = "Yetkilendirme yapılamadı : Girilen e-posta sistemde mevcuttur. Yetkilendirme için lütfen başka bir e-posta adresi giriniz!";
                     $eklenebilir = 0;
                 }
                 else{
                    $eklenebilir = 1;
                   
                 }
              
             }
            
         }
         if($eklenebilir == 1){
              $yetkili = new IsletmeYetkilileri();
              $yetkili->email = $request->eposta_yeni;
              $yetkili->name = $yetkilendirilecekpersonel->personel_adi;
              $yetkili->gsm1 = $request->ceptelefon_yeni;
              $yetkili->password = Hash::make($request->sifre_yeni);
              $yetkili->is_admin = $request->sistemyetki_yeni2;
              $yetkili->personel_id = $request->yetkili_personelid;
              $yetkili->salon_id = Auth::user()->salon_id;
              $yetkili->save();
              $sonuc = $yetkilendirilecekpersonel->personel_adi. ' adlı personel için yetkilendirme başarı ile oluşturuldu';

         }
         $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
      
        $personeltablohtml = "";
        foreach ($personeller as $key => $value) {
          if(Auth::user()->personel_id != $value->id){
                 $personeltablohtml .= '<tr>';
            if($value->profil_resmi == null || $value->profil_resmi =='')
                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';
            else
                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'"></td>';
           $personeltablohtml .= '<td><input type="hidden" id="personeladi'.$value->id.'" value="'.$value->personel_adi.'">'.$value->personel_adi.'</td>';
            if($value->cinsiyet == 0)
                $personeltablohtml .= '<td>Bayan</td>';
            else if($value->cinsiyet==1)
                $personeltablohtml .= '<td>Bay</td>';
            else
                $personeltablohtml .= '<td></td>';
            $personeltablohtml .= '<td>'.$value->unvan.'</td>';
            $personeltablohtml .= '<td>'.RandevuHizmetler::where('personel_id',$value->id)->groupBy('randevu_id')->count().'</td><td>';
            if(\App\IsletmeYetkilileri::where('personel_id',$value->id)->count()==0)
                   $personeltablohtml .= '<button name="kullaniciolustur" data-value='.$value->id.' class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-face"></span>  Yetki Oluştur</button>';
            else
               $personeltablohtml .= '<button name="kullaniciyetkikaldir" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Yetki Kaldır</button><button name="personelyetkiduzenle" data-value="'.$value->id.'" class="btn btn-space btn-default active btn-xs"><span class="icon mdi mdi-settings"></span> Yetki Düzenle</button>';

          
             $personeltablohtml .= '<button name="personelsil" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Personeli Sil</button>
                <a href="/isletmeyonetim/personeldetay/'.$value->id.'" class="btn btn-space btn-primary btn-xs"><span class="icon mdi mdi-settings"></span>  Detaylar</button>
            </td></tr>';
          }
          

        }
        array_push($result['sonuc'], $sonuc);
        array_push($result['liste'], $personeltablohtml);
        return $result;

    }
    public function personelbilgikaldir(Request $request){
         $yetki = IsletmeYetkilileri::where('personel_id',$request->personelid)->first();
         if($yetki->count() !=0)
            $yetki->delete();
         $personel = Personeller::where('id',$request->personelid)->first();
         $personeladi = $personel->personel_adi;
         $personel->delete();
         $result['sonuc'] = array();
         $result['liste'] = array();
         array_push($result['sonuc'], $personeladi.' isimli personel başarı ile kaldırıldı');
          $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
      
        $personeltablohtml = "";
        foreach ($personeller as $key => $value) {
          if(Auth::user()->personel_id != $value->id){
                 $personeltablohtml .= '<tr>';
            if($value->profil_resmi == null || $value->profil_resmi =='')
                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';
            else
                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'"></td>';
           $personeltablohtml .= '<td><input type="hidden" id="personeladi'.$value->id.'" value="'.$value->personel_adi.'">'.$value->personel_adi.'</td>';
            if($value->cinsiyet == 0)
                $personeltablohtml .= '<td>Bayan</td>';
            else if($value->cinsiyet==1)
                $personeltablohtml .= '<td>Bay</td>';
            else
                $personeltablohtml .= '<td></td>';
            $personeltablohtml .= '<td>'.$value->unvan.'</td>';
            $personeltablohtml .= '<td>'.RandevuHizmetler::where('personel_id',$value->id)->groupBy('randevu_id')->count().'</td><td>';
            if(\App\IsletmeYetkilileri::where('personel_id',$value->id)->count()==0)
                   $personeltablohtml .= '<button name="kullaniciolustur" data-value='.$value->id.' class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-face"></span>  Yetki Oluştur</button>';
            else
                  $personeltablohtml .= '<button name="kullaniciyetkikaldir" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Yetki Kaldır</button><button name="personelyetkiduzenle" data-value="'.$value->id.'" class="btn btn-space btn-default active btn-xs"><span class="icon mdi mdi-settings"></span> Yetki Düzenle</button>';

          
             $personeltablohtml .= '<button name="personelsil" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Personeli Sil</button>
                <a href="/isletmeyonetim/personeldetay/'.$value->id.'" class="btn btn-space btn-primary btn-xs"><span class="icon mdi mdi-settings"></span>  Detaylar</button>
            </td></tr>';
          }
          

        }
        
        array_push($result['liste'], $personeltablohtml);
        return $result;

    }
    public function yenipersonelbilgiekle(Request $request){
         $result['sonuc'] = array();

         $result['liste'] = array();
         $personel = new Personeller();
         $personel->personel_adi = $request->personeladi_yeni;
         $personel->unvan = $request->unvan_yeni;
         $personel->sube_id = $request->personel_sube;
         $personel->salon_id = Auth::user()->salon_id;
         $personel->cinsiyet = $request->cinsiyet_yeni;
          if (isset($_FILES["profilresmi_yeni"]["name"])) {
                        $dosya  = $request->profil_resim;
                        $kaynak = $_FILES["profilresmi_yeni"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["profilresmi_yeni"]["name"]);
                        $uzanti = explode(".", $_FILES["profilresmi_yeni"]["name"]);
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
         $sonuc = 'Personel bilgisi başarı ile eklendi';
         $eklenebilir =0 ;
         if($request->eposta_yeni!=''|| $request->ceptelefon_yeni!='' || $request->sifre_yeni!=''){
             if($request->ceptelefon_yeni != ''){
                 if(!is_numeric($request->ceptelefon_yeni)||strlen($request->ceptelefon_yeni) != 10 || substr($request->ceptelefon_yeni, 0, 1) == '0'){
                    $sonuc .= ' fakat yetkilendirme yapılamadı : Lütfen yetkilendirme için geçerli bir cep telefon numarası giriniz!';
                    
                    $eklenebilir = 0;
                }
                else{
                    $yetkilieski = IsletmeYetkilileri::where('gsm1',$request->ceptelefon_yeni)->orwhere('gsm2',$request->ceptelefon_yeni)->first();
                    if($yetkilieski){
                          $sonuc .= " fakat yetkilendirme yapılamadı : Girilen telefon numarası sistemde mevcuttur. Yetkilendirme için lütfen başka bir telefon numarası veya e-posta giriniz!";
                        $eklenebilir = 0;
                    }
                      
                     else
                        $eklenebilir = 1;
                }
                 
             }
             else{
                 
                 $yetkilieski = IsletmeYetkilileri::where('email',$request->eposta_yeni)->first();
                 if($yetkilieski){
                    $sonuc .= " fakat yetkilendirme yapılamadı : Girilen e-posta sistemde mevcuttur. Yetkilendirme için lütfen başka bir e-posta adresi giriniz!";
                     $eklenebilir = 0;
                 }
                 else{
                    $eklenebilir = 1;
                   
                 }
              
             }
            
         }
         if($eklenebilir == 1){
              $yetkili = new IsletmeYetkilileri();
              $yetkili->email = $request->eposta_yeni;
              $yetkili->name = $request->personeladi_yeni;
              $yetkili->gsm1 = $request->ceptelefon_yeni;
              $yetkili->password = Hash::make($request->sifre_yeni);
              $yetkili->is_admin = $request->sistemyetki_yeni;
              $yetkili->personel_id = $personel->id;
              $yetkili->salon_id = Auth::user()->salon_id;
              $yetkili->save();
              $sonuc .= ' ve yetkilendirme başarı ile oluşturuldu';

         }

         for($i=1;$i<=7;$i++){
            
            $personelcalismasaatleri = new PersonelCalismaSaatleri();
            $personelcalismasaatleri->haftanin_gunu = $i;
            $personelcalismasaatleri->personel_id = $personel->id;
            if(isset($_POST['calisiyor'.$i])){

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
          if($request->sunulanhizmetler_yeni!= null){

              foreach ($request->sunulanhizmetler_yeni as $key => $value) {
                   $personelhizmetler = new PersonelHizmetler();
                   $personelhizmetler->personel_id = $personel->id;
                   $personelhizmetler->hizmet_id = $value;
                   $personelhizmetler->save();
              }
          }
         $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
      
        $personeltablohtml = "";
        foreach ($personeller as $key => $value) {
          if(Auth::user()->personel_id != $value->id){
                 $personeltablohtml .= '<tr>';
            if($value->profil_resmi == null || $value->profil_resmi =='')
                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';
            else
                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'"></td>';
           $personeltablohtml .= '<td><input type="hidden" id="personeladi'.$value->id.'" value="'.$value->personel_adi.'">'.$value->personel_adi.'</td>';
            if($value->cinsiyet == 0)
                $personeltablohtml .= '<td>Bayan</td>';
            else if($value->cinsiyet==1)
                $personeltablohtml .= '<td>Bay</td>';
            else
                $personeltablohtml .= '<td></td>';
            $personeltablohtml .= '<td>'.$value->unvan.'</td>';
            $personeltablohtml .= '<td>'.RandevuHizmetler::where('personel_id',$value->id)->groupBy('randevu_id')->count().'</td><td>';
            if(\App\IsletmeYetkilileri::where('personel_id',$value->id)->count()==0)
                   $personeltablohtml .= '<button name="kullaniciolustur" data-value='.$value->id.' class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-face"></span>  Yetki Oluştur</button>';
            else
                  $personeltablohtml .= '<button name="kullaniciyetkikaldir" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Yetki Kaldır</button><button name="personelyetkiduzenle" data-value="'.$value->id.'" class="btn btn-space btn-default active btn-xs"><span class="icon mdi mdi-settings"></span> Yetki Düzenle</button>';

          
             $personeltablohtml .= '<button name="personelsil" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Personeli Sil</button>
                <a href="/isletmeyonetim/personeldetay/'.$value->id.'" class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-settings"></span>  Detaylar</button>
            </td></tr>';
          }
          

        }
        array_push($result['sonuc'], $sonuc);
        array_push($result['liste'], $personeltablohtml);
        return $result;







    }
    public function personelsistemyetkikaldir(Request $request){
         $yetkili = IsletmeYetkilileri::where('personel_id',$request->personelid)->first();
         $yetkili->delete();
         $personel = Personeller::where('id',$request->personelid)->value('personel_adi');
         $sonuc = $personel. ' isimli personelin sistem yetkileri başarı ile kaldırıldı!';
         $result['sonuc'] = array();
         $result['liste'] = array();
         $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
      
        $personeltablohtml = "";
        foreach ($personeller as $key => $value) {
          if(Auth::user()->personel_id != $value->id){
                 $personeltablohtml .= '<tr>';
            if($value->profil_resmi == null || $value->profil_resmi =='')
                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';
            else
                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$value->profil_resmi.'"></td>';
            $personeltablohtml .= '<td><input type="hidden" id="personeladi'.$value->id.'" value="'.$value->personel_adi.'">'.$value->personel_adi.'</td>';
            if($value->cinsiyet == 0)
                $personeltablohtml .= '<td>Bayan</td>';
            else if($value->cinsiyet==1)
                $personeltablohtml .= '<td>Bay</td>';
            else
                $personeltablohtml .= '<td></td>';
            $personeltablohtml .= '<td>'.$value->unvan.'</td>';
            $personeltablohtml .= '<td>'.RandevuHizmetler::where('personel_id',$value->id)->groupBy('randevu_id')->count().'</td><td>';
            if(\App\IsletmeYetkilileri::where('personel_id',$value->id)->count()==0)
                   $personeltablohtml .= '<button name="kullaniciolustur" data-value='.$value->id.' class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-face"></span>  Yetki Oluştur</button>';
            else
                $personeltablohtml .= '<button name="kullaniciyetkikaldir" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Yetki Kaldır</button><button name="personelyetkiduzenle" data-value="'.$value->id.'" class="btn btn-space btn-default active btn-xs"><span class="icon mdi mdi-settings"></span> Yetki Düzenle</button>';

          
             $personeltablohtml .= '<button name="personelsil" data-value='.$value->id.' class="btn btn-space btn-danger btn-xs"><span class="icon mdi mdi-delete"></span> Personeli Sil</button>
                <a href="/isletmeyonetim/personeldetay/'.$value->id.'" class="btn btn-space btn-success btn-xs"><span class="icon mdi mdi-settings"></span>  Detaylar</button>
            </td></tr>';
          }
          

        }
        array_push($result['sonuc'], $sonuc);
        array_push($result['liste'], $personeltablohtml);
        return $result;

    }
      public function personeldetay($id){
        $personel = Personeller::where('id',$id)->orderBy('id','desc')->first();
         $personelhizmetler = PersonelHizmetler::where('personel_id',$id)->orderBy('id','desc')->get();
         $personelcalismasaatleri = PersonelCalismaSaatleri::where('personel_id',$id)->get();
         $subeler = Subeler::where('salon_id',$personel->salon_id)->get();
         $personelhizmetleri_html = "";
          foreach($personelhizmetler as $personelhizmet){
                $personelhizmetleri_html .="<tr><td>".$personelhizmet->hizmetler->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
          }
                   $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        return view('isletmeadmin.personeldetay',['subeler'=>$subeler,'personelsunulanhizmetler'=>$personelhizmetleri_html,'personelcalismasaatleri'=>$personelcalismasaatleri,'personel' => $personel , 'title' => $personel->personel_adi.' | Detaylar & Düzenle | randevumcepte.com.tr', 'pageindex' => 105,'isletme' => $isletme]);
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
                $personelhizmetleri_html .="<tr><td>".$personelhizmet->hizmetler->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
          }
          echo $personelhizmetleri_html;
        
    }
    public function personelhizmetara(Request $request,$id){

        $hizmetler = Hizmetler::where('hizmet_adi', 'like', '%'.$request->hizmet.'%')->get();
        $personelhizmetleri_html = "";

        foreach($hizmetler as $hizmetliste){
            $personelhizmet =PersonelHizmetler::where('hizmet_id',$hizmetliste->id)->where('personel_id',$id)->first();
             if($personelhizmet)
            {
                 $personelhizmetleri_html .="<tr><td>".$hizmetliste->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";
            }
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
    public function personelekle(Request $request){
        $personel = new Personeller();
        $personel->salon_id = Auth::user()->salon_id;
        $personel->personel_adi = $request->personeladi;
        $personel->save();
        return redirect('/isletmeyonetim/isletmem');




    }
    public function personelsil(Request $request){
        $personel = Personeller::where('id',$request->personelno)->first();
        $personel->delete();
         return redirect('/isletmeyonetim/isletmem');

    }
    public function randevuguncelle(Request $request){
        $randevu = Randevular::where('id',$request->randevu_id)->first();
        $randevu->tarih = $request->randevu_tarihi;
        $randevu->saat = $request->randevu_saati;
        $randevu->notlar =  $request->notlar;
        $randevu->personel_notu = $request->personel_notu;
        $randevu->randevuya_geldi = $request->randevuya_geldi_gelmedi;

        $totalsure = 0;
           
           
           
             
             
        $yenisaatbaslangic = $request->randevu_saati;
        foreach ($request->randevuhizmetidleri as $key => $value) {
                $totalsure += $request->hizmet_suresi_adisyon[$key];
                $randevuhizmet = RandevuHizmetler::where('id',$value)->first();

                
                
                $randevuhizmet->hizmet_id = $request->randevuhizmetleri[$key];
                $randevuhizmet->personel_id = $request->randevupersonelleri[$key];
                $randevuhizmet->sure_dk = $request->hizmet_suresi_adisyon[$key];
                $randevuhizmet->fiyat = $request->hizmet_fiyati_adisyon[$key];
                if($key == 0){
                     $randevuhizmet->saat = $request->randevu_saati;
                     $randevuhizmet->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi_adisyon[$key].' minutes', strtotime($request->saat)));
                     $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi_adisyon[$key].' minutes', strtotime($request->saat)));
                }
                   
                 
                else{

                    $randevuhizmet->saat = $yenisaatbaslangic;
                    $randevuhizmet->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));
                    $randevuhizmet = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));
                }
                $randevuhizmet->save();

               
        }

        $randevu->save();
        self::hareket_ekle($request,'Düzenlendi');
        

        return 'Randevu bilgileri başarıyla kaydedildi';


        
          

         
    }
    public function randevuiptalet(Request $request){
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $randevu->durum = 2;
        $randevu->save();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
         

       
      
        
       $mesaj = $isletme->salon_adi." ". date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($randevu->saat)) ." için randevunuz ". $randevu->sube->sube." şubesi tarafından iptal edilmiştir.";
       $musteri = User::where('id', $randevu->user_id)->first();
       $gsm = $musteri->cep_telefon;
       
		
	   
		



        $postUrl = "http://api.efetech.net.tr/v2/sms/basic";
		$apiKey = $isletme->sms_apikey;
		$headers = array(
			 'Authorization: Key '.$apiKey,
			 'Content-Type: application/json',
			 'Accept: application/json'
		);
      	$postData = json_encode( array( "originator"=> $isletme->sms_baslik, "message"=> $mesaj, "to"=>$gsm,"encoding"=>"auto") );

   			  $ch=curl_init();
              curl_setopt($ch,CURLOPT_URL,$postUrl);
              curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
              curl_setopt($ch,CURLOPT_POST,1);
              curl_setopt($ch,CURLOPT_TIMEOUT,5);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
              curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
				
              $response=curl_exec($ch);
                   
     
            
    }
     public function randevuonayla(Request $request){
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $randevu->durum = 1;
        $randevu->save();

        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
      
        
       $mesaj = $isletme->salon_adi." ".date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($randevu->saat)) ." için ". $randevu->sube->sube." şubesi randevunuz onaylanmıştır.";
       $musteri = User::where('id', $randevu->user_id)->first();
       $gsm = $musteri->cep_telefon;

             $postUrl = "http://api.efetech.net.tr/v2/sms/basic";
		$apiKey = $isletme->sms_apikey;
		$headers = array(
			 'Authorization: Key '.$apiKey,
			 'Content-Type: application/json',
			 'Accept: application/json'
		);
      	$postData = json_encode( array( "originator"=> $isletme->sms_baslik, "message"=> $mesaj, "to"=>$gsm,"encoding"=>"auto") );

   			  $ch=curl_init();
              curl_setopt($ch,CURLOPT_URL,$postUrl);
              curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
              curl_setopt($ch,CURLOPT_POST,1);
              curl_setopt($ch,CURLOPT_TIMEOUT,5);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
              curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
				
              $response=curl_exec($ch);
                   
     }
    public function randevubilgiguncelle(Request $request){
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $randevu->tarih = $request->randevutarihi;
        $randevu->saat = $request->randevusaati;
        $randevu->saat_bitis = $request->randevusaatibitis;
        $musteriid = $randevu->user_id;
        $randevu->save();
         $randevueklenen = Randevular::where('id',$randevu->id)->first();
          
                $randevuguncel['newevent']['id'] =  $randevueklenen->id;
                $randevuguncel['newevent']['title'] = User::where('id',$musteriid)->value('name');
                $randevuguncel['newevent']['start'] = $randevueklenen->tarih .' '.$randevueklenen->saat;
                $randevuguncel['newevent']['end'] = $randevueklenen->tarih .' '.$randevueklenen->saat_bitis;
                if($randevueklenen->durum == 2)
                    $randevuguncel['newevent']['color'] = "#FF0000";
                else if($randevueklenen->durum == 1)
                    $randevuguncel['newevent']['color'] = "#34a853";
                else if($randevueklenen->durum ==0 || $randevueklenen->durum=='' ||$randevueklenen->durum == null)
                   $randevuguncel['newevent']['color'] = "#FF4E00";
               $randevuguncel['newevent']['eposta'] = User::where('id',$musteriid)->value('email');
               $randevuguncel['newevent']['telefoncep'] = User::where('id',$musteriid)->value('cep_telefon');
               
               $randevuguncel['newevent']['telefonev'] = User::where('id',$musteriid)->value('ev_telefon');
                $randevuhizmetler = RandevuHizmetler::where('randevu_id',$randevueklenen->id)->get();
               foreach ($randevuhizmetler as $key2 => $value2) {
                    $randevuguncel['newevent']['hizmet'][$key2] = Hizmetler::where('id',$value2->hizmet_id)->value('hizmet_adi');
                    $randevuguncel['newevent']['personel'][$key2] = Personeller::where('id',$value2->personel_id)->value('personel_adi');
                    $randevuguncel['newevent']['hizmetid'][$key2] = Hizmetler::where('id',$value2->hizmet_id)->value('id');
                    $randevuguncel['newevent']['personelid'][$key2] = Personeller::where('id',$value2->personel_id)->value('id');
               }
               $randevuguncel['newevent']['data-modal'] = "md-scale";

          
          $randevuguncel['success'] = 'Randevu başarı ile güncellendi';
        

         

           return json_encode($randevuguncel);
    }
    public function sifredegistir(Request $request){
        $user = Auth::user()->first();
        $cevap = "";
       
            $user->password = Hash::make($request->yenisifre);
            $user->save();
            $cevap = "Şifreniz başarı ile değiştirildi";

       
         echo $cevap;
        
    }
    public function yetkilibilgiguncelle(Request $request){
        $user = User::where('id',Auth::user()->id)->first();
        $user->name = $request->adsoyad;
        $user->email = $request->eposta;
        $user->telefon = $request->telefon;
        $user->gsm1 = $request->gsm1;
        $user->gsm2 = $request->gsm2;
        $user->save();
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
       
        $hizmetlistesi_html = "";

         
        $hizmetlistesi_html.="<option value='".$yenihizmet->id."'>".$yenihizmet->hizmet_adi."</option>";
         
        echo $hizmetlistesi_html;


    }
    public function yenisubeekle(Request $request){
        $sube = new Subeler();
        $sube->sube = $request->subeadi;
        $sube->adres = $request->subeadres;
        $sube->sube_tel = $request->subetel;

        $sube->salon_id = Auth::user()->salon_id;
        $sube->aktif = true;
        $sube->save();
        $sube_html = "<tr name='subesatir' data-value='".$sube->id."'>";
        $sube_html .= "<td>".$sube->sube."</td>";
        $sube_html .= "<td>".$sube->adres."</td>";
        $sube_html .= "<td>".$sube->sube_tel."</td>";
        $sube_html .= "<td> <a title='Şube Pasif Et' name='subepasifet' style='font-size: 20px;cursor: pointer;' data-value='".$sube->id."' class='icon'> ";
        $sube_html .=  '<div class="icon"><span class="mdi mdi-minus-circle"></span></div><span class="icon-class"></span>
                                            </div>


                                      </a></td></tr>';
        echo $sube_html;





    }
     public function subepasifet(Request $request){
        $sube = Subeler::where('id',$request->subeid)->first();
        $sube->aktif = false;
        $sube->save();
        echo  $sube->id;

        
    }
    public function subeaktifet(Request $request){
        $sube = Subeler::where('id',$request->subeid)->first();
        $sube->aktif = true;
        $sube->save();
        echo  $sube->id;


    }
      public function yenipersonelgir(Request $request){
        $personel = new Personeller();
        $personel->personel_adi = $request->personeladi_yeni;
        $personel->cinsiyet = $request->personelcinsiyet_yeni;
        $personel->salon_id = Auth::user()->salon_id;
        $personel->unvan = $request->personelunvan_yeni;
        $personel->sube_id = $request->personelsube_yeni;
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
        
       
 
        $personeller_html = "";
         
         $personeller_html .= "<option selected value='".$personel->id."'>".$personel->personel_adi."</option>";
        
        echo $personeller_html;
    }
      public function salonhizmetsil(Request $request){
        $salonhizmet = SalonHizmetler::where('id',$request->salonhizmetid)->first();
        $salonhizmet->delete();

        $salonhizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
        $hizmetlistebayan_html = '';
        $hizmetlistebay_html = ''; 
        foreach ($salonhizmetler as $key => $value) {
          
            if($value->bolum==0){
                 $hizmetlistebayan_html .= " <tr>                                 
                                  <td>
                                    <input type='hidden' name='salonsunulanhizmetbayanid[]' value='".$value->hizmet_id."'>
                                  </td>
                                  <td>
                                    ".$value->hizmetler->hizmet_adi."
                                  </td>
                                  <td>
                                    <input type='text' class='form-control input-xs' name='salonsunulanhizmetbayanbaslangicfiyat[]' value='".$value->baslangic_fiyat."'>
                                  </td>
                                  <td>
                                    <input type='text' value='".$value->son_fiyat."' class='form-control input-xs' name='salonsunulanhizmetbayansonfiyat[]'>
                                  </td>

                                <td><a name='hizmetlistedensil' style='font-size:20px;cursor: pointer;' data-value='".$value->id."' class='icon'><i class='mdi mdi-delete'></i></a></td>
                            </tr>";
            }
             else{
                $hizmetlistebay_html .= " <tr>
                                 
                                  <td>
                                    <input type='hidden' name='salonsunulanhizmetbayid[]' value='".$value->hizmet_id."'>
                                  </td>
                                  <td>
                                    ".$value->hizmetler->hizmet_adi."
                                  </td>
                                  <td>
                                    <input type='text' class='form-control input-xs' name='salonsunulanhizmetbaybaslangicfiyat[]' value='".$value->baslangic_fiyat."'>
                                  </td>
                                  <td>
                                    <input type='text' value='".$value->son_fiyat."' class='form-control input-xs' name='salonsunulanhizmetbaysonfiyat[]'>
                                  </td>

                                <td><a name='hizmetlistedensil' style='font-size:20px;cursor: pointer;' data-value='".$value->id."' class='icon'><i class='mdi mdi-delete'></i></a></td>
                            </tr>";
             }        }     
         $listehtml = array();
         $listehtml['bayan'] = $hizmetlistebayan_html;
         $listehtml['bay'] = $hizmetlistebay_html;
         return $listehtml;                   
                      


    }

     public function mevcutisletmeduzenleme(Request $request){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $isletme->salon_adi = $request->isletmeadi;
        $isletme->adres = $request->adres;
        $isletme->il_id = $request->il;
        $isletme->ilce_id = $request->ilce;
      
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
       public function personelbilgiguncelle(Request $request,$id){
        $personel = Personeller::where('id',$id)->first();
        $personel->personel_adi = $request->personeladi;
        $personel->unvan = $request->unvan;
        $personel->sube_id = $request->personelsube;
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
     public function kayitlisalongorselisayisi(Request $request){
        $gorselsayisi = SalonGorselleri::where('salon_id',Auth::user()->salon_id)->where('kapak_fotografi','!=',1)->count();
        echo $gorselsayisi;
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
    public function yenirandevu(){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        $sunulanhizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
        $mevcutmusteriler = Randevular::join('users','randevular.user_id','=','users.id')->where('randevular.salon_id',Auth::user()->salon_id)->groupBy('randevular.user_id')->get();
        return view('isletmeadmin.yenirandevu',[ 'title' =>  'Yeni Randevu Ekle | randevumcepte.com.tr','pageindex' => 102,'isletme'=>$isletme,'personeller'=> $personeller, 'sunulanhizmetler'=> $sunulanhizmetler,'mevcutmusteriler'=>$mevcutmusteriler] );
    }

    public function randevupersonelgetir(Request $request){
      $personelhizmetler = PersonelHizmetler::join('salon_personelleri','personel_sunulan_hizmetler.personel_id','=','salon_personelleri.id')->where('salon_personelleri.salon_id',Auth::user()->salon_id)->where('personel_sunulan_hizmetler.hizmet_id',$request->hizmet_id)->get();
      $hizmet = Hizmetler::where('id',$request->hizmetid)->value('hizmet_adi');

      $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
                                    
      $personelsechtml = "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>
                                  <div class='form-group'>
                                    <label>".$hizmet." İçin Personel Seçin</label>
                                        <select required name='yenirandevupersonelleri[]' class='form-control'>";
       foreach ($personelhizmetler as $key => $value) {
             $personelsechtml .= "<option value='".$value->personel_id."'>".$value->personel_adi."</option>";
       }
       if($personelhizmetler->count()==0){
           foreach ($personeller as $key => $value) {
               $personelsechtml .= "<option value='".$value->id."'>".$value->personel_adi."</option>";
           }
       }
      $personelsechtml .="  </select>

                                  </div></div>";
      echo $personelsechtml;
       
         
    }
    public function yenirandevuekle(Request $request){
          $musteriid = 0;
          $olusturulansifre = "";
          if(!is_numeric($request->adsoyad)){
               $musterivar = User::where('name','like','%'.$request->adsoyad.'%')->where('cep_telefon',$request->ceptelefon)->first();
               if($musterivar){
                   $musteriid = $musterivar->id;
               }
               else
                {
                    $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
                      $olusturulansifre = substr($random, 0, 5);
                        
                      $yenimusteri = new User();
                      $yenimusteri->name = $request->adsoyad;
                      $yenimusteri->email = $request->eposta;
                      $yenimusteri->cep_telefon = $request->ceptelefon;
                      $yenimusteri->password = Hash::make($olusturulansifre);
                      $yenimusteri->save();

                      $musteriid = $yenimusteri->id;
                }
                
            
        }
      
        else
            $musteriid = $request->adsoyad;
        $tarihler = "";
        $portfoyvar = MusteriPortfoy::where('user_id',$musteriid)->where('salon_id',Auth::user()->salon_id)->first();
        if(!$portfoyvar){
            $yeniportfoy = new MusteriPortfoy();
            $yeniportfoy->user_id= $musteriid;
            $yeniportfoy->salon_id =Auth::user()->salon_id;
            $yeniportfoy->tur = 0;
            $yeniportfoy->save();
        }
        $randevu_tarihleri = array();
        array_push($randevu_tarihleri,$request->tarih);
        $eklenecek_tarih = $request->tarih;
        if(isset($request->tekrarlayan)){
            for($t=1;$t<$request->tekrar_sayisi;$t++){
                $eklenecek_tarih = date('Y-m-d', strtotime($request->tekrar_sikligi, strtotime($eklenecek_tarih)));
                array_push($randevu_tarihleri,$eklenecek_tarih);
                $tarihler .= $eklenecek_tarih." - ";
            }
           
        }
        foreach($randevu_tarihleri as $tarihler){
            $yenirandevu = new Randevular();
            $yenirandevu->user_id = $musteriid;
            $yenirandevu->salon_id = Auth::user()->salon_id;
            $yenirandevu->sube_id = $request->suberandevu;
            $yenirandevu->tarih = $tarihler;
            $yenirandevu->saat = $request->saat;
            $yenirandevu->personel_notu = $request->personel_notu;
            if(isset($request->sms_hatirlatma))
                $yenirandevu->sms_hatirlatma = true;
            else
                $yenirandevu->sms_hatirlatma = false;
            $totalsure = 0;
            foreach($request->hizmet_suresi as $key => $value)
            {
                $totalsure += $value;
            }
            $yenirandevu->saat_bitis = date("H:i", strtotime('+'.$totalsure.' minutes', strtotime($request->saat)));
            $yenirandevu->durum = 1;
            $yenirandevu->save();
           
             
            $hizmet_id = "";
            $yenisaatbaslangic = $request->saat;
            foreach ($request->randevuhizmetleriyeni as $key => $value) {
                if($value == 0){
                    $hizmet = new Hizmetler();
                    $hizmet->hizmet_adi = $value;
                    $hizmet->hizmet_kategori_id = 46;
                    $hizmet->fiyat = 0;
                    $hizmet->save();
                    $salon_hizmet = new SalonHizmetler();
                    $salon_hizmet->hizmet_id = $hizmet->id;
                    $salon_hizmet->hizmet_kategori_id = 46;
                    $salon_hizmet->salon_id = Auth::user()->salon_id;
                    $salon_hizmet->save();
                    $hizmet_id = $hizmet->id;
                }
                else
                {
                    $hizmet_id = $value;
                }
                $yenirandevuhizmetpersonel = new RandevuHizmetler();
                $yenirandevuhizmetpersonel->randevu_id = $yenirandevu->id;
                $yenirandevuhizmetpersonel->hizmet_id = $hizmet_id;
                $yenirandevuhizmetpersonel->personel_id = $request->randevupersonelleriyeni[$key];
                $yenirandevuhizmetpersonel->sure_dk = $request->hizmet_suresi[$key];
                $yenirandevuhizmetpersonel->fiyat = $request->hizmet_fiyat[$key];
                if($key == 0){
                     $yenirandevuhizmetpersonel->saat = $request->saat;
                     $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));
                     $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));
                }
                   
                 
                else{

                    $yenirandevuhizmetpersonel->saat = $yenisaatbaslangic;
                    $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));
                    $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));
                }
                $yenirandevuhizmetpersonel->save();

               
            }

        }
        
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $musteribilgi = User::where('id',$musteriid)->first();
        $gsm = $musteribilgi->cep_telefon;
        if($olusturulansifre != "")


            $mesaj = $isletme->salon_adi . " randevunuz ".date('d.m.Y',strtotime($request->tarih)) .'-'.$request->saatbaslangic .' olarak oluşturulmuştur. Adres : '.$yenirandevu->sube->adres;
        else
            $mesaj = $isletme->salon_adi . " randevunuz ".date('d.m.Y',strtotime($request->tarih)) .'-'.$request->saatbaslangic .' olarak oluşturulmuştur. Adres : '.$yenirandevu->sube->adres;

        $postUrl = "http://api.efetech.net.tr/v2/sms/basic";
		$apiKey = $isletme->sms_apikey;
		$headers = array(
			 'Authorization: Key '.$apiKey,
			 'Content-Type: application/json',
			 'Accept: application/json'
		);
      	$postData = json_encode( array( "originator"=> $isletme->sms_baslik, "message"=> $mesaj, "to"=>$gsm,"encoding"=>"auto") );
         
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$postUrl);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
				
        $response=curl_exec($ch);
            
       
   			


        $randevu['success'] = 'Randevu başarı ile oluşturuldu.';
        return json_encode($randevu);
        



    }
    public function saatkapamaekle(Request $request){
        $tarihler = "";
        $randevu_tarihleri = array();
        array_push($randevu_tarihleri,$request->tarih);
        $eklenecek_tarih = $request->tarih;
        if(isset($request->tekrarlayan)){
            for($t=1;$t<$request->tekrar_sayisi;$t++){
                $eklenecek_tarih = date('Y-m-d', strtotime($request->tekrar_sikligi, strtotime($eklenecek_tarih)));
                array_push($randevu_tarihleri,$eklenecek_tarih);
                $tarihler .= $eklenecek_tarih." - ";
            }
           
        }
        foreach($randevu_tarihleri as $tarihler){
            $yenirandevu = new Randevular();
            $yenirandevu->user_id = 0;
            $yenirandevu->salon_id = Auth::user()->salon_id;
            $yenirandevu->sube_id = $request->suberandevu;
            $yenirandevu->tarih = $tarihler;
            $yenirandevu->saat = $request->saat;
            $yenirandevu->saat_bitis = $request->saat_bitis;
            $yenirandevu->personel_notu = $request->personel_notu;
            $yenirandevu->save();
            $yenirandevuhizmetpersonel = new RandevuHizmetler();
            $yenirandevuhizmetpersonel->randevu_id = $yenirandevu->id;
            $yenirandevuhizmetpersonel->hizmet_id = 0;
            $yenirandevuhizmetpersonel->personel_id = $request->personel;
            $yenirandevuhizmetpersonel->sure_dk = $request->saat;
            $yenirandevuhizmetpersonel->fiyat = 0;
            $yenirandevuhizmetpersonel->saat = $request->saat;
            $yenirandevuhizmetpersonel->saat_bitis = $request->saat_bitis;
            $yenirandevuhizmetpersonel->save();

        }
        return "Saat kapama başarıyla eklendi";

           




    }
    public function calismasaatigetir(Request $request){
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

          $html = "<label>Başlangıç Saati</label><select name='saatbaslangic' id='saatbaslangic' class='form-control'>";  
          $html_saat_bitis = "<label>Bitiş Saati</label><select name='saatbitis' id='saatbitis' class='form-control'>";
          $randevusaataraligi = Salonlar::where('id',Auth::user()->salon_id)->value('randevu_saat_araligi');
          $mesaibaslangic =  SalonCalismaSaatleri::where('salon_id',Auth::user()->salon_id)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('baslangic_saati');
          $mesaibitis = SalonCalismaSaatleri::where('salon_id',Auth::user()->salon_id)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('bitis_saati');
           
          $dolusaatler = array();
          $musaitolmayansaatler = Randevular::join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')->where('randevular.tarih',$request->randevutarihi)->where('durum',1)->groupBy('randevular.saat')->select('randevular.*')->get();
          foreach ($musaitolmayansaatler as $musaitolmayansaat) {
               $dolusaatler[] = date('H:i' ,strtotime($musaitolmayansaat->saat));
          }
          $saatindex = 0;
          for($j = strtotime(date('H:i', strtotime($mesaibaslangic) )) ; $j < strtotime(date('H:i', strtotime($mesaibitis))); $j+=($randevusaataraligi * 60)){
                // if( (date('H:i',$j) > $nowtime && date('Y-m-d') == date('Y-m-d', strtotime($request->randevutarihi)))  || date('Y-m-d') < date('Y-m-d', strtotime($request->randevutarihi)) )
                    if(!in_array(date('H:i',$j), $dolusaatler)||in_array(date('H:i',$j), $dolusaatler)){
                         $html .= '<option value="'.date('H:i',$j).'">'. date('H:i',$j).'</div>';
                      $html_saat_bitis .= '<option value="'.date('H:i',$j).'">'. date('H:i',$j).'</div>';
                    }
                     
                                
             $saatindex ++;
          }
          if($saatindex != 0){
              $html .="</select>";
              $html_saat_bitis .="</select>";
          }
          $htmlarray['saatbaslangic'] = array();
          $htmlarray['saatbitis'] =array();
          array_push($htmlarray['saatbaslangic'], $html);
           array_push($htmlarray['saatbitis'], $html_saat_bitis);
        
        return $htmlarray;

    }
    public function musteribilgigetir(Request $request){
        /*$eposta = "";
        $telefon = "";
        if(!is_numeric($request->musteriid))
            $musteri = User::where('name','like','%'.$request->musteriid.'%')->first();
        else
            $musteri = User::where('id',$request->musteriid)->first();
        if($musteri){
            $eposta = $musteri->email;
            $telefon = $musteri->cep_telefon;
        }
        $musteribilgi['eposta'] = array();
        $musteribilgi['telefon'] = array();
        array_push($musteribilgi['eposta'], $eposta);
        array_push($musteribilgi['telefon'], $telefon);*/
        $musteri = User::where('id',$request->musteri_id)->first();
        return $musteri;
    }
    public function kampanyalar(){
        $kampanyalar = SalonKampanyalar::where('salon_id',Auth::user()->salon_id)->get();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $kampanyahtml = "";
        foreach ($kampanyalar as $key => $value) {
            $kampanyahtml .= "<tr><td>".$value->kampanya_aciklama."</td>
                                    
                                   <td>".date('d.m.Y',strtotime($value->kampanya_baslangic_tarihi))."</td>
                                   <td>".date('d.m.Y',strtotime($value->kampanya_bitis_tarihi))."</td>
                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->count()."</td>
                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',1)->count()."</td>
                                     <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',0)->count()."</td>";

                                   if($value->onayli == 1)

                                        $kampanyahtml .= "<td style='color:green'>Aktif</td>";
                                   else if($value->onayli == 0)
                                     $kampanyahtml .= "<td style='color:red'>Pasif</td>";
                                   $kampanyahtml .= "</tr>";

        }
        if($kampanyalar->count()==0){
            $kampanyahtml .= "<tr><td style='color:red' colspan='8'><strong>Henüz yayınlanmış avantajınız bulunmamaktadır!</strong></td></tr>";
        }
        return view('isletmeadmin.kampanyalar',['kampanyalar' => $kampanyalar, 'pageindex'=> 105,'title'=>'Avantajlarım | '.$isletme->salon_adi.' Salon Yönetim Paneli','isletme'=>$isletme,'kampanyahtml' => $kampanyahtml]);
    }
    public function avantajkupongetir(Request $request){
         $sonuc['mesaj'] = array();
          $sonuc['kupon'] = array();
         $kupon = SatinAlinanKampanyalar::where('kupon_kodu',$request->kuponkodu)->first();
         $kuponhtml = "";
         $kupononay = "";
         $sonuc['kupononay'] = array();
         if(!$kupon){
             $sonuc['mesaj'] = $request->kuponkodu.' avantaj kodu bulunamadı';
             $sonuc['kupon'] = "<tr><td colspan='7' style='color:red;text-align: center;'><strong>Kayıt bulunamadı. Lütfen avantaj kodunu giriniz</strong></td></tr>";
         }
           
        else{
            $sonuc['mesaj'] = "";
            $kuponhtml .= "<tr><td style='width:110px'>".$kupon->kupon_kodu."</td><td style='width:150px'>".$kupon->users->name."</td><td style='width:300px'>".$kupon->kampanyalar->kampanya_aciklama."</td><td>".date('d.m.Y',strtotime($kupon->son_kullanma_tarihi))."</td><td>".$kupon->kampanyalar->kampanya_fiyat."</td>";
            if($kupon->kullanildi == 1){

                $kuponhtml .= "<td style='color:green'>Kullanıldı</td></tr>";
                $kupononay .= '<button type="button" id="avantajkuponara" class="btn btn-space btn-primary" style="width: 200px;height: 30px;font-size: 20px"><i class="icon mdi mdi-search"></i> Kodu Ara</button>';
            }
            else if($kupon->kullanildi == 0){
                $kuponhtml .= "<td style='color:red'>Kullanılmadı</td></tr>";
                $kupononay ="<button class='btn btn-success' name='avantajkuponkullan' style='width: 200px;height: 30px;font-size: 20px' data-value='".$kupon->id."'><span class='icon mdi mdi-mail-send'></span> Kullan</button>";
            }
            $sonuc['kupon'] = $kuponhtml;
            $sonuc['kupononay'] = $kupononay;
        }

        return json_encode($sonuc);


    }
    public function avantajkuponkullan(Request $request){
        $sonuc['mesaj'] = array();
          $sonuc['kupon'] = array();
          $sonuc['avantaj'] =array();
         $kupon = SatinAlinanKampanyalar::where('id',$request->kuponid)->first();
         $kupon->kullanildi = 1;
         $kupon->save();
         $kuponhtml = "";
         
            $sonuc['mesaj'] = $kupon->kupon_kodu ." avantaj kodu başarı ile kullanıldı";
            $kuponhtml .= "<tr><td>".$kupon->kupon_kodu."</td><td>".$kupon->users->name."</td><td>".$kupon->kampanyalar->kampanya_aciklama."</td><td>".date('d.m.Y',strtotime($kupon->son_kullanma_tarihi))."</td><td>".$kupon->kampanyalar->kampanya_fiyat."</td>";
            if($kupon->kullanildi == 1)
                $kuponhtml .= "<td style='color:green'>Kullanıldı</td><td></td></tr>";
            else if($kupon->kullanildi == 0)
                $kuponhtml .= "<td style='color:red'>Kullanılmadı</td><td><button class='btn btn-success' name='avantajkuponkullan' data-value='".$kupon->id."'><span class='icon mdi mdi-mail-send'></span> Kullan</button</td></tr>";
            $sonuc['kupon'] = $kuponhtml;
             $kampanyalar = SalonKampanyalar::where('salon_id',Auth::user()->salon_id)->get();
        
        $kampanyahtml = "";
        foreach ($kampanyalar as $key => $value) {
            $kampanyahtml .= "<tr><td>".$value->kampanya_aciklama."</td>
                                    
                                   <td>".date('d.m.Y',strtotime($value->kampanya_baslangic_tarihi))."</td>
                                   <td>".date('d.m.Y',strtotime($value->kampanya_bitis_tarihi))."</td>
                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->count()."</td>
                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',1)->count()."</td>
                                     <td>".SatinAlinanKampanyalar::where('salon_id',Auth::user()->salon_id)->where('kullanildi',0)->count()."</td>";

                                   if($value->onayli == 1)

                                        $kampanyahtml .= "<td style='color:green'>Aktif</td>";
                                   else if($value->onayli == 0)
                                     $kampanyahtml .= "<td style='color:red'>Pasif</td>";
                                   $kampanyahtml .= "<td><a class='btn btn-space btn-success' href='/isletmeyonetim/kampanyadetay/".$value->id."'><span class='icon mdi mdi-settings'></span> Detaylar</a></td></tr>";

        }
        if($kampanyalar->count()==0){
            $kampanyahtml .= "<tr><td style='color:red' colspan='8'><strong>Henüz yayınlanmış avantajınız bulunmamaktadır!</strong></td></tr>";
        }
        $sonuc['avantaj'] = $kampanyahtml;
        return json_encode($sonuc);

    }
    public function kampanyadetaylari($id){
        $kampanyalar = SalonKampanyalar::where('id',$id)->first();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $kampanyadetay = SatinAlinanKampanyalar::where('kampanya_id',$id)->get();
        return view('isletmeadmin.kampanyadetay',['pageindex'=> 105,'title'=>'Kampanya Detayları | '.$isletme->salon_adi.' İşletme Yönetim Paneli','isletme'=>$isletme,'kampanyadetay' => $kampanyadetay]);
    }
    public function yenikampanyaekle(){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        return view('isletmeadmin.yenikampanya',['pageindex'=> 105,'title'=>'Yeni Kampanya Ekle | '.$isletme->salon_adi.' İşletme  Yönetim Paneli','isletme'=>$isletme]);

    }
    public function avantajyapilanodemeler(){
        $avantajodemeler = KampanyaYapilanOdemeler::join('kampanyalar','kampanya_yapilan_odemeler.kampanya_id','=','kampanyalar.id')->where('kampanyalar.salon_id',Auth::user()->salon_id)->select('kampanya_yapilan_odemeler.id','kampanyalar.kampanya_aciklama','kampanya_yapilan_odemeler.created_at','kampanya_yapilan_odemeler.adet','kampanya_yapilan_odemeler.tutar')->get();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
         
        return view('isletmeadmin.kampanyayapilanodemeler',['pageindex'=>1051,'title'=>'Yapılan Ödemeler | '.$isletme->salon_adi.' İşletme Yönetim Paneli','avantajodemeler'=> $avantajodemeler,'isletme'=>$isletme]);
    }
    public function gorselyukle(){
        $ds          = DIRECTORY_SEPARATOR;  
 
        $storeFolder = 'public/salon_gorselleri';
        $enable_upload = true;   
 
        if ( !empty($_FILES) && $enable_upload ) {
     
            $tempFile = $_FILES['file']['tmp_name'];                 
      
            $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  
     
         $targetFile =  $targetPath. $_FILES['file']['name']; 
 
         move_uploaded_file($tempFile,$targetFile); 
     
        }
    }
    public function yenismslistesiekle(Request $request){
        $resulthtml['sonuc'] = array();
        $resulthtml['liste'] = array();
        $resulthtml['eklenen'] = array();

        if(isset($_FILES["listedosyasi_yeni"]["name"])){
          
              $dosya  = $request->listedosyasi_yeni;
              $kaynak = $_FILES["listedosyasi_yeni"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["listedosyasi_yeni"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["listedosyasi_yeni"]["name"]);
                        $uzanti = explode(".", $_FILES["listedosyasi_yeni"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/listeler/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        } 
            
            $dosya_veritabaninaaktarilacak = $request->listedosyasi_yeni;
              if( $dosya_veritabaninaaktarilacak->isFile() ) {
                    $file = $hedef;

                    $excel = App::make('excel');
                    $excelFile = $excel->load($file)->get();

                     

                    $modelMap = [
                        'name' => 'ad_soyad',
                        'email' => 'e_posta',
                        'cep_telefon' => 'cep_telefonu',
                        
                    ];

                    $count = 0;
                    $listehtmlappend = "";
                    $html2append = "";
                    foreach($excelFile as $item) {

                        $data = [];
                        foreach($modelMap as $key => $value) {
                            if( $value != '' ) {
                                $data[$key] = $item[$value];
                                $html2append .= $item[$value] . ' - ';
                            }
                        } 
                        
                        $import = new User($data);
                        
                        $import->save() ;
                        $portfoy = new MusteriPortfoy();
                        $portfoy->user_id = $import->id;
                        $portfoy->salon_id = Auth::user()->salon_id;
                        $portfoy->save();
                        $listehtmlappend .='<tr>
                        <td> 

                            <div class="be-checkbox be-checkbox-color inline">
                                <input id="user'.$import->id.'" name="user'.$import->id.'" type="checkbox">
                                <label for="user'.$import->id.'"></label>
                             </div></td>
                        <td>
                        '.$import->name.'
                        </td>
                        <td>
                           '.$import->cep_telefon.'
                        </td>
                        <td>
                            '.$import->email.'
                        </td>
                    </tr>';
                        $count++;

                          
                    } 
                    array_push($resulthtml['eklenen'], $html2append);
                    array_push($resulthtml['sonuc'],$count . ' adet müşteri listenize başarı ile eklendi');
                    array_push($resulthtml['liste'], $listehtmlappend);
                    return $resulthtml;
              } 

        }
        else{
            array_push($resulthtml['sonuc'],'Lütfen excel dosyası yükleyiniz');
            array_push($resulthtml['liste'], $listehtmlappend);
            
        }
    }
    public function smslistedetay($listeid){
        $smslistedetaylari = SMSListeBilgiler::where('sms_listeleri_id',$listeid)->get();
        $listeadi = SMSListeleri::where('id',$listeid)->value('sms_liste_adi');
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        return view('isletmeadmin.smslistedetay',['title' => $listeadi.' SMS Liste Detayı | randevumcepte.com.tr','pageindex' => 107,'isletme'=>$isletme,'smslistedetaylari'=> $smslistedetaylari]);
    }
    public function smsbilgiguncelle(Request $request){
        $smslistedetayi = SMSListeBilgiler::where('id',$request->smslistebilgiid)->first();
        $smslistedetayi->ad_soyad = $request->liste_ad_soyad;
        $smslistedetayi->cep_telefon = $request->liste_cep_telefon;
        if($request->liste_karaliste=='on'){

            $smslistedetayi->sms_kampanya_karaliste == 1;
            if($request->liste_karalistenedeni == 1)
                $smslistedetayi->sms_kampanya_karaliste_nedeni == 'Çok fazla gönderim yapıldığını bildirdi';
            if($request->liste_karalistenedeni == 2)
                $smslistedetayi->sms_kampanya_karaliste_nedeni == 'Gönderimlerle ilgilenmediğini bildirdi';
            if($request->liste_karalistenedeni == 3)
                $smslistedetayi->sms_kampanya_karaliste_nedeni == $request->liste_karalistenedeni_diger;
            
        }
        $smslistedetayi->save();


    }
    public function smslistedetaybilgigetir(Request $request){
        $smslistedetay = SMSListeBilgiler::where('id',$request->detayid)->first();
       
       
        return json_encode($smslistedetay);
    }
    public function smsraporlar(Request $request){
         $raporhtml = "";
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $raporlar = SMSIletimRaporlari::where('salon_id',Auth::user()->salon_id)->get();
        $smsbilgiler = SMSBilgiler::where('salon_id',Auth::user()->salon_id)->first();
        foreach ($raporlar as $key => $value) {
              $postUrl="http://panel.1sms.com.tr:8080/api/dlr/v1?username=".$smsbilgiler->kullanici_adi."&password=".$smsbilgiler->sifre."&id=".$value->rapor_id;

            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,$postUrl);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

            $response=curl_exec($ch);
            curl_close($ch);
            $responsemesaj = explode('|', $response);
           
            foreach ($responsemesaj as $key2 => $value2) {
                 
                if($key2==0 && $value2=='99')
                    $raporhtml .='<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 99 : Dokümante edilmemiş bilinmeyen hata</td><td></td><td></td>';
                else if($key2==0 && $value2=='95')
                    $raporhtml .= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 95 : USE_GET_METHOD Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                 else if($key2==0 && $value2=='93')
                    $raporhtml .= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 93 : MISSING_GET_PARAMS Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                 else if($key2==0 && $value2=='87')
                    $raporhtml .= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 87 : WRONG_USER_OR_PASSWORD Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                else if($key2==0 && $value2=='79')
                    $raporhtml .= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>İleti Raporu Görüntülemede Sistemsel Hata 79 : DLR_ID_NOT_FOUND Rapor bulunamadı. Lütfen sistem yöneticisi ile iletişime geçiniz.</td><td></td><td></td>';
                 else if($key2==0 && $value2=='29')
                    $raporhtml .= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>Mesaj yollanmak üzere. Lütfen bekleyiniz</td><td></td><td></td>';
                 else if($key2==0 && $value2=='27')
                    $raporhtml .= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td><td>Mesaj yollanırken raporlamada beklenmeyen hata oluştu</td><td></td><td></td>';
                 else if($key2>0){
                    $raporhtml.= '<tr><td>'.$value->rapor_id.'</td><td>'.date('d.m.Y H:i',strtotime($value->created_at)).'</td>';
                    $numara_durum = explode(' ', $value2);
                    $mesajdurum = '';
                    if($numara_durum[1] == '0')
                            $mesajdurum = '<td style="color:orange">Beklemede</td>';
                    else if($numara_durum[1]=='5')
                        $mesajdurum = '<td style="color:orange">SMS Gönderildi, İletim Raporu Bekleniyor</td>';
                    else if($numara_durum[1]=='6')
                        $mesajdurum = '<td style="color:red">Başarısız</td>';
                    else if($numara_durum[1]=='9')
                        $mesajdurum = '<td style="color:green">Başarılı</td>';
                    $raporhtml .= '<td>'.$value->aciklama.'</td><td>'.$numara_durum[0].'</td>'.$mesajdurum;
                 }
                 $raporhtml .='</tr>'; 


            }
            
        }
        return view('isletmeadmin.smsraporlar',['pageindex'=> 109,'title'=>'SMS Raporlarım | '.$isletme->salon_adi.' İşletme Yönetim Paneli','isletme'=>$isletme,'rapor'=>$raporhtml]);
    }
    public function smstaslakolarakkaydet(Request $request){
        $result['sonuc'] = array();
        $result['liste'] = array();
        if(SMSTaslaklari::where('salon_id',Auth::user()->salon_id)->count() ==8){
            array_push($result['sonuc'], 'En fazla 8 tane taslak kaydedebilirsiniz');
            array_push($result['liste'], '');
        }
        else{
             $taslak = new SMSTaslaklari();
        $taslak->taslak_icerik = $request->taslak_icerik;
        $taslak->salon_id = Auth::user()->salon_id;
        $taslak->save();
        $taslakhtml = "";
        
        $taslaklar = SMSTaslaklari::where('salon_id',Auth::user()->salon_id)->get();
         foreach($taslaklar as $taslak){
             $taslakhtml .= '
             <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <input type="hidden" id="smstaslak'.$taslak->id.'" value="'.$taslak->taslak_icerik.'">
                <a title="Metni Kopyala" data-value="'.$taslak->id.'" name="smstaslaklari" style="position:relative; cursor: pointer;">
                   
                  <img src="http://'.$_SERVER['SERVER_NAME'].'/public/img/taslak.png" style="width:100%;height: 100%" width="323" height="621">
                  <span style="position: absolute;left:20px;top:-150px;font-size:20px;font-weight: bold;color:black">'.$taslak->baslik.'</span>
                  <span style="position: absolute;left: 20px;top:-120px;padding:10px;background-color: #e4e4e2; border-radius: 10px;color:black;font-size:14px;max-width: 165px">'.$taslak->taslak_icerik.'</span>
                </a>
             </div>';
         }
         array_push($result['sonuc'], 'Mesajınız taslaklarınıza başarı ile eklendi');
         array_push($result['liste'], $taslakhtml);
        }
       
         return $result;
           
    }
    public function toplusmsgonderme(Request $request){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $gsm = array();
        foreach ($request->musteriler as $musteri) {
            $musteri = User::where('id',$musteri)->first();
            array_push($gsm,$musteri->cep_telefon);
                    
        }
        $mesaj = $request->smsmesaj;
        $postUrl = "http://api.efetech.net.tr/v2/sms/basic";
        $apiKey = $isletme->sms_apikey;
        $headers = array(
             'Authorization: Key '.$apiKey,
             'Content-Type: application/json',
             'Accept: application/json'
        );
        $postData = json_encode( array( "originator"=> $isletme->sms_baslik, "message"=> $mesaj, "to"=>$gsm,"encoding"=>"auto") );

              $ch=curl_init();
              curl_setopt($ch,CURLOPT_URL,$postUrl);
              curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
              curl_setopt($ch,CURLOPT_POST,1);
              curl_setopt($ch,CURLOPT_TIMEOUT,5);
              curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
              curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                
              $response=curl_exec($ch);

                
        
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                /*if($response == '99')
                    echo 'Sistemsel hata 99 : Bilinmeyen bir hata oluştu. Lütfen sistem yöneticisi ile iletişime  geçiniz';
                else if($response == '97')
                    echo 'Sistemsel hata 97 : USE_POST_METHOD. Lütfen sistem yöneticisi ile iletişime  geçiniz.';
                else if($response == '89')
                    echo 'Sistemsel hata 89: WRONG_XML_FORMAT. Lütfen sistem yöneticisi ile iletişime  geçiniz.';
                else if($response == '87') 
                    echo 'Sistemsel hata 87: WRONG_USER_OR_PASSWORD. Lütfen sistem yöneticisi ile iletişime  geçiniz';
                else if($response == '85')
                    echo 'Sistemsel hata 85 : WRONG_SMS_HEADER. Lütfen sistem yöneticisi ile iletişime  geçiniz';
                else if($response == '84')
                    echo 'İleri tarihli gönderim zamanı hatalı bir formata sahip veya 1 yıldan daha ileri bir zamanı gösteriyor';
                else if($response == '83')
                    echo 'Mesaj metni ve numaralar incelendikten sonra sistem yollanacak bir SMS oluşturmaya yetecek en az 1 numara ve en az 1 karakterden oluşan mesaj metnine sahip olamadı. Gönderim yapılacak verilerin yeterli olmadığına karar verdi.';
                else if($response == '81')
                    echo 'Yetersiz bakiye : Gönderilecek olan mesaj için yeterli krediye sahip değilsiniz.';
                else if($response == '77')
                    echo 'Son 2 dakika içinde aynı SMSin gönderilmesi durumu gerçekleşti. Lütfen yeniden deneyiniz';*/
                
                /*    $responsemesaj = explode(' ', $response);
                    $rapor = new SMSIletimRaporlari();
                    $rapor->salon_id = Auth::user()->salon_id;
                    $rapor->rapor_id = $responsemesaj[1];
                    $rapor->aciklama = $mesaj;
                    $rapor->save();*/
                if($http_status == 200)
                    echo "Mesajınız alıcılara başarı ile iletildi";
                else
                    echo "Mesajınız gönderilirken bir hata oluştu. Lütfen sistem yöneticisine başvurunuz";
                 
    }
    public function musteriliste(Request $request){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $musteriler = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->orderBy('user_id','desc')->get();
        //usteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->leftjoin('randevular','musteri_portfoy.user_id','=','randevular.user_id')->select('users.name as name','users.cep_telefon as cep_telefon','users.created_at as created_at',DB::raw('COUNT(*)'));
        $paketler = self::paket_liste_getir('',true);
        return view('isletmeadmin.musteriler',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'sayfa_baslik'=>'Müşteriler','pageindex' => 4, 'musteriler'=> $musteriler,'isletme'=> $isletme ]);
    }
    public function musteribilgiguncelle(Request $request){
         $musteri = User::where('id',$request->musteriid)->first();
         $musteri->name = $request->adsoyad;
         $musteri->cep_telefon = $request->telefon;
         $musteri->dogum_tarihi = $request->dogum_tarihi;
         $musteri->cinsiyet = $request->cinsiyet;
         $musteri->email = $request->email;
         $musteri->adres = $request->adres;
         $musteri->save();
    }
    public function musteriportfoykaldir(Request $request){
        $portfoymusteri = MusteriPortfoy::where('user_id',$request->musteriid)->where('salon_id',Auth::user()->salon_id)->first();



        $result['sonuc'] = array();
        $result['liste'] = array();
        $result['toplammusteri'] = array();
        if($portfoymusteri){
            $adsoyad = $portfoymusteri->users->name;
            $portfoymusteri->delete();
            array_push($result['sonuc'], $adsoyad .' isimli müşteri portföyünüzden başarıyla kaldırıldı');
        }
        $portfoy = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get();
        $portfoytablohtml = "";
        $musteriprofilresim = "";
        foreach ($portfoy as $key => $value) {
            $user = User::where('id',$value->user_id)->first();
            $cinsiyet = "";
            $tur = "";
            $renk1 = "";
            if($user->profil_resim!= null ||$user->profil_resim != '' )
                $musteriprofilresim = $user->profil_resim;
            else
                $musteriprofilresim = 'public/isletmeyonetim_assets/img/avatar.png';
            if($user->cinsiyet == '0')
                $cinsiyet = 'Bayan';
            else if($user->cinsiyet == '1')
                $cinsiyet = 'Bay';
            else
                $cinsiyet = '';
            if($value->tur == 1){
                $tur = 'A';
                $renk1 = '#FF4E00';
            }
            else if($value->tur == 0){
                $tur = 'KM';
                $renk1 = '#1266f1';
            }
            $portfoytablohtml .= '<tr><td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$musteriprofilresim.'" alt="Profil Resmi"></td><td>'.$user->name.'</td><td>'.$cinsiyet.'</td><td style="color:white"><span style="position:relative;float:left;width:30px; text-align:center;background-color:'.$renk1.';padding:5px">'.$tur.'</span></td>
            <td>'.$user->email.'</td>
            <td>'.$user->cep_telefon.'</td>
            <td>'.Randevular::where('user_id',$user->id)->count().'</td>';
            if(SalonPuanlar::where('user_id',$user->id)->value('puan') == null || SalonPuanlar::where('user_id',$user->id)->value('puan') == '')
             $portfoytablohtml .= '<td>0</td>';
             else
                $portfoytablohtml .= '<td>'.SalonPuanlar::where('user_id',$user->id)->value('puan').'</td>';

            $portfoytablohtml .= '<td>
                        <a class="btn btn-space btn-success btn-xs" data-value="'.$user->id.'" href="/isletmeyonetim/musteridetay/'.$user->id.'"><span class="icon mdi mdi-settings"></span> Detaylar</a>
                  <button class="btn btn-space btn-danger btn-xs" data-value="'.$user->id.'" name="musterikaldir"><span class="icon mdi mdi-delete"></span> Kaldır</button>
            </td></tr>';
        }
        array_push($result['toplammusteri'], $portfoy->count());
        array_push($result['liste'], $portfoytablohtml);
        return $result;

    }
    public function yenimusterilistesiekle(Request $request){
        $resulthtml['sonuc'] = array();
        $resulthtml['liste'] = array();
        $resulthtml['toplammusteri'] = array();
        $portfoytablohtml = "";
        $musteriprofilresim = "";
        if(isset($_FILES["listedosyasi_yeni_musteri"]["name"])){
          
              $dosya  = $request->listedosyasi_yeni_musteri;
              $kaynak = $_FILES["listedosyasi_yeni_musteri"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["listedosyasi_yeni_musteri"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["listedosyasi_yeni_musteri"]["name"]);
                        $uzanti = explode(".", $_FILES["listedosyasi_yeni_musteri"]["name"]);
                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/listeler/".$dosya;
                                $dosya   = $dosya;
                            }
                            move_uploaded_file($kaynak, $hedef);
                        } 
            
            $dosya_veritabaninaaktarilacak = $request->listedosyasi_yeni_musteri;
            if( $dosya_veritabaninaaktarilacak->isFile() ) {

                    $file = $hedef;

                    $excel = App::make('excel');
                     
                    $excelFile = $excel->load($file)->get();
                    $data = array();
                    $datasayisi =0;
                    $count=0;
                    $count2 = Excel::load($file,function($reader) use($count){
                        foreach ($reader->toArray() as $key => $row) {
                            $data['name'] = $row['ad_soyad'];
                            $row['cep_telefonu'] = str_replace('+90', '', $row['cep_telefonu']);
                            $data['cep_telefon'] =$row['cep_telefonu'];

                            
                            if(!empty($data)){
                                $eskidata = "";
                                
                                $eskidata = User::where('cep_telefon',$data['cep_telefon'])->first();
                                if($eskidata)
                                {
                                    
                                    if($eskidata->cep_telefon == null || $eskidata->cep_telefon =='')
                                        $eskidata->cep_telefon = $row['cep_telefonu'];
                                    $eskidata->save();
                                    $portfoyvar = MusteriPortfoy::where('user_id',$eskidata->id)->where('salon_id',Auth::user()->salon_id)->first();
                                    if(!$portfoyvar){
                                        $portfoyyeni = new MusteriPortfoy();
                                        $portfoyyeni->salon_id = Auth::user()->salon_id;
                                        $portfoyyeni->user_id = $eskidata->id;
                                        $portfoyyeni->save();
                                        $count++;                                  

                                       
                                    }
                                
                                }
                                else{
                                    $yenimusteri = new User($data);
                                    $yenimusteri->save();
                                    $portfoyyeni2 = new MusteriPortfoy();
                                    $portfoyyeni2->salon_id = Auth::user()->salon_id;
                                    $portfoyyeni2->user_id = $yenimusteri->id;
                                    $portfoyyeni2->save();
                                    $count++;
                                }
                                
                            }
                        }
                        
                       
                    })->toArray();
                    foreach ($count2 as $sayi) {
                         $datasayisi++;
                    } 
                    array_push($resulthtml['sonuc'],$datasayisi . ' adet müşteri listenize başarı ile eklendi.');


                    $portfoy2 = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get();
                    
                    foreach ($portfoy2 as $key => $value) {
                        $user = User::where('id',$value->user_id)->first();
                        $cinsiyet = "";
                        $tur = "";
                        $renk1 = "";
                        if($user->profil_resim!= null ||$user->profil_resim != '' )
                            $musteriprofilresim = $user->profil_resim;
                        else
                            $musteriprofilresim = 'public/isletmeyonetim_assets/img/avatar.png';
                        if($user->cinsiyet == '0')
                            $cinsiyet = 'Bayan';
                        else if($user->cinsiyet == '1')
                            $cinsiyet = 'Bay';
                        else
                            $cinsiyet = '';
                        if($value->tur == 1){
                            $tur = 'A';
                            $renk1 = '#FF4E00';
                        }
                        else if($value->tur == 0){
                            $tur = 'KM';
                            $renk1 = '#1266f1';
                        }
                        $portfoytablohtml .= '<tr><td class="user-avatar"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$musteriprofilresim.'" alt="Profil Resmi"></td><td>'.$user->name.'</td><td>'.$cinsiyet.'</td><td style="color:white"><span style="position:relative;float:left;width:30px; text-align:center;background-color:'.$renk1.';padding:5px">'.$tur.'</span></td>
                        <td>'.$user->email.'</td>
                        <td>'.$user->cep_telefon.'</td>
                        <td>'.Randevular::where('user_id',$user->id)->count().'</td>';
                        if(SalonPuanlar::where('user_id',$user->id)->value('puan') == null || SalonPuanlar::where('user_id',$user->id)->value('puan') == '')
                         $portfoytablohtml .= '<td>0</td>';
                         else
                            $portfoytablohtml .= '<td>'.SalonPuanlar::where('user_id',$user->id)->value('puan').'</td>';

                        $portfoytablohtml .= '<td>
                                    <a class="btn btn-space btn-success btn-xs" data-value="'.$user->id.'" href="/isletmeyonetim/musteridetay/'.$user->id.'"><span class="icon mdi mdi-settings"></span> Detaylar</a>
                              <button class="btn btn-space btn-danger btn-xs" data-value="'.$user->id.'" name="musterikaldir"><span class="icon mdi mdi-delete"></span> Kaldır</button>
                        </td></tr>';
                    }

                    array_push($resulthtml['liste'], $portfoytablohtml);
                    array_push($resulthtml['toplammusteri'], $portfoy2->count());
                    return $resulthtml;
              } 

        }
        else{
            array_push($resulthtml['sonuc'],'Lütfen excel dosyası yükleyiniz');
            array_push($resulthtml['liste'], $portfoytablohtml);
            
        }
    }
    public function musteridetay(Request $request,$id){
        $musteri = User::where('id',$id)->first();
        $randevular = Randevular::where('user_id',$musteri->id)->orderBy('tarih','desc')->get();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $sube_sayisi = Subeler::where('salon_id',Auth::user()->salon_id)->where('aktif',1)->count();
        $islemler = Islemler::where('user_id',$id)->groupBy('hizmet_kategori_id')->orderBy('id','desc')->get();

        $puan = SalonPuanlar::where('user_id',$musteri->id)->where('salon_id',Auth::user()->salon_id)->value('puan');
        $yorum = SalonYorumlar::where('user_id',$musteri->id)->where('salon_id',Auth::user()->salon_id)->value('yorum');
         
        $salon_hizmetleri = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->groupBy('hizmet_kategori_id')->get();


         $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
$sunulanhizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->groupBy('hizmet_id')->get();
         $paketler = self::paket_liste_getir('',true);
        return view('isletmeadmin.musteridetay',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'sunulanhizmetler'=>$sunulanhizmetler,'personeller'=>$personeller,'salon_hizmetleri'=>$salon_hizmetleri,'sube_sayisi'=>$sube_sayisi,'islemler'=>$islemler,'pageindex'=>41, 'sayfa_baslik'=> $musteri->name,'isletme'=>$isletme, 'musteri'=>$musteri,'puan'=>$puan,'yorum'=>$yorum,'randevular'=>$randevular]);
    }
    public function musteriexceleaktar(){
        $musteriler = MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get();
        $musteri_array[] = array('Ad Soyad','Cinsiyet','Tür','E-posta','Cep Telefon','Randevu Sayısı','Puanlama');
        foreach ($musteriler as $key => $value) {
            $user=User::where('id',$value->user_id)->first();
            $tur = "";
            $cinsiyet = "";
            if($value->tur == 1)
                    $tur = 'randevumcepte.com.tr';
            else if($value->tur == 0)
                $tur = 'Ekleme & Kendi Müşterilerim';
            if($user->cinsiyet == 0)
                $cinsiyet = 'Bayan';
            else if($user->cinsiyet == 1)
                $cinsiyet = 'Bay';
            $musteri_array[] = array(
                'Ad Soyad' => $user->name,
                'Cinsiyet' => $cinsiyet,
                'Tür' => $tur,
                'E-posta' => $user->email,
                'Cep Telefon' => $user->cep_telefon,
                'Randevu Sayısı' => Randevular::where('user_id',$user->id)->count(),
                'Puanlama' => SalonPuanlar::where('user_id',$user->id)->value('puan'),

            );

        }
         Excel::create('Müşteri Data',function($excel) use($musteri_array){
                $excel->setTitle('Müşteri Data');
                $excel->sheet('Müşteri Data',function($sheet) use($musteri_array){

                    $sheet->fromArray($musteri_array,null,'A1',false,false);
                });

        })->export('xlsx');

    }
    public function avantajraporgetir(Request $request){
        $avantajlar = SalonKampanyalar::where('salon_id',Auth::user()->salon_id)->get();
        $raporhtml = "";
        foreach ($avantajlar as $key => $value) {
           
            $toplamsatis = SatinAlinanKampanyalar::where('kampanya_id',$value->id)->where('odeme_yapildi',1)->where('created_at','>=',date('Y-m-d',strtotime($request->tarihbaslangic)))->where('created_at','<=',date('Y-m-d',strtotime($request->tarihbitis)))->count();
            $kullanilan = SatinAlinanKampanyalar::where('kampanya_id',$value->id)->where('odeme_yapildi',1)->where('kullanildi',1)->where('created_at','>=',date('Y-m-d',strtotime($request->tarihbaslangic)))->where('created_at','<=',date('Y-m-d',strtotime($request->tarihbitis)))->count();
             $kullanilmayan = SatinAlinanKampanyalar::where('kampanya_id',$value->id)->where('odeme_yapildi',1)->where('kullanildi','!=',1)->where('created_at','>=',date('Y-m-d',strtotime($request->tarihbaslangic)))->where('created_at','<=',date('Y-m-d',strtotime($request->tarihbitis)))->count();
            $raporhtml .= "<tr><td>".$value->kampanya_aciklama."</td><td>".$toplamsatis."</td><td>".$kullanilan."</td><td>".$kullanilmayan."</td>";
            if($value->onayli == 1)
                $raporhtml .= "<td style='color:green;font-weight:bold'>Aktif</td>";
            if($value->onayli == 0)
                $raporhtml .= "<td style='color:red;font-weight:bold'>Pasif</td>";
            $raporhtml .="</tr>";
            
        }
        echo $raporhtml;
    }
    public function toplusmsbasvuru(){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();

        $smspaketler = SMSPaketleri::all();
         return view('isletmeadmin.toplusmsbasvuru',['title' => 'Toplu SMS Paket Satın Al - Başvur | randevumcepte.com.tr','pageindex' => 114,'isletme'=>$isletme,'smspaketler' => $smspaketler]);
    }
    public function isletmdetaygetir(Request $request){
        $islemdetaylari = Islemler::where('user_id',$request->musteri_id)->where('hizmet_kategori_id',$request->hizmet_kategori_id)->get();
         $musteri = User::where('id',$request->musteri_id)->first();

        
        $html = "<h4>".$musteri->name." için ".Hizmet_Kategorisi::where('id',$request->hizmet_kategori_id)->value('hizmet_kategorisi_adi')." İşlemleri Takibi</h4>";

        if($request->hizmet_kategori_id == 36){
            $html .= "<p><b>Başlangıç Kilo</b> : ".$musteri->baslangic_kg. '</p>';
            $html .= "<p><b>Başlangıç Göğüs</b> : ".$musteri->baslangic_gogus . '</p>';
            $html .= "<p><b>Başlangıç Göbek</b> : ".$musteri->baslangic_gobek. '</p>';
            $html .= "<p><b>Başlangıç Kalça</b> : ".$musteri->baslangic_kalca. '</p>';
            $html .= "<p><b>Başlangıç Basen</b> : ".$musteri->baslangic_basen. '</p>';
            $html .= "<p><b>Başlangıç Kalça</b> : ".$musteri->baslangic_bel. '</p>';
            $html .= "<p><b>Başlangıç Basen</b> : ".$musteri->baslangic_sirt. '</p><br><br>';
        }

        $html .= ' <table class="table table-striped table-hover table-fw-widget" id="table6"><tbody><thead>
                      <tr><th>Tarih</th>
                          <th>Seans</th>';
        if($request->hizmet_kategori_id == 33){
            $html .= '<p><b>Kıl Yapısı : </b>';
            if($musteri->kil_yapisi == 0)
                $html .= 'İnce</p>';
            elseif($musteri->kil_yapisi == 1) {
                 $html .= 'Kalın</p>';
            }
            else
                $html .= 'Belirtilmemiş</p>';
            $html .= '<p><b>Ten Rengi : </b>';
            if($musteri->ten_rengi == 0)
                $html .= 'Esmer</p>';
            elseif($musteri->ten_rengi == 1)
                $html .= 'Beyaz</p>';
            elseif($musteri->ten_rengi == 2)
                $html .= 'Kumral</p>';
            else
                $html .= 'Belirtilmemiş</p>';
               
            $html .= '<th>K.Altı</th><th>Bacak</th><th>Kol</th><th>Bikini</th><th>Yüz</th><th>Göğüs</th><th>Göbek</th><th>Sırt</th><th>Bıyık</th><th>Favori</th><th>Ense</th>';
        }
        $html .= "<th>Uzman</th><th>Yapılan İşlem</th><th>Ücret</th><th>Açıklama</th><th></th></tr><tbody>";
        foreach($islemdetaylari as $islemdetayi)
        {
            $html .= '<tr><td>'.date('d.m.Y',strtotime($islemdetayi->tarih)).'</td>';
            $html .= '<td>'.$islemdetayi->seans_no.'</td>';
            if($request->hizmet_kategori_id == 33){
                if($islemdetayi->koltuk_alti == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->bacak == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->kol == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->bikini == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->yuz == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->gogus == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->gobek == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->sirt == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->biyik == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->favori == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';
                if($islemdetayi->ense == true)
                   $html .= '<td style="text-align:center">X</td>';
                else
                   $html .= '<td style="text-align:center"></td>';

            }
            $html .= '<td>'.$islemdetayi->personeller->personel_adi.'</td>';
            $html .= '<td>'.$islemdetayi->yapilan_islemler.'</td>';
            $html .= '<td>'.$islemdetayi->alinan_odeme.'</td>';
            $html .= '<td>'.$islemdetayi->aciklama.'</td>';
            $html .= '<td><a class="btn btn-space btn-primary btn-xs" href="#" name="islem-duzenle" data-value="'.$islemdetayi->id.'"> Düzenle</a></td></tr>';



        }
        $html .= '</table>';
        return $html;
                      

    }
    public function saglikbilgilerigir(Request $request){
         $user = User::where('id',$request->musteri_id)->first();
         $user->hemofili_hastaligi_var = $request->hemofili_hastaligi_var;
         $user->seker_hastaligi_var  = $request->seker_hastaligi_var;
         $user->hamile = $request->hamile;
         $user->yakin_zamanda_ameliyat_gecirildi = $request->yakin_zamanda_ameliyat_gecirildi;
         $user->alerji_var = $request->alerji_var;
         $user->alkol_alimi_yapildi = $request->alkol_alimi_yapildi;
         $user->regl_doneminde = $request->regl_doneminde;   
         $user->deri_yumusak_doku_hastaligi_var = $request->deri_yumusak_doku_hastaligi_var;  
         $user->surekli_kullanilan_ilac_Var = $request->surekli_kullanilan_ilac_Var;  
         //$user->surekli_kullanilan_ilac_aciklama = $request->     
         $user->kemoterapi_goruyor = $request->kemoterapi_goruyor;   
         $user->daha_once_uygulama_yaptirildi = $request->daha_once_uygulama_yaptirildi;    
         //$user->daha_once_yaptirilan_uygulama_aciklama = $request->   
         $user->ek_saglik_sorunu = $request->ek_saglik_sorunu;
         $user->cilt_tipi = $request->cilt_tipi;
         $user->save();
         echo 'Sağlık bilgileri başarıyla kaydedildi / güncellendi';
    }
    public function randevu_sil(Request $request){
        $randevu_hizmetler = RandevuHizmetler::where('randevu_id',$request->randevuid)->get();
        foreach($randevu_hizmetler as $randevu_hizmet)
            $randevu_hizmet->delete();
        $randevu = Randevular::find($request->randevuid);
        $randevu->delete();
        
        $randevular = "";
        if(Auth::user()->is_admin)
            $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->where('tarih','like','%'.$request->tarih.'%')->where('sube_id','like','%'.$request->sube.'%')->orderBy('id','desc')->get();
        else
            $randevular = Randevular::where('salon_id',Auth::user()->salon_id)->where('tarih','like','%'.$request->tarih.'%')->where('sube_id',Auth::user()->salon_personelleri->sube_id)->orderBy('id','desc')->get();

        $randevuliste = array();
        foreach($randevular as $randevu){
            $randevuliste1['musteri'] = '<span style="display:none">'.strtotime($randevu->tarih).'</span>'.$randevu->users->name;
            $randevuliste1['tarihsaat'] = date('d.m.Y',strtotime($randevu->tarih)). ' '.date('H:i', strtotime($randevu->saat));
            $randevuliste1['sube'] = $randevu->sube->sube;
            $hizmethtml = "";
            $durumhtml = "";
            $islemlerhtml = "";
            foreach(RandevuHizmetler::where('randevu_id',$randevu->id)->get() as $hizmet) 
                $hizmethtml .= $hizmet->hizmetler->hizmet_adi. "<br>" ;
                $islemlerhtml .= '  <button class="btn btn-primary randevudetayigetir" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-edit"></span> Düzenle
                            </button>';
            if($randevu->durum == 0){
                $durumhtml .= "<button class='btn btn-warning'>Beklemede</button>";
                $islemlerhtml .= ' <button class="btn btn-success randevuonayla" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-check-circle"></span> Onayla 
                            </button>';
                $islemlerhtml .=   '<button class="btn btn-danger randevuiptalet" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-minus-circle"></span> İptal Et 
                            </button>';
            }
            elseif($randevu->durum == 1){
                  $durumhtml .= "<button class='btn btn-success'>Onaylı</button>";
                $islemlerhtml .=   '<button class="btn btn-danger randevuiptalet" data-value="'.$randevu->id.'">    
                               <span class="mdi mdi-minus-circle"></span> İptal Et 
                            </button>';
            }
          
            else
                $durumhtml .= "<button class='btn btn-danger'>İptal</button>";
         
            $islemlerhtml .=   ' <button class="btn btn-default randevusil" style="background-color: #0080FF;color:#fff"  data-value="'.$randevu->id.'">
                               
                               <span class="mdi mdi-delete"></span> Sil
                             </button>
                              '; 

                            
                           
            $randevuliste1['durum'] = $durumhtml;
            $randevuliste1['hizmetler'] = $hizmethtml;
            $randevuliste1['islemler'] = $islemlerhtml;
            array_push($randevuliste, $randevuliste1);


        }
        return $randevuliste;
    }
    public function musteri_sil(Request $request){
        $randevular = Randevular::where('user_id',$request->id)->get();
        $islemler = Islemler::where('user_id',$request->id)->get();
        $muster_portfoy = MusteriPortfoy::where('user_id',$request->id)->get();
        foreach($randevular as $randevu)
            $randevu->delete();
        foreach($islemler as $islem)
            $islem->delete();
        foreach($musteri_portfoy as $portfoy)
            $portfoy->delete();
        $user = User::find($request->id);
        $user->delete();
        echo $request->id .' nolu ait tüm müşteri kayıtları sistemden başarıyla silindi';
        
    }
    public function randevudetay(Request $request,$id){
        $personeller="";
        $randevu = Randevular::where('id',$id)->first();
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $hizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
        $randevu_hizmetler = RandevuHizmetler::where('randevu_id',$id)->get();
        $subeler = Subeler::where('salon_id',Auth::user()->salon_id)->where('aktif',1)->get();
        $urunler = UrunSatislari::where('randevu_id',$id)->get();
        $tum_urunler = Urunler::where('salon_id',Auth::user()->salon_id)->get();
        $tahsilatlar = Tahsilatlar::where('randevu_id',$id)->get();

        $tahsilat_tutari = Tahsilatlar::where('randevu_id',$id)->sum('tutar');
        $toplam_tutar = UrunSatislari::where('randevu_id',$id)->sum('fiyat') + RandevuHizmetler::where('randevu_id',$id)->sum('fiyat');
         

        if(Auth::user()->is_admin)
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        else
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->where('sube_id',Auth::user()->salon_personelleri->sube_id)->get();
        $paketler = self::paket_liste_getir('',true);
        return view('isletmeadmin.randevudetay',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'pageindex'=>4, 'sayfa_baslik'=>$randevu->users->name.' Adisyon Detayları','randevu'=>$randevu,'isletme'=>$isletme,'personeller'=>$personeller,'subeler'=>$subeler,'sunulanhizmetler'=>$hizmetler,'hizmetler'=>$randevu_hizmetler,'urunler'=>$urunler,'tum_urunler'=>$tum_urunler,'tahsilatlar'=>$tahsilatlar,'tahsilat_tutari'=>$tahsilat_tutari, 'toplam_tutar'=> $toplam_tutar]);
    }
    public function urunler(){
        $urunler = self::urun_liste_getir("");
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $paketler = self::paket_liste_getir('',true);
        return view('isletmeadmin.urunler',['paketler'=>$paketler,'pageindex'=>6, 'sayfa_baslik'=>'Ürünler','urunler'=>$urunler,'isletme'=>$isletme]);
    }
    public function urun_ekle_guncelle(Request $request){
        $urun="";
        $returntext="";
        if($request->urun_id == 0){
            $urun = new Urunler();
            $returntext = "Ürün başarıyla eklendi";
        }
        else{
            $urun = Urunler::where('id',$request->urun_id)->first();
            $returntext = "Ürün başarıyla güncellendi";
        }
        $urun->urun_adi = $request->urun_adi;
        $urun->fiyat = $request->fiyat;
        $urun->barkod = $request->barkod;
        $urun->stok_adedi = $request->stok_adedi;
        $urun->salon_id = Auth::user()->salon_id;

        $urun->save();

        
        return self::urun_liste_getir($returntext);

    }
    public function paket_ekle_guncelle(Request $request){
        $paket="";
        $returntext="";
        if($request->paket_id == 0){
            $paket = new Paketler();
            $returntext = "Paket başarıyla eklendi";
        }
        else{
            $paket = Paketler::where('id',$request->paket_id)->first();
            $returntext = "Paket başarıyla güncellendi";
        }
        $paket->miktar = $request->adet;
        $paket->fiyat = $request->fiyat;
        $paket->hizmet_id = $request->hizmet;
        $paket->tip = $request->tip;
        $paket->salon_id = Auth::user()->salon_id;

        $paket->save();

        
        return self::paket_liste_getir($returntext,false);
    }
    public function paket_sil(Request $request){
        Paketler::where('id',$request->paket_id)->delete();
        PaketSatislari::where('paket_id',$request->paket_id)->delete();
        return self::paket_liste_getir("Paket başarıyla kaldırıldı",false);
    }
    public function urun_sil(Request $request){
        Urunler::where('id',$request->urun_id)->delete();
        UrunSatislari::where('urun_id',$request->urun_id)->delete();
        return self::urun_liste_getir("Ürün başarıyla kaldırıldı");

    }

                                
                         
    public function urun_liste_getir($returntext){
        $urun_liste = DB::table('urunler')->select('urun_adi','stok_adedi','fiyat','barkod',DB::raw('CONCAT("<div class=\"dropdown\">
                        <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                                  href=\"#\"
                                  role=\"button\"
                                  data-toggle=\"dropdown\"
                                ><i class=\"dw dw-more\"></i>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                                    <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"
                  data-target=\"#urun-modal\" name=\"urun_duzenle\" data-value=\"",id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                                    <a class=\"dropdown-item\" href=\"#\" name=\"urun_sil\" data-value=\"",id,"\"><i class=\"fa fa-remove\"></i> Sil</a>
                                </div>
                                </div>") AS islemler'))->where('salon_id',Auth::user()->salon_id)->orderBy('id','desc')->get();
        return array(
                'status' => $returntext,
                'urun_liste' => $urun_liste,
    
        ); 
    }

    public function paket_liste_getir($returntext,$addition){
        $paket_liste = "";
        if($addition)
            $paket_liste = DB::table('paketler')->join('hizmetler','paketler.hizmet_id','=','hizmetler.id')->select('paketler.miktar as miktar',
                DB::raw('CASE WHEN paketler.tip = 0 THEN "Dakika" ELSE  "Seans" END as tip'),
                'hizmetler.id as hizmet_id','hizmetler.hizmet_adi as hizmet' ,'paketler.fiyat as fiyat',DB::raw('CONCAT("<button title=\"Ekle\" class=\"btn btn-success\" name=\"satis_formuna_paket_ekle\" data-value=\"",paketler.id,"\"><i class=\"fa fa-plus\"></i> Ekle</a>") AS islemler'))->where('salon_id',Auth::user()->salon_id)->orderBy('paketler.id','desc')->get();
        else
            $paket_liste = DB::table('paketler')->join('hizmetler','paketler.hizmet_id','=','hizmetler.id')->select('paketler.miktar as miktar',DB::raw('CASE WHEN paketler.tip = 0 THEN "Dakika" ELSE  "Seans" END as tip'),'hizmetler.hizmet_adi as hizmet' ,'paketler.fiyat as fiyat',DB::raw('CONCAT("<div class=\"dropdown\">
                        <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                                  href=\"#\"
                                  role=\"button\"
                                  data-toggle=\"dropdown\"
                                ><i class=\"dw dw-more\"></i>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                                    <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"
                  data-target=\"#paket-modal\" name=\"paket_duzenle\" data-value=\"",paketler.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                                    <a class=\"dropdown-item\" href=\"#\" name=\"paket_sil\" data-value=\"",paketler.id,"\"><i class=\"fa fa-remove\"></i> Sil</a>
                                </div>
                                </div>") AS islemler'))->where('salon_id',Auth::user()->salon_id)->orderBy('paketler.id','desc')->get();
        
        return array(
                'status' => $returntext,
                'paket_liste' => $paket_liste,
    
        ); 
    }


    public function hizmetsurefiyatgetir(Request $request){
        $hizmet = SalonHizmetler::where('hizmet_id',$request->hizmet_id)->first();
        return json_encode(array('fiyat'=>$hizmet->baslangic_fiyat,'sure'=>$hizmet->sure_dk));
    }
    public function urunfiyatgetir(Request $request){
        $urun = Urunler::where('id',$request->urun_id)->first();
        return $urun->fiyat;
    }
    public function randevuurunekle(Request $request){

        foreach($request->urunyeni as $key=>$yeni_urun){

            $urun_satisi = new UrunSatislari();
            $urun_satisi->urun_id = $yeni_urun;
            $urun_satisi->randevu_id = $request->randevu_id;
            $urun_satisi->personel_id = $request->urun_satici[$key];
            $urun_satisi->tarih = $request->urun_satis_tarihi;
            $urun_satisi->fiyat = $request->urun_fiyati[$key];
            $urun_satisi->adet = $request->urun_adedi[$key];
            $urun_satisi->notlar = $request->satis_notlari;
            $urun_satisi->user_id =$request->musteri_id;
            $urun_satisi->save();
            $urun = Urunler::where('id',$yeni_urun)->first();
            $urun->stok_adedi-=$request->urun_adedi[$key];
            $urun->save();
        }

        $satislar = UrunSatislari::where('randevu_id',$request->randevu_id)->get();
        $html = "";
        foreach($satislar as $urun)
        {
            $html .= '<tr>
                        <td>'.$urun->urunler->urun_adi.'</td>
                        <td>'.$urun->adet.'</td>
                        <td>
                           <input type="hidden" name="urun_fiyati_adisyon[]" value="'.$urun->urunler->fiyat.'"> 
                           '.$urun->urunler->fiyat.'

                        </td>
                        
                        <td style="width:30px">
                          
                           <button type="button" name="urun_formdan_sil" data-value="'.$urun->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                           
                          
                        </td>
                     </tr>';
        }
        return array(
                'status' => 'Ürün adisyona başarıyla kaydedildi',
                'html' => $html,
    
        ); 
      

        
    }
    public function musteriarama(Request $request){
        $musteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->select('users.id as id','users.name as ad_soyad')->where('musteri_portfoy.salon_id',107)->where('users.name','like','%'.$request->musteri_adi.'%')->get();
        $listehtml = "";
        foreach($musteriler as $musteri)
            $listehtml .= '<option value="'.$musteri->ad_soyad.'" data-value="/isletmeyonetim/musteridetay/'.$musteri->id.'"></option>';
        return $listehtml;
    }
    public function musteriekleguncelle(Request $request){
            $returnvar = "";
       
            $musteri = "";
            $yeniekleme = false;
            if($request->musteri_id != ""){
                $musteri = User::where('id',$request->musteri_id)->first();
                
            }
            else{
                $musteri_var = User::where('cep_telefon',$request->telefon)->count();
        
                if($musteri_var > 0)
                {
                    $mevcut = User::where('cep_telefon',$request->telefon)->first();
                    $returnvar = array(
                        'detailtext' => '',
                        'title' => 'Uyarı',
                        'mesaj' => 'Sistemde girdiğiniz telefon numarasına ait '.$mevcut->name.' isimli kayıt mevcuttur',
                        'musteri_id' => 0,
                        'yeniekleme' => $yeniekleme,
                        'status' => 'warning'
                    );
                    return $returnvar;
                    exit();
                }   
                else{
                    $yeniekleme = true;
                    $musteri = new User();
                    $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
                    $olusturulansifre = substr($random, 0, 5);
                    $musteri->password = Hash::make($olusturulansifre);

                }
            }
            $musteri->name = $request->ad_soyad;
                $musteri->email = $request->email;
                $musteri->cep_telefon = $request->telefon;
                $musteri->dogum_tarihi = date('Y-m-d',strtotime($request->dogum_tarihi));
                $musteri->cinsiyet = $request->cinsiyet;
                $musteri->ozel_notlar = $request->ozel_notlar;
                $musteri->save();
                if($request->musteri_id == "")
                {
                    $yeniportfoy = new MusteriPortfoy();
                    $yeniportfoy->user_id= $musteri->id;
                    $yeniportfoy->salon_id =Auth::user()->salon_id;
                    $yeniportfoy->save();
                }
                $returntext =  "<p><b>ID : </b>".$musteri->id."</p>";
                $returntext .= "<p><b>Ad Soyad : </b>".$musteri->name."</p>";
                $returntext .= "<p><b>Telefon : </b>".$musteri->cep_telefon."</p>";
                $returntext .= "<p><b>E-posta : </b>".$musteri->email."</p>";
                $returntext .= "<p><b>Doğum Tarihi : </b>".date('d.m.Y', strtotime($musteri->dogum_tarihi))."</p>";
                $returntext .= "<p><b>Cinsiyet : </b>";
                if ($musteri->cinsiyet === 0)
                    $returntext .="Kadın";
                elseif ($musteri->cinsiyet===1) 
                    $returntext .= "Erkek";
                else
                    $returntext .= "Belirtilmemiş";
                $returntext .= "</p>";
                $returnvar = array(
                'title' => "Başarılı",
                'detailtext' => $returntext,
                'mesaj' => "Müşteri bilgileri başarıyla kaydedildi.",
                'musteri_id' => $musteri->id,
                'yeniekleme' => $yeniekleme,
                'status' => 'success'

            );
                   
            
                
                            
           
            
        
        

            return $returnvar;

        
        
    }
    public function calismasaatleriduzenle(Request $request){
        $saloncalismasaatlerieski = SalonCalismaSaatleri::where('salon_id',Auth::user()->salon_id)->delete();
        $salonmolasaaterieski = SalonMolaSaatleri::where('salon_id',Auth::user()->salon_id)->delete();
       
        for($i=1;$i<=7;$i++){
            
            $saloncalismasaatleri = new SalonCalismaSaatleri();
            $salonmolasaatleri = new SalonMolaSaatleri();
            $saloncalismasaatleri->haftanin_gunu = $i;
            $salonmolasaatleri->haftanin_gunu = $i;
            $saloncalismasaatleri->salon_id = Auth::user()->salon_id;
            $salonmolasaatleri->salon_id = Auth::user()->salon_id;
            
            if(isset($_POST['calisiyor'.$i])){

                $saloncalismasaatleri->calisiyor = 1;
               
               
            }
            else{
                $saloncalismasaatleri->calisiyor = 0;
            }
            if(isset($_POST['molavar'.$i])){

                $salonmolasaatleri->calisiyor = 1;
               
               
            }
            else{
                $salonmolasaatleri->calisiyor = 0;
            }
                
             if($i==1){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati1;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati1;

                      $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati1;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati1;
                }
                 if($i==2){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati2;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati2;
                     $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati2;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati2;
                }
                 if($i==3){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati3;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati3;
                     $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati3;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati3;
                }
                 if($i==4){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati4;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati4;
                     $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati4;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati4;
                }
                 if($i==5){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati5;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati5;
                     $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati5;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati5;
                }
                 if($i==6){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati6;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati6;
                     $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati6;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati6;
                }
                 if($i==7){
                     $saloncalismasaatleri->baslangic_saati = $request->baslangicsaati7;
                     $saloncalismasaatleri->bitis_saati = $request->bitissaati7;
                     $salonmolasaatleri->baslangic_saati = $request->molabaslangicsaati7;
                     $salonmolasaatleri->bitis_saati = $request->molabitissaati7;
                }
                $saloncalismasaatleri->save();
                $salonmolasaatleri->save();
            
        }
        return "Çalışma saatleri başarıyla kaydedildi";
    }
    public function isletmebilgiguncelle(Request $request){
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $isletme->salon_adi = $request->isletme_adi;
        $isletme->salon_turu_id = $request->isletme_turu;
        $isletme->vergi_adi = $request->vergi_adi;
        $isletme->vergi_no = $request->vergi_no;
        $isletme->vergi_dairesi = $request->vergi_dairesi;
        $isletme->vergi_adresi = $request->vergi_adresi;
        $isletme->kdv_orani = $request->kdv_orani;
        $isletme->save();
        return "İşletme bilgileri başarıyla kaydedildi";
    }
    
    public function randevuhizmetekle(Request $request)
    {
            $son_hizmet = RandevuHizmetler::where('randevu_id',$request->randevu_id)->orderBy('id','desc')->first();
            $yenisaatbaslangic = $son_hizmet->saat_bitis;
            foreach ($request->randevuhizmetleriyeni as $key => $value) {
                if($value == 0){
                    $hizmet = new Hizmetler();
                    $hizmet->hizmet_adi = $value;
                    $hizmet->hizmet_kategori_id = 46;
                    $hizmet->fiyat = 0;
                    $hizmet->save();
                    $salon_hizmet = new SalonHizmetler();
                    $salon_hizmet->hizmet_id = $hizmet->id;
                    $salon_hizmet->hizmet_kategori_id = 46;
                    $salon_hizmet->salon_id = Auth::user()->salon_id;
                    $salon_hizmet->save();
                    $hizmet_id = $hizmet->id;
                }
                else
                {
                    $hizmet_id = $value;
                }
                $yenirandevuhizmetpersonel = new RandevuHizmetler();
                $yenirandevuhizmetpersonel->randevu_id = $request->randevu_id;
                $yenirandevuhizmetpersonel->hizmet_id = $hizmet_id;
                $yenirandevuhizmetpersonel->personel_id = $request->randevupersonelleriyeni[$key];
                $yenirandevuhizmetpersonel->sure_dk = $request->hizmet_suresi[$key];
                $yenirandevuhizmetpersonel->fiyat = $request->hizmet_fiyat[$key];
                if($key == 0){
                     $yenirandevuhizmetpersonel->saat = $request->saat;
                     $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));
                     $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));
                }
                   
                 
                else{

                    $yenirandevuhizmetpersonel->saat = $yenisaatbaslangic;
                    $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));
                    $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));
                }
                $yenirandevuhizmetpersonel->save();

               
            }
            $randevuhizmetlermevcut = RandevuHizmetler::where('randevu_id',$request->randevu_id)->get();
            $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
            $sunulanhizmetler = SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get();
            $html = "";
            foreach($randevuhizmetlermevcut as $hizmet){
                $html .= '<div class="row" data-value="0" style="background-color:#e2e2e2;margin-bottom: 5px;">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Personel</label>
                           <select name="randevupersonelleri[]" class="form-control custom-select2" style="width: 100%;">';
                foreach($personeller as $personel)
                {
                    if($hizmet->personel_id == $personel->id)
                        $html .= '<option selected value="'.$personel->id.'">'.$personel->personel_adi.'</option>';
                    else
                        $html .= '<option value="'.$personel->id.'">'.$personel->personel_adi.'</option>';
                }
                $html .= '</select>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Hizmet</label>
                           <select name="randevuhizmetleri[]" class="form-control custom-select2" style="width: 100%;">';
                foreach($sunulanhizmetler as $hizmetliste){
                    if($hizmet->hizmet_id == $hizmetliste->hizmet_id)
                        $html .= '<option selected value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';
                    else
                        $html .= '<option value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';
                }
                $html .= '</select>
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Süre (dk)</label>
                           <input type="tel" class="form-control" name="hizmet_suresi_adisyon[]" value="'.$hizmet->sure_dk.'" >
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Fiyat (₺)</label>
                           <input type="tel" class="form-control" name="hizmet_fiyati_adisyon[]" value="'.$hizmet->fiyat.'">
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">Kaldır</label>
                           <button type="button" name="hizmet_formdan_sil_2"  data-value="'.$hizmet->hizmet_id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                        </div>
                     </div>
                  </div>';
            }  
            return array(
                'statustext' => 'Hizmet randevuya başarıyla eklendi',
                'html' => $html
            ); 
    }
    public function yaklasan_dogumgunleri()
    {
        
         $yaklasan_dogumgunleri = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->select('users.name as ad_soyad','users.cep_telefon as telefon', 'users.dogum_tarihi as dogum_tarihi')->whereDay('dogum_tarihi', '>=',date('d'))->whereDay('dogum_tarihi','<=',date('d',strtotime('+5 days',strtotime(date('Y-m-d')))))->where('musteri_portfoy.salon_id',Auth::user()->salon_id)->get();
         echo $yaklasan_dogumgunleri;
    }
    public function tahsilatekle(Request $request){
        $tahsilat = new Tahsilatlar();
        $tahsilat->randevu_id = $request->randevu_id;
        $tahsilat->tutar = $request->tahsilat_tutari;
        $tahsilat->odeme_tarihi = $request->tahsilat_tarihi;    
        $tahsilat->olusturan_id = Auth::user()->id;
        $tahsilat->salon_id = Auth::user()->salon_id;
        $tahsilat->yapilan_odeme = $request->tahsilat_tutari;
        $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;
        $tahsilat->notlar = $request->tahsilat_notlari;
        $tahsilat->save();

        return self::adisyon_tahsilatlari($request,'Tahsilat kaydı başarıyla eklendi');
    }
    public function adisyonlar(Request $request){
        $adisyonlar = self::adisyon_yukle($request);
        $paketler = self::paket_liste_getir('',true);

        return view('isletmeadmin.adisyonlar',['paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request), 'sayfa_baslik'=>'Adisyonlar','pageindex' => 11,'adisyonlar'=>$adisyonlar]);
    }
    public function adisyon_yukle(Request $request)
    {
        $adisyonlar = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->join('users','randevular.user_id','=','users.id')->leftJoin('urun_satislari','urun_satislari.randevu_id','=','randevular.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->leftJoin('urunler','urun_satislari.urun_id','=','urunler.id')->leftJoin('tahsilatlar','tahsilatlar.randevu_id','=','randevular.id')->leftjoin('odeme_yontemleri','tahsilatlar.odeme_yontemi_id','=','odeme_yontemleri.id')->select(
                    DB::raw("CASE WHEN randevular.acik = 1 THEN 'Açık' ELSE 'Kapalı' END as durum")
                    ,
                    'users.name as musteri',
                    DB::raw("CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') as hizmetler"),
                    DB::raw('GROUP_CONCAT(urunler.urun_adi) as urunler'),
                    DB::raw('DATE_FORMAT(randevular.tarih, "%d.%m.%Y") as tarih'),
                    DB::raw('DATE_FORMAT(randevular.saat, "%H:%i") as saat'),
                    DB::raw('CASE WHEN randevular.randevuya_geldi = 1 THEN "Gelmedi" ELSE "Gelmedi" END as geldimi'),
                    DB::raw('SUM(randevu_hizmetler.fiyat) + COALESCE(SUM(urun_satislari.fiyat), 0) as toplam'),
                    DB::raw('COALESCE(SUM(tahsilatlar.tutar), 0) as odenen'),
                    DB::raw('SUM(randevu_hizmetler.fiyat) + COALESCE(SUM(urun_satislari.fiyat), 0) - COALESCE(SUM(tahsilatlar.tutar), 0) as kalan_tutar'), 
                    DB::raw('CONCAT("<a title=\"Detaylı Bilgi\" href=\"/isletmeyonetim/randevudetay/",randevular.id,"\" type=\"button\" class=\"btn btn-info\"><i class=\"dw dw-eye\"></i></a>") as islemler'),
                )->where('randevular.salon_id',Auth::user()->salon_id)->groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();
        return $adisyonlar;
    }
    public function adisyon_tahsilatlari(Request $request,$statstr)
    {
        $tahsilatlar = Tahsilatlar::where('randevu_id',$request->randevu_id)->get();
        $tahsilat_tutari = 0;
        $html = "";

        $urunsatislari = UrunSatislari::where('randevu_id',$request->randevu_id)->sum('fiyat');
        $randevuhizmetleri = RandevuHizmetler::where('randevu_id',$request->randevu_id)->sum('fiyat');
        
        foreach ($tahsilatlar as $tahsilatliste)
        {
            $html .= ' <tr>
                           <td>'.date('d.m.Y',strtotime($tahsilatliste->created_at)).'</td>
                           <td>'.$tahsilatliste->tutar.'</td>
                           <td>
                              '.$tahsilatliste->odeme_yontemi->odeme_yontemi.'
                           </td>
                           <td>
                              <button type="button" name="tahsilat_adisyondan_sil" data-value="'.$tahsilatliste->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                           </td>
                            
                        </tr>';
            $tahsilat_tutari += $tahsilatliste->tutar;
        } 

        if($tahsilat_tutari == ($urunsatislari + $randevuhizmetleri))
        {
            $randevu = Randevular::where('id',$request->randevu_id)->first();
            $randevu->acik = false;
            $randevu->save();
        }

        $statustext = $statstr;
        return array(
            'statustext' => $statustext,
            'html' => $html,
            'tahsilat_tutari' => $tahsilat_tutari,
            'toplam_tutar' => $urunsatislari + $randevuhizmetleri,
            'kalan_tutar' => ($urunsatislari + $randevuhizmetleri)-$tahsilat_tutari,

        );
    }
    public function hareket_ekle(Request $request,$islem)
    {
        $hareketler = new RandevuHareketleri();
        $hareketler->personel_id = Auth::user()->personel_id;
        $hareketler->randevu_id = $request->randevu_id;
        $hareketler->islem = $islem;
        $hareketler->save();
    }
    public function tahsilatkaldir(Request $request){
        $tahsilat = Tahsilatlar::where('id',$request->tahsilatid)->delete();
        return self::adisyon_tahsilatlari($request,'Tahsilat kaydı başarıyla kaldırıldı');

    }
    public function ongorusmeler(Request $request)
    {
        $isletme =Salonlar::where('id',Auth::user()->salon_id)->first();
        $ongorusmeler = self::ongorusmegetir($request);
        $paketler = self::paket_liste_getir('',true);
        
        return view('isletmeadmin.ongorusmeler',['paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'sayfa_baslik'=>'Ön Görüşmeler','on_gorusmeler'=>$ongorusmeler,'pageindex' => 12,'isletme'=>$isletme]);  
    }
    public function ongorusmeekleduzenle(Request $request)
    {
        $ongorusme = "";
        $tarih = "";

        if($request->on_gorusme_id != "")
        {
            $ongorusme = OnGorusmeler::where('id',$request->on_gorusme_id)->first();
            $tarih = $request->tarih;

        }
        else
        {
            $ongorusme = new OnGorusmeler();
            $tarih = date('Y-m-d',strtotime('+'.$request->hatirlatma_kac_gun_sonra.' days', strtotime(date('Y-m-d'))));    
        }
        if($request->musteri_id != 0)
            $ongorusme->user_id = $request->musteri_id;
        $ongorusme->salon_id = Auth::user()->salon_id;
        $ongorusme->ad_soyad = $request->ad_soyad;
        $ongorusme->cep_telefon = $request->telefon;
        $ongorusme->email =$request->email;
        $ongorusme->cinsiyet = $request->cinsiyet;
        $ongorusme->adres = $request->adres;
        $ongorusme->aciklama=$request->aciklama;
        $ongorusme->il_id =$request->sehir;
        $ongorusme->musteri_tipi = $request->musteri_tipi;
        $ongorusme->meslek = $request->meslek;
        $ongorusme->paket_id = $request->paket;
        $ongorusme->hatirlatma_tarihi = $tarih;
        $ongorusme->personel_id = $request->gorusmeyi_yapan;
        $ongorusme->yonlendiren = 
        $ongorusme->durum = false;
        $ongorusme->save();
        echo '<p>Ön görüşme başarıyla kaydedildi</p><a class="btn btn-primary btn-lg btn-block" href="/isletmeyonetim/ongorusmeler">Ön Görüşme Listeme Git</a>';

    }
    public function ongorusme_musteriye_aktar(Request $request)
    {
        $ongorusme = OnGorusmeler::where('id',$request->on_gorusme_id)->first();
        $ongorusme->durum = true;
        if($ongorusme->user_id === null){
            $user = new User(); 

        }
    }
    public function ongorusmedetay(Request $request){
        return OnGorusmeler::where('id',$request->ongorusme_id)->first();
    }
    public function hatirlatmasmsgonder(Request $request){
        $ongorusmeler = OnGorusmeler::whereIn('id',$request->on_gorusme_bilgi)->get();
        $mesajlar = array();
        foreach($ongorusmeler as $ongorusme)
        {
            $paketstr = "";
            if($ongorusme->paket->tip == 0)
                $paketstr = "Dakika";
            else
                $paketstr = "Seans";
            array_push($mesajlar, array("to"=>$ongorusme->cep_telefon,"message"=> "Sayın ".$ongorusme->ad_soyad."\n\n". $ongorusme->paket->miktar." ".$paketstr." ".$ongorusme->paket->hizmet->hizmet_adi." ilgili görüşmüştük. Konu ile ilgili yanıtınızı beklediğimizi belirtir, iyi günler dileriz."));

        }
        self::sms_gonder($request,$mesajlar);
    }
    public function sms_gonder(Request $request,$mesajlar)
    {
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
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
       
    }
    public function smscoklutest(Request $request)
    {
        $mesajlar = array(
            array("to"=>"5316237563","message"=>"test1"),

        );
        self::sms_gonder($request,$mesajlar);

    }
    
    public function ongorusmesatisyapildi(Request $request)
    {
        $ongorusme = OnGorusmeler::where('id',$request->on_gorusme_id)->first();
        $ongorusme->durum = true;
        $ongorusme->save;
        if($ongorusme->user_id === null){
            
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 6);
                        
            $user = new User();
            $user->name = $ongorusme->ad_soyad;
            $user->email = $ongorusme->email;
            $user->cep_telefon = $ongorusme->cep_telefon;
            $user->password = Hash::make($olusturulansifre);
            $user->save();
            $mesaj = array(array("to"=>$ongorusme->cep_telefon,"message"=>$ongorusme->salon->salon_adi ." randevu sistemi kullanıcı hesabınız oluşturulmuştur. Kullanıcı adınız : ".$user->cep_telefon.", şifreniz : ".$olusturulansifre.""));
        }

    }
    public function denemedb2(Request $request){
        echo DB::connection('mysql1')->table('users')->get();
    }
    public function ongorusmegetir(Request $request){
         return DB::table('on_gorusmeler')
        ->join('salonlar','on_gorusmeler.salon_id','=','salonlar.id')
        ->leftjoin('users','on_gorusmeler.user_id','=','users.id')
        
        ->join('salon_personelleri','on_gorusmeler.personel_id','=','salon_personelleri.id')
        ->leftjoin('paketler','on_gorusmeler.paket_id','=','paketler.id')
        ->join('hizmetler','paketler.hizmet_id','=','hizmetler.id')
        ->leftjoin('il','on_gorusmeler.il_id','=','il.id')
        ->select(                             
            DB::raw('CONCAT("<div class=\"dt-checkbox\"><input type=\"checkbox\" name=\"on_gorusme_bilgi[]\" value=\"",on_gorusmeler.id,"\"><span class=\"dt-checkbox-label\"></span></div>") as id'),
            'on_gorusmeler.ad_soyad as musteri',
            'on_gorusmeler.cep_telefon as telefon',
            DB::raw('DATE_FORMAT(on_gorusmeler.created_at, "%d.%m.%Y") as olusturulma'),
            DB::raw('DATE_FORMAT(on_gorusmeler.hatirlatma_tarihi, "%d.%m.%Y") as hatirlatma'),
            DB::raw('CONCAT(paketler.miktar,CASE WHEN paketler.tip=0 THEN " Dakika " ELSE " Seans " END, hizmetler.hizmet_adi) as paket'),
            'salon_personelleri.personel_adi as gorusmeyiyapan',
            DB::raw('CASE WHEN on_gorusmeler.durum=0 THEN "<button class=\"btn btn-danger btn-block\">Satış Yapılmadı</button>" ELSE "<button class=\"btn btn-success btn-block\">Satış Yapıldı</button>" END as durum'),
                DB::raw('CONCAT(
                "<div class=\"dropdown\">
                    <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                        href=\"#\"
                        role=\"button\"
                        data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                        <a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",on_gorusmeler.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                        <a class=\"dropdown-item\" href=\"#\" name=\"musteriye_hatirlatma_smsi_gonder\" data-value=\"",on_gorusmeler.id,"\"><i class=\"fa fa-envelope\"></i> SMS Gönder</a>
                        <a class=\"dropdown-item\" href=\"#\" name=\"satis_yapildi\" data-value=\"",on_gorusmeler.id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a>
                        </div></div>") as islemler'))->where('salonlar.id',Auth::user()->salon_id)->get();
    }
    public function paketsatislari(Request $request)
    {
        $isletme = Salonlar::where('id',Auth::user()->salon_id)->first();
        $paketsatislari = self::paketsatislarigetir($request);
        $paketler = self::paket_liste_getir("",true);
        $personeller = Personeller::where('salon_id',Auth::user()->salon_id)->get();
        return view('isletmeadmin.paketsatislari',['paketler'=>$paketler, 'bildirimler'=>self::bildirimgetir($request),'paketsatislari'=>$paketsatislari,'sayfa_baslik' => 'Paket Satışları','pageindex' => 13,'isletme' => $isletme,'personeller'=>$personeller]);
    }
    public function paketsatislarigetir(Request $request)
    {
        $paketsatislari = DB::table('paket_satislari')
        ->join('paketler','paket_satislari.paket_id','=','paketler.id')
        ->join('users','paket_satislari.user_id','=','users.id')
        ->join('salon_personelleri','paket_satislari.satici_id','=','salon_personelleri.id')
        ->join('hizmetler','paketler.hizmet_id','=','hizmetler.id')
        ->join('isletmeyetkilileri','paket_satislari.olusturan_id','=','isletmeyetkilileri.id')
        ->select(
            DB::raw('DATE_FORMAT(paket_satislari.satis_tarihi,"%d.%m.%Y") as satis_tarihi'),
            'users.name as musteri',
            'salon_personelleri.personel_adi as satici',
            'hizmetler.hizmet_adi as hizmet', 
            DB::raw('CONCAT(paketler.miktar,CASE WHEN paketler.tip=0 THEN " Dakika" ELSE " Seans" END) as miktar'),
            'paket_satislari.kullanilan as kullanilan',
            'paket_satislari.kalan_kullanim as kalan_kullanim',
            'paket_satislari.toplam_tutar as toplam_tutar',
            'paket_satislari.odenen as odenen_tutar',
            'paket_satislari.kalan_tutar as kalan_tutar',
            'isletmeyetkilileri.name as olusturan',
            DB::raw('DATE_FORMAT(paket_satislari.created_at,"%d.%m.%Y") as olusturulma'),
             DB::raw('CONCAT( 
                "<div class=\"dropdown\">
                    <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                        href=\"#\"
                        role=\"button\"
                        data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                        <a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\" data-target=\"#paket-satis-detay-modal\" name=\"islem_olustur\" data-value=\"",paket_satislari.id,"\"><i class=\"fa fa-edit\"></i> Detaylı Bilgi & Düzenle</a>
                        <a class=\"dropdown-item\" href=\"#\" name=\"islem_olusturma\" data-toggle=\"modal\" data-target=\"#paket-islem-kaydi-modal\"  data-value=\"",paket_satislari.id,"\"><i class=\"fa fa-plus\"></i> Yeni Seans Kaydı Ekle</a>
                        
                        </div></div>") as islemler'))->where('paket_satislari.salon_id',Auth::user()->salon_id)->get();
        return $paketsatislari;


        
    }


    public function randevuliste(Request $request)
    {
        $paketler = self::paket_liste_getir('',true);
        $randevular = self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),null,null,null,null);
         return view('isletmeadmin.randevular_liste',['randevular_liste'=>$randevular,'paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'pageindex'=>3,'sayfa_baslik'=>'Randevular']);
    }

    public function randevulistegetir(Request $request)
    {
        
    }

    public function bildirimgetir(Request $request)
    {
        $bildirimler = Bildirimler::where('salon_id',Auth::user()->salon_id)->where('personel_id',Auth::user()->id)->get();
        return $bildirimler;
    }
    public function paketsatisekle(Request $request){
        foreach($request->paketadet as $key=>$paket){
            $paket = "";
            if($request->paket_id[$key] != "")
                $paket = Paketler::where('id',$request->paket_id[$key])->first();
            else
                $paket = new Paketler();
            $paket->tip = $request->pakettip[$key];
            $paket->miktar = $request->paketadet[$key];
            $paket->fiyat = $request->paketfiyat[$key];
            $paket->hizmet_id = $request->pakethizmet[$key];
            $paket->save();
            $paketsatis = new PaketSatislari();
            $paketsatis->paket_id = $paket->id;
            $paketsatis->user_id = $request->musteri;
            $paketsatis->satis_tarihi = $request->paket_satis_tarihi;
            $paketsatis->baslangic_tarihi = $request->paketbaslangictarihi[$key];
            $paketsatis->olusturan_id = Auth::user()->id;
            $paketsatis->salon_id = Auth::user()->salon_id;
            $paketsatis->satici_id = $request->paket_satici;
            $paketsatis->aciklama = $request->paket_satis_notlari;


            $paketsatis->save();
            return self::paketsatislarigetir($request);

        }
    }
    public function alacaklar(Request $request)
    {
        return view('isletmeadmin.alacaklar',['pageindex'=>16,'sayfa_baslik'=>'Alacaklar','bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true),'masraflar'=>self::alacakgetir($request)]);

    }
    public function masrafgetir(Request $request)
    {
        $masraflar = DB::table('masraflar')
        ->join('masraf_kategorileri','masraflar.masraf_kategori_id','=','masraf_kategorileri.id')
        ->join('salon_personelleri','masraflar.harcayan_id','=','salon_personelleri.id')
        ->join('odeme_yontemleri','masraflar.odeme_yontemi_id','=','odeme_yontemleri.id')
        ->join('salonlar','masraflar.salon_id','=','salonlar.id')
        ->select(
            'masraf_kategorileri.kategori as kategori',
            'masraflar.notlar as aciklama',
            'masraflar.tutar as tutar',
            'salon_personelleri.personel_adi as masraf_sahibi',
            'odeme_yontemleri.odeme_yontemi as odeme_yontemi',
            DB::raw('DATE_FORMAT(masraflar.tarih,"%d.%m.%Y") as tarih'),
            DB::raw('DATE_FORMAT(masraflar.created_at,"%d.%m.%Y") as olusturulma'),
            DB::raw('CONCAT(<button onclick=\"modalbaslikata(\'Masraf Düzenleme\',\'\' )\" class=\"btn btn-primary\" href=\"#\" data-toggle=\"modal\" data-target=\"#yeni_masraf_modal\" name=\"masraf_duzenle\" data-value=\"",masraflar.id,"\"><i class=\"fa fa-edit\"></i></button>) as islemler') 
        )->where('salon_id',Auth::user()->salon_id)->get();
        return $masraflar;

    }
    public function masraflar(Request $request)
    {
        return view('isletmeadmin.masraflar',['pageindex'=>15,'sayfa_baslik'=>'Masraflar','bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true),'masraflar'=>self::masrafgetir($request)]);
    }
     public function alacakgetir(Request $request)
    {

          $alacaklar = DB::table('alacaklar')
        ->join('users','alacaklar.user_id','=','users.id')
        ->join('senetler','alacaklar.senet_id','=','senetler.id')
        ->join('paket_satislari','alacaklar.paket_satis_id','=','paket_satislari.id')
        ->join('urun_satislari','alacaklar.urun_satis_id','=','urun_satislari.id')
        ->join('randevular','alacaklar.randevu_id','=','randevular.id')
        ->join('salonlar','alacaklar.salon_id','=','salonlar.id')
        ->select(
            'users.name as musteri',
            DB::raw('CASE WHEN alacaklar.randevu_id IS NOT NULL THEN "Randevu" WHEN alacaklar.paket_satis_id IS NOT NULL then "Paket Satışı" WHEN alacaklar.urun_satis_id IS NOT NULL THEN "Ürün Satışı" WHEN alacaklar.senet_id IS NOT NULL THEN "Senet" ELSE "Belirtilmemiş" END as tip'),
            'alacaklar.tutar as tutar',
             
            DB::raw('DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%d.%m.%Y") as planlanan_odeme_tarihi'),
            DB::raw('DATE_FORMAT(alacaklar.created_at,"%d.%m.%Y") as olusturulma'),
            DB::raw('CONCAT(<button onclick=\"modalbaslikata(\'Alacak Düzenleme\',\'\' )\" class=\"btn btn-primary\"  data-toggle=\"modal\" data-target=\"#yeni_masraf_modal\" name=\"alacak_duzenle\" data-value=\"",alacaklar.id,"\"><i class=\"fa fa-edit\"></i></button>) as islemler') 
        )->where('alacaklar.salon_id',Auth::user()->salon_id)->get();
        return $alacaklar;
    }
    public function masrafekleduzenle(Request $request)
    {
        $masraf = "";
        if(isset($request->masraf_id))
            $masraf = Masraflar::where('id',$request->masraf_id)->first();
        else
            $masraf = new Masraflar();
        $masraf->salon_id = Auth::user()->salon_id;
        $masraf->masraf_kategori_id = $request->masraf_kategorisi;
        $masraf->tarih = $request->tarih;
        $masraf->odeme_yontemi_id = $request->masraf_odeme_yontemi;
        $masraf->harcayan_id = $request->harcayan;
        $masraf->tutar=$request->masraf_tutari;
        $masraf->aciklama = $request->masraf_aciklama;
        $masraf->notlar = $request->masraf_notlari;
        $masraf->save();

    }
    public function alacakekle(Request $request)
    {
        $alacak = "";
        if(isset($request->alacak_id))
            $alacak = Alacaklar::where('id',$request->alacak_id)->first();
        else
            $alacak = new Alacaklar();
        if(isset($request->randevu_id))
            $alacak->randevu_id = $request->randevu_id;
        if(isset($request->paket_satis_id))
            $alacak->paket_satis_id = $request->paket_satis_id;
        if(isset($request->urun_satis_id))
            $alacak->urun_satis_id = $request->urun_satis_id;
        if(isset($request->senet_id))
            $alacak->senet_id = $request->senet_id;
        $alacak->tutar = $request->alacak_tutari;
        $alacak->aciklama = $request->alacak_notlari;
        $alacak->planlanan_odeme_tarihi = $request->planlanan_odeme_tarihi;
        $alacak->olusturan_id = Auth::user()->id;
        $alacak->salon_id = Auth::user()->salon_id;
        $alacak->user_id = $request->musteri;
        $alacak->save();

    }
    public function bildirimkontrolet(Request $request)
    {
        $bildirimler = Bildirimler::where('personel_id',Auth::user()->id)->orderBy('tarih_saat','desc')->get();
        $html = "";
        foreach($bildirimler as $bildirim)
        {
            $html .= '<ul><li>
                
            <a href="#" name="bildirim" data-value="'.$bildirim->id.'">        
             <img src="'.$bildirim->img_src.'" alt="" class="mCS_img_loaded">                         
                                 <h3>';
            if(!$bildirim->okundu)
                $html .= '<b>';
            $html .= $bildirim->aciklama;
            if(!$bildirim->okundu)
                $html .= '</b>';
            $html .='</h3>
            <p style="font-size:12px">';
            $to_time = strtotime(date('Y-m-d H:i:s'));
            $from_time = strtotime($bildirim->tarih_saat);
            $diff = round(abs($to_time - $from_time) / 60,0)." dakika önce";
            if($diff >= 60){
                $diff = round(abs($to_time - $from_time) / 3600,0)." saat önce";
                if(round(abs($to_time - $from_time) / 3600,0) >= 24)
                    $diff = date('d.m.Y H:i',strtotime($bildirim->tarih_saat));
            }
            $html .= $diff."</p>
                              </a>
                           </li>
                          
                        </ul>";
        }
        return array(
            'bildirim_sayisi' => $bildirimler->where('okundu',false)->count(),
            'bildirimler' => $html
        );
        
    }
    public function bildirimokundu(Request $request)
    {
        $bildirim = Bildirimler::where('id',$request->bildirim_id)->first();
        $bildirim->okundu = true;
        $bildirim->save();
        return $bildirim->url;
    }
    public function randevu_liste_filtre(Request $request)
    {
        $tariharaligi = "";
        $web = false;
        $uygulama = false;
        $salon = false;
        if($request->ozeltarih !== '')
            $tariharaligi = $request->ozeltarih;
        else
            $tariharaligi = $request->zaman;
        $tarih = explode('/',$tariharaligi);
        $tarih = explode('-',$tariharaligi);
        if($request->olusturulma == "web")
            $web = true;
        if($request->olusturulma == "salon")
            $salon = true;
        if($request->olusturulma == "uygulama")
            $uygulama = true;
        echo date('Y-m-d',strtotime($tarih[0]))." ".date('Y-m-d',strtotime($tarih[1]))." ".$request->olusturulma;
        //return self::randevu_liste_getir($request,date('Y-m-d',strtotime($tarih[0])),date('Y-m-d',strtotime($tarih[1])),$salon,$web,$uygulama,$request->durum);
    }
    public function liste_deneme(Request $request)
    {
        return self::randevu_liste_getir($request,'2023.05.01','2023.06.07',false,true,false,0);
    }
    public function randevu_liste_getir(Request $request,$tarih1,$tarih2,$salon,$web,$uygulama,$durum)
    {
         $randevular = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->join('users','randevular.user_id','=','users.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->leftjoin('isletmeyetkilileri','randevular.olusturan_personel_id','=','isletmeyetkilileri.id')->select(
                    'users.name as musteri',
                    'users.cep_telefon as telefon',
                    DB::raw("CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') as hizmetler"),
                    
                    'randevular.tarih as tarih',
                    'randevular.saat as saat',
                    'randevular.durum as durum_int',
                    'randevular.web as web_olusturma',
                    'randevular.uygulama as uygulama_olusturma',
                    'randevular.salon as salon_olusturma',
                    DB::raw("CASE WHEN randevular.durum=1 THEN 'Onaylı' WHEN randevular.durum=0 then 'Beklemede' WHEN randevular.durum=3 THEN 'Müşteri Tarafından İptal' ELSE 'İptal' END AS durum"),
                    DB::raw('CONCAT(COALESCE(SUM(randevu_hizmetler.fiyat),0) ," ₺") as toplam'),
                    DB::raw('CASE WHEN randevular.web=1 THEN "Web" WHEN randevular.uygulama=1 THEN "Uygulama" ELSE isletmeyetkilileri.name END as olusturan'),
                    DB::raw('DATE_FORMAT(randevular.tarih, "%d.%m.%Y %H:%i") as olusturulma'),
                    DB::raw('CONCAT("<a title=\"Detaylı Bilgi\" href=\"/isletmeyonetim/randevudetay/",randevular.id," type=\"button\" class=\"btn btn-info\"><i class=\"dw dw-eye\"></i></a>") as islemler'),
                )->where('randevular.salon_id',Auth::user()->salon_id)->groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();
         if($tarih1 !== null && $tarih2 !== null)
            $randevular = $randevular->where('tarih','>=',$tarih1)->where('tarih','<=',$tarih2);
         if($salon)
            $randevular = $randevular->where('salon_olusturma',true);
         if($web)
            $randevular = $randevular->where('web_olusturma',true);
         if($uygulama )
            $randevular = $randevular->where('uygulama_olusturma',true);
        if($durum !== null )
            $randevular = $randevular->where('durum_int',$durum);

         return $randevular;
    }

    public function cikisyap(){
        auth('isletmeyonetim')->logout();

        return redirect('/isletmeyonetim' ); 
    }
        
}
