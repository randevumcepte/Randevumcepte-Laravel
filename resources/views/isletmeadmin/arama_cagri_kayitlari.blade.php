@extends("layout.layout_isletmeadmin")
@section("content")

@include('isletmeadmin.partials.cagri_kayit_stil')

<div class="ck-wrap">
   <div class="ck-hero">
      <h4><a href="javascript:void(0)" class="ck-geri" onclick="ckGeri()"><i class="fa fa-arrow-left"></i> Geri</a><i class="fa fa-phone"></i> Çağrı Kayıtları</h4>
      <p>Her telefon görüşmesinin dökümü: tarih, sonuç, not ve <b>ses kaydı</b>. Soldan bir çağrı seçin, sağda detayını ve kaydını dinleyin.</p>
   </div>

   <div class="ck-ozet" id="ck_ozet">
      <div class="ck-oz t-gri"><div class="n" id="oz_toplam">0</div><div class="t"><span class="dot"></span> Toplam Çağrı</div></div>
      <div class="ck-oz t-mor"><div class="n" id="oz_kayitli">0</div><div class="t"><span class="dot"></span> Ses Kayıtlı</div></div>
      <div class="ck-oz t-yesil"><div class="n" id="oz_gelen">0</div><div class="t"><span class="dot"></span> Gelen</div></div>
      <div class="ck-oz t-mavi"><div class="n" id="oz_giden">0</div><div class="t"><span class="dot"></span> Giden</div></div>
   </div>

   <div class="ck-grid">
      <div class="ck-panel">
         <div class="ck-ara"><i class="fa fa-search"></i><input type="text" id="ck_ara" placeholder="Müşteri, telefon veya not ara..."></div>
         <div class="ck-liste" id="ck_liste"><div class="ck-bos" style="padding:40px;"><i class="fa fa-spinner fa-spin"></i></div></div>
      </div>
      <div class="ck-panel">
         <div class="ck-detay" id="ck_detay">
            <div class="ck-bos"><i class="fa fa-headphones"></i><div>Soldan bir çağrı seçin.</div></div>
         </div>
      </div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

<script>
function ckEsc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
var ckSube = $('input[name="sube"]').val();
var ckVeri = [];
var ckSecili = null;

function ckGeri(){ if(window.history.length>1){ window.history.back(); } else { window.location.href='/isletmeyonetim/arama-listelerim'+(ckSube?('?sube='+ckSube):''); } }

function ckYon(y){
   return (y==='gelen')
      ? {bl:'bl-yesil', b:'b-yesil', et:'↙ Gelen'}
      : {bl:'bl-mavi',  b:'b-mavi',  et:'↗ Giden'};
}
function ckSure(sn){ sn=parseInt(sn)||0; if(sn<=0) return '—'; if(sn<60) return sn+' sn'; var d=Math.floor(sn/60), s=sn%60; return d+' dk'+(s?(' '+s+' sn'):''); }

function ckListeCiz(){
   var ara = ($('#ck_ara').val()||'').toLocaleLowerCase('tr');
   var veri = ckVeri.filter(function(k){
      if(!ara) return true;
      return (k.ad+' '+k.telefon+' '+(k.not||'')).toLocaleLowerCase('tr').indexOf(ara) >= 0;
   });
   if(!veri.length){ $('#ck_liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-inbox"></i><div>Kayıt yok.</div></div>'); return; }
   var html='';
   veri.forEach(function(k){
      var y = ckYon(k.yon);
      var bas = (k.ad||'?').trim().charAt(0).toLocaleUpperCase('tr');
      html += '<div class="ck-item'+(ckSecili===k.id?' aktif':'')+'" data-id="'+ckEsc(k.id)+'">'
         + '<div class="ck-av">'+ckEsc(bas)+'</div>'
         + '<div class="bil"><div class="ad">'+ckEsc(k.ad)+'</div>'
         + '<div class="alt">'+(k.not? ckEsc(k.not) : ckEsc(k.telefon||'—'))+'</div></div>'
         + '<div class="sag"><div class="zmn">'+ckEsc(k.tarih)+'</div>'
         + '<span class="ck-badge '+y.b+'">'+y.et+'</span>'
         + (k.ses? ' <i class="fa fa-microphone" style="color:#7c3aed;margin-left:3px;" title="Ses kaydı var"></i>':'')
         + '</div></div>';
   });
   $('#ck_liste').html(html);
}

