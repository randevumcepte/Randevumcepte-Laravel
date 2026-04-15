<?php 
namespace App\Imports;
use App\User;
use App\MusteriPortfoy;
use App\Adisyonlar;
use App\AdisyonHizmetler;
use App\AdisyonPaketler;
use App\AdisyonUrunler;
use App\AdisyonPaketSeanslar;
use App\Randevular;
use App\RandevuHizmetler;
use App\Personeller;
use App\SalonHizmetler;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\SalonHizmetKategoriRenkleri;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SatisImport implements ToCollection, WithHeadingRow
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
             
           
                $userId = User::where('name',$row['musteri'])->first() ? User::where('name',$row['musteri'])->value('id') : self::yeniMusteriEkle($row);
                $adisyon_id = self::yeni_adisyon_olustur($userId,$this->sube,$row["aciklama"],date('Y-m-d',strtotime($row["tarih"])));
            

                $seanslar = self::seansAyristirma($row);
                foreach($seanslar as $seans)
                {
                    $adisyon_hizmet = new AdisyonHizmetler();
                    $adisyon_hizmet->hizmet_id = SalonHizmetler::wherehas('hizmetler',function($q) use($seans){
                        $q->where('hizmet_adi','LIKE','%'.$seans["baslik"].'%');
                    })->where('salon_id',$this->sube)->value('hizmet_id');
                    $adisyon_hizmet->adisyon_id = $adisyon_id;
                    $adisyon_hizmet->fiyat = $seans["fiyat"];
                    $adisyon_hizmet->personel_id = Personeller::where('personel_adi','LIKE','%'.$row["satisi_yapan"].'%')->where('salon_id',$this->sube)->value('id');
                    $adisyon_hizmet->save();
                    for($i=0;$i<$seans["seans"];$i++)
                    {
                        $randevular =  DB::table('randevu_hizmetler')->join('hizmetler','hizmetler.id','=','randevu_hizmetler.hizmet_id')->join('randevular','randevu_hizmetler.randevu_id','=','randevular.id')->select('randevular.id','randevu_hizmetler.personel_id','randevular.tarih','randevular.saat','randevular.randevuya_geldi')->where('randevular.salon_id',$this->sube)->where('hizmetler.hizmet_adi',$seans["baslik"])->where('randevular.user_id',$userId)->get();
                        $seanslar = new AdisyonPaketSeanslar();
                        $seanslar->hizmet_id = $adisyon_hizmet->hizmet_id;
                        $seanslar->adisyon_hizmet_id = $adisyon_hizmet->id;
                        if($randevular->count()>=$seans["seans"])
                        {
                            $seanslar->seans_tarih = $randevular[$i]->tarih;
                            $seanslar->seans_saat = $randevular[$i]->saat;
                            if($randevular[$i]->randevuya_geldi == 1)
                                $seanslar->geldi = 1;
                            if($randevular[$i]->randevuya_geldi == 0)
                                $seanslar->geldi = 0;
                            $seanslar->randevu_id = $randevular[$i]->id;
                            $seanslar->personel_id = $randevular[$i]->personel_id;
                        }
                        $seanslar->save();
                    }

               
                


                 
                }
            
           

        }
    }
    public function  hizmetEkleVeyaGetir($hizmetstr)
    {
        
         
        $hizmet_arr = array();
        $hizmet_return =  array();
         
        array_push($hizmet_arr,$hizmetstr);
        foreach($hizmet_arr as $hizmet)
        {
            $hizmetVar = SalonHizmetler::wherehas('hizmetler',function($q) use($hizmet){
                $q->where('hizmet_adi','LIKE','%'.$hizmet.'%');
            })->where('salon_id',$this->sube)->first();
            if($hizmetVar)
             
                array_push($hizmet_return,$hizmetVar->hizmet_id);
                 
             
           

        }
        return $hizmet_return;



        
    }
    public function yeni_adisyon_olustur($musteriid,$salonid,$adisyonnotu,$tarih)
    {
        $adisyon = new Adisyonlar();
        $adisyon->user_id = $musteriid;
        $adisyon->salon_id =  $salonid;
        $adisyon->olusturan_id = Auth::guard('isletmeyonetim')->user()->id;
        $adisyon->tarih = $tarih;
        $adisyon->notlar = $adisyonnotu;
        $adisyon->save();
        return $adisyon->id;
    }
    public function seansAyristirma($row)
    {
        $text = $row["hizmetler"];

        // 1. Metni virgüllere göre parçala
        $protokoller = explode(',', $text);

        $sonuc = [];

        foreach ($protokoller as $protokol) {
            // 2. Parantez içindeki değeri almak için regex kullan
            preg_match('/\((\d+ Seans) = ([0-9]+\.[0-9]{2} TRY)\)/', $protokol, $matches);
            
            if (!empty($matches)) {
                $seans = $matches[1]; // "18 Seans"
                $fiyat = $matches[2]; // "7000.00 TRY"

                // 3. Parantez öncesindeki kısmı almak için ayır
                $baslik = trim(str_replace($matches[0], '', $protokol)); // "6 X 12 PROTOKOLÜ"

                // 4. Sonucu diziye ekle
                $sonuc[] = [
                    'baslik' => $baslik,
                    'seans' => str_replace(" Seans","",$seans),
                    'fiyat' => str_replace(" TRY","",$fiyat),
                ];
            }
        }

        
        return($sonuc);
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
     
    public function yeniMusteriEkle($row)
    {
        $user = new User();
        $user->name = $row["musteri"];
        $user->cep_telefon = $row["telefon"];
        $user->save();
        $portfoy = new MusteriPortfoy();
        $portfoy->user_id = $user->id;
        $portfoy->salon_id = $this->sube;
        $portfoy->save();
        return $user;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
