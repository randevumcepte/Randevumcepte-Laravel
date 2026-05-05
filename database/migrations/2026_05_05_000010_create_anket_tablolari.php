<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnketTablolari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('anket_sablonlari')) {
            Schema::create('anket_sablonlari', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('salon_id')->index();
                $table->string('ad', 200);
                $table->text('aciklama')->nullable();
                $table->mediumText('sorular_json')->nullable();
                $table->boolean('otomatik_gonder')->default(false);
                $table->unsignedSmallInteger('gonder_saat_sonra')->default(24);
                $table->boolean('aktif')->default(true);
                $table->boolean('varsayilan')->default(false);
                $table->integer('sira')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('anket_gonderimleri')) {
            Schema::create('anket_gonderimleri', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('salon_id')->index();
                $table->unsignedInteger('sablon_id');
                $table->unsignedInteger('randevu_id')->nullable()->index();
                $table->unsignedInteger('arsiv_id')->nullable();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->unsignedInteger('personel_id')->nullable()->index();
                $table->string('token', 64)->unique();
                $table->string('ad_soyad', 150)->nullable();
                $table->string('telefon', 20)->nullable();
                $table->string('gonderim_kanali', 20)->default('sms'); // sms / whatsapp / manuel
                $table->timestamp('gonderim_zamani')->nullable();
                $table->timestamp('son_gecerlilik')->nullable();
                $table->boolean('cevaplandi')->default(false);
                $table->timestamp('cevap_zamani')->nullable();
                $table->mediumText('cevaplar_json')->nullable();
                $table->tinyInteger('nps_skoru')->nullable();             // 0-10
                $table->decimal('csat_skoru', 3, 2)->nullable();         // ortalama 1.00-5.00
                $table->text('genel_yorum')->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 250)->nullable();
                $table->boolean('kvkk_onay')->default(false);
                $table->timestamps();

                $table->index(['salon_id', 'cevaplandi']);
                $table->index(['salon_id', 'cevap_zamani']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('anket_gonderimleri');
        Schema::dropIfExists('anket_sablonlari');
    }
}
