@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>{{ $duyuru ? $duyuru->baslik.' — Düzenle' : 'Yeni Duyuru' }}</h2>
        <div class="subtitle">Salonlara duyurulacak içerik · Banner olarak işletmeyonetim panelinde gözükür</div>
    </div>
    <a href="/sistemyonetim/v2/duyuru" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Geri</a>
</div>

<form method="post" action="{{ $duyuru ? '/sistemyonetim/v2/duyuru/'.$duyuru->id : '/sistemyonetim/v2/duyuru' }}">
    @csrf
    @if($duyuru)<input type="hidden" name="_method" value="PUT">@endif

    <div class="sy-grid-2-1">
        <div class="sy-card">
            <div class="sy-card-head"><h3>İçerik</h3></div>
            <div class="sy-card-body">
                <div class="sy-form-group">
                    <label>Başlık *</label>
                    <input type="text" name="baslik" class="sy-input" required value="{{ old('baslik', $duyuru->baslik ?? '') }}">
                </div>
                <div class="sy-form-group">
                    <label>İçerik *</label>
                    <textarea name="icerik" class="sy-textarea" rows="6" required>{{ old('icerik', $duyuru->icerik ?? '') }}</textarea>
                    <div class="sy-text-muted sy-fs-12 sy-mt-12">HTML kullanılabilir. Banner'da gösterilir.</div>
                </div>

                <div class="sy-form-row">
                    <div class="sy-form-group">
                        <label>Eylem Buton Metni (opsiyonel)</label>
                        <input type="text" name="cta_metin" class="sy-input" value="{{ old('cta_metin', $duyuru->cta_metin ?? '') }}" placeholder="Detayları Gör">
                    </div>
                    <div class="sy-form-group">
                        <label>Eylem Linki</label>
                        <input type="text" name="cta_link" class="sy-input" value="{{ old('cta_link', $duyuru->cta_link ?? '') }}" placeholder="/isletmeyonetim/...">
                    </div>
                </div>
            </div>
        </div>

        <div class="sy-stack">
            <div class="sy-card">
                <div class="sy-card-head"><h3>Yayın Ayarları</h3></div>
                <div class="sy-card-body">
                    @php $tip = old('tip', $duyuru->tip ?? 'bilgi'); @endphp
                    <div class="sy-form-group">
                        <label>Tip *</label>
                        <select name="tip" class="sy-select">
                            <option value="bilgi" {{ $tip=='bilgi'?'selected':'' }}>Bilgi · mavi</option>
                            <option value="uyari" {{ $tip=='uyari'?'selected':'' }}>Uyarı · sarı</option>
                            <option value="onemli" {{ $tip=='onemli'?'selected':'' }}>Önemli · kırmızı</option>
                            <option value="bakim" {{ $tip=='bakim'?'selected':'' }}>Bakım · gri</option>
                            <option value="kampanya" {{ $tip=='kampanya'?'selected':'' }}>Kampanya · yeşil</option>
                        </select>
                    </div>
                    <div class="sy-form-row">
                        <div class="sy-form-group">
                            <label>Başlangıç</label>
                            <input type="datetime-local" name="baslangic_tarihi" class="sy-input"
                                   value="{{ old('baslangic_tarihi', isset($duyuru->baslangic_tarihi) ? \Carbon\Carbon::parse($duyuru->baslangic_tarihi)->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div class="sy-form-group">
                            <label>Bitiş</label>
                            <input type="datetime-local" name="bitis_tarihi" class="sy-input"
                                   value="{{ old('bitis_tarihi', isset($duyuru->bitis_tarihi) ? \Carbon\Carbon::parse($duyuru->bitis_tarihi)->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>
                    <div class="sy-form-group">
                        <label class="sy-flex-row" style="align-items:center"><input type="checkbox" name="aktif" value="1" {{ ($duyuru && $duyuru->aktif) || !$duyuru ? 'checked' : '' }}> Aktif</label>
                        <label class="sy-flex-row" style="align-items:center"><input type="checkbox" name="sticky" value="1" {{ ($duyuru && $duyuru->sticky) ? 'checked' : '' }}> Sabit (her panele girişte gözüksün)</label>
                    </div>
                </div>
            </div>

            <div class="sy-card">
                <div class="sy-card-head"><h3>Hedef</h3></div>
                <div class="sy-card-body">
                    @php $h = old('hedef_tipi', $duyuru->hedef_tipi ?? 'hepsi'); @endphp
                    <div class="sy-form-group">
                        <label>Hedef Tipi</label>
                        <select name="hedef_tipi" class="sy-select" id="hedefTipi" onchange="hedefDegis(this.value)">
                            <option value="hepsi" {{ $h=='hepsi'?'selected':'' }}>Tüm Salonlar</option>
                            <option value="secili" {{ $h=='secili'?'selected':'' }}>Seçili Salonlar</option>
                            <option value="il" {{ $h=='il'?'selected':'' }}>İle Göre</option>
                        </select>
                    </div>

                    <div class="sy-form-group" id="ilSecim" style="display: {{ $h=='il'?'block':'none' }}">
                        <label>İl(ler)</label>
                        <select name="il_ids[]" class="sy-select" multiple size="6">
                            @php $secilenIller = $duyuru ? $duyuru->hedefIdsArray() : []; @endphp
                            @foreach($iller as $il)
                                <option value="{{ $il->id }}" {{ in_array($il->id, $secilenIller) ? 'selected' : '' }}>{{ $il->il_adi }}</option>
                            @endforeach
                        </select>
                        <div class="sy-text-muted sy-fs-12">Ctrl/Cmd ile çoklu seçim</div>
                    </div>

                    <div class="sy-form-group" id="salonSecim" style="display: {{ $h=='secili'?'block':'none' }}">
                        <label>Salonlar (ID'leri virgülle yaz veya seç)</label>
                        <input type="text" id="salonAra" class="sy-input" placeholder="Salon adı ile ara..." onkeyup="salonAra(this.value)" style="margin-bottom:8px">
                        <div id="salonSonuc" style="max-height:160px;overflow:auto;border:1px solid var(--sy-border);border-radius:8px;padding:8px;display:none;background:#fff"></div>
                        <div id="secilenSalonlar" class="sy-flex-row sy-mt-12">
                            @if(!empty($secilenSalonlar) && $secilenSalonlar->count() > 0)
                                @foreach($secilenSalonlar as $s)
                                    <span class="sy-badge sy-badge-info" data-id="{{ $s->id }}" style="cursor:pointer" onclick="salonCikar({{ $s->id }})">
                                        {{ $s->salon_adi }} ×
                                        <input type="hidden" name="salon_ids[]" value="{{ $s->id }}">
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="sy-card">
                <div class="sy-card-body sy-flex-row" style="justify-content:flex-end">
                    <a href="/sistemyonetim/v2/duyuru" class="sy-btn">İptal</a>
                    <button class="sy-btn sy-btn-primary"><span class="mdi mdi-bullhorn"></span> Kaydet</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function hedefDegis(v) {
    document.getElementById('salonSecim').style.display = v === 'secili' ? 'block' : 'none';
    document.getElementById('ilSecim').style.display = v === 'il' ? 'block' : 'none';
}
let salonAraTimeout = null;
function salonAra(q) {
    clearTimeout(salonAraTimeout);
    if (q.length < 2) {
        document.getElementById('salonSonuc').style.display = 'none';
        return;
    }
    salonAraTimeout = setTimeout(() => {
        fetch('/sistemyonetim/v2/api/salon-ara?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(d => {
                const div = document.getElementById('salonSonuc');
                if (!d.length) { div.innerHTML = '<div class="sy-text-muted">Sonuç yok</div>'; div.style.display='block'; return; }
                div.innerHTML = d.map(s => '<div style="padding:6px 8px;cursor:pointer;border-radius:6px" onmouseover="this.style.background=\'#f5f0fa\'" onmouseout="this.style.background=\'\'" onclick="salonEkle('+s.id+',\''+ (s.salon_adi || '').replace(/'/g, "\\'") +'\')">'+s.salon_adi+'</div>').join('');
                div.style.display = 'block';
            });
    }, 250);
}
function salonEkle(id, adi) {
    const div = document.getElementById('secilenSalonlar');
    if (div.querySelector('[data-id="'+id+'"]')) return;
    const span = document.createElement('span');
    span.className = 'sy-badge sy-badge-info';
    span.dataset.id = id;
    span.style.cursor = 'pointer';
    span.onclick = () => salonCikar(id);
    span.innerHTML = adi + ' ×<input type="hidden" name="salon_ids[]" value="'+id+'">';
    div.appendChild(span);
    document.getElementById('salonAra').value = '';
    document.getElementById('salonSonuc').style.display = 'none';
}
function salonCikar(id) {
    const el = document.querySelector('#secilenSalonlar [data-id="'+id+'"]');
    if (el) el.remove();
}
</script>
@endpush

@endsection
