@extends('layout.layout_sistemadmin')
@section('content')
  <div class="main-content container-fluid">
  	 <form id="yetkilidetayduzenleme" method="post"  enctype="multipart/form-data">

                	{!!csrf_field()!!}

          <div class="splash-container sign-up">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
              <div class="panel-heading">
              	 <div class="user-display">
                  <div class="user-display-bottom">
                    <div class="user-display-avatar">
                    	@if($yetkili->profil_resim == null || $yetkili->profil_resim =='')
                    	<img id="yetkiliprofilresim" src="{{secure_asset('public/isletmeyonetim_assets/img/avatar-150.png')}}" alt="Avatar">
                    	@else
                    	<img id="yetkiliprofilresim" src="{{secure_asset($yetkili->profil_resim)}}" alt="Avatar">
                    	@endif
                    	<div class="single-file-input">
                            <input type="file" id="yetkiliprofil" name="yetkiliprofil">
                             <div class="btn btn-primary"><span class="mdi mdi-photo-size-select-large"></span></div>
                     	</div>
                    </div>
                    <div class="user-display-info">
                      <div class="name">
                      	<div class="form-group">
                      	 <input type="text" name="yetkiliadi" value="{{$yetkili->name}}" class="form-control">
                      	</div>
                      </div>
                         
                       
                    </div>
                     <div class="row user-display-details">
                       <select name="yetkiliolunansalon" class="form-control">
                      	  <option value="0">Yetkili olduğu salonları seç...</option>
                      	  @foreach($salonlar as $salon)
                      	  @if($salon->id == $yetkili->salon_id)
                          <option selected value="{{$salon->id}}">{{$salon->salon_adi}}</option>
                          @else
                           <option value="{{$salon->id}}">{{$salon->salon_adi}}</option>
                          @endif
                          @endforeach
                           
                        </select>
                    </div>
                  </div>
              </div>
              	 

              </div>
              <div class="panel-body">
                  <input type="hidden" value="{{$yetkili->id}}" id="yetkiliid" name="yetkiliid">
                  <div class="form-group">
                    <input type="email" name="eposta" required value="{{$yetkili->email}}" placeholder="E-posta" autocomplete="off" class="form-control">
                  </div>
                  <div class="form-group">
                    <input type="telefon" name="number"  placeholder="Telefon" class="form-control">
                  </div>
                  <div class="form-group"> 
                      <input name="gsm1" type="number"  value="{{$yetkili->gsm1}}" placeholder="Gsm 1" class="form-control">
                  </div>
                   <div class="form-group"> 
                      <input name="gsm2" type="number" value="{{$yetkili->gsm2}}" placeholder="Gsm 2" class="form-control">
                  </div>
                   <div class="form-group"> 
                      <input name="sifre" type="text" placeholder="Şifre (değiştirmeden güncelleme için boş bırakın...)" class="form-control">
                  </div>
                  <div class="form-group">
                  	<button type="submit" class="btn btn-primary" style="width:100%">Bilgileri Güncelle</button>
                  </div>
                  <div id="hata" class="form-group">
                  </div>
              
              </div>
            </div>
            <div class="splash-footer"> </div>
          </div>
      </form>
        </div>

@endsection