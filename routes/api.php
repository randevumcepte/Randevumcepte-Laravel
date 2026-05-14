<?php
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
 
Route::group([
'prefix'=> 'v1'
], function () {
Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::get('loggedin','AuthController@loggedin');
Route::get('subeler/{salonid}','ApiController@subeler');
Route::post('/logout', 'AuthController@logout')->middleware('auth:isletmeyonetim-api');
Route::post('/randevular/{salonid}/{returnres}','ApiController@randevular');
Route::get('/randevular/{salonid}/{returnres}','ApiController@randevular');
Route::get('/musteriler/{salonid}','ApiController@musteriler');
Route::get('/musteriler','ApiController@musteriler2');
Route::post('/musteritahsilat','ApiController@musteritahsilat');
Route::get('/musteri-detay/{id}','ApiController@musteri_detayi');
Route::get('/musteri-randevulari/{id}','ApiController@musteri_randevulari');
Route::get('/getUserInfo/{userid}','ApiController@getUserInfo');
Route::post('/urunler/{salonid}','ApiController@urunler');
Route::post('/urunler','ApiController@urunler_liste');
Route::get('/getResourceInfo/{salonid}','ApiController@getResourceInfo');
Route::get('/randevuYukle/{salonid}','ApiController@randevuYukle');
Route::post('/siteden-yeni-kullanici-kaydi','ApiController@siteden_yeni_kayit_kullanici');
Route::post('/siteden-yeni-kayit','ApiController@siteden_yeni_kayit');
Route::post('/dashboard','ApiController@ozetsayfasi');
Route::get('/dashboard','ApiController@ozetsayfasi');
// Dashboard analytics — periyot bazli karsilastirma (mobile dashboard cards icin)
// Yeni endpoint: route cache temizlemesi gerekiyor (deploy.sh artik yapar)
// trigger3
Route::post('/dashboardKarsilastirma/{salonId}','ApiController@dashboardKarsilastirma');
Route::get('/dashboardKarsilastirma/{salonId}','ApiController@dashboardKarsilastirma');

// Saat bosluk firsatlari — dashboard onerisinden tek-tikla kampanya olustur
Route::post('/saatBosluguKampanyaOlustur/{salonId}','ApiController@saatBosluguKampanyaOlustur');
Route::post('/saatBosluguKampanyaIptal/{salonId}','ApiController@saatBosluguKampanyaIptal');

// Anket / Reputation Booster — ozet ve gonderim listesi (mobile uygulama icin)
// trigger4 (route cache temizleme tetikleyici)
Route::post('/anketOzet/{salonId}','ApiController@anketOzet');
Route::get('/anketOzet/{salonId}','ApiController@anketOzet');
Route::post('/anketGonderimleri/{salonId}','ApiController@anketGonderimleri');
Route::get('/anketGonderimleri/{salonId}','ApiController@anketGonderimleri');

// Anket Yonetimi (sablon CRUD + manuel gonderim + detay + ayarlar) mobil
Route::get('/anketSablonlari/{salonId}','ApiController@anketSablonListesi');
Route::get('/anketSablon/{salonId}/{sablonId}','ApiController@anketSablonDetay');
Route::post('/anketSablon/{salonId}','ApiController@anketSablonOlustur');
Route::post('/anketSablon/{salonId}/{sablonId}','ApiController@anketSablonGuncelle');
Route::delete('/anketSablon/{salonId}/{sablonId}','ApiController@anketSablonSil');
Route::post('/anketManuelGonder/{salonId}','ApiController@anketManuelGonderApi');
Route::get('/anketGonderimDetay/{salonId}/{gonderimId}','ApiController@anketGonderimDetayApi');
Route::get('/anketAyarlar/{salonId}','ApiController@anketAyarlar');
Route::post('/anketAyarlar/{salonId}','ApiController@anketAyarlar');

// Cark-i Felek Admin (mobil)
Route::get('/carkAdmin/sistem/{salonId}','ApiController@carkSistemGetir');
Route::post('/carkAdmin/dilim-kaydet/{salonId}','ApiController@carkDilimKaydet');
Route::post('/carkAdmin/aktif-toggle/{salonId}','ApiController@carkAktifToggle');
Route::post('/carkAdmin/bildirim-gonder/{salonId}','ApiController@carkBildirimGonder');
Route::get('/carkAdmin/kazananlar/{salonId}','ApiController@carkKazananlarApi');
Route::post('/carkAdmin/kupon-dogrula/{salonId}','ApiController@carkKuponDogrulaApi');
Route::post('/carkAdmin/kupon-kullan/{salonId}','ApiController@carkKuponKullanApi');
Route::get('/carkAdmin/hatirlatma/{salonId}','ApiController@carkHatirlatmaGetirApi');
Route::post('/carkAdmin/hatirlatma/{salonId}','ApiController@carkHatirlatmaKaydetApi');

// WhatsApp Mobil (ayri controller, defensive, izole)
Route::post('/whatsapp/baslat/{salonId}','WhatsappMobileController@baslat');
Route::get('/whatsapp/durum/{salonId}','WhatsappMobileController@durum');
Route::get('/whatsapp/qr/{salonId}','WhatsappMobileController@qr');
Route::post('/whatsapp/cikis/{salonId}','WhatsappMobileController@cikis');
Route::get('/whatsapp/ozet/{salonId}','WhatsappMobileController@ozet');
Route::get('/whatsapp/loglar/{salonId}','WhatsappMobileController@loglar');
Route::get('/whatsapp/aliciler/{salonId}','WhatsappMobileController@aliciler');
Route::get('/whatsapp/alici/{salonId}/{telefon}','WhatsappMobileController@aliciGecmis');
Route::get('/whatsapp/kanal-durum/{salonId}','WhatsappMobileController@kanalDurum');
Route::post('/whatsapp/kanal-toggle/{salonId}','WhatsappMobileController@kanalToggle');
Route::get('/whatsapp/paket-durum/{salonId}','WhatsappMobileController@paketDurum');
Route::post('/whatsapp/paket-talep/{salonId}','WhatsappMobileController@paketTalep');

Route::get('/isletmepuani/{salonid}','ApiController@isletmepuani');
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/ajandaget/{salonid}/{olusturan}','ApiController@ajandagetir');
});
//Route::post('/ajandaget/{salonid}/{olusturan}','ApiController@ajandagetir');
Route::post('/paketler/{salonid}','ApiController@paketler');
Route::post('/paketler','ApiController@paketler_liste');
Route::get('/paketdetay/{paketid}','ApiController@paketdetay');
Route::post('/paketsatisget/{salonid}','ApiController@paketsatisget');
Route::post('/urunsatisget/{salonid}','ApiController@urunsatisgetir');
Route::post('/ongorusmeget/{salonid}','ApiController@ongorusmegetir');
Route::post('/ongorusmegetgunluk/{salonid}','ApiController@ongorusmegetirgunluk');
Route::post('/salon_randevu_getir/{salonid}','ApiController@salon_tarafindan_randevular_get');
Route::post('/web_randevu_getir/{salonid}','ApiController@web_tarafindan_randevular_get');
Route::post('/uygulama_randevu_getir/{salonid}','ApiController@uygulama_uzerindan_randevular_get');
Route::post('/tum_randevulari_getir/{salonid}','ApiController@tum_randevular_get');
Route::post('/randevular','ApiController@tum_randevular_get_filtre');
 
