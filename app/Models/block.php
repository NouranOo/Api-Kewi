<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
 

class block extends Model  
{
     protected $table="blocks";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Blocker_id', 'Blocked_id',
    ];
        protected $casts = [
    'User_id'=>'int'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
     public function Blocker(){
          return $this->belongsTo('App\Models\User','Blocker_id');
     }
     public function BlockedUsers(){
          return $this->belongsTo('App\Models\User','Blocked_id');
     }
     public function myBlocked(){
          return $this->belongsTo('App\Models\User','Blocked_id');
     }
    

   
   
}