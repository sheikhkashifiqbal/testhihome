<?php

namespace App\Http\Resources;
use App\Models\ShopProduct as Product;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
 {
    /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */

    public function toArray( $request )
 {

        $product = Product::find( $this->product_id );
        $category = $product->categories()->first();

        $images['portrait'] = [];
        $images['landscape'] = [];
        foreach ( $product->images_portrait as $img ) {
            $images['portrait'][] = env( 'APP_URL' ).$img->image;
        }
        foreach ( $product->images_landscape as $img ) {
            $images['landscape'][] = env( 'APP_URL' ).$img->image;
        }

        return [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'id' => $this->product_id,
            'item_name' => $product->name,
            'item_description' => $product->description,
            'qty' => $this->quantity,
            'sizes'=> $product->sizes,
            'selected_sizes' => [[
                'id'=>$this->size_id,
                'name'=>$this->size_title,
                'price'=>$this->price/$this->quantity
            ]],
            'price'=>$this->price/$this->quantity,
            'images'=>$images,

        ];
    }
}
