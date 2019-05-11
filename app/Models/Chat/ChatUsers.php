<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatUsers extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id_1', 'user_id_2', 'status'
    ];

//    public function userOne()
//    {
//        return $this->belongsTo('App\User', 'user_id_1', 'id');
//    }
//
//    public function userTwo()
//    {
//        return $this->belongsTo('App\User', 'user_id_2', 'id');
//    }
}
