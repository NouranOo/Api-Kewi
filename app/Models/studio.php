<?php

namespace App\Models;

  use Illuminate\Database\Eloquent\Model;
 
use Carbon\Carbon;
 

class studio extends Model  
{
      protected $table="studios";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'User_id','Photo','Date'
    ];
    protected $casts = [
    'User_id'=>'int'
    ];




}
