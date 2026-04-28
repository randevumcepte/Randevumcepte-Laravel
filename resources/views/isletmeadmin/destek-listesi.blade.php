@extends("layout.layout_isletmeadmin")
@section("content")

@php
    $oncelikRenk = ['acil'=>'danger','yuksek'=>'warning','orta'=>'info','dusuk'=>'secondary'];
    $durumRenk = ['acik'=>'danger','islemde'=>'warning','bekliyor'=>'info','cozumlendi'=>'success','kapali'=>'secondary'];
@endphp

<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
         <div class="title">
            <h1>{{ $sayfa_baslik }}</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="/isletmeyonetim">Ana Sayfa</a></li>
               <li class="breadcrumb-item active" aria-current="page">Destek Talepleri</li>
            </ol>
         </nav>
      </div>
   </div>
</div>

<section class="page-content container-fluid">
   <div class="row">
      <div class="col-md-12">
         <div class="panel">
            <div class="panel-heading">
               <span class="title elipsis"><strong>Destek Talepleriniz</strong></span>
               <a href="/isletmeyonetim/destek/yeni" class="btn btn-primary btn-sm pull-right" style="margin-top:-5px"><i class="material-icons">add</i> Yeni Talep</a>
            </div>
            <div class="panel-body">
               @if(session('basari'))
                  <div class="alert alert-success">{{ session('basari') }}</div>
               @endif
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>Numara</th>
                        <th>Konu</th>
                        <th>Kategori</th>
                        <th>Öncelik</th>
                        <th>Durum</th>
                        <th>Açılış</th>
                        <th></th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($ticketlar as $t)
                        <tr>
                           <td><strong>{{ $t->numara }}</strong></td>
                           <td>{{ $t->konu }}</td>
                           <td>{{ $t->kategori }}</td>
                           <td><span class="label label-{{ $oncelikRenk[$t->oncelik] ?? 'default' }}">{{ $t->oncelik }}</span></td>
                           <td><span class="label label-{{ $durumRenk[$t->durum] ?? 'default' }}">{{ $t->durum }}</span></td>
                           <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d.m.Y H:i') }}</td>
                           <td><a href="/isletmeyonetim/destek/{{ $t->id }}" class="btn btn-default btn-sm">Aç</a></td>
                        </tr>
                     @empty
                        <tr><td colspan="7" class="text-center text-muted" style="padding:40px">Henüz talebiniz yok. <a href="/isletmeyonetim/destek/yeni">Yeni talep oluştur</a></td></tr>
                     @endforelse
                  </tbody>
               </table>
               {{ $ticketlar->links() }}
            </div>
         </div>
      </div>
   </div>
</section>

@endsection
