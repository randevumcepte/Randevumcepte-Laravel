<div id="sozlesmeOlusturModal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog" style="max-width: 700px">
      <form id="sozlesmeOlusturForm">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="h4">Hizmet Sözleşmesi Oluştur</h4>
               <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="padding:20px;">
               <div class="row">
                  <div class="col-md-6 form-group">
                     <label><b>Müşteri *</b></label>
                     <select name="user_id" id="sozlesme_musteri" class="form-control opsiyonelSelect musteri_secimi" style="width:100%;" required>
                        <option></option>
                     </select>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Cep Telefon *</b></label>
                     <input type="tel" name="cep_telefon" id="sozlesme_telefon" class="form-control" required>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Hizmet Seç</b></label>
                     <select name="hizmet_id" id="sozlesme_hizmet" class="form-control opsiyonelSelect" style="width:100%;">
                        <option value="">— Hizmet seçin —</option>
                        @php
                           try {
                              $hizmetler = \DB::table('salon_sunulan_hizmetler')
                                 ->leftJoin('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')
                                 ->where('salon_sunulan_hizmetler.salon_id',$isletme->id)
                                 ->select('salon_sunulan_hizmetler.id','hizmetler.hizmet_adi','salon_sunulan_hizmetler.son_fiyat')
                                 ->get();
                           } catch(\Exception $e){ $hizmetler = collect(); }
                        @endphp
                        @foreach($hizmetler as $h)
                           <option value="{{$h->id}}" data-fiyat="{{$h->son_fiyat ?? 0}}">{{ $h->hizmet_adi ?? '-' }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Paket Seç</b> <small class="text-muted">(opsiyonel)</small></label>
                     <select name="paket_id" id="sozlesme_paket" class="form-control opsiyonelSelect" style="width:100%;">
                        <option value="">— Paket seçin —</option>
                        @php
                           try {
                              $paketlerListesi = \DB::table('paketler')->where('salon_id',$isletme->id)->get();
                           } catch(\Exception $e){ $paketlerListesi = collect(); }
                        @endphp
                        @foreach($paketlerListesi as $p)
                           <option value="{{$p->id}}" data-fiyat="{{$p->paket_fiyati ?? $p->fiyat ?? 0}}" data-seans="{{$p->seans_sayisi ?? 1}}">{{ $p->paket_adi }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Seans Sayısı</b></label>
                     <input type="number" name="seans_sayisi" id="sozlesme_seans" class="form-control" min="1" value="1">
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Toplam Ücret (₺) *</b></label>
                     <div style="position:relative;">
                        <input type="text" id="sozlesme_toplam_display" class="form-control tl-input" inputmode="decimal" required placeholder="0,00" style="padding-right:30px;">
                        <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#666; font-weight:600;">₺</span>
                        <input type="hidden" name="toplam_ucret" id="sozlesme_toplam">
                     </div>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Kapora (₺)</b></label>
                     <div style="position:relative;">
                        <input type="text" id="sozlesme_kapora_display" class="form-control tl-input" inputmode="decimal" placeholder="0,00" style="padding-right:30px;">
                        <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#666; font-weight:600;">₺</span>
                        <input type="hidden" name="kapora" id="sozlesme_kapora" value="0">
                     </div>
                  </div>
                  <div class="col-md-12 form-group">
                     <label><b>Sözleşme Şartları *</b> <small class="text-muted">(müşteriye gösterilecek metin — düzenleyebilirsiniz)</small></label>
                     <textarea name="sozlesme_metni" id="sozlesme_metni" class="form-control" rows="10" required style="font-size:13px;font-family:monospace;"></textarea>
                     <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:4px; flex-wrap:wrap;">
                        <small class="text-muted" style="flex:1; min-width:200px;">İpucu: Müşteri adı, hizmet, fiyat ve kapora otomatik olarak metnin üstünde tabloda gösterilir — burada sadece sözleşme şartlarını yazın.</small>
                        <button type="button" id="sozlesmeVarsayilanKaydetBtn" class="btn btn-sm btn-outline-primary" style="white-space:nowrap;"><i class="fa fa-save"></i> Bu metni varsayılan yap</button>
                     </div>
                  </div>
                  <div class="col-md-12 form-group">
                     <label><b>Ek Not</b> <small class="text-muted">(opsiyonel — sözleşmenin altında ayrıca görünür)</small></label>
                     <textarea name="sozlesme_notu" class="form-control" rows="2" placeholder="Örn: Seans aralığı 15 günü geçemez."></textarea>
                  </div>

                  <div class="col-md-12">
                     <hr style="margin:6px 0 16px;">
                     <div style="background:#f5f3fb; border:1px solid #e3dcf2; border-radius:8px; padding:14px 16px;">
                        <p style="font-weight:700; margin:0 0 4px; color:#5C008E;"><i class="fa fa-building"></i> Salon Yetkilisi İmzası <span style="color:red;">*</span></p>
                        <p style="font-size:12px; color:#666; margin:0 0 12px;">Sözleşme çift taraflı imzalanır. Salon adına aşağıyı doldurup imzalayın; müşteri kendi imzasını SMS ile gelen link üzerinden atacaktır.</p>
                        <div class="row">
                           <div class="col-md-6 form-group">
                              <label><b>Yetkili Ad Soyad</b></label>
                              <input type="text" name="salon_yetkili_ad" id="sozlesme_salon_ad" class="form-control" value="{{ $isletme->yetkili_adi ?? optional(Auth::guard('isletmeyonetim')->user())->name ?? '' }}">
                           </div>
                           <div class="col-md-6 form-group">
                              <label><b>Yetkili Telefon</b></label>
                              <input type="tel" name="salon_yetkili_telefon" id="sozlesme_salon_tel" class="form-control" value="{{ $isletme->yetkili_telefon ?? '' }}">
                           </div>
                        </div>
                        <label><b>İmza</b> <small class="text-muted">(parmağınızla veya farenizle imzalayın)</small></label>
                        <canvas id="sozlesme_salon_imza_canvas" width="100%" height="160"
                           style="width:100%; height:160px; display:block; border:2px dashed #c7b8e6; border-radius:8px; background:#fff; cursor:crosshair;"></canvas>
                        <input type="hidden" name="salon_imza" id="sozlesme_salon_imza">
                        <div style="text-align:right; margin-top:6px;">
                           <button type="button" class="btn btn-sm btn-outline-secondary" id="sozlesmeSalonImzaTemizle"><i class="fa fa-eraser"></i> Temizle</button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
               <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Oluştur ve Müşteriye Gönder</button>
            </div>
         </div>
      </form>
   </div>
</div>
<script>
function sozlesmeTlFormat(num){
   if(num === null || num === undefined || num === '') return '';
   var n = parseFloat(num);
   if(isNaN(n)) return '';
   return n.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function sozlesmeTlParse(str){
   if(!str) return 0;
   var s = String(str).replace(/[^0-9,.-]/g,'').replace(/\./g,'').replace(',','.');
   var n = parseFloat(s);
   return isNaN(n) ? 0 : n;
}
function sozlesmeTlInputBind(displayId, hiddenId){
   var $disp = $('#'+displayId), $hid = $('#'+hiddenId);
   $disp.off('input.tl blur.tl');
   $disp.on('input.tl', function(){
      var raw = $(this).val();
      var parsed = sozlesmeTlParse(raw);
      $hid.val(parsed);
   });
   $disp.on('blur.tl', function(){
      var parsed = sozlesmeTlParse($(this).val());
      if(parsed > 0) $(this).val(sozlesmeTlFormat(parsed));
      $hid.val(parsed);
   });
}

@php
   // Tum salonlar icin gecerli varsayilan (fabrika) sozlesme metni
   $sozlesmeFabrikaMetin = <<<'SOZLESME_TXT'
1- SÖZLEŞMENİN KONUSU VE KAPSAMI
İşbu sözleşmenin konusu, MÜŞTERİ tarafından MERKEZ'den satın aldığı aşağıda detayları belirtilen lazer, bakım ve güzellik hizmetlerinin (bundan böyle "HİZMET" olarak anılacaktır) şartlarının, hizmetlerin sunulmasının, ödeme koşullarının ve tarafların hak ve yükümlülüklerinin belirlenmesidir.
2- ÖDEME ŞEKLİ VE KOŞULLARI
2.1- ÖDEME YÖNTEMİ
Nakit, Kredi Kart (tek çekim, taksit), elden taksit (vade tarihleri ekli ödeme planında belirtilir)
2.2- Müşteri taksitli işlemlerde ödemeleri belirtilen vadelerde yapmakla yükümlüdür. Ödemelerin gecikmesi durumunda MERKEZ, yasal faiz talep etme ve kalan borcunun tamamını muaccel kılma hakkını saklı tutar.
2.3- Hizmet bedeli ödenmeden veya ödeme planına uygulamadan hizmetin ifasına devam edilip edilmeyeceği MERKEZ'in inisiyatifindedir.
3- TARAFLARIN HAK VE YÜKÜMLÜLÜKLERİ
3.1- MERKEZ'İN YÜKÜMLÜLÜKLERİ
Merkez, hizmeti mesleki standartlara uygun, hijyen kurallarına bağlı, konusunda uzman personel tarafından ve taahhüt edilen standartlarda sunmak, kullanılan cihaz ve ürünlerin standartlara uygunluğunu sağlamak ve müşteriye sözleşme şartlarına uygun olarak hizmet vermekle yükümlüdür.
3.2- MÜŞTERİ'NİN YÜKÜMLÜLÜKLERİ VE SAĞLIK BEYANI
Sağlık Beyanı: Müşteri, hamilelik, epilepsi, kalp pili, açık yara, cilt hastalıkları, kanser tedavisi, hormon bozuklukları veya düzenli kullandığı ilaçlar gibi hizmetin uygulanmasında engel olabilecek veya risk oluşturabilecek tüm sağlık durumlarını MERKEZ'e yazılı olarak bildirmek zorundadır.
Müşteri, yanlış veya eksik sağlık beyanından kaynaklanabilecek komplikasyonlardan, yan etkilerden veya hizmetin sonuç vermemesinden MERKEZ'in sorumlu tutulmayacağını kabul ve beyan eder.
İşlem Sonrası Bakım: Müşteri, işlem sonrasında kendisine iletilen (güneşten korunma, su teması vb.) bakım talimatlarına uymak zorundadır. Talimatlara uyulmaması sonucu oluşacak leke, tahriş veya sonuç almama durumlarında MERKEZ sorumlu değildir. Müşteri, işbu sorumluluğun kendisinde olduğunu kabul ve beyan edip, tüm talimatlara eksiksiz uyacağını taahhüt eder.
3.3- TIBBİ İŞLEM UYARISI
Müşteri, MERKEZ'de uygulanan işlemlerin birer "tıbbi tedavi" veya "hastalık teşhis, tedavi yöntemi" olmadığını ve bakım amaçlı uygulamalar olduğunu, %100 sonuç garantisi verilmeyeceğini (kıl yapısı, hormon dengesi, cilt tipi gibi biyolojik faktörlere bağlı olarak) bildiğini kabul eder. Ve hizmetin etkilerinin kişisel özelliklere göre değişebileceğini, garanti sonuç talep etmeyeceğini, kendisinden kaynaklı bir durum ortaya çıktığında bunun MERKEZ'den kaynaklı olmadığını kabul ve beyan eder.
4- RANDEVU, İPTAL VE ERTELEME POLİTİKASI
4.1- MERKEZ, planlanmış randevulara ilişkin hatırlatma mesajını müşterinin bildirdiği telefon numarasına SMS, WhatsApp yolu ile bilgilendirme yapmakla yükümlüdür.
4.2- Müşteri de randevu saatine tam zamanında gelmekle yükümlüdür. 15 dakikayı aşan gecikmelerde MERKEZ, seansı iptal etme ve süreyi kısaltma hakkına sahiptir.
4.3- Randevu iptali veya erteleme talepleri, randevu saatinden en az 24 saat önce MERKEZ'e bildirilmelidir.
4.4- Mazeretsiz Gelmeme (No-Show): 24 saat önceden haber verilmeksizin randevuya gelinmemesi durumunda, ilgili seans "kullanılmış, yapılmış" sayılır ve paket hakkından düşülür. Müşteri bu durumda herhangi bir hak iddia edemez. Müşteri bu durumu eksiksiz kabul ve beyan eder.
4.5- Alınan hizmet paketleri, sözleşmede belirtilen süre içerisinde kullanılmalıdır. MÜŞTERİ'nin kendi kusurlarından kaynaklanan gecikmelerde süre uzatımı talep edemez. Ancak MERKEZ mücbir bir sebep varlığında ya da işletmeden kaynaklı zorunluluklar halinde süre uzatımı yapabilir.
5- CAYMA HAKKI, FESİH VE İADE KOŞULLARI
5.1- Cayma Hakkı: Müşteri sözleşmenin imzalandığı tarihten itibaren 14 (on dört) gün içinde, hizmet alımına başlanmamış olması kaydıyla, herhangi bir gerekçe göstermeksizin ve cezai şart ödemeksizin sözleşmeden cayma hakkına sahiptir.
5.2- Hizmet Başladıktan Sonra Fesih: Hizmetin ifasına başlandıktan (ilk seans yapıldıktan) sonra mücbir nedenlerle sözleşmenin feshedilmesi durumunda; kullanılan seanslar liste fiyatı (indirimli paket fiyatı değil, tek seans birim fiyatı) üzerinden hesaplanır. Toplam ödenen tutardan, kullanılan seansların liste fiyatı bedeli düşülerek kalan tutar iade edilir. Ancak MÜŞTERİ tarafından keyfi nedenlerle sözleşmenin feshedilmesi durumunda işbu sözleşme muaccel hale gelir ve ödenen bedeller geri iade edilmez. Müşteri bunu kabul ettiğini beyan eder.
5.3- MERKEZ'den kaynaklanan kusurlu hizmet (ayıplı hizmet) durumunda, MÜŞTERİ'nin 6502 sayılı kanundan doğan bedel iadesi veya hizmetin yeniden görülmesi hakları saklıdır.
6- HİZMETİN DEVİR VE İADESİ
6.1- İşbu yapılan hizmet sözleşmesi sadece sözleşmeyi imzalayan MÜŞTERİ'yi bağlar. Alınan hizmet herhangi başka birine devredilemez.
6.2- MÜŞTERİ tarafından alınan hizmet bir başkasına satılamaz ve ücret yerine kullanılamaz.
6.3- Müşteri getireceği Resmi Sağlık Kurumu Raporu ile hizmetin kesin olarak alınamayacağını belgelemesi halinde kullanılmayan seansların bedeli iade edilir.
6.4- Peşin ödemelerde yasal zorunluluklar dışında iade yapılmaz.
6.5- MÜŞTERİ, kendisi adına uygulanmış olan kampanya, indirim veya özel fiyatla alınan hizmetleri farklı biri üzerinde kullanamaz.
7- KİŞİSEL VERİLERİN KORUNMASI (KVKK)
7.1- MÜŞTERİ, bu sözleşme kapsamında verdiği kişisel verilerin (kimlik, iletişim, sağlık bilgileri, işlem öncesi ve sonrası fotoğraflar, rıza dahilinde çekilen videolar ve fotoğraflar vb.) 6698 sayılı KVKK kapsamında hizmetin ifası, randevu takibi ve yasal yükümlülükler nedeniyle MERKEZ tarafından işlenmesine, saklanmasına ve mevzuatın izin verdiği kurumlarla, MERKEZ'in yönettiği sosyal medya hesaplarında (Instagram, Facebook, TikTok vb.) paylaşılmasına açık rıza gösterdiğini beyan eder.
7.2- Müşteri yukarıda belirtilen ve MERKEZ'in sosyal medya hesaplarında paylaşılması için video, fotoğraf, görüntü vb. gibi alınan içeriklerin paylaşılmasına açık rıza göstermiyorsa işbu sözleşme ile birlikte imzalanan KVKK aydınlatma metni ve açık rıza formu imzalatılmıştır.
8- YETKİLİ MAHKEMELER VE YÜRÜRLÜK
İşbu sözleşmeden doğacak uyuşmazlıklarda, Tüketici Hakem Heyetleri ve ......................................... Mahkemeleri ve İcra daireleri yetkilidir. İşbu sözleşme 8 (sekiz) maddeden ibaret olup taraflarca iki nüsha olarak tanzim ve imza edilmiştir.
SOZLESME_TXT;
   try {
      $sozlesmeSalonVarsayilan = \DB::table('salonlar')->where('id',$isletme->id)->value('sozlesme_varsayilan_metin');
   } catch(\Exception $e){ $sozlesmeSalonVarsayilan = null; }
@endphp
// Salonun kaydettigi varsayilan metin (bir defa kaydedilir, sonraki sozlesmelerde otomatik dolu gelir)
var sozlesmeSalonVarsayilan = {!! json_encode($sozlesmeSalonVarsayilan) !!};
// Tum salonlar icin gecerli fabrika varsayilani (salon kendi metnini kaydetmediyse bu gelir)
var sozlesmeFabrikaVarsayilan = {!! json_encode($sozlesmeFabrikaMetin) !!};

function sozlesmeVarsayilanMetin(){
   if(sozlesmeSalonVarsayilan && String(sozlesmeSalonVarsayilan).trim()){
      return String(sozlesmeSalonVarsayilan);
   }
   return sozlesmeFabrikaVarsayilan;
}

// --- Salon yetkilisi imza canvas (cift tarafli imza) ---
var sozlesmeSalonImzaCizildi = false;
function sozlesmeSalonImzaKur(){
   var c = document.getElementById('sozlesme_salon_imza_canvas');
   if(!c) return;
   var ctx = c.getContext('2d');
   c.width = c.offsetWidth; c.height = 160;
   ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#222';
   sozlesmeSalonImzaCizildi = false;
   var ciziyor = false;
   function pos(e){ var r = c.getBoundingClientRect(); var t = e.touches ? e.touches[0] : e; return { x: t.clientX - r.left, y: t.clientY - r.top }; }
   function start(e){ e.preventDefault(); ciziyor = true; sozlesmeSalonImzaCizildi = true; var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }
   function draw(e){ if(!ciziyor) return; e.preventDefault(); var p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }
   function end(){ ciziyor = false; }
   // onceki bagli olaylari temizlemek icin klon
   var yeni = c.cloneNode(true); c.parentNode.replaceChild(yeni, c); c = yeni; ctx = c.getContext('2d');
   c.width = c.offsetWidth; c.height = 160;
   ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#222';
   c.addEventListener('mousedown', start); c.addEventListener('mousemove', draw);
   c.addEventListener('mouseup', end); c.addEventListener('mouseleave', end);
   c.addEventListener('touchstart', start); c.addEventListener('touchmove', draw); c.addEventListener('touchend', end);
}

$(document).ready(function(){
   $(document).on('click', '#sozlesmeSalonImzaTemizle', function(){
      var c = document.getElementById('sozlesme_salon_imza_canvas');
      if(c){ c.getContext('2d').clearRect(0,0,c.width,c.height); sozlesmeSalonImzaCizildi = false; $('#sozlesme_salon_imza').val(''); }
   });
   // Modal açılınca select2'yi yeniden başlat (dialog içindeki select için gerekli)
   $('#sozlesmeOlusturModal').on('shown.bs.modal', function(){
      if(!$('#sozlesme_metni').val().trim()){
         $('#sozlesme_metni').val(sozlesmeVarsayilanMetin());
      }
      sozlesmeSalonImzaKur();
      sozlesmeTlInputBind('sozlesme_toplam_display', 'sozlesme_toplam');
      sozlesmeTlInputBind('sozlesme_kapora_display', 'sozlesme_kapora');
      try {
         if($('#sozlesme_musteri').data('select2')) $('#sozlesme_musteri').select2('destroy');
      } catch(e){}
      $('#sozlesme_musteri').select2({
         placeholder: 'Müşteri seçin (en az 2 karakter)',
         allowClear: true,
         dropdownParent: $('#sozlesmeOlusturModal'),
         minimumInputLength: 2,
         language: {
            inputTooShort: function(){ return 'En az 2 karakter girin.'; },
            searching: function(){ return 'Aranıyor...'; },
            noResults: function(){ return 'Sonuç bulunamadı.'; }
         },
         ajax: {
            url: '/isletmeyonetim/musteri-arama-bolumu-verileri',
            dataType: 'json',
            delay: 250,
            data: function(params){
               return { query: params.term || '', normalized_query: params.term || '', sube: '{{$isletme->id}}', aramaMi: false };
            },
            processResults: function(data){
               return { results: data.map(function(m){ return { id: m.id, text: m.ad_soyad }; }) };
            }
         }
      });
   });

   // Müşteri seçimi → telefonu otomatik doldur (endpoint: musteri_id, dönen: telefon)
   $(document).on('change','#sozlesme_musteri', function(){
      var musteriId = $(this).val();
      $('#sozlesme_telefon').val('');
      if(!musteriId || musteriId === '0') return;
      $.ajax({
         url: '/isletmeyonetim/formmusteribilgigetir',
         type: 'GET',
         dataType: 'json',
         data: { musteri_id: musteriId, sube: '{{$isletme->id}}' },
         success: function(result){
            if(result && result.telefon){
               $('#sozlesme_telefon').val(result.telefon);
            }
         }
      });
   });
   // Paket seçilince fiyat/seans otomatik doldur
   $('#sozlesme_paket').on('change', function(){
      var sel = $(this).find('option:selected');
      var fiyat = sel.data('fiyat'); var seans = sel.data('seans');
      if(fiyat){
         $('#sozlesme_toplam').val(fiyat);
         $('#sozlesme_toplam_display').val(sozlesmeTlFormat(fiyat));
      }
      if(seans) $('#sozlesme_seans').val(seans);
   });
   // Hizmet seçilince fiyat doldur
   $('#sozlesme_hizmet').on('change', function(){
      var fiyat = $(this).find('option:selected').data('fiyat');
      if(fiyat && !$('#sozlesme_toplam').val()){
         $('#sozlesme_toplam').val(fiyat);
         $('#sozlesme_toplam_display').val(sozlesmeTlFormat(fiyat));
      }
   });
   // "Bu metni varsayılan yap" → salon için kaydet, sonraki sözleşmelerde otomatik gelir
   $('#sozlesmeVarsayilanKaydetBtn').on('click', function(){
      var metin = $('#sozlesme_metni').val();
      if(!metin || !metin.trim()){
         Swal.fire('Uyarı', 'Önce sözleşme şartlarını yazın.', 'warning');
         return;
      }
      var $b = $(this);
      $b.prop('disabled', true);
      $.ajax({
         url: '/isletmeyonetim/sozlesme-varsayilan-kaydet',
         type: 'POST',
         dataType: 'json',
         data: { sozlesme_metni: metin, _token: '{{ csrf_token() }}' },
         success: function(resp){
            $b.prop('disabled', false);
            if(resp && resp.basarili){
               sozlesmeSalonVarsayilan = metin; // bu oturumda da güncel kalsın
               Swal.fire('Kaydedildi', 'Bu metin varsayılan sözleşme şartlarınız olarak kaydedildi. Bundan sonra otomatik dolu gelecek.', 'success');
            } else {
               Swal.fire('Hata', (resp && resp.mesaj) ? resp.mesaj : 'Kaydedilemedi.', 'error');
            }
         },
         error: function(xhr){
            $b.prop('disabled', false);
            Swal.fire('Hata', 'Sunucu hatası: '+xhr.status, 'error');
         }
      });
   });

   // Form submit
   $('#sozlesmeOlusturForm').on('submit', function(e){
      e.preventDefault();
      // Salon yetkilisi imzasini hidden input'a yaz + zorunlu kontrol
      var sc = document.getElementById('sozlesme_salon_imza_canvas');
      if(!sozlesmeSalonImzaCizildi || !sc){
         Swal.fire('İmza Gerekli', 'Salon yetkilisi imzası zorunludur. Lütfen imza alanına imzanızı atın.', 'warning');
         return;
      }
      $('#sozlesme_salon_imza').val(sc.toDataURL('image/png'));
      var $btn = $(this).find('button[type=submit]');
      $btn.prop('disabled', true).text('Gönderiliyor...');
      $.ajax({
         url: '/isletmeyonetim/sozlesme-olustur',
         type: 'POST',
         dataType: 'json',
         data: $(this).serialize(),
         success: function(resp){
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Sözleşmeyi Gönder');
            if(resp && resp.basarili){
               $('#sozlesmeOlusturModal').modal('hide');
               Swal.fire('Başarılı', 'Sözleşme oluşturuldu ve müşteriye SMS gönderildi.', 'success');
               setTimeout(function(){ location.reload(); }, 1500);
            } else {
               Swal.fire('Hata', (resp && resp.mesaj) ? resp.mesaj : 'Bir hata oluştu.', 'error');
            }
         },
         error: function(xhr){
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Sözleşmeyi Gönder');
            Swal.fire('Hata', 'Sunucu hatası: '+xhr.status, 'error');
         }
      });
   });
});
</script>
