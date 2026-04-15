<?php 
namespace App\Imports;
use App\IsletmeYetkilileri;
use App\Personeller;
use Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class PersonelImport implements ToCollection, WithHeadingRow
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
             

            $yetkili = IsletmeYetkilileri::where('gsm1',$row['telefon'])->first() && $row["telefon"] != '' ? IsletmeYetkilileri::where('gsm1',$row['telefon'])->first() : new IsletmeYetkilileri();
            $yetkili->name = $row['ad']." ".$row["soyad"];
            $yetkili->gsm1 = $row["telefon"];

            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
            $olusturulansifre = substr($random, 0, 6);
            $yetkili->password = Hash::make($olusturulansifre); 
            $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';  
            $yetkili->save();

             DB::insert('insert into model_has_roles (role_id, model_type,model_id,salon_id) values (5, "App\\\IsletmeYetkilileri",'.$yetkili->id.','.$this->sube.')');

            $personel = new Personeller();
            $personel->personel_adi = $row["ad"]." ".$row["soyad"];
            $personel->cep_telefon = $row["telefon"];
            $personel->unvan = $row["unvan"];
            $personel->role_id = 5;
            $sonPersonel = Personeller::where('salon_id',$this->sube)->orderBy('id','desc')->first();
            $personel->renk = $sonPersonel->renk == 10 ? 1 : ++$sonPersonel->renk;
            $personel->aktif =true;
            $personel->yetkili_id = $yetkili->id;
            $personel->takvimde_gorunsun = true;
            $personel->salon_id = $this->sube;
            $personel->save();

        }
    }
    

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
