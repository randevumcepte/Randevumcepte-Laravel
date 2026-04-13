@extends('layout.layoutpagescancel')
@section('content')
  <section class="block">
  	<form id="epostaiptaltalebi" method="get">
                <div class="container" style="text-align: justify;">

                    <h2>E-posta İptal Formu</h2>
                    <p>Bu işlem sonrasında aşağıdaki bilgileri yer alan üye e-posta ile kampanya bildirimi gönderim listemizden çıkartılacaktır </p>
                   
                    <p><strong>Ad Soyad : </strong>
                    	@if(\App\User::where('email',$eposta)->first() !== null)
                    		{{\App\User::where('email',$eposta)->value('name')}}
                    	@else 
                    	    Kayıtlı Kullanıcı Bulunamadı!
                    	    @endif
                    </p>
                    <p><strong>E-posta : </strong>
                    	@if(\App\User::where('email',$eposta)->first() !== null)
                    		{{\App\User::where('email',$eposta)->value('email')}}
                    	@else 
                    	    Kayıtlı Kullanıcı Bulunamadı!
                    	  @endif
                    </p>
                    <p style="border-bottom:1px solid #e4e4e2; width: 100%"></p>
                    <p>E-posta kampanya gönderim listemizden neden çıkmak istiyorsunuz?</p>

        <figure>
                                            <label>
                                                <input type="radio" name="neden" value="1" required checked>
                                                Çok fazla gönderim yapılıyor
                                            </label>
                                            <label>
                                                <input type="radio" name="neden" value="2" required>
                                                Gönderimlerinizle ilgilenmiyorum
                                            </label>
                                            <label>
                                                <input type="radio" name="neden" value="3" required>
                                                Diğer
                                            </label>
                                        </figure>
                             <div class="form-group">
                             	<textarea name="digerneden" class="form-control" placeholder="Diğer nedeni belirtiniz." required=""></textarea>
                             </div>
                             <input type="hidden" name="eposta" id="eposta" value="{{$eposta}}">
                             <div class="form-group">
                             	<button type="submit" class="btn btn-primary btn-rounded">ONAYLA</button>
                             </div>

 
                    </div></form>
                    <div id="hata"></div>
                </section>
@endsection