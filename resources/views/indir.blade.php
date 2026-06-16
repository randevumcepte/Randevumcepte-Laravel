<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $isletme->isletme_adi ?? 'Uygulama' }} — Uygulamayı İndir</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{ --mauve:#7c4a5a; --mauve-d:#5e3543; --mauve-l:#a86b7b; --ink:#3a2a31; --soft:#8c7780; }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',system-ui,sans-serif;color:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
    background:radial-gradient(120% 80% at 50% 0%, #f6e6ec 0%, #efe1e6 60%, #e7d8dd 100%)}
  .card{width:100%;max-width:420px;background:#fff;border-radius:26px;padding:34px 28px 30px;text-align:center;
    box-shadow:0 24px 60px rgba(94,53,67,.25)}
  .logo{width:88px;height:88px;border-radius:22px;margin:0 auto 18px;background:#fff;border:1px solid #f0e3e8;
    display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 10px 24px rgba(124,74,90,.18)}
  .logo img{width:100%;height:100%;object-fit:cover}
  .logo .init{font-family:'Poppins';font-weight:800;font-size:40px;
    background:linear-gradient(135deg,var(--mauve),var(--mauve-l));-webkit-background-clip:text;background-clip:text;color:transparent}
  h1{font-family:'Poppins';font-weight:800;font-size:23px;margin-bottom:6px}
  .sub{font-size:14px;color:var(--soft);margin-bottom:26px;line-height:1.6}
  .btns{display:flex;flex-direction:column;gap:12px}
  .btn{display:flex;align-items:center;justify-content:center;gap:11px;height:56px;border-radius:15px;text-decoration:none;
    background:var(--ink);color:#fff;font-weight:600;font-size:16px;box-shadow:0 10px 24px rgba(20,8,14,.22)}
  .btn svg{width:24px;height:24px;flex:0 0 24px}
  .btn small{display:block;font-size:9px;opacity:.8;font-weight:500;text-align:left;line-height:1}
  .btn b{font-size:15px;font-weight:600}
  .yok{font-size:13px;color:var(--soft);background:#faf4f6;border-radius:12px;padding:14px}
  .ft{margin-top:24px;font-size:11px;color:#b9aab0}
</style>
</head>
<body>
  <div class="card">
    <div class="logo">
      @if(!empty($isletme->logo))
        <img src="{{ secure_asset($isletme->logo) }}" alt="{{ $isletme->isletme_adi }}">
      @else
        <span class="init">{{ mb_strtoupper(mb_substr($isletme->isletme_adi ?? 'A',0,1),'UTF-8') }}</span>
      @endif
    </div>
    <h1>{{ $isletme->isletme_adi ?? 'Uygulamamız' }}</h1>
    <div class="sub">Uygulamamızı indirmek için cihazınıza uygun mağazayı seçin.</div>

    <div class="btns">
      @if(!empty(trim($isletme->ios_uygulama)))
        <a class="btn" href="{{ $isletme->ios_uygulama }}">
          <svg viewBox="0 0 24 24" fill="#fff"><path d="M16.5 12.5c0-2.1 1.7-3.1 1.8-3.2-1-1.4-2.5-1.6-3-1.6-1.3-.1-2.5.8-3.1.8-.6 0-1.6-.8-2.7-.7-1.4 0-2.6.8-3.3 2-1.4 2.4-.4 6 1 8 .7 1 1.5 2 2.5 2 1 0 1.4-.6 2.6-.6 1.2 0 1.5.6 2.6.6 1.1 0 1.8-1 2.5-2 .5-.7.7-1.1 1.1-1.9-2.8-1-2.6-3.7-2.5-3.4zM14.5 5.8c.5-.7.9-1.6.8-2.5-.8 0-1.7.5-2.3 1.2-.5.6-.9 1.5-.8 2.4.9.1 1.7-.4 2.3-1.1z"/></svg>
          <span><small>İNDİR</small><b>App Store</b></span>
        </a>
      @endif
      @if(!empty(trim($isletme->android_uygulama)))
        <a class="btn" href="{{ $isletme->android_uygulama }}">
          <svg viewBox="0 0 24 24"><path fill="#34A853" d="M4 4.5l10 7.5-2.5 1.8z"/><path fill="#FBBC04" d="M16.5 9.7L19.8 11.6c.9.5.9 1.8 0 2.3l-3.3 1.9-2.7-2z"/><path fill="#EA4335" d="M4 11.5l9.8 7.3-2.7 2z"/><path fill="#4285F4" d="M4 4.5v15l9.8-7.5z"/></svg>
          <span><small>İNDİR</small><b>Google Play</b></span>
        </a>
      @endif
      @if(!empty(trim($isletme->huawei_uygulama)))
        <a class="btn" href="{{ $isletme->huawei_uygulama }}">
          <svg viewBox="0 0 24 24" fill="#fff"><path d="M12 2C7 6 6 10 6 12c0 3.3 2.7 6 6 6s6-2.7 6-6c0-2-1-6-6-10z"/></svg>
          <span><small>İNDİR</small><b>AppGallery</b></span>
        </a>
      @endif
      @if(empty(trim($isletme->ios_uygulama)) && empty(trim($isletme->android_uygulama)) && empty(trim($isletme->huawei_uygulama)))
        <div class="yok">Uygulama mağaza bağlantıları henüz hazır değil. Lütfen daha sonra tekrar deneyin.</div>
      @endif
    </div>

    <div class="ft">Güzelliğin dijital adresi</div>
  </div>
</body>
</html>
