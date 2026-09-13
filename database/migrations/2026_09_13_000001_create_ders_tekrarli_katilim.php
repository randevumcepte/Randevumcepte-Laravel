<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// Tekrarli otomatik ders katilimi (studyo modu): musteriye satilan paket/hizmet
// seanslarini, secilen gun/saat/hoca tercihine gore ileri tarihli grup dersi
// oturumlarina otomatik dagitir. Katilim kayitlari ders_katilimcilar.tekrarli_id
// ile bu tabloya baglanir (yeniden dagitimda gelecek katilimlar bulunup silinir).
class CreateDersTekrarliKatilim extends Migration
{
    public function up()
    {
        Schema::create('ders_tekrarli_katilim', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('hizmet_id')->nullable();          // dusum + oturum eslesme hizmeti
            $table->unsignedInteger('adisyon_paket_id')->nullable();   // kaynak satis (paket)
            $table->unsignedInteger('adisyon_hizmet_id')->nullable();  // kaynak satis (tek hizmet)
            $table->integer('toplam_seans')->default(0);               // dagitilacak hedef seans
            $table->string('gunler')->nullable();                      // JSON [1..7] (1=Pzt)
            $table->string('saatler')->nullable();                     // JSON ["09:00",..] veya null=hepsi
            $table->unsignedInteger('personel_id')->nullable();        // tercih edilen hoca (null=farketmez)
            $table->date('baslangic_tarihi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('ders_katilimcilar', function (Blueprint $table) {
            $table->unsignedInteger('tekrarli_id')->nullable()->after('aps_id')->index();
        });
    }

    public function down()
    {
        Schema::table('ders_katilimcilar', function (Blueprint $table) {
            $table->dropColumn('tekrarli_id');
        });
        Schema::dropIfExists('ders_tekrarli_katilim');
    }
}
