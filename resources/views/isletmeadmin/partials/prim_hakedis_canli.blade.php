{{-- Prim & Hak Edis: kartlar + tablo. Kayit/silme sonrasi primhakedisguncel ile yeniden uretilir. --}}
@php
  $_fmt = function($v) use ($maasGor){ return $maasGor ? number_format($v,2,',','.') : '****'; };
  $isGunluk = ($mod === 'gunluk');
  $toplamMaas = array_sum(array_column($rapor,'maas'));
  $toplamPrim = array_sum(array_column($rapor,'prim_toplam'));
  $toplamBonus = array_sum(array_column($rapor,'bonus'));
  $toplamMasraf = array_sum(array_column($rapor,'masraf'));
  $toplamNet = array_sum(array_column($rapor,'net_hakedis'));
  $toplamOdenen = array_sum(array_column($rapor,'odenen_toplam'));
  $toplamBekleyen = array_sum(array_column($rapor,'kalan'));
  // Tip bazli odenen toplamlari (Maas / Prim / Avans-Diger)
  $toplamOdenenMaas  = array_sum(array_column($rapor,'odenen_maas'));
  $toplamOdenenPrim  = array_sum(array_column($rapor,'odenen_prim'));
  $toplamOdenenDiger = array_sum(array_column($rapor,'odenen_diger'));
  // Kart bazinda kalan: avans, kesinti ve personel gideri dusulmus hali
  $toplamKalanMaas = array_sum(array_column($rapor,'kalan_maas'));
  $toplamKalanPrim = array_sum(array_column($rapor,'kalan_prim'));
  $odemeYuzde = $toplamNet > 0 ? min(100, round(($toplamOdenen / $toplamNet) * 100, 1)) : 0;
  // Gunluk toplamlar (gun_* alanlari primHakedisVerisi'den gelir)
  $gunToplamPrim   = array_sum(array_column($rapor, 'gun_prim'));
  $gunToplamOdenen = array_sum(array_column($rapor, 'gun_odenen'));
  $gunToplamKalan  = array_sum(array_column($rapor, 'gun_kalan'));
  $gunOdemeYuzde   = $gunToplamPrim > 0 ? min(100, round(($gunToplamOdenen / $gunToplamPrim) * 100, 1)) : 0;
@endphp
@if($isGunluk)
<div class="pr-stats pr-stats--gunluk">
  <div class="pr-stat pr-stat--prim">
    <div class="pr-stat__icon">%</div>
    <div class="pr-stat__lbl">Günün Primi (Toplam)</div>
    <div class="pr-stat__val">{{$_fmt($gunToplamPrim)}} <small>₺</small></div>
    <div class="pr-stat__brut">{{date('d.m.Y', strtotime($gun))}} · tüm personel</div>
  </div>
  <div class="pr-stat pr-stat--bonus">
    <div class="pr-stat__icon"><i class="fa fa-check-circle"></i></div>
    <div class="pr-stat__lbl">Ödenen Prim (Bugün)</div>
    <div class="pr-stat__val">{{$_fmt($gunToplamOdenen)}} <small>₺</small></div>
    <div class="pr-stat__brut" style="opacity:.65">%{{$gunOdemeYuzde}} ödendi</div>
  </div>
  <div class="pr-stat pr-stat--net">
    <div class="pr-stat__icon"><i class="fa fa-credit-card"></i></div>
    <div class="pr-stat__lbl">Bekleyen Prim<span class="pr-net-progress__yuzde">%{{$gunOdemeYuzde}}</span></div>
    <div class="pr-stat__val">{{$_fmt($gunToplamKalan)}} <small>₺</small></div>
    <div class="pr-stat__brut">Prim: {{$_fmt($gunToplamPrim)}} ₺ &nbsp;·&nbsp; Ödenen: {{$_fmt($gunToplamOdenen)}} ₺</div>
    <div class="pr-net-progress">
      <div class="pr-net-progress__bar">
        <div class="pr-net-progress__fill" style="width: {{$gunOdemeYuzde}}%"></div>
      </div>
    </div>
  </div>
