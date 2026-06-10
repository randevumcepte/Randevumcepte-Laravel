@extends('layout.layout_isletmeadmin')
@section('content')
<div class="main-content container-fluid">
    <h1 class="display-heading text-center">{{ $paket->sms_adet }} SMS — Ödeme</h1>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                    <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
                    <script>iFrameResize({}, '#paytriframe');</script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
