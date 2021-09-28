<?php


namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Services\ShopUserServices;
use App\Modules\RateReview\Transformers\StoreRateTransformer;

class StoreRateWithCustomerTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request=[]) {

        $user = ShopUserServices::getShopUserDetails($this->user_id);
        $rating = new StoreRateTransformer($this);
        //dd($rating->toArray());
        return array_merge(
          $rating->toArray(),
          [
            'customer'  => [
              'first_name' => $user->first_name,
              'last_name' => $user->last_name,
              'email' => $user->email,
              'profile_picture' => sc_image_get_path($user->profile_picture),
            ]
          ]
        );
    }
}
