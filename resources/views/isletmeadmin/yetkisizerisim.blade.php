@if(Auth::guard('satisortakligi')->check())
   @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
   @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)

@section('content')
{{-- 403 — yetki yok. Layout (sidebar + header) korunur, sadece icerik
     bolumunde uyari gosterilir. Boylece kullanici menuden baska bir
     sayfaya gecebilir, oturum acik kalir. --}}
<div class="pd-ltr-20 xs-pd-20-10">
   <div class="min-height-200px">
      <div class="page-header">
         <div class="row">
            <div class="col-md-12">
               <div class="title">
                  <h4>Yetkisiz İşlem</h4>
               </div>
            </div>
         </div>
      </div>

      <div class="card-box pd-30 height-100-p text-center">
         <div style="padding: 50px 20px;">
            <div style="font-size: 96px; font-weight: 900; line-height: 1; background: linear-gradient(135deg,#5C008E,#9D5DC8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 16px;">
               403
            </div>
            <h3 style="font-weight: 700; color: #2D1B3F; margin-bottom: 12px;">
               <i class="bi bi-shield-lock-fill" style="color:#9D5DC8;"></i>
               Bu içeriği görüntüleme yetkiniz bulunmamaktadır
            </h3>
            <p style="color: #6b7280; font-size: 14px; max-width: 480px; margin: 0 auto 24px;">
               Bu sayfaya erişim için gerekli yetkiye sahip değilsiniz.
               Eğer bu sayfayı görmeniz gerektiğini düşünüyorsanız,
               yöneticinize başvurarak yetki güncellemesi talep edebilirsiniz.
            </p>
            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
               <a href="javascript:history.back()" class="btn btn-outline-secondary">
                  <i class="fa fa-chevron-left"></i> Geri Dön
               </a>
               <a href="/isletmeyonetim" class="btn"
                  style="background: linear-gradient(135deg,#5C008E,#9D5DC8); color:#fff; border:none;">
                  <i class="fa fa-home"></i> Ana Sayfaya Dön
               </a>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
