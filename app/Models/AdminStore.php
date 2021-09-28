<?php

#app/Models/AdminStore.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;

class AdminStore extends Model {

    public $timestamps = false;
    public $table = 'admin_store';
    protected $guarded = [];
    protected static $getAll = null;
    private static $getList = null;
    protected static $listBranch = null;

    public function descriptions() {
        return $this->hasMany(AdminStoreDescription::class, 'config_id', 'id');
    }

    public function products() {
        return $this->hasMany(ShopProduct::class, 'store_id', 'id')->with('details');
    }

    public function banners()
    {
        return $this->hasMany(StoreBannerImage::class, 'store_id', 'id');
    }

    public function orders() {
        return $this->hasMany(ShopOrder::class, 'store_id', 'id');
    }

    public function brand() {
        return $this->belongsTo(ShopBrand::class, 'brand_id', 'id');
    }

    public function categories() {
        return $this->belongsToMany(ShopCategory::class, 'admin_store_shop_category', 'store_id', 'category_id');
    }

    public function description() {
        $lang = request('lang') ?: 'en';
        return $this->hasOne(AdminStoreDescription::class, 'config_id', 'id')->where('lang', $lang);
    }

    public function user() {

        return $this->hasOne(ShopUser::class, 'store_id', 'id');
    }

    public function news() {
        return $this->belongsToMany(ShopNews::class, ShopNewsStore::class, 'store_id', 'news_id');
    }

    public function pages() {
        return $this->belongsToMany(ShopPage::class, ShopPageStore::class, 'store_id', 'page_id');
    }

