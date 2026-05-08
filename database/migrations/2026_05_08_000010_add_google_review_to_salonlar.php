<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleReviewToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'google_review_url')) {
                $table->text('google_review_url')->nullable()->after('whatsapp_durum');
            }
            if (!Schema::hasColumn('salonlar', 'google_place_id')) {
                $table->string('google_place_id', 200)->nullable()->after('google_review_url');
            }
            if (!Schema::hasColumn('salonlar', 'google_review_esik_nps')) {
                $table->tinyInteger('google_review_esik_nps')->default(9)->after('google_place_id'); // 0-10 NPS eşiği
            }
            if (!Schema::hasColumn('salonlar', 'google_review_esik_csat')) {
                $table->decimal('google_review_esik_csat', 3, 2)->default(4.50)->after('google_review_esik_nps'); // 1-5 CSAT eşiği
            }
            if (!Schema::hasColumn('salonlar', 'kotu_puan_uyari_telefon')) {
                $table->string('kotu_puan_uyari_telefon', 20)->nullable()->after('google_review_esik_csat'); // SMS uyarı tel
            }
            if (!Schema::hasColumn('salonlar', 'kotu_puan_uyari_esik_nps')) {
                $table->tinyInteger('kotu_puan_uyari_esik_nps')->default(6)->after('kotu_puan_uyari_telefon'); // bu eşit/altı NPS uyarı
            }
            if (!Schema::hasColumn('salonlar', 'kotu_puan_uyari_esik_csat')) {
                $table->decimal('kotu_puan_uyari_esik_csat', 3, 2)->default(2.50)->after('kotu_puan_uyari_esik_nps');
            }
            if (!Schema::hasColumn('salonlar', 'reputation_premium_aktif')) {
                $table->boolean('reputation_premium_aktif')->default(false)->after('kotu_puan_uyari_esik_csat');
            }
        });

        // anket_gonderimleri'ne Google'a tıklama tracking
        Schema::table('anket_gonderimleri', function (Blueprint $table) {
            if (!Schema::hasColumn('anket_gonderimleri', 'google_yonlendirildi')) {
                $table->boolean('google_yonlendirildi')->default(false)->after('genel_yorum');
            }
            if (!Schema::hasColumn('anket_gonderimleri', 'kotu_puan_uyari_gonderildi')) {
                $table->boolean('kotu_puan_uyari_gonderildi')->default(false)->after('google_yonlendirildi');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            $table->dropColumn([
                'google_review_url',
                'google_place_id',
                'google_review_esik_nps',
                'google_review_esik_csat',
                'kotu_puan_uyari_telefon',
                'kotu_puan_uyari_esik_nps',
                'kotu_puan_uyari_esik_csat',
                'reputation_premium_aktif',
            ]);
        });
        Schema::table('anket_gonderimleri', function (Blueprint $table) {
            $table->dropColumn(['google_yonlendirildi', 'kotu_puan_uyari_gonderildi']);
        });
    }
}
