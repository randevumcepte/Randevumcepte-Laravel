# AI Voice Sidecar — Randevumcepte

Asterisk PBX için AI tabanlı sesli randevu asistanı sidecar'ı.

**Stack:**
- STT: Groq Whisper API (whisper-large-v3)
- LLM: Groq Llama 3.3 70B Versatile (tool calling ile)
- TTS: Edge-TTS (Microsoft Neural, Türkçe)
- Asterisk bağlantısı: ARI + External Media (Faz 2)

**Maliyet:** Faz 1 ücretsiz (Groq free tier + Edge-TTS). Yoğun kullanımda ~$10-30/ay.

## Hızlı başlangıç

```bash
cd ai-voice-sidecar
npm install
cp .env.example .env
# .env içine GROQ_API_KEY (https://console.groq.com → API Keys) ekle

# 1. TTS smoke test (Türkçe ses dosyası üretsin)
node test/test-tts.js "merhaba ben randevumcepte sesli asistanı"
# output/test.mp3 dinle

# 2. STT smoke test (ses dosyası → metin)
node test/test-stt.js output/test.mp3

# 3. Tam pipeline (metin girdisi → AI cevabı + ses)
node test/test-pipeline.js
```

## Klasör yapısı

```
ai-voice-sidecar/
├── PLAN.md             ← Yol haritası (yazılımcı arkadaşa göster)
├── README.md           ← bu dosya
├── package.json
├── .env.example        ← API key şablonu
├── src/
│   ├── config.js       ← env yükleme
│   ├── tts/edge-tts.js ← Microsoft Edge TTS wrapper
│   ├── stt/groq-stt.js ← Groq Whisper wrapper
│   ├── llm/groq-llm.js ← Groq LLM client
│   ├── llm/intents.js  ← Randevu tool tanımları
│   ├── api/laravel.js  ← Laravel backend API client
│   └── dialog/state.js ← Konuşma state machine
├── test/
│   ├── test-tts.js
│   ├── test-stt.js
│   └── test-pipeline.js
└── output/             ← Test ses çıktıları (gitignore)
```

## Faz durumu

- ✅ Faz 1: POC pipeline (CLI testi) — şu an
- ⬜ Faz 2: Asterisk ARI entegrasyonu
- ⬜ Faz 3: Laravel API uçları
- ⬜ Faz 4: Production hardening
- ⬜ Faz 5: ElevenLabs upgrade (opsiyonel)

Detay: [PLAN.md](./PLAN.md)
