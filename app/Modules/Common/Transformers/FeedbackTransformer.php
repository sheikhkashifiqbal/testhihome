<?php


namespace App\Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackTransformer extends JsonResource
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
            'user_id'  => $this->user_id,
            'body'  => $this->body,
            'image' => sc_image_get_path($this->image),
        ];
    }
}
