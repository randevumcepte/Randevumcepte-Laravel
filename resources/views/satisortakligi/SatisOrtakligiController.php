<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Salonlar;
use App\SatisOrtakligiModel\SatisOrtagiOdemeTalepleri;
use App\SatisOrtakligiModel\Bankalar;
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
use Hash;
use Excel;

class SatisOrtakligiController extends Controller
{
   
    public function __construct()
    {

         $this->middleware('auth:satisortakligi');
       
    }

    
    public function index(Request $request)
    {
         
        $musteriler = self::musterilerigetir($request);
        $aktif_musteriler = $musteriler->where('aktif', true)->where('demo_hesabi','!=',true)->count();
        $pasif_musteriler = $musteriler->where('aktif','!=', true)->count();
        $demosu_olan_musteriler = self::filtreli_musteri_listesi('demolar');

        $talep_edilen_hakedis = SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->where('satis_ortagi_hakedis_odeme_durumu_id',0)->sum('odeme_miktari');
        $gecmis_odemeler = SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->where('satis_ortagi_hakedis_odeme_durumu_id',1)->sum('odeme_miktari');
        $guncel_musteriler_yeni_satis = Musteri_Formlari::whereIn('satis_ortagi_hakedis_odeme_durumu_id',[1,3])->where('durum_id',7)->where('satis_ortagi_id',Auth::user()->id)->get();     
        return view('satisortakligi.dashboard',['musteri_bilgileri'=>$guncel_musteriler_yeni_satis,'musteriler'=> $musteriler,'talep_edilen_hakedis' => $talep_edilen_hakedis,'gecmis_odemeler' => $gecmis_odemeler,'aktif_musteriler'=>$aktif_musteriler,'pasif_musteriler'=>$pasif_musteriler,'sayfa_baslik'=>'Özet','demosu_olan_musteriler'=>$demosu_olan_musteriler,'pageindex'=>1]);
    }
    public function musterilerigetir(Request $request)
    {
        return Salonlar::leftjoin('salon_personelleri','salonlar.id','=','salon_personelleri.salon_id')->where('salonlar.satis_ortagi_id',Auth::user()->id)->get();
    }
    public function yeni_musteri_girisi(Request $request)
    {
        return view('satisortakligi.yeni-musteri',['sayfa_baslik'=>'Yeni Müşteri/Firma Kaydı','pageindex'=>2]);
    }
    public function yeni_musteri_ekle(Request $request){
       $musteri_bilgisi = new Salonlar();
       $musteri_bilgisi->yetkili_adi = $request->yetkili_adi;
       $musteri_bilgisi->yetkili_telefon = $request->yetkili_telefon;
       $musteri_bilgisi->salon_adi = $request->salon_adi;
       $musteri_bilgisi->adres = $request->adres;
       $musteri_bilgisi->telefon_1 = $request->telefon_1;
       $musteri_bilgisi->telefon_2 = $request->telefon_2;
       $musteri_bilgisi->telefon_3 = $request->telefon_3;
       $musteri_bilgisi->satis_ortagi_id = Auth::user()->id;
       
       $musteri_bilgisi->satis_ortagi_notu = $request->notlar;
       $musteri_bilgisi->aktif = false;
       $musteri_bilgisi->hesap_acildi = false;
       $musteri_bilgisi->save();
       $form = new Musteri_Formlari();
       $form->salon_id = $musteri_bilgisi->id;
       $form->satis_ortagi_id = Auth::user()->id;
       $form->satis_ortagi_hakedis_odeme_durumu_id = 3;
       $form->durum_id = 1;
       $form->save();
      
       return 'Müşteri sisteme başarı ile eklendi';
    }
        public function musteri_excelden_aktar(Request $request){
          $returnstr = "";
          try {
             if(isset($_FILES["excel_dosyasi_yeni"]["name"])){
              
                  $dosya  = $request->excel_dosyasi_yeni;
                  $kaynak = $_FILES["excel_dosyasi_yeni"]["tmp_name"];
                            $dosya  = str_replace(" ", "_", $_FILES["excel_dosyasi_yeni"]["name"]);
                            $dosya = str_replace(" ", "-", $_FILES["excel_dosyasi_yeni"]["name"]);
                            $uzanti = explode(".", $_FILES["excel_dosyasi_yeni"]["name"]);
                            $hedef  = "./" . $dosya;
                            if (@$uzanti[1]) {
                                if (!file_exists($hedef)) {
                                    $hedef   =  "public/satisortakligipanel/musterilisteleri/".$dosya;
                                    $dosya   = $dosya;
                                }
                                move_uploaded_file($kaynak, $hedef);
                            } 
                
                $dosya_veritabaninaaktarilacak = $request->excel_dosyasi_yeni;
                  if( $dosya_veritabaninaaktarilacak->isFile() ) {
                        $file = $hedef;

                        $excel = App::make('excel');
                        //$excelFile = $excel->import($file)->get();
                        Excel::import(new SalonImport, $file);
                      

                        
                         
                  $returnstr =  'Müşteriler sisteme başarı ile aktarıldı';
                  } 

            }
            else{
                $returnstr =  'Lütfen excel dosyası yükleyiniz';
                
            }
            return $returnstr;
       
          } 
          catch (\Exception $e) {
            return $e->getMessage();
          }
           
        }
    public function pasif_musteriler(Request $request){
     

     $pasif_musteriler =  self::filtreli_musteri_listesi('pasif');
     
      
      return view('satisortakligi.pasif-musteriler',['pasif_musteriler'=> $pasif_musteriler,'sayfa_baslik'=>'Pasif Müşteriler','pageindex'=>3]);
    }
    public function filtreli_musteri_listesi($querydurum)
    {
        $query = Musteri_Formlari::query();
        if($querydurum=="pasif")
             $query->where('durum_id', '!=', 7);
        elseif($querydurum=='aktif')
            $query->where('durum_id', '=', 7);
        elseif($querydurum=='demolar')
            $query->whereHas( 'salon', function($q) {$q->where('demo_hesabi', true);} );
        elseif($querydurum=='aktifsuresibitenler')
        {
            $query->whereHas('salon', function($salonQuery) {
                $salonQuery->whereDate('uyelik_bitis_tarihi', '<=', now()->addMonth()); // 1 ay içinde bitiş tarihi olanları alıyoruz
            });
        }
        return $query
    ->with('salon')  
    ->get()
    ->map(function ($musteri) {
        return [
            'salon_id' => $musteri->salon->id ?? null,
            'salon_adi' => $musteri->salon->salon_adi ?? null,
            'yetkili_bilgisi' => $musteri->salon ? $musteri->salon->yetkili_adi . ' - ' . $musteri->salon->yetkili_telefon : null,
            'created_at' => $musteri->salon ? Carbon::parse($musteri->salon->created_at)->format('d.m.Y') : null,
            'kalan_sure'=>$musteri->salon ?   self::lisans_sure_kontrol($musteri->salon->id) : null,
            'islemler' => '<div class="dropdown">
                         <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="fas fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                           
                          '.($musteri->salon->hesap_acildi != true ? '<a class="dropdown-item" name="demohesabiac" data-value="'.$musteri->salon->id.'" href="#">Demo Hesabı Aç</a>' : '').'                           
                          <a class="dropdown-item" name="satisyap" data-value="'.$musteri->salon->id.'" href="#">Satış Yap</a>
                        </div>
                      </div>
                          '
        ];
    });

    }
    public function demohesabiac(Request $request)
    {
        $salon = Salonlar::where('id',$request->salonid)->first();
         $yetkili = new IsletmeYetkilileri();

        $yetkili->name = $salon->yetkili_adi;
        $yetkili->gsm1 = $salon->yetkili_telefon;

             $yetkili->aktif = true;

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
                'mesaj'=> "Demo hesabı başarıyla oluşturulup işletme yetkilisine giriş bilgileri gönderilmiştir.");        
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
       
      $aktif_musteriler = self::filtreli_musteri_listesi('aktif');
      return view('satisortakligi.aktif-musteriler',['aktif_musteriler'=> $aktif_musteriler,'sayfa_baslik'=>'Aktif Müşteriler','pageindex'=>4]);
    }
    public function odeme_talepleri(){
       $talepler = SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->get();
       return view('satisortakligi.hakedis-talepleri',['talepler'=>$talepler,'sayfa_baslik'=>'Ödeme Talepleri','pageindex'=>5]);
    }
    public function gecmis_odemeler(){
        $odemeler = SatisOrtagiOdemeTalepleri::where('satis_ortagi_id',Auth::user()->id)->where('satis_ortagi_hakedis_odeme_durumu_id',2)->get();
        return view('satisortakligi.gecmis-odemeler',['odemeler'=>$odemeler,'sayfa_baslik'=>'Geçmiş Ödemeler','pageindex'=>6]);
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
}