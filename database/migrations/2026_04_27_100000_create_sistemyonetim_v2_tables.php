<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSistemyonetimV2Tables extends Migration
{
    public function up()
    {
        // sistemyoneticileri: rol/aktif/son giris kolonlari
        if (Schema::hasTable('sistemyoneticileri')) {
            Schema::table('sistemyoneticileri', function (Blueprint $table) {
                if (!Schema::hasColumn('sistemyoneticileri', 'rol')) {
                    $table->string('rol', 30)->default('destek')->comment('super_admin|yonetici|destek|izleyici');
                }
                if (!Schema::hasColumn('sistemyoneticileri', 'aktif')) {
                    $table->tinyInteger('aktif')->default(1);
                }
                if (!Schema::hasColumn('sistemyoneticileri', 'telefon')) {
                    $table->string('telefon', 30)->nullable();
                }
                if (!Schema::hasColumn('sistemyoneticileri', 'son_giris_tarihi')) {
                    $table->timestamp('son_giris_tarihi')->nullable();
                }
                if (!Schema::hasColumn('sistemyoneticileri', 'son_giris_ip')) {
                    $table->string('son_giris_ip', 45)->nullable();
                }
                if (!Schema::hasColumn('sistemyoneticileri', 'notlar')) {
                    $table->text('notlar')->nullable();
                }
            });

            // mevcut admin=1 olanlari super_admin yap
            try {
                DB::table('sistemyoneticileri')->where('admin', 1)->update(['rol' => 'super_admin']);
            } catch (\Exception $e) { /* admin sutunu yoksa gec */ }
        }

        // salonlar: askiya alma alani
        if (Schema::hasTable('salonlar')) {
            Schema::table('salonlar', function (Blueprint $table) {
                if (!Schema::hasColumn('salonlar', 'askiya_alindi')) {
                    $table->tinyInteger('askiya_alindi')->default(0);
                }
                if (!Schema::hasColumn('salonlar', 'askiya_alma_sebebi')) {
                    $table->string('askiya_alma_sebebi', 255)->nullable();
                }
                if (!Schema::hasColumn('salonlar', 'askiya_alan_user_id')) {
                    $table->unsignedInteger('askiya_alan_user_id')->nullable();
                }
                if (!Schema::hasColumn('salonlar', 'askiya_alma_tarihi')) {
                    $table->timestamp('askiya_alma_tarihi')->nullable();
                }
            });
        }

        // Audit log
        if (!Schema::hasTable('sistemyonetim_audit_log')) {
            Schema::create('sistemyonetim_audit_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('user_name', 120)->nullable();
                $table->string('user_rol', 30)->nullable();
                $table->string('action', 80);
                $table->string('target_type', 60)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('target_label', 200)->nullable();
                $table->text('aciklama')->nullable();
                $table->text('meta')->nullable()->comment('JSON: eski/yeni veri vs.');
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at'], 'sy_audit_user_at_idx');
                $table->index(['target_type', 'target_id'], 'sy_audit_target_idx');
                $table->index(['action', 'created_at'], 'sy_audit_action_at_idx');
            });
        }

        // Login loglari
        if (!Schema::hasTable('sistemyonetim_login_loglari')) {
            Schema::create('sistemyonetim_login_loglari', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('email_attempt', 191);
                $table->tinyInteger('basarili')->default(0);
                $table->string('hata', 150)->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['email_attempt', 'created_at'], 'sy_login_email_at_idx');
                $table->index('user_id', 'sy_login_user_idx');
            });
        }

        // Impersonation loglari (1-tik salon hesabina giris)
        if (!Schema::hasTable('sistemyonetim_impersonation_loglari')) {
            Schema::create('sistemyonetim_impersonation_loglari', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id');
                $table->string('user_name', 120);
                $table->unsignedBigInteger('salon_id');
                $table->string('salon_adi', 200)->nullable();
                $table->unsignedBigInteger('isletme_yetkili_id')->nullable();
                $table->string('isletme_yetkili_email', 191)->nullable();
                $table->string('sebep', 255)->nullable();
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->timestamp('baslangic_tarihi')->useCurrent();
                $table->timestamp('bitis_tarihi')->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->index(['salon_id', 'baslangic_tarihi'], 'sy_imp_salon_at_idx');
                $table->index('user_id', 'sy_imp_user_idx');
            });
        }

        // Salon notlari (CRM)
        if (!Schema::hasTable('sistemyonetim_salon_notlari')) {
            Schema::create('sistemyonetim_salon_notlari', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('salon_id');
                $table->unsignedInteger('user_id');
                $table->string('user_name', 120);
                $table->string('baslik', 200)->nullable();
                $table->text('icerik');
                $table->string('tip', 30)->default('genel')->comment('genel|uyari|onemli|sikayet|talep|odeme');
                $table->tinyInteger('pinned')->default(0);
                $table->timestamps();
                $table->index(['salon_id', 'created_at'], 'sy_not_salon_at_idx');
            });
        }

        // Destek talepleri (ticket)
        if (!Schema::hasTable('sistemyonetim_destek_talepleri')) {
            Schema::create('sistemyonetim_destek_talepleri', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('numara', 20)->unique('sy_ticket_numara_uniq');
                $table->unsignedBigInteger('salon_id')->nullable();
                $table->string('salon_adi', 200)->nullable();
                $table->string('iletisim_ad', 120)->nullable();
                $table->string('iletisim_telefon', 30)->nullable();
                $table->string('iletisim_email', 191)->nullable();
                $table->string('konu', 250);
                $table->text('aciklama')->nullable();
                $table->string('kategori', 30)->default('diger')->comment('teknik|odeme|egitim|ozellik|sikayet|diger');
                $table->string('oncelik', 20)->default('orta')->comment('dusuk|orta|yuksek|acil');
                $table->string('durum', 20)->default('acik')->comment('acik|islemde|bekliyor|cozumlendi|kapali');
                $table->unsignedInteger('atanan_user_id')->nullable();
                $table->string('atanan_user_name', 120)->nullable();
                $table->unsignedInteger('olusturan_user_id')->nullable();
                $table->string('olusturan_user_name', 120)->nullable();
                $table->timestamp('ilk_yanit_tarihi')->nullable();
                $table->timestamp('cozumlenme_tarihi')->nullable();
                $table->timestamp('kapanis_tarihi')->nullable();
                $table->timestamps();
                $table->index(['durum', 'oncelik'], 'sy_ticket_durum_oncelik_idx');
                $table->index('salon_id', 'sy_ticket_salon_idx');
                $table->index('atanan_user_id', 'sy_ticket_atanan_idx');
            });
        }

        // Destek mesajlari (ticket replies)
        if (!Schema::hasTable('sistemyonetim_destek_mesajlari')) {
            Schema::create('sistemyonetim_destek_mesajlari', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ticket_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('user_name', 120);
                $table->string('user_tipi', 20)->default('ekip')->comment('ekip|salon|sistem');
                $table->text('mesaj');
                $table->tinyInteger('ic_not')->default(0)->comment('1: sadece ekip gorur');
                $table->timestamps();
                $table->index('ticket_id', 'sy_msg_ticket_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sistemyonetim_destek_mesajlari');
        Schema::dropIfExists('sistemyonetim_destek_talepleri');
        Schema::dropIfExists('sistemyonetim_salon_notlari');
        Schema::dropIfExists('sistemyonetim_impersonation_loglari');
        Schema::dropIfExists('sistemyonetim_login_loglari');
        Schema::dropIfExists('sistemyonetim_audit_log');

        if (Schema::hasTable('salonlar')) {
            Schema::table('salonlar', function (Blueprint $table) {
                foreach (['askiya_alindi', 'askiya_alma_sebebi', 'askiya_alan_user_id', 'askiya_alma_tarihi'] as $col) {
                    if (Schema::hasColumn('salonlar', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('sistemyoneticileri')) {
            Schema::table('sistemyoneticileri', function (Blueprint $table) {
                foreach (['rol', 'aktif', 'telefon', 'son_giris_tarihi', 'son_giris_ip', 'notlar'] as $col) {
                    if (Schema::hasColumn('sistemyoneticileri', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
