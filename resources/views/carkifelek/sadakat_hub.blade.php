@extends('layout.layout_profil')

@section('content')

<style>
:root {
    --sd-bg: #f8f5ff;
    --sd-card: #ffffff;
    --sd-purple: #6c5ce7;
    --sd-purple-d: #5b4cdb;
    --sd-pink: #fd79a8;
    --sd-gold: #f59e0b;
    --sd-text: #2d3436;
    --sd-mute: #6b7280;
    --sd-border: #e9e6f5;
}
.sd-page {
    max-width: 980px; margin: 0 auto;
    padding: 24px 18px 80px;
    color: var(--sd-text);
}

/* HERO — Tier card */
.sd-hero {
    position: relative;
    background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
    border-radius: 24px; padding: 28px 26px 24px;
    color: #fff; overflow: hidden;
    box-shadow: 0 18px 48px rgba(92, 0, 142, 0.32);
}
.sd-hero::before {
    content: ''; position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.18), transparent 70%);
}
.sd-hero::after {
    content: ''; position: absolute;
    bottom: -80px; right: 30%;
    width: 180px; height: 180px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.10), transparent 70%);
}
.sd-hero__top {
    display: flex; justify-content: space-between; align-items: flex-start;
    position: relative; z-index: 1; flex-wrap: wrap; gap: 14px;
}
.sd-hero__greet {
    font-size: 13px; opacity: .85; letter-spacing: .3px;
}
.sd-hero__name {
    font-size: 20px; font-weight: 800; margin: 2px 0 0;
}

/* Tier rozet */
.sd-tier {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 50px;
    background: rgba(255,255,255,.18);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.25);
    font-size: 13px; font-weight: 700;
}
.sd-tier__dot {
    width: 10px; height: 10px; border-radius: 50%;
    box-shadow: 0 0 8px currentColor;
}

