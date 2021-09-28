<?php

namespace App\Modules\Offers\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class OffersListResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
          [
            'id'          => $this->id,
            'code'        => $this->code,
            'value'       => $this->value,
            'type'        => $this->type,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
          ],
          $this->getArrayValuesBasedInLang()
        );
    }//toArray

    private function getArrayValuesBasedInLang() {
        if ($this->relationLoaded('singleDescription') && !empty($this->singleDescription)) {
            return $this->singleDescription->only(
                [
                    'title',
                    'description',
                ]
            );
        }
        return [
            'title'        => '',
            'description' => '',
        ];
    }//getArrayValuesBasedInLang
}
