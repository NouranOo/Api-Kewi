<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Interst extends Model
{

    protected $table = "intersts";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Name'
    ];
        protected $casts = [
    'User_id'=>'int'
    ];
 
    public function Users()
    {
        return $this->belongsToMany('App\Models\User','userIntersts', 'User_id' ,'Interst_id');
    }
    // public function getDatteAttribute($value)
    // {
    //     return Carbon::parse($value)->diffForHumans();
    // }
}
