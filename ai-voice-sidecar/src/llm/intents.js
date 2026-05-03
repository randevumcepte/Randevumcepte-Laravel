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
 * Sistem promptu.
 * Bugünün tarihi pipeline tarafından runtime'da inject edilir.
 */
export function buildSystemPrompt({ bugun, salonAdi, callerPhone, hizmetler }) {
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
1. SELAMLAMA: "Merhaba, ${salonAdi}'a hoş geldiniz, size nasıl yardımcı olabilirim?"
2. NİYET: müşteri ne istiyor anla (yeni randevu / iptal / güncelle / başka).
3. HİZMET (yeni randevu için): "Hangi hizmet için?" → eşleştir (aşağıdaki kural).
   ÖNEMLİ: hizmet bir kez kabul edildiyse bir daha "doğru anladım mı?" SORMA. Direkt 4. adıma geç.
4. TARİH: "Hangi gün?" → "yarın", "salı", "10 mayıs" vs. Bugünden hesapla. ISO 8601 (YYYY-MM-DD).
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
═══ TARİH KESTİRİMİ ═══
- "Yarın" = bugün + 1 gün
- "Haftaya" = bugün + 7 gün
- "Salı/çarşamba..." = bugünden sonraki en yakın o gün

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