.sd-hero__main {
    margin-top: 24px;
    display: flex; align-items: center; gap: 22px; flex-wrap: wrap;
    position: relative; z-index: 1;
}
/* Daire içinde puan */
.sd-puan-ring {
    flex-shrink: 0;
    width: 130px; height: 130px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(8px);
    border: 3px solid rgba(255,255,255,.4);
    display: flex; align-items: center; justify-content: center;
    flex-direction: column;
    position: relative;
    box-shadow: inset 0 0 22px rgba(255,255,255,.18);
}
.sd-puan-ring__rakam {
    font-size: 38px; font-weight: 900; line-height: 1;
    text-shadow: 0 2px 8px rgba(0,0,0,.2);
}
.sd-puan-ring__alt {
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 1.5px;
    margin-top: 2px; opacity: .9;
}
.sd-hero__info {
    flex: 1; min-width: 200px;
}
.sd-hero__info h3 { margin: 0 0 6px; font-size: 16px; font-weight: 700; opacity: .92; }
.sd-progress-next {
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(6px);
    border-radius: 12px; padding: 12px 14px;
    border: 1px solid rgba(255,255,255,.2);
}
.sd-progress-next__lbl {
    font-size: 12px; opacity: .9; margin-bottom: 6px;
    display: flex; justify-content: space-between;
}
.sd-progress-next__bar {
    height: 7px; background: rgba(255,255,255,.22);
    border-radius: 4px; overflow: hidden;
}
.sd-progress-next__fill {
    height: 100%; background: linear-gradient(90deg, #FBBF24, #FFE66D);
    border-radius: 4px; transition: width .8s;
    box-shadow: 0 0 12px rgba(255, 204, 60, .6);
}

/* Salon seçici çipler */
.sd-salons {
    display: flex; gap: 8px; overflow-x: auto;
    padding: 16px 0 4px; scrollbar-width: none;
    margin-top: 8px;
}
.sd-salons::-webkit-scrollbar { display: none; }
.sd-salons a {
    flex-shrink: 0; padding: 8px 16px;
    background: #fff; border: 1px solid var(--sd-border);
    border-radius: 50px; text-decoration: none;
    font-size: 13px; font-weight: 600;
    color: var(--sd-mute); white-space: nowrap;
    transition: .2s;
    box-shadow: 0 2px 6px rgba(0,0,0,.04);
}
.sd-salons a.active {
    background: var(--sd-purple); color: #fff; border-color: var(--sd-purple);
    box-shadow: 0 4px 14px rgba(108, 92, 231, .35);
}
.sd-salons a:hover { color: var(--sd-purple); text-decoration: none; }
.sd-salons a.active:hover { color: #fff; }

/* Sekmeler */
.sd-tabs {
    display: flex; background: #fff;
    border-radius: 16px; padding: 6px;
    margin: 24px 0 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,.05);
    border: 1px solid var(--sd-border);
}
.sd-tab {
    flex: 1; text-align: center;
    padding: 12px 14px; cursor: pointer;
    font-size: 14px; font-weight: 700;
    color: var(--sd-mute); border-radius: 12px;
    transition: .2s; border: none; background: transparent;
}
.sd-tab.is-active {
    background: linear-gradient(135deg, var(--sd-purple), var(--sd-pink));
    color: #fff;
    box-shadow: 0 6px 16px rgba(108,92,231,.35);
}

/* PANEL — Genel */
.sd-panel { display: none; }
.sd-panel.is-active { display: block; }

/* Ödül merdiveni */
.sd-ladder { display: flex; flex-direction: column; gap: 14px; }
.sd-step {
    background: #fff; border-radius: 18px;
    padding: 18px 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,.05);
    border: 2px solid transparent;
    position: relative; transition: .25s;
}
.sd-step--ready {
    border-color: #10b981;
    background: linear-gradient(135deg, #fff 0%, #ecfdf5 100%);
    box-shadow: 0 8px 24px rgba(16,185,129,.18);
}
.sd-step--ready::before {
    content: '✓ HAZIR';
    position: absolute; top: 14px; right: 14px;
    background: #10b981; color: #fff;
    padding: 4px 12px; border-radius: 20px;
    font-size: 10px; font-weight: 800; letter-spacing: .8px;
}
.sd-step--locked { opacity: .82; }

.sd-step-row {
    display: flex; gap: 16px; align-items: center;
    flex-wrap: wrap;
}
.sd-step-icon {
    width: 64px; height: 64px; border-radius: 16px;
    background: linear-gradient(135deg, #fde047, #f59e0b);
    color: #78350f;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; flex-shrink: 0;
    box-shadow: 0 6px 14px rgba(245,158,11,.25);
}
.sd-step-icon.tip-hizmet { background: linear-gradient(135deg, #93c5fd, #3b82f6); color: #fff; }
.sd-step-icon.tip-urun   { background: linear-gradient(135deg, #f9a8d4, #e11d48); color: #fff; }
.sd-step-icon.tip-hediye { background: linear-gradient(135deg, #fbbf24, #d97706); color: #fff; }

.sd-step-body { flex: 1; min-width: 180px; }
.sd-step-body h3 { margin: 0 0 4px; font-size: 17px; font-weight: 800; color: var(--sd-text); }
.sd-step-body p  { margin: 0; font-size: 13px; color: var(--sd-mute); line-height: 1.5; }

.sd-step-puan {
    text-align: center; padding: 8px 14px;
    background: linear-gradient(135deg, #fde047, #f59e0b);
    color: #78350f; border-radius: 14px; font-weight: 800;
    box-shadow: 0 4px 10px rgba(245,158,11,.25);
}
.sd-step-puan .rk { font-size: 18px; line-height: 1.1; }
.sd-step-puan .lb { font-size: 9px; letter-spacing: 1px; margin-top: 1px; }

.sd-step-progress { margin-top: 14px; }
.sd-step-progress-bar {
    height: 6px; background: #f3f4f6;
    border-radius: 3px; overflow: hidden;
}
.sd-step-progress-fill {
    height: 100%; border-radius: 3px;
    background: linear-gradient(90deg, #10b981, #3b82f6);
    transition: width .6s;
}
.sd-step-progress-txt {
    display: flex; justify-content: space-between;
    font-size: 11px; color: var(--sd-mute);
    margin-top: 5px; font-weight: 600;
}
.sd-step-act { margin-top: 14px; display: flex; align-items: center; gap: 12px; }
.sd-btn-talep {
    padding: 10px 22px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border: none; border-radius: 10px;
    font-weight: 800; font-size: 13px; cursor: pointer;
    transition: .2s; letter-spacing: .3px;
    box-shadow: 0 6px 14px rgba(16,185,129,.32);
}
.sd-btn-talep:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(16,185,129,.42);
}
.sd-btn-talep:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.sd-eksik {
    font-size: 12px; color: #dc2626; font-weight: 700;
}

/* Kuponlar */
.sd-kupon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.sd-kupon-card {
    background: #fff;
    border-radius: 16px; padding: 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,.05);
    border: 2px dashed #e5e7eb;
    transition: .25s;
    position: relative;
}
.sd-kupon-card:hover:not(.is-pasif) {
    transform: translateY(-3px);
    box-shadow: 0 12px 26px rgba(108,92,231,.18);
}
.sd-kupon-card.is-pasif { opacity: .55; filter: grayscale(.5); }
.sd-kupon-tag {
    position: absolute; top: 12px; right: 12px;
    padding: 4px 10px; border-radius: 12px;
    font-size: 10px; font-weight: 800; letter-spacing: .5px;
}
.sd-tag-aktif { background: #d1fae5; color: #065f46; }
.sd-tag-pasif { background: #fee2e2; color: #991b1b; }
.sd-tag-gecti { background: #f3f4f6; color: #6b7280; }

.sd-kupon-baslik {
    font-size: 18px; font-weight: 800;
    color: var(--sd-purple); margin: 6px 0 4px;
}
.sd-kupon-salon {
    font-size: 12px; color: var(--sd-mute); margin-bottom: 14px;
}
.sd-kupon-kod {
    text-align: center; padding: 10px 12px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    font-family: monospace; font-size: 18px;
    font-weight: 800; letter-spacing: 3px;
    border: 2px dashed #f59e0b;
    border-radius: 10px; margin-bottom: 8px;
    cursor: pointer; user-select: all;
}
.sd-kupon-tarih {
    font-size: 11px; color: var(--sd-mute);
    text-align: center; font-weight: 600;
}

.sd-empty {
    background: #fff; border-radius: 18px;
    padding: 60px 22px; text-align: center;
    border: 2px dashed var(--sd-border);
}
.sd-empty .ic {
    font-size: 56px; margin-bottom: 12px;
    filter: grayscale(.3);
}
.sd-empty p {
    color: var(--sd-mute); margin: 0; font-size: 14px;
    line-height: 1.6;
}
.sd-empty a {
    display: inline-block; margin-top: 16px;
    padding: 10px 24px; background: var(--sd-purple);
    color: #fff; border-radius: 50px;
    text-decoration: none; font-weight: 700;
}
.sd-empty a:hover { text-decoration: none; background: var(--sd-purple-d); }

/* Modal — Talep sonucu */
.sd-modal {
    display: none; position: fixed; inset: 0;
    z-index: 9999; backdrop-filter: blur(8px);
    background: rgba(0,0,0,.55);
    align-items: center; justify-content: center;
}
.sd-modal.is-open { display: flex; }
.sd-modal__box {
    background: #fff; border-radius: 24px;
    padding: 36px 28px; max-width: 420px; width: 92%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    animation: sdPop .45s cubic-bezier(.34,1.56,.64,1);
}
@keyframes sdPop { from { transform: scale(.7); opacity: 0 } to { transform: scale(1); opacity: 1 } }
.sd-modal .emj { font-size: 56px; margin-bottom: 12px; display: block; }
.sd-modal h2 { font-size: 20px; font-weight: 800; margin: 0 0 6px; }
.sd-modal p  { font-size: 13px; color: var(--sd-mute); margin: 0 0 14px; }
.sd-modal .kod {
    display: inline-block; padding: 10px 20px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; border-radius: 12px;
    font-family: monospace; font-size: 22px;
    font-weight: 800; letter-spacing: 4px;
    border: 2px dashed #f59e0b;
    margin-bottom: 14px;
}
.sd-modal__close {
    margin-top: 12px; padding: 12px 30px;
    background: var(--sd-purple); color: #fff;
    border: none; border-radius: 50px;
    font-weight: 700; cursor: pointer;
}

/* Toast */
#sdToast {
    position: fixed; top: 20px; right: 20px;
    z-index: 10001; padding: 14px 20px;
    background: #10b981; color: #fff;
    border-radius: 12px; font-weight: 700;
    box-shadow: 0 10px 30px rgba(0,0,0,.2);
    display: none;
}
#sdToast.show { display: block; }
#sdToast.err { background: #ef4444; }
</style>

<section class="block">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills" id="myTab-pills" role="tablist" style="text-align: center;">
                    <li class="nav-item">
                        <a class="nav-link icon" href="/profilim"><i class="fa fa-user" style="color:white"></i>Profilim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link icon" href="/randevularim">
                            <i class="fa fa-heart"></i>Randevularım
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active icon" href="/sadakat">
                            <i class="fa fa-star"></i>Sadakat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link icon" href="/ayarlarim">
                            <i class="fa fa-recycle"></i>Ayarlarım
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<div class="sd-page">

    {{-- ============== HERO ============== --}}
    <div class="sd-hero">
        <div class="sd-hero__top">
            <div>
                <div class="sd-hero__greet">Merhaba,</div>
                <h1 class="sd-hero__name">{{ Auth::user()->name ?? 'Değerli Müşteri' }}</h1>
            </div>
            <div class="sd-tier">
                <span class="sd-tier__dot" style="background:{{ $tier['renk'] }}; color:{{ $tier['renk'] }};"></span>
                <span>{{ $tier['ad'] }} Üye</span>
            </div>
        </div>

        <div class="sd-hero__main">
            <div class="sd-puan-ring">
                <span class="sd-puan-ring__rakam">{{ (int) $puanBakiyesi }}</span>
                <span class="sd-puan-ring__alt">Puan</span>
            </div>
            <div class="sd-hero__info">
                <h3>@if(!empty($aktifSalon)){{ $aktifSalon->salon_adi }} bakiyeniz @else Tüm salonlar @endif</h3>
                @if($tier['sonraki'])
                    @php
                        $sonrakiYuzde = $tier['sonrakiPuan'] ? min(100, ($toplamPuan / $tier['sonrakiPuan']) * 100) : 100;
                        $kalan = max(0, $tier['sonrakiPuan'] - $toplamPuan);
                    @endphp
                    <div class="sd-progress-next">
                        <div class="sd-progress-next__lbl">
                            <span>{{ $tier['sonraki'] }} üyeliğe</span>
                            <span><b>{{ $kalan }}</b> puan kaldı</span>
                        </div>
                        <div class="sd-progress-next__bar">
                            <div class="sd-progress-next__fill" style="width:{{ $sonrakiYuzde }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="sd-progress-next">
                        <div class="sd-progress-next__lbl"><span>🌟 En yüksek seviye!</span></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============== Salon seçici ============== --}}
    @if($puanKayitlari->count() > 1)
        <div class="sd-salons">
            @foreach($puanKayitlari as $pk)
                @php $s = $tumSalonlar->get($pk->salon_id); @endphp
                @if($s)
                    <a href="{{ url('/sadakat?salon='.$pk->salon_id) }}" class="{{ $aktifSalonId == $pk->salon_id ? 'active' : '' }}">
                        {{ $s->salon_adi }} <b>({{ (int) $pk->puan }})</b>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ============== Sekmeler ============== --}}
    <div class="sd-tabs">
        <button class="sd-tab is-active" data-target="ladder">🎖️ Ödül Merdiveni</button>
        <button class="sd-tab" data-target="kuponlar">🎁 Kuponlarım <span style="background:rgba(255,255,255,.25); padding:1px 8px; border-radius:50px; font-size:11px; margin-left:4px;">{{ $kuponlar->where('kullanildi', 0)->count() }}</span></button>
    </div>

    {{-- ============== Panel: Ödül Merdiveni ============== --}}
    <div class="sd-panel is-active" id="panel-ladder">
        @if(!$aktifSalon)
            <div class="sd-empty">
                <div class="ic">⭐</div>
                <p>Sadakat programını görüntülemek için bir salonu ziyaret edin.<br>Randevu aldığınızda otomatik olarak bağlanırsınız.</p>
                <a href="/">Salonları Keşfet</a>
            </div>
        @elseif($odulSeviyeleri->isEmpty())
            <div class="sd-empty">
                <div class="ic">🎖️</div>
                <p><b>{{ $aktifSalon->salon_adi }}</b> henüz puan ödülü tanımlamamış.<br>Salon yakında ödülleri tanıtacak — puan biriktirmeye başlayabilirsiniz!</p>
            </div>
        @else
            @if($puanBakiyesi == 0)
                <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius:14px; padding:14px 18px; margin-bottom:16px; color:#92400e; display:flex; gap:12px; align-items:center;">
                    <div style="font-size:28px;">💡</div>
                    <div>
                        <b>Henüz puanınız yok</b><br>
                        <span style="font-size:13px;">Çarkı çevirerek ya da randevu alarak puan biriktirebilirsiniz. Aşağıda biriktirme hedeflerinizi görebilirsiniz!</span>
                    </div>
                </div>
            @endif
            <div class="sd-ladder">
                @foreach($odulSeviyeleri as $o)
                    @php
                        $hazir = $puanBakiyesi >= $o->puan_esigi;
                        $yuzde = min(100, round(($puanBakiyesi / max(1, $o->puan_esigi)) * 100));
                        $eksik = max(0, $o->puan_esigi - $puanBakiyesi);
                        $tipCls = ['hizmet_indirimi'=>'tip-hizmet','urun_indirimi'=>'tip-urun','hediye'=>'tip-hediye'][$o->tip] ?? 'tip-hediye';
                        $tipIc  = ['hizmet_indirimi'=>'✂️','urun_indirimi'=>'🛍️','hediye'=>'🎁'][$o->tip] ?? '🏆';
                    @endphp
                    <div class="sd-step {{ $hazir ? 'sd-step--ready' : 'sd-step--locked' }}">
                        <div class="sd-step-row">
                            <div class="sd-step-icon {{ $tipCls }}">{{ $tipIc }}</div>
                            <div class="sd-step-body">
                                <h3>{{ $o->baslik }}</h3>
                                <p>{{ $o->aciklama ?: '—' }}</p>
                            </div>
                            <div class="sd-step-puan">
                                <div class="rk">{{ $o->puan_esigi }}</div>
                                <div class="lb">PUAN</div>
                            </div>
                        </div>
                        <div class="sd-step-progress">
                            <div class="sd-step-progress-bar">
                                <div class="sd-step-progress-fill" style="width:{{ $yuzde }}%"></div>
                            </div>
                            <div class="sd-step-progress-txt">
                                <span>{{ (int) $puanBakiyesi }} / {{ $o->puan_esigi }} puan</span>
                                <span>%{{ $yuzde }}</span>
                            </div>
                        </div>
                        <div class="sd-step-act">
                            @if($hazir)
                                <button class="sd-btn-talep" onclick="sdTalepEt({{ $o->id }}, {{ $o->puan_esigi }}, this)">🎁 Şimdi Talep Et</button>
                            @else
                                <span class="sd-eksik">🔒 {{ (int) $eksik }} puan daha gerekiyor</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============== Panel: Kuponlarım ============== --}}
    <div class="sd-panel" id="panel-kuponlar">
        @if($kuponlar->isEmpty())
            <div class="sd-empty">
                <div class="ic">🎫</div>
                <p>Henüz kazanılmış kuponunuz yok.<br>Çarkı çevirip ya da puan ödülü talep ederek kupon kazanabilirsiniz.</p>
            </div>
        @else
            <div class="sd-kupon-grid">
                @foreach($kuponlar as $k)
                    @php
                        $gecmis = $k->gecerlilik_tarihi && $k->gecerlilik_tarihi->isPast();
                        $pasif  = $k->kullanildi || $gecmis;
                        $ks = $tumSalonlar->get($k->salon_id) ?? \App\Salonlar::find($k->salon_id);
                    @endphp
                    <div class="sd-kupon-card {{ $pasif ? 'is-pasif' : '' }}">
                        @if($k->kullanildi)
                            <span class="sd-kupon-tag sd-tag-pasif">KULLANILDI</span>
                        @elseif($gecmis)
                            <span class="sd-kupon-tag sd-tag-gecti">SÜRESİ DOLDU</span>
                        @else
                            <span class="sd-kupon-tag sd-tag-aktif">GEÇERLİ</span>
                        @endif
                        <div class="sd-kupon-baslik">{{ $k->baslik }}</div>
                        <div class="sd-kupon-salon">{{ $ks->salon_adi ?? 'Salon' }}</div>
                        <div class="sd-kupon-kod" onclick="sdKopyala('{{ $k->kod }}', this)" title="Tıklayarak kopyala">{{ $k->kod }}</div>
                        <div class="sd-kupon-tarih">
                            @if($k->kullanildi && $k->kullanim_tarihi)
                                {{ $k->kullanim_tarihi->format('d.m.Y') }}'de kullanıldı
                            @elseif($k->gecerlilik_tarihi)
                                Son kullanım: {{ $k->gecerlilik_tarihi->format('d.m.Y') }}
                            @else
                                Süresiz geçerli
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Talep sonucu modal --}}
<div class="sd-modal" id="sdModal">
    <div class="sd-modal__box">
        <span class="emj">🎉</span>
        <h2>Ödülünüz Hazır!</h2>
        <p id="sdModalBaslik">—</p>
        <div class="kod" id="sdModalKod">—</div>
        <p style="font-size:12px; color:#9ca3af;">Bu kodu 60 gün içinde salonda ibraz edin.</p>
        <button class="sd-modal__close" onclick="document.getElementById('sdModal').classList.remove('is-open'); location.reload();">Tamam</button>
    </div>
</div>

<div id="sdToast"></div>

<script>
(function(){
    const TALEP_URL = '{{ route("cark.puanodul.talep") }}';
    const SALON_ID  = {{ $aktifSalonId ?? 0 }};
    const CSRF      = '{{ csrf_token() }}';

    function showToast(msg, err){
        const t = document.getElementById('sdToast');
        t.textContent = msg;
        t.className = err ? 'err show' : 'show';
        setTimeout(() => t.classList.remove('show'), 2800);
    }

    // Tab switching
    document.querySelectorAll('.sd-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.sd-tab').forEach(b => b.classList.remove('is-active'));
            document.querySelectorAll('.sd-panel').forEach(p => p.classList.remove('is-active'));
            btn.classList.add('is-active');
            document.getElementById('panel-' + btn.dataset.target).classList.add('is-active');
        });
    });

    window.sdTalepEt = async function(odulId, puanEsigi, btn){
        if (!confirm('Bu ödülü talep etmek istediğinize emin misiniz?\n' + puanEsigi + ' puan düşülecek ve kupon kodunuz oluşacak.')) return;
        btn.disabled = true; btn.textContent = '⏳ İşleniyor...';
        try {
            const resp = await fetch(TALEP_URL, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body: JSON.stringify({salon_id: SALON_ID, odul_id: odulId})
            });
            const data = await resp.json();
            if (!data.success){ showToast(data.message || 'Hata', true); btn.disabled = false; btn.textContent = '🎁 Şimdi Talep Et'; return; }
            document.getElementById('sdModalBaslik').textContent = data.baslik;
            document.getElementById('sdModalKod').textContent = data.kod || '—';
            document.getElementById('sdModal').classList.add('is-open');
        } catch(e){ showToast('Bağlantı hatası', true); btn.disabled = false; btn.textContent = '🎁 Şimdi Talep Et'; }
    };

    window.sdKopyala = function(kod, el){
        try {
            navigator.clipboard.writeText(kod);
            showToast('✓ Kod kopyalandı: ' + kod);
            el.style.transform = 'scale(.97)';
            setTimeout(() => el.style.transform = '', 150);
        } catch(e){
            const r = document.createRange(); r.selectNode(el); window.getSelection().removeAllRanges(); window.getSelection().addRange(r);
        }
    };
})();
</script>
@endsection
