{{-- Musteri karti: iletisim dili (pilot salonlar). Musteri uygulamadan kendi sectiyse personel degistiremez. --}}
@if(\App\Services\MusteriDili::acikMi($isletme->id))
@php $mdKayit = isset($mdUserId) && $mdUserId ? \App\Services\MusteriDili::kayit($mdUserId) : null; @endphp
<div class="col-md-6 col-xs-6 col-sm-6 col-6">
   <label>İletişim Dili</label>
   <select name="iletisim_dili" class="form-control" @if($mdKayit && $mdKayit->kaynak === 'musteri') disabled title="Müşteri bu dili kendisi seçti" @endif>
      <option value="">{{ $mdKayit ? 'Değiştirme' : 'Türkçe (seçilmedi)' }}</option>
      @foreach(\App\Services\MusteriDili::diller() as $d)
      <option value="{{$d['kod']}}" @if($mdKayit && $mdKayit->dil === $d['kod']) selected @endif>{{$d['bayrak']}} {{$d['ad']}}</option>
      @endforeach
   </select>
   <small style="color:#64748b">WhatsApp ve uygulama bildirimleri bu dilde gider; SMS Türkçe gider.</small>
</div>
@endif
