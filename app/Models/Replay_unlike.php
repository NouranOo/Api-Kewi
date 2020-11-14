<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Replay_unlike extends Model
{

    protected $table = "Replay_unlikes";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Replay_id'
    ];

    
     
 
}
