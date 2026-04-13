<div id="hasta_grubu_detay_modal" class="modal fade calendar-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog mx-auto" role="document" style="max-width: 60%;max-height: 95%;top: 50%; transform: translateY(-50%);">
    <!-- max-height: 90vh → 95vh yapıldı -->
    <div class="modal-content" style="border-radius: 10px; max-height: 95vh; overflow: hidden;">
      <form id="arama_liste_detay_formu" method="POST">
        <div class="modal-header" style="display:block;">
          <div class="row">
            <div class="col-md-6">
              <h4 class="modal-title text-primary mb-0" id="grupDetayiBaslik"></h4>
            </div>
            <div class="col-md-6">
               <button type="button" class="close" data-dismiss="modal" aria-label="Kapat" style="float:right;margin-left: 32px;">
                  <span aria-hidden="true">&times;</span>
                </button>
               <input type="text" class="form-control" placeholder="Hasta ara..." id="grupIcındeHastaAra" style="float:right; max-width:200px">
               
            </div>
          </div>
          
         
        
        </div>

        <div class="modal-body px-4 pt-2 pb-4">
          {!! csrf_field() !!}
          <input type="hidden" name="sube" value="{{ $isletme->id }}">
        

          <!-- Scrollable table container -->
          <div class="table-frame" id="aranacak_musteriler" style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px;">
            <table class="table table-bordered table-hover mb-0" id="hastaGrubuHastalari" style="font-size: 16px;">
              <thead class="thead-light" style="position: sticky; top: 0; background-color: #f8f9fa;">
                <tr>
                  <th style="width: 20%;">Hasta</th>
                  <th style="width: 20%;">İlaçlar</th>
                  <th style="width: 20%;">Bitiş Tarihi</th>
                  <th style="width: 20%;">Hatırlatma Tarihi</th>
                  <th style="width: 20%;">Durum</th>
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
