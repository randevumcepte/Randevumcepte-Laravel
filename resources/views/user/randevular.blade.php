@extends('layout.layout_profil')
@section('content')
<section class="block">
   <div class="container">
   <div class="row">
   <div class="col-md-6 col-6 col-xs-6">
      <ul class="nav nav-pills" id="myTab-pills" role="tablist" style="text-align: center;">
         <li class="nav-item">
            <a class="nav-link icon" href="/profilim"><i class="fa fa-user" style="color:white"></i>Profilim</a>
         </li>
         <li class="nav-item">
            <a class="nav-link active icon" href="/randevularim">
            <i class="fa fa-heart"></i>Randevularım
            </a>
         </li>
       
         <li class="nav-item">
            <a class="nav-link icon" href="/ayarlarim">
            <i class="fa fa-recycle"></i>Ayarlarım
            </a> 
         </li>
        
      </ul>
   </div>
   <div class="col-md-6 col-6 col-xs-6" style="text-align: right;">
    
            <a style="background-color: #5C008E; padding:10px 30px 10px 30px; font-size: 16px; border-radius:30px;color:#fff" class="btn btn-primary text-caps btn-rounded btn-framed" href="/">RANDEVU AL</a>
       
   </div>
   <div class="col-md-12">
      @foreach($randevular as $randevuliste)
      <input type="hidden" name="randevuno" value="{{$randevuliste->id}}">
      <input type="hidden" name="salonno" data-value="{{$randevuliste->id}}" value="{{\App\Salonlar::where('id',$randevuliste->salon_id)->value('id')}}">
      <div class="row randevuliste">
         <div class="col-md-2">
            <div class="date">
               <span class="month">{{str_replace(['January','Febuary','March','April','May','June','July','August','September','October','November','December'],['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],date('d F',strtotime($randevuliste->tarih)))}}</span>
               <span class="hour"><strong>{{date('H:i',strtotime($randevuliste->saat))}}</strong></span><span class="day"> {{str_replace(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'],date('l',strtotime($randevuliste->tarih)))}}</span>
            </div>
         </div>
         <div class="col-md-5">
            <span class="confirmed_booking" style="position: relative; float: left;" name="salonadi" data-value="{{$randevuliste->id}}">
               {{\App\Salonlar::where('id',$randevuliste->salon_id)->value('salon_adi')}} <!--<span>Saç Bakımı</span><span>Fark Etmez</span>-->
            </span>
            <!--   <a style="position: relative; float: left;" href="https://www.facebook.com/sharer.php?m2w&s=100&p[url]=http%3A%2F%2F{{$_SERVER['HTTP_HOST']}}%2F{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower(\App\Salonlar::join('salon_turu','salonlar.salon_turu_id','=','salon_turu.id')->value('salon_turu_adi'))))}}%2F{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower(\App\Salonlar::join('il','salonlar.il_id','=','il.id')->value('il_adi'))))}}%2F{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower(\App\Salonlar::join('ilce','salonlar.ilce_id','=','ilce.id')->value('ilce_adi'))))}}%2F{{$randevuliste->salon_id}}%2F{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower(\App\Salonlar::where('id',$randevuliste->salon_id)->value('salon_adi'))))}}&p[images][0]=http%3A%2F%2F{{$_SERVER['HTTP_HOST']}}%2Fpublic%2Fimg%2Ffacebook2.jpg&2F&p[title]={{\App\Salonlar::where('id',$randevuliste->salon_id)->value('salon_adi')}} "> Facebookta Paylaş</a>-->
            <span>
            Hizmetler : 
            @foreach(\App\RandevuHizmetler::where('randevu_id',$randevuliste->id)->get() as $randevuhizmetler)
            {{\App\Hizmetler::where('id',$randevuhizmetler->hizmet_id)->value('hizmet_adi')}} &nbsp;
            @endforeach 
            </span> <br/>
            <span>
            Personeller : 
            @foreach(\App\RandevuHizmetler::where('randevu_id',$randevuliste->id)->get() as $randevuhizmetler)
            {{\App\Personeller::where('id',$randevuhizmetler->personel_id)->value('personel_adi')}} &nbsp;
            @endforeach
            </span>
         </div>
         <div class="col-md-3">
            <ul class="info_booking">
               <li> 
                  @if($randevuliste->durum == 0)
                  <span name="randevudurum"  data-value="{{$randevuliste->id}}" class="btn btn-warning small" style="width: 100%">Beklemede</span> 
                  @elseif($randevuliste->durum == 1)
                  <span name="randevudurum"  data-value="{{$randevuliste->id}}" class="btn btn-success small" style="width: 100%">Onaylandı</span> 
                  @elseif($randevuliste->durum == 2)
                  <span name="randevudurum"  data-value="{{$randevuliste->id}}" class="btn btn-danger small" style="width: 100%">İptal Edildi</span>
                  @elseif($randevuliste->durum == 3)
                  <span name="randevudurum"  class="btn btn-secondary small" style="width: 100%">İptal Ettiniz</span>
                  @endif
                  <span name="guncelrandevudurum" data-value="{{$randevuliste->id}}"></span>
               </li>
               <li> <br /><span class="btn btn-info small" style="font-size: 9px;line-height: 0.5; width: 100%"><strong>Oluşturulma : </strong> {{date('d.m.Y', strtotime($randevuliste->created_at))}}</span></li>
            </ul>
         </div>
         <div class="col-md-2">
            <div class="booking_buttons">
               @if($randevuliste->durum == 1)
               @if(\App\SalonPuanlar::where('user_id',Auth::user()->id)->where('salon_id',$randevuliste->salon_id)->count()==0)
               <a href='/##yorumtext_yorum' class="btn btn-info small" name="puanyorumla" data-value="{{$randevuliste->id}}" style="width: 100%">
               Puanla / Yorumla </a>
               
               @endif
               @elseif($randevuliste->durum == 0)
               <button class="btn btn-danger small" data-value="{{$randevuliste->id}}" name="randevuiptalet" type="button" style="width: 100%">İptal Et</button>
               @endif
               </div>
            </div>
         </div>
         @endforeach
      </div>
   </div>
</section>
@endsection