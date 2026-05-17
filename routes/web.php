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

// GECICI: personel_id=0 olan randevu_hizmetler kayitlarini hizmete uygun ilk personele atayarak duzelt
Route::get('/dev-randevu-personel-duzelt', function() {
    $salon = \App\Salonlar::where('domain', $_SERVER['HTTP_HOST'])->first();
    if (!$salon) return 'Salon bulunamadi.';

    $kayitlar = \App\RandevuHizmetler::where('personel_id', 0)
        ->whereHas('randevu', function($q) use ($salon){ $q->where('salon_id', $salon->id); })
        ->get();

    if ($kayitlar->isEmpty()) {
        return "Bu salonda personel_id=0 olan randevu kaydi yok. Sorun cozuldu.";
    }

    $sonuclar = [];
    foreach ($kayitlar as $rh) {
        $personelHizmetIds = \App\PersonelHizmetler::where('hizmet_id', $rh->hizmet_id)->pluck('personel_id')->toArray();
        $oto = \App\Personeller::where('salon_id', $salon->id)
            ->where('aktif', 1)
            ->whereIn('id', $personelHizmetIds)
            ->first();
        if (!$oto) {
            $oto = \App\Personeller::where('salon_id', $salon->id)->where('aktif', 1)->first();
        }
        if ($oto) {
            $rh->personel_id = $oto->id;
            $rh->save();
            $sonuclar[] = "RandevuHizmet #{$rh->id} (randevu #{$rh->randevu_id}) &rarr; Personel: ".e($oto->personel_adi);
        } else {
            $sonuclar[] = "RandevuHizmet #{$rh->id}: salonda hic aktif personel yok, atlanmadi";
        }
    }

    return "<div style='font-family:sans-serif;max-width:700px;margin:60px auto;padding:30px;background:#f0fff4;border:2px solid #34d399;border-radius:14px;'>"
         . "<h2 style='color:#15803d;margin:0 0 12px;'>".count($sonuclar)." Randevu Duzeltildi</h2>"
         . "<ul style='line-height:1.8;font-size:14px;'><li>".implode('</li><li>', $sonuclar)."</li></ul>"
         . "<p style='margin-top:14px;'><a href='/' style='background:#5C008E;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>Anasayfa</a></p>"
         . "</div>";
});

