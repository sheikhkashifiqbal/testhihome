<?php


namespace App\Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SubLocationTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request) {
        $name= $this->name;
        if($this->lang=='ar')
            $name= $this->name_ar;
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'name'        => $name,
            'active'      => $this->active,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'location_id' => $this->location_id,
        ];
    }
}
