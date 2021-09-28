<?php
#app/Models/ShopProductNotAvailableIn.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopProductNotAvailableIn extends Model
{
    public $timestamps = false;
    public $table = 'shop_product_not_available_in';
    protected $guarded = [];
    private static $getList = null;
    
}
