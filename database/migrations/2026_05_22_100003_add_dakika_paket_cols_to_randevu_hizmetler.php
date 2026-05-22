<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * randevu_hizmetler satirina dakika paketi baglantisi:
 *   musteri_dakika_paketi_id: bu satir hangi paketten dusecek
 *   paket_dakika: bu randevuda paketten kac dk eksilecek (NULL = sure_dk)
 */
class AddDakikaPaketColsToRandevuHizmetler extends Migration
{
    public function up()
    {
        Schema::table('randevu_hizmetler', function (Blueprint $table) {
            if (!Schema::hasColumn('randevu_hizmetler', 'musteri_dakika_paketi_id')) {
                $table->unsignedBigInteger('musteri_dakika_paketi_id')->nullable()->after('fiyat');
                $table->index('musteri_dakika_paketi_id', 'rh_mdp_idx');
            }
            if (!Schema::hasColumn('randevu_hizmetler', 'paket_dakika')) {
                $table->integer('paket_dakika')->nullable()->after('musteri_dakika_paketi_id');
            }
        });
    }

    public function down()
    {
        Schema::table('randevu_hizmetler', function (Blueprint $table) {
            if (Schema::hasColumn('randevu_hizmetler', 'musteri_dakika_paketi_id')) {
                $table->dropIndex('rh_mdp_idx');
                $table->dropColumn('musteri_dakika_paketi_id');
            }
            if (Schema::hasColumn('randevu_hizmetler', 'paket_dakika')) {
                $table->dropColumn('paket_dakika');
            }
        });
    }
}
