<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sadakat puani KAZANIM (Starbucks tarzi): tahsilat (harcama) ve gelinen randevu
 * (ziyaret) basina puan. Bakiye mevcut salon_sadakat_puanlari'nda; puan merdiveni
 * (salon_puan_odulleri) aynen harcar.
 *
 *  - salon_sadakat_ayarlari: salon basina kazanim kurali (varsayilan KAPALI).
 *  - sadakat_puan_hareketleri: kaynak (tahsilat/randevu) basina TEK satir defter.
 *    puan = su an bakiyeye yansimis miktar; kaynak silinirse 0'a cekilir ve fark
 *    bakiyeden dusulur. unique(kaynak, kaynak_id) -> cift puan imkansiz.
 */
class CreateSadakatPuanKazanim extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salon_sadakat_ayarlari')) {
            Schema::create('salon_sadakat_ayarlari', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('salon_id')->unique();
                $t->tinyInteger('aktif')->default(0);
                $t->decimal('harcama_tutari', 10, 2)->default(100);  // her X TL ...
                $t->unsignedInteger('harcama_puani')->default(0);     // ... icin Y puan
                $t->unsignedInteger('ziyaret_puani')->default(0);     // gelinen randevu basina
                $t->dateTime('baslangic')->nullable();                // bundan onceki kayitlar puan kazanmaz
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('sadakat_puan_hareketleri')) {
            Schema::create('sadakat_puan_hareketleri', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedInteger('salon_id');
                $t->unsignedInteger('user_id');
                $t->string('kaynak', 16);                 // tahsilat | randevu
                $t->unsignedBigInteger('kaynak_id');
                $t->decimal('puan', 10, 2)->default(0);   // bakiyeye su an yansimis puan
                $t->timestamps();

                $t->unique(['kaynak', 'kaynak_id'], 'sph_kaynak_uniq');
                $t->index(['salon_id', 'user_id'], 'sph_salon_user_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sadakat_puan_hareketleri');
        Schema::dropIfExists('salon_sadakat_ayarlari');
    }
}
