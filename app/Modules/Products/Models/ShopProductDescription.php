<?php
#app/Models/ShopProductDescription.php
namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class ShopProductDescription extends Model
{
    protected $primaryKey = ['lang', 'product_id'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
    public $table = 'shop_product_description';
}
