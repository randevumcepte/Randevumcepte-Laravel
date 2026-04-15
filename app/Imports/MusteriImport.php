<?php 
namespace App\Imports;
use App\User;
use App\MusteriPortfoy;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DateTime;

class MusteriImport implements ToCollection, WithHeadingRow
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
            $cinsiyet = null;
            if(isset($row["cinsiyet"]))
            {
                if($row["cinsiyet"]=="Kadın")
                    $cinsiyet = 0;
                if($row["cinsiyet"]=="Erkek")
                    $cinsiyet = 1;

            }
            $data = [
                'name' => $row['ad_soyad'],
                'cep_telefon' => str_replace('+90', '', $row['cep_telefonu']),
                'created_at'=> isset($row['tarih']) ? date('Y-m-d H:i:s',strtotime(self::tarihCevirSA($row['tarih']))) : date('Y-m-d H:i:s'),
                'cinsiyet'=>  $cinsiyet,
                'profil_resim'=>'/public/isletmeyonetim_assets/img/avatar.png',
                'tc_kimlik_no'=>$row['tc_kimlik_no'],
                'ozel_notlar'=>$row['aciklama'],
                
            ];

            if (!empty($row['cep_telefonu'])) {
                $eskidata = User::where('cep_telefon', $row['cep_telefonu'])->first();

                if ($eskidata) {
                    $portfoyvar = MusteriPortfoy::where('user_id', $eskidata->id)
                        ->where('salon_id', $this->sube)
                        ->first();

                    if (!$portfoyvar) {
                        $portfoyyeni = new MusteriPortfoy();
                        $portfoyyeni->aktif = true;
                        $portfoyyeni->salon_id = $this->sube;
                        $portfoyyeni->created_at = date('Y-m-d H:i:s',strtotime(self::tarihCevirSA($row['tarih'])));
                        $portfoyyeni->user_id = $eskidata->id;
                        $portfoyyeni->ozel_notlar = $row['aciklama'];
                        $portfoyyeni->save();
                        $this->importedCount++;
                    }
                } else {
                    $yenimusteri = new User($data);
                    $yenimusteri->save();

                    $portfoyyeni = new MusteriPortfoy();
                    $portfoyyeni->aktif = true;
                    $portfoyyeni->created_at = date('Y-m-d H:i:s',strtotime(self::tarihCevirSA($row['tarih'])));
                    $portfoyyeni->salon_id = $this->sube;
                    $portfoyyeni->user_id = $yenimusteri->id;
                    $portfoyyeni->ozel_notlar = $row['aciklama'];
                    $portfoyyeni->save();

                    $this->importedCount++;
                }
            }
            else
            {
                $yenimusteri = new User($data);
                $yenimusteri->save();

                $portfoyyeni = new MusteriPortfoy();
                $portfoyyeni->aktif = true;
                $portfoyyeni->created_at = date('Y-m-d H:i:s',strtotime(self::tarihCevirSA($row['tarih'])));
                $portfoyyeni->salon_id = $this->sube;
                $portfoyyeni->user_id = $yenimusteri->id;
                $portfoyyeni->ozel_notlar = $row['aciklama'];
                $portfoyyeni->save();

                $this->importedCount++;
            }
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
    private function tarihCevirSA($tarih)
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
}
