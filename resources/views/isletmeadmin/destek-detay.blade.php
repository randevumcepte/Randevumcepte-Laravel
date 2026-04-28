@extends("layout.layout_isletmeadmin")
@section("content")

@php
    $oncelikRenk = ['acil'=>'danger','yuksek'=>'warning','orta'=>'info','dusuk'=>'default'];
    $durumRenk = ['acik'=>'danger','islemde'=>'warning','bekliyor'=>'info','cozumlendi'=>'success','kapali'=>'default'];
@endphp

<div class="page-header">
   <div class="row">
      <div class="col-md-12">
         <div class="title"><h1>{{ $sayfa_baslik }} — {{ $ticket->konu }}</h1></div>
         <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="/isletmeyonetim">Ana Sayfa</a></li>
               <li class="breadcrumb-item"><a href="/isletmeyonetim/destek">Destek</a></li>
               <li class="breadcrumb-item active">{{ $ticket->numara }}</li>
            </ol>
         </nav>
      </div>
   </div>
</div>

<section class="page-content container-fluid">
   @if(session('basari'))<div class="alert alert-success">{{ session('basari') }}</div>@endif
   <div class="row">
      <div class="col-md-8">
         <div class="panel">
            <div class="panel-heading">
               <span class="title"><strong>Konuşma</strong></span>
               <span class="pull-right">
                  <span class="label label-{{ $oncelikRenk[$ticket->oncelik] ?? 'default' }}">{{ $ticket->oncelik }}</span>
                  <span class="label label-{{ $durumRenk[$ticket->durum] ?? 'default' }}">{{ $ticket->durum }}</span>
               </span>
            </div>
            <div class="panel-body">
               <div style="background:#f9f7fc;border-radius:8px;padding:14px;max-height:480px;overflow:auto">
                  @forelse($mesajlar as $m)
                     @php $kendi = $m->user_tipi === 'salon'; @endphp
                     <div style="margin-bottom:14px;display:flex;{{ $kendi ? 'flex-direction:row-reverse' : '' }}">
                        <div style="width:36px;height:36px;border-radius:50%;background:{{ $kendi ? '#5C008E' : '#7e7595' }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">
                           {{ mb_substr($m->user_name, 0, 1) }}
                        </div>
                        <div style="flex:1;{{ $kendi ? 'margin-right:10px;text-align:right' : 'margin-left:10px' }}">
                           <div style="font-size:11px;color:#888;margin-bottom:2px">
                              <strong>{{ $m->user_name }}</strong> {{ $kendi ? '(Siz)' : '· Destek Ekibi' }} · {{ \Carbon\Carbon::parse($m->created_at)->format('d.m.Y H:i') }}
                           </div>
                           <div style="background:{{ $kendi ? '#e8d8f7' : '#fff' }};border:1px solid {{ $kendi ? '#d0bff0' : '#e6e0f0' }};border-radius:10px;padding:10px 14px;display:inline-block;text-align:left;max-width:90%">
                              {!! nl2br(e($m->mesaj)) !!}
                           </div>
                        </div>
                     </div>
                  @empty
                     <div class="text-center text-muted" style="padding:40px">Henüz mesaj yok</div>
                  @endforelse
               </div>

               @if($ticket->durum !== 'kapali')
               <form method="post" action="/isletmeyonetim/destek/{{ $ticket->id }}/yanit" style="margin-top:14px">
                  @csrf
                  <div class="form-group">
                     <textarea name="mesaj" class="form-control" rows="3" placeholder="Yanıt yaz..." required></textarea>
                  </div>
                  <div class="text-right">
                     <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Gönder
                     </button>
                  </div>
               </form>
               @else
                  <div class="alert alert-info" style="margin-top:14px"><strong>Bu talep kapatıldı.</strong> Yeni bir sorun için <a href="/isletmeyonetim/destek/yeni">yeni talep</a> oluşturabilirsiniz.</div>
               @endif
            </div>
         </div>
      </div>

      <div class="col-md-4">
         <div class="panel">
            <div class="panel-heading"><span class="title"><strong>Detay</strong></span></div>
            <div class="panel-body">
               <table class="table">
                  <tr><td>Numara</td><td><strong>{{ $ticket->numara }}</strong></td></tr>
                  <tr><td>Kategori</td><td>{{ $ticket->kategori }}</td></tr>
                  <tr><td>Açılış</td><td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y H:i') }}</td></tr>
                  @if($ticket->ilk_yanit_tarihi)
                  <tr><td>İlk Yanıt</td><td>{{ \Carbon\Carbon::parse($ticket->ilk_yanit_tarihi)->format('d.m.Y H:i') }}</td></tr>
                  @endif
                  @if($ticket->cozumlenme_tarihi)
                  <tr><td>Çözüm</td><td>{{ \Carbon\Carbon::parse($ticket->cozumlenme_tarihi)->format('d.m.Y H:i') }}</td></tr>
                  @endif
               </table>
            </div>
         </div>
      </div>
   </div>
</section>

@endsection
