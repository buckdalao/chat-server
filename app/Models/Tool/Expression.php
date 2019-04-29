<?php

namespace App\Models\Tool;

use Illuminate\Database\Eloquent\Model;

class Expression extends Model
{
    //
    protected $primaryKey = 'id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'id', 'phrase', 'type', 'common', 'path', 'is_delete'
    ];

    protected $table = 'expression';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

}
