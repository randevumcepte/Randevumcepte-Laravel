@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
<style>
    :root {
        --primary-color: #6c5ce7;
        --secondary-color: #a29bfe;
        --accent-color: #fd79a8;
        --dark-color: #2d3436;
        --light-color: #f5f6fa;
        --success-color: #00b894;
        --warning-color: #fdcb6e;
        --danger-color: #d63031;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .cark-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .cark-container header {
        text-align: center;
        margin-bottom: 25px;
    }

    .cark-container header h2 {
        color: var(--primary-color);
        font-size: 24px;
        margin-bottom: 5px;
    }

    .cark-container .description {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        margin-top: 10px;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-active {
        background: rgba(0, 184, 148, 0.1);
        color: var(--success-color);
    }

    .status-active .status-dot {
        background: var(--success-color);
    }

    .status-inactive {
        background: rgba(214, 48, 49, 0.1);
        color: var(--danger-color);
    }

    .status-inactive .status-dot {
        background: var(--danger-color);
    }

    .main-content {
        display: flex;
        gap: 30px;
        align-items: flex-start;
    }

    @media (max-width: 768px) {
        .main-content {
            flex-direction: column;
        }
    }

    .wheel-container {
        flex: 0 0 320px;
        text-align: center;
    }

    .wheel-wrapper {
        position: relative;
        width: 250px;
        height: 250px;
        margin: 0 auto 15px;
    }

    .wheel-wrapper svg {
        width: 100%;
        height: 100%;
    }

    .wheel-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: white;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .wheel-center-logo {
        max-width: 40px;
        max-height: 40px;
        object-fit: contain;
    }

    .logo-upload-container {
        margin: 10px 0;
        text-align: center;
    }

    .logo-upload-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 5px;
        color: var(--dark-color);
    }

    .logo-upload-btn {
        display: inline-block;
        padding: 6px 14px;
        background: var(--primary-color);
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        transition: background 0.2s;
    }

    .logo-upload-btn:hover {
        background: #5a4bd1;
    }

    .logo-upload-info {
        font-size: 11px;
        color: #999;
        margin-top: 5px;
    }

    .controls {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 15px;
    }

    .controls button {
        padding: 8px 18px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        color: white;
    }

    .controls .btn-success {
        background: var(--success-color);
    }

    .controls .btn-success:hover {
        background: #00a381;
    }

    .controls .btn-danger {
        background: var(--danger-color);
    }

    .controls .btn-danger:hover {
        background: #c0392b;
    }

    .slice-management {
        flex: 1;
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: var(--shadow);
    }

    .slice-management h2 {
        font-size: 18px;
        color: var(--dark-color);
        margin-bottom: 15px;
    }

    .slice-management h3 {
        font-size: 15px;
        color: var(--dark-color);
        margin: 15px 0 8px;
    }

    .slice-count-control {
        margin-bottom: 10px;
    }

    .slice-count-control .form-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .slice-count-control label {
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
    }

    .slice-count-control input[type="number"] {
        width: 70px;
        padding: 6px 8px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        text-align: center;
    }

    .slices-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .slice-item {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-bottom: 6px;
        background: var(--light-color);
    }

    .slice-info {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
    }

    .slice-color {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .slice-details {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .slice-name-input {
        flex: 1;
        padding: 5px 8px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        min-width: 0;
    }

    .slice-name-input:focus,
    .probability-input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .slice-probability {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        white-space: nowrap;
    }

    .probability-input {
        width: 55px;
        padding: 5px 6px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        text-align: center;
    }

    .total-probability {
        margin-top: 12px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        background: rgba(108, 92, 231, 0.1);
        color: var(--dark-color);
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        max-width: 400px;
        width: 90%;
        margin: auto;
        position: relative;
        top: 50%;
        transform: translateY(-50%);
    }

    .modal-content h2 {
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .modal-content button {
        margin-top: 15px;
        padding: 8px 24px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        color: white;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: var(--success-color);
    }

    .notification.error {
        background: var(--danger-color);
    }

    .slice-text-line {
        fill: white;
        font-weight: bold;
        pointer-events: none;
    }
</style>

<div class="cark-container">
    <header>
        <h2>Carkifelek Sistemi</h2>
        <p class="description">Dilim sayisini belirleyin, olasiliklerini ve isimlerini ayarlayin.</p>
        <div class="status-indicator" id="status-indicator">
            <div class="status-dot"></div>
            <span id="status-text">Aktif</span>
        </div>
    </header>

    <div class="main-content">
        <div class="wheel-container">
            <div class="wheel-wrapper">
                <svg id="wheel" viewBox="0 0 250 250"></svg>
                <div class="wheel-center">
                    <img id="wheel-logo" src="{{($isletme->logo !== null ? '/'.$isletme->logo : '/public/isletmeyonetim_assets/img/avatar.png' )}}" alt="Logo" class="wheel-center-logo">
                </div>
            </div>

            <div class="logo-upload-container">
                <label class="logo-upload-label">Logo Yukle</label>
                <input type="file" id="isletmelogo" name="isletmelogo" accept="image/*" style="display:none;" />
                <div class="logo-upload-btn" onclick="document.getElementById('isletmelogo').click();">
                    <i class="fa fa-pencil"></i> Logo Sec
                </div>
                <div class="logo-upload-info">Maksimum 240px genisliginde veya 100px yuksekligine sahip olmalidir</div>
            </div>

            <div class="controls">
                <button id="toggle-status-btn">Carki Pasif Et</button>
                <button id="save-slices-btn" class="btn-success">Dilimleri Kaydet</button>
            </div>
        </div>

        <div class="slice-management">
            <h2>Dilim Yonetimi</h2>

            <div class="slice-count-control">
                <div class="form-group">
                    <label for="slice-count">Dilim Sayisi (En az 6)</label>
                    <input type="number" id="slice-count" min="6" value="6">
                </div>
            </div>

            <h3>Mevcut Dilimler</h3>
            <p style="margin-bottom: 10px; font-size: 14px; color: #666;">Dilim isimlerini ve olasiliklarini asagidan duzenleyebilirsiniz. Dusuk olasilik = daha az gelme sansi</p>

            <div class="slices-list" id="slices-list">
                <!-- Dilimler buraya eklenecek -->
            </div>

            <div class="total-probability" id="total-probability">
                Toplam Olasilik: 100%
            </div>
        </div>
    </div>
</div>

<div class="modal" id="result-modal">
    <div class="modal-content">
        <h2>Tebrikler!</h2>
        <p id="result-text">100 TL kazandiniz!</p>
        <button id="close-modal">Kapat</button>
    </div>
</div>

<div class="notification" id="notification"></div>

<script>
    // Renk paleti - otomatik atama icin
    const colorPalette = [
        '#6c5ce7', '#a29bfe', '#fd79a8', '#fdcb6e', '#00b894',
        '#e17055', '#74b9ff', '#55efc4', '#ffeaa7', '#fab1a0',
        '#a29bfe', '#81ecec', '#dfe6e9', '#b2bec3', '#636e72',
        '#0984e3', '#00b894', '#fdcb6e', '#e17055', '#d63031'
    ];

    // Dilimleri saklamak icin dizi
    let slices = [
        { name: "100 TL", color: colorPalette[0], probability: 10 },
        { name: "50 TL", color: colorPalette[1], probability: 15 },
        { name: "20 TL", color: colorPalette[2], probability: 20 },
        { name: "10 TL", color: colorPalette[3], probability: 25 },
        { name: "5 TL", color: colorPalette[4], probability: 20 },
        { name: "Tekrar Dene", color: colorPalette[5], probability: 10 }
    ];

    let isWheelActive = true;

    const wheel = document.getElementById('wheel');
    const sliceCountInput = document.getElementById('slice-count');
    const slicesList = document.getElementById('slices-list');
    const totalProbabilityElement = document.getElementById('total-probability');
    const resultModal = document.getElementById('result-modal');
    const resultText = document.getElementById('result-text');
    const closeModal = document.getElementById('close-modal');
    const toggleStatusBtn = document.getElementById('toggle-status-btn');
    const saveSlicesBtn = document.getElementById('save-slices-btn');
    const statusIndicator = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');
    const notification = document.getElementById('notification');
    const logoInput = document.getElementById('isletmelogo');
    const wheelLogo = document.getElementById('wheel-logo');

    const headers = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
    };

    document.addEventListener('DOMContentLoaded', function() {
        loadWheelData();
        sliceCountInput.addEventListener('change', updateSlicesCount);

        logoInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    alert('Dosya boyutu 5MB\'dan kucuk olmalidir.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    wheelLogo.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        saveSlicesBtn.addEventListener('click', saveSlicesToServer);
    });

    async function loadWheelData() {
        try {
            const response = await fetch('{{ route("isletmeadmin.carkverilerigetir") }}', {
                method: 'GET',
                headers: headers
            });

            const data = await response.json();

            if (data.success && data.data) {
                isWheelActive = data.data.aktifmi == 1;

                if (data.data.dilimler && data.data.dilimler.length > 0) {
                    slices = data.data.dilimler.map(dilim => ({
                        name: dilim.name,
                        color: dilim.color || colorPalette[Math.floor(Math.random() * colorPalette.length)],
                        probability: parseInt(dilim.probability) || 1
                    }));

                    sliceCountInput.value = slices.length;
                }
            }
        } catch (error) {
            console.error('Cark verileri yuklenirken hata:', error);
        }

        renderWheel();
        renderSlicesList();
        updateTotalProbability();
        updateStatusUI();
    }

    function renderWheel() {
        wheel.innerHTML = '';
        const totalProbability = slices.reduce((sum, slice) => sum + slice.probability, 0);
        const sliceAngle = 360 / slices.length;

        slices.forEach((slice, index) => {
            const startAngle = index * sliceAngle;
            const endAngle = (index + 1) * sliceAngle;

            const startRad = (startAngle - 90) * Math.PI / 180;
            const endRad = (endAngle - 90) * Math.PI / 180;

            const x1 = 125 + 100 * Math.cos(startRad);
            const y1 = 125 + 100 * Math.sin(startRad);
            const x2 = 125 + 100 * Math.cos(endRad);
            const y2 = 125 + 100 * Math.sin(endRad);

            const largeArcFlag = sliceAngle > 180 ? 1 : 0;

            const pathData = [
                `M 125 125`,
                `L ${x1} ${y1}`,
                `A 100 100 0 ${largeArcFlag} 1 ${x2} ${y2}`,
                `Z`
            ].join(' ');

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathData);
            path.setAttribute('fill', slice.color);
            path.setAttribute('stroke', 'white');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('data-name', slice.name);
            wheel.appendChild(path);

            const textAngle = startAngle + sliceAngle / 2;
            const textRad = (textAngle - 90) * Math.PI / 180;
            let textDistance = 65;

            const textX = 125 + textDistance * Math.cos(textRad);
            const textY = 125 + textDistance * Math.sin(textRad);

            const textGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            textGroup.setAttribute('transform', `rotate(${textAngle}, ${textX}, ${textY})`);
            textGroup.setAttribute('class', 'slice-text');

            const maxCharsPerLine = 10;
            let lines = [];
            const words = slice.name.split(' ');
            let currentLine = '';

            for (let i = 0; i < words.length; i++) {
                const word = words[i];
                if (word.length > maxCharsPerLine) {
                    if (currentLine) { lines.push(currentLine); currentLine = ''; }
                    for (let j = 0; j < word.length; j += maxCharsPerLine) {
                        lines.push(word.substring(j, j + maxCharsPerLine));
                    }
                } else if ((currentLine + ' ' + word).length <= maxCharsPerLine) {
                    currentLine = currentLine ? currentLine + ' ' + word : word;
                } else {
                    if (currentLine) { lines.push(currentLine); }
                    currentLine = word;
                }
            }
            if (currentLine) { lines.push(currentLine); }
            if (lines.length > 3) { lines = [lines[0], lines[1], lines[2] + '...']; }

            const lineHeight = 10;
            const startY = textY - ((lines.length - 1) * lineHeight / 2);

            lines.forEach((line, lineIndex) => {
                const textElement = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                textElement.setAttribute('x', textX);
                textElement.setAttribute('y', startY + (lineIndex * lineHeight));
                textElement.setAttribute('text-anchor', 'middle');
                textElement.setAttribute('class', 'slice-text-line');

                let fontSize = 8;
                if (slices.length > 12) fontSize = 6;
                else if (slices.length > 8) fontSize = 7;

                textElement.setAttribute('font-size', fontSize);
                textElement.textContent = line;
                textGroup.appendChild(textElement);
            });

            const probabilityText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            probabilityText.setAttribute('x', textX);
            probabilityText.setAttribute('y', startY + (lines.length * lineHeight) + 2);
            probabilityText.setAttribute('text-anchor', 'middle');
            probabilityText.setAttribute('class', 'slice-text-line');
            probabilityText.setAttribute('font-size', '6');
            probabilityText.setAttribute('fill', 'rgba(255,255,255,0.8)');
            probabilityText.textContent = `${slice.probability}%`;

            textGroup.appendChild(probabilityText);
            wheel.appendChild(textGroup);
        });
    }

    function renderSlicesList() {
        slicesList.innerHTML = '';

        slices.forEach((slice, index) => {
            const sliceItem = document.createElement('div');
            sliceItem.className = 'slice-item';

            sliceItem.innerHTML = `
                <div class="slice-info">
                    <div class="slice-color" style="background-color: ${slice.color}"></div>
                    <div class="slice-details">
                        <input type="text" class="slice-name-input" value="${slice.name}" data-index="${index}" placeholder="Dilim adi">
                        <div class="slice-probability">
                            Olasilik:
                            <input type="number" class="probability-input" value="${slice.probability}" min="1" max="100" data-index="${index}">
                            %
                        </div>
                    </div>
                </div>
            `;

            slicesList.appendChild(sliceItem);
        });

        document.querySelectorAll('.slice-name-input').forEach(input => {
            input.addEventListener('change', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const newName = this.value.trim();
                if (!newName) { this.value = slices[index].name; alert('Lutfen bir dilim adi girin.'); return; }
                slices[index].name = newName;
                renderWheel();
            });
            input.addEventListener('keypress', function(e) { if (e.key === 'Enter') this.blur(); });
        });

        document.querySelectorAll('.probability-input').forEach(input => {
            input.addEventListener('change', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const newProbability = parseInt(this.value);
                if (isNaN(newProbability) || newProbability < 1) { this.value = slices[index].probability; alert('Olasilik en az 1 olmalidir.'); return; }
                slices[index].probability = newProbability;
                updateTotalProbability();
                renderWheel();
            });
        });
    }

    function updateSlicesCount() {
        const count = parseInt(sliceCountInput.value);
        if (isNaN(count) || count < 6) { alert('Lutfen en az 6 dilim belirleyin.'); sliceCountInput.value = slices.length; return; }

        if (count !== slices.length) {
            const equalProbability = Math.floor(100 / count);
            const remainder = 100 - (equalProbability * count);
            const newSlices = [];

            for (let i = 0; i < count; i++) {
                const colorIndex = i % colorPalette.length;
                const probability = equalProbability + (i < remainder ? 1 : 0);

                if (i < slices.length) {
                    newSlices.push({ name: slices[i].name, color: slices[i].color, probability: probability });
                } else {
                    newSlices.push({ name: `Odul ${i+1}`, color: colorPalette[colorIndex], probability: probability });
                }
            }

            slices = newSlices;
            renderWheel();
            renderSlicesList();
            updateTotalProbability();
            showNotification(`Cark ${count} dilime guncellendi`, 'success');
        }
    }

    async function saveSlicesToServer() {
        document.querySelectorAll('.slice-name-input').forEach(input => {
            const index = parseInt(input.getAttribute('data-index'));
            slices[index].name = input.value.trim() || `Odul ${index+1}`;
        });

        document.querySelectorAll('.probability-input').forEach(input => {
            const index = parseInt(input.getAttribute('data-index'));
            const probability = parseInt(input.value);
            slices[index].probability = isNaN(probability) ? 1 : probability;
        });

        const totalProbability = slices.reduce((sum, slice) => sum + slice.probability, 0);
        if (totalProbability !== 100) {
            showNotification(`Toplam olasilik %100 olmalidir. Su an: %${totalProbability}`, 'error');
            return;
        }

        renderWheel();

        try {
            const response = await fetch('{{ route("isletmeadmin.carkdilimekle") }}', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ dilimler: slices, aktifmi: isWheelActive ? 1 : 0 })
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Dilimler basariyla kaydedildi!', 'success');
                if (data.data && data.data.dilimler) {
                    slices = data.data.dilimler.map(dilim => ({
                        name: dilim.dilim_ismi,
                        color: dilim.renk_kodu,
                        probability: dilim.dilim_olasilik
                    }));
                    renderWheel();
                    renderSlicesList();
                }
            } else {
                showNotification(data.message || 'Dilimler kaydedilirken hata olustu!', 'error');
            }
        } catch (error) {
            console.error('Kaydetme hatasi:', error);
            showNotification('Dilimler kaydedilirken hata olustu!', 'error');
        }
    }

    function updateTotalProbability() {
        const totalProbability = slices.reduce((sum, slice) => sum + slice.probability, 0);
        totalProbabilityElement.textContent = `Toplam Olasilik: ${totalProbability}%`;

        if (totalProbability !== 100) {
            totalProbabilityElement.style.backgroundColor = '#ffebee';
            totalProbabilityElement.style.color = '#d32f2f';
        } else {
            totalProbabilityElement.style.backgroundColor = 'rgba(108, 92, 231, 0.1)';
            totalProbabilityElement.style.color = 'var(--dark-color)';
        }
    }

    function updateStatusUI() {
        if (isWheelActive) {
            statusIndicator.className = 'status-indicator status-active';
            statusText.textContent = 'Aktif';
            toggleStatusBtn.textContent = 'Carki Pasif Et';
            toggleStatusBtn.className = 'btn-danger';
        } else {
            statusIndicator.className = 'status-indicator status-inactive';
            statusText.textContent = 'Pasif';
            toggleStatusBtn.textContent = 'Carki Aktif Et';
            toggleStatusBtn.className = 'btn-success';
        }
    }

    function showNotification(message, type) {
        notification.textContent = message;
        notification.className = `notification ${type} show`;
        setTimeout(() => { notification.classList.remove('show'); }, 3000);
    }

    toggleStatusBtn.addEventListener('click', function() {
        isWheelActive = !isWheelActive;
        updateStatusUI();
        saveStatusToServer();
    });

    async function saveStatusToServer() {
        try {
            const response = await fetch('{{ route("isletmeadmin.carkdilimekle") }}', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ aktifmi: isWheelActive ? 1 : 0, dilimler: slices })
            });

            const data = await response.json();

            if (data.success) {
                showNotification(`Cark ${isWheelActive ? 'aktif' : 'pasif'} edildi!`, 'success');
            } else {
                showNotification('Durum guncellenirken hata olustu!', 'error');
                isWheelActive = !isWheelActive;
                updateStatusUI();
            }
        } catch (error) {
            console.error('Durum guncelleme hatasi:', error);
            showNotification('Durum guncellenirken hata olustu!', 'error');
            isWheelActive = !isWheelActive;
            updateStatusUI();
        }
    }

    closeModal.addEventListener('click', function() { resultModal.style.display = 'none'; });
    window.addEventListener('click', function(event) { if (event.target === resultModal) resultModal.style.display = 'none'; });
</script>
@endsection