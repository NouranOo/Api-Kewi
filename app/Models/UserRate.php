<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;


class UserRate extends Model
{

     protected $table="userrates";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rate','User_id',
    ];



 public function User(){
         return $this->belongTo('App\Models\User','User_id');
     }


}
