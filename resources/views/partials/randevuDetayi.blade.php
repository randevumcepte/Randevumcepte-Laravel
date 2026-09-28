<?php
   $cepTel = $randevu->randevu->users->cep_telefon ?? '';
   $yardimciPersonel = "";
   // On gorusme nedeni: paket/urun/hizmet adi
   $_ogn = '';
   if ($randevu->randevu->on_gorusme_id && $randevu->randevu->ongorusme) {
      $_og = $randevu->randevu->ongorusme;
      if ($_og->paket)       { $_ogn = $_og->paket->paket_adi; }
      elseif ($_og->urun)    { $_ogn = $_og->urun->urun_adi; }
      elseif ($_og->hizmet)  { $_ogn = $_og->hizmet->hizmet_adi; }
   }
   $_olusturanText = '—';
   if ($randevu->randevu->olusturan_personel_id && $randevu->randevu->olusturan_personel) {
      $_olusturanText = $randevu->randevu->olusturan_personel->name;
   } elseif ($randevu->randevu->easistan) {
      $_olusturanText = 'Asistan üzerinden müşteri';
   } elseif ($randevu->randevu->web) {
      $_olusturanText = 'Web üzerinden müşteri';
   } elseif ($randevu->randevu->uygulama) {
      $_olusturanText = 'Uygulama üzerinden müşteri';
   }
?>

