<?php


namespace App\Modules\Products\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductPromotionsTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {
        return [
            'price_promotion' => $this->price_promotion,
            'date_start'      => $this->date_start,
            'date_end'        => $this->date_end,
        ];
    }
}
