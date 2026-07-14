/**
 * LLM tool tanımları.
 * LLM bunlardan birini çağırır, sidecar Laravel API'sine yönlendirir.
 *
 * Tarih/saat formatı: ISO 8601 ("2026-05-15T14:00:00") — LLM'in bu formatta
 * üretmesi sistem promptunda zorunlu kılınmıştır.
 */
export const tools = [
  {
    type: 'function',
    function: {
      name: 'hizmet_eslestir',
      description:
        'Müşteri hangi hizmeti istediğini söyleyince ÖNCE bunu çağır. Müşterinin AĞZINDAN çıkan hizmet ifadesini olduğu gibi ver (örn. "lazer epilasyon", "cilt bakımı"). ' +
        'Sistem doğru hizmeti bulur. Dönüş: eslesme="tek" ise hizmet netleşti (hizmet.id ile devam et); ' +
        'eslesme="coklu" ise müşteriye seçenekleri (secenekler[].ad) okuyup "hangisi?" diye SOR; ' +
        'eslesme="yok" ise nazikçe o hizmetin olmadığını söyle, varsa öneriler[].ad arasından sor. ' +
        'KENDİN hizmet listesinden tahmin YÜRÜTME, hep bu tool ile doğrula.',
      parameters: {
        type: 'object',
        properties: {
          metin: {
            type: 'string',
            description: 'Müşterinin söylediği hizmet ifadesi, aynen (örn. "lazer epilasyon")',
          },
        },
        required: ['metin'],
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'musait_saatleri_getir',
      description:
        'Belirli bir tarih için salonda boş randevu saatlerini listeler. ' +
        'Müşteri "yarın", "salı", "haftaya" gibi belirsiz tarih söylediğinde sistem promptundaki bugünün tarihinden hesaplayıp ISO formatında ver. ' +
        'Müşteri "öğleden sonra", "sabah", "akşam" gibi bir dilim söylediyse zaman_dilimi parametresine geçir.',
      parameters: {
        type: 'object',
        properties: {
          tarih: {
            type: 'string',
            description: 'YYYY-MM-DD formatında tarih (örn. 2026-05-15)',
          },
          zaman_dilimi: {
            type: 'string',
            enum: ['sabah', 'ogle', 'ogleden_sonra', 'aksam'],
            description: 'Opsiyonel: müşteri gün içi dilim belirttiyse (sabah/öğleden sonra/akşam)',
          },
          hizmet_id: {
            type: ['integer', 'string'],
            description: 'Opsiyonel: hizmet_eslestir tool\'undan gelen hizmet.id — sayı olarak ver',
          },
        },
        required: ['tarih'],
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'randevu_olustur',
      description:
        'Müşteri için yeni randevu oluşturur. Tarih + saat + hizmet bilgisi netleştikten ve müşteri ONAYLADIKTAN sonra çağır.',
      parameters: {
        type: 'object',
        properties: {
          telefon: {
            type: 'string',
            description: 'Müşteri telefonu (caller ID, sistem otomatik verir)',
          },
          ad_soyad: {
            type: 'string',
            description: 'Müşteri adı soyadı (yeni müşteri ise sor)',
          },
          tarih_saat: {
            type: 'string',
            description: 'ISO 8601 (örn. "2026-05-15T14:00:00")',
          },
          hizmet_id: {
            type: 'integer',
            description: 'hizmet_eslestir tool\'undan gelen hizmet.id (sayı)',
          },
          notlar: {
            type: 'string',
            description: 'Müşterinin eklediği özel not (varsa)',
          },
        },
        required: ['telefon', 'tarih_saat'],
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'mevcut_randevularim',
      description:
        'Müşterinin gelecekteki randevularını listeler (iptal/güncelleme öncesi).',
      parameters: {
        type: 'object',
        properties: {
          telefon: {
            type: 'string',
            description: 'Müşteri telefonu',
          },
        },
        required: ['telefon'],
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'randevu_iptal',
      description: 'Mevcut randevuyu iptal eder. ÖNCE müşteriye onay sor.',
      parameters: {
        type: 'object',
        properties: {
          randevu_id: {
            type: ['integer', 'string'],
            description: 'mevcut_randevularim çağrısının döndürdüğü id (sayı)',
          },
        },
        required: ['randevu_id'],
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'randevu_guncelle',
      description: 'Mevcut randevunun tarihini/saatini değiştirir.',
      parameters: {
        type: 'object',
        properties: {
          randevu_id: {
            type: ['integer', 'string'],
            description: 'Güncellenecek randevu id (sayı)',
          },
          yeni_tarih_saat: {
            type: 'string',
            description: 'ISO 8601',
          },
        },
        required: ['randevu_id', 'yeni_tarih_saat'],
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'canli_operatore_aktar',
      description:
        'Müşteri AI ile çözemediği bir konu için canlı operatör isterse veya AI 3 turda anlamadıysa çağrıyı insana aktarır.',
      parameters: {
        type: 'object',
        properties: {
          sebep: {
            type: 'string',
            description: 'Aktarma sebebi (loga gider)',
          },
        },
        required: ['sebep'],
      },
    },
  },
];

/**
 * Bir "YYYY-MM-DD" string'ini gün ekleyerek yeni "YYYY-MM-DD" döndürür.
 * UTC üzerinden hesaplar — yerel saat dilimi/DST etkisi YOK, deterministik.
 * (Eski kod new Date().toISOString() ile UTC'ye kayıyordu; gece yarısı
 *  civarı "bugün" yanlış çıkabiliyordu. Artık `bugun` İstanbul tarihidir ve
 *  offset aritmetiği saf UTC ile yapılır — kayma imkânsız.)
 */
function addDaysIso(baseIso, offset) {
  const [y, m, d] = baseIso.split('-').map((x) => parseInt(x, 10));
  const dt = new Date(Date.UTC(y, m - 1, d));
  dt.setUTCDate(dt.getUTCDate() + offset);
  const yy = dt.getUTCFullYear();
  const mm = String(dt.getUTCMonth() + 1).padStart(2, '0');
  const dd = String(dt.getUTCDate()).padStart(2, '0');
  return { iso: `${yy}-${mm}-${dd}`, dow: dt.getUTCDay() };
}

/**
 * Bugun + sonraki 14 gun icin gun_adi -> ISO tarih haritasi.
 * "Persembe", "onumuzdeki cuma" gibi ifadeleri LLM'in dogru cozebilmesi
 * icin hazir tablo. HESAP YAPILMAZ, tablo okunur.
 */
function buildGunHaritasi(bugun) {
  const gunAdlari = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
  const gunlerWeek1 = []; // 0..6 — "bu/önümüzdeki" hafta
  const gunlerWeek2 = []; // 7..13 — "haftaya" / 1 hafta sonra
  for (let i = 0; i < 7; i++) {
    const { iso, dow } = addDaysIso(bugun, i);
    gunlerWeek1.push(`${gunAdlari[dow]} = ${iso}${i === 0 ? ' (BUGÜN)' : i === 1 ? ' (YARIN)' : ''}`);
  }
  for (let i = 7; i < 14; i++) {
    const { iso, dow } = addDaysIso(bugun, i);
    gunlerWeek2.push(`${gunAdlari[dow]} (haftaya) = ${iso}`);
  }
  return { week1: gunlerWeek1, week2: gunlerWeek2 };
}

/**
 * Sistem promptu.
 * Bugünün tarihi (İstanbul saatiyle) pipeline tarafından runtime'da inject edilir.
 */
export function buildSystemPrompt({ bugun, salonAdi, callerPhone, hizmetler, musteriAdi, paketler }) {
  const harita = buildGunHaritasi(bugun);
  const hasList = Array.isArray(hizmetler) && hizmetler.length > 0;

  // Hizmet listesi artık LLM'e "seç" diye değil, sadece BAĞLAM olarak veriliyor.
  // Asıl eşleştirmeyi hizmet_eslestir tool'u (backend) yapıyor.
  let hizmetBolumu = '';
  if (hasList) {
    const liste = hizmetler
      .slice(0, 40)
      .map((h) => `  - ${h.ad}`)
      .join('\n');
    hizmetBolumu = `\nSALONUN HİZMETLERİ (sadece bağlam — eşleştirmeyi SEN yapma, hizmet_eslestir tool'u yapar):\n${liste}\n`;
  }

  // Müşteri tanınıyorsa kişiselleştir
  let musteriBolumu = '';
  if (musteriAdi) {
    musteriBolumu += `\n- Arayan tanınan müşteri: ${musteriAdi} (selamlarken adıyla hitap edebilirsin, "Bey/Hanım" ekle)`;
  }
  if (Array.isArray(paketler) && paketler.length > 0) {
    const pk = paketler
      .slice(0, 6)
      .map((p) => `${p.ad}${p.kalan != null ? ` (kalan ${p.kalan} seans)` : ''}`)
      .join(', ');
    musteriBolumu += `\n- Müşterinin aktif paketleri: ${pk}. İlgili hizmet için randevu alırken "paketinizden düşecek" diyebilirsin.`;
  }

  return `Sen ${salonAdi} işletmesinin sesli randevu asistanısın. Telefonda Türkçe konuşuyorsun.

═══ AKIŞ (sırasıyla, atlamadan) ═══
1. SELAMLAMA: Karşılama metni DIŞARIDAN TTS ile zaten çalındı (sohbet geçmişinin ilk satırı senin söylediğin kabul edilmeli). Selamlamayı TEKRAR ETME. Müşteri konuşmaya başlayınca direkt 2. adıma geç. Geçmişte assistant mesajı yoksa kısa bir "Merhaba, size nasıl yardımcı olabilirim?" de — işletme adına ASLA "-da/-de/-a/-e/-ye" gibi ek ekleme.
2. NİYET: müşteri ne istiyor anla (yeni randevu / iptal / güncelle / başka).
3. HİZMET (yeni randevu için): "Hangi hizmet için?" → müşteri söyleyince **hizmet_eslestir tool'unu çağır** (müşterinin dediğini aynen ver). Sonuca göre:
   • eslesme="tek"  → hizmet netleşti, "doğru anladım mı" SORMA, direkt 4. adıma geç.
   • eslesme="coklu" → seçenekleri kısaca oku ("Kol, bacak, tüm vücut seçeneklerimiz var, hangisi?"), müşteri seçince TEKRAR hizmet_eslestir çağır ya da gelen id'yi kullan.
   • eslesme="yok"  → "Üzgünüm o hizmetimiz yok" + varsa önerileri say, tekrar sor.
4. TARİH: "Hangi gün?" → müşteri gün/tarih söylesin. Aşağıdaki TARİH HARİTASI tablosundan KESİN ISO tarihi al, hesap yapma. Müşteri tarih söylediğinde TEK SEFER kısa onay: "Perşembe yani 7 Mayıs, doğru mu?" → onay alınca 5'e geç.
5. MÜSAİT SAAT: musait_saatleri_getir çağır (müşteri "öğleden sonra" dediyse zaman_dilimi ver). Dönen saatlerden **sadece 2 tanesini** öner: "Saat 13:00 ya da 15:30 uygun, hangisi olsun?" ASLA tüm saatleri sıralama. Hiç saat yoksa başka gün/dilim öner.
6. SAAT SEÇİMİ: müşteri saat söyler.
7. ONAY (TEK SEFER, sadece burada): "[gün] [saat]'de [hizmet] için randevu, onaylıyor musunuz?"
8. randevu_olustur tool'unu çağır (hizmet_eslestir'den gelen hizmet_id ile).
9. SONUÇ: "Randevunuz oluşturuldu, [gün] saat [saat]'de bekliyoruz. İyi günler."

═══ ONAY KELİMELERİNİ TANI ═══
Şu ifadelerin HEPSİ "evet/onay"dır: evet, evet tabii, tabii, tab, olur, olsun, tamam, tamamdır, ok, okey, kabul, onaylıyorum, uygundur, peki, hı hı, he, aynen, oldu. Bunlardan birini duyunca onaylanmış say.
Şunlar "hayır"dır: hayır, yok, istemiyorum, olmaz, vazgeçtim, iptal, başka. Bunları duyunca ilgili adımı iptal et / alternatif sor.

═══ KONUŞMA TARZI ═══
- 1-2 cümleyi geçme. Telefonda kısa ve sıcak konuş.
- Doğal Türkçe. Aşırı formal olma ama saygılı ol.
- Tarih/saat'i Türkçe söyle ("yarın saat ona", "salı on dörde"), tool'a verirken ISO 8601.
- Emin olamadığın bir şeyi (isim, saat) tek kısa cümleyle teyit et, sonra devam et.

═══ BAĞLAM ═══
- Bugün: ${bugun}
- Müşteri telefonu: ${callerPhone || 'bilinmiyor'}
- İşletme: ${salonAdi}${musteriBolumu}
${hizmetBolumu}
═══ TARİH HARİTASI (HESAP YAPMA, DOĞRUDAN BU TABLOYU KULLAN) ═══
Bu hafta:
${harita.week1.map((s) => '  ' + s).join('\n')}
Haftaya (1 hafta sonra):
${harita.week2.map((s) => '  ' + s).join('\n')}

KULLANIM:
- "Perşembe" / "Bu Perşembe" / "Önümüzdeki Perşembe" → bu hafta tablosundan.
- "Gelecek Perşembe" / "Haftaya Perşembe" → haftaya tablosundan.
- "Yarın" / "Öbür gün" / "Bugün" → tablodan al.
- "5 Mayıs" gibi sayılı tarih → ay+gün ile birleştirip ISO yap.
- Geçmiş bir gün söylediyse → "Bu [gün] mü yoksa haftaya mı?" diye netleştir.

═══ SAAT KESTİRİMİ ═══
- "On dörtte" = 14:00 ; "On dört buçukta" / "on dört otuzda" = 14:30
- "İkide" / "saat ikide" = 14:00 (öğleden sonra varsayılan) ; "İki buçukta" = 14:30
- "Sabah dokuz" = 09:00 ; "Akşam yedide" = 19:00 ; "Öğleden sonra üçte" = 15:00
- Belirsizse "Sabah mı öğleden sonra mı?" diye sor.

═══ OPERATÖRE AKTARMA (sadece bu durumlarda) ═══
- Müşteri "operatör/insan/yetkili istiyorum" derse → canli_operatore_aktar.
- Randevu/iptal/güncelle dışı konu (şikayet, fatura, ödeme) → canli_operatore_aktar.
- HİZMET YOKSA AKTARMA — sadece sormaya devam et.

═══ KIRMIZI ÇİZGİLER ═══
- Hizmeti KENDİN tahmin etme, DAİMA hizmet_eslestir tool'u ile çöz.
- eslesme="coklu" dönerse ASLA kendin seçme, müşteriye sor.
- Onayı (7. adım) almadan randevu_olustur ÇAĞIRMA.
- Hizmet bir kez netleştiyse geri dönme, kararı koru.
- Tüm müsait saatleri sıralama, 2 tane öner.`;
}
