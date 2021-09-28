<?php

namespace App\Imports;

use App\Models\AdminStore as Store;
use App\Models\ShopProductImage as Image;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImagesImport implements ToCollection, WithHeadingRow
 {
    private $rows = 0;
    public $brand_id;

    public function __construct( $brand_id )
 {
        $this->brand_id = $brand_id;
    }

    public function collection( Collection $rows )
 {
        //$row is hodling every row of excell later to do validation if exist
        //import Product
        $stores = Store::where( 'brand_id', $this->brand_id )
        ->where( 'site_status', 1 )
        ->pluck( 'id' )->toArray();

     foreach ($rows as $row) {
         //images_portrait/images_landscape
         if (isset($row['item_id']) && $row['item_id']):
             $def_img          = null;
             $images_landscape = [];
             if (isset($row['images_landscape']) && $row['images_landscape']) {
                 $images_landscape = explode(',', str_replace(' ', '', $row['images_landscape']));

                 if (count($images_landscape)) {
                     $def_img = '/data/product/landscape/' . $images_landscape[0];
                 }
             }
            $images_portrait = [];
            if ( isset( $row['images_portrait'] ) && $row['images_portrait'] ) {
                $images_portrait = explode( ',', str_replace( ' ', '', $row['images_portrait'] ) );
                if ( count( $images_portrait ) ) $def_img = '/data/product/portrait/'.$images_portrait[0];
            }

            ++$this->rows;
            //Product::where( 'id', $row['item_id'] )->update( ['image' => $def_img??null] );

            //images
            foreach ( $images_landscape as $img ) {
                Image::create( [
                    'product_id' => $row['item_id'],
                    'image' => '/data/product/landscape/'.$img,
                    'type' => 'landscape'
                ] );

            }
            foreach ( $images_portrait as $img ) {
                Image::create( [
                    'product_id' => $row['item_id'],
                    'image' => '/data/product/portrait/'.$img,
                    'type' => 'portrait'
                ] );

            }

            endif;
        }

    }

    public function getRowCount(): int
 {
        return $this->rows;
    }
}
