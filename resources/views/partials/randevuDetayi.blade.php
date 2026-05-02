<?php
   $cepTel = $randevu->randevu->users->cep_telefon ?? '';
   $yardimciPersonel = "";
   // On gorusme nedeni: yeni gorusme_konusu kolonu varsa ve doluysa onu, yoksa eski paket/urun/hizmet adini
   $_ogn = '';
   if ($randevu->randevu->on_gorusme_id && $randevu->randevu->ongorusme) {
      $_og = $randevu->randevu->ongorusme;
      if (\Schema::hasColumn('on_gorusmeler', 'gorusme_konusu') && !empty($_og->gorusme_konusu)) {
         $_ogn = $_og->gorusme_konusu;
      } elseif ($_og->paket) { $_ogn = $_og->paket->paket_adi; }
      elseif ($_og->urun)  { $_ogn = $_og->urun->urun_adi; }
      elseif ($_og->hizmet){ $_ogn = $_og->hizmet->hizmet_adi; }
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

<style>
   .rd-detail { font-size:13.5px; color:#3a2e57; margin:-10px -15px; }
   .rd-detail .rd-row {
      display:flex; align-items:flex-start;
      padding:9px 14px; border-bottom:1px solid #f1ecf7;
      gap:10px;
   }
   .rd-detail .rd-row:last-child { border-bottom:0; }
   .rd-detail .rd-row:nth-child(odd) { background:#fbfafd; }
   .rd-detail .rd-label {
      flex:0 0 160px; color:#7c6c8a; font-weight:600; font-size:12.5px;
      display:flex; align-items:center; gap:6px;
   }
   .rd-detail .rd-label i { color:#5C008E; opacity:.75; width:14px; text-align:center; }
   .rd-detail .rd-value { flex:1; color:#2d2143; font-weight:500; word-break:break-word; }
   .rd-detail .rd-value.empty { color:#bcb3c9; font-style:italic; font-weight:400; }
   .rd-status {
      display:inline-block; padding:3px 10px; border-radius:20px;
      font-size:11.5px; font-weight:700;
   }
   .rd-status.beklemede { background:#fff4e0; color:#a86200; }
   .rd-status.basarili  { background:#e6f9ed; color:#0c7a3a; }
   .rd-status.iptal     { background:#fdecec; color:#c81e1e; }
   .rd-status.geldi     { background:#e6f9ed; color:#0c7a3a; }
   .rd-status.gelmedi   { background:#fdecec; color:#c81e1e; }
</style>

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
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-magic"></i> Hizmet</div>
           <div class="rd-value">{{ $randevu->hizmet_id && $randevu->hizmetler ? $randevu->hizmetler->hizmet_adi : '—' }}</div>
        </div>
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-user"></i> Personel</div>
           <div class="rd-value">{{ $randevu->personel_id && $randevu->personeller ? $randevu->personeller->personel_adi : '—' }}</div>
        </div>
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
        @if($_yp)
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-users"></i> Yardımcı Personel</div>
           <div class="rd-value">{{ $_yp }}</div>
        </div>
        @endif
        @if($randevu->cihaz_id && $randevu->cihaz)
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-microchip"></i> Cihaz</div>
           <div class="rd-value">{{ $randevu->cihaz->cihaz_adi }}</div>
        </div>
        @endif
        @if($randevu->oda_id && $randevu->oda)
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-cube"></i> Oda</div>
           <div class="rd-value">{{ $randevu->oda->oda_adi }}</div>
        </div>
        @endif
        <div class="rd-row">
           <div class="rd-label"><i class="fa fa-clock-o"></i> Zaman</div>
           <div class="rd-value">{{ \Carbon\Carbon::parse($randevu->randevu->tarih)->format('d.m.Y') }} {{ substr($randevu->saat,0,5) }}</div>
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
