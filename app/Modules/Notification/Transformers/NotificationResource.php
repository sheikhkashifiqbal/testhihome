<?php

namespace App\Modules\Notification\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class NotificationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
          'id'    => $this->id,
          'title' => trans('notifications.'.$this->type),
          'type' => strtolower($this->type),
          'read_at' => $this->read_at,
          'date' => json_decode($this->data),
        ];
    }
}
