<?php 
namespace App\Imports;
use App\User;
use App\MusteriPortfoy;
use App\Randevular;
use App\RandevuHizmetler;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonHizmetler;
use App\Personeller;
use App\Odalar;
use App\Cihazlar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard;
use App\OdaRenkleri;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\SalonHizmetKategoriRenkleri;
use Illuminate\Support\Facades\Log;
class RandevuImportSR implements ToCollection, WithHeadingRow
{
    private $sube;
    private $importedCount = 0;

    public function __construct($sube)
    {
        $this->sube = $sube;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
             
            $randevu = new Randevular();
            $randevu->tarih = self::tarihIngilizceCevir($row['tarihsaat']);
            $randevu->saat = date('H:i:s',strtotime($row['tarihsaat']));
            
            $randevu->user_id = User::where('name',$row['musteri'])->first() ? User::where('name',$row['musteri'])->value('id') : self::yeniMusteriEkle($row);
            $randevu->salon_id =$this->sube;
            $randevu->salon=1;
            $randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;
            $randevu->durum = 1;
            
            
            if(str_contains($row["durum"],"Geldi"))
                $randevu->randevuya_geldi = true;
            if(str_contains($row["durum"],"Gelmedi"))
                $randevu->randevuya_geldi = false;

            $randevu->save();

                $baslangicSaat = $randevu->saat;
                $rHimzet = new RandevuHizmetler();
                $rHimzet->saat = date('H:i:s',strtotime($row['tarihsaat']));
                
                $hizmetSure = SalonHizmetler::whereHas('hizmetler',function($q) use($hizmet){
                    $q->where('hizmet_adi',$row["hizmetler"]);
                })->where('salon_id',$this->sube)->value('sure_dk');
                
                $rHimzet->hizmet_id = Hizmetler::where('hizmet_adi',$row["hizmetler"])->value('id');

                $bitisSaat = date('H:i:s',strtotime('+'.$hizmetSure.' minutes',strtotime($baslangicSaat)));
                
                $rHimzet->randevu_id = $randevu->id;
                $rHimzet->saat_bitis = $bitisSaat;
                

                $rHimzet->sure_dk = $hizmetSure;
                if(Personeller::where('personel_adi','LIKE','%'.$row["personel"].'%')->where('salon_id',$this->sube)->first())
                    $rHimzet->personel_id = Personeller::where('personel_adi','LIKE','%'.$row["personel"].'%')->where('salon_id',$this->sube)->value('id');
                
                
                $baslangicSaat = $bitisSaat;
                $rHimzet->save();
             
           
            
            
           

        }
    }
    function tarihIngilizceCevir($tarih)
    {
        $turkce_aylar = [
            'Ocak'   => 'January',
            'Şubat'  => 'February',
            'Mart'   => 'March',
            'Nisan'  => 'April',
            'Mayıs'  => 'May',
            'Haziran'=> 'June',
            'Temmuz' => 'July',
            'Ağustos'=> 'August',
            'Eylül'  => 'September',
            'Ekim'   => 'October',
            'Kasım'  => 'November',
            'Aralık' => 'December'
        ];


        $tarih_ingilizce = str_replace(array_keys($turkce_aylar), array_values($turkce_aylar), $tarih);

        return date('Y-m-d', strtotime($tarih_ingilizce));  
    }
     function hizmetleriAyrıştır($metin) {
       $sonuçlar = [];
        
        // Virgüle göre ayır
        $parçalar = explode(',', $metin);

        foreach ($parçalar as $parça) {
            // Parantez içindeki tüm ifadeleri bul
            preg_match_all('/\((.*?)\)/', $parça, $eşleşmeler);
            
            if (!empty($eşleşmeler[1])) {
                // Son parantez içindeki değeri al (hizmetVeren)
                $hizmetVeren = array_pop($eşleşmeler[1]); 

                // Hizmet adını, son parantez hariç olan kısımdan al
                $hizmet = trim(substr($parça, 0, strrpos($parça, "({$hizmetVeren})")));

                // Sonuç dizisine ekle
                $sonuçlar[] = [
                    'hizmet' => trim($hizmet),
                    'hizmetVeren' => $hizmetVeren
                ];
            } else {
                // Parantez yoksa sadece 'hizmet' olarak ekle
                $sonuçlar[] = [
                    'hizmet' => trim($parça),
                    'hizmetVeren' => ''
                ];
            }
        }

        return $sonuçlar;
    }
    public function  hizmetEkleVeyaGetir($row)
    {
        
        $hizmetler_str = str_replace(["(",")" ,"1x","2x","3x"],["","","","",""],$row["hizmetler"]);
        $hizmet_arr = array();
        $hizmet_return =  array();
        if(str_contains($hizmetler_str,","))
            $hizmet_arr = explode(',',$hizmetler_str);
        else
            array_push($hizmet_arr,$hizmetler_str);
        foreach($hizmet_arr as $hizmet)
        {
            $hizmetVar = SalonHizmetler::wherehas('hizmetler',function($q) use($hizmet){
                $q->where('hizmet_adi','LIKE','%'.$hizmet.'%');
            })->where('salon_id',$this->sube)->first();
            if($hizmetVar)
             
                array_push($hizmet_return,$hizmetVar->hizmet_id);
                 
            
            else{       
                 
                $hizmetNew = new Hizmetler();
                $hizmetNew->hizmet_adi = $hizmet;
                $hizmetNew->ozel_hizmet = true;
                $hizmetNew->salon_id = $this->sube;

                $hizmetNew->hizmet_kategori_id = Hizmet_Kategorisi::where('hizmet_kategorisi_adi','LIKE',"%".$row["birim"]."%")->count()>0 ? Hizmet_Kategorisi::where('hizmet_kategorisi_adi','LIKE',"%".$row["birim"]."%")->value("id") : self::hizmetKategoriEkle($row);
                $hizmetNew->save(); 
                array_push($hizmet_return,$hizmetNew->id);
                self::hizmetSalonaEkle($hizmetNew);
            }
           

        }
        return $hizmet_return;



        
    }
    public function hizmetSalonaEkle($hizmet)
    {
        $salonHizmet = new SalonHizmetler();
        $salonHizmet->salon_id = $this->sube;
        $salonHizmet->hizmet_id = $hizmet->id;
        $salonHizmet->hizmet_kategori_id=$hizmet->hizmet_kategori_id;
        $salonHizmet->aktif =true;
        $salonHizmet->bolum=2;
        $salonHizmet->save();
         if(SalonHizmetKategoriRenkleri::where('hizmet_kategori_id',$hizmet->hizmet_kategori_id)->where('salon_id',$this->sube)->count() == 0)
                        {
                                $kategori_son_renk = SalonHizmetKategoriRenkleri::where('salon_id',$this->sube)->orderBy('renk_id','desc')->first();
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
                                $yeni_renk->salon_id = 126;
                                $yeni_renk->renk_id = $yeni_kategori_renk;
                                $yeni_renk->hizmet_kategori_id = $salonHizmet->hizmet_kategori_id;
                                $yeni_renk->save();
                        }

    }
    public function hizmetKategoriEkle($row)
    {
        $kategori = new Hizmet_Kategorisi();
        $kategori->hizmet_kategorisi_adi = $row["birim"];
        $kategori->save();
        return $kategori->id;
    }
    public function odaEkleVeyaGetir($row)
    {

        $oda = Odalar::where('oda_adi',$row["oda"])->where('aktifmi',1)->where('salon_id',$this->sube)->first();
        if(!$oda)
            $oda = new Odalar();
        $oda->oda_adi = $row["oda"];
        $oda->durum = 1;
        $oda->aktifmi = 1;
        $oda->salon_id = $this->sube;
        $oda->save();
        
        if(!$oda){
            $kategori_son_renk = OdaRenkleri::where('salon_id',$this->sube)->orderBy('id','desc')->first();
            $yeni_kategori_renk = '';
            if($kategori_son_renk === null)
            {
                    $yeni_kategori_renk = 1;
            }else
            {
                if($kategori_son_renk->renk_id == 10)
                    $yeni_kategori_renk = 1;
                else
                    $yeni_kategori_renk = $kategori_son_renk->renk_id + 1;
            }
           
            $yeni_renk = new OdaRenkleri();
            $yeni_renk->salon_id = $this->sube;
            $yeni_renk->renk_id = $yeni_kategori_renk;
            $yeni_renk->oda_id = $oda->id;

            $yeni_renk->save();
        }
        
       
       

        return $oda->id;
    }
    public function yeniMusteriEkle($row)
    {
        $user = new User();
        $user->name = $row["musteri"];

        $user->save();
        $portfoy = new MusteriPortfoy();
        $portfoy->user_id = $user->id;
        $portfoy->salon_id = $this->sube;
        $portfoy->aktif = 1;
        $portfoy->save();
        return $user->id;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