</div>
@else
<div class="pr-stats">
  <div class="pr-stat pr-stat--maas">
    <div class="pr-stat__icon">₺</div>
    <div class="pr-stat__lbl">Kalan Maaş</div>
    <div class="pr-stat__val">{{$_fmt($toplamKalanMaas)}} <small>₺</small></div>
    <div class="pr-stat__brut">Toplam: {{$_fmt($toplamMaas)}} ₺</div>
    <div class="pr-stat__sub">
      <span class="pill odenen"><i class="fa fa-check-circle"></i> Ödenen <b>{{$_fmt($toplamOdenenMaas)}} ₺</b></span>
      <span class="pill kalan"><i class="fa fa-clock-o"></i> Kalan <b>{{$_fmt($toplamKalanMaas)}} ₺</b></span>
    </div>
  </div>
  <div class="pr-stat pr-stat--prim">
    <div class="pr-stat__icon">%</div>
    <div class="pr-stat__lbl">Kalan Prim</div>
    <div class="pr-stat__val">{{$_fmt($toplamKalanPrim)}} <small>₺</small></div>
    <div class="pr-stat__brut">Toplam: {{$_fmt($toplamPrim)}} ₺</div>
    <div class="pr-stat__sub">
      <span class="pill odenen"><i class="fa fa-check-circle"></i> Ödenen <b>{{$_fmt($toplamOdenenPrim)}} ₺</b></span>
      <span class="pill kalan"><i class="fa fa-clock-o"></i> Kalan <b>{{$_fmt($toplamKalanPrim)}} ₺</b></span>
    </div>
  </div>
  <div class="pr-stat pr-stat--bonus">
    <div class="pr-stat__icon">＋</div>
    <div class="pr-stat__lbl">Toplam Bonus</div>
    <div class="pr-stat__val">{{$_fmt($toplamBonus)}} <small>₺</small></div>
    <div class="pr-stat__brut" style="opacity:.65">Net hak edişe ekleniyor</div>
  </div>
  <div class="pr-stat pr-stat--masraf">
    <div class="pr-stat__icon"><i class="fa fa-shopping-cart"></i></div>
    <div class="pr-stat__lbl">Toplam Masraf</div>
    <div class="pr-stat__val">{{$_fmt($toplamMasraf)}} <small>₺</small></div>
    <div class="pr-stat__brut" style="opacity:.65">Personel giderleri (kasadan alınan) — net hak edişten düşülür</div>
  </div>
  <div class="pr-stat pr-stat--net">
    <div class="pr-stat__icon"><i class="fa fa-credit-card"></i></div>
    <div class="pr-stat__lbl">
      Bekleyen Ödeme
      <span class="pr-net-progress__yuzde">%{{$odemeYuzde}}</span>
    </div>
    <div class="pr-stat__val">{{$_fmt($toplamBekleyen)}} <small>₺</small></div>
    <div class="pr-stat__brut">Net Hak Ediş: {{$_fmt($toplamNet)}} ₺ &nbsp;·&nbsp; Ödenen: {{$_fmt($toplamOdenen)}} ₺</div>
    <div class="pr-net-progress">
      <div class="pr-net-progress__bar">
        <div class="pr-net-progress__fill" style="width: {{$odemeYuzde}}%"></div>
      </div>
    </div>
    <div class="pr-stat__sub">
      <span class="pill"><i class="fa fa-money"></i> Maaş <b>{{$_fmt($toplamOdenenMaas)}} ₺</b></span>
      <span class="pill"><i class="fa fa-percent"></i> Prim <b>{{$_fmt($toplamOdenenPrim)}} ₺</b></span>
      <span class="pill"><i class="fa fa-clock-o"></i> Avans <b>{{$_fmt($toplamOdenenDiger)}} ₺</b></span>
    </div>
  </div>
</div>
@endif

