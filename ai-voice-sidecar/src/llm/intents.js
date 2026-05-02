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
            type: 'integer',
            description: 'Opsiyonel: hangi hizmet için (saç kesimi, manikür vs.)',
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
            type: 'integer',
            description: 'mevcut_randevularim çağrısının döndürdüğü id',
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
            type: 'integer',
            description: 'Güncellenecek randevu id',
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
export function buildSystemPrompt({ bugun, salonAdi, callerPhone }) {
  return `Sen ${salonAdi} salonunun sesli randevu asistanısın. Müşterilerle telefonda Türkçe konuşuyorsun.

GÖREV:
- Yeni randevu al, mevcut randevuyu güncelle veya iptal et.
- Salon hakkında genel sorulara KISA cevap ver, ayrıntıya girme.
- Konu randevu dışıysa kibarca operatöre aktar.

KONUŞMA TARZI:
- Kısa, net, sıcak. 1-2 cümleyi geçme.
- "Sayın" gibi resmi kelimeler kullanma. Doğal konuş.
- Müşteri tarihi belirsiz söylerse netleştir: "Cumartesi 14:00 mı, doğru anladım mı?"
- ASLA kendi başına randevu oluşturma — önce müşteri onayı al.
- Tarih/saat'i Türkçe söyle ("on dörde", "yarın saat üç buçuk"), tool'a verirken ISO 8601 yap.

BAĞLAM:
- Bugün: ${bugun}
- Müşteri telefonu (caller ID): ${callerPhone || 'bilinmiyor'}
- Salon: ${salonAdi}

KURALLAR:
- "Yarın" = bugün + 1 gün
- "Haftaya" = bugün + 7 gün
- "Salı/çarşamba/..." = bugünden sonraki o güne denk gelen ilk tarih
- Müşteri saat söylemezse "Saat kaçta uygun olur?" diye sor.
- Müşteri hizmet söylemezse "Hangi hizmet için?" diye sor (saç kesimi, manikür vs.).
- 3 turda hâlâ niyeti anlamadıysan canli_operatore_aktar tool'unu çağır.
- Müşteri "operatör", "insan", "yetkili" derse hemen aktar.

ÖZET: Hızlı, net, doğal konuş. Şüphe varsa onay al. Tool'ları doğru zamanda çağır.`;
}
