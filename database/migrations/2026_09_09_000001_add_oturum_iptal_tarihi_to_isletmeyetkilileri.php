<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Web oturumu gecersiz kilma damgasi.
 *
 * Personel pasife alininca / silinince (ve bagli login kimliginin markada baska
 * aktif salonu kalmayinca) o kimligin AKTIF WEB oturumlari da sonlanmali. Mobil
 * tarafta bu, Passport token revoke ile saglaniyor; web (session) tarafinda ise
 * boyle bir "token" yok. Bunun icin isletmeyetkilileri'ne bir damga eklenir:
 * OturumServisi oturumu bitirmeye karar verince bu alani gunceller (now). Web'de
 * AuthenticateSession middleware, parola-hash mekanizmasina PARALEL olarak bu
 * damgayi da oturuma isler ve her istekte karsilastirir; damga degismisse (yani
 * pasif/silme sonrasi) oturum sonlanir -> tekrar giris istenir.
 *
 * Ayni mekanizma parola degisimini de kapsar (getAuthPassword damgaya dahil).
 * Idempotent.
 */
class AddOturumIptalTarihiToIsletmeyetkilileri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('isletmeyetkilileri')) {
            return;
        }
        Schema::table('isletmeyetkilileri', function (Blueprint $table) {
            if (!Schema::hasColumn('isletmeyetkilileri', 'oturum_iptal_tarihi')) {
                $table->timestamp('oturum_iptal_tarihi')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('isletmeyetkilileri')) {
            return;
        }
        Schema::table('isletmeyetkilileri', function (Blueprint $table) {
            if (Schema::hasColumn('isletmeyetkilileri', 'oturum_iptal_tarihi')) {
                $table->dropColumn('oturum_iptal_tarihi');
            }
        });
    }
}