Route::post('/odeme-bildirimi','ApiController@odeme_bildirimi');
Route::get('/bildirimgetir/{salonid}/{personelid}', 'ApiController@bildirimgetir');
Route::post('/notekleduzenle/{salonid}/{olusturan}','ApiController@notekleduzenle');
Route::post('/etkinlikyukle/{salonid}','ApiController@etkinlikyukle');
Route::post('/kampanyalar/{salonid}','ApiController@kampanyalar');
Route::get('/smstaslaklari/{salonid}','ApiController@smstaslaklari');
Route::post('/etkinlikekleduzenle/{salonid}','ApiController@etkinlikekleduzenle');
Route::post('/kampanyaekleduzenle/{salonid}','ApiController@kampanyaekleduzenle');
Route::post('/kampanyapasifet','ApiController@kampanyapasifet');
Route::post('/kampanyatekrarsmsgonder','ApiController@kampanyatekrarsmsgonder');
Route::post('/arsivyukle/{salonid}','ApiController@arsivyukle');
Route::get('/kullaniciBilgiGetir/{id}','ApiController@kullaniciBilgiGetir');
Route::post('/urunpasifet','ApiController@urunpasifet');
Route::post('/paketpasifet','ApiController@paketpasifet');
Route::post('/urunekleduzenle/{salonid}','ApiController@urunekleduzenle');
 
