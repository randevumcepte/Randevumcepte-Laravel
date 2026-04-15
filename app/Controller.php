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
use App\Imports\SatisImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
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
   protected function hasAppointmentConflict($personelId, $startSlot, $endSlot,$salonId)
    {

        return $randevu = Randevular::where('tarih', date('Y-m-d', strtotime($startSlot)))
    ->whereHas('hizmetler', function ($q) use ($startSlot, $endSlot, $personelId) {
        $startTime = $startSlot->format('H:i');
        $endTime = $endSlot->format('H:i'); 

        $q->where('personel_id', $personelId);
        $q->where(function ($q2) use ($startTime, $endTime) {
            $q2->where(function ($q3) use ($startTime, $endTime) {
                // Yeni randevu, mevcut randevunun içinde mi?
                $q3->where('saat', '<=', $startTime)
                   ->where('saat_bitis', '>=', $endTime);
            })->orWhere(function ($q3) use ($startTime, $endTime) {
                // Yeni randevu, mevcut randevuyu tamamen kapsıyor mu?
                $q3->where('saat', '>=', $startTime)
                   ->where('saat_bitis', '<=', $endTime);
            })->orWhere(function ($q3) use ($startTime, $endTime) {
                // Yeni randevu, mevcut randevunun başlangıcına denk geliyor mu?
                $q3->whereBetween('saat', [$startTime, $endTime]);
            })->orWhere(function ($q3) use ($startTime, $endTime) {
                // Yeni randevu, mevcut randevunun bitişine denk geliyor mu?
                $q3->whereBetween('saat_bitis', [$startTime, $endTime]);
            });
        });
    })
    ->where('durum', '<=', 1)
    ->where('salon_id', $salonId)
    ->exists();
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

            if ($startSlot->between($molaStart, $molaEnd) || $endSlot->between($molaStart, $molaEnd)) {
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
            $randevuHizmet->personel_id = $request->randevuPersonelleri[$key2];
            if(isset($request->randevuOdalari))
                $randevuHizmet->oda_id = $request->randevuOdalari[$key2];
            if(isset($request->randevuCihazlari))
                $randevuHizmet->cihaz_id = $request->randevuCihazlari[$key2];
           
            $randevuHizmet->fiyat = $request->hizmetFiyati[$key2];
            $randevuHizmet->sure_dk = $request->hizmetSuresi[$key2];
            $birsonraki = $key2+1;
            if($key2 == 0){
                $randevuHizmet->saat = $request->saat;
                $randevuHizmet->saat_bitis = date("H:i", strtotime('+'.$request->hizmetSuresi[$key2].' minutes', strtotime($request->saat)));
                if(!isset($request->{"birlestir{$birsonraki}"}))
                    $yeniSaatBaslangic = date("H:i", strtotime('+'.$request->hizmetSuresi[$key2].' minutes', strtotime($request->saat)));
            }
            else{
                $randevuHizmet->saat = $yeniSaatBaslangic;
                $randevuHizmet->saat_bitis = date("H:i", strtotime('+'.$request->hizmetSuresi[$key2].' minutes', strtotime($yeniSaatBaslangic)));
                if(!isset($request->{"birlestir{$birsonraki}"}))
                    $yeniSaatBaslangic = date("H:i", strtotime('+'.$request->hizmetSuresi[$key2].' minutes', strtotime($yeniSaatBaslangic)));
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
    public function hatirlatmaaramasiyap($salonid,$telefon,$mesaj,$randevuid,$alacakIdler,$kampanyaKatilimci,$exten)
    {   
        $randevu = "";
        $alacaklarJson = "";
        $katilimci = array();
        if($randevuid != "")
            $randevu = Randevular::where('id',$randevuid)->first();
        if($alacakIdler != "")
            $alacaklarJson = json_encode($alacakIdler); 
        $salon = Salonlar::where('id',$salonid)->first();
        $sabitno = SabitNumaralar::where('salon_id',$salonid)->first();
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
        $aranacakTelefonlar = array();
        $i = 0;
        if(!is_array($telefon))
        {
            array_push($katilimci,$kampanyaKatilimci[$i]);
            array_push($aranacakTelefonlar,$telefon);
            $i++;
        }
        else
            $aranacakTelefonlar = $telefon;
        // Context for outbound calls. See /etc/asterisk/extensions.conf if unsure.
        $context = "from-internal-custom";   
         
        $socket = stream_socket_client("tcp://34.45.69.65:$port");
        if($socket)
        {
          
            Log::info('Connected to socket, sending authentication request.');
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
                    Log::info('Authenticated to Asterisk Manager Inteface. Initiating call.');
                    
                    foreach($aranacakTelefonlar as $key=>$tel)
                    {
                        // Prepare originate request
                        $originateRequest = "Action: Originate\r\n";
                        

                        //$originateRequest .= "Channel: Local/".$exten."@from-internal-custom\r\n";
                        $originateRequest .= "Channel: PJSIP/0".$tel."@".$sabitno->numara."\r\n";
                        $originateRequest .= "Callerid: ".$sabitno->numara."\r\n";
                        $originateRequest .= "Exten: ".$exten."\r\n";  // 1 numaralı uzantıya yönlendirme
                        $originateRequest .= "Context: $context\r\n";  // Asterisk bağlamı (context)
                        $originateRequest .= "Variable: myMessage=".base64_encode($mesaj).",kuyruk=".$salon->operator_kanali.",alacaklar=".$alacaklarJson.",katilimci=".$katilimci[$key].",kampanyakatilimci=".$katilimci[$key].",randevuid=".$randevuid.",telefon=".$tel.",sabitno=".$sabitno->numara."\r\n";  // Çağrıya özel değişkenler


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

                            // Check if originate was successful
                            if(strpos($originateResponse, 'Success') !== false)
                            {
                                Log::info('Call initiated, dialing.');

                                
                                if($alacakIdler != "")
                                    self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,1);
                                elseif($kampanyaKatilimci != ""){
                                    self::kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,1);
                                    self::kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,1);
                                }
                                else{
                                     self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,1);
                                }
                            } 
                            else {
                               
                                Log::info("Could not initiate call. Response: " . $originateResponse);
                               
                                if($alacakIdler != "")
                                    self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
                                elseif($kampanyaKatilimci != ""){
                                    self::kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,0);
                                    self::kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,0);
                                }
                                else{
                                    self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
                                } 
                            }
                        } 
                        else 
                        {
                            Log::info('Could not write call initiation request to socket.');
                           
                            if($alacakIdler != "")
                                self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
                            elseif($kampanyaKatilimci != ""){
                                self::kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,0);
                                self::kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,0);
                            }
                            else{
                                self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
                            }
                            
                        }
                    }
                    
                } 
                else {
                     Log::info('Could not authenticate to Asterisk Manager Interface.');
                     
                    if($alacakIdler != "")
                        self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
                    elseif($kampanyaKatilimci != ""){
                        self::kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,0);
                        self::kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,0);
                    }
                    else{
                       self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
                    }
                     
                }
            } 
            else {
                Log::info('Could not write authentication request to socket.');
              
                if($alacakIdler != "")
                    self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
                elseif($kampanyaKatilimci != ""){
                    self::kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,0);
                    self::kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,0);
                } 
                else{
                     self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
                }
                
            }
        } else {
            Log::info('Unable to connect to socket.');
           
            if($alacakIdler != "")
                self::alacakHatırlatmaAramasiYapildiIsaretle($alacakIdler,0);
            elseif($kampanyaKatilimci != ""){
                self::kampanyaTanitimYapildiIsaretle($kampanyaKatilimci,0);
                self::kampanyaHatirlatmaAramasiYapildiIsaretle($kampanyaKatilimci,0);
            }
               
            else{
                 self::randevuHatirlatmaAramasiYapildiIsaretle($randevu->id,0);
            }
            
        }
        if($randevu != "")
            $randevu->save();
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
        $kampanya = KampanyaYonetimi::where('id',KampanyaKatilimcilari::where('id',$kampanyaKatilimci)->value('kampanya_id'))->first();
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
            $katilimci_duzenlenecek->tekrar_arandi = 0;
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
    public function kampanyaSMSileGonder($kampanya)
    {   
        foreach($kampanya->kampanya_katilimcilari as $katilimci)
        {
             
        }
    }

    public function kampanyaAramaIleGonder($kampanya,$katilimcilar)
    {
        Log::info('kampanya arama ile gönderiyor fonksiyon içine girildi.');
        
        $katilimciTel = array();
        $katilimciIdler = array();
        foreach($katilimcilar as $katilimci)
        {    
            if (!self::musteriKaraListedemi($katilimci->user_id, $kampanya->salon_id) 
                && ($katilimci->asistan_ulasamadi == 1 || $katilimci->asistan_ulasamadi == null) 
                && ($katilimci->tekrar_arama_tarih_saat == date('Y-m-d H:i') || $katilimci->tekrar_arama_tarih_saat == null || $katilimci->tekrar_arama_tarih_saat == "0000-00-00 00:00:00") 
                && (
                    ($katilimci->tekrar_arandi != 1 || $katilimci->tekrar_arandi == null) 
                    || ($kampanya->tekrar_aranacak == 1 || $kampanya->tekrar_aranacak == null)
                )
            ) {
                array_push($katilimciTel, $katilimci->musteri->cep_telefon);
                array_push($katilimciIdler, $katilimci->id);
                Log::info($katilimci->musteri->name . ' kara listede değil. hatırlatma arama döngü içine girildi.');
            }

        }

        if (count($katilimciTel) > 0) {
            self::hatirlatmaaramasiyap(
                $kampanya->salon_id,
                $katilimciTel,
                $kampanya->mesaj . " Kampanyaya katılmak istiyorsanız biri, istemiyorsanız ikiyi tuşlayınız.",
                "",
                "",
                $katilimciIdler,
                3
            );
        }
        
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
        $isletme = $salonid != '' ? Salonlar::where('id',$salonid)->first() : '';

        $headers = array(
             'Authorization: Key '.$salonid != '' ? $isletme->sms_apikey : 'LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5',
             'Content-Type: application/json',
             'Accept: application/json'
        );
        $postData = json_encode( array( "originator"=> $salonid != '' ? $isletme->sms_baslik : 'RANDVMCEPTE', "messages"=> $mesajlar,"encoding"=>"auto") );

        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/sms/multi');
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers); 
        $response=curl_exec($ch); 
        $decoded = json_decode($response,true);
        if(count($decoded["response"])!=0 && $decoded != null){ 
                $rapor = new SMSIletimRaporlari();
                $rapor->salon_id = $salonid;
                $rapor->tur = 1;
                $rapor->aciklama = $mesajlar[0]['message'];
                $rapor->rapor_id = $decoded["response"]["message"]["id"];
                $rapor->adet = $decoded["response"]["message"]["count"];
                $rapor->kredi = $decoded["response"]["message"]["total_price"];
                sleep(1);
                $durum = self::sms_rapor_getir($decoded["response"]["message"]["id"],$isletme);
                $rapor->durum = $durum["response"]["message"]["status"];
                $rapor->save(); 
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
        $path = "data-aktarim/drklinik/".$dosyaAdi;
        $fullPath = storage_path("app/" . $path);

        try {
            $import = new SatisImport($salonid);
            $result = Excel::import($import, $dosyaAdi);
            dd("İşlem başarılı", $result);
        } catch (\Exception $e) {
            dd("Import sırasında hata oluştu", $e->getMessage(), $e->getTraceAsString());
        }
    }
    

   
}
