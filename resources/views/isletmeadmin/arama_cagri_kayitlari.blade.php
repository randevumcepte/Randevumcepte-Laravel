@extends("layout.layout_isletmeadmin")
@section("content")

<style>
.ck-wrap{ --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC8; }
.ck-hero{ background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%); border-radius:18px; padding:22px 26px; color:#fff; margin-bottom:16px; box-shadow:0 12px 30px -10px rgba(92,0,142,.45); position:relative; overflow:hidden; }
.ck-hero:after{ content:""; position:absolute; right:-40px; top:-40px; width:170px; height:170px; border-radius:50%; background:rgba(255,255,255,.10); }
.ck-hero h4{ margin:0 0 6px; font-weight:700; font-size:22px; color:#fff; }
.ck-hero p{ margin:0; opacity:.92; font-size:13.5px; max-width:680px; }
.ck-geri{ display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:30px; background:#fff; color:#5C008E !important; font-size:14px; font-weight:800; text-decoration:none; margin-right:12px; vertical-align:middle; box-shadow:0 6px 16px -6px rgba(0,0,0,.4); }
.ck-geri:hover{ color:#5C008E; filter:brightness(.97); }

/* Özet kartları */
.ck-ozet{ display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
.ck-oz{ background:#fff; border:1px solid #f0eef5; border-radius:14px; padding:16px 18px; box-shadow:0 6px 18px -10px rgba(30,30,60,.15); }
.ck-oz .n{ font-size:26px; font-weight:800; line-height:1; color:#241b3a; }
.ck-oz .t{ font-size:12.5px; color:#8a85a0; margin-top:6px; display:flex; align-items:center; gap:6px; }
.ck-oz .dot{ width:9px; height:9px; border-radius:50%; display:inline-block; }
@media (max-width:680px){ .ck-ozet{ grid-template-columns:repeat(2,1fr); } }

/* İki kolon */
.ck-grid{ display:grid; grid-template-columns:380px 1fr; gap:16px; align-items:start; }
@media (max-width:991px){ .ck-grid{ grid-template-columns:1fr; } }
.ck-panel{ background:#fff; border-radius:18px; border:1px solid #eef0f5; box-shadow:0 6px 20px -12px rgba(30,30,60,.16); overflow:hidden; }

/* Sol liste */
.ck-ara{ padding:12px 14px; border-bottom:1px solid #f0eef5; position:relative; }
.ck-ara input{ width:100%; border:1px solid #e7e9f0; border-radius:12px; padding:9px 12px 9px 34px; font-size:13px; outline:none; }
.ck-ara input:focus{ border-color:#9D5DC8; }
.ck-ara .fa{ position:absolute; left:26px; top:50%; transform:translateY(-50%); color:#aab0c0; }
.ck-liste{ max-height:62vh; overflow-y:auto; }
.ck-item{ display:flex; align-items:center; gap:11px; padding:12px 14px; border-bottom:1px solid #f4f3f8; cursor:pointer; transition:background .12s; }
.ck-item:hover{ background:#faf8fe; }
.ck-item.aktif{ background:#f3edfb; border-left:4px solid #6d28d9; }
.ck-av{ width:40px; height:40px; border-radius:50%; flex:0 0 40px; background:linear-gradient(135deg,#6d28d9,#9D5DC8); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; }
.ck-item .bil{ flex:1; min-width:0; }
.ck-item .ad{ font-weight:700; color:#241b3a; font-size:13.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ck-item .alt{ font-size:11.5px; color:#9a95ab; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ck-item .sag{ text-align:right; flex:0 0 auto; }
.ck-item .zmn{ font-size:11px; color:#aab0c0; }
.ck-badge{ font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:20px; display:inline-block; margin-top:3px; }
.b-yesil{ background:#e4f7ec; color:#16a34a; } .b-kirmizi{ background:#fdecec; color:#dc2626; }
.b-mavi{ background:#e7f1fd; color:#1565c0; } .b-turuncu{ background:#fff2e3; color:#e07a1a; }
.b-altin{ background:#fef7e0; color:#b7791f; } .b-gri{ background:#f0eef5; color:#8a85a0; }

/* Sağ detay */
.ck-detay{ padding:22px; min-height:420px; }
.ck-bos{ text-align:center; color:#9097ad; padding:80px 20px; }
.ck-bos i{ font-size:46px; color:#d7d2e6; display:block; margin-bottom:14px; }
.ck-d-bas{ display:flex; align-items:center; gap:14px; margin-bottom:16px; }
.ck-d-av{ width:54px; height:54px; border-radius:50%; flex:0 0 54px; background:linear-gradient(135deg,#6d28d9,#9D5DC8); color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; }
.ck-d-ad{ font-size:20px; font-weight:800; color:#241b3a; }
.ck-d-tel{ font-size:13px; color:#6b7280; margin-top:2px; }
.ck-d-satir{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.ck-d-chip{ background:#faf9fc; border:1px solid #efeaf6; border-radius:10px; padding:8px 12px; font-size:12.5px; color:#574f6b; }
.ck-d-chip b{ color:#241b3a; }
.ck-d-not{ background:#faf9fc; border:1px solid #efeaf6; border-radius:12px; padding:14px; font-size:14px; color:#444; line-height:1.5; margin-bottom:14px; }
.ck-d-not .lbl{ font-size:11.5px; font-weight:700; color:#8a85a0; display:block; margin-bottom:6px; }
.ck-d-audio{ margin-bottom:16px; }
.ck-d-audio audio{ width:100%; }
.ck-d-audio .yok{ font-size:13px; color:#9a95ab; }
.ck-gecmis-bas{ font-size:13px; font-weight:800; color:#5C008E; margin:18px 0 10px; }
.ck-g-row{ border:1px solid #efeaf6; border-left:4px solid #cfc7df; border-radius:12px; padding:11px 13px; margin-bottom:9px; }
.ck-g-row.bl-yesil{ border-left-color:#16a34a; } .ck-g-row.bl-kirmizi{ border-left-color:#dc2626; }
.ck-g-row.bl-mavi{ border-left-color:#1565c0; } .ck-g-row.bl-turuncu{ border-left-color:#e07a1a; }
.ck-g-row.bl-altin{ border-left-color:#b7791f; }
.ck-g-row .ust{ display:flex; align-items:center; gap:8px; }
.ck-g-row .zmn{ margin-left:auto; font-size:12px; color:#9a95ab; }
.ck-g-row .not{ font-size:12.5px; color:#574f6b; margin-top:6px; }
.ck-g-row audio{ width:100%; margin-top:8px; height:34px; }
</style>

<div class="ck-wrap">
   <div class="ck-hero">
      <h4><a href="javascript:void(0)" class="ck-geri" onclick="ckGeri()"><i class="fa fa-arrow-left"></i> Geri</a><i class="fa fa-phone"></i> Çağrı Kayıtları</h4>
      <p>Her telefon görüşmesinin dökümü: tarih, sonuç, not ve <b>ses kaydı</b>. Soldan bir çağrı seçin, sağda detayını ve kaydını dinleyin.</p>
   </div>

   <div class="ck-ozet" id="ck_ozet">
      <div class="ck-oz"><div class="n" id="oz_toplam">0</div><div class="t"><span class="dot" style="background:#9097ad;"></span> Toplam Çağrı</div></div>
      <div class="ck-oz"><div class="n" id="oz_gorusulen">0</div><div class="t"><span class="dot" style="background:#16a34a;"></span> Görüşülen</div></div>
      <div class="ck-oz"><div class="n" id="oz_randevu">0</div><div class="t"><span class="dot" style="background:#1565c0;"></span> Arama Randevusu</div></div>
      <div class="ck-oz"><div class="n" id="oz_satis">0</div><div class="t"><span class="dot" style="background:#b7791f;"></span> Telefonda Satış</div></div>
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

function ckStil(kod){
   switch(kod){
      case 4: return {c:'yesil', bl:'bl-yesil', b:'b-yesil'};
      case 1: return {c:'yesil', bl:'bl-yesil', b:'b-yesil'};
      case 2: return {c:'kirmizi', bl:'bl-kirmizi', b:'b-kirmizi'};
      case 5: return {c:'kirmizi', bl:'bl-kirmizi', b:'b-kirmizi'};
      case 0: return {c:'turuncu', bl:'bl-turuncu', b:'b-turuncu'};
      case 3: return {c:'mavi', bl:'bl-mavi', b:'b-mavi'};
      case 6: return {c:'mavi', bl:'bl-mavi', b:'b-mavi'};
      case 7: return {c:'altin', bl:'bl-altin', b:'b-altin'};
      default: return {c:'gri', bl:'', b:'b-gri'};
   }
}

function ckListeCiz(){
   var ara = ($('#ck_ara').val()||'').toLocaleLowerCase('tr');
   var veri = ckVeri.filter(function(k){
      if(!ara) return true;
      return (k.ad+' '+k.telefon+' '+(k.not||'')).toLocaleLowerCase('tr').indexOf(ara) >= 0;
   });
   if(!veri.length){ $('#ck_liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-inbox"></i><div>Kayıt yok.</div></div>'); return; }
   var html='';
   veri.forEach(function(k){
      var st = ckStil(k.sonuc_kod);
      var bas = (k.ad||'?').trim().charAt(0).toLocaleUpperCase('tr');
      html += '<div class="ck-item'+(ckSecili===k.id?' aktif':'')+'" data-id="'+ckEsc(k.id)+'">'
         + '<div class="ck-av">'+ckEsc(bas)+'</div>'
         + '<div class="bil"><div class="ad">'+ckEsc(k.ad)+'</div>'
         + '<div class="alt">'+(k.not? ckEsc(k.not) : ckEsc(k.telefon||'—'))+'</div></div>'
         + '<div class="sag"><div class="zmn">'+ckEsc(k.tarih)+'</div>'
         + '<span class="ck-badge '+st.b+'">'+ckEsc(k.sonuc)+'</span>'
         + (k.ses? ' <i class="fa fa-microphone" style="color:#7c3aed;margin-left:3px;" title="Ses kaydı var"></i>':'')
         + '</div></div>';
   });
   $('#ck_liste').html(html);
}

function ckDetayCiz(k){
   var st = ckStil(k.sonuc_kod);
   var bas = (k.ad||'?').trim().charAt(0).toLocaleUpperCase('tr');
   // Aynı müşterinin diğer çağrıları (telefon eşleşmesi)
   var gecmis = ckVeri.filter(function(x){ return x.id!==k.id && x.telefon && x.telefon===k.telefon; });
   var satis = (k.satis_tutari!=null) ? ('<div class="ck-d-chip">💰 Satış: <b>'+Number(k.satis_tutari).toLocaleString('tr-TR')+' ₺</b></div>') : '';
   var audio = k.ses
      ? ('<div class="ck-d-audio"><audio controls preload="none" src="'+ckEsc(k.ses)+'"></audio></div>')
      : ('<div class="ck-d-audio"><span class="yok">🎙️ Bu çağrı için ses kaydı yok.</span></div>');
   var not = k.not ? ('<div class="ck-d-not"><span class="lbl">GÖRÜŞME NOTU</span>'+ckEsc(k.not)+'</div>') : '';

   var gecmisHtml='';
   if(gecmis.length){
      gecmisHtml = '<div class="ck-gecmis-bas"><i class="fa fa-history"></i> Bu müşterinin diğer görüşmeleri ('+gecmis.length+')</div>';
      gecmis.slice(0,20).forEach(function(g){
         var gs=ckStil(g.sonuc_kod);
         gecmisHtml += '<div class="ck-g-row '+gs.bl+'"><div class="ust">'
            + '<span class="ck-badge '+gs.b+'">'+ckEsc(g.sonuc)+'</span>'
            + '<span class="zmn">'+ckEsc(g.tarih)+(g.sure_dk>0?(' · '+g.sure_dk+' dk'):'')+'</span></div>'
            + (g.not?('<div class="not">'+ckEsc(g.not)+'</div>'):'')
            + (g.ses?('<audio controls preload="none" src="'+ckEsc(g.ses)+'"></audio>'):'')
            + '</div>';
      });
   }

   $('#ck_detay').html(
      '<div class="ck-d-bas"><div class="ck-d-av">'+ckEsc(bas)+'</div>'
      + '<div><div class="ck-d-ad">'+ckEsc(k.ad)+'</div><div class="ck-d-tel"><i class="fa fa-phone"></i> '+ckEsc(k.telefon||'—')+'</div></div></div>'
      + '<div class="ck-d-satir">'
      +    '<div class="ck-d-chip"><span class="ck-badge '+st.b+'">'+ckEsc(k.sonuc)+'</span></div>'
      +    '<div class="ck-d-chip">🕒 <b>'+ckEsc(k.tarih)+'</b></div>'
      +    (k.sure_dk>0?('<div class="ck-d-chip">⏱️ Süre: <b>'+k.sure_dk+' dk</b></div>'):'')
      +    (k.personel?('<div class="ck-d-chip">👤 '+ckEsc(k.personel)+'</div>'):'')
      +    satis
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
      $('#oz_toplam').text(o.toplam||0); $('#oz_gorusulen').text(o.gorusulen||0);
      $('#oz_randevu').text(o.randevu||0); $('#oz_satis').text(o.satis||0);
      ckListeCiz();
   }).fail(function(){ $('#ck_liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-exclamation-triangle"></i><div>Yüklenemedi.</div></div>'); });
}

$(document).ready(ckYukle);
</script>
@endsection
