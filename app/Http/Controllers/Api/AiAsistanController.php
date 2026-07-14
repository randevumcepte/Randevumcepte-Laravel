<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Randevular;
use App\RandevuHizmetler;
use App\SalonCalismaSaatleri;
use App\Salonlar;
use App\SalonHizmetler;
use App\User;
use App\MusteriPortfoy;
use App\Personeller;
use App\Helpers\CinsiyetTahmin;
use App\Services\NotificationService;
use App\Services\NotificationTypes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * AI sesli asistan sidecar'ı için API uçları.
 * Tüm uçlar AiSidecarAuth middleware'i ile korunur.
 */
class AiAsistanController extends Controller
{
    /* ─────────────────────────────────────────────────────────────
     * 0. Salon bilgi — sidecar cagri basinda salon adini cekmek icin
     * ───────────────────────────────────────────────────────────── */
    public function salonBilgi(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        if (!$salonId) {
            return response()->json(['ok' => false, 'mesaj' => 'salon_id zorunlu'], 422);
        }
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['ok' => false, 'mesaj' => 'Salon bulunamadı'], 404);
        }

        // Salonun sundugu hizmetler — tablo: salon_sunulan_hizmetler, hizmet adi
        // ve fiyat bilgisi join ile hizmetler tablosundan.
        $hizmetler = DB::table('salon_sunulan_hizmetler as sh')
            ->join('hizmetler as h', 'h.id', '=', 'sh.hizmet_id')
            ->where('sh.salon_id', $salonId)
            ->select(
                'sh.hizmet_id as id',
                'h.hizmet_adi as ad',
                'sh.son_fiyat as fiyat'
            )
            ->orderBy('h.hizmet_adi')
            ->get();

        return response()->json([
            'ok' => true,
            'id' => $salon->id,
            'ad' => $salon->salon_adi,
            'karsilama_telaffuz' => $salon->karsilama_telaffuz,
            'adres' => $salon->adres,
            'telefon' => $salon->telefon_1 ?: $salon->telefon_2 ?: $salon->telefon_3,
            'aciklama' => $salon->aciklama,
            'randevu_araligi_dk' => $salon->randevu_saat_araligi ?? 30,
            'hizmetler' => $hizmetler,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 0b. Müşteri bilgi — arayan numaradan müşteriyi tanı (kişiselleştirme +
     *     paket bilgisi). Selamlarken adıyla hitap etmek ve "paketinizden
     *     düşelim mi?" diyebilmek için çağrı başında çağrılır.
     * ───────────────────────────────────────────────────────────── */
    public function musteriBilgi(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $telefon = self::telefonNormalize($request->input('telefon'));

        if (!$telefon) {
            return response()->json(['ok' => true, 'ad' => null, 'paketler' => []]);
        }

        $user = User::where('cep_telefon', $telefon)->first();
        if (!$user) {
            return response()->json(['ok' => true, 'ad' => null, 'paketler' => []]);
        }

        // NOT (paket bilgisi): adisyon_paket_seanslar her satırı TEK seans tutuyor
        // (adisyon_paket_id + geldi/dusulen_miktar üzerinden). Doğru "kalan seans"
        // sayımı paket-satış şemasının dikkatli incelenmesini gerektiriyor; yanlış
        // sayı söylemek müşteriyi yanıltır. Bu yüzden şimdilik boş dönüyoruz ve
        // ayrı bir adımda doğru sorgu ile dolduracağız. Kişiselleştirme (isim)
        // bu turda aktif.
        $paketler = [];

        return response()->json([
            'ok' => true,
            'ad' => $user->name ?: null,
            'cinsiyet' => $user->cinsiyet,
            'paketler' => $paketler,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 1. Müsait saatleri getir
     * ───────────────────────────────────────────────────────────── */
    public function musaitSaatler(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $tarih = $request->input('tarih'); // YYYY-MM-DD
        // Opsiyonel: musteri "ogleden sonra" gibi bir dilim soylediyse LLM bunu
        // gonderir. Bos ise tum gun dondurulur.
        $zamanDilimi = strtolower(trim((string) $request->input('zaman_dilimi', '')));

        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['ok' => false, 'mesaj' => 'Salon bulunamadı'], 404);
        }
        try {
            $tarihObj = Carbon::createFromFormat('Y-m-d', $tarih);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mesaj' => 'Tarih formatı YYYY-MM-DD olmalı'], 422);
        }

        $haftaninGunu = self::haftaninGunu($tarih);
        $calisma = SalonCalismaSaatleri::where('salon_id', $salonId)
            ->where('haftanin_gunu', $haftaninGunu)
            ->where('calisiyor', 1)
            ->first();

        if (!$calisma) {
            return response()->json([
                'ok' => true,
                'tarih' => $tarih,
                'saatler' => [],
                'mesaj' => 'Salon bu gün kapalı'
            ]);
        }

        $aralikDk = max(15, (int) ($salon->randevu_saat_araligi ?? 30));

        // O gün dolu olan saat dilimleri
        $doluSaatler = RandevuHizmetler::join('randevular', 'randevular.id', '=', 'randevu_hizmetler.randevu_id')
            ->where('randevular.salon_id', $salonId)
            ->where('randevular.tarih', $tarih)
            ->where('randevular.durum', Randevular::ONAYLANDI)
            ->select('randevu_hizmetler.saat', 'randevu_hizmetler.saat_bitis')
            ->get();

        $doluSet = [];
        foreach ($doluSaatler as $r) {
            $start = strtotime($r->saat);
            $end = strtotime($r->saat_bitis ?: $r->saat);
            for ($t = $start; $t < $end; $t += 60) {
                $doluSet[date('H:i', $t)] = true;
            }
        }

        $bugun = Carbon::today()->format('Y-m-d');
        $simdi = Carbon::now()->format('H:i');

        // Zaman dilimi -> saat araligi (HH*60+MM cinsinden dk). LLM "sabah",
        // "ogle", "ogleden_sonra"/"ogleden sonra", "aksam" gonderebilir.
        $dilimAralik = null;
        $z = str_replace([' ', '-', '_'], '', $zamanDilimi); // normalize
        if ($z !== '') {
            if (strpos($z, 'sabah') !== false)                                        $dilimAralik = [0, 12 * 60 - 1];       // < 12:00
            elseif (strpos($z, 'ogleden') !== false || strpos($z, 'oglenden') !== false) $dilimAralik = [12 * 60, 18 * 60 - 1];  // 12:00-18:00
            elseif (strpos($z, 'aksam') !== false || strpos($z, 'gece') !== false)     $dilimAralik = [18 * 60, 24 * 60];      // >= 18:00
            elseif (strpos($z, 'ogle') !== false || strpos($z, 'oglen') !== false)     $dilimAralik = [11 * 60, 14 * 60];      // ogle civari
        }

        $musait = [];
        $start = strtotime($calisma->baslangic_saati);
        $end = strtotime($calisma->bitis_saati);
        for ($t = $start; $t < $end; $t += $aralikDk * 60) {
            $hhmm = date('H:i', $t);
            // Geçmiş saatleri atla (sadece bugün için)
            if ($tarih === $bugun && $hhmm <= $simdi) continue;
            // Dolu mu?
            if (isset($doluSet[$hhmm])) continue;
            // Zaman dilimi filtresi
            if ($dilimAralik) {
                $dk = ((int) date('H', $t)) * 60 + ((int) date('i', $t));
                if ($dk < $dilimAralik[0] || $dk > $dilimAralik[1]) continue;
            }
            $musait[] = $hhmm;
        }

        // ONEMLI: eskiden array_slice(0,12) vardi; bu "ogleden sonra" slotlarini
        // kesip atiyordu (09:00-14:30 gosterip 15:00+ hic donmuyordu). Artik tum
        // gunu donuyoruz; sesli asistan zaten 2 tane onerecek. Cok uzun listeyi
        // (nadiren) 24 ile sinirla ki payload sismesin.
        $saatler = array_slice($musait, 0, 24);

        return response()->json([
            'ok' => true,
            'tarih' => $tarih,
            'saatler' => $saatler,
            'zaman_dilimi' => $zamanDilimi ?: null,
            'aralik_dk' => $aralikDk,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 2. Randevu oluştur
     * ───────────────────────────────────────────────────────────── */
    public function randevuOlustur(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $telefon = self::telefonNormalize($request->input('telefon'));
        $adSoyad = trim((string) $request->input('ad_soyad', ''));
        $tarihSaat = $request->input('tarih_saat'); // ISO 8601: 2026-05-15T14:00:00
        $hizmetId = $request->input('hizmet_id');
        $notlar = $request->input('notlar');

        if (!$salonId || !$telefon || !$tarihSaat) {
            return response()->json(['ok' => false, 'mesaj' => 'salon_id, telefon, tarih_saat zorunlu'], 422);
        }
        $salon = Salonlar::find($salonId);
        if (!$salon) {
            return response()->json(['ok' => false, 'mesaj' => 'Salon bulunamadı'], 404);
        }
        try {
            $dt = Carbon::parse($tarihSaat);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mesaj' => 'tarih_saat ISO 8601 olmalı (örn: 2026-05-15T14:00:00)'], 422);
        }
        $tarih = $dt->format('Y-m-d');
        $saat = $dt->format('H:i:s');

        // Müşteri: telefonla bul, yoksa oluştur
        $user = User::where('cep_telefon', $telefon)->first();
        if (!$user) {
            $user = new User();
            $user->cep_telefon = $telefon;
            $user->name = $adSoyad ?: 'Telefon Müşterisi';
            $user->password = bcrypt(str_random_safe(10));
            // Ad-soyaddan otomatik cinsiyet tahmin
            if ($adSoyad) {
                $tahmin = CinsiyetTahmin::tahmin($adSoyad);
                if ($tahmin !== null) $user->cinsiyet = $tahmin;
            }
            $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $user->save();
        } elseif ($adSoyad && empty($user->name)) {
            $user->name = $adSoyad;
            if ($user->cinsiyet === null) {
                $tahmin = CinsiyetTahmin::tahmin($adSoyad);
                if ($tahmin !== null) $user->cinsiyet = $tahmin;
            }
            $user->save();
        }

        // MusteriPortfoy garantile
        $portfoy = MusteriPortfoy::where('user_id', $user->id)
            ->where('salon_id', $salonId)
            ->first();
        if (!$portfoy) {
            $portfoy = new MusteriPortfoy();
            $portfoy->user_id = $user->id;
            $portfoy->salon_id = $salonId;
            $portfoy->aktif = true;
            $portfoy->ozel_notlar = 'AI sesli asistan üzerinden eklendi';
            $portfoy->save();
        }

        // Çakışma: aynı saat ve salonda aktif randevu var mı?
        $cakisma = Randevular::where('salon_id', $salonId)
            ->where('tarih', $tarih)
            ->where('saat', $saat)
            ->where('durum', Randevular::ONAYLANDI)
            ->exists();
        if ($cakisma) {
            return response()->json([
                'ok' => false,
                'mesaj' => 'Bu saat dolu, başka bir saat seçiniz'
            ], 409);
        }

        // Hizmet süresi (varsa)
        $sureDk = 30;
        if ($hizmetId) {
            $sure = SalonHizmetler::where('salon_id', $salonId)
                ->where('hizmet_id', $hizmetId)
                ->value('sure_dk');
            if ($sure) $sureDk = (int) $sure;
        }
        $saatBitis = Carbon::parse($saat)->addMinutes($sureDk)->format('H:i:s');

        DB::beginTransaction();
        try {
            $randevu = new Randevular();
            $randevu->user_id = $user->id;
            $randevu->salon_id = $salonId;
            $randevu->tarih = $tarih;
            $randevu->saat = $saat;
            $randevu->durum = Randevular::ONAYLANDI;
            $randevu->web = 1; // AI üzerinden gelmiş işareti olarak; istersen yeni bir kolon ekle
            $randevu->personel_notu = $notlar;
            $randevu->save();

            if ($hizmetId) {
                $rh = new RandevuHizmetler();
                $rh->randevu_id = $randevu->id;
                $rh->hizmet_id = $hizmetId;
                $rh->saat = $saat;
                $rh->saat_bitis = $saatBitis;
                $rh->sure_dk = $sureDk;
                $rh->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'mesaj' => 'Randevu kaydedilemedi: ' . $e->getMessage()
            ], 500);
        }

        // Salona bildirim (push + panel popup). Randevu olustu, salon calisani
        // popup'a tiklayinca randevu detayina gider. Asla booking'i bozmasin.
        try {
            $hizmetAdi = null;
            if ($hizmetId) {
                $hizmetAdi = DB::table('hizmetler')->where('id', $hizmetId)->value('hizmet_adi');
            }
            $mesaj = ($user->name ?: 'Telefon müşterisi') . ' için '
                . $tarih . ' saat ' . substr($saat, 0, 5)
                . ($hizmetAdi ? " ({$hizmetAdi})" : '')
                . ' randevusu AI santral üzerinden oluşturuldu.';
            self::salonaBildir($salonId, $randevu->id, $user, $mesaj);
        } catch (\Throwable $e) {
            Log::warning('[AI] salon bildirim hatasi: ' . $e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'randevu_id' => $randevu->id,
            'tarih_saat' => $tarih . 'T' . substr($saat, 0, 5) . ':00',
            'mesaj' => "Randevunuz {$tarih} saat " . substr($saat, 0, 5) . " için oluşturuldu"
        ]);
    }

    /**
     * Salon yoneticilerine yeni AI randevusu bildirimi gonder.
     * RandevuSMSHatirlatma'daki yonetici-hedefleme desenini birebir izler:
     * salon_personelleri JOIN model_has_roles, role_id < 5 = yonetici.
     * NotificationService::send() hem FCM push atar hem bildirimler tablosuna
     * panel satirini yazar (popup).
     */
    private static function salonaBildir($salonId, $randevuId, $user, $mesaj)
    {
        $yoneticiIdleri = Personeller::join('model_has_roles', 'salon_personelleri.yetkili_id', '=', 'model_has_roles.model_id')
            ->where('salon_personelleri.salon_id', $salonId)
            ->where('model_has_roles.role_id', '<', 5)
            ->distinct()
            ->pluck('salon_personelleri.id')
            ->toArray();
        $yoneticiIdleri = array_values(array_unique($yoneticiIdleri));

        foreach ($yoneticiIdleri as $pid) {
            try {
                NotificationService::toStaff((int) $pid, (int) $salonId)
                    ->type(NotificationTypes::APPOINTMENT_CREATED)
                    ->title('Yeni Randevu (AI Santral)')
                    ->body($mesaj)
                    ->popup(true)
                    ->randevu((int) $randevuId)
                    ->image($user->profil_resim ?? null)
                    ->deepLink('appointment_detail', ['randevu_id' => $randevuId])
                    ->send();
            } catch (\Throwable $e) {
                Log::warning('[AI] yonetici push fail', ['salon' => $salonId, 'pid' => $pid, 'err' => $e->getMessage()]);
            }
        }
    }

    /* ─────────────────────────────────────────────────────────────
     * 3. Mevcut randevularım
     * ───────────────────────────────────────────────────────────── */
    public function mevcutRandevular(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $telefon = self::telefonNormalize($request->input('telefon'));

        $user = User::where('cep_telefon', $telefon)->first();
        if (!$user) {
            return response()->json(['ok' => true, 'randevular' => []]);
        }

        $bugun = Carbon::today()->format('Y-m-d');
        $randevular = Randevular::where('user_id', $user->id)
            ->where('salon_id', $salonId)
            ->whereDate('tarih', '>=', $bugun)
            ->where('durum', Randevular::ONAYLANDI)
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get(['id', 'tarih', 'saat']);

        $list = $randevular->map(function ($r) {
            return [
                'id' => $r->id,
                'tarih_saat' => $r->tarih . 'T' . $r->saat,
                'tarih' => $r->tarih,
                'saat' => substr($r->saat, 0, 5),
            ];
        });

        return response()->json(['ok' => true, 'randevular' => $list]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 4. Randevu iptal
     * ───────────────────────────────────────────────────────────── */
    public function randevuIptal(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $randevuId = (int) $request->input('randevu_id');

        $randevu = Randevular::where('id', $randevuId)
            ->where('salon_id', $salonId)
            ->first();
        if (!$randevu) {
            return response()->json(['ok' => false, 'mesaj' => 'Randevu bulunamadı'], 404);
        }
        $randevu->durum = Randevular::IPTAL_EDILDI;
        $randevu->save();

        return response()->json([
            'ok' => true,
            'mesaj' => 'Randevu iptal edildi',
            'randevu_id' => $randevu->id
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 5. Randevu güncelle
     * ───────────────────────────────────────────────────────────── */
    public function randevuGuncelle(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $randevuId = (int) $request->input('randevu_id');
        $yeniTs = $request->input('yeni_tarih_saat');

        $randevu = Randevular::where('id', $randevuId)
            ->where('salon_id', $salonId)
            ->first();
        if (!$randevu) {
            return response()->json(['ok' => false, 'mesaj' => 'Randevu bulunamadı'], 404);
        }
        try {
            $dt = Carbon::parse($yeniTs);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mesaj' => 'yeni_tarih_saat ISO 8601 olmalı'], 422);
        }
        $yeniTarih = $dt->format('Y-m-d');
        $yeniSaat = $dt->format('H:i:s');

        // Çakışma kontrolü (kendisi hariç)
        $cakisma = Randevular::where('salon_id', $salonId)
            ->where('tarih', $yeniTarih)
            ->where('saat', $yeniSaat)
            ->where('durum', Randevular::ONAYLANDI)
            ->where('id', '!=', $randevu->id)
            ->exists();
        if ($cakisma) {
            return response()->json(['ok' => false, 'mesaj' => 'Yeni saat dolu'], 409);
        }

        DB::beginTransaction();
        try {
            $eskiSaat = $randevu->saat;
            $randevu->tarih = $yeniTarih;
            $randevu->saat = $yeniSaat;
            $randevu->save();

            // RandevuHizmetler de güncellensin
            $rhList = RandevuHizmetler::where('randevu_id', $randevu->id)->get();
            foreach ($rhList as $rh) {
                $sure = (int) ($rh->sure_dk ?: 30);
                $rh->saat = $yeniSaat;
                $rh->saat_bitis = Carbon::parse($yeniSaat)->addMinutes($sure)->format('H:i:s');
                $rh->save();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'mesaj' => 'Güncelleme başarısız: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'ok' => true,
            'mesaj' => "Randevu {$yeniTarih} saat " . substr($yeniSaat, 0, 5) . " olarak güncellendi",
            'randevu_id' => $randevu->id,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 6. Hizmet eşleştirme — müşterinin söylediği hizmeti çöz.
     *
     * En kritik düzeltme: LLM'in listeden "ilk yakın olanı" seçmesi
     * ("lazer epilasyon" → "Lazer Epilasyon Kol") yerine, eşleştirme
     * KURALLI biçimde burada yapılır:
     *   - tek net eşleşme  → hizmeti döndür
     *   - genel terim çok hizmete uyuyor (kol/bacak/tüm vücut) → SEÇME,
     *     seçenekleri döndür; asistan müşteriye "hangisi?" diye sorar
     *   - hiç eşleşme yok → en yakın öneriler
     * ───────────────────────────────────────────────────────────── */
    public function hizmetEslestir(Request $request)
    {
        $salonId = (int) $request->input('salon_id');
        $metin = trim((string) $request->input('metin', ''));
        if (!$salonId || $metin === '') {
            return response()->json(['ok' => false, 'mesaj' => 'salon_id ve metin zorunlu'], 422);
        }

        $hizmetler = DB::table('salon_sunulan_hizmetler as sh')
            ->join('hizmetler as h', 'h.id', '=', 'sh.hizmet_id')
            ->where('sh.salon_id', $salonId)
            ->select('sh.hizmet_id as id', 'h.hizmet_adi as ad')
            ->orderBy('h.hizmet_adi')
            ->get();

        if ($hizmetler->isEmpty()) {
            return response()->json(['ok' => true, 'eslesme' => 'yok', 'oneriler' => []]);
        }

        $normMetin = self::normalizeTr($metin);
        $metinKelime = array_values(array_filter(explode(' ', $normMetin), function ($w) {
            return mb_strlen($w) >= 2; // "ve", "de" gibi çok kısa parçaları atma dışında filtre
        }));

        $tamListe = [];      // normSvc === normMetin
        $iceren = [];        // müşterinin tüm kelimeleri hizmet adında geçiyor (genel terim)
        $skorlu = [];        // kelime örtüşmesine göre puanlı fallback

        foreach ($hizmetler as $h) {
            $normSvc = self::normalizeTr($h->ad);
            $svcKelime = array_values(array_filter(explode(' ', $normSvc), function ($w) {
                return $w !== '';
            }));

            if ($normSvc === $normMetin) {
                $tamListe[] = ['id' => (int) $h->id, 'ad' => $h->ad];
                continue;
            }

            // Müşterinin söylediği TÜM kelimeler hizmet adında var mı?
            // ("lazer epilasyon" → "Lazer Epilasyon Kol" ✓)
            $hepsiVar = count($metinKelime) > 0;
            foreach ($metinKelime as $mk) {
                if (!in_array($mk, $svcKelime, true) && mb_strpos($normSvc, $mk) === false) {
                    $hepsiVar = false;
                    break;
                }
            }
            if ($hepsiVar) {
                $iceren[] = ['id' => (int) $h->id, 'ad' => $h->ad, 'ek' => trim(str_ireplace($normMetin, '', $normSvc))];
            }

            // Kelime örtüşme skoru (fallback için)
            $ortak = count(array_intersect($metinKelime, $svcKelime));
            if ($ortak > 0) {
                $skorlu[] = [
                    'id' => (int) $h->id,
                    'ad' => $h->ad,
                    'skor' => $ortak / max(1, count($svcKelime)),
                    'ortak' => $ortak,
                ];
            }
        }

        // 1) Tam eşleşme
        if (count($tamListe) === 1) {
            return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => $tamListe[0]]);
        }
        if (count($tamListe) > 1) {
            return response()->json(['ok' => true, 'eslesme' => 'coklu', 'ortak' => $metin, 'secenekler' => $tamListe]);
        }

        // 2) Genel terim: tüm kelimeler eşleşiyor
        if (count($iceren) === 1) {
            return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => $iceren[0]['id'], 'ad' => $iceren[0]['ad']]]);
        }
        if (count($iceren) > 1) {
            $secenekler = array_map(function ($x) {
                return ['id' => $x['id'], 'ad' => $x['ad']];
            }, $iceren);
            return response()->json(['ok' => true, 'eslesme' => 'coklu', 'ortak' => $metin, 'secenekler' => $secenekler]);
        }

        // 3) Fallback: en yüksek kelime örtüşmesi
        usort($skorlu, function ($a, $b) {
            if ($b['ortak'] === $a['ortak']) return $b['skor'] <=> $a['skor'];
            return $b['ortak'] <=> $a['ortak'];
        });

        // Tek belirgin kazanan (skoru yüksek ve ikinciyle arası açık) → kabul
        if (count($skorlu) >= 1 && $skorlu[0]['skor'] >= 0.6) {
            $ikinciFarkli = !isset($skorlu[1]) || ($skorlu[0]['ortak'] > $skorlu[1]['ortak']);
            if ($ikinciFarkli) {
                return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => $skorlu[0]['id'], 'ad' => $skorlu[0]['ad']]]);
            }
        }

        // Öneri listesi (en yakın 3)
        $oneriler = array_map(function ($x) {
            return ['id' => $x['id'], 'ad' => $x['ad']];
        }, array_slice($skorlu, 0, 3));

        return response()->json(['ok' => true, 'eslesme' => 'yok', 'oneriler' => $oneriler]);
    }

    /* ─────────────────────────────────────────────────────────────
     * 7. Çağrı logu — sidecar çağrı boyunca HER TUR bunu çağırır (anlık).
     *    channel_id ile UPSERT: aynı çağrı için tek satır tutulur, turlar
     *    her seferinde yeniden yazılır. Böylece DONMUŞ (hâlâ açık) çağrıda bile
     *    o ana kadarki döküm DB'de görünür. Teşhis içindir; akışı bozmaz.
     * ───────────────────────────────────────────────────────────── */
    public function cagriLog(Request $request)
    {
        try {
            $turlar = $request->input('turlar', []);
            if (is_string($turlar)) {
                $turlar = json_decode($turlar, true) ?: [];
            }

            $channelId = $request->input('channel_id');
            $veri = [
                'salon_id'       => $request->input('salon_id') ? (int) $request->input('salon_id') : null,
                'caller_telefon' => self::telefonNormalize($request->input('caller_telefon')) ?: null,
                'did'            => $request->input('did'),
                'channel_id'     => $channelId,
                'durum'          => $request->input('durum'),
                'sonuc'          => mb_substr((string) $request->input('sonuc', ''), 0, 500),
                'randevu_id'     => $request->input('randevu_id') ? (int) $request->input('randevu_id') : null,
                'tur_sayisi'     => (int) ($request->input('tur_sayisi') ?? count($turlar)),
                'toplam_sure_sn' => $request->input('toplam_sure_sn') ? (int) $request->input('toplam_sure_sn') : null,
                'stt_ms_toplam'  => $request->input('stt_ms_toplam') ? (int) $request->input('stt_ms_toplam') : null,
                'llm_ms_toplam'  => $request->input('llm_ms_toplam') ? (int) $request->input('llm_ms_toplam') : null,
                'tts_ms_toplam'  => $request->input('tts_ms_toplam') ? (int) $request->input('tts_ms_toplam') : null,
                'updated_at'     => now(),
            ];

            // Aynı channel için satır var mı? (anlık upsert)
            $mevcut = $channelId
                ? DB::table('ai_cagri_loglari')->where('channel_id', $channelId)->orderByDesc('id')->first()
                : null;

            if ($mevcut) {
                DB::table('ai_cagri_loglari')->where('id', $mevcut->id)->update($veri);
                $logId = $mevcut->id;
                // Turları tazele (silip yeniden yaz — her POST tam durumu taşır)
                DB::table('ai_cagri_turlari')->where('cagri_log_id', $logId)->delete();
            } else {
                $veri['created_at'] = now();
                $logId = DB::table('ai_cagri_loglari')->insertGetId($veri);
            }

            $rows = [];
            foreach ($turlar as $i => $t) {
                $rows[] = [
                    'cagri_log_id'    => $logId,
                    'tur_no'          => (int) ($t['tur_no'] ?? ($i + 1)),
                    'kullanici_metni' => isset($t['kullanici_metni']) ? mb_substr((string) $t['kullanici_metni'], 0, 2000) : null,
                    'asistan_metni'   => isset($t['asistan_metni']) ? mb_substr((string) $t['asistan_metni'], 0, 2000) : null,
                    'tool_cagrilari'  => isset($t['tool_cagrilari'])
                        ? (is_string($t['tool_cagrilari']) ? $t['tool_cagrilari'] : json_encode($t['tool_cagrilari'], JSON_UNESCAPED_UNICODE))
                        : null,
                    'stt_ms'          => isset($t['stt_ms']) ? (int) $t['stt_ms'] : null,
                    'llm_ms'          => isset($t['llm_ms']) ? (int) $t['llm_ms'] : null,
                    'tts_ms'          => isset($t['tts_ms']) ? (int) $t['tts_ms'] : null,
                    'created_at'      => now(),
                ];
            }
            if ($rows) {
                DB::table('ai_cagri_turlari')->insert($rows);
            }

            return response()->json(['ok' => true, 'log_id' => $logId]);
        } catch (\Throwable $e) {
            Log::warning('[AI] cagri log yazilamadi: ' . $e->getMessage());
            return response()->json(['ok' => false, 'mesaj' => $e->getMessage()]);
        }
    }

    /* ─────────────────────────────────────────────────────────────
     * Helpers
     * ───────────────────────────────────────────────────────────── */

    /**
     * Türkçe metni eşleştirme için normalize et: küçült, aksanları sadeleştir,
     * noktalama/fazla boşlukları temizle. ("Lazer Epilasyon (Kol)" → "lazer epilasyon kol")
     */
    private static function normalizeTr($s)
    {
        $s = (string) $s;
        // Türkçe küçültme
        $s = str_replace(['İ', 'I', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['i', 'i', 's', 'g', 'u', 'o', 'c'], $s);
        $s = mb_strtolower($s, 'UTF-8');
        // Aksanlı harfleri sadeleştir
        $s = str_replace(['ı', 'ş', 'ğ', 'ü', 'ö', 'ç', 'â', 'î', 'û'], ['i', 's', 'g', 'u', 'o', 'c', 'a', 'i', 'u'], $s);
        // Alfa-numerik ve boşluk dışını boşluğa çevir
        $s = preg_replace('/[^a-z0-9 ]+/u', ' ', $s);
        // Fazla boşlukları tekille
        $s = trim(preg_replace('/\s+/', ' ', $s));
        return $s;
    }

    private static function haftaninGunu($tarih)
    {
        $g = date('D', strtotime($tarih));
        $map = ['Mon'=>1,'Tue'=>2,'Wed'=>3,'Thu'=>4,'Fri'=>5,'Sat'=>6,'Sun'=>7];
        return $map[$g] ?? 0;
    }

    /**
     * Telefonu normalize et: rakam dışını sil, +90/0 baş varyantlarını standardize et.
     */
    private static function telefonNormalize($t)
    {
        $t = preg_replace('/[^0-9]/', '', (string) $t);
        if (strlen($t) === 12 && substr($t, 0, 2) === '90') {
            $t = substr($t, 2);
        }
        if (strlen($t) === 11 && substr($t, 0, 1) === '0') {
            $t = substr($t, 1);
        }
        return $t; // 5xxxxxxxxx
    }
}

if (!function_exists('str_random_safe')) {
    function str_random_safe($len = 10)
    {
        return substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, $len);
    }
}
