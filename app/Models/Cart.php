<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['id', 'content', 'key', 'userID', 'brand_id', 'branch_id', 'cart_type'];
    public $incrementing = false;

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
    public function discounts()
    {
        return $this->hasMany(CartDiscount::class, 'cart_id');
    }
    // this is a recommended way to declare event handlers
    public static function boot() {
        parent::boot();

        static::deleting(function($cart) { // before delete() method call this
             $cart->discounts()->delete();
             // do the rest of the cleanup...
        });
    }
}
