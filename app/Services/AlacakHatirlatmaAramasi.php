<?php

namespace App\Services;

use App\Alacaklar;
use App\MusteriPortfoy;
use App\SalonEAsistanAyarlari;
use App\Jobs\HatirlatmaAramaJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Alacak (borc) hatirlatma aramalari. `alacaksms:hatirlat` icinden her dakika cagrilir
 * (eskiden ayri `alacakhatirlatma:aramayap` komutu her dakika alacaklar tablosunu
 * `LIKE` ile tariyordu -> indeks kullanilamiyor, her dakika full scan).
 *
 *  - ILK ARAMA: odeme tarihinden 2 gun once, 12:00'den sonra. Pencere icinde her
 *    ceyrek saatin ilk turunda (12:00, 12:15, ...) aranmamis alacaklar taranir; musteri
 *    basina TEK arama, aramalar SIRAYLA kuyruga girer (her biri ARAMA_ARALIGI_SN arayla).
 *    Sonradan vadesi 2 gun sonraya cekilen alacak da bir sonraki ceyrekte yakalanir.
 *    Mukerrer koruma: musteri+odeme tarihi bazinda cache claim.
 *  - TEKRAR ARAMA: tekrar_arama_tarih_saat, son islenen dakikadan (cache) su ana kadarki
 *    araliga dusenler (sargable range; kacan dakika olmaz). Pencere disindaysa 12:00'ye ertelenir.
 */
class AlacakHatirlatmaAramasi
{
    const SON_DAKIKA_ANAHTARI = 'alacak_hatirlatma_aramasi_son_dk';
    /** ayar_id = 1: alacak hatirlatma aramasi acik/kapali. */
    const AYAR_ID_ALACAK = 1;
    /** Alacak aramalari bu saatler arasinda yapilir. */
    const PENCERE_BASI = '12:00';
    const PENCERE_SONU = '19:30';
    /** Siradaki iki arama arasindaki sure (sn) — santrale tek tek, yigilmadan. */
    const ARAMA_ARALIGI_SN = 20;
    /** Kesinti sonrasi en fazla bu kadar geriye bakilir (birikmis aramalar bir anda yagmasin). */
    const EN_FAZLA_GERI_DK = 120;

    public function calistir()
    {
        $liste = array_merge($this->tekrarAramalari(), $this->ilkAramalar());
        if (empty($liste)) {
            return;
        }
        // Sirali kuyruk: her arama ayri job, ARAMA_ARALIGI_SN arayla.
        foreach (array_values($liste) as $i => $parametre) {
            $job = new HatirlatmaAramaJob([$parametre], $parametre['salonId'], null);
            if ($i > 0) {
                $job->delay(now()->addSeconds($i * self::ARAMA_ARALIGI_SN));
            }
            dispatch($job);
        }
        Log::info('[ALACAK-ARAMA] ' . count($liste) . ' musteri sirayla kuyruga eklendi (' . self::ARAMA_ARALIGI_SN . ' sn arayla).');
    }

    protected function pencereIcinde(): bool
    {
        $s = date('H:i');
        return $s >= self::PENCERE_BASI && $s < self::PENCERE_SONU;
    }

    protected function bekleyenFiltre($q)
    {
        return $q->where(function ($q3) {
                $q3->whereNull('hatirlatma_aramasi_yapildi')->orWhere('hatirlatma_aramasi_yapildi', '!=', 1);
            })
            ->where(function ($q3) {
                $q3->whereNull('hatirlatma_gorevi_iptal')->orWhere('hatirlatma_gorevi_iptal', '!=', 1);
            })
            ->where(function ($q3) {
                $q3->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', '!=', 1);
            });
    }

    /** Odeme tarihinden 2 gun once ilk aramalar (12:00 sonrasi, ceyrek saatte bir tarama). */
    protected function ilkAramalar(): array
    {
        if (!$this->pencereIcinde()) {
            return [];
        }
        // Ceyrek saatte bir tarama (dakika kacsa da o ceyregin ilk turunda calisir).
        $ceyrek = date('Y-m-d H') . ':' . intdiv((int) date('i'), 15);
        if (!Cache::add('alacak_ilk_tarama:' . $ceyrek, 1, 30)) {
            return [];
        }

        $odemeTarihi = date('Y-m-d', strtotime('+2 days'));
        $alacaklar = $this->bekleyenFiltre(Alacaklar::where('planlanan_odeme_tarihi', $odemeTarihi))->get();
        if ($alacaklar->isEmpty()) {
            return [];
        }

        $liste = [];
        $ayarCache = [];
        foreach ($this->grupla($alacaklar) as $anahtar => $grup) {
            $parametre = $this->grupParametresi($grup, $ayarCache);
            if ($parametre === null) {
                continue;
            }
            // Musteri + odeme tarihi basina bir kez (3 gun sakla).
            if (!Cache::add('alacak_ilk_arama:' . $anahtar . ':' . $odemeTarihi, 1, 4320)) {
                continue;
            }
            $liste[] = $parametre;
        }
        if ($liste) {
            Log::info('[ALACAK-ARAMA] ilk hatirlatma: ' . count($liste) . ' musteri (odeme ' . $odemeTarihi . ').');
        }
        return $liste;
    }

