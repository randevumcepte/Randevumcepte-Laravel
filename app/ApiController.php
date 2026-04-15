<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\Iller;
use App\Ilceler;
use App\Ulkeler;
use App\Salonlar;
use App\SalonGorselleri;
use App\Personeller;
use App\OdaRenkleri;
use App\SalonCalismaSaatleri;
use App\SalonMolaSaatleri;
use App\SalonPuanlar;
use App\SalonYorumlar;
use App\PersonelPuanlar;
use App\PersonelYorumlar;
use App\PersonelHizmetler;
use App\SalonHizmetler;
use App\Randevular;
use App\RandevuHizmetler;
use App\Subeler;
use App\Urunler;
use App\Paketler;
 use App\Tahsilatlar;
 use App\TahsilatHizmetler;
 use App\TahsilatUrunler;
use App\TaksitVadeleri;
use App\SalonCihazRenkleri;
 use App\TahsilatPaketler;
use App\IsletmeYetkilileri;
use App\SalonTuru;
use App\OnGorusmeler;
use App\Masraflar;
use App\AdisyonUrunler;
use App\Adisyonlar;
use App\SalonSMSAyarlari;
 use App\AdisyonHizmetler;
use App\PaketHizmetler;
use App\KampanyaKatilimcilari;
use App\AdisyonPaketler;
use App\KampanyaYonetimi;
use App\EtkinlikKatilimcilari;
use App\Etkinlikler;
use App\Ajanda;
use App\Arsiv;
use App\AdisyonPaketSeanslar;
use App\SabitNumaralar;
use App\Bildirimler;
use App\MusteriPortfoy;
use App\SMSTaslaklari;
use App\SMSIletimRaporlari;
use App\FormTaslaklari;
use Illuminate\Support\Facades\DB;
use App\Senetler;
use App\SenetVadeleri;
use App\MasrafKategorisi;
use App\Cihazlar;
use App\Odalar;
use App\PersonelCalismaSaatleri;
use App\PersonelMolaSaatleri;
use App\CihazCalismaSaatleri;
use App\CihazMolaSaatleri;
use App\BildirimKimlikleri;
use App\TaksitliTahsilatlar;
use App\Alacaklar;
use App\SalonHizmetKategoriRenkleri;
 use Hash;
