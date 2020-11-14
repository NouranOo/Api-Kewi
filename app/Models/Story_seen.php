<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Story_seen extends Model
{

    protected $table = "story_seens";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Story_id'
    ];

    public function watcher_user()
    {
        return $this->hasMany('App\Models\User', 'id','User_id');
    }
    
    public function getDatteAttribute($value)
{
    return Carbon::parse($value)->diffForHumans();
}
}
