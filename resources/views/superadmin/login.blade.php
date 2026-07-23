<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Yönetim Paneli · Randevumcepte</title>
    <style>
        :root{
            --brand1:#5C008E; --brand2:#7B2FB8; --brand3:#9D5DC8;
            --ink:#1e1b2e; --muted:#6b6880; --line:#ece9f3; --danger:#e5484d;
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            color:var(--ink);
            background:
                radial-gradient(1000px 600px at 15% -10%, rgba(157,93,200,.55), transparent 60%),
                radial-gradient(900px 600px at 110% 110%, rgba(92,0,142,.55), transparent 55%),
                linear-gradient(135deg, var(--brand1) 0%, var(--brand2) 55%, var(--brand3) 100%);
            display:flex; align-items:center; justify-content:center; padding:24px;
        }
        .card{
            width:100%; max-width:410px; background:#fff; border-radius:22px;
            box-shadow:0 30px 80px rgba(35,0,60,.35); padding:38px 34px 30px; position:relative;
        }
        .brand{ text-align:center; margin-bottom:26px }
        .logo{
            width:64px; height:64px; border-radius:18px; margin:0 auto 14px;
            background:linear-gradient(135deg,var(--brand1),var(--brand3));
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-weight:800; font-size:26px; letter-spacing:.5px;
            box-shadow:0 10px 24px rgba(123,47,184,.4);
        }
        .brand h1{ margin:0; font-size:22px; font-weight:800; letter-spacing:-.3px }
        .brand p{ margin:4px 0 0; font-size:13px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:1px }

        .alert{
            background:#fdecec; color:#b4252a; border:1px solid #f6cfd0;
            border-radius:12px; padding:11px 14px; font-size:13.5px; margin-bottom:18px; line-height:1.4;
        }
        .field{ margin-bottom:16px }
        .field label{ display:block; font-size:12.5px; font-weight:700; color:var(--muted); margin-bottom:7px; letter-spacing:.3px }
        .field input[type=email], .field input[type=password], .field input[type=text]{
            width:100%; height:50px; border:1.5px solid var(--line); border-radius:13px;
            padding:0 16px; font-size:15px; color:var(--ink); background:#faf9fc; transition:.15s;
            outline:none;
        }
        .field input:focus{ border-color:var(--brand2); background:#fff; box-shadow:0 0 0 4px rgba(123,47,184,.12) }
        .field.has-error input{ border-color:var(--danger); background:#fff7f7 }
        .field .err{ color:var(--danger); font-size:12.5px; margin-top:6px; font-weight:600 }

        .row{ display:flex; align-items:center; justify-content:space-between; margin:6px 0 22px }
        .remember{ display:inline-flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted); cursor:pointer; user-select:none }
        .remember input{ width:16px; height:16px; accent-color:var(--brand2) }
        .forgot{ font-size:13px; color:var(--brand2); text-decoration:none; font-weight:700 }
        .forgot:hover{ text-decoration:underline }

        .btn{
            width:100%; height:52px; border:0; border-radius:14px; cursor:pointer;
            background:linear-gradient(135deg,var(--brand1),var(--brand2) 60%,var(--brand3));
            color:#fff; font-size:16px; font-weight:800; letter-spacing:.3px;
            box-shadow:0 12px 26px rgba(92,0,142,.35); transition:.15s;
        }
        .btn:hover{ transform:translateY(-1px); box-shadow:0 16px 32px rgba(92,0,142,.42) }
        .btn:active{ transform:translateY(0) }

        .foot{ text-align:center; margin-top:22px; font-size:12px; color:#a7a3b6 }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="logo">RC</div>
            <h1>Randevumcepte</h1>
            <p>Sistem Yönetim Paneli</p>
        </div>

        @if($errors->any())
            <div class="alert">
                @foreach($errors->all() as $err)
                    {{ $err }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.login.submit') }}" autocomplete="on">
            {{ csrf_field() }}

            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">E-posta</label>
                <input id="email" name="email" type="email" placeholder="ornek@randevumcepte.com.tr"
                       value="{{ old('email') }}" required autofocus>
                @if($errors->has('email'))<div class="err">{{ $errors->first('email') }}</div>@endif
            </div>

            <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">Şifre</label>
                <input id="password" name="password" type="password" placeholder="••••••••" required>
                @if($errors->has('password'))<div class="err">{{ $errors->first('password') }}</div>@endif
            </div>

            <div class="row">
                <label class="remember">
                    <input type="checkbox" name="member" value="1" {{ old('member') ? 'checked' : '' }}>
                    Beni Hatırla
                </label>
                <a class="forgot" href="#" onclick="return false">Şifremi Unuttum</a>
            </div>

            <button type="submit" class="btn">Giriş Yap</button>
        </form>

        <div class="foot">© {{ date('Y') }} Randevumcepte · Tüm hakları saklıdır</div>
    </div>
</body>
</html>
