<?php

namespace App\Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];

    public function scopeGetSellerNotifications($query, $store_id)
    {
      return $query->where('notifiable_type', config('notification.constants.NOTIFIABLE_TYPES.SELLER'))
                   ->where('notifiable_id', $store_id);
    }

}
