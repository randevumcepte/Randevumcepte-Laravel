<?php
use App\Http\Controllers\StoreAdminController;
use Illuminate\Database\Eloquent\Model;
 
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TestExport;
use Illuminate\Support\Facades\Storage;
 
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
Route::get('/save-excel', function () {
    $path = 'exports/test.xlsx';
    Excel::store(new TestExport, $path, 'local'); // 'local' storage/app içine kaydeder
    return response()->download(storage_path("app/$path"));
});
	Route::get('/veriaktarimi','HomeController@veri_aktarimi');
	Route::get('/drklinik','HomeController@drklinik');
	Route::post('/ilcelerigetir','HomeController@ilcelerigetir');
	Route::get('/gizlilik-politikasi','HomeController@gizlilik');
	Route::get('/ornekarama','HomeController@ornekarama');
	Route::get('/odeme-basarili','HomeController@odeme_basarili');
	Route::get('/odemebasarisiz','HomeController@odemebasarisiz');
	Route::get('/merhaba','HomeController@index');
	Route::get('/kampanyakatilim/{id}/{userid}','HomeController@kampanyakatilimanketi');
	Route::get('/etkinlikkatilim/{id}/{userid}','HomeController@etkinlikkatilimanketi');
	Route::post('/kampanyakatilimanketicevapla','HomeController@kampanyakatilimanketicevapla');
	Route::post('/etkinlikkatilimanketicevapla','HomeController@etkinlikkatilimanketicevapla');
    Route::get('/kartbilgiler','KartBankaController@kartbilgiler');
    Route::get('/odeme','HomeController@odemeisleminibaslat');
    Route::get('/paketsatinalma','HomeController@ozelOdeme');
    Route::post('/odemebasarili/{kuponid}/{musteriid}', 'HomeController@odemebasarili');
    Route::post('/odemebasarisiz', 'HomeController@odemebasarisiz')->name('odemebasarisiz');
    /*Route::get('/basariliislem',function(){
    	return view('odemebasarili');
    });
    Route::get('/basarisizislem',function(){
    	return view('odemebasarisiz');
    });*/
     Route::post('/webhook','HomeController@webhook');

    // Deploy tetikleyici — sunucuyu git pull + migrate çalıştırır (kullanımdan sonra silin)
    Route::get('/deploy-trigger-rm2026', function() {
        $flag = storage_path('.deploy-flag');
        file_put_contents($flag, date('Y-m-d H:i:s'));
        return 'Deploy tetiklendi. Yaklaşık 1 dakika içinde sunucu güncellenecek.';
    });
    Route::get('/bulkmailgonder','MailController@test');
    Route::get('/smsgonder','SMSController@sendSMS');
    Route::get('/isletmeyonetim/seopaketleri','HomeController@seobasvuru');
	 Route::get('/musteriformdoldurma/{id}/{userid}','HomeController@arsivmusteriform');
	 	 Route::get('/musteriformdoldurma2/{id}/{userid}','HomeController@arsivmusteriform2');
	 Route::get('/onam-form/{arsiv_id}/{user_id}','HomeController@onamFormSayfasi');
	 Route::post('/onam-form-kaydet','HomeController@onamFormKaydet');
	 	 Route::get('/musteriformdoldurma3/{id}/{userid}','HomeController@arsivmusteriform3');

	
	 	 Route::get('/musteriformdoldurma4/{id}/{userid}','HomeController@arsivmusteriform4');
	 	 Route::get('/musteriformdoldurma5/{id}/{userid}','HomeController@arsivmusteriform5');
 		Route::get('/musteriformdoldurma6/{id}/{userid}','HomeController@arsivmusteriform6');
	 Route::get('/personelformdoldurma2/{id}/{userid}','HomeController@arsivpersonelform2');
	 Route::get('/personelformdoldurma/{id}/{userid}','HomeController@arsivpersonelform');
	 Route::post('/musterionamformugonderme','HomeController@musterionamformugonderme');
	  Route::post('/musterionamformugonderme2','HomeController@musterionamformugonderme2');
	  	  Route::post('/musterionamformugonderme3','HomeController@musterionamformugonderme3');
	 Route::post('/personelonamformugonderme','HomeController@personelonamformugonderme'); 
	 Route::post('/personelonamformugonderme2','HomeController@personelonamformugonderme2'); 
	 
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
	Route::get('/sitemap.xml', 'HomeController@sitemap');
	Route::get('/robots.txt', 'HomeController@robots');
	Route::get('/{isletme_adi}-{isletme_id}', 'HomeController@salonDetay_anasayfa')->name('salondetaylari');
	Route::get('/', 'HomeController@salonDetay');
	//Route::get('/', 'HomeController@salonDetay_anasayfa'); 
	Route::get('/avantajsatinal/{kampanyaid}','HomeController@avantajsatinal');
	Route::get('/{isletme_turu}/{il}/{ilce}/{isletme_id}/{isletme_adi}/{arama_terimi}/{arama_terim_id}', 'HomeController@salonDetay_altsayfa')->name('salondetaylari_altsayfa'); 
	Route::get('/isletmeyonetim/smspaketleri','HomeController@paketsatinalyukseltmeornek');
	Route::get('/isletmeyonetim/smspaketsatinal/{paketno}','HomeController@smspaketsatinal');
	Route::post('/satis-ortakligi-kayit','HomeController@satis_ortakligi_kayit'); 
	 

});

