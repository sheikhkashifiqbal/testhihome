<?php


namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\RateReview\Transformers\StoreRateTransformer;
use App\Modules\RateReview\Transformers\StoreRateWithCustomerTransformer;
use App\Modules\RateReview\Transformers\StoreRateWithStoreTransformer;

class StoreRateFullTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request = []) {
        $rating = new StoreRateTransformer($this);
        $customer = new StoreRateWithCustomerTransformer($this);
        $store = new StoreRateWithStoreTransformer($this);
        return array_merge($rating->toArray(), $customer->toArray(), $store->toArray());
    }
}
