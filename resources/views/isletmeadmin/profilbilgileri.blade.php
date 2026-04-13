@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
  <div class="page-header">
                  <div class="row">
                     <div class="col-md-6 col-sm-12">
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
<div class="row">
						<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 mb-30">
							<div class="pd-20 card-box height-100-p">
								<div class="profile-photo">
									<a
										href="#"
										class="edit-avatar" onclick="thisFileUpload();"
										><i class="fa fa-pencil"></i
									></a>
									<img
										id="mevcut_yetkili_profil_resmi"
										src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
										alt=""
										class="avatar-photo" style="object-fit: cover; width: 160px; height: 160px;"
									/>
									<div
										class="modal fade"
										id="modal"
										tabindex="-1"
										role="dialog"
										aria-labelledby="modalLabel"
										aria-hidden="true"
									>
										<div
											class="modal-dialog"
											role="document"
										>
											<div class="modal-content">
												<div class="modal-body pd-5">
													<div class="img-container">
														<img														
															src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
															alt="Avatar"
														/>
														<input type="file" id="yetkili_profil_resmi" style="display:none;" />
													</div>
												</div>
												<div class="modal-footer" style="display: block;">

													<div class="row">
													 
														
														 
														<div class="col-6 col-xs-6 col-sm-6">
															<button id="button" name="button" value="Upload" class="btn btn-primary btn-lg btn-block" onclick="thisFileUpload();"><i class="fa fa-upload"></i> Fotoğraf Yükle</button>
														</div>
														<div class="col-6 col-xs-6 col-sm-6">
															<button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
																Kapat
															</button>
														</div>
													</div>
														 
													   
															
														
												</form>
												</div>
											</div>
										</div>
									</div>
									<button id="crop_modal_ac"  data-toggle="modal" data-target="#crop_modal" style="display:none">	modal aç</button>							

									<div
										class="modal fade"
										id="crop_modal"
										tabindex="-1"
										role="dialog"
										aria-labelledby="modalLabel"
										aria-hidden="true"
									>
										<div
											class="modal-dialog"
											role="document"
										>
											<div class="modal-content">
												<div class="modal-body pd-5">
													<div class="img-container">
					                            <div class="row">
					                                <div class="col-md-12">  
					                                    <!--  default image where we will set the src via jquery-->
					                                    <img id="croppedimg" src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
					                                </div>
					                                 
					                            </div>
					                        </div>
												</div>

												<div class="modal-footer" style="display: block;">

													<div class="row">
													 
														
														 
														<div class="col-6 col-xs-6 col-sm-6">
															<button id="crop" class="btn btn-primary btn-lg btn-block">Kırp</button>
														</div>
														<div class="col-6 col-xs-6 col-sm-6">
															<button type="button" id="crop_modal_kapat" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
																Kapat
															</button>
														</div>
													</div>
														 
													   
															
														
												</form>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<h5 class="text-center h5 mb-0">{{Auth::guard('isletmeyonetim')->user()->name}}</h5>
								<div class="profile-info">
									<h5 class="mb-20 h5 text-blue">İletişim Bilgileri</h5>
								<ul>
										<li>
											<span>E-Posta Adresi:</span>
											{{Auth::guard('isletmeyonetim')->user()->email}}
										</li>
										<li>
											<span>Telefon Numarası:</span>
											{{Auth::guard('isletmeyonetim')->user()->gsm1}}
										</li>
										<li>
											<span>Ülke:</span>
											Türkiye
										</li>
										
									</ul>
								</div>
								</div>
							</div><div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mb-30">
							<div class="card-box height-100-p overflow-hidden">
								<div class="profile-tab height-100-p">
									<div class="tab height-100-p">
										<ul class="nav nav-tabs customtab" role="tablist">
										
											<li class="nav-item">
												<a
													class="nav-link active"
													data-toggle="tab"
													href="#setting"
													role="tab"
													>Ayarlar</a
												>
											</li>
										</ul>
										<div class="tab-content">
											
											<!-- Setting Tab start -->
											<div
												class="tab-pane fade show active"
												id="setting"
												role="tabpanel"
											>
												<div class="profile-setting">
													<form id="yetkilibilgileri" method="POST">
														{{ csrf_field() }}
														<ul class="profile-edit-list row">
															<li class="weight-500 col-md-6">
															
																<div class="form-group">
																	<label>Ad Soyad</label>
																	<input
																		class="form-control form-control-lg"
																		type="text" name="name" required
																		value="{{Auth::guard('isletmeyonetim')->user()->name}}"
																	/>
																</div>
																
																<div class="form-group">
																	<label>Ünvan</label>
																	<input
																		class="form-control form-control-lg"
																		type="text" name="unvan" value="{{Auth::guard('isletmeyonetim')->user()->unvan}}"
																	/>
																</div>
																
																<div class="form-group">
																	<label>Cinsiyet</label>
																	<div class="d-flex">
																		<div
																			class="custom-control custom-radio mb-5 mr-20"
																		>
																			<input
																				type="radio"
																				id="customRadio4"
																				name="cinsiyet"
																				class="custom-control-input" value="1" 
																				{{(Auth::guard('isletmeyonetim')->user()->cinsiyet==1) ? 'checked' : ''}}
																			/>
																			<label
																				class="custom-control-label weight-400"
																				for="customRadio4"
																				>Erkek</label
																			>
																		</div>
																		<div
																			class="custom-control custom-radio mb-5"
																		>
																			<input
																				type="radio"
																				id="customRadio5"
																				name="cinsiyet"
																				class="custom-control-input" value="0"
																				{{(Auth::guard('isletmeyonetim')->user()->cinsiyet==0) ? 'checked' : ''}}
																			/>
																			<label
																				class="custom-control-label weight-400"
																				for="customRadio5"
																				>Kadın</label
																			>
																		</div>
																	</div>
																</div>
														
															
															</li>
															<li class="weight-500 col-md-6">
																 
																<div class="form-group">
																	<label>E-Posta Adresi</label>
																	<input
																		class="form-control form-control-lg"
																		type="email" name="email"
																		value="{{Auth::guard('isletmeyonetim')->user()->email}}"
																	/>
																</div>
																	<div class="form-group">
																	<label>Telefon Numarası</label>
																	<input
																		class="form-control form-control-lg"
																		type="text" required name="gsm1"
																		value="{{Auth::guard('isletmeyonetim')->user()->gsm1}}"
																	/>
																</div>
																
																	<div class="form-group">
																	<label>Şifre Değiştir</label>
																	<input
																		class="form-control form-control-lg"
																		type="password" name="password"
																	/>
																</div>
															
																
																
															</li>
															<div class="form-group mb-0" style="margin-left: 265px">
																	<input
																		type="submit"
																		class="btn btn-success btn-lg btn-block"
																		value="Bilgileri Güncelle"

																	/>
																</div>
														</ul>
															
													</form>
												</div>
											</div>
											<!-- Setting Tab End -->
										</div>
									</div>
								</div>
							</div>
						</div>
					
<script>
        function thisFileUpload() {
            document.getElementById("yetkili_profil_resmi").click();
        };
</script>
@endsection()