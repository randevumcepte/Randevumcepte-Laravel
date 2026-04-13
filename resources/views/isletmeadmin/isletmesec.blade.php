<!DOCTYPE html>
<html>
	<head>
		<!-- Basic Page Info -->
		<meta charset="utf-8" />
		<title>Şube Seçimi | RandevumCepte</title>
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

		<link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/bootstrap.css')}}">
    	<link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/fontawesome.css')}}">
	    <!-- Theme css -->
	    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/login.css?v=1.5')}}">
		<style>
			.isletmeliste{
				cursor: pointer;
			}
		</style>
		 
	</head>
	<body>

		<section class="page-section login-page">
        <div class="full-width-screen">
            <div class="container-fluid p-0">
                <div class="particles-bg" id="particles-js">
                    <div class="content-detail">
                         <form class="login-form">
                             
                            <div class="input-control">
                            <div class="imgcontainer">
                                <img src="/public/yeni_panel/vendors/images/randevumcepte.png" alt="Randevum Cepte" class="avatar" style="width:100%; height:auto;">
                            </div>
                        	</div>
                           
                            <div class="input-control">
                                 <div class="row">
							 					<div class="col-md-12 text-center">
							 						<h1 style="margin-bottom: 20px;font-size: 25px;">İşletme Seçiniz</h1>
							 					</div>
							 				</div>
											<ul class="row" style="padding: 0;">
																
												@foreach(\App\Salonlar::whereIn('id',$isletmeler)->get() as $sube)
												 
												<li class="col-md-12 isletmeliste" data-value='https://{{$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]}}?sube={{$sube->id}}'>
																	<div class="contact-directory-box" style="min-height: 48px;">
																		<div class="contact-dire-info text-center" style="padding:10px">
																			<div class="contact-avatar">
																				<span style="margin: 0; width:30px; height: 30px; float: left;"> 
																					<img src="{{secure_asset('public/img/isletme.png')}}" alt="{{$isletme->salon_adi}}" />
																				</span>
																				<h2 style="float: left; height:30px; margin-left: 20px; font-size:18px">{{$sube->salon_adi}}</h2>
																			</div>
																			 
																			 
																		</div>
																		 
																	</div>
												</li> 
											 
												
												@endforeach

											</ul>
											<div class="division-lines">
                                    <p>VEYA</p>
                                </div>
                                <div class="login-btns">
                                    <a href="/isletmeyonetim/cikisyap" type="submit">ÇIKIŞ YAPIN</a>
                                </div>
							                                 
                                  
                             
                                 
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
   




	 
		 
		 
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.min.js')}}"></script>
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/process.js')}}"></script>
		<script src="{{secure_asset('public/yeni_panel/vendors/scripts/layout-settings.js')}}"></script>
		 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="{{secure_asset('public/yeni_login/assets/js/particles.min.js')}}"></script>
    <script src="{{secure_asset('public/yeni_login/assets/js/app.js')}}"></script>
    <!-- Theme js-->
    <script src="{{secure_asset('public/yeni_login/assets/js/script.js')}}"></script>
		 <script type="text/javascript">
		 	$(document).ready(function(){
		 		$('.isletmeliste').click(function(e){
		 			window.location.href = $(this).attr('data-value');
		 		});
		 	});
		 </script>
		 
	</body>
</html>
