<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Damga karti ("4 al 1 bedava"): isletme sahibi hizmet bazli kural tanimlar.
 *
 *  - damga_karti_kurallari: salon+hizmet basina tek kural. gereken_adet ucretli
 *    "Geldi" ziyaretine ucretsiz_adet seans hakki. baslangic = acildigi an;
 *    oncesindeki ziyaretler sayilmaz.
 *  - damga_karti_kullanimlari: kullanilan her ucretsiz seans. Kalem %100 indirimle
 *    kapanir, 0 TL tahsilat yazilir; bu satir ikisini birbirine baglar (geri alma icin).
 */
class CreateDamgaKarti extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('damga_karti_kurallari')) {
            Schema::create('damga_karti_kurallari', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('salon_id');
                $t->unsignedInteger('hizmet_id');
                $t->unsignedSmallInteger('gereken_adet')->default(4);
                $t->unsignedSmallInteger('ucretsiz_adet')->default(1);
                $t->tinyInteger('aktif')->default(1);
                $t->dateTime('baslangic')->nullable();
                $t->timestamps();

                $t->unique(['salon_id', 'hizmet_id'], 'dkk_salon_hizmet_uniq');
            });
        }

        if (!Schema::hasTable('damga_karti_kullanimlari')) {
            Schema::create('damga_karti_kullanimlari', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('salon_id');
                $t->unsignedInteger('kural_id');
                $t->unsignedInteger('user_id');
                $t->unsignedInteger('adisyon_hizmet_id')->unique('dkku_kalem_uniq');
                $t->unsignedInteger('randevu_id')->nullable();
                $t->unsignedInteger('tahsilat_id')->nullable();
                $t->decimal('indirim_tutari', 10, 2)->default(0);
                $t->unsignedInteger('olusturan_id')->nullable();
                $t->timestamps();

                $t->index(['kural_id', 'user_id'], 'dkku_kural_user_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('damga_karti_kullanimlari');
        Schema::dropIfExists('damga_karti_kurallari');
    }
}
