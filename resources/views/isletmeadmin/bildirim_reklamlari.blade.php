@extends('layout.layout_isletmeadmin')
@section('content')
@php
   $turEtiket = [
      'kampanya'    => ['Kampanya / İndirim', 'fa-tags', '#7c3aed'],
      'bos_slot'    => ['Boş Slot Doldurma', 'fa-clock-o', '#0ea5e9'],
      'yeni_hizmet' => ['Yeni Hizmet Tanıtımı', 'fa-star', '#f59e0b'],
      'geri_kazanim'=> ['Yeniden Kazanım', 'fa-heart', '#ef4444'],
      'ozel_gun'    => ['Özel Gün', 'fa-gift', '#ec4899'],
      'sadakat'     => ['Sadakat / Puan', 'fa-diamond', '#10b981'],
      'etkinlik'    => ['Etkinlik / Duyuru', 'fa-bullhorn', '#6366f1'],
   ];
   $durumEtiket = [
      'taslak' => ['Taslak', '#64748b'],
      'aktif'  => ['Aktif', '#16a34a'],
      'pasif'  => ['Pasif', '#94a3b8'],
      'bitti'  => ['Bitti', '#dc2626'],
   ];
   $subeQs = isset($_GET['sube']) ? ('?sube='.$isletme->id) : '';
@endphp

<div class="page-header">
   <div class="row align-items-center">
      <div class="col-md-7 col-sm-7">
         <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
         <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="/isletmeyonetim{{$subeQs}}">Ana Sayfa</a></li>
               <li class="breadcrumb-item active" aria-current="page">{{$sayfa_baslik}}</li>
            </ol>
         </nav>
      </div>
      <div class="col-md-5 col-sm-5 text-right">
         <button type="button" class="btn btn-primary" id="brYeniBtn"
                 style="background:#7c3aed;border-color:#7c3aed;">
            <i class="fa fa-plus"></i> Yeni Reklam
         </button>
      </div>
   </div>
</div>

<div class="br-hero">
   <div class="br-hero-icon"><i class="fa fa-megaphone" style="font-style:normal">📣</i></div>
   <div>
      <h2>Müşterilerinize resimli bildirim reklamı gönderin</h2>
      <p>Bir görsel + kısa mesaj hazırlayın. Müşteri uygulamada görsele <b>dokununca indirim kuponu anında hesabına tanımlanır</b>. Kanallar: <b>Push</b> (anlık bildirim) ve <b>Uygulama içi</b> kart.</p>
   </div>
</div>

@if(count($reklamlar) == 0)
   <div class="br-empty">
      <div style="font-size:52px">📭</div>
      <p>Henüz reklam oluşturmadınız.</p>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('brYeniBtn').click()"
              style="background:#7c3aed;border-color:#7c3aed;">İlk reklamı oluştur</button>
   </div>
