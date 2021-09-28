<?php
#app/Models/ShopBrand.php
namespace App\Models;

use App\Models\ShopProduct;
use Illuminate\Database\Eloquent\Model;

class ShopBrand extends Model
{
    public $timestamps = false;
    public $table = 'shop_brand';
    protected $guarded = [];
    private static $getList = null;
    protected static $listBrand = null;

    public static function getList()
    {
        if (self::$getList == null) {
            self::$getList = self::get()->keyBy('id');
        }
        return self::$getList;
    }
    public static function getListBrand()
    {
        if (!self::$listBrand) {
            self::$listBrand = self::pluck('name', 'id')->all();
        }
        return self::$listBrand;
    }

    public function products() //no need for the old s-cart
    {
        return $this->hasMany(ShopProduct::class, 'brand_id', 'id');
    }
    public function categories()
    {
        return $this->hasMany(ShopCategory::class, 'brand_id', 'id');
    }
    public function branches()
    {
        return $this->hasMany(AdminStore::class, 'brand_id', 'id');
    }

    public function getBrandsList()
    {
        return self::where('status', 1)->orderBy('id', 'desc')->orderBy('sort', 'desc')->get();
    }

    public function getBrands($limit = null, $opt = null, $sortBy = null, $sortOrder = 'asc')
    {
        $query = $this->where('status', 1);
        $query = $query->sort($sortBy, $sortOrder);
        if (!(int) $limit) {
            return $query->get();
        } else
        if ($opt == 'paginate') {
            return $query->paginate((int) $limit);
        } else
        if ($opt == 'random') {
            return $query->inRandomOrder()->limit($limit)->get();
        } else {
            return $query->limit($limit)->get();
        }
    }

    public function getProductsToBrand($id, $limit = null, $opt = null, $sortBy = null, $sortOrder = 'asc')
    {
        $query = (new ShopProduct)->where('status', 1)->where('brand_id', $id);

        //Hidden product out of stock
        if (empty(sc_config('product_display_out_of_stock'))) {
            $query = $query->where('stock', '>', 0);
        }
        $query = $query->sort($sortBy, $sortOrder);
        if (!(int) $limit) {
            return $query->get();
        } else
        if ($opt == 'paginate') {
            return $query->paginate((int) $limit);
        } else
        if ($opt == 'random') {
            return $query->inRandomOrder()->limit($limit)->get();
        } else {
            return $query->limit($limit)->get();
        }
    }

    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($brand) {
        });
    }

    /**
     * [getUrl description]
     * @return [type] [description]
     */
    public function getUrl()
    {
        return route('brand', ['alias' => $this->alias]);
    }

    /*
Get thumb
 */
    public function getThumb()
    {
        return sc_image_get_path_thumb($this->image);
    }

    /*
Get image
 */
    public function getImage()
    {
        return sc_image_get_path($this->image);
    }

    //Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'asc')
    {
        $sortBy = $sortBy ?? 'sort';
        return $query->orderBy($sortBy, $sortOrder);
    }
    /**
     * Scope a query to only include active brands.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $query->where('status', 1);
    }
}
