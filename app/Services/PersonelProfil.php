<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Profil sayfasi (2026-11-23): kisi karti + istatistik + siradaki musteri + bu ay kazanc + durum +
 * yorumlar + calisma saatleri + hizmetler + izin talebi + aylik hedef + calismalarim + cihazlarim.
 * Yonetici (rol < 5) salon geneli sayilari gorur; personel kendi sayilarini.
 * Kazanc/ciro mevcut hakedis hesabindan (ApiController::primHakedisTopluApi) YALNIZ OKUNUR; prim'e dokunmaz.
 */
class PersonelProfil
{
    const SEMA = 'personel_profil_sema_v1';
    const KAPAMA_USER = 2012;   // takvim saat kapama kaydi (saatkapamaekle ile ayni)
    const KAPAMA_HIZMET = 463;
    const DURUMLAR = ['musait' => 'Müsait', 'molada' => 'Molada', 'izinli' => 'İzinli'];

    public static function semaGaranti()
    {
        try {
            if (Cache::get(self::SEMA)) return;
            if (!Schema::hasTable('personel_durum')) {
                Schema::create('personel_durum', function ($t) {
                    $t->unsignedInteger('personel_id')->primary();
                    $t->string('durum', 20)->default('musait');
                    $t->timestamps();
                });
            }
            if (!Schema::hasTable('personel_izinleri')) {
                Schema::create('personel_izinleri', function ($t) {
                    $t->increments('id');
                    $t->unsignedInteger('salon_id')->index();
                    $t->unsignedInteger('personel_id')->index();
                    $t->date('baslangic');
                    $t->date('bitis');
                    $t->string('tur', 30)->default('yillik');
                    $t->string('aciklama', 300)->nullable();
                    $t->string('durum', 20)->default('bekliyor'); // bekliyor | onaylandi | reddedildi | iptal
                    $t->unsignedInteger('karar_veren_personel_id')->nullable();
                    $t->string('karar_notu', 300)->nullable();
                    $t->timestamp('karar_tarihi')->nullable();
                    $t->text('kapama_randevu_idleri')->nullable(); // onayda takvime eklenen saat kapama kayitlari
                    $t->timestamps();
                });
            }
            if (!Schema::hasTable('personel_hedefleri')) {
                Schema::create('personel_hedefleri', function ($t) {
                    $t->increments('id');
                    $t->unsignedInteger('salon_id')->index();
                    $t->unsignedInteger('personel_id');
                    $t->string('donem', 7); // YYYY-MM
                    $t->decimal('tutar', 12, 2)->default(0);
                    $t->timestamps();
                    $t->unique(['personel_id', 'donem']);
                });
            }
            if (!Schema::hasTable('personel_calismalari')) {
                Schema::create('personel_calismalari', function ($t) {
                    $t->increments('id');
                    $t->unsignedInteger('salon_id')->index();
                    $t->unsignedInteger('personel_id')->index();
                    $t->string('resim', 255);
                    $t->string('aciklama', 200)->nullable();
                    $t->timestamps();
                });
            }
            Cache::forever(self::SEMA, 1);
        } catch (\Throwable $e) {
            Log::warning('[PersonelProfil] sema hatasi', ['err' => $e->getMessage()]);
        }
    }

    /** Randevu sorgusu: saat kapamalar ve iptal/red (durum 2,3) haric. */
    private static function randevuSorgu($salonId)
    {
        return DB::table('randevular as r')
            ->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->where('r.salon_id', $salonId)
            ->where('r.user_id', '!=', self::KAPAMA_USER)
            ->where('rh.hizmet_id', '!=', self::KAPAMA_HIZMET)
            ->where(function ($q) { $q->whereNull('r.durum')->orWhereNotIn('r.durum', [2, 3]); });
    }

    public static function durum($personelId)
    {
        // Durum onayli mola / izin kayitlarindan hesaplanir (PersonelMola); personel_durum artik kullanilmaz
        return PersonelMola::durumDetay($personelId)['durum'];
    }

