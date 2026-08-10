<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ücretsiz Denemenizi Başlatın — Randevumcepte</title>
    <meta name="description" content="Randevumcepte'yi ücretsiz deneyin. Dakikalar içinde işletmenizin online randevu sistemini kurun.">
    <link rel="icon" href="/favicon.ico">
    <style>
        :root{ --mor:#6541c1; --pembe:#d43396; --grad:linear-gradient(135deg,#6541c1 0%,#d43396 100%); }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            color:#2b2b3a;background:#f5f4fb;line-height:1.5;min-height:100vh;display:flex;flex-direction:column}
        a{color:inherit}
        .ust{background:var(--grad);color:#fff;padding:20px 16px;text-align:center}
        .ust .logo{font-size:22px;font-weight:800;letter-spacing:.3px}
        .hero{background:var(--grad);color:#fff;padding:24px 16px 90px;text-align:center}
        .hero h1{font-size:26px;font-weight:800;line-height:1.25;margin-bottom:10px}
        .hero p{font-size:16px;opacity:.95;max-width:560px;margin:0 auto}
        .wrap{max-width:520px;margin:-70px auto 40px;padding:0 16px;width:100%}
        .kart{background:#fff;border-radius:18px;box-shadow:0 20px 45px rgba(101,65,193,.18);padding:26px 22px}
        .kart h2{font-size:20px;font-weight:800;margin-bottom:4px;text-align:center}
        .kart .alt{font-size:14px;color:#6b6b7b;text-align:center;margin-bottom:20px}
        .fg{margin-bottom:16px}
        .fg label{display:block;font-size:13px;font-weight:700;margin-bottom:6px;color:#3a3a4a}
        .fg input,.fg select{width:100%;padding:13px 14px;font-size:15px;border:1.5px solid #e2e0ee;
            border-radius:12px;background:#fbfbfe;outline:none;transition:border-color .15s,box-shadow .15s}
        .fg input:focus,.fg select:focus{border-color:var(--mor);box-shadow:0 0 0 3px rgba(101,65,193,.12)}
        #dogrulamaKutu input{letter-spacing:8px;text-align:center;font-size:20px;font-weight:800}
        #dogrulamaKutu{background:#f3f0fc;border:1.5px dashed var(--mor);border-radius:12px;padding:14px;margin-bottom:16px}
        #dogrulamaKutu .ipucu{font-size:12px;color:var(--mor);font-weight:700;margin-bottom:8px;text-align:center}
        #kayitMesaj{font-size:14px;font-weight:600;text-align:center;margin:4px 0 14px;min-height:20px}
        .btn{width:100%;border:none;cursor:pointer;color:#fff;background:var(--grad);
            padding:15px;font-size:17px;font-weight:800;border-radius:12px;transition:opacity .15s,transform .05s}
        .btn:hover{opacity:.94}
        .btn:active{transform:translateY(1px)}
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .kvkk{font-size:12px;color:#8a8a99;text-align:center;margin-top:14px}
        .kvkk a{color:var(--mor);font-weight:600;text-decoration:none}
        .avantaj{max-width:520px;margin:0 auto 40px;padding:0 16px;width:100%}
        .avantaj ul{list-style:none;display:grid;gap:10px}
        .avantaj li{background:#fff;border-radius:12px;padding:14px 16px;font-size:14px;font-weight:600;
            display:flex;align-items:center;gap:10px;box-shadow:0 6px 16px rgba(101,65,193,.06)}
        .avantaj li b{color:var(--mor)}
        .tik{flex:0 0 22px;width:22px;height:22px;border-radius:50%;background:var(--grad);color:#fff;
            display:flex;align-items:center;justify-content:center;font-size:13px}
        footer{margin-top:auto;background:#241a3a;color:#c9c3dd;text-align:center;padding:20px 16px;font-size:13px}
        footer a{color:#fff;text-decoration:none}
    </style>
</head>
<body>
    <div class="ust"><div class="logo">Randevumcepte</div></div>

    <section class="hero">
        <h1>Ücretsiz Denemenizi Başlatın</h1>
        <p>İşletmenizin online randevu sistemini dakikalar içinde kurun. Kredi kartı gerekmez.</p>
    </section>

    <div class="wrap">
        <div class="kart">
            <h2>Hemen Üye Olun</h2>
            <div class="alt">Bilgilerinizi girin, telefonunuza gelen kodla hesabınızı açın.</div>

            <form id="uyeOlForm" autocomplete="on" novalidate>
                <div class="fg">
                    <label for="isletmeturu">İşletme Türü</label>
                    <select id="isletmeturu" name="isletmeturu" required>
                        <option value="Kuaförler">Kuaförler</option>
                        <option value="Erkek Kuaförü">Erkek Kuaförü</option>
                        <option value="Güzellik Merkezi">Güzellik Merkezi</option>
                        <option value="Doktor">Doktor</option>
                        <option value="Diyetisyen">Diyetisyen</option>
                        <option value="Klinik">Klinik</option>
                        <option value="Saç Simülasyonu">Saç Simülasyonu</option>
                        <option value="Tırnak Center">Tırnak Center</option>
                        <option value="Saç Ekim">Saç Ekim</option>
                        <option value="Tattoo &amp; Piercing Salonları">Tattoo &amp; Piercing Salonları</option>
                        <option value="Masaj &amp; Spa Salonları">Masaj &amp; Spa Salonları</option>
                        <option value="Kaş ve Kirpik Merkezi">Kaş ve Kirpik Merkezi</option>
                        <option value="Makyaj &amp; Tırnak Stüdyosu">Makyaj &amp; Tırnak Stüdyosu</option>
                    </select>
                </div>

                <div class="fg">
                    <label for="isletmeadi">İşletme Adı</label>
                    <input type="text" id="isletmeadi" name="isletmeadi" required>
                </div>

                <div class="fg">
                    <label for="isletmeadresi">İşletme Adresi</label>
                    <input type="text" id="isletmeadresi" name="isletmeadresi" required>
                </div>

                <div class="fg">
                    <label for="adsoyad">Yetkili Adı Soyadı</label>
                    <input type="text" id="adsoyad" name="adsoyad" required>
                </div>

                <div class="fg">
                    <label for="ceptelefon">Telefon</label>
                    <input type="tel" id="ceptelefon" name="ceptelefon" inputmode="numeric"
                           placeholder="+90 (5__) ___ __ __" required>
                </div>

                <div class="fg">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div id="dogrulamaKutu" style="display:none;">
                    <div class="ipucu">SMS ile gelen 4 haneli kodu giriniz</div>
                    <input type="text" id="dogrulama_kodu" inputmode="numeric" maxlength="4"
                           placeholder="_ _ _ _">
                </div>

                <p id="kayitMesaj"></p>

                <button type="submit" class="btn" id="uyeOlBtn">ÜYE OL</button>

                <div class="kvkk">
                    Üye olarak <a href="https://randevumcepte.com.tr/mesafeli-satis-sozlesmesi" target="_blank" rel="noopener">Kullanıcı Sözleşmesi</a> ve
                    <a href="https://randevumcepte.com.tr/gizlilik-politikasi-ve-guvenlik/" target="_blank" rel="noopener">Gizlilik Politikası ve Güvenlik</a>'i kabul etmiş olursunuz.
                </div>
            </form>
        </div>
    </div>

    <div class="avantaj">
        <ul>
            <li><span class="tik">✓</span> <span><b>7/24 online randevu</b> — müşterileriniz istediği an randevu alsın.</span></li>
            <li><span class="tik">✓</span> <span><b>WhatsApp &amp; SMS hatırlatma</b> — gelmeyen randevu derdine son.</span></li>
            <li><span class="tik">✓</span> <span><b>Personel &amp; kasa takibi</b> — işletmenizi tek ekrandan yönetin.</span></li>
            <li><span class="tik">✓</span> <span><b>Kredi kartı gerekmez</b> — hemen deneyin, sonra karar verin.</span></li>
        </ul>
    </div>

    <footer>
        © {{ date('Y') }} Randevumcepte · <a href="https://wa.me/905412948144" target="_blank">Destek: 0541 294 81 44</a>
    </footer>

    <script>
    (function () {
        var API_URL = '/api/v1/siteden-yeni-kullanici-kaydi';
        var form   = document.getElementById('uyeOlForm');
        var btn    = document.getElementById('uyeOlBtn');
        var kutu   = document.getElementById('dogrulamaKutu');
        var kodEl  = document.getElementById('dogrulama_kodu');
        var mesajEl= document.getElementById('kayitMesaj');
        var telEl  = document.getElementById('ceptelefon');
        var eskiMetin = 'ÜYE OL';

        // --- Telefon maskesi: +90 (5xx) xxx xx xx ---
        telEl.addEventListener('input', function () {
            var d = telEl.value.replace(/\D/g, '');
            if (d.indexOf('90') === 0) d = d.slice(2);
            d = d.slice(0, 10);
            var out = '+90 ';
            if (d.length > 0) out += '(' + d.slice(0, 3);
            if (d.length >= 3) out += ') ';
            if (d.length > 3) out += d.slice(3, 6);
            if (d.length > 6) out += ' ' + d.slice(6, 8);
            if (d.length > 8) out += ' ' + d.slice(8, 10);
            telEl.value = out;
        });

        function veriTopla() {
            return {
                isletmeturu:   form.querySelector('[name="isletmeturu"]').value || '',
                isletmeadi:    form.querySelector('[name="isletmeadi"]').value || '',
                isletmeadresi: form.querySelector('[name="isletmeadresi"]').value || '',
                adsoyad:       form.querySelector('[name="adsoyad"]').value || '',
                ceptelefon:    form.querySelector('[name="ceptelefon"]').value || '',
                email:         form.querySelector('[name="email"]').value || ''
            };
        }

        function mesajGoster(t, renk) { mesajEl.textContent = t || ''; mesajEl.style.color = renk || '#333'; }

        function ilkAdimBosMu() {
            var v = veriTopla();
            return !v.isletmeadi.trim() || !v.isletmeadresi.trim() || !v.adsoyad.trim()
                || !v.ceptelefon.trim() || !v.email.trim();
        }

        function gonder(kod) {
            var veri = veriTopla();
            veri.dogrulama_kodu = kod || '';
            btn.disabled = true;
            btn.textContent = 'Lütfen bekleyin...';

            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(veri)
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false;
                switch (res.durum) {
                    case 'kod_gonderildi':
                        kutu.style.display = 'block';
                        kodEl.focus();
                        btn.textContent = 'Doğrula ve Hesabı Aç';
                        mesajGoster(res.mesaj, '#0a7d29');
                        break;
                    case 'tamam':
                        btn.textContent = 'Hesap Açıldı';
                        mesajGoster(res.mesaj, '#0a7d29');
                        if (res.yonlendir) { setTimeout(function(){ window.location.href = res.yonlendir; }, 2500); }
                        break;
                    case 'suresi_doldu':
                    case 'cok_deneme':
                        kutu.style.display = 'none';
                        kodEl.value = '';
                        btn.textContent = eskiMetin;
                        mesajGoster(res.mesaj || 'Lütfen kaydı yeniden başlatınız.', '#c0181f');
                        break;
                    case 'kod_hatali':
                        btn.textContent = 'Doğrula ve Hesabı Aç';
                        mesajGoster(res.mesaj || 'Doğrulama kodu hatalı.', '#c0181f');
                        break;
                    case 'zaten_uye':
                    case 'hata':
                    default:
                        btn.textContent = (kutu.style.display === 'block') ? 'Doğrula ve Hesabı Aç' : eskiMetin;
                        mesajGoster(res.mesaj || 'Bir hata oluştu, lütfen tekrar deneyin.', '#c0181f');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = (kutu.style.display === 'block') ? 'Doğrula ve Hesabı Aç' : eskiMetin;
                mesajGoster('Bağlantı hatası, lütfen tekrar deneyin.', '#c0181f');
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (kutu.style.display === 'block') {
                var kod = (kodEl.value || '').trim();
                if (kod.length < 4) { mesajGoster('Lütfen 4 haneli kodu giriniz.', '#c0181f'); return; }
                gonder(kod);
            } else {
                if (ilkAdimBosMu()) { mesajGoster('Lütfen tüm alanları eksiksiz doldurunuz.', '#c0181f'); return; }
                gonder('');
            }
        });
    })();
    </script>
</body>
</html>
