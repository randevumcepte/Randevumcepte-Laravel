@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
   $gunler = [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];
   $sablonGunlere = [];
   foreach($sablon as $s){ $sablonGunlere[$s->hafta_gunu][] = $s; }
@endphp

<link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/src/plugins/air-datepicker/dist/css/datepicker.min.css') }}">

<style>
   .dp-page{ padding:8px 4px; }
   .dp-head{ display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; }
   .dp-head .dp-title h3{ margin:0; font-size:20px; font-weight:800; color:#0e6b57; display:flex; align-items:center; gap:8px; }
   .dp-head .dp-title small{ color:#7f8c8d; display:block; margin-top:3px; max-width:560px; }
   .dp-yayinla{ display:flex; align-items:flex-end; gap:10px; background:linear-gradient(135deg,#f0faf7,#eafaf4); border:1px solid #cdeee4; border-radius:14px; padding:12px 14px; }
   .dp-yayinla .fld{ display:flex; flex-direction:column; }
   .dp-yayinla label{ margin:0 0 4px; font-size:11px; font-weight:700; color:#0e6b57; letter-spacing:.2px; }
   .dp-yayinla input{ height:38px; border:1px solid #cfe6de; border-radius:9px; padding:0 10px; font-size:14px; background:#fff; color:#2c3e50; }
   .dp-yayinla input:focus{ outline:none; border-color:#16a085; box-shadow:0 0 0 3px rgba(22,160,133,.15); }
   .dp-yayinla .dp-yayinla-btn{ height:38px; border:none; border-radius:9px; background:#16a085; color:#fff; font-weight:700; font-size:14px; padding:0 16px; cursor:pointer; white-space:nowrap; }
   .dp-yayinla .dp-yayinla-btn:hover{ background:#12876f; }

   .dp-grid{ display:grid; grid-template-columns:repeat(7,minmax(158px,1fr)); gap:10px; overflow-x:auto; padding-bottom:6px; }
   .dp-col{ background:#fff; border:1px solid #eceff1; border-radius:14px; min-height:150px; display:flex; flex-direction:column; box-shadow:0 1px 3px rgba(0,0,0,.04); }
   .dp-col-head{ font-weight:700; font-size:13.5px; text-align:center; padding:11px; background:linear-gradient(135deg,#16a085,#12957d); color:#fff; border-radius:14px 14px 0 0; letter-spacing:.3px; }
   .dp-col-body{ padding:8px; flex:1; }
   .dp-item{ background:#f8fdfb; border:1px solid #dcefe9; border-radius:11px; padding:9px 10px; margin-bottom:8px; font-size:12.5px; }
   .dp-item .t{ font-weight:700; color:#0e6b57; display:flex; align-items:center; justify-content:space-between; gap:6px; }
   .dp-item .k{ background:#16a085; color:#fff; border-radius:999px; padding:2px 9px; font-size:10.5px; font-weight:700; white-space:nowrap; }
   .dp-item .saat{ color:#34495e; font-weight:600; margin-top:4px; }
   .dp-item .egt{ color:#7f8c8d; margin-top:1px; }
   .dp-item .hzm{ margin-top:4px; }
   .dp-item .hzm .tag{ display:inline-block; font-size:10.5px; background:#eef7ff; color:#2471a3; border:1px solid #d3e7f7; border-radius:6px; padding:1px 7px; }
   .dp-item .hzm .tag.off{ background:#f4f6f7; color:#95a5a6; border-color:#e5e8ea; }
   .dp-item .acts{ margin-top:7px; display:flex; gap:6px; }
   .dp-item .acts button{ flex:1; border:1px solid #d7dde3; background:#fff; border-radius:7px; padding:4px 0; font-size:11px; cursor:pointer; color:#34495e; }
   .dp-item .acts button.del{ border-color:#f0b4ac; color:#c0392b; flex:0 0 34px; }
   .dp-item .acts button:hover{ background:#f5f7f8; }
   .dp-add{ display:flex; align-items:center; justify-content:center; gap:6px; border:1.5px dashed #b7e0d6; color:#16a085; border-radius:10px; padding:9px; font-size:12.5px; font-weight:600; cursor:pointer; }
   .dp-add:hover{ background:#f0faf7; border-color:#16a085; }
   @media (max-width:900px){ .dp-grid{ grid-template-columns:repeat(7,168px); } }

   /* ---- temiz modal ---- */
   #dp-modal .modal-dialog{ width:470px; max-width:94%; }
   #dp-modal .modal-content{ border:none; border-radius:16px; overflow:hidden; box-shadow:0 14px 46px rgba(0,0,0,.28); }
   #dp-modal .dpm-head{ background:linear-gradient(135deg,#16a085,#12957d); color:#fff; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; }
   #dp-modal .dpm-head h4, #dp-modal .dpm-head #dp-modal-baslik{ margin:0; font-size:17px; font-weight:700; color:#fff !important; }
   #dp-modal .dpm-head .dpm-close{ background:none; border:none; color:#fff; font-size:22px; line-height:1; cursor:pointer; opacity:.9; }
   #dp-modal .dpm-body{ padding:20px; background:#fff; }
   #dp-modal .dpm-grid{ display:grid; grid-template-columns:1fr 1fr; gap:13px 14px; }
   #dp-modal .dpm-field{ display:flex; flex-direction:column; }
   #dp-modal .dpm-field.full{ grid-column:1 / -1; }
   #dp-modal .dpm-field label{ font-size:12px; font-weight:600; color:#5f6b7a; margin:0 0 5px; }
   #dp-modal .dpm-field label .hint{ color:#adb5bd; font-weight:400; }
   #dp-modal .dpm-field input, #dp-modal .dpm-field select{
      height:42px; border:1px solid #d7dde3; border-radius:9px; padding:0 12px; font-size:14px; color:#2c3e50; background:#fff; width:100%; }
   #dp-modal .dpm-field input:focus, #dp-modal .dpm-field select:focus{ outline:none; border-color:#16a085; box-shadow:0 0 0 3px rgba(22,160,133,.15); }
   #dp-modal .dpm-btn{ display:flex; align-items:center; justify-content:center; gap:8px; width:100%; margin-top:18px; height:46px; border:none; border-radius:10px; background:#16a085; color:#fff; font-size:15px; font-weight:700; cursor:pointer; }
   #dp-modal .dpm-btn:hover{ background:#12876f; }
</style>

<div class="dp-page">
   <div class="dp-head">
      <div class="dp-title">
         <h3>🗓️ Ders Programı</h3>
         <small>Haftalık sabit grup dersi programınızı bir kez tanımlayın. "Programı Yayınla" ileriye dönük ders oturumlarını takvime otomatik oluşturur.</small>
      </div>
      <div class="dp-yayinla">
         <div class="fld"><label>Başlangıç</label>
            <input type="text" id="dp-baslangic" value="{{ date('Y-m-d') }}" readonly style="width:130px;cursor:pointer;"></div>
         <div class="fld"><label>Hafta</label>
            <input type="number" id="dp-hafta" min="1" max="12" value="4" style="width:64px;"></div>
         <button class="dp-yayinla-btn" onclick="dpYayinla();return false;">🚀 Programı Yayınla</button>
      </div>
   </div>

   <div class="dp-grid">
      @foreach($gunler as $gid=>$gad)
      <div class="dp-col">
         <div class="dp-col-head">{{ $gad }}</div>
         <div class="dp-col-body">
            @if(!empty($sablonGunlere[$gid]))
               @foreach($sablonGunlere[$gid] as $s)
               <div class="dp-item">
                  <div class="t"><span>{{ $s->ders_tipi ?: 'Grup Dersi' }}</span> <span class="k">{{ (int)$s->kapasite }} kişi</span></div>
                  <div class="saat">🕒 {{ substr($s->saat,0,5) }} – {{ substr($s->saat_bitis,0,5) }}</div>
                  <div class="egt">👤 {{ $s->personel ? $s->personel->personel_adi : '—' }}</div>
                  <div class="hzm">
                     @if($s->hizmet_id)
                        <span class="tag">paket düşer</span>
                     @else
                        <span class="tag off">paket bağlı değil</span>
                     @endif
                  </div>
                  <div class="acts">
                     <button onclick="dpDuzenle({{ $s->id }},{{ $gid }},'{{ addslashes($s->ders_tipi) }}','{{ $s->personel_id }}','{{ substr($s->saat,0,5) }}','{{ substr($s->saat_bitis,0,5) }}',{{ (int)$s->kapasite }},'{{ $s->hizmet_id }}')">✎ Düzenle</button>
                     <button class="del" onclick="dpSil({{ $s->id }})" title="Sil">🗑</button>
                  </div>
               </div>
               @endforeach
            @endif
            <div class="dp-add" onclick="dpEkle({{ $gid }})">＋ Ders Ekle</div>
         </div>
      </div>
      @endforeach
   </div>
</div>

{{-- Sablon satiri ekle/duzenle modali --}}
<div class="modal fade" id="dp-modal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="dpm-head">
            <h4 id="dp-modal-baslik">Ders Ekle</h4>
            <button type="button" class="dpm-close" data-dismiss="modal">&times;</button>
         </div>
         <div class="dpm-body">
            <input type="hidden" id="dp-sablon-id" value="">
            <div class="dpm-grid">
               <div class="dpm-field full"><label>Gün</label>
                  <select id="dp-gun">
                     @foreach($gunler as $gid=>$gad)<option value="{{ $gid }}">{{ $gad }}</option>@endforeach
                  </select></div>
               <div class="dpm-field full"><label>Ders Tipi</label>
                  <input type="text" id="dp-tipi" placeholder="Reformer / Mat / Crossfit / Birebir"></div>
               <div class="dpm-field full"><label>Eğitmen</label>
                  <select id="dp-personel">
                     <option value="">— Seçiniz —</option>
                     @foreach($personeller as $p)<option value="{{ $p->id }}">{{ $p->personel_adi }}</option>@endforeach
                  </select></div>
               <div class="dpm-field full"><label>Hizmet <span class="hint">(paketten seans düşümü için)</span></label>
                  <select id="dp-hizmet">
                     <option value="">— Hizmet bağlama (paket düşmez) —</option>
                     @foreach(($hizmetler ?? []) as $h)<option value="{{ $h->id }}">{{ $h->hizmet_adi }}</option>@endforeach
                  </select></div>
               <div class="dpm-field"><label>Başlangıç</label>
                  <input type="time" id="dp-saat" value="09:00"></div>
               <div class="dpm-field"><label>Bitiş</label>
                  <input type="time" id="dp-saat-bitis" value="10:00"></div>
               <div class="dpm-field full"><label>Kapasite (kişi)</label>
                  <input type="number" id="dp-kapasite" min="1" value="3"></div>
            </div>
            <button class="dpm-btn" onclick="dpKaydet();return false;">💾 Kaydet</button>
         </div>
      </div>
   </div>
</div>

<script src="{{ secure_asset('public/yeni_panel/src/plugins/air-datepicker/dist/js/datepicker.min.js') }}"></script>
<script>
(function(){
   function _csrf(){ return $('meta[name="csrf-token"]').attr('content'); }
   function _sube(){ return "{{ $isletme->id }}"; }
   function _post(url,data){ data=data||{}; data.sube=_sube();
      return $.ajax({url:url,method:'POST',data:data,headers:{'X-CSRF-TOKEN':_csrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}); }

   // Projenin air-datepicker'i (tr dil objesi inline — ayri i18n dosyasi yok)
   var _trLang = {
      days:['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
      daysShort:['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'],
      daysMin:['Pz','Pt','Sa','Ça','Pe','Cu','Ct'],
      months:['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
      monthsShort:['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'],
      today:'Bugün', clear:'Temizle', dateFormat:'yyyy-mm-dd', firstDay:1
   };
   $(function(){
      if($.fn.datepicker){
         $('#dp-baslangic').datepicker({ language:_trLang, autoClose:true, dateFormat:'yyyy-mm-dd', position:'bottom left', minDate:new Date() });
      }
   });

   window.dpEkle = function(gun){
      $('#dp-sablon-id').val(''); $('#dp-gun').val(gun);
      $('#dp-tipi').val(''); $('#dp-personel').val(''); $('#dp-hizmet').val(''); $('#dp-kapasite').val(3);
      $('#dp-saat').val('09:00'); $('#dp-saat-bitis').val('10:00');
      $('#dp-modal-baslik').text('Ders Ekle'); $('#dp-modal').modal();
   };
   window.dpDuzenle = function(id,gun,tipi,pid,saat,bitis,kap,hid){
      $('#dp-sablon-id').val(id); $('#dp-gun').val(gun);
      $('#dp-tipi').val(tipi); $('#dp-personel').val(pid||''); $('#dp-hizmet').val(hid||''); $('#dp-kapasite').val(kap);
      $('#dp-saat').val(saat); $('#dp-saat-bitis').val(bitis);
      $('#dp-modal-baslik').text('Ders Düzenle'); $('#dp-modal').modal();
   };
   window.dpKaydet = function(){
      _post('{{ url('/isletmeyonetim/ders-sablon-kaydet') }}', {
         sablon_id:$('#dp-sablon-id').val(), hafta_gunu:$('#dp-gun').val(), ders_tipi:$('#dp-tipi').val(),
         personel_id:$('#dp-personel').val(), hizmet_id:$('#dp-hizmet').val(), saat:$('#dp-saat').val(), saat_bitis:$('#dp-saat-bitis').val(),
         kapasite:$('#dp-kapasite').val()
      }).done(function(r){ if(r.durum==='ok') location.reload(); else swal('Hata',(r&&r.mesaj)||'Kaydedilemedi','error'); })
        .fail(function(){ swal('Hata','Kaydedilemedi','error'); });
   };
   window.dpSil = function(id){
      swal({title:'Silinsin mi?',text:'Bu programdan kaldırılacak (oluşturulmuş dersler kalır).',type:'warning',
         showCancelButton:true,confirmButtonText:'Sil',cancelButtonText:'Vazgeç',confirmButtonColor:'#c0392b'})
      .then(function(res){ if(res.value){ _post('{{ url('/isletmeyonetim/ders-sablon-sil') }}',{sablon_id:id})
         .done(function(){ location.reload(); }); } });
   };
   window.dpYayinla = function(){
      var hafta=$('#dp-hafta').val(), bas=$('#dp-baslangic').val();
      swal({title:'Program yayınlansın mı?',text:bas+' tarihinden itibaren '+hafta+' hafta boyunca ders oturumları takvime oluşturulacak.',
         type:'question',showCancelButton:true,confirmButtonText:'Yayınla',cancelButtonText:'Vazgeç',confirmButtonColor:'#16a085'})
      .then(function(res){ if(!res.value) return;
         _post('{{ url('/isletmeyonetim/ders-programi-yayinla') }}',{hafta:hafta,baslangic:bas})
         .done(function(r){ if(r.durum==='ok') swal('Yayınlandı', r.olusan+' yeni ders oluşturuldu ('+r.atlanan+' zaten vardı).','success').then(function(){ location.reload(); });
                            else swal('Hata',(r&&r.mesaj)||'Yayınlanamadı','warning'); })
         .fail(function(x){ swal('Hata',(x.responseJSON&&x.responseJSON.mesaj)||'Yayınlanamadı','error'); });
      });
   };
})();
</script>
@endsection
