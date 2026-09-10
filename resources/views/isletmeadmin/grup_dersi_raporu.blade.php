@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php $o = $rapor['ozet']; @endphp
<style>
   .gr-page{ padding:8px 4px; }
   .gr-head{ display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; }
   .gr-head h3{ margin:0; font-size:20px; font-weight:800; color:#5C008E; }
   .gr-filtre{ display:flex; align-items:flex-end; gap:8px; background:#F3ECFA; border:1px solid #E9DDF5; border-radius:12px; padding:10px 12px; }
   .gr-filtre label{ display:block; font-size:11px; font-weight:700; color:#5C008E; margin:0 0 4px; }
   .gr-filtre input{ height:36px; border:1px solid #cfc3e0; border-radius:8px; padding:0 8px; }
   .gr-filtre button{ height:36px; border:none; border-radius:8px; background:#5C008E; color:#fff; font-weight:700; padding:0 16px; cursor:pointer; }
   .gr-tiles{ display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
   .gr-tile{ background:#fff; border:1px solid #ECE8F4; border-radius:14px; padding:16px; box-shadow:0 1px 3px rgba(92,0,142,.05); }
   .gr-tile .v{ font-size:26px; font-weight:800; color:#5C008E; line-height:1; }
   .gr-tile .l{ font-size:12.5px; color:#7f8c8d; margin-top:6px; }
   .gr-tile.accent{ background:linear-gradient(135deg,#5C008E,#7B2FB8); }
   .gr-tile.accent .v, .gr-tile.accent .l{ color:#fff; }
   .gr-card{ background:#fff; border:1px solid #ECE8F4; border-radius:14px; padding:14px; margin-bottom:16px; }
   .gr-card h4{ margin:0 0 10px; font-size:15px; color:#5C008E; }
   table.gr-tbl{ width:100%; border-collapse:collapse; font-size:13px; }
   table.gr-tbl th{ text-align:left; color:#7f8c8d; font-weight:700; padding:8px 6px; border-bottom:2px solid #F3ECFA; font-size:12px; }
   table.gr-tbl td{ padding:9px 6px; border-bottom:1px solid #F4F1F8; }
   .gr-bar{ height:8px; background:#F3ECFA; border-radius:999px; overflow:hidden; min-width:70px; }
   .gr-bar > span{ display:block; height:100%; background:linear-gradient(90deg,#7B2FB8,#5C008E); }
   .gr-bos{ color:#95a5a6; text-align:center; padding:20px; }
</style>

<div class="gr-page">
   <div class="gr-head">
      <div>
         <h3>📊 Grup Dersi Raporu</h3>
         <small style="color:#7f8c8d;">Doluluk, katılım ve no-show metrikleri. Tarih aralığı seçin.</small>
      </div>
      <form method="GET" class="gr-filtre">
         @if(isset($_GET['sube'])) <input type="hidden" name="sube" value="{{ $isletme->id }}"> @endif
         <div><label>Başlangıç</label><input type="date" name="tarih1" value="{{ $tarih1 }}"></div>
         <div><label>Bitiş</label><input type="date" name="tarih2" value="{{ $tarih2 }}"></div>
         <button type="submit">Getir</button>
      </form>
   </div>

   <div class="gr-tiles">
      <div class="gr-tile accent"><div class="v">%{{ $o['doluluk'] }}</div><div class="l">Ortalama Doluluk</div></div>
      <div class="gr-tile"><div class="v">{{ $o['oturum'] }}</div><div class="l">Toplam Ders</div></div>
      <div class="gr-tile"><div class="v">{{ $o['katilim'] }}</div><div class="l">Toplam Katılım (kayıt)</div></div>
      <div class="gr-tile"><div class="v">{{ $o['gelen'] }}</div><div class="l">Gelen</div></div>
      <div class="gr-tile"><div class="v">{{ $o['gelmedi'] }}</div><div class="l">Gelmedi (no-show %{{ $o['noshow'] }})</div></div>
      <div class="gr-tile"><div class="v">{{ $o['bekleme'] }}</div><div class="l">Bekleme Listesi</div></div>
   </div>

   <div class="gr-card">
      <h4>Eğitmen Bazlı</h4>
      @if(count($rapor['egitmen']) === 0)
         <div class="gr-bos">Bu aralıkta ders yok.</div>
      @else
      <table class="gr-tbl">
         <thead><tr><th>Eğitmen</th><th>Ders</th><th>Katılım</th><th>Gelen</th><th>Gelmedi</th><th>Doluluk</th></tr></thead>
         <tbody>
         @foreach($rapor['egitmen'] as $e)
            <tr>
               <td><strong>{{ $e['personel'] }}</strong></td>
               <td>{{ $e['oturum'] }}</td>
               <td>{{ $e['katilim'] }}</td>
               <td>{{ $e['gelen'] }}</td>
               <td>{{ $e['gelmedi'] }}</td>
               <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                     <div class="gr-bar"><span style="width:{{ $e['doluluk'] }}%"></span></div>
                     <span style="font-weight:700;color:#5C008E;">%{{ $e['doluluk'] }}</span>
                  </div>
               </td>
            </tr>
         @endforeach
         </tbody>
      </table>
      @endif
   </div>

   <div class="gr-card">
      <h4>Ders Bazlı</h4>
      @if(count($rapor['ders']) === 0)
         <div class="gr-bos">Bu aralıkta ders yok.</div>
      @else
      <table class="gr-tbl">
         <thead><tr><th>Ders</th><th>Oturum</th><th>Katılım</th><th>Gelen</th><th>Doluluk</th></tr></thead>
         <tbody>
         @foreach($rapor['ders'] as $d)
            <tr>
               <td><strong>{{ $d['ders_tipi'] }}</strong></td>
               <td>{{ $d['oturum'] }}</td>
               <td>{{ $d['katilim'] }}</td>
               <td>{{ $d['gelen'] }}</td>
               <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                     <div class="gr-bar"><span style="width:{{ $d['doluluk'] }}%"></span></div>
                     <span style="font-weight:700;color:#5C008E;">%{{ $d['doluluk'] }}</span>
                  </div>
               </td>
            </tr>
         @endforeach
         </tbody>
      </table>
      @endif
   </div>
</div>
@endsection
