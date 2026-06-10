<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsPaketSiparisleriTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sms_paket_siparisleri')) {
            return;
        }

        Schema::create('sms_paket_siparisleri', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id')->index();
            $table->unsignedInteger('paket_id')->nullable();
            $table->integer('sms_adet')->default(0);
            $table->double('tutar')->default(0);          // KDV/tüm vergiler dahil toplam
            $table->string('merchant_oid', 100)->unique(); // PayTR sipariş no

            // 0=beklemede, 1=başarılı, 2=başarısız
            $table->tinyInteger('durum')->default(0);
            $table->string('basarisiz_neden')->nullable();

            // Manuel SMS yükleme takibi (VoiceTelekom panelinden elle yüklenir)
            // 0=yüklenmedi, 1=yüklendi
            $table->tinyInteger('yukleme_durumu')->default(0);
            $table->dateTime('yukleme_tarihi')->nullable();
            $table->string('yukleyen')->nullable();

            // Fatura bilgileri (e-Arşiv için)
            $table->string('fatura_unvan')->nullable();
            $table->string('fatura_vkn', 20)->nullable();
            $table->string('fatura_vergi_dairesi')->nullable();
            $table->string('fatura_adres')->nullable();

            // Paraşüt e-Arşiv fatura takibi
            // 0=kesilmedi, 1=kesildi, 2=hata
            $table->tinyInteger('fatura_durumu')->default(0);
            $table->string('fatura_no')->nullable();
            $table->string('fatura_url')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_paket_siparisleri');
    }
}
