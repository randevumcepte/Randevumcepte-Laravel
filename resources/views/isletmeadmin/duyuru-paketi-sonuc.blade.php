@extends('layout.layout_isletmeadmin')
@section('content')
<div class="main-content container-fluid">
    <h1 class="display-heading text-center">Ödeme Sonucu</h1>
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
            <div class="panel panel-default">
                <div class="panel-body text-center" style="padding:30px">
                    @if($siparis && $siparis->durum == 1)
                        <div class="alert alert-success">
                            <h3>Ödemeniz Alındı 🎉</h3>
                            <p><strong>{{ number_format($siparis->sms_adet, 0, ',', '.') }} SMS</strong> paketi için ödemeniz başarıyla gerçekleşti.</p>
                            <p>Paketiniz en kısa sürede hesabınıza tanımlanacaktır. Faturanız "Faturalarım" alanına eklenecektir.</p>
                        </div>
                    @elseif($siparis && $siparis->durum == 2)
                        <div class="alert alert-danger">
                            <h3>Ödeme Başarısız</h3>
                            <p>Ödemeniz tamamlanamadı. Lütfen tekrar deneyin veya farklı bir kart kullanın.</p>
                            @if($siparis->basarisiz_neden)<p class="text-muted">{{ $siparis->basarisiz_neden }}</p>@endif
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h3>Ödemeniz İşleniyor</h3>
                            <p>Ödemeniz kontrol ediliyor. Birkaç dakika içinde bu sayfayı yenileyebilirsiniz.</p>
                        </div>
                    @endif
                    <a href="/isletmeyonetim/toplusmsbasvuru" class="btn btn-primary">Paketlere Dön</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
