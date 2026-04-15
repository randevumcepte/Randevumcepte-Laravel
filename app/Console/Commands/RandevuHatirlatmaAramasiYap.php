<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Randevular;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Salonlar;
class RandevuHatirlatmaAramasiYap extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'randevuarama:yap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Randevu Teyit Aramaları';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
    	Log::info("randevu hatırlatma araması işi başlatılıyor");
    	$controller = app()->make(Controller::class);
 		//whereBetween('tarih',[date('Y-m-d'),date('Y-m-d',strtotime('+ 1 days',strtotime(date('Y-m-d'))))])
		$randevular = Randevular::has('hizmetler')->where('durum',1)->where('user_id','!=',2012)->where(function($q){
			$q->where(function($q2){
				$q2->where('tarih',date('Y-m-d',strtotime('+1 days',strtotime(date('Y-m-d')))))->where('saat',date('H:i:00'));
			});
			$q->orWhere(function($q2){
				$q2->where('tekrar_arama_tarih_saat',date('Y-m-d H:i:00'))->where('tekrar_aranacak',true);
			});
		})->get();     
		Log::info('hatırlatma yapılacak randevu sayısı '.$randevular->count()); 	 
		$controller = app()->make(Controller::class);
        $hatirlatmaAramaParametre = [];
        foreach ($randevular as $key => $value) {
             $karaListedeMi = MusteriPortfoy::where('user_id', $value->user_id)
			                ->where('salon_id', $value->salon_id)
			                ->value('kara_liste');

            if( !$karaListedeMi){ 

	           	if(SalonEAsistanAyarlari::where('salon_id',$value->salon_id)->where('ayar_id',4)->value('acik_kapali') && ($value->tekrar_arandi != true || $value->tekrar_arandi == null ))
	          	
   				{		

	            	 
			            	 
	                if (
	                    (date('d.m.Y H:i') == date('d.m.Y H:i', strtotime('-24 hours', strtotime($value->tarih." ".$value->saat))) || date('d.m.Y H:i', strtotime($value->tekrar_arama_tarih_saat)) == date('d.m.Y H:i'))
	                     && ($value->hatirlatma_gorevi_iptal !== true || $value->hatirlatma_gorevi_iptal === null) 
	                     && ($value->tekrar_arandi !== true || $value->tekrar_arandi === null)
	                      && ($value->tekrar_aranacak === true || $value->tekrar_aranacak === null)
	                )
	                {

	                    if($controller->hatirlatmaSaatiIcinde(date('H:i'))){
	                        $parametreler = [
	                                "alacakIdler" => "",
	                                "randevuid" => $value->id,
	                                "kampanyaKatilimci" => "",
	                                "katilimci" => "",
	                                "mesaj" => "Sayın ".$value->users->name. ". ".Salonlar::where('id',$value->salon_id)->value('santral_telaffuz_2'). " için ".$value->tarih." saat ".date('H:i',strtotime($value->saat))." randevunuzu hatırlatmak isteriz. Randevuya gelecekseniz biri, randevunuzu başka bir tarihe ertelemek istiyorsanız ikiyi tuşlayınız.",
	                                "tel" => $value->users->cep_telefon,
	                                "salonId" => $value->salon_id,
	                                "exten" => 1,
	                            ];
	                        array_push($hatirlatmaAramaParametre,$parametreler);
	                    }
	                    else
	                    {
                            if(date("H:i") > date("H:i",strtotime("19:30")))
                                $value->tekrar_arama_tarih_saat = date("Y-m-d",strtotime("+ 1 days", strtotime(date("Y-m-d"))))." 10:00:00";
                            else
                                $value->tekrar_arama_tarih_saat = date("Y-m-d"). " 10:00:00";
                            $value->save();
                        
	                    }
	                        
	                        
	                }
	               
	            }       
            }
            

        }
        if(count($hatirlatmaAramaParametre)>0){
        	 
            $controller->hatirlatmaaramasiyap($hatirlatmaAramaParametre);
            Log::info("randevu hatırlatma araması işi bitti");
        }
        else{
        	 
        	Log::info("randevu hatırlatma araması yok");
        }


    }

}