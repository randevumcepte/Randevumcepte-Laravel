<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Services\NotificationTypes;
use Illuminate\Console\Command;

/**
 * Gercek bildirim servisiyle (NotificationService) tek kisiye deneme bildirimi gonderir: salonun Firebase
 * projesi, marka suzgeci, tur ve dokununca acilacak hedef gercek gonderimle ayni sekilde calisir.
 *
 *   php artisan push:dene musteri 46120 236 --randevu=12345
 *   php artisan push:dene personel 1580 236 --tur=team_request --hedef=ekibim
 *
 * --tur verilmezse: --randevu varsa randevu hatirlatma, yoksa genel duyuru. --hedef: deep link route adi
 * (appointment_detail, ikram_hizmet, sessions, wheel, customer_detail, ekibim, izinlerim ...),
 * parametreleri --param=anahtar:deger ile (birden fazla verilebilir).
 */
class PushDene extends Command
{
    protected $signature = 'push:dene {kime : musteri|personel|yetkili} {id : user_id (musteri) ya da salon_personelleri.id} {salonId}
        {--tur= : bildirim turu (NotificationTypes)} {--randevu= : randevu id} {--hedef= : deep link route}
        {--param=* : deep link parametresi anahtar:deger} {--baslik=Deneme bildirimi} {--mesaj=Bu bir deneme bildirimidir. Dokununca ilgili ekran açılmalı.}';
    protected $description = 'Gercek bildirim servisiyle tek kisiye deneme bildirimi gonderir (tur + hedef + randevu)';

    public function handle()
    {
        $kime = $this->argument('kime');
        $id = (int) $this->argument('id');
        $salonId = (int) $this->argument('salonId');
        if ($kime === 'musteri') $ns = NotificationService::toCustomer($id, $salonId);
        elseif ($kime === 'personel') $ns = NotificationService::toStaff($id, $salonId);
        elseif ($kime === 'yetkili') $ns = NotificationService::toOwner($id, $salonId);
        else { $this->error('kime: musteri | personel | yetkili'); return 1; }

        $randevu = $this->option('randevu') ? (int) $this->option('randevu') : null;
        $tur = $this->option('tur') ?: ($randevu ? NotificationTypes::APPOINTMENT_REMINDER : NotificationTypes::SYSTEM_ANNOUNCEMENT);
        $ns->type($tur)->title((string) $this->option('baslik'))->body((string) $this->option('mesaj'))->karaListeMuaf();
        if ($randevu) $ns->randevu($randevu);

        $hedef = $this->option('hedef') ?: ($randevu ? 'appointment_detail' : null);
        if ($hedef) {
            $p = [];
            foreach ((array) $this->option('param') as $kv) {
                if (strpos($kv, ':') === false) continue;
                [$k, $v] = explode(':', $kv, 2);
                $p[trim($k)] = trim($v);
            }
            if ($randevu && $hedef === 'appointment_detail') $p['randevu_id'] = $randevu;
            if (!isset($p['salon_id'])) $p['salon_id'] = $salonId;
            $ns->deepLink($hedef, $p);
        }

        $this->line("Gonderiliyor: kime={$kime} id={$id} salon={$salonId} tur={$tur} hedef=" . ($hedef ?: '-') . ' randevu=' . ($randevu ?: '-'));
        $r = $ns->send();
        $this->line('Sonuc: gonderilen=' . ($r['sent'] ?? 0) . ' hatali=' . ($r['failed'] ?? 0) . ' cihaz=' . ($r['total'] ?? 0));
        foreach ((array) ($r['hatalar'] ?? []) as $h) $this->error('  ' . $h);
        if (empty($r['total'])) $this->warn('Bu kisinin bu salonun uygulamasinda kayitli cihazi yok (php artisan push:teshis ' . $salonId . ').');
        return 0;
    }
}
