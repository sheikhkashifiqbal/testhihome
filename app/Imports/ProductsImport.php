<?php

namespace App\Imports;

use App\Models\AdminStore as Store;
use App\Models\ShopProduct as Product;
use App\Models\ShopProductAttribute as Size;
use App\Models\ShopProductDescription as Description;
use App\Models\ShopProductImage as Image;
use App\Models\ShopProductNotAvailableIn as NotAvailableIn;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
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
            $data = Product::create( [
                /* 'id' => $row['item_id'], */
                'sku' => 'Parkers-'.$row['item_id'],
                'brand_id' => $this->brand_id,
                'image' => $def_img??null,
                'alias' => str_slug( $row['item_name'], '-' ).rand(),
                'status' => 1,
                'is_extra' => $row['is_extra']??0,
                'price' => $row['size_1_price'],
                'is_spicy' => $row['is_spicy']?? 0,
                'sort' => $row['item_id'],
            ] );

            Description::create( [
                'product_id' => $data->id,
                'lang' => 'en',
                'name' => $row['item_name'],
                'description' => $row['description']??null
            ] );
            //images
            foreach ( $images_landscape as $img ) {
                Image::create( [
                    'product_id' => $data->id,
                    'image' => '/data/product/landscape/'.$img,
                    'type' => 'landscape'
                ] );

            }
            foreach ( $images_portrait as $img ) {
                Image::create( [
                    'product_id' => $data->id,
                    'image' => '/data/product/portrait/'.$img,
                    'type' => 'portrait'
                ] );

            }
            //category
            $row['category_id'] = $row['category_id']??0;
            $row['size_1'] = $row['size_1']??0;
            $row['size_2'] = $row['size_2']??0;
            $row['size_1_price'] = $row['size_1_price']??0;
            $row['size_2_price'] = $row['size_2_price']??0;
            $row['extras'] = $row['extras']??0;
            $row['not_available_in'] = $row['not_available_in']??0;
            $data->categories()->sync( [$row['category_id']] );
            //sizes
            $size1 = new Size( ['name' => $row['size_1'], 'price' => $row['size_1_price'], 'attribute_group_id' => 1, 'product_id' => $data->id, 'sort' => 1] );
            $data->attributes()->save( $size1 );

            if ( $row['size_2'] ) {
                $size2 = new Size( ['name' => $row['size_2'], 'price' => $row['size_2_price'], 'attribute_group_id' => 1, 'product_id' => $data->id, 'sort' => 2] );
                $data->attributes()->save( $size2 );
            }
            //extr product

            if ( $row['extras'] ) {
                $extras = explode( ',', $row['extras'] );

                $data->extras()->sync( $extras );
            }

            //not available
            $not_available_in_stores = [];
            if ( $row['not_available_in'] ) {

                $not_available_in_stores = explode( ',', $row['not_available_in'] );

                foreach ( $not_available_in_stores as $not ) {
                    $product_not_available_in = new NotAvailableIn( ['product_id' => $data->id, 'store_id' => $not] );
                    $data->not_available_in()->save( $product_not_available_in );
                }
            }
            //dd( $data->id );

            endif;
        }

    }

    public function getRowCount(): int
 {
        return $this->rows;
    }
}
