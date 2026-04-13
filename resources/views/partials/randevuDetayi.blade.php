<?php $cepTel = $randevu->randevu->users->cep_telefon ?? ''; $yardimciPersonel = ""; 
 ?>
<table style="width:100%;margin:0 0 10px 0">
    <tr><td>Telefon</td><td>:</td><td>{{ $rol == 5 ? substr($cepTel, 0, 3) . ' *** **' . substr($cepTel, -2) : $cepTel}}</td></tr>

    @if($randevu->randevu->on_gorusme_id)
        <tr><td>Ön Görüşme Nedeni</td><td>:</td>
            <td>
                {{ $randevu->randevu->ongorusme->paket->paket_adi ?? '' }}
                {{ $randevu->randevu->ongorusme->urun->urun_adi ?? '' }}
                {{ $randevu->randevu->ongorusme->hizmet->hizmet_adi ?? '' }}
            </td>
        </tr>
        <tr><td>Görüşmeyi Yapan</td><td>:</td><td>{{ $randevu->randevu->ongorusme->personel->personel_adi ?? '' }}</td></tr>
        <tr><td>Zaman</td><td>:</td><td>{{ $randevu->randevu->tarih }} {{ $randevu->randevu->saat }}</td></tr>
        <tr><td>Süre(dk)</td><td>:</td><td>{{ $randevu->sure_dk }}</td></tr>
        <tr><td>Oluşturan</td><td>:</td><td>
              @if($randevu->randevu->olusturan_personel_id && $randevu->randevu->olusturan_personel)
                {{$randevu->randevu->olusturan_personel->name}}
            @endif
            @if($randevu->randevu->easistan)
                Asistan üzerinden müşteri tarafından
            @endif
            @if($randevu->randevu->web)
                Web üzerinden müşteri tarafından
            @endif
             @if($randevu->randevu->uygulama)
                Uygulama üzerinden müşteri tarafından
            @endif

        </td></tr>
        <tr>
            <td>Durum</td><td>:</td>
            <td>
                @if($randevu->randevu->ongorusme->durum === 1) Satış Yapıldı
                @elseif(is_null($randevu->randevu->ongorusme->durum)) Beklemede
                @else Satış Yapılmadı @endif
            </td>
        </tr>
        <tr><td>Personel Notu</td><td>:</td><td>{{ $randevu->randevu->ongorusme->aciklama ?? '' }}</td></tr>
    @else
        <tr><td>Hizmet</td><td>:</td><td>{{ $randevu->hizmet_id && $randevu->hizmetler ? $randevu->hizmetler->hizmet_adi : '' }}</td></tr>
        <tr><td>Personel</td><td>:</td><td>{{ $randevu->personel_id && $randevu->personeller ? $randevu->personeller->personel_adi : '' }}</td></tr>
        <tr>
            <td>Yardımcı Personel(-ler)</td><td>:</td>
            <td>
                @foreach($randevu->randevu->hizmetler as $hizmetler)
                    @if($hizmetler->hizmet_id == $randevu->hizmet_id && $randevu->oda_id == $hizmetler->oda_id && $randevu->cihaz_id == $hizmetler->cihaz_id && $randevu->yardimci_personel)
                        <?php $yardimciPersonel .= $randevu->personeller->personel_adi .""; ?>
                        
                    @endif
                @endforeach
            </td>
        </tr>
        <tr><td>Cihaz</td><td>:</td><td>{{ $randevu->cihaz_id && $randevu->cihaz ? $randevu->cihaz->cihaz_adi : '' }}</td></tr>
        <tr><td>Oda</td><td>:</td><td>{{ $randevu->oda_id && $randevu->oda ? $randevu->oda->oda_adi : '' }}</td></tr>
        <tr><td>Zaman</td><td>:</td><td>{{ $randevu->randevu->tarih }} {{ $randevu->saat }}</td></tr>
        <tr><td>Süre(dk)</td><td>:</td><td>{{ $randevu->sure_dk }}</td></tr>
        <tr><td>Oluşturan</td><td>:</td><td>
            @if($randevu->randevu->olusturan_personel_id && $randevu->randevu->olusturan_personel)
                {{$randevu->randevu->olusturan_personel->name}}
            @endif
            @if($randevu->randevu->easistan)
                Asistan üzerinden müşteri tarafından
            @endif
            @if($randevu->randevu->web)
                Web üzerinden müşteri tarafından
            @endif
             @if($randevu->randevu->uygulama)
                Uygulama üzerinden müşteri tarafından
            @endif
         


        </td></tr>
        <tr>
            <td>Geldi mi?</td><td>:</td>
            <td>
                @if($randevu->randevu->randevuya_geldi === 1) Geldi
                @elseif($randevu->randevu->randevuya_geldi === 0) Gelmedi
                @else Belirtilmemiş @endif
            </td>
        </tr>
         @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
        <tr><td>Fiyat (₺)</td><td>:</td><td>{{ $randevu->fiyat }}</td></tr>
        @endif
        <tr><td>Müşteri Notu</td><td>:</td><td>{{ $randevu->randevu->notlar }}</td></tr>
        <tr><td>Personel Notu</td><td>:</td><td>{{ $randevu->randevu->personel_notu }}</td></tr>
    @endif
</table>