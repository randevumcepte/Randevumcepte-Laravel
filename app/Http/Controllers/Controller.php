<?php

namespace App\Http\Controllers;
 
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
 
use App\Randevular;
use App\SabitNumaralar;
use App\RandevuHizmetler;
use Carbon\Carbon;
use App\Alacaklar;
use App\KampanyaYonetimi;
use App\KampanyaKatilimcilari;
use Illuminate\Http\Request;
use App\Salonlar;
use App\SMSIletimRaporlari;
use App\MusteriPortfoy;
use Illuminate\Support\Facades\Log;
use Excel;
use Illuminate\Support\Facades\File;
use App\Imports\MusteriImport;
use App\Imports\PersonelImport;
use App\Imports\RandevuImport;
use App\Imports\RandevuImportSA;
use App\Imports\RandevuImportSR;
use App\Imports\SatisImport;
use App\Imports\SatisImportDR;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use App\Tahsilatlar;
use App\TahsilatHizmetler;
use App\TahsilatUrunler;
use App\TahsilatPaketler;
use App\Imports\HizmetSureImport;
use App\Personeller;
use App\Odalar;
use App\OdaPersonelleri;
use App\Cihazlar;
use App\SalonCalismaSaatleri;
use App\SalonMolaSaatleri;
use App\PersonelCalismaSaatleri;
use App\PersonelMolaSaatleri;
use App\CihazMolaSaatleri;
use App\CihazCalismaSaatleri;
use App\PersonelHizmetler;
use App\CihazHizmetler;
use GuzzleHttp\Client;

