<?php
use Illuminate\Database\Eloquent\Model;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonTuru;
use App\Iller;
use App\Ilceler;
use App\Ulkeler;
use App\Salonlar;
use App\SalonGorselleri;
use App\Personeller;
use App\SalonYorumlar;
use App\SalonKampanyalar;
use App\SalonHizmetler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
 
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|-
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();
Route::group(['middleware' => ['auth']],function(){
	Route::post('/randevuekle','CustomerController@randevuekle');
	Route::get('/randevularim','CustomerController@randevularim');
	Route::get('/randevuiptalet','CustomerController@randevuiptalet');
	Route::get('/randevuyorumlapuanla','CustomerController@randevuyorumlapuanla');
	Route::get('/puanyorumgetir','CustomerController@puanyorumgetir');
	Route::get('/ayarlarim','CustomerController@ayarlar');
	Route::get('/kampanyafirastbildirimackapa','CustomerController@kampanyafirsatbildirimler');
	Route::post('/sifredegistir','CustomerController@sifredegistir')->name('sifredegistir');
	Route::get('/favorilereekle','CustomerController@favorilereekle');
	Route::get('/favorilerim', 'CustomerController@favoriler');
	Route::get('/firsatlarim', 'CustomerController@firsatlar');
	Route::get('/yorumyap','CustomerController@yorumyap')->name('yorumyap');
}); 
Route::group(['middleware' => ['web']], function () {
	Route::get('/kampanyakatilim/{id}/{userid}','HomeController@kampanyakatilimanketi');
	Route::get('/etkinlikkatilim/{id}/{userid}','HomeController@etkinlikkatilimanketi');
	Route::post('/kampanyakatilimanketicevapla','HomeController@kampanyakatilimanketicevapla');
	Route::post('/etkinlikkatilimanketicevapla','HomeController@etkinlikkatilimanketicevapla');
    Route::get('/kartbilgiler','KartBankaController@kartbilgiler');
    Route::get('/odeme','HomeController@odemeisleminibaslat');
    Route::post('/odemebasarili/{kuponid}/{musteriid}', 'HomeController@odemebasarili');
    Route::post('/odemebasarisiz', 'HomeController@odemebasarisiz')->name('odemebasarisiz');
    Route::get('/basariliislem',function(){
    	return view('odemebasarili');
    });
    Route::get('/basarisizislem',function(){
    	return view('odemebasarisiz');
    });
    Route::get('/bulkmailgonder','MailController@test');
    Route::get('/smsgonder','SMSController@sendSMS');
    Route::get('/isletmeyonetim/seopaketleri','HomeController@seobasvuru');
	Route::get('/', function () {
		$hizmetkategorileri = Hizmet_Kategorisi::where('hizmet_kategorisi_adi','!=','Diğer Hizmetler')->limit(6)->inRandomOrder()->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$avantajlar = SalonKampanyalar::where('onayli',1)->inRandomOrder()->get();
		$salonlar = Salonlar::where('uyelik_turu',1)->orWhere('uyelik_turu',3)->inRandomOrder()->get();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('welcome',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler, 'avantajlar' => $avantajlar]);
	});
		Route::get('/avantajlikampanyalar', function () {
		 
		$hizmetler = Hizmetler::all();
		$hizmetkategorileri = Hizmet_Kategorisi::where('avantaj_kosesi',1)->get();
		$salonturleri = SalonTuru::limit(6)->inRandomOrder()->get();
		$salonlar = Salonlar::join('kampanyalar','kampanyalar.salon_id','=','salonlar.id')->where('kampanyalar.onayli',1)->select('salonlar.*','kampanyalar.id as kampanya_id','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat','kampanyalar.kampanya_bitis_tarihi','kampanyalar.kampanya_baslangic_tarihi','kampanyalar.hizmet_normal_fiyat')->inRandomOrder()->get();

		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('avantajlikampanyalar',['hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'hizmetkategorileri' => $hizmetkategorileri]);
	});
	foreach(Hizmet_Kategorisi::where('avantaj_kosesi',1)->get() as $hizmetkategorisi1){
	      Route::get('/avantajlikampanyalar/'.str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetkategorisi1->hizmet_kategorisi_adi))),function() use($hizmetkategorisi1){
	      		$hizmetler = Hizmetler::all();
		$hizmetkategorileri = Hizmet_Kategorisi::where('avantaj_kosesi',1)->get();
		$salonturleri = SalonTuru::limit(6)->inRandomOrder()->get();
		$salonlar = Salonlar::join('kampanyalar','kampanyalar.salon_id','=','salonlar.id')->where('kampanyalar.kampanya_kategori_id',$hizmetkategorisi1->id)->where('kampanyalar.onayli',1)->select('salonlar.*','kampanyalar.id as kampanya_id','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat','kampanyalar.kampanya_bitis_tarihi','kampanyalar.kampanya_baslangic_tarihi','kampanyalar.hizmet_normal_fiyat')->inRandomOrder()->get();

		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('avantajlikampanyalar_altsayfa',['hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'hizmetkategorileri' => $hizmetkategorileri, 'hizmetkategorisi1' => $hizmetkategorisi1]);


	      });
	}
	
Route::get('/avantajlikampanyalar/{il}/{ilce}/{isletme_id}/{isletme_adi}/{kampanya_id}', 'HomeController@avantajlikampanyalar_anasayfa')->name('kampanyadetaylari'); 
Route::get('/avantajlikampanyalar/{il}/{ilce}/{isletme_id}/{isletme_adi}/{kampanya_id}/{arama_terimi}/{arama_terim_id}', 'HomeController@avantajlikampanyalar_altsayfa'); 
	 Route::get('/avantajfiyathesapla','HomeController@avantajfiyathesapla');
		Route::get('/kampanyadetaydemo',function(){
			$hizmetler = Hizmetler::all();
			$salonturleri = SalonTuru::limit(7)->get();
			$salonlar = Salonlar::join('kampanyalar','kampanyalar.salon_id','=','salonlar.id')->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat','kampanyalar.kampanya_bitis_tarihi','kampanyalar.kampanya_baslangic_tarihi','kampanyalar.hizmet_normal_fiyat')->inRandomOrder()->get();

			$iller = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::where('aktif',1)->get();
			$salongorselleri = SalonGorselleri::all();
	    	return view('kampanyadetaylari',['hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler]);
		});
	Route::get('/hakkimizda',function(){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('hakkimizda',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => 'Hakkımızda | randevumcepte.com.tr', 'titlepage' => 'Hakkımızda']);
	});
	Route::get('/kullanici-sozlesmesi',function(){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$salon = Salonlar::where('domain',$_SERVER['SERVER_NAME'])->first();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('kullanicisozlesmesi',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri,'salon'=> $salon, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => 'Kullanıcı Sözleşmesi | randevumcepte.com.tr', 'titlepage' => 'Kullanıcı Sözleşmesi']);
	});
	Route::get('/iletisim',function(){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('iletisim',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => ',Bize Ulaşın | randevumcepte.com.tr', 'titlepage' => 'Bize Ulaşın']);
	});
	Route::get('/yeniuyekaydi',function(){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('yeniuyekaydi',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => ',İşletme Üyeliği Oluştur | randevumcepte.com.tr', 'titlepage' => 'İşletmeyi Kaydet']);
	});
	Route::get('/isletmeyonetim/yenikampanyaekle','HomeController@yenikampanyaekle');
	Route::get('/gizlilik-politikasi',function(){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$salon = Salonlar::where('domain',$_SERVER['SERVER_NAME'])->first();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('gizlilik',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri,'salon' => $salon, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => 'Gizlilik Politikası | randevumcepte.com.tr', 'titlepage' => 'Gizlilik Politikası']);
	});
	Route::get('/smsiptal/{telefon}',function($telefon){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('smsiptal',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => 'SMS İptal Formu | randevumcepte.com.tr', 'titlepage' => 'SMS İptal Formu','telefon' => $telefon]);
	});
	Route::get('/epostaiptal/{eposta}',function($eposta){
		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		$hizmetler = Hizmetler::all();
		$salonturleri = SalonTuru::all();
		$salonlar = Salonlar::inRandomOrder()->get();
		$iller = Iller::where('aktif',1)->get();
		$ilceler = Ilceler::where('aktif',1)->get();
		$salongorselleri = SalonGorselleri::all();
    	return view('mailiptal',['hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler, 'salonturleri' => $salonturleri,'salonlar' => $salonlar, 'salongorselleri' => $salongorselleri, 'iller' => $iller, 'ilceler' => $ilceler,'titlehead' => 'E-posta İptal Formu | randevumcepte.com.tr', 'titlepage' => 'E-posta İptal Formu','eposta' => $eposta]);
	});
	Route::get('/avantajkartodemeadimi','HomeController@avantajkartodemeadimi');
	Route::get('/smskampanyabildirimiptal','HomeController@smskampanyabildirimiptal');
	Route::get('/mailkampanyabildirimiptal','HomeController@mailkampanyabildirimiptal');
   Route::get('/randevual/{hizmet}/{id}','HomeController@randevual');
   Route::get('/saatgetir','HomeController@saatgetir');
   Route::post('/randevuonayla','HomeController@randevuonayla1');
   Route::post('/randevuonaylaauth','HomeController@randevuonaylaauth');
   Route::get('/personelgetir/{id}','HomeController@personeladiminagec');
	Route::get('/personelgetir-sube/{id}/{subeid}','HomeController@personelgetir');


   Route::get('/tarihsaatadiminagec/{id}','HomeController@tarihsaatadiminagec');
   Route::get('/personelbilgigetir/{id}','HomeController@personelbilgigetir');
   Route::get('/randevuonayla/{salonno}/{hizmetler}/{personeller}/{randevutarihi}/{randevusaati}','CustomerController@randevuonayla');
   Route::get('/kullanicikontrolet','HomeController@kullanicikontrolet');
   Route::get('/sifregonder','HomeController@sifregonder');
   Route::get('/sifregonder2','HomeController@sifregonder2');
   Route::post('/salonlar','HomeController@salonara')->name('salonara');
	Route::get('/{isletme_adi}-{isletme_id}', 'HomeController@salonDetay_anasayfa')->name('salondetaylari'); 
	Route::get('/', 'HomeController@salonDetay'); 
	//Route::get('/', 'HomeController@salonDetay_anasayfa'); 
	Route::get('/avantajsatinal/{kampanyaid}','HomeController@avantajsatinal');
	Route::get('/{isletme_turu}/{il}/{ilce}/{isletme_id}/{isletme_adi}/{arama_terimi}/{arama_terim_id}', 'HomeController@salonDetay_altsayfa')->name('salondetaylari_altsayfa'); 
	Route::get('/isletmeyonetim/smspaketleri','HomeController@paketsatinalyukseltmeornek');
	Route::get('/isletmeyonetim/smspaketsatinal/{paketno}','HomeController@smspaketsatinal');
	 
   foreach(Hizmet_Kategorisi::where('avantaj_kosesi','!=',1)->get() as $hizmetkategorileri){
   	    Route::get('/'.str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetkategorileri->hizmet_kategorisi_adi))),function() use($hizmetkategorileri){
   	    	  $il = "";
            $ilce = "";
            
		    $hizmetmenu = Hizmetler::all();
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$iller = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::all();
            $salonsunulanhizmetler = SalonHizmetler::where('hizmet_kategori_id',$hizmetkategorileri->id)->pluck('salon_id')->toArray();

            $salonturleri = SalonTuru::all();
            $hizmetkategorilerliste = Hizmet_Kategorisi::limit(7)->get();
           
            	$kampanyalar = Salonlar::leftjoin('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
				$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
				$salonturu = SalonTuru::whereIn('id',$salonsunulanhizmetler)->first();

				$baslik = $hizmetkategorileri->hizmet_kategorisi_adi.' Hizmetleri & Fiyatları';

				return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorilerliste,'hizmetler'=>$hizmetmenu,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$iller, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar,'salonturleri' => $salonturleri, 'sayfabaslik' =>$baslik]);
            

   	    });
   	    foreach (Iller::where('aktif',1)->get() as $iller) {
   	    	 Route::get('/'.str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetkategorileri->hizmet_kategorisi_adi))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($iller->il_adi)))),function() use($hizmetkategorileri,$iller){
   	    	$il = $iller->il_adi;
            $ilce = "";
            
		    $hizmetmenu = Hizmetler::all();
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$il_listesi = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::all();
            $salonsunulanhizmetler = SalonHizmetler::where('hizmet_kategori_id',$hizmetkategorileri->id)->pluck('salon_id')->toArray();

            $salonturleri = SalonTuru::all();
            $hizmetkategorilerliste = Hizmet_Kategorisi::limit(7)->get();
           
            	$kampanyalar = Salonlar::leftjoin('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
				$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
				$salonturu = SalonTuru::whereIn('id',$salonsunulanhizmetler)->first();

				$baslik = $il.' '.$hizmetkategorileri->hizmet_kategorisi_adi.' Hizmetleri & Fiyatları';

				return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorilerliste,'hizmetler'=>$hizmetmenu,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$il_listesi, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar,'salonturleri' => $salonturleri, 'sayfabaslik' =>$baslik]);
            

   	    });
   	    }
   }
  
    
	foreach(SalonTuru::all() as $isletme_turu){
	    
 
		Route::get("/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme_turu->salon_turu_adi))),function() use($isletme_turu){
            $il = "";
            $ilce = "";
           $hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
			$hizmetler = Hizmetler::all();	 
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$iller = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::where('aktif',1)->get();
			$kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_turu_id',$isletme_turu->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
			$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_turu_id',$isletme_turu->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->select('salonlar.*')->get();
			$salonturu = SalonTuru::where('id',$isletme_turu->id)->first();
			$salonturleri = SalonTuru::all();
            $baslik = $isletme_turu->salon_turu_adi;;
			return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$iller, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar, 'salonturleri' => $salonturleri,'sayfabaslik' => $baslik]);
		});


        foreach (Iller::where('aktif',1)->get() as $iller) {
        	 Route::get("/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme_turu->salon_turu_adi)))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($iller->il_adi))),function() use($isletme_turu,$iller){
            $il = $iller->il_adi;
            $ilce = "";
           $hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
			$hizmetler = Hizmetler::all();	 
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$il_listesi = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::where('aktif',1)->get();
			$salonturleri = SalonTuru::all();
			$kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_turu_id',$isletme_turu->id)->where('salonlar.il_id',$iller->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
			$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_turu_id',$isletme_turu->id)->where('salonlar.il_id',$iller->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
			$salonturu = SalonTuru::where('id',$isletme_turu->id)->first();
			 $baslik = $il .' '.$isletme_turu->salon_turu_adi;;
            return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$il_listesi, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar, 'salonturleri' => $salonturleri,'sayfabaslik' => $baslik]);
			});
        	foreach(Ilceler::where('aktif',1)->get() as $ilceler){
        			Route::get("/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme_turu->salon_turu_adi)))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($iller->il_adi)))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilceler->ilce_adi))),function() use($isletme_turu,$iller,$ilceler){
           			 $il = $iller->il_adi;
            		$ilce = $ilceler->ilce_adi;
            		$il_listesi = Iller::where('aktif',1)->get();
					$ilce_listesi = Ilceler::where('aktif',1)->get();
           		$hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
					$hizmetler = Hizmetler::all();	 
				$salongorselleri = SalonGorselleri::all();
				$salonyorumlar = SalonYorumlar::all();
				$kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_turu_id',$isletme_turu->id)->where('salonlar.il_id',$iller->id)->where('salonlar.ilce_id',$ilceler->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
				$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->where('salonlar.salon_turu_id',$isletme_turu->id)->where('salonlar.il_id',$iller->id)->where('salonlar.ilce_id',$ilceler->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
				$salonturu = SalonTuru::where('id',$isletme_turu->id)->first();
				$salonturleri = SalonTuru::all();
				 $baslik = $il .' '.$ilce.' '.$isletme_turu->salon_turu_adi;
            return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetler,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$il_listesi, 'ilceler' => $ilce_listesi, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar, 'salonturleri' => $salonturleri,'sayfabaslik' => $baslik]);
			});
        	}
        }

	 
	}
	  foreach(Hizmetler::all() as $hizmetler){
     	 Route::get("/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetler->hizmet_adi))),function() use($hizmetler){
     	 	  $il = "";
            $ilce = "";
           $hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		    $hizmetmenu = Hizmetler::all();
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$iller = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::all();
            $salonsunulanhizmetler = SalonHizmetler::where('hizmet_id',$hizmetler->id)->pluck('salon_id')->toArray();

            $salonturleri = SalonTuru::all();

           
            	 
            	$kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
				$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
				$salonturu = SalonTuru::whereIn('id',$salonsunulanhizmetler)->first();

				$baslik =   $baslik =  $il .' '. $hizmetler->hizmet_adi .' Fiyatları';

				return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetmenu,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$iller, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar,'salonturleri' => $salonturleri, 'sayfabaslik' =>$baslik,'salonsunulanhizmetler' => $salonsunulanhizmetler]);
            
            
            

			
     	 });

     	  foreach (Iller::where('aktif',1)->get() as $iller) {
        	 Route::get("/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetler->hizmet_adi)))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($iller->il_adi))),function() use($hizmetler,$iller){
        	 	  $il = $iller->il_adi;
            $ilce = "";
           $hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		    $hizmetmenu = Hizmetler::all();
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$il_listesi = Iller::where('aktif',1)->get();
			$ilceler = Ilceler::all();
            $salonsunulanhizmetler = SalonHizmetler::where('hizmet_id',$hizmetler->id)->pluck('salon_id')->toArray();
            $salonturleri = SalonTuru::all();
           
            
            	 $baslik =  $il .' '. $hizmetler->hizmet_adi .' Fiyatları';
            	$kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->where('salonlar.il_id',$iller->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
				$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->where('salonlar.il_id',$iller->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
				$salonturu = SalonTuru::whereIn('id',$salonsunulanhizmetler)->first();
				return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetmenu,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$il_listesi, 'ilceler' => $ilceler, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar,'salonturleri' => $salonturleri,'sayfabaslik' => $baslik,'salonsunulanhizmetler' =>$salonsunulanhizmetler]);
                


        	 });
        	 foreach(Ilceler::where('aktif',1)->get() as $ilceler){
        	 	Route::get("/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetler->hizmet_adi)))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($iller->il_adi)))."/".str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilceler->ilce_adi))),function() use($hizmetler,$iller,$ilceler){

        	 		 $il = $iller->il_adi;
            $ilce = $ilceler->ilce_adi;
           $hizmetkategorileri = Hizmet_Kategorisi::limit(7)->get();
		    $hizmetmenu = Hizmetler::all();
			$salongorselleri = SalonGorselleri::all();
			$salonyorumlar = SalonYorumlar::all();
			$il_listesi = Iller::where('aktif',1)->get();
			$ilce_listesi = Ilceler::all();
            $salonsunulanhizmetler = SalonHizmetler::where('hizmet_id',$hizmetler->id)->pluck('salon_id')->toArray();
            $salonturleri = SalonTuru::all();

            
            	 $baslik =  $il .' '.$ilce. ' '. $hizmetler->hizmet_adi .' Fiyatları';
            	$kampanyalar = Salonlar::join('kampanyalar', 'kampanyalar.salon_id','=','salonlar.id')->leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->where('salonlar.il_id',$iller->id)->where('salonlar.ilce_id',$ilceler->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*','kampanyalar.kampanya_baslik','kampanyalar.kampanya_aciklama','kampanyalar.kampanya_fiyat')->get();
				$salonlar = Salonlar::leftjoin('salon_sunulan_hizmetler','salon_sunulan_hizmetler.salon_id','=','salonlar.id')->leftjoin('salon_puanlar','salon_puanlar.salon_id','=','salonlar.id')->whereIn('salonlar.id',$salonsunulanhizmetler)->where('salonlar.il_id',$iller->id)->where('salonlar.ilce_id',$ilceler->id)->groupBy('salon_sunulan_hizmetler.salon_id')->groupBy('salon_puanlar.salon_id')->orderByRaw('sum(salon_puanlar.puan) / count(salon_puanlar.puan) desc')->orderByRaw('min(salon_sunulan_hizmetler.baslangic_fiyat) asc')->orderByRaw('count(salon_puanlar.puan) desc') ->select('salonlar.*')->get();
				$salonturu = SalonTuru::whereIn('id',$salonsunulanhizmetler)->first();
				return view('salonlistesi',['salonlar'=>$salonlar,'salonturu' => $salonturu,'hizmetkategorileri' => $hizmetkategorileri,'hizmetler'=>$hizmetmenu,'salongorselleri' => $salongorselleri,'il'=>$il , 'ilce'=> $ilce,'iller' =>$il_listesi, 'ilceler' => $ilce_listesi, 'salonyorumlar' => $salonyorumlar, 'kampanyalar' => $kampanyalar,'salonturleri' => $salonturleri,'sayfabaslik'=>$baslik,'salonsunulanhizmetler' =>$salonsunulanhizmetler]);
             


        	 	});

        	 }
          }

     }

});

