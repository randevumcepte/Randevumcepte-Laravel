@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $tipRenk = ['bilgi'=>'info','uyari'=>'warning','onemli'=>'danger','bakim'=>'muted','kampanya'=>'success'];
    $hedefEt = ['hepsi'=>'Tüm Salonlar','secili'=>'Seçili Salonlar','il'=>'İle Göre'];
@endphp

<div class="sy-page-head">
    <div>
        <h2>Duyurular</h2>
        <div class="subtitle">Salonlara bilgi, uyarı, kampanya, bakım duyurusu gönderin</div>
    </div>
    <a href="/sistemyonetim/v2/duyuru/yeni" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Duyuru</a>
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Tip</th>
                    <th>Hedef</th>
                    <th>Geçerlilik</th>
                    <th>Durum</th>
                    <th>Oluşturan</th>
                    <th class="sy-text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($duyurular as $d)
                    <tr>
                        <td>
                            <a href="/sistemyonetim/v2/duyuru/{{ $d->id }}" class="sy-fw-600">{{ $d->baslik }}</a>
                            @if($d->sticky)<span class="sy-badge sy-badge-warning"><span class="mdi mdi-pin"></span></span>@endif
                            <div class="sy-text-muted sy-fs-12">{{ \Illuminate\Support\Str::limit(strip_tags($d->icerik), 80) }}</div>
                        </td>
                        <td><span class="sy-badge sy-badge-{{ $tipRenk[$d->tip] ?? 'muted' }}">{{ $d->tip }}</span></td>
                        <td class="sy-fs-13">{{ $hedefEt[$d->hedef_tipi] ?? $d->hedef_tipi }}</td>
                        <td class="sy-fs-12 nowrap">
                            @if($d->baslangic_tarihi){{ \Carbon\Carbon::parse($d->baslangic_tarihi)->format('d.m.Y') }}@else—@endif
                            <br>
                            @if($d->bitis_tarihi){{ \Carbon\Carbon::parse($d->bitis_tarihi)->format('d.m.Y') }}@else süresiz@endif
                        </td>
                        <td>
                            @if($d->aktif)
                                <span class="sy-badge sy-badge-success">Aktif</span>
                            @else
                                <span class="sy-badge sy-badge-muted">Pasif</span>
                            @endif
                        </td>
                        <td class="sy-fs-13">{{ $d->olusturan_user_name }}</td>
                        <td class="sy-text-right nowrap">
                            <a href="/sistemyonetim/v2/duyuru/{{ $d->id }}/duzenle" class="sy-btn sy-btn-sm sy-btn-soft"><span class="mdi mdi-pencil"></span></a>
                            <form method="post" action="/sistemyonetim/v2/duyuru/{{ $d->id }}" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="sy-btn sy-btn-sm sy-btn-danger"><span class="mdi mdi-delete"></span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="sy-empty"><div class="icon mdi mdi-bullhorn-outline"></div><div class="baslik">Duyuru yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">{{ $duyurular->links() }}</div>

@endsection
