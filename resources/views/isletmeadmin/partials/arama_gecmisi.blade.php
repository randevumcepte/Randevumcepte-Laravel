{{-- Musterinin ARAMA GECMISI (arama randevulari + gorusme notlari + santral cagrilari). ORTAK:
     musteri detay > Arama Gecmisi sekmesi ve takvimlerdeki "Arama Gecmisi" popup'i.
     Veri: /isletmeyonetim/musteri-arama-gecmisi (yalniz cagri merkezi paketi; personel kendi kapsaminda).
     window.AramaGecmisi.yukle($kok, userId) -> kok icine cizer; .popup(userId, ad) -> pencerede acar.
     Tetik: [class~=rd-arama-gecmisi-btn] data-userid + data-ad. --}}
@include('isletmeadmin.partials.cagri_kayit_stil')
<style>
#agp_ov{ position:fixed; inset:0; background:rgba(20,10,40,.55); z-index:2000001; display:flex; align-items:center; justify-content:center; padding:16px; }
#agp_ov .agp-kutu{ background:#f7f5fb; border-radius:16px; width:100%; max-width:1080px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 30px 70px -20px rgba(40,10,70,.55); }
#agp_ov .agp-bas{ background:#fff; border-bottom:1px solid #ece6f3; padding:14px 18px; display:flex; align-items:center; gap:10px; }
#agp_ov .agp-bas b{ color:#5C008E; font-size:16.5px; flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
#agp_ov .agp-kapat{ width:34px; height:34px; border:0; background:transparent; border-radius:8px; font-size:24px; line-height:1; color:#6b7280; cursor:pointer; }
#agp_ov .agp-kapat:hover{ background:#f3eefa; }
#agp_ov .agp-govde{ padding:14px 16px; overflow-y:auto; }
#agp_ov .ck-liste{ max-height:48vh; }
#agp_ov .ck-detay{ min-height:0; }
@media (max-width:767px){ #agp_ov{ padding:0; } #agp_ov .agp-kutu{ max-height:100vh; height:100%; border-radius:0; } }
</style>
<script>
(function(){
   if (window.AramaGecmisi) return;
   function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
   function sure(sn){ sn=parseInt(sn)||0; if(sn<=0) return ''; if(sn<60) return sn+' sn'; var d=Math.floor(sn/60), s=sn%60; return d+' dk'+(s?(' '+s+' sn'):''); }
   // Sonuc kodu -> renk (aranacakDurumMetin ile ayni kodlar)
   function renk(k){
      if(k.tip==='cagri') return k.yon==='gelen' ? {b:'b-yesil',et:'↙ Gelen'} : {b:'b-mavi',et:'↗ Giden'};
      var c=k.sonuc_kod;
      if(c===4||c===1||c===8) return {b:'b-yesil',et:k.sonuc};
      if(c===7) return {b:'b-altin',et:k.sonuc};
      if(c===3||c===6) return {b:'b-turuncu',et:k.sonuc};
      if(c===0||c===2||c===5||c===9) return {b:'b-kirmizi',et:k.sonuc};
      return {b:'b-gri',et:k.sonuc||'—'};
   }
   function baslik(k){ return k.personel || (k.tip==='cagri' ? 'Santral çağrısı' : 'Görüşme'); }
   function bas1(k){ return k.tip==='cagri' ? '<i class="fa fa-phone"></i>' : esc(baslik(k).trim().charAt(0).toLocaleUpperCase('tr')); }
   function iskelet(){
      return '<div class="ck-wrap" style="padding-top:6px;">'
         +'<div class="ck-ozet c5">'
         +'<div class="ck-oz t-gri"><div class="n" data-o="gorusme">0</div><div class="t"><span class="dot"></span> Toplam Görüşme</div></div>'
         +'<div class="ck-oz t-mor"><div class="n" data-o="kayitli">0</div><div class="t"><span class="dot"></span> Ses Kayıtlı</div></div>'
         +'<div class="ck-oz t-mor" style="--acc:#e07a1a;"><div class="n" data-o="bekleyen">0</div><div class="t"><span class="dot"></span> Bekleyen Arama Randevusu</div></div>'
         +'<div class="ck-oz t-yesil"><div class="n" data-o="gelen">0</div><div class="t"><span class="dot"></span> Gelen</div></div>'
         +'<div class="ck-oz t-mavi"><div class="n" data-o="giden">0</div><div class="t"><span class="dot"></span> Giden</div></div>'
         +'</div><div class="agp-randevular"></div>'
         +'<div class="ck-grid"><div class="ck-panel">'
         +'<div class="ck-ara"><i class="fa fa-search"></i><input type="text" class="agp-ara" placeholder="Not, sonuç veya personel ara..." aria-label="Görüşmelerde ara"></div>'
         +'<div class="ck-liste agp-liste"><div class="ck-bos" style="padding:40px;"><i class="fa fa-spinner fa-spin"></i></div></div>'
         +'</div><div class="ck-panel"><div class="ck-detay agp-detay"><div class="ck-bos"><i class="fa fa-headphones"></i><div>Soldan bir görüşme seçin.</div></div></div></div>'
         +'</div></div>';
   }
   function randevuCiz($k, r){
      if(!r.length){ $k.find('.agp-randevular').html(''); return; }
      var et={bekliyor:['b-turuncu','Bekliyor'],gecikti:['b-kirmizi','Gecikti'],tamamlandi:['b-yesil','Arandı']};
      var h='<div class="ck-ar-bas"><i class="fa fa-calendar-check-o"></i> Arama Randevuları ('+r.length+')</div><div class="ck-ar-liste">';
      r.forEach(function(x){
         var e=et[x.durum]||['b-gri',x.durum];
         h+='<div class="ck-ar-kart d-'+esc(x.durum)+'"><div style="display:flex;align-items:center;gap:8px;">'
           +'<div class="zm">'+esc(x.tarih)+' '+esc(x.saat)+'</div><span class="ck-badge '+e[0]+'" style="margin-left:auto;">'+e[1]+'</span></div>'
           +(x.personel?'<div class="al"><i class="fa fa-user"></i> '+esc(x.personel)+'</div>':'')
           +(x.liste?'<div class="al"><i class="fa fa-list"></i> '+esc(x.liste)+'</div>':'')
           +(x.aranan?'<div class="al"><i class="fa fa-check"></i> Arandı: '+esc(x.aranan)+'</div>':'')
           +'</div>';
      });
      $k.find('.agp-randevular').html(h+'</div>');
   }
   function listeCiz($k){
      var veri=$k.data('agpVeri')||[], sec=$k.data('agpSecili');
      var ara=($k.find('.agp-ara').val()||'').toLocaleLowerCase('tr');
      var v=veri.filter(function(k){ return !ara || (k.not+' '+k.sonuc+' '+k.personel+' '+k.kategori+' '+k.tarih).toLocaleLowerCase('tr').indexOf(ara)>=0; });
      if(!v.length){ $k.find('.agp-liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-inbox"></i><div>Görüşme kaydı yok.</div></div>'); return; }
      var h='';
      v.forEach(function(k){
         var r=renk(k), b=baslik(k);
         h+='<div class="ck-item'+(sec===k.id?' aktif':'')+'" data-id="'+esc(k.id)+'" tabindex="0" role="button">'
           +'<div class="ck-av">'+bas1(k)+'</div>'
           +'<div class="bil"><div class="ad">'+esc(b)+'</div><div class="alt">'+esc(k.not||k.kategori||(sure(k.sure_sn)||'—'))+'</div></div>'
           +'<div class="sag"><div class="zmn">'+esc(k.tarih)+'</div><span class="ck-badge '+r.b+'">'+esc(r.et)+'</span>'
           +(k.ses?' <i class="fa fa-microphone" style="color:#7c3aed;margin-left:3px;" title="Ses kaydı var"></i>':'')
           +'</div></div>';
      });
      $k.find('.agp-liste').html(h);
   }
   function detayCiz($k, k){
      var r=renk(k), b=baslik(k);
      $k.find('.agp-detay').html('<div class="ck-d-bas"><div class="ck-d-av">'+bas1(k)+'</div>'
         +'<div><div class="ck-d-ad">'+esc(b)+'</div><div class="ck-d-tel">'+(k.tip==='cagri'?'Santral çağrısı (not girilmemiş)':'Görüşme notu')+'</div></div></div>'
         +'<div class="ck-d-satir"><div class="ck-d-chip"><span class="ck-badge '+r.b+'">'+esc(r.et)+'</span></div>'
         +(k.tip==='cagri'?'<div class="ck-d-chip">'+esc(k.sonuc)+'</div>':(k.yon?'<div class="ck-d-chip">'+(k.yon==='gelen'?'↙ Gelen':'↗ Giden')+'</div>':''))
         +'<div class="ck-d-chip">🕒 <b>'+esc(k.tarih)+'</b></div>'
         +(sure(k.sure_sn)?'<div class="ck-d-chip">⏱️ Süre: <b>'+sure(k.sure_sn)+'</b></div>':'')
         +(k.randevu?'<div class="ck-d-chip">📅 Arama randevusu: <b>'+esc(k.randevu)+'</b></div>':'')
         +(k.satis!==null&&k.satis!==undefined?'<div class="ck-d-chip">💰 Satış: <b>'+Number(k.satis).toLocaleString('tr-TR',{minimumFractionDigits:2})+' ₺</b></div>':'')
         +(k.kategori?'<div class="ck-d-chip">🏷️ <b>'+esc(k.kategori)+'</b></div>':'')
         +'</div>'
         +(k.ses?'<div class="ck-d-audio"><audio controls preload="none" src="'+esc(k.ses)+'"></audio></div>':'<div class="ck-d-audio"><span class="yok">🎙️ Bu görüşme için ses kaydı yok.</span></div>')
         +(k.not?'<div class="ck-d-not"><span class="lbl">GÖRÜŞME NOTU</span>'+esc(k.not)+'</div>':''));
   }
   function sec($k, id){
      $k.data('agpSecili', id);
      $k.find('.agp-liste .ck-item').removeClass('aktif').filter(function(){ return String($(this).attr('data-id'))===String(id); }).addClass('aktif');
      var k=($k.data('agpVeri')||[]).filter(function(x){ return String(x.id)===String(id); })[0]; if(k) detayCiz($k, k);
   }
   function yukle($k, userId){
      if(!$k.find('.agp-liste').length) $k.html(iskelet());
      var no=($k.data('agpNo')||0)+1; $k.data('agpNo', no);
      $k.find('.agp-liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-spinner fa-spin"></i></div>');
      return $.get('/isletmeyonetim/musteri-arama-gecmisi',{user_id:userId,sube:$('input[name="sube"]').val()},function(res){
         if($k.data('agpNo')!==no) return;
         var veri=(res&&res.gecmis)?res.gecmis:[], o=(res&&res.ozet)?res.ozet:{};
         $k.data('agpVeri', veri).data('agpSecili', null);
         var say={gorusme:veri.length,kayitli:o.kayitli||0,bekleyen:o.bekleyen||0,gelen:o.gelen||0,giden:o.giden||0};
         $k.find('[data-o]').each(function(){ $(this).text(say[$(this).attr('data-o')]); });
         randevuCiz($k,(res&&res.randevular)?res.randevular:[]);
         listeCiz($k);
         if(veri.length) sec($k, veri[0].id);
      }).fail(function(){ if($k.data('agpNo')===no) $k.find('.agp-liste').html('<div class="ck-bos" style="padding:40px;"><i class="fa fa-exclamation-triangle"></i><div>Yüklenemedi.</div></div>'); });
   }
   $(document).on('click','.agp-kok .agp-liste .ck-item',function(){ sec($(this).closest('.agp-kok'), $(this).attr('data-id')); });
   $(document).on('keydown','.agp-kok .agp-liste .ck-item',function(e){ if(e.which===13||e.which===32){ e.preventDefault(); $(this).trigger('click'); } });
   $(document).on('input','.agp-kok .agp-ara',function(){ listeCiz($(this).closest('.agp-kok')); });

   var oncekiOdak=null;
   function kapat(){ $('#agp_ov').remove(); $(document).off('keydown.agp'); if(oncekiOdak && oncekiOdak.focus) try{ oncekiOdak.focus(); }catch(e){} }
   function popup(userId, ad){
      if(!userId) return;
      kapat(); oncekiOdak=document.activeElement;
      // Acik Bootstrap penceresi (randevu/on gorusme detayi) odak tuzagi arama kutusunu kilitlemesin
      $(document).off('focusin.bs.modal');
      var $ov=$('<div id="agp_ov" role="dialog" aria-modal="true" aria-label="Arama geçmişi"><div class="agp-kutu">'
         +'<div class="agp-bas"><i class="fa fa-phone" style="color:#5C008E"></i><b></b><button type="button" class="agp-kapat" aria-label="Kapat">&times;</button></div>'
         +'<div class="agp-govde agp-kok"></div></div></div>');
      $ov.find('.agp-bas b').text('Arama Geçmişi — '+(ad||'Müşteri'));
      $('body').append($ov);
      $ov.on('click',function(e){ if(e.target===this) kapat(); });
      $ov.find('.agp-kapat').on('click',kapat).trigger('focus');
      $(document).on('keydown.agp',function(e){ if(e.which===27){ e.stopPropagation(); kapat(); } });
      yukle($ov.find('.agp-kok'), userId);
   }
   $(document).on('click','.rd-arama-gecmisi-btn',function(e){
      e.preventDefault(); e.stopPropagation();
      popup($(this).attr('data-userid'), $(this).attr('data-ad'));
   });
   window.AramaGecmisi = { yukle:yukle, popup:popup };
})();
</script>
