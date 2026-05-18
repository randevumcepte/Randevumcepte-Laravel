<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaslikToBildirimler extends Migration
{
    public function up()
    {
        Schema::table('bildirimler', function (Blueprint $table) {
            if (!Schema::hasColumn('bildirimler', 'baslik')) {
                // NotificationService push bildirimlerinin baslik'ini saklar.
                // Eski kayitlarda aciklama tek alandi; yeni bildirimler iki ayri
                // alan kullaniyor (push notification.title + push notification.body).
                $table->string('baslik', 255)->nullable()->after('salon_id');
            }
        });
    }

    public function down()
    {
        Schema::table('bildirimler', function (Blueprint $table) {
            if (Schema::hasColumn('bildirimler', 'baslik')) {
                $table->dropColumn('baslik');
            }
        });
    }
}
