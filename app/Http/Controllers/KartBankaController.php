<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\param\GetInstallmentPlanForMerchant;
 use App\param\GetInstallmentPlanForUser;
 use App\KartKomisyonOranlari;
class KartBankaController extends Controller
{
     public function __construct()
    {
        
 
         $this->middleware('guest', [ 'except' => 'logout' ]);
    }
    public function kartbilgiler(){
         $saleObj = new GetInstallmentPlanForUser('16577', 'TP10033903', '69E21AD2FFCE19C9','CCAD72EE-F1D2-4C89-B497-4B9A34F75ECF', 'TEST');
         $saleObj->send();
          $paramResponse =  $saleObj->parse();
          foreach ($paramResponse as $komisyonlar) {
            foreach ($komisyonlar as $komisyonoranlar) {
                var_dump($komisyonoranlar);
                $komisyoneskibilgi = KartKomisyonOranlari::where('id',$komisyonoranlar['SanalPOS_ID'])->first();
                if($komisyoneskibilgi)
                {
                      $komisyoneskibilgi->id = $komisyonoranlar['SanalPOS_ID'];
                    $komisyoneskibilgi->Kredi_Karti_Banka = $komisyonoranlar['Kredi_Karti_Banka'];
                    $komisyoneskibilgi->Kredi_Karti_Banka_Gorsel = $komisyonoranlar['Kredi_Karti_Banka_Gorsel'];
                    $komisyoneskibilgi->MO_01 = $komisyonoranlar['MO_01'];
                    $komisyoneskibilgi->MO_02 = $komisyonoranlar['MO_02'];
                    $komisyoneskibilgi->MO_03 = $komisyonoranlar['MO_03'];
                    $komisyoneskibilgi->MO_04 = $komisyonoranlar['MO_04'];
                    $komisyoneskibilgi->MO_05 = $komisyonoranlar['MO_05'];
                    $komisyoneskibilgi->MO_06 = $komisyonoranlar['MO_06'];
                    $komisyoneskibilgi->MO_07 = $komisyonoranlar['MO_07'];
                    $komisyoneskibilgi->MO_08 = $komisyonoranlar['MO_08'];
                    $komisyoneskibilgi->MO_09 = $komisyonoranlar['MO_09'];
                    $komisyoneskibilgi->MO_10 = $komisyonoranlar['MO_10'];
                    $komisyoneskibilgi->MO_11 = $komisyonoranlar['MO_11'];
                    $komisyoneskibilgi->MO_11 = $komisyonoranlar['MO_12'];
                    $komisyoneskibilgi->save();
                }
                else{
                      $komisyonbilgi = new KartKomisyonOranlari();
                    $komisyonbilgi->id = $komisyonoranlar['SanalPOS_ID'];
                    $komisyonbilgi->Kredi_Karti_Banka = $komisyonoranlar['Kredi_Karti_Banka'];
                    $komisyonbilgi->Kredi_Karti_Banka_Gorsel = $komisyonoranlar['Kredi_Karti_Banka_Gorsel'];
                    $komisyonbilgi->MO_01 = $komisyonoranlar['MO_01'];
                    $komisyonbilgi->MO_02 = $komisyonoranlar['MO_02'];
                    $komisyonbilgi->MO_03 = $komisyonoranlar['MO_03'];
                    $komisyonbilgi->MO_04 = $komisyonoranlar['MO_04'];
                    $komisyonbilgi->MO_05 = $komisyonoranlar['MO_05'];
                    $komisyonbilgi->MO_06 = $komisyonoranlar['MO_06'];
                    $komisyonbilgi->MO_07 = $komisyonoranlar['MO_07'];
                    $komisyonbilgi->MO_08 = $komisyonoranlar['MO_08'];
                    $komisyonbilgi->MO_09 = $komisyonoranlar['MO_09'];
                    $komisyonbilgi->MO_10 = $komisyonoranlar['MO_10'];
                    $komisyonbilgi->MO_11 = $komisyonoranlar['MO_11'];
                    $komisyonbilgi->MO_11 = $komisyonoranlar['MO_12'];
                    $komisyonbilgi->save();
                }
              
            }
            
              
          }
          
            

    }

}