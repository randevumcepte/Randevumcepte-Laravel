<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\AramaListesi;
use App\AranacakMusteriler;
use App\Bildirimler;
use App\BildirimKimlikleri;
use App\Salonlar;
use App\Personeller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
class TekrarAramaHatirlat extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arama:hatirlat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arama Hatırlatmaları';

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
    	 
		 $aranacaklar = AranacakMusteriler::where('tarih',date('Y-m-d'))->where('saat',date('H:i:00'))->get();
		 foreach ($aranacaklar as $aranacak)
		 {
		 	$liste = AramaListesi::where('id',$aranacak->arama_id)->first();
		 	$yetkiliIdleri = Personeller::where('id',$liste->personel_id)->pluck('yetkili_id')->toArray();

		 	$mesaj = date('d.m.Y',strtotime($aranacak->tarih))." ".date('H:i',strtotime($aranacak->saat))." de ".$aranacak->musteri->name ." isimli müşteri tekrar aranacak.";

		 	foreach ($yetkiliIdleri as $yid) {
		 		try {
		 			\App\Services\NotificationService::toStaff((int) $yid, (int) $liste->salon_id)
		 				->type(\App\Services\NotificationTypes::SYSTEM_ANNOUNCEMENT)
		 				->title('Tekrar Arama Hatırlatma')
		 				->body($mesaj)
		 				->send();
		 		} catch (\Throwable $e) {
		 			Log::warning('[TEKRAR-ARAMA] push fail', ['yetkili_id' => $yid, 'err' => $e->getMessage()]);
		 		}
		 	}

		 	self::bildirimekle($liste->salon_id,$mesaj,'#',$liste->personel_id,$aranacak->user_id,$aranacak->musteri->profil_resim,null,null);


		 }


    }
    public function bildirimekle($salonid,$mesaj,$url,$personelid,$musteriid,$imgurl,$randevuid,$satisortagiid)
    {
        $bildirim = new Bildirimler();
        $bildirim->aciklama = $mesaj;
        $bildirim->salon_id = $salonid;
        $bildirim->personel_id = $personelid;
        $bildirim->satis_ortagi_id = $satisortagiid;
        $bildirim->url = $url;
        $bildirim->tarih_saat = date('Y-m-d H:i:s');
        $bildirim->okundu = false;
        $bildirim->user_id = $musteriid;
        $bildirim->img_src = $imgurl;
        $bildirim->randevu_id = $randevuid;
        $bildirim->save();
    }
}