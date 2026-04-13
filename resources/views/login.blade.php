@extends('layout.layout_login')

@section('content')
  <section class="block">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <form class="form-clearfix" role="form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}
                         <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                  
                                    <input name="email" style="border-radius: 60px" type="email" class="form-control" id="email" placeholder="E-posta..." required  value="{{ old('email') }}" required autofocus> 
                                    @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                     
                                    <input name="password"  style="border-radius: 60px" type="password" class="form-control" id="password" placeholder="Şifre..." required>
                                      @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        Beni Hatırla
                                    </label>

                                    <button type="submit" class="btn btn-primary btn-rounded">Giriş Yap</button>
                                    <a href="/register" class="btn btn-primary btn-rounded">Kayıt Ol</a>
                                </div>
                            </form>
                            <hr>
                            <p>
                                  <a href="#" class="link">Şifremi Unuttum.</a>
                            </p>
                        </div>
                        <!--end col-md-6-->
                    </div>
                    <!--end row-->
                </div>
                <!--end container-->
            </section>


@endsection
