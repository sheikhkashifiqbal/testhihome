<?php
#app/Models/ShopUserAddress.php
namespace App\Modules\Common\Models;

use App\Modules\Auth\Models\ShopUser;
use Illuminate\Database\Eloquent\Model;

class ShopUserAddress extends Model
{
    public $table = 'shop_user_address';
    public $timestamps = false;
    protected $fillable = [
        'tag',
        'address',
        'lat',
        'long',
        'landmark',
        'name',
        'mobile',
        'email',
        'address_line_1',
        'address_line_2',
        'area',
        'emirate',
        'is_default',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(ShopUser::class, 'user_id', 'id');
    }

}
