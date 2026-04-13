<?php

namespace App\Http\Controllers;

use App\Callback;
use App\Form;
use App\Data;
use App\Logs;
 use Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard; 
use App\User;
 use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
	 public function __construct()
    { 
      
    }
    public function onaylionaysizsatislar(){
         $satislar = Form::join('data','forms.data_id','=','data.id')->join('users','forms.user_id','=','users.id')->whereIn('forms.status',[2,7])->where(    function ($q) 
                        { 
                            $q->where('data.category','like','%doktor%');
                              $q->orWhere('data.category','like','%diyetisyen%');
                                $q->orWhere('data.category','like','%cerrah%');    
                                $q->orWhere('data.category','like','%hekim%');
                                $q->orWhere('data.category','like','%psikolog%');
                                $q->orWhere('data.category','like','%psikiyatri%');  
                                 $q->orWhere('data.category','like','%klinik%');  
                                  $q->orWhere('data.category','like','%jinekolog%');   
                                 $q->orWhere('data.category','like','%ortodonti%');    
                                  $q->orWhere('data.category','like','%tüp bebek%');   
                                   $q->orWhere('data.category','like','%veteriner%');
                                     $q->orWhere('data.category','like','%akupunktur%');  
                                      $q->orWhere('data.category','like','%zayıflama merkez%');                   
                        }
                    )->select('users.name','data.title','data.address','data.phone','data.mobile','data.mobile_alt','data.category','forms.packages','data.keywords','data.website','data.author')->get();
           return view('onaylionaysizsatislar',['satislar'=>$satislar]);
    }
    public function dashboard()
    {
          if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
          if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
       
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('dashboard',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);

    }

    public function loadDashboard()
    {
        return [
            'calls' =>  [
                'saved' => Form::where('user_id',Auth::user()->id)->where('status', Data::SAVED)->count(),
                  'confirmed' => Form::where('user_id', Auth::user()->id)->where('status', Data::CONFIRMED)->count(),
                 'not_reached' => Form::where('user_id', Auth::user()->id)->where('status', Data::NOT_REACHED)->count(),
                'false' => Form::where('user_id', Auth::user()->id)->where('status', Data::FALSE_NUMBER)->count(),
                'canceled' => Form::where('user_id', Auth::user()->id)->where('status', Data::CANCELED)->count(),
                'not_interesting' => Form::where('user_id', Auth::user()->id)->where('status', Data::NOT_INTERESTING)->count(),
             'callback' => Callback::where('user_id', Auth::user()->id)->count(),
                'not_answered' => Form::where('user_id', Auth::user()->id)->where('status', Data::NOT_ANSWERED)->count(),
                'not_available' => Form::where('user_id', Auth::user()->id)->where('status', Data::NOT_AVAILABLE)->count(),
                'state_agency' => Form::where('user_id', Auth::user()->id)->where('status', Data::STATE_AGENCY)->count(),   
            ]
        ];

    }
      public function loadDashboard2(Request $request)
    {
          $remainingTime =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',$request->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime2 =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',$request->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
        $remainingTime_break = '01:30:00';
        foreach ($remainingTime as $remainingTime1) {
            if(!is_null($remainingTime1->remaining)){

             $remainingTime_break = $remainingTime1->remaining;
            }
              
        }
         
        $remainingTime_meeting = '00:30:00';
        foreach ($remainingTime2 as $remainingTime_1) {
            if(!is_null($remainingTime_1->remaining)){
             $remainingTime_meeting = $remainingTime_1->remaining;
            }
              
        }
       


        return [
            'calls' =>  [
                'saved' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::SAVED)->count(),
                  'confirmed' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::CONFIRMED)->count(),
                 'not_reached' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::NOT_REACHED)->count(),
                'false' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::FALSE_NUMBER)->count(),
                  'canceled' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::CANCELED)->count(),
                'not_interesting' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::NOT_INTERESTING)->count(),
             'callback' => Callback::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->count(),
                'not_answered' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::NOT_ANSWERED)->count(),
                'not_available' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::NOT_AVAILABLE)->count(),
                'state_agency' => Form::where('user_id', $request->id)->where('updated_at','>=', date("Y-m-d").' 00:00:00')->where('updated_at','<=', date("Y-m-d").' '.date('H:i'))->where('status', Data::STATE_AGENCY)->count(),   
                'breaks' => $remainingTime_break,
                'meeting' => $remainingTime_meeting,
                
            ]
        ];

    }

    public function warningMessage(){
        return view('warning');
    }
    public function warningMessage2(){
        return view('warning2');
    }
      public function warningMessage3(){
        return view('warning3');
    }
      public function warningMessage4(){
        return view('warning4');
    }
      public function warningMessage5(){
        return view('warning5');
    }
      public function warningMessage6(){
        return view('warning6');
    }
    public function startTalk(){
        $logs = new Logs();
        $logs->user_id=Auth::user()->id;
        $logs->description="Görüşme başlatıldı.";
        $logs->save();

    }
    public function calling(){
        $logs = new Logs();
        $logs->user_id=Auth::user()->id;
        $logs->description="Arama başlatıldı.";
        $logs->save();
    }
       public function calling2(){
        $logs = new Logs();
        $logs->user_id=Auth::user()->id;
        $logs->description="Arama başlatıldı (alternatif numara).";
        $logs->save();
    }
       public function calling3(){
        $logs = new Logs();
        $logs->user_id=Auth::user()->id;
        $logs->description="Arama başlatıldı (2. alternatif numara).";
        $logs->save();
    }

    public function changeStatusToBreakMode(){
           
           $user = auth()->user();
           $user->status=1;
           $user->save(); 
           $logs = new Logs();
           $logs->user_id=Auth::user()->id;
           $logs->description = "Agent mola moduna geçildi";
           $logs->start_time = date('Y-m-d H:i:s');
           $logs->end_time = date('Y-m-d H:i:s');
           $logs->save();
          
    }
      public function changeStatusToBreakMode2(){
           
           $user = auth()->user();
           $user->status=4;
           $user->save(); 
          
          
    }
    public function saveStatus(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
            
             }
             if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Agent toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save();
             

            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save();
           

            
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent toplantı moduna geçildi";
             $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/form');
    }

     public function saveStatus2(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
              if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Agent toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save();
           

            
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent toplantı moduna geçildi";
              $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/notreached');
    }
     public function saveStatus6(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
              if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Agent toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save();
           

            
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent toplantı moduna geçildi";
              $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/search');
    }
      public function saveStatus_developer1(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
              if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Developer toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save();
           

            
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Developer toplantı moduna geçildi";
              $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/jobs1');
    }
    public function saveStatus_developer2(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
              if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Developer toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save();
           

            
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Developer toplantı moduna geçildi";
              $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/jobs2');
    }
        public function saveStatus_developer3(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
              if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Developer toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save();
           

            
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Developer toplantı moduna geçildi";
              $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/jobs3');
    }



     public function saveStatus3(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
             if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Agent toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save(); 
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent toplantı moduna geçildi";
             $logs->start_time =date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/callbacks');
    }
     public function saveStatus4(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
              if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Agent toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save(); 
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent toplantı moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/forms');
    }
      public function saveStatus5(){
        $user = auth()->user();
        if($_POST['action']=="start"){
            if($user->status==1){
                 $logLastBreak = Logs::where('user_id',$user->id)->where('description','=','Agent mola moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->orderBy('id','desc')->first();
             if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
             $logLastBreak->save();
             }
            
             }
             if($user->status==3){
                  $logLastBreak = Logs::where('description','=','Agent toplantı moduna geçildi')->where('start_time','like','%'.date('Y-m-d').'%')->where('user_id',$user->id)->orderBy('id','desc')->first();
               if($logLastBreak){
                   $logLastBreak->end_time = date("Y-m-d H:i:s");
                     $logLastBreak->save();
                }
             }
             $user->status=2;
             $user->save();

          
             $logs = new Logs();
             $logs->user_id = Auth::user()->id;
             $logs->description = "Sistem yeniden başlatma";
             
             $logs->save(); 
            
        }
        if($_POST['action']=="start2"){
             
             $user->status=2;
             $user->save(); 
        }
       else if($_POST['action']=="stop1"){ 
             $user->status=1;
             $user->save();  
            
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent mola moduna geçildi";
             $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
      
         else if($_POST['action']=="meeting"){ 
             $user->status=3;
             $user->save(); 
             $logs= new Logs();
             $logs->user_id=Auth::user()->id;
             $logs->description = "Agent toplantı moduna geçildi";
              $logs->start_time = date("Y-m-d H:i:s");
             $logs->save(); 
       }
        return redirect('/');
    }

    public function callbacks()
    { 
          if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('callbacks',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
       
      public function notreached()
    { 
          if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('notreached',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
     
    public function loadCallbacks()
    {
        return Callback::where('user_id', Auth::user()->id)->orderBy('id','desc')->get();
    }
     public function loadNotreached()
    {
        return Form::where('user_id', Auth::user()->id)->where(function($q){ $q->where('status', Data::NOT_REACHED); $q->orWhere('status', Data::NOT_ANSWERED); $q->orWhere('status',Data::NOT_AVAILABLE);})->orderBy('id','desc')->get();
    }  

    public function search()
    {
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('search',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
     
    /* saha ekibi */
    
      public function searchfield()
    {
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('searchfield',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

     public function hotsalefield()
    {
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('hotsalefield',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

    /*saha ekibi son */


   /*saha ekibi search data */
       public function searchDataField(Request $request)
    {
        $phone = '%'.$request['phone'] . '%';
       $data = Data::where('pool_id','=' ,605)->where(function ($q) use($phone){
            $q->where('phone', 'like', $phone);
            $q->orWhere('mobile', 'like', $phone);
            $q->orWhere('mobile_alt', 'like', $phone);
            $q->orWhere('title', 'like', $phone); 
            
             $q->orWhere('address', 'like', $phone);

        })->limit(100)->get();
       

       return $data;
        
    }
/*saha ekibi search data son */
   
    public function searchData(Request $request)
    {
        $phone = '%'.$request['phone'] . '%';
       $data = Data::where('status','!=' ,Data::CALLBACK)->where('status','!=',Data::SAVED)->where('status','!=',Data::CONFIRMED)->where('status','!=',Data::FALSE_NUMBER)->where('status','!=',Data::STATE_AGENCY)->where(function ($q) use($phone){
            $q->where('phone', 'like', $phone);
            $q->orWhere('mobile', 'like', $phone);
            $q->orWhere('mobile_alt', 'like', $phone);
            $q->orWhere('title', 'like', $phone); 
            $q->orWhere('tax_no', 'like', $phone);

        })->groupBy('phone')->limit(50)->offset(0)->get();
       $data2['filtereddata'] = '';
       foreach ($data as $eachdata) {
           $datasaved = Data::where('phone','=',$eachdata->phone)->where(function ($q){$q->where('status',Data::CALLBACK);$q->orWhere('status',Data::SAVED);$q->orWhere('status',Data::CONFIRMED);})->count();
           if($datasaved == 0){
                $data2['filtereddata'][] = [
                'id' => $eachdata->id,
                'title' => $eachdata->title,
                'address' => $eachdata->address,
                'city' =>$eachdata->city,
                'postalcode' => $eachdata->postalcode,
                'phone' => $eachdata->phone,
                'mobile' => $eachdata->mobile,
                'mobile_alt' => $eachdata->mobile_alt,
                'fax' => $eachdata->fax,
                'category' => $eachdata->category,
                'author' => $eachdata->author,
                'keywords' => $eachdata->keywords,
                'email' => $eachdata->email,
                'tax_no' => $eachdata->tax_no,
                'tax_admin' => $eachdata->tax_admin,
                'status' => $eachdata->status,
                'pool_id' => $eachdata->pool_id,
                'website' => $eachdata->website,
                'site_title' => $eachdata->site_title,
                'note' => $eachdata->note,
                'about' => $eachdata->about,
                'description' => $eachdata->description,



                ];

           }
       }

       return $data2;
        
    }
    public function searchData_Filter(){

    }
    public function userInfo(){
        return [
            'users' =>  [
                'status' => User::where('id', Auth::user()->id)->value('status'),
                'is_admin' => User::where('id', Auth::user()->id)->value('is_admin'),
                'id' => User::where('id',Auth::user()->id)->value('id'),
            ]
        ];
 
    }
     public function activeAgentInfo(Request $request){
        
        return [
            'activeagent' =>  [
                'name' => User::where('id', $request['agent2'])->value('name'),
                'formid' => $request['formid'],
                 
            ]
        ];
 
    }
    


     
    public function forms()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('admin.forms',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

 public function formswebsite()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('admin.formswebsite',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }



  public function lawenforcement()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('admin.lawenforcement',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

    /*icra sayfası*/

 public function enforcement()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('lawyer.enforcement',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

    /*icra sayfası sonu */

    public function enforcementClosed()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('lawyer.enforcementclosed',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
   
public function completed()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('completed',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

    public function loadForms(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
            $status1='';
            $status2='';
            $status3='';
             $status4='';
            $status5='';
            $status6='';
             $status7='';
            if(Auth::user()->is_admin){
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
             if($request->request->get('status')!=''){
                if($request->request->get('status')==0){
                    $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
                }
                else if($request->request->get('status')==2){
                      $status1='2';
                     $status2='2';
                      $status3='2';
                    $status4='2';
                     $status5='2';
                     $status6='2';
                      $status7='2';
                }
                else if($request->request->get('status')==7){
                     $status1='7';
                     $status2='7';
                      $status3='7';
                         $status4='7';
                     $status5='7';
                      $status6='7';
                       $status7='7';
                }
                  else if($request->request->get('status')==6){
                     $status1='6';
                     $status2='6';
                      $status3='6';
                         $status4='6';
                     $status5='6';
                      $status6='6';
                       $status7='6';
                }
                 else if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                      $status3='12';
                         $status4='12';
                     $status5='12';
                      $status6='12';
                       $status7='12';
                }
                 else if($request->request->get('status')==13){
                     $status1='13';
                     $status2='13';
                      $status3='13';
                         $status4='13';
                     $status5='13';
                      $status6='13';
                       $status7='13';
                }
                 else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                      $status3='14';
                         $status4='14';
                     $status5='14';
                      $status6='14';
                       $status7='14';
                }
             }
             else{
                   $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2,$status3,$status4,$status5,$status6,$status7])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->limit(40)->orderBy('forms.id','desc');
             
             
            }
            else{
                  if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

           
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
           if($request->request->get('status')!=''){
                if($request->request->get('status')==0){
                    $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
                }
                else if($request->request->get('status')==2){
                      $status1='2';
                     $status2='2';
                      $status3='2';
                    $status4='2';
                     $status5='2';
                     $status6='2';
                      $status7='2';
                }
                else if($request->request->get('status')==7){
                     $status1='7';
                     $status2='7';
                      $status3='7';
                         $status4='7';
                     $status5='7';
                      $status6='7';
                       $status7='7';
                }
                  else if($request->request->get('status')==6){
                     $status1='6';
                     $status2='6';
                      $status3='6';
                         $status4='6';
                     $status5='6';
                      $status6='6';
                       $status7='6';
                }
                 else if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                      $status3='12';
                         $status4='12';
                     $status5='12';
                      $status6='12';
                       $status7='12';
                }
                 else if($request->request->get('status')==13){
                     $status1='13';
                     $status2='13';
                      $status3='13';
                         $status4='13';
                     $status5='13';
                      $status6='13';
                       $status7='13';
                }
                 else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                      $status3='14';
                         $status4='14';
                     $status5='14';
                      $status6='14';
                       $status7='14';
                }
             }
             else{
                   $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2,$status3,$status4,$status5,$status6,$status7])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','=', Auth::user()->id)->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->limit(40)->orderBy('forms.id','desc');
             
            }
         
 
           
            return $forms->get();
     //   }
       
         
       
    }
    /*web siteler için ayrı*/

    public function loadFormsWebsite(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
            $status1='';
            $status2='';
            $status3='';
             $status4='';
            $status5='';
            $status6='';
             $status7='';
            if(Auth::user()->is_admin){
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
             if($request->request->get('status')!=''){
                if($request->request->get('status')==0){
                    $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
                }
                else if($request->request->get('status')==2){
                      $status1='2';
                     $status2='2';
                      $status3='2';
                    $status4='2';
                     $status5='2';
                     $status6='2';
                      $status7='2';
                }
                else if($request->request->get('status')==7){
                     $status1='7';
                     $status2='7';
                      $status3='7';
                         $status4='7';
                     $status5='7';
                      $status6='7';
                       $status7='7';
                }
                  else if($request->request->get('status')==6){
                     $status1='6';
                     $status2='6';
                      $status3='6';
                         $status4='6';
                     $status5='6';
                      $status6='6';
                       $status7='6';
                }
                 else if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                      $status3='12';
                         $status4='12';
                     $status5='12';
                      $status6='12';
                       $status7='12';
                }
                 else if($request->request->get('status')==13){
                     $status1='13';
                     $status2='13';
                      $status3='13';
                         $status4='13';
                     $status5='13';
                      $status6='13';
                       $status7='13';
                }
                 else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                      $status3='14';
                         $status4='14';
                     $status5='14';
                      $status6='14';
                       $status7='14';
                }
             }
             else{
                   $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2,$status3,$status4,$status5,$status6,$status7])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.packages','like','%"package_4":{"selected":true%')->where('forms.form_date','<=',$enddate)->limit(40)->orderBy('forms.id','desc');
             
             
            }
            else{
                  if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

           
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
           if($request->request->get('status')!=''){
                if($request->request->get('status')==0){
                    $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
                }
                else if($request->request->get('status')==2){
                      $status1='2';
                     $status2='2';
                      $status3='2';
                    $status4='2';
                     $status5='2';
                     $status6='2';
                      $status7='2';
                }
                else if($request->request->get('status')==7){
                     $status1='7';
                     $status2='7';
                      $status3='7';
                         $status4='7';
                     $status5='7';
                      $status6='7';
                       $status7='7';
                }
                  else if($request->request->get('status')==6){
                     $status1='6';
                     $status2='6';
                      $status3='6';
                         $status4='6';
                     $status5='6';
                      $status6='6';
                       $status7='6';
                }
                 else if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                      $status3='12';
                         $status4='12';
                     $status5='12';
                      $status6='12';
                       $status7='12';
                }
                 else if($request->request->get('status')==13){
                     $status1='13';
                     $status2='13';
                      $status3='13';
                         $status4='13';
                     $status5='13';
                      $status6='13';
                       $status7='13';
                }
                 else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                      $status3='14';
                         $status4='14';
                     $status5='14';
                      $status6='14';
                       $status7='14';
                }
             }
             else{
                   $status1='2';
                     $status2='7';
                     $status3='6';
                     $status4='12';
                     $status5='13';
                     $status6='14';
                     $status7='15';
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2,$status3,$status4,$status5,$status6,$status7])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('forms.packages','like','%"package_4":{"selected":true%')->where('users.id','=', Auth::user()->id)->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->limit(40)->orderBy('forms.id','desc');
             
            }
         
 
           
            return $forms->get();
     //   }
       
         
       
    }


    /*web siteler için ayrı */


     public function loadLawenforcement(Request $request)
    {   
          $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
            $status1='';
            $status2='';
            $status3='';
             $status4='';
         $status5='';
            if(Auth::user()->is_admin){
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
             if($request->request->get('status')!=''){
                  if($request->request->get('status')==0){
                 
                     $status1='6';
                     $status2='12';
                     $status3='13';
                     $status4='14';
                      $status5='15';
                  }
            
                  else if($request->request->get('status')==6){
                     $status1='6';
                     $status2='6';
                      $status3='6';
                         $status4='6';
                          $status5='6';
                   
                }
                 else if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                      $status3='12';
                         $status4='12';
                          $status5='12';
                    
                }
                 else if($request->request->get('status')==13){
                     $status1='13';
                     $status2='13';
                      $status3='13';
                         $status4='13';
                          $status5='13';
                    
                }
                 else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                      $status3='14';
                         $status4='14';
                          $status5='14';
                   
                }
                 else if($request->request->get('status')==14){
                     $status1='15';
                     $status2='15';
                      $status3='15';
                         $status4='15';
                          $status5='15';
                   
                }
             }
             else{
                 
                     $status1='6';
                     $status2='12';
                     $status3='13';
                     $status4='14';
                      $status5='15';
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2,$status3,$status4,$status5])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.law_date','>=',$startdate)->where('forms.law_date','<=',$enddate)->limit(40)->orderBy('forms.law_date','desc');
             
             
            }
            else{
                  if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

           
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
          if($request->request->get('status')!=''){
                  if($request->request->get('status')==0){
                 
                     $status1='6';
                     $status2='12';
                     $status3='13';
                     $status4='14';
                      $status5='15';
                  }
            
                  else if($request->request->get('status')==6){
                     $status1='6';
                     $status2='6';
                      $status3='6';
                         $status4='6';
                          $status5='6';
                   
                }
                 else if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                      $status3='12';
                         $status4='12';
                          $status5='12';
                    
                }
                 else if($request->request->get('status')==13){
                     $status1='13';
                     $status2='13';
                      $status3='13';
                         $status4='13';
                          $status5='13';
                    
                }
                 else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                      $status3='14';
                         $status4='14';
                          $status5='14';
                   
                }
                 else if($request->request->get('status')==14){
                     $status1='15';
                     $status2='15';
                      $status3='15';
                         $status4='15';
                          $status5='15';
                   
                }
             }
             else{
                 
                     $status1='6';
                     $status2='12';
                     $status3='13';
                     $status4='14';
                      $status5='15';
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2,$status3,$status4,$status5])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','=', Auth::user()->id)->where('forms.law_date','>=',$startdate)->where('forms.law_date','<=',$enddate)->limit(40)->orderBy('forms.law_date','desc');
             
            }
         
 
           
            return $forms->get();
     //   }

         
       
    }

      public function loadEnforcementClosed(Request $request)
    {   
          $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
            $status1='';
            $status2='';
            $status3='';
             $status4='';
         $status5='';
            if(Auth::user()->is_admin){
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
         if($request->request->get('status')!=''){

                 if($request->request->get('status')==0){
                     $status1='12';
                     $status2='15';
                   
                    
                }
                else  if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                   
                    
                }
                 else if($request->request->get('status')==15){
                     $status1='15';
                     $status2='15';
                     
                   
                }
              
             }
             else{
                   
                     $status1='12';
                     $status2='15';
                 
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.close_date','>=',$startdate)->where('forms.close_date','<=',$enddate)->limit(40)->orderBy('forms.close_date','desc');
             
             
            }
            else{
                  if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

           
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
           if($request->request->get('status')!=''){

                 if($request->request->get('status')==0){
                     $status1='12';
                     $status2='15';
                   
                    
                }
                else  if($request->request->get('status')==12){
                     $status1='12';
                     $status2='12';
                   
                    
                }
                 else if($request->request->get('status')==15){
                     $status1='15';
                     $status2='15';
                     
                   
                }
              
             }
             else{
                   
                     $status1='12';
                     $status2='15';
                 
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','=', Auth::user()->id)->where('forms.close_date','>=',$startdate)->where('forms.close_date','<=',$enddate)->limit(40)->orderBy('forms.close_date','desc');
             
            }
         
 
           
            return $forms->get();
     //   }

         
       
    }
    
    /*icra dosyası yükleme*/

  public function loadEnforcement(Request $request)
    {   
          $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
           $agent ='%';
            $startdate = '';
            $enddate = '';
        if(Auth::user()->is_admin){
           
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.status','=',14)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.form_date','asc');

        }
        else{
             $agent ='%%';
                if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.status','=',14)->where('forms.user_id','=',Auth::user()->id)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.form_date','asc');
        }
            
              
 
           
        return $forms->get();
     
         
       
    }


    /*icra dosyası yükleme son*/

     public function loadLawyerFiles(Request $request)
    {   
          $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
           $agent ='%';
            $startdate = '';
            $enddate = '';
            $status1="";
            $status2="";
        if(Auth::user()->is_admin || Auth::user()->is_lawyer){
           
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
             if($request->request->get('status')!=''){
                if($request->request->get('status')==0){
                    $status1='13';
                     $status2='14';
                   
                }
                else if($request->request->get('status')==13){
                      $status1='13';
                     $status2='13';
                   
                }
                else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                  
                }
                
             }
             else{
                   $status1='13';
                     $status2='14';
                  
             }
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.law_date','>=',$startdate)->where('forms.law_date','<=',$enddate)->orderBy('forms.law_date','asc');

        }
        else{
             $agent = Auth::user()->id;
                if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }
 
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
              if($request->request->get('status')!=''){
                if($request->request->get('status')==0){
                    $status1='13';
                     $status2='14';
                   
                }
                else if($request->request->get('status')==13){
                      $status1='13';
                     $status2='13';
                   
                }
                else if($request->request->get('status')==14){
                     $status1='14';
                     $status2='14';
                  
                }
                
             }
             else{
                   $status1='13';
                     $status2='14';
                  
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.status',[$status1,$status2])->where('forms.user_id','=',Auth::user()->id)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('forms.law_date','>=',$startdate)->where('forms.law_date','<=',$enddate)->orderBy('forms.law_date','asc');
        }
            
              
 
           
        return $forms->get();
     
         
       
    }
      public function jobs()
    {
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
    

    public function jobs1()
    {
        
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs1',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
      public function jobs2()
    {
       
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs2',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
      public function jobs3()
    {
       
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs3',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

    /*websiteleri ayrı */
    

      public function jobs1website()
    {
        
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs1website',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
      public function jobs2website()
    {
       
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs2website',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }
      public function jobs3website()
    {
       
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('developer.jobs3website',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    }

    /*websiteleri ayrı*/

    public function jobsCount() {
         $jobscount[] = [
                'jobs1' => Form::where('jobstatus','=',1)->count(),
                'jobs2' => Form::where('jobstatus','=',2)->count(),
                'jobs3' => Form::where('jobstatus','=',3)->count(),

           
            ];
            return $jobscount;
    }

      public function loadJobs(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
            $jobstatus='';
           
           $jobstatus2='';$jobstatus3='';
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
             if($request['jobstatus']==''||$request['jobstatus']==0){
                 $jobstatus=1;
                   $jobstatus2=2;
                   $jobstatus3=3;
             }
             if($request['jobstatus']==1){
                   $jobstatus=1;
                   $jobstatus2=1;
                   $jobstatus3=1;
             }
               if($request['jobstatus']==2){
                   $jobstatus=2;
                   $jobstatus2=2;
                   $jobstatus3=2;
             }
               if($request['jobstatus']==3){
                   $jobstatus=3;
                   $jobstatus2=3;
                   $jobstatus3=3;
             }

            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->whereIn('forms.jobstatus',[$jobstatus,$jobstatus2,$jobstatus3])->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.id','desc');
              
 
           
            return $forms->get();
     //   }
       
         
       
    }
    public function loadJobs1(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',1)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.form_date','asc');
              
 
           
            return $forms->get();
     //   }
       
         
       
    }
     public function loadJobs2(Request $request)
    {   
             $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',2)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.page_develop_date','desc');
              
 
           
            return $forms->get();
     //   }
         
       
    }
     public function loadJobs3(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',3)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.seo_develop_date','desc');
              
 
           
            return $forms->get();
     //   }
       
         
       
    }
    /*websiteler için ayrı*/
     public function loadJobs1website(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',1)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('forms.packages','like','%"package_4":{"selected":true%')->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.form_date','asc');
              
 
           
            return $forms->get();
     //   }
       
         
       
    }
     public function loadJobs2website(Request $request)
    {   
             $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',2)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('forms.packages','like','%"package_4":{"selected":true%')->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.page_develop_date','desc');
              
 
           
            return $forms->get();
     //   }
         
       
    }
     public function loadJobs3website(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
            $agent ='%%';
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }

            if( $request->request->get('agent')!='' && $request->request->get('agent')!=0 ){
                    $agent = $request->request->get('agent');
               
            }
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',3)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo);                            
                        }
                    )->where('data.title','like',$title)->where('forms.packages','like','%"package_4":{"selected":true%')->where('users.id','like','%'.$agent.'%')->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orderBy('forms.seo_develop_date','desc');
              
 
           
            return $forms->get();
     //   }
       
         
       
    }


    /*websiteler için ayrı*/


      public function loadCompletedWorks(Request $request)
    {   
        
            $taxNo = '%%';
            $phoneNo = '%%';
            $taxAdmin = '%%';
            $title = '%%';
            $forms ='%%';
           
            $startdate = '';
            $enddate = '';
           
             if($request['taxNo']!=''){  
                $taxNo = '%'.$request['taxNo'].'%';
              
  
            }
             if($request['taxAdmin']!=''){
                 $taxAdmin = '%'.$request['taxAdm'].'%';
             
            }
             if($request['phone']!=''){
                $phoneNo = '%'.$request['phone'].'%';
              
            }
             if($request['title']!=''){
                 $title = '%'.$request['title'].'%';
             
            }
 
             if($request['startdate']!=''){
                    $startdate = $request['startdate'];
              
            }
            else
                { $startdate='2000-01-01';}

             if($request['enddate']!=''){
                $enddate = $request['enddate'];

             }
             else{
                $enddate = date('Y-m-d');
             }
            
            $forms = Form::join('users','forms.user_id','=','users.id')->join('data','forms.data_id','=','data.id')->where('forms.jobstatus','=',3)->where('data.tax_no','like',$taxNo)->where('data.tax_admin','like',$taxAdmin)->where(    function ($q) use($phoneNo)
                        { 
                            $q->where('data.phone','like', $phoneNo);
                              $q->orWhere('data.mobile','like', $phoneNo);
                                $q->orWhere('data.mobile_alt','like', $phoneNo); 
                                                      
                        }
                    )->where('data.title','like',$title)->where('user_id',Auth::user()->id)->where('forms.form_date','>=',$startdate)->where('forms.form_date','<=',$enddate)->orWhere('active_agent_id',Auth::user()->id)->orderBy('forms.id','desc');
              
 
           
            return $forms->get();
     //   }
       
         
       
    }
     public function enforcementnotification(){
        $enforcementnotification = 0;
         if(Auth::user()->is_admin){
             $enforcementnotification = Form::where('status',Data::CANCELED)->where('sms_sent','=',1)->where('hold_on_to_date' ,'<=',date('Y-m-d'))->where('hold_on_to_time','<=', date('H:i:s'))->where('at_lawyer','!=',1)->count();

         }
         else
            $enforcementnotification = Form::where('status',Data::CANCELED)->where('user_id','=', Auth::user()->id)->where('sms_sent','=',1)->where('hold_on_to_date' ,'<=',date('Y-m-d'))->where('hold_on_to_time','<=', date('H:i:s'))->where('at_lawyer','!=',1)->count();
        return $enforcementnotification;
     }
     public function lawyer(){
       
      
        if(Auth::user()->status==1){
            $log = Logs::where('description','=',"Agent mola moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 

        if(Auth::user()->status==3){
            $log = Logs::where('description','=',"Agent toplantı moduna geçildi")->orderBy('id','desc')->first();
            $log->end_time = date('Y-m-d H:i:s');
            $log->save();
        } 
        $remainingTime1 =  DB::Table('logs')-> where('description', "Agent mola moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("01:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
       $remainingTime_meeting =  DB::Table('logs')-> where('description', "Agent toplantı moduna geçildi")->
                    where('updated_at', 'like','%'.date('Y-m-d').'%')->where('user_id',Auth::user()->id)->selectRaw('timediff("00:30:00",sec_to_time(sum(time_to_sec(end_time)-time_to_sec(start_time)))) as "remaining"')->get();
      
        $remaining1 = "1 saat 30 dakika 0 saniye";
        $remainingHour = 1;
        $remainingMinute =30;
        $remainingSecond=0;

        $remaining2 = ""; 
        $remainingHour2 = 0;
         $remainingMinute2 =30;
        $remainingSecond2=0;
       
         foreach ($remainingTime1 as $remainingTime) {
             
            $remaining1 = $remainingTime->remaining;
            $datetime = date('Y-i-m '.$remaining1.'');
            $dateparts = date_parse( $datetime );
            $remainingHour = $dateparts['hour'];
            $remainingMinute = $dateparts['minute'];
            $remainingSecond = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining1 >0){
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining1 == null){
                  $remaining1 = "Mola süreniz : 1 saat 30 dakika 0 saniye";
                   $remainingHour = 1;
                   $remainingMinute =30;
                    $remainingSecond=0;
            }
            elseif(strpos( $remaining1, '-')!==FALSE){
                $remaining1 = 'Mola süreniz (saat:dakika:saniye): '.$remainingTime->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining1 = 'Mola süreniz : '.$dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
        


         foreach ($remainingTime_meeting as $remainingTime2) {
             
            $remaining2 = $remainingTime2->remaining;
            $datetime = date('Y-i-m '.$remaining2.'');
            $dateparts = date_parse( $datetime );
            $remainingHour2 = $dateparts['hour'];
            $remainingMinute2 = $dateparts['minute'];
            $remainingSecond2 = $dateparts['second'];
           // $remaining1 = $dateparts['hour'] .' saat '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            if($remaining2 >0){
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }
            elseif($remaining2 == null){
                  $remaining2 = "Toplantı süreniz : 30 dakika 0 saniye";
                   $remainingHour2 = 0;
                   $remainingMinute2 =30;
                    $remainingSecond2=0;
            }
            elseif(strpos( $remaining2, '-')!==FALSE){
                $remaining2 = 'Toplantı süreniz (saat:dakika:saniye): '.$remainingTime2->remaining.' kadar aşıldı';
            }
            else 
            {
                 $remaining2 = 'Toplantı süreniz : '.$dateparts['minute'] .' dakika '.$dateparts['second'] .' saniye';
            }

             
            
        }  
          
        return view ('lawyer.lawyer',['remainingTime1' => $remaining1,'remainingHour'=>$remainingHour,'remainingMinute'=>$remainingMinute,'remainingSecond'=>$remainingSecond,'remainingTime2'=>$remaining2,'remainingHour2'=>$remainingHour2,'remainingMinute2'=>$remainingMinute2,'remainingSecond2'=>$remainingSecond2]);
    
     }
     public function saveForm(Request $request)
    {

        $form = Form::where('data_id',$request['id'])->first();

        if( !empty($request['packages']) ) {
            $form->packages = json_encode($request['packages']);
        }
        $form->note = $request['note'];
        $form->form_date = $request['form_date']; 
        if($form->sms_sent==1 && $form->status==6){
            $form->hold_on_to_date = $request['hold_on_to_date'];
            $form->hold_on_to_time = $request['hold_on_to_time'].':00';
            $form->form_message = $request['form_message'];
        }
        if($form->status==6 || $form->status==13 || $form->status==14){
            $form->law_date = date('Y-m-d');
        }
        if($form->status==13){
            $form->enforcement_note = $request['enforcement_note'];

        }
         if($form->status==14){
            $form->enforcement_note2 = $request['enforcement_note2'];
            
        }
          if($form->status==12){
            $form->enforcement_note3 = $request['enforcement_note3'];
            
        }
        $form->save();

        $data = Data::find($form->data_id);
        $data->website = $request['data']['website'];
        $data->note = $request['data']['note'];
        $data->site_title = $request['data']['site_title']; 
        $data->title = $request['data']['title'];
        $data->author = $request['data']['author'];
        $data->phone = $request['data']['phone'];
         if($request['data']['mobile']!=''){
            $data->mobile = $request['data']['mobile'];
         }
         else{
            $data->mobile = '';
         }
          if($request['data']['mobile_alt']!=''){
            $data->mobile_alt = $request['data']['mobile_alt'];
         }
         else{
            $data->mobile_alt = '';
         } 
        $data->fax = $request['data']['fax'];
        $data->email = $request['data']['email'];
        $data->address = $request['data']['address'];
        $data->postalcode = $request['data']['postalcode'];
        $data->category = $request['data']['category'];
        $data->keywords = $request['data']['keywords'];
         if($request['data']['tax_no']!=''){
            $data->tax_no = $request['data']['tax_no'];
         }
         else{
            $data->tax_no = '';
         }
         
        if($request['data']['tax_admin']!=''){
            $data->tax_admin = $request['data']['tax_admin'];
         }
         else{
            $data->tax_admin = '';
         }
        $data->save();

        return $form;
    }

 public function saveForm2(Request $request, Request $callback)
    {

       $form = Form::where('data_id',$request['id'])->first();

        if( !empty($request['packages']) ) {
            $form->packages = json_encode($request['packages']);
        }
        $form->note = $request['note'];
        $form->form_date = $request['form_date']; 


     

        $data = Data::find($form->data_id);
        $data->website = $request['data']['website'];
        $data->note = $request['data']['note'];
        $data->site_title = $request['data']['site_title']; 
        $data->title = $request['data']['title'];
        $data->author = $request['data']['author'];
        $data->phone = $request['data']['phone'];
         if($request['data']['mobile']!=''){
            $data->mobile = $request['data']['mobile'];
         }
         else{
            $data->mobile = '';
         }
          if($request['data']['mobile_alt']!=''){
            $data->mobile_alt = $request['data']['mobile_alt'];
         }
         else{
            $data->mobile_alt = '';
         } 
        $data->fax = $request['data']['fax'];
        $data->email = $request['data']['email'];
        $data->address = $request['data']['address'];
        $data->postalcode = $request['data']['postalcode'];
        $data->category = $request['data']['category'];
        $data->keywords = $request['data']['keywords'];
         if($request['data']['tax_no']!=''){
            $data->tax_no = $request['data']['tax_no'];
         }
         else{
            $data->tax_no = '';
         }
         
        if($request['data']['tax_admin']!=''){
            $data->tax_admin = $request['data']['tax_admin'];
         }
         else{
            $data->tax_admin = '';
         }
         if($callback){
                $data->notified=2;

            $form->notified_date=$request['data']['callback_date'] . ' ' . $request['data']['callback_time'] . ':00';
            $form->notified_message = $request['data']['callback_message'] == '' ? 'Mesaj yok.' : $request['data']['callback_message'];

            $data->notified_date=$request['data']['callback_date'] . ' ' . $request['data']['callback_time'] . ':00';
            $data->notified_message = $request['data']['callback_message'] == '' ? 'Mesaj yok.' : $request['data']['callback_message'];
           $form->notified=2;
         }
            else{
                   $data->notified=1; 
            $form->notified_message ='';
          
            $data->notified_message = '';
             $form->notified=1;
            } 
          
          

        
        $data->save();
        $form->save();
        return $form;
    }
    

     public function confirmForm1(Request $request)
    {
        $form = Form::where('data_id',$request['id'])->first();
        $form->jobstatus = 2;
        $form->page_develop_date = date('Y-m-d').' '.date('H:i:s');
        $form->page_developer_id=Auth::user()->id;
        $form->selected_by_developer=0;
        $form->save();
        return $form;
    }

      public function confirmForm2(Request $request)
    {
        $form = Form::where('data_id',$request['id'])->first();
        $form->jobstatus = 3;
        $form->seo_developer_id=Auth::user()->id;
        $form->seo_develop_date = date('Y-m-d').' '.date('H:i:s');
        $form->notified=0;
        $form->selected_by_developer=0;
        $form->save();
        $data = Data::where('id',$request['id'])->first();
        $data->notified=0;
       
        $data->save();
        return $form;
    }
      public function confirmForm3(Request $request)
    {
        $data = Data::where('id',$request['id'])->first();
        $form = Form::where('data_id',$request['id'])->first();
        $form->notified = 1; 
        $data->notified =1;
       
        $data->save();
        $form->save();
        return $form;
    }
     public function devret(Request $request)
    {
         
        $form = Form::where('data_id',$request['formid'])->first();
        $form->active_agent_id = $request['agent2'];
        $form->save();
        return $form;
    }
       public function selectForm1(Request $request)
    {
        $form = Form::where('data_id',$request['id'])->first();
        $form->selected_by_developer = 1;
          $form->page_developer_id=Auth::user()->id;
        $form->save();
        return $form;
    }
        public function selectForm2(Request $request)
    {
        $form = Form::where('data_id',$request['id'])->first();
        $form->selected_by_developer = 1;
        $form->seo_developer_id=Auth::user()->id;
        $form->save();
        return $form;
    }
        public function unselectForm1(Request $request)
    {
        $form = Form::where('data_id',$request['id'])->first();
        $form->selected_by_developer = 0;
        $form->page_developer_id=0;
        $form->save();
        return $form;
    }
        public function unselectForm2(Request $request)
    {
        $form = Form::where('data_id',$request['id'])->first();
        $form->selected_by_developer = 0;
        $form->seo_developer_id=0;
        $form->save();
        return $form;
    }
    public function developerInfo(){
         return [
            'developer' =>  [
               
                'id' => User::where('id', Auth::user()->id)->value('id')
            ]
        ];
    }

}
