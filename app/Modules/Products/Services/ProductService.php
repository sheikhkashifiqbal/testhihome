<?php
namespace App\Modules\Products\Services;

use App\Modules\Products\Models\ShopProduct;
use App\Modules\Products\Transformers\ProductTransformer;

class ProductService
{

  public static function getProductsById($id, $lang = 'en')
  {
    return ShopProduct::where('id', $id)
                        ->with(['singleDescription' => function ($q) use ($lang) {
                            $q->where('lang', $lang);
                        }])
                      ->first();
  }

  public static function sellerHasFeaturedProduct($store_id)
  {
    return ShopProduct::where('store_id', $store_id)
                      ->where('is_feature', 1)
                      ->exists();
  }

  public static function getProductsOfSeller($product_ids=[], $store_id, $lang='en')
  {
    $products = ShopProduct::where('status', 1)->where('store_id', $store_id);
    if(!empty($product_ids)){
      $products = $products->whereIn('id', $product_ids);
    }
    $products = $products->with('productDescription')->get();

    return ProductTransformer::collection($products);
    //return SellerServiceTransformer::collection($stores);
  }//getAllActiveStores

  public static function getProductsByArrayOfIds($product_ids=[], $lang='en')
  {
    $products = ShopProduct::whereIn('id', $product_ids)->get();

    return $products->toArray();
  }//getProductsByArrayOfIds

  public static function getTransformProductById($id, $lang = 'en')
  {
    $product = ShopProduct::where('id', $id)
                        ->with('productDescription')
                      ->first();
    $tproduct = new ProductTransformer($product);
    return $tproduct;
  }

}
