@extends('layout.layout_sistemadmin')
@section('content')

 
      
        <div class="page-head">
          <h2 class="page-head-title" style="float: left;">İşletmeler</h2> <a style="float: left; margin:10px 0 0 10px" href="/isletmeyonetim/yeniyetkili" class="btn btn-primary">Yeni Ekle</a>
          
        </div>
        <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                 
                <div class="panel-body">
                  <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Yetkili Olduğu Salon</th>
                        <th>Üyelik Tarihi</th>
                        <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>
                    	@foreach($yetkililer as $yetkili)
                      <tr>
                        <td>{{$yetkili->name}}</td>
                        <td>
                            {{$yetkili->email}}  
                        </td>
                        <td>
                        	@foreach(\App\Salonlar::where('id',$yetkili->salon_id)->get() as $yetkilioldugusalon)

                        	{{$yetkilioldugusalon->salon_adi}}
                        	@endforeach

                        </td>
                        <td class="center">{{date('d.m.Y', strtotime($yetkili->created_at))}}</td>
                        <td class="center" style="font-size: 15px;">
                        	<a href="/sistemyonetim/yetkilidetay/{{$yetkili->id}}" title="Detaylar & Düzenle" class="icon"><i class="mdi mdi-settings"></i></a>
                        	<a href="/sistemyonetim/yetkilipasifet/{{$yetkili->id}}" title="Pasif Duruma Al" class="icon"><i class="mdi mdi-delete"></i></a>

                        </td>
                      </tr>

                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
           
      

@endsection