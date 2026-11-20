<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $r->baslik }} · {{ $salon->salon_adi ?? '' }}</title>
<meta name="robots" content="noindex">
<meta property="og:title" content="{{ $r->baslik }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $r->metin)), 150) }}">
<meta property="og:site_name" content="{{ $salon->salon_adi ?? '' }}">
<meta property="og:url" content="{{ $link }}">
@if($afisTam)<meta property="og:image" content="{{ $afisTam }}">@endif
<style>
   :root { --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC9; --zemin:#f6f3fb; --yazi:#1f1530; --soluk:#6b6080; }
   * { box-sizing:border-box; }
   body { margin:0; background:var(--zemin); color:var(--yazi); font-family:-apple-system,"Segoe UI",Roboto,Arial,sans-serif; }
   .k { max-width:520px; margin:0 auto; padding:16px 16px 40px; }
   .ust { display:flex; align-items:center; gap:10px; margin:6px 2px 14px; }
   .logo { width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,var(--mor1),var(--mor3)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; }
   .ust b { font-size:16px; }
   .kart { background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 10px 30px rgba(92,0,142,.10); }
   .kart img { display:block; width:100%; height:auto; }
   .ic { padding:18px 18px 20px; }
   h1 { font-size:22px; line-height:1.25; margin:0 0 10px; }
   .metin { font-size:15.5px; line-height:1.55; color:#3a2f4d; white-space:pre-line; }
   .son { display:inline-block; margin-top:14px; padding:6px 12px; border-radius:999px; background:#f3e8ff; color:var(--mor1); font-size:13px; font-weight:700; }
   .btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; margin-top:18px; padding:15px; border-radius:14px; border:none;
          background:#16a34a; color:#fff; font-size:16px; font-weight:700; text-decoration:none; }
   .bitti { margin-top:16px; padding:12px 14px; border-radius:12px; background:#fef3c7; color:#92400e; font-size:14px; }
   .alt { margin-top:16px; text-align:center; color:var(--soluk); font-size:12.5px; line-height:1.5; }
</style>
</head>
<body>
<div class="k">
   <div class="ust">
      <div class="logo">{{ mb_strtoupper(mb_substr(trim($salon->salon_adi ?? 'S'), 0, 1)) }}</div>
      <b>{{ $salon->salon_adi ?? '' }}</b>
   </div>
   <div class="kart">
      @if($afis)<img src="{{ $afis }}" alt="{{ $r->baslik }}">@endif
      <div class="ic">
         <h1>{{ $r->baslik }}</h1>
         @if(trim((string) $r->metin) !== '')<div class="metin">{{ $r->metin }}</div>@endif
         @if($bitti)
            <div class="bitti">Bu kampanya sona erdi. Güncel kampanyalar ve randevu için bize WhatsApp'tan yazabilirsiniz.</div>
         @elseif($r->bitis)
            @php($ts = strtotime($r->bitis))
            <span class="son">Son gün: {{ date('j', $ts) }} {{ ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'][(int) date('n', $ts)] }} {{ date('Y', $ts) }}</span>
         @endif
         @if($waLink)
            <a class="btn" href="{{ $waLink }}">
               <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3Z"/></svg>
               WhatsApp'tan randevu al
            </a>
         @endif
      </div>
   </div>
   <div class="alt">{{ $salon->adres ?? '' }}</div>
</div>
</body>
</html>
