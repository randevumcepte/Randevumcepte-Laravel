@extends('layout.layout_sistemadmin')
@section('content')
    <div class="page-head">
          <h2 class="page-head-title" style="float: left;">Müşteri Temsilcileri</h2> <a style="float: left; margin:10px 0 0 10px" href="/isletmeyonetim/yenimusteritemsilcisiekle" class="btn btn-primary">Yeni Ekle</a>
          
        </div>
        <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                 
                <div class="panel-body">
                  <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Müşteri Temsilcisi</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                     	<th>Yetkiler</th>
                        <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>
                    	@foreach($sistemyoneticileri as $sistemyonetici)
                      <tr>
                        <td>{{$sistemyonetici->name}}</td>
                        <td>
                            {{$sistemyonetici->email}}  
                        </td>
                        <td>
                          {{$sistemyonetici->telefon}}

                        </td>
                        <td class="center">@if($sistemyonetici->admin) Admin @else Müşteri Temsilcisi @endif</td>
                        <td class="center" style="font-size: 15px;">
                        	 
                        	<a href="/sistemyonetim/sistemyoneticisil/{{$sistemyonetici->id}}" title="Yönetici Hesabını Sil" class="icon"><i class="mdi mdi-delete"></i></a>

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