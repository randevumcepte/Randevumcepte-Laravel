$ErrorActionPreference = "Stop"

# ---- Renk sabitleri (RRGGBB) ----
$MOR='5C008E'; $PLUM='2B0A45'; $GOLD='C9A24B'; $INK='241832'; $GRI='5A526E'
$KREM='E7D3A0'; $MORYAZI='E6D9F2'; $ALTINBG='FDF7E8'; $CIZGI='ECE7F4'; $BEYAZ='FFFFFF'

function Esc([string]$s){
  if($null -eq $s){return ''}
  $s=$s -replace '&','&amp;'; $s=$s -replace '<','&lt;'; $s=$s -replace '>','&gt;'
  return $s
}
# Run: metin, renk, kalin, boyut(yarim punto), italik, font
function Rn([string]$t,[string]$color,[bool]$bold,[int]$sz,[bool]$ital=$false,[string]$font='Segoe UI'){
  $rpr='<w:rPr><w:rFonts w:ascii="'+$font+'" w:hAnsi="'+$font+'"/>'
  if($bold){$rpr+='<w:b/>'}
  if($ital){$rpr+='<w:i/>'}
  $rpr+='<w:color w:val="'+$color+'"/><w:sz w:val="'+$sz+'"/></w:rPr>'
  return '<w:r>'+$rpr+'<w:t xml:space="preserve">'+(Esc $t)+'</w:t></w:r>'
}
function P([string]$runs,[string]$ppr=''){ return '<w:p><w:pPr>'+$ppr+'</w:pPr>'+$runs+'</w:p>' }
function PC([string]$runs,[int]$after){ return P $runs ('<w:jc w:val="center"/><w:spacing w:before="0" w:after="'+$after+'"/>') }

function Bar([string]$kicker,[string]$title){
  $p1=P (Rn ($kicker.ToUpper()) $KREM $true 15) '<w:shd w:val="clear" w:color="auto" w:fill="5C008E"/><w:spacing w:before="60" w:after="0"/><w:ind w:left="80"/>'
  $r2='<w:r><w:rPr><w:rFonts w:ascii="Georgia" w:hAnsi="Georgia"/><w:b/><w:color w:val="FFFFFF"/><w:sz w:val="30"/></w:rPr><w:t xml:space="preserve">'+(Esc $title)+'</w:t></w:r>'
  $p2=P $r2 '<w:shd w:val="clear" w:color="auto" w:fill="5C008E"/><w:spacing w:before="0" w:after="80"/><w:ind w:left="80"/>'
  return $p1+$p2
}
function ArtHead([string]$no,[string]$title){
  $runs=(Rn ($no+'. ') $GOLD $true 24)+(Rn $title $MOR $true 24)
  return P $runs '<w:spacing w:before="160" w:after="50"/>'
}
function Clause([string]$id,[string]$text){
  $runs=(Rn ($id+'  ') $GOLD $true 19)+(Rn $text $INK $false 19)
  return P $runs '<w:spacing w:before="0" w:after="70"/><w:ind w:left="220"/><w:jc w:val="both"/>'
}
function Def([string]$term,[string]$text){
  $runs=(Rn ($term+' ') $MOR $true 19)+(Rn $text $INK $false 19)
  return P $runs '<w:spacing w:before="0" w:after="70"/><w:ind w:left="220"/><w:jc w:val="both"/>'
}
$PB='<w:p><w:r><w:br w:type="page"/></w:r></w:p>'

$bordL='<w:tblBorders><w:top w:val="single" w:sz="4" w:color="ECE7F4"/><w:left w:val="single" w:sz="4" w:color="ECE7F4"/><w:bottom w:val="single" w:sz="4" w:color="ECE7F4"/><w:right w:val="single" w:sz="4" w:color="ECE7F4"/><w:insideH w:val="single" w:sz="4" w:color="ECE7F4"/><w:insideV w:val="single" w:sz="4" w:color="ECE7F4"/></w:tblBorders>'
$bordGold='<w:tblBorders><w:top w:val="single" w:sz="8" w:color="C9A24B"/><w:left w:val="single" w:sz="8" w:color="C9A24B"/><w:bottom w:val="single" w:sz="8" w:color="C9A24B"/><w:right w:val="single" w:sz="8" w:color="C9A24B"/><w:insideH w:val="single" w:sz="4" w:color="ECE7F4"/><w:insideV w:val="single" w:sz="4" w:color="ECE7F4"/></w:tblBorders>'
$bordNone='<w:tblBorders><w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/><w:insideH w:val="none"/><w:insideV w:val="none"/></w:tblBorders>'
$cmar='<w:tcMar><w:top w:w="70" w:type="dxa"/><w:left w:w="130" w:type="dxa"/><w:bottom w:w="70" w:type="dxa"/><w:right w:w="130" w:type="dxa"/></w:tcMar>'

