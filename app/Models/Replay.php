<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
 

class Replay extends Model  
{
   protected $table="replay";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Replay', 'Comment_id','User_id'
    ];
     protected $casts = [
    'Likes' => 'int',
      'User_id'=>'int'

    ];


    // public function Comment(){
    //     return $this->belongsTo('App\Model\Comment','Comment_id');

    // }
     public function Comment(){
       return $this->belongsTo('App\Models\Comment','Comment_id');

     }  
     public function User(){
      return $this->belongsTo('App\Models\User','User_id');
  }

       public function Owner(){
       return $this->belongsTo('App\Models\User','User_id');

     }
   
}
