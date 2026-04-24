<?php

namespace App\Http\Controllers;

use App\CarkifelekSistemi;
use App\CarkifelekDilimleri;
use App\CarkifelekCevirmeLoglari;
use App\CarkifelekOdulleri;
use App\Randevular;
use App\Salonlar;
use App\SalonPuanlar;
use App\SalonPuanOdulleri;
use App\Hizmet_Kategorisi;
use App\Hizmetler;
use App\SalonTuru;
use App\Iller;
use App\Ilceler;
use App\User;
use App\Bildirimler;
use App\MusteriPortfoy;
use App\SmsDogrulamaKodlari;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CarkifelekMusteriController extends Controller
{
    /**
     * Layout (layout/layout.blade.php) için zorunlu olan ortak değişkenleri döner.
     */
    private function layoutData()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        return [
            'hizmetkategorileri' => Hizmet_Kategorisi::limit(8)->get(),
            'hizmetler'          => Hizmetler::all(),
            'salonturleri'       => SalonTuru::all(),
            'salonlar'           => Salonlar::limit(20)->get(),
            'iller'              => Iller::all(),
            'ilceler'            => Ilceler::all(),
            'salon'              => Salonlar::where('domain', $host)->first(),
        ];
    }

    /**
     * Müşterinin bu salondaki çevirme hakkı:
     *   Onaylanmış (durum=1) randevu sayısı – bu randevulardan log'a yazılmış olanlar
     */
    private function kalanHak($salonId, $userId)
    {
        $onaylanmisRandevuIds = Randevular::where('salon_id', $salonId)
            ->where('user_id', $userId)
            ->where('durum', Randevular::ONAYLANDI)
            ->pluck('id');

        if ($onaylanmisRandevuIds->isEmpty()) return [];

        $kullanilmis = CarkifelekCevirmeLoglari::whereIn('randevu_id', $onaylanmisRandevuIds)
            ->where('tip', '!=', 'tekrar_dene')
            ->pluck('randevu_id')
            ->toArray();

        return $onaylanmisRandevuIds->diff($kullanilmis)->values()->toArray();
    }

    /**
     * Müşteri bugün (yerel tarih) bu salonda çarkı çevirdi mi?
     * "tekrar_dene" sayılmaz — gerçek çekilen bir ödül var mı?
     */
    private function bugunCevirdi($salonId, $userId)
    {
        return CarkifelekCevirmeLoglari::where('salon_id', $salonId)
            ->where('user_id', $userId)
            ->where('tip', '!=', 'tekrar_dene')
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->exists();
    }

    /**
     * Çark sayfasını gösterir — misafir de erişebilir.
     */
    public function goster(Request $request, $salonId)
    {
        $this->tablolariGaranti();
        $salon = Salonlar::find($salonId);
        if (!$salon) abort(404, 'Salon bulunamadı.');

        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return view('carkifelek.pasif', array_merge($this->layoutData(), ['salon' => $salon]));
        }

        $dilimler = CarkifelekDilimleri::where('cark_id', $cark->id)->orderBy('sira')->get();
        if ($dilimler->count() < 2) {
            return view('carkifelek.pasif', array_merge($this->layoutData(), ['salon' => $salon]));
        }

        // Misafir veya üye — durumu ayır
        $isMisafir    = !Auth::check();
        $kullanilabilir = [];
        $bugunCevirdi   = false;

        if ($isMisafir) {
            // Session tabanlı "bugün çevirdi mi"
            $sessionKey = "cark_bugun_{$salonId}";
            $bugunCevirdi = $request->session()->get($sessionKey) === Carbon::today()->toDateString();
        } else {
            $kullanilabilir = $this->kalanHak($salonId, Auth::id());
            $bugunCevirdi   = $this->bugunCevirdi($salonId, Auth::id());
        }

        $yarin = Carbon::tomorrow()->format('d.m.Y H:i');

        $dilimlerJson = $dilimler->map(function ($d) {
            return [
                'id'    => $d->id,
                'ismi'  => $d->dilim_ismi,
                'renk'  => $d->renk_kodu,
                'tip'   => isset($d->tip) ? $d->tip : 'bos',
                'deger' => $d->deger !== null ? (float) $d->deger : null,
            ];
        })->values()->toArray();

        return view('carkifelek.cevir', array_merge($this->layoutData(), [
            'salon'           => $salon,
            'cark'            => $cark,
            'dilimler'        => $dilimler,
            'dilimlerJson'    => $dilimlerJson,
            'kalanHak'        => $isMisafir ? 1 : count($kullanilabilir),
            'randevuIdleri'   => $kullanilabilir,
            'bugunCevirdi'    => $bugunCevirdi,
            'yarinSaat'       => $yarin,
            'isMisafir'       => $isMisafir,
        ]));
    }

    /**
     * AJAX: Çarkı çevirir, ödülü işler, sonucu döner. Misafir de çevirebilir.
     * - Üye ise: direkt puan/kupon yaratılır.
     * - Misafir ise: ödül bilgisi session'a yazılır; müşteri kayıt olduktan sonra işlenir.
     */
    public function cevir(Request $request)
    {
        $this->tablolariGaranti();
        $isMisafir = !Auth::check();
        $userId    = $isMisafir ? null : Auth::id();
        $salonId   = (int) $request->input('salon_id');

        $cark = CarkifelekSistemi::where('salon_id', $salonId)->first();
        if (!$cark || !$cark->aktifmi) {
            return response()->json(['success' => false, 'message' => 'Çarkıfelek şu an aktif değil.']);
        }

        $randevuId = null;
        $sessionKey = "cark_bugun_{$salonId}";

        if ($isMisafir) {
            // Misafir: session'dan bugün çevirdi mi
            if ($request->session()->get($sessionKey) === Carbon::today()->toDateString()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bugün çarkı çevirdiniz. Yarın tekrar deneyebilirsiniz.',
                ]);
            }
        } else {
            $kullanilabilir = $this->kalanHak($salonId, $userId);
            if (empty($kullanilabilir)) {
                return response()->json(['success' => false, 'message' => 'Çevirme hakkınız bulunmuyor. Onaylanmış randevunuz olmalı.']);
            }
            if ($this->bugunCevirdi($salonId, $userId)) {
                $yarin = Carbon::tomorrow()->format('d.m.Y H:i');
                return response()->json([
                    'success' => false,
                    'message' => 'Bugün çarkı çevirdiniz. Bir sonraki çevirme: ' . $yarin . ' veya yeni onaylı randevunuzdan sonra.',
                ]);
            }
            $randevuId = $kullanilabilir[0];
        }

        $dilimler = CarkifelekDilimleri::where('cark_id', $cark->id)->orderBy('sira')->get();
        if ($dilimler->count() < 2) {
            return response()->json(['success' => false, 'message' => 'Çark henüz hazırlanmamış.']);
        }

        $secilen = $this->olasilikIleSec($dilimler);
        if (!$secilen) {
            return response()->json(['success' => false, 'message' => 'Dilim seçilemedi.']);
        }

        $secilenIndex = $dilimler->search(function ($d) use ($secilen) {
            return $d->id === $secilen->id;
        });

        // Üye: direkt işle; Misafir: session'a pending_odul yaz, kayıt sonrası işlenir
        $odulKodu = null;
        $kayitGerekli = false;

        if ($isMisafir) {
            $hasPrize = in_array($secilen->tip, ['puan', 'hizmet_indirimi', 'urun_indirimi']) && $secilen->deger;
            $kayitGerekli = $hasPrize;

            // Log kaydet — misafir için user_id=0 (kayıt sonrası gerçek user_id'ye güncellenir)
            $logData = [
                'cark_id'     => $cark->id,
                'salon_id'    => $salonId,
                'user_id'     => 0,
                'randevu_id'  => null,
                'dilim_id'    => $secilen->id,
                'tip'         => $secilen->tip,
                'deger'       => $secilen->deger,
                'dilim_ismi'  => $secilen->dilim_ismi,
            ];
            // session_id/misafir_ip migration'ı geçtiyse yaz
            if (Schema::hasColumn('carkifelek_cevirme_loglari', 'session_id')) {
                $logData['session_id'] = $request->session()->getId();
            }
            if (Schema::hasColumn('carkifelek_cevirme_loglari', 'misafir_ip')) {
                $logData['misafir_ip'] = $request->ip();
            }
            CarkifelekCevirmeLoglari::create($logData);

            // Bugün çevirdi işareti (tekrar_dene hariç)
            if ($secilen->tip !== 'tekrar_dene') {
                $request->session()->put($sessionKey, Carbon::today()->toDateString());
            }

            // Pending ödül session'a
            if ($hasPrize) {
                $request->session()->put('cark_pending_odul', [
                    'salon_id'     => $salonId,
                    'cark_id'      => $cark->id,
                    'dilim_id'     => $secilen->id,
                    'tip'          => $secilen->tip,
                    'deger'        => (float) $secilen->deger,
                    'baslik'       => $this->baslikUret($secilen),
                    'dilim_ismi'   => $secilen->dilim_ismi,
                    'created_at'   => Carbon::now()->timestamp,
                ]);
            }
        } else {
            // Üye — atomik işlem
            $sonuc = DB::transaction(function () use ($cark, $secilen, $salonId, $userId, $randevuId) {
                $log = CarkifelekCevirmeLoglari::create([
                    'cark_id'     => $cark->id,
                    'salon_id'    => $salonId,
                    'user_id'     => $userId,
                    'randevu_id'  => $randevuId,
                    'dilim_id'    => $secilen->id,
                    'tip'         => $secilen->tip,
                    'deger'       => $secilen->deger,
                    'dilim_ismi'  => $secilen->dilim_ismi,
                ]);
                $odul = null;
                if ($secilen->tip === 'puan' && $secilen->deger) {
                    $puanKaydi = SalonPuanlar::firstOrNew(['salon_id' => $salonId, 'user_id' => $userId]);
                    $puanKaydi->puan = ((float) $puanKaydi->puan) + (float) $secilen->deger;
                    $puanKaydi->save();
                } elseif (in_array($secilen->tip, ['hizmet_indirimi', 'urun_indirimi']) && $secilen->deger) {
                    $odul = CarkifelekOdulleri::create([
                        'log_id'            => $log->id,
                        'salon_id'          => $salonId,
                        'user_id'           => $userId,
                        'kod'               => strtoupper(Str::random(8)),
                        'tip'               => $secilen->tip,
                        'deger'             => $secilen->deger,
                        'baslik'            => $this->baslikUret($secilen),
                        'gecerlilik_tarihi' => Carbon::now()->addDays(30)->toDateString(),
                    ]);
                }
                return compact('log', 'odul');
            });
            $odulKodu = $sonuc['odul']->kod ?? null;
        }

        return response()->json([
            'success'      => true,
            'dilimIndex'   => (int) $secilenIndex,
            'dilim'        => [
                'id'     => $secilen->id,
                'ismi'   => $secilen->dilim_ismi,
                'tip'    => $secilen->tip,
                'deger'  => $secilen->deger,
                'baslik' => $this->baslikUret($secilen),
            ],
            'odulKodu'     => $odulKodu,
            'kayitGerekli' => $kayitGerekli,
            'isMisafir'    => $isMisafir,
            'kalanHak'     => $isMisafir ? 0 : max(0, count($kullanilabilir) - 1),
        ]);
    }

    /**
     * Çarkıfelek sisteminin ihtiyaç duyduğu tablolar yoksa runtime'da oluşturur
     * (migration çalıştırılamadığı ortamlar için güvence).
     */
    private function tablolariGaranti()
    {
        if (!Schema::hasTable('sms_dogrulama_kodlari')) {
            Schema::create('sms_dogrulama_kodlari', function ($table) {
                $table->increments('id');
                $table->string('telefon', 20);
                $table->string('kod', 6);
                $table->string('ip', 45)->nullable();
                $table->string('amac', 50)->default('cark_kayit');
                $table->timestamp('son_gecerlilik');
                $table->tinyInteger('dogrulandi')->default(0);
                $table->timestamps();
                $table->index(['telefon', 'amac']);
                $table->index('son_gecerlilik');
            });
        }
        if (!Schema::hasTable('carkifelek_cevirme_loglari')) {
            Schema::create('carkifelek_cevirme_loglari', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('cark_id');
                $table->unsignedInteger('salon_id');
                $table->unsignedInteger('user_id')->default(0);
                $table->string('session_id', 100)->nullable();
                $table->string('misafir_ip', 45)->nullable();
                $table->unsignedInteger('randevu_id')->nullable();
                $table->unsignedInteger('dilim_id')->nullable();
                $table->string('tip', 50)->default('bos');
                $table->decimal('deger', 10, 2)->nullable();
                $table->string('dilim_ismi', 150)->nullable();
                $table->timestamps();
                $table->index(['salon_id', 'user_id']);
                $table->index('randevu_id');
            });
        } else {
            // Eski tabloda eksik kolonlar varsa ekle
            Schema::table('carkifelek_cevirme_loglari', function ($table) {
                if (!Schema::hasColumn('carkifelek_cevirme_loglari', 'session_id')) {
                    $table->string('session_id', 100)->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('carkifelek_cevirme_loglari', 'misafir_ip')) {
                    $table->string('misafir_ip', 45)->nullable()->after('session_id');
                }
            });
        }
        if (!Schema::hasTable('carkifelek_odulleri')) {
            Schema::create('carkifelek_odulleri', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('log_id')->nullable();
                $table->unsignedInteger('salon_id');
                $table->unsignedInteger('user_id');
                $table->string('kod', 12)->unique();
                $table->string('tip', 50);
                $table->decimal('deger', 10, 2);
                $table->string('baslik', 150)->nullable();
                $table->tinyInteger('kullanildi')->default(0);
                $table->timestamp('kullanim_tarihi')->nullable();
                $table->date('gecerlilik_tarihi')->nullable();
                $table->timestamps();
                $table->index(['salon_id', 'user_id']);
                $table->index('kullanildi');
            });
        }
        if (!Schema::hasTable('salon_puan_odulleri')) {
            Schema::create('salon_puan_odulleri', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('salon_id');
                $table->integer('puan_esigi');
                $table->string('baslik', 150);
                $table->string('aciklama', 300)->nullable();
                $table->string('tip', 50);
                $table->decimal('deger', 10, 2)->nullable();
                $table->tinyInteger('aktif')->default(1);
                $table->integer('sira')->default(0);
                $table->timestamps();
                $table->index(['salon_id', 'aktif']);
            });
        }
    }

    /**
     * AJAX: Misafir için telefona SMS doğrulama kodu gönderir.
     */
    public function smsKodGonder(Request $request)
    {
        $this->tablolariGaranti();

        $telefon = preg_replace('/[^0-9]/', '', $request->input('telefon', ''));
        $ad      = trim($request->input('ad', ''));
        $soyad   = trim($request->input('soyad', ''));

        if (strlen($telefon) === 11 && $telefon[0] === '0') {
            $telefon = substr($telefon, 1); // başındaki 0'ı at
        }
        if (strlen($telefon) !== 10 || $telefon[0] !== '5') {
            return response()->json(['success' => false, 'message' => 'Geçerli bir cep telefon numarası girin (5XX...).']);
        }
        if ($ad === '' || $soyad === '') {
            return response()->json(['success' => false, 'message' => 'Ad ve soyad zorunlu.']);
        }

        // Pending ödül yoksa neden kayıt yapalım?
        if (!$request->session()->has('cark_pending_odul')) {
            return response()->json(['success' => false, 'message' => 'Önce çarkı çevirmelisiniz.']);
        }

        // Rate limit — aynı numaraya 60 saniye içinde 1 kere
        $sonKod = SmsDogrulamaKodlari::where('telefon', $telefon)
            ->where('amac', 'cark_kayit')
            ->orderByDesc('created_at')->first();
        if ($sonKod && Carbon::parse($sonKod->created_at)->diffInSeconds(Carbon::now()) < 60) {
            return response()->json(['success' => false, 'message' => 'Çok sık deniyorsunuz. Lütfen 1 dakika bekleyin.']);
        }

        $kod = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        SmsDogrulamaKodlari::create([
            'telefon'        => $telefon,
            'kod'            => $kod,
            'ip'             => $request->ip(),
            'amac'           => 'cark_kayit',
            'son_gecerlilik' => Carbon::now()->addMinutes(5),
            'dogrulandi'     => 0,
        ]);

        // Kayıt sırasında kullanılacak isim bilgisini session'a yaz
        $request->session()->put('cark_kayit_bilgi', compact('ad', 'soyad', 'telefon'));

        // Gerçek SMS gönderimi — Salon SMS ayarları tanımlıysa
        $pending = $request->session()->get('cark_pending_odul');
        $salonId = $pending['salon_id'] ?? null;
        $gonderildi = false;
        if ($salonId) {
            try {
                $salon = Salonlar::find($salonId);
                if ($salon && $salon->sms_user_name && $salon->sms_secret) {
                    require_once app_path('VoiceTelekom/Sms/SmsApi.php');
                    require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
                    require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
                    $smsApi = new \SmsApi('smsvt.voicetelekom.com', $salon->sms_user_name, $salon->sms_secret);
                    $req = new \SendSingleSms();
                    $req->title   = 'Doğrulama';
                    $req->content = $salon->salon_adi . ' çarkıfelek doğrulama kodunuz: ' . $kod;
                    $req->number  = '90' . $telefon;
                    $req->encoding = 0;
                    $req->customID = 'cark_' . date('Ymd_His') . '_' . substr(md5(microtime()), 0, 8);
                    $req->sender   = $salon->sms_baslik ?: 'RANDEVUMCEPTE';
                    $req->skipAhsQuery = true;
                    $smsApi->sendSingleSms($req);
                    $gonderildi = true;
                }
            } catch (\Exception $e) {
                Log::warning('Cark SMS gönderilemedi: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Telefonunuza 4 haneli kod gönderildi. Lütfen kodu girin.',
            // DEV AMAÇLI: SMS sağlayıcı yoksa kodu response'ta dön (test için). Prod'da kaldır.
            'dev_kod' => $gonderildi ? null : $kod,
        ]);
    }

    /**
     * AJAX: SMS kodunu doğrular → User create → Auth::login → pending ödülü işler.
     * İşletmeye bildirim oluşturur.
     */
    public function smsKodDogrula(Request $request)
    {
        $this->tablolariGaranti();

        $kod = trim($request->input('kod', ''));
        $bilgi = $request->session()->get('cark_kayit_bilgi');
        $pending = $request->session()->get('cark_pending_odul');

        if (!$bilgi || !$pending) {
            return response()->json(['success' => false, 'message' => 'Oturum süresi doldu. Baştan başlayın.']);
        }

        $telefon = $bilgi['telefon'];

        $kayit = SmsDogrulamaKodlari::where('telefon', $telefon)
            ->where('amac', 'cark_kayit')
            ->where('kod', $kod)
            ->where('dogrulandi', 0)
            ->where('son_gecerlilik', '>=', Carbon::now())
            ->orderByDesc('created_at')->first();

        if (!$kayit) {
            return response()->json(['success' => false, 'message' => 'Kod hatalı veya süresi dolmuş.']);
        }

        $kayit->dogrulandi = 1;
        $kayit->save();

        // Mevcut kullanıcı var mı?
        $user = User::where('cep_telefon', $telefon)->first();
        $yeniUyelik = false;

        if (!$user) {
            $user = User::create([
                'name'        => trim($bilgi['ad'] . ' ' . $bilgi['soyad']),
                'cep_telefon' => $telefon,
                'password'    => Hash::make(Str::random(16)), // kullanıcı şifresi SMS akışına kurulmamış
            ]);
            $yeniUyelik = true;
        }

        Auth::login($user);

        // MusteriPortfoy ilişkisi (yoksa oluştur)
        try {
            if (class_exists(MusteriPortfoy::class)) {
                MusteriPortfoy::firstOrCreate([
                    'user_id'  => $user->id,
                    'salon_id' => $pending['salon_id'],
                ], ['aktif' => 1]);
            }
        } catch (\Exception $e) {
            Log::warning('Cark MusteriPortfoy oluşturulamadı: ' . $e->getMessage());
        }

        // Pending ödülü kupona/puana dönüştür
        $odulKodu = null;
        DB::transaction(function () use (&$odulKodu, $pending, $user) {
            if ($pending['tip'] === 'puan' && $pending['deger']) {
                $puanKaydi = SalonPuanlar::firstOrNew([
                    'salon_id' => $pending['salon_id'],
                    'user_id'  => $user->id,
                ]);
                $puanKaydi->puan = ((float) $puanKaydi->puan) + (float) $pending['deger'];
                $puanKaydi->save();
            } elseif (in_array($pending['tip'], ['hizmet_indirimi', 'urun_indirimi'])) {
                $kupon = CarkifelekOdulleri::create([
                    'log_id'            => null,
                    'salon_id'          => $pending['salon_id'],
                    'user_id'           => $user->id,
                    'kod'               => strtoupper(Str::random(8)),
                    'tip'               => $pending['tip'],
                    'deger'             => $pending['deger'],
                    'baslik'            => $pending['baslik'],
                    'gecerlilik_tarihi' => Carbon::now()->addDays(30)->toDateString(),
                ]);
                $odulKodu = $kupon->kod;
            }

            // Log'daki misafir kayıtları (user_id=0) bu user'a bağla
            if (Schema::hasColumn('carkifelek_cevirme_loglari', 'session_id')) {
                CarkifelekCevirmeLoglari::where('session_id', session()->getId())
                    ->where('user_id', 0)
                    ->update(['user_id' => $user->id]);
            }
        });

        // İşletmeye bildirim — yalnızca YENİ üyelikse
        if ($yeniUyelik) {
            try {
                $bildirim = new Bildirimler();
                $bildirim->salon_id    = $pending['salon_id'];
                $bildirim->user_id     = $user->id;
                $bildirim->baslik      = '🤖 Yeni Müşteri Kaydı (Yapay Zeka)';
                $bildirim->aciklama    = 'Çarkıfelek üzerinden yeni bir müşteri kaydedildi: '
                                       . $user->name . ' (0' . $telefon . '). Pending ödül: ' . $pending['baslik'];
                $bildirim->url         = '/isletmeyonetim/carkkazananlar';
                $bildirim->img_src     = null;
                $bildirim->tarih_saat  = Carbon::now();
                $bildirim->okundu      = 0;
                $bildirim->butonlar    = json_encode([]);
                $bildirim->save();
            } catch (\Exception $e) {
                Log::warning('Cark bildirim kaydedilemedi: ' . $e->getMessage());
            }
        }

        // Session temizle
        $request->session()->forget('cark_pending_odul');
        $request->session()->forget('cark_kayit_bilgi');

        return response()->json([
            'success'  => true,
            'odulKodu' => $odulKodu,
            'baslik'   => $pending['baslik'],
            'tip'      => $pending['tip'],
            'yeniUye'  => $yeniUyelik,
        ]);
    }

    /**
     * Müşterinin salon bazlı puan merdiveni sayfası.
     */
    public function puanOdullerim(Request $request, $salonId = null)
    {
        if (!Auth::check()) return redirect('/login');

        $userId = Auth::id();

        // Müşterinin puanı olan salonları getir — biri istenmişse onu seç
        $puanKayitlari = SalonPuanlar::where('user_id', $userId)
            ->where('puan', '>', 0)
            ->get();

        if ($puanKayitlari->isEmpty() && !$salonId) {
            return view('carkifelek.puan_odullerim_bos', $this->layoutData());
        }

        $salonId = $salonId ? (int) $salonId : (int) $puanKayitlari->first()->salon_id;
        $salon   = Salonlar::find($salonId);
        if (!$salon) abort(404);

        $puanBakiyesi = (float) (SalonPuanlar::where('user_id', $userId)->where('salon_id', $salonId)->value('puan') ?: 0);

        $odulSeviyeleri = SalonPuanOdulleri::where('salon_id', $salonId)
            ->where('aktif', 1)
            ->orderBy('puan_esigi')
            ->get();

        $tumSalonlar = Salonlar::whereIn('id', $puanKayitlari->pluck('salon_id'))->get()->keyBy('id');

        return view('carkifelek.puan_odullerim', array_merge($this->layoutData(), [
            'salon'          => $salon,
            'salonId'        => $salonId,
            'puanBakiyesi'   => $puanBakiyesi,
            'odulSeviyeleri' => $odulSeviyeleri,
            'puanKayitlari'  => $puanKayitlari,
            'tumSalonlar'    => $tumSalonlar,
        ]));
    }

    /**
     * AJAX: Müşteri bir puan ödülü talep ediyor.
     * Puanı düşer, kupon oluşur.
     */
    public function puanOdulTalep(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Giriş yapmalısınız.'], 401);
        }

        $userId  = Auth::id();
        $salonId = (int) $request->input('salon_id');
        $odulId  = (int) $request->input('odul_id');

        $odul = SalonPuanOdulleri::where('id', $odulId)
            ->where('salon_id', $salonId)
            ->where('aktif', 1)
            ->first();
        if (!$odul) {
            return response()->json(['success' => false, 'message' => 'Ödül bulunamadı veya pasif.']);
        }

        $puanKaydi = SalonPuanlar::where('salon_id', $salonId)->where('user_id', $userId)->first();
        $mevcutPuan = $puanKaydi ? (float) $puanKaydi->puan : 0;

        if ($mevcutPuan < $odul->puan_esigi) {
            return response()->json([
                'success' => false,
                'message' => 'Yetersiz puan. Gerekli: ' . $odul->puan_esigi . ', mevcut: ' . ((int) $mevcutPuan),
            ]);
        }

        $sonuc = \DB::transaction(function () use ($puanKaydi, $odul, $salonId, $userId) {
            // Puanı düş
            $puanKaydi->puan = ((float) $puanKaydi->puan) - (float) $odul->puan_esigi;
            $puanKaydi->save();

            // Kupon tipi: hizmet/ürün indirimi veya "hediye" (de hizmet_indirimi gibi davranır ama başlık farklı)
            $kuponTip = in_array($odul->tip, ['hizmet_indirimi', 'urun_indirimi']) ? $odul->tip : 'hizmet_indirimi';

            $kupon = CarkifelekOdulleri::create([
                'log_id'            => null,
                'salon_id'          => $salonId,
                'user_id'           => $userId,
                'kod'               => strtoupper(\Illuminate\Support\Str::random(8)),
                'tip'               => $kuponTip,
                'deger'             => $odul->deger ?: 0,
                'baslik'            => $odul->baslik,
                'gecerlilik_tarihi' => \Carbon\Carbon::now()->addDays(60)->toDateString(),
            ]);

            return $kupon;
        });

        return response()->json([
            'success'      => true,
            'kod'          => $sonuc->kod,
            'baslik'       => $sonuc->baslik,
            'kalanPuan'    => (int) ($puanKaydi->puan),
        ]);
    }

    /**
     * Müşterinin kazandığı (kullanılmamış) ödüller.
     */
    public function odullerim()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $odullerim = CarkifelekOdulleri::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('carkifelek.odullerim', array_merge($this->layoutData(), ['odullerim' => $odullerim]));
    }

    /* ───────── yardımcılar ───────── */

    private function olasilikIleSec($dilimler)
    {
        $toplam = $dilimler->sum('dilim_olasilik');
        if ($toplam <= 0) return null;

        $rand     = mt_rand(1, $toplam);
        $birikim  = 0;
        foreach ($dilimler as $d) {
            $birikim += (int) $d->dilim_olasilik;
            if ($rand <= $birikim) return $d;
        }
        return $dilimler->last();
    }

    private function baslikUret($d)
    {
        switch ($d->tip) {
            case 'puan':            return $d->deger ? ((int) $d->deger) . ' Puan' : 'Puan';
            case 'hizmet_indirimi': return $d->deger ? '%' . ((int) $d->deger) . ' Hizmet İndirimi' : 'Hizmet İndirimi';
            case 'urun_indirimi':   return $d->deger ? '%' . ((int) $d->deger) . ' Ürün İndirimi'   : 'Ürün İndirimi';
            case 'tekrar_dene':     return 'Tekrar Dene';
            case 'bos':             return 'Boş';
            default:                return $d->dilim_ismi ?: 'Ödül';
        }
    }
}