Route::get('/profilim', 'HomeController@profilim');
Route::post('/musteri_profil_guncelleme', 'HomeController@musteri_profil_guncelleme')->name('musteri_profil_guncelleme');
Route::get('/musteri_profil_resmi_kaldirma', 'HomeController@musteri_profil_resmi_kaldirma')->name('musteri_profil_resmi_kaldirma');
 	

Route::prefix('/satisortakligi')->group(function(){
	Route::get('/','SatisOrtakligiController@index')->name('satisortakligi.dashboard');
	Route::get('/kayitol','HomeController@satisortagikayitol');
	Route::get('/girisyap', 'SatisOrtakligi\LoginController@showLoginForm')->name('satisortakligi.login');
	Route::post('/girisyap','SatisOrtakligi\LoginController@login')->name('satisortakligi.login.submit')->middleware('throttle:5,1');
	Route::get('/sifremiunuttum','SatisOrtakligi\LoginController@sifremiunuttum');
	Route::post('/sifregonder','SatisOrtakligi\LoginController@sifregonder');
	Route::post('/sifredegistir','SatisOrtakligi\LoginController@sifredegistir');		
	Route::get('/yeni-musteri','SatisOrtakligiController@yeni_musteri_girisi');
	Route::get('/materyalleri-indir','HomeController@materyalleri_indir');

  Route::get('/sifre-ayarlari','SatisOrtakligiController@sifre_ayarlari');

  Route::post('/sifre-guncelle','SatisOrtakligiController@sifre_guncelle');
  Route::post('/yeni-musteri-ekle','SatisOrtakligiController@yeni_musteri_ekle')->name('yeni_musteri_ekle');
  Route::post('/yeni-musteri-ekle-excel','SatisOrtakligiController@musteri_excelden_aktar');
  Route::get('/pasif-musteriler','SatisOrtakligiController@pasif_musteriler');
  Route::get('/demosu-olan-musteriler','SatisOrtakligiController@demosu_olan_musteriler');
  Route::get('/satis-yapilamayan-musteriler','SatisOrtakligiController@satis_yapilamayan_musteriler');
  Route::post('/demohesabiac','SatisOrtakligiController@demohesabiac');
  Route::post('/demosurasiuzat','SatisOrtakligiController@demosuresiuzat');
  Route::get('/aktif-musteriler','SatisOrtakligiController@aktif_musteriler');
  Route::get('/odeme-talepleri','SatisOrtakligiController@odeme_talepleri');
  Route::get('/gecmis-odemeler','SatisOrtakligiController@gecmis_odemeler');
  Route::post('/gecmis-odemeler-filtre','SatisOrtakligiController@gecmis_odemeler_filtre');
	Route::get('/cikis-yap','SatisOrtakligiController@cikis_yap');
  Route::get('/isletmedetaylari','SatisOrtakligiController@isletmedetaylari');
  Route::post('/formu-kaydet','SatisOrtakligiController@musteri_formunu_kaydet');
	Route::get('/hesap-ayarlari','SatisOrtakligiController@hesap_ayarlari');
	Route::post('/bilgileri-guncelle','SatisOrtakligiController@bilgileri_guncelle');
	Route::post('/yeni-banka-hesabi-ekle','SatisOrtakligiController@yeni_banka_hesabi_ekle');
  Route::post('/odeme-talebi-gonder','SatisOrtakligiController@odeme_talebi_gonder');
  Route::get('/pasif-ortaklar','SatisOrtakligiController@pasif_ortaklar');
   Route::get('/pasif-ortak-musterileri/{pasifortakid}','SatisOrtakligiController@pasif_ortak_musterileri');
  Route::post('/pasif-ortak-ekle-guncelle','SatisOrtakligiController@pasif_ortak_ekle_guncelle');
  Route::post('/pasif-ortak-kaldir','SatisOrtakligiController@pasif_ortak_kaldir');
   Route::get('/one-cikan-ozellikler','SatisOrtakligiController@one_cikan_ozellikler');
   Route::get('/sunulanlar','SatisOrtakligiController@satis_ortaklarina_sunulanlar'); 
   Route::get('/basarili-satis','SatisOrtakligiController@basarili_satis');
   Route::get('/satis-sunumu','SatisOrtakligiController@satis_sunumu');
 	 Route::get('/satis-artirici-ozelllikler','SatisOrtakligiController@satis_artirici_ozellikler');
 	  Route::get('/reklam-kurallari','SatisOrtakligiController@reklam_kurallari');
 	 Route::post('/sozlesme-fesih-talebi-gonder','SatisOrtakligiController@fesih_hesap_silme_talebi');
 	 Route::get('/musteri-detaylari','SatisOrtakligiController@musteri_detaylari');
 	 Route::post('/musteri-guncelle','SatisOrtakligiController@musteri_guncelle');
 	 
     
    /*
     
    Route::get('/ads-musterileri','BayiController@ads_musterileri');
    Route::get('/ajans-musterileri','BayiController@ajans_musterileri');
    Route::get('/firma-detay-dokum/{id}','BayiController@firma_detay_dokum');
    Route::get('/sikayet-memnuniyet-bildir','BayiController@sikayet_memnuniyet_bildir');
     Route::post('/sikayet-memnuniyet-gonder','BayiController@sikayet_memnuniyet_gonder');
     Route::post('/bayi-banka-hesabi-kaldir','BayiController@bayi_banka_hesabi_kaldir');
      Route::get('/cikis-yap','BayiController@cikis_yap')->name('bayi.cikis_yap');*/


});

