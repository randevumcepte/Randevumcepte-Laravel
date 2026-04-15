<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Alacaklar;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Salonlar;
class AlacakHatirlatmaAramasiYap extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alacakhatirlatma:aramayap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alacak Teyit Aramaları';

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
    	/*$controller = app()->make(Controller::class);

		$alacaklar = Alacaklar::where(function ($q) use($controller) {
                    $q->where(function ($q2) use($controller) {
                            if($controller->hatirlatmaSaatiIcinde(date('H:i')))
                                $q2->where('planlanan_odeme_tarihi', date('Y-m-d', strtotime('+2 days')))
                                    ->where(function ($q3) {
                                    $q3->whereNull('hatirlatma_aramasi_yapildi')
                                       ->orWhere('hatirlatma_aramasi_yapildi', '!=', 1);
                                });
                        
                    });

                    $q->orWhere(function ($q2) {
                        $q2->where('hatirlatma_aramasi_yapildi', 1)
                            ->where('hatirlatma_ulasilamadi', 1)
                            ->where('tekrar_arama_tarih_saat', 'like', '%' . date('Y-m-d H:i') . '%');
                    });
                    $q->orWhere(function ($q2) {
                            
                        $q2->where(function ($q3) {
                                    $q3->whereNull('hatirlatma_aramasi_yapildi')
                                       ->orWhere('hatirlatma_aramasi_yapildi', '!=', 1);
                        });
                        $q2->where('tekrar_arama_tarih_saat', 'like', '%' . date('Y-m-d H:i') . '%');
                        
                    }); 
                })
                ->where(function ($q) {
                    $q->whereNull('hatirlatma_gorevi_iptal')
                      ->orWhere('hatirlatma_gorevi_iptal', '!=', 1);
                })->where(function($q){
                    $q->whereNull('tekrar_arandi')->orWhere('tekrar_arandi','!=',1);
                })->get();   
		$controller = app()->make(Controller::class);
        $hatirlatmaAramaParametre = [];
        $alacakIdler = [];
        $mesaj = "";
        if($alacaklar->count()>0)
        {

                    $metin = "";
                    $tarihler = "";
                    $toplamtutar = 0;

                    foreach ($alacaklar as $key => $alacak) { 
                    	array_push($alacakIdler,$alacak->id);
 						$karaListedeMi = MusteriPortfoy::where('user_id', $alacak->user_id)
			                ->where('salon_id', $alacak->salon_id)
			                ->value('kara_liste');
			            if(!$karaListedeMi)

                        $toplamtutar += $alacak->tutar;

                        // **Hata düzeltilmesi: `$key = 0` yanlış, `===` kullanılmalı**
                        if ($key === 0) {
                            $tarihler .= $alacak->planlanan_odeme_tarihi;
                        } elseif ($key > 0 && $alacaklar[$key]->planlanan_odeme_tarihi != $alacaklar[$key - 1]->planlanan_odeme_tarihi && $key != $alacaklar->count() - 1) {
                            $tarihler .= ", " . $alacak->planlanan_odeme_tarihi;
                        } elseif ($key == $alacaklar->count() - 1) {
                            $tarihler .= " ve " . $alacak->planlanan_odeme_tarihi;
                        }
                        if(!$controller->hatirlatmaSaatiIcinde(date('H:i')))
                        {
                            if(date("H:i") > date("H:i",strtotime("19:30")))
                                $alacak->tekrar_arama_tarih_saat = date("Y-m-d",strtotime("+ 1 days", strtotime(date("Y-m-d"))))." 10:00:00";
                            else
                                $alacak->tekrar_arama_tarih_saat = date("Y-m-d"). " 10:00:00";
                            $alacak->save();
                        }


                    }
                    
                    $mesaj = "Sayın " . $alacak->musteri->name . ". Sizi ".$alacak->salon->salon_adi." adına arıyorum. " . $tarihler . 
                        " tarihinde ödemeniz gereken toplam " . $toplamtutar . 
                        " TL borcunuz bulunmaktadır. Ödemeyi bu tarihte gerçekleştirecekseniz biri, vade güncelleme yapmak istiyorsanız operatöre bağlanmak için ikiyi tuşlayınız.";
        }
        foreach ($alacaklar as $key => $alacak) {
             $karaListedeMi = MusteriPortfoy::where('user_id', $alacak->user_id)
			                ->where('salon_id', $alacak->salon_id)
			                ->value('kara_liste');

            if(!$karaListedeMi && SalonEAsistanAyarlari::where('salon_id', $alacak->salon_id)->where('ayar_id', 1)->value('acik_kapali')){ 
  	

	                    if($controller->hatirlatmaSaatiIcinde(date('H:i'))){
	                        $parametreler = [
	                                "alacakIdler" => $alacakIdler,
	                                "randevuid" => "",
	                                "kampanyaKatilimci" => "",
	                                "katilimci" => "",
	                                "mesaj" => $mesaj,
	                                "tel" => $alacak->musteri->cep_telefon,
	                                "salonId" => $alacak->salon_id,
	                                "exten" => 1,
	                            ];
	                        array_push($hatirlatmaAramaParametre,$parametreler);
	                    }
	                    else
	                    {
                            if(date("H:i") > date("H:i",strtotime("19:30")))
                                $alacak->tekrar_arama_tarih_saat = date("Y-m-d",strtotime("+ 1 days", strtotime(date("Y-m-d"))))." 10:00:00";
                            else
                                $alacak->tekrar_arama_tarih_saat = date("Y-m-d"). " 10:00:00";
                            $alacak->save();
                        
	                    }
	                         
	                   
            }
            

        }
        if(count($hatirlatmaAramaParametre)>0){
        	Log::info('Alacak araması başlatılıyor');
            $controller->hatirlatmaaramasiyap($hatirlatmaAramaParametre);
        }
        else
        	Log::info('Arama yapılacak alacaklı bulunmadı.');*/


    }

}