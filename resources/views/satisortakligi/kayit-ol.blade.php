@extends('layout.layout_satisortakligilogin')

@section('content')

  <section class="page-section login-page">
        <div class="full-width-screen">
            <div class="container-fluid p-0">
                <div class="particles-bg" id="particles-js">
                    <div class="content-detail">
                        <!-- Login form -->
                        <form class="login-form" id="satisortakligikayitformu" >
                            <input type="hidden" id="onesignalid" name="bildirimid">
                            {{csrf_field() }}
                            <div class="input-control">
                            <div class="imgcontainer">
                                <img src="/public/yeni_panel/vendors/images/randevumcepte.png" alt="Randevum Cepte" class="avatar" style="width:100%; height:auto;">
                                <h5 style="margin-top: 20px;">Ortaklığı Paneli Kullanıcı Kaydı</h5>
                            </div>
                        </div>
                           
                            <div class="input-control">
                                @if(session()->get('error') != '')
                                 <p style='color: #721c24;
                                    background-color: #f8d7da;
                                        border-color: #f5c6cb;font-size:16px;padding:5px'>
                                        {!! session()->get('error') !!}
                                        
                            </p> 
                            @endif
                                 
                                <input type="text" required placeholder="Adınız*" name="name" value=""  autofocus>
                                <input type="text" required placeholder="Soyadınız*" name="surname" value=""  autofocus>
                                <input type="tel" required placeholder="Telefon*" data-inputmask =" 'mask' : '5999999999'" name="phone" value=""  autofocus>   
                                <input type="email"  placeholder="Email" name="email" value=""  autofocus>   
                                
                                <label class="label-container"><a target="_blank" href="/public/egitimdosyasi/Randevum Cepte Satış Ortaklığı Anlaşması Şart ve Koşulları.pdf">RandevumCepte Satış Ortaklığı Anlaşması Şart ve Koşulları</a>'nı kabul ediyorum.
                                    <input type="checkbox" required name="sozlesme_kabul">
                                    <span class="checkmark"></span>
                                </label>                             
                                 
                            <div class="login-btns">
                                    <button type="submit" style="width:100%;">KAYIT OL</button>
                            </div>
                                 
                                 
                                 
                                  
                             
                                 
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
   


@endsection
