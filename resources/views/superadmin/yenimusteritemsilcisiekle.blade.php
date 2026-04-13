@extends('layout.layout_sistemadmin')
@section('content')
  <div class="page-head">
          <h2 class="page-head-title" style="float: left;">Yeni Müşteri Temsilcisi</h2>  
          
   </div>
     <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
            	<form id="yenimusteritemsilcisi" method="post">
            		{!!csrf_field()!!}
            		<div class="form-group">
            			<input type="text" required class="form-control" placeholder="Ad Soyad" name="name">
            		</div>
            		 <div class="form-group">
            			<input type="number" class="form-control" placeholder="Telefon" name="phone">
            		</div> 
            		<div class="form-group">
            			<input type="email" required class="form-control" placeholder="E-posta (Kullanıcı adı)" name="email">
            		</div> 
            	
            		<div class="form-group">
            			<input type="password" required class="form-control" placeholder="Şifre" name="password">
            		</div> 
            		<div class="form-group">
            			<div class="be-checkbox be-checkbox-color inline">
                          <input id="admin" name="admin" type="checkbox">
                          <label for="admin">Admin Yetkileri Verilsin</label>
                        </div>
            		</div>
            		<div class="form-group">
            			<button type="submit" class="btn btn-space btn-primary btn-big" style="width:100%">Ekle</button>
            		</div>


            	</form>
            </div>
        </div>
    </div>
@endsection