@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $rol = Auth::guard('sistemyonetim')->user()->rol ?? (Auth::guard('sistemyonetim')->user()->admin == 1 ? 'super_admin' : 'destek');
@endphp

<div class="sy-page-head">
    <div>
        <h2>{{ $salon->salon_adi }}
            @if($salon->askiya_alindi)
                <span class="sy-badge sy-badge-danger">Askıda</span>
            @else
                <span class="sy-badge sy-badge-success">Aktif</span>
            @endif
        </h2>
        <div class="subtitle">
            {{ optional($salon->il)->il_adi }} / {{ optional($salon->ilce)->ilce_adi }} ·
            ID: {{ $salon->id }} ·
            Kayıt: {{ \Carbon\Carbon::parse($salon->created_at)->format('d.m.Y') }}
        </div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/salonlar" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Liste</a>
        <a href="/sistemyonetim/isletmedetay/{{ $salon->id }}" class="sy-btn sy-btn-soft" target="_blank">
            <span class="mdi mdi-cog"></span> Klasik Düzenle
        </a>
        <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/hesabina-gir" style="display:inline" onsubmit="return confirm('Salonun hesabına geçiş yapılacak. Tüm hareketleriniz loglanacaktır. Devam edilsin mi?');">
            @csrf
            <input type="hidden" name="sebep" value="Destek girişi">
            <button type="submit" class="sy-btn sy-btn-primary" {{ $salon->askiya_alindi ? 'disabled title=\'Salon askıda\'' : '' }}>
                <span class="mdi mdi-login"></span> Salonun Hesabına Gir
            </button>
        </form>
    </div>
</div>

@if($salon->askiya_alindi)
    <div class="sy-alert sy-alert-warning">
        <strong>Bu salon askıda.</strong>
        @if($salon->askiya_alma_sebebi) Sebep: {{ $salon->askiya_alma_sebebi }} @endif
        @if(in_array($rol, ['super_admin','yonetici']))
            <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/aktif-et" style="display:inline; margin-left:10px">
                @csrf
                <button class="sy-btn sy-btn-sm sy-btn-success">Aktif Et</button>
            </form>
        @endif
    </div>
@endif

@php
    $skorRenk = ['kritik'=>'danger','riskli'=>'warning','orta'=>'info','iyi'=>'success'];
    $sR = $skorRenk[$saglik['durum']] ?? 'muted';
@endphp

