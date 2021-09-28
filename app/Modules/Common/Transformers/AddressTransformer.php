<?php
/**
 * Created by PhpStorm.
 * User: mohamed
 * Date: 12/5/18
 * Time: 2:14 PM
 */

namespace App\Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressTransformer extends JsonResource
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
            'id'             => $this->id,
            'tag'            => $this->tag,
            'address'        => $this->address,
            'lat'            => $this->lat,
            'long'           => $this->long,
            'landmark'       => $this->landmark,
            'name'           => $this->name,
            'mobile'         => $this->mobile,
            'email'          => $this->email,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'area'           => $this->area,
            'emirate'        => $this->emirate,
            'is_default'     => $this->is_default,
        ];
    }

}
