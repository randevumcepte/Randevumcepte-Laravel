<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappProviderToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'whatsapp_saglayici')) {
                $table->string('whatsapp_saglayici', 20)->default('baileys')
                    ->comment('baileys (unofficial) | cloud_api (Meta resmi)');
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_phone_number_id')) {
                $table->string('cloud_api_phone_number_id', 50)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_token')) {
                $table->text('cloud_api_token')->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_template_1gun')) {
                $table->string('cloud_api_template_1gun', 100)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_template_yaklasan')) {
                $table->string('cloud_api_template_yaklasan', 100)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_template_iptal')) {
                $table->string('cloud_api_template_iptal', 100)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_template_guncelleme')) {
                $table->string('cloud_api_template_guncelleme', 100)->nullable();
            }
            if (!Schema::hasColumn('salonlar', 'cloud_api_template_dil')) {
                $table->string('cloud_api_template_dil', 10)->default('tr');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach ([
                'whatsapp_saglayici', 'cloud_api_phone_number_id', 'cloud_api_token',
                'cloud_api_template_1gun', 'cloud_api_template_yaklasan',
                'cloud_api_template_iptal', 'cloud_api_template_guncelleme',
                'cloud_api_template_dil',
            ] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
