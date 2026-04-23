<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Çarkıfelek') | randevumcepte.com.tr</title>
    <meta name="robots" content="noindex, follow">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin:0; padding:0;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg,#ede9fe 0%,#fce7f3 100%);
            min-height:100vh;
            color:#2d3436;
        }
        a { color: inherit; }

        .ck-nav {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(108,92,231,.15);
            padding: 12px 22px;
            display:flex; align-items:center; gap:16px;
            position:sticky; top:0; z-index:100;
        }
        .ck-nav-brand { font-weight:800; color:#6c5ce7; text-decoration:none; font-size:16px; letter-spacing:-.3px; }
        .ck-nav-brand:hover { text-decoration:none; }
        .ck-nav-links { margin-left:auto; display:flex; gap:14px; font-size:13px; font-weight:600; }
        .ck-nav-links a {
            text-decoration:none; color:#636e72; padding:7px 14px; border-radius:50px; transition:.2s;
        }
        .ck-nav-links a:hover { background: rgba(108,92,231,.1); color:#6c5ce7; text-decoration:none; }

        @media (max-width:520px) {
            .ck-nav { padding: 10px 14px; }
            .ck-nav-links { gap: 6px; }
            .ck-nav-links a { padding: 6px 10px; font-size: 12px; }
        }
    </style>
    @yield('head')
</head>
<body>
    <nav class="ck-nav">
        <a href="/" class="ck-nav-brand">🎡 randevumcepte</a>
        <div class="ck-nav-links">
            @if(Auth::check())
                <a href="{{ route('cark.odullerim') }}">🎁 Kuponlarım</a>
                <a href="{{ route('cark.puanodullerim') }}">⭐ Puanlarım</a>
            @else
                <a href="/login">Giriş</a>
            @endif
        </div>
    </nav>

    @yield('content')
</body>
</html>
