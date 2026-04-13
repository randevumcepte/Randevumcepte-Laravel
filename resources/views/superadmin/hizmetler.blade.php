@extends('layout.layout_sistemadmin')
@section('content')
<div class="page-head">
          <h2 class="page-head-title" style="float: left;">Hizmetler & Hizmet Kategorileri</h2>  
          
        </div>
        <div class="main-content container-fluid">
          <div class="row" style="margin-top: 30px">
            <div class="col-md-6">
              <div class="panel panel-default panel-table">
                
                <div class="panel-heading">Kayıtlı Hizmet Listesi
                     
                </div>
                <div class="panel-body">
                	<div id="hatamesaj"></div>
                </div>
                <div class="panel-body">
                  <table id="table4" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Hizmet</th>
                        <th>Kategori</th>
                        
                        <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>
                      
                    	<tr>
                    		<form method="get" id="yenihizmetekleme">
                    			 {!! csrf_field() !!}
                    		<td><input name="hizmetadi" placeholder="Hizmet adı" style="width:100%" class="form-control input-xs"></td>
                    		<td>
                    			<select name="hizmetkategorisi" style="width:100%"  class="form-control input-xs">
                    			@foreach($hizmetkategorileri as $kategoriler)
                                    <option value="{{$kategoriler->id}}">{{$kategoriler->hizmet_kategorisi_adi}} </option>
                    			@endforeach
                                 </select>
                    		</td>

                    		<td><button type="submit" class="btn btn-primary">Ekle</button></td>
                    		 </form>
                    	</tr>
                   
                    	@foreach($hizmetler as $hizmetliste)
                      <tr>
                        <td><input type="hidden" name="hizmetid" value="{{$hizmetliste->id}}" > {{$hizmetliste->hizmet_adi}}</td>
                        <td>
                            {{$hizmetliste->hizmet_kategorisi->hizmet_kategorisi_adi}}  
                        </td>
                         
                        <td class="center" style="font-size: 15px;">
                        	<a href="#" title="Detaylar & Düzenle" class="icon"><i class="mdi mdi-settings"></i></a>
                        	<a data-value="{{$hizmetliste->id}}" name="hizmetsil" title="Sil" class="icon"><i class="mdi mdi-delete"></i></a>

                        </td>
                      </tr>

                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
             <div class="col-md-6">
              <div class="panel panel-default panel-table">
                 
                <div class="panel-body">
                  <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Hizmet Kategorisi</th>
                       
                        <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>
                    	<tr>
                    		<form method="get" id="yenihizmetkategoriekleme">
                    			 {!! csrf_field() !!}
                    		<td><input name="hizmetkategorisiadi" placeholder="Hizmet kategorisi" class="form-control input-xs" style="width:100%"></td>
                    		 

                    		<td><button type="submit" class="btn btn-primary">Ekle</button></td>
                    		 </form>
                    	</tr>
                    	@foreach($hizmetkategorileri as $kategoriliste)
                      <tr>
                        <td><input type="hidden" name="hizmetkategoriid" value="{{$kategoriliste->id}}" >{{$kategoriliste->hizmet_kategorisi_adi}}</td> 
                        <td class="center" style="font-size: 15px;">
                        	<a href="#" title="Düzenle" class="icon"><i class="mdi mdi-settings"></i></a>
                        	<a data-value="{{$kategoriliste->id}}" name="hizmetkategorisil" title="Sil" class="icon"><i class="mdi mdi-delete"></i></a>

                        </td>
                      </tr>

                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div></div>
@endsection