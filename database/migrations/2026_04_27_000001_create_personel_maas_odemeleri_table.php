<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonelMaasOdemeleriTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('personel_maas_odemeleri')) {
            Schema::create('personel_maas_odemeleri', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('personel_id');
                $table->unsignedInteger('salon_id');
                $table->char('donem', 7);
                $table->decimal('tutar', 12, 2);
                $table->date('odeme_tarihi');
                $table->string('odeme_yontemi', 60)->nullable();
                $table->string('aciklama', 300)->nullable();
                $table->unsignedInteger('ekleyen_yetkili_id')->nullable();
                $table->timestamps();

                $table->index(['salon_id', 'personel_id', 'donem']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('personel_maas_odemeleri');
    }
}
