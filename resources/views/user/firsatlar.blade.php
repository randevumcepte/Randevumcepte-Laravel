@extends('layout.layout_firsatlar')
@section('content')
	<section class="block">
 <div class="container">
                    <div class="row">
                        <div class="col-md-12" >

                             <ul class="nav nav-pills" id="myTab-pills" role="tablist" style="text-align: center;">
                                    <li class="nav-item">
                                        <a class="nav-link icon" href="/profilim"><i class="fa fa-user" style="color:white"></i>Profilim</a>
                                    </li>
                                    <li class="nav-item">
                                       <a class="nav-link icon" href="/randevularim">
                                    <i class="fa fa-heart"></i>Randevularım
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                         <a class="nav-link icon" href="/favorilerim">
                                             <i class="fa fa-star"></i>Favorilerim
                                         </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active icon" href="/firsatlarim">
                                            <i class="fa fa-check"></i>Fırsatlarım
                                          </a>
                                    </li>
                                     <li class="nav-item">
                                         <a class="nav-link  icon" href="/ayarlarim">
                                             <i class="fa fa-recycle"></i>Ayarlarım
                                        </a> 
                                    </li>
                                </ul>
                        
                        </div>
                        <div class="col-md-12">

                        	 YAKINDA
                        </div>
                    </div>
                </div>

	</section>
	 
@endsection