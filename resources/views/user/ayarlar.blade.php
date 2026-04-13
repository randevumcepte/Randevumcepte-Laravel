@extends('layout.layout_profil')
@section('content')

 <section class="block">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12" >

                             <ul class="nav nav-pills" id="myTab-pills" role="tablist" style="text-align: center;">
                                    <li class="nav-item">
                                        <a class="nav-link icon" href="/profilim"><i class="fa fa-user" style="color:white"></i>Profilim</a>
                                    </li>
                                    <li class="nav-item">
                                       <a class="nav-link icon" href="/randevularim">
                                    <i class="fa fa-heart"></i>Randevularım
                                        </a>
                                    </li>
                                   
                                     <li class="nav-item">
                                         <a class="nav-link active icon" href="/ayarlarim">
                                             <i class="fa fa-recycle"></i>Ayarlarım
                                        </a> 
                                    </li>
                                </ul>
                        
                        </div>
                        <div class="col-md-2">  </div>
                        <div class="col-md-8">
                            
                                    <h2>Şifre Değiştir</h2>
                                    <div class="sifredegistirmealani" style="border-radius: 30px;">
                                        <form  method="POST" id='musteri_sifre_degistir' action="{{ route('sifredegistir') }}">
                                         {{ csrf_field() }}
 
                                            <div class="form-group">
                             
 
                                                 <div class="col-md-12">
                                                    <input id="current-password" style="border-radius: 60px" type="password" class="form-control" name="current-password" placeholder="Mevcut şifreniz..." required>
              
                                                     @if ($errors->has('current-password'))
                                                    <span class="help-block">
                                                     <strong>{{ $errors->first('current-password') }}</strong>
                                                     </span>
                                                    @endif
                                                </div>
                                            </div>
 
                                            <div class="form-group">
                             
 
                                            <div class="col-md-12">
                                                <input id="new-password" style="border-radius: 60px" type="password" class="form-control" name="new-password" placeholder="Yeni şifreniz..." required>
 
                                                     @if ($errors->has('new-password'))
                                                        <span class="help-block">
                                                        <strong>{{ $errors->first('new-password') }}</strong>
                                                        </span>
                                                    @endif
                                            </div>
                                        </div>
 
                                         <div class="form-group">
                            
 
                                            <div class="col-md-12">
                                                <input id="new-password-confirm" style="border-radius: 60px" type="password" class="form-control" name="new-password_confirmation" placeholder="Yeni şifreniz (tekrar)" required>
                                            </div>
                                         </div>
 
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <button type="submit" style="border-radius: 60px" class="btn btn-primary">
                                                Şifreyi Değiştir
                                                </button>
                                            </div>
                                        </div>
                                     </form>
                                    </div>
                                
                              
                        </div>
                         <div class="col-md-2">  </div>
                  
                </div>
                
            </section>
          



@endsection