# ======== KAPAK ========
$cov=''
$cov+=PC (Rn 'RANDEVUM CEPTE' $BEYAZ $true 30) 40
$cov+=PC (Rn 'DİJİTAL ÇÖZÜM ORTAĞINIZ' $KREM $true 16) 240
$cov+=PC (Rn 'RESMİ SÖZLEŞME' $GOLD $true 22) 80
$covTitle='<w:r><w:rPr><w:rFonts w:ascii="Georgia" w:hAnsi="Georgia"/><w:b/><w:color w:val="FFFFFF"/><w:sz w:val="60"/></w:rPr><w:t xml:space="preserve">'+(Esc 'Satış ve Hizmet Sözleşmesi')+'</w:t></w:r>'
$cov+=PC $covTitle 200
$cov+=PC (Rn 'İşbu sözleşme, Randevum Cepte ile müşteri arasında sunulan dijital hizmetlerin kapsamını, tarafların hak ve yükümlülüklerini, mali koşulları ve gizlilik esaslarını düzenler.' $MORYAZI $false 22) 300
$cov+=PC (Rn 'Düzenleyen: Randevum Cepte      •      Geçerlilik: 1 (bir) yıl      •      11 Ana Madde' $BEYAZ $true 20) 260
$cov+=PC (Rn 'Bu sözleşme 24.01.2015 tarihli ve 29246 sayılı Resmi Gazete''de yayımlanan "Abonelik Sözleşmeleri Yönetmeliği" esas alınarak hazırlanmıştır.' $KREM $false 18) 0
$coverTbl='<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/>'+$bordNone+'</w:tblPr><w:tblGrid><w:gridCol w:w="10488"/></w:tblGrid><w:tr><w:trPr><w:trHeight w:hRule="exact" w:val="14600"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="5000" w:type="pct"/><w:shd w:val="clear" w:color="auto" w:fill="5C008E"/><w:vAlign w:val="center"/><w:tcMar><w:left w:w="600" w:type="dxa"/><w:right w:w="600" w:type="dxa"/></w:tcMar></w:tcPr>'+$cov+'</w:tc></w:tr></w:tbl>'

# ======== TARAFLAR TABLOSU ========
function HeadCell([string]$text,[string]$fill){
  return '<w:tc><w:tcPr><w:tcW w:w="2500" w:type="pct"/><w:shd w:val="clear" w:color="auto" w:fill="'+$fill+'"/>'+$cmar+'</w:tcPr>'+(P (Rn $text $BEYAZ $true 18)) +'</w:tc>'
}
function BodyCell([string[]]$lines,[string]$color){
  $ps=''
  foreach($ln in $lines){
    if($ln -eq ''){ $ps+=P '' '<w:spacing w:after="40"/>' }
    else { $ps+=P (Rn $ln $color $false 17) '<w:spacing w:after="40"/>' }
  }
  return '<w:tc><w:tcPr><w:tcW w:w="2500" w:type="pct"/>'+$cmar+'</w:tcPr>'+$ps+'</w:tc>'
}
$supLines=@(
  'Ünvanı: WEB FİRMAM İNTERNET HİZ. REK. SAN. TİC. LTD. ŞTİ.',
  'Adresi: Adalet, Şht. Polis Fethi Sekin Cd. No: 6 Kat:3 Ofis32 Bayraklı / İzmir',
  'V.D / V.N: Karşıyaka · 8000544090',
  'Telefon: 0541 294 81 44',
  'E-mail: info@webfirmam.com.tr'
)
$cusLines=@(
  'Ad Soyad / Ünvan: ____________________','',
  'Adresi: ____________________','',
  'V.D / V.N: ____________________','',
  'Telefon: ____________________','',
  'E-mail: ____________________'
)
$partiesTbl='<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/>'+$bordL+'<w:tblLayout w:type="fixed"/></w:tblPr><w:tblGrid><w:gridCol w:w="5244"/><w:gridCol w:w="5244"/></w:tblGrid>'+
  '<w:tr>'+(HeadCell 'SAĞLAYICI BİLGİLERİ' $MOR)+(HeadCell 'MÜŞTERİ BİLGİLERİ' $PLUM)+'</w:tr>'+
  '<w:tr>'+(BodyCell $supLines $INK)+(BodyCell $cusLines $GRI)+'</w:tr>'+
  '</w:tbl>'+(P '' '<w:spacing w:after="60"/>')

# ======== HİZMET DETAYLARI TABLOSU ========
function SdRow([string]$k,[string]$v,[bool]$hi){
  $shd = if($hi){'<w:shd w:val="clear" w:color="auto" w:fill="FDF7E8"/>'}else{''}
  $kc='<w:tc><w:tcPr><w:tcW w:w="2300" w:type="pct"/>'+$shd+$cmar+'</w:tcPr>'+(P (Rn $k $GRI $false 19))+'</w:tc>'
  $vc='<w:tc><w:tcPr><w:tcW w:w="2700" w:type="pct"/>'+$shd+$cmar+'</w:tcPr>'+(P (Rn $v $MOR $true 22) '<w:jc w:val="right"/>')+'</w:tc>'
  return '<w:tr>'+$kc+$vc+'</w:tr>'
}
$sdTbl='<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/>'+$bordGold+'<w:tblLayout w:type="fixed"/></w:tblPr><w:tblGrid><w:gridCol w:w="4820"/><w:gridCol w:w="5668"/></w:tblGrid>'+
  (SdRow 'Başlangıç Tarihi' '…… / …… / ………' $false)+
  (SdRow 'Bitiş Tarihi' '…… / …… / ………' $false)+
  (SdRow 'Geçerlilik Süresi' '1 (bir) yıl' $false)+
  (SdRow 'Ödeme Periyodu' 'Peşin' $false)+
  (SdRow 'İlk Yıl Kullanım Süresi' '16 AY  (12 + 4 hediye)' $true)+
  '</w:tbl>'

