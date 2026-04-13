<!DOCTYPE html>
<html>
	<head>
		<!-- Basic Page Info -->
		<meta charset="utf-8" />
		<title>Üyelik | RandevumCepte</title>
 		<link
         rel="apple-touch-icon"
         sizes="180x180"
         href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <link
         rel="icon"
         type="image/png"
         sizes="32x32"
         href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <link
         rel="icon"
         type="image/png"
         sizes="16x16"
         href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <!-- Mobile Specific Metas -->
      <meta
         name="viewport"
         content="width=device-width, initial-scale=1, maximum-scale=1"
         />
		<!-- Google Font -->
		<link
			href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
			rel="stylesheet"
		/>
		<!-- CSS -->
		<link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/core.css')}}" />
		<link
			rel="stylesheet"
			type="text/css"
			href="{{secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}"
		/>
		<link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/style.css')}}" />

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script
			async
			src="https://www.googletagmanager.com/gtag/js?id=G-GBZ3SGGX85"
		></script>
		<style>
			.isletmeliste{
				cursor: pointer;
			}
		</style>
		 
	</head>
	<body>
	 	<div class="container isletmesec" style="  position: absolute;
  left: 50%;
  top: 50%;
  -webkit-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);">
 			<div class="contact-directory-list">
 				<div class="row">
 					<div class="col-md-12 text-center">
 						<h1 style="margin-bottom: 20px;">İşleteme Seçiniz</h1>
 					</div>
 				</div>
 				<div class="row">
							<div class="col-md-4 mb-30">
								<div class="card-box pricing-card mt-30 mb-30">
									<div class="pricing-icon">
										<img src="vendors/images/icon-Cash.png" alt="" />
									</div>
									<div class="price-title">Beginner</div>
									<div class="pricing-price"><sup>$</sup>49<sub>/mo</sub></div>
									<div class="text">
										Card servicing<br />
										for 1month
									</div>
									<div class="cta">
										<a href="#" class="btn btn-primary btn-rounded btn-lg"
											>Order Now</a
										>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-30">
								<div class="card-box pricing-card mt-30 mb-30">
									<div class="pricing-icon">
										<img src="vendors/images/icon-debit.png" alt="" />
									</div>
									<div class="price-title">expert</div>
									<div class="pricing-price"><sup>$</sup>199<sub>/mo</sub></div>
									<div class="text">
										Card servicing<br />
										for 6month
									</div>
									<div class="cta">
										<a href="#" class="btn btn-primary btn-rounded btn-lg"
											>Order Now</a
										>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-30">
								<div class="card-box pricing-card mt-30 mb-30">
									<div class="pricing-icon">
										<img src="vendors/images/icon-online-wallet.png" alt="" />
									</div>
									<div class="price-title">experience</div>
									<div class="pricing-price"><sup>$</sup>599<sub>/yr</sub></div>
									<div class="text">
										Card servicing<br />
										for 1year
									</div>
									<div class="cta">
										<a href="#" class="btn btn-primary btn-rounded btn-lg"
											>Order Now</a
										>
									</div>
								</div>
							</div>
						</div>
				 
			</div>
		</div>
		 
		 
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.min.js')}}"></script>
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/process.js')}}"></script>
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/layout-settings.js')}}"></script>
		 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		 <script type="text/javascript">
		 	$(document).ready(function(){
		 		$('.isletmeliste').click(function(e){
		 			window.location.href = $(this).attr('data-value');
		 		});
		 	});
		 </script>
		 
	</body>
</html>
