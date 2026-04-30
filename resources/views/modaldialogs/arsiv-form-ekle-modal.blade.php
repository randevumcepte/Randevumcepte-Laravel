<div id="formugondermodal" class="modal modal-top fade calendar-modal">
    <div class="modal-dialog modal-dailog-centered" style="max-width: 600px">
        <form id="arsivformekleme">
            {{ csrf_field() }}
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <input type="hidden" name="arsiv_id" id="arsiv_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="h4">Form Gönder</h4>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                    >
                    ×
                    </button>
                </div>
                <div class="modal-body" style="padding:20px;">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label><b>Form/Sözleşme Türü</b></label>
                            <select name="formtaslaklari" id="formtaslaklari" class="form-control opsiyonelSelect" style="width: 100%;">
                                @php
                                    $salonFormlari = \App\FormTaslaklari::where('salon_id',$isletme->id)->orderBy('sira','asc')->orderByDesc('id')->get();
                                @endphp
                                @if($salonFormlari->count())
                                    @foreach($salonFormlari as $formTaslak)
                                    <option value="{{$formTaslak->id}}" data-dinamik="{{$formTaslak->is_dinamik ? '1' : '0'}}">{{$formTaslak->form_adi}}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Henüz form oluşturmadınız — Ayarlar &gt; Form Taslakları</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label><b>Müşteri</b></label>
                            <select name="formmusterisec" id="formmusterisec" class="form-control opsiyonelSelect musteri_secimi" style="width: 100%;">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label><b>Cep Telefon</b></label>
                            <input class="form-control" required type="tel" name="formmustericeptelefon" id="formmustericeptelefon">
                        </div>

                        <input type="hidden" name="formmusterikimlikno" id="formmusterikimlikno" value="">
                        <input type="hidden" name="formmustericinsiyet" id="formmustericinsiyet" value="0">
                        <input type="hidden" name="formmusteriyas" id='formmusteriyas' value="">

                        <div class="col-md-6 form-group hizmet-alani" style="display: none;">
                            <label>Hizmet</label>
                            <select style="width:100%" class="form-control opsiyonelSelect hizmet_secimi" name="hizmetSozlesmesiHizmet" id="hizmetSozlesmesiHizmet"></select>
                        </div>
                        <div class="col-md-6 form-group ucret-alani" style="display: none;">
                            <label>Toplam Ücret (₺)</label>
                            <input class="form-control" type="tel" name="toplam_ucret" id="toplam_ucret" placeholder="0.00">
                        </div>
                        <div class="col-md-6 form-group ucret-alani" style="display: none;">
                            <label>Kapora (₺)</label>
                            <input class="form-control" type="tel" name="kapora" id="kapora" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: center;">
                    <div class="col-md-6 col-xs-6 col-6 col-sm-6">
                        <button type="submit" class="btn btn-success btn-block">Gönder</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form türü değiştiğinde kontrol et
    $('#formtaslaklari').change(function() {
        var selectedValue = $(this).val();
        
        // Gelin Başı (9) veya Riskli Saç (10) seçildiğinde
        if (selectedValue == '9' || selectedValue == '10' ) {
            $('.ucret-alani').slideDown(300);
            $('#toplam_ucret').prop('required', true);
            $('#kapora').prop('required', true);
        }

         else {
            $('.ucret-alani').slideUp(300);

            $('#toplam_ucret').prop('required', false);
            $('#kapora').prop('required', false);
        }
        if(selectedValue == "12")
        {
           $('.hizmet-alani').slideDown(300);
            $('.ucret-alani').slideDown(300);
            $('#toplam_ucret').prop('required', true);
            $('#kapora').prop('required', false);
        }
        else if(selectedValue != '9' && selectedValue != '10')
        {
            $('.hizmet-alani').slideUp(300);
            $('#toplam_ucret').prop('required', false);
            $('#kapora').prop('required', false);
            $('.ucret-alani').slideUp(300);
        }


    });

    // Modal kapandığında alanları temizle
    $('#formugondermodal').on('hidden.bs.modal', function() {
        $('.ucret-alani').hide();
        $('#toplam_ucret').val('');
        $('#kapora').val('');
        $('#toplam_ucret').prop('required', false);
        $('#kapora').prop('required', false);
    });

    // Form gönderiminde validasyon
    $('#arsivformekleme').submit(function(e) {
        var selectedForm = $('#formtaslaklari').val();
        var toplamUcret = $('#toplam_ucret').val();
        var kapora = $('#kapora').val();

        // Gelin Başı veya Riskli Saç için ücret kontrolü
        if ((selectedForm == '9' || selectedForm == '10') && (!toplamUcret || !kapora)) {
            e.preventDefault();
            alert('Gelin Başı ve Riskli Saç sözleşmeleri için Toplam Ücret ve Kapora alanları zorunludur!');
            return false;
        }

        // Kapora toplam ücretten fazla olamaz
        if (toplamUcret && kapora && parseFloat(kapora) > parseFloat(toplamUcret)) {
            e.preventDefault();
            alert('Kapora miktarı toplam ücretten fazla olamaz!');
            return false;
        }
    });
});
</script>