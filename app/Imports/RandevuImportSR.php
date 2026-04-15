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
            
           
            $randevu->tarih = self::tarihCevirExcelUyumlu($row['randevu_baslangic_tarihi']);
            $randevu->saat = self::yuvarlaSaat($row['randevu_baslangic_tarihi']);
            
            $randevu->user_id = User::where('name',$row["musteri_adi"]." ".$row["musteri_soyadi"])->first() ? User::where('name',$row["musteri_adi"]." ".$row["musteri_soyadi"])->value('id') : self::yeniMusteriEkle($row);
            $randevu->salon_id =$this->sube;
            $randevu->salon=1;
            $randevu->olusturan_personel_id = Auth::guard('isletmeyonetim')->user()->id;
            $randevu->durum = 1;
            $randevu->created_at = self::tarihCevirExcelUyumlu($row["olusturulma_tarihi"])." ".self::yuvarlaSaat($row['olusturulma_tarihi']);
            
            $randevu->personel_notu = $row["not"];
            if(str_contains($row["durum"],"Geldi"))
                $randevu->randevuya_geldi = true;
            if(str_contains($row["durum"],"Gelmedi"))
                $randevu->randevuya_geldi = false;
            if(str_contains($row["durum"],"İptal"))
                $randevu->durum = 2;

            $randevu->save();

                $baslangicSaat = $randevu->saat;
                $rHimzet = new RandevuHizmetler();
                $rHimzet->saat = $baslangicSaat;
                
                $hizmetSure = SalonHizmetler::whereHas('hizmetler',function($q) use($row){
                    $q->where('hizmet_adi',$row["hizmet_ismi"]);
                })->where('salon_id',$this->sube)->value('sure_dk');
                if(!$hizmetSure)
                    $hizmetSure = 30;
                $rHimzet->hizmet_id = Hizmetler::where('hizmet_adi',$row["hizmet_ismi"])->value('id');

                $bitisSaat = date('H:i:s',strtotime('+'.$hizmetSure.' minutes',strtotime($baslangicSaat)));
                
                $rHimzet->randevu_id = $randevu->id;
                $rHimzet->saat_bitis = $bitisSaat;
                

                $rHimzet->sure_dk = $hizmetSure;
                if(Personeller::where('personel_adi','LIKE','%'.$row["calisan_ismi"]." ".$row["calisan_soyadi"].'%')->where('salon_id',$this->sube)->first())
                    $rHimzet->personel_id = Personeller::where('personel_adi','LIKE','%'.$row["calisan_ismi"]." ".$row["calisan_soyadi"].'%')->where('salon_id',$this->sube)->value('id');

                
                
                $baslangicSaat = $bitisSaat;
                $rHimzet->save();
             
           
            
            
           

        }
    }
    function yuvarlaSaat($excelFloat)
{
    // Excel tarihinden saat, dakika ve saniye kısmını elde et
    $dt = self::excelToDateTime($excelFloat);
    
    // Saat, dakika, saniye formatında al
    $hour = $dt->format("H");
    $minute = $dt->format("i");
    $second = $dt->format("s");

    // Eğer saniye 30'dan küçükse, dakika bir üst değere yuvarlanacak
    if ($second >= 30) {
        $minute++;
    }

    // Eğer dakika 60'a ulaşırsa, saat bir artacak
    if ($minute == 60) {
        $minute = 0;
        $hour++;
    }

    // Yuvarlanmış saati ve dakikayı döndür
    return sprintf("%02d:%02d:%02d", $hour, $minute, 0); // Saniyeyi 0 olarak ayarlıyoruz
}
    function excelToDateTime($excelFloat)
{
    // Excel 1900 epoch: 25569 günü = 1970-01-01
    $days = floor($excelFloat);
    $seconds = ($excelFloat - $days) * 86400; // Excel ondalıklı kısmı saniyeye çevirir

    // Excel start date (1899-12-30) tarihinden başlıyoruz
    $date = new \DateTime('1899-12-30'); 
    $date->add(new \DateInterval("P{$days}D")); // Gün sayısını ekliyoruz

    // Saniyeyi ekliyoruz
    $hours = floor($seconds / 3600); // Saatleri alıyoruz
    $minutes = floor(($seconds % 3600) / 60); // Dakikaları alıyoruz
    $remainingSeconds = $seconds % 60; // Kalan saniyeyi alıyoruz

    // Saat, dakika, saniyeyi ekliyoruz
    $date->add(new \DateInterval("PT{$hours}H{$minutes}M{$remainingSeconds}S"));

    return $date;
}

function tarihCevirExcelUyumlu($excelFloat)
{
    $dt = self::excelToDateTime($excelFloat);
    return $dt->format("Y-m-d");
}

function saatCevirExcelUyumlu($excelFloat)
{
    $dt = self::excelToDateTime($excelFloat);
    return $dt->format("H:i:s");
}
    function tarihCevir($tarih)
    {
         $dateString = $tarih;

        // Giriş formatı: gün.ay.yıl saat:dakika
        $date = \DateTime::createFromFormat('j.n.Y H:i', $dateString);

        if ($date) {
            // Yıl-ay-gün formatına çevir
            return $date->format('Y-m-d'); // Çıktı: 2025-10-01
        } else {
            return "Tarih çevrilemedi.";
        }
    }
    function saatCevir($tarih)
    {
         $dateString = $tarih;

        // Giriş formatı: gün.ay.yıl saat:dakika
        $date = \DateTime::createFromFormat('j.n.Y H:i', $dateString);

        if ($date) {
            // Yıl-ay-gün formatına çevir
            return $date->format('H:i:s'); // Çıktı: 2025-10-01
        } else {
            return "Saat çevrilemedi.";
        }
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
        
        $hizmetler_str = str_replace(["(",")" ,"1x","2x","3x"],["","","","",""],$row["hizmet_ismi"]);
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
        $user->name = $row["musteri_adi"]." ".$row["musteri_soyadi"];
        $user->cep_telefon = $row["musteri_telefonu"];

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
