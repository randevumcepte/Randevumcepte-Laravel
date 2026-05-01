<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloudApiMarketingToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            // Hibrit mod: salon Baileys'i utility için kullanırken marketing'i Cloud API'den atabilir
            if (!Schema::hasColumn('salonlar', 'cloud_api_marketing_aktif')) {
                $table->boolean('cloud_api_marketing_aktif')->default(0)
                    ->comment('1 = marketing/kampanya mesajları Cloud API üzerinden, utility ise saglayici ne ise');
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_template_kampanya')) {
                $table->string('cloud_api_template_kampanya', 100)->nullable()
                    ->comment('Kampanya/duyuru için Meta onaylı marketing template adı');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_kampanya_aylik_dahil')) {
                $table->unsignedInteger('whatsapp_kampanya_aylik_dahil')->default(0)
                    ->comment('Pakete dahil aylık ücretsiz kampanya mesajı sayısı');
            }
            if (!Schema::hasColumn('salonlar', 'whatsapp_kampanya_birim_fiyat')) {
                $table->decimal('whatsapp_kampanya_birim_fiyat', 5, 2)->default(3.00)
                    ->comment('Aylık dahil aşıldığında salonun ödediği mesaj başı TL fiyatı');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach ([
                'cloud_api_marketing_aktif',
                'cloud_api_template_kampanya',
                'whatsapp_kampanya_aylik_dahil',
                'whatsapp_kampanya_birim_fiyat',
            ] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
