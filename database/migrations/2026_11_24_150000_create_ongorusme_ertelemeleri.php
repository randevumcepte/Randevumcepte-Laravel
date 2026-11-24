<?php

use App\Services\OnGorusmeErteleme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOngorusmeErtelemeleri extends Migration
{
    public function up()
    {
        OnGorusmeErteleme::semaGaranti();
    }

    public function down()
    {
        Schema::dropIfExists(OnGorusmeErteleme::TABLO);
    }
}
