@extends('layout.layout_isletmeadmin')

@section('content')
<style>
    .yorum-liste { max-width: 900px; margin: 20px auto; padding: 0 12px; }
    .yorum-header {
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .yorum-header h1 { font-size: 22px; font-weight: 800; margin: 0; color: #1f2937; }
    .yorum-header .rozet {
        background: #FEE2E2; color: #DC2626; padding: 4px 12px; border-radius: 20px;
        font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;
    }
    .yorum-header .toplam { font-size: 13px; color: #6B7280; }
    .filtre-row {
        background: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 12px;
        display: flex; gap: 8px; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .filtre-row label { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; }
    .yorum-kart {
        background: white; border-radius: 14px; padding: 16px; margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: border 0.2s;
    }
    .yorum-kart.bildirilen { border: 2px solid #DC2626; }
    .bildirim-info {
        background: #FEE2E2; color: #DC2626; padding: 8px 12px; border-radius: 8px;
        font-size: 12px; font-weight: 700; margin-bottom: 10px;
    }
    .yorum-baslik { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .yorum-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: linear-gradient(135deg, #6C5CE7, #A29BFE);
        color: white; display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 15px;
    }
    .yorum-ad { font-weight: 700; font-size: 14px; color: #1f2937; }
    .yorum-tarih { font-size: 11px; color: #9CA3AF; }
    .yorum-puan { margin-left: auto; color: #FFB400; font-size: 14px; }
    .yorum-metin { font-size: 14px; color: #374151; line-height: 1.5; margin: 10px 0; }
    .yorum-actions { text-align: right; }
    .btn-sil {
        background: white; color: #DC2626; border: 1px solid #FCA5A5;
        padding: 6px 14px; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px;
    }
    .btn-sil:hover { background: #FEE2E2; }
    .bos-durum {
        text-align: center; padding: 60px 20px; color: #6B7280; font-size: 15px;
    }
</style>

<div class="yorum-liste">
    <div class="yorum-header">
        <h1>📝 Müşteri Yorumları</h1>
        <span id="rozet-bildirilen" class="rozet" style="display:none;">
            🚩 <span id="bildirilen-sayi">0</span> bildirilen
        </span>
        <span class="toplam" id="toplam-yazi">0 yorum</span>
    </div>

    <div class="filtre-row">
        <label>
            <input type="checkbox" id="sadece-bildirilen" onchange="filtreleUygula()">
            Sadece bildirilen yorumları göster
        </label>
        <button onclick="yukle()" style="margin-left:auto; background:#6C5CE7; color:white; border:none; padding:6px 14px; border-radius:8px; cursor:pointer;">
            🔄 Yenile
        </button>
    </div>

    <div id="yorum-liste-container">
        <div class="bos-durum">Yükleniyor...</div>
    </div>
</div>

<script>
    const SALON_ID = {{ (int) ($isletme->id ?? 0) }};
    let YORUMLAR = [];

    async function yukle() {
        document.getElementById('yorum-liste-container').innerHTML =
            '<div class="bos-durum">Yükleniyor...</div>';
        try {
            const res = await fetch(`/api/v1/musteri-yorumlari-admin/${SALON_ID}`, {
                headers: {'Accept': 'application/json'},
            });
            const data = await res.json();
            if (data.success) {
                YORUMLAR = data.yorumlar || [];
                document.getElementById('toplam-yazi').textContent = `${data.toplam} yorum`;
                const bildirilenSayi = data.bildirilen_sayi || 0;
                const rozet = document.getElementById('rozet-bildirilen');
                if (bildirilenSayi > 0) {
                    document.getElementById('bildirilen-sayi').textContent = bildirilenSayi;
                    rozet.style.display = 'inline-flex';
                } else {
                    rozet.style.display = 'none';
                }
                filtreleUygula();
            } else {
                document.getElementById('yorum-liste-container').innerHTML =
                    '<div class="bos-durum">Yorumlar yüklenemedi</div>';
            }
        } catch (e) {
            document.getElementById('yorum-liste-container').innerHTML =
                '<div class="bos-durum">Bağlantı hatası</div>';
        }
    }

    function filtreleUygula() {
        const sadeceBildirilen = document.getElementById('sadece-bildirilen').checked;
        const goster = sadeceBildirilen
            ? YORUMLAR.filter(y => (y.bildirilen_sayisi || 0) > 0)
            : YORUMLAR;
        renderYorumlar(goster);
    }

    function renderYorumlar(list) {
        const kap = document.getElementById('yorum-liste-container');
        if (!list.length) {
            kap.innerHTML = '<div class="bos-durum">Görüntülenecek yorum yok</div>';
            return;
        }
        kap.innerHTML = list.map(y => yorumKartHtml(y)).join('');
    }

    function yorumKartHtml(y) {
        const bildirilenSayi = y.bildirilen_sayisi || 0;
        const bildirim = bildirilenSayi > 0 ? `
            <div class="bildirim-info">
                🚩 ${bildirilenSayi} kez bildirildi${y.bildirim_sebep ? ' — Son sebep: ' + escapeHtml(y.bildirim_sebep) : ''}
            </div>` : '';
        const yildizlar = y.puan > 0
            ? '★'.repeat(y.puan) + '☆'.repeat(5 - y.puan)
            : '';
        const ad = escapeHtml(y.kullanici_adi || 'Müşteri');
        const harf = ad.charAt(0).toUpperCase();
        return `
            <div class="yorum-kart ${bildirilenSayi > 0 ? 'bildirilen' : ''}">
                ${bildirim}
                <div class="yorum-baslik">
                    <div class="yorum-avatar">${harf}</div>
                    <div style="flex:1;">
                        <div class="yorum-ad">${ad}</div>
                        <div class="yorum-tarih">${escapeHtml(y.tarih || '')}</div>
                    </div>
                    ${yildizlar ? `<div class="yorum-puan">${yildizlar}</div>` : ''}
                </div>
                <div class="yorum-metin">${escapeHtml(y.yorum || '')}</div>
                <div class="yorum-actions">
                    <button class="btn-sil" onclick="yorumSil(${y.id})">🗑 Sil</button>
                </div>
            </div>
        `;
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    async function yorumSil(id) {
        if (!confirm('Bu yorumu kalıcı olarak silmek istediğinize emin misiniz?')) return;
        try {
            const res = await fetch(`/api/v1/musteri-yorumu-sil/${id}`, { method: 'DELETE' });
            const data = await res.json();
            if (data.success) {
                yukle();
            } else {
                alert('Silme başarısız: ' + (data.message || ''));
            }
        } catch (e) {
            alert('Bağlantı hatası');
        }
    }

    yukle();
</script>
@endsection
