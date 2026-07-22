<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Personeller;
use App\IsletmeYetkilileri;

/**
 * Yanlislikla "Personel" (rol 5) yapilip hesabi kilitlenen bir Hesap Sahibini
 * geri rol 1'e alir. Hem personeller.role_id hem model_has_roles guncellenir.
 *
 * Ornek:
 *   php artisan hesap:sahip-yap 391 --name=Ferdi
 *   php artisan hesap:sahip-yap 391 --gsm=05xxxxxxxxx
 *   php artisan hesap:sahip-yap 391 --yetkili=1234
 */
class HesapSahibiYap extends Command
{
    protected $signature = 'hesap:sahip-yap {salon : Salon (sube) id} {--name= : Personel adinda gecen metin} {--gsm= : Yetkili gsm/telefon} {--yetkili= : IsletmeYetkilileri id} {--rol=1 : Atanacak rol id (varsayilan 1=Hesap Sahibi)}';

    protected $description = 'Yanlislikla personele dusurulmus hesap sahibini geri rol 1 (Hesap Sahibi) yapar';

    public function handle()
    {
        $salon = (int) $this->argument('salon');
        $rol   = (int) $this->option('rol');
        $yetkiliId = $this->option('yetkili');
        $gsm  = $this->option('gsm');
        $name = $this->option('name');

        // 1) Hedef yetkili(ler)i coz
        $yetkili = null;

        if ($yetkiliId) {
            $yetkili = IsletmeYetkilileri::find((int) $yetkiliId);
        } elseif ($gsm) {
            $temiz = preg_replace('/\D/', '', $gsm);
            $yetkili = IsletmeYetkilileri::whereRaw("REPLACE(REPLACE(REPLACE(gsm1,' ',''),'-',''),'+','') LIKE ?", ["%{$temiz}%"])->first();
        } elseif ($name) {
            // Salondaki personel adindan yetkili_id bul
            $adaylar = Personeller::where('salon_id', $salon)
                ->where('personel_adi', 'like', "%{$name}%")
                ->get(['id', 'personel_adi', 'yetkili_id', 'role_id']);

            if ($adaylar->count() === 0) {
                $this->error("Salon {$salon} icinde '{$name}' adinda personel bulunamadi.");
                return 1;
            }
            if ($adaylar->count() > 1) {
                $this->warn('Birden fazla eslesme var, --yetkili ile netlestir:');
                foreach ($adaylar as $a) {
                    $this->line("  personel_id={$a->id}  yetkili_id={$a->yetkili_id}  role_id={$a->role_id}  ad={$a->personel_adi}");
                }
                return 1;
            }
            $yetkili = IsletmeYetkilileri::find($adaylar->first()->yetkili_id);
        } else {
            $this->error('En az bir tanimlayici verin: --name, --gsm veya --yetkili');
            return 1;
        }

        if (!$yetkili) {
            $this->error('Yetkili bulunamadi.');
            return 1;
        }

        $this->info("Yetkili: id={$yetkili->id}  ad={$yetkili->name}  gsm={$yetkili->gsm1}");

        // 2) personeller.role_id = $rol
        $personel = Personeller::where('salon_id', $salon)
            ->where('yetkili_id', $yetkili->id)
            ->first();

        if ($personel) {
            $eski = $personel->role_id;
            $personel->role_id = $rol;
            $personel->save();
            $this->info("personeller.role_id: {$eski} -> {$rol} (personel_id={$personel->id})");
        } else {
            $this->warn("Salon {$salon} icinde yetkili_id={$yetkili->id} olan personel satiri yok (yalnizca model_has_roles guncellenecek).");
        }

        // 3) model_has_roles: bu salon icin rolu yeniden yaz
        DB::table('model_has_roles')
            ->where('model_id', $yetkili->id)
            ->where('salon_id', $salon)
            ->delete();

        DB::insert(
            'insert into model_has_roles (role_id, model_type, model_id, salon_id) values (?, ?, ?, ?)',
            [$rol, 'App\\IsletmeYetkilileri', $yetkili->id, $salon]
        );
        $this->info("model_has_roles -> role_id={$rol} (model_id={$yetkili->id}, salon_id={$salon})");

        $this->info('Tamam. Kullanici tekrar giris yapmali (oturum yenilensin).');
        return 0;
    }
}
