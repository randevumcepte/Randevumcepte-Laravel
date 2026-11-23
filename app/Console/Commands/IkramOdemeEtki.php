<?php

namespace App\Console\Commands;

use App\Services\DamgaKartiServisi;
use Illuminate\Console\Command;

/**
 * Salt okuma: Ikram Hizmet'te "odemesi alinmis olsun" sarti (DamgaKartiServisi::ODEME_SARTI)
 * acilirsa kac musterinin ilerlemesi/hakki duser. Hicbir kayit degistirmez.
 *
 *   php artisan ikram:odeme-etki
 *   php artisan ikram:odeme-etki 236
 */
class IkramOdemeEtki extends Command
{
    protected $signature = 'ikram:odeme-etki {salonId?}';
    protected $description = 'Ikram Hizmet odeme sarti acilirsa etkilenecek musteri sayisi (salt okuma)';

    public function handle()
    {
        $satirlar = DamgaKartiServisi::odemeEtkisi($this->argument('salonId'));
        if (!$satirlar) {
            $this->info('Ikram kampanyasi yok.');
            return 0;
        }
        $this->table(['Kural', 'Salon', 'Aktif', 'Musteri', 'Damga', 'Odenmis damga', 'Etkilenen musteri', 'Hakki dusen'],
            array_map('array_values', $satirlar));
        $this->info('Toplam etkilenen: ' . array_sum(array_column($satirlar, 'etkilenen'))
            . ', hakki dusen: ' . array_sum(array_column($satirlar, 'hakki_dusen')));
        return 0;
    }
}
