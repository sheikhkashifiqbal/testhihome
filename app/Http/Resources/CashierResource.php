<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CashierResource extends JsonResource
 {
    /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public $token = null;

    public function __construct( $resource, $token = null ) {
        // Ensure you call the parent constructor
        parent::__construct( $resource );
        $this->resource = $resource;
        $this->token = $token;
    }

    public function toArray( $request ) {
        //dd( $this-> );
        $brand = $this->branch->brand??null;
        $branch = $this->branch??null;

        if ( !$this->token )
        $this->token = $this->api_token;

        return [
            'cashier'=>[
                'id' => $this->id,
                'name' => $this->first_name.' '.$this->last_name,
                'email' => $this->email
            ],

            'brand' => [
                'id'=>$brand->id,
                'name'=>$brand->name,
                'image'=>env( 'APP_URL' ).$brand->image
            ],
            'branch' => [
                'id'=>$branch->id,
                'name'=>$branch->description->title,
                'time_active'=>$branch->time_active,
                'lat'=>$branch->lat,
                'long'=>$branch->long,
                'address'=>$branch->description->address,
                'phone'=>$branch->phone,
                'email'=>$branch->email,
            ],
            'token'=>$this->token

        ];
    }
}
