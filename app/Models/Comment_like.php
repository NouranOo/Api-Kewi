<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Comment_like extends Model
{

    protected $table = "comment_likes";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Comment_id'
    ];
        protected $casts = [
    'User_id'=>'int'
    ];
    public function User()
    {
        return $this->belongsTo('App\Models\User','User_id','id');
    }

    public function Comment()
    {
        return $this->belongsTo('App\Models\Comment','Comment_id','id');
    }
    
     
 
}
