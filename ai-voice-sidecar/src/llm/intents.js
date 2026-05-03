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
      name: 'musait_saatleri_getir',
      description:
        'Belirli bir tarih için salonda boş randevu saatlerini listeler. ' +
        'Müşteri "yarın", "salı", "haftaya" gibi belirsiz tarih söylediğinde sistem promptundaki bugünün tarihinden hesaplayıp ISO formatında ver.',
      parameters: {
        type: 'object',
        properties: {
          tarih: {
            type: 'string',
            description: 'YYYY-MM-DD formatında tarih (örn. 2026-05-15)',
          },
          hizmet_id: {
            type: ['integer', 'string'],
            description: 'Opsiyonel: hangi hizmet için (saç kesimi, manikür vs.) — sayı olarak ver',
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
            description: 'Hangi hizmet (opsiyonel, varsayılan salon paketi)',
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
 * Bugun + sonraki 14 gun icin gun_adi -> ISO tarih haritasi.
 * "Persembe", "onumuzdeki cuma" gibi ifadeleri LLM'in dogru
 * cozebilmesi icin hazir tablo.
 */
function buildGunHaritasi(bugun) {
  const t0 = new Date(`${bugun}T00:00:00`);
  const isoOfDay = (offset) => {
    const d = new Date(t0);
    d.setDate(d.getDate() + offset);
    return d.toISOString().slice(0, 10);
  };
  const gunAdlari = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
  const gunlerWeek1 = []; // 0..6 — "bu/önümüzdeki" hafta
  const gunlerWeek2 = []; // 7..13 — "haftaya" / 1 hafta sonra
  for (let i = 0; i < 7; i++) {
    const iso = isoOfDay(i);
    const ad = gunAdlari[new Date(`${iso}T00:00:00`).getDay()];
    gunlerWeek1.push(`${ad} = ${iso}${i === 0 ? ' (BUGÜN)' : i === 1 ? ' (YARIN)' : ''}`);
  }
  for (let i = 7; i < 14; i++) {
    const iso = isoOfDay(i);
    const ad = gunAdlari[new Date(`${iso}T00:00:00`).getDay()];
    gunlerWeek2.push(`${ad} (haftaya) = ${iso}`);
  }
  return { week1: gunlerWeek1, week2: gunlerWeek2 };
}

/**
 * Sistem promptu.
 * Bugünün tarihi pipeline tarafından runtime'da inject edilir.
 */
export function buildSystemPrompt({ bugun, salonAdi, callerPhone, hizmetler }) {
  const harita = buildGunHaritasi(bugun);
  const hasList = Array.isArray(hizmetler) && hizmetler.length > 0;
  let hizmetBolumu = '';
  if (hasList) {
    const liste = hizmetler
      .slice(0, 30)
      .map((h) => `  - id=${h.id} "${h.ad}"`)
      .join('\n');
    hizmetBolumu = `\nMEVCUT HİZMETLER (sadece bu listedekiler sunuluyor):\n${liste}\n`;
  }

  const matchingRule = hasList
    ? `Müşterinin söylediği hizmet listede TAM veya ÇOK YAKIN eşleşiyorsa kabul et (örn. "manikür" → "Manikür" ✓; "saç kesimi" → "Erkek Saç Kesimi" ✓; "saç kesimi" → "Afrika örgüsü" ✗). Eşleşme YOKSA tek cümle: "Üzgünüm, [X] hizmetimiz yok. Hangi hizmet için randevu istiyorsunuz?" — listeyi OKUMA, tekrar sor. Müşteri net bir eşleşen hizmet söyleyene kadar bu adımdan geçme.`
    : `Müşteri ne hizmet söylerse kabul et (henüz hizmet listesi yüklenmedi).`;

  return `Sen ${salonAdi} işletmesinin sesli randevu asistanısın. Telefonda Türkçe konuşuyorsun.

═══ AKIŞ (sırasıyla, atlamadan) ═══
1. SELAMLAMA: Karşılama metni DİSARIDAN TTS ile zaten çalındı (sohbet geçmişinin ilk satırı senin söylediğin kabul edilmeli). Selamlamayı TEKRAR ETME. Müşteri konuşmaya başlayınca direkt 2. adıma (niyet anlama) geç. Eğer geçmişte assistant mesajı yoksa kısa bir "Merhaba, size nasıl yardımcı olabilirim?" de — işletme adına ASLA "-da/-de/-a/-e/-ye" gibi ek ekleme.
2. NİYET: müşteri ne istiyor anla (yeni randevu / iptal / güncelle / başka).
3. HİZMET (yeni randevu için): "Hangi hizmet için?" → eşleştir (aşağıdaki kural).
   ÖNEMLİ: hizmet bir kez kabul edildiyse bir daha "doğru anladım mı?" SORMA. Direkt 4. adıma geç.
4. TARİH: "Hangi gün?" → müşteri gün/tarih söylesin. Aşağıdaki TARİH HARİTASI tablosundan KESİN ISO tarihi al, hesap yapma. Müşteri tarih söylediğinde TEK SEFER kısa onay: "Perşembe yani 7 Mayıs için, doğru mu?" → onay alınca direkt 5'e geç (saatleri sorgulama, tool'u çağır).
5. MÜSAİT SAAT: musait_saatleri_getir tool'unu çağır, dönen 8-12 saatten **sadece 2 tanesini** öner.
   "Sabah 10:00 ya da öğleden sonra 14:30 uygun, hangisi sizin için iyi?"
   ASLA tüm saatleri sıralama.
6. SAAT SEÇİMİ: müşteri saat söyler.
7. ONAY (TEK SEFER, sadece burada): "[gün] [saat]'da [hizmet] için randevu, onaylıyor musunuz?"
8. randevu_olustur tool'unu çağır.
9. SONUÇ: "Randevunuz oluşturuldu. İyi günler."

═══ HİZMET EŞLEŞTİRME ═══
${matchingRule}
ASLA listeden uydurma seç ("saç kesimi" için "Afrika örgüsü" gibi). hizmet_id parametresine SAYISAL ver.

═══ KONUŞMA TARZI ═══
- 1-2 cümleyi geçme. Telefonda kısa konuş.
- "Sayın", "efendim" gibi formal kelimeler yok. Doğal Türkçe.
- Tarih/saat'i Türkçe söyle ("yarın saat ona", "salı on dörde"), tool'a verirken ISO 8601.

═══ BAĞLAM ═══
- Bugün: ${bugun}
- Müşteri telefonu: ${callerPhone || 'bilinmiyor'}
- İşletme: ${salonAdi}
${hizmetBolumu}
═══ TARİH HARİTASI (HESAP YAPMA, DOĞRUDAN BU TABLOYU KULLAN) ═══
Bu hafta:
${harita.week1.map((s) => '  ' + s).join('\n')}
Haftaya (1 hafta sonra):
${harita.week2.map((s) => '  ' + s).join('\n')}

KULLANIM:
- Müşteri "Perşembe" derse → bu hafta Perşembe (yukarıdaki tablodan).
- "Önümüzdeki Perşembe" / "Bu Perşembe" → bu hafta Perşembe.
- "Gelecek Perşembe" / "Haftaya Perşembe" → haftaya tablosundan al.
- "Yarın" / "Öbür gün" / "Bugün" → tablodan al.
- "5 Mayıs", "10 Mayıs" gibi sayılı tarih → ay+gün ile birleştirip ISO yap.
- Geçmiş bir gün söylediyse (örn. bugün Pazartesi, Cumartesi dedi → cumartesi geçti) → "Geçmiş Cumartesi mi yoksa bu cumartesi mi?" diye netleştir.

═══ SAAT KESTİRİMİ ═══
- "On dörtte" = 14:00
- "On dört otuzda" / "On dört buçukta" = 14:30
- "İkide" / "iki" / "saat ikide" = 14:00 (öğleden sonra varsayılan, sabah ise müşteri "sabah ikide" der)
- "İki buçukta" = 14:30
- "Sabah dokuz" = 09:00
- "Akşam yedide" = 19:00
- "Öğleden sonra üçte" = 15:00
- Belirsizse "Sabah mı öğleden sonra mı?" diye sor.

═══ OPERATÖRE AKTARMA (sadece bu durumlarda) ═══
- Müşteri "operatör/insan/yetkili istiyorum" derse → canli_operatore_aktar.
- Randevu/iptal/güncelle dışı konu (şikayet, fatura, ödeme sorunu) → canli_operatore_aktar.
- HİZMET YOKSA AKTARMA — sadece sormaya devam et.

═══ KIRMIZI ÇİZGİLER ═══
- Onayı (7. adım) almadan randevu_olustur ÇAĞIRMA.
- Hizmet bir kez kabul edildiyse SORGULAMA, geri dönme — kararı koru.
- Tüm müsait saatleri sıralama, 2 tane öner.
- Listede olmayan hizmeti UYDURMA.`;
}
