{{-- Prim & Hak Edis modallarinin ortak detay butonlari.
     $aktif: acik olan detay tipi (maas|prim|diger|bonus|kesinti); $odeButonu: "Ode" ekranina donus butonu. --}}
@php
  $_detayButonlari = [
    'maas'    => ['Maaş Detay',    'fa-university', 'liste'],
    'prim'    => ['Prim Detay',    'fa-bar-chart',  'detay'],
    'diger'   => ['Avans Detay',   'fa-clock-o',    'avans'],
    'bonus'   => ['Bonus Detay',   'fa-plus-circle','bonus'],
    'kesinti' => ['Kesinti Detay', 'fa-minus-circle','kesinti'],
  ];
  $_aktifDetay = $aktif ?? null;
@endphp
<div class="pm-quick-actions">
  @if(!empty($odeButonu))
  @yetki('personel.odeme_yap')
  <button type="button" class="pm-quick-btn pm-quick-btn--ode pm-quick-ode">
    <i class="fa fa-credit-card"></i> Öde
  </button>
  @endyetki
  @endif
  @foreach($_detayButonlari as $_tip => $_b)
  <button type="button" class="pm-quick-btn pm-quick-btn--{{$_b[2]}} pm-quick-detay {{ $_aktifDetay === $_tip ? 'pm-quick-btn--aktif' : '' }}" data-detay="{{$_tip}}" {{ $_aktifDetay === $_tip ? 'disabled' : '' }}>
    <i class="fa {{$_b[1]}}"></i> {{$_b[0]}}
  </button>
  @endforeach
</div>
