@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $oncelikRenk = ['acil'=>'danger','yuksek'=>'warning','orta'=>'info','dusuk'=>'muted'];
    $durumRenk = ['acik'=>'danger','islemde'=>'warning','bekliyor'=>'info','cozumlendi'=>'success','kapali'=>'muted'];
@endphp

<div class="sy-page-head">
    <div>
        <h2>{{ $ticket->numara }} — {{ \Illuminate\Support\Str::limit($ticket->konu, 70) }}</h2>
        <div class="subtitle">
            <span class="sy-badge sy-badge-{{ $oncelikRenk[$ticket->oncelik] ?? 'muted' }}">Öncelik: {{ $ticket->oncelik }}</span>
            <span class="sy-badge sy-badge-{{ $durumRenk[$ticket->durum] ?? 'muted' }}">{{ $ticket->durum }}</span>
            <span class="sy-badge sy-badge-muted">{{ $ticket->kategori }}</span>
            · Açılış: {{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y H:i') }}
        </div>
    </div>
    <a href="/sistemyonetim/v2/ticket" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Liste</a>
</div>

<div class="sy-grid-2-1">
    <div class="sy-stack">
        <div class="sy-card">
            <div class="sy-card-head"><h3>Konuşma</h3></div>
            <div class="sy-card-body">
                <div class="sy-chat">
                    @forelse($mesajlar as $m)
                        <div class="sy-msg {{ $m->user_tipi === 'salon' ? 'salon' : '' }} {{ $m->ic_not ? 'ic-not' : '' }}">
                            <div class="avatar">{{ mb_substr($m->user_name, 0, 1) }}</div>
                            <div class="bubble">
                                <div class="meta">
                                    <strong>{{ $m->user_name }}</strong>
                                    <span class="sy-badge sy-badge-muted">{{ $m->user_tipi }}</span>
                                    @if($m->ic_not)<span class="sy-badge sy-badge-warning"><span class="mdi mdi-eye-off"></span> İç Not</span>@endif
                                    · {{ \Carbon\Carbon::parse($m->created_at)->format('d.m.Y H:i') }}
                                </div>
                                <div>{!! nl2br(e($m->mesaj)) !!}</div>
                            </div>
                        </div>
                    @empty
                        <div class="sy-empty"><div class="baslik">Henüz mesaj yok</div></div>
                    @endforelse
                </div>

                <form method="post" action="/sistemyonetim/v2/ticket/{{ $ticket->id }}/yanit" class="sy-mt-18">
                    @csrf
                    <div class="sy-form-group" style="position:relative">
                        <div class="sy-flex-row" style="justify-content:space-between;margin-bottom:6px">
                            <label style="margin:0">Yanıt</label>
                            <button type="button" class="sy-btn sy-btn-sm sy-btn-soft" id="hcAcButton"><span class="mdi mdi-message-text-fast"></span> Hazır Cevap</button>
                        </div>
                        <textarea name="mesaj" id="ticketYanitArea" class="sy-textarea" rows="5" placeholder="Yanıt yaz... (hazır cevap için /kisayol veya butonu kullan)" required></textarea>

                        <div id="hcPanel" style="display:none;position:absolute;right:0;top:0;width:340px;background:#fff;border:1px solid var(--sy-border-strong);border-radius:10px;box-shadow:var(--sy-shadow-lg);z-index:50;max-height:380px;overflow-y:auto">
                            <div style="padding:10px;border-bottom:1px solid var(--sy-border);background:var(--sy-surface-2)">
                                <input type="text" id="hcArama" class="sy-input" placeholder="Hazır cevap ara..." style="height:32px;font-size:12px;padding:6px 10px">
                            </div>
                            <div id="hcListe" style="padding:6px"><div class="sy-text-muted sy-fs-12" style="padding:14px;text-align:center">Yükleniyor...</div></div>
                        </div>
                    </div>
                    <div class="sy-flex-row" style="justify-content:space-between">
                        <label class="sy-flex-row sy-fs-13">
                            <input type="checkbox" name="ic_not" value="1"> İç not olarak ekle (sadece ekip görür)
                        </label>
                        <button class="sy-btn sy-btn-primary"><span class="mdi mdi-send"></span> Yanıtla</button>
                    </div>
                </form>

                @push('scripts')
                <script>
                (function(){
                    const btn = document.getElementById('hcAcButton');
                    const panel = document.getElementById('hcPanel');
                    const arama = document.getElementById('hcArama');
                    const liste = document.getElementById('hcListe');
                    const area = document.getElementById('ticketYanitArea');
                    if (!btn) return;

                    let cache = null;
                    function yukle(q) {
                        const url = '/sistemyonetim/v2/api/hazir-cevap' + (q ? '?q=' + encodeURIComponent(q) : '');
                        fetch(url).then(r => r.json()).then(d => {
                            cache = d;
                            if (!d.length) { liste.innerHTML = '<div class="sy-text-muted sy-fs-12" style="padding:14px;text-align:center">Sonuç yok</div>'; return; }
                            liste.innerHTML = d.map(c =>
                                '<div class="hcItem" data-id="'+c.id+'" data-icerik="'+escAttr(c.icerik)+'" style="padding:10px;border-radius:6px;cursor:pointer;border-bottom:1px solid #f2eef9">'+
                                '<div style="font-weight:600;font-size:13px">'+esc(c.baslik)+(c.kisayol?' <span class="sy-text-muted sy-fs-12">'+esc(c.kisayol)+'</span>':'')+'</div>'+
                                '<div class="sy-text-muted sy-fs-12">'+esc(c.icerik.substring(0,80))+(c.icerik.length>80?'...':'')+'</div>'+
                                '</div>'
                            ).join('');
                            liste.querySelectorAll('.hcItem').forEach(el => {
                                el.addEventListener('mouseover', () => el.style.background = '#f5f0fa');
                                el.addEventListener('mouseout', () => el.style.background = '');
                                el.addEventListener('click', () => {
                                    const icerik = el.dataset.icerik;
                                    const id = el.dataset.id;
                                    if (area.value.trim()) area.value += '\n\n' + icerik; else area.value = icerik;
                                    panel.style.display = 'none';
                                    area.focus();
                                    fetch('/sistemyonetim/v2/api/hazir-cevap/' + id + '/kullan', { method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content} });
                                });
                            });
                        });
                    }
                    function esc(t){return String(t||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
                    function escAttr(t){return esc(t);}

                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        if (panel.style.display === 'none' || !panel.style.display) {
                            panel.style.display = 'block';
                            arama.focus();
                            yukle();
                        } else {
                            panel.style.display = 'none';
                        }
                    });
                    arama.addEventListener('input', e => yukle(e.target.value.trim()));
                    document.addEventListener('click', e => {
                        if (!panel.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                            panel.style.display = 'none';
                        }
                    });
                })();
                </script>
                @endpush
            </div>
        </div>
    </div>

    <div class="sy-stack">
        <div class="sy-card">
            <div class="sy-card-head"><h3>İşlemler</h3></div>
            <div class="sy-card-body">
                <form method="post" action="/sistemyonetim/v2/ticket/{{ $ticket->id }}/durum" class="sy-form-group">
                    @csrf
                    <label>Durum</label>
                    <div class="sy-flex-row">
                        <select name="durum" class="sy-select">
                            <option value="acik" {{ $ticket->durum=='acik'?'selected':'' }}>Açık</option>
                            <option value="islemde" {{ $ticket->durum=='islemde'?'selected':'' }}>İşlemde</option>
                            <option value="bekliyor" {{ $ticket->durum=='bekliyor'?'selected':'' }}>Bekliyor</option>
                            <option value="cozumlendi" {{ $ticket->durum=='cozumlendi'?'selected':'' }}>Çözümlendi</option>
                            <option value="kapali" {{ $ticket->durum=='kapali'?'selected':'' }}>Kapalı</option>
                        </select>
                        <button class="sy-btn sy-btn-sm sy-btn-soft">Güncelle</button>
                    </div>
                </form>

                <form method="post" action="/sistemyonetim/v2/ticket/{{ $ticket->id }}/oncelik" class="sy-form-group">
                    @csrf
                    <label>Öncelik</label>
                    <div class="sy-flex-row">
                        <select name="oncelik" class="sy-select">
                            <option value="dusuk" {{ $ticket->oncelik=='dusuk'?'selected':'' }}>Düşük</option>
                            <option value="orta" {{ $ticket->oncelik=='orta'?'selected':'' }}>Orta</option>
                            <option value="yuksek" {{ $ticket->oncelik=='yuksek'?'selected':'' }}>Yüksek</option>
                            <option value="acil" {{ $ticket->oncelik=='acil'?'selected':'' }}>Acil</option>
                        </select>
                        <button class="sy-btn sy-btn-sm sy-btn-soft">Güncelle</button>
                    </div>
                </form>

                <form method="post" action="/sistemyonetim/v2/ticket/{{ $ticket->id }}/ata" class="sy-form-group">
                    @csrf
                    <label>Atanan</label>
                    <div class="sy-flex-row">
                        <select name="atanan_user_id" class="sy-select">
                            <option value="">— atanmamış —</option>
                            @foreach($ekip as $e)
                                <option value="{{ $e->id }}" {{ $ticket->atanan_user_id==$e->id?'selected':'' }}>{{ $e->name }}</option>
                            @endforeach
                        </select>
                        <button class="sy-btn sy-btn-sm sy-btn-soft">Ata</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="sy-card">
            <div class="sy-card-head"><h3>Detay</h3></div>
            <div class="sy-card-body">
                <div class="sy-stack" style="gap:8px">
                    @if($salon)
                        <div>
                            <span class="sy-text-muted sy-fs-12">Salon</span>
                            <div class="sy-fw-600"><a href="/sistemyonetim/v2/salon/{{ $salon->id }}">{{ $salon->salon_adi }}</a></div>
                            <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/hesabina-gir" class="sy-mt-12" onsubmit="return confirm('Salonun hesabına geçilecek. Devam?')">
                                @csrf
                                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                <input type="hidden" name="sebep" value="Ticket #{{ $ticket->numara }}">
                                <button class="sy-btn sy-btn-sm sy-btn-primary"><span class="mdi mdi-login"></span> Hesabına Gir</button>
                            </form>
                        </div>
                    @endif
                    <div><span class="sy-text-muted sy-fs-12">Açan</span><div>{{ $ticket->olusturan_user_name ?: '—' }}</div></div>
                    @if($ticket->iletisim_ad)<div><span class="sy-text-muted sy-fs-12">İletişim Adı</span><div>{{ $ticket->iletisim_ad }}</div></div>@endif
                    @if($ticket->iletisim_telefon)<div><span class="sy-text-muted sy-fs-12">Telefon</span><div>{{ $ticket->iletisim_telefon }}</div></div>@endif
                    @if($ticket->iletisim_email)<div><span class="sy-text-muted sy-fs-12">E-posta</span><div>{{ $ticket->iletisim_email }}</div></div>@endif
                    @if($ticket->ilk_yanit_tarihi)<div><span class="sy-text-muted sy-fs-12">İlk Yanıt</span><div>{{ \Carbon\Carbon::parse($ticket->ilk_yanit_tarihi)->format('d.m.Y H:i') }}</div></div>@endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
