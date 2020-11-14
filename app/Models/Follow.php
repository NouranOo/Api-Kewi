<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{

    protected $table = "follows";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'follower', 'following',
    ];
    protected $casts = [
    'User_id'=>'int'
    ];

    public function followers()
    {
        return $this->belongsTo('App\Models\User','follower');
    }
    public function followings()
    {
        return $this->belongsTo('App\Models\User','following');
    }
    public function getDatteAttribute($value)
{
    return Carbon::parse($value)->diffForHumans();
}
}
