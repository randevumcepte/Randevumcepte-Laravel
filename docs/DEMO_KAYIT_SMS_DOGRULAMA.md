# Demo Kaydı — SMS Doğrulama (2 Adım)

`POST /api/v1/siteden-yeni-kullanici-kaydi` endpoint'i artık **iki adımlı** çalışır.
Amaç: yanlış/sahte telefon numaralarıyla anında hesap açılmasını önlemek.

- **Adım 1** (`dogrulama_kodu` boş gönderilir): Numaraya 4 haneli kod SMS ile gider,
  form verisi sunucuda 10 dakika bekler. **HESAP AÇILMAZ.**
- **Adım 2** (`dogrulama_kodu` dolu gönderilir): Kod doğruysa hesap açılır ve
  şifre SMS ile gider.

Backend değişikliği: `app/Http/Controllers/ApiController.php` →
`siteden_yeni_kayit_kullanici()`. Frontend (WordPress "Üye Ol" formu) aşağıdaki gibi
güncellenmelidir.

---

## İstek (Request) parametreleri — her iki adımda aynı

| Alan            | Açıklama                                   |
|-----------------|--------------------------------------------|
| `isletmeturu`   | İşletme Türü metni (ör: `Kuaförler`)       |
| `isletmeadi`    | İşletme Adı                                |
| `isletmeadresi` | İşletme adresi                             |
| `adsoyad`       | Yetkili Adı Soyadı                         |
| `ceptelefon`    | Telefon                                    |
| `email`         | Email                                      |
| `dogrulama_kodu`| **Adım 1'de BOŞ**, Adım 2'de kullanıcının girdiği 4 haneli kod |

