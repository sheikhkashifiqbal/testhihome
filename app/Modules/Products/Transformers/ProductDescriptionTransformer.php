<?php


namespace App\Modules\Products\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDescriptionTransformer extends JsonResource
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
            'name'                => $this->name,
            'description'         => $this->description,
            'keyword'             => $this->keyword,
            'content'             => $this->content,
            'primery_ingredients' => $this->primery_ingredients,
            'ingredients'         => $this->ingredients,
            'allergies'           => $this->allergies,
            'gluten_free'         => $this->gluten_free,
            'weight'              => $this->weight,
        ];
    }
}
