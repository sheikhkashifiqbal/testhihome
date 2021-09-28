<?php


namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Sellers\Services\StoreService;
use App\Modules\RateReview\Transformers\StoreRateTransformer;

class StoreRateWithStoreTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request=[]) {

        $lang = 'en';
        $store = StoreService::getStoreDetails($this->store_id, $lang);
        $rating = new StoreRateTransformer($this);
        //dd($rating->toArray());
        return array_merge(
          $rating->toArray(),
          [
            'seller'   => [
              'store_id' => $store->id,
              'name'     => $store->singleDescription->title,
              'email'    => $store->legal_business_email,
              'logo'     => sc_image_get_path($store->logo),
            ]
          ]
        );
    }
}
