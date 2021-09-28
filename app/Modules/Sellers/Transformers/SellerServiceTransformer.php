<?php


namespace App\Modules\Sellers\Transformers;

use App\Modules\Cart\Transformers\CartItemTransformer;
use App\Modules\Common\Transformers\CategoryTransformer;
use App\Modules\Products\Transformers\ProductTransformer;
use App\Modules\RateReview\Services\StoreRatingService;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Orders\Services\OrderService;
use Auth;

class SellerServiceTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {

        $rating_data = StoreRatingService::getStoreAverageRating($this->id);

        $userOrderCount = OrderService::usersDeliveredOrdersInStoreCount(Auth::user()->id, $this->id);

        return array_merge(
            [
                'seller_id'          => $this->brand_id,
                'store_id'           => $this->id,
                'name'               => empty($this->description->title) ? '' : $this->description->title,
                'description'        => empty($this->description->description) ? '' : $this->description->description,
                'address'            => empty($this->description->address) ? '' : $this->description->address,
                'address'            => empty($this->description->address) ? '' : $this->description->address,
                'logo'               => sc_image_cdn_get_path($this->logo),
                'liked_by_auth_user' => $this->is_user_like,
                'rank'               => $this->rank,
                'reviewer_count'     => 0,
                'rank'               => 0,
                'currency'           => $this->currency->code ?? 'AED',
                'average_rating'     => $rating_data['average_rating'],
                'rating_count'       => $rating_data['rating_count'],
                'can_rate_seller'   => ($userOrderCount) ? true : false

            ],
        );
    }
}
