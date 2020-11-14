<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Shared_Post extends Model
{

    protected $table = "shared_posts";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
     'User_id', 'Owner_id','Post_id',
    ];

     public function User()
    {
        return $this->belongsTo('App\Models\User','User_id','id');
    }
    public function Owner()
    {
        return $this->belongsTo('App\Models\User','Owner_id','id');
    }

    public function post()
    {
        return $this->belongsTo('App\Models\Post','Post_id','id');
    }



    
     
 
}
