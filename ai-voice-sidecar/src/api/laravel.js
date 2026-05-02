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

export async function musaitSaatleriGetir({ salonId, tarih, hizmetId }) {
  if (USE_MOCK) return mockMusait(tarih);
  const { data } = await http.post('/ai/musait-saatler', {
    salon_id: salonId,
    tarih,
    hizmet_id: hizmetId,
  });
  return data;
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
  const { data } = await http.post('/ai/randevu-olustur', {
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
  const { data } = await http.post('/ai/mevcut-randevular', {
    salon_id: salonId,
    telefon,
  });
  return data;
}

export async function randevuIptal({ salonId, randevuId }) {
  if (USE_MOCK) {
    return { ok: true, mesaj: `Randevu ${randevuId} iptal edildi (mock)` };
  }
  const { data } = await http.post('/ai/randevu-iptal', {
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
  const { data } = await http.post('/ai/randevu-guncelle', {
    salon_id: salonId,
    randevu_id: randevuId,
    yeni_tarih_saat: yeniTarihSaat,
  });
  return data;
}

/**
 * Tool name → handler mapping. Dialog state machine bunu kullanır.
 */
export function makeToolHandlers({ salonId, callerPhone }) {
  return {
    musait_saatleri_getir: async (args) =>
      musaitSaatleriGetir({
        salonId,
        tarih: args.tarih,
        hizmetId: args.hizmet_id,
      }),
    randevu_olustur: async (args) =>
      randevuOlustur({
        salonId,
        telefon: args.telefon || callerPhone,
        adSoyad: args.ad_soyad,
        tarihSaat: args.tarih_saat,
        hizmetId: args.hizmet_id,
        notlar: args.notlar,
      }),
    mevcut_randevularim: async (args) =>
      mevcutRandevular({
        salonId,
        telefon: args.telefon || callerPhone,
      }),
    randevu_iptal: async (args) =>
      randevuIptal({ salonId, randevuId: args.randevu_id }),
    randevu_guncelle: async (args) =>
      randevuGuncelle({
        salonId,
        randevuId: args.randevu_id,
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
