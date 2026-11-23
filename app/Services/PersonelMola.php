<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Musaitlik durumu (2026-11-23): Musait / Molada / Izinli.
 * - Personel durumunu kendisi degistiremez: "Molada" secince yoneticilere/sahibe TALEP gider (15/30/45/60 dk
 *   ya da "saate kadar"); ilk karar veren gecerli. Onayda simdiden bitise kadar takvim kapatilir (saat kapama,
 *   saatkapamaekle ile ayni kayit), sure dolunca kendiliginden Musait.
 * - Erken donus (Musait) onaysiz: kalan kapama silinir/kisaltilir.
 * - Izinli personel secicide yok; Izinlerim'den onaylanan izin ya da yoneticinin dogrudan verdigi izin
 *   suresince "Izinli · X'e kadar" gorunur (personel_izinleri + gunluk kapamalar).
 * - Yonetici (rol < 5) kendi durumunu ve personelin durumunu onaysiz degistirir.
 * Durum ayri saklanmaz; onayli mola / izin kayitlarindan hesaplanir (takvimle celismez).
 */
class PersonelMola
{
    const SEMA = 'personel_mola_sema_v1';
    const SURELER = [15, 30, 45, 60];

    public static function semaGaranti()
    {
        try {
            if (Cache::get(self::SEMA)) return;
            if (!Schema::hasTable('personel_molalari')) {
                Schema::create('personel_molalari', function ($t) {
                    $t->increments('id');
                    $t->unsignedInteger('salon_id')->index();
                    $t->unsignedInteger('personel_id')->index();
                    $t->unsignedSmallInteger('dakika')->nullable();     // 15/30/45/60
                    $t->string('istenen_bitis', 5)->nullable();          // "saate kadar" HH:MM
                    $t->dateTime('baslangic')->nullable();               // onay aninda dolar
                    $t->dateTime('bitis')->nullable();
                    $t->string('durum', 20)->default('bekliyor');       // bekliyor | onaylandi | reddedildi | iptal | bitti
                    $t->unsignedInteger('talep_eden_personel_id')->nullable();
                    $t->unsignedInteger('karar_veren_personel_id')->nullable();
                    $t->timestamp('karar_tarihi')->nullable();
                    $t->unsignedInteger('kapama_randevu_id')->nullable();
                    $t->timestamps();
                });
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[PersonelMola] sema hatasi', ['err' => $e->getMessage()]);
        }
    }

    private static function tarihTr($ymd)
    {
        return date('d.m.Y', strtotime($ymd));
    }

    private static function sureMetni($m)
    {
        return $m->dakika ? $m->dakika . ' dk' : ($m->istenen_bitis ? $m->istenen_bitis . '\'e kadar' : '');
    }

    /** Anlik durum: onayli izin (bugun) > onayli mola (simdi) > musait; bekleyen mola talebi ayrica. */
    public static function durumDetay($personelId)
    {
        self::semaGaranti();
        $pid = (int) $personelId;
        $bugun = date('Y-m-d');
        $simdi = date('Y-m-d H:i:s');
        $d = ['durum' => 'musait', 'bitis' => null, 'metin' => 'Müsait', 'bekleyen_mola' => null];
        try {
            $izin = DB::table('personel_izinleri')->where('personel_id', $pid)->where('durum', 'onaylandi')
                ->where('baslangic', '<=', $bugun)->where('bitis', '>=', $bugun)->orderByDesc('bitis')->first();
            if ($izin) {
                return ['durum' => 'izinli', 'bitis' => $izin->bitis, 'metin' => 'İzinli · ' . self::tarihTr($izin->bitis) . '\'e kadar',
                    'izin_id' => (int) $izin->id, 'bekleyen_mola' => null];
            }
            $mola = DB::table('personel_molalari')->where('personel_id', $pid)->where('durum', 'onaylandi')
                ->where('baslangic', '<=', $simdi)->where('bitis', '>', $simdi)->orderByDesc('bitis')->first();
            if ($mola) {
                $d = ['durum' => 'molada', 'bitis' => $mola->bitis, 'metin' => 'Molada · ' . substr($mola->bitis, 11, 5) . '\'e kadar',
                    'mola_id' => (int) $mola->id, 'bekleyen_mola' => null];
            }
            $bek = DB::table('personel_molalari')->where('personel_id', $pid)->where('durum', 'bekliyor')
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))->orderByDesc('id')->first();
            if ($bek) $d['bekleyen_mola'] = ['id' => (int) $bek->id, 'sure' => self::sureMetni($bek)];
        } catch (\Throwable $e) {
            Log::warning('[PersonelMola] durum', ['err' => $e->getMessage()]);
        }
        return $d;
    }

    /** Personelin mola talebi. Donus: id ya da hata metni. */
    public static function talep($salonId, $p, $dakika, $bitisSaat, $req)
    {
        self::semaGaranti();
        $dakika = (int) $dakika;
        $bitisSaat = trim((string) $bitisSaat);
        if (!in_array($dakika, self::SURELER, true)) $dakika = 0;
        if (!$dakika) {
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $bitisSaat)) return 'Mola süresini seçin.';
            if (strtotime(date('Y-m-d') . ' ' . $bitisSaat) <= time() + 60) return 'Bitiş saati şu andan sonra olmalı.';
        } else {
            $bitisSaat = null;
        }
        $det = self::durumDetay($p->id);
        if ($det['durum'] === 'izinli') return 'Şu an izinlisiniz.';
        if ($det['durum'] === 'molada') return 'Zaten moladasınız.';
        if ($det['bekleyen_mola']) return 'Onay bekleyen bir mola talebiniz var.';
        $simdi = date('Y-m-d H:i:s');
        $id = DB::table('personel_molalari')->insertGetId([
            'salon_id' => $salonId, 'personel_id' => $p->id, 'dakika' => $dakika ?: null, 'istenen_bitis' => $bitisSaat,
            'durum' => 'bekliyor', 'talep_eden_personel_id' => $p->id, 'created_at' => $simdi, 'updated_at' => $simdi,
        ]);
        $sure = $dakika ? $dakika . ' dk' : $bitisSaat . '\'e kadar';
        $metin = $p->personel_adi . ' ' . $sure . ' mola istiyor';
        self::log($salonId, $req, 'mola_talep', $p, $metin);
        foreach (BildirimTercih::yoneticiIdleri($salonId) as $yid) {
            if ((int) $yid === (int) $p->id) continue;
            try {
                NotificationService::toStaff((int) $yid, $salonId)->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                    ->title('Mola talebi')->body($metin . '. Onayınızı bekliyor (Profil > Ekibim).')->cevaptanSonra()->send();
            } catch (\Throwable $e) {}
        }
        return (int) $id;
    }

    /** Yonetici karari; ilk karar veren gecerli. */
    public static function karar($salonId, $yonetici, $id, $onay, $req)
    {
        self::semaGaranti();
        $m = DB::table('personel_molalari')->where('id', $id)->where('salon_id', $salonId)->first();
        if (!$m || $m->durum !== 'bekliyor') return 'Bu talep zaten karara bağlanmış.';
        $p = \App\Personeller::where('id', $m->personel_id)->first(['id', 'personel_adi']);
        if (!$p) return 'Personel bulunamadı.';
        $simdi = date('Y-m-d H:i:s');
        if ($onay) {
            $bas = date('Y-m-d H:i:00');
            $bit = $m->dakika ? date('Y-m-d H:i:00', strtotime($bas) + $m->dakika * 60) : date('Y-m-d') . ' ' . $m->istenen_bitis . ':00';
            if (strtotime($bit) <= time()) return 'İstenen mola saati geçmiş; talep reddedilebilir.';
            if (substr($bit, 0, 10) !== substr($bas, 0, 10)) $bit = substr($bas, 0, 10) . ' 23:59:00';
        }
        // Yaris korumasi: yalniz hala bekliyorsa guncelle
        $guncel = DB::table('personel_molalari')->where('id', $id)->where('durum', 'bekliyor')->update([
            'durum' => $onay ? 'onaylandi' : 'reddedildi', 'karar_veren_personel_id' => $yonetici->id, 'karar_tarihi' => $simdi,
            'baslangic' => $onay ? $bas : null, 'bitis' => $onay ? $bit : null, 'updated_at' => $simdi,
        ]);
        if (!$guncel) return 'Bu talep az önce başka bir yönetici tarafından karara bağlandı.';
        if ($onay) {
            $rid = self::kapamaEkle($salonId, $p, substr($bas, 0, 10), substr($bas, 11), substr($bit, 11), 'Mola', $yonetici->id);
            if ($rid) DB::table('personel_molalari')->where('id', $id)->update(['kapama_randevu_id' => $rid]);
        }
        $metin = $onay ? 'Molanız onaylandı (' . substr($bas, 11, 5) . ' – ' . substr($bit, 11, 5) . ').' : 'Mola talebiniz reddedildi.';
        self::log($salonId, $req, $onay ? 'mola_onay' : 'mola_red', $p,
            $yonetici->personel_adi . ', ' . $p->personel_adi . ' için mola talebini ' . ($onay ? 'onayladı (' . substr($bas, 11, 5) . ' – ' . substr($bit, 11, 5) . ')' : 'reddetti'));
        try {
            NotificationService::toStaff((int) $p->id, $salonId)->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                ->title($onay ? 'Mola onaylandı' : 'Mola reddedildi')->body($metin)->cevaptanSonra()->send();
        } catch (\Throwable $e) {}
        return true;
    }

    /** Musait'e donus (onaysiz): aktif mola kisaltilir, bekleyen talep iptal edilir. Yonetici ise aktif izin de bugunden itibaren biter. */
    public static function musaitYap($salonId, $p, $yapan, $izinDeBitir, $req)
    {
        self::semaGaranti();
        $simdi = date('Y-m-d H:i:00');
        $degisti = false;
        DB::table('personel_molalari')->where('personel_id', $p->id)->where('durum', 'bekliyor')
            ->update(['durum' => 'iptal', 'updated_at' => $simdi]);
        foreach (DB::table('personel_molalari')->where('personel_id', $p->id)->where('durum', 'onaylandi')->where('bitis', '>', $simdi)->get() as $m) {
            self::kapamaKisalt($m->kapama_randevu_id, $simdi);
            DB::table('personel_molalari')->where('id', $m->id)->update(['durum' => 'bitti', 'bitis' => max($simdi, $m->baslangic), 'updated_at' => $simdi]);
            $degisti = true;
        }
        if ($izinDeBitir) {
            $bugun = date('Y-m-d');
            foreach (DB::table('personel_izinleri')->where('personel_id', $p->id)->where('durum', 'onaylandi')
                         ->where('baslangic', '<=', $bugun)->where('bitis', '>=', $bugun)->get() as $iz) {
                // Bugun ve sonrasindaki izin kapamalari silinir; gecmis gunler kayitta kalir
                $idler = json_decode((string) $iz->kapama_randevu_idleri, true) ?: [];
                $kalan = [];
                foreach ($idler as $rid) {
                    $t = DB::table('randevular')->where('id', $rid)->where('user_id', PersonelProfil::KAPAMA_USER)->value('tarih');
                    if ($t && $t >= $bugun) {
                        DB::table('randevu_hizmetler')->where('randevu_id', $rid)->where('hizmet_id', PersonelProfil::KAPAMA_HIZMET)->delete();
                        DB::table('randevular')->where('id', $rid)->delete();
                    } else {
                        $kalan[] = $rid;
                    }
                }
                $dun = date('Y-m-d', strtotime('-1 day'));
                DB::table('personel_izinleri')->where('id', $iz->id)->update([
                    'durum' => $iz->baslangic >= $bugun ? 'iptal' : 'onaylandi', 'bitis' => $iz->baslangic >= $bugun ? $iz->bitis : $dun,
                    'kapama_randevu_idleri' => $kalan ? json_encode($kalan) : null, 'updated_at' => $simdi,
                ]);
                $degisti = true;
            }
        }
        if ($degisti) {
            $kim = (int) $yapan->id === (int) $p->id ? $p->personel_adi . ' Müsait\'e döndü' : $yapan->personel_adi . ', ' . $p->personel_adi . ' durumunu Müsait yaptı';
            self::log($salonId, $req, 'durum_musait', $p, $kim);
        }
        return true;
    }

    /** Yoneticinin dogrudan durum vermesi (onaysiz). */
    public static function dogrudan($salonId, $yonetici, $p, $durum, array $v, $req)
    {
        self::semaGaranti();
        if ($durum === 'musait') return self::musaitYap($salonId, $p, $yonetici, true, $req);
        if ($durum === 'molada') {
            $det = self::durumDetay($p->id);
            if ($det['durum'] !== 'musait') return 'Önce Müsait yapın.';
            DB::table('personel_molalari')->where('personel_id', $p->id)->where('durum', 'bekliyor')->update(['durum' => 'iptal']);
            $dk = (int) ($v['dakika'] ?? 0);
            $saat = trim((string) ($v['bitis_saat'] ?? ''));
            $bas = date('Y-m-d H:i:00');
            if (in_array($dk, self::SURELER, true)) {
                $bit = date('Y-m-d H:i:00', strtotime($bas) + $dk * 60);
            } elseif (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $saat) && strtotime(date('Y-m-d') . ' ' . $saat) > time() + 60) {
                $bit = date('Y-m-d') . ' ' . $saat . ':00';
            } else {
                return 'Mola süresini seçin.';
            }
            if (substr($bit, 0, 10) !== substr($bas, 0, 10)) $bit = substr($bas, 0, 10) . ' 23:59:00';
            $simdi = date('Y-m-d H:i:s');
            $id = DB::table('personel_molalari')->insertGetId([
                'salon_id' => $salonId, 'personel_id' => $p->id, 'dakika' => $dk ?: null, 'istenen_bitis' => $dk ? null : $saat,
                'baslangic' => $bas, 'bitis' => $bit, 'durum' => 'onaylandi', 'talep_eden_personel_id' => $yonetici->id,
                'karar_veren_personel_id' => $yonetici->id, 'karar_tarihi' => $simdi, 'created_at' => $simdi, 'updated_at' => $simdi,
            ]);
            $rid = self::kapamaEkle($salonId, $p, substr($bas, 0, 10), substr($bas, 11), substr($bit, 11), 'Mola', $yonetici->id);
            if ($rid) DB::table('personel_molalari')->where('id', $id)->update(['kapama_randevu_id' => $rid]);
            self::log($salonId, $req, 'mola_dogrudan', $p, ((int) $yonetici->id === (int) $p->id ? $p->personel_adi . ' molaya çıktı' : $yonetici->personel_adi . ', ' . $p->personel_adi . ' için mola verdi')
                . ' (' . substr($bas, 11, 5) . ' – ' . substr($bit, 11, 5) . ')');
            if ((int) $yonetici->id !== (int) $p->id) {
                try {
                    NotificationService::toStaff((int) $p->id, $salonId)->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                        ->title('Mola')->body('Yöneticiniz size ' . substr($bit, 11, 5) . '\'e kadar mola verdi.')->cevaptanSonra()->send();
                } catch (\Throwable $e) {}
            }
            return true;
        }
        if ($durum === 'izinli') {
            $bitis = (string) ($v['bitis_tarih'] ?? '');
            $bugun = date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bitis) || $bitis < $bugun) return 'İzin bitiş tarihini seçin.';
            if ((strtotime($bitis) - strtotime($bugun)) / 86400 > 60) return 'En fazla 60 gün izin verilebilir.';
            if (DB::table('personel_izinleri')->where('personel_id', $p->id)->whereIn('durum', ['bekliyor', 'onaylandi'])
                ->where('baslangic', '<=', $bitis)->where('bitis', '>=', $bugun)->exists()) {
                return 'Bu tarihlerde zaten bir izin kaydı var (Ekibim > izinler).';
            }
            self::musaitYap($salonId, $p, $yonetici, false, $req); // aktif molayi kapat
            $simdi = date('Y-m-d H:i:s');
            $iid = DB::table('personel_izinleri')->insertGetId([
                'salon_id' => $salonId, 'personel_id' => $p->id, 'baslangic' => $bugun, 'bitis' => $bitis, 'tur' => 'yillik',
                'aciklama' => 'Yönetici tarafından verildi', 'durum' => 'bekliyor', 'created_at' => $simdi, 'updated_at' => $simdi,
            ]);
            // Onay + takvim kapama + bildirim + log mevcut izin akisi ile ayni
            return PersonelProfil::izinKarar($salonId, $yonetici, $iid, true, '', $req);
        }
        return 'Geçersiz durum.';
    }

    /** Onay bekleyen mola talepleri + o surede cakisan randevular. */
    public static function bekleyenler($salonId)
    {
        self::semaGaranti();
        return DB::table('personel_molalari as m')->join('salon_personelleri as sp', 'sp.id', '=', 'm.personel_id')
            ->where('m.salon_id', $salonId)->where('m.durum', 'bekliyor')->where('m.created_at', '>=', date('Y-m-d 00:00:00'))
            ->orderBy('m.id')->get(['m.id', 'm.personel_id', 'sp.personel_adi', 'm.dakika', 'm.istenen_bitis', 'm.created_at'])
            ->map(function ($m) use ($salonId) {
                $bas = date('H:i:00');
                $bit = $m->dakika ? date('H:i:00', time() + $m->dakika * 60) : $m->istenen_bitis . ':00';
                return [
                    'id' => (int) $m->id, 'personel_id' => (int) $m->personel_id, 'personel_adi' => $m->personel_adi,
                    'sure' => self::sureMetni($m), 'saat' => substr($m->created_at, 11, 5),
                    'cakisan' => self::cakisanlar($salonId, $m->personel_id, date('Y-m-d'), date('Y-m-d'), $bas, $bit),
                ];
            })->all();
    }

    /** Verilen aralikta personelin gercek (kapama olmayan, iptal olmayan) randevulari. */
    public static function cakisanlar($salonId, $pid, $tarihBas, $tarihBit, $saatBas = null, $saatBit = null)
    {
        try {
            $q = DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
                ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
                ->where('r.salon_id', $salonId)->where('rh.personel_id', $pid)
                ->where('r.user_id', '!=', PersonelProfil::KAPAMA_USER)->where('rh.hizmet_id', '!=', PersonelProfil::KAPAMA_HIZMET)
                ->where(function ($w) { $w->whereNull('r.durum')->orWhereNotIn('r.durum', [2, 3]); })
                ->whereBetween('r.tarih', [$tarihBas, $tarihBit]);
            if ($saatBas && $saatBit) {
                $q->where('rh.saat', '<', $saatBit)->where(function ($w) use ($saatBas) {
                    $w->where('rh.saat_bitis', '>', $saatBas)->orWhereNull('rh.saat_bitis');
                });
            }
            return $q->orderBy('r.tarih')->orderBy('rh.saat')->limit(20)
                ->get(['r.tarih', 'rh.saat', 'u.name'])
                ->map(function ($r) {
                    return ['tarih' => $r->tarih, 'saat' => substr((string) $r->saat, 0, 5), 'musteri' => (string) ($r->name ?: 'Müşteri')];
                })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Takvime saat kapama kaydi (saatkapamaekle ile ayni sema). */
    public static function kapamaEkle($salonId, $p, $tarih, $bas, $bit, $etiket, $olusturanId)
    {
        try {
            $r = new \App\Randevular();
            $r->user_id = PersonelProfil::KAPAMA_USER; $r->salon_id = $salonId; $r->durum = 1; $r->tarih = $tarih;
            $r->saat = $bas; $r->saat_bitis = $bit; $r->personel_notu = $etiket . ' (' . $p->personel_adi . ')';
            $r->olusturan_personel_id = $olusturanId;
            $r->save();
            $rh = new \App\RandevuHizmetler();
            $rh->randevu_id = $r->id; $rh->hizmet_id = PersonelProfil::KAPAMA_HIZMET; $rh->personel_id = $p->id;
            $rh->saat = $bas; $rh->saat_bitis = $bit;
            $rh->sure_dk = max(5, (int) round((strtotime($bit) - strtotime($bas)) / 60)); $rh->fiyat = 0;
            $rh->save();
            return (int) $r->id;
        } catch (\Throwable $e) {
            Log::warning('[PersonelMola] kapama', ['err' => $e->getMessage()]);
            return null;
        }
    }

    /** Mola kapamasini simdiye kisaltir; henuz baslamadiysa siler. Yalniz bizim kapama kaydimiza dokunur. */
    private static function kapamaKisalt($rid, $simdi)
    {
        if (!$rid) return;
        $r = DB::table('randevular')->where('id', $rid)->where('user_id', PersonelProfil::KAPAMA_USER)->first();
        if (!$r) return;
        $bas = $r->tarih . ' ' . $r->saat;
        if (strtotime($simdi) <= strtotime($bas) + 60) {
            DB::table('randevu_hizmetler')->where('randevu_id', $rid)->where('hizmet_id', PersonelProfil::KAPAMA_HIZMET)->delete();
            DB::table('randevular')->where('id', $rid)->delete();
            return;
        }
        $saat = substr($simdi, 11);
        DB::table('randevular')->where('id', $rid)->update(['saat_bitis' => $saat]);
        DB::table('randevu_hizmetler')->where('randevu_id', $rid)->where('hizmet_id', PersonelProfil::KAPAMA_HIZMET)
            ->update(['saat_bitis' => $saat, 'sure_dk' => max(1, (int) round((strtotime($simdi) - strtotime($bas)) / 60))]);
    }

    private static function log($salonId, $req, $islem, $p, $aciklama)
    {
        try {
            $web = false;
            try { $web = \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->check(); } catch (\Throwable $e) {}
            if (!$web && $req) {
                \App\SalonYonetim\Audit::logApi($salonId, $req, $islem, 'personel', $p->id, $p->personel_adi, $aciklama);
            } else {
                \App\SalonYonetim\Audit::log($salonId, $islem, 'personel', $p->id, $p->personel_adi, $aciklama);
            }
        } catch (\Throwable $e) {}
    }
}
