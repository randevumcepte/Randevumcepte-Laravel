@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $rol = Auth::guard('sistemyonetim')->user()->rol ?? (Auth::guard('sistemyonetim')->user()->admin == 1 ? 'super_admin' : 'destek');
    $duzenleyebilir = in_array($rol, ['super_admin', 'yonetici']);

    // Sifir tarih (0000-00-00) ve bozuk degerlerde Carbon::parse 500 atiyor — guvenli formatlayicilar
    $fmtTarih = function ($v, $fmt = 'd.m.Y H:i') {
        if (empty($v)) return '—';
        if (substr((string) $v, 0, 4) === '0000') return '—';
        try { return \Carbon\Carbon::parse($v)->format($fmt); } catch (\Throwable $e) { return '—'; }
    };
    $diffTarih = function ($v) {
        if (empty($v)) return '';
        if (substr((string) $v, 0, 4) === '0000') return '';
        try { return \Carbon\Carbon::parse($v)->diffForHumans(); } catch (\Throwable $e) { return ''; }
    };
@endphp

<div class="sy-page-head">
    <div>
        <h2 style="font-size:28px;font-weight:800;line-height:1.15;margin-bottom:2px">{{ $salon->salon_adi }}
            @if($salon->askiya_alindi)
                <span class="sy-badge sy-badge-danger">Askıda</span>
            @else
                <span class="sy-badge sy-badge-success">Aktif</span>
            @endif
        </h2>
        @php
            $telLink = preg_replace('/[^0-9+]/', '', (string) ($iletisimTel ?? ''));
        @endphp
        @if(!empty($iletisimAd) || !empty($iletisimTel))
        <div style="margin-top:6px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            @if(!empty($iletisimAd))
                <span style="font-size:16px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
                    <span class="mdi mdi-account"></span>{{ $iletisimAd }}
                </span>
            @endif
            @if(!empty($iletisimTel))
                <a href="tel:{{ $telLink }}" style="font-size:20px;font-weight:700;color:var(--sy-primary);text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    <span class="mdi mdi-phone"></span>{{ $iletisimTel }}
                </a>
            @endif
        </div>
        @endif
        <div class="subtitle" style="margin-top:6px">
            {{ optional($salon->il)->il_adi }} / {{ optional($salon->ilce)->ilce_adi }} ·
            ID: {{ $salon->id }} ·
            Kayıt: {{ $fmtTarih($salon->created_at, 'd.m.Y') }}
        </div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/salonlar" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Liste</a>
        <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/hesabina-gir" style="display:inline">
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

@if($duzenleyebilir)
@php
    $ub = $salon->uyelik_bitis_tarihi;
    $ubGecerli = $ub && substr((string) $ub, 0, 4) !== '0000';
    $kalanGun = $ubGecerli ? (int) floor((strtotime($ub . ' 23:59:59') - time()) / 86400) : null;
    $ubRenk = !$ubGecerli ? 'muted' : ($kalanGun < 0 ? 'danger' : ($kalanGun <= 7 ? 'warning' : 'success'));
@endphp
<div class="sy-card sy-mt-12" style="border-left:4px solid var(--sy-{{ $ubRenk }})">
    <div class="sy-card-body">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:24px;flex-wrap:wrap">
            <div style="min-width:200px">
                <div class="sy-text-muted sy-fs-12" style="text-transform:uppercase;letter-spacing:.5px">
                    <span class="mdi mdi-clock-outline"></span> Demo / Üyelik Bitişi
                </div>
                <div style="display:flex;align-items:baseline;gap:10px;margin-top:2px">
                    <span style="font-size:26px;font-weight:800;color:var(--sy-{{ $ubRenk }})">
                        {{ $ubGecerli ? \Carbon\Carbon::parse($ub)->format('d.m.Y') : '— tanımsız' }}
                    </span>
                    @if($ubGecerli)
                        <span class="sy-badge sy-badge-{{ $ubRenk }}">
                            @if($kalanGun < 0) {{ abs($kalanGun) }} gün önce doldu
                            @elseif($kalanGun === 0) bugün doluyor
                            @else {{ $kalanGun }} gün kaldı @endif
                        </span>
                    @endif
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                @foreach([['gun'=>7,'etiket'=>'+7 gün','stil'=>'soft'], ['gun'=>15,'etiket'=>'+15 gün','stil'=>'soft'], ['gun'=>30,'etiket'=>'+30 gün','stil'=>'primary'], ['gun'=>90,'etiket'=>'+90 gün','stil'=>'soft'], ['gun'=>365,'etiket'=>'+1 yıl','stil'=>'soft']] as $qs)
                    <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/sure-uzat" style="display:inline">
                        @csrf
                        <input type="hidden" name="gun" value="{{ $qs['gun'] }}">
                        <button type="submit" class="sy-btn sy-btn-sm sy-btn-{{ $qs['stil'] }}">{{ $qs['etiket'] }}</button>
                    </form>
                @endforeach
                <span class="sy-text-muted" style="opacity:.4">|</span>
                <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/sure-uzat" style="display:flex;gap:6px;align-items:center">
                    @csrf
                    <input type="date" name="tarih" class="sy-input" style="width:150px" value="{{ $ubGecerli ? \Carbon\Carbon::parse($ub)->format('Y-m-d') : '' }}">
                    <button type="submit" class="sy-btn sy-btn-sm sy-btn-success">Ayarla</button>
                </form>
            </div>
        </div>
    </div>
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

