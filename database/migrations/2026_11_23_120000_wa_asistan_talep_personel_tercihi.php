<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "WhatsApp asistani size soruyor" bildirimi 2026-11-23'ten itibaren yonetici (rol < 5) ve sohbet yetkili
 * personel (rol 5) icin ayri ayarlanir. Daha once tohumlanmis salonlarda personel satiri 'ek' (kapali) olarak
 * yazilmisti; bugun bu bildirimi alan yetkili personel almaya devam etsin diye yonetici satiri personele kopyalanir.
 */
class WaAsistanTalepPersonelTercihi extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salon_bildirim_tercihleri')) return;
        $simdi = date('Y-m-d H:i:s');
        foreach (DB::table('salon_bildirim_tercihleri')->where('kod', 'wa_asistan_talep')->where('alici', 'yonetici')->get() as $y) {
            $var = DB::table('salon_bildirim_tercihleri')->where('salon_id', $y->salon_id)->where('kod', 'wa_asistan_talep')->where('alici', 'personel')->exists();
            $veri = ['sms' => (int) $y->sms, 'whatsapp' => (int) $y->whatsapp, 'push' => (int) $y->push, 'updated_at' => $simdi];
            if ($var) {
                DB::table('salon_bildirim_tercihleri')->where('salon_id', $y->salon_id)->where('kod', 'wa_asistan_talep')->where('alici', 'personel')->update($veri);
            } else {
                DB::table('salon_bildirim_tercihleri')->insert($veri + ['salon_id' => $y->salon_id, 'kod' => 'wa_asistan_talep', 'alici' => 'personel', 'created_at' => $simdi]);
            }
        }
    }

    public function down()
    {
        // Geri alinmaz (isletme tercihleri).
    }
}
