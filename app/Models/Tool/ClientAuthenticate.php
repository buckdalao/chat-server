<?php

namespace App\Models\Tool;

use Illuminate\Database\Eloquent\Model;

class ClientAuthenticate extends Model
{
    protected $primaryKey = 'id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'id', 'token', 'expire_time', 'status'
    ];

    protected $table = 'client_authenticate';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];
}
