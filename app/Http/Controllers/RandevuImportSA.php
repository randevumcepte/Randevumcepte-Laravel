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
use App\Adisyonlar;
use App\AdisyonHizmetler;
use App\Tahsilatlar;
use App\TahsilatHizmetler;
class RandevuImportSA implements ToCollection, WithHeadingRow
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
            $randevuTarihi = self::tarihIngilizceCevir($row['tarih']);
            $olusturmaTarihi = self::tarihIngilizceCevir($row['olusturulma']);
            $randevu->tarih = $randevuTarihi;
            $randevu->saat = date('H:i:s',strtotime($row['saat']));
            $musteri = User::where('name',$row['musteri'])->where(
                function($q) use($row)
                { 
                    if($row['telefon_numarasi']!= "") 
                        $q->where('cep_telefon',self::telefonFormatiAktarma($row['telefon_numarasi'])); 
                }
            )->first();
            if(!$musteri)
                $musteri = self::yeniMusteriEkle($row);
            $randevu->user_id = $musteri;
            $randevu->salon_id =$this->sube;
           
            $olusturan = self::olusturanAyristir($row['olusturan']);
            $olusturanKaynak = $olusturan['kaynak'];
            $olusturanKisi = $olusturan['kisi'];
            $randevu->created_at = $olusturmaTarihi;
            $personel = Personeller::where('personel_adi',$olusturanKisi)->where('salon_id',$this->sube)->first();
            if(!$personel)
                $personel = self::yeniPersonelKaydi($olusturanKisi);
            $randevu->olusturan_personel_id = Personeller::where('personel_adi',$olusturanKisi)->where('salon_id',$this->sube)->value('yetkili_id');
            if($olusturanKaynak=="Salon")
                $randevu->salon = 1;
            if($olusturanKaynak=="Müşteri"){
                $randevu->web = 1;
                $randevu->olusturan_user_id = $musteri;
            }
            $randevu->durum = 1;
            
            
            if(str_contains($row["durum"],"Geldi"))
                $randevu->randevuya_geldi = true;
            if(str_contains($row["durum"],"Gelmedi"))
                $randevu->randevuya_geldi = false;

            $randevu->save();
            $hizmetler = self::hizmetleriAyrıştır($row["hizmetler"]);
            $baslangicSaat = $row["saat"];
            $adisyonId = self::yeni_adisyon_olustur($musteri,$this->sube,'',$randevuTarihi);

            foreach($hizmetler as $hizmet)
            {

                $rHimzet = new RandevuHizmetler();
                $rHimzet->saat = date('H:i:s',strtotime($row['saat']));
                $salonHizmet = SalonHizmetler::where('aktif',1)->whereHas('hizmetler',function($q) use($hizmet){
                    $q->where('hizmet_adi',$hizmet["hizmet"]);
                })->where('salon_id',$this->sube)->first();
                $hizmetSure = $salonHizmet->sure_dk;
                $hizmetId = $salonHizmet->hizmet_id;


                $rHimzet->hizmet_id = $hizmetId;//Hizmetler::where('hizmet_adi',$hizmet["hizmet"])->value('id');
                $bitisSaat = date('H:i:s',strtotime('+'.$hizmetSure.' minutes',strtotime($baslangicSaat)));
                $rHimzet->randevu_id = $randevu->id;
                $rHimzet->saat_bitis = $bitisSaat;
                $rHimzet->sure_dk = $hizmetSure;
                if(Personeller::where('personel_adi','LIKE','%'.$hizmet["hizmetVeren"].'%')->where('salon_id',$this->sube)->first())
                    $rHimzet->personel_id = Personeller::where('personel_adi','LIKE','%'.$hizmet["hizmetVeren"].'%')->where('salon_id',$this->sube)->value('id');
                if(Cihazlar::where('cihaz_adi','LIKE','%'.$hizmet["hizmetVeren"].'%')->where('salon_id',$this->sube)->first())
                    $rHimzet->cihaz_id = Cihazlar::where('cihaz_adi','LIKE','%'.$hizmet["hizmetVeren"].'%')->where('salon_id',$this->sube)->value('id');
                
                $baslangicSaat = $bitisSaat;
                $rHimzet->save();
                $fiyat = 0;
                if ($hizmet === end($hizmetler)) {
                     $fiyat = str_replace(" TL","",$row['indirim_sonrasi_tutar']);
                }
                 
                
                $adisyonHizmet = adisyon_hizmet_ekle($adisyonId,$rHizmet->hizmet_id,$randevu->tarih,$randevu->saat,$rHimzet->sure_dk,$fiyat,$randevu->randevuya_geldi,$rHimzet->personel_id,$rHimzet->cihaz_id,'','',$randevu->id);

            }
            $odenenTutar = str_replace(' TL','',$row['odenen_tutar']);

            if($odenenTutar > 0)
            {
                $odemeYontemi = "";
                if($row["odeme_sekli"] == "Kredi Kartı")
                    $odemeYontemi= 2;
                elseif($row["odeme_sekli"] == "Nakit")
                    $odemeYontemi = 1;
                elseif($row["odeme_sekli"] == "Havale")
                    $odemeYontemi = 3;
                else
                    $odemeYontemi = 4;
                $tahsilat = new Tahsilatlar();
                $tahsilat->user_id = $randevu->user_id;
                $tahsilat->adisyon_id = $adisyonId;
                $tahsilat->yapilan_odeme = $odenenTutar;
                $tahsilat->tutar = $odenenTutar;
                $tahsilat->odeme_yontemi_id = $odemeYontemi;
                $tahsilat->save();
                $adisyon = Adisyonlar::where('id',$adisyonId)->first();
                foreach($adisyon->hizmetler as $hizmet)
                {
                     $tahsilatHizmet = new TahsilatHizmetler();
                     $tahsilatHizmet->adisyon_id = $adisyonId;
                     $tahsilatHizmet->hizmet_id =$hizmet->hizmet_id;
                     $tahsilatHizmet->tutar = $hizmet->fiyat != 0 ? $odenenTutar : 0;
                     $tahsilatHizmet->salon_id = $this->sube;
                     $tahsilatHizmet->user_id = $musteriId;
                     $tahsilatHizmet->save();
                }

            }



        }
    }
    public function yeni_adisyon_olustur($musteriid,$salonid,$adisyonnotu,$tarih,$olusturan)
    {
        $adisyon = new Adisyonlar();
        $adisyon->user_id = $musteriid;
        $adisyon->salon_id =  $salonid;
        $adisyon->olusturan_id =Personeller::where('id',$olsuturan)->value('yetkili_id');
        $adisyon->tarih = $tarih;
        $adisyon->save();
        return $adisyon->id;
    }
    public function adisyon_hizmet_ekle($adisyon_id,$hizmet_id,$islem_tarihi,$islem_saati,$sure,$fiyat,$geldi,$personel_id,$cihaz_id,$senet_id,$taksitli_tahsilat_id,$randevuId)
    {
        $cihazid=null;
            if(str_contains($cihaz_id,'cihaz'))
            {
                $str = explode('-',$cihaz_id);
                $cihazid = $str[1];
            }
            else{
                $cihazid=$cihaz_id;
            }
        $adisyon_hizmet = new AdisyonHizmetler();
        $adisyon_hizmet->adisyon_id = $adisyon_id;
        $adisyon_hizmet->personel_id = $personel_id;
        $adisyon_hizmet->cihaz_id = $cihazid;
        $adisyon_hizmet->geldi = $geldi;
        $adisyon_hizmet->hizmet_id = $hizmet_id;
        $adisyon_hizmet->islem_tarihi = $islem_tarihi;
        $adisyon_hizmet->islem_saati = $islem_saati;
        $adisyon_hizmet->sure = $sure;
        $adisyon_hizmet->fiyat = $fiyat;
        $adisyon_hizmet->senet_id = $senet_id;
        $adisyon_hizmet->randevu_id = $randevuId;
        $adisyon_hizmet->taksitli_tahsilat_id = $taksitli_tahsilat_id;
        $adisyon_hizmet->save();
        return $adisyon_hizmet->id;
    }
    function yeniPersonelKaydi($personel,$isletme)
    {
        $personel = new Personeller();
        $personel->personel_adi = $personel;
        $personel->salon_id = $isletme;
        $personel->aktif = false;
        $personel->save();
        return $personel->id;
    }
    function olusturanAyristir($olusturan)
    {
        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $olusturan, $matches)) {
            return [
                'kaynak' => trim($matches[1]),   // "Salon"
                'kisi' => trim($matches[2])    // "Gamze"
            ];
        }
        return [ 'kisi' => $input, 'kaynak' => null ]; // Parantez yoksa sadece isim döner
    }
    function telefonFormatiAktarma($telefon)
    {
        $formatted = str_replace(["(",")"," "],["","",""],preg_replace('/^\+?90/', '', $telefon));
        $formatted = preg_replace('/^0/', '', $formatted);
        return $formatted;
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
        $user->cep_telefon = $row['telefon_numarasi'];

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
