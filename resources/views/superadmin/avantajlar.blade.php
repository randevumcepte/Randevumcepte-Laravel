@extends('layout.layout_sistemadmin')
@section('content')
 <div class="page-head">
          <h2 class="page-head-title" style="float: left;">Avantajlar</h2> <a style="float: left; margin:10px 0 0 10px" href="/sistemyonetim/yeniavantaj" class="btn btn-primary">Yeni Ekle</a>
          
        </div>
        <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                 
                <div class="panel-body">
                  <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                          
                        <th style="width: 200px">Avantaj</th>
                        <th>İşletme</th>
                        <th style="width: 200px">Yayınlama Tarihi</th>
                        <th style="width: 200px">Bitiş Tarihi</th>                        
                        <th style="width: 200px">Toplam Satış</th>
                        <th>Kullanılan</th>
                        <th>Kullanılmayan</th>                
                         <th>Durum</th>   
                        <th style="width:200px">İşlemler</th>
                      </tr>
                    </thead>
                     <tbody id="avantajtablohtml">
                    	 {!!$avantajhtml!!}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div id="hata"></div>

@endsection