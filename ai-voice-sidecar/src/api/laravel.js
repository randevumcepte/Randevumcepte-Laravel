import axios from 'axios';
import { config } from '../config.js';

/**
 * Laravel API client.
 *
 * NOT: Bu uçlar Faz 3'te Laravel tarafında oluşturulacak. Şimdilik mock
 * mode (USE_MOCK=true) ile cevap dönüyor, böylece sidecar pipeline'ını
 * Laravel API'siz test edebiliriz.
 */
const USE_MOCK = !config.laravel.token; // token yoksa mock

const http = axios.create({
  baseURL: config.laravel.base,
  timeout: 8000,
  headers: config.laravel.token
    ? { Authorization: `Bearer ${config.laravel.token}` }
    : {},
});

/* ───────── Mock implementations ───────── */

const mockMusait = (tarih) => ({
  tarih,
  saatler: ['09:00', '10:30', '13:00', '14:00', '15:30', '17:00'],
});

const mockRandevular = (telefon) => [
  {
    id: 12345,
    telefon,
    tarih_saat: '2026-05-10T14:00:00',
    hizmet: 'Saç Kesimi',
    personel: 'Ayşe Hanım',
  },
];

/* ───────── Public API ───────── */

/**
 * Salon bilgilerini cek — cagri basinda sidecar bunu cagirir, salon adini
 * sistem promptuna gomer ve hizmet listesini LLM'e ad->id eslestirmesi
 * icin verir.
 */
export async function salonBilgiGetir({ salonId }) {
  if (USE_MOCK) {
    return {
      ok: true,
      id: salonId,
      ad: process.env.SALON_ADI || `Salon ${salonId}`,
      adres: '',
      telefon: '',
      hizmetler: [],
    };
  }
  const { data } = await http.post('/v1/ai/salon-bilgi', { salon_id: salonId });
  return data;
}

export async function musaitSaatleriGetir({ salonId, tarih, hizmetId, zamanDilimi }) {
  if (USE_MOCK) return mockMusait(tarih);
  const { data } = await http.post('/v1/ai/musait-saatler', {
    salon_id: salonId,
    tarih,
    hizmet_id: hizmetId,
    zaman_dilimi: zamanDilimi,
  });
  return data;
}

/**
 * Musterinin soyledigi hizmet ifadesini backend'de kuralli eslestir.
 * Donus: { eslesme: 'tek'|'coklu'|'yok', hizmet?, secenekler?, oneriler? }
 */
export async function hizmetEslestir({ salonId, metin }) {
  if (USE_MOCK) {
    return { ok: true, eslesme: 'tek', hizmet: { id: 1, ad: metin } };
  }
  const { data } = await http.post('/v1/ai/hizmet-eslestir', {
    salon_id: salonId,
    metin,
  });
  return data;
}

/**
 * Arayan numaradan musteriyi tani — selamlamayi kisisellestirmek ve paket
 * bilgisini soyleyebilmek icin cagri basinda cagrilir.
 * Donus: { ad, paketler: [{ad, kalan}] } (taninmiyorsa bos alanlar).
 */
export async function musteriBilgiGetir({ salonId, telefon }) {
  if (USE_MOCK || !telefon) return { ok: true, ad: null, paketler: [] };
  try {
    const { data } = await http.post('/v1/ai/musteri-bilgi', {
      salon_id: salonId,
      telefon,
    });
    return data;
  } catch (e) {
    // Tanima basarisiz olursa cagri yine de devam etsin
    return { ok: false, ad: null, paketler: [] };
  }
}

/**
 * Cagri bitiminde tum konusma dokumunu tek POST ile loglar (fire-and-forget).
 * Teshis icindir; hata olsa da cagri akisini etkilemez.
 */
export async function cagriLogGonder(payload) {
  if (USE_MOCK) return { ok: true, mock: true };
  try {
    const { data } = await http.post('/v1/ai/cagri-log', payload);
    return data;
  } catch (e) {
    return { ok: false, mesaj: e.message };
  }
}

export async function randevuOlustur({
  salonId,
  telefon,
  adSoyad,
  tarihSaat,
  hizmetId,
  notlar,
}) {
  if (USE_MOCK) {
    return {
      ok: true,
      randevu_id: 99999,
      mesaj: `Randevu oluşturuldu: ${tarihSaat} (mock)`,
    };
  }
  const { data } = await http.post('/v1/ai/randevu-olustur', {
    salon_id: salonId,
    telefon,
    ad_soyad: adSoyad,
    tarih_saat: tarihSaat,
    hizmet_id: hizmetId,
    notlar,
  });
  return data;
}

export async function mevcutRandevular({ salonId, telefon }) {
  if (USE_MOCK) return mockRandevular(telefon);
  const { data } = await http.post('/v1/ai/mevcut-randevular', {
    salon_id: salonId,
    telefon,
  });
  return data;
}

export async function randevuIptal({ salonId, randevuId }) {
  if (USE_MOCK) {
    return { ok: true, mesaj: `Randevu ${randevuId} iptal edildi (mock)` };
  }
  const { data } = await http.post('/v1/ai/randevu-iptal', {
    salon_id: salonId,
    randevu_id: randevuId,
  });
  return data;
}

export async function randevuGuncelle({
  salonId,
  randevuId,
  yeniTarihSaat,
}) {
  if (USE_MOCK) {
    return {
      ok: true,
      mesaj: `Randevu ${randevuId} → ${yeniTarihSaat} (mock)`,
    };
  }
  const { data } = await http.post('/v1/ai/randevu-guncelle', {
    salon_id: salonId,
    randevu_id: randevuId,
    yeni_tarih_saat: yeniTarihSaat,
  });
  return data;
}

/**
 * LLM bazen integer ID'leri string olarak donderir ("1" yerine 1).
 * Database int bekliyor — burada coerce et.
 */
function toInt(v) {
  if (v === undefined || v === null || v === '') return undefined;
  const n = typeof v === 'number' ? v : parseInt(String(v).trim(), 10);
  return Number.isFinite(n) ? n : undefined;
}

/**
 * Tool name → handler mapping. Dialog state machine bunu kullanır.
 */
export function makeToolHandlers({ salonId, callerPhone }) {
  return {
    hizmet_eslestir: async (args) =>
      hizmetEslestir({ salonId, metin: String(args.metin || '').trim() }),
    musait_saatleri_getir: async (args) =>
      musaitSaatleriGetir({
        salonId,
        tarih: args.tarih,
        hizmetId: toInt(args.hizmet_id),
        zamanDilimi: args.zaman_dilimi,
      }),
    randevu_olustur: async (args) =>
      randevuOlustur({
        salonId,
        telefon: args.telefon || callerPhone,
        adSoyad: args.ad_soyad,
        tarihSaat: args.tarih_saat,
        hizmetId: toInt(args.hizmet_id),
        notlar: args.notlar,
      }),
    mevcut_randevularim: async (args) =>
      mevcutRandevular({
        salonId,
        telefon: args.telefon || callerPhone,
      }),
    randevu_iptal: async (args) =>
      randevuIptal({ salonId, randevuId: toInt(args.randevu_id) }),
    randevu_guncelle: async (args) =>
      randevuGuncelle({
        salonId,
        randevuId: toInt(args.randevu_id),
        yeniTarihSaat: args.yeni_tarih_saat,
      }),
    canli_operatore_aktar: async (args) => ({
      ok: true,
      action: 'transfer',
      sebep: args.sebep,
      mesaj: 'Operatöre aktarılıyor.',
    }),
  };
}
