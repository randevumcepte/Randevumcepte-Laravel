import Groq from 'groq-sdk';
import { config } from '../config.js';
import { tools, buildSystemPrompt } from './intents.js';

/**
 * Groq Llama 3.3 70B Versatile wrapper.
 * Tool calling ile niyet anlamayı yönetir.
 *
 * Free tier: 14.4K req/gün (bir randevu çağrısı ~5-10 LLM çağrısı,
 *            yani ~1500-3000 randevu/gün ücretsiz tutar).
 * Ücretli: $0.59/M input + $0.79/M output token (çok ucuz).
 */
export class GroqLLM {
  constructor() {
    this.client = new Groq({ apiKey: config.groq.apiKey });
    this.model = config.groq.llmModel;
  }

  /**
   * Bir konuşma turunu çözümle.
   * @param {Object} params
   * @param {Array} params.messages - chat history (system + user + assistant + tool)
   * @param {Object} params.context - { salonAdi, callerPhone, bugun }
   * @returns {Promise<{content: string|null, tool_calls: Array, raw}>}
   */
  async respond({ messages, context }) {
    const systemMsg = {
      role: 'system',
      content: buildSystemPrompt({
        bugun: context.bugun ?? new Date().toISOString().slice(0, 10),
        salonAdi: context.salonAdi ?? 'Salon',
        callerPhone: context.callerPhone ?? null,
      }),
    };

    const t0 = Date.now();
    const completion = await this.client.chat.completions.create({
      model: this.model,
      messages: [systemMsg, ...messages],
      tools,
      tool_choice: 'auto',
      temperature: 0.3,
      max_tokens: 300,
    });

    const choice = completion.choices?.[0];
    const msg = choice?.message ?? {};

    return {
      content: msg.content ?? null,
      tool_calls: msg.tool_calls ?? [],
      durationMs: Date.now() - t0,
      raw: completion,
      // Chat history'ye eklenecek mesaj
      assistantMessage: {
        role: 'assistant',
        content: msg.content,
        tool_calls: msg.tool_calls,
      },
    };
  }
}

export const llm = new GroqLLM();
