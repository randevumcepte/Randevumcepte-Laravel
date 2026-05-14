@extends('layout.layout_login')

@section('content')
  <section class="block">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <form class="form-clearfix" role="form" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}
                         <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">

                                    <input name="name" style="border-radius: 60px" type="text" class="form-control" id="name" placeholder="Ad Soyad..." required value="{{ old('name') }}" autofocus>
                                    @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                                    <input name="email" style="border-radius: 60px" type="email" class="form-control" id="email" placeholder="E-posta..." required value="{{ old('email') }}">
                                    @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('cep_telefon') ? ' has-error' : '' }}">

                                    <input name="cep_telefon" maxlength="10" pattern="[0-9]*" style="border-radius: 60px" type="text" class="form-control" id="cep_telefon" required placeholder="Cep Telefonu (5XXXXXXXXX)..." value="{{ old('cep_telefon') }}">
                                    @if ($errors->has('cep_telefon'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('cep_telefon') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">

                                    <input name="password" style="border-radius: 60px" type="password" class="form-control" id="password" placeholder="Şifre..." required>
                                    @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group">

                                    <input name="password_confirmation" style="border-radius: 60px" type="password" class="form-control" id="password-confirm" placeholder="Şifre tekrar..." required>
                                </div>
                                <!--end form-group-->
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <label>
                                        <input type="checkbox" name="kosulkabul" checked>
                                        <a href="/gizlilik-politikasi" target="_blank">Koşulları</a> kabul ediyorum.
                                    </label>

                                    <button type="submit" class="btn btn-primary btn-rounded">Kayıt Ol</button>
                                </div>
                            </form>
                            <hr>
                            <p>
                                  <a href="/login" class="link">Zaten üye misiniz? Giriş Yap</a>
                            </p>
                        </div>
                        <!--end col-md-6-->
                    </div>
                    <!--end row-->
                </div>
                <!--end container-->
            </section>


@endsection