// GECICI: SMS gonderim diagnostigi - salonun konfigi + saglayici yaniti gosterir
// Kullanim: /dev-sms-test?tel=05XXXXXXXXX
Route::get('/dev-sms-test', function() {
    $tel = request('tel', '');
    $salon = \App\Salonlar::where('domain', $_SERVER['HTTP_HOST'])->first();
    if (!$salon) return 'Bu domain icin salon bulunamadi.';

    $maskla = function($s, $head=4){
        if (!$s) return '<em>(bos)</em>';
        $len = mb_strlen($s);
        if ($len <= $head) return str_repeat('*', $len);
        return mb_substr($s, 0, $head) . str_repeat('*', max(0, $len - $head));
    };

    $cfg = '<div style="background:#f3f4f6;padding:14px;border-radius:8px;margin:10px 0;font-family:monospace;font-size:13px;line-height:1.7;">'
         . '<b>Salon ID:</b> '.$salon->id.'<br>'
         . '<b>salon_adi:</b> '.htmlspecialchars($salon->salon_adi).'<br>'
         . '<b>yeni_sms (flag):</b> '.var_export($salon->yeni_sms, true).' &rarr; <b>'.($salon->yeni_sms ? 'VoiceTelekom' : 'Efetech').'</b> kullaniliyor<br>'
         . '<b>sms_baslik:</b> '.htmlspecialchars($salon->sms_baslik ?: '').'<br>'
         . '<b>sms_user_name (VT):</b> '.$maskla($salon->sms_user_name).'<br>'
         . '<b>sms_secret (VT):</b> '.$maskla($salon->sms_secret, 2).'<br>'
         . '<b>sms_apikey (Efetech):</b> '.$maskla($salon->sms_apikey, 4).'<br>'
         . '</div>';

    if (!$tel) {
        return "<div style='font-family:sans-serif;max-width:700px;margin:60px auto;padding:30px;background:#fff;border:2px solid #5C008E;border-radius:14px;'>"
             . "<h2 style='color:#5C008E;margin-top:0;'>SMS Diagnostic</h2>"
             . "<p>Salon yapilandirmasi:</p>".$cfg
             . "<p>Test SMS gondermek icin URL'e <code>?tel=05XXXXXXXXX</code> ekleyin.</p>"
             . "</div>";
    }

    if (!preg_match('/^05[0-9]{9}$/', $tel)) {
        return "Gecersiz telefon: '$tel'. Format: 05XXXXXXXXX";
    }

    $mesaj = $salon->salon_adi . ' SMS test - ' . date('H:i:s') . ' - kod:' . rand(1000,9999);
    $log = '';
    $sonuc = '';

    try {
        if ($salon->yeni_sms) {
            require_once app_path('VoiceTelekom/Sms/SmsApi.php');
            require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
            require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
            $smsApi = new \SmsApi("smsvt.voicetelekom.com", $salon->sms_user_name, $salon->sms_secret);
            $req = new \SendMultiSms();
            $req->customID = "test_" . date('Ymd_His') . "_" . substr(md5(microtime()), 0, 8);
            $req->content = $mesaj;
            $req->title = 'SMS test';
            $req->numbers = [$tel];
            $req->encoding = 0;
            $req->sender = $salon->sms_baslik;
            $req->skipAhsQuery = true;
            $resp = $smsApi->sendMultiSms($req);
            $log = '<pre style="background:#1f2937;color:#10b981;padding:14px;border-radius:8px;overflow:auto;font-size:12px;">' . htmlspecialchars(print_r($resp, true)) . '</pre>';
            if ($resp->err == null) {
                $sonuc = '<div style="background:#dcfce7;color:#15803d;padding:14px;border-radius:8px;font-weight:bold;">VoiceTelekom: API kabul etti. Mesaj kuyrukta.</div>';
            } else {
                $sonuc = '<div style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:8px;font-weight:bold;">VoiceTelekom hatasi: ' . htmlspecialchars(($resp->err->code ?? '?') . ' - ' . ($resp->err->message ?? '')) . '</div>';
            }
        } else {
            $postUrl = "https://api.efetech.net.tr/v2/sms/multi";
            $headers = ['Authorization: Key '.$salon->sms_apikey, 'Content-Type: application/json', 'Accept: application/json'];
            $postData = json_encode([
                "originator" => $salon->sms_baslik,
                "messages"   => [['to' => $tel, 'message' => $mesaj]],
                "encoding"   => "auto",
            ]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $postUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            $respHeader = $resp !== false ? substr($resp, 0, $headerSize) : '';
            $respBody = $resp !== false ? substr($resp, $headerSize) : '';

            $log = '<div style="font-size:13px;margin:8px 0;"><b>HTTP Code:</b> '.$httpCode.($curlErr ? ' &nbsp; <b style="color:#b91c1c;">cURL Error:</b> '.htmlspecialchars($curlErr) : '').'</div>'
                 . '<div style="font-size:13px;margin:4px 0;"><b>Request body:</b></div>'
                 . '<pre style="background:#1f2937;color:#fde68a;padding:12px;border-radius:8px;overflow:auto;font-size:12px;">' . htmlspecialchars($postData) . '</pre>'
                 . '<div style="font-size:13px;margin:4px 0;"><b>Response headers:</b></div>'
                 . '<pre style="background:#1f2937;color:#93c5fd;padding:12px;border-radius:8px;overflow:auto;font-size:12px;">' . htmlspecialchars($respHeader) . '</pre>'
                 . '<div style="font-size:13px;margin:4px 0;"><b>Response body:</b></div>'
                 . '<pre style="background:#1f2937;color:#10b981;padding:12px;border-radius:8px;overflow:auto;font-size:12px;">' . htmlspecialchars($respBody !== '' ? $respBody : '(bos)') . '</pre>';

            $hataKodlari = ['99'=>'UNKNOWN','97'=>'USE_POST_METHOD','89'=>'WRONG_XML_FORMAT','87'=>'WRONG_USER_OR_PASSWORD - API KEY HATALI','85'=>'WRONG_SMS_HEADER - Sms basligi onayli degil','84'=>'Tarih formati hatali','83'=>'Yetersiz veri','81'=>'YETERSIZ BAKIYE','77'=>'Ayni SMS son 2 dk icinde'];
            $bodyTrim = trim($respBody);
            if (isset($hataKodlari[$bodyTrim])) {
                $sonuc = '<div style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:8px;font-weight:bold;">Efetech hata kodu '.$bodyTrim.': '.$hataKodlari[$bodyTrim].'</div>';
            } elseif ($curlErr) {
                $sonuc = '<div style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:8px;font-weight:bold;">cURL baglanti hatasi: '.htmlspecialchars($curlErr).'</div>';
            } elseif ($httpCode >= 400) {
                $sonuc = '<div style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:8px;font-weight:bold;">HTTP '.$httpCode.' (response body ustte)</div>';
            } elseif (trim($respBody) === '') {
                $sonuc = '<div style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:8px;font-weight:bold;">Saglayici bos yanit dondu</div>';
            } else {
                $sonuc = '<div style="background:#dcfce7;color:#15803d;padding:14px;border-radius:8px;font-weight:bold;">Efetech yanit aldik. Body: '.htmlspecialchars($bodyTrim).'</div>';
            }
        }
    } catch (\Throwable $e) {
        $sonuc = '<div style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:8px;font-weight:bold;">EXCEPTION: '.htmlspecialchars($e->getMessage()).'</div>';
        $log = '<pre style="background:#1f2937;color:#f87171;padding:12px;border-radius:8px;overflow:auto;font-size:12px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }

    return "<div style='font-family:sans-serif;max-width:900px;margin:30px auto;padding:30px;background:#fff;border:2px solid #5C008E;border-radius:14px;'>"
         . "<h2 style='color:#5C008E;margin-top:0;'>SMS Test Sonucu</h2>"
         . "<p><b>Telefon:</b> $tel <br><b>Mesaj:</b> ".htmlspecialchars($mesaj)."</p>"
         . $sonuc
         . "<h3 style='margin-top:24px;color:#5C008E;'>Saglayici Cevabi</h3>"
         . $log
         . "<h3 style='margin-top:24px;color:#5C008E;'>Salon SMS Konfigi</h3>"
         . $cfg
         . "</div>";
});

// GECICI: Personel detay popup'ini test etmek icin ornek data doldurma rotasi.
// Kullanildiktan sonra bu blok silinecek.
// Bos olan TUM personellere farkli uzmanliklarda demo veri yazar.
Route::get('/dev-personel-demo-doldur', function() {
    $salon = \App\Salonlar::where('domain', $_SERVER['HTTP_HOST'])->first();
    if (!$salon) return 'Bu domain icin salon bulunamadi.';

    $demolar = [
        ['uzmanlik'=>'Saç & Renk Uzmanı','tecrube'=>8,'insta'=>'randevumcepte','aciklama'=>"8 yılı aşkın süredir saç tasarımı ve renklendirme alanında profesyonel hizmet vermektedir. L'Oréal Professional ve Wella Master Colorist sertifikalarına sahip; balayage, ombre ve highlight teknikleriyle özel tasarımlar hazırlar.\n\nİstanbul Güzellik Sanatları Akademisi mezunu. Müşteri memnuniyetini her zaman ön planda tutar; yenilikçi tekniklerle güncel trendleri takip eder."],
        ['uzmanlik'=>'Cilt Bakım Uzmanı','tecrube'=>6,'insta'=>'randevumcepte','aciklama'=>"Profesyonel cilt analizi, yaşlanma karşıtı bakım, akne ve leke tedavisi konularında uzmanlaşmıştır. Hydrafacial, mikrodermabrazyon ve kimyasal peeling sertifikalarına sahiptir.\n\nHer cilt tipine özel kişiselleştirilmiş bakım planları hazırlar; cildin sağlığını ve canlılığını maksimuma çıkarır."],
        ['uzmanlik'=>'Manikür & Pedikür Uzmanı','tecrube'=>5,'insta'=>'randevumcepte','aciklama'=>"Tırnak sağlığı, kalıcı oje, jel & akrilik tırnak uygulamaları, nail-art ve French manikür konularında uzman. Hijyen kurallarına titizlikle uyar; her uygulamada steril ekipman kullanır.\n\nSon trend tasarımları yakından takip eder; size özel tırnak stilini birlikte tasarlar."],
        ['uzmanlik'=>'Kuaför','tecrube'=>10,'insta'=>'randevumcepte','aciklama'=>"10 yıllık deneyimi ile saç kesimi, fön, topuz ve özel gün şekillendirmeleri konusunda uzmanlaşmıştır. Klasik ve modern saç stilleri arasında köprü kurar.\n\nDüğün, davet ve özel günler için profesyonel topuz ve saç tasarımı yapar; doğal ve şık görünümler hazırlar."],
        ['uzmanlik'=>'Makyaj Sanatçısı','tecrube'=>7,'insta'=>'randevumcepte','aciklama'=>"Düğün, gelin, davet ve günlük makyaj konusunda uzman. Profesyonel airbrush teknikleri ve uzun süre kalıcı makyaj uygulamalarıyla tanınır.\n\nHer cilt tonuna uygun renk paletleri kullanır; doğal güzelliğinizi ön plana çıkarır."],
    ];

    $personeller = \App\Personeller::where('salon_id', $salon->id)
        ->where(function($q){ $q->whereNull('uzmanlik')->orWhere('uzmanlik',''); })
        ->where(function($q){ $q->whereNull('aciklama')->orWhere('aciklama',''); })
        ->orderBy('id')
        ->get();

    if ($personeller->isEmpty()) {
        return "<div style='font-family:sans-serif;max-width:520px;margin:80px auto;padding:30px;background:#fef3c7;border:2px solid #f59e0b;border-radius:14px;'>"
             . "<h2 style='color:#92400e;margin:0 0 12px;'>ℹ Bos Personel Yok</h2>"
             . "<p>Bu salonda bos bio alanli personel kalmadi. Tum personellerin uzmanlik veya aciklamasi zaten dolu.</p>"
             . "<p style='margin-top:16px;'><a href='/' style='background:#5C008E;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>Anasayfaya Don</a></p>"
             . "</div>";
    }

    $sonuclar = [];
    foreach ($personeller as $i => $per) {
        $d = $demolar[$i % count($demolar)];
        $per->uzmanlik = $d['uzmanlik'];
        $per->aciklama = $d['aciklama'];
        $per->yillik_tecrube = $d['tecrube'];
        $per->instagram = $d['insta'];
        $per->save();
        $sonuclar[] = "<li><strong>".e($per->personel_adi)."</strong> &rarr; ".e($d['uzmanlik'])." ({$d['tecrube']} yil)</li>";
    }

    return "<div style='font-family:sans-serif;max-width:600px;margin:60px auto;padding:30px;background:#f0fff4;border:2px solid #34d399;border-radius:14px;'>"
         . "<h2 style='color:#15803d;margin:0 0 12px;'>✓ ".count($sonuclar)." Personele Demo Veri Yazildi</h2>"
         . "<ul style='line-height:1.8;'>".implode('', $sonuclar)."</ul>"
         . "<p style='margin-top:16px;'><a href='/' style='background:#5C008E;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>Anasayfaya Don &rarr; Personel kartina tikla</a></p>"
         . "</div>";
});
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

	Route::get('/odullerim',            'CarkifelekMusteriController@odullerim')->name('cark.odullerim');

	/* Puan ödülleri — müşteri tarafı */
	Route::get('/puanodullerim/{salonId?}', 'CarkifelekMusteriController@puanOdullerim')->name('cark.puanodullerim');
	Route::post('/puanodultalep',           'CarkifelekMusteriController@puanOdulTalep')->name('cark.puanodul.talep');

	/* Sadakat hub — birleşik puan + kupon sayfası */
	Route::get('/sadakat',                  'CarkifelekMusteriController@sadakatHub')->name('cark.sadakat');
});

