import { llm } from '../llm/groq-llm.js';
import { makeToolHandlers } from '../api/laravel.js';

/**
 * "Bugün"ü İstanbul saatiyle döndürür (YYYY-MM-DD).
 * Eski kod new Date().toISOString() (UTC) kullanıyordu; gece yarısı civarı
 * "bugün" bir gün yanlış çıkabiliyordu. Bu deterministik ve tz-güvenli.
 */
export function istanbulBugun() {
  return new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Europe/Istanbul',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date());
}

/**
 * Dialog state machine.
 *
 * Bir telefon çağrısı = bir Conversation instance. Her turda:
 *   1. Müşteri girdisi (STT'den gelen metin) eklenir
 *   2. LLM çağrılır
 *   3. LLM tool çağırırsa: tool çalıştırılır, sonuç tekrar LLM'e verilir
 *   4. LLM düz metin dönerse: TTS'e gönderilir
 *   5. Akış "transfer" veya "hangup" olana kadar devam
 */
export class Conversation {
  constructor({ salonId, salonAdi, callerPhone, hizmetler, karsilamaTelaffuz, musteriAdi, paketler }) {
    this.salonId = salonId;
    this.salonAdi = salonAdi;
    this.callerPhone = callerPhone;
    this.hizmetler = Array.isArray(hizmetler) ? hizmetler : [];
    this.karsilamaTelaffuz = karsilamaTelaffuz || null;
    this.musteriAdi = musteriAdi || null;
    this.paketler = Array.isArray(paketler) ? paketler : [];
    this.messages = []; // chat history (system prompt buildSystemPrompt'ta otomatik eklenir)
    // karsilama_telaffuz tanimliysa LLM'e "ilk soyledigin sey buydu" diye seed et,
    // boylece LLM ikinci turda akisi devam ettirir (selamlamayi tekrar etmez).
    if (this.karsilamaTelaffuz) {
      this.messages.push({ role: 'assistant', content: this.karsilamaTelaffuz });
    }
    this.toolHandlers = makeToolHandlers({ salonId, callerPhone });
    this.turnCount = 0;
    this.transferred = false;
    this.bugun = istanbulBugun();
    // Loglama için: son randevu id (randevu_olustur sonucu) ve tool özetleri
    this.sonRandevuId = null;
  }

  context() {
    return {
      salonAdi: this.salonAdi,
      callerPhone: this.callerPhone,
      bugun: this.bugun,
      hizmetler: this.hizmetler,
      musteriAdi: this.musteriAdi,
      paketler: this.paketler,
    };
  }

  /**
   * Whisper STT için hotword/bağlam metni — salon adı + hizmet adları +
   * onay kelimeleri. Domain kelimelerinin doğru transkript edilmesini artırır.
   */
  sttPrompt() {
    const parcalar = [];
    if (this.salonAdi) parcalar.push(`${this.salonAdi} güzellik salonu randevu görüşmesi.`);
    if (this.hizmetler.length) {
      const adlar = this.hizmetler.slice(0, 25).map((h) => h.ad).filter(Boolean).join(', ');
      if (adlar) parcalar.push(`Hizmetler: ${adlar}.`);
    }
    parcalar.push('Geçebilecek kelimeler: evet, onaylıyorum, tabii, olur, tamam, hayır, iptal, yarın, bugün, pazartesi, salı, çarşamba, perşembe, cuma, cumartesi, pazar, sabah, öğleden sonra, akşam, saat.');
    return parcalar.join(' ');
  }

  /**
   * Bir konuşma turu işle.
   * @param {string} userText - müşterinin söylediği (STT çıktısı). null = ilk açılış selamı
   * @returns {Promise<{reply, action, durations, turnCount, tools}>}
   */
  async turn(userText) {
    this.turnCount++;
    const durations = {};
    const toolsBuKez = []; // bu turda çalışan tool'ların özeti (loglama için)

    if (userText) {
      this.messages.push({ role: 'user', content: userText });
    } else if (this.messages.length === 0) {
      // İlk açılış: kullanıcı henüz konuşmadı, sistem selamlamayla başlasın
      this.messages.push({
        role: 'user',
        content: '(çağrı başladı, kullanıcıyı kısaca selamla)',
      });
    }

    // LLM çağrısı (gerekirse tool döngüsü ile)
    let safeguard = 5; // tool döngüsü sonsuza gitmesin
    let finalReply = null;
    let action = null;

    while (safeguard-- > 0) {
      const r = await llm.respond({
        messages: this.messages,
        context: this.context(),
      });
      durations.llm = (durations.llm || 0) + r.durationMs;

      this.messages.push(r.assistantMessage);

      if (r.tool_calls && r.tool_calls.length > 0) {
        // Tool çağrılarını çalıştır
        for (const tc of r.tool_calls) {
          const name = tc.function.name;
          let args = {};
          try {
            args = JSON.parse(tc.function.arguments || '{}');
          } catch (e) {
            args = {};
          }
          const handler = this.toolHandlers[name];
          let result;
          try {
            const tt0 = Date.now();
            result = handler ? await handler(args) : { error: `Bilinmeyen tool: ${name}` };
            durations.tool = (durations.tool || 0) + (Date.now() - tt0);
          } catch (e) {
            result = { error: e.message || String(e) };
          }

          // randevu_olustur başarılıysa randevu id'sini yakala (loglama + sonuç)
          if (name === 'randevu_olustur' && result && result.randevu_id) {
            this.sonRandevuId = result.randevu_id;
          }

          // Tool özeti (loglama için — ham sonucu değil kısa özet)
          toolsBuKez.push({
            name,
            args,
            ozet: this._toolOzet(name, result),
          });

          // Tool sonucunu chat history'ye ekle
          this.messages.push({
            role: 'tool',
            tool_call_id: tc.id,
            content: JSON.stringify(result),
          });

          // Transfer ise akışı kes
          if (name === 'canli_operatore_aktar' || result?.action === 'transfer') {
            action = 'transfer';
            this.transferred = true;
          }
        }
        // Tool sonucu sonrası LLM tekrar konuşsun
        continue;
      }

      // Düz metin cevabı → bu turu bitir
      finalReply = r.content || '';
      break;
    }

    return {
      reply: (finalReply || '').trim(),
      action,
      durations,
      turnCount: this.turnCount,
      tools: toolsBuKez,
    };
  }

  /** Tool sonucundan kısa, loga yazılabilir özet çıkar. */
  _toolOzet(name, result) {
    if (!result) return '';
    if (result.error) return `hata: ${result.error}`;
    switch (name) {
      case 'hizmet_eslestir':
        if (result.eslesme === 'tek') return `tek: ${result.hizmet?.ad || ''}`;
        if (result.eslesme === 'coklu') return `coklu: ${(result.secenekler || []).map((s) => s.ad).join(' | ')}`;
        return `yok${result.oneriler?.length ? ' (öneri: ' + result.oneriler.map((o) => o.ad).join(', ') + ')' : ''}`;
      case 'musait_saatleri_getir':
        return `saatler: ${(result.saatler || []).join(', ')}`;
      case 'randevu_olustur':
        return result.ok ? `randevu #${result.randevu_id}` : `başarısız: ${result.mesaj || ''}`;
      case 'randevu_iptal':
      case 'randevu_guncelle':
        return result.mesaj || (result.ok ? 'ok' : 'başarısız');
      case 'mevcut_randevularim':
        return `${(result.randevular || result || []).length || 0} randevu`;
      case 'canli_operatore_aktar':
        return `transfer: ${result.sebep || ''}`;
      default:
        return '';
    }
  }
}
