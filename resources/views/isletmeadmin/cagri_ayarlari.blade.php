@extends("layout.layout_isletmeadmin")
@section("content")

<style>
.ca-wrap{ --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC8; }
.ca-hero{ background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%); border-radius:18px; padding:20px 24px; color:#fff; margin-bottom:18px; box-shadow:0 12px 30px -10px rgba(92,0,142,.45); position:relative; overflow:hidden; }
.ca-hero:after{ content:""; position:absolute; right:-40px; top:-40px; width:180px; height:180px; border-radius:50%; background:rgba(255,255,255,.10); }
.ca-hero h4{ margin:0 0 4px; font-weight:700; font-size:21px; color:#fff; }
.ca-hero p{ margin:0; opacity:.92; font-size:13px; max-width:680px; }

.ca-grid{ display:grid; grid-template-columns:1fr 1fr; gap:18px; align-items:start; }
@media (max-width:991px){ .ca-grid{ grid-template-columns:1fr; } }

.ca-panel{ background:#fff; border-radius:18px; border:1px solid #efeaf6; box-shadow:0 6px 20px -12px rgba(30,30,60,.2); overflow:hidden; }
.ca-phead{ padding:15px 18px; border-bottom:1px solid #f0eef5; display:flex; align-items:center; gap:10px; }
.ca-phead h5{ margin:0; font-weight:700; font-size:15.5px; color:#241b3a; }
.ca-phead .fa{ color:#9D5DC8; }
.ca-phead .ekle{ margin-left:auto; background:linear-gradient(120deg,#5C008E,#7B2FB8); color:#fff; border:none; border-radius:10px; padding:7px 13px; font-size:12.5px; font-weight:700; cursor:pointer; }
.ca-pbody{ padding:16px 18px; }

.ca-bos{ text-align:center; color:#9a95ab; padding:26px 10px; font-size:13px; }
.ca-bos .fa{ font-size:30px; color:#cfc7df; display:block; margin-bottom:8px; }

/* Script kartı */
.ca-script{ border:1px solid #efeaf6; border-left:4px solid #9D5DC8; border-radius:12px; padding:12px 14px; margin-bottom:11px; }
.ca-script .bas{ font-weight:700; color:#241b3a; font-size:14px; display:flex; align-items:center; gap:8px; }
.ca-script .bas .pas{ font-size:10.5px; font-weight:700; background:#f0eef5; color:#8a85a0; border-radius:20px; padding:2px 8px; }
.ca-script .ic{ font-size:12.5px; color:#6a6580; margin-top:6px; white-space:pre-wrap; line-height:1.45; max-height:64px; overflow:hidden; }
.ca-script .ar{ margin-top:9px; display:flex; gap:7px; }

/* Form alanları */
.ca-inp{ width:100%; border:1px solid #e3dcf0; border-radius:10px; padding:9px 12px; font-size:13.5px; outline:none; }
.ca-inp:focus{ border-color:#9D5DC8; }
textarea.ca-inp{ resize:vertical; min-height:90px; }
.ca-form{ background:#faf9fc; border:1px dashed #d9cfee; border-radius:12px; padding:14px; margin-bottom:14px; display:none; }
.ca-form .satir{ margin-bottom:10px; }
.ca-form label{ font-size:12px; font-weight:700; color:#6a6580; display:block; margin-bottom:4px; }
.ca-btn{ border:none; border-radius:10px; padding:9px 16px; font-size:13px; font-weight:700; cursor:pointer; }
.ca-btn.kaydet{ background:linear-gradient(120deg,#5C008E,#7B2FB8); color:#fff; }
.ca-btn.iptal{ background:#eceaf3; color:#6a6580; }
.ca-mini{ border:none; background:#f3eefa; color:#5C008E; border-radius:8px; padding:6px 10px; font-size:12px; font-weight:700; cursor:pointer; }
.ca-mini.sil{ background:#fdecec; color:#d33; }

/* Kategori */
.ca-kat{ border:1px solid #efeaf6; border-radius:12px; padding:12px 14px; margin-bottom:11px; }
.ca-kat-bas{ display:flex; align-items:center; gap:8px; }
.ca-kat-ad{ font-weight:700; color:#241b3a; font-size:14px; }
.ca-kat-ad .fa{ color:#7B2FB8; margin-right:6px; }
.ca-kat-arac{ margin-left:auto; display:flex; gap:6px; }
.ca-altlar{ display:flex; flex-wrap:wrap; gap:7px; margin-top:10px; }
.ca-alt{ background:#f6f0fc; border:1px solid #e6d8f5; border-radius:20px; padding:4px 10px 4px 12px; font-size:12px; color:#5C008E; font-weight:600; display:flex; align-items:center; gap:7px; }
.ca-alt .x{ cursor:pointer; color:#b39ad6; font-weight:700; }
.ca-alt .x:hover{ color:#d33; }
.ca-alt-ekle{ display:flex; gap:6px; margin-top:10px; }
.ca-alt-ekle input{ flex:1; border:1px solid #e3dcf0; border-radius:8px; padding:6px 10px; font-size:12.5px; outline:none; }
.ca-spin{ text-align:center; padding:24px; color:#9a95ab; }
</style>

<div class="ca-wrap">

   <div class="ca-hero">
      <h4><i class="fa fa-cogs"></i> {{ $sayfa_baslik }}</h4>
      <p>Çağrı merkezi personelinizin kullanacağı <b>görüşme scriptlerini</b> ve görüşme sonucu için <b>kategori/alt kategori</b> ağacını buradan yönetin. Personel çalışma ekranında bunları seçip kullanır.</p>
   </div>

   <div class="ca-grid">

      {{-- Scriptler --}}
      <div class="ca-panel">
         <div class="ca-phead">
            <i class="fa fa-file-text-o"></i><h5>Görüşme Scriptleri</h5>
            <button class="ekle" id="ca_script_yeni"><i class="fa fa-plus"></i> Yeni Script</button>
         </div>
         <div class="ca-pbody">
            <div class="ca-form" id="ca_script_form">
               <input type="hidden" id="ca_script_id">
               <div class="satir"><label>Başlık</label><input type="text" class="ca-inp" id="ca_script_baslik" placeholder="Örn: Tanıtım Araması"></div>
               <div class="satir"><label>Script İçeriği</label><textarea class="ca-inp" id="ca_script_icerik" placeholder="Merhaba, ben ... Size ... hakkında bilgi vermek istiyorum..."></textarea></div>
               <button class="ca-btn kaydet" id="ca_script_kaydet"><i class="fa fa-save"></i> Kaydet</button>
               <button class="ca-btn iptal" id="ca_script_iptal">İptal</button>
            </div>
            <div id="ca_script_liste"><div class="ca-spin"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div></div>
         </div>
      </div>

      {{-- Kategoriler --}}
      <div class="ca-panel">
         <div class="ca-phead">
            <i class="fa fa-sitemap"></i><h5>Sonuç Kategorileri</h5>
            <button class="ekle" id="ca_kat_yeni"><i class="fa fa-plus"></i> Yeni Kategori</button>
         </div>
         <div class="ca-pbody">
            <div class="ca-form" id="ca_kat_form">
               <div class="satir"><label>Ana Kategori Adı</label><input type="text" class="ca-inp" id="ca_kat_ad" placeholder="Örn: İlgilendi"></div>
               <button class="ca-btn kaydet" id="ca_kat_kaydet"><i class="fa fa-save"></i> Ekle</button>
               <button class="ca-btn iptal" id="ca_kat_iptal">İptal</button>
            </div>
            <div id="ca_kat_liste"><div class="ca-spin"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div></div>
         </div>
      </div>

   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

<script>
function caEsc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
function caTok(){ return $('input[name="_token"]').val(); }
function caSube(){ return $('input[name="sube"]').val(); }

/* ============ SCRIPTLER ============ */
function caScriptYukle(){
   $.get('/isletmeyonetim/cagri-script-liste', { sube:caSube() }, function(res){
      var s = (res && res.scriptler) ? res.scriptler : [];
      if (!s.length){ $('#ca_script_liste').html('<div class="ca-bos"><i class="fa fa-file-o"></i>Henüz script yok. "Yeni Script" ile ekleyin.</div>'); return; }
      var html='';
      s.forEach(function(x){
         var pasif = (parseInt(x.aktif,10)===0) ? '<span class="pas">Pasif</span>' : '';
         html += '<div class="ca-script">'+
                    '<div class="bas">'+caEsc(x.baslik)+pasif+'</div>'+
                    (x.icerik ? '<div class="ic">'+caEsc(x.icerik)+'</div>' : '')+
                    '<div class="ar">'+
                       '<button class="ca-mini ca-script-duzenle" data-id="'+x.id+'" data-baslik="'+caEsc(x.baslik)+'" data-icerik="'+caEsc(x.icerik||'')+'"><i class="fa fa-pencil"></i> Düzenle</button>'+
                       '<button class="ca-mini sil ca-script-sil" data-id="'+x.id+'"><i class="fa fa-trash"></i> Sil</button>'+
                    '</div>'+
                 '</div>';
      });
      $('#ca_script_liste').html(html);
   });
}
function caScriptFormAc(id, baslik, icerik){
   $('#ca_script_id').val(id||'');
   $('#ca_script_baslik').val(baslik||'');
   $('#ca_script_icerik').val(icerik||'');
   $('#ca_script_form').slideDown(120);
}
$('#ca_script_yeni').on('click', function(){ caScriptFormAc('', '', ''); });
$('#ca_script_iptal').on('click', function(){ $('#ca_script_form').slideUp(120); });
$(document).on('click', '.ca-script-duzenle', function(){
   caScriptFormAc($(this).data('id'), $(this).data('baslik'), $(this).data('icerik'));
   $('html,body').animate({ scrollTop:$('#ca_script_form').offset().top-90 }, 200);
});
$('#ca_script_kaydet').on('click', function(){
   var baslik = $('#ca_script_baslik').val().trim();
   if (!baslik){ swal({ type:'warning', title:'Başlık gerekli' }); return; }
   $.post('/isletmeyonetim/cagri-script-kaydet', { id:$('#ca_script_id').val(), baslik:baslik, icerik:$('#ca_script_icerik').val(), sube:caSube(), _token:caTok() }, function(res){
      if (res.success){ $('#ca_script_form').slideUp(120); caScriptYukle(); }
      else swal({ type:'error', title:'Hata', text:res.message||'Kaydedilemedi' });
   });
});
$(document).on('click', '.ca-script-sil', function(){
   var id = $(this).data('id');
   swal({ title:'Script silinsin mi?', type:'warning', showCancelButton:true, confirmButtonText:'Sil', cancelButtonText:'Vazgeç' }).then(function(r){
      if (r.value){ $.post('/isletmeyonetim/cagri-script-sil', { id:id, sube:caSube(), _token:caTok() }, function(){ caScriptYukle(); }); }
   });
});

/* ============ KATEGORİLER ============ */
function caKatYukle(){
   $.get('/isletmeyonetim/cagri-kategori-liste', { sube:caSube() }, function(res){
      var k = (res && res.kategoriler) ? res.kategoriler : [];
      if (!k.length){ $('#ca_kat_liste').html('<div class="ca-bos"><i class="fa fa-sitemap"></i>Henüz kategori yok. "Yeni Kategori" ile ekleyin.</div>'); return; }
      var html='';
      k.forEach(function(ana){
         var altHtml='';
         (ana.altlar||[]).forEach(function(a){
            altHtml += '<span class="ca-alt">'+caEsc(a.ad)+'<span class="x ca-alt-sil" data-id="'+a.id+'" title="Sil">&times;</span></span>';
         });
         html += '<div class="ca-kat">'+
                    '<div class="ca-kat-bas">'+
                       '<span class="ca-kat-ad"><i class="fa fa-folder"></i>'+caEsc(ana.ad)+'</span>'+
                       '<span class="ca-kat-arac"><button class="ca-mini sil ca-kat-sil" data-id="'+ana.id+'"><i class="fa fa-trash"></i></button></span>'+
                    '</div>'+
                    '<div class="ca-altlar">'+ (altHtml || '<span style="font-size:12px;color:#b6aecb;">alt kategori yok</span>') +'</div>'+
                    '<div class="ca-alt-ekle">'+
                       '<input type="text" class="ca-alt-input" data-ust="'+ana.id+'" placeholder="Alt kategori ekle...">'+
                       '<button class="ca-mini ca-alt-ekle-btn" data-ust="'+ana.id+'"><i class="fa fa-plus"></i></button>'+
                    '</div>'+
                 '</div>';
      });
      $('#ca_kat_liste').html(html);
   });
}
$('#ca_kat_yeni').on('click', function(){ $('#ca_kat_ad').val(''); $('#ca_kat_form').slideDown(120); });
$('#ca_kat_iptal').on('click', function(){ $('#ca_kat_form').slideUp(120); });
$('#ca_kat_kaydet').on('click', function(){
   var ad = $('#ca_kat_ad').val().trim();
   if (!ad){ swal({ type:'warning', title:'Ad gerekli' }); return; }
   $.post('/isletmeyonetim/cagri-kategori-kaydet', { ad:ad, sube:caSube(), _token:caTok() }, function(res){
      if (res.success){ $('#ca_kat_form').slideUp(120); caKatYukle(); }
      else swal({ type:'error', title:'Hata', text:res.message||'Eklenemedi' });
   });
});
// Alt kategori ekle
function caAltEkle(ustId, input){
   var ad = (input.val()||'').trim();
   if (!ad){ return; }
   $.post('/isletmeyonetim/cagri-kategori-kaydet', { ad:ad, ust_id:ustId, sube:caSube(), _token:caTok() }, function(res){
      if (res.success){ caKatYukle(); }
      else swal({ type:'error', title:'Hata', text:res.message||'Eklenemedi' });
   });
}
$(document).on('click', '.ca-alt-ekle-btn', function(){
   var ust = $(this).data('ust');
   caAltEkle(ust, $('.ca-alt-input[data-ust="'+ust+'"]'));
});
$(document).on('keypress', '.ca-alt-input', function(e){
   if (e.which===13){ e.preventDefault(); caAltEkle($(this).data('ust'), $(this)); }
});
$(document).on('click', '.ca-alt-sil', function(){
   var id=$(this).data('id');
   $.post('/isletmeyonetim/cagri-kategori-sil', { id:id, sube:caSube(), _token:caTok() }, function(){ caKatYukle(); });
});
$(document).on('click', '.ca-kat-sil', function(){
   var id=$(this).data('id');
   swal({ title:'Kategori silinsin mi?', text:'Alt kategorileri de silinecek.', type:'warning', showCancelButton:true, confirmButtonText:'Sil', cancelButtonText:'Vazgeç' }).then(function(r){
      if (r.value){ $.post('/isletmeyonetim/cagri-kategori-sil', { id:id, sube:caSube(), _token:caTok() }, function(){ caKatYukle(); }); }
   });
});

$(document).ready(function(){ caScriptYukle(); caKatYukle(); });
</script>
@endsection
