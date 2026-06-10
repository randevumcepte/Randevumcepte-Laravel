@extends('layout.layout_isletmeadminpaketornek')
@section('content')
@php
    // Fiyatlar tüm vergiler dahil girilir → matrah = ucret / 1,20
    $kdvOran = 0.20;
    $tutar   = (float) $paket->ucret;
    $matrah  = $tutar / (1 + $kdvOran);
    $kdv     = $tutar - $matrah;
@endphp
<div class="main-content container-fluid">
    <h1 class="display-heading text-center">İnteraktif Duyuru Paketi Satın Alma</h1>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Satın Alınacak Paket</div>
                <div class="panel-body table-responsive">
                    <table class="table table-striped table-borderless">
                        <thead style="font-size:18px">
                            <tr>
                                <th>Paket</th>
                                <th>Kullanım Süresi</th>
                                <th class="text-right">Matrah</th>
                                <th class="text-right">KDV (%20)</th>
                                <th class="text-right">Toplam (KDV Dahil)</th>
                            </tr>
                        </thead>
                        <tbody class="no-border-x" style="font-size:15px">
                            <tr>
                                <td><strong>{{ number_format($paket->sms_adet, 0, ',', '.') }} SMS</strong></td>
                                <td>Sınırsız</td>
                                <td class="text-right">{{ number_format($matrah, 2, ',', '.') }} <span class="simge-tl">&#8378;</span></td>
                                <td class="text-right">{{ number_format($kdv, 2, ',', '.') }} <span class="simge-tl">&#8378;</span></td>
                                <td class="text-right"><strong>{{ number_format($tutar, 2, ',', '.') }} <span class="simge-tl">&#8378;</span></strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted" style="margin-top:8px">Fiyatlara KDV ve tüm vergiler dahildir.</p>
                </div>
            </div>
        </div>

        {{-- Fatura Bilgileri (e-Arşiv faturası için; sonradan Paraşüt'e gönderilecek) --}}
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Fatura Bilgileri</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Ünvan / Ad Soyad</label>
                            <input type="text" id="fatura_unvan" class="form-control" value="{{ $isletme->salon_adi ?? '' }}" placeholder="Fatura ünvanı">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>VKN / TC Kimlik No</label>
                            <input type="text" id="fatura_vkn" class="form-control" placeholder="Vergi veya TC kimlik numarası">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Vergi Dairesi</label>
                            <input type="text" id="fatura_vd" class="form-control" placeholder="Vergi dairesi (şahıssa boş bırakın)">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Adres</label>
                            <input type="text" id="fatura_adres" class="form-control" placeholder="Fatura adresi">
                        </div>
                    </div>
                    <p class="text-muted">Fatura, ödeme tamamlandıktan sonra e-Arşiv olarak düzenlenip "Faturalarım" alanınıza eklenecektir.</p>
                </div>
            </div>
        </div>

        {{-- Ödeme yöntemi --}}
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Ödeme Yöntemi</div>
                <div class="panel-body">
                    <button type="button" class="btn btn-primary" style="font-size:15px" onclick="duyuruOdemeGoster('kart')">Kredi Kartı ile Öde</button>
                    <button type="button" class="btn btn-primary" style="font-size:15px" onclick="duyuruOdemeGoster('havale')">Havale/EFT ile Öde</button>
                </div>
            </div>
        </div>

        {{-- Kredi kartı: PayTR bağlamasi sonra eklenecek (placeholder) --}}
        <div class="col-xs-12 col-sm-12 col-md-12" id="duyuru_kart_bolum" style="display:none">
            <div class="panel panel-default panel-table">
                <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Kredi Kartı ile Ödeme</div>
                <div class="panel-body">
                    <div class="alert alert-info" style="margin-bottom:0">
                        Kredi kartı ile ödeme altyapısı (PayTR) en kısa sürede aktif olacaktır. Taksit imkânı bu alanda sunulacaktır.
                        Şimdilik <strong>Havale/EFT</strong> ile ödeme yapabilirsiniz.
                    </div>
                    {{-- TODO(PayTR): buraya iframe token akışı + taksit (no_installment/max_installment) + fatura bilgileri submit eklenecek --}}
                </div>
            </div>
        </div>

        {{-- Havale/EFT: aktif --}}
        <div class="col-xs-12 col-sm-12 col-md-12" id="duyuru_havale_bolum" style="display:none">
            <div class="panel panel-default">
                <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Banka Bilgilerimiz</div>
                <div class="panel-body table-responsive">
                    <div class="form-group">
                        <label><strong>Aşağıdaki bankalardan havale/EFT yapabilirsiniz. Açıklamaya işletme adınızı ve "{{ $paket->sms_adet }} SMS" yazınız.</strong></label>
                    </div>
                    <table class="table table-striped table-borderless">
                        <thead>
                            <tr>
                                <th>Banka</th>
                                <th>Hesap Sahibi</th>
                                <th>Şube</th>
                                <th>Hesap No</th>
                                <th>IBAN</th>
                            </tr>
                        </thead>
                        <tbody class="no-border-x">
                            <tr>
                                <td>GARANTİ BANKASI</td>
                                <td>WEBFİRMAM İNTERNET HİZ.REK.SAN.TİC.LTD.ŞTİ.</td>
                                <td>1100</td>
                                <td>6298737</td>
                                <td>TR070006200110000006298737</td>
                            </tr>
                            <tr>
                                <td>İŞ BANKASI</td>
                                <td>WEBFİRMAM İNTERNET HİZ.REK.SAN.TİC.LTD.ŞTİ.</td>
                                <td>3430</td>
                                <td>0829529</td>
                                <td>TR880006400000134300829529</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted">Havale/EFT sonrası paketiniz kontrol edilip tanımlanacaktır.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function duyuruOdemeGoster(tip){
    document.getElementById('duyuru_kart_bolum').style.display   = (tip === 'kart')   ? 'block' : 'none';
    document.getElementById('duyuru_havale_bolum').style.display = (tip === 'havale') ? 'block' : 'none';
}
</script>
@endsection
