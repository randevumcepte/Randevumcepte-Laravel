{{-- Paket ekle + duzenle modallari ortak: tur / gecerlilik suresi / gunluk limit. Davranis custom.js paketTurGuncelle. --}}
<div class="col-md-4">
    <div class="form-group">
        <label>Paket Türü</label>
        <select name="sinirsiz" class="form-control">
            <option value="0">Seanslı</option>
            <option value="1">Sınırsız</option>
        </select>
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label>Geçerlilik Süresi <small class="paket-sure-ipucu">(opsiyonel)</small></label>
        <div style="display:flex;gap:8px">
            <input type="number" name="gecerlilik_sayi" class="form-control" min="1" max="120" step="1" placeholder="Süre">
            <select name="gecerlilik_birim" class="form-control">
                <option value="ay">Ay</option>
                <option value="yil">Yıl</option>
            </select>
        </div>
        <small class="text-muted">Müşterinin ilk geliş tarihinden itibaren başlar.</small>
    </div>
</div>
<div class="col-md-4 paket-limit-alani" style="display:none">
    <div class="form-group">
        <label>Günlük Kullanım Limiti</label>
        <input type="number" name="gunluk_limit" class="form-control" min="1" max="50" step="1" value="1">
        <small class="text-muted">Hizmet başına, günde en fazla.</small>
    </div>
</div>
