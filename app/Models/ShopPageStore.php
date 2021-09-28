<?php
#app/Models/ShopPageStore.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPageStore extends Model
{
    protected $primaryKey = ['store_id', 'page_id'];
    public $incrementing  = false;
    protected $guarded    = [];
    public $timestamps    = false;
    public $table = 'shop_page_store';
    protected $connection = SC_CONNECTION;
}
