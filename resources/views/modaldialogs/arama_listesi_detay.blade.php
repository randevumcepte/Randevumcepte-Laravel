<div id="arama_detay_modal" class="modal fade calendar-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog mx-auto" role="document" style="max-width: 90%;max-height: 95%;top: 50%; transform: translateY(-50%);">
    <!-- max-height: 90vh → 95vh yapıldı -->
    <div class="modal-content" style="border-radius: 10px; max-height: 95vh; overflow: hidden;">
      <form id="arama_liste_detay_formu" method="POST">
        <div class="modal-header px-4 py-3">
          <h4 class="modal-title text-primary mb-0">Arama Listesi Detayı</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body px-4 pt-2 pb-4">
          {!! csrf_field() !!}
          <input type="hidden" name="sube" value="{{ $isletme->id }}">
          <input type="hidden" name="arama_detay_id" id="arama_detay_id" value="">

          <!-- Scrollable table container -->
          <div class="table-frame" id="aranacak_musteriler" style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px;">
            <table class="table table-bordered table-hover mb-0" id="arama_liste_detay_tablo" style="font-size: 16px;">
              <thead class="thead-light" style="position: sticky; top: 0; background-color: #f8f9fa;">
                <tr>
                  <th style="width: 20%;">Müşteri</th>
                  <th style="width: 20%;">Telefon Numarası</th>
                  <th style="width: 20%;">Durum</th>
                  <th style="width: 20%;">Not</th>
                  <th style="width: 20%;"></th>
                </tr>
              </thead>
              <tbody>
                <!-- JavaScript ile doldurulacak -->
              </tbody>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
