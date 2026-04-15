<?php 
namespace App\Imports;
use App\User;
use App\MusteriPortfoy;
use App\Adisyonlar;
use App\AdisyonHizmetler;
use App\AdisyonPaketler;
use App\AdisyonUrunler;
use App\AdisyonPaketSeanslar;
use App\Tahsilatlar;
use App\TahsilatHizmetler;
use App\TahsilatUrunler;
use App\Randevular;
use App\RandevuHizmetler;
use App\Personeller;
use App\SalonHizmetler;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\Urunler;
use App\Cihazlar;
use App\Odalar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\SalonHizmetKategoriRenkleri;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class SatisImportSA implements ToCollection, WithHeadingRow
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
             
            $salonId = $this->sube;
            $users = MusteriPortfoy::whereHas('users', function($q) use ($row) {
                $q->where("name", [$row['musteri']]);
                 if(property_exists($row, 'telefon')) {

                    if(empty($row->telefon)) {
                        $q->whereNull('cep_telefon');
                    } else {
                        $q->where('cep_telefon', $row->telefon);
                    }

                }
            })
            ->where('salon_id', $salonId)
            ->get();
             
            $aciklama = $row['aciklama'] ?? null;

            $userId = '';
            if($users->count()==0)
            {
                
                $userId = self::yeniMusteriEkle($row);
            }
            else{

                if($users->count()>1){

                       $usersCS = MusteriPortfoy::whereHas('users', function($q) use ($row) {
                            $q->whereRaw("name COLLATE utf8mb4_bin = ?", [$row['musteri']]);
                             if(property_exists($row, 'telefon')) {

                                if(empty($row->telefon)) {
                                    $q->whereNull('cep_telefon');
                                } else {
                                    $q->where('cep_telefon', $row->telefon);
                                }

                            }
                        })
                        ->where('salon_id', $salonId)
                        ->get();
                        if($usersCS->count()>1)
                            continue;
                        elseif($usersCS->count()==1) {
                            $userId = $usersCS[0]->id;
                        }
                        else
                        {
                            $userId = self::yeniMusteriEkle($row);
                        }
                }
                else
                    $userId = $users[0]->user_id;
            }

             

            $satisTarihi = self::tarihIngilizceCevir($row['tarih']);
            $hizmetler = self::hizmetleriAyrıştır($row['hizmetler']);
            $hizmetAdlari = array_column($hizmetler, 'hizmet');
            $adayAdisyonlar = Adisyonlar::where('salon_id',$salonId)
                ->where('user_id',$userId)
                ->where('tarih', date('Y-m-d',strtotime($satisTarihi)))
                ->with('hizmetler.hizmet')
                ->get();
            $adisyonVar = '';
           $normalize = function ($value) {
                $value = trim($value);
                $value = preg_replace('/\s*\(.*?\)/', '', $value);
                $value = mb_strtolower($value, 'UTF-8');

                // combining karakterleri temizle
                $value = preg_replace('/\p{Mn}/u', '', $value);

                return $value;
            };
            foreach($adayAdisyonlar as $adisyon) {

                
                $dbHizmetler = $adisyon->hizmetler
                    ->pluck('hizmet.hizmet_adi')
                    ->map($normalize)
                    ->sort()
                    ->values()
                    ->toArray();

                $excelHizmetler = collect($hizmetAdlari)
                    ->map($normalize)
                    ->sort()
                    ->values()
                    ->toArray();

                Log::info('db hizmetler '.json_encode($dbHizmetler));
                Log::info('excel hizmetler '.json_encode($excelHizmetler));
                if($dbHizmetler == $excelHizmetler) { // === yerine == daha güvenli
                    $adisyonVar = $adisyon;
                    break;
                }
            }

            if(!$adisyonVar)
            { 
            
                $randevu = new Randevular();
                $randevuTarihi = self::tarihIngilizceCevir($row['tarih']);
                $olusturmaTarihi = self::tarihIngilizceCevir($row['olusturulma']);
                $randevu->tarih = $randevuTarihi;
                $randevu->saat = date('H:i:s',strtotime($row['saat']));
                $randevu->user_id = $userId;
                $randevu->salon_id =$this->sube;
                $randevu->excelden_ekleme = true;
                $olusturan = self::olusturanAyristir($row['olusturan']);
                $olusturanKaynak = $olusturan['kaynak'];
                $olusturanKisi = $olusturan['kisi'];
                $randevu->created_at = $olusturmaTarihi;
                $personel = Personeller::where('personel_adi',$olusturanKisi)->where('salon_id',$this->sube)->first();
                if(!$personel)
                    $personel = self::yeniPersonelKaydi($olusturanKisi,$this->sube);
                $randevu->olusturan_personel_id = Personeller::where('personel_adi',$olusturanKisi)->where('salon_id',$this->sube)->value('yetkili_id');
                if($olusturanKaynak=="Salon")
                    $randevu->salon = 1;
                if($olusturanKaynak=="Müşteri"){
                    $randevu->web = 1;
                    $randevu->olusturan_user_id = $musteri;
                }
                $randevu->durum = 1;
                
                
                if(str_contains($row["geldi_mi"],"Geldi"))
                    $randevu->randevuya_geldi = true;
                if(str_contains($row["geldi_mi"],"Gelmedi"))
                    $randevu->randevuya_geldi = false;

                $randevu->save();
                $hizmetler = self::hizmetleriAyrıştır($row["hizmetler"]);
                $baslangicSaat = date('H:i:s',strtotime($row["saat"]));
                

                foreach($hizmetler as $hizmet)
                {

                    $rHimzet = new RandevuHizmetler();
                    $rHimzet->saat = $baslangicSaat;
                    $hizmetVarmi = Hizmetler::where('hizmet_adi',$hizmet['hizmet'])->first();
                    $salonHizmet = SalonHizmetler::where('aktif',1)->whereHas('hizmetler',function($q) use($hizmet){
                        $q->where('hizmet_adi',$hizmet["hizmet"]);
                    })->where('salon_id',$this->sube)->first();
                    $sure_dk = 30;
                    
                    if(!$salonHizmet && $hizmetVarmi)
                    {
                        $salonHizmet = new SalonHizmetler();
                        $salonHizmet->sure_dk = 30;
                        $salonHizmet->hizmet_id = $hizmetVarmi->id;
                        $salonHizmet->hizmet_kategori_id = $hizmetVarmi->hizmet_kategori_id;
                        $salonHizmet->save();
                    }
                    elseif(!$hizmetVarmi)
                    {
                        $yHizmet = new Hizmetler();
                        $yHizmet->hizmet_adi = $hizmet["hizmet"];
                        $yHizmet->hizmet_kategori_id = 13;
                        $yHizmet->save();
                        $salonHizmet = new SalonHizmetler();
                        $salonHizmet->sure_dk = 30;
                        $salonHizmet->hizmet_id = $yHizmet->id;
                        $salonHizmet->hizmet_kategori_id = $yHizmet->hizmet_kategori_id;
                        $salonHizmet->save();

                    }
                    else
                        $sure_dk = $salonHizmet->sure_dk;
                    $hizmetSure = $sure_dk;
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
                     if(Odalar::where('oda_adi','LIKE','%'.$hizmet["hizmetVeren"].'%')->where('salon_id',$this->sube)->first())
                        $rHimzet->oda_id = Odalar::where('oda_adi','LIKE','%'.$hizmet["hizmetVeren"].'%')->where('salon_id',$this->sube)->value('id');
                    echo $baslangicSaat . ' ';
                    $baslangicSaat = $bitisSaat;
                    echo $bitisSaat ."<br>";
                    $rHimzet->save();
                     

                } 
                $adisyon_id = self::yeni_adisyon_olustur($userId,$this->sube,$aciklama,date('Y-m-d',strtotime($satisTarihi)));
                foreach($hizmetler as $key=>$hizmet)
                {
                    $hizmet_var = SalonHizmetler::wherehas('hizmetler',function($q) use($hizmet) {
                        $q->where('hizmet_adi','LIKE','%'.$hizmet['hizmet'].'%');
                    })->where('salon_id',$salonId)->first();

                    if($hizmet_var)
                    {
                        $adisyon_hizmet = new AdisyonHizmetler();
                        $adisyon_hizmet->hizmet_id = SalonHizmetler::wherehas('hizmetler',function($q) use($hizmet){
                            $q->where('hizmet_adi',$hizmet['hizmet']);
                        })->where('salon_id',$salonId)->value('hizmet_id');

                        $adisyon_hizmet->adisyon_id = $adisyon_id;
                        $geldi = null;
                        if($row['geldi_mi']=='Geldi')
                            $geldi = 1;
                        elseif($row['geldi_mi']=='Gelmedi')
                            $geldi = 0;
                        $adisyon_hizmet->fiyat = str_replace(' TL','',$row['indirim_sonrasi_tutar']) / count($hizmetler);
                        $adisyon_hizmet->aciklama = $aciklama;
                        $adisyon_hizmet->geldi = $geldi; 
                        $adisyon_hizmet->personel_id = $hizmet['hizmetVeren'] != '' ? Personeller::where('personel_adi','LIKE','%'.$hizmet['hizmetVeren'] .'%')->where('salon_id',$salonId)->value('id') : null;
                        $adisyon_hizmet->islem_saati = date('H:i:s',strtotime($row['saat']));
                        $adisyon_hizmet->islem_tarihi = date('Y-m-d',strtotime($satisTarihi));
                        $adisyon_hizmet->randevu_id = $randevu->id;
                        $adisyon_hizmet->save();
 
                    }
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
        
        $user->cep_telefon =$row['telefon'] ?? null;
        $user->save();
        $portfoy = new MusteriPortfoy();
        $portfoy->user_id = $user->id;
        $portfoy->salon_id = $this->sube;
        $portfoy->aktif = true;
        $portfoy->save();
        return $user->id;
    }
    public function tarihIngilizceCevir($tarih)
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

        // Ayları İngilizceye çevir
        $tarih_ingilizce = str_replace(array_keys($turkce_aylar), array_values($turkce_aylar), $tarih);

        // strtotime ile timestamp'e çevir
        $timestamp = strtotime($tarih_ingilizce);

        // Saat bilgisi var mı kontrol et (sadece saat varsa timestamp %H:%M'yi kontrol et)
        if (preg_match('/\d{1,2}:\d{2}/', $tarih)) {
            return date('Y-m-d H:i:s', $timestamp);
        } else {
            return date('Y-m-d', $timestamp);
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
    function yeniPersonelKaydi($personel,$isletme)
    {
        $personel2 = new Personeller();
        $personel2->personel_adi = $personel;
        $personel2->salon_id = $isletme;
        $personel2->aktif = false;
        $personel2->save();
        return $personel2;
    }
    function olusturanAyristir($olusturan)
    {
        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $olusturan, $matches)) {
            return [
                'kaynak' => trim($matches[1]),   // "Salon"
                'kisi' => trim($matches[2])    // "Gamze"
            ];
        }
        return [ 'kisi' => $olusturan, 'kaynak' => null ]; // Parantez yoksa sadece isim döner
    }
    function telefonFormatiAktarma($telefon)
    {
        $formatted = str_replace(["(",")"," "],["","",""],preg_replace('/^\+?90/', '', $telefon));
        $formatted = preg_replace('/^0/', '', $formatted);
        return $formatted;
    }
    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
