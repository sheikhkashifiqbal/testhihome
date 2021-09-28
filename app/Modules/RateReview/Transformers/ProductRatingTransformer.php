<?php

namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\Resource;
use App\Modules\Products\Services\ProductService;

class ProductRatingTransformer extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request = [])
    {
        $product = ProductService::getProductsById($this->product_id);
        return [
          'product_id'    => $this->product_id,
          'product_name'  => $product->singleDescription->name,
          'user_id'       => $this->user_id,
          'rate'          => $this->rate,
          'order_id'      => $this->order_id,
        ];
    }
}