<div class="pr-table-card">
  <div class="pr-table-toolbar">
    <h3><i class="fa fa-users"></i> @if($isGunluk)Günlük Prim — {{date('d.m.Y', strtotime($gun))}}@else Personel Bazında Hak Ediş @endif</h3>
  </div>
  <div class="pr-table-scroll" style="padding: 0 14px 6px">
    {{-- "data-table" sinifi verilmez: custom.js sekme geciste o siniftaki tablolari yikip varsayilanla yeniden kurar --}}
    <table class="table hover nowrap" id="primrapor_tablo" style="width:100%">
      <thead>
        <tr>
          <th>Personel</th>
          <th>Maaş</th>
          <th>Hizmet Primi</th>
          <th>Ürün Primi</th>
          <th>Paket Primi</th>
          <th>Prim Toplam</th>
          <th>Bonus</th>
          <th title="Personele uygulanan kesintiler (net hak edişten düşülür)">Kesinti</th>
          <th title="Telafi randevusu ile baska personele devredilen prim (ödenmişse)">Telafi Devri</th>
          <th>Masraf</th>
          <th>@if($isGunluk)Günün Primi @else NET Ödenecek @endif</th>
          <th>Durum</th>
          <th class="datatable-nosort">İşlemler</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rapor as $r)
          @php
            $bas = mb_strtoupper(mb_substr($r['personel_adi'],0,1,'UTF-8'),'UTF-8');
            // Gunluk modda durum/tutarlar gun_* alanlarindan
            $satirDurum = $isGunluk ? ($r['gun_durum'] ?? 'bekliyor') : $r['durum'];
            $rowCls = '';
            if($satirDurum==='tam' || $satirDurum==='fazla') $rowCls = 'pr-row-tam';
            elseif($satirDurum==='kismi') $rowCls = 'pr-row-kismi';
            $gPrim   = $r['gun_prim'] ?? 0;
            $gOdenen = $r['gun_odenen'] ?? 0;
            $gKalan  = $r['gun_kalan'] ?? 0;
          @endphp
          <tr class="{{ $rowCls }}">
            <td class="pr-cell-personel"><span class="pr-cell-personel-inner"><span class="pr-avatar">{{$bas}}</span><span>{{$r['personel_adi']}}</span></span></td>
            <td>{{$_fmt($r['maas'])}} ₺</td>
            <td>{{$_fmt($r['hizmet_primi'])}} ₺</td>
            <td>{{$_fmt($r['urun_primi'])}} ₺</td>
            <td>{{$_fmt($r['paket_primi'])}} ₺</td>
            <td><strong>{{$_fmt($r['prim_toplam'])}} ₺</strong></td>
            <td class="pr-cell-bonus">+{{$_fmt($r['bonus'])}}@if($r['hareket_sayisi']>0) <small style="color:var(--rmc-muted); font-weight:500">({{$r['hareket_sayisi']}})</small>@endif</td>
            <td class="pr-cell-kesinti" title="Net hak edişten düşülen kesinti toplamı">
                @php $_ks = (float) ($r['kesinti'] ?? 0); @endphp
                @if($_ks > 0)
                   <span style="color:#dc3545;font-weight:600;">−{{$_fmt($_ks)}} ₺</span>
                @else
                   <span style="color:#9ca3af;">0,00 ₺</span>
                @endif
            </td>
            <td class="pr-cell-telafi" title="Telafi randevusu ile başka personele devredilen prim (ödenmiş olduğu için gelecekten düşülecek bakiye)">
                @php $_td = (float) ($r['telafi_devri'] ?? 0); @endphp
                @if($_td < 0)
                   <span style="color:#dc3545;font-weight:600;">−{{$_fmt(abs($_td))}} ₺</span>
                @elseif($_td > 0)
                   <span style="color:#059669;font-weight:600;">+{{$_fmt($_td)}} ₺</span>
                @else
                   <span style="color:#9ca3af;">0,00 ₺</span>
                @endif
            </td>
            <td class="pr-cell-masraf" title="Personelin kasadan aldığı/harcadığı tutar — net hak edişten düşülür">@if($r['masraf']>0)−@endif{{$_fmt($r['masraf'])}} ₺</td>
            <td class="pr-cell-net">
              <strong>{{$_fmt($isGunluk ? $gPrim : $r['net_hakedis'])}} ₺</strong>
              @if(!$isGunluk && !empty($r['borclu']))<br><span class="pr-borclu-badge">Borçlu</span>@endif
            </td>
            <td>
              @if($isGunluk)
                @if($satirDurum==='bekliyor')
                  <span class="pr-durum-badge pr-durum--bekliyor"><span class="lbl"><i class="fa fa-clock-o"></i> Bekliyor</span></span>
                @elseif($satirDurum==='kismi')
                  <span class="pr-durum-badge pr-durum--kismi"><span class="lbl"><i class="fa fa-hourglass-half"></i> Kısmi</span><span class="alt">{{$_fmt($gOdenen)}} / {{$_fmt($gPrim)}} ₺</span></span>
                @elseif($satirDurum==='tam')
                  <span class="pr-durum-badge pr-durum--tam"><span class="lbl"><i class="fa fa-check-circle"></i> Ödendi</span></span>
                @else
                  <span class="pr-durum-badge pr-durum--fazla"><span class="lbl"><i class="fa fa-arrow-up"></i> Fazla</span><span class="alt">{{$_fmt($gOdenen)}} / {{$_fmt($gPrim)}} ₺</span></span>
                @endif
              @else
                @if($r['durum']==='bekliyor')
                  <span class="pr-durum-badge pr-durum--bekliyor"><span class="lbl"><i class="fa fa-clock-o"></i> Bekliyor</span></span>
                @elseif($r['durum']==='kismi')
                  <span class="pr-durum-badge pr-durum--kismi prim-odeme-detay" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Ödeme detayı">
                    <span class="lbl"><i class="fa fa-hourglass-half"></i> Kısmi Ödeme</span>
                    <span class="alt">{{$_fmt($r['odenen_toplam'])}} / {{$_fmt($r['net_hakedis'])}} ₺</span>
                  </span>
                @elseif($r['durum']==='tam')
                  <span class="pr-durum-badge pr-durum--tam prim-odeme-detay" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Ödeme detayı">
                    <span class="lbl"><i class="fa fa-check-circle"></i> Ödendi</span>
                  </span>
                @else
                  <span class="pr-durum-badge pr-durum--fazla prim-odeme-detay" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Ödeme detayı">
                    <span class="lbl"><i class="fa fa-arrow-up"></i> Fazla Ödeme</span>
                    <span class="alt">{{$_fmt($r['odenen_toplam'])}} / {{$_fmt($r['net_hakedis'])}} ₺</span>
                  </span>
                @endif
              @endif
            </td>
            <td>
              <div style="display:inline-flex; gap:6px; align-items:center">
                @yetki('personel.odeme_yap')
                @if($isGunluk)
                <button class="pr-ode-btn prim-gunluk-ode"
                  data-value="{{$r['personel_id']}}"
                  data-adi="{{$r['personel_adi']}}"
                  data-gunprim="{{$gPrim}}"
                  data-gunkalan="{{$gKalan}}"
                  @if($gKalan<=0) disabled style="opacity:.45;cursor:default" @endif
                  title="Bu günün primini öde">
                  <i class="fa fa-credit-card"></i>
                  <span>@if($gKalan<=0)Ödendi @else Öde @endif</span>
                </button>
                @else
                <button class="pr-ode-btn prim-ode"
                  data-value="{{$r['personel_id']}}"
                  data-adi="{{$r['personel_adi']}}"
                  data-net="{{$r['net_hakedis']}}"
                  data-odenen="{{$r['odenen_toplam']}}"
                  data-kalan="{{$r['kalan']}}"
                  title="Prim & Hak Ediş İşlemleri">
                  <i class="fa fa-credit-card"></i>
                  <span>Öde</span>
                </button>
                @endif
                @endyetki
                <button class="pr-musteri-btn prim-musteri-detay"
                  data-value="{{$r['personel_id']}}"
                  data-adi="{{$r['personel_adi']}}"
                  title="{{ $isGunluk ? 'Bu gün' : 'Bu ay' }} satış yaptığı müşteriler">
                  <i class="fa fa-users"></i>
                </button>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
