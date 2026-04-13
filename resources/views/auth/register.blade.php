@extends('layout.layout_register')

@section('content')
 <section class="block">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                           <form class="form-clearfix" method="POST" action="{{ route('register') }}">
                            {{ csrf_field() }}

                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                     
                                    <input name="name" style="border-radius: 60px" type="text" class="form-control" id="name" placeholder="Ad Soyad..." required>
                                     @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                     
                                    <input name="email" style="border-radius: 60px" type="email" class="form-control" id="email" placeholder="E-posta...">
                                     @if ($errors->has('email'))
                                    <span class="help-block" style="margin-left: 10px;color:#FF4E00">
                                        @if($errors->first('email') == 'validation.unique')
                                            Girdiğiniz e-posta adresi sistemimizde kayıtlıdır. Giriş yapmak için <a href="/login" style="color: #FF4E00">tıklayınız</a>
                                        @else
                                        <strong>{{ $errors->first('email') }}</strong>
                                        @endif
                                    </span>
                                @endif
                                </div>
                                  <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                     
                                    <input maxlength="10" required pattern="[0-9]*"  type="text" class="form-control" name="cep_telefon" id="cep_telefon" placeholder="Cep Telefonu (Başında 0 olmadan 5XXXXXXXXX şeklinde...)" >
                                     @if ($errors->has('cep_telefon')== 'validation.unique')
                                    <span class="help-block">
                                         @if($errors->first('cep_telefon') == 'validation.unique')
                                            Girdiğiniz telefon sistemimizde kayıtlıdır. Giriş yapmak için <a href="/login" style="color: #FF4E00">tıklayınız</a>
                                        @elseif($errors->first('cep_telefon') == 'validation.regex')
                                            Lütfen geçerli bir cep telefon numarası giriniz
                                        @else
                                        <strong>{{ $errors->first('cep_telefon') }}</strong>
                                        @endif
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    
                                    <input name="password" style="border-radius: 60px" type="password" class="form-control" id="password" placeholder="Şifre..." required>

                                     @if ($errors->has('password') == 'validation.confirmed')
                                    <span class="help-block">
                                        @if($errors->first('password')=='validation.confirmed')
                                        Girdiğiniz şifreler eşleşmemektedir. Lütfen yeniden deneyiniz.
                                        @else
                                        <strong>{{ $errors->first('password') }}</strong>
                                        @endif
                                    </span>
                                     @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group">
                                    
                                    <input name="password_confirmation" style="border-radius: 60px" type="password" class="form-control" id="password-confirm" placeholder="Şifre tekrar..." required>
                                </div>
                                <!--end form-group-->
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="kosulkabul" checked>
                                        <a href="/kullanici-sozlesmesi">Kullanım</a> ve <a href="/gizlilik-politikasi" target="_blank">gizlilik koşullarını</a> kabul ediyorum.
                                    </label>
                                  
                                </div>
                                <div class="form-group">
                                      <button id="kayitol" type="submit" class="btn btn-primary btn-rounded" style="width: 100%" >Kayıt Ol</button>
                                </div>
                            </form>
                           
                        </div>
                        <!--end col-md-6-->
                    </div>
                    <!--end row-->
                </div>
                <!--end container-->
            </section>
@endsection
