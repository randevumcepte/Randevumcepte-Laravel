@isset($isletme)
@php
  $pgPersoneller = \App\Personeller::where('salon_id',$isletme->id)
      ->where('aktif',1)
      ->where(function($q){ $q->where('arsivli',false)->orWhereNull('arsivli'); })
      ->orderBy('takvim_sirasi','asc')->get();
@endphp
<div id="personel_gideri_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <style>
    #personel_gideri_modal .modal-dialog{ max-width:560px; margin:1.75rem auto; }
    #personel_gideri_modal .pg-content{
      border:0; border-radius:18px; overflow:hidden; background:#fff;
      box-shadow:0 24px 60px -12px rgba(30,41,59,.35);
    }
    #personel_gideri_modal .pg-head{
      padding:20px 24px; background:linear-gradient(135deg,#4f46e5,#6366f1);
      color:#fff; display:flex; align-items:center; gap:14px;
    }
    #personel_gideri_modal .pg-head__icon{
      width:44px; height:44px; border-radius:12px; flex-shrink:0;
      background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
      font-size:20px;
    }
    #personel_gideri_modal .pg-head h4{ margin:0; font-size:19px; font-weight:800; letter-spacing:.2px; }
    #personel_gideri_modal .pg-head p{ margin:2px 0 0; font-size:12.5px; opacity:.85; }
    #personel_gideri_modal .pg-close{
      margin-left:auto; background:rgba(255,255,255,.15); border:0; color:#fff; width:34px; height:34px;
      border-radius:9px; font-size:18px; cursor:pointer; line-height:1;
    }
    #personel_gideri_modal .pg-close:hover{ background:rgba(255,255,255,.28); }
    #personel_gideri_modal .pg-body{ padding:22px 24px; }
    #personel_gideri_modal .pg-note{
      display:flex; gap:10px; align-items:flex-start; padding:12px 14px; margin-bottom:18px;
      background:#eef2ff; border:1px solid #c7d2fe; border-radius:12px; color:#3730a3; font-size:13px; line-height:1.45;
    }
    #personel_gideri_modal .pg-note i{ margin-top:2px; }
    #personel_gideri_modal .pg-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px 16px; }
    #personel_gideri_modal .pg-col-full{ grid-column:1 / -1; }
    #personel_gideri_modal label.pg-lbl{
      display:block; font-size:12.5px; font-weight:700; color:#475569; margin-bottom:6px;
    }
    #personel_gideri_modal label.pg-lbl .req{ color:#dc2626; }
    #personel_gideri_modal .pg-body .form-control{
      border:1.5px solid #e2e8f0; border-radius:10px; height:44px; font-size:14px; color:#1e293b;
      background:#f8fafc; transition:border-color .15s, background .15s;
    }
    #personel_gideri_modal .pg-body textarea.form-control{ height:auto; min-height:60px; padding-top:10px; }
    #personel_gideri_modal .pg-body .form-control:focus{
      border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.12); outline:0;
    }
    #personel_gideri_modal .pg-foot{
      padding:16px 24px 22px; display:flex; gap:12px;
    }
    #personel_gideri_modal .pg-btn{
      flex:1; height:48px; border:0; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer;
      display:flex; align-items:center; justify-content:center; gap:8px;
    }
    #personel_gideri_modal .pg-btn--save{ background:linear-gradient(135deg,#4f46e5,#6366f1); color:#fff; }
    #personel_gideri_modal .pg-btn--save:hover{ filter:brightness(1.05); }
    #personel_gideri_modal .pg-btn--cancel{ background:#f1f5f9; color:#475569; }
    #personel_gideri_modal .pg-btn--cancel:hover{ background:#e2e8f0; }
    @media(max-width:520px){ #personel_gideri_modal .pg-grid{ grid-template-columns:1fr; } }
  </style>
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content pg-content">
      <form id="personel_gideri_formu" method="POST">
        <div class="pg-head">
          <div class="pg-head__icon"><i class="fa fa-shopping-basket"></i></div>
          <div>
            <h4>Personel Gideri Ekle</h4>
            <p>Personelin kasadan aldığı harcama (sigara, yiyecek vb.)</p>
          </div>
          <button type="button" class="pg-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
        </div>
        <div class="pg-body">
          {!!csrf_field()!!}
          <input type="hidden" name="sube" value="{{$isletme->id}}">
          <input type="hidden" name="masraf_id" id="pg_masraf_id" value="">
          <input type="hidden" name="personel_gideri" id="pg_flag" value="1">

          <div class="pg-note">
            <i class="fa fa-info-circle"></i>
            <span>Bu tutar hem <b>kasadan</b> düşülür (kasa açık vermez) hem de seçilen personelin <b>net hak edişinden</b> otomatik düşülür.</span>
          </div>

          <div class="pg-grid">
            <div>
              <label class="pg-lbl">Personel <span class="req">*</span></label>
              <select name="harcayan" id="pg_harcayan" class="form-control">
                <option value="">Personel seçin</option>
                @foreach($pgPersoneller as $per)
                  <option value="{{$per->id}}">{{$per->personel_adi}}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="pg-lbl">Tutar (₺) <span class="req">*</span></label>
              <input type="tel" name="masraf_tutari" id="pg_tutar" class="form-control try-currency" placeholder="0,00">
            </div>
            <div>
              <label class="pg-lbl">Tarih</label>
              <input type="text" name="tarih" id="pg_tarih" class="form-control" value="{{date('Y-m-d')}}" autocomplete="off">
            </div>
            <div>
              <label class="pg-lbl">Ödeme Yöntemi</label>
              <select name="masraf_odeme_yontemi" id="pg_odeme" class="form-control">
                @foreach(\App\OdemeYontemleri::all() as $oy)
                  <option value="{{$oy->id}}" {{ stripos($oy->odeme_yontemi,'Nakit')!==false ? 'selected' : '' }}>{{$oy->odeme_yontemi}}</option>
                @endforeach
              </select>
            </div>
            <div class="pg-col-full">
              <label class="pg-lbl">Gider Türü / Kategori</label>
              <select name="masraf_kategorisi" id="pg_kategori" class="form-control">
                <option value="">Otomatik: Personel Gideri</option>
                @foreach(\App\MasrafKategorisi::all() as $cat)
                  <option value="{{$cat->id}}">{{$cat->kategori}}</option>
                @endforeach
              </select>
            </div>
            <div class="pg-col-full">
              <label class="pg-lbl">Açıklama</label>
              <input type="text" name="masraf_aciklama" id="pg_aciklama" class="form-control" placeholder="Ör: Öğle yemeği, sigara...">
            </div>
            <div class="pg-col-full">
              <label class="pg-lbl">Notlar</label>
              <textarea name="masraf_notlari" id="pg_notlari" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="pg-foot">
          <button type="submit" class="pg-btn pg-btn--save"><i class="fa fa-save"></i> Kaydet</button>
          <button type="button" class="pg-btn pg-btn--cancel" data-dismiss="modal"><i class="fa fa-times"></i> Kapat</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endisset
