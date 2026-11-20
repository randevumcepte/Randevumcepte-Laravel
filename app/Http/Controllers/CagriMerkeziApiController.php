<?php

namespace App\Http\Controllers;

use App\AramaListesi;
use App\AranacakMusteriler;
use App\SistemYonetim\DakikaHesap;
use Illuminate\Http\Request;

/**
 * Cagri Merkezi - Mobil API koprusu.
 *
 * Mobil uygulama (Flutter) ile web panelin AYNI cagri merkezi is mantigini
 * paylasmasi icin StoreAdminController'i genisletir. Tum cagri merkezi metotlari
 * (arama_kartlarim, arama_baslat, santral_not_ekle, ...) parent'tan aynen miras alinir.
 *
 * TEK FARK kimlik dogrulamadir:
 *  - StoreAdminController web constructor'i 'isletmeyonetim' (session) guard'ini zorunlu kilar.
 *  - Bu sinif parent constructor'ini CAGIRMAZ; yerine 'isletmeyonetim-api' (Passport/Bearer)
 *    guard'ini uygular. Boylece WEB CONSTRUCTOR'INA HIC DOKUNULMAZ, web davranisi bire bir korunur.
 *
 * Miras alinan metotlardaki kimlik cozumleme cmAuthId()/cmUser() (Controller.php) uzerinden
 * yapildigi icin web istegi eski davranisla ayni, mobil istek api guard ile calisir.
 */
class CagriMerkeziApiController extends StoreAdminController
{
    public function __construct()
    {
        // Web constructor'ini bilerek cagirmiyoruz (parent::__construct YOK).
        $this->middleware('auth:isletmeyonetim-api');
    }

    // ------------------------------------------------------------------
    // SANTRAL DAKIKA KAPISI (yalniz mobil; web arama_baslat'a dokunulmaz)
    // Softphone ve Bria (originate) aramalari salonun santral dakikasini harcar;
    // tanimli dakika yoksa (0) ya da bittiyse santral aramasi BASLATILMAZ, uyari
    // doner. Yeni uygulama bunu once dakika-durum ile sorup kendisi telefon
    // moduna (telefon_ile=1) duser; telefon modu santral kullanmadigi icin engellenmez.
    // ------------------------------------------------------------------

    public function arama_baslat(Request $request)
    {
        if (!filter_var($request->input('telefon_ile'), FILTER_VALIDATE_BOOLEAN)) {
            $kayit = AranacakMusteriler::where('id', $request->aranacak_musteri_id)->first();
            $liste = $kayit ? AramaListesi::where('id', $kayit->arama_id)->first() : null;
            if ($liste && ($uyari = DakikaHesap::aramaEngeli($liste->salon_id))) {
                return $this->dakikaBittiCevabi($uyari);
            }
        }
        return parent::arama_baslat($request);
    }

    public function arama_baslat_numara(Request $request)
    {
        if (!filter_var($request->input('telefon_ile'), FILTER_VALIDATE_BOOLEAN)) {
            if ($uyari = DakikaHesap::aramaEngeli($this->mevcutsube($request))) {
                return $this->dakikaBittiCevabi($uyari);
            }
        }
        return parent::arama_baslat_numara($request);
    }

    /** Tus takimi (uygulama ici softphone) acilmadan once: {ok, engel, mesaj}. */
    public function dakika_durum(Request $request)
    {
        $uyari = DakikaHesap::aramaEngeli($this->mevcutsube($request));
        return response()->json(['ok' => true, 'engel' => $uyari !== null, 'mesaj' => $uyari]);
    }

    /**
     * Santral dakika ozeti (dashboard + Cagri Merkezi karti). Web santral sayfasi ile ayni kural:
     *  rol < 5 : salon havuzu (tanimli / kullanilan / kalan)
     *  rol 5   : yalniz kendi dahililerinin giden konusma dakikasi (havuz rakamlari gizli);
     *            dakika bittiyse 'bitti' bayragi yine doner (arama neden engelli anlasilsin).
     * goster=false: santral_aktif != 1 ya da salonda santral hatti da tanimli dakika da yok (kart cizilmez).
     */
    public function dakika_ozet(Request $request)
    {
        $salonId = (int) $this->mevcutsube($request);
        if (!$salonId) return response()->json(['ok' => false, 'goster' => false]);
        // Dakika yalniz santrali aktif isletmede gosterilir (web menusuyle ayni kosul)
        if (!(int) \App\Salonlar::where('id', $salonId)->value('santral_aktif')) {
            return response()->json(['ok' => true, 'goster' => false]);
        }

        $trunk = \App\SabitNumaralar::where('salon_id', $salonId)->value('numara');
        $tanimli = (int) \App\SistemYonetim\SalonDakikaPaketi::where('salon_id', $salonId)->value('tanimli_dakika');
        if (empty($trunk) && $tanimli <= 0) return response()->json(['ok' => true, 'goster' => false]);

        $havuz = DakikaHesap::hesapla($salonId);
        $bitti = !$havuz['hata'] && $havuz['tanimli'] > 0 && $havuz['kalan'] <= 0;
        $rol = (int) $this->kullaniciRolu($salonId, $this->cmAuthId());

        if ($rol < 5) {
            return response()->json(array_merge($havuz, ['ok' => true, 'goster' => true, 'personel' => false, 'bitti' => $bitti]));
        }

        // Personel: kendi dahilileri (Bria + web + uygulama softphone) toplami
        $per = \App\Personeller::where('salon_id', $salonId)->where('yetkili_id', $this->cmAuthId())
            ->first(self::dahiliKolonlari());
        $dahililer = [];
        if ($per) foreach (self::dahiliKolonlari() as $k) if (!empty($per->$k)) $dahililer[] = (string) $per->$k;
        $dahililer = array_values(array_unique($dahililer));
        if (!$dahililer) return response()->json(['ok' => true, 'goster' => false]);

        $kullanilan = 0.0; $adet = 0; $hata = false;
        foreach ($dahililer as $d) {
            $r = DakikaHesap::cdrKullanim($havuz['trunk'], $havuz['sayim_baslangic'], date('Y-m-d'), $d);
            $kullanilan += $r['dakika']; $adet += $r['adet']; $hata = $hata || $r['hata'];
        }
        return response()->json([
            'ok' => true, 'goster' => true, 'personel' => true, 'bitti' => $bitti,
            'kullanilan' => round($kullanilan, 1), 'adet' => $adet, 'hata' => $hata,
            'sayim_baslangic' => $havuz['sayim_baslangic'], 'dahili' => implode(', ', $dahililer),
        ]);
    }

    /** 200 + success=false: uygulama mesaji alt bantta (kirmizi) gosterir. */
    private function dakikaBittiCevabi(string $uyari)
    {
        return response()->json(['success' => false, 'dakika_bitti' => true,
            'message' => $uyari . ' Santral araması başlatılmadı; telefonunuzun kendi arama uygulamasını kullanın.']);
    }
}
