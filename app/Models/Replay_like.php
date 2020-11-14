<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Replay_like extends Model
{

    protected $table = "replay_likes";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Replay_id'
    ];
    public function User()
    {
        return $this->belongsTo('App\Models\User','User_id','id');
    }

    public function   Comment  ()
    {
        return $this->belongsTo('App\Models\Comment','Replay_id','id');
    }
    
     
 
}