<div class="sy-card sy-mt-12" style="border-left:4px solid var(--sy-{{ $sR }})">
    <div class="sy-card-body">
        <div class="sy-flex-row" style="justify-content:space-between;align-items:center">
            <div>
                <div class="sy-text-muted sy-fs-12" style="text-transform:uppercase;letter-spacing:0.5px">Sağlık Skoru</div>
                <div style="display:flex;align-items:baseline;gap:10px;margin-top:2px">
                    <span style="font-size:36px;font-weight:700;color:var(--sy-{{ $sR }})">{{ $saglik['skor'] }}</span>
                    <span class="sy-text-muted">/100</span>
                    <span class="sy-badge sy-badge-{{ $sR }}">{{ $saglik['durum'] }}</span>
                </div>
            </div>
            <div style="flex:1;max-width:380px;margin-left:24px">
                <div class="sy-progress" style="height:10px"><div class="fill" style="width:{{ $saglik['skor'] }}%;background:var(--sy-{{ $sR }})"></div></div>
                @if(!empty($saglik['sebepler']))
                    <ul class="sy-text-muted sy-fs-12" style="margin:8px 0 0 18px;padding:0">
                        @foreach($saglik['sebepler'] as $s)
                            <li>{{ $s }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="sy-text-muted sy-fs-12 sy-mt-12">Salon sağlıklı çalışıyor.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="sy-metric-grid sy-mt-12">
    <div class="sy-metric"><div class="icon-bg mdi mdi-calendar-multiple"></div><div class="label">Toplam Randevu</div><div class="value">{{ $istatistik['toplam_randevu'] }}</div></div>
    <div class="sy-metric info"><div class="icon-bg mdi mdi-calendar-month"></div><div class="label">Bu Ay</div><div class="value">{{ $istatistik['bu_ay_randevu'] }}</div></div>
    <div class="sy-metric success"><div class="icon-bg mdi mdi-account-multiple-plus"></div><div class="label">Bu Ay Yeni Müşteri</div><div class="value">{{ $istatistik['bu_ay_yeni_musteri'] }}</div></div>
    <div class="sy-metric {{ $istatistik['whatsapp_aktif'] ? 'success' : '' }}"><div class="icon-bg mdi mdi-whatsapp"></div><div class="label">WhatsApp</div><div class="value">{{ $istatistik['whatsapp_aktif'] ? 'Aktif' : 'Pasif' }}</div></div>
</div>

<div class="sy-grid-2-1">
    <div class="sy-stack">
        <!-- Salon notlari -->
        <div class="sy-card">
            <div class="sy-card-head">
                <h3><span class="mdi mdi-note-multiple"></span> İç Notlar</h3>
                <span class="sy-text-muted sy-fs-12">{{ $notlar->count() }} not</span>
            </div>
            <div class="sy-card-body">
                <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/not" class="sy-stack" style="margin-bottom:16px">
                    @csrf
                    <div class="sy-form-row">
                        <div class="sy-form-group" style="margin:0">
                            <label>Başlık (opsiyonel)</label>
                            <input type="text" name="baslik" class="sy-input" placeholder="Kısa başlık">
                        </div>
                        <div class="sy-form-group" style="margin:0">
                            <label>Tip</label>
                            <select name="tip" class="sy-select">
                                <option value="genel">Genel</option>
                                <option value="uyari">Uyarı</option>
                                <option value="onemli">Önemli</option>
                                <option value="sikayet">Şikayet</option>
                                <option value="talep">Özellik Talebi</option>
                                <option value="odeme">Ödeme</option>
                            </select>
                        </div>
                    </div>
                    <div class="sy-form-group" style="margin:0">
                        <textarea name="icerik" class="sy-textarea" rows="3" placeholder="Salon hakkında not..." required></textarea>
                    </div>
                    <div class="sy-flex-row" style="justify-content: space-between">
                        <label class="sy-fs-13" style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="pinned" value="1"> Sabitle</label>
                        <button class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Not Ekle</button>
                    </div>
                </form>

                @if($notlar->isEmpty())
                    <div class="sy-empty"><div class="icon mdi mdi-note-outline"></div><div class="baslik">Henüz not yok</div></div>
                @else
                    @foreach($notlar as $n)
                        <div class="sy-not-card tip-{{ $n->tip }} {{ $n->pinned ? 'pinned' : '' }}">
                            <div class="head">
                                <div>
                                    <strong>{{ $n->user_name }}</strong>
                                    · <span class="sy-badge sy-badge-muted">{{ $n->tip }}</span>
                                    @if($n->pinned)<span class="sy-badge sy-badge-warning"><span class="mdi mdi-pin"></span> Sabit</span>@endif
                                </div>
                                <div class="sy-text-soft">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</div>
                            </div>
                            @if($n->baslik)<div class="baslik">{{ $n->baslik }}</div>@endif
                            <div>{!! nl2br(e($n->icerik)) !!}</div>
                            <div class="sy-flex-row sy-mt-12" style="justify-content:flex-end">
                                <a href="/sistemyonetim/v2/not/{{ $n->id }}/pin" class="sy-btn sy-btn-sm">{{ $n->pinned ? 'Sabitten Kaldır' : 'Sabitle' }}</a>
                                @if($rol === 'super_admin' || $n->user_id == Auth::guard('sistemyonetim')->user()->id)
                                    <form method="post" action="/sistemyonetim/v2/not/{{ $n->id }}" onsubmit="return confirm('Not silinsin mi?')">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="sy-btn sy-btn-sm sy-btn-danger"><span class="mdi mdi-delete"></span></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Salon ticketlari -->
        <div class="sy-card">
            <div class="sy-card-head">
                <h3><span class="mdi mdi-lifebuoy"></span> Bu Salonun Talepleri</h3>
                <a href="/sistemyonetim/v2/ticket/yeni?salon_id={{ $salon->id }}" class="sy-btn sy-btn-sm sy-btn-soft"><span class="mdi mdi-plus"></span></a>
            </div>
            <div class="sy-card-body tight">
                @forelse($ticketlar as $t)
                    <a href="/sistemyonetim/v2/ticket/{{ $t->id }}" style="display:block; padding:12px 18px; border-bottom:1px solid var(--sy-border); color:var(--sy-text)">
                        <div class="sy-flex-row" style="justify-content:space-between">
                            <strong>{{ $t->numara }}</strong>
                            <span class="sy-badge sy-badge-{{ $t->durum=='cozumlendi'||$t->durum=='kapali' ? 'success' : ($t->oncelik=='acil'?'danger':'info') }}">{{ $t->durum }}</span>
                        </div>
                        <div class="sy-fs-13">{{ \Illuminate\Support\Str::limit($t->konu, 70) }}</div>
                        <div class="sy-text-muted sy-fs-12">{{ \Carbon\Carbon::parse($t->created_at)->format('d.m.Y H:i') }}</div>
                    </a>
                @empty
                    <div class="sy-empty"><div class="baslik">Talep yok</div></div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="sy-stack">
        <!-- Iletisim -->
        <div class="sy-card">
            <div class="sy-card-head"><h3><span class="mdi mdi-card-account-details"></span> İletişim</h3></div>
            <div class="sy-card-body">
                <div class="sy-stack" style="gap:8px">
                    <div><span class="sy-text-muted sy-fs-12">Yetkili Adı</span><div class="sy-fw-600">{{ $salon->yetkili_adi ?: '—' }}</div></div>
                    <div><span class="sy-text-muted sy-fs-12">Yetkili Telefon</span><div>{{ $salon->yetkili_telefon ?: '—' }}</div></div>
                    <div><span class="sy-text-muted sy-fs-12">İşletme Telefon</span><div>{{ $salon->telefon_1 ?: '—' }}</div></div>
                    <div><span class="sy-text-muted sy-fs-12">Adres</span><div class="sy-fs-13">{{ $salon->adres ?: '—' }}</div></div>
                </div>
            </div>
        </div>

        <!-- Yetkililer -->
        <div class="sy-card">
            <div class="sy-card-head"><h3><span class="mdi mdi-account-key"></span> İşletme Yetkilileri</h3></div>
            <div class="sy-card-body tight">
                @forelse($yetkililer as $y)
                    <div style="padding:12px 18px; border-bottom:1px solid var(--sy-border)">
                        <div class="sy-fw-600">{{ $y->name }} @if($y->is_admin)<span class="sy-badge sy-badge-info">Admin</span>@endif</div>
                        <div class="sy-text-muted sy-fs-12">{{ $y->email }}</div>
                    </div>
                @empty
                    <div class="sy-empty"><div class="baslik">Yetkili yok</div></div>
                @endforelse
            </div>
        </div>

        @if(in_array($rol, ['super_admin','yonetici']))
        <!-- Mt atama -->
        <div class="sy-card">
            <div class="sy-card-head"><h3><span class="mdi mdi-account-tie"></span> Müşteri Temsilcisi</h3></div>
            <div class="sy-card-body">
                <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/mt-ata">
                    @csrf
                    <div class="sy-form-group">
                        <select name="musteri_yetkili_id" class="sy-select">
                            <option value="">Atama yok</option>
                            @foreach($musteriTemsilcileri as $mt)
                                <option value="{{ $mt->id }}" {{ $salon->musteri_yetkili_id==$mt->id?'selected':'' }}>{{ $mt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="sy-btn sy-btn-primary sy-btn-sm">Kaydet</button>
                </form>
            </div>
        </div>

        <!-- Askiya al -->
        @if(!$salon->askiya_alindi)
        <div class="sy-card">
            <div class="sy-card-head"><h3 style="color:var(--sy-danger)"><span class="mdi mdi-cancel"></span> Salonu Askıya Al</h3></div>
            <div class="sy-card-body">
                <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/askiya-al" onsubmit="return confirm('Salon askıya alınacak. Hesaba giriş engellenecek. Devam?')">
                    @csrf
                    <textarea name="sebep" class="sy-textarea" rows="2" placeholder="Sebep" required></textarea>
                    <button class="sy-btn sy-btn-danger sy-mt-12 sy-btn-sm">Askıya Al</button>
                </form>
            </div>
        </div>
        @endif
        @endif

        <!-- Hesabina giris log -->
        <div class="sy-card">
            <div class="sy-card-head"><h3><span class="mdi mdi-history"></span> Son Hesap Girişleri</h3></div>
            <div class="sy-card-body tight">
                @forelse($impersonationGecmisi as $i)
                    <div style="padding:10px 18px; border-bottom:1px solid var(--sy-border); font-size:12.5px">
                        <strong>{{ $i->user_name }}</strong> · {{ \Carbon\Carbon::parse($i->baslangic_tarihi)->format('d.m.Y H:i') }}
                        <div class="sy-text-muted sy-fs-12">{{ $i->sebep ?: '—' }}</div>
                    </div>
                @empty
                    <div class="sy-empty"><div class="baslik">Henüz giriş yok</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