Route::prefix('sistemyonetim')->group(function() {
	Route::get('/','AdminController@index')->name('superadmin.dashboard');
	Route::get('/girisyap', 'AuthSuperAdmin\LoginController@showSuperAdminLoginForm')->name('superadmin.login');

	Route::post('/girisyap','AuthSuperAdmin\LoginController@login')->name('superadmin.login.submit')->middleware('throttle:5,1');;
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
	Route::post('/girisyap','AuthStoreAdmin\LoginController@login')->name('isletmeyonetim.login.submit')->middleware('throttle:5,1');
	Route::post('/satisortagiornekhesapgirisi','AuthStoreAdmin\LoginController@satisortagiornekhesapgirisi');
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
	Route::get('/hizmet-yonetimi', function(){ return redirect('/isletmeyonetim/ayarlar?p=hizmetler'); });
	Route::post('/hizmet-yonetimi/guncelle','StoreAdminController@hizmetYonetimiGuncelle');
	Route::post('/hizmet-yonetimi/kategori-ekle','StoreAdminController@hizmetKategoriEkle');
	Route::post('/hizmet-yonetimi/kategori-sil','StoreAdminController@hizmetKategoriSil');
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
	Route::get('/musterilistegetir/{durum}','StoreAdminController@musteri_liste_getir');
	Route::get('/adisyonlistegetir/','StoreAdminController@adisyonlistegetir');
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
	Route::get('/e_asistan','StoreAdminController@e_asistan');
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
   Route::middleware('auth')->group(function() {
    Route::get('/gunluk-urun-satislari', [StoreAdminController::class, 'gunlukUrunSatislari']);
    Route::get('/gunluk-paket-satislari', [StoreAdminController::class, 'gunlukPaketSatislari']);
    Route::get('/santral-raporlari', [StoreAdminController::class, 'santralRaporlari']);
    Route::get('/on-gorusmeler', [StoreAdminController::class, 'onGorusmeler']);
    Route::get('/easistan-data', [StoreAdminController::class, 'easistanData']);
});
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
	Route::post('/randevugeldiisaretle','StoreAdminController@randevugeldiisaretle');
	Route::post('/randevutahsilet','StoreAdminController@randevutahsilet');
	Route::get('/adisyon/{id}','StoreAdminController@adisyondetay');
	Route::post('/seansgirdiguncelle','StoreAdminController@seansgirdiguncelle');
	Route::post('/senetekleguncelle','StoreAdminController@senetekleguncelle');
	Route::post('/taksitekleguncelle','StoreAdminController@taksitekleguncelle');
	Route::get('/senetfiltre','StoreAdminController@senetfiltre');
	Route::get('/senetvadegetir','StoreAdminController@senetvadegetir');
	Route::get('/taksitvadegetir','StoreAdminController@taksitvadegetir');

	Route::get('/senetvadegetir-tahsilat','StoreAdminController@senetvadegetir_tahsilat');
	Route::get('/taksitvadegetir-tahsilat','StoreAdminController@taksitvadegetir_tahsilat');

	Route::get('/kampanya_yonetimi','StoreAdminController@kampanya_yonetimi_liste');
	Route::get('/kampanyadetay','StoreAdminController@kampanyadetay');
	Route::post('/kampanyasil','StoreAdminController@kampanya_sil');
	Route::post('/etkinliksil','StoreAdminController@etkinlik_sil');
	Route::post('/kampanyaekleduzenle','StoreAdminController@kampanyaekleduzenle');
	Route::get('/kampanya-sablon-filtre','StoreAdminController@kampanyaSablonFiltre');
	Route::get('/kampanyaIceriginiGoruntule','StoreAdminController@kampanyaIceriginiGoruntule');
	Route::post('/kampanyakatilimcisil','StoreAdminController@kampanyakatilimcisil');
	Route::post('/kampanyakatilimciekle','StoreAdminController@kampanyakatilimciekle');
	Route::post('/sablonSil','StoreAdminController@sablonSil');
	Route::get('/hizmet-secimi-2','StoreAdminController@hizmet_secimi_2');
	Route::get('/urun-secimi','StoreAdminController@urun_secimi');
	Route::get('/paket-secimi','StoreAdminController@paket_secimi');
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
	Route::post('/santral-ayar-kaydet','StoreAdminController@santral_ayar_kaydet');
	Route::post('/ongorusmesatisyapilmadi','StoreAdminController@ongorusmesatisyapilmadi');
	 
	Route::post('/grupsil','StoreAdminController@grup_sil');
 
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
	Route::post('/taksitvadeguncelle','StoreAdminController@taksitvadeguncelle');
	Route::post('/senetvadeodemeyitamamla','StoreAdminController@senetvadeodemeyitamamla');
	Route::post('/taksitvadeodemeyitamamla','StoreAdminController@taksitvadeodemeyitamamla');
	Route::post('/senetodemedogrulamakodugonder','StoreAdminController@senet_odeme_dogrulama_kodu_gonder');
	Route::post('/taksitodemedogrulamakodugonder','StoreAdminController@taksit_odeme_dogrulama_kodu_gonder');
	Route::get('/tahsilatdetaygetir','StoreAdminController@tahsilatdetaygetir');
	Route::get('/urunfiyathesapla','StoreAdminController@urunfiyathesapla');
	Route::get('/masrafgetir','StoreAdminController@masrafgetir');
	Route::get('/kasaraporugetir','StoreAdminController@kasa_raporu_getir');
	Route::get('/kasaraporufiltre','StoreAdminController@kasa_raporu_filtre');
	Route::get('/kampanyapaketfiyatgetir','StoreAdminController@kampanyapaketfiyatgetir');
	Route::post('/musterikaralisteayari','StoreAdminController@musterikaralisteayari');
	Route::get('/musteridetaybilgi','StoreAdminController@musteridetaybilgi');
	Route::post('/kampanyabeklenensms', 'StoreAdminController@kampanyabeklenensms');
	Route::get('/sms-raporlari','StoreAdminController@sms_raporlari');
	Route::post('/sms-rapor-detay','StoreAdminController@sms_rapor_detay');
	Route::post('/sms-raporlari-sayfali','StoreAdminController@sms_raporlari_sayfali');
	Route::post('/sms-karaliste-sayfali','StoreAdminController@sms_karaliste_sayfali');
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
	Route::get('/musterilerjson/{durum}','StoreAdminController@musteri_liste_getir');
	Route::get('/adisyon-filtreli-getir-personel','StoreAdminController@satis_filtre');
	Route::get('/saatlerigetir','StoreAdminController@saatlerigetir');
	Route::post('/masraf-sil','StoreAdminController@masrafSil');
	Route::get('/masraf-detay','StoreAdminController@masraf_detay');
	Route::get('/cakisan-randevu-kontrol','StoreAdminController@cakisan_randevu_kontrol');
	Route::get('/exceldataaktarornek','StoreAdminController@exceldataaktarornek');
	Route::get('/randevuornek','StoreAdminController@randevu_excel');
	Route::get('/musteritest','StoreAdminController@musteri_liste_deneme');
	Route::get('/testcases','StoreAdminController@testcases');
	Route::get('/randevular-test','StoreAdminController@randevular_test');
	Route::post('/musterisil','StoreAdminController@musteri_sil');
	Route::post('/adisyon-sil','StoreAdminController@adisyon_sil');
	Route::get('/personelcihazhizmetlerinigetir','StoreAdminController@personelcihazhizmetlerinigetir');
	Route::get('/personel-cihaz-hizmetleri-json','StoreAdminController@personelCihazHizmetleriJson');
	Route::get('/randevu-modal-hizmet-verisi','StoreAdminController@randevuModalHizmetVerisi');
	Route::get('/randevu-duzenle-json','StoreAdminController@randevuDuzenleJson');
 	Route::post('/pakettahsilatagit','StoreAdminController@pakettahsilatagit')->name('pakettahsilatagit');
 	
 	Route::post('/uruntahsilatagit','StoreAdminController@uruntahsilatagit')->name('uruntahsilatagit');
 	Route::get('/tahsilat/{musteriid}/{adisyonid}','StoreAdminController@tahsilatekrani');
 	Route::post('/musteriindirimkaydet','StoreAdminController@musteriindirim_kaydet');
 	Route::post('/hizmettahsilattutaridegistir','StoreAdminController@hizmettahsilattutaridegistir');
 	Route::post('/uruntahsilattutaridegistir','StoreAdminController@uruntahsilattutaridegistir');
 	Route::post('/urunadetdegistir','StoreAdminController@urunadetdegistir');

 	Route::post('/paketseansdegistir','StoreAdminController@paketseansdegistir');

 	Route::post('/pakettahsilattutaridegistir','StoreAdminController@pakettahsilattutaridegistir');
 	Route::post('/hizmet-hediye-isle','StoreAdminController@hizmet_hediye_isle');
 	Route::post('/urun-hediye-isle','StoreAdminController@urun_hediye_isle');
 	Route::post('/paket-hediye-isle','StoreAdminController@paket_hediye_isle');
 	Route::post('/hizmet-hediye-kaldir','StoreAdminController@hizmet_hediye_kaldir');
 	Route::post('/urun-hediye-kaldir','StoreAdminController@urun_hediye_kaldir');
 	Route::post('/paket-hediye-kaldir','StoreAdminController@paket_hediye_kaldir');
 	Route::get('/ajanda','StoreAdminController@ajanda')->name('isletmeadmin.ajanda');

	Route::post('/ajandayayeninotekle','StoreAdminController@ajandaya_yeni_not_ekle');
	Route::get('/ajandaguncelle','StoreAdminController@ajanda_guncelle');
	Route::post('/ajandasil','StoreAdminController@ajanda_sil');
	Route::post('/ajandaokunduisaretle','StoreAdminController@ajanda_okunduisaretle');
	Route::get('/ajandayukle','StoreAdminController@takvim_degistir_ajanda');
	Route::get('/ajandadetay','StoreAdminController@ajandadetay');
	Route::post('/eventrenk','StoreAdminController@eventrenk');
	Route::get('/ajandadetaygetir','StoreAdminController@ajandadetaygetir');
	Route::get('/taksitsenetkontrol','StoreAdminController@taksitsenetkontrol');
	Route::get('/santral','StoreAdminController@santral');
	Route::get('/santral-token','StoreAdminController@santral_token_al');
	Route::get('/arsivyonetimi','StoreAdminController@arsivyonetimi')->name('isletmeadmin.arsivyonetimi');
	Route::get('/formolusturma','StoreAdminController@formolusturma')->name('isletmeadmin.formolusturma');
	Route::get('/form-sablonlari','StoreAdminController@formSablonlari')->name('isletmeadmin.formSablonlari');
	Route::get('/form-sablonlari-getir','StoreAdminController@formSablonlariGetir');
	Route::post('/form-sablonlari-kaydet','StoreAdminController@formSablonlariKaydet');
	Route::post('/form-sablonlari-guncelle','StoreAdminController@formSablonlariGuncelle');
	Route::post('/form-sablonlari-sil','StoreAdminController@formSablonlariSil');
	Route::get('/formmusteribilgigetir','StoreAdminController@formmusteribilgigetir');
	Route::get('/formpersonelbilgigetir','StoreAdminController@formpersonelbilgigetir');
	Route::get('/onamformmikropdf', 'StoreAdminController@onamformindir')->name('download');
	Route::get('/onamformkimyasalpdf', 'StoreAdminController@onamformindirkimyasal')->name('download');
	Route::get('/onamformdovmepdf', 'StoreAdminController@onamformindirdovme')->name('download');
	Route::get('/onamformciltpdf', 'StoreAdminController@onamformindircilt')->name('download');
	Route::get('/onamformlazerpdf', 'StoreAdminController@onamformindirlazer')->name('download');
	Route::get('/onamformdermopdf', 'StoreAdminController@onamformindirdermo')->name('download');
	Route::get('/onamformbolgeselpdf', 'StoreAdminController@onamformindirbolgesel')->name('download');
	Route::post('/arsivformekleme','StoreAdminController@arsivformekleme');
	Route::get('/musteriformugonder','StoreAdminController@musteriformugonder')->name('isletmeadmin.musteriformugonder');
	Route::get('/formindir','StoreAdminController@formindir');
	Route::get('/formyazdir','StoreAdminController@formyazdir');
	Route::get('/formgoster','StoreAdminController@formgoster');
	Route::post('/arsivonaylaform','StoreAdminController@arsivonaylaform');
	Route::post('/arsiviptalform','StoreAdminController@arsiviptalform');
	Route::post('/haricibelgeekleme','StoreAdminController@haricibelgeekleme');
	Route::post('/formutekrargonder','StoreAdminController@formutekrargonder');
	Route::get('/cdrraporugetir','StoreAdminController@cdr_rapor_filtre');
	Route::get('/seskaydiindir','StoreAdminController@ses_kaydi_indir');
	Route::get('/santralcalismasaatleri','StoreAdminController@santral_calisma_saati_ayari');
	Route::get('/dahilibilgial','StoreAdminController@dahilibilgial');
	Route::post('/dahilibaglandi','StoreAdminController@dahilibaglandi');
	Route::get('/yenitahsilat','StoreAdminController@yenitahsilat');
	Route::get('/tahsilatbilgigetir','StoreAdminController@tahsilatbilgigetir');
	Route::post('/kasayaparaekle','StoreAdminController@kasaya_para_ekle');
	Route::post('/kasadanparaal','StoreAdminController@kasadanparaal');
	Route::get('/urunfiyatdegistir','StoreAdminController@urunfiyatdegistir');
	Route::get('/urunfiyatindirimdegistir','StoreAdminController@urunfiyatindirimdegistir');
	Route::post('/paracekmeonaykodugonder','StoreAdminController@paracekmeonaykodugonder');
	Route::post('/musteriprofilresimyukle','StoreAdminController@musteriprofilresimyukle');
	Route::post('/islemsonrasiresimyukleme','StoreAdminController@islemsonrasiresimyukleme');
	Route::post('/islemsonrasinotekleme','StoreAdminController@islemsonrasinotekleme');
	Route::get('/islemdetayigetir', 'StoreAdminController@islemdetayigetir');
	Route::get('/uyelik','StoreAdminController@uyelik');
	Route::post('/odeme-bildirimi','StoreAdminController@odeme_bildirimi');
	Route::post('/uyelikiletisimvefaturabilgiguncelle','StoreAdminController@uyelikiletisimvefaturabilgiguncelle');
	Route::post('/e_asistan_ayar_kaydet','StoreAdminController@e_asistan_ayar_kaydet');
	Route::post('/gorev-iptal-et','StoreAdminController@gorev_iptal_et');
	Route::post('/kampanyaSMSGonder','StoreAdminController@kampanyabeklenensms');
	Route::post('/kampanyaAra','StoreAdminController@kampanyaAra');
	Route::get('/easistandata/{bugunyarin}','StoreAdminController@easistandata');
	Route::get('/drKlinikMusteriAktarma','StoreAdminController@drKlinikMusteriAktarma');
	Route::get('/musteri-arama-bolumu-verileri','StoreAdminController@musteri_arama_bolumu_verileri');
	Route::get('/personel-secimi','StoreAdminController@personel_secimi');
	Route::get('/cihaz-secimi','StoreAdminController@cihaz_secimi');
	Route::get('/oda-secimi','StoreAdminController@oda_secimi');
	Route::get('/hizmet-secimi','StoreAdminController@hizmet_secimi');
	Route::get('/drKlinikPersonelAktarma','StoreAdminController@drKlinikPersonelAktarma');
	Route::get('/drKlinikRandevuAktarma','StoreAdminController@drKlinikRandevuAktarma');
	Route::get('/drKlinikSatisAktarma','StoreAdminController@drKlinikSatisAktarma');
	Route::get('/odadetayigetir','StoreAdminController@odadetayigetir');
	Route::get('/salonAppyHizmetDetayAktarma','StoreAdminController@salonAppyHizmetDetayAktarma');
	Route::get('/salonAppyRandevuAktarma','StoreAdminController@salonAppyRandevuAktarma');
	Route::get('/salonRandevuRandevuAktarma','StoreAdminController@salonRandevuRandevuAktarma');

	Route::get('/salonAppyRandevuSatisAktarma','StoreAdminController@salonAppyRandevuSatisAktarma');

	Route::get('/salonAppyTahsilatAktarma','StoreAdminController@salonAppyTahsilatAktarma');

	Route::get('/adisyonOdemeDetaylari','StoreAdminController@adisyonOdemeDetaylari');
	Route::get('/personelSiralamaArtir','StoreAdminController@personelSiralamaArtir');
	Route::get('/personelSiralamaAzalt','StoreAdminController@personelSiralamaAzalt');
	Route::get('/odaSiralamaArtir','StoreAdminController@odaSiralamaArtir');
	Route::get('/odaSiralamaAzalt','StoreAdminController@odaSiralamaAzalt');
	Route::get('/cihazSiralamaArtir','StoreAdminController@cihazSiralamaArtir');
	Route::get('/cihazSiralamaAzalt','StoreAdminController@cihazSiralamaAzalt');
	Route::get('/paketTahsilatlari','StoreAdminController@paketTahsilatlari');
	Route::get('/arama_listesi_getir','StoreAdminController@arama_listesi_getir');
	Route::post('/arama_listesi_ekle','StoreAdminController@arama_listesi_ekle');
	Route::post('/arama_liste_detay_getir','StoreAdminController@arama_liste_detay_getir');
	Route::post('/santral_not_ekle','StoreAdminController@santral_not_ekle');
	Route::post('/musteriportfoydropliste','StoreAdminController@musteriportfoydropliste');
	Route::post('/arama-listesi-arandi-isaretle','StoreAdminController@arama_listesi_arandi_isaretle');
	Route::post('/aramaListesineSesKaydiEkle','StoreAdminController@aramaListesineSesKaydiEkle');
	Route::get('/raporlar','StoreAdminController@raporlar');
	Route::get('/hizmetRaporFiltre','StoreAdminController@hizmetRaporFiltre');
	Route::get('/urunRaporFiltre','StoreAdminController@urunRaporFiltre');
	Route::get('/paketRaporFiltre','StoreAdminController@paketRaporFiltre');
	Route::get('/personelRaporFiltre','StoreAdminController@personelRaporFiltre');
	Route::get('/personel-yonetimi','StoreAdminController@personelYonetimi');
	 	 	Route::get('/carkifelek','StoreAdminController@carkifelek')->name('isletmeadmin.carkifelek');
	 	 	Route::post('/carkdilimekle', [StoreAdminController::class, 'carkdilimekle'])->name('isletmeadmin.carkdilimekle');
Route::get('/carkverilerigetir', [StoreAdminController::class, 'carkverilerigetir'])->name('isletmeadmin.carkverilerigetir');

Route::get('/personel_listesi_getir','StoreAdminController@personel_listesi_getir');
	Route::get('/adisyon_filtreli_getir_personel','StoreAdminController@adisyon_filtreli_getir_personel');
	 	 	Route::get('/devreden-aylar','StoreAdminController@devredenAylar')->name('isletmeadmin.kasadefteri');

	Route::post('/paketSecimKaydet','StoreAdminController@paketSecimKaydet');

Route::get('/bosFormIndir','StoreAdminController@bosFormIndir');
Route::get('/bosFormIndirDinamik','StoreAdminController@bosFormIndirDinamik');
Route::get('/satisDetaylariveDuzenleme','StoreAdminController@satisDetaylariveDuzenleme');
Route::get('/paketVarmiKontrolu','StoreAdminController@paketVarmiKontrolu');
Route::post('/seansEkle','StoreAdminController@seansEkle');
Route::post('/seansGuncelle','StoreAdminController@seansGuncelle');
	Route::get('/seansGetir','StoreAdminController@seans_getir');
	Route::post('/randevuGeldiGelmediIsaretiKaldir','StoreAdminController@randevuGeldiGelmediIsaretiKaldir');
});
Route::get('/run-schedule', function () {
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    return 'Schedule çalıştırıldı!';
});
Route::get('/check-memory', function () {
    return 'Memory Limit: ' . ini_get('memory_limit');
});