# ======== HOŞGELDİN KUTUSU ========
$hgTitle=P (Rn 'HOŞGELDİN KAMPANYASI — Yeni Müşteri' $MOR $true 22) '<w:shd w:val="clear" w:color="auto" w:fill="FDF7E8"/><w:spacing w:before="120" w:after="0"/><w:ind w:left="120" w:right="120"/>'
$hgBody=P (Rn 'Aramıza yeni katılan müşterilerimize özel; standart 12 aylık kullanım süresine ek olarak 4 ay hediye verilir → toplam 16 ay kullanım. Bu 4 aylık ek süre, yalnızca ilk yıla özel hoşgeldin kampanyası kapsamında tanımlanır. Takip eden yıllardaki uzatmalarda kullanım süresi 12 ay olarak uygulanır.' $INK $false 19) '<w:shd w:val="clear" w:color="auto" w:fill="FDF7E8"/><w:spacing w:before="0" w:after="120"/><w:ind w:left="120" w:right="120"/><w:jc w:val="both"/>'

# ======== İMZA TABLOSU ========
function SignCell([string[]]$lines){
  $ps=''
  $i=0
  foreach($ln in $lines){
    $i++
    if($ln -eq ''){ $ps+=P '' '<w:jc w:val="center"/><w:spacing w:after="60"/>'; continue }
    if($i -eq 1){ $ps+=PC (Rn $ln $MOR $true 18) 80 }
    elseif($ln -like '____*'){ $ps+=PC (Rn $ln $GRI $false 18) 40 }
    elseif($ln -eq 'KAŞE VE İMZA'){ $ps+=PC (Rn $ln $GOLD $true 16) 0 }
    else { $ps+=PC (Rn $ln $INK $false 17) 60 }
  }
  return '<w:tc><w:tcPr><w:tcW w:w="2500" w:type="pct"/><w:tcMar><w:top w:w="140" w:type="dxa"/><w:left w:w="160" w:type="dxa"/><w:bottom w:w="180" w:type="dxa"/><w:right w:w="160" w:type="dxa"/></w:tcMar></w:tcPr>'+$ps+'</w:tc>'
}
$mLines=@('MÜŞTERİ','','Müşteri Adı Soyadı / Ünvan','','"Bu sözleşmeyi imzalamadan önce tüm maddeleri okudum, anladım, aynen kabul ediyorum."','','','____________________________','KAŞE VE İMZA')
$sLines=@('SAĞLAYICI','','Randevum Cepte Adına','','WEB FİRMAM İNTERNET HİZ. REK. SAN. TİC. LTD. ŞTİ.','','','____________________________','KAŞE VE İMZA')
$signTbl='<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/>'+$bordL+'<w:tblLayout w:type="fixed"/></w:tblPr><w:tblGrid><w:gridCol w:w="5244"/><w:gridCol w:w="5244"/></w:tblGrid><w:tr>'+(SignCell $mLines)+(SignCell $sLines)+'</w:tr></w:tbl>'

# ================= GÖVDE =================
$body=''
$body+=$coverTbl+$PB

