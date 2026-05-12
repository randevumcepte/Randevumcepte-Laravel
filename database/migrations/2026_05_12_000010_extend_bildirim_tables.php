<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendBildirimTables extends Migration
{
    public function up()
    {
        Schema::table('bildirim_kimlikleri', function (Blueprint $table) {
            if (!Schema::hasColumn('bildirim_kimlikleri', 'platform')) {
                $table->string('platform', 16)->nullable();
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'token_tipi')) {
                $table->string('token_tipi', 16)->default('fcm');
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'kullanici_tipi')) {
                $table->string('kullanici_tipi', 16)->nullable();
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'salon_id')) {
                $table->unsignedBigInteger('salon_id')->nullable();
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'aktif')) {
                $table->boolean('aktif')->default(true);
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'son_kullanim_tarihi')) {
                $table->timestamp('son_kullanim_tarihi')->nullable();
            }
            if (!Schema::hasColumn('bildirim_kimlikleri', 'gonderim_hatalari')) {
                $table->unsignedSmallInteger('gonderim_hatalari')->default(0);
            }
        });

        Schema::table('bildirimler', function (Blueprint $table) {
            if (!Schema::hasColumn('bildirimler', 'tip')) {
                $table->string('tip', 64)->nullable();
            }
            if (!Schema::hasColumn('bildirimler', 'deep_link')) {
                $table->string('deep_link', 512)->nullable();
            }
            if (!Schema::hasColumn('bildirimler', 'image_url')) {
                $table->string('image_url', 512)->nullable();
            }
            if (!Schema::hasColumn('bildirimler', 'popup')) {
                $table->boolean('popup')->default(false);
            }
            if (!Schema::hasColumn('bildirimler', 'extra_data')) {
                $table->text('extra_data')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('bildirim_kimlikleri', function (Blueprint $table) {
            foreach (['platform','token_tipi','kullanici_tipi','salon_id','aktif','son_kullanim_tarihi','gonderim_hatalari'] as $c) {
                if (Schema::hasColumn('bildirim_kimlikleri', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('bildirimler', function (Blueprint $table) {
            foreach (['tip','deep_link','image_url','popup','extra_data'] as $c) {
                if (Schema::hasColumn('bildirimler', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
}
