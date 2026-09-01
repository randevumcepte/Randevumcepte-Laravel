<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Apple 1.2 UGC uyumu: musteri uygunsuz yorumu bildirebilir.
 * salon_yorumlar tablosuna bildirim alanlari eklenir.
 * Isletme paneli bildirilen yorumlari rozetli gosterir, 24 saat icinde silme SLA.
 * Idempotent.
 */
class AddBildirimToSalonYorumlar extends Migration
{
    public function up()
    {
        Schema::table('salon_yorumlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salon_yorumlar', 'bildirilen_sayisi')) {
                $table->unsignedSmallInteger('bildirilen_sayisi')->default(0)->after('yorum');
            }
            if (!Schema::hasColumn('salon_yorumlar', 'bildirim_sebep')) {
                $table->string('bildirim_sebep', 200)->nullable()->after('bildirilen_sayisi');
            }
            if (!Schema::hasColumn('salon_yorumlar', 'bildiren_id')) {
                $table->unsignedBigInteger('bildiren_id')->nullable()->after('bildirim_sebep');
            }
            if (!Schema::hasColumn('salon_yorumlar', 'bildirim_tarihi')) {
                $table->timestamp('bildirim_tarihi')->nullable()->after('bildiren_id');
            }
        });
    }

    public function down()
    {
        Schema::table('salon_yorumlar', function (Blueprint $table) {
            foreach (['bildirim_tarihi', 'bildiren_id', 'bildirim_sebep', 'bildirilen_sayisi'] as $col) {
                if (Schema::hasColumn('salon_yorumlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
