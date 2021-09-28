<?php

namespace App\Modules\RateReview\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderReviewTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request = [])
    {
        return [
          'order_id' => $this->order_id,
          'review'   => $this->review
        ];
    }
}
