<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Order2Collection extends ResourceCollection
 {

    public $collects = 'App\Http\Resources\Order2Resource';

    /**
    * Transform the resource collection into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */

    public function toArray( $request )
 {
        return $this->collection;
        /* return [
            'status'=>1,
            'orders' => $this->collection,
            'orders_count' => $this->collection->count()
        ]; */
    }
}
