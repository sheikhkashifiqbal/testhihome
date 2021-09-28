<?php

namespace App\Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['id', 'key', 'user_id'];

    public function items() {
        return $this->hasMany(CartItem::class, 'cart_id');
    }


}
