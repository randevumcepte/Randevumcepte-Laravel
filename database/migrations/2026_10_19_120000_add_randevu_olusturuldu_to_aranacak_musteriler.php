<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRandevuOlusturulduToAranacakMusteriler extends Migration
{
    public function up()
    {
        Schema::table('aranacak_musteriler', function (Blueprint $t) {
            if (!Schema::hasColumn('aranacak_musteriler', 'randevu_olusturuldu')) {
                // Cagri Merkezi: bu musteriye NORMAL salon randevusu verildiyse 1.
                // durum=8'den FARKLI: kayit listede KALIR (kuyruktan cikmaz), sadece
                // "Randevu Verildi" rozeti gosterilir. (Arama randevusu=durum 3 listeden cikarir.)
                $t->tinyInteger('randevu_olusturuldu')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('aranacak_musteriler', function (Blueprint $t) {
            if (Schema::hasColumn('aranacak_musteriler', 'randevu_olusturuldu')) {
                $t->dropColumn('randevu_olusturuldu');
            }
        });
    }
}
