<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Salonlar;
use App\SalonSantralAyarlari;
use App\SatisOrtakligiModel\SatisOrtagiOdemeTalepleri;
use App\SatisOrtakligiModel\Bankalar;
use App\SatisOrtakligiModel\Musteri_Formlari_Hizmetler;
use App\SatisOrtakligiModel\Satis_Ortagi_Banka_Hesaplari;
use App\SatisOrtakligiModel\SatisOrtaklari;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
 use App\SatisOrtakligiModel\Musteri_Formlari;
 use Illuminate\Support\Facades\DB;
 use App\Imports\SalonImport;
 use App\IsletmeYetkilileri;
 use App\Adisyonlar;
 use App\AdisyonHizmetler;
 use App\AdisyonUrunler;
 use App\AdisyonPaketler;
 use App\Randevular;
 use App\RandevuHizmetler;
 use App\OnGorusmeler;
 use App\Hizmetler;
 use App\Urunler;
 use App\Paketler;
 use App\AdisyonPaketSeanslar;
 use App\Ajanda;
 use App\Arsiv;
use App\Etkinlikler;
 use App\EtkinlikKatilimcilari;
 use App\KampanyaKatilimcilari;
 use App\KampanyaYonetimi;
use Carbon\Carbon;
use App\SalonCalismaSaatleri;
use App\SalonMolaSaatleri;
use App\SalonSMSAyarlari;
use App\Personeller;
use App\PaketHizmetler;
use App\Uyelik;
use App\Bildirimler;
use App\BildirimKimlikleri;
use App\SatisOrtakligiModel\Telefon_Randevulari;
use Hash;
use Excel;
use Illuminate\Support\Facades\Mail;
use App\Ilceler;
use App\Iller;
  
class SatisOrtakligiController extends Controller
{
   
