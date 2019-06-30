<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class UserNotifyBadge extends Model
{
    protected $table = 'user_notify_badge';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type', 'user_id', 'count'
    ];

    /**
     * 用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Chat\User', 'user_id', 'id');
    }
}