Route::get('/profilim', 'HomeController@profilim');
Route::post('/musteri_profil_guncelleme', 'HomeController@musteri_profil_guncelleme')->name('musteri_profil_guncelleme');
Route::get('/musteri_profil_resmi_kaldirma', 'HomeController@musteri_profil_resmi_kaldirma')->name('musteri_profil_resmi_kaldirma');
 
Route::prefix('sistemyonetim')->group(function() {
	Route::get('/','AdminController@index')->name('superadmin.dashboard');
	Route::get('/girisyap', 'AuthSuperAdmin\LoginController@showSuperAdminLoginForm')->name('superadmin.login');

	Route::post('/girisyap','AuthSuperAdmin\LoginController@login')->name('superadmin.login.submit');
	Route::get('/isletmeler','AdminController@isletmeler')->name('superadmin.isletmeler');
    Route::get('/avantajlar','AdminController@avantajlar')->name('superadmin.avantajlar');
    Route::get('/yeniavantaj','AdminController@yeniavantaj');
    Route::post('/yeniavantajyayinla','AdminController@yeniavantajyayinla');
    Route::get('/avantajpasifdurumaal','AdminController@avantajpasifdurumaal');
    Route::get('/avantajdetay/{id}','AdminController@avantajdetayi');
    Route::post('/mevcutavantajduzenleme','AdminController@mevcutavantajduzenleme');
	Route::get('/isletmedetay/{id}','AdminController@isletmedetay');
	Route::get('/hizmetler','AdminController@hizmetlistesi');
	Route::get('/yenihizmetekleme','AdminController@yenihizmetekleme');
	Route::get('/yenihizmetkategoriekleme','AdminController@yenihizmetkategoriekleme');
	Route::get('/hizmetkategorisisil','AdminController@hizmetkategorisisil');
	Route::get('/hizmetsil','AdminController@hizmetsil');
	Route::get('/yenisalonhizmetiekle','AdminController@yenisalonhizmetiekle');
	Route::get('/isletmeaciklamaekle' ,'AdminController@isletmeaciklamaekle');
	Route::get('/aciklamaguncelle' ,'AdminController@aciklamaguncelle');
	Route::post('/calismasaatiguncelle','AdminController@calismasaatiguncelle');
	Route::get('/personeldetay/{id}','AdminController@personeldetay');
	Route::get('/yeniisletme','AdminController@yeniisletme');
	Route::get('/yetkililer','AdminController@isletmeyetkilileri');
	Route::get('/yetkilidetay/{id}','AdminController@yetkilidetay');
	Route::post('/yetkilidetayduzenleme','AdminController@yetkilidetayguncelle');
	Route::post('/yetkiliresimguncelle','AdminController@yetkiliresimguncelle');
	Route::get('/musteritemsilcileri','AdminController@musteritemsilcileri');
	Route::get('/yenimusteritemsilcisi','AdminController@yenimusteritemsilcisiekle');
	Route::post('/yenimusteritemsilcisiekle','AdminController@yenimusteritemsilcisi');
	Route::get('/sistemeyenihizmetekle','AdminController@sistemeyenihizmetekle');
	Route::get('/ilcelistele','AdminController@ilcelistele');
	Route::get('/yeniyetkilibilgisiekle','AdminController@yeniyetkilibilgisiekle');
	Route::get('/yeniisletmeturuekle','AdminController@yeniisletmeturuekle');
	Route::post('/personelprofilresmiyukle/{id}','AdminController@personelprofilresmiyukle');
	Route::get('/yenipersonelgir','AdminController@yenipersonelgir');
	Route::post('/yeniisletmeekle','AdminController@yeniisletmeekle');
	Route::get('/gorselsil','AdminController@gorselsil');
	Route::get('/kayitlisalongorselisayisi','AdminController@kayitlisalongorselisayisi');
	Route::post('/mevcutisletmeduzenleme','AdminController@mevcutisletmeduzenleme');
	Route::get('/personelbilgiguncelle/{id}','AdminController@personelbilgiguncelle');
	Route::get('/personelhizmetekle/{id}','AdminController@personelhizmetekle');
	Route::get('/personelhizmetsil/{id}','AdminController@personelhizmetsil');
	Route::get('/salonhizmetsil/{id}','AdminController@salonhizmetsil');
	Route::get('/personelsil/{id}','AdminController@personelsil');
	Route::get('/cikisyap','AdminController@cikisyap');


});

