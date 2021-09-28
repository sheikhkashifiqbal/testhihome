<?php
namespace App\Modules\Sellers\Services;

use App\Modules\Sellers\Models\Store;
use App\Modules\Sellers\Transformers\SellerServiceTransformer;

class StoreService
{

  public static function getStoreDetails($store_id, $lang='en')
  {
    return Store::where('id', $store_id)
                  ->with(['singleDescription' => function($query) use ($lang){
                    $query->where('lang', $lang);
                  }])
                  ->first();
  }//getStoreDetails

  public static function getAllStores($sellers=[], $lang='end')
  {
    $stores = Store::where('status', 1);
    if(!empty($sellers)){
      $stores = $stores->whereIn('id', $sellers);
    }
    $stores = $stores->with(['singleDescription' => function($query) use ($lang){
                    $query->where('lang', $lang);
                  }])
              ->get();

    return SellerServiceTransformer::collection($stores);
  }//getAllActiveStores


}
