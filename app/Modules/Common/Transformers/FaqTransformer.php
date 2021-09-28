<?php


namespace App\Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqTransformer extends JsonResource
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
            'id'       => $this->id,
            'question' => $this->question,
            'answer'   => $this->answer,
        ];
    }
}
