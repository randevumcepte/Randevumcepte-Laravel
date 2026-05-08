<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <title>Şube Seçimi | RandevumCepte</title>
   <link rel="apple-touch-icon" sizes="180x180" href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}" />
   <link rel="icon" type="image/png" sizes="32x32" href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}" />
   <link rel="icon" type="image/png" sizes="16x16" href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}" />
   <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
   <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/fontawesome.css')}}">

   <style>
      :root {
         --rmc-purple: #5C008E;
         --rmc-purple-2: #7B2FB8;
         --rmc-purple-3: #9D5DC8;
         --rmc-purple-soft: #faf5ff;
         --rmc-border: #ece6f3;
         --rmc-text: #3a2e57;
         --rmc-muted: #7c6c8a;
      }
      * { box-sizing: border-box; }
      html, body { margin: 0; padding: 0; min-height: 100vh; font-family: 'Inter', -apple-system, sans-serif; color: var(--rmc-text); }
      body {
         background: linear-gradient(135deg, #2d1547 0%, #5C008E 50%, #7B2FB8 100%);
         display: flex; align-items: center; justify-content: center; padding: 16px;
      }

      /* Animated star pattern bg overlay */
      body::before {
         content: ""; position: fixed; inset: 0; pointer-events: none;
         background-image:
            radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,.6), transparent),
            radial-gradient(1.5px 1.5px at 60% 70%, rgba(255,255,255,.4), transparent),
            radial-gradient(2px 2px at 80% 20%, rgba(255,255,255,.5), transparent),
            radial-gradient(1.5px 1.5px at 30% 80%, rgba(255,255,255,.45), transparent),
            radial-gradient(2px 2px at 90% 60%, rgba(255,255,255,.35), transparent);
         background-size: 100% 100%;
         opacity: .65;
         animation: twinkle 6s ease-in-out infinite alternate;
         z-index: 0;
      }
      @keyframes twinkle { from { opacity: .35; } to { opacity: .8; } }

      .iss-card {
         position: relative; z-index: 2;
         width: 100%; max-width: 760px;
         background: #fff; border-radius: 22px;
         box-shadow: 0 30px 80px rgba(0,0,0,.35), 0 8px 24px rgba(92,0,142,.25);
         overflow: hidden;
         display: flex; flex-direction: column;
         max-height: calc(100vh - 32px);
      }
      .iss-head {
         padding: 22px 28px 16px;
         text-align: center;
         border-bottom: 1px solid var(--rmc-border);
         background: linear-gradient(180deg, var(--rmc-purple-soft) 0%, #fff 100%);
      }
      .iss-logo { display: inline-block; margin-bottom: 10px; }
      .iss-logo img { height: 56px; width: auto; }
      .iss-title { margin: 0; font-size: 20px; font-weight: 700; color: var(--rmc-purple); letter-spacing: -.3px; }
      .iss-sub { margin: 4px 0 0; font-size: 13px; color: var(--rmc-muted); }

      .iss-search {
         margin: 14px 28px 0;
         position: relative;
      }
      .iss-search input {
         width: 100%; padding: 11px 14px 11px 40px;
         border: 1.5px solid var(--rmc-border); border-radius: 11px;
         font-size: 14px; color: var(--rmc-text);
         transition: border-color .15s, box-shadow .15s;
         outline: 0;
         background: #fbfafd;
      }
      .iss-search input:focus { border-color: var(--rmc-purple); box-shadow: 0 0 0 3px rgba(92,0,142,.12); background:#fff; }
      .iss-search i {
         position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
         color: var(--rmc-muted); font-size: 14px;
      }
      .iss-count {
         margin: 10px 28px 0;
         font-size: 12px; color: var(--rmc-muted); font-weight: 500;
      }
      .iss-count b { color: var(--rmc-purple); }

      .iss-list {
         padding: 12px 22px 8px;
         overflow-y: auto;
         display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
         max-height: 50vh;
      }
      .iss-list::-webkit-scrollbar { width: 8px; }
      .iss-list::-webkit-scrollbar-track { background: transparent; }
      .iss-list::-webkit-scrollbar-thumb { background: #ddd1ec; border-radius: 4px; }
      .iss-list::-webkit-scrollbar-thumb:hover { background: var(--rmc-purple-3); }

      .iss-item {
         display: flex; align-items: center; gap: 12px;
         padding: 11px 13px;
         border: 1.5px solid var(--rmc-border); border-radius: 12px;
         background: #fff; cursor: pointer;
         transition: transform .12s, box-shadow .15s, border-color .15s, background .15s;
         text-decoration: none; color: var(--rmc-text);
         min-height: 60px;
      }
      .iss-item:hover {
         border-color: var(--rmc-purple);
         background: var(--rmc-purple-soft);
         transform: translateY(-1px);
         box-shadow: 0 8px 18px rgba(92,0,142,.14);
      }
      .iss-item:hover .iss-item__name { color: var(--rmc-purple); }
      .iss-avatar {
         flex: 0 0 38px; width: 38px; height: 38px; border-radius: 10px;
         background: linear-gradient(135deg, var(--rmc-purple) 0%, var(--rmc-purple-3) 100%);
         display: flex; align-items: center; justify-content: center;
         color: #fff; font-weight: 700; font-size: 15px;
         overflow: hidden;
      }
      .iss-avatar img { width: 100%; height: 100%; object-fit: cover; }
      .iss-item__body { flex: 1 1 auto; min-width: 0; }
      .iss-item__name {
         font-size: 14px; font-weight: 600; color: var(--rmc-text);
         line-height: 1.3;
         display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
         overflow: hidden; word-break: break-word;
      }
      .iss-item__sub { font-size: 11.5px; color: var(--rmc-muted); margin-top: 2px; }
      .iss-item__arrow {
         flex: 0 0 auto; color: var(--rmc-purple-3); opacity: 0; transition: opacity .15s, transform .15s;
         font-size: 14px;
      }
      .iss-item:hover .iss-item__arrow { opacity: 1; transform: translateX(2px); }

      .iss-empty {
         grid-column: 1 / -1;
         text-align: center; padding: 30px 16px; color: var(--rmc-muted);
         font-size: 13px;
      }
      .iss-empty i { font-size: 36px; opacity: .35; display: block; margin-bottom: 8px; }

      .iss-footer {
         padding: 14px 28px 18px;
         border-top: 1px solid var(--rmc-border);
         background: #fbfafd;
         display: flex; align-items: center; justify-content: space-between; gap: 10px;
      }
      .iss-footer__hint { font-size: 12px; color: var(--rmc-muted); }
      .iss-logout {
         padding: 8px 16px; border-radius: 9px;
         background: #fff; color: var(--rmc-muted) !important; text-decoration: none !important;
         font-weight: 600; font-size: 13px;
         border: 1.5px solid var(--rmc-border); transition: all .15s;
      }
      .iss-logout:hover { color: #c81e1e !important; border-color: #fca5a5; background: #fef2f2; }

      @media (max-width: 600px) {
         .iss-list { grid-template-columns: 1fr; padding: 10px 14px; gap: 8px; max-height: 55vh; }
         .iss-head { padding: 18px 18px 12px; }
         .iss-search { margin: 12px 16px 0; }
         .iss-count { margin: 10px 16px 0; }
         .iss-footer { padding: 12px 18px; flex-direction: column; align-items: stretch; }
         .iss-logout { text-align: center; }
         .iss-title { font-size: 18px; }
         .iss-card { max-height: calc(100vh - 24px); }
      }
   </style>
</head>
<body>

<div class="iss-card">
   <div class="iss-head">
      <div class="iss-logo">
         <img src="{{secure_asset('public/yeni_panel/vendors/images/randevumcepte.png')}}" alt="RandevumCepte">
      </div>
      <h1 class="iss-title">İşletme Seçiniz</h1>
      <p class="iss-sub">Yönetmek istediğiniz işletmeyi seçin, hemen panele giriş yapın.</p>
   </div>

   @php $_subeler = \App\Salonlar::whereIn('id', $isletmeler)->orderBy('salon_adi')->get(); @endphp

   <div class="iss-search">
      <i class="fa fa-search"></i>
      <input type="text" id="issAra" placeholder="İşletme ara..." autocomplete="off">
   </div>
   <div class="iss-count">Toplam <b id="issToplam">{{ $_subeler->count() }}</b> işletme</div>

   <div class="iss-list" id="issListe">
      @forelse($_subeler as $sube)
         @php
            $_baseUrl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $_sep = (strpos($_baseUrl, '?') !== false) ? '&' : '?';
            $_link = $_baseUrl.$_sep.'sube='.$sube->id;
            $_initial = mb_strtoupper(mb_substr($sube->salon_adi, 0, 1));
         @endphp
         <a href="{{ $_link }}" class="iss-item" title="{{ $sube->salon_adi }}">
            <div class="iss-avatar">
               @if(!empty($sube->logo))
                  <img src="/{{ ltrim($sube->logo, '/') }}" alt="{{ $sube->salon_adi }}" onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'{{ $_initial }}'}));">
               @else
                  <span>{{ $_initial }}</span>
               @endif
            </div>
            <div class="iss-item__body">
               <div class="iss-item__name">{{ $sube->salon_adi }}</div>
               @if(!empty($sube->ilce) || !empty($sube->il))
                  <div class="iss-item__sub">
                     {{ $sube->ilce->ilce_adi ?? '' }}{{ ($sube->ilce && $sube->il) ? ' · ' : '' }}{{ $sube->il->il_adi ?? '' }}
                  </div>
               @endif
            </div>
            <i class="fa fa-arrow-right iss-item__arrow"></i>
         </a>
      @empty
         <div class="iss-empty">
            <i class="fa fa-inbox"></i>
            <div>Henüz tanımlı işletmeniz yok.</div>
         </div>
      @endforelse
   </div>

   <div class="iss-footer">
      <span class="iss-footer__hint"><i class="fa fa-info-circle" style="color:var(--rmc-purple-3);"></i> Aramak için yukarıdaki kutuyu kullanabilirsiniz.</span>
      <a href="/isletmeyonetim/cikisyap" class="iss-logout"><i class="fa fa-sign-out"></i> Çıkış Yap</a>
   </div>
</div>

<script>
   (function(){
      var ara = document.getElementById('issAra');
      var liste = document.getElementById('issListe');
      var sayac = document.getElementById('issToplam');
      if (!ara || !liste) return;
      var items = Array.prototype.slice.call(liste.querySelectorAll('.iss-item'));
      function normalize(s) {
         return (s || '').toLocaleLowerCase('tr-TR')
            .replace(/ı/g,'i').replace(/ğ/g,'g').replace(/ü/g,'u')
            .replace(/ş/g,'s').replace(/ö/g,'o').replace(/ç/g,'c');
      }
      ara.addEventListener('input', function(){
         var q = normalize(ara.value.trim());
         var sayilan = 0;
         items.forEach(function(el){
            var t = normalize(el.getAttribute('title') || el.textContent);
            var goster = !q || t.indexOf(q) >= 0;
            el.style.display = goster ? '' : 'none';
            if (goster) sayilan++;
         });
         if (sayac) sayac.textContent = sayilan;
      });
      // İlk inputa fokus (mobilde klavye açmamak için sadece desktop)
      if (window.matchMedia('(min-width: 700px)').matches) {
         setTimeout(function(){ ara.focus(); }, 200);
      }
   })();
</script>
</body>
</html>
