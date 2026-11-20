<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp asistani — restaurant-laravel /api/wa/gelen akisinin randevumcepte uyarlamasi.
 *
 * Kopru (whatsmeow-bridge) gelen mesaji "message.received", personelin kendi telefonundan yazdigini
 * "message.manual" olarak WhatsAppWebhookController'a yollar; o da buraya devreder.
 * Cevabi santraldeki RandevuAsistan uretir ('whatsapp' kanal modu); tum randevu araclari ayni.
 *
 * Restoranda OLMAYAN korumalar (ban + maliyet riski) burada en bastan var:
 *  - numara basina hiz siniri (10 dk'da N cevap; asilirsa 60 dk sus + isletmeye bildirim)
 *  - dongu tespiti (ayni mesaj 3 kez) ve "otomatik yanit" kalibi (iki botun birbirine cevap vermesi)
 *  - hat basina gunluk bot cevabi tavani
 *  - haric numaralar: isletme personeli, yetkili telefonu, WA_ASISTAN_HARIC listesi (+ kara liste)
 *  - 10 dk'dan eski (cevrimdisi birikmis) mesajlar kopruda elenir
 * Restorandan aynen: grup/durum mesaji yok, echo ayikligi, personel elle yazinca AI susar (esikten sonra geri alir).
 *
 * Webhook hemen doner; is cevap gonderildikten sonra (app()->terminating) yapilir. Art arda yazilan
 * mesajlar birlestir_sn icinde tek mesaj sayilir; ayni sohbette ayni anda tek islem (DB kilidi).
 */
class WaAsistan
{
    const SEMA = 'wa_asistan_sema_v1';

    // ------------------------------------------------------------------ sema
    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        try {
            if (!Schema::hasTable('wa_asistan_oturumlari')) {
                Schema::create('wa_asistan_oturumlari', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');                 // WhatsApp hattinin (oturumun) salonu
                    $t->unsignedBigInteger('isletme_id');               // asistanin hizmet verdigi salon (paylasilan hatta farkli olabilir)
                    $t->string('anahtar', 100);                         // numara (90...) ya da gizli numara JID'i (<lid>@lid)
                    $t->string('jid', 100)->nullable();                 // cevap hedefi
                    $t->string('telefon', 30)->nullable();              // WhatsApp numarasi (gizliyse null)
                    $t->string('musteri_tel', 30)->nullable();          // kayitta ogrenilen telefon (gizli numara)
                    $t->string('ad', 100)->nullable();                  // WhatsApp profil adi
                    $t->longText('gecmis')->nullable();                 // JSON [{role,content,...}]
                    $t->string('durum', 15)->default('acik');           // acik | kapandi
                    $t->string('sonuc', 20)->nullable();                // randevu | iptal | guncelle | aktar | teyit ...
                    $t->unsignedBigInteger('randevu_id')->nullable();
                    $t->tinyInteger('insan_devraldi')->default(0);
                    $t->timestamp('son_insan_at')->nullable();
                    $t->timestamp('sessiz_bitis')->nullable();          // bu zamana kadar AI cevap vermez
                    $t->string('sessiz_neden', 30)->nullable();         // aktar | hiz_siniri | dongu | otomatik_yanit
                    $t->text('cevap_zamanlari')->nullable();            // JSON [unix] (hiz siniri)
                    $t->unsignedInteger('mesaj_sayisi')->default(0);
                    $t->unsignedInteger('cevap_sayisi')->default(0);
                    $t->unsignedInteger('girdi_token')->default(0);
                    $t->unsignedInteger('cikti_token')->default(0);
                    $t->unsignedInteger('cache_okuma_token')->default(0);
                    $t->unsignedInteger('cache_yazma_token')->default(0);
                    $t->decimal('maliyet_usd', 12, 6)->default(0);
                    $t->string('kilit', 40)->nullable();
                    $t->timestamp('kilit_at')->nullable();
                    $t->timestamp('son_mesaj_at')->nullable();
                    $t->timestamp('created_at')->nullable();
                    $t->timestamp('updated_at')->nullable();
                    $t->index(['salon_id', 'anahtar']);
                    $t->index(['isletme_id', 'created_at']);
                });
            }
            if (!Schema::hasTable('wa_asistan_gelen')) {
                Schema::create('wa_asistan_gelen', function ($t) {
                    $t->bigIncrements('id');
                    $t->unsignedBigInteger('salon_id');
                    $t->string('anahtar', 100);
                    $t->string('jid', 100)->nullable();
                    $t->string('telefon', 30)->nullable();
                    $t->string('ad', 100)->nullable();
                    $t->text('metin');
                    $t->string('islem', 40)->nullable();                // isleyen token (null = bekliyor)
                    $t->timestamp('created_at')->nullable();
                    $t->index(['salon_id', 'anahtar', 'islem']);
                });
            }
            Cache::forever(self::SEMA, 1);
            return true;
        } catch (\Throwable $e) {
            Log::error('[WA-ASISTAN] sema olusturulamadi: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ ayarlar
    protected static function ayar(string $k, $vars = null)
    {
        return config('services.wa_asistan.' . $k, $vars);
    }

    /** Bu salonun WhatsApp hattinda asistan acik mi? (.env WA_ASISTAN_SALONLAR="20,236" | "*") */
    public static function acikMi(int $salonId): bool
    {
        if ($salonId <= 0) return false;
        $l = trim((string) self::ayar('salonlar', ''));
        if ($l !== '' && ($l === '*' || in_array($salonId, array_map('intval', preg_split('/[\s,;]+/', $l)), true))) return true;
        // Sistem Yonetimi > WhatsApp Asistan Paketleri'nde paketi tanimli hat da acik
        $p = WaPaket::paket($salonId);
        return $p && (int) $p->dahil_mesaj > 0;
    }

    protected static function tel(?string $t): ?string
    {
        return $t ? app(WhatsAppService::class)->normalizePhone($t) : null;
    }

    /** Asistanin cevap vermeyecegi numaralar: personel, yetkili, .env listesi. */
    protected static function haricMi(int $salonId, string $tel): bool
    {
        $liste = Cache::remember("wa_asistan_haric2_{$salonId}", 10, function () use ($salonId) {
            return array_keys(self::haricListesi($salonId));
        });
        return in_array($tel, $liste, true);
    }

    /**
     * [telefon => neden] — asistanin cevap vermedigi numaralar. Yalniz AKTIF personel (isten ayrilan personelin numarasi
     * musteri olarak yazabilir; eskiden pasif personel de haricti ve mesaj hic kaydedilmiyordu — 2026-11-19).
     */
    public static function haricListesi(int $salonId): array
    {
        $out = [];
        foreach (preg_split('/[\s,;]+/', (string) self::ayar('haric', '')) as $n) {
            if ($n = self::tel($n)) $out[$n] = 'Asistan ayarlarındaki hariç numara listesinde';
        }
        try {
            $salonlar = DB::table('salonlar')->where('whatsapp_paylasilan_salon_id', $salonId)->pluck('id')->push($salonId)->all();
            $pq = DB::table('salon_personelleri')->whereIn('salon_id', $salonlar);
            if (\Illuminate\Support\Facades\Schema::hasColumn('salon_personelleri', 'aktif')) $pq->where('aktif', 1);
            foreach ($pq->get(['cep_telefon', 'personel_adi']) as $p) {
                if ($n = self::tel($p->cep_telefon)) $out[$n] = 'Personel numarası (' . trim((string) $p->personel_adi) . ')';
            }
            foreach (DB::table('salonlar')->whereIn('id', $salonlar)->get(['yetkili_telefon', 'salon_adi']) as $s) {
                if ($n = self::tel($s->yetkili_telefon)) $out[$n] = 'İşletme yetkili telefonu (' . trim((string) $s->salon_adi) . ')';
            }
        } catch (\Throwable $e) {}
        return $out;
    }

    // ------------------------------------------------------------------ webhook girisleri
    /** Kopru type -> gecmise yazilan etiket (icerigi okunamayan mesajlar). */
    const MEDYA = [
        'audio' => '[🎤 Sesli mesaj]', 'ptt' => '[🎤 Sesli mesaj]', 'voice' => '[🎤 Sesli mesaj]',
        'video' => '[🎬 Video]', 'document' => '[📄 Belge]', 'sticker' => '[Çıkartma]',
        'location' => '[📍 Konum]', 'contact' => '[👤 Kişi kartı]', 'contacts' => '[👤 Kişi kartı]',
    ];

    /** Yalniz okunamayan medya mi? -> sabit cevap (AI'a gitmez). null = degil. */
    public static function medyaCevabi(string $metin): ?array
    {
        $t = trim($metin);
        if (!preg_match('/^(\[[^\]]+\]\s*)+$/u', $t) || !preg_match('/Sesli mesaj|Video|Belge|Çıkartma|Konum|Kişi kartı|açılamadı/u', $t)) return null;
        if (preg_match('/^(\[Çıkartma\]\s*)+$/u', $t)) return ['cevap' => null]; // emoji gibi: cevap gerekmez
        if (preg_match('/Sesli mesaj|Video/u', $t)) return ['cevap' => 'Sesli mesaj ve videoları şu an dinleyemiyorum 🙏 İsteğinizi kısaca yazar mısınız? Hemen yardımcı olayım.'];
        if (preg_match('/açılamadı/u', $t)) return ['cevap' => 'Gönderdiğiniz fotoğrafı açamadım 🙏 Tekrar gönderebilir ya da ne istediğinizi kısaca yazabilir misiniz?'];
        if (preg_match('/Konum/u', $t)) return ['cevap' => 'Konumunuzu aldım 🙏 Size nasıl yardımcı olabilirim? Randevu, fiyat ya da yol tarifi için yazabilirsiniz.'];
        return ['cevap' => 'Gönderdiğiniz dosyayı aldım ve işletmemize ilettim 🙏 Size nasıl yardımcı olabilirim?', 'talep' => true];
    }

    /** message.received: mesaji kuyruga yaz, isi cevap gonderildikten sonra yap. */
    public static function gelen(array $p): array
    {
        $salonId = (int) ($p['salonId'] ?? 0);
        if (!self::acikMi($salonId)) return ['tip' => 'kapali'];
        $metin = trim((string) ($p['text'] ?? ''));
        // Fotograf (kopru: type=image, image=base64, text=fotograf aciklamasi): Gorsel Danisman
        $resim = ($p['type'] ?? '') === 'image' && !empty($p['image']) ? (string) $p['image'] : '';
        // Sesli mesaj / video / belge / cikartma / konum / kisi karti: icerik okunamaz ama mesaj KAYBOLMAZ
        // (eskiden 'bos' deyip atiliyordu: panelde gorunmuyor, musteri cevapsiz kaliyordu — 2026-11-19 Ola Nail)
        $medya = self::MEDYA[(string) ($p['type'] ?? '')] ?? null;
        if ($metin === '' && $resim === '' && $medya) $metin = $medya;
        elseif ($medya && $resim === '') $metin = $medya . ' ' . $metin;
        if ($metin === '' && $resim === '') return ['tip' => 'bos'];
        $metin = mb_substr($metin, 0, 1000);
        $jid = (string) ($p['fromJid'] ?? '');
        $lid = !empty($p['fromLid']) || substr($jid, -4) === '@lid';
        $tel = $lid ? null : self::tel((string) ($p['from'] ?? ''));
        $anahtar = $lid ? $jid : (string) $tel;
        // Gizli kimlik (LID): kopru gercek numarayi biliyorsa (fromPn) musteri tanınır, telefon sorulmaz (anahtar LID kalir)
        if ($lid && !empty($p['fromPn'])) $tel = self::tel((string) $p['fromPn']);
        if ($anahtar === '') return ['tip' => 'gecersiz'];
        if (!self::semaGaranti()) return ['tip' => 'sema_yok'];

        $mid = (string) ($p['messageId'] ?? '');
        if ($mid !== '' && !Cache::add('wa_asistan_mid_' . $mid, 1, 60)) return ['tip' => 'tekrar'];
        if ($tel && self::haricMi($salonId, $tel)) {
            Log::info('[WA-ASISTAN] haric numara, cevap yok', ['salon' => $salonId, 'tel' => $tel]);
            // Teshis icin iz (panel "Numara kontrol"): islenmez, 7 gunde silinir
            try {
                DB::table('wa_asistan_gelen')->insert(['salon_id' => $salonId, 'anahtar' => $anahtar, 'jid' => $jid ?: null, 'telefon' => $tel,
                    'ad' => mb_substr((string) ($p['pushName'] ?? ''), 0, 100) ?: null, 'metin' => mb_substr($metin, 0, 1000), 'islem' => 'haric', 'created_at' => now()]);
            } catch (\Throwable $e) {}
            return ['tip' => 'haric'];
        }

        $gorselId = null;
        if ($resim !== '' && WaAsistanVeri::semaGaranti()) {
            try { $gorselId = WaGorselDanisman::fotografKaydet($salonId, $anahtar, $resim); }
            catch (\Throwable $e) { Log::warning('[WA-GORSEL] fotograf kaydedilemedi: ' . $e->getMessage()); }
            if (!$gorselId && $metin === '') $metin = '[📷 Fotoğraf — açılamadı]'; // atilmaz: panelde gorunur, asistan tarif ister
        }
        $satir = [
            'salon_id' => $salonId, 'anahtar' => $anahtar, 'jid' => $jid ?: null, 'telefon' => $tel,
            'ad' => mb_substr((string) ($p['pushName'] ?? ''), 0, 100) ?: null, 'metin' => $gorselId ? trim('[fotoğraf] ' . $metin) : $metin,
            'created_at' => now(),
        ];
        if ($gorselId) $satir['gorsel_id'] = $gorselId;
        $id = DB::table('wa_asistan_gelen')->insertGetId($satir);
        app()->terminating(function () use ($salonId, $anahtar, $id) {
            try { self::isle($salonId, $anahtar, $id); }
            catch (\Throwable $e) {
                Log::error('[WA-ASISTAN] isleme hatasi: ' . $e->getMessage(), ['salon' => $salonId, 'anahtar' => $anahtar, 'yer' => $e->getFile() . ':' . $e->getLine()]);
                self::hataSakla($salonId, $anahtar, $e);
            }
        });
        if (mt_rand(1, 200) === 1) {
            try { DB::table('wa_asistan_gelen')->where('created_at', '<', now()->subDays(7))->delete(); } catch (\Throwable $e) {}
        }
        return ['tip' => 'kuyruk'];
    }

    /**
     * Ilk cevabin basina selam: model zaten selamla basladiysa eklenmez ("Merhaba Rabia Hanım. Merhaba Rabia Hanım, ..."
     * cift selami — 2026-11-20 Ola Nail).
     */
    public static function selamEkle(string $selam, string $cevap): string
    {
        $bas = mb_strtolower(strtr(ltrim(preg_replace('/^[^\p{L}]+/u', '', $cevap)), ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        if ($cevap === '' || preg_match('/^(merhaba|merhabalar|selam|selamlar|iyi günler|iyi akşamlar|günaydın|hoş geldiniz|hoşgeldiniz)\b/u', $bas)) return $cevap;
        return trim($selam . ' ' . $cevap);
    }

    /** Isleme hatasi: son 20 hata 1 gun saklanir (panel /whatsapp-asistan/son-hata, sistem yoneticisi; sunucu loguna gerek kalmaz). */
    public static function hataSakla(int $salonId, string $anahtar, \Throwable $e): void
    {
        try {
            $l = Cache::get('wa_asistan_son_hatalar', []);
            $iz = [];
            foreach (array_slice($e->getTrace(), 0, 6) as $t) $iz[] = basename($t['file'] ?? '?') . ':' . ($t['line'] ?? '?') . ' ' . ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? '');
            array_unshift($l, ['at' => date('Y-m-d H:i:s'), 'salon' => $salonId, 'anahtar' => $anahtar, 'hata' => mb_substr($e->getMessage(), 0, 500),
                'yer' => basename($e->getFile()) . ':' . $e->getLine(), 'iz' => $iz]);
            Cache::put('wa_asistan_son_hatalar', array_slice($l, 0, 20), 1440);
        } catch (\Throwable $x) {}
    }

    /** message.manual: personel kendi telefonundan yazdi -> o sohbette AI susar (insan_esik_dk boyunca). */
    public static function manuel(array $p): array
    {
        $salonId = (int) ($p['salonId'] ?? 0);
        if (!self::acikMi($salonId) || !self::semaGaranti()) return ['tip' => 'kapali'];
        $jid = (string) ($p['toJid'] ?? '');
        $lid = substr($jid, -4) === '@lid';
        $tel = $lid ? null : self::tel((string) ($p['to'] ?? ''));
        $anahtar = $lid ? $jid : (string) $tel;
        if ($anahtar === '') return ['tip' => 'gecersiz'];
        $o = self::aktifOturum($salonId, $anahtar) ?: self::yeniOturum($salonId, $anahtar, $jid ?: null, $tel, null);
        $g = json_decode($o->gecmis ?: '[]', true) ?: [];
        $metin = mb_substr(trim((string) ($p['text'] ?? '')), 0, 1000);
        if ($metin !== '') $g[] = ['role' => 'assistant', 'content' => $metin, 'personel' => 1, 'at' => date('Y-m-d H:i:s')];
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update([
            'gecmis' => json_encode(array_slice($g, -40), JSON_UNESCAPED_UNICODE),
            'insan_devraldi' => 1, 'son_insan_at' => now(), 'son_mesaj_at' => now(), 'updated_at' => now(),
        ]);
        // Isletme musteriye dondu: bekleyen talepler "cevaplandi" (panelde sonuc gorunur, takip/hatirlatma durur)
        if ($metin !== '') { try { WaTalep::cevaplandi((int) $o->id, 'İşletme (WhatsApp\'tan yazdı)'); } catch (\Throwable $e) {} }
        Log::info('[WA-ASISTAN] personel devraldi', ['salon' => $salonId, 'oturum' => $o->id]);
        return ['tip' => 'insan_devraldi'];
    }

    // ------------------------------------------------------------------ oturum
    /** Acik sohbet; oturum_dk sessizlikten sonra yeni yazisma (susturulmus sohbet susturma bitene kadar surer). */
    protected static function aktifOturum(int $salonId, string $anahtar)
    {
        $o = DB::table('wa_asistan_oturumlari')->where('salon_id', $salonId)->where('anahtar', $anahtar)
            ->where('durum', 'acik')->orderByDesc('id')->first();
        if (!$o) return null;
        $dk = max(5, (int) self::ayar('oturum_dk', 30));
        $taze = $o->son_mesaj_at && strtotime($o->son_mesaj_at) >= time() - $dk * 60;
        $sessiz = $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time();
        $insan = $o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - max(1, (int) self::ayar('insan_esik_dk', 5)) * 60;
        // Geri kazanma mesaji gonderilmis sohbet: musteri 2 gun icinde cevap verirse ayni sohbette devam (baglam: onerilen saatler)
        $gk = !empty($o->geri_kazanim_id) && $o->son_mesaj_at && strtotime($o->son_mesaj_at) >= time() - 48 * 3600;
        if ($taze || $sessiz || $insan || $gk) return $o;
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update(['durum' => 'kapandi', 'updated_at' => now()]);
        return null;
    }

    protected static function yeniOturum(int $salonId, string $anahtar, ?string $jid, ?string $tel, ?string $ad)
    {
        $id = DB::table('wa_asistan_oturumlari')->insertGetId([
            'salon_id' => $salonId, 'isletme_id' => $salonId, 'anahtar' => $anahtar, 'jid' => $jid, 'telefon' => $tel,
            'ad' => $ad, 'gecmis' => '[]', 'durum' => 'acik', 'son_mesaj_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return DB::table('wa_asistan_oturumlari')->where('id', $id)->first();
    }

    // ------------------------------------------------------------------ isleme
    protected static function isle(int $salonId, string $anahtar, int $gelenId): void
    {
        $bek = max(0, min(15, (int) self::ayar('birlestir_sn', 2)));
        if ($bek > 0) sleep($bek);
        // Bu arada musteri yeni mesaj yazdiysa onu isleyen istek hepsini birlikte cevaplar
        $yeniVar = DB::table('wa_asistan_gelen')->where('salon_id', $salonId)->where('anahtar', $anahtar)
            ->whereNull('islem')->where('id', '>', $gelenId)->exists();
        if ($yeniVar) return;

        WaAsistanVeri::semaGaranti(); // ek kolonlar (isaretler, memnuniyet, geri kazanma) + cevap kaydi tablosu
        $ilk = DB::table('wa_asistan_gelen')->where('id', $gelenId)->first();
        $o = self::aktifOturum($salonId, $anahtar)
            ?: self::yeniOturum($salonId, $anahtar, $ilk->jid ?? null, $ilk->telefon ?? null, $ilk->ad ?? null);

        // Ayni sohbette tek islem: onceki cevap uretilirken gelen mesaj, o bitince islenir
        $token = substr(md5(uniqid('', true)), 0, 32);
        $alindi = 0;
        for ($i = 0; $i < 120 && !$alindi; $i++) {
            $alindi = DB::table('wa_asistan_oturumlari')->where('id', $o->id)->where(function ($w) {
                $w->whereNull('kilit')->orWhere('kilit_at', '<', now()->subMinutes(2));
            })->update(['kilit' => $token, 'kilit_at' => now()]);
            if (!$alindi) usleep(500000);
        }
        if (!$alindi) {
            Log::warning('[WA-ASISTAN] sohbet kilidi alinamadi', ['oturum' => $o->id]);
            return;
        }
        try {
            DB::table('wa_asistan_gelen')->where('salon_id', $salonId)->where('anahtar', $anahtar)->whereNull('islem')->update(['islem' => $token]);
            $satirlar = DB::table('wa_asistan_gelen')->where('islem', $token)->orderBy('id')->get();
            if ($satirlar->isEmpty()) return;
            self::cevapla(DB::table('wa_asistan_oturumlari')->where('id', $o->id)->first(), $satirlar);
        } finally {
            DB::table('wa_asistan_oturumlari')->where('id', $o->id)->where('kilit', $token)->update(['kilit' => null, 'kilit_at' => null]);
        }
    }

    protected static function cevapla($o, $satirlar): void
    {
        $son = $satirlar->last();
        $metin = trim($satirlar->pluck('metin')->implode("\n"));
        $gecmis = json_decode($o->gecmis ?: '[]', true) ?: [];
        $tel = $o->telefon ?: $son->telefon;
        $hedef = $son->jid ?: ($o->jid ?: $tel);
        $upd = ['son_mesaj_at' => now(), 'updated_at' => now(), 'mesaj_sayisi' => DB::raw('mesaj_sayisi + ' . count($satirlar))];
        if ($son->jid) $upd['jid'] = $son->jid;
        if ($son->ad && !$o->ad) $upd['ad'] = $son->ad;
        if ($son->ad && !$o->ad) $upd['ad'] = $son->ad;
        // Mesaj saati (panelde balonun altinda): musteri mesaji = ilk gelen satirin zamani
        $musteriAn = (string) ($satirlar->first()->created_at ?? date('Y-m-d H:i:s'));
        $kaydet = function (array $g) use ($o, &$upd) {
            $upd['gecmis'] = json_encode(array_slice($g, -40), JSON_UNESCAPED_UNICODE);
            DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update($upd);
        };
        // Cevap verilmeyecekse mesaj yine gecmise yazilir (panel + AI geri dondugunde baglam)
        $sus = function (string $neden) use (&$gecmis, $metin, $kaydet, $o, $musteriAn) {
            $gecmis[] = ['role' => 'user', 'content' => $metin, 'at' => $musteriAn];
            $kaydet($gecmis);
            Log::info('[WA-ASISTAN] cevap verilmedi: ' . $neden, ['oturum' => $o->id, 'salon' => $o->salon_id]);
        };

        // 1) PERSONEL DEVRALDI: esik dolana kadar AI susar; personel sustuysa AI geri alir
        if ($o->insan_devraldi) {
            if ($o->son_insan_at && strtotime($o->son_insan_at) > time() - max(1, (int) self::ayar('insan_esik_dk', 5)) * 60) {
                $sus('insan_devralmada');
                return;
            }
            $upd['insan_devraldi'] = 0;
        }
        // 2) SUSTURULMUS SOHBET (personel devraldi / hiz siniri / dongu / otomatik yanit).
        //    'aktar' artik susturmaz (eski kayitlar dahil): asistan talebi isletmeye iletir, sohbete devam eder (WaTalep).
        //    'hiz_siniri' de artik susturmaz (kural 2026-11-20 kaldirildi; onceden susturulmus sohbetler hemen acilir).
        if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time() && !in_array($o->sessiz_neden, ['aktar', 'hiz_siniri'], true)) {
            $sus('sessiz:' . $o->sessiz_neden);
            return;
        }
        // 3) KARSI TARAF BOT: "otomatik yanit" kalibi -> 24 saat sus (iki bot birbirine sonsuz cevap vermesin)
        if (self::otomatikYanitMi($metin)) {
            $upd['sessiz_bitis'] = now()->addHours(24); $upd['sessiz_neden'] = 'otomatik_yanit';
            $sus('otomatik_yanit');
            return;
        }
        // Yalniz cikartma: kayda gecer, cevap gerekmez (kapanisMi: havada sayilmaz)
        if (($mc = self::medyaCevabi($metin)) && $mc['cevap'] === null) {
            $sus('cikartma');
            return;
        }
        // 4) DONGU: ayni mesaj ust uste 3. kez
        $oncekiler = array_values(array_filter($gecmis, function ($m) { return ($m['role'] ?? '') === 'user'; }));
        $n = count($oncekiler);
        if ($n >= 2 && self::sade($oncekiler[$n - 1]['content'] ?? '') === self::sade($metin)
            && self::sade($oncekiler[$n - 2]['content'] ?? '') === self::sade($metin)) {
            $upd['sessiz_bitis'] = now()->addMinutes(60); $upd['sessiz_neden'] = 'dongu';
            $sus('dongu');
            return;
        }
        // 5) NUMARA BASI HIZ SINIRI kaldirildi (2026-11-20, kullanici karari): hizli yazan musteri susturulmaz.
        //    Bot/dongu korumasi 3) ve 4) ile devam eder.
        // 6) HAT BASI GUNLUK TAVAN — varsayilan KAPALI (0). Asistan yalniz kendisine yazan musteriye cevap verir;
        // Meta riski toplu/istenmeyen gonderimde (geri kazanma kendi tavanini korur). Ayni numara dongusu 5) ile sinirli.
        // Gerekirse .env WA_ASISTAN_GUNLUK_TAVAN=600 ile acilir.
        // $oturumSalonlari tavan blogunun DISINDA: asagida hatirlatma aramasi da kullanir (blok icindeyken tavan=0'da
        // tanimsiz kaliyordu -> ErrorException, yeni sohbetlerde asistan hic cevap vermiyordu — 2026-11-20 Ola Nail)
        $oturumSalonlari = DB::table('salonlar')->where('whatsapp_paylasilan_salon_id', $o->salon_id)->pluck('id')->push($o->salon_id)->all();
        $tavan = (int) self::ayar('gunluk_tavan', 0);
        if ($tavan > 0) {
        $bugun = 0;
        try {
            $bugun = DB::table('whatsapp_gonderim_loglari')->whereIn('salon_id', $oturumSalonlari)
                ->where('gonderim_tipi', WhatsAppService::ASISTAN_TIPI)->where('created_at', '>=', now()->startOfDay())->count();
        } catch (\Throwable $e) {}
        if ($bugun >= max(10, $tavan)) {
            $sus('gunluk_tavan');
            if (Cache::add("wa_asistan_tavan_{$o->salon_id}_" . date('Ymd'), 1, 1440)) {
                self::isletmeyeBildir((int) $o->isletme_id, $o, 'WhatsApp asistanı bugünkü cevap sınırına ulaştı; bugün gelen yeni mesajlara cevap vermeyecek.');
            }
            return;
        }
        }

        // 7) PAKET BITTI (Sistem Yonetimi > WhatsApp Asistan Paketleri; asimda durdur): ay sonuna kadar cevap yok
        if (WaPaket::kapali((int) $o->salon_id)) {
            $sus('paket_bitti');
            return;
        }

        $salon = Salonlar::find($o->salon_id);
        if (!$salon) return;

        // ---- cevap
        $selam = null; $d = null; $cevap = '';
        $kaynak = 'kod';           // wa_asistan_cevaplar.kaynak (rapor); 'ai' = Claude cagrildi (paketten duser)
        $ekUsd = 0.0;              // Claude disi AI maliyeti (gorsel analiz)
        $baglamEk = '';            // gecmise gizli not (gorsel analiz)
        $hatirlatma = null;
        if (empty($gecmis) && $tel && ($hatirlatma = self::sonHatirlatma($oturumSalonlari, $tel))) {
            // WhatsApp hatirlatmasina cevap: hatirlatmanin salonu asistanin salonu olur (paylasilan hat)
            $upd['isletme_id'] = $hatirlatma['salon_id'];
            $o->isletme_id = $hatirlatma['salon_id'];
        }
        $ayar = WaAsistanVeri::ayarlar((int) $o->isletme_id);
        // Numara gizli (LID) ama bu kisi daha once numarasini verdiyse: onceki sohbetten (her seferinde tekrar sorulmasin)
        $musteriTel = $o->musteri_tel ?: $tel;
        if (!$musteriTel) {
            $musteriTel = DB::table('wa_asistan_oturumlari')->where('salon_id', $o->salon_id)->where('anahtar', $o->anahtar)
                ->whereNotNull('musteri_tel')->orderByDesc('id')->value('musteri_tel');
            if ($musteriTel) $upd['musteri_tel'] = $musteriTel;
        }
        $as = new RandevuAsistan((int) $o->isletme_id, $musteriTel, 'whatsapp');
        $as->waFiyatSoyle = !empty($ayar['fiyat_soyle']);
        $as->waProfilAdiAyarla($son->ad ?: $o->ad);
        try { $as->waDersleriAyarla(WaDenetci::aktifDersler((int) $o->isletme_id)); } catch (\Throwable $e) {} // Gece Denetcisi dersleri
        $musteriMesajVar = (bool) array_filter($gecmis, function ($m) { return ($m['role'] ?? '') === 'user'; });
        $gorselId = (int) ($satirlar->pluck('gorsel_id')->filter()->last() ?: 0);
        $gkCevap = (!empty($o->geri_kazanim_id) && !$musteriMesajVar) ? WaGeriKazanim::cevapGeldi((int) $o->geri_kazanim_id, $metin) : null;
        $on = null;

        if (self::yanlisNumaraMi($metin, $as->taninanMusteri())) {
            // YANLIS NUMARA / "bana mesaj atmayin": AI'ya birakilmaz (bilgi uydurmasin, tutulamayacak soz vermesin).
            // Ozur + isletmeye talep (musteri kartindaki numara yanlis olabilir) + bu numaraya asistan 30 gun yazmaz.
            $cevap = "Kusura bakmayın, rahatsızlık için özür dileriz 🙏 Numaranız işletmemizin kayıtlarında yanlışlıkla başka birine ait görünüyor olabilir. Durumu işletmemize ilettim, kayıt düzeltilecek. Bu sohbette size tekrar yazmayacağız.";
            $kaynak = 'kod';
            $upd['ret'] = 1; $upd['sessiz_bitis'] = now()->addDays(30); $upd['sessiz_neden'] = 'yanlis_numara';
            $kayitli = $as->taninanMusteri();
            WaTalep::ac($o, 'yanlis_numara', 'Müşteri: "' . mb_substr($metin, 0, 200) . '".'
                . ($kayitli ? " Bu numara sistemde \"{$kayitli}\" adına kayıtlı; müşteri kartındaki telefonu kontrol edin." : ' Bu numaraya mesaj gönderilmemesini istiyor.'));
        } elseif (($mc = self::medyaCevabi($metin)) !== null && $mc['cevap'] !== null) {
            // Sesli mesaj / video / belge / konum: icerik okunamaz -> sabit, kisa cevap (AI'ya gitmez, uydurmaz)
            $cevap = $mc['cevap']; $kaynak = 'kod';
            if (!empty($mc['talep'])) WaTalep::ac($o, 'diger', 'Müşteri bir dosya/kişi kartı gönderdi: ' . mb_substr($metin, 0, 100) . ' (WhatsApp\'tan bakın).');
        } elseif ($gkCevap !== null) {
            $cevap = $gkCevap; $kaynak = 'geri_kazanim';
        } elseif ($gorselId) {
            // MODEL FOTOGRAFI -> ozellik analizi + isletme kurallariyla fiyat (AI fiyat uydurmaz)
            $yazi = trim(preg_replace('/\[fotoğraf\]\s*/u', '', $metin));
            $g = WaGorselDanisman::isle($gorselId, (int) $o->isletme_id, (int) $o->id, $as->hizmetler(), $yazi);
            $ekUsd = (float) $g['maliyet_usd'];
            $kaynak = $g['ai'] ? 'gorsel' : 'gorsel_onbellek';
            $upd['gorsel_sayisi'] = DB::raw('gorsel_sayisi + 1');
            if (!empty($g['hizmet']['id'])) { $upd['son_hizmet_id'] = (int) $g['hizmet']['id']; $upd['son_hizmet_ad'] = $g['hizmet']['ad']; }
            if ($g['durum'] === 'fiyat') $upd['fiyat_verildi'] = 1;
            $baglamEk = (string) $g['baglam'];
            // Fiyat (ya da ek ucret) kurali yok: isletmeye sorulur (talep) ama asistan SUSMAZ, randevuya devam eder
            $cevap = $g['cevap'];
            if (!empty($g['talep'])) {
                WaTalep::ac($o, $g['talep'], trim('[fotoğraf] ' . ($g['tarif'] ?? '') . ($yazi !== '' ? ' — müşteri: ' . $yazi : '')));
                $upd['sonuc'] = 'aktar';
            }
            if (empty($gecmis) && $as->taninanMusteri()) $cevap = self::selamEkle($as->waSelam(), $cevap);
        } elseif ($hatirlatma && ($meta = $as->waHatirlatmaMeta($hatirlatma['randevu_id']))) {
            $gecmis[] = ['role' => 'assistant', 'content' => $hatirlatma['mesaj'], 'hatirlatma' => $meta];
            $d = $as->konus($metin, $gecmis);
        } elseif (empty($gecmis) && self::selamMi($metin)) {
            // Yalniz selam: karsilama. Ilk mesajda istek varsa (kayitsiz da olsa) karsilama YOK, istege dogrudan cevap
            // (kayit randevu onayinda; RandevuAsistan WA kayit kurali)
            $cevap = $as->waKarsilama();
            $kaynak = 'karsilama';
        } elseif ($on = WaBedavaKatman::onCevap((int) $o->isletme_id, $as, $metin, $gecmis, $o, $ayar)) {
            // BEDAVA KATMAN: fiyat / SSS / hizmet bilgisi / ogrenilen / tesekkur (Claude'a gitmez, paketten dusmez)
            $cevap = (string) $on['cevap'];
            $kaynak = (string) $on['kaynak'];
            if ($cevap !== '' && empty($gecmis)) $cevap = self::selamEkle($as->waSelam(), $cevap);
            if (!empty($on['hizmet']['id'])) { $upd['son_hizmet_id'] = (int) $on['hizmet']['id']; $upd['son_hizmet_ad'] = $on['hizmet']['ad']; }
            if (!empty($on['isaret']['fiyat'])) $upd['fiyat_verildi'] = 1;
            if (!empty($on['isaret']['bilgi'])) $upd['bilgi_verildi'] = 1;
            if (isset($on['memnuniyet'])) $upd['memnuniyet'] = (int) $on['memnuniyet'];
        } elseif (empty($gecmis)) {
            $selam = $as->waSelam();
            $d = $as->konus($metin, []);
        } else {
            $d = $as->konus($metin, $gecmis);
        }

        $aksiyon = null;
        if ($d) {
            $cevap = trim((string) ($d['cevap'] ?? ''));
            $aksiyon = $d['aksiyon'] ?? null;
            if ($aksiyon === 'aktar') {
                // Isletmeye sorulur (talep + bildirim + takip) ama asistan SUSMAZ: musteri sonraki mesajinda cevap alir
                $konu = WaTalep::konuBul($metin, (string) ($d['veri']['sebep'] ?? '') . ' ' . $cevap);
                $t = WaTalep::ac($o, $konu, $metin);
                $cevap = WaTalep::iletildiMetni($konu, !$t['yeni']);
                if ($o->sessiz_neden === 'aktar') { $upd['sessiz_bitis'] = null; $upd['sessiz_neden'] = null; }
            }
            $cevap = self::telefonIfadeleri($cevap);
            if ($selam) $cevap = self::selamEkle($selam, $cevap);
            if (!empty($d['bitir'])) $upd['durum'] = 'kapandi';
            if (in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'aktar', 'teyit', 'odeme'], true)) $upd['sonuc'] = $aksiyon;
            if (!empty($d['veri']['randevu_id'])) $upd['randevu_id'] = (int) $d['veri']['randevu_id'];
            if (($as->kullanim['cagri'] ?? 0) > 0) $kaynak = 'ai';
            elseif (!$gorselId) $kaynak = 'kod';
            self::isaretler($upd, $metin, $d, $as, $aksiyon);
            // OGRENEN ONBELLEK: baglamsiz bilgi sorusuna AI'nin tek cagrili, aracsiz cevabi -> bu isletmede sonra bedava
            if ($kaynak === 'ai' && WaBedavaKatman::cevapOgrenilebilir($metin, $d, $as->kullanim, $as->taninanMusteri())) {
                WaBedavaKatman::ogren((int) $o->isletme_id, $metin, $cevap);
            }
        }
        if ($as->yeniTelefon) $upd['musteri_tel'] = $as->yeniTelefon;
        // Musteri acikca vazgecti: bu sohbet icin geri kazanma mesaji gitmesin
        if (preg_match('/\b(istemiyorum|vazgeçtim|vazgectim|ilgilenmiyorum|gerek yok)\b/iu', $metin)) $upd['ret'] = 1;
        // Randevudan sonra tek seferlik memnuniyet sorusu (cevap bedava katmanda kaydedilir)
        if ($aksiyon === 'randevu' && !empty($ayar['memnuniyet_sor']) && empty($o->memnuniyet_soruldu) && $cevap !== '') {
            $cevap .= "\n\nYazışmamızdan memnun kaldıysanız 👍, kalmadıysanız 👎 gönderebilirsiniz.";
            $upd['memnuniyet_soruldu'] = 1;
        }

        // Maliyet (Claude token) -> oturum + AI kullanim kaydi
        $k = $as->kullanim;
        $usd = 0.0;
        if (($k['cagri'] ?? 0) > 0) {
            $usd = SantralPaket::claudeMaliyetUsd($k);
            $upd['girdi_token'] = DB::raw('girdi_token + ' . (int) $k['girdi']);
            $upd['cikti_token'] = DB::raw('cikti_token + ' . (int) $k['cikti']);
            $upd['cache_okuma_token'] = DB::raw('cache_okuma_token + ' . (int) $k['cache_okuma']);
            $upd['cache_yazma_token'] = DB::raw('cache_yazma_token + ' . (int) $k['cache_yazma']);
            AiKullanimLog::yaz((int) $o->isletme_id, 'wa_asistan', (int) $k['girdi'], (int) $k['cikti'],
                (string) (config('services.anthropic.santral_model') ?: 'claude-haiku-5-5'), false, true, $usd);
        }
        if ($usd + $ekUsd > 0) $upd['maliyet_usd'] = DB::raw('maliyet_usd + ' . sprintf('%.6F', $usd + $ekUsd));

        $gecmis[] = ['role' => 'user', 'content' => $metin, 'at' => $musteriAn];
        $gonderildi = false;
        if ($cevap !== '' && $hedef) {
            $r = app(WhatsAppService::class)->sendAsistanCevap($salon, $hedef, $cevap, $tel);
            $gonderildi = !empty($r['ok']);
            if (!$gonderildi) Log::warning('[WA-ASISTAN] cevap gonderilemedi', ['oturum' => $o->id, 'hata' => $r['error'] ?? null]);
        }
        // Musteri gormediyse cevap gecmise yazilmaz (islem yapildiysa yazilir: model bilsin)
        if ($gonderildi || in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'teyit', 'odeme'], true)) {
            $asistanMsj = ['role' => 'assistant', 'content' => $cevap, 'at' => date('Y-m-d H:i:s')];
            $b = trim(($d && !empty($d['baglam']) ? $d['baglam'] : '') . ($baglamEk !== '' ? ' ' . $baglamEk : ''));
            if ($b !== '') $asistanMsj['baglam'] = $b;
            $gecmis[] = $asistanMsj;
        }
        if ($gonderildi) {
            $upd['cevap_sayisi'] = DB::raw('cevap_sayisi + 1');
        }
        $kaydet($gecmis);
        // OLCUM: gonderilen her cevap (kaynak, AI mi, cevap suresi, mesai disi, randevu) -> "Kazandirdiklari" + paket sayaci
        $yapayZeka = $kaynak === 'ai' || $kaynak === 'gorsel';
        if ($gonderildi || ($yapayZeka && in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'teyit', 'odeme'], true))) {
            $ilkAn = strtotime((string) ($satirlar->first()->created_at ?? '')) ?: time();
            WaAsistanVeri::cevapKaydet([
                'salon_id' => (int) $o->salon_id, 'isletme_id' => (int) $o->isletme_id, 'oturum_id' => (int) $o->id,
                'kaynak' => $kaynak, 'ai' => $yapayZeka, 'sure_sn' => time() - $ilkAn, 'aksiyon' => $aksiyon,
                'randevu_id' => !empty($d['veri']['randevu_id']) ? (int) $d['veri']['randevu_id'] : null, 'maliyet_usd' => $usd + $ekUsd,
            ]);
            if ($yapayZeka) WaPaket::esikKontrol((int) $o->salon_id);
        }
        Log::info('[WA-ASISTAN] tur', ['oturum' => $o->id, 'salon' => $o->isletme_id, 'metin' => $metin, 'cevap' => $cevap,
            'aksiyon' => $aksiyon, 'kaynak' => $kaynak, 'teshis' => $as->teshis, 'gonderildi' => $gonderildi]);
    }

    /**
     * AI turundan geri kazanma / rapor isaretleri: fiyat verildi, musait saat onerildi, bilgi verildi, son konusulan hizmet.
     * Hizmet: arac baglamindaki hizmet_id ya da mesajda gecen hizmet adi.
     */
    protected static function isaretler(array &$upd, string $metin, array $d, RandevuAsistan $as, ?string $aksiyon): void
    {
        $baglam = is_array($d['baglam'] ?? null) ? implode(' ', $d['baglam']) : (string) ($d['baglam'] ?? '');
        if ($aksiyon !== 'aktar' && $as->waFiyatSoyle && WaBedavaKatman::fiyatSorusuMu($metin) && preg_match('/\d[\d.,]*\s*(TL|₺)/u', (string) ($d['cevap'] ?? ''))) {
            $upd['fiyat_verildi'] = 1;
        }
        if (strpos($baglam, 'musait_saatler') !== false) $upd['saat_onerildi'] = 1;
        if (!$aksiyon && WaBedavaKatman::bilgiSorusuMu($metin)) $upd['bilgi_verildi'] = 1;
        $hizmetler = $as->hizmetler();
        $hid = preg_match('/hizmet_id=(\d+)/', $baglam, $m) ? (int) $m[1] : null;
        if (!$hid && ($b = WaBedavaKatman::hizmetBul($metin, $hizmetler))) $hid = (int) $b[0]['id'];
        if ($hid) {
            foreach ($hizmetler as $h) {
                if ((int) $h['id'] === $hid) { $upd['son_hizmet_id'] = $hid; $upd['son_hizmet_ad'] = mb_substr((string) $h['ad'], 0, 150); break; }
            }
        }
    }

    // ------------------------------------------------------------------ yardimcilar
    /** Son 48 saatte bu numaraya WhatsApp'tan giden randevu hatirlatmasi (cevap ona verilmis olabilir). */
    protected static function sonHatirlatma(array $salonlar, string $tel): ?array
    {
        try {
            $l = DB::table('whatsapp_gonderim_loglari')->whereIn('salon_id', $salonlar)->where('telefon', $tel)
                ->whereIn('gonderim_tipi', ['randevu_hatirlatma_1gun', 'randevu_hatirlatma'])
                ->whereNotNull('randevu_id')->where('created_at', '>=', now()->subHours(48))
                ->orderByDesc('id')->first();
        } catch (\Throwable $e) {
            return null;
        }
        return $l ? ['salon_id' => (int) $l->salon_id, 'randevu_id' => (int) $l->randevu_id, 'mesaj' => (string) $l->mesaj] : null;
    }

    protected static function sade(string $s): string
    {
        $s = mb_strtolower(strtr($s, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s]/u', '', $s)));
    }

    /** Yalniz selamlasma mi ("merhaba", "iyi günler", "selamlar") -> karsilama. */
    protected static function selamMi(string $m): bool
    {
        $s = self::sade($m);
        if ($s === '' || count(explode(' ', $s)) > 4) return false;
        return (bool) preg_match('/^(merhaba(lar)?|mrb|mrh|selam(lar)?|slm|sa|selamün aleyküm|selamun aleykum|iyi (günler|akşamlar|geceler)|günaydın|hayırlı (günler|akşamlar)|hello|hi|hey)( (iyi günler|kolay gelsin|nasılsınız))?$/u', $s);
    }

    /** Karsi taraf otomatik yanit/bot mu? (iki bot birbirine cevap vermesin) */
    protected static function otomatikYanitMi(string $m): bool
    {
        $s = mb_strtolower($m, 'UTF-8');
        return (bool) preg_match('/otomatik (yanıt|cevap|mesaj)|auto.?reply|automatic reply|out of office|mesajınız (bize )?(ulaştı|alınmıştır|alındı)|mesajınızı aldık|en kısa sürede (size )?(dönüş|geri dönüş|geri dön)|mesai saatleri(miz)? dışında|şu an(da)? (müsait değil|cevap veremiyor|ulaşılamıyor)|bu numara(ya)? (whatsapp )?(mesaj|hizmet) (vermemektedir|kabul etmemektedir|kullanılmamaktadır)/u', $s);
    }

    /** RandevuAsistan'in telefon kalibi kalmissa yaziya cevir. */
    protected static function telefonIfadeleri(string $c): string
    {
        $c = str_replace(['bizi aradığınız için', 'Bizi aradığınız için'], ['bize yazdığınız için', 'Bize yazdığınız için'], $c);
        $c = preg_replace('/,?\s*lütfen hatta kalın\.?/u', '.', $c);
        $c = preg_replace('/\.{2,}/u', '.', $c);
        return trim($c);
    }

    /** Panel bildirimi + yoneticilere push (WhatsAppWebhookController ban uyarisi ile ayni kanal). */
    protected static function isletmeyeBildir(int $salonId, $o, string $mesaj): void
    {
        self::bildir($salonId, $mesaj);
    }

    /** Salon yoneticilerine panel bildirimi + push (Gece Denetcisi haftalik ozeti de kullanir). */
    /** @return array[] [[ad, ulasti]] bildirim gonderilen yoneticiler; ulasti = telefon/tarayicisina push gercekten gitti (panelde "kime bildirildi") */
    public static function bildir(int $salonId, string $mesaj, ?string $url = null): array
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
        $adlar = [];
        $oturumId = $url && preg_match('/[?&]sohbet=(\d+)/', $url, $mm) ? (int) $mm[1] : 0;
        try {
            // Alicilar: "WhatsApp sohbetlerini gorsun" yetkisi olanlar (sahip her zaman; yonetici/sekreter varsayilan acik,
            // personel rolunde salon sahibi acarsa). Yetki okunamazsa eski kural: rol < 5.
            $yoneticiler = [];
            foreach (\App\Personeller::where('salon_id', $salonId)->whereNotNull('yetkili_id')->get(['id', 'personel_adi', 'yetkili_id']) as $p) {
                try {
                    $izin = \App\Services\PersonelYetkiServisi::yetkiliYetkiVar((int) $p->yetkili_id, $salonId, 'whatsapp.sohbet_gor');
                } catch (\Throwable $e) {
                    $izin = (int) DB::table('model_has_roles')->where('model_id', $p->yetkili_id)->min('role_id') < 5;
                }
                if ($izin) $yoneticiler[(int) $p->id] = $p->personel_adi;
            }
            foreach ($yoneticiler as $pid => $ad) {
                // Mobil: bildirime tiklayinca WhatsApp Asistani ekrani (varsa ilgili sohbet) acilir
                $r = NotificationService::toStaff((int) $pid, $salonId)->type(NotificationTypes::WA_ASISTAN_TALEP)
                    ->title('WhatsApp Asistanı')->body($mesaj)->deepLink('wa_assistant', ['salon_id' => $salonId, 'oturum_id' => $oturumId])->send();
                // Gercekten ulasti mi: kayitli cihazina (uygulama / tarayici bildirimi) en az bir push gitti mi
                if (trim((string) $ad) !== '') $adlar[] = ['ad' => trim((string) $ad), 'ulasti' => (int) ($r['sent'] ?? 0) > 0, 'pid' => (int) $pid, 'hata' => $r['hatalar'][0] ?? null];
            }
        } catch (\Throwable $e) {
            Log::warning('[WA-ASISTAN] push gonderilemedi: ' . $e->getMessage());
        }
        return $adlar;
    }

    /**
     * GUVENLIK AGI (5 dk'da bir, wa:talep-takip): havada kalan sohbeti asistan tamamlar.
     *  - Son mesaj musterinin ve 3+ dk cevapsiz (isleme hatasi / zaman asimi)  -> o mesaja yeniden cevap.
     *  - Son mesaj asistanin "bir bakayım / not aldım" sozu (soru/secenek yok)   -> kontrolu yaptirip sonucu yazar.
     * Personel insan_esik_dk icinde yazdiysa, sohbet susturulduysa, paket bittiyse ya da 60 dk'yi gectiyse dokunulmaz. Her durum bir kez denenir.
     *
     * $insanSustu = true (her dakika, wa:insan-devam): personel yazdi, musteri SONRA yazdi ve personel insan_esik_dk (5) boyunca
     * cevap vermedi -> asistan musteriye hemen doner (musteri beklemez). 5 dk yasi beklenmez.
     */
    public static function havadaKalanlariTamamla(bool $insanSustu = false): int
    {
        if (!self::semaGaranti()) return 0;
        $n = 0;
        $esik = max(1, (int) self::ayar('insan_esik_dk', 5));
        $adaylar = $insanSustu
            ? DB::table('wa_asistan_oturumlari')->where('durum', 'acik')->where('insan_devraldi', 1)
                ->whereBetween('son_insan_at', [now()->subMinutes(60), now()->subMinutes($esik)])
                ->whereColumn('son_mesaj_at', '>', 'son_insan_at')->orderBy('id')->limit(50)->get()
            : DB::table('wa_asistan_oturumlari')->where('durum', 'acik')
                ->whereBetween('son_mesaj_at', [now()->subMinutes(60), now()->subMinutes(5)])->orderBy('id')->limit(50)->get();
        foreach ($adaylar as $o) {
            try {
                if (!self::acikMi((int) $o->salon_id) || WaPaket::kapali((int) $o->salon_id)) continue;
                if ($o->sessiz_bitis && strtotime($o->sessiz_bitis) > time() && $o->sessiz_neden !== 'aktar') continue;
                if ($o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - $esik * 60) continue;
                if ($insanSustu) {
                    // personel sustu: sohbet asistana doner (sonraki musteri mesajlari da normal cevaplanir)
                    DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update(['insan_devraldi' => 0, 'updated_at' => now()]);
                }
                $g = json_decode($o->gecmis ?: '[]', true) ?: [];
                $son = end($g);
                // yarim: cevapsiz / sozde / tekrar (dongu) / sikayet ("boş mesaj, gelmedi"); kapanis mesajlari ("tamam, 👍") haric
                $mod = self::yarimMi($g);
                // Asistan soru/onay sordu, musteri 5 dk'dir sessiz -> bir kez nazikce hatirlatir (gece yazmaz)
                if (!$mod && self::cevapBekliyorMu($g)) {
                    $h = (int) date('G');
                    if ($h < 9 || $h >= 22) continue;
                    $mod = 'hatirlat';
                }
                if (!$mod) continue;
                $rol = $son['role'] ?? '';
                // Bilerek cevapsiz birakilanlar: gunluk tavan dolu hat
                if ((int) self::ayar('gunluk_tavan', 0) > 0 && Cache::get("wa_asistan_tavan_{$o->salon_id}_" . date('Ymd'))) continue;
                // isleme hala suruyor olabilir (mesaj birlestirme / AI cagrisi): islenmemis gelen kayit varsa bekle
                if ($rol === 'user' && DB::table('wa_asistan_gelen')->where('salon_id', $o->salon_id)->where('anahtar', $o->anahtar)
                    ->where('created_at', '>=', $insanSustu ? now()->subSeconds(30) : now()->subMinutes(2))->exists()) continue;
                if (!Cache::add('wa_havada_' . $o->id . '_' . md5(json_encode($son)), 1, 120)) continue;
                if (self::tamamla($o, $g, $mod)) $n++;
            } catch (\Throwable $e) {
                Log::warning('[WA-HAVADA] tamamlanamadi: ' . $e->getMessage(), ['oturum' => $o->id]);
            }
        }
        return $n;
    }

    /**
     * "Ben Leyla Ahmet değilim, yanlış numara" / "numaramı nereden buldunuz" / "bana mesaj atmayın" (saf).
     * "ben müsait değilim" gibi cumleler yakalanmaz: "ben <Ad> değilim" icin ad buyuk harfle ya da kayitli musteri adi olmali.
     */
    public static function yanlisNumaraMi(string $metin, ?string $kayitliAd = null): bool
    {
        $s = mb_strtolower(strtr($metin, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        if (preg_match('/yan[lı]{1,2}[sş] ?numara|numaram[ıi]? ner(e)?den|atmay[ıi]n bana mesaj|numaram[ıi] (kim|nas[ıi]l) (verdi|buldu)|bana (bir daha |art[ıi]k )?mesaj (atmay[ıi]n|atma|g[öo]ndermeyin)|mesaj atmay[ıi]n|rahats[ıi]z etmeyin|numaram[ıi] silin|listeden [çc][ıi]kar/u', $s)) return true;
        if (preg_match('/\b[Bb]en\s+([A-ZÇĞİÖŞÜ][\p{L}]+(\s+[A-ZÇĞİÖŞÜ][\p{L}]+){0,2})\s+de[ğg]il(im)?\b/u', $metin)) return true;
        if ($kayitliAd) {
            $ilk = mb_strtolower(strtok(trim($kayitliAd), ' ') ?: '', 'UTF-8');
            if ($ilk !== '' && mb_strlen($ilk) >= 3 && preg_match('/\bben\s+' . preg_quote($ilk, '/') . '[\p{L}\s]*de[ğg]il/u', $s)) return true;
        }
        return false;
    }

    /**
     * Sohbet yarim mi (saf; guvenlik agi + panel "Asistan tamamlasın"):
     *  'cevapsiz' = son mesaj cevapsiz musteri mesaji; 'sozde' = asistan "bir bakayım" deyip birakti;
     *  'tekrar'   = asistan ayni cevabi tekrarladi (dongu: istek karsilanmadi);
     *  'sikayet'  = musteri son mesajlarinda "boş mesaj / gelmedi / acil / hâlâ" dedi ama asistan cozemedi.
     * Bir kez gozden gecirilmis (gozden=1 asistan mesaji) noktadan oncesine bakilmaz: ayni sohbet tekrar tekrar yazilmaz.
     */
    public static function yarimMi(array $g): ?string
    {
        $son = end($g);
        if (!$son || !empty($son['personel']) || !empty($son['gozden'])) return null;
        if (($son['role'] ?? '') === 'user') return self::kapanisMi((string) ($son['content'] ?? '')) ? null : 'cevapsiz';
        if (RandevuAsistan::sozVeripBirakti((string) ($son['content'] ?? ''))) return 'sozde';
        // son gozden gecirmeden sonraki kisim: son 4 asistan + son 2 musteri mesaji
        $asistan = []; $musteri = [];
        for ($i = count($g) - 1; $i >= 0; $i--) {
            $m = $g[$i];
            if (!empty($m['gozden']) || !empty($m['personel'])) break;
            $c = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) ($m['content'] ?? ''))), 'UTF-8');
            if (($m['role'] ?? '') === 'assistant' && count($asistan) < 4) $asistan[] = $c;
            if (($m['role'] ?? '') === 'user' && count($musteri) < 2) $musteri[] = $c;
        }
        if (count($asistan) >= 2 && count(array_unique($asistan)) < count($asistan)) return 'tekrar';
        foreach ($musteri as $c) {
            if (preg_match('/bo[şs] (mesaj|geldi|gitti)|gelmedi|gitmedi|ula[şs]mad[ıi]|h[âa]l[âa]|acil|cevap ver|anlam[ıi]yor|sa[çc]ma|olmad[ıi]|yine ayn[ıi]|ka[çc] kere/u', $c)) return 'sikayet';
        }
        return null;
    }

    /**
     * Son mesaj asistanin musteriden cevap bekleyen sorusu mu (onay / saat secimi / bilgi) (saf).
     * "Başka bir konuda yardımcı olabilir miyim?" gibi kapanis sorulari ve zaten bir kez hatirlatilmis mesajlar haric.
     */
    public static function cevapBekliyorMu(array $g): bool
    {
        $son = end($g);
        if (!$son || ($son['role'] ?? '') !== 'assistant' || !empty($son['personel']) || !empty($son['gozden'])) return false;
        $c = trim((string) ($son['content'] ?? ''));
        // Islem zaten tamamlandi (randevu olustu / iptal / guncellendi): kapanis mesajidir, hatirlatma yok
        // (canli 2026-11-19 Ebrar: olusan randevudan sonra "hâlâ bekliyor, oluşturalım mı?" yazildi)
        if (preg_match('/olu[şs]turdum|olu[şs]turuldu|olu[şs]turulmu[şs]|kaydettim|kaydedildi|iptal ettim|iptal edildi|g[üu]ncelledim|g[üu]ncellendi|ta[şs][ıi]nd[ıi]|ta[şs][ıi]d[ıi]m/iu', $c)) return false;
        if (!preg_match_all('/[^.!?\n]*\?/u', $c, $m)) return false;
        $soru = mb_strtolower(trim(end($m[0])), 'UTF-8');
        if (preg_match('/ba[şs]ka (bir )?(konu|[şs]ey|iste|sorunuz|yard)|yard[ıi]mc[ıi] ol(abil|ay[ıi]m|mam)|sorunuz var m[ıi]|memnun (kald|musunuz)|yeterli mi/u', $soru)) return false;
        return true;
    }

    /** Panelden "Asistan tamamlasın": yarim kalan sohbeti asistan simdi surdurur. @return ['ok', 'hata'?] */
    public static function devamEttir(int $oturumId): array
    {
        $o = DB::table('wa_asistan_oturumlari')->where('id', $oturumId)->first();
        if (!$o) return ['ok' => 0, 'hata' => 'Sohbet bulunamadı.'];
        if (WaPaket::kapali((int) $o->salon_id)) return ['ok' => 0, 'hata' => 'Paket doldu; asistan bu ay cevap vermiyor.'];
        $g = json_decode($o->gecmis ?: '[]', true) ?: [];
        $durum = self::yarimMi($g);
        if (!$durum) return ['ok' => 0, 'hata' => 'Bu sohbette yarım kalan bir şey yok; son mesaj asistanın sonuçlu cevabı.'];
        // Personel devraldiysa: devam isteyen isletme -> asistana geri verilir
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update(['insan_devraldi' => 0, 'sessiz_bitis' => null, 'sessiz_neden' => null, 'durum' => 'acik', 'updated_at' => now()]);
        $o = DB::table('wa_asistan_oturumlari')->where('id', $o->id)->first();
        return self::tamamla($o, $g, $durum) ? ['ok' => 1] : ['ok' => 0, 'hata' => 'Mesaj gönderilemedi (WhatsApp bağlantısını kontrol edin).'];
    }

    /** Kisa onay / tesekkur / veda / emoji: cevap beklemeyen mesaj (saf). */
    public static function kapanisMi(string $m): bool
    {
        if (preg_match('/^(\[Çıkartma\]\s*)+$/u', trim($m))) return true;
        $s = strtr(self::sade($m), ['ç' => 'c', 'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ı' => 'i']);
        if ($s === '') return (bool) preg_match('/^[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\s]+$/u', trim($m));
        return (bool) preg_match('/^((tamam|tamamdir|ok|okey|peki|anladim|anlasildi|tmm|olur|super|harika|tesekkur\w*|tsk\w*|sagol\w*|sag ol\w*|eyvallah|iyi gunler|iyi aksamlar|iyi geceler|gorusuruz|kolay gelsin|rica ederim|size de|sizlere de|ederim|ederiz|cok)\s*)+$/u', $s);
    }

    /** @param string $mod cevapsiz | sozde | tekrar | sikayet (yarimMi) */
    protected static function tamamla($o, array $g, string $mod): bool
    {
        $sozde = $mod === 'sozde';
        $ayar = WaAsistanVeri::ayarlar((int) $o->isletme_id);
        $as = new RandevuAsistan((int) $o->isletme_id, $o->musteri_tel ?: $o->telefon, 'whatsapp');
        $as->waFiyatSoyle = !empty($ayar['fiyat_soyle']);
        $as->waProfilAdiAyarla($o->ad);
        try { $as->waDersleriAyarla(WaDenetci::aktifDersler((int) $o->isletme_id)); } catch (\Throwable $e) {}
        if ($sozde) {
            $metin = '[SİSTEM — müşteri bunu yazmadı] Son mesajında yapacağını söylediğin kontrolü yapmadın ve müşteri bekliyor. '
                . 'Gereken aracı (ör. musait_saatler) ŞİMDİ çağır ve sonucu müşteriye net bir seçenek ya da soruyla yaz. Özür dileme, doğrudan sonucu yaz.';
            $once = $g;
        } elseif ($mod === 'hatirlat') {
            // Musteri soruya 5 dk'dir cevap vermedi: tek kisa hatirlatma (yeni islem yok)
            $metin = '[SİSTEM — müşteri bunu yazmadı] Müşteri son sorunuza birkaç dakikadır cevap vermedi. '
                . 'Tek, kısa ve nazik bir hatırlatma mesajı yaz: bekleyen soruyu/onayı seçenekleriyle (gün, saat, uzman) koruyarak yeniden sor; '
                . 'müşteri kısa cevapla (ör. "evet" ya da saat) tamamlayabilsin. Araç çağırma, yeni işlem yapma, önceki mesajı kelimesi kelimesine tekrarlama. '
                . 'Yazışmada randevu zaten oluşturulduysa / işlem tamamlandıysa onu yeniden oluşturmayı SORMA, "bekliyor" DEME.';
            $once = $g;
        } elseif ($mod === 'tekrar' || $mod === 'sikayet') {
            // Yazisma takildi (ayni cevap tekrarlandi / musteri "boş mesaj, gelmedi" dedi): bastan oku, asil istegi coz
            $metin = '[SİSTEM — müşteri bunu yazmadı] Yazışmayı baştan oku. Müşterinin asıl isteği karşılanmadı: '
                . ($mod === 'tekrar' ? 'aynı cevap tekrar tekrar gönderildi. ' : 'müşteri bir şeyin olmadığını / gelmediğini söyledi. ')
                . 'Müşterinin asıl isteğini belirle ve ŞİMDİ çöz; gerekiyorsa ilgili aracı yeniden çağır (ör. yol_tarifi_gonder, musait_saatler). '
                . 'Önceki cevabını tekrarlama. Kısaca özür dile ve sonucu doğrudan yaz; çözemiyorsan aktar.';
            $once = $g;
        } else {
            $sonUser = array_pop($g);
            $metin = (string) ($sonUser['content'] ?? '');
            $once = $g;
        }
        $d = $as->konus($metin, $once);
        $cevap = trim((string) ($d['cevap'] ?? ''));
        $aksiyon = $d['aksiyon'] ?? null;
        if ($aksiyon === 'aktar') {
            $konu = WaTalep::konuBul($metin, $cevap);
            $t = WaTalep::ac($o, $konu, $sozde ? 'Asistan sözünü tamamlayamadı' : $metin);
            $cevap = WaTalep::iletildiMetni($konu, !$t['yeni']);
        }
        $cevap = self::telefonIfadeleri($cevap);
        // Hatirlatma uretilemediyse yazilmaz (musteri zaten soruyu gordu; isletmeye talep acmaya gerek yok)
        if ($mod === 'hatirlat' && $aksiyon !== 'aktar' && ($cevap === '' || RandevuAsistan::sozVeripBirakti($cevap))) return false;
        if ($cevap === '' || RandevuAsistan::sozVeripBirakti($cevap)) {
            // Ikinci kez de sonuc yok: musteri bekletilmez, isletmeye sorulur
            $t = WaTalep::ac($o, 'diger', 'Asistan müşterinin isteğini sonuçlandıramadı: "' . mb_substr($metin, 0, 160) . '"');
            $cevap = WaTalep::iletildiMetni('diger', !$t['yeni']);
        }
        $salon = Salonlar::find($o->salon_id);
        $hedef = $o->jid ?: $o->telefon;
        if (!$salon || !$hedef) return false;
        $r = app(WhatsAppService::class)->sendAsistanCevap($salon, $hedef, $cevap, $o->telefon);
        if (empty($r['ok'])) return false;
        $taze = DB::table('wa_asistan_oturumlari')->where('id', $o->id)->first();
        $g2 = json_decode($taze->gecmis ?: '[]', true) ?: [];
        $msj = ['role' => 'assistant', 'content' => $cevap, 'at' => date('Y-m-d H:i:s'), 'gozden' => 1]; // bir kez gozden gecirildi: tekrar tetiklenmez
        if (!empty($d['baglam'])) $msj['baglam'] = $d['baglam'];
        $g2[] = $msj;
        $upd = ['gecmis' => json_encode(array_slice($g2, -40), JSON_UNESCAPED_UNICODE), 'cevap_sayisi' => DB::raw('cevap_sayisi + 1'), 'updated_at' => now()];
        if (in_array($aksiyon, ['randevu', 'iptal', 'guncelle', 'teyit', 'odeme'], true)) $upd['sonuc'] = $aksiyon;
        if (!empty($d['veri']['randevu_id'])) $upd['randevu_id'] = (int) $d['veri']['randevu_id'];
        if ($as->yeniTelefon) $upd['musteri_tel'] = $as->yeniTelefon;
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update($upd);
        $k = $as->kullanim; $usd = 0.0;
        if (($k['cagri'] ?? 0) > 0) {
            $usd = SantralPaket::claudeMaliyetUsd($k);
            AiKullanimLog::yaz((int) $o->isletme_id, 'wa_asistan', (int) $k['girdi'], (int) $k['cikti'],
                (string) (config('services.anthropic.santral_model') ?: 'claude-haiku-5-5'), false, true, $usd);
        }
        WaAsistanVeri::cevapKaydet(['salon_id' => (int) $o->salon_id, 'isletme_id' => (int) $o->isletme_id, 'oturum_id' => (int) $o->id,
            'kaynak' => ($k['cagri'] ?? 0) > 0 ? 'ai' : 'kod', 'ai' => ($k['cagri'] ?? 0) > 0, 'aksiyon' => $aksiyon,
            'randevu_id' => !empty($d['veri']['randevu_id']) ? (int) $d['veri']['randevu_id'] : null, 'maliyet_usd' => $usd]);
        Log::info('[WA-HAVADA] sohbet tamamlandi', ['oturum' => $o->id, 'sozde' => $sozde, 'cevap' => $cevap, 'aksiyon' => $aksiyon]);
        return true;
    }

    /**
     * Asistanin sohbete kendiliginden yazmasi (talep takibi: "isletmemiz henuz donemedi"). Personel devraldiysa yazmaz.
     * Gecmise eklenir ve cevap olcumune (kaynak) yazilir; AI degil, paketten dusmez.
     */
    public static function oturumaYaz(int $oturumId, string $metin, string $kaynak = 'takip', bool $personelIstegi = false, ?string $yazan = null): bool
    {
        $o = DB::table('wa_asistan_oturumlari')->where('id', $oturumId)->first();
        if (!$o || trim($metin) === '') return false;
        $devral = ($o->sessiz_neden === 'devral' && $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time())
            || ($o->insan_devraldi && $o->son_insan_at && strtotime($o->son_insan_at) > time() - max(1, (int) self::ayar('insan_esik_dk', 5)) * 60);
        $sessiz = $o->sessiz_bitis && strtotime($o->sessiz_bitis) > time() && $o->sessiz_neden !== 'aktar'; // yanlis numara / hiz siniri vb.
        // Personel panelden gonderdiyse (yol tarifi vb.) devral/sessizlik engellemez
        if (!$personelIstegi && ($devral || $sessiz || WaPaket::kapali((int) $o->salon_id))) return false;
        $salon = Salonlar::find($o->salon_id);
        $hedef = $o->jid ?: $o->telefon;
        if (!$salon || !$hedef) return false;
        $r = app(WhatsAppService::class)->sendAsistanCevap($salon, $hedef, $metin, $o->telefon);
        if (empty($r['ok'])) return false;
        $g = json_decode($o->gecmis ?: '[]', true) ?: [];
        $g[] = ['role' => 'assistant', 'content' => $metin, 'at' => date('Y-m-d H:i:s')] + ($personelIstegi ? ['personel' => 1] : []) + ($yazan ? ['yazan' => $yazan] : []);
        DB::table('wa_asistan_oturumlari')->where('id', $o->id)->update(['gecmis' => json_encode(array_slice($g, -40), JSON_UNESCAPED_UNICODE),
            'cevap_sayisi' => DB::raw('cevap_sayisi + 1'), 'updated_at' => now()]);
        try {
            WaAsistanVeri::cevapKaydet(['salon_id' => (int) $o->salon_id, 'isletme_id' => (int) $o->isletme_id, 'oturum_id' => (int) $o->id, 'kaynak' => $kaynak, 'ai' => false]);
        } catch (\Throwable $e) {}
        return true;
    }

    /** Personel yazinca asistanin sustugu sure (dk). */
    public static function insanEsikDk(): int
    {
        return max(1, (int) self::ayar('insan_esik_dk', 5));
    }

    /**
     * Personel uygulamadan musteriye yazdi (mobil > WhatsApp sohbetleri). Musteriye ISIMSIZ gider (salon numarasi);
     * gecmiste kimin yazdigi ('yazan') tutulur. Personelin kendi telefonundan yazmasiyla (manuel) ayni etki: asistan
     * insan_esik_dk (5) susar; personel sonra cevap vermezse asistan musteriye doner (wa:insan-devam). Bekleyen talep cevaplandi olur.
     * @return array ['ok' => bool, 'hata' => ?string]
     */
    public static function personelYazdi(int $oturumId, string $metin, string $yazan): array
    {
        $metin = mb_substr(trim($metin), 0, 2000);
        if ($metin === '') return ['ok' => false, 'hata' => 'Mesaj boş.'];
        if (!self::oturumaYaz($oturumId, $metin, 'personel', true, $yazan)) return ['ok' => false, 'hata' => 'Mesaj gönderilemedi (WhatsApp bağlantısını kontrol edin).'];
        DB::table('wa_asistan_oturumlari')->where('id', $oturumId)->update(['insan_devraldi' => 1, 'son_insan_at' => now(), 'son_mesaj_at' => now(), 'durum' => 'acik', 'updated_at' => now()]);
        try { WaTalep::cevaplandi($oturumId, $yazan . ' (uygulamadan yazdı)'); } catch (\Throwable $e) {}
        Log::info('[WA-ASISTAN] personel uygulamadan yazdi', ['oturum' => $oturumId, 'yazan' => $yazan]);
        return ['ok' => true, 'hata' => null];
    }
}
