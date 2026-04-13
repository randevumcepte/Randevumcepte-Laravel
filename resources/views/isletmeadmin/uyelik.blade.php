@extends("layout.layout_isletmeadmin")
@section("content")
<div class="page-header" style="display: none;">
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

					<div class="container px-0 text-center" style="max-width:1800px;display: none;">
						<h2 style="margin-bottom:20px">Paketler</h2>
						<h2 style="margin-bottom:40px">Yıllık plan seçin <span style="color: #ff0000;"> 2 ay ücretsiz kullanın</span></h2>Aylık
						<label class="switch">
                                                <input id="periyot" name="periyot" type="checkbox">
                                                <span class="slider"></span>
                                                </label> Yıllık
					</div>
					<div class="container px-0" style="max-width:1800px;margin-top:30px;display: none;">
					
						<div class="row" id="aylik_uyelikler">
							<div class="col-md-3 mb-30"></div>
							<div class="col-md-3 mb-30">
								<div class="card-box pricing-card-style2">
									<div class="pricing-card-header">
										<div class="left">
											<h3>BAŞLANGIÇ</h3> 
										</div>
										<div class="right">
											<div class="pricing-price">{{number_format(1250,2,',','.')}} ₺<span>/AY<br>KDV HARİÇ</span></div>
										</div>
									</div>
									<div class="pricing-card-body">
										<div class="pricing-points">
											<ul>
												<li>Randevumcepte Uygulaması</li>
												<li>3 Kullanıcı</li> 
												<li>Randevu Yönetimi</li>
												<li>Müşteri Yönetimi</li>
												<li>Personel Yönetimi</li>
												<li>İşletme Yönetimi</li>											
												<li>Ajanda Yönetimi</li>
												<li>Çoklu Şube Yönetimi</li>
												<li>Ön Görüşme</li>
												<li>Özel Müşteri Temsilcisi</li>

												<li>SMS Yönetimi (500 SMS hediye)</li>
											
											</ul>
										</div>
									</div>
									<div class="cta">
										@if($isletme->uyelik_turu == 1 && $isletme->uyelik_periyodu == 1 && $kalan_uyelik_suresi >= 0 && !isset($_GET['yenisube']))
										<a href="#" class="btn btn-danger btn-rounded btn-lg">MEVCUT PAKET</a>
										@else
										<a href="/isletmeyonetim/odeme?uyelikturu=1&periyot=aylik&sube={{(isset($_GET['yenisube'])) ? $_GET['yenisube'] : $isletme->id }}" class="btn btn-primary btn-rounded btn-lg"
											>ÜYELİĞİ BAŞLAT</a
										>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-30">
								<div class="card-box pricing-card-style2">
									<div class="pricing-card-header">
										<div class="left">
											<h3>STANDART</h3>
										 
										</div>
										<div class="right">
											<div class="pricing-price">{{number_format(1800,2,',','.')}} ₺<span>/AY<br>KDV HARİÇ</span></div>
										</div>
									</div>
									<div class="pricing-card-body">
										<div class="pricing-points">
											<ul>
												<li style="color:#ff0000">Başlangıç Paket Tüm Özellikleri</li>
												
												<li>6 Personel</li>
												<li>Web Sitesi</li>
												<li>Lokal SEO Yönetimi</li>
												<li>Paket Yönetimi / Seans Takibi</li>								
												<li>Stok Yönetimi</li>
												<li>Kasa Yönetimi</li>
												<li>Borç Takibi</li>
												<li>Satış Yönetimi</li>
												<li>Arşiv Yönetimi</li>
												<li>Prim Hesaplama</li>
												<li>Gelişmiş İstatistik</li>
												<li>Sınırsız Randevu</li>
												<li>Sözleşme Yönetimi</li>
												<li>Dijital İmza</li>
												<li>SMS Yönetimi (1000 SMS hediye)</li>
											
											</ul>
										</div>
									</div>
									<div class="cta">
										@if($isletme->uyelik_turu == 2 && $isletme->uyelik_periyodu == 1 && $kalan_uyelik_suresi >= 0 && !isset($_GET['yenisube']))
										<a href="#" class="btn btn-danger btn-rounded btn-lg">MEVCUT PAKET</a>
										@else
										<a href="/isletmeyonetim/odeme?uyelikturu=2&periyot=aylik&sube={{(isset($_GET['yenisube'])) ? $_GET['yenisube'] : $isletme->id }}" class="btn btn-primary btn-rounded btn-lg"
											>ÜYELİĞİ BAŞLAT</a
										>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-30"></div>
							<div class="col-md-3 mb-30" style="display:none">
								<div class="card-box pricing-card-style2">
									<div class="pricing-card-header">
										<div class="left">
											<h3>PREMIUM</h3>
											<h5>Yıllık 1000 SMS ve 500dk. Santral Hediye</h5>
										</div>
										<div class="right">
											<div class="pricing-price">{{number_format(1200,2,',','.')}} ₺<span>/AY<br>KDV HARİÇ</span></div>
										</div>
									</div>
									<div class="pricing-card-body">
										<div class="pricing-points">
											<ul>
												<li style="color:#ff0000">Standart Paket Tüm Özellikleri</li>
												<li>Sınırsız Personel</li>
												<li>Adınıza Özel Uygulama</li>
												<li>Sosyal Medya Entegrasyonu</li>
												<li>Etklinlik Yönetimi</li>
												<li>Senet Takibi</li>
												<li>Reklam Yönetimi</li>
												<li>Sesli Randevuya Gelme Onayı</li>
												<li>Sesli Randevu Hatırlatma</li>
												<li>Sesli Anket</li>
											</ul>
										</div>
									</div>
									<div class="cta">
										{{$isletme->uyelik_turu}} {{$kalan_uyelik_suresi}} {{$isletme->uyelik_periyodu}}
										{{$isletme->uyelik_turu}}
										@if((int)$isletme->uyelik_turu === 3 
    && (int)$isletme->uyelik_periyodu === 1  
    && (int)$kalan_uyelik_suresi >= 0 
    && !request()->has('yenisube'))
										<a href="#" class="btn btn-danger btn-rounded btn-lg">MEVCUT PAKET</a>
										@else
										<a href="/isletmeyonetim/odeme?uyelikturu=3&periyot=aylik&{{(isset($_GET['sube'])) ? 'sube='.$isletme->id : '' }}{{(isset($_GET['yenisube'])) ? '&yenisube=1' : '' }}" class="btn btn-primary btn-rounded btn-lg"
											>ÜYELİĞİ BAŞLAT</a
										>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-30" style="display:none">
								<div class="card-box pricing-card mt-30 mb-30">
									<div class="pricing-icon">
										<img src="vendors/images/icon-online-wallet.png" alt="" />
									</div>
									<div class="price-title">KURUMSAL</div>
									<div class="pricing-price">TEKLİF AL</div>
									<div class="text">
										Fiyat bilgisi için<br />
										bize ulaşın
									</div>
									<div class="cta">
										<a href="tel:08503801035" class="btn btn-primary btn-rounded btn-lg"
											>BİZİ ARAYIN</a
										>
									</div>
								</div>
							</div>
						</div>
						<div class="row" id="yillik_uyelikler" style="display: none;">
							<div class="col-md-3 mb-30">
								<div class="card-box pricing-card-style2">
									<div class="pricing-card-header">
										<div class="left">
											<h3>BAŞLANGIÇ</h3>
											<h5>2 Ay Ücretsiz</h5>
											
										</div>
										<div class="right">
											<div class="pricing-price">{{number_format(12500,2,',','.')}} ₺<span>/YIL<br>KDV HARİÇ</span></div>
										</div>
									</div>
									<div class="pricing-card-body">
										<div class="pricing-points">
											<ul>
												<li>Randevumcepte Uygulaması</li>
												<li>3 Kullanıcı</li>
												<li>Randevu Yönetimi</li>
												<li>Müşteri Yönetimi</li>
												<li>Personel Yönetimi</li>
												<li>İşletme Yönetimi</li>
												<li>Ajanda Yönetimi</li>
												<li>Çoklu Şube Yönetimi</li>
												<li>Ön Görüşme</li>
												<li>Özel Müşteri Temsilcisi</li>
												<li>SMS Yönetimi (500 SMS hediye)</li>
											
											</ul>
										</div>
									</div>
									<div class="cta">
										@if($isletme->uyelik_turu == 1 && $isletme->uyelik_periyodu == 2 && $kalan_uyelik_suresi >= 0 && !isset($_GET['yenisube']))
										<a href="#" class="btn btn-danger btn-rounded btn-lg">MEVCUT PAKET</a>
										@else
										<a href="/isletmeyonetim/odeme?uyelikturu=1&periyot=yillik&sube={{(isset($_GET['yenisube'])) ? $_GET['yenisube'] : $isletme->id }}" class="btn btn-primary btn-rounded btn-lg"
											>ÜYELİĞİ BAŞLAT</a
										>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-30">
								<div class="card-box pricing-card-style2">
									<div class="pricing-card-header">
										<div class="left">
											<h3>STANDART</h3>
											<h5>2 Ay Ücretsiz</h5>
										</div>
										<div class="right">
											<div class="pricing-price">{{number_format(18000,2,',','.')}} ₺<span>/YIL<br>KDV HARİÇ</span></div>
										</div>
									</div>
									<div class="pricing-card-body">
										<div class="pricing-points">
											<ul>
												<li style="color:#ff0000">Başlangıç Paket Tüm Özellikleri</li>
												<li>6 Personel</li>
												<li>Web Sitesi</li>
												<li>Lokal SEO Yönetimi</li>
												<li>Paket Yönetimi / Seans Takibi</li>
												
												<li>Stok Yönetimi</li>
												<li>Kasa Yönetimi</li>
												<li>Borç Takibi</li>
												<li>Satış Yönetimi</li>
												<li>Arşiv Yönetimi</li>
												<li>Prim Hesaplama</li>
												<li>Gelişmiş İstatistik</li>
												<li>Sınırsız Randevu</li>
												<li>Sözleşme Yönetimi</li>
												<li>Dijital İmza</li>
												<li>SMS Yönetimi (1000 SMS hediye)</li>
											
											</ul>
										</div>
									</div>
									<div class="cta">
										@if($isletme->uyelik_turu == 2 && $isletme->uyelik_periyodu == 2 && $kalan_uyelik_suresi >= 0 && !isset($_GET['yenisube']))
										<a href="#" class="btn btn-danger btn-rounded btn-lg">MEVCUT PAKET</a>
										@else
										<a href="/isletmeyonetim/odeme?uyelikturu=2&periyot=yillik&sube={{(isset($_GET['yenisube'])) ? $_GET['yenisube'] : $isletme->id }}" class="btn btn-primary btn-rounded btn-lg"
											>ÜYELİĞİ BAŞLAT</a
										>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-30">
								<div class="card-box pricing-card-style2">
									<div class="pricing-card-header">
										<div class="left">
											<h3>PREMIUM</h3>
											<h5>2 Ay Ücretsiz</h5>
										</div>
										<div class="right">
											<div class="pricing-price">{{number_format(25000,2,',','.')}} ₺<span>/YIL<br>KDV HARİÇ</span></div>
										</div>
									</div>
									<div class="pricing-card-body">
										<div class="pricing-points">
											<ul>
												<li style="color:#ff0000">Standart Paket Tüm Özellikleri</li>
												<li>Santral Sistemi (5 dahili)</li>
											
												<li>Sınırsız Personel</li>
												<li>Adınıza Özel Uygulama</li>
												<li>Sosyal Medya Entegrasyonu</li>
												<li>Etklinlik Yönetimi</li>
												<li>Senet Takibi</li>
												<li>Reklam Yönetimi</li>
												<li>Sesli Randevuya Gelme Onayı</li>
												<li>Sesli Randevu Hatırlatma</li>
												<li>Sesli Anket</li>
												<li>SMS Yönetimi (Yıllık 2000 SMS Hediye)</li>
											</ul>
										</div>
									</div>
									<div class="cta">
										@if($isletme->uyelik_turu == 3 && $isletme->uyelik_periyodu == 2 && $kalan_uyelik_suresi >= 0 && !isset($_GET['yenisube']))
										<a href="#" class="btn btn-danger btn-rounded btn-lg">MEVCUT PAKET</a>
										@else
										<a href="/isletmeyonetim/odeme?uyelikturu=3&periyot=yillik&sube={{(isset($_GET['yenisube'])) ? $_GET['yenisube'] : $isletme->id }}" class="btn btn-primary btn-rounded btn-lg"
											>ÜYELİĞİ BAŞLAT</a
										>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-30">
								<div class="card-box pricing-card mt-30 mb-30">
									<div class="pricing-icon">
										<img src="vendors/images/icon-online-wallet.png" alt="" />
									</div>
									<div class="price-title">KURUMSAL</div>
									<div class="pricing-price">TEKLİF AL</div>
									<div class="text">
										Fiyat bilgisi için<br />
										bize ulaşın
									</div>
									<div class="cta">
										<a href="tel:08503801035" class="btn btn-primary btn-rounded btn-lg"
											>BİZİ ARAYIN</a
										>
									</div>
								</div>
							</div>
						</div>
					</div>
					

@endsection