$body+=Bar 'Sözleşme · Madde 01' 'Taraflar ve Tanımlar'
$body+=Clause '1.a.' 'Bu sözleşme 24.01.2015 tarihli ve 29246 sayılı Resmi Gazete''de yayımlanarak yürürlüğe giren "Abonelik Sözleşmeleri Yönetmeliği" esas alınarak hazırlanmıştır. Randevum Cepte ile müşteri arasında, tarafların hür iradeleriyle incelenip onaylanarak aşağıda belirtilen şartlarda imza altına alınmıştır.'
$body+=Clause '1.b.' 'RANDEVUM CEPTE ve müşteri birlikte taraflar olarak anılacaktır.'
$body+=Clause '1.c.' 'Bu sözleşmede kullanılan terim ve sözcükler, yürürlükteki mevzuata aykırı düşmemesi şartıyla, aşağıdaki anlamlarda kullanılmış olup sözleşmede tanımı bulunmayan terim ve sözcükler için ilgili mevzuatta yapılan tanımlamalar geçerli olacaktır.'
$body+=P '' '<w:spacing w:after="40"/>'
$body+=$partiesTbl
$body+=ArtHead '1' 'Marka ve Taraf Tanımları'
$body+=Clause '1.d.' 'Randevum Cepte bir Web Firmam İnternet Hizmetleri Reklam Sanayi Ticaret Limited Şirketi Markasıdır.'
$body+=Clause '1.e.' 'Web Firmam İnternet Hizmetleri Reklam Sanayi Ticaret Limited Şirketi, sözleşmenin sağlayıcı bilgileri bölümünde bilgileri bulunan (Bundan sonra RANDEVUM CEPTE olarak anılacaktır) İle sözleşmenin müşteri bilgileri bölümünde bilgileri bulunan, yönetici kurulunda olan ve karar defterinde ismi geçen dernek yönetici ve yönetim kurulu olarak tanımlanan, yetkili kılınmış gerçek ve ya tüzel kişileri, (Bundan sonra MÜŞTERİ olarak anılacaktır).'
$body+=Def 'Kullanıcı;' 'Randevum Cepte hizmetlerini kullanan müşteri tarafından sunulan hizmetlerden veya ürünlerden faydalanan müşterinin, internet sitesine erişmesi için yetkilendirdiği üyelerin, gerçek ve ya tüzel kişilerin her birini,'
$body+=Def 'İnternet Sitesi;' 'www.randevumcepte.com.tr adresinde faaliyet gösteren veya bu adrese yönlendirilmiş alan adları ile Randevum Cepte tarafından oluşturulan internet sitesini,'
$body+=Def 'Ziyaretçi;' 'İnternet sitesini müşteri veya kullanıcı olmaksızın ziyaret eden üçüncü kişileri,'
$body+=Def 'Hizmet;' 'Yönetilen Salonun Randevum Cepte tarafından geliştirilmiş platformun bu sözleşme ve ekleri ile belirlenmiş koşullar kapsamında kullanım hakkını,'
$body+=Def 'Ek belgeler;' 'Müşterinin, Randevum Cepte''nin sunduğu hizmeti kullanacağı anlaşma detaylarını içeren ek belgeleri,'
$body+=Def 'Ek Hizmetler;' 'Randevum Cepte tarafından sunulan / aracı olunan ve bu sözleşme hükümlerine tabi olan diğer hizmetleri,'
$body+=Def 'Mücbir Sebep;' 'Sözleşme''nin imzalandığı tarihte öngörülmesi mümkün olmayan ve tarafların dâhili olmaksızın gelişen ve tarafların sözleşme ile yüklendikleri borç ve sorumlulukları kısmen veya tamamen yerine getirmelerini imkânsızlaştıran doğal afetler, harp, seferberlik, yangın, grev ve lokavt veya hükümet veya resmi makamlarca alınmış kararlar ile altyapı sorunları, telefon şebekelerindeki arızalar, elektrik kesintileri veya benzeri halleri ifade eder.'
$body+=$PB