> Not: Adım 2'de sunucu, form verisini **cache'ten** (Adım 1'de kaydedilen) okur;
> yani ikinci istekte sadece `ceptelefon` + `dogrulama_kodu` yeterlidir. Yine de
> tüm alanları göndermek zararsızdır.

## Yanıt (Response) — her zaman JSON, `durum` alanına bakın

| `durum`          | Anlamı / Yapılacak                                             |
|------------------|---------------------------------------------------------------|
| `kod_gonderildi` | (Adım 1 başarılı) Kod girişi ekranını göster                  |
| `zaten_uye`      | Bu numarayla zaten üyelik var — mesajı göster                 |
| `hata`           | Geçersiz numara / eksik alan / SMS gitmedi — mesajı göster    |
| `kod_hatali`     | (Adım 2) Kod yanlış — tekrar denet                            |
| `suresi_doldu`   | 10 dk doldu — kaydı baştan başlat                             |
| `cok_deneme`     | 5 kez yanlış — kaydı baştan başlat                            |
| `tamam`          | Hesap açıldı — `yonlendir` alanındaki URL'e yönlendir         |

Her yanıtta gösterilebilir bir `mesaj` alanı bulunur.

---

## WordPress form JS (vanilla, jQuery gerekmez)

> Aşağıdaki **SELECTOR**'ları kendi formunun gerçek `id`/`name` değerleriyle
> değiştir. `API_URL`'i formun şu an POST ettiği adresle aynı yap.

```html
<!-- Formun içine, gönder butonunun ÜSTÜNE, başlangıçta gizli kod alanı ekle -->
<div id="dogrulamaKutu" style="display:none;margin:12px 0;">
  <input type="text" id="dogrulama_kodu" inputmode="numeric" maxlength="4"
         placeholder="SMS ile gelen 4 haneli kod"
         style="width:100%;padding:12px;font-size:18px;letter-spacing:4px;text-align:center;">
</div>
<p id="kayitMesaj" style="margin:8px 0;"></p>
```

```javascript
(function () {
  // ==== AYARLAR — kendi değerlerinle değiştir ====
  var API_URL = 'https://app.randevumcepte.com.tr/api/v1/siteden-yeni-kullanici-kaydi';
  var form    = document.querySelector('#uyeOlForm');      // <-- formun selector'ı
  var btn     = form.querySelector('button[type="submit"], .uye-ol-btn'); // <-- gönder butonu
  var kutu    = document.getElementById('dogrulamaKutu');
  var kodEl   = document.getElementById('dogrulama_kodu');
  var mesajEl = document.getElementById('kayitMesaj');

  // Form alanlarını oku — kendi id/name'lerinle eşle
  function veriTopla() {
    return {
      isletmeturu:   (form.querySelector('[name="isletmeturu"]')   || {}).value || '',
      isletmeadi:    (form.querySelector('[name="isletmeadi"]')    || {}).value || '',
      isletmeadresi: (form.querySelector('[name="isletmeadresi"]') || {}).value || '',
      adsoyad:       (form.querySelector('[name="adsoyad"]')       || {}).value || '',
      ceptelefon:    (form.querySelector('[name="ceptelefon"]')    || {}).value || '',
      email:         (form.querySelector('[name="email"]')         || {}).value || ''
    };
  }

  function mesajGoster(t, renk) { mesajEl.textContent = t || ''; mesajEl.style.color = renk || '#333'; }

  function gonder(kod) {
    var veri = veriTopla();
    veri.dogrulama_kodu = kod || '';
    btn.disabled = true;
    var eskiMetin = btn.textContent;
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
          mesajGoster(res.mesaj, '#0a7d29');
          if (res.yonlendir) { window.location.href = res.yonlendir; }
          break;
        case 'kod_hatali':
        case 'suresi_doldu':
        case 'cok_deneme':
        case 'zaten_uye':
        case 'hata':
        default:
          btn.textContent = eskiMetin;
          mesajGoster(res.mesaj || 'Bir hata oluştu, lütfen tekrar deneyin.', '#c0181f');
          // Süre dolduysa/çok deneme -> baştan başlat
          if (res.durum === 'suresi_doldu' || res.durum === 'cok_deneme') {
            kutu.style.display = 'none';
            kodEl.value = '';
            btn.textContent = eskiMetin;
          }
      }
    })
    .catch(function () {
      btn.disabled = false;
      btn.textContent = eskiMetin;
      mesajGoster('Bağlantı hatası, lütfen tekrar deneyin.', '#c0181f');
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    // Kod kutusu açıksa -> Adım 2 (kod ile), değilse -> Adım 1 (kod boş)
    if (kutu.style.display === 'block') {
      var kod = (kodEl.value || '').trim();
      if (kod.length < 4) { mesajGoster('Lütfen 4 haneli kodu giriniz.', '#c0181f'); return; }
      gonder(kod);
    } else {
      gonder('');
    }
  });
})();
```

## Akış özeti

1. Kullanıcı formu doldurur, **Üye Ol** → `dogrulama_kodu` boş POST edilir.
2. Sunucu numarayı zaten üye mi diye kontrol eder; değilse SMS kod gönderir
   (`durum: kod_gonderildi`). Kod girişi görünür, buton **"Doğrula ve Hesabı Aç"** olur.
3. Kullanıcı kodu girip butona basar → `dogrulama_kodu` dolu POST edilir.
4. Kod doğruysa hesap açılır (`durum: tamam`) ve `yonlendir` URL'ine gidilir; şifre
   ayrıca SMS ile gelir. Kod yanlışsa `kod_hatali` döner (5 hakka kadar).

## Notlar

- Kod **10 dakika** geçerlidir, **5 yanlış** denemeden sonra iptal olur (baştan başlanır).
- Doğrulama verisi Laravel Cache'te (`demo_kayit_dogrulama_<telefon>`) tutulur; ayrı
  tablo/migration gerekmez.
- SMS, mevcut VoiceTelekom entegrasyonuyla `RANDVMCEPTE` başlığından gider.
- Kod önce **apptest** ortamına deploy olur; canlıya (`app`) geçmeden apptest'te test edin.
