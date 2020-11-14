<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Comment_unlike extends Model
{

    protected $table = "comment_unlikes";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id',  'Comment_id'
    ];

    
     
 
}
