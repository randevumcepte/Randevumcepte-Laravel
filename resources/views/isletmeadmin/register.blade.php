@extends('layout.layout_register')

@section('content')
 <section class="block">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 col-lg-5 col-md-6 col-sm-8">
                           <form class="form-clearfix" method="POST" action="{{ route('register') }}">
                            {{ csrf_field() }}

                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                     
                                    <input name="name" type="text" class="form-control" id="name" placeholder="Ad Soyad..." required>
                                     @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                     
                                    <input name="email" type="email" class="form-control" id="email" placeholder="E-posta..." >
                                     @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                </div>
                                 <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                     
                                    <input name="text" maxlength="10" pattern="[0-9]*"  type="email" class="form-control" id="cep_telefon" placeholder="Cep Telefonu (Başında 0 olmadan 5XXXXXXXXX şeklinde..." >
                                     @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    
                                    <input name="password" type="password" class="form-control" id="password" placeholder="Şifre..." required>
                                     @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                     @endif
                                </div>
                                <!--end form-group-->
                                <div class="form-group">
                                    
                                    <input name="password_confirmation" type="password" class="form-control" id="password-confirm" placeholder="Şifre tekrar..." required>
                                </div>
                                <!--end form-group-->
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <label>
                                        <input type="checkbox" name="newsletter" value="1">
                                        Receive Newsletter
                                    </label>
                                    <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                                </div>
                            </form>
                            <hr>
                            <p>
                                By clicking "Register" button, you agree with our <a href="#" class="link">Terms & Conditions.</a>
                            </p>
                        </div>
                        <!--end col-md-6-->
                    </div>
                    <!--end row-->
                </div>
                <!--end container-->
            </section>
@endsection
