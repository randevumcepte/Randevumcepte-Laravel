@extends('layout.layout_sistemadmin')
@section('content')

<style>
   .mol-wrap { padding: 18px 22px 60px; max-width: 920px; }
   .mol-baslik { font-size: 22px; font-weight: 700; color: #1f2937; margin: 0 0 4px; }
   .mol-altyazi { color: #6b7280; font-size: 13px; margin-bottom: 22px; }
   .mol-bolum {
      background: #fff; border-radius: 14px; padding: 20px 22px; margin-bottom: 18px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
      border: 1px solid #f1f3f7;
   }
   .mol-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
   .mol-input, .mol-select {
      width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
      padding: 9px 12px; font-size: 14px; color: #111827; background: #fff;
   }
   .mol-input:focus, .mol-select:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }

   .mol-satir { display: grid; grid-template-columns: 1fr 110px 150px 44px; gap: 10px; align-items: end; margin-bottom: 10px; }
   .mol-satir .mol-sil {
      background: #fee2e2; color: #b91c1c; border: none; border-radius: 8px;
      height: 38px; font-size: 18px; cursor: pointer; line-height: 1;
   }
   .mol-satir .mol-sil:hover { background: #fecaca; }

   .mol-ekle-btn {
      background: #eef2ff; color: #4338ca; border: 1px dashed #c7d2fe; border-radius: 8px;
      padding: 9px 16px; font-weight: 600; font-size: 13px; cursor: pointer; margin-top: 4px;
   }
   .mol-ekle-btn:hover { background: #e0e7ff; }

   .mol-toplam { text-align: right; font-size: 15px; font-weight: 700; color: #111827; margin-top: 8px; }

   .mol-olustur {
      background: linear-gradient(135deg, #6d3aaa, #8a5cc7); color: #fff; border: none;
      padding: 12px 26px; border-radius: 10px; font-weight: 700; font-size: 15px; cursor: pointer;
   }
   .mol-olustur:hover { filter: brightness(1.05); }
   .mol-olustur:disabled { opacity: 0.6; cursor: not-allowed; }

   .mol-sonuc {
      display: none; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px;
      padding: 16px 18px; margin-top: 18px;
   }
   .mol-sonuc .baslik { font-weight: 700; color: #065f46; font-size: 14px; margin-bottom: 8px; }
   .mol-link-kutu { display: flex; gap: 8px; }
   .mol-link-input { flex: 1; border: 1px solid #6ee7b7; border-radius: 8px; padding: 9px 12px; font-size: 13px; background: #fff; color: #065f46; }
   .mol-kopyala { background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 9px 16px; font-weight: 600; cursor: pointer; white-space: nowrap; }
   .mol-kopyala:hover { background: #059669; }

   .mol-hata { display: none; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 12px 16px; margin-top: 14px; font-size: 13.5px; }
</style>

<div class="mol-wrap">
   <h1 class="mol-baslik">Manuel Ödeme Linki</h1>
   <p class="mol-altyazi">İşletmeye satılan hizmeti, adedi ve tutarı serbestçe girip PayTR ödeme linki üretin. Üretilen linki işletmeye iletebilirsiniz.</p>

   <div class="mol-bolum">
      <label class="mol-label">İşletme</label>
      <select id="mol-salon" class="mol-select">
         <option value="">— İşletme seçin —</option>
         @foreach($isletmeler as $isletme)
            <option value="{{ $isletme->id }}">{{ $isletme->salon_adi }} @if($isletme->yetkili_adi) — {{ $isletme->yetkili_adi }} @endif @if($isletme->yetkili_telefon) ({{ $isletme->yetkili_telefon }}) @endif</option>
         @endforeach
      </select>
      <small style="color:#9aa3b2;font-size:11.5px;">Ödeme sayfası e-posta/telefon bilgisini seçilen işletmeden alır.</small>
   </div>

   <div class="mol-bolum">
      <label class="mol-label">Hizmetler</label>
      <div style="display:grid;grid-template-columns:1fr 110px 150px 44px;gap:10px;font-size:11px;color:#9aa3b2;font-weight:600;margin-bottom:6px;">
         <span>HİZMET ADI</span><span>ADET</span><span>BİRİM TUTAR (₺)</span><span></span>
      </div>
      <div id="mol-satirlar"></div>
      <button type="button" class="mol-ekle-btn" id="mol-satir-ekle">+ Hizmet satırı ekle</button>
      <div class="mol-toplam">Toplam: <span id="mol-toplam">0,00</span> ₺</div>
   </div>

   <div class="mol-bolum">
      <label class="mol-label">Not (opsiyonel)</label>
      <input type="text" id="mol-notlar" class="mol-input" placeholder="İç not (ödeme sayfasında görünmez)">
   </div>

   <button type="button" class="mol-olustur" id="mol-olustur">Ödeme Linki Oluştur</button>

   <div class="mol-hata" id="mol-hata"></div>

   <div class="mol-sonuc" id="mol-sonuc">
      <div class="baslik">✓ Ödeme linki oluşturuldu (Form #<span id="mol-formid"></span>) — Toplam: <span id="mol-sonuc-toplam"></span> ₺</div>
      <div class="mol-link-kutu">
         <input type="text" class="mol-link-input" id="mol-link" readonly>
         <button type="button" class="mol-kopyala" id="mol-kopyala">Kopyala</button>
      </div>
   </div>
</div>

<script>
$(document).ready(function(){
   function satirHtml(){
      return '<div class="mol-satir">' +
         '<input type="text" class="mol-input mol-ad" placeholder="Örn. Üyelik bedeli / Danışmanlık">' +
         '<input type="number" min="1" value="1" class="mol-input mol-adet">' +
         '<input type="text" class="mol-input mol-tutar" placeholder="0,00">' +
         '<button type="button" class="mol-sil" title="Satırı sil">&times;</button>' +
         '</div>';
   }

   function toplamHesapla(){
      var toplam = 0;
      $('#mol-satirlar .mol-satir').each(function(){
         var adet = parseInt($(this).find('.mol-adet').val()) || 0;
         var tutarStr = ($(this).find('.mol-tutar').val() || '').replace(/\./g, '').replace(',', '.');
         var tutar = parseFloat(tutarStr) || 0;
         toplam += adet * tutar;
      });
      $('#mol-toplam').text(toplam.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
   }

   $('#mol-satir-ekle').on('click', function(){
      $('#mol-satirlar').append(satirHtml());
   });

   // ilk satir
   $('#mol-satirlar').append(satirHtml());

   $('#mol-satirlar').on('click', '.mol-sil', function(){
      if($('#mol-satirlar .mol-satir').length > 1){
         $(this).closest('.mol-satir').remove();
      } else {
         $(this).closest('.mol-satir').find('input').val('');
         $(this).closest('.mol-satir').find('.mol-adet').val('1');
      }
      toplamHesapla();
   });

   $('#mol-satirlar').on('input', '.mol-adet, .mol-tutar', toplamHesapla);

   $('#mol-olustur').on('click', function(){
      var btn = $(this);
      $('#mol-hata').hide();
      $('#mol-sonuc').hide();

      var salonId = $('#mol-salon').val();
      if(!salonId){
         $('#mol-hata').text('Lütfen bir işletme seçin.').show();
         return;
      }

      var hizmetler = [];
      $('#mol-satirlar .mol-satir').each(function(){
         var ad = $.trim($(this).find('.mol-ad').val());
         var adet = parseInt($(this).find('.mol-adet').val()) || 1;
         var tutar = $.trim($(this).find('.mol-tutar').val());
         if(ad !== '' && tutar !== ''){
            hizmetler.push({ ad: ad, adet: adet, tutar: tutar });
         }
      });

      if(hizmetler.length === 0){
         $('#mol-hata').text('En az bir geçerli hizmet satırı girin (ad ve tutar zorunlu).').show();
         return;
      }

      btn.prop('disabled', true).text('Oluşturuluyor...');

      $.post('{{ url('/sistemyonetim/manuel-odeme-linki-olustur') }}', {
         _token: '{{ csrf_token() }}',
         salon_id: salonId,
         notlar: $('#mol-notlar').val(),
         hizmetler: hizmetler
      }).done(function(res){
         if(res && res.type === 'success'){
            $('#mol-formid').text(res.form_id);
            $('#mol-sonuc-toplam').text(res.toplam);
            $('#mol-link').val(res.link);
            $('#mol-sonuc').show();
         } else {
            $('#mol-hata').text((res && res.message) ? res.message : 'Bir hata oluştu.').show();
         }
      }).fail(function(){
         $('#mol-hata').text('Sunucu hatası. Lütfen tekrar deneyin.').show();
      }).always(function(){
         btn.prop('disabled', false).text('Ödeme Linki Oluştur');
      });
   });

   $('#mol-kopyala').on('click', function(){
      var inp = document.getElementById('mol-link');
      inp.select();
      inp.setSelectionRange(0, 99999);
      try { document.execCommand('copy'); } catch(e){}
      if(navigator.clipboard){ navigator.clipboard.writeText(inp.value).catch(function(){}); }
      var b = $(this); b.text('Kopyalandı ✓');
      setTimeout(function(){ b.text('Kopyala'); }, 1800);
   });
});
</script>

@endsection
