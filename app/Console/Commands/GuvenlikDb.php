<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Güvenlik Duvarı — root watchdog icin DB KOPRU komutu.
 *
 * NEDEN: Sunucu Docker'li. DB container'da, host'a 127.0.0.1:3310 map'li. Host'tan
 * (root cron / mysql CLI) baglanan istemci MySQL'e 172.17.0.1 (docker0) olarak gorunur
 * ve app kullanicisinin grant'i bu host'u kapsamadigi icin 'Access denied' alir.
 * Dolayisiyla watchdog'un bash->mysql yolu bu sunucuda CALISMAZ (olay/heartbeat dusmez).
 * PHP/artisan ise .env'i Dotenv ile okuyup app'in gecerli baglantisini kullanir (panelle
 * AYNI baglanti). Bu komut watchdog'un tum DB islerini PHP uzerinden yurutur.
 *
 * Kullanim (scripts/guvenlik-watchdog.sh cagirir):
 *   php artisan guvenlik:db kurallar            -> "WL <ip>" / "BL <ip>" satirlari (stdout)
 *   php artisan guvenlik:db heartbeat <ban>     -> heartbeat + aktif_ban upsert
 *   php artisan guvenlik:db olaylar <tsv_dosya> -> TSV'deki olaylari toplu insert
 *   php artisan guvenlik:db bildirildi          -> son 2 dk'daki bildirilmemis olaylari isaretle
 */
class GuvenlikDb extends Command
{
    protected $signature = 'guvenlik:db {action} {arg1?}';

    protected $description = 'Guvenlik watchdog icin DB kopru komutu (Docker grant nedeniyle host mysql CLI baglanamiyor)';

    public function handle()
    {
        switch ((string) $this->argument('action')) {
            case 'kurallar':   return $this->kurallar();
            case 'heartbeat':  return $this->heartbeat((int) $this->argument('arg1'));
            case 'olaylar':    return $this->olaylar((string) $this->argument('arg1'));
            case 'bildirildi': return $this->bildirildi();
            default:
                $this->error('bilinmeyen action');
                return 1;
        }
    }

    /** Whitelist + blacklist IP'leri sade satir formatinda bas (watchdog awk ile ayristirir). */
    private function kurallar()
    {
        if (!Schema::hasTable('guvenlik_ip_kurallari')) return 0;
        foreach (DB::table('guvenlik_ip_kurallari')->where('tip', 'whitelist')->pluck('ip') as $ip) {
            $this->line('WL ' . $ip);
        }
        foreach (DB::table('guvenlik_ip_kurallari')->where('tip', 'blacklist')->pluck('ip') as $ip) {
            $this->line('BL ' . $ip);
        }
        return 0;
    }

    /** Her turda: watchdog canli (heartbeat) + o anki ipset ban sayisi. */
    private function heartbeat($ban)
    {
        if (!Schema::hasTable('guvenlik_durum')) return 0;
        $now = date('Y-m-d H:i:s');
        DB::table('guvenlik_durum')->updateOrInsert(['anahtar' => 'heartbeat'], ['deger' => 'ok', 'updated_at' => $now]);
        DB::table('guvenlik_durum')->updateOrInsert(['anahtar' => 'aktif_ban'], ['deger' => (string) max(0, $ban), 'updated_at' => $now]);
        return 0;
    }

    /** TSV dosyasindaki olaylari toplu insert et. Satir: tur\tip\tdeger\tesik\taksiyon\tdetay\tbildirildi */
    private function olaylar($dosya)
    {
        if (!$dosya || !is_file($dosya) || !Schema::hasTable('guvenlik_olaylari')) return 0;
        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach (file($dosya, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $s) {
            $p = explode("\t", $s);
            if (count($p) < 7) continue;
            $rows[] = [
                'tur'        => mb_substr($p[0], 0, 30),
                'ip'         => $p[1] !== '' ? mb_substr($p[1], 0, 45) : null,
                'deger'      => $p[2] !== '' ? (int) $p[2] : null,
                'esik'       => $p[3] !== '' ? (int) $p[3] : null,
                'aksiyon'    => mb_substr($p[4], 0, 20),
                'detay'      => $p[5] !== '' ? mb_substr($p[5], 0, 255) : null,
                'bildirildi' => (int) $p[6],
                'created_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('guvenlik_olaylari')->insert($chunk);
        }
        return 0;
    }

    /** Alarm gonderildikten sonra: son 2 dk'daki bildirilmemis olaylari isaretle. */
    private function bildirildi()
    {
        if (!Schema::hasTable('guvenlik_olaylari')) return 0;
        DB::table('guvenlik_olaylari')
            ->where('bildirildi', 0)
            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 120))
            ->update(['bildirildi' => 1]);
        return 0;
    }
}
