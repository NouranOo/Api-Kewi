<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
 

class LikeUserProfile extends Model  
{
     protected $table="likeuserprofile";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Liker_id', 'Liked_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public function Liker(){
              return $this->belongsTo('App\Models\User','Liker_id');
    }
    public function Likked(){
         return $this->belongsTo('App\Models\User','Liked_id');
    }

    public function myLiked(){
        return $this->belongsTo('App\Models\User','Liked_id');
   }
   
}