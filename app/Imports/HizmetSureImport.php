<?php 
namespace App\Imports;

use App\SalonHizmetler;
use App\Hizmetler;
use Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class HizmetSureImport implements ToCollection, WithHeadingRow
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
            
             $salonHizmet = SalonHizmetler::where('salon_id',$this->sube)->where('hizmet_id',Hizmetler::where('hizmet_adi',$row["hizmet"])->value('id'))->where('aktif',1)->first();
             $salonHizmet->sure_dk = $row["sure"];
             
             $salonHizmet->save();


             

        }
    }
    

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