class ApiController extends Controller
{
    public function yenimusteridanisankaydi(Request $request)
    {
        $salonidler = array();
        if(str_contains($request->salonidler,','))
        {
           $salonidler = explode(",",$request->salonidler);
        }
        else
            array_push($salonidler,$request->salonidler);
        if(MusteriPortfoy::whereHas('users',function($q) use($request){$q->where('cep_telefon',$request->cep_telefon);})->whereIn('salon_id',$salonidler)->count() >= 1){
            return "exists";
            exit();
        }
        else{
            $kullanici = new User();
            $kullanici->name = $request->name;
            $kullanici->cep_telefon = $request->cep_telefon;
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 5);
            $kullanici->password = Hash::make($olusturulansifre);
            $kullanici->save();
            $salonbaslik = '';
            foreach($salonidler as $salonid)
            {
                $portfoy = new MusteriPortfoy();
                $portfoy->user_id = $kullanici->id;
                $portfoy->salon_id = $salonid; 
                $portfoy->save();
                
                 
            }
            $kullanici = User::where('cep_telefon',$request->cep_telefon)->first();
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 5);
            $kullanici->password = Hash::make($olusturulansifre);
            $kullanici->save();
               $headers = array(
                 'Authorization: Key '.$request->sms_apikey,
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $postData = json_encode( array( "originator"=> $request->sms_baslik, "messages"=>array(array("to"=>$request->cep_telefon,"message"=> $request->isletmeadi." uygulama şifreniz  : ".$olusturulansifre)),"encoding"=>"auto") );
 
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
            $response=curl_exec($ch);
            exit();
        }
    }
    public function sifregonder(Request $request)
    {
        $kullanicivar = false;
        if(User::where('cep_telefon',$request->cep_telefon)->count() >= 1)
        {
            $kullanicivar = true;
            $kullanici = User::where('cep_telefon',$request->cep_telefon)->first();
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 5);
            $kullanici->password = Hash::make($olusturulansifre);
            $kullanici->save();
               $headers = array(
                 'Authorization: Key '.$request->sms_apikey,
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $postData = json_encode( array( "originator"=> $request->sms_baslik, "messages"=>array(array("to"=>$request->cep_telefon,"message"=> $request->isletmeadi." uygulama şifreniz  : ".$olusturulansifre)),"encoding"=>"auto") );
 
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
            $response=curl_exec($ch);
            
            
        }
        if(IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->count() >= 1)
        {
            $kullanicivar = true;
            $kullanici = IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->first();
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 5);
            $kullanici->password = Hash::make($olusturulansifre);
            $kullanici->save();
               $headers = array(
                 'Authorization: Key '.$request->sms_apikey,
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $postData = json_encode( array( "originator"=> $request->sms_baslik, "messages"=>array(array("to"=>$request->cep_telefon,"message"=> $request->isletmeadi." uygulama şifreniz  : ".$olusturulansifre)),"encoding"=>"auto") );
 
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
            $response=curl_exec($ch);
             
        }
        if(!$kullanicivar){
            return "error";
            exit();
        }
        else{
            return "success";
            exit();
        }
      
    }
    public function kullaniciBilgiGetir(Request $request,$id)
    {
        if(IsletmeYetkilileri::where('id',$id)->first())
        {
            return IsletmeYetkilileri::where('id',$id)->first();
            exit();
        }
        else{
            return User::where('id',$id)->first();
            exit();
        }
           
        
    }
    public function salonhizmetler(Request $request){
        
        $salonsunulanhizmetler = SalonHizmetler::where('salon_id' ,$isletme_id)->get();
        
        
    }
    public function subeler(Request $request,$isletme_id){
        $subeler = Subeler::where('salon_id',$isletme_id)->get();
        return $subeler;
        
        
    }
   public function adisyon_yukle(Request $request,$adisyonturu,$adisyondurumu,$tarih1,$tarih2,$musteriid,$personelid,$isletme_id)
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
                'p1.personel_adi as hizmet_veren',
                'p2.personel_adi as urun_satan',
                'p3.personel_adi as paket_satan',  
                 DB::raw("CONCAT('') as planlanan_alacak_tarihi"),
                /* DB::raw('CASE WHEN ((SELECT COUNT() FROM senet_vadeleri INNER JOIN senetler on senet_vadeleri.senet_id = senetler.id  where senetler.user_id = users.id and senet_vadeleri.odendi is not true and senet_vadeleri.vade_tarih < NOW()) + (SELECT COUNT() FROM taksit_vadeleri INNER JOIN taksitli_tahsilatlar on taksit_vadeleri.taksitli_tahsilat_id = taksitli_tahsilatlar.id  where taksitli_tahsilatlar.user_id = users.id  and taksit_vadeleri.odendi is not true and taksit_vadeleri.vade_tarih < NOW())) > 0 THEN  
                     
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
               
                DB::raw('CONCAT(FORMAT(
                    ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar),0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id)*(COALESCE(p1.hizmet_prim_yuzde,0)/100)) + 
                    ((SELECT COALESCE(SUM(tahsilat_urunler.tutar),0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id)*(COALESCE(p2.urun_prim_yuzde,0)/100)) + 
                     ((SELECT COALESCE(SUM(tahsilat_paketler.tutar),0) FROM tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)*(COALESCE(p3.paket_prim_yuzde,0)/100)),2,"tr_TR"))  as hakedis'),
                 
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
                        
                        DB::raw('CASE WHEN adisyonlar.tarih IS NOT NULL THEN DATE_FORMAT(adisyonlar.tarih, "%d.%m.%Y") ELSE DATE_FORMAT(adisyonlar.created_at, "%d.%m.%Y") END AS acilis_tarihi'),
                        //DB::raw('CONCAT("<p style=\"display:none\">",DATE_FORMAT(adisyonlar.created_at, "%Y%m%d"), "</p>",DATE_FORMAT(adisyonlar.created_at, "%d.%m.%Y")) as acilis_tarihi'),
                       'users.name as musteri',
                        'users.id as user_id',
                        DB::raw('
                            CONCAT(CASE WHEN COUNT(adisyon_hizmetler.id) > 0 THEN "Hizmet" ELSE "" END," ",CASE WHEN COUNT(adisyon_paketler.id) > 0 THEN "Paket" ELSE "" END," ",CASE WHEN COUNT(adisyon_urunler.id) > 0 THEN "Ürün" ELSE "" END) as 
                             satis_turu'),
                        DB::raw('CONCAT(( SELECT COALESCE(GROUP_CONCAT(hizmetler.hizmet_adi),"") from hizmetler where adisyon_hizmetler.hizmet_id=hizmetler.id)," ",(SELECT COALESCE(GROUP_CONCAT(paketler.paket_adi),"") from paketler where adisyon_paketler.paket_id = paketler.id)," ",(SELECT COALESCE(GROUP_CONCAT(urunler.urun_adi),"") FROM urunler where adisyon_urunler.urun_id = urunler.id)) as icerik'),
                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) as  indirim'),
                        
                        DB::raw('CONCAT(FORMAT((SELECT COALESCE(SUM(adisyon_hizmetler.fiyat),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)),2,"tr_TR") )  as toplam'),
                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.fiyat),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) - (SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) as hizmet_toplam_numeric'),
                        
                        DB::raw('(SELECT COALESCE(SUM(adisyon_urunler.fiyat),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) - (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) as urun_toplam_numeric'),
                        
                        DB::raw('(SELECT COALESCE(SUM(adisyon_paketler.fiyat),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) as paket_toplam_numeric'),
                        
                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.fiyat),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)) as toplam_numeric'),
                        DB::raw('CONCAT(FORMAT((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id),2,"tr_TR"))  as odenen'),
                        
                        DB::raw(' (SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)
                            as odenen_numeric'),
                        DB::raw('CONCAT(FORMAT((SELECT COALESCE(SUM(adisyon_hizmetler.fiyat), 0) FROM adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat), 0) FROM adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat), 0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id))  - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)) ,2,"tr_TR"))  as kalan_tutar'), 
                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.fiyat), 0) FROM adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_urunler.fiyat), 0) FROM adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id ) + (SELECT COALESCE(SUM(adisyon_paketler.fiyat), 0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) - ((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id))  - ((SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id)) as kalan_tutar_numeric'), 
                        DB::raw('(SELECT COALESCE(SUM(adisyon_hizmetler.indirim_tutari),0) from adisyon_hizmetler where adisyon_hizmetler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_urunler.indirim_tutari),0) from adisyon_urunler where adisyon_urunler.adisyon_id = adisyonlar.id) + (SELECT COALESCE(SUM(adisyon_paketler.indirim_tutari),0) FROM adisyon_paketler where adisyon_paketler.adisyon_id = adisyonlar.id) as indirim_tutari_toplam_numeric')
                    )->where('adisyonlar.salon_id',$isletme_id)->where('adisyonlar.tarih','>=',$tarih1)->where('adisyonlar.tarih','<=',$tarih2)
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
                    ->orderBy('adisyonlar.tarih','desc')->paginate(10);
       
        return $adisyonlar;
    }
    public function musteri_randevulari(Request $request,$id){
        $randevular = Randevular::with('hizmetler')->where('user_id',$id)->orderBy('id','desc')->get();
        return $randevular;
    }
    public function urunler(Request $request,$isletme_id){
        return Urunler::where('salon_id',$isletme_id)->where('urun_adi','like','%')->where('aktif',true)->orderBy('id','desc')->paginate(10);
    }
    public function urunler_liste(Request $request)
    {
        return Urunler::where('salon_id',$request->salon_id)->where('aktif',true)->orderBy('id','desc')->get();
    }
     public function paketler_liste(Request $request)
    {
        return Paketler::where('salon_id',$request->salon_id)->where('aktif',true)->orderBy('id','desc')->get();
    }
    public function randevular(Request $request,$isletme_id,$returnres){
        if($returnres==2){
            return Randevular::where('salon_id',$isletme_id)->get();
            exit();
        }
        $randevular = Randevular::where('salon_id',$isletme_id)->pluck('id');
        $salon = Salonlar::where('id',$isletme_id)->first();
        $randevu_hizmetler = "";
        $resources = "";
        
         $personel_idler = Personeller::where('salon_id',$isletme_id)->pluck('id');
        //if($salon->randevu_takvim_turu == 1){
        $randevu_hizmetler =   DB::table('randevu_hizmetler')
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
            ->select('randevular.id as randevu_id','randevular.user_id as userid','randevular.id as id', 
                DB::raw('CONCAT("#ffffff") as borderColor'),
                DB::raw('users.name as musteri'),
                DB::raw('CONCAT(COALESCE(randevular.durum,"na"),"-",COALESCE(randevular.randevuya_geldi,"na")) as durum'),
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
                    TRIM(CONCAT("Telefon : ",COALESCE(users.cep_telefon,""),"\nÖn Görüşme Nedeni : ",COALESCE(paketler.paket_adi,""),COALESCE(urunler.urun_adi,""),"\nGörüşmeyi Yapan : ",COALESCE(salon_personelleri.personel_adi,""),"\nZaman : ",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"\nSüre(dk) : ",COALESCE(randevu_hizmetler.sure_dk,""),"\nOluşturan : ",COALESCE(isletmeyetkilileri.name,""),"\nDurum : ",CASE WHEN on_gorusmeler.durum=1 THEN "Satış Yapıldı" WHEN on_gorusmeler.durum is null THEN "Beklemede" ELSE "Satış Yapılmadı" END,"\nPersonel Notu : ",COALESCE(on_gorusmeler.aciklama,"")))  
                    ELSE 
                     TRIM(CONCAT("Telefon : ",COALESCE(users.cep_telefon,""),"\nHizmet : ",COALESCE(hizmetler.hizmet_adi,""),"\nPersonel : ",COALESCE((select group_concat(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id where randevu_hizmetler.yardimci_personel is null and randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id group by randevu_hizmetler.randevu_id),"Belirtilmemiş"),"\nYardımcı Personel(-ler) : ",COALESCE((select group_concat(salon_personelleri.personel_adi) from salon_personelleri inner join randevu_hizmetler on randevu_hizmetler.personel_id = salon_personelleri.id where randevu_hizmetler.yardimci_personel = 1 and randevu_hizmetler.randevu_id = randevular.id and randevu_hizmetler.hizmet_id= hizmetler.id group by randevu_hizmetler.randevu_id),"Belirtilmemiş"),"\nCihaz : ",COALESCE(cihazlar.cihaz_adi,""),"\nOda : ",COALESCE(odalar.oda_adi,""),"\nZaman : ",COALESCE(randevular.tarih,"")," ",COALESCE(randevu_hizmetler.saat,""),"\nSüre(dk) : ",COALESCE(randevu_hizmetler.sure_dk,""),"\nOluşturan : ",COALESCE(isletmeyetkilileri.name,""),"\nGeldi mi? : ",CASE WHEN randevular.randevuya_geldi=1 THEN "Geldi" WHEN randevular.randevuya_geldi=0 THEN "Gelmedi" ELSE "Belirtilmemiş" END,"\nFiyat (₺) : ",COALESCE(randevu_hizmetler.fiyat,""),"\nMüşteri Notu : ",COALESCE(randevular.notlar,""),"\nPersonel Notu : ",COALESCE(randevular.personel_notu,""),"\n")) end as notes'),
                  
                   DB::raw('randevular.on_gorusme_id as ongorusmeid'),
                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),
                DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),
 
                 DB::raw('CASE WHEN randevular.user_id=2012 then "#000000" WHEN randevular.randevuya_geldi=0 then "0xFFFF0000" WHEN randevular.randevuya_geldi=1 THEN "0xFF008000" ELSE REPLACE(renk_duzenleri.renk,"#","0xFF") END as bgcolor'),
               
               'randevu_hizmetler.personel_id as resourceId',
                
              //  DB::raw('JSON_ARRAY(COALESCE(GROUP_CONCAT(CAST(yp.id AS CHAR)  ),""),COALESCE( CAST(randevu_hizmetler.personel_id AS CHAR),"" )  ) as resourceIds')
              //  DB::raw('CONCAT("[","\'",COALESCE(randevu_hizmetler.personel_id,"\'"),"\'", ","  , GROUP_CONCAT(QUOTE(COALESCE(yp.id,"" ))), "]"  ) as resourceIds')
             )->where('randevular.salon_id',$isletme_id)->where('randevular.durum','<',2)->get();
     
        $resources = DB::table('salon_personelleri')->join('isletmeyetkilileri','salon_personelleri.yetkili_id','=','isletmeyetkilileri.id')->join('renk_duzenleri','salon_personelleri.renk','=','renk_duzenleri.id')->select('salon_personelleri.id as id','salon_personelleri.personel_adi as name',DB::raw('REPLACE(renk_duzenleri.renk,"#","0xFF") as bgcolor'),'isletmeyetkilileri.profil_resim as avatar')->where('salon_personelleri.salon_id',$isletme_id)->get();
        $personeller = Personeller::where('salon_id',$isletme_id)->get();
        /*}
        else{
            $randevu_hizmetler = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('users','randevular.user_id','=','users.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->select('randevu_hizmetler.id as id','users.name as title',DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),DB::raw("(CASE WHEN randevular.durum=2 THEN '#FF0000' WHEN randevular.durum=1 THEN '#34a853' ELSE '#FF4E00' END) as color"),'hizmetler.hizmet_kategori_id as resourceId')->where('randevular.salon_id',$isletme_id)->get();
            $resources = DB::table('salon_sunulan_hizmetler')->join('hizmet_kategorisi','salon_sunulan_hizmetler.hizmet_kategori_id','=','hizmet_kategorisi.id')->select(['salon_sunulan_hizmetler.hizmet_kategori_id as id','hizmet_kategorisi.hizmet_kategorisi_adi as title','hizmet_kategorisi.renk as bgcolor'])->where('salon_sunulan_hizmetler.salon_id',$isletme_id)->groupBy('salon_sunulan_hizmetler.hizmet_kategori_id')->get();
        }*/
        return array(
            'randevular_liste' => Randevular::where('salon_id',$isletme_id)->get(),
            'randevular' => $randevu_hizmetler,
            'resources' => $resources,
            'personeller' => $personeller
        );
        /*if($returnres==1)
        {
            return $resources;
            exit();
        }
        elseif($returnres==0)
        {
            return $randevu_hizmetler;
            exit();
        }
        return array(
                'randevu' => $randevu_hizmetler,
                'resource' => $resources,
    
        );*/
    }
    public function musteriler(Request $request,$salonid){
        $musteri_idler = MusteriPortfoy::where('salon_id',$salonid)->pluck('user_id')->toArray();
        return MusteriPortfoy::join('users','users.id','=','musteri_portfoy.user_id')->select(
            "users.id",
"users.name",
"users.cep_telefon",
 
 
"users.cinsiyet",
"users.email",
"users.dogum_tarihi",
 
"users.tc_kimlik_no",
"musteri_portfoy.musteri_tipi",
"users.meslek",
"users.created_at",
"users.email",
"users.il_id")->where('musteri_portfoy.salon_id',$salonid)->get();
        /*$musteriler = DB::table('musteri_portfoy')->join('users','musteri_portfoy.user_id','=','users.id')->select('musteri_portfoy.user_id as musteri_id','users.name as musteri_adi')->where('musteri_portfoy.salon_id',$isletme_id)->orderBy('users.name','asc')->get();
        return $musteriler;*/
    }
    public function paketget(Request $request,$salonid)
    {
        return Paketler::where('salon_id',$salonid)->get();
    }
    public function musteri_detayi(Request $request,$id){
        $musteri = User::where('id',$id)->get();
        return $musteri;
    }
    public function ajandagetir(Request $request,$isletme_id,$olusturan){
        $ajanda=DB::table('ajanda')->join('salon_personelleri','ajanda.ajanda_olusturan','=','salon_personelleri.id')->select(
            'ajanda.id as id',
            'ajanda.ajanda_baslik as title',
            'ajanda.ajanda_icerik as description', 
            'ajanda.ajanda_hatirlatma_saat as ajanda_hatirlatma_saat',
             'salon_personelleri.personel_adi as ajanda_olusturan',
             'ajanda.ajanda_hatirlatma as ajanda_hatirlatma',
             'ajanda.ajanda_durum as durum',
             DB::raw('DATE_FORMAT(ajanda.ajanda_saat, "%H:%i") as saat'),
             'ajanda.ajanda_tarih as tarih',
            
              DB::raw('CONCAT(ajanda_tarih," ",DATE_FORMAT(ajanda.ajanda_saat, "%H:%i")) as start'),
            DB::raw('CONCAT(ajanda_tarih," ",DATE_ADD(ajanda.ajanda_saat, INTERVAL 30 MINUTE)) as end'),
        )->where('ajanda.salon_id',$isletme_id)->where('ajanda.ajanda_baslik','like','%'.$request->baslik.'%')->where('ajanda.ajanda_olusturan',Personeller::where('salon_id',$isletme_id)->where('yetkili_id',$olusturan)->value('id'))->orderBy('ajanda.ajanda_tarih','desc')->paginate(10);
        return $ajanda;
    }
    public function paketgetir(Request $request,$isletme_id){
        $paketler=DB::table('paketler')->join('paket_hizmetler','paketler.id','=','paket_hizmetler.paket_id')->join('hizmetler','paket_hizmetler.hizmet_id','=','hizmetler.id')->select(
            'paketler.id as id',
            'paketler.paket_adi as paket_adi', 
                  DB::raw('CONCAT( GROUP_CONCAT(hizmetler.hizmet_adi)) as hizmetler'),
                        DB::raw('CONCAT( GROUP_CONCAT(paket_hizmetler.seans)) as seanslar'),
                        DB::raw('CONCAT(COALESCE(SUM(paket_hizmetler.fiyat),0)) as fiyat'),
        )->where('aktif',true)->where('paketler.salon_id',$isletme_id)->where('paketler.paket_adi','like','%'.$request->arama.'%')->groupBy('paket_hizmetler.paket_id')->paginate(10);
        return $paketler;
        
    }
    public function paketsatisget(Request $request,$isletme_id){
        $paket_satislari=DB::table('adisyon_paketler')->join('paketler','adisyon_paketler.paket_id','=','paketler.id')->join('salon_personelleri','adisyon_paketler.personel_id','=','salon_personelleri.id')->join('adisyonlar','adisyon_paketler.adisyon_id','=','adisyonlar.id')->join('users','adisyonlar.user_id','=','users.id')->select(
             'adisyon_paketler.id as id',
            'adisyon_paketler.fiyat as fiyat',
            'paketler.paket_adi as paket_adi', 
            'salon_personelleri.personel_adi as satan',
            'users.name as musteri',
            DB::raw('DATE_FORMAT(adisyon_paketler.created_at,"%d.%m.%Y") as tarih'),
        )->where('users.name','like','%'.$request->musteridanisan.'%')->where('adisyonlar.salon_id',$isletme_id)->where('adisyon_paketler.created_at','like',"%".date('Y-m-d')."%")->paginate(10);
        return $paket_satislari;
    }
    public function urunsatisgetir(Request $request,$isletme_id){
        $urun_satislari=DB::table('adisyon_urunler')->join('urunler','adisyon_urunler.urun_id','=','urunler.id')->join('salon_personelleri','adisyon_urunler.personel_id','=','salon_personelleri.id')->join('adisyonlar','adisyon_urunler.adisyon_id','=','adisyonlar.id')->join('users','adisyonlar.user_id','=','users.id')->select(
            'adisyon_urunler.id as id',
            'adisyon_urunler.fiyat as fiyat',
            'urunler.urun_adi as urun_adi',
            'salon_personelleri.personel_adi as satan',
            'users.name as musteri'
        )->where('users.name','like','%'.$request->musteridanisan.'%')->where('adisyonlar.salon_id',$isletme_id)->where('adisyon_urunler.created_at','like',"%".date('Y-m-d')."%")->paginate(10);
        return $urun_satislari;
    }
    public function ongorusmegetir(Request $request,$isletme_id){
        $ongorusmeler = OnGorusmeler::where('ad_soyad','like','%'.$request->musteridanisan.'%')->where('salon_id',$isletme_id)->orderBy('id','desc')->paginate(10);
        /*$ongorusmeler=DB::table('on_gorusmeler')->join('salonlar','on_gorusmeler.salon_id','=','salonlar.id')
        ->leftjoin('users','on_gorusmeler.user_id','=','users.id')
        
        ->join('salon_personelleri','on_gorusmeler.personel_id','=','salon_personelleri.id')
        ->leftjoin('paketler','on_gorusmeler.paket_id','=','paketler.id')
        ->leftjoin('urunler','on_gorusmeler.urun_id','=','urunler.id')->select(
             'on_gorusmeler.ad_soyad as musteri',
            'on_gorusmeler.cep_telefon as telefon',
            'on_gorusmeler.satisyapilmadi_not as satisyapilmadisebep',
   DB::raw('CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN paketler.paket_adi ELSE urunler.urun_adi END as ilgili '),
             DB::raw('DATE_FORMAT(on_gorusmeler.hatirlatma_tarihi, "%d.%m.%Y") as hatirlatma'),
            DB::raw('CASE WHEN on_gorusmeler.musteri_tipi=1 THEN "İnternet" WHEN on_gorusmeler.musteri_tipi=2 THEN "Reklam" WHEN on_gorusmeler.musteri_tipi=3 THEN "Instagram" WHEN on_gorusmeler.musteri_tipi=4 THEN "Facebook" WHEN on_gorusmeler.musteri_tipi=5 THEN "Tanıdık" END as musteri_tipi'),
                DB::raw('CONCAT(DATE_FORMAT(on_gorusmeler.created_at, "%d.%m.%Y")) as olusturulma'),
                  'salon_personelleri.personel_adi as gorusmeyiyapan',
                  'on_gorusmeler.durum as durum',
        )->where('salonlar.id',$isletme_id)->where('on_gorusmeler.created_at','like',"%".date('Y-m-d')."%")->get();*/
        return $ongorusmeler;
    }
     public function ongorusmegetirgunluk(Request $request,$isletme_id){
        $ongorusmeler = OnGorusmeler::where('ad_soyad','like','%'.$request->musteridanisan.'%')->where('on_gorusmeler.created_at','like',"%".date('Y-m-d')."%")->where('salon_id',$isletme_id)->orderBy('id','desc')->paginate(10);
        /*$ongorusmeler=DB::table('on_gorusmeler')->join('salonlar','on_gorusmeler.salon_id','=','salonlar.id')
        ->leftjoin('users','on_gorusmeler.user_id','=','users.id')
        
        ->join('salon_personelleri','on_gorusmeler.personel_id','=','salon_personelleri.id')
        ->leftjoin('paketler','on_gorusmeler.paket_id','=','paketler.id')
        ->leftjoin('urunler','on_gorusmeler.urun_id','=','urunler.id')->select(
             'on_gorusmeler.ad_soyad as musteri',
            'on_gorusmeler.cep_telefon as telefon',
            'on_gorusmeler.satisyapilmadi_not as satisyapilmadisebep',
   DB::raw('CASE WHEN on_gorusmeler.paket_id IS NOT NULL THEN paketler.paket_adi ELSE urunler.urun_adi END as ilgili '),
             DB::raw('DATE_FORMAT(on_gorusmeler.hatirlatma_tarihi, "%d.%m.%Y") as hatirlatma'),
            DB::raw('CASE WHEN on_gorusmeler.musteri_tipi=1 THEN "İnternet" WHEN on_gorusmeler.musteri_tipi=2 THEN "Reklam" WHEN on_gorusmeler.musteri_tipi=3 THEN "Instagram" WHEN on_gorusmeler.musteri_tipi=4 THEN "Facebook" WHEN on_gorusmeler.musteri_tipi=5 THEN "Tanıdık" END as musteri_tipi'),
                DB::raw('CONCAT(DATE_FORMAT(on_gorusmeler.created_at, "%d.%m.%Y")) as olusturulma'),
                  'salon_personelleri.personel_adi as gorusmeyiyapan',
                  'on_gorusmeler.durum as durum',
        )->where('salonlar.id',$isletme_id)->where('on_gorusmeler.created_at','like',"%".date('Y-m-d')."%")->get();*/
        return $ongorusmeler;
    }
   public function calisma_saati_guncelle_ekle(Request $request,$isletme_id){
       
         $calismasaati = SalonCalismaSaatleri::where('salon_id',$isletme_id)->get();
                 
                foreach ($calismasaati as $key => $value) {
                    if($key == 0){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                         $calismasaatiherbiri->calisiyor = $request->calisiyor1;
                    $calismasaatiherbiri->haftanin_gunu = 1;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic1;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis1;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 1){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                         $calismasaatiherbiri->haftanin_gunu = 2;
                          $calismasaatiherbiri->calisiyor = $request->calisiyor2;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic2;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis2;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 2){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 3;
                          $calismasaatiherbiri->calisiyor = $request->calisiyor3;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic3;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis3;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 3){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->calisiyor = $request->calisiyor4;
                          $calismasaatiherbiri->haftanin_gunu = 4;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic4;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis4;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 4){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 5;
                          $calismasaatiherbiri->calisiyor = $request->calisiyor5;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic5;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis5;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 5){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 6;
                          $calismasaatiherbiri->calisiyor = $request->calisiyor6;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic6;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis6;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 6){
                          $calismasaatiherbiri = SalonCalismaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 7;
                          $calismasaatiherbiri->calisiyor = $request->calisiyor7;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic7;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis7;
                          $calismasaatiherbiri->save();
                    }
                     
                } 
    }
    public function getUserInfo(Request $request,$userid){
        return User::where('id',$userid)->get();
    }
    public function getResourceInfo(Request $request,$isletme_id){
        $jsonVeri = "";
        $isletme = Salonlar::where('id',$isletme_id)->first();
        if($isletme->randevu_takvim_turu == 1)
            $jsonVeri = Personeller::where('salon_id',$isletme_id)->orWhere('id',183)->get(['id as id','personel_adi as title','renk as bgcolor']);
        else
            $jsonVeri = DB::table('salon_sunulan_hizmetler')->join('hizmet_kategorisi','salon_sunulan_hizmetler.hizmet_kategori_id','=','hizmet_kategorisi.id')->select(['salon_sunulan_hizmetler.hizmet_kategori_id as id','hizmet_kategorisi.hizmet_kategorisi_adi as title','hizmet_kategorisi.renk as bgcolor'])->where('salon_sunulan_hizmetler.salon_id',$isletme_id)->groupBy('salon_sunulan_hizmetler.hizmet_kategori_id')->get();
        return $jsonVeri;
    }
    public function randevuYukle(Request $request,$isletme_id){
       
        $salon = Salonlar::where('id',$isletme_id)->first();
        $randevu_hizmetler = "";
        $resources = "";
        if($salon->randevu_takvim_turu == 1){
            $randevu_hizmetler = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->join('users','randevular.user_id','=','users.id')->select('randevu_hizmetler.id as id','users.name as title',DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),"salon_personelleri.renk as color",'randevu_hizmetler.personel_id as resourceId')->where('randevular.salon_id',$isletme_id)->get();
          
        }
        else{
            $randevu_hizmetler = DB::table('randevu_hizmetler')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->join('users','randevular.user_id','=','users.id')->join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->select('randevu_hizmetler.id as id','users.name as title',DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat is NULL THEN randevular.saat ELSE randevu_hizmetler.saat END) AS start'),DB::raw('CONCAT(randevular.tarih," ", CASE WHEN randevu_hizmetler.saat_bitis is NULL THEN randevular.saat_bitis ELSE  randevu_hizmetler.saat_bitis END) AS end'),DB::raw("(CASE WHEN randevular.durum=2 THEN '#FF0000' WHEN randevular.durum=1 THEN '#34a853' ELSE '#FF4E00' END) as color"),'hizmetler.hizmet_kategori_id as resourceId')->where('randevular.salon_id',$isletme_id)->get();
          
        }
        return $randevu_hizmetler;
    }
    public function siteden_yeni_kayit_kullanici(Request $request)
    {
        $count = IsletmeYetkilileri::where(function($q) use($request){$q->where('gsm1',$request->ceptelefon); $q->orWhere('email',$request->email); })->where('dogrulama_kodu_kullanildi',true)->count();
        if($count>=1)
        {
            return 'Girdiğiniz telefon numarası veya email adresi ile daha önceden açılmış bir üyelik bulunmaktadır. Farklı bir telefon numarası veya email adresi ile tekrar deneyiniz.';
            exit;
        }
        else
        {
            $user = '';
            if($count>=1)
                $user = IsletmeYetkilileri::where(function($q) use($request){$q->where('gsm1',$request->ceptelefon); $q->orWhere('email',$request->email); })->where('dogrulama_kodu_kullanildi',true)->first();
            else
                $user = new IsletmeYetkilileri();
            $user->name = $request->adsoyad;
            $user->gsm1 = $request->ceptelefon;
            $user->email = $request->email;
            $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $random = str_shuffle('1234567890');
            $kod = substr($random, 0, 4);      
            $user->dogrulama_kodu = $kod;
            $user->dogrulama_kodu_kullanildi = false;
            $user->save();
            
    
            $headers = array(
                 'Authorization: Key LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5',
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $postData = json_encode( array( "originator"=> "RANDVMCEPTE", "messages"=>array(array("to"=>$request->ceptelefon,"message"=> "Randevum Cepte üyelik doğrulama kodunuz : ".$kod)),"encoding"=>"auto") );
 
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
            $response=curl_exec($ch);
           
            return $user->id;
        }
       
    }
    public function siteden_yeni_kayit(Request $request)
    {
        $yetkili = IsletmeYetkilileri::where('id',$request->yetkiliid)->first();
        if($yetkili->dogrulama_kodu != $request->dogrulamakodu)
        {
            return 'hatalikod';
            exit;
        }
        else
        {
            $yetkili->dogrulama_kodu_kullanildi = true;
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 5);
            $yetkili->password = Hash::make($olusturulansifre);
            $yetkili->save();
            $salon = new Salonlar();
            $salon->salon_adi = $request->isletmeadi;
            $salon->salon_turu_id = SalonTuru::where('salon_turu_adi',$request->isletmeturu)->value('id');
            $salon->randevu_saat_araligi = 15;
            $salon->randevu_takvim_turu = 1;
            $salon->uyelik_bitis_tarihi = date('Y-m-d',strtotime('+7 days',strtotime(date('Y-m-d'))));
            $salon->uyelik_turu = 3;
            $salon->demo_hesabi = true;
            $salon->save();
            SalonCalismaSaatleri::insert([
                ['salon_id'=> $salon->id,'haftanin_gunu'=>1,'calisiyor'=>1,'baslangic_saati'=>'07:00:00','bitis_saati'=>'20:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>2,'calisiyor'=>1,'baslangic_saati'=>'07:00:00','bitis_saati'=>'20:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>3,'calisiyor'=>1,'baslangic_saati'=>'07:00:00','bitis_saati'=>'20:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>4,'calisiyor'=>1,'baslangic_saati'=>'07:00:00','bitis_saati'=>'20:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>5,'calisiyor'=>1,'baslangic_saati'=>'07:00:00','bitis_saati'=>'20:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>6,'calisiyor'=>1,'baslangic_saati'=>'07:00:00','bitis_saati'=>'20:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>7,'calisiyor'=>1,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]
            ]);
            SalonMolaSaatleri::insert([
                ['salon_id'=> $salon->id,'haftanin_gunu'=>1,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>2,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>3,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>4,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>5,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>6,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'haftanin_gunu'=>7,'mola_var'=>0,'baslangic_saati'=>'00:00:00','bitis_saati'=>'00:00:00','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]
            ]);
            SalonSMSAyarlari::insert([
                ['salon_id'=> $salon->id,'ayar_id'=>1,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>2,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>3,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>4,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>5,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>6,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>7,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>8,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>9,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>10,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>11,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>12,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>13,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>14,'musteri'=>1,'personel'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>15,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>16,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>17,'musteri'=>0,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>18,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
                ['salon_id'=> $salon->id,'ayar_id'=>19,'musteri'=>1,'personel'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')],
            ]);
            DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values (1, "App\\\IsletmeYetkilileri",'.$yetkili->id.','.$salon->id.')');
            $personel = new Personeller();
            $personel->salon_id = $salon->id;
            $personel->personel_adi = $yetkili->name;
            $personel->cep_telefon = $yetkili->gsm1;
            $personel->yetkili_id = $yetkili->id;
            $personel->takvimde_gorunsun = true;
            $personel->takvim_sirasi = 1;
            $personel->renk=1;
            $personel->aktif = true;    
            
            $personel->save();
            
        
            /*$ornekportfoy = new MusteriPortfoy();
            $ornekportfoy->user_id=1;
            $ornekportfoy->salon_id = $salon->id;
            $ornekportfoy->tur=0;
            $ornekportfoy->aktif=1;
            $ornekportfoy->karaliste=0;
            $ornekportfoy->save();
            $ornekportfoy2 = new MusteriPortfoy();
            $ornekportfoy2->user_id=2;
            $ornekportfoy2->salon_id = $salon->id;
            $ornekportfoy2->tur=0;
            $ornekportfoy2->aktif=1;
            $ornekportfoy2->karaliste=0;
            $ornekportfoy2->save();*/
            
            $randevu = new Randevular();
            $randevu->user_id = 1;
            $randevu->tarih = date('Y-m-d');
            $randevu->saat = date('H:i:s');
            $randevu->personel_notu = 'Örnek personel notu';
            $randevu->notlar = 'Örnek müşteri/danışan notu';
            $randevu->salon = true;
            $randevu->olusturan_personel_id = $yetkili->id;
            $randevu->sms_hatirlatma = true;
            $randevu->saat_bitis = date('H:i',strtotime('+1 hours',strtotime(date('H:i'))));
            $randevu->durum = 1;
            $randevu->save();
            $randevuhizmet = new RandevuHizmetler();
            $randevuhizmet->personel_id = $personel->id;
            $randevuhizmet->hizmet_id = 2;
            $randevuhizmet->saat = date('H:i:s');
            $randevuhizmet->saat_bitis = date('H:i:s',strtotime('+1 hours',strtotime(date('H:i'))));
            $randevuhizmet->sure_dk = 60;
            $randevuhizmet->cihaz_id = 1;
            $randevuhizmet->oda_id = 1;
            $randevuhizmet->save();
            $urun = new Urunler();
            $urun->urun_adi = 'Örnek Ürün 1';
            $urun->fiyat = 100;
            $urun->stok_adedi = 5;
            $urun->dusuk_stok_siniri = 1;
            $urun->salon_id = $salon->id;
            $urun->aktif=true;
            $urun->save();
            $paket = new Paketler();
            $paket->paket_adi = 'Örnek Paket 1';
            $paket->aktif = true;
            $paket->salon_id = $salon->id;
            $paket->save();            
            $pakethizmet = new PaketHizmetler();
            $pakethizmet->paket_id = $paket->id;
            $pakethizmet->hizmet_id = 2;
            $pakethizmet->seans = 1;
            $pakethizmet->fiyat = 100;
            $pakethizmet->save();
            $urun2 = new Urunler();
            $urun2->urun_adi = 'Örnek Ürün 2';
            $urun2->fiyat = 200;
            $urun2->stok_adedi = 5;
            $urun2->dusuk_stok_siniri = 1;
            $urun2->salon_id = $salon->id;
            $urun2->aktif=true;
            $urun2->save();
            $paket2 = new Paketler();
            $paket2->paket_adi = 'Örnek Paket 2';
            $paket2->aktif = true;
            $paket2->salon_id = $salon->id;
            $paket2->save();            
            $pakethizmet2 = new PaketHizmetler();
            $pakethizmet2->paket_id = $paket2->id;
            $pakethizmet2->hizmet_id = 3;
            $pakethizmet2->seans = 1;
            $pakethizmet2->fiyat = 200;
            $pakethizmet2->save();
            
            $ongorusme = new OnGorusmeler(); 
            $ongorusme->salon_id = $salon->id;
            $ongorusme->user_id = 1;
            $ongorusme->ad_soyad = 'Örnek Müşteri/Danışan 1';
            $ongorusme->email = 'ornek@email.com';
            $ongorusme->cinsiyet = 0;
            $ongorusme->adres = 'Örnek Adres 1';
            $ongorusme->aciklama='Örnek Açıklama 1';
            $ongorusme->il_id =1;
            $ongorusme->musteri_tipi = 1;
            $ongorusme->meslek = 'Örnek Meslek 1';
            $ongorusme->urun_id = $urun->id;
            
            $ongorusme->hatirlatma_tarihi = date('Y-m-d');
            $ongorusme->personel_id = Personeller::where('yetkili_id',$yetkili->id)->where('salon_id',$salon->id)->value('id');
            $ongorusme->save();
            $randevu_ongorusme = new Randevular();
            $randevu_ongorusme->on_gorusme_id = $ongorusme->id;
            $randevu_ongorusme->user_id = $ongorusme->user_id;
            $randevu_ongorusme->salon_id = $ongorusme->salon_id;
            $randevu_ongorusme->tarih = date('Y-m-d');
            $randevu_ongorusme->saat = date('H:i:s');
            $randevu_ongorusme->salon = true;
            $randevu_ongorusme->sms_hatirlatma = true; 
            $randevu_ongorusme->durum = 1;
            $randevu_ongorusme->olusturan_personel_id = $yetkili->id;
            $randevu_ongorusme->save();
            
            $ongorusmehizmeti = new RandevuHizmetler();
            $ongorusmehizmeti->hizmet_id = 1;
            $ongorusmehizmeti->personel_id = $ongorusme->personel_id;
            $ongorusmehizmeti->saat = $ongorusme->ongorusme_saati;
            $ongorusmehizmeti->saat_bitis = date('H:i:s',strtotime('+1 hours',strtotime($ongorusme->ongorusme_saati)));
            $ongorusmehizmeti->randevu_id = $randevu_ongorusme->id;
            $ongorusmehizmeti->save();
            $ongorusme_paket = new OnGorusmeler(); 
            $ongorusme_paket->salon_id = $salon->id;
            $ongorusme_paket->user_id = 2;
            $ongorusme_paket->ad_soyad = 'Örnek Müşteri/Danışan 2';
            $ongorusme_paket->email = 'ornek@email.com';
            $ongorusme_paket->cinsiyet = 1;
            $ongorusme_paket->adres = 'Örnek Adres 2';
            $ongorusme_paket->aciklama='Örnek Açıklama 2';
            $ongorusme_paket->il_id =2;
            $ongorusme_paket->musteri_tipi = 2;
            $ongorusme_paket->meslek = 'Örnek Meslek 2';
            $ongorusme_paket->paket_id = $paket->id;
             
            $ongorusme_paket->hatirlatma_tarihi = date('Y-m-d');
            $ongorusme_paket->personel_id = Personeller::where('yetkili_id',$yetkili->id)->where('salon_id',$salon->id)->value('id');
            $ongorusme_paket->save();
            $randevu_ongorusme_paket = new Randevular();
            $randevu_ongorusme_paket->on_gorusme_id = $ongorusme_paket->id;
            $randevu_ongorusme_paket->user_id = $ongorusme_paket->user_id;
            $randevu_ongorusme_paket->salon_id = $ongorusme_paket->salon_id;
            $randevu_ongorusme_paket->tarih = date('Y-m-d');
            $randevu_ongorusme_paket->saat = date('H:i:s');
            $randevu_ongorusme_paket->salon = true;
            $randevu_ongorusme_paket->sms_hatirlatma = true; 
            $randevu_ongorusme_paket->durum = 1;
            $randevu_ongorusme_paket->olusturan_personel_id = $yetkili->id;
            $randevu_ongorusme_paket->save();
            
            $ongorusmehizmeti_paket = new RandevuHizmetler();
            $ongorusmehizmeti_paket->hizmet_id = 1;
            $ongorusmehizmeti_paket->personel_id = $ongorusme_paket->personel_id;
            $ongorusmehizmeti_paket->saat = date('H:i:s');
            $ongorusmehizmeti_paket->saat_bitis = date('H:i:s',strtotime('+1 hours',strtotime($ongorusme_paket->ongorusme_saati)));
            $ongorusmehizmeti_paket->randevu_id = $randevu_ongorusme_paket->id;
            $ongorusmehizmeti_paket->save();
            $urun_adisyon_id = self::yeni_adisyon_olustur(1,$salon->id,'Ürün Satışı',date('Y-m-d'),$yetkili);
            $adisyon_urun = new AdisyonUrunler();
            $adisyon_urun->islem_tarihi = date('Y-m-d');
            $adisyon_urun->adisyon_id= $urun_adisyon_id;
            $adisyon_urun->urun_id = $urun2->id;
            $adisyon_urun->adet = 1;
            $adisyon_urun->fiyat = 200;
            $adisyon_urun->personel_id = Personeller::where('salon_id',$salon->id)->where('yetkili_id',$yetkili->id)->value('id');
            $adisyon_urun->save();
 
            $adisyon_id2 = self::yeni_adisyon_olustur(1,$salon->id,'Paket Satışı',date('Y-m-d'),$yetkili); 
            $adisyon_paket_id = self::adisyona_paket_ekle($adisyon_id2,$paket2->id,200,date('Y-m-d'),1,Personeller::where('salon_id',$salon->id)->where('yetkili_id',$yetkili->id)->value('id'),null,null);
            $paket_mevcut = Paketler::where('id',$paket2->id)->first(); 
            $seans_randevu = new Randevular();  
            $seans_randevu->user_id = 1;
            $seans_randevu->tarih = date('Y-m-d');
            $seans_randevu->salon_id = $salon->id;
            $seans_randevu->durum = 1;
            $seans_randevu->saat = date('H:i:s');
            $seans_randevu->olusturan_personel_id = Personeller::where('yetkili_id',$yetkili->id)->where('salon_id',$request->sube)->value('id');
            $seans_randevu->salon = 1;
            $seans_randevu->save(); 
            $seans = new AdisyonPaketSeanslar();
            $seans->adisyon_paket_id = $adisyon_paket_id;
            $seans->seans_tarih = date('Y-m-d');
            $seans->hizmet_id = 3;
            $seans->seans_no = 1;
            $seans->seans_saat = date('H:i:s');
            $seans->randevu_id = $seans_randevu->id;
            $seans->save();
            $seans_randevu_hizmet = new RandevuHizmetler();
            $seans_randevu_hizmet->randevu_id = $seans_randevu->id;
            $seans_randevu_hizmet->hizmet_id = 3;
                        
            $seans_randevu_hizmet->sure_dk = 60;
                        
            $seans_randevu_hizmet->saat = date('H:i:s');
            $seans_randevu_hizmet->saat_bitis = date("H:i:s", strtotime('+60 minutes', strtotime(date('H:i:s')))); 
            $seans_randevu_hizmet->save(); 
                
         
            $etkinlik = new Etkinlikler();
            $etkinlik->etkinlik_adi = 'Örnek Etkinlik 1';
            $etkinlik->tarih_saat = date('Y-m-d H:i:s');    
            $etkinlik->fiyat = 1000;
            $etkinlik->salon_id = $salon->id;
            $etkinlik->aktifmi=1;
            $etkinlik->mesaj='Etkinliğimizde davetlisiniz örnek mesaj 1';
            $etkinlik->save();
         
            $yenikatilimci = new EtkinlikKatilimcilari();
            $yenikatilimci->etkinlik_id = $etkinlik->id;
            $yenikatilimci->user_id = 2;
            $yenikatilimci->save();
            $yenikatilimci2 = new EtkinlikKatilimcilari();
            $yenikatilimci2->etkinlik_id = $etkinlik->id;
            $yenikatilimci2->user_id = 1;
            $yenikatilimci2->save();
             
            $kampanya_yonetimi = new KampanyaYonetimi();
            $kampanya_yonetimi->paket_isim = 'Örnek Paket 1';
            $kampanya_yonetimi->hizmet_adi = 'Örnek Hizmet Kampanyası';
            $kampanya_yonetimi->fiyat = 200;
            $kampanya_yonetimi->seans = 1;
            $kampanya_yonetimi->salon_id = $salon->id;
            $kampanya_yonetimi->aktifmi=1;
            $kampanya_yonetimi->mesaj= 'Örnek Hizmet Kampanyası';
            $kampanya_yonetimi->save();
            
             
            $yenikatilimcikampanya = new KampanyaKatilimcilari();
            $yenikatilimcikampanya->kampanya_id = $kampanya_yonetimi->id;
            $yenikatilimcikampanya->user_id = 1;
            $yenikatilimcikampanya->save();
            $yenikatilimcikampanya = new KampanyaKatilimcilari();
            $yenikatilimcikampanya->kampanya_id = $kampanya_yonetimi->id;
            $yenikatilimcikampanya->user_id = 2;
            $yenikatilimcikampanya->save();
            $yeninot = new Ajanda();
            $yeninot->ajanda_baslik='Örnek Hatırlatma';
            $yeninot->ajanda_tarih=date('Y-m-d');
            $yeninot->ajanda_hatirlatma=0;
            $yeninot->ajanda_saat=date('H:i:s');
            $yeninot->ajanda_icerik='Örnek ajanda notu';
            $yeninot->ajanda_hatirlatma_saat=date('H:i:s');
            $yeninot->salon_id = $salon->id;
            $yeninot->ajanda_olusturan=Personeller::where('salon_id',$salon->id)->where('yetkili_id',$yetkili->id)->value('id');
            $yeninot->aktif=true;
            $yeninot->save();
            $form=new Arsiv();
            $random = str_shuffle('1234567');
            $kod = substr($random, 0, 1);      
            $form->user_id=1;
            $form->form_id=$kod;
            $form->personel_id=Personeller::where('salon_id',$salon->id)->where('yetkili_id',$yetkili->id)->value('id');
            $form->cevapladi=false;
            $form->cevapladi2=false;
            $form->salon_id = $salon->id;
            $form->form_olusturan=Personeller::where('salon_id',$request->sube)->where('yetkili_id',$yetkili->id)->value('id');
            $form->save();
             
            $form2=new Arsiv();
            $random2 = str_shuffle('1234567');
            $kod2 = substr($random2, 0, 1);      
            $form2->user_id=2;
            $form2->form_id=$kod2;
            $form2->personel_id=Personeller::where('salon_id',$salon->id)->where('yetkili_id',$yetkili->id)->value('id');
            $form2->cevapladi=false;
            $form2->cevapladi2=false;
            $form2->salon_id = $salon->id;
            $form2->form_olusturan=Personeller::where('salon_id',$request->sube)->where('yetkili_id',$yetkili->id)->value('id');
            $form2->save();
            /*$paraekle=new Tahsilatlar();
            $paraekle->olusturan_id=Personeller::where('salon_id',$request->sube)->where('yetkili_id',$yetkili->id)->value('id');
            $paraekle->salon_id = $salon->id;
            $paraekle->odeme_tarihi=date('Y-m-d');
            $paraekle->tutar=200;
            $paraekle->odeme_yontemi_id=1;
            $paraekle->notlar='Örnek tahsilat';
            $paraekle->save();
            $paraal=new Masraflar();
            $paraal->harcayan_id=$request->paraalan;
            $paraal->salon_id = $request->sube;
            $paraal->tarih=$request->paraalma_tarihi;
            $paraal->tutar=str_replace('.','',$request->paraalma_tutari);
            $paraal->odeme_yontemi_id=$request->paraalma_odeme_yontemi;
            $paraal->notlar=$request->paraalma_aciklama;
            $paraal->save();*/
            $headers = array(
                 'Authorization: Key LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5',
                 'Content-Type: application/json',
                 'Accept: application/json'
            );
            $postData = json_encode( array( "originator"=> "RANDVMCEPTE", "messages"=>array(array("to"=>$yetkili->gsm1,"message"=> "Randevumcepte'ye hoşgeldiniz. Sistem kullanıcı adınız : ".$yetkili->gsm1. ' şifreniz : '.$olusturulansifre)),"encoding"=>"auto") );
 
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                    
            $response=curl_exec($ch);
            return 'Hesabınız başarıyla oluşturulmuştur. Telefonunuza gönderilen şifreniz ile sisteme giriş yapabilirsiniz. Yönlendiriliyorsunuz...';
        }
         
       
    }
    public function kalan_sms($isletme_id){
        $isletme = Salonlar::where('id',$isletme_id)->first();
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
         $kalan_sms_miktar = 0;
         if($isletme->sms_apikey !== null){
            $kalan_sms = json_decode($response,true);
            if(array_key_exists('balance',$kalan_sms['response']))
                  $kalan_sms_miktar = $kalan_sms['response']['balance'];
         }
        return $kalan_sms_miktar;
    }
    public function isletmepuani(Request $request,$isletme_id)
    {
        return round(SalonPuanlar::where('salon_id',$isletme_id)->sum('puan') / SalonPuanlar::where('salon_id',$isletme_id)->count(),1);
    }
    public function ozetsayfasi(Request $request,$isletme_id,$userid){
        $adisyonlar = self::adisyon_yukle($request,'','',date('Y-m-01'),date('Y-m-d'),'','',$isletme_id);
  
        $santral_raporlari = self::santral_raporlari($isletme_id,date('Y-m-d'),date('Y-m-d'),'',$request);
        
         $randevu=Randevular::where('salon_id',$isletme_id)->where('tarih',date('Y-m-d'))->count();
         $ongorusme=OnGorusmeler::where('salon_id',$isletme_id)->where('on_gorusmeler.created_at','like','%'.date('Y-m-d').'%')->count();
         $salon = Salonlar::where('id',$isletme_id)->value('salon_adi');
         $urun_satis=self::adisyon_yukle($request,3,0,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),'','',$isletme_id)->count();
         $paket_satis=self::adisyon_yukle($request,2,0,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),'','',$isletme_id)->count();
         $tahsilat=Tahsilatlar::where('salon_id',$isletme_id)->where('odeme_tarihi','>=',date('Y-m-01'))->where('odeme_tarihi','<=',date('Y-m-d'))->get();
         $masraf=Masraflar::where('salon_id',$isletme_id)->where('tarih','>=',date('Y-m-01'))->where('tarih','<=',date('Y-m-d'))->get();
         $alacaklar = Alacaklar::where('salon_id',$isletme_id)->where('planlanan_odeme_tarihi','>=',date('Y-m-01'))->where('planlanan_odeme_tarihi','<=',date('Y-m-d'))->sum('tutar');
        $okunmamisbildirimler = Bildirimler::where('salon_id', $isletme_id)
    ->where('personel_id', Personeller::where('yetkili_id', $userid)->where('salon_id', $isletme_id)->value('id'))
    ->where('okundu', '0') // Count only unread notifications
    ->count();
         $ajandanot = '';
         $puan = SalonPuanlar::where('salon_id',$isletme_id)->count() == 0 ? 0 : SalonPuanlar::where('salon_id',$isletme_id)->sum('puan') / SalonPuanlar::where('salon_id',$isletme_id)->count();
         $ajandanot=DB::table('ajanda')->join('salon_personelleri','ajanda.ajanda_olusturan','=','salon_personelleri.id')->
        select(
            'ajanda.id as id',
            'ajanda.ajanda_baslik as title',
            'ajanda.ajanda_icerik as description',
            'ajanda.ajanda_hatirlatma_saat as ajanda_hatirlatama_saat',
            DB::raw("CASE WHEN ajanda.ajanda_hatirlatma=1 THEN '<i class=\'fa fa-check\' style=\'font-size:20px; color:green;margin-left:40px;\'> </i>' WHEN ajanda.ajanda_hatirlatma=0 THEN '<i class=\'fa fa-check\' style=\'font-size:20px; color:green; display:none; margin-left:40px;\'> </i>' END AS ajanda_hatirlatma"),
           
            DB::raw('CONCAT(ajanda_tarih," ",DATE_FORMAT(ajanda.ajanda_saat, "%H:%i")) as start'),
            
            'salon_personelleri.personel_adi as ajanda_olusturan',
            DB::raw("CASE WHEN ajanda.ajanda_durum=1 THEN '<button class=\'btn btn-success btn-block\' style=\'line-height:5px\'>Okundu</button>' WHEN 
            ajanda.ajanda_durum=0 THEN '<button class=\'btn btn-danger btn-block\' style=\'line-height:5px\'>Okunmadı</button>' END AS ajanda_durum"),
          
          
           
        )->where('ajanda.aktif',true)->where('ajanda.ajanda_olusturan',Personeller::where('yetkili_id',$userid)->where('salon_id',$isletme_id)->value('id'))->where('ajanda.salon_id',$isletme_id)->where('ajanda.ajanda_tarih',date('Y-m-d'))->orderBy('ajanda.ajanda_saat','asc')->get();
         
         return array('randevu_sayisi' => $randevu,
            'ongorusme_sayisi'=>$ongorusme,
            'urun_satislari'=>$urun_satis,
            'paket_satislari'=>$paket_satis,
            'toplam_kasa'=>number_format(($tahsilat->sum('tutar')-$masraf->sum('tutar')),2,',','.'),
            'kalan_tutar' =>number_format($alacaklar,2,',','.') ,
            'ajanda'=>$ajandanot,
            'kalan_sms'=>self::kalan_sms($isletme_id),
            'puan'=>$puan,
            'gelen_arama'=>$santral_raporlari['gelen_arama'],
            'cevapsiz_arama'=>$santral_raporlari['cevapsiz_arama'],
            'giden_arama'=>$santral_raporlari['giden_arama'],
            'isletme_adi'=>$salon,
            'puan'=>round( $puan ,1),
            'okunmamisbildirimler'=>$okunmamisbildirimler,
        );
    }
    public function randevulistedeneme(Request $request,$isletme_id)
    {
          $salon=null;
        $web=null;
        $uygulama=null;
        if($request->salon)
            $salon=true;
        if($request->web)
            $web=true;
        if($request->uygulama)
            $uygulama=true;
         return self::randevu_liste_getir($request,$request->tarih1,$request->tarih2,$salon,$web,$uygulama,$request->durum,$isletme_id,'',$request->musteridanisan);
    }
    public function randevu_liste_getir(Request $request,$tarih1,$tarih2,$salon,$web,$uygulama,$durum,$salon_id,$userid,$musteridanisanadi)
    {
  
         
         $randevular = DB::table('randevu_hizmetler')->
        join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->
        join('hizmetler','randevu_hizmetler.hizmet_id','=','hizmetler.id')->
        join('users','randevular.user_id','=','users.id')->
        leftjoin('salon_personelleri','randevu_hizmetler.personel_id','=','salon_personelleri.id')->
        
        leftjoin('isletmeyetkilileri as y1','randevular.olusturan_personel_id','=','y1.id')->
        leftjoin('isletmeyetkilileri as y2','randevu_hizmetler.personel_id','=','y2.personel_id')->
        leftjoin('model_has_roles','y2.id','=','model_has_roles.model_id')->
        
        leftjoin('cihazlar','randevu_hizmetler.cihaz_id','=','cihazlar.id')->
        leftjoin('odalar','randevu_hizmetler.oda_id','=','odalar.id')->
        select(
            'randevular.salon as salon',
            'randevular.uygulama as uygulama',
            'randevular.web as web',
            'users.id as user_id',
            'randevular.id as id',
            'randevu_hizmetler.yardimci_personel',
                'users.name as musteri',
                'users.cep_telefon as telefon',
                    DB::raw("CASE WHEN randevu_hizmetler.personel_id is not null THEN CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', GROUP_CONCAT(salon_personelleri.personel_adi), ')') WHEN randevu_hizmetler.cihaz_id is not null THEN CONCAT(GROUP_CONCAT(hizmetler.hizmet_adi),' (', cihazlar.cihaz_adi, ')') END as hizmetler"),
                    'odalar.oda_adi as odalar',
                    DB::raw('DATE_FORMAT(randevular.tarih, "%d.%m.%Y") as tarih'),
                    DB::raw('DATE_FORMAT(randevular.saat, "%H:%i") as  saat'),
                    
                    //DB::raw("CASE WHEN randevular.durum=1 THEN CONCAT('Onaylı',CASE WHEN randevular.randevuya_geldi=true THEN ' - Geldi' WHEN randevular.randevuya_geldi=false THEN ' - Gelmedi' WHEN randevular.randevuya_geldi IS NULL THEN '' END) WHEN randevular.durum=0 then CONCAT('Beklemede') WHEN randevular.durum=3 THEN CONCAT('Müşteri Tarafından İptal') ELSE CONCAT('İptal') END AS durum"),
                 'randevular.randevuya_geldi as geldimi',
                    'randevular.durum as durum',
                    
                    'randevular.tahsilat_eklendi as tahsilat_eklendi',
                    DB::raw('CONCAT(COALESCE(SUM(randevu_hizmetler.fiyat),0) ," ₺") as toplam'),
                    DB::raw('CASE WHEN randevular.web=1 THEN "Web" WHEN randevular.uygulama=1 THEN "Uygulama" ELSE y1.name END as olusturan'),
                    DB::raw('DATE_FORMAT(randevular.created_at, "%d.%m.%Y %H:%i") as olusturulma'),
                   
  
                )->where('randevular.salon_id',$salon_id)->where('users.name','like','%'.$musteridanisanadi.'%')->
            where(function($q) use ($tarih1,$tarih2) {if($tarih1 != '') $q->where('randevular.tarih','>=',$tarih1);if($tarih2 != '') $q->where('randevular.tarih','<=',$tarih2); })->
            
            where(function($q) use ($salon,$uygulama,$web){ if($salon!='') $q->where('randevular.salon', $salon); if($web!='') $q->orWhere('randevular.web',$web); if($uygulama!='') $q->orWhere('randevular.uygulama',$uygulama);})->
            where(function($q) use ($durum){if($durum!='') $q->where('randevular.durum',$durum);})->where(function($q) use ($userid){if($userid!='') $q->where('randevular.user_id',$userid);})->
        groupBy('randevu_hizmetler.randevu_id')->orderBy('randevular.id','desc')->paginate(10);
        
        return $randevular; 
    }
    public function salon_tarafindan_randevular_get(Request $request,$isletme_id){
        return self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),true,null,null,null,$isletme_id,'','');
    }
    public function web_tarafindan_randevular_get(Request $request,$isletme_id){
        return self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),null,true,null,null,$isletme_id,'','');
    }
    public function uygulama_uzerindan_randevular_get(Request $request,$isletme_id){
        return self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),null,null,true,null,$isletme_id,'','');
    }
    public function tum_randevular_get(Request $request,$isletme_id){
        return self::randevu_liste_getir($request,date('Y-m-d'),date('Y-m-d'),true,true,true,'',$isletme_id,'','');
    }
    public function tum_randevular_get_filtre(Request $request,$isletme_id)
    { 
          $musteri_id="";
        $musteri_id=MusteriPortfoy::where('user_id',$request->user_id)->where('salon_id',$isletme_id)->value('id');
       
        $salon=null;
        $web=null;
        $uygulama=null;
        if($request->salon)
            $salon=true;
        if($request->web)
            $web=true;
        if($request->uygulama)
            $uygulama=true;
         return self::randevu_liste_getir($request,$request->tarih1,$request->tarih2,$salon,$web,$uygulama,$request->durum,$isletme_id,$request->musteri_id,$request->musteridanisan);
    }
    
    public function logout(Request $res)
    {
      if (Auth::user()) {
        $user = Auth::guard('isletmeyonetim')->user()->token();
        $user->revoke();
        return response()->json([
          'success' => true,
          'message' => 'Logout successfully'
      ]);
      }else {
        return response()->json([
          'success' => false,
          'message' => 'Unable to Logout'
        ]);
      }
     }
    public function alacaklar(Request $request,$isletme_id){
        $alacaklar= DB::table('alacaklar')->join('users','alacaklar.user_id','=','users.id')->
        select('alacaklar.id as id',
            'users.name as musteri',
            'alacaklar.planlanan_odeme_tarihi as planlanan_odeme_tarihi',
            DB::raw('FORMAT(alacaklar.tutar,2,"tr_TR") as tutar'),   DB::raw('DATE_FORMAT(alacaklar.created_at, "%d.%m.%Y %H:%i") as olusturulma'),)->where('users.name','like','%'.$request->musteridanisan.'%')->where('planlanan_odeme_tarihi','>=',date('Y-m-01'))->where('planlanan_odeme_tarihi','<=',date('Y-m-d'))->where('alacaklar.salon_id',$isletme_id)->paginate(10);
        return $alacaklar;
     }
      
    public function odeme_bildirimi(Request $request){
        ## 2. ADIM için örnek kodlar ##
        ## ÖNEMLİ UYARILAR ##
        ## 1) Bu sayfaya oturum (SESSION) ile veri taşıyamazsınız. Çünkü bu sayfa müşterilerin yönlendirildiği bir sayfa değildir.
        ## 2) Entegrasyonun 1. ADIM'ında gönderdiğniz merchant_oid değeri bu sayfaya POST ile gelir. Bu değeri kullanarak
        ## veri tabanınızdan ilgili siparişi tespit edip onaylamalı veya iptal etmelisiniz.
        ## 3) Aynı sipariş için birden fazla bildirim ulaşabilir (Ağ bağlantı sorunları vb. nedeniyle). Bu nedenle öncelikle
        ## siparişin durumunu veri tabanınızdan kontrol edin, eğer onaylandıysa tekrar işlem yapmayın. Örneği aşağıda bulunmaktadır.
        $post = $_POST;
        ####################### DÜZENLEMESİ ZORUNLU ALANLAR #######################
        #
        ## API Entegrasyon Bilgileri - Mağaza paneline giriş yaparak BİLGİ sayfasından alabilirsiniz.
        $merchant_key   = 'Mwjwj1HdCwxYJY2j';
        $merchant_salt  = 'TuF3kaYgxbNKR7Zx';
        ###########################################################################
        ####### Bu kısımda herhangi bir değişiklik yapmanıza gerek yoktur. #######
        #
        ## POST değerleri ile hash oluştur.
        $hash = base64_encode( hash_hmac('sha256', $post['merchant_oid'].$merchant_salt.$post['status'].$post['total_amount'], $merchant_key, true) );
        #
        ## Oluşturulan hash'i, paytr'dan gelen post içindeki hash ile karşılaştır (isteğin paytr'dan geldiğine ve değişmediğine emin olmak için)
        ## Bu işlemi yapmazsanız maddi zarara uğramanız olasıdır.
        if( $hash != $post['hash'] )
            die('PAYTR notification failed: bad hash');
        ###########################################################################
        ## BURADA YAPILMASI GEREKENLER
        ## 1) Siparişin durumunu $post['merchant_oid'] değerini kullanarak veri tabanınızdan sorgulayın.
        ## 2) Eğer sipariş zaten daha önceden onaylandıysa veya iptal edildiyse  echo "OK"; exit; yaparak sonlandırın.
        /* Sipariş durum sorgulama örnek
           $durum = SQL
           if($durum == "onay" || $durum == "iptal"){
                echo "OK";
                exit;
            }
         */
        if( $post['status'] == 'success' ) { ## Ödeme Onaylandı 
             //$isletme = \App\Salonlar::where('id',$post['sube'])->first();
            echo "OK";
            exit;
        } else { ## Ödemeye Onay Verilmedi
            ## BURADA YAPILMASI GEREKENLER
            ## 1) Siparişi iptal edin.
            ## 2) Eğer ödemenin onaylanmama sebebini kayıt edecekseniz aşağıdaki değerleri kullanabilirsiniz.
            ## $post['failed_reason_code'] - başarısız hata kodu
            ## $post['failed_reason_msg'] - başarısız hata mesajı 
            echo "Başarısız";
            exit; 
        }  
    }
    public function yeni_adisyon_olustur($musteriid,$salonid,$adisyonnotu,$tarih,$yetkili)
    {
        $adisyon = new Adisyonlar();
        $adisyon->user_id = $musteriid;
        $adisyon->salon_id =  $salonid;
        $adisyon->olusturan_id = $yetkili->id;
        $adisyon->tarih = $tarih;
        $adisyon->save();
        return $adisyon->id;
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
                if(SabitNumaralar::where('salon_id',$salon_id)->value('numara'))
                {
                    if($cdr['src']==SabitNumaralar::where('salon_id',$salon_id)->value('numara') || $cdr['did']==SabitNumaralar::where('salon_id',$salon_id)->value('numara'))
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
                                $musteri_tel = $tel_kaynak; 
                        }
                        if($cdr['disposition']=='NO ANSWER' && str_contains($cdr['recordingfile'],'in-')){
                            $durum = '<button class="btn btn-danger">CEVAPSIZ</button>';
                            $gorusmeyi_yapan =  Personeller::where('dahili_no',$cdr['cnum'])->orWhere('dahili_no',$cdr['dst'])->value('personel_adi');
                            $cevapsiz_arama++; 
                        }
                        else{
                            $cevapsiz_arama_var = false;
                            if(SabitNumaralar::where('salon_id',$salon_id)->value('numara')==$cdr['src']){
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
                            $ses_kaydi = '<a download name="ses_kaydi_indir" href="https://voicerecords.randevumcepte.com.tr/monitor/'.$tarih_dir[0].'/'.$tarih_dir[1].'/'.$tarih_son[0].'/'.$cdr['recordingfile'].'" class="btn btn-primary"><i class="fa fa-download"></i></a>
                                <button name="ses_kaydi_cal" data-value="https://voicerecords.randevumcepte.com.tr/monitor/'.$tarih_dir[0].'/'.$tarih_dir[1].'/'.$tarih_son[0].'/'.$cdr['recordingfile'].'" class="btn btn-danger"><i class="fa fa-play"></i></button>';
                        
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
    
    public function bildirimgetir(Request $request,$isletme_id,$personel_id)
    {
        $personel = Personeller::where('salon_id',$isletme_id )->where('yetkili_id',$personel_id)->value('id');
    
        $bildirimler = Bildirimler::where('personel_id',$personel_id)->where('salon_id',$isletme_id)->orderBy('id','desc')->get();
        return $bildirimler;
    }
    
    public function bildirimguncelle(Request $request){
         $bildirim = Bildirimler::where('id',$request->bildirim_id)->first();
  
       $bildirim->okundu=1;
        $bildirim->save();
        return $bildirim;
    }
    public function notekleduzenle(Request $request,$salonid,$olusturan)
    {   
                $yeninot = '';
        if($request->ajandaid!='')
            $yeninot=Ajanda::where('id',$request->ajandaid)->first();
        else
            $yeninot = new Ajanda();
        //$yeninot = new Ajanda();
        $yeninot->ajanda_baslik=$request->baslik;
        $yeninot->ajanda_tarih=date('Y-m-d',strtotime($request->tarih));
        $yeninot->ajanda_hatirlatma=$request->hatirlatma;
        $yeninot->ajanda_saat=date('H:i:s',strtotime($request->saat));
        $yeninot->ajanda_icerik=$request->icerik;
        $yeninot->ajanda_hatirlatma_saat=$request->hatirlatma_saati;
        $yeninot->salon_id = $salonid;
        $yeninot->ajanda_olusturan=Personeller::where('salon_id',$salonid)->where('yetkili_id',$olusturan)->value('id');
        $yeninot->aktif=true;
        $yeninot->save();
        return 'Notunuz başarıyla kaydedildi';
    }
    public function ajandasil(Request $request)
    {
        Ajanda::where('id',$request->id)->delete();
    }
    public function etkinlikyukle(Request $request,$salonid)
    {
        return Etkinlikler::with('katilimcilar')->where('salon_id',$salonid)->where('aktifmi',1)->where(function ($q) use($request){$q->where('etkinlik_adi','like','%'.$request->arama.'%');})->where('created_at','like',"%".date('Y-m-d')."%")->paginate(10);
    }
    public function kampanyalar(Request $request,$salonid)
    {
        return KampanyaYonetimi::with('kampanya_katilimcilari')->where('salon_id',$salonid)->where('aktifmi',1)
        ->where(function ($q) use($request){$q->where('paket_isim','like','%'.$request->arama.'%'); $q->orWhere('hizmet_adi','like','%'.$request->arama.'%');})->paginate(10);
    }
    public function paketler(Request $request,$salonid)
    {
          return  Paketler::whereHas('hizmetler.hizmet', function ($query) use ($request) {
            $query->where('hizmet_adi', 'like', '%'.$request->arama.'%');
        })->where('salon_id',$salonid)->where('paket_adi','like','%'.$request->arama.'%')->where('aktif',1)->paginate(10);
    } 
    public function paketdetay(Request $request,$paketid)
    {
        return Paketler::where('id',$paketid)->first();
    }
    public function etkinlikekleduzenle(Request $request,$salonid)
    {
        $etkinlik = "";
        if(isset($request->etkinlik_id))
            $etkinlik = Etkinlikler::where('id',$request->etkinlik_id)->first();
        else
            $etkinlik = new Etkinlikler();
        $etkinlik->etkinlik_adi = $request->etkinlik_adi;
        $etkinlik->tarih_saat = $request->etkinlik_tarih ." ".$request->etkinlik_saat;
        $etkinlik->fiyat = $request->etkinlik_fiyat;
        $etkinlik->salon_id = $salonid;
        $etkinlik->aktifmi=1;
        $etkinlik->mesaj=$request->etkinlik_mesaj;
        $etkinlik->save();
        EtkinlikKatilimcilari::where('etkinlik_id',$etkinlik->id)->delete();
        $gsm = array();
        $mesajlar=array();
        if (isset($request->secilen_katilimcilar)) {
            foreach(json_decode($request->secilen_katilimcilar,false) as $key=>$katilimci)
            {
                $yenikatilimci = new EtkinlikKatilimcilari();
                $yenikatilimci->etkinlik_id = $etkinlik->id;
                $yenikatilimci->user_id = $katilimci->id;
                $yenikatilimci->save();
                $toplumusteri = User::where('id',$katilimci->id)->first();
                $katilim_link = ''; 
                if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$etkinlik->salon_id)->value('musteri')==1 )
                 
                    $katilim_link = ' Katılım için : https://app.randevumcepte.com.tr/etkinlikkatilim/'.$etkinlik->id.'/'.$toplumusteri->id;
                if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$etkinlik->salon_id)->value('kara_liste')!=1)
                    array_push($mesajlar, array("to"=>$toplumusteri->cep_telefon,"message"=> $etkinlik->mesaj.$katilim_link));
            }
        }
           self::sms_gonder($request,$mesajlar,false,6,false,$salonid);
      
        return 'başarılı';
    }
    public function kampanyaekleduzenle(Request $request,$salonid)
    {
        $kampanya_yonetimi = "";
        if(isset($request->kampanya_id))
            $kampanya_yonetimi = KampanyaYonetimi::where('id',$request->kampanya_id)->first();
        else
            $kampanya_yonetimi = new KampanyaYonetimi();
        $kampanya_yonetimi->paket_isim = Paketler::where('id',$request->paket)->value('paket_adi');
        $kampanya_yonetimi->hizmet_adi = $request->kampanyapakethizmet;
        $kampanya_yonetimi->fiyat = $request->kampanyapaketfiyat;
        $kampanya_yonetimi->seans = $request->kampanyapaketseans;
        $kampanya_yonetimi->salon_id = $salonid;
        $kampanya_yonetimi->aktifmi=1;
        $kampanya_yonetimi->mesaj=$request->kampanya_sms;
        $kampanya_yonetimi->save();
        KampanyaKatilimcilari::where('kampanya_id',$kampanya_yonetimi->id)->delete();
        $gsm = array();
        $mesajlar=array();
        if (isset($request->secilen_katilimcilar)) {
            foreach(json_decode($request->secilen_katilimcilar,false) as $key=>$katilimci)
            {
                $yenikatilimci = new KampanyaKatilimcilari();
                $yenikatilimci->kampanya_id = $kampanya_yonetimi->id;
                $yenikatilimci->user_id = $katilimci->id;
                $yenikatilimci->save();
                $toplumusteri = User::where('id',$katilimci->id)->first();
                $katilim_link = ''; 
                if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$kampanya_yonetimi->salon_id)->value('musteri')==1 )
                 
                    $katilim_link = ' Katılım için : https://app.randevumcepte.com.tr/kampanyakatilim/'.$kampanya_yonetimi->id.'/'.$toplumusteri->id;
                if(MusteriPortfoy::where('user_id',$toplumusteri->id)->where('salon_id',$kampanya_yonetimi->salon_id)->value('kara_liste')!=1)
                    array_push($mesajlar, array("to"=>$toplumusteri->cep_telefon,"message"=> $kampanya_yonetimi->mesaj.$katilim_link));
            }
        }
        
        self::sms_gonder($request,$mesajlar,false,4,false,$salonid);
        return 'başarılı';
    }
    public function kampanyapasifet(Request $request)
    {
        $kampanya = KampanyaYonetimi::where('id',$request->kampanyaid)->first();
        $kampanya->aktifmi = 0;
        $kampanya->save();
    }
    public function urunpasifet(Request $request)
    {
        $urun = Urunler::where('id',$request->urunid)->first();
        $urun->aktif=false;
        $urun->save();
    }
    public function paketpasifete(Request $request){
        $paket = Paketler::where('id',$request->paketid)->first();
        $paket->aktif = false;
        $paket->save();
        
    }
    public function urunekleduzenle(Request $request,$salonid)
    {
        $urun="";
         
        if($request->urun_id == 0){
            $urun = new Urunler();
           
        }
        else{
            $urun = Urunler::where('id',$request->urun_id)->first();
            
        }
        $urun->urun_adi = $request->urun_adi;
        $urun->fiyat = $request->fiyat;
        $urun->barkod = $request->barkod;
        $urun->stok_adedi = $request->stok_adedi;
        $urun->dusuk_stok_siniri = $request->dusuk_stok_siniri;
        $urun->salon_id = $salonid;
        $urun->aktif=true;
        $urun->save();
    }
    public function smstaslaklari(Request $request,$salonid)
    {
        return SMSTaslaklari::where('salon_id',$salonid)->get();
    }
    public function sms_gonder(Request $request,$mesajlar,$geribildirimgonder,$tur,$dogrulama,$salonid)
    {
        $isletme = Salonlar::where('id',$salonid)->first();
        $sms_baslik = '';
        $sms_apikey = '';
        if($salonid!='')
        {
            
            $sms_baslik = $isletme->sms_baslik;
            $sms_apikey =  $isletme->sms_apikey; 
        }
        else
        {
            $sms_baslik = 'RANDVMCEPTE';
            $sms_apikey = 'LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5';
        }
        
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
                    $rapor->aciklama = $mesajlar[0]['message'];
                    $rapor->rapor_id = $decoded["response"]["message"]["id"];
                    $rapor->adet = $decoded["response"]["message"]["count"];
                    $rapor->kredi = $decoded["response"]["message"]["total_price"];
                    sleep(1);
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
    public function kampanyatekrarsmsgonder(Request $request)
    {
          $kampanyabeklenen= KampanyaYonetimi::where('id',$request->kampanyaid)->first();
          $mesajlar=array();
          $kampanyamesaj = $kampanyabeklenen->mesaj;
          
           
          foreach ($kampanyabeklenen->kampanya_katilimcilari as  $katilimci) {
            if($katilimci->durum===null)
            {
                $katilim_link = ''; 
                if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$kampanyabeklenen->salon_id)->value('musteri')==1)
                    $katilim_link = ' Katılım için : https://app.randevumcepte.com.tr/kampanyakatilim/'.$kampanyabeklenen->id.'/'.$katilimci->user_id;
                if(MusteriPortfoy::where('user_id',$katilimci->user_id)->where('salon_id',$kampanyabeklenen->salon_id)->value('kara_liste')!=1)
                    array_push($mesajlar, array("to"=>$katilimci->musteri->cep_telefon,"message"=> $kampanyabeklenen->mesaj.$katilim_link));
            }
            
          }
            $gonder= self::sms_gonder($request,$mesajlar,true,5,false,$kampanyabeklenen->salon_id);
          
            return array(
              "mesaj" => "SMS başarıyla gönderildi",
              "gonder"=>$gonder,
            );
    }
    public function arsivyukle(Request $request,$salonid)
    {
        $cevapladi = null;
        $cevapladi2 = null;
        $durum = null;
        $harici = false;
        $beklenen = false;
        if($request->durum == '1')
            $durum = true;
        elseif($request->durum == '0')
            $durum = false;
        if($request->cevapladi == '1')
            $cevapladi = true;
        elseif($request->cevapladi == '0')
            $cevapladi = false;
        if($request->cevapladi2 == '1')
            $cevapladi2 = true;
        elseif($request->cevapladi2 == '0')
            $cevapladi2 = false;
        if($request->cevapladi2 == 'null' &&$request->cevapladi == 'null' && $request->durum=="null" )
            $harici = true;
        if($request->cevapladi2 == 'b' &&$request->cevapladi == 'b' && $request->durum=="null" )
            $beklenen = true;
        if($harici)
        {
             return Arsiv::where(function($q) use($request){
                $q->whereHas('form',function($q2) use($request) {$q2->where('form_adi','like', '%' . $request->arama . '%');});
                $q->orWhereHas('musteri',function($q2) use($request){$q2->where('name','like', '%' . $request->arama . '%');});
             })->where('salon_id',$salonid)
         
        ->where(function($q) use($harici) {if($harici) $q->where('form_id',0);})
        ->orderBy('created_at','desc')
        ->paginate(9); 
                exit;
        }
        elseif($beklenen)
        {
              return Arsiv::where(function($q) use($request){
                $q->whereHas('form',function($q2) use($request) {$q2->where('form_adi','like', '%' . $request->arama . '%');});
                $q->orWhereHas('musteri',function($q2) use($request){$q2->where('name','like', '%' . $request->arama . '%');});
             })->where('salon_id',$salonid)
         
        ->where('form_id','!=',0)->where('durum',null)
        ->orderBy('created_at','desc')
        ->paginate(9); 
                exit;
        }
        elseif($durum==true)
        {
             return Arsiv::where(function($q) use($request){
                $q->whereHas('form',function($q2) use($request) {$q2->where('form_adi','like', '%' . $request->arama . '%');});
                $q->orWhereHas('musteri',function($q2) use($request){$q2->where('name','like', '%' . $request->arama . '%');});
             })->where('salon_id',$salonid)
         
        ->where('form_id','!=',0)->where('durum',true)
        ->orderBy('created_at','desc')
        ->paginate(9); 
                exit;
        }
        else
        {
              return Arsiv::where(function($q) use($request){
                $q->whereHas('form',function($q2) use($request) {$q2->where('form_adi','like', '%' . $request->arama . '%');});
                $q->orWhereHas('musteri',function($q2) use($request){$q2->where('name','like', '%' . $request->arama . '%');});
             })->where('salon_id',$salonid)
        ->where(function($q) use($durum) {if($durum !== null) $q->where('durum', $durum);})
        ->where(function($q) use($cevapladi) {if($cevapladi !== null) $q->where('cevapladi', $cevapladi);})
        ->where(function($q) use($cevapladi2) {if($cevapladi2 !== null) $q->where('cevapladi2', $cevapladi2);})
        ->where(function($q) use($harici) {if($harici) $q->where('form_id',0);})
        ->orderBy('created_at','desc')
        ->paginate(9);
        exit;
        }
        
        
    }
    public function seans_getir(Request $request,$salonid)
    {
        $result = array();
    $musteri_id="";
        $musteri_id=MusteriPortfoy::where('user_id',$request->user_id)->where('salon_id',$salonid)->value('id');
        $adisyonlar = Adisyonlar::where('salon_id',$salonid)->where('user_id',$request->musteri_id)->whereIn('user_id',User::where('name','like','%'.$request->arama.'%')->pluck('id')->toArray())->get();
        $paketler = AdisyonPaketler::whereIn('adisyon_id',$adisyonlar->pluck('id'))->paginate(10);
        foreach($paketler->items() as $paket){  
             $adisyon = Adisyonlar::where('id',$paket->adisyon_id)->first();
            array_push($result,array( 
                'adisyon' => $adisyonlar[0]->id,
                'musteri'=>$adisyon->musteri,
                'paket' => $paket->paket->paket_adi,
                'seanslar'=> AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->get(),
                 
            )); 
        } 
        return array(
            'data' => $result,
            'current_page'=> $paketler->currentPage(),
            'last_page'=>$paketler->lastPage()
        );
    }
    public function senetler(Request $request,$salonid)
    {
        if($request->durum=='Tümü')
        {
            return Senetler::whereHas('musteri',function($q) use($request){$q->where('name','like','%'.$request->arama.'%');})->whereHas('vadeler')->where('salon_id',$salonid)->paginate(10);
            exit();
        }
        if($request->durum=='Kapalı')
        {
            return Senetler::whereHas('musteri',function($q) use($request){$q->where('name','like','%'.$request->arama.'%');})->whereHas('vadeler')->whereHas('vadeler',function($q) use($request){$q->where('odendi','!=',1);},'=',0)->where('salon_id',$salonid)->paginate(10);
            exit();
        }
        if($request->durum=='Açık')
        {
            return Senetler::whereHas('musteri',function($q) use($request){$q->where('name','like','%'.$request->arama.'%');})->whereHas('vadeler')->whereHas('vadeler',function($q) use($request){$q->where('odendi',0);},'>',0)->where('salon_id',$salonid)->paginate(10);
            exit();
        } 
        if($request->durum=='Ödenmemiş')
        {
            return Senetler::whereHas('musteri',function($q) use($request){$q->where('name','like','%'.$request->arama.'%');})->whereHas('vadeler')->whereHas('vadeler',function($q) use($request){
                $q->where('odendi',0); $q->where('vade_tarih','<=', date('Y-m-d'));
            },'>',0)->where('salon_id',$salonid)->paginate(10);
            exit();
        } 
    }
   public function dogrulamakodukontrol(Request $request,$tur)
    {
        
        if($tur=='Senet'){
            $vade = SenetVadeleri::where('id',$request->vade_id)->first();
            $senet = Senetler::where('id',$vade->senet_id)->first();
            if(SalonSMSAyarlari::where('salon_id',$senet->salon_id)->where('ayar_id',16)->value('musteri'))
            {
               return true;
               exit;
            }
            else
            {
                return false;
                exit;
            } 
        }
           
    }
    public function dogrulamakodugonder(Request $request,$tur)
    {
        if($tur=='Senet'){
            $vade = SenetVadeleri::where('id',$request->vade_id)->first();
            $senet = Senetler::where('id',$vade->senet_id)->first();
            $random = str_shuffle('1234567890');
            $kod = substr($random, 0, 4);      
            $vade->dogrulama_kodu = $kod;
            $vade->save(); 
            $mesaj = array(
                    array("to"=>$senet->musteri->cep_telefon,"message"=>$senet->id. " nolu senedinizin ".date('d.m.Y', strtotime($vade->vade_tarih))." tarihli vadesinin ödemesi için doğrulama kodunuz : ".$kod),
            );  
            self::sms_gonder($request,$mesaj,false,1,true,$senet->salon_id);
               
           
        }
    }
    public function senetode(Request $request)
    {
        
        if($request->dogrulama_kodu=='' && self::dogrulamakodukontrol($request,'Senet'))
        {
                self::dogrulamakodugonder($request,'Senet');
                return json_encode(array('dogrulamavar'=>true));
                exit;
        }
        
            
        else
        {
            $vade = SenetVadeleri::where('id',$request->vade_id)->first();
            if($vade->dogrulama_kodu==$request->dogrulama_kodu)
            {
                $vade->odendi = true;
                $vade->odeme_yontemi_id = $request->odeme_yontemi;
                $vade->save();
              
                $tahsilat = new Tahsilatlar();
                $senet = Senetler::where('id',$vade->senet_id)->first();
                $tahsilat->user_id = $senet->user_id;
                $tahsilat->adisyon_id = $senet->adisyon_id;
                $tahsilat->tutar = $vade->tutar;
                $tahsilat->odeme_tarihi = $request->tarih;    
                $tahsilat->olusturan_id = Personeller::where('salon_id',$senet->salon_id)->where('yetkili_id',$request->user_id)->value('id');
                $tahsilat->salon_id = $senet->salon_id;
                $tahsilat->yapilan_odeme = $vade->tutar;
                $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;
                $tahsilat->notlar = $vade->senet_id.' nolu senedin '.date('d.m.Y', strtotime($vade->vade_tarih)).' tarihli vadesinin ödemesi';
                $tahsilat->save();
                return json_encode(array('dogrulamayanlis'=>false,'dogrulamavar'=>false,'odemebasarili'=>true));
                exit;
            }
            else
            {
                return json_encode(array('dogrulamayanlis'=>true,'dogrulamavar'=>false,'odemebasarili'=>false));
                exit;
            }
           
        }
       
         
    }
    public function taksitodemedogrulamakodugonder(Request $request)
    {
        $vade = TaksitVadeleri::where('id',$request->vade_id)->first();
        $taksitlitahsilat = TaksitliTahsilatlar::where('id',$vade->taksitli_tahsilat_id)->first();
        $random = str_shuffle('1234567890');
        $kod = substr($random, 0, 4);      
        $vade->dogrulama_kodu = $kod;
        $vade->save(); 
        $mesaj = array(
            array("to"=>$taksitlitahsilat->musteri->cep_telefon,"message"=>$taksitlitahsilat->id ." nolu taksitli ödemenizin ".date('d.m.Y', strtotime($vade->vade_tarih))." tarihli vadesinin ödemesi için doğrulama kodunuz : ".$kod),
        ); 
        self::sms_gonder($request,$mesaj,false,1,true,$taksitlitahsilat->salon_id);
        
    }
        public function tahsilatraporu(Request $request,$salonid)
        { 
                return Tahsilatlar::where('salon_id',$salonid)->where(function($q) use($request){
                    if($request->tarih1!==null && $request->tarih2!==null) {
                        $q->where('odeme_tarihi','>=',$request->tarih1);
                        $q->where('odeme_tarihi','<=',$request->tarih2);
                    }
                })->where(function($q) use($request){if($request->odemeyontemi!==null) $q->where('odeme_yontemi_id','=',$request->odemeyontemi);})->orderBy('odeme_tarihi','desc')->paginate(10); 
        }
        public function masrafraporu(Request $request,$salonid) 
        { 
                return Masraflar::where('salon_id',$salonid)->where(function($q) use($request)
                    {
                        if($request->tarih1!==null && $request->tarih2!==null) {
                            $q->where('tarih','>=',$request->tarih1);
                            $q->where('tarih','<=',$request->tarih2);
                        }
                    })->where(function($q) use($request){if($request->odemeyontemi!==null) $q->where('odeme_yontemi_id','=',$request->odemeyontemi);})->whereHas('harcayan',function($q) use($request){$q->where('personel_adi','like','%'.$request->harcayan.'%'); })->orderBy('tarih','desc')->paginate(10); 
        }
        public function kasaraporu(Request $request,$salonid)
        {
            return array(
                'toplamgelir'=>Tahsilatlar::where('salon_id',$salonid)->where(function($q) use($request)
                    {
                        if($request->tarih1!==null && $request->tarih2!==null){
                          $q->where('odeme_tarihi','>=',$request->tarih1);
                          $q->where('odeme_tarihi','<=',$request->tarih2);  
                        } 
                    })->where(function($q) use($request){if($request->odemeyontemi!==null) $q->where('odeme_yontemi_id','=',$request->odemeyontemi);})->sum('tutar'),
                'toplamgider'=>Masraflar::where('salon_id',$salonid)->where(function($q) use($request)
                {
                    if($request->tarih1!==null && $request->tarih2!==null)
                    {
                        $q->where('tarih','>=',$request->tarih1);
                        $q->where('tarih','<=',$request->tarih2);
                    } })->where(function($q) use($request){if($request->odemeyontemi!==null) $q->where('odeme_yontemi_id','=',$request->odemeyontemi);})->sum('tutar')
            );
        }
        public function masrafekleduzenle(Request $request,$salonid)
        {
            $masraf = "";
            
            if($request->id!='') 
                $masraf = Masraflar::where('id',$request->id)->first(); 
            else
                $masraf = new Masraflar();
            $masraf->salon_id = $salonid;
            $masraf->masraf_kategori_id = $request->masraf_kategorisi;
            $masraf->tarih = $request->tarih;
            $masraf->odeme_yontemi_id = $request->masraf_odeme_yontemi;
            $masraf->harcayan_id = $request->harcayan;
            $masraf->tutar= str_replace(['.',','],['','.'],$request->masraf_tutari);
            $masraf->aciklama = $request->masraf_aciklama;
            $masraf->notlar = $request->masraf_notlari;
            $masraf->save(); 
        }
        public function personeller(Request $request, $salonid)
        {
            return Personeller::where('salon_id',$salonid)->get();
        }
        public function masrafkategorileri()
        {
            return MasrafKategorisi::all();
        }
        public function musteri_liste_getir(Request $request,$salonid)
    {
         
         $musteriler = '';
        if($request->durum == 3)
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
          
            ->select('users.id as id','users.name as name','users.cep_telefon as cep_telefon','users.email as email','users.ozel_notlar as ozel_notlar','users.dogum_tarihi as dogum_tarihi','users.cinsiyet as cinsiyet','musteri_portfoy.musteri_tipi as musteri_tipi','users.hemofili_hastaligi_var as hemofili_hastaligi_var','users.seker_hastaligi_var as seker_hastaligi_var','users.hamile as hamile','users.alerji_var as alerji_var','users.alkol_alimi_yapildi as alkol_alimi_yapildi','users.regl_doneminde as regl_doneminde','users.deri_yumusak_doku_hastaligi_var as deri_yumusak_doku_hastaligi_var','users.surekli_kullanilan_ilac_Var as surekli_kullanilan_ilac_Var','users.surekli_kullanilan_ilac_aciklama as surekli_kullanilan_ilac_aciklama','users.kemoterapi_goruyor as kemoterapi_goruyor','users.daha_once_uygulama_yaptirildi as daha_once_uygulama_yaptirildi','users.daha_once_yaptirilan_uygulama_aciklama as daha_once_yaptirilan_uygulama_aciklama','users.ek_saglik_sorunu as ek_saglik_sorunu','users.cilt_tipi as cilt_tipi','users.yakin_zamanda_ameliyat_gecirildi as yakin_zamanda_ameliyat_gecirildi','users.ek_saglik_sorunu as ek_saglik_sorunu',
                DB::raw('DATE_FORMAT(users.created_at,"%d.%m.%Y") as kayit_tarihi'),
                DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id and randevular.salon_id = salonlar.id) as randevu_sayisi'),
                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),
                     DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)),2,"tr_TR"),"</button>")  as odenen'),
                DB::raw('CONCAT("<div class=\"dropdown\">
                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                                      href=\"#\"
                                      role=\"button\"
                                      data-toggle=\"dropdown\"
                                    ><i class=\"dw dw-more\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                                     
                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>
                                           <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"
                      data-target=\"#musteri-bilgi-duzenle-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                        <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>
                                    </div>
                                    </div>") AS islemler'),
            )->where('musteri_portfoy.salon_id',$salonid)
            ->where('musteri_portfoy.aktif',true)
            ->where('salonlar.id',$salonid)
            ->where('users.name','like','%'.$request->arama.'%')
            ->groupBy('users.id')->orderBy('users.id','desc')->paginate(9);
        }
        if($request->durum == 0){
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
            ->select('users.id as id','users.name as name','users.cep_telefon as cep_telefon','users.email as email','users.ozel_notlar as ozel_notlar','users.dogum_tarihi as dogum_tarihi','users.cinsiyet as cinsiyet','musteri_portfoy.musteri_tipi as musteri_tipi','users.hemofili_hastaligi_var as hemofili_hastaligi_var','users.seker_hastaligi_var as seker_hastaligi_var','users.hamile as hamile','users.alerji_var as alerji_var','users.alkol_alimi_yapildi as alkol_alimi_yapildi','users.regl_doneminde as regl_doneminde','users.deri_yumusak_doku_hastaligi_var as deri_yumusak_doku_hastaligi_var','users.surekli_kullanilan_ilac_Var as surekli_kullanilan_ilac_Var','users.surekli_kullanilan_ilac_aciklama as surekli_kullanilan_ilac_aciklama','users.kemoterapi_goruyor as kemoterapi_goruyor','users.daha_once_uygulama_yaptirildi as daha_once_uygulama_yaptirildi','users.daha_once_yaptirilan_uygulama_aciklama as daha_once_yaptirilan_uygulama_aciklama','users.ek_saglik_sorunu as ek_saglik_sorunu','users.cilt_tipi as cilt_tipi','users.yakin_zamanda_ameliyat_gecirildi as yakin_zamanda_ameliyat_gecirildi','users.ek_saglik_sorunu as ek_saglik_sorunu',
                DB::raw('DATE_FORMAT(users.created_at,"%d.%m.%Y") as kayit_tarihi'),
                 DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id) as randevu_sayisi'),
                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),
    DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)),2,"tr_TR"),"</button>")  as odenen'),
                DB::raw('CONCAT("<div class=\"dropdown\">
                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                                      href=\"#\"
                                      role=\"button\"
                                      data-toggle=\"dropdown\"
                                    ><i class=\"dw dw-more\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                                     
                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>
                                           <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"
                  data-target=\"#musteri-bilgi-duzenle-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                    <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>
                                    </div>
                                    </div>") AS islemler'),
            )->where('musteri_portfoy.salon_id',$salonid) 
             ->where('musteri_portfoy.aktif',true)
             ->where('users.name','like','%'.$request->arama.'%')
            ->having(DB::raw('(SELECT  COUNT(*) FROM adisyonlar where adisyonlar.user_id = users.id )') ,0)  
            ->groupBy('users.id')->orderBy('users.id','desc')->paginate(9);
          
        }
           
        if($request->durum == 1){
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
            ->select('users.id as id','users.name as name','users.cep_telefon as cep_telefon','users.email as email','users.ozel_notlar as ozel_notlar','users.dogum_tarihi as dogum_tarihi','users.cinsiyet as cinsiyet','musteri_portfoy.musteri_tipi as musteri_tipi','users.hemofili_hastaligi_var as hemofili_hastaligi_var','users.seker_hastaligi_var as seker_hastaligi_var','users.hamile as hamile','users.alerji_var as alerji_var','users.alkol_alimi_yapildi as alkol_alimi_yapildi','users.regl_doneminde as regl_doneminde','users.deri_yumusak_doku_hastaligi_var as deri_yumusak_doku_hastaligi_var','users.surekli_kullanilan_ilac_Var as surekli_kullanilan_ilac_Var','users.surekli_kullanilan_ilac_aciklama as surekli_kullanilan_ilac_aciklama','users.kemoterapi_goruyor as kemoterapi_goruyor','users.daha_once_uygulama_yaptirildi as daha_once_uygulama_yaptirildi','users.daha_once_yaptirilan_uygulama_aciklama as daha_once_yaptirilan_uygulama_aciklama','users.ek_saglik_sorunu as ek_saglik_sorunu','users.cilt_tipi as cilt_tipi','users.yakin_zamanda_ameliyat_gecirildi as yakin_zamanda_ameliyat_gecirildi','users.ek_saglik_sorunu as ek_saglik_sorunu',
                DB::raw('DATE_FORMAT(users.created_at,"%d.%m.%Y") as kayit_tarihi'),
                 DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id) as randevu_sayisi'),
                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),
    DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)),2,"tr_TR"),"</button>")  as odenen'),
                DB::raw('CONCAT("<div class=\"dropdown\">
                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                                      href=\"#\"
                                      role=\"button\"
                                      data-toggle=\"dropdown\"
                                    ><i class=\"dw dw-more\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                                     
                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>
                                            <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"
                  data-target=\"#musteri-bilgi-duzenle-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                   <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>
                                    </div>
                                    </div>") AS islemler'),
            )->where('musteri_portfoy.salon_id',$salonid) 
             ->where('musteri_portfoy.aktif',true)
             ->where('users.name','like','%'.$request->arama.'%')
            ->orHaving(DB::raw('(SELECT  COUNT(*) FROM adisyonlar where adisyonlar.user_id = users.id )') ,'>', 0)  
            ->orHaving(DB::raw('(SELECT  DATE_ADD(adisyonlar.created_at , INTERVAL 3 MONTH) FROM adisyonlar where adisyonlar.user_id = users.id order by adisyonlar.id desc limit 1)'),'>=',date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))->groupBy('users.id')->orderBy('users.id','desc')->paginate(9);
           
        }
        if($request->durum == 2){
                
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
           ->select('users.id as id','users.name as name','users.cep_telefon as cep_telefon','users.email as email','users.ozel_notlar as ozel_notlar','users.dogum_tarihi as dogum_tarihi','users.cinsiyet as cinsiyet','musteri_portfoy.musteri_tipi as musteri_tipi','users.hemofili_hastaligi_var as hemofili_hastaligi_var','users.seker_hastaligi_var as seker_hastaligi_var','users.hamile as hamile','users.alerji_var as alerji_var','users.alkol_alimi_yapildi as alkol_alimi_yapildi','users.regl_doneminde as regl_doneminde','users.deri_yumusak_doku_hastaligi_var as deri_yumusak_doku_hastaligi_var','users.surekli_kullanilan_ilac_Var as surekli_kullanilan_ilac_Var','users.surekli_kullanilan_ilac_aciklama as surekli_kullanilan_ilac_aciklama','users.kemoterapi_goruyor as kemoterapi_goruyor','users.daha_once_uygulama_yaptirildi as daha_once_uygulama_yaptirildi','users.daha_once_yaptirilan_uygulama_aciklama as daha_once_yaptirilan_uygulama_aciklama','users.ek_saglik_sorunu as ek_saglik_sorunu','users.cilt_tipi as cilt_tipi','users.yakin_zamanda_ameliyat_gecirildi as yakin_zamanda_ameliyat_gecirildi','users.ek_saglik_sorunu as ek_saglik_sorunu',
            DB::raw('DATE_FORMAT(users.created_at,"%d.%m.%Y") as kayit_tarihi'),
                  DB::raw('(SELECT COUNT(*) from randevular where randevular.user_id = users.id) as randevu_sayisi'),
                DB::raw("DATE_FORMAT((SELECT randevular.tarih FROM randevular WHERE randevular.user_id = users.id order by randevular.id desc limit 1),'%d.%m.%Y')  as son_randevu_tarihi"),
    DB::raw('CONCAT("<button class=\"btn btn-success btn-block\"  style=\"line-height:5px\">",FORMAT(COALESCE((SELECT COALESCE(SUM(tahsilat_hizmetler.tutar), 0) from tahsilat_hizmetler where tahsilat_hizmetler.adisyon_hizmet_id = adisyon_hizmetler.id) + 
                                 (SELECT COALESCE(SUM(tahsilat_urunler.tutar), 0) from tahsilat_urunler where tahsilat_urunler.adisyon_urun_id = adisyon_urunler.id) +
                                 (SELECT COALESCE(SUM(tahsilat_paketler.tutar), 0) from tahsilat_paketler where tahsilat_paketler.adisyon_paket_id = adisyon_paketler.id)),2,"tr_TR"),"</button>")  as odenen'),
                DB::raw('CONCAT("<div class=\"dropdown\">
                            <a class=\"btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle\"
                                      href=\"#\"
                                      role=\"button\"
                                      data-toggle=\"dropdown\"
                                    ><i class=\"dw dw-more\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right dropdown-menu-icon-list\">
                                     
                                        <a class=\"dropdown-item\" href=\"/isletmeyonetim/musteridetay/",users.id,"\"><i class=\"fa fa-eye\"></i> Detaylı Bilgi</a>
                                            <a class=\"dropdown-item\" href=\"#\" data-toggle=\"modal\"
                  data-target=\"#musteri-bilgi-duzenle-modal\" name=\"musteri_duzenle\" data-value=\"",users.id,"\"><i class=\"fa fa-edit\"></i> Düzenle</a>
                    <a class=\"dropdown-item\" href=\"#\" name=\"musteri_sil\" data-value=\"",musteri_portfoy.id,"\"><i class=\"fa fa-minus\"></i> Sil</a>
                                    </div>
                                    </div>") AS islemler'),
            )->where('musteri_portfoy.salon_id',$salonid) 
             ->where('musteri_portfoy.aktif',true)
             ->where('users.name','like','%'.$request->arama.'%')
            ->having(DB::raw('(SELECT  COUNT(*) FROM adisyonlar where adisyonlar.user_id = users.id )') ,'>=', 3)  
            ->having(DB::raw('(SELECT  DATE_ADD(adisyonlar.created_at , INTERVAL 3 MONTH) FROM adisyonlar where adisyonlar.user_id = users.id order by adisyonlar.id desc limit 1)'),'<',date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))->groupBy('users.id')->orderBy('users.id','desc')->paginate(9);
           
        }
        return $musteriler;
         
    }
        public function hizmetler(Request $request,$salonid)
        {
            return SalonHizmetler::where('salon_id',$salonid)->get();
        }
         public function randevuhizmetler(Request $request )
        {
            return RandevuHizmetler::all();
        }
        public function randevudetay(Request $request)
        {
            return Randevular::where('id',$request->randevuid)->first();
        }
            public function odalar(Request $request,$salonid )
        {
            return Odalar::where('salon_id',$salonid)->get();
        }
         public function cihazlar(Request $request,$salonid )
        {
            return Cihazlar::where('salon_id',$salonid)->get();
        }
      public function randevuekleguncelle(Request $request){
        $musteriid = $request->user_id;
        $tarihler = ""; 
        $randevu_tarihleri = array();
        array_push($randevu_tarihleri,$request->randevu_tarihi);
        $eklenecek_tarih = $request->randevu_tarihi;
        if(isset($request->tekrarlayan)){
            for($t=1;$t<$request->tekrar_sayisi;$t++)
            {
                $eklenecek_tarih = date('Y-m-d', strtotime($request->tekrar_sikligi, strtotime($eklenecek_tarih)));
                array_push($randevu_tarihleri,$eklenecek_tarih);
            } 
        }
        $cakisma_varmi = '';
        if($request->cakisanrandevuekle == '')
            $cakisma_varmi = self::cakisan_randevu_kontrol($request,$randevu_tarihleri);
        if($cakisma_varmi != '' && $request->cakisanrandevuekle!='1')
        {
            return array('cakismavar'=>"1", "cakisanunsurlar" => $cakisma_varmi);
            exit();
        }
        elseif(Salonlar::where('id',$request->salonid)->value('demo_hesabi') == 1 && Randevular::where('salon_id',$request->salonid)->count()>20)
        {
            return array('cakismavar'=>"0", 'eklenemez'=>'Deneme hesabında en fazla 20 randevu eklenebilir. Devam etmek için lütfen "Üyelik" bölümünden paket üyeliği başlatınız.');
            exit();
        }
        else
        {
            if($cakisma_varmi == '' || $request->cakisanrandevuekle=="1")
            {
                $mesajlar = array();
                foreach($randevu_tarihleri as $tarihler){
                    $yenirandevu = '';
                    $eskitarihsaat = '';
                    $guncelleme = false;
                    if($request->randevu_id != ''){
                        $guncelleme = true;
                        $yenirandevu = Randevular::where('id',$request->randevu_id)->first();
                        $eskitarihsaat = date('d.m.y H:i',strtotime($yenirandevu->tarih.' '.$yenirandevu->saat));
                        RandevuHizmetler::where('randevu_id',$yenirandevu->id)->delete();
                    }
                    else
                        $yenirandevu = new Randevular();
                    $yenirandevu->user_id = $request->user_id;
                    $yenirandevu->salon_id = $request->salonid;
                   
                    $yenirandevu->tarih = $tarihler;
                    $yenirandevu->saat = $request->randevu_saati;
                    $yenirandevu->personel_notu = $request->notlar;
                    $yenirandevu->salon= true;
                    $yenirandevu->olusturan_personel_id = $request->olusturan;
                    
                    $totalsure = 0;
                    foreach($request->hizmetler as $key => $value)
                    {
                        $totalsure += $value["sure_dk"];
                    }
                    $yenirandevu->saat_bitis = date("H:i", strtotime('+'.$totalsure.' minutes', strtotime($request->randevu_saati)));
                    $yenirandevu->durum = 1;
                    $yenirandevu->save(); 
                    $hizmet_id = "";
                    $yenisaatbaslangic = $request->randevu_saati;
                    
                    $hizmet_sureleri_okunan = array();
                    foreach ($request->hizmetler as $key2 => $value) {
                        array_push($hizmet_sureleri_okunan,$value["sure_dk"]); 
                        $yenirandevuhizmetpersonel = new RandevuHizmetler(); 
                        $yenirandevuhizmetpersonel->randevu_id = $yenirandevu->id; 
                        $yenirandevuhizmetpersonel->hizmet_id = $value["hizmet_id"]; 
                        $yenirandevuhizmetpersonel->cihaz_id = $value["cihaz_id"]=="null" ? null : $value["cihaz_id"];  
                        $yenirandevuhizmetpersonel->personel_id =  $value["personel_id"]=="null" ? null: $value["personel_id"]; 
                       $yenirandevuhizmetpersonel->oda_id = $value["oda_id"]=="null" ? null: $value["oda_id"];
                        $yenirandevuhizmetpersonel->sure_dk = $value["sure_dk"]; 
                        $yenirandevuhizmetpersonel->fiyat = $value["fiyat"];
                        $birsonraki = $key2+1;
                        if($key2 == 0){
                             $yenirandevuhizmetpersonel->saat = $request->randevu_saati;
                             $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($request->randevu_saati)));
                             if(!$value["birlestir"] != "1")
                                $yenisaatbaslangic = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($request->randevu_saati)));
 
                        } 
                        else{
                            $yenirandevuhizmetpersonel->saat = $yenisaatbaslangic;
                            $yenirandevuhizmetpersonel->saat_bitis = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($yenisaatbaslangic)));
                            if(!$value["birlestir"] != "1")
                                $yenisaatbaslangic = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($yenisaatbaslangic))); 
                                 
                        }
                        
                        $yenirandevuhizmetpersonel->save();
                         
                        foreach($request->yardimcipersoneller as $_yardimcipersonel)
                        {
                            if($yardimcipersonel["randevuhizmetid"] = $yenirandevuhizmetpersonel->hizmet_id)
                            {
                                $yardimci_personel = new RandevuHizmetler();
                                $yardimci_personel->randevu_id =  $yenirandevu->id;
                                $yardimci_personel->hizmet_id = $yenirandevuhizmetpersonel->hizmet_id;
                                $yardimci_personel->cihaz_id = $yenirandevuhizmetpersonel->cihaz_id;                
                                $yardimci_personel->personel_id = $_yardimcipersonel["yardimcipersonel"]["id"];
                                $yardimci_personel->oda_id = $yenirandevuhizmetpersonel->oda_id;
                                $yardimci_personel->sure_dk = $yenirandevuhizmetpersonel->sure_dk;
                                $yardimci_personel->fiyat = $yenirandevuhizmetpersonel->fiyat;
                                $yardimci_personel->saat = $yenirandevuhizmetpersonel->saat;
                                $yardimci_personel->saat_bitis = $yenirandevuhizmetpersonel->saat_bitis;
                                $yardimci_personel->yardimci_personel = true;
                                $yardimci_personel->save();
                            } 
                           
                        } 
                    }
                    $isletme = Salonlar::where('id',$yenirandevu->salon_id)->first();
                    $musteribilgi = User::where('id',$yenirandevu->user_id)->first();
                    $gsm = $musteribilgi->cep_telefon;
                    $cumleyeek = "oluşturulmuştur";
                    if($guncelleme)
                        $cumleyeek = "güncellenmiştir";
                    if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$yenirandevu->salon_id)->value('musteri')==1)
                    {
                        
                        array_push($mesajlar, array("to"=>$gsm,"message"=>$isletme->salon_adi . " tarafından ".date('d.m.Y',strtotime($request->randevu_tarihi)) .'-'.$request->randevu_saati .' olarak randevunuz '.$cumleyeek.'. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0'.$isletme->telefon_1)); 
                        
                       
                    }
                   
                    foreach($yenirandevu->hizmetler as $hizmet)
                    {
                        if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$yenirandevu->salon_id)->value('personel')==1)
                        {
                            $mesaj = $yenirandevu->users->name." isimli müşterinin ". date('d.m.Y',strtotime($yenirandevu->tarih)) ." - ". date('H:i',strtotime($hizmet->saat)) ." ".$hizmet->hizmetler->hizmet_adi." randevusu ".IsletmeYetkilileri::where('id',$request->olusturan)->value('name')." tarafından ".$cumleyeek.".";
                            $yetkiliid=Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');
                            array_push($mesajlar, array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj)); 
                            
                        }
                        self::bildirimekle($request,$yenirandevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, IsletmeYetkilileri::where('id',$request->olusturan)->value('profil_resim'),$yenirandevu->id);
                        $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',Personeller::where('id',$hizmet->personel_id)->value('yetkili_id'))->pluck('bildirim_id')->toArray();
                        self::bildirimgonder($bildirimkimlikleri,"Yeni Randevu",$mesaj,$yenirandevu->salon_id);
                    } 
                    
                }
                
               /* $isletme = Salonlar::where('id',$request->salonid)->first();
                $musteribilgi = User::where('id',$musteriid)->first();
                $gsm = $musteribilgi->cep_telefon;
                 
                if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$yenirandevu->salon_id)->value('musteri')==1)
                {
                    $mesajlar = array(
                    array("to"=>$gsm,"message"=>$isletme->salon_adi . " tarafından ".date('d.m.Y',strtotime($request->randevu_tarihi)) .'-'.$request->randevu_saati .' olarak randevunuz oluşturulmuştur. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0'.$isletme->telefon_1),
                    );
                    self::sms_gonder($request,$mesajlar,false,1,false);
                }
                if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$yenirandevu->salon_id)->value('personel')==1)
                {
                    foreach($yenirandevu->hizmetler as $hizmet)
                    {
                        $mesaj = $yenirandevu->users->name." isimli müşterinin ". date('d.m.Y',strtotime($yenirandevu->tarih)) ." - ". date('H:i',strtotime($hizmet->saat)) ." ".$hizmet->hizmetler->hizmet_adi." randevusu ".Auth::user()->name." tarafından oluşturulmuştur.";
                        $yetkiliid=Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');
                        $mesajlar = array(
                        array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj),
                        );
                        self::sms_gonder($request,$mesajlar,false,1,false);
                        self::bildirimekle($request,$yenirandevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, Auth::user()->profil_resim,$yenirandevu->id);
                            $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 
                        self::bildirimgonder($bildirimkimlikleri,"Yeni Randevu",$mesaj,$yenirandevu->salon_id);
                    }
                }  */
                if(count($mesajlar)>0)
                    self::sms_gonder($request,$mesajlar,false,1,false,$request->salonid);
                return array('cakismavar'=>"0","cakisanunsurlar"=>"Başarılı");
                exit();
            }
        }
    } 
         
    
      
    public function calismasaatleri(Request $request)
    {
        return SalonCalismaSaatleri::where('salon_id',$request->salonid)->first();
    }
    public function formlar(Request $request)
    {
        return FormTaslaklari::all();
    }
     public function arsivformekleguncelle(Request $request){
        $form = '';
        if($request->id != '')
            $form = Arsiv::where('id',$request->id)->first();
        else
            $form=new Arsiv();
        $random = str_shuffle('1234567890');
        $kod = substr($random, 0, 4);      
        $form->dogrulama_kodu = $kod;
        $form->user_id=$request->user_id;
        $form->form_id=$request->form_id;
        $form->personel_id=$request->personel_id;
        $form->cevapladi=false;
        $form->cevapladi2=false;
        $form->salon_id = $request->salon_id;
        $form->form_olusturan=Personeller::where('salon_id',$request->salon_id)->where('yetkili_id',$request->olusturan)->value('id');
        
        $form->save();
        $gsm = array();
        $mesajlar=array();
        $mesajlar2=array();
        if ($request->user_id) {
            
            $user = User::where('id',$request->user_id)->first();
            $user->dogum_tarihi = $request->dogum_tarihi;
            $user->cep_telefon = $request->cep_telefon;
            $user->tc_kimlik_no = $request->tc_kimlik_no;
            $user->save();
            $katilim_link = ' Formu doldurmak için : https://app.randevumcepte.com.tr/musteriformdoldurma/'.$form->id.'/'.$form->user_id.' Onay Kodu:'.$kod;
            if(MusteriPortfoy::where('user_id',$request->user_id)->where('salon_id',$form->salon_id)->value('kara_liste')!=1)
                    array_push($mesajlar, array("to"=>$request->cep_telefon,"message"=>$katilim_link));
        } 
        $gonder=self::sms_gonder($request,$mesajlar,true,6,true,$request->salon_id);
         if ($request->personel_id) {
            
            $katilim_link2 = ' İmza atmak için : https://app.randevumcepte.com.tr/personelformdoldurma/'.$form->id.'/'.$request->personel_id;
                    array_push($mesajlar2, array("to"=>$request->personel_cep,"message"=>$katilim_link2));
        }
        $gonder2=self::sms_gonder($request,$mesajlar2,true,6,true,$request->salon_id);  
        return 'Başarılı'; 
    }
    public function haricibelgeekle(Request $request)
    {
        $form =new Arsiv();
        $form->user_id=$request->user_id;
        $form->harici_belge=$request->form_baslik;
        $form->form_id=0;
        $form->personel_id=$request->personel_id;
        $form->salon_id = $request->salon_id;
        $form->form_olusturan=Personeller::where('salon_id',$request->salon_id)->where('yetkili_id',$request->olusturan)->value('id');
      
        if(isset($_FILES["file"]["name"])){
          
              $dosya  = $request->hariciformyukle;
              $kaynak = $_FILES["file"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["file"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["file"]["name"]);
                        $uzanti = explode(".", $_FILES["file"]["name"]);
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
        return 'basarili';
    }
     public function arsiviptal(Request $request)
    {
        $arsiv = Arsiv::where('id',$request->id)->first;
        $arsiv->durum = 0;
        $arsiv->save();
    }
    public function cdrrapor(Request $request)
    {
        $tarih1 = '';
        if($request->tarih1=='')
            $tarih1='1970-01-01';
        else
            $tarih1 = $request->tarih1;
        $authToken = '';
        if(Salonlar::where('id',$request->salon_id)->value('santral_token_expires') < date('Y-m-d H:i:s'))
            $authToken = self::santral_token_al($request->salon_id);
        else
            $authToken = Salonlar::where('id',$request->salon_id)->value('santral_token');
         
        $endpoint = "http://34.45.69.65/admin/api/api/gql";
        $qry = 'query{
          fetchAllCdrs (
             first : 99999999 
            startDate: "'.$tarih1.'"
            endDate: "'.$request->tarih2.'"
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
        $results_per_page = 10;
        if (isset($request->page) && is_numeric($request->page)) {
            $current_page = (int) $request->page;
        } else {
            $current_page = 1;
        }
        $start_index = ($current_page - 1) * $results_per_page;
        if($result['data']['fetchAllCdrs']['totalCount']>0)
        {
            foreach($result['data']['fetchAllCdrs']['cdrs'] as $cdr)
            {
                if(SabitNumaralar::where('salon_id',$request->salon_id)->value('numara'))
                {
                    if($cdr['src']==SabitNumaralar::where('salon_id',$request->salon_id)->value('numara') || $cdr['did']==SabitNumaralar::where('salon_id',$request->salon_id)->value('numara'))
                {  
                        $tel_kaynak = str_replace('+','',$cdr['src']);
                        $tel_hedef = str_replace('+','',$cdr['dst']);
                        $tel_kaynak = str_replace('90','',$tel_kaynak);
                        $tel_hedef = str_replace('90','',$tel_hedef);
                        $tel_kaynak = ltrim($tel_kaynak, '0'); 
                        $tel_hedef = ltrim($tel_hedef, '0'); 
                        $musteri_tel = '';
                        $musteri_adi = '';
                        $avatar = 'https://app.randevumcepte.com.tr/public/isletmeyonetim_assets/img/avatar.png';
                        $durum = '';
                        $gorusmeyi_yapan='';
                        $cevapsiz_arama_var = true;
                        $musteri_var = User::join('musteri_portfoy','musteri_portfoy.user_id','=','users.id')->select('users.name as ad_soyad','users.cep_telefon as telefon')->where('musteri_portfoy.salon_id',$request->salon_id)->where(function($q) use($tel_kaynak,$tel_hedef){ 
                            $q->where('users.cep_telefon',$tel_kaynak);
                            $q->orWhere('users.cep_telefon',$tel_hedef); })->first();
                        if($musteri_var){
                            $musteri_tel= $musteri_var->telefon;
                            $musteri_adi = $musteri_var->ad_soyad;
                            $avatar = $musteri_var->profil_resim !==null ? $musteri_var->profil_resim : 'https://app.randevumcepte.com.tr/public/isletmeyonetim_assets/img/avatar.png';
                        } 
                        else
                        { 
                                $musteri_tel = $tel_kaynak; 
                        }
                        if($cdr['disposition']=='NO ANSWER' && str_contains($cdr['recordingfile'],'in-')){
                            $durum = '0'; //CEVAPSIZ
                            $gorusmeyi_yapan =  Personeller::where('dahili_no',$cdr['cnum'])->orWhere('dahili_no',$cdr['dst'])->value('personel_adi');
                            $cevapsiz_arama++;
                            
                        }
                        else{
                            $cevapsiz_arama_var = false;
                            if(SabitNumaralar::where('salon_id',$request->salon_id)->value('numara')==$cdr['src']){
                                if($cdr['disposition']=='NO ANSWER'){
                                    $durum = '1'; //GİDEN ULAŞILAMADI 
                                    $basarisiz_arama++;
                                    $cevapsiz_arama_var = true;
                                }
                                else
                                {
                                    $durum = '2';  //GİDEN
                                }
                                
                                $gorusmeyi_yapan = Personeller::where('dahili_no',$cdr['cnum'])->value('personel_adi');
                                $giden_arama++;
                            }
                            else{   
                                if($cdr['lastapp']=='VoiceMail' || str_contains($cdr['dst'],'vmu') )
                                {
                                    $cevapsiz_arama_var = true;
                                    $durum = '4'; //SESLİ MESAJ
                                    $dst = ltrim($cdr['dst'],'vmu');
                                    $gorusmeyi_yapan = Personeller::where('dahili_no',$dst)->value('personel_adi'); 
                                    $sesli_mesaj++;
                                }
                                else{ 
                                    $durum = '3'; //GELEN
                                    $gorusmeyi_yapan = Personeller::where('dahili_no',$cdr['dst'])->value('personel_adi'); 
                                    $gelen_arama++;
                                }
                                
                            }
                        }
                        $arama_butonu = "0".$musteri_tel;
                        $ses_kaydi = '';
                        $tarih_dir =explode("-",$cdr['calldate']);
                        $tarih_son = explode(' ',$tarih_dir[2]);
                        if(!$cevapsiz_arama_var)
                            $ses_kaydi = 'https://voicerecords.randevumcepte.com.tr/monitor/'.$tarih_dir[0].'/'.$tarih_dir[1].'/'.$tarih_son[0].'/'.$cdr['recordingfile'];
                        
                        array_push($rapor,array(
                            'tarih' => date('Y-m-d',strtotime($cdr['calldate'])),
                            'saat' => date('H:i',strtotime('+3 hours', strtotime( $cdr['calldate']))),
                            'musteri' => $musteri_adi,
                            'gorusmeyiyapan' => $gorusmeyi_yapan,
                            'telefon'=> $musteri_tel,
                            'durum' => $durum,
                            'seskaydi' => $ses_kaydi,
                            'avatar' => $avatar,
                        ));
                     
                }
                }
                
                     
                 
                
               
                
            }
        }
        $total_pages = ceil(count($rapor) / $results_per_page);
       usort($rapor, function($a, $b) {
    $dateTimeA = strtotime($a['tarih'].' '.$a['saat']);
    $dateTimeB = strtotime($b['tarih'].' '.$b['saat']);
    
    // Debugging: Print the date and time being compared
   
    
    return $dateTimeB - $dateTimeA;
});//array_slice($rapor, $start_index, $results_per_page);
            
        return $rapor; 
        
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
    public function sehirler(){
        return Iller::all();
    }
    public function ongorusmenedeni(Request $request,$salonid)
    {
        return array(
            'urunler'=>Urunler::where('salon_id',$salonid)->get(),
            'paketler'=>Paketler::where('salon_id',$salonid)->get()
        );
    }
    public function ongorusmeekleguncelle(Request $request)
    {
        $ongorusme = "";
        
        $user = '';
        if($request->on_gorusme_id != "")
        {
            $ongorusme = OnGorusmeler::where('id',$request->on_gorusme_id)->first(); 
            $eskirandevu = Randevular::where('on_gorusme_id',$request->on_gorusme_id)->first();
            if($eskirandevu){
                foreach($eskirandevu->hizmetler as $hizmet){
                    $hizmet->delete();
                    RandevuHizmetYardimciPersonel::where('randevu_hizmet_id',$hizmet->id)->delete();
                }
                $eskirandevu->delete();
            }
        }
        else
        {
            $ongorusme = new OnGorusmeler(); 
        }
        if($request->musteri_id != 0){
            $user = User::where('id',$request->musteri_id)->first();
            $ongorusme->user_id = $request->musteri_id;
        }
        else
        {
            
            $portfoy = '';
            if(User::where('cep_telefon',$request->telefon)->count() > 0){
                $user = User::where('cep_telefon',$request->telefon)->first(); 
            }
            else
                $user = new User();
            $user->name = $request->ad_soyad;
            $user->cep_telefon = $request->telefon;
            $user->cinsiyet = $request->cinsiyet;
            
            $user->il_id = $request->il_id;
            $user->meslek = $request->meslek;
            $user->email = $request->email;
            $user->save();
            $portfoy = '';
            if(MusteriPortfoy::where('user_id',$user->id)->where('salon_id',$request->salonid)->count()>0)
                $portfoy = MusteriPortfoy::where('user_id',$user->id)->where('salon_id',$request->salonid)->first();
            else
            {
                $portfoy = new MusteriPortfoy();
                $portfoy->musteri_tipi = $request->musteri_tipi;
                $portfoy->aktif = 1;
                $portfoy->kara_liste = 0;
            } 
            $portfoy->user_id = $user->id;
            $portfoy->save();
            $ongorusme->user_id = $user->id;
        }
       
        
        $cakisma_varmi = '';
        if($request->cakisanrandevuekle == '')
            $cakisma_varmi = self::cakisan_randevu_kontrol($request,array($request->randevu_tarihi));
        if($cakisma_varmi != '' && $request->cakisanrandevuekle=='')
        {
            return array('cakismavar'=>"1", "cakisanunsurlar" => $cakisma_varmi);
            exit();
        }
        elseif(Salonlar::where('id',$request->salonid)->value('demo_hesabi') == 1 && Randevular::where('salon_id',$request->salonid)->count()>20)
        {
            return 'Deneme hesabında en fazla 20 randevu eklenebilir. Devam etmek için lütfen "Üyelik" bölümünden paket üyeliği başlatınız.';
            exit();
        }
        else
        {
            if($cakisma_varmi == '' || $request->cakisanrandevuekle== "1")
            {
                $ongorusme->salon_id = $request->salonid;
                $ongorusme->ad_soyad = $request->ad_soyad;
                $ongorusme->cep_telefon = $request->telefon;
                $ongorusme->email =$request->email;
                $ongorusme->cinsiyet = $request->cinsiyet;
                $ongorusme->adres = $request->adres;
                $ongorusme->aciklama=$request->aciklama;
                $ongorusme->il_id =$request->il_id;
                $ongorusme->musteri_tipi = $request->musteri_tipi;
                $ongorusme->meslek = $request->meslek;
                $ongorusme->on_gorusme_saati = $request->randevu_saati;
                if($request->urun_id != ''){ 
                    $ongorusme->urun_id = $request->urun_id; 
                }
                 if($request->paket_id != ''){ 
                    $ongorusme->paket_id = $request->paket_id;
                }
                $ongorusme->hatirlatma_tarihi = $request->randevu_tarihi;
                $ongorusme->personel_id = $request->gorusmeyi_yapan;
                $ongorusme->save();
                $randevu = new Randevular();
                $randevu->on_gorusme_id = $ongorusme->id;
                $randevu->user_id = $ongorusme->user_id;
                $randevu->salon_id = $ongorusme->salon_id;
                 
                $randevu->tarih = $request->randevu_tarihi;
                $randevu->saat = $request->randevu_saati;
                $randevu->salon = true;
                $randevu->sms_hatirlatma = true; 
                $randevu->durum = 1;
                $randevu->olusturan_personel_id = Personeller::where('salon_id',$request->salonid)->where('yetkili_id',$request->olusturan)->value('id');
            
                $randevu->save();
                $ongorusmehizmeti = new RandevuHizmetler();
                $ongorusmehizmeti->hizmet_id = 1;
                $ongorusmehizmeti->personel_id =  $request->gorusmeyi_yapan;
                $ongorusmehizmeti->saat = $request->ongorusme_saati;
                $ongorusmehizmeti->saat_bitis = date('H:i:s',strtotime('+1 hours',strtotime($request->randevu_saati)));
                $ongorusmehizmeti->randevu_id = $randevu->id;
                $ongorusmehizmeti->save();
           
                $gsm = $user->cep_telefon;
                $mesajlar = array();
                        if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$ongorusme->salon_id)->value('musteri')==1)
                        {
                            array_push( $mesajlar ,   array("to"=>$gsm,"message"=>$ongorusme->salon->salon_adi . " tarafından ".date('d.m.Y',strtotime($request->randevu_tarihi)) .'-'.date('H:i', strtotime($request->randevu_saati)) .' olarak ön görüşme randevunuz düzenlenmiştir. Randevunuza 15 dk önce gelmenizi rica ederiz. Detaylı bilgi için bize ulaşın. 0'.$ongorusme->salon->telefon_1) 
                          
                            );
                           
                        }
                        if(SalonSMSAyarlari::where('ayar_id',12)->where('salon_id',$ongorusme->salon_id)->value('personel')==1)
                        {
                            foreach($randevu->hizmetler as $hizmet)
                            {
                                $mesaj = '';
                                if($ongorusme->paket_id !== null)
                                    $mesaj = $ongorusme->musteri->name." isimli müşterinin ". date('d.m.Y',strtotime($request->randevu_tarihi)) ." - ". date('H:i',strtotime($request->randevu_saati)) ." ".$ongorusme->paket->paket_adi." için ön görüşme randevusu randevusu ".IsletmeYetkilileri::where('id',$request->olusturan)->value('name')." tarafından düzenlenmiştir.";
                                if($ongorusme->urun_id !== null)
                                    $mesaj = $ongorusme->musteri->name." isimli müşterinin ". date('d.m.Y',strtotime($request->randevu_tarihi)) ." - ". date('H:i',strtotime($request->randevu_saati)) ." ".$ongorusme->urun->urun_adi." için ön görüşme randevusu randevusu ".IsletmeYetkilileri::where('id',$request->olusturan)->value('name')." tarafından düzenlenmiştir.";
                                $yetkiliid=Personeller::where('id',$request->gorusmeyi_yapan)->value('yetkili_id');
                                array_push( $mesajlar,
                                        array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj) 
                                ); 
                                self::bildirimekle($request,$request->salonid,$mesaj,"#",$request->gorusmeyi_yapan,null, IsletmeYetkilileri::where('id',$request->olusturan)->value('profil_resim'),$randevu->id);
                                $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$request->personel_id)->pluck('bildirim_id')->toArray();
                                self::bildirimgonder($bildirimkimlikleri,"Randevu Düzenleme",$mesaj,$randevu->salon_id);
                            }
                        } 
                if(count($mesajlar) > 0)
                    self::sms_gonder($request,$mesajlar,false,1,false,$request->salonid); 
            }
             return array('cakismavar'=>"0", "cakisanunsurlar" => "Başarılı");
             exit();
        }
        
       
    }
    public function cakisan_randevu_kontrol(Request $request,$randevu_tarihleri)
    {
        
        $cakisan_unsurlar = '';
        $isletme_calisma_saatleri = array();
        foreach($randevu_tarihleri as $tarihler){
            
            $yenisaatbaslangic = $request->randevu_saati;
            
            $totalsure = 0;
            foreach($request->hizmetler as $key => $value)
            {
                $totalsure += $value["sure_dk"];
            }
            $hizmet_sureleri_okunan = array();
            foreach ($request->hizmetler as $key2 => $value) {
                array_push($hizmet_sureleri_okunan,$value["sure_dk"]);
                
                
                $birsonraki = $key2+1;
                $saat_baslangic='';
                $saat_bitis = '';
                if($key2 == 0){
                     
                     $saat_baslangic = $request->randevu_saati;
                     $saat_bitis = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($request->randevu_saati)));
                     if(!$value["birlestir"] != "1")
                        $yenisaatbaslangic = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($request->randevu_saati)));
                }
                else{
                    $saat_baslangic = $yenisaatbaslangic;
                    $saat_bitis = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($yenisaatbaslangic)));
                    if(!$value["birlestir"] != "1")
                        $yenisaatbaslangic = date("H:i", strtotime('+'.$value["sure_dk"].' minutes', strtotime($yenisaatbaslangic)));
                  
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
                ->where('randevular.tarih',$tarihler)->where('randevu_hizmetler.saat',$saat_baslangic)->where(
                    function($q) use ($request,$value)
                    {       
                        $q->where('randevu_hizmetler.personel_id', $value["personel_id"]);
                        $q->orWhere('randevu_hizmetler.cihaz_id',$value["cihaz_id"]);
                        $q->orWhere('randevu_hizmetler.oda_id',$value["oda_id"]);
                        
                    })->where('randevular.durum',1)->where('randevular.salon_id',$request->salonid)->get();
                foreach($onaylirandevular as $onaylirandevu)
                {   
                    if(self::saatAraliginda($onaylirandevu->saat,$onaylirandevu->saat_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)). ' '.date('H:i',strtotime($saat_baslangic)).' : '.$onaylirandevu->personel_adi.' '.$onaylirandevu->hizmet_adi. ' randevusu.';
                }
                
                $personel_calisma_saati_baslangic = PersonelCalismaSaatleri::where('personel_id',$value["personel_id"])->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');
                $personel_calisma_saati_bitis = PersonelCalismaSaatleri::where('personel_id',$value["personel_id"])->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');
                $personel_mola_saati_baslangic = PersonelMolaSaatleri::where('personel_id',$value["personel_id"])->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');
                $personel_mola_saati_bitis = PersonelMolaSaatleri::where('personel_id',$value["personel_id"])->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');
                $cihaz_calisma_saati_baslangic = CihazCalismaSaatleri::where('cihaz_id',$value["cihaz_id"])->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');
                $cihaz_calisma_saati_bitis = CihazCalismaSaatleri::where('cihaz_id',$value["cihaz_id"])->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');
                $cihaz_mola_saati_baslangic = CihazMolaSaatleri::where('cihaz_id',$value["cihaz_id"])->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');
                $cihaz_mola_saati_bitis = CihazMolaSaatleri::where('cihaz_id',$value["cihaz_id"])->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');
                $salon_calisma_saati_baslangic = SalonCalismaSaatleri::where('salon_id',$request->salonid)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');
                $salon_calisma_saati_bitis = SalonCalismaSaatleri::where('salon_id',$request->salonid)->where('calisiyor',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');
                $salon_mola_saati_baslangic= SalonMolaSaatleri::where('salon_id',$request->salonid)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('baslangic_saati');
                $salon_mola_saati_bitis = SalonMolaSaatleri::where('salon_id',$request->salonid)->where('mola_var',1)->where('haftanin_gunu',self::haftanin_gunu($tarihler))->value('bitis_saati');
                if($salon_calisma_saati_baslangic!=''&& $salon_calisma_saati_bitis!='') {
                    if(!self::saatAraliginda($salon_calisma_saati_baslangic,$salon_calisma_saati_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)).' tarihinde işletmenizin çalışma saatlerinin dışına denk geliyor. ';   
                }
                if($salon_mola_saati_baslangic != '' && $salon_mola_saati_bitis != '')
                {
                    if(self::saatAraliginda($salon_mola_saati_baslangic,$salon_mola_saati_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)).' tarihinde işletmenizin mola saatine denk geliyor.';
                }
                if($personel_calisma_saati_baslangic != '' && $personel_calisma_saati_bitis != '')
                {
                    if(!self::saatAraliginda($personel_calisma_saati_baslangic,$personel_calisma_saati_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Personeller::where('id',$value["personel_id"])->value('personel_adi').' isimli personelin çalışma saatinin dışına denk geliyor.';  
                }
                if($personel_mola_saati_baslangic != '' && $personel_mola_saati_bitis != '')
                {
                    if(self::saatAraliginda($personel_mola_saati_baslangic,$personel_mola_saati_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Personeller::where('id',$value["personel_id"])->value('personel_adi').' isimli personelin mola saatine denk geliyor.';
                }
                if($cihaz_calisma_saati_baslangic != '' && $cihaz_calisma_saati_bitis != '')
                {
                    if(!self::saatAraliginda($cihaz_calisma_saati_baslangic,$cihaz_calisma_saati_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Cihazlar::where('id',$value["cihaz_id"])->value('cihaz_adi').' isimli cihazın çalışma saatinin dışına denk geliyor.';  
                }
                if($cihaz_mola_saati_baslangic != '' && $cihaz_mola_saati_bitis != '')
                {
                    if(self::saatAraliginda($cihaz_mola_saati_baslangic,$cihaz_mola_saati_bitis,$saat_baslangic))
                        $cakisan_unsurlar .= '\n'.date('d.m.Y',strtotime($tarihler)).' tarihinde '.Cihazlar::where('id',$value["cihaz_id"])->value('cihaz_adi').' isimli cihazın mola saatine denk geliyor.'; 
                }
               
               
            }
        }
        return $cakisan_unsurlar;
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
   public function yetkilibilgiguncelle(Request $request){
        $user = IsletmeYetkilileri::where('id',$request->yetkili_id)->first();
        $user->name = $request->name;
        $user->email = $request->email;
        if($request->password != "")
            $user->password = Hash::make($request->password);
        $user->gsm1 = $request->gsm1;
        $user->unvan = $request->unvan;
        $user->sms_gonderimi = $request->sms_gonderimi;
        $user->cinsiyet = $request->cinsiyet;
        $user->save();
        return $user;
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
    public function profilresimyukle(Request $request) {
        $user = IsletmeYetkilileri::where('id', $request->yetkili_id)->first();
        if ($request->hasFile('folderPath')) {
        $file = $request->file('folderPath');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $folderPath = '/home/webfirma/randevumcepteweb3/public/profil_resimleri/';
        $file->move($folderPath, $filename);
        $user->profil_resim = "/public/profil_resimleri/" . $filename;
        $user->save();
        return response()->json(['profilresmi' => $user->profil_resim]);
        }    
        else {
            return response()->json(['error' => 'File not found in request'], 400);
        }
    }
    public function satislar(Request $request) 
    {
 
        $personel_id = '';
        
         
        if(DB::table('model_has_roles')->join('roles','model_has_roles.role_id','=','roles.id')->where('salon_id',$request->sube)->where('model_id',$request->userid)->value('roles.id')==5)
            $personel_id = Personeller::where('salon_id',$request->salonid)->where('yetkili_id',$request->user_id)->value('id');
        return   self::adisyon_yukle($request, $request->adisyonturu, '',$request->tarih1, $request->tarih2,$request->musteriid,$request->personel_id,$request->salonid);
    }
    public function randevugeldiisaretle(Request $request)
    {
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $dogrulama_kodu_ayari = SalonSMSAyarlari::where('salon_id',$randevu->salon_id)->where('ayar_id',16)->value('musteri');
        if($dogrulama_kodu_ayari && $request->dogrulama_kodu == '' )
        {
            self::dogrulama_kodu_gonder($request);
             return array(
                'hatali' => '2',
                'mesaj' => 'Lütfen müşteri/danışanın telefon numarasına gönderilen doğrulama kodunu giriniz!'
            );
             exit;
        }
        if(($dogrulama_kodu_ayari && $randevu->dogrulama == $request->dogrulama_kodu) || !$dogrulama_kodu_ayari)
        {
            $randevu->randevuya_geldi = true;
            $randevu->save();
            if(AdisyonPaketSeanslar::where('randevu_id',$request->randevuid)->count()!=0)
                AdisyonPaketSeanslar::where('randevu_id',$request->randevuid)->update(['geldi'=>true]);
            return array('hatali'=>'0','mesaj'=>'Başarılı');
            exit;
        }
        else
        {
            
            return array(
                'hatali' => '1',
                'mesaj' => 'Doğrulama kodu hatalı, lütfen yeniden deneyiniz'
            );
            exit;
        }
      
           
    }
    public function randevuyagelmedi(Request $request)
    {
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $randevu->randevuya_geldi = false;
        $randevu->save();
        if(AdisyonPaketSeanslar::where('randevu_id',$request->randevuid)->count() != 0)
                $seans = AdisyonPaketSeanslar::where('randevu_id',$request->randevuid)->update(['geldi'=>false]);
        return 'Başarılı';
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
                    $mesaj = $randevu->users->name .' isimli müşterinin yarın '.date('H:i',strtotime($hizmet->saat)).' saatli '.$hizmet->hizmetler->hizmet_adi.' randevusu '.IsletmeYetkilileri::where('id',$randevu->olusturan_personel_id)->value('name').' tarafından reddedilmiştir.';
                   $yetkiliid = Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');
                   array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj));
                  
                   self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, IsletmeYetkilileri::where('id',$randevu->olusturan_personel_id)->value('profil_resim'),$randevu->id);
                   $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',Personeller::where('id',$hizmet->personel_id)->value('yetkili_id'))->pluck('bildirim_id')->toArray(); 
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
                    $mesaj = $randevu->users->name .' isimli müşterinin yarın '.date('H:i',strtotime($hizmet->saat)).' saatli '.$hizmet->hizmetler->hizmet_adi.' randevusu '.IsletmeYetkilileri::where('id',$randevu->olusturan_personel_id)->value('name').' tarafından iptal edilmiştir.';
                    array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj));
                   
                    self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, IsletmeYetkilileri::where('id',$randevu->olusturan_personel_id)->value('profil_resim'),$randevu->id);
                    $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 
                    self::bildirimgonder($bildirimkimlikleri,"Randevu İptali",$mesaj,$randevu->salon_id);
                }
               
            }
        }
        if(count($mesajlar)>0)
            self::sms_gonder($request,$mesajlar,false,1,false,$randevu->salon_id);
       
        return 'Başarılı';
        
        
            
    }
    public function dogrulama_kodu_gonder(Request $request)
    {
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $random = str_shuffle('1234567890');
        $kod = substr($random, 0, 4);
        $randevu->dogrulama = $kod;
        $randevu->save();
        $mesaj = array(
            array("to"=>$randevu->users->cep_telefon,"message"=>"Doğrulama kodunuz : ".$kod),
        );
        self::sms_gonder($request,$mesaj,false,1,true,$randevu->salon_id);
         
    }
    public function musteri_danisan_turunu_getir(Request $request)
    {
        $tur = 0;
        if(Adisyonlar::where('user_id',$request->musteri_id)->where('salon_id',$request->salon_id)->count()>3 
                              && 
                           date('Y-m-d H:i:s', strtotime('+90 days',strtotime(Adisyonlar::where('user_id',$request->musteri_id)->where('salon_id',$request->salon_id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))
            $tur = 2;
        elseif(Adisyonlar::where('user_id',$request->musteri_id)->where('salon_id',$request->salon_id)->count()==0)
            $tur = 0;
        else
            $tur = 1;
        return $tur;
    }
    public function tum_alacaklar(Request $request)
    {
        $adisyon_hizmetler = array();
        $adisyon_urunler = array();
        $adisyon_paketler = array();
        foreach(Adisyonlar::where('salon_id',$request->salon_id)->where('user_id',$request->musteri_id)->get() as $adisyon)
        {
            foreach($adisyon->hizmetler as $key=>$hizmet)
            {
                if((($hizmet->fiyat -TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')  - $hizmet->indirim_tutari > 0 || $hizmet->hediye)&&  $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null))
                {
                    array_push($adisyon_hizmetler,array(
                        "id"=>$hizmet->id,
                      "hizmet_id"=> $hizmet->hizmet_id,
                      "islem_tarihi"=> $hizmet->islem_tarihi,
                      "islem_saati"=>$hizmet->islem_saati,
                      "sure"=>$hizmet->sure,
                      "fiyat"=>$hizmet->fiyat - TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,
                      "geldi"=>$hizmet->geldi,
                      "personel_id"=>$hizmet->personel_id,
                      "cihaz_id"=>$hizmet->cihaz_id,
                      "oda_id"=>$hizmet->oda_id,
                      "dogrulama_kodu"=>$hizmet->dogrulama_kodu,
                      "taksitli_tahsilat_id"=>$hizmet->taksitli_tahsilat_id,
                      "senet_id"=>$hizmet->senet_id,
                      "indirim_tutari"=>$hizmet->indirim_tutari,
                      "hediye"=>$hizmet->hediye,
                      "adisyon_id"=>$hizmet->adisyon_id,
                      "oda"=>$hizmet->oda,
                      "personel"=>$hizmet->personel,
                      "cihaz"=>$hizmet->cihaz,
                      "hizmet"=>$hizmet->hizmet,
                    ));
                }
                
            }
            foreach($adisyon->urunler as $key=>$urun){
                if((($urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari>0 || $urun->hediye)&&  $urun->senet_id === null && $urun->taksitli_tahsilat_id === null) )
                {
                    array_push($adisyon_urunler,array(
                        "id"=>$urun->id,
                          "adisyon_id"=>$urun->adisyon_id,
                          "urun_id"=>$urun->urun_id,
                          "adet"=>$urun->adet,
                          "fiyat"=>$urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,
                          "personel_id"=>$urun->personel_id,
                          "taksitli_tahsilat_id"=>$urun->taksitli_tahsilat_id,
                          "senet_id"=>$urun->senet_id,
                          "indirim_tutari"=>$urun->indirim_tutari,
                          "hediye"=>$urun->hediye,
                          "aciklama"=>$urun->aciklama,
                          "personel"=> $urun->personel,
                          "urun"=>$urun->urun,
                    ));     
                }
               
            }
            foreach($adisyon->paketler as $key=>$paket){
                if((($paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari > 0 || $paket->hediye)&&  $paket->senet_id === null && $paket->taksitli_tahsilat_id === null)  )
                {
                    array_push($adisyon_paketler,array(
                        "id"=>$paket->id,
                         "adisyon_id"=>$paket->adisyon_id,
                          "paket_id"=>$paket->paket_id,
                          "seans_araligi"=>$paket->seans_araligi,
                          "fiyat"=>$paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,
                          "personel_id"=>$paket->personel_id,
                          "taksitli_tahsilat_id"=>$paket->taksitli_tahsilat_id,
                          "senet_id"=>$paket->senet_id,
                          "indirim_tutari"=>$paket->indirim_tutari,
                          "hediye"=>$paket->hediye,
                          "seanslar"=>$paket->seanslar,
                          "personel"=>$paket->personel,
                          "urun"=>$paket->urun,
                          "baslangic_tarihi"=> $paket->baslangic_tarihi,
                          "paket"=> $paket->paket,
                    ));           
                }
            }
        }  
        return array(
            'senet'=>Senetler::where('senetler.salon_id',$request->salon_id)->where('senetler.user_id',$request->musteri_id)->get(),
            'taksit'=>TaksitliTahsilatlar::where('taksitli_tahsilatlar.salon_id',$request->salon_id)->where('taksitli_tahsilatlar.user_id',$request->musteri_id)->get(),
            'adisyon_hizmet'=>$adisyon_hizmetler,
            'adisyon_urun'=>$adisyon_urunler,
            'adisyon_paket'=>$adisyon_paketler,
        );
    }
     public function tahsilatekle(Request $request){
        
        $tahsilat = new Tahsilatlar();
      
        $tahsilat->adisyon_id = $request->adisyon_id;
        $tahsilat->tutar = str_replace('.','',$request->indirimli_toplam_tahsilat_tutari);
        $tahsilat->user_id = $request->ad_soyad;
        $tahsilat->odeme_tarihi = $request->tahsilat_tarihi;    
        $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',$request->olusturan)->value('id');
        $tahsilat->salon_id = $request->sube;
        $tahsilat->yapilan_odeme = str_replace('.','',$request->indirimli_toplam_tahsilat_tutari);
        $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;
        $tahsilat->notlar = $request->tahsilat_notlari;
        $tahsilat->save();
        if(isset($request->adisyon_hizmet_id))
        {
            foreach($request->adisyon_hizmet_id as $key=>$hizmet_id)
            {        
                     
                         
                    $odeme = new TahsilatHizmetler();
                    $odeme->adisyon_hizmet_id = $hizmet_id;
                    $odeme->tahsilat_id = $tahsilat->id;
                    $hizmet_tahsilat_tutar = 0;
                    
                    $hizmet_tahsilat_tutar = $request->adisyon_hizmetleri[$key]["fiyat"];
                    $odeme->tutar = (str_replace(['.',','],['','.'],$hizmet_tahsilat_tutar)/str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);
                    $odeme->save();
               
                
            }
         
        }
        if(isset($request->adisyon_urun_id))
        {
            foreach($request->adisyon_urun_id as $key2=>$urun_id)
            {
                
                    $odeme = new TahsilatUrunler();
                    $odeme->adisyon_urun_id = $urun_id;
                    $odeme->tahsilat_id = $tahsilat->id;
                    $urun_tahsilat_tutar = 0;
                    $urun_tahsilat_tutar = $request->adisyon_urunleri[$key]["fiyat"];
                    $odeme->tutar = (str_replace(['.',','],['','.'],$urun_tahsilat_tutar)/str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);
                    $odeme->save();
                
                 
            }
        }
        if(isset($request->adisyon_paket_id))
        {
            foreach($request->adisyon_paket_id as $key3=>$paket_id)
            {
                 
                
                    $odeme = new TahsilatPaketler();
                    $odeme->adisyon_paket_id = $paket_id;
                    $odeme->tahsilat_id = $tahsilat->id;
                    $paket_tahsilat_tutar = 0;
                   
                    $paket_tahsilat_tutar =$request->adison_paketleri[$key3]["fiyat"];
                    $odeme->tutar = (str_replace(['.',','],['','.'],$paket_tahsilat_tutar)/str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);
                    $odeme->save();
                
                
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
        //taksitsiz tahsilat bölümü 
        if($alacak != 0)
        {
            
        
            $alacak_kaydi = new Alacaklar();
            $alacak_kaydi->salon_id = $request->sube;
            $alacak_kaydi->adisyon_id = $request->adisyon_id;
            $alacak_kaydi->tutar = $alacak;
            $alacak_kaydi->aciklama = $request->tahsilat_notlari;
            $alacak_kaydi->planlanan_odeme_tarihi = $request->planlanan_alacak_tarihi;
            $alacak_kaydi->olusturan_id = Auth::user()->id;
            $alacak_kaydi->salon_id = $request->sube;
            $alacak_kaydi->user_id = $request->ad_soyad;
            $alacak_kaydi->save(); 
        }
        
        return "Başarılı";
            
    }
    public function taksitekleguncelle(Request $request)
    {
         
        if(isset($request->senet_vadeleri))
            foreach($request->senet_vadeleri as $senetvadesi)
            {
                Alacaklar::where('senet_vade_id',$senetvadesi["id"])->delete();
                $vade = SenetVadeleri::where('id',$senetvadesi["id"])->first();
                $vade->odendi = true;
                $vade->odeme_yontemi_id = $request->odeme_yontemi;
                $vade->save();
            }
        if(isset($request->taksit_vade_id))
            foreach($request->taksit_vadeleri as $taksitvadesi){
                Alacaklar::where('taksit_vade_id',$taksitvadesi["id"])->delete();
                $vade = TaksitVadeleri::where('id',$taksitvadesi["id"])->first();
                $vade->odendi = true;
                $vade->odeme_yontemi_id = $request->odeme_yontemi;
                $vade->save();
            }
        $taksitlitahsilat = '';
        if(is_numeric($request->taksitli_tahsilat_id))
            $taksitlitahsilat = TaksitliTahsilatlar::where('id',$request->taksitli_tahsilat_id)->first();
        else
            $taksitlitahsilat = new TaksitliTahsilatlar();
        $musteri = User::where('id',$request->ad_soyad)->first();
        
        
        $taksitlitahsilat->user_id = $request->ad_soyad;
        
        $taksitlitahsilat->vade_sayisi = $request->vade;
        $taksitlitahsilat->salon_id = $request->sube;
        $taksitlitahsilat->olusturan_id = $request->olusturan;
        
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
            $alacak->olusturan_id = $request->olusturan;
             
            $alacak->user_id = $musteri->id;
            $alacak->save();
        }
        $hizmet_kalem_sayisi = isset($request->adisyon_hizmet_id) ? AdisyonHizmetler::whereIn('id',$request->adisyon_hizmet_id)->where('indirim_tutari',null)->count() : 0;
        $urun_kalem_sayisi = isset($request->adisyon_urun_id) ? AdisyonUrunler::whereIn('id',$request->adisyon_urun_id)->where('indirim_tutari',null)->count() : 0;
        $paket_kalem_sayisi = isset($request->adisyon_paket_id) ? AdisyonPaketler::whereIn('id',$request->adisyon_paket_id)->where('indirim_tutari',null)->count() : 0;
          $kalem_sayisi = $hizmet_kalem_sayisi+$urun_kalem_sayisi+$paket_kalem_sayisi;
       $kalem_basina_indirim_tutari = round((str_replace(['.',','],['','.'],$request->musteri_indirimi))/ $kalem_sayisi,2);
        
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
        if(isset($request->indirimli_toplam_tahsilat_tutari)&&str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari) > 0)
        {
            $tahsilat = new Tahsilatlar();
            
            $tahsilat->user_id = $request->ad_soyad;
            $tahsilat->tutar = str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);
            $tahsilat->odeme_tarihi = $request->tahsilat_tarihi;    
            $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',$request->olusturan)->value('id');
            $tahsilat->salon_id = $request->sube;
            $tahsilat->yapilan_odeme = str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari);
            $tahsilat->odeme_yontemi_id = $request->odeme_yontemi;
            $tahsilat->notlar = $request->tahsilat_notlari;
            $tahsilat->save();
            
            foreach($request->adisyon_hizmetleri as $key=>$hizmetler)
                {      
                     
                        $odeme = new TahsilatHizmetler();
                        $odeme->adisyon_hizmet_id = $hizmet_id;
                        $odeme->tahsilat_id = $tahsilat->id;
                        $odeme->tutar = ((str_replace(['.',','],['','.'],$hizmetler["fiyat"])-$kalem_basina_indirim_tutari)/str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 
                        $odeme->aciklama = (str_replace(['.',','],['','.'],$hizmetler["fiyat"])."/".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))."*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 
                        $odeme->save();  
                    
                  
                }
             
            
           
            foreach($request->adisyon_urunleri as $key2=>$urunler)
                {
                     
                        $odeme = new TahsilatUrunler();
                        $odeme->adisyon_urun_id = $urun_id;
                        $odeme->tahsilat_id = $tahsilat->id;
                        $odeme->tutar = ((str_replace(['.',','],['','.'],$urunler["fiyat"])-$kalem_basina_indirim_tutari)/str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 
                        $odeme->aciklama = (str_replace(['.',','],['','.'],$urunler["fiyat"])."/".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))."*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 
                        $odeme->save(); 
                    
                }
            
            foreach($request->adisyon_paketleri as $key3=>$paketler)
                { 
                     
                        $odeme = new TahsilatPaketler();
                        $odeme->adisyon_paket_id = $paket_id;
                        $odeme->tahsilat_id = $tahsilat->id;
                        $odeme->tutar = ((str_replace(['.',','],['','.'],$paketler["fiyat"])-$kalem_basina_indirim_tutari)/str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))*str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 
                        $odeme->aciklama = (str_replace(['.',','],['','.'],$paketler["fiyat"])."/".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari))."*".str_replace(['.',','],['','.'],$request->indirimli_toplam_tahsilat_tutari); 
                        $odeme->save(); 
                     
                }
            
        }
        /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format($request->senet_tutar,2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senediniz oluşturulmuştur. Detaylı bilgi için bize ulaşın. 0".$senet->salon->telefon_1 )),false,1,false);
        
        $yetkili_liste = self::yetkili_telefonlari($request);
        foreach($yetkili_liste as $_yetkili)
        {
            self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::user()->name .' tarafından '.date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format($request->senet_tutar,2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senet oluşturulmuştur.")),false,1,false);
        }
        */
        return 'başarılı';
        
         
    }
   public function adisyonhizmetekle(Request $request)
    {
        $adisyon_id = '';
        $adisyon_hizmet = '';
        // Debugging output
        error_log('Received adisyon_id: ' . $request->adisyon_id);
        if(isset($request->adisyon_id) && $request->adisyon_id !== '') {
            $adisyon_id = $request->adisyon_id;
        } else {
            $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id, $request->sube, 'Hizmet Satışı', date('Y-m-d'), IsletmeYetkilileri::where('id', $request->olusturan)->first());
        }
       if(isset($request->adisyon_hizmet_id) && $request->adisyon_hizmet_id !== '') 
            $adisyon_hizmet = AdisyonHizmetler::where('id',$request->adisyon_hizmet_id)->first();
        else
            $adisyon_hizmet = new AdisyonHizmetler();
        $adisyon_hizmet->adisyon_id = $adisyon_id;
        $adisyon_hizmet->hizmet_id = $request->adisyonhizmetleriyeni;
        $adisyon_hizmet->islem_tarihi = date('Y-m-d', strtotime($request->islemtarihiyeni));
        $adisyon_hizmet->islem_saati = date('H:i:s', strtotime($request->islemsaatiyeni));
        $adisyon_hizmet->sure = $request->adisyonhizmetsuresi;
        $adisyon_hizmet->fiyat = $request->adisyonhizmetfiyati;
        $adisyon_hizmet->personel_id = $request->adisyonhizmetpersonelleriyeni;
        $adisyon_hizmet->geldi = true;
        $adisyon_hizmet->save();
        return AdisyonHizmetler::where('id', $adisyon_hizmet->id)->first();
    }
      public function adisyonurunekle(Request $request){
        $adisyon_id = '';
        $adisyon_urun = '';
        if(isset($request->adisyon_id) && $request->adisyon_id !== ""){
            $adisyon_id = $request->adisyon_id;
        }
        else
        {
            $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id,$request->sube,'Ürün Satışı',$request->urun_satis_tarihi,IsletmeYetkilileri::where('id', $request->olusturan)->first());
        }
  
       if(isset($request->adisyon_urun_id) && $request->adisyon_urun_id !== ""){ 
            $adisyon_urun = AdisyonUrunler::where('id',$request->adisyon_urun_id)->first();
        }
        else
            $adisyon_urun = new AdisyonUrunler();
        $adisyon_urun->islem_tarihi = $request->urun_satis_tarihi;
        $adisyon_urun->adisyon_id= $adisyon_id;
        $adisyon_urun->urun_id = $request->urunyeni;
        $adisyon_urun->personel_id = $request->urun_satici;
        $adisyon_urun->adet = $request->urun_adedi;
        $adisyon_urun->fiyat = $request->urun_fiyati;
        $adisyon_urun->save();
        $urun = Urunler::where('id',$request->urunyeni)->first();
        $urun->stok_adedi -= $request->urun_adedi;
        $urun->save();
           
        return AdisyonUrunler::where('id', $adisyon_urun->id)->first();
 
        
      
        
    }
    public function adisyonpaketekle(Request $request){
        $adisyon_id = '';
        $adisyon_paket_id = '';
        if(isset($request->adisyon_id) && $request->adisyon_id != ""){
            $adisyon_id = $request->adisyon_id;
            
        }
        else
            $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id,$request->sube,'Paket Satışı',$request->paket_satis_tarihi,IsletmeYetkilileri::where('id', $request->olusturan)->first());
       
             
        $paket = Paketler::where('id',$request->paketid)->first(); 
        $adisyon_paket_id = self::paketsatisiekleguncelle($adisyon_id,$request->adisyon_paket_id,$request->paketid,$request->paketfiyat,$request->paketbaslangictarihi,$request->seansaralikgun,$request->personel_id,null,null);
        $adisyon_paket = AdisyonPaketler::where('id',$adisyon_paket_id)->first();
        foreach($adisyon_paket->seanslar as $paketseans)
        {
            $randevu = Randevular::where('id',$paketseans->randevu_id)->first();
            foreach($randevu->hizmetler as $randevuhizmet){
                $hizmet = RandevuHizmetler::where('id',$randevuhizmet->id)->first();
                $hizmet->delete();
            }
            $randevu->delete();
        }
        self::pakettenrandevuveseansolustur($request,$adisyon_paket_id);
        return AdisyonPaketler::where('id',$adisyon_paket_id)->first();
        
    }
    public function pakettenrandevuveseansolustur(Request $request,$adisyon_paket_id)
    {       $hizmete_ait_randevu = array();
            $paket_mevcut = Paketler::where('id',$request->paketid)->first();
            $seanstarih = '';
            foreach($paket_mevcut->hizmetler as $key2 => $hizmet2)
            {
                $randevu_id  ='';
                for($i=1;$i<=$hizmet2->seans;$i++)
                {
                     
                    if($i==1){
                        $seanstarih = $request->paketbaslangictarihi;
                        
                    }
                    if($i>1){
                        $seanstarih = date('Y-m-d',strtotime('+'.$request->seansaralikgun.' days',strtotime($seanstarih)));
                         
                    } 
                    
                    
                    
                    $hizmet_sure = 60;
                    if(SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$hizmet2->hizmet_id)->value('sure_dk') > 0)
                        $hizmet_sure = SalonHizmetler::where('salon_id',$request->sube)->where('hizmet_id',$hizmet2->hizmet_id)->value('sure_dk');
                    if($key2==0||count($hizmete_ait_randevu)<$i)
                    {
                         
                        $yenisaatbaslangic = $request->paket_satis_seans_saati;
                        $seans_randevu = new Randevular(); 
                        
                        $seans_randevu->user_id = $request->musteri_id;
                        $seans_randevu->tarih = $seanstarih;
                        $seans_randevu->salon_id = $request->sube;
                        $seans_randevu->durum = 1;
                        $seans_randevu->saat = $request->paket_satis_seans_saati;
                        $seans_randevu->olusturan_personel_id = $request->olusturan; //Personeller::where('yetkili_id',Auth::user()->id)->where('salon_id',$request->sube)->value('id');
                     
                        $seans_randevu->salon = 1;
                        $seans_randevu->save(); 
                        
                        $randevu_id = $seans_randevu->id; 
                        array_push($hizmete_ait_randevu,$randevu_id);
                        if($i==$hizmet2->seans)
                            $yenisaatbaslangic = date("H:i", strtotime('+'.$hizmet_sure.' minutes', strtotime($request->paket_satis_seans_saati)));
                    }
                    else
                        $randevu_id = $hizmete_ait_randevu[$i-1];
                    
                    
                    $seans = new AdisyonPaketSeanslar();
                    $seans->adisyon_paket_id = $adisyon_paket_id;
                    $seans->seans_tarih = $seanstarih;
                    $seans->hizmet_id = $hizmet2->hizmet_id;
                    $seans->seans_no = $i;
                    $seans->seans_saat = $yenisaatbaslangic;
                    $seans->personel_id = 183;
                    $seans->randevu_id = $randevu_id;
                    $seans->save();
                    $seans_randevu_hizmet = new RandevuHizmetler();
                    $seans_randevu_hizmet->randevu_id = $randevu_id;
                    $seans_randevu_hizmet->hizmet_id =$hizmet2->hizmet_id;
                    $seans_randevu_hizmet->personel_id = 183;
                    $seans_randevu_hizmet->sure_dk = $hizmet_sure;
                    if($key2==0||count($hizmete_ait_randevu)<$i)    
                        $seans_randevu_hizmet->saat = $request->paket_satis_seans_saati;
                    else
                        $seans_randevu_hizmet->saat = $yenisaatbaslangic;
                     
                    $seans_randevu_hizmet->saat_bitis = date("H:i", strtotime('+'.$hizmet_sure.' minutes', strtotime($yenisaatbaslangic))); 
                    $seans_randevu_hizmet->save(); 
                } 
            }
    }
    public function paketsatisiekleguncelle($adisyon_id,$adisyon_paket_id,$paket_id,$fiyat,$baslangic_tarihi,$seans_araligi,$personel_id,$senet_id,$taksitli_tahsilat_id){
        $adisyon_paket = '';
        if($adisyon_paket_id != "")
            $adisyon_paket = AdisyonPaketler::where('id',$adisyon_paket_id)->first();
        else
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
    public function tahsilat_urun_sil(Request $request)
    {
        $adisyonurun = AdisyonUrunler::where('id',$request->adisyonurunid)->first();
        $tahsilatlar = Tahsilatlar::where('adisyon_id',$adisyonurun->adisyon_id)->get();
        
        $uruneaittahsilatvar = false;
        
        foreach($tahsilatlar as $tahsilat)
        {
            if(TahsilatUrunler::where('tahsilat_id',$tahsilat->id)->where('adisyon_urun_id',$adisyonurun->id)->count()!=0)
                $uruneaittahsilatvar = true;
        }            
        if($uruneaittahsilatvar)
        {
            return array(
               'basarili'=>"0", 'mesaj'=>$adisyonurun->urun->urun . ' için tahsilat kaydı bulunmakta olduğundan adisyondan silme işlemi gerçekleştirilemiyor. Önce tahsilat kaydının kaldırılması gereklidir.'
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
             
            return array(
               'basarili'=>"1", 'mesaj'=>$adisyonurun->urun->urun . ' başarıyla kaldırıldı.'
            );
            exit;
        }
        
    }
    public function tahsilat_paket_sil(Request $request)
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
                'basarili'=>'0',
                'mesaj'=>$adisyonpaket->paket->paket_adi.' için tahsilat kaydı bulunmakta olduğundan adisyondan silme işlemi gerçekleştirilemiyor. Önce tahsilat kaydının kaldırılması gereklidir.'
            );
            exit;
        }
        else
        {
            $paketseanslari = AdisyonPaketSeanslar::where('adisyon_paket_id',$request->adisyonpaketid)->get();
            foreach($paketseanslari as $paketseans)
            {
                $seansrandevu = Randevular::where('id',$paketseans->randevu_id)->first();
                if($seansrandevu)
                {
                    foreach($seansrandevu->hizmetler as $seansrandevuhizmet)
                    {
                        $randevuhizmet = RandevuHizmetler::where('id',$seansrandevuhizmet->id)->first();
                        $randevuhizmet->delete();
                    }
                    $seansrandevu->delete();
                }
                
                
            }
            $adisyonpaket->delete();
            if(AdisyonHizmetler::where('adisyon_id',$adisyon_id)->count()+AdisyonUrunler::where('adisyon_id',$adisyon_id)->count()+AdisyonPaketler::where('adisyon_id',$adisyon_id)->count()==0)
            {
                Adisyonlar::where('id',$adisyon_id)->delete(); 
            }
             return array(
                'basarili'=>'1',
                'mesaj'=>$adisyonpaket->paket->paket_adi.' tahsilat ekranından başarıyla kaldırıldı'
            );
            exit;
             
           
        }
        
    }
    public function tahsilat_hizmet_sil(Request $request)
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
                'basarili'=>'0', 'mesaj'=>$hizmet->hizmet->hizmet_adi . ' için tahsilat kaydı bulunmakta olduğundan adisyondan silme işlemi gerçekleştirilemiyor. Önce tahsilat kaydının kaldırılması gereklidir.'
            );
            exit;
        }
         
        else
        {
            $hizmet->delete();
            /*if(AdisyonHizmetler::where('adisyon_id',$adisyon_id)->count()+AdisyonUrunler::where('adisyon_id',$adisyon_id)->count()+AdisyonPaketler::where('adisyon_id',$adisyon_id)->count()==0)
                Adisyonlar::where('id',$adisyon_id)->delete()*/
            return array('basarili'=>'1', 'mesaj'=>$hizmet->hizmet->hizmet_adi.' başarıyla kaldırıldı');
            exit;
        }
        
    }
