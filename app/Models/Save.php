<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Save extends Model
{

    protected $table = "saves";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Post_id',
    ];

    public function owner()
    {
        return $this->hasMany('App\Models\User', 'User_id');
    }
    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'Post_id');
    }
    public function getDatteAttribute($value)
{
    return Carbon::parse($value)->diffForHumans();
}
}
