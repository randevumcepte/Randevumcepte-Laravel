{{-- Otomatik Ders Plani (studyo modu) — slot bazli planlama modali. Paketler + musteri detay ortak.
     Kullanim: dersPlaniModalAc(musteriId, redirectUrl?) --}}
<style>
   #ders_plani_modal .dp-chip-row{ display:flex;flex-wrap:wrap;gap:7px;margin:8px 0; }
   #ders_plani_modal .dp-chip{ padding:7px 14px;border:1px solid #e0d6ec;border-radius:20px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;color:#4b5563;user-select:none; }
   #ders_plani_modal .dp-chip.on{ background:#5C008E;border-color:#5C008E;color:#fff; }
   #ders_plani_modal .dp-slot{ display:flex;align-items:center;gap:10px;padding:11px 12px;border:1px solid #e0d6ec;border-radius:12px;margin-bottom:7px;cursor:pointer; }
   #ders_plani_modal .dp-slot.on{ background:rgba(92,0,142,.08);border-color:#5C008E; }
   #ders_plani_modal .dp-slot .dp-rad{ width:20px;height:20px;border-radius:50%;border:2px solid #cbd5e1;flex:0 0 auto; }
   #ders_plani_modal .dp-slot.on .dp-rad{ border-color:#5C008E;background:#5C008E;box-shadow:inset 0 0 0 3px #fff; }
   #ders_plani_modal .dp-uyari{ background:#fff7ed;color:#9a3412;padding:10px;border-radius:9px;font-size:12.5px; }
   #ders_plani_modal .modal-header .modal-title,
   #ders_plani_modal .modal-header .close,
   #ders_plani_modal .modal-header .close span{ color:#fff !important; opacity:1; text-shadow:none; }
</style>
<div class="modal fade" id="ders_plani_modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><i class="fa fa-refresh"></i> Otomatik Ders Planı</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Kapat"><span aria-hidden="true">&times;</span></button>
         </div>
         <div class="modal-body">
            <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Paketin/hizmetin seanslarını, seçtiğiniz sabit ders slotlarına (gün+saat+eğitmen) haftalık otomatik dağıtır.</p>
            <div class="form-group">
               <label style="font-weight:600;font-size:13px;">Paket / Hizmet</label>
               <select id="dp_kaynak" class="form-control" onchange="dpRenderSlots()"></select>
            </div>
            <div id="dp_slot_wrap" style="margin-top:6px;">
               <label style="font-weight:600;font-size:13px;">Ders Slotları</label>
               <div id="dp_egitmen_filtre" class="dp-chip-row"></div>
               <div id="dp_gun_sekme" class="dp-chip-row"></div>
               <div id="dp_slot_liste"></div>
            </div>
         </div>
         <div class="modal-footer">
            <span id="dp_secim_ozet" style="margin-right:auto;color:#5C008E;font-weight:700;font-size:13px;"></span>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
            <button type="button" class="btn btn-primary" id="dp_kaydet_btn" style="background:#5C008E;border-color:#5C008E;" onclick="dersPlaniKaydet()">Planla ve Dağıt</button>
         </div>
      </div>
   </div>
