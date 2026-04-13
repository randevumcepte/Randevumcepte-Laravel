@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<style>
    .whatsapp-container {
    text-align: center;
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

#qr-code-container {
    margin-top: 20px;
}

#status-message {
    margin-top: 20px;
    color: #333;
}
</style>
<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-6">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>

         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">
                  {{$sayfa_baslik}}
               </li>
            </ol>
         </nav>
      </div>


    
   </div>
</div>
<div class="card-box mb-30">
<div id="qrcode"></div>
    <script>
        $(document).ready(function() {
            var qrCodeUrl = 'https://api.whatsapp.com/send?phone=YOUR_PHONE_NUMBER';
            QRCode.toCanvas(document.getElementById('qrcode'), qrCodeUrl, function (error) {
                if (error) console.error(error);
                console.log('QR kodu başarıyla oluşturuldu!');
            });
        });
    

             
   
</div>
<style type="text/css"></style>
<div id="hata"></div>

</div>



@endsection