@if($duzenleyebilir)
<!-- Isletme bilgileri — duzenlenebilir -->
<div class="sy-card sy-mt-12">
    <div class="sy-card-head">
        <h3><span class="mdi mdi-store-edit"></span> İşletme Bilgileri</h3>
        <span class="sy-text-muted sy-fs-12">Tüm temel bilgileri buradan güncelleyebilirsiniz</span>
    </div>
    <div class="sy-card-body">
        <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/bilgi-guncelle">
            @csrf

            <div class="sy-form-group">
                <label>Salon Adı</label>
                <input type="text" name="salon_adi" class="sy-input" value="{{ $salon->salon_adi }}" required>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İl</label>
                    <select name="il_id" id="sy-il-select" class="sy-select">
                        <option value="">Seçiniz</option>
                        @foreach($iller as $il)
                            <option value="{{ $il->id }}" {{ $salon->il_id == $il->id ? 'selected' : '' }}>{{ $il->il_adi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sy-form-group">
                    <label>İlçe</label>
                    <select name="ilce_id" id="sy-ilce-select" class="sy-select">
                        <option value="">Seçiniz</option>
                    </select>
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İşletme Türü</label>
                    <select name="salon_turu_id" class="sy-select">
                        <option value="">Seçiniz</option>
                        @foreach($salonTurleri as $st)
                            <option value="{{ $st->id }}" {{ $salon->salon_turu_id == $st->id ? 'selected' : '' }}>{{ $st->salon_turu_adi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sy-form-group">
                    <label>Domain <span class="sy-text-muted sy-fs-12">(online randevu mini web sitesi)</span></label>
                    <input type="text" name="domain" class="sy-input" value="{{ $salon->domain }}" placeholder="ornek.randevumcepte.com.tr">
                </div>
            </div>

            <div class="sy-form-group">
                <label>Adres</label>
                <textarea name="adres" class="sy-textarea" rows="2">{{ $salon->adres }}</textarea>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İşletme Telefon 1</label>
                    <input type="text" name="telefon_1" class="sy-input" value="{{ $salon->telefon_1 }}">
                </div>
                <div class="sy-form-group">
                    <label>İşletme Telefon 2</label>
                    <input type="text" name="telefon_2" class="sy-input" value="{{ $salon->telefon_2 }}">
                </div>
                <div class="sy-form-group">
                    <label>İşletme Telefon 3</label>
                    <input type="text" name="telefon_3" class="sy-input" value="{{ $salon->telefon_3 }}">
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Yetkili Adı</label>
                    <input type="text" name="yetkili_adi" class="sy-input" value="{{ $salon->yetkili_adi }}">
                </div>
                <div class="sy-form-group">
                    <label>Yetkili Telefon</label>
                    <input type="text" name="yetkili_telefon" class="sy-input" value="{{ $salon->yetkili_telefon }}">
                </div>
                <div class="sy-form-group">
                    <label>Yetkili Mail</label>
                    <input type="email" name="yetkili_mail" class="sy-input" value="{{ $salon->yetkili_mail }}">
                </div>
            </div>

            <div class="sy-form-group">
                <label>Açıklama</label>
                <textarea name="aciklama" class="sy-textarea" rows="2">{{ $salon->aciklama }}</textarea>
            </div>

            <div class="sy-divider" style="margin:18px 0;border-top:1px solid var(--sy-border)"></div>
            <div class="sy-text-muted sy-fs-12" style="text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">Mobil Uygulama Linkleri</div>

            <div class="sy-form-group">
                <label><span class="mdi mdi-android"></span> Android Uygulama Linki</label>
                <input type="text" name="android_uygulama" class="sy-input" value="{{ $salon->android_uygulama }}" placeholder="https://play.google.com/...">
            </div>
            <div class="sy-form-group">
                <label><span class="mdi mdi-apple"></span> iOS Uygulama Linki</label>
                <input type="text" name="ios_uygulama" class="sy-input" value="{{ $salon->ios_uygulama }}" placeholder="https://apps.apple.com/...">
            </div>
            <div class="sy-form-group">
                <label><span class="mdi mdi-cellphone"></span> Huawei Uygulama Linki</label>
                <input type="text" name="huawei_uygulama" class="sy-input" value="{{ $salon->huawei_uygulama }}" placeholder="https://appgallery.huawei.com/...">
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Android Son Versiyon</label>
                    <input type="text" name="android_son_versiyon" class="sy-input" maxlength="5" value="{{ $salon->android_son_versiyon }}" placeholder="1.0.0">
                </div>
                <div class="sy-form-group">
                    <label>iOS Son Versiyon</label>
                    <input type="text" name="ios_son_versiyon" class="sy-input" maxlength="5" value="{{ $salon->ios_son_versiyon }}" placeholder="1.0.0">
                </div>
                <div class="sy-form-group">
                    <label>Huawei Son Versiyon</label>
                    <input type="text" name="huawei_son_versiyon" class="sy-input" maxlength="5" value="{{ $salon->huawei_son_versiyon }}" placeholder="1.0.0">
                </div>
            </div>

            <div class="sy-flex-row" style="justify-content:flex-end;margin-top:8px">
                <button class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Bilgileri Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endif

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
                                <div class="sy-text-soft">{{ $diffTarih($n->created_at) }}</div>
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
                        <div class="sy-text-muted sy-fs-12">{{ $fmtTarih($t->created_at, 'd.m.Y H:i') }}</div>
                    </a>
                @empty
                    <div class="sy-empty"><div class="baslik">Talep yok</div></div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="sy-stack">
        @unless($duzenleyebilir)
        <!-- Iletisim (salt okunur — duzenleme yetkisi olmayan roller icin) -->
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
        @endunless

        <!-- Yetkililer / Personel hesaplari -->
        <div class="sy-card">
            <div class="sy-card-head"><h3><span class="mdi mdi-account-key"></span> İşletme & Personel Hesapları</h3></div>
            <div class="sy-card-body tight">
                @forelse($yetkililer as $y)
                    <div style="padding:12px 18px; border-bottom:1px solid var(--sy-border)">
                        <div class="sy-flex-row" style="justify-content:space-between;align-items:center;gap:10px">
                            <div style="min-width:0">
                                <div class="sy-fw-600">
                                    {{ $y->name }}
                                    @if($y->is_admin)
                                        <span class="sy-badge sy-badge-info">Sahip / Admin</span>
                                    @else
                                        <span class="sy-badge sy-badge-muted">Personel</span>
                                    @endif
                                </div>
                                <div class="sy-text-muted sy-fs-12">{{ $y->email ?: '—' }}</div>
                                @php $tel = $y->gsm1 ?: ($y->gsm2 ?: $y->telefon); @endphp
                                <div class="sy-text-muted sy-fs-12">
                                    <span class="mdi mdi-phone"></span>
                                    @if($tel)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $tel) }}" style="color:inherit">{{ $tel }}</a>@else — @endif
                                </div>
                            </div>
                            <form method="post" action="/sistemyonetim/v2/salon/{{ $salon->id }}/hesabina-gir" style="margin:0;flex-shrink:0"
                                  onsubmit="return confirm('Bu {{ $y->is_admin ? 'sahip' : 'personel' }} hesabına geçiş yapılacak. Tüm hareketleriniz loglanacaktır. Devam edilsin mi?');">
                                @csrf
                                <input type="hidden" name="yetkili_id" value="{{ $y->id }}">
                                <input type="hidden" name="sebep" value="{{ $y->is_admin ? 'Sahip hesabı girişi' : 'Personel hesabı girişi' }}">
                                <button type="submit" class="sy-btn sy-btn-sm sy-btn-soft" {{ $salon->askiya_alindi ? 'disabled title=\'Salon askıda\'' : '' }}>
                                    <span class="mdi mdi-login"></span> Bu hesapla gir
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="sy-empty"><div class="baslik">Hesap yok</div></div>
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
                        <strong>{{ $i->user_name }}</strong> · {{ $fmtTarih($i->baslangic_tarihi, 'd.m.Y H:i') }}
                        <div class="sy-text-muted sy-fs-12">{{ $i->sebep ?: '—' }}</div>
                    </div>
                @empty
                    <div class="sy-empty"><div class="baslik">Henüz giriş yok</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if($duzenleyebilir)
<script>
(function () {
    var ilceData = {!! json_encode($ilceler->map(function ($i) { return ['id' => (int) $i->id, 'il_id' => (int) $i->il_id, 'ad' => $i->ilce_adi]; })->values()) !!};
    var ilSel = document.getElementById('sy-il-select');
    var ilceSel = document.getElementById('sy-ilce-select');
    var seciliIlce = {{ (int) ($salon->ilce_id ?? 0) }};
    if (!ilSel || !ilceSel) return;

    function doldur() {
        var il = parseInt(ilSel.value, 10) || 0;
        ilceSel.innerHTML = '<option value="">Seçiniz</option>';
        for (var k = 0; k < ilceData.length; k++) {
            var d = ilceData[k];
            if (d.il_id === il) {
                var o = document.createElement('option');
                o.value = d.id;
                o.textContent = d.ad;
                if (d.id === seciliIlce) o.selected = true;
                ilceSel.appendChild(o);
            }
        }
    }
    ilSel.addEventListener('change', function () { seciliIlce = 0; doldur(); });
    doldur();
})();
</script>
@endif

@endsection
