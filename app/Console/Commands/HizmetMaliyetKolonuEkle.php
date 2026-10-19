<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class HizmetMaliyetKolonuEkle extends Command
{
    protected $signature = 'hizmet:maliyet-kolon-ekle';
    protected $description = 'salon_sunulan_hizmetler tablosuna maliyet kolonu ekle (idempotent)';

    public function handle()
    {
        if (Schema::hasColumn('salon_sunulan_hizmetler', 'maliyet')) {
            $this->info('maliyet kolonu zaten var.');
            return 0;
        }
        Schema::table('salon_sunulan_hizmetler', function (Blueprint $table) {
            $table->decimal('maliyet', 12, 2)->default(0)->after('son_fiyat');
        });
        $this->info('maliyet kolonu basariyla eklendi.');
        return 0;
    }
}
