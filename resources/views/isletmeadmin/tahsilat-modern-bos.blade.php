@extends('layout.layout_isletmeadmin')
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-12">
         <div class="title">
            <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
         </div>
      </div>
   </div>
</div>
<div class="card-box pd-30" style="text-align:center; margin:30px auto; max-width:560px; border-radius:14px; background:linear-gradient(135deg,#5C008E 0%,#7B2FB8 50%,#9D5DC8 100%); color:#fff;">
   <i class="fa fa-flask" style="font-size:40px; opacity:.85;"></i>
   <h2 style="color:#fff; margin-top:10px;">Modern Tahsilat (Beta)</h2>
   <p style="opacity:.9; margin:10px 0 18px 0;">
      Henüz satış kaydınız bulunmuyor. Önce bir satış oluşturup ardından bu yeni tasarımı görebilirsiniz.
   </p>
   <a href="/isletmeyonetim/yenitahsilat{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}"
      class="btn btn-light" style="font-weight:600; color:#5C008E;">
      <i class="fa fa-plus"></i> Yeni Satış & Tahsilat
   </a>
</div>
@endsection
