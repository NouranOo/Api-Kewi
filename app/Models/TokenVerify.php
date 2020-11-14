<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
 

class TokenVerify extends Model  
{
   protected $table="tokenverifies";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Token_verify','User_id'
    ];

    // public function User(){
    //     return $this->hasOne('App\Model\User');
    // }

     
}