Route::prefix('isletmeyonetim')->group(function() {

	
	Route::get('/girisyap', 'AuthStoreAdmin\LoginController@showStoreAdminLoginForm')->name('isletmeadmin.login');
	Route::post('/girisyap','AuthStoreAdmin\LoginController@login')->name('isletmeyonetim.login.submit');
	Route::get('/sifremiunuttum','AuthStoreAdmin\LoginController@sifremiunuttum')->name('isletmeadmin.sifremiunuttum');
	Route::post('/sifregonder','AuthStoreAdmin\LoginController@sifregonder');
	Route::post('/sifredegistir','AuthStoreAdmin\LoginController@sifredegistir');
	Route::get('/kayitol','AuthStoreAdmin\RegisterController@kayit_ol');
	Route::get('/','StoreAdminController@index')->name('isletmeadmin.dashboard');
	Route::get('/randevular','StoreAdminController@randevular')->name('isletmeadmin.randevular');
	Route::get('/randevular-filtre','StoreAdminController@randevularfiltre');
	Route::get('/randevusil','StoreAdminController@randevu_sil');
	Route::get('/randevugetir','StoreAdminController@randevugetir');
	Route::get('/islemsonuraporugir','StoreAdminController@islemsonuraporugir');
	Route::get('/islemraporlari','StoreAdminController@islemraporlari');
	Route::get('/raporlar-filtre','StoreAdminController@islemraporlari_filtre');
	Route::get('/islemgetir','StoreAdminController@islemgetir');
	Route::get('/islemdeneme','StoreAdminController@islemdeneme');
	Route::get('/islemdetaygetir','StoreAdminController@isletmdetaygetir');
	Route::get('/islemkalanodemealindi','StoreAdminController@islemkalanodemealindi');
   Route::get('/saglikbilgilerigir','StoreAdminController@saglikbilgilerigir');
	Route::get('/isletmem','StoreAdminController@isletme');
    Route::get('/kasadefteri','StoreAdminController@kasadefteri');
    Route::get('/giderekle','StoreAdminController@giderekle');
    Route::get('/gelirekle','StoreAdminController@gelirekle');
    Route::get('/kasadefterigirdisil','StoreAdminController@kasadefterigirdisil');
    Route::get('/kasadefterifiltre','StoreAdminController@kasadefterifiltre'); 
    Route::get('/calismasaatiguncelle','StoreAdminController@calismasaatiguncelle');
    Route::get('/personeldetay/{id}','StoreAdminController@personeldetay');

	Route::post('/hizmetekleduzenle','StoreAdminController@hizmetekleduzenle');
	Route::get('/personelhizmetara/{id}','StoreAdminController@personelhizmetara');
	Route::get('/personelhizmetsil/{id}','StoreAdminController@personelhizmetsil');
	Route::post('/profilresimyukle','StoreAdminController@profilresimyukle');
	Route::get('/personelekle','StoreAdminController@personelekle');
	Route::get('/personelsil','StoreAdminController@personelsil');
	Route::get('/randevuyukle','StoreAdminController@takvim_degistir');
	Route::post('/randevuguncelle','StoreAdminController@randevuguncelle');
	Route::post('/randevuguncelledragdropresize','StoreAdminController@randevu_resize_drop');
	Route::get('/randevuiptalet','StoreAdminController@randevuiptalet');
	Route::get('/randevuonayla','StoreAdminController@randevuonayla');
	Route::get('/randevubilgiguncelle','StoreAdminController@randevubilgiguncelle');
	//Route::middleware('role:Hesap Sahibi,Süpervizör,Yönetici')->get('/ayarlar','StoreAdminController@ayarlar');
	Route::get('/ayarlar','StoreAdminController@ayarlar');
	Route::get('/sifredegistir','StoreAdminController@sifredegistir');
	Route::post('/yetkilibilgiguncelle','StoreAdminController@yetkilibilgiguncelle');
	Route::post('/sistemeyenihizmetekle','StoreAdminController@sistemeyenihizmetekle');
	Route::get('/yenisubeekle','StoreAdminController@yenisubeekle');
	Route::get('/subepasifet','StoreAdminController@subepasifet');
	Route::get('/subeaktifet','StoreAdminController@subeaktifet');

	Route::get('/randevudetay/{id}','StoreAdminController@randevudetay');
	Route::get('/urunler','StoreAdminController@urunler');
	Route::post('/urunekleguncelle','StoreAdminController@urun_ekle_guncelle');
	Route::post('/paketekleguncelle','StoreAdminController@paket_ekle_guncelle');
	Route::get('/paketdetayigetir','StoreAdminController@paketdetayigetir');
	Route::post('/urunsil','StoreAdminController@urun_sil');
	Route::post('/paketsil','StoreAdminController@paket_sil');

	Route::get('/hizmetsurefiyatgetir','StoreAdminController@hizmetsurefiyatgetir');
	Route::get('/urunfiyatgetir','StoreAdminController@urunfiyatgetir');
	Route::post('/urunsatisekle','StoreAdminController@urunsatisiekle');
	Route::post('/urunadisyondansil','StoreAdminController@urunadisyondansil');
	Route::post('/paketadisyondansil','StoreAdminController@paketadisyondansil');
	Route::get('/acikadisyonlar','StoreAdminController@acikadisyonlar');

	Route::post('/salonhizmetsil','StoreAdminController@salonhizmetsil');
	Route::get('/yenipersonelgir','StoreAdminController@yenipersonelgir');
	Route::post('/mevcutisletmeduzenleme','StoreAdminController@mevcutisletmeduzenleme');
	Route::get('/personelbilgiguncelle/{id}','StoreAdminController@personelbilgiguncelle');
	Route::get('/kayitlisalongorselisayisi','StoreAdminController@kayitlisalongorselisayisi');
	Route::get('/gorselsil','StoreAdminController@gorselsil');
	Route::get('/yenirandevu','StoreAdminController@yenirandevu');
	Route::get('/randevupersonelgetir','StoreAdminController@randevupersonelgetir');
	Route::post('/yenirandevuekle','StoreAdminController@yenirandevuekle');
	Route::get('/calismasaatigetir','StoreAdminController@calismasaatigetir');
	Route::get('/musteribilgigetir','StoreAdminController@musteribilgigetir');
	Route::get('/avantajlar','StoreAdminController@kampanyalar');
	Route::get('/avantajkupongetir','StoreAdminController@avantajkupongetir');
	Route::get('/avantajkuponkullan','StoreAdminController@avantajkuponkullan');
	Route::get('/gorselyukle','StoreAdminController@gorselyukle')->name('gorselyukle');
	Route::get('/toplusms','StoreAdminController@toplusmsgonder');
	Route::get('/toplumail','StoreAdminController@toplumailgonder');
	Route::get('/smslistesi','StoreAdminController@smslistesi');
	Route::get('/smslistedetay/{listeid}','StoreAdminController@smslistedetay');
	Route::get('/smslistedetaybilgigetir','StoreAdminController@smslistedetaybilgigetir');
	Route::get('/smsbilgiguncelle','StoreAdminController@smsbilgiguncelle');
	Route::post('/yenismslistesiekle','StoreAdminController@yenismslistesiekle');
	//Route::get('/smspaketleri','StoreAdminController@smspaketleri');
	Route::get('/hazirsmsmesajlari','StoreAdminController@hazirsmsmesajlari');
	Route::get('/maillistesi','StoreAdminController@maillistesi');
	Route::get('/mailpaketleri','StoreAdminController@mailpaketleri');
	Route::get('/hazirmailtaslaklari','StoreAdminController@hazirmailtaslaklari');
	Route::get('/smsraporlar','StoreAdminController@smsraporlar');
	Route::get('/smstaslakolarakkaydet','StoreAdminController@smstaslakolarakkaydet');
	Route::post('/toplusmsgonder','StoreAdminController@toplusmsgonderme');
	Route::get('/musteriler','StoreAdminController@musteriliste');
	Route::get('/personeller','StoreAdminController@personeller');
	Route::post('/personelekleduzenle','StoreAdminController@personelekleduzenle');
	Route::get('/personelsistemyetkikaldir','StoreAdminController@personelsistemyetkikaldir');
	Route::get('/personelyetkiolustur','StoreAdminController@personelyetkiolustur');
	Route::get('/personelbilgikaldir','StoreAdminController@personelbilgikaldir');
	Route::get('/musteriexceleaktar','StoreAdminController@musteriexceleaktar')->name('musteriexceleaktar');
	Route::get('/musteriportfoykaldir','StoreAdminController@musteriportfoykaldir');
	Route::post('/yenimusterilistesiekle','StoreAdminController@yenimusterilistesiekle');
	Route::get('/musteridetay/{id}','StoreAdminController@musteridetay');
	Route::get('/musteribilgiguncelle','StoreAdminController@musteribilgiguncelle');
	Route::get('/avantajraporlar','StoreAdminController@avantajraporlar');
	Route::get('/kampanyadetaylari/{id}','StoreAdminController@kampanyadetaylari');
	Route::get('/toplusmsbasvuru','StoreAdminController@toplusmsbasvuru');
	Route::get('/cikisyap','StoreAdminController@cikisyap');
	Route::get('/musteriarama','StoreAdminController@musteriarama');
	Route::post('/musteriekleguncelle','StoreAdminController@musteriekleguncelle');
	Route::post('/calismasaatleriduzenle','StoreAdminController@calismasaatleriduzenle');
	Route::post('/isletmebilgiguncelle','StoreAdminController@isletmebilgiguncelle');
	Route::post('/saatkapamaekle','StoreAdminController@saatkapamaekle');
	Route::post('/kapalisaatsil','StoreAdminController@kapalisaatsil');
	Route::get('/yaklasandogumgunleri','StoreAdminController@yaklasan_dogumgunleri');
	Route::post('/adisyonhizmetekle','StoreAdminController@adisyonhizmetekle');
	Route::post('/tahsilatekle','StoreAdminController@tahsilatekle');
	Route::get('/adisyonlar','StoreAdminController@adisyonlar');
	Route::post('/tahsilatkaldir','StoreAdminController@tahsilatkaldir');
	Route::post('/alacakekleduzenle','StoreAdminController@alacakekleduzenle');
	Route::get('/ongorusmeler','StoreAdminController@ongorusmeler');
	Route::post('/ongorusmeekleduzenle','StoreAdminController@ongorusmeekleduzenle');
	Route::get('/ongorusmedetay','StoreAdminController@ongorusmedetay');
	Route::get('/hatirlatmasmsgonder','StoreAdminController@hatirlatmasmsgonder');
	Route::get('/smstest','StoreAdminController@smscoklutest');
	Route::get('denemedb2','StoreAdminController@denemedb2');
	Route::get('/paketsatislari','StoreAdminController@paketsatislari');
	Route::get('/randevular-liste','StoreAdminController@randevuliste');
	Route::post('/paketsatisekle','StoreAdminController@paketsatisekle');
	Route::post('/masrafekleduzenle','StoreAdminController@masrafekleduzenle');
	Route::get('/masraflar','StoreAdminController@masraflar');
	Route::get('/alacaklar','StoreAdminController@alacaklar');
	Route::get('/seanslar','StoreAdminController@seanslar');
	Route::get('/bildirimkontrolet','StoreAdminController@bildirimkontrolet');
	Route::post('/bildirimokundu','StoreAdminController@bildirimokundu');
	Route::get('/randevulistefiltre','StoreAdminController@randevu_liste_filtre');
	Route::get('/listedeneme','StoreAdminController@liste_deneme');
	Route::get('/senetler', 'StoreAdminController@senetler');
   Route::post('/pdf', 'StoreAdminController@download');
   Route::get('/qrpdf', 'StoreAdminController@QRdownload')->name('download');
   Route::get('/profil','StoreAdminController@profilbilgileri');
   Route::get('/hizmetpersoneldeneme','StoreAdminController@denemesql');
   Route::post('/yeniisletmeekle','StoreAdminController@yeniisletmeekle');
   Route::post('/randevuayarguncelle','StoreAdminController@randevuayarguncelle');
   Route::get('/rolata','StoreAdminController@assing_roles');
   Route::get('/odeme','StoreAdminController@odeme_sayfasi');
   Route::post('/etkinlikekleduzenle','StoreAdminController@etkinlikekleduzenle');
   Route::get('/hizmetpersonelsecimigetir','StoreAdminController@hizmetpersonelsecimigetir');
   Route::get('/personellistegetir','StoreAdminController@personel_liste_getir');
   Route::get('/ongorusmegetir','StoreAdminController@ongorusmegetir');
	Route::post('/sistemeyenihizmetkategorisiekle','StoreAdminController@sistemeyenihizmetkategorisiekle');
   Route::get('/etkinlik','StoreAdminController@etkinlikler');
  	Route::get('/etkinlikdetay','StoreAdminController@etkinlikdetay');
  	Route::post('/odamusaitisaretle','StoreAdminController@odamusaitisaretle');
   Route::post('/odamusaitdegilisaretle','StoreAdminController@odamusaitdegilisaretle');
   Route::post('/cihazmusaitisaretle','StoreAdminController@cihazmusaitisaretle');
   Route::post('/cihazmusaitdegilisaretle','StoreAdminController@cihazmusaitdegilisaretle');
   Route::post('/odasil','StoreAdminController@oda_sil');
	Route::post('/cihazsil','StoreAdminController@cihaz_sil');
	Route::post('/cihazekleduzenle','StoreAdminController@cihazekleduzenle');
	Route::post('/odaekleduzenle','StoreAdminController@odaekleduzenle');
	Route::post('/randevudogrulamakodugonder','StoreAdminController@randevu_dogrulama_kodu_gonder');
	Route::post('/hizmetdogrulamakodugonder','StoreAdminController@hizmetdogrulamakodugonder');
   Route::post('/adisyonhizmetguncelle','StoreAdminController@adisyonhizmetguncelle');
   Route::post('/adisyonhizmetpersonelguncelle','StoreAdminController@adisyonhizmetpersonelguncelle');
   Route::post('/adisyonhizmethizmetguncelle','StoreAdminController@adisyonhizmethizmetguncelle');
   Route::post('/adisyonhizmetfiyatguncelle','StoreAdminController@adisyonhizmetfiyatguncelle');
	Route::post('/seansdogrulamakodugonder','StoreAdminController@seans_dogrulama_kodu_gonder');
	Route::post('/randevugeldiisaretleadisyonolustur','StoreAdminController@randevu_geldi_isaretle_adisyon_olustur');
	Route::get('/adisyon/{id}','StoreAdminController@adisyondetay');
	Route::post('/seansgirdiguncelle','StoreAdminController@seansgirdiguncelle');
	Route::post('/senetekleguncelle','StoreAdminController@senetekleguncelle');
	Route::get('/senetfiltre','StoreAdminController@senetfiltre');
	Route::get('/senetvadegetir','StoreAdminController@senetvadegetir');
	Route::get('/kampanya_yonetimi','StoreAdminController@kampanya_yonetimi_liste');
	Route::get('/kampanyadetay','StoreAdminController@kampanyadetay');
	Route::post('/kampanyasil','StoreAdminController@kampanya_sil');
	Route::post('/etkinliksil','StoreAdminController@etkinlik_sil');
	Route::post('/kampanyaekleduzenle','StoreAdminController@kampanyaekleduzenle');
	Route::get('/etkinlikduzenle','StoreAdminController@etkinlikduzenle');
	Route::post('/seanstanrandevuolustur','StoreAdminController@seanstanrandevuolustur');
	Route::get('/randevudetayigetir','StoreAdminController@randevudetayigetir');
	Route::get('/kategoriyegorehizmetgetir','StoreAdminController@kategoriyegorehizmetgetir');
	Route::post('/ongorusmesatisyapildi','StoreAdminController@ongorusmesatisyapildi');
	Route::post('/randevuyagelmedi','StoreAdminController@randevuyagelmedi');
	Route::get('/seanstakip','StoreAdminController@seanstakip');
	Route::post('/grupsil','StoreAdminController@grup_sil');
	Route::post('/grupduzenle','StoreAdminController@grupduzenle');
	 
	Route::get('/paketfiyatgetir','StoreAdminController@paketfiyatgetir');
	Route::post('/yeni-adisyon','StoreAdminController@yeni_adisyon');
	Route::get('/adisyon-filtreli-getir','StoreAdminController@adisyon_filtreli_getir');
	Route::get('/adisyon-filtreli-getir-perosnel','StoreAdminController@adisyon_filtreli_getir_personel');
	Route::post('/sms-ayar-kaydet','StoreAdminController@sms_ayar_kaydet');
	Route::post('/ongorusmesatisyapilmadi','StoreAdminController@ongorusmesatisyapilmadi');
	 
	Route::post('/grupsil','StoreAdminController@grup_sil');
	Route::post('/grupduzenle','StoreAdminController@grupduzenle');
	Route::post('/grupsmsekle','StoreAdminController@grupsmsekle');
	Route::post('/toplusmseklegonder','StoreAdminController@toplusmseklegonder');
	Route::get('/cinsiyetegore','StoreAdminController@cinsiyetegore');
	Route::get('/hizmetegore','StoreAdminController@hizmetegore');
	Route::get('/cinsiyetehizmetegore','StoreAdminController@cinsiyetehizmetegore');
	Route::post('/filtrelismsgonder','StoreAdminController@filtrelismsgonder');
	Route::post('/grupsmsgonder','StoreAdminController@grupsmsgonderme');
	Route::post('/musteriportfoyeekle','StoreAdminController@musteriportfoyeekle');
	Route::get('/musait-randevu-saatlerini-getir','StoreAdminController@musait_randevu_saatlerini_getir');
	Route::get('/musait-randevu-saatlerini-getir2','StoreAdminController@musait_randevu_saatlerini_getir2');
	Route::post('/adisyon-hizmet-sil','StoreAdminController@adisyon_hizmet_sil');
	Route::get('/seansdetaylari','StoreAdminController@seansdetaylari');
	Route::post('/senetvadeguncelle','StoreAdminController@senetvadeguncelle');
	Route::post('/senetvadeodemeyitamamla','StoreAdminController@senetvadeodemeyitamamla');
	Route::post('/senetodemedogrulamakodugonder','StoreAdminController@senet_odeme_dogrulama_kodu_gonder');
	Route::get('/tahsilatdetaygetir','StoreAdminController@tahsilatdetaygetir');
	Route::get('/urunfiyathesapla','StoreAdminController@urunfiyathesapla');
	Route::get('/masrafgetir','StoreAdminController@masrafgetir');
	Route::get('/kasaraporugetir','StoreAdminController@kasa_raporu_getir');
	Route::get('/kampanyapaketfiyatgetir','StoreAdminController@kampanyapaketfiyatgetir');
	Route::post('/musterikaralisteayari','StoreAdminController@musterikaralisteayari');
	Route::get('/musteridetaybilgi','StoreAdminController@musteridetaybilgi');
	Route::post('/kampanyabeklenensms', 'StoreAdminController@kampanyabeklenensms');
	Route::get('/sms-raporlari','StoreAdminController@sms_raporlari');
	Route::post('/etkinlikbeklenensms', 'StoreAdminController@etkinlikbeklenensms');
	Route::post('/personelsifregonder','StoreAdminController@personelsifregonder');
	Route::post('/personelaktifpasifyap','StoreAdminController@personelaktifpasifyap');
	Route::post('/grupsmsekleduzenle','StoreAdminController@grupsmsekleduzenle');
	Route::post('/urunguncelle','StoreAdminController@urun_guncelle');
	Route::get('/smsraportest','StoreAdminController@smsraportest');
	Route::post('/isletmekapakresimyukle','StoreAdminController@isletmekapakresimyukle');
	Route::post('/isletmegorselekle','StoreAdminController@isletmegorselekle');
	Route::post('/isletmelogoyukle','StoreAdminController@isletmelogoyukle');
	Route::get('/smsbakiye','StoreAdminController@sms_bakiye_sorgulama');
	Route::get('/personeldetaygetir','StoreAdminController@personeldetaygetir');
	Route::get('/musterilerjson','StoreAdminController@musteri_liste_getir');
	Route::get('/adisyon-filtreli-getir-personel','StoreAdminController@satis_filtre');
	Route::get('/saatlerigetir','StoreAdminController@saatlerigetir');
	Route::post('/masraf-sil','StoreAdminController@masraf_sil');
	Route::get('/masraf-detay','StoreAdminController@masraf_detay');
	Route::get('/cakisan-randevu-kontrol','StoreAdminController@cakisan_randevu_kontrol');
	Route::get('/exceldataaktarornek','StoreAdminController@exceldataaktarornek');
	Route::get('/randevuornek','StoreAdminController@randevu_excel');
	Route::get('/musteritest','StoreAdminController@musteri_liste_deneme');
	Route::get('/testcases','StoreAdminController@testcases');
	Route::get('/randevular-test','StoreAdminController@randevular_test');
 
	
});


