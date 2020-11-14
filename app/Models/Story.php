<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{

    protected $table = "stories";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'text','photo','likes','seen'
    ];

    public function owner()
    {
        return $this->hasMany('App\Models\User', 'id','User_id');
    }
    public function watcher(){
        return $this->hasMany('App\Models\Story_seen', 'User_id','User_id') ;
    }
    
    public function getDatteAttribute($value)
{
    return Carbon::parse($value)->diffForHumans();
}
}
