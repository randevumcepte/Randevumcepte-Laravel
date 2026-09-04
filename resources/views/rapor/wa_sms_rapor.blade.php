<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>WhatsApp + SMS Raporu - {{ $salon->salon_adi }}</title>
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10px; color: #222; margin: 0; }
    h1 { font-size: 18px; margin: 0 0 4px; color: #128c7e; }
    h2 { font-size: 13px; margin: 18px 0 6px; padding: 4px 8px; background: #128c7e; color: #fff; border-radius: 3px; }
    h3 { font-size: 11px; margin: 12px 0 4px; color: #333; }
    .meta { color: #666; margin-bottom: 12px; font-size: 10px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 8px; }
    th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
    th { background: #f0f0f0; font-weight: bold; }
    .ozet-box { display: inline-block; padding: 6px 10px; background: #eef7f4; border: 1px solid #b6dcd0; border-radius: 4px; margin: 2px 4px 2px 0; }
    .ozet-box b { color: #128c7e; font-size: 13px; }
    .durum-1 { color: #128c7e; font-weight: bold; }
    .durum-2 { color: #c0392b; }
    .durum-0 { color: #b58900; }
    .durum-3 { color: #7d5ba6; }
    .tel { font-family: monospace; }
    .mesaj-kisa { max-width: 260px; word-break: break-word; }
    .kucuk { font-size: 9px; }
    .sag { text-align: right; }
    .orta { text-align: center; }
</style>
</head>
<body>

<h1>WhatsApp + SMS Gönderim Raporu</h1>
<div class="meta">
    <b>{{ $salon->salon_adi }}</b> (ID: {{ $salon->id }})<br>
    Aralık: <b>{{ $baslangic }}</b> — <b>{{ $bitis }}</b><br>
    Rapor tarihi: {{ date('d.m.Y H:i') }}
</div>

<h2>WhatsApp Özeti</h2>
<div>
    <span class="ozet-box">Toplam: <b>{{ number_format($waOzet['toplam'],0,',','.') }}</b></span>
    <span class="ozet-box">Başarılı: <b class="durum-1">{{ number_format($waOzet['basarili'],0,',','.') }}</b></span>
    <span class="ozet-box">Hata: <b class="durum-2">{{ number_format($waOzet['hata'],0,',','.') }}</b></span>
    <span class="ozet-box">Kuyrukta: <b class="durum-0">{{ number_format($waOzet['kuyrukta'],0,',','.') }}</b></span>
    <span class="ozet-box">SMS'e düşen: <b class="durum-3">{{ number_format($waOzet['sms_fallback'],0,',','.') }}</b></span>
</div>

<h3>WhatsApp — Gönderim Tipine Göre</h3>
<table>
    <thead>
        <tr><th>Tip</th><th class="sag">Toplam</th><th class="sag">Başarılı</th><th class="sag">Hata</th><th class="sag">SMS'e düşen</th></tr>
    </thead>
    <tbody>
    @foreach($waTipeGore as $tip => $d)
        <tr>
            <td>{{ $tip ?: '(bos)' }}</td>
            <td class="sag">{{ $d['toplam'] }}</td>
            <td class="sag durum-1">{{ $d['basarili'] }}</td>
            <td class="sag durum-2">{{ $d['hata'] }}</td>
            <td class="sag durum-3">{{ $d['fallback'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h3>WhatsApp — Günlük Dağılım</h3>
<table>
    <thead><tr><th>Tarih</th><th class="sag">Toplam</th><th class="sag">Başarılı</th></tr></thead>
    <tbody>
    @foreach($waGunluk as $tarih => $d)
        <tr><td>{{ $tarih }}</td><td class="sag">{{ $d['toplam'] }}</td><td class="sag durum-1">{{ $d['basarili'] }}</td></tr>
    @endforeach
    </tbody>
</table>

<h2>SMS Özeti (sms_iletim_raporlari)</h2>
<div>
    <span class="ozet-box">Kayıt: <b>{{ number_format($smsOzet['toplam_kayit'],0,',','.') }}</b></span>
    <span class="ozet-box">Toplam SMS Adet: <b>{{ number_format($smsOzet['toplam_adet'],0,',','.') }}</b></span>
    <span class="ozet-box">Toplam Kredi: <b>{{ number_format($smsOzet['toplam_kredi'],0,',','.') }}</b></span>
</div>

<h3>SMS — Türe Göre</h3>
<table>
    <thead><tr><th>Tür</th><th class="sag">Kayıt</th><th class="sag">Adet</th><th class="sag">Kredi</th></tr></thead>
    <tbody>
    @foreach($smsTureGore as $tur => $d)
        <tr>
            <td>{{ $turAdi[$tur] ?? ('Tür '.$tur) }}</td>
            <td class="sag">{{ $d['kayit'] }}</td>
            <td class="sag">{{ $d['adet'] }}</td>
            <td class="sag">{{ $d['kredi'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>WhatsApp Detay Listesi ({{ $waTumu->count() }} kayıt)</h2>
<table>
    <thead>
        <tr>
            <th>Tarih</th><th>Telefon</th><th>Tip</th><th>Durum</th>
            <th class="mesaj-kisa">Mesaj / Hata</th>
        </tr>
    </thead>
    <tbody>
    @foreach($waTumu as $w)
        @php
            $dTxt = ['0'=>'Kuyrukta','1'=>'Başarılı','2'=>'Hata','3'=>'SMS\'e düştü'][$w->durum] ?? $w->durum;
            $dCls = 'durum-'.$w->durum;
        @endphp
        <tr>
            <td class="kucuk">{{ $w->created_at }}</td>
            <td class="tel">{{ $w->telefon }}</td>
            <td class="kucuk">{{ $w->gonderim_tipi ?? '-' }}</td>
            <td class="{{ $dCls }} kucuk">{{ $dTxt }}</td>
            <td class="mesaj-kisa kucuk">
                {{ mb_substr((string)$w->mesaj, 0, 90) }}{{ mb_strlen((string)$w->mesaj) > 90 ? '...' : '' }}
                @if(!empty($w->hata))
                    <br><span class="durum-2">Hata: {{ mb_substr((string)$w->hata,0,80) }}</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>SMS Detay Listesi ({{ $smsTumu->count() }} kayıt)</h2>
<table>
    <thead>
        <tr><th>Tarih</th><th>Tür</th><th class="sag">Adet</th><th class="sag">Kredi</th><th>Durum</th><th>Açıklama</th></tr>
    </thead>
    <tbody>
    @foreach($smsTumu as $s)
        <tr>
            <td class="kucuk">{{ $s->updated_at }}</td>
            <td class="kucuk">{{ $turAdi[$s->tur] ?? ('Tür '.$s->tur) }}</td>
            <td class="sag">{{ $s->adet }}</td>
            <td class="sag">{{ $s->kredi }}</td>
            <td class="kucuk">{{ $s->durum }}</td>
            <td class="kucuk">{{ mb_substr((string)($s->aciklama ?? ''),0,120) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
