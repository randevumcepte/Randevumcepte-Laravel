                              {{-- AI asistan gorusmeleri: restoran santral-kayitlar tasarimi (sol liste, sag sohbet dokumu + ses) --}}
                              <div class="tab-pane fade" id="asistan-gorusmeleri" role="tabpanel" style="margin-top: 20px;">
                                 <div class="ag-kok">
                                    <div class="ag-stats">
                                       <div class="ag-stat"><div class="n" id="ag_s_top">—</div><div class="l"><span class="dot b-none"></span>Toplam görüşme</div></div>
                                       <div class="ag-stat"><div class="n" id="ag_s_rnd">—</div><div class="l"><span class="dot b-randevu"></span>Randevu talebi</div></div>
                                       <div class="ag-stat"><div class="n" id="ag_s_hat">—</div><div class="l"><span class="dot b-teyit"></span>Hatırlatma araması</div></div>
                                       <div class="ag-stat"><div class="n" id="ag_s_akt">—</div><div class="l"><span class="dot b-aktar"></span>İşletmeye aktarılan</div></div>
                                    </div>
                                    <div class="ag-geribant" id="ag_geribant" style="display:none">
                                       📞 <b id="ag_geri_say">0</b> müşteri geri aranmayı bekliyor <span class="ag-kucuk">(yetkiliye ulaşılamadı / mesai dışı — asistan not aldı)</span>
                                       <button type="button" id="ag_geri_goster">Göster</button>
                                    </div>
                                    <div class="ag-filtre">
                                       <input type="date" id="ag_baslangic" value="{{date('Y-m-d', strtotime('-6 days'))}}">
                                       <span style="color:#94a3b8">—</span>
                                       <input type="date" id="ag_bitis" value="{{date('Y-m-d')}}">
                                       <select id="ag_tip">
                                          <option value="">Tüm görüşmeler</option>
                                          <option>Gelen arama</option>
                                          <option>Randevu hatırlatma</option>
                                          <option>Ödeme hatırlatma</option>
                                          <option value="geri_ara">Geri aranacaklar</option>
                                       </select>
                                       <button type="button" id="ag_listele"><i class="fa fa-refresh"></i> Yenile</button>
                                    </div>
                                    <div class="ag-grid">
                                       <div class="ag-panel">
                                          <div class="ag-ara"><input id="ag_q" placeholder="🔎 Müşteri, telefon veya içerik ara…"></div>
                                          <div class="ag-liste" id="ag_liste"><div class="ag-bos">Yükleniyor…</div></div>
                                       </div>
                                       <div class="ag-panel"><div class="ag-detay" id="ag_detay"><div class="ag-bos">Soldan bir görüşme seçin</div></div></div>
                                    </div>
                                 </div>
                              </div>
                              <style>
                                 .ag-kok{--line:#e5e7eb;--sub:#64748b;--p2:#f8fafc;--indigo:#6366f1;--us:linear-gradient(135deg,#6366f1,#8b5cf6)}
                                 .ag-kok *{box-sizing:border-box}
                                 .ag-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
                                 @media(max-width:768px){.ag-stats{grid-template-columns:repeat(2,1fr)}}
                                 .ag-stat{background:#fff;border:1px solid var(--line);border-radius:14px;padding:14px 16px}
                                 .ag-stat .n{font-size:24px;font-weight:800;color:#0f172a}
                                 .ag-stat .l{font-size:12px;color:var(--sub);margin-top:2px;display:flex;align-items:center;gap:6px}
                                 .ag-stat .dot{width:8px;height:8px;border-radius:50%;display:inline-block}
                                 .ag-filtre{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:14px}
                                 .ag-filtre input,.ag-filtre select{border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-size:13px;background:#fff}
                                 .ag-filtre button{border:none;background:var(--us);color:#fff;border-radius:10px;padding:9px 16px;font-weight:700;font-size:13px;cursor:pointer}
                                 .ag-grid{display:grid;grid-template-columns:360px minmax(0,1fr);gap:16px}
                                 @media(max-width:900px){.ag-grid{grid-template-columns:minmax(0,1fr)}}
                                 .ag-panel{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden}
                                 .ag-ara{padding:12px;border-bottom:1px solid var(--line)}
                                 .ag-ara input{width:100%;background:var(--p2);border:1px solid var(--line);border-radius:10px;padding:10px 12px;font-size:14px;outline:none}
                                 .ag-liste{max-height:70vh;overflow:auto}
                                 .ag-kayit{padding:12px 14px;border-bottom:1px solid var(--line);cursor:pointer;display:flex;gap:11px;align-items:flex-start;border-left:3px solid transparent;transition:.12s}
                                 .ag-kayit:hover{background:var(--p2)}
                                 .ag-kayit.sec{background:#eef2ff;border-left-color:var(--indigo)}
                                 .ag-av{width:38px;height:38px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;font-size:17px;color:#fff}
                                 .ag-ov{flex:1;min-width:0}
                                 .ag-ad{font-weight:700;font-size:14px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                 .ag-sn{font-size:12px;color:var(--sub);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                 .ag-alt{display:flex;align-items:center;gap:6px;margin-top:5px;flex-wrap:wrap}
                                 .ag-rozet{font-size:10.5px;border-radius:20px;padding:2px 9px;font-weight:800;letter-spacing:.02em}
                                 .ag-zaman{font-size:11px;color:#94a3b8;margin-left:auto;white-space:nowrap;padding-left:6px}
                                 .ag-kucuk{font-size:11px;color:#94a3b8}
                                 .c-randevu{background:rgba(59,130,246,.14);color:#2563eb}.b-randevu{background:#3b82f6}
                                 .c-teyit{background:rgba(34,197,94,.14);color:#16a34a}.b-teyit{background:#22c55e}
                                 .c-iptal{background:rgba(239,68,68,.14);color:#dc2626}.b-iptal{background:#ef4444}
                                 .c-aktar{background:rgba(245,158,11,.16);color:#d97706}.b-aktar{background:#f59e0b}
                                 .c-guncelle{background:rgba(99,102,241,.14);color:#4f46e5}.b-guncelle{background:#6366f1}
                                 .c-none{background:rgba(100,116,139,.14);color:#475569}.b-none{background:#64748b}
                                 .c-geriara{background:rgba(225,29,72,.13);color:#e11d48}.b-geriara{background:#e11d48}
                                 .ag-geribant{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff1f2;border:1px solid #fecdd3;color:#9f1239;border-radius:12px;padding:11px 14px;margin-bottom:14px;font-size:14px}
                                 .ag-geribant button{margin-left:auto;border:none;background:#e11d48;color:#fff;border-radius:9px;padding:7px 14px;font-weight:700;font-size:12.5px;cursor:pointer}
                                 .ag-gerikutu{background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;padding:12px 14px;margin-bottom:14px;font-size:13.5px;color:#881337}
                                 .ag-gerikutu.ok{background:#f0fdf4;border-color:#bbf7d0;color:#14532d}
                                 .ag-gerikutu .bas{font-weight:700;margin-bottom:4px}
                                 .ag-gerikutu .not{background:#fff;border:1px solid #fecdd3;border-radius:9px;padding:8px 10px;margin-top:6px;color:#0f172a;white-space:pre-wrap}
                                 .ag-gerikutu button{margin-top:10px;border:none;background:#16a34a;color:#fff;border-radius:9px;padding:8px 14px;font-weight:700;font-size:12.5px;cursor:pointer}
                                 .ag-detay{padding:18px;max-height:74vh;overflow:auto}
                                 .ag-meta{background:var(--p2);border:1px solid var(--line);border-radius:12px;padding:14px;margin-bottom:14px}
                                 .ag-meta .ln1{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-weight:800;font-size:16px;color:#0f172a}
                                 .ag-meta .ln2{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:8px;font-size:12px;color:var(--sub)}
                                 .ag-chip{background:#fff;border:1px solid var(--line);border-radius:8px;padding:3px 9px;font-size:12px;color:#0f172a}
                                 .ag-ses{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;margin-top:8px}
                                 .ag-ses .et{font-size:12px;color:var(--sub);margin-bottom:5px;font-weight:700}
                                 .ag-ses audio{width:100%;height:34px}
                                 .ag-kopyala{background:var(--us);border:none;color:#fff;border-radius:9px;padding:8px 14px;font-weight:700;cursor:pointer;font-size:12.5px;margin-bottom:14px}
                                 .ag-msg{max-width:84%;padding:10px 14px;border-radius:15px;font-size:14px;line-height:1.5;margin-bottom:10px;white-space:pre-wrap}
                                 .ag-msg .kim{font-size:10.5px;opacity:.75;margin-bottom:3px;font-weight:700;letter-spacing:.02em}
                                 .ag-us{background:var(--us);color:#fff;border-bottom-right-radius:5px;margin-left:auto}
                                 .ag-ai{background:var(--p2);border:1px solid var(--line);border-bottom-left-radius:5px;color:#0f172a}
                                 .ag-bos{color:var(--sub);text-align:center;padding:40px 20px}
                              </style>
                              <script>
                              window.addEventListener('load', function () {
                                 var $ = window.jQuery; if (!$) return;
                                 var sube = '{{$isletme->id}}', yuklendi = false, hepsi = [], dokumMetni = '';
                                 var IKON = {randevu:'📅', teyit:'✅', iptal:'✖️', aktar:'↪️', guncelle:'🔁', geriara:'📞', none:'📞'};
                                 var TIP_IKON = {'Gelen arama':'📞', 'Randevu hatırlatma':'⏰', 'Ödeme hatırlatma':'💳'};
                                 function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
                                 function rozet(x){ return '<span class="ag-rozet c-' + x.renk + '">' + (IKON[x.renk] || '•') + ' ' + esc(x.sonuc) + '</span>'; }

                                 function listele(){
                                    $('#ag_liste').html('<div class="ag-bos">Yükleniyor…</div>');
                                    $.ajax({ url: '/isletmeyonetim/santral-asistan-gorusmeler', dataType: 'json', cache: false,
                                       data: { sube: sube, baslangic: $('#ag_baslangic').val(), bitis: $('#ag_bitis').val() },
                                       success: function (r) {
                                          var tip = $('#ag_tip').val();
                                          hepsi = (r.liste || []).filter(function (x) { return !tip || (tip === 'geri_ara' ? x.geri_ara === 1 : x.tip === tip); });
                                          $('#ag_geri_say').text(r.bekleyen_geri_ara || 0);
                                          $('#ag_geribant').toggle((r.bekleyen_geri_ara || 0) > 0);
                                          $('#ag_s_top').text(hepsi.length);
                                          $('#ag_s_rnd').text(hepsi.filter(function (x) { return x.renk === 'randevu'; }).length);
                                          $('#ag_s_hat').text(hepsi.filter(function (x) { return x.tip !== 'Gelen arama'; }).length);
                                          $('#ag_s_akt').text(hepsi.filter(function (x) { return x.renk === 'aktar'; }).length);
                                          filtrele();
                                          if (hepsi[0]) detay(hepsi[0].id); else $('#ag_detay').html('<div class="ag-bos">Bu aralıkta asistan görüşmesi yok.</div>');
                                       },
                                       error: function (q) { $('#ag_liste').html('<div class="ag-bos">' + esc((q.responseJSON && q.responseJSON.hata) || 'Liste alınamadı.') + '</div>'); }
                                    });
                                 }
                                 function ciz(list){
                                    if (!list.length) { $('#ag_liste').html('<div class="ag-bos">Kayıt yok</div>'); return; }
                                    var h = '';
                                    list.forEach(function (x) {
                                       h += '<div class="ag-kayit" id="ag_k' + x.id + '" data-id="' + x.id + '">' +
                                            '<div class="ag-av b-' + x.renk + '">' + (TIP_IKON[x.tip] || '📞') + '</div>' +
                                            '<div class="ag-ov"><div style="display:flex;align-items:center"><span class="ag-ad">' + esc(x.musteri || x.telefon) + '</span><span class="ag-zaman">' + esc(x.tarih) + '</span></div>' +
                                            '<div class="ag-sn">' + esc(x.son_musteri || (x.musteri ? x.telefon : '—')) + '</div>' +
                                            '<div class="ag-alt">' + rozet(x) + '<span class="ag-kucuk">' + esc(x.tip) + (x.sure ? ' · ' + esc(x.sure) : '') + (x.ses ? ' · 🎙️' : '') + '</span></div></div></div>';
                                    });
                                    $('#ag_liste').html(h);
                                 }
                                 function filtrele(){
                                    var q = ($('#ag_q').val() || '').toLocaleLowerCase('tr').trim();
                                    if (!q) { ciz(hepsi); return; }
                                    ciz(hepsi.filter(function (x) { return [x.musteri, x.telefon, x.son_musteri, x.sonuc].join(' ').toLocaleLowerCase('tr').indexOf(q) !== -1; }));
                                 }
                                 function detay(id){
                                    $('.ag-kayit').removeClass('sec'); $('#ag_k' + id).addClass('sec');
                                    $('#ag_detay').find('audio').each(function () { this.pause(); });
                                    $('#ag_detay').html('<div class="ag-bos">Yükleniyor…</div>');
                                    $.ajax({ url: '/isletmeyonetim/santral-asistan-gorusme/' + id, data: { sube: sube }, dataType: 'json', cache: false,
                                       success: function (r) {
                                          var k = r.kayit || {};
                                          var chips = '<span>🕐 ' + esc(k.tarih) + '</span><span class="ag-chip">' + esc(k.tip) + '</span>';
                                          if (k.randevu) chips += '<span class="ag-chip">📅 Randevu ' + esc(k.randevu) + '</span>';
                                          if (k.sure) chips += '<span class="ag-chip">⏱ ' + esc(k.sure) + '</span>';
                                          var m = '<div class="ag-meta"><div class="ln1">' + esc(k.musteri || k.telefon || 'Gizli numara') + ' ' + rozet(k) + '</div>' +
                                                  '<div class="ln2">' + (k.musteri ? '<span>📱 ' + esc(k.telefon) + '</span>' : '') + chips + '</div>';
                                          if (k.sesler && k.sesler.length) {
                                             k.sesler.forEach(function (a) { m += '<div class="ag-ses"><div class="et">' + (a.aktarma ? '↪️ ' : '🤖 ') + esc(a.tur) + '</div><audio controls preload="none" src="' + esc(a.url) + '"></audio></div>'; });
                                          } else m += '<div style="margin-top:10px;font-size:12px;color:#94a3b8">🎙️ Ses kaydı yok</div>';
                                          m += '</div>';
                                          if (k.geri_ara) {
                                             m += '<div class="ag-gerikutu' + (k.geri_ara === 2 ? ' ok' : '') + '">' +
                                                  '<div class="bas">' + (k.geri_ara === 2 ? '✅ Geri arandı' + (k.geri_arandi ? ' — ' + esc(k.geri_arandi) : '') : '📞 Bu müşteri geri aranmayı bekliyor') + '</div>' +
                                                  (k.geri_ara_neden ? '<div>' + esc(k.geri_ara_neden) + ', asistan müşteriye geri aranacağını söyledi.</div>' : '') +
                                                  '<div class="not">' + (k.geri_ara_not ? '📝 Müşterinin notu: ' + esc(k.geri_ara_not) : 'Müşteri not bırakmadı — konuşma dökümüne bakabilirsiniz.') + '</div>' +
                                                  (k.geri_ara === 1 ? '<button type="button" class="ag-geri-arandi" data-id="' + k.id + '">✔ Aradım, tamamlandı</button>' : '') +
                                                  '</div>';
                                          }
                                          var c = '';
                                          dokumMetni = (k.tarih || '') + ' | ' + (k.musteri || '') + ' ' + (k.telefon || '') + ' | ' + (k.sonuc || '') + '\n';
                                          (k.konusma || []).forEach(function (x) {
                                             var ai = x.rol === 'asistan';
                                             c += '<div class="ag-msg ' + (ai ? 'ag-ai' : 'ag-us') + '"><div class="kim">' + (ai ? '🤖 ASİSTAN' : '🧑 MÜŞTERİ') + '</div>' + esc(x.metin) + '</div>';
                                             dokumMetni += '\n' + (ai ? 'ASİSTAN' : 'MÜŞTERİ') + ': ' + x.metin;
                                          });
                                          $('#ag_detay').html(m + '<button type="button" class="ag-kopyala" id="ag_kopyala">📋 Dökümü kopyala</button>' + (c || '<div class="ag-bos">Konuşma boş (müşteri konuşmadan kapattı)</div>'));
                                       },
                                       error: function (q) { $('#ag_detay').html('<div class="ag-bos">' + esc((q.responseJSON && q.responseJSON.hata) || 'Kayıt alınamadı.') + '</div>'); }
                                    });
                                 }
                                 // Sekme tiklanarak da, sayfa son acik sekmeyi geri yukleyerek de (tiklama olmadan) acilabilir:
                                 // her iki durumda da listeyi bir kez yukle.
                                 function gerekirseYukle() { if (!yuklendi && $('#asistan-gorusmeleri').is(':visible')) { yuklendi = true; listele(); } }
                                 $(document).on('shown.bs.tab click', '[href="#asistan-gorusmeleri"]', function () { setTimeout(gerekirseYukle, 50); });
                                 gerekirseYukle();
                                 setTimeout(gerekirseYukle, 600);
                                 setTimeout(gerekirseYukle, 1500);
                                 $('#ag_listele').on('click', listele);
                                 $('#ag_tip, #ag_baslangic, #ag_bitis').on('change', listele);
                                 $('#ag_q').on('input', filtrele);
                                 $('#ag_liste').on('click', '.ag-kayit', function () { detay($(this).data('id')); });
                                 $('#ag_geri_goster').on('click', function () {
                                    $('#ag_tip').val('geri_ara');
                                    $('#ag_baslangic').val('{{date('Y-m-d', strtotime('-30 days'))}}');
                                    listele();
                                 });
                                 $('#ag_detay').on('click', '.ag-geri-arandi', function () {
                                    var id = $(this).data('id'), $b = $(this).prop('disabled', true);
                                    $.ajax({ url: '/isletmeyonetim/santral-asistan-geri-arandi/' + id, type: 'POST', dataType: 'json',
                                       data: { sube: sube, _token: '{{ csrf_token() }}' },
                                       success: function (r) {
                                          if (!r.ok) { $b.prop('disabled', false); swal({ type: 'warning', title: r.hata || 'İşaretlenemedi' }); return; }
                                          swal({ type: 'success', title: 'Geri arandı olarak işaretlendi', showConfirmButton: false, timer: 1400 });
                                          listele();
                                       },
                                       error: function () { $b.prop('disabled', false); swal({ type: 'error', title: 'İşaretlenemedi' }); }
                                    });
                                 });
                                 $('#ag_detay').on('click', '#ag_kopyala', function () {
                                    if (navigator.clipboard) navigator.clipboard.writeText(dokumMetni).then(function () { swal({ type: 'success', title: 'Döküm kopyalandı', showConfirmButton: false, timer: 1500 }); });
                                 });
                              });
                              </script>
