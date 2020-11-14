<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
 

class Comment extends Model  
{
 
     protected $table="comments";
     protected $withCount = ['Replaies'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Comment', 'Post_id','User_id'
    ];
    protected $casts = [
    'Likes' => 'int',
    'replaies_count'=>'int',
    'User_id'=>'int'
    ];

    public function Replaies(){
       return  $this->hasMany('App\Models\Replay','Comment_id');
    }
    public function User(){
        return $this->belongsTo('App\Models\User','User_id');
    }
    public function post(){

      return $this->belongsTo('App\Models\Post','Post_id');

   }


    
   public function Owner(){
    return $this->belongsTo('App\Models\User','User_id');
   }
   
}
