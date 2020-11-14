<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $table = "messages";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Message_From', 'Message_To', 'Message', 'Seen', 'Seen_at','MessageDelivery','type'
    ];
     protected $casts = [
    'User_id'=>'int',
    'type'=>'int'
    ];
    public function UserSent()
    {
        return $this->belongsTo('App\Models\User', 'Message_From');
    }
    public function UserRecived()
    {
        return $this->belongsTo('App\Models\User', 'Message_To');
    }

}
