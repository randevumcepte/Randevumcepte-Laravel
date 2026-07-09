@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif
@extends($_layout)
@section('content')
@php $subeParam = (isset($_GET['sube'])) ? '?sube='.$isletme->id : ''; @endphp
<div class="page-header">
  <div class="row">
    <div class="col-md-6 col-sm-12">
      <div class="title"><h1>{{ $sayfa_baslik }}</h1></div>
      <nav aria-label="breadcrumb" role="navigation">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/isletmeyonetim{{ $subeParam }}">Ana Sayfa</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $sayfa_baslik }}</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<style>
  .afis-wrap{display:flex;gap:24px;flex-wrap:wrap}
  .afis-onizleme{flex:0 0 360px;max-width:100%}
  .afis-onizleme img{width:100%;border-radius:14px;box-shadow:0 12px 34px rgba(94,53,67,.22);border:1px solid #eee}
  .afis-bilgi{flex:1;min-width:280px}
  .afis-bilgi h3{font-weight:700;color:#3a2a31;margin-bottom:10px}
  .afis-bilgi p{color:#6b6076;line-height:1.7;margin-bottom:14px}
  .afis-adim{display:flex;gap:12px;margin-bottom:12px}
  .afis-adim .n{flex:0 0 30px;width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#7c4a5a,#a86b7b);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px}
  .afis-adim .t{font-size:14px;color:#46343c;padding-top:4px}
  .afis-btn{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,#7c4a5a,#a86b7b);color:#fff;
    border:none;border-radius:12px;padding:14px 26px;font-size:16px;font-weight:600;text-decoration:none;box-shadow:0 10px 24px rgba(124,74,90,.3);cursor:pointer}
  .afis-btn:hover{color:#fff;opacity:.94}
  .afis-uyari{background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:20px 22px;color:#9a3412;display:flex;gap:14px;align-items:flex-start}
  .afis-uyari .ic{flex:0 0 28px;width:28px;height:28px;border-radius:50%;background:#fb923c;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800}
  .afis-uyari b{display:block;margin-bottom:4px}
  .afis-link-kutu{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:6px}
  .afis-link-kutu input{flex:1;min-width:260px;border:1px solid #e5e0e6;border-radius:12px;padding:12px 16px;font-size:14px;color:#46343c;background:#faf7fb}
  .afis-link-mini{display:inline-flex;align-items:center;gap:8px;border:1px solid #d9cfdd;background:#fff;color:#7c4a5a;border-radius:12px;padding:12px 18px;font-size:14px;font-weight:600;text-decoration:none;cursor:pointer}
  .afis-link-mini:hover{background:#faf5fb;color:#7c4a5a}
  .afis-rozet{display:inline-block;background:#eef7ee;border:1px solid #cde8cd;color:#2f6b34;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;margin-left:8px}
</style>

<div class="pd-20 card-box mb-30">
  <div class="afis-wrap">
    <div class="afis-bilgi">
      <h3>Salonunuza özel uygulama indirme afişi</h3>
      <p>Müşterileriniz afişteki QR kodu telefonlarıyla okuttuğunda, cihazlarına uygun mağazadan (App Store / Google Play) uygulamayı indirebilir. Afişi yazdırıp salonunuzda görünür bir yere asabilirsiniz.</p>

      @if($linklerHazir)
        <div class="afis-adim"><div class="n">1</div><div class="t">Afişi PDF olarak indirin.</div></div>
        <div class="afis-adim"><div class="n">2</div><div class="t">A4 boyutunda yazdırın (kenar boşluğu olmadan).</div></div>
        <div class="afis-adim"><div class="n">3</div><div class="t">Salonunuzda müşterilerin görebileceği bir yere asın.</div></div>
        <a class="afis-btn" href="/isletmeyonetim/uygulama-afisi-pdf{{ $subeParam }}">
          <i class="fa fa-download"></i> Afişi PDF İndir
        </a>
      @else
        <div class="afis-uyari">
          <div class="ic">!</div>
          <div>
            <b>Uygulama mağaza bağlantılarınız henüz hazır değil.</b>
            Afişinizin oluşturulabilmesi için hem <strong>App Store</strong> hem de <strong>Google Play</strong> uygulama linklerinizin tanımlı olması gerekir. Uygulamanız yayına alınıp linkler tanımlandığında afişiniz buradan indirilebilir olacaktır.
          </div>
        </div>
      @endif
    </div>

    @if($linklerHazir)
    <div class="afis-onizleme">
      <img src="/isletmeyonetim/uygulama-afisi-pdf?onizleme=1{{ (isset($_GET['sube'])) ? '&sube='.$isletme->id : '' }}" alt="Afiş önizleme">
    </div>
    @endif
  </div>
</div>

@if($herhangiLink)
<div class="pd-20 card-box mb-30">
  <h3 style="font-weight:700;color:#3a2a31;margin-bottom:10px">
    Uygulama indirme linki
    <span class="afis-rozet">Akıllı yönlendirme</span>
  </h3>
  <p style="color:#6b6076;line-height:1.7;margin-bottom:14px">
    Bu tek linki mesaj, e-posta veya sosyal medya üzerinden müşterilerinize gönderebilirsiniz. Link açıldığında telefon
    <strong>Android</strong> ise Google Play, <strong>iPhone</strong> ise App Store sayfasına otomatik yönlendirilir.
    Afişteki QR kod da aynı adrese gider.
  </p>

  <div class="afis-link-kutu">
    <input type="text" id="afisIndirLinki" value="{{ $indirLinki }}" readonly onclick="this.select()">
    <button type="button" class="afis-link-mini" id="afisLinkKopyala">
      <i class="fa fa-copy"></i> <span>Kopyala</span>
    </button>
    <a class="afis-link-mini" href="https://api.whatsapp.com/send?text={{ rawurlencode('Uygulamamızı indirmek için: ' . $indirLinki) }}" target="_blank" rel="noopener">
      <i class="fa fa-whatsapp"></i> WhatsApp ile paylaş
    </a>
    <a class="afis-link-mini" href="{{ $indirLinki }}" target="_blank" rel="noopener">
      <i class="fa fa-external-link"></i> Test et
    </a>
  </div>
</div>

<script>
  document.getElementById('afisLinkKopyala').addEventListener('click', function () {
    var girdi = document.getElementById('afisIndirLinki');
    var etiket = this.querySelector('span');
    girdi.select();
    girdi.setSelectionRange(0, 99999);
    try {
      document.execCommand('copy');
      etiket.textContent = 'Kopyalandı!';
      setTimeout(function () { etiket.textContent = 'Kopyala'; }, 2000);
    } catch (e) {}
  });
</script>
@endif
@endsection
