import { llm } from '../llm/groq-llm.js';
import { makeToolHandlers } from '../api/laravel.js';

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
  constructor({ salonId, salonAdi, callerPhone, hizmetler }) {
    this.salonId = salonId;
    this.salonAdi = salonAdi;
    this.callerPhone = callerPhone;
    this.hizmetler = Array.isArray(hizmetler) ? hizmetler : [];
    this.messages = []; // chat history (system prompt buildSystemPrompt'ta otomatik eklenir)
    this.toolHandlers = makeToolHandlers({ salonId, callerPhone });
    this.turnCount = 0;
    this.transferred = false;
    this.bugun = new Date().toISOString().slice(0, 10);
  }

  context() {
    return {
      salonAdi: this.salonAdi,
      callerPhone: this.callerPhone,
      bugun: this.bugun,
      hizmetler: this.hizmetler,
    };
  }

  /**
   * Bir konuşma turu işle.
   * @param {string} userText - müşterinin söylediği (STT çıktısı). null = ilk açılış selamı
   * @returns {Promise<{reply: string, action: string|null, durations: Object}>}
   */
  async turn(userText) {
    this.turnCount++;
    const durations = {};

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
      const t0 = Date.now();
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
    };
  }
}
