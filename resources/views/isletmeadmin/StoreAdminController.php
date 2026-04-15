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

use App\CihazHizmetler;

use App\PersonelCalismaSaatleri;

use App\PersonelMolaSaatleri;

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

 

use Spatie\Permission\Models\Role;

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

use App\YetkiliOlunanSubeler;

use App\Etkinlikler;

use App\EtkinlikKatilimcilari;

use App\SalonHizmetKategoriRenkleri;

use App\Cihazlar;

use App\Odalar;

use App\CihazCalismaSaatleri;

use App\CihazMolaSaatleri;

use App\Adisyonlar;

use App\AdisyonHizmetler;

use App\AdisyonUrunler;

use App\AdisyonPaketler;

use App\AdisyonPaketSeanslar;

use App\Senetler;

use App\SenetVadeleri;

use App\SalonCihazRenkleri;

use App\OdaRenkleri;

use App\PaketHizmetler;

use App\SalonSMSAyarlari;

use App\SalonSantralAyarlari;

use App\SMSAyarlari;

use App\SantralAyarlari;

use App\GrupSMS;

use App\GrupSMSKatilimcilari;

use App\OdemeYontemleri;

use App\KampanyaYonetimi;

use App\KampanyaKatilimcilari;

use App\TahsilatHizmetler;

use App\TahsilatUrunler;

use App\TahsilatPaketler;

use App\TaksitliTahsilatlar;

use App\TaksitVadeleri;

use App\Ajanda;

use App\Arsiv;

use App\FormTaslaklari;

use App\Dahililer;

use App\SabitNumaralar;

use App\Islemler;

use App\RandevuHizmetYardimciPersonel;

use App\Uyelik;

use Hash;

use Mail;

use Excel;

use PDF;

use Intervention\Image\Facades\Image as Image;

use Ifsnop\Mysqldump as IMysqldump;

use App\SantralBilgileri;



use Illuminate\Support\Facades\Cache;



 





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

    

    public function mevcutsube($request)

    {

        $sube = "";

        if(isset($request->sube))

            $sube = $request->sube;

        else

        { 

            $subeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();

            if($_SERVER['HTTP_HOST'] != 'webapp.randevumcepte.com.tr'){

                foreach($subeler as $sube_list)

                    if(Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('id') == $sube_list)

                        $sube = $sube_list;

            }

            else

                $sube = $subeler[0];

        }

        return $sube;

    }

    public function sube_yetki_kontrol_et($request)

    {

        $mevcutsube = self::mevcutsube($request);

        if(YetkiliOlunanSubeler::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$mevcutsube)->first()===null){

            return false;

            exit;

        }

        return true;

    }

    public function assing_roles(Request $request)

    {

        $user = Auth::user();

        $user->syncRoles('Hesap Sahibi');



    }

    public function yetki_kontrol(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

     

    }

    public function uyelik(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

       

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        return view('isletmeadmin.uyelik',[

            

            'bildirimler'=>self::bildirimgetir($request),

            'sayfa_baslik'=>isset($request->yenisube) ? Salonlar::where('id',$request->yenisube)->value('salon_adi').' için Üyelik Seçimi' : 'Üyelik', 
             

            'title' => 'Özet | '.Salonlar::where('id',self::mevcutsube($request))->value('salon_adi'),

            'pageindex' => 60,

            

            'isletme'=>$isletme,

            'portfoy_drop'=>self::musteriportfoydropliste($request),

            'urun_drop'=>self::urundropliste($request),

            'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),

            'yetkiliolunanisletmeler'=>$isletmeler,

            'paketler'=>self::paket_liste_getir('',true,$request),

            'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request)

            



        ]); 



    }

    public function index(Request $request)

    {

       

        



        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

            exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        } 

        $randevular = "";

        $randevusayisi = 0;

        $musterisayisi = 0;

         

        $randevular_tum =  self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),true,true,true,null,self::mevcutsube($request),'');

   





        

        $kalan_uyelik_suresi = self::lisans_sure_kontrol($request);

        $randevular_salon =  self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),true,null,null,null,self::mevcutsube($request),'');

        

        $randevular_web = self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),null,true,null,null,self::mevcutsube($request),'');

        

        $randevular_uygulama =  self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),null,null,true,null,self::mevcutsube($request),'');

       

        

        $randevusayisi = $randevular_salon->count() + $randevular_web->count() + $randevular_uygulama->count();

         

       

        $salonyorumlar =  DB::table('salon_yorumlar')->join('salon_puanlar as p1','salon_yorumlar.user_id','=','p1.user_id')->join('salon_puanlar as p2','salon_yorumlar.salon_id','=','p2.salon_id')->join('users','p1.user_id','=','users.id')->

            select(DB::raw('DATE_FORMAT(salon_yorumlar.created_at,"%d.%m.%Y") as tarih'),

                    'users.name as musteri',



                    'salon_yorumlar.yorum as yorum',

                    'p1.puan as puan' , 



            )->where('salon_yorumlar.salon_id',self::mevcutsube($request))->orderBy('salon_yorumlar.created_at','desc')->get();

        

       

        

 

        $gunluk_urun_satislari = 0;

        $gunluk_paket_satislari = 0;

        $alacak_hatirlatmalari = json_encode(array());

        $etkinlikler = json_encode(array());

        $on_gorusmeler = json_encode(array());

        $paketler = json_encode(array());

        $santral_raporlari = array();

        if($isletme->uyelik_turu > 1)

        {

            $gunluk_urun_satislari = 

                 self::adisyon_yukle($request,3,0,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),'','');

            

            $gunluk_paket_satislari = 

                 self::adisyon_yukle($request,2,0,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),'','');

          

            $santral_raporlari = 

                 self::santral_raporlari($isletme->id,date('Y-m-d'),date('Y-m-d'),'',$request);

          

            

            



            $alacak_hatirlatmalari = 

                 DB::table('alacaklar')->join('users','alacaklar.user_id','=','users.id')->select('users.name as musteri','alacaklar.planlanan_odeme_tarihi as planlanan_odeme_tarihi',DB::raw('FORMAT(alacaklar.tutar,2,"tr_TR") as tutar'))->where('alacaklar.salon_id',self::mevcutsube($request))->where('planlanan_odeme_tarihi','=',date('Y-m-d'))->get();

          





            if($isletme->uyelik_turu > 2)

                $etkinlikler =

                     DB::table('etkinlikler')->join('etkinlik_katilimcilari','etkinlikler.id','=','etkinlik_katilimcilari.etkinlik_id')->select('etkinlikler.etkinlik_adi as etkinlik_adi',

                       DB::raw('etkinlikler.fiyat*COUNT(etkinlik_katilimcilari.user_id) as toplam_tutar'),

                       DB::raw('COUNT(etkinlik_katilimcilari.user_id) as katilimci_sayisi')



                    )->where('etkinlikler.tarih_saat','like','%'.date('Y-m-d').'%')



                ->where('etkinlikler.salon_id',self::mevcutsube($request))->groupBy('etkinlik_katilimcilari.etkinlik_id')->get();

            

            $on_gorusmeler = 

                 self::ongorusmegetir($request,true);

              



           

        }

           



        

        

           

        

        

          

          

       return view('isletmeadmin.dashboard',[

            

            'bildirimler'=>self::bildirimgetir($request),

            'sayfa_baslik'=>'Özet',

            'randevular_tum'=>$randevular_tum,

            'randevular_salon'=>$randevular_salon,

            'randevular_web'=>$randevular_web,

            'randevular_uygulama'=>$randevular_uygulama,

             

            'randevusayisi'=>$randevusayisi,

            'salonyorumlar' => $salonyorumlar,

             

            'title' => 'Özet | '.Salonlar::where('id',self::mevcutsube($request))->value('salon_adi'),

            'pageindex' => 1,

            'on_gorusmeler'=>$on_gorusmeler,

            'isletme'=>$isletme,

            'portfoy_drop'=>self::musteriportfoydropliste($request),

             

            'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()), 

             

            'alacak_hatirlatmalari'=>$alacak_hatirlatmalari,

           

            'etkinlikler'=>$etkinlikler,

            'gunluk_urun_satislari'=>$gunluk_urun_satislari,

            'gunluk_paket_satislari'=>$gunluk_paket_satislari,

            'kalan_uyelik_suresi' => $kalan_uyelik_suresi,

            'santral_raporlari'=>$santral_raporlari,

            'yetkiliolunanisletmeler'=>$isletmeler,

            'musteridanisanarama'=> self::musteridanisanarama($request)

            



        ]);



        

    }

    public function musteridanisanarama(Request $request)

    {

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $html = '<option  value="0">';

        if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) 

            $html .= 'Danışan ';

        else

            $html .= 'Müşteri ';

        $html .= 'Arayın...</option>';

        foreach(MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri){



            $html .= '<option value="/isletmeyonetim/musteridetay/'.$mevcutmusteri->user_id;

            if(isset($_GET['sube']))

                $html .= '?sube='.$isletme->id;

            $html .= '">'.$mevcutmusteri->users->name;

            if($mevcutmusteri->users->cep_telefon != '' && $mevcutmusteri->users->cep_telefon !== null)

                $html .= '('.$mevcutmusteri->users->cep_telefon.')';

            $html .='</option>';

        }



      

        return $html;     

    }

    public function acikadisyonlar(Request $request){

        $acik_adisyonlar = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->join('users','randevular.user_id','=','users.id')->leftJoin('urun_satislari','urun_satislari.randevu_id','=','randevular.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->leftJoin('urunler','urun_satislari.urun_id','=','urunler.id')->select(

                    'users.name as musteri',

                    DB::raw("CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') as hizmetler"),

                    DB::raw('GROUP_CONCAT(urunler.urun_adi) as urunler'),

                    DB::raw('SUM(randevu_hizmetler.fiyat) + COALESCE(SUM(urun_satislari.fiyat), 0) as toplam'),

                    'randevular.tarih as tarih',

                    DB::raw('CONCAT("<a title=\"Detaylı Bilgi\" href=\"/isletmeyonetim/musteridetay/",randevular.id," type=\"button\" class=\"btn btn-info\"><i class=\"dw dw-eye\"></i></a>") as islemler'),

                )->where('randevular.salon_id',self::mevcutsube($request))->where('randevular.acik',true)->whereMonth('randevular.tarih','=',date('m'))->groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();

        return $acik_adisyonlar;

    }

    public function randevular(Request $request){ 

         $isletmeler =  

             Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();

      

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $randevular = '';

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0|| Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))

            $randevular = self::randevuyukle($request,1,date('Y-m-d'),date('Y-m-d'));

        else    

            $randevular = self::randevuyukle($request,Salonlar::where('id',self::mevcutsube($request))->value('randevu_takvim_turu'),date('Y-m-d'),date('Y-m-d'));

         

         

               

        $kalan_uyelik_suresi = 

             self::lisans_sure_kontrol($request);

       

        $hizmet_drop = 

             self::hizmetdropliste($request);

     

        $personel_drop = 

             self::personeldropliste($request,array());

         

        $portfoy_drop = 

             self::musteriportfoydropliste($request);

       

        

       

        $musteridanisanarama = 

              self::musteridanisanarama($request);

       



        

               

         

        return view('isletmeadmin.randevular',['bildirimler'=>self::bildirimgetir($request),  'sayfa_baslik'=>'Randevu Takvimi','pageindex' => 2,'randevular'=>$randevular,'isletme'=>$isletme,'kalan_uyelik_suresi'=>$kalan_uyelik_suresi,'portfoy_drop'=>$portfoy_drop, 'hizmet_drop'=>$hizmet_drop,'personel_drop'=>$personel_drop,'yetkiliolunanisletmeler'=>$isletmeler,'musteridanisanarama'=>$musteridanisanarama]);

    }

    public function randevular_test(Request $request){

 

        $personeller = "";

        $randevular = '';

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

            $randevular = self::randevuyukle($request,1,date('Y-m-d'),date('Y-m-d'));

        else    

            $randevular = self::randevuyukle($request,Salonlar::where('id',self::mevcutsube($request))->value('randevu_takvim_turu'),date('Y-m-d'),date('Y-m-d'));

        $hizmetler = SalonHizmetler::where('salon_id',self::mevcutsube($request))->where('aktif',true)->get();

      

        $mevcutmusteriler = MusteriPortfoy::where('salon_id',self::mevcutsube($request))->get();

        $paketler = self::paket_liste_getir('',true,$request);

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        

        $personeller = Personeller::where('salon_id',self::mevcutsube($request))->get();

       

         echo 'metod bitti';

    }

    public function randevularfiltre(Request $request){

        $randevular = "";

        if(Auth::guard('isletmeyonetim')->user()->is_admin)

            $randevular = Randevular::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like','%'.$request->tarih.'%')->where('sube_id','like','%'.$request->sube.'%')->orderBy('id','desc')->get();

        else

            $randevular = Randevular::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like','%'.$request->tarih.'%')->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->orderBy('id','desc')->get();



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

        $html = '<table style="width:100%;margin:0 0 10px 0"><tr><td>Telefon</td><td>:</td><td>'.$randevu->users->cep_telefon.'</td></tr>

                    <tr><td>Hizmet</td><td>:</td><td>';

        foreach($randevu->hizmetler as $hizmet)

        {

            $html.=$hizmet->hizmetler->hizmet_adi .' ('.$hizmet->personeller->personel_adi.') <br>';



        }



        $html.="</td></tr><tr><td>Zaman</td><td>:</td><td>".date('d.m.Y', strtotime($randevu->tarih))." ".date('H:i', strtotime($randevu->saat)). "</td></tr>

            <tr><td>Oluşturan</td><td>:</td><td>";

        if($randevu->web)

            $html.="Web";

        if($randevu->uygulama)

            $html.="Uygulama";

        if($randevu->salon)

            $html.= "Salon (".$randevu->olusturan_personel->name.")";

        $html .= '

            </td></tr>

            <tr><td>Geldi mi?</td><td>:</td><td>';

        if($randevu->randevuya_geldi===true)

            $html.="Geldi";

        elseif($randevu->randevuya_geldi===false)

            $html.="Gelmedi";

        else

            $html.="Belirtilmemiş";

        $html ."</td></tr>

                 Müşteri Notu</td><td>:</td><td>".$randevu->notlar."</td></tr>

                      <tr><td>Personel Notu</td><td>:</td><td>".$randevu->personel_notu."</td></tr>

                    </table>";

        $buttons = "";

        if($randevu->durum===1)



            $buttons = '<div class="row">

                                <div class="col-3 col-xs-3 col-sm-3"><a name="randevu_duzenle" href="#" class="btn btn-primary btn-block btn-lg" data-value="'.$randevu->id.'"> Düzenle</a></div>

                                <div class="col-3 col-xs-3 col-sm-3"><a name="gelmedi_isaretle" href="#" class="btn btn-danger btn-block btn-lg" data-value="'.$randevu->id.'"> Gelmedi</a></div>

                                <div class="col-3 col-xs-3 col-sm-3"><a name="geldi_isaretle" href="#" class="btn btn-success btn-block btn-lg" data-value="'.$randevu->id.'"> Geldi & Tahsilat</a></div>

                                <div class="col-3 col-xs-3 col-sm-3"><button class="btn btn-danger btn-block btn-lg randevuiptalet" data-value="'.$randevu->id.'"> İptal Et</button></div></div>';



        if($randevu->durum === 0)

            $buttons = '<div class="row"><div class="col-6 col-xs-6 col-sm-6"><button class="btn btn-success btn-block btn-lg randevuonayla" data-value="'.$randevu->id.'"> Onayla</a></div><div class="col-6 col-xs-6 col-sm-6"><button class="btn btn-danger btn-block btn-lg randevuiptalet" data-value="'.$randevu->id.'"> İptal Et</button></div></div>';

        if($request->bildirim_okundu)

        {

            $bildirim = Bildirimler::where('id',$request->bildirimid)->first();

            if($bildirim){

                $bildirim->okundu = true;

                $bildirim->save();

            } 

        }

        return array(

            'randevu_icerik' => $html,

            'butonlar' => $buttons,

            'ad_soyad' => $randevu->users->name

        );

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

            $kasa->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

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

        $rapor->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

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



        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $tarih = "";

        if(isset($request->tarih))

            $tarih = $request->tarih;

        else

            $tarih = date('Y-m-d');



        

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

         $kalan_uyelik_suresi = self::lisans_sure_kontrol($request);        

      

        $sunulanhizmetler = SalonHizmetler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->groupBy('hizmet_id')->get();

         

        $personeller = "";

        $hizmetler = SalonHizmetler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $subeler = Subeler::where('salon_id',$isletme->id)->get();

        

        $islemler = "";

        $gelen_musteri = 0;

        $alinan_odeme = 0;

        $kalan_odeme = 0;

        if(Auth::guard('isletmeyonetim')->user()->is_admin){

            $islemler = Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like','%'.$tarih.'%')->orderBy('id','desc')->get();



            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        }

        else{

            $islemler = Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->where('tarih','like','%'.$tarih.'%')->orderBy('id','desc')->get();

            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->get();

        }

        foreach($islemler as $islem){

            $alinan_odeme = $alinan_odeme + $islem->alinan_odeme;

            $kalan_odeme = $kalan_odeme + $islem->kalan_odeme;



        }

        $gelen_musteri = Islemler::where('tarih','like','%'.$tarih.'%')->where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->distinct()->pluck('user_id')->count();



        





        $mevcutmusteriler = Randevular::join('users','randevular.user_id','=','users.id')->where('randevular.salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->groupBy('randevular.user_id')->get();

        return view('isletmeadmin.yapilanislemler',[ 'title' =>  'Günlük Genel Raporlar & Yapılan İşlemler | Aleyna Pelit Beauty Studio','pageindex' => 200,'sunulanhizmetler'=>$sunulanhizmetler,'isletme'=>$isletme,'personeller'=> $personeller,  'hizmetler'=> $hizmetler,'mevcutmusteriler'=>$mevcutmusteriler,'subeler'=>$subeler,'gelen_musteri'=>$gelen_musteri,'alinan_odeme'=>$alinan_odeme,'kalan_odeme'=>$kalan_odeme,'islemler'=>$islemler,'tarih'=>$tarih, 'kalan_uyelik_suresi'=>$kalan_uyelik_suresi,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);





     }

     public function islemraporlari_filtre(Request $request){

        $islemlar = "";

        $gelen_musteri = 0;

        $alinan_odeme = 0;

        $kalan_odeme = 0;

        $personeller = "";

        if(Auth::guard('isletmeyonetim')->user()->is_admin){





            if($request->sube != 0){

                $islemlar = Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',$request->sube)->orderBy('id','desc')->get();

                $gelen_musteri =  Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',$request->sube)->distinct()->pluck('user_id')->count();

                $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('sube_id',date('Y-m-d', strtotime($request->tarih)))->get();



            }



              

            else{

                $islemlar = Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->orderBy('id','desc')->get();

                $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

                $gelen_musteri =  Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->distinct()->pluck('user_id')->count();

            }

            

        }

        else{

            $islemlar = Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->orderBy('id','desc')->get();

             $gelen_musteri =  Islemler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','like', '%'.$request->tarih.'%')->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->distinct()->pluck('user_id')->count();

            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->get();

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

        $subeler = Subeler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $musteriler = MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $personeller = "";

        $hizmetler = SalonHizmetler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $sube_html = "";

        $musteri_html = "";

        $personel_html = "";

        $hizmet_html = "";



        $islemhizmetler = explode('<br>',$islem->yapilan_islemler);

        if(Auth::guard('isletmeyonetim')->user()->is_admin)

            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        else

            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->get();



        

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

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $avantajlar = SalonKampanyalar::where('salon_id',$isletme->id)->get();



        return view ('isletmeadmin.kampanyaraporlar',['title' => 'Avantaj Raporları |Avantajbu.com','pageindex' => 1052,'isletme' => $isletme,'avantajlar' => $avantajlar,'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function toplusmsgonder(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $portfoy = MusteriPortfoy::where('salon_id',self::mevcutsube($request))->groupBy('user_id')->get();

        $sms_ayarlari = SalonSMSAyarlari::where('salon_id',self::mevcutsube($request))->get();

        $taslaklar = SMSTaslaklari::where('salon_id',self::mevcutsube($request))->orWhere('salon_id',null)->get();

        $paketler = self::paket_liste_getir("",true,$request);

        $grup=self::grup_sms_liste_getir($request);

        $raporlar =self::sms_raporlari($request);

        $kredi = self::sms_bakiye_sorgulama($request);

        $karaliste = DB::table('users')->join('musteri_portfoy','musteri_portfoy.user_id','=','users.id')->select(

            'users.name as ad_soyad', 

            'users.cep_telefon as telefon',

            DB::raw('DATE_FORMAT(musteri_portfoy.updated_at,"%d.%m.%Y") as eklenme_tarihi'),

            DB::raw('CONCAT("<button class=\"btn btn-primary\" name=\"numara_karalisteden_kaldir\" data-value=\"",users.id,"\">Numarayı Listeden Kaldır</button>") AS islemler')



        )->where('musteri_portfoy.salon_id',self::mevcutsube($request))->where('musteri_portfoy.kara_liste',1)->get();

        return view('isletmeadmin.toplusmsgonder',['paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'title' => 'Toplu SMS Gönder','pageindex' => 106,'isletme'=>$isletme,'portfoy' => $portfoy,'taslaklar'=>$taslaklar,'grup'=>$grup,'sms_ayarlari'=>$sms_ayarlari,'raporlar'=>$raporlar,'karaliste'=>$karaliste,'kredi'=>$kredi,'sayfa_baslik'=>'SMS Yönetimi' , 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function smslistesi(){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $listeler = SMSListeleri::where('user_id',Auth::guard('isletmeyonetim')->user()->id)->get();

         $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        return view('isletmeadmin.smslistesi',['title' => 'Toplu SMS Listelerim | Avantajbu.com','pageindex' => 107,'isletme'=>$isletme,'listeler'=> $listeler, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function toplumailgonder(){

        /*$isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        return view('isletmeadmin.toplumailgonder',['title' => 'Toplu Mail Gönder | Avantajbu.com','pageindex' => 110,'isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array())]);*/

    }

    public function ayarlar(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $subeler =  Salonlar::whereIn('id',$isletmeler)->get();

        $salonhizmetler = SalonHizmetler::where('salon_id',self::mevcutsube($request))->get();

        $personeller = self::personel_liste_getir($request);

        $saloncalismasaatleri = SalonCalismaSaatleri::where('salon_id',self::mevcutsube($request))->orderBy('haftanin_gunu','asc')->get();

        $salonmolasaatleri = SalonMolaSaatleri::where('salon_id',self::mevcutsube($request))->orderBy('haftanin_gunu','asc')->get();

        $salongorselleri = SalonGorselleri::where('salon_id',self::mevcutsube($request))->where('kapak_fotografi','!=',1)->get();

        $gorseller_html = self::salon_gorselleri($request);

        $salongorselkapak = SalonGorselleri::where('salon_id',self::mevcutsube($request))->where('kapak_fotografi',1)->first();

        $etiketler = AramaTerimleri::where('salon_id',self::mevcutsube($request))->orderBy('id','asc')->get();

        $isletmeturu = SalonTuru::all();  

        $hizmetler = Hizmetler::all();

        $isletmeturu_html = "";

        $aramaterimleri = AramaTerimleri::where('salon_id',self::mevcutsube($request))->get();





       



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

 

      

 

        $urunler = self::urun_liste_getir($request,"");

        $hizmetler = self::hizmet_liste_getir($request,"","");

        $paketler = self::paket_liste_getir("",true,$request);

        $paketler_liste = self::paket_liste_getir("",false,$request);

        $cihazlar=Cihazlar::where('salon_id',self::mevcutsube($request))->where('aktifmi',true)->get();

        $odalar=Odalar::where('salon_id',self::mevcutsube($request))->where('aktifmi',true)->get();

        return view('isletmeadmin.ayarlar',['hizmetler'=>$hizmetler,'bildirimler'=>self::bildirimgetir($request),'sayfa_baslik' => 'Hesap Ayarları','pageindex' => 9,'salongorselleri'=> $salongorselleri,'saloncalismasaatleri'=>$saloncalismasaatleri,'personeller' => $personeller, 'salonhizmetler' => $salonhizmetler,'isletme'=> $isletme,'sayfa_baslik' => $isletme->salon_adi.' | Detayları & Düzenle', 'etiketler' => $etiketler,'isletmeturulistesi' => $isletmeturu_html,'gorseller_html' => $gorseller_html,'hizmetlistesi'=>$hizmetlistesi_html,'salongorselkapak'=>$salongorselkapak,'subeler'=>$subeler,'salonmolasaatleri'=>$salonmolasaatleri,'urunler'=> $urunler,'paketler'=>$paketler,'paketler_liste'=>$paketler_liste,'roller'=>Role::all(),'cihazlar'=>$cihazlar,'odalar'=>$odalar,'aramaterimleri'=>$aramaterimleri, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }



    public function randevuyukle(Request $request,$takvim_turu,$tarih1,$tarih2){

        $randevu_hizmetler = "";

        $resources = ""; 

        

        $personel_idler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->where('salon_id',self::mevcutsube($request))->pluck('id');

        if($takvim_turu == 1 || DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0){

            if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

                $randevu_hizmetler = DB::table('randevu_hizmetler')

            ->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')

            

           

            ->leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')

            ->leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')

            ->leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')

            ->join('users','randevular.user_id','=','users.id')



            ->join('hizmetler','randevu_hizmetler.hizmet_id','hizmetler.id')

            ->join('renk_duzenleri','salon_personelleri.renk','=','renk_duzenleri.id')

            ->leftjoin('isletmeyetkilileri','randevular.olusturan_personel_id','=','isletmeyetkilileri.id')

            ->leftjoin('on_gorusmeler','randevular.on_gorusme_id','on_gorusmeler.id')                                   

            ->leftjoin('paketler','on_gorusmeler.paket_id','paketler.id')

            ->leftjoin('urunler','on_gorusmeler.urun_id','urunler.id')

             

            ->select('randevular.id as randevu_id','randevular.user_id as userid','randevu_hizmetler.id as id',

                

                    DB::raw('CONCAT("#ffffff") as borderColor'),

                    DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE 

                CONCAT(

                    users.name, 

                    CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " (PAKET) " ELSE "" END, 

                    CASE WHEN randevular.on_gorusme_id > 0 then " (ÖN GÖRÜŞME) " else "" end,

                    CASE WHEN randevular.on_gorusme_id is not null THEN 

                    CONCAT("\nÖn Görüşme Nedeni:",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,"")) 

                    ELSE CONCAT(   

                        "\n",hizmetler.hizmet_adi

                    )END

                ) END as title'),



           DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE CONCAT(users.name, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " Paket Randevusu Detayları " ELSE "" END , CASE WHEN randevular.on_gorusme_id > 0 then " Ön Görüşme Randevusu Detayları" else "" end, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) = 0 AND randevular.on_gorusme_id is null THEN " Randevu Detayları" ELSE "" END ) END as modal_title'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null  



                    then 



                    TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>

                    <tr><td>Ön Görüşme Nedeni</td><td>:</td><td>",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,""),"</td></tr>

                    <tr><td>Görüşmeyi Yapan</td><td>:</td><td>",COALESCE(salon_personelleri.personel_adi,""),"</td></tr>

                    

                    <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                    <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                    <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                    <tr><td>Durum</td><td>:</td><td>",CASE WHEN on_gorusmeler.durum=1 THEN "Satış Yapıldı" WHEN on_gorusmeler.durum is null THEN "Beklemede" ELSE "Satış Yapılmadı" END,"</td></tr>

                       

                       

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(on_gorusmeler.aciklama,""),"</td></tr>

                    </table>"))  

                    

                   



                    ELSE 



                     TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>



                    <tr><td>Hizmet</td><td>:</td><td>",COALESCE(hizmetler.hizmet_adi,""),"</td></tr>

                      <tr><td>Yardımcı Personel(-ler)</td><td>:</td><td>",COALESCE((select group_concat(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id where randevu_hizmetler.yardimci_personel = 1 and randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id group by randevu_hizmetler.randevu_id),"Belirtilmemiş"),"</td></tr>

                      

                      

                    <tr><td>Cihaz</td><td>:</td><td>",COALESCE(cihazlar.cihaz_adi,""),"</td></tr>



                    <tr><td>Oda</td><td>:</td><td>",COALESCE(odalar.oda_adi,""),"</td></tr>

                     <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                        <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                       <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                        <tr><td>Geldi mi?</td><td>:</td><td>",CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" WHEN randevular.randevuya_geldi=0 THEN "Gelmedi" ELSE "Belirtilmemiş" END,"</td></tr>

                       <tr><td>Fiyat (₺)</td><td>:</td><td>",COALESCE(randevu_hizmetler.fiyat,""),"</td></tr>

                      <tr><td>Müşteri Notu</td><td>:</td><td>",COALESCE(randevular.notlar,""),"</td></tr>

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(randevular.personel_notu,""),"</td></tr>

                    </table>")) end as description'),





                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null THEN TRIM(CONCAT("<a onclick=\"modalbaslikata(\"Ön Görüşme Düzenleme\",\"\" )\" class=\"btn btn-primary btn-block btn-lg\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>")) ELSE TRIM(CONCAT("<a data-toggle=\"modal\" data-target=\"#randevu-duzenle-modal\" name=\"randevu_duzenle\" href=\"#\" class=\"btn btn-primary\" data-value=\"",randevular.id,"\"> Düzenle</a>")) END as duzenle_buton'),

                    DB::raw(' CASE WHEN randevular.durum=1 AND randevular.randevuya_geldi IS NOT TRUE THEN 

                    (CASE WHEN randevular.on_gorusme_id is not null THEN



                    TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-success btn-block btn-lg\" href=\"#\" name=\"", (CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN "satis_yapildi" ELSE "urun_satis_yapildi" END)  ,"\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-danger btn-block btn-lg\" href=\"#\" name=\"satis_yapilmadi\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-times\"></i> Satış Yapılmadı</a></div></div>"))



                     ELSE TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"tahsil_et\" href=\"#\" class=\"btn btn-primary btn-block btn-lg\" data-value=\"",randevular.id,"\"> Tahsilat</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>")) END)

                    



                    WHEN randevular.durum=0 THEN  TRIM(CONCAT("<div class=\"row\"><div class=\"col-6 col-xs-6 col-sm-6\"><button data-value=\"",randevular.id,"\" class=\"btn btn-success btn-block btn-lg randevuonayla\" data-value=\"",randevular.id,"\"> Onayla</a></div><div class=\"col-6 col-xs-6 col-sm-6\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))







                    END as eventbuttons'),





                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),



                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),

              DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" WHEN randevular.randevuya_geldi=0 then "#ff0000" WHEN randevular.randevuya_geldi=1 THEN "#008000" ELSE renk_duzenleri.renk END as color')

                ,'randevu_hizmetler.personel_id as resourceId')->where('randevular.salon_id',self::mevcutsube($request))->where('randevular.durum','<',2)->where(function($q) use($personel_idler,$request){ if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) $q->whereIn('randevu_hizmetler.personel_id',$personel_idler); })->get();

            else

                  $randevu_hizmetler = DB::table('randevu_hizmetler')

            ->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')

             

             

            ->leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')

            ->leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')

            ->leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')

            ->join('users','randevular.user_id','=','users.id')



            ->join('hizmetler','randevu_hizmetler.hizmet_id','hizmetler.id')

            ->join('renk_duzenleri','salon_personelleri.renk','=','renk_duzenleri.id')

            ->leftjoin('isletmeyetkilileri','randevular.olusturan_personel_id','=','isletmeyetkilileri.id')

            ->leftjoin('on_gorusmeler','randevular.on_gorusme_id','on_gorusmeler.id')                                   

            ->leftjoin('paketler','on_gorusmeler.paket_id','paketler.id')

            ->leftjoin('urunler','on_gorusmeler.urun_id','urunler.id')

            ->select('randevular.id as randevu_id','randevular.user_id as userid','randevu_hizmetler.id as id', 

                DB::raw('CONCAT("#ffffff") as borderColor'),

                 DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE 

                CONCAT(

                    users.name, 

                    CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " (PAKET) " ELSE "" END, 

                    CASE WHEN randevular.on_gorusme_id > 0 then " (ÖN GÖRÜŞME) " else "" end,

                    CASE WHEN randevular.on_gorusme_id is not null THEN 

                    CONCAT("\nÖn Görüşme Nedeni:",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,"")) 

                    ELSE CONCAT(   

                        "\n",hizmetler.hizmet_adi

                    )END

                ) END as title'),



             DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE CONCAT(users.name, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " Paket Randevusu Detayları " ELSE "" END , CASE WHEN randevular.on_gorusme_id > 0 then " Ön Görüşme Randevusu Detayları" else "" end, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) = 0 AND randevular.on_gorusme_id is null THEN " Randevu Detayları" ELSE "" END ) END as modal_title'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null  



                    then 



                    TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>

                    <tr><td>Ön Görüşme Nedeni</td><td>:</td><td>",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,""),"</td></tr>

                    <tr><td>Görüşmeyi Yapan</td><td>:</td><td>",COALESCE(salon_personelleri.personel_adi,""),"</td></tr>

                    

                    <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                    <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                    <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                    <tr><td>Durum</td><td>:</td><td>",CASE WHEN on_gorusmeler.durum=1 THEN "Satış Yapıldı" WHEN on_gorusmeler.durum is null THEN "Beklemede" ELSE "Satış Yapılmadı" END,"</td></tr>

                       

                       

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(on_gorusmeler.aciklama,""),"</td></tr>

                    </table>"))  

                    

                   



                    ELSE 



                     TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>



                    <tr><td>Hizmet</td><td>:</td><td>",COALESCE(hizmetler.hizmet_adi,""),"</td></tr>

                    <tr><td>Personel</td><td>:</td><td>",COALESCE((select group_concat(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id where randevu_hizmetler.yardimci_personel is null and randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id group by randevu_hizmetler.randevu_id),"Belirtilmemiş"),"</td></tr>

                    <tr><td>Yardımcı Personel(-ler)</td><td>:</td><td>",COALESCE((select group_concat(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id where randevu_hizmetler.yardimci_personel = 1 and randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id group by randevu_hizmetler.randevu_id),"Belirtilmemiş"),"</td></tr>

                      

                    <tr><td>Cihaz</td><td>:</td><td>",COALESCE(cihazlar.cihaz_adi,""),"</td></tr>



                    <tr><td>Oda</td><td>:</td><td>",COALESCE(odalar.oda_adi,""),"</td></tr>

                     <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                        <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                       <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                        <tr><td>Geldi mi?</td><td>:</td><td>",CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" WHEN randevular.randevuya_geldi=0 THEN "Gelmedi" ELSE "Belirtilmemiş" END,"</td></tr>

                       <tr><td>Fiyat (₺)</td><td>:</td><td>",COALESCE(randevu_hizmetler.fiyat,""),"</td></tr>

                      <tr><td>Müşteri Notu</td><td>:</td><td>",COALESCE(randevular.notlar,""),"</td></tr>

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(randevular.personel_notu,""),"</td></tr>

                    </table>")) end as description'),

                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null THEN TRIM(CONCAT("<a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"btn btn-primary btn-block btn-lg\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>")) ELSE TRIM(CONCAT("<a data-toggle=\"modal\" data-target=\"#randevu-duzenle-modal\" name=\"randevu_duzenle\" href=\"#\" class=\"btn btn-primary\" data-value=\"",randevular.id,"\"> Düzenle</a>")) END as duzenle_buton'),

                    DB::raw('CASE 

                                WHEN randevular.on_gorusme_id is not null 

                                THEN TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-success btn-block btn-lg\" href=\"#\" name=\"", (CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN "satis_yapildi" ELSE "urun_satis_yapildi" END)  ,"\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-danger btn-block btn-lg\" href=\"#\" name=\"satis_yapilmadi\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-times\"></i> Satış Yapılmadı</a></div></div>"))

                                WHEN randevular.durum=0 THEN 

                                TRIM(CONCAT("<div class=\"row\"><div class=\"col-6 col-xs-6 col-sm-6\"><button data-value=\"",randevular.id,"\" class=\"btn btn-success btn-block btn-lg randevuonayla\" data-value=\"",randevular.id,"\"> Onayla</a></div><div class=\"col-6 col-xs-6 col-sm-6\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))



                                ELSE 

                                  TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"tahsil_et\" href=\"#\" class=\"btn btn-primary btn-block btn-lg\" data-value=\"",randevular.id,"\"> Tahsilat</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))      

                                END as eventbuttons'),



                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),



                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),



                 DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" WHEN randevular.randevuya_geldi=0 then "#ff0000" WHEN randevular.randevuya_geldi=1 THEN "#008000" ELSE renk_duzenleri.renk END as color'),

               

               'randevu_hizmetler.personel_id as resourceId',

                

              //  DB::raw('JSON_ARRAY(COALESCE(GROUP_CONCAT(CAST(yp.id AS CHAR)  ),""),COALESCE( CAST(randevu_hizmetler.personel_id AS CHAR),"" )  ) as resourceIds')

              //  DB::raw('CONCAT("[","\'",COALESCE(randevu_hizmetler.personel_id,"\'"),"\'", ","  , GROUP_CONCAT(QUOTE(COALESCE(yp.id,"" ))), "]"  ) as resourceIds')



             )->where('randevular.tarih','>=',$tarih1)->where('randevular.tarih','<=',$tarih2)->where('randevular.salon_id',self::mevcutsube($request))->where('randevular.durum','<',2)->where(function($q) use($personel_idler,$request){ if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) $q->whereIn('randevu_hizmetler.personel_id',$personel_idler); })->get();

            $resources = Personeller::join('renk_duzenleri','salon_personelleri.renk','=','renk_duzenleri.id')

                       

                        ->where(function($q) use($request,$personel_idler){ 

                            if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) {

                                $q->whereIn('salon_personelleri.id',$personel_idler); 



                            }

                            else{

                                $q->where('salon_personelleri.salon_id',self::mevcutsube($request));

                         

                                $q->orWhere('salon_personelleri.id',183);

                            } 

                        })->where('salon_personelleri.aktif',true) 

                        ->where('salon_personelleri.takvimde_gorunsun',true)->orderBy('salon_personelleri.takvim_sirasi','asc')

                        ->get(['salon_personelleri.id as id','salon_personelleri.personel_adi as title','renk_duzenleri.renk as bgcolor']);

        }

        if($takvim_turu == 0 && DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() == 0){ 

            $randevu_hizmetler = DB::table('randevu_hizmetler')->

            join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')

            

            ->join('users','randevular.user_id','=','users.id')->

            leftjoin('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->

            join('hizmet_kategorisi','hizmetler.hizmet_kategori_id','=','hizmet_kategorisi.id')->

            join('salon_hizmet_kategori_renkleri','salon_hizmet_kategori_renkleri.hizmet_kategori_id','=','hizmet_kategorisi.id')->

            join('renk_duzenleri','salon_hizmet_kategori_renkleri.renk_id','=','renk_duzenleri.id')

            ->leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')

            

            ->leftjoin('isletmeyetkilileri','randevular.olusturan_personel_id','=','isletmeyetkilileri.id')

            ->leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')

            ->leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')

            ->leftjoin('on_gorusmeler','randevular.on_gorusme_id','on_gorusmeler.id')                                   

            ->leftjoin('paketler','on_gorusmeler.paket_id','paketler.id')

            ->leftjoin('urunler','on_gorusmeler.urun_id','urunler.id')

            ->select('randevular.id as randevu_id','randevular.user_id as userid','randevu_hizmetler.id as id',

                DB::raw('CONCAT(randevu_hizmetler.randevu_id,randevu_hizmetler.hizmet_id,DATE_FORMAT(randevu_hizmetler.saat, "%H:%i"),DATE_FORMAT(randevu_hizmetler.saat_bitis, "%H:%i")) as hizmetgrubu'),

                DB::raw('CONCAT("#ffffff") as borderColor'),

                  DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE 

                CONCAT(

                    users.name, 

                    CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " (PAKET) " ELSE "" END, 

                    CASE WHEN randevular.on_gorusme_id > 0 then " (ÖN GÖRÜŞME) " else "" end,

                    CASE WHEN randevular.on_gorusme_id is not null THEN 

                    CONCAT("\nÖn Görüşme Nedeni:",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,"")) 

                    ELSE CONCAT(   

                        "\n",hizmetler.hizmet_adi

                    )END

                ) END as title'),



               DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE CONCAT(users.name, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " Paket Randevusu Detayları " ELSE "" END , CASE WHEN randevular.on_gorusme_id > 0 then " Ön Görüşme Randevusu Detayları" else "" end, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) = 0 AND randevular.on_gorusme_id is null THEN " Randevu Detayları" ELSE "" END ) END as modal_title'),

 



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null  



                    then 



                    TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>

                    <tr><td>Ön Görüşme Nedeni</td><td>:</td><td>",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,""),"</td></tr>

                    <tr><td>Görüşmeyi Yapan</td><td>:</td><td>",COALESCE(salon_personelleri.personel_adi,""),"</td></tr>

                    

                    <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                    <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                    <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                    <tr><td>Durum</td><td>:</td><td>",CASE WHEN on_gorusmeler.durum=1 THEN "Satış Yapıldı" WHEN on_gorusmeler.durum is null THEN "Beklemede" ELSE "Satış Yapılmadı" END,"</td></tr>

                       

                       

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(on_gorusmeler.aciklama,""),"</td></tr>

                    </table>"))  

                    

                   



                    ELSE 



                     TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>



                    <tr><td>Hizmet</td><td>:</td><td>",COALESCE(hizmetler.hizmet_adi,""),"</td></tr>

                    <tr><td>Personel</td><td>:</td><td>",(COALESCE((SELECT GROUP_CONCAT(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id  where randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id and randevu_hizmetler.yardimci_personel is null),"Belirtilmemiş")),"</td></tr>

                        <tr><td>Yardımcı Personel(-ler)</td><td>:</td><td>", (COALESCE((SELECT GROUP_CONCAT(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id  where randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id and randevu_hizmetler.yardimci_personel is not null),"Belirtilmemiş")),"</td></tr>

                    <tr><td>Cihaz</td><td>:</td><td>",COALESCE(cihazlar.cihaz_adi,""),"</td></tr>



                    <tr><td>Oda</td><td>:</td><td>",COALESCE(odalar.oda_adi,""),"</td></tr>

                     <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                        <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                       <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                        <tr><td>Geldi mi?</td><td>:</td><td>",CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" WHEN randevular.randevuya_geldi=0 THEN "Gelmedi" ELSE "Belirtilmemiş" END,"</td></tr>

                       <tr><td>Fiyat (₺)</td><td>:</td><td>",COALESCE(randevu_hizmetler.fiyat,""),"</td></tr>

                      <tr><td>Müşteri Notu</td><td>:</td><td>",COALESCE(randevular.notlar,""),"</td></tr>

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(randevular.personel_notu,""),"</td></tr>

                    </table>")) end as description'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null THEN TRIM(CONCAT("<a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"btn btn-primary btn-block btn-lg\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>")) ELSE TRIM(CONCAT("<a data-toggle=\"modal\" data-target=\"#randevu-duzenle-modal\" name=\"randevu_duzenle\" href=\"#\" class=\"btn btn-primary\" data-value=\"",randevular.id,"\"> Düzenle</a>")) END as duzenle_buton'),

                    DB::raw('CASE 

                                WHEN randevular.on_gorusme_id is not null 

                                THEN TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-success btn-block btn-lg\" href=\"#\" name=\"", (CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN "satis_yapildi" ELSE "urun_satis_yapildi" END)  ,"\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-danger btn-block btn-lg\" href=\"#\" name=\"satis_yapilmadi\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-times\"></i> Satış Yapılmadı</a></div></div>"))

                                WHEN randevular.durum=0 THEN 

                                TRIM(CONCAT("<div class=\"row\"><div class=\"col-6 col-xs-6 col-sm-6\"><button data-value=\"",randevular.id,"\" class=\"btn btn-success btn-block btn-lg randevuonayla\" data-value=\"",randevular.id,"\"> Onayla</a></div><div class=\"col-6 col-xs-6 col-sm-6\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))



                                ELSE 

                                  TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"tahsil_et\" href=\"#\" class=\"btn btn-primary btn-block btn-lg\" data-value=\"",randevular.id,"\"> Tahsilat</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))      

                                END as eventbuttons'),



                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'), DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" WHEN randevular.randevuya_geldi=0 then "#ff0000" WHEN randevular.randevuya_geldi=1 THEN "#008000" ELSE renk_duzenleri.renk END as color'),'hizmetler.hizmet_kategori_id as resourceId')->where('randevular.salon_id',self::mevcutsube($request))->where('randevular.durum','<',2)->where('randevular.tarih','>=',$tarih1)->where('randevular.tarih','<=',$tarih2)->groupBy('hizmetgrubu')->get();

            $resources = DB::table('salon_sunulan_hizmetler')->

            join('hizmet_kategorisi','salon_sunulan_hizmetler.hizmet_kategori_id','=','hizmet_kategorisi.id')->

            join('salon_hizmet_kategori_renkleri','salon_hizmet_kategori_renkleri.hizmet_kategori_id','=','hizmet_kategorisi.id')->

            join('renk_duzenleri','salon_hizmet_kategori_renkleri.renk_id','=','renk_duzenleri.id')->

            select(['salon_sunulan_hizmetler.hizmet_kategori_id as id','hizmet_kategorisi.hizmet_kategorisi_adi as title','renk_duzenleri.renk as bgcolor'])->where('salon_sunulan_hizmetler.aktif',true)->where('salon_sunulan_hizmetler.salon_id',self::mevcutsube($request))->groupBy('salon_sunulan_hizmetler.hizmet_kategori_id')->get();

        }

        if($takvim_turu == 2 && DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() == 0){

            $randevu_hizmetler = DB::table('randevu_hizmetler')->

            join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->



            join('users','randevular.user_id','=','users.id')->

            join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->

            join('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')->

            join('salon_cihaz_renkleri','salon_cihaz_renkleri.cihaz_id','=','cihazlar.id')->

            join('renk_duzenleri','salon_cihaz_renkleri.renk_id','=','renk_duzenleri.id')

             

            ->leftjoin('isletmeyetkilileri','randevular.olusturan_personel_id','=','isletmeyetkilileri.id')

             

            ->leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')

           

             

            ->leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')

            

            ->leftjoin('on_gorusmeler','randevular.on_gorusme_id','on_gorusmeler.id')                                   

            ->leftjoin('paketler','on_gorusmeler.paket_id','paketler.id')

            ->leftjoin('urunler','on_gorusmeler.urun_id','urunler.id')

            ->select(

                'randevular.id as randevu_id',

                'randevular.user_id as userid',

                'randevu_hizmetler.id as id',

                  DB::raw('CONCAT("#ffffff") as borderColor'),

                  DB::raw('CONCAT(randevu_hizmetler.randevu_id,randevu_hizmetler.hizmet_id,DATE_FORMAT(randevu_hizmetler.saat, "%H:%i"),DATE_FORMAT(randevu_hizmetler.saat_bitis, "%H:%i")) as hizmetgrubu'),

                DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE 

                CONCAT(

                    users.name, 

                    CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " (PAKET) " ELSE "" END, 

                    CASE WHEN randevular.on_gorusme_id > 0 then " (ÖN GÖRÜŞME) " else "" end,

                    CASE WHEN randevular.on_gorusme_id is not null THEN 

                    CONCAT("\nÖn Görüşme Nedeni:",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,"")) 

                    ELSE CONCAT(   

                        "\n",hizmetler.hizmet_adi

                    )END

                ) END as title'),



                DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE CONCAT(users.name, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " Paket Randevusu Detayları " ELSE "" END , CASE WHEN randevular.on_gorusme_id > 0 then " Ön Görüşme Randevusu Detayları" else "" end, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) = 0 AND randevular.on_gorusme_id is null THEN " Randevu Detayları" ELSE "" END ) END as modal_title'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null  



                    then 



                    TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>

                    <tr><td>Ön Görüşme Nedeni</td><td>:</td><td>",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,""),"</td></tr>

                    <tr><td>Görüşmeyi Yapan</td><td>:</td><td>",COALESCE(salon_personelleri.personel_adi,""),"</td></tr>

                    

                    <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                    <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                    <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                    <tr><td>Durum</td><td>:</td><td>",CASE WHEN on_gorusmeler.durum=1 THEN "Satış Yapıldı" WHEN on_gorusmeler.durum is null THEN "Beklemede" ELSE "Satış Yapılmadı" END,"</td></tr>

                       

                       

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(on_gorusmeler.aciklama,""),"</td></tr>

                    </table>"))  

                    

                   



                    ELSE 



                     TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>



                    <tr><td>Hizmet</td><td>:</td><td>",COALESCE(hizmetler.hizmet_adi,""),"</td></tr>

                    <tr><td>Personel</td><td>:</td><td>",(COALESCE((SELECT GROUP_CONCAT(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id  where randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id and randevu_hizmetler.yardimci_personel is null),"Belirtilmemiş")),"</td></tr>

                        <tr><td>Yardımcı Personel(-ler)</td><td>:</td><td>", (COALESCE((SELECT GROUP_CONCAT(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id  where randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id and randevu_hizmetler.yardimci_personel is not null),"Belirtilmemiş")),"</td></tr>

                    <tr><td>Cihaz</td><td>:</td><td>",COALESCE(cihazlar.cihaz_adi,""),"</td></tr>



                    <tr><td>Oda</td><td>:</td><td>",COALESCE(odalar.oda_adi,""),"</td></tr>

                     <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                        <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                       <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                        <tr><td>Geldi mi?</td><td>:</td><td>",CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" WHEN randevular.randevuya_geldi=0 THEN "Gelmedi" ELSE "Belirtilmemiş" END,"</td></tr>

                       <tr><td>Fiyat (₺)</td><td>:</td><td>",COALESCE(randevu_hizmetler.fiyat,""),"</td></tr>

                      <tr><td>Müşteri Notu</td><td>:</td><td>",COALESCE(randevular.notlar,""),"</td></tr>

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(randevular.personel_notu,""),"</td></tr>

                    </table>")) end as description'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null THEN TRIM(CONCAT("<a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"btn btn-primary btn-block btn-lg\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>")) ELSE TRIM(CONCAT("<a data-toggle=\"modal\" data-target=\"#randevu-duzenle-modal\" name=\"randevu_duzenle\" href=\"#\" class=\"btn btn-primary\" data-value=\"",randevular.id,"\"> Düzenle</a>")) END as duzenle_buton'),

                    DB::raw('CASE 

                                WHEN randevular.on_gorusme_id is not null 

                                THEN TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-success btn-block btn-lg\" href=\"#\" name=\"", (CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN "satis_yapildi" ELSE "urun_satis_yapildi" END)  ,"\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-danger btn-block btn-lg\" href=\"#\" name=\"satis_yapilmadi\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-times\"></i> Satış Yapılmadı</a></div></div>"))

                                WHEN randevular.durum=0 THEN 

                                TRIM(CONCAT("<div class=\"row\"><div class=\"col-6 col-xs-6 col-sm-6\"><button data-value=\"",randevular.id,"\" class=\"btn btn-success btn-block btn-lg randevuonayla\" data-value=\"",randevular.id,"\"> Onayla</a></div><div class=\"col-6 col-xs-6 col-sm-6\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))



                                ELSE 

                                  TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"tahsil_et\" href=\"#\" class=\"btn btn-primary btn-block btn-lg\" data-value=\"",randevular.id,"\"> Tahsilat</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))      

                                END as eventbuttons'),



                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),

                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),

                DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" WHEN randevular.randevuya_geldi=0 then "#ff0000" WHEN randevular.randevuya_geldi=1 THEN "#008000" ELSE renk_duzenleri.renk END as color'),

                'cihazlar.id as resourceId'

            )->where('randevular.salon_id',self::mevcutsube($request))->where('randevular.durum','<',2)->where('randevular.tarih','>=',$tarih1)->where('randevular.tarih','<=',$tarih2)->groupBy('hizmetgrubu')->get();

            $resources = DB::table('cihazlar')->

            join('salon_cihaz_renkleri','salon_cihaz_renkleri.cihaz_id','=','cihazlar.id')->

            join('renk_duzenleri','salon_cihaz_renkleri.renk_id','=','renk_duzenleri.id')->

            select(['cihazlar.id as id','cihazlar.cihaz_adi as title','renk_duzenleri.renk as bgcolor'])->where('cihazlar.salon_id',self::mevcutsube($request))->where('cihazlar.aktifmi',true)->get();

        }

        if($takvim_turu == 3 && DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() == 0){

            $randevu_hizmetler = DB::table('randevu_hizmetler')->

            join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->

             

            join('users','randevular.user_id','=','users.id')->

            join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->

            join('odalar','randevu_hizmetler.oda_id','=','odalar.id')->



            join('salon_oda_renkleri','salon_oda_renkleri.oda_id','=','odalar.id')->

            join('renk_duzenleri','salon_oda_renkleri.renk_id','=','renk_duzenleri.id')

            ->leftjoin('isletmeyetkilileri','randevular.olusturan_personel_id','=','isletmeyetkilileri.id')

           

            ->leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')

             

            ->leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')

           

            ->leftjoin('on_gorusmeler','randevular.on_gorusme_id','on_gorusmeler.id')                                   

            ->leftjoin('paketler','on_gorusmeler.paket_id','paketler.id')

            ->leftjoin('urunler','on_gorusmeler.urun_id','urunler.id')

            ->select(

                'randevular.id as randevu_id',

                'randevular.user_id as userid',

                'randevu_hizmetler.id as id',

                 DB::raw('CONCAT(randevu_hizmetler.randevu_id,randevu_hizmetler.hizmet_id,DATE_FORMAT(randevu_hizmetler.saat, "%H:%i"),DATE_FORMAT(randevu_hizmetler.saat_bitis, "%H:%i")) as hizmetgrubu'),

                DB::raw('CONCAT("#ffffff") as borderColor'),

             DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE CONCAT(users.name, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " Paket Randevusu Detayları " ELSE "" END , CASE WHEN randevular.on_gorusme_id > 0 then " Ön Görüşme Randevusu Detayları" else "" end, CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) = 0 AND randevular.on_gorusme_id is null THEN " Randevu Detayları" ELSE "" END ) END as modal_title'),

                  DB::raw('CASE when randevular.user_id = 2012 THEN "Kapalı Saat" ELSE 

                CONCAT(

                    users.name, 

                    CASE WHEN (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paket_seanslar.randevu_id = randevular.id) > 0 THEN " (PAKET) " ELSE "" END, 

                    CASE WHEN randevular.on_gorusme_id > 0 then " (ÖN GÖRÜŞME) " else "" end,

                    CASE WHEN randevular.on_gorusme_id is not null THEN 

                    CONCAT("\nÖn Görüşme Nedeni:",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,"")) 

                   ELSE CONCAT(   

                        "\n",hizmetler.hizmet_adi

                    )END

                ) END as title'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null  



                    then 



                    TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>

                    <tr><td>Ön Görüşme Nedeni</td><td>:</td><td>",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,""),"</td></tr>

                    <tr><td>Görüşmeyi Yapan</td><td>:</td><td>",COALESCE(salon_personelleri.personel_adi,""),"</td></tr>

                    

                    <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                    <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                    <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                    <tr><td>Durum</td><td>:</td><td>",CASE WHEN on_gorusmeler.durum=1 THEN "Satış Yapıldı" WHEN on_gorusmeler.durum is null THEN "Beklemede" ELSE "Satış Yapılmadı" END,"</td></tr>

                       

                       

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(on_gorusmeler.aciklama,""),"</td></tr>

                    </table>"))  

                    

                   



                    ELSE 



                     TRIM(CONCAT("<table style=\"width:100%;margin:0 0 10px 0\"><tr><td>Telefon</td><td>:</td><td>",COALESCE(users.cep_telefon,""),"</td></tr>



                    <tr><td>Hizmet</td><td>:</td><td>",COALESCE(hizmetler.hizmet_adi,""),"</td></tr>

                    <tr><td>Personel</td><td>:</td><td>",(COALESCE((SELECT GROUP_CONCAT(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id  where randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id and randevu_hizmetler.yardimci_personel is null),"Belirtilmemiş")),"</td></tr>

                    <tr><td>Yardımcı Personel(-ler)</td><td>:</td><td>", (COALESCE((SELECT GROUP_CONCAT(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id  where randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id and randevu_hizmetler.yardimci_personel is not null),"Belirtilmemiş")),"</td></tr>

                     

                    <tr><td>Cihaz</td><td>:</td><td>",COALESCE(cihazlar.cihaz_adi,""),"</td></tr>



                    <tr><td>Oda</td><td>:</td><td>",COALESCE(odalar.oda_adi,""),"</td></tr>

                     <tr><td>Zaman</td><td>:</td><td>",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"</td></tr>

                        <tr><td>Süre(dk)</td><td>:</td><td>",COALESCE(randevu_hizmetler.sure_dk,""),"</td></tr>

                       <tr><td>Oluşturan</td><td>:</td><td>",COALESCE(isletmeyetkilileri.name,""),"</td></tr>

                        <tr><td>Geldi mi?</td><td>:</td><td>",CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" WHEN randevular.randevuya_geldi=0 THEN "Gelmedi" ELSE "Belirtilmemiş" END,"</td></tr>

                       <tr><td>Fiyat (₺)</td><td>:</td><td>",COALESCE(randevu_hizmetler.fiyat,""),"</td></tr>

                      <tr><td>Müşteri Notu</td><td>:</td><td>",COALESCE(randevular.notlar,""),"</td></tr>

                      <tr><td>Personel Notu</td><td>:</td><td>",COALESCE(randevular.personel_notu,""),"</td></tr>

                    </table>")) end as description'),



                    DB::raw('CASE WHEN randevular.on_gorusme_id is not null THEN TRIM(CONCAT("<a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"btn btn-primary btn-block btn-lg\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>")) ELSE TRIM(CONCAT("<a data-toggle=\"modal\" data-target=\"#randevu-duzenle-modal\" name=\"randevu_duzenle\" href=\"#\" class=\"btn btn-primary\" data-value=\"",randevular.id,"\"> Düzenle</a>")) END as duzenle_buton'),

                    DB::raw('CASE 

                                WHEN randevular.on_gorusme_id is not null 

                                THEN TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-success btn-block btn-lg\" href=\"#\" name=\"", (CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN "satis_yapildi" ELSE "urun_satis_yapildi" END)  ,"\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a class=\"btn btn-danger btn-block btn-lg\" href=\"#\" name=\"satis_yapilmadi\" data-value=\"",randevular.on_gorusme_id,"\"><i class=\"fa fa-times\"></i> Satış Yapılmadı</a></div></div>"))

                                WHEN randevular.durum=0 THEN 

                                TRIM(CONCAT("<div class=\"row\"><div class=\"col-6 col-xs-6 col-sm-6\"><button data-value=\"",randevular.id,"\" class=\"btn btn-success btn-block btn-lg randevuonayla\" data-value=\"",randevular.id,"\"> Onayla</a></div><div class=\"col-6 col-xs-6 col-sm-6\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))



                                ELSE 

                                  TRIM(CONCAT("<div class=\"row\">

                                

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"gelmedi_isaretle\" href=\"#\" class=\"btn btn-danger btn-block btn-lg\" data-value=\"",randevular.id,"\"> Gelmedi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"geldi_isaretle\" href=\"#\" class=\"btn btn-success btn-block btn-lg\" data-value=\"",randevular.id,"\"> Geldi</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><a name=\"tahsil_et\" href=\"#\" class=\"btn btn-primary btn-block btn-lg\" data-value=\"",randevular.id,"\"> Tahsilat</a></div>

                                <div class=\"col-3 col-xs-3 col-sm-3\"><button class=\"btn btn-danger btn-block btn-lg randevuiptalet\" data-value=\"",randevular.id,"\"> İptal Et</button></div></div>"))      

                                END as eventbuttons'),



                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),

                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),

               DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" WHEN randevular.randevuya_geldi=0 then "#ff0000" WHEN randevular.randevuya_geldi=1 THEN "#008000" ELSE renk_duzenleri.renk END as color'),

                'odalar.id as resourceId'

            )->where('randevular.salon_id',self::mevcutsube($request))->where('randevular.durum','<',2)->where('randevular.tarih','>=',$tarih1)->where('randevular.tarih','<=',$tarih2)->groupBy('hizmetgrubu')->where(function($q){ if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) $q->where('randevu_hizmetler.personel_id',Auth::guard('isletmeyonetim')->user()->personel_id); })->get();

            $resources = DB::table('odalar')->

            join('salon_oda_renkleri','salon_oda_renkleri.oda_id','=','odalar.id')->

            join('renk_duzenleri','salon_oda_renkleri.renk_id','=','renk_duzenleri.id')->

            select(['odalar.id as id','odalar.oda_adi as title','renk_duzenleri.renk as bgcolor'])->where('odalar.salon_id',self::mevcutsube($request))->where('odalar.aktifmi',true)->get();

        }

         

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $calismasaatleri = array();

        foreach($isletme->calisma_saatleri as $calisma_saati)

        {

            if($calisma_saati->calisiyor)

                array_push($calismasaatleri, array('dow'=>[($calisma_saati->haftanin_gunu==7)? '0' : $calisma_saati->haftanin_gunu ],'start'=>date('H:i',strtotime($calisma_saati->baslangic_saati)),'end'=>date('H:i',strtotime($calisma_saati->bitis_saati))));

        }

         $takvim_baslangic_saat = '';

        $takvim_bitis_saat = '';

        $tarih = date('Y-m-d');

        if(isset($request->takvimtarih))

            $tarih = $request->takvimtarih;

        $baslangic_saatleri = array();

        $bitis_saatleri = array();

        $baslangic_saatleri_tarihli = array();

        $bitis_saatleri_tarihili = array();

        foreach($randevu_hizmetler as $randevu)

        {

            if(date('Y-m-d',strtotime($randevu->start)) == $tarih){

                array_push($baslangic_saatleri,date('H:i:s', strtotime($randevu->start))); 

                array_push($baslangic_saatleri_tarihli,$randevu->start); 

            }

            if(date('Y-m-d',strtotime($randevu->end)) == $tarih){

                array_push($bitis_saatleri,date('H:i:s',strtotime($randevu->end)));

                array_push($bitis_saatleri_tarihili,$randevu->end); 

            }

        }

        $day=0;

        if(date('D', strtotime($tarih))=='Mon') $day=1;

        else if(date('D', strtotime($tarih))=='Tue') $day=2;

        else if(date('D', strtotime($tarih))=='Wed') $day=3;

        else if(date('D', strtotime($tarih))=='Thu') $day=4;

        else if(date('D', strtotime($tarih))=='Fri') $day=5;

        else if(date('D', strtotime($tarih))=='Sat') $day=6;

        else if(date('D', strtotime($tarih))=='Sun') $day=7;

        $calismasaatibaslangic = SalonCalismaSaatleri::where('salon_id',$isletme->id)->where('haftanin_gunu',$day)->value('baslangic_saati');

        $calismasaatibitis = SalonCalismaSaatleri::where('salon_id',$isletme->id)->where('haftanin_gunu',$day)->value('bitis_saati');

        if($calismasaatibaslangic == '00:00:00')

            $calismasaatibaslangic = "06:00:00";

        if($calismasaatibitis == '00:00:00')

            $calismasaatibitis = "20:00:00";

        if( !empty($baslangic_saatleri) && strtotime(min($baslangic_saatleri)) < strtotime($calismasaatibaslangic))

            $takvim_baslangic_saat = min($baslangic_saatleri);

        else

            $takvim_baslangic_saat = $calismasaatibaslangic;

        if( !empty($bitis_saatleri) && strtotime(max($bitis_saatleri)) > strtotime($calismasaatibitis))

            $takvim_bitis_saat = max($bitis_saatleri);

        else

            $takvim_bitis_saat = $calismasaatibitis;

        /*$hizmetstr = json_encode($randevu_hizmetler);

        $hizmetstr = str_replace(['"[',']"'],["[","]"],$hizmetstr);

        

        //$hizmetstr = str_replace('\"' ,'"' ,$hizmetstr);*/

       return array(

                'randevu' => $randevu_hizmetler,

                'resource' => $resources,

                'baslangic' =>  $takvim_baslangic_saat,

                'bitis' =>  $takvim_bitis_saat,

                'calismasaatleri' => $calismasaatleri ,

                'baslangic_saatleri' => $baslangic_saatleri,

                'bitis_saatleri' => $takvim_bitis_saat

                



    

        );

       

        

    }

    public function kasadefteri(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $kasa = self::kasa_raporu_getir($request,'');

        return view('isletmeadmin.kasadefteri',['sayfa_baslik'=>'Kasa Raporu', 'title'=> 'Kasa Defteri | '.$isletme->salon_adi.' İşletme Yönetim Paneli',   'pageindex' => 103, 'bildirimler'=>self::bildirimgetir($request),'isletme'=>$isletme,'kasa'=>$kasa, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

        

    }

    public function kasa_raporu_filtre(Request $request)

    {

        return self::kasa_raporu_getir($request,'');

    }

    public function kasa_raporu_getir(Request $request,$returntext)

    {

        $tarih_baslangic = date('Y-m-01');

        $tarih_bitis = date('Y-m-d');

        $odeme_yontemi = '';

        if(isset($request->kasa_baslangic_tarihi))

        {

            if($request->kasa_baslangic_tarihi!='')

                $tarih_baslangic = $request->kasa_baslangic_tarihi;

        }

        if(isset($request->kasa_bitis_tarihi)){

            if($request->kasa_bitis_tarihi!='')

                $tarih_bitis = $request->kasa_bitis_tarihi;

        }

        if(isset($request->baslangic_bitis_tarihi))

        {

            if($request->baslangic_bitis_tarihi != 'ozel'){

                $tarih = explode(' / ',$request->baslangic_bitis_tarihi);

                $tarih_baslangic = $tarih[0];

                $tarih_bitis = $tarih[1];

            }

            

        }

        if(isset($request->odeme_yontemi))

            $odeme_yontemi = $request->odeme_yontemi;



        $tahsilatlar = Tahsilatlar::where('salon_id',self::mevcutsube($request))->where('odeme_tarihi','>=',$tarih_baslangic)->where('odeme_tarihi','<=',$tarih_bitis)->where(function($q) use($odeme_yontemi){if($odeme_yontemi != '') $q->where('odeme_yontemi_id',$odeme_yontemi);})->get();

        $tahsilat_liste = '';

        foreach($tahsilatlar as $tahsilat){

            $tahsilat_liste .= '<tr>

                            <td>'.date('d.m.Y',strtotime($tahsilat->odeme_tarihi)).'</td>

                            <td>';

            if($tahsilat->user_id !== null)

            {



                $tahsilat_liste .= $tahsilat->musteri->name;

            }

            $tahsilat_liste .= '</td>';

             



            $tahsilat_liste .= '<td>'.$tahsilat->olusturan->personel_adi.'</td>';

            

            $tahsilat_liste .='</td>

                            <td>'.$tahsilat->notlar.'</td>

                            <td>'.$tahsilat->odeme_yontemi->odeme_yontemi.'</td>

                            <td>'.number_format($tahsilat->tutar,2,',','.').'</td>

                          </tr>';

        }

            

        $masraflar = Masraflar::where('salon_id',self::mevcutsube($request))->where('tarih','>=',$tarih_baslangic)->where('tarih','<=',$tarih_bitis)->where(function($q) use($odeme_yontemi){if($odeme_yontemi != '') $q->where('odeme_yontemi_id',$odeme_yontemi);})->get();

        $masraf_liste = '';

        foreach($masraflar as $masraf)

            $masraf_liste .= '<tr>

                            <td>'.date('d.m.Y',strtotime($masraf->tarih)).'</td>

                            <td>'.$masraf->harcayan->personel_adi.'</td>

                            <td>'.$masraf->aciklama.'</td>

                            <td>'.$masraf->odeme_yontemi->odeme_yontemi.'</td>

                            <td>'.number_format($masraf->tutar,2,',','.').'</td>

                          </tr>';

        return array(

            'tahsilatlar'=>$tahsilat_liste,

            'masraflar'=>$masraf_liste,

            'gelir'=>number_format($tahsilatlar->sum('tutar'),2,',','.'),

            'gider'=>number_format($masraflar->sum('tutar'),2,',','.'),

            'toplam'=>number_format(($tahsilatlar->sum('tutar')-$masraflar->sum('tutar')),2,',','.'),

            'mesaj'=>$returntext

        );

    }





    public function isletme(){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        $id = Auth::guard('isletmeyonetim')->user()->salon_id;

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

        $paketler = self::paket_liste_getir('',true,$request);

        return view('isletmeadmin.isletme',['paketler'=>$paketler,'salongorselleri'=> $salongorselleri,'saloncalismasaatleri'=>$saloncalismasaatleri,'personeller' => $personeller, 'salonhizmetler' => $salonhizmetler,'isletme'=> $isletme,'sayfa_baslik' => $isletme->salon_adi.' | Detayları & Düzenle', 'pageindex' => 6,'etiketler' => $etiketler,'isletmeturulistesi' => $isletmeturu_html,'gorseller_html' => $gorseller_html,'hizmetlistesi'=>$hizmetlistesi_html,'salongorselkapak'=>$salongorselkapak,'subeler'=>$subeler, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function giderekle(Request $request){

         $giderkayit = new KasaDefteri();

         $giderkayit->gelir_gider = 0;

         $giderkayit->tarih = $request->gider_tarih;

         $giderkayit->aciklama = $request->gider_aciklama;

         $giderkayit->miktar = $request->gider_miktar;

         $giderkayit->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

         $giderkayit->save();

         $kasadefterigider = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',0)->where('tarih',$request->gider_tarih)->get();

                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',1)->where('tarih',$request->gider_tarih)->get(); 

            $toplamgelir = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih',$request->gider_tarih)->where('gelir_gider',1)->sum('miktar');

            $toplamgider =  KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih',$request->gider_tarih)->where('gelir_gider',0)->sum('miktar');

            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','<',$request->gider_tarih)->where('gelir_gider',1)->sum('miktar');

            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','<',$request->gider_tarih)->where('gelir_gider',0)->sum('miktar');

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

         $giderkayit->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

         $giderkayit->save();



          $kasadefterigider = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',0)->where('tarih',$request->gelir_tarih)->get();

                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',1)->where('tarih',$request->gelir_tarih)->get(); 

            $toplamgelir = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih',$request->gelir_tarih)->where('gelir_gider',1)->sum('miktar');

            $toplamgider =  KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih',$request->gelir_tarih)->where('gelir_gider',0)->sum('miktar');

            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','<',$request->gelir_tarih)->where('gelir_gider',1)->sum('miktar');

            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','<',$request->gelir_tarih)->where('gelir_gider',0)->sum('miktar');

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



          $kasadefterigider = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',0)->where('tarih',$girditarihi)->get();

                $kasadefterigelir = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',1)->where('tarih',$girditarihi)->get(); 

            $toplamgelir = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih',$girditarihi)->where('gelir_gider',1)->sum('miktar');

            $toplamgider =  KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih',$girditarihi)->where('gelir_gider',0)->sum('miktar');

            $toplamgelir_oncekigunler = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','<',$girditarihi)->where('gelir_gider',1)->sum('miktar');

            $toplamgider_oncekigunler = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tarih','<',$girditarihi)->where('gelir_gider',0)->sum('miktar');

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

         

         

        $kasadefteri = KasaDefteri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('gelir_gider',$request->gelir_gider)->where('tarih', $request->girdi_tarih)->get();

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



           

          

                $calismasaati = SalonCalismaSaatleri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

                 

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

    public function personeller(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $subeler  =Subeler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $personeltablohtml = "";

        /*foreach ($personeller as $key => $value) {

          if(Auth::guard('isletmeyonetim')->user()->personel_id != $value->id){

                 $personeltablohtml .= '<tr>';

            if($value->profil_resmi == null || $value->profil_resmi =='')

                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';

            else

                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$value->profil_resmi.'"></td>';

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

        $paketler = self::paket_liste_getir('',true,$request);

        return view ('isletmeadmin.personeller',['paketler'=>$paketler,'pageindex' =>5 , 'sayfa_baslik' => 'Personeller','isletme'=>$isletme,'personeller'=>$personeller,'subeler'=>$subeler,'yetkiliolunanisletmeler'=>$isletmeler]);

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

              $yetkili->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

              $yetkili->save();

              $sonuc = $yetkilendirilecekpersonel->personel_adi. ' adlı personel için yetkilendirme başarı ile oluşturuldu';



         }

         $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

      

        $personeltablohtml = "";

        foreach ($personeller as $key => $value) {

          if(Auth::guard('isletmeyonetim')->user()->personel_id != $value->id){

                 $personeltablohtml .= '<tr>';

            if($value->profil_resmi == null || $value->profil_resmi =='')

                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';

            else

                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$value->profil_resmi.'"></td>';

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

          $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

      

        $personeltablohtml = "";

        foreach ($personeller as $key => $value) {

          if(Auth::guard('isletmeyonetim')->user()->personel_id != $value->id){

                 $personeltablohtml .= '<tr>';

            if($value->profil_resmi == null || $value->profil_resmi =='')

                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';

            else

                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$value->profil_resmi.'"></td>';

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

    public function personelekleduzenle(Request $request){

        $result = '';

        $swaltitle = '';

        $swalstat  ='';

        $yenihesapacma = false;

        $yeniekleme = false;

        $olusturulansifre = '';

        if($request->personel_id == '' && Personeller::where('cep_telefon',$request->cep_telefon)->where('salon_id',$request->sube)->count() == 1 && IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->count()==1){



            $personel = Personeller::where('id',$request->personel_id)->where('salon_id',$request->sube)->first();

            $result = 'Girmiş olduğunuz cep telefonu ile sistemde '.IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->value('name').' isimli kayıt zaten mevcut. Lütfen başka bir kayıt giriniz';

            $swaltitle = 'Uyarı';

            $swalstat  = 'warning';

        }

        else{

            

            $sistemyetkisi = '';

            if(isset($request->sistem_yetki))

                $sistemyetkisi = $request->sistem_yetki;

            else

                $sistemyetkisi = 'Hesap Sahibi';

            $rol_id = DB::table('roles')->where('name',$sistemyetkisi)->value('id');

            $personel = '';

            $yetkili = '';

            if(IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->count()==0){

                $yetkili = new IsletmeYetkilileri();

                $yenihesapacma = true;



            }

            else

                $yetkili = IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->first();

            if(Personeller::where('cep_telefon',$request->cep_telefon)->where('salon_id',$request->sube)->count()==0){

                $personel = new Personeller();

                $yeniekleme = true;

                $personel->aktif=true;

                $personel->takvimde_gorunsun = true;

                $son_eklenen_personel = Personeller::where('salon_id',$request->sube)->orderBy('id','desc')->first();

                if($son_eklenen_personel->renk == 10)

                        $personel->renk = 1;

                else

                        $personel->renk = $son_eklenen_personel->renk + 1;

            }

            else{ 

                $personel = Personeller::where('cep_telefon',$request->cep_telefon)->where('salon_id',$request->sube)->first();

                 

            } 

            $personel->personel_adi = $request->personel_adi;

            $personel->unvan = $request->unvan;

            $yetkili->unvan = $request->unvan;

            $yetkili->cinsiyet = $request->cinsiyet;

            $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';

            $yetkili->name = $request->personel_adi;

            $yetkili->gsm1 = $request->cep_telefon;

            if($yenihesapacma)

            { 

                $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');

                $olusturulansifre = substr($random, 0, 6);

                $yetkili->password = Hash::make($olusturulansifre);  

                $yetkili->aktif=true; 

            }

            $yetkili->save();

            $personel->cep_telefon = $request->cep_telefon;

            $personel->salon_id = $request->sube;

            $personel->cinsiyet = $request->cinsiyet;

            $personel->maas = $request->personel_maas;

            $personel->unvan = $request->unvan;

            $personel->hizmet_prim_yuzde = $request->hizmet_prim_yuzde;

            $personel->urun_prim_yuzde = $request->urun_prim_yuzde;

            $personel->paket_prim_yuzde = $request->paket_prim_yuzde; 

            $personel->yetkili_id = $yetkili->id;

            $personel->role_id = $rol_id;

            $personel->save();

            

            PersonelCalismaSaatleri::where('personel_id',$personel->id)->delete();  

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

            PersonelMolaSaatleri::where('personel_id',$personel->id)->delete();

                

            for($i=1;$i<=7;$i++){

                    

                    $personelmolasaatleri = new PersonelMolaSaatleri();

                    $personelmolasaatleri->haftanin_gunu = $i;

                    $personelmolasaatleri->personel_id = $personel->id;

                    if(isset($_POST['molavar'.$i])){



                        $personelmolasaatleri->mola_var = 1;

                       

                       

                    }

                    else{

                        $personelmolasaatleri->mola_var = 0;

                    }

                        

                     if($i==1){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati1;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati1;

                        }

                         if($i==2){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati2;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati2;

                        }

                         if($i==3){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati3;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati3;

                        }

                         if($i==4){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati4;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati4;

                        }

                         if($i==5){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati5;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati5;

                        }

                         if($i==6){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati6;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati6;

                        }

                         if($i==7){

                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati7;

                             $personelmolasaatleri->bitis_saati = $request->molabitissaati7;

                        }

                        $personelmolasaatleri->save();

            } 

            if($yeniekleme){

                    $mesajlar = array();

                    array_push($mesajlar, array("to"=>$yetkili->gsm1,"message"=> "Sayın ".$yetkili->name.". Randevu sistemi şifreniz : ".$olusturulansifre));

                    self::sms_gonder($request,$mesajlar,false,1,false);

            } 

            $yetkili->roles()->detach();   
            DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values ('.$rol_id.', "App\\\IsletmeYetkilileri",'.$yetkili->id.','.$request->sube.')');   
            $result = 'Personel başarıyla kaydedildi'; 
            $swaltitle = 'Başarılı'; 
            $swalstat = 'success';

        }

        return array(

            'swaltitle' => $swaltitle,

            'swalstat'=>$swalstat,

            'sonuc'=>$result,

            'personeller'=>self::personel_liste_getir($request)

        );

          



    }

    public function personel_liste_getir(Request $request)

    {



        $personeller =  DB::table('salon_personelleri')

        ->join('isletmeyetkilileri','salon_personelleri.yetkili_id','=','isletmeyetkilileri.id')

         

        ->join('model_has_roles','isletmeyetkilileri.id','=','model_has_roles.model_id')

        ->join('roles','model_has_roles.role_id','=','roles.id')

        ->select(

            'isletmeyetkilileri.name as ad_soyad',

            'isletmeyetkilileri.gsm1 as telefon',

            'roles.name as hesap_turu', 

            DB::raw('CASE WHEN salon_personelleri.aktif=0 THEN "<button class=\"btn btn-danger\">Pasif</button>" WHEN salon_personelleri.aktif=1 THEN "<button class=\"btn btn-success\">Aktif</button>" END as durum'),

            DB::raw('CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                  <a class=\"dropdown-item\" href=\"/isletmeyonetim/personeldetay/",salon_personelleri.id,"?sube='.self::mevcutsube($request).'\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>

                <a class=\"dropdown-item\" href=\"#\" onclick=\"modalbaslikata(\'Personel Bilgileri\',\'\' )\" name=\"personel_detayi\" data-toggle=\"modal\" data-target=\"#personel-modal\" data-value=\"",salon_personelleri.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>    

                  

                  

                    <a class=\"dropdown-item\" href=\"#\" name=\"personel_sifre_degistir_gonder\" data-value=\"",salon_personelleri.id,"\"><i class=\"icon-copy dw dw-password\"></i> Şifre Değiştir & Gönder</a>

                    <a class=\"dropdown-item\" href=\"#\" name=\"personel_pasif_aktif_yap\" data-index-number=\"",CASE WHEN salon_personelleri.aktif = 0 THEN "1" ELSE  "0" END,"\" data-value=\"",salon_personelleri.id,"\">",CASE WHEN salon_personelleri.aktif = 0 THEN "<i class=\"fa fa-plus\"></i> Aktif Yap" ELSE  "Pasif Yap" END,"</a></div></div>") AS islemler')

        )->where('salon_personelleri.salon_id',self::mevcutsube($request))->where('model_has_roles.salon_id',self::mevcutsube($request))->orderBy('isletmeyetkilileri.id','asc')->get();

        return $personeller;

    }

    public function personelsistemyetkikaldir(Request $request){

         $yetkili = IsletmeYetkilileri::where('personel_id',$request->personelid)->first();

         $yetkili->delete();

         $personel = Personeller::where('id',$request->personelid)->value('personel_adi');

         $sonuc = $personel. ' isimli personelin sistem yetkileri başarı ile kaldırıldı!';

         $result['sonuc'] = array();

         $result['liste'] = array();

         $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

      

        $personeltablohtml = "";

        foreach ($personeller as $key => $value) {

          if(Auth::guard('isletmeyonetim')->user()->personel_id != $value->id){

                 $personeltablohtml .= '<tr>';

            if($value->profil_resmi == null || $value->profil_resmi =='')

                $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/public/isletmeyonetim_assets/img/avatar.png"></td>';

            else

                  $personeltablohtml .= '<td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$value->profil_resmi.'"></td>';

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

      public function personeldetay(Request $request,$id){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

       if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $salonpersonel = Personeller::where('id',$id)->first();

        $personel = IsletmeYetkilileri::where('id',$salonpersonel->yetkili_id)->first();



         $personelhizmetler = PersonelHizmetler::where('personel_id',$id)->orderBy('id','desc')->get();

         $personelcalismasaatleri = PersonelCalismaSaatleri::where('personel_id',$id)->get();

         //$subeler = Subeler::where('salon_id',$personel->salon_id)->get();

         $personelhizmetleri_html = "";

          /*foreach($personelhizmetler as $personelhizmet){

                $personelhizmetleri_html .="<tr><td>".$personelhizmet->hizmetler->hizmet_adi."</td><td class='actions'><a name='hizmetsil_personel' title='Çıkar' data-value='".$personelhizmet->id."' class='icon'><i class='mdi mdi-delete'></i></a></td></tr>";

          }*/

          $adisyonlar = self::adisyon_yukle($request,'','',date('Y-m-01 00:00:00'),date('Y-m-d H:i:s'),'',$id);

                   $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        return view('isletmeadmin.personeldetay',['sayfa_baslik' => $personel->name.' Bilgileri','personelsunulanhizmetler'=>$personelhizmetleri_html,'personelcalismasaatleri'=>$personelcalismasaatleri,'personel' => $personel , 'title' => $personel->name.' | Detaylar & Düzenle', 'pageindex' => 105,'isletme' => $isletme,'bildirimler'=>self::bildirimgetir($request),'adisyonlar'=>$adisyonlar,'salonpersonel'=>$salonpersonel, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

    public function denemesql()

    {

        $veri = DB::table('salon_sunulan_hizmetler')->

        join('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')

        ->leftjoin('personel_sunulan_hizmetler','salon_sunulan_hizmetler.hizmet_id','=','personel_sunulan_hizmetler.hizmet_id')

        ->leftjoin('salon_personelleri','personel_sunulan_hizmetler.personel_id','=','salon_personelleri.id')

        ->select('hizmetler.hizmet_adi as hizmet_adi',DB::raw('GROUP_CONCAT(salon_personelleri.personel_adi) as personel'))->where('salon_sunulan_hizmetler.salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->groupBy('hizmetler.id')->get();



       





        return $veri;

    }

    public function profilresimyukle(Request $request){

        $personel = Auth::user(); 

       

        $folderPath = '/home/webfirma/randevumcepteweb3/public/profil_resimleri/';

        $image_parts = explode(";base64,", $_POST['profilresmi']);

        $image_type_aux = explode("image/", $image_parts[0]);

        $image_type = $image_type_aux[1];

        $image_base64 = base64_decode($image_parts[1]);

        $filename = uniqid() . '.jpg';

        $file = $folderPath . $filename;

        file_put_contents($file, $image_base64);

        $personel->profil_resim = "/public/profil_resimleri/".$filename;

        $personel->save(); 

        echo "Başarılı";   

         

   

    }

     public function isletmekapakresimyukle(Request $request){

        $salon = Salonlar::where('id',$request->sube)->first(); 

       

        $folderPath = '/home/webfirma/randevumcepteweb3/public/salon_gorselleri/';

        $image_parts = explode(";base64,", $request->kapakresmi);

        $image_type_aux = explode("image/", $image_parts[0]);

        $image_type = $image_type_aux[1];

        $image_base64 = base64_decode($image_parts[1]);

        $filename = uniqid() . '.jpg';

        $file = $folderPath . $filename;

        file_put_contents($file, $image_base64);

        

        $gorsel = '';

        if(SalonGorselleri::where('salon_id',$request->sube)->where('kapak_fotografi',1)->count()==1)

            $gorsel =SalonGorselleri::where('salon_id',$request->sube)->where('kapak_fotografi',1)->first();

        else

            $gorsel = new SalonGorselleri();

        $gorsel->salon_id = $request->sube;

        $gorsel->salon_gorseli = "/public/salon_gorselleri/".$filename;

        $gorsel->kapak_fotografi = 1;

        $gorsel->save(); 



        echo "Başarılı";   

         

   

    }

      public function isletmelogoyukle(Request $request){

        $salon = Salonlar::where('id',$request->sube)->first(); 

       

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

              $salon->logo = $hedef;

              $salon->save();

               

        }

        



        echo "Başarılı";   

         

   

    }

    public function personelekle(Request $request){

        $personel = new Personeller();

        $personel->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

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

        

        $randevu_tarihleri = array();

        array_push($randevu_tarihleri,$request->tarih);

        

        $cakisma_varmi = '';

        if(!isset($request->cakisanrandevuekle))

            $cakisma_varmi = self::cakisan_randevu_kontrol($request,$randevu_tarihleri);

        if($cakisma_varmi != '' && !isset($request->cakisanrandevuekle))

        {

            return array('cakismavar'=>$cakisma_varmi);

            exit();

        }

        else{

            if($cakisma_varmi == '' || $request->cakisanrandevuekle==1)

            {

                $randevu = Randevular::where('id',$request->randevu_id)->first();

                $eskitarihsaat = date('d.m.y H:i',strtotime($randevu->tarih.' '.$randevu->saat));

                $randevu->tarih = $request->tarih;

                

                $randevu->saat = $request->saat;

                if(isset($request->notlar))

                    $randevu->notlar =  $request->notlar;

                if(isset($request->personel_notu))

                    $randevu->personel_notu = $request->personel_notu;

                $randevu->sms_hatirlatma = $request->sms_hatirlatma;

                

                

                $hizmet_sureleri_okunan = array();

                $randevu->save();

                $totalsure = 0;

                $yenisaatbaslangic = $request->saat; 

                

                RandevuHizmetler::where('randevu_id',$randevu->id)->delete();

                foreach ($request->randevuhizmetleriyeni as $key => $value) {

                        array_push($hizmet_sureleri_okunan,$request->hizmet_suresi[$key]);

                       

                        $yenirandevuhizmetpersonel = new RandevuHizmetler();

                        $yenirandevuhizmetpersonel->randevu_id = $randevu->id;

                        $yenirandevuhizmetpersonel->hizmet_id = $value;

                        $yenirandevuhizmetpersonel->cihaz_id = $request->randevucihazlariyeni[$key];

                        $yenirandevuhizmetpersonel->personel_id = $request->randevupersonelleriyeni[$key];

                        $yenirandevuhizmetpersonel->oda_id = $request->randevuodalariyeni[$key];

                        $yenirandevuhizmetpersonel->sure_dk = $request->hizmet_suresi[$key];

                        $yenirandevuhizmetpersonel->fiyat = $request->hizmet_fiyat[$key];

                        $birsonraki = $key+1;

                        if($key == 0){

                             $yenirandevuhizmetpersonel->saat = $request->saat;

                             $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));



                             if(!isset($request->{"birlestir{$birsonraki}"}))

                                $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));

                        }

                        else{



                            $yenirandevuhizmetpersonel->saat = $yenisaatbaslangic;

                            $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));

                            if(!isset($request->{"birlestir{$birsonraki}"}))

                                $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));

                         

                                 

                        }



                        

                        $yenirandevuhizmetpersonel->save();

                       

                         

                        if(isset($request->{"randevuyardimcipersonelleriyeni_{$key}"})) {

                            foreach($request->{"randevuyardimcipersonelleriyeni_{$key}"} as $yardimci_personel_id)

                            {

                                if($yardimci_personel_id != '')   

                                {

                                $yardimci_personel = new RandevuHizmetler();

                                $yardimci_personel->randevu_id =  $randevu->id;

                                $yardimci_personel->hizmet_id = $value;

                                $yardimci_personel->cihaz_id = $request->randevucihazlariyeni[$key];                       

                                $yardimci_personel->personel_id = $yardimci_personel_id;

                                $yardimci_personel->oda_id = $request->randevuodalariyeni[$key];

                                $yardimci_personel->sure_dk = $request->hizmet_suresi[$key];

                                $yardimci_personel->fiyat = $request->hizmet_fiyat[$key];

                                $yardimci_personel->saat = $yenirandevuhizmetpersonel->saat;

                                $yardimci_personel->saat_bitis = $yenirandevuhizmetpersonel->saat_bitis;

                                $yardimci_personel->yardimci_personel = true;

                                $yardimci_personel->save();

                                }

                               

                            }

                        } 

                       

                         

                }

                $mesajlar = array();

                if(SalonSMSAyarlari::where('ayar_id',14)->where('salon_id',$randevu->salon_id)->value('musteri')==1)

                {

                    array_push($mesajlar,array("to"=>$randevu->users->cep_telefon,"message"=>$randevu->salonlar->salon_adi . " tarafından ".$eskitarihsaat." randevunuz ".date('d.m.Y',strtotime($randevu->tarih)) .'-'.$randevu->saat .' olarak güncellenmiştir. Detaylı bilgi için bize ulaşın. 0'.$randevu->salonlar->telefon_1));

                    

                    

                }

                foreach($randevu->hizmetler as $hizmet)

                {

                    $mesaj = "";

                    if(SalonSMSAyarlari::where('ayar_id',14)->where('salon_id',$randevu->salon_id)->value('personel')==1)

                    {

                    

                         $yetkiliid = Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');

                        $mesaj = $randevu->users->name." isimli müşterinin ".$hizmet->hizmetler->hizmet_adi." randevusu ". date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($hizmet->saat)) ." olarak ".Auth::guard('isletmeyonetim')->user()->name." tarafından güncellenmiştir.";

                        array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=> $mesaj)); 

                       

                    }

                    self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$randevu->id);

                        $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 

                    self::bildirimgonder($bildirimkimlikleri,"Randevu Güncelleme",$mesaj,$randevu->salon_id);

                }

                if(count($mesajlar)>0)

                    self::sms_gonder($request,$mesajlar,false,1,false); 

                

 



                $butonlar = '';

                $randevuguncellendi=array();

                if(!isset($request->takvim_sayfasi)){



                    $butonlar .= "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular?sube=".$randevu->salon_id."'>"

                                          ."Takvime Git</a>".

                                          "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular-liste?sube=".$randevu->salon_id."'>"

                                          ."Randevu Listesine Git</a>";

                    $randevuguncellendi['timer'] = 30000;

                }

                else

                    $randevuguncellendi['timer'] = 3000;

                $randevuguncellendi['success'] = '<p>Randevu bilgileri başarıyla güncellendi.</p>'.$butonlar;



                return $randevuguncellendi;

                exit();

                 

            }

        }

        

    }

    public function randevu_resize_drop(Request $request)

    {





        $randevu_eski = Randevular::where('id',$request->randevu_id)->first();







        $baslangic = explode('T',$request->start);

        $bitis = explode('T',$request->end);

        $hizmet = '';

        $randevu = '';

        

        $randevu = new Randevular();

        $randevu->user_id = $randevu_eski->user_id;

        $randevu->salon_id = $randevu_eski->salon_id;

        $randevu->tarih = $baslangic[0];

        $randevu->saat = $baslangic[1];

        $randevu->durum = $randevu_eski->durum;

        $randevu->salon = true;

        $randevu->sms_hatirlatma = $randevu_eski->sms_hatirlatma;

        $randevu->notlar = $randevu_eski->notlar;

        $randevu->personel_notu = $randevu_eski->personel_notu;

        $randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;

        $randevu->save();

        

        

        $hizmet = RandevuHizmetler::where('id',$request->id)->first();

        $eskitarih = $randevu_eski->tarih;

        $eskisaat = $hizmet->saat;

        $hizmet->randevu_id = $randevu->id;

        if($request->personel_id!='')

            $hizmet->personel_id = $request->personel_id;

        if($request->cihaz_id !='')

            $hizmet->cihaz_id = $request->cihaz_id;

        if($request->oda_id !='')

            $hizmet->oda_id = $request->oda_id;

        if($request->yeni_hizmet_id != '')

            $hizmet->hizmet_id = $request->yeni_hizmet_id;

        $hizmet->saat = $baslangic[1];

        $hizmet->saat_bitis = $bitis[1];

        $hizmet->save();

        

        if(SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',9)->value('musteri')==1){

             $mesajlar = array(

                array("to"=>$randevu->users->cep_telefon,"message"=>$randevu->salonlar->salon_adi." için oluşturulan ".$hizmet->personeller->personel_adi .' ile '.$hizmet->hizmetler->hizmet_adi.' '.date('d.m.Y H:i', strtotime($eskitarih.' '.$eskisaat))." tarihli randevunuz ".date('d.m.Y',strtotime($randevu->tarih)) ." - ". date('H:i',strtotime($hizmet->saat)) ." olarak güncellenmiştir. Detaylı bilgi için bize ulaşın. 0".$randevu->salonlar->telefon_1),



            );

            self::sms_gonder($request,$mesajlar,false,1,false);



        }

        if(RandevuHizmetler::where('randevu_id',$randevu_eski->id)->count()==0)

            $randevu_eski->delete();

         

        

        //self::hareket_ekle($request,'Düzenlendi');

        return 'Randevu başarıyla güncellendi'; 

    }

    public function randevuiptalet(Request $request){

        $randevu = Randevular::where('id',$request->randevuid)->first();

        $red = false;

        if($randevu->durum == 0)

            $red = true;

        $randevu->durum = 2;

        $randevu->save();

        $isletme = Salonlar::where('id',$randevu->salon_id)->first();

        $mesajlar = array();

        if($red)

        {

            

            if(SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',3)->value('musteri')==1){

                array_push($mesajlar, array("to"=>$randevu->users->cep_telefon,"message"=>$isletme->salon_adi." için oluşturduğunuz ".date('d.m.Y',strtotime($randevu->tarih)) ." ". date('H:i',strtotime($randevu->saat)) ." tarihli randevu talebiniz reddedilmiştir. Detaylı bilgi için bize ulaşın. 0".$isletme->telefon_1)); 

            }

            if(SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',3)->value('personel')==1)

            {



                foreach($randevu->hizmetler as $hizmet)

                {

                    $mesaj = $randevu->users->name .' isimli müşterinin yarın '.date('H:i',strtotime($hizmet->saat)).' saatli '.$hizmet->hizmetler->hizmet_adi.' randevusu '.Auth::guard('isletmeyonetim')->user()->name.' tarafından reddedilmiştir.';

                   $yetkiliid = Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');

                   array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj));

                  

                   self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$randevu->id);

                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 

                    self::bildirimgonder($bildirimkimlikleri,"Randevu Reddi",$mesaj,$randevu->salon_id);

                }

               

            }

           

        }

        else

        {

             

            if(SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',3)->value('musteri')==1){

                array_push($mesajlar,array("to"=>$randevu->users->cep_telefon,"message"=>$isletme->salon_adi." için oluşturulan ".date('d.m.Y',strtotime($randevu->tarih)) ." ". date('H:i',strtotime($randevu->saat)) ." tarihli randevunuz iptal edilmiştir. Detaylı bilgi için bize ulaşın. 0".$isletme->telefon_1));

                 

                

            }

            if(SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',3)->value('personel')==1)

            {



                foreach($randevu->hizmetler as $hizmet)

                {

                    $yetkiliid = Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');

                    $mesaj = $randevu->users->name .' isimli müşterinin yarın '.date('H:i',strtotime($hizmet->saat)).' saatli '.$hizmet->hizmetler->hizmet_adi.' randevusu '.Auth::guard('isletmeyonetim')->user()->name.' tarafından iptal edilmiştir.';

                    array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj));

                   

                    self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$randevu->id);

                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 

                    self::bildirimgonder($bildirimkimlikleri,"Randevu İptali",$mesaj,$randevu->salon_id);

                }

               

            }

        }

         if(count($mesajlar)>0)

            self::sms_gonder($request,$mesajlar,false,1,false);

       

       

        if(is_numeric($request->musteriid))

        {

            return self::randevu_liste_getir($request,'','','','','','',self::mevcutsube($request),$request->musteriid); 

            exit;

        }

        else{

            return self::randevu_liste_getir($request,'','','','','','',self::mevcutsube($request),''); 

            exit;

        }

        

            

    }

     public function randevuonayla(Request $request){

        $randevu = Randevular::where('id',$request->randevuid)->first();

        $randevu->durum = 1;

        $randevu->save();



        $isletme = Salonlar::where('id',$randevu->salon_id)->first();

        $mesajlar = array();

        if(SalonSMSAyarlari::where('ayar_id',2)->where('salon_id',$randevu->salon_id)->value('musteri') == 1 )

        {

            array_push($mesajlar,array("to"=>$randevu->users->cep_telefon,"message"=>$isletme->salon_adi." için oluşturduğunuz ".date('d.m.Y',strtotime($randevu->tarih)) ." ". date('H:i',strtotime($randevu->saat)) ." tarihli randevu talebiniz onaylanmıştır. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0".$randevu->salonlar->telefon_1));

           

        }

        if(SalonSMSAyarlari::where('ayar_id',2)->where('salon_id',$randevu->salon_id)->value('personel') == 1)

        {

                foreach($randevu->hizmetler as $hizmet)

                {

                    $yetkiliid = Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');

                    $mesaj = $randevu->users->name .' isimli müşterinin yarın '.date('H:i',strtotime($hizmet->saat)).' saatli '.$hizmet->hizmetler->hizmet_adi.' randevusu '.Auth::guard('isletmeyonetim')->user()->name.' tarafından onaylanmıştır.';

                    $randevutarihsaat = date('d.m.Y',strtotime($randevu->tarih)).' '.date('H:i:s',strtotime($hizmet->saat));  

                     array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj )); 

                    self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$randevu->id);

                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 

                    self::bildirimgonder($bildirimkimlikleri,"Randevu Onayı",$mesaj,$randevu->salon_id); 

                   

                }

        }

        if(count($mesajlar)>0)

            self::sms_gonder($request,$mesajlar,false,1,false);

       

        return self::randevu_liste_getir($request,'','','','','','',self::mevcutsube($request),'');

        

                   

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

        $user = Auth::guard('isletmeyonetim')->user()->first();

        $cevap = "";

       

            $user->password = Hash::make($request->yenisifre);

            $user->save();

            $cevap = "Şifreniz başarı ile değiştirildi";



       

         echo $cevap;

        

    }

    public function yetkilibilgiguncelle(Request $request){

        $user = IsletmeYetkilileri::where('id',Auth::guard('isletmeyonetim')->user()->id)->first();

        $user->name = $request->name;

        $user->email = $request->email;

        if($request->password != "")

            $user->password = Hash::make($request->password);

        $user->gsm1 = $request->gsm1;

        $user->unvan = $request->unvan;

        $user->sms_gonderimi = $request->sms_gonderimi;

        $user->cinsiyet = $request->cinsiyet;

        $user->save();

    }

   

    public function yenisubeekle(Request $request){

        $sube = new Subeler();

        $sube->sube = $request->subeadi;

        $sube->adres = $request->subeadres;

        $sube->sube_tel = $request->subetel;



        $sube->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

        $sube->aktif = false;

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

        $personel->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

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

      



     public function mevcutisletmeduzenleme(Request $request){

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

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



                $image = $request->isletmegorselleri[$i];

                $filename = strtotime(date('H:i:s')) . '-' .$_FILES["isletmegorselleri"]["name"][$i] . '.' . $image->getClientOriginalExtension();

                Image::make($image)->resize(null, 720, function ($constraint) {

                    $constraint->aspectRatio();

                })->save( public_path('/salon_gorselleri/' . $filename) );                 

                





                $salongorselleri = new SalonGorselleri();

                

                $salongorselleri->salon_gorseli = "/public/salon_gorselleri/".$filename;

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

        $gorselsayisi = SalonGorselleri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('kapak_fotografi','!=',1)->count();

        echo $gorselsayisi;

    }

     public function gorselsil(Request $request){

        $gorsel = SalonGorselleri::where('id',$request->gorselid)->first();

        $isletmeid = $gorsel->salon_id;

        unlink('/home/gvxrande/public_html/demolar_2'.$gorsel->salon_gorseli);

        $gorsel->delete();

        $gorseller_html = self::salon_gorselleri($request);

        echo $gorseller_html;







    }

    public function yenirandevu(){



        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $sunulanhizmetler = SalonHizmetler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $mevcutmusteriler = Randevular::join('users','randevular.user_id','=','users.id')->where('randevular.salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->groupBy('randevular.user_id')->get();

        return view('isletmeadmin.yenirandevu',[ 'title' =>  'Yeni Randevu Ekle | Avantajbu.com','pageindex' => 102,'isletme'=>$isletme,'personeller'=> $personeller, 'sunulanhizmetler'=> $sunulanhizmetler,'mevcutmusteriler'=>$mevcutmusteriler, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler] );

    }



    public function randevupersonelgetir(Request $request){

        $personelhizmetler = PersonelHizmetler::join('salon_personelleri','personel_sunulan_hizmetler.personel_id','=','salon_personelleri.id')->where('salon_personelleri.salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('personel_sunulan_hizmetler.hizmet_id',$request->hizmet_id)->get();

        $hizmet = Hizmetler::where('id',$request->hizmetid)->value('hizmet_adi');



        $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

                                    

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

        $musteriid = $request->adsoyad;

        $tarihler = ""; 

        $randevu_tarihleri = array();

        array_push($randevu_tarihleri,$request->tarih);

        $eklenecek_tarih = $request->tarih;

        if(isset($request->tekrarlayan)){

            for($t=1;$t<$request->tekrar_sayisi;$t++)

            {

                $eklenecek_tarih = date('Y-m-d', strtotime($request->tekrar_sikligi, strtotime($eklenecek_tarih)));

                array_push($randevu_tarihleri,$eklenecek_tarih);

            } 

        }

        $cakisma_varmi = '';

        if(!isset($request->cakisanrandevuekle))

            $cakisma_varmi = self::cakisan_randevu_kontrol($request,$randevu_tarihleri);

        if($cakisma_varmi != '' && !isset($request->cakisanrandevuekle))

        {

            return array('cakismavar'=>$cakisma_varmi);

            exit();

        }

        elseif(Salonlar::where('id',$request->sube)->value('demo_hesabi') == 1 && Randevular::where('salon_id',$request->sube)->count()>95)

        {

            return array('sube'=>Randevular::where('salon_id',$request->sube)->count(),'eklenemez'=>'<p>Deneme hesabında en fazla 5 randevu eklenebilir. Devam etmek için lütfen "Üyelik" bölümünden paket üyeliği başlatınız.</p><a href="/isletmeyonetim/uyelik?sube="'.$request->sube.' class="btn btn-primary">Paketleri İncele</a>');

        }

        else

        {

            if($cakisma_varmi == '' || $request->cakisanrandevuekle==1)

            {

                foreach($randevu_tarihleri as $tarihler){

                    $totalsure = 0;

                    foreach($request->hizmet_suresi as $key => $value)

                    {

                        $totalsure += $value;

                    }

                    $yenirandevu = [

                        

                            'user_id'=>$musteriid,

                            'salon_id'=>$request->sube,

                            'tarih'=>$tarihler,

                            'saat'=>$request->saat,

                            'salon'=>true,

                            'olusturan_personel_id'=>Auth::guard('isletmeyonetim')->user()->id,

                            'saat_bitis'=>date("H:i", strtotime('+'.$totalsure.' minutes', strtotime($request->saat))),

                            'durum'=>1,

                            'personel_notu'=>$request->personel_notu,

                        

                    ];

                    $yenirandevu = Randevular::create($yenirandevu);



                    /*$yenirandevu->user_id = $musteriid;

                    $yenirandevu->salon_id = $request->sube;

                   

                    $yenirandevu->tarih = $tarihler;

                    $yenirandevu->saat = $request->saat;

                    $yenirandevu->personel_notu = $request->personel_notu;

                    $yenirandevu->salon= true;

                    $yenirandevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;

                    

                   

                  

                    $yenirandevu->saat_bitis = date("H:i", strtotime('+'.$totalsure.' minutes', strtotime($request->saat)));

                    $yenirandevu->durum = 1;

                    $yenirandevu->save(); */

                    $hizmet_id = "";

                    $yenisaatbaslangic = $request->saat;

                    

                    $hizmet_sureleri_okunan = array();



                    foreach ($request->randevuhizmetleriyeni as $key2 => $value) {

                        array_push($hizmet_sureleri_okunan,$request->hizmet_suresi[$key2]);

                       

                        $yenirandevuhizmetpersonel = new RandevuHizmetler();

                        $yenirandevuhizmetpersonel->randevu_id = $yenirandevu->id;

                        $yenirandevuhizmetpersonel->hizmet_id = $value;

                        $yenirandevuhizmetpersonel->cihaz_id = $request->randevucihazlariyeni[$key2];

                       

                        $yenirandevuhizmetpersonel->personel_id = $request->randevupersonelleriyeni[$key2];

                        $yenirandevuhizmetpersonel->oda_id = $request->randevuodalariyeni[$key2];

                        $yenirandevuhizmetpersonel->sure_dk = $request->hizmet_suresi[$key2 ];

                        $yenirandevuhizmetpersonel->fiyat = $request->hizmet_fiyat[$key2];

                        $birsonraki = $key2+1;

                        if($key2 == 0){

                             $yenirandevuhizmetpersonel->saat = $request->saat;

                             $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key2].' minutes', strtotime($request->saat)));



                             if(!isset($request->{"birlestir{$birsonraki}"}))

                                $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key2].' minutes', strtotime($request->saat)));

 

                        } 

                        else{



                            $yenirandevuhizmetpersonel->saat = $yenisaatbaslangic;

                            $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key2].' minutes', strtotime($yenisaatbaslangic)));

                            if(!isset($request->{"birlestir{$birsonraki}"}))

                                $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key2].' minutes', strtotime($yenisaatbaslangic))); 

                                 

                        }

                        

                        $yenirandevuhizmetpersonel->save();

                         

                        foreach($request->{"randevuyardimcipersonelleriyeni_{$key2}"} as $yardimci_personel_id)

                        {

                            if($yardimci_personel_id != '')   

                            {

                                $yardimci_personel = new RandevuHizmetler();

                                $yardimci_personel->randevu_id =  $yenirandevu->id;

                                $yardimci_personel->hizmet_id = $value;

                                $yardimci_personel->cihaz_id = $request->randevucihazlariyeni[$key2];                       

                                $yardimci_personel->personel_id = $yardimci_personel_id;

                                $yardimci_personel->oda_id = $request->randevuodalariyeni[$key2];

                                $yardimci_personel->sure_dk = $request->hizmet_suresi[$key2];

                                $yardimci_personel->fiyat = $request->hizmet_fiyat[$key2];

                                $yardimci_personel->saat = $yenirandevuhizmetpersonel->saat;

                                $yardimci_personel->saat_bitis = $yenirandevuhizmetpersonel->saat_bitis;

                                $yardimci_personel->yardimci_personel = true;

                                $yardimci_personel->save();

                            }

                               

                        }

                        

                        

                       



                       

                    }



                }

                

                $isletme = Salonlar::where('id',$request->sube)->first();

                $musteribilgi = User::where('id',$musteriid)->first();



                $gsm = $musteribilgi->cep_telefon;

                $mesajlar = array();

                if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$yenirandevu->salon_id)->value('musteri')==1)

                {

                    array_push($mesajlar, array("to"=>$gsm,"message"=>$isletme->salon_adi . " tarafından ".date('d.m.Y',strtotime($request->tarih)) .'-'.$request->saat .' olarak randevunuz oluşturulmuştur. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0'.$isletme->telefon_1)); 

                    

                   

                }

                foreach($yenirandevu->hizmetler as $hizmet)

                {

                    $mesaj = "";

                    if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$yenirandevu->salon_id)->value('personel')==1)

                    {

                    

                        $mesaj = $yenirandevu->users->name." isimli müşterinin ". date('d.m.Y',strtotime($yenirandevu->tarih)) ." - ". date('H:i',strtotime($hizmet->saat)) ." ".$hizmet->hizmetler->hizmet_adi." randevusu ".Auth::guard('isletmeyonetim')->user()->name." tarafından oluşturulmuştur.";



                        $yetkiliid=Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');

                        array_push($mesajlar, array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj)); 



                       



                    }

                    self::bildirimekle($request,$yenirandevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$yenirandevu->id);

                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 



                    self::bildirimgonder($bildirimkimlikleri,"Yeni Randevu",$mesaj,$yenirandevu->salon_id);

                } 

                if(count($mesajlar)>0)

                    self::sms_gonder($request,$mesajlar,false,1,false);

                $butonlar = '';

                $randevu = array();

                if(!isset($request->takvim_sayfasi) ){

                    $butonlar .= "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular?sube=".$request->sube."'>"

                                          ."Takvime Git</a>".

                                          "<a class='btn btn-primary btn-lg btn-block' href='/isletmeyonetim/randevular-liste?sube=".$request->sube."'>"

                                          ."Randevu Listesine Git</a>";

                    $randevu['timer'] = 30000;

                }

                else

                     $randevu['timer'] = 3000;

                $randevu['success'] = '<p>Randevu başarı ile oluşturuldu.</p>'.$butonlar;

                



                return $randevu;

                exit();

            }

        }

        







    }

    

    public function personel_cihaz_oda($id,$salonid)

    {

        $durum = 0; 

        if(Personeller::where('id',$id)->where('salon_id',$salonid)->count()==1)

            $durum=1;

        if(Cihazlar::where('id',$id)->where('salon_id',$salonid)->count()==1)

            $durum=2;

        if(Odalar::where('id',$id)->where('salon_id',$salonid)->count()==1)

            $durum=3; 

        return $durum;

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

            $yenirandevu->user_id = 2012;

            $yenirandevu->salon_id = self::mevcutsube($request);

            $yenirandevu->durum = 1;

            $yenirandevu->tarih = $tarihler;

            $yenirandevuhizmetpersonel = new RandevuHizmetler();

            if(isset($request->saat)&& isset($request->saat_bitis)){

                $yenirandevu->saat = $request->saat;

                $yenirandevu->saat_bitis = $request->saat_bitis;

                $yenirandevuhizmetpersonel->saat = $request->saat;

                $yenirandevuhizmetpersonel->saat_bitis = $request->saat_bitis;

            }

            else{

                $day = '';

                if(date('D', strtotime($tarihler))=='Mon') $day=1;

                else if(date('D', strtotime($tarihler))=='Tue') $day=2;

                else if(date('D', strtotime($tarihler))=='Wed') $day=3;

                else if(date('D', strtotime($tarihler))=='Thu') $day=4;

                else if(date('D', strtotime($tarihler))=='Fri') $day=5;

                else if(date('D', strtotime($tarihler))=='Sat') $day=6;

                else if(date('D', strtotime($tarihler))=='Sun') $day=7;

                $yenirandevu->saat = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',$day)->value('baslangic_saati');

                $yenirandevu->saat_bitis = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',$day)->value('bitis_saati');

                $yenirandevuhizmetpersonel->saat = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',$day)->value('baslangic_saati');

                $yenirandevuhizmetpersonel->saat_bitis = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',$day)->value('bitis_saati');

            }

            

            $yenirandevu->personel_notu = $request->personel_notu;

            $yenirandevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;

            $yenirandevu->save();

           

            $yenirandevuhizmetpersonel->randevu_id = $yenirandevu->id;

            $yenirandevuhizmetpersonel->hizmet_id = 463;

            $yenirandevuhizmetpersonel->personel_id = $request->personel;

            $yenirandevuhizmetpersonel->sure_dk = $request->saat;

            $yenirandevuhizmetpersonel->fiyat = 0;

            

            $yenirandevuhizmetpersonel->save();



        }

        return "Saat kapama başarıyla eklendi";



           









    }

    public function kapalisaatsil(Request $request)

    {

        Randevular::where('id',$request->randevu_id)->delete();

        RandevuHizmetler::where('randevu_id',$request->randevu_id)->delete();

        return 'Saat kapama başarıyla kaldırldı';

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

          $randevusaataraligi = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->value('randevu_saat_araligi');

          $mesaibaslangic =  SalonCalismaSaatleri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('baslangic_saati');

          $mesaibitis = SalonCalismaSaatleri::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('calisiyor',1)->where('haftanin_gunu',$day)->value('bitis_saati');

           

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

        $name = '';

        $email = '';

        $cinsiyet = '';

        $adres = '';

        $meslek = '';

        $musteri_tipi = '';

        $sehir = '';

        $cep_telefon = '';

        $referans = '';

        $tc = '';

        $notlar = '';

        if(!empty($request->musteri_id)){

            $musteri = User::where('id',$request->musteri_id)->first();



            $portfoy = MusteriPortfoy::where('salon_id',$request->sube)->where('user_id',$musteri->id)->first();

            $name = $musteri->name;

            $email = $musteri->email;

            $cinsiyet = $musteri->cinsiyet;

            $adres = $musteri->adres;

            $meslek = $musteri->meslek;

            $musteri_tipi =$portfoy->musteri_tipi;

            $sehir = $musteri->id_id;

            $cep_telefon = $musteri->cep_telefon;

            $referans = $portfoy->musteri_tipi;

            $tc = $musteri->tc_kimlik_no;

            $notlar = $portfoy->ozel_notlar;

        }

            

        return json_encode(array(

            'name'=>$name,

            'email'=>$email,

            'cinsiyet'=>$cinsiyet,

            'adres'=>$adres,

            'meslek'=>$meslek,

            'musteri_tipi'=>$musteri_tipi,

            'sehir'=>$sehir,

            'cep_telefon'=>$cep_telefon,

            'referans'=>$referans,

            'tc'=>$tc,

            'notlar'=>$notlar,

        ));

    }

    public function kampanyalar(){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $kampanyalar = SalonKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $kampanyahtml = "";

        foreach ($kampanyalar as $key => $value) {

            $kampanyahtml .= "<tr><td>".$value->kampanya_aciklama."</td>

                                    

                                   <td>".date('d.m.Y',strtotime($value->kampanya_baslangic_tarihi))."</td>

                                   <td>".date('d.m.Y',strtotime($value->kampanya_bitis_tarihi))."</td>

                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->count()."</td>

                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('kullanildi',1)->count()."</td>

                                     <td>".SatinAlinanKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('kullanildi',0)->count()."</td>";



                                   if($value->onayli == 1)



                                        $kampanyahtml .= "<td style='color:green'>Aktif</td>";

                                   else if($value->onayli == 0)

                                     $kampanyahtml .= "<td style='color:red'>Pasif</td>";

                                   $kampanyahtml .= "</tr>";



        }

        if($kampanyalar->count()==0){

            $kampanyahtml .= "<tr><td style='color:red' colspan='8'><strong>Henüz yayınlanmış avantajınız bulunmamaktadır!</strong></td></tr>";

        }

        return view('isletmeadmin.kampanyalar',['kampanyalar' => $kampanyalar, 'pageindex'=> 105,'title'=>'Avantajlarım | '.$isletme->salon_adi.' Salon Yönetim Paneli','isletme'=>$isletme,'kampanyahtml' => $kampanyahtml, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

             $kampanyalar = SalonKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        

        $kampanyahtml = "";

        foreach ($kampanyalar as $key => $value) {

            $kampanyahtml .= "<tr><td>".$value->kampanya_aciklama."</td>

                                    

                                   <td>".date('d.m.Y',strtotime($value->kampanya_baslangic_tarihi))."</td>

                                   <td>".date('d.m.Y',strtotime($value->kampanya_bitis_tarihi))."</td>

                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->count()."</td>

                                   <td>".SatinAlinanKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('kullanildi',1)->count()."</td>

                                     <td>".SatinAlinanKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('kullanildi',0)->count()."</td>";



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

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $kampanyalar = SalonKampanyalar::where('id',$id)->first();

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $kampanyadetay = SatinAlinanKampanyalar::where('kampanya_id',$id)->get();

        return view('isletmeadmin.kampanyadetay',['pageindex'=> 105,'title'=>'Kampanya Detayları | '.$isletme->salon_adi.' İşletme Yönetim Paneli','isletme'=>$isletme,'kampanyadetay' => $kampanyadetay, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function yenikampanyaekle(){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        return view('isletmeadmin.yenikampanya',['pageindex'=> 105,'title'=>'Yeni Kampanya Ekle | '.$isletme->salon_adi.' İşletme  Yönetim Paneli','isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);



    }

    public function avantajyapilanodemeler(){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $avantajodemeler = KampanyaYapilanOdemeler::join('kampanyalar','kampanya_yapilan_odemeler.kampanya_id','=','kampanyalar.id')->where('kampanyalar.salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->select('kampanya_yapilan_odemeler.id','kampanyalar.kampanya_aciklama','kampanya_yapilan_odemeler.created_at','kampanya_yapilan_odemeler.adet','kampanya_yapilan_odemeler.tutar')->get();

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

         

        return view('isletmeadmin.kampanyayapilanodemeler',['pageindex'=>1051,'title'=>'Yapılan Ödemeler | '.$isletme->salon_adi.' İşletme Yönetim Paneli','avantajodemeler'=> $avantajodemeler,'isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

                        $portfoy->salon_id = Auth::guard('isletmeyonetim')->user()->salon_id;

                        $portfoy->aktif = true;

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

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $smslistedetaylari = SMSListeBilgiler::where('sms_listeleri_id',$listeid)->get();

        $listeadi = SMSListeleri::where('id',$listeid)->value('sms_liste_adi');

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        return view('isletmeadmin.smslistedetay',['title' => $listeadi.' SMS Liste Detayı | Avantajbu.com','pageindex' => 107,'isletme'=>$isletme,'smslistedetaylari'=> $smslistedetaylari, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

         $raporhtml = "";

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $raporlar = SMSIletimRaporlari::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $smsbilgiler = SMSBilgiler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

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

        return view('isletmeadmin.smsraporlar',['pageindex'=> 109,'title'=>'SMS Raporlarım | '.$isletme->salon_adi.' İşletme Yönetim Paneli','isletme'=>$isletme,'rapor'=>$raporhtml, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

     public function smstaslakolarakkaydet(Request $request){

             $taslak = new SMSTaslaklari();

             $taslak->baslik=$request->baslik;

        $taslak->taslak_icerik = $request->sablonsmsmesaj;

        $taslak->salon_id = $request->sube;

        $taslak->save();

         

          $taslakhtml = "";

        

        $taslaklar = SMSTaslaklari::where('salon_id',self::mevcutsube($request))->get();

         foreach($taslaklar as $taslak){

             $taslakhtml .= '

           <div class="col-md-3">

                <div class="form-group">

                  

<div style="   width:100%; max-height:100%; margin-left: 5px; margin-top: 15px; ">

<input type="hidden" id="smstaslak'.$taslak->id.'" value="'.$taslak->taslak_icerik.'">

                 <input type="hidden" id="smstaslakbaslik'.$taslak->id.'" value="'.$taslak->baslik.'">

                  <a class="smstaslaklari" title="Metni Kopyala"  data-value="'.$taslak->id.'" style="position:relative; cursor: pointer;"  name="smstaslaklari" >

                 

                   <p style="border:1px solid grey;font-size:18px;font-weight: bold;color:black ;border-radius: 30px; text-align: center;">'.$taslak->baslik.'</p>

                  <p style="border:1px solid grey;padding:5px;background-color: #e4e4e2; border-radius: 20px;border-bottom-left-radius: 0;color:black;font-size:15px; overflow: hidden;

    display: -webkit-box;

    -webkit-line-clamp: 5;

    -webkit-box-orient: vertical;" >'.$taslak->taslak_icerik.'</p>

                

                  

                </a>

           </div>

           

                </div>

          

           </div>';

         

       

        }

     

        

        

         return array(

          'sonuc'=>'Şablon başarıyla oluşturuldu',

          'liste'=>$taslakhtml

        );

           

    }

    public function toplusmsgonderme(Request $request){

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $gsm = array();

        $mesajlar=array();

        foreach ($request->duallistbox_demo2 as $musteri) {

            $toplumusteri = User::where('id',$musteri)->first();

            if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$isletme->id)->value('kara_liste')!=1)

                array_push($mesajlar, array("to"=>$toplumusteri->cep_telefon,"message"=> $request->smsmesaj));

                    

        }

        if(count($mesajlar) > 0){

            return self::sms_gonder($request,$mesajlar,true,4,false);

            exit;

        }

        else

        {

            return array(

                'text' => 'Seçili müşteriler karalistenizde olduğu için mesajınız gönderilmemiştir',

                'title' => 'Hata',

                'status' => 'error',

            );

            exit;

        }

    }

    public function musteriliste(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        

        

         

        //usteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->leftjoin('randevular','musteri_portfoy.user_id','=','randevular.user_id')->select('users.name as name','users.cep_telefon as cep_telefon','users.created_at as created_at',DB::raw('COUNT(*)'));

        $paketler = self::paket_liste_getir('',true,$request);

         

        return view('isletmeadmin.musteriler',[ 'bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'sayfa_baslik'=>'Müşteriler','pageindex' => 4,  'isletme'=> $isletme,'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function musteri_liste_deneme(Request $request)

    {

       

        echo 'Bitti';



    }

     

         

    public function musteri_liste_getir(Request $request,$durum)

    {

         

        $musteriler = '';

        $length = $request->input('length');

        $start = $request->input('start', 0);

        $dir = $request->input('order.0.dir');

        $searchValue = $request->input('search.value'); // Search value

        if ($length == 0) {

            $length = 10; // Default length value if zero

        }

        $currentPage = ($start / $length) + 1;



        if($durum == 3)

        {

             

            $musteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->

            leftjoin('randevular','randevular.user_id','=','users.id')->

            leftJoin('adisyonlar','adisyonlar.user_id','users.id')->

            leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('tahsilatlar','tahsilatlar.adisyon_id','=','adisyonlar.id')

            ->leftjoin('tahsilat_hizmetler as th1','th1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_urunler as tu1','tu1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_paketler as tp1','tp1.tahsilat_id','=','tahsilatlar.id')->

       

             



            join('salonlar','musteri_portfoy.salon_id','=','salonlar.id')

            ->select('users.id as id','users.name as ad_soyad','users.cep_telefon as telefon',

                DB::raw('DATE_FORMAT(musteri_portfoy.created_at,"%d.%m.%Y") as kayit_tarihi'),

                DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id and randevular.salon_id = salonlar.id) as randevu_sayisi'),

                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id and  randevular.salon_id = musteri_portfoy.salon_id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),

                     



                     DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id AND adisyon_hizmetler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id AND adisyon_urunler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id AND adisyon_paketler.adisyon_id = adisyonlar.id and adisyonlar.salon_id = salonlar.id )),2,"tr_TR"),"</button>")  as odenen'),









                DB::raw('CONCAT("<div class=\"dropdown\">

                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                      href=\"#\"

                                      role=\"button\"

                                      data-toggle=\"dropdown\"

                                    ><i class=\"dw dw-more\"></i>

                            </a>

                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                     

                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"?sube=",musteri_portfoy.salon_id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>



                                           <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                      data-target=\"#musteri-bilgi-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                        <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>

                                    </div>

                                    </div>") AS islemler'),

            )->where('musteri_portfoy.salon_id',self::mevcutsube($request))

            ->where('musteri_portfoy.aktif',true)

            

            ->where('salonlar.id',self::mevcutsube($request))

            ->groupBy('users.id')->orderBy('users.id','desc'); 



        }

        if($durum == 0){

            $musteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->

            leftjoin('randevular','randevular.user_id','=','users.id')->

            leftJoin('adisyonlar','adisyonlar.user_id','users.id')->

            leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

              ->leftjoin('tahsilatlar','tahsilatlar.adisyon_id','=','adisyonlar.id')

            ->leftjoin('tahsilat_hizmetler as th1','th1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_urunler as tu1','tu1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_paketler as tp1','tp1.tahsilat_id','=','tahsilatlar.id')

            ->join('salonlar','musteri_portfoy.salon_id','=','salonlar.id')

            ->select('users.name as ad_soyad','users.cep_telefon as telefon',

                DB::raw('DATE_FORMAT(musteri_portfoy.created_at,"%d.%m.%Y") as kayit_tarihi'),

                 DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id and randevular.salon_id = musteri_portfoy.salon_id) as randevu_sayisi'),

                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id and  randevular.salon_id = musteri_portfoy.salon_id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),

                DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id AND adisyon_hizmetler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id AND adisyon_urunler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id AND adisyon_paketler.adisyon_id = adisyonlar.id and adisyonlar.salon_id = salonlar.id )),2,"tr_TR"),"</button>")  as odenen'),

                DB::raw('CONCAT("<div class=\"dropdown\">

                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                      href=\"#\"

                                      role=\"button\"

                                      data-toggle=\"dropdown\"

                                    ><i class=\"dw dw-more\"></i>

                            </a>

                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                     

                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"?sube=",musteri_portfoy.salon_id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>



                                           <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#musteri-bilgi-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                    <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>

                                    </div>

                                    </div>") AS islemler'),

            )->where('musteri_portfoy.salon_id',self::mevcutsube($request)) 

             ->where('musteri_portfoy.aktif',true)

            ->having(DB::raw('(SELECT  COUNT(*) FROM tahsilatlar where tahsilatlar.user_id = users.id and tahsilatlar.salon_id = '.self::mevcutsube($request).')') ,0)  

            ->groupBy('users.id')->orderBy('users.id','desc');

          

        }

           

        if($durum == 1){

             $musteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->

            leftjoin('randevular','randevular.user_id','=','users.id')->

            leftJoin('adisyonlar','adisyonlar.user_id','users.id')->

   leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

              ->leftjoin('tahsilatlar','tahsilatlar.adisyon_id','=','adisyonlar.id')

            ->leftjoin('tahsilat_hizmetler as th1','th1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_urunler as tu1','tu1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_paketler as tp1','tp1.tahsilat_id','=','tahsilatlar.id')

            ->join('salonlar','musteri_portfoy.salon_id','=','salonlar.id')

            ->select('users.name as ad_soyad','users.cep_telefon as telefon',

                DB::raw('DATE_FORMAT(musteri_portfoy.created_at,"%d.%m.%Y") as kayit_tarihi'),

                 DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id and randevular.salon_id = musteri_portfoy.salon_id) as randevu_sayisi'),

                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id and randevular.salon_id = musteri_portfoy.salon_id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),

    DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id AND adisyon_hizmetler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id AND adisyon_urunler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id AND adisyon_paketler.adisyon_id = adisyonlar.id and adisyonlar.salon_id = salonlar.id )),2,"tr_TR"),"</button>")  as odenen'),

                DB::raw('CONCAT("<div class=\"dropdown\">

                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                      href=\"#\"

                                      role=\"button\"

                                      data-toggle=\"dropdown\"

                                    ><i class=\"dw dw-more\"></i>

                            </a>

                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                     

                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"?sube=",musteri_portfoy.salon_id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>



                                            <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#musteri-bilgi-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                   <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>

                                    </div>

                                    </div>") AS islemler'),

            )->where('musteri_portfoy.salon_id',self::mevcutsube($request)) 

             ->where('musteri_portfoy.aktif',true)

            ->having(DB::raw('(SELECT  COUNT(*) FROM tahsilatlar where tahsilatlar.user_id = users.id and tahsilatlar.salon_id = '.self::mevcutsube($request).')') ,'<=', 2)  

            ->having(DB::raw('(SELECT  DATE_ADD(tahsilatlar.created_at , INTERVAL 3 MONTH) FROM tahsilatlar where tahsilatlar.user_id = users.id  and tahsilatlar.salon_id = '.self::mevcutsube($request).' order by tahsilatlar.id desc limit 1)'),'>=',date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))->groupBy('users.id')->orderBy('users.id','desc');

           

        }

        if($durum == 2){

                

                  $musteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->

            leftjoin('randevular','randevular.user_id','=','users.id')->

            leftJoin('adisyonlar','adisyonlar.user_id','users.id')->

   leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

              ->leftjoin('tahsilatlar','tahsilatlar.adisyon_id','=','adisyonlar.id')

            ->leftjoin('tahsilat_hizmetler as th1','th1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_urunler as tu1','tu1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_paketler as tp1','tp1.tahsilat_id','=','tahsilatlar.id')

            ->join('salonlar','musteri_portfoy.salon_id','=','salonlar.id')

            ->select('users.name as ad_soyad','users.cep_telefon as telefon',

                DB::raw('DATE_FORMAT(musteri_portfoy.created_at,"%d.%m.%Y") as kayit_tarihi'),

                  DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id and randevular.salon_id = musteri_portfoy.salon_id) as randevu_sayisi'),

                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),

                DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id AND adisyon_hizmetler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id AND adisyon_urunler.adisyon_id = adisyonlar.id AND adisyonlar.salon_id = salonlar.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id AND adisyon_paketler.adisyon_id = adisyonlar.id and adisyonlar.salon_id = salonlar.id )),2,"tr_TR"),"</button>")  as odenen'),

                DB::raw('CONCAT("<div class=\"dropdown\">

                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                      href=\"#\"

                                      role=\"button\"

                                      data-toggle=\"dropdown\"

                                    ><i class=\"dw dw-more\"></i>

                            </a>

                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                     

                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"?sube=",musteri_portfoy.salon_id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>



                                            <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#musteri-bilgi-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                    <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>

                                    </div>

                                    </div>") AS islemler'),

            )->where('musteri_portfoy.salon_id',self::mevcutsube($request)) 

             ->where('musteri_portfoy.aktif',true)

            ->having(DB::raw('(SELECT  COUNT(*) FROM tahsilatlar where tahsilatlar.user_id = users.id and tahsilatlar.salon_id = '.self::mevcutsube($request).')') ,'>=', 3)  

            ->having(DB::raw('(SELECT  DATE_ADD(tahsilatlar.created_at , INTERVAL 3 MONTH) FROM tahsilatlar where tahsilatlar.user_id = users.id and tahsilatlar.salon_id = '.self::mevcutsube($request).' order by tahsilatlar.id desc limit 1)'),'<',date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))->groupBy('users.id')->orderBy('users.id','desc');

           

        }

        if ($searchValue) {

            $musteriler->where(function($query) use ($searchValue) {

                $query->where('users.name', 'like', '%' . $searchValue . '%')

                      ->orWhere('users.cep_telefon', 'like', '%' . $searchValue . '%');

            });

        }

        $musteriler = $musteriler->paginate($length, ['*'], 'page', $currentPage);

        return response()->json([

            'data' => $musteriler->items(),

            'draw' => $request->input('draw'),

            'recordsTotal' => $musteriler->total(),

            'recordsFiltered' => $musteriler->total(),

        ]);

       // return $musteriler;

         

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

        $portfoymusteri = MusteriPortfoy::where('user_id',$request->musteriid)->where('salon_id',self::mevcutsube($request))->first();







        $result['sonuc'] = array();

        $result['liste'] = array();

        $result['toplammusteri'] = array();

        if($portfoymusteri){

            $adsoyad = $portfoymusteri->users->name;

            $portfoymusteri->delete();

            array_push($result['sonuc'], $adsoyad .' isimli müşteri portföyünüzden başarıyla kaldırıldı');

        }

        $portfoy = MusteriPortfoy::where('salon_id',self::mevcutsube($request))->get();

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

            $portfoytablohtml .= '<tr><td class="user-avatar"><img src="http://'.$_SERVER['HTTP_HOST'].'/'.$musteriprofilresim.'" alt="Profil Resmi"></td><td>'.$user->name.'</td><td>'.$cinsiyet.'</td><td style="color:white"><span style="position:relative;float:left;width:30px; text-align:center;background-color:'.$renk1.';padding:5px">'.$tur.'</span></td>

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

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  



        $resulthtml['sonuc'] = array();

        $resulthtml['liste'] = array();

        $resulthtml['toplammusteri'] = array();

        $portfoytablohtml = "";

        $portfoy = '';

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

                                if($data['cep_telefon'] != ''){

                                    $eskidata = User::where('cep_telefon',$data['cep_telefon'])->first();

                                    if($eskidata)

                                    {

                                    

                                        

                                        $portfoyvar = MusteriPortfoy::where('user_id', $eskidata->id)->where('salon_id', $_POST['sube'])->first();

                                        if(!$portfoyvar){

                                            $portfoyyeni = new MusteriPortfoy();

                                            $portfoyyeni->aktif = true;

                                            $portfoyyeni->salon_id = $_POST['sube'];

                                            $portfoyyeni->user_id = $eskidata->id;

                                            $portfoyyeni->save();

                                            $count++;                                  



                                           

                                        }

                                

                                    }

                                    else

                                    {

                                        $yenimusteri = new User($data);

                                        $yenimusteri->save();

                                        $portfoyyeni2 = new MusteriPortfoy();

                                        $portfoyyeni2->aktif = true;

                                        $portfoyyeni2->salon_id = $_POST['sube'];

                                        $portfoyyeni2->user_id = $yenimusteri->id;

                                        $portfoyyeni2->aktif=true;

                                        $portfoyyeni2->save();

                                        $count++;

                                    }

                                }

                                

                                else{

                                    $eskidata = User::where('name',$data['name'])->first();

                                    if($eskidata)

                                    {

                                        $portfoyvar = MusteriPortfoy::where('user_id', $eskidata->id)->where('salon_id', $_POST['sube'])->first();

                                        if(!$portfoyvar){

                                            $portfoyyeni = new MusteriPortfoy();

                                            $portfoyyeni->aktif = true;

                                            $portfoyyeni->salon_id = $_POST['sube'];

                                            $portfoyyeni->user_id = $eskidata->id;

                                            $portfoyyeni->aktif=true;

                                            $portfoyyeni->save();

                                            $count++;  

                                        }

                                    }

                                    $yenimusteri2 = new User($data);

                                    $yenimusteri2->save();

                                    $portfoyyeni3 = new MusteriPortfoy();

                                    $portfoyyeni3->aktif = true;

                                    $portfoyyeni3->salon_id = $_POST['sube'];

                                    $portfoyyeni3->user_id = $yenimusteri2->id;

                                    $portfoyyeni3->aktif = true;

                                    $portfoyyeni3->save();

                                    $count++;

                                }

                                

                            }

                        }

                        

                       

                    })->toArray();

                    $returnmessage = '';

                    

                    if(count($isletmeler)>1)

                    array_push($resulthtml['sonuc'],$count . ' adet müşteri listenize başarı ile eklendi.');





                    



                    array_push($resulthtml['liste'], self::musteri_liste_getir($request,3));

                    array_push($resulthtml['toplammusteri'], $count);

                    

              } 



        }

        else{

            array_push($resulthtml['sonuc'],'Lütfen excel dosyası yükleyiniz');

            array_push($resulthtml['liste'], $portfoytablohtml);

            

        }

        return $resulthtml;



    }

    public function musteridetay(Request $request,$id){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();



        $randevular_liste = self::randevu_liste_getir($request,'','','','','','',self::mevcutsube($request),$id); 

        $randevular = Randevular::where('user_id',$id)->orderBy('tarih','desc')->where('salon_id',self::mevcutsube($request))->get();

        $form= self::arsiv_liste_getir($request,'',$id);

        $adisyonlar = self::adisyon_yukle($request,'','','1970-01-01 00:00:00',date('Y-m-d 23:59:59'),$id,'');

        $portfoy = MusteriPortfoy::where("user_id",$id)->where('salon_id',$isletme->id)->first();

        $paketler = self::paket_liste_getir('',true,$request);

        $form_onayli= self::arsiv_liste_getir($request,0,$id);

        $form_iptal= self::arsiv_liste_getir($request,1,$id);

        $form_beklenen= self::arsiv_liste_getir($request,2,$id);

        $form_harici= self::arsiv_liste_getir($request,3,$id);

        $islemler = Islemler::where('user_id',$id)->get();

        return view('isletmeadmin.musteridetay',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'pageindex'=>41, 'sayfa_baslik'=> $portfoy->users->name,'isletme'=>$isletme,'portfoy'=>$portfoy,'arsiv'=>$form, 'musteri_bilgi'=>$portfoy->users,'randevular'=>$randevular,'adisyonlar'=>$adisyonlar, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'arsiv_onayli'=>$form_onayli, 'arsiv_iptal'=>$form_iptal, 'arsiv_beklenen'=>$form_beklenen, 'arsiv_harici'=>$form_harici,'randevular_liste'=>$randevular_liste,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'islemler'=>$islemler,'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function musteriexceleaktar(){

        $musteriler = MusteriPortfoy::where('salon_id',self::mevcutsube($request))->get();

        $musteri_array[] = array('Ad Soyad','Cinsiyet','Tür','E-posta','Cep Telefon','Randevu Sayısı','Puanlama');

        foreach ($musteriler as $key => $value) {

            $user=User::where('id',$value->user_id)->first();

            $tur = "";

            $cinsiyet = "";

            if($value->tur == 1)

                    $tur = 'Avantajbu.com';

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

        $avantajlar = SalonKampanyalar::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

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

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();



        $smspaketler = SMSPaketleri::all();

         return view('isletmeadmin.toplusmsbasvuru',['title' => 'Toplu SMS Paket Satın Al - Başvur | Avantajbu.com','pageindex' => 114,'isletme'=>$isletme,'smspaketler' => $smspaketler, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

        if(Auth::guard('isletmeyonetim')->user()->is_admin)

            $randevular = Randevular::where('salon_id',self::mevcutsube($request))->where('tarih','like','%'.$request->tarih.'%')->where('sube_id','like','%'.$request->sube.'%')->orderBy('id','desc')->get();

        else

            $randevular = Randevular::where('salon_id',self::mevcutsube($request))->where('tarih','like','%'.$request->tarih.'%')->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->orderBy('id','desc')->get();



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

        $portfoy = MusteriPortfoy::where('id',$request->portfoy_id)->first();

        $portfoy->aktif = false;

        $portfoy->save();



        return array(

                'title' => "Başarılı",

                

                'mesaj' => "Kayıt başarıyla silindi.",

                'musteri_id' => $portfoy->user_id,

                'yeniekleme' => false,

                'status' => 'success',

                'musteribilgi' => '',

                'musteriler' => self::musteri_liste_getir($request,3),

                'aktif_musteriler' => self::musteri_liste_getir($request,1),

                'sadik_musteriler' => self::musteri_liste_getir($request,2),

                'pasif_musteriler' => self::musteri_liste_getir($request,0),



        ); 

      

        

    }

    public function randevudetay(Request $request,$id){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $personeller="";

        $randevu = Randevular::where('id',$id)->first();

        $isletme = Salonlar::where('id',Auth::guard('isletmeyonetim')->user()->salon_id)->first();

        $hizmetler = SalonHizmetler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $randevu_hizmetler = RandevuHizmetler::where('randevu_id',$id)->get();

        $subeler = Subeler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('aktif',1)->get();

        $urunler = UrunSatislari::where('randevu_id',$id)->get();

        $tum_urunler = Urunler::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        $tahsilatlar = Tahsilatlar::where('randevu_id',$id)->get();



        $tahsilat_tutari = Tahsilatlar::where('tahsilat_tutari',$id)->sum('tutar');

        $toplam_tutar = UrunSatislari::where('randevu_id',$id)->sum('fiyat') + RandevuHizmetler::where('randevu_id',$id)->sum('fiyat');

          



        if(Auth::guard('isletmeyonetim')->user()->is_admin)

            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        else

            $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('sube_id',Auth::guard('isletmeyonetim')->user()->salon_personelleri->sube_id)->get();

        $paketler = self::paket_liste_getir('',true,$request);

        return view('isletmeadmin.randevudetay',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'pageindex'=>111, 'sayfa_baslik'=>$randevu->users->name.' Adisyon Detayları','randevu'=>$randevu,'isletme'=>$isletme,'personeller'=>$personeller,'subeler'=>$subeler,'sunulanhizmetler'=>$hizmetler,'hizmetler'=>$randevu_hizmetler,'urunler'=>$urunler,'tum_urunler'=>$tum_urunler,'tahsilatlar'=>$tahsilatlar,'tahsilat_tutari'=>$tahsilat_tutari, 'toplam_tutar'=> $toplam_tutar, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function adisyondetay(Request $request,$id)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0); 

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]); 

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $adisyon = Adisyonlar::where('id',$id)->first();

         

        $paketler = self::paket_liste_getir('',true,$request);

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        

         

        $tahsilatlar = Tahsilatlar::where('user_id',$adisyon->user_id)->get();

        return view('isletmeadmin.adisyondetay',['isletme'=>$isletme,'paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request), 'sayfa_baslik'=>$adisyon->musteri->name .' '.date('d.m.Y', strtotime($adisyon->created_at)).' tarihli adisyon detayı','pageindex' => 111,'adisyon'=>$adisyon,'request'=>$request, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request), 'musteri'=>$adisyon->musteri,'tahsilatlar'=>$tahsilatlar,'adisyon_id'=>$id,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function tahsilatekrani(Request $request,$musteriid)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

         if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $acik_adisyonlar = self::adisyon_yukle($request,'','','1970-01-01',date('Y-m-d'),$musteriid,''); 

        $user = User::where('id',$musteriid)->first();

        $paketler = self::paket_liste_getir('',true,$request);

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        

         

        $tahsilatlar = Tahsilatlar::where('user_id',$musteriid)->where('salon_id',self::mevcutsube($request))->get();

        $request->attributes->set('musteriid',$musteriid);



        $tum_senetler = self::senetvadegetir_tahsilat($request);

        $tum_takstiler = self::taksitvadegetir_tahsilat($request);

        $senet_gelen_vadeler = SenetVadeleri::join('senetler','senet_vadeleri.senet_id','=','senetler.id')->select('senet_vadeleri.id as senet_vade_id','senet_vadeleri.vade_tarih as tarih','senet_vadeleri.tutar as tutar')->where('senetler.user_id',$musteriid)->where('senet_vadeleri.vade_tarih','<=',date('Y-m-d'))->where('odendi',false)->get();

        $taksit_gelen_vadeler = TaksitVadeleri::join('taksitli_tahsilatlar','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->select('taksit_vadeleri.id as taksit_vade_id','taksit_vadeleri.vade_tarih as tarih','taksit_vadeleri.tutar as tutar')->where('taksitli_tahsilatlar.user_id',$musteriid)->where('taksit_vadeleri.vade_tarih','<=',date('Y-m-d'))->where('odendi',false)->get();

        return view('isletmeadmin.tahsilat',['isletme'=>$isletme,'paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request), 'sayfa_baslik'=>$user->name .' Tahsilatları','pageindex' => 1111,'acik_adisyonlar'=>$acik_adisyonlar,'request'=>$request, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request), 'musteri'=>$user,'tahsilatlar'=>$tahsilatlar,'senet_gelen_vadeler'=>$senet_gelen_vadeler,'taksit_gelen_vadeler'=>$taksit_gelen_vadeler,'tum_senetler'=>$tum_senetler,'tum_taksitler'=>$tum_takstiler,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);





    }

   public function urunler(Request $request){



        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) ||$isletme->uyelik_turu<2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $urunler = self::urun_liste_getir($request,"");

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $paketler = self::paket_liste_getir('',true,$request);

        return view('isletmeadmin.urunler',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'pageindex'=>30, 'sayfa_baslik'=>'Ürünler','urunler'=>$urunler,'isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

        $urun->dusuk_stok_siniri = $request->dusuk_stok_siniri;

        $urun->salon_id = self::mevcutsube($request);

        $urun->aktif=true;

        $urun->save();



        

        return self::urun_liste_getir($request,$returntext);



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

        $paket->paket_adi = $request->adpaket;

        $paket->aktif = true;

        $paket->salon_id = $request->sube;



        $paket->save();

        $toplamtutar = 0;

        PaketHizmetler::where('paket_id',$paket->id)->delete();

        foreach($request->hizmetler as $key => $paket_hizmet)

        {

            $pakethizmet = new PaketHizmetler();

            $pakethizmet->paket_id = $paket->id;

            $pakethizmet->hizmet_id = $paket_hizmet;

            $pakethizmet->seans = $request->seanslar[$key];

            $pakethizmet->fiyat = $request->fiyatlar[$key];

            $toplamtutar += $request->fiyatlar[$key]; 

            $pakethizmet->save();

        }



        

        return array(

            'paketler' => self::paket_liste_getir($returntext,false,$request),

            'eklenen_paket_id' =>$paket->id,

            'eklenen_paket' => $paket->paket_adi,

            'toplam_tutar' => $toplamtutar

        );

    }



    public function paketdetayigetir(Request $request)

    {

        $paket = Paketler::where('id',$request->paket_id)->first();

        $html = '';

        foreach ($paket->hizmetler as $key=>$hizmet)

        {

            $html .='<div class="row" data-value="'.$key.'">

                              <div class="col-md-4">

                                 <div class="form-group">

                                    <label>Hizmet</label>

                                    <select name="hizmetler[]" class="form-control custom-select2" style="width:100%">';



            foreach(SalonHizmetler::where('salon_id',$request->sube)->get() as $hizmetliste)

            {

                if($hizmet->hizmet_id == $hizmetliste->hizmet_id)

                    $html .= '<option selected value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

                else

                    $html .= '<option value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

            }

            $html .= '</select>

                                 </div>

                              </div>

                              <div class="col-md-3">

                                 <div class="form-group">

                                    <label>Seans</label>

                                    <input type="tel" required name="seanslar[]" class="form-control" required value="'.$hizmet->seans.'">

                                 </div>

                              </div>

                              <div class="col-md-4">

                                 <div class="form-group">

                                    <label>Fiyat (₺)</label>

                                    <input type="tel" name="fiyatlar[]" class="form-control" required value="'.$hizmet->fiyat.'">

                                 </div>

                              </div>

                              <div class="col-md-1">

                                 <div class="form-group">

                                    <label style="visibility: hidden;width: 100%;">Kaldır</label>';

            if($key == 0)

                $html .= ' <button type="button" name="paket_hizmet_formdan_sil_duzenleme"  data-value="'.$key.'" disabled class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>';

            else

                $html .= '<button type="button" name="paket_hizmet_formdan_sil_duzenleme"  data-value="'.$key.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>';

            $html .= '</div>

                              </div>

                           </div>';

        }

        return array(

            'id' => $paket->id,

            'paket_adi' => $paket->paket_adi,

            'paket_hizmetler' =>$html

           

        );

    }



    public function paket_sil(Request $request){

        $paket = Paketler::where('id',$request->paket_id)->first();

        $paket->aktif = false;

        $paket->save();



        return self::paket_liste_getir("Paket başarıyla kaldırıldı",false,$request);

    }

     public function salonhizmetsil(Request $request){

        

        $sunulan_hizmet = SalonHizmetler::where('id',$request->sunulan_hizmet_id)->first();

        $sunulan_hizmet->aktif = false;

        $sunulan_hizmet->save();

        $secilmeyenhizmetler = self::secilmeyen_hizmet_liste_getir($request);

         

        return self::hizmet_liste_getir($request,"Hizmet başarıyla kaldırıldı",$secilmeyenhizmetler);

    }



    public function urun_sil(Request $request){

        $urun = Urunler::where('id',$request->urun_id)->first();

        $urun->aktif = false;

        $urun->save();

        

        return self::urun_liste_getir($request,"Ürün başarıyla kaldırıldı");



    }



                                

                         

 public function urun_liste_getir(Request $request,$returntext){

        $urun_liste = DB::table('urunler')->select(

              DB::raw('CONCAT("<div class=\"dt-checkbox\"><input type=\"checkbox\" style=\"margin-left:11px;\"  name=\"urun_bilgi[]\" value=\"",id,"\"><span style=\"margin-left:11px;\" class=\"dt-checkbox-label\"></span></div>") as id'),

            'urun_adi','stok_adedi','fiyat','barkod','dusuk_stok_siniri',DB::raw('CONCAT("<div class=\"dropdown\">

                        <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                  href=\"#\"

                                  role=\"button\"

                                  data-toggle=\"dropdown\"

                                ><i class=\"dw dw-more\"></i>

                        </a>

                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                    <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#urun-modal-duzenle\" name=\"urun_duzenle\" data-value=\"",id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                                    <a class=\"dropdown-item\" href=\"#\" name=\"urun_sil\" data-value=\"",id,"\"><i class=\"fa fa-remove\"></i> Sil</a>

                                </div>

                                </div>") AS islemler'))->where('salon_id',self::mevcutsube($request))->where('aktif',true)->orderBy('id','desc')->get();

        return array(

                'status' => $returntext,

                'urun_liste' => $urun_liste,

    

        ); 

    }



    public function paket_liste_getir($returntext,$addition,$request){

        $paket_liste = "";

        if($addition)

            $paket_liste = DB::table('paketler')->join('paket_hizmetler','paketler.id','=','paket_hizmetler.paket_id')->join('hizmetler','paket_hizmetler.hizmet_id','=','hizmetler.id')->select( 

                

                 DB::raw('CONCAT( GROUP_CONCAT(hizmetler.hizmet_adi)) as hizmetler'),

                        DB::raw('CONCAT( GROUP_CONCAT(paket_hizmetler.seans)) as seanslar'),'paketler.paket_adi as paket_adi',DB::raw('CONCAT(COALESCE(SUM(paket_hizmetler.fiyat),0)) as fiyat'),DB::raw('CONCAT("<button title=\"Ekle\" class=\"btn btn-success\" name=\"satis_formuna_paket_ekle\" data-value=\"",paketler.id,"\"><i class=\"fa fa-plus\"></i> Ekle</a>") AS islemler'))->where('paketler.salon_id',self::mevcutsube($request))->orderBy('paketler.id','desc')->groupBy('paket_hizmetler.paket_id')->get();

        else

            $paket_liste = DB::table('paketler')->join('paket_hizmetler','paketler.id','=','paket_hizmetler.paket_id')->join('hizmetler','paket_hizmetler.hizmet_id','=','hizmetler.id')->

                    select(

                      DB::raw('CONCAT("<div class=\"dt-checkbox\"><input type=\"checkbox\" style=\"margin-left:11px;\"  name=\"paket_bilgi[]\" value=\"",paketler.id,"\"><span style=\"margin-left:11px;\" class=\"dt-checkbox-label\"></span></div>") as id'),

                        'paketler.paket_adi as paket_adi', 

                        DB::raw('CONCAT( GROUP_CONCAT(hizmetler.hizmet_adi)) as hizmetler'),

                        DB::raw('CONCAT( GROUP_CONCAT(paket_hizmetler.seans)) as seanslar'),

                        DB::raw('CONCAT(COALESCE(SUM(paket_hizmetler.fiyat),0)) as fiyat'),



                        DB::raw('CONCAT("<div class=\"dropdown\">

                        <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                  href=\"#\"

                                  role=\"button\"

                                  data-toggle=\"dropdown\"

                                ><i class=\"dw dw-more\"></i>

                        </a>

                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                    <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#paket-duzenle-modal\" name=\"paket_duzenle\" data-value=\"",paketler.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                                    <a class=\"dropdown-item\" href=\"#\" name=\"paket_sil\" data-value=\"",paketler.id,"\"><i class=\"fa fa-remove\"></i> Sil</a>



                                </div>

                                </div>") AS islemler'))->where('aktif',true)->where('paketler.salon_id',self::mevcutsube($request))->groupBy('paket_hizmetler.paket_id')->orderBy('paketler.id','desc')->get();

        

        return array(

                'status' => $returntext,

                'paket_liste' => $paket_liste,

    

        );

    }



    public function hizmet_liste_getir(Request $request,$returntext,$secilmeyenhizmetler){

    $hizmet_liste = "";

      $hizmet_liste= DB::table('salon_sunulan_hizmetler')->

        join('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')

        ->leftjoin('personel_sunulan_hizmetler','salon_sunulan_hizmetler.hizmet_id','=','personel_sunulan_hizmetler.hizmet_id')

        ->leftjoin('cihaz_sunulan_hizmetler','cihaz_sunulan_hizmetler.hizmet_id','=','salon_sunulan_hizmetler.hizmet_id')

        ->leftjoin('salon_personelleri','personel_sunulan_hizmetler.personel_id','=','salon_personelleri.id')

        ->leftjoin('cihazlar','cihaz_sunulan_hizmetler.cihaz_id','=','cihazlar.id')

        ->select("hizmetler.id as hizmet_id","personel_sunulan_hizmetler.personel_id as personel_id",'hizmetler.hizmet_adi as hizmet_adi',DB::raw('GROUP_CONCAT(salon_personelleri.personel_adi ) as personel'),DB::raw('CONCAT("<div class=\"dropdown\">

                        <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                  href=\"#\"

                                  role=\"button\"

                                  data-toggle=\"dropdown\"

                                ><i class=\"dw dw-more\"></i>

                        </a>

                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                    <a class=\"dropdown-item\"  name=\"hizmet_duzenle\" data-value=\"",salon_sunulan_hizmetler.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                                    <a class=\"dropdown-item\" href=\"#\" name=\"hizmet_sil\" data-value=\"",salon_sunulan_hizmetler.id,"\"><i class=\"fa fa-remove\"></i> Sil</a>

                                </div> </div>") AS islemler'))->where('salon_sunulan_hizmetler.salon_id',self::mevcutsube($request))->where('salon_personelleri.salon_id',

        self::mevcutsube($request))->where('salon_sunulan_hizmetler.aktif',true)->groupBy('hizmetler.id')->get();

        

          return array(

                'status' => $returntext,

                'hizmet_liste' => $hizmet_liste,

                'secilmeyen_hizmetler'=>$secilmeyenhizmetler

    

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

    public function paketfiyatgetir(Request $request){

        return PaketHizmetler::where('paket_id',$request->paket_id)->sum('fiyat');

        

    }

     

    public function urunsatisiekle(Request $request){

        $adisyon_id = '';

        if(isset($request->adisyon_id)){

            $adisyon_id = $request->adisyon_id;

        }

        else

        {

            $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id,$request->sube,'Ürün Satışı',$request->urun_satis_tarihi);

        }

         

        foreach($request->urunyeni as $key=>$yeni_urun){



            /*$urun_satisi = new UrunSatislari();

            $urun_satisi->urun_id = $yeni_urun;

            $urun_satisi->randevu_id = $request->randevu_id;

            $urun_satisi->personel_id = $request->urun_satici[$key];

            $urun_satisi->tarih = $request->urun_satis_tarihi;

            $urun_satisi->fiyat = $request->urun_fiyati[$key];

            $urun_satisi->adet = $request->urun_adedi[$key];

            $urun_satisi->notlar = $request->satis_notlari;

            $urun_satisi->user_id =$request->musteri_id;

            $urun_satisi->save();*/

            

            $adisyon_urun = new AdisyonUrunler();

            $adisyon_urun->islem_tarihi = $request->urun_satis_tarihi;

            $adisyon_urun->adisyon_id= $adisyon_id;

            $adisyon_urun->urun_id = $yeni_urun;

            $adisyon_urun->personel_id = $request->urun_satici;

            $adisyon_urun->adet = $request->urun_adedi[$key];

            $adisyon_urun->fiyat = $request->urun_fiyati[$key];

            $adisyon_urun->save();

            $urun = Urunler::where('id',$yeni_urun)->first();

            $urun->stok_adedi -= $request->urun_adedi[$key];

            $urun->save();

           







        }

        $indirim = (Adisyonlar::where('id',$adisyon_id)->value('indirim_tutari')) ? Adisyonlar::where('id',$adisyon_id)->value('indirim_tutari') : 0;

 

        $adisyon_toplam_tutar = AdisyonHizmetler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonUrunler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonPaketler::where('adisyon_id',$adisyon_id)->sum('fiyat') - $indirim;

            $tahsil_edilen_tutar = Tahsilatlar::where('adisyon_id',$adisyon_id)->sum('tutar');

            $kalan_tutar = $adisyon_toplam_tutar - $tahsil_edilen_tutar;

        $adisyon_html =  self::adsiyon_urun_liste_getir($request,$adisyon_id);

         

            return self::musteri_tahsilatlari($request,$request->musteri_id,$request->adisyon_id);

             

        

      



        

    }

    public function adsiyon_urun_liste_getir(Request $request,$adisyon_id)

    {

        $satislar = AdisyonUrunler::where('adisyon_id',$adisyon_id)->get();

        $html = "";

        $tahsilat_urun_eklenecek = '';

        foreach($satislar as $urun)

        {

            $tahsilat_urun_eklenecek .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">



                              <div class="col-md-4">

                                 <input type="hidden" class="adisyon_kalemler" name="adisyon_odeme_urun[]" value="'.($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar')).'" data-value="'.$urun->id.'">

                                 '.$urun->urun->urun_adi.'

                              </div>

                              <div class="col-md-3">

                                  '.$urun->personel->personel_adi.'



                              </div>

                              <div class="col-md-2">

                                    '.$urun->adet.' adet



                              </div>

                              

                              <div class="col-md-2" style="text-align:right">

                                     

                                    <span name="adisyon_urun_tahsilat_tutari" data-value="'.$urun->id.'">'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar'),2,',','.').'</span> ₺

                                    <input type="hidden" name="adisyon_urun_tahsilat_tutari[]" data-value="'.$urun->id.'"   data-inputmask =" \'alias\' : \'currency\'">

                                    <input type="hidden" name="adisyon_urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.($urun->fiyat -TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar')).'"  data-inputmask =" \'alias\' : \'currency\'">

                                 </div>

                                 <div class="col-md-1">

                                     <button type="button"  style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="urun_formdan_sil" data-value="'.$urun->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                                 </div>

                           </div>';

            $html .= '<tr>

                        <td>'.$urun->urun->urun_adi.'</td>

                        <td>'.$urun->adet.'</td>

                        <td>'.$urun->personel->personel_adi.'</td>

                        <td>

                           <input type="hidden" name="urun_fiyati_adisyon[]" value="'.$urun->fiyat.'"> 

                           '.number_format($urun->fiyat,2,',','.').'



                        </td>

                        

                        <td style="width:30px">

                          

                           <button type="button" name="urun_formdan_sil" data-value="'.$urun->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                           

                          

                        </td>

                     </tr>';

        }

        if($satislar->count()==0)

        {

            $html .= '     <td colspan="4" class="text-center">

                           Kayıt Bulunamadı

                        </td>

                     </tr>';

        }

        return array(

            'html'=>$html,

            'tahsilat_urun_eklenecek'=>$tahsilat_urun_eklenecek,

        );

    }

    public function urunadisyondansil(Request $request)

    {



        $adisyonurun = AdisyonUrunler::where('id',$request->adisyonurunid)->first();

        $tahsilatlar = Tahsilatlar::where('adisyon_id',$adisyonurun->adisyon_id)->get();

        $musteriid = Adisyonlar::where('id',$adisyonurun->adisyon_id)->value('user_id');

        $uruneaittahsilatvar = false;

        

        foreach($tahsilatlar as $tahsilat)

        {

            if(TahsilatUrunler::where('tahsilat_id',$tahsilat->id)->where('adisyon_urun_id',$adisyonurun->id)->count()!=0)

                $uruneaittahsilatvar = true;

        }            

        if($uruneaittahsilatvar)

        {

            return array(

                'silinemez'=>$adisyonurun->urun->urun . ' için tahsilat kaydı bulunmakta olduğundan adisyondan silme işlemi gerçekleştirilemiyor. Önce tahsilat kaydının kaldırılması gereklidir.'

            );

            exit;

        }

        else

        {

            $urunid = $adisyonurun->urun_id;

            $adet = $adisyonurun->adet;

            $adisyon_id = $adisyonurun->adisyon_id;

            $adisyonurun->delete();

            $urun = Urunler::where('id',$urunid)->first();

            $urun->stok_adedi += $adet;

            $urun->save();

            $indirim = (Adisyonlar::where('id',$adisyon_id)->value('indirim_tutari')) ? Adisyonlar::where('id',$adisyon->id)->value('indirim_tutari') : 0;

            $adisyon_toplam_tutar = AdisyonHizmetler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonUrunler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonPaketler::where('adisyon_id',$adisyon_id)->sum('fiyat')-$indirim;

                $tahsil_edilen_tutar = Tahsilatlar::where('adisyon_id',$adisyon_id)->sum('tutar');

                $kalan_tutar = $adisyon_toplam_tutar - $tahsil_edilen_tutar;

            $adisyon_urun = self::adsiyon_urun_liste_getir($request,$adisyon_id);





            return array(

                    'status' => 'Ürün adisyondan başarıyla kaldırıldı',

                    'html' => $adisyon_urun['html'],

                    'tahsil_edilen'=>number_format($tahsil_edilen_tutar,2,',','.'),

                    'kalan_tutar' =>number_format($kalan_tutar,2,',','.'),

                    'tahsilat_urun_eklenecek' => $adisyon_urun['tahsilat_urun_eklenecek'],

                    'tum_tahsilatlar' => self::musteri_tahsilatlari($request,$musteriid,"")

        

        

            );  

            exit;

        }

        

    }

    public function urunfiyathesapla(Request $request)

    {

        return $request->adet * Urunler::where('id',$request->urun_id)->value('fiyat');

    }

    public function paketadisyondansil(Request $request)

    {

        

        $adisyonpaket = AdisyonPaketler::where('id',$request->adisyonpaketid)->first();



        $adisyon_id = $adisyonpaket->adisyon_id;

        $musteriid = Adisyonlar::where('id',$adisyon_id)->value('user_id');

        if(empty($musteriid))

            $musteriid = $request->musteri_id;

        $paketeaittahsilatvar = false;

        $tahsilatlar = Tahsilatlar::where('adisyon_id',$adisyonpaket->adisyon_id)->get();

        foreach($tahsilatlar as $tahsilat)

        {

            if(TahsilatPaketler::where('tahsilat_id',$tahsilat->id)->where('adisyon_paket_id',$adisyonpaket->id)->count()!=0)

                $paketeaittahsilatvar = true;

        }            

        if($paketeaittahsilatvar)

        {

            return array(

                'silinemez'=>$adisyonpaket->paket->paket_adi . ' için tahsilat kaydı bulunmakta olduğundan adisyondan silme işlemi gerçekleştirilemiyor. Önce tahsilat kaydının kaldırılması gereklidir.'

            );

            exit;

        }

        else

        {



            AdisyonPaketSeanslar::where('adisyon_paket_id',$request->adisyonpaketid)->delete();

            $adisyonpaket->delete();

            if(AdisyonHizmetler::where('adisyon_id',$adisyon_id)->count()+AdisyonUrunler::where('adisyon_id',$adisyon_id)->count()+AdisyonPaketler::where('adisyon_id',$adisyon_id)->count()==0)

            {

                Adisyonlar::where('id',$adisyon_id)->delete();

                if($request->tahsilat_ekrani==0){

                    return array(

                       'status' => 'Paket adisyondan başarıyla kaldırıldı. Yönlendiriliyorsunuz',

                       'url' => '/isletmeadmin/adisyonlar',

                       'tum_tahsilatlar'=>self::musteri_tahsilatlari($request,$musteriid,""),

                    );

                    exit;

                }

                else

                {

                    return array(

                       'status' => 'Paket tahsilatı başarıyla kaldırıldı.',

                       'tum_tahsilatlar'=>self::musteri_tahsilatlari($request,$musteriid,"")

                    );

                    exit;

                }

            }

            else

            {

                $indirim = (Adisyonlar::where('id',$adisyon_id)->value('indirim_tutari')) ? Adisyonlar::where('id',$adisyon->id)->value('indirim_tutari') : 0;





                $adisyon_toplam_tutar = AdisyonHizmetler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonUrunler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonPaketler::where('adisyon_id',$adisyon_id)->sum('fiyat')-$indirim;

                    $tahsil_edilen_tutar = Tahsilatlar::where('adisyon_id',$adisyon_id)->sum('tutar');

                    $kalan_tutar = $adisyon_toplam_tutar - $tahsil_edilen_tutar;

                $adisyon_paket_liste = self::adisyon_paket_satis_getir($adisyon_id,false,'');



                return array(

                        'status' => 'Ürün adisyondan başarıyla kaldırıldı',

                        'html' => $adisyon_paket_liste['html'],

                         'tahsil_edilen'=>number_format($tahsil_edilen_tutar,2,',','.'),

                        'kalan_tutar' =>number_format($kalan_tutar,2,',','.'),

                        'tahsilat_paket_eklenecek'=>$adisyon_paket_liste['tahsilat_paket_eklenecek'],



                        'tum_tahsilatlar'=>self::musteri_tahsilatlari($request,$musteriid,"")

            

                );  

                exit;

            }

           

        }

        

    }

    public function musteriarama(Request $request){

        $telefon = str_replace('+','',$request->telefon);

        $telefon = str_replace(substr($telefon, 0, 2),'',$telefon);

        



        $musteri = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->select('users.id as id','users.name as ad_soyad')->where('musteri_portfoy.salon_id',$request->sube)->where('users.cep_telefon','like','%'.$telefon.'%')->first(); 

        if($musteri){

            return $musteri->ad_soyad;

            exit;

        }

        else

        {

            return $request->telefon;

            exit;

        }

        

    }

    public function musteriekleguncelle(Request $request){

            $returnvar = "";

       

            $musteri = "";

            $yeniekleme = false;

            $baskaekleme = false;

            $olusturulansifre = '';

            $portfoy = '';

            if($request->musteri_id != ""){

                $musteri = User::where('id',$request->musteri_id)->first();





            }



            else{

                $musteri_var = User::where('cep_telefon',$request->telefon)->count();

        

                if($musteri_var > 0)

                {

                    $mevcut = User::where('cep_telefon',$request->telefon)->first();

                    

                    $portfoyvar = MusteriPortfoy::where('user_id',$mevcut->id)->where('salon_id',$request->sube)->where('aktif',true)->count();

                    



                    if($portfoyvar==1)

                        $returnvar = array(

                            'detailtext' => '',

                            'title' => 'Uyarı',

                            'mesaj' => 'Sistemde girdiğiniz telefon numarasına ait '.$mevcut->name.' isimli kayıt portföyünüzde mevcuttur',

                            'musteri_id' => 0,

                            'yeniekleme' => $yeniekleme,

                            'status' => 'warning',

                            'showCloseButton'=> false,

                            'showCancelButton'=> false,

                            'showConfirmButton'=>false,



                        );

                    else{

                        $yeniekleme = true; 

                        $baskaekleme = true;

                        $portfoy = '';

                        if(MusteriPortfoy::where('user_id',$mevcut->id)->where('salon_id',$request->sube)->where('aktif','!=',true)->count()==1)

                            $portfoy = MusteriPortfoy::where('user_id',$mevcut->id)->where('salon_id',$request->sube)->where('aktif','!=',true)->first();

                        else

                            $portfoy = new MusteriPortfoy();

                        $portfoy->user_id = $mevcut->id;

                        $portfoy->salon_id = $request->sube;

                        $portfoy->musteri_tipi = $request->musteri_referans;

                        $portfoy->ozel_notlar = $request->ozel_notlar;

                         $portfoy->aktif = true;

                        $portfoy->save();

                        $returnvar = array(

                            'title' => "Başarılı",

                            'detailtext' => '',

                            'mesaj' => "Müşteri bilgileri başarıyla kaydedildi.",

                            'musteri_id' => $mevcut->id,



                            'yeniekleme' => $yeniekleme,

                            'status' => 'success',

                            'musteribilgi' => DB::table('users')->select('id as id','name as text')->where('id',$mevcut->id)->get(),

                            'showCloseButton'=> false,

                            'showCancelButton'=> false,

                            'showConfirmButton'=>false,

                            'musteriler' => self::musteri_liste_getir($request,3),

                            'aktif_musteriler' => self::musteri_liste_getir($request,1),

                            'sadik_musteriler' => self::musteri_liste_getir($request,2),

                            'pasif_musteriler' => self::musteri_liste_getir($request,0),



                        );

                    }

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

            if($request->dogum_tarihi_gun != '' && $request->dogum_tarihi_ay != '' && $request->dogum_tarihi_yil != '' )

                $musteri->dogum_tarihi = date('Y-m-d',strtotime($request->dogum_tarihi_yil.'-'.$request->dogum_tarihi_ay.'-'.$request->dogum_tarihi_gun));

            if($request->cinsiyet == 0 || $request->cinsiyet == 1)

                $musteri->cinsiyet = $request->cinsiyet;

            $musteri->save();

            if($request->musteri_id == "") 

                 $portfoy = new MusteriPortfoy(); 

            else

                $portfoy = MusteriPortfoy::where('user_id',$musteri->id)->where('salon_id',$request->sube)->first();

            $portfoy->user_id= $musteri->id;

            $portfoy->salon_id =$request->sube;

            $portfoy->musteri_tipi = $request->musteri_referans;

            $portfoy->ozel_notlar = $request->ozel_notlar;

            $portfoy->aktif = true;

            $portfoy->save();

            $returntext =  "<p><b>ID : </b>".$musteri->id."</p>";

            $returntext .= "<p><b>Ad Soyad : </b>".$musteri->name."</p>";

            $returntext .= "<p><b>Telefon : </b>".$musteri->cep_telefon."</p>";

            $returntext .= "<p><b>E-posta : </b>".$musteri->email."</p>";

            $returntext .= "<p><b>Referans : </b>";

            if($portfoy->musteri_tipi == 1)

                $returntext .= "İnternet";

            elseif($portfoy->musteri_tipi == 2)

                $returntext .= "Reklam";

            elseif($portfoy->musteri_tipi == 3)                            

                $returntext .= "Instagram";

            elseif($portfoy->musteri_tipi == 4)

                $returntext .= "Facebook";

            elseif($portfoy->musteri_tipi == 5)

                $returntext .= "Tanıdık";

            else

                $returntext .= "Yok";                                      

                                         



            $returntext .= "</p>";

            $returntext .= "<p><b>Doğum Tarihi : </b>".date('d.m.Y', strtotime($musteri->dogum_tarihi))."</p>";

            $returntext .= "<p><b>Cinsiyet : </b>";

            if ($musteri->cinsiyet === 0)

                    $returntext .="Kadın";

            elseif ($musteri->cinsiyet === 1) 

                    $returntext .= "Erkek";

            else

                    $returntext .= "Belirtilmemiş";

            $returntext .= "<p><b>Notlar : </b>".$portfoy->ozel_notlar;

            if(SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',4)->value('musteri')){

                if($yeniekleme || $baskaekleme){

                    $mesaj = Salonlar::where('id',$request->sube)->value('salon_adi')." tarafından müşteri kaydınız oluşturulmuştur.";

                    if(Salonlar::where('id',$request->sube)->value('uygulamalar_kisa_link'))

                        $mesaj .=' Uygulamamızı indirmek için linke tıklayın. '.Salonlar::where('id',$request->sube)->value('uygulamalar_kisa_link');

                    self::sms_gonder($request,array(array("to"=>$musteri->cep_telefon,"message"=>$mesaj)),false,1,false);

                }

                 

            }

            $returntext .= "</p>";

            $returnvar = array(

                'title' => "Başarılı",

                'detailtext' => $returntext,

                'mesaj' => "Müşteri bilgileri başarıyla kaydedildi.",

                'musteri_id' => $musteri->id,

                'yeniekleme' => $yeniekleme,

                'status' => 'success',

                'musteribilgi' => DB::table('users')->select('id as id','name as text')->where('id',$musteri->id)->get(),

                'musteriler' => self::musteri_liste_getir($request,3),

                'aktif_musteriler' => self::musteri_liste_getir($request,1),

                'sadik_musteriler' => self::musteri_liste_getir($request,2),

                'pasif_musteriler' => self::musteri_liste_getir($request,0),



            ); 



            return $returnvar; 

    }

    public function calismasaatleriduzenle(Request $request){

        $saloncalismasaatlerieski = SalonCalismaSaatleri::where('salon_id',self::mevcutsube($request))->delete();

        $salonmolasaaterieski = SalonMolaSaatleri::where('salon_id',self::mevcutsube($request))->delete();

       

        for($i=1;$i<=7;$i++){

            

            $saloncalismasaatleri = new SalonCalismaSaatleri();

            $salonmolasaatleri = new SalonMolaSaatleri();

            $saloncalismasaatleri->haftanin_gunu = $i;

            $salonmolasaatleri->haftanin_gunu = $i;

            $saloncalismasaatleri->salon_id = self::mevcutsube($request);

            $salonmolasaatleri->salon_id = self::mevcutsube($request);

            

            if(isset($_POST['calisiyor'.$i])){



                $saloncalismasaatleri->calisiyor = 1;

               

               

            }

            else{

                $saloncalismasaatleri->calisiyor = 0;

            }

            if(isset($_POST['molavar'.$i])){



                $salonmolasaatleri->mola_var = 1;

               

               

            }

            else{

                $salonmolasaatleri->mola_var = 0;

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

        $isletme = Salonlar::where('id',$request->sube)->first();

        $isletme->salon_adi = $request->isletme_adi;

        $isletme->adres =$request->isletme_adres;

        $isletme->salon_turu_id = $request->isletme_turu;

         $isletme->telefon_1=$request->isletme_telefon;

        $isletme->vergi_adi = $request->vergi_adi;

        $isletme->vergi_no = $request->vergi_tc_no;

        $isletme->vergi_dairesi = $request->vergi_dairesi;

        $isletme->vergi_adresi = $request->vergi_adresi;

        $isletme->kdv_orani = $request->kdv_orani;

        $isletme->meta_description = $request->seo_description;

        $isletme->facebook_sayfa = $request->facebook_url;

        $isletme->instagram_sayfa = $request->instagram_url;

        $isletme->whatsapp = $request->whatsapp;

        AramaTerimleri::where('salon_id',$isletme->id)->delete();

        foreach($request->anahtar_kelimeler as $anahtar_kelime)

        {

            if($anahtar_kelime != ''){

                $ak = new AramaTerimleri();

                $ak->salon_id = $isletme->id;

                $ak->arama_terimi =$anahtar_kelime;

                $ak->save();

            }

           



        }

        $isletme->save();

        return "İşletme bilgileri başarıyla kaydedildi";

    }

    public function adisyon_hizmet_sil(Request $request)

    {       

        $hizmet = AdisyonHizmetler::where('id',$request->hizmet_id)->first();



        $adisyon_id = $hizmet->adisyon_id;

        $musteriid = Adisyonlar::where('id',$adisyon_id)->value('user_id');

        $tahsilatlar = Tahsilatlar::where('adisyon_id',$adisyon_id)->get();

        $hizmeteaittahsilatvar = false;

        $tahsilatekrani  = false;



        foreach($tahsilatlar as $tahsilat)

        { 

            if(TahsilatHizmetler::where('tahsilat_id',$tahsilat->id)->where('adisyon_hizmet_id',$hizmet->id)->count() != 0)

                $hizmeteaittahsilatvar = true; 

        }

        if($hizmeteaittahsilatvar){

            return array(

                'silinemez'=>$hizmet->hizmet->hizmet_adi . ' için tahsilat kaydı bulunmakta olduğundan adisyondan silme işlemi gerçekleştirilemiyor. Önce tahsilat kaydının kaldırılması gereklidir.'

            );

            exit;

        }

        elseif($tahsilatekrani)

        {

            return self::musteri_tahsilatlari($request,$musteriid,"");

            exit;

        }

        else

        {

            $hizmet->delete();

            /*if(AdisyonHizmetler::where('adisyon_id',$adisyon_id)->count()+AdisyonUrunler::where('adisyon_id',$adisyon_id)->count()+AdisyonPaketler::where('adisyon_id',$adisyon_id)->count()==0)

                Adisyonlar::where('id',$adisyon_id)->delete()*/

            return self::musteri_tahsilatlari($request,$musteriid,"");//self::adisyon_detay($request,Adisyonlar::where('id',$adisyon_id)->first(),false,$musteriid);

            exit;

        }

        

    }

    public function adisyonhizmetekle(Request $request)

    {       

            $adisyon_id = '';

            if($request->adisyon_id != '')

            {

                $adisyon_id = $request->adisyon_id;

            }

            else

                $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id,$request->sube,'Hizmet Satışı',date('Y-m-d'));

            foreach($request->adisyonhizmetleriyeni as $key=>$adisyon_hizmet){

                $adisyon_hizmet = new AdisyonHizmetler();

                $adisyon_hizmet->adisyon_id = $adisyon_id;

                $adisyon_hizmet->hizmet_id = $request->adisyonhizmetleriyeni[$key];

                $adisyon_hizmet->islem_tarihi = date('Y-m-d',strtotime($request->islemtarihiyeni[$key]));

                $adisyon_hizmet->islem_saati = date('H:i:s',strtotime($request->islemsaatiyeni[$key]));

                $adisyon_hizmet->sure = $request->adisyonhizmetsuresi[$key];

                $adisyon_hizmet->fiyat = $request->adisyonhizmetfiyati[$key];

                $adisyon_hizmet->personel_id = $request->adisyonhizmetpersonelleriyeni[$key];

                $adisyon_hizmet->geldi = true;

                $adisyon_hizmet->save();

             

            }

            if(isset($request->tahsilatekrani))

            {

                return self::musteri_tahsilatlari($request,$request->musteri_id,"");

                exit;

            }

            else

            {

                  return self::adisyon_detay($request,$request,Adisyonlar::where('id',$adisyon_hizmet->adisyon_id)->first(),false,'');

                  exit;

            }

          

            

    }

    public function adisyon_detay(Request $request,$adisyon,$yeniekleme,$musteriid)

    {

        

        $personeller = Personeller::where('salon_id',$adisyon->salon_id)->get();

        $hizmetler = SalonHizmetler::where('salon_id',$adisyon->salon_id)->get();

        $html = '';

        $hizmet_tahsilata_eklenecek = '';



        foreach($adisyon->hizmetler as $hizmet)

        {



             $hizmet_tahsilata_eklenecek .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0"><div class="col-md-4">

                                 <input type="hidden" class="adisyon_kalemler" name="adisyon_odeme_hizmet[]" value="'. ($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')).'" data-value="'.$hizmet->id.'">



                                  '.$hizmet->hizmet->hizmet_adi.' 

                              </div>

                              <div class="col-md-3">

                                 '.$hizmet->personel->personel_adi.'



                              </div>

                              <div class="col-md-2">

                                   

                                  1 adet

                              </div>

                             

                              <div class="col-md-2" style="text-align:right">



                                    <span name="adisyon_hizmet_tahsilat_tutari" data-value="'.$hizmet->id.'">'.number_format($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar'),2,',','.').'</span> ₺

                                    <input type="hidden" name="adisyon_hizmet_tahsilat_tutari[]" data-value="'.$hizmet->id.'" data-inputmask =" \'alias\' : \'currency\'">

                                    <input type="hidden" name="adisyon_hizmet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')).'"  data-inputmask =" \'alias\' : \'currency\'">

                                 </div>

                                 <div class="col-md-1">

                                    <button  type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="hizmet_formdan_sil_adisyon_mevcut"  data-value="'.$hizmet->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                              </div></div>';

 





               

                $html .= '<input type="hidden" name="adisyonhizmetidleri[]" value="'.$hizmet->hizmet_id.'">

                  <div class="row" data-value="'.$hizmet->id.'" style="background-color:#e2e2e2;margin-bottom: 5px;">

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>İşlem Tarihi & Saati</label>

                           <input type="text" required class="form-control datetimepicker" data-value="'.$hizmet->id.'" name="hizmet_islem_tarihi" value="'.$hizmet->islem_tarihi.'">

                        </div>

                     </div>

                    <div class="col-md-2">

                        <div class="form-group">

                           <label>İşlem Saati</label>

                           <input type="time" required class="form-control" data-value="'.$hizmet->id.'" name="hizmet_saati_tarihi" value="'.$hizmet->islem_saati.'">

                        </div>

                     </div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>Personel</label>

                           <select name="islem_personelleri" data-value="'.$hizmet->id.'" class="form-control custom-select2" style="width: 100%;">';

                foreach($personeller as $personel){

                    if($hizmet->personel_id == $personel->id)

                        $html .=  '<option selected value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

                    else

                        $html .= '<option value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

                }

                $html .='</select> </div>

                     </div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>Hizmet</label>

                           <select name="islem_hizmetleri" data-value="'.$hizmet->id.'" class="form-control custom-select2" style="width: 100%;">';

                foreach($hizmetler as $hizmetliste)

                {

                    if($hizmet->hizmet_id == $hizmetliste->hizmet_id)

                        $html .= ' <option selected value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

                    else

                        $html .= ' <option value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

                }

                $html .= '</select></div></div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>Durum</label>

                           <select name="adisyon_hizmet_durum" data-value="'.$hizmet->id.'" id="adisyon_hizmet_durum" class="form-control">';

                if($hizmet->geldi === 1)

                {

                    $html .=  '<option value="1" selected>Geldi</option>

                           <option value="0">Gelmedi</option>

                           <option>Bekliyor</option>';

                }

                if($hizmet->geldi === 0)

                {

                    $html .=  '<option value="1" >Geldi</option>

                           <option selected value="0">Gelmedi</option>

                           <option>Bekliyor</option>';

                } 

                if($hizmet->geldi === null)

                {

                    $html .=  '<option value="1" >Geldi</option>

                           <option value="0">Gelmedi</option>

                           <option selected>Bekliyor</option>';

                } 

                            

                           $html .= '</select>

                        </div>

                     </div>

                     <div class="col-md-1">

                        <div class="form-group">

                           <label>Fiyat (₺)</label>

                           <input type="tel" class="form-control" name="hizmet_fiyati_adisyon" data-value="'.$hizmet->id.'" value="'.$hizmet->fiyat.'">

                        </div>

                     </div>

                     <div class="col-md-1">

                        <div class="form-group">

                           <label style="visibility: hidden;width: 100%;">Kaldır</label>

                           <button type="button" name="hizmet_formdan_sil_adisyon_mevcut"  data-value="'.$hizmet->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                        </div>

                     </div>

                  </div>';



        }

        $indirim = (Adisyonlar::where('id',$adisyon->id)->value('indirim_tutari')) ? Adisyonlar::where('id',$adisyon->id)->value('indirim_tutari') : 0;

        $adisyon_toplam_tutar = AdisyonHizmetler::where('adisyon_id',$adisyon->id)->sum('fiyat')+AdisyonUrunler::where('adisyon_id',$adisyon->id)->sum('fiyat')+AdisyonPaketler::where('adisyon_id',$adisyon->id)->sum('fiyat')-$indirim;

        $tahsil_edilen_tutar = Tahsilatlar::where('adisyon_id',$adisyon->id)->sum('tutar');

        $kalan_tutar = $adisyon_toplam_tutar - $tahsil_edilen_tutar;

        return array(

                'html'=>$html,

                 'tahsil_edilen'=>number_format($tahsil_edilen_tutar,2,',','.'),

                'kalan_tutar' =>number_format($kalan_tutar,2,',','.'),

                'hizmet_tahsilata_eklenecek'=>$hizmet_tahsilata_eklenecek,

                'tum_tahsilatlar' => self::musteri_tahsilatlari($request,$musteriid,"")

        );

    }

    public function yaklasan_dogumgunleri()

    {

        

         $yaklasan_dogumgunleri = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->select('users.name as ad_soyad','users.cep_telefon as telefon', 'users.dogum_tarihi as dogum_tarihi')->whereDay('dogum_tarihi', '>=',date('d'))->whereMonth('dogum_tarihi',date('m'))->whereDay('dogum_tarihi','<=',date('d',strtotime('+5 days',strtotime(date('Y-m-d')))))->where('musteri_portfoy.salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

         echo $yaklasan_dogumgunleri;

    }

    public function tahsilatekle(Request $request){

        

        $tahsilat = new Tahsilatlar();

      

        $tahsilat->adisyon_id = $request->adisyon_id;

        $tahsilat->tutar = str_replace('.','',$request->indirimli_toplam_tahsilat_tutari);

        $tahsilat->user_id = $request->ad_soyad;

        $tahsilat->odeme_tarihi = $request->tahsilat_tarihi;    

        $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        $tahsilat->salon_id = $request->sube;

        $tahsilat->yapilan_odeme = str_replace('.','',$request->indirimli_toplam_tahsilat_tutari);

        $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;

        $tahsilat->notlar = $request->tahsilat_notlari;

        $tahsilat->save();



        if(isset($request->adisyon_hizmet_id) && isset($request->adisyon_hizmet_senet_id) && isset($request->adisyon_hizmet_taksitli_tahsilat_id))

        {

            foreach($request->adisyon_hizmet_id as $key=>$hizmet_id)

            {        

                     

                if($request->adisyon_hizmet_senet_id[$key] == '' && $request->adisyon_hizmet_taksitli_tahsilat_id[$key] == '')

                {           

                    $odeme = new TahsilatHizmetler();

                    $odeme->adisyon_hizmet_id = $hizmet_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $hizmet_tahsilat_tutar = 0;

                    if(isset($request->himzet_tahsilat_tutari_girilen))

                        $hizmet_tahsilat_tutar = $request->himzet_tahsilat_tutari_girilen[$key];

                    else

                        $hizmet_tahsilat_tutar = $request->adisyon_hizmet_tahsilat_tutari[$key];

                    $odeme->tutar = (str_replace(['.',','],['','.'],$hizmet_tahsilat_tutar)/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);



                    $odeme->save();

                }

                



            }

         

        }

        if(isset($request->adisyon_urun_id) && isset($request->adisyon_urun_senet_id) && isset($request->adisyon_urun_taksitli_tahsilat_id))

        {

            foreach($request->adisyon_urun_id as $key2=>$urun_id)

            {

                if($request->adisyon_urun_senet_id[$key2] == '' && $request->adisyon_urun_taksitli_tahsilat_id[$key2] == '')

                {

                    $odeme = new TahsilatUrunler();

                    $odeme->adisyon_urun_id = $urun_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $urun_tahsilat_tutar = 0;

                    if(isset($request->urun_tahsilat_tutari_girilen))

                        $urun_tahsilat_tutar = $request->urun_tahsilat_tutari_girilen[$key2];

                     else

                        $urun_tahsilat_tutar = $request->adisyon_urun_tahsilat_tutari[$key2];

                    $odeme->tutar = (str_replace(['.',','],['','.'],$urun_tahsilat_tutar)/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);



                    $odeme->save();

                }

                 



            }

        }

        if(isset($request->adisyon_paket_id)&& isset($request->adisyon_paket_senet_id) && isset($request->adisyon_paket_taksitli_tahsilat_id))

        {

            foreach($request->adisyon_paket_id as $key3=>$paket_id)

            {

                 

                if($request->adisyon_paket_senet_id[$key3] == '' && $request->adisyon_paket_taksitli_tahsilat_id[$key3] == ''){

                    $odeme = new TahsilatPaketler();

                    $odeme->adisyon_paket_id = $paket_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $paket_tahsilat_tutar = 0;

                    if(isset($request->paket_tahsilat_tutari_girilen))

                         $paket_tahsilat_tutar= $request->paket_tahsilat_tutari_girilen[$key3];

                     else

                        $paket_tahsilat_tutar =$request->adisyon_paket_tahsilat_tutari[$key3];

                    $odeme->tutar = (str_replace(['.',','],['','.'],$paket_tahsilat_tutar)/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);



                    $odeme->save();

                }

                

            }

        }

        if(isset($request->taksit_vade_id))

        {

            foreach($request->taksit_vade_id as $taksitvadesi)

            {

                $taksit_vade = TaksitVadeleri::where('id', $taksitvadesi)->first();

                $taksit_vade->odendi = true;

                $taksit_vade->odeme_yontemi_id = $request->odeme_yontemi;

                $taksit_vade->save();

                $taksit_toplami = TaksitVadeleri::where('taksitli_tahsilat_id',$taksit_vade->taksitli_tahsilat_id)->sum('tutar');

                foreach(AdisyonHizmetler::where('taksitli_tahsilat_id',$taksit_vade->taksitli_tahsilat_id)->get() as $key=>$hizmet)

                {

                    $oncekitahsilatlar = TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar');

                    $odeme = new TahsilatHizmetler();

                    $odeme->adisyon_hizmet_id = $hizmet->id;

                    $odeme->tahsilat_id = $tahsilat->id; 

                    $hizmet_tahsilat_tutar = $hizmet->fiyat; 



                    $odeme->tutar = ((str_replace(['.',','],['','.'],$hizmet_tahsilat_tutar)-$hizmet->indirim_tutari-$oncekitahsilatlar)/str_replace(['.',','],['','.'],$taksit_toplami))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 



                    $odeme->save();

                }

                foreach(AdisyonUrunler::where('taksitli_tahsilat_id',$taksit_vade->taksitli_tahsilat_id)->get() as $key2=>$urun)

                {

                    $oncekitahsilatlar = TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar');

                    

                    $odeme = new TahsilatUrunler();

                    $odeme->adisyon_urun_id = $urun->id;

                    $odeme->tahsilat_id = $tahsilat->id; 

                    $urun_tahsilat_tutar = $urun->fiyat; 



                    $odeme->tutar = ((str_replace(['.',','],['','.'],$urun_tahsilat_tutar)-$urun->indirim_tutari-$oncekitahsilatlar)/str_replace(['.',','],['','.'],$taksit_toplami))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 



                    $odeme->aciklama = "((".str_replace(['.',','],['','.'],$urun_tahsilat_tutar)."-".$urun->indirim_tutari."/".str_replace(['.',','],['','.'],$taksit_toplami).")*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                    $odeme->save();

                }

                foreach(AdisyonPaketler::where('taksitli_tahsilat_id',$taksit_vade->taksitli_tahsilat_id)->get() as $key3=>$paket)

                {

                    $oncekitahsilatlar = TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar'); 

                    $odeme = new TahsilatPaketler();

                    $odeme->adisyon_paket_id = $paket->id;

                    $odeme->tahsilat_id = $tahsilat->id; 

                    $paket_tahsilat_tutar = $paket->fiyat; 



                    $odeme->tutar = ((str_replace(['.',','],['','.'],$paket_tahsilat_tutar)-$paket->indirim_tutari-$oncekitahsilatlar)/str_replace(['.',','],['','.'],$taksit_toplami))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 



                    $odeme->save();

                }



            }

            

        }

        if(isset($request->senet_vade_id))

        {

            foreach($request->senet_vade_id as $senetvadesi)

            {

                $senet_vade = SenetVadeleri::where('id', $senetvadesi)->first();

                $senet_vade->odendi = true;

                $senet_vade->odeme_yontemi_id = $request->odeme_yontemi;

                $senet_vade->save();

                $senet_toplami = SenetVadeleri::where('senet_id',$senet_vade->senet_id)->sum('tutar');

                foreach(AdisyonHizmetler::where('senet_id',$senet_vade->senet_id)->get() as $key=>$hizmet)

                {

                    $oncekitahsilatlar = TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar');

                    $odeme = new TahsilatHizmetler();

                    $odeme->adisyon_hizmet_id = $hizmet->id;

                    $odeme->tahsilat_id = $tahsilat->id; 

                    $hizmet_tahsilat_tutar = $hizmet->fiyat; 



                    $odeme->tutar = ((str_replace(['.',','],['','.'],$hizmet_tahsilat_tutar)-$hizmet->indirim_tutari-$oncekitahsilatlar)/str_replace(['.',','],['','.'],$senet_toplami))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 



                    $odeme->save();

                }

                foreach(AdisyonUrunler::where('senet_id',$senet_vade->senet_id)->get() as $key2=>$urun)

                {

                    $oncekitahsilatlar = TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar');

                    $odeme = new TahsilatUrunler();

                    $odeme->adisyon_urun_id = $urun->id;

                    $odeme->tahsilat_id = $tahsilat->id; 

                    $urun_tahsilat_tutar = $urun->fiyat; 



                    $odeme->tutar = ((str_replace(['.',','],['','.'],$urun_tahsilat_tutar)-$urun->indirim_tutari-$oncekitahsilatlar)/str_replace(['.',','],['','.'],$senet_toplami))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 



                    $odeme->save();

                }

                foreach(AdisyonPaketler::where('senet_id',$senet_vade->senet_id)->get() as $key3=>$paket)

                {

                    $oncekitahsilatlar = TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar');

                    $odeme = new TahsilatPaketler();

                    $odeme->adisyon_paket_id = $paket->id;

                    $odeme->tahsilat_id = $tahsilat->id; 

                    $paket_tahsilat_tutar = $paket->fiyat; 



                    $odeme->tutar = ((str_replace(['.',','],['','.'],$paket_tahsilat_tutar)-$paket->indirim_tutari-$oncekitahsilatlar)/str_replace(['.',','],['','.'],$senet_toplami))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 



                    $odeme->save();

                }



            }



        }

        $alacak = str_replace('.','',$request->odenecek_tutar);

         

        if($alacak != 0)

        {

            

            $alacak_kaydi = new Alacaklar();

            $alacak_kaydi->salon_id = $request->sube;

            $alacak_kaydi->adisyon_id = $request->adisyon_id;

            $alacak_kaydi->tutar = $alacak;

            $alacak_kaydi->aciklama = $request->tahsilat_notlari;

            $alacak_kaydi->planlanan_odeme_tarihi = $request->planlanan_alacak_tarihi;

            $alacak_kaydi->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

            $alacak_kaydi->salon_id = $request->sube;

            $alacak_kaydi->user_id = $request->ad_soyad;

            $alacak_kaydi->save(); 



        }

        

        return self::musteri_tahsilatlari($request,$request->ad_soyad,"");

            

    }

    public function tahsilatdetaygetir(Request $request)

    {

        return DB::table('tahsilatlar')->select('odeme_tarihi as tarih','tutar as tutar','odeme_yontemi_id as odeme_yontemi','notlar as notlar')->where('id',$request->tahsilat_id)->get();

    }

    public function adisyonlar(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $adisyonlar = '';

        $tur = '';

        $personel_id = '';

        if(isset($request->paket) && $request->paket==true)

            $tur = '2';

        if(isset($request->urun) && $request->urun==true)

            $tur = '3';

        if(!isset($request->urun) && !isset($request->paket))

            $tur = '';

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

            $personel_id = Personeller::where('salon_id',$isletme->id)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        

        $adisyonlar =  self::adisyon_yukle($request,$tur,'','1970-01-01',date('Y-m-d'),'',$personel_id);

        $adisyonlar_hizmet = self::adisyon_yukle($request,1,'','1970-01-01',date('Y-m-d'),'',$personel_id);

        $adisyonlar_urun = self::adisyon_yukle($request,3,'','1970-01-01',date('Y-m-d'),'',$personel_id);

        $adisyonlar_paket = self::adisyon_yukle($request,2,'','1970-01-01',date('Y-m-d'),'',$personel_id);

        $paketler = self::paket_liste_getir('',true,$request);

        



        return view('isletmeadmin.adisyonlar',['isletme'=>$isletme,'paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request), 'sayfa_baslik'=>'Satış Takibi','pageindex' => 11,'adisyonlar'=>$adisyonlar,'request'=>$request, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'tum_taksitler'=>self::taksitleri_getir($request,'',''),

        'portfoy_drop'=>self::musteriportfoydropliste($request),

        'taksitler_acik'=>self::taksitleri_getir($request,0,''),

        'taksitler_kapali'=>self::taksitleri_getir($request,1,''),

            'taksitler_odenmemis'=>self::taksitleri_getir($request,2,''),'adisyonlar_hizmet'=>$adisyonlar_hizmet

            ,'adisyonlar_urun'=>$adisyonlar_urun

            ,'adisyonlar_paket'=>$adisyonlar_paket ,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

   

    public function testcases(Request $request)

    { 





        $authToken = '';

        if(Salonlar::where('id',114)->value('santral_token_expires') < date('Y-m-d H:i:s'))

            $authToken = self::santral_token_al(114);

        else

            $authToken = Salonlar::where('id',114)->value('santral_token');

         

        $endpoint = "http://34.45.69.65/admin/api/api/gql";

        $qry = 'query{

          fetchAllCdrs (

             first : 99999999 

            startDate: "1970-01-01"

            endDate: "'.date('Y-m-d').'"

          )

          {

            cdrs {

              id

                uniqueid

                calldate

                timestamp

                clid

                src

                dst

                dcontext

                channel

                dstchannel

                lastapp

                lastdata

                duration

                billsec

                disposition

                accountcode

                userfield

                did

                recordingfile

                cnum

                outbound_cnum

                outbound_cnam

                dst_cnam

                linkedid

                peeraccount

                sequence

                amaflags

            }

            totalCount

            status

            message

          }

        }';

        $headers = array();

        $headers[] = 'Content-Type: application/json';

        $headers[] = 'Authorization: Bearer '.$authToken;



        $ch = curl_init();



        curl_setopt($ch, CURLOPT_URL, $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $qry]));

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);



        $result = json_decode(curl_exec($ch),true);

        $rapor = array();

        $gelen_arama = 0;

        $giden_arama = 0;

        $cevapsiz_arama = 0;

        $sesli_mesaj = 0;

        $basarisiz_arama = 0;

        return $result;

         

    }

    public function adisyon_filtreli_getir(Request $request)

    {       

        $tur = '';

        $durum = '';

        $personelid ='';

        $musteriid = '';

        $tarih1 = '1970-01-01 00:00:00';

        $tarih2 = date('Y-m-d 23:59:59');

        if($request->musteri_id!='')

            $musteriid = $request->musteri_id;

        

        if($request->tariharaligi!=''){

            $tarih = explode(' / ',$request->tariharaligi);

            $tarih1 = $tarih[0].' 00:00:00';

            $tarih2 = $tarih[1].' 23:59:59';

        }

        if(isset($request->personelid))

            $personelid = $request->personelid;

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

            $personelid = Auth::guard('isletmeyonetim')->user()->personel_id;

        return array(

            'tum_adisyonlar' => self::adisyon_yukle($request,$tur,$durum,$tarih1,$tarih2,$musteriid,$personelid),

            'hizmet_adisyonlar' => self::adisyon_yukle($request,1,$durum,$tarih1,$tarih2,$musteriid,$personelid),

            'urun_adisyonlar' => self::adisyon_yukle($request,3,$durum,$tarih1,$tarih2,$musteriid,$personelid),

            'paket_adisyonlar' => self::adisyon_yukle($request,2,$durum,$tarih1,$tarih2,$musteriid,$personelid)



        );

    }

    public function adisyon_filtreli_getir_personel(Request $request)

    {       

        $tur = $request->satisturu;

         

        $tarih1 = $request->yil.'-'.$request->ay.'-01 00:00:00';

        $tarih2 = $request->yil.'-'.$request->ay.'-31 23:59:59';



        $personelid = $request->personelid;

        return self::adisyon_yukle($request,$tur,$durum,$tarih1,$tarih2,'',$personelid);

    }

    

    public function seanstakip(Request $request){

       $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

      if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

       $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

       $adisyonlar = self::seans_getir($request,0,'1970-01-01 00:00:00',date('Y-m-d 23:59:59'),'');



       return view('isletmeadmin.seanstakip',['bildirimler'=>self::bildirimgetir($request),'adisyonlar'=>$adisyonlar,'pageindex'=>14, 'sayfa_baslik'=>'Seans Takibi','isletme'=>$isletme,'seanstakip'=>$adisyonlar, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function seans_getir(Request $request,$adisyondurumu,$tarih1,$tarih2,$musteriid)

    {

         

        return DB::table('adisyon_paketler')

            ->leftjoin('adisyonlar','adisyon_paketler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paket_seanslar','adisyon_paketler.id','=','adisyon_paket_seanslar.adisyon_paket_id')

            ->leftjoin('paketler','adisyon_paketler.paket_id','=','paketler.id')

            ->join('users','adisyonlar.user_id','=','users.id') 

            ->select(

                'adisyonlar.id as id',

                'adisyonlar.created_at as olusturma_tarih',

                        DB::raw('DATE_FORMAT(adisyon_paketler.baslangic_tarihi, "%d.%m.%Y") as baslangic_tarihi'),

                       'users.name as musteri',

                        'paketler.paket_adi',

                        DB::raw('CONCAT("

                            <button name=\"paketteki_seanslarlar\" type=\"button\" style=\"width:50px;font-size:10px\" class=\"btn btn-primary\">",

                                 (SELECT COUNT(*) FROM adisyon_paket_seanslar where adisyon_paketler.id = adisyon_paket_seanslar.adisyon_paket_id)

                            ,

                            "&nbsp;<i class=\"fa fa-plus\"></i></button>

                            <button name=\"paketteki_seanslari_beklemede_isaretle\" style=\"width:50px;font-size:10px\" type=\"button\" class=\"btn btn-warning\">",

                                 (SELECT COUNT(*) FROM adisyon_paket_seanslar

                                WHERE adisyon_paket_seanslar.geldi is null and adisyon_paketler.id = adisyon_paket_seanslar.adisyon_paket_id)

                            ,

                            "&nbsp;<i class=\"fa fa-calendar\"></i></button>

                                       <button type=\"button\" name=\"paketteki_seanslari_geldi_isaretle\" style=\"width:50px;font-size:10px\" class=\"btn btn-success\" >",

                                       (SELECT COUNT(*) FROM adisyon_paket_seanslar

                                WHERE adisyon_paket_seanslar.geldi = true and adisyon_paketler.id = adisyon_paket_seanslar.adisyon_paket_id)

                                       ,"&nbsp; <i class=\"fa fa-check\"></i></button>

                                       <button type=\"button\" name=\"paketteki_seanslari_gelmedi_isaretle\" style=\"width:50px;font-size:10px\" class=\"btn btn-danger\">",

                               (SELECT COUNT(*) FROM adisyon_paket_seanslar

                                WHERE adisyon_paket_seanslar.geldi = false and adisyon_paketler.id = adisyon_paket_seanslar.adisyon_paket_id)

                                      ,"&nbsp;<i class=\"fa fa-times\"></i></button>") as durum'),

                        

                        

                        DB::raw('CONCAT("<button type=\"button\" name=\"paket_seans_detay_getir_modal\" data-value=\"",adisyon_paketler.id,"\" class=\"btn btn-primary\"><i class=\"fa fa-eye\"></i></button>") as islemler'),

                    )->where('adisyonlar.salon_id',self::mevcutsube($request))->where('adisyonlar.tarih','>=',$tarih1)->where('adisyonlar.tarih','<=',$tarih2)

                    ->where(function($q) use($musteriid){if($musteriid!='') $q->where('adisyonlar.user_id',$musteriid);})

                        

                    ->groupBy('adisyon_paket_seanslar.adisyon_paket_id')

                    

                    ->orderBy('adisyonlar.id','desc')->get();



    }

    public function satis_filtre(Request $request)

    {

        $adisyonlar = '';

        if($request->tarih1!=''&&$request->tarih2!='')

            $adisyonlar =self::adisyon_yukle($request,'','',date($request->tarih1.' 00:00:00'),date($request->tarih2.' 23:59:59'),'',$request->personel_id);

        else

            $adisyonlar =self::adisyon_yukle($request,'','',date($request->yil.'-'.$request->ay.'-01 00:00:00'),date($request->yil.'-'.$request->ay.'-d 23:59:59'),'',$request->personel_id);

        return array(

            'adisyonlar'=>$adisyonlar,

            'hizmet_satisi'=>number_format($adisyonlar->sum('hizmet_toplam_numeric'),2,',','.'),

            'hizmet_primi'=>number_format($adisyonlar->sum('hizmet_hakedis_numeric'),2,',','.'),

            'urun_satisi'=>number_format($adisyonlar->sum('urun_toplam_numeric'),2,',','.'),

            'urun_primi'=>number_format($adisyonlar->sum('urun_hakedis_numeric'),2,',','.'),

            'paket_satisi'=>number_format($adisyonlar->sum('paket_toplam_numeric'),2,',','.'),

            'paket_primi'=>number_format($adisyonlar->sum('paket_hakedis_numeric'),2,',','.'),

            'toplam_hakedis'=>number_format($adisyonlar->sum('hakedis_numeric'),2,',','.')

        );

    }

    public function adisyon_yukle(Request $request,$adisyonturu,$adisyondurumu,$tarih1,$tarih2,$musteriid,$personelid)

    {

        $adisyonlar  = '';

        $esit_veya_buyuk = '';

        $hizmetegore = '';

        $urunegore = '';

        $paketegore = '';

        if($adisyondurumu=='0')

            $esit_veya_buyuk = '>';

        elseif($adisyondurumu=='1')

            $esit_veya_buyuk = '=';

        else

        {

           

            $esit_veya_buyuk = '>=';

        }

        if($adisyonturu == '1'){

            $hizmetegore = '>';

            $urunegore = '=';

            $paketegore = '=';

        }

        elseif($adisyonturu == '2')

        {

            $hizmetegore = '=';

            $urunegore = '=';

            $paketegore = '>';

        }

        elseif($adisyonturu == '3')

        {

            $hizmetegore = '=';

            $urunegore = '>';

            $paketegore = '=';

        } 

        else 

        {

            $hizmetegore = '>=';

            $urunegore = '>=';

            $paketegore = '>=';

        }

        //if($adisyondurumu==0)

            $adisyonlar = DB::table('adisyonlar') 

            ->leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('hizmetler','adisyon_hizmetler.hizmet_id','=','hizmetler.id')

            ->leftjoin('urunler','adisyon_urunler.urun_id','=','urunler.id')

            ->leftjoin('paketler','adisyon_paketler.paket_id','=','paketler.id')

            ->join('users','adisyonlar.user_id','=','users.id')

            ->leftjoin('tahsilatlar','tahsilatlar.user_id','=','adisyonlar.user_id')

            ->leftjoin('salon_personelleri as p1','adisyon_hizmetler.personel_id','=','p1.id')

            ->leftjoin('salon_personelleri as p2','adisyon_urunler.personel_id','=','p2.id')

            ->leftjoin('salon_personelleri as p3','adisyon_paketler.personel_id','=','p3.id')

            ->leftjoin('tahsilat_hizmetler as th1','th1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_urunler as tu1','tu1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_paketler as tp1','tp1.tahsilat_id','=','tahsilatlar.id')

            ->leftjoin('tahsilat_hizmetler as th2','th2.adisyon_hizmet_id','=','adisyon_hizmetler.id')

            ->leftjoin('tahsilat_urunler as tu2','tu2.adisyon_urun_id','=','adisyon_urunler.id')

            ->leftjoin('tahsilat_paketler as tp2','tp2.adisyon_paket_id','=','adisyon_paketler.id')

             

            ->select(

                'adisyonlar.id as id',

                'hizmetler.id as hizmet_id',

                'users.id as musteri_id',

                'p1.personel_adi as hizmet_veren',

                'p2.personel_adi as urun_satan',

                'p3.personel_adi as paket_satan',  

                 DB::raw("CONCAT('') as planlanan_alacak_tarihi"),

                /* DB::raw('CASE WHEN ((SELECT COUNT(*) FROM senet_vadeleri INNER JOIN senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi is not true and senet_vadeleri.vade_tarih < NOW()) + (SELECT COUNT(*) FROM taksit_vadeleri INNER JOIN taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id  where taksitli_tahsilatlar.user_id = users.id  and taksit_vadeleri.odendi is not true and taksit_vadeleri.vade_tarih < NOW())) > 0 THEN  

                     

                    CONCAT("<span style=\"display:none\">",DATE_FORMAT((SELECT alacaklar.planlanan_odeme_tarihi FROM alacaklar where alacaklar.user_id = users.id ORDER BY alacaklar.planlanan_odeme_tarihi ASC LIMIT 1 ),"%Y%m%d"),"</span>","<button type=\"button\" class=\"btn btn-danger\">",DATE_FORMAT((SELECT alacaklar.planlanan_odeme_tarihi FROM alacaklar where alacaklar.user_id = users.id ORDER BY alacaklar.planlanan_odeme_tarihi ASC LIMIT 1 ),"%d.%m.%Y"),"</button>") ELSE 



                    CONCAT("<span style=\"display:none\">",DATE_FORMAT((SELECT alacaklar.planlanan_odeme_tarihi FROM alacaklar where alacaklar.user_id = users.id ORDER BY alacaklar.planlanan_odeme_tarihi ASC LIMIT 1 ),"%Y%m%d"),"</span>","<button type=\"button\" class=\"btn btn-primary\">",DATE_FORMAT((SELECT alacaklar.planlanan_odeme_tarihi FROM alacaklar where alacaklar.user_id = users.id ORDER BY alacaklar.planlanan_odeme_tarihi ASC LIMIT 1 ),"%d.%m.%Y"),"</button>") END as planlanan_alacak_tarihi'),*/

                

              DB::raw('case when

    (

        (select taksit_vadeleri.vade_tarih  from taksit_vadeleri inner join taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id where taksitli_tahsilatlar.user_id = users.id and taksit_vadeleri.odendi = 0 order by taksit_vadeleri.vade_tarih asc LIMIT 1

        ) is not null AND (

            select senet_vadeleri.vade_tarih from senet_vadeleri inner join senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi = 0 order by senet_vadeleri.vade_tarih asc LIMIT 1) is not null

    ) 

    THEN 

        (select DATE_FORMAT(LEAST((select senet_vadeleri.vade_tarih from senet_vadeleri inner join senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi = 0 order by senet_vadeleri.vade_tarih asc LIMIT 1),(select taksit_vadeleri.vade_tarih  from taksit_vadeleri inner join taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id   where taksitli_tahsilatlar.user_id = users.id and taksit_vadeleri.odendi = 0 order by taksit_vadeleri.vade_tarih asc LIMIT 1)) , "%d.%m.%Y")

        ) 

        when

    (

        (select taksit_vadeleri.vade_tarih  from taksit_vadeleri inner join taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id   where taksitli_tahsilatlar.user_id = users.id and taksit_vadeleri.odendi = 0 order by taksit_vadeleri.vade_tarih asc LIMIT 1

        ) is null AND (

            select senet_vadeleri.vade_tarih from senet_vadeleri inner join senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi = 0 order by senet_vadeleri.vade_tarih asc LIMIT 1) is not null 

    ) 

    THEN (DATE_FORMAT((select senet_vadeleri.vade_tarih from senet_vadeleri inner join senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi = 0 order by senet_vadeleri.vade_tarih asc LIMIT 1),"%d.%m.%Y"))

     when

    (

        (select taksit_vadeleri.vade_tarih  from taksit_vadeleri inner join taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id   where taksitli_tahsilatlar.user_id = users.id and taksit_vadeleri.odendi = 0 order by taksit_vadeleri.vade_tarih asc LIMIT 1

        ) is not null AND (

            select senet_vadeleri.vade_tarih from senet_vadeleri inner join senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi = 0 order by senet_vadeleri.vade_tarih asc LIMIT 1) is null 

    ) 

    THEN (DATE_FORMAT((select taksit_vadeleri.vade_tarih  from taksit_vadeleri inner join taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id   where taksitli_tahsilatlar.user_id = users.id and taksit_vadeleri.odendi = 0 order by taksit_vadeleri.vade_tarih asc LIMIT 1),"%d.%m.%Y"))

 



        END as planlanan_alacak_tarihi'),

               



                DB::raw('CONCAT("<button class=\"btn btn-warning btn-block\" style=\'line-height:5px\'>",FORMAT(

                    ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar),0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id)*(COALESCE(p1.hizmet_prim_yuzde,0)/100)) + 

                    ((SELECT COALESCE(SUM(tahsilat_urunler.tutar),0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id)*(COALESCE(p2.urun_prim_yuzde,0)/100)) + 

                     ((SELECT COALESCE(SUM(tahsilat_paketler.tutar),0) FROM tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)*(COALESCE(p3.paket_prim_yuzde,0)/100)),2,"tr_TR"),"</button>")  as hakedis'),

                 

                 DB::raw('FORMAT(

                    ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar),0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id)*(COALESCE(p1.hizmet_prim_yuzde,0)/100)),2,"tr_TR") as hizmet_hakedis'),

                 

                 DB::raw('FORMAT(

                  

                    ((SELECT COALESCE(SUM(tahsilat_urunler.tutar),0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id)*(COALESCE(p2.urun_prim_yuzde,0)/100)),2,"tr_TR") as urun_hakedis'),

                 

                 DB::raw('FORMAT( 

                    ((SELECT COALESCE(SUM(tahsilat_paketler.tutar),0) FROM tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)*(COALESCE(p3.paket_prim_yuzde,0)/100)),2,"tr_TR") as paket_hakedis'), 

                  DB::raw('

                    ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar),0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id)*(COALESCE(p1.hizmet_prim_yuzde,0)/100)) as hizmet_hakedis_numeric'),

                 DB::raw('

                  

                    ((SELECT COALESCE(SUM(tahsilat_urunler.tutar),0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id)*(COALESCE(p2.urun_prim_yuzde,0)/100)) as urun_hakedis_numeric'),

                 DB::raw('

                   

                   ((SELECT COALESCE(SUM(tahsilat_paketler.tutar),0) FROM tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)*(COALESCE(p3.paket_prim_yuzde,0)/100)) as paket_hakedis_numeric'),



                DB::raw('

                   ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar),0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id)*(COALESCE(p1.hizmet_prim_yuzde,0)/100)) + 

                    ((SELECT COALESCE(SUM(tahsilat_urunler.tutar),0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id)*(COALESCE(p2.urun_prim_yuzde,0)/100)) + 

                     ((SELECT COALESCE(SUM(tahsilat_paketler.tutar),0) FROM tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)*(COALESCE(p3.paket_prim_yuzde,0)/100)) as hakedis_numeric'),

                        



                        DB::raw('CASE WHEN adisyonlar.tarih IS NOT NULL THEN CONCAT("<p style=\"display:none\">",DATE_FORMAT(adisyonlar.tarih, "%Y%m%d"), "</p>",DATE_FORMAT(adisyonlar.tarih, "%d.%m.%Y")) ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(adisyonlar.created_at, "%Y%m%d"), "</p>",DATE_FORMAT(adisyonlar.created_at, "%d.%m.%Y")) END AS acilis_tarihi'),

                        //DB::raw('CONCAT("<p style=\"display:none\">",DATE_FORMAT(adisyonlar.created_at, "%Y%m%d"), "</p>",DATE_FORMAT(adisyonlar.created_at, "%d.%m.%Y")) as acilis_tarihi'),

                       'users.name as musteri',

                        DB::raw('

                            CONCAT(CASE WHEN COUNT(adisyon_hizmetler.id) > 0 THEN "Hizmet" ELSE "" END," ",CASE WHEN COUNT(adisyon_paketler.id) > 0 THEN "Paket" ELSE "" END," ",CASE WHEN COUNT(adisyon_urunler.id) > 0 THEN "Ürün" ELSE "" END) as 

                             satis_turu'),

                        DB::raw('CONCAT(( SELECT COALESCE(GROUP_CONCAT(hizmetler.hizmet_adi),"") from hizmetler where adisyon_hizmetler.hizmet_id=hizmetler.id)," ",(SELECT COALESCE(GROUP_CONCAT(paketler.paket_adi),"") from paketler where adisyon_paketler.paket_id = paketler.id)," ",(SELECT COALESCE(GROUP_CONCAT(urunler.urun_adi),"") FROM urunler where adisyon_urunler.urun_id = urunler.id)) as icerik'),

                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) as  indirim'),

                        

                        DB::raw('CONCAT("<button class=\"btn btn-primary btn-block\" style=\"line-height:5px\">",FORMAT((SELECT COALESCE(SUM(adisyon_hizmetler.fiyat),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)),2,"tr_TR"), "</button>"  )  as toplam'),



                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.fiyat),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) - (SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) as hizmet_toplam_numeric'),

                        



                        DB::raw('(SELECT COALESCE(SUM(adisyon_urunler.fiyat),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) - (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) as urun_toplam_numeric'),

                        

                        DB::raw('(SELECT COALESCE(SUM(adisyon_paketler.fiyat),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) as paket_toplam_numeric'),

                        

                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.fiyat),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)) as toplam_numeric'),



                        DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id),2,"tr_TR"),"</button>")  as odenen'),

                        



                        DB::raw(' (SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)



                            as odenen_numeric'),



                        DB::raw('CONCAT("<button class=\"btn btn-danger btn-block\" style=\"line-height:5px\">",FORMAT((SELECT COALESCE(SUM(adisyon_hizmetler.fiyat), 0) FROM adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat), 0) FROM adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat), 0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id))  - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)) ,2,"tr_TR"),"</button>")  as kalan_tutar'), 



                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.fiyat), 0) FROM adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat), 0) FROM adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat), 0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 

                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +

                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id))  - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)) as kalan_tutar_numeric'), 



                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) as indirim_tutari_toplam_numeric'),





                        DB::raw('CONCAT("<a style=\"line-height:5px;padding:5px\" title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",adisyonlar.user_id, "?sube=", adisyonlar.salon_id  ,"\" type=\"button\"  class=\"btn btn-success\"><i class=\"fa fa-money\"></i> </a> 

                             

                            <button style=\"line-height:5px;padding:5px\"  class=\"btn btn-danger\" href=\"#\" title=\"Adisyonu Sil\"  name=\"adisyon_sil\" data-value=\"",adisyonlar.id,"\"><i class=\"fa fa-times\"></i></button>") as islemler'),

                    )->where('adisyonlar.salon_id',self::mevcutsube($request))->where('adisyonlar.tarih','>=',$tarih1)->where('adisyonlar.tarih','<=',$tarih2)

                    ->where(function($q) use($musteriid){if($musteriid!='') $q->where('adisyonlar.user_id',$musteriid);})

                    ->where(function($q) use($personelid){if($personelid!='') {

                        $q->where('adisyon_hizmetler.personel_id',$personelid);

                        $q->orWhere('adisyon_urunler.personel_id',$personelid);

                        $q->orWhere('adisyon_paketler.personel_id',$personelid);

                        }

                    })

                     

                    ->having(DB::raw('COUNT(adisyon_hizmetler.id)'),$hizmetegore,0 )

                    ->having(DB::raw('COUNT(adisyon_urunler.id)'),$urunegore,0 )

                    ->having(DB::raw('COUNT(adisyon_paketler.id)'),$paketegore,0 )

                    ->groupBy('adisyon_hizmetler.adisyon_id')

                    ->groupBy('adisyon_urunler.adisyon_id')

                    ->groupBy('adisyon_paketler.adisyon_id')

                    ->orderBy('adisyonlar.tarih','desc')->get();

        /*else

            $adisyonlar = DB::table('adisyonlar')

            ->leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

            ->leftjoin('hizmetler','adisyon_hizmetler.hizmet_id','=','hizmetler.id')

            ->leftjoin('urunler','adisyon_urunler.urun_id','=','urunler.id')

            ->leftjoin('paketler','adisyon_paketler.paket_id','=','paketler.id')

            ->join('users','adisyonlar.user_id','=','users.id')

            ->leftjoin('tahsilatlar','tahsilatlar.adisyon_id','=','adisyonlar.id')

            ->select(

                        DB::raw('DATE_FORMAT(adisyonlar.created_at, "%d.%m.%Y") as acilis_tarihi'),

                       'users.name as musteri',

                        DB::raw('

                            CONCAT(CASE WHEN COUNT(adisyon_hizmetler.id) > 0 THEN "Hizmet" ELSE "" END," ",CASE WHEN COUNT(adisyon_paketler.id) > 0 THEN "Paket" ELSE "" END," ",CASE WHEN COUNT(adisyon_urunler.id) > 0 THEN "Ürün" ELSE "" END) as 

                             satis_turu'),

                        DB::raw('CONCAT(COALESCE(GROUP_CONCAT(hizmetler.hizmet_adi),"")," ",COALESCE(GROUP_CONCAT(paketler.paket_adi),"")," ",COALESCE(GROUP_CONCAT(urunler.urun_adi),"") ) as icerik'),

                        

                        DB::raw('COALESCE(SUM(adisyon_hizmetler.fiyat), 0) + COALESCE(SUM(adisyon_urunler.fiyat), 0) + COALESCE(SUM(adisyon_paketler.fiyat), 0) as toplam'),

                        DB::raw('COALESCE(SUM(tahsilatlar.tutar), 0) as odenen'),

                        DB::raw('COALESCE(SUM(adisyon_hizmetler.fiyat), 0) + COALESCE(SUM(adisyon_urunler.fiyat), 0) + COALESCE(SUM(adisyon_paketler.fiyat), 0) - COALESCE(SUM(tahsilatlar.tutar), 0) as kalan_tutar'), 

                        DB::raw('CONCAT("<a title=\"Detaylı Bilgi\" href=\"/isletmeyonetim/adisyon/",adisyonlar.id,"\" type=\"button\" class=\"btn btn-info\"><i class=\"dw dw-eye\"></i></a>") as islemler'),

                    )->where('adisyonlar.salon_id',self::mevcutsube($request))->where('adisyonlar.created_at','>=',$tarih_1)->where('adisyonlar.created_at','<=',$tarih_2)

                    ->groupBy('adisyon_hizmetler.adisyon_id')

                    ->groupBy('adisyon_urunler.adisyon_id')

                    ->groupBy('adisyon_paketler.adisyon_id')

                    ->orderBy('adisyonlar.id','desc')->get();*/

        return $adisyonlar;

    }

    public function musteri_tahsilatlari(Request $request,$musteriid,$adisyon_id)

    {

        $tahsilatlar = Tahsilatlar::where('user_id',$musteriid)->where('salon_id',$request->sube)->get();

        $acik_adisyonlar = '';

        if($adisyon_id == '')

            $acik_adisyonlar = self::adisyon_yukle($request,'','','1970-01-01',date('Y-m-d'),$musteriid,''); 

        else

            $acik_adisyonlar = Adisyonlar::where('id',$adisyon_id)->get();

        $html = '';

        $html_tahsilat = ' <tr>

                              <td colspan="4" style="border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;">Ödeme Akışı</td>

                           </tr>';

        foreach(Adisyonlar::whereIn('id',$acik_adisyonlar->pluck('id'))->get() as $adisyon) 

        {



            foreach($adisyon->hizmetler as $key=>$hizmet)

            {

                if($adisyon_id != '' || (($hizmet->fiyat -TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')  - $hizmet->indirim_tutari > 0 || $hizmet->hediye)&&  $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null))    

                {

                    $html .= '<div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="0">



                              <div class="col-md-4 col-5 col-xs-5  col-sm-4">

                                  

                                       '.$hizmet->hizmet->hizmet_adi.'

                              </div> 

                               

                              <div class="col-md-3 col-7 col-xs-7  col-sm-3">';

                    if($hizmet->personel_id !== null)

                        $html .= $hizmet->personel->personel_adi;

                    else

                        $html .= $hizmet->cihaz->cihaz_adi;

                    $html .='</div>

                        <div class="col-md-2 col-5 col-xs-5  col-sm-2"> 

                                       

                                      1 adet

                                  </div>

                                 

                                  <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">

                                       <input type="hidden" name="adisyon_hizmet_id[]" value="'.$hizmet->id.'">

                                       <input type="hidden" name="indirim[]" data-value="'.$hizmet->id.'" value="'.$hizmet->indirim_tutari.'">

                                        <input type="hidden" name="adisyon_hizmet_senet_id[]" value="'.$hizmet->senet_id.'">

                                   <input type="hidden" name="adisyon_hizmet_taksitli_tahsilat_id[]" value="'.$hizmet->taksitli_tahsilat_id.'">

                                       ';

                    if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)

                        $html .= '<input  type="tel" class="form-control try-currency tahsilat_kalemleri" style="height:26px;width: 70%;float:left;text-align:right" name="himzet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.number_format($hizmet->fiyat -TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')  - $hizmet->indirim_tutari,2,',','.').'" >';

                    else

                        $html .= '<input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="himzet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.number_format($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari ,2,',','.').'" ><p style="position: relative; float: left; width: 70%;">'.number_format($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,2,',','.').' ₺</p>';

                    $html .= '<p style="position: relative; float: left;width: 15%;margin: 0;">';

                    if($hizmet->hediye)

                        $html .= ' <i class="fa fa-gift" style="font-size: 25px"></i>';

                    else

                        $html .= ' <i class="fa fa-gift" style="visibility:hidden"></i>';

                    $html .= ' </p>

                                        <div class="dropdown" style="width: 15%;float:left">

                                           <a

                                              class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"

                                              href="#"

                                              role="button"

                                              data-toggle="dropdown"

                                           >

                                              <i class="dw dw-more"></i>

                                           </a>

                                           <div

                                              class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"

                                           >';

                    if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)

                    {

                        if(!$hizmet->hediye)

                                $html .= '<a class="dropdown-item tahsilat_hizmet_hediye_ver" data-value="'.$hizmet->id.'" href="#"

                                                 ><i class="fa fa-gift"></i> Hediye Ver</a

                                              >';

                        else

                                $html .= '<a class="dropdown-item tahsilat_hizmet_hediye_kaldir" data-value="'.$hizmet->id.'" href="#"

                                                 ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a

                                              >';

                        $html .='<a class="dropdown-item tahsilat_hizmet_sil" data-value="'.$hizmet->id.'" href="#"

                                                 ><i class="dw dw-delete-3"></i> Sil</a

                                              >';

                    }

                    $html .= '</div>

                                        </div>

                                     </div>

                                  </div>';

                }           

                

            }

            foreach($adisyon->urunler as $key=>$urun)

            {   

                if($adisyon_id != '' || (($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari>0 || $urun->hediye)&&  $urun->senet_id === null && $urun->taksitli_tahsilat_id === null))

                {

                    $html .= '<div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="0">



                              <div class="col-md-4 col-5 col-xs-5 col-sm-4">

                                  '.$urun->urun->urun_adi.' 

                                 </div> 

                              <div class="col-md-3  col-7 col-xs-7  col-sm-3">

                                  '.$urun->personel->personel_adi.'



                              </div>

                              <div class="col-md-2 col-5 col-xs-5  col-sm-2">';

                    if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)

                        $html .= '<input type="tel" value="'.$urun->adet.'" data-value="'.$urun->id.'" class="form-control" style="height:26px;float:left;width: 60%;" name="urun_adet_girilen[]"> <span style="float:left;position:relative;">adet</span> ';

                    else

                        $html .= $urun->adet.' adet';

                    $html .= '</div> 

                                  

                                  <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">

                                     <input type="hidden" name="adisyon_urun_id[]" value="'.$urun->id.'"> 

                                     <input type="hidden" name="indirim[]" data-value="'.$urun->id.'" value="'.$urun->indirim_tutari.'">

<input type="hidden" name="adisyon_urun_senet_id[]" value="'.$urun->senet_id.'">

                                   <input type="hidden" name="adisyon_urun_taksitli_tahsilat_id[]" value="'.$urun->taksitli_tahsilat_id.'">

                                     ';



                    if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)

                        $html .= '<input type="tel"   class="form-control try-currency tahsilat_kalemleri" style="height:26px;width: 70%;float:left;text-align:right" name="urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.').'" >';

                    else

                        $html .= '<input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.').'" >

                                          <p style="position: relative; float: left; width: 70%;">'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.').' ₺</p>';

                    $html .= '<p style="position: relative; float: left;width: 15%;margin: 0;">';

                    if($urun->hediye)

                        $html .= '<i class="fa fa-gift" style="font-size: 25px"></i>';

                    else

                        $html .= '<i class="fa fa-gift" style="visibility: hidden"></i>';

                    $html .= '</p>

                                         <div class="dropdown" style="width: 15%;float:left">

                                           <a

                                              class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"

                                              href="#"

                                              role="button"

                                              data-toggle="dropdown"

                                           >

                                              <i class="dw dw-more"></i>

                                           </a>

                                           <div

                                              class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"

                                           > ';

                    if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)

                    {

                        if(!$urun->hediye)

                            $html .= '<a class="dropdown-item tahsilat_urun_hediye_ver" data-value="'.$urun->id.'" href="#"

                                                 ><i class="fa fa-gift"></i> Hediye Ver</a

                                              >';

                        else

                            $html .= '<a class="dropdown-item tahsilat_urun_hediye_kaldir" data-value="'.$urun->id.'" href="#"

                                                 ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a

                                              >';

                        $html .= '<a class="dropdown-item tahsilat_urun_sil" href="#" data-value="'.$urun->id.'"

                                                 ><i class="dw dw-delete-3"></i> Sil</a

                                              >';

                    }

                    $html .= '</div>

                                        </div>

                                        

                                     

                                  </div>



                                     

                               </div>';

                }

                

            }

            foreach($adisyon->paketler as $key=>$paket)

            {   

                 

                if($adisyon_id != '' ||(($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari > 0 || $paket->hediye)&&  $paket->senet_id === null && $paket->taksitli_tahsilat_id === null))

                {

                    $html .= '<div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">



                              <div class="col-md-4 col-5 col-xs-5  col-sm-4">

                                 '.$paket->paket->paket_adi.'

                              </div> 

                              <div class="col-md-3  col-7 col-xs-7  col-sm-3">

                                  '.$paket->personel->personel_adi.'



                              </div>

                               <div class="col-md-2 col-5 col-xs-5  col-sm-2">

                                  1 adet

                              </div>

                              <div class="col-md-3 col-7 col-xs-7  col-sm-3"  style="text-align:right">

                                 <input type="hidden" name="adisyon_paket_id[]" value="'.$paket->id.'"> 

                                 <input type="hidden" name="indirim[]" data-value="'.$paket->id.'" value="'.$paket->indirim_tutari.'">

                                 <input type="hidden" name="adisyon_paket_senet_id[]" value="'.$paket->senet_id.'">

                                   <input type="hidden" name="adisyon_paket_taksitli_tahsilat_id[]" value="'.$paket->taksitli_tahsilat_id.'">

                                 ';

                    if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)

                        $html .= '<input type="tel"  style="height: 26px;width: 70%;float:left;text-align:right" class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.number_format($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.').'">';

                    else 

                        $html .= '<input type="hidden"  class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.number_format($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.').'">

                                      <p style="position: relative; float: left; width: 70%;">'.number_format($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.').' ₺ </p>';

                    $html .= '<p style="position: relative; float: left;width: 15%; margin:0">';

                    if($paket->hediye)

                        $html .= '<i class="fa fa-gift" style="font-size: 25px"></i>';

                    else

                        $html .= '<i class="fa fa-gift" style="visibility: hidden"></i>';

                    $html .= '</p>

                                      <div class="dropdown"  style="width: 15%;float:left">

                                           <a

                                              class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"

                                              href="#"

                                              role="button"

                                              data-toggle="dropdown"

                                           >

                                              <i class="dw dw-more"></i>

                                           </a>

                                           <div

                                              class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"

                                           > ';

                    if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)

                    {

                        if(!$paket->hediye)

                            $html .= '<a class="dropdown-item tahsilat_paket_hediye_ver" data-value="'.$paket->id.'" href="#"

                                                 ><i class="fa fa-gift"></i> Hediye Ver</a

                                              >';

                        else

                            $html .= '<a class="dropdown-item tahsilat_paket_hediye_kaldir" data-value="'.$paket->id.'" href="#"

                                                 ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a

                                              >';

                         $html .= '<a class="dropdown-item tahsilat_paket_sil" data-value="'.$paket->id.'" href="#"

                                                 ><i class="dw dw-delete-3"></i> Sil</a

                                              >';

                    }

                    $html .= ' </div>

                                        </div>

                                     

                                  </div>

                                  

                                

                               </div>';

                }

                

            }

        }

        foreach($tahsilatlar as $key=>$tahsilat)

            $html_tahsilat  .= '<tr>

                             

                              <td>'.date('d.m.Y',strtotime($tahsilat->odeme_tarihi)).'</td>

                              <td>'.number_format($tahsilat->tutar,2,',','.').'</td>

                              <td>

                                 '.$tahsilat->odeme_yontemi->odeme_yontemi.'

                              </td>

                              <td>

                                 <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" name="tahsilat_adisyondan_sil" data-value="'.$tahsilat->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                              </td>

                           </tr>';

                            

        return array(

            'kalemler'=>$html,

            'tahsilatlar' => $html_tahsilat

        ); 

 

    }

    public function adisyon_tahsilatlari(Request $request,$statstr)

    {

        $tahsilatlar = Tahsilatlar::where('adisyon_id',$request->adisyon_id)->get();



        $adisyon = Adisyonlar::where('id',$request->adisyon_id)->first();

        $tahsilat_tutari = 0;

        $html = "";



        $urunsatislari = AdisyonUrunler::where('adisyon_id',$request->adisyon_id)->sum('fiyat');

        $hizmetsatislari = AdisyonHizmetler::where('adisyon_id',$request->adisyon_id)->sum('fiyat');

        $paketsatislari = AdisyonPaketler::where('adisyon_id',$request->adisyon_id)->sum('fiyat');

        $html .= '<tr>

                              <td colspan="4" style="border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;">Ödeme Akışı</td>

                           </tr>';

        foreach ($tahsilatlar as $key => $tahsilatliste)

        {

            $html .= ' <tr>

                           

                           <td>'.date('d.m.Y',strtotime($tahsilatliste->odeme_tarihi)).'</td>

                           <td>'.number_format($tahsilatliste->tutar,2,',','.').'</td>

                           <td>

                              '.$tahsilatliste->odeme_yontemi->odeme_yontemi.'

                           </td>

                            <td>

                                <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" name="tahsilat_adisyondan_sil" data-value="'.$tahsilatliste->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                            </td>

                            

                            

                        </tr>';

            $tahsilat_tutari += $tahsilatliste->tutar;

        } 

        $adisyon_hizmet_html = '';

        $adisyon_urun_html = '';

        $adisyon_paket_html = '';

        foreach($adisyon->hizmetler as $hizmet)

        {

            $tutar = $hizmet->fiyat-TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar');

            $adisyon_hizmet_html .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0"> 



                              <div class="col-md-4">

                                 <input type="hidden" class="adisyon_kalemler" name="adisyon_odeme_hizmet[]" value="'.$tutar.'" data-value="'.$hizmet->id.'">



                                  '.$hizmet->hizmet->hizmet_adi.' 

                              </div>

                              <div class="col-md-3">';

                            if($hizmet->personel_id != null)



                                $adisyon_hizmet_html .= $hizmet->personel->personel_adi;

                            if($hizmet->cihaz_id != null) 

                                 $adisyon_hizmet_html .= $hizmet->cihaz->cihaz_adi;



                             $adisyon_hizmet_html .='</div>

                              <div class="col-md-2">

                                   

                                  1 adet

                              </div>

                             

                              <div class="col-md-2" style="text-align:right">



                                    <span name="adisyon_hizmet_tahsilat_tutari" data-value="'.$hizmet->id.'">'.number_format($tutar,2,',','.').'</span> ₺

                                    <input type="hidden" name="adisyon_hizmet_tahsilat_tutari[]" data-value="'.$hizmet->id.'" data-inputmask =" \'alias\' : \'currency\'">

                                    <input type="hidden" name="adisyon_hizmet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.$tutar.'"  data-inputmask =" \'alias\' : \'currency\'">

                                 </div>

                                 <div class="col-md-1">

                                    <button  type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="hizmet_formdan_sil_adisyon_mevcut"  data-value="'.$hizmet->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                              </div>

                           </div>';

        }

        foreach($adisyon->urunler as $urun)

        {

            $tutar = $urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar');

            $adisyon_urun_html .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">



                              <div class="col-md-4">

                                 <input type="hidden" class="adisyon_kalemler" name="adisyon_odeme_urun[]" value="'.$tutar.'" data-value="'.$urun->id.'">

                                 '.$urun->urun->urun_adi.'

                              </div>

                              <div class="col-md-3">

                                  '.$urun->personel->personel_adi.'



                              </div>

                              <div class="col-md-2">

                                    '.$urun->adet.' adet



                              </div>

                              

                              <div class="col-md-2" style="text-align:right">

                                     

                                    <span name="adisyon_urun_tahsilat_tutari" data-value="'.$urun->id.'">'.number_format($tutar,2,',','.').'</span> ₺

                                    <input type="hidden" name="adisyon_urun_tahsilat_tutari[]" data-value="'.$urun->id.'" value="" data-inputmask =" \'alias\' : \'currency\'">

                                    <input type="hidden" name="adisyon_urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.$tutar.'"  data-inputmask =" \'alias\' : \'currency\'">

                                 </div>

                                 <div class="col-md-1">

                                     <button type="button"  style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="urun_formdan_sil" data-value="'.$urun->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                                 </div>

                           </div>';

        }

        foreach($adisyon->paketler as $paket)

        {

            $tutar = $paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar');

            $adisyon_paket_html .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">



                              <div class="col-md-4">

                                 <input type="hidden"  class="adisyon_kalemler" name="adisyon_odeme_paket[]" value="'.$tutar.'" data-value="'.$paket->id.'">

                                 '.$paket->paket->paket_adi.'

                              </div>

                              <div class="col-md-3">

                                  '.$paket->personel->personel_adi.'



                              </div>

                               <div class="col-md-2">

                                  1 adet

                              </div>

                              <div class="col-md-2"  style="text-align:right">

                                     <span name="adisyon_paket_tahsilat_tutari" data-value="'.$paket->id.'">'.number_format($tutar,2,',','.').'</span> ₺

                                     <input type="hidden" name="adisyon_paket_tahsilat_tutari[]" data-value="'.$paket->id.'" value="" data-inputmask =" \'alias\' : \'currency\'">

                                     <input type="hidden" name="adisyon_paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.$tutar.'"  data-inputmask =" \'alias\' : \'currency\'">

                              </div>

                              <div class="col-md-1">



                                      <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="paket_formdan_sil" data-value="'.$paket->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>



                              </div></div>';

        }



       



        $statustext = $statstr;

        return array(

            'statustext' => $statustext,

            'html' => $html,

            'tahsilat_tutari' => number_format($tahsilat_tutari,2,',','.'),

            'toplam_tutar' => number_format(($urunsatislari + $hizmetsatislari + $paketsatislari - $adisyon->indirim_tutari),2,',','.'),

            'kalan_tutar' => number_format((($urunsatislari + $hizmetsatislari + $paketsatislari - $adisyon->indirim_tutari)-$tahsilat_tutari),2,',','.'),

            'adisyon_hizmetler_html' => $adisyon_hizmet_html,

            'adisyon_urunler_html' => $adisyon_urun_html,

            'adisyon_paketler_html' => $adisyon_paket_html,

            'tahsilat_sayisi' =>$tahsilatlar->count()





        );

    }

    public function hareket_ekle(Request $request,$islem)

    {

        $hareketler = new RandevuHareketleri();

        $hareketler->personel_id = Auth::guard('isletmeyonetim')->user()->personel_id;

        $hareketler->randevu_id = $request->randevu_id;

        $hareketler->islem = $islem;

        $hareketler->save();

    }

    public function tahsilatkaldir(Request $request){

        $adisyon_id = $request->adisyon_id;



        $tahsilat_paketler = TahsilatPaketler::where('tahsilat_id',$request->tahsilatid)->get();

        $tahsilat_urunler = TahsilatUrunler::where('tahsilat_id',$request->tahsilatid)->get();

        $tahsilat_hizmetler = TahsilatHizmetler::where('tahsilat_id',$request->tahsilatid)->get();

        foreach($tahsilat_paketler as $tahsilat_paket){

            AdisyonPaketler::where('id',$tahsilat_paket->adisyon_paket_id)->update(['hediye'=>false,'indirim_tutari'=>null]);

            $tahsilat_paket->delete();

        }

        foreach($tahsilat_urunler as $tahsilat_urun){

            AdisyonPaketler::where('id',$tahsilat_urun->adisyon_urun_id)->update(['hediye'=>false,'indirim_tutari'=>null]);

            $tahsilat_urun->delete();

        }

        foreach($tahsilat_hizmetler as $tahsilat_hizmet){

            AdisyonPaketler::where('id',$tahsilat_hizmet->adisyon_hizmet_id)->update(['hediye'=>false,'indirim_tutari'=>null]);

            $tahsilat_hizmet->delete();

        }

        



        $tahsilat = Tahsilatlar::where('id',$request->tahsilatid)->first();

        $musteri_id = $tahsilat->user_id;

        $tahsilat->delete();

        return self::musteri_tahsilatlari($request,$musteri_id,""); 

    }

    public function ongorusmeler(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu <2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

         

        $ongorusmeler = self::ongorusmegetir($request,false);

        $paketler = self::paket_liste_getir('',true,$request);

        

        return view('isletmeadmin.ongorusmeler',['paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'sayfa_baslik'=>'Ön Görüşmeler','on_gorusmeler'=>$ongorusmeler,'pageindex' => 12,'isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);  

    }

    public function ongorusmeekleduzenle(Request $request)

    {

        $ongorusme = "";

        



        if($request->on_gorusme_id != "")

        {

            $ongorusme = OnGorusmeler::where('id',$request->on_gorusme_id)->first(); 

        }

        else

        {

            $ongorusme = new OnGorusmeler(); 

        }

        if($request->musteri_id != 0)

            $ongorusme->user_id = $request->musteri_id;

        else

        {

            $user = '';

            $portfoy = '';

            if(User::where('cep_telefon',$request->telefon)->count() > 0){

                $user = User::where('cep_telefon',$request->telefon)->first(); 

            }

            else

                $user = new User();

            $user->name = $request->ad_soyad;

            $user->cep_telefon = $request->telefon;

            $user->cinsiyet = $request->cinsiyet;

            $user->adres = $request->adres;

            $user->il_id = $request->sehir;

            $user->meslek = $request->meslek;

            $user->email = $request->email;

            $user->save();

            if(MusteriPortfoy::where('user_id',$user->id)->where('salon_id',$request->sube)->count()>0)

                $portfoy = MusteriPortfoy::where('user_id',$user->id)->where('salon_id',$request->sube)->first();

            else

                $portfoy = new MusteriPortfoy();

            $portfoy->salon_id=$request->sube;

            $portfoy->musteri_tipi = $request->musteri_tipi;

            $portfoy->aktif = 1;

            $portfoy->kara_liste = 0;

            $portfoy->user_id = $user->id;

            $portfoy->save();

            $ongorusme->user_id = $user->id;



        }

        $ongorusme->salon_id = $request->sube;

        $ongorusme->ad_soyad = $request->ad_soyad;

        $ongorusme->cep_telefon = $request->telefon;

        $ongorusme->email =$request->email;

        $ongorusme->cinsiyet = $request->cinsiyet;

        $ongorusme->adres = $request->adres;

        $ongorusme->aciklama=$request->aciklama;

        $ongorusme->il_id =$request->sehir;

        $ongorusme->musteri_tipi = $request->musteri_tipi;

        $ongorusme->meslek = $request->meslek;

        if(str_contains($request->paket_urun,'urun')){

            $str = explode('-',$request->paket_urun);

            $ongorusme->urun_id = $str[1];



        }

        else

            $ongorusme->paket_id = $request->paket_urun;

        $ongorusme->hatirlatma_tarihi = $request->ongorusme_tarihi;

        $ongorusme->personel_id = $request->gorusmeyi_yapan;

        $ongorusme->save();

        $eskirandevu = Randevular::where('on_gorusme_id',$ongorusme->id)->first();

        if($eskirandevu){

            foreach($eskirandevu->hizmetler as $hizmet){

                $hizmet->delete();

                RandevuHizmetYardimciPersonel::where('randevu_hizmet_id',$hizmet->id)->delete();

            }

            $eskirandevu->delete();

        }

        

        

        $randevu = new Randevular();

        $randevu->on_gorusme_id = $ongorusme->id;

        $randevu->user_id = $ongorusme->user_id;

        $randevu->salon_id = $ongorusme->salon_id;

         

        $randevu->tarih = $request->ongorusme_tarihi;

        $randevu->saat = $request->ongorusme_saati;

        $randevu->salon = true;

        $randevu->sms_hatirlatma = true; 

        $randevu->durum = 1;

        $randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;

    

        $randevu->save();

        $ongorusmehizmeti = new RandevuHizmetler();

        $ongorusmehizmeti->hizmet_id = 1;

        $ongorusmehizmeti->personel_id =  $request->gorusmeyi_yapan;

        $ongorusmehizmeti->saat = $request->ongorusme_saati;

        $ongorusmehizmeti->saat_bitis = date('H:i:s',strtotime('+1 hours',strtotime($request->ongorusme_saati)));

        $ongorusmehizmeti->randevu_id = $randevu->id;

        $ongorusmehizmeti->save();

   

        $gsm = $user->cep_telefon;

                 

                if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$ongorusme->salon_id)->value('musteri')==1)

                {

                    $mesajlar = array(

                    array("to"=>$gsm,"message"=>$ongorusme->salon->salon_adi . " tarafından ".date('d.m.Y',strtotime($request->ongorusme_tarihi)) .'-'.date('H:i', strtotime($request->ongorusme_saati)) .' olarak ön görüşme randevunuz düzenlenmiştir. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0'.$ongorusme->salon->telefon_1),



                    );

                    self::sms_gonder($request,$mesajlar,false,1,false);

                }

                if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$ongorusme->salon_id)->value('personel')==1)

                {

                    foreach($randevu->hizmetler as $hizmet)

                    {

                        $mesaj = '';

                        if($ongorusme->paket_id !== null)

                            $mesaj = $ongorusme->musteri->name." isimli müşterinin ". date('d.m.Y',strtotime($request->ongorusme_tarihi)) ." - ". date('H:i',strtotime($request->ongorusme_saati)) ." ".$ongorusme->paket->paket_adi." için ön görüşme randevusu randevusu ".Auth::guard('isletmeyonetim')->user()->name." tarafından düzenlenmiştir.";

                        if($ongorusme->urun_id !== null)

                            $mesaj = $ongorusme->musteri->name." isimli müşterinin ". date('d.m.Y',strtotime($request->ongorusme_tarihi)) ." - ". date('H:i',strtotime($request->ongorusme_saati)) ." ".$ongorusme->urun->urun_adi." için ön görüşme randevusu randevusu ".Auth::guard('isletmeyonetim')->user()->name." tarafından düzenlenmiştir.";



                        $yetkiliid=Personeller::where('id',$request->gorusmeyi_yapan)->value('yetkili_id');

                        $mesajlar = array(

                        array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj),



                        );

                        self::sms_gonder($request,$mesajlar,false,1,false);



                        self::bildirimekle($request,$ongorusme->salon_id,$mesaj,"#",$request->gorusmeyi_yapan,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$randevu->id);

                            $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$request->gorusmeyi_yapan)->pluck('bildirim_id')->toArray(); 



                        self::bildirimgonder($bildirimkimlikleri,"Yeni Ön Görüşme Randevusu",$mesaj,$ongorusme->salon_id);



                    }

        } 





       



        return self::ongorusmegetir($request,false);



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

        return json_encode(DB::table('randevular')->join('on_gorusmeler','randevular.on_gorusme_id','=','on_gorusmeler.id')->select('on_gorusmeler.*','randevular.tarih as tarih','randevular.saat as saat')->where('on_gorusmeler.id',$request->ongorusme_id)->first());

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

            array_push($mesajlar, array("to"=>$ongorusme->cep_telefon,"message"=> "Sayın ".$ongorusme->ad_soyad."\n\n". $ongorusme->paket->paket_adi." ile ilgili ön görüşme sağlamıştık. Geri dönüşünüzü beklediğimizi belirtir, iyi günler dileriz."));



        }

        self::sms_gonder($request,$mesajlar,true,1,false);

    }

    public function sms_gonder(Request $request,$mesajlar,$geribildirimgonder,$tur,$dogrulama)

    {

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        if($isletme->sms_baslik !== null && $isletme->sms_apikey !== null)

        {

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

            $decoded = json_decode($response,true);

            $iletimdurum = '';



            if(count($decoded["response"])!=0 && $decoded != null){

                if(!$dogrulama)

                {

                

                    $rapor = new SMSIletimRaporlari();

                    $rapor->salon_id = $isletme->id;

                    $rapor->tur = $tur;

                    $aciklama = '';

                    foreach($mesajlar as $mesaj)

                        $aciklama .= $mesaj['message'].'\n';

                    $rapor->aciklama = $aciklama;

                    $rapor->rapor_id = $decoded["response"]["message"]["id"];

                    $rapor->adet = $decoded["response"]["message"]["count"];

                    $rapor->kredi = $decoded["response"]["message"]["total_price"];

                    sleep(0.5);

                    $durum = self::sms_rapor_getir($decoded["response"]["message"]["id"],$isletme);

                    $rapor->durum = $durum["response"]["message"]["status"];

                    $rapor->save();

                    $iletimdurum = $durum["response"]["message"]["status"];

                }

                

            }  

            $returntext = '';

            $statustext = '';

            $titletext = '';

            if($iletimdurum==91){

                $returntext = 'Mesajınız bakiyeniz yetersiz olduğu için alıcılarınıza gönderilemedi.';

                $titletext = 'Hata';

                $statustext ='error';

            }

            elseif($iletimdurum==92){

                $returntext = 'Mesajınız gönderimlerin sağlayıcımız tarafından durudurulması nedeniyle alıcılarınıza gönderilemedi. Lütfen daha sonra tekrar deneyiniz.';

                $titletext = 'Hata';

                $statustext ='error';

            }



            elseif($iletimdurum==93){

                $returntext = 'Mesajınız teknik bir arıza nedeniye alıcılarınıza gönderilemedi. Lütfen daha sonra tekrar deneyiniz.';

                $titletext = 'Hata';

                $statustext ='error';

            }

            elseif($iletimdurum==94){

                $returntext = 'Mesajınız gönderiminiz engellendiği için alıcılarınıza gönderilemedi. Lütfen sistem yöneticisine başvurunuz.';

                $titletext = 'Hata';

                $statustext ='error';

            }

            else{

                $titletext = 'Başarılı';

                $returntext = 'Mesajınız alıcılarınıza başarıyla gönderildi.';

                $statustext ='success';

            } 

            if($geribildirimgonder){

                  return array(

                    'title' =>$titletext,

                    'status' =>$statustext,

                    'text'=>$returntext,

                );

                exit;

            }

            else

            {

                return '';

                exit;

            } 

        }

      

    }

    public function smscoklutest(Request $request)

    { 

          

        /*$isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $randevular = '';

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0|| Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))

            $randevular = self::randevuyukle($request,1);

        else    

            $randevular = self::randevuyukle($request,Salonlar::where('id',self::mevcutsube($request))->value('randevu_takvim_turu'));

         

         

        $paketler = self::paket_liste_getir('',true,$request);

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $kalan_uyelik_suresi = self::lisans_sure_kontrol($request);*/   

         $randevular = self::randevuyukle($request,Salonlar::where('id',self::mevcutsube($request))->value('randevu_takvim_turu'),date('Y-m-d'),date('Y-m-d'));

        return $randevular;

         

    } 

    public function denemedb2(Request $request){

        echo DB::connection('mysql1')->table('users')->get();

    }

    public function ongorusmegetir(Request $request,$gunluk){

         return DB::table('on_gorusmeler')

        ->join('salonlar','on_gorusmeler.salon_id','=','salonlar.id')

        ->leftjoin('users','on_gorusmeler.user_id','=','users.id')

        

        ->join('salon_personelleri','on_gorusmeler.personel_id','=','salon_personelleri.id')

        ->leftjoin('paketler','on_gorusmeler.paket_id','=','paketler.id')

        ->leftjoin('urunler','on_gorusmeler.urun_id','=','urunler.id')

        ->leftjoin('il','on_gorusmeler.il_id','=','il.id')

        ->select(                             

            DB::raw('CONCAT("<div class=\"dt-checkbox\"><input type=\"checkbox\" name=\"on_gorusme_bilgi[]\" value=\"",on_gorusmeler.id,"\"><span class=\"dt-checkbox-label\"></span></div>") as id'),

            'on_gorusmeler.ad_soyad as musteri',

            'on_gorusmeler.cep_telefon as telefon',

            DB::raw('CONCAT("<span style=\"display:none\">",UNIX_TIMESTAMP(on_gorusmeler.created_at),"</span>",DATE_FORMAT(on_gorusmeler.created_at, "%d.%m.%Y")) as olusturulma'),

            DB::raw('DATE_FORMAT(on_gorusmeler.hatirlatma_tarihi, "%d.%m.%Y") as hatirlatma'),

            DB::raw('CASE WHEN on_gorusmeler.musteri_tipi=1 THEN "İnternet" WHEN on_gorusmeler.musteri_tipi=2 THEN "Reklam" WHEN on_gorusmeler.musteri_tipi=3 THEN "Instagram" WHEN on_gorusmeler.musteri_tipi=4 THEN "Facebook" WHEN on_gorusmeler.musteri_tipi=5 THEN "Tanıdık" END as musteri_tipi'),

            DB::raw('CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN CONCAT("Paket : ",paketler.paket_adi) ELSE CONCAT("Ürün : ",urunler.urun_adi) END as paket '),

           

            'salon_personelleri.personel_adi as gorusmeyiyapan',

            DB::raw('CASE WHEN on_gorusmeler.durum=0 THEN CONCAT("<a style=\"color:#fff\" name=\"satisyapilmamasebep\" data-value=\"",on_gorusmeler.id,"\" class=\"btn btn-danger btn-block\">Satış Yapılmadı</a><input type=\"hidden\"  name=\"satisyapilmamanotu\" data-value=\"",on_gorusmeler.id,"\" value=\"",COALESCE(on_gorusmeler.satisyapilmadi_not,"Belirtilmemiş"),"\">") WHEN on_gorusmeler.durum=1 THEN "<a style=\"color:#fff\"  class=\"btn btn-success btn-block\">Satış Yapıldı</a>" WHEN on_gorusmeler.durum IS NULL THEN "<a style=\"color:#000\" class=\"btn btn-warning btn-block\">Beklemede</a>" END as durum'),

                DB::raw('CASE WHEN on_gorusmeler.durum IS NULL THEN CONCAT(

                "<div class=\"dropdown\">

                    <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                        href=\"#\"

                        role=\"button\"

                        data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                    </a>

                    <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                        <a onclick=\"modalbaslikata(\'Ön Görüşme Düzenleme\',\'\' )\" class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\" data-target=\"#ongorusme-modal\" name=\"ongorusme_duzenle\" data-value=\"",on_gorusmeler.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>

                  

                        <a class=\"dropdown-item\" href=\"#\" name=\"", (CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN "satis_yapildi" ELSE "urun_satis_yapildi" END)  ,"\" data-value=\"",on_gorusmeler.id,"\"><i class=\"fa fa-plus\"></i> Satış Yapıldı</a>

                        <a class=\"dropdown-item\" href=\"#\" name=\"satis_yapilmadi\" data-value=\"",on_gorusmeler.id,"\"><i class=\"fa fa-times\"></i> Satış Yapılmadı</a>

                        </div></div>") END as islemler'))

        ->where('salonlar.id',self::mevcutsube($request))

        ->where(function ($q) use ($request){

            if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) 

                $q->where('salon_personelleri.id',Personeller::where('salon_id',self::mevcutsube($request))->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id'));

        })

        ->where(function ($q) use ($gunluk){if($gunluk) $q->where('on_gorusmeler.created_at','like','%'.date('Y-m-d').'%');})

        ->get();

    }

    public function paketsatislari(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $paketler = self::paket_liste_getir("",false,$request);

        

        $personeller = Personeller::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->get();

        return view('isletmeadmin.paketsatislari',['paketler'=>$paketler, 'bildirimler'=>self::bildirimgetir($request),'sayfa_baslik' => 'Paketler','pageindex' => 13,'isletme' => $isletme,'personeller'=>$personeller, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

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

                        

                        </div></div>") as islemler'))->where('paket_satislari.salon_id',self::mevcutsube($request))->get();

        return $paketsatislari;





        

    }





    public function randevuliste(Request $request)

    {

         $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $paketler = self::paket_liste_getir('',true,$request);

 

        $randevular = self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),true,true,true,null,self::mevcutsube($request),'');



        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

          echo 'Bitti';

         

        return view('isletmeadmin.randevular_liste',['isletme'=>$isletme,'randevular_liste'=>$randevular,'paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request),'pageindex'=>3,'sayfa_baslik'=>'Randevular', 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request), 'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);



    }



    public function randevulistegetir(Request $request)

    {

        

    }



    public function bildirimgetir(Request $request)

    {

        $personel = Personeller::where('salon_id',self::mevcutsube($request) )->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        $bildirimler = Bildirimler::where('personel_id',$personel)->where('salon_id',self::mevcutsube($request))->orderBy('id','desc')->get();

        return $bildirimler;

    }

    public function paketsatisekle(Request $request){

        $adisyon_id = '';

         $hizmete_ait_randevu = array();

        $adisyon_duzenleme = false;

        if(is_numeric($request->adisyon_id)){

            $adisyon_id = $request->adisyon_id;

            $adisyon_duzenleme = true;

        }

        else

            $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id,$request->sube,'Paket Satışı',$request->paket_satis_tarihi);

        foreach($request->paketadi as $key=>$paket){

             

            $paket = Paketler::where('id',$request->paketadi[$key])->first();

            

         

            $adisyon_paket_id = self::adisyona_paket_ekle($adisyon_id,$request->paketadi[$key],$request->paketfiyat[$key],$request->paketbaslangictarihi[$key],$request->seansaralikgun[$key],$request->paket_satici,null,null);

           

            $seanstarih = '';

            $yenisaatbaslangic = '';

            foreach($paket->hizmetler as $key2 => $hizmet2)

            {

                $randevu_id  ='';

                for($i=1;$i<=$hizmet2->seans;$i++)

                {

                    if($i==1){

                         $seanstarih = $request->paketbaslangictarihi[$key];

                        

                    }

                    if($i>1){

                        $seanstarih = date('Y-m-d',strtotime('+'.$request->paketbaslangictarihi[$key].' days',strtotime($seanstarih)));

                         

                    }  

                    $hizmet_sure = 60;

                    if(SalonHizmetler::where('salon_id',$paket->salon_id)->where('hizmet_id',$hizmet2->hizmet_id)->value('sure_dk') > 0)

                        $hizmet_sure = SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$hizmet2->hizmet_id)->value('sure_dk');

                    if($key2==0||count($hizmete_ait_randevu)<$i)

                    {

                        $yenisaatbaslangic = $request->paketbaslangicrandevusaati[$key];

                        $seans_randevu = new Randevular(); 

                        

                        $seans_randevu->user_id = $request->musteri_id;

                        $seans_randevu->tarih = $seanstarih;

                        $seans_randevu->salon_id = $paket->salon_id;

                        $seans_randevu->durum = 1;

                        $seans_randevu->saat = $request->paketbaslangicrandevusaati[$key];

                        $seans_randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;  

                     

                        $seans_randevu->salon = 1;

                        $seans_randevu->save(); 

                        

                        $randevu_id = $seans_randevu->id; 

                        array_push($hizmete_ait_randevu,$randevu_id);

                        if($i==$hizmet2->seans)

                            $yenisaatbaslangic = date("H:i", strtotime('+'.$hizmet_sure.' minutes', strtotime($request->paketbaslangicrandevusaati[$key])));

                    }

                    else

                        $randevu_id = $hizmete_ait_randevu[$i-1];

                    $seans = new AdisyonPaketSeanslar();

                    $seans->adisyon_paket_id = $paket->id;

                    $seans->seans_tarih = $seanstarih;

                    $seans->hizmet_id = $hizmet2->hizmet_id;

                    $seans->seans_no = $i;

                    $seans->seans_saat = $yenisaatbaslangic;



                    $seans->randevu_id = $randevu_id;

                    $seans->save();





                    $seans_randevu_hizmet = new RandevuHizmetler();

                    $seans_randevu_hizmet->randevu_id = $randevu_id;

                    $seans_randevu_hizmet->hizmet_id =$hizmet2->hizmet_id;

                    $seans_randevu_hizmet->personel_id = 183;



                    $seans_randevu_hizmet->sure_dk = $hizmet_sure;

                    if($key2==0||count($hizmete_ait_randevu)<$i)    

                        $seans_randevu_hizmet->saat = $request->paketbaslangicrandevusaati[$key];

                    else

                        $seans_randevu_hizmet->saat = $yenisaatbaslangic;



                     

                    $seans_randevu_hizmet->saat_bitis = date("H:i", strtotime('+'.$hizmet_sure.' minutes', strtotime($yenisaatbaslangic))); 

                    $seans_randevu_hizmet->save(); 

                }  

            }



            /*for($i=1;$i<=$paket->hizmetler->sum('seans');$i++)

            {

                if($i>1)

                    $seanstarih = date('Y-m-d',strtotime('+'.$request->seansaralikgun[$key].' days',strtotime($seanstarih)));

                $seans = new AdisyonPaketSeanslar();

                $seans->adisyon_paket_id = $adisyon_paket_id;

                $seans->seans_tarih = $seanstarih;

                $seans->seans_no = $i;

                $hizmet = $paket->hizmetler[$i-1];

                $seans->hizmet_id = $hizmet->hizmet_id;

                $seans->save();



            }*/



             



        }

        $adisyon_paket_liste = self::adisyon_paket_satis_getir($adisyon_id,false,'');

        $indirim = (Adisyonlar::where('id',$adisyon_id)->value('indirim_tutari')) ? Adisyonlar::where('id',$adisyon_id)->value('indirim_tutari') : 0;

        $adisyon_toplam_tutar = AdisyonHizmetler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonUrunler::where('adisyon_id',$adisyon_id)->sum('fiyat')+AdisyonPaketler::where('adisyon_id',$adisyon_id)->sum('fiyat') - $indirim;

        $tahsil_edilen_tutar = Tahsilatlar::where('adisyon_id',$adisyon_id)->sum('tutar');

        $kalan_tutar = $adisyon_toplam_tutar - $tahsil_edilen_tutar;

        return array(

           'paketsatislari' => json_encode(self::paketsatislarigetir($request)),

           'adisyonpaketleri' => $adisyon_paket_liste['html'],

           'tahsilat_paket_eklenecek' => $adisyon_paket_liste['tahsilat_paket_eklenecek'],

           'adisyonduzenleme' => $adisyon_duzenleme,

           'tahsil_edilen'=>number_format($tahsil_edilen_tutar,2,',','.'),

           'kalan_tutar' =>number_format($kalan_tutar,2,',','.'),

           'tum_tahsilatlar'=>self::musteri_tahsilatlari($request,$request->musteri_id,"")

                

        );

    }

    public function adisyon_paket_satis_getir($adisyon_id,$visibility,$paket_id)

    {

        $satislar = AdisyonPaketler::where('adisyon_id',$adisyon_id)->get();

        $salon_id = Adisyonlar::where('id',$adisyon_id)->value('salon_id');

        $html = "";

        $tahsilat_paket_eklenecek = '';

        foreach($satislar as $paket){

            $tahsilat_paket_eklenecek .= ' <div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">



                              <div class="col-md-4">

                                 <input type="hidden"  class="adisyon_kalemler" name="adisyon_odeme_paket[]" value="'.($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar')).'" data-value="'.$paket->id.'">

                                 '.$paket->paket->paket_adi.'

                              </div>

                              <div class="col-md-3">

                                  '.$paket->personel->personel_adi.'



                              </div>

                               <div class="col-md-2">

                                  1 adet

                              </div>

                              <div class="col-md-2"  style="text-align:right">

                                     <span name="adisyon_paket_tahsilat_tutari" data-value="'.$paket->id.'">'.number_format($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar'),2,',','.').'</span> ₺

                                     <input type="hidden" name="adisyon_paket_tahsilat_tutari[]" data-value="'.$paket->id.'" value="" data-inputmask =" \'alias\' : \'currency\'">

                                     <input type="hidden" name="adisyon_paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar')).'"  data-inputmask =" \'alias\' : \'currency\'">

                              </div>

                              <div class="col-md-1">



                                      <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="paket_formdan_sil" data-value="'.$paket->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>



                              </div>

                            

                           </div>';

                        $html .= '<tr>

                        <td>'.date('d.m.Y',strtotime($paket->baslangic_tarihi)).'</td> 

         

                        <td>'.$paket->personel->personel_adi.'</td> 

                        

                        <td>'.$paket->paket->paket_adi.'</td>

                        <td>

                           <button name="paketteki_seanslari_beklemede_isaretle" type="button" class="btn btn-warning">

                           '.AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',null)->count().'&nbsp;

                            <i class="fa fa-calendar"></i></button>

                           <button name="paketteki_seanslari_geldi_isaretle" type="button" class="btn btn-success">

                           '.AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',true)->count().'&nbsp;

                            <i class="fa fa-check"></i></button>

                           <button name="paketteki_seanslari_geldi_isaretle" type="button" class="btn btn-danger">

                           '.AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',false)->count().'&nbsp;

                            <i class="fa fa-times"></i></button>



                        </td>

                        <td> <input type="hidden" name="paket_fiyati_adisyon[]" value="'.$paket->fiyat.'"> 

                           '.$paket->fiyat.'₺



                        </td>

                        <td>

                            <button type="button" name="paket_formdan_sil" data-value="'.$paket->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                        ';

                        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$salon_id)->count() == 0)

                            $html .='<button type="button" name="paket_seans_detay_getir" data-value="'.$paket->id.'" class="btn btn-primary"><i class="fa fa-chevron-down"></i></button>';



                        $html .= '</td>

                            

                        

                     </tr>

                     ';

                     if($visibility && $paket_id == $paket->id && DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() == 0)

                        $html .= '<tr name="paket_seanslari" data-value="'.$paket->id.'" style="visibility: visible">';

                     else

                        $html .= '<tr name="paket_seanslari" data-value="'.$paket->id.'" style="visibility: collapse">';



                    $html .= '<td colspan="5">';

                    foreach($paket->seanslar as $key=>$seans)

                    {

                        $html.=  '<div class="row">

                              <div class="col-md-2">

                                 <div class="form-group">

                                    <label style="font-size:12px">'.++$key.' Seans Tarihi</label>';

                        

                            if($seans->seans_tarih!==null) 

                                $html .= '<input type="text" class="form-control" name="seans_tarihi_adisyon_paket" data-value="'.$seans->id.'" required value="'.$seans->seans_tarih.'">';

                            else

                                $html .= '<input type="text" class="form-control" name="seans_tarihi_adisyon_paket" data-value="'.$seans->id.'" required value="'.date('Y-m-d').'">';

                        



                        $html .= '</div>

                              </div>

                              <div class="col-md-2">

                                 <div class="form-group">

                                    <label style="font-size:12px">İşlem Adı</label>';

                         

                            $html .= '<select class="form-control" name="paketseanshizmet" data-value="'.$seans->id.'"><option value="">Hizmet Seçimi</option>'; 

                            foreach($paket->paket->hizmetler as $hizmet)

                            {

                                if($seans->hizmet_id == $hizmet->hizmet_id)

                                    $html .= '<option selected value="'.$hizmet->hizmet_id.'">'.$hizmet->hizmet->hizmet_adi.'</option>';

                                else

                                    $html .= '<option value="'.$hizmet->hizmet_id.'">'.$hizmet->hizmet->hizmet_adi.'</option>';

                            }

                            $html .= '</select>';

                        

                        $html .='</div>

                              </div>

                              <div class="col-md-3">

                                 <div class="form-group">

                                    <label style="font-size:12px">Personel & Cihaz</label>';

                         

                            $html .= '<select name="paketseanspersonelcihaz" data-value="'.$seans->id.'" class="form-control custom-select2 opsiyonelSelect" style="width: 100%;"><option></option>';

                            foreach(IsletmeYetkilileri::where('salon_id',$salon_id)->where('aktif',true)->get() as $personel)

                            {

                                if($seans->personel_id == $personel->personel_id)

                                    $html .= ' <option selected value="'.$personel->personel_id.'">'.$personel->name.'</option>';

                                else

                                     $html .= ' <option value="'.$personel->personel_id.'">'.$personel->name.'</option>';

                            }

                            foreach(Cihazlar::where('salon_id',$salon_id)->where('durum',true)->where('aktifmi',true)->get() as $cihaz){

                                if($seans->cihaz_id == $cihaz->id)

                                        $html .= '<option selected value="cihaz-'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                                else

                                         $html .= '<option  value="cihaz-'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                            } 

                            $html .= '</select>';

                         

                                       

                        $html .= '

                                 </div>

                      

                                 

                              </div>

                              <div class="col-md-2">

                                <div class="form-group">

                                    <label  style="font-size:12px">Oda (Opsiyonel)</label>';

                                     

                                        $html .= ' <select name="paketseansoda" data-value="'.$seans->id.'"  class="form-control opsiyonelSelect" style="width:100%">

                                                               <option></option>';



                                        foreach(Odalar::where('salon_id',$salon_id)->where('durum',true)->where('aktifmi',true)->get() as $oda){

                                            if($seans->oda_id == $oda->id)

                                                $html .='<option selected value="'.$oda->id.'">'.$oda->oda_adi.'</option>';

                                            else

                                                $html .= '<option value="'.$oda->id.'">'.$oda->oda_adi.'</option>';

                                        }

                                        $html .= '</select>';

                                    

                                        if($seans->oda_id !== null)

                                            $html .= ' <p style="font-size:14px">'.$seans->oda->oda_adi.'</p>';



                        $html .='</div>

                              </div>

                              <div class="col-md-2">

                                 <div class="form-group">

                                    <label style="font-size:12px;width:100%">Durum</label>';

                        if($seans->geldi === 1)

                            $html .= '<select name="paketseansdurum"  data-value="'.$seans->id.'" class="form-control form-control-success" style="width: 100%;">

                                       <option class="btn btn-success" selected value="1">Geldi</option>

                                       <option class="btn btn-danger"  value="0">Gelmedi</option>

                                       <option class="btn btn-warning" value="2">Bekliyor</option></select>';

                        if($seans->geldi === 0)

                            $html .= '<select name="paketseansdurum"  data-value="'.$seans->id.'" class="form-control form-control-danger" style="width: 100%;">

                                       <option class="btn btn-success" value="1">Geldi</option>

                                       <option class="btn btn-danger" selected value="0">Gelmedi</option>

                                       <option class="btn btn-warning" value="2">Bekliyor</option></select>';

                        if($seans->geldi === null)

                            $html .= '<select name="paketseansdurum"  data-value="'.$seans->id.'" class="form-control form-control-warning" style="width: 100%;">

                                       <option class="btn btn-success" value="1">Geldi</option>

                                       <option class="btn btn-danger" value="0">Gelmedi</option>

                                       <option class="btn btn-warning" selected value="2">Bekliyor</option></select>';

                        

                       



                                   

                        $html .= '</div>



                              </div>

                              <div class="col-md-1">

                                 <div class="form-group">

                                     <label style="visibility: hidden;">Randevu</label>';

                       if($seans->geldi !== 1)

                       {

                             if($seans->randevu_olusturuldu)



                            $html .= '<button title="Seçili tarihte randevu oluşturulmuştur" type="button" name="randevu_olusturuldu" data-value="'.$paket->adisyon_id.'" data-index-number="'.$seans->id.'" class="btn btn-success" style="padding: 6px;font-size: 30px"><i class="fa fa-calendar"></i></button>';

                        else

                            $html .= '<button title="Seçili tarihe randevu oluşturun" type="button" name="seans_randevu_olustur" data-value="'.$paket->adisyon_id.'" data-index-number="'.$seans->id.'" class="btn btn-primary" style="padding: 6px;font-size: 30px"><i class="fa fa-calendar"></i></button>';

                       }

                       

                        $html .= '</div>

                              </div>

                           </div>';

                     }

                     $html .='</td>

                     </tr>';

        }

        if($satislar->count()==0)

            $html .= '<tr>

                        <td colspan="5" style="text-align: center;">Kayıt Bulunamadı</td>

                     </tr>';

        return array(

            'html' => $html,

            'tahsilat_paket_eklenecek'=>$tahsilat_paket_eklenecek,

        );

    }

    public function alacaklar(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        return view('isletmeadmin.alacaklar',['pageindex'=>16,'sayfa_baslik'=>'Alacak Takibi','bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true,$request),'alacaklar'=>self::alacakgetir($request),'isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);



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

            DB::raw('FORMAT(masraflar.tutar,2,"tr_TR") as tutar'),

            'salon_personelleri.personel_adi as masraf_sahibi',

            'odeme_yontemleri.odeme_yontemi as odeme_yontemi',

            DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(masraflar.tarih,"%Y%m%d"), "</span>",DATE_FORMAT(masraflar.tarih,"%d.%m.%Y"))  as tarih'),

            DB::raw('CONCAT("<button onclick=\"modalbaslikata(\'Masraf Düzenleme\',\'\' )\" class=\"btn btn-primary\" href=\"#\" data-toggle=\"modal\" data-target=\"#yeni_masraf_modal\" name=\"masraf_duzenle\" data-value=\"",masraflar.id,"\"><i class=\"fa fa-edit\"></i></button> <button   class=\"btn btn-danger\" href=\"#\"  name=\"masraf_sil\" data-value=\"",masraflar.id,"\"><i class=\"fa fa-times\"></i></button>") as islemler') 

        )->where('masraflar.salon_id',self::mevcutsube($request))->get();

        return $masraflar;



    }

    public function masraflar(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler )  || $isletme->uyelik_turu < 2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        return view('isletmeadmin.masraflar',['isletme'=>$isletme,'bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true,$request),'pageindex'=>15,'sayfa_baslik'=>'Masraflar','bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true,$request),'masraflar'=>self::masrafgetir($request), 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function alacakgetir(Request $request)

    {

 





          $alacaklar = DB::table('alacaklar')

        ->join('users','alacaklar.user_id','=','users.id')

        ->join('salonlar','alacaklar.salon_id','=','salonlar.id')

        ->join('adisyonlar','alacaklar.adisyon_id','=','adisyonlar.id')

        ->leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

        ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

        ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

        ->leftjoin('hizmetler','adisyon_hizmetler.hizmet_id','=','hizmetler.id')

        ->leftjoin('urunler','adisyon_urunler.urun_id','=','urunler.id')

        ->leftjoin('paketler','adisyon_paketler.paket_id','=','paketler.id')

        ->select(

            'users.name as musteri',

            

             

            DB::raw('FORMAT(alacaklar.tutar,2,"tr_TR") as tutar'),

            



            DB::raw('CONCAT(( SELECT COALESCE(GROUP_CONCAT(hizmetler.hizmet_adi),"") from hizmetler where adisyon_hizmetler.hizmet_id=hizmetler.id)," ",(SELECT COALESCE(GROUP_CONCAT(paketler.paket_adi),"") from paketler where adisyon_paketler.paket_id = paketler.id)," ",(SELECT COALESCE(GROUP_CONCAT(urunler.urun_adi),"") FROM urunler where adisyon_urunler.urun_id = urunler.id)) as icerik'),



            DB::raw('CONCAT("<p style=\"display:none\">",DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%Y%m%d") ,"</p><span ", CASE WHEN alacaklar.planlanan_odeme_tarihi < NOW() THEN "class=\"btn btn-danger\">" ELSE "class=\"btn btn-primary\">" END,  DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%d.%m.%Y"),"</span>" ) as planlanan_odeme_tarihi'),



            /* DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),*/



            

            DB::raw('DATE_FORMAT(alacaklar.created_at,"%d.%m.%Y") as olusturulma'),

            DB::raw('CONCAT("<a href=\"/isletmeyonetim/adisyon/",adisyonlar.id,"\" class=\"btn btn-primary\"> Tahsil Et <i class=\"fa fa-chevron-right\"></i></button>") as islemler') 

        )->where('alacaklar.salon_id',self::mevcutsube($request))->get();

        $alacaklar_hizmet=DB::table('alacaklar')

         ->join('users','alacaklar.user_id','=','users.id')

        ->join('salonlar','alacaklar.salon_id','=','salonlar.id')

        ->join('adisyonlar','alacaklar.adisyon_id','=','adisyonlar.id')

          ->leftjoin('adisyon_hizmetler','adisyon_hizmetler.adisyon_id','=','adisyonlar.id')

        ->leftjoin('hizmetler','adisyon_hizmetler.hizmet_id','=','hizmetler.id')

          ->select(

            'users.name as musteri',

            

             

            DB::raw('FORMAT(alacaklar.tutar,2,"tr_TR") as tutar'),

            



            DB::raw('CONCAT(( SELECT COALESCE(GROUP_CONCAT(hizmetler.hizmet_adi),"") from hizmetler where adisyon_hizmetler.hizmet_id=hizmetler.id)) as icerik'),



            DB::raw('CONCAT("<p style=\"display:none\">",DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%Y%m%d") ,"</p><span ", CASE WHEN alacaklar.planlanan_odeme_tarihi < NOW() THEN "class=\"btn btn-danger\">" ELSE "class=\"btn btn-primary\">" END,  DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%d.%m.%Y"),"</span>" ) as planlanan_odeme_tarihi'),



            /* DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),*/



            

            DB::raw('DATE_FORMAT(alacaklar.created_at,"%d.%m.%Y") as olusturulma'),

            DB::raw('CONCAT("<a href=\"/isletmeyonetim/adisyon/",adisyonlar.id,"\" class=\"btn btn-primary\"> Tahsil Et <i class=\"fa fa-chevron-right\"></i></button>") as islemler') 

        )->where('alacaklar.salon_id',self::mevcutsube($request))->get();

          $alacaklar_urun=DB::table('alacaklar')

         ->join('users','alacaklar.user_id','=','users.id')

        ->join('salonlar','alacaklar.salon_id','=','salonlar.id')

        ->join('adisyonlar','alacaklar.adisyon_id','=','adisyonlar.id')

        ->leftjoin('adisyon_urunler','adisyon_urunler.adisyon_id','=','adisyonlar.id')

        ->leftjoin('urunler','adisyon_urunler.urun_id','=','urunler.id')

          ->select(

            'users.name as musteri',

            

             

            DB::raw('FORMAT(alacaklar.tutar,2,"tr_TR") as tutar'),

            



            DB::raw('CONCAT(( SELECT COALESCE(GROUP_CONCAT(urunler.urun_adi),"") FROM urunler where adisyon_urunler.urun_id = urunler.id)) as icerik'),



            DB::raw('CONCAT("<p style=\"display:none\">",DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%Y%m%d") ,"</p><span ", CASE WHEN alacaklar.planlanan_odeme_tarihi < NOW() THEN "class=\"btn btn-danger\">" ELSE "class=\"btn btn-primary\">" END,  DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%d.%m.%Y"),"</span>" ) as planlanan_odeme_tarihi'),



            /* DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),*/



            

            DB::raw('DATE_FORMAT(alacaklar.created_at,"%d.%m.%Y") as olusturulma'),

            DB::raw('CONCAT("<a href=\"/isletmeyonetim/adisyon/",adisyonlar.id,"\" class=\"btn btn-primary\"> Tahsil Et <i class=\"fa fa-chevron-right\"></i></button>") as islemler') 

        )->where('alacaklar.salon_id',self::mevcutsube($request))->get();

           $alacaklar_paket=DB::table('alacaklar')

         ->join('users','alacaklar.user_id','=','users.id')

        ->join('salonlar','alacaklar.salon_id','=','salonlar.id')

        ->join('adisyonlar','alacaklar.adisyon_id','=','adisyonlar.id')

        ->leftjoin('adisyon_paketler','adisyon_paketler.adisyon_id','=','adisyonlar.id')

        ->leftjoin('paketler','adisyon_paketler.paket_id','=','paketler.id')

          ->select(

            'users.name as musteri',

            

             

            DB::raw('FORMAT(alacaklar.tutar,2,"tr_TR") as tutar'),

            



            DB::raw('CONCAT(( SELECT COALESCE(GROUP_CONCAT(paketler.paket_adi),"") from paketler where adisyon_paketler.paket_id = paketler.id)) as icerik'),



            DB::raw('CONCAT("<p style=\"display:none\">",DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%Y%m%d") ,"</p><span ", CASE WHEN alacaklar.planlanan_odeme_tarihi < NOW() THEN "class=\"btn btn-danger\">" ELSE "class=\"btn btn-primary\">" END,  DATE_FORMAT(alacaklar.planlanan_odeme_tarihi,"%d.%m.%Y"),"</span>" ) as planlanan_odeme_tarihi'),



            /* DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),*/



            

            DB::raw('DATE_FORMAT(alacaklar.created_at,"%d.%m.%Y") as olusturulma'),

            DB::raw('CONCAT("<a href=\"/isletmeyonetim/adisyon/",adisyonlar.id,"\" class=\"btn btn-primary\"> Tahsil Et <i class=\"fa fa-chevron-right\"></i></button>") as islemler') 

        )->where('alacaklar.salon_id',self::mevcutsube($request))->get();



        

        return array(

        'alacaklar'=>$alacaklar,

        'alacaklar_hizmet'=>$alacaklar_hizmet,

         'alacaklar_urun'=>$alacaklar_urun,

          'alacaklar_paket'=>$alacaklar_paket,

          'tum_taksitler'=>self::taksitleri_getir($request,'',''),

        

        'taksitler_acik'=>self::taksitleri_getir($request,0,''),

        'taksitler_kapali'=>self::taksitleri_getir($request,1,''),

            'taksitler_odenmemis'=>self::taksitleri_getir($request,2,''),

      ); 

        

    }

    public function masrafekleduzenle(Request $request)

    {

        $masraf = "";

        $guncelleme = false;

        if(isset($request->masraf_id))

        {

            $masraf = Masraflar::where('id',$request->masraf_id)->first();

            $guncelleme = true;





        }

        else

            $masraf = new Masraflar();

        $masraf->salon_id = $request->sube;

        $masraf->masraf_kategori_id = $request->masraf_kategorisi;

        $masraf->tarih = $request->tarih;

        $masraf->odeme_yontemi_id = $request->masraf_odeme_yontemi;

        $masraf->harcayan_id = $request->harcayan;

        $masraf->tutar= str_replace(['.',','],['','.'],$request->masraf_tutari);

        $masraf->aciklama = $request->masraf_aciklama;

        $masraf->notlar = $request->masraf_notlari;

        $masraf->save();



        $returntext = '';

        $butontext = '';



        if(!isset($request->masraf_sayfasi))

            $butontext = "<p><a href='/isletmeyonetim/masraflar?sube='".$request->sube." class='btn btn-primary btn-lg btn-block'>Masraf Listeme Git</a></p>";

        

        if($guncelleme)

            $returntext = 'Masraf başarıyla güncellendi';

        else

            $returntext = 'Masraf başarıyla kaydedildi';

        return array(

            'mesaj' => $returntext.$butontext,

            'masraflar' => self::masrafgetir($request),

            'kasa_raporu' =>self::kasa_raporu_getir($request,'')

        );



    }

    public function masraf_sil(Request $request)

    {

        Masraflar::where('id',$request->masraf_id)->delete();

        return array(

            'mesaj' => 'Masraf kaydı başarıyla kaldırıldı',

            'masraflar' => self::masrafgetir($request),

        );

    }

    public function masraf_detay(Request $request)

    {

        $masraf = Masraflar::where('id',$request->masraf_id)->first();

        return array(

            'id'=>$masraf->id,

            'masraf_kategori_id'=>$masraf->masraf_kategori_id,

            'harcayan_id'=>$masraf->harcayan_id,

            'tarih'=>$masraf->tarih,

            'aciklama'=>$masraf->aciklama,

            'tutar'=>$masraf->tutar,

            'notlar'=>$masraf->notlar,

            'odeme_yontemi_id'=>$masraf->odeme_yontemi_id,

            'salon_id'=>$masraf->salon_id,

        );



    }

    public function alacakekleduzenle(Request $request)

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

        $alacak->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

        $alacak->salon_id = self::mevcutsube($request);

        $alacak->user_id = $request->musteri;

        $alacak->save();

        return 'Bilgiler başarıyla kaydedildi';

    }

    public function bildirimkontrolet(Request $request)

    {   

        $personel = Personeller::where('salon_id',self::mevcutsube($request) )->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        $bildirimler = Bildirimler::where('personel_id',$personel)->where('salon_id',self::mevcutsube($request) )->orderBy('id','desc')->get();

        $html = "";

        foreach($bildirimler as $bildirim)

        {

            $html .= '<ul><li>

                

            <a href="'.$bildirim->url.'" name="bildirim" data-index-number="'.$bildirim->id.'" data-value="'.$bildirim->randevu_id.'">        

             <img src="'.$bildirim->img_src.'" alt="" class="mCS_img_loaded">                         

                                 ';

            if(!$bildirim->okundu)

                $html .= '<h3 style="background:#5C008E; padding: 5px; border-radius:5px; color:#fff"><b>';

            else

                $html .= "<h3>";

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

        $web = '';

        $uygulama = '';

        $salon = '';

        $tarih = array();

        if($request->ozeltarih != ''){

            $tariharaligi = $request->ozeltarih; 

            $tarih = explode(' / ',$tariharaligi);

        }

        else{

            $tariharaligi = $request->zaman;

            if($tariharaligi != '')

                $tarih = explode(' / ',$tariharaligi);

            else{

                $tarih[0] = '';

                $tarih[1] = '';

            }

        }

        if($request->olusturulma == "web")

            $web = true;

        if($request->olusturulma == "salon")

            $salon = true;

        if($request->olusturulma == "uygulama")

            $uygulama = true;

        

        return self::randevu_liste_getir($request,$tarih[0],$tarih[1],$salon,$web,$uygulama,$request->durum,$request->salon_id,'');

    }

    public function liste_deneme(Request $request)

    {



       return self::randevu_liste_getir($request,'2023-06-01','2023-06-31',true,null,null,1,$sube,'');

    }

    public function randevu_liste_getir(Request $request,$tarih1,$tarih2,$salon,$web,$uygulama,$durum,$salon_id,$userid)

    {

       

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

            $randevular = DB::table('randevu_hizmetler')->

        join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->

        join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->

        join('users','randevular.user_id','=','users.id')->

        leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->



        leftjoin('isletmeyetkilileri as y1','randevular.olusturan_personel_id','=','y1.id')->

       

        

        leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')->

        leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')->



        select(

                'users.name as musteri',

                 DB::raw('COALESCE(CONCAT(LEFT(REGEXP_REPLACE(users.cep_telefon,"[0-9]","X"),6), RIGHT(users.cep_telefon,4)),"") as telefon'),

                    //DB::raw("CASE WHEN randevu_hizmetler.personel_id is not null THEN CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') WHEN randevu_hizmetler.cihaz_id is not null THEN CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', cihazlar.cihaz_adi, ')') END as hizmetler"),

                 DB::raw("GROUP_CONCAT(hizmetler.hizmet_adi) as hizmetler"),

                    'odalar.oda_adi as odalar',

                    DB::raw('CONCAT("<span style=\"display:none\">",UNIX_TIMESTAMP(randevular.tarih),UNIX_TIMESTAMP(randevular.saat),"</span>",DATE_FORMAT(randevular.tarih, "%d.%m.%Y")) as tarih'),

                    DB::raw('DATE_FORMAT(randevular.saat, "%H:%i") as  saat'),

                    





                    //DB::raw("CASE WHEN randevular.durum=1 THEN CONCAT('Onaylı',CASE WHEN randevular.randevuya_geldi=true THEN ' - Geldi' WHEN randevular.randevuya_geldi=false THEN ' - Gelmedi' WHEN randevular.randevuya_geldi IS NULL THEN '' END) WHEN randevular.durum=0 then CONCAT('Beklemede') WHEN randevular.durum=3 THEN CONCAT('Müşteri Tarafından İptal') ELSE CONCAT('İptal') END AS durum"),

                    DB::raw('CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" ELSE "Gelmedi" END as geldimi'),



                    DB::raw("CASE WHEN randevular.durum=1 AND randevular.randevuya_geldi=true THEN '<button class=\'btn btn-success btn-block\' style=\'line-height:5px\'>Geldi</button>' WHEN randevular.durum=1 AND randevular.randevuya_geldi=0 THEN '<button class=\'btn btn-danger btn-block\' style=\'line-height:5px\'>Gelmedi</button>' WHEN randevular.durum=1 AND randevular.randevuya_geldi IS null THEN '<button class=\'btn btn-primary btn-block\' style=\'line-height:5px\'>Onaylı</button>' WHEN randevular.durum=2 THEN '<button class=\'btn btn-dark btn-block\' style=\'line-height:5px\'>İptal</button>' WHEN randevular.durum=3 THEN '<button class=\'btn btn-dark btn-block\'>Müşteri Tarafından İptal</button>' WHEN randevular.durum=0 THEN '<button class=\'btn btn-warning btn-block\' style=\'line-height:5px\'>Beklemede</button>' END AS durum"),

                    





                    DB::raw('CONCAT(COALESCE(SUM(randevu_hizmetler.fiyat),0) ," ₺") as toplam'),

                    DB::raw('CASE WHEN randevular.web=1 THEN "Web" WHEN randevular.uygulama=1 THEN "Uygulama" ELSE y1.name END as olusturan'),

                    DB::raw('DATE_FORMAT(randevular.created_at, "%d.%m.%Y %H:%i") as olusturulma'),

                   



                    DB::raw('CASE WHEN randevular.durum = 0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"   data-value=\"",randevular.id,"\"  name=\"randevu_duzenle\" href=\"#\"> <i class=\"fa fa-edit\"></i> Düzenle</a>

                    <a class=\"dropdown-item randevuonayla\" data-value=\"",randevular.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i> Onayla</a>

                  

                    <a class=\"dropdown-item randevuiptalet\" data-value=\"",randevular.id,"\"  href=\"#\"> <i class=\"fa fa-times\"></i> İptal Et</a>

                   </div></div>") 



                   WHEN randevular.durum = 1 AND randevular.randevuya_geldi IS NOT TRUE THEN 

                     CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"   data-value=\"",randevular.id,"\"  name=\"randevu_duzenle\" href=\"#\"> <i class=\"fa fa-edit\"></i> Düzenle</a>

                   

                  

                    <a class=\"dropdown-item randevuiptalet\" data-value=\"",randevular.id,"\"  href=\"#\"> <i class=\"fa fa-times\"></i> İptal Et</a>

                   </div></div>") WHEN randevular.durum = 1 AND randevular.randevuya_geldi IS  TRUE THEN 

                     CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

               

                   

                  

                    <a class=\"dropdown-item\" data-value=\"",randevular.id,"\" name=\"randevu_sonrasi_islem_notu\" href=\"#\"> <i class=\"fa fa-plus\"></i> İşlem Not Ekle</a>

                   </div></div>")

                   WHEN randevular.durum = 2 THEN ""

                   END



                    AS islemler')

                )->where('randevular.salon_id',$salon_id)->where('randevu_hizmetler.yardimci_personel','!=',true)->

            where(function($q) use ($tarih1,$tarih2) {if($tarih1 != '') $q->where('randevular.tarih','>=',$tarih1);if($tarih2 != '') $q->where('randevular.tarih','<=',$tarih2); })->

            

            where(function($q) use ($salon,$uygulama,$web){ if($salon!='') $q->where('randevular.salon', $salon); if($web!='') $q->orWhere('randevular.web',$web); if($uygulama!='') $q->orWhere('randevular.uygulama',$uygulama);})->

            where(function($q) use ($durum){if($durum!='') $q->where('randevular.durum',$durum);})->where(function($q) use($salon_id,$request){ if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) $q->where('randevu_hizmetler.personel_id',Personeller::where('salon_id',$salon_id)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id')); })->

            where(function($q) use ($userid){if($userid!='') $q->where('randevular.user_id',$userid);})->



        groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();

        else    

            $randevular = DB::table('randevu_hizmetler')->

        join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->

        join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->

        join('users','randevular.user_id','=','users.id')->

        leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->



        leftjoin('isletmeyetkilileri as y1','randevular.olusturan_personel_id','=','y1.id')->

         

        

        

        leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')->

        leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')->



        select(

            'randevu_hizmetler.yardimci_personel',

                'users.name as musteri',

                'users.cep_telefon as telefon',

                    //DB::raw("CASE WHEN randevu_hizmetler.personel_id is not null THEN CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') WHEN randevu_hizmetler.cihaz_id is not null THEN CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', cihazlar.cihaz_adi, ')') END as hizmetler"),

                DB::raw("GROUP_CONCAT(hizmetler.hizmet_adi) as hizmetler"),

                    'odalar.oda_adi as odalar',

                    DB::raw('CONCAT("<span style=\"display:none\">",UNIX_TIMESTAMP(randevular.tarih),UNIX_TIMESTAMP(randevular.saat),"</span>",DATE_FORMAT(randevular.tarih, "%d.%m.%Y")) as tarih'),

                    DB::raw('DATE_FORMAT(randevular.saat, "%H:%i") as  saat'),

                    





                    //DB::raw("CASE WHEN randevular.durum=1 THEN CONCAT('Onaylı',CASE WHEN randevular.randevuya_geldi=true THEN ' - Geldi' WHEN randevular.randevuya_geldi=false THEN ' - Gelmedi' WHEN randevular.randevuya_geldi IS NULL THEN '' END) WHEN randevular.durum=0 then CONCAT('Beklemede') WHEN randevular.durum=3 THEN CONCAT('Müşteri Tarafından İptal') ELSE CONCAT('İptal') END AS durum"),

                    DB::raw('CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" ELSE "Gelmedi" END as geldimi'),



                    DB::raw("CASE WHEN randevular.durum=1 AND randevular.randevuya_geldi=true THEN '<button class=\'btn btn-success btn-block\' style=\'line-height:5px\'>Geldi</button>' WHEN randevular.durum=1 AND randevular.randevuya_geldi=0 THEN '<button class=\'btn btn-danger btn-block\' style=\'line-height:5px\'>Gelmedi</button>' WHEN randevular.durum=1 AND randevular.randevuya_geldi IS null THEN '<button class=\'btn btn-primary btn-block\' style=\'line-height:5px\'>Onaylı</button>' WHEN randevular.durum=2 THEN '<button class=\'btn btn-dark btn-block\' style=\'line-height:5px\'>İptal</button>' WHEN randevular.durum=3 THEN '<button class=\'btn btn-dark btn-block\'>Müşteri Tarafından İptal</button>' WHEN randevular.durum=0 THEN '<button class=\'btn btn-warning btn-block\' style=\'line-height:5px\'>Beklemede</button>' END AS durum"),

                    





                    DB::raw('CONCAT(COALESCE(SUM(randevu_hizmetler.fiyat),0) ," ₺") as toplam'),

                    DB::raw('CASE WHEN randevular.web=1 THEN "Web" WHEN randevular.uygulama=1 THEN "Uygulama" ELSE y1.name END as olusturan'),

                    DB::raw('DATE_FORMAT(randevular.created_at, "%d.%m.%Y %H:%i") as olusturulma'),

                   



                    DB::raw('CASE WHEN randevular.durum = 0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  data-value=\"",randevular.id,"\"  name=\"randevu_duzenle\" href=\"#\"> <i class=\"fa fa-edit\"></i> Düzenle</a>

                    <a class=\"dropdown-item randevuonayla\" data-value=\"",randevular.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i> Onayla</a>

                  

                    <a class=\"dropdown-item randevuiptalet\" data-value=\"",randevular.id,"\"  href=\"#\"> <i class=\"fa fa-times\"></i> İptal Et</a>

                   </div></div>") 



                   WHEN randevular.durum = 1 AND randevular.randevuya_geldi IS NOT TRUE THEN 

                     CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"   data-value=\"",randevular.id,"\"  name=\"randevu_duzenle\" href=\"#\"> <i class=\"fa fa-edit\"></i> Düzenle</a>

                   

                  

                    <a class=\"dropdown-item randevuiptalet\" data-value=\"",randevular.id,"\"  href=\"#\"> <i class=\"fa fa-times\"></i> İptal Et</a>

                   </div></div>") 

                   WHEN randevular.durum = 1 AND randevular.randevuya_geldi IS  TRUE THEN 

                     CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

               

                   

                  

                    <a class=\"dropdown-item\" data-value=\"",randevular.id,"\" name=\"randevu_sonrasi_islem_notu\" href=\"#\"> <i class=\"fa fa-plus\"></i> İşlem Not Ekle</a>

                   </div></div>")

                   WHEN randevular.durum = 2 THEN ""

                   END



                    AS islemler')

                )->where('randevular.salon_id',$salon_id)->

            where(function($q) use ($tarih1,$tarih2) {if($tarih1 != '') $q->where('randevular.tarih','>=',$tarih1);if($tarih2 != '') $q->where('randevular.tarih','<=',$tarih2); })->

            

            where(function($q) use ($salon,$uygulama,$web){ if($salon!='') $q->where('randevular.salon', $salon); if($web!='') $q->orWhere('randevular.web',$web); if($uygulama!='') $q->orWhere('randevular.uygulama',$uygulama);})->

            where(function($q) use ($durum){if($durum!='') $q->where('randevular.durum',$durum);})->where(function($q) use($salon_id,$request){ if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) $q->where('randevu_hizmetler.personel_id',Personeller::where('salon_id',$salon_id)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id')); })->

            where(function($q) use ($userid){if($userid!='') $q->where('randevular.user_id',$userid);})->



        groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->get();

        



        return $randevular;

    }

    public function senetler(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }



        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $senetler = self::senetleri_getir($request,'','');

        $senetler_acik = self::senetleri_getir($request,0,'');

        $senetler_kapali = self::senetleri_getir($request,1,'');

        $senetler_odenmemis = self::senetleri_getir($request,2,'');

         return view('isletmeadmin.senetler',['pageindex'=>17,'sayfa_baslik'=>'Senet Takibi','bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true,$request),'isletme'=>$isletme,'senetler'=>$senetler,'senetler_acik'=>$senetler_acik,'senetler_kapali'=>$senetler_kapali,'senetler_odenmemis'=>$senetler_odenmemis, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function senetfiltre(Request $request)

    {

        return self::senetleri_getir($request,$request->sorgu,'');

    }

    public function senetleri_getir(Request $request,$sorgu,$musteriid)

    {

        if($sorgu===''){

            return DB::table('senetler')->join('senet_vadeleri','senet_vadeleri.senet_id','=','senetler.id')->join('users','senetler.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from senet_vadeleri where senet_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

            DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(senet_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id),"</button>" ) END as odenmemis'),



           DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),

           DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE  DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    )END as yaklasan_vade_tarihi'),



                 DB::raw('CONCAT("<a href=\"#\" name=\"senet_vadeleri\"  data-value=\"",senetler.id,"\" class=\"btn btn-primary\">Detaylar</a>

                <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",senetler.user_id, "/?sube=", senetler.salon_id   ,"\" data-value=\"",senetler.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\"></i></a>") as islemler')



            )->where('senetler.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('senetler.user_id',$musteriid);})->groupBy('senet_vadeleri.senet_id')->get();

            exit;

        }

        if($sorgu===0)

        {

            return  DB::table('senetler')->join('senet_vadeleri','senet_vadeleri.senet_id','=','senetler.id')->join('users','senetler.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from senet_vadeleri where senet_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

            DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(senet_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id),"</button>" ) END as odenmemis'),



            DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),



            DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ) END as yaklasan_vade_tarihi'),



                 DB::raw('CONCAT("<a href=\"#\" name=\"senet_vadeleri\"  data-value=\"",senetler.id,"\" class=\"btn btn-primary\">Detaylar</a>

                <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",senetler.user_id, "/?sube=", senetler.salon_id  ,"\"   data-value=\"",senetler.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\"></i></a>") as islemler')



            )->where('senetler.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('senetler.user_id',$musteriid);})->having('yaklasan_vade','!=','<button class="btn btn-warning">Kapalı Senet</button>')->groupBy('senet_vadeleri.senet_id')->get();

            exit;

        }

        if($sorgu===1)

        {

            return  DB::table('senetler')->join('senet_vadeleri','senet_vadeleri.senet_id','=','senetler.id')->join('users','senetler.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from senet_vadeleri where senet_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

           DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(senet_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id),"</button>" ) END as odenmemis'),



            DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),

             DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE  DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    )  END as yaklasan_vade'),



                 DB::raw('CONCAT("<a href=\"#\" name=\"senet_vadeleri\"  data-value=\"",senetler.id,"\" class=\"btn btn-primary\">Detaylar</a>

                <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",senetler.user_id, "/?sube=", senetler.salon_id ,"\"  data-value=\"",senetler.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\"></i></a>") as islemler')



            )->where('senetler.salon_id',self::mevcutsube($request))->having('yaklasan_vade','=','<button class="btn btn-warning">Kapalı Senet</button>')->groupBy('senet_vadeleri.senet_id')->get();

            exit;

        }

        if($sorgu===2)

        {

            return  DB::table('senetler')->join('senet_vadeleri','senet_vadeleri.senet_id','=','senetler.id')->join('users','senetler.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from senet_vadeleri where senet_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

            DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(senet_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = true AND senet_vadeleri.senet_id = senetler.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM senet_vadeleri

                                WHERE senet_vadeleri.odendi = false  AND senet_vadeleri.senet_id = senetler.id),"</button>" ) END as odenmemis'),



            DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),



             DB::raw('CASE WHEN (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE DATE_FORMAT(

                        (SELECT senet_vadeleri.vade_tarih FROM senet_vadeleri WHERE senet_vadeleri.odendi=false AND senet_vadeleri.senet_id = senetler.id

                                order by senet_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ) END as yaklasan_vade_tarihi'),



            DB::raw('CONCAT("<a href=\"#\" name=\"senet_vadeleri\"  data-value=\"",senetler.id,"\" class=\"btn btn-primary\">Detaylar</a>

                <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",senetler.user_id, "/?sube=", senetler.salon_id ,"\" data-value=\"",senetler.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\"></i></a>") as islemler')



            )->where('senetler.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('senetler.user_id',$musteriid);})->where('senet_vadeleri.odendi','<',true)->where('senet_vadeleri.vade_tarih','<=',date('Y-m-d'))->groupBy('senet_vadeleri.senet_id')->get();

            exit;

        }

        

    }

    public function senetvadegetir(Request $request)

    {



        $vadeler = SenetVadeleri::where('senet_id',$request->id)->get();

        $html = '';

        foreach($vadeler as $key => $vade)

        {

            $html .= ' <label id="vadelercheck"  style="width:100%;height:60px; font-size:18px;"

                        



                           class="list-group-item list-group-item-action" data-value="'.$vade->id.'"

                           >  <input id="vadecheck" type="checkbox" name=>  Vade '.++$key .' : <b>'.number_format($vade->tutar,2,',','.').' ₺</b>';

            if($vade->odendi != true && $vade->notlar != null)

                $html.=' ('.$vade->notlar.')';

            $html.='<input type="hidden" name="vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

            if($vade->odendi==true){

                $html .= '<button type="button"  data-value="'.$vade->id.'"   data-toggle="modal" data-target="#senet_onay_modal"   name="senet_vadesi"  class="btn btn-success" style="float:right">Ödendi ';

                if($vade->odeme_turu !== null) $html. '('.$vade->odeme_turu->odeme_yontemi.')';

                $html .= '</button>';

            }

            else{

                if(date('Y-m-d') > $vade->vade_tarih)

                    $html .= '<button type="button"   data-value="'.$vade->id.'"  data-toggle="modal" data-target="#senet_onay_modal"  data-value="'.$vade->id.'" name="senet_vadesi"  class="btn btn-danger" style="float:right">Ödenmedi</button>';

                else

                    $html .= '<button type="button"   data-value="'.$vade->id.'"  data-toggle="modal" data-target="#senet_onay_modal"  name="senet_vadesi"  class="btn btn-primary" style="float:right">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</button>';

            }

            $html .= '</label>';

        }

        if($vadeler->count() == 0)

        {

            $html .= '<label id="vadelercheck"  style="color:#ff0000;width:100%;height:60px; font-size:18px;" 

                           class="list-group-item list-group-item-action;text-align:center">Oluşturulmuş bir senet bulunamadı!</label>';

        }

        return $html;

    }

    public function senetvadegetir_tahsilat(Request $request)

    {



        $vadeler = SenetVadeleri::join('senetler','senet_vadeleri.senet_id','=','senetler.id')->leftJoin('odeme_yontemleri','senet_vadeleri.odeme_yontemi_id','=','odeme_yontemleri.id')->where('senetler.user_id',$request->musteriid)->select('senetler.id as senet_id','senet_vadeleri.id as id','senet_vadeleri.vade_tarih as vade_tarih','senet_vadeleri.odendi as odendi','senet_vadeleri.notlar as notlar','odeme_yontemleri.odeme_yontemi as odeme_yontemi','senet_vadeleri.tutar as tutar')->where('senetler.salon_id',$request->sube)->where('senet_vadeleri.odendi',false)->get();

        $html = '';

        foreach($vadeler as $key => $vade)

        {

             

                $html .= ' <label for="senetvadecheck'.$key.'" style="width:100%;height:60px; font-size:18px;" class="list-group-item list-group-item-action" data-value="'.$vade->id.'"

                           >  <input type="hidden" name="senet_vade_id[]" data-value="'.$vade->id.'" value="'.$vade->id.'">



                           <input id="senetvadecheck'.$key.'"';



                if(date('Y-m-d') >= $vade->vade_tarih)

                    $html .= ' checked ';

                $html .= 'type="checkbox" name="senetvadeler[]" data-value="'.$vade->id.'">  Vade '.++$key .' : <b><span name="vade_tutar[]" data-value="'.$vade->id.'">'.number_format($vade->tutar,2,',','.').'</span> ₺</b>';

                if($vade->odendi != true && $vade->notlar != null)

                    $html.=' ('.$vade->notlar.')';

                $html.='<input type="hidden" name="senet_vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

                if($vade->odendi==true)

                    $html .= '<button type="button"     data-toggle="modal" data-target="#senet_onay_modal"   name="senet_vadesi"  class="btn btn-success" style="float:right">Ödendi ('.$vade->odeme_turu->odeme_yontemi.')</button>';

                else{

                    if(date('Y-m-d') > $vade->vade_tarih)

                        $html .= '<button type="button"    data-toggle="modal" data-target="#senet_onay_modal"  name="senet_vadesi"  class="btn btn-danger" style="float:right">Ödenmedi</button>';

                    else

                        $html .= '<button type="button"    data-toggle="modal" data-target="#senet_onay_modal"  name="senet_vadesi"  class="btn btn-primary" style="float:right">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</button>';

                }

                $html .= '</label>';

             

            

        }

        if($vadeler->count() == 0)

        {

            $html .= '<label id="vadelercheck"  style="color:#ff0000;width:100%;height:60px; font-size:18px;" 

                           class="list-group-item list-group-item-action;text-align:center">Oluşturulmuş ve devam eden bir senet vadesi bulunamadı!</label>';

        }

        return $html;

    }

    public function taksitvadegetir(Request $request)

    {



        $vadeler = TaksitVadeleri::where('taksitli_tahsilat_id',$request->id)->get();

        $html = '';

        foreach($vadeler as $key => $vade)

        {

            $html .= ' <a style="width:100%"

                           data-toggle="modal" data-target="#taksit_onay_modal" 

                           class="list-group-item list-group-item-action" name="taksit_vadesi"  data-value="'.$vade->id.'"

                           >Vade '.++$key .' : <b>'.number_format($vade->tutar,2,',','.').' ₺</b>';

            if($vade->odendi != true && $vade->notlar != null)

                $html.=' ('.$vade->notlar.')';

            $html.='<input type="hidden" name="vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

            if($vade->odendi==true)

                $html .= '<button type="button" class="btn btn-success" style="float:right">Ödendi ('.$vade->odeme_turu->odeme_yontemi.')</button>';

            else{

                if(date('Y-m-d') > $vade->vade_tarih)

                    $html .= '<button type="button" class="btn btn-danger" style="float:right">Ödenmedi</button>';

                else

                    $html .= '<button type="button"  class="btn btn-primary" style="float:right">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</button>';

            }

            $html .= '</a>';

        }

         if($vadeler->count() == 0)

        {

            $html .= '<label id="vadelercheck"  style="color:#ff0000;width:100%;height:60px; font-size:18px;" 

                           class="list-group-item list-group-item-action;text-align:center">Oluşturulmuş taksit bulunamadı!</label>';

        }

        return $html;

    }

     public function taksitvadegetir_tahsilat(Request $request)

    {



        $vadeler = TaksitVadeleri::join('taksitli_tahsilatlar','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->leftJoin('odeme_yontemleri','taksit_vadeleri.odeme_yontemi_id','=','odeme_yontemleri.id')->where('taksitli_tahsilatlar.user_id',$request->musteriid)->select('taksitli_tahsilatlar.id as taksitli_tahsilat_id','taksit_vadeleri.id as id','taksit_vadeleri.vade_tarih as vade_tarih','taksit_vadeleri.odendi as odendi','taksit_vadeleri.notlar as notlar','odeme_yontemleri.odeme_yontemi as odeme_yontemi','taksit_vadeleri.tutar as tutar')->where('taksitli_tahsilatlar.salon_id',$request->sube)->where('taksit_vadeleri.odendi',false)->get();



        $html = '';



        foreach($vadeler as $key => $vade)

        {

            

                $html .= ' <label for="taksitvadecheck'.$key.'" style="width:100%;height:60px; font-size:18px;" class="list-group-item list-group-item-action" data-value="'.$vade->id.'"

                               >  <input id="taksitvadecheck'.$key.'" type="checkbox"';

                if(date('Y-m-d') >= $vade->vade_tarih)

                    $html .= ' checked ';

                $html .= ' name="taksitvadeler[]" data-value="'.$vade->id.'">  Vade '.++$key .' : <b><span name="vade_tutar[]" data-value="'.$vade->id.'">'.number_format($vade->tutar,2,',','.').'</span> ₺</b>';

                if($vade->odendi != true && $vade->notlar != null)

                    $html.=' ('.$vade->notlar.')';

                $html.='<input type="hidden" name="taksit_vade_id[]" data-value="'.$vade->id.'" value="'.$vade->id.'"><input type="hidden" name="taksit_vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

                if($vade->odendi==true)

                    $html .= '<button type="button" class="btn btn-success" style="float:right">Ödendi ('.$vade->odeme_yontemi.')</button>';

                else{

                    if(date('Y-m-d') > $vade->vade_tarih)

                        $html .= '<a name="taksit_vadesi" data-toggle="modal" data-target="#taksit_onay_modal" data-value="'.$vade->id.'" class="btn btn-danger" style="float:right;color:#fff">Ödenmedi</a>';

                    else

                        $html .= '<a name="taksit_vadesi" data-toggle="modal" data-target="#taksit_onay_modal" data-value="'.$vade->id.'" class="btn btn-primary" style="float:right;color:#fff">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</a>';

                }

                $html .= '</label>';

            

        }

        if($vadeler->count() == 0)

        {

            $html .= '<label id="vadelercheck"  style="color:#ff0000;width:100%;height:60px; font-size:18px;" 

                           class="list-group-item list-group-item-action;text-align:center">Oluşturulmuş ve devam eden taksit vadesi bulunamadı!</label>';

        }

        return $html;

    }

    public function download(Request $request)

    {

         // retreive all records from db

      // share data to view

      $senet = Senetler::where('id',$request->senetid)->first();

      

      $pdf = PDF::loadView('sample', [

                'title' => date('Y-m-d-H-i-s'),

                'senet' => $senet

              



                

        ]); 

        return $pdf->download(date('Y-m-d-H-i-s').'.pdf');

           

    }



    public function QRdownload(Request $request)

    {

        // retreive all records from db

        // share data to view

        $isletme=Salonlar::where('id',self::mevcutsube($request))->first();

        $pdf = PDF::loadView('qrCode', [

            'title' => date('Y-m-d-H-i-s'),

            'isletme'=>$isletme 

        ]);

    

        return $pdf->download(date('Y-m-d-H-i-s').'.pdf');

    }

    public function profilbilgileri(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

         if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        return view('isletmeadmin.profilbilgileri',['pageindex'=>19,'sayfa_baslik'=>'Profil Bilgileri','bildirimler'=>self::bildirimgetir($request),'paketler'=> self::paket_liste_getir('',true,$request),'isletme'=>$isletme, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function QrCodeController(){

         return view('qrcode');

    }

    public function yeniisletmeekle(Request $request)

    {

        $mevcutisletme = Salonlar::where('id',self::mevcutsube($request))->first(); 
        $isletme = new Salonlar(); 
        $isletme->salon_adi = $request->firma_adi; 
        $isletme->telefon_1 = $request->telefon; 
        $isletme->sms_apikey = $mevcutisletme->sms_apikey; 
        $isletme->sms_baslik = $mevcutisletme->sms_baslik; 
        $isletme->il_id = $request->firma_il;
        $isletme->ilce_id = $request->firma_ilce;
        $isletme->aktif = false; 
        $isletme->save();
        if(isset($request->yonetici_ekle))
        {
            $yetkili = '';
            if(IsletmeYetkilileri::where('gsm1',$request->telefon)->count()>0)
                $yetkili = IsletmeYetkilileri::where('gsm1',$request->telefon)->first();
            else
                $yetkili = new IsletmeYetkilileri();
           
            $yetkili->name = $request->yetkili_adi; 

            $yetkili->gsm1 = $request->telefon;
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890'); 
            $olusturulansifre = substr($random, 0, 6); 
            
            $yetkili->password = Hash::make($olusturulansifre);
            
            $yetkili->save();
            DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values (3, "App\\\IsletmeYetkilileri",'.$yetkili->id.','.$isletme->id.')');
            YetkiliOlunanSubeler::create(

                ['salon_id'=> $isletme->id,'yetkili_id'=>$yetkili->id]
            ); 
            $personel = new Personeller();
            $personel->personel_adi = $request->yetkili_adi; 
            $personel->cep_telefon = $request->telefon;
            $personel->role_id = 3;
            $personel->renk = 2;
            $personel->takvimde_gorunsun = true;
            $personel->takvim_sirasi = 2;
            $personel->aktif = true;
            $personel->role_id = 3;
            $personel->save();

        }
        YetkiliOlunanSubeler::create(

            ['salon_id'=> $isletme->id, 'yetkili_id'=> Auth::guard('isletmeyonetim')->user()->id]
        ); 
        DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values (1, "App\\\IsletmeYetkilileri",'.Auth::guard('isletmeyonetim')->user()->id.','.$isletme->id.')');
        $personel = new Personeller();
        $personel->personel_adi = Auth::guard('isletmeyonetim')->user()->name; 
        $personel->cep_telefon = Auth::guard('isletmeyonetim')->user()->gsm1;
        $personel->role_id = 1;
        $personel->renk = 1;
        $personel->takvimde_gorunsun = true;
        $personel->takvim_sirasi = 1;
        $personel->aktif = true;
        $personel->role_id = 1;
        $personel->save(); 

        return array(

            'mesaj' => 'Şube bilgileri başarıyla kaydedildi. Yönlendiriliyorsunuz...',

            'sube' => $isletme->id

        );



    }

    public function odeme_sayfasi(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {
            var_dump($isletmeler);
            var_dump($isletme);

           // return view('isletmeadmin.yetkisizerisim',['isletme'=>$isletme,'isletmeler']);

           exit(0);

        } 

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0) 

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $urunler = self::urun_liste_getir($request,"");

        $hizmetler = self::hizmet_liste_getir($request,"","");

        $paketler = self::paket_liste_getir("",false,$request);

        $bildirimler = self::bildirimgetir($request);

        $musteri_bilgileri = Auth::user();

        $uyelik = Uyelik::where('id',$request->uyelikturu)->first(); 
         
        return view('isletmeadmin.odeme',['sayfa_baslik'=>'Ödeme','urunler'=>$urunler,'hizmetler'=>$hizmetler,'paketler'=>$paketler,'pageindex'=>70,'musteri_bilgileri'=>$musteri_bilgileri,'isletme'=>$isletme,'bildirimler'=>$bildirimler, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'request'=>$request,

            'uyelik'=>$uyelik,

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);



    }

    public function randevuayarguncelle(Request $request)

    {

        $isletme = Salonlar::where('id',$request->salon_id)->first();

        $isletme->randevu_saat_araligi=$request->randevu_saat_araligi;

        $isletme->randevu_takvim_turu = $request->randevu_takvim_turu;

        

        $isletme->save();

        return 'Randevu ayarları başarıyla kaydedildi';

    }

   public function etkinlikekleduzenle(Request $request)

    {

        $etkinlik = "";

        if(isset($request->etkinlik_id))

            $etkinlik = Etkinlikler::where('id',$request->etkinlik_id)->first();

        else

            $etkinlik = new Etkinlikler();

        $etkinlik->etkinlik_adi = $request->etkinlik_adi;

        $etkinlik->tarih_saat = $request->etkinlik_tarihi ." ".$request->etkinlik_saati;

        $etkinlik->fiyat = $request->etkinlik_fiyati;

        $etkinlik->salon_id = self::mevcutsube($request);

        $etkinlik->aktifmi=1;

        $etkinlik->mesaj=$request->etkinlik_sms;

        $etkinlik->save();

        $katilimcilar = EtkinlikKatilimcilari::where('id',$etkinlik->id)->delete();



         $gsm = array();

        $mesajlar=array();



        if (isset($request->etkinlik_katilimci_musteriler)) {

            foreach($request->etkinlik_katilimci_musteriler as $key=>$katilimci)

            {

                $yenikatilimci = new EtkinlikKatilimcilari();

                $yenikatilimci->etkinlik_id = $etkinlik->id;

                $yenikatilimci->user_id = $katilimci;

                $yenikatilimci->save();

                $toplumusteri = User::where('id',$katilimci)->first();

                $katilim_link = ''; 

                if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$etkinlik->salon_id)->value('musteri')==1 )

                 

                    $katilim_link = ' Katılım için : https://'.$_SERVER['HTTP_HOST'].'/etkinlikkatilim/'.$etkinlik->id.'/'.$toplumusteri->id;

                if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$etkinlik->salon_id)->value('kara_liste')!=1)

                    array_push($mesajlar, array("to"=>$toplumusteri->cep_telefon,"message"=> $request->etkinlik_sms.$katilim_link));

            }

        }

        if (isset($request->etkinlik_grup_katilimci_musteriler)) {

            foreach($request->etkinlik_grup_katilimci_musteriler as $grupkatilimci)

            {

         

                $grupkatilimcilar=GrupSmsKatilimcilari::where('grup_id',$grupkatilimci)->get();

                foreach ($grupkatilimcilar as $grup) {

                    $yenikatilimci = new EtkinlikKatilimcilari();

                    $yenikatilimci->etkinlik_id = $etkinlik->id;

                    $yenikatilimci->user_id = $grup->user_id;

                    $yenikatilimci->save();

                    $toplumusteri = User::where('id',$grup->user_id)->first(); 

                    $katilim_link = ''; 

                    if (SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$etkinlik->salon_id)->value('musteri')==1)

                        $katilim_link = ' Katılım için : https://'.$_SERVER['HTTP_HOST'].'/etkinlikkatilim/'.$etkinlik->id.'/'.$toplumusteri->id;

                    if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$etkinlik->salon_id)->value('kara_liste')!=1)

                        array_push($mesajlar, array("to"=>$toplumusteri->cep_telefon,"message"=> $request->etkinlik_sms.$katilim_link));

                }

            

            

            }

       }

        $gonder=self::sms_gonder($request,$mesajlar,true,6,false);

      

        return array(



          "mesaj" => "Etklinlik başarıyla kaydedildi",

           "gonder"=>$gonder,

           "katilimci"=>self::etkinlikyukle($request)



        );





    }

   public function etkinlikbeklenensms(Request $request){



      $etkinlikbeklenen= Etkinlikler::where('id',$request->etkinlikid)->first();

      $mesajlar=array();

 

       foreach ($etkinlikbeklenen->katilimcilar as  $katilimci) {

        if($katilimci->durum===null)

        {

            $katilim_link = ''; 

            if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$etkinlikbeklenen->salon_id)->value('musteri')==1)

                $katilim_link = ' Katılım için : https://'.$_SERVER['HTTP_HOST'].'/etkinlikkatilim/'.$etkinlikbeklenen->id.'/'.$katilimci->user_id;

            if(MusteriPortfoy::where('user_id',$katilimci->user_id)->where('salon_id',$etkinlikbeklenen->salon_id)->value('kara_liste')!=1)

                array_push($mesajlar, array("to"=>$katilimci->musteri->cep_telefon,"message"=> $etkinlikbeklenen->mesaj.$katilim_link));

        }

        

      }









        $gonder=self::sms_gonder($request,$mesajlar,true,6,false);

      

        return array(



          "mesaj" => "SMS başarıyla gönderildi",

          "gonder"=>$gonder,



        );

    }

    public function hizmetpersonelsecimigetir(Request $request){

        $secilen_hizmetler = '';

        if(isset($request->sunulanhizmetid))

        {

            $mevcuthizmet = SalonHizmetler::where('id',$request->sunulanhizmetid)->first();

            $secilen_hizmetler = Hizmetler::where('id',$mevcuthizmet->hizmet_id)->get();

        }

        else{

            $secilen_hizmetler = Hizmetler::whereIn('id',$request->salon_hizmetleri)->get();

        }

       

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $personeller = Personeller::where('salon_id',$isletme->id)->where('aktif',1)->get();

        $cihazlar = Cihazlar::where('salon_id',$isletme->id)->where('aktifmi',1)->get();

        $html = '';

        foreach($secilen_hizmetler as $secilen_hizmet)

        {

            $html.= ' <div class="row" style="margin-bottom:20px">

                        <div class="col-3 col-xs-3 col-sm-3">

                        <div class="form-group">



                        <input type="hidden" name="hizmet_idler[]" value="'.$secilen_hizmet->id.'">';

            if($secilen_hizmet->ozel_hizmet == true)

                $html .= '<label>Hizmet adı</label><input class="form-control" type="text" name="ozel_hizmet_adi" value="'.$secilen_hizmet->hizmet_adi.'">';

            else

                $html .= $secilen_hizmet->hizmet_adi;

            $html .= '

                        </div></div>

                        <div class="col-5 col-xs-5 col-sm-5">

                            <div class="form-group"><label>Personel(-ler)</label>

                             <select class="custom-select2 form-control" required

                      multiple="multiple"

                      style="width: 100%"

                      data-style="btn-outline-primary" name="hizmet_personelleri_'.$secilen_hizmet->id.'[]"

                    >';

            foreach($personeller as $personel)

            {

                if(PersonelHizmetler::where('hizmet_id',$secilen_hizmet->id)->where('personel_id',$personel->id)->count()>0)

                     $html .= '<option selected value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

                 else

                     $html .= '<option value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

               

            }

            foreach($cihazlar as $cihaz)

            {

                if(CihazHizmetler::where('hizmet_id',$secilen_hizmet->id)->where('cihaz_id',$cihaz->id)->count()>0)

                     $html .= '<option selected value="cihaz-'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                 else

                     $html .= '<option value="cihaz-'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

            }

            $html .= '</select></div>

                        </div>

                        <div class="col-2 col-xs-2 col-sm-2">

                        <div class="form-group">

                        <label>Süre(dk)</label>

                        <input type="tel" class="form-control" name="hizmet_sure_'.$secilen_hizmet->id.'" required value="';



            if(SalonHizmetler::where('hizmet_id',$secilen_hizmet->id)->where('salon_id',$request->sube)->value('sure_dk')!==null)

                $html .= SalonHizmetler::where('hizmet_id',$secilen_hizmet->id)->where('salon_id',$request->sube)->value('sure_dk');

            else

                $html .= Hizmetler::where('id',$secilen_hizmet->id)->value('sure_dk');



            $html .= '" placeholder="Süre(dk)"></div></div>

                         <div class="col-2 col-xs-2 col-sm-2">

                         <div class="form-group"><label>Fiyat(₺)</label>

                         <input type="tel" class="form-control" name="hizmet_fiyat_'.$secilen_hizmet->id.'" required placeholder="Fiyat" value="';

             

            if(SalonHizmetler::where('hizmet_id',$secilen_hizmet->id)->where('salon_id',$request->sube)->value('baslangic_fiyat')!==null)

                $html .= SalonHizmetler::where('hizmet_id',$secilen_hizmet->id)->where('salon_id',$request->sube)->value('baslangic_fiyat');





            $html.='"></div></div>';

              

            $html.='</div>';

        }

        $html .= '<div class="row"><div class="col-md-12">

                       <button type="submit" id="hizmetleri_kaydet" class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>Kaydet </button></div></div>';

        return $html;

    }

    public function hizmetekleduzenle(Request $request){

        foreach($request->hizmet_idler as $hizmet_id){

            $salon_hizmet = '';

            if(isset($request->ozel_hizmet_adi)){

                $hizmet = Hizmetler::where('id',$hizmet_id)->first();

                $hizmet->hizmet_adi = $request->ozel_hizmet_adi;

                $hizmet->save();

            }

            

            if(SalonHizmetler::where('hizmet_id',$hizmet_id)->where('salon_id',$request->sube)->count()>0)

                $salon_hizmet = SalonHizmetler::where('hizmet_id',$hizmet_id)->first();

            else

                $salon_hizmet = new SalonHizmetler();

            $salon_hizmet->salon_id = $request->sube;



            $salon_hizmet->hizmet_id = $hizmet_id;

            $salon_hizmet->baslangic_fiyat= $request->{"hizmet_fiyat_{$hizmet_id}"};

            $salon_hizmet->sure_dk= $request->{"hizmet_sure_{$hizmet_id}"};

            $salon_hizmet->aktif = true;

            $salon_hizmet->bolum = 0;

            if(isset($request->ozel_hizmet_kategorisi))

                 $salon_hizmet->hizmet_kategori_id = $request->ozel_hizmet_kategorisi;

            else

                $salon_hizmet->hizmet_kategori_id = Hizmetler::where('id',$hizmet_id)->value('hizmet_kategori_id');



            $salon_hizmet->save();

            

            if(SalonHizmetKategoriRenkleri::where('hizmet_kategori_id',$salon_hizmet->hizmet_kategori_id)->where('salon_id',$request->sube)->count() == 0)

            {



                $kategori_son_renk = SalonHizmetKategoriRenkleri::where('salon_id',$request->sube)->orderBy('renk_id','desc')->first();

                $yeni_kategori_renk = '';

                 if($kategori_son_renk===null)

                    $yeni_kategori_renk = 1;

                else

                {

                    if($kategori_son_renk->renk_id == 10)

                        $yeni_kategori_renk = 1;

                    else

                        $yeni_kategori_renk = $kategori_son_renk->renk_id + 1;

                }

                $yeni_renk = new SalonHizmetKategoriRenkleri();

                $yeni_renk->salon_id = $request->sube;

                $yeni_renk->renk_id = $yeni_kategori_renk;

                $yeni_renk->hizmet_kategori_id = Hizmetler::where('id',$hizmet_id)->value('hizmet_kategori_id');

                $yeni_renk->save();



            }

            

            PersonelHizmetler::where('hizmet_id',$hizmet_id)->delete();

            foreach($request->{"hizmet_personelleri_{$hizmet_id}"} as $personel_id){



                $personelhizmet = new PersonelHizmetler();

                $personelhizmet->personel_id = $personel_id;

                $personelhizmet->hizmet_id = $hizmet_id;

                $personelhizmet->save();

            }

               



        }

        $secilmeyenhizmetler = self::secilmeyen_hizmet_liste_getir($request);

        return self::hizmet_liste_getir($request,"Hizmet(-ler) başarıyla eklendi",$secilmeyenhizmetler);

        

    }

    public function hizmetkategorirenkekle($request,$hizmet_id)

    {

       

        return null;

    }

    public function sistemeyenihizmetekle(Request $request){ 

        $yenihizmet = new Hizmetler();

        $yenihizmet->hizmet_kategori_id = $request->hizmet_kategorisi;

        $yenihizmet->hizmet_adi = $request->hizmet_adi;

        $yenihizmet->ozel_hizmet = true;

        $yenihizmet->salon_id = $request->sube;

        $yenihizmet->cinsiyet = $request->cinsiyet;

        $yenihizmet->sure_dk = $request->hizmet_sure;

        $yenihizmet->save();

        $sunulanhizmet = new SalonHizmetler();

        $sunulanhizmet->hizmet_id = $yenihizmet->id;

        $sunulanhizmet->hizmet_kategori_id = $request->hizmet_kategorisi;

        $sunulanhizmet->bolum = $request->cinsiyet;

        $sunulanhizmet->baslangic_fiyat = $request->hizmet_fiyati;

        $sunulanhizmet->aktif = true;

        $sunulanhizmet->sure_dk = $request->hizmet_sure;

        $sunulanhizmet->salon_id = $request->sube;

        $sunulanhizmet->save();

         

        if(SalonHizmetKategoriRenkleri::where('hizmet_kategori_id',$request->hizmet_kategorisi)->where('salon_id',$request->sube)->count() == 0)

        {

                $kategori_son_renk = SalonHizmetKategoriRenkleri::where('salon_id',$request->sube)->orderBy('renk_id','desc')->first();

                $yeni_kategori_renk = '';

                if($kategori_son_renk===null)

                    $yeni_kategori_renk = 1;

                else

                {

                    if($kategori_son_renk->renk_id == 10)

                        $yeni_kategori_renk = 1;

                    else

                        $yeni_kategori_renk = $kategori_son_renk->renk_id + 1;

                }

                

                $yeni_renk = new SalonHizmetKategoriRenkleri();

                $yeni_renk->salon_id = $request->sube;

                $yeni_renk->renk_id = $yeni_kategori_renk;

                $yeni_renk->hizmet_kategori_id = $request->hizmet_kategorisi;

                $yeni_renk->save();



        }

        if(is_array($request->personeller))

        {

            foreach($request->personeller as $personel){



                if(str_contains($personel,'cihaz')){

                    $str = explode('-',$personel);

                    $personelhizmet = new CihazHizmetler();

                    $personelhizmet->cihaz_id = $str[1];

                    $personelhizmet->hizmet_id = $yenihizmet->id;

                    $personelhizmet->save();

                }

                else

                {

                    $personelhizmet = new PersonelHizmetler();

                    $personelhizmet->personel_id = $personel;

                    $personelhizmet->hizmet_id = $yenihizmet->id;

                    $personelhizmet->save();

                }

               

            }

        }

       

        $secilmeyenhizmetler = self::secilmeyen_hizmet_liste_getir($request);

        return self::hizmet_liste_getir($request,"Hizmet başarıyla eklendi",$secilmeyenhizmetler); 



    }

    public function secilmeyen_hizmet_liste_getir(Request $request)

    {

        $liste = '';

        foreach(Hizmet_Kategorisi::all() as $hizmet_kategorisi){



            if(Hizmetler::whereNotIn('id',\App\SalonHizmetler::where('salon_id',self::mevcutsube($request))->where('aktif',true)->pluck('hizmet_id'))->where('hizmet_kategori_id',$hizmet_kategorisi->id)->where('id','!=',463)->count()>0){

                $liste.='<tr style="background: #e2e2e2;">

                            <td> </td>

                                          <td> 

                                             <strong>'.$hizmet_kategorisi->hizmet_kategorisi_adi.'</strong>

                                          </td></tr>';

                foreach(Hizmetler::whereNotIn('id',\App\SalonHizmetler::where('salon_id',self::mevcutsube($request))->where('aktif',true)->pluck('hizmet_id'))->where('hizmet_kategori_id',$hizmet_kategorisi->id)->where('id','!=',463)->get() as $secilmeyenhizmetler)

                    $liste.='<tr>

                                          <td><input type="checkbox" name="salon_hizmetleri[]" value="'.$secilmeyenhizmetler->id.'"></td>

                                          <td>'.$secilmeyenhizmetler->hizmet_adi.'</td>

                    </tr>';

                             



                                    

            }                      

                               

        }

        return $liste;

    }

    public function takvim_degistir(Request $request)

    {

        $tarih1 = date('Y-m-d');

        $tarih2 = date('Y-m-d');

        $tarih=str_replace('T',' ',$request->takvimtarih);

        if($request->takvimgorunum=='agendaDay')

        {

            $tarih1 = date('Y-m-d',strtotime($tarih));

            $tarih2 = date('Y-m-d',strtotime($tarih));

        }

        if($request->takvimgorunum=='agendaWeek')

        {

            $tarih1 = date('Y-m-d',strtotime($tarih));

            $tarih2 = date('Y-m-d',strtotime('+7 days',strtotime($tarih)));

        }

        if($request->takvimgorunum=='month')

        {

            $tarih1 = date('Y-m-d',strtotime($tarih));

            $tarih2 = date('Y-m-d',strtotime('+1 month',strtotime($tarih)));

        }

        return self::randevuyukle($request,$request->ayar,$tarih1,$tarih2);

    }

    public function sistemeyenihizmetkategorisiekle(Request $request)

    {

        $kategori = '';

        if($request->hizmet_kategori_id!= '')

            $kategori = Hizmet_Kategorisi::where('id',$request->hzimet_kategori_id)->first();

        else

            $kategori = new Hizmet_Kategorisi();

        $kategori->salon_id = $request->sube;

        $kategori->ozel_kategori = true;

        $kategori->hizmet_kategorisi_adi = $request->hizmet_kategorisi;

        $kategori->save();

        $option_olarak_eklenecek = DB::table('hizmet_kategorisi')->select('id as id','hizmet_kategorisi_adi as text')->where('id',$kategori->id)->get();

        return array(

            'sonuc'=>'Hizmet kategorisi başarıyla kaydedildi',

            'kategori'=>$option_olarak_eklenecek

        );

    }

    public function etkinlikler(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 3)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

           return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

       $paketler = self::paket_liste_getir('',true,$request);

       $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

       $katilimci= self::etkinlikyukle($request);

        return view('isletmeadmin.etkinlikler',['paketler'=>$paketler,'pageindex'=>20,'sayfa_baslik'=>'Etkinlikler','isletme'=>$isletme,'bildirimler'=>self::bildirimgetir($request),'katilimci'=>$katilimci, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);



    }

     public function etkinlikyukle(Request $request)

    {

        return DB::table('etkinlik_katilimcilari')->join('etkinlikler','etkinlik_katilimcilari.etkinlik_id','=','etkinlikler.id')

       ->select(

          DB::raw('COUNT(etkinlik_katilimcilari.etkinlik_id) as katilimci_sayisi'),

          DB::raw('DATE_FORMAT(etkinlikler.tarih_saat,"%d.%m.%Y %H:%i") as tarih'),

          'etkinlikler.etkinlik_adi as etkinlik_adi',

          'etkinlikler.fiyat as fiyat',

         DB::raw('CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\" href=\"#\"  data-toggle=\"modal\"  name=\"etkinlik_detay\"data-target=\"#etkinlik_detay_modal\" data-value=\"",etkinlikler.id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>

                    <a class=\"dropdown-item\" href=\"#\" name=\"etkinlik_sil\" data-value=\"",etkinlikler.id,"\"><i class=\"dw dw-delete-3\"></i>Sil</a></div></div>") AS islemler')

        )

       ->where('etkinlikler.salon_id',self::mevcutsube($request))

       ->where('etkinlikler.aktifmi',true)

       ->groupBy('etkinlik_katilimcilari.etkinlik_id')->get();



    }

    public function etkinlikdetay(Request $request)

    {

      $katilimcilar=DB::table('etkinlik_katilimcilari')->join('users','etkinlik_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon', DB::raw('CASE WHEN etkinlik_katilimcilari.durum IS NULL THEN "Bekleniyor" WHEN etkinlik_katilimcilari.durum=0 THEN "Katılmıyor" WHEN etkinlik_katilimcilari.durum=1 THEN "Katılıyor" END as durum'),

        DB::raw('CONCAT("<input type=\"checkbox\" name=\"sec\" value=\"",users.id,"\">",users.name) AS sms_liste')



    )->where('etkinlik_katilimcilari.etkinlik_id',$request->etkinlikid)->get();

      $katilimcilar_katilanlar=DB::table('etkinlik_katilimcilari')->join('users','etkinlik_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('etkinlik_katilimcilari.etkinlik_id',$request->etkinlikid)->where('etkinlik_katilimcilari.durum',true)->get();

       $katilimcilar_katilmayanlar=DB::table('etkinlik_katilimcilari')->join('users','etkinlik_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('etkinlik_katilimcilari.etkinlik_id',$request->etkinlikid)->where('etkinlik_katilimcilari.durum',false)->where('etkinlik_katilimcilari.durum','!=',null)->get();

      $katilimcilar_beklenen=DB::table('etkinlik_katilimcilari')->join('users','etkinlik_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('etkinlik_katilimcilari.etkinlik_id',$request->etkinlikid)->where('etkinlik_katilimcilari.durum',null)->get();



      $etkinlik=DB::table('etkinlik_katilimcilari')->join('etkinlikler','etkinlik_katilimcilari.etkinlik_id','=','etkinlikler.id')

       ->select(

          DB::raw('COUNT(etkinlik_katilimcilari.etkinlik_id) as katilimci_sayisi'),

          DB::raw('DATE_FORMAT(etkinlikler.tarih_saat,"%d.%m.%Y %H:%i") as tarih'),

          'etkinlikler.etkinlik_adi as etkinlik_adi',

          'etkinlikler.fiyat as fiyat',)

         

       

       ->groupBy('etkinlik_katilimcilari.etkinlik_id')->where('etkinlikler.id',$request->etkinlikid)->first();

    return array(

      'katilimcilar'=>$katilimcilar,

      'etkinlik'=>$etkinlik,

      'katilimcilar_katilanlar'=>$katilimcilar_katilanlar,

      'katilimcilar_katilmayanlar'=>$katilimcilar_katilmayanlar,

      'katilimcilar_beklenen'=>$katilimcilar_beklenen,

      'etkinlikid'=>$request->etkinlikid,

      'beklenen_count'=>$katilimcilar_beklenen->count()

    );

    

    }

     public function cihazekleduzenle(Request $request){

        $cihazlar = new Cihazlar();

        $returntext="";

        $cihazlar->salon_id = $request->sube;

        $cihazlar->cihaz_adi = $request->cihaz_adi;

        $cihazlar->aktifmi = true;

        $cihazlar->durum = true;

        $cihazlar->save();

        $cihazrenk = new SalonCihazRenkleri();

        $kategori_son_renk = SalonCihazRenkleri::where('salon_id',$request->sube)->orderBy('id','desc')->first();

        $yeni_kategori_renk = '';

        if($kategori_son_renk === null)

        {

             $yeni_kategori_renk = 1;

        }

        else

        {

            if($kategori_son_renk->renk_id == 10)

                $yeni_kategori_renk = 1;

            else

                $yeni_kategori_renk = $kategori_son_renk->renk_id + 1;

        }

       

        $yeni_renk = new SalonCihazRenkleri();

        $yeni_renk->salon_id = $request->sube;

        $yeni_renk->renk_id = $yeni_kategori_renk;

        $yeni_renk->cihaz_id = $cihazlar->id;

        $yeni_renk->save();

        

        for($i=1;$i<=7;$i++){ 

                    $cihazcalismasaatleri = new CihazCalismaSaatleri();

                    $cihazcalismasaatleri->haftanin_gunu = $i;

                    $cihazcalismasaatleri->cihaz_id = $cihazlar->id;

                    if(isset($_POST['calisiyor'.$i])){ 

                        $cihazcalismasaatleri->calisiyor = 1; 

                    }

                    else{

                        $cihazcalismasaatleri->calisiyor = 0;

                    }

                        

                     if($i==1){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati1;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati1;

                        }

                         if($i==2){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati2;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati2;

                        }

                         if($i==3){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati3;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati3;

                        }

                         if($i==4){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati4;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati4;

                        }

                         if($i==5){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati5;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati5;

                        }

                         if($i==6){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati6;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati6;

                        }

                         if($i==7){

                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati7;

                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati7;

                        }

                        $cihazcalismasaatleri->save();

                    

                }

                for($i=1;$i<=7;$i++){

                    

                    $cihazmolasaatleri = new CihazMolaSaatleri();

                    $cihazmolasaatleri->haftanin_gunu = $i;

                    $cihazmolasaatleri->cihaz_id = $cihazlar->id;

                    if(isset($_POST['molavar'.$i])){



                        $cihazmolasaatleri->mola_var = 1;

                       

                       

                    }

                    else{

                        $cihazmolasaatleri->mola_var = 0;

                    }

                        

                     if($i==1){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati1;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati1;

                        }

                         if($i==2){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati2;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati2;

                        }

                         if($i==3){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati3;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati3;

                        }

                         if($i==4){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati4;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati4;

                        }

                         if($i==5){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati5;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati5;

                        }

                         if($i==6){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati6;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati6;

                        }

                         if($i==7){

                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati7;

                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati7;

                        }

                        $cihazmolasaatleri->save();

                    

        }

            

        return self::cihaz_liste_getir($request,$returntext); 









    }

    public function odaekleduzenle(Request $request){

        $odalar = new Odalar();

        $returntext="";

        $odalar->salon_id = $request->sube;

        $odalar->oda_adi = $request->oda_adi;

        $odalar->aktifmi = true;

        $odalar->durum = true;

        $odalar->save();

        

        $kategori_son_renk = OdaRenkleri::where('salon_id',$request->sube)->orderBy('id','desc')->first();

        $yeni_kategori_renk = '';

        if($kategori_son_renk === null)

        {

            $yeni_kategori_renk = 1;

        }

        else

        {

            if($kategori_son_renk->renk_id == 10)

                $yeni_kategori_renk = 1;

            else

                $yeni_kategori_renk = $kategori_son_renk->renk_id + 1;

        }

       

        $yeni_renk = new OdaRenkleri();

        $yeni_renk->salon_id = $request->sube;

        $yeni_renk->renk_id = $yeni_kategori_renk;

        $yeni_renk->oda_id = $odalar->id;

        $yeni_renk->save();

        return self::oda_liste_getir($request,$returntext); 









    }

    public function odamusaitisaretle(Request $request){

       $oda = Odalar::where('id',$request->oda_id)->first();

        $oda->durum = $request->durum;

          $oda->aciklama=null;

        $oda->save();

        return self::oda_liste_getir($request,'Durum güncellendi');

    }

    public function odamusaitdegilisaretle(Request $request){

       $oda = Odalar::where('id',$request->oda_id)->first();

        $oda->durum = $request->durum;

       $oda->aciklama=$request->aciklama;

        $oda->save();

        return self::oda_liste_getir($request,'Durum güncellendi');

    }

    public function cihazmusaitisaretle(Request $request){

       $cihaz = Cihazlar::where('id',$request->cihaz_id)->first();

        $cihaz->durum = $request->durum;

        $cihaz->aciklama=null;

        $cihaz->save();

        return self::cihaz_liste_getir($request,'Durum güncellendi');

    }

    public function cihazmusaitdegilisaretle(Request $request){

       $cihaz = Cihazlar::where('id',$request->cihaz_id)->first();

        $cihaz->durum = $request->durum;

       $cihaz->aciklama=$request->aciklama;

        $cihaz->save();

        return self::cihaz_liste_getir($request,'Durum güncellendi');

    }

    public function oda_liste_getir(Request $request,$returntext){

      

      $odalar= DB::table('odalar')->select('odalar.oda_adi as oda_adi',

        DB::raw('CASE WHEN odalar.durum = 0 THEN "Müsait Değil" ELSE  "Müsait" END as durum'),'odalar.aciklama as oda_aciklama',

        DB::raw('CONCAT("<div class=\"dropdown\"><a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i></a><div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\"><a name=\"oda_musait_isaretle\" data-value=\"",odalar.id,"\" class=\"dropdown-item\" href=\"#\"

                                       ><i class=\"icon-copy fa fa-check\" aria-hidden=\"true\"></i>Müsait</a

                                       >

                                    <a name=\"oda_musaitdegil_isaretle\" data-value=\"",odalar.id,"\" class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#oda_duzenle_modal\"

                                       ><i class=\"icon-copy fa fa-close\" aria-hidden=\"true\"></i>Müsait Değil</a

                                       >

                                   

                                    <a class=\"dropdown-item\" href=\"#\"  name=\"oda_sil\" data-value=\"",odalar.id,"\"

                                       ><i class=\"dw dw-delete-3\"></i> Sil</a

                                       ></div></div>") AS islemler')

    )->where('odalar.salon_id',$request->sube)->where('odalar.aktifmi',true)->get();

      return array(

        'sonuc'=>$returntext,

        'odalar'=>$odalar

      );

    }

    public function cihaz_liste_getir(Request $request,$returntext){

      

      $cihazlar= DB::table('cihazlar')->select('cihazlar.cihaz_adi as cihaz_adi',

         DB::raw('CASE WHEN cihazlar.durum = 0 THEN "Müsait Değil" ELSE  "Müsait" END as durum'),'cihazlar.aciklama as cihaz_aciklama',

        DB::raw('CONCAT("<div class=\"dropdown\"><a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i></a><div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\"><a name=\"cihaz_musait_isaretle\" data-value=\"",cihazlar.id,"\" class=\"dropdown-item\" href=\"#\"

                                       ><i class=\"icon-copy fa fa-check\" aria-hidden=\"true\"></i>Müsait</a

                                       >

                                    <a name=\"cihaz_musaitdegil_isaretle\" data-value=\"",cihazlar.id,"\" class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"

                  data-target=\"#cihaz_duzenle_modal\"

                                       ><i class=\"icon-copy fa fa-close\" aria-hidden=\"true\"></i>Müsait Değil</a

                                       >

                                   

                                    <a class=\"dropdown-item\" href=\"#\"  name=\"cihaz_sil\" data-value=\"",cihazlar.id,"\"

                                       ><i class=\"dw dw-delete-3\"></i> Sil</a

                                       ></div></div>") AS islemler')

    )->where('cihazlar.salon_id',$request->sube)->where('aktifmi',true)->get(); 

      return array(

        'sonuc'=>$returntext,

        'cihazlar'=>$cihazlar);

    }

    public function oda_sil(Request $request){

        Odalar::where('id',$request->oda_id)->update(['aktifmi'=>false]);

        OdaRenkleri::where('oda_id',$request->oda_id)->delete();

        return self::oda_liste_getir($request,"Oda başarıyla kaldırıldı");



    }

    public function cihaz_sil(Request $request){

        Cihazlar::where('id',$request->cihaz_id)->update(['aktifmi'=>false]);

        SalonCihazRenkleri::where('cihaz_id',$request->cihaz_id)->delete();

        return self::cihaz_liste_getir($request,"Cihaz başarıyla kaldırıldı");



    }

    public function randevu_dogrulama_kodu_gonder(Request $request)

    {

        $randevu = Randevular::where('id',$request->randevuid)->first();

        $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);

        $randevu->dogrulama = $kod;

        $randevu->save();

        $mesaj = array(

            array("to"=>$randevu->users->cep_telefon,"message"=>"Doğrulama kodunuz : ".$kod),



        );

        var_dump( self::sms_gonder($request,$mesaj,false,1,true));

        return '';



    }

    public function adisyonhizmetguncelle(Request $request)

    {



        $hizmet = AdisyonHizmetler::where('id',$request->hizmet_id)->first();

        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',Adisyonlar::where('id',$hizmet->adisyon_id)->value('salon_id'))->where('ayar_id',16)->value('musteri');



        if(($dogrulama_kodu_ayari && $hizmet->dogrulama_kodu == $request->dogrulama_kodu) || (!$dogrulama_kodu_ayari || $request->geldimi!==1)) 

        {

            $hizmet->geldi = $request->geldimi;

            $hizmet->save();

            return self::adisyon_hizmet_getir($hizmet->id);

            exit;

        }

        else

        {

            return 'Doğrulama kodu hatalı, lütfen yeniden deneyiniz.';

            exit;

        }

        

    }

    public function adisyonhizmetpersonelguncelle(Request $request)

    {



        $hizmet = AdisyonHizmetler::where('id',$request->hizmet_id)->first();

         

        $hizmet->personel_id = $request->personel_id;

        $hizmet->save();

        return self::adisyon_hizmet_getir($hizmet->id);

            

        

    }

    public function adisyonhizmethizmetguncelle(Request $request)

    {



        $hizmet = AdisyonHizmetler::where('id',$request->hizmet_id)->first();

         

        $hizmet->hizmet_id = $request->adisyon_hizmet_id;

        $hizmet->save();

        return self::adisyon_hizmet_getir($hizmet->id); 

    }

    public function adisyonhizmetfiyatguncelle(Request $request)

    {



        $hizmet = AdisyonHizmetler::where('id',$request->hizmet_id)->first();

        $hizmet->fiyat = $request->tutar;

        $hizmet->save();

        $adisyon = Adisyonlar::where('id',$hizmet->adisyon_id)->first();

         

        



        return array(

            'hizmet_liste'=>self::adisyon_hizmet_getir($hizmet->id),

            'adisyon_detay'=>self::adisyon_detay($request,$adisyon,false,''), 

        ); 

    }

    public function hizmetdogrulamakodugonder(Request $request)

    {

        $hizmet = AdisyonHizmetler::where('id',$request->hizmet_id)->first();

        $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);

        $hizmet->dogrulama_kodu = $kod;

        $hizmet->save();

        $musteri = User::where('id',Adisyonlar::where('id',$hizmet->adisyon_id)->value('user_id'))->first();

        $mesaj = array(

            array("to"=>$musteri->cep_telefon,"message"=>"Doğrulama kodunuz : ".$kod),



        );

        var_dump( self::sms_gonder($request,$mesaj,false,1,true));

        return '';



    }



    public function seans_dogrulama_kodu_gonder(Request $request)

    {

        $seans = AdisyonPaketSeanslar::where('id',$request->seans_id)->first();

        $adisyon_paket = AdisyonPaketler::where('id',$seans->adisyon_paket_id)->first();

        $random = str_shuffle('1234567890');



        $kod = substr($random, 0, 4);

        $seans->dogrulama_kodu = $kod;

        $seans->save();

        if(Randevular::where('seans_id',$seans->id)->count()==1)

        {

            $randevu = Randevular::where('seans_id',$seans->id)->first();

            $randevu->dogrulama = $kod;

            $randevu->save();

        }

        

        $mesaj = array(

            array("to"=>$adisyon_paket->adisyon->musteri->cep_telefon,"message"=>"Doğrulama kodunuz : ".$kod),



        );

        var_dump( self::sms_gonder($request,$mesaj,false,1,true));

        return '';

    }

    public function randevugeldiisaretle(Request $request)
    {
        Log::info('Geldi işaretle başladı');

        // REQUEST'TEN GELEN DEĞERLERİ AL
        $dogrulamaSoruldu = $request->dogrulamaSoruldu ?? false;
        $dogrulamaSorulduGonderilecek = $request->dogrulamaSorulduGonderilecek ?? false;

        $randevu = Randevular::where('id', $request->randevuid)->first();
        $seansVar = AdisyonPaketSeanslar::where('randevu_id', $request->randevuid)->count();
        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id', $randevu->salon_id)
            ->where('ayar_id', 16)->value('musteri');
        $mesaj = "";
        $mesajlar = array();

        // ============================================================
        // ADIM 1: DOĞRULAMA AKIŞI (sadece seanslı randevularda)
        // ============================================================
        if($seansVar > 0 && $dogrulama_kodu_ayari)
        {
            // 1a: Henüz sorulmadıysa → "Doğrulama gönderilsin mi?" diye sor
            if(!$dogrulamaSoruldu)
            {
                Log::info('dogrulamaSorulsun dönecek');
                return array(
                    'dogrulamaSorulsun' => 1,
                    'hatamesaj' => 'Müşteriye doğrulama kodu gönderilsin mi?'
                );
            }

            // 1b: Kullanıcı "Gönder" dediyse → kod gönder ve doğrula
            if($dogrulamaSorulduGonderilecek)
            {
                // Kod henüz girilmediyse → SMS gönder ve kod iste
                if(empty($request->dogrulama_kodu))
                {
                    if(!$randevu->dogrulama_sms_gonderildi)
                    {
                        self::randevu_dogrulama_kodu_gonder($request);
                    }
                    return array(
                        'dogrulamaGerekli' => 1,
                        'hatamesaj' => 'Lütfen doğrulama kodunu giriniz'
                    );
                }

                // Kod girilmiş → doğrula (yeni SMS göndermeden!)
                if($randevu->dogrulama != $request->dogrulama_kodu)
                {
                    return array(
                        'dogrulamaGerekli' => 1,
                        'hatamesaj' => 'Doğrulama kodu hatalı, lütfen yeniden deneyiniz'
                    );
                }
                Log::info('Doğrulama kodu doğrulandı');
            }
        }

        // ============================================================
        // ADIM 2: KVKK ONAY KONTROLÜ
        // ============================================================
        $kvkkOnayiAktif = SalonSMSAyarlari::where('salon_id', $request->sube)
            ->where('ayar_id', 22)->value('musteri');

        $portfoy = MusteriPortfoy::where('user_id', $randevu->user_id)
            ->where('salon_id', $randevu->salon_id)->first();

        if($kvkkOnayiAktif && $portfoy && !$portfoy->kvkk_onay_alindi)
        {
            $olusturulanOnayKodu = $portfoy->onay_kodu;

            // 2a: KVKK kodu henüz istenmemişse → SMS gönder ve iste
            if(!$request->isKvkkProcess)
            {
                $onayMesaji = "Sn. ".$randevu->users->name.", ".$olusturulanOnayKodu." numarali onay kodunu ilgiliye soyleyerek, ".$randevu->salonlar->kvkk_link." kvkk linkinde yer alan KVKK Aydinlatma Metni'ne gore kisisel verilerinizin islenmesine ve sistemlerimizde kayitli iletisim adresleriniz uzerinden SMS, e-posta ve aramalar vasitasiyla ticari elektronik ileti gonderimine izin vermis olacaksiniz";
                Log::info('KVKK SMS gönderilecek');
                self::sms_gonder_bildirimli($request, array(array(
                    "to" => $randevu->users->cep_telefon,
                    "message" => $onayMesaji
                )), false, 1, false);

                return array(
                    'detailtext' => '',
                    'title' => 'Uyarı',
                    'mesaj' => '<p>Müşteri bilgilerini sisteme kaydetmeden önce verilerinin işlenmesine ve kayıtlı iletişim adresleri üzerinden SMS, e-posta ve aramalar vasıtasıyla ticari elektronik ileti gönderimine izin verdiğine dair cep telefonuna gönderilen onay kodunu girmeniz gerekmektedir.<p>',
                    'onayGerekli' => true,
                    'status' => 'warning',
                    'showCloseButton' => false,
                    'showCancelButton' => true,
                    'showConfirmButton' => true,
                );
            }

            // 2b: KVKK kodu girilmişse → doğrula
            if($request->isKvkkProcess)
            {
                if(empty($request->kvkkOnayKodu) || $request->kvkkOnayKodu != $olusturulanOnayKodu)
                {
                    Log::info('KVKK yanlış girildi tekrar uyarı');
                    return array(
                        'detailtext' => '',
                        'title' => 'Uyarı',
                        'mesaj' => '<p>Girilen onay kodu hatalı. Lütfen müşteriye gönderilen doğru onay kodunu giriniz.<p>',
                        'onayGerekli' => true,
                        'status' => 'warning',
                        'showCloseButton' => false,
                        'showCancelButton' => true,
                        'showConfirmButton' => true,
                    );
                }

                $portfoy->kvkk_onay_alindi = true;
                $portfoy->save();
                Log::info('KVKK onayı alındı');
            }
        }

        // ============================================================
        // ADIM 3: GELDİ OLARAK İŞARETLE
        // ============================================================
        $randevu->randevuya_geldi = true;
        $randevu->save();

        $seanslar = AdisyonPaketSeanslar::where('randevu_id', $randevu->id);
        foreach($seanslar->get() as $seans)
        {
            $seans->geldi = true;
            $seans->save();
        }

        return array('mesaj' => 'Başarılı', 'geldiIsaretlendi' => true);
    }



    public function randevutahsilet(Request $request)

    {

        $randevu = Randevular::where('id',$request->randevuid)->first();

        //$randevu->randevuya_geldi = true;

        $adisyonvar = false;

        $adisyon = '';

        foreach($randevu->hizmetler as $hizmet)

        {

            $hizmetlernonexp = explode('+',$hizmet->hizmetler->hizmet_adi);

            foreach($hizmetlernonexp as $hizmetlerexp)

            {

                $adisyon_var = DB::table('adisyonlar')

                ->join('adisyon_paketler','adisyonlar.id','=','adisyon_paketler.adisyon_id')

                ->join('adisyon_paket_seanslar','adisyon_paketler.id','=','adisyon_paket_seanslar.adisyon_paket_id')

                ->join('paketler','adisyon_paketler.paket_id','=','paketler.id')

                ->join('paket_hizmetler','paketler.id','=','paket_hizmetler.paket_id')

                ->join('hizmetler','paket_hizmetler.hizmet_id','=','hizmetler.id')

                ->select('adisyonlar.id as adisyon_id',DB::raw('(SELECT COUNT(*) from adisyon_paket_seanslar where adisyon_paket_seanslar.geldi is null and adisyon_paket_seanslar.adisyon_paket_id = adisyon_paketler.id) as gelinmeyen_seans_sayisi'))

                ->where('adisyonlar.salon_id',$randevu->salon_id)

                ->where(function($q) use($hizmetlerexp){

                        if(!str_contains($hizmetlerexp,'Tüm Vücut'))

                        {

                            $q->where('hizmetler.hizmet_adi','like','%'.$hizmetlerexp.'%');

                        }

                    } 

                )->where('adisyonlar.user_id',$randevu->user_id)->having(DB::raw('gelinmeyen_seans_sayisi') ,'>',0)->first();

                

                if($adisyon_var && !$adisyonvar)

                {

                    $adisyon = Adisyonlar::where('id',$adisyon_var->adisyon_id)->first();

                    $adisyonvar = true;

                    break;

                }

            }



        }

        if(AdisyonPaketSeanslar::where('id',$randevu->seans_id)->count()==1)

        {

            $seans = AdisyonPaketSeanslar::where('id',$randevu->seans_id)->first();

            $paket = AdisyonPaketler::where('id',$seans->adisyon_paket_id)->first();

            $adisyon = $paket->adisyon;

            $adisyonvar = true;

        }

        /*$dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',16)->value('musteri');

        if(($dogrulama_kodu_ayari && $randevu->dogrulama == $request->dogrulama_kodu) || !$dogrulama_kodu_ayari)

        {*/

            $adisyon_id = '';

            if(!$adisyonvar)

                $adisyon_id = self::yeni_adisyon_olustur($randevu->user_id,$randevu->salon_id,date('d.m.Y',strtotime($randevu->tarih)).' tarihli randevuda alınan hizmetlerin ödemesi',date('Y-m-d'));

            else

                $adisyon_id=$adisyon->id;

            

            //$randevu->save();

            foreach($randevu->hizmetler as $hizmet)

                if(!$adisyonvar){



                    

                    self::adisyon_hizmet_ekle($adisyon_id,$hizmet->hizmet_id,$randevu->tarih,$hizmet->saat,$hizmet->sure_dk,$hizmet->fiyat,true,$hizmet->personel_id,$hizmet->cihaz_id,null,null);

                }

            return $randevu->user_id;

            exit;

        /*}

        else

        {

            return 'Doğrulama kodu hatalı, lütfen yeniden deneyiniz';

            exit;

        }*/



    }

    public function yeni_adisyon(Request $request)

    {

        $adisyon_id = self::yeni_adisyon_olustur($request->musteri,$request->sube,$request->adisyon_not,$request->adisyon_tarihi);

        if(isset($request->adisyon_hizmet_id)){

            foreach($request->adisyon_hizmet_id as $key => $hizmet){

                self::adisyon_hizmet_ekle($adisyon_id,$hizmet,$request->adisyon_hizmet_tarih[$key],$request->adisyon_hizmet_saat[$key],$request->adisyon_hizmet_sure[$key],$request->adisyon_hizmet_fiyat[$key],false,$request->adisyon_hizmet_personel[$key],$request->adisyon_hizmet_personel[$key],null,null); 

            }

        }

        if(isset($request->urun_id_adisyon)){

            foreach($request->urun_id_adisyon as $key => $urun)

            {

                $adisyon_urun = new AdisyonUrunler();

                $adisyon_urun->islem_tarihi = $request->adisyon_tarihi;

                $adisyon_urun->adisyon_id= $adisyon_id;

                $adisyon_urun->urun_id = $urun;

                $adisyon_urun->adet = $request->urun_adet_adisyon[$key];

                $adisyon_urun->fiyat = $request->urun_fiyat_adisyon[$key];

                $adisyon_urun->personel_id = $request->urun_satan_adisyon[$key];

                $adisyon_urun->save();

           

            }

        }

        if(isset($request->paket_id_adisyon))

        {

            foreach($request->paket_id_adisyon as $key=>$paket){

                $adisyon_paket_id = self::adisyona_paket_ekle($adisyon_id,$paket,$request->paket_fiyat_adisyon[$key],$request->paket_baslangic_tarihi_adisyon[$key],$request->sens_aralik_gun_adisyon[$key],$request->paket_satan_adisyon[$key],null,null);

                $seanstarih = $request->paket_baslangic_tarihi_adisyon[$key];

                $paket = Paketler::where('id',$paket)->first();

                $toplam_seans_sayilari = $paket->hizmetler->sum('seans');



           

                for($i=1;$i<=$toplam_seans_sayilari;$i++)

                {

                        if($i>1)

                            $seanstarih = date('Y-m-d',strtotime('+'.$request->paket_seans_aralik_gun_adisyon[$key].' days',strtotime($seanstarih)));

                        $seans = new AdisyonPaketSeanslar();

                        $seans->adisyon_paket_id = $adisyon_paket_id;

                        $seans->seans_tarih = $seanstarih;

                        $seans->save();

                }

            } 

        }

        $personelid = '';

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

            $pesonelid=Auth::guard('isletmeyonetim')->user()->id;

        return array(

            'tum_adisyonlar'=>self::adisyon_yukle($request,'','','1970-01-01 00:00:00',date('Y-m-d 23:59:59'),'',$personelid),

            'hizmet_adisyonlar' => self::adisyon_yukle($request,1,'','1970-01-01 00:00:00',date('Y-m-d 23:59:59'),'',$personelid),

            'urun_adisyonlar' => self::adisyon_yukle($request,3,'','1970-01-01 00:00:00',date('Y-m-d 23:59:59'),'',$personelid),

            'paket_adisyonlar' => self::adisyon_yukle($request,2,'','1970-01-01 00:00:00',date('Y-m-d 23:59:59'),'',$personelid),

            'user_id'=>$request->musteri

        );

        



    }



    public function yeni_adisyon_olustur($musteriid,$salonid,$adisyonnotu,$tarih)

    {

        $adisyon = new Adisyonlar();

        $adisyon->user_id = $musteriid;

        $adisyon->salon_id =  $salonid;

        $adisyon->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

        $adisyon->tarih = $tarih;

        $adisyon->save();

        return $adisyon->id;

    }

    public function adisyon_hizmet_ekle($adisyon_id,$hizmet_id,$islem_tarihi,$islem_saati,$sure,$fiyat,$geldi,$personel_id,$cihaz_id,$senet_id,$taksitli_tahsilat_id)

    {

        $cihazid=null;

            if(str_contains($cihaz_id,'cihaz'))

            {

                $str = explode('-',$cihaz_id);

                $cihazid = $str[1];

                

            }

            else{



                $cihazid=$cihaz_id;

             

            }

        $adisyon_hizmet = new AdisyonHizmetler();

        $adisyon_hizmet->adisyon_id = $adisyon_id;

        $adisyon_hizmet->personel_id = $personel_id;

        $adisyon_hizmet->cihaz_id = $cihazid;

        $adisyon_hizmet->geldi = $geldi;

        $adisyon_hizmet->hizmet_id = $hizmet_id;

        $adisyon_hizmet->islem_tarihi = $islem_tarihi;

        $adisyon_hizmet->islem_saati = $islem_saati;

        $adisyon_hizmet->sure = $sure;

        $adisyon_hizmet->fiyat = $fiyat;

        $adisyon_hizmet->senet_id = $senet_id;

        $adisyon_hizmet->taksitli_tahsilat_id = $taksitli_tahsilat_id;

        $adisyon_hizmet->save();

        return $adisyon_hizmet->id;

    }

    public function adisyona_paket_ekle($adisyon_id,$paket_id,$fiyat,$baslangic_tarihi,$seans_araligi,$personel_id,$senet_id,$taksitli_tahsilat_id){

        $adisyon_paket = new AdisyonPaketler();

        $adisyon_paket->adisyon_id = $adisyon_id;

        $adisyon_paket->paket_id = $paket_id;

        $adisyon_paket->fiyat = $fiyat;

        $adisyon_paket->baslangic_tarihi = $baslangic_tarihi;

        $adisyon_paket->seans_araligi = $seans_araligi;

        $adisyon_paket->personel_id = $personel_id;

        $adisyon_paket->senet_id = $senet_id;

        $adisyon_paket->taksitli_tahsilat_id = $taksitli_tahsilat_id;

        $adisyon_paket->save();

        return $adisyon_paket->id;

    }

    public function seansgirdiguncelle(Request $request)

    {

        $seans = AdisyonPaketSeanslar::where('id',$request->id)->first();

        $adisyon_id = AdisyonPaketler::where('id',$seans->adisyon_paket_id)->value('adisyon_id');



        $salon_id = Adisyonlar::where('id',$adisyon_id)->value('salon_id');

        if(isset($request->tarih))

            $seans->seans_tarih = $request->tarih;

        if(isset($request->hizmet))

            $seans->hizmet_id = $request->hizmet;       

        if(isset($request->personelcihaz)){

            if(str_contains($request->personelcihaz,'cihaz'))

            {

                $str = explode('-',$request->personelcihaz);

                $seans->cihaz_id = $str[1];

                $seans->personel_id = null;

            }

            else{



                $seans->personel_id = $request->personelcihaz;

                $seans->cihaz_id = null;

             

            }

        }

        if(isset($request->geldi)){

            $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$salon_id)->where('ayar_id',16)->value('musteri');

        

            if(($dogrulama_kodu_ayari &&$request->geldi==1 && $request->dogrulama_kodu == $seans->dogrulama_kodu)||(!$dogrulama_kodu_ayari || $request->geldi!==1)){

                if($request->geldi == 2)

                    $seans->geldi = null;

                else



                    $seans->geldi = $request->geldi;



            }

            else{

                if($request->dogrulama_kodu != $seans->dogrulama_kodu){

                    return 'hatalikod';

                    exit;

                }

                

            }



        }

        if(isset($request->oda))

            $seans->oda_id = $request->oda;

        $seans->save();

       

        if($request->seans_sayfa=='true'){

            return self::guncellemesonrasiseansdetaygetir($request,$seans->adisyon_paket_id,$request->musteri_id); 

            exit;

        }

        

        else

        {



            return self::adisyon_paket_satis_getir($adisyon_id,true,$seans->adisyon_paket_id);

            exit;

        } 



    }

    public function senetekleguncelle(Request $request)

    {



        $adisyon_id = '';



        if(isset($request->adisyon_id)){

            $adisyon_id = Adisyonlar::where('id',$request->adisyon_id)->first();

            AdisyonHizmetler::where('adisyon_id',$adisyon_id)->delete();

            AdisyonUrunler::where('adisyon_id',$adisyon_id)->delete();

            $adisyon_paketler = AdisyonPaketler::where('adisyon_id',$adisyon_id)->get();

            foreach($adisyon_paketler as $adisyon_paket)

            {

                AdisyonPaketSeanslar::where('adisyon_paket_id',$adisyon_paket->id)->delete();

            }

            AdisyonPaketler::where('adisyon_id',$adisyon_id)->delete();

            $eskitahsilatlar = Tahsilatlar::where('adisyon_id',$adisyon_id)->get();

            foreach($eskitahsilatlar as $tahsilat)

            {

                TahsilatHizmetler::where('tahsilat_id',$tahsilat->id)->delete();

                TahsilatUrunler::where('tahsilat_id',$tahsilat->id)->delete();

                TahsilatPaketler::where('tahsilat_id',$tahsilat->id)->delete();

            }

            Tahsilatlar::where('adisyon_id',$adisyon_id)->delete();

            

        }

        else

            $adisyon_id = self::yeni_adisyon_olustur($request->ad_soyad,$request->sube,'Senetle Ödeme',date('Y-m-d'));



        $on_odeme_tutari = str_replace(['.',','],

        ['','.'],$request->on_odeme_tutari); $on_odeme_var  = false; if

        ($on_odeme_tutari != 0) $on_odeme_var  = true; $tahsilat = '';



        if($on_odeme_var)

        {

            $tahsilat = new Tahsilatlar();

            $tahsilat->adisyon_id = $adisyon_id;

            $tahsilat->user_id = $request->ad_soyad;

            $tahsilat->tutar = $on_odeme_tutari;

            $tahsilat->odeme_tarihi = date('Y-m-d');    

            $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

            $tahsilat->salon_id = $request->sube;

            $tahsilat->yapilan_odeme = $on_odeme_tutari;

            $tahsilat->odeme_yontemi_id = $request->on_odeme_turu;

            $tahsilat->save();

        }

        Alacaklar::where('adisyon_id',$adisyon_id)->delete();

        $senet = '';

        if(is_numeric($request->senet_id))

            $senet = Senetler::where('id',$request->senet_id)->first();

        else

            $senet = new Senetler();

        $musteri = User::where('id',$request->ad_soyad)->first();

        $musteri->tc_kimlik_no = $request->tc_kimlik_no;

        $musteri->adres = $request->adres;

        $musteri->save();

        $senet->kefil_adi = $request->kefil_adi;

        $senet->kefil_adres = $request->kefil_adres;

        $senet->kefil_tc_vergi_no = $request->kefil_tc_vergi_no;

        $senet->user_id = $request->ad_soyad;

        $senet->adisyon_id = $adisyon_id;

        $senet->vade_sayisi = $request->vade;

        $senet->salon_id = $request->sube;

        $senet->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

        $senet->senet_turu = $request->senet_turu;

        $senet->save();

        if(isset($request->senet_hizmet_id)){

            foreach($request->senet_hizmet_id as $key => $hizmet){

                $adisyon_hizmet_id = self::adisyon_hizmet_ekle($adisyon_id,$hizmet,$request->senet_hizmet_tarih[$key],$request->senet_hizmet_saat[$key],$request->senet_hizmet_sure[$key],$request->senet_hizmet_fiyat[$key],false,$request->senet_hizmet_personel[$key],$request->senet_hizmet_personel[$key],$senet->id,null); 

                if($on_odeme_var)

                {

                    $odeme = new TahsilatHizmetler();

                    $odeme->adisyon_hizmet_id = $adisyon_hizmet_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->tutar = round(

                                str_replace(['.',','],['','.'],$request->senet_hizmet_fiyat[$key])

                                /($on_odeme_tutari+str_replace(['.',','],['','.'],$request->senet_tutar))*$on_odeme_tutari,2

                            );



                    $odeme->save();

                }

            }

            

        }

        if(isset($request->urun_id_senet)){

            foreach($request->urun_id_senet as $key => $urun)

            {

                $adisyon_urun = new AdisyonUrunler();

                $adisyon_urun->islem_tarihi = date('Y-m-d');

                $adisyon_urun->adisyon_id= $adisyon_id;

                $adisyon_urun->urun_id = $urun;

                $adisyon_urun->adet = $request->urun_adet_senet[$key];

                $adisyon_urun->fiyat = $request->urun_fiyat_senet[$key];

                $adisyon_urun->personel_id = $request->urun_satan_senet[$key];

                $adisyon_urun->senet_id = $senet->id;



                $adisyon_urun->save();

                if($on_odeme_var)

                {

                    $odeme = new TahsilatUrunler();

                    $odeme->adisyon_urun_id = $adisyon_urun->id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->aciklama = "round((str_replace(['.',','],['','.'],".$request->urun_fiyat_senet[$key].")/(str_replace(['.',','],['','.'],".$on_odeme_tutari.")+str_replace(['.',','],['','.'],".$request->senet_tutar.")))*str_replace(['.',','],['','.'],".$on_odeme_tutari.") ,2 )";

$odeme->tutar = round((str_replace(['.',','],['','.'],$request->urun_fiyat_senet[$key])/($on_odeme_tutari+str_replace(['.',','],['','.'],$request->senet_tutar)))*$on_odeme_tutari ,2 );



                    $odeme->save();

                }

           

            }

        }

        if(isset($request->paket_id_senet))

        {

            foreach($request->paket_id_senet as $key=>$paket){

                

                $adisyon_paket_id = self::adisyona_paket_ekle(

                    $adisyon_id,

                    $paket,

                    $request->paket_fiyat_senet[$key],

                    $request->paket_baslangic_tarihi_senet[$key],

                    $request->paket_seans_aralik_gun_senet[$key],

                    $request->paket_satan_senet[$key],

                    $senet->id,

                    null);

                



                $seanstarih = $request->paket_baslangic_tarihi_senet[$key];

                $paket = Paketler::where('id',$paket)->first();

                $toplam_seans_sayilari = $paket->hizmetler->sum('seans');



           

                for($i=1;$i<=$toplam_seans_sayilari;$i++)

                {

                        if($i>1)

                            $seanstarih = date('Y-m-d',strtotime('+'.$request->paket_seans_aralik_gun_senet[$key].' days',strtotime($seanstarih)));

                        $seans = new AdisyonPaketSeanslar();

                        $seans->adisyon_paket_id = $adisyon_paket_id;

                        $seans->seans_tarih = $seanstarih;

                        $seans->save();

                }

                if($on_odeme_var)

                {

                    $odeme = new TahsilatPaketler();

                    $odeme->adisyon_paket_id = $adisyon_paket_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->tutar = round(

                                str_replace(['.',','],['','.'],$request->paket_fiyat_senet[$key])

                                /($on_odeme_tutari+str_replace(['.',','],['','.'],$request->senet_tutar))*$on_odeme_tutari,2

                    );



                    $odeme->save();

                }

            } 

        }





        

        $vadeler = SenetVadeleri::where('senet_id',$senet->id)->delete();

        $vade_tarihi = $request->vade_baslangic_tarihi;

        $tutar = str_replace(['.',','],['','.'],$request->senet_tutar)/$request->vade;

        for($i=1;$i<=$request->vade;$i++){

            $yeni_vadeler = new SenetVadeleri();

            $yeni_vadeler->senet_id = $senet->id;

            if($i==1)

                $yeni_vadeler->vade_tarih = $request->vade_baslangic_tarihi;

            else{

                $vade_tarihi = date("Y-m-d", strtotime("+1 month", strtotime($vade_tarihi)));

                $yeni_vadeler->vade_tarih = $vade_tarihi;

            }

            $yeni_vadeler->odendi = false;

            $yeni_vadeler->tutar = number_format($tutar,2,'.','');

            $yeni_vadeler->save();

            $alacak = new Alacaklar();

            $alacak->adisyon_id= $adisyon_id;

            $alacak->salon_id = $request->sube;

             

            $alacak->tutar = $yeni_vadeler->tutar;

            

            $alacak->planlanan_odeme_tarihi = $yeni_vadeler->vade_tarih;

            $alacak->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

             

            $alacak->user_id = $musteri->id;

            $alacak->senet_id = $senet->id;

            $alacak->save();

        }

        

            

         

        /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format(str_replace(['.',','],['','.'],$request->senet_tutar),2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senediniz oluşturulmuştur. Detaylı bilgi için bize ulaşın. 0".$senet->salon->telefon_1 )),false,1,false);

        

        $yetkili_liste = self::yetkili_telefonlari($request);

        foreach($yetkili_liste as $_yetkili)

        {

            self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::guard('isletmeyonetim')->user()->name .' tarafından '.date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format(str_replace(['.',','],['','.'],$request->senet_tutar),2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senet oluşturulmuştur.")),false,1,false);

        }*/

        

        return array(

            'senet_id' => $senet->id,

            'senetler'=>self::senetleri_getir($request,'',''),

            'senetler_acik'=>self::senetleri_getir($request,0,''),

            'senetler_kapali'=>self::senetleri_getir($request,1,''),

            'senetler_odenmemis'=>self::senetleri_getir($request,2,''),

            'adisyon_id' => $adisyon_id

        );





    }

    public function taksitekleguncelle(Request $request)

    {

        

        if(isset($request->senet_vade_id))

            foreach($request->senet_vade_id as $senetvadesi)

                Alacaklar::where('senet_vade_id',$request->senetvadesi)->delete();

        if(isset($request->taksit_vade_id))

            foreach($request->taksit_vade_id as $senetvadesi)

                Alacaklar::where('taksit_vade_id',$request->senetvadesi)->delete();



        $taksitlitahsilat = '';

        if(is_numeric($request->taksitli_tahsilat_id))

            $taksitlitahsilat = TaksitliTahsilatlar::where('id',$request->taksitli_tahsilat_id)->first();

        else

            $taksitlitahsilat = new TaksitliTahsilatlar();

        $musteri = User::where('id',$request->ad_soyad)->first();

        

        

        $taksitlitahsilat->user_id = $request->ad_soyad;

        if(isset($request->adisyon_id))

            $taksitlitahsilat->adisyon_id = $request->adisyon_id;



        $taksitlitahsilat->vade_sayisi = $request->vade;

        $taksitlitahsilat->salon_id = $request->sube;

        $taksitlitahsilat->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

        

        $taksitlitahsilat->save();

        $vadeler = TaksitVadeleri::where('taksitli_tahsilat_id',$taksitlitahsilat->id)->delete();



        $vade_tarihi = $request->vade_baslangic_tarihi;

        $tutar = str_replace(['.',','],['','.'],$request->taksit_tutar)/$request->vade;

        for($i=1;$i<=$request->vade;$i++){

            $yeni_vadeler = new TaksitVadeleri();

            $yeni_vadeler->taksitli_tahsilat_id = $taksitlitahsilat->id;

            if($i==1)

                $yeni_vadeler->vade_tarih = $request->vade_baslangic_tarihi;

            else{

                $vade_tarihi = date("Y-m-d", strtotime("+1 month", strtotime($vade_tarihi)));

                $yeni_vadeler->vade_tarih = $vade_tarihi;

            }

            $yeni_vadeler->odendi = false;

            $yeni_vadeler->tutar = number_format($tutar,2,'.','');

            $yeni_vadeler->save();

            $alacak = new Alacaklar();

            $alacak->adisyon_id= $request->adisyon_id;

            $alacak->salon_id = $request->sube;

            

            $alacak->tutar = $yeni_vadeler->tutar;

            $alacak->taksitli_tahsilat_id = $taksitlitahsilat->id;

            $alacak->planlanan_odeme_tarihi = $yeni_vadeler->vade_tarih;

            $alacak->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

             

            $alacak->user_id = $musteri->id;

            $alacak->save();

        }

        $hizmet_kalem_sayisi = isset($request->adisyon_hizmet_id) ? AdisyonHizmetler::whereIn('id',$request->adisyon_hizmet_id)->where('indirim_tutari',null)->count() : 0;

        $urun_kalem_sayisi = isset($request->adisyon_urun_id) ? AdisyonUrunler::whereIn('id',$request->adisyon_urun_id)->where('indirim_tutari',null)->count() : 0;

        $paket_kalem_sayisi = isset($request->adisyon_paket_id) ? AdisyonPaketler::whereIn('id',$request->adisyon_paket_id)->where('indirim_tutari',null)->count() : 0;

        $kalem_sayisi = $hizmet_kalem_sayisi+$urun_kalem_sayisi+$paket_kalem_sayisi;

                    

        

        $kalem_basina_indirim_tutari = round((str_replace(['.',','],['','.'],$request->indirim_tutari)+$request->musteri_indirimi)/ $kalem_sayisi,2);

        if(isset($request->adisyon_hizmet_id)){

            foreach($request->adisyon_hizmet_id as $hizmet_id)

            {

                $adisyonhizmet = AdisyonHizmetler::where('id',$hizmet_id)->first();

                

                if($adisyonhizmet->senet_id === null && $adisyonhizmet->taksitli_tahsilat_id === null)

                    $adisyonhizmet->taksitli_tahsilat_id = $taksitlitahsilat->id;

                if($adisyonhizmet->indirim_tutari === null)

                    $adisyonhizmet->indirim_tutari = $kalem_basina_indirim_tutari;

                $adisyonhizmet->save(); 

                

            }

        }

        if(isset($request->adisyon_urun_id)){

            foreach($request->adisyon_urun_id as $urun_id)

            {

                $adisyonurun = AdisyonUrunler::where('id',$urun_id)->first();

                if($adisyonurun->senet_id === null && $adisyonurun->taksitli_tahsilat_id === null)

                    $adisyonurun->taksitli_tahsilat_id = $taksitlitahsilat->id;

                if($adisyonurun->indirim_tutari === null)

                    $adisyonurun->indirim_tutari = $kalem_basina_indirim_tutari;

                $adisyonurun->save();

                

            }

        }

        if(isset($request->adisyon_paket_id)){

            foreach($request->adisyon_paket_id as $paket_id)

            {

                $adisyonpaket = AdisyonPaketler::where('id',$paket_id)->first();

                if($adisyonpaket->senet_id === null && $adisyonpaket->taksitli_tahsilat_id === null)

                    $adisyonpaket->taksitli_tahsilat_id = $taksitlitahsilat->id;

                if($adisyonpaket->indirim_tutari === null) 

                    $adisyonpaket->indirim_tutari = $kalem_basina_indirim_tutari;

                $adisyonpaket->save();

                 

            }

        }

        if(isset($request->indirimli_toplam_tahsilat_tutari)&&$request->indirimli_toplam_tahsilat_tutari > 0)

        {



            $tahsilat = new Tahsilatlar();

            if(isset($request->adisyon_id))

                $tahsilat->adisyon_id = $request->adisyon_id;

            $tahsilat->user_id = $request->ad_soyad;

            $tahsilat->tutar = str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);

            $tahsilat->odeme_tarihi = $request->tahsilat_tarihi;    

            $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

            $tahsilat->salon_id = $request->sube;

            $tahsilat->yapilan_odeme = str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);

            $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;

            $tahsilat->notlar = $request->tahsilat_notlari;

            $tahsilat->save();

            if(isset($request->adisyon_hizmet_id) && isset($request->adisyon_hizmet_senet_id) && isset($request->adisyon_hizmet_taksitli_tahsilat_id))

            {

                foreach($request->adisyon_hizmet_id as $key=>$hizmet_id)

                {      

                    if($request->adisyon_hizmet_senet_id[$key] == '' && $request->adisyon_hizmet_taksitli_tahsilat_id[$key] == '')

                    {

                        $odeme = new TahsilatHizmetler();

                        $odeme->adisyon_hizmet_id = $hizmet_id;

                        $odeme->tahsilat_id = $tahsilat->id;

                        $odeme->tutar = ((str_replace(['.',','],['','.'],$request->himzet_tahsilat_tutari_girilen[$key])-$kalem_basina_indirim_tutari)/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                        $odeme->aciklama = (str_replace(['.',','],['','.'],$request->himzet_tahsilat_tutari_girilen[$key])."/".str_replace(['.',','],['','.'],$request->tahsilat_tutari))."*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                        $odeme->save();  

                    }

                  

                }

             

            }

            if(isset($request->adisyon_urun_id) && isset($request->adisyon_urun_senet_id) && isset($request->adisyon_urun_taksitli_tahsilat_id))

            {

                foreach($request->adisyon_urun_id as $key2=>$urun_id)

                {

                    if($request->adisyon_urun_senet_id[$key2] == '' && $request->adisyon_urun_taksitli_tahsilat_id[$key2] == '')

                    {

                        $odeme = new TahsilatUrunler();

                        $odeme->adisyon_urun_id = $urun_id;

                        $odeme->tahsilat_id = $tahsilat->id;

                        $odeme->tutar = ((str_replace(['.',','],['','.'],$request->urun_tahsilat_tutari_girilen[$key2])-$kalem_basina_indirim_tutari)/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                        $odeme->aciklama = (str_replace(['.',','],['','.'],$request->urun_tahsilat_tutari_girilen[$key2])."/".str_replace(['.',','],['','.'],$request->tahsilat_tutari))."*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                        $odeme->save(); 

                    }

                }

            }

            if(isset($request->adisyon_paket_id)&& isset($request->adisyon_paket_senet_id) && isset($request->adisyon_paket_taksitli_tahsilat_id))

            {

                foreach($request->adisyon_paket_id as $key3=>$paket_id)

                { 

                    if($request->adisyon_paket_senet_id[$key3] == '' && $request->adisyon_paket_taksitli_tahsilat_id[$key3] == ''){

                        $odeme = new TahsilatPaketler();

                        $odeme->adisyon_paket_id = $paket_id;

                        $odeme->tahsilat_id = $tahsilat->id;

                        $odeme->tutar = ((str_replace(['.',','],['','.'],$request->paket_tahsilat_tutari_girilen[$key3])-$kalem_basina_indirim_tutari)/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                        $odeme->aciklama = (str_replace(['.',','],['','.'],$request->paket_tahsilat_tutari_girilen[$key3])."/".str_replace(['.',','],['','.'],$request->tahsilat_tutari))."*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 

                        $odeme->save(); 

                    }

                }

            }

        }

        /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format($request->senet_tutar,2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senediniz oluşturulmuştur. Detaylı bilgi için bize ulaşın. 0".$senet->salon->telefon_1 )),false,1,false);

        

        $yetkili_liste = self::yetkili_telefonlari($request);

        foreach($yetkili_liste as $_yetkili)

        {

            self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::guard('isletmeyonetim')->user()->name .' tarafından '.date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format($request->senet_tutar,2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senet oluşturulmuştur.")),false,1,false);

        }*/

        

        return self::musteri_tahsilatlari($request,$musteri->id,"");

        exit;

        

        





    }

    public function kampanya_yonetimi_liste(Request $request){

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();



        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 3)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $paketler = self::paket_liste_getir('',true,$request);

        

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

     return view('isletmeadmin.kampanya_yonetimi',['paketler'=>$paketler,'pageindex'=>22,'sayfa_baslik'=>'Reklam Yönetimi','isletme'=>$isletme,'bildirimler'=>self::bildirimgetir($request),'kampanya_yonetimi'=>self::paket_kampanyalar($request), 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);



  

    }

    public function paket_kampanyalar(Request $request)

    {

       return DB::table('kampanya_yonetimi')->join('kampanya_katilimcilari','kampanya_yonetimi.id','=','kampanya_katilimcilari.kampanya_id')->select('kampanya_yonetimi.paket_isim as paket_isim','kampanya_yonetimi.seans as seans','kampanya_yonetimi.fiyat as fiyat','kampanya_yonetimi.hizmet_adi as hizmet_adi',

        DB::raw('COUNT(kampanya_yonetimi.id) as katilimci_sayisi'),

        DB::raw('CONCAT("<div class=\"dropdown\">

                        <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"

                                  href=\"#\"

                                  role=\"button\"

                                  data-toggle=\"dropdown\"

                                ><i class=\"dw dw-more\"></i>

                        </a>

                        <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                                    <a class=\"dropdown-item\" href=\"#\"  data-toggle=\"modal\" name=\"kampanya_detay\"data-target=\"#kampanya_detay_modal\" data-value=\"",kampanya_yonetimi.id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>

                    <a class=\"dropdown-item\" href=\"#\" name=\"kampanya_sil\" data-value=\"",kampanya_yonetimi.id,"\"><i class=\"dw dw-delete-3\"></i>Sil</a></div></div>") AS islemler'))->where('kampanya_yonetimi.salon_id',self::mevcutsube($request))->groupBy('kampanya_yonetimi.id')->where('kampanya_yonetimi.aktifmi',true)->get();

    }

     public function kampanyadetay(Request $request)

    {

      $katilimcilar=DB::table('kampanya_katilimcilari')->join('users','kampanya_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon', DB::raw('CASE WHEN kampanya_katilimcilari.durum IS NULL THEN "Bekleniyor" WHEN kampanya_katilimcilari.durum=0 THEN "Katılmıyor" WHEN kampanya_katilimcilari.durum=1 THEN "Katılıyor" END as durum'),

        DB::raw('CONCAT("<input type=\"checkbox\" name=\"sec\" value=\"",users.id,"\">",users.name) AS sms_liste')



    )->where('kampanya_katilimcilari.kampanya_id',$request->kampanyaid)->get();

      $katilimcilar_katilanlar=DB::table('kampanya_katilimcilari')->join('users','kampanya_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('kampanya_katilimcilari.kampanya_id',$request->kampanyaid)->where('kampanya_katilimcilari.durum',true)->get();

       $katilimcilar_katilmayanlar=DB::table('kampanya_katilimcilari')->join('users','kampanya_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('kampanya_katilimcilari.kampanya_id',$request->kampanyaid)->where('kampanya_katilimcilari.durum',false)->where('kampanya_katilimcilari.durum','!=',null)->get();

      $katilimcilar_beklenen=DB::table('kampanya_katilimcilari')->join('users','kampanya_katilimcilari.user_id','=','users.id')

      ->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('kampanya_katilimcilari.kampanya_id',$request->kampanyaid)->where('kampanya_katilimcilari.durum',null)->get();





      $kampanya=DB::table('kampanya_yonetimi')->join('kampanya_katilimcilari','kampanya_yonetimi.id','=','kampanya_katilimcilari.kampanya_id')

       ->select(

          DB::raw('COUNT(kampanya_katilimcilari.kampanya_id) as katilimci_sayisi'),

         'kampanya_yonetimi.seans as seans',

         'kampanya_yonetimi.hizmet_adi as hizmet_adi',

          'kampanya_yonetimi.paket_isim as paket_isim',

          'kampanya_yonetimi.fiyat as fiyat'

        )

         

       

       ->groupBy('kampanya_katilimcilari.kampanya_id')->where('kampanya_yonetimi.id',$request->kampanyaid)->first();

    return array(

      'katilimcilar'=>$katilimcilar,

      'kampanya'=>$kampanya,

      'katilimcilar_katilanlar'=>$katilimcilar_katilanlar,

      'katilimcilar_katilmayanlar'=>$katilimcilar_katilmayanlar,

      'katilimcilar_beklenen'=>$katilimcilar_beklenen,

      'kampanyaid'=>$request->kampanyaid,

      'beklenen_count'=>$katilimcilar_beklenen->count()

    );

    

    }

    public function kampanyaekleduzenle(Request $request)

    {

        $kampanya_yonetimi = "";

        if(isset($request->kampanya_id))

            $kampanya_yonetimi = KampanyaYonetimi::where('id',$request->kampanya_id)->first();

        else

            $kampanya_yonetimi = new KampanyaYonetimi();

        $kampanya_yonetimi->paket_isim = Paketler::where('id',$request->kampanyapaketadi)->value('paket_adi');

        $kampanya_yonetimi->hizmet_adi = $request->kampanyapakethizmet;

        $kampanya_yonetimi->fiyat = $request->kampanyapaketfiyat;

         $kampanya_yonetimi->seans = $request->kampanyapaketseans;

        $kampanya_yonetimi->salon_id = self::mevcutsube($request);

        $kampanya_yonetimi->aktifmi=1;

        $kampanya_yonetimi->mesaj=$request->kampanya_sms;

        $kampanya_yonetimi->save();

        

        

        $katilimcilar = KampanyaKatilimcilari::where('id',$kampanya_yonetimi->id)->delete();

        $gsm = array();

        $mesajlar=array();

        if(isset($request->kampanya_katilimci_musteriler)){

            foreach($request->kampanya_katilimci_musteriler as $katilimci)

            {

                $yenikatilimci = new KampanyaKatilimcilari();

                $yenikatilimci->kampanya_id = $kampanya_yonetimi->id;

                $yenikatilimci->user_id = $katilimci;

                $yenikatilimci->save();

                $toplumusteri = User::where('id',$katilimci)->first();

                $katilim_link = ''; 

                if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$kampanya_yonetimi->salon_id)->value('musteri')==1)

                    $katilim_link = ' Katılım için : https://'.$_SERVER['HTTP_HOST'].'/kampanyakatilim/'.$kampanya_yonetimi->id.'/'.$toplumusteri->id;

                if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$kampanya_yonetimi->salon_id)->value('kara_liste')!=1)

                    array_push($mesajlar, array("to"=>$toplumusteri->cep_telefon,"message"=> $request->kampanya_sms.$katilim_link));

            }

        }

        if (isset($request->grup_katilimci_musteriler)) {

            foreach($request->grup_katilimci_musteriler as $grupkatilimci)

            {

         

                $grupkatilimcilar=GrupSmsKatilimcilari::where('grup_id',$grupkatilimci)->get();

                foreach ($grupkatilimcilar as $grup) {

                    $yenikatilimci = new KampanyaKatilimcilari();

                    $yenikatilimci->kampanya_id = $kampanya_yonetimi->id;

                    $yenikatilimci->user_id = $grup->user_id;

                    $yenikatilimci->save();

                    $toplumusteri = User::where('id',$grup->user_id)->first();

                    $katilim_link = '';

                    if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$kampanya_yonetimi->salon_id)->value('musteri')==1)

                        $katilim_link = ' Katılım için : https://'.$_SERVER['HTTP_HOST'].'/kampanyakatilim/'.$kampanya_yonetimi->id.'/'.$toplumusteri->id;

                    if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$kampanya_yonetimi->salon_id)->value('kara_liste')!=1)

                        array_push($mesajlar, array("to"=>$grup->musteri->cep_telefon,"message"=> $request->kampanya_sms .$katilim_link));

                }

            

            

            }

       }

       

           

       

        $gonder=self::sms_gonder($request,$mesajlar,false,4,false);

      

        return array(



          "mesaj" => "Kampanya başarıyla kaydedildi ve gönderildi",

          "gonder"=>$gonder,

          "kampanya_yonetimi"=>self::paket_kampanyalar($request)



        );





    }

    public function kampanya_sil(Request $request){

        KampanyaYonetimi::where('id',$request->kampanya_id)->update(['aktifmi'=>false]);

        return self::paket_kampanyalar($request);



    }

    public function etkinlik_sil(Request $request){

        Etkinlikler::where('id',$request->etkinlik_id)->update(['aktifmi'=>false]);

        return self::etkinlikyukle($request);



    }

    public function seanstanrandevuolustur(Request $request)

    {

        $adisyon = Adisyonlar::where('id',$request->adisyonid)->first();

        $randevu = new Randevular();

        $randevu->user_id = $adisyon->user_id;

        $randevu->salon_id = $adisyon->salon_id;

        $tarih = explode(' ',$request->tarih_saat);

        $randevu->tarih = $request->tarih;

        $randevu->saat = $request->saat;

        $randevu->salon = true;

        $randevu->sms_hatirlatma = true;

        $randevu->seans_id = $request->seansid;

        $randevu->durum = 1;

        $randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;

        $randevu->adisyon_paket_uzerinden_olusturma = true;

        $hizmet = Hizmetler::where('id',$request->hizmetid)->value('hizmet_adi');

        $randevu->save();

        $yenirandevuhizmetpersonel = new RandevuHizmetler();

        $yenirandevuhizmetpersonel->randevu_id = $randevu->id;

        $yenirandevuhizmetpersonel->hizmet_id = $request->hizmetid;

        if(str_contains($request->personelid,'cihaz'))

        {

                $str = explode('-',$request->personelid);

                $yenirandevuhizmetpersonel->cihaz_id = $str[1];

        }

        else{

                $yenirandevuhizmetpersonel->personel_id = $request->personelid;

        }

        if($request->odaid != '')

            $yenirandevuhizmetpersonel->oda_id = $request->odaid;

        $sure_dk = SalonHizmetler::where('hizmet_id',$request->hizmetid)->where('salon_id',$adisyon->salon_id)->value('sure_dk');

        $yenirandevuhizmetpersonel->sure_dk = $sure_dk;

        $yenirandevuhizmetpersonel->fiyat = SalonHizmetler::where('hizmet_id',$request->hizmetid)->where('salon_id',$adisyon->salon_id)->value('baslangic_fiyat');

         

        $yenirandevuhizmetpersonel->saat = $request->saat;

        $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$sure_dk.' minutes', strtotime($request->saat)));



                    

        $yenirandevuhizmetpersonel->save();





        $seans = AdisyonPaketSeanslar::where('id',$request->seansid)->first();



        $seans->randevu_olusturuldu = true;

        $seans->randevu_id = $randevu->id;

        $seans->save();



        if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$randevu->salon_id)->value('musteri')){

            $mesajlar = array(

                array("to"=>$randevu->users->cep_telefon,"message"=>$randevu->salonlar->salon_adi . " tarafından ".date('d.m.Y',strtotime($randevu->tarih)) .'-'.date('H:i',

                    strtotime($randevu->saat)).' olarak randevunuz oluşturulmuştur. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0'.$randevu->salonlar->telefon_1 ),

                

            



            );

            self::sms_gonder($request,$mesajlar,false,1,false);

        }

        if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$randevu->salon_id)->value('personel')){

            if(is_numeric($request->personelid)){

                $yetkiliid=Personeller::where('id',$request->personel_id)->value('yetkili_id');

                      

                $gsm = IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1');



                $mesajlar = array(

                    array("to"=>$gsm,"message"=> $randevu->users->name .' isimli müşterinin '. $hizmet . " için ".date('d.m.Y',strtotime($randevu->tarih)) .'-'.date('H:i',

                        strtotime($randevu->saat)) .' tarihli randevusu oluşturulmuştur.'),



                );

            } 

            self::sms_gonder($request,$mesajlar,false,1,false);

            $mesaj = $randevu->users->name .' isimli müşterinin '. $hizmet . " için ".date('d.m.Y',strtotime($randevu->tarih)) .'-'.date('H:i',

                        strtotime($randevu->saat)) .' tarihli randevusu oluşturulmuştur.';

            self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$request->personel_id,null, Auth::guard('isletmeyonetim')->user()->profil_resim,$randevu->id);

                $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$request->personel_id)->pluck('bildirim_id')->toArray(); 

            self::bildirimgonder($bildirimkimlikleri,"Randevu Güncelleme",$mesaj,$randevu->salon_id);



        }

        



        return self::adisyon_paket_satis_getir($adisyon->id,true,$seans->adisyon_paket_id);





    }



  



    public function randevudetayigetir(Request $request)

    {

        $randevu = Randevular::where('id',$request->randevu_id)->first();

        $hizmetler_html = '';

        foreach($randevu->hizmetler as $key => $hizmet)

        {

            if($hizmet->yardimci_personel != 1)

            {

                $hizmetler_html .= '<div class="row" data-value="'.$key.'" style="background:#e2e2e2; margin-bottom:10px">

                                    <div class="col-md-3 col-sm-6 col-xs-6 col-6">

                                        <div class="form-group">

                                            <label>Personel </label>

                                            <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect" style="width: 100%;"><option></option>'.self::personeldropliste($request,[$hizmet->personel_id]).'</select></div></div>';

            

                

                $hizmetler_html .= '<div class="col-md-3 col-sm-6 col-xs-6 col-6">

                                                 <label>Yardımcı Personel(-ler) </label>

                                                 <select name="randevuyardimcipersonelleriyeni" id="randevuyardimcipersonelleriyeni_'.$key.'[]" multiple class="form-control custom-select2" style="width: 100%;">'.self::personeldropliste($request,RandevuHizmetler::where('hizmet_id',$hizmet->hizmet_id)->where('randevu_id',$randevu->id)->where('yardimci_personel',true)->pluck('personel_id')->toArray()).'</select>

                                              </div>';

               

                $hizmetler_html .= '<div class="col-md-3 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">

                                <label>Cihaz</label>

                                <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect" style="width: 100%;"><option></option>';

                foreach(Cihazlar::where('salon_id',$randevu->salon_id)->where('durum',true)->where('aktifmi',true)->get() as $cihaz)

                {

                    if($hizmet->cihaz_id == $cihaz->id)

                        $hizmetler_html .= '<option selected value="'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                    else

                        $hizmetler_html .= '<option value="'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                }

                $hizmetler_html .= '</select></div></div>

                         <div class="col-md-3 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">

                               <label>Hizmet</label>

                               <select name="randevuhizmetleriyeni[]" class="form-control opsiyonelSelect" style="width: 100%;"><option></option>';

                foreach(SalonHizmetler::where('salon_id',$randevu->salon_id)->get() as $hizmetliste)

                {

                    if($hizmet->hizmet_id == $hizmetliste->hizmet_id)

                        $hizmetler_html .= '<option selected value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

                    else

                        $hizmetler_html .= '<option value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

                }

                $hizmetler_html .= '</select>

                            </div>

                         </div>

                         <div class="col-md-3 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">

                               <label>Oda (opsiyonel)</label>

                               <select name="randevuodalariyeni[]"  class="form-control opsiyonelSelect" style="width:100%">';

                if($hizmet->oda_id === null)

                    $hizmetler_html .= '<option selected></option>';

                else

                    $hizmetler_html .= '<option></option>';

                foreach(Odalar::where('salon_id',$randevu->salon_id)->where('durum',true)->where('aktifmi',true)->get() as $oda)

                {

                    if($hizmet->oda_id == $oda->id)

                        $hizmetler_html .= '<option selected value="'.$oda->id.'">'.$oda->oda_adi.'</option>';

                    else

                        $hizmetler_html .= '<option value="'.$oda->id.'">'.$oda->oda_adi.'</option>';

                }

                $hizmetler_html .= ' </select>

                            </div>

                         </div>

                         <div class="col-md-3 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">

                               <label>Süre</label>

                               <input type="tel" class="form-control" name="hizmet_suresi[]" value="'.$hizmet->sure_dk.'">

                            </div>

                         </div>

                         <div class="col-md-3 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">

                               <label>Fiyat</label>

                               <input type="tel" class="form-control" name="hizmet_fiyat[]" value="'.$hizmet->fiyat.'">

                            </div>

                         </div>

                         <div class="col-md-2 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">';

                if($key==0)

                                $hizmetler_html .= '<label>Üsttekiyle Birleştir</label>

                                 

                               <div class="custom-control custom-checkbox mb-5">



                                  <input type="checkbox" class="custom-control-input" name="birlestir'.$key.'" disabled style="display:none" id="customCheck_'.$key.'"/>

                                  <label class="custom-control-label" name="birlestir_label" for="customCheck_'.$key.'"></label>

                               </div>';

                else

                    $hizmetler_html .= '<label style="visibility:hidden;font-size:12px;width:100%" class="usttekiylebirlestiryazi"  >Üsttekiyle Birleştir</label>

                                 

                               <div class="custom-control custom-checkbox mb-5">



                                  <input type="checkbox" class="custom-control-input" name="birlestir'.$key.'" id="customCheck_'.$key.'"/>

                                  <label class="custom-control-label" name="birlestir_label" for="customCheck_'.$key.'"></label>

                               </div>';

                $hizmetler_html .= '</div>

                         </div>

                         <div class="col-md-1 col-sm-6 col-xs-6 col-6">

                            <div class="form-group">

                               <label style="visibility: hidden;width: 100%;">Kaldır</label>';

                if($key > 0)

                    $hizmetler_html .= ' <button type="button" name="hizmet_formdan_sil_randevu_duzenleme"  data-value="'.$key.'" class="btn btn-danger" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:18px;background-color: transparent; border-color: transparent;color:#dc3545"><i class="icon-copy fa fa-remove"></i></button>';

                else

                    $hizmetler_html .= ' <button type="button" name="hizmet_formdan_sil_randevu_duzenleme"  data-value="'.$key.'" class="btn btn-danger" disabled style="padding:1px; border-radius: 0; line-height: 1px ; font-size:18px;background-color: transparent; border-color: transparent;color:#dc3545"><i class="icon-copy fa fa-remove"></i></button>';

                               

                $hizmetler_html .= '</div>

                         </div></div>';

            }

            

        }

             

        return array(

            'randevu_id' => $randevu->id,

            'musteri' => $randevu->users->id,

            'sms_hatirlatma' =>$randevu->sms_hatirlatma,

            'randevu_tarih' => $randevu->tarih,

            'randevu_saat' => $randevu->saat,

            'randevu_notu' => $randevu->personel_notu,



            'randevu_hizmetler' => str_replace("\n", "", $hizmetler_html)

        );

    }

    public function randevukontrolet(Request $request)

    {

        $htmltext = '';



    }

    public function kategoriyegorehizmetgetir(Request $request)

    {

        $hizmetler = SalonHizmetler::where('hizmet_kategori_id',$request->hizmet_kategori_id)->where('salon_id',$request->sube)->get();

        $hizmet_kategorisi_adi = Hizmet_Kategorisi::where('id',$request->hizmet_kategori_id)->value('hizmet_kategorisi_adi');

        $html = '<label>'.$hizmet_kategorisi_adi.' hizmetlerinden birini seçiniz.</label><select id="hizmet_kategorisine_gore_yeni_hizmet_secimi" class="form-control" style="width:100%">';

        foreach($hizmetler as $hizmet)

        {   

            $html .= '<option value="'.$hizmet->hizmet_id.'">'.$hizmet->hizmetler->hizmet_adi.'</option>';

        }

        $html .= '</select>';

        return $html;

    }

    public function ongorusmesatisyapilmadi(Request $request)

    {

        $ongorusme = OnGorusmeler::where('id',$request->ongorusmeid)->first();

        $ongorusme->durum = false;

        $ongorusme->satisyapilmadi_not=$request->satisyapilmamasebebi;

        $ongorusme->save();

        return self::ongorusmegetir($request,false);

    }

    public function ongorusmesatisyapildi(Request $request)

    {

        $ongorusme = OnGorusmeler::where('id',$request->ongorusmeid)->first();

        $ongorusme->durum = true;

        $ongorusme->save();

        $user = '';

        if($ongorusme->user_id == null)

        {

            if(User::where('cep_telefon',$ongorusme->cep_telefon)->count() == 0){

                $user = new User();

                $user->name = $ongorusme->ad_soyad;

                $user->cep_telefon = $ongorusme->cep_telefon;

                $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');

                $olusturulansifre = substr($random, 0, 6);

                $user->password = Hash::make($olusturulansifre);

                $user->cinsiyet = $ongorusme->cinsiyet;

                $user->meslek = $ongorusme->meslek;

                $user->adres = $ongorusme->adres;

                $user->musteri_tipi = $ongorusme->musteri_tipi;

                $user->il_id = $ongorusme->il_id;

                $user->save();

                self::sms_gonder($request,array(array("to"=>$user->cep_telefon,"message"=>" hesabınız oluşturulmuş olup ".$olusturulansifre." şifrenizle giriş yapabilirsiniz.")),false,1,false);

            }

            else

                $user = User::where('cep_telefon',$ongorusme->cep_telefon)->first();

            if(MusteriPortfoy::where('salon_id',$ongorusme->salon_id)->where('user_id',$user->id)->count() == 0){

                $portfoy = new MusteriPortfoy();

                $portfoy->salon_id = $ongorusme->salon_id;

                $portfoy->user_id = $user->id;

                $portfoy->aktif = true;

                $portfoy->save();

            } 

           

        }

        else

            $user = User::where('id',$ongorusme->user_id)->first();

        $adisyon_id = '';

        if($ongorusme->paket_id != null)

        {

            $adisyon_id = self::yeni_adisyon_olustur($user->id,$ongorusme->salon_id,$ongorusme->paket->paket_adi.' paketinin öngörüşme sonrası satışı',date('Y-m-d'));

        

            $adisyon_paket_id = self::adisyona_paket_ekle($adisyon_id,$ongorusme->paket_id,$ongorusme->paket->hizmetler->sum('fiyat'),$request->baslangic_tarihi,$request->seans_araligi,$ongorusme->personel_id,null,null);

            $seanstarih = $request->baslangic_tarihi;

            $toplam_seans_sayilari = $ongorusme->paket->hizmetler->sum('seans');



           

            for($i=1;$i<=$toplam_seans_sayilari;$i++)

            {

                    if($i>1)

                        $seanstarih = date('Y-m-d',strtotime('+'.$request->seans_araligi.' days',strtotime($seanstarih)));

                    $seans = new AdisyonPaketSeanslar();

                    $seans->adisyon_paket_id = $adisyon_paket_id;

                    $seans->seans_tarih = $seanstarih;

                    $seans->save();

            }

        }

        else{

            $urun = Urunler::where('id',$ongorusme->urun_id)->first();

            $adisyon_id = self::yeni_adisyon_olustur($user->id,$ongorusme->salon_id,$ongorusme->urun->urun_adi.' ürününün öngörüşme sonrası satışı',date('Y-m-d'));

            $adisyon_urun = new AdisyonUrunler();

            $adisyon_urun->islem_tarihi = date('Y-m-d');

            $adisyon_urun->adisyon_id= $adisyon_id;

            $adisyon_urun->urun_id = $ongorusme->urun_id;

            $adisyon_urun->personel_id = $ongorusme->personel_id;

            $adisyon_urun->adet = $request->urun_adedi;

            $adisyon_urun->fiyat = $urun->fiyat * $request->urun_adedi;

            $adisyon_urun->save(); 

            $urun->stok_adedi -= $request->urun_adedi;

            $urun->save();

        } 

        return array( 'adisyon_id' => $adisyon_id,'on_gorusmeler' => self::ongorusmegetir($request,false),'user_id'=>$user->id);







    }

   

    public function randevuyagelmedi(Request $request)

    {

        $randevu = Randevular::where('id',$request->randevuid)->first();

        $randevu->randevuya_geldi = false;

        $randevu->save();

        if(AdisyonPaketSeanslar::where('randevu_id',$request->randevuid)->count() != 0)

                $seans = AdisyonPaketSeanslar::where('randevu_id',$request->randevuid)->update(['geldi'=>false]);

        return array('mesaj'=>'Başarılı'); 

    }

     public function grupsmsekle(Request $request)

    {

        $grupsms = "";

        if(isset($request->grup_id))

            $grupsms = GrupSms::where('id',$request->grup_id)->first();

        else

            $grupsms = new GrupSms();

        $grupsms->grup_adi = $request->grup_adi;

        $grupsms->salon_id = self::mevcutsube($request);

        $grupsms->aktif_mi = true;

        $grupsms->save();

        $grup_katilimcilar = GrupSmsKatilimcilari::where('grup_id',$grupsms->id)->delete();

        foreach($request->duallistbox_demo1 as $key => $grup_katilimci)

        {

            $grup_yenikatilimci = new GrupSmsKatilimcilari();

            $grup_yenikatilimci->grup_id = $grupsms->id;

            $grup_yenikatilimci->user_id = $grup_katilimci;

            $grup_yenikatilimci->save();

        }

        return array(



          "mesaj" => "Grup Başarıyla Oluşturuldu",

          'grup' => self::grup_sms_liste_getir($request)



        );





    }

   

    public function santral_ayar_kaydet(Request $request)

    {

        if(isset($request->santralayar_1_musteri))

            SalonSantralAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['musteri'=>true]);

        else

            SalonSantralAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['musteri'=>false]);

        if(isset($request->santralayar_1_personel))

            SalonSantralAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['personel'=>true]);

        else

            SalonSantralAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['personel'=>false]);

        $salon = Salonlar::where('id',$request->sube)->first();



        $salon->randevu_cagri_hatirlatma = $request->randevu_hatirlatama_saat_once;

        $salon->save();

        return 'Santral ayarları başarıyla kaydedildi';



    }                                                                                                               

    

    public function sms_ayar_kaydet(Request $request)

    {

        if(isset($request->randevuayar_1_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['musteri'=>false]);

        if(isset($request->randevuayar_1_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',1)->update(['personel'=>false]);

        if(isset($request->randevuayar_2_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',2)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',2)->update(['musteri'=>false]);

        if(isset($request->randevuayar_2_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',2)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',2)->update(['personel'=>false]);

        if(isset($request->randevuayar_3_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',3)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',3)->update(['musteri'=>false]);

        if(isset($request->randevuayar_3_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',3)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',3)->update(['personel'=>false]);

        if(isset($request->randevuayar_4_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',4)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',4)->update(['musteri'=>false]);

        if(isset($request->randevuayar_5_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',5)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',5)->update(['musteri'=>false]);

        if(isset($request->randevuayar_6_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',6)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',6)->update(['musteri'=>false]);

        if(isset($request->randevuayar_6_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',6)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',6)->update(['personel'=>false]);

        if(isset($request->randevuayar_7_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',7)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',7)->update(['musteri'=>false]);

        if(isset($request->randevuayar_7_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',7)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',7)->update(['personel'=>false]);

        if(isset($request->randevuayar_8_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',8)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',8)->update(['musteri'=>false]);

        if(isset($request->randevuayar_9_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',9)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',9)->update(['musteri'=>false]);

        if(isset($request->randevuayar_10_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',10)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',10)->update(['musteri'=>false]);

        if(isset($request->randevuayar_11_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',11)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',11)->update(['musteri'=>false]);

        if(isset($request->randevuayar_11_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',11)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',11)->update(['personel'=>false]);

        if(isset($request->randevuayar_12_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',12)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',12)->update(['musteri'=>false]);

        if(isset($request->randevuayar_12_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',12)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',12)->update(['personel'=>false]);

        if(isset($request->randevuayar_13_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',13)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',13)->update(['musteri'=>false]);

        if(isset($request->randevuayar_14_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',14)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',14)->update(['musteri'=>false]);

        if(isset($request->randevuayar_14_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',14)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',14)->update(['personel'=>false]);

        if(isset($request->randevuayar_15_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',15)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',15)->update(['musteri'=>false]);

        if(isset($request->randevuayar_16_musteri_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',16)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',16)->update(['musteri'=>false]);

        if(isset($request->randevuayar_17_personel_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',17)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',17)->update(['personel'=>false]);



        if(isset($request->randevuayar_25_musteri))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',18)->update(['musteri'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',18)->update(['musteri'=>false]);

        if(isset($request->randevuayar_25_personel))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',18)->update(['personel'=>true]);



        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',18)->update(['personel'=>false]);

        if(isset($request->randevuayar_26_personel_acik_kapali))

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',19)->update(['personel'=>true]);

        else

            SalonSMSAyarlari::where('salon_id',$request->sube)->where('ayar_id',19)->update(['personel'=>false]);

        $salon = Salonlar::where('id',$request->sube)->first();



        $salon->randevu_sms_hatirlatma = $request->randevu_hatirlatama_saat_once;

        $salon->save();

        return 'SMS ayarları başarıyla kaydedildi';













        return $request->randevuayar_1_musteri;

    }

    public function musait_randevu_saatlerini_getir(Request $request)

    {

        $personel_cihaz_id= '';

        if(str_contains($request->personelid,'cihaz'))

        {

                $str = explode('-',$request->personelid);

                $personel_cihaz_id = $str[1];

        }

        else{

                $personel_cihaz_id = $request->personelid;

        }

        $hizmet_saatleri = array();

        //$randevuhizmetler = Randevular::where('tarih',$request->tarih)-

        $salon = Salonlar::where('id',$request->sube)->first();

        $tarih = $request->tarih_saat;

        $baslangic_saati = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',self::haftanin_gunu($tarih))->where('calisiyor',1)->value('baslangic_saati');

        $bitis_saati = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',self::haftanin_gunu($tarih))->where('calisiyor',1)->value('bitis_saati');

        

        $nowtime = date('H:i');

        $dolusaatler = array();

    

        $dolusaatlerstr = '';

        $musaitsaatler = '<select id="seansadisyonsaat" class="form-control" data-value="'.$request->seansid.'">';

        $musaitlikvar = false;

        $hizmet_suresi = SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$request->hizmetid)->value('sure_dk');



        $randevular = Randevular::join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')->where('randevular.tarih',$tarih)->where('randevular.salon_id',$request->sube)->where(

            function($q) use ($personel_cihaz_id)

            {

                $q->where('randevu_hizmetler.personel_id', $personel_cihaz_id);

                $q->orWhere('randevu_hizmetler.cihaz_id',$personel_cihaz_id);

            })->where(function($q) use ($request)

                        {

                            if($request->odaid != '')

                                $q->where('randevu_hizmetler.oda_id', $request->odaid);

                        }

                    )->where('randevular.durum',1)->get(['randevu_hizmetler.saat as baslangic_saati','randevu_hizmetler.saat_bitis as bitis_saati']);





        foreach($randevular as $randevu)

        {

            $saatdongu = date('H:i',strtotime($randevu->baslangic_saati));

            while($saatdongu!= date('H:i',strtotime($randevu->bitis_saati)))

            {

                array_push($dolusaatler,date('H:i', strtotime($saatdongu)));

                $saatdongu = date('H:i', strtotime('+1 minute', strtotime($saatdongu)));

                $dolusaatlerstr .= date('H:i', strtotime($saatdongu)).'<br>';

            }







        }

        $salon_mola_baslangic = SalonMolaSaatleri::where('salon_id',$request->sube)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('baslangic_saati');

        $salon_mola_bitis = SalonMolaSaatleri::where('salon_id',$request->sube)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('baslangic_saati');

        while(date('H:i',strtotime($salon_mola_baslangic)) != date('H:i',strtotime($salon_mola_bitis)))

        {

                array_push($dolusaatler,date('H:i', strtotime($salon_mola_baslangic)));

                $salon_mola_baslangic = date('H:i', strtotime('+1 minute', strtotime($salon_mola_baslangic)));

                $dolusaatlerstr .= date('H:i', strtotime($salon_mola_baslangic)).'<br>';

        }

        $personel_mola_baslangic = PersonelMolaSaatleri::where('personel_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('baslangic_saati');

        $personel_mola_bitis = PersonelMolaSaatleri::where('personel_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('bitis_saati');

        $cihaz_mola_baslangic = CihazMolaSaatleri::where('cihaz_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('baslangic_saati');

        $cihaz_mola_bitis = CihazMolaSaatleri::where('cihaz_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('bitis_saati');

        while(date('H:i',strtotime($personel_mola_baslangic)) != date('H:i',strtotime($personel_mola_bitis)))

        {

                array_push($dolusaatler,date('H:i', strtotime($personel_mola_baslangic)));

                $personel_mola_baslangic = date('H:i', strtotime('+1 minute', strtotime($personel_mola_baslangic)));

                $dolusaatlerstr .= date('H:i', strtotime($personel_mola_baslangic)).'<br>';

        } 

        while(date('H:i',strtotime($cihaz_mola_baslangic)) != date('H:i',strtotime($cihaz_mola_bitis)))

        {

                array_push($dolusaatler,date('H:i', strtotime($cihaz_mola_baslangic)));

                $cihaz_mola_baslangic = date('H:i', strtotime('+1 minute', strtotime($cihaz_mola_baslangic)));

                $dolusaatlerstr .= date('H:i', strtotime($cihaz_mola_baslangic)).'<br>';

        } 

        for($j = strtotime(date('H:i', strtotime($baslangic_saati) )) ; $j < strtotime(date('H:i', strtotime($bitis_saati))); $j+=($salon->randevu_saat_araligi*60)) 

        {     

                if((date('H:i',$j) > $nowtime && $tarih = date('Y-m-d') )||$tarih > date('Y-m-d')){

                    $hizmet_dongu = date('H:i',$j);

                    array_push($hizmet_saatleri,$hizmet_dongu);

                    $cakismavar = false;

                    



                    while($hizmet_dongu != date('H:i',strtotime('+'.$hizmet_suresi.' minutes',strtotime(date('H:i',$j)))))

                    {



                        //echo date('H:i',strtotime('+'.$hizmet_suresi.' minutes',strtotime(date('H:i',$j)))).'<br>';

                        $cakismavar = false;

                        if(in_array($hizmet_dongu,$dolusaatler)){

                            $cakismavar = true;

                             

                            break;

                        }

                        else 

                            



                        $hizmet_dongu = date('H:i', strtotime('+1 minute', strtotime($hizmet_dongu)));

                    }

                    if( !$cakismavar ){



                      $musaitsaatler .= '<option value="'.date('H:i',$j).'">'.date('H:i',$j).'</option>';

                       

                      $musaitlikvar = true;

                    }

                    else

                    {

                      $musaitsaatler .= '<option disabled style="background-color:red;color:#fff" value="'.date('H:i',$j).'">'.date('H:i',$j).'</option>';

                      $musaitlikvar = true;

                       

                    }

                    

                      

                }

        }

        $musaitsaatler .= '</select>';

        if($musaitlikvar)

        {

            return '<p>Randevu oluşturmak için lütfen saat seçiniz</p>'.$musaitsaatler;

            exit;

        }

        else

        {

            return '<p>Seçili tarih ve bilgilere uygun randevu bulunamadı</p>';

            exit;

        }



    }

    /*Bu metodu iş bitince sil */

    public function musait_randevu_saatlerini_getir2(Request $request)

    {

        $personel_cihaz_id= '';

        if(str_contains($request->personelid,'cihaz'))

        {

                $str = explode('-',$request->personelid);

                $personel_cihaz_id = $str[1];

        }

        else{

                $personel_cihaz_id = $request->personelid;

        }

        $hizmet_saatleri = array();

        //$randevuhizmetler = Randevular::where('tarih',$request->tarih)-

        $salon = Salonlar::where('id',$request->sube)->first();

        $tarih = $request->tarih_saat;

        $baslangic_saati = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',self::haftanin_gunu($tarih))->where('calisiyor',1)->value('baslangic_saati');

        $bitis_saati = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',self::haftanin_gunu($tarih))->where('calisiyor',1)->value('bitis_saati');

        

        $nowtime = date('H:i');

        $dolusaatler = array();

    

        $dolusaatlerstr = '';

        $musaitsaatler = '<select id="seansadisyonsaat" class="form-control" data-value="'.$request->seansid.'">';

        $musaitlikvar = false;

        $hizmet_suresi = SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$request->hizmetid)->value('sure_dk')+1;



        $randevular = Randevular::join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')->where('randevular.tarih',$tarih)->where('randevular.salon_id',$request->sube)->where(

            function($q) use ($personel_cihaz_id)

            {

                $q->where('randevu_hizmetler.personel_id', $personel_cihaz_id);

                $q->orWhere('randevu_hizmetler.cihaz_id',$personel_cihaz_id);

            })->where(function($q) use ($request)

                        {

                            if($request->odaid != '')

                                $q->where('randevu_hizmetler.oda_id', $request->odaid);

                        }

                    )->where('randevular.durum',1)->get(['randevu_hizmetler.saat as baslangic_saati','randevu_hizmetler.saat_bitis as bitis_saati']);





        foreach($randevular as $randevu)

        {

            $saatdongu = date('H:i',strtotime($randevu->baslangic_saati));

            while($saatdongu!= date('H:i',strtotime($randevu->bitis_saati)))

            {

                array_push($dolusaatler,date('H:i', strtotime($saatdongu)));

                $saatdongu = date('H:i', strtotime('+1 minute', strtotime($saatdongu)));

                $dolusaatlerstr .= date('H:i', strtotime($saatdongu)).'<br>';

            }   

        }

        for($j = strtotime(date('H:i', strtotime($baslangic_saati) )) ; $j < strtotime(date('H:i', strtotime($bitis_saati))); $j+=($salon->randevu_saat_araligi*60)) 

        {     

                if((date('H:i',$j) > $nowtime && $tarih = date('Y-m-d') )||$tarih > date('Y-m-d')){

                    $hizmet_dongu = date('H:i',$j);

                    array_push($hizmet_saatleri,$hizmet_dongu);

                    $cakismavar = false;

                    echo $hizmet_dongu.' +'.$hizmet_suresi.'<br>';



                    while($hizmet_dongu != date('H:i',strtotime('+'.$hizmet_suresi.' minutes',strtotime(date('H:i',$j)))))

                    {



                        //echo date('H:i',strtotime('+'.$hizmet_suresi.' minutes',strtotime(date('H:i',$j)))).'<br>';

                        $cakismavar = false;

                        if(in_array($hizmet_dongu,$dolusaatler)){

                            $cakismavar = true;

                            echo $hizmet_dongu.' de çakışma tespit edildi <br>';

                            break;

                        }

                        else 

                            echo $hizmet_dongu.' de çakışma tespit edilmedi <br>';



                        $hizmet_dongu = date('H:i', strtotime('+1 minute', strtotime($hizmet_dongu)));

                    }

                    if( !$cakismavar ){



                      $musaitsaatler .= '<option value="'.date('H:i',$j).'">'.date('H:i',$j).'</option>';

                      echo date('H:i',$j).' için müsaitlik var <br>';

                      $musaitlikvar = true;

                    }

                    else

                    {

                      $musaitsaatler .= '<option disabled style="background-color:red;color:#fff" value="'.date('H:i',$j).'">'.date('H:i',$j).'</option>';

                      $musaitlikvar = true;

                      echo date('H:i',$j).' için müsaitlik yok <br>';

                    }

                    

                      

                }

        }







    }

     

    public function haftanin_gunu($tarih)

    {

                   

        $day = 0; 

        if(date('D', strtotime($tarih))=='Mon') $day=1;

        else if(date('D', strtotime($tarih))=='Tue') $day=2;

        else if(date('D', strtotime($tarih))=='Wed') $day=3;

        else if(date('D', strtotime($tarih))=='Thu') $day=4;

        else if(date('D', strtotime($tarih))=='Fri') $day=5;

        else if(date('D', strtotime($tarih))=='Sat') $day=6;

        else if(date('D', strtotime($tarih))=='Sun') $day=7;

        return $day;

    }

    public function grup_sms_liste_getir(Request $request){

      return DB::table('grup_sms')->join('grup_sms_katilimcilari','grup_sms.id','=','grup_sms_katilimcilari.grup_id')->select(

        DB::raw('COUNT(grup_sms_katilimcilari.grup_id) as grup_katilimci_sayisi'),

        'grup_sms.grup_adi as grup_adi',

        DB::raw(

          'CONCAT("

                

                <button style=\"margin:10px;\" class=\" btn btn-success \" name=\"grup_sms_gonder\" data-toggle=\"modal\" data-target=\"#grup_sms_gonder_modal\"

                    data-value=\"",grup_sms.id,"\"><i class=\"dw dw-message\"></i> SMS Gönder</button>

                    <button style=\"margin:10px;\" class=\" btn btn-primary \" name=\"grup_duzenle\" data-toggle=\"modal\" data-target=\"#grup_sms_duzenle_modal\"

                    data-value=\"",grup_sms.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</button>

                    <button style=\"margin:10px;\" class=\"btn btn-danger \" name=\"grup_sil\" data-value=\"",grup_sms.id,"\"><i class=\"dw dw-delete-3\"></i>Sil</button>") as islemler'

        )





      )->where('salon_id',self::mevcutsube($request))->groupBy('grup_sms.id')->where('grup_sms.aktif_mi',true)->get();

    } 

                                                                                                                      

    public function grup_sil(Request $request){

        GrupSms::where('id',$request->grup_id)->update(['aktif_mi'=>false]);

        return self::grup_sms_liste_getir($request);



    } 

    public function grupduzenle(Request $request){

      $grup= GrupSms::where('id',$request->grup_id)->first();

      $gmusteri=MusteriPortfoy::where('salon_id',self::mevcutsube($request))->get();

      $html='';

      

      

      $html.=' <select multiple="multiple" name="duallistbox_demo1[]" title="duallistbox_demo1[]" ';

        

        foreach ($gmusteri as  $musteri) {

          $selected = false;

          foreach ($grup->musteriler as $grupmusteri) {

            

            if($musteri->user_id == $grupmusteri->user_id)

              $selected = true;

             



          }

          if($selected)

               

              $html.='<option selected value="'.$musteri->user_id.'">

                  '.$musteri->users->name.'</option>';

          else

               $html.='<option value="'.$musteri->user_id.'">

                  '.$musteri->users->name.'</option>'; 

        }

          

          

       

        $html.='</select>';

      

       return $html;

    }   

    public function grupsmsgonderme(Request $request){

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $grup=GrupSms::where('id',$request->grup_id)->first();

        $gsm = array();

        $mesajlar = array();

        foreach ($grup->musteriler as $musteri) {

            $grupmusteri = User::where('id',$musteri->user_id)->first();

            if(MusteriPortfoy::where('user_id',$musteri->user_id)->where('salon_id',$grup->salon_id)->value('kara_liste')!=1)

                array_push($mesajlar, array("to"=>$grupmusteri->cep_telefon,"message"=> $request->grup_mesaj));

                    

        }    

        if(count($mesajlar) > 0){

            return self::sms_gonder($request,$mesajlar,true,2,false);

            exit;

        }

        else

        {

            return array(

                'text' => 'Seçili müşteriler karalistenizde olduğu için mesajınız gönderilmemiştir',

                'title' => 'Hata',

                'status' => 'error',

            );

            exit;

        }

        

      

        

    }

     public function cinsiyetegore(Request $request){

      $musteriler=MusteriPortfoy::where('salon_id',$request->sube)->get();

      $musterihtml='';



      foreach ($musteriler as $musteri) {

        if($musteri->users->cinsiyet==$request->cinsiyet)

          $musterihtml.='<option value="'.$musteri->user_id.'">'.$musteri->users->name.'</option>';

        else{

          if ($request->cinsiyet=='Yok') {

             $musterihtml.='<option value="'.$musteri->user_id.'">'.$musteri->users->name.'</option>';

          }

         

        }

      }

      return $musterihtml;



    }

    public function hizmetegore(Request $request){

      $adisyonlar=Adisyonlar::where('salon_id',$request->sube)->get();

      $kullanici=array();



      foreach ($adisyonlar as $adisyon) {

        foreach ($adisyon->hizmetler as $alinanhizmet) {

          if($alinanhizmet->hizmet_id==$request->hizmet)

            array_push( $kullanici, $adisyon->user_id);

        }

        foreach ($adisyon->paketler as $alinanpaket) {

          foreach ($alinanpaket->paket->hizmetler as $pakethizmet) {

            if ($pakethizmet->hizmet_id==$request->hizmet) {

              array_push( $kullanici, $adisyon->user_id);

            }

          }

        }

      }

      $musteriler=MusteriPortfoy::whereIn('user_id',$kullanici)->get();

      $musterihtml='';



      foreach ($musteriler as $musteri) {

       

          $musterihtml.='<option value="'.$musteri->user_id.'">'.$musteri->users->name.'</option>';

       

            

         

        

      }

      return $musterihtml;







            }



    public function toplu_sms_liste_getir(Request $request){

      return DB::table('toplu_sms')->join('toplusms_katilimcilari','toplu_sms.id','=','toplusms_katilimcilari.toplu_id')->select(

        DB::raw('COUNT(toplusms_katilimcilari.toplu_id) as toplusms_katilimci_sayisi'),

        'toplu_sms.toplusms_icerik as toplusms_icerik',

        DB::raw(

          'CONCAT("<button class=\"btn btn-danger btn-block btn-lg\"><i class=\"dw dw-delete-3\"></i> Sil<\button>") AS islemler'

        )





      )->where('salon_id',self::mevcutsube($request))->groupBy('toplu_sms.id')->get();

    } 



    public function cinsiyetehizmetegore(Request $request){

       $adisyonlar=Adisyonlar::where('salon_id',$request->sube)->get();

      $kullanici=array();



      foreach ($adisyonlar as $adisyon) {

        foreach ($adisyon->hizmetler as $alinanhizmet) {

          if($alinanhizmet->hizmet_id==$request->hizmet)

            array_push( $kullanici, $adisyon->user_id);

        }

        foreach ($adisyon->paketler as $alinanpaket) {

          foreach ($alinanpaket->paket->hizmetler as $pakethizmet) {

            if ($pakethizmet->hizmet_id==$request->hizmet) {

              array_push( $kullanici, $adisyon->user_id);

            }

          }

        }

      }

      $musteriler=MusteriPortfoy::whereIn('user_id',$kullanici)->get();

      $musterihtml='';



      foreach ($musteriler as $musteri) {

       

          if($musteri->users->cinsiyet==$request->cinsiyet)

          $musterihtml.='<option value="'.$musteri->user_id.'">'.$musteri->users->name.'</option>';

        else{

          if ($request->cinsiyet=='Yok') {

             $musterihtml.='<option value="'.$musteri->user_id.'">'.$musteri->users->name.'</option>';

          }

         

        }

       

            

         

        

      }

      return $musterihtml;

    }



     public function filtrelismsgonder(Request $request){

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $gsm = array();

        $mesajlar=array();

        foreach ($request->duallistbox_demo3 as $musteri) {

            $filtrelimusteri = User::where('id',$musteri)->first();

            if(MusteriPortfoy::where('user_id',$musteri)->where('salon_id',$isletme->id)->value('kara_liste')!=1)

                array_push($mesajlar, array("to"=>$filtrelimusteri->cep_telefon,"message"=> $request->filtre_sms));

                    

        }

        if(count($mesajlar) > 0){

            return self::sms_gonder($request,$mesajlar,true,3,false);

            exit;

        }

        else

        {

            return array(

                'text' => 'Seçili müşteriler karalistenizde olduğu için mesajınız gönderilmemiştir',

                'title' => 'Hata',

                'status' => 'error',

            );

            exit;

        }

    }

     

    public function kampanyabeklenensms(Request $request){



      $kampanyabeklenen= KampanyaYonetimi::where('id',$request->kampanyaid)->first();

      $mesajlar=array();

      $kampanyamesaj = $kampanyabeklenen->mesaj;

      

       

      foreach ($kampanyabeklenen->kampanya_katilimcilari as  $katilimci) {

        if($katilimci->durum===null)

        {

            $katilim_link = ''; 

            if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$kampanyabeklenen->salon_id)->value('musteri')==1)

                $katilim_link = ' Katılım için : https://'.$_SERVER['HTTP_HOST'].'/kampanyakatilim/'.$kampanyabeklenen->id.'/'.$katilimci->user_id;

            if(MusteriPortfoy::where('user_id',$katilimci->user_id)->where('salon_id',$kampanyabeklenen->salon_id)->value('kara_liste')!=1)

                array_push($mesajlar, array("to"=>$katilimci->musteri->cep_telefon,"message"=> $kampanyabeklenen->mesaj.$katilim_link));

        }

        

      }







        $gonder=self::sms_gonder($request,$mesajlar,true,5,false);

      

        return array(



          "mesaj" => "SMS başarıyla gönderildi",

          "gonder"=>$gonder,



        );

    }

     public function kampanyapaketfiyatgetir(Request $request){

     

        $kampanya_hizmetler=PaketHizmetler::where('paket_id',$request->paket_id)->get();

        $secilihizmetler='';

        $hizmetler = array();

        foreach ($kampanya_hizmetler as $key=> $hizmet) {

            $secilihizmetler.=$hizmet->hizmet->hizmet_adi .' '; 

            array_push($hizmetler,$hizmet->hizmet_id);

        } 

        $adisyonlar = Adisyonlar::where('salon_id',$request->sube)->get();

        $adisyon_musteriler = array();



        foreach($adisyonlar as $adisyon)

        {

            foreach($adisyon->hizmetler as $adisyonhizmet)

            {

                if(in_array($adisyonhizmet->hizmet_id,$hizmetler))

                    array_push($adisyon_musteriler,$adisyon->user_id);

            }

            foreach($adisyon->paketler as $adisyonpaket)

            {

                foreach($adisyonpaket->paket->hizmetler as $pakethizmet){

                    if(in_array($pakethizmet->hizmet_id, $hizmetler))

                        array_push($adisyon_musteriler,$adisyon->user_id);

                }



            }

        }

        $musteriler = User::whereIn('id',$adisyon_musteriler)->get();

        $hizmetegoremusterihtml  ='';

        foreach($musteriler as $musteri)

        {

            $hizmetegoremusterihtml .=  '<tr>

                                                   <td> 

                                                      <div class="be-checkbox be-checkbox-color inline" >

                                                         <input type="checkbox"  name="kampanya_katilimci_musteriler[]" value="'.$musteri->id.'">'.$musteri->name.'

                                                      </div>

                                                   </td>

                                                </tr>';

        }











        return array(

          'fiyat'=>$kampanya_hizmetler->sum('fiyat'),

          'hizmetler'=>$secilihizmetler,

          'seans'=>$kampanya_hizmetler->sum('seans'),

          'hizmete_gore_musteri'=> $hizmetegoremusterihtml

        );

    }

    public function musteriportfoyeekle(Request $request)

    {

        $portfoy = new MusteriPortfoy();

        $portfoy->user_id = $request->user_id;

        $portfoy->salon_id = $request->sube;

        $portfoy->aktif = true;

        $portfoy->save();

        //return DB::table('users')->select('name as ad_soyad','id as id','m');

    }

     public function guncellemesonrasiseansdetaygetir(Request $request,$adisyonpaketid,$musteriid){



        $paket_seanslar = AdisyonPaketSeanslar::where('adisyon_paket_id',$adisyonpaketid)->get();

        $paket = AdisyonPaketler::where('id',$adisyonpaketid)->first();

        $adisyon = $paket->adisyon;

        $html  ='';

        $html2 = '';                 

        foreach($paket_seanslar as $key => $seans)

        {

            $html .= '<div class="row">

                        <div class="col-md-2">

                            <div class="form-group"> <label style="font-size:12px">'. ++$key .'. Seans Tarihi</label>';

           

                $html .= '  <input type="text" class="form-control" name="seans_tarihi_adisyon_paket" data-value="'.$seans->id.'" required value="'.$seans->seans_tarih.'">';

            

            $html .= '  </div>

                    </div>

                             

                    <div class="col-md-2">

                        <div class="form-group">

                            <label style="font-size:12px">İşlem Adı</label>';

            

                $html .= '<select class="form-control"  name="paketseanshizmet" data-value="'.$seans->id.'">

                            <option value="">Hizmet Seçimi</option>';

                foreach($paket->paket->hizmetler as $hizmet)

                {

                    if($seans->hizmet_id == $hizmet->hizmet_id)

                        $html .= '<option selected value="'.$hizmet->hizmet_id.'">'.$hizmet->hizmet->hizmet_adi.'</option>';

                    else

                        $html .= '<option value="'.$hizmet->hizmet_id.'">'.$hizmet->hizmet->hizmet_adi.'</option>';

                }

                $html .= ' </select>';

 

            $html .= '  </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <label style="font-size:12px">Personel & Cihaz</label>';

           

                $html .= ' <select name="paketseanspersonelcihaz" data-value="'.$seans->id.'" class="form-control custom-select2 opsiyonelSelect" style="width: 100%;"> <option></option>';

                foreach(\App\IsletmeYetkilileri::where('salon_id',$adisyon->salon_id)->where('aktif',true)->get() as $personel)

                {

                    if($seans->personel_id == $personel->personel_id)

                        $html .= '<option selected value="'.$personel->personel_id.'">'.$personel->name.'</option>';

                    else

                        $html .= '<option value="'.$personel->personel_id.'">'.$personel->name.'</option>';



                }

                foreach(\App\Cihazlar::where('salon_id',$adisyon->salon_id)->where('durum',true)->where('aktifmi',true)->get() as $cihaz)

                {

                    if($seans->cihaz_id == $cihaz->id)

                        $html .= '<option selected value="cihaz-'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                    else

                        $html .= '<option value="cihaz-'.$cihaz->id.'">'.$cihaz->cihaz_adi.'</option>';

                }

                $html .= '</select>';

            

            $html .= '</div>

                    </div>

                    <div class="col-md-2">

                        <div class="form-group">

                            <label style="font-size:12px">Oda (Opsiyonel)</label>';

             

                $html .= '<select name="paketseansoda" data-value="'.$seans->id.'"  class="form-control opsiyonelSelect" style="width:100%"><option></option>';

                foreach(\App\Odalar::where('salon_id',$adisyon->salon_id)->where('durum',true)->where('aktifmi',true)->get() as $oda)

                {

                    if($seans->oda_id == $oda->id)

                        $html .= '<option selected value="'.$oda->id.'">'.$oda->oda_adi.'</option>';

                    else

                        $html .= '<option value="'.$oda->id.'">'.$oda->oda_adi.'</option>';

                }

                $html .= '</select>';

            

            $html .= '</div>

                    </div>

                    <div class="col-md-2">

                        <div class="form-group">

                            <label style="font-size:12px;width: 100%;">Durum</label>';

            

                $html .= ' <select name="paketseansdurum" data-value="'.$seans->id.'" class="form-control';

                if($seans->geldi===0) $html .=' form-control-danger ';

                elseif ($seans->geldi===null) $html .= ' form-control-warning '; 

                else $html .= ' form-control-success '; 

                $html .= '" style="width: 100%;"><option style="padding:10px 0" class="btn btn-success"';

                if($seans->geldi===1) $html .= 'selected';

                $html .= ' value="1">Geldi</option><option style="padding:10px 0" class="btn btn-danger"';

                if($seans->geldi===0) $html .= 'selected';

                $html .= ' value="0">Gelmedi</option><option style="padding:10px 0" class="btn btn-warning"';

                if($seans->geldi===null) $html .= 'selected';

                $html .= ' value="2">Bekliyor</option></select>';



            

            $html .= ' </div>

                    </div>

                    <div class="col-md-1">';

            if($seans->geldi !== 1)

            {

                $html .= '<div class="form-group"><label style="visibility: hidden;">Randevu</label>';

                if($seans->randevu_olusturuldu)

                    $html .= '<button title="Seçili tarihe randevu oluşturuldu" data-value="'.$adisyon->id.'" data-index-number="'.$seans->id.'" type="button" name="randevu_olusturuldu" class="btn btn-success" style="padding: 6px;font-size: 30px"><i class="fa fa-calendar"></i></button>';

                else

                    $html .= '<button title="Seçili tarihe randevu oluşturun" data-value="'.$adisyon->id.'" data-index-number="'.$seans->id.'" type="button" name="seans_randevu_olustur" class="btn btn-primary" style="padding: 6px;font-size: 30px"><i class="fa fa-calendar"></i> </button>';

                $html .= '</div>';

            }

            $html .= ' </div></div>';

        }





        if($musteriid!='')

        {

            $adisyonlar = Adisyonlar::where('user_id',$request->musteri_id)->get();

            if($adisyonlar->count()>0)

            {

                foreach($adisyonlar as $adisyon)

                {

                    foreach(AdisyonPaketler::where('adisyon_id',$adisyon->id)->get() as $paket)

                    {

                        $html2 .= " <tr>

                                    <td>".date('d.m.Y',strtotime($paket->baslangic_tarihi))."</td>

                                    <td>".$paket->paket->paket_adi."</td>

                                    <td>

                                       <button name='paketteki_seanslari_beklemede_isaretle' title='Beklemede' class='btn btn-warning'>

                                       ".AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',null)->count()."&nbsp;

                                        <i class='fa fa-calendar'></i></button>

                                       <button name='paketteki_seanslari_geldi_isaretle' title='Geldi' class='btn btn-success'>

                                       ".AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',true)->count()."&nbsp;

                                        <i class='fa fa-check'></i></button>

                                       <button name='paketteki_seanslari_gelmedi_isaretle' title='Gelmedi' class='btn btn-danger'>

                                       ".AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',false)->count()." &nbsp;

                                        <i class='fa fa-times'></i></button>



                                    </td> 

                                    <td>

                                       <input type='hidden' name='paket_fiyati_adisyon[]' value='".$paket->fiyat."'> 

                                       ".$paket->fiyat." ₺



                                    </td>

                                    <td>

                                          

                                          <button type='button' name='paket_seans_detay_getir_modal'  data-value='".$paket->id."' class='btn btn-primary'><i class='fa fa-eye'></i></button>

                                        

                                    </td>

                                        

                                    

                                 </tr>";

                    



                                    

                    }

                    if(AdisyonPaketler::where('adisyon_id',$adisyon->id)->count()==0)

                        $html2 .= '<tr>

                                    <td colspan="5" style="text-align: center;">Kayıt Bulunamadı</td>

                                 </tr>';

                }

                

                                 

                               

            }

            else

                $html2 .= '<tr>

                                    <td colspan="5" style="text-align: center;">Kayıt Bulunamadı</td>

                                 </tr>';

        }

 

        return array(

            'html' => $html,

            'tahsilat_paket_eklenecek'=>'',

            'seanslar_liste'=>self::seans_getir($request,0,'1970-01-01 00:00:00',date('Y-m-d 23:59:59'),''),

            'html2' => $html2

        );

    }

    public function seansdetaylari(Request $request){



        $paket_seanslar = AdisyonPaketSeanslar::where('adisyon_paket_id',$request->adisyonpaketid)->get();



        $paket = AdisyonPaketler::where('id',$request->adisyonpaketid)->first();

        $adisyon = $paket->adisyon;

        $html  ='';

         $pakethizmeti = '';        

        foreach($paket_seanslar as $seans)

        {

            if($pakethizmeti != $seans->hizmet->hizmet_adi)

            {

                $html .='<div class="row"><div class="col-md-12"><h3>'.$seans->hizmet->hizmet_adi.' Seansları</h3></div></div>';

                $pakethizmeti = $seans->hizmet->hizmet_adi;

            }

            $html .= '<div class="row" style="margin-bottom:10px;background:#e2e2e2">

                        <div class="col-md-2 col-sm-6 col-xs-6 col-6">

                            <div class="form-group"> <label style="font-size:12px">'. $seans->seans_no .'. Seans Tarihi</label><br>';

             

                $html .= date('d.m.Y',strtotime($seans->seans_tarih)).'<input style="display:none" type="text" class="form-control" name="seans_tarihi_adisyon_paket" data-value="'.$seans->id.'" required value="'.$seans->seans_tarih.'">';

            

            $html .= '  </div>

                    </div>

                             

                    <div class="col-md-1 col-sm-6 col-xs-6 col-6">

                        <div class="form-group">

                            <label style="font-size:12px">Saati</label><br>';

            $html .= '<p>';

            if($seans->randevu_id !== null)

                $html .= date('H:i', strtotime(RandevuHizmetler::where('hizmet_id',$seans->hizmet_id)->where('randevu_id',$seans->randevu_id)->value('saat'))); 

            else

                $html .= 'Belirtilmemiş';

            $html .= '</p>';

                

            

            $html .= '  </div>

                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-6 col-6">

                        <div class="form-group">

                            <label style="font-size:12px">Personel</label><br>';

            $html .= '<p>';

            if($seans->randevu_id !== null && RandevuHizmetler::where('randevu_id',$seans->randevu_id)->where('hizmet_id',$seans->hizmet_id)->value('personel_id') !== null)

                $html .= Personeller::where('id',RandevuHizmetler::where('randevu_id',$seans->randevu_id)->where('hizmet_id',$seans->hizmet_id)->value('personel_id') )->value('personel_adi');

            else

                $html .='Belirtilmemiş';

            $html .= '</p>';

            $html .= '  </div>

                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-6 col-6">

                        <div class="form-group">

                            <label style="font-size:12px">Cihaz</label><br>';

            if($seans->cihaz_id !== null)

                $html .= Cihazlar::where('id',$seans->cihaz_id)->value('cihaz_adi');

            else

                $html .= ' Belirtilmemiş';

            $html .= '</p>';        

            

            $html .= '</div>

                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-6 col-6">

                        <div class="form-group">

                           <label style="font-size:12px">Oda</label><br>';

            $html .= '<p>';

            if($seans->oda_id !== null)

                $html .= Odalar::where('id',$seans->oda_id)->value('oda_adi');

            else

                $html .= ' Belirtilmemiş';



            $html .= '</p>';        

           

            $html .= '</div>

                    </div>

                    <div class="col-md-2 col-sm-3 col-xs-3 col-3">

                        <div class="form-group">

                            <label style="font-size:12px;width: 100%;">Durum</label><p>';

            if($seans->geldi === 0)

                $html .= '<button type="button" class="btn btn-danger">Gelmedi</button>';



            if($seans->geldi === 1)

                $html .= '<button type="button" class="btn btn-success">Geldi</button>';

            if($seans->geldi === null)

                $html .= '<button type="button" class="btn btn-warning">Bekliyor</button>';

                              

           

            $html .= '</p> </div>

                    </div>

                    <div class="col-md-1 col-sm-3 col-xs-3 col-3">';

            if($seans->geldi != 1 && $seans->randevu_id !== null)

            {



                $html .= '<div class="form-group"><label style="visibility: hidden;">Randevu</label>';

                 

                $html .= '<a href="#"  title="Randevu Düzenle" data-value="'.$seans->randevu_id.'" name="randevu_duzenle" class="btn btn-success" style="padding: 6px;"><i class="fa fa-calendar"></i></a>';

                

                $html .= '</div>';

            }

            $html .= ' </div></div>';

        }

        return $html;

    }

    public function senetvadeguncelle(Request $request) 

    {

        $vade = SenetVadeleri::where('id',$request->vade_id)->first();

        $senet = Senetler::where('id',$vade->senet_id)->first();

        $eskivadetarihi = $vade->vade_tarih;

        $vade->vade_tarih = $request->planlanan_odeme_tarihi;

        

        $vade->notlar = $request->notlar;

        $vade->save();

        $vadeler = SenetVadeleri::where('senet_id',$vade->senet_id)->get();

        $html = '';

        foreach($vadeler as $key => $vade)

        {

            $html .= ' <a style="width:100%"

                           data-toggle="modal" data-target="#senet_onay_modal" 

                           class="list-group-item list-group-item-action" name="senet_vadesi"  data-value="'.$vade->id.'"

                           >Vade '.++$key .' : <b>'.number_format($vade->tutar,2,',','.').' ₺</b>';

            if($vade->odendi != true && $vade->notlar != null)

                $html.=' ('.$vade->notlar.')';

            $html.='<input type="hidden" name="vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

            if($vade->odendi==true)

                $html .= '<button type="button" class="btn btn-success" style="float:right">Ödendi ('.$vade->odeme_turu->odeme_yontemi.')</button>';

            else{

                if(date('Y-m-d') > $vade->vade_tarih)

                    $html .= '<button type="button" class="btn btn-danger" style="float:right">Ödenmedi</button>';

                else

                    $html .= '<button type="button"  class="btn btn-primary" style="float:right">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</button>';

            }

            $html .= '</a>';

        }

        /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($eskivadetarihi))." vade tarihli ".number_format($vade->tutar,2,',','.')." TL tutarlı senedinizin vade tarihi ".date('d.m.Y',strtotime($request->planlanan_odeme_tarihi))." olarak güncellenmiştir. Detaylı bilgi için bize ulaşın. 0".$senet->salon->telefon_1 )),false,1,false);

        

        $yetkili_liste = self::yetkili_telefonlari($request);

        foreach($yetkili_liste as $_yetkili)

        {

            self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::guard('isletmeyonetim')->user()->name .' tarafından '.date('d.m.Y',strtotime($eskivadetarihi))." vade tarihli ve tutarı ".number_format($vade->tutar,2,',','.')." TL olan senedin vade tarihi ".date('d.m.Y',strtotime($request->planlanan_odeme_tarihi)). " olarak güncellenmiştir.")),false,1,false);

        }*/

        

        return array(

            'vadeler'=>$html,

            'senetler'=>self::senetleri_getir($request,'',''),

            'senetler_acik'=>self::senetleri_getir($request,0,''),

            'senetler_kapali'=>self::senetleri_getir($request,1,''),

            'senetler_odenmemis'=>self::senetleri_getir($request,2,''),

        );



    }

     public function taksitvadeguncelle(Request $request) 

    {

        $vade = TaksitVadeleri::where('id',$request->vade_id)->first();

        $taksit = TaksitliTahsilatlar::where('id',$vade->taksitli_tahsilat_id)->first();

       

        $eskivadetarihi = $vade->vade_tarih;

        $vade->vade_tarih = $request->planlanan_odeme_tarihi;

        

        $vade->notlar = $request->notlar;

        $vade->save();

        $vadeler = TaksitVadeleri::where('taksitli_tahsilat_id',$vade->taksitli_tahsilat_id)->get();

        $html = '';

        foreach($vadeler as $key => $vade)

        {

            $html .= ' <a style="width:100%"

                           data-toggle="modal" data-target="#taksit_onay_modal" 

                           class="list-group-item list-group-item-action" name="taksit_vadesi"  data-value="'.$vade->id.'"

                           >Vade '.++$key .' : <b>'.number_format($vade->tutar,2,',','.').' ₺</b>';

            if($vade->odendi != true && $vade->notlar != null)

                $html.=' ('.$vade->notlar.')';

            $html.='<input type="hidden" name="vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

            if($vade->odendi==true)

                $html .= '<button type="button" class="btn btn-success" style="float:right">Ödendi ('.$vade->odeme_turu->odeme_yontemi.')</button>';

            else{

                if(date('Y-m-d') > $vade->vade_tarih)

                    $html .= '<button type="button" class="btn btn-danger" style="float:right">Ödenmedi</button>';

                else

                    $html .= '<button type="button"  class="btn btn-primary" style="float:right">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</button>';

            }

            $html .= '</a>';

        }

        /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($eskivadetarihi))." vade tarihli ".number_format($vade->tutar,2,',','.')." TL tutarlı senedinizin vade tarihi ".date('d.m.Y',strtotime($request->planlanan_odeme_tarihi))." olarak güncellenmiştir. Detaylı bilgi için bize ulaşın. 0".$senet->salon->telefon_1 )),false,1,false);

        

        $yetkili_liste = self::yetkili_telefonlari($request);

        foreach($yetkili_liste as $_yetkili)

        {

            self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::guard('isletmeyonetim')->user()->name .' tarafından '.date('d.m.Y',strtotime($eskivadetarihi))." vade tarihli ve tutarı ".number_format($vade->tutar,2,',','.')." TL olan senedin vade tarihi ".date('d.m.Y',strtotime($request->planlanan_odeme_tarihi)). " olarak güncellenmiştir.")),false,1,false);

        }*/

        

        return array(

            'vadeler'=>$html,

             

        );



    }

    public function senetvadeodemeyitamamla(Request $request)

    {

        $vade = SenetVadeleri::where('id',$request->vade_id)->first();

        $senet = Senetler::where('id',$vade->senet_id)->first();

        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$senet->salon_id)->where('ayar_id',16)->value('musteri');

        if(($dogrulama_kodu_ayari && $vade->dogrulama_kodu == $request->dogrulama_kodu) || !$dogrulama_kodu_ayari)

        {

            $vade->odendi = true;

            $vade->odeme_yontemi_id = $request->odeme_yontemi;

            $vade->save();

            $vadeler = SenetVadeleri::where('senet_id',$vade->senet_id)->get();

            

            $html = '';

            /*foreach($vadeler as $key => $vade)

            {

                $html .= ' <a style="width:100%"

                               data-toggle="modal" data-target="#senet_onay_modal" 

                               class="list-group-item list-group-item-action" name="senet_vadesi"  data-value="'.$vade->id.'"

                               >Vade '.++$key.'<input type="hidden" name="vade_tarihi" data-value="'.$vade->id.'" value="'.$vade->vade_tarih.'">';

                if($vade->odendi==true)

                    $html .= '<button type="button" class="btn btn-success" style="float:right">Ödendi</button>';

                else{

                    if(date('Y-m-d') > $vade->vade_tarih)

                        $html .= '<button type="button" class="btn btn-danger" style="float:right">Ödenmedi</button>';

                    else

                        $html .= '<button type="button"  class="btn btn-primary" style="float:right">'.date('d.m.Y', strtotime($vade->vade_tarih)).'</button>';

                }

                $html .= '</a>';

            }*/

            $tahsilat = new Tahsilatlar();

            

            $tahsilat->adisyon_id = Senetler::where('id',$vade->senet_id)->value('adisyon_id');

            $tahsilat->tutar = $vade->tutar;

            $tahsilat->odeme_tarihi = date('Y-m-d');    

            $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

            $tahsilat->salon_id = $request->sube;



            $tahsilat->yapilan_odeme = $vade->tutar;

            $tahsilat->odeme_yontemi_id = 5;

            $tahsilat->notlar = $vade->senet_id.' nolu senedin '.date('d.m.Y', strtotime($vade->vade_tarih)).' tarihli vadesinin ödemesi';

            $tahsilat->save();

            /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($vade->vade_tarih))." vade tarihli ".number_format($vade->tutar,2,',','.')." TL tutarlı senedinizin ödemesi ".OdemeYontemleri::where('id',$request->odeme_yontemi_id)->value('odeme_yontemi')." ödeme olarak gerçekleştirilmiştir." )),false,1,false);

        

            $yetkili_liste = self::yetkili_telefonlari($request);

            foreach($yetkili_liste as $_yetkili)

            {

                self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::guard('isletmeyonetim')->user()->name .' tarafından '.date('d.m.Y',strtotime($vade->vade_tarih))." vade tarihli ve tutarı ".number_format($vade->tutar,2,',','.')." TL olan senedin ödemesi ".OdemeYontemleri::where('id',$request->odeme_yontemi_id)->value('odeme_yontemi')." ödeme olarak tahsil edilmiştir.")),false,1,false);

            }*/

            $tahsilatlar = '';

            if($request->adisyon_id)

                $tahsilatlar = self::adisyon_tahsilatlari($request,'');



            return array(

                'vadeler'=>$html,

                'senetler'=>self::senetleri_getir($request,'',''),

                'senetler_acik'=>self::senetleri_getir($request,0,''),

                'senetler_kapali'=>self::senetleri_getir($request,1,''),

                'senetler_odenmemis'=>self::senetleri_getir($request,2,''),

                'status'=>'Başarılı',

                'tahsilatlar' => $tahsilatlar,

            );

            exit;

        }

        else{

            return array(

                'vadeler'=>'',

                'senetler'=>'',

                'status'=>'hatalikod',

                'tahsilatlar' => $tahsilatlar,

            );

            exit;

        }

            

        

    }

    public function taksitvadeodemeyitamamla(Request $request)

    {

         

        $vadeler = TaksitVadeleri::whereIn('id',$request->vade_id)->get();

        $vade = TaksitVadeleri::whereIn('id',$request->vade_id)->first();

        $taksitlitahsilat = TaksitliTahsilatlar::where('id',$vade->taksitli_tahsilat_id)->first();

        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$taksitlitahsilat->salon_id)->where('ayar_id',16)->value('musteri');

        if(($dogrulama_kodu_ayari && $vade->dogrulama_kodu == $request->dogrulama_kodu) || !$dogrulama_kodu_ayari)

        {



            foreach($vadeler as $vade)

            {

                $vade->odendi = true;

                $vade->odeme_yontemi_id = $request->odeme_yontemi;

                $vade->save();

               

                $tahsilat = new Tahsilatlar();

                

                $tahsilat->adisyon_id = TaksitliTahsilatlar::where('id',$vade->taksitli_tahsilat_id)->value('adisyon_id');



                $tahsilat->user_id =$request->musteri_id;

                $tahsilat->tutar = $vade->tutar;

                $tahsilat->odeme_tarihi = date('Y-m-d');    

                $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

                $tahsilat->salon_id = $request->sube;



                $tahsilat->yapilan_odeme = $vade->tutar;

                $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;

                $tahsilat->notlar = $tahsilat->adisyon_id.' nolu adisyonun '.date('d.m.Y', strtotime($vade->vade_tarih)).' tarihli vadesinin ödemesi';

                $tahsilat->save();

                if(isset($request->adisyon_hizmet_id)  &&isset($request->adisyon_hizmet_taksitli_tahsilat_id))

                {

                    foreach($request->adisyon_hizmet_id as $key=>$hizmet_id)

                    {        

                        

                        if($request->adisyon_hizmet_taksitli_tahsilat_id[$key] != '')

                        {

                            $taksit_toplami = TaksitVadeleri::where('taksitli_tahsilat_id',$request->adisyon_hizmet_taksitli_tahsilat_id[$key])->sum('tutar');

                            $odeme = new TahsilatHizmetler();

                            $odeme->adisyon_hizmet_id = $hizmet_id;

                            $odeme->tahsilat_id = $tahsilat->id;

                            $hizmet_tahsilat_tutar = 0;

                            if(isset($request->himzet_tahsilat_tutari_girilen))

                                $hizmet_tahsilat_tutar = $request->himzet_tahsilat_tutari_girilen[$key];

                            else

                                $hizmet_tahsilat_tutar = $request->adisyon_hizmet_tahsilat_tutari[$key];

                            $odeme->tutar = (str_replace(['.',','],['','.'],$hizmet_tahsilat_tutar)/str_replace(['.',','],['','.'],$taksit_toplami))*str_replace(['.',','],['','.'],$vade->tutar);



                            $odeme->save();

                        }

                       

                        



                    }

                 

                }

                if(isset($request->adisyon_urun_id) &&  isset($request->adisyon_urun_taksitli_tahsilat_id))

                {

                    foreach($request->adisyon_urun_id as $key2=>$urun_id)

                    {

                       

                        if($request->adisyon_urun_taksitli_tahsilat_id[$key2] != '')

                        {

                            $taksit_toplami = TaksitVadeleri::where('taksitli_tahsilat_id',$request->adisyon_urun_taksitli_tahsilat_id[$key2])->sum('tutar');

                            $odeme = new TahsilatUrunler();

                            $odeme->adisyon_urun_id = $urun_id;

                            $odeme->tahsilat_id = $tahsilat->id;

                            $urun_tahsilat_tutar = 0;

                            if(isset($request->urun_tahsilat_tutari_girilen))

                                $urun_tahsilat_tutar = $request->urun_tahsilat_tutari_girilen[$key2];

                             else

                                $urun_tahsilat_tutar = $request->adisyon_urun_tahsilat_tutari[$key2];

                            $odeme->tutar = (str_replace(['.',','],['','.'],$urun_tahsilat_tutar)/str_replace(['.',','],['','.'],$taksit_toplami))*str_replace(['.',','],['','.'],$vade->tutar);



                            $odeme->save();

                        }



                    }

                }

                if(isset($request->adisyon_paket_id) &&  isset($request->adisyon_paket_taksitli_tahsilat_id))

                {

                    foreach($request->adisyon_paket_id as $key3=>$paket_id)

                    {

                         

                        if($request->adisyon_paket_taksitli_tahsilat_id[$key3] != '')

                        {

                            $taksit_toplami = TaksitVadeleri::where('taksitli_tahsilat_id',$request->adisyon_paket_taksitli_tahsilat_id[$key3])->sum('tutar');

                            $odeme = new TahsilatPaketler();

                            $odeme->adisyon_paket_id = $paket_id;

                            $odeme->tahsilat_id = $tahsilat->id;

                            $paket_tahsilat_tutar = 0;

                            if(isset($request->paket_tahsilat_tutari_girilen))

                                 $paket_tahsilat_tutar= $request->paket_tahsilat_tutari_girilen[$key3];

                             else

                                $paket_tahsilat_tutar =$request->adisyon_paket_tahsilat_tutari[$key3];

                            $odeme->tutar = (str_replace(['.',','],['','.'],$paket_tahsilat_tutar)/str_replace(['.',','],['','.'],$taksit_toplami))*str_replace(['.',','],['','.'],$vade->tutar);



                            $odeme->save();       

                        } 

                    }

                }



            }

            

            /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($vade->vade_tarih))." vade tarihli ".number_format($vade->tutar,2,',','.')." TL tutarlı senedinizin ödemesi ".OdemeYontemleri::where('id',$request->odeme_yontemi_id)->value('odeme_yontemi')." ödeme olarak gerçekleştirilmiştir." )),false,1,false);

        

            $yetkili_liste = self::yetkili_telefonlari($request);

            foreach($yetkili_liste as $_yetkili)

            {

                self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::guard('isletmeyonetim')->user()->name .' tarafından '.date('d.m.Y',strtotime($vade->vade_tarih))." vade tarihli ve tutarı ".number_format($vade->tutar,2,',','.')." TL olan senedin ödemesi ".OdemeYontemleri::where('id',$request->odeme_yontemi_id)->value('odeme_yontemi')." ödeme olarak tahsil edilmiştir.")),false,1,false);

            }*/

            

             

            

            if(isset($request->tahsilatekrani))

            {

                return array(

                    'vadeler'=>'', 

                    'status'=>'Başarılı',

                    'tahsilatlar' => self::musteri_tahsilatlari($request,$request->musteri_id,"")

                );

                exit;

            }

            else

            {

                return array(

                    'vadeler'=>'', 

                    'status'=>'Başarılı',

                    'tahsilatlar' => self::adisyon_tahsilatlari($request,'')

                );

                exit;

            }

            

        }

        else{

            return array(

                'vadeler'=>'',

                'senetler'=>'',

                'status'=>'hatalikod',

                'tahsilatlar' => self::adisyon_tahsilatlari($request,'')

            );

            exit;

        }

            

        

    }

    public function senet_odeme_dogrulama_kodu_gonder(Request $request)

    {

        $vade = SenetVadeleri::where('id',$request->vade_id)->first();

        $senet = Senetler::where('id',$vade->senet_id)->first();

        $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);      

        $vade->dogrulama_kodu = $kod;

        $vade->save(); 

        $mesaj = array(

            array("to"=>$senet->musteri->cep_telefon,"message"=>"Doğrulama kodunuz : ".$kod),



        ); 

        self::sms_gonder($request,$mesaj,false,1,true);

        return '';



    }

    public function taksit_odeme_dogrulama_kodu_gonder(Request $request)

    {

        $vade = TaksitVadeleri::where('id',$request->vade_id)->first();

        $taksitlitahsilat = TaksitliTahsilatlar::where('id',$vade->taksitli_tahsilat_id)->first();

        $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);      

        $vade->dogrulama_kodu = $kod;

        $vade->save(); 

        $mesaj = array(

            array("to"=>$taksitlitahsilat->musteri->cep_telefon,"message"=>"Doğrulama kodunuz : ".$kod),



        ); 

        self::sms_gonder($request,$mesaj,false,1,true);

        return '';



    }

    public function yetkili_telefonlari(Request $request)

    {

        $yetkili_liste = array();

        foreach(IsletmeYetkilileri::where('salon_id',$request->sube)->get() as $yetkili)

        {

            if(!$yetkili->hasRole('Personel'))

                array_push($yetkili_liste,$yetkili->gsm1);



        }

        return $yetkili_liste;

    }

    public function musterikaralisteayari(Request $request)

    {   

        $portfoy = MusteriPortfoy::where('user_id',$request->user_id)->where('salon_id',$request->sube)->first();

        $portfoy->kara_liste = $request->karaliste;

        $portfoy->save();



        if(SalonSMSAyarlari::where('ayar_id',15)->where('salon_id',$portfoy->salon_id)->value('musteri')==1 && $request->karaliste==1)

        {

            

                $mesaj = array(

                    array("to"=>$portfoy->users->cep_telefon,"message"=>"Sayın ".$portfoy->users->name.", isteğiniz doğrultusunda telefon numaranız kara listeye alınmıştır. Kampanya ve reklam SMS leri gönderilmeyecektir. Detaylı bilgi için bize ulaşınız. 0".Salonlar::where('id',$request->sube)->value('telefon_1')),



                ); 

            

            self::sms_gonder($request,$mesaj,false,1,false);

        } 

        

        return DB::table('users')->join('musteri_portfoy','musteri_portfoy.user_id','=','users.id')->select(

            'users.name as ad_soyad', 

            'users.cep_telefon as telefon',

            DB::raw('DATE_FORMAT(musteri_portfoy.updated_at,"%d.%m.%Y") as eklenme_tarihi'),

            DB::raw('CONCAT("<button class=\"btn btn-primary\" name=\"numara_karalisteden_kaldir\" data-value=\"",users.id,"\">Numarayı Listeden Kaldır</button>") AS islemler')



        )->where('musteri_portfoy.salon_id',$portfoy->salon_id)->where('musteri_portfoy.kara_liste',1)->get();

    }

    public function musteridetaybilgi(Request $request)

    {

        $musteri = User::where('id',$request->musteriid)->first();

        $portfoy = MusteriPortfoy::where('user_id',$request->musteriid)->where('salon_id',$request->sube)->first();

        return array(

            'ad_soyad' =>$musteri->name,

            'cep_telefon' =>$musteri->cep_telefon,

            'eposta'=>$musteri->email,

            'dogum_tarihi_gun'=>date('d',strtotime($musteri->dogum_tarihi)),

            'dogum_tarihi_ay'=>date('m',strtotime($musteri->dogum_tarihi)),

            'dogum_tarihi_yil'=>date('Y',strtotime($musteri->dogum_tarihi)),

            'cinsiyet'=>$musteri->cinsiyet,

            'tc'=>$musteri->tc_kimlik_no,

            'referans'=>$portfoy->musteri_tipi,

            'notlar'=>$musteri->ozel_notlar



        );

    }

    public function sms_raporlari(Request $request)

    {

        $isletme =Salonlar::where('id',self::mevcutsube($request))->first();

        $smsraportoplu = DB::table('sms_iletim_raporlari')->select(DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(updated_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(updated_at,"%d.%m.%Y %H:%i:%s")) as date'),'adet as count','kredi as price','aciklama as msgdetails', 'durum as status')->where('salon_id',self::mevcutsube($request))->where('tur',4)->get();

        $smsraporbildirim = DB::table('sms_iletim_raporlari')->select(DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(updated_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(updated_at,"%d.%m.%Y %H:%i:%s")) as date'),'adet as count','kredi as price','aciklama as msgdetails', 'durum as status')->where('salon_id',self::mevcutsube($request))->where(function($q){$q->where('tur',1); $q->orWhere('tur',''); $q->orWhere('tur',null);})->get();

        $smsraporgrup = DB::table('sms_iletim_raporlari')->select(DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(updated_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(updated_at,"%d.%m.%Y %H:%i:%s")) as date'),'adet as count','kredi as price','aciklama as msgdetails', 'durum as status')->where('salon_id',self::mevcutsube($request))->where('tur',2)->get();

        $smsraporfiltre = DB::table('sms_iletim_raporlari')->select(DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(updated_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(updated_at,"%d.%m.%Y %H:%i:%s")) as date'),'adet as count','kredi as price','aciklama as msgdetails', 'durum as status')->where('salon_id',self::mevcutsube($request))->where('tur',3)->get();

        $smsraporkampanya = DB::table('sms_iletim_raporlari')->select(DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(updated_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(updated_at,"%d.%m.%Y %H:%i:%s")) as date'),'adet as count','kredi as price','aciklama as msgdetails', 'durum as status')->where('salon_id',self::mevcutsube($request))->where('tur',5)->get();

        $smsraporetkinlik = DB::table('sms_iletim_raporlari')->select(DB::raw('CONCAT("<span style=\"display:none\">",DATE_FORMAT(updated_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(updated_at,"%d.%m.%Y %H:%i:%s")) as date'),'adet as count','kredi as price','aciklama as msgdetails', 'durum as status')->where('salon_id',self::mevcutsube($request))->where('tur',6)->get();

        

        return array(

            'toplu' => $smsraportoplu,

            'bildirim' => $smsraporbildirim,

            'grup' => $smsraporgrup,

            'filtre' => $smsraporfiltre,

            'kampanya' => $smsraporkampanya,

            'etkinlik' => $smsraporetkinlik,

        );

    }

    public function smsraportest(Request $request)

    {



        $mesajlar = array(array("to"=>'5316237563',"message"=> 'Test'));

        $headers = array(

             'Authorization: Key 9cOnA33ZqICtTZGXC08vUSLsYr7QX2lCyb9ZRL0syr5S',

             'Content-Type: application/json',

             'Accept: application/json'

            );

            $postData = json_encode( array( "originator"=> 'ADALIFE', "messages"=> $mesajlar,"encoding"=>"auto") );



            $ch=curl_init();

            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');

            curl_setopt($ch,CURLOPT_NOBODY ,false);

            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);

            curl_setopt($ch,CURLOPT_POST,1);

            curl_setopt($ch,CURLOPT_TIMEOUT,5);

            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

                    

            $response=curl_exec($ch);

            if(curl_exec($ch) === false)

        {

            echo 'Curl error: ' . curl_error($ch);

        }

        else

        {

            echo 'Operation completed without any errors';

        }

            $decoded = json_decode($response,true);

            echo $response;

        //return self::sms_rapor_getir(892524,Salonlar::where('id',114)->first());

    }

    public function sms_rapor_getir($raporid,$isletme)

    {

        $headers = array(

                     'Authorization: Key '.$isletme->sms_apikey,

                     'Content-Type: application/json',

                     'Accept: application/json'

        );

        $postData = json_encode( array( "originator"=> $isletme->sms_baslik, "id"=> $raporid) );



        $ch=curl_init();

        curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/get/report');

        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);

        curl_setopt($ch,CURLOPT_POST,1);

        curl_setopt($ch,CURLOPT_TIMEOUT,5);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response,true);



    }

    public function personelsifregonder(Request $request)

    {

        $personel = Personeller::where('id',$request->personelid)->first();

        $yetkili = IsletmeYetkilileri::where('id',$personel->yetkili_id)->first();

        $random = str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');

        $kod = substr($random, 0, 6);



        $yetkili->password = Hash::make($kod);

        $yetkili->save();

        $personel->sifre = $kod;

        $personel->save();

        $mesajlar = array(array("to"=>$yetkili->gsm1,"message"=> "Sayın ".$yetkili->name.". Randevumcepte yeni şifreniz : ".$kod. ' olarak '.Auth::guard('isletmeyonetim')->user()->name.' tarafından belirlenmiştir.'));

        self::sms_gonder($request,$mesajlar,false,1,false);

        return $yetkili->name.' isimli kullanıcının şifresi başarıyla değiştirilip telefon numarasına iletilmiştir';

              

    }

    public function personelaktifpasifyap(Request $request)

    {

        $yetkili = Personeller::where('id',$request->personelid)->first();

        $returntext = '';

        if($request->aktif==1){

            $yetkili->aktif = true;

            $returntext = 'aktif';

        }

        else{

             $yetkili->aktif = false;

             $yetkili->save(); 

        }

        $yetkili->save(); 

        return array(

            'mesaj'=>$yetkili->name.' isimli personel başarıyla '.$returntext.' edildi',

            'personeller'=>self::personel_liste_getir($request)

        );





    }

    public function grupsmsekleduzenle(Request $request)

    {

        $grupsms = "";

        if(isset($request->grup_id))

            $grupsms = GrupSms::where('id',$request->grup_id)->first();

        else

            $grupsms = new GrupSms();

        $grupsms->grup_adi = $request->grup_ad;

        $grupsms->salon_id = self::mevcutsube($request);

        $grupsms->aktif_mi = true;

        $grupsms->save();

        $grup_katilimcilar = GrupSmsKatilimcilari::where('grup_id',$grupsms->id)->delete();

        foreach($request->duallistbox_demo1 as $key => $grup_katilimci)

        {

            $grup_yenikatilimci = new GrupSmsKatilimcilari();

            $grup_yenikatilimci->grup_id = $grupsms->id;

            $grup_yenikatilimci->user_id = $grup_katilimci;

            $grup_yenikatilimci->save();

        }

        return array(



          "mesaj" => "Grup Başarıyla Oluşturuldu",

          'grup' => self::grup_sms_liste_getir($request)



        );





    }

    public function urun_guncelle(Request $request){

        $urun="";

        $returntext="";

        if($request->urun_id_duzenle == 0){

            $urun = new Urunler();

            $returntext = "Ürün başarıyla eklendi";

        }

        else{

            $urun = Urunler::where('id',$request->urun_id_duzenle)->first();

            $returntext = "Ürün başarıyla güncellendi";

        }

        $urun->urun_adi = $request->urun_ad;

        $urun->fiyat = $request->fiyat_duzenle;

        $urun->barkod = $request->barkod_duzenle;

        $urun->stok_adedi = $request->stok_aded;

        $urun->dusuk_stok_siniri = $request->dusuk_stok_siniri;

        $urun->salon_id = self::mevcutsube($request);



        $urun->save();



        

        return self::urun_liste_getir($request,$returntext);



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

    public function bildirimgonder($bildirimkimlikleri,$mesaj,$baslik,$salonid){

        $salon = Salonlar::where('id',$salonid)->first();

        $post_url_push_notification = "https://onesignal.com/api/v1/notifications";



        $headers_push_notification = array(

                                        'Accept: application/json',

                                        'Authorization: Basic MjFiNDE3ZGQtZjY3ZC00OTE3LWI1NWQtMjBlMjcxODgxNjFj',

                                        'Content-Type: application/json',

        );



         

        $post_data_push_notification = 

            json_encode( 

            

                array( 

                    "app_id"=> $salon->bildirim_app_id,

                 

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

    public function isletmegorselekle(Request $request)

    {

        if(isset($_FILES["isletmegorselleri"]["name"])){

            $salongorselleri = '';

            for($i=0;$i<count($_FILES["isletmegorselleri"]["name"]);$i++){

                $salongorselleri = new SalonGorselleri(); 

                $image = $request->isletmegorselleri[$i];

                $filename = strtotime(date('H:i:s')). '-' .$_FILES["isletmegorselleri"]["name"][$i] . '.' . $image->getClientOriginalExtension();

                Image::make($image)->resize(null, 720, function ($constraint) {

                    $constraint->aspectRatio();

                })->save( public_path('/salon_gorselleri/' . $filename) );      

                $salongorselleri->salon_gorseli = "/public/salon_gorselleri/".$filename;

                $salongorselleri->salon_id = $request->sube;

                $salongorselleri->kapak_fotografi = 0;

                $salongorselleri->save();

            }

               

        }

        $gorselsayisi = SalonGorselleri::where('salon_id',$request->sube)->where('kapak_fotografi','!=',1)->count();

        $kalan = 12-$gorselsayisi;

        return array(

            'eklemetext' => 'İşletme Görsellerini Ekleyin (Max:'.$kalan.' adet)',

            'gorseller_html' => self::salon_gorselleri($request),

        );







    }

    public function salon_gorselleri(Request $request)

    {

        $gorseller_html = '';

        $salongorselleri = SalonGorselleri::where('salon_id',self::mevcutsube($request))->where('kapak_fotografi','!=',1)->get();

        foreach($salongorselleri as $key => $value){

            $gorselindex = $key+1;

            $gorseller_html .= '<li class="col-lg-3 col-md-3 col-sm-12">

                                    <div class="da-card box-shadow">

                                       <div class="da-card-photo">

                                          <img  id="gorsel'.$gorselindex.'" src="'.$value->salon_gorseli.'" alt="" />

                                          <div class="da-overlay">

                                             <div class="da-social">

                                                <h5 class="mb-10 color-white pd-20">

                                                  

                                                </h5>

                                                <ul class="clearfix">

                                                   <li>

                                                      <a

                                                         href="'.$value->salon_gorseli.'"

                                                         data-fancybox="images"

                                                         ><i class="fa fa-picture-o"></i

                                                      ></a>

                                                   </li>

                                                   <li>

                                                      <a href="#" name="gorsel_sil"  data-value="'.$value->id.'"><i class="fa fa-times"></i></a>

                                                   </li>

                                                </ul>

                                             </div>

                                          </div>

                                       </div>

                                    </div>

                                 </li>';

        }

        if($salongorselleri->count()<12){

            for($i=$salongorselleri->count()+1;$i<=12;$i++){

                $gorseller_html .='<li class="col-lg-3 col-md-3 col-sm-12">

                                    <div class="da-card box-shadow">

                                       <div class="da-card-photo">

                                          <img id="gorsel'.$i.'" src="/public/yeni_panel/vendors/images/product-img4.jpg" alt="" />

                                          <div class="da-overlay">

                                             <div class="da-social">

                                                <h5 class="mb-10 color-white pd-20">

                                                   İşletmenizin görsellerini ekleyebilirsiniz.

                                                </h5>

                                                <ul class="clearfix">

                                                   <li>

                                                      <a

                                                         src="/public/yeni_panel/vendors/images/product-img4.jpg"

                                                         data-fancybox="images"

                                                         ><i class="fa fa-picture-o"></i

                                                      ></a>

                                                   </li>

                                                   

                                                </ul>

                                             </div>

                                          </div>

                                       </div>

                                    </div>

                                 </li>';

            }

        } 

        return $gorseller_html;

    }

    public function sms_bakiye_sorgulama(Request $request)

    {

        $isletme =Salonlar::where('id',self::mevcutsube($request))->first();

        $headers = array(

                     'Authorization: Key '.$isletme->sms_apikey,

                     'Content-Type: application/json',

                     'Accept: application/json'

        );

       



        $ch=curl_init();

        curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/get/balance');

      

        curl_setopt($ch,CURLOPT_POST,1);

        curl_setopt($ch,CURLOPT_TIMEOUT,5);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response,true);



    }

    function adisyon_hizmet_getir($hizmetid){

        $adisyon = Adisyonlar::where('id',AdisyonHizmetler::where('id',$hizmetid)->value('adisyon_id'))->first();

        $html = '';

        foreach($adisyon->hizmetler as $hizmet)

        {

            $html .= '<input type="hidden" name="adisyonhizmetidleri[]" value="'.$hizmet->hizmet_id.'">

                  <div class="row" data-value="0" style="background-color:#e2e2e2;margin-bottom: 5px;">

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>İşlem Tarihi</label>

                           <input type="text" required class="form-control date-picker" data-value="'.$hizmet->id.'" name="hizmet_islem_tarihi" value="'.$hizmet->islem_tarihi.'">

                        </div>

                     </div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>İşlem Saati</label>

                           <input type="time" required class="form-control" data-value="'.$hizmet->id.'" name="hizmet_saati_tarihi" value="'.$hizmet->islem_saati.'">

                        </div>

                     </div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>Personel</label>

                           <select name="islem_personelleri" class="form-control custom-select2"  data-value="'.$hizmet->id.'" style="width: 100%;">';

            foreach(Personeller::where('salon_id',$adisyon->salon_id)->get() as $personel){

                if($hizmet->personel_id == $personel->id)

                    $html .= '<option selected value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

                else

                    $html .= '<option  value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

            }

            $html .= '</select>

                        </div>

                     </div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>Hizmet</label>

                           <select name="islem_hizmetleri" data-value="{{$hizmet->id}}" class="form-control custom-select2" style="width: 100%;">';

            foreach(SalonHizmetler::where('salon_id',$adisyon->salon_id)->get() as $hizmetliste)

            {

                if($hizmet->hizmet_id == $hizmetliste->hizmet_id)

                    $html .= '<option selected value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

                else

                    $html .= '<option value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

            }

            $html .= '</select>

                        </div>

                     </div>

                     <div class="col-md-2">

                        <div class="form-group">

                           <label>Durum</label>';

            if($hizmet->geldi===1){

                $html .= '<select name="adisyon_hizmet_durum" data-value="'.$hizmet->id.'" class="form-control form-control-success" >

                    <option value="1" class="btn btn-success" selected>Geldi</option>

                    <option value="0" class="btn btn-danger" >Gelmedi</option>

                    <option value="2" class="btn btn-warning">Bekliyor</option> ';

            }

            elseif($hizmet->geldi===0)

                $html .= '<select name="adisyon_hizmet_durum" data-value="'.$hizmet->id.'" class="form-control form-control-danger" >

                     <option value="1" class="btn btn-success" >Geldi</option>

                    <option value="0" class="btn btn-danger" selected>Gelmedi</option>

                    <option value="2" class="btn btn-warning">Bekliyor</option> ';

            else

                $html .= '<select name="adisyon_hizmet_durum" data-value="'.$hizmet->id.'" class="form-control form-control-warning" ><option value="1" class="btn btn-success" >Geldi</option>

                    <option value="0" class="btn btn-danger" >Gelmedi</option>

                    <option value="2" class="btn btn-warning" selected>Bekliyor</option> ';

            $html .= '</select>

                        </div>

                     </div>

                     <div class="col-md-1">

                        <div class="form-group">

                           <label>Fiyat (₺)</label>

                           <input type="tel" class="form-control" name="hizmet_fiyati_adisyon" data-value="'.$hizmet->id.'" value="'.$hizmet->fiyat.'">

                        </div>

                     </div>

                     <div class="col-md-1">

                        <div class="form-group">

                           <label style="visibility: hidden;width: 100%;">Kaldır</label>

                           <button type="button" name="hizmet_formdan_sil_adisyon_mevcut"  data-value="'.$hizmet->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                        </div>

                     </div>

                  </div>';

        }

        return $html;       

    }

    public function personeldetaygetir(Request $request)

    { 

        $personel = Personeller::where('id',$request->personelid)->first();

        $yetkili = IsletmeYetkilileri::where('id',$personel->yetkili_id)->first();

        return array(

            'personelbilgi'=>json_decode($personel),

            'calismasaatleri'=>json_decode(PersonelCalismaSaatleri::where('personel_id',$request->personelid)->orderBy('haftanin_gunu','asc')->get()),

            'molasaatleri'=>json_decode(PersonelMolaSaatleri::where('personel_id',$request->personelid)->orderBy('haftanin_gunu','asc')->get()),

            'hesapturu'=> DB::table('model_has_roles')->join('roles','model_has_roles.role_id','=','roles.id')->where('salon_id',$request->sube)->where('model_id',$yetkili->id)->value('roles.name'),

            'hizmetyuzde' => Personeller::where('id',$request->personelid)->value('hizmet_prim_yuzde'),

            'urunyuzde' => Personeller::where('id',$request->personelid)->value('urun_prim_yuzde'),

            'paketyuzde' => Personeller::where('id',$request->personelid)->value('paket_prim_yuzde'),

            'maas' => Personeller::where('id',$request->personelid)->value('maas'),

            'cep_telefon' => $yetkili->gsm1

        );

    }

    public function saatlerigetir(Request $request)

    {

        $html = '';

        $day = 0; 

        if(date('D', strtotime($request->tarih))=='Mon') $day=1;

        else if(date('D', strtotime($request->tarih))=='Tue') $day=2;

        else if(date('D', strtotime($request->tarih))=='Wed') $day=3;

        else if(date('D', strtotime($request->tarih))=='Thu') $day=4;

        else if(date('D', strtotime($request->tarih))=='Fri') $day=5;

        else if(date('D', strtotime($request->tarih))=='Sat') $day=6;

        else if(date('D', strtotime($request->tarih))=='Sun') $day=7;

        $isletme = Salonlar::where('id',$request->sube)->first();

        $secanahtar=1;

        for($j = strtotime(date('00:00')) ; $j < strtotime(date('23:59')); $j+=(5*60)) 

        {

            if( $j< strtotime(date('H:i', strtotime(SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',$day)->value('baslangic_saati')) )) || $j >= strtotime(date('H:i', strtotime(SalonCalismaSaatleri::where('salon_id',$request->sube)->where ('haftanin_gunu',$day)->value('bitis_saati')) )) ||($request->tarih==date('Y-m-d') && $j < strtotime(date('H:i')) ) )

            {

                $html .= '<option ';

                 

                 

                $html .= ' style="background-color:red;color:#fff" value="'.date('H:i:00',$j).'">'.date('H:i',$j).'</option>';

            }

                

            else 

            {

                if($secanahtar == 1)

                    $html .= '<option selected value="'.date('H:i:00',$j).'">'.date('H:i',$j).'</option>';

                else

                    $html .= '<option  value="'.date('H:i:00',$j).'">'.date('H:i',$j).'</option>';

                $secanahtar++;

            }

        }

        return $html;

                                                    



                                                

                                                      

    }

      public function saatAraliginda($start, $end,$saat){



            $now = date("H:i:s",strtotime($saat));

             



            // time frame rolls over midnight

            if(date("H:i:s",strtotime($start)) > date("H:i:s",strtotime($end))) {

                

                // if current time is past start time or before end time

                

                if($now >= date("H:i:s",strtotime($start)) || $now < date("H:i:s",strtotime($end))){

                    return true;

                }

            }



            // else time frame is within same day check if we are between start and end

            

            else if ($now >= date("H:i:s",strtotime($start)) && $now <= date("H:i:s",strtotime($end))) {

                return true;

            }



            return false;

        }

    public function cakisan_randevu_kontrol(Request $request,$randevu_tarihleri)

    {

        

        $cakisan_unsurlar = '';

        $isletme_calisma_saatleri = array();



        foreach($randevu_tarihleri as $tarihler){

            

            $yenisaatbaslangic = $request->saat;

            

            $totalsure = 0;

            foreach($request->hizmet_suresi as $key => $value)

            {

                $totalsure += $value;

            }

            $hizmet_sureleri_okunan = array();

            foreach ($request->randevuhizmetleriyeni as $key => $value) {

                array_push($hizmet_sureleri_okunan,$request->hizmet_suresi[$key]);

                $personel_cihaz_id = '';

                if(str_contains($request->randevupersonelleriyeni[$key],'cihaz'))

                {

                    $str = explode('-',$request->randevupersonelleriyeni[$key]);

                    $personel_cihaz_id = $str[1];

                }

                else

                    $personel_cihaz_id = $request->randevupersonelleriyeni[$key];

                

                $birsonraki = $key+1;

                $saat_baslangic='';

                $saat_bitis = '';

                if($key == 0){

                     

                     $saat_baslangic = $request->saat;

                     $saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));



                     if(!isset($request->{"birlestir{$birsonraki}"}))

                        $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($request->saat)));



                }

                else{

                    $saat_baslangic = $yenisaatbaslangic;

                    $saat_bitis = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));

                    if(!isset($request->{"birlestir{$birsonraki}"}))

                        $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic)));

                  

                }

                $onaylirandevular = DB::table('randevular')->join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')

                 

                ->leftjoin('salon_personelleri as sp','randevu_hizmetler.personel_id','=','sp.id')

                 

                ->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')

                ->leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')

                ->leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')->select(

                    'sp.personel_adi',

                    'hizmetler.hizmet_adi',

                   

                    'randevular.saat','randevular.saat_bitis'

                )

                ->where('randevular.tarih',$tarihler)->where(

                    function($q) use ($personel_cihaz_id,$request)

                    {       

                        $q->where('randevu_hizmetler.personel_id', $personel_cihaz_id);

                        $q->orWhere('randevu_hizmetler.cihaz_id',$personel_cihaz_id);

                        $q->orWhereIn('randevu_hizmetler.oda_id',$request->randevuodalariyeni);

                        

                    })->where('randevular.durum',1)->where(function($q) use($request){if(isset($request->randevu_id)) $q->where('randevular.id','!=',$request->randevu_id);})->get();

                foreach($onaylirandevular as $onaylirandevu)

                {   

                    if(self::saatAraliginda($onaylirandevu->saat,$onaylirandevu->saat_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)). ' '.date('H:i',strtotime($saat_baslangic)).' : '.$onaylirandevu->personel_adi.' '.$onaylirandevu->hizmet_adi. ' randevusu.</p>';

                }

                



                $personel_calisma_saati_baslangic = PersonelCalismaSaatleri::where('personel_id',$personel_cihaz_id)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');

                $personel_calisma_saati_bitis = PersonelCalismaSaatleri::where('personel_id',$personel_cihaz_id)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');

                $personel_mola_saati_baslangic = PersonelMolaSaatleri::where('personel_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');

                $personel_mola_saati_bitis = PersonelMolaSaatleri::where('personel_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');

                $cihaz_calisma_saati_baslangic = CihazCalismaSaatleri::where('cihaz_id',$personel_cihaz_id)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');

                $cihaz_calisma_saati_bitis = CihazCalismaSaatleri::where('cihaz_id',$personel_cihaz_id)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');

                $cihaz_mola_saati_baslangic = CihazMolaSaatleri::where('cihaz_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');

                $cihaz_mola_saati_bitis = CihazMolaSaatleri::where('cihaz_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');

                $salon_calisma_saati_baslangic= SalonCalismaSaatleri::where('salon_id',$request->sube)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');

                $salon_calisma_saati_bitis = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');

                $salon_mola_saati_baslangic= SalonMolaSaatleri::where('salon_id',$request->sube)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');

                $salon_mola_saati_bitis = SalonMolaSaatleri::where('salon_id',$request->sube)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');

                if($salon_calisma_saati_baslangic!=''&& $salon_calisma_saati_bitis!='') {

                    if(!self::saatAraliginda($salon_calisma_saati_baslangic,$salon_calisma_saati_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)).' tarihinde işletmenizin çalışma saatlerinin dışına denk geliyor. </p>';   

                }

                if($salon_mola_saati_baslangic != '' && $salon_mola_saati_bitis != '')

                {

                    if(self::saatAraliginda($salon_mola_saati_baslangic,$salon_mola_saati_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)).' tarihinde işletmenizin mola saatine denk geliyor. </p>';

                }

                if($personel_calisma_saati_baslangic != '' && $personel_calisma_saati_bitis != '')

                {

                    if(!self::saatAraliginda($personel_calisma_saati_baslangic,$personel_calisma_saati_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Personeller::where('id',$personel_cihaz_id)->value('personel_adi').' isimli personelin çalışma saatinin dışına denk geliyor. </p>';  

                }

                if($personel_mola_saati_baslangic != '' && $personel_mola_saati_bitis != '')

                {

                    if(self::saatAraliginda($personel_mola_saati_baslangic,$personel_mola_saati_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Personeller::where('id',$personel_cihaz_id)->value('personel_adi').' isimli personelin mola saatine denk geliyor. </p>';

                }

                if($cihaz_calisma_saati_baslangic != '' && $cihaz_calisma_saati_bitis != '')

                {

                    if(!self::saatAraliginda($cihaz_calisma_saati_baslangic,$cihaz_calisma_saati_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Cihazlar::where('id',$personel_cihaz_id)->value('cihaz_adi').' isimli cihazın çalışma saatinin dışına denk geliyor. </p>';  

                }

                if($cihaz_mola_saati_baslangic != '' && $cihaz_mola_saati_bitis != '')

                {

                    if(self::saatAraliginda($cihaz_mola_saati_baslangic,$cihaz_mola_saati_bitis,$saat_baslangic))

                        $cakisan_unsurlar .= '<p style="font-size:14px;color:#fff">'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Cihazlar::where('id',$personel_cihaz_id)->value('cihaz_adi').' isimli cihazın mola saatine denk. </p>'; 

                }

               

               

            }



        }

      

        return $cakisan_unsurlar;

        

        

         

        /*$salon = Salonlar::where('id',$request->sube)->first();

        $tarih = $request->tarih;

        $baslangic_saati = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',self::haftanin_gunu($tarih))->where('calisiyor',1)->value('baslangic_saati');

        $bitis_saati = SalonCalismaSaatleri::where('salon_id',$request->sube)->where('haftanin_gunu',self::haftanin_gunu($tarih))->where('calisiyor',1)->value('bitis_saati');

        

        $nowtime = date('H:i');

        $dolusaatler = array();

    

        $dolusaatlerstr = '';

        $musaitsaatler = '<select id="seansadisyonsaat" class="form-control" data-value="'.$request->seansid.'">';

        $musaitlikvar = false;

        $hizmet_suresi = SalonHizmetler::where('salon_id',$request->sube)->whereIn('hizmet_id',$request->randevuhizmetleriyeni)->pluck('sure_dk');



        $randevular = Randevular::join('randevu_hizmetler','randevu_hizmetler.randevu_id','=','randevular.id')->where('randevular.tarih',$tarih)->where('randevular.salon_id',$request->sube)->where(

            function($q) use ($personel_cihaz_id)

            {

                $q->whereIn('randevu_hizmetler.personel_id', $personel_cihaz_id);

                $q->orWhereIn('randevu_hizmetler.cihaz_id',$personel_cihaz_id);

            })->where(function($q) use ($request)

                        {

                            if($request->odaid != '')

                                $q->whereIn('randevu_hizmetler.oda_id', $request->randevuodalariyeni[$key]);

                        }

                    )->where('randevular.durum',1)->get(['randevu_hizmetler.saat as baslangic_saati','randevu_hizmetler.saat_bitis as bitis_saati']);





        foreach($randevular as $randevu)

        {

            $saatdongu = date('H:i',strtotime($randevu->baslangic_saati));

            while($saatdongu!= date('H:i',strtotime($randevu->bitis_saati)))

            {

                array_push($dolusaatler,date('H:i', strtotime($saatdongu)));

                $dolusaatlerstr .= date('H:i', strtotime($saatdongu)).'<br>';

                $saatdongu = date('H:i', strtotime('+1 minute', strtotime($saatdongu)));

                

            } 

        }

        $salon_mola_baslangic = SalonMolaSaatleri::where('salon_id',$request->sube)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('baslangic_saati');

        $salon_mola_bitis = SalonMolaSaatleri::where('salon_id',$request->sube)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->value ('baslangic_saati');

        while(date('H:i',strtotime($salon_mola_baslangic)) != date('H:i',strtotime($salon_mola_bitis)))

        {

                array_push($dolusaatler,date('H:i', strtotime($salon_mola_baslangic)));

                $dolusaatlerstr .= date('H:i', strtotime($salon_mola_baslangic)).'<br>';

                $salon_mola_baslangic = date('H:i', strtotime('+1 minute', strtotime($salon_mola_baslangic)));

                

        }

        $personel_mola_baslangic = PersonelMolaSaatleri::whereIn('personel_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->pluck('baslangic_saati');

        $personel_mola_bitis = PersonelMolaSaatleri::whereIn('personel_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->pluck ('bitis_saati');

        $cihaz_mola_baslangic = CihazMolaSaatleri::whereIn('cihaz_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->pluck ('baslangic_saati');

        $cihaz_mola_bitis = CihazMolaSaatleri::whereIn('cihaz_id',$personel_cihaz_id)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarih))->pluck('bitis_saati');

        foreach($personel_mola_baslangic as $key=>$personel_mola)

        {

            while(date('H:i',strtotime($personel_mola)) != date('H:i',strtotime($personel_mola_bitis[$key])))

            {

                array_push($dolusaatler,date('H:i', strtotime($personel_mola)));

                $dolusaatlerstr .= date('H:i', strtotime($personel_mola)).'<br>';

                $personel_mola = date('H:i', strtotime('+1 minute', strtotime($personel_mola)));

                

            } 

        }

        foreach($cihaz_mola_baslangic as $key2=>$cihaz_mola)

        {

            while(date('H:i',strtotime($cihaz_mola)) != date('H:i',strtotime($cihaz_mola_bitis[$key2])))

            {

                array_push($dolusaatler,date('H:i', strtotime($cihaz_mola)));

                $dolusaatlerstr .= date('H:i', strtotime($cihaz_mola)).'<br>';

                $cihaz_mola = date('H:i', strtotime('+1 minute', strtotime($cihaz_mola)));

                

            } 

        }

       

        for($j = strtotime(date('H:i', strtotime($baslangic_saati) )) ; $j < strtotime(date('H:i', strtotime($bitis_saati))); $j+=($salon->randevu_saat_araligi*60)) 

        {     

                if((date('H:i',$j) > $nowtime && $tarih = date('Y-m-d') )||$tarih > date('Y-m-d')){

                    $hizmet_dongu = date('H:i',$j);

                   

                    $cakismavar = false;

                    

                    foreach($hizmet_suresi as $hizmet_sure)

                    {

                        while($hizmet_dongu != date('H:i',strtotime('+'.$hizmet_sure.' minutes',strtotime(date('H:i',$j)))))

                        {



                            //echo date('H:i',strtotime('+'.$hizmet_suresi.' minutes',strtotime(date('H:i',$j)))).'<br>';

                            $cakismavar = false;

                            if(in_array($hizmet_dongu,$dolusaatler)){

                                $cakismavar = true;

                             

                            break;

                        }

                        else 

                            



                            $hizmet_dongu = date('H:i', strtotime('+1 minute', strtotime($hizmet_dongu)));

                        }

                        if( !$cakismavar ){



                          $musaitsaatler .= '<option value="'.date('H:i',$j).'">'.date('H:i',$j).'</option>';

                           

                          $musaitlikvar = true;

                        }

                        else

                        {

                          $musaitsaatler .= '<option disabled style="background-color:red;color:#fff" value="'.date('H:i',$j).'">'.date('H:i',$j).'</option>';

                          $musaitlikvar = true;

                           

                        }

                    }

                }

        }

        $musaitsaatler .= '</select>';

        return $dolusaatlerstr; */ 

    }



    public function cikisyap(Request $request){

        auth('isletmeyonetim')->logout();

        $request->session()->invalidate(); // Oturumu tamamen temizle

        $request->session()->regenerateToken(); // Yeni bir CSRF token oluştur

        return redirect('/isletmeyonetim' ); 

    }

    public function exceldataaktarornek(Request $request)

    {

       

        

        Excel::load('/public/datalar/fulya_paketler.xlsx',function($reader) {

            $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();

            

            $previous_val_musteri = '';

            $previous_val_tarih = '';

            $previous_val_adisyon_id = '';

            $adisyon_index = 0;

            foreach ($reader->toArray() as $key => $row) {

                

                $musteri_var = DB::table('musteri_portfoy')->join('users','users.id','=','musteri_portfoy.user_id')->select('users.id as id')->where('users.name',$row['musteri'])->where('musteri_portfoy.salon_id',$salon->id)->first();

                 

                if($musteri_var)

                    echo $row['musteri'].' mevcut<br>';

                else

                    echo $row['musteri'].' mevcut değil<br>';

                $satan_id = Personeller::where('salon_id',$salon->id)->where('personel_adi',$row['satici'])->value('id');

                $hizmetvar = DB::table('salon_sunulan_hizmetler')->join('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')->select('hizmetler.id as hizmet_id')->where('hizmetler.hizmet_adi',$row['hizmet'])->where('salon_sunulan_hizmetler.salon_id',$salon->id)->orderBy('hizmetler.id','desc')->first();

                $hizmet = '';

                $satici  ='';



                if($satan_id)

                    $satici = $row['satici'].' mevcut.';

                else

                    $satici = $row['satici'].' mevcut değil';

                if($hizmetvar)

                    $hizmet = $row['hizmet'].' mevcut. id : '.$hizmetvar->hizmet_id;

                else

                    $hizmet = $row['hizmet'].' mevcut değil';

                if(

                    ($previous_val_musteri == $row['musteri'] && $previous_val_tarih != $row['tarih']) ||

                    ($previous_val_musteri != $row['musteri'] && $previous_val_tarih == $row['tarih']) ||

                    ($previous_val_musteri != $row['musteri'] && $previous_val_tarih != $row['tarih'])

                ) 

                {

                    $previous_val_adisyon_id = self::yeni_adisyon_olustur($musteri_var->id,$salon->id,'SalonAppyden aktarma',date('Y-m-d',strtotime(self::convertMonthToTurkishCharacter($row['tarih']))));

                    $adisyon_index++;

                }

                $previous_val_musteri = $row['musteri'];

                $previous_val_tarih = $row['tarih'];

                $paket_id = '';

                $iliskili_paket = DB::table('paket_hizmetler')->join('paketler','paketler.id','=','paket_hizmetler.paket_id')->select('paketler.id as paket_id')->where('paket_hizmetler.hizmet_id',$hizmetvar->hizmet_id)->where('paketler.salon_id',$salon->id)->where('paket_hizmetler.seans',$row['seans'])->first();

                if($iliskili_paket)

                    $paket_id = $iliskili_paket->paket_id;

                else

                    $paket_id = 'Bulunamadı';

                //var_dump($iliskili_paket);

                $adisyon_paket_id = self::adisyona_paket_ekle($previous_val_adisyon_id,$paket_id,$row['tutar'],date('Y-m-d',strtotime(self::convertMonthToTurkishCharacter($row['tarih']))),7,$satan_id,null,null); 

                //echo '<b>adisyon '. $adisyon_index.' : </b> Satan : '.$satici .' Müşteri : '.$row['musteri']. ' - '.$row['tarih'].' - '.$row['seans']. ' seans '.$hizmet.'. '.$row['kullanilan']. ' kullanıldı '.$row['kullanilmayan'] .' kaldı.'. $row['tutar'].' TL nin '.$row['odenen'].' TL si ödendi '.$row['kalan']. ' TL si kaldı. İlişkili paket id : '.$paket_id; 

                //echo '<br><br>'; 

                /*var_dump($row);*/ 

                $toplam_seans_sayilari = $row['seans'];



           

                for($i=1;$i<=$toplam_seans_sayilari;$i++)

                {

                    

                        $seans = new AdisyonPaketSeanslar();

                        $seans->adisyon_paket_id = $adisyon_paket_id;

                        $seans->hizmet_id = $hizmetvar->hizmet_id;

                        if($i <= $row['kullanilan'])

                            $seans->geldi = true; 

                        $seans->save();



                }

                if($row['odenen'] > 0)

                {

                    $tahsilat= new Tahsilatlar();

                    $tahsilat->adisyon_id = $previous_val_adisyon_id;

                    $tahsilat->tutar = $row['odenen'];

                    $tahsilat->odeme_tarihi = date('Y-m-d',strtotime(self::convertMonthToTurkishCharacter($row['tarih'])));

                    $tahsilat->olusturan_id = $row['satici'];

                    $tahsilat->salon_id = $salon->id;

                    $tahsilat->yapilan_odeme = $row['odenen'];

                    $tahsilat->odeme_yontemi_id = 1;

                    $tahsilat->save();

                    $odeme = new TahsilatPaketler();

                    $odeme->adisyon_paket_id = $adisyon_paket_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->tutar = $row['odenen'];

                    $odeme->save();

                } 



            }





        });

        echo 'başarılı aktarım';

    }

    public function randevu_excel(Request $request)

    {

        $salon = Salonlar::where('domain',$_SERVER['HTTP_HOST'])->first();

        Excel::load('/public/datalar/nerolia_randevular.xlsx',function($reader) use($salon) {

            foreach ($reader->toArray() as $key => $row) {

                $musteri_var = DB::table('musteri_portfoy')->join('users','users.id','=','musteri_portfoy.user_id')->select('users.id as id')->where('users.name',$row['danisan_adi'])->where('musteri_portfoy.salon_id',$salon->id)->first();

                 if($musteri_var)

                    echo $row['danisan_adi'].' mevcut<br>';

                else

                    echo $row['danisan_adi'].' mevcut değil<br>';

                

                    

                $randevu = new Randevular();

                $randevu->user_id = $musteri_var->id;

                $randevu->salon_id = $salon->id;

                $randevu->tarih = date('Y-m-d',strtotime(self::convertMonthToTurkishCharacter($row['baslangic_tarihi'])));

                $randevu->saat = $row['baslangic_saati'];

                if($row['durum']=='Beklemede')

                    $randevu->durum = 0;

                if($row['durum']=='Tamamlandı')

                {

                    $randevu->durum = 1;

                    $randevu->randevuya_geldi = 1;



                }

                if($row['durum']=='İptal' || $row['durum']=='Tıraşlamadan Dolayı İptal')

                    $randevu->durum = 2;



                if($row['durum']=='Gelmedi')

                {

                    $randevu->durum = 1;

                    $randevu->randevuya_geldi = 0;

                }

                if($row['durum']=='Odada')

                    $randevu->durum = 1;

                $randevu->salon = true;

                $randevu->personel_notu = $row['notlar'];

                

                $randevu->olusturan_personel_id = Personeller::where('salon_id',126)->where('personel_adi',$row['olusturan'])->value('id');

                $randevu->created_at = date('Y-m-d',strtotime(self::convertMonthToTurkishCharacter($row['kayit_tarihi'])));

                $randevu->updated_at = date('Y-m-d',strtotime(self::convertMonthToTurkishCharacter($row['degisiklik_tarihi'])));

                $randevu->save(); 

                

                $yenisaatbaslangic =  $row['baslangic_saati'];

                $hizmet_sureleri_okunan = array();

                $hizmet_kategorileri = explode(', ',$row['hizmetkategorisi']);

                foreach(/*self::extract_outside_text($row['hizmetadi'])*/explode(', ',$row['hizmetadi']) as $key => $hizmet){ 

                   

                    $hizmet1 = $hizmet;

                    

                    $hizmetvar = DB::table('salon_sunulan_hizmetler')->join('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')->where('salon_sunulan_hizmetler.salon_id',$salon->id)->select('hizmetler.id as hizmet_id','salon_sunulan_hizmetler.baslangic_fiyat as fiyat','salon_sunulan_hizmetler.sure_dk as sure')->where('hizmetler.hizmet_adi',$hizmet1)->first();

                     

                    if($hizmetvar)

                        echo $hizmet1.' mevcut. ID : '.$hizmetvar->hizmet_id.' Süre dk : '.$hizmetvar->sure.' Fiyat : '.$hizmetvar->fiyat;

                    else{

                        $yenihizmet = new Hizmetler();

                        $yenihizmet->hizmet_kategori_id = Hizmet_Kategorisi::where('hizmet_kategorisi_adi',$hizmet_kategorileri[$key])->value('id');

                        $yenihizmet->hizmet_adi = $hizmet1;

                        $yenihizmet->ozel_hizmet = true;

                        $yenihizmet->salon_id = 126;

                        $yenihizmet->cinsiyet = 2;

                        $yenihizmet->save();

                        $hizmetvar = new SalonHizmetler();

                        $hizmetvar->hizmet_id = $yenihizmet->id;

                        $hizmetvar->hizmet_kategori_id = Hizmet_Kategorisi::where('hizmet_kategorisi_adi',$hizmet_kategorileri[$key])->value('id');

                        $hizmetvar->bolum = 2;

                         

                        $hizmetvar->aktif = true;

                        

                        $hizmetvar->salon_id = 126;

                        $hizmetvar->save();

                         

                        if(SalonHizmetKategoriRenkleri::where('hizmet_kategori_id',$hizmetvar->hizmet_kategori_id)->where('salon_id',126)->count() == 0)

                        {

                                $kategori_son_renk = SalonHizmetKategoriRenkleri::where('salon_id',126)->orderBy('renk_id','desc')->first();

                                $yeni_kategori_renk = '';

                                if($kategori_son_renk===null)

                                    $yeni_kategori_renk = 1;

                                else

                                {

                                    if($kategori_son_renk->renk_id == 10)

                                        $yeni_kategori_renk = 1;

                                    else

                                        $yeni_kategori_renk = $kategori_son_renk->renk_id + 1;

                                }

                                

                                $yeni_renk = new SalonHizmetKategoriRenkleri();

                                $yeni_renk->salon_id = 126;

                                $yeni_renk->renk_id = $yeni_kategori_renk;

                                $yeni_renk->hizmet_kategori_id = $yenihizmet->hizmet_kategori_id;

                                $yeni_renk->save(); 

                        }

                    }



                    echo '<br>';

                    

                    //$paket_var = DB::table('adisyon_paketler')->join('adisyonlar','adisyon_paketler.adisyon_id','=','adisyon_id')->join('paketler','adisyon_paketler.paket_id','=','paketler.id')->join('paket_hizmetler','paket_hizmetler.paket_id','=','paketler.id')->join('hizmetler','paket_hizmetler.hizmet_id','=','hizmetler.id')

                    $yenirandevuhizmetpersonel = new RandevuHizmetler();

                    $yenirandevuhizmetpersonel->randevu_id = $randevu->id;

                    $yenirandevuhizmetpersonel->hizmet_id = $hizmetvar->hizmet_id; 

                    //$yenirandevuhizmetpersonel->cihaz_id = Cihazlar::where('cihaz_adi',$cihaz[$key])->where('salon_id',$salon->id)->value('id'); 

                    //$yenirandevuhizmetpersonel->sure_dk = $hizmetvar->sure;

                    //$yenirandevuhizmetpersonel->fiyat = $hizmetvar->fiyat;

                    $yenirandevuhizmetpersonel->personel_id = Personeller::where('salon_id',126)->where('personel_adi',$row['personel'])->value('id');

                    $yenirandevuhizmetpersonel->saat = $row['baslangic_saati'];

                    $yenirandevuhizmetpersonel->saat_bitis = $row['bitis_saati'];

                    $yenirandevuhizmetpersonel->oda_id = Odalar::where('oda_adi',$row['oda'])->value('id');

                    /*$birsonraki = $key+1;

                    if($key == 0){

                            $yenirandevuhizmetpersonel->saat = $row['saat'];

                            $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$hizmetvar->sure.' minutes', strtotime($row['saat'])));



                            

                            $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($row['saat'])));

 

                    } 

                    else{ 

                            $yenirandevuhizmetpersonel->saat = $yenisaatbaslangic;

                            $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$hizmetvar->sure.' minutes', strtotime($yenisaatbaslangic)));

                            

                            $yenisaatbaslangic = date("H:i", strtotime('+'.$request->hizmet_suresi[$key].' minutes', strtotime($yenisaatbaslangic))); 

                    }*/

                    $yenirandevuhizmetpersonel->save(); 

                }

                echo '<br>';

                

                

                echo '<br>';

                echo 'başarılı aktarım';

                    /*$randevu = new Randevular()

                    $randevu->user_id = $randevu_eski->user_id;

                    $randevu->salon_id = $randevu_eski->salon_id;

                    $randevu->tarih = $baslangic[0];

                    $randevu->saat = $baslangic[1];

                    $randevu->durum = $randevu_eski->durum;

                    $randevu->salon = true;

                    $randevu->sms_hatirlatma = $randevu_eski->sms_hatirlatma;

                    $randevu->notlar = $randevu_eski->notlar;

                    $randevu->personel_notu = $randevu_eski->personel_notu;

                    $randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;*/



                 

            }



        });

    }

    

    public function convertMonthToTurkishCharacter($date){

        $aylar = array(

            'Ocak'    =>    'Jan',

            'Şubat'    =>    'Feb',

            'Mart'        =>    'Mar',

            'Nisan'        =>    'Apr',

            'Mayıs'        =>    'May',

            'Haziran'        =>    'Jun',

            'Temmuz'        =>    'Jul',

            'Ağustos'    =>    'Aug',

            'Eylül'    =>    'Sep',

            'Ekim'    =>    'Oct',

            'Kasım'    =>    'Nov',

            'Aralık'    =>    'Dec',

          



        );

        return  strtr($date, $aylar);

    }

    function extract_inside_text($string)

   {

    $text_outside=array();

    $text_inside=array();

    $t="";

    for($i=0;$i<strlen($string);$i++)

    {

        if($string[$i]=='[')

        {

            $text_outside[]=$t;

            $t="";

            $t1="";

            $i++;

            while($string[$i]!=']')

            {

                $t1.=$string[$i];

                $i++;

            }

            $text_inside[] = $t1;



        }

        else {

            if($string[$i]!=']')

            $t.=$string[$i];

            else {

                continue;

            }



        }

    }

    if($t!="")

    $text_outside[]=$t;

    

    

    return $text_inside;

  }

   function extract_outside_text($string)

   {

    $text_outside=array();

    $text_inside=array();

    $t="";

    for($i=0;$i<strlen($string);$i++)

    {

        if($string[$i]=='[')

        {

            $text_outside[]=$t;

            $t="";

            $t1="";

            $i++;

            while($string[$i]!=']')

            {

                $t1.=$string[$i];

                $i++;

            }

            $text_inside[] = $t1;



        }

        else {

            if($string[$i]!=']')

            $t.=$string[$i];

            else {

                continue;

            }



        }

    }

    if($t!="")

    $text_outside[]=$t;

    

    

    return $text_outside;

  }

  public function selectboxelemanlari(Request $request)

  {

        $musteri_arama = '';

        $musteriler = '';

        foreach(MusteriPortfoy::where('salon_id',self::mevcutsube($request))->get() as $mevcutmusteri){

            $musteri_arama .= '<option value="/isletmeyonetim/musteridetay/'.$mevcutmusteri->user_id.'">'.$mevcutmusteri->users->name.'('.$mevcutmusteri->users->cep_telefon.')</option>';

            $musteriler = '<option value="'.$mevcutmusteri->user_id.'">'.$mevcutmusteri->users->name.'</option>';

        }

        return array(

            'musteri_arama' => $musteri_arama,

            'musteriler' => $musteriler



        );

                 

  }

  public function adisyon_sil(Request $request)

   {

            $adisyonhizmetler = AdisyonHizmetler::where('adisyon_id',$request->adisyon_id)->get();

            $adisyonurunler = AdisyonUrunler::where('adisyon_id',$request->adisyon_id)->get();

            $adisyonpaketler = AdisyonPaketler::where('adisyon_id',$request->adisyon_id)->get();

            $senet_idler = array();

            $taksitlitahsilat_idler = array();

            $tahsilat_idler = array();

            foreach($adisyonhizmetler as $adisyonhizmet)

            {

                array_push($senet_idler,$adisyonhizmet->senet_id);

                array_push($taksitlitahsilat_idler,$adisyonhizmet->taksitli_tahsilat_id);

                foreach(TahsilatHizmetler::where('adisyon_hizmet_id',$adisyonhizmet->id)->get() as $hizmettahsilat)

                {

                    array_push($tahsilat_idler,$hizmettahsilat->tahsilat_id);

                    $hizmettahsilat->delete();

                }

                $adisyonhizmet->delete();

            }

            foreach($adisyonurunler as $adisyonurun)

            {

                array_push($senet_idler,$adisyonurun->senet_id);

                array_push($taksitlitahsilat_idler,$adisyonurun->taksitli_tahsilat_id);

                foreach(TahsilatUrunler::where('adisyon_urun_id',$adisyonurun->id)->get() as $uruntahsilat)

                {

                    array_push($tahsilat_idler,$uruntahsilat->tahsilat_id);

                    $uruntahsilat->delete();

                }

                $adisyonurun->delete();

            }

            foreach($adisyonpaketler as $adisyonpaket)

            {

                array_push($senet_idler,$adisyonpaket->senet_id);

                array_push($taksitlitahsilat_idler,$adisyonpaket->taksitli_tahsilat_id);

                foreach(TahsilatPaketler::where('adisyon_paket_id',$adisyonpaket->id)->get() as $pakettahsilat)

                {

                    array_push($tahsilat_idler,$pakettahsilat->tahsilat_id);

                    $pakettahsilat->delete();

                }

                $adisyonpaket->delete();

            }

            $taksitler = TaksitliTahsilatlar::whereIn('id',$taksitlitahsilat_idler)->get();

            foreach($taksitler as $taksit)

            {

                foreach($taksit->vadeler as $vade)

                    $vade->delete();

                $taksit->delete();

            }

            $senetler = Senetler::whereIn('id',$senet_idler)->get();

            foreach($senetler as $senet)

            {

                foreach($senet->vadeler as $vade)

                    $vade->delete();

                $senet->delete();

            }

            Tahsilatlar::whereIn('id',$tahsilat_idler)->delete(); 

            Adisyonlar::where('id',$request->adisyon_id)->delete();

            $musteriid = '';

            if($request->musteri_id!='')

                $musteriid = $request->musteri_id;

            $tarih1 = '';

            $tarih2 = '';

            if($request->tariharaligi!=''){

                $tarih = explode(' / ',$request->tariharaligi);

                $tarih1 = $tarih[0].' 00:00:00';

                $tarih2 = $tarih[1].' 23:59:59';

            }

            else{

                $tarih1 = '1970-01-01 00:00:00';

                $tarih2 = date('Y-m-d 23:59:59');

            }

           $personelid='';

            if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

                $personelid = Auth::guard('isletmeyonetim')->user()->personel_id;

            return array(

                'mesaj' => 'Adisyon kaydı başarıyla kaldırıldı',

                'tum_adisyonlar' => self::adisyon_yukle($request,'','',$tarih1,$tarih2,$musteriid,$personelid),

                'hizmet_adisyonlar' => self::adisyon_yukle($request,1,'',$tarih1,$tarih2,$musteriid,$personelid),

                'urun_adisyonlar' => self::adisyon_yukle($request,3,'',$tarih1,$tarih2,$musteriid,$personelid),

                'paket_adisyonlar' => self::adisyon_yukle($request,2,'',$tarih1,$tarih2,$musteriid,$personelid),

            );

    }

    public function lisans_sure_kontrol(Request $request)

    {

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        $from_time = strtotime(date('Y-m-d H:i:s'));

        $to_time = strtotime(date($isletme->uyelik_bitis_tarihi.' 23:59:59'));



        $diff = round(($to_time - $from_time) / (3600*24),0);

        return $diff;



    }

    public function senetlitahsilatekle(Request $request){

        $tahsilat = '';

        if($request->tahsilat_id != '')

            $tahsilat = Tahsilatlar::where('id',$request->tahsilat_id)->first();

        else

            $tahsilat = new Tahsilatlar();

        $tahsilat->adisyon_id = $request->adisyon_id;

        $tahsilat->tutar = str_replace('.','',$request->indirimli_toplam_tahsilat_tutari);

        $tahsilat->odeme_tarihi = $request->tahsilat_tarihi;    

         $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        $tahsilat->salon_id = $request->sube;

        $tahsilat->yapilan_odeme = str_replace('.','',$request->indirimli_toplam_tahsilat_tutari);

        $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;

        $tahsilat->notlar = $request->tahsilat_notlari;

        $adisyon = Adisyonlar::where('id',$request->adisyon_id)->first();

        $adisyon->indirim_tutari = str_replace('.','',$request->indirim_tutari);

        $adisyon->save();

        $tahsilat->save();



        if(isset($request->adisyon_hizmet_id))

        {

            foreach($request->adisyon_hizmet_id as $key=>$hizmet_id)

            {        

                if($request->tahsilat_id != '')

                    TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet_id)->delete();

                if($request->hizmet_odeme_secili[$key]=='true'){

                    $odeme = new TahsilatHizmetler();

                    $odeme->adisyon_hizmet_id = $hizmet_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->tutar = (str_replace(['.',','],['','.'],$request->adisyon_hizmet_tahsilat_tutari[$key])/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);



                    $odeme->save();

                } 



            }

         

        }

        if(isset($request->adisyon_urun_id))

        {

            foreach($request->adisyon_urun_id as $key2=>$urun_id)

            {

                if($request->tahsilat_id != '')

                    TahsilatUrunler::where('adisyon_urun_id',$urun_id)->delete();

                if($request->urun_odeme_secili[$key2]=='true'){

                    $odeme = new TahsilatUrunler();

                    $odeme->adisyon_urun_id = $urun_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->tutar = (str_replace(['.',','],['','.'],$request->adisyon_urun_tahsilat_tutari[$key2])/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);



                    $odeme->save();

                } 



            }

        }

        if(isset($request->adisyon_paket_id))

        {

            foreach($request->adisyon_paket_id as $key3=>$paket_id)

            {

                if($request->tahsilat_id != '')

                    TahsilatPaketler::where('adisyon_paket_id',$paket_id)->delete();

                if($request->paket_odeme_secili[$key3]=='true'){

                    $odeme = new TahsilatPaketler();

                    $odeme->adisyon_paket_id = $paket_id;

                    $odeme->tahsilat_id = $tahsilat->id;

                    $odeme->tutar = (str_replace(['.',','],['','.'],$request->adisyon_paket_tahsilat_tutari[$key3])/str_replace(['.',','],['','.'],$request->tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);



                    $odeme->save();

                } 

            }

        }

       

        $alacak = str_replace('.','',$request->odenecek_tutar);

        Alacaklar::where('adisyon_id',$request->adisyon_id)->delete();

        if($alacak != 0)

        {

            

            $alacak_kaydi = new Alacaklar();

            $alacak_kaydi->salon_id = $request->sube;

            $alacak_kaydi->adisyon_id = $request->adisyon_id;

            $alacak_kaydi->tutar = $alacak;

            $alacak_kaydi->aciklama = $request->tahsilat_notlari;

            $alacak_kaydi->planlanan_odeme_tarihi = $request->planlanan_alacak_tarihi;

            $alacak_kaydi->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;

            $alacak_kaydi->salon_id = $request->sube;

            $alacak_kaydi->user_id = $adisyon->user_id;

            $alacak_kaydi->save();





        }

       



        return self::adisyon_tahsilatlari($request,'Tahsilat kaydı başarıyla eklendi');

    }

    public function personelcihazhizmetlerinigetir(Request $request)

    {

        $html = '';

        $hizmetidler = array();

        if($request->personel_id != '')

        {

            $personelsunulanhizmetler = PersonelHizmetler::where('personel_id',$request->personel_id)->get();

            foreach($personelsunulanhizmetler as $personelsunulanhizmet) 

                array_push($hizmetidler,$personelsunulanhizmet->hizmet_id); 

             

        }

        if($request->cihaz_id != '')

        {

            $cihazsunulanhizmetler = CihazHizmetler::where('cihaz_id',$request->cihaz_id)->get();

            foreach($cihazsunulanhizmetler as $cihazsunulanhizmet)

                array_push($hizmetidler,$cihazsunulanhizmet->hizmet_id); 

        }

        $secilihizmetler = Hizmetler::whereIn('id',array_unique($hizmetidler))->get();

        foreach($secilihizmetler as $secilihizmet)

            $html .= '<option value="'.$secilihizmet->id.'">'.$secilihizmet->hizmet_adi.'</option>';

        return $html;



    }

    public function taksitleri_getir(Request $request,$sorgu,$musteriid)

    {

        if($sorgu===''){

            return DB::table('taksitli_tahsilatlar')->join('taksit_vadeleri','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->join('users','taksitli_tahsilatlar.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from taksit_vadeleri where taksit_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

            DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(taksit_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>" ) END as odenmemis'),



           DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),

            DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ) END as yaklasan_vade_tarihi'),

            DB::raw('CONCAT("<a href=\"#\" name=\"taksit_vadeleri\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-primary\">Detaylar</a> <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",taksitli_tahsilatlar.user_id, "?sube=", taksitli_tahsilatlar.salon_id ,"\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\" </a>") as islemler')



            )->where('taksitli_tahsilatlar.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('taksitli_tahsilatlar.user_id',$musteriid);})->groupBy('taksit_vadeleri.taksitli_tahsilat_id')->get();

            exit;

        }

        if($sorgu===0)

        {

            return  DB::table('taksitli_tahsilatlar')->join('taksit_vadeleri','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->join('users','taksitli_tahsilatlar.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from taksit_vadeleri where taksit_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

            DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(taksit_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>" ) END as odenmemis'),



          DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),

           DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    )  END as yaklasan_vade_tarihi'),

            DB::raw('CONCAT("<a href=\"#\" name=\"taksit_vadeleri\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-primary\">Detaylar</a> <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",taksitli_tahsilatlar.user_id,"?sube=", taksitli_tahsilatlar.salon_id ,"\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\" </a>") as islemler')



            )->where('taksitli_tahsilatlar.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('taksitli_tahsilatlar.user_id',$musteriid);})->having('yaklasan_vade','!=','<button class="btn btn-warning">Kapalı Senet</button>')->groupBy('taksit_vadeleri.taksitli_tahsilat_id')->get();

            exit;

        }

        if($sorgu===1)

        {

            return  DB::table('taksitli_tahsilatlar')->join('taksit_vadeleri','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->join('users','taksitli_tahsilatlar.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from taksit_vadeleri where taksit_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

           DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(taksit_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>" ) END as odenmemis'),



          DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),

           DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ) END as yaklasan_vade_tarihi'),

            DB::raw('CONCAT("<a href=\"#\" name=\"taksit_vadeleri\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-primary\">Detaylar</a> <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",taksitli_tahsilatlar.user_id,"?sube=", taksitli_tahsilatlar.salon_id ,"\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\" </a>") as islemler')



            )->where('taksitli_tahsilatlar.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('taksitli_tahsilatlar.user_id',$musteriid);})->having('yaklasan_vade','=','<button class="btn btn-warning">Kapalı Senet</button>')->groupBy('taksit_vadeleri.taksitli_tahsilat_id')->get();

            exit;

        }

        if($sorgu===2)

        {

            return  DB::table('taksitli_tahsilatlar')->join('taksit_vadeleri','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->join('users','taksitli_tahsilatlar.user_id','=','users.id')->select(

            DB::raw('CASE WHEN (select count(*) from taksit_vadeleri where taksit_vadeleri.odendi=false) > 0 THEN "Açık" ELSE "Kapalı" END as durum'),

            'users.name as ad_soyad',

            DB::raw('CONCAT ("<button class=\"btn btn-primary\">", COUNT(taksit_vadeleri.id),"</button>" ) as vade_sayisi'),

 

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-success\">",(SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = true AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>") END as odenmis'),

            DB::raw('CASE WHEN (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id) > 0 THEN CONCAT("<button class=\"btn btn-danger\">", (SELECT COUNT(*) FROM taksit_vadeleri

                                WHERE taksit_vadeleri.odendi = false  AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id),"</button>" ) END as odenmemis'),



         DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE CONCAT("<p style=\"display:none\">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%Y%m%d"

                    ),"</p><span class=\"", CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) < NOW() THEN "btn btn-danger" ELSE "" END ," \">",DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ),"</span>") END as yaklasan_vade'),



            DB::raw('CASE WHEN (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1) IS NULL THEN "<button class=\"btn btn-warning\">Kapalı Senet</button>" ELSE DATE_FORMAT(

                        (SELECT taksit_vadeleri.vade_tarih FROM taksit_vadeleri WHERE taksit_vadeleri.odendi=false AND taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id

                                order by taksit_vadeleri.id asc  LIMIT 1),"%d.%m.%Y"

                    ) END as yaklasan_vade_tarihi'),

           DB::raw('CONCAT("<a href=\"#\" name=\"taksit_vadeleri\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-primary\">Detaylar</a> <a title=\"Tahsil Et\" href=\"/isletmeyonetim/tahsilat/",taksitli_tahsilatlar.user_id,"?sube=", taksitli_tahsilatlar.salon_id ,"\"  data-value=\"",taksitli_tahsilatlar.id,"\" class=\"btn btn-success\"><i class=\"fa fa-money\" </a>") as islemler')



            )->where('taksitli_tahsilatlar.salon_id',self::mevcutsube($request))->where(function($q) use($musteriid){if($musteriid!='') $q->where('taksitli_tahsilatlar.user_id',$musteriid);})->where('taksit_vadeleri.odendi','<',true)->where('taksit_vadeleri.vade_tarih','<=',date('Y-m-d'))->groupBy('taksit_vadeleri.taksitli_tahsilat_id')->get();

            exit;

        }

        

    }

    public function pakettahsilatagit(Request $request){

        $bilgi = Paketler::whereIn('id',$request->paket_bilgi)->get();

        $paketler = self::paket_liste_getir("",true,$request);

        $isletme =Salonlar::where('id',self::mevcutsube($request))->first();

        $adisyon_id = self::yeni_adisyon_olustur($request->paket_satis_musteri_id,$request->sube,'Paket Satışı',date('Y-m-d'));

        $hizmete_ait_randevu = array();

        foreach($bilgi as $key => $paket)

        {

            $toplamtutar=0;

            foreach ($paket->hizmetler as $hizmet) 

            {

                $toplamtutar+=$hizmet->fiyat;

            }

            $adisyon_paket_id = self::adisyona_paket_ekle($adisyon_id,$paket->id,$toplamtutar,$request->paket_satis_seans_baslangic[$key],$request->paket_satis_seans_araligi[$key],Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id'),null,null);

            

            $paket_mevcut = Paketler::where('id',$paket->id)->first();

            

            $seanstarih = '';

             

            

            $yenisaatbaslangic = '';

            foreach($paket_mevcut->hizmetler as $key2 => $hizmet2)

            {

                $randevu_id  ='';

                for($i=1;$i<=$hizmet2->seans;$i++)

                {

                     

                    if($i==1){

                        $seanstarih = $request->paket_satis_seans_baslangic[$key];

                        

                    }

                    if($i>1){

                        $seanstarih = date('Y-m-d',strtotime('+'.$request->paket_satis_seans_araligi[$key].' days',strtotime($seanstarih)));

                         

                    }  

                    

                    $hizmet_sure = 60;

                    if(SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$hizmet2->hizmet_id)->value('sure_dk') > 0)

                        $hizmet_sure = SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$hizmet2->hizmet_id)->value('sure_dk');

                    if($key2==0||count($hizmete_ait_randevu)<$i)

                    {

                         

                        $yenisaatbaslangic = $request->paket_satis_seans_saati[$key];

                        $seans_randevu = new Randevular(); 

                        

                        $seans_randevu->user_id = $request->paket_satis_musteri_id;

                        $seans_randevu->tarih = $seanstarih;

                        $seans_randevu->salon_id = $request->sube;

                        $seans_randevu->durum = 1;

                        $seans_randevu->saat = $request->paket_satis_seans_saati[$key];

                        $seans_randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;  

                     

                        $seans_randevu->salon = 1;

                        $seans_randevu->save(); 

                        

                        $randevu_id = $seans_randevu->id; 

                        array_push($hizmete_ait_randevu,$randevu_id);

                        if($i==$hizmet2->seans)

                            $yenisaatbaslangic = date("H:i", strtotime('+'.$hizmet_sure.' minutes', strtotime($request->paket_satis_seans_saati[$key])));

                    }

                    else

                        $randevu_id = $hizmete_ait_randevu[$i-1];



                    

                    

                    $seans = new AdisyonPaketSeanslar();

                    $seans->adisyon_paket_id = $adisyon_paket_id;

                    $seans->seans_tarih = $seanstarih;

                    $seans->hizmet_id = $hizmet2->hizmet_id;

                    $seans->seans_no = $i;

                    $seans->seans_saat = $yenisaatbaslangic;



                    $seans->randevu_id = $randevu_id;

                    $seans->save();





                    $seans_randevu_hizmet = new RandevuHizmetler();

                    $seans_randevu_hizmet->randevu_id = $randevu_id;

                    $seans_randevu_hizmet->hizmet_id =$hizmet2->hizmet_id;

                     $seans_randevu_hizmet->personel_id = 183;

                    $seans_randevu_hizmet->sure_dk = $hizmet_sure;

                    if($key2==0||count($hizmete_ait_randevu)<$i)    

                        $seans_randevu_hizmet->saat = $request->paket_satis_seans_saati[$key];

                    else

                        $seans_randevu_hizmet->saat = $yenisaatbaslangic;



                     

                    $seans_randevu_hizmet->saat_bitis = date("H:i", strtotime('+'.$hizmet_sure.' minutes', strtotime($yenisaatbaslangic))); 

                    $seans_randevu_hizmet->save(); 

                } 

            }



            

        }

        return $request->paket_satis_musteri_id.'?sube='.self::mevcutsube($request);

 

         

    

    }

    public function uruntahsilatagit(Request $request){

        $bilgi = Urunler::whereIn('id',$request->urun_bilgi)->get();

        $urunler = self::urun_liste_getir($request,"");

        $isletme =Salonlar::where('id',self::mevcutsube($request))->first();

        $adisyon_id = self::yeni_adisyon_olustur($request->urun_satis_musteri_id,$request->sube,'Ürün Satışı',date('Y-m-d'));

        foreach($bilgi as $key => $urun)

        {

                $urun->stok_adedi -= $request->urun_adedi_tahsilat[$key];

                $urun->save();

                $adisyon_urun = new AdisyonUrunler();

                $adisyon_urun->islem_tarihi = date('Y-m-d');

                $adisyon_urun->adisyon_id= $adisyon_id;

                $adisyon_urun->urun_id = $urun->id;

                $adisyon_urun->adet = $request->urun_adedi_tahsilat[$key];

                $adisyon_urun->fiyat = $urun->fiyat*$request->urun_adedi_tahsilat[$key];

                $adisyon_urun->personel_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

                $adisyon_urun->save();





           

        }

        return $request->urun_satis_musteri_id.'?sube='.self::mevcutsube($request);;

    }

  

    public function musteriindirim_kaydet(Request $request){

        $isletme=Salonlar::where('id',$request->sube)->first();





        if(isset($request->sadik_acikkapali)){

            $isletme->sadik_musteri_indirim_yuzde=$request->sadik_musteri_indirimi;

        }

        else

            $isletme->sadik_musteri_indirim_yuzde=0;

        if (isset($request->aktif_acikkapali)) {

            $isletme->aktif_musteri_indirim_yuzde=$request->aktif_musteri_indirimi;

        }

        else

            $isletme->aktif_musteri_indirim_yuzde=0;

        

      

        $isletme->save();

        return('İşlem başarıyla kaydedildi');



    }

    public function hizmettahsilattutaridegistir(Request $request)

    {

         $adisyon_hizmet = AdisyonHizmetler::where('id',$request->adisyonhizmetid)->first();

         $adisyon_hizmet->fiyat = str_replace(['.',','],['','.'],$request->tutar);

         $adisyon_hizmet->save();

    }

    public function uruntahsilattutaridegistir(Request $request)

    {

         $adisyon_urun = AdisyonUrunler::where('id',$request->adisyonurunid)->first();

         $adisyon_urun->fiyat = str_replace(['.',','],['','.'],$request->tutar);

         $adisyon_urun->save();





    }

    public function urunadetdegistir(Request $request)

    {

         $adisyon_urun = AdisyonUrunler::where('id',$request->adisyonurunid)->first();

         $musteri_id = Adisyonlar::where('id',$adisyon_urun->adisyon_id)->value('user_id');

         $adisyon_urun->adet = $request->adet;

         $adisyon_urun->fiyat = $adisyon_urun->urun->fiyat * $request->adet;

         $adisyon_urun->save();

         return self::musteri_tahsilatlari($request,$musteri_id,"");





    }

    public function pakettahsilattutaridegistir(Request $request)

    {

        $adisyon_paket = AdisyonPaketler::where('id',$request->adisyonpaketid)->first();

        $adisyon_paket->fiyat = str_replace(['.',','],['','.'],$request->tutar);

        $adisyon_paket->save();

    }

    public function hizmet_hediye_isle(Request $request)

    {

        $adisyon_hizmet = AdisyonHizmetler::where('id',$request->adisyonhizmetid)->first();

        $adisyon_hizmet->fiyat = 0;

        $musteri_id = Adisyonlar::where('id',$adisyon_hizmet->adisyon_id)->value('user_id');

        $adisyon_hizmet->hediye = true;

        $adisyon_hizmet->save();

        return self::musteri_tahsilatlari($request,$musteri_id,"");

        



    }

    public function hizmet_hediye_kaldir(Request $request)

    {

        $adisyon_hizmet = AdisyonHizmetler::where('id',$request->adisyonhizmetid)->first();

        $adisyon_hizmet->fiyat = SalonHizmetler::where('hizmet_id',$adisyon_hizmet->hizmet_id)->value('baslangic_fiyat');

        $musteri_id = Adisyonlar::where('id',$adisyon_hizmet->adisyon_id)->value('user_id');

        $adisyon_hizmet->hediye = false;

        $adisyon_hizmet->save();

        return self::musteri_tahsilatlari($request,$musteri_id,"");

       



    }

    public function urun_hediye_isle(Request $request)

    {

        $adisyon_urun = AdisyonUrunler::where('id',$request->adisyonurunid)->first();

        $adisyon_urun->fiyat = 0;

        $musteri_id = Adisyonlar::where('id',$adisyon_urun->adisyon_id)->value('user_id');

        $adisyon_urun->hediye = true;

        $adisyon_urun->save();

        return self::musteri_tahsilatlari($request,$musteri_id,"");



    }

    public function urun_hediye_kaldir(Request $request)

    {

        $adisyon_urun = AdisyonUrunler::where('id',$request->adisyonurunid)->first();

        $adisyon_urun->fiyat = $adisyon_urun->urun->fiyat * $request->adet;

        $musteri_id = Adisyonlar::where('id',$adisyon_urun->adisyon_id)->value('user_id');

        $adisyon_urun->hediye = false;

        $adisyon_urun->save();

        return self::musteri_tahsilatlari($request,$musteri_id,"");



    }

    public function paket_hediye_isle(Request $request)

    {

        $adisyon_paket = AdisyonPaketler::where('id',$request->adisyonpaketid)->first();

        $adisyon_paket->fiyat = 0;

        $musteri_id = Adisyonlar::where('id',$adisyon_paket->adisyon_id)->value('user_id');

        $adisyon_paket->hediye = true;

        $adisyon_paket->save();

        return self::musteri_tahsilatlari($request,$musteri_id,"");



    }

    public function paket_hediye_kaldir(Request $request)

    {

        $adisyon_paket = AdisyonPaketler::where('id',$request->adisyonpaketid)->first();

        

        $adisyon_paket->fiyat = PaketHizmetler::where('paket_id',$adisyon_paket->paket_id)->sum('fiyat');

        $musteri_id = Adisyonlar::where('id',$adisyon_paket->adisyon_id)->value('user_id');

        $adisyon_paket->hediye = false;

        $adisyon_paket->save();

       return self::musteri_tahsilatlari($request,$musteri_id,"");



    }

    public function ajanda(Request $request){ 

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

       

         

        $paketler = self::paket_liste_getir('',true,$request);

      

        $kalan_uyelik_suresi = self::lisans_sure_kontrol($request);  

        $ajanda_liste = self::ajanda_liste_getir($request,'');        

        

        return view('isletmeadmin.ajanda',['bildirimler'=>self::bildirimgetir($request),'ajanda'=>$ajanda_liste, 'paketler'=>$paketler,'sayfa_baslik'=>'Ajanda','pageindex' => 40,'isletme'=>$isletme,'kalan_uyelik_suresi'=>$kalan_uyelik_suresi,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function ajanda_liste_getir(Request $request, $returntext){

        $ajanda = "";

        $ajanda=DB::table('ajanda')->join('salon_personelleri','ajanda.ajanda_olusturan','=','salon_personelleri.id')->

        select(

            'ajanda.id as id',

            'ajanda.ajanda_baslik as title',

            'ajanda.ajanda_icerik as description',

            'ajanda.ajanda_hatirlatma_saat as ajanda_hatirlatma_saat',

            DB::raw("CASE WHEN ajanda.ajanda_hatirlatma=1 THEN '<i class=\'fa fa-check\' style=\'font-size:20px; color:green;margin-left:40px;\'> </i>' WHEN ajanda.ajanda_hatirlatma=0 THEN '<i class=\'fa fa-check\' style=\'font-size:20px; color:green; display:none; margin-left:40px;\'> </i>' END AS ajanda_hatirlatma"),

           

            DB::raw('CONCAT(ajanda_tarih," ",DATE_FORMAT(ajanda.ajanda_saat, "%H:%i")) as start'),

            DB::raw('CONCAT(ajanda_tarih," ",DATE_ADD(ajanda.ajanda_saat, INTERVAL 30 MINUTE)) as end'),



            'salon_personelleri.personel_adi as ajanda_olusturan',

            DB::raw("CASE WHEN ajanda.ajanda_durum=1 THEN '<button class=\'btn btn-success btn-block\' style=\'line-height:5px\'>Okundu</button>' WHEN 

            ajanda.ajanda_durum=0 THEN '<button class=\'btn btn-danger btn-block\' style=\'line-height:5px\'>Okunmadı</button>' END AS ajanda_durum"),



            DB::raw('CASE WHEN ajanda.ajanda_durum=1 THEN CONCAT("#008b00") WHEN ajanda.ajanda_durum=0 THEN CONCAT("#5C008E") END AS color'),



            DB::raw('CASE WHEN ajanda.ajanda_durum=1 THEN CONCAT("<div class=\"modal-footer\" style=\"justify-content: center;\">

                   <div class=\"col-md-6 col-xs-6 col-6 col-sm-6\" >

                         <button  data-toggle=\"modal\"  class=\"btn btn-success btn-block\"  data-value=\"",ajanda.id,"\" data-target=\"#ajanda_duzenle_modal\" name=\"ajanda_notu_duzenle\" >Düzenle</button>

                   </div>

                </div>") WHEN ajanda.ajanda_durum=0 THEN CONCAT(

               "<div class=\"modal-footer\" style=\"justify-content: center;\">

                   <div class=\"col-md-6 col-xs-6 col-6 col-sm-6\" >

                         <button  data-toggle=\"modal\"  class=\"btn btn-success btn-block\"  data-value=\"",ajanda.id,"\" data-target=\"#ajanda_duzenle_modal\" name=\"ajanda_notu_duzenle\" >Düzenle</button>

                   </div>

                </div>"

                ) END AS eventbuttons'),

            

            DB::raw('CASE WHEN ajanda.ajanda_durum=1 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\" data-toggle=\"modal\" data-value=\"",ajanda.id,"\" data-target=\"#ajanda_duzenle_modal\" name=\"ajanda_notu_duzenle\" href=\"#\"> <i class=\"fa fa-edit\"></i> Düzenle</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",ajanda.id,"\" name=\"ajanda_sil\"   href=\"#\"> <i class=\"fa fa-times\"></i>Sil</a>

                   </div></div>") 

               WHEN  ajanda.ajanda_durum=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\" data-toggle=\"modal\" data-value=\"",ajanda.id,"\" data-target=\"#ajanda_duzenle_modal\" name=\"ajanda_notu_duzenle\" href=\"#\"> <i class=\"fa fa-edit\"></i> Düzenle</a>

                    <a class=\"dropdown-item \" name=\"ajanda_okundu_isaretle\" data-value=\"",ajanda.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i>Okundu</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",ajanda.id,"\" name=\"ajanda_sil\"   href=\"#\"> <i class=\"fa fa-times\"></i>Sil</a>

                   </div></div>")  

                   END AS islemler')

        )->where('ajanda.aktif',true)->where('ajanda.salon_id',self::mevcutsube($request))->where('ajanda.ajanda_olusturan',Personeller::where('salon_id',self::mevcutsube($request))->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id'))->orderBy('ajanda.id','desc')->get();

       return array(

                'status' => $returntext,

                'ajanda' => $ajanda,

    

        ); 

    }



    public function ajandaya_yeni_not_ekle(Request $request){



            $yeninot = new Ajanda();

            $returntext = "Notunuz başarıyla eklendi";

        

       



        $yeninot->ajanda_baslik=$request->ajandabaslik;

        $yeninot->ajanda_tarih=$request->ajandatarih;

        if (isset($request->ajandahatirlatma)) {

           $yeninot->ajanda_hatirlatma=1;

        }

        $yeninot->ajanda_saat=$request->ajandasaat;

        $yeninot->ajanda_icerik=$request->ajandaicerik;

        $yeninot->ajanda_hatirlatma_saat=$request->ajanda_hatirlatma_saat_once;

        $yeninot->salon_id = $request->sube;

        $yeninot->ajanda_olusturan=Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        $yeninot->aktif=true;

        $yeninot->save();

        return self::ajanda_liste_getir($request,$returntext);







    }

    public function ajanda_guncelle(Request $request){

      

      $ajandanot = Ajanda::where('id',$request->ajanda_id_duzenle)->first();

        $returntext = "Notunuz başarıyla güncellendi";

        $salon_id = Adisyonlar::where('id',$request->ajanda_id_duzenle)->value('salon_id');

       



        $eventtarih=$ajandanot->ajanda_tarih;

        $eventsaat=$ajandanot->ajanda_saat;



        $girilentarih=$request->ajandatarihduzenle;

        $girilensaat=$request->ajandasaatduzenle;





        $ajandanot->ajanda_baslik=$request->ajandabaslikduzenle;

        $ajandanot->ajanda_tarih=$request->ajandatarihduzenle;

                $ajandanot->ajanda_saat=$request->ajandasaatduzenle;

        $ajandanot->ajanda_icerik=$request->ajandaicerikduzenle;

        $ajandanot->ajanda_hatirlatma_saat=$request->ajanda_hatirlatma_saat_once_duzenle;

        if (isset($request->ajandahatirlatmaduzenle)) {

           $ajandanot->ajanda_hatirlatma=1;

            $sms = SalonSMSAyarlari::where('salon_id',$salon_id)->where('ayar_id',17)->value('personel');

        }





        if( strtotime($eventtarih) < strtotime( $girilentarih)){

            $ajandanot->ajanda_durum=0;

        }

        if( strtotime($eventsaat) < strtotime( $girilensaat)){

            $ajandanot->ajanda_durum=0;

        }

        $ajandanot->ajanda_olusturan=Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        $ajandanot->salon_id = $request->sube;

        $ajandanot->aktif=true;

        $ajandanot->save();

         

        return self::ajanda_liste_getir($request,$returntext);









    }

    

    public function ajanda_sil(Request $request){

        $ajandanot = Ajanda::where('id',$request->ajanda_id)->first();

        $ajandanot->aktif = false;

        $ajandanot->save();

        

        return self::ajanda_liste_getir($request,"Notunuz başarıyla kaldırıldı");



    }

    public function ajanda_okunduisaretle(Request $request){

         $ajandanot = Ajanda::where('id',$request->ajanda_id)->first();

        $returntext = "Notunuz okundu olarak güncellendi";



       $ajandanot->ajanda_durum=1;



        $ajandanot->aktif=true;

        $ajandanot->save();

        return self::ajanda_liste_getir($request,$returntext);



    }





     public function takvim_degistir_ajanda(Request $request)

    {

        return self::ajanda_liste_getir($request,'');

    }

    public function ajandadetay(Request $request){

        $ajandanot = Ajanda::where('id',$request->ajandaid)->first();

         $returntext = "Notunuz başarıyla güncellendi";



        $ajandanot->aktif=true;

        return  array(

            'tarih'=>$ajandanot->ajanda_tarih,

            'saat'=>$ajandanot->ajanda_saat,

            'baslik'=>$ajandanot->ajanda_baslik,

            'icerik'=>$ajandanot->ajanda_icerik,

            'hatirlatma'=>$ajandanot->ajanda_hatirlatma,

            'durum'=>$ajandanot->ajanda_durum,

            'status'=>$returntext,

            'id'=>$ajandanot->id,

            'hatirlatmasaat'=>$ajandanot->ajanda_hatirlatma_saat,

        );



        

    }

    public function eventrenk(Request $request){

         $ajandanot = Ajanda::where('id',$request->ajanda_id)->first();

        $returntext = "Notunuz okundu olarak güncellendi";



       $ajandanot->ajanda_durum=1;



        $ajandanot->aktif=true;

        $ajandanot->save();

        return self::ajanda_liste_getir($request,$returntext);



    }

    public function ajandadetaygetir(Request $request){

        $ajandanot = Ajanda::where('id',$request->ajandaid)->first();

        $bildirim = Bildirimler::where('id',$request->bildirimid)->first();

        $bildirim->okundu = true;

        $bildirim->save();

        return array(

            'tarih' => date('d/m/Y',strtotime($ajandanot->ajanda_tarih)),

            'saat' => date('H:i',strtotime($ajandanot->ajanda_saat)),

            'baslik' => $ajandanot->ajanda_baslik .' Not Detayı',

            'icerik' => $ajandanot->ajanda_icerik,

            'id'=> $ajandanot->id





        );

    }

    public function taksitsenetkontrol(Request $request)

    {

        $senetler_acik = self::senetleri_getir($request,0,$request->user_id);

        $taksitler_acik = self::taksitleri_getir($request,0,$request->user_id);

        $senetler_odenmemis = self::senetleri_getir($request,2,$request->user_id);

        $taksitler_odenmemis = self::taksitleri_getir($request,2,$request->user_id);

        $text = "";

        $odenmemis = false;

        if($taksitler_odenmemis->count()>0){

            $text .= $taksitler_odenmemis[0]->yaklasan_vade_tarihi;

            $odenmemis = true;

        }

        if($senetler_odenmemis->count()>0){

            $text .= $senetler[0]->yaklasan_vade_tarihi;

            $odenmemis = true;

        }

        if(($senetler_acik->count()>0 || $taksitler_acik->count()>0) && !$odenmemis)  

            $text .= User::where('id',$request->user_id)->value('name') ." için devam eden vadeler bulunmaktadır. Yine de işleme devam etmek istiyor musunuz?"; 

        if($odenmemis)

            $text .= " tarihinde ödenmemiş vade bulunmaktadır. Yine de işleme devam etmek istiyor musunuz?";

        return $text;

       



         

    }

    public function santral(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu <2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $paketler = self::paket_liste_getir('',true,$request);

        /*$interval = '-1 days';

        if(self::haftanin_gunu(date('Y-m-d'))==1)

            $interval = '-3 days';*/

        $santral_raporlari = self::santral_raporlari($isletme->id,date('Y-m-d'),date('Y-m-d'),'',$request);

        

        $kalan_uyelik_suresi = self::lisans_sure_kontrol($request);



        

        $santral_ayarlari = SalonSantralAyarlari::where('salon_id',self::mevcutsube($request))->get();

         

        return view('isletmeadmin.santral',['bildirimler'=>self::bildirimgetir($request),'paketler'=>$paketler,'sayfa_baslik'=>'Santral Sistemi & Aramalar','pageindex' => 43,'isletme'=>$isletme,'kalan_uyelik_suresi'=>$kalan_uyelik_suresi, 'santral_raporlari'=>$santral_raporlari,

            'santral_ayarlari' => $santral_ayarlari,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler



    ]);

    }

    public function cdr_rapor_filtre(Request $request)

    {

        $baslangic = date('Y-m-d');

        $bitis = date('Y-m-d');

        if(isset($request->cdr_baslangic_tarihi))

        {

            if($request->cdr_baslangic_tarihi!='')

                $baslangic = $request->cdr_baslangic_tarihi;

        }

        if(isset($request->cdr_bitis_tarihi)){

            if($request->cdr_bitis_tarihi!='')

                $bitis = $request->cdr_bitis_tarihi;

        }

        return self::santral_raporlari($request->sube,$baslangic,$bitis,'',$request);

    }

   

    public function santral_raporlari($salon_id,$tarih1,$tarih2,$durum,$request)

    {

        $authToken = '';

        if(Salonlar::where('id',$salon_id)->value('santral_token_expires') < date('Y-m-d H:i:s'))

            $authToken = self::santral_token_al($salon_id);

        else

            $authToken = Salonlar::where('id',$salon_id)->value('santral_token');

         

        $endpoint = "http://34.45.69.65/admin/api/api/gql";

        $qry = 'query{

          fetchAllCdrs (

             first : 99999999 

            startDate: "'.$tarih1.'"

            endDate: "'.$tarih2.'"

          )

          {

            cdrs {

              id

                uniqueid

                calldate

                timestamp

                clid

                src

                dst

                dcontext

                channel

                dstchannel

                lastapp

                lastdata

                duration

                billsec

                disposition

                accountcode

                userfield

                did

                recordingfile

                cnum

                outbound_cnum

                outbound_cnam

                dst_cnam

                linkedid

                peeraccount

                sequence

                amaflags

            }

            totalCount

            status

            message

          }

        }';

        $headers = array();

        $headers[] = 'Content-Type: application/json';

        $headers[] = 'Authorization: Bearer '.$authToken;



        $ch = curl_init();



        curl_setopt($ch, CURLOPT_URL, $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $qry]));

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);



        $result = json_decode(curl_exec($ch),true);

        $rapor = array();

        $gelen_arama = 0;

        $giden_arama = 0;

        $cevapsiz_arama = 0;

        $sesli_mesaj = 0;

        $basarisiz_arama = 0;

        if($result['data']['fetchAllCdrs']['totalCount']>0)

        {



            foreach($result['data']['fetchAllCdrs']['cdrs'] as $cdr)

            {

                if(SabitNumaralar::where('salon_id',self::mevcutsube($request))->value('numara'))

                {

                    if($cdr['src']==SabitNumaralar::where('salon_id',self::mevcutsube($request))->value('numara') || $cdr['did']==SabitNumaralar::where('salon_id',self::mevcutsube($request))->value('numara'))

                {  

                        $tel_kaynak = str_replace('+','',$cdr['src']);

                        $tel_hedef = str_replace('+','',$cdr['dst']);

                        $tel_kaynak = str_replace('90','',$tel_kaynak);

                        $tel_hedef = str_replace('90','',$tel_hedef);

                        $tel_kaynak = ltrim($tel_kaynak, '0'); 

                        $tel_hedef = ltrim($tel_hedef, '0'); 

                        $musteri_tel = '';

                        $musteri_adi = '';

                        $durum = '';

                        $gorusmeyi_yapan='';

                        $cevapsiz_arama_var = true;

                        $musteri_var = User::join('musteri_portfoy','musteri_portfoy.user_id','=','users.id')->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('musteri_portfoy.salon_id',$salon_id)->where(function($q) use($tel_kaynak,$tel_hedef){ 

                            $q->where('users.cep_telefon',$tel_kaynak);

                            $q->orWhere('users.cep_telefon',$tel_hedef); })->first();

                        if($musteri_var){

                            $musteri_tel= $musteri_var->telefon;

                            $musteri_adi = $musteri_var->ad_soyad;

                        } 

                        else

                        {

                            if(SabitNumaralar::where('numara',$cdr['dst'])->count()==0)

                                $musteri_tel = $tel_hedef;

                            else

                                $musteri_tel = $tel_kaynak;

                        }

                        if($cdr['disposition']=='NO ANSWER' && str_contains($cdr['recordingfile'],'in-')){

                            $durum = '<button class="btn btn-danger">CEVAPSIZ</button>';

                            $gorusmeyi_yapan =  Personeller::where('dahili_no',$cdr['cnum'])->orWhere('dahili_no',$cdr['dst'])->value('personel_adi');

                            $cevapsiz_arama++;

                            

                        }

                        else{

                            $cevapsiz_arama_var = false;

                            if(SabitNumaralar::where('salon_id',self::mevcutsube($request))->value('numara')==$cdr['src']){

                                if($cdr['disposition']=='NO ANSWER'){

                                    $durum = '<button class="btn btn-danger"><span style="display:none">GİDEN</span>ULAŞILAMADI</button>'; 

                                    $basarisiz_arama++;

                                    $cevapsiz_arama_var = true;

                                }

                                else

                                {

                                    $durum = '<button class="btn btn-primary">GİDEN</button>'; 

                                }

                                

                                $gorusmeyi_yapan = Personeller::where('dahili_no',$cdr['cnum'])->value('personel_adi');

                                $giden_arama++;

                            }

                            else{   

                                if($cdr['lastapp']=='VoiceMail' || str_contains($cdr['dst'],'vmu') )

                                {

                                    $cevapsiz_arama_var = true;

                                    $durum = '<button class="btn btn-info">SESLİ MESAJ</button>';

                                    $dst = ltrim($cdr['dst'],'vmu');

                                    $gorusmeyi_yapan = Personeller::where('dahili_no',$dst)->value('personel_adi'); 

                                    $sesli_mesaj++;

                                }

                                else{ 

                                    $durum = '<button class="btn btn-success">GELEN</button>';

                                    $gorusmeyi_yapan = Personeller::where('dahili_no',$cdr['dst'])->value('personel_adi'); 

                                    $gelen_arama++;

                                }

                                



                            }

                        }

                        $arama_butonu = '<button title="Ara" class="btn btn-success" name="musteriyi_ara" style="margin-right:2px" data-value="0'.$musteri_tel.'"><i class="fa fa-phone"></i></button>';

                        $ses_kaydi = '';

                        $tarih_dir =explode("-",$cdr['calldate']);

                        $tarih_son = explode(' ',$tarih_dir[2]);

                        if(!$cevapsiz_arama_var)

                            $ses_kaydi = '<button name="ses_kaydi_cal" data-value="https://voicerecords.randevumcepte.com.tr/monitor/'.$tarih_dir[0].'/'.$tarih_dir[1].'/'.$tarih_son[0].'/'.$cdr['recordingfile'].'" class="btn btn-danger"><i class="fa fa-play"></i></button>';

                        

                        array_push($rapor,array(

                            'tarih' => date('d.m.Y',strtotime($cdr['calldate'])),

                            'saat' => date('H:i',strtotime('+3 hours', strtotime( $cdr['calldate']))),

                            'musteri' => $musteri_adi,

                            'gorusmeyiyapan' => $gorusmeyi_yapan,

                            'telefon'=> $musteri_tel,

                            'durum' => $durum,

                            'seskaydi' => $arama_butonu.$ses_kaydi





                        ));

                     

                }

                }



                

                     

                 

                

               

                

            }

        }

        

        return  array(



            'rapor'=>$rapor,

            'gelen_arama'=>$gelen_arama,

            'giden_arama'=>$giden_arama,

            'cevapsiz_arama'=>$cevapsiz_arama,

            'basarisiz_arama'=>$basarisiz_arama

            

        ); 



    }

    

    public function santral_token_al($salon_id)

    {

         $ch = curl_init();



        curl_setopt($ch, CURLOPT_URL, 'http://34.45.69.65/admin/api/api/token');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        $post = array(

            'grant_type' => 'client_credentials',

            'client_id' => 'ab6553d9183c664f87b8236a75cb6727f8d333586b8c1607c01426ebd9390add',

            'client_secret' => '9a44c50ba6d572e7263c97120fee00a0'

        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

       

        $result = curl_exec($ch);

        

        if (curl_errno($ch)) {

            echo 'Error:' . curl_error($ch);

        }

         curl_close($ch);

        $result2 = json_decode($result,true);

        $isletme = Salonlar::where('id',$salon_id)->first();

        $isletme->santral_token = $result2['access_token'];

        $isletme->santral_token_expires = date('Y-m-d H:i:s',strtotime('+55 minutes',strtotime(date('Y-m-d H:i:s'))));

        $isletme->save();

        return $result2["access_token"];

    }

     



    public function arsivyonetimi(Request $request){ 

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ) || $isletme->uyelik_turu < 2)

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

       if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

         

        $paketler = self::paket_liste_getir('',true,$request);

      

        $kalan_uyelik_suresi = self::lisans_sure_kontrol($request); 





        $arsiv=self::arsiv_liste_getir($request,'',''); 

        $arsiv_onayli=self::arsiv_liste_getir($request,0,'');

        $arsiv_iptal=self::arsiv_liste_getir($request,1,'');

        $arsiv_beklenen=self::arsiv_liste_getir($request,2,'');

        $arsiv_harici=self::arsiv_liste_getir($request,3,'');



    

        

        return view('isletmeadmin.arsivyonetimi',['bildirimler'=>self::bildirimgetir($request),'arsiv'=>$arsiv,'arsiv_onayli'=>$arsiv_onayli, 'arsiv_iptal'=>$arsiv_iptal, 'arsiv_beklenen'=>$arsiv_beklenen, 'arsiv_harici'=>$arsiv_harici,   'paketler'=>$paketler,'sayfa_baslik'=>'Arşiv Yönetimi','pageindex' => 50,'isletme'=>$isletme,'kalan_uyelik_suresi'=>$kalan_uyelik_suresi,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }





    public function arsiv_liste_getir(Request $request,$sorgu,$user_id){



        if($sorgu===''){

             return DB::table('arsiv')->leftjoin('formtaslaklari','arsiv.form_id','=','formtaslaklari.id')->join('users','arsiv.user_id','=','users.id')->join('salon_personelleri','arsiv.personel_id','=','salon_personelleri.id')->select(

                'arsiv.id as id',

                'users.name as musteriadi',

                DB::raw(' CONCAT("<a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_goster\" href=\"#\">",CASE WHEN arsiv.form_id !=0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END , ".pdf <i class=\"fa fa-file-pdf-o\"></i> </a>") AS belge_durum'),

                DB::raw('CASE WHEN arsiv.form_id != 0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END AS baslik'),

                DB::raw('CONCAT("<span style=\"display:none\">", DATE_FORMAT(arsiv.created_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(arsiv.created_at,"%d.%m.%Y") )   as tarih'),

                DB::raw("CASE WHEN arsiv.durum=1 THEN '<button class=\'btn btn-success \' style=\'line-height:5px;\'>Onaylandı</button>' WHEN 

            arsiv.durum=0 THEN '<button class=\'btn btn-danger \' style=\'line-height:5px\'>İptal Edildi</button>' WHEN arsiv.durum IS NULL THEN CASE

            WHEN arsiv.cevapladi=1 OR arsiv.cevapladi2=1 THEN '<button class=\'btn btn-warning \' >Onay Bekleniyor </button>' WHEN arsiv.cevapladi=0 OR arsiv.cevapladi2=0 THEN '<button class=\'btn btn-warning \' >Form Bekleniyor</button>' WHEN arsiv.cevapladi IS NULL OR arsiv.cevapladi2 IS NULL THEN '<button class=\'btn btn-primary \' >Harici Belge </button>' END END AS durum "),

                 DB::raw('CASE WHEN arsiv.form_id=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                WHEN arsiv.durum IS NULL THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.id,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>



                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\"  name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>



                    <a class=\"dropdown-item \"  name=\"form_onaylandı\" data-value=\"",arsiv.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i>Onayla</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                WHEN arsiv.durum=1 OR arsiv.durum=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\"  data-value=\"",arsiv.id,"\"  href=\"#\"  name=\"form_yazdir\" > <i class=\"fa fa-print\"></i> Yazdır</a>

                        <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\" name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>

            

                   </div></div>") END AS islemler'))->where('arsiv.salon_id',self::mevcutsube($request))->where(function($q) use($user_id){if(is_numeric($user_id)) $q->where('arsiv.user_id',$user_id);} )->orderBy('arsiv.id','desc')->get();exit;

        }

        if($sorgu===0){

            return DB::table('arsiv')->leftjoin('formtaslaklari','arsiv.form_id','=','formtaslaklari.id')->join('users','arsiv.user_id','=','users.id')->

        join('salon_personelleri','arsiv.personel_id','=','salon_personelleri.id')->

        select(



            'arsiv.id as id',

           DB::raw(' CONCAT("<a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_goster\" href=\"#\">",CASE WHEN arsiv.form_id !=0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END , ".pdf <i class=\"fa fa-file-pdf-o\"></i> </a>") AS belge_durum'),

            'users.name as musteriadi',

            DB::raw('CASE WHEN arsiv.form_id != 0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END AS baslik'),

            DB::raw('CONCAT("<span style=\"display:none\">", DATE_FORMAT(arsiv.created_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(arsiv.created_at,"%d.%m.%Y") )   as tarih'),

            DB::raw('CASE WHEN arsiv.durum=1 THEN "<button class=\"btn btn-success \" style=\"line-height:5px;\">Onaylandı</button>" WHEN 

            arsiv.durum=0 THEN "<button class=\"btn btn-danger \" style=\"line-height:5px\">İptal Edildi</button>" WHEN arsiv.durum IS NULL THEN CASE

            WHEN arsiv.cevapladi=1 OR arsiv.cevapladi2=1 THEN "<button class=\"btn btn-warning \" >Onay Bekleniyor </button>" WHEN arsiv.cevapladi=0 OR arsiv.cevapladi2=0 THEN "<button class=\"btn btn-warning \" >Form Bekleniyor</button>" WHEN arsiv.cevapladi IS NULL OR arsiv.cevapladi2 IS NULL THEN "<button class=\"btn btn-primary \" >Harici Belge </button>" END

            END AS durum '),

            DB::raw('CASE WHEN arsiv.form_id=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>





                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                WHEN arsiv.durum IS NULL THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.id,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>



                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\"  name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>



                    <a class=\"dropdown-item \"  name=\"form_onaylandı\" data-value=\"",arsiv.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i>Onayla</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                   ELSE CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\"  data-value=\"",arsiv.id,"\"  href=\"#\"  name=\"form_yazdir\" > <i class=\"fa fa-print\"></i> Yazdır</a>

                        <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\" name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>

            

                   </div></div>") END AS islemler

                   ' )







        )->where('arsiv.durum', 1)->where('arsiv.salon_id',self::mevcutsube($request))->where(function($q) use($user_id){if(is_numeric($user_id)) $q->where('arsiv.user_id',$user_id);} )->orderBy('arsiv.id','desc')->get();

 exit;



        }

         if($sorgu===1){

                return DB::table('arsiv')->leftjoin('formtaslaklari','arsiv.form_id','=','formtaslaklari.id')->join('users','arsiv.user_id','=','users.id')->

        join('salon_personelleri','arsiv.personel_id','=','salon_personelleri.id')->select(  'arsiv.id as id',

           DB::raw(' CONCAT("<a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_goster\" href=\"#\">",CASE WHEN arsiv.form_id !=0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END , ".pdf <i class=\"fa fa-file-pdf-o\"></i> </a>") AS belge_durum'),

            'users.name as musteriadi',

            DB::raw('CASE WHEN arsiv.form_id != 0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END AS baslik'),

            DB::raw('CONCAT("<span style=\"display:none\">", DATE_FORMAT(arsiv.created_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(arsiv.created_at,"%d.%m.%Y") )   as tarih'),

            DB::raw("CASE WHEN arsiv.durum=1 THEN '<button class=\'btn btn-success \' style=\'line-height:5px;\'>Onaylandı</button>' WHEN 

            arsiv.durum=0 THEN '<button class=\'btn btn-danger \' style=\'line-height:5px\'>İptal Edildi</button>' WHEN arsiv.durum IS NULL THEN CASE

            WHEN arsiv.cevapladi=1 OR arsiv.cevapladi2=1 THEN '<button class=\'btn btn-warning \' >Onay Bekleniyor </button>' WHEN arsiv.cevapladi=0 OR arsiv.cevapladi2=0 THEN '<button class=\'btn btn-warning \' >Form Bekleniyor</button>' WHEN arsiv.cevapladi IS NULL OR arsiv.cevapladi2 IS NULL THEN '<button class=\'btn btn-primary \' >Harici Belge </button>' END

            END AS durum "),

            DB::raw('CASE WHEN arsiv.form_id=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>





                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                WHEN arsiv.durum IS NULL THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.id,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>



                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\"  name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>



                    <a class=\"dropdown-item \"  name=\"form_onaylandı\" data-value=\"",arsiv.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i>Onayla</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                   ELSE CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\"  data-value=\"",arsiv.id,"\"  href=\"#\"  name=\"form_yazdir\" > <i class=\"fa fa-print\"></i> Yazdır</a>

                        <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\" name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>

            

                   </div></div>") END AS islemler

                   '))->where('arsiv.durum', 0)->where('arsiv.salon_id',self::mevcutsube($request))->where(function($q) use($user_id){if(is_numeric($user_id)) $q->where('arsiv.user_id',$user_id);} )->orderBy('arsiv.id','desc')->get();exit;

        }

        if($sorgu===2){

                return DB::table('arsiv')->leftjoin('formtaslaklari','arsiv.form_id','=','formtaslaklari.id')->join('users','arsiv.user_id','=','users.id')->

        join('salon_personelleri','arsiv.personel_id','=','salon_personelleri.id')->

        select(



            'arsiv.id as id',

           DB::raw(' CONCAT("<a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_goster\" href=\"#\">",CASE WHEN arsiv.form_id !=0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END , ".pdf <i class=\"fa fa-file-pdf-o\"></i> </a>") AS belge_durum'),

            'users.name as musteriadi',

            DB::raw('CASE WHEN arsiv.form_id != 0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END AS baslik'),

           DB::raw('CONCAT("<span style=\"display:none\">", DATE_FORMAT(arsiv.created_at,"%Y%m%d%H%i%s"),"</span>",DATE_FORMAT(arsiv.created_at,"%d.%m.%Y") )   as tarih'),

            DB::raw('CASE WHEN arsiv.durum=1 THEN "<button class=\"btn btn-success \" style=\"line-height:5px;\">Onaylandı</button>" WHEN 

            arsiv.durum=0 THEN "<button class=\"btn btn-danger \" style=\"line-height:5px\">İptal Edildi</button>" WHEN arsiv.durum IS NULL THEN CASE

            WHEN arsiv.cevapladi=1 OR arsiv.cevapladi2=1 THEN "<button class=\"btn btn-warning \" >Onay Bekleniyor </button>" WHEN arsiv.cevapladi=0 OR arsiv.cevapladi2=0 THEN "<button class=\"btn btn-warning \" >Form Bekleniyor</button>" WHEN arsiv.cevapladi IS NULL OR arsiv.cevapladi2 IS NULL THEN "<button class=\"btn btn-primary \" >Harici Belge </button>" END

            END AS durum '),

            DB::raw('CASE WHEN arsiv.form_id=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>





                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                WHEN arsiv.durum IS NULL THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.id,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>



                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\"  name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>



                    <a class=\"dropdown-item \"  name=\"form_onaylandı\" data-value=\"",arsiv.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i>Onayla</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                   ELSE CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\"  data-value=\"",arsiv.id,"\"  href=\"#\"  name=\"form_yazdir\" > <i class=\"fa fa-print\"></i> Yazdır</a>

                        <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\" name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>

            

                   </div></div>") END AS islemler

                   ' )







        )->whereNull('arsiv.durum')->where('arsiv.salon_id',self::mevcutsube($request))->where('arsiv.form_id','!=',0)->where(function($q) use($user_id){if(is_numeric($user_id)) $q->where('arsiv.user_id',$user_id);} )->orderBy('arsiv.id','desc')->get();exit;

        }

        if($sorgu===3){

                return DB::table('arsiv')->leftjoin('formtaslaklari','arsiv.form_id','=','formtaslaklari.id')->join('users','arsiv.user_id','=','users.id')->

        join('salon_personelleri','arsiv.personel_id','=','salon_personelleri.id')->

        select(



            'arsiv.id as id',

           DB::raw(' CONCAT("<a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_goster\" href=\"#\">",CASE WHEN arsiv.form_id !=0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END , ".pdf <i class=\"fa fa-file-pdf-o\"></i> </a>") AS belge_durum'),

            'users.name as musteriadi',

            DB::raw('CASE WHEN arsiv.form_id != 0 THEN formtaslaklari.form_adi WHEN arsiv.form_id=0 THEN arsiv.harici_belge END AS baslik'),

            DB::raw('DATE_FORMAT(arsiv.created_at,"%d.%m.%Y") as tarih'),

            DB::raw("CASE WHEN arsiv.durum=1 THEN '<button class=\'btn btn-success \' style=\'line-height:5px;\'>Onaylandı</button>' WHEN 

            arsiv.durum=0 THEN '<button class=\'btn btn-danger \' style=\'line-height:5px\'>İptal Edildi</button>' WHEN arsiv.durum IS NULL THEN CASE

            WHEN arsiv.cevapladi=1 OR arsiv.cevapladi2=1 THEN '<button class=\'btn btn-warning \' >Onay Bekleniyor </button>' WHEN arsiv.cevapladi=0 OR arsiv.cevapladi2=0 THEN '<button class=\'btn btn-warning \' >Form Bekleniyor</button>' WHEN arsiv.cevapladi IS NULL OR arsiv.cevapladi2 IS NULL THEN '<button class=\'btn btn-primary \' >Harici Belge </button>' END

            END AS durum "),

            DB::raw('CASE WHEN arsiv.form_id=0 THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.uzanti,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>





                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                WHEN arsiv.durum IS NULL THEN CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\" data-value=\"",arsiv.id,"\" name=\"form_yazdir\" href=\"#\"> <i class=\"fa fa-print\"></i> Yazdır</a>



                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\"  name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>



                    <a class=\"dropdown-item \"  name=\"form_onaylandı\" data-value=\"",arsiv.id,"\"  href=\"#\"> <i class=\"fa fa-check\"></i>Onayla</a>

                  

                    <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" name=\"form_iptal\"   href=\"#\"> <i class=\"fa fa-times\"></i>İptal Et</a>

                   </div></div>")

                   ELSE CONCAT("<div class=\"dropdown\">

                <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\"><i class=\"dw dw-more\"></i>

                </a>

                <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">

                    <a class=\"dropdown-item\"  href=\"/isletmeyonetim/formindir?arsivid=",arsiv.id,"\"> <i class=\"fa fa-download\"></i> İndir</a>



                      <a class=\"dropdown-item\"  data-value=\"",arsiv.id,"\"  href=\"#\"  name=\"form_yazdir\" > <i class=\"fa fa-print\"></i> Yazdır</a>

                        <a class=\"dropdown-item \" data-value=\"",arsiv.id,"\" data-toggle=\"modal\" data-target=\"#formutekrargondermodal\" name=\"form_tekrar_gonder\"   href=\"#\"> <i class=\"fa fa-send\"></i>Formu Gönder</a>

            

                   </div></div>") END AS islemler

                   ' )







        )->whereNull('arsiv.durum')->where('arsiv.form_id',0)->where('arsiv.salon_id',self::mevcutsube($request))->where(function($q) use($user_id){if(is_numeric($user_id)) $q->where('arsiv.user_id',$user_id);} )->orderBy('arsiv.id','desc')->get();

     exit;



        }



    }



        public function musteriformugonder(Request $request){ 

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

        $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        {

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            return redirect()->route('isletmeadmin.randevular');

           exit(0);

        }

       

         if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        $paketler = self::paket_liste_getir('',true,$request);

      

        $kalan_uyelik_suresi = self::lisans_sure_kontrol($request); 





        $arsiv=self::arsiv_liste_getir($request,'',''); 

        $arsiv_onayli=self::arsiv_liste_getir($request,0,'');

        $arsiv_iptal=self::arsiv_liste_getir($request,1,'');

        $arsiv_beklenen=self::arsiv_liste_getir($request,2,'');

        $arsiv_harici=self::arsiv_liste_getir($request,3,'');



    

        

        return view('isletmeadmin.musteriformugonder',['bildirimler'=>self::bildirimgetir($request),'arsiv'=>$arsiv,'arsiv_onayli'=>$arsiv_onayli, 'arsiv_iptal'=>$arsiv_iptal, 'arsiv_beklenen'=>$arsiv_beklenen, 'arsiv_harici'=>$arsiv_harici,'paketler'=>$paketler,'sayfa_baslik'=>'Form  ','pageindex' => 50,'isletme'=>$isletme,'kalan_uyelik_suresi'=>$kalan_uyelik_suresi,'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }





    public function formmusteribilgigetir(Request $request){



        $ceptelefon = '';

        $kimlikno = '';

        $cinsiyet = '';

        $yas = '';

        if(!empty($request->musteri_id)){



            $musteri=User::where('id',$request->musteri_id)->first();



            $portfoy=MusteriPortfoy::where('salon_id',$request->sube)->where('user_id',$musteri->id)->first();



            $ceptelefon=$musteri->cep_telefon;

            $kimlikno=$musteri->tc_kimlik_no;

            $cinsiyet=$musteri->cinsiyet;

            $yas=$musteri->dogum_tarihi;



        }



        return array(

            'telefon'=>$ceptelefon,

            'tc'=>$kimlikno,

            'cins'=>$cinsiyet,

            'yas'=>$yas



        );



    }

     public function arsivformekleme(Request $request){



        $form=new Arsiv();

        $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);      

        $form->dogrulama_kodu = $kod;

        $form->user_id=$request->formmusterisec;

        $form->form_id=$request->formtaslaklari;

        $form->personel_id=$request->formpersonelsec;

        $form->cevapladi=false;

        $form->cevapladi2=false;



        $form->salon_id = $request->sube;

        $form->form_olusturan=Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

        

        $form->save();

        $gsm = array();

        $mesajlar=array();

         $mesajlar2=array();



        if ($request->formmusterisec) {



            



            $katilim_link = ' Formu doldurmak için : https://'.$_SERVER['HTTP_HOST'].'/musteriformdoldurma/'.$form->id.'/'.$form->user_id.' Onay Kodu:'.$kod;

            if(MusteriPortfoy::where('user_id',$request->formmusterisec)->where('salon_id',$form->salon_id)->value('kara_liste')!=1)

                    array_push($mesajlar, array("to"=>$request->formmustericeptelefon,"message"=>$katilim_link));



        }

        $gonder=self::sms_gonder($request,$mesajlar,true,6,true);

         if ($request->formpersonelsec) {



            



            $katilim_link2 = ' İmza atmak için : https://'.$_SERVER['HTTP_HOST'].'/personelformdoldurma/'.$form->id.'/'.$request->formpersonelsec;

                    array_push($mesajlar2, array("to"=>$request->formmpersonelceptelefon,"message"=>$katilim_link2));



        }

        $gonder2=self::sms_gonder($request,$mesajlar2,true,6,false);

      

       



        return array(

        "gonder"=>$gonder,

        "gonder"=>$gonder2,

        "arsiv"=>self::arsiv_liste_getir($request,'',''),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,''),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,''),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,''),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,''),

 



        );





        









    }

     public function formutekrargonder(Request $request){



        $form = Arsiv::where('id',$request->arsiv_id)->first();

        $mesajlar=array();

        $form->cevapladi=false;

         $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);      

        $form->dogrulama_kodu = $kod;



        $form->durum=null;

        $form->save();

        if ($form->user_id) {

            $katilim_link = ' Formu doldurmak için : https://'.$_SERVER['HTTP_HOST'].'/musteriformdoldurma/'.$form->id.'/'.$form->user_id.' Onay Kodu:'.$kod;

            if(MusteriPortfoy::where('user_id',$form->user_id)->where('salon_id',$form->salon_id)->value('kara_liste')!=1)

                    array_push($mesajlar, array("to"=>$form->musteri->cep_telefon,"message"=>$katilim_link));



        }

        $gonder=self::sms_gonder($request,$mesajlar,true,6,true);

        if(is_numeric($request->musteriid))

        {

        return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',$request->musteriid),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,$request->musteriid),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,$request->musteriid),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,$request->musteriid),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,$request->musteriid),



        );

            exit;

        }

        else{

            return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',''),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,''),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,''),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,''),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,''),



        );

            exit;

        }

        

    }

     public function formpersonelbilgigetir(Request $request){



        $ceptelefon = '';



        if(!empty($request->personel_id)){



            $personel=Personeller::where('id',$request->personel_id)->first();



            $ceptelefon=$personel->cep_telefon;



        }



        return array(

            'telefon'=>$ceptelefon,



        );



    }

   

    public function formindir(Request $request){

        $arsiv = Arsiv::where('id',$request->arsivid)->first();

        $taslak = FormTaslaklari::where('id',$arsiv->form_id)->value('taslak');

        $harici = $arsiv->uzanti;

        $baslik=FormTaslaklari::where('id',$arsiv->form_id)->value('form_adi');

        $isletme=Salonlar::where('id',self::mevcutsube($request))->first();





        if($arsiv->form_id!=0){

               $pdf = PDF::loadView($taslak, [

                'title' => date('Y-m-d-H-i-s'),

                'arsiv'=>$arsiv,

                'isletme'=>$isletme

            ])->setOptions(['defaultFont' => 'sans-serif',



            ]); 

             return $pdf->download($baslik.'.pdf');

             exit();



        }

        else{

         

         return $pdf = response()->download($harici);



             

            exit();

       

        }

        





        

    }

     public function formyazdir(Request $request)

    {

        $arsiv = Arsiv::where('id',$request->arsiv_id)->first();

        $taslak = FormTaslaklari::where('id',$arsiv->form_id)->value('taslak2');

        $baslik=FormTaslaklari::where('id',$arsiv->form_id)->value('form_adi');

        $isletme=Salonlar::where('id',self::mevcutsube($request))->first();

        if(isset($request->bildirimid))

        {

            $bildirim = Bildirimler::where('id',$request->bildirimid)->first();

            $bildirim->okundu = true;

            $bildirim->save();

        }

       



       

                   $html = view($taslak, [

                    'title' => date('Y-m-d-H-i-s'),

                    'arsiv'=>$arsiv,

                    'isletme'=>$isletme

                ]);

                return $html->render();

                exit();



      

        

   

    }

    public function arsivonaylaform(Request $request){

        $form = Arsiv::where('id',$request->arsiv_id)->first();



        $form->durum=1;

        $form->cevapladi=false;

        $form->save();

      if(is_numeric($request->musteriid))

        {

        return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',$request->musteriid),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,$request->musteriid),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,$request->musteriid),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,$request->musteriid),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,$request->musteriid),



        );

            exit;

        }

        else{

            return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',''),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,''),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,''),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,''),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,''),



        );

            exit;

        }

        

    }

      public function arsiviptalform(Request $request){

        $form = Arsiv::where('id',$request->arsiv_id)->first();



        $form->durum=0;

        $form->cevapladi=false;

         $form->save();

        if(is_numeric($request->musteriid))

        {

        return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',$request->musteriid),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,$request->musteriid),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,$request->musteriid),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,$request->musteriid),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,$request->musteriid),



        );

            exit;

        }

        else{

            return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',''),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,''),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,''),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,''),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,''),



        );

            exit;

        }

        

       



      

    }

    public function haricibelgeekleme(Request $request){



        $form =new Arsiv();



        $form->user_id=$request->haricibelgemusteri;

        $form->harici_belge=$request->haricibelgeformbaslik;

        $form->form_id=0;

        $form->personel_id=$request->haricibelgepersonel;

        $form->salon_id = $request->sube;

        $form->form_olusturan=Personeller::where('salon_id',$request->sube)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id');

      

        if(isset($_FILES["hariciformyukle"]["name"])){

          

              $dosya  = $request->hariciformyukle;

              $kaynak = $_FILES["hariciformyukle"]["tmp_name"];

                        $dosya  = str_replace(" ", "_", $_FILES["hariciformyukle"]["name"]);

                        $dosya = str_replace(" ", "-", $_FILES["hariciformyukle"]["name"]);

                        $uzanti = explode(".", $_FILES["hariciformyukle"]["name"]);

                        $hedef  = "./" . $dosya;

                        if (@$uzanti[1]) {

                            if (!file_exists($hedef)) {

                                $hedef   =  "public/formlar/".$dosya;

                                $dosya   = $dosya;

                            }

                            move_uploaded_file($kaynak, $hedef);

                        } 

            }

        $form->uzanti=$hedef;

         $form->save();

        return array(

        "arsiv"=>self::arsiv_liste_getir($request,'',''),

        "arsiv_onayli"=>self::arsiv_liste_getir($request,0,''),

        "arsiv_iptal"=>self::arsiv_liste_getir($request,1,''),

        "arsiv_beklenen"=>self::arsiv_liste_getir($request,2,''),

        "arsiv_harici"=>self::arsiv_liste_getir($request,3,''),

        ); 

    }

    public function ses_kaydi_indir(Request $request)

    {

        $filePath = $request->url;

        $fileName = basename($request->url);

        if (empty($filePath)) {

            echo "'path' cannot be empty";

            exit;

        }



        if (!file_exists($filePath)) {

            echo $filePath." does not exist";

            exit;

        }



        header("Content-disposition: attachment; filename=" . $fileName);

        header("Content-type: " . mime_content_type($filePath));

        readfile($filePath);

        $file_name = basename($request->url); 

      

        // Use file_get_contents() function to get the file 

        // from url and use file_put_contents() function to 

        // save the file by using base name 

        if (file_put_contents($file_name, file_get_contents($request->url))) 

        { 

            return "File downloaded successfully"; 

            exit;

        } 

        else

        { 

            return "File downloading failed."; 

            exit;

        } 

    }

    public function santral_calisma_saati_ayari(Request $request)

    {

        $isletme = self::mevcutsube($request);

        $ch = curl_init(); 

        curl_setopt($ch,CURLOPT_URL,'http://santral.randevumcepte.com.tr/scripts/santralcalismasaatiayari.php?sube=114'); 

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 

     // curl_setopt($ch,CURLOPT_HEADER, false); 

        

        $output=curl_exec($ch); 

        curl_close($ch); 

        return json_decode($output,false);



    }

    public function dahilibilgial(Request $request)

    {

        return Dahililer::where('numara',$request->dahilino)->value('dahili_sifre');

         

    }

    public function dahilibaglandi(Request $request)

    {

        $dahili = Dahililer::where('numara',$request->dahilino)->first();

        if($request->baglandi==1){

            $dahili->durum = true; 

        }

        else{

            $dahili->durum = false; 

        }

        $dahili->bagli_cihaz = $request->device;



        $dahili->save();

        return $request->baglandi;

    }

    public function yenitahsilat(Request $request)

    {

        $isletmeler = Auth::guard('isletmeyonetim')->user()->yetkili_olunan_isletmeler->pluck('salon_id')->toArray();  

          $isletme= Salonlar::where('id',self::mevcutsube($request))->first();

        if(!in_array(self::mevcutsube($request),$isletmeler ))

        {

            return view('isletmeadmin.yetkisizerisim');

           exit(0);

        }

        if(str_contains(self::lisans_sure_kontrol($request),'-'))

        { 

            

            return view('isletmeadmin.lisanssurebitti',['isletme'=>$isletme]);

            exit(0);

        }

        if(count($isletmeler)>1 && !isset($_GET['sube'])) 

        {

            return view('isletmeadmin.isletmesec',['isletmeler'=>$isletmeler,'isletme'=>$isletme]);

            exit(0);

        }

        //$acik_adisyonlar = self::adisyon_yukle($request,'','','1970-01-01',date('Y-m-d'),$musteriid,''); 

        //$user = User::where('id',$musteriid)->first();

        $paketler = self::paket_liste_getir('',true,$request);

        $isletme = Salonlar::where('id',self::mevcutsube($request))->first();

        

         

        //$tahsilatlar = Tahsilatlar::where('user_id',$musteriid)->get();

        //$request->attributes->set('musteriid',$musteriid);



        /*$tum_senetler = self::senetvadegetir_tahsilat($request);

        $tum_takstiler = self::taksitvadegetir_tahsilat($request);

        $senet_gelen_vadeler = SenetVadeleri::join('senetler','senet_vadeleri.senet_id','=','senetler.id')->select('senet_vadeleri.id as senet_vade_id','senet_vadeleri.vade_tarih as tarih','senet_vadeleri.tutar as tutar')->where('senetler.user_id',$musteriid)->where('senet_vadeleri.vade_tarih','<=',date('Y-m-d'))->where('odendi',false)->get();

        $taksit_gelen_vadeler = TaksitVadeleri::join('taksitli_tahsilatlar','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->select('taksit_vadeleri.id as taksit_vade_id','taksit_vadeleri.vade_tarih as tarih','taksit_vadeleri.tutar as tutar')->where('taksitli_tahsilatlar.user_id',$musteriid)->where('taksit_vadeleri.vade_tarih','<=',date('Y-m-d'))->where('odendi',false)->get();*/

        return view('isletmeadmin.yenitahsilat',['isletme'=>$isletme,'paketler'=>$paketler,'bildirimler'=>self::bildirimgetir($request), 'sayfa_baslik'=>'



            Yeni Tahsilat','pageindex' => 11111 ,'request'=>$request, 'kalan_uyelik_suresi' => self::lisans_sure_kontrol($request),'portfoy_drop'=>self::musteriportfoydropliste($request),'urun_drop'=>self::urundropliste($request),'hizmet_drop'=>self::hizmetdropliste($request),

            'personel_drop'=>self::personeldropliste($request,array()),'yetkiliolunanisletmeler'=>$isletmeler]);

    }

    public function tahsilatbilgigetir(Request $request)

    {

        $indirim = 0;

        $isletme = Salonlar::where('id',$request->sube)->first();

        if(Adisyonlar::where('user_id',$request->musteriid)->where('salon_id',$isletme->id)->count()>3 

                              && 

                           date('Y-m-d H:i:s', strtotime('+90 days',strtotime(Adisyonlar::where('user_id',$request->musteriid)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))

            $indirim = $isletme->sadik_musteri_indirim_yuzde;

        elseif(Adisyonlar::where('user_id',$request->musteriid)->where('salon_id',$isletme->id)->count()==0)

            $indirim = $isletme->pasif_musteri_indirim_yuzde;

        else

            $indirim = $isletme->aktif_musteri_indirim_yuzde;

        $senet_gelen_vadeler = SenetVadeleri::join('senetler','senet_vadeleri.senet_id','=','senetler.id')->select('senet_vadeleri.id as senet_vade_id','senet_vadeleri.vade_tarih as tarih','senet_vadeleri.tutar as tutar')->where('senetler.user_id',$request->musteriid)->where('senet_vadeleri.vade_tarih','<=',date('Y-m-d'))->where('senetler.salon_id',$request->sube)->where('odendi',false)->get();

        

        $taksit_gelen_vadeler = TaksitVadeleri::join('taksitli_tahsilatlar','taksit_vadeleri.taksitli_tahsilat_id','=','taksitli_tahsilatlar.id')->select('taksit_vadeleri.id as taksit_vade_id','taksit_vadeleri.vade_tarih as tarih','taksit_vadeleri.tutar as tutar')->where('taksitli_tahsilatlar.user_id',$request->musteriid)->where('taksitli_tahsilatlar.salon_id',$request->sube)->where('taksit_vadeleri.vade_tarih','<=',date('Y-m-d'))->where('odendi',false)->get();

        $taksitler = '';

        $senetler = '';

        foreach($taksit_gelen_vadeler as $taksit_gelen_vade)

        {

            $taksitler .= ' <div class="row tahsilat_kalemleri_listesi taksit_vadeleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="'.$taksit_gelen_vade->taksit_vade_id.'">

                         <div class="col-md-4 col-5 col-xs-5  col-sm-4">Taksit Vadesi</div>

                         <div class="col-md-3 col-7 col-xs-7  col-sm-3">'.date('d.m.Y', strtotime($taksit_gelen_vade->tarih)).'</div>

                         <div class="col-md-2 col-5 col-xs-5  col-sm-2">1 adet</div>

                         <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">

                           <input type="hidden" name="taksit_vade_id[]" value="'.$taksit_gelen_vade->taksit_vade_id.'">

                           <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="taksit_tahsilat_tutari_girilen[]" value="'.number_format($taksit_gelen_vade->tutar,2,',','.').'" style="text-align: right;">

                              <p style="position: relative; float: left; width: 70%;">'.number_format($taksit_gelen_vade->tutar,2,',','.').'</p>

                              <div class="dropdown" style="width: 15%;float:left">

                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown"><i class="dw dw-more"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"> <a class="dropdown-item tahsilat_taksit_sil" data-value="'.$taksit_gelen_vade->taksit_vade_id.'" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div> </div></div>



                      </div>';

        }

                     

        foreach($senet_gelen_vadeler as $senet_gelen_vade){

            $senetler .= '<div class="row tahsilat_kalemleri_listesi senet_vadeleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="'.$senet_gelen_vade->senet_vade_id.'">

                         <div class="col-md-4 col-5 col-xs-5  col-sm-4">Senet Vadesi</div>

                         <div class="col-md-3 col-7 col-xs-7  col-sm-3">'.date('d.m.Y', strtotime($senet_gelen_vade->tarih)).'</div>

                         <div class="col-md-2 col-5 col-xs-5  col-sm-2">1 adet</div>

                         <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">

                           <input type="hidden" name="taksit_vade_id[]" value="'.$senet_gelen_vade->senet_vade_id.'">

                           <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="senet_tahsilat_tutari_girilen[]" value="'.number_format($senet_gelen_vade->tutar,2,',','.').'" style="text-align: right;">

                              <p style="position: relative; float: left; width: 70%;">'.number_format($senet_gelen_vade->tutar,2,',','.').'</p>

                              <div class="dropdown" style="width: 15%;float:left">

                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown"><i class="dw dw-more"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"> <a class="dropdown-item tahsilat_senet_sil" data-value="'.$senet_gelen_vade->senet_vade_id.'" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div> </div></div>



                      </div>';

         }

        $tum_senetler = self::senetvadegetir_tahsilat($request);

        $tum_taksitler = self::taksitvadegetir_tahsilat($request);   

        $tahsilatlar = Tahsilatlar::where('user_id',$request->musteriid)->get();

        $acik_adisyonlar = self::adisyon_yukle($request,'','','1970-01-01',date('Y-m-d'),$request->musteriid,'');    

        $tahsilat_liste = '';

        $tahsilatlar = Tahsilatlar::where('user_id',$request->musteriid)->where('salon_id',$request->sube)->get();

        $odeme_akisi = '<tr>

                              <td colspan="4" style="border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;">Ödeme Akışı</td>

                           </tr>';

        foreach($tahsilatlar as $key=>$tahsilat)

            $odeme_akisi .= '<tr>

                             

                              <td>'.date('d.m.Y',strtotime($tahsilat->odeme_tarihi)).'</td>

                              <td>'.number_format($tahsilat->tutar,2,',','.').'</td>

                              <td>

                                 '.$tahsilat->odeme_yontemi->odeme_yontemi.'

                              </td>

                              <td>

                                 <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" name="tahsilat_adisyondan_sil" data-value="'.$tahsilat->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                              </td>

                           </tr>';

                           

        foreach(Adisyonlar::whereIn('id',$acik_adisyonlar->pluck('id'))->get() as $adisyon)

        {

            foreach($adisyon->hizmetler as $key=>$hizmet)

            {

                if(($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari > 0 || $hizmet->hediye) &&  $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null)

                {

                    $tahsilat_liste .=' <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="0">



                              <div class="col-md-4 col-5 col-xs-5  col-sm-4">

                                  

                                       '.$hizmet->hizmet->hizmet_adi.'

                              </div><div class="col-md-3 col-7 col-xs-7  col-sm-3">';

                    if($hizmet->personel_id !== null)

                                    $tahsilat_liste .= $hizmet->personel->personel_adi;

                    else

                                    $tahsilat_liste .= $hizmet->cihaz->cihaz_adi;

                    $tahsilat_liste .= '</div>

                              <div class="col-md-2 col-5 col-xs-5  col-sm-2">

                                   

                                  1 adet

                              </div><div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">

                                   <input type="hidden" name="adisyon_hizmet_id[]" value="'.$hizmet->id.'"> 

                                   <input type="hidden" name="indirim[]" data-value="'.$hizmet->id.'" value="'.$hizmet->indirim_tutari.'">

                                   <input type="hidden" name="adisyon_hizmet_senet_id[]" value="'.$hizmet->senet_id.'">

                                   <input type="hidden" name="adisyon_hizmet_taksitli_tahsilat_id[]" value="'.$hizmet->taksitli_tahsilat_id.'">';

                    if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)

                        $tahsilat_liste .= '<input type="tel" class="form-control try-currency tahsilat_kalemleri" style="height:26px;width: 70%;float:left;" name="himzet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.number_format($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')  - $hizmet->indirim_tutari,2,',','.').'" >' ;

                    else{

                        $tahsilat_liste .= '<input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="himzet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.number_format($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari ,2,',','.').'" >

                                      <p style="position: relative; float: left; width: 70%;">'.number_format($hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,2,',','.').' ₺</p>';    

                        if($hizmet->hediye)   

                            $tahsilat_liste .= '<i class="fa fa-gift"></i>';  

                    }

                    $tahsilat_liste .= '<p style="position: relative; float: left;width: 15%;margin: 0;">';

                    if($hizmet->hediye)

                        $tahsilat_liste .= '<i class="fa fa-gift" style="font-size: 25px"></i>';

                    else

                        $tahsilat_liste .=  '<i class="fa fa-gift" style="visibility: hidden"></i>';

                    $tahsilat_liste .= '</p>

                                    <div class="dropdown" style="width: 15%;float:left">

                                       <a

                                          class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"

                                          href="#"

                                          role="button"

                                          data-toggle="dropdown"

                                       >

                                          <i class="dw dw-more"></i>

                                       </a>

                                       <div

                                          class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"

                                       >

                                          <a class="dropdown-item tahsilat_hizmet_bilgi" data-value="'.$hizmet->id.'" href="#"

                                             ><i class="dw dw-eye"></i> Bilgi</a

                                          >';

                    if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)

                    {

                        if(!$hizmet->hediye)

                            $tahsilat_liste .= '<a class="dropdown-item tahsilat_hizmet_hediye_ver" data-value="'.$hizmet->id.'" href="#"

                                             ><i class="fa fa-gift"></i> Hediye Ver</a

                                          >';

                        else

                            $tahsilat_liste .= ' <a class="dropdown-item tahsilat_hizmet_hediye_kaldir" data-value="'.$hizmet->id.'" href="#"

                                             ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a

                                          >';

                        $tahsilat_liste .= '<a class="dropdown-item tahsilat_hizmet_sil" data-value="'.$hizmet->id.'" href="#"

                                             ><i class="dw dw-delete-3"></i> Sil</a

                                          >';

                    }

                    $tahsilat_liste .= '  </div>

                                    </div>

                                 </div>

                              </div>';



                }

                

            }

            foreach($adisyon->urunler as $key=>$urun){

                if(($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari > 0 || $urun->hediye) &&  $urun->senet_id === null && $urun->taksitli_tahsilat_id===null )

                {

                    $tahsilat_liste .= '<div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="0">



                              <div class="col-md-4 col-5 col-xs-5 col-sm-4">

                                  '.$urun->urun->urun_adi.' 

                                 </div>

                                  

                                 

                                

                                 

                               

                              <div class="col-md-3  col-7 col-xs-7  col-sm-3">

                                  '.$urun->personel->personel_adi.'



                              </div>

                              <div class="col-md-2 col-5 col-xs-5  col-sm-2">';

                    if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)

                        $tahsilat_liste .= '<input type="tel" value="'.$urun->adet.'" data-value="'.$urun->id.'" class="form-control" style="height:26px;float:left;width: 60%;" name="urun_adet_girilen[]"> <span style="float:left;position:relative;">adet</span> ';

                    else

                        $tahsilat_liste .= $urun->adet .' adet';

                    $tahsilat_liste .= '</div> 

                              

                              <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">

                                 <input type="hidden" name="adisyon_urun_id[]" value="'.$urun->id.'"> 

                                 <input type="hidden" name="indirim[]" data-value="'.$urun->id.'" value="'.$urun->indirim_tutari.'">

                                 <input type="hidden" name="adisyon_urun_senet_id[]" value="'.$urun->senet_id.'">

                                 <input type="hidden" name="adisyon_urun_taksitli_tahsilat_id[]" value="'.$urun->taksitli_tahsilat_id.'">';

                    if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)

                        $tahsilat_liste .= '<input type="tel" class="form-control try-currency tahsilat_kalemleri" style="height:26px;width: 70%;float:left" name="urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.').'" >';

                    else

                    {

                         if($urun->senet_id == null || $urun->taksitli_tahsilat_id == null)

                            $tahsilat_liste .= '<input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.').'" >';

                        else

                            $tahsilat_liste .= '<input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.number_format(0,2,',','.').'" >';

                                

                        $tahsilat_liste .= '<p style="position: relative; float: left; width: 70%;">'.number_format($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.').' ₺</p>';

                    }

                    $tahsilat_liste .= '<p style="position: relative; float: left;width: 15%;margin: 0;">';

                    if($urun->hediye)

                        $tahsilat_liste .= '<i class="fa fa-gift" style="font-size: 25px"></i>';

                    else

                        $tahsilat_liste .= '<i class="fa fa-gift" style="visibility: hidden"></i>';

                    $tahsilat_liste .= '</p>

                                     <div class="dropdown" style="width: 15%;float:left">

                                       <a

                                          class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"

                                          href="#"

                                          role="button"

                                          data-toggle="dropdown"

                                       >

                                          <i class="dw dw-more"></i>

                                       </a>

                                       <div

                                          class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"

                                       >

                                         ';

                    if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)

                    {

                        if(!$urun->hediye)

                            $tahsilat_liste .= '<a class="dropdown-item tahsilat_urun_hediye_ver" data-value="'.$urun->id.'" href="#"

                                             ><i class="fa fa-gift"></i> Hediye Ver</a

                                          >';

                        else

                            $tahsilat_liste .= ' <a class="dropdown-item tahsilat_urun_hediye_kaldir" data-value="'.$urun->id.'" href="#"

                                             ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a

                                          >';

                        $tahsilat_liste .= '<a class="dropdown-item tahsilat_urun_sil" href="#" data-value="'.$urun->id.'"

                                             ><i class="dw dw-delete-3"></i> Sil</a

                                          >';

                    }

                     $tahsilat_liste .= '   </div>

                                    </div>

                                    

                                 

                              </div>



                                 

                           </div>';

                }

               

            }

            foreach($adisyon->paketler as $key=>$paket){

                if(($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari > 0 || $paket->hediye) &&  $paket->senet_id === null && $paket->taksitli_tahsilat_id === null   )

                {

                    $tahsilat_liste .= '<div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">



                              <div class="col-md-4 col-5 col-xs-5  col-sm-4">

                                 '.$paket->paket->paket_adi.'

                              </div>

                               

                                 

                                 

                              

                              <div class="col-md-3  col-7 col-xs-7  col-sm-3">

                                  '.$paket->personel->personel_adi.'



                              </div>

                               <div class="col-md-2 col-5 col-xs-5  col-sm-2">

                                  1 adet

                              </div>

                              <div class="col-md-3 col-7 col-xs-7  col-sm-3"  style="text-align:right">

                                 <input type="hidden" name="adisyon_paket_id[]" value="'.$paket->id.'"> 

                                 <input type="hidden" name="adisyon_paket_senet_id[]" value="'.$paket->senet_id.'">

                                 <input type="hidden" name="adisyon_paket_taksitli_tahsilat_id[]" value="'.$paket->taksitli_tahsilat_id.'">

                                 <input type="hidden" name="indirim[]" data-value="'.$paket->id.'" value="'.$paket->indirim_tutari.'">';

                    if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0) 

                        $tahsilat_liste .= '<input type="tel"  style="height: 26px;width: 70%;float:left" class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.').'">'; 

                    else

                        $tahsilat_liste .= ' <input type="hidden"  class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.number_format($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.').'">

                                  <p style="position: relative; float: left; width: 70%;">'.number_format($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.').'₺ </p>';

                    $tahsilat_liste .= '<p style="position: relative; float: left;width: 15%; margin:0">';

                    if($paket->hediye)

                        $tahsilat_liste .= '<i class="fa fa-gift" style="font-size: 25px"></i>';

                    else

                        $tahsilat_liste .= '<i class="fa fa-gift" style="visibility: hidden"></i>';

                    $tahsilat_liste .= '</p>

                                  <div class="dropdown"  style="width: 15%;float:left">

                                       <a

                                          class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"

                                          href="#"

                                          role="button"

                                          data-toggle="dropdown"

                                       >

                                          <i class="dw dw-more"></i>

                                       </a>

                                       <div

                                          class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"

                                       >



                                          <a class="dropdown-item tahsilat_paket_bilgi" data-value="'.$paket->id.'" href="#"

                                             ><i class="dw dw-eye"></i> Bilgi</a

                                          >';

                    if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)

                    {

                        if(!$paket->hediye)

                            $tahsilat_liste .= '<a class="dropdown-item tahsilat_paket_hediye_ver" data-value="'.$paket->id.'" href="#"

                                             ><i class="fa fa-gift"></i> Hediye Ver</a

                                          >';

                        else

                            $tahsilat_liste .= '<a class="dropdown-item tahsilat_paket_hediye_kaldir" data-value="'.$paket->id.'" href="#"

                                             ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a

                                          >';

                        $tahsilat_liste .= '<a class="dropdown-item tahsilat_paket_sil" data-value="'.$paket->id.'" href="#"

                                             ><i class="dw dw-delete-3"></i> Sil</a

                                          >';

                    }

                    $tahsilat_liste .= '  </div>

                                    </div>

                                 

                              </div>

                              

                            

                           </div>';

                }

            }

        }                 

                      

        return array(

            'indirim' => $indirim,

            'taksitler_senetler' => $taksitler.$senetler,

            'tum_senetler' => $tum_senetler,

            'tum_taksitler' => $tum_taksitler,

            'tahsilat_liste' => $tahsilat_liste,

            'odeme_akisi' => $odeme_akisi,

            'musteribilgi' => DB::table('users')->select('id as id','name as text')->where('id',$request->musteriid)->get(),

        );

    }

   

 

    public function musteriportfoydropliste(Request $request)

    {

        $portfoy = '';

        

        foreach(MusteriPortfoy::where('salon_id',self::mevcutsube($request))->where('aktif',true)->get() as $mevcutmusteri)

        {

            $portfoy .= '<option value="'.$mevcutmusteri->user_id.'">'.$mevcutmusteri->users->name.'</option>';

        }

        return $portfoy;



    }

    public function urundropliste(Request $request)

    {

        $urunliste = '';

        foreach(Urunler::where('salon_id',self::mevcutsube($request))->where('aktif',true)->get() as $urun)

            $urunliste .='<option value="'.$urun->id.'">'.$urun->urun_adi.'</option>';

        return $urunliste;

    }  

    public function hizmetdropliste(Request $request)

    {

        $hizmetdropliste = '';

        foreach(SalonHizmetler::where('salon_id',self::mevcutsube($request))->where('aktif',true)->get() as $hizmetliste)

            $hizmetdropliste .= '<option value="'.$hizmetliste->hizmet_id.'">'.$hizmetliste->hizmetler->hizmet_adi.'</option>';

        return $hizmetdropliste;                              

    } 

    public function personeldropliste(Request $request,$personelid)

    {

        $personeldropliste = '';

        if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',self::mevcutsube($request))->count() > 0)

        {

            $personeldropliste .= '<option selected value="'.Personeller::where('salon_id',self::mevcutsube($request))->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id').'">'.Auth::guard('isletmeyonetim')->user()->name.'</option>';

        }

        else{

            foreach(Personeller::where('salon_id',self::mevcutsube($request))->where('aktif',true)->get() as $personel)

            {

                if(count($personelid) > 0 && in_array($personel->id,$personelid))

                    $personeldropliste .= '<option selected value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

                else

                    $personeldropliste .= '<option value="'.$personel->id.'">'.$personel->personel_adi.'</option>';

            }

        }

         

        return $personeldropliste;  

    } 

    public function urunfiyatdegistir(Request $request){

        $totalPrice = 0;

         $returntext = "İşlem Başarılı"; // Set a default return text

      

         if ($request->has('urun_bilgi')) {

        foreach($request->urun_bilgi as $urun_id){

            $urun = Urunler::where('id',$urun_id)->first();

            $returntext="İşlem Başarılı";

            // Increase the price of the product by the entered value

               $newPrice = $urun->fiyat+ ($urun->fiyat *($request->urun_oran / 100));

            // Update the product's price

            $urun->fiyat = $newPrice;// Assuming fiyat is the field representing the price

            $urun->save();

        }

    } else {

        // Handle case where urun_bilgi is not present in the request

        $returntext = "Ürün bilgileri eksik.";

    }

    

        return json_encode(self::urun_liste_getir($request,$returntext));



    }

    public function urunfiyatindirimdegistir(Request $request){

        $totalPrice = 0;

         $returntext = "İşlem Başarılı"; // Set a default return text

      

         if ($request->has('urun_bilgi')) {

        foreach($request->urun_bilgi as $urun_id){

            $urun = Urunler::where('id',$urun_id)->first();

            $returntext="İşlem Başarılı";

            // Increase the price of the product by the entered value

               $newPrice = $urun->fiyat- ($urun->fiyat *($request->urun_oran / 100));

            // Update the product's price

            $urun->fiyat = $newPrice;// Assuming fiyat is the field representing the price

            $urun->save();

        }

    } else {

        // Handle case where urun_bilgi is not present in the request

        $returntext = "Ürün bilgileri eksik.";

    }

    

        return self::urun_liste_getir($request,$returntext);



    }

    public function paracekmeonaykodugonder(Request $request){

      



        $user = IsletmeYetkilileri::where('id',Auth::guard('isletmeyonetim')->user()->id)->first();

      

        // Fetch the yetkililer

        $yetkililer = Personeller::join('model_has_roles','salon_personelleri.yetkili_id','=','model_has_roles.model_id')

            ->where('salon_personelleri.salon_id', $user->salon_id)

            ->whereIn('role_id', [1,2,3])

            ->get(['salon_personelleri.id','salon_personelleri.cep_telefon','salon_personelleri.personel_adi']);

        $random = str_shuffle('1234567890');

        $kod = substr($random, 0, 4);      

        $user->dogrulama_kodu = $kod;

        $user->save();

      



        

        foreach ($yetkililer as $yetkili) {

            $mesajlar = array(

                array("to" => $yetkili->cep_telefon, "message" => "Kasadan ".$request->paraalma_tutari." TL çekilmesi için onay kodu: ".$kod)

            );



           

        }

         self::sms_gonder($request, $mesajlar, false, 1, true);

        



        return ''; // Moved outside of the foreach loop

   

    }

   

   

    public function kasaya_para_ekle(Request $request){

        $paraekle=new Tahsilatlar();

        $paraekle->olusturan_id=$request->paraekleyen;

        $paraekle->salon_id = $request->sube;

        $paraekle->odeme_tarihi=$request->parakoyma_tarihi;

        $paraekle->tutar=str_replace('.','',$request->para_tutari);

        $paraekle->odeme_yontemi_id=$request->para_odeme_yontemi;

        $paraekle->notlar=$request->para_aciklama;

        $paraekle->save();

        $yetkililer=Personeller::join('model_has_roles','salon_personelleri.yetkili_id','=','model_has_roles.model_id')->where('salon_personelleri.salon_id',$paraekle->salon_id)->whereIn('role_id',[1,2,3])->get(['salon_personelleri.id','salon_personelleri.cep_telefon','salon_personelleri.personel_adi']);

        if(SalonSMSAyarlari::where('ayar_id',20)->where('salon_id',$paraekle->salon_id)->value('personel')){

            foreach ($yetkililer as $yetkili) {

                 $mesajlar = array(

                array("to"=>$yetkili->cep_telefon,"message"=>"Kasanıza ".$paraekle->olusturan->personel_adi. " tarafından ".$paraekle->tutar.' TL eklenmiştir.',

                

            



            ));

            

            }

           self::sms_gonder($request,$mesajlar,false,1,false);

        }

        return self::kasa_raporu_getir($request,'');



    }

    public function kasadanparaal(Request $request){

        

        $returntext='';

         

        $user = IsletmeYetkilileri::where('id',Auth::guard('isletmeyonetim')->user()->id)->first();



       if ($request->onaykoduparacekme == $user->dogrulama_kodu) {

            $paraal=new Masraflar();

            $paraal->harcayan_id=$request->paraalan;

            $paraal->salon_id = $request->sube;

            $paraal->tarih=$request->paraalma_tarihi;

            $paraal->tutar=str_replace('.','',$request->paraalma_tutari);

            $paraal->odeme_yontemi_id=$request->paraalma_odeme_yontemi;

            $paraal->notlar=$request->paraalma_aciklama;

            $paraal->save();

            $returntext='İşlem Başarılı';

            $yetkililer=Personeller::join('model_has_roles','salon_personelleri.yetkili_id','=','model_has_roles.model_id')->where('salon_personelleri.salon_id',$paraal->salon_id)->whereIn('role_id',[1,2,3])->get(['salon_personelleri.id','salon_personelleri.cep_telefon','salon_personelleri.personel_adi']);

            if(SalonSMSAyarlari::where('ayar_id',19)->where('salon_id',$paraal->salon_id)->value('personel')){

                foreach ($yetkililer as $yetkili) {

                    $mesajlar = array(

                        array("to"=>$yetkili->cep_telefon,"message"=>"Kasanızdan ".$paraal->harcayan->personel_adi. " tarafından ".$paraal->tutar.' TL alınmıştır.',

                

            



                    ));

            

                }

                self::sms_gonder($request,$mesajlar,false,1,false);

          

            }

            return self::kasa_raporu_getir($request,'İşlem başarılı.');

            exit;

        

        }

        else{

            return self::kasa_raporu_getir($request,'Doğrulama kodu hatalı, lütfen tekrar deneyiniz.'); 

            exit;

        }

       

        

    }

    public function musteriprofilresimyukle(Request $request){

        $musteri = User::where('id',$request->user_id)->first(); 

       

        $folderPath = '/home/webfirma/randevumcepteweb3/public/profil_resimleri/';

        $image_parts = explode(";base64,", $_POST['profilresmi']);

        $image_type_aux = explode("image/", $image_parts[0]);

        $image_type = $image_type_aux[1];

        $image_base64 = base64_decode($image_parts[1]);

        $filename = uniqid() . '.jpg';

        $file = $folderPath . $filename;

        file_put_contents($file, $image_base64);

        $musteri->profil_resim = "/public/profil_resimleri/".$filename;

        $musteri->save(); 

        echo "Başarılı";   

         

   

    }

  public function islemsonrasiresimyukleme(Request $request){

        $resimler=new Islemler();

        $imagesPaths = array();

        $resimler->tarih=$request->resimtarih;

        if(isset($_FILES["musteriresimyukle"]["name"])){



          

             for($i=0;$i<count($_FILES["musteriresimyukle"]["name"]);$i++){

                 

                $image = $request->musteriresimyukle[$i];

                $filename = strtotime(date('H:i:s')). '-' .$_FILES["musteriresimyukle"]["name"][$i] . '.' . $image->getClientOriginalExtension();

                Image::make($image)->resize(null, 720, function ($constraint) {

                    $constraint->aspectRatio();

                })->save( public_path('/musteri_gorselleri/' . $filename) );                 

                array_push($imagesPaths,"public/musteri_gorselleri/".$filename);

                //Image::make($image)->resize(1280, 720)->save( public_path('/musteri_resimleri/' . $filename) )->move(public_path() . '/musteri_resimleri', md5($filename) . ".jpg");



                 





                 /*$dosya = $request->musteriresimyukle[$i];

                 $kaynak = $_FILES["musteriresimyukle"]["tmp_name"][$i];

                 $dosya  = str_replace(" ", "_", $_FILES["musteriresimyukle"]["name"][$i]);

                 $dosya  = str_replace(" ", "-", $_FILES["musteriresimyukle"]["name"][$i]);

                 $uzanti = explode(".", $_FILES["musteriresimyukle"]["name"][$i]);

                 $hedef  = "./" . $dosya;

                 if (@$uzanti[1]) {

                            if (!file_exists($hedef)) {

                                $hedef   =  "public/musteri_gorselleri/".$dosya;

                                $dosya   = $dosya;

                            }

                            move_uploaded_file($kaynak, $hedef);

                 }  

                 array_push($imagesPaths,$hedef);*/

                

             }

               

        }

        $resimler->islem_fotolari = json_encode($imagesPaths);

        $resimler->user_id = $request->user_id;

        $resimler->save(); 

        return $resimler->id; 

         

    }

    public function islemsonrasinotekleme(Request $request){

         $randevu = Randevular::where('id',$request->randevu_id)->first();

         $randevu->randevu_sonrasi_not=$request->islemsonrasinot;

         $randevu->save();

        return self::randevu_liste_getir($request,'','','','','','',self::mevcutsube($request),''); 



    }

   public function islemdetayigetir(Request $request){

        $islem=Islemler::where('id',$request->islem_id)->first();

        $images=json_decode($islem->islem_fotolari,true);

      $islem_gorselleri='';

           foreach($images as $key => $image){

            $islem_gorselleri .= '<li class="col-lg-3 col-md-3 col-sm-12" style="list-style: none; float:left;">

                                    <div class="da-card box-shadow">

                                       <div class="da-card-photo " style=" height: 180px;">

                                          <img  id="gorsel'.$key.'" src="/'.$image.'" alt="" />

                                          <div class="da-overlay">

                                             <div class="da-social">

                                                <h5 class="mb-10 color-white pd-20">

                                                  

                                                </h5>

                                                <ul class="clearfix">

                                                   <li>

                                                      <a

                                                         href="/'.$image.'"

                                                         data-fancybox="images"

                                                         ><i class="fa fa-picture-o"></i

                                                      ></a>

                                                   </li>

                                                  

                                                </ul>

                                             </div>

                                          </div>

                                       </div>

                                    </div>

                                 </li>';

        }

    return $islem_gorselleri;

    }

    public function odeme_bildirimi(Request $request)

    {

      

        

    }

    public function uyelikiletisimvefaturabilgiguncelle(Request $request)

    {

        $user = IsletmeYetkilileri::where('id',Auth::guard('isletmeyonetim')->user()->id)->first();

        $user->name = $request->adsoyad;

        $user->email = $request->email; 

        $user->gsm1 = $request->telefon;

        $user->tc_kimlik_no = $request->tc_kimlik_no;    

        $user->save();

        $isletme = Salonlar::where('id',$request->sube)->first();

        $isletme->vergi_adi = $request->vergi_adi;

        $isletme->vergi_adresi = $request->adres;

        $isletme->adres = $request->adres;

        $isletme->vergi_no = $request->vergi_no;

        $isletme->kdv_orani = $request->kdv_orani;

        $isletme->save();

        return 'Başarılı';

    }



        

}

