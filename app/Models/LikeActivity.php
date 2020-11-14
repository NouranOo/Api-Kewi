<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class LikeActivity extends Model
{

    protected $table = "likesactivites";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Model','Target_id','Body','Date'
    ];
    protected $casts = [
    'User_id'=>'int'
    ];
    public function User()
    {
        return $this->belongsTo('App\Models\User','User_id','id');
    }



    
     
 
}