use App\SalonHizmetler;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected function checkAvailability($personelId,$cihazId, $tarih, $baslangic, $bitis)
    {
        return !DB::table('randevu_hizmetler')
        ->join('randevular', 'randevu_hizmetler.randevu_id', '=', 'randevular.id')
        ->leftjoin('personel_calisma_saatleri','randevu_hizmetler.personel_id','=','personel_calisma_saatleri.personel_id')
        ->leftjoin('personel_mola_saatleri','randevu_hizmetler.personel_id','=','personel_mola_saatleri.personel_id')
        ->leftjoin('cihaz_calisma_saatleri','randevu_hizmetler.cihaz_id','=','cihaz_calisma_saatleri.cihaz_id')
        ->leftjoin('cihaz_mola_saatleri','randevu_hizmetler.cihaz_id','=','cihaz_mola_saatleri.cihaz_id')
        ->where(function($q) use($personelId,$cihazId){
            $q->where('randevu_hizmetler.personel_id', $personelId);
            $q->orWhere('randevu_hizmetler.cihaz_id',$cihazId);
        })

        ->where('randevular.tarih', $tarih)

        ->where(function ($query) use ($baslangic, $bitis, $tarih) {
            $query->where(function ($query) use ($baslangic, $bitis, $tarih) {
                // Saatleri tam datetime formatına çeviriyoruz
                $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $tarih . ' ' . $baslangic->format('H:i:s'));
                $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $tarih . ' ' . $bitis->format('H:i:s'));
                
                
                
                // Çakışma kontrolü: yeni randevu, mevcut randevunun zaman dilimine çakışıyorsa uygun değil
                $query->where('randevu_hizmetler.saat_bitis', '<', date('H:i:s',strtotime($endTime)))  // Yeni randevu başlangıcı mevcut bitişten önce
                      ->orWhere('randevu_hizmetler.saat', '>', date('H:i:s',strtotime($startTime)));  // Yeni randevu bitişi mevcut başlangıçtan sonra
            });
        })
        ->exists();
    }
   protected function hasAppointmentConflict($resourceId, $startSlot, $endSlot, $salonId, $excludeRandevuId = null)
{
    $tarih = $startSlot->format('Y-m-d');
    // DB'de saat alanları H:i formatinda saklaniyor (randevuyaHizmetEkle). TIME veya VARCHAR kolonu da ayni formatla karsilastir.
    $startTime = $startSlot->format('H:i');
    $endTime = $endSlot->format('H:i');

    Log::info("🔍 Cakisma kontrolu - Kaynak: $resourceId, Tarih: $tarih, Saat: $startTime - $endTime" . ($excludeRandevuId ? ", haric: $excludeRandevuId" : ""));

    $buildQuery = function ($column) use ($tarih, $salonId, $excludeRandevuId, $resourceId, $startTime, $endTime) {
        $q = Randevular::where('tarih', $tarih)
            ->where('salon_id', $salonId)
            ->where('durum', '<=', 1);
        if ($excludeRandevuId) {
            $q->where('id', '!=', $excludeRandevuId);
        }
        return $q->whereHas('hizmetler', function ($qh) use ($column, $resourceId, $startTime, $endTime) {
            $qh->where($column, $resourceId)
               // Klasik overlap: mevcut.saat < yeni.bitis AND mevcut.bitis > yeni.baslangic
               ->where('saat', '<', $endTime)
               ->where('saat_bitis', '>', $startTime);
        });
    };

    $cakisma = $buildQuery('personel_id')->exists();
    if (!$cakisma) $cakisma = $buildQuery('cihaz_id')->exists();
    if (!$cakisma) $cakisma = $buildQuery('oda_id')->exists();

    Log::info("📊 Cakisma sonucu: " . ($cakisma ? 'VAR' : 'YOK'));

    return $cakisma;
}
    protected function convertToBugunYarin($tarih)
    {
        if(date('Y-m-d',strtotime($tarih))==date('Y-m-d'))
            return "Bugün";
        else if(date('Y-m-d',strtotime('+ 1 day',strtotime(date('Y-m-d')))) == date('Y-m-d',strtotime($tarih)))
            return "Yarın";
        else
            return $tarih;
    }   
   protected function isInBreak($startSlot, $endSlot, $molaSaatleri)
    {
        foreach ($molaSaatleri as $mola) {
            $molaStart = Carbon::parse($startSlot->toDateString() . " " . $mola->baslangic);
            $molaEnd = Carbon::parse($startSlot->toDateString() . " " . $mola->bitis);

            // Klasik overlap: slot.start < mola.end AND slot.end > mola.start.
            // Eski between() mantigi slot molayi tamamen kapsadiginda cakismayi kaciriyor, ayrica sinir eslesmelerinde yanlis positive veriyordu.
            if ($startSlot->lt($molaEnd) && $endSlot->gt($molaStart)) {
                return true;
            }
        }
        return false;
    }
    protected function numOfDay($date)
    {
        $day=0;
         
         if(date('D',strtotime($date))=='Mon') $day=1;
         
         else if(date('D',strtotime($date))=='Tue') $day=2;
         
         else if(date('D',strtotime($date))=='Wed') $day=3;
         
         else if(date('D',strtotime($date))=='Thu') $day=4;
         
         else if(date('D',strtotime($date))=='Fri') $day=5;
         
         else if(date('D',strtotime($date))=='Sat') $day=6;
         
         else if(date('D',strtotime($date))=='Sun') $day=7;
         return $day;
    }


    protected function roundDateTimeToNearestFiveMinutes($datetime) {
        $dt = Carbon::parse($datetime);
        $minutes = $dt->minute;
        $roundedMinutes = round($minutes / 5) * 5;

        $dt->minute = $roundedMinutes;
        $dt->second = 0;

        return $dt->toDateTimeString();
    }
    protected function randevuEkle(Request $request)
    {
        $randevu = new Randevular();
        $randevu->user_id = $request->user_id;
        $randevu->tarih = $request->tarih;
        $randevu->saat = $request->saat;
        $randevu->saat_bitis = $request->saat_bitis;
        $randevu->salon = $request->salon;
        $randevu->web = $request->web;
        $randevu->uygulama= $request->uygulma;
        $randevu->easistan = $request->easistan;
        $randevu->olusturan_user_id = $request->olusturan_user_id;
        $randevu->olusturan_personel_id = $request->olusturan_personel_id;
        $randevu->durum = $request->durum;
        $randevu->salon_id = $request->salon_id;
        $randevu->on_gorusme_id = $request->on_gorusme_id;
        $randevu->save();
        self::randevuyaHizmetEkle($request,$randevu);
        return $randevu->id;

    }
    protected function randevuyaHizmetEkle(Request $request,$randevu)
    {
        $yeniSaatBaslangic = $request->saat;
        foreach($request->hizmetler as $key2 => $hizmet){
            $randevuHizmet = new RandevuHizmetler();
            $randevuHizmet->hizmet_id = $hizmet;
            $randevuHizmet->randevu_id = $randevu->id;


            if(Personeller::where('id',$request->randevuPersonelleri[$key2])->where('salon_id',$randevu->salon_id)->count()!=0)
                $randevuHizmet->personel_id = $request->randevuPersonelleri[$key2];
            if(isset($request->randevuOdalari)){
                $randevuHizmet->oda_id = $request->randevuOdalari[$key2];
                if(Odalar::where('id',$request->randevuOdalari[$key2])->where('salon_id',$randevu->salon_id)->count()!=0)
                    $randevuHizmet->oda_id = $request->randevuOdalari[$key2];
            }
            if(isset($request->randevuCihazlari))
                $randevuHizmet->cihaz_id = $request->randevuCihazlari[$key2];
            $cihazlar = Cihazlar::where('salon_id',$randevu->salon_id)->where('aktifmi',1)->pluck('id')->toArray();
            $hizmetCihaz = CihazHizmetler::whereIn('cihaz_id',$cihazlar)->where('hizmet_id',$hizmet)->first();
            if($hizmetCihaz)
                $randevuHizmet->cihaz_id = $hizmetCihaz->cihaz_id;
            if(Cihazlar::where('id',$request->randevuPersonelleri[$key2])->where('salon_id',$randevu->salon_id)->count()!=0)
                $randevuHizmet->cihaz_id = $request->randevuPersonelleri[$key2];


            


            /*if($oda)
                $randevuHizmet->oda_id = $oda->id;*/
            
            $randevuHizmet->fiyat = $request->hizmetFiyati[$key2];
            $sure_dk = 60;
            if($request->paketBilgi != null)
            {
                $sure_dk = 0;
                if($request->paketBilgi['paketSuresi'] != null)
                {
                    $sure_dk = $request->paketBilgi['paketSuresi'];

                }
                else{
                     
                    foreach($request->paketBilgi['hizmetler'] as $pHizmet)
                    {
                        $sHizmet = SalonHizmetler::where('id',$pHizmet['hizmet_id'])->where('salon_id',$randevu->salon_id)->first();
                        $sure_dk += $sHizmet->sure_dk;
                    }
                }
                if($sure_dk ==0)
                    $sure_dk = 60;
                if($key2>0)
                    $sure_dk = 0;

            }
            if($request->hizmetSuresi[$key2] != "")
                $sure_dk = $request->hizmetSuresi[$key2];
            $randevuHizmet->sure_dk = $sure_dk;
            $birsonraki = $key2+1;
            if($key2 == 0){
                $randevuHizmet->saat = $request->saat;
                $randevuHizmet->saat_bitis = date("H:i", strtotime('+'.$sure_dk.' minutes', strtotime($request->saat)));
                if(!isset($request->{"birlestir{$birsonraki}"}))
                    $yeniSaatBaslangic = date("H:i", strtotime('+'.$sure_dk.' minutes', strtotime($request->saat)));
            }
            else{
                $randevuHizmet->saat = $yeniSaatBaslangic;
                $randevuHizmet->saat_bitis = date("H:i", strtotime('+'.$sure_dk.' minutes', strtotime($yeniSaatBaslangic)));
                if(!isset($request->{"birlestir{$birsonraki}"}))
                    $yeniSaatBaslangic = date("H:i", strtotime('+'.$sure_dk.' minutes', strtotime($yeniSaatBaslangic)));
            }
            $randevuHizmet->save();
            if(isset($request->randevuYardimciPersonelleri))
            {
                foreach($request->{"randevuYardimciPersonelleri_{$key2}"} as $yardimci_personel)
                {
                    if($yardimci_personel != '')
                    {
                        $yardimci_personel = new RandevuHizmetler();
                        $yardimci_personel->randevu_id =  $randevu->id;
                        $yardimci_personel->hizmet_id = $hizmet;
                        $yardimci_personel->cihaz_id = $request->randevuCihazlari[$key2];
                        $yardimci_personel->personel_id = $yardimci_personel;
                        $yardimci_personel->oda_id = $request->randevuOdalari[$key2];
                        $yardimci_personel->sure_dk = $request->hizmetSursi[$key2];
                        $yardimci_personel->fiyat = $request->hizmetFiyati[$key2];
                        $yardimci_personel->saat = $randevuHizmet->saat;
                        $yardimci_personel->saat_bitis = $randevuHizmet->saat_bitis;
                        $yardimci_personel->yardimci_personel = true;
                        $yardimci_personel->save();
                    }
                }
            }
           

        }
    }
    protected function alacakVarmi(Request $request)
    {
        try {
            $alacak = Alacaklar::where('user_id',$request->user_id)->where('salon_id',$request->salon_id)->orderBy('id','asc')->where('planlanan_odeme_tarihi','<',date('Y-m-d'))->first();
            if($alacak)
            {
                $metneek = "";
                if(convertToBugunYarin(date('Y-m-d H:i',strtotime($alacak->planlanan_odeme_tarihi))) != "Bugün" && convertToBugunYarin(date('Y-m-d H:i',strtotime($alacak->planlanan_odeme_tarihi))) != "Yarın")
                    $metneek1 = " tarihinde ";
                if($alacak->senet_id != "")
                    $metneek2 = " senet vadesi ";
                else
                    $metneek2 = " taksit vadesi ";
                return response()->json([
                    'success'=> true,
                    'message'=> convertToBugunYarin(date('Y-m-d H:i',strtotime($alacak->planlanan_odeme_tarihi))) . $metneek . " ödenmesi gereken ".$alacak->tutar." türk lirası tutarında gecikmiş".$metneek2." borcunuz bulunmaktadır. Ödemeyi bugün içinde gerçekleştirecekseniz biri, planlanan ödeme tarihini güncellemek için ikiyi tuşlayınız",
                    'alacak_id'=>$alacak->id,
                    "borc_var"=>true
                ]);
            }
            else
                return response()->json([
                    'success'=> true,
                    'message'=> "Ödeme tarihi geçmiş borcunuz bulunmamaktadır. Sizi tekrar ana menüye yönlendiriyorum",
                    "alacak_id"=>"",
                    "borc_var"=>false,
                ]);


        }
        catch (\Exception $e) {
            // Hata durumunda dönecek cevap
            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
                'alacak_id'=> '',
                'borc_var'=>false,
                
            ], 500);
        }
      
    }
    public function alacakSorgula($userId)
    {
         try {
            $alacak = Alacaklar::where('user_id',$request->user_id)->where('salon_id',$request->salon_id)->orderBy('id','asc')->where('planlanan_odeme_tarihi','<',date('Y-m-d'))->first();
            if($alacak)
            {
                $metneek = "";
                if(convertToBugunYarin(date('Y-m-d H:i',strtotime($alacak->planlanan_odeme_tarihi))) != "Bugün" && convertToBugunYarin(date('Y-m-d H:i',strtotime($alacak->planlanan_odeme_tarihi))) != "Yarın")
                    $metneek1 = " tarihinde ";
                if($alacak->senet_id != "")
                    $metneek2 = " senet vadesi ";
                else
                    $metneek2 = " taksit vadesi ";
                return response()->json([
                    'success'=> true,
                    'message'=> convertToBugunYarin(date('Y-m-d H:i',strtotime($alacak->planlanan_odeme_tarihi))) . $metneek . " ödenmesi gereken ".$alacak->tutar." türk lirası tutarında gecikmiş".$metneek2." borcunuz bulunmaktadır. Ödemeyi bugün içinde gerçekleştirecekseniz biri, planlanan ödeme tarihini güncellemek için ikiyi tuşlayınız",
                    'alacak_id'=>$alacak->id,
                    "borc_var"=>true
                ]);
            }
            else
                return response()->json([
                    'success'=> true,
                    'message'=> "Ödeme tarihi geçmiş borcunuz bulunmamaktadır. Sizi tekrar ana menüye yönlendiriyorum",
                    "alacak_id"=>"",
                    "borc_var"=>false,
                ]);


        }
        catch (\Exception $e) {
            // Hata durumunda dönecek cevap
            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
                'alacak_id'=> '',
                'borc_var'=>false,
                
            ], 500);
        }
    }
    public function mevcutRandevuSorgula($userId)
    {
       
    }
    public function hatirlatmaaramasiyap( $parametreler )
    {   
       
       
        // Replace with your port if not using the default.
        // If unsure check /etc/asterisk/manager.conf under [general];
        $port = 5038;

        // Replace with your username. You can find it in /etc/asterisk/manager.conf.
        // If unsure look for a user with "originate" permissions, or create one sizi
        // shown at http://www.voip-info.org/wiki/view/Asterisk+config+manager.conf.
        $username = "cxpanel";

        // Replace with your password (refered to as "secret" in /etc/asterisk/manager.conf)
        $password = "cxmanager*con";

        // Internal phone line to call from
        $internalPhoneline = "31";
       
        $i = 0;
        
        // Context for outbound calls. See /etc/asterisk/extensions.conf if unsure.
        $context = "from-internal-custom";   
         
        $socket = stream_socket_client("tcp://34.45.69.65:$port");
        if($socket)
        {
          
            
            // Prepare authentication request
            $authenticationRequest = "Action: Login\r\n";
            $authenticationRequest .= "Username: $username\r\n";
            $authenticationRequest .= "Secret: $password\r\n";
            $authenticationRequest .= "Events: off\r\n\r\n";

            // Send authentication request
            $authenticate = stream_socket_sendto($socket, $authenticationRequest);
            if($authenticate > 0)
            {
                // Wait for server response
                usleep(200000);

                // Read server response
                $authenticateResponse = fread($socket, 4096);

                // Check if authentication was successful
                if(strpos($authenticateResponse, 'Success') !== false)
                {
                   
                    
                    foreach($parametreler as $key=>$parametre)
                    {
                        $salon = Salonlar::where('id',$parametre["salonId"])->first();
                        $sabitno = SabitNumaralar::where('salon_id',$parametre["salonId"])->first();
                        $alacaklarJson = "";
                        
                        
                        if($parametre["alacakIdler"] != "")
                            $alacaklarJson = json_encode($parametre["alacakIdler"]);               
                        // Prepare originate request
                        $originateRequest = "Action: Originate\r\n";
                        

                        //$originateRequest .= "Channel: Local/".$exten."@from-internal-custom\r\n";
                        $originateRequest .= "Channel: PJSIP/0".$parametre["tel"]."@".$sabitno->numara."\r\n";
                        $originateRequest .= "Callerid: ".$sabitno->numara."\r\n";
                        $originateRequest .= "Exten: ".$parametre["exten"]."\r\n";  // 1 numaralı uzantıya yönlendirme
                        $originateRequest .= "Context: $context\r\n";  // Asterisk bağlamı (context)
                        $originateRequest .= "Variable: myMessage=" . base64_encode($parametre["mesaj"]) . "\r\n";
                        $originateRequest .= "Variable: kuyruk=" . $salon->operator_kanali . "\r\n";
                        $originateRequest .= "Variable: alacaklar=" . $alacaklarJson . "\r\n";
                        $originateRequest .= "Variable: katilimci=" . $parametre["kampanyaKatilimci"] . "\r\n";
                        $originateRequest .= "Variable: kampanyakatilimci=" . $parametre["kampanyaKatilimci"] . "\r\n";
                        $originateRequest .= "Variable: randevuid=" . $parametre["randevuid"] . "\r\n";
                        $originateRequest .= "Variable: telefon=" . $parametre["tel"] . "\r\n";
                        $originateRequest .= "Variable: sabitno=" . $sabitno->numara . "\r\n";

                        $originateRequest .= "Priority: 1\r\n";  // İlk öncelik
                        $originateRequest .= "Async: yes\r\n\r\n";  // Asenkron çağrı

                       

                        

                        // Send originate request
                        $originate = stream_socket_sendto($socket, $originateRequest);
                        if($originate > 0)
                        {
                            // Wait for server response
                            usleep(200000);

                            // Read server response
                            $originateResponse = fread($socket, 4096);
                            Log::info("Originate Response: " . $originateResponse);
                            // Check if originate was successful
                            if(strpos($originateResponse, 'Success') !== false)
                            {
                                if($parametre["alacakIdler"] != "")
                                    self::alacakHatırlatmaAramasiYapildiIsaretle($parametre["alacakIdler"],1);
                                
                                elseif($parametre["kampanyaKatilimci"] != ""){
                                    self::kampanyaTanitimYapildiIsaretle($parametre["kampanyaKatilimci"],1);
                                    self::kampanyaHatirlatmaAramasiYapildiIsaretle($parametre["kampanyaKatilimci"],1);
                                }
                                else{
                                     self::randevuHatirlatmaAramasiYapildiIsaretle($parametre["randevuid"],1);
                                }
                            } 
                            else {
                               
                                
                               
                                if($parametre["alacakIdler"] != "")
                                    self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
                                elseif($parametre["kampanyaKatilimci"] != ""){
                                    self::kampanyaTanitimYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                                    self::kampanyaHatirlatmaAramasiYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                                }
                                else{
                                    self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
                                } 
                            }
                        } 
                        else 
                        {
                           
                           
                            if($parametre["alacakIdler"] != "")
                                self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
                            elseif($parametre["kampanyaKatilimci"] != ""){
                                self::kampanyaTanitimYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                                self::kampanyaHatirlatmaAramasiYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                            }
                            else{
                                self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
                            }
                            
                        }
                    }
                    
                } 
                else {
                    foreach($parametreler as $key=>$parametre)
                    {    
                     
                        if($parametre["alacakIdler"] != "")
                            self::alacakHatırlatmaAramasiYapildiIsaretle($parametre["alacakIdler"],0);
                        elseif($parametre["kampanyaKatilimci"] != ""){
                            self::kampanyaTanitimYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                            self::kampanyaHatirlatmaAramasiYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                        }
                        else{
                           self::randevuHatirlatmaAramasiYapildiIsaretle($parametre["randevuid"],0);
                        }
                    }
                     
                }
            } 
            else {
                
              
                    foreach($parametreler as $key=>$parametre)
                    {    
                     
                        if($parametre["alacakIdler"] != "")
                            self::alacakHatırlatmaAramasiYapildiIsaretle($parametre["alacakIdler"],0);
                        elseif($parametre["kampanyaKatilimci"] != ""){
                            self::kampanyaTanitimYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                            self::kampanyaHatirlatmaAramasiYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                        }
                        else{
                           self::randevuHatirlatmaAramasiYapildiIsaretle($parametre["randevuid"],0);
                        }
                    }
                
            }
        } else {
           
           
            foreach($parametreler as $key=>$parametre)
            {    
                     
                        if($parametre["alacakIdler"] != "")
                            self::alacakHatırlatmaAramasiYapildiIsaretle($parametre["alacakIdler"],0);
                        elseif($parametre["kampanyaKatilimci"] != ""){
                            self::kampanyaTanitimYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                            self::kampanyaHatirlatmaAramasiYapildiIsaretle($parametre["kampanyaKatilimci"],0);
                        }
                        else{
                           self::randevuHatirlatmaAramasiYapildiIsaretle($parametre["randevuid"],0);
                        }
            }
            
        }
        
    }

    public function randevuHatirlatmaAramasiYapildiIsaretle($randevuId,$yapildi)
    {
        $randevu = Randevular::where('id',$randevuId)->first();
        $randevu->hatirlatma_aramasi_yapildi = $yapildi;
        $randevu->hatirlatma_ulasilamadi = 1;
        $randevu->arama_saat = date('H:i:s');
        if($yapildi)
        {
            if($randevu->tekrar_aranacak == 0 || $randevu->tekrar_aranacak == null)
                $randevu->tekrar_aranacak = 1;
            else
                $randevu->tekrar_arandi = 1;

            $randevu->tekrar_arama_tarih_saat = date('Y-m-d H:i:s',strtotime('+'.($randevu->salonlar->e_asistan_hatirlatma ? $randevu->salonlar->e_asistan_hatirlatma : 0 ).' hours',strtotime(date('Y-m-d H:i:s')))); 
        }
       
        $randevu->save();
    }
    public function kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,$yapildi)
    {
        $katilimci = KampanyaKatilimcilari::where('id',$kampanyaKatilimci)->first();   
        $kampanya = KampanyaYonetimi::where('id',$katilimci->kampanya_id)->first();
        if($kampanya->arama_yapildi == null)
            $kampanya->arama_yapildi = $yapildi;
        $kampanya->save();
    }
    public function kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,$yapildi)
    {
       

        $katilimci_duzenlenecek = KampanyaKatilimcilari::where('id',$kampanyaKatilimci)->first();
        $kampanya = KampanyaYonetimi::where('id',$katilimci_duzenlenecek->kampanya_id)->first();
        $katilimci_duzenlenecek->asistan_arama_yapildi = $yapildi;
        $katilimci_duzenlenecek->asistan_ulasamadi = 1;
        $katilimci_duzenlenecek->arama_saat= date('H:i:s');
        $katilimci_duzenlenecek->tekrar_arama_tarih_saat = date('Y-m-d H:i:s',strtotime('+'.($kampanya->salon->e_asistan_hatirlatma ? $kampanya->salon->e_asistan_hatirlatma : 0).' hours',strtotime(date('Y-m-d H:i:s'))));
        if($katilimci_duzenlenecek->tekrar_aranacak == 0 || $katilimci_duzenlenecek->tekrar_aranacak == null)
            $katilimci_duzenlenecek->tekrar_aranacak = 1;
        else
            $katilimci_duzenlenecek->tekrar_arandi = 1;
        $katilimci_duzenlenecek->save();
         
    }
    public function alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,$yapildi)
    {
        
        foreach(Alacaklar::whereIn('id',$alacakIdler)->get() as $alacak)
        {
            $alacak_duzenlenecek = Alacaklar::where('id',$alacak->id)->first();
            $alacak_duzenlenecek->hatirlatma_aramasi_yapildi = $yapildi;
            $alacak_duzenlenecek->hatirlatma_ulasilamadi = 1;
            $alacak_duzenlenecek->arama_saat = date('H:i:s');
            
            if($yapildi)
            {
                $alacak_duzenlenecek->tekrar_arama_tarih_saat = date('Y-m-d H:i:s',strtotime('+'.($alacak_duzenlenecek->salon->e_asistan_hatirlatma ? $alacak_duzenlenecek->salon->e_asistan_hatirlatma : 0).' hours',strtotime(date('Y-m-d H:i:s'))));
                if($alacak_duzenlenecek->tekrar_aranacak == 0 || $alacak_duzenlenecek->tekrar_aranacak == null)
                    $alacak_duzenlenecek->tekrar_aranacak = 1;
                else
                    $alacak_duzenlenecek->tekrar_arandi = 1; 
            }
           
            $alacak_duzenlenecek->save();
        }
    }

    public function gorevIptalEt(Request $request)
    {

        if(isset($request->randevu_id))
        {
            $randevu = Randevular::where('id',$request->randevu_id)->first();
            $randevu->hatirlatma_gorevi_iptal = true;
            $randevu->save();
            return "Randevu hatırlatma görevi başarıyla iptal edildi";
            exit;
        }
        if(isset($request->alacak_id))
        {
            $alacak = Alacaklar::where('id',$request->alacak_id)->first();
            $alacak->hatirlatma_gorevi_iptal = true;
            $alacak->save();
            return "Alacak hatırlatma görevi başarıyla iptal edildi";
            exit;
        }
        if(isset($request->kampanya_id))
        {
            $kampanya = KampanyaYonetimi::where('id',$request->kampanya_id)->first();
            $kampanya->tanitim_gorev_iptal = true;
            $kampanya->save();
            return "Kampanya tanıtım görevi başarıyla iptal edildi";
            exit;
        }
            

    
    }
    public function gorevIptalEt2(Request $request)
    {
        if(isset($request->randevu_id)) {
            $randevu = Randevular::where('id', $request->randevu_id)->first();
            if ($randevu) {
                $randevu->hatirlatma_gorevi_iptal = true;
                $randevu->save();
                return ["success" => true, "message" => "Randevu hatırlatma görevi başarıyla iptal edildi"];
            }
        }
    
        if(isset($request->alacak_id)) {
            $alacak = Alacaklar::where('id', $request->alacak_id)->first();
            if ($alacak) {
                $alacak->hatirlatma_gorevi_iptal = true;
                $alacak->save();
                return ["success" => true, "message" => "Alacak hatırlatma görevi başarıyla iptal edildi"];
            }
        }
    
        if(isset($request->kampanya_id)) {
            $kampanya = KampanyaYonetimi::where('id', $request->kampanya_id)->first();
            if ($kampanya) {
                $kampanya->tanitim_gorev_iptal = true;
                $kampanya->save();
                return ["success" => true, "message" => "Kampanya tanıtım görevi başarıyla iptal edildi"];
            }
        }
    
        return ["success" => false, "message" => "Geçerli bir ID bulunamadı, görev iptal edilemedi."];
    }
    
    public function musteriKaraListedemi($user_id,$salon_id)
    {
        return MusteriPortfoy::where('id',$user_id)->where('salon_id',$salon_id)->value('kara_liste');
    }
   
   
     public function hatirlatmaSaatiIcinde($date)
    {
        if(date('H:i',strtotime($date)) < date('H:i',strtotime('19:30')) || date('H:i',strtotime($date)) < date('H:i',strtotime('10:00'))){
            return true;
        }
        else{ 
            return false;
        }
    }
   public function sms_gonder($salonid,$mesajlar)
    {
        $alicilar = array_column((array) $mesajlar, 'to');
        $ilkMesaj = isset($mesajlar[0]['message']) ? $mesajlar[0]['message'] : '';
        Log::info('[SMS-API] sms_gonder cagrildi', [
            'salon_id' => $salonid,
            'alici_sayisi' => count($alicilar),
            'ilk_alici' => $alicilar[0] ?? null,
            'mesaj_uzunluk' => strlen($ilkMesaj),
            'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? null,
        ]);

        $isletme = $salonid != '' ? Salonlar::where('id',$salonid)->first() : '';

        $apiKey = "";
        if($isletme != '')

            $apiKey = $isletme->sms_apikey;
        else
            $apiKey = "LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5";

        if (!$isletme) {
            Log::warning('[SMS-API] salon bulunamadi — global API key ile devam', ['salon_id' => $salonid]);
        } else {
            Log::info('[SMS-API] saglayici secimi', [
                'salon_id' => $salonid,
                'salon' => $isletme->salon_adi,
                'yeni_sms' => (int) $isletme->yeni_sms,
                'baslik' => $isletme->sms_baslik,
                'apikey_var' => !empty($isletme->sms_apikey),
                'sms_user' => $isletme->sms_user_name,
                'sms_secret_var' => !empty($isletme->sms_secret),
            ]);
        }
        if($isletme != '' && $isletme->yeni_sms == 1)
        {

            require_once app_path('VoiceTelekom/Sms/SmsApi.php');
            require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
            require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
            //$smsApi = new \SmsApi("smsvt.voicetelekom.com","webfirmam","nBJeB5xb*4");
            $smsApi = new \SmsApi("smsvt.voicetelekom.com",$isletme->sms_user_name,$isletme->sms_secret);

            $request = new \SendMultiSms(); // başına "\" koyman lazım


            $request->content = $mesajlar[0]['message'];
            $request->title = 'Bildirim';
            $toList = array_column($mesajlar, 'to');
            $request->numbers = $toList;
            $request->encoding = 0;
            $request->sender = $isletme->sms_baslik;

            $request->skipAhsQuery = true;

            //İleri tarihli gönderim için
            //$request->sendingDate = "2025-01-10 13:00";

            //Gönderen başlığına tanımlı ağ geçidi
            //$request->gateway = "1b09b8c5-ae80-42af-8779-21a61afd5da1";

            //Paket periyodik olarak gönderilecekse
            //$request->periodicSettings = new PeriodicSettings();
            //$request->periodicSettings->interval = 1;
            //$request->periodicSettings->amount = 1000;

            //Rapor push olarak alınmak isteniyorsa ilgili url girilir
            //$request->pushUrl = "https://webhook.site/8d7ed0f7"

            try {
                $t0 = microtime(true);
                $response = $smsApi->sendMultiSms($request);
                $sureMs = (int) ((microtime(true) - $t0) * 1000);
                Log::info('[SMS-API] VoiceTelekom yanit', [
                    'salon_id' => $salonid,
                    'sure_ms' => $sureMs,
                    'pkgID' => $response->pkgID ?? null,
                    'err_status' => $response->err->status ?? null,
                    'err_code' => $response->err->code ?? null,
                    'err_message' => $response->err->message ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::error('[SMS-API] VoiceTelekom istisna', [
                    'salon_id' => $salonid, 'err' => $e->getMessage(),
                ]);
                return;
            }

            if($response->err == null){
                Log::info("MessageId: ".$response->pkgID."\n");
            }else{
                Log::info( "SMS Status: ".$response->err->status."\n");
                Log::info("Code: ".$response->err->code."\n");
                Log::info("Message: ".$response->err->message."\n");
            }



        }
        else
        {
            $headers = array(
                 'Authorization: Key '.$apiKey,
                 'Content-Type: application/json',
                 'Accept: application/json'
            );

            $postData = json_encode( array( "originator"=> ($salonid != '' ? $isletme->sms_baslik : 'RANDVMCEPTE'), "messages"=> $mesajlar,"encoding"=>"auto") );

            $t0 = microtime(true);
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,'https://api.efetech.net.tr/v2/sms/multi');
            curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
            $response=curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            $sureMs = (int) ((microtime(true) - $t0) * 1000);
            curl_close($ch);

            $decoded = json_decode($response,true);
            Log::info('[SMS-API] efetech yanit', [
                'salon_id' => $salonid,
                'http' => $http,
                'sure_ms' => $sureMs,
                'curl_err' => $curlErr ?: null,
                'response_id' => $decoded['response']['message']['id'] ?? null,
                'response_count' => $decoded['response']['message']['count'] ?? null,
                'response_total_price' => $decoded['response']['message']['total_price'] ?? null,
                'response_status' => $decoded['status'] ?? null,
                'response_error' => $decoded['error'] ?? ($decoded['errors'] ?? null),
            ]);
            if($salonid!="")
            {
                if($decoded != null && isset($decoded['response']) && count($decoded['response'])!=0){
                    $rapor = new SMSIletimRaporlari();
                    $rapor->salon_id = $salonid;
                    $rapor->tur = 1;
                    $rapor->aciklama = $mesajlar[0]['message'];
                    $rapor->rapor_id = $decoded["response"]["message"]["id"];
                    $rapor->adet = $decoded["response"]["message"]["count"];
                    $rapor->kredi = $decoded["response"]["message"]["total_price"];
                    sleep(1);

                    $durum = self::sms_rapor_getir($decoded["response"]["message"]["id"],$isletme);
                    $rapor->durum = 0;
                    $rapor->save();
                } else {
                    Log::warning('[SMS-API] efetech response bos veya hatali — rapor yazilmadi', [
                        'salon_id' => $salonid,
                        'raw' => is_string($response) ? substr($response, 0, 300) : null,
                    ]);
                }
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
    public function getAccessTokenNLP($salonid) {
        $credentialsPath = base_path('storage/neon-emitter-410111-d4474f1f1f0a.json'); 
        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $jwtHeader = base64_encode(json_encode(["alg" => "RS256", "typ" => "JWT"]));
        $jwtPayload = base64_encode(json_encode([
            "iss" => $credentials["client_email"],
            "scope" => "https://www.googleapis.com/auth/dialogflow",
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => time() + 3600,
            "iat" => time()
        ]));

        $privateKey = $credentials["private_key"];
        openssl_sign("$jwtHeader.$jwtPayload", $signature, $privateKey, "SHA256");
        $jwtSignature = base64_encode($signature);
        $assertion = "$jwtHeader.$jwtPayload.$jwtSignature";

        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion" => $assertion
        ]));

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $isletme = Salonlar::where('id',$salonid)->first();
        $isletme->nlp_token = $response['access_token'] ?? null;
        $isletme->nlp_token_expires = date('Y-m-d H:i:s',strtotime('+1 hours',strtotime(date('Y-m-d H:i:s'))));
        $isletme->save();
         
    }
    public function tarihBilgisiDondur($data){
         
        // Parametrelerden tarihi al
        $gun = $data["queryResult"]["parameters"]["number"] ?? null;
        $startDate = $data["queryResult"]["parameters"]["date"] ?? null;
        $startTime = $data["queryResult"]["parameters"]["time"] ?? null;
        $randevuTarihi = "";
        $randevuSaati = ""; 
        // Eğer tarih verisi varsa, parçalayarak kullan
        if ($startDate) {
            $yil = $startDate["year"] ?? date("Y");
            $ay = $startDate["month"] ?? date("m");
            $gun = $gun ?? ($startDate["day"] ?? date("d"));

            // Tarihi oluştur
            $randevuTarihi = sprintf("%04d-%02d-%02d", $yil, $ay, $gun);
           
            
        } 
        if($startTime)
        {
            $second = $startTime["seconds"] ?? date("s");
            $minute = $startTime["minutes"] ?? date("i");
            $hour = $startTime["hours"] ?? date("H");
            $randevuSaati = sprintf("%02d:%02d:%02d",$hour,$minute,$second);
        }
        return $randevuTarihi." ".$randevuSaati;
    }
    public function getCachedResponse($text)
    {
        $cacheKeys = Cache::get('dialogflow:keys', []);

        // Mevcut cache anahtarları arasında benzer metin arıyoruz
        foreach ($cacheKeys as $key) {
            similar_text($text, $key, $percent); // Benzerlik oranını hesapla
            
            if ($percent > 85) { // %85 ve üzeri benzerlik varsa aynı cache'i kullan
                return Cache::get("dialogflow:$key");
            }
        }

        return null; // Eğer benzer bir cache bulunamazsa null döndür
    }

    public function storeCache($text, $response)
    {
        $cacheKeys = Cache::get('dialogflow:keys', []);

        
        $cacheKeys[] = $text;
        Cache::put('dialogflow:keys', $cacheKeys, 86400); // Anahtarları sakla (1 gün)

        // Cache'e yeni yanıtı ekle
        Cache::put("dialogflow:$text", $response, 86400); // 1 gün sakla
    }

    public function drKlinikMusteriEkle($salonid,$dosyaAdi)
    {
       $path = "data-aktarim/drklinik/olgatirnakmusteriler.xlsx";
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new MusteriImport($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }
    }
    public function drKlinikRandevuAktar($salonid,$dosyaAdi)
    {
        $path = $dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new RandevuImport($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }

    }
     public function salonRandevuRandevuAktar($salonid,$dosyaAdi)
    {
        $path = $dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new RandevuImportSR($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }

    }
    public function salonAppyRandevuAktar($salonid,$dosyaAdi)
    {
        $path = $dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new RandevuImportSA($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }

    }
    public function drKlinikPersonelAktar($salonid,$dosyaAdi)
    {
        $path = "data-aktarim/drklinik/".$dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new PersonelImport($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }
    }
    public function drKlinikSatisAktar($salonid,$dosyaAdi)
    {
        $path = $dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new SatisImportDR($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }
    }
    public function salonAppyHizmetDetayAktar($salonid,$dosyaAdi)
    {
        $path = $dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new HizmetSureImport($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }
    }
    public function drKlinikTahsilatEkle(Request $request){
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
        
    }
    public function enyakinuygunrandevubul($randevuid, $salonHizmet, $maxDaysToCheck = 30, $tarihSaat,$paketBilgisi)  
    {
        $salon_id = "";
        $eskiBaslangic = null;
        $eskiBitis = null;
        $eskiPersonelIdler = null;
        $eskiCihazIdler = null;
        $eskiOdaIdler =null;
        $olusturmaGuncelleme = "";

        $cihazlar = collect();
        $personeller = collect();
        $odalar = null;

        $randevu="";

        if ($randevuid != "") {
            $olusturmaGuncelleme = "güncellemek";
            // Mevcut randevunun salonunu al
            $randevu = Randevular::where('id', $randevuid)->first();
            if (!$randevu) return response()->json(['success' => false, 'message' => 'Mevcut randevu bulunamadı.']);
            
            $salon_id = $randevu->salon_id;
            
            // Mevcut randevu hizmetlerini al
            $randevuHizmetleri = RandevuHizmetler::where('randevu_id', $randevuid)->get();

            if($randevuHizmetleri->count() > 0)
            {
                $baslangicSaatleri = RandevuHizmetler::where('randevu_id', $randevuid)->pluck('saat')->toArray();
                $bitisSaatleri = RandevuHizmetler::where('randevu_id', $randevuid)->pluck('saat_bitis')->toArray();
                $eskiPersonelIdler = RandevuHizmetler::where('randevu_id', $randevuid)->pluck('personel_id')->toArray();
                
                $personeller = Personeller::whereIn('id',$eskiPersonelIdler)->get();


                $eskiCihazIdler = RandevuHizmetler::where('randevu_id', $randevuid)->pluck('cihaz_id')->toArray();
                $cihazlar = Cihazlar::whereIn('id',$eskiCihazIdler)->get();
                $eskiOdaIdler = RandevuHizmetler::where('randevu_id', $randevuid)->pluck('oda_id')->toArray();



                $eskiBaslangic = Carbon::createFromTimestamp(
                    min(array_map('strtotime', $baslangicSaatleri))
                );

                $eskiBitis = Carbon::createFromTimestamp(
                    max(array_map('strtotime', $bitisSaatleri))
                );
            }
            
        } 
        else {

            $olusturmaGuncelleme = "oluşturmak";
            if($paketBilgisi != null)
                $salon_id = $paketBilgisi['salonId'];
            else{
                $salon_id = $salonHizmet->salon_id;
            }
            // Yeni randevu senaryosunda kaynaklari (cihaz/personel) $salon_id belirlendikten sonra yukle.
            // Eskiden basta bos $salon_id ile yukleniyordu; cihaz-bazli isletmelerde hicbir cihaz bulunamiyordu.
            $cihazlar = Cihazlar::where('salon_id',$salon_id)->where('aktifmi',true)->where('durum',1)->orderBy('takvim_sirasi','asc')->get();
            $personeller = Personeller::where('salon_id', $salon_id)->where('aktif', true)->where('role_id', 5)->orderBy('takvim_sirasi','asc')->get();
        }

        $startDate = "";
        $tarihSaat = trim($tarihSaat);

        /*if($randevuid != ""){ 
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $randevu->tarih . ' ' . $randevu->saat)->addDay();
        }*/
        if($tarihSaat != "") {
            // Tarih formatını dene
            try {
                $startDate = Carbon::createFromFormat('Y-m-d H:i', $tarihSaat);
            } catch (Exception $e) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $tarihSaat);
                } catch (Exception $e) {
                    $startDate = Carbon::parse($tarihSaat);
                }
            }
            Log::info("📅 TarihSaat parametresi: " . $tarihSaat);
            Log::info("📅 Parse edilen: " . $startDate->format('Y-m-d H:i:s'));
        }
        else {
            $startDate = Carbon::now();
        }
        
        
         
        if($salonHizmet != "")  {
            $hizmetVerenPersoneller = PersonelHizmetler::whereHas('personeller', function($q) use($salon_id){
                $q->where('salon_id',$salon_id);
            })->where('hizmet_id', $salonHizmet->hizmet_id)->pluck('personel_id')->toArray();
            
            if(count($hizmetVerenPersoneller)>0)
                $personeller = Personeller::whereIn('id', $hizmetVerenPersoneller)->where('aktif', true)->orderBy('takvim_sirasi','asc')->get();/*where('role_id', 5)->*/
            else
                $personeller = Personeller::where('salon_id', $salon_id)->where('aktif', true)->orderBy('takvim_sirasi','asc')->get();/*where('role_id', 5)->*/
        }
        if($paketBilgisi != null)
        {
            $paketHizmetleri = $paketBilgisi['hizmetler'];
            $pHizmetler = array();
            foreach($paketHizmetleri as $paketHizmet)
            {
                array_push($pHizmetler,$paketHizmet['hizmet_id']);
            }
            $hizmetVerenPersoneller = PersonelHizmetler::whereHas('personeller', function($q) use($salon_id){
                $q->where('salon_id',$salon_id);
            })->whereIn('hizmet_id', $pHizmetler)->pluck('personel_id')->toArray();

            if(count($hizmetVerenPersoneller)>0)
                $personeller = Personeller::whereIn('id', $hizmetVerenPersoneller)->where('aktif', true)->orderBy('takvim_sirasi','asc')->get();/*where('role_id', 5)->*/
            else
                $personeller = Personeller::where('salon_id', $salon_id)->where('aktif', true)->orderBy('takvim_sirasi','asc')->get();/*where('role_id', 5)->*/
        }

        $tarihIncelendi = false;
        $alternatifMode = false;
        $orijinalTarihSaat = $tarihSaat;

        // TARİHSAAT VARSA - ÖNCE O SAATİ KONTROL ET; UYGUN DEĞİLSE İLERİYE DOĞRU EN YAKIN UYGUNU ARA
        if($tarihSaat != "") {
            $day = 0;
            $checkDate = $startDate->format('Y-m-d');
            $dayOfWeek = $startDate->dayOfWeek;
            if($dayOfWeek == 0) $dayOfWeek = 7;

            Log::info("🔍 Kontrol edilen gün: " . $checkDate . " - Gün: " . $dayOfWeek);

            // Gecmis tarih/saat reddi (5 dk tolerans): geriye dogru arama yapamayiz → hard fail.
            if ($startDate->lt(Carbon::now()->subMinutes(5))) {
                Log::info("❌ Gecmis tarih/saat istendi: " . $startDate->format('Y-m-d H:i'));
                return response()->json([
                    'success' => false,
                    'metin' => base64_encode('Geçmiş bir tarih ve saat için randevu oluşturamıyoruz. Lütfen ileri bir tarih ve saat söyleyin.'),
                    "tarihsaat" => $tarihSaat,
                    'alternatifOneri' => false,
                ]);
            }

            $exactResult = null;

            // Süre hesaplama (hem exact hem fallback için gerekli)
            $sureDk = 0;
            if($paketBilgisi != null) {
                if($paketBilgisi['paketSuresi'] != null) {
                    $sureDk = $paketBilgisi['paketSuresi'];
                } else {
                    foreach($paketBilgisi['hizmetler'] as $pHizmet) {
                        $salonHizmet = SalonHizmetler::where('hizmet_id',$pHizmet['hizmet_id'])->where('salon_id',$salon_id)->first();
                        $sureDk += $salonHizmet->sure_dk;
                    }
                }
            } else {
                $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
            }

            // Salon çalışma saatleri kontrolü
            $salonCalismaSaatleri = SalonCalismaSaatleri::where('salon_id', $salon_id)
                ->where('haftanin_gunu', $dayOfWeek)
                ->where('calisiyor', 1)
                ->first();

            if ($salonCalismaSaatleri) {
                $salonMolaSaatleri = SalonMolaSaatleri::where('salon_id', $salon_id)
                    ->where('haftanin_gunu', $dayOfWeek)
                    ->where('mola_var', 1)
                    ->get();

                $startSlot = $startDate->copy();
                $endSlot = $startSlot->copy()->addMinutes($sureDk);

                Log::info("⏰ Kontrol edilen slot: " . $startSlot->format('Y-m-d H:i:s') . " - " . $endSlot->format('Y-m-d H:i:s'));

                $salonStartBoundary = Carbon::parse($checkDate . " " . $salonCalismaSaatleri->baslangic_saati);
                $salonEndBoundary = Carbon::parse($checkDate . " " . $salonCalismaSaatleri->bitis_saati);
                $salonSaatUygun = !($startSlot->lt($salonStartBoundary) || $endSlot->gt($salonEndBoundary));
                $molaUygun = !($salonMolaSaatleri && $salonMolaSaatleri->count() > 0 && $this->isInBreak($startSlot, $endSlot, $salonMolaSaatleri));

                $takvimTuru = Salonlar::where('id', $salon_id)->value('randevu_takvim_turu');

                if ($salonSaatUygun && $molaUygun && $takvimTuru == 3) {
                    // ODA-TABANLI: dogrudan salonun aktif odalari uzerinden kontrol
                    $salonOdalari = Odalar::where('salon_id', $salon_id)->where('aktifmi', 1)->orderBy('takvim_sirasi', 'asc')->get();
                    Log::info("🏠 Oda listesi (aday): " . $salonOdalari->pluck('id')->implode(','));
                    foreach ($salonOdalari as $oda) {
                        if ($this->hasAppointmentConflict($oda->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) continue;
                        Log::info("✅ EXACT UYGUN (oda-based)! Oda: " . $oda->id);
                        $exactResult = [
                            'success' => true,
                            'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                            'personelid' => $oda->personel_id ?? 0,
                            'hizmetid' => $salonHizmet ? $salonHizmet->hizmet_id : "",
                            'sure' => $sureDk,
                            'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                            'randevuid' => $randevuid,
                            'odaid' => $oda->id,
                            "hizmetbulunamadi" => false,
                            'personelSecimiGerekli' => false,
                            'alternatifOneri' => false,
                        ];
                        break;
                    }
                } else if ($salonSaatUygun && $molaUygun) {
                    // PERSONEL KONTROLÜ (takvim_turu 0/1/default)
                    foreach ($personeller as $personel) {
                        $personelCalismaSaatleri = PersonelCalismaSaatleri::where('personel_id', $personel->id)
                            ->where('haftanin_gunu', $dayOfWeek)
                            ->where('calisiyor', 1)
                            ->first();

                        if (!$personelCalismaSaatleri) continue;

                        $personelStart = Carbon::parse($checkDate . " " . $personelCalismaSaatleri->baslangic_saati);
                        $personelEnd = Carbon::parse($checkDate . " " . $personelCalismaSaatleri->bitis_saati);

                        if ($startSlot->lt($personelStart) || $endSlot->gt($personelEnd)) continue;

                        $personelMolaSaatleri = PersonelMolaSaatleri::where('personel_id', $personel->id)
                            ->where('haftanin_gunu', $dayOfWeek)
                            ->where('mola_var', 1)
                            ->get();

                        $molaSaatleri = $personelMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $personelMolaSaatleri;
                        if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;

                        $odalarPersonel = OdaPersonelleri::where('personel_id', $personel->id)
                            ->where('salon_id', $salon_id)
                            ->whereHas('oda', function($q){ $q->where('aktifmi', 1); })
                            ->get()
                            ->sortBy(function($op){ return $op->oda->takvim_sirasi ?? 999999; })
                            ->values();

                        // Personelin baska bir kaynakta (oda/cihaz) cakisan randevusu var mi?
                        if ($this->hasAppointmentConflict($personel->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                            Log::info("❌ EXACT: Personel " . $personel->id . " bu slotta baska randevuda");
                            continue;
                        }

                        if ($odalarPersonel->count() > 0) {
                            foreach ($odalarPersonel as $oda) {
                                if (!$this->hasAppointmentConflict($oda->oda_id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                    Log::info("✅ EXACT UYGUN! Personel: " . $personel->id . ", Oda: " . $oda->oda_id);
                                    $exactResult = [
                                        'success' => true,
                                        'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                        'personelid' => $personel->id,
                                        'hizmetid' => $salonHizmet ? $salonHizmet->hizmet_id : "",
                                        'sure' => $sureDk,
                                        'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                        'randevuid' => $randevuid,
                                        'odaid' => $oda->oda_id,
                                        "hizmetbulunamadi" => false,
                                        'personelSecimiGerekli' => false,
                                        'alternatifOneri' => false,
                                    ];
                                    break 2;
                                }
                            }
                        } else {
                            if (!$this->hasAppointmentConflict($personel->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                Log::info("✅ EXACT UYGUN (odasız)! Personel: " . $personel->id);
                                $exactResult = [
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $personel->id,
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'randevuid' => $randevuid,
                                    'odaid' => '',
                                    "hizmetbulunamadi" => false,
                                    'personelSecimiGerekli' => false,
                                    'alternatifOneri' => false,
                                ];
                                break;
                            }
                        }
                    }

                    // CİHAZ KONTROLÜ (sadece cihaz-bazli takvimde — takvim_turu=2)
                    if ($exactResult === null && $takvimTuru == 2) {
                        foreach ($cihazlar as $cihaz) {
                            if ($salonHizmet != "") {
                                if (!CihazHizmetler::where('cihaz_id', $cihaz->id)->where('hizmet_id', $salonHizmet->hizmet_id)->exists()) continue;
                            }

                            $cihazCalismaSaatleri = CihazCalismaSaatleri::where('cihaz_id', $cihaz->id)
                                ->where('haftanin_gunu', $dayOfWeek)
                                ->where('calisiyor', 1)
                                ->first();

                            if (!$cihazCalismaSaatleri) continue;

                            $cihazStart = Carbon::parse($checkDate . " " . $cihazCalismaSaatleri->baslangic_saati);
                            $cihazEnd = Carbon::parse($checkDate . " " . $cihazCalismaSaatleri->bitis_saati);

                            if ($startSlot->lt($cihazStart) || $endSlot->gt($cihazEnd)) continue;

                            $cihazMolaSaatleri = CihazMolaSaatleri::where('cihaz_id', $cihaz->id)
                                ->where('haftanin_gunu', $dayOfWeek)
                                ->where('mola_var', 1)
                                ->get();

                            $molaSaatleri = $cihazMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $cihazMolaSaatleri;
                            if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;

                            if (!$this->hasAppointmentConflict($cihaz->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                Log::info("✅ EXACT UYGUN (cihaz)! Cihaz: " . $cihaz->id);
                                $exactResult = [
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $cihaz->id,
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'randevuid' => $randevuid,
                                    'odaid' => '',
                                    "hizmetbulunamadi" => false,
                                    'personelSecimiGerekli' => false,
                                    'alternatifOneri' => false,
                                ];
                                break;
                            }
                        }
                    }
                } else {
                    Log::info("ℹ️ Istenen slot salon saat/mola disi → en yakin uygun aranacak");
                }
            } else {
                Log::info("ℹ️ Salon " . $checkDate . " kapali → en yakin uygun aranacak");
            }

            if ($exactResult !== null) {
                return response()->json($exactResult);
            }

            // Exact bulunamadi → nearest-available aramasi icin normal akisa dus
            Log::info("🔁 Exact slot uygun degil, en yakin uygun aranacak (alternatifMode=true).");
            $alternatifMode = true;
            $tarihSaat = ""; // normal akış nearest-available scan yapsın ($startDate zaten istenen tarih/saat)
            // Caller tarihSaat varken maxDaysToCheck=1 gonderiyor; alternatif arama icin genis pencere gerekli
            if ($maxDaysToCheck < 30) {
                $maxDaysToCheck = 30;
            }
        }

        // NORMAL AKIŞ (tarihSaat yoksa veya alternatif arama) - ORJİNAL KODUN TAMAMI
        for ($day = 0; $day < $maxDaysToCheck; $day++) {
            $checkDate = Carbon::parse($startDate)->addDays($day)->toDateString();
            $dayOfWeek = Carbon::parse($checkDate)->dayOfWeek;
            if($dayOfWeek == 0) $dayOfWeek =7;

            // İşletmenin çalışma saatlerini al
            $salonCalismaSaatleri = SalonCalismaSaatleri::where('salon_id', $salon_id)->where('haftanin_gunu', $dayOfWeek)->where('calisiyor', 1)->first();

            if (!$salonCalismaSaatleri) continue;

            if($tarihSaat == '' && !$alternatifMode){
                if ($day == 0 && date('H:i') > date('H:i', strtotime($salonCalismaSaatleri->bitis_saati))) continue;
            }

            $salonMolaSaatleri = SalonMolaSaatleri::where('salon_id', $salon_id)->where('haftanin_gunu', $dayOfWeek)->where('mola_var', 1)->get();

            if ($alternatifMode && $day == 0 && $randevuid == "") {
                // Alternatif arama: istenen tarih/saat'ten basla (salon acilisina clamp)
                $istekZamani = Carbon::parse($orijinalTarihSaat);
                $salonAcilis = Carbon::parse($checkDate . " " . ($salonCalismaSaatleri->baslangic_saati ?? "09:00"));
                $salonStart = $istekZamani->gt($salonAcilis) ? $istekZamani : $salonAcilis;
            } else {
                $salonStart = $day == 0 && $randevuid=="" ? Carbon::parse(self::roundDateTimeToNearestFiveMinutes(date('H:i', strtotime('+ 1 hour', strtotime(date('Y-m-d H:i')))))) : Carbon::parse($checkDate . " " . ($salonCalismaSaatleri->baslangic_saati ?? "09:00"));
            }

            if($tarihSaat != "" && $day==0 && !$tarihIncelendi){
                $salonStart = Carbon::parse($tarihSaat);
                $tarihIncelendi = true;
            }

            $step = 15 * 60;

            if($tarihSaat != ''){
                $salonEnd = Carbon::parse($tarihSaat)->addMinutes(15);
                Log::info($salonEnd);
            }
            else
                $salonEnd = Carbon::parse($checkDate . " " . ($salonCalismaSaatleri->bitis_saati ?? "18:00"));

            if($salonStart >= $salonEnd){
                Log::info('salon start end den büyük');
                continue;
            }
            if($salonEnd->timestamp - $salonStart->timestamp < $step) {
                Log::info('step geçiyor');
                continue;
            }

            $normalTakvimTuru = Salonlar::where('id',$salon_id)->value('randevu_takvim_turu');

            foreach (range($salonStart->timestamp, $salonEnd->timestamp, $step) as $timestamp) {
                if ($normalTakvimTuru == 3) {
                    // ODA-TABANLI salon: aktif odalari gez, oda cakismasi yoksa uygun
                    $startSlot = Carbon::createFromTimestamp($timestamp);
                    $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
                    $endSlot = $startSlot->copy()->addMinutes($sureDk);

                    if ($endSlot->gt(Carbon::parse($checkDate . " " . ($salonCalismaSaatleri->bitis_saati ?? "18:00")))) continue;
                    if ($salonMolaSaatleri && $this->isInBreak($startSlot, $endSlot, $salonMolaSaatleri)) continue;

                    $salonOdalari = Odalar::where('salon_id',$salon_id)->where('aktifmi',1)->orderBy('takvim_sirasi','asc')->get();
                    Log::info("🏠 Oda listesi (aday, normal akis): " . $salonOdalari->pluck('id')->implode(','));
                    foreach ($salonOdalari as $oda) {
                        if ($randevuid != "" && is_array($eskiOdaIdler) && in_array($oda->id, $eskiOdaIdler) && $startSlot->eq($eskiBaslangic) && $endSlot->eq($eskiBitis)) {
                            continue;
                        }
                        if ($this->hasAppointmentConflict($oda->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) continue;

                        return response()->json([
                            'success' => true,
                            'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                            'personelid' => $oda->personel_id ?? 0,
                            'hizmetid' => $salonHizmet->hizmet_id ?? "",
                            'sure' => $sureDk,
                            'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                            'metin' => $randevuid != "" ? base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız") : base64_encode(($salonHizmet && $salonHizmet->hizmetler ? $salonHizmet->hizmetler->hizmet_adi : 'Hizmet'). " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " " . $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu oluşturmak istiyor musunuz?"),
                            'randevuid' => $randevuid,
                            'odaid' => $oda->id,
                            "hizmetbulunamadi" => false,
                            'personelSecimiGerekli' => false,
                            'alternatifOneri' => $alternatifMode,
                            'orijinalTarihSaat' => $orijinalTarihSaat,
                        ]);
                    }
                }
                else if ($normalTakvimTuru != 2)
                {
                    foreach ($personeller as $personel) {
                        $personelCalismaSaatleri = PersonelCalismaSaatleri::where('personel_id', $personel->id)->where('haftanin_gunu', $dayOfWeek)->where('calisiyor', 1)->first();
                        $personelMolaSaatleri = PersonelMolaSaatleri::where('personel_id', $personel->id)->where('haftanin_gunu', $dayOfWeek)->where('mola_var', 1)->get();
                        $personelStart = $personelCalismaSaatleri && $day != 0 ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->baslangic_saati) : Carbon::parse($salonStart);
                        $personelEnd = $personelCalismaSaatleri ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->bitis_saati) : Carbon::parse($salonEnd);
                        $molaSaatleri = $personelMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $personelMolaSaatleri;
                        $startSlot = Carbon::createFromTimestamp($timestamp);
                        $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
                        $endSlot = $startSlot->copy()->addMinutes($sureDk);
                        $odaId= "";

                        if ($endSlot->gt($personelEnd)) continue;
                        if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;
                        
                        // Eğer bu eski randevu ise, çakışma kontrolünde eski zamanı görmezden gel
                        if ($randevuid != "" && in_array($personel->id, $eskiPersonelIdler) /*$eskiPersonelId == $personel->id*/ && $startSlot->eq($eskiBaslangic) && $endSlot->eq($eskiBitis)) {
                            continue;
                        }

                        // Personelin baska kaynakta (oda/cihaz) cakisan randevusu var mi?
                        if ($this->hasAppointmentConflict($personel->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                            continue;
                        }

                        $odalar = OdaPersonelleri::where('personel_id',$personel->id)
                            ->where('salon_id',$salon_id)
                            ->whereHas('oda', function($q){ $q->where('aktifmi', 1); })
                            ->get()
                            ->sortBy(function($op){ return $op->oda->takvim_sirasi ?? 999999; })
                            ->values();

                        foreach($odalar as $oda) {
                            if ($this->hasAppointmentConflict($oda->oda_id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                continue;
                            }
                            else{
                                $odaId = $oda->oda_id;
                                return response()->json([
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $personel->id,
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " te uygun randevu bulunmaktadır. Randevunuzu oluşturmak istiyor musunuz?"),
                                    'randevuid'=>$randevuid,
                                    'odaid'=>$odaId,
                                    "hizmetbulunamadi"=>false,
                                    'personelSecimiGerekli'=>false,
                                    'alternatifOneri' => $alternatifMode,
                                    'orijinalTarihSaat' => $orijinalTarihSaat,
                                ]);
                            }
                        }

                        if($odalar->count()== 0){
                            if ($this->hasAppointmentConflict($personel->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                continue;
                            }
                            else
                                return response()->json([
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $personel->id,
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu  oluşturmak istiyor musunuz?"),
                                    'randevuid'=>$randevuid,
                                    'odaid'=>$odaId,
                                    "hizmetbulunamadi"=>false,
                                    'personelSecimiGerekli'=>false,
                                    'alternatifOneri' => $alternatifMode,
                                    'orijinalTarihSaat' => $orijinalTarihSaat,
                                ]);
                        }
                    }
                }
                else{
                    foreach ($cihazlar as $cihaz) {
                        if ($salonHizmet != "") {
                            if (!CihazHizmetler::where('cihaz_id', $cihaz->id)->where('hizmet_id', $salonHizmet->hizmet_id)->exists() ) {
                                continue;
                            }
                        }
                        
                        $personelCalismaSaatleri = CihazCalismaSaatleri::where('cihaz_id', $cihaz->id)->where('haftanin_gunu', $dayOfWeek)->where('calisiyor', 1)->first();
                        $personelMolaSaatleri = CihazMolaSaatleri::where('cihaz_id', $cihaz->id)->where('haftanin_gunu', $dayOfWeek)->where('mola_var', 1)->get();
                        $personelStart = $personelCalismaSaatleri && $day != 0 ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->baslangic_saati) : Carbon::parse($salonStart);
                        $personelEnd = $personelCalismaSaatleri ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->bitis_saati) : Carbon::parse($salonEnd);
                        $molaSaatleri = $personelMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $personelMolaSaatleri;
                        $startSlot = Carbon::createFromTimestamp($timestamp);
                        $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
                        $endSlot = $startSlot->copy()->addMinutes($sureDk);
                        
                        if ($endSlot->gt($personelEnd)) continue;
                        if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;
                        
                        // Eğer bu eski randevu ise, çakışma kontrolünde eski zamanı görmezden gel
                        if ($randevuid != "" && in_array($cihaz->id,$eskiCihazIdler) /*$eskiCihazId == $cihaz->id*/ && $startSlot->eq($eskiBaslangic) && $endSlot->eq($eskiBitis)) {
                            continue;
                        }

                        if ($this->hasAppointmentConflict($cihaz->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                            continue;
                        }

                        return response()->json([
                            'success' => true,
                            'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                            'personelid' => $cihaz->id,
                            'hizmetid' => $salonHizmet->hizmet_id ?? "",
                            'sure' => $sureDk,
                            'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                            'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu  oluşturmak istiyor musunuz?"),
                            'randevuid'=>$randevuid,
                            'odaid'=>'',
                            "hizmetbulunamadi"=>false,
                            'personelSecimiGerekli'=>false,
                            'alternatifOneri' => $alternatifMode,
                            'orijinalTarihSaat' => $orijinalTarihSaat,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => false,
            'metin' => base64_encode($alternatifMode ? 'İleri tarihli uygun randevu bulunamadı. Lütfen başka bir tarih ve saat söyleyin.' : 'Uygun randevu tarihi bulunamadı.'),
            "tarihsaat" => "",
            'alternatifOneri' => false,
            'orijinalTarihSaat' => $orijinalTarihSaat,
        ]);
    }
    /*public function enyakinuygunrandevubul($randevuid, $salonHizmet, $maxDaysToCheck = 30, $tarihSaat)  
    {
        $salon_id = "";
        $eskiBaslangic = null;
        $eskiBitis = null;
        $eskiPersonelId = null;
        $eskiCihazId = null;
        $olusturmaGuncelleme = "";
        $randevu="";

        if ($randevuid != "") {
            $olusturmaGuncelleme = "güncellemek";
            // Mevcut randevunun salonunu al
            $randevu = Randevular::where('id', $randevuid)->first();
            if (!$randevu) return response()->json(['success' => false, 'message' => 'Mevcut randevu bulunamadı.']);
            
            $salon_id = $randevu->salon_id;
            
            // Mevcut randevu hizmetlerini al
            $randevuHizmetleri = RandevuHizmetler::where('randevu_id', $randevuid)->first();
            if ($randevuHizmetleri) {
                $eskiBaslangic = Carbon::parse($randevuHizmetleri->baslangic_saati);
                $eskiBitis = Carbon::parse($randevuHizmetleri->bitis_saati);
                $eskiPersonelId = $randevuHizmetleri->personel_id;
                $eskiCihazId = $randevuHizmetleri->cihaz_id;
            }
        } else {
            $olusturmaGuncelleme = "oluşturmak";
            $salon_id = $salonHizmet->salon_id;
        }

        $startDate = "";
        $tarihSaat = trim($tarihSaat);

        if($randevuid != ""){ 
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $randevu->tarih . ' ' . $randevu->saat)->addDay();
        }
        elseif($tarihSaat != "") {
            // Tarih formatını dene
            try {
                $startDate = Carbon::createFromFormat('Y-m-d H:i', $tarihSaat);
            } catch (Exception $e) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $tarihSaat);
                } catch (Exception $e) {
                    $startDate = Carbon::parse($tarihSaat);
                }
            }
            Log::info("📅 TarihSaat parametresi: " . $tarihSaat);
            Log::info("📅 Parse edilen: " . $startDate->format('Y-m-d H:i:s'));
        }
        else {
            $startDate = Carbon::now();
        }
        
        $personeller = Personeller::where('salon_id', $salon_id)->where('aktif', true)->where('role_id', 5)->get();
         
        if($salonHizmet != "")  {
            $hizmetVerenPersoneller = PersonelHizmetler::whereHas('personeller', function($q) use($salon_id){
                $q->where('salon_id',$salon_id);
            })->where('hizmet_id', $salonHizmet->hizmet_id)->pluck('personel_id')->toArray();
            
            if(count($hizmetVerenPersoneller)>0)
                $personeller = Personeller::whereIn('id', $hizmetVerenPersoneller)->where('aktif', true)->where('role_id', 5)->get();
            else
                $personeller = Personeller::where('salon_id', $salon_id)->where('aktif', true)->where('role_id', 5)->get();
        }
        
        $cihazlar = Cihazlar::where('salon_id',$salon_id)->where('aktifmi',true)->where('durum',1)->get();
        $tarihIncelendi = false;

        // TARİHSAAT VARSA - DİREKT O SAATİ KONTROL ET
        if($tarihSaat != "") {
            $day = 0;
            $checkDate = $startDate->format('Y-m-d');
            $dayOfWeek = $startDate->dayOfWeek;
            if($dayOfWeek == 0) $dayOfWeek = 7;
            
            Log::info("🔍 Kontrol edilen gün: " . $checkDate . " - Gün: " . $dayOfWeek);
            
            // Salon çalışma saatleri kontrolü
            $salonCalismaSaatleri = SalonCalismaSaatleri::where('salon_id', $salon_id)
                ->where('haftanin_gunu', $dayOfWeek)
                ->where('calisiyor', 1)
                ->first();
            
            if ($salonCalismaSaatleri) {
                $salonMolaSaatleri = SalonMolaSaatleri::where('salon_id', $salon_id)
                    ->where('haftanin_gunu', $dayOfWeek)
                    ->where('mola_var', 1)
                    ->get();
                
                $startSlot = $startDate->copy();
                $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
                $endSlot = $startSlot->copy()->addMinutes($sureDk);
                
                Log::info("⏰ Kontrol edilen slot: " . $startSlot->format('Y-m-d H:i:s') . " - " . $endSlot->format('Y-m-d H:i:s'));
                
                // PERSONEL KONTROLÜ
                foreach ($personeller as $personel) {
                    $personelCalismaSaatleri = PersonelCalismaSaatleri::where('personel_id', $personel->id)
                        ->where('haftanin_gunu', $dayOfWeek)
                        ->where('calisiyor', 1)
                        ->first();
                    
                    if (!$personelCalismaSaatleri) {
                        Log::info("❌ Personel " . $personel->id . " bu gün çalışmıyor");
                        continue;
                    }
                    
                    $personelStart = Carbon::parse($checkDate . " " . $personelCalismaSaatleri->baslangic_saati);
                    $personelEnd = Carbon::parse($checkDate . " " . $personelCalismaSaatleri->bitis_saati);
                    
                    Log::info("👤 Personel " . $personel->id . " çalışma: " . $personelStart->format('H:i') . " - " . $personelEnd->format('H:i'));
                    
                    // Çalışma saatleri kontrolü
                    if ($startSlot->lt($personelStart) || $endSlot->gt($personelEnd)) {
                        Log::info("❌ Personel " . $personel->id . " çalışma saatleri dışında");
                        continue;
                    }
                    
                    // Mola kontrolü
                    $personelMolaSaatleri = PersonelMolaSaatleri::where('personel_id', $personel->id)
                        ->where('haftanin_gunu', $dayOfWeek)
                        ->where('mola_var', 1)
                        ->get();
                    
                    $molaSaatleri = $personelMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $personelMolaSaatleri;
                    
                    if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) {
                        Log::info("❌ Personel " . $personel->id . " mola saatinde");
                        continue;
                    }
                    
                    // Oda kontrolü
                    $odalar = OdaPersonelleri::where('personel_id', $personel->id)
                        ->where('salon_id', $salon_id)
                        ->get();
                    
                    if ($odalar->count() > 0) {
                        foreach ($odalar as $oda) {
                            if (!$this->hasAppointmentConflict($oda->oda_id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                Log::info("✅ UYGUN BULUNDU! Personel: " . $personel->id . ", Oda: " . $oda->oda_id);
                                
                                return response()->json([
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $personel->id,                           
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " te uygun randevu bulunmaktadır. Randevunuzu oluşturmak istiyor musunuz?"),
                                    'randevuid'=>$randevuid,
                                    'odaid'=>$oda->oda_id,
                                    "hizmetbulunamadi"=>false,
                                    'personelSecimiGerekli'=>false,
                                ]);
                            }
                        }
                    } else {
                        // Odasız personel
                        if (!$this->hasAppointmentConflict($personel->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                            Log::info("✅ UYGUN BULUNDU (odasız)! Personel: " . $personel->id);
                            
                            return response()->json([
                                'success' => true,
                                'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                'personelid' => $personel->id,                           
                                'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                'sure' => $sureDk,
                                'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu  oluşturmak istiyor musunuz?"),
                                'randevuid'=>$randevuid,
                                'odaid'=>'',
                                "hizmetbulunamadi"=>false,
                                'personelSecimiGerekli'=>false,
                            ]);
                        }
                    }
                }
                
                // CİHAZ KONTROLÜ
                foreach ($cihazlar as $cihaz) {
                    if ($salonHizmet != "") {
                        if (!CihazHizmetler::where('cihaz_id', $cihaz->id)->where('hizmet_id', $salonHizmet->hizmet_id)->exists()) {
                            continue;
                        }
                    }
                    
                    $cihazCalismaSaatleri = CihazCalismaSaatleri::where('cihaz_id', $cihaz->id)
                        ->where('haftanin_gunu', $dayOfWeek)
                        ->where('calisiyor', 1)
                        ->first();
                    
                    if (!$cihazCalismaSaatleri) continue;
                    
                    $cihazStart = Carbon::parse($checkDate . " " . $cihazCalismaSaatleri->baslangic_saati);
                    $cihazEnd = Carbon::parse($checkDate . " " . $cihazCalismaSaatleri->bitis_saati);
                    
                    if ($startSlot->lt($cihazStart) || $endSlot->gt($cihazEnd)) continue;
                    
                    $cihazMolaSaatleri = CihazMolaSaatleri::where('cihaz_id', $cihaz->id)
                        ->where('haftanin_gunu', $dayOfWeek)
                        ->where('mola_var', 1)
                        ->get();
                    
                    $molaSaatleri = $cihazMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $cihazMolaSaatleri;
                    
                    if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;
                    
                    if (!$this->hasAppointmentConflict($cihaz->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                        Log::info("✅ UYGUN BULUNDU (cihaz)! Cihaz: " . $cihaz->id);
                        
                        return response()->json([
                            'success' => true,
                            'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                            'personelid' => $cihaz->id,
                            'hizmetid' => $salonHizmet->hizmet_id ?? "",
                            'sure' => $sureDk,
                            'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                            'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu  oluşturmak istiyor musunuz?"),
                            'randevuid'=>$randevuid,
                            'odaid'=>'',
                            "hizmetbulunamadi"=>false,
                            'personelSecimiGerekli'=>false,
                        ]);
                    }
                }
                
                Log::info("❌ İstenen saatte uygun randevu bulunamadı: " . $tarihSaat);
                return response()->json(['success' => false, 'metin' => base64_encode('Belirttiğiniz saatte uygun randevu bulunamadı.'), "tarihsaat"=>$tarihSaat]);
            }
        }

        // NORMAL AKIŞ (tarihSaat yoksa) - ORJİNAL KODUN TAMAMI
        for ($day = 0; $day < $maxDaysToCheck; $day++) {
            $checkDate = Carbon::parse($startDate)->addDays($day)->toDateString();
            $dayOfWeek = Carbon::parse($checkDate)->dayOfWeek;
            if($dayOfWeek == 0) $dayOfWeek =7;
            
            // İşletmenin çalışma saatlerini al
            $salonCalismaSaatleri = SalonCalismaSaatleri::where('salon_id', $salon_id)->where('haftanin_gunu', $dayOfWeek)->where('calisiyor', 1)->first();
            
            if (!$salonCalismaSaatleri) continue;

            if($tarihSaat == ''){
                if ($day == 0 && date('H:i') > date('H:i', strtotime($salonCalismaSaatleri->bitis_saati))) continue;
            }

            $salonMolaSaatleri = SalonMolaSaatleri::where('salon_id', $salon_id)->where('haftanin_gunu', $dayOfWeek)->where('mola_var', 1)->get();

            $salonStart = $day == 0 && $randevuid=="" ? Carbon::parse(self::roundDateTimeToNearestFiveMinutes(date('H:i', strtotime('+ 1 hour', strtotime(date('Y-m-d H:i')))))) : Carbon::parse($checkDate . " " . ($salonCalismaSaatleri->baslangic_saati ?? "09:00"));
            
            if($tarihSaat != "" && $day==0 && !$tarihIncelendi){
                $salonStart = Carbon::parse($tarihSaat);
                $tarihIncelendi = true;
            }
            
            $step = 15 * 60;

            if($tarihSaat != ''){
                $salonEnd = Carbon::parse($tarihSaat)->addMinutes(15);
                Log::info($salonEnd);
            }
            else
                $salonEnd = Carbon::parse($checkDate . " " . ($salonCalismaSaatleri->bitis_saati ?? "18:00"));

            if($salonStart >= $salonEnd){
                Log::info('salon start end den büyük');
                continue;
            }
            if($salonEnd->timestamp - $salonStart->timestamp < $step) {
                Log::info('step geçiyor');
                continue;
            }
            
            foreach (range($salonStart->timestamp, $salonEnd->timestamp, $step) as $timestamp) {
                if(Salonlar::where('id',$salon_id)->value('randevu_takvim_turu')!=2 )
                {
                    foreach ($personeller as $personel) {
                        $personelCalismaSaatleri = PersonelCalismaSaatleri::where('personel_id', $personel->id)->where('haftanin_gunu', $dayOfWeek)->where('calisiyor', 1)->first();
                        $personelMolaSaatleri = PersonelMolaSaatleri::where('personel_id', $personel->id)->where('haftanin_gunu', $dayOfWeek)->where('mola_var', 1)->get();
                        $personelStart = $personelCalismaSaatleri && $day != 0 ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->baslangic_saati) : Carbon::parse($salonStart);
                        $personelEnd = $personelCalismaSaatleri ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->bitis_saati) : Carbon::parse($salonEnd);
                        $molaSaatleri = $personelMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $personelMolaSaatleri;
                        $startSlot = Carbon::createFromTimestamp($timestamp);
                        $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
                        $endSlot = $startSlot->copy()->addMinutes($sureDk);
                        $odaId= "";

                        if ($endSlot->gt($personelEnd)) continue;
                        if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;
                        
                        // Eğer bu eski randevu ise, çakışma kontrolünde eski zamanı görmezden gel
                        if ($randevuid != "" && $eskiPersonelId == $personel->id && $startSlot->eq($eskiBaslangic) && $endSlot->eq($eskiBitis)) {
                            continue;
                        }
                        
                        $odalar = OdaPersonelleri::where('personel_id',$personel->id)->where('salon_id',$salon_id)->get();
                        
                        foreach($odalar as $oda) {
                            if ($this->hasAppointmentConflict($oda->oda_id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                continue;
                            }
                            else{
                                $odaId = $oda->oda_id;
                                return response()->json([
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $personel->id,                           
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " te uygun randevu bulunmaktadır. Randevunuzu oluşturmak istiyor musunuz?"),
                                    'randevuid'=>$randevuid,
                                    'odaid'=>$odaId,
                                    "hizmetbulunamadi"=>false,
                                    'personelSecimiGerekli'=>false,
                                ]);
                            }
                        }
                        
                        if($odalar->count()== 0){
                            if ($this->hasAppointmentConflict($personel->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                                continue;
                            }
                            else
                                return response()->json([
                                    'success' => true,
                                    'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                                    'personelid' => $personel->id,                           
                                    'hizmetid' => $salonHizmet->hizmet_id ?? "",
                                    'sure' => $sureDk,
                                    'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                                    'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu  oluşturmak istiyor musunuz?"),
                                    'randevuid'=>$randevuid,
                                    'odaid'=>$odaId,
                                    "hizmetbulunamadi"=>false,
                                    'personelSecimiGerekli'=>false,
                                ]);
                        }
                    }
                }
                else{
                    foreach ($cihazlar as $cihaz) {
                        if ($salonHizmet != "") {
                            if (!CihazHizmetler::where('cihaz_id', $cihaz->id)->where('hizmet_id', $salonHizmet->hizmet_id)->exists() ) {
                                continue;
                            }
                        }
                        
                        $personelCalismaSaatleri = CihazCalismaSaatleri::where('cihaz_id', $cihaz->id)->where('haftanin_gunu', $dayOfWeek)->where('calisiyor', 1)->first();
                        $personelMolaSaatleri = CihazMolaSaatleri::where('cihaz_id', $cihaz->id)->where('haftanin_gunu', $dayOfWeek)->where('mola_var', 1)->get();
                        $personelStart = $personelCalismaSaatleri && $day != 0 ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->baslangic_saati) : Carbon::parse($salonStart);
                        $personelEnd = $personelCalismaSaatleri ? Carbon::parse($checkDate . " " . $personelCalismaSaatleri->bitis_saati) : Carbon::parse($salonEnd);
                        $molaSaatleri = $personelMolaSaatleri->isEmpty() ? $salonMolaSaatleri : $personelMolaSaatleri;
                        $startSlot = Carbon::createFromTimestamp($timestamp);
                        $sureDk = $salonHizmet ? $salonHizmet->sure_dk : RandevuHizmetler::where('randevu_id', $randevuid)->sum('sure_dk');
                        $endSlot = $startSlot->copy()->addMinutes($sureDk);
                        
                        if ($endSlot->gt($personelEnd)) continue;
                        if ($molaSaatleri && $this->isInBreak($startSlot, $endSlot, $molaSaatleri)) continue;
                        
                        // Eğer bu eski randevu ise, çakışma kontrolünde eski zamanı görmezden gel
                        if ($randevuid != "" && $eskiCihazId == $cihaz->id && $startSlot->eq($eskiBaslangic) && $endSlot->eq($eskiBitis)) {
                            continue;
                        }
                        
                        if ($this->hasAppointmentConflict($cihaz->id, $startSlot, $endSlot, $salon_id, $randevuid ?: null)) {
                            continue;
                        }
                        
                        return response()->json([
                            'success' => true,
                            'tarihsaat' => $startSlot->format('Y-m-d H:i'),
                            'personelid' => $cihaz->id,
                            'hizmetid' => $salonHizmet->hizmet_id ?? "",
                            'sure' => $sureDk,
                            'fiyat' => $salonHizmet ? $salonHizmet->baslangic_fiyat : "",
                            'metin' => $randevuid !="" ?  base64_encode(self::convertToBugunYarin($randevu->tarih)." saat ".$randevu->saat ." randevunuzu " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " saat " . $startSlot->format('H:i') . " olarak güncelleyebiliriz. Randevunuzu güncellemek için biri, operatöre bağlanmak için ikiyi tuşlayınız")  : base64_encode($salonHizmet->hizmetler->hizmet_adi. " için " . self::convertToBugunYarin($startSlot->format('Y-m-d')) . " ". $startSlot->format('H:i') . " saatinde uygun randevu bulunmaktadır. Randevunuzu  oluşturmak istiyor musunuz?"),
                            'randevuid'=>$randevuid,
                            'odaid'=>'',
                            "hizmetbulunamadi"=>false,
                            'personelSecimiGerekli'=>false,
                        ]);
                    }
                }
            }
        }

        return response()->json(['success' => false, 'metin' => base64_encode('Uygun randevu tarihi bulunamadı.'), "tarihsaat"=>""]);
    }*/
    public function telefonGizle($phone) {
        return substr($phone, 0, 3) . ' *** **' . substr($phone, -2);
    }
    public function ePostaGizle($email) {
        [$username, $domain] = explode('@', $email);
        $maskedUsername = substr($username, 0, 1) . str_repeat('*', max(strlen($username) - 1, 3));
        
        $domainParts = explode('.', $domain);
        $maskedDomain = substr($domainParts[0], 0, 1) . str_repeat('*', max(strlen($domainParts[0]) - 1, 3));
        $extension = isset($domainParts[1]) ? '.' . $domainParts[1] : '';

        return $maskedUsername . '@' . $maskedDomain . $extension;
    }
    public function kullaniciRolu($salonId,$userId)
    {
        return DB::table('model_has_roles')->where('model_id',$userId)->where('salon_id',$salonId)->value('role_id');
    }
     
    
    function getFirebaseAccessToken($firebasePath)
    {
        $jsonPath = storage_path($firebasePath);
        $json = json_decode(file_get_contents($jsonPath), true);
    
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
    
        $now = time();
        $claim = [
            'iss'   => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $json['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600
        ];
    
        $base64url = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };
    
        $jwtHeader = $base64url(json_encode($header));
        $jwtClaim  = $base64url(json_encode($claim));
    
        $data = $jwtHeader . '.' . $jwtClaim;
    
        $privateKey = $json['private_key'];
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    
        $jwt = $data . '.' . $base64url($signature);
    
        $client = new Client();
        $response = $client->post($json['token_uri'], [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt
            ]
        ]);
    
        if ($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
            return $body['access_token'];
        }
    
        throw new \Exception('Firebase access token alınamadı: ' . $response->getBody()->getContents());
    }
    
    function sendFcmMessage($firebaseJsonFile, $deviceToken, $title, $body, $data = [])
    {
        $accessToken = self::getFirebaseAccessToken($firebaseJsonFile);
    
        $projectId = json_decode(file_get_contents(storage_path($firebaseJsonFile)), true)['project_id'];
    
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    
        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'HIGH'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default'
                        ]
                    ]
                ]
            ]
        ];
    
        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ],
            'json' => $message
        ]);
    
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('FCM mesaj gönderilemedi: ' . $response->getBody()->getContents());
        }
    
        return json_decode($response->getBody()->getContents(), true);
    }

}
