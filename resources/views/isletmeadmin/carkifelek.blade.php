@if(Auth::guard('satisortakligi')->check()) 
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp 
@else 
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp 
@endif 
@extends($_layout)
@section('content')
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çarkıfelek Sistemi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* CSS kodları aynı kalacak */
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

        /* ... Diğer CSS kodları aynı ... */
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2>Çarkıfelek Sistemi</h2>
            <p class="description">Dilim sayısını belirleyin, olasılıklarını ve isimlerini ayarlayın.</p>
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
                    <label class="logo-upload-label">Logo Yükle</label>
                    <input type="file" id="isletmelogo" name="isletmelogo" accept="image/*" style="display:none;" />
                    <div class="logo-upload-btn" onclick="document.getElementById('isletmelogo').click();">
                        <i class="fa fa-pencil"></i> Logo Seç
                    </div>
                    <div class="logo-upload-info">Maksimum 240px genişliğinde veya 100px yüksekliğine sahip olmalıdır</div>
                </div>
                
                <div class="controls">
                    <button id="toggle-status-btn">Çarkı Pasif Et</button>
                    <button id="save-slices-btn" class="btn-success">Dilimleri Kaydet</button>
                </div>
            </div>

            <div class="slice-management">
                <h2>Dilim Yönetimi</h2>
                
                <div class="slice-count-control">
                    <div class="form-group">
                        <label for="slice-count">Dilim Sayısı (En az 6)</label>
                        <input type="number" id="slice-count" min="6" value="6">
                    </div>
                </div>

                <h3>Mevcut Dilimler</h3>
                <p style="margin-bottom: 10px; font-size: 14px; color: #666;">Dilim isimlerini ve olasılıklarını aşağıdan düzenleyebilirsiniz. Düşük olasılık = daha az gelme şansı</p>
                
                <div class="slices-list" id="slices-list">
                    <!-- Dilimler buraya eklenecek -->
                </div>
                
                <div class="total-probability" id="total-probability">
                    Toplam Olasılık: 100%
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="result-modal">
        <div class="modal-content">
            <h2>Tebrikler!</h2>
            <p id="result-text">100 TL kazandınız!</p>
            <button id="close-modal">Kapat</button>
        </div>
    </div>

    <div class="notification" id="notification"></div>

    <script>
        // Renk paleti - otomatik atama için
        const colorPalette = [
            '#6c5ce7', '#a29bfe', '#fd79a8', '#fdcb6e', '#00b894', 
            '#e17055', '#74b9ff', '#55efc4', '#ffeaa7', '#fab1a0',
            '#a29bfe', '#81ecec', '#dfe6e9', '#b2bec3', '#636e72',
            '#0984e3', '#00b894', '#fdcb6e', '#e17055', '#d63031'
        ];

        // Dilimleri saklamak için dizi - Başlangıçta sunucudan gelen verilerle dolduracağız
        let slices = [
            { name: "100 TL", color: colorPalette[0], probability: 10 },
            { name: "50 TL", color: colorPalette[1], probability: 15 },
            { name: "20 TL", color: colorPalette[2], probability: 20 },
            { name: "10 TL", color: colorPalette[3], probability: 25 },
            { name: "5 TL", color: colorPalette[4], probability: 20 },
            { name: "Tekrar Dene", color: colorPalette[5], probability: 10 }
        ];

        // Çark durumu - Sunucudan gelecek
        let isWheelActive = true;

        // DOM elementleri
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

        // CSRF Token için headers
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        };

        // Sayfa yüklendiğinde çarkı ve dilim listesini oluştur
        document.addEventListener('DOMContentLoaded', function() {
            // Sunucudan çark verilerini getir
            loadWheelData();
            
            // Dilim sayısı değiştiğinde otomatik güncelle
            sliceCountInput.addEventListener('change', updateSlicesCount);
            
            // Logo yükleme işlemi
            logoInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    
                    // Dosya boyutu kontrolü (opsiyonel)
                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        alert('Dosya boyutu 5MB\'dan küçük olmalıdır.');
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        wheelLogo.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(file);
                }
            });
            
            // Dilimleri kaydet butonu
            saveSlicesBtn.addEventListener('click', saveSlicesToServer);
        });

        // Sunucudan çark verilerini yükle
        async function loadWheelData() {
            try {
                const response = await fetch('{{ route("isletmeadmin.carkverilerigetir") }}', {
                    method: 'GET',
                    headers: headers
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Çark aktif/pasif durumunu güncelle
                    isWheelActive = data.data.aktifmi == 1;
                    updateStatusUI();
                    
                    // Dilimleri güncelle
                    if (data.data.dilimler && data.data.dilimler.length > 0) {
                        slices = data.data.dilimler.map(dilim => ({
                            name: dilim.name,
                            color: dilim.color || colorPalette[Math.floor(Math.random() * colorPalette.length)],
                            probability: dilim.probability
                        }));
                        
                        sliceCountInput.value = slices.length;
                        renderWheel();
                        renderSlicesList();
                        updateTotalProbability();
                    }
                }
            } catch (error) {
                console.error('Çark verileri yüklenirken hata:', error);
                // Hata durumunda varsayılan dilimleri göster
                renderWheel();
                renderSlicesList();
                updateTotalProbability();
                updateStatusUI();
            }
        }

        // Çarkı oluştur
        function renderWheel() {
            wheel.innerHTML = '';
            
            // Toplam olasılık
            const totalProbability = slices.reduce((sum, slice) => sum + slice.probability, 0);
            
            // EŞİT BOYUTLU DİLİMLER - her dilim aynı açıda
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
                
                // Path elementini oluştur
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                path.setAttribute('fill', slice.color);
                path.setAttribute('stroke', 'white');
                path.setAttribute('stroke-width', '2');
                path.setAttribute('data-name', slice.name);
                
                wheel.appendChild(path);
                
                // Metin ekleme (dilim ortasına)
                const textAngle = startAngle + sliceAngle / 2;
                const textRad = (textAngle - 90) * Math.PI / 180;
                
                // Metin konumunu ayarla
                let textDistance = 65;
                
                const textX = 125 + textDistance * Math.cos(textRad);
                const textY = 125 + textDistance * Math.sin(textRad);
                
                // Metin grubu oluştur
                const textGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
                textGroup.setAttribute('transform', `rotate(${textAngle}, ${textX}, ${textY})`);
                textGroup.setAttribute('class', 'slice-text');
                
                // Metni satırlara böl - GELİŞMİŞ YÖNTEM
                const maxCharsPerLine = 10;
                let lines = [];
                
                // Uzun metinleri kelimelere böl
                const words = slice.name.split(' ');
                let currentLine = '';
                
                for (let i = 0; i < words.length; i++) {
                    const word = words[i];
                    // Eğer kelime tek başına çok uzunsa, kelimeyi böl
                    if (word.length > maxCharsPerLine) {
                        if (currentLine) {
                            lines.push(currentLine);
                            currentLine = '';
                        }
                        // Uzun kelimeyi parçalara böl
                        for (let j = 0; j < word.length; j += maxCharsPerLine) {
                            lines.push(word.substring(j, j + maxCharsPerLine));
                        }
                    } else if ((currentLine + ' ' + word).length <= maxCharsPerLine) {
                        if (currentLine) {
                            currentLine += ' ' + word;
                        } else {
                            currentLine = word;
                        }
                    } else {
                        if (currentLine) {
                            lines.push(currentLine);
                        }
                        currentLine = word;
                    }
                }
                
                if (currentLine) {
                    lines.push(currentLine);
                }
                
                // Çok fazla satır varsa kısalt
                if (lines.length > 3) {
                    lines = [lines[0], lines[1], lines[2] + '...'];
                }
                
                // Her satır için text elementi oluştur
                const lineHeight = 10;
                const startY = textY - ((lines.length - 1) * lineHeight / 2);
                
                lines.forEach((line, lineIndex) => {
                    const textElement = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    textElement.setAttribute('x', textX);
                    textElement.setAttribute('y', startY + (lineIndex * lineHeight));
                    textElement.setAttribute('text-anchor', 'middle');
                    textElement.setAttribute('class', 'slice-text-line');
                    
                    // Font boyutunu dilim sayısına göre ayarla
                    let fontSize = 8;
                    if (slices.length > 12) {
                        fontSize = 6;
                    } else if (slices.length > 8) {
                        fontSize = 7;
                    }
                    
                    textElement.setAttribute('font-size', fontSize);
                    textElement.textContent = line;
                    
                    textGroup.appendChild(textElement);
                });
                
                // Olasılık yüzdesini de göster
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

        // Dilim listesini oluştur
        function renderSlicesList() {
            slicesList.innerHTML = '';
            
            slices.forEach((slice, index) => {
                const sliceItem = document.createElement('div');
                sliceItem.className = 'slice-item';
                
                sliceItem.innerHTML = `
                    <div class="slice-info">
                        <div class="slice-color" style="background-color: ${slice.color}"></div>
                        <div class="slice-details">
                            <input type="text" class="slice-name-input" value="${slice.name}" data-index="${index}" placeholder="Dilim adı">
                            <div class="slice-probability">
                                Olasılık: 
                                <input type="number" class="probability-input" value="${slice.probability}" min="1" max="100" data-index="${index}">
                                %
                            </div>
                        </div>
                    </div>
                `;
                
                slicesList.appendChild(sliceItem);
            });
            
            // Dilim ismi inputlarına event listener ekle
            document.querySelectorAll('.slice-name-input').forEach(input => {
                input.addEventListener('change', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    const newName = this.value.trim();
                    
                    if (!newName) {
                        this.value = slices[index].name;
                        alert('Lütfen bir dilim adı girin.');
                        return;
                    }
                    
                    slices[index].name = newName;
                    renderWheel();
                });
                
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            });
            
            // Olasılık inputlarına event listener ekle
            document.querySelectorAll('.probability-input').forEach(input => {
                input.addEventListener('change', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    const newProbability = parseInt(this.value);
                    
                    if (isNaN(newProbability) || newProbability < 1) {
                        this.value = slices[index].probability;
                        alert('Olasılık en az 1 olmalıdır.');
                        return;
                    }
                    
                    slices[index].probability = newProbability;
                    updateTotalProbability();
                    renderWheel();
                });
            });
        }

        // Dilim sayısını güncelle
        function updateSlicesCount() {
            const count = parseInt(sliceCountInput.value);
            
            if (isNaN(count) || count < 6) {
                alert('Lütfen en az 6 dilim belirleyin.');
                sliceCountInput.value = slices.length;
                return;
            }
            
            // Mevcut dilim sayısından farklıysa güncelle
            if (count !== slices.length) {
                // Eşit olasılıkla yeni dilimler oluştur
                const equalProbability = Math.floor(100 / count);
                const remainder = 100 - (equalProbability * count);
                
                // Yeni dilimleri oluştur
                const newSlices = [];
                for (let i = 0; i < count; i++) {
                    const colorIndex = i % colorPalette.length;
                    const probability = equalProbability + (i < remainder ? 1 : 0);
                    
                    // Eğer eski dilim varsa onu kullan, yoksa yeni oluştur
                    if (i < slices.length) {
                        newSlices.push({
                            name: slices[i].name,
                            color: slices[i].color,
                            probability: probability
                        });
                    } else {
                        newSlices.push({
                            name: `Ödül ${i+1}`,
                            color: colorPalette[colorIndex],
                            probability: probability
                        });
                    }
                }
                
                slices = newSlices;
                
                // Çarkı ve listeyi güncelle
                renderWheel();
                renderSlicesList();
                updateTotalProbability();
                
                showNotification(`Çark ${count} dilime güncellendi`, 'success');
            }
        }

        // Sunucuya dilimleri kaydet
        async function saveSlicesToServer() {
            // Input değerlerini güncelle
            document.querySelectorAll('.slice-name-input').forEach(input => {
                const index = parseInt(input.getAttribute('data-index'));
                slices[index].name = input.value.trim() || `Ödül ${index+1}`;
            });
            
            document.querySelectorAll('.probability-input').forEach(input => {
                const index = parseInt(input.getAttribute('data-index'));
                const probability = parseInt(input.value);
                slices[index].probability = isNaN(probability) ? 1 : probability;
            });
            
            // Toplam olasılık kontrolü
            const totalProbability = slices.reduce((sum, slice) => sum + slice.probability, 0);
            
            if (totalProbability !== 100) {
                showNotification(`Toplam olasılık %100 olmalıdır. Şu an: %${totalProbability}`, 'error');
                return;
            }
            
            // Çarkı güncelle
            renderWheel();
            
            // Sunucuya kaydet
            try {
                const response = await fetch('{{ route("isletmeadmin.carkdilimekle") }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        dilimler: slices,
                        aktifmi: isWheelActive ? 1 : 0
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Dilimler başarıyla kaydedildi!', 'success');
                    // Kaydedilen verileri güncelle
                    if (data.data && data.data.dilimler) {
                        // Dilimleri güncelle
                        slices = data.data.dilimler.map(dilim => ({
                            name: dilim.dilim_ismi,
                            color: dilim.renk_kodu,
                            probability: dilim.dilim_olasilik
                        }));
                        renderWheel();
                        renderSlicesList();
                    }
                } else {
                    showNotification(data.message || 'Dilimler kaydedilirken hata oluştu!', 'error');
                }
            } catch (error) {
                console.error('Kaydetme hatası:', error);
                showNotification('Dilimler kaydedilirken hata oluştu!', 'error');
            }
        }

        // Toplam olasılığı güncelle
        function updateTotalProbability() {
            const totalProbability = slices.reduce((sum, slice) => sum + slice.probability, 0);
            totalProbabilityElement.textContent = `Toplam Olasılık: ${totalProbability}%`;
            
            if (totalProbability !== 100) {
                totalProbabilityElement.style.backgroundColor = '#ffebee';
                totalProbabilityElement.style.color = '#d32f2f';
            } else {
                totalProbabilityElement.style.backgroundColor = 'rgba(108, 92, 231, 0.1)';
                totalProbabilityElement.style.color = 'var(--dark-color)';
            }
        }

        // Durum UI'ını güncelle
        function updateStatusUI() {
            if (isWheelActive) {
                statusIndicator.className = 'status-indicator status-active';
                statusText.textContent = 'Aktif';
                toggleStatusBtn.textContent = 'Çarkı Pasif Et';
                toggleStatusBtn.className = 'btn-danger';
            } else {
                statusIndicator.className = 'status-indicator status-inactive';
                statusText.textContent = 'Pasif';
                toggleStatusBtn.textContent = 'Çarkı Aktif Et';
                toggleStatusBtn.className = 'btn-success';
            }
        }

        // Bildirim göster
        function showNotification(message, type) {
            notification.textContent = message;
            notification.className = `notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Çark durumunu değiştir
        toggleStatusBtn.addEventListener('click', function() {
            isWheelActive = !isWheelActive;
            updateStatusUI();
            
            // Durum değişikliğini sunucuya kaydet
            saveStatusToServer();
        });

        // Çark durumunu sunucuya kaydet
        async function saveStatusToServer() {
            try {
                const response = await fetch('{{ route("isletmeadmin.carkdilimekle") }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        aktifmi: isWheelActive ? 1 : 0,
                        dilimler: slices
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(`Çark ${isWheelActive ? 'aktif' : 'pasif'} edildi!`, 'success');
                } else {
                    showNotification('Durum güncellenirken hata oluştu!', 'error');
                    // Hata durumunda durumu geri al
                    isWheelActive = !isWheelActive;
                    updateStatusUI();
                }
            } catch (error) {
                console.error('Durum güncelleme hatası:', error);
                showNotification('Durum güncellenirken hata oluştu!', 'error');
                // Hata durumunda durumu geri al
                isWheelActive = !isWheelActive;
                updateStatusUI();
            }
        }

        // Modal'ı kapat
        closeModal.addEventListener('click', function() {
            resultModal.style.display = 'none';
        });

        // Modal dışına tıklayınca kapat
        window.addEventListener('click', function(event) {
            if (event.target === resultModal) {
                resultModal.style.display = 'none';
            }
        });

        // Logo yükleme fonksiyonu
        function thisFileUploadLogo() {
            document.getElementById('isletmelogo').click();
        }
    </script>
</body>
</html>
@endsection