$body+=Bar 'Sözleşme · Madde 02 – 04' 'Süre, Kullanım Kuralları ve Sorumluluklar'
$body+=ArtHead '2' 'Süre'
$body+=Clause '2.a.' 'Sözleşmenin süresi imza tarihinden itibaren 1 (bir) yıldır. Yenilenen dönem için geçerli olacak ücretler sözleşmenin 5.d. ve 5.e. maddesine uygun olarak belirlenecektir. Yıllık aboneliklerde süre bitimini takip eden bir aylık sürede, aylık aboneliklerde ise süre bitimini takip eden bir hafta içerisinde hizmet bedelinin ödenmemesi durumunda Randevum Cepte tek taraflı olarak sözleşmeyi feshedip aboneliği sona erdirme ve abonenin veritabanını silme hakkını haizdir.'
$body+=Clause '2.b.' 'Sözleşme süresinin bitimine bir ay (1) kala taraflardan herhangi biri sözleşmeyi sonlandırdıklarını yazılı şekilde bildirmedikleri takdirde, sözleşme hükümleri aynı şartlarda bir yıllık uzatılır.'
$body+=ArtHead '3' 'Hizmet Kullanım Kuralları'
$body+=Clause '3.a.' 'Randevum Cepte, gerekli gördüğü durumlarda platformda geliştirme ve güncellemeler yapabilir. Müşteriler, web sitesi üzerinden iletişim formu, telefon veya e-posta ile talep ve şikayetlerini Randevum Cepte''ye iletebilir. Randevum Cepte, web sitesi üzerinden yapılan geri bildirimlerin tümünü kayıt altına alır ve en kısa sürede cevaplandırır.'
$body+=Clause '3.b.' 'Müşteri, Randevum Cepte müşteri hizmetleri ile yaptığı her türlü görüşme veya geri bildirimlerin Randevum Cepte tarafından kaydedileceğini ve bu kayıtların Randevum Cepte tarafından en fazla 1 (bir) sene süreyle saklanacağını ve hizmetlere ilişkin çıkabilecek uyuşmazlıklarda kullanılabileceğini peşinen kabul eder.'
$body+=Clause '3.d.' 'İnteraktif duyuru ve Santral sistemi, hizmet bedelini ödeme sürecini müteakiben BTK tarafından zorunlu tutulan belgeleri tarafımıza ulaştırmanız sonrasında aktivasyonu sağlanacaktır. (Hediye olarak verilen santral ve sms kullanımı için gerekli belgeler ayrıca İnteraktif duyuru ve Santral sistemi sözleşmesi sırasında belirtilecektir.)'
$body+=Clause '3.e.' 'Randevum Cepte dışında satın alınmış İnteraktif duyuru ve Santral sistemi paketleri Randevum Cepte paneli üzerinde herhangi bir geçerliliği bulunmamaktadır.'
$body+=Clause '3.f.' 'Aracı kuruluşlardan alınacak hizmetler bu kuruluşlar ile yapılacak müstakil sözleşmelerle belirlenecek olup, Randevum Cepte dışında değerlendirilecektir.'
$body+=ArtHead '4' 'Tarafların Sorumlulukları'
$body+=Clause '4.a.' 'Bu sözleşme kapsamında Randevum Cepte''nin asli sorumluluğu, hizmetlerin sunulması ve müşterinin asli sorumluluğu ise belirlenen ödemenin zamanında ve kararlaştırılan şekilde yapılması ile hizmetleri iş bu sözleşmede belirlenen şekilde kullanmasıdır.'
$body+=Clause '4.b.' 'Müşteri, bu sözleşmeyi akdetmeye yetkili olduğunu ve sözleşme ve eklerinde beyan ettiği bilgilerin doğruluğunu ve de hizmetleri yürürlükteki mevzuata uygun şekilde kullanacağını beyan, kabul ve taahhüt eder.'
$body+=Clause '4.c.' 'Müşteri, sunulan hizmetleri sözleşme tarihinde mevcut haliyle, "olduğu gibi" kabul eder. Randevum Cepte, müşterinin yazılımla ilgili tüm taleplerinin karşılanacağı garantisini vermez; ancak, hizmet kalitesini arttırmak, kapsamını genişletmek ya da çeşitlendirmek amacıyla hizmet içeriğinde, özelliklerinde veya modüllerde, müşteriye sözleşme tarihinde verilen hizmetlerde azalma olmamak üzere, değişiklik yapma hakkını saklı tutar.'
$body+=Clause '4.d.' 'Randevum Cepte, hizmetlerin kesintisiz ve sürekli olarak sunulması için gerekli önlemleri alacağını ve hizmetlerin herhangi bir nedenle kesintiye uğraması durumunda, mümkün olan en kısa süre içerisinde duruma müdahale edeceğini taahhüt eder. Ancak mücbir sebepler nedeniyle hizmetlerin aksaması veya kesintiye uğramasından ve Randevum Cepte''nin kastı ile oluşmayan diğer her türlü neden (madde 1. mücbir sebep) dolayısıyla uğranılacak kayıplardan ve zararlardan sorumlu tutulamaz.'
$body+=Clause '4.e.' 'Hizmetlerin doğrudan Randevum Cepte''den kaynaklanan bir nedenle, 48 (kırk sekiz) saat süreyle aralıksız kesintiye uğraması halinde müşteri sözleşmeyi fesih hakkını haizdir.'
$body+=Clause '4.f.' 'Randevum Cepte, sağladığı hizmetlerin hatalı kullanımlarından, hatalı veya mevzuata aykırı içerik girişinden, kullanıcılara ve üçüncü kişilere eposta ve kısa mesaj (SMS) ile yapılan bildirimlerden, müşterinin kullanımına özgülenen internet sitesinden doğabilecek maddi veya manevi zararlarından sorumlu tutulamaz. Hizmetlerin kullanımına ilişkin tüm sorumluluk yalnızca ve sadece müşteriye aittir.'
$body+=Clause '4.g.' 'Randevum Cepte site bakım ve onarım kapsamında hizmet sunumunu durdurması halinde bu çalışmanın başlangıç ve bitiş sürelerini müşteriye en az 8 saat öncesinden duyurmayı taahhüt eder.'
$body+=$PB

