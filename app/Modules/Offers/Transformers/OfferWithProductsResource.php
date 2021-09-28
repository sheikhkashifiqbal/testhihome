<?php

namespace App\Modules\Offers\Transformers;

use Illuminate\Http\Resources\Json\Resource;
use App\Modules\Offers\Transformers\OffersListResource;

use App\Modules\Products\Services\ProductService;

class OfferWithProductsResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $offer = new OffersListResource($this);
        return array_merge(
          $offer->toArray([]),
          $this->getProductsArray($this, $request->store_id)
        );
    }//toArray

    private function getProductsArray($offer, $store_id)
    {
      $products = [];
      if($offer->apply_to == config('offers.contants.OFFER_APPLY_TO.PRODUCTS')){
         $offer_products = $offer->products->pluck('product_id')->toArray();
         $products = ProductService::getProductsOfSeller($offer_products, $store_id);
      }else{
         $products = ProductService::getProductsOfSeller([], $store_id);
      }
      return [
        'products' => $products
      ];
    }//getProductsArray



}
