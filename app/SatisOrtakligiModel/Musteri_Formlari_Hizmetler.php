<?php

namespace App\SatisOrtakligiModel;

use Illuminate\Database\Eloquent\Model;
use App\Uyelik; // Uyelik modelini doğru namespace'den çağırıyoruz.
use App\SatisOrtakligiModel\Musteri_Formlari;
class Musteri_Formlari_Hizmetler extends Model
{
    // Tablo adı
    protected $table = 'musteri_formlari_hizmetler';
    
    // İlişkili modelleri otomatik yükleme
    protected $with = ['uyelik'];

     

    // Uyelik ile ilişki
    public function uyelik()
    {
        return $this->belongsTo(Uyelik::class, 'uyelik_id'); // 'hizmet_id' yerine doğru foreign key kullanıldı.
    }
}
