

@extends('layout.layout_profil')
@section('content')
           
           <section class="block">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12" >

                             <ul class="nav nav-pills" id="myTab-pills" role="tablist" style="text-align: center;">
                                    <li class="nav-item">
                                        <a class="nav-link active icon" href="/profilim"><i class="fa fa-user" style="color:white"></i>Profilim</a>
                                    </li>
                                    <li class="nav-item">
                                       <a class="nav-link icon" href="/randevularim">
                                    <i class="fa fa-heart"></i>Randevularım
                                        </a>
                                    </li>
                                       <li class="nav-item">
                                         <a class="nav-link icon" href="/ayarlarim">
                                             <i class="fa fa-recycle"></i>Ayarlarım
                                        </a> 
                                    </li>
                                </ul>
                        
                        </div>
                        <!--end col-md-3-->
                        <div class="col-md-12">
                            <form class="form" enctype="multipart/form-data" method="POST" value="{{ csrf_token() }}" action="{{route('musteri_profil_guncelleme')}}">
                                 {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-9"  style="margin-top: 30px">
                                        <h2 style="margin-bottom: 30px">Profil Bilgilerim</h2>
                                        
                                             
                                          <div class="col-md-6" style="float: left;">     
                                            <div class="form-group">
                                                        <label for="name" class="col-form-label required">Ad Soyad</label>
                                                        <input style="border-radius: 60px" name="name" type="text" class="form-control" id="name" placeholder="Your Name" value="{{Auth::user()->name}}" required>
                                            </div>
                                                
                                            <div class="form-group">
                                                <label for="cep_telofon" class="col-form-label required">Cep Telefonu</label>
                                                <input style="border-radius: 60px"  name="cep_telofon" type="number" class="form-control" id="cep_telofon" required placeholder="Cep Telefonu" value="{{Auth::user()->cep_telefon}}">
                                            </div>
                                             <div class="form-group">
                                                        <label for="email" class="col-form-label required">E-posta</label>
                                                        <input style="border-radius: 60px"  name="email" type="text" class="form-control" id="email" placeholder="E-posta" value="{{Auth::user()->email}}" required>
                                            </div>
                                            <!--end form-group-->
                                             
                                        </div>
                                        <div class="col-md-6" style="float: left;">
                                            
                                                
                                            <div class="form-group">
                                                <label for="ev_telofon" class="col-form-label">Ev Telefonu</label>
                                                <input style="border-radius: 60px"  name="ev_telofon" type="number" class="form-control" id="ev_telofon" placeholder="Ev Telefonu" value="{{Auth::user()->ev_telefon}}">
                                            </div>
                                             <div class="form-group">
                                                <label for="cep_telofon" class="col-form-label ">Doğum Tarihi</label>
                                                <input style="border-radius: 60px"  name="dogum_tarihi" type="date" class="form-control" id="dogum_tarihi" placeholder="Doğum Tarihi" value="{{Auth::user()->dogum_tarihi}}">
                                            </div>
                                            <div class="form-group">
                                                    <label for="cinsiyet" class="col-form-label ">Cinsiyet</label>
                                                     <select name="cinsiyet" style="border-radius: 60px"  id="cinsiyet" data-placeholder="Cinsiyet Seçin"> 
                                                     @if(Auth::user()->cinsiyet==1)
                                                      <option value="0">Kadın</option>
                                                       
                                                      <option value="1" selected="true">
                                                         Erkek
                                                      </option>
                                                      @else
                                                        <option value="0" selected="true">Kadın</option>
                                                       
                                                      <option value="1">
                                                         Erkek
                                                      </option>
                                                      @endif
                                                     
                                                   
                                                      
                                                
                                            </select>

                                            </div>
                                           
                                        </div>
                                            
                                    </div>
                                    <!--end col-md-8-->
                                    <div class="col-md-3"  style="margin-top: 30px">
                                        <div class="profile-image">
                                            <div class="image background-image">
                                                @if(Auth::user()->profil_resim != '' || Auth::user()->profil_resim != null)
                                                 <img src="{{secure_asset(Auth::user()->profil_resim)}}" alt="">
                                              
                                                @else
                                                 <img src="{{secure_asset('public/img/author-09.jpg')}}" alt="">
                                                @endif

                                            </div>
                                            <div class="single-file-input">
                                                <input type="file" id="profil_resim" name="profil_resim">
                                                <div class="btn btn-framed btn-primary small btn-rounded">Resim Yükle</div>
                                                 <a href="{{route('musteri_profil_resmi_kaldirma')}}" class="btn btn-framed btn-primary small btn-rounded">Resmi Sil</a>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                                <div class="row" style="position: relative;float: left;width: 100%;text-align: left;margin-top:20px">
                                    <div class="col-md-12">
                                      <div class="col-md-12">
                                       <div class="form-group">
                                                  <button id="profilbilgiguncelle" type="submit" class="btn btn-primary btn-rounded" style="width: 100%">Bilgileri Güncelle</button>
                                            </div>
                                          </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end row-->
                </div>
                <!--end container-->
            </section>
          
@endsection
        