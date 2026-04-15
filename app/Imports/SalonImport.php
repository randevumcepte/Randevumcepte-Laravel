<?php

namespace App\Imports;

use App\Salonlar;
use App\Iller;
use App\Ilceler;
use App\SatisOrtakligiModel\Musteri_Formlari;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class SalonImport implements ToCollection,WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    private $pasif_ortak;
    private $importedCount = 0;
    private $notImportedCount = 0;
     public function __construct($pasif_ortak)
    {
        $this->pasif_ortak = $pasif_ortak;
    }

    public function collection(Collection $rows)
    {
        $salon = '';
       
        foreach ($rows as $row) {
            $il_id=Iller::where('il_adi',self::turkceStrUpper($row['isletme_ili']))->value('id');
            Log::info('user:', ['id' => Auth::user()->id]);
                Log::info('il sorgu:', ['il' => $il_id]);
        Log::info('ilçe sorgu:', ['ilce' => Ilceler::where('ilce_adi',self::turkceStrUpper($row['isletme_ilcesi']))->where('il_id',$il_id)->value('id')]);
        Log::info('pasif ortak:', ['pasif' => $this->pasif_ortak]);
        Log::info('il upper:', ['ilupper' => self::turkceStrUpper($row['isletme_ili'])]);
         Log::info('ilçe upper :', ['ilceupper' => self::turkceStrUpper($row['isletme_ilcesi'])]);
       
            $data = [
                'yetkili_adi' => $row['yetkili_adi'],
                'yetkili_telefon' => str_replace('+90', '', $row['cep_telefonu']),
                'salon_adi'=> $row['isletme_adi'],
                'telefon_1'=> $row['isletme_telefon_1'],
                'telefon_2'=> $row['isletme_telefon_2'],
                'telefon_3'=> $row['isletme_telefon_3'],
                'adres'=>$row['isletme_adres'],
                'il_id'=>$il_id,
                'hesap_acildi'=>false,
                'ilce_id'=>Ilceler::where('ilce_adi',self::turkceStrUpper($row['isletme_ilcesi']))->where('il_id',$il_id)->value('id'),
                'satis_ortagi_id'=>Auth::user()->id,
                'pasif_ortak_id'=>$this->pasif_ortak != "0" ? $this->pasif_ortak : null,

            ];

            if (!empty($row['cep_telefonu'])) {
                $eskidata = Salonlar::where('yetkili_telefon', $row['cep_telefonu'])->first();

                if (!$eskidata) {
                   
                    $salon = new Salonlar($data);
                    $salon->save();
                    $musteri_form = new Musteri_Formlari([
                        'salon_id' => $salon->id, // Yaratılan salonun ID'sini ilişkilendiriyoruz
                        'satis_ortagi_id' => Auth::user()->id, // Örneğin, kullanıcı ID'sini burada kullanıyoruz
                        'satis_ortagi_hakedis_odeme_durumu_id' => 3, // Varsayılan değer veya başka bir ilişki ekleyebilirsiniz
                        'durum_id' => 1, // Varsayılan durum ID'si
                        // Diğer gerekli alanları burada tanımlayın
                    ]);
                    $musteri_form->save();
                    $this->importedCount++;
                    
                }
                else
                    $this->notImportedCount++;
              
            }
        }
        

        // Musteri_Formlari kaydını oluşturun
        
        
        return $salon;
        
       
    }
    function turkceStrUpper($string) {
        $replacements = [
            'ş' => 'Ş',
            'ı' => 'I',
            'ğ' => 'Ğ',
            'ü' => 'Ü',
            'ö' => 'Ö',
            'ç' => 'Ç',
            'i' => 'İ',
            'ş' => 'Ş',
            'ı' => 'I',
        ];

        // Türkçe karakterleri doğru şekilde değiştir
        $string = strtr($string, $replacements);

        // Son olarak mb_strtoupper ile büyük harfe dönüştür
        return mb_strtoupper($string);
    }
    public function getImportedCount()
    {
        return $this->importedCount;
    }
    public function getNotImportedCount()
    {
        return $this->notImportedCount;
    }
    
}