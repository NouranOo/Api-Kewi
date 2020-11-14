<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
/* use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract; */

class User extends Model  
{
    use Notifiable;
     protected $table="users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Name','UserName', 'Email','Password','Fname','Lname','BirthDay','Phone','CountryCode',
        'Location','Gendre','VerifyCode','RecoveryCode','Token','ApiToken','FacebookId','Likes',
        'Token_verify','Verified','Photo','BackgroundPhoto','Describition' ,'Rate','Status','personal_Status','last_active','QuestionNotify'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
     protected $casts = [
    'Likes' => 'int',
    'Asked_id'=>'int'
    ];
    public function userrate()
    {
         return $this->hasMany('App\Models\UserRate','User_id');
    }

    public function Comments()
    {
     return $this->hasMany('App\Models\Comment','User_id');
    }
 
 
 public function posts(){
      return $this->hasMany('App\Models\Post','User_id');
 
    }
 
    public function Reports()
    {
     return $this->hasMany('App\Models\Report');
    }
  /*   public function Messages(){
        return $this->hasMany('App\Models\Message','User_id');

    } */

    // public function TokenVerify(){
    //     return $this->belongsTo('App\Model\TokenVerify');
    // }
      public function notifications(){
      return $this->hasMany('App\Models\Notfication','notify_from');
    }
    public function intersts(){
      return $this->belongsToMany('App\Models\Interst','userIntersts', 'User_id' ,'Interst_id');
    }
    public function postLikes()
    {
      return $this->hasMany('App\Models\Post_like');
    }


 public function getDatteAttribute($value)
{
    return Carbon::parse($value)->diffForHumans();
}
public function is_online(){
    if($this->last_active==null){
        $this->Online=false;
    }else{
      $to = Carbon::createFromFormat('Y-m-d H:s:i', Carbon::now());
$from = Carbon::createFromFormat('Y-m-d H:s:i', $this->last_active);
$diff_in_minutes = $to->diffInMinutes($from);
 
    if( $diff_in_minutes>5){
        $this->Online= true;
    }else{
       $this->Online= false;
    }
    
    }
    

}

    public function SharedPosts()
    {
        return $this->hasMany('App\Models\Shared_Post','User_id','id');
    }
    public function SharedComments()
    {
        return $this->hasMany('App\Models\Shared_Comment','User_id','id');
    }



}
