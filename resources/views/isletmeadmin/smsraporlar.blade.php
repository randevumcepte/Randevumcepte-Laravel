@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                 
                <div class="panel-body"  style="overflow-x: auto">
                  <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Rapor No</th>
                        <th>Tarih</th>
                        <th>Mesaj</th>
                        <th>Numara</th>                        
                        <th>İletim Durumu</th>
                        
                        
                      </tr>
                    </thead>
                    <tbody>
                    	 {!!$rapor!!}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
@endsection