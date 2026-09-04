<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>SMS Raporu - {{ $salon->salon_adi }}</title>
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 11px; color: #222; margin: 0; }
    h1 { font-size: 20px; margin: 0 0 4px; color: #2c5aa0; }
    h2 { font-size: 14px; margin: 20px 0 8px; padding: 6px 10px; background: #2c5aa0; color: #fff; border-radius: 3px; }
    .meta { color: #666; margin-bottom: 14px; font-size: 10px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 8px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #f0f0f0; font-weight: bold; }
    .ozet-box { display: inline-block; padding: 10px 16px; background: #eef2fa; border: 1px solid #b6c9e0; border-radius: 4px; margin: 3px 6px 3px 0; min-width: 120px; }
    .ozet-box .l { font-size: 10px; color: #555; display: block; }
    .ozet-box b { color: #2c5aa0; font-size: 16px; }
    .sag { text-align: right; }
</style>
</head>
<body>

<h1>💬 SMS Gönderim Raporu</h1>
<div class="meta">
    <b>{{ $salon->salon_adi }}</b> (ID: {{ $salon->id }})<br>
    Aralık: <b>{{ $baslangic }}</b> — <b>{{ $bitis }}</b><br>
    Rapor tarihi: {{ date('d.m.Y H:i') }}
</div>

<h2>Genel Özet</h2>
<div>
    <div class="ozet-box"><span class="l">Kayıt Sayısı</span><b>{{ number_format((int)($smsOzet->kayit ?? 0),0,',','.') }}</b></div>
    <div class="ozet-box"><span class="l">Toplam SMS Adet</span><b>{{ number_format((int)($smsOzet->adet ?? 0),0,',','.') }}</b></div>
    <div class="ozet-box"><span class="l">Toplam Kredi</span><b>{{ number_format((int)($smsOzet->kredi ?? 0),0,',','.') }}</b></div>
</div>

<h2>Türe Göre</h2>
<table>
    <thead>
        <tr>
            <th>Tür</th>
            <th class="sag">Kayıt</th>
            <th class="sag">SMS Adet</th>
            <th class="sag">Kredi</th>
        </tr>
    </thead>
    <tbody>
    @foreach($smsTureGore as $s)
        <tr>
            <td>{{ $turAdi[$s->tur] ?? ('Tür '.$s->tur) }}</td>
            <td class="sag">{{ number_format($s->kayit,0,',','.') }}</td>
            <td class="sag">{{ number_format($s->adet,0,',','.') }}</td>
            <td class="sag">{{ number_format($s->kredi,0,',','.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Aylık Dağılım</h2>
<table>
    <thead>
        <tr>
            <th>Ay</th>
            <th class="sag">Kayıt</th>
            <th class="sag">SMS Adet</th>
            <th class="sag">Kredi</th>
        </tr>
    </thead>
    <tbody>
    @foreach($smsAylik as $a)
        <tr>
            <td>{{ $a->ay }}</td>
            <td class="sag">{{ number_format($a->kayit,0,',','.') }}</td>
            <td class="sag">{{ number_format($a->adet,0,',','.') }}</td>
            <td class="sag">{{ number_format($a->kredi,0,',','.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
