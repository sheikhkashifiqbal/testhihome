<?php

namespace App\Modules\Offers\Transformers;

use Illuminate\Http\Resources\Json\Resource;
use App\Modules\Offers\Transformers\OffersListResource;

use App\Modules\Sellers\Services\StoreService;

class OfferWithSellerResource extends Resource
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
          $this->getSellersArray($this)
        );
    }//toArray

    private function getSellersArray($offer)
    {
      $sellers = [];
      if($offer->apply_to == config('offers.contants.OFFER_APPLY_TO.ALL')){
        $sellers = StoreService::getAllStores([], 'en');
      }else{
        $offer_sellers = $offer->sellers->pluck('seller_id')->toArray();
        $sellers = StoreService::getAllStores($offer_sellers,'en');
      }
      //StoreService::getAllActiveStores('en')
      return [
        'sellers' => $sellers
      ];
    }



}
