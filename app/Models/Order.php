<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
 {
    protected $fillable = ['products', 'totalPrice', 'userID', 'transactionID', 'name', 'address'];

    public function customer()
 {
        return $this->belongsTo( 'App\Models\ShopUser', 'user_id', 'id' );
    }

    public function orderStatus()
 {
        return $this->hasOne( ShopOrderStatus::class, 'id', 'status' );
    }

    public function address()
 {
        return $this->hasOne( ShopUserAddress::class, 'address_id', 'id' );
    }

    public function paymentStatus()
 {
        return $this->hasOne( ShopPaymentStatus::class, 'id', 'payment_status' );
    }

    public function history()
 {
        return $this->hasMany( ShopOrderHistory::class, 'order_id', 'id' );
    }

}
