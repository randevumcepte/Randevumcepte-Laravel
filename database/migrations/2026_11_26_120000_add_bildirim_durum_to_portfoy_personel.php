<?php

use App\Services\BildirimDurumSemasi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AddBildirimDurumToPortfoyPersonel extends Migration
{
    public function up()
    {
        BildirimDurumSemasi::ensure();
    }

    public function down()
    {
        foreach (BildirimDurumSemasi::TABLOLAR as $t) {
            DB::statement("ALTER TABLE `$t` DROP INDEX `idx_bildirim_durum`, DROP COLUMN `bildirim_durum`, DROP COLUMN `bildirim_durum_tarihi`, DROP COLUMN `bildirim_sorun_baslangic`, DROP COLUMN `bildirim_hata`, DROP COLUMN `bildirim_cihaz`");
        }
        Cache::forget(BildirimDurumSemasi::CACHE_ANAHTAR);
    }
}
