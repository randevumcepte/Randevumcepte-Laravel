<?php

namespace App\Http\Controllers\SistemYonetim;

use App\Http\Controllers\Controller;
use App\Salonlar;
use App\IsletmeYetkilileri;
use App\SistemYoneticileri;
use App\Personeller;
use App\Randevular;
use App\SistemYonetim\Audit;
use App\SistemYonetim\AuditLog;
use App\SistemYonetim\LoginLog;
use App\SistemYonetim\ImpersonationLog;
use App\SistemYonetim\SalonNotu;
use App\SistemYonetim\DestekTalebi;
use App\SistemYonetim\DestekMesaji;
use App\SatisOrtakligiModel\Musteri_Formlari;
use App\SatisOrtakligiModel\Musteri_Formlari_Hizmetler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PanelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sistemyonetim');
    }

    /* ============================================================
     * Yardimci: rol kontrol
     * ============================================================ */
    private function user()
    {
        return Auth::guard('sistemyonetim')->user();
    }

    private function rol()
    {
        $u = $this->user();
        if (!$u) return null;
        if (!empty($u->rol)) return $u->rol;
        return $u->admin == 1 ? 'super_admin' : 'destek';
    }

    private function yetkiVarMi($izinler)
    {
        $rol = $this->rol();
        if (!is_array($izinler)) $izinler = [$izinler];
        return in_array($rol, $izinler, true);
    }

    private function gerektir($izinler, $msg = 'Bu işlem icin yetkiniz yok.')
    {
        if (!$this->yetkiVarMi($izinler)) {
            abort(403, $msg);
        }
    }

    /* ============================================================
     * DASHBOARD
     * ============================================================ */
    public function dashboard()
    {
        $bugun = date('Y-m-d');
        $bugunBaslangic = date('Y-m-d 00:00:00');
        $haftaOnce = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $haftaTrendBaslangic = date('Y-m-d 00:00:00', strtotime('-6 days'));

        // ───────── METRIKLER (her biri kendi optimize edilmis sorgu, cacheli) ─────────
        $metrikler = \Cache::remember('sy.dashboard.metrikler', 300, function () use ($bugunBaslangic, $haftaOnce) {
            // Salon stats: tek sorgu ile tum kriterleri al
            $salonStats = DB::table('salonlar')->selectRaw("
                COUNT(*) as toplam,
                SUM(CASE WHEN askiya_alindi = 0 THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN askiya_alindi = 1 THEN 1 ELSE 0 END) as askida,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as bugun,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as hafta
            ", [$bugunBaslangic, $haftaOnce])->first();

            // Ticket stats: tek sorgu
            $ticketStats = DB::table('sistemyonetim_destek_talepleri')->selectRaw("
                SUM(CASE WHEN durum IN ('acik','islemde','bekliyor') THEN 1 ELSE 0 END) as acik,
                SUM(CASE WHEN durum IN ('acik','islemde') AND oncelik='acil' THEN 1 ELSE 0 END) as acil
            ")->first();

            // Randevu stats: bu ag table'da pahali, sadece bugun ve toplam
            // toplam'i information_schema yaklasik dan al (full scan kacin)
            $bugunRandevu = (int) DB::table('randevular')->where('created_at', '>=', $bugunBaslangic)->count();
            // toplam_randevu icin information_schema yaklasik (full scan kacin)
            $toplamRandevu = 0;
            try {
                $row = DB::selectOne("SELECT TABLE_ROWS as c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'randevular'");
                if ($row && isset($row->c)) $toplamRandevu = (int) $row->c;
            } catch (\Exception $e) {}

            return [
                'toplam_salon'     => (int) $salonStats->toplam,
                'aktif_salon'      => (int) $salonStats->aktif,
                'askida_salon'     => (int) $salonStats->askida,
                'bugun_yeni_salon' => (int) $salonStats->bugun,
                'hafta_yeni_salon' => (int) $salonStats->hafta,
                'toplam_yetkili'   => (int) DB::table('isletmeyetkilileri')->count(),
                'toplam_personel'  => (int) DB::table('salon_personelleri')->count(),
                'toplam_randevu'   => $toplamRandevu,
                'bugun_randevu'    => $bugunRandevu,
                'acik_ticket'      => (int) ($ticketStats->acik ?? 0),
                'acil_ticket'      => (int) ($ticketStats->acil ?? 0),
                'aktif_ekip'       => (int) DB::table('sistemyoneticileri')->where('aktif', 1)->count(),
            ];
        });

        $sonAktiviteler = AuditLog::orderBy('id', 'desc')->limit(15)->get();
        $bekleyenTicketlar = DestekTalebi::whereIn('durum', ['acik', 'islemde', 'bekliyor'])
            ->orderByRaw("FIELD(oncelik, 'acil','yuksek','orta','dusuk')")
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();
        $sonGirisler = LoginLog::orderBy('id', 'desc')->limit(10)->get();

        // ───────── 7 GUNLUK TREND: 14 ayri query yerine 2 GROUP BY ─────────
        $trend = \Cache::remember('sy.dashboard.trend', 300, function () use ($haftaTrendBaslangic) {
            $salonGunluk = DB::table('salonlar')
                ->where('created_at', '>=', $haftaTrendBaslangic)
                ->selectRaw('DATE(created_at) as tarih, COUNT(*) as adet')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('adet', 'tarih')->toArray();

            $randevuGunluk = DB::table('randevular')
                ->where('created_at', '>=', $haftaTrendBaslangic)
                ->selectRaw('DATE(created_at) as tarih, COUNT(*) as adet')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('adet', 'tarih')->toArray();

            $out = [];
            for ($i = 6; $i >= 0; $i--) {
                $g = date('Y-m-d', strtotime("-$i days"));
                $out[] = [
                    'tarih' => $g,
                    'salon' => (int) ($salonGunluk[$g] ?? 0),
                    'randevu' => (int) ($randevuGunluk[$g] ?? 0),
                ];
            }
            return $out;
        });

        return view('sistemyonetim.v2.dashboard', [
            'title' => 'Sistem Yönetim Paneli',
            'aktifMenu' => 'dashboard',
            'metrikler' => $metrikler,
            'sonAktiviteler' => $sonAktiviteler,
            'bekleyenTicketlar' => $bekleyenTicketlar,
            'sonGirisler' => $sonGirisler,
            'trend' => $trend,
        ]);
    }

    /* ============================================================
     * SALON YONETIMI
     * ============================================================ */
    public function salonlar(Request $request)
    {
        $q = trim($request->get('q', ''));
        $durum = $request->get('durum', 'hepsi'); // hepsi | aktif | askida
        $musteriYetkiliId = $request->get('mt');
        $tip = $request->get('tip', 'tumu'); // tumu | demo | aktif (hizli filtre butonlari)

        // Demo/Aktif ayrimi listedeki rozetle AYNI: demo = uyelik_turu=3 VE lisans <=90 gun.
        $esik  = date('Y-m-d', strtotime('+90 days'));
        $bugun = date('Y-m-d');
        $demoScope = function ($qq) use ($esik) {
            $qq->where('uyelik_turu', 3)->where(function ($w) use ($esik) {
                $w->whereNull('uyelik_bitis_tarihi')->orWhere('uyelik_bitis_tarihi', '<=', $esik);
            });
        };
        $aktifScope = function ($qq) use ($bugun, $esik) {
            $qq->where('askiya_alindi', 0)
               ->where('uyelik_bitis_tarihi', '>=', $bugun)
               ->where(function ($w) use ($esik) {
                   $w->where('uyelik_turu', '!=', 3)->orWhere('uyelik_bitis_tarihi', '>', $esik);
               });
        };

        // Ortak kapsam (arama + MT + destek rolu) — sayilar ve liste bunu paylasir
        $base = Salonlar::query();
        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('salon_adi', 'like', "%$q%")
                  ->orWhere('telefon_1', 'like', "%$q%")
                  ->orWhere('yetkili_telefon', 'like', "%$q%")
                  ->orWhere('yetkili_adi', 'like', "%$q%");
            });
        }
        if ($musteriYetkiliId) $base->where('musteri_yetkili_id', $musteriYetkiliId);
        if ($this->rol() === 'destek') $base->where('musteri_yetkili_id', $this->user()->id);

        // Hizli filtre sayilari (tip filtresinden bagimsiz)
        $sayilar = [
            'tumu'  => (clone $base)->count(),
            'demo'  => (clone $base)->where($demoScope)->count(),
            'aktif' => (clone $base)->where($aktifScope)->count(),
        ];

        // Listeleme sorgusu (Il/Ilce eager-load — N+1 onler)
        $query = (clone $base)->with(['il', 'ilce']);
        if ($durum === 'aktif') $query->where('askiya_alindi', 0);
        if ($durum === 'askida') $query->where('askiya_alindi', 1);
        if ($tip === 'demo')  $query->where($demoScope);
        if ($tip === 'aktif') $query->where($aktifScope);

        $perPage = (int) $request->get('per_page', 100);
        if (!in_array($perPage, [50, 100, 200, 500], true)) $perPage = 100;
        $salonlar = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->all());
        $musteriTemsilcileri = SistemYoneticileri::orderBy('name')->get();

        // MT id->name map (her satirda ayri sorgu yerine tek seferde)
        $mtMap = $musteriTemsilcileri->pluck('name', 'id');

        // Hesap sahibi fallback: yetkili_adi/yetkili_telefon bos olan salonlar icin
        // salon_personelleri'nde role_id=1 (hesap sahibi) kaydinin adi+telefonu.
        // Sayfadaki tum salonlar icin tek sorgu (N+1 yok).
        $salonIdler = collect($salonlar->items())->pluck('id')->all();
        $hesapSahipleri = [];
        if (!empty($salonIdler)) {
            $rows = DB::table('salon_personelleri')
                ->where('role_id', 1)
                ->whereIn('salon_id', $salonIdler)
                ->orderBy('id', 'asc')
                ->get(['salon_id', 'personel_adi', 'cep_telefon']);
            foreach ($rows as $r) {
                if (!isset($hesapSahipleri[$r->salon_id])) {
                    $hesapSahipleri[$r->salon_id] = $r; // salon basina ilk kayit
                }
            }
        }

        return view('sistemyonetim.v2.salonlar', [
            'title' => 'Salonlar',
            'aktifMenu' => 'salonlar',
            'salonlar' => $salonlar,
            'musteriTemsilcileri' => $musteriTemsilcileri,
            'mtMap' => $mtMap,
            'hesapSahipleri' => $hesapSahipleri,
            'q' => $q,
            'durum' => $durum,
            'mt' => $musteriYetkiliId,
            'tip' => $tip,
            'sayilar' => $sayilar,
        ]);
    }

    public function salonDetay($id)
    {
        $salon = Salonlar::findOrFail($id);
        if ($this->rol() === 'destek' && $salon->musteri_yetkili_id != $this->user()->id) {
            abort(403, 'Bu salonu görme yetkiniz yok.');
        }

        // Iletisim (yetkili) adi + telefonu — salon listesiyle AYNI fallback:
        // salon.yetkili_adi/telefon bos ise hesap sahibine (salon_personelleri role_id=1) dus.
        $iletisimAd  = trim((string) $salon->yetkili_adi);
        $iletisimTel = trim((string) $salon->yetkili_telefon);
        if ($iletisimAd === '' && $iletisimTel === '') {
            $hs = DB::table('salon_personelleri')
                ->where('role_id', 1)
                ->where('salon_id', $id)
                ->orderBy('id', 'asc')
                ->first(['personel_adi', 'cep_telefon']);
            if ($hs) {
                $iletisimAd  = trim((string) $hs->personel_adi);
                $iletisimTel = trim((string) $hs->cep_telefon);
            }
        }

        // Yetkililer: kanonik olarak personeller.yetkili_id uzerinden
        $personeller = Personeller::where('salon_id', $id)->get();
        // NOT: Collection::whereNotNull() Laravel 5.6'da yok (5.7+); pluck->filter ile null/bos elenir
        $yetkiliIds = $personeller->pluck('yetkili_id')->filter()->unique()->values();

        // Legacy: dogrudan salon_id ile bagli olabilen eski kayitlar
        $legacyIds = IsletmeYetkilileri::where('salon_id', $id)->pluck('id');
        $tumYetkiliIds = $yetkiliIds->merge($legacyIds)->unique();
        $yetkililer = $tumYetkiliIds->isNotEmpty()
            ? IsletmeYetkilileri::whereIn('id', $tumYetkiliIds)->get()
            : collect();

        // yetkili_id -> aktif mi (isten cikarilmis personel pasif gorunur). Ayni yetkili
        // birden cok personel kaydina baglanabilir; herhangi biri aktifse aktif say.
        $yetkiliAktif = [];
        foreach ($personeller as $p) {
            if (!$p->yetkili_id) continue;
            $yetkiliAktif[$p->yetkili_id] = max($yetkiliAktif[$p->yetkili_id] ?? 0, (int) $p->aktif);
        }
        $notlar = SalonNotu::where('salon_id', $id)->orderByDesc('pinned')->orderByDesc('id')->get();
        $impersonationGecmisi = ImpersonationLog::where('salon_id', $id)->orderByDesc('id')->limit(20)->get();
        $ticketlar = DestekTalebi::where('salon_id', $id)->orderByDesc('id')->limit(20)->get();
        $musteriTemsilcileri = SistemYoneticileri::orderBy('name')->get();

        $ayBaslangic = date('Y-m-01 00:00:00');
        // Randevu istatistikleri: tek sorguda toplam + bu ay
        $rStat = DB::table('randevular')
            ->where('salon_id', $id)
            ->selectRaw("COUNT(*) as toplam, SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as bu_ay", [$ayBaslangic])
            ->first();

        $istatistik = [
            'toplam_randevu'     => (int) ($rStat->toplam ?? 0),
            'bu_ay_randevu'      => (int) ($rStat->bu_ay ?? 0),
            'bu_ay_yeni_musteri' => 0,
            'whatsapp_aktif'     => $salon->whatsapp_aktif ? 1 : 0,
        ];
        try {
            $istatistik['bu_ay_yeni_musteri'] = (int) DB::table('musteri_portfoy')
                ->where('salon_id', $id)
                ->where('created_at', '>=', $ayBaslangic)
                ->count();
        } catch (\Exception $e) {}

        // Saglik skoru: 5 dakikalik cache
        $saglik = \Cache::remember('sy.saglik.salon.'.$id, 300, function () use ($id) {
            return \App\SistemYonetim\SaglikSkoru::hesapla($id);
        });

        // Duzenleme formu icin: il / ilce / salon turu listeleri (eager-load yuku olmadan)
        $iller = DB::table('il')->orderBy('il_adi')->get(['id', 'il_adi']);
        $ilceler = DB::table('ilce')->orderBy('ilce_adi')->get(['id', 'il_id', 'ilce_adi']);
        $salonTurleri = DB::table('salon_turu')->orderBy('salon_turu_adi')->get(['id', 'salon_turu_adi']);

        return view('sistemyonetim.v2.salon-detay', [
            'title' => $salon->salon_adi,
            'aktifMenu' => 'salonlar',
            'salon' => $salon,
            'iletisimAd' => $iletisimAd,
            'iletisimTel' => $iletisimTel,
            'yetkililer' => $yetkililer,
            'yetkiliAktif' => $yetkiliAktif,
            'personeller' => $personeller,
            'notlar' => $notlar,
            'impersonationGecmisi' => $impersonationGecmisi,
            'ticketlar' => $ticketlar,
            'musteriTemsilcileri' => $musteriTemsilcileri,
            'istatistik' => $istatistik,
            'saglik' => $saglik,
            'iller' => $iller,
            'ilceler' => $ilceler,
            'salonTurleri' => $salonTurleri,
        ]);
    }

    /**
     * Salon temel isletme bilgilerini gunceller (v2 panel, salon detay).
     * Mass-assignment guard'ina takilmamak icin alanlar tek tek atanir.
     */
    public function salonBilgiGuncelle(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $salon = Salonlar::findOrFail($id);

        $temizle = function ($v) {
            $v = is_string($v) ? trim($v) : $v;
            return ($v === '' || $v === null) ? null : $v;
        };

        // Salon adi bos birakilmasin
        $yeniAd = $temizle($request->get('salon_adi'));
        if ($yeniAd !== null) {
            $salon->salon_adi = $yeniAd;
        }

        $salon->domain          = $temizle($request->get('domain'));
        $salon->il_id           = $temizle($request->get('il_id'));
        $salon->ilce_id         = $temizle($request->get('ilce_id'));
        $salon->salon_turu_id   = $temizle($request->get('salon_turu_id'));
        $salon->adres           = $temizle($request->get('adres'));
        $salon->telefon_1       = $temizle($request->get('telefon_1'));
        $salon->telefon_2       = $temizle($request->get('telefon_2'));
        $salon->telefon_3       = $temizle($request->get('telefon_3'));
        $salon->yetkili_adi     = $temizle($request->get('yetkili_adi'));
        $salon->yetkili_telefon = $temizle($request->get('yetkili_telefon'));
        $salon->yetkili_mail    = $temizle($request->get('yetkili_mail'));
        $salon->aciklama        = $temizle($request->get('aciklama'));

        $salon->android_uygulama = $temizle($request->get('android_uygulama'));
        $salon->ios_uygulama     = $temizle($request->get('ios_uygulama'));
        $salon->huawei_uygulama  = $temizle($request->get('huawei_uygulama'));

        // varchar(5) — fazlasini kirp
        $salon->android_son_versiyon = $temizle(mb_substr((string) $request->get('android_son_versiyon'), 0, 5));
        $salon->ios_son_versiyon     = $temizle(mb_substr((string) $request->get('ios_son_versiyon'), 0, 5));
        $salon->huawei_son_versiyon  = $temizle(mb_substr((string) $request->get('huawei_son_versiyon'), 0, 5));

        $degisen = array_keys($salon->getDirty());
        $salon->save();

        Audit::log('salon_bilgi_guncelle', 'salon', $salon->id, $salon->salon_adi, 'Temel işletme bilgileri güncellendi', ['degisen' => $degisen]);

        return redirect()->back()->with('basari', 'İşletme bilgileri güncellendi.');
    }

    public function salonAskiyaAl(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);

        $salon = Salonlar::findOrFail($id);
        $sebep = $request->get('sebep', '');

        $eski = ['askiya_alindi' => $salon->askiya_alindi, 'sebep' => $salon->askiya_alma_sebebi];

        $salon->askiya_alindi = 1;
        $salon->askiya_alma_sebebi = $sebep;
        $salon->askiya_alan_user_id = $this->user()->id;
        $salon->askiya_alma_tarihi = date('Y-m-d H:i:s');
        $salon->save();

        Audit::log('salon_askiya_al', 'salon', $salon->id, $salon->salon_adi, "Sebep: $sebep", ['eski' => $eski, 'yeni' => ['askiya_alindi' => 1, 'sebep' => $sebep]]);

        return redirect()->back()->with('basari', 'Salon askıya alındı.');
    }

    public function salonAktifEt($id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $salon = Salonlar::findOrFail($id);

        $eski = ['askiya_alindi' => $salon->askiya_alindi];
        $salon->askiya_alindi = 0;
        $salon->askiya_alma_sebebi = null;
        $salon->askiya_alan_user_id = null;
        $salon->askiya_alma_tarihi = null;
        $salon->save();

        Audit::log('salon_aktif_et', 'salon', $salon->id, $salon->salon_adi, null, ['eski' => $eski]);

        return redirect()->back()->with('basari', 'Salon yeniden aktif edildi.');
    }

    public function salonMusteriTemsilcisiAta(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $salon = Salonlar::findOrFail($id);
        $eski = $salon->musteri_yetkili_id;
        $salon->musteri_yetkili_id = $request->get('musteri_yetkili_id') ?: null;
        $salon->save();

        Audit::log('musteri_temsilcisi_ata', 'salon', $salon->id, $salon->salon_adi, null, [
            'eski_id' => $eski,
            'yeni_id' => $salon->musteri_yetkili_id,
        ]);

        return redirect()->back()->with('basari', 'Müşteri temsilcisi güncellendi.');
    }

    /**
     * Salon demo/uyelik suresini uzatir (sistem yonetimi).
     * - gun: hizli uzatma (+7/+15/+30/... ). Mevcut bitis GELECEKteyse onun uzerine
     *   eklenir (kalan sure kaybolmaz); bos/gecmis ise BUGUNden itibaren eklenir.
     * - tarih: dogrudan belirli bir bitis tarihine ayarlar (Y-m-d).
     * uyelik_bitis_tarihi 'Y-m-d' formatinda tutulur (bkz. lisans_sure_kontrol).
     */
    public function salonSureUzat(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $salon = Salonlar::findOrFail($id);

        $gun   = (int) $request->get('gun', 0);
        $tarih = trim((string) $request->get('tarih', ''));

        $eski = $salon->uyelik_bitis_tarihi;
        $eskiGecerli = $eski && substr((string) $eski, 0, 4) !== '0000' && strtotime((string) $eski) !== false;

        if ($tarih !== '') {
            $ts = strtotime($tarih);
            if ($ts === false) {
                return redirect()->back()->with('hata', 'Geçersiz tarih girdiniz.');
            }
            $yeni = date('Y-m-d', $ts);
        } elseif ($gun > 0) {
            // Mevcut bitis bugunden ileriyse onun uzerine ekle; degilse bugunden
            $bazTs = ($eskiGecerli && strtotime((string) $eski) > strtotime(date('Y-m-d')))
                ? strtotime((string) $eski)
                : strtotime(date('Y-m-d'));
            $yeni = date('Y-m-d', strtotime('+' . $gun . ' days', $bazTs));
        } else {
            return redirect()->back()->with('hata', 'Uzatma için bir gün seçin ya da tarih girin.');
        }

        $salon->uyelik_bitis_tarihi = $yeni;
        $salon->save();

        Audit::log('salon_sure_uzat', 'salon', $salon->id, $salon->salon_adi,
            $tarih !== '' ? ('Tarihe ayarlandı: ' . $yeni) : ('+' . $gun . ' gün'),
            ['eski' => $eski, 'yeni' => $yeni]);

        return redirect()->back()->with('basari',
            'Demo/üyelik süresi güncellendi. Yeni bitiş tarihi: ' . date('d.m.Y', strtotime($yeni)));
    }

    /* ============================================================
     * YENI SALON (DEMO) OLUSTUR
     * ============================================================ */
    public function salonEkleForm()
    {
        $this->gerektir(['super_admin', 'yonetici']);
        return view('sistemyonetim.v2.salon-ekle', [
            'title'        => 'Yeni Salon (Demo)',
            'aktifMenu'    => 'salonlar',
            'iller'        => DB::table('il')->orderBy('il_adi')->get(['id', 'il_adi']),
            'ilceler'      => DB::table('ilce')->orderBy('ilce_adi')->get(['id', 'il_id', 'ilce_adi']),
            'salonTurleri' => DB::table('salon_turu')->orderBy('salon_turu_adi')->get(['id', 'salon_turu_adi']),
        ]);
    }

    /**
     * Yeni DEMO salon olusturur — site kaydiyla ayni iskelet:
     * yetkili (giris hesabi) + salon (demo_hesabi=1, uyelik_turu=3) + calisma/mola
     * saatleri + rol (hesap sahibi) + personel. Demo hesabi hemen giris yapabilir.
     */
    public function salonEkleKaydet(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $this->validate($request, [
            'salon_adi'       => 'required|min:2',
            'salon_turu_id'   => 'required',
            'yetkili_ad'      => 'required|min:2',
            'yetkili_telefon' => 'required',
            'yetkili_email'   => 'required|email|unique:isletmeyetkilileri,email',
            'demo_gun'        => 'nullable|integer|min:1|max:3650',
        ]);

        $demoGun = (int) ($request->get('demo_gun') ?: 7);

        // Sifre: verildiyse onu kullan, yoksa 6 haneli uret
        $sifre = trim((string) $request->get('yetkili_sifre'));
        if ($sifre === '') {
            $sifre = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 6);
        }
        $telefon = preg_replace('/[^0-9]/', '', (string) $request->get('yetkili_telefon'));

        DB::beginTransaction();
        try {
            // 1) Yetkili (giris hesabi)
            $yetkili = new IsletmeYetkilileri();
            $yetkili->name     = $request->get('yetkili_ad');
            $yetkili->email    = trim((string) $request->get('yetkili_email'));
            $yetkili->gsm1     = $telefon;
            $yetkili->password = Hash::make($sifre);
            $yetkili->save();

            // 2) Salon (DEMO)
            $salon = new Salonlar();
            $salon->salon_adi           = $request->get('salon_adi');
            $salon->salon_turu_id       = $request->get('salon_turu_id');
            $salon->il_id               = $request->get('il_id') ?: null;
            $salon->ilce_id             = $request->get('ilce_id') ?: null;
            $salon->adres               = $request->get('adres') ?: null;
            $salon->yetkili_adi         = $request->get('yetkili_ad');
            $salon->yetkili_telefon     = $telefon;
            $salon->randevu_saat_araligi = 15;
            $salon->randevu_takvim_turu = 1;
            $salon->uyelik_turu         = 3;   // demo
            $salon->demo_hesabi         = 1;
            $salon->uyelik_bitis_tarihi = date('Y-m-d', strtotime('+' . $demoGun . ' days'));
            $salon->save();

            // 3) Calisma saatleri (Pzt-Cmt acik 09-19, Paz kapali)
            $cs = [];
            for ($g = 1; $g <= 7; $g++) {
                $acik = $g <= 6 ? 1 : 0;
                $cs[] = [
                    'salon_id' => $salon->id, 'haftanin_gunu' => $g, 'calisiyor' => $acik,
                    'baslangic_saati' => $acik ? '09:00:00' : '00:00:00',
                    'bitis_saati'     => $acik ? '19:00:00' : '00:00:00',
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            \App\SalonCalismaSaatleri::insert($cs);

            // 4) Mola saatleri (kapali)
            $ms = [];
            for ($g = 1; $g <= 7; $g++) {
                $ms[] = [
                    'salon_id' => $salon->id, 'haftanin_gunu' => $g, 'mola_var' => 0,
                    'baslangic_saati' => '00:00:00', 'bitis_saati' => '00:00:00',
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            \App\SalonMolaSaatleri::insert($ms);

            // 5) Rol: hesap sahibi (role_id=1)
            DB::table('model_has_roles')->insert([
                'role_id'    => 1,
                'model_type' => 'App\\IsletmeYetkilileri',
                'model_id'   => $yetkili->id,
                'salon_id'   => $salon->id,
            ]);

            // 6) Personel (yetkili = takvimde gorunen personel; kanonik yetkili-salon baglantisi)
            $personel = new Personeller();
            $personel->salon_id          = $salon->id;
            $personel->personel_adi      = $yetkili->name;
            $personel->cep_telefon       = $telefon;
            $personel->yetkili_id        = $yetkili->id;
            $personel->takvimde_gorunsun = 1;
            $personel->takvim_sirasi     = 1;
            $personel->renk              = 1;
            $personel->aktif             = 1;
            $personel->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('hata', 'Demo salon oluşturulamadı: ' . $e->getMessage());
        }

        Audit::log('salon_demo_olustur', 'salon', $salon->id, $salon->salon_adi,
            'Demo salon oluşturuldu (' . $demoGun . ' gün)', ['yetkili_email' => $yetkili->email]);

        return redirect('/sistemyonetim/v2/salon/' . $salon->id)->with('basari',
            'Demo salon oluşturuldu ✓  Giriş e-postası: ' . $yetkili->email
            . '  ·  Şifre: ' . $sifre
            . '  ·  Demo bitiş: ' . date('d.m.Y', strtotime($salon->uyelik_bitis_tarihi)));
    }

    /**
     * Demo salonun lisansini aktif eder: demo_hesabi=0, uyelik_turu=1 (paketli),
     * uyelik_bitis_tarihi secilen sure kadar uzatilir. Listede 'Lisanslı' olur.
     */
    public function salonLisansAktif(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $salon = Salonlar::findOrFail($id);

        $gun = (int) ($request->get('gun') ?: 365);
        $eski = [
            'demo_hesabi'         => $salon->demo_hesabi,
            'uyelik_turu'         => $salon->uyelik_turu,
            'uyelik_bitis_tarihi' => $salon->uyelik_bitis_tarihi,
        ];

        // Mevcut bitis gelecekteyse onun uzerine ekle; degilse bugunden
        $ub = $salon->uyelik_bitis_tarihi;
        $bazTs = ($ub && substr((string) $ub, 0, 4) !== '0000' && strtotime((string) $ub) > strtotime(date('Y-m-d')))
            ? strtotime((string) $ub)
            : strtotime(date('Y-m-d'));

        $salon->demo_hesabi         = 0;
        $salon->uyelik_turu         = 1;   // paketli (lisanslı)
        $salon->uyelik_bitis_tarihi = date('Y-m-d', strtotime('+' . $gun . ' days', $bazTs));
        $salon->save();

        Audit::log('salon_lisans_aktif', 'salon', $salon->id, $salon->salon_adi,
            '+' . $gun . ' gün lisans', ['eski' => $eski, 'yeni_bitis' => $salon->uyelik_bitis_tarihi]);

        return redirect()->back()->with('basari',
            'Lisans aktif edildi ✓  Bitiş tarihi: ' . date('d.m.Y', strtotime($salon->uyelik_bitis_tarihi)));
    }

    /**
     * Personeli işten çıkar (pasif) / geri al. İşten çıkan hesap impersonation
     * varsayilaninda atlanir ve salon personel listesinde pasif gorunur.
     */
    public function salonHesapPasif(Request $request, $id)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $salon = Salonlar::findOrFail($id);
        $yetkiliId = (int) $request->get('yetkili_id');
        $aktif = (int) $request->get('aktif', 0); // 0 = isten cikar, 1 = geri al
        if (!$yetkiliId) return redirect()->back()->with('hata', 'Hesap seçilmedi.');

        $etkilenen = Personeller::where('salon_id', $id)->where('yetkili_id', $yetkiliId)->update(['aktif' => $aktif]);
        $yetkili = IsletmeYetkilileri::find($yetkiliId);
        $ad = $yetkili->name ?? ('#' . $yetkiliId);

        Audit::log($aktif ? 'salon_hesap_geri_al' : 'salon_hesap_pasif', 'salon', $salon->id, $salon->salon_adi,
            $ad . ($aktif ? ' tekrar aktif edildi' : ' işten çıkarıldı (pasif)'),
            ['yetkili_id' => $yetkiliId, 'etkilenen' => $etkilenen]);

        return redirect()->back()->with('basari',
            $ad . ($aktif ? ' tekrar aktif edildi.' : ' işten çıkarıldı — giriş varsayılanı artık bu hesabı atlar.'));
    }

    /* ============================================================
     * SALON HESABINA GIRIS (IMPERSONATION)
     * ============================================================ */
    public function salonHesabinaGir(Request $request, $salonId)
    {
        $this->gerektir(['super_admin', 'yonetici', 'destek']);

        $salon = Salonlar::findOrFail($salonId);
        if ($this->rol() === 'destek' && $salon->musteri_yetkili_id != $this->user()->id) {
            abort(403, 'Bu salona giriş yetkiniz yok.');
        }
        if ($salon->askiya_alindi) {
            return redirect()->back()->with('hata', 'Salon askıda — önce aktif edin.');
        }

        $yetkili = null;

        // Belirli bir hesap secildiyse (personel veya sahip): yetkili_id ile gir.
        // GUVENLIK: secilen hesap mutlaka bu salona bagli olmali.
        $hedefYetkiliId = $request->get('yetkili_id');
        if ($hedefYetkiliId) {
            $bagli = Personeller::where('salon_id', $salonId)->where('yetkili_id', $hedefYetkiliId)->exists()
                  || IsletmeYetkilileri::where('id', $hedefYetkiliId)->where('salon_id', $salonId)->exists();
            if (!$bagli) {
                return redirect()->back()->with('hata', 'Seçilen hesap bu salona bağlı değil.');
            }
            $yetkili = IsletmeYetkilileri::find($hedefYetkiliId);
            if (!$yetkili) {
                return redirect()->back()->with('hata', 'Seçilen hesap bulunamadı.');
            }
        }

        // Hesap secilmediyse: salonun ilk AKTIF yetkili hesabina gir (isten cikarilan
        // = pasif personel atlanir; boylece ayrilan kisi varsayilan olmaz).
        if (!$yetkili) {
            $personel = Personeller::where('salon_id', $salonId)
                ->whereNotNull('yetkili_id')
                ->where('aktif', 1)
                ->orderBy('id', 'asc')
                ->first();
            // Hic aktif yoksa (edge) eski davranisa dus
            if (!$personel) {
                $personel = Personeller::where('salon_id', $salonId)
                    ->whereNotNull('yetkili_id')
                    ->orderBy('id', 'asc')
                    ->first();
            }
            if ($personel) {
                $yetkili = IsletmeYetkilileri::find($personel->yetkili_id);
            }
            // Legacy fallback: bazi eski salonlarda isletmeyetkilileri.salon_id dolu olabilir
            if (!$yetkili) {
                $yetkili = IsletmeYetkilileri::where('salon_id', $salonId)->first();
            }
        }
        if (!$yetkili) {
            return redirect()->back()->with('hata', 'Bu salona bağlı yetkili-personel kaydı yok. Önce salon detayından yetkili ekleyin.');
        }

        $sebep = $request->get('sebep') ?: 'Destek girişi';
        $ticketId = $request->get('ticket_id');

        $log = ImpersonationLog::create([
            'user_id'   => $this->user()->id,
            'user_name' => $this->user()->name,
            'salon_id'  => $salon->id,
            'salon_adi' => $salon->salon_adi,
            'isletme_yetkili_id'    => $yetkili->id,
            'isletme_yetkili_email' => $yetkili->email,
            'sebep'             => $sebep,
            'ticket_id'         => $ticketId,
            'baslangic_tarihi'  => date('Y-m-d H:i:s'),
            'ip'                => $request->ip(),
            'user_agent'        => mb_substr((string) $request->header('User-Agent'), 0, 255),
        ]);

        // session'a impersonation isaretle (cikiste log kapatmak icin)
        session([
            'sysadmin_impersonation_id'   => $log->id,
            'sysadmin_impersonation_uid'  => $this->user()->id,
        ]);

        Audit::log('salon_hesabina_gir', 'salon', $salon->id, $salon->salon_adi, $sebep, [
            'yetkili_id'    => $yetkili->id,
            'yetkili_email' => $yetkili->email,
            'hesap_tipi'    => !empty($yetkili->is_admin) ? 'sahip' : 'personel',
            'ticket_id'     => $ticketId,
        ]);

        // simdi isletmeyonetim guard'ina giris yap
        Auth::guard('isletmeyonetim')->login($yetkili);

        // ONEMLI: AuthenticateSession middleware'i oturumda 'password_hash_isletmeyonetim'
        // tutar ve her istekte mevcut kullanicinin parola hash'iyle karsilastirir; uymazsa
        // butun oturumu flush edip kullaniciyi atar. Onceki bir impersonation'dan (baska
        // salon yetkilisi) kalan ESKI hash yeni girilen salonun yetkilisiyle uyusmadigi
        // icin "girer girmez disari atiyor" yasaniyordu. Yeni kullanicinin hash'iyle
        // senkronla ki middleware oturumu gecersiz saymasin.
        session()->put('password_hash_isletmeyonetim', $yetkili->getAuthPassword());

        // ?sube= ile panelin DOGRU salon baglaminda acilmasini garantiye al.
        // Yetki kontrolu (PersonelYetkiServisi) $isletme->id'ye gore personel
        // kaydini bulur; salon yanlissa personel bulunamaz ve fail-open ile
        // tum menuler acilir. sube=salon_id verince personelin yetkileri uygulanir.
        return redirect('/isletmeyonetim?sube=' . $salon->id);
    }

    public function impersonationBitir()
    {
        $logId = session('sysadmin_impersonation_id');
        if ($logId) {
            $log = ImpersonationLog::find($logId);
            if ($log && !$log->bitis_tarihi) {
                $log->bitis_tarihi = date('Y-m-d H:i:s');
                $log->save();
            }
        }
        session()->forget(['sysadmin_impersonation_id', 'sysadmin_impersonation_uid']);
        Auth::guard('isletmeyonetim')->logout();
        // AuthenticateSession hash anahtarini da temizle ki bir sonraki impersonation'da
        // bu salonun eski hash'i kalip yeni salona girerken oturumu flush etmesin.
        session()->forget('password_hash_isletmeyonetim');
        return redirect('/sistemyonetim/v2/dashboard')->with('basari', 'Salon hesabından çıkıldı.');
    }

    /* ============================================================
     * SALON NOTLARI
     * ============================================================ */
    public function notEkle(Request $request, $salonId)
    {
        $this->validate($request, [
            'icerik' => 'required|min:2',
            'tip'    => 'nullable|in:genel,uyari,onemli,sikayet,talep,odeme',
        ]);
        $salon = Salonlar::findOrFail($salonId);

        SalonNotu::create([
            'salon_id'  => $salonId,
            'user_id'   => $this->user()->id,
            'user_name' => $this->user()->name,
            'baslik'    => $request->get('baslik'),
            'icerik'    => $request->get('icerik'),
            'tip'       => $request->get('tip', 'genel'),
            'pinned'    => $request->get('pinned') ? 1 : 0,
        ]);

        Audit::log('not_ekle', 'salon', $salonId, $salon->salon_adi, $request->get('baslik'));

        return redirect()->back()->with('basari', 'Not eklendi.');
    }

    public function notSil($id)
    {
        $not = SalonNotu::findOrFail($id);
        if ($this->rol() !== 'super_admin' && $not->user_id != $this->user()->id) {
            abort(403, 'Sadece kendi notunuzu silebilirsiniz.');
        }
        $salonId = $not->salon_id;
        $not->delete();
        Audit::log('not_sil', 'salon', $salonId, null, "#$id");
        return redirect()->back()->with('basari', 'Not silindi.');
    }

    public function notPin($id)
    {
        $not = SalonNotu::findOrFail($id);
        $not->pinned = $not->pinned ? 0 : 1;
        $not->save();
        return redirect()->back();
    }

    /* ============================================================
     * EKIP & ROLLER
     * ============================================================ */
    public function ekip()
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $ekip = SistemYoneticileri::orderBy('aktif', 'desc')->orderBy('id', 'desc')->get();
        return view('sistemyonetim.v2.ekip', [
            'title' => 'Ekip & Roller',
            'aktifMenu' => 'ekip',
            'ekip' => $ekip,
        ]);
    }

    public function ekipFormYeni()
    {
        $this->gerektir(['super_admin']);
        return view('sistemyonetim.v2.ekip-form', [
            'title' => 'Yeni Ekip Üyesi',
            'aktifMenu' => 'ekip',
            'duzenleniyor' => null,
        ]);
    }

    public function ekipKaydet(Request $request)
    {
        $this->gerektir(['super_admin']);
        $this->validate($request, [
            'name'     => 'required|min:2',
            'email'    => 'required|email|unique:sistemyoneticileri,email',
            'password' => 'required|min:6',
            'rol'      => 'required|in:super_admin,yonetici,destek,izleyici',
        ]);

        $u = new SistemYoneticileri();
        $u->name = $request->name;
        $u->email = $request->email;
        $u->password = Hash::make($request->password);
        $u->rol = $request->rol;
        $u->admin = $request->rol === 'super_admin' ? 1 : 0;
        $u->aktif = 1;
        $u->telefon = $request->telefon;
        $u->save();

        Audit::log('kullanici_olustur', 'sistem_yoneticisi', $u->id, $u->name, "Rol: {$u->rol}");

        return redirect('/sistemyonetim/v2/ekip')->with('basari', 'Ekip üyesi eklendi.');
    }

    public function ekipFormDuzenle($id)
    {
        $this->gerektir(['super_admin']);
        $u = SistemYoneticileri::findOrFail($id);
        return view('sistemyonetim.v2.ekip-form', [
            'title' => $u->name,
            'aktifMenu' => 'ekip',
            'duzenleniyor' => $u,
        ]);
    }

    public function ekipGuncelle(Request $request, $id)
    {
        $this->gerektir(['super_admin']);
        $u = SistemYoneticileri::findOrFail($id);
        $this->validate($request, [
            'name'  => 'required|min:2',
            'email' => 'required|email|unique:sistemyoneticileri,email,'.$id,
            'rol'   => 'required|in:super_admin,yonetici,destek,izleyici',
        ]);

        $eski = ['rol' => $u->rol, 'aktif' => $u->aktif, 'email' => $u->email];

        $u->name = $request->name;
        $u->email = $request->email;
        $u->rol = $request->rol;
        $u->admin = $request->rol === 'super_admin' ? 1 : 0;
        $u->aktif = $request->aktif ? 1 : 0;
        $u->telefon = $request->telefon;
        if ($request->password) {
            $u->password = Hash::make($request->password);
        }
        $u->save();

        Audit::log('kullanici_guncelle', 'sistem_yoneticisi', $u->id, $u->name, null, [
            'eski' => $eski,
            'yeni' => ['rol' => $u->rol, 'aktif' => $u->aktif, 'email' => $u->email],
        ]);

        return redirect('/sistemyonetim/v2/ekip')->with('basari', 'Ekip üyesi güncellendi.');
    }

    public function ekipPasifEt($id)
    {
        $this->gerektir(['super_admin']);
        $u = SistemYoneticileri::findOrFail($id);
        if ($u->id == $this->user()->id) {
            return redirect()->back()->with('hata', 'Kendinizi pasif edemezsiniz.');
        }
        $u->aktif = 0;
        $u->save();
        Audit::log('kullanici_pasif', 'sistem_yoneticisi', $u->id, $u->name);
        return redirect()->back()->with('basari', 'Pasif edildi.');
    }

    /* ============================================================
     * AKTIVITE LOG
     * ============================================================ */
    public function aktiviteLog(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);

        $q = $request->get('q', '');
        $action = $request->get('action');
        $userId = $request->get('user_id');
        $tarih = $request->get('tarih');

        $query = AuditLog::query()->orderBy('id', 'desc');
        if ($q) $query->where(function ($w) use ($q) {
            $w->where('target_label', 'like', "%$q%")
              ->orWhere('aciklama', 'like', "%$q%")
              ->orWhere('user_name', 'like', "%$q%");
        });
        if ($action) $query->where('action', $action);
        if ($userId) $query->where('user_id', $userId);
        if ($tarih) $query->whereDate('created_at', $tarih);

        $loglar = $query->paginate(50)->appends($request->all());

        $kullanicilar = SistemYoneticileri::orderBy('name')->get(['id','name']);
        // Distinct action listesi nadiren degisir — 5dk cache
        $aksiyonlar = \Cache::remember('sy.aktivite.actions', 300, function () {
            return AuditLog::distinct()->pluck('action')->filter()->values();
        });

        return view('sistemyonetim.v2.aktivite-log', [
            'title' => 'Aktivite Logu',
            'aktifMenu' => 'aktivite',
            'loglar' => $loglar,
            'kullanicilar' => $kullanicilar,
            'aksiyonlar' => $aksiyonlar,
            'q' => $q, 'action' => $action, 'user_id' => $userId, 'tarih' => $tarih,
        ]);
    }

    /* ============================================================
     * DESTEK TICKETLARI
     * ============================================================ */
    public function ticketlar(Request $request)
    {
        $durum = $request->get('durum', 'acik_islemde');
        $oncelik = $request->get('oncelik');
        $atanan = $request->get('atanan');
        $q = $request->get('q', '');

        $query = DestekTalebi::query();
        if ($durum === 'acik_islemde') $query->whereIn('durum', ['acik', 'islemde', 'bekliyor']);
        elseif ($durum && $durum !== 'hepsi') $query->where('durum', $durum);
        if ($oncelik) $query->where('oncelik', $oncelik);
        if ($atanan === 'bana') $query->where('atanan_user_id', $this->user()->id);
        elseif ($atanan === 'atanmamis') $query->whereNull('atanan_user_id');
        elseif ($atanan) $query->where('atanan_user_id', $atanan);
        if ($q) $query->where(function ($w) use ($q) {
            $w->where('konu', 'like', "%$q%")
              ->orWhere('numara', 'like', "%$q%")
              ->orWhere('salon_adi', 'like', "%$q%");
        });

        $ticketlar = $query->orderByRaw("FIELD(durum,'acik','islemde','bekliyor','cozumlendi','kapali')")
            ->orderByRaw("FIELD(oncelik,'acil','yuksek','orta','dusuk')")
            ->orderBy('id', 'desc')
            ->paginate(25)->appends($request->all());

        $ekip = SistemYoneticileri::where('aktif', 1)->orderBy('name')->get();

        return view('sistemyonetim.v2.ticketlar', [
            'title' => 'Destek Talepleri',
            'aktifMenu' => 'ticket',
            'ticketlar' => $ticketlar,
            'ekip' => $ekip,
            'q' => $q, 'durum' => $durum, 'oncelik' => $oncelik, 'atanan' => $atanan,
        ]);
    }

    public function ticketYeni()
    {
        $salonlar = Salonlar::orderBy('salon_adi')->get(['id', 'salon_adi']);
        return view('sistemyonetim.v2.ticket-yeni', [
            'title' => 'Yeni Destek Talebi',
            'aktifMenu' => 'ticket',
            'salonlar' => $salonlar,
        ]);
    }

    /**
     * Ticket olustur/guncelle/durum degisikligi sonrasi bildirim ve badge cache'ini temizle.
     */
    private function ticketCacheTemizle()
    {
        try {
            \Cache::forget('sy.layout.bekleyen_ticket');
            $aktifIds = DB::table('sistemyoneticileri')->where('aktif', 1)->pluck('id');
            foreach ($aktifIds as $sid) {
                \Cache::forget('sy.bildirim.user.' . $sid);
            }
        } catch (\Exception $e) {}
    }

    public function ticketKaydet(Request $request)
    {
        $this->validate($request, [
            'konu'     => 'required|min:2',
            'kategori' => 'required|in:teknik,odeme,egitim,ozellik,sikayet,diger',
            'oncelik'  => 'required|in:dusuk,orta,yuksek,acil',
        ]);

        $salonAdi = null;
        if ($request->salon_id) {
            $salonAdi = Salonlar::where('id', $request->salon_id)->value('salon_adi');
        }

        $numara = 'T-'.date('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $ticket = DestekTalebi::create([
            'numara'   => $numara,
            'salon_id' => $request->salon_id ?: null,
            'salon_adi' => $salonAdi,
            'iletisim_ad'      => $request->iletisim_ad,
            'iletisim_telefon' => $request->iletisim_telefon,
            'iletisim_email'   => $request->iletisim_email,
            'konu'     => $request->konu,
            'aciklama' => $request->aciklama,
            'kategori' => $request->kategori,
            'oncelik'  => $request->oncelik,
            'durum'    => 'acik',
            'olusturan_user_id'   => $this->user()->id,
            'olusturan_user_name' => $this->user()->name,
        ]);

        if ($request->aciklama) {
            DestekMesaji::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $this->user()->id,
                'user_name' => $this->user()->name,
                'user_tipi' => 'ekip',
                'mesaj'     => $request->aciklama,
                'ic_not'    => 0,
            ]);
        }

        Audit::log('ticket_olustur', 'ticket', $ticket->id, "$numara | {$request->konu}");
        $this->ticketCacheTemizle();

        return redirect('/sistemyonetim/v2/ticket/'.$ticket->id)->with('basari', 'Talep oluşturuldu.');
    }

    public function ticketDetay($id)
    {
        $ticket = DestekTalebi::findOrFail($id);
        $mesajlar = DestekMesaji::where('ticket_id', $id)->orderBy('id', 'asc')->get();
        $ekip = SistemYoneticileri::where('aktif', 1)->orderBy('name')->get();
        $salon = $ticket->salon_id ? Salonlar::find($ticket->salon_id) : null;
        return view('sistemyonetim.v2.ticket-detay', [
            'title' => '#'.$ticket->numara,
            'aktifMenu' => 'ticket',
            'ticket' => $ticket,
            'mesajlar' => $mesajlar,
            'ekip' => $ekip,
            'salon' => $salon,
        ]);
    }

    public function ticketYanit(Request $request, $id)
    {
        $this->validate($request, ['mesaj' => 'required|min:1']);
        $ticket = DestekTalebi::findOrFail($id);

        DestekMesaji::create([
            'ticket_id' => $id,
            'user_id'   => $this->user()->id,
            'user_name' => $this->user()->name,
            'user_tipi' => 'ekip',
            'mesaj'     => $request->mesaj,
            'ic_not'    => $request->ic_not ? 1 : 0,
        ]);

        if (!$ticket->ilk_yanit_tarihi) {
            $ticket->ilk_yanit_tarihi = date('Y-m-d H:i:s');
        }
        if ($ticket->durum === 'acik') $ticket->durum = 'islemde';
        $ticket->save();

        Audit::log('ticket_yanit', 'ticket', $ticket->id, "{$ticket->numara}");

        return redirect()->back()->with('basari', 'Yanıt eklendi.');
    }

    public function ticketDurum(Request $request, $id)
    {
        $this->validate($request, [
            'durum' => 'required|in:acik,islemde,bekliyor,cozumlendi,kapali',
        ]);
        $ticket = DestekTalebi::findOrFail($id);
        $eski = $ticket->durum;
        $ticket->durum = $request->durum;
        if ($request->durum === 'cozumlendi' && !$ticket->cozumlenme_tarihi) {
            $ticket->cozumlenme_tarihi = date('Y-m-d H:i:s');
        }
        if ($request->durum === 'kapali' && !$ticket->kapanis_tarihi) {
            $ticket->kapanis_tarihi = date('Y-m-d H:i:s');
        }
        $ticket->save();

        Audit::log('ticket_durum', 'ticket', $ticket->id, "{$ticket->numara}", "$eski → {$ticket->durum}");
        $this->ticketCacheTemizle();
        return redirect()->back()->with('basari', 'Durum güncellendi.');
    }

    public function ticketAta(Request $request, $id)
    {
        $ticket = DestekTalebi::findOrFail($id);
        $eski = $ticket->atanan_user_name;
        if ($request->atanan_user_id) {
            $u = SistemYoneticileri::find($request->atanan_user_id);
            $ticket->atanan_user_id = $u->id;
            $ticket->atanan_user_name = $u->name;
        } else {
            $ticket->atanan_user_id = null;
            $ticket->atanan_user_name = null;
        }
        $ticket->save();
        Audit::log('ticket_ata', 'ticket', $ticket->id, $ticket->numara, $eski.' → '.($ticket->atanan_user_name ?: 'atanmamış'));
        $this->ticketCacheTemizle();
        return redirect()->back()->with('basari', 'Atama güncellendi.');
    }

    public function ticketOncelik(Request $request, $id)
    {
        $this->validate($request, ['oncelik' => 'required|in:dusuk,orta,yuksek,acil']);
        $ticket = DestekTalebi::findOrFail($id);
        $eski = $ticket->oncelik;
        $ticket->oncelik = $request->oncelik;
        $ticket->save();
        Audit::log('ticket_oncelik', 'ticket', $ticket->id, $ticket->numara, "$eski → {$ticket->oncelik}");
        return redirect()->back();
    }

    /* ============================================================
     * SISTEM SAGLIK
     * ============================================================ */
    public function sistemSaglik()
    {
        $this->gerektir(['super_admin', 'yonetici']);

        $dbDurum = 'OK';
        $dbVersion = null;
        try {
            $dbVersion = DB::select('SELECT VERSION() AS v')[0]->v ?? null;
        } catch (\Exception $e) {
            $dbDurum = 'HATA: '.$e->getMessage();
        }

        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $diskKullanim = $diskTotal ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1) : 0;

        $logHatalari = [];
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $boyut = filesize($logFile);
            if ($boyut > 0) {
                $f = fopen($logFile, 'r');
                if ($boyut > 50000) fseek($f, $boyut - 50000);
                while (($line = fgets($f)) !== false) {
                    if (strpos($line, '.ERROR') !== false || strpos($line, '.CRITICAL') !== false) {
                        $logHatalari[] = mb_substr($line, 0, 250);
                    }
                }
                fclose($f);
                $logHatalari = array_slice(array_reverse($logHatalari), 0, 30);
            }
        }

        $whatsappAktif = 0;
        try {
            $whatsappAktif = (int) DB::table('salonlar')->where('whatsapp_aktif', 1)->count();
        } catch (\Exception $e) {}

        return view('sistemyonetim.v2.sistem-saglik', [
            'title' => 'Sistem Sağlık',
            'aktifMenu' => 'saglik',
            'dbDurum' => $dbDurum,
            'dbVersion' => $dbVersion,
            'diskTotal' => $diskTotal,
            'diskFree' => $diskFree,
            'diskKullanim' => $diskKullanim,
            'logHatalari' => $logHatalari,
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'whatsappAktif' => $whatsappAktif,
        ]);
    }

    /* ============================================================
     * GIRIS LOGLARI
     * ============================================================ */
    public function girisLoglari()
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $loglar = LoginLog::orderBy('id', 'desc')->paginate(50);
        return view('sistemyonetim.v2.giris-loglari', [
            'title' => 'Giriş Logları',
            'aktifMenu' => 'guvenlik',
            'loglar' => $loglar,
        ]);
    }

    public function impersonationLoglari()
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $loglar = ImpersonationLog::orderBy('id', 'desc')->paginate(50);
        return view('sistemyonetim.v2.impersonation-loglari', [
            'title' => 'Salon Hesabına Giriş Logları',
            'aktifMenu' => 'guvenlik',
            'loglar' => $loglar,
        ]);
    }

    /* ============================================================
     * WHATSAPP YONETIM (eski panel v2 layout'ta)
     * ============================================================ */
    public function whatsappPanel()
    {
        return view('sistemyonetim.v2.whatsapp', [
            'title' => 'WhatsApp Yönetim',
            'aktifMenu' => 'whatsapp',
        ]);
    }

    /* ============================================================
     * MANUEL ODEME LINKI
     * Serbest metin hizmet + adet + tutar girip PayTR odeme linki uretir.
     * (Mevcut Musteri_Formlari + /odeme?form=ID + PayTR callback akisini kullanir)
     * ============================================================ */
    public function manuelOdemeLinki()
    {
        $isletmeler = Salonlar::orderBy('salon_adi')
            ->get(['id', 'salon_adi', 'yetkili_adi', 'yetkili_mail', 'yetkili_telefon']);

        return view('sistemyonetim.v2.manuel-odeme-linki', [
            'title' => 'Manuel Ödeme Linki',
            'aktifMenu' => 'manuelodeme',
            'isletmeler' => $isletmeler,
        ]);
    }

    public function manuelOdemeLinkiOlustur(Request $request)
    {
        $salon = Salonlar::find($request->salon_id);
        if (!$salon) {
            return response()->json(['type' => 'error', 'message' => 'Lütfen geçerli bir işletme seçin.']);
        }

        $hizmetler = $request->hizmetler;
        if (empty($hizmetler) || !is_array($hizmetler)) {
            return response()->json(['type' => 'error', 'message' => 'En az bir hizmet satırı girmelisiniz.']);
        }

        // Gecerli satirlari ayikla (form, en az 1 gecerli satir olmadan olusmasin)
        $gecerli = [];
        foreach ($hizmetler as $h) {
            $ad    = isset($h['ad']) ? trim($h['ad']) : '';
            $adet  = isset($h['adet']) ? (int) $h['adet'] : 1;
            // tutar: binlik ayraci '.' temizlenir, ondalik icin ',' beklenir (Turkce bicim)
            $tutar = isset($h['tutar']) ? (float) str_replace(',', '.', str_replace('.', '', $h['tutar'])) : 0;
            if ($ad === '' || $tutar <= 0) {
                continue;
            }
            if ($adet < 1) {
                $adet = 1;
            }
            $gecerli[] = ['ad' => $ad, 'adet' => $adet, 'tutar' => $tutar];
        }

        if (empty($gecerli)) {
            return response()->json(['type' => 'error', 'message' => 'Geçerli bir hizmet satırı bulunamadı (ad ve tutar zorunlu).']);
        }

        $form = new Musteri_Formlari();
        $form->salon_id = $salon->id;
        $form->durum_id = 6; // Odeme bekleniyor
        $form->satis_tarihi = date('Y-m-d H:i:s');
        $form->notlar = $request->notlar;
        $form->save();

        $toplam = 0;
        foreach ($gecerli as $g) {
            $satir = new Musteri_Formlari_Hizmetler();
            $satir->form_id = $form->id;
            $satir->uyelik_id = null;     // manuel/serbest hizmet — uyelik paketine bagli degil
            $satir->aciklama = $g['ad'];
            $satir->adet = $g['adet'];
            $satir->ucret = $g['tutar']; // birim tutar (odeme.blade adet ile carpar)
            $satir->save();
            $toplam += $g['tutar'] * $g['adet'];
        }

        Audit::log('manuel_odeme_linki', 'musteri_formu', $form->id, $salon->salon_adi, number_format($toplam, 2, ',', '.') . ' TL');

        $link = 'https://app.randevumcepte.com.tr/odeme?form=' . $form->id;

        return response()->json([
            'type' => 'success',
            'form_id' => $form->id,
            'link' => $link,
            'toplam' => number_format($toplam, 2, ',', '.'),
        ]);
    }

    /* ============================================================
     * GLOBAL ARAMA (AJAX)
     * ============================================================ */
    public function globalArama(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['salon' => [], 'ticket' => [], 'ekip' => []]);
        }
        $like = '%' . $q . '%';

        $salonlar = Salonlar::where(function ($w) use ($like) {
            $w->where('salon_adi', 'like', $like)
              ->orWhere('telefon_1', 'like', $like)
              ->orWhere('yetkili_telefon', 'like', $like)
              ->orWhere('yetkili_adi', 'like', $like);
        })->limit(8)->get(['id', 'salon_adi', 'yetkili_adi', 'telefon_1', 'askiya_alindi']);

        $ticketlar = DestekTalebi::where(function ($w) use ($like) {
            $w->where('numara', 'like', $like)
              ->orWhere('konu', 'like', $like)
              ->orWhere('salon_adi', 'like', $like);
        })->limit(8)->get(['id', 'numara', 'konu', 'durum', 'oncelik', 'salon_adi']);

        $ekip = SistemYoneticileri::where(function ($w) use ($like) {
            $w->where('name', 'like', $like)->orWhere('email', 'like', $like);
        })->limit(8)->get(['id', 'name', 'email', 'rol']);

        return response()->json([
            'salon' => $salonlar,
            'ticket' => $ticketlar,
            'ekip' => $ekip,
        ]);
    }

    public function salonAraJson(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (mb_strlen($q) < 2) return response()->json([]);
        $list = Salonlar::where('salon_adi', 'like', '%' . $q . '%')
            ->orderBy('salon_adi')
            ->limit(20)
            ->get(['id', 'salon_adi']);
        return response()->json($list);
    }

    /* ============================================================
     * NOTIFICATION FEED (AJAX)
     * ============================================================ */
    public function bildirimFeed()
    {
        $u = $this->user();
        // Cache 15sn (yeni gelen ticketlar gec gorunmesin)
        $tickets = \Cache::remember('sy.bildirim.user.'.$u->id, 15, function () use ($u) {
            return DestekTalebi::whereIn('durum', ['acik', 'islemde', 'bekliyor'])
                ->where(function ($w) use ($u) {
                    // 1) bana atanmis
                    $w->where('atanan_user_id', $u->id)
                    // 2) atanmamis (yeni gelen) — herkesin gormesi lazim
                      ->orWhereNull('atanan_user_id')
                    // 3) acil (atansa dahi)
                      ->orWhere('oncelik', 'acil');
                })
                ->where('created_at', '>=', date('Y-m-d', strtotime('-14 days')))
                ->orderBy('id', 'desc')
                ->limit(15)
                ->get();
        });
        $bildirimler = [];

        foreach ($tickets as $t) {
            $bildirimler[] = [
                'tip'   => 'ticket',
                'ikon'  => $t->oncelik === 'acil' ? 'mdi-alert' : 'mdi-lifebuoy',
                'renk'  => $t->oncelik === 'acil' ? 'danger' : 'info',
                'baslik'=> $t->numara . ' — ' . mb_substr($t->konu, 0, 50),
                'aciklama' => ($t->salon_adi ?: 'Genel') . ' · ' . $t->oncelik,
                'link'  => '/sistemyonetim/v2/ticket/' . $t->id,
                'zaman' => \Carbon\Carbon::parse($t->created_at)->diffForHumans(),
            ];
        }

        // Yeni demo kayitlari (son 3 gun) — musteri kendi demosunu acinca burada gorunur
        $yeniDemolar = Salonlar::where('uyelik_turu', 3)
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->orderBy('id', 'desc')->limit(10)
            ->get(['id', 'salon_adi', 'created_at', 'yetkili_adi', 'yetkili_telefon']);
        foreach ($yeniDemolar as $d) {
            $bildirimler[] = [
                'tip'   => 'demo',
                'ikon'  => 'mdi-store-plus',
                'renk'  => 'warning',
                'baslik'=> 'Yeni Demo: ' . mb_substr((string) $d->salon_adi, 0, 40),
                'aciklama' => 'Demo hesap açıldı' . ($d->yetkili_telefon ? ' · ' . $d->yetkili_telefon : ''),
                'link'  => '/sistemyonetim/v2/salon/' . $d->id,
                'zaman' => \Carbon\Carbon::parse($d->created_at)->diffForHumans(),
            ];
        }

        return response()->json([
            'sayi' => count($bildirimler),
            'liste' => $bildirimler,
        ]);
    }

    /* ============================================================
     * CSV EXPORT
     * ============================================================ */
    private function csvDownload($filename, $headers, $rows)
    {
        $f = fopen('php://temp', 'w+');
        fputs($f, "\xEF\xBB\xBF"); // UTF-8 BOM (Excel Turkce karakterler icin)
        fputcsv($f, $headers, ';');
        foreach ($rows as $row) fputcsv($f, $row, ';');
        rewind($f);
        $csv = stream_get_contents($f);
        fclose($f);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function salonlarCsv(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);

        $query = Salonlar::query();
        if ($q = $request->get('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('salon_adi', 'like', "%$q%")
                  ->orWhere('telefon_1', 'like', "%$q%")
                  ->orWhere('yetkili_adi', 'like', "%$q%");
            });
        }
        if ($request->get('durum') === 'aktif') $query->where('askiya_alindi', 0);
        if ($request->get('durum') === 'askida') $query->where('askiya_alindi', 1);

        $salonlar = $query->orderBy('id', 'desc')->get();
        $rows = [];
        foreach ($salonlar as $s) {
            $rows[] = [
                $s->id,
                $s->salon_adi,
                optional($s->il)->il_adi,
                optional($s->ilce)->ilce_adi,
                $s->yetkili_adi,
                $s->yetkili_telefon,
                $s->telefon_1,
                SistemYoneticileri::where('id', $s->musteri_yetkili_id)->value('name'),
                $s->askiya_alindi ? 'Askıda' : 'Aktif',
                date('d.m.Y H:i', strtotime($s->created_at)),
            ];
        }

        Audit::log('csv_export', 'salon', null, 'Salon listesi (' . count($rows) . ')');

        return $this->csvDownload(
            'salonlar-' . date('Ymd-His') . '.csv',
            ['ID','Salon Adı','İl','İlçe','Yetkili','Yetkili Tel','İşletme Tel','Müşteri Temsilcisi','Durum','Kayıt Tarihi'],
            $rows
        );
    }

    public function aktiviteCsv(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $loglar = AuditLog::orderBy('id', 'desc')->limit(5000)->get();
        $rows = [];
        foreach ($loglar as $l) {
            $rows[] = [
                date('d.m.Y H:i:s', strtotime($l->created_at)),
                $l->user_name,
                $l->user_rol,
                $l->action,
                $l->target_type,
                $l->target_label,
                $l->aciklama,
                $l->ip,
            ];
        }
        Audit::log('csv_export', 'aktivite', null, 'Aktivite log (' . count($rows) . ')');
        return $this->csvDownload(
            'aktivite-log-' . date('Ymd-His') . '.csv',
            ['Zaman','Kullanıcı','Rol','İşlem','Hedef Tipi','Hedef','Açıklama','IP'],
            $rows
        );
    }

    public function ticketCsv(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $ticketlar = DestekTalebi::orderBy('id', 'desc')->get();
        $rows = [];
        foreach ($ticketlar as $t) {
            $rows[] = [
                $t->numara, $t->salon_adi, $t->konu, $t->kategori,
                $t->oncelik, $t->durum, $t->atanan_user_name, $t->olusturan_user_name,
                date('d.m.Y H:i', strtotime($t->created_at)),
                $t->ilk_yanit_tarihi ? date('d.m.Y H:i', strtotime($t->ilk_yanit_tarihi)) : '',
                $t->cozumlenme_tarihi ? date('d.m.Y H:i', strtotime($t->cozumlenme_tarihi)) : '',
            ];
        }
        Audit::log('csv_export', 'ticket', null, 'Ticket listesi (' . count($rows) . ')');
        return $this->csvDownload(
            'ticketlar-' . date('Ymd-His') . '.csv',
            ['Numara','Salon','Konu','Kategori','Öncelik','Durum','Atanan','Açan','Açılış','İlk Yanıt','Çözüm'],
            $rows
        );
    }

    /* ============================================================
     * TOPLU ISLEMLER (Salonlar)
     * ============================================================ */
    public function topluIslem(Request $request)
    {
        $this->gerektir(['super_admin', 'yonetici']);
        $ids = array_map('intval', array_filter((array) $request->input('ids', [])));
        if (empty($ids)) return redirect()->back()->with('hata', 'Salon seçilmedi.');

        $islem = $request->get('islem');
        $sayi = 0;

        if ($islem === 'mt_ata') {
            $mtId = $request->get('mt_id') ?: null;
            $sayi = Salonlar::whereIn('id', $ids)->update(['musteri_yetkili_id' => $mtId]);
            Audit::log('toplu_mt_ata', 'salon', null, "$sayi salon", null, ['ids' => $ids, 'mt_id' => $mtId]);
            return redirect()->back()->with('basari', "$sayi salonun müşteri temsilcisi güncellendi.");
        }

        if ($islem === 'askiya_al') {
            $sebep = $request->get('sebep') ?: 'Toplu askıya alma';
            $sayi = Salonlar::whereIn('id', $ids)->update([
                'askiya_alindi' => 1,
                'askiya_alma_sebebi' => $sebep,
                'askiya_alan_user_id' => $this->user()->id,
                'askiya_alma_tarihi' => date('Y-m-d H:i:s'),
            ]);
            Audit::log('toplu_askiya_al', 'salon', null, "$sayi salon", $sebep, ['ids' => $ids]);
            return redirect()->back()->with('basari', "$sayi salon askıya alındı.");
        }

        if ($islem === 'aktif_et') {
            $sayi = Salonlar::whereIn('id', $ids)->update([
                'askiya_alindi' => 0,
                'askiya_alma_sebebi' => null,
                'askiya_alan_user_id' => null,
                'askiya_alma_tarihi' => null,
            ]);
            Audit::log('toplu_aktif_et', 'salon', null, "$sayi salon", null, ['ids' => $ids]);
            return redirect()->back()->with('basari', "$sayi salon aktif edildi.");
        }

        // ============================================================
        // GECICI: TEST SALONU SILME (temizlik icin). Isin bitince bu blok
        // + bulk dropdown'daki 'sil' secenegi kaldirilacak. Sadece super_admin.
        // ============================================================
        if ($islem === 'sil') {
            if ($this->rol() !== 'super_admin') {
                return redirect()->back()->with('hata', 'Silme yetkisi yalnızca süper adminde.');
            }
            DB::beginTransaction();
            try {
                DB::table('salon_personelleri')->whereIn('salon_id', $ids)->delete();
                DB::table('salon_calisma_saatleri')->whereIn('salon_id', $ids)->delete();
                DB::table('salon_mola_saatleri')->whereIn('salon_id', $ids)->delete();
                DB::table('model_has_roles')->whereIn('salon_id', $ids)->delete();
                $sayi = Salonlar::whereIn('id', $ids)->delete();
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return redirect()->back()->with('hata', 'Silme başarısız: ' . $e->getMessage());
            }
            Audit::log('toplu_salon_sil', 'salon', null, "$sayi salon SILINDI (test)", null, ['ids' => $ids]);
            return redirect()->back()->with('basari', "$sayi salon silindi (test temizliği).");
        }

        return redirect()->back()->with('hata', 'Geçersiz işlem.');
    }

    /* ============================================================
     * SISTEM WHATSAPP (salon-bagimsiz bildirim oturumu)
     * ============================================================ */
    private function waService()
    {
        return app(\App\Services\WhatsAppService::class);
    }

    public function sistemWhatsapp()
    {
        $this->gerektir(['super_admin']);
        return view('sistemyonetim.v2.sistem-whatsapp', [
            'title'     => 'Sistem WhatsApp',
            'aktifMenu' => 'sistem-whatsapp',
            'ayar'      => \App\Services\SistemBildirim::ayarOku(),
        ]);
    }

    public function sistemWhatsappBaglat()
    {
        $this->gerektir(['super_admin']);
        return response()->json($this->waService()->startSession(\App\Services\SistemBildirim::SESSION));
    }

    public function sistemWhatsappQr()
    {
        $this->gerektir(['super_admin']);
        return response()->json($this->waService()->qr(\App\Services\SistemBildirim::SESSION));
    }

    public function sistemWhatsappStatus()
    {
        $this->gerektir(['super_admin']);
        return response()->json($this->waService()->status(\App\Services\SistemBildirim::SESSION));
    }

    public function sistemWhatsappCikis()
    {
        $this->gerektir(['super_admin']);
        $this->waService()->logout(\App\Services\SistemBildirim::SESSION);
        return redirect()->back()->with('basari', 'Sistem WhatsApp oturumu kapatıldı.');
    }

    public function sistemWhatsappAyar(Request $request)
    {
        $this->gerektir(['super_admin']);
        \App\Services\SistemBildirim::ayarYaz($request->get('numara'), $request->get('aktif') ? 1 : 0);
        return redirect()->back()->with('basari', 'Bildirim ayarı kaydedildi.');
    }

    public function sistemWhatsappTest()
    {
        $this->gerektir(['super_admin']);
        $r = \App\Services\SistemBildirim::gonder('✅ Test: Sistem bildirimi çalışıyor. (' . date('d.m.Y H:i') . ')');
        if (empty($r['ok'])) {
            return redirect()->back()->with('hata', 'Gönderilemedi — numara girip "aktif" işaretleyin ve kaydedin.');
        }
        return redirect()->back()->with('basari', 'Test mesajı gönderildi (WhatsApp + SMS).');
    }

    /* ============================================================
     * PROFIL & SIFRE DEGISTIR
     * ============================================================ */
    public function profil()
    {
        $u = $this->user();
        $sonGirisler = LoginLog::where('user_id', $u->id)->orderBy('id', 'desc')->limit(20)->get();
        $sonAktiviteler = AuditLog::where('user_id', $u->id)->orderBy('id', 'desc')->limit(30)->get();
        return view('sistemyonetim.v2.profil', [
            'title' => 'Profilim',
            'aktifMenu' => 'profil',
            'u' => $u,
            'sonGirisler' => $sonGirisler,
            'sonAktiviteler' => $sonAktiviteler,
        ]);
    }

    public function profilGuncelle(Request $request)
    {
        $u = $this->user();
        $this->validate($request, [
            'name'  => 'required|min:2',
            'email' => 'required|email|unique:sistemyoneticileri,email,' . $u->id,
        ]);
        $u->name = $request->name;
        $u->email = $request->email;
        $u->telefon = $request->telefon;
        $u->save();
        Audit::log('profil_guncelle', 'sistem_yoneticisi', $u->id, $u->name);
        return redirect('/sistemyonetim/v2/profil')->with('basari', 'Profil güncellendi.');
    }

    public function profilSifre(Request $request)
    {
        $u = $this->user();
        $this->validate($request, [
            'mevcut_sifre'      => 'required',
            'yeni_sifre'        => 'required|min:6|confirmed',
        ]);
        if (!\Hash::check($request->mevcut_sifre, $u->password)) {
            return redirect()->back()->withErrors(['mevcut_sifre' => 'Mevcut şifre hatalı.']);
        }
        $u->password = \Hash::make($request->yeni_sifre);
        $u->save();
        Audit::log('sifre_degistir', 'sistem_yoneticisi', $u->id, $u->name);
        return redirect('/sistemyonetim/v2/profil')->with('basari', 'Şifre güncellendi.');
    }
}
