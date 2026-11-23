<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistani — ortak veri katmani (WaAsistan'in eklentileri).
 *
 * Tablolar (runtime sema; sunucuda migrate gerekmez, SantralPaket deseni):
 *   wa_asistan_cevaplar   : asistanin gonderdigi HER cevap (kaynak, ai=paketten duser mi, cevap suresi, mesai disi,
 *                           aksiyon, randevu). "Kazandirdiklari" raporu + paket sayaci buradan.
 *   wa_asistan_ayarlar    : isletme ayarlari (fiyat soyle, geri kazanma, memnuniyet sorusu, gorsel fiyat).
 *   wa_asistan_ogrenilen  : AI'nin baglamsiz bilgi sorusuna verdigi cevap -> ayni soru sonra BEDAVA (isletme bazli).
 *   wa_asistan_oturumlari : ek kolonlar (fiyat/bilgi/saat isaretleri, son hizmet, memnuniyet, geri kazanma baglantisi).
 *   wa_asistan_gelen      : ek kolon gorsel_id (fotograf mesaji).
 */
class WaAsistanVeri
{
    const SEMA = 'wa_asistan_veri_sema_v1';

    // Cevap kaynaklari. AI = Claude cagrildi (paketten duser); digerleri bedava.
    const KAYNAK_AI = 'ai';

    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        if (!WaAsistan::semaGaranti()) return false;
        try {
            if (!Schema::hasTable('wa_asistan_cevaplar')) {
                Schema::create('wa_asistan_cevaplar', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');                  // WhatsApp hattinin salonu (paket bu salona ait)
                    $t->unsignedBigInteger('isletme_id');                // hizmet verilen salon (rapor)
                    $t->unsignedBigInteger('oturum_id')->nullable();
                    $t->string('kaynak', 20);                            // ai | kod | fiyat | kalip | sss | ogrenilen | sohbet | karsilama | gorsel | gorsel_onbellek | geri_kazanim | memnuniyet
                    $t->tinyInteger('ai')->default(0);                   // 1 = Claude cagrildi -> paketten duser
                    $t->unsignedInteger('sure_sn')->nullable();          // musterinin mesajindan cevaba kadar
                    $t->tinyInteger('mesai_disi')->default(0);
                    $t->string('aksiyon', 20)->nullable();
                    $t->unsignedBigInteger('randevu_id')->nullable();
                    $t->decimal('maliyet_usd', 12, 6)->default(0);
                    $t->timestamp('created_at')->nullable();
                    $t->index(['salon_id', 'created_at'], 'wa_cvp_salon_tarih_idx');
                    $t->index(['isletme_id', 'created_at'], 'wa_cvp_isletme_tarih_idx');
                });
            }
            if (!Schema::hasTable('wa_asistan_ayarlar')) {
                Schema::create('wa_asistan_ayarlar', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id')->unique();
                    $t->tinyInteger('fiyat_soyle')->default(1);
                    $t->tinyInteger('geri_kazanim')->default(1);
                    $t->unsignedInteger('gk_bekle_saat')->default(24);
                    $t->text('gk_kampanya')->nullable();               // isletmenin kendi teklif cumlesi (bos = teklif yok)
                    $t->tinyInteger('memnuniyet_sor')->default(1);
                    $t->tinyInteger('gorsel_fiyat')->default(1);
                    $t->string('gorsel_sektor', 30)->default('tirnak');
                    $t->text('gorsel_not')->nullable();                // fiyat cevabinin altina eklenen not
                    $t->string('guncelleyen', 120)->nullable();
                    $t->timestamps();
                });
            }
            if (!Schema::hasTable('wa_asistan_ogrenilen')) {
                Schema::create('wa_asistan_ogrenilen', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('anahtar', 32);
                    $t->text('soru');
                    $t->text('cevap');
                    $t->unsignedInteger('adet')->default(0);             // kac kez bedava kullanildi
                    $t->tinyInteger('aktif')->default(1);
                    $t->timestamps();
                    $t->unique(['salon_id', 'anahtar'], 'wa_ogr_salon_anahtar_uq');
                });
            }
            $ek = [
                'fiyat_verildi' => function ($t) { $t->tinyInteger('fiyat_verildi')->default(0); },
                'bilgi_verildi' => function ($t) { $t->tinyInteger('bilgi_verildi')->default(0); },
                'saat_onerildi' => function ($t) { $t->tinyInteger('saat_onerildi')->default(0); },
                'gorsel_sayisi' => function ($t) { $t->unsignedInteger('gorsel_sayisi')->default(0); },
                'son_hizmet_id' => function ($t) { $t->unsignedBigInteger('son_hizmet_id')->nullable(); },
                'son_hizmet_ad' => function ($t) { $t->string('son_hizmet_ad', 150)->nullable(); },
                'memnuniyet' => function ($t) { $t->tinyInteger('memnuniyet')->nullable(); },
                'memnuniyet_soruldu' => function ($t) { $t->tinyInteger('memnuniyet_soruldu')->default(0); },
                'geri_kazanim_id' => function ($t) { $t->unsignedBigInteger('geri_kazanim_id')->nullable(); },
                'ret' => function ($t) { $t->tinyInteger('ret')->default(0); },  // musteri "istemiyorum/vazgectim" dedi
            ];
            foreach ($ek as $kolon => $f) {
                if (!Schema::hasColumn('wa_asistan_oturumlari', $kolon)) Schema::table('wa_asistan_oturumlari', $f);
            }
            if (!Schema::hasColumn('wa_asistan_gelen', 'gorsel_id')) {
                Schema::table('wa_asistan_gelen', function ($t) { $t->unsignedBigInteger('gorsel_id')->nullable(); });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-ASISTAN] veri semasi olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ ayarlar
    const AYAR_VARSAYILAN = [
        'fiyat_soyle' => 1, 'geri_kazanim' => 1, 'gk_bekle_saat' => 24, 'gk_kampanya' => null,
        'memnuniyet_sor' => 1, 'gorsel_fiyat' => 1, 'gorsel_sektor' => 'tirnak', 'gorsel_not' => null,
        'denetci' => 1, 'denetci_oto' => 1,   // Gece Denetcisi (kolonlar WaDenetci::semaGaranti)
        'reklam' => 0,                        // salon reklami (WaReklam; kolon WaReklam::semaGaranti; yalniz sistem yoneticisi acar)
    ];

    public static function ayarlar(int $salonId): array
    {
        $v = self::AYAR_VARSAYILAN;
        if (!self::semaGaranti()) return $v;
        try {
            $r = Cache::remember("wa_asistan_ayar_{$salonId}", 2, function () use ($salonId) {
                return DB::table('wa_asistan_ayarlar')->where('salon_id', $salonId)->first() ?: '-';
            });
        } catch (\Throwable $e) {
            return $v;
        }
        if (!is_object($r)) return $v;
        foreach ($v as $k => $_) if (property_exists($r, $k)) $v[$k] = $r->$k;
        foreach (['fiyat_soyle', 'geri_kazanim', 'gk_bekle_saat', 'memnuniyet_sor', 'gorsel_fiyat', 'denetci', 'denetci_oto', 'reklam'] as $k) $v[$k] = (int) $v[$k];
        return $v;
    }

    // ------------------------------------------------------------------ santral (telefon) fiyat ayari
    // WhatsApp'in fiyat_soyle ayarindan AYRI, varsayilan KAPALI: salon istemedikce fiyatlar telefonda soylenmez
    // (Santral > Ayarlar'dan acilir). Ola Nail (236) isletme istegiyle acik baslar (2026-11-23).
    const SANTRAL_FIYAT_SEMA = 'wa_ayar_santral_fiyat_v1';

    protected static function santralFiyatSema(): bool
    {
        if (Cache::get(self::SANTRAL_FIYAT_SEMA)) return true;
        if (!self::semaGaranti()) return false;
        try {
            if (!Schema::hasColumn('wa_asistan_ayarlar', 'santral_fiyat_soyle')) {
                Schema::table('wa_asistan_ayarlar', function ($t) { $t->tinyInteger('santral_fiyat_soyle')->default(0); });
                foreach ([236] as $sid) { // ilk kurulum: fiyatlari telefonda da soyleyecek salonlar
                    if (DB::table('wa_asistan_ayarlar')->where('salon_id', $sid)->exists()) {
                        DB::table('wa_asistan_ayarlar')->where('salon_id', $sid)->update(['santral_fiyat_soyle' => 1, 'fiyat_soyle' => 1]);
                    } else {
                        DB::table('wa_asistan_ayarlar')->insert(['salon_id' => $sid, 'santral_fiyat_soyle' => 1, 'fiyat_soyle' => 1, 'created_at' => now(), 'updated_at' => now()]);
                    }
                    Cache::forget("wa_asistan_ayar_{$sid}");
                }
            }
            Cache::forever(self::SANTRAL_FIYAT_SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] santral fiyat kolonu eklenemedi: ' . $e->getMessage());
            return false;
        }
    }

    /** Telefon asistani fiyat soylesin mi (varsayilan hayir). */
    public static function santralFiyatSoyle(int $salonId): bool
    {
        if ($salonId <= 0 || !self::santralFiyatSema()) return false;
        try {
            return (int) DB::table('wa_asistan_ayarlar')->where('salon_id', $salonId)->value('santral_fiyat_soyle') === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function santralFiyatKaydet(int $salonId, bool $acik): void
    {
        if ($salonId <= 0 || !self::santralFiyatSema()) return;
        if (DB::table('wa_asistan_ayarlar')->where('salon_id', $salonId)->exists()) {
            DB::table('wa_asistan_ayarlar')->where('salon_id', $salonId)->update(['santral_fiyat_soyle' => $acik ? 1 : 0, 'updated_at' => now()]);
        } else {
            DB::table('wa_asistan_ayarlar')->insert(['salon_id' => $salonId, 'santral_fiyat_soyle' => $acik ? 1 : 0, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public static function ayarKaydet(int $salonId, array $veri, ?string $guncelleyen = null): void
    {
        self::semaGaranti();
        $temiz = [];
        if (array_key_exists('denetci', $veri) || array_key_exists('denetci_oto', $veri)) WaDenetci::semaGaranti();
        if (array_key_exists('reklam', $veri)) WaReklam::semaGaranti();
        foreach (['fiyat_soyle', 'geri_kazanim', 'memnuniyet_sor', 'gorsel_fiyat', 'denetci', 'denetci_oto', 'reklam'] as $k) {
            if (array_key_exists($k, $veri)) $temiz[$k] = $veri[$k] ? 1 : 0;
        }
        if (array_key_exists('gk_bekle_saat', $veri)) $temiz['gk_bekle_saat'] = max(2, min(168, (int) $veri['gk_bekle_saat']));
        foreach (['gk_kampanya', 'gorsel_not'] as $k) {
            if (array_key_exists($k, $veri)) $temiz[$k] = mb_substr(trim((string) $veri[$k]), 0, 500) ?: null;
        }
        if (array_key_exists('gorsel_sektor', $veri) && isset(WaGorselDanisman::sektorler()[$veri['gorsel_sektor']])) {
            $temiz['gorsel_sektor'] = $veri['gorsel_sektor'];
        }
        $temiz['guncelleyen'] = $guncelleyen ? mb_substr($guncelleyen, 0, 120) : null;
        $temiz['updated_at'] = now();
        if (DB::table('wa_asistan_ayarlar')->where('salon_id', $salonId)->exists()) {
            DB::table('wa_asistan_ayarlar')->where('salon_id', $salonId)->update($temiz);
        } else {
            DB::table('wa_asistan_ayarlar')->insert($temiz + ['salon_id' => $salonId, 'created_at' => now()]);
        }
        Cache::forget("wa_asistan_ayar_{$salonId}");
    }

    // ------------------------------------------------------------------ cevap kaydi
    /**
     * Gonderilen her asistan cevabi. $ai=true -> paketten duser. Hata yutar (asistan akisi bozulmasin).
     * @param array $k ['salon_id','isletme_id','oturum_id','kaynak','ai','sure_sn','aksiyon','randevu_id','maliyet_usd']
     */
    public static function cevapKaydet(array $k): void
    {
        try {
            if (!self::semaGaranti()) return;
            $isletme = (int) ($k['isletme_id'] ?? $k['salon_id']);
            DB::table('wa_asistan_cevaplar')->insert([
                'salon_id' => (int) $k['salon_id'],
                'isletme_id' => $isletme,
                'oturum_id' => $k['oturum_id'] ?? null,
                'kaynak' => mb_substr((string) ($k['kaynak'] ?? 'kod'), 0, 20),
                'ai' => !empty($k['ai']) ? 1 : 0,
                'sure_sn' => isset($k['sure_sn']) ? max(0, (int) $k['sure_sn']) : null,
                'mesai_disi' => self::mesaiDisiMi($isletme, time()) ? 1 : 0,
                'aksiyon' => $k['aksiyon'] ?? null,
                'randevu_id' => $k['randevu_id'] ?? null,
                'maliyet_usd' => (float) ($k['maliyet_usd'] ?? 0),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] cevap kaydi yazilamadi: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ mesai
    /** Salonun calisma saatleri [gun(1=Pzt..7=Paz) => ['acik'=>bool,'bas'=>'HH:MM','bit'=>'HH:MM']] (10 dk onbellek). */
    public static function calismaSaatleri(int $salonId): array
    {
        try {
            return Cache::remember("wa_asistan_mesai_{$salonId}", 10, function () use ($salonId) {
                $out = [];
                foreach (DB::table('salon_calisma_saatleri')->where('salon_id', $salonId)->get() as $r) {
                    $out[(int) $r->haftanin_gunu] = [
                        'acik' => (int) $r->calisiyor === 1,
                        'bas' => substr((string) $r->baslangic_saati, 0, 5),
                        'bit' => substr((string) $r->bitis_saati, 0, 5),
                    ];
                }
                return $out;
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function mesaiDisiMi(int $salonId, int $ts): bool
    {
        return self::mesaiDisiHesapla(self::calismaSaatleri($salonId), $ts);
    }

    /**
     * Saf: verilen an mesai disi mi? Saat tanimi yoksa 09:00-19:00, Pazar kapali varsayilir.
     * Gun kaydi yoksa o gun kapali sayilmaz (eksik tanim isletmeyi "hep mesai disi" gostermesin) -> varsayilan saat.
     */
    public static function mesaiDisiHesapla(array $saatler, int $ts): bool
    {
        $gun = (int) date('N', $ts);
        $hm = date('H:i', $ts);
        if (!$saatler) return $gun === 7 || $hm < '09:00' || $hm >= '19:00';
        $g = $saatler[$gun] ?? null;
        if (!$g) return $hm < '09:00' || $hm >= '19:00';
        if (!$g['acik']) return true;
        if (!$g['bas'] || !$g['bit'] || $g['bas'] === $g['bit']) return false;
        return $hm < $g['bas'] || $hm >= $g['bit'];
    }

    // ------------------------------------------------------------------ bildirim
    /** Panel bildirimi + yoneticilere push (WaAsistan::isletmeyeBildir ile ayni kanal). Hata yutar. */
    public static function bildir(int $salonId, string $mesaj, ?string $url = null): void
    {
        try {
            $b = new \App\Bildirimler();
            $b->aciklama = $mesaj;
            $b->salon_id = $salonId;
            $b->url = $url ?: '/isletmeyonetim/whatsapp-asistan?sube=' . $salonId;
            $b->tarih_saat = date('Y-m-d H:i:s');
            $b->okundu = false;
            $b->save();
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] panel bildirimi yazilamadi: ' . $e->getMessage());
        }
        // Bildirim tercihleri: wa_asistan_talep / personel / push kapaliysa push atilmaz
        if (!BildirimTercih::izin($salonId, 'wa_asistan_talep', 'yonetici', 'push')) return;
        try {
            $yoneticiler = \App\Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
                ->where('salon_personelleri.salon_id', $salonId)->where('model_has_roles.role_id', '<', 5)
                ->pluck('salon_personelleri.id')->toArray();
            foreach ($yoneticiler as $pid) {
                NotificationService::toStaff((int) $pid, $salonId)->type(NotificationTypes::SYSTEM_ANNOUNCEMENT)
                    ->title('WhatsApp Asistanı')->body($mesaj)->send();
            }
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] push gonderilemedi: ' . $e->getMessage());
        }
    }
}
