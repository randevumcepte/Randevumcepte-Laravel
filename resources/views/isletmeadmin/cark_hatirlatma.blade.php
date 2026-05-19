@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')

<style>
:root {
    --hat-purple: #5C008E;
    --hat-purple-mid: #7B2FB8;
    --hat-purple-light: #9D5DC8;
}
.hat-wrap { padding: 24px; }
.hat-wrap * { box-sizing: border-box; }

.hat-hero {
    background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
    color: #fff; border-radius: 16px;
    padding: 26px 28px; margin-bottom: 22px;
    box-shadow: 0 14px 36px rgba(92, 0, 142, 0.28);
    position: relative; overflow: hidden;
}
.hat-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:200px; height:200px; border-radius:50%;
    background: radial-gradient(circle, rgba(255,255,255,.12), transparent 70%);
}
.hat-hero h1 { margin:0 0 6px; font-size:22px; font-weight:800; letter-spacing:-.3px; position:relative; z-index:1; }
.hat-hero p  { margin:0; opacity:.92; font-size:13px; max-width: 720px; position:relative; z-index:1; }

/* Master toggle row */
.hat-master {
    background: #fff; border-radius: 14px;
    padding: 18px 22px; margin-bottom: 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,.05);
    border: 1px solid #ede5f5;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 14px;
}
.hat-master-info b { font-size: 15px; color: #2d1b4e; }
.hat-master-info p {
    margin: 4px 0 0; font-size: 12px; color: #6b7280;
}
.hat-master-info .stat {
    display: inline-block; margin-top: 6px;
    padding: 4px 12px; background: #ede5f5;
    color: var(--hat-purple); border-radius: 20px;
    font-size: 12px; font-weight: 700;
}

/* Toggle */
.hat-toggle { position: relative; width: 56px; height: 30px; cursor: pointer; flex-shrink: 0; }
.hat-toggle input { opacity: 0; width: 0; height: 0; }
.hat-toggle .slider {
    position: absolute; inset: 0;
    background: #d1d5db; border-radius: 30px; transition: .25s;
}
.hat-toggle .slider::before {
    content: ''; position: absolute;
    width: 24px; height: 24px;
    left: 3px; top: 3px; background: #fff;
    border-radius: 50%; transition: .25s;
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
}
.hat-toggle input:checked + .slider {
    background: linear-gradient(135deg, var(--hat-purple), var(--hat-purple-light));
}
.hat-toggle input:checked + .slider::before { transform: translateX(26px); }

/* Saat kartları grid */
.hat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px; margin-bottom: 18px;
}
.hat-card {
    background: #fff; border-radius: 14px;
    padding: 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,.05);
    border: 1px solid #ede5f5;
    transition: .2s;
}
.hat-card:hover { box-shadow: 0 8px 22px rgba(92, 0, 142, .12); }
.hat-card__head {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 12px; padding-bottom: 10px;
    border-bottom: 1px solid #f3f0fa;
}
.hat-card__head > .hat-card__txt { flex: 1; min-width: 0; }
.hat-card.is-pasif { opacity: .55; }
.hat-card.is-pasif input, .hat-card.is-pasif textarea { background: #f6f6f6; }
.hat-toggle--mini { width: 44px !important; height: 24px !important; }
.hat-toggle--mini .slider::before { width: 18px !important; height: 18px !important; top: 3px !important; left: 3px !important; }
.hat-toggle--mini input:checked + .slider::before { transform: translateX(20px) !important; }
.hat-card__icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, var(--hat-purple), var(--hat-purple-light));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.hat-card__title {
    font-size: 14px; font-weight: 700; color: #2d1b4e;
    flex: 1;
}
.hat-card__sub { font-size: 11px; color: #9ca3af; }

.hat-field { margin-bottom: 10px; }
.hat-field label {
    display: block; font-size: 11px; font-weight: 700;
    color: #6b7280; margin-bottom: 5px;
    text-transform: uppercase; letter-spacing: .3px;
}
.hat-field input[type="time"],
.hat-field textarea {
    width: 100%; padding: 10px 12px;
    border: 2px solid #ede5f5;
    border-radius: 9px; background: #fff;
    font-size: 13px; transition: .2s;
}
.hat-field input[type="time"]:focus,
.hat-field textarea:focus {
    outline: none; border-color: var(--hat-purple-mid);
    box-shadow: 0 0 0 4px rgba(123, 47, 184, .12);
}
.hat-field textarea { resize: vertical; min-height: 60px; }

/* Günler bölümü */
.hat-days {
    background: #fff; border-radius: 14px;
    padding: 18px 22px; margin-bottom: 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,.05);
    border: 1px solid #ede5f5;
}
.hat-days h4 {
    margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #2d1b4e;
}
.hat-days p {
    margin: 0 0 12px; font-size: 12px; color: #9ca3af;
}
.hat-days__list { display: flex; gap: 8px; flex-wrap: wrap; }
.hat-days__chip {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px; background: #f5f0fc;
    border: 2px solid transparent;
    border-radius: 10px; font-size: 13px;
    cursor: pointer; transition: .15s;
    color: #5b3a8c; font-weight: 600;
}
.hat-days__chip input { display: none; }
.hat-days__chip.is-checked {
    background: linear-gradient(135deg, var(--hat-purple), var(--hat-purple-light));
    color: #fff; border-color: var(--hat-purple);
}

