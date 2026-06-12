{{-- ================================================================
     HARİCİ TAHSİLAT MODALI (Geçmişe Dönük / Dış Kaynaklı Satış)
     Rakip programlardan aktarılan satışları finansal olarak işler.
     SALT-OKUNUR: stok düşmez, sarf reçetesi uygulanmaz, paket seansı oluşmaz.
     ================================================================ --}}
<div class="modal fade" id="harici_tahsilat_modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document" style="max-width:820px">
      <div class="modal-content">
         <div class="modal-header" style="background:#4338ca;color:#fff;border-bottom:none">
            <h5 class="modal-title" style="font-weight:600;color:#fff">
               <i class="fa fa-cloud-download"></i> Harici Tahsilat — Geçmişe Dönük Satış
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Kapat" style="color:#fff;opacity:.9">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>

         <form id="harici_tahsilat_form" method="POST">
            {!! csrf_field() !!}
            <input type="hidden" name="sube" value="{{$isletme->id}}">

            <div class="modal-body" style="background:#f8fafc">

               {{-- Bilgilendirme --}}
               <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#3730a3;line-height:1.5">
                  <i class="fa fa-info-circle"></i>
                  Bu satış yalnızca <b>finansal tablolara</b> (satış takibi, satış &amp; kasa raporu) seçtiğiniz tarihte yansır.
                  <b>Stok düşmez</b>, <b>seans oluşmaz</b> — sadece geçmiş satış kaydı tutulur.
               </div>

               {{-- Müşteri + Tarih --}}
               <div class="row">
                  <div class="col-md-7">
                     <div class="form-group">
                        <label style="font-weight:600;font-size:13px">Müşteri / Danışan <span style="color:#dc2626">*</span></label>
                        <select name="musteri_id" id="harici_musteri_id" style="width:100%" class="form-control"></select>
                     </div>
                  </div>
                  <div class="col-md-5">
                     <div class="form-group">
                        <label style="font-weight:600;font-size:13px">Satış Tarihi <span style="color:#dc2626">*</span></label>
                        <input type="date" name="satis_tarihi" id="harici_satis_tarihi" class="form-control" max="{{date('Y-m-d')}}" value="{{date('Y-m-d')}}" required>
                     </div>
                  </div>
               </div>

               {{-- Kalem Ekleme --}}
               <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-top:6px">
                  <div style="font-weight:600;font-size:13px;color:#334155;margin-bottom:10px">Satış Kalemi Ekle</div>
                  <div class="row" style="align-items:flex-end">
                     <div class="col-md-3">
                        <label style="font-size:12px">Tür</label>
                        <select id="harici_kalem_tip" class="form-control">
                           <option value="hizmet">Hizmet</option>
                           <option value="urun">Ürün</option>
                           <option value="paket">Paket</option>
                        </select>
                     </div>
                     <div class="col-md-5">
                        <label style="font-size:12px">Seçim</label>
                        <select id="harici_hizmet_select" class="form-control harici-item-select">
                           <option value="">Seçiniz...</option>
                           @foreach($harici_hizmetler as $h)
                              <option value="{{$h->id}}" data-fiyat="{{$h->fiyat}}">{{$h->ad}}</option>
                           @endforeach
                        </select>
                        <select id="harici_urun_select" class="form-control harici-item-select" style="display:none">
                           <option value="">Seçiniz...</option>
                           @foreach($harici_urunler as $u)
                              <option value="{{$u->id}}" data-fiyat="{{$u->fiyat}}">{{$u->ad}}</option>
                           @endforeach
                        </select>
                        <select id="harici_paket_select" class="form-control harici-item-select" style="display:none">
                           <option value="">Seçiniz...</option>
                           @foreach($harici_paketler as $p)
                              <option value="{{$p->id}}" data-fiyat="{{$p->fiyat}}">{{$p->ad}}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-2" id="harici_adet_kutu" style="display:none">
                        <label style="font-size:12px">Adet</label>
                        <input type="number" id="harici_kalem_adet" class="form-control" value="1" min="1" step="1">
                     </div>
                     <div class="col-md-2">
                        <label style="font-size:12px">Tutar (₺)</label>
                        <input type="number" id="harici_kalem_fiyat" class="form-control" value="0" min="0" step="0.01">
                     </div>
                  </div>
                  <div class="text-right" style="margin-top:10px">
                     <button type="button" id="harici_kalem_ekle" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Listeye Ekle</button>
                  </div>
               </div>

               {{-- Kalemler Tablosu --}}
               <table class="table table-sm" style="margin-top:14px;background:#fff;font-size:13px">
                  <thead>
                     <tr style="background:#f1f5f9">
                        <th style="font-size:12px">Tür</th>
                        <th style="font-size:12px">Kalem</th>
                        <th style="font-size:12px;text-align:center">Adet</th>
                        <th style="font-size:12px;text-align:right">Tutar (₺)</th>
                        <th style="width:36px"></th>
                     </tr>
                  </thead>
                  <tbody id="harici_kalem_tablosu">
                     <tr id="harici_kalem_bos"><td colspan="5" class="text-center" style="color:#94a3b8;padding:14px">Henüz kalem eklenmedi</td></tr>
                  </tbody>
                  <tfoot>
                     <tr>
                        <td colspan="3" style="text-align:right;font-weight:600">Toplam</td>
                        <td style="text-align:right;font-weight:700;color:#4338ca" id="harici_toplam_goster">0,00 ₺</td>
                        <td></td>
                     </tr>
                  </tfoot>
               </table>

               {{-- Ödeme --}}
               <div class="row" style="margin-top:6px">
                  <div class="col-md-4">
                     <div class="form-group">
                        <label style="font-weight:600;font-size:13px">Tahsil Edilen Tutar (₺)</label>
                        <input type="number" name="tahsilat_tutari" id="harici_tahsilat_tutari" class="form-control" value="0" min="0" step="0.01"
                               style="background:#d1fae5;border-color:#a7f3d0;font-weight:600">
                        <small style="color:#64748b">Boş/0 bırakılırsa açık satış (tahsilatsız) olarak kaydedilir.</small>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label style="font-weight:600;font-size:13px">Ödeme Yöntemi</label>
                        <select name="odeme_yontemi" id="harici_odeme_yontemi" class="form-control">
                           @foreach(\App\OdemeYontemleri::all() as $oy)
                              <option value="{{$oy->id}}">{{$oy->odeme_yontemi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label style="font-weight:600;font-size:13px">Banka (opsiyonel)</label>
                        <select name="banka" id="harici_banka" class="form-control">
                           <option value="">Seçiniz...</option>
                           @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
                              <option value="{{$banka->id}}">{{$banka->banka}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label style="font-weight:600;font-size:13px">Not (opsiyonel)</label>
                        <input type="text" name="not" id="harici_not" class="form-control" placeholder="Örn: X programından aktarılan satış">
                     </div>
                  </div>
               </div>

            </div>

            <div class="modal-footer" style="background:#f8fafc;border-top:1px solid #e2e8f0">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
               <button type="submit" id="harici_kaydet" class="btn btn-success"><i class="fa fa-save"></i> Kaydet</button>
            </div>
         </form>
      </div>
   </div>
</div>
