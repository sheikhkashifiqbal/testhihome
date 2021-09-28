<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartDiscount extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public $incrementing = false;

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->hasOne(ShopProduct::class);
    }
}