$body+=Bar 'Sözleşme · Madde 05 – 07' 'Mali Koşullar, Yaptırımlar ve Gizlilik'
$body+=ArtHead '5' 'Mali Koşullar'
$body+=Clause '5.a.' 'Randevum Cepte, ödemeleri nakit ya da kredi kartı aracılığı ile tek seferde yapılmaktadır. Müşteri, hizmetler karşılığında sözleşmede belirlenen ücreti peşinen ödemeyi kabul ve taahhüt eder.'
$body+=Clause '5.b.' 'Randevum Cepte, ödenen bedele ait faturayı müşteri veya temsil ettiği kurum adına düzenleyip 5 iş günü içinde posta ya da kargoya teslim etmeyi taahhüt eder. Posta ya da kargoda yaşanabilecek gecikmelerden Randevum Cepte sorumlu tutulamaz.'
$body+=Clause '5.c.' 'Hizmetlere ilişkin faturalar, gerekirse müşterinin talebi doğrultusunda elektronik haberleşme yöntemleri kullanılarak bilgilendirme amaçlı ulaştırılabilir.'
$body+=Clause '5.d.' 'Hizmet bedeli fiyat artışları, bir sonraki yılın asgari ücret zam yüzdeliğini geçmeyecek oranda yapılır. Randevum Cepte''nin belirtilen senelik artışları için müşteriye ayrıca bildirimde bulunma zorunluluğu yoktur.'
$body+=Clause '5.e.' 'Bu sözleşmede belirtilen hükümler dışında müşterinin herhangi bir nedenle kendi isteği dahilinde hizmet kullanımından vazgeçmesi durumunda veya yönetimin değişerek yeni yönetimin tarafımızdan sunulan hizmeti kullanmak istememesi durumunda müşteriye hizmet bedeli iadesi yapılmayacaktır.'
$body+=ArtHead '6' 'Yaptırımlar'
$body+=Clause '6.a.' 'Sözleşmenin ve hizmet kullanım kurallarının ihlali, müşteriye sağlanan hizmetlerin askıya alınması ya da sözleşmenin feshedilmesi ile internet sitesine erişimini tamamen iptal edilmesi sonucunu doğurabilir. Randevum Cepte''nin ihlaller konusunda müşteriye önceden herhangi bir bildirimde bulunma zorunluluğu yoktur.'
$body+=Clause '6.b.' 'Müşteri, hizmetlerin kullanımı sebebiyle, üçüncü kişilerin ve Randevum Cepte''nin uğrayabileceği tüm zararı kusuru oranında tazmin etmekle yükümlü olduğunu kabul, beyan ve taahhüt eder. Kullanıcı veya müşteri tarafından sözleşmenin ihlali sebebiyle Randevum Cepte''nin doğan zarar ve ziyanlarının tazmini için yasal yollara başvurma hakkı saklıdır.'
$body+=Clause '6.c.' 'Müşteri, hizmet kullanım kurallarının üçüncü kişiler tarafından kendi aleyhine ihlal edildiğini düşündüğü durumlarda, en geç 7 (yedi) gün içinde destek@randevumcepte.com.tr adresine ihlalin detaylarını içeren bilgi ve deliller ile durumu bildirmekle yükümlüdür. İhlalin, Randevum Cepte''ye bu süre içinde bildirilmemesi halinde veya bildirilse dahi Randevum Cepte''nin hukuken müdahale edemeyeceği hallerde, Randevum Cepte''nin herhangi bir sorumluluğu bulunmamaktadır.'
$body+=Clause '6.d.' 'Randevum Cepte, kendisine yapılan bildirim sonucunda ve ihlalin kesinleşmesi ile birlikte ihlal edenin kullanıcı veya müşteri olduğunun anlaşılması halinde ve gerekli görmesi halinde, kullanıcıya veya müşteriye, sözlü veya yazılı uyarıda bulunabilir; Kullanıcının veya müşterinin internet sitesine veya uygulamaya erişimini belirli bir süreliğine askıya alabilir; iş bu sözleşmeyi feshederek kullanıcının veya müşterinin internet sitesine veya uygulamaya erişimini tamamen iptal edebilir.'
$body+=ArtHead '7' 'Gizlilik'
$body+=Clause '7.a.' 'Randevum Cepte, müşteri''ye ait tüm bilgileri ve elde ettiği elektronik ortamdaki veri ve bilgileri yürürlükteki mevzuata uygun olarak sözleşme sona erse dahi gizlilik içerisinde tutacağını ve koruyacağını taahhüt eder.'
$body+=Clause '7.b.' 'Güvenlik, geliştirme ve destek amacıyla Randevum Cepte sunucularına yapılan tüm istekler IP (Internet Protocol) bazında kayıt altına alınabilir. Bu bilgiler kanuni zorunluluklar dışında üçüncü kişiler ile paylaşılmaz.'
$body+=Clause '7.c.' 'Randevum Cepte, müşterinin bilgilerinin yetkisiz erişime, kullanımına ve ifşasına karşı korumak için çeşitli güvenlik teknolojileri ve usulleri kullanarak azami gayret gösterecektir.'
$body+=$PB

