<?php
#app/Models/PaymentTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $primaryKey = 'transaction_id'; // or null

    public $incrementing = false;


    protected $keyType = 'string';

    protected $guarded = [];
    public $table = 'payment_transaction';
    public function user()
    {
        return $this->belongsTo(ShopUser::class, 'user_id', 'id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'transaction_id', 'transaction');
    }
}
/* 
{
   "transaction_id":"533042",
   "order_id":"1234567899",
   "response_code":"100",
   "customer_name":"Sen A R",
   "customer_email":"sen@mccollinsmedia.com",
   "transaction_amount":"10.000000",
   "transaction_currency":"AED",
   "customer_phone":"00971521377349",
   "last_4_digits":"1111",
   "first_4_digits":"4111",
   "card_brand":"Visa",
   "datetime":"07-07-2020 06:03:54 PM",
   "shipping_address":"Dubai",
   "shipping_city":"Dubai",
   "shipping_country":"ARE",
   "shipping_state":"Dubai",
   "shipping_postalcode":"123456",
   "amount":"10.00",
   "currency":"AED",
   "detail":"Approved",
   "transaction_response_code":"100",
   "authcode":"831000",
   "rrn":"5941342358636984304007",
   "tokenization":"1",
   "pt_customer_email":"sen@mccollinsmedia.com",
   "pt_customer_password":"XqK4fNPbSL",
   "pt_token":"AgaUc6h2xp0giUfWbCL8qIybMNYBZQmz"
}
{
   
   
   
   
   
   
   
   
   
   
   "phone_num":null,
   
   "email":"customer@email.com",
   
   "reference_id":"ref number",
   
   "invoice_id":"457336",
   "payment_type":"CREDIT CARD",
   
   
   "customer_full_name":"Muhsan Taher",
   "secure_sign":"4fd5d9a979942047cb3cd9a6874201a778684230",
   
}
*/