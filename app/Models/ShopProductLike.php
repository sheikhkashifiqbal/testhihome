<?php
#app/Models/ShopProductLike.php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ShopProductLike extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'product_id'];
    public function product()
    {
        return $this->belongsTo(ShopProduct::class,'product_id','id');
    }

    public function user()
    {
        return $this->belongsTo(ShopUser::class,'user_id','id');
    }

}
