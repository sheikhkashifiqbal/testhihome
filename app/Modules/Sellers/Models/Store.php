<?php
#app/Models/Store.php
namespace App\Modules\Sellers\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Common\Models\Currency;
use App\Modules\Common\Models\ShopCategory;
use App\Modules\Common\Models\SubLocation;
use App\Modules\Common\Models\Location;
use App\Modules\Products\Models\ShopProduct;
use App\Modules\Sellers\Models\StoreBannerImage;
use Illuminate\Support\Facades\Auth;

class Store extends MyBaseApiModel
{
    public $timestamps = false;
    public $table = 'admin_store';
    protected $guarded = [];
    protected static $getAll = null;
    protected $appends = [
        'is_user_like',
    ];

    public function cartItems() {
        return $this->hasMany(CartItem::class, 'store_id');
    }

    public function descriptions() {
        return $this->hasMany(StoreDescription::class, 'config_id', 'id');
    }

    public function description() {
        $lang = request()->header('lang') ?: 'en';
        return $this->hasOne(StoreDescription::class, 'config_id', 'id')->where('lang', $lang);
    }
    public function brand()
    {
        return $this->belongsTo(ShopBrand::class, 'brand_id', 'id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }
    public function categories()
    {
        return $this->belongsToMany(ShopCategory::class, 'admin_store_shop_category', 'store_id', 'category_id');
    }
    public function featureProduct()
    {
        return $this->hasOne(ShopProduct::class, 'store_id', 'id')->where('status', 1);
    }

    public function banners()
    {
        return $this->hasMany(StoreBannerImage::class, 'store_id', 'id');
    }

    public function products()
    {
        return $this->hasOne(ShopProduct::class, 'store_id', 'id')
            ->where('status', 1);
    }

    public function subLocations()
    {
        return $this->belongsToMany(SubLocation::class, 'stores_sub_locations', 'store_id', 'sub_location_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'emirates_id', 'id');
    }

    public static function getData($id = 1)
    {
        if (self::$getAll == null) {
            self::$getAll = self::with('descriptions')->find($id);
        }
        return self::$getAll;
    }

    public function likes() {
        return $this->hasMany(StoreLike::class, 'store_id', 'id');
    }

    public function user_likes() {
        return $this->hasMany(StoreLike::class, 'store_id', 'id')
                    ->where('user_id', Auth::user()->id);
    }

    public function rates() {
        return $this->hasMany(StoreRate::class, 'store_id', 'id');
    }

    public function user_rates() {
        return $this->hasMany(StoreRate::class, 'store_id', 'id')
                    ->where('user_id', Auth::user()->id);
    }

    public function getIsUserLikeAttribute() {
        return $this->user_likes->count() ? true : false;
    }

    public static function getStores($brand_id = 1) {

        $stores = self::with('descriptions')->where('brand_id', $brand_id)->orderBy('sort')->get();
        $list   = [];

        foreach ($stores as $store) {
            $descriptions     = $store->descriptions->keyBy('lang')[sc_get_locale()];
            $list[$store->id] = $descriptions->title;
        }


        return $list;
    }
    //Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'asc')
    {
        $sortBy = $sortBy ?? 'sort';
        return $query->orderBy($sortBy, $sortOrder);
    }
    /**
     * Scope a query to only include active branches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $query->where('site_status', 1);
    }

    public function singleDescription() {
        return $this->hasOne(StoreDescription::class, 'config_id', 'id');
    }

    public function scopeApprovedAndVisible($query)
    {
        return $query->where('status', 1)->where('approval', 1)->where('accept_orders', 1);
    }

}
