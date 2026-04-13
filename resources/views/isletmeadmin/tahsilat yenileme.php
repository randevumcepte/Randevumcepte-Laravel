


    public function musteri_tahsilatlari(Request $request,$statstr,$musteriid)
    {
        $tahsilatlar = Tahsilatlar::where('musteri_id',$musteriid)->get();
        $acik_adisyonlar = self::adisyon_yukle($request,'',0,'1970-01-01',date('Y-m-d'),$musteriid,''); 
         
        $tahsilat_tutari = 0;
        $html = "";

        $urunsatislari = AdisyonUrunler::where('adisyon_id',$request->adisyon_id)->sum('fiyat');
        $hizmetsatislari = AdisyonHizmetler::where('adisyon_id',$request->adisyon_id)->sum('fiyat');
        $paketsatislari = AdisyonPaketler::where('adisyon_id',$request->adisyon_id)->sum('fiyat');
        $html .= '<tr>
                              <td colspan="4" style="border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;">Ödeme Akışı</td>
                           </tr>';
        foreach ($tahsilatlar as $key => $tahsilatliste)
        {
            $html .= ' <tr>
                           <td>'.++$key.' Ödeme</td>
                           <td>'.date('d.m.Y',strtotime($tahsilatliste->odeme_tarihi)).'</td>
                           <td>'.number_format($tahsilatliste->tutar,2,',','.').'</td>
                           <td>
                              '.$tahsilatliste->odeme_yontemi->odeme_yontemi.'
                           </td>
                            <td>
                                <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" name="tahsilat_adisyondan_sil" data-value="'.$tahsilatliste->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                            </td>
                            
                            
                        </tr>';
            $tahsilat_tutari += $tahsilatliste->tutar;
        } 
        $adisyon_hizmet_html = '';
        $adisyon_urun_html = '';
        $adisyon_paket_html = '';
        foreach(Adisyonlar::whereIn('id',$acik_adisyonlar->pluck('id'))->get() as $adisyon)
        {
            foreach($adisyon->hizmetler as $hizmet)
            {
                $tutar = $hizmet->fiyat-TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar');
                $adisyon_hizmet_html .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0"> 

                                  <div class="col-md-4">
                                     <input type="hidden" class="adisyon_kalemler" name="adisyon_odeme_hizmet[]" value="'.$tutar.'" data-value="'.$hizmet->id.'">

                                      '.$hizmet->hizmet->hizmet_adi.' 
                                  </div>
                                  <div class="col-md-3">';
                                if($hizmet->personel_id != null)

                                    $adisyon_hizmet_html .= $hizmet->personel->personel_adi;
                                if($hizmet->cihaz_id != null) 
                                     $adisyon_hizmet_html .= $hizmet->cihaz->cihaz_adi;

                                 $adisyon_hizmet_html .='</div>
                                  <div class="col-md-2">
                                       
                                      1 adet
                                  </div>
                                 
                                  <div class="col-md-2" style="text-align:right">

                                        <span name="adisyon_hizmet_tahsilat_tutari" data-value="'.$hizmet->id.'">'.number_format($tutar,2,',','.').'</span> ₺
                                        <input type="hidden" name="adisyon_hizmet_tahsilat_tutari[]" data-value="'.$hizmet->id.'" data-inputmask =" \'alias\' : \'currency\'">
                                        <input type="hidden" name="adisyon_hizmet_tahsilat_tutari_girilen[]" data-value="'.$hizmet->id.'" value="'.$tutar.'"  data-inputmask =" \'alias\' : \'currency\'">
                                     </div>
                                     <div class="col-md-1">
                                        <button  type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="hizmet_formdan_sil_adisyon_mevcut"  data-value="'.$hizmet->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                                  </div>
                               </div>';
            }
            foreach($adisyon->urunler as $urun)
            {
                $tutar = $urun->fiyat - TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar');
                $adisyon_urun_html .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">

                                  <div class="col-md-4">
                                     <input type="hidden" class="adisyon_kalemler" name="adisyon_odeme_urun[]" value="'.$tutar.'" data-value="'.$urun->id.'">
                                     '.$urun->urun->urun_adi.'
                                  </div>
                                  <div class="col-md-3">
                                      '.$urun->personel->personel_adi.'

                                  </div>
                                  <div class="col-md-2">
                                        '.$urun->adet.' adet

                                  </div>
                                  
                                  <div class="col-md-2" style="text-align:right">
                                         
                                        <span name="adisyon_urun_tahsilat_tutari" data-value="'.$urun->id.'">'.number_format($tutar,2,',','.').'</span> ₺
                                        <input type="hidden" name="adisyon_urun_tahsilat_tutari[]" data-value="'.$urun->id.'" value="" data-inputmask =" \'alias\' : \'currency\'">
                                        <input type="hidden" name="adisyon_urun_tahsilat_tutari_girilen[]" data-value="'.$urun->id.'" value="'.$tutar.'"  data-inputmask =" \'alias\' : \'currency\'">
                                     </div>
                                     <div class="col-md-1">
                                         <button type="button"  style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="urun_formdan_sil" data-value="'.$urun->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                                     </div>
                               </div>';
            }
            foreach($adisyon->paketler as $paket)
            {
                $tutar = $paket->fiyat - TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar');
                $adisyon_paket_html .= '<div class="row" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">

                                  <div class="col-md-4">
                                     <input type="hidden"  class="adisyon_kalemler" name="adisyon_odeme_paket[]" value="'.$tutar.'" data-value="'.$paket->id.'">
                                     '.$paket->paket->paket_adi.'
                                  </div>
                                  <div class="col-md-3">
                                      '.$paket->personel->personel_adi.'

                                  </div>
                                   <div class="col-md-2">
                                      1 adet
                                  </div>
                                  <div class="col-md-2"  style="text-align:right">
                                         <span name="adisyon_paket_tahsilat_tutari" data-value="'.$paket->id.'">'.number_format($tutar,2,',','.').'</span> ₺
                                         <input type="hidden" name="adisyon_paket_tahsilat_tutari[]" data-value="'.$paket->id.'" value="" data-inputmask =" \'alias\' : \'currency\'">
                                         <input type="hidden" name="adisyon_paket_tahsilat_tutari_girilen[]" data-value="'.$paket->id.'" value="'.$tutar.'"  data-inputmask =" \'alias\' : \'currency\'">
                                  </div>
                                  <div class="col-md-1">

                                          <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545"  name="paket_formdan_sil" data-value="'.$paket->id.'" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>

                                  </div></div>';
            }

        }
        

       

        $statustext = $statstr;
        return array(
            'statustext' => $statustext,
            'html' => $html,
            'tahsilat_tutari' => number_format($tahsilat_tutari,2,',','.'),
            'toplam_tutar' => number_format(($urunsatislari + $hizmetsatislari + $paketsatislari - $adisyon->indirim_tutari),2,',','.'),
            'kalan_tutar' => number_format((($urunsatislari + $hizmetsatislari + $paketsatislari - $adisyon->indirim_tutari)-$tahsilat_tutari),2,',','.'),
            'adisyon_hizmetler_html' => $adisyon_hizmet_html,
            'adisyon_urunler_html' => $adisyon_urun_html,
            'adisyon_paketler_html' => $adisyon_paket_html,
            'tahsilat_sayisi' =>$tahsilatlar->count()


        );
    }