    /** Ulasilamayan aramalarin tekrari: son islenen dakikadan su ana kadar planlananlar. */
    protected function tekrarAramalari(): array
    {
        $simdi = now();
        $bitis = $simdi->format('Y-m-d H:i:59');
        $enEski = $simdi->copy()->subMinutes(self::EN_FAZLA_GERI_DK)->format('Y-m-d H:i:59');
        $son = Cache::get(self::SON_DAKIKA_ANAHTARI);
        $baslangic = ($son && $son > $enEski) ? $son : $simdi->copy()->subMinute()->format('Y-m-d H:i:59');
        if ($baslangic >= $bitis) {
            return []; // bu dakika zaten islendi
        }

        // Ilk arama Originate edilince Controller hatirlatma_aramasi_yapildi=1 + tekrar_aranacak=1 yazar;
        // musteri acarsa (ulasildi / asistan) tekrar_aranacak=0 olur. Bu yuzden burada
        // hatirlatma_aramasi_yapildi filtresi YOK (eskiden vardi -> tekrar arama hic calismiyordu).
        $alacaklar = Alacaklar::where('tekrar_arama_tarih_saat', '>', $baslangic)
            ->where('tekrar_arama_tarih_saat', '<=', $bitis)
            ->where('tekrar_aranacak', 1)
            ->where(function ($q) {
                $q->whereNull('hatirlatma_gorevi_iptal')->orWhere('hatirlatma_gorevi_iptal', '!=', 1);
            })
            ->where(function ($q) {
                $q->whereNull('tekrar_arandi')->orWhere('tekrar_arandi', '!=', 1);
            })
            ->get()
            // Salon tekrar arama suresi 0 ise tekrar yok (yoksa ilk aramanin hemen ardindan tekrar arardi).
            ->filter(function ($a) {
                return !empty(optional($a->salon)->e_asistan_hatirlatma);
            });

        // Aralik islendi: hata olsa da ayni dakikalari tekrar isleyip mukerrer arama yapmayalim.
        Cache::forever(self::SON_DAKIKA_ANAHTARI, $bitis);

        if ($alacaklar->isEmpty()) {
            return [];
        }

        // Pencere (12:00–19:30) disindaysa: pencere basina ertele.
        if (!$this->pencereIcinde()) {
            $tekrarSaat = (date('H:i') >= self::PENCERE_SONU)
                ? date('Y-m-d', strtotime('+1 days')) . ' ' . self::PENCERE_BASI . ':00'
                : date('Y-m-d') . ' ' . self::PENCERE_BASI . ':00';
            foreach ($alacaklar as $alacak) {
                $alacak->tekrar_arama_tarih_saat = $tekrarSaat;
                $alacak->save();
            }
            Log::info('[ALACAK-ARAMA] saat disinda, ' . $alacaklar->count() . ' alacak ' . $tekrarSaat . '\'e ertelendi.');
            return [];
        }

        $liste = [];
        $ayarCache = [];
        foreach ($this->grupla($alacaklar) as $grup) {
            $parametre = $this->grupParametresi($grup, $ayarCache);
            if ($parametre !== null) {
                $liste[] = $parametre;
            }
        }
        return $liste;
    }

    /** Musteri (salon + user) bazinda grupla — tek aramada tum borclar. */
    protected function grupla($alacaklar): array
    {
        $gruplar = [];
        foreach ($alacaklar as $alacak) {
            $gruplar[$alacak->salon_id . '_' . $alacak->user_id][] = $alacak;
        }
        return $gruplar;
    }

    /**
     * Bir musterinin alacak grubu icin arama parametresi; aranmamasi gerekiyorsa null.
     */
    protected function grupParametresi(array $grup, array &$ayarCache)
    {
        $ilk = $grup[0];
        $salonId = $ilk->salon_id;
        $userId = $ilk->user_id;

        $karaListedeMi = MusteriPortfoy::where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->value('kara_liste');
        if ($karaListedeMi) {
            return null;
        }

        if (!array_key_exists($salonId, $ayarCache)) {
            $ayarCache[$salonId] = SalonEAsistanAyarlari::where('salon_id', $salonId)
                ->where('ayar_id', self::AYAR_ID_ALACAK)
                ->value('acik_kapali');
        }
        if (!$ayarCache[$salonId]) {
            return null;
        }

        if (!$ilk->musteri || !$ilk->musteri->cep_telefon) {
            return null;
        }

        $alacakIdler = [];
        $toplamTutar = 0;
        $tarihSeti = [];
        foreach ($grup as $alacak) {
            $alacakIdler[] = $alacak->id;
            $toplamTutar += (float) $alacak->tutar;
            if ($alacak->planlanan_odeme_tarihi) {
                $tarihSeti[date('d.m.Y', strtotime($alacak->planlanan_odeme_tarihi))] = true;
            }
        }
        $tarihler = implode(', ', array_keys($tarihSeti));
        $salonAdi = $ilk->salon ? $ilk->salon->salon_adi : '';

        $mesaj = 'Sayin ' . $ilk->musteri->name . '. Sizi ' . $salonAdi . ' adina ariyorum. ' .
            $tarihler . ' tarihinde odemeniz gereken toplam ' . $this->tutarMetni($toplamTutar) .
            ' TL borcunuz bulunmaktadir. Odemeyi gerceklestirecekseniz biri, ' .
            'vade guncellemesi icin operatore baglanmak istiyorsaniz ikiyi tuslayiniz.';

        return [
            'alacakIdler' => $alacakIdler,
            'randevuid' => '',
            'kampanyaKatilimci' => '',
            'katilimci' => '',
            'mesaj' => $mesaj,
            'tel' => $ilk->musteri->cep_telefon,
            'salonId' => $salonId,
            // 2 = [alacak-hatirlatma]; yeni asistan acik salonlarda 9 -> Stasis(randevu-santral-ai).
            'exten' => RandevuAsistan::aiHatirlatmaAcik($salonId) ? 9 : 2,
            'aiHedef' => 'alacak:' . implode('-', array_map('intval', (array) $alacakIdler)),
        ];
    }

    protected function tutarMetni($tutar)
    {
        return (floor($tutar) == $tutar)
            ? number_format($tutar, 0, ',', '.')
            : number_format($tutar, 2, ',', '.');
    }
}
