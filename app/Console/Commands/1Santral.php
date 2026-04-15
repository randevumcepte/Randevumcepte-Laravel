<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\SantralBilgileri;
use App\Cdr;
use App\User;
use App\Personeller;
use App\SabitNumaralar;



class Santral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'santral:islemleri';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Santral raporları ve işlemleri bölümü';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {    
        $authToken = '';
        $santralbilgi = SantralBilgileri::first();
        if($santralbilgi->expires < date('Y-m-d H:i:s'))
            $authToken = self::santral_token_al();
        else
            $authToken = base64_decode($santralbilgi->token);
         
        $endpoint = "http://35.225.252.7/admin/api/api/gql";
        $qry = 'query{
          fetchAllCdrs (
             first : 999999 
            startDate: "1970-01-01"
            endDate: "'.date('Y-m-d').'"
          )
          {
            cdrs {
              id
                uniqueid
                calldate
                timestamp
                clid
                src
                dst
                dcontext
                channel
                dstchannel
                lastapp
                lastdata
                duration
                billsec
                disposition
                accountcode
                userfield
                did
                recordingfile
                cnum
                outbound_cnum
                outbound_cnam
                dst_cnam
                linkedid
                peeraccount
                sequence
                amaflags
            }
            totalCount
            status
            message
          }
        }';
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$authToken;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $qry]));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = json_decode(curl_exec($ch),true);
        $rapor = array();
        $gelen_arama = 0;
        $giden_arama = 0;
        $cevapsiz_arama = 0;
        $sesli_mesaj = 0;
        $basarisiz_arama = 0;
        $kayit_sayisi = 0;
        if($result['data']['fetchAllCdrs']['totalCount']>0)
        {

            foreach($result['data']['fetchAllCdrs']['cdrs'] as $cdr)
            {
                        $tel_kaynak = str_replace('+','',$cdr['src']);
                        $tel_hedef = str_replace('+','',$cdr['dst']);
                        $tel_kaynak = str_replace('90','',$tel_kaynak);
                        $tel_hedef = str_replace('90','',$tel_hedef);
                        $tel_kaynak = ltrim($tel_kaynak, '0'); 
                        $tel_hedef = ltrim($tel_hedef, '0'); 
                        $musteri_tel = '';
                        $musteri_id = '';
                        $durum = '';
                        $gorusmeyi_yapan='';
                        $cevapsiz_arama_var = true;
                        $musteri_var = User::join('musteri_portfoy','musteri_portfoy.user_id','=','users.id')->select('users.id as id','users.cep_telefon as telefon')->where(function($q) use($tel_kaynak,$tel_hedef){ 
                            $q->where('users.cep_telefon',$tel_kaynak);
                            $q->orWhere('users.cep_telefon',$tel_hedef); })->first();
                        if($musteri_var){
                            $musteri_tel= $musteri_var->telefon;
                            $musteri_id = $musteri_var->id;
                        } 
                        else
                        {
                            if(SabitNumaralar::where('numara',$cdr['dst'])->count()==0)
                                $musteri_tel = $tel_hedef;
                            else
                                $musteri_tel = $tel_kaynak;
                        }
                        if($cdr['disposition']=='NO ANSWER' && str_contains($cdr['recordingfile'],'in-')){
                            $durum = 0;
                            $gorusmeyi_yapan =  Personeller::where('dahili_no',$cdr['cnum'])->orWhere('dahili_no',$cdr['dst'])->value('personel_adi');
                            $cevapsiz_arama++;
                            
                        }
                        else{
                            $cevapsiz_arama_var = false;
                            $sabitnumaralar = SabitNumaralar::pluck('numara')->toArray();

                            if(in_array($cdr['src'],$sabitnumaralar )){
                                if($cdr['disposition']=='NO ANSWER'){
                                    $durum = 4; 
                                    $basarisiz_arama++;
                                    $cevapsiz_arama_var = true;
                                }
                                else
                                {
                                    $durum = 2; 
                                }
                                
                                $gorusmeyi_yapan = Personeller::where('dahili_no',$cdr['cnum'])->value('id');
                                $giden_arama++;
                            }
                            else{   
                                if($cdr['lastapp']=='VoiceMail' || str_contains($cdr['dst'],'vmu') )
                                {
                                    $cevapsiz_arama_var = true;
                                    $durum = 3;
                                    $dst = ltrim($cdr['dst'],'vmu');
                                    $gorusmeyi_yapan = Personeller::where('dahili_no',$dst)->value('id'); 
                                    $sesli_mesaj++;
                                }
                                else{ 
                                    $durum = 1;
                                    $gorusmeyi_yapan = Personeller::where('dahili_no',$cdr['dst'])->value('id'); 
                                    $gelen_arama++;
                                }
                                

                            }
                        }
                        $arama_butonu = '<button title="Ara" class="btn btn-success" name="musteriyi_ara" style="margin-right:2px" data-value="0'.$musteri_tel.'"><i class="fa fa-phone"></i></button>';
                        $ses_kaydi = '';
                        $tarih_dir =explode("-",$cdr['calldate']);
                        $tarih_son = explode(' ',$tarih_dir[2]);
                        if(!$cevapsiz_arama_var)
                            $ses_kaydi = '<a download name="ses_kaydi_indir" href="https://voicerecords.randevumcepte.com.tr/monitor/'.$tarih_dir[0].'/'.$tarih_dir[1].'/'.$tarih_son[0].'/'.$cdr['recordingfile'].'" class="btn btn-primary"><i class="fa fa-download"></i></a>
                                <button name="ses_kaydi_cal" data-value="https://voicerecords.randevumcepte.com.tr/monitor/'.$tarih_dir[0].'/'.$tarih_dir[1].'/'.$tarih_son[0].'/'.$cdr['recordingfile'].'" class="btn btn-danger"><i class="fa fa-play"></i></button>';
                        if(Cdr::where('kayit_no',$cdr['uniqueid'])->count()==0) 
                            array_push($rapor,array(
                                'tarih_saat' => date('Y-m-d',strtotime($cdr['calldate'])).' '.date('H:i:s',strtotime('+3 hours', strtotime( $cdr['calldate']))),
                
                                'user_id' => $musteri_id,
                                'personel_id' => $gorusmeyi_yapan,
                                'telefon'=> $musteri_tel,
                                'durum' => $durum,
                                'ses_kaydi' => $arama_butonu.$ses_kaydi,
                                'kayit_no'=>$cdr['uniqueid'],



                            ));
                       
                
                
            }
        }
        Cdr::insert($rapor);

    }
    public function santral_token_al()
    {
        $ch = curl_init();
        $santralbilgi = SantralBilgileri::first();
        curl_setopt($ch, CURLOPT_URL, 'http://35.225.252.7/admin/api/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $post = array(
            'grant_type' => 'client_credentials',
            'client_id' => base64_decode($santralbilgi->client_id),
            'client_secret' => base64_decode($santralbilgi->client_secret)
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
       
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
         curl_close($ch);
        $result2 = json_decode($result,true);
        $santralbilgi = SantralBilgileri::first();
        $santralbilgi->token = base64_encode($result2['access_token']);
        $santralbilgi->expires = date('Y-m-d H:i:s',strtotime('+55 minutes',strtotime(date('Y-m-d H:i:s'))));
        $santralbilgi->save();
        return $result2["access_token"];
    }
}