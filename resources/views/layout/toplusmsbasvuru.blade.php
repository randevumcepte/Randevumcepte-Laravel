@extends('layout.layout_isletmeadmin')
@section('content')
<div class="main-content container-fluid">
          <h1 class="display-heading text-center">SMS Paketleri</h1>
         
          <div class="row pricing-tables">
          	@foreach($smspaketler as $smspaketleri)
            <div class="col-md-3">
              <div class="pricing-table pricing-table-color pricing-table-{{$smspaketleri->class}}">
                 
                <div class="pricing-table-title"><strong style="font-size:30px">{{$smspaketleri->sms_adet}} SMS</strong></div>
                <div class="panel-divider panel-divider-xl"></div>
                <div class="pricing-table-price"><span class="value">{{$smspaketleri->ucret}} <span class="simge-tl">&#8378;</span></span></div>
                <ul class="pricing-table-features"> 
                  <li>Süresiz Kullanım Hakkı</li>
                  <li>Garantili Sms Gönderimi</li>
                  <li>Başlıklı Gönderim</li>
                  <li>7/24 Teknik Destek</li>
                </ul>
                @if(\App\SMSBilgiler::where('salon_id',Auth::user()->salon_id)->count() != 0)
                <a href="/isletmeyonetim/smspaketsatinal/{{$smspaketleri->id}}"  class="btn btn-{{$smspaketleri->class}} btn-outline">SATIN AL</a>
                @endif
              </div>
            </div>
             @endforeach
          </div>
          <div class="row">
          	<div class="col-md-12 text-center">
          		 @if(\App\SMSBilgiler::where('salon_id',Auth::user()->salon_id)->count() == 0)
          		    <h1 class="display-heading text-center">SMS Gönderimi İçin İstenen Belgeler</h1>

          		     
          		    	<p>Şirket adına imza yetkisi olan kişinin, T.C. kimpk belgesi fotokopisi,</p> 
							<p>Ticaret Sicil Gazetesi örneği veya Ticaret Sicil Kaydı</p>
							<p>Vergi Levhası</p>
							<p>İmza Sirküsü</p>
						<p>Yetki Belgesi</p>
						<p>Unvanı ispatlayıcı diğer resmi belgeler (oda kayıt belgesi vb.).</p>
						<p>SMS başvuru formu (imzalı ve kaşeli).<a href="/public/Alfanumerik-Başlık-ve-Aktivasyon-Sözleşmesi.docx"> İndir</a></p>
          		     
          		    <p>Yukarıdaki belgeleri <a href="mailto:info@randevumcepte.com.tr">info@randevumcepte.com.tr</a> adresine gönderiniz.</p>
         
          		 @endif

          	</div>
          </div>
        </div>

@endsection