function ckDetayCiz(k){
   var y = ckYon(k.yon);
   var bas = (k.ad||'?').trim().charAt(0).toLocaleUpperCase('tr');
   // Aynı numaranın diğer çağrıları
   var gecmis = ckVeri.filter(function(x){ return x.id!==k.id && x.telefon && x.telefon===k.telefon; });
   var audio = k.ses
      ? ('<div class="ck-d-audio"><audio controls preload="none" src="'+ckEsc(k.ses)+'"></audio></div>')
      : ('<div class="ck-d-audio"><span class="yok">🎙️ Bu çağrı için ses kaydı yok.</span></div>');
   var not = k.not ? ('<div class="ck-d-not"><span class="lbl">SON GÖRÜŞME NOTU</span>'+ckEsc(k.not)+'</div>') : '';

   var gecmisHtml='';
   if(gecmis.length){
      gecmisHtml = '<div class="ck-gecmis-bas"><i class="fa fa-history"></i> Bu numaranın diğer çağrıları ('+gecmis.length+')</div>';
      gecmis.slice(0,30).forEach(function(g){
         var gy=ckYon(g.yon);
         gecmisHtml += '<div class="ck-g-row '+gy.bl+'"><div class="ust">'
            + '<span class="ck-badge '+gy.b+'">'+gy.et+'</span>'
            + '<span class="zmn">'+ckEsc(g.tarih)+(g.sure_sn>0?(' · '+ckSure(g.sure_sn)):'')+'</span></div>'
            + (g.ses?('<audio controls preload="none" src="'+ckEsc(g.ses)+'"></audio>'):'')
            + '</div>';
      });
   }

   $('#ck_detay').html(
      '<div class="ck-d-bas"><div class="ck-d-av">'+ckEsc(bas)+'</div>'
      + '<div><div class="ck-d-ad">'+ckEsc(k.ad)+'</div><div class="ck-d-tel"><i class="fa fa-phone"></i> '+ckEsc(k.telefon||'—')+'</div></div></div>'
      + '<div class="ck-d-satir">'
      +    '<div class="ck-d-chip"><span class="ck-badge '+y.b+'">'+y.et+'</span></div>'
      +    '<div class="ck-d-chip">🕒 <b>'+ckEsc(k.tarih)+'</b></div>'
      +    (k.sure_sn>0?('<div class="ck-d-chip">⏱️ Süre: <b>'+ckSure(k.sure_sn)+'</b></div>'):'')
      + '</div>'
      + audio + not + gecmisHtml
   );
}

$(document).on('click', '#ck_liste .ck-item', function(){
   var id = $(this).data('id');
   ckSecili = id;
   $('#ck_liste .ck-item').removeClass('aktif'); $(this).addClass('aktif');
   var k = ckVeri.find(function(x){ return String(x.id)===String(id); });
   if(k) ckDetayCiz(k);
});
$('#ck_ara').on('input', ckListeCiz);

function ckYukle(){
   $.get('/isletmeyonetim/arama-cagri-kayitlari-veri', { sube: ckSube }, function(res){
      ckVeri = (res && res.kayitlar) ? res.kayitlar : [];
      var o = (res && res.ozet) ? res.ozet : {};
      $('#oz_toplam').text(o.toplam||0); $('#oz_kayitli').text(o.kayitli||0);
      $('#oz_gelen').text(o.gelen||0); $('#oz_giden').text(o.giden||0);
      ckListeCiz();
   }).fail(function(){ $('#ck_liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-exclamation-triangle"></i><div>Yüklenemedi.</div></div>'); });
}

$(document).ready(ckYukle);
</script>
@endsection
