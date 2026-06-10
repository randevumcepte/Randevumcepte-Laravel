@extends('sistemyonetim.v2.layout')

@section('content')

<style>
   .mol-satir { display: grid; grid-template-columns: 1fr 110px 160px 42px; gap: 10px; align-items: end; margin-bottom: 10px; }
   .mol-satir-head { display: grid; grid-template-columns: 1fr 110px 160px 42px; gap: 10px; font-size: 11px; color: var(--sy-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
   .mol-sil { background: rgba(239,68,68,.12); color: #ef4444; border: none; border-radius: 8px; height: 40px; font-size: 20px; line-height: 1; cursor: pointer; }
   .mol-sil:hover { background: rgba(239,68,68,.2); }
   .mol-ekle { margin-top: 6px; }
   .mol-toplam { text-align: right; font-size: 15px; font-weight: 700; margin-top: 10px; }
   .mol-sonuc { display: none; margin-top: 16px; padding: 14px 16px; border-radius: 10px; background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.35); }
   .mol-sonuc .baslik { font-weight: 700; color: #059669; margin-bottom: 8px; font-size: 14px; }
   .mol-link-kutu { display: flex; gap: 8px; }
   .mol-link-input { flex: 1; }
   .mol-hata { display: none; margin-top: 14px; padding: 12px 14px; border-radius: 10px; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #b91c1c; font-size: 13.5px; }
</style>

<div class="sy-page-head">
    <div>
        <h2>Manuel Ödeme Linki</h2>
        <div class="subtitle">İşletmeye satılan hizmeti, adedi ve tutarı girip PayTR ödeme linki üretin</div>
    </div>
</div>

<div class="sy-card" style="max-width:860px">
    <div class="sy-card-body">

        <div class="sy-form-group">
            <label>İşletme *</label>
            <select id="mol-salon" class="sy-select">
                <option value="">— İşletme seçin —</option>
                @foreach($isletmeler as $isletme)
                    <option value="{{ $isletme->id }}">{{ $isletme->salon_adi }}@if($isletme->yetkili_adi) — {{ $isletme->yetkili_adi }}@endif @if($isletme->yetkili_telefon)({{ $isletme->yetkili_telefon }})@endif</option>
                @endforeach
            </select>
            <small style="color:var(--sy-muted,#94a3b8)">Ödeme sayfası e-posta/telefon bilgisini seçilen işletmeden alır.</small>
        </div>

        <div class="sy-form-group" style="margin-top:18px">
            <label>Hizmetler *</label>
            <div class="mol-satir-head">
                <span>Hizmet Adı</span><span>Adet</span><span>Birim Tutar (₺)</span><span></span>
            </div>
            <div id="mol-satirlar"></div>
            <button type="button" class="sy-btn sy-btn-soft sy-btn-sm mol-ekle" id="mol-satir-ekle">
                <span class="mdi mdi-plus"></span> Hizmet satırı ekle
            </button>
            <div class="mol-toplam">Toplam: <span id="mol-toplam">0,00</span> ₺</div>
        </div>

        <div class="sy-form-group" style="margin-top:14px">
            <label>Not (opsiyonel — ödeme sayfasında görünmez)</label>
            <input type="text" id="mol-notlar" class="sy-input" placeholder="İç not">
        </div>

        <div class="sy-flex-row" style="justify-content:flex-end;margin-top:18px">
            <button type="button" class="sy-btn sy-btn-primary" id="mol-olustur">
                <span class="mdi mdi-link-variant"></span> Ödeme Linki Oluştur
            </button>
        </div>

        <div class="mol-hata" id="mol-hata"></div>

        <div class="mol-sonuc" id="mol-sonuc">
            <div class="baslik">✓ Ödeme linki oluşturuldu (Form #<span id="mol-formid"></span>) — Toplam: <span id="mol-sonuc-toplam"></span> ₺</div>
            <div class="mol-link-kutu">
                <input type="text" class="sy-input mol-link-input" id="mol-link" readonly>
                <button type="button" class="sy-btn sy-btn-primary" id="mol-kopyala"><span class="mdi mdi-content-copy"></span> Kopyala</button>
            </div>
        </div>

    </div>
</div>

<script>
(function(){
    var TOKEN = '{{ csrf_token() }}';
    var POST_URL = '/sistemyonetim/v2/manuel-odeme-linki';

    function el(html){ var d = document.createElement('div'); d.innerHTML = html.trim(); return d.firstChild; }

    function satirHtml(){
        return '<div class="mol-satir">' +
            '<input type="text" class="sy-input mol-ad" placeholder="Örn. Üyelik bedeli / Danışmanlık">' +
            '<input type="number" min="1" value="1" class="sy-input mol-adet">' +
            '<input type="text" class="sy-input mol-tutar" placeholder="0,00">' +
            '<button type="button" class="mol-sil" title="Satırı sil">&times;</button>' +
            '</div>';
    }

    var satirlar = document.getElementById('mol-satirlar');

    function toplamHesapla(){
        var toplam = 0;
        satirlar.querySelectorAll('.mol-satir').forEach(function(row){
            var adet = parseInt(row.querySelector('.mol-adet').value) || 0;
            var tutarStr = (row.querySelector('.mol-tutar').value || '').replace(/\./g, '').replace(',', '.');
            var tutar = parseFloat(tutarStr) || 0;
            toplam += adet * tutar;
        });
        document.getElementById('mol-toplam').textContent = toplam.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function satirEkle(){ satirlar.appendChild(el(satirHtml())); }

    satirlar.addEventListener('click', function(e){
        if(e.target.classList.contains('mol-sil')){
            var rows = satirlar.querySelectorAll('.mol-satir');
            if(rows.length > 1){ e.target.closest('.mol-satir').remove(); }
            else {
                var r = e.target.closest('.mol-satir');
                r.querySelector('.mol-ad').value = '';
                r.querySelector('.mol-tutar').value = '';
                r.querySelector('.mol-adet').value = '1';
            }
            toplamHesapla();
        }
    });
    satirlar.addEventListener('input', function(e){
        if(e.target.classList.contains('mol-adet') || e.target.classList.contains('mol-tutar')) toplamHesapla();
    });

    document.getElementById('mol-satir-ekle').addEventListener('click', satirEkle);
    satirEkle(); // ilk satir

    document.getElementById('mol-olustur').addEventListener('click', function(){
        var btn = this;
        var hata = document.getElementById('mol-hata');
        var sonuc = document.getElementById('mol-sonuc');
        hata.style.display = 'none';
        sonuc.style.display = 'none';

        var salonId = document.getElementById('mol-salon').value;
        if(!salonId){ hata.textContent = 'Lütfen bir işletme seçin.'; hata.style.display = 'block'; return; }

        var hizmetler = [];
        satirlar.querySelectorAll('.mol-satir').forEach(function(row){
            var ad = row.querySelector('.mol-ad').value.trim();
            var adet = parseInt(row.querySelector('.mol-adet').value) || 1;
            var tutar = row.querySelector('.mol-tutar').value.trim();
            if(ad !== '' && tutar !== '') hizmetler.push({ ad: ad, adet: adet, tutar: tutar });
        });
        if(hizmetler.length === 0){ hata.textContent = 'En az bir geçerli hizmet satırı girin (ad ve tutar zorunlu).'; hata.style.display = 'block'; return; }

        var fd = new FormData();
        fd.append('_token', TOKEN);
        fd.append('salon_id', salonId);
        fd.append('notlar', document.getElementById('mol-notlar').value);
        hizmetler.forEach(function(h, i){
            fd.append('hizmetler['+i+'][ad]', h.ad);
            fd.append('hizmetler['+i+'][adet]', h.adet);
            fd.append('hizmetler['+i+'][tutar]', h.tutar);
        });

        btn.disabled = true;
        var eskiHtml = btn.innerHTML;
        btn.innerHTML = '<span class="mdi mdi-loading mdi-spin"></span> Oluşturuluyor...';

        fetch(POST_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            credentials: 'same-origin'
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if(res && res.type === 'success'){
                document.getElementById('mol-formid').textContent = res.form_id;
                document.getElementById('mol-sonuc-toplam').textContent = res.toplam;
                document.getElementById('mol-link').value = res.link;
                sonuc.style.display = 'block';
            } else {
                hata.textContent = (res && res.message) ? res.message : 'Bir hata oluştu.';
                hata.style.display = 'block';
            }
        })
        .catch(function(){ hata.textContent = 'Sunucu hatası. Lütfen tekrar deneyin.'; hata.style.display = 'block'; })
        .then(function(){ btn.disabled = false; btn.innerHTML = eskiHtml; });
    });

    document.getElementById('mol-kopyala').addEventListener('click', function(){
        var inp = document.getElementById('mol-link');
        inp.select(); inp.setSelectionRange(0, 99999);
        try { document.execCommand('copy'); } catch(e){}
        if(navigator.clipboard){ navigator.clipboard.writeText(inp.value).catch(function(){}); }
        var b = this, old = b.innerHTML;
        b.innerHTML = '<span class="mdi mdi-check"></span> Kopyalandı';
        setTimeout(function(){ b.innerHTML = old; }, 1800);
    });
})();
</script>

@endsection
