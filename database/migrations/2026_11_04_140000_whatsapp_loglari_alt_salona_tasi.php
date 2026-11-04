<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Paylasilan WhatsApp oturumu: loglar artik mesaji gonderen salona yazilir (WhatsAppService).
 * Eski kayitlar ust salonun id'siyle yazilmisti; kaynagi KESIN belli olanlar alt salona tasinir:
 *   1) randevuya bagli log → randevunun salonu
 *   2) randevusuz log → musteri yalniz alt salonun portfoyundeyse
 * Iki salonda da musterisi olan randevusuz loglar ayirt edilemez, ust salonda kalir.
 */
class WhatsappLoglariAltSalonaTasi extends Migration
{
    public function up()
    {
        $altSalonlar = DB::table('salonlar')->whereNotNull('whatsapp_paylasilan_salon_id')
            ->whereColumn('whatsapp_paylasilan_salon_id', '<>', 'id')
            ->get(['id', 'whatsapp_paylasilan_salon_id as ust_id']);

        foreach ($altSalonlar as $s) {
            DB::update(
                'UPDATE whatsapp_gonderim_loglari wl JOIN randevular r ON r.id = wl.randevu_id
                 SET wl.salon_id = r.salon_id WHERE wl.salon_id = ? AND r.salon_id = ?',
                [$s->ust_id, $s->id]
            );
            DB::update(
                'UPDATE whatsapp_gonderim_loglari wl
                 JOIN musteri_portfoy alt ON alt.user_id = wl.user_id AND alt.salon_id = ?
                 LEFT JOIN musteri_portfoy ust ON ust.user_id = wl.user_id AND ust.salon_id = ?
                 SET wl.salon_id = ? WHERE wl.salon_id = ? AND wl.randevu_id IS NULL AND ust.user_id IS NULL',
                [$s->id, $s->ust_id, $s->id, $s->ust_id]
            );
        }
    }

    public function down()
    {
        // Geri alinmaz: tasima oncesi hangi kaydin nerede oldugu tutulmuyor (kayit silinmez, yalniz salon_id degisir).
    }
}
