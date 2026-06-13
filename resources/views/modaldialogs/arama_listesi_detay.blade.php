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
                  <th style="width: 6%;">#</th>
                  <th style="width: 19%;">Müşteri</th>
                  <th style="width: 19%;">Telefon Numarası</th>
                  <th style="width: 18%;">Durum</th>
                  <th style="width: 20%;">Not</th>
                  <th style="width: 18%;"></th>
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

{{-- Cagri Merkezi: bir musteriyle yapilan TUM gorusme ses kayitlari --}}
<div id="ses_kayitlari_modal" class="modal fade" role="dialog" style="z-index:1080;">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:12px;">
      <div class="modal-header" style="background:linear-gradient(120deg,#5C008E,#9D5DC8);color:#fff;border-radius:12px 12px 0 0;">
        <h5 class="modal-title" id="ses_kayitlari_baslik">Görüşme Kayıtları</h5>
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="ses_kayitlari_govde" style="max-height:60vh;overflow-y:auto;">
        <p class="text-muted">Yükleniyor...</p>
      </div>
    </div>
  </div>
</div>
