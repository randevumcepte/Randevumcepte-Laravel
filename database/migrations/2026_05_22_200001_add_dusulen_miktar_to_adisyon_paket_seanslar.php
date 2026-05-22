<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * adisyon_paket_seanslar tablosuna 'dusulen_miktar' kolonu:
 *   Seans paketi popup'unda salonun girdigi miktar kadar duser.
 *   Default 1 (eski davranis): her geldi=true satir 1 seans sayilir.
 *   Salon "2 seans" veya "10 dk" gibi farkli bir miktar girebilir.
 *
 *   Kalan seans hesabi (paket detay/musteri seans listesi/raporlar)
 *   artik COUNT(geldi=true) yerine SUM(dusulen_miktar WHERE geldi=true)
 *   yapilir.
 */
class AddDusulenMiktarToAdisyonPaketSeanslar extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('adisyon_paket_seanslar', 'dusulen_miktar')) {
            Schema::table('adisyon_paket_seanslar', function (Blueprint $table) {
                $table->integer('dusulen_miktar')->default(1)->after('geldi');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('adisyon_paket_seanslar', 'dusulen_miktar')) {
            Schema::table('adisyon_paket_seanslar', function (Blueprint $table) {
                $table->dropColumn('dusulen_miktar');
            });
        }
    }
}
