@isset($isletme)
@php
  $personelListesi = \App\Personeller::where('salon_id',$isletme->id)
      ->orderBy('takvim_sirasi','asc')->get();
@endphp
<div id="yeni_masraf_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <style>
    #yeni_masraf_modal .modal-dialog{ max-width:580px; margin:1.75rem auto; }
    #yeni_masraf_modal .mx-content{
      border:0; border-radius:18px; overflow:hidden; background:#fff;
      box-shadow:0 24px 60px -12px rgba(30,41,59,.35);
    }
    #yeni_masraf_modal .mx-head{
      padding:20px 24px; background:linear-gradient(135deg,#5C008E,#7B2FB8 55%,#9D5DC8);
      color:#fff; display:flex; align-items:center; gap:14px;
    }
    #yeni_masraf_modal .mx-head__icon{
      width:44px; height:44px; border-radius:12px; flex-shrink:0;
      background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; font-size:20px;
    }
    #yeni_masraf_modal .mx-head h4{ margin:0; font-size:19px; font-weight:800; letter-spacing:.2px; color:#fff !important; }
    #yeni_masraf_modal .mx-head p{ margin:2px 0 0; font-size:12.5px; opacity:.85; color:#fff !important; }
    #yeni_masraf_modal .mx-close{
      margin-left:auto; background:rgba(255,255,255,.15); border:0; color:#fff; width:34px; height:34px;
      border-radius:9px; font-size:18px; cursor:pointer; line-height:1;
    }
    #yeni_masraf_modal .mx-close:hover{ background:rgba(255,255,255,.28); }
    /* Iki mod (Masraf / Personel Gideri) ayni yukseklikte kalsin: kategori satiri
       ile bilgi kutusu yer degistirse de govde sabit min-height ile boyut degismez.
       box-sizing:content-box -> min-height dogrudan icerik alanina uygulanir. */
    #yeni_masraf_modal .mx-body{ padding:20px 24px; min-height:432px; box-sizing:content-box; }
    /* Segment gecis */
    #yeni_masraf_modal .mx-seg{
      display:grid; grid-template-columns:1fr 1fr; gap:8px; padding:5px; margin-bottom:18px;
      background:#f1f5f9; border-radius:12px;
    }
    #yeni_masraf_modal .mx-seg__btn{
      border:0; background:transparent; padding:10px 12px; border-radius:9px; cursor:pointer;
      font-size:14px; font-weight:700; color:#64748b; display:flex; align-items:center; justify-content:center; gap:7px;
      transition:all .15s;
    }
    #yeni_masraf_modal .mx-seg__btn:hover{ color:#5C008E; }
    #yeni_masraf_modal .mx-seg__btn.is-active{
      background:#fff; color:#5C008E; box-shadow:0 2px 8px rgba(92,0,142,.16);
    }
    #yeni_masraf_modal .mx-note{
      display:flex; gap:10px; align-items:flex-start; padding:12px 14px; margin-bottom:18px;
      background:#f7f0fb; border:1px solid #e2cdf0; border-radius:12px; color:#5C008E; font-size:13px; line-height:1.45;
    }
    #yeni_masraf_modal .mx-note i{ margin-top:2px; }
    #yeni_masraf_modal .mx-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px 16px; }
    #yeni_masraf_modal .mx-col-full{ grid-column:1 / -1; }
    #yeni_masraf_modal label.mx-lbl{ display:block; font-size:12.5px; font-weight:700; color:#475569; margin-bottom:6px; }
    #yeni_masraf_modal label.mx-lbl .req{ color:#dc2626; }
    #yeni_masraf_modal .mx-body .form-control{
      border:1.5px solid #e2e8f0; border-radius:10px; height:44px; font-size:14px; color:#1e293b;
      background:#f8fafc; transition:border-color .15s, background .15s; width:100%;
    }
    #yeni_masraf_modal .mx-body textarea.form-control{ height:auto; min-height:60px; padding-top:10px; }
    #yeni_masraf_modal .mx-body .form-control:focus{
      border-color:#7B2FB8; background:#fff; box-shadow:0 0 0 3px rgba(123,47,184,.14); outline:0;
    }
    #yeni_masraf_modal .mx-foot{ padding:16px 24px 22px; display:flex; gap:12px; }
    #yeni_masraf_modal .mx-btn{
      flex:1; height:48px; border:0; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer;
      display:flex; align-items:center; justify-content:center; gap:8px;
    }
    #yeni_masraf_modal .mx-btn--save{ background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; }
    #yeni_masraf_modal .mx-btn--save:hover{ filter:brightness(1.06); }
    #yeni_masraf_modal .mx-btn--cancel{ background:#f1f5f9; color:#475569; }
    #yeni_masraf_modal .mx-btn--cancel:hover{ background:#e2e8f0; }
    @media(max-width:540px){ #yeni_masraf_modal .mx-grid{ grid-template-columns:1fr; } }
  </style>
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content mx-content">
      <form id="masraf_formu" method="POST">
        <div class="mx-head">
          <div class="mx-head__icon"><i class="fa fa-shopping-basket" id="masraf_modal_ikon"></i></div>
          <div style="flex:1; min-width:0">
            <h4 id="masraf_modal_baslik">Masraf Ekle</h4>
            <p id="masraf_modal_altbaslik">Salon gideri (elektrik, malzeme, kira vb.)</p>
          </div>
          <button type="button" class="mx-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
        </div>
        <div class="mx-body">
          {!!csrf_field()!!}
          <input type="hidden" name="sube" value="{{$isletme->id}}">
          <input type="hidden" name="masraf_id" id="masraf_id" value="">
          <input type="hidden" name="personel_gideri" id="masraf_personel_gideri" value="0">
          @if(isset($pageindex) && $pageindex==15)<input type="hidden" name="masraf_sayfasi" value="1">@endif
          @if(isset($pageindex) && $pageindex==103)<input type="hidden" id="kasa_sayfasi" name="kasa_sayfasi" value="1">@endif

          <div class="mx-seg">
            <button type="button" class="mx-seg__btn is-active" data-mod="masraf" onclick="masrafModalMod('masraf')"><i class="fa fa-wallet"></i> Masraf</button>
            <button type="button" class="mx-seg__btn" data-mod="gideri" onclick="masrafModalMod('gideri')"><i class="fa fa-shopping-basket"></i> Personel Gideri</button>
          </div>

          <div id="masraf_pg_info" class="mx-note" style="display:none">
            <i class="fa fa-info-circle"></i>
            <span>Bu tutar hem <b>kasadan</b> düşülür (kasa açık vermez) hem de seçilen personelin <b>net hak edişinden</b> otomatik düşülür.</span>
          </div>

          <div class="mx-grid">
            <div>
              <label class="mx-lbl" id="masraf_harcayan_lbl">Harcayan Personel <span class="req">*</span></label>
              <select name="harcayan" id="harcayan" class="form-control">
                <option value="">Personel seçin</option>
                @foreach($personelListesi as $per)
                  <option value="{{$per->id}}">{{$per->personel_adi}}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mx-lbl">Tutar (₺) <span class="req">*</span></label>
              <input type="tel" name="masraf_tutari" id="masraf_tutari" class="form-control try-currency" placeholder="0,00">
            </div>
            <div>
              <label class="mx-lbl">Tarih</label>
              <input type="text" name="tarih" id="masraf_tarihi" class="form-control" value="{{date('Y-m-d')}}" autocomplete="off">
            </div>
            <div>
              <label class="mx-lbl">Ödeme Yöntemi</label>
              <select name="masraf_odeme_yontemi" id="masraf_odeme_yontemi" class="form-control">
                <option value="">Seçiniz</option>
                @foreach(\App\OdemeYontemleri::all() as $oy)
                  <option value="{{$oy->id}}">{{$oy->odeme_yontemi}}</option>
                @endforeach
              </select>
            </div>
            <div class="mx-col-full" id="masraf_kategori_wrap">
              <label class="mx-lbl">Masraf Kategorisi</label>
              <select name="masraf_kategorisi" id="masraf_kategorisi" class="form-control">
                <option value="">Seçiniz</option>
                @foreach(\App\MasrafKategorisi::all() as $cat)
                  <option value="{{$cat->id}}">{{$cat->kategori}}</option>
                @endforeach
              </select>
            </div>
            <div class="mx-col-full">
              <label class="mx-lbl">Açıklama</label>
              <input type="text" name="masraf_aciklama" id="masraf_aciklama" class="form-control" placeholder="Ör: Öğle yemeği, sigara...">
            </div>
            <div class="mx-col-full">
              <label class="mx-lbl">Notlar</label>
              <textarea name="masraf_notlari" id="masraf_notlari" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="mx-foot">
          <button type="submit" class="mx-btn mx-btn--save"><i class="fa fa-save"></i> Kaydet</button>
          <button type="button" class="mx-btn mx-btn--cancel" data-dismiss="modal"><i class="fa fa-times"></i> Kapat</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endisset
