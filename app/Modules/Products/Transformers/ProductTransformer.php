<?php


namespace App\Modules\Products\Transformers;

use App\Modules\Sellers\Transformers\SellerTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Modules\RateReview\Services\ProductRatingService;

class ProductTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {
        $relations_loaded = [
            'seller'      => new SellerTransformer($this->whenLoaded('store')),
            'images_list' => ProductImageTransformer::collection($this->whenLoaded('images'))
        ];
        $rating = ProductRatingService::getProductAverageRating( $this->id);
        $categories = $this->categories()->first();
        $category_id = ($categories) ? $categories->id : 0;

        return array_merge(
            [
                'id'                 => $this->id,
                //                'seller_id'          => $this->brand_id,
                'store_id'           => $this->store_id,
                'main_image'         => isset($this->image) ? sc_image_cdn_get_path($this->image) : null,
                'images'             => $this->when($this->whenLoaded('images'), $this->images()),
                'price'              => $this->price,
                'serve_count'         => $this->serve_count,
                'discount_percentage'  => $this->discount_percentage,
                'discount_start_date'  => $this->discount_start_date,
                'discount_expiry_date' => $this->discount_expiry_date,
                'shares'             => $this->shares ?? 0,
                'comments'           => $this->comments ?? 0,
                'likes_count'        => $this->likes_count ?? 0,
                'liked_by_auth_user' => $this->is_user_like,
                'prepration_time'    => $this->prepration_time,
                'status'             => $this->status,
                'out_of_stock'       => $this->out_of_stock,
                'is_feature'         => $this->is_feature,
                'average_rating'     => $rating['average_rating'],
                'rating_count'       => $rating['rating_count'],
                'category_id'        => $category_id,
                'reviewer_count'     => 0,
                'rank'               => 0,

            ],
            $this->getArrayValuesBasedInLang(),
            $relations_loaded
        );
    }

    private function images() {
        $images = [];
        
        if (isset($this->image)) {
            $images[] = sc_image_cdn_get_path($this->image);
        }

        if ($this->images) {
            foreach ($this->images as $image) {
                $images[] = sc_image_cdn_get_path($image->image);
            }
        }
        return $images;
    }

    private function getArrayValuesBasedInLang() {
        if ($this->relationLoaded('productDescription') && !empty($this->productDescription)) {
            return $this->productDescription->only(
                [
                    'name',
                    'description',
                    'ingredients',
                    'allergies',
                    'gluten_free',
                    'weight'
                ]
            );
        }
        return [
            'name'        => '',
            'description' => '',
            'ingredients' => '',
            'allergies'   => '',
            'gluten_free' => '',
            'weight'      => '',
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
