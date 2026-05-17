@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<style>
   .my-page-title h1 { color:#3a1a52; font-weight:700; }
   .my-summary { display:grid; grid-template-columns: 1.1fr 1fr; gap:18px; margin-bottom:22px; }
   @media (max-width: 900px){ .my-summary { grid-template-columns: 1fr; } }

   .my-card { background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(92,0,142,.06); padding:22px; border:1px solid #f0eaf6; }
   .my-card .my-card-h { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
   .my-card .my-card-h .my-ikon { width:34px; height:34px; background:#5C008E; color:#fff; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; }
   .my-card .my-card-h h4 { margin:0; font-size:15px; color:#3a1a52; font-weight:700; }

   .my-ortalama-box { display:flex; align-items:center; gap:18px; }
   .my-ortalama-no { font-size:54px; font-weight:800; color:#5C008E; line-height:1; letter-spacing:-1.5px; }
   .my-ortalama-alt { font-size:12.5px; color:#8a8295; margin-top:6px; }
   .my-stars { color:#FFB400; letter-spacing:2px; font-size:22px; }
   .my-stars .o { color:#e2dce8; }
   .my-counts { display:flex; gap:22px; margin-top:10px; }
   .my-counts .ct { font-size:13px; color:#5C008E; font-weight:600; }
   .my-counts .ct b { display:block; font-size:20px; color:#3a1a52; font-weight:800; }

   .my-bar-row { display:flex; align-items:center; gap:10px; margin-bottom:7px; font-size:12.5px; }
   .my-bar-row .lbl { width:38px; font-weight:700; color:#5C008E; display:flex; align-items:center; gap:4px; }
   .my-bar-row .lbl i { color:#FFB400; font-size:11px; }
   .my-bar-row .bar { flex:1; height:9px; background:#f1ecf6; border-radius:5px; overflow:hidden; }
   .my-bar-row .bar > span { display:block; height:100%; background:linear-gradient(90deg,#FFB400,#FF8A00); border-radius:5px; }
   .my-bar-row .ct { width:46px; text-align:right; color:#8a8295; font-weight:600; }

   .my-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:14px; }
   .my-toolbar .my-arama { flex:1 1 280px; max-width:380px; position:relative; }
   .my-toolbar .my-arama input { width:100%; border:1.5px solid #dfd6ea; border-radius:8px; padding:9px 12px 9px 36px; font-size:13.5px; }
   .my-toolbar .my-arama input:focus { outline:none; border-color:#5C008E; box-shadow:0 0 0 3px rgba(92,0,142,.1); }
   .my-toolbar .my-arama i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#a89cb5; }

   .my-filt-grup { display:flex; gap:6px; flex-wrap:wrap; }
   .my-filt-grup .filt { background:#fff; border:1.5px solid #e6dcef; color:#5C008E; padding:7px 12px; border-radius:20px; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:.15s; }
   .my-filt-grup .filt:hover { border-color:#5C008E; }
   .my-filt-grup .filt.aktif { background:#5C008E; border-color:#5C008E; color:#fff; }
   .my-filt-grup .filt i { color:#FFB400; font-size:11px; }
   .my-filt-grup .filt.aktif i { color:#FFD968; }

   .my-yorum-listesi { display:flex; flex-direction:column; gap:12px; }
   .my-yorum-kart { background:#fff; border:1px solid #efe8f5; border-radius:12px; padding:16px 18px; transition:.15s; }
   .my-yorum-kart:hover { box-shadow:0 4px 14px rgba(92,0,142,.08); border-color:#dccbe7; }
   .my-yorum-ust { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
   .my-avatar { width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#5C008E,#9b3fc5); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; flex-shrink:0; overflow:hidden; }
   .my-avatar img { width:100%; height:100%; object-fit:cover; }
   .my-yorum-meta { flex:1; min-width:0; }
   .my-yorum-meta .ad { font-size:14px; font-weight:700; color:#2d2143; display:block; }
   .my-yorum-meta .tarih { font-size:11.5px; color:#9a8fad; }
   .my-yorum-puan { display:flex; align-items:center; gap:6px; }
   .my-yorum-puan .stars-mini { color:#FFB400; letter-spacing:1.5px; font-size:14px; }
   .my-yorum-puan .stars-mini .o { color:#e2dce8; }
   .my-yorum-puan .badge-puan { background:#fff7e0; color:#a86200; font-weight:700; font-size:11.5px; padding:3px 9px; border-radius:11px; }
   .my-yorum-metin { color:#3a2e57; font-size:13.5px; line-height:1.55; margin:0; white-space:pre-wrap; word-break:break-word; }
   .my-yorum-metin.bos { color:#bcb3c9; font-style:italic; }

   .my-empty { text-align:center; padding:60px 20px; }
   .my-empty .ic { font-size:54px; color:#d8d2e0; }
   .my-empty h4 { color:#5C008E; margin-top:14px; font-weight:700; }
   .my-empty p { color:#8a8295; max-width:460px; margin:8px auto 0; font-size:13.5px; }
</style>

<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-12 my-page-title">
         <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
         <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$sayfa_baslik}}</li>
         </ol></nav>
      </div>
   </div>
</div>

<div class="my-summary">
   <div class="my-card">
      <div class="my-card-h">
         <div class="my-ikon"><i class="fa fa-star"></i></div>
         <h4>Genel Memnuniyet</h4>
      </div>
      <div class="my-ortalama-box">
         <div>
            <div class="my-ortalama-no">{{ number_format($ortalama, 1, ',', '.') }}</div>
            <div class="my-ortalama-alt">5 üzerinden</div>
         </div>
         <div style="flex:1;">
            <div class="my-stars">
               @php $tam = floor($ortalama); $yarim = ($ortalama - $tam) >= 0.5; @endphp
               @for($i=1; $i<=5; $i++)
                  @if($i <= $tam)
                     <i class="fa fa-star"></i>
                  @elseif($i == $tam+1 && $yarim)
                     <i class="fa fa-star-half-o"></i>
                  @else
                     <i class="fa fa-star-o o"></i>
                  @endif
               @endfor
            </div>
            <div class="my-counts">
               <div class="ct"><b>{{ $toplam_puan }}</b>Puan</div>
               <div class="ct"><b>{{ $toplam_yorum }}</b>Yorum</div>
            </div>
         </div>
      </div>
   </div>

   <div class="my-card">
      <div class="my-card-h">
         <div class="my-ikon" style="background:#FFB400;"><i class="fa fa-bar-chart"></i></div>
         <h4>Yıldız Dağılımı</h4>
      </div>
      @php $maxD = max(array_values($dagilim)) ?: 1; @endphp
      @for($s=5; $s>=1; $s--)
         @php $val = $dagilim[$s] ?? 0; $perc = $maxD>0 ? round(($val/$maxD)*100) : 0; @endphp
         <div class="my-bar-row">
            <div class="lbl">{{$s}}<i class="fa fa-star"></i></div>
            <div class="bar"><span style="width: {{ $perc }}%;"></span></div>
            <div class="ct">{{ $val }}</div>
         </div>
      @endfor
   </div>
</div>

<div class="my-card">
   <form method="GET" action="/isletmeyonetim/musteri-yorumlari" class="my-toolbar" id="myFilterForm">
      @if(isset($_GET['sube']))
         <input type="hidden" name="sube" value="{{ $isletme->id }}">
      @endif
      <input type="hidden" name="puan" value="{{ $puan_filtre }}" id="myPuanInput">
      <div class="my-arama">
         <i class="fa fa-search"></i>
         <input type="text" name="q" value="{{ $arama }}" placeholder="Yorumlarda ara...">
      </div>
      <div class="my-filt-grup">
         @php
            $base = '/isletmeyonetim/musteri-yorumlari?'.http_build_query(array_filter([
               'sube' => isset($_GET['sube']) ? $isletme->id : null,
               'q'    => $arama !== '' ? $arama : null,
            ]));
            $sep = (strpos($base,'?')===strlen($base)-1) ? '' : '&';
         @endphp
         <a href="{{ $base }}{{ $sep }}puan=0" class="filt {{ $puan_filtre==0 ? 'aktif' : '' }}">Tümü</a>
         @for($s=5; $s>=1; $s--)
            <a href="{{ $base }}{{ $sep }}puan={{$s}}" class="filt {{ $puan_filtre==$s ? 'aktif' : '' }}">
               {{$s}} <i class="fa fa-star"></i>
            </a>
         @endfor
      </div>
   </form>

   @if($yorumlar->count() === 0)
      <div class="my-empty">
         <div class="ic"><i class="fa fa-comments-o"></i></div>
         <h4>Henüz Yorum Yok</h4>
         <p>
            @if($arama !== '' || $puan_filtre > 0)
               Bu filtreyle eşleşen yorum bulunamadı. Filtreyi temizleyip tekrar deneyin.
            @else
               Müşterileriniz işletmenize henüz yorum bırakmamış. Müşteri uygulamasından yorum ve puan paylaşıldığında burada görüntülenecek.
            @endif
         </p>
      </div>
   @else
      <div class="my-yorum-listesi">
         @foreach($yorumlar as $y)
            @php
               $kAd = $y->kullanici ? trim($y->kullanici->name.' '.($y->kullanici->surname ?? '')) : 'Müşteri';
               $kAd = $kAd ?: 'Müşteri';
               $bashar = mb_strtoupper(mb_substr($kAd, 0, 1, 'UTF-8'), 'UTF-8');
               $kAvatar = null;
               if($y->kullanici){
                  foreach(['profil_resmi','profil_resim','avatar','resim'] as $alan){
                     if(isset($y->kullanici->$alan) && !empty($y->kullanici->$alan)){
                        $kAvatar = $y->kullanici->$alan;
                        break;
                     }
                  }
               }
            @endphp
            <div class="my-yorum-kart">
               <div class="my-yorum-ust">
                  <div class="my-avatar">
                     @if($kAvatar)
                        <img src="{{ $kAvatar }}" alt="{{ $kAd }}" onerror="this.style.display='none'; this.parentNode.innerHTML='{{ $bashar }}';">
                     @else
                        {{ $bashar }}
                     @endif
                  </div>
                  <div class="my-yorum-meta">
                     <span class="ad">{{ $kAd }}</span>
                     <span class="tarih">
                        <i class="fa fa-clock-o"></i>
                        {{ $y->tarih ? \Carbon\Carbon::parse($y->tarih)->translatedFormat('d M Y H:i') : '' }}
                     </span>
                  </div>
                  <div class="my-yorum-puan">
                     @if($y->puan > 0)
                        <div class="stars-mini">
                           @for($i=1; $i<=5; $i++)
                              @if($i <= $y->puan)
                                 <i class="fa fa-star"></i>
                              @else
                                 <i class="fa fa-star-o o"></i>
                              @endif
                           @endfor
                        </div>
                        <span class="badge-puan">{{ $y->puan }}.0</span>
                     @else
                        <span class="badge-puan" style="background:#f1ecf6; color:#9a8fad;">Puan yok</span>
                     @endif
                  </div>
               </div>
               <p class="my-yorum-metin {{ trim($y->yorum) === '' ? 'bos' : '' }}">
                  {{ trim($y->yorum) !== '' ? $y->yorum : 'Bu müşteri sadece puan verdi, yorum yazmadı.' }}
               </p>
            </div>
         @endforeach
      </div>
   @endif
</div>

<script>
   document.querySelector('#myFilterForm input[name="q"]').addEventListener('keydown', function(e){
      if(e.key === 'Enter'){
         e.preventDefault();
         document.getElementById('myPuanInput').value = {{ $puan_filtre }};
         this.form.submit();
      }
   });
</script>

@endsection
