<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;



class Notfication extends Model  
{
 
     protected $table="notfications";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'User_id', 'Seen','Title','body',
        'key','seen_at','Model',
        'notify_target_id','Type','Anoynoumes'
    ];
    protected $casts = [
    'User_id'=>'int'
    ];
    public function userFrom(){
      return $this->belongsTo('App\Models\User','notify_from');
    }
    

 
    
    

   
}
