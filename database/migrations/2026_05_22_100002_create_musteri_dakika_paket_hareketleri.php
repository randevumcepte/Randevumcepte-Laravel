<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * musteri_dakika_paket_hareketleri:
 *   Bir dakika paketinin her kullanim / iade / duzeltme hareketinin
 *   tarihcesi. Bakiyenin nasil eridigini gormek + iade gerektiginde
 *   geri yuklemek icin tutuyoruz. Silinmez, ters hareketle iptal edilir.
 *
 *   tur:
 *     randevu_kullanim: randevu "geldi" isaretlendiginde otomatik
 *     manuel_kullanim:  randevu disinda manuel dusum
 *     iade:             "geldi" geri alindiginda otomatik geri yukleme
 *     duzeltme:         personel/admin manuel +/- duzeltme
 *
 *   dakika alani isaretli: + kullanim, - iade
 */
class CreateMusteriDakikaPaketHareketleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('musteri_dakika_paket_hareketleri')) {
            Schema::create('musteri_dakika_paket_hareketleri', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('musteri_dakika_paketi_id');
                $table->unsignedBigInteger('randevu_id')->nullable();
                $table->unsignedBigInteger('randevu_hizmet_id')->nullable();
                $table->integer('dakika');
                $table->enum('tur', ['randevu_kullanim', 'manuel_kullanim', 'iade', 'duzeltme']);
                $table->dateTime('tarih');
                $table->text('aciklama')->nullable();
                $table->unsignedBigInteger('olusturan_user_id')->nullable();
                $table->unsignedBigInteger('olusturan_personel_id')->nullable();
                $table->timestamps();

                $table->index('musteri_dakika_paketi_id', 'mdph_paket_idx');
                $table->index('randevu_id', 'mdph_randevu_idx');
                $table->index('randevu_hizmet_id', 'mdph_rh_idx');
                $table->index(['musteri_dakika_paketi_id', 'tarih'], 'mdph_paket_tarih_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('musteri_dakika_paket_hareketleri');
    }
}
