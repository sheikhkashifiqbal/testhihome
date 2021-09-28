<?php


namespace App\Modules\Sellers\Transformers;

use App\Modules\Cart\Transformers\CartItemTransformer;
use App\Modules\Common\Transformers\CategoryTransformer;
use App\Modules\Products\Transformers\ProductTransformer;
use App\Modules\RateReview\Services\StoreRatingService;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerSearchTransformer extends JsonResource
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

        return array_merge(
            [
                'seller_id'      => $this->brand_id,
                'store_id'       => $this->id,
                'name'           => empty($this->description->title) ? '' : $this->description->title,
                'description'    => empty($this->description->description) ? '' : $this->description->description,
                'logo'           => sc_image_cdn_get_path($this->logo),
                'feature_images' => $this->when($this->whenLoaded('banners'), $this->featureImages()),
                'average_rating'     => $rating_data['average_rating'],
                'rating_count'       => $rating_data['rating_count']
            ],
            $relations_loaded
        );
    }

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
}