Route::post('/senetler/{salonid}','ApiController@senetler');
Route::post('/senetvadeguncelle','ApiController@senetvadeguncelle');
Route::post('/dogrulamakontrol','ApiController@senetode');
Route::post('/tahsilatraporu/{salonid}','ApiController@tahsilatraporu');
Route::post('/masrafraporu/{salonid}','ApiController@masrafraporu');
Route::post('/kasaraporu/{salonid}','ApiController@kasaraporu');
Route::get('/paketget/{salonid}','ApiController@paketget');
Route::post('/masrafekleduzenle/{salonid}','ApiController@masrafekleduzenle');
Route::get('/personeller/{salonid}','ApiController@personeller');
Route::get('/masrafkategorileri','ApiController@masrafkategorileri');
Route::get('/hizmetler/{salonid}','ApiController@hizmetler');
Route::get('/randevuhizmetler','ApiController@randevuhizmetler');
Route::post('/randevudetay','ApiController@randevudetay');
Route::post('/ajandasil','ApiController@ajandasil');
Route::post('/sifregonder','SifreSifirlamaController@sifreSifirla');
//Route::post('/sifregonder','ApiController@sifregonder');
Route::post('/sifreSifirla','SifreSifirlamaController@sifreSifirla');
Route::post('/yenimusteridanisankaydi','ApiController@yenimusteridanisankaydi');
Route::post('/seanslar','ApiController@seans_getir');
Route::get('/seanslar','ApiController@seans_getir');
Route::post('/musterilistegetir/{salonid}','ApiController@musteri_liste_getir');
  
Route::get('/odalar/{salonid}','ApiController@odalar');
Route::get('/cihazlar/{salonid}','ApiController@cihazlar');
Route::post('/randevuekleguncelle','ApiController@randevuekleguncelle');
Route::post('/isletmecalismasaatleri','ApiController@calismasaatleri');
Route::get('/formlar','ApiController@formlar');
Route::post('/arsivformekleguncelle','ApiController@arsivformekleguncelle');
Route::post('/haricibelgeekle','ApiController@haricibelgeekle');
Route::post('/arsiviptal','ApiController@arsiviptal');
Route::post('/cdrrapor','ApiController@cdrrapor');
Route::get('/cdrrapor','ApiController@cdrrapor');
Route::get('/cdrraporson','ApiController@cdrRaporLatest');
Route::get('/sehirler','ApiController@sehirler');
Route::get('/ongorusmenedeni/{salonid}','ApiController@ongorusmenedeni');
Route::post('/ongorusmeekleguncelle','ApiController@ongorusmeekleguncelle');
Route::post('/bilgiguncelle','ApiController@yetkilibilgiguncelle');
Route::post('/musteribilgiguncelle','ApiController@musteribilgiguncelle');
Route::post('/profilresimyukle','ApiController@profilresimyukle');
Route::post('/musteriprofilresimyukle','ApiController@musteriprofilresimyukle');
Route::post('/satislar','ApiController@satislar');
Route::post('alacaklar/{salonid}','ApiController@alacaklar');
Route::post('/randevugeldiisaretle','ApiController@randevugeldiisaretle');
Route::post('/randevuyagelmediisaretle','ApiController@randevuyagelmedi');

Route::get('/randevuiptalet','ApiController@randevuiptalet');