    protected static function boot() {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($store) {
            //Store id 1 is default
            if ($store->id == 1) {
                return false;
            }
            //Delete store descrition
            $store->descriptions()->delete();
            $store->products()->delete();
            $store->categories()->delete();
            $store->user()->delete();
            //$store->banners()->delete();
            //$store->news()->delete();
            //$store->pages()->delete();
            AdminConfig::where('store_id', $store->id)->delete();
        });
    }

    /**
     * [getAll description]
     *
     * @return  [type]  [return description]
     */
    public static function getListAll() {
        /* if (sc_config_global('cache_status') && sc_config_global('cache_store')) {
          if (!Cache::has('cache_store')) {
          if (self::$getAll === null) {
          self::$getAll = self::with('descriptions')
          ->get()
          ->keyBy('id');
          }
          Cache::put('cache_store', self::$getAll, $seconds = sc_config_global('cache_time')?:600);
          }
          return Cache::get('cache_store');
          } else { */

        /* if (self::$getList == null) {
          self::$getList = self::get()->keyBy('id');
          }
          return self::$getList;
         */
        if (self::$getAll == null) {
            self::$getAll = self::with('descriptions')
                    ->get()
                    ->keyBy('id');
        }


        return self::$getAll;
        //}
    }

    /**
     * Get all template used
     *
     * @return  [type]  [return description]
     */
    public static function getAllTemplateUsed() {
        return self::pluck('template')->all();
    }

    /**
     * Get all domain and id store
     *
     * @return  [array]  [return description]
     */
    public static function getDomain() {
        return self::where('status', 1)
                        ->pluck('domain', 'id')
                        ->all();
    }

    public static function getList() {
        if (self::$getList == null) {
            self::$getList = self::get()->keyBy('id');
        }
        return self::$getList;
    }

    public static function getListBranch() {
        if (!self::$listBranch) {
            self::$listBranch = self::pluck('title', 'id')->all();
        }
        return self::$listBranch;
    }

    public static function getData($id = 1) {
        if (self::$getAll == null) {
            self::$getAll = self::with('descriptions')->find($id);
        }
        return self::$getAll;
    }

    public static function getStores($brand_id = null) {

        $stores = self::with('descriptions');
        if ($brand_id)
            $stores = $stores->where('brand_id', $brand_id);

        $stores = $stores->orderBy('sort')->get();
        $list = [];

        foreach ($stores as $store) {
            $descriptions = $store->descriptions->keyBy('lang')[sc_get_locale()];
            $list[$store->id] = $descriptions->title;
        }



        return $list;
    }

    public static function getStoresHtml($brand_id = null, $html = 1) {
        $stores = self::with('descriptions');
        if ($brand_id)
            $stores = $stores->where('brand_id', $brand_id);

        $stores = $stores->orderBy('sort')->get();
        $list = [];
        $optstore = '';
        $optcat = '';
        if ($html) :
            $optstore .= '<option  value="0">Select Seller</options>';
            foreach ($stores as $store) {
                $descriptions = $store->descriptions->keyBy('lang')[sc_get_locale()];
                $optstore .= '<option value="'.$store->id.'">' . $descriptions->title . '</options>';
            }


//dd($optstore);
            return $optstore;
        else :
            return $stores;
        endif;

    }

    public static function getStoresWithCategories($brand_id = null, $html = 1) {

        $stores = self::with('descriptions')->with('categories');
        if ($brand_id)
            $stores = $stores->where('brand_id', $brand_id);

        $stores = $stores->orderBy('sort')->get();
        $list = [];
        $optstore = '';
        $optcat = '';
        if ($html) :
            foreach ($stores as $store) {
                $descriptions = $store->descriptions->keyBy('lang')[sc_get_locale()];
                $optstore .= '<optgroup label="' . $descriptions->title . '">';
                foreach ($store->categories as $cat) {
                    $optcat .= '<option value="' . $cat->id . '">' . $cat->descriptions->keyBy('lang')[sc_get_locale()]['name'] . '</option>';
                }
                $optstore .= $optcat . '</optgroup>';
                $optcat = '';




                /* $list[$store->id]['parent'] = $descriptions->title;
                  $list[$store->id]['categories'] = $store->categories; */
            }



            return $optstore;
        else :
            return $stores;
        endif;
    }

    //Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'asc') {
        $sortBy = $sortBy ?? 'sort';
        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Scope a query to only include active branches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        $query->where('site_status', 1);
    }

    /*
      Get thumb
     */

    public function getThumb() {
        return sc_image_get_path_thumb($this->logo);
    }

    /*
      Get image
     */

    public function getImage() {
        return sc_image_get_path($this->logo);
    }

    public function scopeWithApproval($query, $approval){
        return $query->where('admin_store.approval', $approval);
    }

    public function scopeWithStatus($query, $status){
        return $query->where('admin_store.status', $status);
    }

    public function scopeWithOrderBy($query, $sort_order){
        $field = explode('__', $sort_order)[0];
        $sort_field = explode('__', $sort_order)[1];
        return $query->orderBy($field, $sort_field);
        //return $query->where('admin_store.status', $status);
    }

    public function scopeWithRelationships($query){
        return $query->leftJoin('admin_store_description', 'admin_store_description.config_id', 'admin_store.id')
                    ->leftJoin('most_product_ordered', 'most_product_ordered.store_id', 'admin_store.id')
                    ->leftjoin('shop_product', 'most_product_ordered.product_id', 'shop_product.id')
                    ->leftjoin('shop_product_description', function($join){
                      $join->on('shop_product_description.product_id', '=', 'shop_product.id');
                       $join->where('shop_product_description.lang','=', sc_get_locale());
                    })

                    ->select('admin_store_description.*', 'admin_store_description.title as store_name', 'shop_product_description.name as product_name', 'admin_store.*', 'most_product_ordered.*', 'shop_product.image', 'shop_product.alias')
                    ->where('admin_store_description.lang', sc_get_locale());
    }

    public function scopeWithSearchKeywords($query, $keyword){
        return $query->whereRaw('(admin_store.id = ' . (int) $keyword . ' OR admin_store_description.title like "%' . $keyword . '%" )');
    }


}
