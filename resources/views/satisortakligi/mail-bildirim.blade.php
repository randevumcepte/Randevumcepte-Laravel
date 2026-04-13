<p>{{$bildirim}}</p>
<br>
<p>Ad Soyad : {{$satis_ortagi->ad_soyad}}</p>
<p>Telefon : {{$satis_ortagi->telefon}}</p>
<p>E-mail : {{$satis_ortagi->email}}</p>
@if($hesap_silinsin != '')
<p >Veriler ve hesap silinsin : {{$hesap_silinsin}}</p>
@endif