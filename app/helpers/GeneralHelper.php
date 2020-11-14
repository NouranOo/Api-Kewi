<?php
namespace App\Helpers;

 
use App\Models\User;
use App\Models\Notfication;
use Carbon\Carbon;
class GeneralHelper
{
    protected static $currentUser;
   

    public static function SetCurrentUser($apitoken)
    {
        self::$currentUser = User::where('ApiToken', $apitoken)->first();
         self::$currentUser->last_active= Carbon::parse(Carbon::now())->diffForHumans() ;
        self::$currentUser->save();

    }
   
    public static function getcurrentUser()
    {
        self::$currentUser->last_active= Carbon::parse(Carbon::now())->diffForHumans() ;
                self::$currentUser->save();

        return self::$currentUser;
    }
    public static  function SetNotfication($title,$body,$model,$notify_from,$notify_to,$notify_target,$type,$Anoynoumes=0)
    {
        $Notfiy = new Notfication();
        $Notfiy->Title=$title;
        $Notfiy->body=$body;
        $Notfiy->User_id=$notify_to;
        $Notfiy->Model=$model;
        $Notfiy->notify_from=$notify_from;
        $Notfiy->notify_target_id=$notify_target;
        $Notfiy->Type=$type;
        $Notfiy->Anoynoumes=$Anoynoumes;
        $Notfiy->seen_at="";
        $Notfiy->save();         
    }
    public static function verifyEmail($user){

        require '../vendor/autoload.php';
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("hossamyahia1017@gmail.com", "Kewi App");
        $email->setSubject("Welcome to Kuwi Registerarion");
        $email->addTo($user->Email,$user->UserName);
        $email->addContent("text/plain", " Let's Verify Your Email");
        
         
        $email->addContent(
            "text/html",  view('mail')->with('user',$user)->render()
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
      
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
            }
    
 public static function RecoveryEmail($user){

        require '../vendor/autoload.php';
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("hossamyahia1017@gmail.com", "Kewi App");
        $email->setSubject("Welcome to Kuwi Registerarion");
        $email->addTo($user->Email,$user->UserName);
        $email->addContent("text/plain", " Let's Verify Your Email");
        
         
        $email->addContent(
            "text/html",  view('mailRecovery')->with('user',$user)->render()
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
      
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
            }
}
