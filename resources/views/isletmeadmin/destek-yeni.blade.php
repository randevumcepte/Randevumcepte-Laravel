@extends("layout.layout_isletmeadmin")
@section("content")

<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
         <div class="title"><h1>{{ $sayfa_baslik }}</h1></div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="/isletmeyonetim">Ana Sayfa</a></li>
               <li class="breadcrumb-item"><a href="/isletmeyonetim/destek">Destek</a></li>
               <li class="breadcrumb-item active">Yeni Talep</li>
            </ol>
         </nav>
      </div>
   </div>
</div>

<section class="page-content container-fluid">
   <div class="row">
      <div class="col-md-8 col-md-offset-2">
         <div class="panel">
            <div class="panel-heading"><span class="title"><strong>Yeni Destek Talebi</strong></span></div>
            <div class="panel-body">
               @if($errors->any())
                  <div class="alert alert-danger">
                     @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                  </div>
               @endif
               <form method="post" action="/isletmeyonetim/destek">
                  @csrf
                  <div class="form-group">
                     <label>Konu <span style="color:red">*</span></label>
                     <input type="text" name="konu" class="form-control" required maxlength="250" placeholder="Kısa özet">
                  </div>
                  <div class="row">
                     <div class="col-sm-6 form-group">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control">
                           <option value="teknik">Teknik Sorun</option>
                           <option value="odeme">Ödeme</option>
                           <option value="egitim">Eğitim Talebi</option>
                           <option value="ozellik">Özellik İsteği</option>
                           <option value="sikayet">Şikayet</option>
                           <option value="diger" selected>Diğer</option>
                        </select>
                     </div>
                     <div class="col-sm-6 form-group">
                        <label>Öncelik</label>
                        <select name="oncelik" class="form-control">
                           <option value="dusuk">Düşük</option>
                           <option value="orta" selected>Orta</option>
                           <option value="yuksek">Yüksek</option>
                           <option value="acil">Acil</option>
                        </select>
                     </div>
                  </div>
                  <div class="form-group">
                     <label>Açıklama <span style="color:red">*</span></label>
                     <textarea name="aciklama" class="form-control" rows="6" required placeholder="Sorunu mümkün olduğunca detaylı yazın. Ekran görüntüsü için belge yükleme şu an desteklenmiyor; ekibimiz size geri dönüş yapacak."></textarea>
                  </div>
                  <div class="form-group text-right">
                     <a href="/isletmeyonetim/destek" class="btn btn-default">İptal</a>
                     <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Gönder
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</section>

@endsection
