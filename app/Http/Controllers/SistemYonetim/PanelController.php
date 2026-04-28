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
        $haftaOnce = date('Y-m-d', strtotime('-7 days'));

        $metrikler = [
            'toplam_salon'       => (int) Salonlar::count(),
            'aktif_salon'        => (int) Salonlar::where('askiya_alindi', 0)->count(),
            'askida_salon'       => (int) Salonlar::where('askiya_alindi', 1)->count(),
            'bugun_yeni_salon'   => (int) Salonlar::whereDate('created_at', $bugun)->count(),
            'hafta_yeni_salon'   => (int) Salonlar::where('created_at', '>=', $haftaOnce)->count(),
            'toplam_yetkili'     => (int) IsletmeYetkilileri::count(),
            'toplam_personel'    => (int) Personeller::count(),
            'toplam_randevu'     => (int) DB::table('randevular')->count(),
            'bugun_randevu'      => (int) DB::table('randevular')->whereDate('created_at', $bugun)->count(),
            'acik_ticket'        => (int) DestekTalebi::whereIn('durum', ['acik', 'islemde', 'bekliyor'])->count(),
            'acil_ticket'        => (int) DestekTalebi::whereIn('durum', ['acik', 'islemde'])->where('oncelik', 'acil')->count(),
            'aktif_ekip'         => (int) SistemYoneticileri::where('aktif', 1)->count(),
        ];

        $sonAktiviteler = AuditLog::orderBy('id', 'desc')->limit(15)->get();
        $bekleyenTicketlar = DestekTalebi::whereIn('durum', ['acik', 'islemde', 'bekliyor'])
            ->orderByRaw("FIELD(oncelik, 'acil','yuksek','orta','dusuk')")
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();
        $sonGirisler = LoginLog::orderBy('id', 'desc')->limit(10)->get();

        // 7 gunluk trend
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $g = date('Y-m-d', strtotime("-$i days"));
            $trend[] = [
                'tarih' => $g,
                'salon' => (int) Salonlar::whereDate('created_at', $g)->count(),
                'randevu' => (int) DB::table('randevular')->whereDate('created_at', $g)->count(),
            ];
        }

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

        $query = Salonlar::query();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('salon_adi', 'like', "%$q%")
                  ->orWhere('telefon_1', 'like', "%$q%")
                  ->orWhere('yetkili_telefon', 'like', "%$q%")
                  ->orWhere('yetkili_adi', 'like', "%$q%");
            });
        }
        if ($durum === 'aktif') $query->where('askiya_alindi', 0);
        if ($durum === 'askida') $query->where('askiya_alindi', 1);
        if ($musteriYetkiliId) $query->where('musteri_yetkili_id', $musteriYetkiliId);

        // Sadece super_admin tum salonlari gorur, destek temsilcisi atandiklarini gorur
        if ($this->rol() === 'destek') {
            $query->where('musteri_yetkili_id', $this->user()->id);
        }

        $perPage = (int) $request->get('per_page', 100);
        if (!in_array($perPage, [50, 100, 200, 500], true)) $perPage = 100;
        $salonlar = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->all());
        $musteriTemsilcileri = SistemYoneticileri::orderBy('name')->get();

        return view('sistemyonetim.v2.salonlar', [
            'title' => 'Salonlar',
            'aktifMenu' => 'salonlar',
            'salonlar' => $salonlar,
            'musteriTemsilcileri' => $musteriTemsilcileri,
            'q' => $q,
            'durum' => $durum,
            'mt' => $musteriYetkiliId,
        ]);
    }

    public function salonDetay($id)
    {
        $salon = Salonlar::findOrFail($id);
        if ($this->rol() === 'destek' && $salon->musteri_yetkili_id != $this->user()->id) {
            abort(403, 'Bu salonu görme yetkiniz yok.');
        }

        // Yetkililer: kanonik olarak personeller.yetkili_id uzerinden
        $personeller = Personeller::where('salon_id', $id)->get();
        $yetkiliIds = $personeller->whereNotNull('yetkili_id')->pluck('yetkili_id')->unique()->filter()->values();

        // Legacy: dogrudan salon_id ile bagli olabilen eski kayitlar
        $legacyIds = IsletmeYetkilileri::where('salon_id', $id)->pluck('id');
        $tumYetkiliIds = $yetkiliIds->merge($legacyIds)->unique();
        $yetkililer = $tumYetkiliIds->isNotEmpty()
            ? IsletmeYetkilileri::whereIn('id', $tumYetkiliIds)->get()
            : collect();
        $notlar = SalonNotu::where('salon_id', $id)->orderByDesc('pinned')->orderByDesc('id')->get();
        $impersonationGecmisi = ImpersonationLog::where('salon_id', $id)->orderByDesc('id')->limit(20)->get();
        $ticketlar = DestekTalebi::where('salon_id', $id)->orderByDesc('id')->limit(20)->get();
        $musteriTemsilcileri = SistemYoneticileri::orderBy('name')->get();

        $istatistik = [
            'toplam_randevu'   => (int) DB::table('randevular')->where('salon_id', $id)->count(),
            'bu_ay_randevu'    => (int) DB::table('randevular')->where('salon_id', $id)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
            'bu_ay_yeni_musteri' => 0,
            'whatsapp_aktif'   => $salon->whatsapp_aktif ? 1 : 0,
        ];
        try {
            $istatistik['bu_ay_yeni_musteri'] = (int) DB::table('musteri_portfoy')
                ->where('salon_id', $id)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count();
        } catch (\Exception $e) {}

        $saglik = \App\SistemYonetim\SaglikSkoru::hesapla($id);

        return view('sistemyonetim.v2.salon-detay', [
            'title' => $salon->salon_adi,
            'aktifMenu' => 'salonlar',
            'salon' => $salon,
            'yetkililer' => $yetkililer,
            'personeller' => $personeller,
            'notlar' => $notlar,
            'impersonationGecmisi' => $impersonationGecmisi,
            'ticketlar' => $ticketlar,
            'musteriTemsilcileri' => $musteriTemsilcileri,
            'istatistik' => $istatistik,
            'saglik' => $saglik,
        ]);
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

        // Kanonik: yetkili-salon iliskisi personeller.yetkili_id uzerinden kurulur
        $yetkili = null;
        $personel = Personeller::where('salon_id', $salonId)
            ->whereNotNull('yetkili_id')
            ->orderBy('id', 'asc')
            ->first();
        if ($personel) {
            $yetkili = IsletmeYetkilileri::find($personel->yetkili_id);
        }
        // Legacy fallback: bazi eski salonlarda isletmeyetkilileri.salon_id dolu olabilir
        if (!$yetkili) {
            $yetkili = IsletmeYetkilileri::where('salon_id', $salonId)->first();
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
            'ticket_id'     => $ticketId,
        ]);

        // simdi isletmeyonetim guard'ina giris yap
        Auth::guard('isletmeyonetim')->login($yetkili);

        return redirect('/isletmeyonetim');
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

        $kullanicilar = SistemYoneticileri::orderBy('name')->get();
        $aksiyonlar = AuditLog::distinct()->pluck('action')->filter()->values();

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
        $bildirimler = [];

        // Bana atanan + son 7 gun acilen ticketlar
        $tickets = DestekTalebi::whereIn('durum', ['acik', 'islemde', 'bekliyor'])
            ->where(function ($w) use ($u) {
                $w->where('atanan_user_id', $u->id)->orWhere('oncelik', 'acil');
            })
            ->where('created_at', '>=', date('Y-m-d', strtotime('-7 days')))
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

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

        return redirect()->back()->with('hata', 'Geçersiz işlem.');
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
