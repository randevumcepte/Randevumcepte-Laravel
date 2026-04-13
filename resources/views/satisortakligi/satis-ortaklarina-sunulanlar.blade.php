    @extends('layout.layout_satisortakligi')
@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;   background-size: cover; background-position: center top;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">Satış Ortaklarımıza Sunduklarımız</h1>
          
           
          </div>
        </div>
      </div> 
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--6">
      <div class="row">
        
        <div class="col-xl-12 order-xl-1">
          
          <div class="card">
             
            <div class="card-body">
               <div style="width: 100%; max-width: 800px; padding: 10px;">
        <canvas id="pdf-canvas"></canvas>

        <div class="navigation-buttons">
            <button id="prev-btn" class="btn btn-secondary btn-sm" style="background-color: #e2e2e2;">Önceki Sayfa</button>
            <span id="page-num">1</span>
            <button id="next-btn"  class="btn btn-secondary btn-sm" style="background-color: #e2e2e2;">Sonraki Sayfa</button>
        </div>
         <div class="pdfloader" id="pdfloader">İçerik Yükleniyor...</div>
    </div>
    <script>
        const url = 'https://app.randevumcepte.com.tr/public/egitimdosyasi/satis-ortaklarimiza-sunduklarimiz.pdf'; // Buraya PDF dosyanızın URL'sini yazın
        const canvas = document.getElementById('pdf-canvas');
        const context = canvas.getContext('2d');
        let pdfDoc = null;
        let currentPage = 1;

        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.8.335/pdf.worker.min.js';

        // PDF dosyasını yükle
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            pdfDoc = pdf;
            renderPage(currentPage);
        }).catch(function(error) {
            // PDF yükleme hatası durumunda
            console.error("PDF yüklenemedi: ", error);
        });

        // Sayfayı render et
        function renderPage(pageNum) {
            pdfDoc.getPage(pageNum).then(function(page) {
                const scale = getResponsiveScale(); // Mobil uyumluluk için ölçekleme
                const viewport = page.getViewport({ scale: scale });

                // Canvas boyutunu, viewport boyutlarına göre güncelle
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render işlemi
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                page.render(renderContext).promise.then(function() {
                    document.getElementById('page-num').textContent = pageNum;
                    document.getElementById('pdfloader').style.display = 'none'; // Yükleme bittiğinde preloader'ı gizle
                });
            });
        }

        // Sayfa ölçeğini responsive hale getir
        function getResponsiveScale() {
            const containerWidth = window.innerWidth; // Pencere genişliğini kullan
            const dpi = window.devicePixelRatio || 1; // Cihazın DPI'sını al
            const scale = (containerWidth * dpi) / 800; // PDF'nin orijinal genişliği (veya istediğiniz genişlik)
            return scale;
        }

        // Önceki sayfaya git
        document.getElementById('prev-btn').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderPage(currentPage);
            }
        });

        // Sonraki sayfaya git
        document.getElementById('next-btn').addEventListener('click', function() {
            if (currentPage < pdfDoc.numPages) {
                currentPage++;
                renderPage(currentPage);
            }
        });
    </script>
             
             
            </div>
          </div>
        </div>
      </div>
      <div id="hata"></div>
       

 
@endsection