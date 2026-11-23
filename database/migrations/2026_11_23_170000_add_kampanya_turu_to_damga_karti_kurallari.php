<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ikram Hizmet kampanya turu + kampanya bitisi:
 *   tur = 'ikram' (X al M hediye, eski davranis) | 'indirim' (X al, sonraki M alimda %indirim_orani)
 *   bitis_tarihi = bu tarihten (dahil) sonraki ziyaretler sayilmaz; NULL = suresiz kampanya.
 * Kazanilmis hak kampanya bitince de (kullanim suresi icinde) kullanilir.
 */
class AddKampanyaTuruToDamgaKartiKurallari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('damga_karti_kurallari')) return;
        Schema::table('damga_karti_kurallari', function (Blueprint $t) {
            if (!Schema::hasColumn('damga_karti_kurallari', 'tur')) {
                $t->string('tur', 10)->default('ikram')->after('hizmet_id');
            }
            if (!Schema::hasColumn('damga_karti_kurallari', 'indirim_orani')) {
                $t->unsignedTinyInteger('indirim_orani')->nullable()->after('ucretsiz_adet');
            }
            if (!Schema::hasColumn('damga_karti_kurallari', 'bitis_tarihi')) {
                $t->date('bitis_tarihi')->nullable()->after('baslangic');
            }
        });
    }

    public function down()
    {
        Schema::table('damga_karti_kurallari', function (Blueprint $t) {
            $t->dropColumn(['tur', 'indirim_orani', 'bitis_tarihi']);
        });
    }
}
