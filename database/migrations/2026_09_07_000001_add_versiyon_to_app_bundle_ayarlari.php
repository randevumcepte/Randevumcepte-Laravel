<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Genel (beyaz-etiket olmayan, tüm uyelik 1/2 salonlarının kullandığı ortak)
 * uygulama için sürüm kontrolü.
 *
 * Marka applerde versiyonAppKontrol, salonlar tablosunda app_bundle ile eşleşen
 * salonun ios/android_son_versiyon alanlarına bakar (her marka = tek salon).
 * Genel app tek bir app_bundle (com.randevumcepte.randevumcepte) ile TÜM uyelik
 * 1/2 salonlarında ortak çalıştığı için sürüm bilgisi salon-bazlı olamaz.
 *
 * Bu migration, zaten app_bundle bazlı olan app_bundle_ayarlari tablosuna sürüm
 * alanları ekler. versiyonAppKontrol önce buraya bakar; sürüm doluysa buradan
 * döner (bundle-bazlı, global). Marka bundle'larında bu alanlar boş olduğundan
 * mevcut salon-bazlı mantık aynen çalışmaya devam eder (geriye dönük uyumlu).
 *
 * Idempotent.
 */
class AddVersiyonToAppBundleAyarlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('app_bundle_ayarlari')) {
            return;
        }

        Schema::table('app_bundle_ayarlari', function (Blueprint $table) {
            if (!Schema::hasColumn('app_bundle_ayarlari', 'android_son_versiyon')) {
                $table->string('android_son_versiyon', 20)->nullable();
            }
            if (!Schema::hasColumn('app_bundle_ayarlari', 'ios_son_versiyon')) {
                $table->string('ios_son_versiyon', 20)->nullable();
            }
            if (!Schema::hasColumn('app_bundle_ayarlari', 'huawei_son_versiyon')) {
                $table->string('huawei_son_versiyon', 20)->nullable();
            }
            if (!Schema::hasColumn('app_bundle_ayarlari', 'android_uygulama')) {
                $table->string('android_uygulama', 300)->nullable();
            }
            if (!Schema::hasColumn('app_bundle_ayarlari', 'ios_uygulama')) {
                $table->string('ios_uygulama', 300)->nullable();
            }
            if (!Schema::hasColumn('app_bundle_ayarlari', 'huawei_uygulama')) {
                $table->string('huawei_uygulama', 300)->nullable();
            }
        });

        // Genel app bundle satırı — idempotent.
        // Sürüm DEĞERLERİ mevcut yayın sürümüne (1.0.2) eşit; böylece migration
        // çalışınca kazara zorunlu güncelleme TETİKLENMEZ. Yeni sürüm yayınlayınca
        // bu satırdaki android/ios_son_versiyon değerini artır -> eski kurulumlar
        // güncellemeye zorlanır.
        //
        // ios_uygulama'yı App Store'daki gerçek numaralı URL ile GÜNCELLE
        // (ör: https://apps.apple.com/tr/app/idXXXXXXXXXX). Şu an boş bırakıldı;
        // sürüm eşit olduğu için iOS'ta güncelleme butonu zaten çıkmaz.
        $bundle = 'com.randevumcepte.randevumcepte';
        $now = date('Y-m-d H:i:s');
        $mevcut = DB::table('app_bundle_ayarlari')->where('app_bundle', $bundle)->first();
        if ($mevcut) {
            DB::table('app_bundle_ayarlari')->where('app_bundle', $bundle)->update([
                'android_son_versiyon' => $mevcut->android_son_versiyon ?? '1.0.2',
                'ios_son_versiyon'     => $mevcut->ios_son_versiyon ?? '1.0.2',
                'android_uygulama'     => $mevcut->android_uygulama
                    ?? 'https://play.google.com/store/apps/details?id=com.randevumcepte.randevumcepte',
                'updated_at'           => $now,
            ]);
        } else {
            DB::table('app_bundle_ayarlari')->insert([
                'app_bundle'           => $bundle,
                'android_son_versiyon' => '1.0.2',
                'ios_son_versiyon'     => '1.0.2',
                'android_uygulama'     => 'https://play.google.com/store/apps/details?id=com.randevumcepte.randevumcepte',
                'ios_uygulama'         => '',
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('app_bundle_ayarlari')) {
            return;
        }
        Schema::table('app_bundle_ayarlari', function (Blueprint $table) {
            foreach ([
                'android_son_versiyon', 'ios_son_versiyon', 'huawei_son_versiyon',
                'android_uygulama', 'ios_uygulama', 'huawei_uygulama',
            ] as $col) {
                if (Schema::hasColumn('app_bundle_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
