<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>WhatsApp Raporu - {{ $salon->salon_adi }}</title>
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 11px; color: #222; margin: 0; }
    h1 { font-size: 20px; margin: 0 0 4px; color: #128c7e; }
    h2 { font-size: 14px; margin: 20px 0 8px; padding: 6px 10px; background: #128c7e; color: #fff; border-radius: 3px; }
    .meta { color: #666; margin-bottom: 14px; font-size: 10px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 8px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #f0f0f0; font-weight: bold; }
    .ozet-box { display: inline-block; padding: 10px 16px; background: #eef7f4; border: 1px solid #b6dcd0; border-radius: 4px; margin: 3px 6px 3px 0; min-width: 100px; }
    .ozet-box .l { font-size: 10px; color: #555; display: block; }
    .ozet-box b { color: #128c7e; font-size: 16px; }
    .durum-1 { color: #128c7e; font-weight: bold; }
    .durum-2 { color: #c0392b; }
    .durum-0 { color: #b58900; }
    .durum-3 { color: #7d5ba6; }
    .sag { text-align: right; }
</style>
</head>
<body>

<h1>📱 WhatsApp Gönderim Raporu</h1>
<div class="meta">
    <b>{{ $salon->salon_adi }}</b> (ID: {{ $salon->id }})<br>
    Aralık: <b>{{ $baslangic }}</b> — <b>{{ $bitis }}</b><br>
    Rapor tarihi: {{ date('d.m.Y H:i') }}
</div>

<h2>Genel Özet</h2>
<div>
    <div class="ozet-box"><span class="l">Toplam</span><b>{{ number_format($waOzet['toplam'],0,',','.') }}</b></div>
    <div class="ozet-box"><span class="l">Başarılı</span><b class="durum-1">{{ number_format($waOzet['basarili'],0,',','.') }}</b></div>
    <div class="ozet-box"><span class="l">Hata</span><b class="durum-2">{{ number_format($waOzet['hata'],0,',','.') }}</b></div>
    <div class="ozet-box"><span class="l">Kuyrukta</span><b class="durum-0">{{ number_format($waOzet['kuyrukta'],0,',','.') }}</b></div>
    <div class="ozet-box"><span class="l">SMS'e Düşen</span><b class="durum-3">{{ number_format($waOzet['sms_fallback'],0,',','.') }}</b></div>
</div>

<h2>Gönderim Tipine Göre</h2>
<table>
    <thead>
        <tr>
            <th>Tip</th>
            <th class="sag">Toplam</th>
            <th class="sag">Başarılı</th>
            <th class="sag">Hata</th>
            <th class="sag">SMS'e Düşen</th>
        </tr>
    </thead>
    <tbody>
    @foreach($waTipeGore as $t)
        <tr>
            <td>{{ $t->gonderim_tipi ?: '(belirtilmemiş)' }}</td>
            <td class="sag">{{ number_format($t->toplam,0,',','.') }}</td>
            <td class="sag durum-1">{{ number_format($t->basarili,0,',','.') }}</td>
            <td class="sag durum-2">{{ number_format($t->hata,0,',','.') }}</td>
            <td class="sag durum-3">{{ number_format($t->fallback,0,',','.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Aylık Dağılım</h2>
<table>
    <thead>
        <tr>
            <th>Ay</th>
            <th class="sag">Toplam</th>
            <th class="sag">Başarılı</th>
        </tr>
    </thead>
    <tbody>
    @foreach($waAylik as $a)
        <tr>
            <td>{{ $a->ay }}</td>
            <td class="sag">{{ number_format($a->toplam,0,',','.') }}</td>
            <td class="sag durum-1">{{ number_format($a->basarili,0,',','.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
