{{-- Paket ekle + duzenle ortak formu. Parametre: $duzenleme (bool). Stil: public/css/paket-form.css, davranis: custom.js paketTurGuncelle. --}}
@php $d = !empty($duzenleme); @endphp
<div class="modal-header pkf-ust">
    <div>
        <div class="pkf-baslik">{{$d ? 'Paket Düzenle' : 'Yeni Paket'}}</div>
        <div class="pkf-alt">{{$d ? 'Yapılan değişiklikler daha önce yapılan satışları etkilemez.' : 'Paket bilgilerini girin, satışta bu değerler hazır gelir.'}}</div>
    </div>
    <button type="button" class="pkf-kapat" data-dismiss="modal" aria-label="Kapat">&times;</button>
</div>
<div class="modal-body pkf-govde">
    {!!csrf_field()!!}
    @if($d)<input type="hidden" name="paket_id" id="paket_id_duzenleme">@endif
    <input type="hidden" name="sube" value="{{$isletme->id}}">
    <div class="pkf-izgara">
        <div class="pkf-alan pkf-tam">
            <label>Paket Adı <span class="pkf-zorunlu">*</span></label>
            <input type="text" required name="adpaket" @if($d) id="paketad" @endif class="form-control" placeholder="Örn. 3 Bölge Lazer Epilasyon">
        </div>
        <div class="pkf-alan pkf-tam">
            <label>Hizmetler <span class="pkf-zorunlu">*</span></label>
            <select name="hizmetler[]" @if($d) id="paketHizmetleri" @endif multiple class="form-control opsiyonelSelect hizmet_secimi" style="width:100% !important">
                <option></option>
            </select>
        </div>
        <div class="pkf-alan">
            <label>Paket Türü</label>
            <select name="sinirsiz" class="form-control">
                <option value="0">Seanslı</option>
                <option value="1">Sınırsız</option>
            </select>
        </div>
        <div class="pkf-alan pkf-seans">
            <label>Seans Sayısı</label>
            <input type="tel" name="seanslar" @if($d) id="paketSeanslari" @endif class="form-control" placeholder="Örn. 8">
        </div>
        <div class="pkf-alan paket-limit-alani" style="display:none">
            <label>Günlük Limit <small>(opsiyonel)</small></label>
            <input type="number" name="gunluk_limit" class="form-control" min="1" max="50" step="1" placeholder="Limitsiz">
            <small>Boş bırakılırsa her randevu seansa yazılır</small>
        </div>
        <div class="pkf-alan" @if(!empty($isletme->studyo_modu))style="display:none"@endif>
            <label>Paket Fiyatı (₺)</label>
            <input type="tel" name="fiyatlar" @if($d) id="paketFiyati" @endif class="form-control" placeholder="0">
        </div>
        <div class="pkf-alan">
            <label>Süre (dk)</label>
            <input type="tel" name="paketsure" class="form-control" placeholder="Örn. 60">
        </div>
        <div class="pkf-alan pkf-iki">
            <label>Geçerlilik Süresi <small class="paket-sure-ipucu">(opsiyonel)</small></label>
            <div class="pkf-sure">
                <input type="number" name="gecerlilik_sayi" class="form-control" min="1" max="120" step="1" placeholder="Süre">
                <select name="gecerlilik_birim" class="form-control">
                    <option value="ay">Ay</option>
                    <option value="yil">Yıl</option>
                </select>
            </div>
            <small>Müşterinin ilk geliş tarihinden itibaren başlar</small>
        </div>
    </div>
    @if(!$d)
    {{-- Eski hizmet satiri kodu bu kapsayiciyi bekliyor --}}
    <div class="paket_hizmetler_bolumu" hidden><div class="row" data-value="0"></div></div>
    @endif
</div>
<div class="modal-footer pkf-alt-bar">
    <button type="button" class="pkf-btn pkf-btn-ikincil" data-dismiss="modal">Kapat</button>
    <button type="submit" class="pkf-btn pkf-btn-birincil"><i class="fa fa-check"></i> Kaydet</button>
</div>
