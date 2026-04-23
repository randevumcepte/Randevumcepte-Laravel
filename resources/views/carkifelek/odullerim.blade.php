@extends('layout.layout')

@section('content')
<style>
.od-page { max-width: 860px; margin: 40px auto; padding: 20px; }
.od-hero {
    background: linear-gradient(135deg,#fdcb6e 0%,#fd79a8 100%);
    color:#fff; padding:28px 24px; border-radius:20px; margin-bottom:22px;
    box-shadow:0 10px 30px rgba(253,121,168,.25);
}
.od-hero h1 { font-size:24px; font-weight:800; margin:0 0 4px; }
.od-hero p  { font-size:14px; opacity:.92; margin:0; }

.od-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:16px; }
.od-card {
    background:#fff; border-radius:16px; padding:20px 18px;
    box-shadow: 0 6px 22px rgba(0,0,0,.06);
    border: 2px dashed #e5e7eb;
    position:relative; overflow:hidden;
    transition:.25s;
}
.od-card.kullanildi { opacity:.55; filter:grayscale(.6); }
.od-card:not(.kullanildi):hover { transform:translateY(-4px); box-shadow:0 14px 32px rgba(108,92,231,.18); }

.od-tag {
    position:absolute; top:12px; right:12px;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:.3px;
}
.od-tag.yeni { background:#d1fae5; color:#065f46; }
.od-tag.kul  { background:#fee2e2; color:#991b1b; }
.od-tag.sure { background:#fef3c7; color:#92400e; }

.od-baslik { font-size:20px; font-weight:800; color:#6c5ce7; margin-bottom:6px; }
.od-salon  { font-size:13px; color:#636e72; margin-bottom:14px; }
.od-kod    {
    text-align:center; padding:10px;
    background:#fef3c7; color:#92400e;
    font-family: monospace; font-size:18px; font-weight:800; letter-spacing:3px;
    border:1.5px dashed #f59e0b; border-radius:10px; margin-bottom:8px;
}
.od-tarih  { font-size:12px; color:#636e72; text-align:center; }

.od-empty {
    text-align:center; padding:60px 20px;
    background:#fff; border-radius:16px;
}
.od-empty-icon { font-size:64px; margin-bottom:10px; }
.od-empty p { font-size:15px; color:#636e72; margin:0; }
</style>

<div class="od-page">
    <div class="od-hero">
        <h1>🎁 Kazandığım Ödüller</h1>
        <p>Çarkıfelekten kazandığınız kuponları burada görebilirsiniz. Salonda randevu aldığınızda kupon kodunu ilgili personele söyleyin.</p>
    </div>

    @if($odullerim->isEmpty())
        <div class="od-empty">
            <div class="od-empty-icon">🎡</div>
            <p>Henüz kazanılmış bir ödülünüz yok.<br>Onaylanmış randevularınız üzerinden çarkı çevirebilirsiniz.</p>
        </div>
    @else
        <div class="od-list">
            @foreach($odullerim as $o)
                @php
                    $gecmis = $o->gecerlilik_tarihi && $o->gecerlilik_tarihi->isPast();
                    $pasif  = $o->kullanildi || $gecmis;
                @endphp
                <div class="od-card {{ $pasif ? 'kullanildi' : '' }}">
                    @if($o->kullanildi)
                        <span class="od-tag kul">Kullanıldı</span>
                    @elseif($gecmis)
                        <span class="od-tag kul">Süresi Doldu</span>
                    @else
                        <span class="od-tag yeni">Geçerli</span>
                    @endif

                    <div class="od-baslik">{{ $o->baslik }}</div>
                    <div class="od-salon">{{ optional(\App\Salonlar::find($o->salon_id))->salon_adi ?? 'Salon' }}</div>

                    <div class="od-kod">{{ $o->kod }}</div>

                    <div class="od-tarih">
                        @if($o->kullanildi && $o->kullanim_tarihi)
                            {{ $o->kullanim_tarihi->format('d.m.Y') }} tarihinde kullanıldı
                        @elseif($o->gecerlilik_tarihi)
                            Son kullanım: {{ $o->gecerlilik_tarihi->format('d.m.Y') }}
                        @else
                            Süresiz
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
