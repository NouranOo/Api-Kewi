<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Post_like extends Model
{

    protected $table = "post_likes";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Post_id',
    ];

    public function User()
    {
        return $this->belongsTo('App\Models\User','User_id','id');
    }

    public function post()
    {
        return $this->belongsTo('App\Models\Post','Post_id','id');
    }



    
     
 
}
