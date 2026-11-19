<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistaninin isletmeye sordugu konular ("talep"): musteri ASLA havada kalmaz.
 *  - Asistan bilmedigi tek konuyu (fotograf fiyati, sikayet, yetkili istegi...) isletmeye sorar ama SUSMAZ, sohbete devam eder.
 *  - Talep: kime bildirildi, ne zaman; personel telefondan yazinca "cevaplandi"; panelden "Cevapladim".
 *  - takip() (5 dk'da bir): 15 dk -> yoneticilere hatirlatma; 45 dk -> asistan musteriye nazikce doner; 24 saat -> cevapsiz.
 */
class WaTalep
{
    const SEMA = 'wa_talep_sema_v1';
    const KONU = ['fiyat' => 'Fiyat sorusu', 'fotograf' => 'Fotoğraftaki modelin fiyatı', 'fotograf_ek' => 'Fotoğraftaki ek uygulamanın ücreti',
        'yetkili' => 'Yetkiliyle görüşmek istiyor', 'sikayet' => 'Şikâyet', 'diger' => 'Asistanın cevaplayamadığı konu',
        'yanlis_numara' => 'Yanlış numara / mesaj istemiyor (müşteri kartını kontrol edin)'];
    const HATIRLAT_DK = 15;
    const ASISTAN_DON_DK = 45;
    const CEVAPSIZ_SAAT = 24;

    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        if (!WaAsistan::semaGaranti()) return false;
        try {
            if (!Schema::hasTable('wa_asistan_talepler')) {
                Schema::create('wa_asistan_talepler', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->unsignedBigInteger('isletme_id');
                    $t->unsignedBigInteger('oturum_id');
                    $t->string('konu', 20);
                    $t->text('ozet')->nullable();                     // musterinin mesaji / asistanin notu
                    $t->text('bildirilen')->nullable();               // json ["Salih K.", ...]
                    $t->string('durum', 15)->default('bekliyor');     // bekliyor | cevaplandi | cevapsiz
                    $t->timestamp('hatirlatma_at')->nullable();
                    $t->timestamp('asistan_dondu_at')->nullable();
                    $t->timestamp('cevaplandi_at')->nullable();
                    $t->string('cevaplayan', 120)->nullable();
                    $t->timestamps();
                    $t->index(['isletme_id', 'durum'], 'wa_talep_isletme_durum_idx');
                    $t->index('oturum_id', 'wa_talep_oturum_idx');
                });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-TALEP] sema olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ saf
    /** Asistanin "aktar" sebebi + musteri mesajindan konu. */
    public static function konuBul(string $metin, string $sebep = ''): string
    {
        $s = mb_strtolower($sebep . ' ' . $metin);
        if (preg_match('/şikay|sikay|memnun değil|rezalet|berbat|kötü|mağdur|iade|para(mı|yı) geri/u', $s)) return 'sikayet';
        if (preg_match('/yetkili|müdür|sahibi|biriyle|bir insan|gerçek kişi|personelle|canlı|operatör/u', $s)) return 'yetkili';
        if (preg_match('/fiyat|ücret|ne kadar|kaç para|kaç tl/u', $s)) return 'fiyat';
        return 'diger';
    }

    /** Asistanin talebi isletmeye ilettiginde musteriye yazdigi (susmaz, devam eder). */
    public static function iletildiMetni(string $konu, bool $tekrar): string
    {
        if ($tekrar) return 'Talebinizi işletmemize iletmiştim; en kısa sürede buradan dönüş yapacaklar. Bu arada yardımcı olabileceğim başka bir konu varsa buradayım 😊';
        $bas = in_array($konu, ['fiyat', 'fotograf', 'fotograf_ek'], true) ? 'Fiyat bilgisi için talebinizi' : ($konu === 'sikayet' ? 'Yaşadığınız durumu' : 'Talebinizi');
        return $bas . ' işletmemize ilettim; en kısa sürede buradan size dönüş yapacaklar. 🙏 Bu arada randevu ya da başka bir konuda yardımcı olabilirim.';
    }

    /** 45 dk dolup isletme donmediyse asistanin musteriye yazdigi. */
    public static function takipMetni(string $konu, bool $randevuVar): string
    {
        if (in_array($konu, ['fiyat', 'fotograf', 'fotograf_ek'], true)) {
            return 'Kusura bakmayın, fiyat bilgisi için işletmemizden henüz dönüş alamadım; netleşir netleşmez buradan yazacağız. 🙏'
                . ($randevuVar ? ' Randevunuz kayıtlı, sizi bekliyoruz 😊' : "\n\nDilerseniz bu arada randevunuzu oluşturabilirim; hangi gün ve saat size uygun?");
        }
        return 'Kusura bakmayın, yetkili arkadaşımız henüz dönüş yapamadı; talebiniz öncelikli olarak iletildi, en kısa sürede buradan yazacaklar. 🙏'
            . ($randevuVar ? '' : ' Bu arada randevu ya da başka bir konuda yardımcı olabilirim.');
    }

    /** "7 dk", "2 sa", "1 gün" */
    public static function sureMetni(int $sn): string
    {
        $dk = max(0, (int) floor($sn / 60));
        if ($dk < 60) return $dk . ' dk';
        if ($dk < 1440) return floor($dk / 60) . ' sa';
        return floor($dk / 1440) . ' gün';
    }

    // ------------------------------------------------------------------ islemler
    /** Talep ac (ayni sohbette bekleyen talep varsa yenisi acilmaz, bildirim tekrarlanmaz). @return ['id','yeni'] */
    public static function ac($o, string $konu, string $ozet): array
    {
        if (!self::semaGaranti()) return ['id' => 0, 'yeni' => false];
        $konu = isset(self::KONU[$konu]) ? $konu : 'diger';
        $var = DB::table('wa_asistan_talepler')->where('oturum_id', $o->id)->where('durum', 'bekliyor')->orderByDesc('id')->first();
        if ($var) {
            DB::table('wa_asistan_talepler')->where('id', $var->id)->update(['ozet' => mb_substr(trim($var->ozet . "\n" . $ozet), -1000), 'updated_at' => now()]);
            return ['id' => (int) $var->id, 'yeni' => false];
        }
        $id = DB::table('wa_asistan_talepler')->insertGetId(['salon_id' => (int) $o->salon_id, 'isletme_id' => (int) $o->isletme_id, 'oturum_id' => (int) $o->id,
            'konu' => $konu, 'ozet' => mb_substr($ozet, 0, 1000), 'durum' => 'bekliyor', 'created_at' => now(), 'updated_at' => now()]);
        $kim = WaAsistan::bildir((int) $o->isletme_id, self::bildirimMetni($o, $konu, $ozet), self::url((int) $o->isletme_id, (int) $o->id));
        DB::table('wa_asistan_talepler')->where('id', $id)->update(['bildirilen' => json_encode($kim, JSON_UNESCAPED_UNICODE)]);
        return ['id' => $id, 'yeni' => true];
    }

    public static function url(int $isletmeId, int $oturumId): string
    {
        return '/isletmeyonetim/whatsapp-asistan?sube=' . $isletmeId . '&sohbet=' . $oturumId;
    }

    protected static function bildirimMetni($o, string $konu, string $ozet, bool $hatirlatma = false): string
    {
        if ($konu === 'yanlis_numara') {
            return ($hatirlatma ? 'HATIRLATMA: ' : '') . 'Bir kişi WhatsApp\'tan "yanlış numara / mesaj atmayın" yazdı (' . ($o->musteri_tel ?: $o->telefon ?: 'gizli numara') . '). '
                . mb_substr($ozet, 0, 220) . ' Asistan özür diledi ve bu numaraya artık yazmıyor.';
        }
        // Ad yalniz noktalama ise ('.', '..', '-') ad yok sayilir (bildirimde "asistanı .. için" cikiyordu)
        $kisi = trim(($o->ad && preg_match('/[\p{L}\p{N}]/u', (string) $o->ad) ? $o->ad : 'Bir müşteri') . (($o->musteri_tel ?: $o->telefon) ? ' (' . ($o->musteri_tel ?: $o->telefon) . ')' : ''));
        return ($hatirlatma ? 'HATIRLATMA: ' : '') . "WhatsApp asistanı {$kisi} için sizden dönüş bekliyor. Konu: " . self::KONU[$konu] . '. Müşteri: "'
            . mb_substr($ozet, 0, 160) . '". Asistan müşteriyle ilgilenmeye devam ediyor; cevabınızı WhatsApp\'tan yazın, sohbet panelde.';
    }

    /** Personel telefondan yazdi / panelden "Cevapladim" -> bekleyen talepler kapanir. */
    public static function cevaplandi(int $oturumId, string $kim): int
    {
        if (!self::semaGaranti()) return 0;
        return DB::table('wa_asistan_talepler')->where('oturum_id', $oturumId)->where('durum', 'bekliyor')
            ->update(['durum' => 'cevaplandi', 'cevaplandi_at' => now(), 'cevaplayan' => mb_substr($kim, 0, 120), 'updated_at' => now()]);
    }

    /** Zamanlanmis takip (5 dk'da bir). */
    public static function takip(): array
    {
        $r = ['hatirlatma' => 0, 'asistan' => 0, 'cevapsiz' => 0];
        if (!self::semaGaranti()) return $r;
        $saat = (int) date('G');
        $gunduz = $saat >= 9 && $saat < 21;
        foreach (DB::table('wa_asistan_talepler')->where('durum', 'bekliyor')->where('created_at', '<', now()->subMinutes(self::HATIRLAT_DK))->orderBy('id')->limit(300)->get() as $t) {
            $yas = time() - strtotime($t->created_at);
            $o = DB::table('wa_asistan_oturumlari')->where('id', $t->oturum_id)->first();
            if (!$o) continue;
            if ($yas >= self::CEVAPSIZ_SAAT * 3600) {
                DB::table('wa_asistan_talepler')->where('id', $t->id)->update(['durum' => 'cevapsiz', 'updated_at' => now()]);
                $r['cevapsiz']++;
                continue;
            }
            if (!$t->hatirlatma_at && $saat >= 8 && $saat < 22) {
                WaAsistan::bildir((int) $t->isletme_id, self::bildirimMetni($o, $t->konu, (string) $t->ozet, true), self::url((int) $t->isletme_id, (int) $t->oturum_id));
                DB::table('wa_asistan_talepler')->where('id', $t->id)->update(['hatirlatma_at' => now(), 'updated_at' => now()]);
                $r['hatirlatma']++;
            }
            if (!$t->asistan_dondu_at && $yas >= self::ASISTAN_DON_DK * 60 && $gunduz && $t->konu !== 'yanlis_numara') {
                if (WaAsistan::oturumaYaz((int) $t->oturum_id, self::takipMetni($t->konu, !empty($o->randevu_id)), 'takip')) $r['asistan']++;
                DB::table('wa_asistan_talepler')->where('id', $t->id)->update(['asistan_dondu_at' => now(), 'updated_at' => now()]);
            }
        }
        return $r;
    }

    // ------------------------------------------------------------------ okuma
    public static function oturumTalepleri(int $oturumId): array
    {
        if (!self::semaGaranti()) return [];
        return DB::table('wa_asistan_talepler')->where('oturum_id', $oturumId)->orderBy('id')->get()->map(function ($t) { return self::satir($t); })->all();
    }

    /** [oturum_id => son talep satiri] */
    public static function oturumlarIcin(array $oturumIdler): array
    {
        if (!$oturumIdler || !self::semaGaranti()) return [];
        $out = [];
        foreach (DB::table('wa_asistan_talepler')->whereIn('oturum_id', $oturumIdler)->orderBy('id')->get() as $t) $out[(int) $t->oturum_id] = self::satir($t);
        return $out;
    }

    public static function bekleyenler(int $isletmeId): array
    {
        if (!self::semaGaranti()) return [];
        return DB::table('wa_asistan_talepler as t')->join('wa_asistan_oturumlari as o', 'o.id', '=', 't.oturum_id')
            ->where('t.isletme_id', $isletmeId)->where('t.durum', 'bekliyor')->orderBy('t.id')->limit(50)
            ->get(['t.*', 'o.ad', 'o.telefon', 'o.musteri_tel'])->map(function ($t) {
                return self::satir($t) + ['ad' => $t->ad, 'telefon' => $t->musteri_tel ?: $t->telefon, 'tarih' => $t->created_at];
            })->all();
    }

    public static function satir($t): array
    {
        $bitis = $t->cevaplandi_at ?: ($t->durum === 'bekliyor' ? date('Y-m-d H:i:s') : $t->updated_at);
        return ['id' => (int) $t->id, 'oturum_id' => (int) $t->oturum_id, 'konu' => $t->konu, 'konu_ad' => self::KONU[$t->konu] ?? $t->konu,
            'ozet' => $t->ozet, 'bildirilen' => json_decode($t->bildirilen ?: '[]', true) ?: [], 'durum' => $t->durum,
            'acildi' => $t->created_at, 'hatirlatma_at' => $t->hatirlatma_at, 'asistan_dondu_at' => $t->asistan_dondu_at,
            'cevaplandi_at' => $t->cevaplandi_at, 'cevaplayan' => $t->cevaplayan,
            'sure' => self::sureMetni(strtotime($bitis) - strtotime($t->created_at))];
    }

    /** Sistem yonetimi: tum salonlar (son N gun). */
    public static function sistemListesi(int $gun = 7): array
    {
        if (!self::semaGaranti()) return [];
        return DB::table('wa_asistan_talepler as t')->join('wa_asistan_oturumlari as o', 'o.id', '=', 't.oturum_id')
            ->leftJoin('salonlar as s', 's.id', '=', 't.isletme_id')
            ->where(function ($w) use ($gun) { $w->where('t.durum', 'bekliyor')->orWhere('t.created_at', '>=', now()->subDays($gun)); })
            ->orderByRaw("FIELD(t.durum,'bekliyor','cevapsiz','cevaplandi')")->orderByDesc('t.id')->limit(500)
            ->get(['t.*', 'o.ad', 'o.telefon', 'o.musteri_tel', 's.salon_adi'])->map(function ($t) {
                return self::satir($t) + ['salon' => $t->salon_adi ?: ('Salon #' . $t->isletme_id), 'isletme_id' => (int) $t->isletme_id,
                    'ad' => $t->ad, 'telefon' => $t->musteri_tel ?: $t->telefon];
            })->all();
    }
}
