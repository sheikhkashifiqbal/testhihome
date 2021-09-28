<?php


namespace App\Modules\Products\Transformers;

use App\Modules\Sellers\Transformers\SellerTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSearchTransformer extends JsonResource
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
            'seller'    => new SellerTransformer($this->whenLoaded('store')),
            'promotion' => new ProductPromotionsTransformer($this->whenLoaded('validPromotions'))
        ];
        return array_merge(
            [
                'id'    => $this->id,
                'image' => sc_image_cdn_get_path($this->image),
                'price' => $this->price,
                'status'=> $this->status,
                'out_of_stock'=> $this->out_of_stock,
                'store_id'=> $this->store_id,
                'discount_percentage'  => $this->discount_percentage,
                'discount_start_date'  => $this->discount_start_date,
                'discount_expiry_date' => $this->discount_expiry_date,
            ],
            $this->getArrayValuesBasedInLang(),
            $relations_loaded
        );
    }

    private function getArrayValuesBasedInLang() {
        if ($this->relationLoaded('productDescription') && !empty($this->productDescription)) {
            return $this->productDescription->only(
                [
                    'name',
                    'description'
                ]
            );
        }
        return [
            'name'        => '',
            'description' => ''
        ];
    }

}
