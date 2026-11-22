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
            ->leftJoin('hizmet_kategorisi as hk', 'hk.id', '=', 'h.hizmet_kategori_id')
            ->where('sh.salon_id', $salonId)
            ->where('sh.aktif', 1) // panelden kaldirilan (pasif) hizmetler asistana gitmesin (eski ad/fiyat okunuyordu)
            ->select(
                'sh.hizmet_id as id',
                'h.hizmet_adi as ad',
                // Hizmet fiyati baslangic_fiyat'ta (son_fiyat sistemde kullanilmiyor; Ola Nail 236: "Manikür (E)" fiyatsiz saniliyordu)
                DB::raw('NULLIF(sh.baslangic_fiyat, 0) as fiyat'),
                'sh.sure_dk as sure_dk',  // ek alan (sesli asistan "ne kadar sürer" icin); mevcut tuketicileri etkilemez
                'hk.hizmet_kategorisi_adi as kategori' // ek alan (sesli asistan hizmet listesi: 5 ornek + kategoriler)
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

        // Salon sahibi kürasyonu: online'a AÇIK saatler (haftalık pencere/istisna).
        // null=kısıtlama yok. İzin dışı slotlar sesli asistanda da önerilmez.
        $izinliAraliklar = \App\SalonOnlineRandevuSaatleri::izinliAraliklar($salonId, $tarih);
        if ($izinliAraliklar !== null) {
            $musait = array_values(array_filter($musait, function ($hhmm) use ($izinliAraliklar) {
                return \App\SalonOnlineRandevuSaatleri::slotIzinliMi($izinliAraliklar, $hhmm, 0);
            }));
            $limit = (int) ($salon->online_gunluk_slot_limiti ?? 0);
            if ($limit > 0 && count($musait) > $limit) {
                $bolunmus = \App\SalonOnlineRandevuSaatleri::seyrelt($musait, $limit);
                $musait = $bolunmus['secili'];
            }
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
                ->orderByDesc('aktif') // ayni hizmetin pasif eski satiri varsa aktif olan
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
        // Bildirim tercihleri: telefon_asistani / personel / push kapaliysa gonderilmez
        if (!\App\Services\BildirimTercih::izin($salonId, 'telefon_asistani', 'yonetici', 'push')) return;
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
            ->where('sh.aktif', 1)
            ->select('sh.hizmet_id as id', 'h.hizmet_adi as ad')
            ->orderBy('h.hizmet_adi')
            ->get();

        if ($hizmetler->isEmpty()) {
            return response()->json(['ok' => true, 'eslesme' => 'yok', 'oneriler' => []]);
        }

        $normMetin = self::halkDili(self::normalizeTr($metin));

        // 0) Salonun ogrettigi ifade ("Asistan size soruyor" > hizmet secimi): en uzun eslesen ifade kazanir
        try {
            $ara = ' ' . $normMetin . ' ';
            $esan = DB::table('wa_hizmet_esanlam')->where('salon_id', $salonId)->get(['ifade', 'hizmet_id'])
                ->filter(function ($e) use ($ara) { return $e->ifade !== '' && strpos($ara, ' ' . $e->ifade . ' ') !== false; })
                ->sortByDesc(function ($e) { return strlen($e->ifade); })->first();
            if ($esan && ($h = $hizmetler->firstWhere('id', (int) $esan->hizmet_id))) {
                return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => (int) $h->id, 'ad' => $h->ad], 'kaynak' => 'salon']);
            }
        } catch (\Throwable $e) {} // tablo henuz yok

        // 0b) Musteri hizmetin tam adini yazdiysa dogrudan o (kutuphane bunu degistirmesin)
        $tamAd = $hizmetler->filter(function ($h) use ($normMetin) { return self::halkDili(self::normalizeTr($h->ad)) === $normMetin; })->values();
        if (count($tamAd) === 1) {
            return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => (int) $tamAd[0]->id, 'ad' => $tamAd[0]->ad], 'kaynak' => 'tam_ad']);
        }

        // 0c) Merkezi ifade kutuphanesi (database/ifade_kutuphanesi): halk dili -> kavram -> bu salonun hizmeti.
        // Bulamazsa asagidaki kelime eslestirmesi aynen devam eder (eski davranis yedek).
        $kut = self::kutuphaneSecim($salonId, $metin, $hizmetler);
        if ($kut && $kut['durum'] === 'tek') {
            $h = $hizmetler->firstWhere('id', $kut['hizmetler'][0]);
            if ($h) return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => (int) $h->id, 'ad' => $h->ad], 'kaynak' => 'kutuphane']);
        }
        if ($kut && $kut['durum'] === 'coklu') {
            $sec = $hizmetler->whereIn('id', $kut['hizmetler'])->map(function ($h) { return ['id' => (int) $h->id, 'ad' => $h->ad]; })->values()->all();
            if (count($sec) > 1) {
                return response()->json(['ok' => true, 'eslesme' => 'coklu', 'ortak' => $metin, 'secenekler' => $sec, 'kaynak' => 'kutuphane']
                    + ($kut['soru'] ? ['soru' => $kut['soru'], 'not' => 'Müşterinin isteği bu seçenekler arasında net değil. Seçenekleri tek tek sayma; "soru"yu kendi cümlenle, yalnız bu salondaki seçenekleri anarak sor.'] : []));
            }
        }

        // Once cumle aynen; tam/genel eslesme yoksa is fiilleri atilarak tekrar ("protez takma", "kaş yaptırmak istiyorum").
        // Aynen deneme once: "Kirpik Takma" gibi adinda fiil gecen hizmetler bozulmasin.
        foreach (array_unique([$normMetin, self::fiilAt($normMetin)]) as $normMetin) {
            $metinKelime =array_values(array_filter(explode(' ', $normMetin), function ($w) {
                return mb_strlen($w) >= 2; // "ve", "de" gibi çok kısa parçaları atma dışında filtre
            }));

            $tamListe = [];      // normSvc === normMetin
            $iceren = [];        // müşterinin tüm kelimeleri hizmet adında geçiyor (genel terim)
            $skorlu = [];        // kelime örtüşmesine göre puanlı fallback

            foreach ($hizmetler as $h) {
                $normSvc = self::halkDili(self::normalizeTr($h->ad));
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
                    if (!in_array($mk, $svcKelime, true) && mb_strpos($normSvc, $mk) === false && !self::ekliVar($mk, $svcKelime)) {
                        $hepsiVar = false;
                        break;
                    }
                }
                if ($hepsiVar) {
                    $iceren[] = ['id' => (int) $h->id, 'ad' => $h->ad, 'ek' => trim(str_ireplace($normMetin, '', $normSvc))];
                }

                // Kelime örtüşme skoru (fallback için)
                $ortak = count(array_filter($metinKelime, function ($mk) use ($svcKelime) { return in_array($mk, $svcKelime, true) || self::ekliVar($mk, $svcKelime); }));
                if ($ortak > 0) {
                    $skorlu[] = [
                        'id' => (int) $h->id,
                        'ad' => $h->ad,
                        'skor' => $ortak / max(1, count($svcKelime)),
                        'ortak' => $ortak,
                    ];
                }
            }
            if ($tamListe || $iceren) break;
        }

        // 1) Tam eşleşme
        if (count($tamListe) === 1) {
            return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => $tamListe[0], 'kaynak' => 'kelime']);
        }
        if (count($tamListe) > 1) {
            return response()->json(['ok' => true, 'eslesme' => 'coklu', 'ortak' => $metin, 'secenekler' => $tamListe, 'kaynak' => 'kelime']);
        }

        // 2) Genel terim: tüm kelimeler eşleşiyor
        if (count($iceren) === 1) {
            return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => $iceren[0]['id'], 'ad' => $iceren[0]['ad']], 'kaynak' => 'kelime']);
        }
        if (count($iceren) > 1) {
            $iceren = self::anaHizmetler($iceren, $metinKelime);
            if (count($iceren) === 1) {
                return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => $iceren[0]['id'], 'ad' => $iceren[0]['ad']], 'kaynak' => 'kelime']);
            }
            $secenekler = array_map(function ($x) {
                return ['id' => $x['id'], 'ad' => $x['ad']];
            }, $iceren);
            return response()->json(['ok' => true, 'eslesme' => 'coklu', 'ortak' => $metin, 'secenekler' => $secenekler, 'kaynak' => 'kelime']);
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
                return response()->json(['ok' => true, 'eslesme' => 'tek', 'hizmet' => ['id' => $skorlu[0]['id'], 'ad' => $skorlu[0]['ad']], 'kaynak' => 'kelime']);
            }
        }

        // Öneri listesi (en yakın 3)
        $oneriler = array_map(function ($x) {
            return ['id' => $x['id'], 'ad' => $x['ad']];
        }, array_slice($skorlu, 0, 3));

        $yok = ['ok' => true, 'eslesme' => 'yok', 'oneriler' => $oneriler, 'kaynak' => 'yok'];
        // Kutuphane hizmeti tanidi ama salonda yok ("saç kesimi" tirnak salonunda): asistan benzer hizmet uydurmasin
        if ($kut && $kut['durum'] === 'yok' && $kut['kavramlar'] && count($kut['kavramlar']) <= 3) {
            $kv = \App\Services\IfadeKutuphanesi::yukle()['kavram'];
            $yok['istenen'] = implode(', ', array_map(function ($k) use ($kv) { return $kv[$k]['ad'] ?? $k; }, $kut['kavramlar']));
            $yok['not'] = 'Müşteri "' . $yok['istenen'] . '" istiyor; bu hizmet bu salonun listesinde YOK. Listede gerçekten aynı işi gören bir hizmet varsa onu öner, yoksa bu hizmetin yapılmadığını kibarca söyle. Uydurma.';
        }
        return response()->json($yok);
    }

    /**
     * Merkezi ifade kutuphanesi karari (App\Services\IfadeKutuphanesi::secim). Salon katalogu (hizmet adi -> kavram)
     * hizmet listesi degismedikce 60 dk onbellekte. Hata olursa null (eski eslestirme devam eder).
     */
    protected static function kutuphaneSecim(int $salonId, string $metin, $hizmetler): ?array
    {
        try {
            \App\Services\IfadeOgrenme::hazirla(); // 3+ salonda dogrulanmis ogrenilen ifadeler
            $liste = $hizmetler->pluck('ad', 'id')->all();
            $anahtar = 'ifade_katalog_' . $salonId . '_' . md5(json_encode($liste) . \App\Services\IfadeKutuphanesi::surum());
            $katalog = \Illuminate\Support\Facades\Cache::remember($anahtar, 60, function () use ($liste) { return \App\Services\IfadeKutuphanesi::katalog($liste); });
            $s = \App\Services\IfadeKutuphanesi::secim($metin, $liste, $katalog);
            \Illuminate\Support\Facades\Log::info('[IFADE] hizmet_eslestir', ['salon' => $salonId, 'metin' => mb_substr($metin, 0, 120), 'durum' => $s['durum'], 'hizmetler' => $s['hizmetler'], 'kavramlar' => array_slice($s['kavramlar'], 0, 6)]);
            return $s;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[IFADE] kutuphane hatasi: ' . $e->getMessage());
            return null;
        }
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
    /**
     * Halk dili -> hizmet dili (normalize edilmis metin uzerinde). Canli 2026-11-20: musteri "oje kaldırma" yazdi,
     * hizmet "Kalıcı Oje Çıkarma" bulunamadi. Tirnak baglaminda kaldir/sil/sok/aldir = cikarma; "kas aldirma" gibi
     * baska hizmetlere dokunmaz (yalniz oje/jel/protez/tirnak... geciyorsa).
     */
    /** Is fiilleri / dolgu kelimeler (takma, yaptirma, istiyorum...) metinden atilir; geriye bir sey kalmazsa metin aynen doner. */
    public static function fiilAt(string $norm): string
    {
        $k = array_values(array_filter(explode(' ', $norm), function ($w) {
            return $w !== '' && !preg_match('/^(takma|takmak|takil|taktir|yaptir|yapma|yapmak|yapil|attir|koydur|uygulat|istiyor|isterim|istedim|olsun|lazim|randevu|icin|bir|tane)/', $w);
        }));
        return $k ? implode(' ', $k) : $norm;
    }

    /** Ekli kelime hizmet kelimesine uyuyor mu: "tirnagimi" ~ "tirnak", "kalicimi" ~ "kalici" (ortak kok >= 4 harf). */
    public static function ekliVar(string $mk, array $svcKelime): bool
    {
        if (strlen($mk) < 4) return false;
        foreach ($svcKelime as $sk) {
            if (strlen($sk) < 4) continue;
            $n = 0; $max = min(strlen($mk), strlen($sk));
            while ($n < $max && $mk[$n] === $sk[$n]) $n++;
            if ($n >= 4 && $n >= strlen($sk) - 1) return true; // tirnag|imi ~ tirnak, kalici|mi ~ kalici
        }
        return false;
    }

    /**
     * Coklu eslesmede ikincil islemler (cikarma, dolgu, tamir...) musteri soylemediyse elenir:
     * "protez" -> "Protez Tırnak" (Protez Tırnak Çıkarma / Dolgu degil). Hepsi ikincilse liste aynen kalir.
     */
    public static function anaHizmetler(array $secenekler, array $metinKelime): array
    {
        $ikincil = ['cikarma', 'dolgu', 'tamir', 'onarim', 'sokum'];
        $soyledi = array_intersect($ikincil, $metinKelime);
        $ana = array_values(array_filter($secenekler, function ($s) use ($ikincil, $soyledi) {
            $sk = explode(' ', self::halkDili(self::normalizeTr($s['ad'])));
            foreach ($ikincil as $w) if (!in_array($w, $soyledi, true) && in_array($w, $sk, true)) return false;
            return true;
        }));
        return $ana ?: $secenekler;
    }

    /** wa_hizmet_esanlam anahtari: musterinin ifadesi, hizmetEslestir'deki ayni normalizasyonla. */
    public static function esanlamAnahtar(string $s): string
    {
        return self::halkDili(self::normalizeTr($s));
    }

    public static function halkDili(string $norm): string
    {
        $k = explode(' ', $norm);
        $tirnak = (bool) array_filter($k, function ($w) {
            return (bool) preg_match('/^(oje|kalici|jel|protez|tirnak|kaplama|akrilik|nail|gel|manikur|pedikur|cilali|kroms?|kalicim)/', $w);
        });
        if ($tirnak) {
            $k = array_map(function ($w) {
                return preg_match('/^(kaldir|sil(me|mek|e|)$|silin|sildir|aldir|cikar|cikart|sokme|soktur|sokul|sokum|temizlet)/', $w) ? 'cikarma' : $w;
            }, $k);
        } else {
            // "kaş aldırma", "bıyık aldırmak" -> Kaş Alma / Bıyık Alma
            $k = array_map(function ($w) { return preg_match('/^aldir(ma|mak|ta|)$/', $w) ? 'alma' : $w; }, $k);
        }
        // iyelik/hal ekleri: "ojemi", "ojeyi", "ojeleri" -> oje
        $k = array_map(function ($w) { return preg_match('/^oje(m|mi|yi|ler|leri|lerimi|nin|lerin)$/', $w) ? 'oje' : $w; }, $k);
        return implode(' ', $k);
    }

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
