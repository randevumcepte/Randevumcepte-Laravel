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
		 	$bildirimkimlikleri = BildirimKimlikleri::whereIn('isletme_yetkili_id',Personeller::where('id',$liste->personel_id)->pluck('yetkili_id')->toArray())->pluck('bildirim_id')->toArray(); 

		 	$mesaj = date('d.m.Y',strtotime($aranacak->tarih))." ".date('H:i',strtotime($aranacak->saat))." de ".$aranacak->musteri->name ." isimli müşteri tekrar aranacak.";
		 	self::bildirimgonder($bildirimkimlikleri,"Tekrar Arama Hatırlatma",$mesaj,$liste->salon_id,'12d6537e-7a7d-4d1d-a838-e3fc947eaf44','5e50f84e-2cd8-4532-a765-f2cb82a22ff9','os_v2_app_lzipqtrm3bctfj3f6lfyfirp7ghx6w4i7t6e6iufqzlj6ginpkucdwamtgxy5bclne737yh7y62zxlfmep2c4ijioiimrps4jcq5ysi');
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
    public function bildirimgonder($bildirimkimlikleri,$baslik,$mesaj,$salonid,$channelid,$appid,$key)
    {
        $salon = Salonlar::where('id',$salonid)->first();
        $post_url_push_notification = "https://api.onesignal.com/notifications?c=push";

        $headers_push_notification = array(
                                        'Accept: application/json',
                                        'Authorization: Key '.$key,
                                        'Content-Type: application/json',
        );

         
        $post_data_push_notification = 
            json_encode( 
            
                array( 
                    "app_id"=> $appid,
                 
                    "include_player_ids" =>  $bildirimkimlikleri,
                    "android_channel_id" => $channelid,
                    "contents" => array("en"=>  $mesaj),
                    "headings" =>  array("en"=> $baslik),
                    "sound" => "default",
                    'url'=>"https://app.randevumcepte.com.tr/isletmeyonetim/santral?sube=".$salonid,
                     
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

}