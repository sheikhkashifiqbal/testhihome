<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
 {
    /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */

    public function toArray( $request )
 {

        if ( $this->orderStatus->id ) {

        } else {

        }
        $dt = Carbon::parse( $this->created_at );
        $estimated_time=Carbon::parse( $this->estimated_time )->format( 'h:i a' );

     /* $diff = $dt->diff( Carbon::now() )->format( '%H:%I:%S' );
     $diff = $dt->diffInSeconds( Carbon::now() ); */

        /* //$dt
        $now = Carbon::now();
        $diff = $dt->diffInHours( $now );
        */
        return [
            'data'=>[
                'order_id' => $this->id,
            'transaction_id' => $this->transaction,
            'order_type' => $this->order_type,
            'order_date'=> $dt->format( 'M d' ),
            'order_year'=> $dt->format( 'Y' ),
            'order_time'=> $dt->format( 'h:i a' ),
            'estimated_time'=>$estimated_time,
            'status' => $this->orderStatus,

            'brand' => $this->brand,
            'branch' => $this->branch,
            'ordered_items' => json_decode( $this->items ),
            'price' => [
                'subtotal'=>$this->subtotal,
                'shipping'=>$this->shipping,
                'discount'=>$this->discount,
                'tax'=>$this->tax,
                'total'=>'AED '.$this->total,
            ],
            'customer' => [
                'name' => $this->first_name.' '.$this->last_name,
                'address' => $this->address1,
                'landmark' => $this->address2,
                'mobile' => $this->phone,
                'email' => $this->email,
            ],
            ]

        ];
    }
}
