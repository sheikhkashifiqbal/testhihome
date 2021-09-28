<?php

namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Services\ShopUserServices;

class SellerProductListTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
      $user = ShopUserServices::getShopUserDetails($this->user_id);
      return [
        'product_id'  => $this->product_id,
        'user_id'     => $this->user_id,
        'rate'        => $this->rate,
        'order_id'    => $this->order_id,
        'customer'  => [
          'first_name' => $user->first_name,
          'last_name' => $user->last_name,
          'email' => $user->email,
          'profile_picture' => sc_image_get_path($user->profile_picture),
        ]
      ];
    }
}
