@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">
                  {{$sayfa_baslik}}
               </li>
            </ol>
         </nav>
      </div>
     
   </div>
</div>
<div class="card-box mb-30">
	<div class="pd-20">
			@if(isset($_GET['moid']))
				@if(\App\Odemeler::where('odeme_no',$_GET['moid'])->value('basarili'))
		 			<div class="alert alert-success alert-dismissible fade show" role="alert">
		 		 	<span>Ödemeniz başarıyla gerçekleşmiştir. Teşekkür ederiz.</span>
		 		@else
          		<div class="alert alert-danger alert-dismissible fade show" role="alert">
          		<span>Ödeme sırasında bir sorun meydana geldi. Lütfen tekrar deneyiniz!<br> Hata Kodu : {{\App\Odemeler::where('odeme_no',$_GET['moid'])->value('basarisiz_kod')}}<br>Hata Nedeni : {{\App\Odemeler::where('odeme_no',$_GET['moid'])->value('basarisiz_neden')}}</span>;
            @endif
             
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
        	</div>
        	@endif
	</div>
	 <div class="pd-20" style="padding-top:20px">

	 		 <?php
	 		 		if($isletme->adres != null )
	 		 		{
	 		 			## 1. ADIM için örnek kodlar ##

									####################### DÜZENLEMESİ ZORUNLU ALANLAR #######################
									#
									## API Entegrasyon Bilgileri - Mağaza paneline giriş yaparak BİLGİ sayfasından alabilirsiniz.
									$merchant_id 	= '452223';
									$merchant_key 	= 'Mwjwj1HdCwxYJY2j';
									$merchant_salt	= 'TuF3kaYgxbNKR7Zx';
									#
									## Müşterinizin sitenizde kayıtlı veya form vasıtasıyla aldığınız eposta adresi
									$email = $musteri_bilgileri->email;
									#
									
									#
									## Sipariş numarası: Her işlemde benzersiz olmalıdır!! Bu bilgi bildirim sayfanıza yapılacak bildirimde geri gönderilir.
									$merchant_oid = Auth::guard('isletmeyonetim')->user()->id.date('YmdHis');
									#
									## Müşterinizin sitenizde kayıtlı veya form aracılığıyla aldığınız ad ve soyad bilgisi
									$user_name = $musteri_bilgileri->name;
									#
									## Müşterinizin sitenizde kayıtlı veya form aracılığıyla aldığınız adres bilgisi
									$user_address = $isletme->adres;
									#
									## Müşterinizin sitenizde kayıtlı veya form aracılığıyla aldığınız telefon bilgisi
									$user_phone = $musteri_bilgileri->gsm1;
									#
									## Başarılı ödeme sonrası müşterinizin yönlendirileceği sayfa
									## !!! Bu sayfa siparişi onaylayacağınız sayfa değildir! Yalnızca müşterinizi bilgilendireceğiniz sayfadır!
									## !!! Siparişi onaylayacağız sayfa "Bildirim URL" sayfasıdır (Bakınız: 2.ADIM Klasörü).!
									
									$merchant_ok_url =  "https://".$_SERVER['HTTP_HOST']."/isletmeyonetim/uyelik?sube=".$request->sube."&success=true&moid=".$merchant_oid;

									if(isset($_GET['yenisube']))
										$merchant_ok_url .= "?yenisube=1";
									
									$urun =  $isletme->salon_adi.' için '.$uyelik->uyelik_adi;
									$payment_amount = 0;
									if($request->periyot=='aylik')
									{
										$urun .= ' Aylık Üyelik';
										$payment_amount = $uyelik->aylik_tutar*100;

									}
									if($request->periyot=='yillik'){
										$urun .= ' Yıllık Üyelik';
										$payment_amount = $uyelik->yillik_tutar*100;
									}
									## Tahsil edilecek tutar.
									

									

									
									## Ödeme sürecinde beklenmedik bir hata oluşması durumunda müşterinizin  yönlendirileceği sayfa
									## !!! Bu sayfa siparişi iptal edeceğiniz sayfa değildir! Yalnızca müşterinizi bilgilendireceğiniz sayfadır!
									## !!! Siparişi iptal edeceğiniz sayfa "Bildirim URL" sayfasıdır (Bakınız: 2.ADIM Klasörü).
									$merchant_fail_url = "https://".$_SERVER['HTTP_HOST']."/isletmeyonetim/uyelik?sube=".$request->sube."&success=false&moid=".$merchant_oid;
									#
									## Müşterinin sepet/sipariş içeriği
									 
									 
									$user_basket = base64_encode(json_encode(array(
										array($urun, $payment_amount, 1) // 1. ürün (Ürün Ad - Birim Fiyat - Adet )
										 // 2. ürün (Ürün Ad - Birim Fiyat - Adet )
										   // 3. ürün (Ürün Ad - Birim Fiyat - Adet )
									)));
									 
									############################################################################################

									## Kullanıcının IP adresi
									if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
										$ip = $_SERVER["HTTP_CLIENT_IP"];
									} elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
										$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
									} else {
										$ip = $_SERVER["REMOTE_ADDR"];
									}

									## !!! Eğer bu örnek kodu sunucuda değil local makinanızda çalıştırıyorsanız
									## buraya dış ip adresinizi (https://www.whatismyip.com/) yazmalısınız. Aksi halde geçersiz paytr_token hatası alırsınız.
									$user_ip=$ip;
									##

									## İşlem zaman aşımı süresi - dakika cinsinden
									$timeout_limit = "30";

									## Hata mesajlarının ekrana basılması için entegrasyon ve test sürecinde 1 olarak bırakın. Daha sonra 0 yapabilirsiniz.
									$debug_on = 1;

								    ## Mağaza canlı modda iken test işlem yapmak için 1 olarak gönderilebilir.
								    $test_mode = 0;

									$no_installment	= 0; // Taksit yapılmasını istemiyorsanız, sadece tek çekim sunacaksanız 1 yapın

									## Sayfada görüntülenecek taksit adedini sınırlamak istiyorsanız uygun şekilde değiştirin.
									## Sıfır (0) gönderilmesi durumunda yürürlükteki en fazla izin verilen taksit geçerli olur.
									$max_installment = 0;

									$currency = "TL";
									
									####### Bu kısımda herhangi bir değişiklik yapmanıza gerek yoktur. #######
									$hash_str = $merchant_id .$user_ip .$merchant_oid .$email .$payment_amount .$user_basket.$no_installment.$max_installment.$currency.$test_mode;
									$paytr_token=base64_encode(hash_hmac('sha256',$hash_str.$merchant_salt,$merchant_key,true));
									$post_vals=array(
											'merchant_id'=>$merchant_id,
											'user_ip'=>$user_ip,
											'merchant_oid'=>$merchant_oid,
											'email'=>$email,
											'payment_amount'=>$payment_amount,
											'paytr_token'=>$paytr_token,
											'user_basket'=>$user_basket,
											'debug_on'=>$debug_on,
											'no_installment'=>$no_installment,
											'max_installment'=>$max_installment,
											'user_name'=>$user_name,
											'user_address'=>$user_address,
											'user_phone'=>$user_phone,
											'merchant_ok_url'=>$merchant_ok_url,
											'merchant_fail_url'=>$merchant_fail_url,
											'timeout_limit'=>$timeout_limit,
											'currency'=>$currency,
								         'test_mode'=>$test_mode
										);
									
									$ch=curl_init();
									curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch, CURLOPT_POST, 1) ;
									curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
									curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
									curl_setopt($ch, CURLOPT_TIMEOUT, 20);
									
									 // XXX: DİKKAT: lokal makinanızda "SSL certificate problem: unable to get local issuer certificate" uyarısı alırsanız eğer
								     // aşağıdaki kodu açıp deneyebilirsiniz. ANCAK, güvenlik nedeniyle sunucunuzda (gerçek ortamınızda) bu kodun kapalı kalması çok önemlidir!
								     // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									 
									$result = @curl_exec($ch);

									if(curl_errno($ch))
										die("PAYTR IFRAME connection error. err:".curl_error($ch));

									curl_close($ch);
									
									$result=json_decode($result,1);
										
									if($result['status']=='success'){
										$post_vals2 = array();
										if(isset($_GET['sube']))
										{
												$post_vals2=array(
													'merchant_id'=>$merchant_id,
													'status'=>$result['status'],
													'merchant_oid'=>$merchant_oid,
													'total_amount'=>$payment_amount,
													'payment_amount'=>$payment_amount,
													
													'hash'=>$hash_str,
													'payment_type'=>'card',
		 											'periyot' => $_GET['periyot'],
		 											'uyelik_turu' => $uyelik->id,
													'currency'=>$currency,
													'sube'=>$_GET['sube'],
													'failed_reason_code'=>isset($result['failed_reason_code']) ? $result['failed_reason_code'] : '',
										         'failed_reason_msg'=>isset($result['failed_reason_msg']) ? $result['failed_reason_msg'] : ''
												); 
										}
										else{
											$post_vals2=array(
													'merchant_id'=>$merchant_id,
													'status'=>$result['status'],
													'merchant_oid'=>$merchant_oid,
													'total_amount'=>$payment_amount,
													'payment_amount'=>$payment_amount,
													
													'hash'=>$hash_str,
													'payment_type'=>'card',
		 											'periyot' => $_GET['periyot'],
		 											'uyelik_turu' => $uyelik->id,
													'currency'=>$currency,
													 
													'failed_reason_code'=>isset($result['failed_reason_code']) ? $result['failed_reason_code'] : '',
										         'failed_reason_msg'=>isset($result['failed_reason_msg']) ? $result['failed_reason_msg'] : ''
											); 
										}
											
										
										$token=$result['token'];
										$ch2=curl_init();
										curl_setopt($ch2, CURLOPT_URL, "https://app.randevumcepte.com.tr/api/v1/odeme-bildirimi");
										curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch2, CURLOPT_POST, 1) ;
										curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_vals2);
										curl_setopt($ch2, CURLOPT_FRESH_CONNECT, true);
										curl_setopt($ch2, CURLOPT_TIMEOUT, 20);

										$result_notification = @curl_exec($ch);
									 


										


									}
									else
										die("PAYTR IFRAME failed. reason:".$result['reason']);
									#########################################################################

									
	 		 		?>
	 		 		<!-- Ödeme formunun açılması için gereken HTML kodlar / Başlangıç -->
								   <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
								   <iframe src="https://www.paytr.com/odeme/guvenli/<?php echo $token;?>" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
									<script>iFrameResize({},'#paytriframe');</script>
					<?php 
					}
	 		 		else{
	 		 			 ?>
	 		 			 <form method="POST" id="uyelikiletisimbilgileri">
	 		 			 		{!!csrf_field()!!}
	 		 			 		<div class="row">
	 		 			 			<div class="col-md-12">
	 		 			 				<div class="alert alert-warning alert-dismissible fade show" role="alert">
          								<img src="/public/img/caution-sign.png" >
              							<span>Üyelik işlemleri için lütfen kişisel ve iletişim bilgilerinizi giriniz.</span>
                					</div>
	 		 			 			</div>
	 		 			 	 
	 		 			 			<div class="col-md-6">
	 		 			 		
                  				<h2>İletişim Bilgileri</h2>
                   
		                  		<div class="form-group">
		                  			<label>Ad Soyad (zorunlu)</label>
		                  			<input type="text" name="adsoyad" class="form-control" required value="{{Auth::guard('isletmeyonetim')->user()->name}}">
		                  		</div>
		                  	 
		                  		<div class="form-group">
		                  			<label>Telefon (zorunlu)</label>
		                  			<input type="tel" name="telefon" required data-inputmask=" 'mask' : '5999999999'"  class="form-control" value="{{Auth::guard('isletmeyonetim')->user()->gsm1}}" inputmode="text">
		                  	 	</div>
		                  	 	<div class="form-group">
		                  			<label>Email (zorunlu)</label>
		                  			<input type="email" name="email"  required class="form-control" value="{{Auth::guard('isletmeyonetim')->user()->email}}" inputmode="text">
		                  	 	</div>
		                  	 	<div class="form-group">
		                  			<label>TC Kimlik No (zorunlu)</label>
		                  			<input type="tel" required name="tc_kimlik_no" data-inputmask=" 'mask' : '99999999999'" class="form-control" value="{{Auth::guard('isletmeyonetim')->user()->tc_kimlik_no}}" inputmode="text">
		                  	 	</div>
		                  	 	 
		                  	 	
		                  	  
		                  	</div>
		                  	<div class="col-md-6">
		                  		<h2>Fatura Bilgileri</h2>
                   						<div class="form-group">
		                  			<label>Adres (zorunlu)</label>
		                  			<textarea name="adres" required class="form-control" value="{{Auth::guard('isletmeyonetim')->user()->adres}}"></textarea>
		                  	 	</div>
		                  		<div class="form-group">
		                  			<label>Vergi Adı (Vergi levhası üzerine olan gerçek/tüzel kişilik, zorunlu)</label>
		                  			<input type="text" name="vergi" class="form-control" required value="{{$isletme->vergi_adi}}">
		                  		</div>
		                  	 
		                  		<div class="form-group">
		                  			<label>Vergi Dairesi (zorunlu)</label>
		                  			<input type="text" name="vergi_dairesi" required  class="form-control" value="{{$isletme->vergi_dairesi}}">
		                  	 	</div>
		                  	 	<div class="form-group">
		                  			<label>Vergi Numarası (zorunlu)</label>
		                  			<input type="tel" name="vergi_no"  required data-inputmask=" 'mask' : '9999999999'" class="form-control" value="{{$isletme->vergi_no}}" inputmode="text">
		                  	 	</div>
		                  	 	<div class="form-group">
		                  			<label>KDV Oranı</label>
		                  			<input type="tel" name="kdv_orani" class="form-control" value="{{$isletme->kdv_orani}}">
		                  	 	</div>
		                  	 	<div class="form-group">
		                  	 		 
		                  	 		<button type="submit" class="btn btn-success">Ödeme Adımına Geç</button>
		                  	 	</div>
		                  	 	 
		                  	</div>
		                  

		                  </div>


                  </form>
            
	 		 			 <?php
	 		 		}
							  
									?>

									
	 				 
	 </div>
</div>

@endsection