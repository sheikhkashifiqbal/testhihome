<?php
#app/Models/ShopUserAddress.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopUserAddress extends Model {
    public $table = 'shop_user_address';
    public $timestamps = false;
    protected $fillable = ['tag,address,lat,long,landmark,user_id'];

    public function user() {
        return $this->belongsTo( ShopUser::class, 'user_id', 'id' );
    }

}
