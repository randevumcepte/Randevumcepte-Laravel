@extends('layout.layout_sistemadmin')
@section('content')

 
      
        <div class="page-head">
          <h2 class="page-head-title" style="float: left;">İşletmeler</h2> <a style="float: left; margin:10px 0 0 10px" href="/sistemyonetim/yeniisletme" class="btn btn-primary">Yeni Ekle</a>
          
        </div>
        <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                 
                <div class="panel-body">
                  <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>İşletme Adı</th>
                        <th>İl / İlçe</th>
                        <th>Yetkili</th>
                        <th>Üyelik Tarihi</th>
                        @if(Auth::user()->admin==1)
                        <th>Müşteri Temsilcisi</th>
                        @endif
                        <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>
                    	@foreach($isletmeler as $isletmeliste)
                      <tr>
                        <td>{{$isletmeliste->salon_adi}}</td>
                        <td>
                            {{ optional($isletmeliste->il)->il_adi ?: '—' }} / {{ optional($isletmeliste->ilce)->ilce_adi ?: '—' }}
                        </td>
                        <td>
                        	@foreach(\App\IsletmeYetkilileri::where('salon_id',$isletmeliste->id)->get() as $isletmeyetkilileri)

                        	{{$isletmeyetkilileri->name}}
                        	@endforeach

                        </td>
                        <td class="center">{{date('d.m.Y', strtotime($isletmeliste->created_at))}}</td>
                        @if(Auth::user()->admin==1)
                          <td>{{\App\SistemYoneticileri::where('id',$isletmeliste->musteri_yetkili_id)->value('name')}}</td>
                        @endif
                        <td class="center" style="font-size: 20px;">
                           
                        	<a href="/sistemyonetim/isletmedetay/{{$isletmeliste->id}}" title="Detaylar & Düzenle" class="icon"><i class="mdi mdi-settings"></i></a>

                          @if($isletmeliste->uyelik_turu == 1 ||$isletmeliste->uyelik_turu==3)
                          @php
                              $slugify = function($s){
                                  return $s ? str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($s))) : null;
                              };
                              $tur = optional($isletmeliste->salon_turu)->salon_turu_adi;
                              $il = optional($isletmeliste->il)->il_adi;
                              $ilce = optional($isletmeliste->ilce)->ilce_adi;
                          @endphp
                          @if($tur && $il && $ilce)
                          <a href="/{{ $slugify($tur) }}/{{ $slugify($il) }}/{{ $slugify($ilce) }}/{{$isletmeliste->id}}/{{ $slugify($isletmeliste->salon_adi) }}" target="_blank" title="İşletmeyi sayfada görüntüle"><span class="mdi mdi-search-in-page"></span></a>
                          @endif
                          @if($isletmeliste->uyelik_turu == 3)
                          <a href="/sistemyonetim/avantajlar" title="İşletmenin avantajlarını görüntüle"><span class="mdi mdi-search-in-page"></span></a>
                          @endif
                          @endif
                          @if($isletmeliste->uyelik_turu == 2)
                          <a href="/sistemyonetim/avantajlar" title="İşletmenin avantajlarını görüntüle"><span class="mdi mdi-search-in-page"></span></a>
                          @endif
                        	<a href="/sistemyonetim/isletmepasifet/{{$isletmeliste->id}}" title="Pasif Duruma Al" class="icon"><i class="mdi mdi-delete"></i></a>

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