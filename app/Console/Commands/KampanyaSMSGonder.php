<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\KampanyaYonetimi;
use App\KampanyaKatilimcilari;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\SalonSMSAyarlari;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
class KampanyaSMSGonder extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kampanyasms:gonder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kampanya SMS hatırlatmaları';

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
    	$controller = app()->make(Controller::class);

		// Kampanyaları alın (örnek satır, kendi sorgunuza göre açabilirsiniz)
		$kampanyalar = KampanyaYonetimi::where('sms_tarih_saat','<=', date('Y-m-d H:i:s'))->where('aktifmi',1)->where('sms_ile_gonderim',1)->where(function($q){
			$q->where('sms_gonderildi',null);
			$q->orWhere('sms_gonderildi','!=',1);

		})->get();
			
		
		foreach ($kampanyalar as $kampanya) {
			 
			
			 
					
			        $mesajlar = [];      
			        foreach ($kampanya->kampanya_katilimcilari as $katilimci) {

			            // Katılımcı kara listede değilse
			            $karaListedeMi = MusteriPortfoy::where('user_id', $katilimci->user_id)
			                ->where('salon_id', $kampanya->salon_id)
			                ->value('kara_liste');


			            if ($karaListedeMi != 1) {
			            	$katilim_link = '';
			                if(SalonSMSAyarlari::where('ayar_id',10)->where('salon_id',$kampanya->salon_id)->value('musteri')==1)
			                    $katilim_link = ' Katılım için : https://'.$kampanya->salon->domain.'/kampanyakatilim/'.$kampanya->id.'/'.$katilimci->user_id;
			            	$parametre = [
			            		'to'=>$katilimci->musteri->cep_telefon,
			            		'message'=>$kampanya->mesaj.$katilim_link,
			            	];
			            	array_push($mesajlar,$parametre);
			            	Log::info('sms için müşteri kaydedildi');		
			               
			            }
			        }
			        if (count($mesajlar) > 0) {
			    
					    $controller->sms_gonder($kampanya->salon_id,$mesajlar); 
					    $kampanya->sms_gonderildi = true;
					    $kampanya->save();
					}  
			   
			

			
		   
		}

		

    }

}