$body+=Bar 'Sözleşme · Madde 07 – 11' 'Gizlilik, Veriler, Fesih, İş Birlikleri ve Diğer Hükümler'
$body+=ArtHead '7' 'Gizlilik (devamı)'
$body+=Clause '7.d.' 'İnternet Sitesi''nde oluşturulan bilgiler müşteriye aittir ve bu bilgiler, Randevum Cepte tarafından 6698 sayılı Kişisel Verilerin Korunması Kanunu''na uygun şekilde işlenecek olup Randevum Cepte''nin müşteriye verilen hizmetlerin çeşitliliği ve kalitesinin devamı açısından entegrasyon sağlanan diğer kurumlarla sözleşme dahilinde, kredi kartı hizmet sağlayıcı, sms hizmet sağlayıcı, ödeme hizmet sağlayıcı, internet servis sağlayıcı ve internet sitesi yazılım bakım ve destek hizmetleri sağlayan ve benzeri gerçek ve tüzel kişilerle paylaşabilir. Bu madde, sayılan gerçek ve tüzel kişiler için müşteri tarafından verilmiş bir açık rıza niteliğindedir. Bununla birlikte, müşteriye ilişkin kişisel veriler, sayılanlar haricinde olan ve Randevum Cepte ile sözleşme ilişkisi içinde olmayan üçüncü kişilerle müşteri veya kullanıcının açık rızası olmaksızın paylaşılmaz.'
$body+=Clause '7.e.' 'Hesapların ve kişisel bilgilerin korunması için kullanılan kullanıcı adı, şifre ve benzeri bilgileri gizli tutmak müşterinin sorumluluğundadır. Müşteri, bu bilgilerin sadece kendi veya kullanıcıların kullanımında olacağını, kullanıcı adı, şifre, parola gibi bilgilerinin gizliliğini ve güvenliğini sağlayacağını, başka hiçbir kişiye hiçbir durumda kullandırmayacağını veya kullanımını sağlayacak bilgileri üçüncü kişilerle paylaşmayacağını, aksi halde Randevum Cepte''nin hiçbir sorumluluğunun bulunmadığını kabul ve beyan eder.'
$body+=Clause '7.f.' 'Randevum Cepte, bu sözleşme ile hizmet verdiği bütün kişi, kurum, kuruluş ve markaları referans olarak gösterebilir; referans gösterdiği kişi, kurum ve kuruluşları internet sitesi veya diğer araçlar vasıtasıyla adlarını ve logolarını kullanarak yayınlayabilir. Referans olarak belirtilmek istemeyen müşterilerin bu taleplerini Randevum Cepte''ye yazılı olarak bildirmeleri gerekmektedir.'
$body+=ArtHead '8' 'Müşteri Verileri'
$body+=Clause '8.a.' 'Randevum Cepte, müşteri verilerini sözleşmenin sona ermesi veya feshedilmesinden itibaren 1 (bir) ay süre ile saklar. Randevum Cepte''nin müşteri verilerini sözleşmenin sona erme veya fesih tarihinden itibaren 1 (bir) aydan uzun süre saklama yükümlülüğü bulunmamaktadır.'
$body+=ArtHead '9' 'Sözleşmenin Feshi'
$body+=Clause '9.a.' 'Müşteri, hiçbir gerekçe göstermeksizin fatura kesim tarihinden itibaren 15 (on beş) gün önceden yazılı olarak ihtar etmek ve geçmişe dönük tüm borçlarını ödemiş olmak şartı ile sözleşmeyi feshetme hakkını haizdir. Ancak, Randevum Cepte''den kaynaklanmayan nedenlerle sözleşmenin feshi halinde, müşteri geçmişte veya peşinen yaptığı ödemelere istinaden ücret iadesi talep edemez.'
$body+=Clause '9.b.' 'Müşteri''nin taahhütte bulunarak aylık olarak ödeme yaptığı durumlarda, ödemeyi yapmadığı ayı takip eden ilk hafta (7 gün) içerisinde Randevum Cepte tek taraflı olarak sözleşmeyi feshetme hakkını haizdir.'
$body+=ArtHead '10' 'İş Birlikleri'
$body+=Clause '10.a.' 'Randevum Cepte, üçüncü gerçek veya tüzel kişilerle, müşteriye sunduğu hizmetlere ek çözümler üretebilmek adına iş ilişkisi veya çoklu iş ortaklıkları kurabilir ve sözleşme ilişkisine girebilir. Bu kapsamda Randevum Cepte''nin iş ilişkisi veya çoklu iş ortaklıkları ve sözleşme ilişkisine girdiği gerçek veya tüzel kişiler "Çözüm Ortağı"dır ve Çözüm Ortağı''nın sunacağı ürün ve hizmetler müşteriye veya kullanıcıya yönelik olabilir.'
$body+=Clause '10.b.' 'Müşterilerin, kendilerinin veya kullanıcıların kişisel veri veya sair bilgilerinin çözüm ortağı ile paylaşılmasına izin vermeleri halinde söz konusu bilgiler çözüm ortağı ile paylaşılır. Kullanıcıların bilgilerin paylaşılmasına ilişkin açık rıza alınmasına ilişkin tüm sorumluluk müşteriye ait olup Randevum Cepte bu hususta kendisine yöneltilen her türlü talebi müşteriye yönlendirecektir. Bu kapsamda müşteri, kullanıcıların usulüne uygun şekilde açık rıza vermemesinden kaynaklanan hukuki ve cezai sorumluluğu üstlendiğini peşinen kabul ve taahhüt eder.'
$body+=Clause '10.c.' 'Randevum Cepte, çözüm ortağı tarafından verilecek ek hizmet veya ürüne ilişkin herhangi bir sorumluluğu bulunmadığı gibi, verilecek hizmete veya ürüne ilişkin de herhangi bir taahhütte de bulunmamaktadır.'
$body+=Clause '10.d.' 'Randevum Cepte ile sözleşmenin sona ermesini müteakiben aracı kuruluşlar (iş birlikleri) ile de sağlanan diğer hizmetler de (kredi kartı ile ödeme, entegrasyon, sms vb.) sonlandırılacaktır.'
$body+=ArtHead '11' 'Diğer Hükümler'
$body+=Clause '11.a.' 'Sözleşme, yönetici veya yönetim kurulu olarak tanımlanan, yetkili kılınmış gerçek veya tüzel kişilerin her ne sebeple olursa olsun değişmesi halinde dahi geçerliliğini ve bağlayıcılığını sürdürür.'
$body+=Clause '11.b.' 'Sözleşme taraflar arasındaki sözleşmenin tamamını teşkil eder. Sözleşme dışında, sözleşme öncesi ve sonrasında taraflar arasında yapılan görüşmeler ve müzakereler kapsamındaki beyanlar sözleşmenin bir parçası değildir ve sözleşme hükümleri ancak yazılı olarak değiştirilebilir. Sözleşme maddelerinin başlıkları yalnızca inceleme kolaylığı sebebiyle yazılmış olup sözleşme bir bütün olarak geçerlidir. Sözleşme maddelerinden biri veya birkaçının herhangi bir nedenle geçersiz olması halinde, sözleşmenin diğer maddeleri geçerliliğini koruyacaktır.'
$body+=Clause '11.c.' 'Randevum Cepte''nin sorumluluğunu sınırlayan hükümlerden birinin yetkili mahkeme tarafından mevzuata aykırı bulunması halinde söz konusu hüküm mevzuata uygun olan ve Randevum Cepte''nin sorumluluğunu en fazla sınırlayan hali ile geçerli sayılacak ve Randevum Cepte''nin sözleşmeden doğan sorumluluğu buna uygun olarak belirlenecektir.'
$body+=Clause '11.d.' 'Sözleşmede belirtilen tarafların adresleri tarafların tebligat adresleri olup sözleşme kapsamında yapılacak tebligatlar bu adreslere yapılacaktır. Müşteri, tebligat adresinin değişmesi halinde değişen adresi Randevum Cepte''ye derhal bildirmekle yükümlüdür. Aksi takdirde, bu sözleşmede bulunan adresi geçerli tebligat adresi olarak kabul edilecektir.'
$body+=Clause '11.e.' 'Müşteri, iş bu sözleşme konusu hizmetlerin verilmesinden doğacak uyuşmazlıklarda Randevum Cepte''ye ait kayıtların, ödemeye ilişkin olan uyuşmazlıklarda ise tarafların ticari defterlerine ilişkin kayıtların geçerli ve sağlayıcı olduğunu, kesin delil teşkil edeceğini ve iş bu maddenin 6100 sayılı HMK''nın 193. maddesi anlamında bir yazılı delil sözleşmesi beyanı anlamında olduğunu kabul, beyan ve taahhüt eder.'
$body+=Clause '11.f.' 'Sözleşme''den doğacak uyuşmazlıkları çözmeye İzmir Mahkemeleri ve İcra Daireleri yetkilidir ve sözleşme Türkiye Cumhuriyeti hukukuna tabidir.'
$body+=Clause '11.g.' 'Bu sözleşme toplam 11 (on bir) ana maddeden ibaret olup taraflarca imzalandığı gün tüm hüküm ve koşullarıyla yürürlüğe girmiştir.'
$body+=$PB