    /** Ana ozet (profil sayfasi tek cagri). */
    public static function ozet($salonId, $p, $yonetici, $kazanc = true)
    {
        self::semaGaranti();
        $pid = (int) $p->id;
        $bugun = date('Y-m-d');
        $simdi = date('H:i:s');
        $donem = date('Y-m');
        $det = PersonelMola::durumDetay($pid);
        $s = ['durum' => $det['durum'], 'durum_detay' => $det];

        // Istatistikler
        try {
            $q = self::randevuSorgu($salonId)->where('r.tarih', $bugun);
            if (!$yonetici) $q->where('rh.personel_id', $pid);
            $s['bugun_randevu'] = (int) $q->distinct()->count('r.id');
        } catch (\Throwable $e) { $s['bugun_randevu'] = 0; }
        try {
            if ($yonetici) {
                $s['toplam_musteri'] = (int) DB::table('musteri_portfoy')->where('salon_id', $salonId)->count();
            } else {
                $s['toplam_musteri'] = (int) self::randevuSorgu($salonId)->where('rh.personel_id', $pid)->distinct()->count('r.user_id');
            }
        } catch (\Throwable $e) { $s['toplam_musteri'] = 0; }
        try {
            $a = DB::table('anket_gonderimleri')->where('salon_id', $salonId)->where('cevaplandi', 1)->whereNotNull('csat_skoru');
            if (!$yonetici) $a->where('personel_id', $pid);
            $r = $a->selectRaw('AVG(csat_skoru) o, COUNT(*) c')->first();
            $s['ortalama_puan'] = $r && $r->c ? round((float) $r->o, 1) : null;
            $s['puan_sayisi'] = $r ? (int) $r->c : 0;
        } catch (\Throwable $e) { $s['ortalama_puan'] = null; $s['puan_sayisi'] = 0; }

        // Siradaki musteri (bugun, simdiden sonra; yoksa onumuzdeki 7 gun)
        $s['siradaki'] = null;
        try {
            $q = self::randevuSorgu($salonId)
                ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
                ->leftJoin('hizmetler as h', 'h.id', '=', 'rh.hizmet_id')
                ->leftJoin('salon_personelleri as sp', 'sp.id', '=', 'rh.personel_id')
                ->where(function ($w) use ($bugun, $simdi) {
                    $w->where(function ($x) use ($bugun, $simdi) { $x->where('r.tarih', $bugun)->where('rh.saat', '>=', $simdi); })
                      ->orWhere(function ($x) use ($bugun) { $x->where('r.tarih', '>', $bugun)->where('r.tarih', '<=', date('Y-m-d', strtotime('+7 day'))); });
                });
            if (!$yonetici) $q->where('rh.personel_id', $pid);
            $n = $q->orderBy('r.tarih')->orderBy('rh.saat')
                ->first(['r.id', 'r.tarih', 'rh.saat', 'u.name as musteri', 'h.hizmet_adi as hizmet', 'sp.personel_adi as personel']);
            if ($n) {
                $zaman = strtotime($n->tarih . ' ' . $n->saat);
                $s['siradaki'] = [
                    'randevu_id' => (int) $n->id,
                    'tarih' => $n->tarih,
                    'saat' => substr((string) $n->saat, 0, 5),
                    'musteri' => (string) ($n->musteri ?: 'Müşteri'),
                    'hizmet' => (string) ($n->hizmet ?: ''),
                    'personel' => $yonetici ? (string) ($n->personel ?: '') : '',
                    'dakika' => max(0, (int) round(($zaman - time()) / 60)),
                    'bugun' => $n->tarih === $bugun,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[PersonelProfil] siradaki', ['err' => $e->getMessage()]);
        }

        // Bu ay kazanc / ciro + hedef
        // Hakedis hesabi agir (tum personel); hizli cagri atlar, ekran ikinci cagriyla tamamlar
        $h = $kazanc ? self::hakedis($salonId) : [];
        $satir = $h[$pid] ?? null;
        $ciro = $satir ? $satir['ciro'] : 0.0;
        $s['kazanc_hazir'] = (bool) $kazanc;
        $s['bu_ay'] = [
            'kazanc' => $satir ? $satir['net'] : null,
            'ciro' => $yonetici ? round(array_sum(array_column($h, 'ciro')), 2) : $ciro,
            'donem' => $donem,
        ];
        $hedef = null;
        try { $hedef = DB::table('personel_hedefleri')->where('personel_id', $pid)->where('donem', $donem)->value('tutar'); } catch (\Throwable $e) {}
        $s['hedef'] = $hedef !== null && (float) $hedef > 0
            ? ['tutar' => (float) $hedef, 'gerceklesen' => $ciro, 'yuzde' => (int) min(999, round($ciro / (float) $hedef * 100))]
            : null;
        // Hizli cagrida kazanc/ciro hesaplanmadi: 0 gostermesin (ekran tam cagriyla doldurur)
        if (!$kazanc) { $s['bu_ay'] = null; $s['hedef'] = null; }

        // Sayaclar (satir rozetleri)
        try {
            $s['izin_bekleyen'] = (int) DB::table('personel_izinleri')->where('personel_id', $pid)->where('durum', 'bekliyor')->count();
            $s['izin_onay_bekleyen'] = $yonetici ? (int) DB::table('personel_izinleri')->where('salon_id', $salonId)->where('durum', 'bekliyor')->count() : 0;
            $s['calisma_sayisi'] = (int) DB::table('personel_calismalari')->where('personel_id', $pid)->count();
            $s['hizmet_sayisi'] = (int) DB::table('personel_sunulan_hizmetler')->where('personel_id', $pid)->count();
            $s['cihaz_sayisi'] = count(self::cihazlar($salonId, $pid));
            $s['okunmamis_bildirim'] = (int) DB::table('bildirimler')->where('personel_id', $pid)->where(function ($q) { $q->where('okundu', 0)->orWhereNull('okundu'); })->count();
        } catch (\Throwable $e) {}
        return $s;
    }

    /** Bu ayin hakedis satirlari (personel_id => net, ciro). 5 dk onbellek; hata olursa bos. */
    public static function hakedis($salonId)
    {
        return Cache::remember('profil_hakedis:' . $salonId . ':' . date('Y-m'), 5, function () use ($salonId) {
            try {
                $c = app(\App\Http\Controllers\ApiController::class);
                $cevap = $c->primHakedisTopluApi(new \Illuminate\Http\Request(['ay' => date('m'), 'yil' => date('Y')]), $salonId);
                $veri = json_decode($cevap->getContent(), true);
                $out = [];
                foreach (($veri['personeller'] ?? []) as $r) {
                    $net = (float) ($r['maas'] ?? 0) + (float) ($r['hizmet_hakedis'] ?? 0) + (float) ($r['urun_hakedis'] ?? 0)
                        + (float) ($r['paket_hakedis'] ?? 0) + (float) ($r['bonus'] ?? 0) - (float) ($r['kesinti'] ?? 0)
                        - (float) ($r['masraf'] ?? 0) + (float) ($r['telafi_devri'] ?? 0);
                    $out[(int) $r['personel_id']] = [
                        'net' => round($net, 2),
                        'ciro' => round((float) ($r['hizmet_toplam'] ?? 0) + (float) ($r['urun_toplam'] ?? 0) + (float) ($r['paket_toplam'] ?? 0), 2),
                    ];
                }
                return $out;
            } catch (\Throwable $e) {
                Log::warning('[PersonelProfil] hakedis', ['err' => $e->getMessage()]);
                return [];
            }
        });
    }

    public static function durumKaydet($salonId, $p, $durum, $req)
    {
        if (!isset(self::DURUMLAR[$durum])) return false;
        self::semaGaranti();
        $simdi = date('Y-m-d H:i:s');
        $eski = self::durum($p->id);
        if ($eski === $durum) return true;
        DB::table('personel_durum')->updateOrInsert(['personel_id' => (int) $p->id], ['durum' => $durum, 'updated_at' => $simdi, 'created_at' => $simdi]);
        self::log($salonId, $req, 'personel_durum', $p, $p->personel_adi . ' durumu: ' . self::DURUMLAR[$durum]);
        return true;
    }

    public static function yorumlar($salonId, $pid, $yonetici)
    {
        try {
            $q = DB::table('anket_gonderimleri as a')
                ->leftJoin('salon_personelleri as sp', 'sp.id', '=', 'a.personel_id')
                ->where('a.salon_id', $salonId)->where('a.cevaplandi', 1)->whereNotNull('a.csat_skoru');
            if (!$yonetici) $q->where('a.personel_id', $pid);
            return $q->orderByDesc('a.cevap_zamani')->limit(50)
                ->get(['a.id', 'a.ad_soyad', 'a.csat_skoru', 'a.genel_yorum', 'a.cevap_zamani', 'sp.personel_adi'])
                ->map(function ($r) use ($yonetici) {
                    $ad = trim((string) $r->ad_soyad);
                    // Musteri adi kisaltilir (KVKK): "Ayşe K."
                    $parca = preg_split('/\s+/u', $ad);
                    $kisa = $ad === '' ? 'Müşteri' : ($parca[0] . (count($parca) > 1 ? ' ' . mb_substr(end($parca), 0, 1) . '.' : ''));
                    return [
                        'id' => (int) $r->id, 'musteri' => $kisa, 'puan' => round((float) $r->csat_skoru, 1),
                        'yorum' => (string) ($r->genel_yorum ?? ''), 'tarih' => $r->cevap_zamani ? substr($r->cevap_zamani, 0, 10) : '',
                        'personel' => $yonetici ? (string) ($r->personel_adi ?? '') : '',
                    ];
                })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function calismaSaatleri($pid)
    {
        $gunler = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $satirlar = DB::table('personel_calisma_saatleri')->where('personel_id', $pid)->get()->keyBy('haftanin_gunu');
        $out = [];
        foreach ($gunler as $no => $ad) {
            $r = $satirlar->get($no);
            $out[] = [
                'gun' => $ad, 'no' => $no, 'bugun' => (int) date('N') === $no,
                'calisiyor' => $r ? (bool) $r->calisiyor : false,
                'baslangic' => $r && $r->baslangic_saati ? substr($r->baslangic_saati, 0, 5) : '',
                'bitis' => $r && $r->bitis_saati ? substr($r->bitis_saati, 0, 5) : '',
            ];
        }
        $izinler = DB::table('personel_izinleri')->where('personel_id', $pid)->where('durum', 'onaylandi')
            ->where('bitis', '>=', date('Y-m-d'))->orderBy('baslangic')->get(['baslangic', 'bitis', 'tur']);
        return ['gunler' => $out, 'yaklasan_izinler' => $izinler, 'kayitli' => $satirlar->count() > 0];
    }

    public static function hizmetler($salonId, $pid)
    {
        return DB::table('personel_sunulan_hizmetler as ph')
            ->join('hizmetler as h', 'h.id', '=', 'ph.hizmet_id')
            ->leftJoin('salon_sunulan_hizmetler as ss', function ($j) use ($salonId) {
                $j->on('ss.hizmet_id', '=', 'ph.hizmet_id')->where('ss.salon_id', '=', $salonId);
            })
            ->where('ph.personel_id', $pid)
            ->orderBy('h.hizmet_adi')
            ->get(['h.id', 'h.hizmet_adi', 'ss.baslangic_fiyat', 'ss.son_fiyat'])
            ->unique('id')->values()
            ->map(function ($r) {
                return ['id' => (int) $r->id, 'ad' => (string) $r->hizmet_adi,
                    'fiyat' => $r->son_fiyat !== null ? (float) $r->son_fiyat : ($r->baslangic_fiyat !== null ? (float) $r->baslangic_fiyat : null)];
            })->all();
    }

    // ─────────────────────────────────────────────── izin

    public static function izinler($salonId, $pid, $tumSalon = false)
    {
        $q = DB::table('personel_izinleri as i')->leftJoin('salon_personelleri as sp', 'sp.id', '=', 'i.personel_id')
            ->where('i.salon_id', $salonId);
        if (!$tumSalon) $q->where('i.personel_id', $pid);
        return $q->orderByRaw("FIELD(i.durum,'bekliyor') DESC")->orderByDesc('i.baslangic')->limit(100)
            ->get(['i.id', 'i.personel_id', 'sp.personel_adi', 'i.baslangic', 'i.bitis', 'i.tur', 'i.aciklama', 'i.durum', 'i.karar_notu', 'i.created_at'])
            ->map(function ($r) {
                $r->gun = (int) round((strtotime($r->bitis) - strtotime($r->baslangic)) / 86400) + 1;
                return $r;
            })->all();
    }

    public static function izinTalep($salonId, $p, array $v, $req)
    {
        self::semaGaranti();
        $bas = $v['baslangic'] ?? ''; $bit = $v['bitis'] ?? $bas;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bas) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bit)) return 'Tarih seçin.';
        if ($bit < $bas) return 'Bitiş tarihi başlangıçtan önce olamaz.';
        if ($bas < date('Y-m-d')) return 'Geçmiş bir tarih için izin istenemez.';
        if ((strtotime($bit) - strtotime($bas)) / 86400 > 60) return 'Tek seferde en fazla 60 gün izin istenebilir.';
        $cakisan = DB::table('personel_izinleri')->where('personel_id', $p->id)->whereIn('durum', ['bekliyor', 'onaylandi'])
            ->where('baslangic', '<=', $bit)->where('bitis', '>=', $bas)->exists();
        if ($cakisan) return 'Bu tarihlerde zaten bir izin talebiniz var.';
        $tur = in_array($v['tur'] ?? '', ['yillik', 'mazeret', 'rapor', 'ucretsiz'], true) ? $v['tur'] : 'yillik';
        $simdi = date('Y-m-d H:i:s');
        $id = DB::table('personel_izinleri')->insertGetId([
            'salon_id' => $salonId, 'personel_id' => $p->id, 'baslangic' => $bas, 'bitis' => $bit, 'tur' => $tur,
            'aciklama' => mb_substr(trim((string) ($v['aciklama'] ?? '')), 0, 300) ?: null,
            'durum' => 'bekliyor', 'created_at' => $simdi, 'updated_at' => $simdi,
        ]);
        $metin = $p->personel_adi . ' ' . self::tarihMetni($bas, $bit) . ' için izin istedi';
        self::log($salonId, $req, 'izin_talep', $p, $metin);
        // Yoneticilere bildirim
        foreach (BildirimTercih::yoneticiIdleri($salonId) as $yid) {
            if ((int) $yid === (int) $p->id) continue;
            try {
                NotificationService::toStaff((int) $yid, $salonId)->type(NotificationTypes::EKIP_TALEBI)->deepLink('ekibim', ['salon_id' => (int) $salonId])
                    ->title('İzin talebi')->body($metin . '. Onay bekliyor.')->cevaptanSonra()->send();
            } catch (\Throwable $e) {}
        }
        return (int) $id;
    }

    public static function izinIptal($salonId, $p, $id, $req)
    {
        $i = DB::table('personel_izinleri')->where('id', $id)->where('personel_id', $p->id)->first();
        if (!$i || !in_array($i->durum, ['bekliyor', 'onaylandi'], true)) return 'İzin bulunamadı.';
        if ($i->durum === 'onaylandi' && $i->bitis < date('Y-m-d')) return 'Geçmiş izin iptal edilemez.';
        self::kapamalariSil($i);
        DB::table('personel_izinleri')->where('id', $id)->update(['durum' => 'iptal', 'kapama_randevu_idleri' => null, 'updated_at' => date('Y-m-d H:i:s')]);
        self::log($salonId, $req, 'izin_iptal', $p, $p->personel_adi . ' ' . self::tarihMetni($i->baslangic, $i->bitis) . ' iznini iptal etti');
        return true;
    }

    /** Yonetici karari. Onayda calisma gunleri takvimde kapatilir (saat kapama kaydi). */
    public static function izinKarar($salonId, $yonetici, $id, $onay, $not, $req)
    {
        $i = DB::table('personel_izinleri')->where('id', $id)->where('salon_id', $salonId)->first();
        if (!$i || $i->durum !== 'bekliyor') return 'Talep bulunamadı ya da zaten karara bağlandı.';
        $personel = \App\Personeller::where('id', $i->personel_id)->first(['id', 'personel_adi', 'salon_id']);
        if (!$personel) return 'Personel bulunamadı.';
        $kapamalar = [];
        if ($onay) {
            $saatler = DB::table('personel_calisma_saatleri')->where('personel_id', $i->personel_id)->get()->keyBy('haftanin_gunu');
            for ($t = strtotime($i->baslangic); $t <= strtotime($i->bitis); $t += 86400) {
                $tarih = date('Y-m-d', $t);
                $cs = $saatler->get((int) date('N', $t));
                if ($cs && !$cs->calisiyor) continue; // zaten calismadigi gun
                $bas = $cs && $cs->baslangic_saati ? $cs->baslangic_saati : '09:00:00';
                $bit = $cs && $cs->bitis_saati ? $cs->bitis_saati : '18:00:00';
                try {
                    $r = new \App\Randevular();
                    $r->user_id = self::KAPAMA_USER; $r->salon_id = $salonId; $r->durum = 1; $r->tarih = $tarih;
                    $r->saat = $bas; $r->saat_bitis = $bit; $r->personel_notu = 'İzin (' . $personel->personel_adi . ')';
                    $r->olusturan_personel_id = $yonetici->id;
                    $r->save();
                    $rh = new \App\RandevuHizmetler();
                    $rh->randevu_id = $r->id; $rh->hizmet_id = self::KAPAMA_HIZMET; $rh->personel_id = $i->personel_id;
                    $rh->saat = $bas; $rh->saat_bitis = $bit;
                    $rh->sure_dk = max(15, (int) round((strtotime($bit) - strtotime($bas)) / 60)); $rh->fiyat = 0;
                    $rh->save();
                    $kapamalar[] = (int) $r->id;
                } catch (\Throwable $e) {
                    Log::warning('[PersonelProfil] izin kapama', ['izin' => $id, 'err' => $e->getMessage()]);
                }
            }
        }
        DB::table('personel_izinleri')->where('id', $id)->update([
            'durum' => $onay ? 'onaylandi' : 'reddedildi', 'karar_veren_personel_id' => $yonetici->id,
            'karar_notu' => mb_substr(trim((string) $not), 0, 300) ?: null, 'karar_tarihi' => date('Y-m-d H:i:s'),
            'kapama_randevu_idleri' => $kapamalar ? json_encode($kapamalar) : null, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $metin = self::tarihMetni($i->baslangic, $i->bitis) . ' izin talebiniz ' . ($onay ? 'onaylandı' : 'reddedildi') . ($not ? ': ' . $not : '.');
        self::log($salonId, $req, $onay ? 'izin_onay' : 'izin_red', $personel,
            $yonetici->personel_adi . ', ' . $personel->personel_adi . ' için ' . self::tarihMetni($i->baslangic, $i->bitis) . ' iznini ' . ($onay ? 'onayladı' : 'reddetti'));
        try {
            NotificationService::toStaff((int) $personel->id, $salonId)->type(NotificationTypes::IZIN_KARARI)->deepLink('izinlerim', ['salon_id' => (int) $salonId])
                ->title($onay ? 'İzniniz onaylandı' : 'İzniniz reddedildi')->body($metin)->cevaptanSonra()->send();
        } catch (\Throwable $e) {}
        return true;
    }

    private static function kapamalariSil($i)
    {
        $idler = json_decode((string) $i->kapama_randevu_idleri, true);
        if (!is_array($idler) || !$idler) return;
        // Yalniz bizim olusturdugumuz saat kapama kayitlari (guvenlik: user 2012 + hizmet 463)
        $gercek = DB::table('randevular as r')->join('randevu_hizmetler as rh', 'rh.randevu_id', '=', 'r.id')
            ->whereIn('r.id', $idler)->where('r.user_id', self::KAPAMA_USER)->where('rh.hizmet_id', self::KAPAMA_HIZMET)
            ->pluck('r.id')->all();
        if (!$gercek) return;
        DB::table('randevu_hizmetler')->whereIn('randevu_id', $gercek)->delete();
        DB::table('randevular')->whereIn('id', $gercek)->delete();
    }

    // ─────────────────────────────────────────────── hedef + ekip

    /** Yonetici ekranı: personeller + durum + bu ay ciro + hedef. */
    public static function ekip($salonId)
    {
        self::semaGaranti();
        $donem = date('Y-m');
        $h = self::hakedis($salonId);
        $hedefler = DB::table('personel_hedefleri')->where('salon_id', $salonId)->where('donem', $donem)->pluck('tutar', 'personel_id');
        return \App\Personeller::where('salon_id', $salonId)->where('aktif', true)
            ->where(function ($q) { $q->where('arsivli', false)->orWhereNull('arsivli'); })
            ->orderBy('takvim_sirasi')->orderBy('id')->get(['id', 'personel_adi', 'unvan', 'profil_resmi'])
            ->map(function ($p) use ($h, $hedefler) {
                $ciro = $h[$p->id]['ciro'] ?? 0.0;
                $hedef = isset($hedefler[$p->id]) ? (float) $hedefler[$p->id] : 0.0;
                return ['id' => (int) $p->id, 'ad' => (string) $p->personel_adi, 'unvan' => (string) ($p->unvan ?? ''),
                    'resim' => (string) ($p->profil_resmi ?? ''), 'durum' => ($dd = PersonelMola::durumDetay($p->id))['durum'], 'durum_metni' => $dd['metin'],
                    'ciro' => $ciro, 'hedef' => $hedef, 'yuzde' => $hedef > 0 ? (int) round($ciro / $hedef * 100) : null];
            })->all();
    }

    public static function hedefKaydet($salonId, $yonetici, $personelId, $tutar, $req)
    {
        self::semaGaranti();
        $p = \App\Personeller::where('id', $personelId)->where('salon_id', $salonId)->first(['id', 'personel_adi']);
        if (!$p) return 'Personel bulunamadı.';
        $tutar = max(0, min(100000000, round((float) $tutar, 2)));
        $donem = date('Y-m');
        $simdi = date('Y-m-d H:i:s');
        DB::table('personel_hedefleri')->updateOrInsert(['personel_id' => $p->id, 'donem' => $donem],
            ['salon_id' => $salonId, 'tutar' => $tutar, 'updated_at' => $simdi, 'created_at' => $simdi]);
        self::log($salonId, $req, 'personel_hedef', $p, $yonetici->personel_adi . ', ' . $p->personel_adi . ' için ' . $donem . ' hedefini '
            . number_format($tutar, 0, ',', '.') . ' ₺ yaptı');
        return true;
    }

    // ─────────────────────────────────────────────── calismalarim (portfolyo)

    public static function calismalar($pid)
    {
        return DB::table('personel_calismalari')->where('personel_id', $pid)->orderByDesc('id')->limit(200)
            ->get(['id', 'resim', 'aciklama', 'created_at'])->all();
    }

    public static function calismaEkle($salonId, $p, $dosya, $aciklama, $req)
    {
        self::semaGaranti();
        $uzanti = strtolower($dosya->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($uzanti, ['jpg', 'jpeg', 'png', 'webp', 'heic'], true)) return 'Yalnızca fotoğraf yüklenebilir.';
        if ($dosya->getSize() > 12 * 1024 * 1024) return 'Fotoğraf en fazla 12 MB olabilir.';
        $klasor = public_path('personel_calismalari');
        if (!is_dir($klasor)) @mkdir($klasor, 0755, true);
        $ad = $p->id . '_' . uniqid() . '.' . $uzanti;
        $dosya->move($klasor, $ad);
        $simdi = date('Y-m-d H:i:s');
        $id = DB::table('personel_calismalari')->insertGetId(['salon_id' => $salonId, 'personel_id' => $p->id,
            'resim' => '/public/personel_calismalari/' . $ad, 'aciklama' => mb_substr(trim((string) $aciklama), 0, 200) ?: null,
            'created_at' => $simdi, 'updated_at' => $simdi]);
        self::log($salonId, $req, 'calisma_ekle', $p, $p->personel_adi . ' Çalışmalarım\'a fotoğraf ekledi');
        return (int) $id;
    }

    public static function calismaSil($salonId, $p, $id, $req)
    {
        $r = DB::table('personel_calismalari')->where('id', $id)->where('personel_id', $p->id)->first();
        if (!$r) return 'Fotoğraf bulunamadı.';
        DB::table('personel_calismalari')->where('id', $id)->delete();
        $yol = public_path(preg_replace('#^/public/#', '', $r->resim));
        if (strpos(realpath(dirname($yol)) ?: '', 'personel_calismalari') !== false) @unlink($yol);
        self::log($salonId, $req, 'calisma_sil', $p, $p->personel_adi . ' Çalışmalarım\'dan fotoğraf sildi');
        return true;
    }

    // ─────────────────────────────────────────────── cihazlarim

    public static function cihazlar($salonId, $pid)
    {
        $q = DB::table('bildirim_kimlikleri')->where('isletme_yetkili_id', $pid)->where('aktif', 1)
            ->whereNotNull('bildirim_id')->where('bildirim_id', '!=', '');
        try {
            $bundle = \App\Salonlar::where('id', $salonId)->value('app_bundle');
            if ($bundle) $q->where('app_bundle', $bundle);
        } catch (\Throwable $e) {}
        $cols = ['id', 'bildirim_id', 'created_at'];
        foreach (['cihaz', 'platform', 'son_kullanim_tarihi', 'updated_at'] as $k) {
            if (Schema::hasColumn('bildirim_kimlikleri', $k)) $cols[] = $k;
        }
        return $q->orderByDesc('id')->get($cols)->all();
    }

    /** Cihazdan cikis: o cihaza force_logout push'u gonderilir ve cihaz kaydi silinir. */
    public static function cihazKapat($salonId, $p, $id, $req)
    {
        $c = DB::table('bildirim_kimlikleri')->where('id', $id)->where('isletme_yetkili_id', $p->id)->first();
        if (!$c) return 'Cihaz bulunamadı.';
        try {
            NotificationService::forTokens([$c->bildirim_id], $salonId)->type(NotificationTypes::SIFRE_DEGISTI)
                ->title('Oturum kapatıldı')->body('Bu cihazdaki oturumunuz başka bir cihazdan kapatıldı.')
                ->extra(['force_logout' => '1'])->send();
        } catch (\Throwable $e) {}
        DB::table('bildirim_kimlikleri')->where('id', $id)->delete();
        self::log($salonId, $req, 'cihaz_cikis', $p, $p->personel_adi . ' bir cihazdaki oturumu kapattı (' . ($c->cihaz ?? $c->platform ?? 'cihaz') . ')');
        return true;
    }

    // ─────────────────────────────────────────────── yetkilerim

    /** Kisinin yetkileri, kategori gruplu ve okunur etiketli (PersonelYetkiServisi ile ayni karar). */
    public static function yetkiler($salonId, $yetkiliId)
    {
        $etiketler = \App\PersonelYetkiSabitleri::kategoriEtiketleri();
        $gruplar = [];
        foreach (\App\PersonelYetkiSabitleri::tanimlar() as $t) {
            $k = $t["kategori"] ?? "diger";
            if (!isset($gruplar[$k])) $gruplar[$k] = ["kod" => $k, "ad" => $etiketler[$k]["ad"] ?? ucfirst($k), "ikon" => $etiketler[$k]["ikon"] ?? "", "yetkiler" => []];
            $acik = false;
            try { $acik = PersonelYetkiServisi::yetkiliYetkiVar((int) $yetkiliId, $salonId, $t["key"]); } catch (\Throwable $e) {}
            $gruplar[$k]["yetkiler"][] = ["ad" => (string) $t["label"], "aciklama" => (string) ($t["aciklama"] ?? ""), "acik" => $acik];
        }
        return array_values($gruplar);
    }

    // ─────────────────────────────────────────────── yardimci

    public static function tarihMetni($bas, $bit)
    {
        $f = function ($d) { return date('d.m.Y', strtotime($d)); };
        return $bas === $bit ? $f($bas) : $f($bas) . ' – ' . $f($bit);
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