public function etkinliktekrarsmsgonder(Request $request)
    {
      $etkinlikbeklenen= Etkinlikler::where('id',$request->etkinlikid)->first();
      $mesajlar=array();
 
       foreach ($etkinlikbeklenen->katilimcilar as  $katilimci) {
        if($katilimci->durum===null)
        {
            $katilim_link = ''; 
            if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$etkinlikbeklenen->salon_id)->value('musteri')==1)
                $katilim_link = ' Katılım için : https://'.$_SERVER['SERVER_NAME'].'/etkinlikkatilim/'.$etkinlikbeklenen->id.'/'.$katilimci->user_id;
            if(MusteriPortfoy::where('user_id',$katilimci->user_id)->where('salon_id',$etkinlikbeklenen->salon_id)->value('kara_liste')!=1)
                array_push($mesajlar, array("to"=>$katilimci->musteri->cep_telefon,"message"=> $etkinlikbeklenen->mesaj.$katilim_link));
        }
        
      }
        $gonder= self::sms_gonder($request,$mesajlar,true,6,false,$etkinlikbeklenen->salon_id);
       
        return array(
          "mesaj" => "SMS başarıyla gönderildi",
          "gonder"=>$gonder,
        );
    }
    public function etkinlikpasifet(Request $request)
    {
        $etkinlik = Etkinlikler::where('id',$request->etkinlikid)->first();
        $etkinlik->aktifmi = 0;
        $etkinlik->save();
    }
   public function formgonder(Request $request){
        $form = Arsiv::where('id',$request->id)->first();
        $mesajlar=array();
        $form->cevapladi=false;
         $random = str_shuffle('1234567890');
        $kod = substr($random, 0, 4);      
        $form->dogrulama_kodu = $kod;
        $form->durum=null;
        $form->save();
        if ($form->user_id) {
            $katilim_link = ' Formu doldurmak için : https://'.$_SERVER['SERVER_NAME'].'/musteriformdoldurma/'.$form->id.'/'.$form->user_id.' Onay Kodu:'.$kod;
            if(MusteriPortfoy::where('user_id',$form->user_id)->where('salon_id',$form->salon_id)->value('kara_liste')!=1)
                    array_push($mesajlar, array("to"=>$form->musteri->cep_telefon,"message"=>$katilim_link));
        }
         $gonder=self::sms_gonder($request,$mesajlar,true,6,true,$form->salon_id);
          return array(
          "mesaj" => "SMS başarıyla gönderildi",
          "gonder"=>$gonder,
        );
    }
    public function arsivonayla(Request $request){
        $form = Arsiv::where('id',$request->id)->first();
        $form->durum=1;
        $form->cevapladi=false;
        $form->save();
    }
     public function musteriekleguncelle(Request $request,$sube){
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
                    $portfoyvar = MusteriPortfoy::where('user_id',$mevcut->id)->where('salon_id',$sube)->where('aktif',true)->count();
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
                        if(MusteriPortfoy::where('user_id',$mevcut->id)->where('salon_id',$sube)->where('aktif','!=',true)->count()==1)
                            $portfoy = MusteriPortfoy::where('user_id',$mevcut->id)->where('salon_id',$sube)->where('aktif','!=',true)->first();
                        else
                            $portfoy = new MusteriPortfoy();
                        $portfoy->user_id = $mevcut->id;
                        $portfoy->salon_id = $sube;
                        $portfoy->musteri_tipi = $request->musteri_tipi;
                         $portfoy->aktif = true;
                        $portfoy->save();
                      
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
            $musteri->dogum_tarihi=$request->dogum_tarihi; 
               $musteri->ozel_notlar=$request->ozel_notlar; 
               $musteri->musteri_tipi=$request->musteri_tipi;    
            if($request->cinsiyet == 0 || $request->cinsiyet == 1)
                $musteri->cinsiyet = $request->cinsiyet;
            $musteri->save();
            if($request->musteri_id == "") 
                 $portfoy = new MusteriPortfoy(); 
            else
                $portfoy = MusteriPortfoy::where('user_id',$musteri->id)->where('salon_id',$sube)->first();
            $portfoy->user_id= $musteri->id;
            $portfoy->salon_id =$sube;
            $portfoy->musteri_tipi = $request->musteri_tipi;
            $portfoy->ozel_notlar = $request->ozel_notlar; 
            $portfoy->aktif = true;
            $portfoy->save(); 
            if(SalonSMSAyarlari::where('salon_id',$sube)->where('ayar_id',4)->value('musteri')){
                if($yeniekleme || $baskaekleme){
                    $mesaj = Salonlar::where('id',$sube)->value('salon_adi')." tarafından müşteri kaydınız oluşturulmuştur.";
                    if(Salonlar::where('id',$sube)->value('uygulamalar_kisa_link'))
                        $mesaj .=' Uygulamamızı indirmek için linke tıklayın. '.Salonlar::where('id',$sube)->value('uygulamalar_kisa_link');
                    self::sms_gonder($request,array(array("to"=>$musteri->cep_telefon,"message"=>$mesaj)),false,1,false,$sube);
                }
                 
            } 
            $returnvar = $musteri;
            return $returnvar; 
    }
     public function musteri_sil(Request $request){
        $portfoy = MusteriPortfoy::where('user_id',$request->portfoy_id)->where('salon_id',$request->salonid)->first();
        $portfoy->aktif = 0;
        $portfoy->save();
    
        
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
    
    }
        public function salonlar(Request $request)
    {
        
    $isletme = Salonlar::where('id',$request->salon_id)->first();
    return $isletme;
}
 public function salonsaatleri(Request $request,$salon_id)
    {
        
    $isletme = SalonCalismaSaatleri::where('salon_id',$salon_id)->get();
    return $isletme;
}
        public function randevuayarguncelle(Request $request)
    {
        $isletme = Salonlar::where('id',$request->salon_id)->first();
        $isletme->randevu_saat_araligi=$request->randevu_saat_araligi;
        $isletme->randevu_takvim_turu = $request->randevu_takvim_turu;
        
        $isletme->save();
        return 'Randevu ayarları başarıyla kaydedildi';
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
     public function salonmolasaatleri(Request $request,$salon_id)
    {
        
    $isletme = SalonMolaSaatleri::where('salon_id',$salon_id)->get();
    return $isletme;
}
 public function personelmolasaatleri(Request $request,$perosnelid)
    {
        
    $isletme = PersonelMolaSaatleri::where('personel_id',$perosnelid)->get();
    return $isletme;
}
 public function personelcalismasaatleri(Request $request,$perosnelid)
    {
        
    $isletme = PersonelCalismaSaatleri::where('personel_id',$perosnelid)->get();
    return $isletme;
}
  public function mola_saati_guncelle_ekle(Request $request,$isletme_id){
       
         $calismasaati = SalonMolaSaatleri::where('salon_id',$isletme_id)->get();
                 
                foreach ($calismasaati as $key => $value) {
                    if($key == 0){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                         $calismasaatiherbiri->mola_var = $request->calisiyor1;
                    $calismasaatiherbiri->haftanin_gunu = 1;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic1;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis1;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 1){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                         $calismasaatiherbiri->haftanin_gunu = 2;
                          $calismasaatiherbiri->mola_var = $request->calisiyor2;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic2;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis2;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 2){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 3;
                          $calismasaatiherbiri->mola_var = $request->calisiyor3;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic3;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis3;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 3){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->mola_var = $request->calisiyor4;
                          $calismasaatiherbiri->haftanin_gunu = 4;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic4;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis4;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 4){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 5;
                          $calismasaatiherbiri->mola_var = $request->calisiyor5;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic5;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis5;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 5){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 6;
                          $calismasaatiherbiri->mola_var = $request->calisiyor6;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic6;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis6;
                          $calismasaatiherbiri->save();
                    }
                     if($key == 6){
                          $calismasaatiherbiri = SalonMolaSaatleri::where('id',$value->id)->first();
                          $calismasaatiherbiri->haftanin_gunu = 7;
                          $calismasaatiherbiri->mola_var = $request->calisiyor7;
                          $calismasaatiherbiri->baslangic_saati = $request->calismasaatibaslangic7;
                          $calismasaatiherbiri->bitis_saati =$request->calismasaatibisis7;
                          $calismasaatiherbiri->save();
                    }
                     
                } 
    }
public function personelgetir(Request $request,$isletme_id){
    $personeller=DB::table('salon_personelleri')->join('isletmeyetkilileri','salon_personelleri.yetkili_id','=','isletmeyetkilileri.id')
         
        ->join('model_has_roles','isletmeyetkilileri.id','=','model_has_roles.model_id')
        ->join('roles','model_has_roles.role_id','=','roles.id')->select('salon_personelleri.id as id','salon_personelleri.personel_adi as personel_adi','salon_personelleri.hizmet_prim_yuzde as hizmet_prim_yuzde','salon_personelleri.paket_prim_yuzde as paket_prim_yuzde','salon_personelleri.urun_prim_yuzde as urun_prim_yuzde','salon_personelleri.unvan as unvan','salon_personelleri.maas as maas','salon_personelleri.cep_telefon as cep_telefon','roles.id as hesap_turu','salon_personelleri.cinsiyet as cinsiyet','salon_personelleri.aktif as aktif')->where('salon_personelleri.salon_id',$isletme_id)->where('salon_personelleri.personel_adi','like','%'.$request->baslik.'%')->paginate(10); 
        return $personeller;
}
    public function cihazgetir(Request $request,$isletme_id){
         $cihazlar= DB::table('cihazlar')->select('cihazlar.id as id','cihazlar.cihaz_adi as cihaz_adi','cihazlar.durum as durum','cihazlar.aciklama as aciklama','cihazlar.aktifmi as aktifmi',
       )->where('cihazlar.salon_id',$isletme_id)->where('cihazlar.cihaz_adi','like','%'.$request->baslik.'%')->where('aktifmi',true)->paginate(10); 
         return $cihazlar;
    }
 public function cihazmusaitisaretle(Request $request){
       $cihaz = Cihazlar::where('id',$request->cihaz_id)->first();
        $cihaz->durum = 1;
        $cihaz->aciklama=null;
        $cihaz->save();
    }
    public function cihazmusaitdegilisaretle(Request $request){
       $cihaz = Cihazlar::where('id',$request->cihaz_id)->first();
        $cihaz->durum = false;
       $cihaz->aciklama=$request->aciklama;
        $cihaz->save();
     
    }
      public function cihaz_sil(Request $request){
        Cihazlar::where('id',$request->cihaz_id)->update(['aktifmi'=>false]);
        SalonCihazRenkleri::where('cihaz_id',$request->cihaz_id)->delete();
       
    }
    
 public function odagetir(Request $request,$isletme_id){
         $odalar= DB::table('odalar')->select('odalar.id as id','odalar.oda_adi as oda_adi','odalar.durum as durum','odalar.aciklama as aciklama','odalar.aktifmi as aktifmi',
       )->where('odalar.salon_id',$isletme_id)->where('odalar.oda_adi','like','%'.$request->baslik.'%')->where('aktifmi',true)->paginate(10); 
         return $odalar;
    }
 public function odamusaitisaretle(Request $request){
       $oda = Odalar::where('id',$request->oda_id)->first();
        $oda->durum = 1;
        $oda->aciklama=null;
        $oda->save();
    }
    public function odamusaitdegilisaretle(Request $request){
       $oda = Odalar::where('id',$request->oda_id)->first();
        $oda->durum = false;
       $oda->aciklama=$request->aciklama;
        $oda->save();
     
    }
      public function oda_sil(Request $request){
        Odalar::where('id',$request->oda_id)->update(['aktifmi'=>false]);
        OdaRenkleri::where('oda_id',$request->oda_id)->delete();
       
    }
        public function odaekleduzenle(Request $request,$isletme_id){
        $odalar = new Odalar();
        $returntext="";
        $odalar->salon_id = $isletme_id;
        $odalar->oda_adi = $request->oda_adi;
        $odalar->aktifmi = true;
        $odalar->durum = true;
        $odalar->save();
        
        $kategori_son_renk = OdaRenkleri::where('salon_id',$isletme_id)->orderBy('id','desc')->first();
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
     
    }
        public function personelekleduzenle(Request $request,$isletme_id){
        $result = '';
        $swaltitle = '';
        $swalstat  ='';
        $yenihesapacma = false;
        $yeniekleme = false;
        $olusturulansifre = '';
        if($request->personel_id == '' && Personeller::where('cep_telefon',$request->cep_telefon)->where('salon_id',$isletme_id)->count() == 1 && IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->count()==1){
            $personel = Personeller::where('id',$request->personel_id)->where('salon_id',$isletme_id)->first();
            $result = 'Girmiş olduğunuz cep telefonu ile sistemde '.IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->value('name').' isimli kayıt zaten mevcut. Lütfen başka bir kayıt giriniz';
            $swaltitle = 'Uyarı';
            $swalstat  = 'warning';
        }
        else{
            $personel = '';
            $yetkili = '';
            if(IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->count()==0){
                $yetkili = new IsletmeYetkilileri();
                $yenihesapacma = true;
            }
            else
                $yetkili = IsletmeYetkilileri::where('gsm1',$request->cep_telefon)->first();
            if(Personeller::where('cep_telefon',$request->cep_telefon)->where('salon_id',$isletme_id)->count()==0){
                $personel = new Personeller();
                $yeniekleme = true;
                $personel->aktif=true;
                $son_eklenen_personel = Personeller::where('salon_id',$isletme_id)->orderBy('id','desc')->first();
                if($son_eklenen_personel->renk == 10)
                        $personel->renk = 1;
                else
                        $personel->renk = $son_eklenen_personel->renk + 1;
            }
            else{ 
                $personel = Personeller::where('cep_telefon',$request->cep_telefon)->where('salon_id',$isletme_id)->first();
                 
            } 
            $personel->personel_adi = $request->personel_adi;
            $personel->unvan = $request->unvan;
            $yetkili->unvan = $request->unvan;
            
            $personel->maas = $request->personel_maas;
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
            $personel->salon_id = $isletme_id;
            $personel->cinsiyet = $request->cinsiyet;
            $personel->maas = $request->personel_maas;
            $personel->unvan = $request->unvan;
            $personel->hizmet_prim_yuzde = $request->hizmet_prim_yuzde;
            $personel->urun_prim_yuzde = $request->urun_prim_yuzde;
            $personel->paket_prim_yuzde = $request->paket_prim_yuzde; 
            $personel->yetkili_id = $yetkili->id;
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
                              $personelcalismasaatleri->calisiyor =  $request->calisiyor1;
                             $personelcalismasaatleri->bitis_saati = $request->bitissaati1;
                        }
                         if($i==2){
                             $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati2;
                              $personelcalismasaatleri->calisiyor =  $request->calisiyor2;
                             $personelcalismasaatleri->bitis_saati = $request->bitissaati2;
                        }
                         if($i==3){
                             $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati3;
                              $personelcalismasaatleri->calisiyor =  $request->calisiyor3;
                             $personelcalismasaatleri->bitis_saati = $request->bitissaati3;
                        }
                         if($i==4){
                             $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati4;
                              $personelcalismasaatleri->calisiyor =  $request->calisiyor4;
                             $personelcalismasaatleri->bitis_saati = $request->bitissaati4;
                        }
                         if($i==5){
                             $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati5;
                             $personelcalismasaatleri->calisiyor =  $request->calisiyor5;
                             $personelcalismasaatleri->bitis_saati = $request->bitissaati5;
                        }
                         if($i==6){
                             $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati6;
                              $personelcalismasaatleri->calisiyor =  $request->calisiyor6;
                             $personelcalismasaatleri->bitis_saati = $request->bitissaati6;
                        }
                         if($i==7){
                             $personelcalismasaatleri->baslangic_saati = $request->baslangicsaati7;
                              $personelcalismasaatleri->calisiyor =  $request->calisiyor7;
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
                              $personelmolasaatleri->mola_var =  $request->mola1;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati1;
                        }
                         if($i==2){
                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati2;
                             $personelmolasaatleri->mola_var =  $request->mola2;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati2;
                        }
                         if($i==3){
                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati3;
                             $personelmolasaatleri->mola_var =  $request->mola3;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati3;
                        }
                         if($i==4){
                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati4;
                             $personelmolasaatleri->mola_var =  $request->mola4;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati4;
                        }
                         if($i==5){
                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati5;
                             $personelmolasaatleri->mola_var =  $request->mola5;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati5;
                        }
                         if($i==6){
                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati6;
                             $personelmolasaatleri->mola_var =  $request->mola6;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati6;
                        }
                         if($i==7){
                             $personelmolasaatleri->baslangic_saati = $request->molabaslangicsaati7;
                             $personelmolasaatleri->mola_var =  $request->mola7;
                             $personelmolasaatleri->bitis_saati = $request->molabitissaati7;
                        }
                        $personelmolasaatleri->save();
            } 
            if($yeniekleme){
                    $mesajlar = array();
                    array_push($mesajlar, array("to"=>$yetkili->gsm1,"message"=> "Sayın ".$yetkili->name.". Randevu sistemi şifreniz : ".$olusturulansifre));
                    self::sms_gonder($request,$mesajlar,false,1,false,$request->isletme_id);
            } 
            $yetkili->roles()->detach();
            $sistemyetkisi = '';
            if(isset($request->sistem_yetki))
                $sistemyetkisi = $request->sistem_yetki;
            else
                $sistemyetkisi = 1;
            
            DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values ('.$sistemyetkisi.', "App\\\IsletmeYetkilileri",'.$yetkili->id.','.$isletme_id.')');
            
            $result = 'Personel başarıyla kaydedildi';
            $swaltitle = 'Başarılı';
            $swalstat = 'success';
        }
    
          
    }
    public function cihazekle(Request $request,$isletme_id){
                $cihazlar = new Cihazlar();
        $returntext="";
        $cihazlar->salon_id = $isletme_id;
        $cihazlar->cihaz_adi = $request->cihaz_adi;
        $cihazlar->aktifmi = true;
        $cihazlar->durum = true;
        $cihazlar->save();
        $cihazrenk = new SalonCihazRenkleri();
        $kategori_son_renk = SalonCihazRenkleri::where('salon_id',$isletme_id)->orderBy('id','desc')->first();
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
                      
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor1;
                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati1;
                        }
                         if($i==2){
                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati2;
                    
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor2;
                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati2;
                        }
                         if($i==3){
                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati3;
                         
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor3;
                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati3;
                        }
                         if($i==4){
                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati4;
                        
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor4;
                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati4;
                        }
                         if($i==5){
                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati5;
                
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor5;
                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati5;
                        }
                         if($i==6){
                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati6;
                             
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor6;
                             $cihazcalismasaatleri->bitis_saati = $request->cihaz_bitissaati6;
                        }
                         if($i==7){
                             $cihazcalismasaatleri->baslangic_saati = $request->cihaz_baslangicsaati7;
                           
                             $cihazcalismasaatleri->calisiyor =  $request->calisiyor7;
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
                           
                             $cihazmolasaatleri->mola_var =  $request->mola1;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati1;
                        }
                         if($i==2){
                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati2;
                     
                                  $cihazmolasaatleri->mola_var =  $request->mola2;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati2;
                        }
                         if($i==3){
                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati3;
                        
                                  $cihazmolasaatleri->mola_var =  $request->mola3;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati3;
                        }
                         if($i==4){
                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati4;
                            
                                  $cihazmolasaatleri->mola_var =  $request->mola4;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati4;
                        }
                         if($i==5){
                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati5;
                           
                                  $cihazmolasaatleri->mola_var =  $request->mola5;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati5;
                        }
                         if($i==6){
                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati6;
          
                                  $cihazmolasaatleri->mola_var =  $request->mola6;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati6;
                        }
                         if($i==7){
                             $cihazmolasaatleri->baslangic_saati = $request->cihaz_molabaslangicsaati7;
                       
                                  $cihazmolasaatleri->mola_var =  $request->mola7;
                             $cihazmolasaatleri->bitis_saati = $request->cihaz_molabitissaati7;
                        }
                        $cihazmolasaatleri->save();
                    
        }
    }
    public function bildirimkimligiekleguncelle(Request $request)
    {
        if(BildirimKimlikleri::where('isletme_yetkili_id',$request->yetkili_id)->where('bildirim_id',$request->onesignalid)->count()==0)
        {
            //BildirimKimlikleri::where('isletme_yetkili_id',$request->userid)->delete();
            $bildirimkimligi = new BildirimKimlikleri();
            $bildirimkimligi->isletme_yetkili_id = $request->yetkili_id;
            $bildirimkimligi->bildirim_id = $request->onesignalid;
            $bildirimkimligi->save();
        }
    }
    public function ajanda_okunduisaretle(Request $request){
         $ajandanot = Ajanda::where('id',$request->ajanda_id)->first();
  
       $ajandanot->ajanda_durum=1;
        $ajandanot->aktif=true;
        $ajandanot->save();
        return $ajandanot;
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
        
        foreach($randevu->hizmetler as $hizmet)
        {
            $mesaj = $randevu->users->name .' isimli müşterinin yarın '.date('H:i',strtotime($hizmet->saat)).' saatli '.$hizmet->hizmetler->hizmet_adi.' randevusu '.IsletmeYetkilileri::where('id',$request->user)->value('name').' tarafından onaylanmıştır.';
            if(SalonSMSAyarlari::where('ayar_id',2)->where('salon_id',$randevu->salon_id)->value('personel') == 1)
            {
                    $yetkiliid = Personeller::where('id',$hizmet->personel_id)->value('yetkili_id');
                    
                    $randevutarihsaat = date('d.m.Y',strtotime($randevu->tarih)).' '.date('H:i:s',strtotime($hizmet->saat));  
                     array_push($mesajlar,array("to"=>IsletmeYetkilileri::where('id',$yetkiliid)->value('gsm1'),"message"=>$mesaj )); 
                    
                   
            }
            self::bildirimekle($request,$randevu->salon_id,$mesaj,"#",$hizmet->personel_id,null, IsletmeYetkilileri::where('id',$request->user)->value('profil_resim'),$randevu->id);
            $bildirimkimlikleri = BildirimKimlikleri::where('isletme_yetkili_id',$hizmet->personel_id)->pluck('bildirim_id')->toArray(); 
            self::bildirimgonder($bildirimkimlikleri,"Randevu Onayı",$mesaj,$randevu->salon_id); 
        }
        if(count($mesajlar)>0)
            self::sms_gonder($request,$mesajlar,false,1,false,$randevu->salon_id);
       
        return 'başarılı';
        
                   
    }
    
 public function ongorusmesatisyapilmadi(Request $request)
    {
        $ongorusme = OnGorusmeler::where('id',$request->ongorusmeid)->first();
        $ongorusme->durum = false;
        $ongorusme->satisyapilmadi_not=$request->satisyapilmamasebebi;
        $ongorusme->save();
        return $ongorusme;
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
            $adisyon_id = self::yeni_adisyon_olustur($user->id,$ongorusme->salon_id,$ongorusme->paket->paket_adi.' paketinin öngörüşme sonrası satışı',date('Y-m-d'),IsletmeYetkilileri::where('id', $request->olusturan)->first());
        
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
            $adisyon_id = self::yeni_adisyon_olustur($user->id,$ongorusme->salon_id,$ongorusme->urun->urun_adi.' ürününün öngörüşme sonrası satışı',date('Y-m-d'),IsletmeYetkilileri::where('id', $request->olusturan)->first());
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
        return 'Başarılı';  
    }
    public function personelaktifyap(Request $request)
    {
        $yetkili = Personeller::where('id',$request->personelid)->first();
  
            $yetkili->aktif = true;
    
        $yetkili->save(); 
        return $yetkili; 
    }
    public function personelpasifyap(Request $request)
    {
        $yetkili = Personeller::where('id',$request->personelid)->first();
  
            $yetkili->aktif = false;
    
        $yetkili->save(); 
        return $yetkili; 
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
        $mesajlar = array(array("to"=>$yetkili->gsm1,"message"=> "Sayın ".$yetkili->name.". Randevumcepte yeni şifreniz : ".$kod. ' olarak güncellenmiştir.'));
        self::sms_gonder($request,$mesajlar,false,1,false,$yetkili->salon_id);
        return $personel;
              
    }
    
    
    public function hizmet_liste_getir(Request $request,$salon_id){
      $hizmet_liste= DB::table('salon_sunulan_hizmetler')->
        join('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')
        ->leftjoin('personel_sunulan_hizmetler','salon_sunulan_hizmetler.hizmet_id','=','personel_sunulan_hizmetler.hizmet_id')
        ->leftjoin('cihaz_sunulan_hizmetler','cihaz_sunulan_hizmetler.hizmet_id','=','salon_sunulan_hizmetler.hizmet_id')
        ->leftjoin('salon_personelleri','personel_sunulan_hizmetler.personel_id','=','salon_personelleri.id')
        ->leftjoin('cihazlar','cihaz_sunulan_hizmetler.cihaz_id','=','cihazlar.id')
        ->select('salon_sunulan_hizmetler.id as id','hizmetler.hizmet_kategori_id as hizmet_kategori','hizmetler.hizmet_adi as hizmet_adi',DB::raw('GROUP_CONCAT(salon_personelleri.personel_adi ) as personel'),'hizmetler.fiyat as fiyat','hizmetler.sure_dk as sure_dk')->where('salon_sunulan_hizmetler.salon_id',$salon_id)->where('hizmetler.hizmet_adi','like','%'.$request->baslik.'%')->where('salon_sunulan_hizmetler.aktif',true)->groupBy('hizmetler.id')->paginate(10);
        
          return $hizmet_liste;
                
    
    }
    public function randevutahsilet(Request $request)
    {
        $randevu = Randevular::where('id',$request->randevuid)->first();
        $randevu->tahsilat_eklendi = true;
        $randevu->save();
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
                $adisyon_id = self::yeni_adisyon_olustur($randevu->user_id,$randevu->salon_id,date('d.m.Y',strtotime($randevu->tarih)).' tarihli randevuda alınan hizmetlerin ödemesi',date('Y-m-d'),IsletmeYetkilileri::where('id', $request->olusturan)->first());
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
    public function hizmetekleduzenle(Request $request,$isletme_id){
        foreach($request->hizmet_idler as $hizmet_id){
            $salon_hizmet = '';
            if(isset($request->ozel_hizmet_adi)){
                $hizmet = Hizmetler::where('id',$hizmet_id)->first();
                $hizmet->hizmet_adi = $request->ozel_hizmet_adi;
                $hizmet->save();
            }
            
            if(SalonHizmetler::where('hizmet_id',$hizmet_id)->where('salon_id',$isletme_id)->count()>0)
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
            
            if(SalonHizmetKategoriRenkleri::where('hizmet_kategori_id',$salon_hizmet->hizmet_kategori_id)->where('salon_id',$isletme_id)->count() == 0)
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
        $secilmeyenhizmetler = self::secilmeyen_hizmet_liste_getir($request,$isletme_id);
        return self::hizmet_liste_getir($request,"Hizmet(-ler) başarıyla eklendi",$secilmeyenhizmetler);
        
        
    }
    public function seciliolmayanhizmetlerigetir(Request $request)
    {
        return Hizmetler::whereNotIn('id',SalonHizmetler::where('salon_id',$request->sube)->pluck('id')->where('aktif',true))->where('id','!=',463)->get(); 
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
     
     public function sistemeyenihizmetekle(Request $request){ 
        $yenihizmet = new Hizmetler();
        $yenihizmet->hizmet_kategori_id = $request->hizmet_kategorisi;
        $yenihizmet->hizmet_adi = $request->hizmet_adi;
        $yenihizmet->ozel_hizmet = true;
        $yenihizmet->salon_id = $request->sube;
        $yenihizmet->cinsiyet = $request->cinsiyet;
        $yenihizmet->sure_dk = $request->hizmet_sure;
        $yenihizmet->fiyat = $request->hizmet_fiyat;
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
                $yeni_renk->salon_id = $isletme_id;
                $yeni_renk->renk_id = $yeni_kategori_renk;
                $yeni_renk->hizmet_kategori_id = $request->hizmet_kategorisi;
                $yeni_renk->save();
        }
        if(is_array($request->personelidler))
        {
            foreach($request->personelidler as $personelid){
               
                    $personelhizmet = new PersonelHizmetler();
                    $personelhizmet->personel_id = $personelid;
                    $personelhizmet->hizmet_id = $yenihizmet->id;
                    $personelhizmet->save();
                
               
            }
            foreach($request->cihazidler as $cihazid)
            {
                    $personelhizmet = new CihazHizmetler();
                    $personelhizmet->cihaz_id = $cihazid;
                    $personelhizmet->hizmet_id = $yenihizmet->id;
                    $personelhizmet->save();
                 
            }
        }
       
        return 'Başarılı';
    }
        public function paket_ekle_guncelle(Request $request, $isletme_id)
{
    $request->validate([
        'adpaket' => 'required|string',
        'hizmetler' => 'required|array',
        'hizmetler.*.hizmet_id' => 'required|integer',
        'hizmetler.*.seans' => 'required|integer',
        'hizmetler.*.fiyat' => 'required|numeric',
    ]);
    $paket = $request->paket_id == 0 ? new Paketler() : Paketler::where('id', $request->paket_id)->first();
    
    $paket->paket_adi = $request->adpaket;
    $paket->aktif = true;
    $paket->salon_id = $isletme_id;
    $paket->save();
    $toplamtutar = 0;
    PaketHizmetler::where('paket_id', $paket->id)->delete();
    foreach ($request->hizmetler as $key => $paket_hizmet) {
        if (!isset($paket_hizmet['hizmet_id'], $paket_hizmet['seans'], $paket_hizmet['fiyat'])) {
            return response()->json(['error' => 'Eksik hizmet verisi.'], 400);
        }
        $pakethizmet = new PaketHizmetler();
        $pakethizmet->paket_id = $paket->id;
        $pakethizmet->hizmet_id = $paket_hizmet['hizmet_id'];
        $pakethizmet->seans = $paket_hizmet['seans'];
        $pakethizmet->fiyat = $paket_hizmet['fiyat'];
        $toplamtutar += $paket_hizmet['fiyat']; // Corrected this part
        $pakethizmet->save();
    }
    return 'başarılı';
}
    public function hizmetkategorileri()
    {
        return Hizmet_Kategorisi::all();
    }
      public function paket_sil(Request $request){
        $paket = Paketler::where('id',$request->paket_id)->first();
        $paket->aktif = false;
        $paket->save();
        return 'başarılı';
    }
    public function mobildegelenaramagoster(Request $request){
        
        $post_url_push_notification = "https://onesignal.com/api/v1/notifications";
        $headers_push_notification = array(
                                        'Accept: application/json',
                                        'Authorization: Basic MjFiNDE3ZGQtZjY3ZC00OTE3LWI1NWQtMjBlMjcxODgxNjFj',
                                        'Content-Type: application/json',
        );
        $post_data_push_notification = 
            json_encode( 
                array( 
                    "app_id"=> $request->appid,
                    "include_player_ids" =>  [$request->bildirimkimligi],
                    "android_channel_id" => 'ae34cc4e-d2c3-41bd-8def-7e147ecaa8af',
                    "contents" => array("en"=>  $request->icerik),
                    "headings" =>  array("en"=> $request->baslik), 
                    "buttons" => array(
                        array(
                            "id" => "yanitla",
                            "text" => "YANITLA",
                           
                        ),
                        array(
                            "id" => "reddet",
                            "text" => "REDDET",
                             
                        )
                    ),
                    "priority" => "high", // Ensure high priority
                    "content_available" => true, // Make it a background notification
                    "mutable_content" => true, // Required for some actions on iOS    
                    "sticky" => true, // Set sticky to true      
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
    public function denemesantral(Request $request)
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
            startDate: "2024-10-25"
            endDate: "2024-10-25"
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
        return $result;
    }
    public function arayanmusteribilgi(Request $request)
    {
        $tel_kaynak = ltrim($request->telefon,'+');
       
        $tel_kaynak = ltrim($tel_kaynak, '90');
      
        $tel_kaynak = ltrim($tel_kaynak, '0'); 
       
        $user = User::where('cep_telefon',$tel_kaynak)->first();
        $portfoydevar = MusteriPortfoy::where('salon_id',$request->sube)->where('user_id',$user->id)->count();
        if($portfoydevar != 0)
        {
            return array(
                'musteri_adi'=>$user->name,
                'avatar'=> $user->profil_resim !== null ? $user->profil_resim : 'https://app.randevumcepte.com.tr/public/isletmeyonetim_assets/img/avatar.png',
                'numara'=>$user->cep_telefon
            );
            exit;
        }
        else
        {
            return array(
                'musteri_adi'=>$request->telefon,
                'avatar'=>  'https://app.randevumcepte.com.tr/public/isletmeyonetim_assets/img/avatar.png',
                'numara'=>$tel_kaynak
            );
        }
    }
     public function senetvadeguncelle(Request $request) 
    {
        $vade = SenetVadeleri::where('id',$request->vade_id)->first();
        $senet = Senetler::where('id',$vade->senet_id)->first();
        $eskivadetarihi = $vade->vade_tarih;
        $vade->vade_tarih = $request->tarih;
        
        $vade->notlar = $request->not;
        $vade->save();
         
        return 'Başarılı';
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
            $adisyon_id = self::yeni_adisyon_olustur($request->musteri_id,$request->sube,'Senetle Ödeme',date('Y-m-d'),IsletmeYetkilileri::where('id',$request->olusturan)->first());
        $on_odeme_tutari = str_replace(['.',','],
        ['','.'],$request->on_odeme_tutari); 
        $on_odeme_var  = false; 
        if($on_odeme_tutari != 0) 
            $on_odeme_var  = true; 
        $tahsilat = '';
        if($on_odeme_var)
        {
            $tahsilat = new Tahsilatlar();
            $tahsilat->adisyon_id = $adisyon_id;
            $tahsilat->user_id = $request->musteri_id;
            $tahsilat->tutar = $$request->olusturan;
            $tahsilat->odeme_tarihi = date('Y-m-d');    
            $tahsilat->olusturan_id = Personeller::where('salon_id',$request->sube)->where('yetkili_id',$request->olusturan)->value('id');
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
        $musteri = User::where('id',$request->musteri_id)->first();
        $musteri->tc_kimlik_no = $request->tc_kimlik_no;
        $musteri->adres = $request->adres;
        $musteri->save();
        $senet->kefil_adi = $request->kefil_adi;
        $senet->kefil_adres = $request->kefil_adres;
        $senet->kefil_tc_vergi_no = $request->kefil_tc_vergi_no;
        $senet->user_id = $request->musteri_id;
        $senet->adisyon_id = $adisyon_id;
        $senet->vade_sayisi = $request->vade;
        $senet->salon_id = $request->sube;
        $senet->olusturan_id = $request->olusturan;
        $senet->senet_turu = $request->senet_turu;
        $senet->save();
        if(isset($request->senet_hizmet_id)){
            foreach($request->senet_hizmet_id as $key => $hizmet){
                $adisyon_hizmet_id = self::adisyon_hizmet_ekle($adisyon_id,$hizmet,$request->senet_hizmetleri[$key]["islem_tarihi"],$request->senet_hizmetleri[$key]["islem_saati"],$request->senet_hizmetleri[$key]["sure"],$request->senet_hizmetleri[$key]["fiyat"],false,$request->senet_hizmetleri[$key]["personel_id"],$request->senet_hizmetleri[$key]["cihaz_id"],$senet->id,null); 
                if($on_odeme_var)
                {
                    $odeme = new TahsilatHizmetler();
                    $odeme->adisyon_hizmet_id = $adisyon_hizmet_id;
                    $odeme->tahsilat_id = $tahsilat->id;
                    $odeme->tutar = round(
                                str_replace(['.',','],['','.'],$request->senet_hizmetleri[$key]["fiyat"])
                                /($on_odeme_tutari+str_replace(['.',','],['','.'],$request->senet_tutar))*$on_odeme_tutari,2
                            );
                    $odeme->save();
                }
            }
            
        }
        if(isset($request->senet_urun_id)){
            foreach($request->senet_urun_id as $key => $urun)
            {
                $adisyon_urun = new AdisyonUrunler();
                $adisyon_urun->islem_tarihi = date('Y-m-d');
                $adisyon_urun->adisyon_id= $adisyon_id;
                $adisyon_urun->urun_id = $urun;
                $adisyon_urun->adet = $request->senet_urunleri[$key]["adet"];
                $adisyon_urun->fiyat = $request->senet_urunleri[$key]["fiyat"];
                $adisyon_urun->personel_id = $request->senet_urunleri[$key]["personel_id"];
                $adisyon_urun->senet_id = $senet->id;
                $adisyon_urun->save();
                if($on_odeme_var)
                {
                    $odeme = new TahsilatUrunler();
                    $odeme->adisyon_urun_id = $adisyon_urun->id;
                    $odeme->tahsilat_id = $tahsilat->id;
                    $odeme->aciklama = "round((str_replace(['.',','],['','.'],".$request->senet_urunleri[$key]["fiyat"].")/(str_replace(['.',','],['','.'],".$on_odeme_tutari.")+str_replace(['.',','],['','.'],".$request->senet_tutar.")))*str_replace(['.',','],['','.'],".$on_odeme_tutari.") ,2 )";
$odeme->tutar = round((str_replace(['.',','],['','.'],$request->senet_urunleri[$key]["fiyat"])/($on_odeme_tutari+str_replace(['.',','],['','.'],$request->senet_tutar)))*$on_odeme_tutari ,2 );
                    $odeme->save();
                }
           
            }
        }
        if(isset($request->senet_paket_id))
        {
            foreach($request->senet_paket_id as $key=>$paket_p){
                
                $adisyon_paket_id = self::adisyona_paket_ekle(
                    $adisyon_id,
                    $paket_p,
                    $request->senet_paketleri[$key]["fiyat"],
                    $request->senet_paketleri[$key]["baslangic_tarihi"],
                    $request->senet_paketleri[$key]["seans_araligi"],
                    $request->senet_paketleri[$key]["personel_id"],
                    $senet->id,
                    null);
                
                $seanstarih = $request->senet_paketleri[$key]["baslangic_tarihi"];
                $paket = Paketler::where('id',$paket_p)->first();
                $request["paketid"]=$paket->id;
                $request["paketbaslangictarihi"] = $request->senet_paketleri[$key]["baslangic_tarihi"];
                $request["seansaralikgun"] = $request->senet_paketleri[$key]["seans_araligi"];
                $request["paket_satis_seans_saati"] =  $request->senet_paketleri[$key]["seans_baslangic_saati"];
                $toplam_seans_sayilari = $paket->hizmetler->sum('seans');
                self::pakettenrandevuveseansolustur($request,$adisyon_paket_id,$request->senet_paketleri[$key]["baslangic_tarihi"],$request->senet_paketleri[$key]["seans_araligi"],$request->senet_paketleri[$key]["seans_baslangic_saati"]);
           
                
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
            $alacak->olusturan_id = $request->olusturan;
             
            $alacak->user_id = $musteri->id;
            $alacak->senet_id = $senet->id;
            $alacak->save();
        }
        
            
         
        /*self::sms_gonder($request,array(array("to"=>$senet->musteri->cep_telefon,"message"=>date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format(str_replace(['.',','],['','.'],$request->senet_tutar),2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senediniz oluşturulmuştur. Detaylı bilgi için bize ulaşın. 0".$senet->salon->telefon_1 )),false,1,false);
        
        $yetkili_liste = self::yetkili_telefonlari($request);
        foreach($yetkili_liste as $_yetkili)
        {
            self::sms_gonder($request,array(array("to"=>$_yetkili,"message"=>$senet->musteri->name." isimli müşteri için ".Auth::user()->name .' tarafından '.date('d.m.Y',strtotime($request->vade_baslangic_tarihi))." vade başlangıç tarihli ve tutarı ".number_format(str_replace(['.',','],['','.'],$request->senet_tutar),2,',','.')." TL olan ".$request->vade. " adet vadeden oluşan senet oluşturulmuştur.")),false,1,false);
        }*/
        
        return 'Başarılı';
    }
}
 