$body+=Bar 'Sözleşme · Ek Belge' 'Hizmet Detayları ve İmza'
$body+=P '' '<w:spacing w:after="40"/>'
$body+=$sdTbl
$body+=$hgTitle+$hgBody
$body+=P (Rn 'Bu sözleşme …… / …… / ……… tarihinde tarafların karşılıklı müzakeresi ve mutabakatı ile 5 (beş) sayfa ve iki nüsha olarak imzalanmıştır.' $GRI $false 19 $true) '<w:jc w:val="center"/><w:spacing w:before="160" w:after="160"/>'
$body+=$signTbl
$body+=P (Rn 'www.randevumcepte.com.tr      •      0541 294 81 44      •      info@webfirmam.com.tr' $MOR $true 18) '<w:jc w:val="center"/><w:spacing w:before="200" w:after="0"/>'

$sectPr='<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="760" w:right="680" w:bottom="680" w:left="680" w:header="0" w:footer="0" w:gutter="0"/></w:sectPr>'
$documentXml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'+
  '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'+$body+$sectPr+'</w:body></w:document>'

$contentTypes='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>'
$rels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'

$root='c:\Users\ferdi\Desktop\randevumcepteuygulamaweb\Randevumcepte-Laravel'
$stage=Join-Path $root '_docx_build'
if(Test-Path $stage){ Remove-Item $stage -Recurse -Force }
New-Item -ItemType Directory -Path $stage | Out-Null
New-Item -ItemType Directory -Path (Join-Path $stage '_rels') | Out-Null
New-Item -ItemType Directory -Path (Join-Path $stage 'word') | Out-Null
$enc=New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText((Join-Path $stage '[Content_Types].xml'),$contentTypes,$enc)
[System.IO.File]::WriteAllText((Join-Path $stage '_rels\.rels'),$rels,$enc)
[System.IO.File]::WriteAllText((Join-Path $stage 'word\document.xml'),$documentXml,$enc)

$docx=Join-Path $root 'Sozlesme-Satis-Hizmet.docx'
if(Test-Path $docx){ Remove-Item $docx -Force }
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($stage,$docx)
if(Test-Path $docx){ Write-Output ("DOCX_OK "+(Get-Item $docx).Length+" bytes") } else { Write-Output 'DOCX_YOK' }
