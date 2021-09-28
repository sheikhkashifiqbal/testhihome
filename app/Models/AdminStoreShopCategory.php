<?php
#app/Models/ShopCategoryStore.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminStoreShopCategory extends Model
{
    
    public $incrementing  = false;
    protected $guarded    = [];
    public $timestamps    = false;
    public $table = 'admin_store_shop_category';
    protected $connection = SC_CONNECTION;
}
//admin_store_shop_category