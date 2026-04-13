<div
    id="paket-modal"
    class="modal modal-top fade calendar-modal"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="max-width:1100px; max-height: 90%;width:100%">
            <form id="paket_formu" method="POST">
                <div class="modal-body">
                    {!!csrf_field()!!}
                    <input type="hidden" name="sube" value="{{$isletme->id}}">
                    <h2 class="text-blue h2 mb-10">Yeni Paket</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Paket Adı</label>
                                <input type="text" required name="adpaket" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hizmet</label>
                                <select name="hizmetler[]" multiple class="form-control opsiyonelSelect hizmet_secimi" style="width:100% !important">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Süre Alanı -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Süre </label>
                                <div class="input-group">
                                    <input type="tel" name="paketsure" class="form-control" min="1" placeholder="Süre">
                                  
                            </div>
                        </div>
                         </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Seans (opsiyonel)</label>
                                <input type="tel" name="seanslar" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fiyat (₺) (opsiyonel)</label>
                                <input type="tel" name="fiyatlar" class="form-control">
                            </div>
                        </div>
                        
                        <div class="paket_hizmetler_bolumu" style="margin-left: 20px">
                            <div class="row" data-value="0">
                                <!-- Hizmet seçimleri buraya gelecek -->
                            </div>
                        </div>
                   
                </div>
                <div class="modal-footer" style="display:block">
                    <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                Kaydet
                            </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                            <button id="modal_kapat_paket"
                                type="button"
                                class="btn btn-danger btn-lg btn-block"
                                data-dismiss="modal"
                            >
                                Kapat
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>