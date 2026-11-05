<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sinirsiz / sureli paket:
 *   sinirsiz         : 1 = seans adedi yok, gecerlilik suresince kullanilir
 *   gecerlilik_sayi  : sure rakami (ör. 6, 1, 2)
 *   gecerlilik_birim : 'ay' | 'yil'
 *   gunluk_limit     : sinirsiz pakette hizmet basina gunluk kullanim
 *
 * adisyon_paketler'e satis aninda KOPYALANIR: katalog sonradan degisse de satilmis paket
 * etkilenmez. Bitis tarihi saklanmaz; ilk "Geldi" seansindan hesaplanir (App\Services\PaketGecerlilik).
 */
class AddSinirsizGecerlilikToPaketler extends Migration
{
    public function up()
    {
        foreach (['paketler', 'adisyon_paketler'] as $tablo) {
            if (!Schema::hasColumn($tablo, 'sinirsiz')) {
                Schema::table($tablo, function (Blueprint $table) {
                    $table->boolean('sinirsiz')->default(false);
                    $table->unsignedSmallInteger('gecerlilik_sayi')->nullable();
                    $table->string('gecerlilik_birim', 3)->nullable();
                    $table->unsignedSmallInteger('gunluk_limit')->nullable();
                });
            }
        }
    }

    public function down()
    {
        foreach (['paketler', 'adisyon_paketler'] as $tablo) {
            if (Schema::hasColumn($tablo, 'sinirsiz')) {
                Schema::table($tablo, function (Blueprint $table) {
                    $table->dropColumn(['sinirsiz', 'gecerlilik_sayi', 'gecerlilik_birim', 'gunluk_limit']);
                });
            }
        }
    }
}
