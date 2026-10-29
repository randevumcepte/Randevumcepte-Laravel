<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v2 harici indirim DELTA LOG'u — bir tahsilat sirasinda kaleme EKLENEN indirim
 * miktarini kayit altina alir ki IADE'de TAM o kadar geri alinabilsin (eski
 * tahsilatkaldir indirim_tutari'ni bluntca null'luyordu = standing indirim kaybi).
 *
 * YENI TABLO: eski monolite ve tahsilatlar'a SIFIR etki. Yalnizca yeni L13 app
 * yazar/okur. Sema eski klasorde yonetilir; yeni app migrate ETMEZ.
 */
class CreateTahsilatIndirimHareketleri extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tahsilat_indirim_hareketleri')) {
            return;
        }
        Schema::create('tahsilat_indirim_hareketleri', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('tahsilat_id');
            $t->string('kalem_tip', 16);          // hizmet | urun | paket
            $t->unsignedBigInteger('kalem_id');   // adisyon_*_id
            $t->decimal('indirim_delta', 12, 2);  // bu tahsilatta EKLENEN indirim (TL)
            $t->timestamps();

            $t->index('tahsilat_id', 'tih_tahsilat_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tahsilat_indirim_hareketleri');
    }
}
