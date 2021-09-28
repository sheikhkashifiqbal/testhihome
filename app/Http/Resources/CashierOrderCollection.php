<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CashierOrderCollection extends ResourceCollection
 {

    public $collects = 'App\Http\Resources\CashierOrderResource';

    /**
    * Transform the resource collection into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */

    public function toArray( $request )
 {
        
        /* return [
            'status'=>1,
            'data' => $this->collection,
            'links'=> [
                'first'=> $this->firstPageUrl(),
                'last'=> $this->last_page_url,
                'prev'=> $this->prev_page_url,
                'next'=> $this->next_page_url
            ],
            'meta'=> [
                'current_page'=> $this->currentPage(),
                //'from'=> $this->from(),
                //'to'=> $this->to(),
                'total_pages'=> $this->lastPage(),
                'path'=> $this->path(),
                'per_page'=> $this->perPage(),
                'total'=> $this->total()
            ]

        ]; */
        return $this->collection;

    }
}
