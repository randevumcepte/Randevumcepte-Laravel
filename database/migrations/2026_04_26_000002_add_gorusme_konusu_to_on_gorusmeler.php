<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGorusmeKonusuToOnGorusmeler extends Migration
{
    public function up()
    {
        Schema::table('on_gorusmeler', function (Blueprint $table) {
            if (!Schema::hasColumn('on_gorusmeler', 'gorusme_konusu')) {
                $table->string('gorusme_konusu', 255)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('on_gorusmeler', function (Blueprint $table) {
            if (Schema::hasColumn('on_gorusmeler', 'gorusme_konusu')) {
                $table->dropColumn('gorusme_konusu');
            }
        });
    }
}
