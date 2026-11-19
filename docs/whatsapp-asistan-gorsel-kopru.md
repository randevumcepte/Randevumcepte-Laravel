# WhatsApp Asistanı — Köprüde fotoğraf iletimi (whatsmeow-bridge)

Laravel tarafı (AI Görsel Danışman) hazır; müşterinin gönderdiği model fotoğrafının asistana ulaşması için
sunucudaki `whatsmeow-bridge/main.go` içinde **gelen mesaj** bloğuna fotoğraf indirme eklenmeli.
Şu an köprü fotoğraf/sticker gibi medyayı `if text == "" ... return // medya/sticker vb. -> atla` satırında atlıyor.

## Laravel'in beklediği veri (`message.received`)

| alan      | değer                                                        |
|-----------|--------------------------------------------------------------|
| `type`    | `"image"`                                                    |
| `image`   | fotoğrafın **base64** verisi (JPEG / PNG / WebP, en fazla 5 MB) |
| `mime`    | `imageMessage.GetMimetype()` (bilgi amaçlı; Laravel içerikten doğrular) |
| `text`    | fotoğrafın açıklaması (caption), yoksa `""`                  |
| diğerleri | `salonId`, `from`, `fromJid`, `pushName`, `messageId` — metin mesajıyla aynı |

## Değişiklik (restoran köprüsündeki aynı blok baz alınmıştır)

```go
text := evt.Message.GetConversation()
if text == "" {
    if ext := evt.Message.GetExtendedTextMessage(); ext != nil {
        text = ext.GetText()
    }
}
payload := map[string]interface{}{ /* salonId, from, fromJid, text, pushName, messageId, type: "text" ... (mevcut) */ }

// --- YENI: fotograf -> base64 (5 MB ust sinir) ---
if img := evt.Message.GetImageMessage(); img != nil && img.GetFileLength() <= 5*1024*1024 {
    if data, err := sess.Client.Download(context.Background(), img); err == nil && len(data) > 0 {
        payload["type"] = "image"
        payload["image"] = base64.StdEncoding.EncodeToString(data)
        payload["mime"] = img.GetMimetype()
        payload["text"] = img.GetCaption()
        text = img.GetCaption() + " " // asagidaki "bos mesaj -> atla" kontrolune takilmasin
    } else {
        log.Printf("[%s] fotograf indirilemedi: %v", sess.SalonID, err)
    }
}
// --- /YENI ---

if text == "" && payload["type"] != "location" {
    return // medya/sticker vb. -> atla
}
```

`import` listesine `"encoding/base64"` eklenmeli (`context` zaten var). Eski whatsmeow sürümünde
`Download` bağlam almıyorsa `sess.Client.Download(img)` kullanın.

## Sunucuda

```bash
cd /var/www/www-root/data/www/randevumcepte/whatsmeow-bridge
go build -o whatsmeow-bridge . && pm2 restart 2   # (pm2 adi/no: pm2 ls)
```

Not: `whatsmeow-bridge/` deploy'da `.gitignore`'da; değişiklik git ile gitmez, sunucuda yapılır.
Webhook gövdesi büyüyeceği için nginx/PHP `client_max_body_size` / `post_max_size` en az 8 MB olmalı.

## Kontrol

1. İşletme paneli > WhatsApp Asistanı > **Fotoğraf Fiyatları**: ana işlem + ek fiyatlarını girin.
2. Personel OLMAYAN bir numaradan işletmenin hattına bir model fotoğrafı gönderin.
3. Laravel log: `[WA-ASISTAN] tur ... "kaynak":"gorsel"` ve müşteriye fiyatlı cevap.
4. Panel > Sohbetler'de fotoğraf, asistanın tarifi ve verdiği fiyat görünür.

---

# Profil fotoğrafı (panel > WhatsApp Asistanı > Son konuşmalar)

Laravel, müşterinin WhatsApp profil fotoğrafını köprüden `GET /session/{salonId}/avatar?jid=<jid>` ile ister
(`X-Service-Token` başlığı, diğer uçlarla aynı). Cevap: `{"url":"https://..."}` — foto yoksa/gizliyse **404**.
Laravel fotoğrafı indirip 7 gün saklar; uç yoksa panel baş harfleri gösterir (hata vermez).

```go
// Router'a (diger /session/{id}/... uclarinin yanina):
// GET /session/{id}/avatar?jid=905321112233@s.whatsapp.net  (ya da <lid>@lid)
func avatarHandler(w http.ResponseWriter, r *http.Request, sess *Session) {
    jid, err := types.ParseJID(r.URL.Query().Get("jid"))
    if err != nil { http.Error(w, `{"error":"jid"}`, 400); return }
    info, err := sess.Client.GetProfilePictureInfo(context.Background(), jid, &whatsmeow.GetProfilePictureParams{Preview: true})
    if err != nil || info == nil || info.URL == "" { http.Error(w, `{"error":"yok"}`, 404); return }
    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(map[string]string{"url": info.URL})
}
```

Eski whatsmeow sürümünde `GetProfilePictureInfo` bağlam almıyorsa `sess.Client.GetProfilePictureInfo(jid, &whatsmeow.GetProfilePictureParams{Preview: true})`.
Sonra sunucuda: `go build -o whatsmeow-bridge . && pm2 restart <köprü>`.

---

# Gizli kimlik (LID) ile yazan müşterinin gerçek numarası (`fromPn`)

WhatsApp bazı müşterileri numara yerine gizli kimlikle (`...@lid`) gönderiyor. Bu durumda Laravel numarayı bilmediği için
kayıtlı müşteriyi tanıyamıyor ve telefon numarasını soruyor. whatsmeow çoğu zaman gerçek numarayı bilir; köprü bunu
`fromPn` alanında gönderirse asistan müşteriyi tanır ve telefon sormaz (Laravel tarafı hazır).

```go
// message.received payload'ini kurdugun yerde (fromJid'in yanina):
if evt.Info.Sender.Server == types.HiddenUserServer { // "@lid"
    pn := evt.Info.SenderAlt // yeni whatsmeow surumlerinde dogrudan gelir
    if pn.IsEmpty() {
        if p, err := sess.Client.Store.LIDs.GetPNForLID(context.Background(), evt.Info.Sender); err == nil {
            pn = p
        }
    }
    if !pn.IsEmpty() {
        payload["fromPn"] = pn.User // ornek: "905321112233"
    }
}
```

Eski sürümde `SenderAlt` alanı yoksa yalnız `GetPNForLID` satırını kullanın (bağlam almıyorsa `GetPNForLID(evt.Info.Sender)`).
Sonra: `go build -o whatsmeow-bridge . && pm2 restart <köprü>`.
