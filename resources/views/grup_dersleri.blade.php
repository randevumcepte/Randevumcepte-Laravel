<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $salon->salon_adi }} — Grup Dersleri</title>
<style>
  *{box-sizing:border-box;}
  body{margin:0;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f4f6f8;color:#2c3e50;}
  .gd-head{background:#16a085;color:#fff;padding:20px 16px;text-align:center;}
  .gd-head h1{margin:0;font-size:19px;}
  .gd-head p{margin:4px 0 0;font-size:13px;opacity:.9;}
  .gd-wrap{max-width:560px;margin:0 auto;padding:14px;}
  .gd-gun{font-weight:700;color:#16a085;margin:16px 4px 8px;font-size:15px;border-bottom:2px solid #e6f4f1;padding-bottom:4px;}
  .gd-card{background:#fff;border:1px solid #eef1f4;border-radius:12px;padding:12px 14px;margin-bottom:9px;display:flex;justify-content:space-between;align-items:center;gap:10px;}
  .gd-card .t{font-weight:700;font-size:15px;}
  .gd-card .d{font-size:12.5px;color:#7f8c8d;margin-top:2px;}
  .gd-card .doluluk{font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:999px;display:inline-block;margin-top:5px;}
  .doluluk.ok{background:#e8f8f5;color:#148f77;}
  .doluluk.full{background:#fdedec;color:#c0392b;}
  .gd-btn{background:#16a085;color:#fff;border:none;border-radius:8px;padding:9px 14px;font-size:13.5px;font-weight:700;cursor:pointer;white-space:nowrap;}
  .gd-btn.full{background:#e67e22;}
  .gd-bos{color:#95a5a6;text-align:center;padding:40px 10px;}
  /* modal */
  .gd-ov{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;padding:16px;z-index:50;}
  .gd-modal{background:#fff;border-radius:14px;max-width:380px;width:100%;overflow:hidden;}
  .gd-modal-h{background:#16a085;color:#fff;padding:14px 16px;font-weight:700;}
  .gd-modal-b{padding:16px;}
  .gd-modal-b label{font-size:12px;font-weight:600;color:#5f6b7a;display:block;margin:8px 0 4px;}
  .gd-modal-b input{width:100%;height:42px;border:1px solid #d7dde3;border-radius:8px;padding:0 11px;font-size:15px;}
  .gd-modal-b .info{font-size:12.5px;color:#7f8c8d;margin:0 0 6px;}
  .gd-modal-b .ders-ozet{background:#f4f6f8;border-radius:8px;padding:8px 10px;font-size:13px;margin-bottom:10px;}
  .gd-modal-b button{width:100%;height:44px;border:none;border-radius:9px;background:#16a085;color:#fff;font-weight:700;font-size:15px;margin-top:14px;cursor:pointer;}
  .gd-modal-b .iptal{background:#eee;color:#555;height:38px;margin-top:8px;}
  .gd-msg{padding:10px 12px;border-radius:8px;font-size:13.5px;margin-top:10px;display:none;}
  .gd-msg.ok{background:#e8f8f5;color:#148f77;} .gd-msg.err{background:#fdedec;color:#c0392b;}
</style>
</head>
<body>
  <div class="gd-head">
    <h1>{{ $salon->salon_adi }}</h1>
    <p>Grup Derslerine Online Rezervasyon</p>
  </div>
  <div class="gd-wrap">
    @php
      $gunAd = [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];
      $ayAd  = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
      $gruplu = collect($dersler)->groupBy('tarih');
    @endphp

    @if(count($dersler) === 0)
      <div class="gd-bos">Şu an rezervasyona açık grup dersi bulunmuyor.</div>
    @endif

    @foreach($gruplu as $tarih => $liste)
      @php $ts = strtotime($tarih); $iso = (int)date('N',$ts); @endphp
      <div class="gd-gun">{{ (int)date('j',$ts) }} {{ $ayAd[(int)date('n',$ts)] }} — {{ $gunAd[$iso] }}</div>
      @foreach($liste as $d)
        <div class="gd-card">
          <div>
            <div class="t">{{ $d['ders_tipi'] }}</div>
            <div class="d">{{ $d['saat'] }} - {{ $d['saat_bitis'] }}@if($d['egitmen']) • {{ $d['egitmen'] }}@endif</div>
            @if($d['bos'] > 0)
              <span class="doluluk ok">{{ $d['bos'] }} yer boş</span>
            @else
              <span class="doluluk full">Dolu — bekleme listesi</span>
            @endif
          </div>
          <button class="gd-btn {{ $d['bos']>0 ? '' : 'full' }}"
            onclick="gdRez({{ $d['id'] }}, {{ json_encode($d['ders_tipi']) }}, {{ json_encode($d['tarih'].' '.$d['saat']) }}, {{ $d['bos']>0 ? 1 : 0 }})">
            {{ $d['bos']>0 ? 'Rezervasyon' : 'Bekleme' }}
          </button>
        </div>
      @endforeach
    @endforeach
  </div>

  {{-- Rezervasyon modali --}}
  <div class="gd-ov" id="gd-ov">
    <div class="gd-modal">
      <div class="gd-modal-h">Rezervasyon</div>
      <div class="gd-modal-b">
        <div class="ders-ozet" id="gd-ozet"></div>
        <p class="info">Kayıtlı müşteri telefonunuz ve şifrenizle giriş yapın. Bu ders için paket/seans hakkınız kontrol edilir.</p>
        <label>Cep Telefonu</label>
        <input type="tel" id="gd-tel" placeholder="05XXXXXXXXX">
        <label>Şifre</label>
        <input type="password" id="gd-sifre" placeholder="Şifreniz">
        <div class="gd-msg" id="gd-msg"></div>
        <button id="gd-onayla" onclick="gdGonder()">Rezervasyonu Tamamla</button>
        <button class="iptal" onclick="document.getElementById('gd-ov').style.display='none'">Vazgeç</button>
      </div>
    </div>
  </div>

<script>
  var GD_SALON = {{ (int)$salon->id }};
  var gdSecili = null;
  function gdRez(oturumId, ad, zaman, bos){
    gdSecili = oturumId;
    document.getElementById('gd-ozet').textContent = ad + '  •  ' + zaman + (bos ? '' : '  (Kapasite dolu — bekleme listesine eklenirsiniz)');
    var m=document.getElementById('gd-msg'); m.style.display='none'; m.className='gd-msg';
    document.getElementById('gd-ov').style.display='flex';
  }
  function gdGonder(){
    var btn=document.getElementById('gd-onayla'); btn.disabled=true; btn.textContent='Gönderiliyor...';
    var m=document.getElementById('gd-msg');
    fetch('/grup-dersi-rezervasyon',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
      body:JSON.stringify({salon_id:GD_SALON, oturum_id:gdSecili, ceptelefon:document.getElementById('gd-tel').value, sifre:document.getElementById('gd-sifre').value})
    }).then(function(r){return r.json().then(function(j){return {ok:r.ok,j:j};});})
    .then(function(res){
      btn.disabled=false; btn.textContent='Rezervasyonu Tamamla';
      m.style.display='block';
      if(res.j && res.j.durum==='ok'){ m.className='gd-msg ok'; m.textContent=res.j.mesaj||'Rezervasyonunuz alındı.'; setTimeout(function(){location.reload();},2200); }
      else { m.className='gd-msg err'; m.textContent=(res.j&&res.j.mesaj)||'İşlem başarısız.'; }
    }).catch(function(){ btn.disabled=false; btn.textContent='Rezervasyonu Tamamla'; m.style.display='block'; m.className='gd-msg err'; m.textContent='Bağlantı hatası, tekrar deneyin.'; });
  }
</script>
</body>
</html>
