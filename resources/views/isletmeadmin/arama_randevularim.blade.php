@extends("layout.layout_isletmeadmin")
@section("content")

<style>
.arl-wrap{ --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC8; }
.arl-hero{ background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%); border-radius:18px; padding:22px 26px; color:#fff; margin-bottom:18px; box-shadow:0 12px 30px -10px rgba(92,0,142,.45); position:relative; overflow:hidden; }
.arl-hero:after{ content:""; position:absolute; right:-40px; top:-40px; width:170px; height:170px; border-radius:50%; background:rgba(255,255,255,.10); }
.arl-hero h4{ margin:0 0 6px; font-weight:700; font-size:22px; color:#fff; }
.arl-geri{ display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:30px; background:#fff; color:#5C008E !important; font-size:14px; font-weight:800; text-decoration:none; margin-right:12px; vertical-align:middle; box-shadow:0 6px 16px -6px rgba(0,0,0,.4); transition:transform .12s, filter .15s; }
.arl-geri:hover{ color:#5C008E; transform:translateX(-2px); filter:brightness(.97); }
.arl-geri i{ font-size:16px; }
.arl-hero p{ margin:0; opacity:.92; font-size:13.5px; max-width:680px; }

/* Araç çubuğu: tarih seçimi + hızlı butonlar */
.arl-bar{ background:#fff; border:1px solid #eef0f5; border-radius:14px; padding:14px 16px; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 6px 20px -14px rgba(30,30,60,.16); }
.arl-bar label{ font-size:13px; font-weight:700; color:#5a5570; margin:0; }
.arl-bar input[type=date]{ border:1px solid #e3dcf0; border-radius:10px; padding:9px 12px; font-size:14px; outline:none; }
.arl-bar input[type=date]:focus{ border-color:#9D5DC8; }
.arl-btn{ border:1px solid #e3dcf0; background:#fff; color:#5C008E; border-radius:10px; padding:9px 14px; font-size:13px; font-weight:700; cursor:pointer; }
.arl-btn:hover{ border-color:#9D5DC8; background:#faf7ff; }
.arl-btn.aktif{ background:linear-gradient(120deg,#5C008E,#7B2FB8); color:#fff; border-color:transparent; }
.arl-ozet{ margin-left:auto; display:flex; gap:10px; flex-wrap:wrap; }
.arl-oz{ background:#faf9fc; border:1px solid #efeaf6; border-radius:10px; padding:7px 13px; text-align:center; min-width:78px; }
.arl-oz .n{ font-size:18px; font-weight:800; line-height:1; } .arl-oz .t{ font-size:11px; color:#8a85a0; margin-top:3px; display:block; }
.n-mavi{ color:#1565c0; } .n-kirmizi{ color:#dc2626; } .n-yesil{ color:#16a34a; } .n-turuncu{ color:#e07a1a; } .n-koyu{ color:#241b3a; }

/* Liste */
.arl-liste{ display:flex; flex-direction:column; gap:10px; }
.arl-row{ background:#fff; border:1px solid #eef0f5; border-left:5px solid #2563eb; border-radius:14px; padding:14px 16px; box-shadow:0 4px 14px -10px rgba(30,30,60,.18); }
.arl-row.gecikti{ border-left-color:#dc2626; background:#fef4f4; }
.arl-row.zamaninda{ border-left-color:#16a34a; }
.arl-row.gec{ border-left-color:#e07a1a; }
.arl-row .ust{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.arl-row .ad{ font-weight:700; font-size:15px; color:#241b3a; }
.arl-badge{ font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:20px; }
.b-mavi{ background:#e7f1fd; color:#1565c0; } .b-kirmizi{ background:#fdecec; color:#dc2626; }
.b-yesil{ background:#e4f7ec; color:#16a34a; } .b-turuncu{ background:#fff2e3; color:#e07a1a; }
.arl-row .zaman{ margin-left:auto; font-size:14px; font-weight:800; color:#374151; }
.arl-row.gecikti .zaman{ color:#dc2626; }
.arl-row .not{ font-size:13px; color:#6b7280; font-style:italic; margin:7px 0 2px; }
.arl-row .ses{ font-size:12px; color:#7c3aed; margin-top:3px; }
.arl-row .aksiyon{ display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }
.arl-row .aksiyon button{ border:0; border-radius:9px; padding:8px 14px; font-size:13px; font-weight:700; cursor:pointer; }
.b-ara{ background:#16a34a; color:#fff; } .b-ara:hover{ filter:brightness(.95); }
.b-ertele{ background:#eef0f5; color:#374151; } .b-ertele:hover{ background:#e3e6ef; }
.arl-bos{ text-align:center; color:#9097ad; padding:50px 20px; font-size:14px; }
.arl-bos i{ font-size:40px; color:#cfc7df; display:block; margin-bottom:12px; }

/* Ertele modalı */
.arl-ov{ position:fixed; inset:0; background:rgba(20,20,40,.55); backdrop-filter:blur(3px); z-index:99998; display:none; align-items:center; justify-content:center; }
.arl-ov .box{ background:#fff; border-radius:16px; padding:22px; width:92%; max-width:360px; box-shadow:0 24px 64px rgba(0,0,0,.25); }
.arl-ov h5{ margin:0 0 14px; font-weight:700; font-size:16px; color:#241b3a; }
.arl-ov .alan{ margin-bottom:12px; } .arl-ov label{ display:block; font-size:12.5px; font-weight:700; color:#5a5570; margin-bottom:4px; }
.arl-ov input{ width:100%; border:1px solid #e3dcf0; border-radius:10px; padding:9px 12px; font-size:14px; }
.arl-ov .btns{ display:flex; gap:8px; margin-top:6px; }
.arl-ov .btns button{ flex:1; border:0; border-radius:10px; padding:11px; font-weight:700; cursor:pointer; }
.arl-ov .kaydet{ background:#5C008E; color:#fff; } .arl-ov .vazgec{ background:#9097ad; color:#fff; }
</style>

<div class="arl-wrap">
   <div class="arl-hero">
      <h4><a href="javascript:void(0)" class="arl-geri" onclick="arlGeri()" title="Geri"><i class="fa fa-arrow-left"></i> Geri</a><i class="fa fa-calendar-check-o"></i> Arama Randevularım</h4>
      <p>Kimi ne zaman arayacağınızın listesi. Tarih seçin, o güne ait aramalar listelensin. Zamanı geçmiş aramalar <b>kırmızı</b> görünür. Buradan doğrudan arayabilir veya başka güne erteleyebilirsiniz.</p>
   </div>

   <div class="arl-bar">
      <label for="arl_tarih"><i class="fa fa-calendar"></i> Tarih:</label>
      <input type="date" id="arl_tarih">
      <button class="arl-btn" id="arl_bugun">Bugün</button>
      <button class="arl-btn" id="arl_tumu">Tüm Bekleyenler</button>
      <div class="arl-ozet" id="arl_ozet"></div>
   </div>

   <div class="arl-liste" id="arl_liste">
      <div class="arl-bos"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>
   </div>
</div>

{{-- Ertele modalı --}}
<div class="arl-ov" id="arl_ertele_ov">
   <div class="box">
      <h5><i class="fa fa-clock-o"></i> Aramayı Ertele</h5>
      <div class="alan"><label>Yeni Tarih</label><input type="date" id="arl_e_tarih"></div>
      <div class="alan"><label>Yeni Saat</label><input type="time" id="arl_e_saat"></div>
      <div class="btns">
         <button class="kaydet" id="arl_e_kaydet">Kaydet</button>
         <button class="vazgec" id="arl_e_vazgec">Vazgeç</button>
      </div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<script>
function arlEsc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
var arlSube = $('input[name="sube"]').val();
var arlToken = $('input[name="_token"]').val();
var arlErteleId = null;

function arlDurumBilgi(d){
   switch(d){
      case 'gecikti':   return { cls:'gecikti',   bcls:'b-kirmizi', et:'⚠️ Gecikti' };
      case 'zamaninda': return { cls:'zamaninda', bcls:'b-yesil',   et:'✓ Zamanında Arandı' };
      case 'gec':       return { cls:'gec',       bcls:'b-turuncu', et:'Geç Arandı' };
      default:          return { cls:'',          bcls:'b-mavi',    et:'Planlı' };
   }
}

function arlYukle(){
   var veri = { sube: arlSube };
   if (!arlTumMod){ veri.tarih = $('#arl_tarih').val(); }
   $('#arl_liste').html('<div class="arl-bos"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
   $.get('/isletmeyonetim/arama-randevularim-veri', veri, function(res){
      var liste = (res && res.randevular) ? res.randevular : [];
      // özet
      var say = { toplam:liste.length, bekliyor:0, gecikti:0, tamam:0 };
      liste.forEach(function(r){
         if (r.durum==='gecikti') say.gecikti++;
         else if (r.durum==='zamaninda'||r.durum==='gec') say.tamam++;
         else say.bekliyor++;
      });
      $('#arl_ozet').html(
         '<div class="arl-oz"><div class="n n-koyu">'+say.toplam+'</div><span class="t">Toplam</span></div>'+
         '<div class="arl-oz"><div class="n n-mavi">'+say.bekliyor+'</div><span class="t">Bekleyen</span></div>'+
         '<div class="arl-oz"><div class="n n-kirmizi">'+say.gecikti+'</div><span class="t">Geciken</span></div>'+
         '<div class="arl-oz"><div class="n n-yesil">'+say.tamam+'</div><span class="t">Arandı</span></div>'
      );

      if (!liste.length){
         $('#arl_liste').html('<div class="arl-bos"><i class="fa fa-calendar-o"></i>Bu seçime ait arama randevusu yok.</div>');
         return;
      }
      var html = '';
      liste.forEach(function(r){
         var b = arlDurumBilgi(r.durum);
         var tamam = (r.durum==='zamaninda'||r.durum==='gec');
         var ses = (r.ses_sayisi>0) ? ('<div class="ses">🎙️ '+r.ses_sayisi+' ses kaydı'+(r.ses_son?(' · son: '+r.ses_son):'')+'</div>') : '';
         var not = r.not ? ('<div class="not">“'+arlEsc(r.not)+'”</div>') : '';
         // Ara butonu HER zaman gorunur (tamamlanmis randevu da tekrar aranabilir);
         // Ertele yalnizca henuz aranmamis (bekleyen/geciken) randevularda anlamli.
         var aksiyon =
            '<div class="aksiyon">'+
               '<button class="b-ara" data-id="'+arlEsc(r.id)+'"><i class="fa fa-phone"></i> Ara</button>'+
               (tamam ? '' : '<button class="b-ertele" data-id="'+arlEsc(r.id)+'"><i class="fa fa-clock-o"></i> Ertele</button>')+
            '</div>';
         html += '<div class="arl-row '+b.cls+'" id="arl_row_'+arlEsc(r.id)+'">'+
            '<div class="ust"><span class="ad">'+arlEsc(r.ad)+'</span>'+
               '<span class="arl-badge '+b.bcls+'">'+b.et+'</span>'+
               '<span class="zaman">'+arlEsc(r.tarih)+' '+arlEsc(r.saat)+'</span></div>'+
            not + ses + aksiyon +
         '</div>';
      });
      $('#arl_liste').html(html);
   }).fail(function(){
      $('#arl_liste').html('<div class="arl-bos"><i class="fa fa-exclamation-triangle"></i>Yüklenirken hata oluştu.</div>');
   });
}

var arlTumMod = false;
function arlModGuncelle(){
   $('#arl_tumu').toggleClass('aktif', arlTumMod);
   $('#arl_tarih').prop('disabled', arlTumMod);
}

$('#arl_tarih').on('change', function(){ arlTumMod = false; arlModGuncelle(); arlYukle(); });
$('#arl_bugun').on('click', function(){ arlTumMod = false; arlModGuncelle(); $('#arl_tarih').val(arlBugunStr()); arlYukle(); });
$('#arl_tumu').on('click', function(){ arlTumMod = !arlTumMod; arlModGuncelle(); arlYukle(); });

// Ara: softphone (WebRTC) hazirsa dogrudan ondan ara; degilse santral originate (Bria).
$(document).on('click', '#arl_liste .b-ara', function(){
   var id = $(this).data('id');
   var $btn = $(this);
   var softphoneHazir = (window.webphoneHazir === true && typeof window.webphoneAra === 'function');
   $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Bağlanıyor...');
   $.ajax({ url:'/isletmeyonetim/arama-baslat', method:'POST',
      data:{ aranacak_musteri_id:id, softphone: softphoneHazir ? 1 : 0, sube:arlSube, _token:arlToken },
      success:function(res){
         if (res && res.success){
            if (res.softphone && res.numara_ara){
               var basladi = window.webphoneAra(res.numara_ara);
               if (!basladi && typeof swal==='function'){
                  swal({type:'warning', title:'Softphone hazır değil', text:'Sayfanın üst köşesindeki telefonun "Bağlandı" olduğundan emin olun.'});
               }
            } else {
               if (typeof swal==='function') swal({type:'success', title:'Arama başlatıldı', text:'Santral sizi bağlıyor. Telefonunuz çalacak.', timer:3000, showConfirmButton:false});
            }
            setTimeout(arlYukle, 1500);
         } else {
            $btn.prop('disabled', false).html('<i class="fa fa-phone"></i> Ara');
            alert((res&&res.message)?res.message:'Arama başlatılamadı');
         }
      },
      error:function(){ $btn.prop('disabled', false).html('<i class="fa fa-phone"></i> Ara'); alert('Arama başlatılamadı'); }
   });
});

// Ertele: modal aç
$(document).on('click', '#arl_liste .b-ertele', function(){
   arlErteleId = $(this).data('id');
   $('#arl_e_tarih').val($('#arl_tarih').val() || arlBugunStr());
   $('#arl_e_saat').val('');
   $('#arl_ertele_ov').css('display','flex');
});
$('#arl_e_vazgec').on('click', function(){ $('#arl_ertele_ov').hide(); arlErteleId=null; });
$('#arl_ertele_ov').on('click', function(e){ if(e.target===this){ $(this).hide(); arlErteleId=null; } });
$('#arl_e_kaydet').on('click', function(){
   var t = $('#arl_e_tarih').val(), s = $('#arl_e_saat').val();
   if (!t || !s){ alert('Tarih ve saat seçin'); return; }
   $.ajax({ url:'/isletmeyonetim/arama-randevu-ertele', method:'POST',
      data:{ aranacak_musteri_id:arlErteleId, tarih:t, saat:s, sube:arlSube, _token:arlToken },
      success:function(res){ if(res&&res.success){ $('#arl_ertele_ov').hide(); arlErteleId=null; arlYukle(); } else { alert((res&&res.message)?res.message:'Ertelenemedi'); } },
      error:function(){ alert('Ertelenemedi'); }
   });
});

function arlBugunStr(){ var d=new Date(); var m=('0'+(d.getMonth()+1)).slice(-2); var g=('0'+d.getDate()).slice(-2); return d.getFullYear()+'-'+m+'-'+g; }
function arlGeri(){ if(window.history.length>1){ window.history.back(); } else { window.location.href='/isletmeyonetim/arama-listelerim'+(arlSube?('?sube='+arlSube):''); } }

$(document).ready(function(){
   var acId = null;
   try { acId = new URLSearchParams(window.location.search).get('ac'); } catch(e){}
   $('#arl_tarih').val(arlBugunStr());
   arlModGuncelle();
   arlYukle();
   // Popup'tan "Hemen Ara" ile gelindiyse ilgili satırı vurgula
   if (acId){
      setTimeout(function(){
         var row = document.getElementById('arl_row_'+acId);
         if (row){ row.style.boxShadow='0 0 0 3px #16a34a'; row.scrollIntoView({block:'center'}); }
      }, 800);
   }
});
</script>
@endsection
