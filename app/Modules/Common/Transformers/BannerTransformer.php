<?php
/**
 * Created by PhpStorm.
 * User: mohamed
 * Date: 12/5/18
 * Time: 2:14 PM
 */

namespace App\Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerTransformer extends JsonResource
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
            "image" => sc_image_cdn_get_path($this->image)
        ];
    }

}
