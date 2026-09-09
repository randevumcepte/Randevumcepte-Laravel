@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
   $gunler = [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];
   $sablonGunlere = [];
   foreach($sablon as $s){ $sablonGunlere[$s->hafta_gunu][] = $s; }
@endphp

<style>
   .dp-page{ padding:6px 2px; }
   .dp-head{ display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; }
   .dp-head h3{ margin:0; font-size:19px; color:#16a085; }
   .dp-head small{ color:#7f8c8d; display:block; }
   .dp-yayinla{ display:flex; align-items:center; gap:6px; background:#f5eefe; border:1px solid #ead4ff; border-radius:10px; padding:8px 10px; }
   .dp-yayinla input{ height:34px; border:1px solid #ccc; border-radius:6px; padding:0 8px; }
   .dp-grid{ display:grid; grid-template-columns:repeat(7,minmax(150px,1fr)); gap:8px; overflow-x:auto; }
   .dp-col{ background:#fff; border:1px solid #eee; border-radius:10px; min-height:120px; display:flex; flex-direction:column; }
   .dp-col-head{ font-weight:700; text-align:center; padding:8px; background:#16a085; color:#fff; border-radius:10px 10px 0 0; }
   .dp-col-body{ padding:6px; flex:1; }
   .dp-item{ background:#f8fdfb; border:1px solid #d4efe8; border-radius:8px; padding:6px 8px; margin-bottom:6px; font-size:12.5px; position:relative; }
   .dp-item .t{ font-weight:700; color:#117a65; }
   .dp-item .d{ color:#555; }
   .dp-item .k{ display:inline-block; background:#16a085; color:#fff; border-radius:999px; padding:1px 7px; font-size:11px; font-weight:700; }
   .dp-item .acts{ margin-top:4px; }
   .dp-item .acts a{ font-size:11px; margin-right:8px; cursor:pointer; }
   .dp-add{ display:block; text-align:center; border:1px dashed #b7e0d6; color:#16a085; border-radius:8px; padding:6px; font-size:12px; cursor:pointer; }
   .dp-add:hover{ background:#f0faf7; }
   @media (max-width:900px){ .dp-grid{ grid-template-columns:repeat(7,160px); } }
</style>

<div class="dp-page">
   <div class="dp-head">
      <div>
         <h3><i class="fa fa-grid"></i> Ders Programı</h3>
         <small>Haftalık sabit grup dersi programınızı bir kez tanımlayın. "Programı Yayınla" ileriye dönük ders oturumlarını takvime otomatik oluşturur.</small>
      </div>
      <div class="dp-yayinla">
         <label style="margin:0;font-size:12px;color:#5C008E;">Başlangıç</label>
         <input type="date" id="dp-baslangic" value="{{ date('Y-m-d') }}">
         <label style="margin:0;font-size:12px;color:#5C008E;">Hafta</label>
         <input type="number" id="dp-hafta" min="1" max="12" value="4" style="width:60px;">
         <button class="btn btn-success btn-sm" onclick="dpYayinla();return false;"><i class="fa fa-rocket"></i> Programı Yayınla</button>
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
                  <div class="t">{{ $s->ders_tipi ?: 'Grup Dersi' }} <span class="k">{{ (int)$s->kapasite }} kişi</span></div>
                  <div class="d">{{ substr($s->saat,0,5) }} - {{ substr($s->saat_bitis,0,5) }}</div>
                  <div class="d">{{ $s->personel ? $s->personel->personel_adi : '—' }}</div>
                  <div class="acts">
                     <a class="text-primary"
                        onclick="dpDuzenle({{ $s->id }},{{ $gid }},'{{ addslashes($s->ders_tipi) }}','{{ $s->personel_id }}','{{ substr($s->saat,0,5) }}','{{ substr($s->saat_bitis,0,5) }}',{{ (int)$s->kapasite }},'{{ $s->hizmet_id }}')">
                        <i class="fa fa-edit"></i> Düzenle</a>
                     <a class="text-danger" onclick="dpSil({{ $s->id }})"><i class="fa fa-trash"></i> Sil</a>
                  </div>
               </div>
               @endforeach
            @endif
            <span class="dp-add" onclick="dpEkle({{ $gid }})"><i class="fa fa-plus"></i> Ders Ekle</span>
         </div>
      </div>
      @endforeach
   </div>
</div>

{{-- Sablon satiri ekle/duzenle modali --}}
<div class="modal fade" id="dp-modal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header" style="background:#16a085;color:#fff;">
            <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
            <h4 class="modal-title" id="dp-modal-baslik">Ders Ekle</h4>
         </div>
         <div class="modal-body">
            <input type="hidden" id="dp-sablon-id" value="">
            <div class="form-group"><label>Gün</label>
               <select id="dp-gun" class="form-control">
                  @foreach($gunler as $gid=>$gad)<option value="{{ $gid }}">{{ $gad }}</option>@endforeach
               </select></div>
            <div class="form-group"><label>Ders Tipi</label>
               <input type="text" id="dp-tipi" class="form-control" placeholder="Reformer / Mat / Crossfit / Birebir"></div>
            <div class="form-group"><label>Eğitmen</label>
               <select id="dp-personel" class="form-control">
                  <option value="">— Seçiniz —</option>
                  @foreach($personeller as $p)<option value="{{ $p->id }}">{{ $p->personel_adi }}</option>@endforeach
               </select></div>
            <div class="form-group"><label>Hizmet <small style="color:#95a5a6;">(paketten seans düşümü için)</small></label>
               <select id="dp-hizmet" class="form-control">
                  <option value="">— Hizmet bağlama (paket düşmez) —</option>
                  @foreach(($hizmetler ?? []) as $h)<option value="{{ $h->id }}">{{ $h->hizmet_adi }}</option>@endforeach
               </select></div>
            <div class="row">
               <div class="form-group col-xs-4"><label>Başlangıç</label>
                  <input type="time" id="dp-saat" class="form-control" value="09:00"></div>
               <div class="form-group col-xs-4"><label>Bitiş</label>
                  <input type="time" id="dp-saat-bitis" class="form-control" value="10:00"></div>
               <div class="form-group col-xs-4"><label>Kapasite</label>
                  <input type="number" id="dp-kapasite" class="form-control" min="1" value="3"></div>
            </div>
            <button class="btn btn-success btn-block" onclick="dpKaydet();return false;"><i class="fa fa-save"></i> Kaydet</button>
         </div>
      </div>
   </div>
</div>

<script>
(function(){
   function _csrf(){ return $('meta[name="csrf-token"]').attr('content'); }
   function _sube(){ return "{{ $isletme->id }}"; }
   function _post(url,data){ data=data||{}; data.sube=_sube();
      return $.ajax({url:url,method:'POST',data:data,headers:{'X-CSRF-TOKEN':_csrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}); }

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
         .done(function(r){ if(r.durum==='ok') swal('Yayınlandı', r.olusan+' yeni ders oluşturuldu ('+r.atlanan+' zaten vardı).','success');
                            else swal('Hata',(r&&r.mesaj)||'Yayınlanamadı','warning'); })
         .fail(function(x){ swal('Hata',(x.responseJSON&&x.responseJSON.mesaj)||'Yayınlanamadı','error'); });
      });
   };
})();
</script>
@endsection
