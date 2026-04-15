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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\SalonHizmetKategoriRenkleri;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TahsilatAktarSA implements ToCollection, WithHeadingRow
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
                        ->first();
                        $userId = $usersCS->id;
                }
                else
                    $userId = $users[0]->user_id;
            }




            $satisTarihi = self::tarihIngilizceCevir($row['satis_tarihi']);
            $odemeTarihi = self::tarihIngilizceCevir($row['olusturulma']);
            $hizmetler = self::hizmetleriAyrıştır($row['urun_hizmet']);
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

               
                if($dbHizmetler == $excelHizmetler) { // === yerine == daha güvenli
                    $adisyonVar = $adisyon;
                    break;
                }
            }
            if($adisyonVar=='')
            {

                $adisyonVar = self::yeni_adisyon_olustur($userId,$this->sube,'',date('Y-m-d',strtotime($satisTarihi)));
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

                        $adisyon_hizmet->adisyon_id = $adisyonVar->id;
                        $adisyon_hizmet->fiyat = str_replace(' TL','',$row['tutar']) / count($hizmetler);
                        $adisyon_hizmet->aciklama = $aciklama;
                         
                        $adisyon_hizmet->personel_id =  null;
                        
                        $adisyon_hizmet->islem_tarihi = date('Y-m-d',strtotime($satisTarihi));
                        
                        $adisyon_hizmet->save();
 
                    }
                }  
            }
             $tahsilatVar = Tahsilatlar::where('adisyon_id',$adisyonVar->id)->where('tutar',str_replace(['.',','],['','.'],$row['tutar']))->where('odeme_tarihi',$odemeTarihi)->first();
                if(!$tahsilatVar && $adisyonVar)
                {
                    $odemeYontemi = 4;
                    $odeme = trim($row['odeme_yontemi']);

                    if(strcasecmp($odeme, 'Nakit') == 0)
                        $odemeYontemi = 1;
                    elseif(strcasecmp($odeme, 'Kredi kartı') == 0)
                        $odemeYontemi = 2;
                    elseif(strcasecmp($odeme, 'Havale') == 0)
                        $odemeYontemi = 3;
                    $tahsilat = new Tahsilatlar();
                    $tahsilat->adisyon_id = $adisyonVar->id;
                    $tahsilat->user_id = $userId;
                    $tahsilat->tutar = str_replace(['.',','],['','.'],$row['tutar']);
                    $tahsilat->odeme_tarihi = date('Y-m-d',strtotime($odemeTarihi));
                    $olusturanKisi = $row['olusturan'];
                    $tahsilat->olusturan_id = Personeller::where('personel_adi',$olusturanKisi)->where('salon_id',$this->sube)->value('id');
                    $tahsilat->salon_id = $this->sube;
                    $tahsilat->yapilan_odeme = str_replace(['.',','],['','.'],$row['tutar']);
                    $tahsilat->odeme_yontemi_id = $odemeYontemi;
                    $tahsilat->notlar = $row['notlar']." - ".$row['odeme_yontemi'];
                    $tahsilat->save();
                    foreach($adisyonVar->hizmetler as $hizmet_id)
                    {
                        $odeme = new TahsilatHizmetler();
                        $odeme->adisyon_hizmet_id = $hizmet_id->id;
                        $odeme->tahsilat_id = $tahsilat->id;
                        $hizmet_tahsilat_tutar = AdisyonHizmetler::where('id',$hizmet_id->id)->where('adisyon_id',$adisyonVar->id)->value('fiyat');
                         
                        $toplamAdisyonTutari = AdisyonHizmetler::where('adisyon_id',$adisyonVar->id)->sum('fiyat')+AdisyonUrunler::where('adisyon_id',$adisyonVar->id)->sum('fiyat')+AdisyonPaketler::where('adisyon_id',$adisyonVar->id)->sum('fiyat');
                        
                        $odeme->tutar =  ($hizmet_tahsilat_tutar/$toplamAdisyonTutari)*$row['tutar'];

                        $odeme->save();

                    }
                }
                
            

            
            /*$urun_var = Urunler::where('urun_adi','LIKE','%'.$row['hizmetler'].'%')->where('salon_id',$salonId)->first();
            if($urun_var)
            {
                $adisyon_urun = new AdisyonUrunler();
                $adisyon_urun->urun_id = Urunler::where('urun_adi','LIKE','%'.$row['hizmetler'].'%')->where('salon_id',$salonId)->value('id');
                $adisyon_urun->adisyon_id = $adisyon_id;
                $adisyon_urun->adet = $row['miktar'];
                $adisyon_urun->fiyat = $row['toplam'];
                $adisyon_urun->islem_tarihi = date('Y-m-d',strtotime($satisTarihi));
                $adisyon_urun->personel_id = $row['calisan'] != '' ? Personeller::where('personel_adi','LIKE','%'.$row['calisan'].'%')->where('salon_id',$salonId)->value('id') : null;
                $adisyon_urun->aciklama = $row["aciklama"];
                $adisyon_urun->save();
                   
            } */
             
          
           

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
        return $adisyon;
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
        $portfoy->save();
        return $user;
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
