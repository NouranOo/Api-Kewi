<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;


class Report extends Model
{

     protected $table="reports";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Reporter_id', 'Reported_id','Reason','Type','Model','TargetId'
    ];

    protected $casts = [
    'User_id'=>'int'
    ];
     public function Reported(){
      return $this->belongsTo('App\User','reported_id');
    }


      public function Reporter(){
      return $this->belongsTo('App\User','reporter_id');
    }


}
