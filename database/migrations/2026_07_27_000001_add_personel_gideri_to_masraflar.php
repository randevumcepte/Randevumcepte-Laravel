<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * masraflar.personel_gideri: 0 = normal salon masrafi (kasadan duser, hakedise
 * etki etmez), 1 = personel gideri (personel kasadan para aldi; hem kasadan duser
 * HEM personelin hakedisinden dusulur). Eski tum kayitlar 0 (salon masrafi) sayilir.
 */
class AddPersonelGideriToMasraflar extends Migration
{
    public function up()
    {
        if(!Schema::hasColumn('masraflar','personel_gideri')){
            Schema::table('masraflar', function (Blueprint $table) {
                $table->boolean('personel_gideri')->default(0)->after('harcayan_id');
            });
        }
    }

    public function down()
    {
        if(Schema::hasColumn('masraflar','personel_gideri')){
            Schema::table('masraflar', function (Blueprint $table) {
                $table->dropColumn('personel_gideri');
            });
        }
    }
}
