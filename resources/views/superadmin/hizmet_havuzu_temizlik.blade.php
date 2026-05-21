@extends('layout.layout_sistemadmin')
@section('content')

<style>
   .hht-wrap { padding: 18px 22px 60px; }
   .hht-baslik { font-size: 22px; font-weight: 700; color: #1f2937; margin: 0 0 4px; }
   .hht-altyazi { color: #6b7280; font-size: 13px; margin-bottom: 22px; }

   .hht-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 22px; }
   .hht-kart {
      background: #fff; border-radius: 14px; padding: 16px 18px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
      border: 1px solid #f1f3f7;
   }
   .hht-kart .etiket { font-size: 11px; color: #9aa3b2; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
   .hht-kart .deger { font-size: 26px; font-weight: 700; color: #111827; margin-top: 4px; line-height: 1.1; }
   .hht-kart .alt { font-size: 11px; color: #6b7280; margin-top: 4px; }
   .hht-kart.warn  { border-left: 4px solid #f59e0b; }
   .hht-kart.bad   { border-left: 4px solid #ef4444; }
   .hht-kart.ok    { border-left: 4px solid #10b981; }
   .hht-kart.info  { border-left: 4px solid #6366f1; }

   .hht-uyari {
      background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412;
      padding: 14px 18px; border-radius: 12px; margin-bottom: 22px;
      display: flex; align-items: center; gap: 14px;
   }
   .hht-uyari .text { flex: 1; }
   .hht-uyari .baslik { font-weight: 700; font-size: 14px; }
   .hht-uyari .aciklama { font-size: 12.5px; color: #7c2d12; margin-top: 2px; }
   .hht-btn {
      background: #f59e0b; color: #fff; border: none; padding: 8px 16px;
      border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;
      transition: background 0.15s;
   }
   .hht-btn:hover { background: #d97706; }
   .hht-btn:disabled { opacity: 0.6; cursor: not-allowed; }

   .hht-bolum {
      background: #fff; border-radius: 14px; padding: 18px 22px; margin-bottom: 18px;
      border: 1px solid #f1f3f7;
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
   }
   .hht-bolum-baslik { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 6px; }
   .hht-bolum-alt { font-size: 12.5px; color: #6b7280; margin-bottom: 14px; }

   .hht-grup {
      border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px;
      overflow: hidden;
   }
   .hht-grup-head {
      background: #f9fafb; padding: 10px 14px; cursor: pointer;
      display: flex; align-items: center; gap: 10px; font-size: 13px;
   }
   .hht-grup-head:hover { background: #f3f4f6; }
   .hht-grup-head .ad { font-weight: 600; color: #111827; flex: 1; }
   .hht-grup-head .badge-sayi {
      background: #ef4444; color: #fff; padding: 2px 9px; border-radius: 999px;
      font-size: 11px; font-weight: 700;
   }
   .hht-grup-body { padding: 0; display: none; }
   .hht-grup.acik .hht-grup-body { display: block; }
   .hht-grup table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
   .hht-grup table th { background: #fafbfc; padding: 8px 12px; text-align: left; color: #6b7280; font-weight: 600; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
   .hht-grup table td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; color: #111827; }
   .hht-grup table tr:last-child td { border-bottom: none; }
   .hht-grup table .ana-satir td { background: #f0fdf4; }
   .hht-grup table .ana-satir td:first-child::before {
      content: "ANA ADAY"; background: #10b981; color: #fff; padding: 2px 6px;
      border-radius: 6px; font-size: 9px; font-weight: 700; margin-right: 6px;
   }
   .hht-ozel-rozet {
      background: #ede9fe; color: #6d28d9; padding: 1px 7px; border-radius: 6px;
      font-size: 10px; font-weight: 700;
   }

   .hht-kat-liste { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px; }
   .hht-kat-item {
      background: #f9fafb; padding: 10px 14px; border-radius: 10px;
      display: flex; justify-content: space-between; align-items: center;
      font-size: 13px;
   }
   .hht-kat-item .ad { color: #374151; }
   .hht-kat-item .sayi { font-weight: 700; color: #6366f1; }

   .hht-bos {
      text-align: center; padding: 32px 16px; color: #9ca3af; font-size: 13px;
   }
   .hht-yardim {
      background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px;
      border-radius: 8px; font-size: 12.5px; color: #1e3a8a; margin-bottom: 18px;
   }
   .hht-yardim b { color: #1e40af; }
</style>

<div class="hht-wrap">
   <h2 class="hht-baslik">Hizmet Havuzu Temizlik</h2>
   <p class="hht-altyazi">Sistem havuzundaki hizmetlerin sağlık durumu — duplicate, sahipsiz ve kategori uyumsuzluğu raporu.</p>

   {{-- Ozet kartlar --}}
   <div class="hht-grid">
      <div class="hht-kart info">
         <div class="etiket">Toplam Hizmet</div>
         <div class="deger">{{ number_format($toplam, 0, ',', '.') }}</div>
         <div class="alt">silinmiş dahil</div>
      </div>
      <div class="hht-kart ok">
         <div class="etiket">Aktif</div>
         <div class="deger">{{ number_format($aktif, 0, ',', '.') }}</div>
         <div class="alt">havuzda görünür</div>
      </div>
      <div class="hht-kart">
         <div class="etiket">Soft-deleted</div>
         <div class="deger">{{ number_format($silinmis, 0, ',', '.') }}</div>
         <div class="alt">geçmiş referansları korumak için saklanıyor</div>
      </div>
      <div class="hht-kart {{ $duplicateGrupSayisi > 0 ? 'bad' : 'ok' }}">
         <div class="etiket">Duplicate Grup</div>
         <div class="deger">{{ number_format($duplicateGrupSayisi, 0, ',', '.') }}</div>
         <div class="alt">{{ $duplicateToplam }} kayıt birleştirilebilir</div>
      </div>
      <div class="hht-kart {{ $sahipsizSayisi > 0 ? 'warn' : 'ok' }}">
         <div class="etiket">Sahipsiz</div>
         <div class="deger">{{ number_format($sahipsizSayisi, 0, ',', '.') }}</div>
         <div class="alt">hiçbir kayıt referans vermiyor</div>
      </div>
      <div class="hht-kart info">
         <div class="etiket">Kategoriler</div>
         <div class="deger">{{ $kategoriDagilim->count() }}</div>
         <div class="alt">aktif kategori sayısı</div>
      </div>
   </div>

   {{-- Normalize uyarisi --}}
   @if($normalizeEdilmemis > 0)
   <div class="hht-uyari">
      <div class="text">
         <div class="baslik">⚠ Önce normalize edilmesi gereken {{ $normalizeEdilmemis }} hizmet var</div>
         <div class="aciklama">Duplicate tespiti için tüm hizmet adlarının Türkçe karakter + boşluk + büyük/küçük harf bağımsız hâlinde indekslenmesi gerek. Bu işlem veriyi değiştirmez, sadece arama için kanonik form üretir.</div>
      </div>
      <button class="hht-btn" id="normalize_btn">Normalize Et</button>
   </div>
   @endif

   {{-- Aciklama / yardim --}}
   <div class="hht-yardim">
      <b>Bu sayfa sadece okuma yapar.</b> Hiçbir hizmet silinmez veya değiştirilmez.
      Duplicate adayları, normalize edilmiş ada göre eşleşen kayıtlardır — gerçekten aynı hizmet olduklarını manuel doğrulamalısın.
      "ANA ADAY" en çok kullanılan kayıttır; birleştirme aracında varsayılan hedef olur.
   </div>

   {{-- Duplicate gruplari --}}
   <div class="hht-bolum">
      <h3 class="hht-bolum-baslik">🔁 Duplicate Aday Grupları</h3>
      <p class="hht-bolum-alt">İsim normalize edildiğinde aynı kanonik forma denk gelen hizmetler. Yalnızca havuz hizmetleri (salon_id boş) taranır — salonların kendi özel hizmetleri sahte duble saymaması için hariç tutulur. İlk 200 grup gösterilir.</p>

      @if($normalizeEdilmemis > 0)
         <div class="hht-bos">Önce yukarıdaki "Normalize Et" butonuna bas — sonra duplicate grupları burada görünecek.</div>
      @elseif(empty($duplicateDetay))
         <div class="hht-bos">🎉 Duplicate yok — havuz bu açıdan temiz.</div>
      @else
         @foreach($duplicateDetay as $g)
         <div class="hht-grup">
            <div class="hht-grup-head" onclick="this.parentElement.classList.toggle('acik')">
               <span class="ad">"{{ $g['satirlar'][0]['hizmet_adi'] ?? $g['normalized_ad'] }}"</span>
               <span class="badge-sayi">{{ $g['adet'] }} kayıt</span>
               <span style="color:#9ca3af;font-size:11px;">{{ collect($g['satirlar'])->sum('kullanim') }} toplam kullanım ▾</span>
            </div>
            <div class="hht-grup-body">
               <table>
                  <thead>
                     <tr>
                        <th style="width:60px;">ID</th>
                        <th>Hizmet Adı</th>
                        <th>Kategori</th>
                        <th style="width:100px;">Kullanım</th>
                        <th style="width:80px;">Tip</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($g['satirlar'] as $i => $s)
                     <tr class="{{ $i === 0 ? 'ana-satir' : '' }}">
                        <td>#{{ $s['id'] }}</td>
                        <td>{{ $s['hizmet_adi'] }}</td>
                        <td>{{ $s['kategori_adi'] ?? '—' }}</td>
                        <td>{{ $s['kullanim'] }} kayıt</td>
                        <td>
                           @if($s['ozel_hizmet'])
                              <span class="hht-ozel-rozet">salon özel</span>
                           @else
                              <span style="color:#9ca3af;font-size:11px;">sistem</span>
                           @endif
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
         @endforeach
      @endif
   </div>

   {{-- Kategori dagilimi --}}
   <div class="hht-bolum">
      <h3 class="hht-bolum-baslik">📂 Kategori Dağılımı</h3>
      <p class="hht-bolum-alt">Her kategorideki aktif hizmet sayısı. Az kayıtlı kategoriler birleştirilebilir veya silinebilir adaylar.</p>
      @if($kategoriDagilim->isEmpty())
         <div class="hht-bos">Henüz kategori yok.</div>
      @else
         <div class="hht-kat-liste">
            @foreach($kategoriDagilim as $kat)
            <div class="hht-kat-item">
               <span class="ad">{{ $kat->kategori_adi ?? '— Kategorisiz —' }}</span>
               <span class="sayi">{{ $kat->adet }}</span>
            </div>
            @endforeach
         </div>
      @endif
   </div>

   {{-- Sonraki adimlar --}}
   <div class="hht-yardim">
      <b>Sonraki adımlar:</b>
      Bu rapora göre yapılacaklar — (1) duplicate gruplar manuel onayla birleştirilecek,
      (2) yanlış kategorideki hizmetler doğru kategoriye taşınacak,
      (3) sahipsiz hizmetler soft-delete edilecek,
      (4) tüm aktif hizmetlere sektör (salon_turu_id) atanacak.
      Birleştirme/taşıma aracı bir sonraki adımda eklenecek.
   </div>
</div>

<script>
$(function() {
   $('#normalize_btn').on('click', function() {
      var btn = $(this);
      btn.prop('disabled', true).text('Normalize ediliyor...');
      $.post('{{ url('/sistemyonetim/hizmet-havuzu-normalize') }}', { _token: '{{ csrf_token() }}' })
         .done(function(r) {
            if (r && r.basarili) {
               btn.text(r.islenen + ' hizmet normalize edildi — sayfa yenileniyor...');
               setTimeout(function(){ location.reload(); }, 800);
            } else {
               btn.prop('disabled', false).text('Tekrar Dene');
               alert('Normalize başarısız.');
            }
         })
         .fail(function(){
            btn.prop('disabled', false).text('Tekrar Dene');
            alert('Sunucuya ulaşılamadı.');
         });
   });
});
</script>

@endsection