@else
<div class="row" id="brListe">
   @foreach($reklamlar as $r)
   @php
      $t = $turEtiket[$r->tur] ?? ['Reklam','fa-bullhorn','#7c3aed'];
      $d = $durumEtiket[$r->durum] ?? ['—','#64748b'];
   @endphp
   <div class="col-md-4 col-sm-6 br-card-col" data-reklam='@json($r)'>
      <div class="br-card">
         <div class="br-card-img" style="background:{{$t[2]}}12;">
            @if($r->gorsel)
               <img src="{{$r->gorsel}}" alt="">
            @else
               <i class="fa {{$t[1]}}" style="color:{{$t[2]}};font-size:34px"></i>
            @endif
            <span class="br-durum" style="background:{{$d[1]}}">{{$d[0]}}</span>
         </div>
         <div class="br-card-body">
            <span class="br-tur" style="color:{{$t[2]}};background:{{$t[2]}}15;">
               <i class="fa {{$t[1]}}"></i> {{$t[0]}}
            </span>
            <h4>{{$r->baslik}}</h4>
            <p>{{ \Illuminate\Support\Str::limit($r->mesaj, 70) }}</p>
            <div class="br-meta">
               @if($r->kanal_push)<span><i class="fa fa-bell"></i> Push</span>@endif
               @if($r->kanal_inapp)<span><i class="fa fa-mobile"></i> Uygulama içi</span>@endif
               @if($r->aksiyon_tipi=='kupon')
                  <span><i class="fa fa-ticket"></i>
                     {{ $r->kupon_indirim_tipi=='tutar' ? (rtrim(rtrim(number_format($r->kupon_deger,2,',','.'),'0'),',').' ₺') : (intval($r->kupon_deger).'%') }} kupon
                  </span>
               @endif
            </div>
            @if($r->aksiyon_tipi=='kupon' && $r->kupon_toplam_adet)
               <div class="br-adet">{{$r->kupon_dagitilan}} / {{$r->kupon_toplam_adet}} kupon kapıldı</div>
            @endif
         </div>
         <div class="br-card-actions">
            <button class="br-a br-edit" title="Düzenle"><i class="fa fa-pencil"></i></button>
            @if($r->kanal_push)
            <button class="br-a br-send" title="Push Gönder"><i class="fa fa-paper-plane"></i></button>
            @endif
            @if($r->durum=='aktif')
               <button class="br-a br-toggle" data-yeni="pasif" title="Duraklat"><i class="fa fa-pause"></i></button>
            @else
               <button class="br-a br-toggle" data-yeni="aktif" title="Yayınla"><i class="fa fa-play"></i></button>
            @endif
            <button class="br-a br-del" title="Sil"><i class="fa fa-trash"></i></button>
         </div>
      </div>
   </div>
   @endforeach
</div>
@endif