</div>
<script>
   var DP_MUSTERI = null, DP_REDIRECT = null;
   var dpKaynaklar = [], dpSablonlar = [], dpSecili = {}, dpFiltreEgitmen = null, dpAktifGun = null;
   var DP_GUNAD = ['','Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
   function dpCsrf(){ return ($('meta[name="csrf-token"]').attr('content')) || ($('input[name="_token"]').first().val()) || ''; }
   async function dpFetchP(url, body){
      const r = await fetch(url, { method:'POST', headers:{ 'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':dpCsrf(),'X-Requested-With':'XMLHttpRequest' }, body: JSON.stringify(body||{}) });
      let j = {}; try { j = await r.json(); } catch(e){}
      return { ok:r.ok, status:r.status, data:j };
   }

   // Modal kapaninca (planlamadan) satis sonrasi yonlendirme yapilsin.
   $(document).on('hidden.bs.modal','#ders_plani_modal',function(){ if(DP_REDIRECT){ var u=DP_REDIRECT; DP_REDIRECT=null; window.location.href=u; } });

   async function dersPlaniModalAc(musteriId, redirectUrl){
      DP_MUSTERI = musteriId; DP_REDIRECT = redirectUrl || null;
      dpKaynaklar = []; dpSablonlar = []; dpSecili = {}; dpFiltreEgitmen = null; dpAktifGun = null;
      $('#dp_kaynak').html('<option>Yükleniyor…</option>');
      $('#dp_egitmen_filtre,#dp_gun_sekme,#dp_slot_liste').html('');
      $('#dp_secim_ozet').text('');
      $('#ders_plani_modal').modal('show');
      const res = await dpFetchP('/isletmeyonetim/ders-tekrarli-kaynaklar', { musteri_id:DP_MUSTERI });
      if(!(res.ok && res.data && res.data.durum==='ok')){ $('#dp_kaynak').html('<option>Yüklenemedi</option>'); return; }
      dpKaynaklar = res.data.kaynaklar || [];
      dpSablonlar = res.data.sablonlar || [];
      if(!dpKaynaklar.length){
         $('#dp_kaynak').html('<option value="">Uygun (kalan seanslı) satış yok</option>');
         $('#dp_slot_wrap').hide(); $('#dp_kaydet_btn').prop('disabled',true); return;
      }
      $('#dp_slot_wrap').show(); $('#dp_kaydet_btn').prop('disabled',false);
      $('#dp_kaynak').html(dpKaynaklar.map(function(k,i){ return '<option value="'+i+'">'+k.etiket+' • '+k.kalan_seans+' seans</option>'; }).join(''));
      dpRenderSlots();
   }
   function dpKaynak(){ var i = parseInt($('#dp_kaynak').val()); return (i>=0 && dpKaynaklar[i]) ? dpKaynaklar[i] : null; }
   function dpUygunSablon(){ var k = dpKaynak(); if(!k) return []; return dpSablonlar.filter(function(s){ return String(s.hizmet_id) === String(k.hizmet_id); }); }
   function dpRenderSlots(){
      dpSecili = {};
      var slot = dpUygunSablon();
      if(!slot.length){
         $('#dp_egitmen_filtre,#dp_gun_sekme').html('');
         $('#dp_slot_liste').html('<div class="dp-uyari">Bu paketin hizmeti için haftalık programda ders yok. Önce Ders Programı ekleyin.</div>');
         dpOzetGuncelle(); return;
      }
      dpRenderEgit(); dpRenderGunVeSlot();
   }
   function dpRenderEgit(){
      var slot = dpUygunSablon(); var egit={}; slot.forEach(function(s){ if(s.personel_id) egit[s.personel_id]=s.personel; });
      var egitArr=Object.keys(egit); var eh='';
      if(egitArr.length>1){
         eh += '<span class="dp-chip '+(dpFiltreEgitmen===null?'on':'')+'" onclick="dpFiltreEgit(null)">Tüm Eğitmenler</span>';
         egitArr.forEach(function(pid){ eh += '<span class="dp-chip '+(dpFiltreEgitmen===pid?'on':'')+'" onclick="dpFiltreEgit(\''+pid+'\')">'+egit[pid]+'</span>'; });
      }
      $('#dp_egitmen_filtre').html(eh);
   }
   function dpFiltreEgit(pid){ dpFiltreEgitmen = pid; dpAktifGun = null; dpRenderEgit(); dpRenderGunVeSlot(); }
   function dpFiltreli(){ return dpUygunSablon().filter(function(s){ return dpFiltreEgitmen===null || String(s.personel_id)===String(dpFiltreEgitmen); }); }
   function dpRenderGunVeSlot(){
      var f = dpFiltreli();
      var gunler = []; f.forEach(function(s){ if(gunler.indexOf(s.hafta_gunu)<0) gunler.push(s.hafta_gunu); });
      gunler.sort(function(a,b){return a-b;});
      if(dpAktifGun===null || gunler.indexOf(dpAktifGun)<0) dpAktifGun = gunler.length? gunler[0] : null;
      $('#dp_gun_sekme').html(gunler.map(function(g){ return '<span class="dp-chip '+(dpAktifGun===g?'on':'')+'" onclick="dpGunSec('+g+')">'+DP_GUNAD[g]+'</span>'; }).join(''));
      var gsl = f.filter(function(s){ return s.hafta_gunu===dpAktifGun; }).sort(function(a,b){ return (a.saat||'').localeCompare(b.saat||''); });
      if(!gsl.length){ $('#dp_slot_liste').html('<div style="color:#888;padding:14px;">Bu günde ders yok.</div>'); dpOzetGuncelle(); return; }
      $('#dp_slot_liste').html(gsl.map(function(s){
         var on = !!dpSecili[s.id];
         var alt = (s.personel||'Eğitmen atanmamış') + (s.ders_tipi? ' • '+s.ders_tipi:'') + (s.kapasite>0? ' • '+s.kapasite+' kişi':'');
         return '<div class="dp-slot '+(on?'on':'')+'" onclick="dpSlotTikla('+s.id+')"><div class="dp-rad"></div>'+
                '<div><div style="font-weight:700;font-size:14px;">'+s.saat+(s.saat_bitis?' – '+s.saat_bitis:'')+'</div>'+
                '<div style="font-size:12px;color:#6b7280;">'+alt+'</div></div></div>';
      }).join(''));
      dpOzetGuncelle();
   }
   function dpGunSec(g){ dpAktifGun = g; dpRenderGunVeSlot(); }
   function dpSlotTikla(id){ if(dpSecili[id]) delete dpSecili[id]; else dpSecili[id]=true; dpRenderGunVeSlot(); }
   function dpOzetGuncelle(){ var n = Object.keys(dpSecili).length; $('#dp_secim_ozet').text(n? (n+' slot seçildi') : ''); }

   async function dersPlaniKaydet(){
      var k = dpKaynak();
      if(!k){ swal('Uyarı','Paket/hizmet seçin.','warning'); return; }
      var slotlar = Object.keys(dpSecili).map(Number);
      if(!slotlar.length){ swal('Uyarı','En az bir ders slotu seçin.','warning'); return; }
      $('#dp_kaydet_btn').prop('disabled',true).text('Dağıtılıyor…');
      var res = await dpFetchP('/isletmeyonetim/ders-tekrarli-kaydet', {
         musteri_id: DP_MUSTERI, hizmet_id: k.hizmet_id, toplam_seans: k.kalan_seans,
         sablonlar: slotlar, adisyon_paket_id: k.adisyon_paket_id, adisyon_hizmet_id: k.adisyon_hizmet_id,
         baslangic: '{{ date('Y-m-d') }}'
      });
      $('#dp_kaydet_btn').prop('disabled',false).text('Planla ve Dağıt');
      if(res.ok && res.data && res.data.durum==='ok'){
         var s = res.data.sonuc || {};
         var _redir = DP_REDIRECT; DP_REDIRECT = null; // basarili planlamada modal-kapali yonlendirmesini iptal et
         $('#ders_plani_modal').modal('hide');
         swal({ title:'Planlandı', text:(s.olusan||0)+' ders otomatik oluşturuldu'+((s.yerlesmeyen>0)?(' • '+s.yerlesmeyen+' seans yerleştirilemedi'):'')+'.', icon:'success' })
            .then(function(){ if(_redir) window.location.href=_redir; });
      } else {
         swal('Hata',(res.data && res.data.mesaj)||'Kaydedilemedi.','error');
      }
   }
</script>
