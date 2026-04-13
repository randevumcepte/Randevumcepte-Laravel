@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="page-head">
	<h2>Yeni Randevu Ekle</h2>
</div>
 <div class="main-content container-fluid">
 	


 </div>
<div id="hata"></div>
@endsection