<div class="rd-detail">
    <div class="rd-row">
       <div class="rd-label"><i class="fa fa-phone"></i> Telefon</div>
       <div class="rd-value">
          {{ $rol == 5 ? substr($cepTel, 0, 3) . ' *** **' . substr($cepTel, -2) : ($cepTel ?: '—') }}
       </div>
    </div>

    @if($randevu->randevu->on_gorusme_id)
        {{-- ÖN GÖRÜŞME RANDEVUSU --}}
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-bullhorn"></i> Ön Görüşme Nedeni</div>
           <div class="rd-value {{ $_ogn ? '' : 'empty' }}">{{ $_ogn ?: 'Belirtilmemiş' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-user"></i> Görüşmeyi Yapan</div>
           <div class="rd-value">{{ $randevu->randevu->ongorusme->personel->personel_adi ?? '—' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-clock-o"></i> Zaman</div>
           <div class="rd-value">{{ \Carbon\Carbon::parse($randevu->randevu->tarih)->format('d.m.Y') }} {{ substr($randevu->randevu->saat,0,5) }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-hourglass-half"></i> Süre</div>
           <div class="rd-value">{{ $randevu->sure_dk }} dk</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-pencil"></i> Oluşturan</div>
           <div class="rd-value">{{ $_olusturanText }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-info-circle"></i> Durum</div>
           <div class="rd-value">
              @if($randevu->randevu->ongorusme->durum === 1)
                 <span class="rd-status basarili">Satış Yapıldı</span>
              @elseif(is_null($randevu->randevu->ongorusme->durum))
                 <span class="rd-status beklemede">Beklemede</span>
              @else
                 <span class="rd-status iptal">Satış Yapılmadı</span>
              @endif
           </div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-sticky-note"></i> Personel Notu</div>
           <div class="rd-value {{ !empty($randevu->randevu->ongorusme->aciklama) ? '' : 'empty' }}">
              {{ $randevu->randevu->ongorusme->aciklama ?: 'Not eklenmemiş' }}
           </div>
        </div>
    @else
        {{-- NORMAL RANDEVU --}}
        @if(!empty($paketAdi))
        {{-- PAKET RANDEVUSU: paket adini ust satira yaz, sonra paket icindeki tum hizmetleri listele --}}
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-gift"></i> Paket</div>
           <div class="rd-value"><strong>{{ $paketAdi }}</strong></div>
        </div>
        @php
           $_paketHizmetler = $randevu->randevu && $randevu->randevu->hizmetler
               ? $randevu->randevu->hizmetler->filter(function($h){ return $h->hizmet_id; })
               : collect();
           $_paketToplamSure  = $_paketHizmetler->sum('sure_dk');
           $_paketToplamFiyat = $_paketHizmetler->sum('fiyat');
        @endphp
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-list"></i> Hizmetler ({{ $_paketHizmetler->count() }})</div>
           <div class="rd-value">
              @foreach($_paketHizmetler as $_ph)
              <div style="padding:3px 0; border-bottom:1px dashed #f1ecf7;">
                 <strong style="color:#2d2143;">{{ $_ph->hizmetler ? $_ph->hizmetler->hizmet_adi : '—' }}</strong>
                 <span style="color:#7c6c8a; font-size:11.5px; margin-left:8px;">
                    {{ $_ph->sure_dk ? $_ph->sure_dk.' dk' : '' }}
                 </span>
                 @if($_ph->personeller)
                 <span style="color:#5C008E; font-size:11.5px; margin-left:6px;"><i class="fa fa-user" style="font-size:10px;"></i> {{ $_ph->personeller->personel_adi }}</span>
                 @endif
              </div>
              @endforeach
              @if($_paketHizmetler->count() === 0)
              <span style="color:#bcb3c9;">—</span>
              @endif
           </div>
        </div>
        @if($_paketToplamSure > 0 || $_paketToplamFiyat > 0)
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-calculator"></i> Toplam</div>
           <div class="rd-value">
              <span style="font-weight:600;">{{ $_paketToplamSure }} dk</span>
              @if($_paketToplamFiyat > 0)
                 · <span style="font-weight:600;">{{ number_format($_paketToplamFiyat,0,',','.') }} ₺</span>
              @endif
           </div>
        </div>
        @endif
        @else
        {{-- TEKLI HIZMET (paket degil) --}}
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-magic"></i> Hizmet</div>
           <div class="rd-value">{{ $randevu->hizmet_id && $randevu->hizmetler ? $randevu->hizmetler->hizmet_adi : '—' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-user"></i> Personel</div>
           <div class="rd-value">{{ $randevu->personel_id && $randevu->personeller ? $randevu->personeller->personel_adi : '—' }}</div>
        </div>
        @endif
        @php
           $_yp = '';
           foreach($randevu->randevu->hizmetler as $hizmetler) {
              if ($hizmetler->hizmet_id == $randevu->hizmet_id
                  && $randevu->oda_id == $hizmetler->oda_id
                  && $randevu->cihaz_id == $hizmetler->cihaz_id
                  && $randevu->yardimci_personel) {
                 $_yp .= ($randevu->personeller->personel_adi ?? '') . ' ';
              }
           }
           $_yp = trim($_yp);
        @endphp
        @if($_yp && empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-users"></i> Yardımcı Personel</div>
           <div class="rd-value">{{ $_yp }}</div>
        </div>
        @endif
        @if($randevu->cihaz_id && $randevu->cihaz && empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-microchip"></i> Cihaz</div>
           <div class="rd-value">{{ $randevu->cihaz->cihaz_adi }}</div>
        </div>
        @endif
        @if($randevu->oda_id && $randevu->oda && empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-cube"></i> Oda</div>
           <div class="rd-value">{{ $randevu->oda->oda_adi }}</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-clock-o"></i> Zaman</div>
           <div class="rd-value">{{ \Carbon\Carbon::parse($randevu->randevu->tarih)->format('d.m.Y') }} {{ substr($randevu->saat,0,5) }}</div>
        </div>
        @if(empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-hourglass-half"></i> Süre</div>
           <div class="rd-value">{{ $randevu->sure_dk }} dk</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-pencil"></i> Oluşturan</div>
           <div class="rd-value">{{ $_olusturanText }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-check-circle"></i> Geldi mi?</div>
           <div class="rd-value">
              @php $__telafiVar = \App\AdisyonPaketSeanslar::where('randevu_id', $randevu->randevu->id)->where('geldi', 2)->exists(); @endphp
              @if($randevu->randevu->randevuya_geldi === 1)
                 <span class="rd-status geldi">Geldi</span>
              @elseif($randevu->randevu->randevuya_geldi === 0)
                 <span class="rd-status gelmedi">Gelmedi</span>
              @else
                 <span class="rd-status beklemede">Belirtilmemiş</span>
              @endif
              @if($__telafiVar)<span class="rd-status" style="background:#ffe8d6;color:#c2410c;margin-left:6px;">Telafi</span>@endif
           </div>
        </div>
        @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-money"></i> Fiyat</div>
           <div class="rd-value">{{ number_format($randevu->fiyat ?: 0, 2, ',', '.') }} ₺</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-comment-o"></i> Müşteri Notu</div>
           <div class="rd-value rd-not-inline {{ !empty($randevu->randevu->notlar) ? '' : 'empty' }}"
                data-not-alan="notlar"
                data-randevu-id="{{ $randevu->randevu->id }}"
                data-orig="{{ $randevu->randevu->notlar }}"
                title="Düzenlemek için tıklayın">
              <span class="rd-not-goster">{{ $randevu->randevu->notlar ?: 'Not yok' }}</span>
              <i class="fa fa-pencil rd-not-icon" style="opacity:.35;margin-left:6px;font-size:11px;"></i>
           </div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-sticky-note"></i> Personel Notu</div>
           <div class="rd-value rd-not-inline {{ !empty($randevu->randevu->personel_notu) ? '' : 'empty' }}"
                data-not-alan="personel_notu"
                data-randevu-id="{{ $randevu->randevu->id }}"
                data-orig="{{ $randevu->randevu->personel_notu }}"
                title="Düzenlemek için tıklayın">
              <span class="rd-not-goster">{{ $randevu->randevu->personel_notu ?: 'Not yok' }}</span>
              <i class="fa fa-pencil rd-not-icon" style="opacity:.35;margin-left:6px;font-size:11px;"></i>
           </div>
        </div>
    @endif
</div>

<style>
.rd-not-inline { cursor: pointer; position: relative; transition: background 0.15s; }
.rd-not-inline:hover { background: #faf5ff; }
.rd-not-inline .rd-not-icon { transition: opacity 0.15s; }
.rd-not-inline:hover .rd-not-icon { opacity: .8; }
.rd-not-inline.editing { cursor: text; background: #fff; padding: 4px !important; }
.rd-not-inline textarea.rd-not-input {
    width: 100%; min-height: 60px; border: 1px solid #a78bfa; border-radius: 4px;
    padding: 6px 8px; font-size: 13px; font-family: inherit; resize: vertical;
    outline: none; box-shadow: 0 0 0 2px rgba(139,92,246,.15);
}
.rd-not-actions { margin-top: 6px; display: flex; gap: 6px; justify-content: flex-end; }
.rd-not-actions button {
    padding: 4px 10px; font-size: 12px; border-radius: 4px; border: none; cursor: pointer;
}
.rd-not-save { background: #7c3aed; color: #fff; }
.rd-not-save:hover { background: #6d28d9; }
.rd-not-cancel { background: #e5e7eb; color: #4b5563; }
.rd-not-cancel:hover { background: #d1d5db; }
</style>
<script>
(function(){
    // Ayni partial birden fazla insert edilebilir; her seferinde tek delegasyon yeter
    if(window._rdNotInlineWired) return;
    window._rdNotInlineWired = true;

    var _csrf = function(){ return $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val() || ''; };

    $(document).on('click', '.rd-not-inline:not(.editing)', function(e){
        e.stopPropagation();
        var $el = $(this);
        var orig = $el.attr('data-orig') || '';
        $el.addClass('editing');
        $el.html(
            '<textarea class="rd-not-input" placeholder="Not yazın..."></textarea>' +
            '<div class="rd-not-actions">' +
                '<button type="button" class="rd-not-cancel">İptal</button>' +
                '<button type="button" class="rd-not-save"><i class="fa fa-check"></i> Kaydet</button>' +
            '</div>'
        );
        var $ta = $el.find('textarea.rd-not-input');
        $ta.val(orig).focus();
        // Cursor sona
        try { $ta[0].setSelectionRange(orig.length, orig.length); } catch(_){}
    });

    // Sadece iptal butonu
    $(document).on('click', '.rd-not-inline .rd-not-cancel', function(e){
        e.stopPropagation();
        var $el = $(this).closest('.rd-not-inline');
        var orig = $el.attr('data-orig') || '';
        _renderStatic($el, orig);
    });

    // Kaydet
    $(document).on('click', '.rd-not-inline .rd-not-save', function(e){
        e.stopPropagation();
        var $el = $(this).closest('.rd-not-inline');
        _kaydet($el);
    });

    // Ctrl+Enter kaydet, Esc iptal
    $(document).on('keydown', '.rd-not-inline textarea.rd-not-input', function(e){
        var $el = $(this).closest('.rd-not-inline');
        if(e.key === 'Escape'){
            e.preventDefault();
            _renderStatic($el, $el.attr('data-orig') || '');
        } else if((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
            e.preventDefault();
            _kaydet($el);
        }
    });

    // Ic tikta bubble edilmesin (yeniden edit moda gecmesin)
    $(document).on('click', '.rd-not-inline.editing', function(e){ e.stopPropagation(); });

    function _renderStatic($el, deger){
        $el.removeClass('editing');
        var isEmpty = !deger || !String(deger).trim();
        $el.toggleClass('empty', isEmpty);
        $el.attr('data-orig', deger || '');
        $el.html(
            '<span class="rd-not-goster">' + (isEmpty ? 'Not yok' : _escape(deger)) + '</span>' +
            '<i class="fa fa-pencil rd-not-icon" style="opacity:.35;margin-left:6px;font-size:11px;"></i>'
        );
    }

    function _kaydet($el){
        var randevuId = $el.attr('data-randevu-id');
        var alan = $el.attr('data-not-alan');
        var deger = $el.find('textarea.rd-not-input').val() || '';
        var $save = $el.find('.rd-not-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.ajax({
            type: 'POST',
            url: '/isletmeyonetim/randevu-not-guncelle',
            dataType: 'json',
            data: { _token: _csrf(), randevu_id: randevuId, alan: alan, deger: deger }
        }).done(function(res){
            _renderStatic($el, res.deger !== undefined ? res.deger : deger);
            // Randevu detay HTML client cache'i temizle — modal kapatilip yeniden
            // acildiginda backend'ten fresh yuklensin (yoksa notlar hala eski gorunur).
            try { if(window._rcDetayCache) window._rcDetayCache = {}; } catch(_){}
        }).fail(function(xhr){
            $save.prop('disabled', false).html('<i class="fa fa-check"></i> Kaydet');
            var msg = 'Kaydedilemedi';
            try { var j = JSON.parse(xhr.responseText); if(j && j.error) msg = j.error; } catch(_){}
            if(typeof swal !== 'undefined'){
                swal({type:'error', title:'Hata', text: msg, showConfirmButton:false, timer:2500});
            } else { alert(msg); }
        });
    }

    function _escape(s){
        return String(s).replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }
})();
</script>