<!-- ============ OLUŞTUR / DÜZENLE MODALI ============ -->
<div class="modal fade" id="brModal" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-header" style="background:#7c3aed;color:#fff">
            <h5 class="modal-title" id="brModalBaslik">Yeni Bildirim Reklamı</h5>
            <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9">&times;</button>
         </div>
         <div class="modal-body">
            <form id="brForm">
               <input type="hidden" name="id" id="br_id">
               <input type="hidden" name="gorsel" id="br_gorsel">

               <label class="br-lbl">Reklam Türü</label>
               <select class="form-control" name="tur" id="br_tur">
                  @foreach($turEtiket as $k=>$v)
                     <option value="{{$k}}">{{$v[0]}}</option>
                  @endforeach
               </select>

               <div class="row" style="margin-top:14px">
                  <div class="col-md-7">
                     <label class="br-lbl">Başlık <span style="color:#dc2626">*</span></label>
                     <input type="text" class="form-control" name="baslik" id="br_baslik" maxlength="120" placeholder="Örn: Bu haftaya özel %20 indirim!">

                     <label class="br-lbl" style="margin-top:12px">Mesaj</label>
                     <textarea class="form-control" name="mesaj" id="br_mesaj" rows="3" maxlength="300" placeholder="Kısa açıklama (bildirim gövdesi)"></textarea>
                  </div>
                  <div class="col-md-5">
                     <label class="br-lbl">Görsel (uygulama içi kart)</label>
                     <div class="br-upload" id="br_upload_box">
                        <img id="br_onizleme" src="" style="display:none">
                        <div id="br_upload_ph"><i class="fa fa-image" style="font-size:26px;color:#94a3b8"></i><br><small>Görsel yükle</small></div>
                     </div>
                     <input type="file" id="br_dosya" accept="image/*" style="display:none">
                     <button type="button" class="btn btn-sm btn-light" id="br_gorsel_sec" style="width:100%;margin-top:6px">Görsel Seç</button>
                  </div>
               </div>

               <label class="br-lbl" style="margin-top:14px">Kanallar</label>
               <div class="br-kanallar">
                  <label class="br-check"><input type="checkbox" name="kanal_push" id="br_push" checked> <i class="fa fa-bell"></i> Push (anlık bildirim)</label>
                  <label class="br-check"><input type="checkbox" name="kanal_inapp" id="br_inapp" checked> <i class="fa fa-mobile"></i> Uygulama içi kart</label>
               </div>

               <label class="br-lbl" style="margin-top:14px">Görsele dokununca ne olsun?</label>
               <select class="form-control" name="aksiyon_tipi" id="br_aksiyon">
                  <option value="kupon">🎟️ İndirim kuponu tanımlansın (tek dokunuş)</option>
                  <option value="randevu">📅 Randevu ekranı açılsın</option>
                  <option value="yok">ℹ️ Sadece görsel (aksiyon yok)</option>
               </select>

               <!-- KUPON AYARLARI -->
               <div id="br_kupon_kutu" class="br-kupon-kutu">
                  <div class="br-kupon-baslik"><i class="fa fa-ticket"></i> Kupon Ayarları</div>
                  <div class="row">
                     <div class="col-md-4">
                        <label class="br-lbl">İndirim Tipi</label>
                        <select class="form-control" name="kupon_indirim_tipi" id="br_kupon_tip">
                           <option value="yuzde">Yüzde (%)</option>
                           <option value="tutar">Tutar (₺)</option>
                        </select>
                     </div>
                     <div class="col-md-4">
                        <label class="br-lbl">Değer</label>
                        <input type="number" class="form-control" name="kupon_deger" id="br_kupon_deger" min="0" step="1" placeholder="20">
                     </div>
                     <div class="col-md-4">
                        <label class="br-lbl">Geçerli Hizmet</label>
                        <select class="form-control" name="kupon_hizmet_id" id="br_kupon_hizmet">
                           <option value="">Tüm hizmetler</option>
                           @foreach($hizmetler as $h)
                              <option value="{{$h->hizmet_id}}">{{ optional($h->hizmetler)->hizmet_adi }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row" style="margin-top:10px">
                     <div class="col-md-4">
                        <label class="br-lbl">Geçerlilik (gün)</label>
                        <input type="number" class="form-control" name="kupon_gecerlilik_gun" id="br_kupon_gun" min="0" placeholder="Boş = süresiz">
                     </div>
                     <div class="col-md-4">
                        <label class="br-lbl">Toplam Adet</label>
                        <input type="number" class="form-control" name="kupon_toplam_adet" id="br_kupon_adet" min="0" placeholder="Boş = sınırsız">
                     </div>
                     <div class="col-md-4">
                        <label class="br-lbl">Kişi Başı Limit</label>
                        <input type="number" class="form-control" name="kupon_kisi_limit" id="br_kupon_limit" min="1" value="1">
                     </div>
                  </div>
                  <small class="br-ipucu">Aynı müşteri kuponu yalnızca <b>1 kez</b> kapabilir. Toplam adet dolunca kampanya otomatik biter.</small>
               </div>

               <label class="br-lbl" style="margin-top:14px">Hedef Kitle (Push kime gitsin?)</label>
               <select class="form-control" name="hedef_kitle" id="br_hedef">
                  <option value="tumu">Tüm müşteriler</option>
                  <option value="segment">Belirli segment</option>
               </select>

               <div id="br_segment_kutu" class="br-segment-kutu" style="display:none">
                  <select class="form-control" name="segment_tip" id="br_seg_tip">
                     <option value="gelmeyen">Uzun süredir gelmeyenler</option>
                     <option value="dogum_gunu">Doğum günü bu ay olanlar</option>
                     <option value="hizmet">Belirli hizmeti alanlar</option>
                     <option value="cinsiyet">Cinsiyete göre</option>
                  </select>

                  <div id="seg_gelmeyen" class="br-seg-alan" style="margin-top:10px">
                     <label class="br-lbl">Kaç gündür gelmeyenler?</label>
                     <input type="number" class="form-control" name="segment_gun" id="br_seg_gun" min="1" value="60" placeholder="60">
                  </div>
                  <div id="seg_hizmet" class="br-seg-alan" style="margin-top:10px;display:none">
                     <label class="br-lbl">Hangi hizmeti alanlar?</label>
                     <select class="form-control" name="segment_hizmet_id" id="br_seg_hizmet">
                        @foreach($hizmetler as $h)
                           <option value="{{$h->hizmet_id}}">{{ optional($h->hizmetler)->hizmet_adi }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div id="seg_cinsiyet" class="br-seg-alan" style="margin-top:10px;display:none">
                     <label class="br-lbl">Cinsiyet</label>
                     <select class="form-control" name="segment_cinsiyet" id="br_seg_cinsiyet">
                        <option value="0">Kadın</option>
                        <option value="1">Erkek</option>
                     </select>
                  </div>
               </div>

               <label class="br-lbl" style="margin-top:14px">Durum</label>
               <select class="form-control" name="durum" id="br_durum">
                  <option value="taslak">Taslak (yayında değil)</option>
                  <option value="aktif">Aktif (yayında)</option>
                  <option value="pasif">Pasif</option>
               </select>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-light" data-dismiss="modal">Vazgeç</button>
            <button type="button" class="btn btn-primary" id="brKaydet" style="background:#7c3aed;border-color:#7c3aed">
               <i class="fa fa-save"></i> Kaydet
            </button>
         </div>
      </div>
   </div>
</div>

<style>
.br-hero{display:flex;gap:16px;align-items:center;background:linear-gradient(135deg,#7c3aed,#9d5dc8);color:#fff;border-radius:14px;padding:20px 22px;margin-bottom:20px}
.br-hero-icon{font-size:38px;flex:0 0 auto}
.br-hero h2{margin:0 0 4px;font-size:19px;color:#fff}
.br-hero p{margin:0;font-size:13px;opacity:.95}
.br-empty{text-align:center;padding:50px 20px;color:#64748b;background:#fff;border-radius:14px}
.br-empty p{margin:12px 0 16px;font-size:15px}
.br-card-col{margin-bottom:22px}
.br-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(15,23,42,.06);overflow:hidden;display:flex;flex-direction:column;height:100%}
.br-card-img{position:relative;height:130px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.br-card-img img{width:100%;height:100%;object-fit:cover}
.br-durum{position:absolute;top:10px;right:10px;color:#fff;font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600}
.br-card-body{padding:14px 16px;flex:1}
.br-tur{display:inline-block;font-size:11px;font-weight:600;padding:3px 9px;border-radius:6px;margin-bottom:8px}
.br-card-body h4{font-size:15px;margin:0 0 6px;font-weight:700;color:#1e293b}
.br-card-body p{font-size:13px;color:#64748b;margin:0 0 10px;min-height:18px}
.br-meta{display:flex;flex-wrap:wrap;gap:10px;font-size:12px;color:#475569}
.br-meta i{color:#7c3aed;margin-right:3px}
.br-adet{margin-top:8px;font-size:12px;color:#0ea5e9;font-weight:600}
.br-card-actions{display:flex;border-top:1px solid #f1f5f9}
.br-a{flex:1;border:0;background:#fff;padding:10px 0;color:#64748b;cursor:pointer;transition:.15s}
.br-a:hover{background:#f8fafc;color:#7c3aed}
.br-del:hover{color:#dc2626}
.br-lbl{font-size:13px;font-weight:600;color:#334155;margin-bottom:5px;display:block}
.br-kanallar{display:flex;gap:18px;flex-wrap:wrap}
.br-check{font-weight:500;color:#475569;cursor:pointer}
.br-check i{color:#7c3aed}
.br-segment-kutu{margin-top:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px}
.br-kupon-kutu{margin-top:14px;background:#faf5ff;border:1px solid #ede9fe;border-radius:12px;padding:14px 16px}
.br-kupon-baslik{font-weight:700;color:#7c3aed;margin-bottom:10px}
.br-ipucu{display:block;margin-top:10px;color:#7c3aed;font-size:12px}
.br-upload{height:110px;border:2px dashed #cbd5e1;border-radius:12px;display:flex;align-items:center;justify-content:center;text-align:center;color:#94a3b8;overflow:hidden;background:#f8fafc}
.br-upload img{width:100%;height:100%;object-fit:cover}
</style>

<script>
(function(){
   var CSRF = $('meta[name="csrf-token"]').attr('content');
   var SUBE = {{ $isletme->id }};
   function url(p){ return '/isletmeyonetim/'+p+'?sube='+SUBE; }

   // Kupon kutusu aksiyon tipine gore goster/gizle
   function kuponGoster(){
      $('#br_kupon_kutu').toggle($('#br_aksiyon').val()==='kupon');
   }
   $('#br_aksiyon').on('change', kuponGoster);

   // Segment kutusu + alt alanlar
   function segmentGoster(){
      $('#br_segment_kutu').toggle($('#br_hedef').val()==='segment');
   }
   function segAlanGoster(){
      var t = $('#br_seg_tip').val();
      $('#seg_gelmeyen').toggle(t==='gelmeyen');
      $('#seg_hizmet').toggle(t==='hizmet');
      $('#seg_cinsiyet').toggle(t==='cinsiyet');
   }
   $('#br_hedef').on('change', segmentGoster);
   $('#br_seg_tip').on('change', segAlanGoster);

   // ---- Yeni ----
   $('#brYeniBtn').on('click', function(){
      $('#brForm')[0].reset();
      $('#br_id').val(''); $('#br_gorsel').val('');
      $('#br_onizleme').hide().attr('src',''); $('#br_upload_ph').show();
      $('#br_push,#br_inapp').prop('checked',true);
      $('#br_hedef').val('tumu');
      $('#brModalBaslik').text('Yeni Bildirim Reklamı');
      kuponGoster(); segmentGoster(); segAlanGoster();
      $('#brModal').modal('show');
   });

   // ---- Duzenle ----
   $(document).on('click','.br-edit', function(){
      var r = $(this).closest('.br-card-col').data('reklam');
      $('#brForm')[0].reset();
      $('#br_id').val(r.id);
      $('#br_tur').val(r.tur);
      $('#br_baslik').val(r.baslik);
      $('#br_mesaj').val(r.mesaj||'');
      $('#br_gorsel').val(r.gorsel||'');
      if(r.gorsel){ $('#br_onizleme').attr('src',r.gorsel).show(); $('#br_upload_ph').hide(); }
      else { $('#br_onizleme').hide().attr('src',''); $('#br_upload_ph').show(); }
      $('#br_push').prop('checked', r.kanal_push==1||r.kanal_push===true);
      $('#br_inapp').prop('checked', r.kanal_inapp==1||r.kanal_inapp===true);
      $('#br_aksiyon').val(r.aksiyon_tipi||'kupon');
      $('#br_kupon_tip').val(r.kupon_indirim_tipi||'yuzde');
      $('#br_kupon_deger').val(r.kupon_deger>0?parseFloat(r.kupon_deger):'');
      $('#br_kupon_hizmet').val(r.kupon_hizmet_id||'');
      $('#br_kupon_gun').val(r.kupon_gecerlilik_gun||'');
      $('#br_kupon_adet').val(r.kupon_toplam_adet||'');
      $('#br_kupon_limit').val(r.kupon_kisi_limit||1);
      // Hedef kitle / segment
      $('#br_hedef').val(r.hedef_kitle||'tumu');
      var kosul = {};
      try { kosul = r.hedef_kosul ? (typeof r.hedef_kosul==='string' ? JSON.parse(r.hedef_kosul) : r.hedef_kosul) : {}; } catch(e){ kosul = {}; }
      if(kosul && kosul.tip){
         $('#br_seg_tip').val(kosul.tip);
         if(kosul.gun) $('#br_seg_gun').val(kosul.gun);
         if(kosul.hizmet_id) $('#br_seg_hizmet').val(String(kosul.hizmet_id));
         if(kosul.cinsiyet!==undefined && kosul.cinsiyet!==null) $('#br_seg_cinsiyet').val(String(kosul.cinsiyet));
      }
      $('#br_durum').val(r.durum||'taslak');
      $('#brModalBaslik').text('Reklamı Düzenle');
      kuponGoster(); segmentGoster(); segAlanGoster();
      $('#brModal').modal('show');
   });

   // ---- Gorsel sec + base64 yukle ----
   $('#br_gorsel_sec, #br_upload_box').on('click', function(){ $('#br_dosya').click(); });
   $('#br_dosya').on('change', function(e){
      var f = e.target.files[0]; if(!f) return;
      var reader = new FileReader();
      reader.onload = function(ev){
         $('#br_onizleme').attr('src', ev.target.result).show(); $('#br_upload_ph').hide();
         $.ajax({ url:url('bildirim-reklam-gorsel'), method:'POST',
            data:{ _token:CSRF, sube:SUBE, gorsel:ev.target.result },
            success:function(res){ if(res.durum==='basarili'){ $('#br_gorsel').val(res.yol); }
               else{ alert(res.mesaj||'Görsel yüklenemedi'); } },
            error:function(){ alert('Görsel yüklenemedi'); }
         });
      };
      reader.readAsDataURL(f);
   });

   // ---- Kaydet ----
   $('#brKaydet').on('click', function(){
      if(!$('#br_baslik').val().trim()){ alert('Başlık zorunlu'); return; }
      var btn=$(this); btn.prop('disabled',true);
      var data = $('#brForm').serializeArray();
      data.push({name:'_token',value:CSRF});
      data.push({name:'sube',value:SUBE});
      data.push({name:'kanal_push',value:$('#br_push').is(':checked')?1:0});
      data.push({name:'kanal_inapp',value:$('#br_inapp').is(':checked')?1:0});
      $.ajax({ url:url('bildirim-reklam-kaydet'), method:'POST', data:data,
         success:function(res){ if(res.durum==='basarili'){ location.reload(); }
            else{ alert(res.mesaj||'Kaydedilemedi'); btn.prop('disabled',false); } },
         error:function(x){ alert((x.responseJSON&&x.responseJSON.mesaj)||'Kaydedilemedi'); btn.prop('disabled',false); }
      });
   });

   // ---- Durum degistir ----
   $(document).on('click','.br-toggle', function(){
      var r = $(this).closest('.br-card-col').data('reklam');
      var yeni = $(this).data('yeni');
      $.ajax({ url:url('bildirim-reklam-durum'), method:'POST',
         data:{ _token:CSRF, sube:SUBE, id:r.id, durum:yeni },
         success:function(res){ if(res.durum==='basarili'){ location.reload(); } else{ alert(res.mesaj); } }
      });
   });

   // ---- Push gonder ----
   $(document).on('click','.br-send', function(){
      var r = $(this).closest('.br-card-col').data('reklam');
      if(!confirm('"'+r.baslik+'" reklamı salonun tüm müşterilerine push bildirim olarak gönderilsin mi?')) return;
      var b=$(this); b.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
      $.ajax({ url:url('bildirim-reklam-gonder'), method:'POST',
         data:{ _token:CSRF, sube:SUBE, id:r.id },
         success:function(res){ alert(res.mesaj||'Gönderildi'); location.reload(); },
         error:function(x){ alert((x.responseJSON&&x.responseJSON.mesaj)||'Gönderilemedi'); b.prop('disabled',false).html('<i class="fa fa-paper-plane"></i>'); }
      });
   });

   // ---- Sil ----
   $(document).on('click','.br-del', function(){
      var r = $(this).closest('.br-card-col').data('reklam');
      if(!confirm('"'+r.baslik+'" reklamı silinsin mi?')) return;
      $.ajax({ url:url('bildirim-reklam-sil'), method:'POST',
         data:{ _token:CSRF, sube:SUBE, id:r.id },
         success:function(res){ if(res.durum==='basarili'){ location.reload(); } else{ alert(res.mesaj); } }
      });
   });
})();
</script>
@endsection
