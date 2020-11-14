<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $table = "posts";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'Post', 'User_id','Photo','Likes','UnLikes','privacy','IsAnonymous','Asked_id','Video'
    ];
    protected $casts = [
    'Likes' => 'int',
    'comments_count'=>'int',
    'User_id'=>'int'
    

    ];

    public function Comments()
    {
        return $this->hasMany('App\Models\Comment', 'Post_id');
    }
    public function Owner()
    {
        return $this->belongsTo('App\Models\User', 'User_id');
    }
     public function User()
    {
        return $this->belongsTo('App\Models\User', 'User_id');
    }
    public function getDatteAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }
    public function postLikes()
    {
      return $this->hasMany('App\Models\Post_like');
    }
    public function AskedUser(){
        return $this->belongsTo('App\Models\User', 'Asked_id');

    }
    
     public function SharedPosts()
    {
        return $this->hasMany('App\Models\Shared_Post','Post_id','id');
    }


}
