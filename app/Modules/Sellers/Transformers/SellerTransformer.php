<?php


namespace App\Modules\Sellers\Transformers;

use App\Modules\Cart\Transformers\CartItemTransformer;
use App\Modules\Common\Transformers\CategoryTransformer;
use App\Modules\Products\Transformers\ProductTransformer;
use App\Modules\RateReview\Services\StoreRatingService;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Orders\Services\OrderService;
use Auth;

class SellerTransformer extends JsonResource
{
    private $ignore_loaded_relations;

    public function __construct($resource, $ignore_loaded_relations = []) {
        parent::__construct($resource);
        $this->ignore_loaded_relations = is_array($ignore_loaded_relations) ? $ignore_loaded_relations : [];
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {
        $relations_loaded = [];

        if (!in_array('featureProduct', $this->ignore_loaded_relations)) {
            $relations_loaded['feature_product'] = new ProductTransformer($this->whenLoaded('featureProduct'));
        }
        if (!in_array('categories', $this->ignore_loaded_relations)) {
            $relations_loaded['categories'] = CategoryTransformer::collection($this->whenLoaded('categories'));
        }

        if (!in_array('cartItems', $this->ignore_loaded_relations)) {
            $relations_loaded['cart_items'] = CartItemTransformer::collection($this->whenLoaded('cartItems'));
        }

        $rating_data = StoreRatingService::getStoreAverageRating($this->id);
        $emirate = $this->location()->first();
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
                'feature_images'     => $this->when($this->whenLoaded('banners'), $this->featureImages()),
                'average_rating'     => $rating_data['average_rating'],
                'rating_count'       => $rating_data['rating_count'],
                'can_rate_seller'    => $this->customerCanRateSeller($this->id),
                'city'               => $emirate->name,

            ],
            $this->calcRates(),
            $relations_loaded/* ,
            $this->locations() */
        );
    }

    private function customerCanRateSeller($store_id){
      $user_id = Auth::user()->id;
      $userOrderCount = OrderService::usersDeliveredOrdersInStoreCount($user_id, $store_id);
      $ratingCount = StoreRatingService::checkIfUserRatedStore($user_id, $store_id);

      if( $userOrderCount > 0 && $ratingCount == 0 ){
        return true;
      }
      return false;
    }//customerCanRateSeller

    private function featureImages() {
        $images = [];

        if($this->banners()->count() > 0){
          foreach ($this->banners()->get() as $image) {
              $images[] = sc_image_cdn_get_path($image->image);
          }
        }else{
          if (isset($this->featureProduct->image)) {
              $images[] = sc_image_cdn_get_path($this->featureProduct->image);
          }

          if (isset($this->featureProduct->images)) {
              foreach ($this->featureProduct->images as $image) {
                  $images[] = sc_image_cdn_get_path($image->image);
              }
          }
        }

        return $images;
    }

    private function locations() {
        $locations     = [];
        $sub_locations = [];
        if (isset($this->brand->subLocations)) {
            foreach ($this->brand->subLocations as $sub_location) {
                $sub_locations[] = $sub_location->name;
                if (isset($sub_location->location->name)) {
                    $locations[] = $sub_location->location->name;
                }
            }
        }
        return [
            'locations'    => $locations,
            'sublocations' => $sub_locations,
        ];
    }

    private function calcRates() {
        $arr = [
            'reviewer_count' => (int)$this->rates_count,
            'rank'           => 0
        ];

        if (!empty($this->rates_count) && !empty($this->sum_rates)) {
            $arr['rank'] = (int)$this->sum_rates / (int)$this->rates_count;
        }

        return $arr;
    }
}
