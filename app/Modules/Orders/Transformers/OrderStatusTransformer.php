<?php


namespace App\Modules\Orders\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusTransformer extends JsonResource
{

    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id'   => $this->id,
            'name' => ($request->header('lang') == 'ar') ? $this->name_ar : $this->name,
            'text' => ($request->header('lang') == 'ar') ? $this->text_ar : $this->text,
            'type' => $this->type,
        ];
    }
}
