<?php


namespace App\Modules\Products\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageTransformer extends JsonResource
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
            'id'    => $this->id,
            'image' => sc_image_cdn_get_path($this->image),
            //            'type'  => $this->type,
        ];
    }
}