Route::post('/randevuiptalet','ApiController@randevuiptalet');
Route::post('/musteri-danisan-turunu-getir','ApiController@musteri_danisan_turunu_getir');
Route::post('/tum-alacaklar','ApiController@tum_alacaklar');
Route::post('/taksitekleguncelle','ApiController@taksitekleguncelle');
Route::post('/tahsilatekle','ApiController@tahsilatekle');
Route::post('/adisyonhizmetekle','ApiController@adisyonhizmetekle');
Route::post('/adisyonurunekle','ApiController@adisyonurunekle');
Route::post('/adisyonpaketekle','ApiController@adisyonpaketekle');
Route::post('/tahsilat-hizmet-sil','ApiController@tahsilat_hizmet_sil');
Route::post('/tahsilat-urun-sil','ApiController@tahsilat_urun_sil');
Route::post('/tahsilat-paket-sil','ApiController@tahsilat_paket_sil');
Route::post('/etkinliktekrarsmsgonder','ApiController@etkinliktekrarsmsgonder');
Route::post('/etkinlikpasifet','ApiController@etkinlikpasifet');
Route::post('/formgonder','ApiController@formgonder');
Route::post('/arsivonayla','ApiController@arsivonayla');
Route::post('/musteriekleguncelle/{salonid}','ApiController@musteriekleguncelle');
Route::post('check_phone', 'ApiController@checkPhone');

Route::post('/musterisil','ApiController@musteri_sil');
Route::post('/saglikbilgilerigir','ApiController@saglikbilgilerigir');
Route::post('/randevuayarguncelle','ApiController@randevuayarguncelle');
Route::post('/salonlar','ApiController@salonlar');
Route::post('/musteriindirim_kaydet','ApiController@musteriindirim_kaydet');
Route::get('/calisma_saati_guncelle_ekle/{salonid}','ApiController@calisma_saati_guncelle_ekle');
Route::get('/salonsaatleri/{salonid}','ApiController@salonsaatleri');
Route::get('/mola_saati_guncelle_ekle/{salonid}','ApiController@mola_saati_guncelle_ekle');
Route::get('/salonmolasaatleri/{salonid}','ApiController@salonmolasaatleri');
Route::post('/bildirimkimligiekleguncelle','ApiController@bildirimkimligiekleguncelle');

