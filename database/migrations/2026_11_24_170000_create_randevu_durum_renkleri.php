<?php

use App\Services\RandevuDurumRenk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRandevuDurumRenkleri extends Migration
{
    public function up()
    {
        RandevuDurumRenk::semaGaranti();
    }

    public function down()
    {
        Schema::dropIfExists(RandevuDurumRenk::TABLO);
    }
}
