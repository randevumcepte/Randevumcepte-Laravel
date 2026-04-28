@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $katRenk = ['genel'=>'muted','teknik'=>'info','odeme'=>'warning','egitim'=>'success','iade'=>'danger','kapanis'=>'muted'];
@endphp

<div class="sy-page-head">
    <div>
        <h2>Hazır Cevaplar</h2>
        <div class="subtitle">Ticket yanıtlarken tek tıkla kullanabileceğiniz şablon mesajlar</div>
    </div>
    <a href="/sistemyonetim/v2/hazir-cevap/yeni" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Cevap</a>
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Kategori</th>
                    <th>Kısayol</th>
                    <th>İçerik (önizleme)</th>
                    <th class="sy-text-right">Kullanım</th>
                    <th>Durum</th>
                    <th class="sy-text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $i)
                    <tr>
                        <td><strong>{{ $i->baslik }}</strong></td>
                        <td><span class="sy-badge sy-badge-{{ $katRenk[$i->kategori] ?? 'muted' }}">{{ $i->kategori }}</span></td>
                        <td><code>{{ $i->kisayol ?: '—' }}</code></td>
                        <td class="sy-fs-13 sy-text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($i->icerik), 100) }}</td>
                        <td class="sy-text-right"><strong>{{ $i->kullanim_sayisi }}</strong></td>
                        <td>
                            @if($i->aktif)<span class="sy-badge sy-badge-success">Aktif</span>
                            @else<span class="sy-badge sy-badge-muted">Pasif</span>@endif
                        </td>
                        <td class="sy-text-right nowrap">
                            <a href="/sistemyonetim/v2/hazir-cevap/{{ $i->id }}/duzenle" class="sy-btn sy-btn-sm sy-btn-soft"><span class="mdi mdi-pencil"></span></a>
                            <form method="post" action="/sistemyonetim/v2/hazir-cevap/{{ $i->id }}" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="sy-btn sy-btn-sm sy-btn-danger"><span class="mdi mdi-delete"></span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="sy-empty"><div class="icon mdi mdi-message-text-outline"></div><div class="baslik">Hazır cevap yok</div><div>İlk cevabı ekleyin → ticket yanıtlarken kullanın</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">{{ $list->links() }}</div>

@endsection
