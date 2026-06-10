@extends('layout.layout_isletmeadmin')
@section('content')
<div class="main-content container-fluid">
    <div style="max-width:760px;margin:0 auto;">

        {{-- Modern marka başlık kartı --}}
        <div style="position:relative;overflow:hidden;border-radius:20px;margin-bottom:22px;
                    background:radial-gradient(circle at 12% 20%, rgba(217,70,239,0.45) 0%, transparent 45%),
                               linear-gradient(135deg,#1a0533 0%,#5C008E 55%,#7B2FB8 100%);
                    color:#fff;padding:26px 30px;box-shadow:0 16px 40px rgba(92,0,142,0.30);">
            <div style="position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                <div>
                    <div style="font-size:12px;letter-spacing:.6px;opacity:.85;text-transform:uppercase;">
                        <i class="fa fa-bullhorn" style="margin-right:6px;"></i> İnteraktif Duyuru Paketi
                    </div>
                    <div style="font-size:27px;font-weight:800;margin-top:6px;line-height:1.1;">
                        {{ $paket->paket_adi ?: (number_format($paket->sms_adet, 0, ',', '.').' SMS') }}
                    </div>
                    <div style="font-size:13px;opacity:.85;margin-top:4px;">Güvenli ödeme · Kredi kartına taksit imkânı</div>
                </div>
                <div style="text-align:right;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);
                            border-radius:14px;padding:12px 18px;backdrop-filter:blur(6px);">
                    <div style="font-size:11px;opacity:.85;text-transform:uppercase;letter-spacing:.5px;">Ödenecek Tutar</div>
                    <div style="font-size:26px;font-weight:800;">{{ number_format($paket->ucret, 2, ',', '.') }} ₺</div>
                    <div style="font-size:11px;opacity:.8;">Tüm vergiler dahil</div>
                </div>
            </div>
        </div>

        {{-- PayTR ödeme alanı --}}
        <div style="background:#fff;border-radius:18px;box-shadow:0 4px 18px rgba(15,23,42,0.06);
                    border:1px solid #f0f1f4;padding:10px 12px;">
            <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
            <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
            <script>iFrameResize({}, '#paytriframe');</script>
        </div>

        <div style="text-align:center;color:#9aa0ab;font-size:12px;margin:16px 0;">
            <i class="fa fa-lock" style="margin-right:5px;"></i> Ödemeleriniz 256-bit SSL ile PayTR güvencesiyle korunmaktadır.
        </div>

    </div>
</div>
@endsection
