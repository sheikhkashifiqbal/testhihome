<?php


namespace App\Modules\Cart\Transformers;

use App\Modules\Products\Transformers\ProductTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemTransformer extends JsonResource
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
            'product_details' => new ProductTransformer($this->whenLoaded('product'))
        ];
        return array_merge(
            [
                'cart_id'    => $this->cart_id,
                'product_id' => $this->product_id,
                'quantity'   => $this->quantity,
                'price'      => $this->price
            ],
            $relations_loaded
        );
    }
}
