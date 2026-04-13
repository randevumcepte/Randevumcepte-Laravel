@extends('layout.layout_login')

@section('content')
  <section class="block">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <form class="form-clearfix" role="form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}
                         <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group{{ $errors->has('cep_telefon') ? ' has-error' : '' }}">
                                  
                                    <input name="cep_telefon" style="border-radius: 60px" type="text" class="form-control" id="cep_telefon" placeholder="Cep telefon (başında 0 olmadan 5XXXXXXXXX)" maxlength="10" required  value="{{ old('cep_telefon') }}" required autofocus> 
                                   
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                     
                                    <input name="password"  style="border-radius: 60px" type="password" class="form-control" id="password" placeholder="Şifre..." required>
                                   
                                </div>
                                   @if ($errors->has('cep_telefon') || $errors->has('password'))
                                <div class="form-group">

                                    <span class="help-block" style="border-radius: 10px;  background-color: #dc3545;color:white;padding: 10px;float: left;position: relative;margin-bottom: 10px">
                                        <div style="width: 10%;float: left;">
                                            <img src="{{secure_asset('public/img/error.png')}}" width="20" height="20" alt="Giriş Hatası">
                                        </div>
                                        <div style="width: 90%;float: left;">
                                        Giriş yapılamadı : Kullanıcı bilgileriniz sistemdekiler ile eşleşmemektedir.
                                    </div>
                                         
                                    </span>

                               
                                </div>
                                 @endif
                                <div class="form-group">
                                    <button type="submit" id="girisyap" class="btn btn-primary btn-rounded" style="width: 100%">Giriş Yap</button>
                                </div>
                                <div class="form-group" style="text-align: center; display: none;">
                                    <a href="#" class="link">Şifrenizi mi unuttunuz.</a>
                                </div>
                                <div class="form-group" style="text-align: center;">
                                    Bir hesabınız yok mu? <br/>
                                    <a style="color:#ff4e00;font-size: 16px" href="/register">Kayıt Ol</a>
                                </div>
                                <!--end form-group-->
                                
                            </form>
                           
                        </div>
                        <!--end col-md-6-->
                    </div>
                    <!--end row-->
                </div>
                <!--end container-->
            </section>


@endsection
