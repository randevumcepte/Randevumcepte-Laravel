@extends('layout.layout_satisortakligilogin')

@section('content')

  <section class="page-section login-page">
        <div class="full-width-screen">
            <div class="container-fluid p-0">
                <div class="particles-bg" id="particles-js">
                    <div class="content-detail">
                        <!-- Login form -->
                        <form class="login-form" role="form" method="POST" action="{{ route('satisortakligi.login.submit') }}">
                            <input type="hidden" id="onesignalid" name="bildirimid">
                            {{csrf_field() }}
                            <div class="input-control">
                            <div class="imgcontainer">
                                <img src="/public/yeni_panel/vendors/images/randevumcepte.png" alt="Randevum Cepte" class="avatar" style="width:100%; height:auto;">
                            </div>
                        </div>
                           
                            <div class="input-control">
                                @if(session()->get('error') != '')
                                 <p style='color: #721c24;
                                    background-color: #f8d7da;
                                        border-color: #f5c6cb;font-size:16px;padding:5px'>
                                        {!! session()->get('error') !!}
                                        @endif
                            </p> 
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <input type="text" placeholder="Cep Telefonu" name="email" value="{{ old('email') }}" required autofocus>
                                    
                                   
                                    
                                </div>
                                
                                <!--end form-group-->
                                <span class="password-field-show{{ $errors->has('password') ? ' has-error' : '' }}">
                                     
                                    <input type="password" placeholder="Şifre" name="password"
                                        class="password-field" value="" required>
                                    <span data-toggle=".password-field"
                                        class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                    @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </span>

                               
                                <label class="label-container" >Beni Hatırla
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <span class="checkmark"></span>
                                </label>
                                <span class="psw"><a href="/isletmeyonetim/sifremiunuttum" class="forgot-btn">Şifremi Unuttum</a></span>
                                <div class="login-btns">
                                    <button type="submit" style="width:100%;">GİRİŞ YAP</button>
                                </div>
                                
                                 
                                  
                             
                                 
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
   


@endsection
