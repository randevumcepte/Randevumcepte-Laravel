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
Route::get('/randevular/{salonid}/{returnres}','ApiController@randevular');
Route::get('/musteriler/{salonid}','ApiController@musteriler');
Route::get('/musteri-detay/{id}','ApiController@musteri_detayi');
Route::get('/musteri-randevulari/{id}','ApiController@musteri_randevulari');
Route::get('/getUserInfo/{userid}','ApiController@getUserInfo');
Route::post('/urunler/{salonid}','ApiController@urunler');
Route::post('/urunler','ApiController@urunler_liste');
Route::get('/getResourceInfo/{salonid}','ApiController@getResourceInfo');
Route::get('/randevuYukle/{salonid}','ApiController@randevuYukle');
Route::post('/siteden-yeni-kullanici-kaydi','ApiController@siteden_yeni_kayit_kullanici');
Route::post('/siteden-yeni-kayit','ApiController@siteden_yeni_kayit');
Route::get('/dashboard/{salonid}/{userid}','ApiController@ozetsayfasi');
Route::get('/isletmepuani/{salonid}','ApiController@isletmepuani');
Route::post('/ajandaget/{salonid}/{olusturan}','ApiController@ajandagetir');
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
Route::post('/randevular/{salonid}','ApiController@tum_randevular_get_filtre');
 
Route::post('/odeme-bildirimi','ApiController@odeme_bildirimi');
Route::get('/bildirimgetir/{salonid}/{okundu}/{personelid}', 'ApiController@bildirimgetir');
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
Route::post('/urunekleduzenle/{salonid}','ApiContrarsivyukleoller@urunekleduzenle');
 
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
Route::post('/sifregonder','ApiController@sifregonder');
Route::post('/yenimusteridanisankaydi','ApiController@yenimusteridanisankaydi');
Route::post('/seanslar/{salonid}','ApiController@seans_getir');
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
Route::get('/sehirler','ApiController@sehirler');
Route::get('/ongorusmenedeni/{salonid}','ApiController@ongorusmenedeni');
Route::post('/ongorusmeekleguncelle','ApiController@ongorusmeekleguncelle');
Route::post('/bilgiguncelle','ApiController@yetkilibilgiguncelle');
Route::post('/profilresimyukle','ApiController@profilresimyukle');
Route::post('/satislar','ApiController@satislar');

Route::post('alacaklar/{salonid}','ApiController@alacaklar');

Route::post('/randevugeldiisaretle','ApiController@randevugeldiisaretle');
Route::post('/randevuyagelmediisaretle','ApiController@randevuyagelmedi');
Route::post('/randevuiptalet','ApiController@randevuiptalet');
Route::post('/musteri-danisan-turunu-getir','ApiController@musteri_danisan_turunu_getir');
Route::post('/tum-alacaklar','ApiController@tum_alacaklar');
Route::post('/taksitekleguncelle','ApiController@taksitekleguncelle');
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
Route::post('/musterisil','ApiController@musteri_sil');
Route::post('/saglikbilgilerigir','ApiController@saglikbilgilerigir');
Route::post('/randevuayarguncelle','ApiController@randevuayarguncelle');
Route::post('/salonlar','ApiController@salonlar');
Route::post('/musteriindirim_kaydet','ApiController@musteriindirim_kaydet');
Route::get('/calisma_saati_guncelle_ekle/{salonid}','ApiController@calisma_saati_guncelle_ekle');
Route::get('/salonsaatleri/{salonid}','ApiController@salonsaatleri');
Route::get('/mola_saati_guncelle_ekle/{salonid}','ApiController@mola_saati_guncelle_ekle');
Route::get('/salonmolasaatleri/{salonid}','ApiController@salonmolasaatleri');

 
});
