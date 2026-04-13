@extends('layout.layout_sifremiunuttum')

@section('content')

  <section class="page-section login-page">
        <div class="full-width-screen">
            <div class="container-fluid p-0">
                <div class="particles-bg" id="particles-js">
                    <div class="content-detail">
                        <!-- Login form -->
                        <form id='sifremiunuttum_isletme' class="login-form" role="form" method="POST">

                            {{csrf_field() }}
                            <div class="imgcontainer">
                                <img src="/public/yeni_panel/vendors/images/randevumcepte.png" alt="Randevum Cepte" class="avatar" style="width:100%; height:auto;">
                            </div>
                            <div class="input-control">
                                <h4 style="text-align:center;">Şifremi unuttum</h4>    
                                <div class="form-group}">
                                     <label> Cep Telefonu </label>
                                     <input type="tel" placeholder="Cep Telefonu" required name="telefon" autofocus  data-inputmask =" 'mask' : '5999999999'" id="telefon" class="form-control" required>
                                </div>
                                
                                <!--end form-group-->

                                <div class="login-btns" style="text-align:center;">
                                    <button type="submit" style="width:100%;">Gönder</button>
                                    <p style="text-align:center;">VEYA</p>
                                    <a href="/isletmeyonetim/girisyap">Giriş Sayfasına Dön</a>
                                </div>

                               
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
   


@endsection
