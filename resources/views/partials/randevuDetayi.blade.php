<?php
   $cepTel = $randevu->randevu->users->cep_telefon ?? '';
   $yardimciPersonel = "";
   // On gorusme nedeni: paket/urun/hizmet adi
   $_ogn = '';
   if ($randevu->randevu->on_gorusme_id && $randevu->randevu->ongorusme) {
      $_og = $randevu->randevu->ongorusme;
      if ($_og->paket)       { $_ogn = $_og->paket->paket_adi; }
      elseif ($_og->urun)    { $_ogn = $_og->urun->urun_adi; }
      elseif ($_og->hizmet)  { $_ogn = $_og->hizmet->hizmet_adi; }
   }
   $_olusturanText = '—';
   if ($randevu->randevu->olusturan_personel_id && $randevu->randevu->olusturan_personel) {
      $_olusturanText = $randevu->randevu->olusturan_personel->name;
   } elseif ($randevu->randevu->easistan) {
      $_olusturanText = 'Asistan üzerinden müşteri';
   } elseif ($randevu->randevu->web) {
      $_olusturanText = 'Web üzerinden müşteri';
   } elseif ($randevu->randevu->uygulama) {
      $_olusturanText = 'Uygulama üzerinden müşteri';
   }
?>

<div class="rd-detail">
    <div class="rd-row">
       <div class="rd-label"><i class="fa fa-phone"></i> Telefon</div>
       <div class="rd-value">
          {{ $rol == 5 ? substr($cepTel, 0, 3) . ' *** **' . substr($cepTel, -2) : ($cepTel ?: '—') }}
       </div>
    </div>

    @if($randevu->randevu->on_gorusme_id)
        {{-- ÖN GÖRÜŞME RANDEVUSU --}}
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-bullhorn"></i> Ön Görüşme Nedeni</div>
           <div class="rd-value {{ $_ogn ? '' : 'empty' }}">{{ $_ogn ?: 'Belirtilmemiş' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-user"></i> Görüşmeyi Yapan</div>
           <div class="rd-value">{{ $randevu->randevu->ongorusme->personel->personel_adi ?? '—' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-clock-o"></i> Zaman</div>
           <div class="rd-value">{{ \Carbon\Carbon::parse($randevu->randevu->tarih)->format('d.m.Y') }} {{ substr($randevu->randevu->saat,0,5) }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-hourglass-half"></i> Süre</div>
           <div class="rd-value">{{ $randevu->sure_dk }} dk</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-pencil"></i> Oluşturan</div>
           <div class="rd-value">{{ $_olusturanText }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-info-circle"></i> Durum</div>
           <div class="rd-value">
              @if($randevu->randevu->ongorusme->durum === 1)
                 <span class="rd-status basarili">Satış Yapıldı</span>
              @elseif(is_null($randevu->randevu->ongorusme->durum))
                 <span class="rd-status beklemede">Beklemede</span>
              @else
                 <span class="rd-status iptal">Satış Yapılmadı</span>
              @endif
           </div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-sticky-note"></i> Personel Notu</div>
           <div class="rd-value {{ !empty($randevu->randevu->ongorusme->aciklama) ? '' : 'empty' }}">
              {{ $randevu->randevu->ongorusme->aciklama ?: 'Not eklenmemiş' }}
           </div>
        </div>
    @else
        {{-- NORMAL RANDEVU --}}
        @if(!empty($paketAdi))
        {{-- PAKET RANDEVUSU: paket adini ust satira yaz, sonra paket icindeki tum hizmetleri listele --}}
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-gift"></i> Paket</div>
           <div class="rd-value"><strong>{{ $paketAdi }}</strong></div>
        </div>
        @php
           $_paketHizmetler = $randevu->randevu && $randevu->randevu->hizmetler
               ? $randevu->randevu->hizmetler->filter(function($h){ return $h->hizmet_id; })
               : collect();
           $_paketToplamSure  = $_paketHizmetler->sum('sure_dk');
           $_paketToplamFiyat = $_paketHizmetler->sum('fiyat');
        @endphp
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-list"></i> Hizmetler ({{ $_paketHizmetler->count() }})</div>
           <div class="rd-value">
              @foreach($_paketHizmetler as $_ph)
              <div style="padding:3px 0; border-bottom:1px dashed #f1ecf7;">
                 <strong style="color:#2d2143;">{{ $_ph->hizmetler ? $_ph->hizmetler->hizmet_adi : '—' }}</strong>
                 <span style="color:#7c6c8a; font-size:11.5px; margin-left:8px;">
                    {{ $_ph->sure_dk ? $_ph->sure_dk.' dk' : '' }}
                 </span>
                 @if($_ph->personeller)
                 <span style="color:#5C008E; font-size:11.5px; margin-left:6px;"><i class="fa fa-user" style="font-size:10px;"></i> {{ $_ph->personeller->personel_adi }}</span>
                 @endif
              </div>
              @endforeach
              @if($_paketHizmetler->count() === 0)
              <span style="color:#bcb3c9;">—</span>
              @endif
           </div>
        </div>
        @if($_paketToplamSure > 0 || $_paketToplamFiyat > 0)
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-calculator"></i> Toplam</div>
           <div class="rd-value">
              <span style="font-weight:600;">{{ $_paketToplamSure }} dk</span>
              @if($_paketToplamFiyat > 0)
                 · <span style="font-weight:600;">{{ number_format($_paketToplamFiyat,0,',','.') }} ₺</span>
              @endif
           </div>
        </div>
        @endif
        @else
        {{-- TEKLI HIZMET (paket degil) --}}
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-magic"></i> Hizmet</div>
           <div class="rd-value">{{ $randevu->hizmet_id && $randevu->hizmetler ? $randevu->hizmetler->hizmet_adi : '—' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-user"></i> Personel</div>
           <div class="rd-value">{{ $randevu->personel_id && $randevu->personeller ? $randevu->personeller->personel_adi : '—' }}</div>
        </div>
        @endif
        @php
           $_yp = '';
           foreach($randevu->randevu->hizmetler as $hizmetler) {
              if ($hizmetler->hizmet_id == $randevu->hizmet_id
                  && $randevu->oda_id == $hizmetler->oda_id
                  && $randevu->cihaz_id == $hizmetler->cihaz_id
                  && $randevu->yardimci_personel) {
                 $_yp .= ($randevu->personeller->personel_adi ?? '') . ' ';
              }
           }
           $_yp = trim($_yp);
        @endphp
        @if($_yp && empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-users"></i> Yardımcı Personel</div>
           <div class="rd-value">{{ $_yp }}</div>
        </div>
        @endif
        @if($randevu->cihaz_id && $randevu->cihaz && empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-microchip"></i> Cihaz</div>
           <div class="rd-value">{{ $randevu->cihaz->cihaz_adi }}</div>
        </div>
        @endif
        @if($randevu->oda_id && $randevu->oda && empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-cube"></i> Oda</div>
           <div class="rd-value">{{ $randevu->oda->oda_adi }}</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-clock-o"></i> Zaman</div>
           <div class="rd-value">{{ \Carbon\Carbon::parse($randevu->randevu->tarih)->format('d.m.Y') }} {{ substr($randevu->saat,0,5) }}</div>
        </div>
        @if(empty($paketAdi))
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-hourglass-half"></i> Süre</div>
           <div class="rd-value">{{ $randevu->sure_dk }} dk</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-pencil"></i> Oluşturan</div>
           <div class="rd-value">{{ $_olusturanText }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-check-circle"></i> Geldi mi?</div>
           <div class="rd-value">
              @if($randevu->randevu->randevuya_geldi === 1)
                 <span class="rd-status geldi">Geldi</span>
              @elseif($randevu->randevu->randevuya_geldi === 0)
                 <span class="rd-status gelmedi">Gelmedi</span>
              @else
                 <span class="rd-status beklemede">Belirtilmemiş</span>
              @endif
           </div>
        </div>
        @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-money"></i> Fiyat</div>
           <div class="rd-value">{{ number_format($randevu->fiyat ?: 0, 2, ',', '.') }} ₺</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-comment-o"></i> Müşteri Notu</div>
           <div class="rd-value {{ !empty($randevu->randevu->notlar) ? '' : 'empty' }}">
              {{ $randevu->randevu->notlar ?: 'Not yok' }}
           </div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-sticky-note"></i> Personel Notu</div>
           <div class="rd-value {{ !empty($randevu->randevu->personel_notu) ? '' : 'empty' }}">
              {{ $randevu->randevu->personel_notu ?: 'Not yok' }}
           </div>
        </div>
    @endif
</div>