/* Save button */
.hat-save {
    padding: 14px 32px;
    background: linear-gradient(135deg, var(--hat-purple), var(--hat-purple-mid));
    color: #fff; border: none; border-radius: 12px;
    font-weight: 800; font-size: 15px; cursor: pointer;
    transition: .25s; letter-spacing: .3px;
    box-shadow: 0 8px 20px rgba(92, 0, 142, .35);
}
.hat-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(92, 0, 142, .45);
}
.hat-save:disabled { opacity: .55; cursor: not-allowed; transform: none; }

/* Toast */
#hatToast {
    position: fixed; top: 20px; right: 20px;
    z-index: 10000; padding: 14px 22px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border-radius: 12px;
    font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,.2);
    display: none;
}
#hatToast.show { display: block; }
#hatToast.err { background: linear-gradient(135deg, #ef4444, #dc2626); }
</style>

<div class="hat-wrap">
    @include('partials.carkifelek_tabs')
    <div class="hat-hero">
        <h1>⏰ Çarkıfelek Hatırlatma Sistemi</h1>
        <p>Müşterilerinize gün içinde 1 ile 4 arası push bildirimi gönderir, çarkı çevirmelerini hatırlatır. Tıklayan müşteri otomatik olarak çark popup'ına yönlenir. Aşağıdan her hatırlatmayı ayrı ayrı açıp kapatabilirsiniz.</p>
    </div>

    <div class="hat-master">
        <div class="hat-master-info">
            <b>Hatırlatma Sistemi</b>
            <p>Aktif olduğunda, açtığınız hatırlatmalar belirlenen saatlerde push olarak gönderilir.</p>
            <span class="stat">Bugün gönderilen: <b id="hat-bugun">{{ $bugun }}</b></span>
        </div>
        <label class="hat-toggle">
            <input type="checkbox" id="hat-aktif" {{ $ayar->aktif ? 'checked' : '' }}>
            <span class="slider"></span>
        </label>
    </div>

    <div class="hat-grid">
        @php
            $aktif1   = isset($ayar->aktif_1)   ? (int)$ayar->aktif_1   : 1;
            $aktif2   = isset($ayar->aktif_2)   ? (int)$ayar->aktif_2   : 1;
            $aktif3   = isset($ayar->aktif_3)   ? (int)$ayar->aktif_3   : 1;
            $aktifSon = isset($ayar->aktif_son) ? (int)$ayar->aktif_son : 1;
        @endphp

        <div class="hat-card {{ $aktif1 ? '' : 'is-pasif' }}" id="hat-card-1">
            <div class="hat-card__head">
                <div class="hat-card__icon">🎡</div>
                <div class="hat-card__txt">
                    <div class="hat-card__title">1. Hatırlatma — Yumuşak</div>
                    <div class="hat-card__sub">Sabah, ilk hatırlatma</div>
                </div>
                <label class="hat-toggle hat-toggle--mini" title="Bu hatırlatmayı aç/kapat">
                    <input type="checkbox" class="hat-asama-aktif" data-asama="1" id="hat-aktif-1" {{ $aktif1 ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="hat-field">
                <label>Saat</label>
                <input type="time" id="hat-saat-1" value="{{ substr($ayar->saat_1, 0, 5) }}">
            </div>
            <div class="hat-field">
                <label>Mesaj</label>
                <textarea id="hat-mesaj-1" rows="2" maxlength="200">{{ $ayar->mesaj_1 }}</textarea>
            </div>
        </div>
        <div class="hat-card {{ $aktif2 ? '' : 'is-pasif' }}" id="hat-card-2">
            <div class="hat-card__head">
                <div class="hat-card__icon">⏰</div>
                <div class="hat-card__txt">
                    <div class="hat-card__title">2. Hatırlatma — Orta</div>
                    <div class="hat-card__sub">Öğleden sonra</div>
                </div>
                <label class="hat-toggle hat-toggle--mini" title="Bu hatırlatmayı aç/kapat">
                    <input type="checkbox" class="hat-asama-aktif" data-asama="2" id="hat-aktif-2" {{ $aktif2 ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="hat-field">
                <label>Saat</label>
                <input type="time" id="hat-saat-2" value="{{ substr($ayar->saat_2, 0, 5) }}">
            </div>
            <div class="hat-field">
                <label>Mesaj</label>
                <textarea id="hat-mesaj-2" rows="2" maxlength="200">{{ $ayar->mesaj_2 }}</textarea>
            </div>
        </div>
        <div class="hat-card {{ $aktif3 ? '' : 'is-pasif' }}" id="hat-card-3">
            <div class="hat-card__head">
                <div class="hat-card__icon">🚨</div>
                <div class="hat-card__txt">
                    <div class="hat-card__title">3. Hatırlatma — Aciliyet</div>
                    <div class="hat-card__sub">Akşam, son saatler</div>
                </div>
                <label class="hat-toggle hat-toggle--mini" title="Bu hatırlatmayı aç/kapat">
                    <input type="checkbox" class="hat-asama-aktif" data-asama="3" id="hat-aktif-3" {{ $aktif3 ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="hat-field">
                <label>Saat</label>
                <input type="time" id="hat-saat-3" value="{{ substr($ayar->saat_3, 0, 5) }}">
            </div>
            <div class="hat-field">
                <label>Mesaj</label>
                <textarea id="hat-mesaj-3" rows="2" maxlength="200">{{ $ayar->mesaj_3 }}</textarea>
            </div>
        </div>
        <div class="hat-card {{ $aktifSon ? '' : 'is-pasif' }}" id="hat-card-son">
            <div class="hat-card__head">
                <div class="hat-card__icon">🎯</div>
                <div class="hat-card__txt">
                    <div class="hat-card__title">Son Şans</div>
                    <div class="hat-card__sub">Gece, son uyarı</div>
                </div>
                <label class="hat-toggle hat-toggle--mini" title="Bu hatırlatmayı aç/kapat">
                    <input type="checkbox" class="hat-asama-aktif" data-asama="son" id="hat-aktif-son" {{ $aktifSon ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="hat-field">
                <label>Saat</label>
                <input type="time" id="hat-saat-son" value="{{ substr($ayar->saat_son, 0, 5) }}">
            </div>
            <div class="hat-field">
                <label>Mesaj</label>
                <textarea id="hat-mesaj-son" rows="2" maxlength="200">{{ $ayar->mesaj_son }}</textarea>
            </div>
        </div>
    </div>

    <div class="hat-days">
        <h4>Hangi günler gönderilmesin?</h4>
        <p>İşaretlediğiniz günler push gönderilmez (örn. salonun kapalı olduğu günler).</p>
        <div class="hat-days__list">
            @php
                $haftaGunleri = [1=>'Pzt', 2=>'Sal', 3=>'Çar', 4=>'Per', 5=>'Cum', 6=>'Cmt', 7=>'Paz'];
                $skipDays = is_array($ayar->gonderim_gunleri) ? $ayar->gonderim_gunleri : [];
            @endphp
            @foreach($haftaGunleri as $g => $isim)
                <label class="hat-days__chip {{ in_array($g, $skipDays) ? 'is-checked' : '' }}">
                    <input type="checkbox" class="hat-gun" value="{{ $g }}" {{ in_array($g, $skipDays) ? 'checked' : '' }}>
                    {{ $isim }}
                </label>
            @endforeach
        </div>
    </div>

    <button class="hat-save" onclick="hatKaydet()">💾 Hatırlatma Ayarlarını Kaydet</button>
</div>

<div id="hatToast"></div>

<script>
const HAT_KAYDET_URL = '{{ route("isletmeadmin.cark.hatirlatma.kaydet") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const CSRF = '{{ csrf_token() }}';

function showHatToast(msg, err) {
    const t = document.getElementById('hatToast');
    t.textContent = msg;
    t.className = err ? 'err show' : 'show';
    setTimeout(() => t.classList.remove('show'), 2800);
}

// Day chip toggle
document.querySelectorAll('.hat-days__chip input').forEach(cb => {
    cb.addEventListener('change', e => {
        const lbl = e.target.closest('label');
        if (e.target.checked) lbl.classList.add('is-checked');
        else lbl.classList.remove('is-checked');
    });
});

// Asama aktif/pasif gorsel feedback (kart opaklasir)
document.querySelectorAll('.hat-asama-aktif').forEach(cb => {
    cb.addEventListener('change', e => {
        const asama = e.target.dataset.asama;
        const card = document.getElementById('hat-card-' + asama);
        if (!card) return;
        if (e.target.checked) card.classList.remove('is-pasif');
        else card.classList.add('is-pasif');
    });
});

async function hatKaydet() {
    const gunler = Array.from(document.querySelectorAll('.hat-gun:checked')).map(cb => parseInt(cb.value));
    const data = {
        aktif: document.getElementById('hat-aktif').checked ? 1 : 0,
        saat_1: document.getElementById('hat-saat-1').value,
        saat_2: document.getElementById('hat-saat-2').value,
        saat_3: document.getElementById('hat-saat-3').value,
        saat_son: document.getElementById('hat-saat-son').value,
        mesaj_1: document.getElementById('hat-mesaj-1').value.trim(),
        mesaj_2: document.getElementById('hat-mesaj-2').value.trim(),
        mesaj_3: document.getElementById('hat-mesaj-3').value.trim(),
        mesaj_son: document.getElementById('hat-mesaj-son').value.trim(),
        aktif_1:   document.getElementById('hat-aktif-1').checked ? 1 : 0,
        aktif_2:   document.getElementById('hat-aktif-2').checked ? 1 : 0,
        aktif_3:   document.getElementById('hat-aktif-3').checked ? 1 : 0,
        aktif_son: document.getElementById('hat-aktif-son').checked ? 1 : 0,
        gonderim_gunleri: gunler,
    };
    const btn = document.querySelector('.hat-save');
    btn.disabled = true; btn.textContent = '⏳ Kaydediliyor...';
    try {
        const r = await fetch(HAT_KAYDET_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify(data)
        });
        const d = await r.json();
        btn.disabled = false; btn.textContent = '💾 Hatırlatma Ayarlarını Kaydet';
        if (d.success) showHatToast('✓ Hatırlatma ayarları kaydedildi');
        else showHatToast(d.message || 'Hata', true);
    } catch(e) {
        btn.disabled = false; btn.textContent = '💾 Hatırlatma Ayarlarını Kaydet';
        showHatToast('Bağlantı hatası', true);
    }
}
</script>
@endsection
