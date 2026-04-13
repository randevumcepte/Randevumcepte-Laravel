@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
   @if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="main-content container-fluid">
        
      <div class="row">
        <div class="col-sm-12">
          <div class="panel panel-default panel-table">
            <div class="panel-heading panel-heading-divider xs-pb-15 avantaj" style="font-weight: bold;font-size:20px">Yapılan Ödemeler</div>
            <div class="panel-body" style="padding-bottom: 10px;overflow-x:auto">
               <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Sıra</th>
                        <th>Tarih</th>
                        <th style="width: 300px">Avantaj</th>     
                        <th style="width: 110px">Adet</th>
                        <th>Ödenen Tutar</th> 
                        
                      </tr>
                    </thead>
                    <tbody>
                    	 

                    	@foreach($avantajodemeler as $key=>$value)
                    	<tr>
                    		<td>{{$value->id}}</td>
                    		<td>{{$value->created_at}}</td>
                    		<td>{{$value->kampanya_aciklama}}</td>
                    		<td>{{$value->adet}}</td>
                    		<td>{{$value->tutar}}</td>
                    	</tr>
                    	@endforeach
                        
                    </tbody>
                  </table>
            </div>
          </div>
        </div>
      </div>
  </div>
@endsection
@endsection