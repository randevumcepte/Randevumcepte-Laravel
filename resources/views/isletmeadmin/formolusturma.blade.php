@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<form id="arsivformekleme">
   {{ csrf_field() }}
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <input type="hidden" name="ajanda_id" id="ajanda_id" value="0">
  <div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-12">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>

         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim/arsivyonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Arşiv Yönetimi</a>
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
   <div style="padding: 20px">
             <div class="row">
               <div class="col-md-2 col-sm-3 col-xs-3 col-3">
                 <label>Form/Sözleşme Türü</label>
                   <select name="formtaslaklari" id="formtaslaklari" class="form-control custom-select2" style="width: 100%;">
                                                <option value="0">Seçiniz</option>
                                                <option value="1">Kimyasal Peeling Onam Formu</option>
                                                <option value="2">Dövme Silme Onam Formu</option>
                                                <option value="3">Mikropigmentasyon Uygulaması Onam Formu</option>
                                                <option value="4">Lazer Epilasyon Onam Formu</option>
                                                <option value="5">Dermoroller Onam Formu</option>
                                                <option value="6">Bölgesel İncelme Onam Formu</option>
                                                <option value="7">Cilt Üzerinde Kullanılan Lazer Onam Formu</option>
                                             </select>
               </div>
                 <div class="col-md-2 col-sm-3 col-xs-3 col-3">
                 <label>Müşteri</label>
                   <select name="formmusterisec" id="formmusterisec" class="form-control custom-select2" style="width: 100%;">
                        <option value="0">Seçiniz</option>
                      @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                        <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                      @endforeach
                    </select>
               </div>
               <div class="col-md-2 col-xs-3 col-sm-3 col-3">
                 <label>Cep Telefon</label>
                 <input class="form-control" type="tel" name="formmustericeptelefon" id="formmustericeptelefon">
               </div>
               <div class="col-md-2 col-xs-3 col-sm-3 col-3">
                 <label>TC Kimlik No</label>
                 <input class="form-control" type="tel" name="formmusterikimlikno" id="formmusterikimlikno">
               </div>
               
                 <div class="col-md-2 col-xs-2 col-sm-2 col-2">
                 <label>Cinsiyet</label>
                  <select name="formmustericinsiyet" id="formmustericinsiyet" class="form-control">
                                 <option value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                              </select>
               </div>
                  <div class="col-md-2 col-xs-2 col-sm-2 col-2">
                 <label>Doğum Tarihi</label>
                  <input type="text" name="formmusteriyas" id='formmusteriyas' required class="form-control date-picker"  value="" autocomplete="off">
               </div>

            
             </div>
             <div class="row">
               <div class="col-md-2">
                         <label>İşlemi Yapan Personel</label>
                   <select name="formpersonelsec" id="formpersonelsec" class="form-control custom-select2" style="width: 100%;">
                         <option>Seçiniz</option>
                                                   @if(Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::guard('isletmeyonetim')->user()->personel_id}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option> 
                                                   @else
                                                      @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)

                                                      <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                                      @endforeach
                                                      
                                                   @endif
                    </select>
               </div>
                    <div class="col-md-2 col-xs-3 col-sm-3 col-3">
                 <label>Cep Telefon</label>
                 <input class="form-control" type="tel" name="formmpersonelceptelefon" id="formpersonelceptelefon">
               </div>
             </div>
             <div class="row " style="justify-content: flex-end; padding-top: 20px;" >
             
                <div class="col-md-2 col-xs-6 col-sm-6 col-6">
                 <button type="submit" class="btn btn-primary btn-lg btn-block"> Gönder</button>
               </div>
             </div>
        </div>    


<div id="hata"></div>

</div>
</form>





@endsection