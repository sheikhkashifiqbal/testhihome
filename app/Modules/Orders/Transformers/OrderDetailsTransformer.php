<?php


namespace App\Modules\Orders\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsTransformer extends JsonResource
{

    public function __construct($resource) {
        parent::__construct($resource);
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
        return [
            'order_id'    => $this->order_id,
            'product_id'  => $this->product_id,
            'name'        => $this->name,
            'price'       => $this->price,
            'qty'         => $this->qty,
            'total_price' => $this->total_price,
            'sku'         => $this->sku,
            'currency'    => $this->currency,
        ];
    }
}
