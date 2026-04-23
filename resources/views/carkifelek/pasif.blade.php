@extends('layout.layout_cark')

@section('title', 'Çarkıfelek')
@section('content')
<div style="max-width:720px;margin:80px auto;padding:40px 28px;background:#fff;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);text-align:center;">
    <div style="font-size:64px;margin-bottom:14px;">🎡</div>
    <h2 style="font-weight:800;color:#2d3436;margin-bottom:10px;">Çarkıfelek Şu An Aktif Değil</h2>
    <p style="color:#636e72;font-size:15px;line-height:1.6;">
        {{ $salon->salon_adi ?? 'Salon' }} çarkıfelek kampanyasını henüz başlatmamış veya geçici olarak kapatmış.<br>
        Kampanya başladığında onaylanmış randevularınız üzerinden çevirme hakkı kazanacaksınız.
    </p>
    <a href="/" style="display:inline-block;margin-top:24px;padding:12px 32px;background:#6c5ce7;color:#fff;border-radius:50px;text-decoration:none;font-weight:600;">Anasayfaya Dön</a>
</div>
@endsection
