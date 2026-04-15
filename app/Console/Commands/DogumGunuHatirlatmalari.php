<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\User;
use App\Salonlar;
use App\SalonSMSAyarlari;
use App\IsletmeYetkilileri;
use App\MusteriPortfoy;

class DogumGunuHatirlatmalari extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dogumgunu:hatirlatmalari';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        
         
            
        
         
           
         

        
    }
    public function sms_gonder($salonid,$mesajlar)
    {
        $isletme = Salonlar::where('id',$salonid)->first();
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

      

       
    } 
}