/* Çarkıfelek — misafir erişimi serbest; kayıt kısmı kendi içinde zorlar */
Route::group(['middleware' => ['web']], function () {
	Route::get('/cark/{salonId}',   'CarkifelekMusteriController@goster')->name('cark.goster');
	Route::post('/cark/cevir',      'CarkifelekMusteriController@cevir')->name('cark.cevir');
	Route::post('/cark/smskod',     'CarkifelekMusteriController@smsKodGonder')->name('cark.smskod');
	Route::post('/cark/smsdogrula', 'CarkifelekMusteriController@smsKodDogrula')->name('cark.smsdogrula');
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
	 Route::get('/sozlesme/{arsiv_id}/{user_id}','HomeController@sozlesmeSayfasi');
	 Route::post('/sozlesme-kaydet','HomeController@sozlesmeKaydet');
	 Route::get('/anket/{token}','HomeController@anketSayfasi');
	 Route::post('/anket-kaydet','HomeController@anketKaydet');
	 Route::post('/anket-google-tiklandi','HomeController@anketGoogleTiklandi');
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
   // Yeni: SMS/sifre gerektirmeyen direkt randevu onayi (honeypot + rate-limit korumali)
   Route::post('/randevuonayla-direkt','HomeController@randevuonaylaDirekt');
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
	Route::get('/{isletme_adi}-{isletme_id}/personel/{personel_id}', 'HomeController@personelDetayPublic')->where('personel_id','[0-9]+')->name('personeldetay_public');
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

	// WhatsApp Yönetim Paneli
	Route::get('/whatsapp-panel', 'WhatsAppPanelController@index')->name('superadmin.whatsapp.panel');
	Route::get('/whatsapp-panel/dashboard-data', 'WhatsAppPanelController@dashboardData');
	Route::get('/whatsapp-panel/salonlar-data', 'WhatsAppPanelController@salonlarData');
	Route::get('/whatsapp-panel/loglar-data', 'WhatsAppPanelController@loglarData');
	Route::get('/whatsapp-panel/grafik-data', 'WhatsAppPanelController@grafikData');
	Route::get('/whatsapp-panel/mesaj/{id}', 'WhatsAppPanelController@mesajDetay');
	Route::get('/whatsapp-panel/salon/{salonId}/aliciler', 'WhatsAppPanelController@salonAliciDetay');
	Route::get('/whatsapp-panel/salon/{salonId}/alici/{telefon}/gecmis', 'WhatsAppPanelController@aliciMesajGecmisi');
	Route::get('/whatsapp-panel/salon/{salonId}/paket', 'WhatsAppPanelController@salonPaketDetay');
	Route::post('/whatsapp-panel/salon/{salonId}/deneme-baslat', 'WhatsAppPanelController@denemeBaslat');
	Route::post('/whatsapp-panel/salon/{salonId}/deneme-iptal', 'WhatsAppPanelController@denemeIptal');
	Route::post('/whatsapp-panel/salon/{salonId}/paket-set', 'WhatsAppPanelController@paketSet');
	Route::get('/whatsapp-panel/loglar-csv', 'WhatsAppPanelController@loglarCsv');
	Route::get('/whatsapp-panel/tip-dagilim', 'WhatsAppPanelController@tipDagilim');
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

/*
|--------------------------------------------------------------------------
| Sistem Yonetim V2 — Yeni gelismis admin paneli
|--------------------------------------------------------------------------
*/
Route::prefix('sistemyonetim/v2')->namespace('SistemYonetim')->group(function() {
    Route::get('/', 'PanelController@dashboard');
    Route::get('/dashboard', 'PanelController@dashboard')->name('sistemyonetim.v2.dashboard');

    // Salonlar
    Route::get('/salonlar', 'PanelController@salonlar')->name('sistemyonetim.v2.salonlar');
    Route::get('/salon/{id}', 'PanelController@salonDetay')->name('sistemyonetim.v2.salon');
    Route::post('/salon/{id}/askiya-al', 'PanelController@salonAskiyaAl');
    Route::post('/salon/{id}/aktif-et', 'PanelController@salonAktifEt');
    Route::post('/salon/{id}/mt-ata', 'PanelController@salonMusteriTemsilcisiAta');
    Route::post('/salon/{id}/hesabina-gir', 'PanelController@salonHesabinaGir');
    Route::get('/impersonation-bitir', 'PanelController@impersonationBitir')->name('sistemyonetim.v2.impersonation.bitir');

    // Notlar
    Route::post('/salon/{id}/not', 'PanelController@notEkle');
    Route::delete('/not/{id}', 'PanelController@notSil');
    Route::get('/not/{id}/pin', 'PanelController@notPin');

    // Ekip
    Route::get('/ekip', 'PanelController@ekip');
    Route::get('/ekip/yeni', 'PanelController@ekipFormYeni');
    Route::post('/ekip', 'PanelController@ekipKaydet');
    Route::get('/ekip/{id}/duzenle', 'PanelController@ekipFormDuzenle');
    Route::put('/ekip/{id}', 'PanelController@ekipGuncelle');
    Route::post('/ekip/{id}/pasif', 'PanelController@ekipPasifEt');

    // Aktivite
    Route::get('/aktivite-log', 'PanelController@aktiviteLog');

    // Ticket
    Route::get('/ticket', 'PanelController@ticketlar');
    Route::get('/ticket/yeni', 'PanelController@ticketYeni');
    Route::post('/ticket', 'PanelController@ticketKaydet');
    Route::get('/ticket/{id}', 'PanelController@ticketDetay');
    Route::post('/ticket/{id}/yanit', 'PanelController@ticketYanit');
    Route::post('/ticket/{id}/durum', 'PanelController@ticketDurum');
    Route::post('/ticket/{id}/ata', 'PanelController@ticketAta');
    Route::post('/ticket/{id}/oncelik', 'PanelController@ticketOncelik');

    // Saglik & Guvenlik
    Route::get('/sistem-saglik', 'PanelController@sistemSaglik');
    Route::get('/guvenlik/girisler', 'PanelController@girisLoglari');
    Route::get('/guvenlik/impersonation', 'PanelController@impersonationLoglari');

    // WhatsApp panel (v2 layout, AJAX endpoint'leri eski controllerda kalir)
    Route::get('/whatsapp', 'PanelController@whatsappPanel');

    // Profil
    Route::get('/profil', 'PanelController@profil');
    Route::put('/profil', 'PanelController@profilGuncelle');
    Route::post('/profil/sifre', 'PanelController@profilSifre');

    // CSV Export
    Route::get('/salonlar/csv', 'PanelController@salonlarCsv');
    Route::get('/aktivite-log/csv', 'PanelController@aktiviteCsv');
    Route::get('/ticket/csv', 'PanelController@ticketCsv');

    // Toplu islem
    Route::post('/salon/toplu-islem', 'PanelController@topluIslem');

    // API: arama + bildirim
    Route::get('/api/global-arama', 'PanelController@globalArama');
    Route::get('/api/salon-ara', 'PanelController@salonAraJson');
    Route::get('/api/bildirim-feed', 'PanelController@bildirimFeed');

    // Duyurular
    Route::get('/duyuru', 'DuyuruController@index');
    Route::get('/duyuru/yeni', 'DuyuruController@yeni');
    Route::post('/duyuru', 'DuyuruController@kaydet');
    Route::get('/duyuru/{id}', 'DuyuruController@detay')->where('id', '[0-9]+');
    Route::get('/duyuru/{id}/duzenle', 'DuyuruController@duzenle');
    Route::put('/duyuru/{id}', 'DuyuruController@guncelle');
    Route::delete('/duyuru/{id}', 'DuyuruController@sil');

    // Analiz: Risk + Performans + Hazir cevap + Dashboard chart
    Route::get('/risk', 'AnalizController@riskliSalonlar');
    Route::get('/performans', 'AnalizController@ekipPerformansi');

    Route::get('/hazir-cevap', 'AnalizController@hazirCevaplar');
    Route::get('/hazir-cevap/yeni', 'AnalizController@hazirCevapYeni');
    Route::post('/hazir-cevap', 'AnalizController@hazirCevapKaydet');
    Route::get('/hazir-cevap/{id}/duzenle', 'AnalizController@hazirCevapDuzenle');
    Route::put('/hazir-cevap/{id}', 'AnalizController@hazirCevapGuncelle');
    Route::delete('/hazir-cevap/{id}', 'AnalizController@hazirCevapSil');

    // API
    Route::get('/api/hazir-cevap', 'AnalizController@hazirCevapJson');
    Route::post('/api/hazir-cevap/{id}/kullan', 'AnalizController@hazirCevapKullan');
    Route::get('/api/dashboard-chart', 'AnalizController@dashboardChartData');
});

// Salon paneli — destek + duyuru okundu
Route::prefix('isletmeyonetim')->middleware('auth:isletmeyonetim')->group(function() {
    Route::post('/duyuru/{id}/okundu', 'SalonDestekController@duyuruOkundu');
    Route::get('/destek', 'SalonDestekController@destekListesi');
    Route::get('/destek/yeni', 'SalonDestekController@destekYeniForm');
    Route::post('/destek', 'SalonDestekController@destekKaydet');
    Route::get('/destek/{id}', 'SalonDestekController@destekDetay')->where('id', '[0-9]+');
    Route::post('/destek/{id}/yanit', 'SalonDestekController@destekYanit');
});

// Salon paneli — akilli hatirlatmalar (popup + bildirim feed)
Route::prefix('isletmeyonetim')->group(function() {
    Route::get('/api/hatirlatma-feed', 'SalonHatirlatmaController@feed')->name('isletmeadmin.hatirlatma.feed');
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
	Route::get('/log-hareketleri','StoreAdminController@logHareketleri')->name('isletmeadmin.log');
	Route::get('/whatsapp','StoreAdminController@whatsapp')->name('whatsapp.sayfa');
	Route::post('/whatsapp/baslat','StoreAdminController@whatsappBaslat')->name('whatsapp.baslat');
	Route::get('/whatsapp/durum','StoreAdminController@whatsappDurum')->name('whatsapp.durum');
	Route::get('/whatsapp/qr','StoreAdminController@whatsappQR')->name('whatsapp.qr');
	Route::post('/whatsapp/cikis','StoreAdminController@whatsappCikis')->name('whatsapp.cikis');
	Route::get('/whatsapp/kanal-durum','StoreAdminController@whatsappKanalDurum')->name('whatsapp.kanal.durum');
	Route::post('/whatsapp/kanal-toggle','StoreAdminController@whatsappKanalToggle')->name('whatsapp.kanal.toggle');
	// Salon kendi istatistikleri (sadece kendi salonu, başkasını göremez)
	Route::get('/whatsapp/ozet-data','StoreAdminController@whatsappOzetData')->name('whatsapp.ozet.data');
	Route::get('/whatsapp/loglar-data','StoreAdminController@whatsappLoglarData')->name('whatsapp.loglar.data');
	Route::get('/whatsapp/aliciler-data','StoreAdminController@whatsappAlicilarData')->name('whatsapp.aliciler.data');
	Route::get('/whatsapp/alici/{telefon}/gecmis','StoreAdminController@whatsappAliciGecmisData')->name('whatsapp.alici.gecmis');
	Route::get('/whatsapp/paket-durum','StoreAdminController@whatsappPaketDurum')->name('whatsapp.paket.durum');
	Route::post('/whatsapp/paket-talep','StoreAdminController@whatsappPaketTalep')->name('whatsapp.paket.talep');
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
	Route::get('/stok-yonetimi','StoreAdminController@urunler');
	Route::match(['get','post'],'/stok/{action}','StoreAdminController@stokApi');
	Route::post('/urunekleguncelle','StoreAdminController@urun_ekle_guncelle');
	Route::post('/paketekleguncelle','StoreAdminController@paket_ekle_guncelle');
	Route::get('/paketkopyala/{kaynak}/{hedef}','StoreAdminController@paket_kopyala_salonlar_arasi');
	Route::get('/pakethizmetlerinisalonakopyala/{kaynak}/{hedef}','StoreAdminController@paket_hizmetlerini_salona_kopyala');
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
	Route::post('/cinsiyet-otomatik-doldur','StoreAdminController@cinsiyetOtomatikDoldur');
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
	Route::get('/tts-proxy','StoreAdminController@ttsProxy');
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
 	Route::get('/tahsilat-modern/{musteriid}/{adisyonid}','StoreAdminController@tahsilatekraniModern');
 	Route::get('/tahsilat-modern','StoreAdminController@tahsilatModernSecim');
 	Route::post('/adisyon-tarih-guncelle','StoreAdminController@adisyonTarihGuncelle');
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
	Route::post('/form-sablonlari-sira-guncelle','StoreAdminController@formSablonlariSiraGuncelle');

	// Memnuniyet Anketi
	Route::get('/anket-sablonlari','StoreAdminController@anketSablonlari')->name('isletmeadmin.anketSablonlari');
	Route::get('/anket-sablon-getir','StoreAdminController@anketSablonGetir');
	Route::post('/anket-sablon-kaydet','StoreAdminController@anketSablonKaydet');
	Route::post('/anket-sablon-guncelle','StoreAdminController@anketSablonGuncelle');
	Route::post('/anket-sablon-sil','StoreAdminController@anketSablonSil');
	Route::post('/anket-manuel-gonder','StoreAdminController@anketManuelGonder');
	Route::get('/anket-sonuclari','StoreAdminController@anketSonuclari')->name('isletmeadmin.anketSonuclari');
	Route::get('/anket-gonderim-detay','StoreAdminController@anketGonderimDetay');
	Route::post('/google-review-kaydet','StoreAdminController@googleReviewKaydet');
	Route::post('/reputation-premium-ac','StoreAdminController@reputationPremiumAc');

	// Musteri yorumlari + puanlari (salon yorum panosu)
	Route::get('/musteri-yorumlari','StoreAdminController@musteriYorumlari')->name('isletmeadmin.musteriYorumlari');

	Route::post('/sozlesme-olustur','StoreAdminController@sozlesmeOlustur');
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
	Route::get('/hesabim','StoreAdminController@hesabim');
	Route::post('/hesabim/fatura-bilgi-guncelle','StoreAdminController@hesabimFaturaBilgiGuncelle');
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
	Route::get('/personelTakvimdeGorunsunToggle','StoreAdminController@personelTakvimdeGorunsunToggle');
	Route::post('/personelArsivle','StoreAdminController@personelArsivle');
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
	Route::get('/primraporu','StoreAdminController@primRaporu')->name('isletmeadmin.primraporu');
	Route::post('/primhareketekle','StoreAdminController@primHareketEkle');
	Route::post('/primhareketsil','StoreAdminController@primHareketSil');
	Route::get('/primhareketlistesi','StoreAdminController@primHareketListesi');
	Route::post('/primode','StoreAdminController@primOde');
	Route::post('/primodemesil','StoreAdminController@primOdemeSil');
	Route::get('/primodemelistesi','StoreAdminController@primOdemeListesi');
	Route::get('/personelprimdetayi','StoreAdminController@personelPrimDetayi');
	Route::get('/hizmetRaporFiltre','StoreAdminController@hizmetRaporFiltre');
	Route::get('/hizmetiAlanMusteriler','StoreAdminController@hizmetiAlanMusteriler');
	Route::get('/urunRaporFiltre','StoreAdminController@urunRaporFiltre');
	Route::get('/paketRaporFiltre','StoreAdminController@paketRaporFiltre');
	Route::get('/personelRaporFiltre','StoreAdminController@personelRaporFiltre');
	Route::get('/personel-yonetimi','StoreAdminController@personelYonetimi');
	 	 	Route::get('/carkifelek','StoreAdminController@carkifelek')->name('isletmeadmin.carkifelek');
	 	 	Route::post('/carkdilimekle', [StoreAdminController::class, 'carkdilimekle'])->name('isletmeadmin.carkdilimekle');
Route::get('/carkverilerigetir', [StoreAdminController::class, 'carkverilerigetir'])->name('isletmeadmin.carkverilerigetir');
Route::get('/carkkazananlar', [StoreAdminController::class, 'carkKazananlar'])->name('isletmeadmin.cark.kazananlar');
Route::post('/carkkuponkullan', [StoreAdminController::class, 'carkKuponKullan'])->name('isletmeadmin.cark.kuponkullan');
Route::post('/carkkupondogrula', [StoreAdminController::class, 'carkKuponDogrula'])->name('isletmeadmin.cark.kupondogrula');
Route::get('/carkhatirlatma',       [StoreAdminController::class, 'carkHatirlatma'])->name('isletmeadmin.cark.hatirlatma');
Route::get('/carkhatirlatmagetir',  [StoreAdminController::class, 'carkHatirlatmaGetir'])->name('isletmeadmin.cark.hatirlatma.getir');
Route::post('/carkhatirlatmakaydet', [StoreAdminController::class, 'carkHatirlatmaKaydet'])->name('isletmeadmin.cark.hatirlatma.kaydet');

/* Puan ödülleri — admin yönetim */
Route::get('/puanodulleri',      [StoreAdminController::class, 'puanOdulleri'])->name('isletmeadmin.puanodulleri');
Route::post('/puanodulkaydet',   [StoreAdminController::class, 'puanOdulKaydet'])->name('isletmeadmin.puanodul.kaydet');
Route::post('/puanodulsil',      [StoreAdminController::class, 'puanOdulSil'])->name('isletmeadmin.puanodul.sil');
Route::post('/puanorneklerekle-admin', [StoreAdminController::class, 'puanOrneklerEkle'])->name('isletmeadmin.puanodul.ornekekle');

/* GEÇİCİ — örnek puan ödülleri ekle (sonra silinecek) */
Route::get('/puanorneklerekle/{salonId}', function ($salonId) {
    $salonId = (int) $salonId;
    $salon = \App\Salonlar::find($salonId);
    if (!$salon) return 'Salon bulunamadı.';

    // Tablo yoksa oluştur
    if (!\Schema::hasTable('salon_puan_odulleri')) {
        \Schema::create('salon_puan_odulleri', function ($table) {
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

    $ornekler = [
        ['puan_esigi' => 100,  'baslik' => '%10 Hizmet İndirimi', 'aciklama' => 'Bir sonraki hizmet alımınızda %10 indirim kazanın.', 'tip' => 'hizmet_indirimi', 'deger' => 10,  'sira' => 1],
        ['puan_esigi' => 250,  'baslik' => '%20 Hizmet İndirimi', 'aciklama' => 'Sevdiğiniz hizmeti %20 indirimli alın.',                'tip' => 'hizmet_indirimi', 'deger' => 20,  'sira' => 2],
        ['puan_esigi' => 400,  'baslik' => '%15 Ürün İndirimi',   'aciklama' => 'Tüm bakım ürünlerinde geçerli %15 indirim.',           'tip' => 'urun_indirimi',   'deger' => 15,  'sira' => 3],
        ['puan_esigi' => 600,  'baslik' => '%30 Hizmet İndirimi', 'aciklama' => 'Premium hizmet indirimi — sınırlı zaman.',             'tip' => 'hizmet_indirimi', 'deger' => 30,  'sira' => 4],
        ['puan_esigi' => 1000, 'baslik' => 'Ücretsiz Saç Bakımı', 'aciklama' => 'Tamamen ücretsiz profesyonel saç bakımı paketi.',      'tip' => 'hediye',          'deger' => null,'sira' => 5],
        ['puan_esigi' => 1500, 'baslik' => 'Ücretsiz Cilt Bakımı','aciklama' => 'Yüz analizi + cilt bakımı (50 dk) hediyemizdir.',      'tip' => 'hediye',          'deger' => null,'sira' => 6],
    ];

    $sayac = 0;
    foreach ($ornekler as $o) {
        \App\SalonPuanOdulleri::create(array_merge($o, [
            'salon_id' => $salonId,
            'aktif'    => 1,
        ]));
        $sayac++;
    }

    return "Tamam! {$sayac} örnek puan ödülü oluşturuldu. <a href='/isletmeyonetim/puanodulleri?sube={$salonId}'>→ Puan Ödülleri sayfasına git</a>";
});

/* GEÇİCİ — örnek çarkıfelek kazanan verisi üret (sonra silinecek) */
Route::get('/carkornekveriuret/{salonId}', function ($salonId) {
    $salonId = (int) $salonId;
    $cark = \App\CarkifelekSistemi::where('salon_id', $salonId)->first();
    if (!$cark) return 'Bu salonda çark sistemi yok. Önce çarkı kurun.';

    $users = \App\User::orderBy('id')->limit(8)->pluck('id')->toArray();
    if (count($users) < 1) return 'Sistemde kullanıcı yok.';

    $dilimler = \App\CarkifelekDilimleri::where('cark_id', $cark->id)->get();
    $orneklerSablonu = [
        ['tip' => 'puan',            'deger' => 50,  'ismi' => 'Puan'],
        ['tip' => 'puan',            'deger' => 100, 'ismi' => 'Puan'],
        ['tip' => 'puan',            'deger' => 200, 'ismi' => 'Puan'],
        ['tip' => 'hizmet_indirimi', 'deger' => 10,  'ismi' => 'Hizmet İnd.'],
        ['tip' => 'hizmet_indirimi', 'deger' => 20,  'ismi' => 'Hizmet İnd.'],
        ['tip' => 'hizmet_indirimi', 'deger' => 25,  'ismi' => 'Hizmet İnd.'],
        ['tip' => 'urun_indirimi',   'deger' => 15,  'ismi' => 'Ürün İnd.'],
        ['tip' => 'urun_indirimi',   'deger' => 30,  'ismi' => 'Ürün İnd.'],
        ['tip' => 'tekrar_dene',     'deger' => null,'ismi' => 'Tekrar Dene'],
        ['tip' => 'bos',             'deger' => null,'ismi' => 'Boş'],
    ];

    $sayac = 0;
    foreach ($orneklerSablonu as $i => $o) {
        $userId = $users[$i % count($users)];
        $gunOnce = rand(0, 14);
        $tarih = \Carbon\Carbon::now()->subDays($gunOnce)->subMinutes(rand(0, 720));

        $dilim = $dilimler->where('tip', $o['tip'])->first();

        $log = \App\CarkifelekCevirmeLoglari::create([
            'cark_id'     => $cark->id,
            'salon_id'    => $salonId,
            'user_id'     => $userId,
            'randevu_id'  => null,
            'dilim_id'    => $dilim ? $dilim->id : null,
            'tip'         => $o['tip'],
            'deger'       => $o['deger'],
            'dilim_ismi'  => $o['ismi'],
        ]);
        \App\CarkifelekCevirmeLoglari::where('id', $log->id)->update([
            'created_at' => $tarih, 'updated_at' => $tarih
        ]);

        if ($o['tip'] === 'puan') {
            $puan = \App\SalonPuanlar::firstOrNew(['salon_id' => $salonId, 'user_id' => $userId]);
            $puan->puan = ((float) ($puan->puan ?? 0)) + $o['deger'];
            $puan->save();
        } elseif (in_array($o['tip'], ['hizmet_indirimi', 'urun_indirimi'])) {
            $kullanildi = rand(0, 2) === 0 ? 1 : 0;   // ~%33 kullanılmış
            $sureDoldu  = $gunOnce > 10 && !$kullanildi && rand(0, 1) === 0;
            $sonGun     = $sureDoldu
                ? \Carbon\Carbon::now()->subDays(rand(1, 5))->toDateString()
                : \Carbon\Carbon::parse($tarih)->addDays(30)->toDateString();

            $baslik = '%' . ((int) $o['deger']) . ' ' . ($o['tip'] === 'hizmet_indirimi' ? 'Hizmet İndirimi' : 'Ürün İndirimi');

            \App\CarkifelekOdulleri::create([
                'log_id'            => $log->id,
                'salon_id'          => $salonId,
                'user_id'           => $userId,
                'kod'               => strtoupper(\Illuminate\Support\Str::random(8)),
                'tip'               => $o['tip'],
                'deger'             => $o['deger'],
                'baslik'            => $baslik,
                'kullanildi'        => $kullanildi,
                'kullanim_tarihi'   => $kullanildi ? \Carbon\Carbon::parse($tarih)->addDays(rand(1, 5)) : null,
                'gecerlilik_tarihi' => $sonGun,
            ]);
        }
        $sayac++;
    }

    return "Tamam! {$sayac} örnek kayıt oluşturuldu. <a href='/isletmeyonetim/carkkazananlar?sube={$salonId}'>→ Kazananlar sayfasına git</a>";
});

Route::get('/personel_listesi_getir','StoreAdminController@personel_listesi_getir');
	Route::get('/adisyon_filtreli_getir_personel','StoreAdminController@adisyon_filtreli_getir_personel');
	 	 	Route::get('/devreden-aylar','StoreAdminController@devredenAylar')->name('isletmeadmin.kasadefteri');

	Route::post('/paketSecimKaydet','StoreAdminController@paketSecimKaydet');

Route::get('/bosFormIndir','StoreAdminController@bosFormIndir');
Route::get('/bosFormIndirDinamik','StoreAdminController@bosFormIndirDinamik');
Route::get('/satisDetaylariveDuzenleme','StoreAdminController@satisDetaylariveDuzenleme');
Route::post('/satisTarihiGuncelle','StoreAdminController@satisTarihiGuncelle');
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

Route::post('/webhook/whatsapp','WhatsAppWebhookController@handle')->name('whatsapp.webhook');
Route::get('/check-memory', function () {
    return 'Memory Limit: ' . ini_get('memory_limit');
});