/* ───────── Yeni nesil bildirim API'si (NotificationApiController) ───────── */
Route::post('/bildirim/cihaz-kaydet',     'NotificationApiController@cihazKaydet');
Route::post('/bildirim/cihaz-sil',        'NotificationApiController@cihazSil');
Route::post('/bildirim/test',             'NotificationApiController@test');
Route::get ('/bildirim/liste',            'NotificationApiController@liste');
Route::post('/bildirim/okundu',           'NotificationApiController@okundu');
Route::get ('/bildirim/okunmamis-sayi',   'NotificationApiController@okunmamisSayi');
 Route::post('/randevuonayla','ApiController@randevuonayla');
 Route::get('/randevulistedeneme/{salonid}','ApiController@randevulistedeneme');
  Route::post('/ajanda_okunduisaretle','ApiController@ajanda_okunduisaretle');
  Route::post('/odagetir/{salonid}','ApiController@odagetir');
  Route::post('/cihazgetir/{salonid}','ApiController@cihazgetir');
  Route::post('/personelgetir/{salonid}','ApiController@personelgetir');
   Route::get('/personelgetir/{salonid}','ApiController@personelgetir');
 Route::post('/ongorusmesatisyapilmadi','ApiController@ongorusmesatisyapilmadi');
  Route::post('/odamusaitdegilisaretle','ApiController@odamusaitdegilisaretle');
  Route::post('/cihazmusaitdegilisaretle','ApiController@cihazmusaitdegilisaretle');
  Route::post('/odamusaitisaretle','ApiController@odamusaitisaretle');
  Route::post('/cihazmusaitisaretle','ApiController@cihazmusaitisaretle');
  Route::post('/ongorusmesatisyapildi','ApiController@ongorusmesatisyapildi');
  Route::get('/cihazekle/{salonid}','ApiController@cihazekle');
  Route::get('/odaekleduzenle/{salonid}','ApiController@odaekleduzenle');
  Route::post('/personelekleduzenle','ApiController@personelekleduzenle');
  Route::get('/personelcalismasaatleri/{personelid}','ApiController@personelcalismasaatleri');
  Route::get('/personelmolasaatleri/{personelid}','ApiController@personelmolasaatleri');
 Route::post('/hizmet_liste_getir/{salonid}','ApiController@hizmet_liste_getir');
 Route::post('/randevutahsilet','ApiController@randevutahsilet');
 Route::post('/seciliolmayanhizmetlerigetir','ApiController@seciliolmayanhizmetlerigetir');
  Route::get('/seciliolmayanhizmetlerigetir','ApiController@seciliolmayanhizmetlerigetir');
 Route::post('/sistemeyenihizmetekle','ApiController@sistemeyenihizmetekle');
 Route::post('/hizmetkategorileri','ApiController@hizmetkategorileri');
  Route::post('/paket_sil','ApiController@paket_sil');
 Route::post('/paket_ekle_guncelle/{salonid}','ApiController@paket_ekle_guncelle');
  Route::post('/paketgetir/{salonid}','ApiController@paketgetir');
 Route::post('/mobildegelenaramagoster','ApiController@mobildegelenaramagoster');
  Route::post('/bildirimguncelle','ApiController@bildirimguncelle');
  Route::get('/denemesantral','ApiController@denemesantral');
       Route::post('/personelaktifyap','ApiController@personelaktifyap');
    Route::post('/personelpasifyap','ApiController@personelpasifyap');
    Route::post('/personelsifregonder','ApiController@personelsifregonder');
    Route::post('/personelArsivle','ApiController@personelArsivle');
    Route::post('/personelSiralamaKaydir','ApiController@personelSiralamaKaydir');
    Route::post('/personelTakvimdeGorunsunToggle','ApiController@personelTakvimdeGorunsunToggle');
    Route::post('/personelPrimHesaplaAyYil','ApiController@personelPrimHesaplaAyYil');
    Route::post('/primOde','ApiController@primOdeApi');
    Route::post('/primOdemeListesi','ApiController@primOdemeListesiApi');
    Route::post('/primOdemeSil','ApiController@primOdemeSilApi');
    Route::post('/primHareketEkle','ApiController@primHareketEkleApi');
    Route::post('/primHareketSil','ApiController@primHareketSilApi');
        Route::post('/cihaz_sil','ApiController@cihaz_sil');
         Route::post('/oda_sil','ApiController@oda_sil');
    Route::post('/arayanmusteribilgi','ApiController@arayanmusteribilgi');
    Route::post('/senetekleguncelle','ApiController@senetekleguncelle');
    Route::post('/hizmetekleduzenle','ApiController@hizmetekleduzenle');
         Route::post('/randevularimusteri/{musterid}','ApiController@randevularimusteri');
                  Route::post('/ongorusmebilgi','ApiController@ongorusmebilgi');
    Route::post('/personelprimhesapla','ApiController@personelprimhesapla');
      Route::post('/musteriresimleri','ApiController@musteriresimleri');
        Route::post('/randevudegerlendir','ApiController@randevudegerlendir');
     Route::post('/yorumyap','ApiController@yorumyap');
    Route::post('/musteriozet','ApiController@musteriozet');
     Route::post('/bildirimgetirmusteri','ApiController@bildirimgetirmusteri');
     Route::get('/illerigetir','ApiController@illerigetir');
     Route::get('/ilcelerigetir','ApiController@ilcelerigetir');
     Route::get('/subdomainekle','ApiController@subdomainekle');
     Route::post('/randevuyagelecek','ApiController@randevuyagelecek');
     Route::post('/randevuhatirlatmaaramasiyapildi','ApiController@randevuhatirlatmaaramasiyapildi');
     Route::post('/uygunrandevubul','ApiController@uygunrandevubul');
     Route::post('/santralkarsilamametni','ApiController@santralkarsilamametni');
 Route::get('/santralkarsilamametni','ApiController@santralkarsilamametni');
    Route::post('/randevuyuenyakintariheguncelle','ApiController@randevuyuenyakintariheguncelle');
    Route::post('/hizmetbul','ApiController@hizmetbul');
     Route::get('/hizmetbul','ApiController@hizmetbul');
    Route::post('/santralRandevuEkle','ApiController@santralRandevuEkle');
    Route::get('/easistandata/{bugunYarin}/{salon_id}','ApiController@easistandata');
    Route::get('/easistandatadashboard/{bugunYarin}/{salon_id}','ApiController@easistandatadashboard');
    Route::post('/alacakKontrol','ApiController@alacakKontrol');
    Route::post('/asistanUlasti','ApiController@asistanUlasti');
    Route::get('/alacakdeneme','ApiController@alacakdeneme');
    Route::post('/alacakOdenecek','ApiController@alacakOdenecek');
    Route::post('kampanyaKatilinacak','ApiController@kampanyaKatilinacak');
    Route::get('/nlpIntentDeneme','ApiController@nlpIntentDeneme');
    Route::post('/cevapVer','ApiController@cevapVer');
    Route::get('/cevapVer','ApiController@cevapVer');
    Route::post('/enYakinRandevuIptalEt','ApiController@enYakinRandevuIptalEt');
    Route::post('/asistanRandevuIptalEt','ApiController@asistanRandevuIptalEt');
    Route::post('/gorev-iptal-et','ApiController@gorev_iptal_et');
    Route::post('/yolTarifiGonder','ApiController@yolTarifiGonder');
    Route::post('/drKlinikSatisEkle','ApiController@drKlinikSatisEkle');
    Route::post('/drKlinikSatisHizmetEkle','ApiController@drKlinikSatisHizmetEkle');
    Route::post('/drKlinikTahsilatEkle','ApiController@drKlinikTahsilatEkle');
    Route::post('/topluHizmetAktar','ApiController@topluHizmetAktar');
    Route::middleware('salonappy.cors')->group(function () {
        Route::match(['post','options'], '/aktarimMusteriKontrol','ApiController@aktarimMusteriKontrol');
        Route::match(['post','options'], '/salonAppyRandevuAktar','ApiController@salonAppyRandevuAktar');
        Route::get('/ayristirmaDeneme','ApiController@ayristirmaDeneme');
        Route::match(['post','options'], '/salonAppyPaketSatisEkle','ApiController@salonAppyPaketSatisEkle');
        Route::match(['post','options'], '/satissisTahsilat','ApiController@satissisTahsilat');
        Route::match(['post','options'], '/salonAppyAdisyonRandevuEkle','ApiController@salonAppyAdisyonRandevuEkle');
        Route::match(['post','options'], '/salonAppyTahsilatEkle','ApiController@salonAppyTahsilatEkle');
    });
    Route::get('/randevuIcinGerekliVeriler','ApiController@randevuIcinGerekliVeriler');
    Route::post('/randevuIcinGerekliVeriler','ApiController@randevuIcinGerekliVeriler');
    Route::post('/voipTokenKaydet','ApiController@voipTokenKaydet');
    Route::get('/voipTokenKaydet','ApiController@voipTokenKaydet');
    Route::get('/oneSignalTest','ApiController@oneSignalTest');
    Route::get('/firebaseBaslat','ApiController@firebaseBaslat');
    Route::get('/testToken','ApiController@testToken');
    Route::get('/hesapSilmeTalebiGonderMusteri','ApiController@hesapSilmeTalebiGonderMusteri');
    Route::post('/hesapSilmeTalebiGonderMusteri','ApiController@hesapSilmeTalebiGonderMusteri');
      Route::get('/hesapSilmeTalebiGonderPersonel','ApiController@hesapSilmeTalebiGonderPersonel');
    Route::post('/hesapSilmeTalebiGonderPersonel','ApiController@hesapSilmeTalebiGonderPersonel');
    Route::post('/isletmeBilgileri','ApiController@isletmeBilgileri');
    Route::get('/randevuTarihSaatAdimi','ApiController@randevuTarihSaatAdimi');
    Route::post('/randevuTarihSaatAdimi','ApiController@randevuTarihSaatAdimi');
    Route::post('/personelAdiminaGec','ApiController@personelAdiminaGec');
    Route::get('/personelAdiminaGec','ApiController@personelAdiminaGec');
    Route::post('/parseGunSaatText','ApiController@parseGunSaatText');
   Route::get('/parseGunSaatText','ApiController@parseGunSaatText');
       Route::get('/musteri_sayilari_getir/{salonid}','ApiController@musteri_sayilari_getir');
           Route::post('/hizmetRaporlari','ApiController@hizmetRaporlari');
    Route::post('/urunRaporlari','ApiController@urunRaporlari');
    Route::post('/paketRaporlari','ApiController@paketRaporlari');
    Route::post('/personelRaporlari','ApiController@personelRaporlari');
    Route::post('/hizmet-musteri-listes','ApiController@hizmetMusteriListesiGetir');
    Route::post('/urun-musteri-listesi','ApiController@urunMusteriListesiGetir');
    Route::post('/paket-musteri-listesi','ApiController@paketMusteriListesiGetir');
    Route::post('/randevuUygunlukKontrolEt','ApiController@randevuUygunlukKontrolEt');
      Route::get('/randevuUygunlukKontrolEt','ApiController@randevuUygunlukKontrolEt');
    Route::post('/surukleBirakRandevuGuncelle','ApiController@surukleBirakRandevuGuncelle');
    Route::post('/adisyonSil','ApiController@adisyonSil');
     Route::post('/devredenAylar', 'ApiController@devredenAylar');
     Route::post('/hizmetsil','ApiController@hizmetSil');
        Route::post('/versiyonAppKontrol','ApiController@versiyonAppKontrol');
    Route::post('/salonAppyUrunSatisEkle','ApiController@salonAppyUrunSatisEkle');
    Route::post('/randevuGeldiGelmediIsaretiKaldir','ApiController@randevuGeldiGelmediIsaretiKaldir');
    Route::post('/seansEkle','ApiController@seansEkle');
    Route::post('/seansGuncelle','ApiController@seansGuncelle');

    /* ───────── Çarkıfelek (müşteri uygulaması) ───────── */
    Route::get ('/cark/durum',          'CarkifelekApiController@durum');
    Route::post('/cark/durum',          'CarkifelekApiController@durum');
    Route::post('/cark/cevir',          'CarkifelekApiController@cevir');
    Route::get ('/cark/odullerim',      'CarkifelekApiController@odullerim');
    Route::post('/cark/odullerim',      'CarkifelekApiController@odullerim');
    Route::get ('/cark/puanodullerim',  'CarkifelekApiController@puanOdullerim');
    Route::post('/cark/puanodullerim',  'CarkifelekApiController@puanOdullerim');
    Route::post('/cark/puanodultalep',  'CarkifelekApiController@puanOdulTalep');

    /* ───────── SMS Yönetimi (uygulama içi) ───────── */
    Route::get ('/sms-yonetim/init/{salonid}',                'ApiController@smsYonetimInit');
    Route::post('/sms-yonetim/musteri-listele/{salonid}',     'ApiController@smsYonetimMusteriListele');
    Route::post('/sms-yonetim/toplu-gonder/{salonid}',        'ApiController@smsYonetimTopluGonder');
    Route::post('/sms-yonetim/filtreli-gonder/{salonid}',     'ApiController@smsYonetimFiltreliGonder');
    Route::post('/sms-yonetim/taslak-kaydet/{salonid}',       'ApiController@smsYonetimTaslakKaydet');
    Route::post('/sms-yonetim/taslak-sil',                    'ApiController@smsYonetimTaslakSil');
    Route::get ('/sms-yonetim/raporlar/{salonid}',            'ApiController@smsYonetimRaporlar');
    Route::post('/sms-yonetim/rapor-detay/{salonid}',         'ApiController@smsYonetimRaporDetay');
    Route::post('/sms-yonetim/ayar-kaydet/{salonid}',         'ApiController@smsYonetimAyarKaydet');
    Route::get ('/sms-yonetim/karaliste/{salonid}',           'ApiController@smsYonetimKaraListe');
    Route::post('/sms-yonetim/karaliste-ekle/{salonid}',      'ApiController@smsYonetimKaraListeEkle');
    Route::post('/sms-yonetim/karaliste-sil/{salonid}',       'ApiController@smsYonetimKaraListeSil');
    Route::get ('/sms-yonetim/bakiye/{salonid}',              'ApiController@smsYonetimBakiye');

    /* ───────── AI Sesli Asistan (sidecar erisir) ───────── */
    Route::middleware('ai.sidecar')->prefix('ai')->group(function () {
        Route::post('/salon-bilgi',       'Api\AiAsistanController@salonBilgi');
        Route::post('/musait-saatler',    'Api\AiAsistanController@musaitSaatler');
        Route::post('/randevu-olustur',   'Api\AiAsistanController@randevuOlustur');
        Route::post('/mevcut-randevular', 'Api\AiAsistanController@mevcutRandevular');
        Route::post('/randevu-iptal',     'Api\AiAsistanController@randevuIptal');
        Route::post('/randevu-guncelle',  'Api\AiAsistanController@randevuGuncelle');
    });

    /* ───────── Stok Yonetimi v2 ───────── */
    Route::prefix('stok')->group(function () {
        // Ozet & raporlar
        Route::get ('/ozet/{salonid}',              'StokController@ozet');
        Route::get ('/dusuk-stok/{salonid}',        'StokController@dusukStokListesi');
        Route::get ('/urun-satis-raporu/{urunid}',  'StokController@urunSatisRaporu');

        // Urun CRUD
        Route::get ('/urunler/{salonid}',           'StokController@urunListesi');
        Route::post('/urunler/{salonid}',           'StokController@urunListesi');
        Route::get ('/urun/{urunid}',               'StokController@urunDetay');
        Route::post('/urun-barkod/{salonid}',       'StokController@urunBarkodAra');
        Route::post('/urun-kaydet/{salonid}',       'StokController@urunKaydet');
        Route::post('/urun-sil',                    'StokController@urunSil');

        // Kategori
        Route::get ('/kategoriler/{salonid}',       'StokController@kategoriListesi');
        Route::post('/kategori-kaydet/{salonid}',   'StokController@kategoriKaydet');
        Route::post('/kategori-sil',                'StokController@kategoriSil');

        // Depo
        Route::get ('/depolar/{salonid}',           'StokController@depoListesi');
        Route::post('/depo-kaydet/{salonid}',       'StokController@depoKaydet');
        Route::post('/depo-sil',                    'StokController@depoSil');

        // Tedarikci
        Route::get ('/tedarikciler/{salonid}',      'StokController@tedarikciListesi');
        Route::post('/tedarikci-kaydet/{salonid}',  'StokController@tedarikciKaydet');
        Route::post('/tedarikci-sil',               'StokController@tedarikciSil');

        // Hareketler & islemler
        Route::get ('/hareketler/{salonid}',        'StokController@hareketListesi');
        Route::post('/hareketler/{salonid}',        'StokController@hareketListesi');
        Route::post('/manuel-hareket/{salonid}',    'StokController@manuelHareket');
        Route::post('/alis-girisi/{salonid}',       'StokController@alisGirisi');
        Route::post('/transfer/{salonid}',          'StokController@transfer');
        Route::post('/sayim-uygula/{salonid}',      'StokController@sayimUygula');
        Route::post('/hizli-satis/{salonid}',       'StokController@hizliSatis');

        // Sarf receteleri (Faz 6)
        Route::get ('/receteler/{salonid}',         'StokController@receteListesi');
        Route::post('/recete-kaydet/{salonid}',     'StokController@receteKaydet');
        Route::post('/recete-sil',                  'StokController@receteSil');
    });
});
  