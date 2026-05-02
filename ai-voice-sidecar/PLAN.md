# AI Sesli Randevu Asistanı — Plan

Mevcut Asterisk IVR sistemini, AI tabanlı doğal konuşan sesli asistan ile değiştirme/genişletme planı.

## Hedef akış

```
Müşteri arar
   ↓
Asterisk PBX → AI menü extension'ına yönlendirir
   ↓
ARI sidecar (Node.js) konuşmayı yönetir:
   1. Müşteri konuşur → STT (Groq Whisper) → metin
   2. Metin → LLM (Groq Llama 3.3 70B) → niyet + slot
   3. Niyet → Laravel API → randevu CRUD
   4. Cevap metni → TTS (Edge-TTS) → ses
   5. Ses → Asterisk → müşteri kulağı
   ↓
Tamamlandı → kapat veya canlı operatöre devret
```

## Bileşenler

| Bileşen | Servis | Maliyet | Açıklama |
|---|---|---|---|
| **STT** | Groq Whisper API (whisper-large-v3) | Free tier (yoğun kullanımda ~$0.00185/dk) | Ses → Türkçe metin, ~250ms latency |
| **LLM** | Groq Llama 3.3 70B Versatile | Free tier 14.4K req/gün (sonra ~$0.59/M token) | Niyet anlama + Türkçe tarih/saat parsing + tool calling |
| **TTS** | Edge-TTS (Microsoft Neural, "tr-TR-EmelNeural" / "tr-TR-AhmetNeural") | **Ücretsiz** | Akıcı Türkçe ses, ~300ms |
| **Bağlantı** | Asterisk ARI + External Media (RTP audio) | Mevcut PBX'te kurulum | WebSocket ile ses akışı |
| **Backend** | Laravel API (mevcut sistem + yeni AI endpoint'leri) | $0 ek | Randevu CRUD |

**Hedef tek-tur latency: 1-1.5 saniye** (müşteri konuşmayı bitirdi → sistem cevap sesi başladı)

## Faz planı

### Faz 0 — Hazırlık (yazılımcı arkadaş ile birlikte) ✅ ŞU AN BURADA
- [ ] Asterisk versiyonunu doğrula (16+ olmalı, ARI external-media için)
- [ ] Mevcut IVR menüsünü çıkar (hangi tuş ne yapıyor?)
- [ ] Sunucu rolleri: Asterisk nerede? Sidecar nerede çalışacak? (aynı sunucu / ayrı sunucu)
- [ ] Test telefon hattı / test SIP extension'ı hazırla
- [ ] Groq API key oluştur (https://console.groq.com)

### Faz 1 — Sidecar iskeleti (ASTERISK YOK, sadece pipeline test)
- [x] Klasör yapısı + package.json + README
- [ ] Edge-TTS modülü → `node test/test-tts.js "merhaba dünya"` mp3 üretsin
- [ ] Groq Whisper STT → ses dosyası → metin
- [ ] Groq LLM + tool definitions (randevu_al, randevu_iptal, vs.)
- [ ] Laravel API mock client
- [ ] Dialog state machine (basit FSM)
- [ ] CLI test: ses dosyası girişi → tüm pipeline → cevap ses dosyası

**Çıktı: Komut satırından test edilebilen tam pipeline. Asterisk'siz.**

### Faz 2 — Asterisk ARI entegrasyonu
- [ ] `ari-client` ile bağlan, bir extension'da Stasis app'i başlat
- [ ] External Media kanalı (RTP server)
- [ ] Audio buffering + VAD (voice activity detection - susunca STT'ye gönder)
- [ ] Sidecar → Asterisk audio playback (PCM/μ-law dönüşümü)
- [ ] Test extension'ında 1 test arama akışı

### Faz 3 — Laravel API uçları
- [ ] `POST /api/ai/musait-saatler` (salon_id + tarih → boş slotlar)
- [ ] `POST /api/ai/randevu-olustur` (telefon + tarih + saat + hizmet → randevu)
- [ ] `POST /api/ai/randevu-iptal` (telefon + tarih → iptal)
- [ ] `POST /api/ai/musteri-bul` (telefon → müşteri kaydı veya yeni)
- [ ] API token middleware (sadece sidecar erişebilsin)

### Faz 4 — Production sertleştirme
- [ ] Hata yönetimi (Groq down → Edge-TTS ile özür + canlı operatöre devret)
- [ ] Konuşma loglama (her çağrı için STT/LLM/TTS kayıtları DB'ye)
- [ ] Müşteri onayı: "Salı saat 14'e ekledim, doğru mu?" (yanlış slot atamayı önle)
- [ ] Karaliste (kötü amaçlı arayan koru)
- [ ] Çoklu salon desteği (arayan numaradan salon tespit)
- [ ] Monitoring (turn süresi, başarı oranı, transfer oranı)

### Faz 5 — TTS yükseltmesi (opsiyonel)
- [ ] ElevenLabs Multilingual v2'ye TTS modülünü değiştir (1 saat iş)
- [ ] A/B test: hangi salonda ElevenLabs (premium), hangisinde Edge-TTS (standart)

## Kritik karar noktaları

1. **Sidecar konumu:** Aynı sunucuda mı (Asterisk + sidecar) yoksa ayrı mı?
   - Aynı sunucu: latency düşük, deploy basit
   - Ayrı sunucu: kaynak izolasyonu, ölçek kolay
   - **Öneri:** Başta aynı sunucu, sorun olunca ayır

2. **Audio codec:** Asterisk → Sidecar arası ne format?
   - μ-law 8kHz (telefon standardı, küçük) → STT'den önce 16kHz'e upsample
   - **Öneri:** μ-law 8kHz, RTP üzerinden

3. **VAD (sustu mu konuşuyor mu):**
   - **WebRTC VAD** (Node.js: `node-vad`) — küçük, hızlı
   - Sustuktan sonra 800ms bekle → STT tetikle

4. **Yarım konuşma kesintisi:**
   - Sistem cevap verirken müşteri konuşursa? → "Barge-in" desteği
   - Faz 4'te ekle, Faz 1-2'de basit "sırayla konuş" mantığı

## Riskler ve azaltma

| Risk | Azaltma |
|---|---|
| Groq free tier kotası dolar | Ücretli plana geç ($0.05/M token, ucuz) |
| Edge-TTS API'sinin Microsoft'tan kapanması | Piper'a fallback (yerel, GPU'suz çalışır) |
| Whisper Türkçe yanlış transcribe | Onaylama turu ekle: "Çarşamba dediniz, doğru mu?" |
| Müşteri AI'yı sevmez | "1'e basın canlı operatöre bağlayalım" fallback |
| Latency >2sn | Pipeline paralelleştir: TTS'i streaming başlat |

## Kurulum (Faz 1 için)

```bash
cd ai-voice-sidecar
npm install
cp .env.example .env
# .env içine GROQ_API_KEY ekle
node test/test-tts.js "merhaba ben randevumcepte sesli asistanı"
# → output/test.mp3 üretir
```

## Sonraki adım

Faz 0'daki kontrolleri tamamla, Groq API key al, sonra `npm install && node test/test-tts.js` çalıştır. Ses üretiyorsa pipeline'a devam.
