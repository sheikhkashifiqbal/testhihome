<?php
/**
 * Created by PhpStorm.
 * User: mohamed
 * Date: 12/5/18
 * Time: 2:14 PM
 */

namespace App\Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationTransformer extends JsonResource
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
            'sub-locations' => SubLocationTransformer::collection($this->whenLoaded('subLocations')),
        ];
        
        $name= $this->name;
        if($this->lang=='ar')
            $name= $this->name_ar;
        return array_merge(
            [
                'id'            => $this->id,
                'name'          => $name,
                'isocode_short' => $this->isocode_short,
            ]
            ,
            $relations_loaded
        );
    }
}
