<?php
/**
 * Created by PhpStorm.
 * User: mohamed
 * Date: 12/5/18
 * Time: 2:14 PM
 */

namespace App\Modules\Common\Transformers;

use App\Modules\Products\Transformers\ProductTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTransformer extends JsonResource
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
            'products' => ProductTransformer::collection($this->whenLoaded('products')),

        ];
        return array_merge(
            [
                'id'    => $this->id,
                'image' => sc_image_cdn_get_path($this->image),
            ],
            $this->getArrayValuesBasedInLang(),
            $relations_loaded
        );
    }

    protected function getArrayValuesBasedInLang() {
        if ($this->relationLoaded('Categorydescription') && !empty($this->Categorydescription)) {
            return $this->Categorydescription->only(['name']);
        }
        return ['name' => ''];
    }

}