    public function __construct()
    {

         $this->middleware('auth:satisortakligi');
            
    }

    
    public function index(Request $request)
    {
         
        $musteriler = self::musterilerigetir($request);
        $aktif_musteriler = self::filtreli_musteri_listesi('aktifsuresibitenler','');
        $pasif_musteriler = self::filtreli_musteri_listesi('pasif','');
        $demosu_olan_musteriler = self::filtreli_musteri_listesi('demolar','');

        $talep_edilen_hakedis = SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->where('satis_ortagi_hakedis_odeme_durumu_id',0)->sum('odeme_miktari');
        $gecmis_odemeler = SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->where('satis_ortagi_hakedis_odeme_durumu_id',1)->sum('odeme_miktari');
        $guncel_musteriler_yeni_satis = Musteri_Formlari::whereBetween('satis_tarihi', [Carbon::now()->subMonths(12)->startOfMonth()->toDateTimeString(), Carbon::now()->endOfDay()->toDateTimeString()])->whereIn('satis_ortagi_hakedis_odeme_durumu_id', [1, 3])->where('durum_id', 7)->where('satis_ortagi_id', Auth::user()->id)->get();  
        $hakedis = self::hakedis_hesaplama($request,$guncel_musteriler_yeni_satis);  
        return view('satisortakligi.dashboard',['musteri_bilgileri'=>$guncel_musteriler_yeni_satis,'musteriler'=> $musteriler,'talep_edilen_hakedis' => $talep_edilen_hakedis,'gecmis_odemeler' => $gecmis_odemeler,'aktif_musteriler'=>$aktif_musteriler,'pasif_musteriler'=>$pasif_musteriler,'sayfa_baslik'=>'Özet','demosu_olan_musteriler'=>$demosu_olan_musteriler,'pageindex'=>1,'hakedis'=>$hakedis,'bildirimler'=>self::bildirimler($request)]);

    }
    public function musterilerigetir(Request $request)
    {
        return Salonlar::leftjoin('satis_ortaklari','salonlar.satis_ortagi_id','=','satis_ortaklari.id')->where('salonlar.satis_ortagi_id',Auth::user()->id)->get();
    }
    public function yeni_musteri_girisi(Request $request)
    {
        return view('satisortakligi.yeni-musteri',['sayfa_baslik'=>'Yeni Müşteri/Firma Kaydı','pageindex'=>2,'bildirimler'=>self::bildirimler($request)]);
    }
    public function yeniformkaydi(Request $request,$musteri_bilgisi)
    {
        $form = new Musteri_Formlari();
        $form->salon_id = $musteri_bilgisi->id;
        $form->satis_ortagi_id = Auth::user()->id;
        $form->satis_ortagi_hakedis_odeme_durumu_id = 3;
        $form->durum_id = 1;
        $form->save();
        return $form;
    }
    public function yeni_musteri_ekle(Request $request)
    {
        // Validate request to ensure required fields are present
      
    
        // Format phone number for consistent checking
        $formattedPhone = self::telefon_no_format_duzenle($request->yetkili_telefon);
    
        // Check if a customer with the same phone number already exists
        $existingCustomer = Salonlar::where('yetkili_telefon', $formattedPhone)->first();
    
        if ($existingCustomer) {
            $hasDemoAccount = $existingCustomer->demo_hesabi ?? 0;
    
            // Check if a form exists for this salon
            $form = Musteri_Formlari::where('salon_id', $existingCustomer->id)->orderBy('id', 'desc')->first();
    
            // If no form exists, create one
            if (!$form) {
                $form = self::yeniformkaydi($request, $existingCustomer);
            }
    
            return response()->json([
                'status' => 'warning',
                'has_demo_account' => $hasDemoAccount,
                'message' => $hasDemoAccount
                    ? 'Bu müşteri zaten sistemde bir demo hesabı var!'
                    : 'Bu müşteri zaten sistemde kayıtlı! Demo hesabı açabilirsiniz.',
                'salonid' => $existingCustomer->id,
                'formid' => $form->id,
            ]);
        }
    
        // Create a new customer
        $musteri_bilgisi = new Salonlar();
        $musteri_bilgisi->yetkili_adi = $request->yetkili_adi;
        $musteri_bilgisi->yetkili_telefon = $formattedPhone;
        $musteri_bilgisi->salon_adi = $request->salon_adi;
        $musteri_bilgisi->adres = $request->adres;
        $musteri_bilgisi->telefon_1 = $request->telefon_1;
        $musteri_bilgisi->telefon_2 = $request->telefon_2;
        $musteri_bilgisi->telefon_3 = $request->telefon_3;
        $musteri_bilgisi->satis_ortagi_id = Auth::user()->id;
    
        if ($request->pasif_ortak != 0) {
            $musteri_bilgisi->pasif_ortak_id = $request->pasif_ortak;
        }
    
        $musteri_bilgisi->satis_ortagi_notu = $request->satis_ortagi_notu;
        $musteri_bilgisi->aktif = false;
        $musteri_bilgisi->hesap_acildi = false;
        $musteri_bilgisi->demo_hesabi = 0; // Default value
        $musteri_bilgisi->save();
    
        // Create a new form for the new customer
        $form = self::yeniformkaydi($request, $musteri_bilgisi);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Müşteri sisteme başarı ile eklendi',
            'salonid' => $musteri_bilgisi->id,
            'formid' => $form->id,
        ], 200);
    }
    public function musteri_excelden_aktar(Request $request){

        $returnstr = "";

        if ($request->hasFile('excel_dosyasi_yeni')) {
            $file = $request->file('excel_dosyasi_yeni');
            // Dosyayı belirli bir yere kaydetme
            $hedef = $file->storeAs('public/satisortakligipanel/musterilisteleri', str_replace(' ', '_', $file->getClientOriginalName()));

            // Excel verilerini işle
            $import = new SalonImport($request->pasif_ortak);
            Excel::import($import, $hedef);

            $count = $import->getImportedCount();
            $uncount = $import->getNotImportedCount();

            $returnstr =  $count.' adet müşteri sisteme başarı ile aktarıldı.';

        } 
        else {
            $returnstr = 'Lütfen excel dosyası yükleyiniz';
        }
         return $returnstr;


        }

    public function pasif_musteriler(Request $request){

     $pasif_musteriler =  self::filtreli_musteri_listesi('pasif','');
      return view('satisortakligi.pasif-musteriler',['pasif_musteriler'=> $pasif_musteriler,'sayfa_baslik'=>'Pasif Müşteriler','pageindex'=>3,'bildirimler'=>self::bildirimler($request)]);

    }
    public function demosu_olan_musteriler(Request $request){

     $demosu_olan_musteriler =  self::filtreli_musteri_listesi('demolar','');
      return view('satisortakligi.demosu-olan-musteriler',['demosu_olan_musteriler'=> $demosu_olan_musteriler,'sayfa_baslik'=>'Demosu Olan Müşteriler','pageindex'=>7,'bildirimler'=>self::bildirimler($request)]);

    }
    public function satis_yapilamayan_musteriler(Request $request){

     $satis_yapilamayan_musteriler =  self::filtreli_musteri_listesi('satisolmamis','');
      return view('satisortakligi.satis-yapilamayan-musteriler',['satis_yapilamayan_musteriler'=> $satis_yapilamayan_musteriler,'sayfa_baslik'=>'Satış Yapılamayan Müşteriler','pageindex'=>10,'bildirimler'=>self::bildirimler($request)]);

    }
   public function filtreli_musteri_listesi($querydurum, $pasif_ortak)
   {
    $query = Musteri_Formlari::query();
    $query->where('satis_ortagi_id',Auth::user()->id);
    // Pasif ortak koşulu
    if ($pasif_ortak != '') {
        $query->whereHas('salon', function ($salonQuery) use ($pasif_ortak) {
            $salonQuery->where('pasif_ortak_id', $pasif_ortak);
        });
    }

    // Duruma göre filtreleme
    if ($querydurum == "pasif") {
        $query->where('durum_id', '!=', 7)
            ->whereHas('salon', function ($salonQuery) {
                $salonQuery->where('hesap_acildi', '!=', true); // Hesap açılmayanlar
            });
    } elseif ($querydurum == 'aktif') {
        $query->where('durum_id', '=', 7);
    } elseif ($querydurum == 'demolar') {
        $query->where('durum_id', '!=', 7)
            ->whereHas('salon', function ($q) {
                $q->where('demo_hesabi', true)
                    ->where('hesap_acildi', true);
            });
    } elseif ($querydurum == 'satisolmamis') {
        $query->where('durum_id', '!=', 7)
            ->whereHas('salon', function ($q) {
                $q->where('uyelik_bitis_tarihi', '<=', date('Y-m-d'))
                    ->where('demo_hesabi', true);
            });
    } elseif ($querydurum == 'aktifsuresibitenler') {
        $query->where('durum_id', '7')
            ->whereHas('salon', function ($salonQuery) {
                $salonQuery->whereDate('uyelik_bitis_tarihi', '<=', now()->addMonth()); // 1 ay içinde bitiş tarihi olanları alıyoruz
            });
    }

    // Veriyi çek ve map ile işleyerek döndür
    return $query->with('salon') // İlişkili salon verisini yükle
        ->get() // Verileri çek
        ->map(function ($musteri) use ($querydurum) {
            // Durum butonlarını belirle
            $durum = '';
            $menu = '';
            Log::info('', ['querydurum' => $querydurum]);
            if ($musteri->durum_id != 7 && $musteri->salon->hesap_acildi != true){
                $durum = '<button class="btn btn-dark btn-sm" style="background-color:grey">Pasif</button>';
            }
            elseif ($musteri->durum_id == 6){
                $durum = '<button class="btn btn-warning btn-sm">Ödeme Bekleniyor</button>';
            }
            elseif ($musteri->durum_id == 7){
                $durum = '<button class="btn btn-success btn-sm">Aktif</button>';
            }
            elseif ($musteri->durum_id != 7 && $musteri->salon->demo_hesabi == true && $musteri->salon->hesap_acildi == true){
                $durum = '<button class="btn btn-primary btn-sm">Demo Hesabı Açıldı</button>';
            }
            elseif ($musteri->durum_id != 7 && $musteri->salon->uyelik_bitis_tarihi <= date('Y-m-d') && $musteri->salon->demo_hesabi == true)
                $durum = '<button class="btn btn-info btn-sm">Satışı Gerçekleşmedi</button>';

            // Kullanıcı bilgilerini döndür
            return [
                'salon_id' => $musteri->salon->id ?? null,
                'salon_adi' => $musteri->salon->salon_adi ?? null,
                'yetkili_bilgisi' => $musteri->salon ? $musteri->salon->yetkili_adi : null,
                'yetkili_telefon' => $musteri->salon ? $musteri->salon->yetkili_telefon : null,
                'created_at' => $musteri->salon ? Carbon::parse($musteri->salon->created_at)->format('d.m.Y') : null,
                'satilan_paket' => $musteri->hizmetler
                    ? $musteri->hizmetler->map(function ($paket) use ($querydurum) {
                        $periyotyazi = '';
                        if ($paket->periyot == 'aylik') $periyotyazi = 'Aylık';
                        if ($paket->periyot == 'yillik') $periyotyazi = 'Yıllık';
                        return $paket->uyelik->uyelik_adi . ' ' . $periyotyazi;
                    })->implode('<br>')
                    : null,
                'durum' => $durum,
                'notlar' => $musteri->salon->satis_ortagi_notu,
                'kalan_sure' => $musteri->salon ? self::lisans_sure_kontrol($musteri->salon->id) . ' Gün Kaldı' : null,
                'islemler' => 
                    ($musteri->salon->hesap_acildi == 0) || ($querydurum != 'aktif' || $querydurum == 'aktifsuresibitenler') || ($musteri->salon->demouzatildi != 1 && $querydurum != 'aktif') ?

                 '<div class="dropdown">
                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                        '.(($musteri->salon->hesap_acildi == 0) ? '<a class="dropdown-item" name="musteriduzenle" data-value="' . $musteri->salon->id . '" href="#">Bilgileri Düzenle</a><a class="dropdown-item" name="demohesabiac" data-value="' . $musteri->salon->id . '" href="#">Demo Hesabı Aç</a>' : '').

                   (($querydurum != 'aktif' || $querydurum == 'aktifsuresibitenler') ? '<a class="dropdown-item" name="satisyap" data-value="' . $musteri->id . '" href="#" data-toggle="modal" data-target="#satis-formu">Satış Yap</a>' : '') .
                    (($musteri->salon->demouzatildi != 1 && $querydurum != 'aktif')
                        ? '<a class="dropdown-item" name="demouzat" data-value="' . $musteri->salon->id . '" href="#" data-toggle="modal">Demo Süresi Uzat</a>'
                        : '') . '
                    </div>
                </div>' : ''
            ];
        });
}
    public function isletmedetaylari(Request $request)

    {

        $musteri = Musteri_Formlari::with('salon')->where('id', $request->formid)->first();
        $ilceler = Ilceler::where('il_id',$musteri->salon->il_id)->get();
        $ilceler_json = $ilceler->map(function ($ilce) {
            return [
                'id' => $ilce->id,
                'text' => $ilce->ilce_adi,
            ];
        });

    return array(

        'id' => $musteri->salon->id,

        'firma_unvani' => $musteri->salon->firma_unvani,

        'isletme_adi' => $musteri->salon->salon_adi,

        'ad_soyad' => $musteri->salon->yetkili_adi,

        'email' => $musteri->salon->yetkili_mail,

        'yetkili_telefon' => $musteri->salon->yetkili_telefon,

        'isletme_telefon' => $musteri->salon->telefon_1,

        'gsm_1' => $musteri->salon->telefon_2,

        'gsm_2' => $musteri->salon->telefon_3,

        'adres' => $musteri->salon->adres,

        'vergi_dairesi' => $musteri->salon->vergi_dairesi,

        'vergi_tc_no' => $musteri->salon->vergi_no,

        'il_id' => $musteri->salon->il_id,

        'ilce_id' => $musteri->salon->ilce_id,

        'ilceler' => $ilceler_json,

            );

    }

    public function demohesabiac(Request $request)

    {

        $salon = Salonlar::where('id',$request->salonid)->first(); 
        // Check if the salon's 'yetkili_telefon' is empty
        if (empty($salon->yetkili_telefon)) {
            // If the phone number is empty, request it
                if (empty($request->yetkili_telefon)) {
                    return array(
                        "dogrulama_gerekiyor" => "2",
                        "mesaj" => "Lütfen işletme yetkilisinin telefon numarasını giriniz.",
                    );
                    exit;
                } else {
                    // Save the provided phone number to the salon
                    $salon->yetkili_telefon = $request->yetkili_telefon;
                    $salon->save();
                }
            }

    // If the 'dogrulama_kodu' is empty, create a new verification code
    if ($request->dogrulama_kodu == '') {
        $yetkili = new IsletmeYetkilileri();
        $yetkili->name = $salon->yetkili_adi;
        $yetkili->gsm1 = $salon->yetkili_telefon;
        $yetkili->aktif = true;
        
        // Generate a random 4-digit verification code
        $random2 = str_shuffle("1234567890");
        $yetkili->dogrulama_kodu = substr($random2, 0, 4);
        $yetkili->save();

        // Send the verification code via SMS
        $mesaj = [
            [
                "to" => $yetkili->gsm1,
                "message" => "Demo hesabı için doğrulama kodunuz : " . $yetkili->dogrulama_kodu
            ]
        ];
        self::sms_gonder_so($request, $mesaj);

        return array(
            "dogrulama_gerekiyor" => "1",
            "mesaj" => "Lütfen müşterinin cep telefonuna gönderilen doğrulama kodunu giriniz.",
            'yetkili_id' => $yetkili->id
        );
    }
        else{
            $yetkili = IsletmeYetkilileri::where('dogrulama_kodu',$request->dogrulama_kodu)->first();
            if(!$yetkili)
            {
                return array("dogrulama_gerekiyor"=>"1","mesaj"=>"Lütfen müşterinin cep telefonuna gönderilen doğrulama kodunu giriniz.",'yetkili_id'=>$yetkili->id);
                 exit();
            }
            else{
                $random = str_shuffle(
                        "abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890"
                );
                $olusturulansifre = substr($random, 0, 5);
                $yetkili->password = Hash::make($olusturulansifre);
                 $yetkili->save();
                $salon->randevu_saat_araligi = 15;
               $salon->randevu_takvim_turu = 1;
                $salon->uyelik_bitis_tarihi = date(
                    "Y-m-d",
                    strtotime("+7 days", strtotime(date("Y-m-d")))
                );
    
                $salon->uyelik_turu = 3;
                $salon->demo_hesabi = true;
                $salon->hesap_acildi = true;
                $salon->save();

                $santral_ayari = new SalonSantralAyarlari();
            $santral_ayari->ayar_id = 1;
            $santral_ayari->salon_id = $salon->id;
            $santral_ayari->musteri = 0;
            $santral_ayari->personel = 0;
            $santral_ayari->save();
                SalonCalismaSaatleri::insert([
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 1,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "07:00:00",
    
                        "bitis_saati" => "20:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 2,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "07:00:00",
    
                        "bitis_saati" => "20:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 3,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "07:00:00",
    
                        "bitis_saati" => "20:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 4,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "07:00:00",
    
                        "bitis_saati" => "20:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 5,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "07:00:00",
    
                        "bitis_saati" => "20:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 6,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "07:00:00",
    
                        "bitis_saati" => "20:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 7,
    
                        "calisiyor" => 1,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
                ]);
    
    
    
                SalonMolaSaatleri::insert([
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 1,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 2,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 3,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 4,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 5,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 6,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "haftanin_gunu" => 7,
    
                        "mola_var" => 0,
    
                        "baslangic_saati" => "00:00:00",
    
                        "bitis_saati" => "00:00:00",
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
                ]);
    
    
    
                SalonSMSAyarlari::insert([
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 1,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 2,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 3,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 4,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 5,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 6,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 7,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 8,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 9,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 10,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 11,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 12,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 13,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 14,
    
                        "musteri" => 1,
    
                        "personel" => 1,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 15,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 16,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 17,
    
                        "musteri" => 0,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 18,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
    
    
                    [
    
                        "salon_id" => $salon->id,
    
                        "ayar_id" => 19,
    
                        "musteri" => 1,
    
                        "personel" => 0,
    
                        "created_at" => date("Y-m-d H:i:s"),
    
                        "updated_at" => date("Y-m-d H:i:s"),
    
                    ],
    
                ]);
    
    
    
                DB::insert(
    
                    'insert into model_has_roles (role_id, model_type,model_id,salon_id) values (1, "App\\\IsletmeYetkilileri",' .
    
                        $yetkili->id .
    
                        "," .
    
                        $salon->id .
    
                        ")"
    
                );
    
                $personel = new Personeller();
    
                $personel->salon_id = $salon->id;
    
                $personel->personel_adi = $yetkili->name;
    
                $personel->cep_telefon = $yetkili->gsm1;
    
                $personel->yetkili_id = $yetkili->id;
    
                $personel->takvimde_gorunsun = true;
    
                $personel->takvim_sirasi = 1;
    
                $personel->renk = 1;
    
                $personel->aktif = true;
    
                $personel->role_id = 1;
    
                $personel->save();
    
                $randevu = new Randevular();
    
                $randevu->user_id = 1;
    
                $randevu->tarih = date("Y-m-d");
    
                $randevu->saat = date("H:i:s");
    
                $randevu->personel_notu = "Örnek personel notu";
    
                $randevu->notlar = "Örnek müşteri/danışan notu";
    
                $randevu->salon = true;
    
                $randevu->olusturan_personel_id = $yetkili->id;
    
                $randevu->sms_hatirlatma = true;
    
                $randevu->saat_bitis = date(
    
                    "H:i",
    
                    strtotime("+1 hours", strtotime(date("H:i")))
    
                );
    
                $randevu->durum = 1;
    
                $randevu->save();
    
                $randevuhizmet = new RandevuHizmetler();
    
                $randevuhizmet->personel_id = $personel->id;
    
                $randevuhizmet->hizmet_id = 2;
    
                $randevuhizmet->saat = date("H:i:s");
    
                $randevuhizmet->saat_bitis = date(
    
                    "H:i:s",
    
                    strtotime("+1 hours", strtotime(date("H:i")))
    
                );
    
    
    
                $randevuhizmet->sure_dk = 60;
    
                $randevuhizmet->cihaz_id = 1;
    
                $randevuhizmet->oda_id = 1;
    
                $randevuhizmet->save();
    
                $urun = new Urunler();
    
                $urun->urun_adi = "Örnek Ürün 1";
    
                $urun->fiyat = 100;
    
                $urun->stok_adedi = 5;
    
                $urun->dusuk_stok_siniri = 1;
    
                $urun->salon_id = $salon->id;
    
                $urun->aktif = true;
    
                $urun->save();
    
                $paket = new Paketler();
    
                $paket->paket_adi = "Örnek Paket 1";
    
                $paket->aktif = true;
    
                $paket->salon_id = $salon->id;
    
                $paket->save();
    
                $pakethizmet = new PaketHizmetler();
    
                $pakethizmet->paket_id = $paket->id;
    
                $pakethizmet->hizmet_id = 2;
    
                $pakethizmet->seans = 1;
    
                $pakethizmet->fiyat = 100;
    
                $pakethizmet->save();
    
                $urun2 = new Urunler();
    
                $urun2->urun_adi = "Örnek Ürün 2";
    
                $urun2->fiyat = 200;
    
                $urun2->stok_adedi = 5;
    
                $urun2->dusuk_stok_siniri = 1;
    
                $urun2->salon_id = $salon->id;
    
                $urun2->aktif = true;
    
                $urun2->save();
    
                $paket2 = new Paketler();
    
                $paket2->paket_adi = "Örnek Paket 2";
    
                $paket2->aktif = true;
    
                $paket2->salon_id = $salon->id;
    
                $paket2->save();
    
                $pakethizmet2 = new PaketHizmetler();
    
                $pakethizmet2->paket_id = $paket2->id;
    
                $pakethizmet2->hizmet_id = 3;
    
                $pakethizmet2->seans = 1;
    
                $pakethizmet2->fiyat = 200;
    
                $pakethizmet2->save();
    
                $ongorusme = new OnGorusmeler();
    
                $ongorusme->salon_id = $salon->id;
    
                $ongorusme->user_id = 1;
    
                $ongorusme->ad_soyad = "Örnek Müşteri/Danışan 1";
    
                $ongorusme->email = "ornek@email.com";
    
                $ongorusme->cinsiyet = 0;
    
                $ongorusme->adres = "Örnek Adres 1";
    
                $ongorusme->aciklama = "Örnek Açıklama 1";
    
                $ongorusme->il_id = 1;
    
                $ongorusme->musteri_tipi = 1;
    
                $ongorusme->meslek = "Örnek Meslek 1";
    
                $ongorusme->urun_id = $urun->id;
    
                $ongorusme->hatirlatma_tarihi = date("Y-m-d");
    
                $ongorusme->personel_id = Personeller::where(
    
                    "yetkili_id",
    
                    $yetkili->id
    
                )
    
                    ->where("salon_id", $salon->id)
    
                    ->value("id");
    
    
    
            $ongorusme->save();
    
                $randevu_ongorusme = new Randevular();
    
                $randevu_ongorusme->on_gorusme_id = $ongorusme->id;
    
                $randevu_ongorusme->user_id = $ongorusme->user_id;
    
                $randevu_ongorusme->salon_id = $ongorusme->salon_id;
    
                $randevu_ongorusme->tarih = date("Y-m-d");
    
                $randevu_ongorusme->saat = date("H:i:s");
    
                $randevu_ongorusme->salon = true;
    
                $randevu_ongorusme->sms_hatirlatma = true;
    
                $randevu_ongorusme->durum = 1;
    
                $randevu_ongorusme->olusturan_personel_id = $yetkili->id;
    
                $randevu_ongorusme->save();
    
                $ongorusmehizmeti = new RandevuHizmetler();
    
                $ongorusmehizmeti->hizmet_id = 1;
    
                $ongorusmehizmeti->personel_id = $ongorusme->personel_id;
    
                $ongorusmehizmeti->saat = $ongorusme->ongorusme_saati;
    
                $ongorusmehizmeti->saat_bitis = date(
    
                "H:i:s",
    
                strtotime("+1 hours", strtotime($ongorusme->ongorusme_saati))
    
            );
    
                $ongorusmehizmeti->randevu_id = $randevu_ongorusme->id;
    
                $ongorusmehizmeti->save();
    
                $ongorusme_paket = new OnGorusmeler();
    
                $ongorusme_paket->salon_id = $salon->id;
    
                $ongorusme_paket->user_id = 2;
    
                $ongorusme_paket->ad_soyad = "Örnek Müşteri/Danışan 2";
    
                $ongorusme_paket->email = "ornek@email.com";
    
                $ongorusme_paket->cinsiyet = 1;
    
                $ongorusme_paket->adres = "Örnek Adres 2";
    
                $ongorusme_paket->aciklama = "Örnek Açıklama 2";
    
                $ongorusme_paket->il_id = 2;
    
                $ongorusme_paket->musteri_tipi = 2;
    
                $ongorusme_paket->meslek = "Örnek Meslek 2";
    
                $ongorusme_paket->paket_id = $paket->id;
    
                $ongorusme_paket->hatirlatma_tarihi = date("Y-m-d");
    
                $ongorusme_paket->personel_id = Personeller::where(
    
                "yetkili_id",
    
                $yetkili->id
    
            )
    
                ->where("salon_id", $salon->id)
    
                ->value("id");
    
                $ongorusme_paket->save();
    
                $randevu_ongorusme_paket = new Randevular();
    
                $randevu_ongorusme_paket->on_gorusme_id = $ongorusme_paket->id;
    
                $randevu_ongorusme_paket->user_id = $ongorusme_paket->user_id;
    
                $randevu_ongorusme_paket->salon_id = $ongorusme_paket->salon_id;
    
                $randevu_ongorusme_paket->tarih = date("Y-m-d");
    
                $randevu_ongorusme_paket->saat = date("H:i:s");
    
                $randevu_ongorusme_paket->salon = true;
    
                $randevu_ongorusme_paket->sms_hatirlatma = true;
    
                $randevu_ongorusme_paket->durum = 1;
    
                $randevu_ongorusme_paket->olusturan_personel_id = $yetkili->id;
    
                $randevu_ongorusme_paket->save();
    
                $ongorusmehizmeti_paket = new RandevuHizmetler();
    
                $ongorusmehizmeti_paket->hizmet_id = 1;
    
                $ongorusmehizmeti_paket->personel_id =
    
                $ongorusme_paket->personel_id;
    
                $ongorusmehizmeti_paket->saat = date("H:i:s");
    
                $ongorusmehizmeti_paket->saat_bitis = date(
    
                "H:i:s",
    
                strtotime(
    
                    "+1 hours",
    
                    strtotime($ongorusme_paket->ongorusme_saati)
    
                )
    
            );
    
                $ongorusmehizmeti_paket->randevu_id = $randevu_ongorusme_paket->id;
    
                $ongorusmehizmeti_paket->save();
    
                $urun_adisyon_id = self::yeni_adisyon_olustur(
    
                1,
    
                $salon->id,
    
                "Ürün Satışı",
    
                date("Y-m-d"),
    
                $yetkili
    
            );
    
                $adisyon_urun = new AdisyonUrunler();
    
                $adisyon_urun->islem_tarihi = date("Y-m-d");
    
                $adisyon_urun->adisyon_id = $urun_adisyon_id;
    
                $adisyon_urun->urun_id = $urun2->id;
    
                $adisyon_urun->adet = 1;
    
                $adisyon_urun->fiyat = 200;
    
                $adisyon_urun->personel_id = Personeller::where(
    
                "salon_id",
    
                $salon->id
    
            )
    
                ->where("yetkili_id", $yetkili->id)
    
                ->value("id");
    
                $adisyon_urun->save();
    
                $adisyon_id2 = self::yeni_adisyon_olustur(
    
                1,
    
            $salon->id,
    
                "Paket Satışı",
    
                date("Y-m-d"),
    
                $yetkili
    
            );
    
                $adisyon_paket_id = self::adisyona_paket_ekle(
    
                $adisyon_id2,
    
                $paket2->id,
    
                200,
    
                date("Y-m-d"),
    
                1,
    
                Personeller::where("salon_id", $salon->id)
    
                    ->where("yetkili_id", $yetkili->id)
    
                    ->value("id"),
    
                null,
    
                null
    
            );
    
                $paket_mevcut = Paketler::where("id", $paket2->id)->first();
    
                $seans_randevu = new Randevular();
    
                $seans_randevu->user_id = 1;
    
                $seans_randevu->tarih = date("Y-m-d");
    
                $seans_randevu->salon_id = $salon->id;
    
                $seans_randevu->durum = 1;
    
                $seans_randevu->saat = date("H:i:s");
    
                $seans_randevu->olusturan_personel_id = Personeller::where(
    
                "yetkili_id",
    
                $yetkili->id
    
            )
    
                ->where("salon_id", $request->sube)
    
                ->value("id");
    
                $seans_randevu->salon = 1;
    
                $seans_randevu->save();
    
                $seans = new AdisyonPaketSeanslar();
    
                $seans->adisyon_paket_id = $adisyon_paket_id;
    
                $seans->seans_tarih = date("Y-m-d");
    
                $seans->hizmet_id = 3;
    
                $seans->seans_no = 1;
    
                $seans->seans_saat = date("H:i:s");
    
                $seans->randevu_id = $seans_randevu->id;
    
                $seans->save();
    
                $seans_randevu_hizmet = new RandevuHizmetler();
    
                $seans_randevu_hizmet->randevu_id = $seans_randevu->id;
    
                $seans_randevu_hizmet->hizmet_id = 3;
    
                $seans_randevu_hizmet->sure_dk = 60;
    
                $seans_randevu_hizmet->saat = date("H:i:s");
    
                $seans_randevu_hizmet->saat_bitis = date(
    
                "H:i:s",
    
                strtotime("+60 minutes", strtotime(date("H:i:s")))
    
            );
    
                $seans_randevu_hizmet->save();
    
                $etkinlik = new Etkinlikler();
    
                $etkinlik->etkinlik_adi = "Örnek Etkinlik 1";
    
                $etkinlik->tarih_saat = date("Y-m-d H:i:s");
    
                $etkinlik->fiyat = 1000;
    
                $etkinlik->salon_id = $salon->id;
    
                $etkinlik->aktifmi = 1;
    
                $etkinlik->mesaj = "Etkinliğimizde davetlisiniz örnek mesaj 1";
    
                $etkinlik->save();
    
                $yenikatilimci = new EtkinlikKatilimcilari();
    
                $yenikatilimci->etkinlik_id = $etkinlik->id;
    
                $yenikatilimci->user_id = 2;
    
                $yenikatilimci->save();
    
                $yenikatilimci2 = new EtkinlikKatilimcilari();
    
                $yenikatilimci2->etkinlik_id = $etkinlik->id;
    
                $yenikatilimci2->user_id = 1;
    
                $yenikatilimci2->save();
    
                $kampanya_yonetimi = new KampanyaYonetimi();
    
                $kampanya_yonetimi->paket_isim = "Örnek Paket 1";
    
                $kampanya_yonetimi->hizmet_adi = "Örnek Hizmet Kampanyası";
    
                $kampanya_yonetimi->fiyat = 200;
    
                $kampanya_yonetimi->seans = 1;
    
                $kampanya_yonetimi->salon_id = $salon->id;
    
                $kampanya_yonetimi->aktifmi = 1;
    
                $kampanya_yonetimi->mesaj = "Örnek Hizmet Kampanyası";
    
                $kampanya_yonetimi->save();
    
                $yenikatilimcikampanya = new KampanyaKatilimcilari();
    
                $yenikatilimcikampanya->kampanya_id = $kampanya_yonetimi->id;
    
                $yenikatilimcikampanya->user_id = 1;
    
                $yenikatilimcikampanya->save();
    
                $yenikatilimcikampanya = new KampanyaKatilimcilari();
    
                $yenikatilimcikampanya->kampanya_id = $kampanya_yonetimi->id;
    
                $yenikatilimcikampanya->user_id = 2;
    
                $yenikatilimcikampanya->save();
    
                $yeninot = new Ajanda();
    
                $yeninot->ajanda_baslik = "Örnek Hatırlatma";
    
                $yeninot->ajanda_tarih = date("Y-m-d");
    
                $yeninot->ajanda_hatirlatma = 0;
    
                $yeninot->ajanda_saat = date("H:i:s");
    
                $yeninot->ajanda_icerik = "Örnek ajanda notu";
    
                $yeninot->ajanda_hatirlatma_saat = date("H:i:s");
    
                $yeninot->salon_id = $salon->id;
    
                $yeninot->ajanda_olusturan = Personeller::where(
    
                "salon_id",
    
                $salon->id
    
            )
    
                ->where("yetkili_id", $yetkili->id)
    
                ->value("id");
    
                $yeninot->aktif = true;
    
                $yeninot->save();
    
                $form = new Arsiv();
    
                $random = str_shuffle("1234567");
    
                $kod = substr($random, 0, 1);
    
                $form->user_id = 1;
    
                $form->form_id = $kod;
    
                $form->personel_id = Personeller::where("salon_id", $salon->id)
    
                    ->where("yetkili_id", $yetkili->id)
    
                ->value("id");
    
                $form->cevapladi = false;
    
                $form->cevapladi2 = false;
    
                $form->salon_id = $salon->id;
    
                $form->form_olusturan = Personeller::where(
    
                "salon_id",
    
                    $request->sube
    
            )
    
                ->where("yetkili_id", $yetkili->id)
    
                ->value("id");
    
                $form->save();
    
                $form2 = new Arsiv();
    
                $random2 = str_shuffle("1234567");
    
                $kod2 = substr($random2, 0, 1);
    
                $form2->user_id = 2;
    
                $form2->form_id = $kod2;
    
                $form2->personel_id = Personeller::where("salon_id", $salon->id)
    
                ->where("yetkili_id", $yetkili->id)
    
                ->value("id");
    
                $form2->cevapladi = false;
    
                $form2->cevapladi2 = false;
    
                $form2->salon_id = $salon->id;
    
                $form2->form_olusturan = Personeller::where(
    
                "salon_id",
    
                $request->sube
    
            )
    
                ->where("yetkili_id", $yetkili->id)
    
                ->value("id");
    
                $form2->save(); 
    
                $headers = [
    
                "Authorization: Key LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5",
    
                    "Content-Type: application/json",
    
                    "Accept: application/json",
    
            ];
    
                $postData = json_encode([
    
                "originator" => "RANDVMCEPTE",
    
                "messages" => [
    
                    [
    
                        "to" => $yetkili->gsm1,
    
                        "message" =>
    
                                "Randevumcepte'ye hoşgeldiniz. Sistem kullanıcı adınız : " .
    
                            $yetkili->gsm1 .
    
                            " şifreniz : " .
    
                            $olusturulansifre,
    
                    ],
    
                ],
    
                "encoding" => "auto",
    
            ]);
    
                $ch = curl_init();
    
                curl_setopt(
    
                $ch,
    
                CURLOPT_URL,
    
                "http://api.efetech.net.tr/v2/sms/multi"
    
            );
    
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
                curl_setopt($ch, CURLOPT_POST, 1);
    
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
                $response = curl_exec($ch);
    
                return array(
                    'dogrulama_gerekiyor'=>"0",
                    'mesaj'=> "Demo hesabı başarıyla oluşturulup işletme yetkilisine giriş bilgileri gönderilmiştir.",
                    'pasif_musteriler'=>self::filtreli_musteri_listesi('pasif',$request->pasifortakid),
                    'demosu_olan_musteriler'=>self::filtreli_musteri_listesi('demolar',$request->pasifortakid),
                    'satis_yapilamayan_musteriler'=>self::filtreli_musteri_listesi('satisolmamis',$request->pasifortakid),
                    'aktif_musteriler'=>self::filtreli_musteri_listesi('aktif',$request->pasifortakid),
                    'suresi_bitecek_musteriler'=>self::filtreli_musteri_listesi('aktifsuresibitenler',$request->pasifortakid),
                    'dashboard_verileri'=>self::hakedis_hesaplama($request,Musteri_Formlari::whereBetween('satis_tarihi', [Carbon::now()->subMonths(12)->startOfMonth()->toDateTimeString(), Carbon::now()->endOfDay()->toDateTimeString()])->whereIn('satis_ortagi_hakedis_odeme_durumu_id', [1, 3])->where('durum_id', 7)->where('satis_ortagi_id', Auth::user()->id)->get())
                );
                     
                exit();

            } 

        } 
      

    }   

    public function yeni_adisyon_olustur($musteriid,$salonid,$adisyonnotu,$tarih)

    {

        $adisyon = new Adisyonlar();

        $adisyon->user_id = $musteriid;

        $adisyon->salon_id =  $salonid;

        $adisyon->olusturan_id = Auth::user()->id;

        $adisyon->tarih = $tarih;

        $adisyon->save();

        return $adisyon->id;

    }     

    public function adisyona_paket_ekle($adisyon_id,$paket_id,$fiyat,$baslangic_tarihi,$seans_araligi,$personel_id,$senet_id,$taksitli_tahsilat_id){

        $adisyon_paket = new AdisyonPaketler();

        $adisyon_paket->adisyon_id = $adisyon_id;

        $adisyon_paket->paket_id = $paket_id;

        $adisyon_paket->fiyat = $fiyat;

        $adisyon_paket->baslangic_tarihi = $baslangic_tarihi;

        $adisyon_paket->seans_araligi = $seans_araligi;

        $adisyon_paket->personel_id = $personel_id;

        $adisyon_paket->senet_id = $senet_id;

        $adisyon_paket->taksitli_tahsilat_id = $taksitli_tahsilat_id;

        $adisyon_paket->save();

        return $adisyon_paket->id;



    }

    public function aktif_musteriler(Request $request){

       

      $aktif_musteriler = self::filtreli_musteri_listesi('aktif','');
    $guncel_musteriler_yeni_satis = Musteri_Formlari::whereIn('satis_ortagi_hakedis_odeme_durumu_id',[1,3])->where('durum_id',7)->where('satis_ortagi_id',Auth::user()->id)->get();
      $hakedis = self::hakedis_hesaplama($request,$guncel_musteriler_yeni_satis);  
      return view('satisortakligi.aktif-musteriler',['musteri_bilgileri'=>$guncel_musteriler_yeni_satis,'aktif_musteriler'=> $aktif_musteriler,'sayfa_baslik'=>'Aktif Müşteriler','pageindex'=>4,'hakedis'=>$hakedis,'bildirimler'=>self::bildirimler($request)]);

    }

    public function odeme_talepleri(Request $request){

       $talepler = self::odeme_talepleri_liste($request);
      $guncel_musteriler_yeni_satis = Musteri_Formlari::whereIn('satis_ortagi_hakedis_odeme_durumu_id',[1,3])->where('durum_id',7)->where('satis_ortagi_id',Auth::user()->id)->get();
        $hakedis = self::hakedis_hesaplama($request,$guncel_musteriler_yeni_satis);  
       return view('satisortakligi.hakedis-talepleri',['talepler'=>$talepler,'sayfa_baslik'=>'Ödeme Talepleri','pageindex'=>5,'hakedis'=>$hakedis,'bildirimler'=>self::bildirimler($request)]);

    }
    public function odeme_talepleri_liste(Request $request)
    {
        return SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->get()->map(function ($talep) {
            return [
            'tarih'=>'<span style="display:none">'.date('Ymd',strtotime($talep->created_at)).'</span>'.date('d.m.Y',strtotime($talep->created_at)),
            'tutar' => number_format($talep->odeme_miktari,2,',','.'),
            'durum' =>  ($talep->satis_ortagi_hakedis_odeme_durumu_id == 1 ? ' <button type="button" class="btn btn-warning btn-sm">Talep Edildi</button>' : ' <button type="button" class="btn btn-success btn-sm">
                          
                          '.date('d.m.Y H:i',strtotime($talep->updated_at)).' tarihinde <br> ödeme gerçekleşti
                         </button>')
                         
                          

        
            ];

        });
    }
    public function odeme_talebi_gonder(Request $request)
    {
        $returntext = "";
        if(isset($_FILES["komisyon_fatura_gider_pusulasi_belge"]["name"])){
            $odeme_talebi = new SatisOrtagiOdemeTalepleri();
            $dosya  = $request->komisyon_fatura_gider_pusulasi_belge;
            $kaynak = $_FILES["komisyon_fatura_gider_pusulasi_belge"]["tmp_name"];
            $dosya  = str_replace(" ", "_", $_FILES["komisyon_fatura_gider_pusulasi_belge"]["name"]);
            $dosya = str_replace(" ", "-", $_FILES["komisyon_fatura_gider_pusulasi_belge"]["name"]);
            $uzanti = explode(".", $_FILES["komisyon_fatura_gider_pusulasi_belge"]["name"]);
            $hedef  = "./" . $dosya;
            if (@$uzanti[1]) {
                if (!file_exists($hedef)) {
                    $hedef   =  "public/satisortagifatura/".$dosya;
                    $dosya   = $dosya;
                }
                move_uploaded_file($kaynak, $hedef);
            }  
           
            $odeme_talebi->satis_ortagi_id = Auth::user()->id;
            $odeme_talebi->satis_ortagi_hakedis_odeme_durumu_id = 1;
            $odeme_talebi->odeme_miktari = str_replace('.','',$request->hakedis_miktari);
            $odeme_talebi->komisyon_faturasi_gider_pusulasi = $hedef;
            $odeme_talebi->baslangic_tarihi = Musteri_Formlari::where('satis_ortagi_id',Auth::user()->id)->where('durum_id',7)->where('satis_ortagi_hakedis_odeme_durumu_id',3)->orderBy('satis_tarihi','asc')->first()->satis_tarihi;
            $odeme_talebi->bitis_tarihi = date('Y-m-d H:i:s');
            $odeme_talebi->save();
            Musteri_Formlari::where('satis_ortagi_id',Auth::user()->id)->where('satis_ortagi_hakedis_odeme_durumu_id',3)->where('durum_id',7)->update(['satis_ortagi_hakedis_odeme_durumu_id' => 1]);
             /*Mail::send(['html' =>"bayi.bayi-odeme-talebi-bildirim-mail"],["bayi"=>Auth::user()], function ($message) use($bayi) {
                $message->from("bilgilendirme@webfirmam.com.tr", "İnostra DanışmanlıkKurumsal Yönetim Paneli");
                $message->to("bilgilendirme@webfirmam.com.tr", "İnostra DanışmanlıkKurumsal Yönetim Paneli")->subject(Auth::user()->ad_soyad .' tarafından eklenen yeni müşteriler var');
              });*/
            
            return array("returntext" => "Ödeme talebiniz başarı ile oluşturulmuştur",'talepler'=>self::odeme_talepleri_liste($request));
            exit();
               
        }
        else{
            return array("returntext"=>"Lütfen gider pusulası veya komisyon faturanızı ekleyiniz",'talepler'=>self::odeme_talepleri_liste($request));
            exit();
           
        }
        
    }
    public function gecmis_odemeler(Request $request){ 
         $request->merge([
            'baslangic' => date('Y-m-01'),
            'bitis'=>date('Y-m-d')
        ]);

        $odemeler = self::gecmis_odemeler_filtre($request);
        return view('satisortakligi.gecmis-odemeler',['gecmis_odemeler'=>$odemeler,'sayfa_baslik'=>'Geçmiş Ödemeler','pageindex'=>6,'bildirimler'=>self::bildirimler($request)]);

    }
    public function gecmis_odemeler_filtre(Request $request)
    {
        return SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->whereBetween('odeme_tarihi',[$request->baslangic.' 00:00:00',$request->bitis. ' 23:59:59'])->where('satis_ortagi_hakedis_odeme_durumu_id',2)->get()->map(function ($odeme) {
            return [
                'tarih'=>"<span style='display:none'>".date('YmdHis',strtotime($odeme->odeme_tarihi))."</span>".date('d.m.Y H:i',strtotime($odeme->odeme_tarihi)) ?? null,
                'tutar'=>number_format($odeme->odeme_miktari,2,',','.') ?? null,
                'banka' => $odeme->banka->banka ?? null 
            
            ];

        });
    }
    public function lisans_sure_kontrol($salonid)
    { 



        $isletme = Salonlar::where('id',$salonid)->first();

        $from_time = strtotime(date('Y-m-d H:i:s'));

        $to_time = strtotime(date($isletme->uyelik_bitis_tarihi.' 23:59:59'));

        $diff = round(($to_time - $from_time) / (3600*24),0);

        if($isletme->uyelik_bitis_tarihi == null||$isletme->uyelik_bitis_tarihi == '' )

            $diff .= '-';



        return $diff;







    }

    public function musteri_formunu_kaydet(Request $request){

    $str = explode('-',$request->form_hizmet);
        $uyelik = Uyelik::where('id',$str[0])->first();
        $periyotyazi = "";
        $fiyat = "";
        $fiyatnum = 0;
        if($str[1]=="aylik")
        {
            $fiyat = number_format($uyelik->aylik_tutar,2,',','.');
            $fiyatnum = $uyelik->aylik_tutar;

            $periyotyazi = "Aylık";
        }
        if($str[1]=="yillik"){
            $fiyat = number_format($uyelik->yillik_tutar,2,',','.');
            $fiyatnum = $uyelik->yillik_tutar;
            $periyotyazi = "Yıllık";
        }
        $request->merge([
            'uyelikturu' => $str[0],
            'periyot'=>$str[1]
        ]);

        $musteri = Salonlar::where('id',$request->form_islemleri_musteri_id)->first();
        $musteri->firma_unvani = $request->form_islemleri_firma_unvani;
        $musteri->yetkili_adi = $request->form_islemleri_yetkili_kisi;
        $musteri->yetkili_telefon = $request->form_islemleri_yetkili_telefon;
        $musteri->telefon_1 = $request->form_islemleri_gsm1;
        $musteri->telefon_2 = $request->form_islemleri_gsm2;
        $musteri->telefon_3 = $request->form_islemleri_gsm3;
        $musteri->yetkili_mail = $request->form_islemleri_email;
        $musteri->il_id = $request->il_id;
        $musteri->ilce_id = $request->ilce_id;
        $musteri->adres = $request->form_islemleri_adres;
        $musteri->vergi_dairesi = $request->form_islemleri_vergi_dairesi;
        $musteri->vergi_adresi = $request->form_islemleri_adres;
        $musteri->uyelik_turu = $str[0];
        $musteri->vergi_no = $request->form_islemleri_vergi_tc_no;
        $musteri->save();
        
        if($request->form_islemleri_randevu_tarihi!='' && $request->form_islemleri_randevu_saati!=''&& $request->form_islemleri_randevu_notu !=''){
            $form = Musteri_Formlari::where('id',$request->orm_islemleri_form_id)->first();
            $form->salon_id = $musteri->id;
            $form->satis_ortagi_id = Auth::user()->id;
            $form->durum_id = 2;
            $form->notlar = $request->form_islemleri_notlar;
            $form->save();
            $randevu = new Telefon_Randevulari();
            $randevu->musteri_id = $musteri->id;
            $randevu->satis_ortagi_id = Auth::user()->id;
            $randevu->randevu_notu = $request->randevu_notu;
            $randevu->baslangic_tarih_saat = $request->form_islemleri_randevu_tarihi ." ".$request->form_islemleri_randevu_saati;
            $randevu->save();
            exit;
        }
        else{
            $form="";
            $musteri->aktif = true;
            $daha_once_satis_var_mi = Musteri_Formlari::where('id',$request->form_islemleri_form_id)->whereIn('durum_id',[6,7])->count();
            if($daha_once_satis_var_mi > 0){
                $form = new Musteri_Formlari();
                $form->devam_eden_odeme = true;
                $form->satan_satis_ortagi = Auth::user()->id;
            }
                
            else{

            $form = Musteri_Formlari::where('id',$request->form_islemleri_form_id)->first();
                $form->devam_eden_odeme = false;
            }
            $form->salon_id = $musteri->id;
            $form->satis_ortagi_id = Auth::user()->id;
            $form->satan_satis_ortagi = Auth::user()->id;
            $form->durum_id = 6;
            $form->notlar = $request->form_islemleri_notlar;
            $form->satis_tarihi = date('Y-m-d H:i:s', strtotime($request->form_islemleri_tarih));
            $form->save();
         

        
            $form_hizmet = new Musteri_Formlari_Hizmetler();
            $form_hizmet->form_id = $form->id;
            $form_hizmet->uyelik_id = $uyelik->id; 
            $form_hizmet->periyot = $str[1];
            $form_hizmet->ucret = $fiyatnum;
            
            $form_hizmet->save(); 
            if($request->satis_odeme_turu == 1)
        {
            $mesaj = [
                [
                    "to" => $musteri->yetkili_telefon,
                    "message" => "Sayın ".$musteri->yetkili_adi.", ".$uyelik->uyelik_adi." Paketi ".$periyotyazi." hizmetinize ait ".$fiyat." TRY ödeme bilgileri aşağıdaki linkte yer almaktadır. Güvenli bir şekilde ödemenizi tamamlamak için lütfen linke tıklayın: https://app.randevumcepte.com.tr/odeme?form=".$form->id
                        
                ]
                    
            ];
            self::sms_gonder_so($request,$mesaj); 
        }
        if($request->satis_odeme_turu == 2)
        {
            $mesaj = [
                [
                    "to" => $musteri->yetkili_telefon,
                    "message" => "Sayın ".$musteri->yetkili_adi.", ".$musteri->id." nolu üye eferans numaralı ".$uyelik->uyelik_adi." Paket ".$periyotyazi." hizmetinize it ".$fiyat." TRY ödeme bilgileri aşağıda belirtilmiştir. Ödemenizi banka avalesi/eft yoluyla aşağıdaki hesap bilgilerine yapabilirsiniz:\n\n
Banka Adı: Garanti BBVA\n
Hesap Adı: 1100-6297259 BYRAKLI\n
IBAN: TR46 0006 2001 1000 0006 2972 59\n
Açıklama: [Açıklamaya Eklenecek Referans Bilgisi]\n\n
Açıklamaya mutlaka referans bilgilerinizi yazmanız gerekmektedir. Ödemenizi tamamladıktan sonra lütfen bizi bilgilendiriniz. Teşekkür ederiz."
                    

                ]

            ];

            //self::sms_gonder($request,$mesaj);

        }

        return array(

            'type'=>'success',

            'mesaj'=>'başarılı',
            'pasif_musteriler'=>self::filtreli_musteri_listesi('pasif',$request->pasifortakid),
            'demosu_olan_musteriler'=>self::filtreli_musteri_listesi('demolar',$request->pasifortakid),
            'satis_yapilamayan_musteriler'=>self::filtreli_musteri_listesi('satisolmamis',$request->pasifortakid),
            'aktif_musteriler'=>self::filtreli_musteri_listesi('aktif',$request->pasifortakid),
            'suresi_bitecek_musteriler'=>self::filtreli_musteri_listesi('aktifsuresibitenler',$request->pasifortakid),
            'dashboard_verileri'=>self::hakedis_hesaplama($request,Musteri_Formlari::whereBetween('satis_tarihi', [Carbon::now()->subMonths(12)->startOfMonth()->toDateTimeString(), Carbon::now()->endOfDay()->toDateTimeString()])->whereIn('satis_ortagi_hakedis_odeme_durumu_id', [1, 3])->where('durum_id', 7)->where('satis_ortagi_id', Auth::user()->id)->get())

        );
        exit;



        } 
         
        
    }

    public function sms_gonder_so(
        Request $request,
        $mesajlar
    ) {
             
        $headers = [
                "Authorization: Key LSS3WTaRVz5kD33yTqXuny1W9fKBBYD5GRSD2o6Bo9L5",
                "Content-Type: application/json",
                "Accept: application/json",
        ];
        $postData = json_encode([
            "originator" => "RANDVMCEPTE",
            "messages" => $mesajlar,
            "encoding" => "auto",
        ]);
        $ch = curl_init();
        curl_setopt(
                $ch,
                CURLOPT_URL,
                "http://api.efetech.net.tr/v2/sms/multi"
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
           

    }

    public function demosuresiuzat(Request $request){
        $demo=Salonlar::where('id',$request->salonid)->first();
        $demo->demouzatildi=true;
        $currentEndDate = Carbon::parse($demo->uyelik_bitis_tarihi);
        $newEndDate = $currentEndDate->addWeek();
        $demo->uyelik_bitis_tarihi = $newEndDate;
        $demo->save();
        
            $mesaj = [

                [

                    "to" => $demo->yetkili_telefon,
                    "message" => "Sayın ".$demo->yetkili_adi.", RandevumCepte demo süreniz 1 hafta uzatılmıştır."
                ]

            ];

            self::sms_gonder_so($request,$mesaj);
        return array(
                 'pasif_musteriler'=>self::filtreli_musteri_listesi('pasif',$request->pasifortakid),
            'demosu_olan_musteriler'=>self::filtreli_musteri_listesi('demolar',$request->pasifortakid),
            'satis_yapilamayan_musteriler'=>self::filtreli_musteri_listesi('satisolmamis',$request->pasifortakid),
            'aktif_musteriler'=>self::filtreli_musteri_listesi('aktif',$request->pasifortakid),
            'suresi_bitecek_musteriler'=>self::filtreli_musteri_listesi('aktifsuresibitenler',$request->pasifortakid),
            'dashboard_verileri'=>self::hakedis_hesaplama($request,Musteri_Formlari::whereBetween('satis_tarihi', [Carbon::now()->subMonths(12)->startOfMonth()->toDateTimeString(), Carbon::now()->endOfDay()->toDateTimeString()])->whereIn('satis_ortagi_hakedis_odeme_durumu_id', [1, 3])->where('durum_id', 7)->where('satis_ortagi_id', Auth::user()->id)->get())
        );
    }
    public function hesap_ayarlari(Request $request){
      $bayi_bilgileri = Auth::user();
      $bayi_banka_hesaplari = self::satis_ortagi_banka_hesaplari($request);
      return view('satisortakligi.hesap-ayarlari',['bayi_bilgileri'=>$bayi_bilgileri,'bayi_banka_hesaplari'=>$bayi_banka_hesaplari,'pageindex'=>8,'sayfa_baslik'=>'Hesap Ayarları','bildirimler'=>self::bildirimler($request)]);
    }
    public function bilgileri_guncelle(Request $request)
    {
       $bayi_bilgileri = Auth::user();
       $bayi_bilgileri->ad_soyad = $request->ad_soyad;
       $bayi_bilgileri->email = $request->email;
       $bayi_bilgileri->telefon = $request->telefon;
    
        
       $bayi_bilgileri->save();
       return "Profil bilgileriniz başarı ile güncellendi";

    }
   
    public function profil_resmi_degistir(Request $request){
      
      if(isset($_FILES["profil_resmi"]["name"])){
                         
                        $dosya  = $request->profil_resmi;
                        $kaynak = $_FILES["profil_resmi"]["tmp_name"];
                        $dosya  = str_replace(" ", "_", $_FILES["profil_resmi"]["name"]);
                        $dosya = str_replace(" ", "-", $_FILES["profil_resmi"]["name"]);
                        $uzanti = explode(".", $_FILES["profil_resmi"]["name"]);
                         

                        $hedef  = "./" . $dosya;
                        if (@$uzanti[1]) {
                            if (!file_exists($hedef)) {
                                $hedef   =  "public/profil_resimleri/".$dosya;
                                $hedefdizin = "public/profil_resimleri/";

                                
                                if(!File::isDirectory($hedefdizin)){

                                      File::makeDirectory($hedefdizin, 0755, true, true);

                                }
                                $dosya   = $dosya;
                                
                            }
                            move_uploaded_file($kaynak, $hedef);
                        }  
                     
          

                        $musteri_bilgileri->profil_resmi = $hedef;
                        $musteri_bilgileri->save();
               
        }
    }
     public function yeni_banka_hesabi_ekle(Request $request){
        $bayi_banka = "";
        if(is_numeric($request->satis_ortagi_banka_id))
            $bayi_banka = Satis_Ortagi_Banka_Hesaplari::where('id',$request->satis_ortagi_banka_id)->first();
        else  
            $bayi_banka = new Satis_Ortagi_Banka_Hesaplari(); 
       $bayi_banka->banka_id = $request->satis_ortagi_banka_adi;
       $bayi_banka->satis_ortagi_id = Auth::user()->id;
       $bayi_banka->alici = $request->satis_ortagi_alici_hesap_adi;
       $bayi_banka->sube_kodu = $request->satis_ortagi_hesap_sube_kodu;
       $bayi_banka->hesap_no = $request->satis_ortagi_hesap_no;
       $bayi_banka->iban = $request->satis_ortagi_hesap_iban;
       $bayi_banka->aktif = true;
       $bayi_banka->save();
        
        return array(
            'bankalar'=>self::satis_ortagi_banka_hesaplari($request),
            'mesaj'=>"Banka bilgisi başarı ile kaydedildi"
        );
    }
     public function bayi_banka_hesabi_kaldir(Request $request)
    {
        $bayi_banka = Satis_Ortagi_Banka_Hesaplari::where('id',$request->bayi_banka_id)->first();
        $bayi_banka->aktif = false;
        $bayi_banka->save();
        $bayi_banka_html = "";
         
        return self::satis_ortagi_banka_hesaplari($request);
    }
    public function satis_ortagi_banka_hesaplari(Request $request)
    {
      return  Satis_Ortagi_Banka_Hesaplari::with('banka')->where('satis_ortagi_id',Auth::user()->id)  
    ->get()
    ->map(function ($banka_hesabi) {
        return [
            'id'=>$banka_hesabi->id,
            'banka' => $banka_hesabi->banka->banka ?? null,
            'iban' => $banka_hesabi->iban ?? null,
            'alici' => $banka_hesabi->alici ?? null,
            'sube_kodu' => $banka_hesabi->sube_kodu ?? null,
            'hesap_no'=> $banka_hesabi->hesap_no ?? null,
                
            
            'islemler' => '<a data-value="'.$banka_hesabi->id.'" name="satis_ortagi_banka_bilgi_guncelle" class="table-action" data-toggle="tooltip" data-original-title="Banka bilgilerini güncelle" data-toggle="modal" data-target="#banka-bilgi-ekleme">
                              <i class="fas fa-user-edit"></i>
                            </a>
                            <a href="#!" data-value="'.$banka_hesabi->id.'" name="satis_ortagi_banka_bilgi_kaldir" class="table-action table-action-delete" data-toggle="tooltip" data-original-title="Kaldır">
                              <i class="fas fa-trash"></i>
                            </a>'

        
        ];

    });
    }
     public function sifre_guncelle(Request $request){
        $bayi_bilgileri = Auth::user();
        if(Hash::check($request->mevcut_sifre, $bayi_bilgileri->password )){
            $bayi_bilgileri->password= Hash::make($request->yeni_sifre);
             $bayi_bilgileri->save();
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                <span class="alert-text"><strong>Tebrikler!</strong> Şifrenizi başarı ile güncellediniz.</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>';
             
        }
          
        else
          echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="alert-icon">&times;</span>
                <span class="alert-text">Eski şifrenizi yanlış girdiniz! Lütfen yeniden deneyiniz</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>';

    }
    public function sifre_ayarlari(Request $request)
    {
      $bayi_bilgileri = Auth::user();
      return view('satisortakligi.sifre-ayarlari',['bayi_bilgileri' => $bayi_bilgileri,'pageindex'=>9,'sayfa_baslik'=>'Şifre Ayarları','bildirimler'=>self::bildirimler($request)]);
    }
    public function hakedis_hesaplama(Request $request,$musteriBilgileri)
    {
        

        $toplam = 0;
        $talepEdilmisToplam = 0;
        $kdvsizsatistoplami = 0;
        $datetime = Carbon::now('Europe/Istanbul');

        foreach ($musteriBilgileri as $guncelMusteri) {
            foreach ($guncelMusteri->hizmetler as $basariliSatisHizmetler) {
                $kdvOrani = 20;
                $kdvDahilTutar = $basariliSatisHizmetler->ucret;
                $kdvHaricTutar = $kdvDahilTutar / (1 + $kdvOrani / 100);
                $kdvsizsatistoplami += $kdvHaricTutar;
                if (!$guncelMusteri->devam_eden_odeme) {
                    if ($guncelMusteri->satis_ortagi_hakedis_odeme_durumu_id == 3) {
                        if ($guncelMusteri->satan_musteri_temsilcisi !== null || $guncelMusteri->satan_satis_ortagi != $guncelMusteri->satis_ortagi_id) {
                            $toplam += ($kdvHaricTutar / 5);
                        } else {
                            $toplam += ($kdvHaricTutar / 2.5);
                        }
                    } else {
                         if ($guncelMusteri->satan_musteri_temsilcisi !== null || $guncelMusteri->satan_satis_ortagi != $guncelMusteri->satis_ortagi_id) {
                            $talepEdilmisToplam += ($kdvHaricTutar / 5);
                        } else {
                            $talepEdilmisToplam += ($kdvHaricTutar / 2.5);
                        }
                    }
                } else {
                  if ($guncelMusteri->satis_ortagi_hakedis_odeme_durumu_id == 3){
                         if ($guncelMusteri->satan_musteri_temsilcisi !== null || $guncelMusteri->satan_satis_ortagi != $guncelMusteri->satis_ortagi_id) {
                            $toplam += ($kdvHaricTutar / 6.66666667);
                        }
                    } else {
                        if ($guncelMusteri->musteri_temsilcisi_id !== null || $guncelMusteri->salon->satis_ortagi_id != $guncelMusteri->satis_ortagi_id) {
                            $talepEdilmisToplam += ($kdvHaricTutar / 6.66666667);
                        }
                    }
                }
            }
        }

        return ['toplam' => $toplam, 'talepEdilmisToplam' => $talepEdilmisToplam,'kdvsiztoplam'=>$kdvsizsatistoplami,'hakedis_toplam'=>$toplam+$talepEdilmisToplam];
    }
    public function pasif_ortaklar(Request $request)
    {
        return view('satisortakligi.pasif-ortaklar',['pageindex'=>11,'sayfa_baslik'=>'Pasif Ortaklar','pasif_ortaklar'=>self::pasif_ortaklar_liste(),'bildirimler'=>self::bildirimler($request)]);
    }
    public function pasif_ortaklar_liste()
    {
        return SatisOrtaklari::where('aktif',true)->where('ana_satis_ortagi_id',Auth::user()->id)->where('pasif_ortak',true)->get()->map(function($pasifortak){
            return [
                'adsoyad'=>"<a href='/satisortakligi/pasif-ortak-musterileri/".$pasifortak->id."'>".$pasifortak->ad_soyad."</a>",
                'email'=>$pasifortak->email,
                'telefon'=>$pasifortak->telefon,
                'satisyuzde'=>$pasifortak->satis_yuzde,
                'islemler'=>'<a data-value="'.$pasifortak->id.'" name="pasif_ortak_bilgi_guncelle" class="table-action" data-toggle="tooltip" data-original-title="Banka bilgilerini güncelle" data-toggle="modal" data-target="#pasif-ortak-bilgi">
                              <i class="fas fa-user-edit"></i>
                            </a>
                            <a href="#" data-value="'.$pasifortak->id.'" name="pasif_ortak_kaldir" class="table-action table-action-delete" data-toggle="tooltip" data-original-title="Pasif Ortak Kaldır">
                              <i class="fas fa-trash"></i>
                            </a>'
            ];
            
        });
    }
    public function pasif_ortak_ekle_guncelle(Request $request)
    {
        $pasifortak ='';
        $eklemeguncellemetext = '';
        if($request->pasifortakid == '')
        {
            $pasifortakeski = SatisOrtaklari::where('telefon',$request->telefon)->first();
            if($pasifortakeski)
            {
                return array(
                    'type'=>'warning',
                    'mesaj'=>$request->telefon. ' telefon numarasına ait pasif ortak/satış ortağı kaydı mevcuttur!',
                    'pasifortaklar'=>self::pasif_ortaklar_liste()
                );
                exit();
            }
            else{
                $pasifortak = new SatisOrtaklari();
                $eklemeguncellemetext = ' eklendi';
            }
        }
        else
        {
            $eklemeguncellemetext = ' güncellendi';
            $pasifortak = SatisOrtaklari::where('id',$request->pasifortakid)->first();
        }

            
        $pasifortak->ad_soyad = $request->adsoyad;
        $pasifortak->email = $request->email;
        $pasifortak->telefon = $request->telefon;
        $pasifortak->pasif_ortak = true;
        $pasifortak->satis_yuzde = $request->satisyuzde;
        $pasifortak->ana_satis_ortagi_id = Auth::user()->id;
        $pasifortak->aktif = true;
        $pasifortak->save();
        return array(
            'type'=>'success',
            'mesaj'=>'Pasif ortak kaydı başarıyla '.$eklemeguncellemetext.'.',
            'pasifortaklar'=>self::pasif_ortaklar_liste()
        );
        
        
    }
    public function pasif_ortak_kaldir(Request $request)
    {
        $pasifortak = SatisOrtaklari::where('id',$request->pasifortakid)->first();
        $pasifortak->aktif = false;
        $pasifortak->save();
        return array(
            'type'=>'success',
            'mesaj'=>'Pasif ortak kaydı başarıyla kaldırıldı.',
            'pasifortaklar'=>self::pasif_ortaklar_liste()
        );   
    }
    public function pasif_ortak_musterileri(Request $request,$pasifortak)
    {
        $tum_musteriler = self::filtreli_musteri_listesi('',$pasifortak);
        $pasif_musteriler = self::filtreli_musteri_listesi('pasif',$pasifortak);
        $demosu_olan_musteriler = self::filtreli_musteri_listesi('demolar',$pasifortak);
        $aktif_musteriler = self::filtreli_musteri_listesi('aktif',$pasifortak);
        $satis_yapilamayan_musteriler = self::filtreli_musteri_listesi('satisolmamis',$pasifortak);
        return view('satisortakligi.pasif-ortak-musterileri',['pageindex'=>12,'sayfa_baslik'=>'Pasif Ortak Müşterileri','tum_musteriler'=>$tum_musteriler,'pasif_musteriler'=>$pasif_musteriler,'demosu_olan_musteriler'=>$demosu_olan_musteriler,'aktif_musteriler'=>$aktif_musteriler,'satis_yapilamayan_musteriler'=>$satis_yapilamayan_musteriler,'pasifortakid'=>$pasifortak,'pasifortakadi'=>SatisOrtaklari::where('id',$pasifortak)->value('ad_soyad'),'bildirimler'=>self::bildirimler($request)]);
    }
    public function one_cikan_ozellikler(Request $request){
            return view('satisortakligi.one-cikan-ozellikler',['pageindex'=>13,'sayfa_baslik'=>'Pazarlama Materyalleri','bildirimler'=>self::bildirimler($request)]);
    }
    public function satis_ortaklarina_sunulanlar(Request $request){
            return view('satisortakligi.satis-ortaklarina-sunulanlar',['pageindex'=>13,'sayfa_baslik'=>'Pazarlama Materyalleri','bildirimler'=>self::bildirimler($request)]);
    }
    public function basarili_satis(Request $request){
            return view('satisortakligi.basarili-satis',['pageindex'=>13,'sayfa_baslik'=>'Pazarlama Materyalleri','bildirimler'=>self::bildirimler($request)]);
    }
    public function satis_sunumu(Request $request){
            return view('satisortakligi.satis-sunumu',['pageindex'=>13,'sayfa_baslik'=>'Pazarlama Materyalleri','bildirimler'=>self::bildirimler($request)]);
    }
    public function satis_artirici_ozellikler(Request $request){
            return view('satisortakligi.satis-artirici-ozellikler',['pageindex'=>13,'sayfa_baslik'=>'Pazarlama Materyalleri','bildirimler'=>self::bildirimler($request)]);
    }
    public function reklam_kurallari(Request $request)
    {
            return view('satisortakligi.reklam-kurallari',['pageindex'=>13,'sayfa_baslik'=>'Reklam Kuralları','bildirimler'=>self::bildirimler($request)]);
    }
    public function bildirimgonder($bildirimkimlikleri,$mesaj,$baslik){
        
        $post_url_push_notification = "https://onesignal.com/api/v1/notifications";
        $headers_push_notification = array(
            'Accept: application/json',
            'Authorization: Basic MjFiNDE3ZGQtZjY3ZC00OTE3LWI1NWQtMjBlMjcxODgxNjFj',
            'Content-Type: application/json',
        ); 
        $post_data_push_notification =

            json_encode( 
                array( 
                    "app_id"=> "45403b98-a76d-4b84-8fa7-dbcf06b72dac", 
                    "include_player_ids" =>  $bildirimkimlikleri,
                    "android_channel_id" => '12d6537e-7a7d-4d1d-a838-e3fc947eaf44',
                    "contents" => array("en"=>  $mesaj),
                    "headings" =>  array("en"=> $baslik),
                    "sound" => "default",
                )

            );
        $ch_push_notification=curl_init();
        curl_setopt($ch_push_notification,CURLOPT_URL,$post_url_push_notification);
        curl_setopt($ch_push_notification,CURLOPT_POSTFIELDS,$post_data_push_notification);
        curl_setopt($ch_push_notification,CURLOPT_POST,1);
        curl_setopt($ch_push_notification,CURLOPT_TIMEOUT,5);
        curl_setopt($ch_push_notification,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch_push_notification,CURLOPT_HTTPHEADER,$headers_push_notification);
        $response_push_notifications=curl_exec($ch_push_notification);
        curl_close($ch_push_notification);
    }
    public function fesih_hesap_silme_talebi(Request $request)
    {
        $hesap_silinsin = 'Hayır';
        if(isset($request->hesap_sil))
            $hesap_silinsin = 'Evet';
        Mail::send(['html' =>"satisortakligi.mail-bildirim"],["satis_ortagi"=>Auth::user(),'bildirim'=> $request->fesih_nedeni,'hesap_silinsin'=>$hesap_silinsin], function ($message) {
            $message->from("form@randevumcepte.com.tr", Auth::user()->ad_soyad);
            $message->to('elif@webfirmam.com.tr', "RandevumCepte Satış Ortaklığı")->subject(Auth::user()->ad_soyad.' isimli satış ortağı sözleşme fesih/hesap silme talebinde bulundu');
        });
         return 'Başarılı';
    }
    public function bildirimler(Request $request)
    {
        return Bildirimler::where('satis_ortagi_id',Auth::user()->id)->get();
    }
    public function musteri_guncelle(Request $request)
    {
           
    
        $musteri_bilgisi = Salonlar::where('id',$request->salon_id)->first();
        $musteri_bilgisi->yetkili_adi = $request->yetkili_adi;
        $musteri_bilgisi->yetkili_telefon = $request->yetkili_telefon;
        $musteri_bilgisi->salon_adi = $request->salon_adi;
        $musteri_bilgisi->adres = $request->adres;
        $musteri_bilgisi->telefon_1 = $request->telefon_1;
        $musteri_bilgisi->telefon_2 = $request->telefon_2;
        $musteri_bilgisi->telefon_3 = $request->telefon_3;
        //$musteri_bilgisi->satis_ortagi_id = Auth::user()->id;
        if($request->pasif_ortak != 0)
            $musteri_bilgisi->pasif_ortak_id = $request->pasif_ortak;
     
        $musteri_bilgisi->satis_ortagi_notu = $request->satis_ortagi_notu;
        //$musteri_bilgisi->aktif = false;
        //$musteri_bilgisi->hesap_acildi = false;
        //$musteri_bilgisi->demo_hesabi = 0; // Default value
        $musteri_bilgisi->save();
        //$form = self::yeniformkaydi($request,$musteri_bilgisi);
    
        $tum_musteriler = self::filtreli_musteri_listesi('','');
        $pasif_musteriler = self::filtreli_musteri_listesi('pasif','');
        $demosu_olan_musteriler = self::filtreli_musteri_listesi('demolar','');
        $aktif_musteriler = self::filtreli_musteri_listesi('aktif','');
        $satis_yapilamayan_musteriler = self::filtreli_musteri_listesi('satisolmamis','');
    
        return response()->json([
            'status' => 'success',
            'not'=>$request->satis_ortagi_notu,
            'message' => 'Müşteri başarıyla güncellendi',
            'aktif_musteriler'=>$aktif_musteriler,
            'demosu_olan_musteriler'=>$demosu_olan_musteriler,
            'satis_yapilamayan_musteriler'=>$satis_yapilamayan_musteriler,
            'pasif_musteriler'=>$pasif_musteriler,
            'tum_musteriler'=> $tum_musteriler,

        ], 200);
    }
    public function musteri_detaylari(Request $request)
    {
        $musteri_bilgisi = Salonlar::where('id',$request->salon_id)->first();
        $ilceler = Ilceler::where('il_id',$musteri_bilgisi->il_id)->get();
        $ilceler_json = $ilceler->map(function ($ilce) {
            return [
                'id' => $ilce->id,
                'text' => $ilce->ilce_adi,
            ];
        });
        return array(
            'salon_id'=>$musteri_bilgisi->id,
            'yetkili_adi' => $musteri_bilgisi->yetkili_adi,
            'yetkili_telefon' => $musteri_bilgisi->yetkili_telefon,
            'salon_adi'=>$musteri_bilgisi->salon_adi,
            'telefon1'=>$musteri_bilgisi->telefon_1,
            'telefon2'=>$musteri_bilgisi->telefon_2,
            'telefon3'=>$musteri_bilgisi->telefon_3,
            'adres'=>$musteri_bilgisi->adres,
            'il_id'=>$musteri_bilgisi->il_id,
            'ilce_id'=>$musteri_bilgisi->ilce_id,
            'ilceler' =>$ilceler_json,
            'satis_ortagi_notu'=>$musteri_bilgisi->satis_ortagi_notu,
        );
    }
    public function cikis_yap(Request $request){

        auth('satisortakligi')->logout();
        $request->session()->invalidate(); // Oturumu tamamen temizle
        $request->session()->regenerateToken(); // Yeni bir CSRF token oluştur
        return redirect('/satisortakligi' );
    }
    public function telefon_no_format_duzenle($telefon)
{
    // Remove +90 if it exists
    $telefon = preg_replace('/^\+?90/', '', $telefon);
    
    // Remove parentheses, spaces, and other non-numeric characters
    $telefon = str_replace(["(", ")", " "], "", $telefon);
    
    // Remove leading zero if present
    $telefon = ltrim($telefon, '0');

    return $telefon;
}





}

