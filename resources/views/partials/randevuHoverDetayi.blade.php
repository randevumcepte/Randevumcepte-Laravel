<?php
   $r = $randevu->randevu;
   $cepTel = $r->users->cep_telefon ?? '';
   $musteriAdi = $r->users->name ?? '—';
   $tarihStr = \Carbon\Carbon::parse($r->tarih)->format('d.m.Y');
   $saatStr  = substr($randevu->saat, 0, 5);
   $bitisStr = $randevu->saat_bitis ? substr($randevu->saat_bitis, 0, 5) : '';
   $sureStr  = $randevu->sure_dk;

   $statusLabel = 'Beklemede';
   $statusClass = 'beklemede';
   if($r->randevuya_geldi === 1) { $statusLabel = 'Geldi'; $statusClass = 'geldi'; }
   elseif($r->randevuya_geldi === 0) { $statusLabel = 'Gelmedi'; $statusClass = 'gelmedi'; }
   elseif($r->randevuya_gelecek === 1) { $statusLabel = 'Gelecek'; $statusClass = 'gelecek'; }
   elseif($r->durum === 0) { $statusLabel = 'Onay Bekliyor'; $statusClass = 'beklemede'; }

   $hizmetAdi = ($randevu->hizmet_id && $randevu->hizmetler) ? $randevu->hizmetler->hizmet_adi : '';
   $personelAdi = ($randevu->personel_id && $randevu->personeller) ? $randevu->personeller->personel_adi : '';
   $odaAdi = ($randevu->oda_id && $randevu->oda) ? $randevu->oda->oda_adi : '';
   $cihazAdi = ($randevu->cihaz_id && $randevu->cihaz) ? $randevu->cihaz->cihaz_adi : '';
   $musteriNotu = $r->notlar ?? '';
   $personelNotu = $r->personel_notu ?? '';

   $isOnGorusme = !empty($r->on_gorusme_id);
   $ognAdi = '';
   if($isOnGorusme && $r->ongorusme){
      if($r->ongorusme->paket)       { $ognAdi = $r->ongorusme->paket->paket_adi; }
      elseif($r->ongorusme->urun)    { $ognAdi = $r->ongorusme->urun->urun_adi; }
      elseif($r->ongorusme->hizmet)  { $ognAdi = $r->ongorusme->hizmet->hizmet_adi; }
      if($r->ongorusme->durum === 1) { $statusLabel = 'Satış Yapıldı'; $statusClass = 'geldi'; }
      elseif(is_null($r->ongorusme->durum)) { $statusLabel = 'Beklemede'; $statusClass = 'beklemede'; }
      else { $statusLabel = 'Satış Yapılmadı'; $statusClass = 'gelmedi'; }
   }

   $telGosterim = $cepTel ? ($rol == 5 ? substr($cepTel, 0, 3) . ' *** **' . substr($cepTel, -2) : $cepTel) : '';
?>
<div class="rc-tip">
   <div class="rc-tip-head">
      <div class="rc-tip-name">{{ $musteriAdi }}</div>
      <div class="rc-tip-meta">
         <span class="rc-tip-time"><i class="fa fa-clock-o"></i> {{ $saatStr }}{{ $bitisStr ? '–'.$bitisStr : '' }}</span>
         @if($sureStr)<span class="rc-tip-dur">· {{ $sureStr }} dk</span>@endif
         <span class="rc-tip-status rc-st-{{ $statusClass }}">{{ $statusLabel }}</span>
      </div>
   </div>
   <div class="rc-tip-body">
      @if($telGosterim)
      <div class="rc-tip-row"><i class="fa fa-phone"></i><span>{{ $telGosterim }}</span></div>
      @endif
      @if($isOnGorusme)
         @if($ognAdi)<div class="rc-tip-row"><i class="fa fa-bullhorn"></i><span>{{ $ognAdi }}</span></div>@endif
         @if($r->ongorusme && $r->ongorusme->personel)
         <div class="rc-tip-row"><i class="fa fa-user"></i><span>{{ $r->ongorusme->personel->personel_adi ?? '—' }}</span></div>
         @endif
      @else
         @if(!empty($paketAdi))
            <div class="rc-tip-row"><i class="fa fa-gift"></i><span><strong>{{ $paketAdi }}</strong>@if($hizmetAdi) <span style="opacity:.7">· {{ $hizmetAdi }}</span>@endif</span></div>
         @elseif($hizmetAdi)
            <div class="rc-tip-row"><i class="fa fa-magic"></i><span>{{ $hizmetAdi }}</span></div>
         @endif
         @if($personelAdi)<div class="rc-tip-row"><i class="fa fa-user"></i><span>{{ $personelAdi }}</span></div>@endif
         @if($odaAdi)<div class="rc-tip-row"><i class="fa fa-cube"></i><span>{{ $odaAdi }}</span></div>@endif
         @if($cihazAdi)<div class="rc-tip-row"><i class="fa fa-microchip"></i><span>{{ $cihazAdi }}</span></div>@endif
      @endif
      @if($musteriNotu)
      <div class="rc-tip-row rc-tip-note"><i class="fa fa-comment-o"></i><span>{{ $musteriNotu }}</span></div>
      @endif
      @if($personelNotu)
      <div class="rc-tip-row rc-tip-note"><i class="fa fa-sticky-note"></i><span>{{ $personelNotu }}</span></div>
      @endif
   </div>
</div>
