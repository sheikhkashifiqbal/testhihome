<?php


namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Services\ShopUserServices;
use App\Modules\Sellers\Services\StoreService;

class StoreRateTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request = []) {
        return [
            'id'        => $this->id,
            'user_id'   => $this->user_id,
            'store_id'  => $this->store_id,
            'rate'      => $this->rate,
            'review'    => $this->review,
            'status'    => $this->status,
            'status_title'    => trans('rating.status.'.$this->status)
        ];
    }
}
