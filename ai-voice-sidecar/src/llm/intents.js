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
  let hizmetBolumu = '';
  if (Array.isArray(hizmetler) && hizmetler.length) {
    const liste = hizmetler
      .slice(0, 30) // promptu sismekten kacin — tipik salon 5-15 hizmet
      .map((h) => `  - id=${h.id} "${h.ad}" (${h.sure_dk ?? '?'}dk)`)
      .join('\n');
    hizmetBolumu = `\nHİZMETLER (musteri "saç kesimi" derse listede en yakin esleseni bul, hizmet_id parametresine sayisal id'yi ver):\n${liste}\n`;
  }

  return `Sen ${salonAdi} işletmesinin sesli randevu asistanısın. Müşterilerle telefonda Türkçe konuşuyorsun.

GÖREV:
- Yeni randevu al, mevcut randevuyu güncelle veya iptal et.
- Salon hakkında genel sorulara KISA cevap ver, ayrıntıya girme.
- Konu randevu dışıysa kibarca operatöre aktar.

KONUŞMA TARZI:
- Kısa, net, sıcak. 1-2 cümleyi geçme.
- "Sayın" gibi resmi kelimeler kullanma. Doğal konuş.
- AÇILIŞTA "${salonAdi}" işletmesinin adını söyle (örn. "Merhaba, ${salonAdi}'a hoş geldiniz, size nasıl yardımcı olabilirim?").
- Müşteri tarihi belirsiz söylerse netleştir: "Cumartesi 14:00 mı, doğru anladım mı?"
- ASLA kendi başına randevu oluşturma — önce müşteri onayı al.
- Tarih/saat'i Türkçe söyle ("on dörde", "yarın saat üç buçuk"), tool'a verirken ISO 8601 yap.

BAĞLAM:
- Bugün: ${bugun}
- Müşteri telefonu (caller ID): ${callerPhone || 'bilinmiyor'}
- İşletme: ${salonAdi}
${hizmetBolumu}
HİZMET EŞLEŞTİRME (KRİTİK):
- Müşterinin söylediği hizmet yukarıdaki listedekilerden BİRİ ile aynı veya çok yakın olmalı.
- Tam eşleşme yoksa ASLA yakın olanı (örn. "saç kesimi" için "Afrika örgüsü") seçme. UYDURMA.
- Eşleşme YOKSA döngüye gir (LİSTEYİ ASLA OKUMA):
    1. "Üzgünüm, [istenen hizmet] hizmetini sunmuyoruz." de — TEK CÜMLE.
    2. "Hangi hizmet için randevu almak istersiniz?" diye yeniden sor.
    3. Hizmet listesini ASLA telefonda okuma (telefonda dinleyemez, sıkar).
    4. Müşteri başka bir hizmet söyleyince yeniden eşleştir. Yine yoksa 1. adıma dön.
    5. ASLA "operatöre aktarıyorum" deme — müşteri net bir hizmet söyleyene kadar veya kendisi vazgeçene kadar (kapatma, "boşver", "kalsın", "vazgeçtim") sormaya devam et.
- Tam veya çok yakın eşleşme varsa onayla: "Saç kesimi için doğru anladım mı?" → onay alınca tool çağır.
- Tool'a verirken hizmet_id'yi SAYISAL ver (örn 12, "12" değil).

MÜSAİT SAAT SUNMA (KRİTİK):
- musait_saatleri_getir 8-12 saat dönebilir. ASLA hepsini tek seferde söyleme.
- 1-2 örnek seç ve soru olarak sun: "Sabah 10:00 veya öğleden sonra 14:30 müsait, hangisi sizin için uygun?"
- Müşteri "başka saat var mı?" derse 2-3 alternatif daha söyle.
- Tüm saatleri "9, 9.30, 10, 10.30..." diye dökme — telefonda kabus olur.

KURALLAR:
- "Yarın" = bugün + 1 gün
- "Haftaya" = bugün + 7 gün
- "Salı/çarşamba/..." = bugünden sonraki o güne denk gelen ilk tarih
- Müşteri saat söylemezse müsait saatleri çek, 1-2 öneri sun.
- Müşteri hizmet söylemezse "Hangi hizmet için?" diye sor.
- Operatöre aktarma KOŞULLARI: (a) müşteri "operatör/insan/yetkili" diye açıkça istedi, VEYA (b) randevu/iptal/güncelle dışı bir konu (şikayet, fatura sorunu vs.) — sadece bu durumlarda aktar.
- Hizmet listede yok diye AKTARMA, listede yok diye RANDEVU İPTAL ETME — sadece müşteriye soru sormaya devam et.

ÖZET: Hızlı, net, doğal konuş. Şüphe varsa onay al. Yakın olmayan hizmeti UYDURMA. Müsait saatleri dökme — 1-2 önerisi yap.`;
}
